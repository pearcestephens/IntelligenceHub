<?php
/**
 * MCP Intelligence Hub - Central Dispatcher
 *
 * Single entry point for all MCP tool requests.
 * Routes requests to appropriate handlers based on 'tool' parameter.
 *
 * @package MCP\Dispatcher
 * @version 1.0.0
 */

declare(strict_types=1);

// Load bootstrap (includes autoloader and environment)
require_once __DIR__ . '/bootstrap.php';

use IntelligenceHub\MCP\Tools\SemanticSearchTool;
use IntelligenceHub\MCP\Tools\HealthCheckTool;
use IntelligenceHub\MCP\Tools\SystemStatsTool;
use IntelligenceHub\MCP\Tools\MySQLQueryTool;
use IntelligenceHub\MCP\Tools\PasswordStorageTool;
use IntelligenceHub\MCP\Tools\WebBrowserTool;
use MCP\Tools\CrawlerTool;
use MCP\Tools\DatabaseTool;
use MCP\Tools\RedisTool;
use MCP\Tools\FileTool;
use MCP\Tools\LogsTool;

/**
 * Send JSON response
 */
function sendResponse(bool $success, $data = null, string $message = '', int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data,
        'message' => $message,
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Get request parameters (supports both GET and POST)
 */
function getRequestParams(): array
{
    $params = [];

    // GET parameters
    if (!empty($_GET)) {
        $params = array_merge($params, $_GET);
    }

    // POST parameters
    if (!empty($_POST)) {
        $params = array_merge($params, $_POST);
    }

    // JSON body
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $jsonData = json_decode($json, true);
        if (is_array($jsonData)) {
            $params = array_merge($params, $jsonData);
        }
    }

    return $params;
}

/**
 * Log request for debugging
 */
