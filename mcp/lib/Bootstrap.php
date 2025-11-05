<?php
/**
 * MCP Bootstrap - Minimal initialization for API endpoints
 *
 * This file is required by all API endpoints in /mcp/api/
 * Provides: autoloading, config, database, error handling
 */

declare(strict_types=1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define root path
define('MCP_ROOT', dirname(__DIR__));

// Load environment variables from .env
$envFile = MCP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and decorative lines
        if (empty($line) || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove quotes
        $value = trim($value, '"\'');
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }
}

// Autoloader for namespaced classes
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    // IntelligenceHub\MCP\Database\Connection → src/Database/Connection.php
    $prefix = 'IntelligenceHub\\MCP\\';
    if (strpos($class, $prefix) !== 0) {
        return; // Not our namespace
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = MCP_ROOT . '/src/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load vendor autoload if exists (for Composer dependencies)
if (file_exists(MCP_ROOT . '/vendor/autoload.php')) {
    require_once MCP_ROOT . '/vendor/autoload.php';
}

// Helper function: Get environment variable
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}

// Helper function: JSON response
if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// Helper function: Error response
if (!function_exists('error_response')) {
    function error_response(string $message, int $status = 500, array $extra = []): void {
        json_response([
            'error' => $message,
            'status' => $status,
            'timestamp' => date('c')
        ] + $extra, $status);
    }
}

// Helper function: Generate unique request ID
if (!function_exists('new_request_id')) {
    function new_request_id(): string {
        return 'req_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    }
}

// Helper function: Get request ID (from header or generate new)
if (!function_exists('request_id')) {
    function request_id(): string {
        static $id = null;
        if ($id === null) {
            $id = $_SERVER['HTTP_X_REQUEST_ID'] ?? new_request_id();
        }
        return $id;
    }
}

// Helper function: Standard success envelope
if (!function_exists('envelope_ok')) {
    function envelope_ok($data, int $status = 200): array {
        return [
            'status' => 'success',
            'data' => $data,
            'request_id' => request_id(),
            'timestamp' => date('c')
        ];
    }
}

// Helper function: Standard error envelope (API format)
if (!function_exists('envelope_error')) {
    function envelope_error(string $code, string $message, string $rid, array $extra = [], int $status = 500): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'request_id' => $rid,
            'timestamp' => date('c')
        ] + $extra, JSON_PRETTY_PRINT);
        exit;
    }
}

// Helper function: Standard success envelope (API format)
if (!function_exists('envelope_success')) {
    function envelope_success($data, string $rid, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'request_id' => $rid,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// Helper function: Get PDO connection
if (!function_exists('get_pdo')) {
    function get_pdo(): PDO {
        static $pdo = null;
        if ($pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_NAME', 'hdgwrzntwa')
            );
            $pdo = new PDO(
                $dsn,
                env('DB_USER', 'hdgwrzntwa'),
                env('DB_PASS', ''),
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        }
        return $pdo;
    }
}

// Set timezone
date_default_timezone_set(env('TIMEZONE', 'Pacific/Auckland'));

// CORS headers (if needed)
if (env('ALLOW_CORS', 'false') === 'true') {
    header('Access-Control-Allow-Origin: ' . env('CORS_ORIGIN', '*'));
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Global error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Global exception handler
set_exception_handler(function ($exception) {
    error_log('Uncaught exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

    if (env('APP_DEBUG', 'false') === 'true') {
        error_response(
            $exception->getMessage(),
            500,
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]
        );
    } else {
        error_response('Internal server error', 500);
    }
});

// Bootstrap complete
return true;
