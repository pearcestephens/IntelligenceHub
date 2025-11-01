#!/bin/bash
# ==================================================
# AUTOMATED EXECUTION - NO USER INPUT REQUIRED
# Creates test tables and runs tests
# ==================================================

set -e

# Change to the ai-agent directory (where this script lives)
cd "$(dirname "$0")"

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║   AUTOMATED TEST SETUP & EXECUTION                 ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""

# Step 1: Create test tables
echo "📦 Step 1: Creating test tables..."
php bin/auto-setup.php

if [ $? -ne 0 ]; then
    echo "❌ Failed to create test tables"
    exit 1
fi

# Step 1.5: Fix schema (add version column, FK constraints)
echo ""
echo "🔧 Step 1.5: Applying schema fixes..."
php bin/fix-test-schema.php

if [ $? -ne 0 ]; then
    echo "❌ Failed to apply schema fixes"
    exit 1
fi

# Step 2: Run tests
echo "🧪 Step 2: Running database integration tests..."
echo ""
php vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php --testdox

TEST_RESULT=$?

echo ""
if [ $TEST_RESULT -eq 0 ]; then
    echo "╔════════════════════════════════════════════════════╗"
    echo "║            ✅ ALL TESTS PASSED! ✅                  ║"
    echo "╚════════════════════════════════════════════════════╝"
else
    echo "╔════════════════════════════════════════════════════╗"
    echo "║       ⚠️  TESTS RAN (some may have failed)        ║"
    echo "╚════════════════════════════════════════════════════╝"
fi

echo ""
echo "📊 Summary:"
echo "  • Test tables: test_conversations, test_messages, etc."
echo "  • Production tables: Untouched"
echo "  • Test mode: Enabled via USE_TEST_TABLES=true"
echo ""

exit $TEST_RESULT
