<?php
declare(strict_types=1);

/**
 * File: assets/services/ai-agent/mcp/registry.php
 * Purpose: MCP tool registry endpoint - lists all available tools with schemas
 * Author: GPT-5 Production System
 * Last Modified: 2025-11-02
 * Dependencies: Bootstrap.php
 */

require_once __DIR__ . '/../lib/Bootstrap.php';

// CORS + security headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $reqId = new_request_id();
    response_json(envelope_error('METHOD_NOT_ALLOWED', 'Only GET supported', $reqId), 405);
}

try {
    $pdo = get_pdo();

    // Build tool catalog
    $tools = [];

    // Helper to create tool spec with dual schema keys
    $tool = function(string $name, string $description, array $schema, array $examples = []): array {
        return [
            'name' => $name,
            'description' => $description,
            'input_schema' => $schema,
            'inputSchema' => $schema,  // Dual key for compatibility
            'examples' => $examples,
        ];
    };

    // Database tools
    $tools[] = $tool('db.select', 'Execute read-only SQL SELECT query', [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string', 'description' => 'SQL SELECT statement'],
            'params' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Prepared statement parameters']
        ],
        'required' => ['query']
    ], [
        ['query' => 'SELECT * FROM users WHERE email = ?', 'params' => ['user@example.com']],
        ['query' => 'SELECT COUNT(*) as count FROM products WHERE active = 1']
    ]);

    $tools[] = $tool('db.exec', 'Execute write SQL (INSERT/UPDATE/DELETE) - requires allow_write=true', [
        'type' => 'object',
        'properties' => [
            'query' => ['type' => 'string', 'description' => 'SQL statement'],
            'params' => ['type' => 'array', 'items' => ['type' => 'string']],
            'allow_write' => ['type' => 'boolean', 'description' => 'Must be true for writes', 'default' => false]
        ],
        'required' => ['query', 'allow_write']
    ]);

    // File system tools
    $tools[] = $tool('fs.read', 'Read file content (jailed to project root)', [
        'type' => 'object',
        'properties' => [
            'path' => ['type' => 'string', 'description' => 'Relative path from project root'],
            'max_lines' => ['type' => 'integer', 'description' => 'Limit output lines', 'default' => 1000]
        ],
        'required' => ['path']
    ], [
        ['path' => 'logs/operations.log', 'max_lines' => 100]
    ]);

    $tools[] = $tool('fs.list', 'List directory contents (jailed)', [
        'type' => 'object',
        'properties' => [
            'path' => ['type' => 'string', 'description' => 'Directory path'],
            'recursive' => ['type' => 'boolean', 'default' => false]
        ],
        'required' => ['path']
    ]);

    $tools[] = $tool('fs.write', 'Write file with automatic backup', [
        'type' => 'object',
        'properties' => [
            'path' => ['type' => 'string'],
            'content' => ['type' => 'string'],
            'backup' => ['type' => 'boolean', 'default' => true]
        ],
        'required' => ['path', 'content']
    ]);

    // HTTP tools
    $tools[] = $tool('http.fetch', 'HTTP/HTTPS request with host allowlist', [
        'type' => 'object',
        'properties' => [
            'url' => ['type' => 'string', 'description' => 'Target URL'],
            'method' => ['type' => 'string', 'enum' => ['GET', 'POST', 'PUT', 'DELETE'], 'default' => 'GET'],
            'headers' => ['type' => 'object', 'description' => 'Request headers'],
            'body' => ['type' => 'string', 'description' => 'Request body'],
            'timeout' => ['type' => 'integer', 'default' => 20]
        ],
        'required' => ['url']
    ]);

    // Logs tools
    $tools[] = $tool('logs.tail', 'Tail log file with optional grep filter', [
        'type' => 'object',
        'properties' => [
            'path' => ['type' => 'string', 'description' => 'Log file path'],
            'max_bytes' => ['type' => 'integer', 'default' => 20000],
            'grep' => ['type' => 'string', 'description' => 'Filter pattern']
        ],
        'required' => ['path']
    ]);

    $tools[] = $tool('logs.grep', 'Search logs with pattern', [
        'type' => 'object',
        'properties' => [
            'path' => ['type' => 'string'],
            'pattern' => ['type' => 'string'],
            'max_matches' => ['type' => 'integer', 'default' => 50]
        ],
        'required' => ['path', 'pattern']
    ]);

    // Memory tools
    $tools[] = $tool('memory.retrieve', 'Retrieve conversation memory', [
        'type' => 'object',
        'properties' => [
            'session_key' => ['type' => 'string'],
            'limit' => ['type' => 'integer', 'default' => 10]
        ],
        'required' => ['session_key']
    ]);

    $tools[] = $tool('memory.upsert', 'Store or update memory', [
        'type' => 'object',
        'properties' => [
            'session_key' => ['type' => 'string'],
            'key' => ['type' => 'string'],
            'value' => ['type' => 'string'],
            'category' => ['type' => 'string', 'default' => 'general']
        ],
        'required' => ['session_key', 'key', 'value']
    ]);

    // Try to fetch devkit tools if available
    $devkitTools = [];
    $devkitUrl = env('DEVKIT_ENTERPRISE_URL');
    if ($devkitUrl) {
        try {
            $ch = curl_init($devkitUrl . '/tools/list');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $devkitData = json_decode($response, true);
                if (isset($devkitData['tools']) && is_array($devkitData['tools'])) {
                    foreach ($devkitData['tools'] as $devTool) {
                        // Add prefix to avoid conflicts
                        $devTool['name'] = 'devkit.' . $devTool['name'];
                        $devTool['description'] = '[DEVKIT] ' . ($devTool['description'] ?? '');
                        $devkitTools[] = $devTool;
                    }
                }
            }
        } catch (Throwable $e) {
            // Silently fail devkit discovery
        }
    }

    $allTools = array_merge($tools, $devkitTools);

    // Build registry response
    $registry = [
        'name' => 'Ecigdis AI Agent Tools',
        'version' => AI_AGENT_VERSION,
        'protocol_version' => '1.0.0',
        'description' => 'Production AI Agent tool registry with local + devkit tools',
        'tools' => $allTools,
        'capabilities' => [
            'streaming' => true,
            'idempotency' => true,
            'rate_limiting' => true,
            'circuit_breaker' => true,
        ],
        'endpoints' => [
            'registry' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/mcp/registry.php',
            'call' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/mcp/call.php',
            'events' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/mcp/events.php',
            'chat' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/api/chat.php',
            'chat_stream' => 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/api/chat_stream.php',
        ],
        'statistics' => [
            'local_tools' => count($tools),
            'devkit_tools' => count($devkitTools),
            'total_tools' => count($allTools),
        ],
        'timestamp' => date(DATE_ATOM),
        'request_id' => new_request_id(),
    ];

    envelope_success($registry, $registry['request_id']);

} catch (Throwable $e) {
    error_log("Registry error: " . $e->getMessage());
    $reqId = new_request_id();
    envelope_error('INTERNAL_ERROR', 'Failed to build registry', $reqId, [
        'detail' => $e->getMessage()
    ], 500);
}
