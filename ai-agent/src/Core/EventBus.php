<?php

/**
 * EventBus - Minimal internal publish/dispatch mechanism backed by Redis.
 * Stores events in a Redis list; dispatcher cron pops and invokes handlers.
 */

declare(strict_types=1);

namespace App\Core;

use App\RedisClient;
use App\Logger;

class EventBus
{
    private const QUEUE_KEY = 'eventbus:queue';
    private const MAX_LEN   = 5000; // ring limit

    public static function publish(string $type, array $payload = []): bool
    {
        try {
            $event = [
                'id' => bin2hex(random_bytes(8)),
                'type' => $type,
                'ts' => time(),
                'payload' => $payload
            ];
            RedisClient::listPush(self::QUEUE_KEY, json_encode($event));
            RedisClient::listTrim(self::QUEUE_KEY, 0, self::MAX_LEN - 1);
            return true;
        } catch (\Throwable $e) {
            Logger::error('eventbus.publish_failed', ['type' => $type,'error' => $e->getMessage()]);
            return false;
        }
    }

    public static function fetchBatch(int $count = 50): array
    {
        $events = [];
        for ($i = 0; $i < $count; $i++) {
            $raw = RedisClient::rpop(self::QUEUE_KEY);
            if (!$raw) {
                break;
            }
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $events[] = $data;
            }
        }
        return $events;
    }
}
