<?php
declare(strict_types=1);

/**
 * File: mcp/server_v3.php
 * Purpose: Expose the Ecigdis MCP JSON-RPC endpoint and bridge tool calls into the AI-Agent ToolRegistry (via HTTP wrappers).
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 * Dependencies: mcp_tools_turbo.php (envv(), http_raw(), agent_url(), etc.)
 */

require_once __DIR__ . '/mcp_tools_turbo.php';

$action = $_GET['action'] ?? 'rpc';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

// Read API key from header (X-API-Key or Authorization Bearer)
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (empty($apiKey) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/^Bearer\s+(.+)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        $apiKey = $matches[1];
    }
}

header('X-Content-Type-Options: nosniff');

if ($action === 'meta') {
    // Anonymous OK; do not cache aggressively (to reflect tool changes)
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    $manifest = build_meta_manifest();
    echo json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($action === 'health') {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    echo json_encode([
        'ok'        => true,
        'name'      => 'Ecigdis MCP',
        'version'   => '3.0.0',
        'time'      => date(DATE_ATOM),
        'php'       => PHP_VERSION,
        'host'      => gethostname(),
        'request_id'=> current_request_id(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($action !== 'rpc') {
    respond_error('NOT_FOUND', 'Unsupported action', ['action' => $action], 404);
}

if ($method !== 'POST') {
    respond_error('INVALID_METHOD', 'RPC endpoint expects POST', ['method' => $method], 405);
}

// Auth only required for RPC (meta/health are intentionally public for discovery)
try {
    enforce_api_key($apiKey);
} catch (UnauthorizedException $e) {
    respond_error('UNAUTHORIZED', $e->getMessage(), [], 401);
}

$rawBody = file_get_contents('php://input');
$request = json_decode((string) $rawBody, true);

if ($request === null) {
    respond_error('INVALID_REQUEST', 'Body must be valid JSON', ['json_error' => json_last_error_msg()], 400);
}

// Support single or batch JSON-RPC
if (is_array($request) && array_keys($request) === range(0, count($request) - 1)) {
    $responses = [];
    foreach ($request as $idx => $one) {
        $responses[] = process_jsonrpc_request($one, false);
    }
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Request-Id: ' . current_request_id());
    }
    echo json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

process_jsonrpc_request($request, true);

// ======================================================================
// Support code
// ======================================================================

final class ToolInvocationException extends RuntimeException
{
    private int $status;
    /** @var array<string,mixed> */
    private array $details;

    /** @param array<string,mixed> $details */
    public function __construct(string $message, int $status = 500, array $details = [])
    {
        parent::__construct($message, $status);
        $this->status  = $status;
        $this->details = $details;
    }
    public function getStatusCode(): int { return $this->status; }
    /** @return array<string,mixed> */
    public function getDetails(): array { return $this->details; }
}

final class InvalidRequestException extends RuntimeException {}
final class UnauthorizedException   extends RuntimeException {}

function current_request_id(): string {
    static $rid = null;
    if ($rid === null) {
        $rid = 'mcp-' . bin2hex(random_bytes(8));
    }
    return $rid;
}

function respond(array $data, int $status = 200): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Request-Id: ' . current_request_id());
        http_response_code($status);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function respond_error(string $code, string $message, array $details, int $status): void {
    respond([
        'error' => [
            'code'       => $code,
            'message'    => $message,
            'details'    => $details,
            'request_id' => current_request_id(),
        ]
    ], $status);
}

/** Build MCP meta manifest. */
function build_meta_manifest(): array
{
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '');
    $prefix  = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/mcp/'), '/');
    $tools   = tool_catalog(); // stays in your camelCase schema for now

    return [
        'name'            => 'Ecigdis MCP',
        'version'         => '3.0.0',
        'protocolVersion' => '0.1.0',
        'description'     => 'MCP JSON-RPC server exposing Ecigdis IntelligenceHub tools',
        'capabilities'    => [
            'tools' => [
                'list' => true,
                'call' => true
            ]
        ],
        'endpoints'       => [
            'meta'    => "{$baseUrl}{$prefix}/server_v3.php?action=meta",
            'rpc'     => "{$baseUrl}{$prefix}/server_v3.php?action=rpc",
            'health'  => "{$baseUrl}{$prefix}/server_v3.php?action=health"
        ],
        'auth'            => [
            'type'     => envv('MCP_API_KEY') ? 'bearer' : 'none',
            'header'   => 'Authorization: Bearer <MCP_API_KEY>',
        ],
        // Keep your current tool schema (name/description/inputSchema). If you want strict MCP, also
        // add a parallel 'mcpTools' array using OpenAPI-style {type:"function", function:{name,parameters}}
        'tools'           => $tools,
        'time'            => date(DATE_ATOM),
        'request_id'      => current_request_id(),
    ];
}

/**
 * Process one JSON-RPC request, return response array
 * @param array<string,mixed> $request
 * @param bool $emit If true, will echo JSON response and exit; else returns the array
 * @return array<string,mixed>|never
 */
function process_jsonrpc_request(array $request, bool $emit=true) {
    $id     = $request['id']    ?? null;
    $method = $request['method']?? '';
    $params = $request['params']?? [];

    $send_ok = function($result) use ($id, $emit) {
        $payload = ['jsonrpc'=>'2.0','id'=>$id,'result'=>$result];
        if ($emit) { respond($payload, 200); }
        return $payload;
    };
    $send_err = function(int $http, int $jsonrpcCode, string $code, string $message, array $details=[]) use ($id, $emit) {
        $payload = [
            'jsonrpc' => '2.0',
            'id'      => $id,
            'error'   => [
                'code'    => $jsonrpcCode,
                'message' => $message,
                'data'    => ['code'=>$code,'message'=>$message,'details'=>$details,'request_id'=>current_request_id()],
            ],
        ];
        if ($emit) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('X-Request-Id: ' . current_request_id());
                http_response_code($http);
            }
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        return $payload;
    };

    try {
        if (!is_string($method) || $method === '') {
            return $send_err(400, -32600, 'INVALID_REQUEST', 'Method must be a non-empty string');
        }
        if (!is_array($params)) {
            return $send_err(400, -32602, 'INVALID_PARAMS', 'Params must be an object');
        }

        if ($method === 'initialize') {
            return $send_ok([
                'server'       => ['name'=>'Ecigdis MCP','version'=>'3.0.0'],
                'capabilities' => ['tools'=>['list'=>true,'call'=>true]],
                'tools'        => tool_catalog(), // keep your naming
                'request_id'   => current_request_id(),
            ]);
        }

        if ($method === 'tools/list') {
            return $send_ok(['tools' => tool_catalog(), 'request_id'=> current_request_id()]);
        }

        if ($method === 'health.ping') {
            return $send_ok(['ok'=>true,'time'=>date(DATE_ATOM),'request_id'=> current_request_id()]);
        }

        if ($method === 'tools/call') {
            $name = $params['name']      ?? '';
            $args = $params['arguments'] ?? [];
            $stream = (bool)($params['stream'] ?? false);
            if (!is_string($name) || $name === '') {
                return $send_err(400, -32602, 'INVALID_PARAMS', 'Tool name is required');
            }
            if (!is_array($args)) {
                return $send_err(400, -32602, 'INVALID_PARAMS', 'arguments must be an object');
            }
            // pass through to HTTP bridge
            try {
                $result = forward_tool_call($name, $args, $stream);
                return $send_ok($result);
            } catch (ToolInvocationException $e) {
                return $send_err(clamp_http_status($e->getStatusCode()), -32002, 'TOOL_ERROR', $e->getMessage(), $e->getDetails());
            } catch (Throwable $e) {
                return $send_err(500, -32603, 'INTERNAL_ERROR', 'Unhandled tool exception', ['detail'=>$e->getMessage()]);
            }
        }

        // Support direct method -> route fallback `db.tables`, etc.
        $routes = tool_routes();
        if (isset($routes[$method])) {
            try {
                $result = forward_tool_call($method, $params, (bool)($params['stream'] ?? false));
                return $send_ok($result);
            } catch (ToolInvocationException $e) {
                return $send_err(clamp_http_status($e->getStatusCode()), -32002, 'TOOL_ERROR', $e->getMessage(), $e->getDetails());
            } catch (Throwable $e) {
                return $send_err(500, -32603, 'INTERNAL_ERROR', 'Unhandled tool exception', ['detail'=>$e->getMessage()]);
            }
        }

        return $send_err(404, -32601, 'METHOD_NOT_FOUND', 'Unknown method: ' . $method);
    } catch (UnauthorizedException $e) {
        return $send_err(401, -32001, 'UNAUTHORIZED', $e->getMessage());
    }
}

/**
 * Tool catalog advertised to MCP clients.
 * Provides dual schema keys (input_schema + inputSchema) for compatibility.
 *
 * @return array<int, array<string,mixed>>
 */
function tool_catalog(): array
{
    // Helper to create tool spec with dual schema keys
    $tool = function(string $name, string $description, array $schema): array {
        return [
            'name' => $name,
            'description' => $description,
            'input_schema' => $schema,  // snake_case (MCP standard)
            'inputSchema' => $schema,   // camelCase (backward compat)
        ];
    };

    return [
        // Database tools
        $tool('db.query', 'Read-only SQL SELECT. Params: query, params[]', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 1],
                'params' => ['type' => 'array', 'items' => ['type' => 'string']]
            ],
            'required' => ['query']
        ]),
        $tool('db.schema', 'Describe all tables or a single table', [
            'type' => 'object',
            'properties' => ['table' => ['type' => 'string']]
        ]),
        $tool('db.tables', 'List all tables', [
            'type' => 'object',
            'properties' => []
        ]),
        $tool('db.explain', 'EXPLAIN FORMAT=JSON a SELECT', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 7],
                'params' => ['type' => 'array', 'items' => ['type' => 'string']]
            ],
            'required' => ['query']
        ]),

        // File system tools
        $tool('fs.list', 'List files in jailed root', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'recursive' => ['type' => 'boolean', 'default' => false],
                'show_hidden' => ['type' => 'boolean', 'default' => false]
            ],
            'required' => ['path']
        ]),
        $tool('fs.read', 'Read text file (jailed)', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'max_lines' => ['type' => 'integer']
            ],
            'required' => ['path']
        ]),
        $tool('fs.write', 'Write text file with optional backup (jailed)', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'content' => ['type' => 'string'],
                'backup' => ['type' => 'boolean', 'default' => true]
            ],
            'required' => ['path', 'content']
        ]),
        $tool('fs.info', 'Stat path (file/dir) in jail', [
            'type' => 'object',
            'properties' => ['path' => ['type' => 'string']],
            'required' => ['path']
        ]),

        // Knowledge base tools
        $tool('kb.search', 'RAG search in KnowledgeBase', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string'],
                'limit' => ['type' => 'integer', 'default' => 5],
                'min_similarity' => ['type' => 'number', 'default' => 0.7]
            ],
            'required' => ['query']
        ]),
        $tool('kb.add_document', 'Add a document to KB', [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'content' => ['type' => 'string'],
                'type' => ['type' => 'string', 'default' => 'document'],
                'metadata' => ['type' => 'object']
            ],
            'required' => ['title', 'content']
        ]),
        $tool('kb.list_documents', 'List knowledge documents', [
            'type' => 'object',
            'properties' => [
                'page' => ['type' => 'integer', 'default' => 1],
                'limit' => ['type' => 'integer', 'default' => 20],
                'type' => ['type' => 'string'],
                'search' => ['type' => 'string']
            ]
        ]),
        $tool('kb.get_document', 'Fetch a KB document by ID', [
            'type' => 'object',
            'properties' => ['document_id' => ['type' => 'string']],
            'required' => ['document_id']
        ]),

        // Memory tools
        $tool('memory.get_context', 'Return conversation summary + last N messages', [
            'type' => 'object',
            'properties' => [
                'conversation_id' => ['type' => 'string'],
                'include_summary' => ['type' => 'boolean', 'default' => true],
                'max_messages' => ['type' => 'integer', 'default' => 10]
            ],
            'required' => ['conversation_id']
        ]),
        $tool('memory.store', 'Store a memory item on a conversation', [
            'type' => 'object',
            'properties' => [
                'conversation_id' => ['type' => 'string'],
                'content' => ['type' => 'string'],
                'memory_type' => ['type' => 'string', 'default' => 'fact'],
                'importance' => ['type' => 'string', 'default' => 'medium'],
                'tags' => ['type' => 'array', 'items' => ['type' => 'string']]
            ],
            'required' => ['conversation_id', 'content']
        ]),

        // HTTP tools
        $tool('http.request', 'HTTPS request with allowlist, timeouts', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string'],
                'method' => ['type' => 'string', 'enum' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']],
                'headers' => ['type' => 'object'],
                'body' => ['type' => 'string'],
                'timeout' => ['type' => 'integer', 'default' => 20]
            ],
            'required' => ['url', 'method']
        ]),

        // Logs tools
        $tool('logs.tail', 'Tail operations log with optional grep', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string', 'default' => 'logs/operations.log'],
                'max_bytes' => ['type' => 'integer', 'default' => 20000],
                'grep' => ['type' => 'string']
            ]
        ]),
        $tool('logs.grep', 'Search logs with pattern', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'pattern' => ['type' => 'string'],
                'max_matches' => ['type' => 'integer', 'default' => 50]
            ],
            'required' => ['path', 'pattern']
        ]),

        // Ops tools
        $tool('ops.ready_check', 'Environment readiness checks', [
            'type' => 'object',
            'properties' => []
        ]),
        $tool('ops.security_scan', 'Run security scanner (quick|full)', [
            'type' => 'object',
            'properties' => [
                'scope' => ['type' => 'string', 'enum' => ['quick', 'full'], 'default' => 'quick'],
                'paths' => ['type' => ['array', 'string']]
            ]
        ]),
        $tool('ops.monitoring_snapshot', 'Monitoring snapshot', [
            'type' => 'object',
            'properties' => [
                'window_seconds' => ['type' => 'integer', 'default' => 300]
            ]
        ]),
        $tool('ops.performance_test', 'Simple perf test load generator', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string'],
                'duration' => ['type' => 'integer', 'default' => 60],
                'concurrency' => ['type' => 'integer', 'default' => 4]
            ],
            'required' => ['url']
        ]),

        // Git tools
        $tool('git.search', 'Search code in GitHub installation', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string'],
                'org' => ['type' => 'string'],
                'repo' => ['type' => ['string', 'array']]
            ],
            'required' => ['query']
        ]),
        $tool('git.open_pr', 'Create a PR from prepared branch', [
            'type' => 'object',
            'properties' => [
                'repository_full_name' => ['type' => 'string'],
                'branch' => ['type' => 'string'],
                'base' => ['type' => 'string', 'default' => 'main'],
                'title' => ['type' => 'string'],
                'body' => ['type' => 'string']
            ],
            'required' => ['repository_full_name', 'branch', 'title']
        ]),

        // Redis tools
        $tool('redis.get', 'Read redis key', [
            'type' => 'object',
            'properties' => ['key' => ['type' => 'string']],
            'required' => ['key']
        ]),
        $tool('redis.set', 'Write redis key', [
            'type' => 'object',
            'properties' => [
                'key' => ['type' => 'string'],
                'value' => ['type' => 'string'],
                'ttl' => ['type' => 'integer', 'default' => 0]
            ],
            'required' => ['key', 'value']
        ])
    ];
}

