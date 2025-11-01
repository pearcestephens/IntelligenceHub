<?php
/**
 * Redis Tool - Cache Management & Analysis
 *
 * Manage Redis cache, analyze keys, and monitor performance
 */

namespace MCP\Tools;

class RedisTool
{
    protected string $name = 'redis';
    protected string $description = 'Redis cache management, key analysis, and performance monitoring';

    protected array $inputSchema = [
        'type' => 'object',
        'properties' => [
            'action' => [
                'type' => 'string',
                'enum' => ['get', 'set', 'delete', 'keys', 'stats', 'flush', 'ttl', 'info'],
                'description' => 'Redis operation to perform'
            ],
            'key' => [
                'type' => 'string',
                'description' => 'Cache key'
            ],
            'value' => [
                'type' => 'string',
                'description' => 'Value to set (for set action)'
            ],
            'ttl' => [
                'type' => 'integer',
                'description' => 'Time to live in seconds',
                'default' => 3600
            ],
            'pattern' => [
                'type' => 'string',
                'description' => 'Key pattern for search (e.g., user:*)',
                'default' => '*'
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Maximum keys to return',
                'default' => 100
            ]
        ],
        'required' => ['action']
    ];

    private $redis;

    public function execute(array $arguments): array
    {
        $action = $arguments['action'];

        try {
            $this->connectRedis();

            switch ($action) {
                case 'get':
                    return $this->getKey($arguments['key'] ?? null);

                case 'set':
                    return $this->setKey($arguments);

                case 'delete':
                    return $this->deleteKey($arguments['key'] ?? null);

                case 'keys':
                    return $this->searchKeys($arguments['pattern'] ?? '*', $arguments['limit'] ?? 100);

                case 'stats':
                    return $this->getStats();

                case 'flush':
                    return $this->flushCache($arguments['pattern'] ?? null);

                case 'ttl':
                    return $this->getTTL($arguments['key'] ?? null);

                case 'info':
                    return $this->getInfo();

                default:
                    throw new \Exception("Unknown action: {$action}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function connectRedis(): void
    {
        if ($this->redis) {
            return;
        }

        if (!class_exists('Redis')) {
            throw new \Exception('Redis extension not installed');
        }

        $this->redis = new \Redis();

        // Try to connect (adjust host/port as needed)
        $connected = @$this->redis->connect('127.0.0.1', 6379, 2.5);

        if (!$connected) {
            throw new \Exception('Failed to connect to Redis');
        }

        // Test connection
        try {
            $this->redis->ping();
        } catch (\Exception $e) {
            throw new \Exception('Redis connection test failed: ' . $e->getMessage());
        }
    }

    private function getKey(?string $key): array
    {
        if (!$key) {
            throw new \Exception('Key is required');
        }

        $value = $this->redis->get($key);
        $ttl = $this->redis->ttl($key);
        $type = $this->redis->type($key);

        return [
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
                'ttl' => $ttl,
                'type' => $this->getTypeName($type),
                'exists' => $value !== false
            ]
        ];
    }

    private function setKey(array $args): array
    {
        $key = $args['key'] ?? null;
        $value = $args['value'] ?? null;
        $ttl = $args['ttl'] ?? 3600;

        if (!$key || !$value) {
            throw new \Exception('Key and value are required');
        }

        $result = $this->redis->setex($key, $ttl, $value);

        return [
            'success' => true,
            'data' => [
                'key' => $key,
                'ttl' => $ttl,
                'set' => $result
            ]
        ];
    }

    private function deleteKey(?string $key): array
    {
        if (!$key) {
            throw new \Exception('Key is required');
        }

        $deleted = $this->redis->del($key);

        return [
            'success' => true,
            'data' => [
                'key' => $key,
                'deleted' => $deleted > 0
            ]
        ];
    }

    private function searchKeys(string $pattern, int $limit): array
    {
        $keys = [];
        $cursor = null;

        do {
            $result = $this->redis->scan($cursor, $pattern, $limit);

            if ($result === false) {
                break;
            }

            foreach ($result as $key) {
                $keys[] = [
                    'key' => $key,
                    'ttl' => $this->redis->ttl($key),
                    'type' => $this->getTypeName($this->redis->type($key))
                ];

                if (count($keys) >= $limit) {
                    break 2;
                }
            }
        } while ($cursor !== 0);

        return [
            'success' => true,
            'data' => $keys,
            'count' => count($keys),
            'pattern' => $pattern
        ];
    }

    private function getStats(): array
    {
        $info = $this->redis->info();

        $stats = [
            'memory' => [
                'used' => $info['used_memory_human'] ?? 'N/A',
                'peak' => $info['used_memory_peak_human'] ?? 'N/A',
                'rss' => $info['used_memory_rss_human'] ?? 'N/A'
            ],
            'clients' => [
                'connected' => $info['connected_clients'] ?? 0,
                'blocked' => $info['blocked_clients'] ?? 0
            ],
            'stats' => [
                'total_connections' => $info['total_connections_received'] ?? 0,
                'total_commands' => $info['total_commands_processed'] ?? 0,
                'ops_per_sec' => $info['instantaneous_ops_per_sec'] ?? 0
            ],
            'keyspace' => $this->getKeyspaceStats()
        ];

        return [
            'success' => true,
            'data' => $stats
        ];
    }

    private function getKeyspaceStats(): array
    {
        $info = $this->redis->info('keyspace');

        $keyspace = [];
        foreach ($info as $db => $data) {
            if (preg_match('/^db(\d+)$/', $db, $matches)) {
                $dbNum = $matches[1];
                preg_match_all('/(\w+)=(\d+)/', $data, $matches, PREG_SET_ORDER);

                $stats = [];
                foreach ($matches as $match) {
                    $stats[$match[1]] = (int)$match[2];
                }

                $keyspace["db{$dbNum}"] = $stats;
            }
        }

        return $keyspace;
    }

    private function flushCache(?string $pattern): array
    {
        if (!$pattern) {
            // Flush entire database (dangerous!)
            $this->redis->flushDB();

            return [
                'success' => true,
                'data' => ['flushed' => 'entire database']
            ];
        }

        // Flush keys matching pattern
        $deleted = 0;
        $cursor = null;

        do {
            $keys = $this->redis->scan($cursor, $pattern, 100);

            if ($keys === false) {
                break;
            }

            foreach ($keys as $key) {
                $deleted += $this->redis->del($key);
            }
        } while ($cursor !== 0);

        return [
            'success' => true,
            'data' => [
                'pattern' => $pattern,
                'deleted' => $deleted
            ]
        ];
    }

    private function getTTL(?string $key): array
    {
        if (!$key) {
            throw new \Exception('Key is required');
        }

        $ttl = $this->redis->ttl($key);

        return [
            'success' => true,
            'data' => [
                'key' => $key,
                'ttl' => $ttl,
                'expires_in' => $ttl > 0 ? $this->formatTTL($ttl) : 'never'
            ]
        ];
    }

    private function getInfo(): array
    {
        $info = $this->redis->info();

        return [
            'success' => true,
            'data' => [
                'server' => [
                    'version' => $info['redis_version'] ?? 'unknown',
                    'mode' => $info['redis_mode'] ?? 'standalone',
                    'uptime' => $this->formatUptime($info['uptime_in_seconds'] ?? 0)
                ],
                'memory' => [
                    'used' => $info['used_memory_human'] ?? 'N/A',
                    'peak' => $info['used_memory_peak_human'] ?? 'N/A'
                ],
                'persistence' => [
                    'loading' => ($info['loading'] ?? 0) == 1,
                    'rdb_changes' => $info['rdb_changes_since_last_save'] ?? 0
                ]
            ]
        ];
    }

    private function getTypeName(int $type): string
    {
        $types = [
            \Redis::REDIS_NOT_FOUND => 'none',
            \Redis::REDIS_STRING => 'string',
            \Redis::REDIS_SET => 'set',
            \Redis::REDIS_LIST => 'list',
            \Redis::REDIS_ZSET => 'zset',
            \Redis::REDIS_HASH => 'hash'
        ];

        return $types[$type] ?? 'unknown';
    }

    private function formatTTL(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . 'm';
        } elseif ($seconds < 86400) {
            return round($seconds / 3600) . 'h';
        } else {
            return round($seconds / 86400) . 'd';
        }
    }

    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}
