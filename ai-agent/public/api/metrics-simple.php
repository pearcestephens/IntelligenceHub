<?php
/**
 * Minimal Metrics - No Dependencies
 * Pure PHP metrics for basic system monitoring
 */

declare(strict_types=1);

// Load secure database config
require_once __DIR__ . '/../../../../config/db_config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, max-age=5');

$metrics = [
    'timestamp' => date('c'),
    'php_version' => PHP_VERSION,
    'metrics' => []
];

// System metrics
$metrics['metrics']['system'] = [
    'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
    'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
    'memory_limit' => ini_get('memory_limit')
];

// Server info
$metrics['metrics']['server'] = [
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown'
];

// Redis quick check
try {
    if (extension_loaded('redis')) {
        $redis = new Redis();
        if ($redis->connect('127.0.0.1', 6379, 1.0)) {
            $info = $redis->info();
            $metrics['metrics']['redis'] = [
                'status' => 'connected',
                'used_memory_mb' => isset($info['used_memory']) ? round($info['used_memory'] / 1024 / 1024, 2) : 0,
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands_processed' => $info['total_commands_processed'] ?? 0
            ];
            
            // Try to get key count
            try {
                $dbsize = $redis->dbSize();
                $metrics['metrics']['redis']['total_keys'] = $dbsize;
            } catch (Exception $e) {
                $metrics['metrics']['redis']['total_keys'] = 'unavailable';
            }
            
            // Check for agent-specific keys
            try {
                $agentKeys = $redis->keys('aiagent:*');
                $metrics['metrics']['redis']['agent_keys'] = is_array($agentKeys) ? count($agentKeys) : 0;
            } catch (Exception $e) {
                $metrics['metrics']['redis']['agent_keys'] = 'unavailable';
            }
            
            $redis->close();
        } else {
            $metrics['metrics']['redis'] = ['status' => 'connection_failed'];
        }
    } else {
        $metrics['metrics']['redis'] = ['status' => 'extension_not_loaded'];
    }
} catch (Exception $e) {
    $metrics['metrics']['redis'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// MySQL quick check
try {
    $mysqli = db_config(); // Secure connection from config
    
    if (!$mysqli->connect_error) {
        $metrics['metrics']['mysql'] = [
            'status' => 'connected',
            'server_version' => $mysqli->server_info
        ];
        
        // Get table count
        $result = $mysqli->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'jcepnzzkmj'");
        if ($result) {
            $row = $result->fetch_assoc();
            $metrics['metrics']['mysql']['table_count'] = $row['count'];
            $result->free();
        }
        
        $mysqli->close();
    } else {
        $metrics['metrics']['mysql'] = [
            'status' => 'connection_failed',
            'error' => $mysqli->connect_error
        ];
    }
} catch (Exception $e) {
    $metrics['metrics']['mysql'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

http_response_code(200);
echo json_encode($metrics, JSON_PRETTY_PRINT);
