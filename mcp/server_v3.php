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
$apiKey = envv('MCP_API_KEY', '');

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
            'health'  => "{$baseUrl}{$prefix}/server_v3.php?show=health"
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
 * Keeping your canonical names and input schema, but switch to snake_case for spec parity with MCP-style clients.
 *
 * @return array<int, array<string,mixed>>
 */
function tool_catalog(): array
{
    // NOTE: Rename inputSchema -> input_schema to better align with MCP schema field names.
    //       (If your client expects camelCase, you can keep both keys.)
    return [
        [
            'name' => 'db.query',
            'description' => 'Read-only SQL SELECT. Params: query, params[]',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string','minLength'=>1],
                    'param' => ['type' => 'array','items'=>['type'=>'string']]
                ],
                'required' => ['query']
            ]
        ],
        [
            'name' => 'db.schema',
            'description' => 'Describe all tables or a single table',
            'input_schema' => [
                'type'=>'object',
                'properties'=> ['table'=>['type'=>'string']]
            ]
        ],
        [
            'name'=>'db.tables',
            'description'=>'List all tables',
            'input_schema'=>['type'=>'object','properties'=>[]]
        ],
        [
            'name'=>'db.explain',
            'description'=>'EXPLAIN FORMAT=JSON a SELECT',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'query'=>['type'=>'string','minLength'=>7],
                    'params'=>['type'=>'array','items'=>['type'=>'string']]
                ],
                'required'=>['query']
            ]
        ],

        [
            'name'=>'fs.list',
            'description'=>'List files in jailed root',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'path'=>['type'=>'string'],
                    'recursive'=>['type'=>'boolean','default'=>false],
                    'show_hidden'=>['type'=>'boolean','default'=>false]
                ],
                'required'=>['path']
            ]
        ],
        [
            'name'=>'fs.read',
            'description'=>'Read text file (jailed)',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'path'=>['type'=>'string'],
                    'max_lines'=>['type'=>'integer']
                ],
                'required'=>['path']
            ]
        ],
        [
            'name'=>'fs.write',
            'description'=>'Write text file with optional backup (jailed)',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'path'=>['type'=>'string'],
                    'content'=>['type'=>'string'],
                    'backup'=>['type'=>'boolean','default'=>true]
                ],
                'required'=>['path','content']
            ]
        ],
        [
            'name'=>'fs.info',
            'description'=>'Stat path (file/dir) in jail',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>['path'=>['type'=>'string']],
                'required'=>['path']
            ]
        ],

        [
            'name'=>'kb.search',
            'description'=>'RAG search in KnowledgeBase',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'query'=>['type'=>'string'],
                    'limit'=>['type'=>'integer','default'=>5],
                    'min_similarity'=>['type'=>'number','default'=>0.7]
                ],
                'required'=>['query']
            ]
        ],
        [
            'name'=>'kb.add_document',
            'description'=>'Add a document to KB',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'title'=>['type'=>'string'],
                    'content'=>['type'=>'string'],
                    'type'=>['type'=>'string','default'=>'document'],
                    'metadata'=>['type'=>'object']
                ],
                'required'=>['title','content']
            ]
        ],
        [
            'name'=>'kb.list_documents',
            'description'=>'List knowledge documents',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'page'=>['type'=>'integer','default'=>1],
                    'limit'=>['type'=>'integer','default'=>20],
                    'type'=>['type'=>'string'],
                    'search'=>['type'=>'string']
                ]
            ]
        ],
        [
            'name'=>'kb.get_document',
            'description'=>'Fetch a KB document by ID',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>['document_id'=>['type'=>'string']],
                'required'=>['document_id']
            ]
        ],

        [
            'name'=>'memory.get_context',
            'description'=>'Return conversation summary + last N messages',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'conversation_id'=>['type'=>'string'],
                    'include_summary'=>['type'=>'boolean','default'=>true],
                    'max_messages'=>['type'=>'integer','default'=>10]
                ],
                'required'=>['conversation_id']
            ]
        ],
        [
            'name'=>'memory.store',
            'description'=>'Store a memory item on a conversation',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'conversation_id'=>['type'=>'string'],
                    'content'=>['type'=>'string'],
                    'memory_type'=>['type'=>'string','default'=>'fact'],
                    'importance'=>['type'=>'string','default'=>'medium'],
                    'tags'=>['type'=>'array','items'=>['type'=>'string']]
                ],
                'required'=>['conversation_id','content']
            ]
        ],

        [
            'name'=>'http.request',
            'description'=>'HTTPS request with allowlist, timeouts',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'url'=>['type'=>'string'],
                    'method'=>['type'=>'string','enum'=>['GET','POST','PUT','DELETE','PATCH','HEAD','OPTIONS']],
                    'headers'=>['type'=>'object'],
                    'body'=>['type'=>'string'],
                    'timeout'=>['type'=>'integer','default'=>20]
                ],
                'required'=>['url','method']
            ]
        ],
        [
            'name'=>'logs.tail',
            'description'=>'Tail operations log with optional grep',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'path'=>['type'=>'string','default'=>'/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/operations.log'],
                    'max_bytes'=>['type'=>'integer','default'=>20000],
                    'grep'=>['type'=>'string']
                ]
            ]
        ],

        [
            'name'=>'ops.ready_check',
            'description'=>'Environment readiness checks',
            'input_schema'=>['type'=>'object','properties'=>[]]
        ],
        [
            'name'=>'ops.security_scan',
            'description'=>'Run security scanner (quick|full)',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'scope'=>['type'=>'string','enum'=>['quick','full'],'default'=>'quick'],
                    'paths'=>['type'=>['array','string']]
                ]
            ]
        ],
        [
            'name'=>'ops.monitoring_snapshot',
            'description'=>'Monitoring snapshot',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'window_seconds'=>['type'=>'integer','default'=>300]
                ]
            ]
        ],
        [
            'name'=>'ops.performance_test',
            'description'=>'Simple perf test load generator',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'url'=>['type'=>'string'],
                    'duration'=>['type'=>'integer','default'=>60],
                    'concurrency'=>['type'=>'integer','default'=>4]
                ],
                'required'=>['url']
            ]
        ],

        [
            'name'=>'git.search',
            'description'=>'Search code in GitHub installation',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'query'=>['type'=>'string'],
                    'org'=>['type'=>'string'],
                    'repo'=>['type'=>['string','array']]
                ],
                'required'=>['query']
            ]
        ],
        [
            'name'=>'git.open_pr',
            'description'=>'Create a PR from prepared branch',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'repository_full_name'=>['type'=>'string'],
                    'branch'=>['type'=>'string'],
                    'base'=>['type'=>'string','default'=>'main'],
                    'title'=>['type'=>'string'],
                    'body'=>['type'=>'string']
                ],
                'required'=>['repository_full_name','branch','title']
            ]
        ],

        [
            'name'=>'redis.get',
            'description'=>'Read redis key',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>['key'=>['type'=>'string']],
                'required'=>['key']
            ]
        ],
        [
            'name'=>'redis.set',
            'description'=>'Write redis key',
            'input_schema'=>[
                'type'=>'object',
                'properties'=>[
                    'key'=>['type'=>'string'],
                    'value'=>['type'=>'string'],
                    'ttl'=>['type'=>'integer','default'=>0]
                ],
                'required'=>['key','value']
            ]
        ]
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

    $routes = [
        'db.query'         => ['endpoint'=>'public/api/DatabaseTool.php','action'=>'query'],
        'db.schema'        => [' endpoint'=>'public/api/DatabaseTool.php','action'=>'schema'],
        'db.tables'        => [' endpoint'=>'public/api/DatabaseTool.php','action'=>'tables'],
        'db.explain'       => [' endpoint'=>'public/api/DatabaseTool.php','action'=>'explain'],

        'fs.list'          => ['endpoint'=>'public/api/Files.php','action'=>'list'],
        'fs.read'          => ['endpoint'=>'public/api/Files.php','action'=>'read'],
        'fs.write'         => ['endpoint'=>'public/api/Files.php','action'=>'write'],
        'fs.info'          => ['endpoint'=>'public/api/Files.php','action'=>'info'],

        'kb.search'        => ['endpoint'=>'public/agent/api/knowledge.php','action'=>'search'],
        'kb.add_document'  => ['endpoint'=>'public/agent/api/knowledge.php','action'=>'add_document'],
        'kb.list_documents'=> ['endpoint'=>'public/agent/api/knowledge.php','action'=>'documents'],
        'kb.get_document'  => ['endpoint'=>'public/agent/api/knowledge.php','action'=>'get_document'],

        // FIX: map memory to a wrapper that calls App\Tools\MemoryTool::run()
        'memory.get_context' => ['endpoint'=>'public/api/MemoryTool.php','action'=>'get_context'],
        'memory.store'       => ['endpoint'=>'public/api/MemoryTool.php','action'=>'store_memory'],

        'http.request'     => ['endpoint'=>'public/api/HttpTool.php','action'=>'request'],
        'logs.tail'        => ['endpoint'=>'public/api/LogsTool.php','action'=>'tail'],
        'ops.ready_check'  => ['endpoint'=>'public/api/ReadyCheckTool.php','action'=>null],
        'ops.security_scan'=> ['endpoint'=>'public/api/SecurityScanTool.php','action'=>null],
        'ops.monitoring_snapshot'=> ['endpoint'=>'public/api/MonitoringTool.php','action'=>null],
        'ops.performance_test'=> ['endpoint'=>'public/api/PerformanceTestTool.php','action'=>null],

        'git.search'       => ['endpoint'=>'public/api/GitTool.php','action'=>'search'],      // (optional wrapper)
        'git.open_pr'      => ['endpoint'=>'public/api/GitTool.php','action'=>'open_pr'],    // (optional wrapper)

        'redis.get'        => ['endpoint'=>'public/api/RedisTool.php','action'=>'get'],
        'redis.set'        => ['endpoint'=>'public/api/RedisTool.php','action'=>'set'],
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

/** Build meta manifest; called by /?action=meta */
function build_meta_manifest(): array {
    return [
        'name'            => 'Ecigdis MCP',
        'version'         => '3.0.0',
        'protocolVersion' => '0.1.0',
        'description'     => 'MCP JSON-RPC server exposing Ecigdis IntelligenceHub tools.',
        'capabilities'    => ['tools'=>['list'=>true,'call'=>true]],
        'endpoints'       => [
            'meta'   => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/mcp/'), '/') . '/server_v3.php?action=meta'),
            'rpc'    => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/mcp/'), '/') . '/server_v3.php?action=rpc'),
            'health' => ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/mcp/'), '/') . '/server_v3.php?action=health'),
        ],
        'auth'            => [
            'type'   => envv('MCP_API_KEY') ? 'bearer' : 'none',
            'header' => 'Authorization: Bearer <MCP_API_KEY>'
        ],
        'tools'           => tool_catalog(),
        'time'            => date(DATE_ATOM),
        'request_id'      => current_request_id(),
    ];
}
