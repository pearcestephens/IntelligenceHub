#!/usr/bin/env php
<?php
/**
 * Duplicate Cleanup and Constraint Addition Script
 *
 * Safely removes duplicates and adds UNIQUE constraints to prevent future duplicates
 *
 * Usage: php cleanup_duplicates.php [--dry-run] [--backup-only]
 *
 * Options:
 *   --dry-run      Show what would be done without making changes
 *   --backup-only  Only create backup, don't clean duplicates
 *
 * @package IntelligenceHub\MCP\Maintenance
 */

declare(strict_types=1);

// Increase PHP execution time for large operations
set_time_limit(600); // 10 minutes
ini_set('memory_limit', '512M'); // Increase memory for large datasets

// Configuration
$config = [
    'host' => '127.0.0.1',
    'dbname' => 'hdgwrzntwa',
    'username' => 'hdgwrzntwa',
    'password' => 'bFUdRjh4Jx',
];

$dryRun = in_array('--dry-run', $argv);
$backupOnly = in_array('--backup-only', $argv);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  DUPLICATE CLEANUP & CONSTRAINT ADDITION                    ║\n";
echo "║  Intelligence Hub Database Maintenance                       ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($dryRun) {
    echo "⚠️  DRY RUN MODE - No changes will be made\n\n";
}

if ($backupOnly) {
    echo "📦 BACKUP ONLY MODE - Will create backup and exit\n\n";
}

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 300, // 5 minutes
        ]
    );

    // Increase MySQL timeout for long-running operations
    $pdo->exec("SET SESSION wait_timeout = 600"); // 10 minutes
    $pdo->exec("SET SESSION interactive_timeout = 600"); // 10 minutes

    echo "✅ Connected to database: {$config['dbname']}\n\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Step 1: Analyze current state
echo "📊 ANALYZING CURRENT STATE...\n";
echo str_repeat("-", 64) . "\n";

$stats = [];

// Total files
$stmt = $pdo->query("SELECT COUNT(*) as total FROM intelligence_files");
$stats['total_files'] = (int)($stmt->fetch()['total'] ?? 0);
echo "Total files in database: " . number_format($stats['total_files']) . "\n";

// Unique paths
$stmt = $pdo->query("SELECT COUNT(DISTINCT file_path) as unique_paths FROM intelligence_files");
$stats['unique_paths'] = (int)($stmt->fetch()['unique_paths'] ?? 0);
echo "Unique file paths: " . number_format($stats['unique_paths']) . "\n";

// Duplicates
$duplicates = $stats['total_files'] - $stats['unique_paths'];
echo "Duplicate entries: " . number_format($duplicates) . "\n";

