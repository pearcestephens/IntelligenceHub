#!/usr/bin/env php
<?php
/**
 * BATCH-6 Pre-Flight Checklist
 * Verifies test environment is ready before running tests
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package BATCH-6
 */

declare(strict_types=1);

// Color output helpers
function green(string $text): string { return "\033[32m{$text}\033[0m"; }
function red(string $text): string { return "\033[31m{$text}\033[0m"; }
function yellow(string $text): string { return "\033[33m{$text}\033[0m"; }
function bold(string $text): string { return "\033[1m{$text}\033[0m"; }

echo bold("=== BATCH-6 Pre-Flight Checklist ===\n\n");

$checks = [];
$warnings = [];
$errors = [];

// 1. Check PHP version
echo "Checking PHP version... ";
$phpVersion = PHP_VERSION;
$minVersion = '8.0.0';
if (version_compare($phpVersion, $minVersion, '>=')) {
    echo green("✓") . " PHP {$phpVersion}\n";
    $checks['php_version'] = true;
} else {
    echo red("✗") . " PHP {$phpVersion} (need >= {$minVersion})\n";
    $errors[] = "PHP version too old. Please upgrade to PHP 8.0+";
    $checks['php_version'] = false;
}

// 2. Check required PHP extensions
echo "Checking PHP extensions... ";
$requiredExtensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}
if (empty($missingExtensions)) {
    echo green("✓") . " All required extensions loaded\n";
    $checks['php_extensions'] = true;
} else {
    echo red("✗") . " Missing: " . implode(', ', $missingExtensions) . "\n";
    $errors[] = "Install missing extensions: " . implode(', ', $missingExtensions);
    $checks['php_extensions'] = false;
}

// 3. Check Composer dependencies
echo "Checking Composer dependencies... ";
if (file_exists('vendor/autoload.php')) {
    echo green("✓") . " vendor/autoload.php exists\n";
    $checks['composer'] = true;
} else {
    echo red("✗") . " vendor/autoload.php not found\n";
    $errors[] = "Run: composer install";
    $checks['composer'] = false;
}

// 4. Check PHPUnit
echo "Checking PHPUnit... ";
if (file_exists('vendor/bin/phpunit')) {
    exec('php vendor/bin/phpunit --version 2>&1', $output, $returnCode);
    if ($returnCode === 0) {
        $version = trim($output[0] ?? 'unknown');
        echo green("✓") . " {$version}\n";
        $checks['phpunit'] = true;
    } else {
        echo yellow("⚠") . " PHPUnit found but not working\n";
        $warnings[] = "PHPUnit may need reinstallation";
        $checks['phpunit'] = false;
    }
} else {
    echo red("✗") . " PHPUnit not found\n";
    $errors[] = "Install PHPUnit: composer require --dev phpunit/phpunit";
    $checks['phpunit'] = false;
}

// 5. Check test configuration
echo "Checking test configuration... ";
if (file_exists('phpunit.xml')) {
    echo green("✓") . " phpunit.xml exists\n";
    $checks['phpunit_config'] = true;
} else {
    echo red("✗") . " phpunit.xml not found\n";
    $errors[] = "Create phpunit.xml configuration file";
    $checks['phpunit_config'] = false;
}

// 6. Check test environment file
echo "Checking test environment... ";
if (file_exists('.env.test')) {
    echo green("✓") . " .env.test exists\n";
    $checks['env_test'] = true;
} else {
    echo yellow("⚠") . " .env.test not found (will use .env)\n";
    $warnings[] = "Create .env.test for isolated test environment";
    $checks['env_test'] = false;
}

// 7. Check test directories
echo "Checking test directories... ";
$testDirs = ['tests/Integration', 'tests/Feature', 'tests/Performance'];
$missingDirs = [];
foreach ($testDirs as $dir) {
    if (!is_dir($dir)) {
        $missingDirs[] = $dir;
    }
}
if (empty($missingDirs)) {
    echo green("✓") . " All test directories exist\n";
    $checks['test_dirs'] = true;
} else {
    echo red("✗") . " Missing: " . implode(', ', $missingDirs) . "\n";
    $errors[] = "Create missing directories";
    $checks['test_dirs'] = false;
}

