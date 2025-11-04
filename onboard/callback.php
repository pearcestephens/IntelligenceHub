<?php
/**
 * GitHub OAuth Callback Handler
 * Exchanges code for access token and retrieves user info
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants (don't include index.php to avoid double session start)
define('GITHUB_CLIENT_ID', 'Ov23li1TEjksyfgzzxaw');
define('GITHUB_CLIENT_SECRET', '307ba7874f912cb33fb0621044c99ff2d3cc47a6');

// Verify state to prevent CSRF
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    die('❌ Invalid state parameter. Possible CSRF attack.');
}

if (!isset($_GET['code'])) {
    die('❌ No authorization code received from GitHub.');
}

// Exchange authorization code for access token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id' => GITHUB_CLIENT_ID,
        'client_secret' => GITHUB_CLIENT_SECRET,
        'code' => $_GET['code'],
        'state' => $_GET['state']
    ]),
    CURLOPT_HTTPHEADER => ['Accept: application/json']
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    echo '<h2>❌ Failed to get access token</h2>';
    echo '<pre>' . htmlspecialchars(print_r($token_data, true)) . '</pre>';
    echo '<p><a href="index.php?stage=github">← Try Again</a></p>';
    exit;
}

$_SESSION['github_token'] = $token_data['access_token'];

// Get user information
$ch = curl_init('https://api.github.com/user');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token_data['access_token'],
        'User-Agent: AI-Agent-Onboarding',
        'Accept: application/vnd.github.v3+json'
    ]
]);

$user_response = curl_exec($ch);
curl_close($ch);

$user_data = json_decode($user_response, true);

if (!isset($user_data['login'])) {
    echo '<h2>❌ Failed to get user information</h2>';
    echo '<pre>' . htmlspecialchars(print_r($user_data, true)) . '</pre>';
    echo '<p><a href="index.php?stage=github">← Try Again</a></p>';
    exit;
}

// Get user's email addresses
$ch = curl_init('https://api.github.com/user/emails');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token_data['access_token'],
        'User-Agent: AI-Agent-Onboarding',
        'Accept: application/vnd.github.v3+json'
    ]
]);

$emails_response = curl_exec($ch);
curl_close($ch);

$emails = json_decode($emails_response, true);

// Find primary email
$primary_email = $user_data['email'] ?? null;
if (is_array($emails)) {
    foreach ($emails as $email) {
        if ($email['primary'] ?? false) {
            $primary_email = $email['email'];
            break;
        }
    }
}

$user_data['email'] = $primary_email;

// Store user data in session
$_SESSION['github_user'] = $user_data;

// Clean up OAuth state
unset($_SESSION['oauth_state']);

// Redirect back to GitHub connection page
header('Location: index.php?stage=github');
exit;
