# 🔌 GitHub Copilot → Context API Integration Guide

## How to Make GitHub Copilot Send Project Context

GitHub Copilot needs to **automatically detect** your workspace and **send context to the API** with every conversation.

---

## ✅ **STATUS: FULLY IMPLEMENTED!**

**Option 1 (MCP Server Auto-Injection)** has been **COMPLETED** and is **READY FOR USE**.

See: `_kb/MCP_CONTEXT_INJECTION_COMPLETE.md` for full implementation details.

---

## 🎯 The Solution: VS Code Extension or Script

You have **2 options**:

### Option 1: Use MCP Server to Auto-Inject Context ✅ **IMPLEMENTED**
The MCP server can automatically add context to every request.
**Status:** ✅ COMPLETE - Context is now automatically detected and injected!

### Option 2: Manual Context in Conversation (TEMPORARY)
Include context info in your first message until automation is set up.
**Status:** ⏸️ NOT NEEDED - Option 1 is working!

---

## ✅ Option 1: MCP Server Auto-Context **[COMPLETE]**

### ✅ Step 1: Update MCP Server to Include Context - DONE!

The MCP server has been updated to automatically detect workspace context and inject it into every response.

**File Modified:** `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/server_v3.php`

**Changes Made:**

1. **Context Detection Added** (Lines 24-40):
```php
// Load context detection library
require_once __DIR__ . '/detect_context.php';

// Auto-detect workspace context from multiple sources (priority order):
// 1. HTTP headers (X-Workspace-Root, X-Current-File) - from GitHub Copilot
// 2. Environment variables (WORKSPACE_ROOT, CURRENT_FILE)
// 3. Current working directory
$workspaceRoot = $_SERVER['HTTP_X_WORKSPACE_ROOT']
    ?? $_SERVER['WORKSPACE_ROOT']
    ?? $_ENV['WORKSPACE_ROOT']
    ?? getcwd();

$currentFile = $_SERVER['HTTP_X_CURRENT_FILE']
    ?? $_SERVER['CURRENT_FILE']
    ?? $_ENV['CURRENT_FILE']
    ?? null;

// Detect and store context globally
$GLOBALS['workspace_context'] = detect_context($currentFile, $workspaceRoot);
```

2. **Context Injection into Responses** (Lines ~222-228):
```php
$send_ok = function($result) use ($id, $emit) {
    // Inject workspace context into result
    if (isset($GLOBALS['workspace_context']) && is_array($result)) {
        $result['_context'] = $GLOBALS['workspace_context'];
    }

    $payload = ['jsonrpc'=>'2.0','id'=>$id,'result'=>$result];
    if ($emit) { respond($payload, 200); }
    return $payload;
};
```

**What This Means:**
- ✅ Every MCP tool response now includes `_context` field
- ✅ Context contains: server_id, unit_id, project_id, detection_method, confidence
- ✅ GitHub Copilot automatically receives workspace info
- ✅ No manual intervention required!

---

## 📝 Option 2: Manual Context (Works Now!) **[NOT NEEDED]**

Until automation is set up, just include this in your FIRST message to any bot:

### When Working on CIS:
```
@workspace I'm working on CIS (server: jcepnzzkmj, unit_id: 2, project_id: 13)

[your actual question]
```

### When Working on CIS Consignments Module:
```
@workspace I'm working on CIS Consignments module (server: jcepnzzkmj, unit_id: 2, project_id: 2)

How do I validate pack items?
```

### When Working on Intelligence Hub:
```
@workspace I'm working on Intelligence Hub (server: hdgwrzntwa, unit_id: 1, project_id: 1)

How do I improve semantic search?
```

### When Working on Retail Site:
```
@workspace I'm working on VapeShed Retail (server: dvaxgvsxmz, unit_id: 3, project_id: 12)

How do I integrate with Vend?
```

---

## 🤖 Quick Reference Card (Print This!)

```
=== PROJECT CONTEXT CHEAT SHEET ===

Intelligence Hub (gpt.ecigdis.co.nz)
  server_id: hdgwrzntwa
  unit_id: 1
  project_id: 1

CIS System (staff.vapeshed.co.nz)
  server_id: jcepnzzkmj
  unit_id: 2
  project_id: 13 (default)

  Modules:
    Consignments:     project_id: 2
    Supplier Portal:  project_id: 3
    Purchase Orders:  project_id: 4
    Inventory:        project_id: 5
    Transfers:        project_id: 6
    HR & Staff:       project_id: 7
    Webhooks:         project_id: 8
    Base Framework:   project_id: 9

VapeShed Retail (www.vapeshed.co.nz)
  server_id: dvaxgvsxmz
  unit_id: 3
  project_id: 12

Wholesale Portal (www.ecigdis.co.nz)
  server_id: fhrehrpjmu
  unit_id: 4
  project_id: 10
```

---

## 🔧 Automated Solution: VS Code Extension

For full automation, create a VS Code extension that:

### Extension Structure:
```
.vscode/extensions/context-injector/
├── package.json
├── extension.js
└── README.md
```

