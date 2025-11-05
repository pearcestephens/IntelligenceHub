<?php
/**
 * HTTP Response Handler
 *
 * Consistent JSON response formatting with error envelopes
 *
 * @package BotDeployment\Http
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Http;

class Response
{
    /**
     * Send JSON response
     */
    public static function json(array $data, int $status = 200): void
    {
        self::setHeaders($status);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send success response
     */
    public static function success($data = null, string $message = 'Success', int $status = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ], $status);
    }

    /**
     * Send error response
     */
    public static function error(string $message, int $status = 400, array $details = []): void
    {
        self::json([
            'success' => false,
            'error' => $message,
            'details' => $details,
            'timestamp' => time()
        ], $status);
    }

    /**
     * Send validation error response
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::json([
            'success' => false,
            'error' => $message,
            'validation_errors' => $errors,
            'timestamp' => time()
        ], 422);
    }

    /**
     * Send created response (201)
     */
    public static function created($data, string $message = 'Resource created'): void
    {
        self::success($data, $message, 201);
    }

    /**
     * Send no content response (204)
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Send unauthorized response (401)
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send forbidden response (403)
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send not found response (404)
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send method not allowed response (405)
     */
    public static function methodNotAllowed(string $message = 'Method not allowed'): void
    {
        self::error($message, 405);
    }

    /**
     * Send too many requests response (429)
     */
    public static function tooManyRequests(string $message = 'Too many requests', int $retryAfter = 60): void
    {
        header("Retry-After: {$retryAfter}");
        self::error($message, 429, ['retry_after' => $retryAfter]);
    }

    /**
     * Send server error response (500)
     */
    public static function serverError(string $message = 'Internal server error', array $details = []): void
    {
        self::error($message, 500, $details);
    }

    /**
     * Set response headers
     */
    private static function setHeaders(int $status): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');

        // CORS headers (configure as needed)
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    }

    /**
     * Handle OPTIONS preflight request
     */
    public static function handlePreflight(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setHeaders(200);
            exit;
        }
    }
}
