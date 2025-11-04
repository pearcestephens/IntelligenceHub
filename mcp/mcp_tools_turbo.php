<?php
declare(strict_types=1);

/**
 * File: mcp_tools_turbo.php
 * Purpose: Define IntelligenceHub MCP tool catalog and handlers for chat, knowledge, GitHub, DB, FS, SSH, and ops integrations.
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 * Dependencies: Agent HTTP APIs, cURL extension, PDO (MySQL), environment configuration.
 */

// phpcs:disable PSR1.Functions.CamelCapsMethodName.NotCamelCaps

/**
 * Build a successful tool response envelope.
 *
 * @param array<mixed>|string|int|float|bool|null $data
 * @param int $status
 * @return array<string, mixed>
 */
function ok($data, int $status = 200): array
{
    return [
        'status' => $status,
        'data' => $data,
        'trace_id' => 'mcp_' . date('c') . '_' . bin2hex(random_bytes(4)),
    ];
}

/**
 * Build a failure tool response envelope.
 *
 * @param string $message
 * @param int $status
 * @param array<string, mixed> $extra
 * @return array<string, mixed>
 */
function fail(string $message, int $status = 500, array $extra = []): array
{
    return [
        'status' => $status,
        'data' => ['error' => $message] + $extra,
        'trace_id' => 'mcp_' . date('c') . '_' . bin2hex(random_bytes(4)),
    ];
}

/**
 * Fetch an environment variable with default fallback.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function envv(string $key, string $default = ''): string
{
    // Check $_ENV first (most reliable), then $_SERVER, then getenv() (may be disabled)
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? (@getenv($key) ?: $default);
    return is_string($value) ? $value : $default;
}

/**
 * Enforce API key authentication (used by server_v3.php)
 *
 * @param string $providedKey
 * @throws UnauthorizedException
 * @return void
 */
function enforce_api_key(string $providedKey): void
{
    $expectedKey = envv('MCP_API_KEY', 'bFUdRjh4Jx');

    if ($providedKey === '') {
        throw new UnauthorizedException('API key required');
    }

    if ($providedKey !== $expectedKey) {
        throw new UnauthorizedException('Invalid API key');
    }
}

/**
 * Execute an HTTP JSON request with optional payload.
 *
 * @param string $method
 * @param string $url
 * @param array<mixed>|null $payload
 * @param array<int, string> $headers
 * @param int $timeout
 * @return array{0: mixed, 1: int, 2: string|null}
 */
function http_json(string $method, string $url, ?array $payload = null, array $headers = [], int $timeout = 45): array
{
    $ch = curl_init();
    $finalHeaders = array_merge(['Accept: application/json'], $headers);
    if ($payload !== null) {
        $finalHeaders[] = 'Content-Type: application/json';
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $finalHeaders,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
    ]);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err !== '') {
        return [null, 0, $err];
    }

    if ($code < 200 || $code >= 300) {
        return [null, $code, $body];
    }

    $json = json_decode((string) $body, true);
    return [$json, $code, null];
}

/**
 * Build a URL for the downstream agent service.
 *
 * @param string $path
 * @return string
 */
