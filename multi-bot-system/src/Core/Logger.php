<?php
/**
 * Logger Utility
 *
 * Centralized logging with multiple levels
 */

declare(strict_types=1);

namespace MultiBot\Core;

class Logger
{
    private static string $logPath;
    private static bool $enabled = true;

    /**
     * Initialize logger
     */
    public static function init(string $logPath, bool $enabled = true): void
    {
        self::$logPath = $logPath;
        self::$enabled = $enabled;

        // Create log directory if it doesn't exist
        if (!is_dir(self::$logPath)) {
            mkdir(self::$logPath, 0755, true);
        }
    }

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Write log entry
     */
    private static function log(string $level, string $message, array $context): void
    {
        if (!self::$enabled) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = sprintf("[%s] %s: %s%s\n", $timestamp, $level, $message, $contextStr);

        $filename = self::$logPath . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);

        // Also log errors to PHP error log
        if ($level === 'ERROR') {
            error_log($message);
        }
    }

    /**
     * Log HTTP request
     */
    public static function logRequest(string $method, string $path, array $params = []): void
    {
        self::info(sprintf('HTTP %s %s', $method, $path), $params);
    }

    /**
     * Log bot interaction
     */
    public static function logBotAction(string $botId, string $action, array $context = []): void
    {
        self::info(sprintf('Bot[%s]: %s', $botId, $action), $context);
    }
}
