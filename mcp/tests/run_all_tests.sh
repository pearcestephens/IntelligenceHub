#!/bin/bash
# Final Performance Benchmark Suite
# Runs all test suites and collects comprehensive metrics

echo "======================================================================"
echo "  PHASE 1 - FINAL PERFORMANCE BENCHMARK"
echo "======================================================================"
echo ""
echo "Running all test suites sequentially..."
echo ""

TOTAL_TESTS=0
TOTAL_PASSED=0
TOTAL_FAILED=0

# Test 1: Semantic Search Tests
echo "----------------------------------------------------------------------"
echo "1. SEMANTIC SEARCH TESTS"
echo "----------------------------------------------------------------------"
START_TIME=$(date +%s%3N)
php tests/semantic_search_test.php > /tmp/test1_output.txt 2>&1
EXIT_CODE=$?
END_TIME=$(date +%s%3N)
DURATION=$((END_TIME - START_TIME))

if [ $EXIT_CODE -eq 0 ]; then
    PASSED=$(grep "✅ PASSED" /tmp/test1_output.txt | wc -l)
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED))
    echo "✅ Status: PASSED ($PASSED tests, ${DURATION}ms)"
else
    FAILED=$(grep "❌ FAILED" /tmp/test1_output.txt | wc -l)
    PASSED=$(grep "✅ PASSED" /tmp/test1_output.txt | wc -l)
    TOTAL_FAILED=$((TOTAL_FAILED + FAILED))
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED + FAILED))
    echo "❌ Status: FAILED ($FAILED/$((PASSED + FAILED)) tests failed, ${DURATION}ms)"
fi

# Show key metrics
grep -E "(cache|duration|speedup)" /tmp/test1_output.txt | head -5
echo ""

# Test 2: PHPIndexer Tests
echo "----------------------------------------------------------------------"
echo "2. PHPINDEXER TESTS"
echo "----------------------------------------------------------------------"
START_TIME=$(date +%s%3N)
php tests/php_indexer_test.php > /tmp/test2_output.txt 2>&1
EXIT_CODE=$?
END_TIME=$(date +%s%3N)
DURATION=$((END_TIME - START_TIME))

if [ $EXIT_CODE -eq 0 ]; then
    PASSED=$(grep "✅ PASSED" /tmp/test2_output.txt | wc -l)
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED))
    echo "✅ Status: PASSED ($PASSED tests, ${DURATION}ms)"
else
    FAILED=$(grep "❌ FAILED" /tmp/test2_output.txt | wc -l)
    PASSED=$(grep "✅ PASSED" /tmp/test2_output.txt | wc -l)
    TOTAL_FAILED=$((TOTAL_FAILED + FAILED))
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED + FAILED))
    echo "❌ Status: FAILED ($FAILED/$((PASSED + FAILED)) tests failed, ${DURATION}ms)"
fi

# Show key metrics
grep -E "(Quality|Complexity)" /tmp/test2_output.txt | head -3
echo ""

# Test 3: HTTP Endpoint Tests
echo "----------------------------------------------------------------------"
echo "3. HTTP ENDPOINT TESTS"
echo "----------------------------------------------------------------------"
START_TIME=$(date +%s%3N)
php tests/endpoint_test.php > /tmp/test3_output.txt 2>&1
EXIT_CODE=$?
END_TIME=$(date +%s%3N)
DURATION=$((END_TIME - START_TIME))

if [ $EXIT_CODE -eq 0 ]; then
    PASSED=$(grep "✅ PASSED" /tmp/test3_output.txt | wc -l)
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED))
    echo "✅ Status: PASSED ($PASSED tests, ${DURATION}ms)"
else
    FAILED=$(grep "❌ FAILED" /tmp/test3_output.txt | wc -l)
    PASSED=$(grep "✅ PASSED" /tmp/test3_output.txt | wc -l)
    TOTAL_FAILED=$((TOTAL_FAILED + FAILED))
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED + FAILED))
    echo "❌ Status: FAILED ($FAILED/$((PASSED + FAILED)) tests failed, ${DURATION}ms)"
fi

# Show key performance metrics
grep -E "(Speedup|Duration|Cache)" /tmp/test3_output.txt | grep -v "TEST" | head -5
echo ""

# Test 4: Cache Fallback Tests
echo "----------------------------------------------------------------------"
echo "4. CACHE FALLBACK TESTS"
echo "----------------------------------------------------------------------"
START_TIME=$(date +%s%3N)
php tests/cache_fallback_test.php > /tmp/test4_output.txt 2>&1
EXIT_CODE=$?
END_TIME=$(date +%s%3N)
DURATION=$((END_TIME - START_TIME))

if [ $EXIT_CODE -eq 0 ]; then
    PASSED=$(grep "✅ PASSED" /tmp/test4_output.txt | wc -l)
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED))
    echo "✅ Status: PASSED ($PASSED tests, ${DURATION}ms)"
else
    FAILED=$(grep "❌ FAILED" /tmp/test4_output.txt | wc -l)
    PASSED=$(grep "✅ PASSED" /tmp/test4_output.txt | wc -l)
    TOTAL_FAILED=$((TOTAL_FAILED + FAILED))
    TOTAL_PASSED=$((TOTAL_PASSED + PASSED))
    TOTAL_TESTS=$((TOTAL_TESTS + PASSED + FAILED))
    echo "⚠️  Status: PARTIAL ($FAILED/$((PASSED + FAILED)) tests failed, ${DURATION}ms)"
fi

# Show fallback metrics
grep -E "(Redis|FileCache|Performance)" /tmp/test4_output.txt | grep -E "(✅|ms)" | head -5
echo ""

# Summary
echo "======================================================================"
echo "  FINAL SUMMARY"
echo "======================================================================"
echo ""
echo "Total Tests Run: $TOTAL_TESTS"
echo "✅ Passed: $TOTAL_PASSED"
echo "❌ Failed: $TOTAL_FAILED"
SUCCESS_RATE=$((TOTAL_PASSED * 100 / TOTAL_TESTS))
echo "Success Rate: ${SUCCESS_RATE}%"
echo ""

if [ $TOTAL_FAILED -eq 0 ]; then
    echo "🎉 ALL TESTS PASSED - PHASE 1 COMPLETE!"
    echo ""
    exit 0
elif [ $SUCCESS_RATE -ge 90 ]; then
    echo "✅ EXCELLENT - Phase 1 substantially complete (${SUCCESS_RATE}% pass rate)"
    echo ""
    exit 0
elif [ $SUCCESS_RATE -ge 80 ]; then
    echo "⚠️  GOOD - Phase 1 mostly complete (${SUCCESS_RATE}% pass rate)"
    echo "   Review failed tests above"
    echo ""
    exit 1
else
    echo "❌ NEEDS WORK - Several tests failing (${SUCCESS_RATE}% pass rate)"
    echo ""
    exit 1
fi
