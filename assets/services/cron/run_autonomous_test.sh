#!/bin/bash
# Autonomous Cron System Configuration and Testing Script
# This script will execute the test runner and monitor results

cd /home/master/applications/hdgwrzntwa/public_html/assets/services/cron

echo "Starting autonomous cron system test..."
echo ""

# Run the autonomous test runner
php AUTONOMOUS_TEST_RUNNER.php

# Capture exit code
EXIT_CODE=$?

echo ""
echo "Test runner completed with exit code: $EXIT_CODE"

if [ $EXIT_CODE -eq 0 ]; then
    echo "✅ SUCCESS: All tests passed!"
    echo ""
    echo "Next: Monitor live execution"
    echo "  tail -f smart-cron/logs/smart-cron.log"
else
    echo "❌ FAILURE: Some tests failed"
    echo "Review output above for details"
fi

exit $EXIT_CODE
