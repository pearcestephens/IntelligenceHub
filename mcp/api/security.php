<?php
/**
 * AI Agent - Security API Endpoint
 * 
 * Provides security tokens and session management
 * 
 * @version 2.0.0
 * @author AI Agent System
 */

declare(strict_types=1);

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Generate secure CSRF token
 */
function generateCsrfToken(): string
{
    return bin2hex(random_bytes(32));
}

/**
 * Generate session ID
 */
function generateSessionId(): string
{
    return 'sess_' . bin2hex(random_bytes(16));
}

/**
 * Send JSON response
 */
function sendResponse(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    
    $response = [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'timestamp' => date('c'),
        ...$data
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Start session
    session_start();
    
    // Generate tokens
    $csrfToken = generateCsrfToken();
    $sessionId = generateSessionId();
    
    // Store in session for validation
    $_SESSION['csrf_token'] = $csrfToken;
    $_SESSION['session_id'] = $sessionId;
    $_SESSION['created_at'] = time();
    
    // Send response
    sendResponse([
        'csrf_token' => $csrfToken,
        'session_id' => $sessionId,
        'expires_at' => date('c', time() + 3600), // 1 hour
        'security_level' => 'hardened'
    ]);
    
} catch (Exception $e) {
    sendResponse([
        'error' => 'Failed to generate security tokens',
        'error_code' => 500
    ], 500);
}
?>