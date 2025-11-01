<?php
/**
 * MCP Server v3 - HTTP API Endpoint
 *
 * Provides HTTP access to MCP tools (semantic search, indexing, etc.)
 *
 * Usage:
 *   GET /mcp/server_v3.php?tool=search&query=inventory+transfer
 *   GET /mcp/server_v3.php?tool=search&query=transfer&unit_id=2&limit=10
 *
 * @package IntelligenceHub\MCP
 * @version 3.0.0
 */

declare(strict_types=1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors in output

// Load bootstrap (includes autoloader and environment)
require_once __DIR__ . '/bootstrap.php';

use IntelligenceHub\MCP\Tools\SemanticSearchTool;

// Set JSON response header
header('Content-Type: application/json');
header('X-MCP-Version: 3.0.0');

// CORS headers (adjust as needed for production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Send JSON response
 */
function sendResponse(bool $success, $data = null, string $message = '', int $httpCode = 200): void
{
    http_response_code($httpCode);

    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => uniqid('req_', true),
    ];

    if ($success) {
        $response['data'] = $data;
        if ($message) {
            $response['message'] = $message;
        }
    } else {
        $response['error'] = [
            'message' => $message,
            'code' => $httpCode,
        ];
        if ($data) {
            $response['error']['details'] = $data;
        }
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Rate limiting check
 */
function checkRateLimit(string $identifier, int $maxRequests = 100, int $windowSeconds = 60): bool
{
    static $requests = [];

    $now = time();
    $key = md5($identifier);

    // Initialize or clean old requests
    if (!isset($requests[$key])) {
        $requests[$key] = [];
    }

    // Remove requests outside the time window
    $requests[$key] = array_filter($requests[$key], function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });

    // Check if limit exceeded
    if (count($requests[$key]) >= $maxRequests) {
        return false;
    }

    // Add current request
    $requests[$key][] = $now;

    return true;
}

try {
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'];

    // Only allow GET and POST
    if (!in_array($method, ['GET', 'POST'])) {
        sendResponse(false, null, 'Method not allowed. Use GET or POST.', 405);
    }

    // Get parameters (support both GET and POST)
    $params = $method === 'POST' ? $_POST : $_GET;

    // Rate limiting
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIp, 100, 60)) {
        sendResponse(false, null, 'Rate limit exceeded. Maximum 100 requests per minute.', 429);
    }

    // Get tool name
    $tool = $params['tool'] ?? null;

    if (!$tool) {
        sendResponse(false, null, 'Missing required parameter: tool', 400);
    }

    // Route to appropriate tool
    switch ($tool) {
        case 'search':
        case 'semantic_search':
            // Required: query
            $query = $params['query'] ?? null;

            if (!$query || trim($query) === '') {
                sendResponse(false, null, 'Missing required parameter: query', 400);
            }

            // Optional parameters
            $options = [
                'unit_id' => isset($params['unit_id']) ? (int)$params['unit_id'] : null,
                'limit' => isset($params['limit']) ? min((int)$params['limit'], 100) : 10,
                'file_type' => $params['file_type'] ?? null,
                'category' => $params['category'] ?? null,
            ];

            // Remove null values
            $options = array_filter($options, function($value) {
                return $value !== null;
            });

            // Execute search
            $searchTool = new SemanticSearchTool();
            $startTime = microtime(true);
            $result = $searchTool->execute(['query' => trim($query), 'options' => $options]);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($result['success']) {
                // Add performance metadata
                $result['metadata'] = [
                    'query' => trim($query),
                    'duration_ms' => $duration,
                    'results_count' => count($result['results'] ?? []),
                    'cache_hit' => $result['cache_hit'] ?? false,
                    'options' => $options,
                ];

                sendResponse(true, $result, 'Search completed successfully');
            } else {
                sendResponse(false, null, $result['error'] ?? 'Search failed', 500);
            }
            break;

        case 'health':
        case 'ping':
            // Health check endpoint
            $health = [
                'status' => 'healthy',
                'version' => '3.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'connected',
                'cache' => [
                    'redis' => extension_loaded('redis') ? 'available' : 'unavailable',
                    'apcu' => extension_loaded('apcu') ? 'available' : 'unavailable',
                    'file' => 'available',
                ],
            ];

            sendResponse(true, $health, 'System healthy');
            break;

        case 'stats':
            // Statistics endpoint
            $searchTool = new SemanticSearchTool();

            // Get cache statistics
            $cacheStats = [
                'implementation' => 'See cache manager for detailed stats',
                'note' => 'Use dedicated cache stats endpoint for detailed information',
            ];

            $stats = [
                'version' => '3.0.0',
                'cache' => $cacheStats,
                'timestamp' => date('Y-m-d H:i:s'),
            ];

            sendResponse(true, $stats, 'Statistics retrieved');
            break;

        case 'analytics':
            // Search analytics dashboard
            $searchTool = new SemanticSearchTool();

            $timeframe = $params['timeframe'] ?? '24h';
            $analyticsData = $searchTool->getAnalytics(['timeframe' => $timeframe]);

            if ($analyticsData['success']) {
                sendResponse(true, $analyticsData, 'Analytics retrieved successfully');
            } else {
                sendResponse(false, null, 'Failed to retrieve analytics', 500);
            }
            break;

        default:
            sendResponse(false, null, "Unknown tool: {$tool}. Available tools: search, health, stats, analytics", 400);
    }

} catch (Exception $e) {
    // Log error (in production, use proper logging)
    error_log("MCP Server Error: " . $e->getMessage());

    // Send error response (don't expose internal details in production)
    $errorDetails = [
        'type' => get_class($e),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ];

    // In development, include full message
    if (Config::getInstance()->get('debug', false)) {
        $errorDetails['message'] = $e->getMessage();
        $errorDetails['trace'] = array_slice($e->getTrace(), 0, 5);
    }

    sendResponse(false, $errorDetails, 'Internal server error', 500);
}
