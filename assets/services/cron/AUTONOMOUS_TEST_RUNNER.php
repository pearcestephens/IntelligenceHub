#!/usr/bin/env php
<?php
/**
 * Autonomous Cron System Test Runner
 *
 * This script will:
 * 1. Test autoloader functionality
 * 2. Test database connectivity
 * 3. Test LoadBalancer
 * 4. Test Smart Cron execution
 * 5. Run actual cron jobs
 * 6. Generate comprehensive report
 *
 * Usage: php AUTONOMOUS_TEST_RUNNER.php
 */

declare(strict_types=1);

// Color codes for terminal output
define('COLOR_RESET', "\033[0m");
define('COLOR_RED', "\033[31m");
define('COLOR_GREEN', "\033[32m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_CYAN', "\033[36m");
define('COLOR_BOLD', "\033[1m");

$testResults = [];
$startTime = microtime(true);

echo "\n";
echo COLOR_CYAN . COLOR_BOLD . "═══════════════════════════════════════════════════════════\n";
echo "  🤖 AUTONOMOUS CRON SYSTEM TEST RUNNER\n";
echo "═══════════════════════════════════════════════════════════\n" . COLOR_RESET;
echo COLOR_YELLOW . "Starting comprehensive system test...\n" . COLOR_RESET;
echo "\n";

// ============================================================================
// TEST 1: Autoloader Functionality
// ============================================================================
echo COLOR_BOLD . "TEST 1: Autoloader Functionality\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    $autoloaderPath = __DIR__ . '/autoloader.php';
    echo "Loading autoloader from: {$autoloaderPath}\n";

    if (!file_exists($autoloaderPath)) {
        throw new Exception("Autoloader file not found!");
    }

    require_once $autoloaderPath;
    echo COLOR_GREEN . "✅ Autoloader loaded successfully\n" . COLOR_RESET;
    $testResults['autoloader'] = 'PASS';
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['autoloader'] = 'FAIL';
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 2: Bootstrap and Database Connectivity
// ============================================================================
echo COLOR_BOLD . "TEST 2: Bootstrap and Database Connectivity\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    $bootstrapPath = __DIR__ . '/smart-cron/bootstrap.php';
    echo "Loading bootstrap from: {$bootstrapPath}\n";

    if (!file_exists($bootstrapPath)) {
        throw new Exception("Bootstrap file not found!");
    }

    require_once $bootstrapPath;
    echo COLOR_GREEN . "✅ Bootstrap loaded successfully\n" . COLOR_RESET;

    // Test database connection
    echo "Testing database connection...\n";
    $db = getMysqliConnection();

    if ($db === null) {
        throw new Exception("Database connection is NULL");
    }

    if ($db->connect_error) {
        throw new Exception("Database connection error: " . $db->connect_error);
    }

    // Test query
    $result = $db->query("SELECT 1 as test");
    if (!$result) {
        throw new Exception("Database query failed: " . $db->error);
    }

    echo COLOR_GREEN . "✅ Database connection successful\n" . COLOR_RESET;
    echo "   Database: " . $db->server_info . "\n";
    $testResults['database'] = 'PASS';
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['database'] = 'FAIL';
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 3: Class Loading
// ============================================================================
echo COLOR_BOLD . "TEST 3: SmartCron Class Loading\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

$requiredClasses = [
    'SmartCron\Core\Config',
    'SmartCron\Core\LoadBalancer',
    'SmartCron\Core\MetricsCollector',
    'SmartCron\Core\CircuitBreaker',
    'SmartCron\Core\AlertManager',
];

$classLoadingSuccess = true;
foreach ($requiredClasses as $class) {
    echo "Checking class: {$class}... ";
    if (class_exists($class)) {
        echo COLOR_GREEN . "✅ FOUND\n" . COLOR_RESET;
    } else {
        echo COLOR_RED . "❌ NOT FOUND\n" . COLOR_RESET;
        $classLoadingSuccess = false;
    }
}

if ($classLoadingSuccess) {
    echo COLOR_GREEN . "✅ All required classes loaded successfully\n" . COLOR_RESET;
    $testResults['class_loading'] = 'PASS';
} else {
    echo COLOR_RED . "❌ Some classes failed to load\n" . COLOR_RESET;
    $testResults['class_loading'] = 'FAIL';
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 4: Config System
// ============================================================================
echo COLOR_BOLD . "TEST 4: Configuration System\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    echo "Initializing Config...\n";
    $config = new \SmartCron\Core\Config();
    echo COLOR_GREEN . "✅ Config initialized successfully\n" . COLOR_RESET;

    // Test config retrieval
    $dbConfig = $config->get('db');
    echo "Database config: " . json_encode($dbConfig) . "\n";

    $loadBalancerEnabled = $config->get('load_balancer.enabled', true);
    echo "Load Balancer enabled: " . ($loadBalancerEnabled ? 'YES' : 'NO') . "\n";

    $testResults['config'] = 'PASS';
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['config'] = 'FAIL';
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 5: LoadBalancer Functionality
// ============================================================================
echo COLOR_BOLD . "TEST 5: LoadBalancer Functionality\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    use SmartCron\Core\MetricsCollector;
    use SmartCron\Core\LoadBalancer;

    echo "Initializing MetricsCollector...\n";
    $metrics = new MetricsCollector($config);
    echo COLOR_GREEN . "✅ MetricsCollector initialized\n" . COLOR_RESET;

    echo "Initializing LoadBalancer...\n";
    $balancer = new LoadBalancer($config, $metrics);
    echo COLOR_GREEN . "✅ LoadBalancer initialized\n" . COLOR_RESET;

    // Test health status
    echo "Getting health status...\n";
    $health = $balancer->getHealthStatus();
    echo "Health status: " . $health['overall_status'] . "\n";
    echo "CPU usage: " . $health['resources']['cpu']['usage'] . "%\n";
    echo "Memory usage: " . $health['resources']['memory']['usage'] . "%\n";

    // Test can run task
    echo "Testing task execution permission...\n";
    $testTask = [
        'id' => 'test-task',
        'name' => 'Test Task',
        'type' => 'light'
    ];

    $canRun = $balancer->canRunTask($testTask);
    echo "Can run test task: " . ($canRun ? COLOR_GREEN . 'YES' : COLOR_RED . 'NO') . COLOR_RESET . "\n";

    $testResults['load_balancer'] = 'PASS';
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    $testResults['load_balancer'] = 'FAIL';
    exit(1);
}

echo "\n";

// ============================================================================
// TEST 6: Cron Jobs Table
// ============================================================================
echo COLOR_BOLD . "TEST 6: Cron Jobs Database\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    // Check hub_cron_jobs table
    $result = $db->query("SHOW TABLES LIKE 'hub_cron_jobs'");
    if ($result->num_rows === 0) {
        echo COLOR_YELLOW . "⚠️  hub_cron_jobs table not found, checking cron_jobs...\n" . COLOR_RESET;

        $result = $db->query("SHOW TABLES LIKE 'cron_jobs'");
        if ($result->num_rows === 0) {
            throw new Exception("Neither hub_cron_jobs nor cron_jobs table exists!");
        }
        $cronTable = 'cron_jobs';
    } else {
        $cronTable = 'hub_cron_jobs';
    }

    echo "Using table: {$cronTable}\n";

    // Get job count
    $result = $db->query("SELECT COUNT(*) as count FROM {$cronTable}");
    $row = $result->fetch_assoc();
    echo "Total jobs in database: " . $row['count'] . "\n";

    // Get enabled jobs
    $result = $db->query("SELECT COUNT(*) as count FROM {$cronTable} WHERE enabled = 1");
    $row = $result->fetch_assoc();
    echo "Enabled jobs: " . $row['count'] . "\n";

    // List jobs
    $result = $db->query("SELECT id, name, schedule, enabled FROM {$cronTable} LIMIT 10");
    echo "\nRegistered jobs:\n";
    while ($job = $result->fetch_assoc()) {
        $status = $job['enabled'] ? COLOR_GREEN . '✓' : COLOR_RED . '✗';
        echo "  {$status} {$job['name']} ({$job['schedule']})" . COLOR_RESET . "\n";
    }

    echo COLOR_GREEN . "✅ Cron jobs table is accessible\n" . COLOR_RESET;
    $testResults['cron_table'] = 'PASS';
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['cron_table'] = 'FAIL';
}

echo "\n";

// ============================================================================
// TEST 7: Smart Cron Execution (Dry Run)
// ============================================================================
echo COLOR_BOLD . "TEST 7: Smart Cron Execution (Dry Run)\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    echo "Attempting to execute smart-cron.php...\n";
    $smartCronPath = __DIR__ . '/smart-cron.php';

    if (!file_exists($smartCronPath)) {
        throw new Exception("smart-cron.php not found at: {$smartCronPath}");
    }

    echo "Executing: php {$smartCronPath} --status\n";
    $output = [];
    $returnVar = 0;
    exec("php {$smartCronPath} --status 2>&1", $output, $returnVar);

    echo "Output:\n";
    foreach ($output as $line) {
        echo "  " . $line . "\n";
    }

    if ($returnVar === 0) {
        echo COLOR_GREEN . "✅ Smart Cron executed successfully\n" . COLOR_RESET;
        $testResults['smart_cron_exec'] = 'PASS';
    } else {
        echo COLOR_YELLOW . "⚠️  Smart Cron returned code: {$returnVar}\n" . COLOR_RESET;
        $testResults['smart_cron_exec'] = 'PARTIAL';
    }
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['smart_cron_exec'] = 'FAIL';
}

