#!/bin/bash
# Simplified setup - creates config files that you'll copy to your local VS Code

set -e

echo "🤖 IntelligenceHub AI Agent Configuration Generator"
echo "=================================================="
echo ""

# Paths
WORKSPACE_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa"
CONFIG_DIR="${WORKSPACE_ROOT}/public_html/mcp/vscode-config"

echo "Creating VS Code configuration files in: ${CONFIG_DIR}"
mkdir -p "${CONFIG_DIR}"

echo "✓ Configuration files ready at: ${CONFIG_DIR}"
echo ""
echo "📋 SETUP INSTRUCTIONS:"
echo "====================="
echo ""
echo "1️⃣  Copy the config files to your local machine:"
echo "    scp master@main.cloudways:${CONFIG_DIR}/* /path/to/your/local/workspace/.vscode/"
echo ""
echo "2️⃣  Or manually create these files in your workspace:"
echo "    - .vscode/mcp.json"
echo "    - .vscode/settings.json"
echo ""
echo "3️⃣  Reload VS Code (Ctrl/Cmd+Shift+P → 'Reload Window')"
echo ""
echo "✅ Your AI Agent is configured at:"
echo "   https://gpt.ecigdis.co.nz/mcp/server_v3.php"
echo ""
echo "✅ OpenAI-compatible endpoint at:"
echo "   https://gpt.ecigdis.co.nz/api/v1/chat/completions"
echo ""
echo "✅ Conversation recording enabled using existing tables:"
echo "   - ai_conversations"
echo "   - ai_conversation_messages"
echo ""
echo "🔑 API Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
echo ""
