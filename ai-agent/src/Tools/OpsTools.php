<?php

declare(strict_types=1);

/**
 * Ops-related bot-only tools: ReadyCheckTool and RepoCleanerTool
 * Allows the AI agent to run readiness checks and repository cleanup internally.
 *
 * @package App\Tools
 * @author Ecigdis
 */

namespace App\Tools;

use App\Logger;

final class ReadyCheckTool
{
    /**
     * Execute the ready check script and return its JSON/stdout as array
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

    public static function spec(): array
    {
        return [
            'name' => 'ready_check',
            'description' => 'Run environment readiness checks (bot-only)',
            'category' => 'ops',
            'internal' => true,
            'parameters' => ['type' => 'object','properties' => [],'required' => []],
            'safety' => ['timeout' => 30, 'rate_limit' => 5]
        ];
    }
}

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
        proc_close($proc); // repo-cleaner returns code 0 in bot mode always

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

    public static function spec(): array
    {
        return [
            'name' => 'repo_clean',
            'description' => 'List/archive/delete redundant files (bot-only)',
            'category' => 'ops',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'mode' => ['type' => 'string', 'enum' => ['list','archive','delete']],
                    'confirm' => ['type' => 'boolean'],
                    'no_dirs' => ['type' => 'boolean'],
                    'only' => ['type' => ['array','string']],
                    'dry_run' => ['type' => 'boolean']
                ],
                'required' => ['mode']
            ],
            'safety' => ['timeout' => 60, 'rate_limit' => 3]
        ];
    }
}
