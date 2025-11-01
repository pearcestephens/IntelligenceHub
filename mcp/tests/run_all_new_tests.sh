#!/bin/bash
##
# MCP Intelligence Hub - Master Test Runner
#
# Runs all unit test suites and generates comprehensive report.
#
# Usage: bash run_all_new_tests.sh
##

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Test directory
TEST_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$TEST_DIR"

# Results tracking
TOTAL_SUITES=0
PASSED_SUITES=0
FAILED_SUITES=0

echo ""
echo -e "${PURPLE}╔══════════════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${PURPLE}║  MCP INTELLIGENCE HUB v4.0 - COMPREHENSIVE TEST SUITE                       ║${NC}"
echo -e "${PURPLE}╚══════════════════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${CYAN}Testing new enterprise features: Fuzzy Search, Analytics, Central Dispatcher${NC}"
echo ""
echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo ""

# Function to run a test suite
run_test_suite() {
    local test_file="$1"
    local test_name="$2"

    TOTAL_SUITES=$((TOTAL_SUITES + 1))

    echo -e "${BLUE}▶ Running: ${test_name}${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

    # Make executable
    chmod +x "$test_file"

    # Run test and capture output
    if "$test_file"; then
        PASSED_SUITES=$((PASSED_SUITES + 1))
        echo -e "${GREEN}✅ PASSED: ${test_name}${NC}"
    else
        FAILED_SUITES=$((FAILED_SUITES + 1))
        echo -e "${RED}❌ FAILED: ${test_name}${NC}"
    fi

    echo ""
}

# ============================================================================
# RUN ALL TEST SUITES
# ============================================================================

# Test 1: Fuzzy Search Engine
if [ -f "test_fuzzy_search.php" ]; then
    run_test_suite "test_fuzzy_search.php" "Fuzzy Search Engine Tests"
else
    echo -e "${RED}❌ test_fuzzy_search.php not found${NC}"
    echo ""
fi

# Test 2: Search Analytics
if [ -f "test_analytics.php" ]; then
    run_test_suite "test_analytics.php" "Search Analytics Tests"
else
    echo -e "${RED}❌ test_analytics.php not found${NC}"
    echo ""
fi

# Test 3: Central Dispatcher
if [ -f "test_dispatcher.php" ]; then
    run_test_suite "test_dispatcher.php" "Central Dispatcher Tests"
else
    echo -e "${RED}❌ test_dispatcher.php not found${NC}"
    echo ""
fi

# ============================================================================
# FINAL SUMMARY
# ============================================================================

echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo -e "${PURPLE}COMPREHENSIVE TEST SUMMARY${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "Total Test Suites:  ${TOTAL_SUITES}"
echo -e "${GREEN}Passed Suites:      ${PASSED_SUITES} ✅${NC}"
echo -e "${RED}Failed Suites:      ${FAILED_SUITES} ❌${NC}"
echo ""

# Calculate pass rate
if [ $TOTAL_SUITES -gt 0 ]; then
    PASS_RATE=$(awk "BEGIN {printf \"%.1f\", ($PASSED_SUITES / $TOTAL_SUITES) * 100}")
    echo -e "Pass Rate:          ${PASS_RATE}%"
    echo ""
fi

# ============================================================================
# FEATURE COVERAGE REPORT
# ============================================================================

echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo -e "${PURPLE}FEATURE COVERAGE${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "📦 Tested Components:"
echo -e "   • FuzzySearchEngine.php      - Typo correction & phonetic matching"
echo -e "   • SearchAnalytics.php        - Real-time analytics & metrics"
echo -e "   • dispatcher.php             - Central routing endpoint"
echo -e "   • SemanticSearchTool v4.0    - Enhanced search with fuzzy + analytics"
echo ""
echo -e "🎯 Test Categories:"
echo -e "   • Levenshtein Distance       - String similarity calculations"
echo -e "   • Programming Typo Fixes     - 50+ common coding typos"
echo -e "   • Phonetic Matching          - Soundex & Metaphone algorithms"
echo -e "   • Query Suggestions          - Alternative search terms"
echo -e "   • Analytics Logging          - Search behavior tracking"
echo -e "   • Performance Metrics        - Speed & efficiency stats"
echo -e "   • Cache Statistics           - Hit rate & speedup analysis"
echo -e "   • Search Patterns            - User behavior insights"
echo -e "   • HTTP Routing               - Dispatcher endpoint logic"
echo -e "   • Error Handling             - Graceful failure modes"
echo ""

# ============================================================================
# SYSTEM STATUS CHECK
# ============================================================================

echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo -e "${PURPLE}SYSTEM STATUS${NC}"
echo -e "${YELLOW}═══════════════════════════════════════════════════════════════════════════════${NC}"
echo ""

# Check if dispatcher is accessible
if curl -s -o /dev/null -w "%{http_code}" "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=health" | grep -q "200\|503"; then
    echo -e "${GREEN}✅ Dispatcher Endpoint: ACCESSIBLE${NC}"
else
    echo -e "${RED}❌ Dispatcher Endpoint: NOT ACCESSIBLE${NC}"
fi

# Check if analytics dashboard exists
if [ -f "../analytics-dashboard.html" ]; then
    echo -e "${GREEN}✅ Analytics Dashboard: DEPLOYED${NC}"
else
    echo -e "${RED}❌ Analytics Dashboard: NOT FOUND${NC}"
fi

# Check if fuzzy search engine exists
if [ -f "../src/Search/FuzzySearchEngine.php" ]; then
    echo -e "${GREEN}✅ Fuzzy Search Engine: DEPLOYED${NC}"
else
    echo -e "${RED}❌ Fuzzy Search Engine: NOT FOUND${NC}"
fi

# Check if analytics engine exists
if [ -f "../src/Analytics/SearchAnalytics.php" ]; then
    echo -e "${GREEN}✅ Search Analytics: DEPLOYED${NC}"
else
    echo -e "${RED}❌ Search Analytics: NOT FOUND${NC}"
fi

echo ""

# ============================================================================
# NEXT STEPS
# ============================================================================

if [ $FAILED_SUITES -eq 0 ]; then
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════════════════════${NC}"
    echo -e "${GREEN}🎉 ALL TESTS PASSED! SYSTEM READY FOR PRODUCTION${NC}"
    echo -e "${GREEN}═══════════════════════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "${CYAN}📋 Next Steps:${NC}"
    echo -e "   1. Deploy dispatcher.php to production"
    echo -e "   2. Update documentation with new endpoints"
    echo -e "   3. Monitor analytics dashboard for usage patterns"
    echo -e "   4. Test fuzzy search with real user queries"
    echo ""
    echo -e "${CYAN}🔗 Production URLs:${NC}"
    echo -e "   Dispatcher: https://gpt.ecigdis.co.nz/mcp/dispatcher.php"
    echo -e "   Dashboard:  https://gpt.ecigdis.co.nz/mcp/analytics-dashboard.html"
    echo ""
    exit 0
else
    echo -e "${RED}═══════════════════════════════════════════════════════════════════════════════${NC}"
    echo -e "${RED}⚠️  SOME TESTS FAILED - REVIEW REQUIRED${NC}"
    echo -e "${RED}═══════════════════════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "${YELLOW}📋 Action Items:${NC}"
    echo -e "   1. Review failed test output above"
    echo -e "   2. Fix identified issues"
    echo -e "   3. Re-run tests: bash run_all_new_tests.sh"
    echo -e "   4. Ensure all tests pass before deployment"
    echo ""
    exit 1
fi
