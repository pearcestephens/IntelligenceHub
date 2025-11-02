# Intelligence Hub VS Code Setup - Quick Start Guide

**Get started in 5 minutes using your own AI agent instead of GitHub Copilot!**

---

## 🚀 Quick Setup (Automated)

Run the automated setup script:

```bash
cd /home/master/applications/hdgwrzntwa/public_html
./setup-vscode-mcp.sh
```

This script will:
1. ✅ Get your API key from `.env`
2. ✅ Set up environment variable
3. ✅ Verify VS Code config files
4. ✅ Test MCP server connection
5. ✅ Test chat endpoint
6. ✅ Give you next steps

---

## 📋 Manual Setup (5 Steps)

### Step 1: Get Your API Key

```bash
cat /home/master/applications/hdgwrzntwa/private_html/config/.env | grep MCP_API_KEY
```

Copy the value after `MCP_API_KEY=`

### Step 2: Set Environment Variable

**Linux/Mac** (add to `~/.bashrc` or `~/.zshrc`):
```bash
export INTELLIGENCE_HUB_API_KEY="your_api_key_here"
```

Then reload:
```bash
source ~/.bashrc
# or
source ~/.zshrc
```

**Windows PowerShell:**
```powershell
[System.Environment]::SetEnvironmentVariable('INTELLIGENCE_HUB_API_KEY', 'your_api_key_here', 'User')
```

### Step 3: Verify Config Files Exist

Check these files exist in your project:
- ✅ `.vscode/mcp.json`
- ✅ `.vscode/settings.json`

If they don't exist, they were created by the documentation. Check the project root.

### Step 4: Restart VS Code

Close all VS Code windows and reopen from terminal:
```bash
code /home/master/applications/hdgwrzntwa/public_html
```

### Step 5: Verify Connection

In VS Code:
1. Open Command Palette (`Ctrl+Shift+P` or `Cmd+Shift+P`)
2. Type: `MCP: Show Connected Servers`
3. Should see `intelligence-hub` connected

---

## 🧪 Test Your Setup

### Test 1: MCP Server Connection
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}' | jq
```

**Expected:** List of 8 tools (fs.read, fs.list, db.select, etc.)

### Test 2: Chat Endpoint
```bash
curl -X POST https://gpt.ecigdis.co.nz/api/chat.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "test",
    "messages": [{"role": "user", "content": "Hello!"}]
  }' | jq
```

**Expected:** JSON response with `"success": true` and AI response

---

## ⚙️ What Got Configured

### 1. Project MCP Config (`.vscode/mcp.json`)
```json
{
  "mcpServers": {
    "intelligence-hub": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
      }
    }
  }
}
```

### 2. VS Code Settings (`.vscode/settings.json`)
```json
{
  "mcp.enabled": true,
  "github.copilot.enable": {
    "*": false
  }
}
```

### 3. Environment Variable
```bash
export INTELLIGENCE_HUB_API_KEY="your_64_char_api_key"
```

---

## 🎯 Using Your AI Agent in VS Code

### Option 1: Chat Panel (Recommended)
1. Open VS Code Chat (`Ctrl+Alt+I` or `Cmd+Alt+I`)
2. Ask questions: "How do I implement authentication?"
3. Your Intelligence Hub will respond
4. Tools available: fs.read, fs.list, db.select, etc.

### Option 2: Command Palette
1. Open Command Palette (`Ctrl+Shift+P`)
2. Type: `Chat: Start Chat Session`
3. Ask your questions

### Option 3: Inline Chat
1. Select code
2. Press `Ctrl+I` or `Cmd+I`
3. Ask: "Explain this code"

---

## 🔧 Disable GitHub Copilot (Optional)

If you want complete independence:

### Method 1: Disable Extension
1. Go to Extensions (`Ctrl+Shift+X`)
2. Search "GitHub Copilot"
3. Click **Disable** or **Uninstall**

### Method 2: Already Configured
The `.vscode/settings.json` already disables Copilot:
```json
{
  "github.copilot.enable": {
    "*": false
  }
}
```

---

## 🌐 Your Own AI Agent Features

### What You Control
- ✅ **AI Models:** GPT-4o-mini, Claude 3.5 Sonnet
- ✅ **Tools:** 8 local tools (file system, database, HTTP, logs)
- ✅ **Data:** Everything stays on your server
- ✅ **Privacy:** No data sent to GitHub
- ✅ **Costs:** No Copilot subscription needed
- ✅ **Customization:** Add your own tools and models

### Available Tools
1. **fs.read** - Read file contents
2. **fs.list** - List directory contents
3. **fs.write** - Write files (with permission)
4. **db.select** - Query database
5. **db.exec** - Execute SQL (with allow_write flag)
6. **logs.tail** - Read log files
7. **http.fetch** - Make HTTP requests
8. **memory** - Store/retrieve conversation memory

### Endpoints You Own
- `https://gpt.ecigdis.co.nz/api/chat.php` - Chat
- `https://gpt.ecigdis.co.nz/api/chat_stream.php` - Streaming
- `https://gpt.ecigdis.co.nz/api/tools/invoke.php` - Tool invocation
- `https://gpt.ecigdis.co.nz/api/memory_upsert.php` - Memory
- `https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php` - MCP