function agent_url(string $path): string
{
    // If already a full URL (starts with http:// or https://), return as-is
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $base = rtrim(envv('AGENT_BASE', 'https://gpt.ecigdis.co.nz/ai-agent'), '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Determine the jailed filesystem root for FS tools.
 *
 * @return string
 */
function jail_root(): string
{
    $root = rtrim(envv('TOOL_FS_ROOT', sys_get_temp_dir() . '/agent_sandbox'), '/');
    if (!is_dir($root)) {
        @mkdir($root, 0755, true);
    }

    $resolved = realpath($root);
    return $resolved !== false ? $resolved : $root;
}

/**
 * Resolve a user-supplied path inside the jail.
 *
 * @param string $path
 * @return string
 */
function jail_path(string $path): string
{
    $safePath = str_replace(['..\\', '../'], '', str_replace('\\', '/', $path));
    $fullPath = rtrim(jail_root(), '/') . '/' . ltrim($safePath, '/');
    $realPath = realpath($fullPath);
    if ($realPath === false) {
        return $fullPath;
    }

    $jailRoot = realpath(jail_root());
    if ($jailRoot !== false && strpos($realPath, $jailRoot) === 0) {
        return $realPath;
    }

    throw new RuntimeException('Path escapes jail');
}

/**
 * Return a truncated JSON representation if payload is large.
 *
 * @param mixed $obj
 * @param int $max
 * @return mixed
 */
function json_slim($obj, int $max = 2000)
{
    $encoded = json_encode($obj);
    if ($encoded === false) {
        return $obj;
    }
    if (strlen($encoded) <= $max) {
        return $obj;
    }

    return ['_truncated' => true, 'size' => strlen($encoded)];
}

/**
 * Perform a raw HTTP request and return the string body.
 *
 * @param string $method
 * @param string $url
 * @param array<mixed>|null $payload
 * @param array<int, string> $headers
 * @param int $timeout
 * @return array{0: string|null, 1: int, 2: string|null}
 */
function http_raw(string $method, string $url, $payload, array $headers, int $timeout, ?array &$responseHeaders = null): array {
    $opts = [
        'http' => [
            'method'  => $method,
            'header'  => implode("\r\n", $headers),
            'content' => is_string($payload) ? $payload : json_encode($payload),
            'timeout' => $timeout,
            'ignore_errors' => true,
        ]
    ];
    $ctx = stream_context_create($opts);
    $body = @file_get_contents($url, false, $ctx);
    $status = 0;
    $respHeaders = [];
    foreach ($http_response_header ?? [] as $line) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $line, $m)) { $status = (int)$m[1]; }
        else {
            [$k,$v] = array_map('trim', explode(':', $line, 2) + [1=>'']);
            if ($k) $respHeaders[strtolower($k)] = $v;
        }
    }
    $responseHeaders = $respHeaders;
    return [$body, $status ?: 200, $body === false ? 'fetch_failed' : null];
}


/**
 * Build GitHub API headers with optional overrides.
 *
 * @param array<int, string> $extra
 * @return array<int, string>|null
 */
function github_headers(array $extra = []): ?array
{
    $token = envv('GITHUB_TOKEN', '');
    if ($token === '') {
        return null;
    }
    $base = [
        'Authorization: Bearer ' . $token,
        'User-Agent: ecigdis-mcp',
        'Accept: application/vnd.github+json',
    ];

    if (!empty($extra)) {
        // Remove Accept header if override provided.
        $hasAcceptOverride = false;
        foreach ($extra as $header) {
            if (stripos($header, 'Accept:') === 0) {
                $hasAcceptOverride = true;
                break;
            }
        }
        if ($hasAcceptOverride) {
            $base = array_filter($base, static function (string $header): bool {
                return stripos($header, 'Accept:') !== 0;
            });
        }
        $base = array_merge($base, $extra);
    }

    return $base;
}

/**
 * Tail helper for logs.
 *
 * @param string $path
 * @param int $lines
 * @return string
 */
function tail_file(string $path, int $lines): string
{
    if ($lines <= 0) {
        return '';
    }

    $buffer = '';
    $position = -1;
    $lineCount = 0;
    $fh = fopen($path, 'rb');
    if ($fh === false) {
        return '';
    }

    $chunk = '';
    while (-$position <= filesize($path) && $lineCount < ($lines + 1)) {
        fseek($fh, $position, SEEK_END);
        $chunk = fgetc($fh);
        if ($chunk === false) {
            break;
        }
        $buffer = $chunk . $buffer;
        if ($chunk === "\n") {
            $lineCount++;
        }
        $position--;
    }
    fclose($fh);

    $buffer = explode("\n", $buffer);
    $buffer = array_slice($buffer, -$lines);

    return implode("\n", $buffer);
}

// ---------------------------- tool specs ----------------------------

/**
 * @var array<int, array<string, mixed>>
 */
