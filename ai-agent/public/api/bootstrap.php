<?php
/**
 * API Bootstrap - Shared initialization for all API endpoints
 * 
 * Provides centralized configuration, database, Redis, authentication,
 * JSON response handling, CORS, and error management for Gate 4 APIs.
 * 
 * @author Gate 4 Implementation
 * @version 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access forbidden');
}

// Set secure headers first
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Load dependencies
require_once __DIR__ . '/../../src/bootstrap.php';

use App\Config;
use App\DB;
use App\RedisClient;
use App\Logger;
use App\Util\Errors;

class ApiBootstrap
{
    private static ?Config $config = null;
    private static ?DB $db = null;
    private static ?RedisClient $redis = null;
    private static ?Logger $logger = null;
    private static ?string $requestId = null;
    
    /**
     * Initialize API environment
     */
    public static function init(): void
    {
        // Generate request ID
        self::$requestId = bin2hex(random_bytes(8));
        header('X-Request-ID: ' . self::$requestId);
        
        // Set content type for JSON APIs
        header('Content-Type: application/json; charset=utf-8');
        
        // Handle CORS
        self::handleCors();
        
        // Initialize core services
        self::$config = new Config();
        self::$db = new DB();
        self::$redis = new RedisClient();
        self::$logger = new Logger();
        
        // Set error handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }
    
    /**
     * Handle CORS preflight and headers
     */
    private static function handleCors(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        $allowed = self::getAllowedOrigins();

        if ($origin) {
            if ($allowed === 'same-origin') {
                $host = parse_url($origin, PHP_URL_HOST);
                if ($host && isset($_SERVER['HTTP_HOST']) && $host === $_SERVER['HTTP_HOST']) {
                    header("Access-Control-Allow-Origin: {$origin}");
                }
            } elseif (is_array($allowed) && in_array($origin, $allowed, true)) {
                header("Access-Control-Allow-Origin: {$origin}");
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Request-ID, X-Idempotency-Key');
        header('Access-Control-Expose-Headers: X-Request-ID');
        header('Access-Control-Allow-Credentials: true');
        
        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Get allowed CORS origins from config
     */
    private static function getAllowedOrigins(): array|string
    {
        $config = new Config();
        $origins = $config->get('CORS_ALLOWED_ORIGINS', 'same-origin');
        if ($origins === 'same-origin') {
            return 'same-origin';
        }
        $origins = trim((string)$origins);
        if ($origins === '') {
            return [];
        }
        return array_map('trim', explode(',', $origins));
    }
    
    /**
     * Send JSON response with standard envelope
     */
    public static function respond(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        
        $envelope = [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'request_id' => self::$requestId,
        ];
        
        if ($envelope['success']) {
            $envelope['data'] = $data;
        } else {
            $envelope['error'] = $data;
        }
        
        echo json_encode($envelope, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function error(string $message, int $code = 400, ?string $type = null, ?array $details = null): void
    {
        $error = [
            'message' => $message,
            'type' => $type ?? 'validation_error',
        ];
        
        if ($details !== null) {
            $error['details'] = $details;
        }
        
        // Log error
        self::getLogger()->error('API Error', [
            'request_id' => self::$requestId,
            'error' => $error,
            'http_code' => $code,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        ]);
        
        self::respond($error, $code);
    }
    
    /**
     * Get parsed JSON body
     */
    public static function getJsonBody(): array
    {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            return [];
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::error('Invalid JSON in request body', 400, 'parse_error');
        }
        
        return $data ?? [];
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired(array $data, array $requiredFields): void
    {
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            self::error(
                'Missing required fields: ' . implode(', ', $missing),
                400,
                'validation_error',
                ['missing_fields' => $missing]
            );
        }
    }
    
    /**
     * Get config instance
     */
    public static function getConfig(): Config
    {
        return self::$config ?? new Config();
    }
    
    /**
     * Get database instance
     */
    public static function getDb(): DB
    {
        return self::$db ?? new DB();
    }
    
    /**
     * Get Redis instance
     */
    public static function getRedis(): RedisClient
    {
        return self::$redis ?? new RedisClient();
    }
    
    /**
     * Get logger instance
     */
    public static function getLogger(): Logger
    {
        return self::$logger ?? new Logger();
    }
    
    /**
     * Get current request ID
     */
    public static function getRequestId(): string
    {
        return self::$requestId ?? 'unknown';
    }
    
    /**
     * Error handler
     */
    public static function handleError(int $severity, string $message, string $file, int $line): void
    {
        if (!(error_reporting() & $severity)) {
            return;
        }
        
        self::error(
            'Internal server error',
            500,
            'server_error',
            [
                'error' => $message,
                'file' => basename($file),
                'line' => $line,
            ]
        );
    }
    
    /**
     * Exception handler
     */
    public static function handleException(Throwable $exception): void
    {
        self::getLogger()->error('Uncaught Exception', [
            'request_id' => self::$requestId,
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);
        
        self::error(
            'Internal server error',
            500,
            'server_error',
            ['message' => $exception->getMessage()]
        );
    }
}