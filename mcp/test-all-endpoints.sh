#!/bin/bash
# ============================================================================
# MCP Server v3 - Complete Endpoint Testing Script
# ============================================================================

set -e

API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
BASE_URL="https://gpt.ecigdis.co.nz/mcp"
HEADERS=(-H "Content-Type: application/json" -H "X-API-Key: $API_KEY")

echo "============================================================================"
echo "MCP Server v3 - Endpoint Testing"
echo "============================================================================"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

test_count=0
pass_count=0
fail_count=0

# Test function
test_endpoint() {
    local name="$1"
    local method="$2"
    local url="$3"
    local data="$4"
    local expect_success="${5:-true}"

    test_count=$((test_count + 1))
    echo -e "${YELLOW}[$test_count]${NC} Testing: $name"

    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" "$url" 2>&1)
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" "${HEADERS[@]}" -d "$data" "$url" 2>&1)
    fi

    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | head -n-1)

    if [ "$expect_success" = "true" ] && [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✓ PASS${NC} (HTTP $http_code)"
        pass_count=$((pass_count + 1))
        echo "$body" | jq -C '.' 2>/dev/null || echo "$body"
    elif [ "$expect_success" = "false" ] && [ "$http_code" != "200" ]; then
        echo -e "${GREEN}✓ PASS${NC} (Expected failure: HTTP $http_code)"
        pass_count=$((pass_count + 1))
    else
        echo -e "${RED}✗ FAIL${NC} (HTTP $http_code)"
        fail_count=$((fail_count + 1))
        echo "$body"
    fi
    echo ""
}

# Test JSON-RPC method
test_jsonrpc() {
    local name="$1"
    local method="$2"
    local params="$3"

    test_count=$((test_count + 1))
    echo -e "${YELLOW}[$test_count]${NC} Testing: $name"

    data="{\"jsonrpc\":\"2.0\",\"id\":$test_count,\"method\":\"$method\",\"params\":$params}"

    response=$(curl -s -X POST "${HEADERS[@]}" -d "$data" "$BASE_URL/server_v3.php")

    if echo "$response" | jq -e '.result' > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        pass_count=$((pass_count + 1))
        echo "$response" | jq -C '.'
    elif echo "$response" | jq -e '.error' > /dev/null 2>&1; then
        error_msg=$(echo "$response" | jq -r '.error.message')
        echo -e "${RED}✗ FAIL${NC} - $error_msg"
        fail_count=$((fail_count + 1))
        echo "$response" | jq -C '.'
    else
        echo -e "${RED}✗ FAIL${NC} - Invalid response"
        fail_count=$((fail_count + 1))
        echo "$response"
    fi
    echo ""
}

echo "============================================================================"
echo "PHASE 1: Public Endpoints (No Auth Required)"
echo "============================================================================"
echo ""

test_endpoint "Health Check" "GET" "$BASE_URL/health_v3.php" ""
test_endpoint "Meta/Discovery" "GET" "$BASE_URL/server_v3.php?action=meta" ""
test_endpoint "Server Health (action=health)" "GET" "$BASE_URL/server_v3.php?action=health" ""

echo "============================================================================"
echo "PHASE 2: Authentication Tests"
echo "============================================================================"
echo ""

test_endpoint "RPC without API key (should fail)" "POST" "$BASE_URL/server_v3.php?action=rpc" \
    '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}' "false"

echo "============================================================================"
echo "PHASE 3: Core JSON-RPC Methods"
echo "============================================================================"
echo ""

test_jsonrpc "tools/list - List all available tools" "tools/list" "{}"
test_jsonrpc "initialize - Initialize server" "initialize" '{"protocolVersion":"2024-11-05","capabilities":{},"clientInfo":{"name":"test-client","version":"1.0.0"}}'

echo "============================================================================"
echo "PHASE 4: Database Tools"
echo "============================================================================"
echo ""

test_jsonrpc "db.query - Simple SELECT" "tools/call" \
    '{"name":"db.query","arguments":{"query":"SELECT COUNT(*) as count FROM users LIMIT 1"}}'

test_jsonrpc "db.schema - Get table schema" "tools/call" \
    '{"name":"db.schema","arguments":{"table":"users"}}'

test_jsonrpc "db.tables - List all tables" "tools/call" \
    '{"name":"db.tables","arguments":{}}'

