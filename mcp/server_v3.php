<?php
declare(strict_types=1);

/**
 * File: mcp/server_v3.php
 * Purpose: Expose the Ecigdis MCP JSON-RPC endpoint and bridge tool calls into the AI-Agent ToolRegistry.
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 * Dependencies: mcp_tools_turbo.php, downstream Agent HTTP APIs.
 */

require_once __DIR__ . '/mcp_tools_turbo.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Content-Type-Options: nosniff');

$action = $_GET['action'] ?? 'rpc';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$apiKey = envv('MCP_API_KEY', '');

if ($action === 'rpc') {
	try {
		enforce_api_key($apiKey);
	} catch (UnauthorizedException $e) {
		respond_error('UNAUTHORIZED', $e->getMessage(), [], 401);
	}
}

if ($action === 'meta' && $method === 'GET') {
	respond([
		'name' => 'Ecigdis MCP',
		'version' => '3.0.0',
		'tools' => tool_catalog(),
		'time' => date(DATE_ATOM),
		'request_id' => current_request_id(),
	]);
}

if ($action !== 'rpc') {
	respond_error('NOT_FOUND', 'Unsupported action', ['action' => $action], 404);
}

if ($method !== 'POST') {
	respond_error('INVALID_METHOD', 'RPC endpoint expects POST', ['method' => $method], 405);
}

$rawBody = file_get_contents('php://input');
$request = json_decode((string) $rawBody, true);

if (!is_array($request)) {
	respond_error('INVALID_REQUEST', 'Body must be valid JSON', [], 400);
}

process_jsonrpc_request($request);

// ======================================================================
// Support code
// ======================================================================

class ToolInvocationException extends RuntimeException
{
	/** @var int */
	private $status;

	/** @var array<string, mixed> */
	private $details;

	/**
	 * @param string $message
	 * @param int $status
	 * @param array<string, mixed> $details
	 */
	public function __construct(string $message, int $status, array $details = [])
	{
		parent::__construct($message, $status);
		$this->status = $status;
		$this->details = $details;
	}

