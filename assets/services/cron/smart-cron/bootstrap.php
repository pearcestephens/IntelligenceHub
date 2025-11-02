<?php
/**
 * Smart Cron Bootstrap for Intelligence Hub
 *
 * Database connection and environment setup for smart-cron system
 *
 * @package SmartCron
 * @version 1.0.0
 */

declare(strict_types=1);

// Define constants
if (!defined('SMART_CRON_ROOT')) {
    define('SMART_CRON_ROOT', __DIR__);
}

// Load main app configuration
require_once dirname(__DIR__, 4) . '/app.php';

// Load PSR-4 autoloader for SmartCron classes
$autoloaderPath = dirname(__DIR__) . '/autoloader.php';
error_log("[BOOTSTRAP] Loading autoloader from: {$autoloaderPath}");
error_log("[BOOTSTRAP] Autoloader exists: " . (file_exists($autoloaderPath) ? 'YES' : 'NO'));
require_once $autoloaderPath;
error_log("[BOOTSTRAP] Autoloader loaded successfully");

/**
 * Get database connection for smart-cron system
 * Uses hdgwrzntwa database credentials
 */
function getMysqliConnection(): ?mysqli
{
    static $connection = null;

    if ($connection === null) {
        // Load database config
        $configFile = dirname(__DIR__, 4) . '/config/database.php';
        if (!file_exists($configFile)) {
            error_log("Smart Cron: Database config not found at: " . $configFile);
            return null;
        }

        $config = require $configFile;

        // Create mysqli connection
        $connection = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port'] ?? 3306
        );

        if ($connection->connect_error) {
            error_log("Smart Cron: Database connection failed: " . $connection->connect_error);
            return null;
        }

        // Set charset
        $connection->set_charset($config['charset'] ?? 'utf8mb4');
    }

    return $connection;
}

/**
 * Get PDO connection for smart-cron system
 */
function getPDOConnection(): ?PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $configFile = dirname(__DIR__, 4) . '/config/database.php';
        if (!file_exists($configFile)) {
            error_log("Smart Cron: Database config not found at: " . $configFile);
            return null;
        }

        $config = require $configFile;

        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=" . ($config['charset'] ?? 'utf8mb4');
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Smart Cron: PDO connection failed: " . $e->getMessage());
            return null;
        }
    }

    return $pdo;
}

/**
 * Log smart-cron messages
 */
function smart_cron_log(string $message, string $level = 'INFO'): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

    // Log to smart-cron specific log
    $logFile = __DIR__ . '/logs/smart-cron.log';
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);

    // Also log to PHP error log for critical messages
    if (in_array($level, ['ERROR', 'CRITICAL'])) {
        error_log("Smart Cron [{$level}]: {$message}");
    }
}

// Set memory and time limits for safety
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300'); // 5 minutes max

// Ensure logs directory exists
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

smart_cron_log("Smart Cron bootstrap loaded for Intelligence Hub");
