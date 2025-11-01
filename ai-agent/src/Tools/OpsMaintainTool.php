<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;

final class OpsMaintainTool
{
    /**
     * Run list -> archive(dry_run) -> archive -> ready-check
     * Params: { only?: string[]|string, no_dirs?: bool, dry_run_first?: bool }
     */
    public static function run(array $params, array $context = []): array
    {
        $only = $params['only'] ?? [];
        $noDirs = (bool)($params['no_dirs'] ?? false);
        $dryRunFirst = (bool)($params['dry_run_first'] ?? true);

        // Step 1: List
        $list = RepoCleanerTool::run([
            'mode' => 'list',
            'only' => $only,
            'no_dirs' => $noDirs,
        ], $context);

        // Step 2: Archive (dry-run)
        $archiveDry = null;
        if ($dryRunFirst) {
            $archiveDry = RepoCleanerTool::run([
                'mode' => 'archive',
                'confirm' => true,
                'only' => $only,
                'no_dirs' => $noDirs,
                'dry_run' => true,
            ], $context);
        }

        // Step 3: Archive (real)
        $archiveReal = RepoCleanerTool::run([
            'mode' => 'archive',
            'confirm' => true,
            'only' => $only,
            'no_dirs' => $noDirs,
            'dry_run' => false,
        ], $context);

        // Step 4: Ready check
        $ready = ReadyCheckTool::run([], $context);

        return [
            'success' => ($archiveReal['success'] ?? false) && ($ready['success'] ?? false),
            'list' => $list,
            'archive_dry_run' => $archiveDry,
            'archive' => $archiveReal,
            'ready' => $ready,
        ];
    }
}