echo "============================================================================"
echo "PHASE 5: File System Tools"
echo "============================================================================"
echo ""

test_jsonrpc "fs.read - Read file" "tools/call" \
    '{"name":"fs.read","arguments":{"path":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env","start_line":1,"end_line":10}}'

test_jsonrpc "fs.search - Search files" "tools/call" \
    '{"name":"fs.search","arguments":{"pattern":"*.php","directory":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp","max_results":5}}'

test_jsonrpc "fs.tree - Directory tree" "tools/call" \
    '{"name":"fs.tree","arguments":{"path":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/config","max_depth":2}}'

echo "============================================================================"
echo "PHASE 6: Search Tools"
echo "============================================================================"
echo ""

test_jsonrpc "semantic_search - Semantic search" "tools/call" \
    '{"name":"semantic_search","arguments":{"query":"database connection","limit":3}}'

test_jsonrpc "grep_search - Pattern search" "tools/call" \
    '{"name":"grep_search","arguments":{"pattern":"function","path":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php","max_results":5}}'

test_jsonrpc "find_code - Find code patterns" "tools/call" \
    '{"name":"find_code","arguments":{"pattern":"class.*extends","file_pattern":"*.php","limit":3}}'

echo "============================================================================"
echo "PHASE 7: Context & Memory Tools"
echo "============================================================================"
echo ""

test_jsonrpc "context.detect - Detect context" "tools/call" \
    '{"name":"context.detect","arguments":{"file_path":"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php"}}'

test_jsonrpc "memory.store - Store memory" "tools/call" \
    '{"name":"memory.store","arguments":{"conversation_id":"test-123","content":"Test memory storage","memory_type":"note","importance":"low","tags":["test"]}}'

test_jsonrpc "memory.recall - Recall memory" "tools/call" \
    '{"name":"memory.recall","arguments":{"conversation_id":"test-123","limit":5}}'

echo "============================================================================"
echo "PHASE 8: Conversation Tools"
echo "============================================================================"
echo ""

test_jsonrpc "conversation.list - List conversations" "tools/call" \
    '{"name":"conversation.list","arguments":{"limit":5}}'

test_jsonrpc "conversation.get_project_context - Get project context" "tools/call" \
    '{"name":"conversation.get_project_context","arguments":{"business_unit_id":2,"limit":10}}'

test_jsonrpc "conversation.search - Search conversations" "tools/call" \
    '{"name":"conversation.search","arguments":{"query":"database","limit":5}}'

echo "============================================================================"
echo "PHASE 9: Knowledge Base Tools"
echo "============================================================================"
echo ""

test_jsonrpc "kb.search - Search knowledge base" "tools/call" \
    '{"name":"kb.search","arguments":{"query":"MCP server","limit":3}}'

test_jsonrpc "kb.list - List documents" "tools/call" \
    '{"name":"kb.list","arguments":{"limit":5}}'

test_jsonrpc "kb.get - Get document" "tools/call" \
    '{"name":"kb.get","arguments":{"id":1}}'

echo "============================================================================"
echo "PHASE 10: AI Agent Tool"
echo "============================================================================"
echo ""

test_jsonrpc "ai_agent.query - Query AI Agent" "tools/call" \
    '{"name":"ai_agent.query","arguments":{"query":"What is the MCP server version?","stream":false}}'

echo "============================================================================"
echo "PHASE 11: Utility Tools"
echo "============================================================================"
echo ""

test_jsonrpc "logs.tail - Tail log file" "tools/call" \
    '{"name":"logs.tail","arguments":{"file":"mcp","lines":10}}'

test_jsonrpc "ops.monitoring_snapshot - Get system status" "tools/call" \
    '{"name":"ops.monitoring_snapshot","arguments":{}}'

test_jsonrpc "health.check - Health check" "tools/call" \
    '{"name":"health.check","arguments":{}}'

echo "============================================================================"
echo "TEST SUMMARY"
echo "============================================================================"
echo ""
echo "Total Tests: $test_count"
echo -e "${GREEN}Passed: $pass_count${NC}"
echo -e "${RED}Failed: $fail_count${NC}"
echo ""

if [ $fail_count -eq 0 ]; then
    echo -e "${GREEN}✓ ALL TESTS PASSED!${NC}"
    exit 0
else
    echo -e "${RED}✗ SOME TESTS FAILED${NC}"
    exit 1
fi
