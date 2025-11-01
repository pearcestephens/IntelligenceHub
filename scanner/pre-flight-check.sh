#!/bin/bash
#
# Scanner Application - Pre-Flight Check
# Verifies all infrastructure is in place before copying V2 files
#

echo "=========================================="
echo "Scanner Application - Pre-Flight Check"
echo "=========================================="
echo ""

SCANNER_ROOT="/home/master/applications/hdgwrzntwa/public_html/scanner"
cd "$SCANNER_ROOT"

SUCCESS_COUNT=0
TOTAL_CHECKS=0

check() {
    TOTAL_CHECKS=$((TOTAL_CHECKS + 1))
    if [ "$2" = "true" ]; then
        echo "✅ $1"
        SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
    else
        echo "❌ $1"
    fi
}

echo "📁 DIRECTORY STRUCTURE"
echo "---"
check "scanner/ directory exists" "$([ -d "$SCANNER_ROOT" ] && echo true || echo false)"
check "pages/ directory exists" "$([ -d "$SCANNER_ROOT/pages" ] && echo true || echo false)"
check "assets/css/ directory exists" "$([ -d "$SCANNER_ROOT/assets/css" ] && echo true || echo false)"
check "assets/js/ directory exists" "$([ -d "$SCANNER_ROOT/assets/js" ] && echo true || echo false)"
check "includes/ directory exists" "$([ -d "$SCANNER_ROOT/includes" ] && echo true || echo false)"
check "config/ directory exists" "$([ -d "$SCANNER_ROOT/config" ] && echo true || echo false)"
check "api/ directory exists" "$([ -d "$SCANNER_ROOT/api" ] && echo true || echo false)"

echo ""
echo "📄 CORE FILES"
echo "---"
check "index.php exists" "$([ -f "$SCANNER_ROOT/index.php" ] && echo true || echo false)"
check "config/database.php exists" "$([ -f "$SCANNER_ROOT/config/database.php" ] && echo true || echo false)"
check "includes/sidebar.php exists" "$([ -f "$SCANNER_ROOT/includes/sidebar.php" ] && echo true || echo false)"
check "includes/navbar.php exists" "$([ -f "$SCANNER_ROOT/includes/navbar.php" ] && echo true || echo false)"
check "includes/footer.php exists" "$([ -f "$SCANNER_ROOT/includes/footer.php" ] && echo true || echo false)"

echo ""
echo "🛠️ SETUP SCRIPTS"
echo "---"
check "setup-copy.php exists" "$([ -f "$SCANNER_ROOT/setup-copy.php" ] && echo true || echo false)"
check "setup-copy-files.sh exists" "$([ -f "$SCANNER_ROOT/setup-copy-files.sh" ] && echo true || echo false)"

echo ""
echo "📚 DOCUMENTATION"
echo "---"
check "README.md exists" "$([ -f "$SCANNER_ROOT/README.md" ] && echo true || echo false)"
check "READY_TO_EXECUTE.md exists" "$([ -f "$SCANNER_ROOT/READY_TO_EXECUTE.md" ] && echo true || echo false)"
check "COMPLETE_SUMMARY.md exists" "$([ -f "$SCANNER_ROOT/COMPLETE_SUMMARY.md" ] && echo true || echo false)"

echo ""
echo "🔗 SOURCE FILES (V2)"
echo "---"
SOURCE_PAGES="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/pages-v2"
SOURCE_CSS="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/assets/css"
SOURCE_JS="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/assets/js"

check "pages-v2/ directory accessible" "$([ -d "$SOURCE_PAGES" ] && echo true || echo false)"
check "Source CSS directory accessible" "$([ -d "$SOURCE_CSS" ] && echo true || echo false)"
check "Source JS directory accessible" "$([ -d "$SOURCE_JS" ] && echo true || echo false)"

if [ -d "$SOURCE_PAGES" ]; then
    PAGE_COUNT=$(ls -1 "$SOURCE_PAGES"/*.php 2>/dev/null | wc -l)
    echo "   → Found $PAGE_COUNT PHP page files"
fi

if [ -d "$SOURCE_CSS" ]; then
    CSS_COUNT=$(ls -1 "$SOURCE_CSS"/*.css 2>/dev/null | wc -l)
    echo "   → Found $CSS_COUNT CSS files"
fi

if [ -d "$SOURCE_JS" ]; then
    JS_COUNT=$(ls -1 "$SOURCE_JS"/*.js 2>/dev/null | wc -l)
    echo "   → Found $JS_COUNT JavaScript files"
fi

echo ""
echo "🔐 PERMISSIONS"
echo "---"
check "scanner/ is writable" "$([ -w "$SCANNER_ROOT" ] && echo true || echo false)"
check "pages/ is writable" "$([ -w "$SCANNER_ROOT/pages" ] && echo true || echo false)"
check "assets/ is writable" "$([ -w "$SCANNER_ROOT/assets" ] && echo true || echo false)"

echo ""
echo "⚙️ PHP ENVIRONMENT"
echo "---"
PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
check "PHP 8.0+ available" "$([ $(echo "$PHP_VERSION >= 8.0" | bc) -eq 1 ] && echo true || echo false)"
echo "   → PHP Version: $PHP_VERSION"

check "PDO extension loaded" "$(php -m | grep -q PDO && echo true || echo false)"
check "PDO_MySQL extension loaded" "$(php -m | grep -q pdo_mysql && echo true || echo false)"

echo ""
echo "🗄️ DATABASE"
echo "---"
DB_TEST=$(mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT 1;" 2>&1)
check "Database connection works" "$(echo "$DB_TEST" | grep -q '1' && echo true || echo false)"

if [ $(echo "$DB_TEST" | grep -q '1' && echo true || echo false) = "true" ]; then
    PROJECT_COUNT=$(mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -se "SELECT COUNT(*) FROM projects WHERE status='active';" 2>/dev/null)
    echo "   → Active projects: $PROJECT_COUNT"
fi

echo ""
echo "=========================================="
echo "RESULTS"
echo "=========================================="
echo ""
echo "Passed: $SUCCESS_COUNT / $TOTAL_CHECKS checks"
echo ""

if [ "$SUCCESS_COUNT" -eq "$TOTAL_CHECKS" ]; then
    echo "✅ ALL CHECKS PASSED!"
    echo ""
    echo "🚀 READY TO EXECUTE:"
    echo ""
    echo "   cd $SCANNER_ROOT"
    echo "   php setup-copy.php"
    echo ""
    echo "This will copy:"
    echo "   → $PAGE_COUNT page files"
    echo "   → $CSS_COUNT CSS files"
    echo "   → $JS_COUNT JavaScript files"
    echo ""
else
    FAILED=$((TOTAL_CHECKS - SUCCESS_COUNT))
    echo "⚠️  $FAILED CHECKS FAILED"
    echo ""
    echo "Please fix the issues above before proceeding."
    echo ""
fi

echo "=========================================="