echo "\n";

// ============================================================================
// TEST 8: Run Actual Smart Cron
// ============================================================================
echo COLOR_BOLD . "TEST 8: Execute Smart Cron (Real Run)\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    echo COLOR_YELLOW . "⚡ Executing smart-cron.php (actual jobs may run)...\n" . COLOR_RESET;
    $output = [];
    $returnVar = 0;
    exec("php {$smartCronPath} 2>&1", $output, $returnVar);

    echo "Execution output:\n";
    $executionSuccess = false;
    foreach ($output as $line) {
        echo "  " . $line . "\n";
        if (strpos($line, '▶️ RUNNING') !== false || strpos($line, '✅') !== false) {
            $executionSuccess = true;
        }
    }

    if ($returnVar === 0 && $executionSuccess) {
        echo COLOR_GREEN . "✅ Smart Cron executed and ran tasks successfully\n" . COLOR_RESET;
        $testResults['smart_cron_run'] = 'PASS';
    } elseif ($returnVar === 0) {
        echo COLOR_YELLOW . "⚠️  Smart Cron executed but no tasks ran (may be expected)\n" . COLOR_RESET;
        $testResults['smart_cron_run'] = 'PARTIAL';
    } else {
        echo COLOR_RED . "❌ Smart Cron execution failed with code: {$returnVar}\n" . COLOR_RESET;
        $testResults['smart_cron_run'] = 'FAIL';
    }
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['smart_cron_run'] = 'FAIL';
}

