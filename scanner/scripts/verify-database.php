<?php
/**
 * Quick Database Check Script
 * Verifies what tables exist and what's missing
 */

// Direct database connection
$pdo = new PDO(
    'mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4',
    'hdgwrzntwa',
    'bFUdRjh4Jx',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

echo "========================================\n";
echo "DATABASE VERIFICATION REPORT\n";
echo "========================================\n\n";

try {
    // Get all tables in database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "✅ Database connected: hdgwrzntwa\n";
    echo "📊 Total tables found: " . count($tables) . "\n\n";

    // Required tables for Scanner
    $requiredTables = [
        'projects',
        'intelligence_files',
        'project_rule_violations',
        'code_dependencies',
        'circular_dependencies',
        'business_units',
        'project_unit_mapping',
        'scan_history',
        'scan_config',
        'rules',
        'project_metadata'
    ];

    echo "REQUIRED TABLES STATUS:\n";
    echo "----------------------------------------\n";

    $missing = [];
    $existing = [];

    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ $table\n";
            $existing[] = $table;
        } else {
            echo "❌ $table (MISSING)\n";
            $missing[] = $table;
        }
    }

    echo "\n";
    echo "Summary:\n";
    echo "  ✅ Existing: " . count($existing) . "\n";
    echo "  ❌ Missing: " . count($missing) . "\n\n";

    // Check for data in key tables
    if (in_array('projects', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
        $count = $stmt->fetchColumn();
        echo "📁 Projects: $count records\n";
    }

    if (in_array('intelligence_files', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM intelligence_files");
        $count = $stmt->fetchColumn();
        echo "📄 Files: $count records\n";
    }

    if (in_array('project_rule_violations', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM project_rule_violations");
        $count = $stmt->fetchColumn();
        echo "⚠️ Violations: $count records\n";
    }

    echo "\n";

    if (count($missing) > 0) {
        echo "⚠️ WARNING: Scanner needs these tables to run!\n";
        echo "Run create-missing-tables.php to fix.\n";
    } else {
        echo "✅ All required tables exist!\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
