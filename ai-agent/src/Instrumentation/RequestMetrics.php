<?php

/**
 * RequestMetrics
 * Lightweight per-request instrumentation for recording real performance metrics.
 * Records: response_time (seconds), request_total (count), request_errors (count if error),
 *          page_load_time (alias of response_time for UI pages), memory_peak (bytes),
 *          user_satisfaction heuristic placeholder (optional future).
 *
 * Safe to include early in any page. Requires autoloader + Redis + EnterprisePerformanceMonitor.
 */

declare(strict_types=1);

namespace App\Instrumentation;

use App\Intelligence\EnterprisePerformanceMonitor;
use App\Logger;

class RequestMetrics
{
    private static float $start;
    private static int $errorCount = 0;
    private static bool $initialized = false;
    private static string $correlationId;

    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        self::$start = microtime(true);
        self::$correlationId = bin2hex(random_bytes(8));
        if (!headers_sent()) {
            header('X-Correlation-ID: ' . self::$correlationId);
        }
        $_SERVER['CORRELATION_ID'] = self::$correlationId;

        // Error handler to count errors (non-fatal)
        set_error_handler(function ($severity, $message, $file, $line) {
            // Respect @ suppression
            if (!(error_reporting() & $severity)) {
                return false;
            }
            self::$errorCount++;
            // Log minimal structured error context
            Logger::warning('Request runtime notice', [
                'severity' => $severity,
                'message' => $message,
                'file' => $file,
                'line' => $line
            ]);
            return false; // allow normal handling
        });

        // Shutdown hook
        register_shutdown_function([self::class, 'finalize']);
    }

    public static function finalize(): void
    {
        $duration = microtime(true) - self::$start;
        $memoryPeak = memory_get_peak_usage(true);
        $tags = ['route' => self::routeTag(), 'cid' => self::$correlationId];
        EnterprisePerformanceMonitor::recordMetric('response_time', $duration, $tags);
        EnterprisePerformanceMonitor::recordMetric('request_total', 1, $tags);
        EnterprisePerformanceMonitor::recordMetric('page_load_time', $duration, $tags);
        EnterprisePerformanceMonitor::recordMetric('memory_peak_bytes', (float)$memoryPeak, $tags);
        if (self::$errorCount > 0) {
            EnterprisePerformanceMonitor::recordMetric('request_errors', (float)self::$errorCount, $tags);
        }
        $errRate = self::$errorCount > 0 ? min(1, self::$errorCount / 5) : 0.0;
        $satisfaction = max(0, min(1, 1 - ($duration / 5) - ($errRate * 0.5)));
        EnterprisePerformanceMonitor::recordMetric('user_satisfaction', $satisfaction, $tags);
    }

    private static function routeTag(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? 'unknown';
        return basename($script);
    }
}
