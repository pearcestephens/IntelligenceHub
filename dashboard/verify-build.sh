#!/bin/bash
# Dashboard Build Verification Script
# Verifies all dashboard components are in place

echo "========================================="
echo "  Dashboard Build Verification"
echo "========================================="
echo ""

DASHBOARD_ROOT="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin"

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
FILES_FOUND=0
FILES_EXPECTED=46
ISSUES=0

# Function to check file
check_file() {
    local file=$1
    local description=$2

    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $description"
        ((FILES_FOUND++))
    else
        echo -e "${RED}✗${NC} $description - NOT FOUND"
        ((ISSUES++))
    fi
}

# Function to check directory
check_dir() {
    local dir=$1
    local description=$2

    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $description"
    else
        echo -e "${RED}✗${NC} $description - NOT FOUND"
        ((ISSUES++))
    fi
}

echo "1. CORE FILES"
echo "=============="
check_file "$DASHBOARD_ROOT/index.php" "Main router (index.php)"
check_file "$DASHBOARD_ROOT/_layout.php" "Layout template"
check_file "$DASHBOARD_ROOT/_sidebar.php" "Sidebar component"
check_file "$DASHBOARD_ROOT/_nav.php" "Navigation component"
check_file "$DASHBOARD_ROOT/_footer.php" "Footer component"
echo ""

echo "2. DIRECTORIES"
echo "=============="
check_dir "$DASHBOARD_ROOT/assets" "Assets directory"
check_dir "$DASHBOARD_ROOT/assets/css" "CSS directory"
check_dir "$DASHBOARD_ROOT/assets/js" "JS directory"
check_dir "$DASHBOARD_ROOT/pages" "Pages directory"
check_dir "$DASHBOARD_ROOT/api" "API directory"
echo ""

echo "3. CSS FILES (assets/css/)"
echo "=========================="
for i in {01..10}; do
    check_file "$DASHBOARD_ROOT/assets/css/$i-*.css" "CSS $i"
done
echo ""

echo "4. JAVASCRIPT FILES (assets/js/)"
echo "==============================="
for i in {01..10}; do
    check_file "$DASHBOARD_ROOT/assets/js/$i-*.js" "JS $i"
done
echo ""

echo "5. PAGE TEMPLATES (pages/)"
echo "=========================="
check_file "$DASHBOARD_ROOT/pages/overview.php" "Overview page"
check_file "$DASHBOARD_ROOT/pages/files.php" "Files page"
check_file "$DASHBOARD_ROOT/pages/dependencies.php" "Dependencies page"
check_file "$DASHBOARD_ROOT/pages/violations.php" "Violations page"
check_file "$DASHBOARD_ROOT/pages/rules.php" "Rules page"
check_file "$DASHBOARD_ROOT/pages/metrics.php" "Metrics page"
echo ""

echo "6. API ENDPOINTS (api/)"
echo "======================="
check_file "$DASHBOARD_ROOT/api/projects/get.php" "API: Projects get"
check_file "$DASHBOARD_ROOT/api/files/details.php" "API: Files details"
check_file "$DASHBOARD_ROOT/api/violations/list.php" "API: Violations list"
check_file "$DASHBOARD_ROOT/api/metrics/dashboard.php" "API: Metrics dashboard"
check_file "$DASHBOARD_ROOT/api/scan/run.php" "API: Scan run"
echo ""

echo "7. DOCUMENTATION"
echo "================"
check_file "$DASHBOARD_ROOT/DASHBOARD_BUILD_COMPLETE.md" "Build documentation"
echo ""

# Summary
echo "========================================="
echo "  VERIFICATION SUMMARY"
echo "========================================="
echo ""
echo "Files Found: $FILES_FOUND / $FILES_EXPECTED"
echo "Issues: $ISSUES"
echo ""

if [ $ISSUES -eq 0 ]; then
    echo -e "${GREEN}✓ All dashboard components are in place!${NC}"
    echo ""
    echo "Dashboard URL:"
    echo "  https://your-domain.com/dashboard/admin/"
    echo ""
    echo "Available Pages:"
    echo "  ?page=overview        - Main dashboard"
    echo "  ?page=files          - File browser"
    echo "  ?page=dependencies   - Dependency view"
    echo "  ?page=violations     - Rule violations"
    echo "  ?page=rules          - Coding standards"
    echo "  ?page=metrics        - Analytics & trends"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Dashboard has issues ($ISSUES)${NC}"
    exit 1
fi
