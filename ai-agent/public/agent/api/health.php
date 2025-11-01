<?php

declare(strict_types=1);

/**
 * Health API Endpoint - System health monitoring and metrics
 * 
 * Provides health monitoring endpoints:
 * - GET /health: Overall system health status
 * - GET /metrics: Performance metrics and statistics
 * - GET /status: Detailed component status
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Errors;
use App\Util\SecurityHeaders;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
SecurityHeaders::applyJson();

// Handle preflight OPTIONS request
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests
if ($requestMethod !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Parse URL path for endpoint
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/health';
    $pathParts = explode('/', trim($requestUri, '/'));
    $endpoint = 'health'; // Default endpoint
    
    // Extract endpoint from path
    $apiIndex = array_search('health.php', $pathParts);
    if ($apiIndex !== false && isset($pathParts[$apiIndex + 1])) {
        $endpoint = $pathParts[$apiIndex + 1];
    }
    
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    
    $logger->info('Health API request', [
        'endpoint' => $endpoint,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    // Optional auth for sensitive endpoints
    $requiresAuth = in_array($endpoint, ['metrics','status'], true);
    if ($requiresAuth && php_sapi_name() !== 'cli') {
        $token = Config::get('HEALTH_API_TOKEN', '');
        $provided = $_SERVER['HTTP_X_HEALTH_TOKEN'] ?? '';
        if ($token !== '' && !hash_equals($token, $provided)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'forbidden'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    switch ($endpoint) {
        case 'health':
            // Basic health check with initialization to verify components
            $agent = new Agent($config, $logger);
            $agent->initialize();
            $health = $agent->getHealth();
            
            // Return appropriate HTTP status code based on health
            $statusCode = 200;
            if ($health['status'] === 'unhealthy') {
                $statusCode = 503; // Service Unavailable
            } elseif ($health['status'] === 'degraded') {
                $statusCode = 206; // Partial Content
            }
            
            http_response_code($statusCode);
            echo json_encode($health, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            break;

        case 'ready':
            // Readiness probe: fast checks for DB and Redis connectivity
            $agent = new Agent($config, $logger);
            $agent->initialize();
            $ok = true;
            $details = [
                'db' => $agent->getHealth()['components']['database']['status'] ?? 'unknown',
                'redis' => $agent->getHealth()['components']['redis']['status'] ?? 'unknown',
                'openai_key' => !empty($config->get('OPENAI_API_KEY')) ? 'configured' : 'missing'
            ];
            $ok = ($details['db'] === 'healthy') && ($details['redis'] === 'healthy');
            http_response_code($ok ? 200 : 503);
            echo json_encode([
                'ready' => $ok,
                'components' => $details,
                'timestamp' => date('c')
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $agent->shutdown();
            break;
            
        case 'metrics':
            // Detailed metrics - requires initialization
            $agent = new Agent($config, $logger);
            $agent->initialize();
            
            $metrics = $agent->getMetrics();
            
            // Add system metrics
            $systemMetrics = [
                'server' => [
                    'php_version' => PHP_VERSION,
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit'),
                    'uptime' => file_exists('/proc/uptime') ? trim(explode(' ', file_get_contents('/proc/uptime'))[0]) : null,
                    'load_average' => sys_getloadavg()
                ],
                'request' => [
                    'timestamp' => date('c'),
                    'server_time' => microtime(true),
                    'timezone' => date_default_timezone_get(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'request_method' => $_SERVER['REQUEST_METHOD'],
                    'request_uri' => $_SERVER['REQUEST_URI']
                ]
            ];
            
            $result = array_merge($metrics, ['system' => $systemMetrics]);
            
            http_response_code(200);
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            $agent->shutdown();
            break;
            
        case 'status':
            // Detailed component status
            $agent = new Agent($config, $logger);
            $agent->initialize();
            
            $status = [
                'agent' => $agent->getHealth(),
                'config' => $agent->getConfig(),
                'environment' => [
                    'php_version' => PHP_VERSION,
                    'extensions' => [
                        'pdo' => extension_loaded('pdo'),
                        'pdo_mysql' => extension_loaded('pdo_mysql'),
                        'redis' => extension_loaded('redis'),
                        'curl' => extension_loaded('curl'),
                        'json' => extension_loaded('json'),
                        'mbstring' => extension_loaded('mbstring'),
                        'openssl' => extension_loaded('openssl')
                    ],
                    'php_settings' => [
                        'memory_limit' => ini_get('memory_limit'),
                        'max_execution_time' => ini_get('max_execution_time'),
                        'upload_max_filesize' => ini_get('upload_max_filesize'),
                        'post_max_size' => ini_get('post_max_size'),
                        'error_reporting' => ini_get('error_reporting'),
                        'display_errors' => ini_get('display_errors')
                    ]
                ],
                'filesystem' => [
                    'tmp_writable' => is_writable(sys_get_temp_dir()),
                    'logs_writable' => is_writable(dirname(__DIR__, 3) . '/logs'),
                    'tmp_space' => disk_free_space(sys_get_temp_dir()),
                    'project_space' => disk_free_space(dirname(__DIR__, 3))
                ]
            ];
            
            http_response_code(200);
            echo json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            $agent->shutdown();
            break;
            
        case 'ping':
            // Ultra-lightweight ping endpoint
            echo json_encode([
                'success' => true,
                'message' => 'pong',
                'timestamp' => date('c'),
                'server_time' => microtime(true)
            ]);
            break;
            
        default:
            throw Errors::validationError('Unknown health endpoint: ' . $endpoint);
    }
    
} catch (Exception $e) {
    $errorCode = 500;
    $errorType = 'internal_error';
    
    // Determine appropriate error code
    if (strpos($e->getMessage(), 'Unknown') !== false) {
        $errorCode = 404;
        $errorType = 'not_found_error';
    }
    
    // Log error
    if (isset($logger)) {
        $logger->error('Health API error', [
            'error' => $e->getMessage(),
            'endpoint' => $endpoint ?? 'unknown',
            'type' => $errorType,
            'code' => $errorCode
        ]);
    }
    
    // Return error response
    http_response_code($errorCode);
    
    $errorResponse = [
        'success' => false,
        'error' => [
            'type' => $errorType,
            'message' => $e->getMessage(),
            'code' => $errorCode
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Ensure output is flushed
if (ob_get_level()) {
    ob_end_flush();
}
flush();