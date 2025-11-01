<?php
/**
 * CSRF Protection Service
 * Automatically added by Security Hardening Script
 */
declare(strict_types=1);

class CSRFProtection {
    private static string $sessionKey = 'csrf_token';
    
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::$sessionKey] = $token;
        return $token;
    }
    
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION[self::$sessionKey] ?? self::generateToken();
    }
    
    public static function validateToken(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionToken = $_SESSION[self::$sessionKey] ?? '';
        return hash_equals($sessionToken, $token);
    }
    
    public static function requireValidToken(): void {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'CSRF token validation failed',
                'code' => 'CSRF_INVALID'
            ]);
            exit;
        }
    }
    
    public static function getTokenInput(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
