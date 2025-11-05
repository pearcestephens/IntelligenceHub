<?php
/**
 * MCP Tools API Endpoint
 *
 * Unified REST API for all 48 tool methods
 * Industry Standard: JSON-RPC 2.0 style with MCP extensions
 *
 * REQUEST FORMAT:
 * {
 *   "tool": "db.query_readonly",
 *   "params": {
 *     "sql": "SELECT * FROM users LIMIT 5"
 *   }
 * }
 *
 * RESPONSE FORMAT (Success):
 * {
 *   "status": 200,
 *   "success": true,
 *   "data": { ... },
 *   "meta": {
 *     "tool": "db.query_readonly",
 *     "execution_time_ms": 5.23,
 *     "timestamp": "2025-11-05T12:34:56Z"
 *   }
 * }
 *
 * RESPONSE FORMAT (Error):
 * {
 *   "status": 400,
 *   "success": false,
 *   "error": {
 *     "message": "Error description",
 *     "code": "INVALID_PARAMS",
 *     "details": { ... }
 *   },
 *   "meta": {
 *     "tool": "db.query_readonly",
 *     "execution_time_ms": 0.12,
 *     "timestamp": "2025-11-05T12:34:56Z"
 *   }
 * }
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$startTime = microtime(true);

// Load MCP tools
require_once __DIR__ . '/../bootstrap_tools.php';

// Parse request
$rawInput = file_get_contents('php://input');
$request = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'status' => 400,
        'success' => false,
        'error' => [
            'message' => 'Invalid JSON in request body',
            'code' => 'INVALID_JSON',
            'details' => ['json_error' => json_last_error_msg()]
        ],
        'meta' => [
            'tool' => null,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Validate request structure
if (!isset($request['tool']) || !is_string($request['tool'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 400,
        'success' => false,
        'error' => [
            'message' => 'Missing or invalid "tool" parameter',
            'code' => 'MISSING_TOOL',
            'details' => ['required' => 'tool parameter must be a string (e.g., "db.query_readonly")']
        ],
        'meta' => [
            'tool' => null,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

$tool = $request['tool'];
$params = $request['params'] ?? [];

// Execute tool via registry
try {
    $result = $registry->execute($tool, $params);

    $executionTime = round((microtime(true) - $startTime) * 1000, 2);

    // Normalize response format
    $response = [
        'status' => $result['status'] ?? 200,
        'success' => ($result['status'] ?? 200) >= 200 && ($result['status'] ?? 200) < 300,
        'data' => $result['data'] ?? null,
        'meta' => [
            'tool' => $tool,
            'execution_time_ms' => $executionTime,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ];

    // If error, restructure
    if (!$response['success']) {
        $response['error'] = [
            'message' => $result['data']['error'] ?? 'Unknown error',
            'code' => $result['data']['code'] ?? 'EXECUTION_ERROR',
            'details' => $result['data'] ?? []
        ];
        unset($response['data']);
    }

    http_response_code($response['status']);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'code' => 'INTERNAL_ERROR',
            'details' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 3) // First 3 stack frames
            ]
        ],
        'meta' => [
            'tool' => $tool,
            'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z')
        ]
    ], JSON_PRETTY_PRINT);
}