/**
 * Routes for forwarder -> HTTP wrappers.
 * (We include .php so agent_url() can resolve concrete file; adjust if your nginx maps extless.)
 *
 * @return array<string, array{endpoint:string, action?:string, headers?:array<string,string>, timeout?:int}>
 */
function tool_routes(): array
{
    static $routes = null;
    if ($routes !== null) return $routes;

    // Route ALL tools to unified AI-Agent invoke.php gateway
    $unified = 'assets/services/ai-agent/api/tools/invoke.php';

    $routes = [
        // Database tools (mapped to db.select, db.exec in invoke.php)
        'db.query'         => ['endpoint' => $unified, 'action' => 'db.select'],
        'db.schema'        => ['endpoint' => $unified, 'action' => 'db.select'],
        'db.tables'        => ['endpoint' => $unified, 'action' => 'db.select'],
        'db.explain'       => ['endpoint' => $unified, 'action' => 'db.select'],

        // File system tools
        'fs.list'          => ['endpoint' => $unified, 'action' => 'fs.list'],
        'fs.read'          => ['endpoint' => $unified, 'action' => 'fs.read'],
        'fs.write'         => ['endpoint' => $unified, 'action' => 'fs.write'],
        'fs.info'          => ['endpoint' => $unified, 'action' => 'fs.list'],

        // Knowledge base (legacy, kept for backward compat - will proxy)
        'kb.search'        => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'search'],
        'kb.add_document'  => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'add_document'],
        'kb.list_documents'=> ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'documents'],
        'kb.get_document'  => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'get_document'],

        // Memory tools (unified)
        'memory.get_context' => ['endpoint' => $unified, 'action' => 'memory.retrieve'],
        'memory.store'       => ['endpoint' => $unified, 'action' => 'memory.upsert'],

        // HTTP and logs
        'http.request'     => ['endpoint' => $unified, 'action' => 'http.fetch'],
        'logs.tail'        => ['endpoint' => $unified, 'action' => 'logs.tail'],
        'logs.grep'        => ['endpoint' => $unified, 'action' => 'logs.grep'],

        // Ops tools (will be extended in invoke.php)
        'ops.ready_check'  => ['endpoint' => $unified, 'action' => 'ops.ready_check'],
        'ops.security_scan'=> ['endpoint' => $unified, 'action' => 'ops.security_scan'],
        'ops.monitoring_snapshot' => ['endpoint' => $unified, 'action' => 'ops.monitoring'],
        'ops.performance_test' => ['endpoint' => $unified, 'action' => 'ops.performance_test'],

        // Git tools (will be extended)
        'git.search'       => ['endpoint' => $unified, 'action' => 'git.search'],
        'git.open_pr'      => ['endpoint' => $unified, 'action' => 'git.open_pr'],

        // Redis tools (will be extended)
        'redis.get'        => ['endpoint' => $unified, 'action' => 'redis.get'],
        'redis.set'        => ['endpoint' => $unified, 'action' => 'redis.set'],
    ];

    return $routes;
}

