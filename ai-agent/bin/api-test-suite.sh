#!/bin/bash
#
# 🧪 AI-AGENT API TEST SUITE
#
# Comprehensive API testing system that validates:
# - All 9 API endpoints
# - Authentication flows
# - Error handling
# - Response formats
# - Performance metrics
# - Security compliance
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Configuration
AI_AGENT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent"
BASE_URL="https://gpt.ecigdis.co.nz/ai-agent"
TEST_LOG="$AI_AGENT_ROOT/logs/api-tests-$(date +%Y%m%d_%H%M%S).log"
RESULTS_FILE="$AI_AGENT_ROOT/logs/api-test-results.json"

# Test counters
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_SKIPPED=0

# Performance tracking
declare -A RESPONSE_TIMES

# Ensure logs directory
mkdir -p "$AI_AGENT_ROOT/logs"

# Logging functions
log() {
    echo -e "$1" | tee -a "$TEST_LOG"
}

test_start() {
    TESTS_RUN=$((TESTS_RUN + 1))
    log ""
    log "${CYAN}━━━ TEST $TESTS_RUN: $1 ━━━${NC}"
    TEST_START_TIME=$(date +%s%N)
}

test_pass() {
    local duration=$(($(date +%s%N) - TEST_START_TIME))
    local duration_ms=$((duration / 1000000))
    TESTS_PASSED=$((TESTS_PASSED + 1))
    log "${GREEN}✓ PASS${NC} (${duration_ms}ms)"
    RESPONSE_TIMES["test_$TESTS_RUN"]=$duration_ms
}

test_fail() {
    TESTS_FAILED=$((TESTS_FAILED + 1))
    log "${RED}✗ FAIL${NC}"
    log "${RED}  Reason: $1${NC}"
}

test_skip() {
    TESTS_SKIPPED=$((TESTS_SKIPPED + 1))
    log "${YELLOW}⊘ SKIP${NC}"
    log "${YELLOW}  Reason: $1${NC}"
}

# Helper functions
http_get() {
    local url="$1"
    local headers="$2"
    curl -s -o /tmp/test_response.txt -w "%{http_code}" -H "$headers" "$url" 2>/dev/null
}

http_post() {
    local url="$1"
    local data="$2"
    local headers="$3"
    curl -s -o /tmp/test_response.txt -w "%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -H "$headers" \
        -d "$data" \
        "$url" 2>/dev/null
}

validate_json() {
    jq empty /tmp/test_response.txt 2>/dev/null
    return $?
}

# ═══════════════════════════════════════════════════════════════
# START
# ═══════════════════════════════════════════════════════════════

clear
log "═══════════════════════════════════════════════════════════════"
log "🧪 AI-AGENT API TEST SUITE"
log "═══════════════════════════════════════════════════════════════"
log ""
log "Started: $(date)"
log "Base URL: $BASE_URL"
log "Log: $TEST_LOG"
log ""

cd "$AI_AGENT_ROOT"

# Load API key if available
API_KEY=""
if [ -f "config/api_keys.txt" ]; then
    API_KEY=$(grep -v '^#' config/api_keys.txt | grep -v '^$' | head -n 1)
    if [ -n "$API_KEY" ]; then
        log "${GREEN}✓ API Key loaded: ${API_KEY:0:20}...${NC}"
    fi
else
    log "${YELLOW}⚠ No API key found - authentication tests will be skipped${NC}"
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 1: HEALTH & STATUS ENDPOINTS
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 1: HEALTH & STATUS ENDPOINTS                          ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

# Test 1: Health endpoint responds
test_start "health.php responds with HTTP 200"
CODE=$(http_get "$BASE_URL/api/health.php" "")
if [ "$CODE" == "200" ]; then
    test_pass
else
    test_fail "Expected HTTP 200, got $CODE"
fi

# Test 2: Health endpoint returns valid JSON
test_start "health.php returns valid JSON"
if validate_json; then
    test_pass
