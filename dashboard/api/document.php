<?php
/**
 * Document API Endpoint
 * Retrieves full document content
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

$id = $_GET['id'] ?? '';

if (empty($id)) {
    echo json_encode([
        'success' => false,
        'error' => 'Document ID required'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    
    $stmt = $db->prepare("SELECT * FROM intelligence_files WHERE file_id = ?");
    $stmt->execute([$id]);
    $document = $stmt->fetch();
    
    if (!$document) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Document not found'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $document,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
