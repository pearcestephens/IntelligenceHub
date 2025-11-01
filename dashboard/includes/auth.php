<?php
/**
 * Authentication Functions
 */

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        return false;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Login user
 */
function loginUser($username, $password) {
    // Simple authentication - replace with proper user system
    // For development: username=admin, password=admin123

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['authenticated'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'administrator';
        $_SESSION['permissions'] = ['*']; // All permissions
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();

        // Log activity without using logActivity function (to avoid dependency issues)
        try {
            $db = getDbConnection();
            if ($db) {
                // Check if table exists first
                $tableCheck = $db->query("SHOW TABLES LIKE 'activity_logs'")->fetch();
                if ($tableCheck) {
                    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        1,
                        'user_login',
                        json_encode(['username' => $username]),
                        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    ]);
                }
            }
        } catch (Exception $e) {
            // Silently fail - login should still work
            error_log("Login logging failed: " . $e->getMessage());
        }

        return true;
    }

    // Log failed attempt
    try {
        $db = getDbConnection();
        if ($db) {
            $tableCheck = $db->query("SHOW TABLES LIKE 'activity_logs'")->fetch();
            if ($tableCheck) {
                $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    0,
                    'login_failed',
                    json_encode(['username' => $username]),
                    $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                ]);
            }
        }
    } catch (Exception $e) {
        // Silently fail
        error_log("Failed login logging failed: " . $e->getMessage());
    }

    return false;
}

/**
 * Logout user
 */
function logoutUser() {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        try {
            $db = getDbConnection();
            if ($db) {
                $tableCheck = $db->query("SHOW TABLES LIKE 'activity_logs'")->fetch();
                if ($tableCheck) {
                    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $_SESSION['user_id'] ?? 0,
                        'user_logout',
                        json_encode(['username' => $username]),
                        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    ]);
                }
            }
        } catch (Exception $e) {
            // Silently fail
            error_log("Logout logging failed: " . $e->getMessage());
        }
    }

    session_destroy();
    return true;
}/**
 * Require authentication
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}