// 8. Check test files
echo "Checking test files... ";
$testFiles = [
    'tests/Integration/ConversationApiIntegrationTest.php',
    'tests/Integration/DatabaseIntegrationTest.php',
    'tests/Integration/ToolExecutionIntegrationTest.php',
    'tests/Integration/MemoryIntegrationTest.php',
    'tests/Feature/E2EConversationFlowTest.php',
    'tests/Performance/PerformanceTest.php'
];
$missingFiles = [];
foreach ($testFiles as $file) {
    if (!file_exists($file)) {
        $missingFiles[] = basename($file);
    }
}
if (empty($missingFiles)) {
    echo green("✓") . " All 6 test files exist\n";
    $checks['test_files'] = true;
} else {
    echo red("✗") . " Missing: " . implode(', ', $missingFiles) . "\n";
    $errors[] = "Some test files are missing from BATCH-6";
    $checks['test_files'] = false;
}

// 9. Check database connection (optional)
echo "Checking database connection... ";
if (file_exists('.env') || file_exists('.env.test')) {
    try {
        // Try to load environment
        $envFile = file_exists('.env.test') ? '.env.test' : '.env';
        $env = parse_ini_file($envFile);
        
        if (isset($env['DB_HOST'], $env['DB_NAME'], $env['DB_USER'])) {
            try {
                $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']}";
                $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'] ?? '');
                echo green("✓") . " Connected to {$env['DB_NAME']}\n";
                $checks['database'] = true;
            } catch (PDOException $e) {
                echo yellow("⚠") . " Cannot connect to database\n";
                $warnings[] = "Database connection failed: " . $e->getMessage();
                $checks['database'] = false;
            }
        } else {
            echo yellow("⚠") . " Database config incomplete\n";
            $warnings[] = "Set DB_HOST, DB_NAME, DB_USER in .env file";
            $checks['database'] = false;
        }
    } catch (Exception $e) {
        echo yellow("⚠") . " Cannot parse .env file\n";
        $warnings[] = "Check .env file format";
        $checks['database'] = false;
    }
} else {
    echo yellow("⚠") . " No .env file found\n";
    $warnings[] = "Create .env file with database credentials";
    $checks['database'] = false;
}

// 10. Check Redis connection (optional)
echo "Checking Redis connection... ";
if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        $connected = @$redis->connect('127.0.0.1', 6379, 1);
        if ($connected) {
            echo green("✓") . " Connected to Redis\n";
            $checks['redis'] = true;
        } else {
            echo yellow("⚠") . " Cannot connect to Redis\n";
            $warnings[] = "Redis may not be running on localhost:6379";
            $checks['redis'] = false;
        }
    } catch (Exception $e) {
        echo yellow("⚠") . " Redis connection error\n";
        $warnings[] = "Start Redis server";
        $checks['redis'] = false;
    }
} else {
    echo yellow("⚠") . " Redis extension not loaded\n";
    $warnings[] = "Install php-redis extension (optional but recommended)";
    $checks['redis'] = false;
}

// Summary
echo "\n" . bold("=== Summary ===\n");

$passed = count(array_filter($checks));
$total = count($checks);
$percentage = round(($passed / $total) * 100);

echo "Checks passed: {$passed}/{$total} ({$percentage}%)\n\n";

if (!empty($errors)) {
    echo red(bold("Errors:\n"));
    foreach ($errors as $error) {
        echo red("  ✗ {$error}\n");
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo yellow(bold("Warnings:\n"));
    foreach ($warnings as $warning) {
        echo yellow("  ⚠ {$warning}\n");
    }
    echo "\n";
}

// Final verdict
if (empty($errors)) {
    echo green(bold("✓ READY TO RUN TESTS\n\n"));
    echo "Next steps:\n";
    echo "1. Run gate validation: php bin/batch-6-gate-validation.php\n";
    echo "2. Run all tests: php bin/run-all-tests.php\n";
    echo "3. Or run specific suite: php bin/run-all-tests.php --suite=integration\n";
    exit(0);
} else {
    echo red(bold("✗ NOT READY - Fix errors above first\n\n"));
    echo "After fixing errors, run this again:\n";
    echo "  php bin/preflight-check.php\n";
    exit(1);
}
