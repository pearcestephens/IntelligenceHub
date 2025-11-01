<?php
/**
 * Admin API Endpoint - Execute administrative operations
 * 
 * Provides secure web access to administrative tools and operations.
 * Executes operations from the ops/ directory safely.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

use App\Config;
use App\Logger;
use App\Util\Errors;

// Set headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $config = new Config();
    $logger = new Logger($config);
    
    // Basic security check
    $allowedHosts = ['staff.vapeshed.co.nz', 'localhost', '127.0.0.1'];
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    
    if (!in_array($currentHost, $allowedHosts) && !str_contains($currentHost, 'staff.vapeshed')) {
        throw Errors::internalError('Access denied from this host');
    }
    
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $operation = $_GET['op'] ?? $_POST['op'] ?? '';
    
    $logger->info('Admin API request', [
        'operation' => $operation,
        'method' => $method,
        'host' => $currentHost,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    switch ($operation) {
        case 'health-check':
            $result = performHealthCheck($config, $logger);
            break;
            
        case 'system-doctor':
            $result = runSystemDoctor($config, $logger);
            break;
            
        case 'performance-test':
            $result = runPerformanceTest($config, $logger);
            break;
            
        case 'security-scan':
            $result = runSecurityScan($config, $logger);
            break;
            
        case 'view-logs':
            $result = viewSystemLogs($config, $logger);
            break;
            
        case 'system-info':
            $result = getSystemInfo($config, $logger);
            break;
            
        case 'conversations-stats':
            $result = getConversationStats($config, $logger);
            break;
            
        default:
            throw Errors::validationError('Invalid operation specified');
    }
    
    echo json_encode([
        'success' => true,
        'operation' => $operation,
        'data' => $result,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $logger->error('Admin API error', [
        'error' => $e->getMessage(),
        'operation' => $operation ?? 'unknown'
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'operation' => $operation ?? 'unknown'
        ]
    ], JSON_PRETTY_PRINT);
}

/**
 * Perform comprehensive health check
 */
function performHealthCheck(Config $config, Logger $logger): array
{
    $checks = [];
    
    // Database check
    try {
        $db = new App\DB($config, $logger);
        $db->query("SELECT 1");
        $checks['database'] = ['status' => 'healthy', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        $checks['database'] = ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
    
    // Redis check
    try {
        $redis = new App\RedisClient($config, $logger);
        $redis->set('health_check', 'test', 10);
        $checks['redis'] = ['status' => 'healthy', 'message' => 'Redis connection successful'];
    } catch (Exception $e) {
        $checks['redis'] = ['status' => 'error', 'message' => 'Redis connection failed: ' . $e->getMessage()];
    }
    
    // OpenAI API check
    try {
        $openai = new App\OpenAI($config, $logger);
        $checks['openai'] = ['status' => 'healthy', 'message' => 'OpenAI API configured'];
    } catch (Exception $e) {
        $checks['openai'] = ['status' => 'error', 'message' => 'OpenAI API error: ' . $e->getMessage()];
    }
    
    // Claude API check
    try {
        if ($config->get('ANTHROPIC_API_KEY') && $config->get('ANTHROPIC_API_KEY') !== 'YOUR_CLAUDE_API_KEY_HERE') {
            $claude = new App\Claude($config, $logger);
            $checks['claude'] = ['status' => 'healthy', 'message' => 'Claude API configured'];
        } else {
            $checks['claude'] = ['status' => 'disabled', 'message' => 'Claude API not configured'];
        }
    } catch (Exception $e) {
        $checks['claude'] = ['status' => 'error', 'message' => 'Claude API error: ' . $e->getMessage()];
    }
    
    // File system check
    $agentPath = dirname(__DIR__, 2);
    $checks['filesystem'] = [
        'status' => is_writable($agentPath . '/logs') ? 'healthy' : 'warning',
        'message' => is_writable($agentPath . '/logs') ? 'File system writable' : 'Some directories not writable'
    ];
    
    return [
        'checks' => $checks,
        'overall_status' => array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] !== 'error';
        }, true) ? 'healthy' : 'issues_detected'
    ];
}

/**
 * Run system diagnostics
 */
function runSystemDoctor(Config $config, Logger $logger): array
{
    $diagnostics = [];
    
    // System resources
    $diagnostics['memory_usage'] = [
        'current' => memory_get_usage(true),
        'peak' => memory_get_peak_usage(true),
        'limit' => ini_get('memory_limit')
    ];
    
    // Disk space
    $agentPath = dirname(__DIR__, 2);
    $diagnostics['disk_space'] = [
        'free' => disk_free_space($agentPath),
        'total' => disk_total_space($agentPath),
        'used_percent' => round((1 - disk_free_space($agentPath) / disk_total_space($agentPath)) * 100, 2)
    ];
    
    // PHP configuration
    $diagnostics['php_config'] = [
        'version' => PHP_VERSION,
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ];
    
    // Extensions check
    $requiredExtensions = ['curl', 'json', 'pdo', 'pdo_mysql', 'redis'];
    $diagnostics['php_extensions'] = [];
    foreach ($requiredExtensions as $ext) {
        $diagnostics['php_extensions'][$ext] = extension_loaded($ext) ? 'loaded' : 'missing';
    }
    
    return $diagnostics;
}

/**
 * Run performance tests
 */
