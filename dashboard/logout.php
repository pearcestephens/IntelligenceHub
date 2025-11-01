<?php
/**
 * Logout Handler
 */
// Define access constant
define('DASHBOARD_ACCESS', true);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Log the logout BEFORE destroying session
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user_id'] ?? 0,
            'user_logout',
            json_encode(['username' => $username]),
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch (Exception $e) {
        // Silently fail - logout should still work
    }
}

// Now destroy the session
session_destroy();

// Redirect to login
header('Location: login.php?logout=1');
exit;
