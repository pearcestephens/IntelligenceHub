#!/usr/bin/env php
<?php
/**
 * System Diagnostic and Auto-Fix Script
 * Identifies issues and attempts automatic repairs
 */

declare(strict_types=1);

echo "\n";
echo "🔧 CRON SYSTEM DIAGNOSTIC & AUTO-FIX\n";
echo "═══════════════════════════════════════════════════════════\n\n";

$issues = [];
$fixes = [];

// Check 1: Autoloader exists
echo "[1/10] Checking autoloader...\n";
$autoloaderPath = __DIR__ . '/autoloader.php';
if (file_exists($autoloaderPath)) {
    echo "  ✅ Autoloader found\n";
    require_once $autoloaderPath;
} else {
    echo "  ❌ Autoloader missing at: {$autoloaderPath}\n";
    $issues[] = "Autoloader file missing";
}

// Check 2: Bootstrap exists
echo "[2/10] Checking bootstrap...\n";
$bootstrapPath = __DIR__ . '/smart-cron/bootstrap.php';
if (file_exists($bootstrapPath)) {
    echo "  ✅ Bootstrap found\n";
} else {
    echo "  ❌ Bootstrap missing at: {$bootstrapPath}\n";
    $issues[] = "Bootstrap file missing";
}

// Check 3: Core directory structure
echo "[3/10] Checking directory structure...\n";
$requiredDirs = [
    'smart-cron',
    'smart-cron/core',
    'smart-cron/bin',
    'smart-cron/config',
    'smart-cron/logs',
];

foreach ($requiredDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        echo "  ✅ {$dir}\n";
    } else {
        echo "  ❌ {$dir} missing\n";
        $issues[] = "Missing directory: {$dir}";

        // Auto-fix: Create directory
        if (mkdir($fullPath, 0755, true)) {
            echo "     🔧 AUTO-FIX: Created {$dir}\n";
            $fixes[] = "Created missing directory: {$dir}";
        }
    }
}

// Check 4: Required class files
echo "[4/10] Checking core class files...\n";
$requiredFiles = [
    'smart-cron/core/Config.php',
    'smart-cron/core/LoadBalancer.php',
    'smart-cron/core/MetricsCollector.php',
    'smart-cron/core/CircuitBreaker.php',
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "  ✅ {$file}\n";
    } else {
        echo "  ❌ {$file} missing\n";
        $issues[] = "Missing file: {$file}";
    }
}

// Check 5: Config file
echo "[5/10] Checking configuration...\n";
$configPath = __DIR__ . '/smart-cron/config/config.json';
if (file_exists($configPath)) {
    echo "  ✅ Config file found\n";

    $configContent = file_get_contents($configPath);
    $config = json_decode($configContent, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "  ✅ Config is valid JSON\n";

        // Check database credentials
        if (isset($config['db']['database'])) {
            echo "  ✅ Database: " . $config['db']['database'] . "\n";
        } else {
            echo "  ❌ Database configuration missing\n";
            $issues[] = "Database configuration incomplete";
        }
    } else {
        echo "  ❌ Config JSON is invalid: " . json_last_error_msg() . "\n";
        $issues[] = "Invalid JSON in config file";
    }
} else {
    echo "  ❌ Config file missing\n";
    $issues[] = "Config file missing";
}

// Check 6: Test if bootstrap loads
echo "[6/10] Testing bootstrap loading...\n";
try {
    require_once $bootstrapPath;
    echo "  ✅ Bootstrap loads successfully\n";
} catch (Exception $e) {
    echo "  ❌ Bootstrap failed: " . $e->getMessage() . "\n";
    $issues[] = "Bootstrap loading error: " . $e->getMessage();
}

// Check 7: Test class loading
echo "[7/10] Testing class autoloading...\n";
try {
    if (class_exists('SmartCron\\Core\\Config')) {
        echo "  ✅ Config class loads\n";
    } else {
        echo "  ❌ Config class not found\n";
        $issues[] = "Config class cannot be loaded";
    }
} catch (Exception $e) {
    echo "  ❌ Class loading error: " . $e->getMessage() . "\n";
    $issues[] = "Class loading error: " . $e->getMessage();
}

// Check 8: Database connection
echo "[8/10] Testing database connection...\n";
try {
    if (function_exists('getMysqliConnection')) {
        $db = getMysqliConnection();
        if ($db && !$db->connect_error) {
            echo "  ✅ Database connection successful\n";
            echo "     Server: " . $db->server_info . "\n";
        } else {
            echo "  ❌ Database connection failed\n";
            $issues[] = "Database connection error";
        }
    } else {
        echo "  ⚠️  getMysqliConnection() function not available\n";
        $issues[] = "Database connection function missing";
    }
} catch (Exception $e) {
    echo "  ❌ Database error: " . $e->getMessage() . "\n";
    $issues[] = "Database error: " . $e->getMessage();
}

// Check 9: Log directory permissions
echo "[9/10] Checking log directory permissions...\n";
$logDir = __DIR__ . '/smart-cron/logs';
if (is_dir($logDir)) {
    if (is_writable($logDir)) {
        echo "  ✅ Log directory is writable\n";
    } else {
        echo "  ❌ Log directory is not writable\n";
        $issues[] = "Log directory not writable";

        // Auto-fix: Set permissions
        if (chmod($logDir, 0755)) {
            echo "     🔧 AUTO-FIX: Set log directory permissions to 0755\n";
            $fixes[] = "Fixed log directory permissions";
        }
    }
} else {
    echo "  ❌ Log directory does not exist\n";
    $issues[] = "Log directory missing";
}

// Check 10: Smart cron executable
echo "[10/10] Checking smart-cron.php...\n";
$smartCronPath = __DIR__ . '/smart-cron.php';
if (file_exists($smartCronPath)) {
    echo "  ✅ smart-cron.php found\n";

    if (is_executable($smartCronPath)) {
        echo "  ✅ File is executable\n";
    } else {
        echo "  ⚠️  File is not executable (this is OK for PHP)\n";
    }
} else {
    echo "  ❌ smart-cron.php missing\n";
    $issues[] = "smart-cron.php file missing";
}

// Summary
echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "DIAGNOSTIC SUMMARY\n";
echo "═══════════════════════════════════════════════════════════\n\n";

if (count($issues) === 0) {
    echo "✅ NO ISSUES FOUND - System appears healthy!\n\n";
    echo "Ready to run tests:\n";
    echo "  php test_autoloader_quick.php\n";
    echo "  php AUTONOMOUS_TEST_RUNNER.php\n";
    exit(0);
} else {
    echo "❌ FOUND " . count($issues) . " ISSUE(S):\n\n";
    foreach ($issues as $i => $issue) {
        echo "  " . ($i + 1) . ". " . $issue . "\n";
    }

    if (count($fixes) > 0) {
        echo "\n🔧 APPLIED " . count($fixes) . " AUTO-FIX(ES):\n\n";
        foreach ($fixes as $i => $fix) {
            echo "  " . ($i + 1) . ". " . $fix . "\n";
        }
        echo "\n⚠️  Please re-run this diagnostic to verify fixes.\n";
    }

    echo "\n";
    exit(1);
}
