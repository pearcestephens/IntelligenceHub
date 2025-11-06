<?php
/**
 * JSON Response Handler
 *
 * Standardized API response formatting
 */

declare(strict_types=1);

namespace MultiBot\Core;

class Response
{
    /**
     * Send success response
     */
    public static function success($data = null, string $message = '', int $code = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c'),
        ], $code);
    }

    /**
     * Send error response
     */
    public static function error(string $message, int $code = 400, ?array $details = null): void
    {
        self::json([
            'success' => false,
            'error' => $message,
            'details' => $details,
            'timestamp' => date('c'),
        ], $code);
    }

    /**
     * Send paginated response
     */
    public static function paginated(array $data, int $total, int $page, int $perPage): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ],
            'timestamp' => date('c'),
        ]);
    }

    /**
     * Send JSON response
     */
    private static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send 404 not found
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 404);
    }

    /**
     * Send 401 unauthorized
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * Send 403 forbidden
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * Send 500 internal server error
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }
}
