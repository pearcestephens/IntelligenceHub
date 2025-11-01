<?php

declare(strict_types=1);

namespace App\Tools;

use App\Tools\Contracts\ToolContract;

final class GrepTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $root = realpath(__DIR__ . '/../../');
        $pattern = (string)($params['pattern'] ?? '');
        $include = $params['include'] ?? ['src','public','ops'];
        $exclude = $params['exclude'] ?? ['vendor','node_modules','backups','logs'];
        $maxHits = (int)($params['max_hits'] ?? 200);
        if ($pattern === '') {
            return ['success' => false,'error' => 'missing_pattern'];
        }

        $results = [];
        $included = is_array($include) ? $include : [$include];
        $excluded = is_array($exclude) ? $exclude : [$exclude];

        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($rii as $file) {
            if (count($results) >= $maxHits) {
                break;
            }
            if (!$file->isFile()) {
                continue;
            }
            $path = $file->getPathname();
            $rel = substr($path, strlen($root) + 1);
            $skip = false;
            foreach ($excluded as $ex) {
                if (str_starts_with($rel, $ex . '/')) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }
            $ok = false;
            foreach ($included as $inc) {
                if (str_starts_with($rel, $inc . '/')) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                continue;
            }
            $data = @file_get_contents($path);
            if ($data === false) {
                continue;
            }
            if (preg_match('/' . $pattern . '/i', $data)) {
                $results[] = ['file' => $rel,'size' => strlen($data)];
            }
        }
        return ['success' => true,'count' => count($results),'results' => $results];
    }

    public static function spec(): array
    {
        return [
            'name' => 'grep_tool',
            'description' => 'Recursive code search with include/exclude filters',
            'category' => 'development',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'pattern' => ['type' => 'string', 'minLength' => 1],
                    'include' => ['type' => ['array','string']],
                    'exclude' => ['type' => ['array','string']],
                    'max_hits' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2000]
                ],
                'required' => ['pattern']
            ],
            'safety' => [
                'timeout' => 20,
                'rate_limit' => 5
            ]
        ];
    }
}
