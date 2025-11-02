#!/usr/bin/env php
<?php
/**
 * MASTER AUTONOMOUS CONFIGURATION AND TEST EXECUTION
 *
 * This script orchestrates the entire testing process:
 * 1. Runs diagnostics
 * 2. Applies fixes
 * 3. Tests autoloader
 * 4. Runs comprehensive tests
 * 5. Executes actual cron
 * 6. Generates final report
 */

declare(strict_types=1);

define('STEP', 0);

function step(string $title): void {
    static $stepNum = 0;
    $stepNum++;
    echo "\n";
    echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
    echo "\033[1;36mSTEP {$stepNum}: {$title}\033[0m\n";
    echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
    echo "\n";
}

function execute(string $command, string $description, bool $stopOnFailure = false): bool {
    echo "\033[1m{$description}\033[0m\n";
    echo "Command: \033[33m{$command}\033[0m\n";
    echo str_repeat("─", 63) . "\n";

    $output = [];
    $returnVar = 0;
    exec($command . " 2>&1", $output, $returnVar);

    foreach ($output as $line) {
        echo $line . "\n";
    }

    echo str_repeat("─", 63) . "\n";

    if ($returnVar === 0) {
        echo "\033[32m✅ SUCCESS\033[0m\n";
        return true;
    } else {
        echo "\033[31m❌ FAILED (exit code: {$returnVar})\033[0m\n";

        if ($stopOnFailure) {
            echo "\n\033[1;31m🛑 Critical failure - stopping execution\033[0m\n";
            exit($returnVar);
        }
        return false;
    }
}

echo "\n";
echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
echo "\033[1;36m  🤖 MASTER AUTONOMOUS CRON SYSTEM CONFIGURATOR\033[0m\n";
echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
echo "\n";
echo "This script will autonomously:\n";
echo "  • Diagnose system issues\n";
echo "  • Apply automatic fixes\n";
echo "  • Test all components\n";
echo "  • Execute cron jobs\n";
echo "  • Generate comprehensive report\n";
echo "\n";
echo "Starting in 2 seconds...\n";
sleep(2);

$startTime = microtime(true);
$baseDir = __DIR__;

// ============================================================================
step("SYSTEM DIAGNOSTIC");
// ============================================================================
execute(
    "php {$baseDir}/diagnostic.php",
    "Running system diagnostic and auto-fixes...",
    true
);

// ============================================================================
step("AUTOLOADER VERIFICATION");
// ============================================================================
execute(
    "php {$baseDir}/test_autoloader_quick.php",
    "Testing autoloader functionality...",
    true
);

// ============================================================================
step("COMPREHENSIVE SYSTEM TESTS");
// ============================================================================
execute(
    "php {$baseDir}/AUTONOMOUS_TEST_RUNNER.php",
    "Running full system test suite...",
    false
);

// ============================================================================
step("LOAD BALANCER STRESS TEST");
// ============================================================================
execute(
    "php {$baseDir}/smart-cron/bin/test-load-balancer.php",
    "Running load balancer test suite...",
    false
);

// ============================================================================
step("HEALTH CHECK");
// ============================================================================
execute(
    "php {$baseDir}/smart-cron/bin/health-check.php",
    "Checking system health status...",
    false
);

// ============================================================================
step("MANUAL CRON EXECUTION");
// ============================================================================
execute(
    "php {$baseDir}/smart-cron.php",
    "Executing smart-cron (actual job run)...",
    false
);

// ============================================================================
// FINAL REPORT
// ============================================================================
$duration = round(microtime(true) - $startTime, 2);

echo "\n";
echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
echo "\033[1;36m  📊 MASTER TEST EXECUTION COMPLETE\033[0m\n";
echo "\033[1;36m" . str_repeat("═", 63) . "\033[0m\n";
echo "\n";

echo "\033[1mExecution Statistics:\033[0m\n";
echo "  Total Duration: {$duration} seconds\n";
echo "  Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "\n";

echo "\033[1mNext Steps:\033[0m\n";
echo "  1. Review all output above for any failures\n";
echo "  2. Monitor live cron execution:\n";
echo "     \033[33mtail -f {$baseDir}/smart-cron/logs/smart-cron.log\033[0m\n";
echo "  3. Check system health periodically:\n";
echo "     \033[33mphp {$baseDir}/smart-cron/bin/health-check.php\033[0m\n";
echo "  4. View cron jobs in database:\n";
echo "     \033[33mmysql -e 'SELECT * FROM hub_cron_jobs LIMIT 10;'\033[0m\n";
echo "\n";

echo "\033[1;32m🎉 Autonomous configuration and testing complete!\033[0m\n";
echo "\n";

exit(0);
