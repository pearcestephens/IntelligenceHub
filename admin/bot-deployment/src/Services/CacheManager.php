<?php

namespace BotDeployment\Services;

use Redis;
use Exception;

/**
 * CacheManager - Advanced Caching Service
 *
 * Features:
 * - Redis primary, file fallback
 * - TTL support
 * - Cache tags for bulk invalidation
 * - Namespaces for isolation
 * - JSON serialization
 * - Cache statistics
 */
class CacheManager
{
    private ?Redis $redis = null;
    private string $fileCache;
    private string $namespace = 'bot_deployment';
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0
    ];

    /**
     * Constructor
     *
     * @param array $config Redis config: host, port, password, timeout
     * @param string $fileCachePath Fallback file cache directory
     */
    public function __construct(array $config = [], string $fileCachePath = null)
    {
        // Try Redis connection
        if (extension_loaded('redis')) {
            try {
                $this->redis = new Redis();
                $host = $config['host'] ?? '127.0.0.1';
                $port = $config['port'] ?? 6379;
                $timeout = $config['timeout'] ?? 2.5;

                if ($this->redis->connect($host, $port, $timeout)) {
                    if (!empty($config['password'])) {
                        $this->redis->auth($config['password']);
                    }
                    $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
                } else {
                    $this->redis = null;
                }
            } catch (Exception $e) {
                $this->redis = null;
                error_log("Redis connection failed: " . $e->getMessage());
            }
        }

        // Setup file cache fallback
        $this->fileCache = $fileCachePath ?? __DIR__ . '/../../cache';
        if (!is_dir($this->fileCache)) {
            mkdir($this->fileCache, 0755, true);
        }
    }

    /**
     * Set namespace for cache keys
     *
     * @param string $namespace Namespace prefix
     * @return self
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->buildKey($key);

        // Try Redis first
        if ($this->redis) {
            try {
                $value = $this->redis->get($fullKey);
                if ($value !== false) {
                    $this->stats['hits']++;
                    return $value;
                }
            } catch (Exception $e) {
                error_log("Redis get failed: " . $e->getMessage());
            }
        }

        // Fallback to file cache
        $filePath = $this->getFilePath($fullKey);
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if ($data && ($data['expires'] === 0 || $data['expires'] > time())) {
                $this->stats['hits']++;
                return $data['value'];
            }
            // Expired - delete
            unlink($filePath);
        }

        $this->stats['misses']++;
        return $default;
    }

    /**
     * Set value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Time to live in seconds (0 = forever)
     * @param array $tags Optional tags for bulk invalidation
     * @return bool Success
     */
    public function set(string $key, $value, int $ttl = 3600, array $tags = []): bool
    {
        $fullKey = $this->buildKey($key);
        $success = false;

        // Store in Redis
        if ($this->redis) {
            try {
                if ($ttl > 0) {
                    $success = $this->redis->setex($fullKey, $ttl, $value);
                } else {
                    $success = $this->redis->set($fullKey, $value);
                }

                // Store tags
                if ($success && !empty($tags)) {
                    foreach ($tags as $tag) {
                        $tagKey = $this->buildKey("tag:{$tag}");
                        $this->redis->sAdd($tagKey, $fullKey);
                        if ($ttl > 0) {
                            $this->redis->expire($tagKey, $ttl);
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Redis set failed: " . $e->getMessage());
                $success = false;
            }
        }

        // Store in file cache (always as backup)
        $filePath = $this->getFilePath($fullKey);
        $data = [
            'value' => $value,
            'expires' => $ttl > 0 ? time() + $ttl : 0,
            'tags' => $tags,
            'created' => time()
        ];

        if (file_put_contents($filePath, json_encode($data), LOCK_EX)) {
            $success = true;
        }

        if ($success) {
            $this->stats['sets']++;
        }

        return $success;
    }

    /**
     * Check if key exists
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        $fullKey = $this->buildKey($key);

        // Check Redis
        if ($this->redis) {
            try {
                if ($this->redis->exists($fullKey)) {
                    return true;
                }
            } catch (Exception $e) {
                error_log("Redis exists failed: " . $e->getMessage());
            }
        }

        // Check file cache
        $filePath = $this->getFilePath($fullKey);
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            return $data && ($data['expires'] === 0 || $data['expires'] > time());
        }

        return false;
    }

    /**
     * Delete value from cache
     *
     * @param string $key Cache key
     * @return bool Success
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->buildKey($key);
        $success = false;

        // Delete from Redis
        if ($this->redis) {
            try {
                $success = $this->redis->del($fullKey) > 0;
            } catch (Exception $e) {
                error_log("Redis delete failed: " . $e->getMessage());
            }
        }

        // Delete from file cache
        $filePath = $this->getFilePath($fullKey);
        if (file_exists($filePath)) {
            $success = unlink($filePath) || $success;
        }

        if ($success) {
            $this->stats['deletes']++;
        }

        return $success;
    }

    /**
     * Invalidate all keys with specific tag
     *
     * @param string $tag Tag name
     * @return int Number of keys deleted
     */
    public function invalidateTag(string $tag): int
    {
        $count = 0;

        // Redis tag invalidation
        if ($this->redis) {
            try {
                $tagKey = $this->buildKey("tag:{$tag}");
                $keys = $this->redis->sMembers($tagKey);
                if (!empty($keys)) {
                    $count += $this->redis->del($keys);
                    $this->redis->del($tagKey);
                }
            } catch (Exception $e) {
                error_log("Redis tag invalidation failed: " . $e->getMessage());
            }
        }

        // File cache tag invalidation
        $files = glob($this->fileCache . '/*.cache');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && in_array($tag, $data['tags'] ?? [])) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Clear all cache
     *
     * @return bool Success
     */
    public function clear(): bool
    {
        $success = true;

        // Clear Redis
        if ($this->redis) {
            try {
                $pattern = $this->buildKey('*');
                $keys = $this->redis->keys($pattern);
                if (!empty($keys)) {
                    $this->redis->del($keys);
                }
            } catch (Exception $e) {
                error_log("Redis clear failed: " . $e->getMessage());
                $success = false;
            }
        }

        // Clear file cache
        $files = glob($this->fileCache . '/*.cache');
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get cache statistics
     *
     * @return array Stats: hits, misses, sets, deletes, hit_rate
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0;

        return array_merge($this->stats, [
            'hit_rate' => $hitRate,
            'backend' => $this->redis ? 'redis' : 'file',
            'namespace' => $this->namespace
        ]);
    }

    /**
     * Remember (get or set with callback)
     *
     * @param string $key Cache key
     * @param callable $callback Function to generate value if not cached
     * @param int $ttl Time to live
     * @param array $tags Cache tags
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = 3600, array $tags = [])
    {
        $value = $this->get($key);

        if ($value === null) {
            $value = $callback();
            $this->set($key, $value, $ttl, $tags);
        }

        return $value;
    }

    /**
     * Get multiple values at once
     *
     * @param array $keys Array of cache keys
     * @return array Key => value pairs
     */
    public function getMultiple(array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    /**
     * Set multiple values at once
     *
     * @param array $values Key => value pairs
     * @param int $ttl Time to live
     * @return bool Success
     */
    public function setMultiple(array $values, int $ttl = 3600): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Build full cache key with namespace
     *
     * @param string $key Base key
     * @return string Full key
     */
    private function buildKey(string $key): string
    {
        return "{$this->namespace}:{$key}";
    }

    /**
     * Get file path for cache key
     *
     * @param string $key Cache key
     * @return string File path
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->fileCache . "/{$hash}.cache";
    }

    /**
     * Clean up expired file cache entries
     *
     * @return int Number of files deleted
     */
    public function cleanup(): int
    {
        $count = 0;
        $files = glob($this->fileCache . '/*.cache');

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['expires'] > 0 && $data['expires'] < time()) {
                unlink($file);
                $count++;
            }
        }

        return $count;
    }
}