// Case-sensitive duplicates
$stmt = $pdo->query("
    SELECT COUNT(*) as case_dups
    FROM (
        SELECT LOWER(file_path) as normalized_path, business_unit_id
        FROM intelligence_files
        GROUP BY LOWER(file_path), business_unit_id
        HAVING COUNT(*) > 1
    ) t
");
$stats['case_duplicates'] = (int)($stmt->fetch()['case_dups'] ?? 0);
echo "Case-sensitive duplicates: " . number_format($stats['case_duplicates']) . "\n";

// Show top duplicates
echo "\n📋 TOP 10 DUPLICATE FILES:\n";
$stmt = $pdo->query("
    SELECT
        file_path,
        COUNT(*) as count
    FROM intelligence_files
    GROUP BY file_path
    HAVING count > 1
    ORDER BY count DESC
    LIMIT 10
");
foreach ($stmt->fetchAll() as $row) {
    echo "  • " . basename($row['file_path']) . " (" . $row['count'] . " copies)\n";
}

if ($dryRun) {
    echo "\n⚠️  DRY RUN MODE - Exiting without changes\n";
    exit(0);
}

// Step 2: Create backup table (with lock handling)
echo "\n📦 CREATING BACKUP TABLE...\n";
echo "----------------------------------------------------------------\n";

try {
    // Increase lock wait timeout temporarily
    $pdo->exec("SET SESSION innodb_lock_wait_timeout = 300");

    // Drop backup table if exists
    echo "Checking for existing backup table...\n";
    $pdo->exec("DROP TABLE IF EXISTS intelligence_files_backup_20251102");

    // Create backup with same structure
    echo "Creating backup table structure...\n";
    $pdo->exec("CREATE TABLE intelligence_files_backup_20251102 LIKE intelligence_files");

    // Copy data in smaller chunks to avoid long locks
    echo "Copying data in chunks...\n";
    $chunkSize = 1000;
    $offset = 0;
    $totalCopied = 0;

    while (true) {
        $copied = $pdo->exec("
            INSERT INTO intelligence_files_backup_20251102
            SELECT * FROM intelligence_files
            LIMIT $chunkSize OFFSET $offset
        ");

        if ($copied === 0) {
            break;
        }

        $totalCopied += $copied;
        $offset += $chunkSize;
        echo "  Copied " . number_format($totalCopied) . " records...\r";
    }

    echo "\n✅ Backup created: " . number_format($totalCopied) . " records\n";
} catch (PDOException $e) {
    echo "❌ Backup failed: " . $e->getMessage() . "\n";
    echo "\n⚠️  TIP: The table may be locked by another process.\n";
    echo "Try these commands to check for locks:\n";
    echo "  SHOW PROCESSLIST;\n";
    echo "  SELECT * FROM information_schema.innodb_locks;\n";
    exit(1);
}
echo "\n📦 CREATING BACKUP TABLE...\n";
echo str_repeat("-", 64) . "\n";

try {
    $pdo->exec("DROP TABLE IF EXISTS intelligence_files_backup_20251102");
    $pdo->exec("CREATE TABLE intelligence_files_backup_20251102 AS SELECT * FROM intelligence_files");

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM intelligence_files_backup_20251102");
    $backupCount = (int)($stmt->fetch()['count'] ?? 0);
    echo "✅ Backup created: " . number_format($backupCount) . " rows\n";
} catch (PDOException $e) {
    die("❌ Backup failed: " . $e->getMessage() . "\n");
}

if ($backupOnly) {
    echo "\n✅ BACKUP COMPLETE - Exiting (use without --backup-only to clean duplicates)\n";
    exit(0);
}

// Step 3: Remove duplicates (keep newest)
echo "\n🧹 REMOVING DUPLICATE ENTRIES...\n";
echo str_repeat("-", 64) . "\n";

try {
    // Create temp table with files to keep
    $pdo->exec("DROP TEMPORARY TABLE IF EXISTS temp_duplicate_files");
    $pdo->exec("
        CREATE TEMPORARY TABLE temp_duplicate_files AS
        SELECT
            file_path,
            business_unit_id,
            MAX(file_id) as keep_file_id,
            COUNT(*) as duplicate_count
        FROM intelligence_files
        GROUP BY file_path, business_unit_id
        HAVING COUNT(*) > 1
    ");

    $stmt = $pdo->query("SELECT SUM(duplicate_count - 1) as total_to_delete FROM temp_duplicate_files");
    $toDelete = (int)($stmt->fetch()['total_to_delete'] ?? 0);

    if ($toDelete > 0) {
        echo "Found " . number_format($toDelete) . " duplicate entries to remove...\n";

        // Delete in batches for better performance and progress feedback
        $totalDeleted = 0;
        $batchSize = 1000;

        echo "Deleting in batches of {$batchSize}...\n";

        do {
            $deletedRows = $pdo->exec("
                DELETE f FROM intelligence_files f
                INNER JOIN temp_duplicate_files t
                    ON f.file_path = t.file_path
                    AND f.business_unit_id = t.business_unit_id
                WHERE f.file_id < t.keep_file_id
                LIMIT {$batchSize}
            ");

            $totalDeleted += $deletedRows;

            if ($deletedRows > 0) {
                $percent = round(($totalDeleted / $toDelete) * 100, 1);
                echo "  Progress: " . number_format($totalDeleted) . " / " . number_format($toDelete) . " ({$percent}%)\r";
                flush();
            }

        } while ($deletedRows > 0);

        echo "\n✅ Removed " . number_format($totalDeleted) . " duplicate entries\n";
    } else {
        echo "✅ No exact duplicates found\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Warning during duplicate removal: " . $e->getMessage() . "\n";
}

// Step 4: Handle case-sensitive duplicates
echo "\n🔍 HANDLING CASE-SENSITIVE DUPLICATES...\n";
echo str_repeat("-", 64) . "\n";

try {
    // Create log table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS scanner_duplicate_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            normalized_path VARCHAR(1000),
            business_unit_id INT,
            duplicate_count INT,
            file_ids TEXT,
            file_paths TEXT,
            logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resolved BOOLEAN DEFAULT FALSE,
            INDEX idx_normalized (normalized_path(255)),
            INDEX idx_resolved (resolved)
        )
    ");

    // Find and log case duplicates
    $pdo->exec("DROP TEMPORARY TABLE IF EXISTS temp_case_duplicates");
    $pdo->exec("
        CREATE TEMPORARY TABLE temp_case_duplicates AS
        SELECT
            LOWER(file_path) as normalized_path,
            business_unit_id,
            COUNT(*) as count,
            GROUP_CONCAT(file_id ORDER BY file_id DESC) as file_ids,
            GROUP_CONCAT(file_path ORDER BY file_id DESC SEPARATOR ' | ') as paths
        FROM intelligence_files
        GROUP BY LOWER(file_path), business_unit_id
        HAVING COUNT(*) > 1
    ");

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM temp_case_duplicates");
    $caseDupsFound = (int)($stmt->fetch()['count'] ?? 0);

    if ($caseDupsFound > 0) {
        // Insert into log
        $pdo->exec("
            INSERT INTO scanner_duplicate_log
                (normalized_path, business_unit_id, duplicate_count, file_ids, file_paths)
            SELECT normalized_path, business_unit_id, count, file_ids, paths
            FROM temp_case_duplicates
        ");

        echo "⚠️  Found " . number_format($caseDupsFound) . " case-sensitive duplicates\n";
        echo "   (e.g., OpenAIHelper.php vs openaihelper.php)\n";
        echo "   Logged to scanner_duplicate_log table for manual review\n";
    } else {
        echo "✅ No case-sensitive duplicates found\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Warning during case duplicate handling: " . $e->getMessage() . "\n";
}

// Step 5: Add file_hash column
echo "\n🔐 ADDING FILE HASH COLUMN...\n";
echo str_repeat("-", 64) . "\n";

try {
    // Check if column exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as exists_col
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '{$config['dbname']}'
        AND TABLE_NAME = 'intelligence_files'
        AND COLUMN_NAME = 'file_hash'
    ");

    if ($stmt->fetch()['exists_col'] == 0) {
        $pdo->exec("
            ALTER TABLE intelligence_files
            ADD COLUMN file_hash VARCHAR(64) DEFAULT NULL AFTER file_size,
            ADD INDEX idx_file_hash (file_hash)
        ");
        echo "✅ Added file_hash column with index\n";
    } else {
        echo "✅ file_hash column already exists\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Warning: " . $e->getMessage() . "\n";
}

// Step 6: Add composite index
echo "\n📊 ADDING COMPOSITE INDEX...\n";
echo str_repeat("-", 64) . "\n";

try {
    // Check if index exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as exists_idx
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = '{$config['dbname']}'
        AND TABLE_NAME = 'intelligence_files'
        AND INDEX_NAME = 'idx_path_unit_composite'
    ");

    if ($stmt->fetch()['exists_idx'] == 0) {
        $pdo->exec("
            ALTER TABLE intelligence_files
            ADD INDEX idx_path_unit_composite (business_unit_id, file_path(500))
        ");
        echo "✅ Added composite index for fast duplicate checks\n";
    } else {
        echo "✅ Composite index already exists\n";
    }
} catch (PDOException $e) {
    echo "⚠️  Warning: " . $e->getMessage() . "\n";
}

// Step 7: Add UNIQUE constraint
echo "\n🔒 ADDING UNIQUE CONSTRAINT...\n";
echo str_repeat("-", 64) . "\n";

try {
    // Check if constraint exists
    $stmt = $pdo->query("
        SELECT COUNT(*) as exists_constraint
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = '{$config['dbname']}'
        AND TABLE_NAME = 'intelligence_files'
        AND CONSTRAINT_NAME = 'uk_file_path_unit'
    ");

    if ($stmt->fetch()['exists_constraint'] == 0) {
        $pdo->exec("
            ALTER TABLE intelligence_files
            ADD UNIQUE KEY uk_file_path_unit (business_unit_id, file_path(500))
        ");
        echo "✅ Added UNIQUE constraint - future duplicates will be PREVENTED!\n";
    } else {
        echo "✅ UNIQUE constraint already exists\n";
    }
} catch (PDOException $e) {
    echo "❌ Error adding unique constraint: " . $e->getMessage() . "\n";
    echo "   This might be due to remaining duplicates. Check scanner_duplicate_log.\n";
}

// Step 8: Final statistics
echo "\n📊 FINAL STATISTICS\n";
echo str_repeat("-", 64) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM intelligence_files");
$finalTotal = (int)($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query("SELECT COUNT(DISTINCT file_path) as unique_paths FROM intelligence_files");
$finalUnique = (int)($stmt->fetch()['unique_paths'] ?? 0);

$removed = $stats['total_files'] - $finalTotal;
$remaining = $finalTotal - $finalUnique;

echo "Files before cleanup: " . number_format($stats['total_files']) . "\n";
echo "Files after cleanup:  " . number_format($finalTotal) . "\n";
echo "Duplicates removed:   " . number_format($removed) . "\n";
echo "Remaining duplicates: " . number_format($remaining) . "\n";

if ($remaining > 0) {
    echo "\n⚠️  " . $remaining . " case-sensitive duplicates remain.\n";
    echo "   Check scanner_duplicate_log table for details.\n";
}

echo "\n✅ CLEANUP COMPLETE!\n";
echo "\n";
echo "Next steps:\n";
echo "  1. Review case duplicates: SELECT * FROM scanner_duplicate_log WHERE resolved = FALSE;\n";
echo "  2. Test unique constraint: Try inserting a duplicate (it should fail)\n";
echo "  3. Update scanner to use insert_file_safe() procedure\n";
echo "\n";
