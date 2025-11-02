<?php
/**
 * MCP Server Health Check
 * 
 * Quick health check endpoint for monitoring
 */

header('Content-Type: application/json');

$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';
$password = 'bFUdRjh4Jx';

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Check database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Use intelligence_content table (v2.0 schema)
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_files,
            COUNT(CASE WHEN category_id IS NOT NULL THEN 1 END) as categorized
        FROM intelligence_content
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $health['checks']['database'] = [
        'status' => 'ok',
        'total_files' => (int)$result['total_files'],
        'categorized' => (int)$result['categorized'],
        'coverage' => $result['total_files'] > 0 
            ? round($result['categorized'] / $result['total_files'] * 100, 1) . '%'
            : '0%'
    ];
} catch (PDOException $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ];
}

// Check write permissions
$testFile = __DIR__ . '/test_write_' . time() . '.tmp';
if (@file_put_contents($testFile, 'test')) {
    @unlink($testFile);
    $health['checks']['filesystem'] = ['status' => 'ok'];
} else {
    $health['status'] = 'degraded';
    $health['checks']['filesystem'] = ['status' => 'warning', 'message' => 'Write permissions issue'];
}

// Set HTTP status code
http_response_code($health['status'] === 'healthy' ? 200 : 503);

echo json_encode($health, JSON_PRETTY_PRINT);
