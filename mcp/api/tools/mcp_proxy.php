<?php
declare(strict_types=1);

require_once __DIR__.'/../../lib/Bootstrap.php';

// Simple JSON-RPC proxy to local MCP server. Reads MCP_SERVER_URL and MCP_AUTH_TOKEN from .env
$rid = new_request_id();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        envelope_error('METHOD_NOT_ALLOWED', 'Use POST with application/json', $rid, [], 405);
        exit;
    }

    $db = get_pdo();
    require_api_key_if_enabled($db); // Optional bearer gate if configured

    $in = req_json();

    // Accept two styles:
    // 1) Direct JSON-RPC pass-through (jsonrpc, method, params, id)
    // 2) Convenience: { name: 'semantic_search', arguments: { ... } } → tools/call
    $jsonrpc = (string)($in['jsonrpc'] ?? '2.0');
    $method  = (string)($in['method'] ?? '');
    $params  = $in['params'] ?? null;

    if ($method === '' && isset($in['name'])) {
        // Map to tools/call
        $method = 'tools/call';
        $params = [
            'name' => (string)$in['name'],
            'arguments' => is_array($in['arguments'] ?? null) ? $in['arguments'] : [],
        ];
    }

    if ($method === '') {
        envelope_error('INVALID_INPUT', 'method or name is required', $rid, [], 422);
        exit;
    }

    $payload = [
        'jsonrpc' => $jsonrpc ?: '2.0',
        'method'  => $method,
        'params'  => $params ?? new stdClass(),
        'id'      => $in['id'] ?? $rid,
    ];

    $url = (string)env('MCP_SERVER_URL', 'https://hdgwrzntwa.cloudwaysapps.com/mcp/server_v3.php');
    $auth= env('MCP_AUTH_TOKEN');

    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Request-ID: '.$rid,
    ];
    if ($auth) {
        $headers[] = 'Authorization: Bearer '.$auth;
    }
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $raw = curl_exec($ch);
    $err = $raw === false ? curl_error($ch) : null;
    $code= curl_getinfo($ch, CURLINFO_HTTP_CODE) ?: 0;
    curl_close($ch);

    if ($raw === false || $code >= 500) {
        envelope_error('MCP_PROXY_ERROR', $err ?: ('HTTP '.$code), $rid, ['status'=>$code], 502);
        exit;
    }

    $jr = json_decode((string)$raw, true);
    if (!is_array($jr)) {
        envelope_error('MCP_BAD_RESPONSE', 'Non-JSON response from MCP', $rid, ['status'=>$code,'body'=>substr((string)$raw,0,500)], 502);
        exit;
    }

    if (isset($jr['error'])) {
        $emsg = is_array($jr['error']) ? ($jr['error']['message'] ?? 'MCP error') : (string)$jr['error'];
        envelope_error('MCP_ERROR', $emsg, $rid, ['error'=>$jr['error']], 502);
        exit;
    }

    envelope_success(['jsonrpc'=>$jr['jsonrpc'] ?? '2.0','result'=>$jr['result'] ?? null,'id'=>$jr['id'] ?? null], $rid, 200);
} catch (Throwable $e) {
    envelope_error('MCP_PROXY_FAILURE', $e->getMessage(), $rid, ['trace'=>substr($e->getTraceAsString(),0,600)], 500);
}
