#!/usr/bin/env php
<?php
/**
 * Smart Cron Task Seeder
 * 
 * Seeds smart_cron_tasks table from tasks.json configuration
 * 
 * Usage: php seed-tasks.php [--force]
 * 
 * @package SmartCron\Setup
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/Config.php';

use SmartCron\Core\Config;

// Parse arguments
$force = in_array('--force', $argv);

$config = new Config();
$db = $config->getDbConnection();

if (!$db) {
    echo "❌ ERROR: Database connection failed\n";
    echo "Please check database configuration in Config.php\n";
    exit(1);
}

echo "🌱 Smart Cron Task Seeder\n";
echo "========================\n\n";

// Load tasks from JSON
$tasks = $config->getTasks();
if (empty($tasks)) {
    echo "❌ ERROR: No tasks found in tasks.json\n";
    exit(1);
}

echo "📋 Found " . count($tasks) . " tasks in configuration\n\n";

// Check if tasks already exist
$result = $db->query("SELECT COUNT(*) as count FROM smart_cron_tasks");
$row = $result->fetch_assoc();
$existingCount = (int)$row['count'];

if ($existingCount > 0 && !$force) {
    echo "⚠️  WARNING: {$existingCount} tasks already exist in database\n";
    echo "Use --force to overwrite existing tasks\n";
    exit(0);
}

if ($existingCount > 0 && $force) {
    echo "🗑️  Clearing existing tasks (--force mode)...\n";
    $db->query("TRUNCATE TABLE smart_cron_tasks");
    echo "✅ Cleared {$existingCount} existing tasks\n\n";
}

// Seed tasks
$inserted = 0;
$skipped = 0;
$errors = [];

$stmt = $db->prepare("
    INSERT INTO smart_cron_tasks (
        task_name,
        script_path,
        enabled,
        frequency,
        description,
        timeout_seconds,
        max_retries,
        alert_on_failure,
        alert_threshold_failures
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        script_path = VALUES(script_path),
        enabled = VALUES(enabled),
        frequency = VALUES(frequency),
        description = VALUES(description),
        timeout_seconds = VALUES(timeout_seconds),
        max_retries = VALUES(max_retries),
        alert_on_failure = VALUES(alert_on_failure),
        alert_threshold_failures = VALUES(alert_threshold_failures)
");

foreach ($tasks as $task) {
    try {
        // Validate required fields
        if (empty($task['name']) || empty($task['script'])) {
            $skipped++;
            $errors[] = "Skipped task: missing name or script";
            continue;
        }
        
        // Extract frequency values
        $frequency = $task['frequency'] ?? 'unknown';
        
        // Set defaults
        $timeoutSeconds = 3600; // Default 1 hour
        $maxRetries = 3;
        $alertOnFailure = 1; // 1 for true in mysqli
        $alertThreshold = 3; // Alert after 3 consecutive failures
        
        // Override from task config if available
        if (isset($task['timeout_seconds'])) {
            $timeoutSeconds = (int)$task['timeout_seconds'];
        }
        if (isset($task['max_retries'])) {
            $maxRetries = (int)$task['max_retries'];
        }
        if (isset($task['alert_on_failure'])) {
            $alertOnFailure = $task['alert_on_failure'] ? 1 : 0;
        }
        if (isset($task['alert_threshold'])) {
            $alertThreshold = (int)$task['alert_threshold'];
        }
        
        // Convert values for binding
        $taskName = $task['name'];
        $scriptPath = $task['script'];
        $enabledInt = ($task['enabled'] ?? false) ? 1 : 0;
        $description = $task['description'] ?? null;
        
        // Bind parameters for mysqli
        $stmt->bind_param(
            'ssissiiii',
            $taskName,
            $scriptPath,
            $enabledInt,
            $frequency,
            $description,
            $timeoutSeconds,
            $maxRetries,
            $alertOnFailure,
            $alertThreshold
        );
        
        $stmt->execute();
        
        $inserted++;
        echo "✅ {$task['name']}\n";
        
    } catch (Exception $e) {
        $skipped++;
        $errors[] = "Error seeding {$task['name']}: " . $e->getMessage();
        echo "❌ {$task['name']}: {$e->getMessage()}\n";
    }
}

echo "\n";
echo "📊 Seeding Summary\n";
echo "==================\n";
echo "✅ Successfully seeded: {$inserted} tasks\n";
echo "❌ Skipped/Errors: {$skipped} tasks\n";

if (!empty($errors)) {
    echo "\n⚠️  Errors:\n";
    foreach ($errors as $error) {
        echo "  - {$error}\n";
    }
}

echo "\n✨ Task seeding complete!\n";
exit(0);
