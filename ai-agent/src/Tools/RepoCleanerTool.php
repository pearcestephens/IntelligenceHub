<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;

final class RepoCleanerTool
{
    /**
     * Execute the repo-cleaner with TOOL_PARAMETERS and return its JSON result
     * @param array $params { mode, confirm, no_dirs, only, dry_run }
     * @param array $context
     * @return array
     */
    public static function run(array $params, array $context = []): array
    {
        $script = realpath(__DIR__ . '/../../ops/repo-cleaner.php');
        if ($script === false || !file_exists($script)) {
            throw new \RuntimeException('repo-cleaner.php not found');
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
            throw new \RuntimeException('Failed to start repo-cleaner process');
        }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc); // repo-cleaner exits 0 in bot-mode

        $json = json_decode((string)$stdout, true);
        if (!is_array($json)) {
            Logger::warning('repo-cleaner returned non-JSON output', ['stdout' => $stdout, 'stderr' => $stderr]);
            return [
                'success' => false,
                'error' => 'non_json_output',
                'stdout' => trim((string)$stdout),
                'stderr' => trim((string)$stderr),
            ];
        }

        return $json;
    }
}
