<?php
/**
 * Redis Cache Manager
 * High-performance caching layer for Intelligence Hub
 *
 * @package IntelligenceHub
 * @version 2.0.0
 * @author Ecigdis Limited - Intelligence Hub Team
 */

declare(strict_types=1);

class RedisCache {
    private static ?Redis $redis = null;
    private static bool $enabled = false;
    private static array $stats = ['hits' => 0, 'misses' => 0, 'sets' => 0];

    /**
     * Initialize Redis connection
     */
    public static function init(): bool {
        if (self::$redis !== null) {
            return self::$enabled;
        }

        try {
            self::$redis = new Redis();

            // Connect to Redis (localhost, default port)
            $connected = self::$redis->connect('127.0.0.1', 6379, 2);

            if (!$connected) {
                error_log('[RedisCache] Failed to connect to Redis server');
                self::$enabled = false;
                return false;
            }

            // Optional: Set password if configured
            $redisPassword = getenv('REDIS_PASSWORD');
            if ($redisPassword && $redisPassword !== '') {
                self::$redis->auth($redisPassword);
            }

            // Set prefix for all keys
            self::$redis->setOption(Redis::OPT_PREFIX, 'ihub:');

            // Use serialization for complex data types
            self::$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

            self::$enabled = true;
            error_log('[RedisCache] ✅ Redis cache initialized successfully');

            return true;
        } catch (Exception $e) {
            error_log('[RedisCache] ❌ Redis initialization failed: ' . $e->getMessage());
            self::$enabled = false;
            return false;
        }
    }

    /**
     * Get value from cache
     */
    public static function get(string $key): mixed {
        if (!self::$enabled && !self::init()) {
            return null;
        }

        try {
            $value = self::$redis->get($key);

            if ($value === false) {
                self::$stats['misses']++;
                return null;
            }

            self::$stats['hits']++;
            return $value;
        } catch (Exception $e) {
            error_log('[RedisCache] Get error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set value in cache with optional TTL
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            $result = self::$redis->setex($key, $ttl, $value);

            if ($result) {
                self::$stats['sets']++;
            }

            return $result;
        } catch (Exception $e) {
            error_log('[RedisCache] Set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete key from cache
     */
    public static function delete(string $key): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            return self::$redis->del($key) > 0;
        } catch (Exception $e) {
            error_log('[RedisCache] Delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple keys matching pattern
     */
    public static function deletePattern(string $pattern): int {
        if (!self::$enabled && !self::init()) {
            return 0;
        }

        try {
            $keys = self::$redis->keys($pattern);
            if (empty($keys)) {
                return 0;
            }
            return self::$redis->del($keys);
        } catch (Exception $e) {
            error_log('[RedisCache] DeletePattern error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if key exists
     */
    public static function exists(string $key): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            return self::$redis->exists($key) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get or set (fetch from cache or compute and cache)
     */
    public static function remember(string $key, callable $callback, int $ttl = 3600): mixed {
        $value = self::get($key);

        if ($value !== null) {
            return $value;
        }

        // Cache miss - compute value
        $value = $callback();

        if ($value !== null) {
            self::set($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Increment counter
     */
    public static function increment(string $key, int $amount = 1): int {
        if (!self::$enabled && !self::init()) {
            return 0;
        }

        try {
            return self::$redis->incrBy($key, $amount);
        } catch (Exception $e) {
            error_log('[RedisCache] Increment error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Decrement counter
     */
    public static function decrement(string $key, int $amount = 1): int {
        if (!self::$enabled && !self::init()) {
            return 0;
        }

        try {
            return self::$redis->decrBy($key, $amount);
        } catch (Exception $e) {
            error_log('[RedisCache] Decrement error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Flush all cache
     */
    public static function flush(): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            return self::$redis->flushDB();
        } catch (Exception $e) {
            error_log('[RedisCache] Flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array {
        $localStats = self::$stats;

        if (self::$enabled) {
            try {
                $info = self::$redis->info('stats');
                $localStats['redis_hits'] = $info['keyspace_hits'] ?? 0;
                $localStats['redis_misses'] = $info['keyspace_misses'] ?? 0;
                $localStats['total_keys'] = 0;

                // Count keys in current database
                $dbInfo = self::$redis->info('keyspace');
                if (isset($dbInfo['db0'])) {
                    preg_match('/keys=(\d+)/', $dbInfo['db0'], $matches);
                    $localStats['total_keys'] = (int)($matches[1] ?? 0);
                }
            } catch (Exception $e) {
                error_log('[RedisCache] Stats error: ' . $e->getMessage());
            }
        }

        return $localStats;
    }

    /**
     * Check if Redis is enabled and working
     */
    public static function isEnabled(): bool {
        return self::$enabled;
    }

    /**
     * Get Redis connection instance
     */
    public static function getConnection(): ?Redis {
        if (!self::$enabled && !self::init()) {
            return null;
        }
        return self::$redis;
    }
}

// Auto-initialize on load
RedisCache::init();
