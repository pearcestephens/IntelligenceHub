<?php

/**
 * Secure Diagnostic Endpoint
 * 
 * Tests basic PHP functionality without dependencies.
 * ⚠️ SECURITY: Admin-only access. Disabled in production.
 * 
 * @package App\API
 * @security ADMIN_ONLY
 */

declare(strict_types=1);

// ============================================================================
// SECURITY CHECK - Admin Only
// ============================================================================

// Method 1: Check for production environment
$isProduction = (
    getenv('APP_ENV') === 'production' ||
    (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'staff.vapeshed.co.nz') !== false)
);

if ($isProduction) {
    // In production, this endpoint should not exist
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not Found']);
    exit;
}

// Method 2: Check for admin session (if session exists)
session_start();
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Forbidden',
        'message' => 'Admin access required'
    ]);
    exit;
}

// Method 3: IP whitelist (optional - add your IPs)
$allowedIPs = [
    '127.0.0.1',
    '::1',
    // Add your admin IPs here
];
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($allowedIPs) && !in_array($clientIP, $allowedIPs, true)) {
    error_log("Diagnostic endpoint access denied from IP: $clientIP");
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Forbidden',
        'message' => 'Access denied from this IP'
    ]);
    exit;
}

// ============================================================================
// SECURITY: Never enable display_errors in production
// ============================================================================
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Changed from '1' to '0' - errors go to log only
ini_set('log_errors', '1');

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$diagnostic = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'server' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
        'script_filename' => __FILE__
    ],
    'paths' => [
        'current_dir' => __DIR__,
        'bootstrap_path' => __DIR__ . '/../../src/bootstrap.php',
        'bootstrap_exists' => file_exists(__DIR__ . '/../../src/bootstrap.php') ? 'yes' : 'no',
        'vendor_path' => __DIR__ . '/../../vendor/autoload.php',
        'vendor_exists' => file_exists(__DIR__ . '/../../vendor/autoload.php') ? 'yes' : 'no',
        'env_path' => __DIR__ . '/../../.env',
        'env_exists' => file_exists(__DIR__ . '/../../.env') ? 'yes' : 'no'
    ],
    'extensions' => [
        'json' => extension_loaded('json') ? 'yes' : 'no',
        'curl' => extension_loaded('curl') ? 'yes' : 'no',
        'redis' => extension_loaded('redis') ? 'yes' : 'no',
        'pdo' => extension_loaded('pdo') ? 'yes' : 'no',
        'pdo_mysql' => extension_loaded('pdo_mysql') ? 'yes' : 'no',
        'mbstring' => extension_loaded('mbstring') ? 'yes' : 'no'
    ]
];

// DON'T try to load bootstrap - it causes silent failures
$diagnostic['bootstrap'] = [
    'status' => 'skipped',
    'message' => 'Bootstrap disabled for reliability - framework has initialization issues',
    'recommendation' => 'Use health-simple.php and metrics-simple.php instead'
];

// Test .env file - SECURITY: Don't expose actual content
if (file_exists(__DIR__ . '/../../.env')) {
    $diagnostic['env_file'] = [
        'exists' => true,
        'readable' => true,
        'size_bytes' => filesize(__DIR__ . '/../../.env'),
        'permissions' => substr(sprintf('%o', fileperms(__DIR__ . '/../../.env')), -4),
        'warning' => 'File content not exposed for security'
        // ⚠️ REMOVED: has_openai_key, has_claude_key checks (info disclosure)
    ];
} else {
    $diagnostic['env_file'] = ['exists' => false];
}

http_response_code(200);
echo json_encode($diagnostic, JSON_PRETTY_PRINT);