	public function getStatusCode(): int
	{
		return $this->status;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function getDetails(): array
	{
		return $this->details;
	}
}

class InvalidRequestException extends RuntimeException
{
}

class UnauthorizedException extends RuntimeException
{
}

function current_request_id(): string
{
	static $rid = null;
	if ($rid === null) {
		$rid = 'mcp-' . bin2hex(random_bytes(8));
	}

	return $rid;
}

/**
 * @return array<int, array<string, mixed>>
 */
function tool_catalog(): array
{
	static $catalog = null;

	if ($catalog === null) {
		$catalog = [
			[
				'name' => 'db.query',
				'description' => 'Read-only SQL SELECT. Params: query, params[]',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'query' => ['type' => 'string', 'minLength' => 1],
						'params' => ['type' => 'array', 'items' => ['type' => 'string'], 'default' => []],
					],
					'required' => ['query'],
				],
			],
			[
				'name' => 'db.schema',
				'description' => 'Describe tables or a single table',
				'inputSchema' => [
					'type' => 'object',
					'properties' => ['table' => ['type' => 'string']],
				],
			],
			[
				'name' => 'db.tables',
				'description' => 'List all tables',
				'inputSchema' => ['type' => 'object', 'properties' => []],
			],
			[
				'name' => 'db.explain',
				'description' => 'EXPLAIN FORMAT=JSON a SELECT',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'query' => ['type' => 'string', 'minLength' => 7],
						'params' => ['type' => 'array', 'items' => ['type' => 'string'], 'default' => []],
					],
					'required' => ['query'],
				],
			],
			[
				'name' => 'fs.list',
				'description' => 'List files in jailed root',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'path' => ['type' => 'string'],
						'recursive' => ['type' => 'boolean', 'default' => false],
						'show_hidden' => ['type' => 'boolean', 'default' => false],
					],
					'required' => ['path'],
				],
			],
			[
				'name' => 'fs.read',
				'description' => 'Read text file',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'path' => ['type' => 'string'],
						'max_lines' => ['type' => 'integer'],
					],
					'required' => ['path'],
				],
			],
			[
				'name' => 'fs.write',
				'description' => 'Write text file with optional backup',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'path' => ['type' => 'string'],
						'content' => ['type' => 'string'],
						'backup' => ['type' => 'boolean', 'default' => true],
					],
					'required' => ['path', 'content'],
				],
			],
			[
				'name' => 'fs.info',
				'description' => 'Stat path (file/dir)',
				'inputSchema' => [
					'type' => 'object',
					'properties' => ['path' => ['type' => 'string']],
					'required' => ['path'],
				],
			],
			[
				'name' => 'kb.search',
				'description' => 'RAG search (KB vector + fallback keyword)',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'query' => ['type' => 'string'],
						'limit' => ['type' => 'integer', 'default' => 5],
						'min_similarity' => ['type' => 'number', 'default' => 0.7],
					],
					'required' => ['query'],
				],
			],
			[
				'name' => 'kb.add_document',
				'description' => 'Add a doc to KB',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'title' => ['type' => 'string'],
						'content' => ['type' => 'string'],
						'type' => ['type' => 'string', 'default' => 'document'],
						'metadata' => ['type' => 'object'],
					],
					'required' => ['title', 'content'],
				],
			],
			[
				'name' => 'kb.list_documents',
				'description' => 'List docs',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'page' => ['type' => 'integer', 'default' => 1],
						'limit' => ['type' => 'integer', 'default' => 20],
						'type' => ['type' => 'string'],
						'search' => ['type' => 'string'],
					],
				],
			],
			[
				'name' => 'kb.get_document',
				'description' => 'Get a document',
				'inputSchema' => [
					'type' => 'object',
					'properties' => ['document_id' => ['type' => 'string']],
					'required' => ['document_id'],
				],
			],
			[
				'name' => 'memory.get_context',
				'description' => 'Conversation context + summary',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'conversation_id' => ['type' => 'string'],
						'include_summary' => ['type' => 'boolean', 'default' => true],
						'max_messages' => ['type' => 'integer', 'default' => 10],
					],
					'required' => ['conversation_id'],
				],
			],
			[
				'name' => 'memory.store',
				'description' => 'Store a memory blob on conversation',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'conversation_id' => ['type' => 'string'],
						'content' => ['type' => 'string'],
						'memory_type' => ['type' => 'string', 'default' => 'fact'],
						'importance' => ['type' => 'string', 'default' => 'medium'],
						'tags' => ['type' => 'array', 'items' => ['type' => 'string']],
					],
					'required' => ['conversation_id', 'content'],
				],
			],
			[
				'name' => 'http.request',
				'description' => 'Capped HTTPS request with allowlist',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'url' => ['type' => 'string'],
						'method' => ['type' => 'string', 'enum' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']],
						'headers' => ['type' => 'object'],
						'body' => ['type' => 'string'],
						'timeout' => ['type' => 'integer', 'default' => 20],
					],
					'required' => ['url', 'method'],
				],
			],
			[
				'name' => 'logs.tail',
				'description' => 'Tail operations log with grep',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'path' => ['type' => 'string', 'default' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/operations.log'],
						'max_bytes' => ['type' => 'integer', 'default' => 20000],
						'grep' => ['type' => 'string'],
					],
				],
			],
			[
				'name' => 'ops.ready_check',
				'description' => 'Environment readiness checks',
				'inputSchema' => ['type' => 'object', 'properties' => []],
			],
			[
				'name' => 'ops.security_scan',
				'description' => 'Run security scanner (quick|full)',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'scope' => ['type' => 'string', 'enum' => ['quick', 'full'], 'default' => 'quick'],
						'paths' => ['type' => ['array', 'string']],
					],
				],
			],
			[
				'name' => 'ops.monitoring_snapshot',
				'description' => 'Monitoring snapshot',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'window_seconds' => ['type' => 'integer', 'default' => 300],
					],
				],
			],
			[
				'name' => 'ops.performance_test',
				'description' => 'Simple perf test',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'url' => ['type' => 'string'],
						'duration' => ['type' => 'integer', 'default' => 60],
						'concurrency' => ['type' => 'integer', 'default' => 4],
					],
					'required' => ['url'],
				],
			],
			[
				'name' => 'git.search',
				'description' => 'Search code in GitHub installation',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'query' => ['type' => 'string'],
						'org' => ['type' => 'string'],
						'repo' => ['type' => ['string', 'array']],
					],
					'required' => ['query'],
				],
			],
			[
				'name' => 'git.open_pr',
				'description' => 'Create a PR from in-repo edits',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'repository_full_name' => ['type' => 'string'],
						'branch' => ['type' => 'string'],
						'base' => ['type' => 'string', 'default' => 'main'],
						'title' => ['type' => 'string'],
						'body' => ['type' => 'string'],
					],
					'required' => ['repository_full_name', 'branch', 'title'],
				],
			],
			[
				'name' => 'redis.get',
				'description' => 'Read redis key',
				'inputSchema' => [
					'type' => 'object',
					'properties' => ['key' => ['type' => 'string']],
					'required' => ['key'],
				],
			],
			[
				'name' => 'redis.set',
				'description' => 'Write redis key',
				'inputSchema' => [
					'type' => 'object',
					'properties' => [
						'key' => ['type' => 'string'],
						'value' => ['type' => 'string'],
						'ttl' => ['type' => 'integer', 'default' => 0],
					],
					'required' => ['key', 'value'],
				],
			],
		];
	}

	return $catalog;
}

