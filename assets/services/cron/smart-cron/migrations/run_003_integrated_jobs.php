#!/usr/bin/env php
<?php
/**
 * Smart Cron - Integrated Jobs Migration Runner
 * Run: php run_003_integrated_jobs.php
 */

declare(strict_types=1);

/**
 * Get standalone database connection for migration
 */
function getDbConnection(): ?\mysqli
{
    // Database credentials from parent directory's smart-cron.php
    $host = '127.0.0.1';
    $username = 'jcepnzzkmj';
    $password = 'wprKh9Jq63';
    $database = 'jcepnzzkmj';
    
    $connection = new \mysqli($host, $username, $password, $database);
    
    if ($connection->connect_error) {
        error_log("Database connection failed: " . $connection->connect_error);
        return null;
    }
    
    // Set charset to UTF-8
    $connection->set_charset('utf8mb4');
    
    return $connection;
}

$migrationFile = __DIR__ . '/003_create_integrated_cron_jobs.sql';

if (!file_exists($migrationFile)) {
    die("❌ Migration file not found: {$migrationFile}\n");
}

$sql = file_get_contents($migrationFile);

// Get database connection
$db = getDbConnection();

if (!$db) {
    die("❌ Failed to connect to database\n");
}

echo "🚀 Running migration: 003_create_integrated_cron_jobs.sql\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Parse SQL more carefully - handle multi-line statements
$lines = explode("\n", $sql);
$currentStatement = '';
$statements = [];
$inComment = false;

foreach ($lines as $line) {
    $line = trim($line);
    
    // Skip empty lines
    if (empty($line)) {
        continue;
    }
    
    // Skip single-line comments
    if (strpos($line, '--') === 0) {
        continue;
    }
    
    // Add line to current statement
    $currentStatement .= $line . "\n";
    
    // Check if statement is complete (ends with semicolon)
    if (substr(rtrim($line), -1) === ';') {
        // Skip view definitions for now
        if (stripos($currentStatement, 'CREATE OR REPLACE VIEW') === false &&
            stripos($currentStatement, 'CREATE VIEW') === false) {
            $statements[] = trim($currentStatement);
        }
        $currentStatement = '';
    }
}

$success = 0;
$failed = 0;

echo "📦 Creating tables and inserting data...\n\n";

foreach ($statements as $i => $statement) {
    if (empty(trim($statement))) {
        continue;
    }
    
    // Extract first line for preview
    $firstLine = strtok($statement, "\n");
    $preview = substr($firstLine, 0, 70);
    echo sprintf("[%d/%d] %s...\n", $i + 1, count($statements), $preview);
    
    if ($db->query($statement)) {
        echo "  ✅ Success\n\n";
        $success++;
    } else {
        echo "  ❌ Failed: " . $db->error . "\n\n";
        $failed++;
        // Continue with other statements even if one fails
    }
}

// Now create views (after tables exist)
echo "\n📊 Creating views...\n";

// Extract view definitions from SQL
$viewPattern = '/(CREATE\s+(?:OR\s+REPLACE\s+)?VIEW\s+\w+\s+AS\s+.+?);/is';
if (preg_match_all($viewPattern, $sql, $matches)) {
    foreach ($matches[1] as $viewSql) {
        if (preg_match('/VIEW\s+(\w+)\s+AS/', $viewSql, $nameMatch)) {
            $viewName = $nameMatch[1];
            echo "Creating view: {$viewName}... ";
            
            if ($db->query($viewSql . ';')) {
                echo "✅\n";
                $success++;
            } else {
                echo "⚠️ " . $db->error . "\n";
                // Don't fail migration if views fail
            }
        }
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Migration completed!\n";
echo "   Success: {$success}\n";
echo "   Failed: {$failed}\n\n";

// Verify tables
echo "📋 Verifying tables...\n";
$result = $db->query("
    SELECT 
        TABLE_NAME, 
        TABLE_ROWS, 
        ROUND(DATA_LENGTH / 1024 / 1024, 2) AS size_mb
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME LIKE 'smart_cron%'
    ORDER BY TABLE_NAME
");

if ($result) {
    echo "\n";
    printf("%-45s %10s %10s\n", "TABLE NAME", "ROWS", "SIZE (MB)");
    echo str_repeat("─", 70) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        printf("%-45s %10d %10s\n", 
            $row['TABLE_NAME'], 
            $row['TABLE_ROWS'], 
            $row['size_mb']
        );
    }
    echo "\n";
}

$db->close();

echo "🎉 Ready for integrated cron job management!\n";
echo "\nNext steps:\n";
echo "  1. Run auto-discovery: php bin/discover-cron-jobs.php\n";
echo "  2. View dashboard: https://staff.vapeshed.co.nz/assets/services/cron/smart-cron/dashboard.php\n\n";
