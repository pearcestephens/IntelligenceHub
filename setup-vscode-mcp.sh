#!/bin/bash

# =============================================================================
# Intelligence Hub VS Code Setup Script
# =============================================================================
# This script helps you configure VS Code to use your own Intelligence Hub
# MCP server instead of GitHub Copilot.
# =============================================================================

echo "=========================================="
echo "Intelligence Hub VS Code Setup"
echo "=========================================="
echo ""

# Step 1: Get API KeNBy from server
echo "Step 1: Getting API Key from server..."
API_KEY=$(cat /home/master/applications/hdgwrzntwa/private_html/config/.env | grep MCP_API_KEY | cut -d'=' -f2)

if [ -z "$API_KEY" ]; then
    echo "❌ ERROR: Could not find MCP_API_KEY in .env file"
    exit 1
fi

echo "✅ API Key found: ${API_KEY:0:10}...${API_KEY: -10}"
echo ""

# Step 2: Set environment variable
echo "Step 2: Setting up environment variable..."

# Detect shell
if [ -n "$ZSH_VERSION" ]; then
    SHELL_RC="$HOME/.zshrc"
    SHELL_NAME="zsh"
elif [ -n "$BASH_VERSION" ]; then
    SHELL_RC="$HOME/.bashrc"
    SHELL_NAME="bash"
else
    SHELL_RC="$HOME/.profile"
    SHELL_NAME="sh"
fi

echo "Detected shell: $SHELL_NAME"
echo "Config file: $SHELL_RC"

# Check if already exists
if grep -q "INTELLIGENCE_HUB_API_KEY" "$SHELL_RC"; then
    echo "⚠️  INTELLIGENCE_HUB_API_KEY already exists in $SHELL_RC"
    read -p "Do you want to update it? (y/n): " UPDATE
    if [ "$UPDATE" = "y" ] || [ "$UPDATE" = "Y" ]; then
        # Remove old line
        sed -i '/INTELLIGENCE_HUB_API_KEY/d' "$SHELL_RC"
        echo "Removed old configuration"
    else
        echo "Skipping environment variable setup"
        ENV_SETUP=false
    fi
fi

if [ "$ENV_SETUP" != "false" ]; then
    # Add new line
    echo "" >> "$SHELL_RC"
    echo "# Ecigdis Intelligence Hub MCP API Key" >> "$SHELL_RC"
    echo "export INTELLIGENCE_HUB_API_KEY=\"$API_KEY\"" >> "$SHELL_RC"
    echo "✅ Added INTELLIGENCE_HUB_API_KEY to $SHELL_RC"
fi

# Export for current session
export INTELLIGENCE_HUB_API_KEY="$API_KEY"
echo "✅ Environment variable set for current session"
echo ""

# Step 3: Verify VS Code config files exist
echo "Step 3: Verifying VS Code configuration files..."

PROJECT_ROOT="/home/master/applications/hdgwrzntwa/public_html"
VSCODE_DIR="$PROJECT_ROOT/.vscode"

if [ -f "$VSCODE_DIR/mcp.json" ]; then
    echo "✅ MCP config found: $VSCODE_DIR/mcp.json"
else
    echo "❌ MCP config NOT found: $VSCODE_DIR/mcp.json"
    echo "   Please create this file manually or run setup again"
fi

if [ -f "$VSCODE_DIR/settings.json" ]; then
    echo "✅ VS Code settings found: $VSCODE_DIR/settings.json"
else
    echo "❌ VS Code settings NOT found: $VSCODE_DIR/settings.json"
    echo "   Please create this file manually or run setup again"
fi
echo ""

# Step 4: Test MCP connection
echo "Step 4: Testing MCP server connection..."

RESPONSE=$(curl -s -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}')

if echo "$RESPONSE" | grep -q '"result"'; then
    echo "✅ MCP server connection successful!"
    TOOL_COUNT=$(echo "$RESPONSE" | grep -o '"name"' | wc -l)
    echo "   Found $TOOL_COUNT tools available"
else
    echo "❌ MCP server connection failed"
    echo "   Response: $RESPONSE"
fi
echo ""

# Step 5: Test chat endpoint
echo "Step 5: Testing chat endpoint..."

CHAT_RESPONSE=$(curl -s -X POST https://gpt.ecigdis.co.nz/api/chat.php \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"provider":"openai","model":"gpt-4o-mini","session_key":"setup-test","messages":[{"role":"user","content":"Say OK if you are working"}]}')

if echo "$CHAT_RESPONSE" | grep -q '"success":true'; then
    echo "✅ Chat endpoint working!"
    CONTENT=$(echo "$CHAT_RESPONSE" | grep -o '"content":"[^"]*"' | head -1 | cut -d'"' -f4)
    echo "   AI response: $CONTENT"
else
    echo "❌ Chat endpoint failed"
    echo "   Response: $CHAT_RESPONSE"
fi
echo ""

# Step 6: Instructions
echo "=========================================="
echo "Setup Complete! Next Steps:"
echo "=========================================="
echo ""
echo "1. Reload your shell configuration:"
echo "   source $SHELL_RC"
echo ""
echo "2. Verify environment variable:"
echo "   echo \$INTELLIGENCE_HUB_API_KEY"
echo ""
echo "3. Restart VS Code to load new configuration:"
echo "   - Close all VS Code windows"
echo "   - Open terminal and run: code $PROJECT_ROOT"
echo ""
echo "4. Verify MCP connection in VS Code:"
echo "   - Open Command Palette (Ctrl+Shift+P)"
echo "   - Type: 'MCP: Show Connected Servers'"
echo "   - Should see 'intelligence-hub' connected"
echo ""
echo "5. OPTIONAL: Disable GitHub Copilot extension"
echo "   - Go to Extensions (Ctrl+Shift+X)"
echo "   - Search 'GitHub Copilot'"
echo "   - Click 'Disable' or 'Uninstall'"
echo ""
echo "=========================================="
echo "Configuration Files Created:"
echo "=========================================="
echo "✅ $VSCODE_DIR/mcp.json"
echo "✅ $VSCODE_DIR/settings.json"
echo "✅ Environment variable in $SHELL_RC"
echo ""
echo "=========================================="
echo "Your Intelligence Hub is Ready! 🎉"
echo "=========================================="
echo ""
echo "You can now use your own AI agent instead of GitHub Copilot!"
echo "All data stays on your server: gpt.ecigdis.co.nz"
echo ""