/**
 * @return array<string, array<string, mixed>>
 */
function tool_routes(): array
{
	static $routes = null;

	if ($routes === null) {
		$routes = [
			'db.query' => ['endpoint' => 'public/api/DatabaseTool', 'action' => 'query'],
			'db.schema' => ['endpoint' => 'public/api/DatabaseTool', 'action' => 'schema'],
			'db.tables' => ['endpoint' => 'public/api/DatabaseTool', 'action' => 'tables'],
			'db.explain' => ['endpoint' => 'public/api/DatabaseTool', 'action' => 'explain'],
			'fs.list' => ['endpoint' => 'public/api/Files.php', 'action' => 'list'],
			'fs.read' => ['endpoint' => 'public/api/Files.php', 'action' => 'read'],
			'fs.write' => ['endpoint' => 'public/api/Files.php', 'action' => 'write'],
			'fs.info' => ['endpoint' => 'public/api/Files.php', 'action' => 'info'],
			'kb.search' => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'search'],
			'kb.add_document' => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'add_document'],
			'kb.list_documents' => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'list_documents'],
			'kb.get_document' => ['endpoint' => 'public/agent/api/knowledge.php', 'action' => 'get_document'],
			'memory.get_context' => ['endpoint' => 'public/agent/api/v1/messages.php', 'action' => 'get_context'],
			'memory.store' => ['endpoint' => 'public/agent/api/v1/messages.php', 'action' => 'store'],
			'http.request' => ['endpoint' => 'public/api/HttpTool', 'action' => 'request'],
			'logs.tail' => ['endpoint' => 'public/api/LogsTool', 'action' => 'tail'],
			'ops.ready_check' => ['endpoint' => 'public/api/ReadyCheckTool', 'action' => 'ready_check'],
			'ops.security_scan' => ['endpoint' => 'public/api/SecurityScanTool', 'action' => 'security_scan'],
			'ops.monitoring_snapshot' => ['endpoint' => 'public/api/MonitoringTool', 'action' => 'snapshot'],
			'ops.performance_test' => ['endpoint' => 'public/api/PerformanceTestTool', 'action' => 'performance_test'],
			'git.search' => ['endpoint' => 'public/api/GitTool', 'action' => 'search'],
			'git.open_pr' => ['endpoint' => 'public/api/GitTool', 'action' => 'open_pr'],
			'redis.get' => ['endpoint' => 'public/api/RedisTool', 'action' => 'get'],
			'redis.set' => ['endpoint' => 'public/api/RedisTool', 'action' => 'set'],
		];
	}

	return $routes;
}

