<?php

/**
 * Bootstrap file for PHP AI Agent
 * Loads environment, autoloader, and initializes core services
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

use App\Config;
use App\Logger;
use App\DB;
use App\RedisClient;

// Set error reporting for production grade handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set timezone to Pacific/Auckland (CIS standard)
date_default_timezone_set('Pacific/Auckland');

// Load Composer autoloader
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    http_response_code(500);
    die('Composer dependencies not installed. Run: composer install');
}

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Exception $e) {
    // Environment file not found - use defaults where possible
    error_log("Warning: .env file not found - using environment defaults");
}

// Initialize configuration
try {
    Config::initialize();
} catch (\Exception $e) {
    http_response_code(500);
    error_log("Configuration error: " . $e->getMessage());
    die('Configuration error occurred');
}

// Initialize logger
Logger::initialize();

// Set custom error handler
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    Logger::error('PHP Error', [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ]);

    return true;
});

// Set exception handler
set_exception_handler(function (\Throwable $exception) {
    Logger::error('Uncaught Exception', [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => 'An internal error occurred',
                'correlationId' => $_SERVER['REQUEST_ID'] ?? uniqid()
            ]
        ]);
    }
});

// Set CORS headers for agent endpoints
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/agent/') === 0) {
    $allowedOrigins = Config::get('CORS_ALLOWED_ORIGINS', 'same-origin');

    if ($allowedOrigins === 'same-origin') {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && parse_url($origin, PHP_URL_HOST) === $_SERVER['HTTP_HOST']) {
            header("Access-Control-Allow-Origin: $origin");
        }
    } elseif ($allowedOrigins !== '') {
        $origins = explode(',', $allowedOrigins);
        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($requestOrigin, $origins, true)) {
            header("Access-Control-Allow-Origin: $requestOrigin");
        }
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Conversation-Id, X-Request-Id');
    header('Access-Control-Allow-Credentials: true');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Generate request ID for correlation
if (!isset($_SERVER['REQUEST_ID'])) {
    $_SERVER['REQUEST_ID'] = uniqid('req_', true);
}

// Start session if needed (for rate limiting by session)
if (!session_id() && strpos($_SERVER['REQUEST_URI'] ?? '', '/agent/api/') === 0) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true
    ]);
}

Logger::info('Bootstrap completed', [
    'request_id' => $_SERVER['REQUEST_ID'],
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI'
]);
