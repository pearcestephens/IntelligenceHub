#!/usr/bin/env php
<?php
/**
 * Migration Runner - Run all database migrations
 * 
 * Usage:
 *   php run_migrations.php           # Run all migrations
 *   php run_migrations.php --reset   # Drop and recreate all tables (DANGER!)
 * 
 * @package SmartCron
 */

declare(strict_types=1);

// Load bootstrap for database connection
require_once dirname(__DIR__) . '/smart-cron/bootstrap.php';

$migrationsDir = __DIR__;
$reset = in_array('--reset', $argv);

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║      Smart Cron - Database Migration Runner (Hub)          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($reset) {
    echo "⚠️  WARNING: RESET MODE - Will drop all tables!\n";
    echo "Press Ctrl+C within 5 seconds to cancel...\n";
    sleep(5);
    echo "\n";
}

// Find all migration files
$migrations = glob($migrationsDir . '/*.php');
sort($migrations); // Ensure they run in order

if (empty($migrations)) {
    echo "No migrations found in {$migrationsDir}\n";
    exit(0);
}

echo "Found " . count($migrations) . " migration(s)\n";
echo str_repeat("-", 60) . "\n\n";

$success = 0;
$failed = 0;

foreach ($migrations as $migration) {
    $filename = basename($migration);
    
    // Skip this runner script
    if ($filename === 'run_migrations.php') {
        continue;
    }
    
    echo "Running: {$filename}\n";
    
    // Execute migration as separate process to isolate any issues
    $output = [];
    $returnCode = 0;
    exec("php " . escapeshellarg($migration) . " 2>&1", $output, $returnCode);
    
    // Display output
    foreach ($output as $line) {
        echo "  {$line}\n";
    }
    
    if ($returnCode === 0) {
        $success++;
        echo "  ✓ Success\n";
    } else {
        $failed++;
        echo "  ✗ Failed with code {$returnCode}\n";
    }
    
    echo "\n";
}

echo str_repeat("-", 60) . "\n";
echo "Migration Summary:\n";
echo "  ✓ Successful: {$success}\n";
echo "  ✗ Failed: {$failed}\n";
echo "  Total: " . ($success + $failed) . "\n";
echo "\n";

if ($failed > 0) {
    echo "⚠️  Some migrations failed. Check the output above.\n";
    exit(1);
} else {
    echo "✓ All migrations completed successfully!\n";
    exit(0);
}