$TOOLS = [
    [
        'name' => 'chat.send',
        'description' => 'Send a message to IntelligenceHub and return assistant reply',
        'category' => 'chat',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100000],
                'conversation_id' => ['type' => 'string'],
                'stream' => ['type' => 'boolean'],
            ],
            'required' => ['message'],
        ],
        'safety' => ['timeout' => 45, 'rate_limit' => 30],
    ],
    [
        'name' => 'chat.summarize',
        'description' => 'Return latest or forced summary for a conversation (via Summarizer)',
        'category' => 'chat',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'conversation_id' => ['type' => 'string', 'minLength' => 5],
                'regenerate' => ['type' => 'boolean'],
            ],
            'required' => ['conversation_id'],
        ],
        'safety' => ['timeout' => 45, 'rate_limit' => 10],
    ],
    [
        'name' => 'chat.send_stream',
        'description' => 'Send a streaming chat request to IntelligenceHub and return chunked payload',
        'category' => 'chat',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'message' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 100000],
                'conversation_id' => ['type' => 'string'],
            ],
            'required' => ['message'],
        ],
        'safety' => ['timeout' => 60, 'rate_limit' => 15],
    ],
    [
        'name' => 'knowledge.search',
        'description' => 'Search knowledge base for relevant chunks',
        'category' => 'knowledge',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 1000],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20],
            ],
            'required' => ['query'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 60],
    ],
    [
        'name' => 'knowledge.get_document',
        'description' => 'Get KB document by id',
        'category' => 'knowledge',
        'parameters' => [
            'type' => 'object',
            'properties' => ['document_id' => ['type' => 'string', 'minLength' => 6]],
            'required' => ['document_id'],
        ],
        'safety' => ['timeout' => 20, 'rate_limit' => 120],
    ],
    [
        'name' => 'knowledge.list_documents',
        'description' => 'List KB documents (paged)',
        'category' => 'knowledge',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'page' => ['type' => 'integer', 'minimum' => 1],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
            ],
            'required' => [],
        ],
        'safety' => ['timeout' => 20, 'rate_limit' => 60],
    ],
    [
        'name' => 'github.get_pr_info',
        'description' => 'Get PR metadata (title, state, base/head, description) for a GitHub PR',
        'category' => 'github',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'repo' => ['type' => 'string', 'minLength' => 3],
                'pr_number' => ['type' => 'integer', 'minimum' => 1],
            ],
            'required' => ['repo', 'pr_number'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 60],
    ],
    [
        'name' => 'github.search_repos',
        'description' => 'Search installed repos by keyword',
        'category' => 'github',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'query' => ['type' => 'string', 'minLength' => 2],
                'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50],
            ],
            'required' => ['query'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 60],
    ],
    [
        'name' => 'db.query_readonly',
        'description' => 'Execute a read-only SQL query (SELECT/SHOW/DESCRIBE/EXPLAIN) on the app DB',
        'category' => 'db',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'sql' => ['type' => 'string', 'minLength' => 6],
                'params' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['sql'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 20],
    ],
    [
        'name' => 'db.stats',
        'description' => 'Basic DB stats (table count, size, top 10 largest tables)',
        'category' => 'db',
        'parameters' => ['type' => 'object', 'properties' => [], 'required' => []],
        'safety' => ['timeout' => 20, 'rate_limit' => 20],
    ],
    [
        'name' => 'db.explain',
        'description' => 'Run EXPLAIN FORMAT=JSON for query diagnostics',
        'category' => 'db',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'sql' => ['type' => 'string', 'minLength' => 6],
            ],
            'required' => ['sql'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 10],
    ],
    [
        'name' => 'fs.list',
        'description' => 'List files/dirs in jailed FS root',
        'category' => 'fs',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string', 'minLength' => 1],
                'recursive' => ['type' => 'boolean'],
            ],
            'required' => ['path'],
        ],
        'safety' => ['timeout' => 10, 'rate_limit' => 60],
    ],
    [
        'name' => 'fs.read',
        'description' => 'Read a small text file from jail (max 200KB)',
        'category' => 'fs',
        'parameters' => [
            'type' => 'object',
            'properties' => ['path' => ['type' => 'string', 'minLength' => 1]],
            'required' => ['path'],
        ],
        'safety' => ['timeout' => 10, 'rate_limit' => 60],
    ],
    [
        'name' => 'fs.write',
        'description' => 'Write a UTF-8 text file inside the jail (max 200KB)',
        'category' => 'fs',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string', 'minLength' => 1],
                'content' => ['type' => 'string', 'minLength' => 0, 'maxLength' => 200000],
                'mode' => ['type' => 'string', 'enum' => ['overwrite', 'append']],
            ],
            'required' => ['path', 'content'],
        ],
        'safety' => ['timeout' => 10, 'rate_limit' => 30],
    ],
    [
        'name' => 'fs.info',
        'description' => 'Get file metadata (stat, mime, preview) inside jail',
        'category' => 'fs',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string', 'minLength' => 1],
                'preview_bytes' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 10000],
            ],
            'required' => ['path'],
        ],
        'safety' => ['timeout' => 10, 'rate_limit' => 60],
    ],
    [
        'name' => 'ssh.exec_allowlist',
        'description' => 'Execute an allow-listed SSH command on a configured host',
        'category' => 'ssh',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'command' => ['type' => 'string', 'minLength' => 2],
            ],
            'required' => ['command'],
        ],
        'safety' => ['timeout' => 45, 'rate_limit' => 10],
    ],
    [
        'name' => 'system.health',
        'description' => 'Health summary via agent API',
        'category' => 'system',
        'parameters' => ['type' => 'object', 'properties' => [], 'required' => []],
        'safety' => ['timeout' => 15, 'rate_limit' => 120],
    ],
    [
        'name' => 'ops.ready_check',
        'description' => 'Environment readiness snapshot',
        'category' => 'ops',
        'parameters' => ['type' => 'object', 'properties' => [], 'required' => []],
        'safety' => ['timeout' => 30, 'rate_limit' => 10],
    ],
    [
        'name' => 'ops.security_scan',
        'description' => 'Trigger security scan via ops endpoint',
        'category' => 'ops',
        'parameters' => ['type' => 'object', 'properties' => [], 'required' => []],
        'safety' => ['timeout' => 60, 'rate_limit' => 5],
    ],
    [
        'name' => 'ops.performance_test',
        'description' => 'Trigger performance test via ops endpoint',
        'category' => 'ops',
        'parameters' => ['type' => 'object', 'properties' => [], 'required' => []],
        'safety' => ['timeout' => 120, 'rate_limit' => 5],
    ],
    [
        'name' => 'logs.tail',
        'description' => 'Tail the last N lines of an allowed log file',
        'category' => 'ops',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'log' => ['type' => 'string', 'minLength' => 1],
                'lines' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2000],
            ],
            'required' => ['log'],
        ],
        'safety' => ['timeout' => 10, 'rate_limit' => 30],
    ],
    [
        'name' => 'github.comment_pr',
        'description' => 'Post a comment on a GitHub pull request',
        'category' => 'github',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'repo' => ['type' => 'string', 'minLength' => 3],
                'pr_number' => ['type' => 'integer', 'minimum' => 1],
                'body' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 10000],
            ],
            'required' => ['repo', 'pr_number', 'body'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 20],
    ],
    [
        'name' => 'github.label_pr',
        'description' => 'Add labels to a GitHub pull request',
        'category' => 'github',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'repo' => ['type' => 'string', 'minLength' => 3],
                'pr_number' => ['type' => 'integer', 'minimum' => 1],
                'labels' => ['type' => 'array', 'items' => ['type' => 'string', 'minLength' => 1], 'minItems' => 1],
            ],
            'required' => ['repo', 'pr_number', 'labels'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 20],
    ],
    [
        'name' => 'github.get_pr_diff',
        'description' => 'Fetch the diff for a GitHub pull request',
        'category' => 'github',
        'parameters' => [
            'type' => 'object',
            'properties' => [
                'repo' => ['type' => 'string', 'minLength' => 3],
                'pr_number' => ['type' => 'integer', 'minimum' => 1],
                'max_bytes' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 200000],
            ],
            'required' => ['repo', 'pr_number'],
        ],
        'safety' => ['timeout' => 30, 'rate_limit' => 20],
    ],
];