function process_jsonrpc_request(array $request): void
{
	$id = $request['id'] ?? null;
	$method = $request['method'] ?? '';
	$params = $request['params'] ?? [];

	try {
		if (!is_string($method) || $method === '') {
			throw new InvalidRequestException('Method must be a non-empty string');
		}

		$result = dispatch_jsonrpc($method, $params);
		send_jsonrpc_success($id, $result);
	} catch (InvalidRequestException $e) {
		send_jsonrpc_error($id, 'INVALID_REQUEST', $e->getMessage(), [], 400, -32600);
	} catch (UnauthorizedException $e) {
		send_jsonrpc_error($id, 'UNAUTHORIZED', $e->getMessage(), [], 401, -32001);
	} catch (ToolInvocationException $e) {
		send_jsonrpc_error(
			$id,
			'TOOL_ERROR',
			$e->getMessage(),
			$e->getDetails(),
			clamp_http_status($e->getStatusCode()),
			-32002
		);
	} catch (Throwable $e) {
		send_jsonrpc_error(
			$id,
			'INTERNAL_ERROR',
			'Internal server error',
			['detail' => $e->getMessage()],
			500,
			-32603
		);
	}
}

/**
 * @param mixed $params
 * @return array<string, mixed>
 */
function dispatch_jsonrpc(string $method, $params): array
{
	if (!is_array($params)) {
		throw new InvalidRequestException('Params must be an object');
	}

	switch ($method) {
		case 'initialize':
			return [
				'server' => ['name' => 'Ecigdis MCP', 'version' => '3.0.0'],
				'capabilities' => ['tools' => ['list' => true, 'call' => true]],
				'tools' => tool_catalog(),
				'request_id' => current_request_id(),
			];

		case 'tools/list':
			return ['tools' => tool_catalog(), 'request_id' => current_request_id()];

		case 'tools/call':
			$name = $params['name'] ?? '';
			if (!is_string($name) || $name === '') {
				throw new InvalidRequestException('Tool name is required');
			}

			$arguments = $params['arguments'] ?? [];
			if (!is_array($arguments)) {
				throw new InvalidRequestException('Tool arguments must be an object');
			}

			return execute_tool($name, $arguments);

		case 'health.ping':
			return ['ok' => true, 'time' => date(DATE_ATOM), 'request_id' => current_request_id()];

		default:
			$routes = tool_routes();
			if (isset($routes[$method])) {
				return execute_tool($method, $params);
			}

			throw new InvalidRequestException('Unknown method: ' . $method);
	}
}

/**
 * @param array<string, mixed> $arguments
 * @return array<string, mixed>
 */
function execute_tool(string $name, array $arguments): array
{
	$start = microtime(true);

	try {
		$result = forward_tool_call($name, $arguments);
		log_operation([
			'tool' => $name,
			'success' => true,
			'arguments' => json_slim($arguments, 1500),
			'result' => json_slim($result, 2000),
			'duration_ms' => (int) round((microtime(true) - $start) * 1000),
		]);

		if (is_array($result) && !isset($result['request_id'])) {
			$result['request_id'] = current_request_id();
		}

		return $result;
	} catch (ToolInvocationException $e) {
		log_operation([
			'tool' => $name,
			'success' => false,
			'status' => $e->getStatusCode(),
			'arguments' => json_slim($arguments, 1500),
			'error' => $e->getMessage(),
			'details' => json_slim($e->getDetails(), 1500),
			'duration_ms' => (int) round((microtime(true) - $start) * 1000),
		]);

		throw $e;
	} catch (Throwable $e) {
		log_operation([
			'tool' => $name,
			'success' => false,
			'status' => 500,
			'arguments' => json_slim($arguments, 1500),
			'error' => $e->getMessage(),
			'duration_ms' => (int) round((microtime(true) - $start) * 1000),
		]);

		throw new ToolInvocationException('Unhandled tool exception', 500, ['detail' => $e->getMessage()]);
	}
}

/**
 * @param array<string, mixed> $arguments
 * @return array<string, mixed>
 */
