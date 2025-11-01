#!/usr/bin/env php
<?php
/**
 * Migration: Add output_json to cron_metrics
 * Date: 2025-10-22
 */

require_once __DIR__ . '/../../bootstrap.php';

$db = $conn ?? null;
if (!$db) {
    echo "❌ No database connection available\n";
    exit(1);
}

echo "🔧 Running migration: Add output_json to cron_metrics...\n\n";

// Check if column already exists
$result = $db->query("SHOW COLUMNS FROM cron_metrics LIKE 'output_json'");
if ($result->num_rows > 0) {
    echo "✅ Column output_json already exists\n";
} else {
    // Add the column
    $sql = "ALTER TABLE cron_metrics 
            ADD COLUMN output_json TEXT NULL COMMENT 'Full execution output as JSON array'
            AFTER error_message";
    
    if ($db->query($sql)) {
        echo "✅ Added column: output_json\n";
    } else {
        echo "❌ Error: " . $db->error . "\n";
        exit(1);
    }
}

// Add indexes
echo "\n🔧 Adding indexes...\n";

// Check if index exists
$result = $db->query("SHOW INDEX FROM cron_metrics WHERE Key_name = 'idx_task_name_timestamp'");
if ($result->num_rows > 0) {
    echo "✅ Index idx_task_name_timestamp already exists\n";
} else {
    $sql = "CREATE INDEX idx_task_name_timestamp ON cron_metrics(task_name, timestamp DESC)";
    if ($db->query($sql)) {
        echo "✅ Created index: idx_task_name_timestamp\n";
    } else {
        echo "⚠️  Warning: " . $db->error . "\n";
    }
}

$result = $db->query("SHOW INDEX FROM cron_metrics WHERE Key_name = 'idx_success_timestamp'");
if ($result->num_rows > 0) {
    echo "✅ Index idx_success_timestamp already exists\n";
} else {
    $sql = "CREATE INDEX idx_success_timestamp ON cron_metrics(success, timestamp DESC)";
    if ($db->query($sql)) {
        echo "✅ Created index: idx_success_timestamp\n";
    } else {
        echo "⚠️  Warning: " . $db->error . "\n";
    }
}

echo "\n✅ Migration complete!\n";
echo "\nNow you can view full execution history with output in the dashboard!\n";
