<?php

declare(strict_types=1);

namespace App\Chat\Threads;

use App\RedisClient;

class ThreadRepository
{
    private const KEY_PREFIX = 'chat:thread:';
    private const INDEX_KEY = 'chat:threads:index';

    public function save(Thread $thread): void
    {
        $data = [
            'id' => $thread->id,
            'parent' => $thread->parentId,
            'messages' => $thread->messages,
            'created_at' => $thread->createdAt,
            'tags' => $thread->tags
        ];
        RedisClient::set(self::KEY_PREFIX . $thread->id, json_encode($data), 0);

        // Use numeric timestamp as score, thread id as member
        RedisClient::zadd(self::INDEX_KEY, $thread->createdAt, $thread->id);
    }

    public function get(string $id): ?Thread
    {
        $raw = RedisClient::get(self::KEY_PREFIX . $id);
        if (!$raw) {
            return null;
        }
        $payload = json_decode($raw, true) ?: [];
        $t = new Thread($payload['parent'] ?? null, $payload['id'] ?? $id);
        $t->messages = $payload['messages'] ?? [];
        $t->createdAt = $payload['created_at'] ?? time();
        $t->tags = $payload['tags'] ?? [];
        return $t;
    }

    /** @return array<int,string> */
    public function recent(int $limit = 20): array
    {
        return RedisClient::zrevrange(self::INDEX_KEY, 0, $limit - 1);
    }
}