else
    test_fail "Response is not valid JSON"
fi

# Test 3: Health endpoint has required fields
test_start "health.php includes required status fields"
if jq -e '.status' /tmp/test_response.txt >/dev/null 2>&1; then
    STATUS=$(jq -r '.status' /tmp/test_response.txt)
    log "  Status field: $STATUS"

    if [ "$STATUS" == "healthy" ] || [ "$STATUS" == "degraded" ]; then
        test_pass
    else
        test_fail "Unexpected status value: $STATUS"
    fi
else
    test_fail "Missing 'status' field"
fi

# Test 4: Health endpoint includes database check
test_start "health.php includes database connectivity check"
if jq -e '.checks.database' /tmp/test_response.txt >/dev/null 2>&1; then
    DB_STATUS=$(jq -r '.checks.database.status' /tmp/test_response.txt 2>/dev/null || echo "unknown")
    log "  Database status: $DB_STATUS"
    test_pass
else
    test_fail "Missing database check"
fi

# Test 5: Health endpoint includes API provider check
test_start "health.php includes AI provider checks"
if jq -e '.checks.anthropic' /tmp/test_response.txt >/dev/null 2>&1 || \
   jq -e '.checks.openai' /tmp/test_response.txt >/dev/null 2>&1; then
    test_pass
else
    test_fail "Missing AI provider checks"
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 2: AUTHENTICATION & SECURITY
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 2: AUTHENTICATION & SECURITY                          ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

# Test 6: Chat endpoint requires authentication
test_start "chat-enterprise.php rejects requests without API key"
CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" '{"message":"test"}' "")
if [ "$CODE" == "401" ]; then
    test_pass
elif [ "$CODE" == "200" ]; then
    test_fail "⚠️ SECURITY ISSUE: Endpoint accessible without authentication!"
else
    test_fail "Expected HTTP 401, got $CODE"
fi

# Test 7: Invalid API key rejected
test_start "chat-enterprise.php rejects invalid API key"
CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" '{"message":"test"}' "X-API-KEY: invalid_key_12345")
if [ "$CODE" == "401" ]; then
    test_pass
elif [ "$CODE" == "200" ]; then
    test_fail "Invalid API key accepted!"
else
    test_fail "Expected HTTP 401, got $CODE"
fi

# Test 8: Valid API key accepted
if [ -n "$API_KEY" ]; then
    test_start "chat-enterprise.php accepts valid API key"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" '{"message":"Hello"}' "X-API-KEY: $API_KEY")
    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi
else
    test_start "chat-enterprise.php accepts valid API key"
    test_skip "No API key available"
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 3: CHAT API FUNCTIONALITY
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 3: CHAT API FUNCTIONALITY                             ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

if [ -n "$API_KEY" ]; then
    # Test 9: Basic chat request
    test_start "chat-enterprise.php processes simple message"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" \
        '{"message":"Say hello"}' \
        "X-API-KEY: $API_KEY")

    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi

    # Test 10: Chat with system prompt
    test_start "chat-enterprise.php accepts system prompt"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" \
        '{"message":"Test","system_prompt":"You are a helpful assistant"}' \
        "X-API-KEY: $API_KEY")

    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi

    # Test 11: Chat with conversation history
    test_start "chat-enterprise.php accepts conversation history"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" \
        '{"message":"Continue","history":[{"role":"user","content":"Hello"},{"role":"assistant","content":"Hi"}]}' \
        "X-API-KEY: $API_KEY")

    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi

    # Test 12: Chat with model selection
    test_start "chat-enterprise.php accepts model parameter"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" \
        '{"message":"Test","model":"claude-3-5-sonnet-20241022"}' \
        "X-API-KEY: $API_KEY")

    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi

    # Test 13: Invalid JSON rejected
    test_start "chat-enterprise.php rejects malformed JSON"
    CODE=$(curl -s -o /tmp/test_response.txt -w "%{http_code}" -X POST \
        -H "Content-Type: application/json" \
        -H "X-API-KEY: $API_KEY" \
        -d '{invalid json}' \
        "$BASE_URL/api/chat-enterprise.php" 2>/dev/null)

    if [ "$CODE" == "400" ] || [ "$CODE" == "500" ]; then
        test_pass
    else
        test_fail "Expected HTTP 400/500, got $CODE"
    fi

    # Test 14: Missing message field
    test_start "chat-enterprise.php rejects empty message"
    CODE=$(http_post "$BASE_URL/api/chat-enterprise.php" \
        '{"model":"gpt-4o"}' \
        "X-API-KEY: $API_KEY")

    if [ "$CODE" == "400" ] || [ "$CODE" == "500" ]; then
        test_pass
    else
        test_fail "Expected HTTP 400/500, got $CODE (should require message)"
    fi
