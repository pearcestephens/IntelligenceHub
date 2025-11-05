#!/bin/bash

echo "🔧 MCP Configuration Test & Verification"
echo "========================================"
echo ""

# Test 1: Check files exist
echo "[1/6] Checking config files..."
if [ -f "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/config/mcp-config.json" ]; then
    echo "✅ mcp-config.json found"
else
    echo "❌ mcp-config.json NOT found"
fi

if [ -f "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/.vscode/settings.json" ]; then
    echo "✅ .vscode/settings.json found"
else
    echo "❌ .vscode/settings.json NOT found"
fi

if [ -f "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-server-wrapper.js" ]; then
    echo "✅ mcp-server-wrapper.js found"
    chmod +x "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-server-wrapper.js"
else
    echo "❌ mcp-server-wrapper.js NOT found"
fi

echo ""

# Test 2: Validate JSON syntax
echo "[2/6] Validating JSON syntax..."
if command -v node &> /dev/null; then
    if node -e "JSON.parse(require('fs').readFileSync('/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/config/mcp-config.json', 'utf8'))" 2>&1; then
        echo "✅ mcp-config.json is valid JSON"
    else
        echo "❌ mcp-config.json has JSON syntax errors"
    fi
else
    echo "⚠ Node.js not available for JSON validation"
fi

echo ""

# Test 3: Test wrapper initialization
echo "[3/6] Testing MCP wrapper initialization..."
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp
export MCP_SERVER_URL="https://gpt.ecigdis.co.nz/mcp/server_v3.php"
export MCP_API_KEY="31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35"
export PROJECT_ID="2"
export BUSINESS_UNIT_ID="2"
export ENABLE_CONVERSATION_CONTEXT="true"
export AUTO_RETRIEVE_CONTEXT="true"
export CONTEXT_LIMIT="10"

echo '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' | node mcp-server-wrapper.js 2>&1 | head -10

echo ""

# Test 4: Test tools/list
echo "[4/6] Testing tools/list..."
echo '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":2}' | node mcp-server-wrapper.js 2>&1 | head -20

echo ""

# Test 5: Test semantic search
echo "[5/6] Testing semantic_search tool..."
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"semantic_search","arguments":{"query":"MCP server","limit":3}},"id":3}' | node mcp-server-wrapper.js 2>&1 | head -30

echo ""

# Test 6: Test conversation context retrieval
echo "[6/6] Testing conversation.get_project_context..."
echo '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"conversation.get_project_context","arguments":{"project_id":2,"limit":5}},"id":4}' | node mcp-server-wrapper.js 2>&1 | head -30

echo ""
echo "========================================"
echo "✅ Test Complete!"
echo ""
echo "📝 Configuration Locations:"
echo "   - MCP Config: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/config/mcp-config.json"
echo "   - VS Code Settings: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/.vscode/settings.json"
echo "   - Wrapper: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-server-wrapper.js"
echo ""
echo "🚀 Next Steps:"
echo "   1. Restart VS Code to load new MCP settings"
echo "   2. Check GitHub Copilot status bar for 'intelligence-hub' connection"
echo "   3. Try asking Copilot a question - it will use the MCP tools automatically"
echo ""
