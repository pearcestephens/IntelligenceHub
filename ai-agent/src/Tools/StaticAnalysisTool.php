<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;
use App\Tools\Contracts\ToolContract;

final class StaticAnalysisTool implements ToolContract
{
    public static function run(array $params, array $context = []): array
    {
        $modes = $params['modes'] ?? ['phpstan','phpcs'];
        if (!is_array($modes)) {
            $modes = [$modes];
        }
        $paths = $params['paths'] ?? ['src'];
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $level = (string)($params['level'] ?? 'max');
        $standard = (string)($params['standard'] ?? 'PSR12');

        $root = realpath(__DIR__ . '/../../');
        $vendorBin = $root . '/vendor/bin';

        $results = [];
        $successAll = true;

        if (in_array('phpstan', $modes, true)) {
            $phpstan = $vendorBin . '/phpstan';
            if (!is_file($phpstan) || !is_executable($phpstan)) {
                $results['phpstan'] = ['success' => false, 'error' => 'phpstan_not_found', 'hint' => 'Run composer install'];
                $successAll = false;
            } else {
                $args = [
                    escapeshellarg($phpstan),
                    'analyse',
                    '--error-format=json',
                    '--no-progress',
                    '--level=' . escapeshellarg($level),
                ];
                foreach ($paths as $p) {
                    $args[] = escapeshellarg($p);
                }
                $cmd = implode(' ', $args);
                $out = self::exec($cmd, $root);
                $json = json_decode($out['stdout'] ?? '', true);
                $summary = [
                    'exit_code' => $out['exit'],
                    'totals' => $json['totals'] ?? null,
                    'errors' => $json['files'] ?? [],
                ];
                $results['phpstan'] = ['success' => $out['exit'] === 0, 'summary' => $summary];
                if ($out['exit'] !== 0) {
                    $successAll = false;
                }
            }
        }

        if (in_array('phpcs', $modes, true)) {
            $phpcs = $vendorBin . '/phpcs';
            if (!is_file($phpcs) || !is_executable($phpcs)) {
                $results['phpcs'] = ['success' => false, 'error' => 'phpcs_not_found', 'hint' => 'Run composer install'];
                $successAll = false;
            } else {
                $args = [
                    escapeshellarg($phpcs),
                    '--standard=' . escapeshellarg($standard),
                    '--report=json',
                ];
                foreach ($paths as $p) {
                    $args[] = escapeshellarg($p);
                }
                $cmd = implode(' ', $args);
                $out = self::exec($cmd, $root);
                $json = json_decode($out['stdout'] ?? '', true);
                $summary = [
                    'exit_code' => $out['exit'],
                    'totals' => $json['totals'] ?? null,
                    'files' => $json['files'] ?? [],
                ];
                $results['phpcs'] = ['success' => $out['exit'] === 0, 'summary' => $summary];
                if ($out['exit'] !== 0) {
                    $successAll = false;
                }
            }
        }

        return ['success' => $successAll, 'results' => $results];
    }

    public static function spec(): array
    {
        return [
            'name' => 'static_analysis',
            'description' => 'Run PHPStan and PHPCS and summarize results',
            'category' => 'development',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'modes' => ['type' => ['array','string']],
                    'paths' => ['type' => ['array','string']],
                    'level' => ['type' => 'string'],
                    'standard' => ['type' => 'string']
                ],
                'required' => []
            ],
            'safety' => ['timeout' => 180, 'rate_limit' => 1]
        ];
    }

    private static function exec(string $cmd, string $cwd): array
    {
        $desc = [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']];
        $proc = proc_open($cmd, $desc, $pipes, $cwd, $_ENV);
        if (!is_resource($proc)) {
            return ['exit' => 1, 'stdout' => '', 'stderr' => 'proc_open_failed'];
        }
        fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        Logger::info('StaticAnalysisTool exec', ['cmd' => $cmd, 'exit' => $code]);
        return ['exit' => $code, 'stdout' => $out, 'stderr' => $err];
    }
}
