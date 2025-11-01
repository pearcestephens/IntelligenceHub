<?php
/**
 * Cache Interface - Contract for all cache backends
 *
 * @package IntelligenceHub\MCP\Cache
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Cache;

interface CacheInterface
{
    /**
     * Get value from cache
     *
     * @param string $key Cache key
     * @return mixed|null Value or null if not found/expired
     */
    public function get(string $key);

    /**
     * Store value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to store
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public function set(string $key, $value, int $ttl = 3600): bool;

    /**
     * Delete value from cache
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache
     *
     * @return bool Success status
     */
    public function clear(): bool;

    /**
     * Get cache statistics
     *
     * @return array Stats array
     */
    public function getStats(): array;
}
