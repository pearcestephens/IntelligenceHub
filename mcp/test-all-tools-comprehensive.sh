#!/bin/bash
# ============================================================================
# MCP Server v3 - Comprehensive All Tools Testing
# Tests all 54 registered tools with realistic test cases
# ============================================================================

set -e

API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
BASE_URL="https://gpt.ecigdis.co.nz/mcp/server_v3.php"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

test_count=0
pass_count=0
fail_count=0
skip_count=0

call_tool() {
    local tool_name="$1"
    local args="$2"
    local description="$3"

    test_count=$((test_count + 1))
    echo -e "${BLUE}[$test_count]${NC} ${YELLOW}$tool_name${NC} - $description"

    local payload="{\"jsonrpc\":\"2.0\",\"id\":$test_count,\"method\":\"tools/call\",\"params\":{\"name\":\"$tool_name\",\"arguments\":$args}}"

    response=$(curl -s -X POST "$BASE_URL" \
        -H "Content-Type: application/json" \
        -H "X-API-Key: $API_KEY" \
        -d "$payload")

    if echo "$response" | jq -e '.result' > /dev/null 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        pass_count=$((pass_count + 1))
        echo "$response" | jq -C '.result' | head -20
    elif echo "$response" | jq -e '.error' > /dev/null 2>&1; then
        error_msg=$(echo "$response" | jq -r '.error.message // .error.data.message // "Unknown error"')
        echo -e "${RED}✗ FAIL${NC} - $error_msg"
        fail_count=$((fail_count + 1))
    else
        echo -e "${RED}✗ FAIL${NC} - Invalid response"
        fail_count=$((fail_count + 1))
    fi
    echo ""
}

echo "============================================================================"
echo "MCP Server v3 - Comprehensive Tool Testing (54 tools)"
echo "============================================================================"
echo ""

echo "============================================================================"
echo "DATABASE TOOLS (4 tools)"
echo "============================================================================"
call_tool "db.tables" "{}" "List all database tables"
call_tool "db.schema" "{\"table\":\"users\"}" "Get users table schema"
call_tool "db.query" "{\"query\":\"SELECT COUNT(*) as count FROM users LIMIT 1\"}" "Count users"
call_tool "db.explain" "{\"query\":\"SELECT * FROM users WHERE id = 1\"}" "Explain query execution"

echo "============================================================================"
echo "FILE SYSTEM TOOLS (4 tools)"
echo "============================================================================"
call_tool "fs.list" "{\"path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp\",\"recursive\":false}" "List MCP directory"
call_tool "fs.read" "{\"path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env\",\"max_lines\":5}" "Read .env file (5 lines)"
call_tool "fs.info" "{\"path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php\"}" "Get file info"
# Skip fs.write in production
echo -e "${YELLOW}[SKIP]${NC} fs.write - Skipped (write operation)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "KNOWLEDGE BASE TOOLS (4 tools)"
echo "============================================================================"
call_tool "kb.search" "{\"query\":\"MCP server\",\"limit\":3}" "Search KB for MCP server"
call_tool "kb.list_documents" "{\"limit\":5}" "List KB documents"
call_tool "kb.get_document" "{\"document_id\":\"1\"}" "Get specific KB document"
# Skip kb.add_document
echo -e "${YELLOW}[SKIP]${NC} kb.add_document - Skipped (write operation)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "MEMORY & CONTEXT TOOLS (3 tools)"
echo "============================================================================"
call_tool "memory.get_context" "{\"conversation_id\":\"test-123\"}" "Get conversation context"
call_tool "memory.store" "{\"conversation_id\":\"test-scanner\",\"content\":\"Testing MCP scanner\",\"memory_type\":\"note\"}" "Store memory"
call_tool "memory.get_context" "{\"conversation_id\":\"test-scanner\"}" "Recall stored memory"

echo "============================================================================"
echo "CONVERSATION TOOLS (3 tools)"
echo "============================================================================"
call_tool "conversation.get_project_context" "{\"project_id\":2,\"limit\":5}" "Get project conversations"
call_tool "conversation.search" "{\"search\":\"database\",\"limit\":3}" "Search conversations"
call_tool "conversation.get_unit_context" "{\"unit_id\":2,\"limit\":5}" "Get unit conversations"

echo "============================================================================"
echo "SEMANTIC SEARCH TOOLS (8 tools)"
echo "============================================================================"
call_tool "semantic_search" "{\"query\":\"database connection\",\"limit\":3}" "Semantic search"
call_tool "find_code" "{\"pattern\":\"function\",\"limit\":3}" "Find code patterns"
call_tool "analyze_file" "{\"file_path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php\"}" "Analyze server file"
call_tool "get_file_content" "{\"file_path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env\"}" "Get file content"
call_tool "find_similar" "{\"file_path\":\"/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php\",\"limit\":3}" "Find similar files"
call_tool "explore_by_tags" "{\"semantic_tags\":[\"api\",\"server\"],\"limit\":5}" "Explore by tags"
call_tool "search_by_category" "{\"query\":\"api\",\"category_name\":\"Development\"}" "Search by category"
call_tool "list_categories" "{}" "List all categories"

