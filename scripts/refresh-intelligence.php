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

// Set time limit for long-running scans
set_time_limit(3600); // 1 hour max
ini_set('memory_limit', '1G');

// Bootstrap
require_once dirname(__DIR__) . '/app.php';

// Log start
$startTime = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Intelligence Refresh Started\n";

try {
    // Trigger Scanner V3.0 batch scan API
    $scannerAPI = dirname(__DIR__) . '/scanner/api/batch-scan.php';

    if (!file_exists($scannerAPI)) {
        throw new Exception("Scanner API not found: {$scannerAPI}");
    }

    echo "Triggering Scanner V3.0 batch scan...\n";

    // Execute scanner
    $output = [];
    $returnCode = 0;
    exec("php {$scannerAPI} --all --refresh 2>&1", $output, $returnCode);

    foreach ($output as $line) {
        echo $line . "\n";
    }

    if ($returnCode !== 0) {
        throw new Exception("Scanner failed with code: {$returnCode}");
    }

    // Refresh MCP search index
    echo "Refreshing MCP search index...\n";
    $mcpRefresh = dirname(__DIR__) . '/mcp/scripts/refresh_index.php';

    if (file_exists($mcpRefresh)) {
        exec("php {$mcpRefresh} 2>&1", $output, $returnCode);
        foreach ($output as $line) {
            echo $line . "\n";
        }
    } else {
        echo "Warning: MCP refresh script not found, skipping MCP refresh\n";
    }

    // Update intelligence metrics
    $db = getMysqliConnection();
    $stmt = $db->prepare("INSERT INTO intelligence_metrics (metric_name, metric_value, recorded_at) VALUES (?, ?, NOW())");

    $duration = round(microtime(true) - $startTime, 2);
    $stmt->bind_param('sd', $metricName, $duration);
    $metricName = 'intelligence_refresh_duration';
    $stmt->execute();

    // Get file count
    $result = $db->query("SELECT COUNT(*) as count FROM intelligence_content");
    $row = $result->fetch_assoc();
    $fileCount = (float)$row['count'];

    $stmt->bind_param('sd', $metricName, $fileCount);
    $metricName = 'intelligence_files_indexed';
    $stmt->execute();

    $stmt->close();

    echo "[" . date('Y-m-d H:i:s') . "] Intelligence Refresh Completed in {$duration}s\n";
    echo "Files indexed: " . number_format($fileCount) . "\n";

    exit(0);

} catch (Exception $e) {
    $duration = round(microtime(true) - $startTime, 2);
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] Intelligence Refresh Failed after {$duration}s\n";
    exit(1);
}
