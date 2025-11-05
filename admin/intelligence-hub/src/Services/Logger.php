<?php

namespace IntelligenceHub\Services;

use Exception;

/**
 * Logger Service
 *
 * Centralized logging with multiple severity levels and context
 */
class Logger
{
    private $context;
    private $logPath;

    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';

    public function __construct(string $context = 'app')
    {
        $this->context = $context;
        $this->logPath = getenv('LOG_PATH') ?: __DIR__ . '/../../logs';

        // Create logs directory if it doesn't exist
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';

        $logLine = sprintf(
            "[%s] [%s] [%s] %s%s\n",
            $timestamp,
            $level,
            $this->context,
            $message,
            $contextStr
        );

        // Determine log file
        $filename = $this->logPath . '/' . strtolower($level) . '-' . date('Y-m-d') . '.log';

        // Write to file
        file_put_contents($filename, $logLine, FILE_APPEND | LOCK_EX);

        // Also write to main log file
        $mainLog = $this->logPath . '/intelligence-hub-' . date('Y-m-d') . '.log';
        file_put_contents($mainLog, $logLine, FILE_APPEND | LOCK_EX);
    }
}
