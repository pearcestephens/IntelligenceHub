#!/usr/bin/env php
<?php
/**
 * Fast Duplicate Cleanup - No Backup, Direct Delete in Small Batches
 *
 * This deletes duplicates in batches of 100 without creating temp tables.
 * Much faster than the original approach.
 *
 * Usage: php fast_cleanup.php
 */

declare(strict_types=1);

// Increase limits
ini_set('memory_limit', '512M');
set_time_limit(0);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  FAST DUPLICATE CLEANUP (NO BACKUP)                        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Database connection
try {
        $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
        'hdgwrzntwa',
        'bFUdRjh4Jx',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✓ Connected to database\n\n";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage() . "\n");
}

// Get initial stats
echo "📊 INITIAL STATISTICS\n";
echo "----------------------------------------------------------------\n";

$totalFiles = (int) $pdo->query("SELECT COUNT(*) FROM intelligence_files")->fetchColumn();
$uniquePaths = (int) $pdo->query("SELECT COUNT(DISTINCT file_path) FROM intelligence_files")->fetchColumn();
$duplicates = $totalFiles - $uniquePaths;

echo "Total files in database: " . number_format($totalFiles) . "\n";
echo "Unique file paths: " . number_format($uniquePaths) . "\n";
echo "Duplicate entries: " . number_format($duplicates) . " (" . round(($duplicates / $totalFiles) * 100, 1) . "%)\n";
echo "\n";

if ($duplicates === 0) {
    echo "✓ No duplicates found! Database is clean.\n\n";
    exit(0);
}

// Strategy: Find duplicates and delete older ones in small batches
echo "🧹 REMOVING DUPLICATES (in batches of 100)...\n";
echo "----------------------------------------------------------------\n";

$batchSize = 100;
$totalDeleted = 0;
$batchNum = 0;

while (true) {
    // Find next batch of duplicate file_ids to delete (keep the newest one per path)
    $sql = "
        SELECT f1.file_id
        FROM intelligence_files f1
        INNER JOIN (
            SELECT file_path, MAX(file_id) as max_id
            FROM intelligence_files
            GROUP BY file_path
            HAVING COUNT(*) > 1
        ) f2 ON f1.file_path = f2.file_path AND f1.file_id < f2.max_id
        LIMIT ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $duplicateIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($duplicateIds)) {
        break; // No more duplicates
    }

    // Delete this batch
    $placeholders = implode(',', array_fill(0, count($duplicateIds), '?'));
    $deleteSql = "DELETE FROM intelligence_files WHERE file_id IN ($placeholders)";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute($duplicateIds);

    $deleted = $deleteStmt->rowCount();
    $totalDeleted += $deleted;
    $batchNum++;

    echo "  Batch #" . $batchNum . ": Deleted " . $deleted . " duplicates (Total: " . number_format($totalDeleted) . ")\n";

    // Small delay to avoid overloading the server
    usleep(100000); // 0.1 second
}

echo "\n";
echo "✓ Duplicate removal complete!\n";
echo "  Total duplicates deleted: " . number_format($totalDeleted) . "\n\n";

// Get final stats
echo "📊 FINAL STATISTICS\n";
echo "----------------------------------------------------------------\n";

$finalTotal = (int) $pdo->query("SELECT COUNT(*) FROM intelligence_files")->fetchColumn();
$finalUnique = (int) $pdo->query("SELECT COUNT(DISTINCT file_path) FROM intelligence_files")->fetchColumn();
$remainingDuplicates = $finalTotal - $finalUnique;

echo "Total files in database: " . number_format($finalTotal) . "\n";
echo "Unique file paths: " . number_format($finalUnique) . "\n";
echo "Remaining duplicates: " . number_format($remainingDuplicates) . "\n\n";

if ($remainingDuplicates > 0) {
    echo "⚠️  Warning: " . number_format($remainingDuplicates) . " duplicates still remain.\n";
    echo "   Run this script again to clean them up.\n\n";
} else {
    echo "✅ SUCCESS! Database is now 100% clean - no duplicates!\n\n";
}

// Now add UNIQUE constraint to prevent future duplicates
echo "🔒 ADDING UNIQUE CONSTRAINT...\n";
echo "----------------------------------------------------------------\n";

try {
    // Check if constraint already exists
    $constraintCheck = $pdo->query("
        SELECT COUNT(*)
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = 'hdgwrzntwa'
        AND TABLE_NAME = 'intelligence_files'
        AND CONSTRAINT_NAME = 'uk_file_path_unit'
    ")->fetchColumn();

    if ($constraintCheck > 0) {
        echo "✓ UNIQUE constraint already exists\n\n";
    } else {
        // Add composite index first (required for UNIQUE constraint)
        $pdo->exec("
            ALTER TABLE intelligence_files
            ADD INDEX idx_path_unit_composite (business_unit_id, file_path(500))
        ");
        echo "✓ Added composite index\n";

        // Add UNIQUE constraint
        $pdo->exec("
            ALTER TABLE intelligence_files
            ADD CONSTRAINT uk_file_path_unit
            UNIQUE (business_unit_id, file_path(500))
        ");
        echo "✓ Added UNIQUE constraint (duplicates now impossible!)\n\n";
    }
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "⚠️  Cannot add UNIQUE constraint - duplicates still exist!\n";
        echo "   Run this script again to remove remaining duplicates.\n\n";
    } else {
        echo "❌ Error adding constraint: " . $e->getMessage() . "\n\n";
    }
}

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  CLEANUP COMPLETE!                                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";
