<?php
/**
 * CSRF Protection Helper
 * 
 * Provides session-based CSRF token generation and validation
 * 
 * 🔒 CRITICAL FIX #6: CSRF PROTECTION IMPLEMENTATION
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class CsrfProtection
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_TIMESTAMP_KEY = 'csrf_token_timestamp';
    private const TOKEN_LIFETIME = 3600; // 1 hour
    
    /**
     * Generate a new CSRF token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::TOKEN_TIMESTAMP_KEY] = time();
        
        return $token;
    }
    
    /**
     * Get existing token or generate new one
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists and is not expired
        if (isset($_SESSION[self::TOKEN_KEY]) && isset($_SESSION[self::TOKEN_TIMESTAMP_KEY])) {
            $age = time() - $_SESSION[self::TOKEN_TIMESTAMP_KEY];
            if ($age < self::TOKEN_LIFETIME) {
                return $_SESSION[self::TOKEN_KEY];
            }
        }
        
        // Generate new token if expired or doesn't exist
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token from request
     * 
     * @param string $token Token to validate
     * @param bool $deleteAfterValidation Delete token after successful validation (one-time use)
     * @return bool True if valid, false otherwise
     */
    public static function validateToken(string $token, bool $deleteAfterValidation = false): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists in session
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            error_log('[CSRF] No token in session');
            return false;
        }
        
        // Check token age
        if (isset($_SESSION[self::TOKEN_TIMESTAMP_KEY])) {
            $age = time() - $_SESSION[self::TOKEN_TIMESTAMP_KEY];
            if ($age >= self::TOKEN_LIFETIME) {
                error_log('[CSRF] Token expired (age: ' . $age . 's)');
                self::clearToken();
                return false;
            }
        }
        
        // Compare tokens using timing-safe comparison
        $valid = hash_equals($_SESSION[self::TOKEN_KEY], $token);
        
        if (!$valid) {
            error_log('[CSRF] Token mismatch');
        }
        
        // Delete token after successful validation if requested
        if ($valid && $deleteAfterValidation) {
            self::clearToken();
        }
        
        return $valid;
    }
    
    /**
     * Validate CSRF token from POST request
     */
    public static function validateRequest(bool $deleteAfterValidation = false): bool
    {
        // Check POST parameter
        $postToken = $_POST['csrf_token'] ?? '';
        if (!empty($postToken)) {
            return self::validateToken($postToken, $deleteAfterValidation);
        }
        
        // Check header (for AJAX requests)
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!empty($headerToken)) {
            return self::validateToken($headerToken, $deleteAfterValidation);
        }
        
        error_log('[CSRF] No token in request');
        return false;
    }
    
    /**
     * Clear CSRF token from session
     */
    public static function clearToken(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::TOKEN_KEY]);
        unset($_SESSION[self::TOKEN_TIMESTAMP_KEY]);
    }
    
    /**
     * Generate hidden input field for forms
     */
    public static function getHiddenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Generate meta tag for AJAX requests
     */
    public static function getMetaTag(): string
    {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Get JavaScript snippet for embedding token
     */
    public static function getJavaScript(): string
    {
        $token = self::getToken();
        return "const CSRF_TOKEN = '" . addslashes($token) . "';";
    }
}