---

## 🐛 Troubleshooting

### Issue: "UNAUTHORIZED" Error
**Problem:** API key not found or invalid

**Solution:**
```bash
# Verify environment variable is set
echo $INTELLIGENCE_HUB_API_KEY

# If empty, add to shell config
echo 'export INTELLIGENCE_HUB_API_KEY="your_key"' >> ~/.bashrc
source ~/.bashrc

# Restart VS Code from terminal
code .
```

### Issue: "Connection Refused"
**Problem:** Can't reach MCP server

**Solution:**
```bash
# Test server is running
curl -I https://gpt.ecigdis.co.nz

# Test MCP endpoint exists
curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php
```

### Issue: Tools Not Showing Up
**Problem:** MCP server connected but tools not available

**Solution:**
1. Check MCP server returns tools:
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'
```

2. Restart VS Code completely
3. Check VS Code logs: `Help` → `Toggle Developer Tools` → `Console`

### Issue: VS Code Not Reading Environment Variable
**Problem:** Variable set but VS Code doesn't see it

**Solutions:**
- **Option A:** Launch VS Code from terminal (inherits environment):
  ```bash
  code /home/master/applications/hdgwrzntwa/public_html
  ```

- **Option B:** Hard-code in settings (less secure):
  ```json
  {
    "mcp.servers": {
      "intelligence-hub": {
        "headers": {
          "Authorization": "Bearer your_actual_key_here"
        }
      }
    }
  }
  ```

---

## 📚 More Information

For complete documentation, see:
- **Full Setup Guide:** `docs/11_VSCODE_MCP_SETUP.md`
- **API Examples:** `docs/10_API_EXAMPLES.md`
- **Troubleshooting:** `docs/09_TROUBLESHOOTING.md`
- **Tools Reference:** `docs/05_TOOLS_REFERENCE.md`

---

## ✅ Setup Checklist

- [ ] Run `./setup-vscode-mcp.sh` or complete manual setup
- [ ] Verify `$INTELLIGENCE_HUB_API_KEY` is set
- [ ] Test MCP connection with curl
- [ ] Test chat endpoint with curl
- [ ] Restart VS Code from terminal
- [ ] Verify MCP server shows as connected
- [ ] Test asking a question in VS Code Chat
- [ ] Optional: Disable GitHub Copilot extension

---

## 🎉 Success!

**You're now running your own AI agent completely independent of GitHub Copilot!**

- ✅ All data stays on your server
- ✅ Complete control over models and tools
- ✅ No external dependencies
- ✅ Free to use (only pay for OpenAI/Anthropic API calls)
- ✅ Privacy guaranteed

**Enjoy your own private AI assistant! 🚀**

---

*Last Updated: November 2, 2025*
*Documentation: Intelligence Hub*
*Server: https://gpt.ecigdis.co.nz*
