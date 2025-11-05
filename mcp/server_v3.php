<?php
declare(strict_types=1);

/**
 * File: mcp/server_v3.php
 * Purpose: Expose the Ecigdis MCP JSON-RPC endpoint and bridge tool calls into the AI-Agent ToolRegistry (via HTTP wrappers).
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-04
 * Dependencies: mcp_tools_turbo.php, .env for configuration
 */

// Load .env directly with proper dotenv parser (handles comments and decorative headers)
if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments and invalid lines
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        // Parse key=value (handle values with = in them)
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Skip empty keys
        if (empty($key)) {
            continue;
        }
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

require_once __DIR__ . '/mcp_tools_turbo.php';

// Load context detection library
require_once __DIR__ . '/detect_context.php';

// Load auto-logging middleware
define('MCP_SERVER_RUNNING', true);
require_once __DIR__ . '/lib/MCPAutoLogger.php';

// Initialize auto-logger (captures all requests/responses)
MCPAutoLogger::init();

// Auto-detect workspace context from multiple sources (priority order):
// 1. HTTP headers (X-Workspace-Root, X-Current-File) - from GitHub Copilot
// 2. Environment variables (WORKSPACE_ROOT, CURRENT_FILE)
// 3. Current working directory
$workspaceRoot = $_SERVER['HTTP_X_WORKSPACE_ROOT']
    ?? $_SERVER['WORKSPACE_ROOT']
    ?? $_ENV['WORKSPACE_ROOT']
    ?? getcwd();

$currentFile = $_SERVER['HTTP_X_CURRENT_FILE']
    ?? $_SERVER['CURRENT_FILE']
    ?? $_ENV['CURRENT_FILE']
    ?? null;