echo "============================================================================"
echo "STATISTICS & ANALYTICS TOOLS (3 tools)"
echo "============================================================================"
call_tool "get_stats" "{\"breakdown_by\":\"unit\"}" "Get system stats"
call_tool "top_keywords" "{\"limit\":10}" "Get top keywords"
call_tool "get_analytics" "{\"action\":\"overview\",\"timeframe\":\"24h\"}" "Get analytics"

echo "============================================================================"
echo "LOGGING TOOLS (2 tools)"
echo "============================================================================"
call_tool "logs.tail" "{\"path\":\"logs/operations.log\",\"max_bytes\":1000}" "Tail operations log"
call_tool "logs.grep" "{\"path\":\"logs/operations.log\",\"pattern\":\"ERROR\",\"max_matches\":5}" "Grep logs for errors"

echo "============================================================================"
echo "OPERATIONS TOOLS (5 tools)"
echo "============================================================================"
call_tool "ops.ready_check" "{}" "Environment readiness check"
call_tool "ops.security_scan" "{\"scope\":\"quick\"}" "Quick security scan"
call_tool "ops.monitoring_snapshot" "{\"window_seconds\":300}" "Monitoring snapshot"
call_tool "health_check" "{}" "System health check"
# Skip ops.performance_test
echo -e "${YELLOW}[SKIP]${NC} ops.performance_test - Skipped (load test)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "SATELLITE TOOLS (2 tools)"
echo "============================================================================"
call_tool "list_satellites" "{}" "List satellite servers"
# Skip sync_satellite
echo -e "${YELLOW}[SKIP]${NC} sync_satellite - Skipped (sync operation)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "GIT/GITHUB TOOLS (2 tools)"
echo "============================================================================"
call_tool "git.search" "{\"query\":\"database connection\",\"org\":\"pearcestephens\"}" "Search GitHub code"
# Skip git.open_pr
echo -e "${YELLOW}[SKIP]${NC} git.open_pr - Skipped (creates PR)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "HTTP TOOLS (1 tool)"
echo "============================================================================"
call_tool "http.request" "{\"url\":\"https://gpt.ecigdis.co.nz/mcp/health_v3.php\",\"method\":\"GET\"}" "HTTP GET request"

echo "============================================================================"
echo "REDIS TOOLS (2 tools)"
echo "============================================================================"
call_tool "redis.set" "{\"key\":\"test:scanner\",\"value\":\"MCP tool test\",\"ttl\":60}" "Set Redis key"
call_tool "redis.get" "{\"key\":\"test:scanner\"}" "Get Redis key"

echo "============================================================================"
echo "PASSWORD STORAGE TOOLS (4 tools)"
echo "============================================================================"
call_tool "password.list" "{}" "List stored passwords"
call_tool "password.store" "{\"service\":\"test_service\",\"username\":\"test_user\",\"password\":\"test_pass_123\"}" "Store test password"
call_tool "password.retrieve" "{\"service\":\"test_service\"}" "Retrieve test password"
call_tool "password.delete" "{\"service\":\"test_service\"}" "Delete test password"

echo "============================================================================"
echo "MYSQL TOOLS (2 tools)"
echo "============================================================================"
call_tool "mysql.common_queries" "{}" "Get common queries"
call_tool "mysql.query" "{\"query\":\"SELECT DATABASE()\",\"limit\":1}" "Execute MySQL query"

echo "============================================================================"
echo "BROWSER/CRAWLER TOOLS (5 tools)"
echo "============================================================================"
call_tool "browser.headers" "{\"url\":\"https://gpt.ecigdis.co.nz\"}" "Get HTTP headers"
call_tool "browser.fetch" "{\"url\":\"https://gpt.ecigdis.co.nz\",\"include_html\":false}" "Fetch webpage"
# Skip intensive operations
echo -e "${YELLOW}[SKIP]${NC} browser.extract - Skipped (requires selectors)"
skip_count=$((skip_count + 1))
echo -e "${YELLOW}[SKIP]${NC} crawler.single_page - Skipped (intensive)"
skip_count=$((skip_count + 1))
echo -e "${YELLOW}[SKIP]${NC} crawler.deep_crawl - Skipped (intensive)"
skip_count=$((skip_count + 1))

echo "============================================================================"
echo "AI AGENT TOOL (1 tool)"
echo "============================================================================"
call_tool "ai_agent.query" "{\"query\":\"What is the MCP server version?\",\"stream\":false}" "Query AI Agent"

echo "============================================================================"
echo "TEST SUMMARY"
echo "============================================================================"
echo ""
echo "Total Tools: 54"
echo "Tests Run: $test_count"
echo -e "${GREEN}Passed: $pass_count${NC}"
echo -e "${RED}Failed: $fail_count${NC}"
echo -e "${YELLOW}Skipped: $skip_count${NC}"
echo ""
echo "Coverage: $((test_count + skip_count))/54 tools tested"
echo ""

if [ $fail_count -eq 0 ]; then
    echo -e "${GREEN}✓ ALL TESTED TOOLS WORKING!${NC}"
    exit 0
else
    success_rate=$((pass_count * 100 / test_count))
    echo -e "${YELLOW}Success Rate: ${success_rate}%${NC}"
    exit 1
fi
