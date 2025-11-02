#!/usr/bin/env php
<?php
/**
 * Log Cleanup Script
 *
 * Cleans up old log files from the Intelligence Hub
 * Runs daily at 2:00 AM via cron
 *
 * @package IntelligenceHub
 * @author AI Development Assistant
 * @created 2025-11-02
 */

declare(strict_types=1);

// Configuration
$logDirs = [
    '/home/master/applications/hdgwrzntwa/public_html/logs',
    '/home/master/applications/hdgwrzntwa/private_html/logs',
    '/home/master/applications/hdgwrzntwa/logs'
];
$retentionDays = 30; // Keep logs for 30 days
$compressOlderThan = 7; // Compress logs older than 7 days
$maxLogSize = 100 * 1024 * 1024; // 100MB - rotate logs larger than this

// Log start
echo "[" . date('Y-m-d H:i:s') . "] Log Cleanup Started\n";

try {
    $totalDeleted = 0;
    $totalCompressed = 0;
    $totalRotated = 0;
    $totalSpaceFreed = 0;

    foreach ($logDirs as $logDir) {
        if (!is_dir($logDir)) {
            echo "Skipping non-existent directory: {$logDir}\n";
            continue;
        }

        echo "\nProcessing directory: {$logDir}\n";
        echo str_repeat("-", 60) . "\n";

        // Get all log files
        $files = glob("{$logDir}/*.log*");
        $cutoffTime = time() - ($retentionDays * 86400);
        $compressTime = time() - ($compressOlderThan * 86400);

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $fileSize = filesize($file);
            $fileName = basename($file);

            // Delete old logs
            if ($fileTime < $cutoffTime) {
                echo "Deleting old log: {$fileName} (";
                echo round((time() - $fileTime) / 86400) . " days old)\n";

                $totalSpaceFreed += $fileSize;
                unlink($file);
                $totalDeleted++;
                continue;
            }

            // Compress old uncompressed logs
            if ($fileTime < $compressTime && !preg_match('/\.gz$/', $file)) {
                echo "Compressing: {$fileName}... ";

                $sizeBefore = $fileSize;
                exec("gzip -f " . escapeshellarg($file), $output, $returnCode);

                if ($returnCode === 0 && file_exists("{$file}.gz")) {
                    $sizeAfter = filesize("{$file}.gz");
                    $saved = $sizeBefore - $sizeAfter;
                    $totalSpaceFreed += $saved;
                    echo "✓ (saved " . formatBytes($saved) . ")\n";
                    $totalCompressed++;
                } else {
                    echo "✗ Failed\n";
                }
                continue;
            }

            // Rotate large logs
            if ($fileSize > $maxLogSize && !preg_match('/\.gz$/', $file)) {
                echo "Rotating large log: {$fileName} (" . formatBytes($fileSize) . ")... ";

                $timestamp = date('Y-m-d_H-i-s');
                $rotatedFile = str_replace('.log', ".log.{$timestamp}", $file);

                if (rename($file, $rotatedFile)) {
                    // Create new empty log file
                    touch($file);
                    chmod($file, 0644);

                    // Compress rotated file
                    exec("gzip -f " . escapeshellarg($rotatedFile), $output, $returnCode);

                    echo "✓ Rotated and compressed\n";
                    $totalRotated++;
                } else {
                    echo "✗ Failed to rotate\n";
                }
            }
        }
    }

    echo "\n";
    echo "===========================================\n";
    echo "Log Cleanup Summary\n";
    echo "===========================================\n";
    echo "Logs deleted: {$totalDeleted}\n";
    echo "Logs compressed: {$totalCompressed}\n";
    echo "Logs rotated: {$totalRotated}\n";
    echo "Total space freed: " . formatBytes($totalSpaceFreed) . "\n";
    echo "===========================================\n\n";

    // Record cleanup in database
    require_once dirname(__DIR__) . '/app.php';
    $db = getMysqliConnection();

    $stmt = $db->prepare("
        INSERT INTO intelligence_metrics (metric_name, metric_value, metric_data, recorded_at)
        VALUES (?, ?, ?, NOW())
    ");

    $metricName = 'log_cleanup_space_freed';
    $metricValue = $totalSpaceFreed;
    $metricData = json_encode([
        'logs_deleted' => $totalDeleted,
        'logs_compressed' => $totalCompressed,
        'logs_rotated' => $totalRotated,
        'space_freed_mb' => round($totalSpaceFreed / 1024 / 1024, 2)
    ]);

    $stmt->bind_param('sds', $metricName, $metricValue, $metricData);
    $stmt->execute();
    $stmt->close();

    echo "[" . date('Y-m-d H:i:s') . "] Log Cleanup Completed Successfully\n";

    exit(0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Log Cleanup Failed\n";
    exit(1);
}

/**
 * Format bytes into human-readable format
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}
