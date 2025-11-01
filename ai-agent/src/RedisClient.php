<?php

/**
 * Redis client with vector search support (Redis Stack)
 * Provides connection management and helper methods for AI agent data
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

use Redis;
use RedisException;

class RedisClient
{
    private static ?Redis $connection = null;
    private static string $prefix = '';

    /**
     * Optional constructor for compatibility; no instance state required.
     */
    public function __construct(?Config $config = null, ?Logger $logger = null)
    {
        // No-op: static connection management is used.
    }

    /**
     * Get Redis connection (singleton)
     */
    public static function connection(): Redis
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Get value with automatic JSON decoding
     */
    public static function get(string $key): mixed
    {
        try {
            $value = self::connection()->get(self::prefixKey($key));

            if ($value === false) {
                return null;
            }

            $decoded = json_decode($value, true);
            return $decoded !== null ? $decoded : $value;
        } catch (RedisException $e) {
            Logger::error('Redis GET failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Set value with automatic JSON encoding
     */
    public static function set(string $key, mixed $value, int $ttl = 0): bool
    {
        try {
            $serialized = is_string($value) ? $value : json_encode($value);

            if ($ttl > 0) {
                return self::connection()->setex(self::prefixKey($key), $ttl, $serialized);
            } else {
                return self::connection()->set(self::prefixKey($key), $serialized);
            }
        } catch (RedisException $e) {
            Logger::error('Redis SET failed', [
                'key' => $key,
                'ttl' => $ttl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete key(s)
     */
    public static function delete(string $key): bool
    {
        try {
            $res = self::del($key);
            return (int)$res > 0;
        } catch (RedisException $e) {
            Logger::error('Redis DELETE failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Alias that supports deleting multiple keys at once (array or variadic behavior)
     * Returns the number of keys deleted.
     * @param string|array $keys
     */
    public static function del(string|array $keys): int
    {
        $redis = self::connection();
        if (is_array($keys)) {
            // Prefix all keys
            $prefixed = array_map(fn($k) => self::prefixKey((string)$k), $keys);
            return (int) $redis->del($prefixed);
        }
        return (int) $redis->del(self::prefixKey($keys));
    }

    /**
     * Check if key exists
     */
    public static function exists(string $key): bool
    {
        try {
            $res = self::connection()->exists(self::prefixKey($key));
            return (int)$res > 0;
        } catch (RedisException $e) {
            Logger::error('Redis EXISTS failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Set key expiration time in seconds
     */
    public static function expire(string $key, int $ttl): bool
    {
        try {
            return self::connection()->expire(self::prefixKey($key), $ttl);
        } catch (RedisException $e) {
            Logger::error('Redis EXPIRE failed', [
                'key' => $key,
                'ttl' => $ttl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add to list (left push)
     */
    public static function listPush(string $key, mixed $value): int
    {
        try {
            $serialized = is_string($value) ? $value : json_encode($value);
            return self::connection()->lpush(self::prefixKey($key), $serialized);
        } catch (RedisException $e) {
            Logger::error('Redis LPUSH failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /** Append to list (right push) */
    public static function listRightPush(string $key, mixed $value): int
    {
        try {
            $serialized = is_string($value) ? $value : json_encode($value);
            return self::connection()->rpush(self::prefixKey($key), $serialized);
        } catch (RedisException $e) {
            Logger::error('Redis RPUSH failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get list range with automatic JSON decoding
     */
    public static function listRange(string $key, int $start = 0, int $end = -1): array
    {
        try {
            $values = self::connection()->lrange(self::prefixKey($key), $start, $end);

            return array_map(function ($value) {
                $decoded = json_decode($value, true);
                return $decoded !== null ? $decoded : $value;
            }, $values);
        } catch (RedisException $e) {
            Logger::error('Redis LRANGE failed', [
                'key' => $key,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Trim list to specified size
     */
    public static function listTrim(string $key, int $start, int $end): bool
    {
        try {
            return self::connection()->ltrim(self::prefixKey($key), $start, $end);
        } catch (RedisException $e) {
            Logger::error('Redis LTRIM failed', [
                'key' => $key,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /** Pop from list (left) */
    public static function listPop(string $key): mixed
    {
        try {
            $val = self::connection()->lPop(self::prefixKey($key));
            if ($val === false) {
                return null;
            }
            $dec = json_decode((string)$val, true);
            return $dec !== null ? $dec : $val;
        } catch (RedisException $e) {
            Logger::error('Redis LPOP failed', ['key' => $key,'error' => $e->getMessage()]);
            return null;
        }
    }

    /** List length */
    public static function llen(string $key): int
    {
        try {
            return (int) self::connection()->lLen(self::prefixKey($key));
        } catch (RedisException $e) {
            Logger::error('Redis LLEN failed', ['key' => $key,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Sorted set add (score + member). Member is auto-JSON encoded if not string.
     */
    public static function zadd(string $key, int|float $score, mixed $member): int
    {
        try {
            $serialized = is_string($member) ? $member : json_encode($member);
            return (int) self::connection()->zAdd(self::prefixKey($key), $score, $serialized);
        } catch (RedisException $e) {
            Logger::error('Redis ZADD failed', [
                'key' => $key,
                'score' => $score,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Sorted set reverse range returning decoded JSON where applicable.
     */
    public static function zrevrange(string $key, int $start, int $end, bool $withScores = false): array
    {
        try {
            $raw = self::connection()->zRevRange(self::prefixKey($key), $start, $end, $withScores);
            if (!$withScores) {
                return array_map(function ($v) {
                    if (!is_string($v)) {
                        return $v; // already structured
                    }
                    $d = json_decode($v, true);
                    return $d !== null ? $d : $v;
                }, $raw);
            }
            $out = [];
            foreach ($raw as $member => $score) {
                $d = json_decode($member, true);
                $out[] = ['member' => $d !== null ? $d : $member,'score' => $score];
            }
            return $out;
        } catch (RedisException $e) {
            Logger::error('Redis ZREVRANGE failed', [
                'key' => $key,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /** Standard zrange wrapper */
    public static function zrange(string $key, int $start, int $end, bool $withScores = false): array
    {
        try {
            $raw = self::connection()->zRange(self::prefixKey($key), $start, $end, $withScores);
            if (!$withScores) {
                return array_map(function ($v) {
                    if (!is_string($v)) {
                        return $v;
                    }
                    $d = json_decode($v, true);
                    return $d !== null ? $d : $v;
                }, $raw);
            }
            $out = [];
            foreach ($raw as $member => $score) {
                $d = json_decode($member, true);
                $out[] = ['member' => $d !== null ? $d : $member,'score' => $score];
            }
            return $out;
        } catch (RedisException $e) {
            Logger::error('Redis ZRANGE failed', [
                'key' => $key,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /** Get sorted set cardinality */
    public static function zcard(string $key): int
    {
        try {
            return (int) self::connection()->zCard(self::prefixKey($key));
        } catch (RedisException $e) {
            Logger::error('Redis ZCARD failed', ['key' => $key,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Set add (SADD) */
    public static function sadd(string $key, string|int|float ...$members): int
    {
        try {
            return (int) self::connection()->sAdd(self::prefixKey($key), ...$members);
        } catch (RedisException $e) {
            Logger::error('Redis SADD failed', ['key' => $key,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Set members (SMEMBERS) */
    public static function smembers(string $key): array
    {
        try {
            $res = self::connection()->sMembers(self::prefixKey($key));
            return is_array($res) ? $res : [];
        } catch (RedisException $e) {
            Logger::error('Redis SMEMBERS failed', ['key' => $key,'error' => $e->getMessage()]);
            return [];
        }
    }

    /** Set remove (SREM) */
    public static function srem(string $key, string|int|float ...$members): int
    {
        try {
            return (int) self::connection()->sRem(self::prefixKey($key), ...$members);
        } catch (RedisException $e) {
            Logger::error('Redis SREM failed', ['key' => $key,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Remove members in sorted set by score range */
    public static function zremrangebyscore(string $key, int|float $min, int|float $max): int
    {
        try {
            return (int) self::connection()->zRemRangeByScore(self::prefixKey($key), (string)$min, (string)$max);
        } catch (RedisException $e) {
            Logger::error('Redis ZREMRANGEBYSCORE failed', ['key' => $key,'min' => $min,'max' => $max,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Atomic increment */
    public static function incr(string $key, int $by = 1): int
    {
        try {
            return (int) self::connection()->incrBy(self::prefixKey($key), $by);
        } catch (RedisException $e) {
            Logger::error('Redis INCR failed', ['key' => $key,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Hash increment */
    public static function hincrby(string $key, string $field, int $by = 1): int
    {
        try {
            return (int) self::connection()->hIncrBy(self::prefixKey($key), $field, $by);
        } catch (RedisException $e) {
            Logger::error('Redis HINCRBY failed', ['key' => $key,'field' => $field,'error' => $e->getMessage()]);
            return 0;
        }
    }

    /** Hash get field */
    public static function hget(string $key, string $field): mixed
    {
        try {
            $value = self::connection()->hGet(self::prefixKey($key), $field);
            if ($value === false) {
                return null;
            }

            // Try to decode JSON, fallback to string
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        } catch (RedisException $e) {
            Logger::error('Redis HGET failed', ['key' => $key,'field' => $field,'error' => $e->getMessage()]);
            return null;
        }
    }

    /** Hash set field */
    public static function hset(string $key, string $field, mixed $value): bool
    {
        try {
            $serialized = is_string($value) ? $value : json_encode($value);
            return (bool) self::connection()->hSet(self::prefixKey($key), $field, $serialized);
        } catch (RedisException $e) {
            Logger::error('Redis HSET failed', ['key' => $key,'field' => $field,'error' => $e->getMessage()]);
            return false;
        }
    }


    /**
     * Publish message to channel
     */
    public static function publish(string $channel, mixed $message): int
    {
        try {
            $serialized = is_string($message) ? $message : json_encode($message);
            return self::connection()->publish(self::prefixKey($channel), $serialized);
        } catch (RedisException $e) {
            Logger::error('Redis PUBLISH failed', [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Multi-set keys (array of key => value)
     */
    public static function mset(array $map): bool
    {
        try {
            if (empty($map)) {
                return true;
            }
            $prefixed = [];
            foreach ($map as $k => $v) {
                $prefixed[self::prefixKey((string)$k)] = is_string($v) ? $v : json_encode($v);
            }
            return (bool) self::connection()->mset($prefixed);
        } catch (RedisException $e) {
            Logger::error('Redis MSET failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Multi-get keys (array of keys) with JSON auto decode
     */
    public static function mget(array $keys): array
    {
        try {
            if (empty($keys)) {
                return [];
            }
            $prefixed = array_map(fn($k) => self::prefixKey((string)$k), $keys);
            $values = self::connection()->mget($prefixed) ?: [];
            $out = [];
            foreach ($values as $i => $val) {
                $decoded = is_string($val) ? json_decode($val, true) : null;
                $out[$keys[$i]] = $decoded !== null ? $decoded : $val;
            }
            return $out;
        } catch (RedisException $e) {
            Logger::error('Redis MGET failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Increment counter with TTL
     */
    public static function increment(string $key, int $ttl = 0): int
    {
        try {
            $redis = self::connection();
            $count = $redis->incr(self::prefixKey($key));

            if ($ttl > 0 && $count === 1) {
                $redis->expire(self::prefixKey($key), $ttl);
            }

            return $count;
        } catch (RedisException $e) {
            Logger::error('Redis INCR failed', [
                'key' => $key,
                'ttl' => $ttl,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Vector search using Redis Stack (FT.SEARCH)
     */
    public static function vectorSearch(string $indexName, array $vector, int $limit = 10, float $minScore = 0.0): array
    {
        try {
            $redis = self::connection();
            $vec = pack('f*', ...$vector);
            $hex = bin2hex($vec);
            // Return id + score only; index schema can return other fields if present
            $q = "*=>[KNN{$limit} @embedding $hex AS score]";
            $res = $redis->rawCommand('FT.SEARCH', $indexName, $q, 'PARAMS', '2', 'embedding', $hex, 'SORTBY', 'score', 'DIALECT', '2', 'RETURN', '2', '__key', 'score');
            if (!is_array($res) || count($res) < 2) {
                return [];
            }
            $out = [];
            for ($i = 1; $i < count($res); $i += 2) {
                $doc = $res[$i];
                $fields = $res[$i + 1] ?? [];
                $map = [];
                for ($j = 0; $j < count($fields); $j += 2) {
                    $map[$fields[$j]] = $fields[$j + 1] ?? null;
                }
                $score = isset($map['score']) ? (float)$map['score'] : 0.0;
                if ($score >= $minScore) {
                    $out[] = ['id' => $doc, 'score' => $score];
                }
            }
            return $out;
        } catch (\RedisException $e) {
            Logger::error('Redis vector search failed', ['index' => $indexName,'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Add document to vector index
     */
    public static function vectorAdd(string $indexName, string $docId, array $embedding, array $fields = []): bool
    {
        try {
            $redis = self::connection();
            $key = "doc:$docId";
            $args = ['HSET', $key, 'embedding', pack('f*', ...$embedding)];
            foreach ($fields as $k => $v) {
                $args[] = $k;
                $args[] = is_string($v) ? $v : json_encode($v);
            }
            $redis->rawCommand(...$args);
            return true;
        } catch (\RedisException $e) {
            Logger::error('Redis vector add failed', ['index' => $indexName,'doc' => $docId,'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create vector index if not exists
     */
    public static function createVectorIndex(string $indexName, array $schema): bool
    {
        try {
            $redis = self::connection();

            // Check if index exists
            try {
                $redis->rawCommand('FT.INFO', $indexName);
                return true; // Index already exists
            } catch (RedisException $e) {
                // Index doesn't exist, create it
            }

            $args = ['FT.CREATE', $indexName, 'ON', 'HASH', 'PREFIX', '1', 'doc:', 'SCHEMA'];

            foreach ($schema as $field => $config) {
                $args[] = $field;
                $args[] = $config['type'];

                if (isset($config['options'])) {
                    $args = array_merge($args, $config['options']);
                }
            }

            $result = $redis->rawCommand(...$args);

            Logger::info('Vector index created', [
                'index' => $indexName,
                'schema_fields' => count($schema)
            ]);

            return $result === 'OK';
        } catch (RedisException $e) {
            Logger::error('Redis create vector index failed', [
                'index' => $indexName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete a vector document from index by its docId
     */
    public static function vectorDelete(string $indexName, string $docId): bool
    {
        try {
            $redis = self::connection();
            $key = "doc:$docId";
            // Delete the HASH key representing the vector doc; index updates automatically
            $deleted = $redis->del($key);
            return (int)$deleted > 0;
        } catch (\RedisException $e) {
            Logger::error('Redis vector delete failed', ['index' => $indexName, 'doc' => $docId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if Redis is healthy
     */
    public static function isHealthy(): bool
    {
        try {
            return (bool) self::connection()->ping();
        } catch (\Throwable $e) {
            Logger::error('Redis health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Instance wrapper: ping the Redis server
     */
    public function ping(): bool
    {
        return self::isHealthy();
    }

    /**
     * Get Redis info
     */
    public static function getInfo(): array
    {
        try {
            $info = self::connection()->info();

            return [
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'used_memory_human' => $info['used_memory_human'] ?? 'unknown',
                'total_commands_processed' => $info['total_commands_processed'] ?? 0
            ];
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Instance wrapper: Redis INFO
     * @param string|null $section e.g., 'stats', 'memory'
     */
    public function info(?string $section = null): array
    {
        try {
            if ($section === null) {
                return self::connection()->info();
            }
            return self::connection()->info($section);
        } catch (\Throwable $e) {
            Logger::error('Redis INFO failed', ['section' => $section, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Add key prefix
     */
    private static function prefixKey(string $key): string
    {
        return self::$prefix . $key;
    }

    /**
     * Establish Redis connection
     */
    private static function connect(): void
    {
        $redisUrl = Config::get('REDIS_URL');
        self::$prefix = Config::get('REDIS_PREFIX');

        $urlParts = parse_url($redisUrl);
        $host = $urlParts['host'] ?? '127.0.0.1';
        $port = $urlParts['port'] ?? 6379;
        $password = $urlParts['pass'] ?? null;
        $database = isset($urlParts['path']) ? (int)ltrim($urlParts['path'], '/') : 0;

        try {
            self::$connection = new Redis();

            if (!self::$connection->connect($host, $port, 5.0)) {
                throw new RedisException('Failed to connect to Redis');
            }

            if ($password) {
                self::$connection->auth($password);
            }

            if ($database > 0) {
                self::$connection->select($database);
            }

            Logger::info('Redis connected', [
                'host' => $host,
                'port' => $port,
                'database' => $database
            ]);
        } catch (RedisException $e) {
            Logger::error('Redis connection failed', [
                'host' => $host,
                'port' => $port,
                'error' => $e->getMessage()
            ]);

            throw new \RuntimeException('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Close connection (for testing)
     */
    public static function disconnect(): void
    {
        if (self::$connection) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
