<?php
/**
 * Intelligence Hub - Comprehensive Health Check v2.0
 * 
 * Complete system health monitoring:
 * - Database connectivity & statistics
 * - All satellite connectivity
 * - File system permissions
 * - MCP server functionality
 * - Content processing pipeline
 * - NLP analysis status
 * - FULLTEXT index status
 * - Recent sync activity
 * 
 * @package IntelligenceHub\MCP
 * @version 2.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

// Satellite configuration
define('SATELLITES', [
    1 => ['name' => 'Intelligence Hub', 'url' => 'https://gpt.ecigdis.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    2 => ['name' => 'CIS', 'url' => 'https://staff.vapeshed.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    3 => ['name' => 'VapeShed', 'url' => 'https://vapeshed.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    4 => ['name' => 'Wholesale', 'url' => 'https://wholesale.ecigdis.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
]);

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '2.0.0',
    'uptime' => null,
    'checks' => [],
    'statistics' => [],
    'warnings' => [],
    'errors' => [],
];

$startTime = microtime(true);

// ============================================================================
// 1. DATABASE CONNECTIVITY & STATISTICS
// ============================================================================
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM intelligence_content");
    $totalFiles = (int)$stmt->fetchColumn();
    
    // Get detailed stats
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total_files,
        COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
        COUNT(CASE WHEN ct.content_summary IS NOT NULL THEN 1 END) as with_summary,
        COUNT(CASE WHEN ct.extracted_keywords IS NOT NULL THEN 1 END) as with_keywords,
        COUNT(CASE WHEN ct.semantic_tags IS NOT NULL THEN 1 END) as with_tags,
        COUNT(CASE WHEN c.file_modified IS NOT NULL THEN 1 END) as with_mod_tracking,
        ROUND(AVG(ct.readability_score), 2) as avg_readability,
        SUM(ct.word_count) as total_words,
        MAX(c.created_at) as last_indexed
    FROM intelligence_content c
    LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $coverage = $stats['total_files'] > 0 
        ? round(($stats['with_content'] / $stats['total_files']) * 100, 1) 
        : 0;
    
    $health['checks']['database'] = [
        'status' => 'ok',
        'connection' => 'active',
        'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
    ];
    
    $health['statistics']['database'] = [
        'total_files' => (int)$stats['total_files'],
        'with_content' => (int)$stats['with_content'],
        'with_summary' => (int)$stats['with_summary'],
        'with_keywords' => (int)$stats['with_keywords'],
        'with_tags' => (int)$stats['with_tags'],
        'with_mod_tracking' => (int)$stats['with_mod_tracking'],
        'content_coverage' => $coverage . '%',
        'avg_readability' => (float)$stats['avg_readability'],
        'total_words' => (int)$stats['total_words'],
        'last_indexed' => $stats['last_indexed'],
    ];
    
    // Warnings
    if ($coverage < 50) {
        $health['warnings'][] = "Content coverage is low: {$coverage}% (target: 80%+)";
    }
    if ($stats['with_mod_tracking'] < $stats['total_files'] * 0.9) {
        $health['warnings'][] = "Only " . $stats['with_mod_tracking'] . " files have modification tracking (target: 90%+)";
    }
    
} catch (PDOException $e) {
    $health['status'] = 'unhealthy';
    $health['errors'][] = 'Database connection failed: ' . $e->getMessage();
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Cannot connect to database',
    ];
}

// ============================================================================
// 2. FULLTEXT INDEX CHECK
// ============================================================================
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SHOW INDEX FROM intelligence_content_text WHERE Key_name = 'idx_content_text_fulltext'");
        $index = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($index) {
            $health['checks']['fulltext_index'] = [
                'status' => 'ok',
                'index_name' => 'idx_content_text_fulltext',
                'column' => 'content_text',
            ];
        } else {
            $health['status'] = 'degraded';
            $health['warnings'][] = 'FULLTEXT index missing on content_text - semantic search will be slow';
            $health['checks']['fulltext_index'] = [
                'status' => 'warning',
                'message' => 'FULLTEXT index not found',
            ];
        }
    } catch (PDOException $e) {
        $health['warnings'][] = 'Could not verify FULLTEXT index: ' . $e->getMessage();
    }
}

// ============================================================================
// 3. SATELLITE CONNECTIVITY
// ============================================================================
$satelliteStart = microtime(true);
$satelliteStats = [];

foreach (SATELLITES as $unitId => $config) {
    $satStart = microtime(true);
    
    // Get database stats for this satellite
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total_files,
                COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
                ROUND(AVG(ct.readability_score), 2) as avg_readability,
                SUM(ct.word_count) as total_words,
                MAX(c.created_at) as last_sync
            FROM intelligence_content c
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            WHERE c.unit_id = ?");
            $stmt->execute([$unitId]);
            $satStats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $satCoverage = $satStats['total_files'] > 0 
                ? round(($satStats['with_content'] / $satStats['total_files']) * 100, 1) 
                : 0;
            
            // Test connectivity (with timeout)
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'ignore_errors' => true,
                ]
            ]);
            
            $testUrl = $config['url'] . '?auth=' . $config['auth'] . '&batch=1';
            $response = @file_get_contents($testUrl, false, $context);
            $responseCode = 200;
            if (isset($http_response_header) && preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches)) {
                $responseCode = (int)$matches[1];
            }
            
            $responseTime = round((microtime(true) - $satStart) * 1000, 2);
            
            $satelliteStats[$unitId] = [
                'name' => $config['name'],
                'unit_id' => $unitId,
                'connectivity' => [
                    'status' => $response !== false && $responseCode === 200 ? 'ok' : 'error',
                    'response_time_ms' => $responseTime,
                    'last_checked' => date('Y-m-d H:i:s'),
                ],
                'statistics' => [
                    'total_files' => (int)$satStats['total_files'],
                    'with_content' => (int)$satStats['with_content'],
                    'coverage' => $satCoverage . '%',
                    'avg_readability' => (float)$satStats['avg_readability'],
                    'total_words' => (int)$satStats['total_words'],
                    'last_sync' => $satStats['last_sync'],
                ],
            ];
            
            // Warnings
            if ($response === false || $responseCode !== 200) {
                $health['warnings'][] = "Satellite '{$config['name']}' (unit {$unitId}) is not responding";
                if ($health['status'] === 'healthy') {
                    $health['status'] = 'degraded';
                }
            }
            
            if ($satStats['total_files'] == 0 && $unitId > 1) {
                $health['warnings'][] = "Satellite '{$config['name']}' (unit {$unitId}) has no indexed files";
            }
            
        } catch (PDOException $e) {
            $satelliteStats[$unitId] = [
                'name' => $config['name'],
                'unit_id' => $unitId,
                'connectivity' => ['status' => 'error', 'message' => 'Database query failed'],
                'statistics' => null,
            ];
        }
    }
}

$health['checks']['satellites'] = [
    'status' => count($satelliteStats) === count(SATELLITES) ? 'ok' : 'partial',
    'total_satellites' => count(SATELLITES),
    'checked' => count($satelliteStats),
    'check_time_ms' => round((microtime(true) - $satelliteStart) * 1000, 2),
];

$health['statistics']['satellites'] = $satelliteStats;

// ============================================================================
// 4. FILE SYSTEM PERMISSIONS
// ============================================================================
$testFile = __DIR__ . '/.health_check_' . time() . '.tmp';
$canWrite = @file_put_contents($testFile, 'health_check_test');

if ($canWrite !== false) {
    @unlink($testFile);
    $health['checks']['filesystem'] = [
        'status' => 'ok',
        'write_permission' => true,
        'directory' => __DIR__,
    ];
} else {
    $health['status'] = 'degraded';
    $health['warnings'][] = 'Cannot write to MCP directory - may affect logging';
    $health['checks']['filesystem'] = [
        'status' => 'warning',
        'write_permission' => false,
        'directory' => __DIR__,
    ];
}

// ============================================================================
// 5. MCP SERVER FUNCTIONALITY
// ============================================================================
$mcpServerFile = __DIR__ . '/server_v2_complete.php';
if (file_exists($mcpServerFile)) {
    $health['checks']['mcp_server'] = [
        'status' => 'ok',
        'file' => 'server_v2_complete.php',
        'size_kb' => round(filesize($mcpServerFile) / 1024, 2),
        'last_modified' => date('Y-m-d H:i:s', filemtime($mcpServerFile)),
        'tools_available' => 10,
    ];
} else {
    $health['status'] = 'unhealthy';
    $health['errors'][] = 'MCP server file not found: server_v2_complete.php';
    $health['checks']['mcp_server'] = [
        'status' => 'error',
        'message' => 'Server file missing',
    ];
}

// ============================================================================
// 6. CONTENT PROCESSING PIPELINE
// ============================================================================
if (isset($pdo)) {
    try {
        // Check for recent processing activity
        $stmt = $pdo->query("SELECT 
            COUNT(CASE WHEN ct.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 1 END) as last_hour,
            COUNT(CASE WHEN ct.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h,
            COUNT(CASE WHEN ct.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7d
        FROM intelligence_content_text ct");
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $health['checks']['content_processing'] = [
            'status' => 'ok',
            'pipeline' => 'active',
        ];
        
        $health['statistics']['processing_activity'] = [
            'processed_last_hour' => (int)$activity['last_hour'],
            'processed_last_24h' => (int)$activity['last_24h'],
            'processed_last_7d' => (int)$activity['last_7d'],
        ];
        
        // Warning if no recent activity
        if ($activity['last_24h'] == 0) {
            $health['warnings'][] = 'No content processing activity in the last 24 hours';
        }
        
    } catch (PDOException $e) {
        $health['warnings'][] = 'Could not check processing activity';
    }
}

// ============================================================================
// 7. NLP ANALYSIS STATUS
// ============================================================================
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total,
            COUNT(ct.extracted_keywords) as with_keywords,
            COUNT(ct.semantic_tags) as with_tags,
            COUNT(ct.entities_detected) as with_entities,
            COUNT(ct.readability_score) as with_readability,
            COUNT(ct.sentiment_score) as with_sentiment
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE ct.content_text IS NOT NULL");
        $nlp = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $total = (int)$nlp['total'];
        
        $health['checks']['nlp_analysis'] = [
            'status' => 'ok',
            'pipeline_stages' => [
                'keyword_extraction' => round(($nlp['with_keywords'] / max($total, 1)) * 100, 1) . '%',
                'semantic_tagging' => round(($nlp['with_tags'] / max($total, 1)) * 100, 1) . '%',
                'entity_detection' => round(($nlp['with_entities'] / max($total, 1)) * 100, 1) . '%',
                'readability_analysis' => round(($nlp['with_readability'] / max($total, 1)) * 100, 1) . '%',
                'sentiment_analysis' => round(($nlp['with_sentiment'] / max($total, 1)) * 100, 1) . '%',
            ],
        ];
        
        // Warning if any stage is below 80%
        foreach ($health['checks']['nlp_analysis']['pipeline_stages'] as $stage => $percentage) {
            $pct = (float)str_replace('%', '', $percentage);
            if ($pct < 80 && $total > 100) {
                $health['warnings'][] = "NLP stage '{$stage}' coverage is low: {$percentage} (target: 80%+)";
            }
        }
        
    } catch (PDOException $e) {
        $health['warnings'][] = 'Could not check NLP analysis status';
    }
}

// ============================================================================
// 8. API ENDPOINTS
// ============================================================================
$apiEndpoints = [
    'scan_receiver' => __DIR__ . '/../api/receive_satellite_data.php',
    'agent_kb' => __DIR__ . '/../api/agent_kb.php',
];

$apiStatus = [];
foreach ($apiEndpoints as $name => $path) {
    $apiStatus[$name] = [
        'exists' => file_exists($path),
        'readable' => is_readable($path),
        'size_kb' => file_exists($path) ? round(filesize($path) / 1024, 2) : 0,
    ];
    
    if (!file_exists($path)) {
        $health['warnings'][] = "API endpoint '{$name}' file not found";
    }
}

$health['checks']['api_endpoints'] = [
    'status' => 'ok',
    'endpoints' => $apiStatus,
];

// ============================================================================
// 9. SYSTEM RESOURCES
// ============================================================================
$health['checks']['system_resources'] = [
    'status' => 'ok',
    'php_version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'post_max_size' => ini_get('post_max_size'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
];

// ============================================================================
// 10. OVERALL HEALTH ASSESSMENT
// ============================================================================
$totalChecks = count($health['checks']);
$passedChecks = 0;
foreach ($health['checks'] as $check) {
    if (isset($check['status']) && $check['status'] === 'ok') {
        $passedChecks++;
    }
}

$health['summary'] = [
    'overall_status' => $health['status'],
    'checks_passed' => $passedChecks,
    'checks_total' => $totalChecks,
    'pass_rate' => round(($passedChecks / max($totalChecks, 1)) * 100, 1) . '%',
    'warnings_count' => count($health['warnings']),
    'errors_count' => count($health['errors']),
    'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
];

// Set HTTP status code
$statusCode = match($health['status']) {
    'healthy' => 200,
    'degraded' => 200,
    'unhealthy' => 503,
    default => 500,
};

http_response_code($statusCode);

// Output
echo json_encode($health, JSON_PRETTY_PRINT);
