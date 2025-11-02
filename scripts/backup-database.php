#!/usr/bin/env php
<?php
/**
 * Database Backup Script
 *
 * Creates daily backups of the Intelligence Hub database
 * Runs daily at 3:00 AM via cron
 *
 * @package IntelligenceHub
 * @author AI Development Assistant
 * @created 2025-11-02
 */

declare(strict_types=1);

// Configuration
$backupDir = '/home/master/applications/hdgwrzntwa/private_html/backups/database';
$dbHost = '127.0.0.1';
$dbName = 'hdgwrzntwa';
$dbUser = 'hdgwrzntwa';
$dbPass = 'bFUdRjh4Jx';
$retentionDays = 30;

// Log start
echo "[" . date('Y-m-d H:i:s') . "] Database Backup Started\n";

try {
    // Create backup directory if it doesn't exist
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        echo "Created backup directory: {$backupDir}\n";
    }

    // Generate backup filename
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = "{$backupDir}/hdgwrzntwa_backup_{$timestamp}.sql";
    $gzipFile = "{$backupFile}.gz";

    echo "Backing up database to: {$backupFile}\n";

    // Execute mysqldump
    $command = sprintf(
        "mysqldump -h %s -u %s -p'%s' %s > %s 2>&1",
        escapeshellarg($dbHost),
        escapeshellarg($dbUser),
        $dbPass, // Don't escapeshellarg password, it breaks it
        escapeshellarg($dbName),
        escapeshellarg($backupFile)
    );

    exec($command, $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception("mysqldump failed with code {$returnCode}: " . implode("\n", $output));
    }

    // Check if backup file was created and has content
    if (!file_exists($backupFile) || filesize($backupFile) < 1000) {
        throw new Exception("Backup file is missing or too small");
    }

    $fileSize = filesize($backupFile);
    echo "Backup created: " . number_format($fileSize) . " bytes\n";

    // Compress backup
    echo "Compressing backup...\n";
    exec("gzip -f {$backupFile}", $output, $returnCode);

    if ($returnCode !== 0) {
        echo "Warning: Compression failed, keeping uncompressed backup\n";
        $finalFile = $backupFile;
    } else {
        $finalFile = $gzipFile;
        $compressedSize = filesize($gzipFile);
        $compressionRatio = round((1 - ($compressedSize / $fileSize)) * 100, 1);
        echo "Compressed: " . number_format($compressedSize) . " bytes ({$compressionRatio}% reduction)\n";
    }

    // Clean up old backups (keep last 30 days)
    echo "Cleaning up old backups (retention: {$retentionDays} days)...\n";
    $files = glob("{$backupDir}/hdgwrzntwa_backup_*.sql*");
    $deletedCount = 0;
    $cutoffTime = time() - ($retentionDays * 86400);

    foreach ($files as $file) {
        if (filemtime($file) < $cutoffTime) {
            unlink($file);
            $deletedCount++;
            echo "Deleted old backup: " . basename($file) . "\n";
        }
    }

    if ($deletedCount === 0) {
        echo "No old backups to delete\n";
    } else {
        echo "Deleted {$deletedCount} old backup(s)\n";
    }

    // Record backup in database
    require_once dirname(__DIR__) . '/app.php';
    $db = getMysqliConnection();

    $stmt = $db->prepare("
        INSERT INTO intelligence_metrics (metric_name, metric_value, metric_data, recorded_at)
        VALUES (?, ?, ?, NOW())
    ");

    $metricName = 'database_backup_size';
    $metricValue = file_exists($gzipFile) ? filesize($gzipFile) : filesize($backupFile);
    $metricData = json_encode([
        'file' => basename($finalFile),
        'original_size' => $fileSize,
        'compressed' => file_exists($gzipFile),
        'timestamp' => $timestamp
    ]);

    $stmt->bind_param('sds', $metricName, $metricValue, $metricData);
    $stmt->execute();
    $stmt->close();

    echo "[" . date('Y-m-d H:i:s') . "] Database Backup Completed Successfully\n";
    echo "Backup file: " . basename($finalFile) . "\n";

    exit(0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Database Backup Failed\n";
    exit(1);
}
