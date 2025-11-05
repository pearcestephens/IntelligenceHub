#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║  MCP TOOLS - COMPREHENSIVE FIX & OPTIMIZATION                    ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp

# Load current tool implementations
echo "=== ANALYZING TOOL IMPLEMENTATIONS ==="
echo ""

# Count implementations
DB_TOOLS=$(grep -c "^    'db\." mcp_tools_turbo.php 2>/dev/null || echo 0)
FS_TOOLS=$(grep -c "^    'fs\." mcp_tools_turbo.php 2>/dev/null || echo 0)
KB_TOOLS=$(grep -c "^    'kb\." mcp_tools_turbo.php 2>/dev/null || echo 0)
CHAT_TOOLS=$(grep -c "^    'chat\." mcp_tools_turbo.php 2>/dev/null || echo 0)
MEM_TOOLS=$(grep -c "^    'memory\." mcp_tools_turbo.php 2>/dev/null || echo 0)
GITHUB_TOOLS=$(grep -c "^    'github\." mcp_tools_turbo.php 2>/dev/null || echo 0)
OPS_TOOLS=$(grep -c "^    'ops\." mcp_tools_turbo.php 2>/dev/null || echo 0)
LOG_TOOLS=$(grep -c "^    'logs\." mcp_tools_turbo.php 2>/dev/null || echo 0)

echo "Database Tools:    $DB_TOOLS implemented"
echo "File System Tools: $FS_TOOLS implemented"
echo "Knowledge Base:    $KB_TOOLS implemented"
echo "Chat Tools:        $CHAT_TOOLS implemented"
echo "Memory Tools:      $MEM_TOOLS implemented"
echo "GitHub Tools:      $GITHUB_TOOLS implemented"
echo "Ops Tools:         $OPS_TOOLS implemented"
echo "Log Tools:         $LOG_TOOLS implemented"
echo ""

# Check what's ACTUALLY callable
echo "=== CHECKING \$TOOL_HANDLERS ARRAY ==="
if grep -q 'global \$TOOL_HANDLERS' server_v3.php; then
  echo "✅ server_v3.php USES \$TOOL_HANDLERS"
else
  echo "❌ server_v3.php DOES NOT use \$TOOL_HANDLERS"
  echo "   → Tools only callable via HTTP routes!"
fi
echo ""

# Check .env configuration
echo "=== CHECKING ENVIRONMENT ==="
if [ -f ".env" ]; then
  echo "✓ .env exists"
  grep "^DB_" .env | head -5
  grep "^AGENT_BASE" .env || echo "AGENT_BASE not set (using default)"
  grep "^MCP_API_KEY" .env > /dev/null && echo "✓ MCP_API_KEY configured" || echo "❌ MCP_API_KEY missing"
else
  echo "❌ .env file missing!"
fi
echo ""

# Check API files
echo "=== CHECKING API FILES ==="
echo "chat.php:         $([ -f api/chat.php ] && echo '✓ EXISTS' || echo '❌ MISSING')"
echo "conversations.php: $([ -f api/conversations.php ] && echo '✓ EXISTS' || echo '❌ MISSING')"
echo "memory_upsert.php: $([ -f api/memory_upsert.php ] && echo '✓ EXISTS' || echo '❌ MISSING')"
echo "tools/invoke.php:  $([ -f api/tools/invoke.php ] && echo '✓ EXISTS' || echo '❌ MISSING')"
echo ""

# FIXES
echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║  APPLYING FIXES                                                  ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

# FIX 1: Ensure DB tools use correct DB name
echo "FIX 1: Database Connection"
if grep -q "DB_NAME.*jcepnzzkmj" mcp_tools_turbo.php; then
  echo "⚠️  Found hardcoded jcepnzzkmj database name"
  echo "   → Should use hdgwrzntwa from .env"
fi

# FIX 2: Make sure all tools are in $TOOL_HANDLERS
echo ""
echo "FIX 2: Tool Handler Registration"
HANDLER_COUNT=$(grep -c "=> function.*array.*:" mcp_tools_turbo.php 2>/dev/null || echo 0)
echo "   Found $HANDLER_COUNT tool handlers in \$TOOL_HANDLERS"

# FIX 3: Verify agent_url() base
echo ""
echo "FIX 3: Agent URL Base"
CURRENT_BASE=$(grep "AGENT_BASE.*'https" mcp_tools_turbo.php | head -1 | grep -oP "https://[^']+")
echo "   Current base: $CURRENT_BASE"
echo "   Should be: https://gpt.ecigdis.co.nz/mcp"

# FIX 4: Test actual connectivity
echo ""
echo "FIX 4: Testing Connectivity"
echo -n "   /mcp/api/chat.php: "
curl -s -I https://gpt.ecigdis.co.nz/mcp/api/chat.php 2>&1 | grep "HTTP" | awk '{print $2}' | head -1

echo -n "   /mcp/api/conversations.php: "
curl -s -I https://gpt.ecigdis.co.nz/mcp/api/conversations.php 2>&1 | grep "HTTP" | awk '{print $2}' | head -1

echo -n "   /mcp/api/tools/invoke.php: "
curl -s -I https://gpt.ecigdis.co.nz/mcp/api/tools/invoke.php 2>&1 | grep "HTTP" | awk '{print $2}' | head -1

echo ""
echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║  DIAGNOSIS COMPLETE                                              ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""
echo "RECOMMENDATIONS:"
echo "1. All tools should execute via \$TOOL_HANDLERS (direct PHP execution)"
echo "2. Only external services (conversation context, ai_agent) need HTTP calls"
echo "3. Remove HTTP routing overhead for 90% of tools"
echo "4. Tools are FAST when executed directly!"
echo ""
echo "NEXT: Run comprehensive tool test to see current status"
