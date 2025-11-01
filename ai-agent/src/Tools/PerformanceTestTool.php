<?php

declare(strict_types=1);

namespace App\Tools;

final class PerformanceTestTool
{
    public static function run(array $params, array $context = []): array
    {
        $script = realpath(__DIR__ . '/../../ops/performance-tester.php');
        if ($script === false || !file_exists($script)) {
            throw new \RuntimeException('performance-tester.php not found');
        }
        $env = array_merge($_ENV, [
            'TOOL_PARAMETERS' => json_encode($params),
            'TOOL_CONTEXT' => json_encode($context),
        ]);
        $cmd = 'php ' . escapeshellarg($script);
        $proc = proc_open($cmd, [0 => ['pipe','r'],1 => ['pipe','w'],2 => ['pipe','w']], $pipes, dirname($script), $env);
        if (!is_resource($proc)) {
            throw new \RuntimeException('Failed to start performance tester');
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        $json = json_decode((string)$stdout, true);
        if (is_array($json)) {
            $json['exit_code'] = $code;
            return $json;
        }
        return ['success' => $code === 0, 'exit_code' => $code, 'output' => trim((string)$stdout), 'error_output' => trim((string)$stderr)];
    }

    public static function spec(): array
    {
        return [
            'name' => 'performance_test',
            'description' => 'Run performance tests (depends on ops/performance-tester.php)',
            'category' => 'ops',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => ['type' => 'string'],
                    'duration' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 600],
                    'concurrency' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 64],
                    'endpoints' => ['type' => ['array','string']]
                ],
                'required' => []
            ],
            'safety' => ['timeout' => 180, 'rate_limit' => 1]
        ];
    }
}