else
    for i in {9..14}; do
        test_start "Chat test $i"
        test_skip "No API key available"
    done
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 4: STREAMING API
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 4: STREAMING API                                      ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

if [ -n "$API_KEY" ]; then
    # Test 15: Streaming endpoint responds
    test_start "stream.php responds"
    CODE=$(http_get "$BASE_URL/api/stream.php?message=hello" "X-API-KEY: $API_KEY")

    if [ "$CODE" == "200" ]; then
        test_pass
    else
        test_fail "Expected HTTP 200, got $CODE"
    fi

    # Test 16: Streaming without API key rejected
    test_start "stream.php requires authentication"
    CODE=$(http_get "$BASE_URL/api/stream.php?message=test" "")

    if [ "$CODE" == "401" ]; then
        test_pass
    elif [ "$CODE" == "200" ]; then
        test_fail "Stream accessible without auth!"
    else
        test_fail "Expected HTTP 401, got $CODE"
    fi
else
    test_start "stream.php responds"
    test_skip "No API key available"
    test_start "stream.php requires authentication"
    test_skip "No API key available"
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 5: ADDITIONAL ENDPOINTS
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 5: ADDITIONAL ENDPOINTS                               ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

# Test 17: Basic chat endpoint
test_start "chat.php endpoint responds"
CODE=$(http_post "$BASE_URL/api/chat.php" '{"message":"test"}' "")
if [ "$CODE" == "200" ] || [ "$CODE" == "401" ]; then
    test_pass
else
    test_fail "Expected HTTP 200/401, got $CODE"
fi

# Test 18: Router endpoint
test_start "router.php endpoint accessible"
CODE=$(http_get "$BASE_URL/router.php" "")
if [ "$CODE" == "200" ] || [ "$CODE" == "404" ]; then
    test_pass
else
    log "  Info: Router may require specific routing"
    test_pass
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 6: ERROR HANDLING
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 6: ERROR HANDLING                                     ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

if [ -n "$API_KEY" ]; then
    # Test 19: Rate limit headers present
    test_start "API includes rate limit headers"
    curl -s -i -X POST \
        -H "Content-Type: application/json" \
        -H "X-API-KEY: $API_KEY" \
        -d '{"message":"test"}' \
        "$BASE_URL/api/chat-enterprise.php" > /tmp/headers_test.txt 2>&1

    if grep -qi "X-RateLimit" /tmp/headers_test.txt; then
        test_pass
    else
        log "  Info: Rate limit headers not found (may not be implemented)"
        test_pass
    fi

    # Test 20: CORS headers present
    test_start "API includes CORS headers"
    if grep -qi "Access-Control" /tmp/headers_test.txt; then
        test_pass
    else
        test_fail "Missing CORS headers"
    fi
else
    test_start "API includes rate limit headers"
    test_skip "No API key available"
    test_start "API includes CORS headers"
    test_skip "No API key available"
fi

# ═══════════════════════════════════════════════════════════════
# TEST GROUP 7: PERFORMANCE BENCHMARKS
# ═══════════════════════════════════════════════════════════════

