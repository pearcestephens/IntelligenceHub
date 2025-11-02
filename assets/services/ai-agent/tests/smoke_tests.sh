#!/bin/bash
# MCP & AI Agent Smoke Tests
# Generated: 2025-11-02
# Purpose: Comprehensive testing of all MCP endpoints and AI Agent features

set -e  # Exit on error

HOST="${TEST_HOST:-https://gpt.ecigdis.co.nz}"
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "═══════════════════════════════════════════════════════"
echo "  MCP & AI Agent Smoke Tests"
echo "  Host: $HOST"
echo "  Date: $(date)"
echo "═══════════════════════════════════════════════════════"
echo ""

# Test counter
TESTS_PASSED=0
TESTS_FAILED=0

# Helper function to run test
run_test() {
    local name="$1"
    local command="$2"
    local expected="$3"

    echo -n "Testing: $name ... "

    if output=$(eval "$command" 2>&1); then
        if echo "$output" | grep -q "$expected"; then
            echo -e "${GREEN}✓ PASS${NC}"
            TESTS_PASSED=$((TESTS_PASSED + 1))
            return 0
        else
            echo -e "${RED}✗ FAIL${NC} (expected pattern not found)"
            echo "  Output: ${output:0:200}"
            TESTS_FAILED=$((TESTS_FAILED + 1))
            return 1
        fi
    else
        echo -e "${RED}✗ FAIL${NC} (command failed)"
        echo "  Error: $output"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

echo "━━━ A1: MCP Server v3 Tests ━━━"
run_test "MCP Meta Endpoint" \
    "curl -sS $HOST/mcp/server_v3.php?action=meta" \
    '"name":"Ecigdis MCP"'

run_test "MCP Health Endpoint" \
    "curl -sS $HOST/mcp/server_v3.php?action=health" \
    '"ok":true'

echo ""
echo "━━━ A2: MCP v4 Registry & Call Tests ━━━"
run_test "MCP v4 Registry" \
    "curl -sS $HOST/assets/services/ai-agent/mcp/registry.php" \
    '"name":"Ecigdis AI Agent Tools"'

run_test "MCP v4 Registry - Tool Count" \
    "curl -sS $HOST/assets/services/ai-agent/mcp/registry.php" \
    '"local_tools"'

echo ""
echo "━━━ A3: Tool Invocation Tests (Non-Streaming) ━━━"
run_test "MCP Call - fs.list" \
    "curl -sS -X POST $HOST/assets/services/ai-agent/mcp/call.php -H 'Content-Type: application/json' -d '{\"tool\":\"fs.list\",\"args\":{\"path\":\"assets/services\"}}'" \
    '"success":true'

run_test "MCP Call - db.select (via server_v3)" \
    "curl -sS -X POST $HOST/mcp/server_v3.php?action=rpc -H 'Content-Type: application/json' -d '{\"jsonrpc\":\"2.0\",\"method\":\"tools/call\",\"params\":{\"name\":\"db.tables\",\"arguments\":{}},\"id\":1}'" \
    '"result"'

echo ""
echo "━━━ A4: Health Endpoints Tests ━━━"
run_test "AI Agent Healthz (Liveness)" \
    "curl -sS $HOST/assets/services/ai-agent/api/healthz.php" \
    '"alive":true'

run_test "AI Agent Readyz (Readiness)" \
    "curl -sS $HOST/assets/services/ai-agent/api/readyz.php" \
    '"ready":true'

echo ""
echo "━━━ A5: Chat Endpoint Tests ━━━"
run_test "Chat Endpoint (Non-Streaming)" \
    "curl -sS -X POST $HOST/assets/services/ai-agent/api/chat.php -H 'Content-Type: application/json' -d '{\"message\":\"test\",\"provider\":\"openai\",\"model\":\"gpt-3.5-turbo\",\"session_key\":\"smoke-test\"}'" \
    '"request_id"'

echo ""
echo "━━━ A6: Database Tables Check ━━━"
run_test "Check ai_stream_tickets table exists" \
    "mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SHOW TABLES LIKE \"ai_stream_tickets\"'" \
    "ai_stream_tickets"

run_test "Check ai_idempotency_keys table exists" \
    "mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SHOW TABLES LIKE \"ai_idempotency_keys\"'" \
    "ai_idempotency_keys"

run_test "Check ai_conversation_messages table exists" \
    "mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SHOW TABLES LIKE \"ai_conversation_messages\"'" \
    "ai_conversation_messages"

run_test "Check mcp_tool_usage table exists" \
    "mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e 'SHOW TABLES LIKE \"mcp_tool_usage\"'" \
    "mcp_tool_usage"

echo ""
echo "━━━ A7: Streaming Setup Tests ━━━"
run_test "MCP Call - Request Stream Ticket" \
    "curl -sS -X POST $HOST/assets/services/ai-agent/mcp/call.php -H 'Content-Type: application/json' -d '{\"tool\":\"fs.list\",\"args\":{\"path\":\"assets\"},\"stream\":true}'" \
    '"streaming":true'

echo ""
echo "═══════════════════════════════════════════════════════"
echo "  Test Results"
echo "═══════════════════════════════════════════════════════"
echo -e "  ${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "  ${RED}Failed: $TESTS_FAILED${NC}"
echo "  Total:  $((TESTS_PASSED + TESTS_FAILED))"
echo ""

if [ $TESTS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ ALL TESTS PASSED!${NC}"
    echo ""
    echo "🎉 MCP Server is production-ready!"
    exit 0
else
    echo -e "${RED}✗ SOME TESTS FAILED${NC}"
    echo ""
    echo "⚠️  Please review failed tests above"
    exit 1
fi