function forward_tool_call(string $toolName, array $arguments): array
{
	$routes = tool_routes();
	if (!isset($routes[$toolName])) {
		throw new InvalidRequestException('Tool not registered: ' . $toolName);
	}

	$route = $routes[$toolName];

	$payload = [
		'tool' => $toolName,
		'action' => $route['action'] ?? null,
		'arguments' => $arguments,
		'request_id' => current_request_id(),
	];

	$payload = array_filter($payload, static function ($value) {
		return $value !== null;
	});

	$headers = $route['headers'] ?? [];
	$headers[] = 'Accept: application/json';
	$headers[] = 'X-Request-Id: ' . current_request_id();

	$responseHeaders = [];
	[$body, $status, $error] = http_raw(
		'POST',
		agent_url($route['endpoint']),
		$payload,
		$headers,
		(int) ($route['timeout'] ?? 45),
		$responseHeaders
	);

	if ($error !== null) {
		throw new ToolInvocationException('Upstream request failed', 502, ['error' => $error]);
	}

	$decoded = null;
	if ($body !== null && $body !== '') {
		$decoded = json_decode($body, true);
		if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
			$decoded = null;
		}
	}

	if ($status < 200 || $status >= 300) {
		$details = ['status' => $status];

		if ($decoded !== null) {
			$details['upstream'] = $decoded;
		} elseif ($body !== null) {
			$details['upstream_raw'] = $body;
		}

		if ($status === 429) {
			$retryAfter = $responseHeaders['retry-after'] ?? null;
			if ($retryAfter === null && is_array($decoded) && isset($decoded['retry_after'])) {
				$retryAfter = $decoded['retry_after'];
			}
			$details['retry_after'] = $retryAfter !== null ? $retryAfter : 30;
		}

		throw new ToolInvocationException('Upstream returned HTTP ' . $status, $status, $details);
	}

	if ($decoded === null) {
		return ['status' => $status, 'data' => $body, 'request_id' => current_request_id()];
	}

	return $decoded;
}

function enforce_api_key(string $apiKey): void
{
	if ($apiKey === '') {
		return;
	}

	$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
	if ($authHeader === '') {
		throw new UnauthorizedException('Missing Authorization header');
	}

	if (!preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
		throw new UnauthorizedException('Invalid Authorization header format');
	}

	$token = trim($matches[1]);
	if (!hash_equals($apiKey, $token)) {
		throw new UnauthorizedException('Invalid API key');
	}
}

function respond(array $data, int $status = 200): void
{
	http_response_code($status);
	if (!headers_sent()) {
		header('X-Request-Id: ' . current_request_id());
	}
	echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}

function respond_error(string $code, string $message, array $details, int $status): void
{
	respond([
		'error' => [
			'code' => $code,
			'message' => $message,
			'details' => $details,
			'request_id' => current_request_id(),
		],
	], $status);
}

function send_jsonrpc_success($id, array $result): void
{
	respond([
		'jsonrpc' => '2.0',
		'id' => $id,
		'result' => $result,
	]);
}

function send_jsonrpc_error($id, string $code, string $message, array $details, int $httpStatus, int $jsonRpcCode): void
{
	respond([
		'jsonrpc' => '2.0',
		'id' => $id,
		'error' => [
			'code' => $jsonRpcCode,
			'message' => $message,
			'data' => [
				'code' => $code,
				'message' => $message,
				'details' => $details,
				'request_id' => current_request_id(),
			],
		],
	], $httpStatus);
}

function clamp_http_status(int $status): int
{
	if ($status < 100 || $status > 599) {
		return 500;
	}

	return $status;
}

function operations_log_path(): string
{
	$custom = envv('TOOL_LOGS_PATH', '');
	if ($custom !== '') {
		return $custom;
	}

	return '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/operations.log';
}

/**
 * @param array<string, mixed> $entry
 */
function log_operation(array $entry): void
{
	$path = operations_log_path();
	$directory = dirname($path);
	if (!is_dir($directory)) {
		@mkdir($directory, 0755, true);
	}

	$entry['timestamp'] = date(DATE_ATOM);
	$entry['request_id'] = current_request_id();

	$line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	if ($line === false) {
		return;
	}

	file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}
