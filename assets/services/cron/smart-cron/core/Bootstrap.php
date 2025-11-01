<?php
/**
 * Smart Cron Bootstrap Class
 * 
 * Namespace-based bootstrap functionality for dependency injection and testing.
 * The original bootstrap.php remains for backward compatibility.
 * 
 * @package SmartCron\Core
 * @version 2.0.0
 */

declare(strict_types=1);

namespace SmartCron\Core;

class Bootstrap
{
    private static ?self $instance = null;
    private static bool $initialized = false;
    
    // Execution mode flags
    private bool $isCli;
    private bool $isAjax;
    private bool $isWeb;
    
    // Paths
    private string $rootPath;
    private string $logFile;
    
    private function __construct(string $rootPath = null)
    {
        $this->rootPath = $rootPath ?? dirname(dirname(__DIR__));
        $this->logFile = $this->rootPath . '/../../logs/bootstrap.log';
        
        // Detect execution mode
        $this->isCli = php_sapi_name() === 'cli' || PHP_SAPI === 'cli';
        $this->isAjax = !$this->isCli && (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (isset($_GET['action']) || isset($_POST['action']))
        );
        $this->isWeb = !$this->isCli && !$this->isAjax;
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(string $rootPath = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($rootPath);
        }
        return self::$instance;
    }
    
    /**
     * Initialize bootstrap (one-time setup)
     */
    public static function init(string $rootPath = null): void
    {
        if (self::$initialized) {
            return;
        }
        
        $instance = self::getInstance($rootPath);
        $instance->setupEnvironment();
        $instance->setupErrorHandling();
        $instance->ensureDirectories();
        
        self::$initialized = true;
    }
    
    /**
     * Setup environment (timezone, error reporting)
     */
    private function setupEnvironment(): void
    {
        // Set timezone
        date_default_timezone_set('Pacific/Auckland');
        
        // Error reporting based on mode
        error_reporting(E_ALL);
        if ($this->isCli) {
            ini_set('display_errors', '1');
            ini_set('log_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }
    }
    
    /**
     * Setup error handling
     */
    private function setupErrorHandling(): void
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            $this->handleError("PHP Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}");
            return true;
        });
        
        set_exception_handler(function (\Throwable $e) {
            $this->handleError("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        });
        
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
                $this->handleError("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
            }
        });
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $dirs = [
            dirname($this->logFile),
            $this->rootPath . '/logs',
            $this->rootPath . '/logs/locks',
            $this->rootPath . '/cache',
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                // Try to create directory, but don't fail if permissions denied
                // (directories should be pre-created during installation)
                if (!@mkdir($dir, 0755, true)) {
                    // Only log error in CLI mode (avoid web errors)
                    if ($this->isCli && !is_dir($dir)) {
                        error_log("Warning: Could not create directory: {$dir}");
                    }
                }
            }
        }
    }
    
    /**
     * Handle errors (mode-aware)
     */
    public function handleError(string $message, int $code = 1): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [BOOTSTRAP ERROR] {$message}\n";
        
        // Always log
        @file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        if ($this->isCli) {
            // CLI: Output to stderr and exit
            fwrite(STDERR, "\033[31m✗ ERROR:\033[0m {$message}\n");
            exit($code);
        } elseif ($this->isAjax) {
            // AJAX: JSON response
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $message,
                'timestamp' => $timestamp
            ]);
            exit;
        } else {
            // Web: HTML response
            http_response_code(500);
            echo "<!DOCTYPE html><html><head><title>Bootstrap Error</title></head><body>";
            echo "<h1>System Error</h1>";
            echo "<p>A critical error occurred during system initialization.</p>";
            echo "<p>Please contact the system administrator.</p>";
            echo "<p><small>Timestamp: {$timestamp}</small></p>";
            echo "</body></html>";
            exit;
        }
    }
    
    /**
     * Get database connection (MySQLi)
     */
    public function getDbConnection(): \mysqli
    {
        // Use constants defined by EnvLoader
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            $this->handleError("Database credentials not configured. Check .env file.");
        }
        
        $db = @new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT ?? 3306);
        
        if ($db->connect_error) {
            $this->handleError("Database connection failed: " . $db->connect_error);
        }
        
        // Set charset
        $db->set_charset('utf8mb4');
        
        return $db;
    }
    
    /**
     * Get database connection (PDO)
     */
    public function getPdoConnection(): \PDO
    {
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            $this->handleError("Database credentials not configured. Check .env file.");
        }
        
        $port = DB_PORT ?? 3306;
        $dsn = "mysql:host=" . DB_HOST . ";port={$port};dbname=" . DB_NAME . ";charset=utf8mb4";
        
        try {
            $pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            $this->handleError("PDO connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log message to bootstrap log
     */
    public static function log(string $message, string $level = 'INFO'): void
    {
        $instance = self::getInstance();
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        @file_put_contents($instance->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if running in CLI mode
     */
    public function isCli(): bool
    {
        return $this->isCli;
    }
    
    /**
     * Check if running in AJAX mode
     */
    public function isAjax(): bool
    {
        return $this->isAjax;
    }
    
    /**
     * Check if running in Web mode
     */
    public function isWeb(): bool
    {
        return $this->isWeb;
    }
    
    /**
     * Get root path
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }
    
    /**
     * Get log file path
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }
}

// Provide backward compatibility functions
if (!function_exists('getMysqliConnection')) {
    function getMysqliConnection(): \mysqli {
        return \SmartCron\Core\Bootstrap::getInstance()->getDbConnection();
    }
}

if (!function_exists('getPdoConnection')) {
    function getPdoConnection(): \PDO {
        return \SmartCron\Core\Bootstrap::getInstance()->getPdoConnection();
    }
}

if (!function_exists('handleBootstrapError')) {
    function handleBootstrapError(string $message, int $code = 1): void {
        \SmartCron\Core\Bootstrap::getInstance()->handleError($message, $code);
    }
}