/**
 * Forward a tool call to a local HTTP wrapper. For now we expect the endpoint to accept:
 *   POST JSON: { "tool": "<name>", "action": "<action>|null", "arguments": {..}, "request_id": "<id>" }
 *
 * @param array<string,mixed> $arguments
 * @return array<string,mixed>
 */
function forward_tool_call(string $toolName, array $arguments, bool $stream=false): array
{
    $routes = tool_routes();
    if (!isset($routes[$toolName])) {
        throw new InvalidRequestException('Tool not registered: ' . $toolName);
    }

    $route = $routes[$toolName];

    $payload = [
        'tool'      => $toolName,
        'action'    => $route['action'] ?? null,
        'arguments' => $arguments,
        'request_id'=> current_request_id(),
    ];
    $payload = array_filter($payload, static fn($v) => $v !== null);

    // headers
    $headers   = $route['headers'] ?? [];
    $headers[] = 'Accept: application/json';
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'X-Request-Id: ' . current_request_id();

    // If you need to supply downstream secret (your /public/agent/api/… expects x-gpt-secret)
    $agentSecret = envv('MCP_AGENT_SECRET', '');
    if ($agentSecret) {
        $headers[] = 'x-gpt-secret: ' . $agentSecret;
    }

    $responseHeaders = [];
    // Optionally if you build a streaming variant later, switch to http_raw_stream() when $stream===true
    [ $body, $status, $err ] = http_raw(
        'POST',
        agent_url($route['endpoint']),
        $payload,
        $headers,
        (int)($route['timeout'] ?? 45),
        $responseHeaders
    );

    if ($err !== null) {
        throw new ToolInvocationException('Upstream request failed', 502, ['error'=>$err]);
    }

    $decoded = null;
    if ($body !== '' && $body !== null) {
        $decoded = json_decode($body, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            // Not JSON; return as raw body
            return ['status'=>$status,'data'=>$body,'request_id'=>current_request_id()];
        }
    }

    if ($status < 200 || $status >= 300) {
        $det = ['status'=>$status];
        if ($decoded !== null) $det['upstream'] = $decoded; else $det['upstream_raw'] = $body;
        if (isset($responseHeaders['retry-after'])) $det['retry_after'] = $responseHeaders['retry-after'];
        throw new ToolInvocationException('Upstream returned HTTP ' . $status, $status, $det);
    }

    return is_array($decoded) ? $decoded : ['status'=>$status,'data'=>$body,'request_id'=>current_request_id()];
}
