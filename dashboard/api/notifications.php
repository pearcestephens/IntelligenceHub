<?php
/**
 * Notifications API stub
 * Returns recent notifications for the dashboard.
 */
// Start session if not already started (avoids notices when included)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Allow safe include of shared files
if (!defined('DASHBOARD_ACCESS')) {
    define('DASHBOARD_ACCESS', true);
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Minimal stub - return empty notifications list
    echo json_encode([
        'success' => true,
        'data' => [],
        'count' => 0,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
