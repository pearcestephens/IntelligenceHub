<?php

declare(strict_types=1);

namespace App\Tools;

use App\RedisClient;
use App\Tools\Contracts\ToolContract;

final class RedisTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $action = (string)($params['action'] ?? 'get');
        $key = (string)($params['key'] ?? '');
        $ttl = (int)($params['ttl'] ?? 0);
        $value = $params['value'] ?? null;
        if ($key === '') {
            return ['success' => false,'error' => 'missing_key'];
        }
        switch ($action) {
            case 'get':
                return ['success' => true,'value' => RedisClient::get($key)];
            case 'set':
                return ['success' => RedisClient::set($key, $value, $ttl)];
            case 'delete':
                return ['success' => RedisClient::delete($key)];
            case 'exists':
                return ['success' => true,'exists' => RedisClient::exists($key)];
            default:
                return ['success' => false,'error' => 'unknown_action'];
        }
    }

    public static function spec(): array
    {
        return [
            'name' => 'redis_tool',
            'description' => 'Interact with Redis: get/set/delete/exists',
            'category' => 'data',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['get','set','delete','exists']],
                    'key' => ['type' => 'string', 'minLength' => 1],
                    'value' => ['type' => 'string'],
                    'ttl' => ['type' => 'integer', 'minimum' => 0]
                ],
                'required' => ['action','key']
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 20
            ]
        ];
    }
}
