#!/bin/bash
# Pre-Deployment Verification Script
# Run this before deploying to production

cd "$(dirname "$0")/.."

echo "==================================================================="
echo "  MCP INTELLIGENCE HUB - PRE-DEPLOYMENT VERIFICATION"
echo "==================================================================="
echo ""

PASS=0
FAIL=0
WARN=0

# Function to check and report
check() {
    local name="$1"
    local command="$2"

    if eval "$command" &>/dev/null; then
        echo "✅ $name"
        ((PASS++))
        return 0
    else
        echo "❌ $name"
        ((FAIL++))
        return 1
    fi
}

check_warn() {
    local name="$1"
    local command="$2"

    if eval "$command" &>/dev/null; then
        echo "✅ $name"
        ((PASS++))
        return 0
    else
        echo "⚠️  $name (non-critical)"
        ((WARN++))
        return 1
    fi
}

echo "1. DIRECTORY STRUCTURE"
echo "-------------------------------------------------------------------"
check "Root directory exists" "test -d ."
check "Vendor directory exists" "test -d vendor"
check "Config directory exists" "test -d config"
check "Cache directory exists" "test -d cache"
check "Tests directory exists" "test -d tests"
check "Scripts directory exists" "test -d scripts"
echo ""

echo "2. REQUIRED FILES"
echo "-------------------------------------------------------------------"
check ".env file exists" "test -f .env"
check "composer.json exists" "test -f composer.json"
check "server_v3.php exists" "test -f server_v3.php"
check "Database config exists" "test -f config/database.php"
check "CLI entry point exists" "test -f cli/mcp"
echo ""

echo "3. FILE PERMISSIONS"
echo "-------------------------------------------------------------------"
check "Cache directory writable" "test -w cache"
check ".env file readable" "test -r .env"
check "server_v3.php readable" "test -r server_v3.php"
check "CLI script executable" "test -x cli/mcp"
check_warn "Scripts executable" "test -x scripts/monitor_health.sh"
echo ""

echo "4. PHP ENVIRONMENT"
echo "-------------------------------------------------------------------"
check "PHP available" "which php"
check "PHP version >= 8.0" "php -r 'exit(version_compare(PHP_VERSION, \"8.0.0\", \">=\") ? 0 : 1);'"
check "PDO extension loaded" "php -r 'exit(extension_loaded(\"pdo\") ? 0 : 1);'"
check "PDO MySQL driver loaded" "php -r 'exit(extension_loaded(\"pdo_mysql\") ? 0 : 1);'"
check_warn "Redis extension loaded" "php -r 'exit(extension_loaded(\"redis\") ? 0 : 1);'"
check_warn "APCu extension loaded" "php -r 'exit(extension_loaded(\"apcu\") ? 0 : 1);'"
echo ""

echo "5. COMPOSER DEPENDENCIES"
echo "-------------------------------------------------------------------"
check "Autoload file exists" "test -f vendor/autoload.php"
check "Composer dependencies installed" "php -r 'require \"vendor/autoload.php\"; exit(class_exists(\"IntelligenceHub\\\\MCP\\\\Cache\\\\CacheManager\") ? 0 : 1);'"
echo ""

echo "6. DATABASE CONNECTIVITY"
echo "-------------------------------------------------------------------"
if php -r "
    require 'vendor/autoload.php';
    \$config = require 'config/database.php';
    try {
        \$pdo = new PDO(
            \"mysql:host={\$config['host']};dbname={\$config['database']};charset={\$config['charset']}\",
            \$config['username'],
            \$config['password']
        );
        exit(0);
    } catch (PDOException \$e) {
        exit(1);
    }
" 2>/dev/null; then
    echo "✅ Database connection successful"
    ((PASS++))

    # Check tables exist
    if php -r "
        require 'vendor/autoload.php';
        \$config = require 'config/database.php';
        \$pdo = new PDO(
            \"mysql:host={\$config['host']};dbname={\$config['database']};charset={\$config['charset']}\",
            \$config['username'],
            \$config['password']
        );
        \$stmt = \$pdo->query('SHOW TABLES LIKE \"content_index\"');
        exit(\$stmt->rowCount() > 0 ? 0 : 1);
    " 2>/dev/null; then
        echo "✅ Database tables exist"
        ((PASS++))
    else
        echo "⚠️  Database tables not found (run indexer)"
        ((WARN++))
    fi
else
    echo "❌ Database connection failed"
    ((FAIL++))
fi
echo ""

echo "7. REDIS CONNECTIVITY"
echo "-------------------------------------------------------------------"
if which redis-cli &>/dev/null; then
    if redis-cli ping &>/dev/null; then
        echo "✅ Redis connection successful"
        ((PASS++))
    else
        echo "⚠️  Redis not responding (will fall back to FileCache)"
        ((WARN++))
    fi
else
    echo "⚠️  redis-cli not found (will fall back to FileCache)"
    ((WARN++))
fi
echo ""

echo "8. TEST SUITE"
echo "-------------------------------------------------------------------"
echo "Running comprehensive test suite..."
if bash tests/run_all_tests.sh &>/tmp/verify_tests.log; then
    TOTAL=$(grep "Total Tests Run:" /tmp/verify_tests.log | awk '{print $4}')
    PASSED=$(grep "Passed:" /tmp/verify_tests.log | awk '{print $3}')
    echo "✅ All tests passing ($PASSED/$TOTAL)"
    ((PASS++))
else
    TOTAL=$(grep "Total Tests Run:" /tmp/verify_tests.log | awk '{print $4}')
    PASSED=$(grep "Passed:" /tmp/verify_tests.log | awk '{print $3}')
    echo "❌ Tests failing ($PASSED/$TOTAL)"
    ((FAIL++))
fi
echo ""

echo "9. DISK SPACE"
echo "-------------------------------------------------------------------"
AVAIL=$(df -h . | tail -1 | awk '{print $4}')
AVAIL_KB=$(df -k . | tail -1 | awk '{print $4}')
if [ "$AVAIL_KB" -gt 5242880 ]; then  # 5GB in KB
    echo "✅ Sufficient disk space ($AVAIL available)"
    ((PASS++))
else
    echo "⚠️  Low disk space ($AVAIL available, recommend >5GB)"
    ((WARN++))
fi
echo ""

echo "10. SECURITY CHECKS"
echo "-------------------------------------------------------------------"
check ".env file not in git" "! git ls-files --error-unmatch .env 2>/dev/null"
check "Cache directory not in git" "! git ls-files --error-unmatch cache/ 2>/dev/null || test ! -d .git"
check ".env has restricted permissions" "test $(stat -c '%a' .env) = '600' || test $(stat -c '%a' .env) = '644'"
echo ""

echo "==================================================================="
echo "  VERIFICATION SUMMARY"
echo "==================================================================="
echo ""
echo "✅ Passed: $PASS"
echo "❌ Failed: $FAIL"
echo "⚠️  Warnings: $WARN"
echo ""

if [ $FAIL -eq 0 ]; then
    echo "🎉 VERIFICATION SUCCESSFUL - READY FOR DEPLOYMENT"
    echo ""
    echo "Next steps:"
    echo "  1. Review DEPLOYMENT_GUIDE.md"
    echo "  2. Configure production .env settings"
    echo "  3. Set up monitoring (cron jobs)"
    echo "  4. Deploy to production"
    echo ""
    exit 0
else
    echo "🚫 VERIFICATION FAILED - DO NOT DEPLOY"
    echo ""
    echo "Please fix the failed checks before deploying."
    echo "Review the output above for details."
    echo ""
    exit 1
fi