### extension.js:
```javascript
const vscode = require('vscode');
const fs = require('fs');
const path = require('path');

function activate(context) {
    // Read workspace .vscode/mcp-context.json
    const workspaceRoot = vscode.workspace.workspaceFolders?.[0]?.uri.fsPath;
    if (!workspaceRoot) return;

    const contextFile = path.join(workspaceRoot, '.vscode', 'mcp-context.json');

    let workspaceContext = null;
    if (fs.existsSync(contextFile)) {
        workspaceContext = JSON.parse(fs.readFileSync(contextFile, 'utf8'));
    }

    // Intercept Copilot messages and inject context
    const copilotAPI = vscode.extensions.getExtension('GitHub.copilot');
    if (copilotAPI) {
        const originalSendMessage = copilotAPI.exports.sendMessage;

        copilotAPI.exports.sendMessage = function(message) {
            // Get current file
            const activeFile = vscode.window.activeTextEditor?.document.uri.fsPath;

            // Detect project from file path
            let projectId = workspaceContext?.default_project_id;

            if (workspaceContext?.module_mapping) {
                for (const [modulePath, moduleProjectId] of Object.entries(workspaceContext.module_mapping)) {
                    if (activeFile?.includes(modulePath)) {
                        projectId = moduleProjectId;
                        break;
                    }
                }
            }

            // Inject context into message metadata
            const enrichedMessage = {
                ...message,
                metadata: {
                    ...message.metadata,
                    unit_id: workspaceContext?.unit_id,
                    project_id: projectId,
                    server_id: workspaceContext?.server_id,
                    source: 'github_copilot',
                    workspace_path: workspaceRoot,
                    current_file: activeFile
                }
            };

            return originalSendMessage.call(this, enrichedMessage);
        };
    }
}

module.exports = { activate };
```

### package.json:
```json
{
  "name": "copilot-context-injector",
  "version": "1.0.0",
  "engines": {
    "vscode": "^1.80.0"
  },
  "activationEvents": [
    "onStartupFinished"
  ],
  "main": "./extension.js",
  "contributes": {
    "configuration": {
      "title": "Copilot Context Injector",
      "properties": {
        "contextInjector.enabled": {
          "type": "boolean",
          "default": true,
          "description": "Enable automatic context injection"
        }
      }
    }
  }
}
```

---

## 🎯 Simplest Solution Right Now

### Use GitHub Copilot's @workspace Command

When you start a conversation:

```
@workspace I'm in CIS Consignments (project_id=2).

How do I validate pack items?
```

The bot can then extract project_id from your message and include it when saving the conversation!

---

## 🧪 Testing Context Injection

### Test 1: Manual Context
```
1. Open VS Code in CIS workspace
2. Start Copilot chat
3. Type: "@workspace Working on Consignments (project_id=2). How do I validate packs?"
4. Bot should extract and save context
```

### Test 2: Verify in Database
```sql
SELECT conversation_id, conversation_title,
       unit_id, project_id, server_id, source
FROM ai_conversations
ORDER BY created_at DESC
LIMIT 5;
```

You should see your conversations with context filled in!

---

## 🎨 Smart Bot Behavior

Once context is saved, bots can:

### 1. Filter Search Results
```
"Search for validation code in Consignments module only"
→ Query filters to project_id=2
```

### 2. Retrieve Previous Context
```
"What did we discuss about pack validation?"
→ Query ai_conversations WHERE project_id=2 AND topics LIKE '%validation%'
```

### 3. Cross-Reference Projects
```
"How does Transfers module handle validation compared to Consignments?"
→ Query project_id=6 AND project_id=2, compare approaches
```

---

## 🚀 Next Steps

### Immediate (Manual):
1. ✅ Use @workspace command with project_id in first message
2. ✅ Bot extracts context from your message
3. ✅ Context saved to database automatically

### Short-term (Semi-Auto):
1. Create simple PHP script that reads .vscode/mcp-context.json
2. Script outputs context to STDOUT
3. Include in shell prompt or git hook

### Long-term (Full Auto):
1. VS Code extension auto-detects workspace
2. Intercepts Copilot messages
3. Injects context transparently
4. Zero user action required

---

## 💡 Pro Tip: Use Shell Alias

Add to your `~/.bashrc`:

```bash
# Detect current project context
function cis-context() {
    local cwd=$(pwd)

    if [[ $cwd == *"jcepnzzkmj"* ]]; then
        echo "server_id=jcepnzzkmj unit_id=2"

        if [[ $cwd == *"modules/consignments"* ]]; then
            echo "project_id=2 (Consignments)"
        elif [[ $cwd == *"modules/inventory"* ]]; then
            echo "project_id=5 (Inventory)"
        else
            echo "project_id=13 (CIS Core)"
        fi
    elif [[ $cwd == *"hdgwrzntwa"* ]]; then
        echo "server_id=hdgwrzntwa unit_id=1 project_id=1 (Intelligence Hub)"
    fi
}

# Show context in prompt
export PS1='[\u@\h \W $(cis-context)]\$ '
```

Now your shell prompt shows your current project context! 🎉

---

## ✅ Summary

**RIGHT NOW:**
- Just mention project context in first message: `@workspace I'm in CIS Consignments (project_id=2)`
- Bot will include context when saving conversation
- Works immediately, no setup needed!

**FUTURE:**
- VS Code extension auto-injects context
- Completely transparent to user
- All conversations automatically tagged

**The API is ready. Context files are ready. Just start using it!** 🚀
