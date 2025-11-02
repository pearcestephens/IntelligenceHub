#!/usr/bin/env php
<?php
/**
 * Database Optimization Script
 *
 * Optimizes Intelligence Hub database tables to improve performance
 * Runs weekly on Sundays at 4:00 AM via cron
 *
 * @package IntelligenceHub
 * @author AI Development Assistant
 * @created 2025-11-02
 */

declare(strict_types=1);

// Configuration
$dbHost = '127.0.0.1';
$dbName = 'hdgwrzntwa';
$dbUser = 'hdgwrzntwa';
$dbPass = 'bFUdRjh4Jx';

// Log start
echo "[" . date('Y-m-d H:i:s') . "] Database Optimization Started\n";

try {
    // Connect to database
    require_once dirname(__DIR__) . '/app.php';
    $db = getMysqliConnection();

    // Get all tables
    $result = $db->query("SHOW TABLES");
    $tables = [];
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    $result->free();

    echo "Found " . count($tables) . " tables to optimize\n\n";

    $optimizedCount = 0;
    $skippedCount = 0;
    $totalSpaceSaved = 0;
    $startTime = microtime(true);

    foreach ($tables as $table) {
        echo "Processing: {$table}... ";

        // Get table size before optimization
        $sizeResult = $db->query("
            SELECT (DATA_LENGTH + INDEX_LENGTH) as size_bytes
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$table}'
        ");
        $sizeBefore = $sizeResult->fetch_assoc()['size_bytes'] ?? 0;
        $sizeResult->free();

        // Optimize table
        $optimizeResult = $db->query("OPTIMIZE TABLE `{$table}`");

        if ($optimizeResult) {
            $row = $optimizeResult->fetch_assoc();
            $msgType = $row['Msg_type'] ?? '';
            $msgText = $row['Msg_text'] ?? '';

            // Get table size after optimization
            $sizeResult = $db->query("
                SELECT (DATA_LENGTH + INDEX_LENGTH) as size_bytes
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$table}'
            ");
            $sizeAfter = $sizeResult->fetch_assoc()['size_bytes'] ?? 0;
            $sizeResult->free();

            $spaceSaved = $sizeBefore - $sizeAfter;
            $totalSpaceSaved += $spaceSaved;

            if ($msgType === 'status' && strpos($msgText, 'OK') !== false) {
                $optimizedCount++;
                if ($spaceSaved > 0) {
                    echo "✓ Optimized (saved " . formatBytes($spaceSaved) . ")\n";
                } else {
                    echo "✓ OK (no space saved)\n";
                }
            } elseif ($msgType === 'note' && strpos($msgText, 'already up to date') !== false) {
                $skippedCount++;
                echo "- Already optimized\n";
            } else {
                echo "? {$msgType}: {$msgText}\n";
            }

            $optimizeResult->free();
        } else {
            echo "✗ Failed: " . $db->error . "\n";
        }
    }

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n";
    echo "===========================================\n";
    echo "Database Optimization Summary\n";
    echo "===========================================\n";
    echo "Tables processed: " . count($tables) . "\n";
    echo "Tables optimized: {$optimizedCount}\n";
    echo "Tables already optimal: {$skippedCount}\n";
    echo "Total space saved: " . formatBytes($totalSpaceSaved) . "\n";
    echo "Duration: {$duration} seconds\n";
    echo "===========================================\n\n";

    // Record optimization in database
    $stmt = $db->prepare("
        INSERT INTO intelligence_metrics (metric_name, metric_value, metric_data, recorded_at)
        VALUES (?, ?, ?, NOW())
    ");

    $metricName = 'database_optimization_space_saved';
    $metricValue = $totalSpaceSaved;
    $metricData = json_encode([
        'tables_processed' => count($tables),
        'tables_optimized' => $optimizedCount,
        'tables_skipped' => $skippedCount,
        'duration' => $duration,
        'space_saved_mb' => round($totalSpaceSaved / 1024 / 1024, 2)
    ]);

    $stmt->bind_param('sds', $metricName, $metricValue, $metricData);
    $stmt->execute();
    $stmt->close();

    echo "[" . date('Y-m-d H:i:s') . "] Database Optimization Completed Successfully\n";

    exit(0);

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Database Optimization Failed\n";
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
