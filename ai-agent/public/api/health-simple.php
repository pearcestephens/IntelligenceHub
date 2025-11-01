<?php
/**
 * Minimal Health Check - No Dependencies
 * Pure PHP health check for basic connectivity testing
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'checks' => []
];

// Check MySQL
try {
    $mysqli = new mysqli(
        '127.0.0.1',
        'jcepnzzkmj',
        'wprKh9Jq63',
        'jcepnzzkmj',
        3306
    );
    
    if ($mysqli->connect_error) {
        $health['checks']['mysql'] = [
            'status' => 'unhealthy',
            'error' => $mysqli->connect_error
        ];
        $health['status'] = 'degraded';
    } else {
        $health['checks']['mysql'] = [
            'status' => 'healthy',
            'message' => 'Connected'
        ];
        $mysqli->close();
    }
} catch (Exception $e) {
    $health['checks']['mysql'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// Check Redis
try {
    if (extension_loaded('redis')) {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 2.0)) {
            $ping = $redis->ping();
            $health['checks']['redis'] = [
                'status' => 'healthy',
                'message' => 'Connected',
                'ping' => $ping
            ];
            $redis->close();
        } else {
            $health['checks']['redis'] = [
                'status' => 'unhealthy',
                'error' => 'Connection failed'
            ];
            $health['status'] = 'degraded';
        }
    } else {
        $health['checks']['redis'] = [
            'status' => 'unavailable',
            'message' => 'Redis extension not loaded'
        ];
    }
} catch (Exception $e) {
    $health['checks']['redis'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
    $health['status'] = 'degraded';
}

// Check filesystem
$logsDir = __DIR__ . '/../../logs';
$health['checks']['filesystem'] = [
    'status' => is_writable($logsDir) ? 'healthy' : 'degraded',
    'logs_writable' => is_writable($logsDir),
    'logs_path' => $logsDir
];

if (!is_writable($logsDir)) {
    $health['status'] = 'degraded';
}

// Set HTTP status
$httpStatus = match($health['status']) {
    'healthy' => 200,
    'degraded' => 200,
    'unhealthy' => 503,
    default => 500
};

http_response_code($httpStatus);
echo json_encode($health, JSON_PRETTY_PRINT);
