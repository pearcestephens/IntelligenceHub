#!/usr/bin/env php
<?php
/**
 * Quick Test Executor
 * Runs the master autonomous executor and displays results
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  🔄 RE-RUNNING MASTER AUTONOMOUS EXECUTOR\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Fixes applied since last run:\n";
echo "  ✅ AUTONOMOUS_TEST_RUNNER.php - Fixed illegal 'use' statement\n";
echo "  ✅ MetricsCollector.php - Added \$taskName variable\n";
echo "\n";
echo "Starting tests...\n";
echo "\n";

// Change to correct directory
chdir(__DIR__);

// Execute the master autonomous executor
$output = [];
$exitCode = 0;
exec('php MASTER_AUTONOMOUS_EXECUTOR.php 2>&1', $output, $exitCode);

// Display all output
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST EXECUTION COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Exit Code: " . $exitCode . "\n";
echo "\n";

if ($exitCode === 0) {
    echo "✅ SUCCESS: All critical tests passed!\n";
    echo "\n";
    echo "Next steps:\n";
    echo "  1. Monitor live execution:\n";
    echo "     tail -f smart-cron/logs/smart-cron.log\n";
    echo "\n";
    echo "  2. Check system health:\n";
    echo "     php smart-cron/bin/health-check.php\n";
    echo "\n";
    echo "  3. View cron jobs:\n";
    echo "     mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SELECT * FROM hub_cron_jobs LIMIT 10;'\n";
    echo "\n";
} else {
    echo "⚠️  Some tests failed or returned warnings (exit code: $exitCode)\n";
    echo "\n";
    echo "Review output above for details.\n";
    echo "Check STATUS_REPORT.md for troubleshooting guidance.\n";
    echo "\n";
}

exit($exitCode);
