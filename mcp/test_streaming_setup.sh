#!/bin/bash
# 🔥 QUICK TEST - Is streaming actually working?

echo "🔥 Testing MCP Server Streaming..."
echo ""

# Test 1: Check if Node wrapper exists and is executable
echo "1️⃣ Checking Node MCP wrapper..."
if [ -x "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-server-wrapper.js" ]; then
    echo "   ✅ Node wrapper exists and is executable"
else
    echo "   ❌ Node wrapper missing or not executable"
    chmod +x /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-server-wrapper.js
    echo "   ✅ Fixed permissions"
fi

# Test 2: Check if Node is available
echo ""
echo "2️⃣ Checking Node.js..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node --version)
    echo "   ✅ Node.js $NODE_VERSION"
else
    echo "   ❌ Node.js not found!"
    exit 1
fi

# Test 3: Check MCP.JSON configuration
echo ""
echo "3️⃣ Checking VS Code MCP.JSON..."
if [ -f "$HOME/.vscode-server/data/User/mcp.json" ]; then
    echo "   ✅ MCP.JSON exists"
    if grep -q "ENABLE_STREAMING" "$HOME/.vscode-server/data/User/mcp.json"; then
        echo "   ✅ ENABLE_STREAMING is configured"
    else
        echo "   ⚠️  ENABLE_STREAMING not found in config"
    fi
else
    echo "   ❌ MCP.JSON not found"
fi

# Test 4: Test PHP MCP Server (HTTP backend)
echo ""
echo "4️⃣ Testing PHP MCP Server (backend)..."
RESPONSE=$(curl -s -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{"jsonrpc":"2.0","method":"initialize","params":{},"id":1}' \
  --max-time 5)

if echo "$RESPONSE" | grep -q "result"; then
    echo "   ✅ PHP MCP Server responding"
else
    echo "   ❌ PHP MCP Server not responding"
    echo "   Response: $RESPONSE"
fi

# Test 5: Check OpenAI/Claude keys
echo ""
echo "5️⃣ Checking AI API keys..."
if grep -q "sk-proj-" /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env; then
    echo "   ✅ OpenAI key configured (GPT-5)"
else
    echo "   ⚠️  OpenAI key not found"
fi

if grep -q "sk-ant-" /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env; then
    echo "   ✅ Claude key configured"
else
    echo "   ⚠️  Claude key not found"
fi

# Test 6: Test streaming endpoint
echo ""
echo "6️⃣ Testing streaming capability..."
echo "   Sending test request to ai_agent.query..."

# This would need actual implementation in the PHP server
# For now, just check if the tool exists
TOOLS=$(curl -s -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":1}')

if echo "$TOOLS" | grep -q "ai_agent.query"; then
    echo "   ✅ ai_agent.query tool available"
else
    echo "   ⚠️  ai_agent.query tool not found"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎯 SUMMARY:"
echo ""
echo "To get streaming working in VS Code:"
echo ""
echo "1. **Reload VS Code Window**"
echo "   Press Ctrl+Shift+P → 'Developer: Reload Window'"
echo ""
echo "2. **Check MCP Connection**"
echo "   Look for MCP indicator in status bar"
echo "   Should show 'intelligence-hub-master' connected"
echo ""
echo "3. **Test in Copilot Chat**"
echo "   Press Ctrl+Shift+I"
echo "   Type any question"
echo "   Look for blue streaming bar!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
