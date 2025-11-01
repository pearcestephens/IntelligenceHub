<?php
/**
 * Test file with intentional security vulnerabilities
 * Used for testing Scanner auto-fix functionality
 */

declare(strict_types=1);

// SQL Injection vulnerability (SEC001)
function getUserById($id) {
    global $pdo;
    $query = "SELECT * FROM users WHERE id = " . $_GET['id']; // VULNERABLE!
    $result = $pdo->query($query);
    return $result->fetch();
}

// XSS vulnerability (SEC002)
function displayMessage() {
    echo "<div>Message: " . $_GET['message'] . "</div>"; // VULNERABLE!
}

// Hardcoded credentials (SEC003)
$db_password = "hardcoded123"; // VULNERABLE!
$api_key = "sk_live_123456789"; // VULNERABLE!

// More SQL injection examples
function login($username, $password) {
    global $pdo;
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'"; // VULNERABLE!
    return $pdo->query($sql);
}
