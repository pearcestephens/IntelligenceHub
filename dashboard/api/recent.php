<?php
/**
 * Recent Files API Endpoint
 * Returns recently modified files
 */
// Start session if not already started (avoids notices when included)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Allow API endpoints to include shared functions safely
if (!defined('DASHBOARD_ACCESS')) {
    define('DASHBOARD_ACCESS', true);
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Check authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized'
    ]);
    exit;
}

$limit = (int)($_GET['limit'] ?? 10);
$type = $_GET['type'] ?? '';

try {
    $db = getDbConnection();
    
    $sql = "SELECT file_id, file_name, file_path, file_type, file_size, server_id, updated_at 
            FROM intelligence_files 
            WHERE 1=1";
    $params = [];
    
    if (!empty($type)) {
        $sql .= " AND file_type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY updated_at DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $files = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $files,
        'count' => count($files),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
