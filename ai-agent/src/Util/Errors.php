<?php

/**
 * Error handling and response envelope utilities
 * Provides consistent error responses and safe error messages
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

use App\Logger;
use App\Config;

class Errors
{
    // Standard error codes
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const NOT_FOUND = 'NOT_FOUND';
    public const UNAUTHORIZED = 'UNAUTHORIZED';
    public const FORBIDDEN = 'FORBIDDEN';
    public const RATE_LIMITED = 'RATE_LIMITED';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    public const TIMEOUT = 'TIMEOUT';
    public const TOOL_ERROR = 'TOOL_ERROR';
    public const OPENAI_ERROR = 'OPENAI_ERROR';
    public const DATABASE_ERROR = 'DATABASE_ERROR';
    public const REDIS_ERROR = 'REDIS_ERROR';

    public static function internalError(string $message): \RuntimeException
    {
        return new \RuntimeException($message);
    }

    public static function validationError(string $message): \InvalidArgumentException
    {
        return new \InvalidArgumentException($message);
    }

    public static function conversationError(string $message): \RuntimeException
    {
        return new \RuntimeException($message);
    }

    public static function rateLimitError(string $message): \RuntimeException
    {
        return new \RuntimeException($message);
    }

    /**
     * Initialization error factory (used by Agent and others)
     */
    public static function initializationError(string $message): \RuntimeException
    {
        return new \RuntimeException($message);
    }

    /**
     * Configuration error factory (invalid/missing config)
     */
    public static function configurationError(string $message): \InvalidArgumentException
    {
        return new \InvalidArgumentException($message);
    }

    /**
     * Processing error factory (message processing failures)
     */
    public static function processingError(string $message): \RuntimeException
    {
        return new \RuntimeException($message);
    }

    /**
     * Create error response envelope
     */
    public static function envelope(string $code, string $message, ?string $correlationId = null, array $details = []): array
    {
        $envelope = [
            'error' => [
                'code' => $code,
                'message' => $message,
                'correlationId' => $correlationId ?? Ids::correlationId(),
                'timestamp' => date('c')
            ]
        ];

        if (!empty($details)) {
            $envelope['error']['details'] = $details;
        }

        return $envelope;
    }

    /**
     * Handle exception and return error envelope
     */
    public static function handleException(\Throwable $exception, ?string $correlationId = null): array
    {
        $correlationId = $correlationId ?? Ids::correlationId();

        Logger::error('Exception handled', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Map exception types to error codes and safe messages
        $code = self::INTERNAL_ERROR;
        $message = 'An internal error occurred';
        $details = [];

        if ($exception instanceof \InvalidArgumentException) {
            $code = self::VALIDATION_ERROR;
            $message = $exception->getMessage(); // Safe to expose validation messages
        } elseif ($exception instanceof \RuntimeException) {
            if (str_contains($exception->getMessage(), 'OpenAI')) {
                $code = self::OPENAI_ERROR;
                $message = 'AI service temporarily unavailable';
            } elseif (str_contains($exception->getMessage(), 'Database')) {
                $code = self::DATABASE_ERROR;
                $message = 'Database service unavailable';
            } elseif (str_contains($exception->getMessage(), 'Redis')) {
                $code = self::REDIS_ERROR;
                $message = 'Cache service unavailable';
            } elseif (str_contains($exception->getMessage(), 'timeout')) {
                $code = self::TIMEOUT;
                $message = 'Request timed out';
            }
        } elseif ($exception instanceof \PDOException) {
            $code = self::DATABASE_ERROR;
            $message = 'Database error occurred';
        } elseif ($exception instanceof \RedisException) {
            $code = self::REDIS_ERROR;
            $message = 'Cache error occurred';
        }

        // Add development details in non-production environments
        if (Config::get('LOG_LEVEL') === 'debug') {
            $details = [
                'exception_class' => get_class($exception),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine()
            ];
        }

        return self::envelope($code, $message, $correlationId, $details);
    }

    /**
     * Send JSON error response and exit
     */
    public static function respond(string $code, string $message, int $httpStatus = 500, ?string $correlationId = null, array $details = []): never
    {
        if (!headers_sent()) {
            http_response_code($httpStatus);
            header('Content-Type: application/json');

            // Add rate limit headers if applicable
            if ($code === self::RATE_LIMITED) {
                header('Retry-After: 60');
            }
        }

        echo json_encode(self::envelope($code, $message, $correlationId, $details), JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Handle validation error
     */
    public static function validation(string $message, array $details = []): array
    {
        return self::envelope(self::VALIDATION_ERROR, $message, null, $details);
    }

    /**
     * Handle not found error
     */
    public static function notFound(string $resource = 'Resource'): array
    {
        return self::envelope(self::NOT_FOUND, "{$resource} not found");
    }

    /**
     * Handle rate limit error
     */
    public static function rateLimit(int $retryAfter = 60): array
    {
        return self::envelope(self::RATE_LIMITED, "Rate limit exceeded. Try again in {$retryAfter} seconds.");
    }

    /**
     * Handle tool execution error
     */
    public static function toolError(string $toolName, string $message): array
    {
        return self::envelope(self::TOOL_ERROR, "Tool '{$toolName}' failed: {$message}");
    }

    /**
     * Handle service unavailable error
     */
    public static function serviceUnavailable(string $service = 'Service'): array
    {
        return self::envelope(self::SERVICE_UNAVAILABLE, "{$service} is temporarily unavailable");
    }

    /**
     * Get HTTP status code for error code
     */
    public static function getHttpStatus(string $code): int
    {
        return match ($code) {
            self::VALIDATION_ERROR => 400,
            self::NOT_FOUND => 404,
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::RATE_LIMITED => 429,
            self::TIMEOUT => 408,
            self::SERVICE_UNAVAILABLE => 503,
            default => 500
        };
    }

    /**
     * Log and return tool execution error
     */
    public static function logToolError(string $toolName, \Throwable $exception, array $args = []): array
    {
        Logger::error("Tool execution failed", [
            'tool' => $toolName,
            'error' => $exception->getMessage(),
            'args' => Logger::sanitizeForLog($args),
            'exception_class' => get_class($exception)
        ]);

        // Return sanitized error message
        $message = self::sanitizeErrorMessage($exception->getMessage());
        return self::toolError($toolName, $message);
    }

    /**
     * Sanitize error message for safe display
     */
    public static function sanitizeErrorMessage(string $message): string
    {
        // Remove sensitive information patterns
        $patterns = [
            '/password[=:]\s*[^\s,}]+/i' => 'password=[HIDDEN]',
            '/api[_-]?key[=:]\s*[^\s,}]+/i' => 'api_key=[HIDDEN]',
            '/token[=:]\s*[^\s,}]+/i' => 'token=[HIDDEN]',
            '/Authorization:\s*[^\s,}]+/i' => 'Authorization: [HIDDEN]',
            '/\/home\/[^\/]+\/[^\/\s]+/' => '/[PATH_HIDDEN]/',
            '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/' => '[IP_HIDDEN]'
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }

        // Truncate if too long
        if (strlen($message) > 200) {
            $message = substr($message, 0, 197) . '...';
        }

        return $message;
    }

    /**
     * Create development error (includes full details)
     */
    public static function development(\Throwable $exception): array
    {
        if (Config::get('LOG_LEVEL') !== 'debug') {
            return self::handleException($exception);
        }

        return self::envelope(
            self::INTERNAL_ERROR,
            $exception->getMessage(),
            null,
            [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => array_slice($exception->getTrace(), 0, 10) // Limit trace length
            ]
        );
    }

    /**
     * Check if response is error envelope
     */
    public static function isErrorEnvelope(array $response): bool
    {
        return isset($response['error']) &&
               isset($response['error']['code']) &&
               isset($response['error']['message']);
    }

    /**
     * Extract error code from envelope
     */
    public static function getErrorCode(array $envelope): ?string
    {
        return $envelope['error']['code'] ?? null;
    }

    /**
     * Extract error message from envelope
     */
    public static function getErrorMessage(array $envelope): ?string
    {
        return $envelope['error']['message'] ?? null;
    }
}
