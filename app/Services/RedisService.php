<?php
/**
 * Redis Service - Central Cache Layer
 * 
 * Provides 10-100x performance boost for:
 * - KB search results
 * - API authentication
 * - Rate limiting
 * - File content caching
 * - Session management
 * 
 * @package Ecigdis\Services
 * @version 1.0.0
 */

namespace App\Services;

use Redis;
use Exception;

class RedisService
{
    private Redis $redis;
    private array $config;
    private bool $connected = false;
    private int $connectionAttempts = 0;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/redis.php';
        $this->connect();
    }
    
    /**
     * Establish Redis connection
     */
    private function connect(): void
    {
        try {
            $this->redis = new Redis();
            $connected = $this->redis->connect(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
            
            if (!$connected) {
                throw new Exception("Failed to connect to Redis");
            }
            
            // Authenticate if password is set
            if ($this->config['password']) {
                $this->redis->auth($this->config['password']);
            }
            
            // Select database
            $this->redis->select($this->config['database']);
            
            // Set key prefix
            $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
            
            // Set serializer
            if (isset($this->config['serializer'])) {
                $this->redis->setOption(Redis::OPT_SERIALIZER, $this->config['serializer']);
            }
            
            $this->connected = true;
            $this->connectionAttempts = 0;
            
        } catch (Exception $e) {
            $this->connected = false;
            $this->connectionAttempts++;
            error_log("Redis connection failed (attempt {$this->connectionAttempts}): " . $e->getMessage());
        }
    }
    
    /**
     * Reconnect if connection lost
     */
    private function ensureConnection(): bool
    {
        if (!$this->connected && $this->connectionAttempts < $this->config['max_retries']) {
            $this->connect();
        }
        return $this->connected;
    }
    
    /**
     * Cache-aside pattern: Get from cache or fallback to callback
     * 
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Fallback function if cache miss
     * @return mixed Cached or fresh data
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        if (!$this->ensureConnection()) {
            return $callback(); // Fallback to source if Redis unavailable
        }
        
        try {
            $cached = $this->redis->get($key);
            
            if ($cached !== false) {
                return json_decode($cached, true);
            }
            
            // Cache miss - get from source
            $value = $callback();
            
            // Store in cache
            $this->redis->setex($key, $ttl, json_encode($value));
            
            return $value;
            
        } catch (Exception $e) {
            error_log("Redis remember error: " . $e->getMessage());
            return $callback();
        }
    }
    
    /**
     * Get value from cache
     */
    public function get(string $key)
    {
        if (!$this->ensureConnection()) return null;
        
        try {
            $value = $this->redis->get($key);
            return $value !== false ? json_decode($value, true) : null;
        } catch (Exception $e) {
            error_log("Redis get error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Set value in cache with TTL
     */
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        if (!$this->ensureConnection()) return false;
        
        try {
            return $this->redis->setex($key, $ttl, json_encode($value));
        } catch (Exception $e) {
            error_log("Redis set error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete key from cache
     */
    public function delete(string $key): bool
    {
        if (!$this->ensureConnection()) return false;
        
        try {
            return $this->redis->del($key) > 0;
        } catch (Exception $e) {
            error_log("Redis delete error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate limiting using sorted sets
     * 
     * @param string $key Rate limit key (e.g., "api:rate:user_123")
     * @param int $limit Maximum requests allowed
     * @param int $window Time window in seconds
     * @return bool True if allowed, false if rate limited
     */
    public function rateLimit(string $key, int $limit, int $window): bool
    {
        if (!$this->ensureConnection()) {
            return true; // Allow if Redis down (fail-open)
        }
        
        try {
            $now = time();
            $windowStart = $now - $window;
            
            // Remove old entries outside window
            $this->redis->zRemRangeByScore($key, 0, $windowStart);
            
            // Count current window
            $current = $this->redis->zCard($key);
            
            if ($current >= $limit) {
                return false; // Rate limit exceeded
            }
            
            // Add current request
            $this->redis->zAdd($key, $now, uniqid('', true));
            $this->redis->expire($key, $window);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Redis rate limit error: " . $e->getMessage());
            return true; // Fail-open on error
        }
    }
    
    /**
     * Get current rate limit count
     */
    public function getRateLimitCount(string $key, int $window): int
    {
        if (!$this->ensureConnection()) return 0;
        
        try {
            $now = time();
            $windowStart = $now - $window;
            
            // Clean old entries
            $this->redis->zRemRangeByScore($key, 0, $windowStart);
            
            return $this->redis->zCard($key);
        } catch (Exception $e) {
            error_log("Redis rate limit count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Cache KB file content
     */
    public function cacheFile(string $fileHash, array $data, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->config['ttl']['kb_file'];
        $key = str_replace('{hash}', $fileHash, $this->config['patterns']['kb_file']);
        
        $this->set($key, $data, $ttl);
    }
    
    /**
     * Get cached KB file
     */
    public function getFile(string $fileHash): ?array
    {
        $key = str_replace('{hash}', $fileHash, $this->config['patterns']['kb_file']);
        return $this->get($key);
    }
    
    /**
     * Cache search results
     */
    public function cacheSearch(string $query, array $results, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->config['ttl']['kb_search'];
        $queryHash = md5($query);
        $key = str_replace('{query_hash}', $queryHash, $this->config['patterns']['kb_search']);
        
        $this->set($key, $results, $ttl);
    }
    
    /**
     * Get cached search results
     */
    public function getSearch(string $query): ?array
    {
        $queryHash = md5($query);
        $key = str_replace('{query_hash}', $queryHash, $this->config['patterns']['kb_search']);
        return $this->get($key);
    }
    
    /**
     * Cache API key validation result
     */
    public function cacheApiKey(string $keyId, array $keyData, int $ttl = null): void
    {
        $ttl = $ttl ?? $this->config['ttl']['api_auth'];
        $key = str_replace('{key_id}', $keyId, $this->config['patterns']['api_key']);
        
        $this->set($key, $keyData, $ttl);
    }
    
    /**
     * Get cached API key
     */
    public function getApiKey(string $keyId): ?array
    {
        $key = str_replace('{key_id}', $keyId, $this->config['patterns']['api_key']);
        return $this->get($key);
    }
    
    /**
     * Publish event to subscribers (Pub/Sub)
     */
    public function publish(string $channel, array $message): void
    {
        if (!$this->ensureConnection()) return;
        
        try {
            $this->redis->publish($channel, json_encode($message));
        } catch (Exception $e) {
            error_log("Redis publish error: " . $e->getMessage());
        }
    }
    
    /**
     * Invalidate cache pattern
     * 
     * @param string $pattern Pattern to match (without prefix)
     * @return int Number of keys deleted
     */
    public function invalidate(string $pattern): int
    {
        if (!$this->ensureConnection()) return 0;
        
        try {
            // Get keys matching pattern (Redis adds prefix automatically)
            $keys = $this->redis->keys($pattern);
            
            if (empty($keys)) return 0;
            
            // Delete all matching keys
            return $this->redis->del(...$keys);
            
        } catch (Exception $e) {
            error_log("Redis invalidate error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Flush entire cache (DANGEROUS!)
     */
    public function flushAll(): bool
    {
        if (!$this->ensureConnection()) return false;
        
        try {
            return $this->redis->flushDB();
        } catch (Exception $e) {
            error_log("Redis flush error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        if (!$this->ensureConnection()) {
            return ['connected' => false];
        }
        
        try {
            $info = $this->redis->info();
            $stats = $this->redis->info('stats');
            
            $hits = $stats['keyspace_hits'] ?? 0;
            $misses = $stats['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            return [
                'connected' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'uptime_days' => $info['uptime_in_days'] ?? 0,
                'used_memory' => $info['used_memory_human'] ?? '0',
                'max_memory' => $info['maxmemory_human'] ?? '0',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_commands' => $stats['total_commands_processed'] ?? 0,
                'keyspace_hits' => $hits,
                'keyspace_misses' => $misses,
                'hit_rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
                'evicted_keys' => $stats['evicted_keys'] ?? 0,
            ];
        } catch (Exception $e) {
            error_log("Redis stats error: " . $e->getMessage());
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Health check - ping Redis
     */
    public function ping(): bool
    {
        try {
            if (!$this->ensureConnection()) {
                return false;
            }
            
            $response = $this->redis->ping();
            // Can return bool(true) or string('+PONG') depending on Redis extension version
            return $response === true || $response === '+PONG' || $response === 'PONG';
        } catch (Exception $e) {
            error_log("Redis ping error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get Redis info
     */
    public function info(string $section = null): array
    {
        if (!$this->ensureConnection()) return [];
        
        try {
            return $this->redis->info($section);
        } catch (Exception $e) {
            error_log("Redis info error: " . $e->getMessage());
            return [];
        }
    }
}
