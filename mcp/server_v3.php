<?php
declare(strict_types=1);

/**
 * IntelligenceHub MCP — HTTP JSON-RPC server (no custom ports)
 * - GET  ?action=meta                -> meta ping
 * - POST ?action=rpc  (JSON-RPC 2.0) -> initialize, tools/list, tools/call, health.ping
 *
 * Wraps your existing Agent APIs at:
 *   https://gpt.ecigdis.co.nz/ai-agent/public/agent/api/chat.php
 *   https://gpt.ecigdis.co.nz/ai-agent/public/agent/api/knowledge.php
 *
 * Optional auth: set MCP_API_KEY in env; client sends Authorization: Bearer <key>
 */

require_once __DIR__ . '/mcp_tools_turbo.php';

header('Content-Type: application/json; charset=utf-8');
// Bust caches / Varnish-safe
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

if (!function_exists('str_starts_with')) { // PHP 7 fallback
    function str_starts_with($h, $n) { return substr($h, 0, strlen($n)) === $n; }
}

$ACTION = $_GET['action'] ?? 'rpc';
$METHOD = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ----- Optional API key gate -----
$API_KEY = getenv('MCP_API_KEY') ?: '';
if ($API_KEY !== '') {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $ok = false;
    if (preg_match('/^Bearer\s+(.+)/i', $auth, $m)) {
        $ok = hash_equals($API_KEY, $m[1]);
    }
    if (!$ok) {
        respond(['error' => 'Unauthorized'], 401);
    }
}

// ----- Routes -----
if ($ACTION === 'meta' && $METHOD === 'GET') {
    global $TOOLS;
    // Simple human/testable ping
    respond([
        'name'       => 'IntelligenceHub MCP',
        'version'    => '3.0.0',
        'transport'  => ['http-jsonrpc'],
        'endpoints'  => ['rpc' => '/mcp/server_v3.php?action=rpc'],
        'tools'      => array_values($TOOLS),
        'time'       => date(DATE_ATOM)
    ]);
}

if ($ACTION === 'rpc' && $METHOD === 'POST') {
    $raw = file_get_contents('php://input');
    $req = json_decode($raw, true);

    if (!is_array($req) || !isset($req['method'])) {
        respond(['error'=>'Invalid JSON-RPC request'], 400);
    }

    $id     = $req['id']     ?? null;
    $method = $req['method'] ?? '';
    $params = $req['params'] ?? [];

    try {
        $result = handle_rpc($method, $params, $id);
        send_jsonrpc($id, $result);
    } catch (Throwable $e) {
        // Uniform JSON-RPC error envelope
        send_jsonrpc_error($id, -32603, 'Internal error', ['detail' => $e->getMessage()]);
    }
}

// Fallback
respond(['error' => 'Not Found'], 404);

// ======================== Implementation ========================

function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function send_jsonrpc($id, $result): void
{
    respond(['jsonrpc' => '2.0', 'id' => $id, 'result' => $result], 200);
}

function send_jsonrpc_error($id, int $code, string $message, array $data = []): void
{
    respond([
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ],
    ], 200);
}

function handle_rpc(string $method, array $params, $id) {
    global $TOOLS;
    switch ($method) {
        case 'initialize':
            return [
                'server' => ['name' => 'IntelligenceHub MCP', 'version' => '3.0.0'],
                'capabilities' => ['tools' => true],
                'tools' => array_values($TOOLS)
            ];

        case 'tools/list':
            return ['tools' => array_values($TOOLS)];

        case 'tools/call':
            $name = $params['name'] ?? '';
            $args = $params['arguments'] ?? [];
            return invoke_tool($name, $args);

        case 'health.ping':
            return ['ok' => true, 'time' => date(DATE_ATOM)];

        default:
            // Optional: direct tool name as method (e.g., method="chat.send")
            if (str_starts_with($method, 'chat.') || str_starts_with($method, 'knowledge.') || str_starts_with($method, 'doc.') || str_starts_with($method, 'db.') || str_starts_with($method, 'fs.') || str_starts_with($method, 'ssh.') || str_starts_with($method, 'system.') || str_starts_with($method, 'ops.') || str_starts_with($method, 'github.')) {
                return invoke_tool($method, $params);
            }
            throw new RuntimeException('Method not found: ' . $method);
    }
}

function invoke_tool(string $name, array $args)
{
    global $TOOL_HANDLERS;
    if (!isset($TOOL_HANDLERS[$name])) {
        throw new RuntimeException('Tool not found: ' . $name);
    }

    $handler = $TOOL_HANDLERS[$name];
    $response = $handler($args);
    log_mcp_call($name, $args, $response);

    return $response;
}

function log_mcp_call(string $toolName, array $args, array $response): void
{
    static $pdo = null;
    static $skip = false;

    if ($skip) {
        return;
    }

    try {
        if ($pdo === null) {
            $dsn = 'mysql:host=' . envv('DB_HOST', '127.0.0.1') . ';dbname=' . envv('DB_NAME', 'jcepnzzkmj') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, envv('DB_USER', 'jcepnzzkmj'), envv('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        $stmt = $pdo->prepare('INSERT INTO mcp_tool_calls (tool_name, args_json, status, response_json) VALUES (:tool, :args, :status, :resp)');
        $stmt->execute([
            ':tool' => $toolName,
            ':args' => json_encode($args, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':status' => (int) ($response['status'] ?? 0),
            ':resp' => json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Throwable $e) {
        // Disable logging after first failure to avoid noisy errors.
        $skip = true;
        $pdo = null;
    }
}
