#!/usr/bin/env php
<?php
/**
 * Frontend Integration Database Installer
 *
 * Installs all 9 frontend integration tables with credentials
 *
 * Usage:
 *   php install_frontend_db.php
 *
 * @package FrontendIntegration
 * @version 1.0.0
 */

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  Frontend Integration - Database Installation (PHP)       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Database credentials
$dbConfig = [
    'host' => 'localhost',
    'database' => 'hdgwrzntwa',
    'username' => 'hdgwrzntwa',
    'password' => 'bFUdRjh4Jx',
    'charset' => 'utf8mb4'
];

// Path to SQL file
$sqlFile = __DIR__ . '/frontend_integration_schema.sql';

echo "📋 Configuration:\n";
echo "   Database: {$dbConfig['database']}\n";
echo "   Host: {$dbConfig['host']}\n";
echo "   User: {$dbConfig['username']}\n";
echo "   SQL File: $sqlFile\n";
echo "\n";

// Check if SQL file exists
if (!file_exists($sqlFile)) {
    echo "❌ ERROR: SQL file not found: $sqlFile\n";
    exit(1);
}

// Confirm
echo "⚠️  This will create 9 new tables. Continue? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirm = trim(strtolower($line));
fclose($handle);

if ($confirm !== 'y' && $confirm !== 'yes') {
    echo "❌ Installation cancelled\n";
    exit(0);
}

echo "\n";
echo "🔄 Connecting to database...\n";

try {
    // Connect to database
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected successfully\n";
    echo "\n";
    echo "🔄 Reading SQL file...\n";

    // Read SQL file
    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }

    echo "✅ SQL file read (" . strlen($sql) . " bytes)\n";
    echo "\n";
    echo "🔄 Executing SQL statements...\n";

    // Split SQL into statements (handle multiple queries)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    $executed = 0;
    $errors = 0;

    foreach ($statements as $statement) {
        // Skip empty statements and comments
        if (empty($statement) || substr(trim($statement), 0, 2) === '--') {
            continue;
        }

        try {
            $pdo->exec($statement);
            $executed++;

            // Show progress for CREATE TABLE statements
            if (preg_match('/CREATE TABLE.*?`(\w+)`/i', $statement, $matches)) {
                echo "   ✅ Created table: {$matches[1]}\n";
            } elseif (preg_match('/INSERT INTO.*?`(\w+)`/i', $statement, $matches)) {
                echo "   ✅ Inserted data: {$matches[1]}\n";
            }
        } catch (PDOException $e) {
            $errors++;
            echo "   ⚠️  Error in statement: " . substr($statement, 0, 50) . "...\n";
            echo "      Message: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";

    if ($errors === 0) {
        echo "✅ SUCCESS! Frontend integration database schema installed\n";
        echo "\n";
        echo "📊 Results:\n";
        echo "   SQL statements executed: $executed\n";
        echo "   Errors: $errors\n";
        echo "\n";
        echo "📊 Tables created:\n";
        echo "   1. frontend_pending_fixes\n";
        echo "   2. frontend_workflows\n";
        echo "   3. frontend_workflow_executions\n";
        echo "   4. frontend_audit_history\n";
        echo "   5. frontend_monitors\n";
        echo "   6. frontend_monitor_alerts\n";
        echo "   7. frontend_screenshot_gallery\n";
        echo "   8. frontend_visual_regression\n";
        echo "   9. frontend_deployment_log\n";
        echo "\n";
        echo "🎯 Sample workflows inserted:\n";
        echo "   - Quick Page Audit\n";
        echo "   - Auto-Fix Pipeline\n";
        echo "   - 24/7 Monitoring\n";
        echo "\n";
        echo "🚀 Next steps:\n";
        echo "   1. Visit: https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/approvals.php\n";
        echo "   2. Visit: https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/workflows.php\n";
        echo "   3. Run documentation indexer:\n";
        echo "      php /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/scripts/index_documentation.php\n";
        echo "\n";

        // Verify tables exist
        echo "🔍 Verifying installation...\n";
        $tables = [
            'frontend_pending_fixes',
            'frontend_workflows',
            'frontend_workflow_executions',
            'frontend_audit_history',
            'frontend_monitors',
            'frontend_monitor_alerts',
            'frontend_screenshot_gallery',
            'frontend_visual_regression',
            'frontend_deployment_log'
        ];

        $allExist = true;
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            $exists = $stmt->rowCount() > 0;
            echo "   " . ($exists ? "✅" : "❌") . " $table\n";
            if (!$exists) {
                $allExist = false;
            }
        }

        echo "\n";

        if ($allExist) {
            echo "✅ All tables verified successfully!\n";
            exit(0);
        } else {
            echo "⚠️  Some tables missing - check errors above\n";
            exit(1);
        }
    } else {
        echo "⚠️  Installation completed with $errors errors\n";
        echo "   Check messages above for details\n";
        exit(1);
    }

} catch (PDOException $e) {
    echo "\n";
    echo "❌ ERROR: Database connection failed\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Troubleshooting:\n";
    echo "   1. Check database credentials in this script\n";
    echo "   2. Ensure MySQL is running\n";
    echo "   3. Verify database '{$dbConfig['database']}' exists\n";
    echo "   4. Check user has sufficient permissions\n";
    echo "\n";
    exit(1);
} catch (Exception $e) {
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n";
    exit(1);
}
