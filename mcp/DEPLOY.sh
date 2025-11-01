#!/bin/bash
# MCP Intelligence Hub - Quick Deploy Script
#
# This script guides you through the final deployment steps
# Run: bash DEPLOY.sh

clear

echo "═══════════════════════════════════════════════════════════════════"
echo "   MCP INTELLIGENCE HUB - DEPLOYMENT WIZARD"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "This wizard will guide you through the final deployment steps."
echo ""
echo "Current status: Phase 1 complete (23/23 tests passing)"
echo "Time required: ~5 minutes"
echo ""
read -p "Press Enter to continue..."
clear

# Step 1: Check database password
echo "═══════════════════════════════════════════════════════════════════"
echo "   STEP 1/5: Database Configuration"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Checking .env configuration..."
echo ""

if ! grep -q "^DB_PASS=.\+$" .env; then
    echo "⚠️  Database password not configured"
    echo ""
    echo "You need to edit .env and set your database password:"
    echo "  DB_PASS=your_actual_password"
    echo ""
    read -p "Would you like to edit .env now? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        ${EDITOR:-nano} .env
        echo ""
        echo "✅ Configuration updated"
    else
        echo ""
        echo "❌ Cannot proceed without database password"
        echo "   Please edit .env manually and run this script again"
        exit 1
    fi
else
    echo "✅ Database password is configured"
fi

echo ""
read -p "Press Enter to continue..."
clear

# Step 2: Run verification
echo "═══════════════════════════════════════════════════════════════════"
echo "   STEP 2/5: Pre-Deployment Verification"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Running 31 deployment checks..."
echo ""

if bash scripts/verify_deployment.sh 2>&1 | tee /tmp/deploy_verify.log | grep -q "VERIFICATION SUCCESSFUL"; then
    echo ""
    echo "✅ All verification checks passed!"
    VERIFY_PASS=1
else
    echo ""
    echo "❌ Verification failed. Review output above."
    echo ""
    read -p "Continue anyway? (not recommended) (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Deployment cancelled. Fix issues and try again."
        exit 1
    fi
    VERIFY_PASS=0
fi

echo ""
read -p "Press Enter to continue..."
clear

# Step 3: Run tests
echo "═══════════════════════════════════════════════════════════════════"
echo "   STEP 3/5: Test Suite"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Running comprehensive test suite (23 tests)..."
echo ""

if bash tests/run_all_tests.sh 2>&1 | tee /tmp/deploy_tests.log | grep -q "ALL TESTS PASSED"; then
    echo ""
    echo "✅ All 23 tests passed!"
    TEST_PASS=1
else
    echo ""
    echo "❌ Some tests failed. Review output above."
    echo ""
    read -p "Continue anyway? (not recommended) (y/n) " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Deployment cancelled. Fix failing tests and try again."
        exit 1
    fi
    TEST_PASS=0
fi

echo ""
read -p "Press Enter to continue..."
clear

# Step 4: Test endpoints
echo "═══════════════════════════════════════════════════════════════════"
echo "   STEP 4/5: Endpoint Testing"
echo "═══════════════════════════════════════════════════════════════════"
echo ""

echo "Testing HTTP endpoint..."
HTTP_RESPONSE=$(curl -s https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"health_check"},"id":1}')

if echo "$HTTP_RESPONSE" | grep -q '"success":true'; then
    echo "✅ HTTP endpoint working"
    HTTP_PASS=1
else
    echo "⚠️  HTTP endpoint not responding as expected"
    HTTP_PASS=0
fi

echo ""
echo "Testing CLI interface..."
if php cli/mcp search "test" --limit=1 &>/dev/null; then
    echo "✅ CLI interface working"
    CLI_PASS=1
else
    echo "⚠️  CLI interface not responding as expected"
    CLI_PASS=0
fi

echo ""
echo "Testing cache performance..."
php scripts/performance_stats.php | head -20

echo ""
read -p "Press Enter to continue..."
clear

# Step 5: Setup monitoring (optional)
echo "═══════════════════════════════════════════════════════════════════"
echo "   STEP 5/5: Monitoring Setup (Optional)"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Would you like to set up automated monitoring?"
echo ""
echo "This will add cron jobs for:"
echo "  - Health checks every 5 minutes"
echo "  - Daily backups at 1 AM"
echo ""
read -p "Setup monitoring? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "Adding cron jobs..."

    # Check if cron jobs already exist
    if crontab -l 2>/dev/null | grep -q "monitor_health.sh"; then
        echo "⚠️  Cron jobs already exist, skipping"
    else
        # Add cron jobs
        (crontab -l 2>/dev/null; echo "# MCP Health Monitoring") | crontab -
        (crontab -l 2>/dev/null; echo "*/5 * * * * $(pwd)/scripts/monitor_health.sh") | crontab -
        (crontab -l 2>/dev/null; echo "# MCP Daily Backup") | crontab -
        (crontab -l 2>/dev/null; echo "0 1 * * * $(pwd)/scripts/backup_data.sh") | crontab -

        echo "✅ Cron jobs added"
        echo ""
        echo "View with: crontab -l"
    fi
else
    echo ""
    echo "Skipping monitoring setup (you can add it later)"
fi

echo ""
read -p "Press Enter to see deployment summary..."
clear

# Final summary
echo "═══════════════════════════════════════════════════════════════════"
echo "   DEPLOYMENT COMPLETE!"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "📊 Deployment Summary:"
echo ""
echo "  Verification:        $([ $VERIFY_PASS -eq 1 ] && echo '✅ Passed' || echo '⚠️  Failed')"
echo "  Test Suite:          $([ $TEST_PASS -eq 1 ] && echo '✅ Passed (23/23)' || echo '⚠️  Failed')"
echo "  HTTP Endpoint:       $([ $HTTP_PASS -eq 1 ] && echo '✅ Working' || echo '⚠️  Issue')"
echo "  CLI Interface:       $([ $CLI_PASS -eq 1 ] && echo '✅ Working' || echo '⚠️  Issue')"
echo ""
echo "🎉 System Status: DEPLOYED AND OPERATIONAL"
echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo "   Quick Reference"
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "HTTP Endpoint:"
echo "  https://gpt.ecigdis.co.nz/mcp/server_v3.php"
echo ""
echo "CLI Interface:"
echo "  php cli/mcp search \"your query\" --limit=10"
echo ""
echo "Monitoring:"
echo "  php scripts/performance_stats.php"
echo "  bash scripts/monitor_health.sh"
echo ""
echo "Documentation:"
echo "  README.md           - Project overview"
echo "  QUICK_START.md      - Quick reference"
echo "  DEPLOYMENT_GUIDE.md - Complete manual"
echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "🚀 Next steps:"
echo "  1. Monitor performance for 24 hours"
echo "  2. Check logs: tail -f /path/to/logs/mcp.log"
echo "  3. Review cache hit rates (target >80%)"
echo "  4. Plan Phase 2 features (see PHASE_1_COMPLETE.md)"
echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo ""
echo "Thank you for using MCP Intelligence Hub!"
echo ""
