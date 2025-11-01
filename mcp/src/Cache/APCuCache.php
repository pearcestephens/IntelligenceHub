<?php
/**
 * APCu Cache Backend
 *
 * @package IntelligenceHub\MCP\Cache
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Cache;

class APCuCache implements CacheInterface
{
    private string $prefix = 'mcp_';

    public function get(string $key)
    {
        $value = apcu_fetch($this->prefix . $key, $success);
        return $success ? $value : null;
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        return apcu_store($this->prefix . $key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return apcu_delete($this->prefix . $key);
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function getStats(): array
    {
        $info = apcu_cache_info();
        return [
            'available' => true,
            'entries' => $info['num_entries'] ?? 0,
            'memory_size' => $info['mem_size'] ?? 0,
            'hits' => $info['num_hits'] ?? 0,
            'misses' => $info['num_misses'] ?? 0,
        ];
    }
}
