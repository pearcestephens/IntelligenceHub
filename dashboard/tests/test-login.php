<?php
/**
 * Test Login Flow
 */

// Simulate the login process
session_start();

define('DASHBOARD_ACCESS', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

echo "Testing Login Flow...\n\n";

// Test database connection
echo "1. Testing database connection...\n";
$db = getDbConnection();
if ($db) {
    echo "   ✅ Database connected successfully\n\n";
} else {
    echo "   ❌ Database connection failed\n\n";
}

// Test login function
echo "2. Testing login function...\n";
$result = loginUser('admin', 'admin123');
if ($result) {
    echo "   ✅ Login successful\n";
    echo "   Session data:\n";
    echo "   - authenticated: " . ($_SESSION['authenticated'] ? 'true' : 'false') . "\n";
    echo "   - username: " . ($_SESSION['username'] ?? 'not set') . "\n";
    echo "   - role: " . ($_SESSION['role'] ?? 'not set') . "\n\n";
} else {
    echo "   ❌ Login failed\n\n";
}

// Test authentication check
echo "3. Testing isAuthenticated()...\n";
if (isAuthenticated()) {
    echo "   ✅ User is authenticated\n\n";
} else {
    echo "   ❌ User is not authenticated\n\n";
}

// Test failed login
echo "4. Testing failed login...\n";
$result = loginUser('admin', 'wrongpassword');
if (!$result) {
    echo "   ✅ Failed login rejected correctly\n\n";
} else {
    echo "   ❌ Failed login was accepted (should not happen!)\n\n";
}

echo "All tests complete!\n";
