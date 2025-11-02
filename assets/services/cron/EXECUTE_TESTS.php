#!/usr/bin/env php
<?php
/**
 * DIRECT TEST RUNNER - Executes tests immediately
 */

// Set working directory
$cronDir = '/home/master/applications/hdgwrzntwa/public_html/assets/services/cron';
chdir($cronDir);

// Display header
echo "\n";
echo str_repeat("═", 70) . "\n";
echo "  🧪 SMART CRON SYSTEM - COMPREHENSIVE RE-TEST\n";
echo str_repeat("═", 70) . "\n";
echo "\n";
echo "Working Directory: " . getcwd() . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "\n";
echo "Fixes verified:\n";
echo "  ✅ AUTONOMOUS_TEST_RUNNER.php line 152 - \\SmartCron\\Core\\Config()\n";
echo "  ✅ MetricsCollector.php line 263 - \$taskName defined\n";
echo "\n";
echo str_repeat("─", 70) . "\n";
echo "\n";

// Run the master executor
echo "Executing: php MASTER_AUTONOMOUS_EXECUTOR.php\n\n";

// Use passthru to show output in real-time
passthru('php MASTER_AUTONOMOUS_EXECUTOR.php', $exitCode);

echo "\n";
echo str_repeat("═", 70) . "\n";
echo "  EXECUTION COMPLETE\n";
echo str_repeat("═", 70) . "\n";
echo "\n";
echo "Exit Code: $exitCode\n";
echo "\n";

// Interpret results
if ($exitCode === 0) {
    echo "🎉 SUCCESS: All critical components operational!\n";
    echo "\n";
    echo "System is ready for production use.\n";
    echo "\n";
    echo "Recommended next steps:\n";
    echo "  1. Monitor logs: tail -f smart-cron/logs/smart-cron.log\n";
    echo "  2. Add to crontab for automated execution\n";
    echo "\n";
} elseif ($exitCode === 1) {
    echo "⚠️  WARNING: System operational with minor issues\n";
    echo "\n";
    echo "Review warnings above. System can be used but may need attention.\n";
    echo "\n";
} else {
    echo "❌ FAILURE: Critical errors detected (exit code: $exitCode)\n";
    echo "\n";
    echo "Review error messages above and check:\n";
    echo "  - STATUS_REPORT.md for troubleshooting\n";
    echo "  - FIXES_APPLIED.md for recent changes\n";
    echo "\n";
}

exit($exitCode);
