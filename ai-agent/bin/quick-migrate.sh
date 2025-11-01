#!/bin/bash
# =====================================================
# Quick Migration Executor
# Direct execution without prompts
# =====================================================

cd "$(dirname "$0")"

mysql -h 127.0.0.1 -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj < ../sql/migrations/2025_10_07_batch_7_comprehensive.sql

if [ $? -eq 0 ]; then
    echo "✓ Migration executed successfully"
    echo ""
    echo "Verify with:"
    echo "  php vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php"
else
    echo "✗ Migration failed - check error output above"
    exit 1
fi
