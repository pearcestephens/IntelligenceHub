<?php

declare(strict_types=1);

namespace App\Tools;

use App\Tools\Contracts\ToolContract;

final class LogsTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $path = $params['path'] ?? __DIR__ . '/../../logs/operations.log';
        $maxBytes = (int)($params['max_bytes'] ?? 20000);
        $grep = isset($params['grep']) ? (string)$params['grep'] : null;
        if (!is_file($path) || !is_readable($path)) {
            return ['success' => false, 'error' => 'log_not_readable', 'path' => $path];
        }
        $size = filesize($path) ?: 0;
        $start = max(0, $size - $maxBytes);
        $fh = fopen($path, 'rb');
        if (!$fh) {
            return ['success' => false, 'error' => 'open_failed'];
        }
        if ($start > 0) {
            fseek($fh, $start);
        }
        $data = stream_get_contents($fh) ?: '';
        fclose($fh);
        if ($grep) {
            $lines = preg_grep('/' . preg_quote($grep, '/') . '/i', explode("\n", $data));
            $data = implode("\n", $lines);
        }
        return ['success' => true, 'bytes' => strlen($data), 'tail' => $data, 'path' => $path];
    }

    public static function spec(): array
    {
        return [
            'name' => 'logs_tool',
            'description' => 'Tail logs with optional grep filter',
            'category' => 'diagnostics',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'path' => ['type' => 'string'],
                    'max_bytes' => ['type' => 'integer', 'minimum' => 1000, 'maximum' => 1000000],
                    'grep' => ['type' => 'string']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 10
            ]
        ];
    }
}
