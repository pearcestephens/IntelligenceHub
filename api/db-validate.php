<?php
/**
 * Database Validator API
 * 
 * Bot-friendly API for validating and auto-correcting database queries
 * 
 * Endpoints:
 * - /api/db-validate.php?action=validate_table&table=table_name
 * - /api/db-validate.php?action=validate_field&table=table_name&field=field_name
 * - /api/db-validate.php?action=validate_query&query=SELECT...
 * - /api/db-validate.php?action=table_info&table=table_name
 * - /api/db-validate.php?action=list_tables
 * - /api/db-validate.php?action=auto_correct&query=SELECT...
 * 
 * @package CIS\API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../services/DatabaseValidator.php';

// Response helper
function apiResponse(bool $success, $data = null, string $message = '', int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}

// Get action
$action = $_GET['action'] ?? '';

if (empty($action)) {
    apiResponse(false, null, 'Missing required parameter: action', 400);
}

try {
    $validator = new DatabaseValidator();
    
    switch ($action) {
        case 'validate_table':
            $table = $_GET['table'] ?? '';
            if (empty($table)) {
                apiResponse(false, null, 'Missing required parameter: table', 400);
            }
            
            $result = $validator->validateTable($table);
            apiResponse($result['valid'], $result, $result['message']);
            break;
            
        case 'validate_field':
            $table = $_GET['table'] ?? '';
            $field = $_GET['field'] ?? '';
            
            if (empty($table) || empty($field)) {
                apiResponse(false, null, 'Missing required parameters: table, field', 400);
            }
            
            $result = $validator->validateField($table, $field);
            apiResponse($result['valid'], $result, $result['message']);
            break;
            
        case 'validate_query':
            $query = $_GET['query'] ?? $_POST['query'] ?? '';
            if (empty($query)) {
                apiResponse(false, null, 'Missing required parameter: query', 400);
            }
            
            $result = $validator->validateQuery($query);
            apiResponse($result['valid'], $result, 
                $result['valid'] ? 'Query is valid' : 'Query has issues - see suggestions');
            break;
            
        case 'table_info':
            $table = $_GET['table'] ?? '';
            if (empty($table)) {
                apiResponse(false, null, 'Missing required parameter: table', 400);
            }
            
            $result = $validator->getTableInfo($table);
            apiResponse($result['valid'], $result, 
                $result['valid'] ? 'Table info retrieved' : $result['message']);
            break;
            
        case 'list_tables':
            $tables = $validator->getAllTables();
            $prefix = $_GET['prefix'] ?? '';
            
            if ($prefix) {
                $tables = array_filter($tables, function($table) use ($prefix) {
                    return strpos($table, $prefix) === 0;
                });
            }
            
            apiResponse(true, [
                'tables' => array_values($tables),
                'count' => count($tables)
            ], 'Tables retrieved');
            break;
            
        case 'auto_correct':
            $query = $_GET['query'] ?? $_POST['query'] ?? '';
            if (empty($query)) {
                apiResponse(false, null, 'Missing required parameter: query', 400);
            }
            
            $result = $validator->autoCorrectQuery($query);
            apiResponse(true, $result, 
                $result['auto_corrected'] ? 'Query auto-corrected' : 'No corrections needed');
            break;
            
        case 'clear_cache':
            $validator->clearCache();
            apiResponse(true, null, 'Schema cache cleared');
            break;
        
        case 'scan_code':
            // Scan PHP code for SQL errors
            $code = $_POST['code'] ?? $_GET['code'] ?? '';
            
            if (empty($code)) {
                apiResponse(false, null, 'Missing required parameter: code', 400);
            }
            
            $options = [
                'auto_fix' => isset($_GET['auto_fix']) ? (bool)$_GET['auto_fix'] : true,
                'check_security' => isset($_GET['check_security']) ? (bool)$_GET['check_security'] : true,
                'confidence_threshold' => (float)($_GET['threshold'] ?? 0.4)
            ];
            
            $result = $validator->scanCode($code, $options);
            apiResponse(true, $result);
            break;
            
        case 'scan_file':
            // Scan a PHP file
            $file = $_GET['file'] ?? $_POST['file'] ?? '';
            
            if (empty($file)) {
                apiResponse(false, null, 'Missing required parameter: file', 400);
            }
            
            // Security: ensure file is within project
            $realPath = realpath($file);
            $projectRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            
            if ($realPath === false || strpos($realPath, $projectRoot) !== 0) {
                apiResponse(false, null, 'Invalid file path or file outside project directory', 403);
            }
            
            $options = [
                'auto_fix' => isset($_GET['auto_fix']) ? (bool)$_GET['auto_fix'] : true,
                'check_security' => isset($_GET['check_security']) ? (bool)$_GET['check_security'] : true
            ];
            
            $result = $validator->scanFile($realPath, $options);
            apiResponse(true, $result);
            break;
            
        case 'scan_directory':
            // Scan entire directory
            $directory = $_GET['directory'] ?? $_POST['directory'] ?? '';
            
            if (empty($directory)) {
                apiResponse(false, null, 'Missing required parameter: directory', 400);
            }
            
            // Security: ensure directory is within project
            $realPath = realpath($directory);
            $projectRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            
            if ($realPath === false || strpos($realPath, $projectRoot) !== 0) {
                apiResponse(false, null, 'Invalid directory path or directory outside project', 403);
            }
            
            $options = [
                'auto_fix' => isset($_GET['auto_fix']) ? (bool)$_GET['auto_fix'] : true,
                'check_security' => isset($_GET['check_security']) ? (bool)$_GET['check_security'] : true,
                'recursive' => isset($_GET['recursive']) ? (bool)$_GET['recursive'] : true
            ];
            
            $result = $validator->scanDirectory($realPath, $options);
            apiResponse(true, $result);
            break;
            
        default:
            apiResponse(false, null, 'Invalid action. Available: validate_table, validate_field, validate_query, table_info, list_tables, auto_correct, clear_cache, scan_code, scan_file, scan_directory', 400);
    }
    
} catch (Exception $e) {
    error_log("DB Validator API Error: " . $e->getMessage());
    apiResponse(false, null, 'Internal server error: ' . $e->getMessage(), 500);
}
