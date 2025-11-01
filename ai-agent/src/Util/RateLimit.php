<?php

/**
 * Rate limiting using Redis sliding window
 * Provides IP-based and user-based rate limiting for API endpoints
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

use App\Config;
use App\RedisClient;
use App\Logger;

class RateLimit
{
    private const DEFAULT_WINDOW_MS = 60000; // 1 minute
    private const DEFAULT_MAX_REQUESTS = 120;
    private const CLEANUP_PROBABILITY = 0.01; // 1% chance to cleanup expired keys

    /**
     * Check rate limit for identifier
     */
    public static function check(string $identifier, ?int $windowMs = null, ?int $maxRequests = null): bool
    {
        $windowMs = $windowMs ?? Config::get('RATE_LIMIT_WINDOW_MS', self::DEFAULT_WINDOW_MS);
        $maxRequests = $maxRequests ?? Config::get('RATE_LIMIT_MAX', self::DEFAULT_MAX_REQUESTS);

        $key = Ids::rateLimitKey($identifier);
        $now = (int)(microtime(true) * 1000); // Current time in milliseconds
        $windowStart = $now - $windowMs;

        try {
            // Add current request with timestamp as score
            RedisClient::connection()->zadd($key, (float)$now, uniqid());

            // Remove expired entries
            RedisClient::connection()->zremrangebyscore($key, (string)0, (string)$windowStart);

            // Count requests in current window
            $requestCount = RedisClient::connection()->zcard($key);

            // Set TTL for key cleanup
            RedisClient::connection()->expire($key, (int)ceil($windowMs / 1000) + 60);

            $allowed = $requestCount <= $maxRequests;

            Logger::debug('Rate limit check', [
                'identifier' => $identifier,
                'requests' => $requestCount,
                'max_requests' => $maxRequests,
                'window_ms' => $windowMs,
                'allowed' => $allowed
            ]);

            // Random cleanup of old keys
            if (random_int(1, 100) <= (self::CLEANUP_PROBABILITY * 100)) {
                self::cleanup();
            }

            return $allowed;
        } catch (\Throwable $e) {
            Logger::error('Rate limit check failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);

            // Allow request on Redis failure (fail open)
            return true;
        }
    }

    /**
     * Get current request count for identifier
     */
    public static function getRequestCount(string $identifier, ?int $windowMs = null): int
    {
        $windowMs = $windowMs ?? Config::get('RATE_LIMIT_WINDOW_MS', self::DEFAULT_WINDOW_MS);
        $key = Ids::rateLimitKey($identifier);
        $now = (int)(microtime(true) * 1000);
        $windowStart = $now - $windowMs;

        try {
            // Clean expired entries first
            RedisClient::connection()->zremrangebyscore($key, (string)0, (string)$windowStart);

            return RedisClient::connection()->zcard($key);
        } catch (\Throwable $e) {
            Logger::error('Rate limit count failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get time until rate limit resets (in seconds)
     */
    public static function getResetTime(string $identifier, ?int $windowMs = null): int
    {
        $windowMs = $windowMs ?? Config::get('RATE_LIMIT_WINDOW_MS', self::DEFAULT_WINDOW_MS);
        $key = Ids::rateLimitKey($identifier);

        try {
            // Get oldest request in current window
            $oldest = RedisClient::connection()->zrange($key, 0, 0, ['WITHSCORES' => true]);

            if (empty($oldest)) {
                return 0; // No requests in window
            }

            $oldestTimestamp = (int)array_values($oldest)[0];
            $now = (int)(microtime(true) * 1000);
            $resetTime = $oldestTimestamp + $windowMs;

            return max(0, (int)ceil(($resetTime - $now) / 1000));
        } catch (\Throwable $e) {
            Logger::error('Rate limit reset time failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);
            return 60; // Default 1 minute
        }
    }

    /**
     * Check rate limit and throw exception if exceeded
     */
    public static function enforce(string $identifier, ?int $windowMs = null, ?int $maxRequests = null): void
    {
        if (!self::check($identifier, $windowMs, $maxRequests)) {
            $resetTime = self::getResetTime($identifier, $windowMs);

            Logger::warning('Rate limit exceeded', [
                'identifier' => $identifier,
                'reset_in_seconds' => $resetTime
            ]);

            Errors::respond(
                Errors::RATE_LIMITED,
                'Rate limit exceeded. Please try again later.',
                429,
                null,
                ['retry_after_seconds' => $resetTime]
            );
        }
    }

    /**
     * Create rate limit identifier from request
     */
    public static function getIdentifier(): string
    {
        // Try to get user ID from session or JWT
        $userId = $_SESSION['user_id'] ?? null;

        if ($userId) {
            return 'user_' . $userId;
        }

        // Fall back to IP address
        $ip = self::getClientIp();
        return 'ip_' . hash('sha256', $ip . Config::get('RATE_LIMIT_SALT', 'default'));
    }

    /**
     * Get real client IP address
     */
    public static function getClientIp(): string
    {
        // Check for IP from shared internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // Check for IP passed from proxy
            // Can contain multiple IPs, get the first one
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) { // Check for IP from remote address
            return $_SERVER['REMOTE_ADDR'];
        }

        return 'unknown';
    }

    /**
     * Reset rate limit for identifier
     */
    public static function reset(string $identifier): bool
    {
        $key = Ids::rateLimitKey($identifier);

        try {
            return RedisClient::delete($key);
        } catch (\Throwable $e) {
            Logger::error('Rate limit reset failed', [
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get rate limit status for identifier
     */
    public static function getStatus(string $identifier, ?int $windowMs = null, ?int $maxRequests = null): array
    {
        $windowMs = $windowMs ?? Config::get('RATE_LIMIT_WINDOW_MS', self::DEFAULT_WINDOW_MS);
        $maxRequests = $maxRequests ?? Config::get('RATE_LIMIT_MAX', self::DEFAULT_MAX_REQUESTS);

        $currentCount = self::getRequestCount($identifier, $windowMs);
        $resetTime = self::getResetTime($identifier, $windowMs);
        $remaining = max(0, $maxRequests - $currentCount);

        return [
            'limit' => $maxRequests,
            'remaining' => $remaining,
            'reset_in' => $resetTime,
            'window_ms' => $windowMs,
            'allowed' => $remaining > 0
        ];
    }

    /**
     * Middleware function for rate limiting
     */
    public static function middleware(?string $identifier = null, ?int $windowMs = null, ?int $maxRequests = null): void
    {
        $identifier = $identifier ?? self::getIdentifier();
        self::enforce($identifier, $windowMs, $maxRequests);

        // Add rate limit headers to response
        $status = self::getStatus($identifier, $windowMs, $maxRequests);

        if (!headers_sent()) {
            header("X-RateLimit-Limit: {$status['limit']}");
            header("X-RateLimit-Remaining: {$status['remaining']}");
            header("X-RateLimit-Reset: {$status['reset_in']}");
        }
    }

    /**
     * Cleanup expired rate limit keys
     */
    private static function cleanup(): void
    {
        try {
            $pattern = Config::get('REDIS_PREFIX') . 'rate:*';
            $keys = RedisClient::connection()->keys($pattern);

            $cleaned = 0;
            foreach ($keys as $key) {
                $ttl = RedisClient::connection()->ttl($key);

                // Remove keys that are expired or have very long TTL (likely stuck)
                $ttlInt = (int)$ttl;
                if ($ttlInt <= 0 || $ttlInt > 3600) {
                    RedisClient::connection()->del($key);
                    $cleaned++;
                }
            }

            if ($cleaned > 0) {
                Logger::debug('Rate limit cleanup completed', [
                    'keys_cleaned' => $cleaned
                ]);
            }
        } catch (\Throwable $e) {
            Logger::error('Rate limit cleanup failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if request should be rate limited (bypass for internal calls)
     */
    public static function shouldRateLimit(): bool
    {
        // Skip rate limiting for internal calls
        if (isset($_SERVER['HTTP_X_INTERNAL_CALL']) && $_SERVER['HTTP_X_INTERNAL_CALL'] === 'true') {
            return false;
        }

        // Skip for localhost in development
        if (Config::get('LOG_LEVEL') === 'debug') {
            $ip = self::getClientIp();
            if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
                return false;
            }
        }

        return true;
    }
}
