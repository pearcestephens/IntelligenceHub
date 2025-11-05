#!/usr/bin/env php
<?php
/**
 * System Health Check Tool
 *
 * Comprehensive system diagnostics for bot deployment platform
 *
 * Usage:
 *   php health-check.php
 *   php health-check.php --verbose
 *
 * @package BotDeployment\CLI
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Exceptions/DatabaseException.php';
require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Services/AIAgentService.php';

use BotDeployment\Config\Config;
use BotDeployment\Database\Connection;
use BotDeployment\Services\AIAgentService;

// CLI Colors
class CLI {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";

    public static function print($text, $color = '') {
        echo $color . $text . self::RESET . PHP_EOL;
    }

    public static function success($text) {
        self::print("✓ " . $text, self::GREEN);
    }

    public static function error($text) {
        self::print("✗ " . $text, self::RED);
    }

    public static function info($text) {
        self::print("ℹ " . $text, self::CYAN);
    }

    public static function warning($text) {
        self::print("⚠ " . $text, self::YELLOW);
    }

    public static function heading($text) {
        echo PHP_EOL;
        self::print(str_repeat("=", 60), self::BOLD);
        self::print($text, self::BOLD . self::CYAN);
        self::print(str_repeat("=", 60), self::BOLD);
        echo PHP_EOL;
    }
}

// Health check results
$checks = [];
$verbose = in_array('--verbose', $argv ?? []) || in_array('-v', $argv ?? []);

function addCheck($name, $status, $message, $details = []) {
    global $checks;
    $checks[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'details' => $details
    ];
}

try {
    CLI::heading("System Health Check");

    // 1. PHP Version Check
    CLI::info("Checking PHP version...");
    $phpVersion = PHP_VERSION;
    if (version_compare($phpVersion, '7.4.0', '>=')) {
        addCheck('PHP Version', 'pass', "PHP {$phpVersion}", ['version' => $phpVersion]);
        CLI::success("PHP {$phpVersion}");
    } else {
        addCheck('PHP Version', 'fail', "PHP {$phpVersion} (require >= 7.4)", ['version' => $phpVersion]);
        CLI::error("PHP {$phpVersion} - Requires PHP >= 7.4");
    }

    // 2. Required Extensions
    CLI::info("Checking PHP extensions...");
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
    $missingExtensions = [];

    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }

    if (empty($missingExtensions)) {
        addCheck('PHP Extensions', 'pass', 'All required extensions loaded', ['extensions' => $requiredExtensions]);
        CLI::success("All extensions loaded: " . implode(', ', $requiredExtensions));
    } else {
        addCheck('PHP Extensions', 'fail', 'Missing extensions: ' . implode(', ', $missingExtensions), [
            'missing' => $missingExtensions,
            'required' => $requiredExtensions
        ]);
        CLI::error("Missing extensions: " . implode(', ', $missingExtensions));
    }

    // 3. Database Connection
    CLI::info("Checking database connection...");
    try {
        $pdo = Connection::get();

        // Get database info
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        $stats = Connection::getStats();

        $health = [
            'driver' => $driver,
            'server_version' => $version,
            'pool_size' => $stats['pool_size'],
            'active_connections' => $stats['active_connections']
        ];

        addCheck('Database', 'pass', 'Connected', $health);
        CLI::success("Database connected - {$driver} {$version}");
        if ($verbose) {
            CLI::info("  Pool size: {$stats['pool_size']}");
            CLI::info("  Active connections: {$stats['active_connections']}");
        }

        Connection::release($pdo);
    } catch (Exception $e) {
        addCheck('Database', 'fail', $e->getMessage(), ['error' => $e->getMessage()]);
        CLI::error("Database error: " . $e->getMessage());
    }

    // 4. Database Tables
    CLI::info("Checking database tables...");
    try {
        $requiredTables = [
            'bot_deployments',
            'bot_schedules',
            'bot_execution_logs',
            'multi_thread_sessions',
            'conversation_threads',
            'thread_messages',
            'thread_merge_history'
        ];

        $pdo = Connection::get();
        $stmt = $pdo->query("SHOW TABLES");
        $existingTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        Connection::release($pdo);

        $missingTables = array_diff($requiredTables, $existingTables);

        if (empty($missingTables)) {
            addCheck('Database Tables', 'pass', 'All tables exist', [
                'tables' => $requiredTables,
                'count' => count($existingTables)
            ]);
            CLI::success("All required tables exist (" . count($requiredTables) . " tables)");
        } else {
            addCheck('Database Tables', 'fail', 'Missing tables: ' . implode(', ', $missingTables), [
                'missing' => $missingTables,
                'existing' => $existingTables
            ]);
            CLI::error("Missing tables: " . implode(', ', $missingTables));
        }
    } catch (Exception $e) {
        addCheck('Database Tables', 'fail', $e->getMessage(), ['error' => $e->getMessage()]);
        CLI::error("Table check failed: " . $e->getMessage());
    }

    // 5. File Permissions
    CLI::info("Checking file permissions...");
    $directories = [
        __DIR__ . '/logs',
        __DIR__ . '/cache',
        __DIR__ . '/uploads'
    ];

    $permissionIssues = [];
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            $permissionIssues[] = $dir;
        }
    }

    if (empty($permissionIssues)) {
        addCheck('File Permissions', 'pass', 'All directories writable', ['directories' => $directories]);
        CLI::success("All directories writable");
    } else {
        addCheck('File Permissions', 'warning', 'Some directories not writable', [
            'issues' => $permissionIssues,
            'directories' => $directories
        ]);
        CLI::warning("Not writable: " . implode(', ', $permissionIssues));
    }

    // 6. AI Agent Connection
    CLI::info("Checking AI Agent connection...");
    try {
        $aiConfig = Config::aiAgent();

        // Simple connectivity test
        $ch = curl_init($aiConfig['endpoint']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'message' => 'Health check ping',
                'model' => 'gpt-5-turbo'
            ]),
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $aiConfig['api_key']
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            addCheck('AI Agent', 'pass', 'Connected', [
                'endpoint' => $aiConfig['endpoint'],
                'http_code' => $httpCode
            ]);
            CLI::success("AI Agent connected (HTTP {$httpCode})");
        } else {
            addCheck('AI Agent', 'warning', "HTTP {$httpCode}", [
                'endpoint' => $aiConfig['endpoint'],
                'http_code' => $httpCode
            ]);
            CLI::warning("AI Agent responded with HTTP {$httpCode}");
        }
    } catch (Exception $e) {
        $aiConfig = Config::aiAgent();
        addCheck('AI Agent', 'fail', $e->getMessage(), [
            'endpoint' => $aiConfig['endpoint'],
            'error' => $e->getMessage()
        ]);
        CLI::error("AI Agent connection failed: " . $e->getMessage());
    }

    // 7. Memory Usage
    CLI::info("Checking memory usage...");
    $memoryLimit = ini_get('memory_limit');
    $memoryUsage = memory_get_usage(true);
    $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);

    addCheck('Memory Usage', 'info', "{$memoryUsageMB}MB used (limit: {$memoryLimit})", [
        'used_bytes' => $memoryUsage,
        'used_mb' => $memoryUsageMB,
        'limit' => $memoryLimit
    ]);
    CLI::info("Memory: {$memoryUsageMB}MB used (limit: {$memoryLimit})");

    // Summary
    echo PHP_EOL;
    CLI::heading("Health Check Summary");

    $passCount = 0;
    $failCount = 0;
    $warningCount = 0;

    foreach ($checks as $check) {
        switch ($check['status']) {
            case 'pass':
                CLI::success($check['name'] . ": " . $check['message']);
                $passCount++;
                break;
            case 'fail':
                CLI::error($check['name'] . ": " . $check['message']);
                $failCount++;
                break;
            case 'warning':
                CLI::warning($check['name'] . ": " . $check['message']);
                $warningCount++;
                break;
            case 'info':
                CLI::info($check['name'] . ": " . $check['message']);
                break;
        }

        if ($verbose && !empty($check['details'])) {
            foreach ($check['details'] as $key => $value) {
                if (is_array($value)) {
                    CLI::info("  {$key}: " . json_encode($value));
                } else {
                    CLI::info("  {$key}: {$value}");
                }
            }
        }
    }

    echo PHP_EOL;
    CLI::info("Total checks: " . count($checks));
    CLI::success("Passed: " . $passCount);
    if ($warningCount > 0) {
        CLI::warning("Warnings: " . $warningCount);
    }
    if ($failCount > 0) {
        CLI::error("Failed: " . $failCount);
    }

    echo PHP_EOL;
    if ($failCount > 0) {
        CLI::error("System health check FAILED");
        exit(1);
    } elseif ($warningCount > 0) {
        CLI::warning("System health check completed with warnings");
        exit(0);
    } else {
        CLI::success("System health check PASSED");
        exit(0);
    }

} catch (Exception $e) {
    CLI::error("Health check fatal error: " . $e->getMessage());
    if (in_array('--debug', $argv ?? [])) {
        echo PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
    exit(1);
}
