<?php
/**
 * Application Bootstrap
 * Complete bootstrap file for CIS Intelligence Hub
 * Sets up database, environment, and all necessary globals
 * 
 * @package CIS Intelligence Hub
 * @version 2.0.0
 */

// Prevent multiple includes
if (defined('APP_BOOTSTRAPPED')) {
    return true;
}
define('APP_BOOTSTRAPPED', true);

// Define base paths
if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}
if (!defined('PUBLIC_ROOT')) {
    define('PUBLIC_ROOT', __DIR__);
}

// Autoload if composer exists
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Load environment variables from multiple possible locations
$envLocations = [
    __DIR__ . '/.env',
    __DIR__ . '/../private_html/config/.env',
    __DIR__ . '/config/.env'
];

foreach ($envLocations as $envFile) {
    if (file_exists($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments and invalid lines
            if (empty($line) || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!empty($key)) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        break; // Use first found .env file
    }
}

// Database Configuration Constants
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'hdgwrzntwa');
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DB_USER'] ?? 'hdgwrzntwa');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $_ENV['DB_PASS'] ?? 'bFUdRjh4Jx');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
}

/**
 * Get database connection (singleton pattern)
 * Returns global $db connection for legacy compatibility
 */
function getDatabase() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_TIMEOUT => 5
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            // Return null instead of throwing to allow graceful degradation
            $db = null;
        }
    }
    
    return $db;
}

// Create global $db for legacy scripts that expect it
$GLOBALS['db'] = getDatabase();
$db = &$GLOBALS['db'];

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Pacific/Auckland');

// Error reporting for production
if (($_ENV['APP_ENVIRONMENT'] ?? 'production') === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

return true;
