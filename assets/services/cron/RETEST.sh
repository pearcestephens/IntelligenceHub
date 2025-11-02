#!/bin/bash
# Quick re-test script after fixes

echo "════════════════════════════════════════════════════════════"
echo "  🔧 RE-TESTING AFTER CRITICAL FIXES"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Fixes applied:"
echo "  ✅ AUTONOMOUS_TEST_RUNNER.php - Removed illegal 'use' statement"
echo "  ✅ MetricsCollector.php - Added \$taskName variable definition"
echo ""
echo "Running master autonomous executor..."
echo ""

cd /home/master/applications/hdgwrzntwa/public_html/assets/services/cron

php MASTER_AUTONOMOUS_EXECUTOR.php

EXIT_CODE=$?

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  RE-TEST COMPLETE"
echo "════════════════════════════════════════════════════════════"
echo ""
echo "Exit Code: $EXIT_CODE"
echo ""

if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ SUCCESS: All tests passed!"
    echo ""
    echo "Next steps:"
    echo "  1. Monitor live execution:"
    echo "     tail -f smart-cron/logs/smart-cron.log"
    echo ""
    echo "  2. Check cron jobs:"
    echo "     mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SELECT * FROM hub_cron_jobs LIMIT 10;'"
    echo ""
    echo "  3. Add to crontab:"
    echo "     crontab -e"
    echo "     # Add: * * * * * php $(pwd)/smart-cron.php >> $(pwd)/smart-cron/logs/cron-output.log 2>&1"
else
    echo "❌ FAILURE: Some tests failed (exit code: $EXIT_CODE)"
    echo ""
    echo "Review output above for details."
    echo "Check FIXES_APPLIED.md for troubleshooting."
fi

echo ""
