<?php
/**
 * Cache Manager - Multi-level caching system
 *
 * Supports Redis (primary), APCu (secondary), and File (fallback)
 * Reuses existing infrastructure where possible
 *
 * @package IntelligenceHub\MCP\Cache
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Cache;

class CacheManager
{
    private array $backends = [];
    private array $config;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'enable_redis' => extension_loaded('redis'),
            'enable_apcu' => extension_loaded('apcu') && ini_get('apc.enabled'),
            'enable_file' => true,
            'redis_host' => '127.0.0.1',
            'redis_port' => 6379,
            'file_path' => sys_get_temp_dir() . '/mcp_cache',
            'ttl' => 3600,
        ], $config);

        $this->initializeBackends();
    }

    /**
     * Initialize available cache backends
     */
    private function initializeBackends(): void
    {
        // Try Redis first (fastest)
        if ($this->config['enable_redis']) {
            try {
                $redis = new \Redis();
                if ($redis->connect($this->config['redis_host'], $this->config['redis_port'])) {
                    $this->backends['redis'] = new RedisCache($redis);
                }
            } catch (\Exception $e) {
                // Redis not available, skip
            }
        }

        // Try APCu (in-memory, fast)
        if ($this->config['enable_apcu']) {
            $this->backends['apcu'] = new APCuCache();
        }

        // File cache (always available)
        if ($this->config['enable_file']) {
            $this->backends['file'] = new FileCache($this->config['file_path']);
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key)
    {
        foreach ($this->backends as $name => $backend) {
            try {
                $value = $backend->get($key);
                if ($value !== null) {
                    $this->stats['hits']++;

                    // Populate higher-priority caches
                    $this->populateUpperCaches($key, $value, $name);

                    return $value;
                }
            } catch (\Exception $e) {
                // Try next backend
                continue;
            }
        }

        $this->stats['misses']++;
        return null;
    }

    /**
     * Set value in cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->config['ttl'];
        $success = false;

        foreach ($this->backends as $backend) {
            try {
                if ($backend->set($key, $value, $ttl)) {
                    $success = true;
                }
            } catch (\Exception $e) {
                // Continue to next backend
            }
        }

        if ($success) {
            $this->stats['sets']++;
        }

        return $success;
    }

    /**
     * Delete value from cache
     */
    public function delete(string $key): bool
    {
        $success = false;

        foreach ($this->backends as $backend) {
            try {
                if ($backend->delete($key)) {
                    $success = true;
                }
            } catch (\Exception $e) {
                // Continue to next backend
            }
        }

        if ($success) {
            $this->stats['deletes']++;
        }

        return $success;
    }

    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $success = true;

        foreach ($this->backends as $backend) {
            try {
                $backend->clear();
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $backendStats = [];
        foreach ($this->backends as $name => $backend) {
            $backendStats[$name] = $backend->getStats();
        }

        return [
            'backends' => array_keys($this->backends),
            'total_hits' => $this->stats['hits'],
            'total_misses' => $this->stats['misses'],
            'total_sets' => $this->stats['sets'],
            'total_deletes' => $this->stats['deletes'],
            'hit_rate' => $this->stats['hits'] + $this->stats['misses'] > 0
                ? round($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses']) * 100, 2)
                : 0,
            'backend_stats' => $backendStats,
        ];
    }

    /**
     * Populate higher-priority caches with value found in lower-priority cache
     */
    private function populateUpperCaches(string $key, $value, string $foundIn): void
    {
        $foundIndex = array_search($foundIn, array_keys($this->backends));
        if ($foundIndex === false) {
            return;
        }

        $upperBackends = array_slice($this->backends, 0, $foundIndex, true);
        foreach ($upperBackends as $backend) {
            try {
                $backend->set($key, $value, $this->config['ttl']);
            } catch (\Exception $e) {
                // Ignore errors in cache population
            }
        }
    }

    /**
     * Get list of available backends
     */
    public function getAvailableBackends(): array
    {
        return array_keys($this->backends);
    }
}
