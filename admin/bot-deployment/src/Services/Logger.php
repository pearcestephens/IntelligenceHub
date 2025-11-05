<?php

namespace BotDeployment\Services;

use DateTime;
use Exception;

/**
 * Logger - PSR-3 Compliant Logging Service
 *
 * Features:
 * - PSR-3 log levels (debug, info, notice, warning, error, critical, alert, emergency)
 * - File rotation by size and date
 * - Context injection
 * - Multiple channels
 * - JSON and text formats
 * - Performance tracking
 */
class Logger
{
    // PSR-3 Log Levels
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    private string $logDir;
    private string $channel;
    private string $minLevel;
    private string $format;
    private int $maxFileSize;
    private array $levelPriority = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::NOTICE => 2,
        self::WARNING => 3,
        self::ERROR => 4,
        self::CRITICAL => 5,
        self::ALERT => 6,
        self::EMERGENCY => 7
    ];

    /**
     * Constructor
     *
     * @param string $logDir Log directory path
     * @param string $channel Channel name (e.g., 'bot', 'api', 'security')
     * @param string $minLevel Minimum log level to record
     * @param string $format Log format: 'text' or 'json'
     * @param int $maxFileSize Max file size before rotation (bytes)
     */
    public function __construct(
        string $logDir = null,
        string $channel = 'app',
        string $minLevel = self::INFO,
        string $format = 'text',
        int $maxFileSize = 10485760 // 10MB
    ) {
        $this->logDir = $logDir ?? __DIR__ . '/../../logs';
        $this->channel = $channel;
        $this->minLevel = $minLevel;
        $this->format = $format;
        $this->maxFileSize = $maxFileSize;

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Log emergency message
     * System is unusable
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log alert message
     * Action must be taken immediately
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log critical message
     * Critical conditions
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log error message
     * Runtime errors that don't require immediate action
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log warning message
     * Exceptional occurrences that are not errors
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log notice message
     * Normal but significant events
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log info message
     * Interesting events
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log debug message
     * Detailed debug information
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Main log method
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Check if level should be logged
        if (!$this->shouldLog($level)) {
            return;
        }

        // Prepare log entry
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');
        $processId = getmypid();
        $memoryUsage = memory_get_usage(true);

        $logData = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'channel' => $this->channel,
            'message' => $this->interpolate($message, $context),
            'context' => $context,
            'process_id' => $processId,
            'memory' => $this->formatBytes($memoryUsage)
        ];

        // Format log entry
        $logEntry = $this->format === 'json'
            ? json_encode($logData) . PHP_EOL
            : $this->formatText($logData);

        // Write to file
        $this->write($level, $logEntry);
    }

    /**
     * Check if level should be logged
     *
     * @param string $level Log level to check
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        return ($this->levelPriority[$level] ?? 0) >= ($this->levelPriority[$this->minLevel] ?? 0);
    }

    /**
     * Interpolate context values into message
     *
     * @param string $message Message with placeholders
     * @param array $context Context values
     * @return string Interpolated message
     */
    private function interpolate(string $message, array $context): string
    {
        $replace = [];

        foreach ($context as $key => $val) {
            if (is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

    /**
     * Format log entry as text
     *
     * @param array $logData Log data
     * @return string Formatted text
     */
    private function formatText(array $logData): string
    {
        $text = sprintf(
            "[%s] %s.%s: %s",
            $logData['timestamp'],
            $logData['channel'],
            $logData['level'],
            $logData['message']
        );

        if (!empty($logData['context'])) {
            $text .= ' ' . json_encode($logData['context']);
        }

        $text .= sprintf(" | PID:%d MEM:%s", $logData['process_id'], $logData['memory']);

        return $text . PHP_EOL;
    }

    /**
     * Write log entry to file
     *
     * @param string $level Log level
     * @param string $logEntry Formatted log entry
     */
    private function write(string $level, string $logEntry): void
    {
        $filename = $this->getLogFilename($level);

        // Check for rotation
        if (file_exists($filename) && filesize($filename) >= $this->maxFileSize) {
            $this->rotateLog($filename);
        }

        // Write log entry
        file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get log filename for level
     *
     * @param string $level Log level
     * @return string Full file path
     */
    private function getLogFilename(string $level): string
    {
        $date = date('Y-m-d');
        return "{$this->logDir}/{$this->channel}-{$level}-{$date}.log";
    }

    /**
     * Rotate log file
     *
     * @param string $filename Current log file
     */
    private function rotateLog(string $filename): void
    {
        $timestamp = date('Y-m-d_His');
        $rotatedFilename = $filename . '.' . $timestamp;
        rename($filename, $rotatedFilename);

        // Optional: Compress old log
        if (function_exists('gzopen')) {
            $this->compressLog($rotatedFilename);
        }
    }

    /**
     * Compress log file
     *
     * @param string $filename File to compress
     */
    private function compressLog(string $filename): void
    {
        try {
            $content = file_get_contents($filename);
            $gz = gzopen($filename . '.gz', 'w9');
            gzwrite($gz, $content);
            gzclose($gz);
            unlink($filename);
        } catch (Exception $e) {
            // Compression failed, keep original
        }
    }

    /**
     * Format bytes to human-readable
     *
     * @param int $bytes Bytes
     * @return string Formatted size
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . $units[$i];
    }

    /**
     * Log with timing
     *
     * @param string $level Log level
     * @param string $message Message
     * @param callable $callback Code to time
     * @param array $context Additional context
     * @return mixed Callback return value
     */
    public function logTimed(string $level, string $message, callable $callback, array $context = [])
    {
        $start = microtime(true);

        try {
            $result = $callback();
            $duration = round((microtime(true) - $start) * 1000, 2);

            $this->log($level, $message . " (took {$duration}ms)", array_merge($context, [
                'duration_ms' => $duration,
                'success' => true
            ]));

            return $result;
        } catch (Exception $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);

            $this->log(self::ERROR, $message . " failed (took {$duration}ms)", array_merge($context, [
                'duration_ms' => $duration,
                'error' => $e->getMessage(),
                'success' => false
            ]));

            throw $e;
        }
    }

    /**
     * Clean up old log files
     *
     * @param int $days Number of days to keep
     * @return int Number of files deleted
     */
    public function cleanup(int $days = 30): int
    {
        $count = 0;
        $cutoff = time() - ($days * 86400);

        $files = glob($this->logDir . '/*.log*');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get recent log entries
     *
     * @param string $level Log level to filter (null = all)
     * @param int $lines Number of lines to return
     * @return array Log entries
     */
    public function tail(string $level = null, int $lines = 100): array
    {
        $entries = [];
        $pattern = $level
            ? "{$this->logDir}/{$this->channel}-{$level}-*.log"
            : "{$this->logDir}/{$this->channel}-*.log";

        $files = glob($pattern);
        rsort($files); // Most recent first

        foreach ($files as $file) {
            $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $entries = array_merge($entries, array_reverse(array_slice($content, -$lines)));

            if (count($entries) >= $lines) {
                break;
            }
        }

        return array_slice($entries, 0, $lines);
    }
}
