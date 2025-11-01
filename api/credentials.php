<?php
/**
 * Credentials Management API
 * 
 * Endpoints for managing bot and system credentials
 * 
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../services/CredentialManager.php';

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    $credManager = new CredentialManager();
    
    switch ($action) {
        case 'list':
            // List all credentials (without values)
            $credentials = $credManager->listAll();
            echo json_encode([
                'success' => true,
                'data' => $credentials,
                'count' => count($credentials),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get':
            // Get specific credential
            $type = $_GET['type'] ?? '';
            $key = $_GET['key'] ?? '';
            
            if (empty($type) || empty($key)) {
                throw new Exception('Missing type or key parameter');
            }
            
            $value = $credManager->get($type, $key);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'type' => $type,
                    'key' => $key,
                    'value' => $value
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get_type':
            // Get all credentials of a type
            $type = $_GET['type'] ?? $_POST['type'] ?? '';
            
            if (empty($type)) {
                throw new Exception('Missing type parameter');
            }
            
            $credentials = $credManager->getAllOfType($type);
            
            echo json_encode([
                'success' => true,
                'type' => $type,
                'data' => $credentials,
                'count' => count($credentials),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'get_all':
            // Get all credentials grouped by type
            $allCredentials = CredentialManager::getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $allCredentials,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'store':
            // Store a credential
            $type = $_POST['type'] ?? '';
            $key = $_POST['key'] ?? '';
            $value = $_POST['value'] ?? '';
            $encrypt = isset($_POST['encrypt']) ? (bool)$_POST['encrypt'] : true;
            $description = $_POST['description'] ?? null;
            
            if (empty($type) || empty($key) || empty($value)) {
                throw new Exception('Missing required parameters: type, key, value');
            }
            
            $success = $credManager->store($type, $key, $value, $encrypt, $description);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Credential stored successfully' : 'Failed to store credential',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'delete':
            // Delete a credential
            $type = $_POST['type'] ?? $_GET['type'] ?? '';
            $key = $_POST['key'] ?? $_GET['key'] ?? '';
            
            if (empty($type) || empty($key)) {
                throw new Exception('Missing type or key parameter');
            }
            
            $success = $credManager->delete($type, $key);
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Credential deleted successfully' : 'Failed to delete credential',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'test_database':
            // Test database connection
            $result = $credManager->testDatabaseConnection();
            
            echo json_encode([
                'success' => $result['success'],
                'data' => $result,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'initialize':
            // Initialize default credentials
            $success = $credManager->initializeDefaults();
            
            echo json_encode([
                'success' => $success,
                'message' => $success 
                    ? 'Default credentials initialized successfully' 
                    : 'Failed to initialize defaults',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'database_credentials':
            // Get database credentials
            $credentials = $credManager->getDatabaseCredentials();
            
            echo json_encode([
                'success' => true,
                'data' => $credentials,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'path_credentials':
            // Get path credentials
            $paths = $credManager->getPathCredentials();
            
            echo json_encode([
                'success' => true,
                'data' => $paths,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'api_keys':
            // Get API keys
            $apiKeys = $credManager->getApiKeyCredentials();
            
            echo json_encode([
                'success' => true,
                'data' => $apiKeys,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'server_credentials':
            // Get server credentials
            $server = $credManager->getServerCredentials();
            
            echo json_encode([
                'success' => true,
                'data' => $server,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
