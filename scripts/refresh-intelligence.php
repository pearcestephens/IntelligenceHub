#!/usr/bin/env php
<?php
/**
 * Intelligence Content Refresh Script
 *
 * Refreshes intelligence content by triggering Scanner V3.0 batch scan
 * Runs every 4 hours via cron
 *
 * @package IntelligenceHub
 * @author AI Development Assistant
 * @created 2025-11-02
 */

declare(strict_types=1);

use Scanner\Lib\QuickScanService;

// Set time limit for long-running scans
set_time_limit(3600); // 1 hour max
ini_set('memory_limit', '1G');

// Bootstrap
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/scanner/vendor/autoload.php';

mb_internal_encoding('UTF-8');

$startTime = microtime(true);
echo '[' . date('Y-m-d H:i:s') . "] Intelligence Refresh Started\n";

$pdo = getIntelligenceDB();

/**
 * Sanitize string to ensure valid UTF-8 encoding.
 */
function sanitizeUtf8(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    if (mb_detect_encoding($value, 'UTF-8', true) === false) {
        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if ($converted === false) {
            return null;
        }
        $value = $converted;
    }

    return $value;
}

/**
 * Truncate text safely for UTF-8 strings.
 */
function truncateUtf8(string $value, int $length): string
{
    if (mb_strlen($value, 'UTF-8') <= $length) {
        return $value;
    }

    return mb_substr($value, 0, $length, 'UTF-8');
}

/**
 * Ingest project files into intelligence_files
 *
 * @param PDO $pdo
 * @param int $projectId
 * @param string $projectPath
 * @return array{indexed:int,skipped:int}
 */
