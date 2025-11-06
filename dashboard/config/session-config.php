<?php
/**
 * Unified Session Configuration
 * Ensures consistent session handling across all CIS applications
 *
 * @package CIS Intelligence
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('DASHBOARD_ACCESS')) {
    die('Direct access not permitted');
}

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================

// IMPORTANT: Configure session settings BEFORE starting the session
// Only set these if session hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set unified session name for all CIS applications
    // This allows authentication to work across staff.vapeshed.co.nz and gpt.ecigdis.co.nz
    ini_set('session.name', 'CIS_SESSION');

    // Detect domain and set appropriate cookie domain
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (strpos($host, 'ecigdis.co.nz') !== false) {
        $cookieDomain = '.ecigdis.co.nz'; // For gpt.ecigdis.co.nz
    } elseif (strpos($host, 'vapeshed.co.nz') !== false) {
        $cookieDomain = '.vapeshed.co.nz'; // For staff.vapeshed.co.nz
    } else {
        $cookieDomain = ''; // No domain restriction (use current domain)
    }

    // Session cookie configuration
    ini_set('session.cookie_lifetime', '86400');        // 24 hours
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', $cookieDomain);
    // Only use secure cookies if we're actually on HTTPS
    ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? '1' : '0');
    ini_set('session.cookie_httponly', '1');            // Prevent JavaScript access
    ini_set('session.cookie_samesite', 'Lax');        // CSRF protection

    // Session security settings
    ini_set('session.use_strict_mode', '1');            // Reject uninitialized session IDs
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');              // No session ID in URLs

    // Session save handler
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', '/tmp');

    // Garbage collection
    ini_set('session.gc_maxlifetime', '86400');         // 24 hours
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');

    // Session hash function
    ini_set('session.hash_function', 'sha256');
    ini_set('session.hash_bits_per_character', '5');
}

// ============================================================================
// SESSION START
// ============================================================================

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // Regenerate session ID periodically for security (but NOT on login page to preserve CSRF token)
    if (!defined('SKIP_AUTO_SESSION_REGENERATION')) {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // Every 30 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// ============================================================================
// SESSION HELPER FUNCTIONS
// ============================================================================

/**
 * Set a session value
 */
function session_set($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get a session value
 */
function session_get($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Check if session key exists
 */
function session_has($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove a session value
 */
function session_remove($key) {
    unset($_SESSION[$key]);
}

/**
 * Clear all session data
 */
function session_clear() {
    $_SESSION = [];
}

/**
 * Destroy session completely
 */
function session_destroy_complete() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