// Detect and store context globally
$GLOBALS['workspace_context'] = detect_context($currentFile, $workspaceRoot);

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
        // Inject workspace context into result
        if (isset($GLOBALS['workspace_context']) && is_array($result)) {
            $result['_context'] = $GLOBALS['workspace_context'];
        }

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
        ]),

        // Intelligence Hub Tools (from V2)
        $tool('semantic_search', 'Natural language search across intelligence files with relevance scoring', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Natural language search query'],
                'limit' => ['type' => 'integer', 'default' => 10, 'description' => 'Maximum results to return'],
                'unit_id' => ['type' => 'integer', 'description' => 'Filter by business unit ID'],
                'file_type' => ['type' => 'string', 'description' => 'Filter by file type']
            ],
            'required' => ['query']
        ]),
        $tool('search_by_category', 'Search files within specific business categories', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'Search keywords'],
                'category_name' => ['type' => 'string', 'description' => 'Category name to search within'],
                'limit' => ['type' => 'integer', 'default' => 20],
                'unit_id' => ['type' => 'integer']
            ],
            'required' => ['query', 'category_name']
        ]),
        $tool('find_code', 'Precise pattern matching in code, keywords, tags', [
            'type' => 'object',
            'properties' => [
                'pattern' => ['type' => 'string', 'description' => 'Code pattern to search for'],
                'search_in' => ['type' => 'string', 'enum' => ['all', 'keywords', 'tags', 'entities'], 'default' => 'all'],
                'limit' => ['type' => 'integer', 'default' => 20],
                'unit_id' => ['type' => 'integer']
            ],
            'required' => ['pattern']
        ]),
        $tool('analyze_file', 'Deep file analysis with metrics and insights', [
            'type' => 'object',
            'properties' => [
                'file_path' => ['type' => 'string', 'description' => 'Path to file to analyze']
            ],
            'required' => ['file_path']
        ]),
        $tool('get_file_content', 'Retrieve complete file content with context', [
            'type' => 'object',
            'properties' => [
                'file_path' => ['type' => 'string', 'description' => 'Path to file'],
                'include_related' => ['type' => 'boolean', 'default' => false, 'description' => 'Include related files']
            ],
            'required' => ['file_path']
        ]),
        $tool('find_similar', 'Find files similar to reference by keywords/tags', [
            'type' => 'object',
            'properties' => [
                'file_path' => ['type' => 'string', 'description' => 'Reference file path'],
                'limit' => ['type' => 'integer', 'default' => 10]
            ],
            'required' => ['file_path']
        ]),
        $tool('explore_by_tags', 'Browse files by semantic tags', [
            'type' => 'object',
            'properties' => [
                'semantic_tags' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Tags to search for'],
                'match_all' => ['type' => 'boolean', 'default' => false, 'description' => 'Require all tags or any'],
                'limit' => ['type' => 'integer', 'default' => 20]
            ],
            'required' => ['semantic_tags']
        ]),
        $tool('get_stats', 'System-wide intelligence statistics and trends', [
            'type' => 'object',
            'properties' => [
                'breakdown_by' => ['type' => 'string', 'enum' => ['unit', 'type', 'category'], 'description' => 'How to group statistics']
            ]
        ]),
        $tool('top_keywords', 'Most common keywords across system or unit', [
            'type' => 'object',
            'properties' => [
                'unit_id' => ['type' => 'integer', 'description' => 'Filter by business unit'],
                'limit' => ['type' => 'integer', 'default' => 50]
            ]
        ]),
        $tool('list_categories', 'List all business categories with file counts', [
            'type' => 'object',
            'properties' => [
                'min_priority' => ['type' => 'number', 'default' => 1.0, 'description' => 'Minimum priority threshold'],
                'order_by' => ['type' => 'string', 'enum' => ['priority', 'name', 'file_count'], 'default' => 'priority']
            ]
        ]),
        $tool('get_analytics', 'Real-time analytics and usage data', [
            'type' => 'object',
            'properties' => [
                'action' => ['type' => 'string', 'enum' => ['overview', 'popular_queries', 'trends'], 'default' => 'overview'],
                'timeframe' => ['type' => 'string', 'enum' => ['1h', '24h', '7d', '30d'], 'default' => '24h']
            ]
        ]),
        $tool('health_check', 'System health status and diagnostics', [
            'type' => 'object',
            'properties' => []
        ]),
        $tool('list_satellites', 'Status and statistics for satellite servers', [
            'type' => 'object',
            'properties' => []
        ]),
        $tool('sync_satellite', 'Trigger satellite data synchronization', [
            'type' => 'object',
            'properties' => [
                'satellite_id' => ['type' => 'integer', 'description' => 'Satellite to sync']
            ],
            'required' => ['satellite_id']
        ]),

        // Password Storage Tool
        $tool('password.store', 'Securely store encrypted credentials', [
            'type' => 'object',
            'properties' => [
                'service' => ['type' => 'string', 'description' => 'Service identifier (e.g., "xero_api", "vend_api")'],
                'username' => ['type' => 'string', 'description' => 'Username or account identifier'],
                'password' => ['type' => 'string', 'description' => 'Password or API key to encrypt and store'],
                'url' => ['type' => 'string', 'description' => 'Optional service URL'],
                'notes' => ['type' => 'string', 'description' => 'Optional notes about this credential']
            ],
            'required' => ['service', 'password']
        ]),
        $tool('password.retrieve', 'Retrieve decrypted credentials', [
            'type' => 'object',
            'properties' => [
                'service' => ['type' => 'string', 'description' => 'Service identifier']
            ],
            'required' => ['service']
        ]),
        $tool('password.list', 'List all stored credential services (passwords NOT included)', [
            'type' => 'object',
            'properties' => []
        ]),
        $tool('password.delete', 'Delete stored credentials', [
            'type' => 'object',
            'properties' => [
                'service' => ['type' => 'string', 'description' => 'Service identifier to delete']
            ],
            'required' => ['service']
        ]),

        // MySQL Query Tool
        $tool('mysql.query', 'Execute safe read-only MySQL queries (SELECT, SHOW, DESCRIBE, EXPLAIN)', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'SQL query to execute (read-only)'],
                'limit' => ['type' => 'integer', 'default' => 100, 'description' => 'Max rows to return'],
                'format' => ['type' => 'string', 'enum' => ['array', 'json', 'csv'], 'default' => 'array']
            ],
            'required' => ['query']
        ]),
        $tool('mysql.common_queries', 'Get list of useful pre-built queries', [
            'type' => 'object',
            'properties' => []
        ]),

        // Web Browser Tool
        $tool('browser.fetch', 'Fetch and parse web page content', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string', 'description' => 'URL to fetch'],
                'include_html' => ['type' => 'boolean', 'default' => false, 'description' => 'Include raw HTML'],
                'extract_links' => ['type' => 'boolean', 'default' => true, 'description' => 'Extract all links'],
                'extract_images' => ['type' => 'boolean', 'default' => false, 'description' => 'Extract image URLs']
            ],
            'required' => ['url']
        ]),
        $tool('browser.extract', 'Extract structured content from HTML', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string', 'description' => 'URL to extract from'],
                'selectors' => ['type' => 'object', 'description' => 'CSS selectors for content extraction']
            ],
            'required' => ['url']
        ]),
        $tool('browser.headers', 'Get HTTP headers for a URL', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string', 'description' => 'URL to check']
            ],
            'required' => ['url']
        ]),

        // Crawler Tool
        $tool('crawler.deep_crawl', 'Deep crawl website with headless Chrome (screenshots, performance)', [
            'type' => 'object',
            'properties' => [
                'start_url' => ['type' => 'string', 'description' => 'Starting URL to crawl'],
                'max_depth' => ['type' => 'integer', 'default' => 3, 'description' => 'Maximum crawl depth'],
                'max_pages' => ['type' => 'integer', 'default' => 50, 'description' => 'Maximum pages to crawl'],
                'same_domain_only' => ['type' => 'boolean', 'default' => true, 'description' => 'Stay on same domain'],
                'take_screenshots' => ['type' => 'boolean', 'default' => true, 'description' => 'Capture screenshots'],
                'performance_metrics' => ['type' => 'boolean', 'default' => true, 'description' => 'Collect performance data']
            ],
            'required' => ['start_url']
        ]),
        $tool('crawler.single_page', 'Crawl single page with full analysis', [
            'type' => 'object',
            'properties' => [
                'url' => ['type' => 'string', 'description' => 'URL to analyze'],
                'include_screenshot' => ['type' => 'boolean', 'default' => true],
                'include_console' => ['type' => 'boolean', 'default' => true, 'description' => 'Capture console logs'],
                'include_network' => ['type' => 'boolean', 'default' => true, 'description' => 'Capture network requests']
            ],
            'required' => ['url']
        ]),

        // Conversation Context Tools - AUTOMATIC MEMORY RETRIEVAL
        $tool('conversation.get_project_context', 'Get past conversations for current project - USE THIS AT START OF EVERY CONVERSATION!', [
            'type' => 'object',
            'properties' => [
                'project_id' => ['type' => 'integer', 'description' => 'Project ID (from workspace context)'],
                'limit' => ['type' => 'integer', 'default' => 5, 'description' => 'Number of past conversations to retrieve'],
                'include_messages' => ['type' => 'boolean', 'default' => true, 'description' => 'Include full message history'],
                'date_from' => ['type' => 'string', 'description' => 'Optional: filter from date (YYYY-MM-DD)']
            ],
            'required' => []
        ]),
        $tool('conversation.search', 'Search past conversations by keywords', [
            'type' => 'object',
            'properties' => [
                'search' => ['type' => 'string', 'description' => 'Keywords to search for in conversation titles/context'],
                'project_id' => ['type' => 'integer', 'description' => 'Filter by project'],
                'unit_id' => ['type' => 'integer', 'description' => 'Filter by business unit'],
                'limit' => ['type' => 'integer', 'default' => 10]
            ],
            'required' => ['search']
        ]),
        $tool('conversation.get_unit_context', 'Get all conversations for a business unit (all projects)', [
            'type' => 'object',
            'properties' => [
                'unit_id' => ['type' => 'integer', 'description' => 'Business unit ID (from workspace context)'],
                'limit' => ['type' => 'integer', 'default' => 20, 'description' => 'Number of conversations to retrieve']
            ],
            'required' => ['unit_id']
        ]),

        // AI Agent Tool - Route queries through custom AI orchestrator
        $tool('ai_agent.query', 'Query your custom AI Agent with RAG, conversation memory, and tool execution. Supports streaming to prevent summarization.', [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'description' => 'The question or request to send to the AI Agent'],
                'conversation_id' => ['type' => 'string', 'description' => 'Optional conversation ID to continue an existing conversation'],
                'context' => ['type' => 'object', 'description' => 'Optional additional context (files, code snippets, workspace info)'],
                'stream' => ['type' => 'boolean', 'default' => true, 'description' => 'Enable streaming mode to prevent summarization']
            ],
            'required' => ['query']
        ]),

        // ULTRA SCANNER - Comprehensive Code Intelligence (ALL 18 TABLES)
        $tool('ultra_scanner.scan_file', 'ULTRA SCAN: Populate ALL 18 intelligence tables for one file', [
            'type' => 'object',
            'properties' => [
                'file' => ['type' => 'string', 'description' => 'File path to scan (relative or absolute)'],
                'org_id' => ['type' => 'integer', 'default' => 1, 'description' => 'Organization ID'],
                'unit_id' => ['type' => 'integer', 'default' => 999, 'description' => 'Business unit ID (999=playground)'],
                'project_id' => ['type' => 'integer', 'description' => 'Project ID']
            ],
            'required' => ['file']
        ]),
        $tool('ultra_scanner.scan_project', 'ULTRA SCAN: Entire project/directory to ALL 18 tables', [
            'type' => 'object',
            'properties' => [
                'directory' => ['type' => 'string', 'description' => 'Directory to scan (relative path from public_html)'],
                'max_files' => ['type' => 'integer', 'default' => 500, 'description' => 'Maximum files to scan'],
                'org_id' => ['type' => 'integer', 'default' => 1],
                'unit_id' => ['type' => 'integer', 'default' => 999],
                'project_id' => ['type' => 'integer']
            ],
            'required' => ['directory']
        ]),
        $tool('ultra_scanner.get_stats', 'Get comprehensive stats from all 18 intelligence tables', [
            'type' => 'object',
            'properties' => []
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

        // Intelligence Hub Tools (route to V2 complete server as backend - use full URL)
        'semantic_search'   => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'search_by_category'=> ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'find_code'         => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'analyze_file'      => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'get_file_content'  => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'find_similar'      => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'explore_by_tags'   => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'get_stats'         => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'top_keywords'      => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'list_categories'   => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'get_analytics'     => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'health_check'      => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'list_satellites'   => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],
        'sync_satellite'    => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/OLD_BUILD/server_v2_complete.php'],

        // Password Storage Tool (routes to unified)
        'password.store'    => ['endpoint' => $unified, 'action' => 'password.store'],
        'password.retrieve' => ['endpoint' => $unified, 'action' => 'password.retrieve'],
        'password.list'     => ['endpoint' => $unified, 'action' => 'password.list'],
        'password.delete'   => ['endpoint' => $unified, 'action' => 'password.delete'],

        // MySQL Query Tool (routes to unified)
        'mysql.query'       => ['endpoint' => $unified, 'action' => 'mysql.query'],
        'mysql.common_queries' => ['endpoint' => $unified, 'action' => 'mysql.common_queries'],

        // Web Browser Tool (routes to unified)
        'browser.fetch'     => ['endpoint' => $unified, 'action' => 'browser.fetch'],
        'browser.extract'   => ['endpoint' => $unified, 'action' => 'browser.extract'],
        'browser.headers'   => ['endpoint' => $unified, 'action' => 'browser.headers'],

        // Crawler Tool (routes to unified)
        'crawler.deep_crawl' => ['endpoint' => $unified, 'action' => 'crawler.deep_crawl'],
        'crawler.single_page' => ['endpoint' => $unified, 'action' => 'crawler.single_page'],

        // Conversation Context Tools - AUTOMATIC MEMORY RETRIEVAL
        'conversation.get_project_context' => ['endpoint' => 'https://gpt.ecigdis.co.nz/api/get_project_conversations.php'],
        'conversation.search' => ['endpoint' => 'https://gpt.ecigdis.co.nz/api/get_project_conversations.php'],
        'conversation.get_unit_context' => ['endpoint' => 'https://gpt.ecigdis.co.nz/api/get_project_conversations.php'],

        // AI Agent Tool - Custom orchestrator with RAG + Tools
        'ai_agent.query' => ['endpoint' => 'https://gpt.ecigdis.co.nz/mcp/tools/ai_agent_query_endpoint.php'],

        // ULTRA SCANNER - Comprehensive Code Intelligence (ALL 18 TABLES)
        'ultra_scanner.scan_file' => ['endpoint' => $unified, 'action' => 'ultra_scanner.scan_file'],
        'ultra_scanner.scan_project' => ['endpoint' => $unified, 'action' => 'ultra_scanner.scan_project'],
        'ultra_scanner.get_stats' => ['endpoint' => $unified, 'action' => 'ultra_scanner.get_stats'],
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
    global $TOOL_HANDLERS;

    // First check if tool has a direct handler in $TOOL_HANDLERS (from mcp_tools_turbo.php)
    if (isset($TOOL_HANDLERS[$toolName]) && is_callable($TOOL_HANDLERS[$toolName])) {
        try {
            $result = $TOOL_HANDLERS[$toolName]($arguments);
            return $result;
        } catch (Throwable $e) {
            throw new ToolInvocationException(
                'Tool execution failed: ' . $e->getMessage(),
                500,
                ['tool' => $toolName, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            );
        }
    }

    // Fallback to HTTP routing for tools that need external endpoints
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
