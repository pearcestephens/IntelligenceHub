# VS Code MCP Configuration Guide

**Complete Setup for Using Your Own Intelligence Hub Instead of GitHub Copilot**

---

## Table of Contents

1. [Overview](#overview)
2. [VS Code MCP Configuration](#vs-code-mcp-configuration)
3. [User Settings Configuration](#user-settings-configuration)
4. [Environment Variables Setup](#environment-variables-setup)
5. [Complete Independence from GitHub Copilot](#complete-independence-from-github-copilot)
6. [Testing Your Setup](#testing-your-setup)
7. [Troubleshooting](#troubleshooting)
8. [Advanced Configuration](#advanced-configuration)

---

## Overview

This guide shows you how to configure VS Code to use **your own Intelligence Hub MCP server** instead of GitHub's Copilot service. You'll get:

- ✅ **Complete control** over your AI agent
- ✅ **No external dependencies** on GitHub Copilot
- ✅ **Your own tools** (fs.read, fs.list, db.select, etc.)
- ✅ **Custom models** (GPT-4o-mini, Claude 3.5 Sonnet)
- ✅ **Private data** stays on your server
- ✅ **Free operation** (no GitHub Copilot subscription needed)

### Architecture

```
VS Code Extension
    ↓ (MCP Protocol)
Your Intelligence Hub MCP Server
    ↓ (HTTP/HTTPS)
https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php
    ↓ (Tools & AI)
Your Database + OpenAI/Anthropic APIs
```

---

## VS Code MCP Configuration

### Step 1: Create Project MCP Config

Create `.vscode/mcp.json` in your project root:

```json
{
  "$schema": "https://raw.githubusercontent.com/modelcontextprotocol/servers/main/schema.json",
  "mcpServers": {
    "intelligence-hub": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
      },
      "description": "Ecigdis Intelligence Hub - Complete AI Agent with Tools",
      "capabilities": {
        "tools": true,
        "resources": true,
        "prompts": false
      }
    }
  }
}
```

**File Location:**
```bash
/path/to/your/project/.vscode/mcp.json
```

For this project:
```bash
/home/master/applications/hdgwrzntwa/public_html/.vscode/mcp.json
```

### Step 2: Create Global VS Code MCP Config

Create or edit your global VS Code settings to include MCP:

**Linux/Mac:**
```bash
~/.config/Code/User/settings.json
```

**Windows:**
```
%APPDATA%\Code\User\settings.json
```

Add this configuration:

```json
{
  "mcp.enabled": true,
  "mcp.servers": {
    "intelligence-hub-global": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
      },
      "description": "Ecigdis Intelligence Hub (Global)",
      "autoStart": true
    }
  }
}
```

---

## User Settings Configuration

### Complete VS Code User Settings

Edit your `settings.json` (File → Preferences → Settings → Open Settings (JSON)):

```json
{
  // ============================================================================
  // MCP Configuration - Use Your Own AI Agent
  // ============================================================================

  "mcp.enabled": true,
  "mcp.autoConnect": true,
  "mcp.servers": {
    "intelligence-hub": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
      },
      "description": "Ecigdis Intelligence Hub",
      "autoStart": true,
      "reconnectInterval": 5000,
      "timeout": 30000
    }
  },

  // ============================================================================
  // GitHub Copilot - DISABLED (Using Your Own Agent Instead)
  // ============================================================================

  "github.copilot.enable": {
    "*": false,
    "plaintext": false,
    "markdown": false,
    "scminput": false
  },
  "github.copilot.editor.enableAutoCompletions": false,
  "github.copilot.editor.enableCodeActions": false,

  // ============================================================================
  // Chat Configuration - Use Your MCP Server
  // ============================================================================

  "chat.experimental.mcp.enabled": true,
  "chat.experimental.mcp.servers": ["intelligence-hub"],

  // ============================================================================
  // Editor Configuration
  // ============================================================================

  "editor.inlineSuggest.enabled": true,
  "editor.quickSuggestions": {
    "other": true,
    "comments": false,
    "strings": true
  },

  // ============================================================================
  // Custom AI Agent Settings
  // ============================================================================

  "intelligenceHub.defaultModel": "gpt-4o-mini",
  "intelligenceHub.fallbackModel": "claude-3-5-sonnet-20240620",
  "intelligenceHub.toolsEnabled": true,
  "intelligenceHub.memoryEnabled": true,
  "intelligenceHub.telemetryEnabled": true
}
```

---

## Environment Variables Setup

### Step 1: Set Your API Key

You need to set the `INTELLIGENCE_HUB_API_KEY` environment variable so VS Code can authenticate with your MCP server.

#### Linux/Mac

Add to `~/.bashrc` or `~/.zshrc`:

```bash
# Ecigdis Intelligence Hub MCP API Key
export INTELLIGENCE_HUB_API_KEY="your_mcp_api_key_here"
```

Then reload:
```bash
source ~/.bashrc
# or
source ~/.zshrc
```

#### Windows

**PowerShell:**
```powershell
[System.Environment]::SetEnvironmentVariable('INTELLIGENCE_HUB_API_KEY', 'your_mcp_api_key_here', 'User')
```

**Command Prompt:**
```cmd
setx INTELLIGENCE_HUB_API_KEY "your_mcp_api_key_here"
```

### Step 2: Get Your API Key

Your API key is the `MCP_API_KEY` value from your server's `.env` file:

```bash
# On your server
cat /home/master/applications/hdgwrzntwa/private_html/config/.env | grep MCP_API_KEY
```

**Expected format:**
```
MCP_API_KEY=your_64_character_api_key_here
```

Copy the value after `MCP_API_KEY=` and use it as your `INTELLIGENCE_HUB_API_KEY`.

### Step 3: Verify Environment Variable

**Linux/Mac:**
```bash
echo $INTELLIGENCE_HUB_API_KEY
```

**Windows PowerShell:**
```powershell
$env:INTELLIGENCE_HUB_API_KEY
```

**Windows Command Prompt:**
```cmd
echo %INTELLIGENCE_HUB_API_KEY%
```

---

## Complete Independence from GitHub Copilot

### Option 1: Disable GitHub Copilot Extension

1. Open VS Code
2. Go to **Extensions** (Ctrl+Shift+X or Cmd+Shift+X)
3. Search for "GitHub Copilot"
4. Click **Disable** or **Uninstall**

### Option 2: Keep Extension But Disable Functionality

If you want to keep the extension installed but inactive, use the settings from above:

```json
{
  "github.copilot.enable": {
    "*": false
  },
  "github.copilot.editor.enableAutoCompletions": false
}
```

### Option 3: Use Workspace Settings

Create `.vscode/settings.json` in your project to override global settings:

```json
{
  "github.copilot.enable": {
    "*": false
  },
  "mcp.enabled": true,
  "mcp.servers": {
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

---

## Building Your Own VS Code Extension (Alternative Approach)

If you want **complete control** and don't want to rely on any GitHub infrastructure, you can build your own VS Code extension.

### Architecture

```
Your Custom VS Code Extension
    ↓
Direct HTTP calls to your Intelligence Hub
    ↓
https://gpt.ecigdis.co.nz/api/chat.php
https://gpt.ecigdis.co.nz/api/tools/invoke.php
```

### Extension Package Structure

```
your-ai-extension/
├── package.json
├── src/
│   ├── extension.ts          # Main extension entry point
│   ├── api/
│   │   ├── client.ts          # HTTP client for your API
│   │   ├── chat.ts            # Chat endpoint wrapper
│   │   └── tools.ts           # Tools endpoint wrapper
│   ├── providers/
│   │   ├── completion.ts      # Inline completion provider
│   │   └── chat.ts            # Chat view provider
│   └── commands/
│       ├── askQuestion.ts     # Ask AI command
│       └── explainCode.ts     # Explain code command
└── README.md
```

### Minimal Extension Example

**package.json:**
```json
{
  "name": "intelligence-hub-vscode",
  "displayName": "Intelligence Hub",
  "description": "Your private AI assistant powered by Intelligence Hub",
  "version": "1.0.0",
  "engines": {
    "vscode": "^1.80.0"
  },
  "categories": ["Programming Languages", "Machine Learning"],
  "activationEvents": ["onStartupFinished"],
  "main": "./out/extension.js",
  "contributes": {
    "commands": [
      {
        "command": "intelligenceHub.askQuestion",
        "title": "Intelligence Hub: Ask Question"
      },
      {
        "command": "intelligenceHub.explainCode",
        "title": "Intelligence Hub: Explain Code"
      }
    ],
    "configuration": {
      "title": "Intelligence Hub",
      "properties": {
        "intelligenceHub.apiUrl": {
          "type": "string",
          "default": "https://gpt.ecigdis.co.nz/api",
          "description": "Intelligence Hub API URL"
        },
        "intelligenceHub.apiKey": {
          "type": "string",
          "description": "Intelligence Hub API Key"
        },
        "intelligenceHub.defaultModel": {
          "type": "string",
          "enum": ["gpt-4o-mini", "claude-3-5-sonnet-20240620"],
          "default": "gpt-4o-mini",
          "description": "Default AI model"
        }
      }
    }
  },
  "scripts": {
    "vscode:prepublish": "npm run compile",
    "compile": "tsc -p ./",
    "watch": "tsc -watch -p ./"
  },
  "devDependencies": {
    "@types/node": "^18.x",
    "@types/vscode": "^1.80.0",
    "typescript": "^5.0.0"
  },
  "dependencies": {
    "axios": "^1.6.0"
  }
}
```

**src/extension.ts:**
```typescript
import * as vscode from 'vscode';
import axios from 'axios';

export function activate(context: vscode.ExtensionContext) {
    console.log('Intelligence Hub extension activated');

    // Register Ask Question command
    let askQuestion = vscode.commands.registerCommand('intelligenceHub.askQuestion', async () => {
        const question = await vscode.window.showInputBox({
            prompt: 'Ask Intelligence Hub a question',
            placeHolder: 'What would you like to know?'
        });

        if (!question) return;

        const config = vscode.workspace.getConfiguration('intelligenceHub');
        const apiUrl = config.get<string>('apiUrl');
        const apiKey = config.get<string>('apiKey');
        const model = config.get<string>('defaultModel');

        if (!apiKey) {
            vscode.window.showErrorMessage('Intelligence Hub API key not configured');
            return;
        }

        try {
            const response = await axios.post(`${apiUrl}/chat.php`, {
                provider: model?.startsWith('gpt') ? 'openai' : 'anthropic',
                model: model,
                session_key: generateSessionKey(),
                messages: [
                    { role: 'user', content: question }
                ]
            }, {
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json'
                }
            });

            const answer = response.data.response.content;

            // Show result in output channel
            const output = vscode.window.createOutputChannel('Intelligence Hub');
            output.clear();
            output.appendLine(`Q: ${question}`);
            output.appendLine('');
            output.appendLine(`A: ${answer}`);
            output.show();

        } catch (error: any) {
            vscode.window.showErrorMessage(`Intelligence Hub error: ${error.message}`);
        }
    });

    // Register Explain Code command
    let explainCode = vscode.commands.registerCommand('intelligenceHub.explainCode', async () => {
        const editor = vscode.window.activeTextEditor;
        if (!editor) {
            vscode.window.showErrorMessage('No active editor');
            return;
        }

        const selection = editor.selection;
        const code = editor.document.getText(selection);

        if (!code) {
            vscode.window.showErrorMessage('No code selected');
            return;
        }

        const config = vscode.workspace.getConfiguration('intelligenceHub');
        const apiUrl = config.get<string>('apiUrl');
        const apiKey = config.get<string>('apiKey');
        const model = config.get<string>('defaultModel');

        if (!apiKey) {
            vscode.window.showErrorMessage('Intelligence Hub API key not configured');
            return;
        }

        try {
            const response = await axios.post(`${apiUrl}/chat.php`, {
                provider: model?.startsWith('gpt') ? 'openai' : 'anthropic',
                model: model,
                session_key: generateSessionKey(),
                messages: [
                    {
                        role: 'user',
                        content: `Explain this code:\n\n\`\`\`\n${code}\n\`\`\``
                    }
                ]
            }, {
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json'
                }
            });

            const explanation = response.data.response.content;

            // Show result in webview
            const panel = vscode.window.createWebviewPanel(
                'intelligenceHubExplanation',
                'Code Explanation',
                vscode.ViewColumn.Beside,
                {}
            );

            panel.webview.html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                            padding: 20px;
                            line-height: 1.6;
                        }
                        pre {
                            background: #f5f5f5;
                            padding: 15px;
                            border-radius: 5px;
                            overflow-x: auto;
                        }
                        code {
                            background: #f5f5f5;
                            padding: 2px 5px;
                            border-radius: 3px;
                        }
                    </style>
                </head>
                <body>
                    <h2>Code Explanation</h2>
                    <h3>Original Code:</h3>
                    <pre><code>${escapeHtml(code)}</code></pre>
                    <h3>Explanation:</h3>
                    <div>${formatMarkdown(explanation)}</div>
                </body>
                </html>
            `;

        } catch (error: any) {
            vscode.window.showErrorMessage(`Intelligence Hub error: ${error.message}`);
        }
    });

    context.subscriptions.push(askQuestion, explainCode);
}

function generateSessionKey(): string {
    return `vscode_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}

function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatMarkdown(text: string): string {
    // Basic markdown to HTML conversion
    return text
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/`(.+?)`/g, '<code>$1</code>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
}

export function deactivate() {}
```

**tsconfig.json:**
```json
{
  "compilerOptions": {
    "module": "commonjs",
    "target": "ES2020",
    "outDir": "out",
    "lib": ["ES2020"],
    "sourceMap": true,
    "rootDir": "src",
    "strict": true,
    "esModuleInterop": true
  },
  "exclude": ["node_modules", ".vscode-test"]
}
```

### Building and Installing Your Extension

```bash
# Install dependencies
npm install

# Compile TypeScript
npm run compile

# Package extension (requires vsce)
npm install -g @vscode/vsce
vsce package

# Install in VS Code
code --install-extension intelligence-hub-vscode-1.0.0.vsix
```

---

## Testing Your Setup

### Test 1: Check MCP Connection

1. Open VS Code
2. Open Command Palette (Ctrl+Shift+P or Cmd+Shift+P)
3. Type "MCP: Show Connected Servers"
4. Verify "intelligence-hub" appears

### Test 2: Test API Connection

Create a test script:

```bash
# test-mcp.sh
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/list",
    "id": 1
  }' | jq
```

Run:
```bash
chmod +x test-mcp.sh
./test-mcp.sh
```

**Expected output:**
```json
{
  "jsonrpc": "2.0",
  "result": {
    "tools": [
      {
        "name": "fs.read",
        "description": "Read file contents",
        ...
      },
      {
        "name": "fs.list",
        "description": "List directory contents",
        ...
      },
      ...
    ]
  },
  "id": 1
}
```

### Test 3: Test Tool Invocation

```bash
# test-tool.sh
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/call",
    "params": {
      "name": "fs.list",
      "arguments": {
        "path": ".",
        "max_entries": 10
      }
    },
    "id": 2
  }' | jq
```

### Test 4: Test Chat Endpoint

```bash
# test-chat.sh
curl -X POST https://gpt.ecigdis.co.nz/api/chat.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "test-session",
    "messages": [
      {
        "role": "user",
        "content": "Hello, are you working?"
      }
    ]
  }' | jq
```

**Expected output:**
```json
{
  "success": true,
  "response": {
    "content": "Yes, I'm working! How can I help you?",
    "tokens": {
      "prompt": 12,
      "completion": 10,
      "total": 22
    },
    "latency_ms": 847
  }
}
```

---

## Troubleshooting

### Issue 1: "UNAUTHORIZED" Error

**Symptom:**
```json
{"error": "UNAUTHORIZED", "message": "Missing or invalid API key"}
```

**Solution:**
1. Check environment variable is set:
   ```bash
   echo $INTELLIGENCE_HUB_API_KEY
   ```

2. Verify API key matches `.env` on server:
   ```bash
   cat /home/master/applications/hdgwrzntwa/private_html/config/.env | grep MCP_API_KEY
   ```

3. Restart VS Code after setting environment variable

### Issue 2: "Connection Refused"

**Symptom:**
```
Failed to connect to MCP server
```

**Solution:**
1. Verify server is running:
   ```bash
   curl -I https://gpt.ecigdis.co.nz
   ```

2. Check nginx is running:
   ```bash
   systemctl status nginx
   ```

3. Verify MCP endpoint exists:
   ```bash
   curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php
   ```

### Issue 3: Tools Not Appearing

**Symptom:**
Tools don't show up in VS Code chat

**Solution:**
1. Verify MCP server returns tools:
   ```bash
   curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
     -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
     -H "Content-Type: application/json" \
     -d '{"jsonrpc":"2.0","method":"tools/list","id":1}'
   ```

2. Check VS Code MCP logs:
   - Open Command Palette
   - Type "Developer: Show Logs"
   - Select "Extension Host"
   - Look for MCP-related errors

### Issue 4: VS Code Not Reading Environment Variable

**Symptom:**
VS Code says API key not found even though it's set

**Solution:**
1. **Option A:** Set in VS Code settings directly (less secure):
   ```json
   {
     "mcp.servers": {
       "intelligence-hub": {
         "headers": {
           "Authorization": "Bearer your_actual_api_key_here"
         }
       }
     }
   }
   ```

2. **Option B:** Launch VS Code from terminal (inherits environment):
   ```bash
   # Linux/Mac
   code .

   # Windows
   code.cmd .
   ```

3. **Option C:** Add to VS Code's environment file:

   **Linux/Mac:** `~/.config/Code/User/environment`
   ```
   INTELLIGENCE_HUB_API_KEY=your_api_key_here
   ```

   **Windows:** Create/edit environment via System Properties

---

## Advanced Configuration

### Multiple MCP Servers

You can configure multiple MCP servers for different projects:

```json
{
  "mcp.servers": {
    "intelligence-hub-prod": {
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY_PROD}"
      }
    },
    "intelligence-hub-dev": {
      "url": "https://dev.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY_DEV}"
      }
    }
  }
}
```

### Custom Tool Shortcuts

Add keyboard shortcuts for your tools:

**keybindings.json:**
```json
[
  {
    "key": "ctrl+shift+a",
    "command": "intelligenceHub.askQuestion",
    "when": "editorTextFocus"
  },
  {
    "key": "ctrl+shift+e",
    "command": "intelligenceHub.explainCode",
    "when": "editorTextFocus && editorHasSelection"
  }
]
```

### Inline Completion Provider (Advanced)

For real-time code suggestions like Copilot:

```typescript
// src/providers/completion.ts
import * as vscode from 'vscode';
import axios from 'axios';

export class IntelligenceHubCompletionProvider implements vscode.InlineCompletionItemProvider {
    async provideInlineCompletionItems(
        document: vscode.TextDocument,
        position: vscode.Position,
        context: vscode.InlineCompletionContext,
        token: vscode.CancellationToken
    ): Promise<vscode.InlineCompletionItem[] | undefined> {

        const config = vscode.workspace.getConfiguration('intelligenceHub');
        const apiUrl = config.get<string>('apiUrl');
        const apiKey = config.get<string>('apiKey');

        if (!apiKey) return undefined;

        // Get context (lines before cursor)
        const linesBefore = [];
        for (let i = Math.max(0, position.line - 20); i <= position.line; i++) {
            linesBefore.push(document.lineAt(i).text);
        }
        const prefix = linesBefore.join('\n');

        try {
            const response = await axios.post(`${apiUrl}/chat.php`, {
                provider: 'openai',
                model: 'gpt-4o-mini',
                session_key: `completion_${Date.now()}`,
                messages: [
                    {
                        role: 'system',
                        content: 'You are a code completion assistant. Complete the code snippet. Return only the completion, no explanations.'
                    },
                    {
                        role: 'user',
                        content: `Complete this code:\n\n${prefix}`
                    }
                ]
            }, {
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json'
                },
                timeout: 5000
            });

            const completion = response.data.response.content;

            return [
                new vscode.InlineCompletionItem(
                    completion,
                    new vscode.Range(position, position)
                )
            ];

        } catch (error) {
            console.error('Completion error:', error);
            return undefined;
        }
    }
}

// Register in extension.ts
const completionProvider = vscode.languages.registerInlineCompletionItemProvider(
    { pattern: '**' },
    new IntelligenceHubCompletionProvider()
);
context.subscriptions.push(completionProvider);
```

---

## Summary: Complete Independence Setup

### Quick Start Checklist

- [ ] **Step 1:** Get your MCP API key from server `.env`
- [ ] **Step 2:** Set `INTELLIGENCE_HUB_API_KEY` environment variable
- [ ] **Step 3:** Create `.vscode/mcp.json` in project
- [ ] **Step 4:** Update VS Code `settings.json` with MCP config
- [ ] **Step 5:** Disable GitHub Copilot in settings or extension
- [ ] **Step 6:** Restart VS Code
- [ ] **Step 7:** Test MCP connection with curl
- [ ] **Step 8:** Test chat endpoint
- [ ] **Step 9:** Verify tools list is returned
- [ ] **Step 10:** Use your AI agent in VS Code!

### What You Get

✅ **No GitHub Copilot dependency** - Your own AI agent
✅ **Your own tools** - fs.read, fs.list, db.select, etc.
✅ **Your own models** - GPT-4o-mini, Claude 3.5 Sonnet
✅ **Your own data** - Everything stays on your server
✅ **Complete control** - Customize models, tools, prompts
✅ **Free to use** - No subscription costs
✅ **Privacy** - No data sent to GitHub
✅ **Customizable** - Build your own extension if needed

### API Endpoints You Control

- `https://gpt.ecigdis.co.nz/api/chat.php` - Chat with AI
- `https://gpt.ecigdis.co.nz/api/chat_stream.php` - Streaming responses
- `https://gpt.ecigdis.co.nz/api/tools/invoke.php` - Call tools
- `https://gpt.ecigdis.co.nz/api/memory_upsert.php` - Store/retrieve memory
- `https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php` - MCP protocol

### Next Steps

1. **Test thoroughly** - Use the test scripts above
2. **Build custom extension** - If you want more control
3. **Add more tools** - Extend your MCP server with custom tools
4. **Share with team** - Deploy to your organization

---

**You now have complete independence from GitHub Copilot and full control over your AI agent! 🎉**

---

*Last Updated: November 2, 2025*
*Documentation Version: 1.0*
*System: Intelligence Hub*
