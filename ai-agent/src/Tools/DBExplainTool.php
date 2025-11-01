<?php

declare(strict_types=1);

namespace App\Tools;

use App\DB;
use App\Tools\Contracts\ToolContract;

final class DBExplainTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $query = trim((string)($params['query'] ?? ''));
        if ($query === '' || stripos($query, 'select') !== 0) {
            return ['success' => false,'error' => 'only_select_supported'];
        }
        $plan = DB::select('EXPLAIN ' . $query);
        return ['success' => true,'plan' => $plan];
    }

    public static function spec(): array
    {
        return [
            'name' => 'db_explain',
            'description' => 'Explain a SELECT query to view the execution plan',
            'category' => 'data',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'minLength' => 7]
                ],
                'required' => ['query']
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 10
            ]
        ];
    }
}