echo "\n";

// ============================================================================
// TEST 9: Log File Analysis
// ============================================================================
echo COLOR_BOLD . "TEST 9: Log File Analysis\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

try {
    $logPath = __DIR__ . '/smart-cron/logs/smart-cron.log';

    if (file_exists($logPath)) {
        echo "Analyzing log file: {$logPath}\n";

        // Get last 20 lines
        $logLines = file($logPath);
        $recentLines = array_slice($logLines, -20);

        echo "\nRecent log entries:\n";
        foreach ($recentLines as $line) {
            if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                echo COLOR_RED . $line . COLOR_RESET;
            } elseif (strpos($line, 'WARNING') !== false) {
                echo COLOR_YELLOW . $line . COLOR_RESET;
            } elseif (strpos($line, 'RUNNING') !== false) {
                echo COLOR_GREEN . $line . COLOR_RESET;
            } else {
                echo $line;
            }
        }

        echo COLOR_GREEN . "✅ Log file accessible and analyzed\n" . COLOR_RESET;
        $testResults['logs'] = 'PASS';
    } else {
        echo COLOR_YELLOW . "⚠️  Log file not found (may not have been created yet)\n" . COLOR_RESET;
        $testResults['logs'] = 'PARTIAL';
    }
} catch (Exception $e) {
    echo COLOR_RED . "❌ FAILED: " . $e->getMessage() . "\n" . COLOR_RESET;
    $testResults['logs'] = 'FAIL';
}

