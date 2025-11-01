<?php
/**
 * Migration: Create cron_metrics table
 * 
 * Captures execution time, CPU usage, and memory usage for all cron tasks.
 * 
 * @version 1.0.0
 * @created 2025-10-21
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/smart-cron/bootstrap.php';

// Connect to database
$db = getMysqliConnection();

if (!$db) {
    die("ERROR: Could not connect to database\n");
}

echo "Creating cron_metrics table...\n";

$sql = "CREATE TABLE IF NOT EXISTS cron_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    duration_seconds DECIMAL(10,3) NOT NULL,
    memory_peak_mb DECIMAL(10,2) NOT NULL,
    cpu_peak_percent DECIMAL(5,2) DEFAULT NULL,
    exit_code INT NOT NULL,
    success TINYINT(1) NOT NULL,
    error_message TEXT DEFAULT NULL,
    INDEX idx_task_executed (task_name, executed_at),
    INDEX idx_executed_at (executed_at),
    INDEX idx_success (success)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cron task execution metrics'";

if ($db->query($sql)) {
    echo "✓ cron_metrics table created successfully\n";
} else {
    echo "✗ ERROR: " . $db->error . "\n";
    exit(1);
}

// Check if table has data
$result = $db->query("SELECT COUNT(*) as count FROM cron_metrics");
$row = $result->fetch_assoc();
echo "  Current records: {$row['count']}\n";

$db->close();
echo "\nMigration completed successfully!\n";
