<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;

final class ReadyCheckTool
{
    /**
     * Execute the ready-check script and return its stdout plus success flag.
     * @param array $params Unused currently; reserved for future flags
     * @param array $context Execution context
     * @return array{success:bool, output:string}
     */
    public static function run(array $params, array $context = []): array
    {
        $script = realpath(__DIR__ . '/../../ops/ready-check.php');
        if ($script === false || !file_exists($script)) {
            throw new \RuntimeException('ready-check.php not found');
        }

        $env = array_merge($_ENV, [
            'TOOL_PARAMETERS' => json_encode($params),
            'TOOL_CONTEXT' => json_encode($context),
        ]);

        $cmd = 'php ' . escapeshellarg($script);
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($cmd, $desc, $pipes, dirname($script), $env);
        if (!is_resource($proc)) {
            throw new \RuntimeException('Failed to start ready-check process');
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);

        $ok = ($code === 0);
        if (!$ok) {
            Logger::warning('ready-check exited non-zero', ['code' => $code, 'stderr' => $stderr]);
        }

        return [
            'success' => $ok,
            'output' => trim((string)$stdout),
        ];
    }
}
