<?php

declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 * Sets up testing environment for AI Agent system
 * 
 * @package Tests
 * @author Ecigdis Limited (The Vape Shed)
 */

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define test constants
define('TEST_ENV', true);
define('AI_AGENT_ROOT', dirname(__DIR__));

// ============================================================================
// CRITICAL: Load .env BEFORE autoload to ensure Config has access
// ============================================================================
$envFile = AI_AGENT_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes
            $value = trim($value, '"\'');
            
            if (!empty($key)) {
                // Set in all PHP environment arrays
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load Composer autoloader AFTER .env is loaded
require_once AI_AGENT_ROOT . '/vendor/autoload.php';

// Override database settings for testing if needed
// Override database settings for testing if needed (optional)
if (isset($_ENV['TEST_DB_NAME'])) {
    $_ENV['DB_NAME'] = $_ENV['TEST_DB_NAME'];
    $_SERVER['DB_NAME'] = $_ENV['TEST_DB_NAME'];
    putenv('DB_NAME=' . $_ENV['TEST_DB_NAME']);
}

// Use test Redis prefix to avoid conflicts
if (!isset($_ENV['REDIS_PREFIX']) || $_ENV['REDIS_PREFIX'] !== 'test:aiagent:') {
    $_ENV['REDIS_PREFIX'] = 'test:aiagent:';
    $_SERVER['REDIS_PREFIX'] = 'test:aiagent:';
    putenv('REDIS_PREFIX=test:aiagent:');
}

// Verify critical environment variables are loaded
$requiredVars = ['MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE', 'MYSQL_HOST'];
$missing = [];
foreach ($requiredVars as $var) {
    if (empty($_ENV[$var]) && empty($_SERVER[$var]) && empty(getenv($var))) {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    echo "\n⚠️  WARNING: Missing required environment variables: " . implode(', ', $missing) . "\n";
    echo "Make sure .env file exists and is readable\n\n";
}

// Initialize test logging
use App\Logger;
try {
    Logger::info('PHPUnit test environment initialized', [
        'env' => 'test',
        'root_dir' => AI_AGENT_ROOT,
        'mysql_user' => $_ENV['MYSQL_USER'] ?? 'NOT SET',
        'mysql_host' => $_ENV['MYSQL_HOST'] ?? 'NOT SET',
        'redis_prefix' => $_ENV['REDIS_PREFIX'] ?? 'NOT SET'
    ]);
} catch (\Exception $e) {
    // Ignore logging errors during test setup
}

echo "✅ PHPUnit Bootstrap: Test environment initialized\n";
if (!empty($_ENV['MYSQL_USER'])) {
    echo "✅ Database user: {$_ENV['MYSQL_USER']}\n";
}
echo "\n";