function logRequest(string $tool, array $params, float $startTime): void
{
    $duration = round((microtime(true) - $startTime) * 1000, 2);
    $logFile = __DIR__ . '/logs/dispatcher.log';

    $logEntry = sprintf(
        "[%s] Tool: %s | Params: %s | Duration: %sms | IP: %s\n",
        date('Y-m-d H:i:s'),
        $tool,
        json_encode($params),
        $duration,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    );

    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// ============================================================================
// MAIN DISPATCHER LOGIC
// ============================================================================

try {
    $startTime = microtime(true);

    // Handle OPTIONS request (CORS preflight)
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        exit;
    }

    // Get parameters
    $params = getRequestParams();
    $tool = $params['tool'] ?? null;

    // Validate tool parameter
    if (empty($tool)) {
        sendResponse(false, null, 'Missing required parameter: tool. Available tools: search, health, stats, analytics, fuzzy, mysql, password, browser, crawler, database, redis, file, logs', 400);
    }

    // Route to appropriate handler
    switch ($tool) {

        // =====================================================================
        // SEARCH TOOL (Enhanced with Fuzzy + Analytics)
        // =====================================================================
        case 'search':
            $query = $params['query'] ?? '';
            $limit = isset($params['limit']) ? (int)$params['limit'] : 10;
            $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

            if (empty($query)) {
                sendResponse(false, null, 'Missing required parameter: query', 400);
            }

            $searchTool = new SemanticSearchTool();
            $result = $searchTool->execute([
                'query' => $query,
                'limit' => $limit,
                'offset' => $offset,
            ]);

            logRequest('search', ['query' => $query, 'limit' => $limit], $startTime);

            if ($result['success']) {
                sendResponse(true, $result, 'Search completed successfully');
            } else {
                sendResponse(false, null, $result['error'] ?? 'Search failed', 500);
            }
            break;

        // =====================================================================
        // ANALYTICS TOOL (Real-time Metrics)
        // =====================================================================
        case 'analytics':
            $timeframe = $params['timeframe'] ?? '24h';

            // Validate timeframe
            $validTimeframes = ['1h', '6h', '24h', '7d', '30d'];
            if (!in_array($timeframe, $validTimeframes)) {
                sendResponse(false, null, 'Invalid timeframe. Valid options: ' . implode(', ', $validTimeframes), 400);
            }

            $searchTool = new SemanticSearchTool();
            $analyticsData = $searchTool->getAnalytics(['timeframe' => $timeframe]);

            logRequest('analytics', ['timeframe' => $timeframe], $startTime);

            if ($analyticsData['success']) {
                sendResponse(true, $analyticsData, 'Analytics retrieved successfully');
            } else {
                sendResponse(false, null, 'Failed to retrieve analytics', 500);
            }
            break;

        // =====================================================================
        // HEALTH CHECK TOOL
        // =====================================================================
        case 'health':
            $healthTool = new HealthCheckTool();
            $result = $healthTool->execute([]);

            logRequest('health', [], $startTime);

            if ($result['success']) {
                $httpCode = $result['data']['status'] === 'healthy' ? 200 : 503;
                sendResponse(true, $result['data'], 'Health check completed', $httpCode);
            } else {
                sendResponse(false, null, 'Health check failed', 500);
            }
            break;

        // =====================================================================
        // SYSTEM STATS TOOL
        // =====================================================================
        case 'stats':
            $statsTool = new SystemStatsTool();
            $result = $statsTool->execute([]);

            logRequest('stats', [], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'System stats retrieved successfully');
            } else {
                sendResponse(false, null, 'Failed to retrieve system stats', 500);
            }
            break;

        // =====================================================================
        // FUZZY SEARCH TEST (Direct access for testing)
        // =====================================================================
        case 'fuzzy':
            $query = $params['query'] ?? '';

            if (empty($query)) {
                sendResponse(false, null, 'Missing required parameter: query', 400);
            }

            require_once __DIR__ . '/src/Search/FuzzySearchEngine.php';
            $fuzzy = new IntelligenceHub\MCP\Search\FuzzySearchEngine([
                'max_distance' => 2,
                'enable_phonetic' => true,
                'enable_suggestions' => true,
            ]);

            // correctTypos() returns ['corrected' => string, 'changes' => array]
            $correctionResult = $fuzzy->correctTypos($query);
            $corrected = $correctionResult['corrected'];

            // For suggestions, we'll return the changes made
            $suggestions = $correctionResult['changes'] ?? [];

            logRequest('fuzzy', ['query' => $query], $startTime);

            sendResponse(true, [
                'original_query' => $query,
                'corrected_query' => $corrected,
                'correction_applied' => ($corrected !== $query),
                'suggestions' => $suggestions,
                'changes' => $correctionResult['changes'] ?? [],
            ], 'Fuzzy search test completed');
            break;

        // =====================================================================
        // MYSQL QUERY TOOL (Read-only database queries)
        // =====================================================================
        case 'mysql':
            $mysqlTool = new MySQLQueryTool();
            $result = $mysqlTool->execute($params);

            logRequest('mysql', ['query_length' => strlen($params['query'] ?? '')], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Query executed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // PASSWORD STORAGE TOOL (Encrypted credential management)
        // =====================================================================
        case 'password':
            $passwordTool = new PasswordStorageTool();
            $result = $passwordTool->execute($params);

            logRequest('password', ['action' => $params['action'] ?? 'unknown'], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Password operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // WEB BROWSER TOOL (Fetch and parse web pages)
        // =====================================================================
        case 'browser':
            $browserTool = new WebBrowserTool();
            $result = $browserTool->execute($params);

            logRequest('browser', ['action' => $params['action'] ?? 'fetch', 'url' => $params['url'] ?? ''], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Browser operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // CRAWLER TOOL (Comprehensive web testing - wraps deep-crawler.js)
        // =====================================================================
        case 'crawler':
            $crawlerTool = new CrawlerTool();
            $result = $crawlerTool->execute($params);

            logRequest('crawler', ['mode' => $params['mode'] ?? 'quick', 'url' => $params['url'] ?? ''], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], $result['message'] ?? 'Crawler completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // DATABASE TOOL (Advanced query builder & analyzer)
        // =====================================================================
        case 'database':
            $databaseTool = new DatabaseTool();
            $result = $databaseTool->execute($params);

            logRequest('database', ['action' => $params['action'] ?? 'unknown'], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Database operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // REDIS TOOL (Cache management & analysis)
        // =====================================================================
        case 'redis':
            $redisTool = new RedisTool();
            $result = $redisTool->execute($params);

            logRequest('redis', ['action' => $params['action'] ?? 'unknown'], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Redis operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // FILE TOOL (Safe file operations & analysis)
        // =====================================================================
        case 'file':
            $fileTool = new FileTool();
            $result = $fileTool->execute($params);

            logRequest('file', ['action' => $params['action'] ?? 'unknown'], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'File operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // LOGS TOOL (Log analysis & parsing)
        // =====================================================================
        case 'logs':
            $logsTool = new LogsTool();
            $result = $logsTool->execute($params);

            logRequest('logs', ['action' => $params['action'] ?? 'tail', 'log_file' => $params['log_file'] ?? 'php'], $startTime);

            if ($result['success']) {
                sendResponse(true, $result['data'], 'Logs operation completed successfully');
            } else {
                sendResponse(false, null, $result['error'], 400);
            }
            break;

        // =====================================================================
        // UNKNOWN TOOL
        // =====================================================================
        default:
            logRequest($tool, $params, $startTime);
            sendResponse(
                false,
                null,
                "Unknown tool: {$tool}. Available tools: search, analytics, health, stats, fuzzy, mysql, password, browser, crawler, database, redis, file, logs",
                404
            );
            break;
    }

} catch (\Exception $e) {
    // Log error
    $errorLog = sprintf(
        "[%s] ERROR: %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    @file_put_contents(__DIR__ . '/logs/dispatcher-error.log', $errorLog, FILE_APPEND);

    // Send error response
    sendResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
}