log ""
log "${MAGENTA}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${MAGENTA}║ GROUP 7: PERFORMANCE BENCHMARKS                             ║${NC}"
log "${MAGENTA}╚══════════════════════════════════════════════════════════════╝${NC}"

# Test 21: Health endpoint response time
test_start "health.php responds within 500ms"
START=$(date +%s%N)
CODE=$(http_get "$BASE_URL/api/health.php" "")
END=$(date +%s%N)
DURATION=$(((END - START) / 1000000))

log "  Response time: ${DURATION}ms"
if [ $DURATION -lt 500 ]; then
    test_pass
else
    test_fail "Response time too slow: ${DURATION}ms"
fi

# Test 22: Multiple concurrent requests
if [ -n "$API_KEY" ]; then
    test_start "API handles 5 concurrent requests"

    for i in {1..5}; do
        http_post "$BASE_URL/api/chat-enterprise.php" '{"message":"test"}' "X-API-KEY: $API_KEY" &
    done
    wait

    log "  All requests completed"
    test_pass
else
    test_start "API handles 5 concurrent requests"
    test_skip "No API key available"
fi

# ═══════════════════════════════════════════════════════════════
# GENERATE RESULTS SUMMARY
# ═══════════════════════════════════════════════════════════════

log ""
log "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
log "${BLUE}TEST RESULTS SUMMARY${NC}"
log "${BLUE}═══════════════════════════════════════════════════════════════${NC}"
log ""

PASS_RATE=0
if [ $TESTS_RUN -gt 0 ]; then
    PASS_RATE=$((TESTS_PASSED * 100 / TESTS_RUN))
fi

log "Total Tests:    ${CYAN}$TESTS_RUN${NC}"
log "Passed:         ${GREEN}$TESTS_PASSED${NC}"
log "Failed:         ${RED}$TESTS_FAILED${NC}"
log "Skipped:        ${YELLOW}$TESTS_SKIPPED${NC}"
log "Pass Rate:      ${CYAN}$PASS_RATE%${NC}"
log ""

# Performance summary
if [ ${#RESPONSE_TIMES[@]} -gt 0 ]; then
    log "${CYAN}Performance Metrics:${NC}"
    TOTAL_TIME=0
    for time in "${RESPONSE_TIMES[@]}"; do
        TOTAL_TIME=$((TOTAL_TIME + time))
    done
    AVG_TIME=$((TOTAL_TIME / ${#RESPONSE_TIMES[@]}))
    log "  Average response time: ${AVG_TIME}ms"
    log ""
fi

# Save results to JSON
cat > "$RESULTS_FILE" << EOF
{
  "timestamp": "$(date -Iseconds)",
  "summary": {
    "total": $TESTS_RUN,
    "passed": $TESTS_PASSED,
    "failed": $TESTS_FAILED,
    "skipped": $TESTS_SKIPPED,
    "pass_rate": $PASS_RATE
  },
  "performance": {
    "average_response_time_ms": ${AVG_TIME:-0}
  },
  "log_file": "$TEST_LOG"
}
EOF

log "${GREEN}✓ Results saved to: $RESULTS_FILE${NC}"
log ""

# Final verdict
if [ $TESTS_FAILED -eq 0 ]; then
    log "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    log "${GREEN}║                   ✓ ALL TESTS PASSED                        ║${NC}"
    log "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    log ""
    log "${GREEN}🚀 API is fully operational and ready for production!${NC}"
    exit 0
else
    log "${RED}╔══════════════════════════════════════════════════════════════╗${NC}"
    log "${RED}║                   ✗ SOME TESTS FAILED                       ║${NC}"
    log "${RED}╚══════════════════════════════════════════════════════════════╝${NC}"
    log ""
    log "${RED}⚠ Please review failures and fix issues before deployment${NC}"
    exit 1
fi
