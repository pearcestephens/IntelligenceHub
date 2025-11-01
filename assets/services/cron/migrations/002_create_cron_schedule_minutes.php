<?php
/**
 * Migration: Create cron_schedule_minutes table
 * 
 * Stores the optimized minute-by-minute schedule for all tasks
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

echo "Creating cron_schedule_minutes table...\n";

$sql = "CREATE TABLE IF NOT EXISTS cron_schedule_minutes (
    minute INT NOT NULL,
    task_name VARCHAR(255) NOT NULL,
    priority INT DEFAULT 50,
    estimated_duration DECIMAL(10,3) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (minute, task_name),
    INDEX idx_minute (minute),
    INDEX idx_task_name (task_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Minute-by-minute task schedule'";

if ($db->query($sql)) {
    echo "✓ cron_schedule_minutes table created successfully\n";
} else {
    echo "✗ ERROR: " . $db->error . "\n";
    exit(1);
}

// Check if table has data
$result = $db->query("SELECT COUNT(*) as count FROM cron_schedule_minutes");
$row = $result->fetch_assoc();
echo "  Current records: {$row['count']}\n";

$db->close();
echo "\nMigration completed successfully!\n";