function ingestProject(PDO $pdo, int $projectId, string $projectPath): array
{
    $indexed = 0;
    $skipped = 0;

    if (!is_dir($projectPath)) {
        echo "[WARN] Project path missing: {$projectPath}\n";
        return ['indexed' => 0, 'skipped' => 0];
    }

    $projectPath = rtrim($projectPath, '/');

    $ignoreDirs = ['vendor', 'node_modules', '.git', 'storage', 'logs', 'tmp', 'cache', 'tests'];
    $ignoreExtensions = ['lock', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'bmp', 'ico', 'map', 'mp4', 'mp3'];

    $allowedExtensions = ['php', 'inc', 'phtml', 'js', 'ts', 'tsx', 'vue', 'css', 'scss', 'less', 'json', 'yml', 'yaml', 'xml', 'sql', 'md', 'txt', 'ini', 'conf'];

    $delete = $pdo->prepare('DELETE FROM intelligence_files WHERE project_id = ?');
    $delete->execute([$projectId]);

    $insertSql = <<<'SQL'
        INSERT IGNORE INTO intelligence_files (
            project_id,
            business_unit_id,
            server_id,
            file_path,
            target_file,
            file_name,
            file_type,
            file_size,
            file_content,
            metadata,
            intelligence_type,
            intelligence_data,
            content_summary,
            is_active,
            extracted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    SQL;
    $insert = $pdo->prepare($insertSql);

    $serverId = 'unknown';
    if (preg_match('#applications/([^/]+)/#', $projectPath . '/', $matches)) {
        $serverId = $matches[1];
    }

    $directory = new RecursiveDirectoryIterator($projectPath, FilesystemIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($directory);

    foreach ($iterator as $fileInfo) {
        /** @var SplFileInfo $fileInfo */
        if (!$fileInfo->isFile()) {
            continue;
        }

        $relativePath = substr($fileInfo->getPathname(), strlen($projectPath) + 1);

        $shouldSkip = false;
        foreach ($ignoreDirs as $dir) {
            if (preg_match('#(^|/)' . preg_quote($dir, '#') . '(/|$)#', $relativePath)) {
                $shouldSkip = true;
                break;
            }
        }

        if ($shouldSkip) {
            $skipped++;
            continue;
        }

        $extension = strtolower($fileInfo->getExtension());

        if ($extension && in_array($extension, $ignoreExtensions, true)) {
            $skipped++;
            continue;
        }

        if ($extension && !in_array($extension, $allowedExtensions, true)) {
            $skipped++;
            continue;
        }

        $fullPath = $fileInfo->getPathname();
        $fileSize = $fileInfo->getSize();

        $fileType = in_array($extension, ['md', 'txt', 'html']) ? 'documentation' : 'code_intelligence';
        $intelligenceType = ($fileType === 'documentation' ? 'documentation_' : 'code_') . ($extension ?: 'none');

        $contentSample = '';
        if (is_readable($fullPath) && $fileSize <= 2097152) { // 2 MB cap
            $contentSample = sanitizeUtf8((string)@file_get_contents($fullPath)) ?? '';
        }

        $summary = $contentSample !== '' ? truncateUtf8($contentSample, 500) : null;
        if ($summary !== null && mb_strlen($contentSample, 'UTF-8') > 500) {
            $summary .= '...';
        }

        $metadata = null;
        $intelligenceData = json_encode([
            'extension' => $extension,
            'relative_path' => $relativePath,
            'indexed_at' => date('c'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $fileContentForInsert = null;
        if ($contentSample !== '') {
            $fileContentForInsert = truncateUtf8($contentSample, 20000);
        }

        $insert->execute([
            $projectId,
            1, // default business unit
            $serverId,
            $fullPath,
            null,
            $fileInfo->getFilename(),
            $fileType,
            $fileSize,
            $fileContentForInsert,
            $metadata,
            $intelligenceType,
            $intelligenceData,
            $summary,
        ]);

        $indexed++;
    }

    return ['indexed' => $indexed, 'skipped' => $skipped];
}

try {
    $projectIds = array_map('intval', [2, 3, 4, 5, 6, 7, 8, 9, 13]);

    $stmt = $pdo->prepare('SELECT id, project_name, project_path FROM projects WHERE id IN (' . implode(',', $projectIds) . ')');
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$projects) {
        throw new Exception('No CIS projects found in database');
    }

    echo "[INFO] Re-indexing CIS projects...\n";
    $totalIndexed = 0;

    foreach ($projects as $project) {
        $count = ingestProject($pdo, (int)$project['id'], (string)$project['project_path']);
        echo sprintf("  • %s (ID %d): %d indexed, %d skipped\n", $project['project_name'], $project['id'], $count['indexed'], $count['skipped']);
        $totalIndexed += $count['indexed'];
    }

    echo "[INFO] Indexed {$totalIndexed} files across CIS modules\n";

    echo "[INFO] Running Scanner V3 quick scan...\n";
    $scanner = new QuickScanService($pdo);
    $session = $scanner->startQuickScan($projectIds);
    $scanId = $session['scan_id'];

    do {
        $result = $scanner->scanBatch($scanId, 100);
        $progress = $result['progress'];
        echo sprintf("    Progress: %.2f%% (%d files scanned, %d violations)\n",
            $progress['progress'] ?? 0,
            $progress['files_scanned'] ?? 0,
            $progress['violations_found'] ?? 0
        );
        if (($progress['status'] ?? '') === 'completed') {
            break;
        }
    } while (($progress['files_scanned'] ?? 0) < ($progress['total_files'] ?? 0));

    try {
        $summary = $scanner->getScanSummary($scanId);
        echo "[INFO] Scan complete: \n";
        echo "      Total violations: " . ($summary['total_violations'] ?? 0) . "\n";
        echo "      Critical: " . ($summary['critical_count'] ?? 0) . ", High: " . ($summary['high_count'] ?? 0) . ", Medium: " . ($summary['medium_count'] ?? 0) . ", Low: " . ($summary['low_count'] ?? 0) . "\n";
    } catch (Throwable $summaryError) {
        echo '[WARN] Unable to retrieve scan summary: ' . $summaryError->getMessage() . "\n";
    }

    $duration = round(microtime(true) - $startTime, 2);
    echo '[' . date('Y-m-d H:i:s') . "] Intelligence Refresh Completed in {$duration}s\n";

    exit(0);

} catch (Exception $e) {
    $duration = round(microtime(true) - $startTime, 2);
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo '[' . date('Y-m-d H:i:s') . "] Intelligence Refresh Failed after {$duration}s\n";
    exit(1);
}
