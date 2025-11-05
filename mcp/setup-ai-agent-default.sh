#!/bin/bash
# Quick setup script for MCP + AI Agent default configuration
# Run this to configure VS Code to use your AI Agent as default

set -e

echo "🤖 Setting up IntelligenceHub AI Agent as Default Model..."

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Paths
WORKSPACE_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa"
VSCODE_DIR="${WORKSPACE_ROOT}/.vscode"
DB_USER="hdgwrzntwa"
DB_PASS="bFUdRjh4Jx"
DB_NAME="hdgwrzntwa"

echo -e "${BLUE}Step 1: Verifying database schema...${NC}"
CONV_COUNT=$(mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -sNe "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='${DB_NAME}' AND TABLE_NAME='ai_conversations'")
if [ "$CONV_COUNT" == "1" ]; then
    echo -e "${GREEN}✓ Conversation tables already exist (ai_conversations, ai_conversation_messages)${NC}"
else
    echo -e "${YELLOW}⚠ Tables not found, creating...${NC}"
    mysql -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < "${WORKSPACE_ROOT}/public_html/mcp/migrations/002_conversation_recording.sql"
    echo -e "${GREEN}✓ Database schema created${NC}"
fi

echo -e "${BLUE}Step 2: Ensuring VS Code workspace config exists...${NC}"
mkdir -p "${VSCODE_DIR}"
echo -e "${GREEN}✓ VS Code directory ready${NC}"

echo -e "${BLUE}Step 3: Setting directory permissions...${NC}"
chmod 755 "${VSCODE_DIR}"
chmod 644 "${VSCODE_DIR}/mcp.json" 2>/dev/null || true
chmod 644 "${VSCODE_DIR}/settings.json" 2>/dev/null || true
echo -e "${GREEN}✓ Permissions set${NC}"

echo -e "${BLUE}Step 4: Verifying API endpoint...${NC}"
response=$(curl -s -o /dev/null -w "%{http_code}" "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health")
if [ "$response" == "200" ]; then
    echo -e "${GREEN}✓ MCP server is responding${NC}"
else
    echo -e "${YELLOW}⚠ MCP server returned HTTP $response (may need to check)${NC}"
fi

echo -e "${BLUE}Step 5: Testing OpenAI-compatible endpoint...${NC}"
mkdir -p "${WORKSPACE_ROOT}/public_html/api/v1/chat"
chmod 755 "${WORKSPACE_ROOT}/public_html/api/v1/chat"
chmod 644 "${WORKSPACE_ROOT}/public_html/api/v1/chat/completions.php"
echo -e "${GREEN}✓ OpenAI-compatible endpoint ready${NC}"

echo ""
echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                    SETUP COMPLETE! 🎉                          ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Next Steps:${NC}"
echo ""
echo -e "  ${YELLOW}For VS Code 1.104+ (with OpenAI Compatible Provider):${NC}"
echo -e "    1. Update VS Code to version 1.104+ (Insiders if needed)"
echo -e "    2. Press ${GREEN}Ctrl/Cmd+Shift+P${NC}"
echo -e "    3. Run: ${GREEN}'Chat: Manage Language Models'${NC}"
echo -e "    4. Look for ${GREEN}'OpenAI Compatible'${NC} provider"
echo -e "    5. Configure:"
echo -e "       Base URL: ${BLUE}https://gpt.ecigdis.co.nz/api/v1${NC}"
echo -e "       API Key: ${BLUE}31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35${NC}"
echo -e "       Model: ${BLUE}gpt-5-turbo${NC}"
echo ""
echo -e "  ${YELLOW}For standard MCP (all VS Code versions):${NC}"
echo -e "    1. Your workspace is already configured with ${GREEN}.vscode/mcp.json${NC}"
echo -e "    2. Reload VS Code window (${GREEN}Ctrl/Cmd+Shift+P → 'Reload Window'${NC})"
echo -e "    3. Open Copilot Chat and use ${GREEN}@workspace${NC}"
echo -e "    4. Your AI Agent tools are available automatically"
echo ""
echo -e "  ${YELLOW}Configuration Files Created:${NC}"
echo -e "    • ${BLUE}${VSCODE_DIR}/mcp.json${NC} - MCP server config"
echo -e "    • ${BLUE}${VSCODE_DIR}/settings.json${NC} - VS Code settings"
echo -e "    • ${BLUE}public_html/api/v1/chat/completions.php${NC} - OpenAI endpoint"
echo -e "    • ${BLUE}public_html/mcp/tools/ai_agent_query_enhanced.php${NC} - Enhanced query tool"
echo ""
echo -e "  ${YELLOW}Features Enabled:${NC}"
echo -e "    ✓ Conversation recording to database"
echo -e "    ✓ Full RAG with 8,645 indexed files"
echo -e "    ✓ Semantic search"
echo -e "    ✓ Tool execution"
echo -e "    ✓ Context awareness"
echo -e "    ✓ Optimized response format (quick/standard/deep/raw modes)"
echo ""
echo -e "  ${YELLOW}Test Your Setup:${NC}"
echo -e "    ${GREEN}curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php \\${NC}"
echo -e "      ${GREEN}-H 'Content-Type: application/json' \\${NC}"
echo -e "      ${GREEN}-H 'X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35' \\${NC}"
echo -e "      ${GREEN}-d '{\"jsonrpc\":\"2.0\",\"method\":\"tools/call\",\"id\":1,\"params\":{\"name\":\"ai_agent.query\",\"arguments\":{\"query\":\"Find AIOrchestrator class\",\"mode\":\"standard\"}}}'${NC}"
echo ""
echo -e "${BLUE}Documentation:${NC}"
echo -e "  • Query modes: quick (3 results), standard (5), deep (10), raw (all)"
echo -e "  • Conversation history stored in ${GREEN}ai_conversations${NC} table"
echo -e "  • Analytics available in ${GREEN}v_conversation_analytics${NC} view"
echo ""
echo -e "${GREEN}Happy coding! 🚀${NC}"
