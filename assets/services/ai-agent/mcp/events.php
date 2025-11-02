<?php
declare(strict_types=1);

/**
 * File: assets/services/ai-agent/mcp/events.php
 * Purpose: SSE event stream for streaming tool calls
 * Author: GPT-5 Production System
 * Last Modified: 2025-11-02
 * Dependencies: Bootstrap.php, Telemetry.php
 */

require_once __DIR__ . '/../lib/Bootstrap.php';
require_once __DIR__ . '/../lib/Telemetry.php';

// SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');

// Disable output buffering
if (ob_get_level()) ob_end_clean();

// Helper to send SSE event
function send_sse(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Helper to send error and exit
function sse_error(string $code, string $message, array $details = []): never {
    send_sse('error', [
        'error' => $code,
        'message' => $message,
        'details' => $details,
        'timestamp' => date(DATE_ATOM),
    ]);
    send_sse('done', ['status' => 'error']);
    exit;
}

try {
    $pdo = get_pdo();
    $tel = new Telemetry($pdo);

    $ticket = $_GET['ticket'] ?? null;
    if (!$ticket) {
        sse_error('INVALID_REQUEST', 'Missing ticket parameter');
    }

    // Retrieve and validate ticket
    $stmt = $pdo->prepare("
        SELECT tool, args, request_id, expires_at, used_at
        FROM ai_stream_tickets
        WHERE ticket = ?
    ");
    $stmt->execute([$ticket]);
    $ticketData = $stmt->fetch();

    if (!$ticketData) {
        sse_error('INVALID_TICKET', 'Ticket not found or expired');
    }

    if ($ticketData['used_at']) {
        sse_error('TICKET_USED', 'Ticket already consumed', [
            'used_at' => $ticketData['used_at']
        ]);
    }

    if (strtotime($ticketData['expires_at']) < time()) {
        sse_error('TICKET_EXPIRED', 'Ticket expired', [
            'expired_at' => $ticketData['expires_at']
        ]);
    }

    // Mark ticket as used
    $stmt = $pdo->prepare("UPDATE ai_stream_tickets SET used_at = NOW() WHERE ticket = ?");
    $stmt->execute([$ticket]);

    $tool = $ticketData['tool'];
    $args = json_decode($ticketData['args'], true);
    $requestId = $ticketData['request_id'];

    // Send start event
    send_sse('start', [
        'tool' => $tool,
        'request_id' => $requestId,
        'timestamp' => date(DATE_ATOM),
    ]);

    // Start telemetry
    $toolStartTime = microtime(true);
    $tel->toolStart($tool, $args, [
        'request_id' => $requestId,
        'stream' => true,
        'ticket' => $ticket,
    ]);

    // Send heartbeat
    send_sse('heartbeat', ['timestamp' => date(DATE_ATOM)]);

    // Phase 1: Invoke tool
    send_sse('phase', [
        'phase' => 'invoking',
        'message' => 'Calling tool...',
    ]);

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
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        $tel->toolFinish($tool, ['error' => $curlError], microtime(true) - $toolStartTime, false);
        sse_error('TOOL_ERROR', 'Tool invocation failed', [
            'tool' => $tool,
            'error' => $curlError,
        ]);
    }

    // Phase 2: Processing result
    send_sse('phase', [
        'phase' => 'processing',
        'message' => 'Processing result...',
    ]);

    $result = json_decode($response, true);
    if (!$result) {
        $tel->toolFinish($tool, ['error' => 'Invalid JSON'], microtime(true) - $toolStartTime, false);
        sse_error('TOOL_ERROR', 'Invalid tool response', [
            'tool' => $tool,
            'http_code' => $httpCode,
        ]);
    }

    $success = ($result['success'] ?? false) === true;
    $toolData = $result['data'] ?? $result;

    // Send result event
    send_sse('result', [
        'success' => $success,
        'data' => $toolData,
        'http_code' => $httpCode,
        'duration_ms' => round((microtime(true) - $toolStartTime) * 1000, 2),
    ]);

    // Finish telemetry
    $tel->toolFinish($tool, $toolData, microtime(true) - $toolStartTime, $success);

    // Send completion event
    send_sse('done', [
        'status' => $success ? 'success' : 'error',
        'timestamp' => date(DATE_ATOM),
    ]);

} catch (Throwable $e) {
    error_log("SSE stream error: " . $e->getMessage());
    sse_error('INTERNAL_ERROR', 'Stream failed', [
        'detail' => $e->getMessage()
    ]);
}
