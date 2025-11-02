<?php
declare(strict_types=1);

/**
 * File: assets/services/ai-agent/mcp/call.php
 * Purpose: MCP tool invocation endpoint with streaming support
 * Author: GPT-5 Production System
 * Last Modified: 2025-11-02
 * Dependencies: Bootstrap.php, Telemetry.php, Resilience.php (when available)
 */

require_once __DIR__ . '/../lib/Bootstrap.php';
require_once __DIR__ . '/../lib/Telemetry.php';

// CORS + security headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $reqId = new_request_id();
    response_json(envelope_error('METHOD_NOT_ALLOWED', 'Only POST supported', $reqId), 405);
}

try {
    $pdo = get_pdo();
    $tel = new Telemetry($pdo);
    $body = req_json();

    // Extract parameters
    $tool = $body['tool'] ?? null;
    $args = $body['args'] ?? $body['arguments'] ?? [];
    $stream = (bool)($body['stream'] ?? false);
    $requestId = $body['request_id'] ?? new_request_id();
    $idempotencyKey = $body['idempotency_key'] ?? null;

    if (!$tool || !is_string($tool)) {
        response_json(envelope_error('INVALID_REQUEST', 'Missing or invalid tool name', $requestId), 400);
    }

    if (!is_array($args)) {
        response_json(envelope_error('INVALID_REQUEST', 'Arguments must be an object', $requestId), 400);
    }

    // Check idempotency if key provided
    if ($idempotencyKey) {
        $stmt = $pdo->prepare("
            SELECT result, created_at
            FROM ai_idempotency_keys
            WHERE idempotency_key = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        $stmt->execute([$idempotencyKey]);
        $cached = $stmt->fetch();

        if ($cached) {
            $result = json_decode($cached['result'], true);
            $result['_cached'] = true;
            $result['_cached_at'] = $cached['created_at'];
            response_json(envelope_success($result));
        }
    }

    // Start telemetry
    $toolStartTime = microtime(true);
    $tel->toolStart($tool, $args, [
        'request_id' => $requestId,
        'stream' => $stream,
        'idempotency_key' => $idempotencyKey,
    ]);

    // If streaming requested, generate ticket and return SSE URL
    if ($stream) {
        // Generate one-time ticket
        $ticket = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 min expiry

        $stmt = $pdo->prepare("
            INSERT INTO ai_stream_tickets
            (ticket, tool, args, request_id, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $ticket,
            $tool,
            json_encode($args),
            $requestId,
            $expiresAt,
        ]);

        $sseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                . '/assets/services/ai-agent/mcp/events.php?ticket=' . $ticket;

        $tel->toolFinish($tool, [
            'ticket' => $ticket,
            'sse_url' => $sseUrl,
        ], microtime(true) - $toolStartTime);

        response_json(envelope_success([
            'streaming' => true,
            'ticket' => $ticket,
            'sse_url' => $sseUrl,
            'expires_at' => $expiresAt,
            'instructions' => 'Open SSE URL to receive streamed results',
        ]));
    }

    // Non-streaming: forward to invoke.php
    $invokeUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
               . '/assets/services/ai-agent/api/tools/invoke.php';

    $ch = curl_init($invokeUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Request-ID: ' . $requestId,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'tool' => $tool,
            'args' => $args,
            'request_id' => $requestId,
        ]),
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $tel->toolFinish($tool, ['error' => $curlError], microtime(true) - $toolStartTime, false);
        response_json(envelope_error('TOOL_ERROR', 'Failed to invoke tool', $requestId, [
            'tool' => $tool,
            'error' => $curlError,
        ]), 500);
    }

    $result = json_decode($response, true);
    if (!$result) {
        $tel->toolFinish($tool, ['error' => 'Invalid JSON response'], microtime(true) - $toolStartTime, false);
        response_json(envelope_error('TOOL_ERROR', 'Invalid tool response', $requestId, [
            'tool' => $tool,
            'http_code' => $httpCode,
        ]), 500);
    }

    $success = ($result['success'] ?? false) === true;
    $toolData = $result['data'] ?? $result;

    $tel->toolFinish($tool, $toolData, microtime(true) - $toolStartTime, $success);

    // Store idempotent result if key provided
    if ($idempotencyKey && $success) {
        $stmt = $pdo->prepare("
            INSERT INTO ai_idempotency_keys
            (idempotency_key, result, created_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE result = VALUES(result)
        ");
        $stmt->execute([$idempotencyKey, json_encode($toolData)]);
    }

    response_json($result, $httpCode);

} catch (Throwable $e) {
    error_log("MCP call error: " . $e->getMessage());
    $reqId = $requestId ?? new_request_id();
    response_json(envelope_error('INTERNAL_ERROR', 'Tool invocation failed', $reqId, [
        'detail' => $e->getMessage()
    ]), 500);
}
