<?php
/**
 * Search API Endpoint
 * Searches intelligence files
 */
// Start session if not already started (avoids notices when included)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Define dashboard access guard if not already defined
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

// Accept both 'query' and 'q' parameter names
$query = $_GET['query'] ?? $_GET['q'] ?? '';
$type = $_GET['type'] ?? '';
$server = $_GET['server'] ?? '';
$minSize = isset($_GET['minSize']) && $_GET['minSize'] !== '' ? (int)$_GET['minSize'] : null;
$maxSize = isset($_GET['maxSize']) && $_GET['maxSize'] !== '' ? (int)$_GET['maxSize'] : null;
$dateRange = isset($_GET['dateRange']) && $_GET['dateRange'] !== '' ? (int)$_GET['dateRange'] : null;
$functionsOnly = isset($_GET['functionsOnly']) && $_GET['functionsOnly'] === 'true';
$caseSensitive = isset($_GET['caseSensitive']) && $_GET['caseSensitive'] === 'true';
$limit = (int)($_GET['limit'] ?? 100);

if (empty($query)) {
    echo json_encode([
        'success' => false,
        'error' => 'Query parameter required'
    ]);
    exit;
}

try {
    $db = getDbConnection();
    
    // Build base query with correct column names
    $sql = "SELECT 
        file_id,
        server_id,
        file_path,
        file_name,
        file_type,
        file_size,
        intelligence_type,
        content_summary,
        extracted_at,
        updated_at
    FROM intelligence_files 
    WHERE 1=1";
    $params = [];
    
    // Add search conditions
    if ($caseSensitive) {
        $sql .= " AND (file_name LIKE BINARY ? OR file_path LIKE BINARY ? OR content_summary LIKE BINARY ? OR file_content LIKE BINARY ?)";
        $searchTerm = '%' . $query . '%';
    } else {
        $sql .= " AND (file_name LIKE ? OR file_path LIKE ? OR content_summary LIKE ? OR file_content LIKE ?)";
        $searchTerm = '%' . $query . '%';
    }
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    
    // Type filter (file_type, not intelligence_type for the dropdown)
    if (!empty($type)) {
        // Map frontend types to database enum values
        $typeMap = [
            'code_php' => 'code_intelligence',
            'code_js' => 'code_intelligence',
            'code_python' => 'code_intelligence',
            'documentation' => 'documentation',
            'business_data' => 'business_intelligence',
            'config' => 'operational_intelligence'
        ];
        
        if (isset($typeMap[$type])) {
            $sql .= " AND file_type = ?";
            $params[] = $typeMap[$type];
        } elseif ($type === 'code_intelligence' || $type === 'business_intelligence' || $type === 'operational_intelligence') {
            $sql .= " AND file_type = ?";
            $params[] = $type;
        }
    }
    
    // Server filter
    if (!empty($server)) {
        $sql .= " AND server_id = ?";
        $params[] = $server;
    }
    
    // File size filters
    if ($minSize !== null) {
        $sql .= " AND file_size >= ?";
        $params[] = $minSize;
    }
    
    if ($maxSize !== null) {
        $sql .= " AND file_size <= ?";
        $params[] = $maxSize;
    }
    
    // Date range filter
    if ($dateRange !== null) {
        $sql .= " AND extracted_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params[] = $dateRange;
    }
    
    // Functions only filter
    if ($functionsOnly) {
        $sql .= " AND (file_content LIKE '%function %' OR content_summary LIKE '%function %')";
    }
    
    // Add ordering - prioritize exact matches in filename
    $sql .= " ORDER BY 
        CASE 
            WHEN file_name LIKE ? THEN 1
            WHEN file_path LIKE ? THEN 2
            ELSE 3
        END,
        extracted_at DESC 
    LIMIT ?";
    
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $limit;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add 'last_modified' alias for frontend compatibility
    foreach ($results as &$result) {
        $result['last_modified'] = $result['extracted_at'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results),
        'query' => $query,
        'filters' => [
            'type' => $type,
            'server' => $server,
            'minSize' => $minSize,
            'maxSize' => $maxSize,
            'dateRange' => $dateRange,
            'functionsOnly' => $functionsOnly,
            'caseSensitive' => $caseSensitive
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ]);
}
