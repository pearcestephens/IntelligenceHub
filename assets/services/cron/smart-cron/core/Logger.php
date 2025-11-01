<?php
/**
 * Logger - Structured logging with JSON format
 * 
 * Provides comprehensive logging with correlation IDs, context data,
 * log levels, and automatic rotation.
 * 
 * @package SmartCron\Core
 * @version 1.0.0
 */

declare(strict_types=1);

namespace SmartCron\Core;

class Logger
{
    // Log levels (RFC 5424)
    public const DEBUG = 100;
    public const INFO = 200;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;
    
    private string $logPath;
    private int $minLevel;
    private string $correlationId;
    private bool $jsonFormat;
    private int $maxFileSizeMB;
    private int $rotationKeepDays;
    
    private static array $levelNames = [
        100 => 'DEBUG',
        200 => 'INFO',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL'
    ];
    
    public function __construct(
        string $logPath = null,
        int $minLevel = self::INFO,
        bool $jsonFormat = true,
        int $maxFileSizeMB = 100,
        int $rotationKeepDays = 30
    ) {
        $this->logPath = $logPath ?? $this->getDefaultLogPath();
        $this->minLevel = $minLevel;
        $this->jsonFormat = $jsonFormat;
        $this->maxFileSizeMB = $maxFileSizeMB;
        $this->rotationKeepDays = $rotationKeepDays;
        $this->correlationId = $this->generateCorrelationId();
        
        // Ensure log directory exists
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Check if rotation needed
        $this->checkRotation();
    }
    
    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }
    
    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log a critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Main logging method
     */
    private function log(int $level, string $message, array $context = []): void
    {
        // Check if level is enabled
        if ($level < $this->minLevel) {
            return;
        }
        
        $logEntry = $this->formatLogEntry($level, $message, $context);
        
        // Write to file
        $written = file_put_contents($this->logPath, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        if ($written === false) {
            error_log("Logger: Failed to write to {$this->logPath}");
        }
    }
    
    /**
     * Format log entry
     */
    private function formatLogEntry(int $level, string $message, array $context): string
    {
        $levelName = self::$levelNames[$level] ?? 'UNKNOWN';
        
        if ($this->jsonFormat) {
            return $this->formatJson($level, $levelName, $message, $context);
        } else {
            return $this->formatText($levelName, $message, $context);
        }
    }
    
    /**
     * Format as JSON
     */
    private function formatJson(int $level, string $levelName, string $message, array $context): string
    {
        $entry = [
            'timestamp' => gmdate('Y-m-d\TH:i:s.u\Z'),
            'level' => $levelName,
            'level_value' => $level,
            'message' => $message,
            'correlation_id' => $this->correlationId,
            'context' => $context,
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'pid' => getmypid()
        ];
        
        return json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Format as plain text
     */
    private function formatText(string $levelName, string $message, array $context): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        
        return "[{$timestamp}] {$levelName}: {$message}{$contextStr}";
    }
    
    /**
     * Generate unique correlation ID
     */
    private function generateCorrelationId(): string
    {
        return substr(md5(uniqid((string)mt_rand(), true)), 0, 12);
    }
    
    /**
     * Get correlation ID (for passing between systems)
     */
    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }
    
    /**
     * Set correlation ID (for continuing existing request)
     */
    public function setCorrelationId(string $correlationId): void
    {
        $this->correlationId = $correlationId;
    }
    
    /**
     * Get default log path
     */
    private function getDefaultLogPath(): string
    {
        // ✅ ABSOLUTE PATHS ONLY - NO SYMLINKS!
        $projectRoot = $_SERVER['DOCUMENT_ROOT'] ?? '/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html';
        return $projectRoot . '/logs/smart-cron-' . date('Y-m-d') . '.log';
    }
    
    /**
     * Check if log rotation needed
     */
    private function checkRotation(): void
    {
        if (!file_exists($this->logPath)) {
            return;
        }
        
        $fileSizeMB = filesize($this->logPath) / 1024 / 1024;
        
        if ($fileSizeMB > $this->maxFileSizeMB) {
            $this->rotateLog();
        }
        
        $this->cleanupOldLogs();
    }
    
    /**
     * Rotate current log file
     */
    private function rotateLog(): void
    {
        $timestamp = date('Y-m-d_H-i-s');
        $rotatedPath = $this->logPath . '.' . $timestamp;
        
        if (rename($this->logPath, $rotatedPath)) {
            // Compress rotated log
            $compressed = $rotatedPath . '.gz';
            $fp = gzopen($compressed, 'wb9');
            if ($fp) {
                gzwrite($fp, file_get_contents($rotatedPath));
                gzclose($fp);
                unlink($rotatedPath); // Remove uncompressed
            }
            
            $this->log(self::INFO, "Log rotated", ['rotated_to' => $rotatedPath]);
        }
    }
    
    /**
     * Cleanup old log files
     */
    private function cleanupOldLogs(): void
    {
        $logDir = dirname($this->logPath);
        $baseName = basename($this->logPath);
        $cutoffTime = time() - ($this->rotationKeepDays * 86400);
        
        $files = glob($logDir . '/' . $baseName . '.*');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * Create child logger with context
     */
    public function withContext(array $context): self
    {
        $logger = clone $this;
        // Context would be added to all subsequent log calls
        // Simplified implementation here
        return $logger;
    }
    
    /**
     * Log exception with full context
     */
    public function logException(\Throwable $e, int $level = self::ERROR): void
    {
        $this->log($level, $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => array_slice($e->getTrace(), 0, 10), // First 10 frames
            'code' => $e->getCode()
        ]);
    }
    
    /**
     * Get log file path
     */
    public function getLogPath(): string
    {
        return $this->logPath;
    }
    
    /**
     * Tail log file (get last N lines)
     */
    public function tail(int $lines = 50): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }
        
        $file = file($this->logPath);
        if (!$file) {
            return [];
        }
        
        return array_slice($file, -$lines);
    }
    
    /**
     * Search log for pattern
     */
    public function search(string $pattern, int $maxResults = 100): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }
        
        $matches = [];
        $file = fopen($this->logPath, 'r');
        
        if (!$file) {
            return [];
        }
        
        while (($line = fgets($file)) !== false && count($matches) < $maxResults) {
            if (stripos($line, $pattern) !== false) {
                $matches[] = trim($line);
            }
        }
        
        fclose($file);
        
        return $matches;
    }
    
    /**
     * Get log statistics
     */
    public function getStats(): array
    {
        if (!file_exists($this->logPath)) {
            return [
                'exists' => false,
                'size_mb' => 0,
                'lines' => 0
            ];
        }
        
        $lines = count(file($this->logPath));
        $sizeMB = filesize($this->logPath) / 1024 / 1024;
        
        // Count by level (JSON format only)
        $levelCounts = [
            'DEBUG' => 0,
            'INFO' => 0,
            'WARNING' => 0,
            'ERROR' => 0,
            'CRITICAL' => 0
        ];
        
        if ($this->jsonFormat) {
            $file = fopen($this->logPath, 'r');
            while (($line = fgets($file)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && isset($entry['level'])) {
                    $levelCounts[$entry['level']] = ($levelCounts[$entry['level']] ?? 0) + 1;
                }
            }
            fclose($file);
        }
        
        return [
            'exists' => true,
            'size_mb' => round($sizeMB, 2),
            'lines' => $lines,
            'level_counts' => $levelCounts,
            'modified' => date('Y-m-d H:i:s', filemtime($this->logPath))
        ];
    }
}
