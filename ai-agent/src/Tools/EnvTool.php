<?php

declare(strict_types=1);

namespace App\Tools;

use App\Config;
use App\Tools\Contracts\ToolContract;

final class EnvTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $keys = $params['keys'] ?? [];
        $mask = (bool)($params['mask'] ?? true);
        $out = [];
        $list = is_array($keys) ? $keys : [$keys];
        foreach ($list as $k) {
            $val = Config::get($k);
            if ($val === null) {
                $out[$k] = null;
                continue;
            }
            if ($mask && is_string($val) && strlen($val) > 8) {
                $out[$k] = substr($val, 0, 4) . '…' . substr($val, -4);
            } else {
                $out[$k] = $val;
            }
        }
        return ['success' => true,'values' => $out];
    }

    public static function spec(): array
    {
        return [
            'name' => 'env_tool',
            'description' => 'Read resolved config keys with optional masking',
            'category' => 'diagnostics',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'keys' => ['type' => ['array','string']],
                    'mask' => ['type' => 'boolean']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 5,
                'rate_limit' => 20
            ]
        ];
    }
}
