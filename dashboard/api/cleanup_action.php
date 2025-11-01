<?php
/**
 * Cleanup Action API Endpoint
 * Handles database cleanup operations
 */

// Define guard only if not already defined
if (!defined('DASHBOARD_ACCESS')) {
    define('DASHBOARD_ACCESS', true);
}

// Start session if not already started (avoids notices when included)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Check authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$db = getDbConnection();
$action = $_POST['action'] ?? '';

$response = ['success' => false];

try {
    switch ($action) {
        case 'delete_pattern':
            $condition = $_POST['condition'] ?? '';
            $label = $_POST['label'] ?? '';
            
            if (empty($condition)) {
                throw new Exception('Invalid condition');
            }
            
            // Get count and size before deletion
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            // Delete
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'delete_file':
            $fileId = intval($_POST['file_id'] ?? 0);
            
            if ($fileId <= 0) {
                throw new Exception('Invalid file ID');
            }
            
            $stmt = $db->prepare("DELETE FROM intelligence_files WHERE file_id = ?");
            $stmt->execute([$fileId]);
            
            $response['success'] = true;
            $response['deleted'] = 1;
            break;
            
        case 'clearNodeModules':
            $condition = "file_path LIKE '%node_modules%'";
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'clearVendor':
            $condition = "file_path LIKE '%vendor/%'";
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'clearLogs':
            $condition = "file_path LIKE '%.log'";
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'clearCache':
            $condition = "file_path LIKE '%cache/%' OR file_path LIKE '%/cache%'";
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'clearBackups':
            $condition = "file_path LIKE '%backup%' OR file_path LIKE '%.bak' OR file_path LIKE '%.backup'";
            $stmt = $db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files WHERE $condition");
            $before = $stmt->fetch();
            
            $db->exec("DELETE FROM intelligence_files WHERE $condition");
            
            $response['success'] = true;
            $response['deleted'] = $before['count'];
            $response['size_freed_mb'] = round($before['size'] / 1024 / 1024, 2);
            break;
            
        case 'optimizeDb':
            $db->exec("OPTIMIZE TABLE intelligence_files");
            
            $response['success'] = true;
            $response['message'] = 'Database optimized';
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