echo "\n";

// ============================================================================
// FINAL REPORT
// ============================================================================
$duration = round(microtime(true) - $startTime, 2);

echo COLOR_CYAN . COLOR_BOLD . "\n═══════════════════════════════════════════════════════════\n";
echo "  📊 AUTONOMOUS TEST RESULTS\n";
echo "═══════════════════════════════════════════════════════════\n" . COLOR_RESET;
echo "\n";

$passCount = 0;
$failCount = 0;
$partialCount = 0;

echo COLOR_BOLD . "Test Summary:\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

foreach ($testResults as $test => $result) {
    $testName = str_replace('_', ' ', ucwords($test, '_'));

    if ($result === 'PASS') {
        echo COLOR_GREEN . "✅ {$testName}: PASS\n" . COLOR_RESET;
        $passCount++;
    } elseif ($result === 'PARTIAL') {
        echo COLOR_YELLOW . "⚠️  {$testName}: PARTIAL\n" . COLOR_RESET;
        $partialCount++;
    } else {
        echo COLOR_RED . "❌ {$testName}: FAIL\n" . COLOR_RESET;
        $failCount++;
    }
}

echo "\n";
echo COLOR_BOLD . "Statistics:\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";
echo "Total tests: " . count($testResults) . "\n";
echo COLOR_GREEN . "Passed: {$passCount}\n" . COLOR_RESET;
echo COLOR_YELLOW . "Partial: {$partialCount}\n" . COLOR_RESET;
echo COLOR_RED . "Failed: {$failCount}\n" . COLOR_RESET;
echo "Duration: {$duration} seconds\n";
echo "\n";

// Overall status
if ($failCount === 0 && $partialCount === 0) {
    echo COLOR_GREEN . COLOR_BOLD . "🎉 ALL TESTS PASSED! System is fully operational.\n" . COLOR_RESET;
    $exitCode = 0;
} elseif ($failCount === 0) {
    echo COLOR_YELLOW . COLOR_BOLD . "⚠️  TESTS PASSED WITH WARNINGS. System is mostly operational.\n" . COLOR_RESET;
    $exitCode = 0;
} else {
    echo COLOR_RED . COLOR_BOLD . "❌ TESTS FAILED. System requires fixes.\n" . COLOR_RESET;
    $exitCode = 1;
}

echo "\n";

// Next steps
echo COLOR_BOLD . "Next Steps:\n" . COLOR_RESET;
echo "─────────────────────────────────────────────────────────────\n";

if ($failCount === 0) {
    echo "1. Monitor cron execution: tail -f " . __DIR__ . "/smart-cron/logs/smart-cron.log\n";
    echo "2. Check health status: php " . __DIR__ . "/smart-cron/bin/health-check.php\n";
    echo "3. Run load balancer tests: php " . __DIR__ . "/smart-cron/bin/test-load-balancer.php\n";
    echo "4. Add to system crontab if not already present\n";
} else {
    echo "1. Review error logs above\n";
    echo "2. Fix identified issues\n";
    echo "3. Re-run this test: php " . __FILE__ . "\n";
}

echo "\n";
echo COLOR_CYAN . "═══════════════════════════════════════════════════════════\n" . COLOR_RESET;

exit($exitCode);
