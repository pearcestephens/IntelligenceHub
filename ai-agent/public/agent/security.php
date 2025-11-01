<?php
/**
 * Security API Endpoint
 * Provides CSRF tokens and security validation
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Generate and return CSRF token
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        echo json_encode([
            'success' => true,
            'csrf_token' => $_SESSION['csrf_token'],
            'session_id' => session_id(),
            'expires_at' => time() + (30 * 60) // 30 minutes
        ]);
        break;
        
    case 'POST':
        // Validate CSRF token
        $input = json_decode(file_get_contents('php://input'), true);
        $provided_token = $input['csrf_token'] ?? '';
        
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $provided_token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid CSRF token'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'CSRF token validated successfully'
        ]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed'
        ]);
        break;
}
?>