// ---------------------------- handlers ----------------------------

/**
 * @var array<string, callable(array<string, mixed>): array<string, mixed>>
 */
$TOOL_HANDLERS = [
    'chat.send' => function (array $args): array {
        $payload = [
            'message' => $args['message'] ?? '',
            'conversation_id' => $args['conversation_id'] ?? null,
            'stream' => (bool) ($args['stream'] ?? false),
        ];
        [$json, $code, $err] = http_json('POST', agent_url('public/agent/api/chat.php'), $payload, [], 45);
        if ($err !== null) {
            return fail('agent error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('agent http ' . $code, $code, ['raw' => json_slim($json)]);
        }

        $msg = null;
        if (is_array($json)) {
            $msg = $json['message']
                ?? ($json['response'] ?? ($json['data']['message'] ?? null));
        }
        $conv = is_array($json)
            ? ($json['conversation_id'] ?? ($json['data']['conversation_id'] ?? null))
            : null;

        return ok([
            'message' => $msg,
            'conversation_id' => $conv,
            'raw' => json_slim($json),
        ]);
    },

    'chat.summarize' => function (array $args): array {
        $payload = [
            'message' => '[SYSTEM_REQUEST] Summarize the conversation',
            'conversation_id' => $args['conversation_id'] ?? '',
            'stream' => false,
        ];
        [$json, $code, $err] = http_json('POST', agent_url('public/agent/api/chat.php'), $payload, [], 45);
        if ($err !== null) {
            return fail('agent error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('agent http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        $summary = null;
        if (is_array($json)) {
            $summary = $json['summary']
                ?? ($json['message'] ?? ($json['response'] ?? null));
        }
        return ok(['summary' => $summary, 'raw' => json_slim($json)], $code);
    },

    'chat.send_stream' => function (array $args): array {
        $payload = [
            'message' => $args['message'] ?? '',
            'conversation_id' => $args['conversation_id'] ?? null,
            'stream' => true,
        ];
        [$json, $code, $err] = http_json('POST', agent_url('public/agent/api/chat.php'), $payload, [], 60);
        if ($err !== null) {
            return fail('agent error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('agent http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'knowledge.search' => function (array $args): array {
        $limit = isset($args['limit']) ? (int) $args['limit'] : 5;
        $payload = ['query' => $args['query'] ?? '', 'limit' => $limit];
        [$json, $code, $err] = http_json('POST', agent_url('public/agent/api/knowledge.php?action=search'), $payload, [], 30);
        if ($err !== null) {
            return fail('kb error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('kb http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'knowledge.get_document' => function (array $args): array {
        $docId = $args['document_id'] ?? '';
        [$json, $code, $err] = http_json('GET', agent_url('public/agent/api/knowledge.php?action=documents&id=' . rawurlencode($docId)), null, [], 20);
        if ($err !== null) {
            return fail('kb error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('kb http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        $document = null;
        if (is_array($json)) {
            $document = $json['document'] ?? $json;
        }
        return ok($document ?? ['raw' => $json], $code);
    },

    'knowledge.list_documents' => function (array $args): array {
        $page = max(1, (int) ($args['page'] ?? 1));
        $limit = max(1, min(100, (int) ($args['limit'] ?? 20)));
        $url = agent_url('public/agent/api/knowledge.php?action=documents&limit=' . $limit . '&offset=' . (($page - 1) * $limit));
        [$json, $code, $err] = http_json('GET', $url, null, [], 20);
        if ($err !== null) {
            return fail('kb error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('kb http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'github.get_pr_info' => function (array $args): array {
        $repo = $args['repo'] ?? '';
        $num = (int) ($args['pr_number'] ?? 0);
        $token = envv('GITHUB_TOKEN', '');
        if ($token === '') {
            return fail('GITHUB_TOKEN not configured', 401);
        }
        $url = 'https://api.github.com/repos/' . $repo . '/pulls/' . $num;
        $headers = [
            'Authorization: Bearer ' . $token,
            'User-Agent: ecigdis-mcp',
            'Accept: application/vnd.github+json',
        ];
        [$json, $code, $err] = http_json('GET', $url, null, $headers, 30);
        if ($err !== null) {
            return fail('github error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('github http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        if (!is_array($json)) {
            return fail('Unexpected GitHub response format', 502, ['raw' => json_slim($json)]);
        }
        $result = [
            'number' => $json['number'] ?? null,
            'state' => $json['state'] ?? null,
            'title' => $json['title'] ?? null,
            'user' => $json['user']['login'] ?? null,
            'base' => $json['base']['label'] ?? null,
            'head' => $json['head']['label'] ?? null,
            'created_at' => $json['created_at'] ?? null,
            'updated_at' => $json['updated_at'] ?? null,
            'body' => $json['body'] ?? '',
            'mergeable' => $json['mergeable'] ?? null,
        ];
        return ok($result, $code);
    },

    'github.search_repos' => function (array $args): array {
        $query = $args['query'] ?? '';
        $limit = max(1, min(50, (int) ($args['limit'] ?? 10)));
        $token = envv('GITHUB_TOKEN', '');
        if ($token === '') {
            return fail('GITHUB_TOKEN not configured', 401);
        }
        $url = 'https://api.github.com/search/repositories?q=' . rawurlencode($query) . '&per_page=' . $limit;
        $headers = [
            'Authorization: Bearer ' . $token,
            'User-Agent: ecigdis-mcp',
            'Accept: application/vnd.github+json',
        ];
        [$json, $code, $err] = http_json('GET', $url, null, $headers, 30);
        if ($err !== null) {
            return fail('github error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('github http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        $items = [];
        if (is_array($json) && isset($json['items']) && is_array($json['items'])) {
            foreach ($json['items'] as $repoData) {
                $items[] = [
                    'full_name' => $repoData['full_name'] ?? null,
                    'description' => $repoData['description'] ?? null,
                ];
            }
        }
        return ok(['count' => count($items), 'results' => $items], $code);
    },

    'db.query_readonly' => function (array $args): array {
        if (envv('DB_READONLY', '1') !== '1') {
            return fail('DB_READONLY disabled', 403);
        }
        $sql = trim((string) ($args['sql'] ?? ''));
        $upper = strtoupper($sql);
        $allowedVerbs = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'WITH'];
        $isAllowed = false;
        foreach ($allowedVerbs as $verb) {
            if (strpos($upper, $verb) === 0) {
                $isAllowed = true;
                break;
            }
        }
        if (!$isAllowed) {
            return fail('Only read-only queries are allowed', 400);
        }

        $pdo = null;
        try {
            $dsn = 'mysql:host=' . envv('DB_HOST', '127.0.0.1') . ';dbname=' . envv('DB_NAME', 'jcepnzzkmj') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, envv('DB_USER', 'jcepnzzkmj'), envv('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $stmt = $pdo->prepare($sql);
            $params = isset($args['params']) && is_array($args['params']) ? array_values($args['params']) : [];
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            $rowCount = count($rows);
            if ($rowCount > 2000) {
                $rows = array_slice($rows, 0, 2000);
            }
            return ok(['row_count' => $rowCount, 'rows' => $rows]);
        } catch (Throwable $e) {
            return fail('DB error: ' . $e->getMessage(), 500);
        } finally {
            $pdo = null;
        }
    },

    'db.stats' => function (): array {
        if (envv('DB_READONLY', '1') !== '1') {
            return fail('DB_READONLY disabled', 403);
        }
        try {
            $dsn = 'mysql:host=' . envv('DB_HOST', '127.0.0.1') . ';dbname=' . envv('DB_NAME', 'jcepnzzkmj') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, envv('DB_USER', 'jcepnzzkmj'), envv('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $tables = $pdo->query('SELECT COUNT(*) AS t FROM information_schema.tables WHERE table_schema = DATABASE()')->fetch();
            $largest = $pdo->query('SELECT TABLE_NAME AS name, TABLE_ROWS AS rows, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY DATA_LENGTH + INDEX_LENGTH DESC LIMIT 10')->fetchAll();
            return ok([
                'tables' => isset($tables['t']) ? (int) $tables['t'] : 0,
                'top10' => $largest,
            ]);
        } catch (Throwable $e) {
            return fail('DB error: ' . $e->getMessage(), 500);
        }
    },

    'db.explain' => function (array $args): array {
        if (envv('DB_READONLY', '1') !== '1') {
            return fail('DB_READONLY disabled', 403);
        }
        $sql = trim((string)($args['sql'] ?? ''));
        if ($sql === '') {
            return fail('SQL is required', 400);
        }
        $pdo = null;
        try {
            $dsn = 'mysql:host=' . envv('DB_HOST', '127.0.0.1') . ';dbname=' . envv('DB_NAME', 'jcepnzzkmj') . ';charset=utf8mb4';
            $pdo = new PDO($dsn, envv('DB_USER', 'jcepnzzkmj'), envv('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $stmt = $pdo->prepare('EXPLAIN FORMAT=JSON ' . $sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return ok(['explain' => $row]);
        } catch (Throwable $e) {
            return fail('DB error: ' . $e->getMessage(), 500);
        } finally {
            $pdo = null;
        }
    },

    'fs.list' => function (array $args): array {
        $base = jail_root();
        $path = jail_path((string) ($args['path'] ?? '.'));
        if (!is_dir($path)) {
            return fail('Not a directory', 400);
        }
        $entries = [];
        if (!empty($args['recursive'])) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $entry) {
                $entries[] = substr($entry->getPathname(), strlen($base) + 1);
                if (count($entries) > 5000) {
                    break;
                }
            }
        } else {
            $dirIterator = new DirectoryIterator($path);
            foreach ($dirIterator as $entry) {
                if ($entry->isDot()) {
                    continue;
                }
                $entries[] = $entry->getFilename();
            }
        }
        return ok([
            'root' => $base,
            'path' => substr($path, strlen($base) + 1),
            'entries' => $entries,
        ]);
    },

    'fs.read' => function (array $args): array {
        $path = jail_path((string) ($args['path'] ?? ''));
        if (!is_file($path) || !is_readable($path)) {
            return fail('Unreadable file', 400);
        }
        $size = filesize($path) ?: 0;
        if ($size > 200000) {
            return fail('File too large (>200KB)', 400);
        }
        $content = file_get_contents($path) ?: '';
        return ok([
            'path' => $path,
            'size' => $size,
            'content' => $content,
        ]);
    },

    'fs.write' => function (array $args): array {
        $path = jail_path((string)($args['path'] ?? ''));
        $content = (string)($args['content'] ?? '');
        if (strlen($content) > 200000) {
            return fail('Content exceeds 200KB limit', 400);
        }
        $mode = strtolower((string)($args['mode'] ?? 'overwrite'));
        if ($mode !== 'overwrite' && $mode !== 'append') {
            return fail('Unsupported mode', 400);
        }
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return fail('Unable to create directory', 500);
        }
        $bytes = $mode === 'append'
            ? file_put_contents($path, $content, FILE_APPEND)
            : file_put_contents($path, $content);
        if ($bytes === false) {
            return fail('Failed to write file', 500);
        }
        return ok(['path' => $path, 'bytes_written' => $bytes, 'mode' => $mode]);
    },

    'fs.info' => function (array $args): array {
        $path = jail_path((string)($args['path'] ?? ''));
        if (!file_exists($path)) {
            return fail('Path not found', 404);
        }
        $stat = stat($path);
        $isFile = is_file($path);
        $previewBytes = (int)($args['preview_bytes'] ?? 512);
        $previewBytes = max(0, min(10000, $previewBytes));
        $preview = null;
        if ($isFile && $previewBytes > 0) {
            $fh = fopen($path, 'rb');
            if ($fh !== false) {
                $preview = fread($fh, $previewBytes) ?: '';
                fclose($fh);
            }
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $isFile ? ($finfo->file($path) ?: 'application/octet-stream') : null;
        return ok([
            'path' => $path,
            'type' => $isFile ? 'file' : 'dir',
            'size' => $stat['size'] ?? 0,
            'permissions' => substr(sprintf('%o', $stat['mode'] ?? 0), -4),
            'modified_at' => isset($stat['mtime']) ? date(DATE_ATOM, (int)$stat['mtime']) : null,
            'mime' => $mime,
            'preview' => $preview,
        ]);
    },

    'ssh.exec_allowlist' => function (array $args): array {
        if (envv('SSH_ENABLE', '0') !== '1') {
            return fail('SSH disabled (set SSH_ENABLE=1 to enable)', 403);
        }
        $cmd = trim((string) ($args['command'] ?? ''));
    $allowRaw = trim(envv('SSH_ALLOWED_CMDS', ''));
        if ($allowRaw === '') {
            return fail('SSH_ALLOWED_CMDS not set', 400);
        }
        $allowed = array_filter(array_map('trim', explode('|', $allowRaw)));
        $match = false;
        foreach ($allowed as $allowedCommand) {
            if ($cmd === $allowedCommand) {
                $match = true;
                break;
            }
        }
        if (!$match) {
            return fail('Command not allow-listed', 403, ['allowed' => $allowed]);
        }

        $host = envv('SSH_HOST', '');
        $user = envv('SSH_USER', '');
        $keyPath = envv('SSH_KEY_PATH', '');
        if ($host === '' || $user === '' || $keyPath === '') {
            return fail('SSH host/user/key not configured', 400);
        }

        $escapedCmd = escapeshellarg($cmd);
        $ssh = sprintf(
            'ssh -i %s -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null %s@%s -- %s',
            escapeshellarg($keyPath),
            escapeshellarg($user),
            escapeshellarg($host),
            $escapedCmd
        );
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open($ssh, $descriptorSpec, $pipes);
        if (!is_resource($process)) {
            return fail('Failed to open SSH process', 500);
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0) {
            return fail('SSH non-zero exit', 500, ['stderr' => $stderr, 'exit_code' => $exitCode]);
        }
        return ok(['stdout' => $stdout, 'exit_code' => $exitCode]);
    },

    'system.health' => function (): array {
        [$json, $code, $err] = http_json('GET', agent_url('public/api/health.php'), null, [], 15);
        if ($err !== null) {
            return fail('health error: ' . $err, 502);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'ops.ready_check' => function (): array {
        [$json, $code, $err] = http_json('GET', agent_url('public/api/health-check.php'), null, [], 30);
        if ($err !== null) {
            return fail('ready error: ' . $err, 502);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'ops.security_scan' => function (): array {
        [$json, $code, $err] = http_json('POST', agent_url('public/api/ops/security-scan.php'), [], [], 60);
        if ($err !== null) {
            return fail('ops error: ' . $err, 502);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'ops.performance_test' => function (): array {
        [$json, $code, $err] = http_json('POST', agent_url('public/api/ops/performance-test.php'), [], [], 120);
        if ($err !== null) {
            return fail('ops error: ' . $err, 502);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'logs.tail' => function (array $args): array {
        $allowed = array_filter(array_map('trim', explode('|', envv('TOOL_LOGS_ALLOW', ''))));
        $logKey = (string)($args['log'] ?? '');
        if ($logKey === '' || empty($allowed) || !in_array($logKey, $allowed, true)) {
            return fail('Log not allow-listed', 403, ['allowed' => $allowed]);
        }
        $lines = (int)($args['lines'] ?? 200);
        $lines = max(1, min(2000, $lines));
        $logPath = jail_path($logKey);
        if (!is_file($logPath)) {
            return fail('Log file not found', 404);
        }
        $output = tail_file($logPath, $lines);
        return ok([
            'log' => $logKey,
            'lines' => $lines,
            'content' => $output,
        ]);
    },

    'github.comment_pr' => function (array $args): array {
        $repo = $args['repo'] ?? '';
        $num = (int)($args['pr_number'] ?? 0);
        $body = $args['body'] ?? '';
        $headers = github_headers();
        if ($headers === null) {
            return fail('GITHUB_TOKEN not configured', 401);
        }
        $url = 'https://api.github.com/repos/' . $repo . '/issues/' . $num . '/comments';
        [$json, $code, $err] = http_json('POST', $url, ['body' => $body], $headers, 30);
        if ($err !== null) {
            return fail('github error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('github http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'github.label_pr' => function (array $args): array {
        $repo = $args['repo'] ?? '';
        $num = (int)($args['pr_number'] ?? 0);
        $labels = $args['labels'] ?? [];
        if (!is_array($labels) || empty($labels)) {
            return fail('Labels required', 400);
        }
        $headers = github_headers();
        if ($headers === null) {
            return fail('GITHUB_TOKEN not configured', 401);
        }
        $url = 'https://api.github.com/repos/' . $repo . '/issues/' . $num . '/labels';
        [$json, $code, $err] = http_json('POST', $url, ['labels' => array_values($labels)], $headers, 30);
        if ($err !== null) {
            return fail('github error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('github http ' . $code, $code, ['raw' => json_slim($json)]);
        }
        return ok(is_array($json) ? $json : ['raw' => $json], $code);
    },

    'github.get_pr_diff' => function (array $args): array {
        $repo = $args['repo'] ?? '';
        $num = (int)($args['pr_number'] ?? 0);
        $maxBytes = (int)($args['max_bytes'] ?? 50000);
        $maxBytes = max(1000, min(200000, $maxBytes));
        $headers = github_headers(['Accept: application/vnd.github.diff']);
        if ($headers === null) {
            return fail('GITHUB_TOKEN not configured', 401);
        }
        $url = 'https://api.github.com/repos/' . $repo . '/pulls/' . $num;
        [$body, $code, $err] = http_raw('GET', $url, null, $headers, 30);
        if ($err !== null) {
            return fail('github error: ' . $err, 502);
        }
        if ($code < 200 || $code >= 300) {
            return fail('github http ' . $code, $code, ['raw' => json_slim($body)]);
        }
        $diff = (string)($body ?? '');
        $truncated = false;
        if (strlen($diff) > $maxBytes) {
            $diff = substr($diff, 0, $maxBytes);
            $truncated = true;
        }
        return ok([
            'diff' => $diff,
            'truncated' => $truncated,
        ], $code);
    },
];

// phpcs:enable PSR1.Functions.CamelCapsMethodName.NotCamelCaps