function runPerformanceTest(Config $config, Logger $logger): array
{
    $results = [];
    
    // API response time test
    $startTime = microtime(true);
    try {
        $healthResult = performHealthCheck($config, $logger);
        $results['health_check_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
    } catch (Exception $e) {
        $results['health_check_time'] = 'failed';
    }
    
    // Database query performance
    $startTime = microtime(true);
    try {
        $db = new App\DB($config, $logger);
        $db->query("SELECT COUNT(*) FROM conversations");
        $results['db_query_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
    } catch (Exception $e) {
        $results['db_query_time'] = 'failed';
    }
    
    // Redis performance
    $startTime = microtime(true);
    try {
        $redis = new App\RedisClient($config, $logger);
        $redis->set('perf_test', 'test', 10);
        $redis->get('perf_test');
        $results['redis_operation_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
    } catch (Exception $e) {
        $results['redis_operation_time'] = 'failed';
    }
    
    // File I/O performance
    $startTime = microtime(true);
    $testFile = sys_get_temp_dir() . '/ai_agent_perf_test.tmp';
    file_put_contents($testFile, str_repeat('test', 1000));
    file_get_contents($testFile);
    unlink($testFile);
    $results['file_io_time'] = round((microtime(true) - $startTime) * 1000, 2) . 'ms';
    
    return $results;
}

/**
 * Run security scan
 */
function runSecurityScan(Config $config, Logger $logger): array
{
    $security = [];
    
    // File permissions check
    $agentPath = dirname(__DIR__, 2);
    $criticalPaths = [
        '.env' => $agentPath . '/.env',
        'logs' => $agentPath . '/logs',
        'src' => $agentPath . '/src',
        'public' => $agentPath . '/public'
    ];
    
    $security['file_permissions'] = [];
    foreach ($criticalPaths as $name => $path) {
        if (file_exists($path)) {
            $perms = fileperms($path);
            $security['file_permissions'][$name] = [
                'exists' => true,
                'readable' => is_readable($path),
                'writable' => is_writable($path),
                'permissions' => substr(sprintf('%o', $perms), -4)
            ];
        } else {
            $security['file_permissions'][$name] = ['exists' => false];
        }
    }
    
    // Configuration security
    $security['configuration'] = [
        'openai_key_set' => !empty($config->get('OPENAI_API_KEY')),
        'claude_key_set' => !empty($config->get('ANTHROPIC_API_KEY')) && $config->get('ANTHROPIC_API_KEY') !== 'YOUR_CLAUDE_API_KEY_HERE',
        'db_password_set' => !empty($config->get('MYSQL_PASSWORD')),
        'https_enabled' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'
    ];
    
    // Server security headers
    $security['security_headers'] = [
        'x_frame_options' => $_SERVER['HTTP_X_FRAME_OPTIONS'] ?? 'not_set',
        'x_content_type_options' => $_SERVER['HTTP_X_CONTENT_TYPE_OPTIONS'] ?? 'not_set',
        'strict_transport_security' => $_SERVER['HTTP_STRICT_TRANSPORT_SECURITY'] ?? 'not_set'
    ];
    
    return $security;
}

/**
 * View system logs
 */
function viewSystemLogs(Config $config, Logger $logger): array
{
    $agentPath = dirname(__DIR__, 2);
    $logPath = $agentPath . '/logs/operations.log';
    
    $logs = [];
    
    if (file_exists($logPath)) {
        $lines = array_slice(file($logPath), -50); // Last 50 lines
        $logs['operations'] = array_map('trim', $lines);
    } else {
        $logs['operations'] = ['Log file not found'];
    }
    
    // System log info
    $logs['info'] = [
        'log_file_exists' => file_exists($logPath),
        'log_file_size' => file_exists($logPath) ? filesize($logPath) : 0,
        'log_file_modified' => file_exists($logPath) ? date('c', filemtime($logPath)) : 'never'
    ];
    
    return $logs;
}

/**
 * Get system information
 */
function getSystemInfo(Config $config, Logger $logger): array
{
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'server_os' => php_uname('s') . ' ' . php_uname('r'),
        'server_hostname' => php_uname('n'),
        'current_user' => get_current_user(),
        'memory_usage' => [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true)
        ],
        'loaded_extensions' => get_loaded_extensions(),
        'config' => [
            'openai_model' => $config->get('OPENAI_MODEL', 'not_set'),
            'claude_model' => $config->get('CLAUDE_MODEL', 'not_set'),
            'mysql_host' => $config->get('MYSQL_HOST', 'not_set'),
            'redis_configured' => !empty($config->get('REDIS_URL'))
        ]
    ];
}

/**
 * Get conversation statistics
 */
function getConversationStats(Config $config, Logger $logger): array
{
    try {
        $db = new App\DB($config, $logger);
        
        $totalConvs = $db->query("SELECT COUNT(*) as count FROM conversations")->fetch()['count'] ?? 0;
        $totalMessages = $db->query("SELECT COUNT(*) as count FROM messages")->fetch()['count'] ?? 0;
        
        $todayConvs = $db->query("SELECT COUNT(*) as count FROM conversations WHERE DATE(created_at) = CURDATE()")->fetch()['count'] ?? 0;
        $todayMessages = $db->query("SELECT COUNT(*) as count FROM messages WHERE DATE(created_at) = CURDATE()")->fetch()['count'] ?? 0;
        
        return [
            'total_conversations' => $totalConvs,
            'total_messages' => $totalMessages,
            'today_conversations' => $todayConvs,
            'today_messages' => $todayMessages,
            'avg_messages_per_conversation' => $totalConvs > 0 ? round($totalMessages / $totalConvs, 2) : 0
        ];
        
    } catch (Exception $e) {
        return [
            'error' => 'Failed to get conversation stats: ' . $e->getMessage()
        ];
    }
}