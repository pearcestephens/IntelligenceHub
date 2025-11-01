<?php

/**
 * Health Check Endpoint - SIMPLIFIED (No Bootstrap)
 * 
 * Returns system health status for monitoring and load balancers.
 * Direct MySQL/Redis checks without framework overhead.
 * 
 * @package App\API
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

// Load secure database config
require_once __DIR__ . '/../../../../config/db_config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$startTime = microtime(true);
$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'php_version' => PHP_VERSION,
    'components' => []
];

// Check Database
try {
    $mysqli = db_config(); // Secure connection from config
    
    if ($mysqli->connect_error) {
        $health['components']['database'] = [
            'status' => 'unhealthy',
            'message' => 'Connection failed: ' . $mysqli->connect_error
        ];
        $health['status'] = 'degraded';
    } else {
        $health['components']['database'] = [
            'status' => 'healthy',
            'message' => 'Connected',
            'server_version' => $mysqli->server_info
        ];
        $mysqli->close();
    }
} catch (Exception $e) {
    $health['components']['database'] = [
        'status' => 'unhealthy',
        'message' => 'Error: ' . $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// Check Redis
try {
    if (extension_loaded('redis')) {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 2.0)) {
            $ping = $redis->ping();
            $health['components']['redis'] = [
                'status' => 'healthy',
                'message' => 'Connected',
                'ping' => $ping
            ];
            $redis->close();
        } else {
            $health['components']['redis'] = [
                'status' => 'unhealthy',
                'message' => 'Connection failed'
            ];
            $health['status'] = 'degraded';
        }
    } else {
        $health['components']['redis'] = [
            'status' => 'unavailable',
            'message' => 'Redis extension not loaded'
        ];
    }
} catch (Exception $e) {
    $health['components']['redis'] = [
        'status' => 'unhealthy',
        'message' => 'Error: ' . $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// Check filesystem
$logsDir = __DIR__ . '/../../logs';
$logsWritable = is_writable($logsDir);
$health['components']['filesystem'] = [
    'status' => $logsWritable ? 'healthy' : 'unhealthy',
    'message' => $logsWritable ? 'Logs directory writable' : 'Logs directory not writable',
    'logs_path' => $logsDir
];
if (!$logsWritable) {
    $health['status'] = 'degraded';
}

// Framework check (if requested)
if (isset($_GET['full']) && $_GET['full'] === 'true') {
    $health['components']['framework'] = [
        'status' => 'disabled',
        'message' => 'Framework bootstrap disabled for reliability - use simple endpoints'
    ];
}

$health['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

// Set appropriate HTTP status code
$httpStatus = ($health['status'] === 'healthy') ? 200 : (($health['status'] === 'degraded') ? 200 : 503);

http_response_code($httpStatus);
echo json_encode($health, JSON_PRETTY_PRINT);
