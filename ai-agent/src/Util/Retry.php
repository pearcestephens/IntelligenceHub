<?php

/**
 * Retry Utility with Exponential Backoff and Jitter
 *
 * Implements resilient retry logic for transient failures with:
 * - Exponential backoff to avoid overwhelming failing services
 * - Jitter to prevent thundering herd problem
 * - Transient error detection (network, timeout, rate limit)
 * - Configurable max attempts and delay bounds
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

use App\Logger;
use Exception;

class Retry
{
    /**
     * Retry operation with exponential backoff and jitter
     *
     * @param callable $operation Function to retry (receives attempt number)
     * @param int $maxAttempts Maximum retry attempts (default: 3)
     * @param int $initialDelayMs Initial delay in milliseconds (default: 100ms)
     * @param float $multiplier Backoff multiplier (default: 2.0)
     * @param int $maxDelayMs Maximum delay cap in milliseconds (default: 5000ms)
     * @param Logger|null $logger Optional logger for retry events
     * @return mixed Operation result on success
     * @throws Exception If all retries exhausted or permanent error
     */
    public static function withBackoff(
        callable $operation,
        int $maxAttempts = 3,
        int $initialDelayMs = 100,
        float $multiplier = 2.0,
        int $maxDelayMs = 5000,
        ?Logger $logger = null
    ): mixed {
        $attempt = 0;
        $delay = $initialDelayMs;
        $lastException = null;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                // Attempt operation (pass attempt number for logging)
                return $operation($attempt);
            } catch (Exception $e) {
                $lastException = $e;

                // Check if error is retryable
                if (!self::isTransient($e)) {
                    if ($logger) {
                        $logger->info("Permanent error detected, not retrying", [
                            'error' => $e->getMessage(),
                            'attempt' => $attempt
                        ]);
                    }
                    throw $e;
                }

                // Check if retries exhausted
                if ($attempt >= $maxAttempts) {
                    if ($logger) {
                        $logger->error("Retry exhausted after {$maxAttempts} attempts", [
                            'error' => $e->getMessage(),
                            'final_attempt' => $attempt
                        ]);
                    }
                    throw $e;
                }

                // Calculate delay with jitter (0-50% of base delay)
                $jitter = random_int(0, (int)($delay * 0.5));
                $sleepMs = min($delay + $jitter, $maxDelayMs);

                if ($logger) {
                    $logger->warning("Transient error, retrying", [
                        'error' => $e->getMessage(),
                        'attempt' => $attempt,
                        'max_attempts' => $maxAttempts,
                        'delay_ms' => $sleepMs,
                        'next_attempt' => $attempt + 1
                    ]);
                }

                // Sleep before next attempt
                usleep($sleepMs * 1000);

                // Increase delay for next iteration
                $delay = (int)($delay * $multiplier);
            }
        }

        // Should never reach here, but TypeScript safety
        throw $lastException ?? new Exception('Retry logic error: no exception recorded');
    }

    /**
     * Check if exception represents a transient (retryable) error
     *
     * Transient errors include:
     * - Network timeouts
     * - Connection failures
     * - Service unavailable (503)
     * - Rate limiting (429)
     * - Gateway timeouts (504)
     *
     * @param Exception $e Exception to check
     * @return bool True if error is transient and retryable
     */
    private static function isTransient(Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        // Patterns indicating transient failures
        $transientPatterns = [
            // Network errors
            'timeout',
            'timed out',
            'connection refused',
            'connection reset',
            'connection closed',
            'broken pipe',
            'network unreachable',
            'host unreachable',

            // Service errors
            'temporary failure',
            'service unavailable',
            'temporarily unavailable',
            'try again',

            // Rate limiting
            'too many requests',
            'rate limit',
            'quota exceeded',

            // HTTP status codes
            '429', // Too Many Requests
            '503', // Service Unavailable
            '504', // Gateway Timeout
            '502', // Bad Gateway

            // Database temporary errors
            'deadlock',
            'lock wait timeout',
            'connection lost',
            'gone away'
        ];

        foreach ($transientPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retry operation with simple fixed delay (no backoff)
     *
     * Useful for operations where exponential backoff is not needed.
     *
     * @param callable $operation Function to retry
     * @param int $maxAttempts Maximum retry attempts
     * @param int $delayMs Fixed delay between retries in milliseconds
     * @param Logger|null $logger Optional logger
     * @return mixed Operation result on success
     * @throws Exception If all retries exhausted
     */
    public static function withFixedDelay(
        callable $operation,
        int $maxAttempts = 3,
        int $delayMs = 1000,
        ?Logger $logger = null
    ): mixed {
        return self::withBackoff(
            $operation,
            $maxAttempts,
            $delayMs,
            1.0, // No multiplier (fixed delay)
            $delayMs, // Max delay same as initial
            $logger
        );
    }

    /**
     * Retry operation with immediate retries (no delay)
     *
     * Useful for in-memory operations that might fail due to race conditions.
     *
     * @param callable $operation Function to retry
     * @param int $maxAttempts Maximum retry attempts
     * @param Logger|null $logger Optional logger
     * @return mixed Operation result on success
     * @throws Exception If all retries exhausted
     */
    public static function immediate(
        callable $operation,
        int $maxAttempts = 3,
        ?Logger $logger = null
    ): mixed {
        return self::withBackoff(
            $operation,
            $maxAttempts,
            0, // No delay
            1.0,
            0,
            $logger
        );
    }
}
