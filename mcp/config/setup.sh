#!/bin/bash
# MCP Configuration Setup Script
# Ecigdis Limited - Intelligence Hub MCP Server

set -e

echo "=========================================="
echo "  MCP Server Configuration Setup"
echo "  Intelligence Hub v3.0.0"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running in correct directory
if [ ! -f "mcp.json.example" ]; then
    echo -e "${RED}Error: mcp.json.example not found${NC}"
    echo "Please run this script from the mcp/config directory"
    exit 1
fi

# 1. Setup workspace .vscode folder
echo -e "${YELLOW}[1/4] Setting up workspace MCP configuration...${NC}"
WORKSPACE_DIR="../../.vscode"
mkdir -p "$WORKSPACE_DIR"
cp mcp.json.example "$WORKSPACE_DIR/mcp.json"
echo -e "${GREEN}✓ Created $WORKSPACE_DIR/mcp.json${NC}"

# 2. Setup global user .vscode folder
echo -e "${YELLOW}[2/4] Setting up global MCP configuration...${NC}"
USER_VSCODE_DIR="$HOME/.vscode"
mkdir -p "$USER_VSCODE_DIR"
cp mcp.json.example "$USER_VSCODE_DIR/mcp.json"
echo -e "${GREEN}✓ Created $USER_VSCODE_DIR/mcp.json${NC}"

# 3. Check for API key in environment
echo -e "${YELLOW}[3/4] Checking API key configuration...${NC}"
if [ -z "$INTELLIGENCE_HUB_API_KEY" ]; then
    echo -e "${YELLOW}⚠ INTELLIGENCE_HUB_API_KEY not set in environment${NC}"
    echo ""
    echo "Add this to your ~/.bashrc or ~/.zshrc:"
    echo 'export INTELLIGENCE_HUB_API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"'
    echo ""

    # Offer to add it now
    read -p "Would you like to add it to ~/.bashrc now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo 'export INTELLIGENCE_HUB_API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"' >> ~/.bashrc
        echo -e "${GREEN}✓ Added to ~/.bashrc${NC}"
        echo "Run: source ~/.bashrc"
    fi
else
    echo -e "${GREEN}✓ API key found in environment${NC}"
fi

# 4. Test connection
echo -e "${YELLOW}[4/4] Testing MCP server connection...${NC}"
API_KEY="${INTELLIGENCE_HUB_API_KEY:-31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35}"

RESPONSE=$(curl -s -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}' 2>&1)

if echo "$RESPONSE" | grep -q '"result"'; then
    TOOL_COUNT=$(echo "$RESPONSE" | grep -o '"name"' | wc -l)
    echo -e "${GREEN}✓ MCP server connection successful${NC}"
    echo -e "${GREEN}✓ $TOOL_COUNT tools available${NC}"
else
    echo -e "${RED}✗ MCP server connection failed${NC}"
    echo "Response: $RESPONSE"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Restart VS Code to load the MCP configuration"
echo "2. Open GitHub Copilot Chat"
echo "3. Start using MCP tools with your AI Agent"
echo ""
echo "Documentation: ./README.md"
echo "Dashboard: https://gpt.ecigdis.co.nz/admin/bot-deployment-center.html"
echo ""
