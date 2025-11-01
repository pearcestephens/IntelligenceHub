<?php
/**
 * Redis Cache Backend
 *
 * @package IntelligenceHub\MCP\Cache
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Cache;

class RedisCache implements CacheInterface
{
    private \Redis $redis;
    private string $prefix = 'mcp:';

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key)
    {
        $value = $this->redis->get($this->prefix . $key);
        if ($value === false) {
            return null;
        }

        return json_decode($value, true);
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $serialized = json_encode($value);
        return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
    }

    public function delete(string $key): bool
    {
        return (bool)$this->redis->del($this->prefix . $key);
    }

    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (empty($keys)) {
            return true;
        }

        return (bool)$this->redis->del($keys);
    }

    public function getStats(): array
    {
        try {
            $info = $this->redis->info('stats');
            return [
                'available' => true,
                'keys' => count($this->redis->keys($this->prefix . '*')),
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses'])
                    ? round($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses']) * 100, 2)
                    : 0,
            ];
        } catch (\Exception $e) {
            return ['available' => false, 'error' => $e->getMessage()];
        }
    }
}
