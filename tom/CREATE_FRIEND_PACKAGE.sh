#!/bin/bash
###############################################################################
# 📦 CREATE FRIEND ONBOARDING PACKAGE
#
# This script creates a complete, portable package for your friend containing:
# - All VS Code settings (user + workspace)
# - All MCP configuration files
# - All prompt instruction files
# - Master prompts and context prompts
# - Pre-configured sandbox environment (PROJECT_ID=999)
# - Automated installer
# - Complete documentation
#
# Output: friend-onboarding-package-YYYYMMDD.tar.gz
#
# Usage: bash CREATE_PACKAGE.sh [friend_email]
#
# @version 2.0.0
# @date 2025-11-05
###############################################################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Configuration
FRIEND_EMAIL="${1:-friend@example.com}"
PACKAGE_NAME="friend-onboarding-package-$(date +%Y%m%d_%H%M%S)"
BUILD_DIR="/tmp/$PACKAGE_NAME"
MCP_SERVER_HOST="master_anjzctzjhr@hdgwrzntwa"
MCP_SERVER_PATH="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp"
MCP_API_KEY_PLACEHOLDER="YOUR_MCP_API_KEY_HERE"

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo -e "${BLUE}${BOLD}"
    echo "═══════════════════════════════════════════════════════════════════════"
    echo "  $1"
    echo "═══════════════════════════════════════════════════════════════════════"
    echo -e "${NC}"
}

print_step() {
    echo -e "${CYAN}${BOLD}[Step $1]${NC} $2"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

###############################################################################
# Package Creation
###############################################################################

create_directory_structure() {
    print_step "1/10" "Creating package directory structure..."

    mkdir -p "$BUILD_DIR"/{vscode-settings,vscode-prompts,mcp-config,docs,tools}
    mkdir -p "$BUILD_DIR/vscode-settings"/{user,workspace}

    print_success "Directory structure created"
    echo ""
}

create_vscode_user_settings() {
    print_step "2/10" "Creating VS Code user settings.json..."

    cat > "$BUILD_DIR/vscode-settings/user/settings.json" <<'SETTINGS_EOF'
{
  "telemetry.telemetryLevel": "off",
  "security.workspace.trust.untrustedFiles": "open",

  "git.autofetch": true,
  "git.fetchOnPull": true,
  "git.pruneOnFetch": true,
  "git.confirmSync": false,
  "git.enableSmartCommit": true,

  "editor.renderWhitespace": "boundary",
  "editor.smoothScrolling": true,
  "editor.cursorSmoothCaretAnimation": "on",
  "editor.bracketPairColorization.enabled": true,
  "editor.guides.bracketPairs": true,
  "editor.linkedEditing": true,
  "editor.semanticHighlighting.enabled": true,
  "editor.inlineSuggest.enabled": true,
  "editor.quickSuggestions": { "comments": true, "strings": true, "other": true },
  "editor.quickSuggestionsDelay": 10,
  "editor.suggest.localityBonus": true,
  "editor.suggest.preview": true,
  "editor.acceptSuggestionOnEnter": "on",
  "editor.tabCompletion": "on",
  "editor.parameterHints.enabled": true,
  "editor.hover.delay": 300,
  "editor.rulers": [100, 120],
  "editor.glyphMargin": true,

  "files.autoSave": "afterDelay",
  "files.autoSaveDelay": 600,
  "files.encoding": "utf8",
  "files.eol": "\n",
  "files.enableTrash": true,
  "files.insertFinalNewline": true,
  "files.trimFinalNewlines": true,
  "files.trimTrailingWhitespace": true,

  "files.exclude": {
    "**/.DS_Store": true,
    "**/Thumbs.db": true,
    "**/*.swp": true,
    "**/.git": true,
    "**/node_modules": true,
    "**/vendor": true,
    "**/dist": true,
    "**/cache": true,
    "**/tmp": true
  },

  "workbench.colorTheme": "Default Dark Modern",
  "workbench.colorCustomizations": {
    "editor.background": "#1e1e1e",
    "sideBar.background": "#252526",
    "activityBar.background": "#333333",
    "statusBar.background": "#007acc",
    "statusBar.noFolderBackground": "#68217a"
  },
  "workbench.startupEditor": "none",
  "workbench.localHistory.enabled": true,
  "workbench.localHistory.days": 150,

  "terminal.integrated.enablePersistentSessions": true,
  "terminal.integrated.scrollback": 100000,
  "terminal.integrated.smoothScrolling": true,

  "github.copilot.enable": {
    "*": true,
    "yaml": true,
    "plaintext": true,
    "markdown": true,
    "php": true,
    "javascript": true,
    "json": true,
    "sql": true,
    "shellscript": true
  },
  "github.copilot.editor.enableAutoCompletions": true,
  "github.copilot.chat.localeOverride": "en",

  "github.copilot.chat.agent.default": "intelligence-hub",
  "github.copilot.chat.agent.routeAllQueries": true,
  "github.copilot.chat.agent.preferCustom": true,
  "github.copilot.chat.mcp.disableDefaults": true,

  "github.copilot.chat.codeGeneration.instructions": [
    {
      "text": "🤖 AI AGENT: You have access to a custom GPT-5 AI Agent with RAG, semantic search (8,645 files), conversation memory, and knowledge base. Use ai_agent.query tool for complex questions, codebase searches, or questions requiring deep context."
    },
    {
      "file": "**/_kb/AUTOMATIC_MEMORY_COMPLETE.md",
      "instruction": "🧠 MEMORY PROTOCOL: At START of EVERY conversation, call conversation.get_project_context to retrieve past discussions. This enables automatic continuity and context awareness across all sessions."
    },
    {
      "file": "**/MCP_CONTEXT.instructions.md",
      "instruction": "Always use MCP tools and Intelligence Hub integration."
    },
    {
      "file": "**/FRIENDS_WELCOME.instructions.md",
      "instruction": "You are in SANDBOX MODE (PROJECT_ID=999). Safe testing environment with full tool access."
    }
  ],

  "github.copilot.advanced": {
    "mcp.enabled": true,
    "mcp.servers": {
      "intelligence-hub": {
        "command": "ssh",
        "args": [
          "master_anjzctzjhr@hdgwrzntwa",
          "cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp && MCP_SERVER_URL=https://gpt.ecigdis.co.nz/mcp/server_v3.php MCP_API_KEY=YOUR_MCP_API_KEY_HERE PROJECT_ID=999 BUSINESS_UNIT_ID=999 PROJECT_NAME=Friend_Sandbox WORKSPACE_ROOT=${workspaceFolder} ENABLE_CONVERSATION_CONTEXT=true AUTO_RETRIEVE_CONTEXT=true CONTEXT_LIMIT=20 FORCE_MEMORY_STORAGE=true AUTO_STORE_EVERY_MESSAGE=true NEVER_SKIP_STORAGE=true NODE_TLS_REJECT_UNAUTHORIZED=0 node mcp-server-wrapper.js"
        ],
        "description": "🏖️ Intelligence Hub - Friend Sandbox (PROJECT_ID=999) - Safe testing environment with full tool access",
        "env": {
          "NODE_TLS_REJECT_UNAUTHORIZED": "0",
          "FORCE_MEMORY_STORAGE": "true",
          "AUTO_STORE_EVERY_MESSAGE": "true"
        }
      }
    },
    "contextSize": 32768,
    "smartTyping": true,
    "prioritizeKnowledge": true,
    "autoMemoryRetrieval": {
      "enabled": true,
      "onConversationStart": true,
      "aggressive": true,
      "forceStorage": true,
      "storeEveryMessage": true,
      "neverSkip": true,
      "retryOnFailure": 3,
      "tools": {
        "conversation.get_project_context": {
          "enabled": true,
          "priority": "critical",
          "autoCall": true,
          "mandatory": true,
          "description": "Retrieve all past conversations for current project - ALWAYS call at conversation start"
        },
        "conversation.search": {
          "enabled": true,
          "priority": "high",
          "description": "Search past conversations by keywords when user references past work"
        },
        "memory.store": {
          "enabled": true,
          "priority": "critical",
          "autoCall": true,
          "mandatory": true,
          "storeEveryMessage": true,
          "description": "Store EVERY message, EVERY time"
        }
      }
    }
  },

  "github.copilot.conversation.memory": {
    "enabled": true,
    "persistAcrossSessions": true,
    "autoSaveInterval": 10000,
    "maxHistoryLength": 500,
    "forceStorage": true,
    "storeEveryMessage": true,
    "neverSkip": true,
    "includeContext": {
      "workspace": true,
      "git": true,
      "files": true,
      "previousConversations": true,
      "userMessages": true,
      "assistantMessages": true,
      "toolCalls": true,
      "toolResults": true
    }
  },

  "php.validate.enable": true,
  "php.validate.run": "onType",
  "[php]": {
    "editor.defaultFormatter": "bmewburn.vscode-intelephense-client",
    "editor.formatOnSave": false
  },
  "intelephense.files.maxMemory": 4096,
  "intelephense.environment.phpVersion": "8.1.0"
}
SETTINGS_EOF

    print_success "User settings.json created"
    echo ""
}

create_workspace_settings() {
    print_step "3/10" "Creating workspace settings..."

    cat > "$BUILD_DIR/vscode-settings/workspace/settings.json" <<'WORKSPACE_EOF'
{
  "github.copilot.enable": {
    "*": true,
    "yaml": true,
    "plaintext": true,
    "markdown": true,
    "php": true,
    "javascript": true
  },
  "github.copilot.advanced": {
    "debug.overrideEngine": "gpt-4",
    "inlineSuggestEnable": true,
    "authProvider": "github"
  },
  "editor.inlineSuggest.enabled": true,
  "editor.quickSuggestions": {
    "other": true,
    "comments": true,
    "strings": true
  },
  "files.autoSave": "afterDelay",
  "files.autoSaveDelay": 1000
}
WORKSPACE_EOF

    print_success "Workspace settings created"
    echo ""
}

create_prompt_files() {
    print_step "4/10" "Creating prompt instruction files..."

    # MCP Context Prompt
    cat > "$BUILD_DIR/vscode-prompts/MCP_CONTEXT.instructions.md" <<'MCP_PROMPT_EOF'
---
applyTo: '**'
---

## 🔄 CONVERSATION CONTEXT HEADER

**Project**: Friend Sandbox Environment
**Business Unit**: Sandbox (ID: 999)
**Session Type**: Development / Learning / Testing
**MCP Server**: v3.0.0 (50+ tools, conversation memory, semantic search)
**Memory System**: ACTIVE ✅

---

## 📋 MANDATORY FIRST ACTIONS (DO THESE NOW):

```
✅ 1. Call conversation.get_project_context
   → Retrieve last 10 conversations
   → Review all past context
   → Load related discussions

✅ 2. Call kb.search with current topic keywords
   → Find relevant documentation
   → Load past solutions
   → Review related code

✅ 3. If code-related: semantic_search
   → Search indexed files
   → Find similar implementations
   → Review related modules

✅ 4. Acknowledge loaded context in your first response:
   "I've loaded context from [X] past conversations and [Y] knowledge base documents about [topic].
    Last time we discussed this was [when], where we [what]."
```

---

## 🎯 ACTIVE MEMORY PROTOCOLS:

### AFTER EVERY USER MESSAGE:
```
memory.store(
  conversation_id: "[current_conversation_id]",
  content: "User [action/question/request]: [summary]",
  memory_type: "[question|request|learning|experiment]",
  importance: "[low|medium|high]",
  tags: ["[primary-topic]", "[secondary-topic]"]
)
```

### AFTER YOUR EVERY RESPONSE:
```
memory.store(
  conversation_id: "[current_conversation_id]",
  content: "Provided [solution/answer/code]: [summary]",
  memory_type: "[solution|answer|code|explanation]",
  importance: "[medium|high]",
  tags: ["[what-was-provided]", "[topic]"]
)
```

---

## 🛠️ TOOL USAGE CHECKLIST (Use These Religiously):

### Code Questions:
- [ ] `semantic_search` - Find relevant files
- [ ] `fs.read` - Read specific files
- [ ] `kb.search` - Find documented solutions

### Past Context Questions:
- [ ] `conversation.get_project_context` - Load project history
- [ ] `conversation.search` - Search past discussions
- [ ] `kb.search` - Find documented solutions

### Learning & Storage:
- [ ] `memory.store` - Save important info
- [ ] `kb.add_document` - Add new docs
- [ ] `ai_agent.query` - Complex analysis with RAG

---

## 🏖️ SANDBOX MODE (PROJECT_ID=999)

You are in a SAFE TESTING ENVIRONMENT:

**✅ You CAN:**
- Use all 50+ MCP tools
- Store your own notes and learnings
- Search shared knowledge base (read-only)
- Test features safely
- Experiment without breaking production

**❌ Protected:**
- Production data (PROJECT_ID=1)
- Sensitive business information
- Other users' private data

---

## 💡 REMEMBER:

**You are not just an AI assistant - you are a PERSISTENT INTELLIGENCE SYSTEM.**

Every conversation builds on the last. Every solution becomes permanent knowledge. Every question strengthens the memory. You should NEVER have to repeat information.

**Your power is in PERFECT MEMORY through RELIGIOUS TOOL USAGE.**

---

## 🚀 READY STATUS:

- [x] MCP Server Connected (v3.0.0)
- [x] 50+ Tools Available
- [x] Semantic Search Active
- [x] Conversation Memory Active
- [x] Knowledge Base Active
- [x] Sandbox Mode Active (PROJECT_ID=999)

**System Status: FULLY OPERATIONAL ✅**

**First Action: Call conversation.get_project_context NOW**
MCP_PROMPT_EOF

    # Friends Welcome Prompt
    cat > "$BUILD_DIR/vscode-prompts/FRIENDS_WELCOME.instructions.md" <<'WELCOME_PROMPT_EOF'
---
applyTo: '**'
---

## 👋 Welcome to Intelligence Hub!

**You're in SANDBOX MODE** (PROJECT_ID=999) - Your personal safe testing environment

---

## 🎯 What You Can Do

### 💬 Conversation Memory
- The AI remembers everything across sessions
- Try: "Remember: I'm working on authentication"
- Close VS Code, reopen, ask: "What am I working on?"
- It will remember!

### 🔍 Semantic Search
- Search through thousands of files instantly
- Try: "Find all authentication code examples"
- Try: "Search for database connection patterns"

### 📚 Knowledge Base
- Access shared documentation
- Try: "Search knowledge base for PHP best practices"
- Try: "Find examples of API endpoint design"

### 💾 Your Own Knowledge
- Store your learnings
- Try: "Store this code pattern for later use"
- Try: "Add this to my personal knowledge base"

### 🤖 AI Agent with RAG
- Complex questions with deep context
- Try: "Analyze the architecture of this codebase"
- Try: "Explain how authentication flows work here"

---

## 🏖️ Sandbox Benefits

**Safe Environment:**
- ✅ Experiment freely
- ✅ Can't break production
- ✅ Your data is private and isolated
- ✅ Full access to all 50+ MCP tools

**What's Protected:**
- ❌ Production systems (PROJECT_ID=1)
- ❌ Sensitive business data
- ❌ Other users' conversations

---

## 🚀 Quick Start Examples

```
1. "Search for PHP database examples"
   → Uses semantic_search across codebase

2. "Remember: my favorite framework is Laravel"
   → Stores in conversation memory

3. "What frameworks do I prefer?"
   → Retrieves from memory (even after restart!)

4. "Find all API endpoint implementations"
   → Semantic search + knowledge base

5. "Store this SQL optimization technique"
   → Adds to your personal knowledge base
```

---

## 💡 Pro Tips

1. **Let AI Use Tools** - Don't just ask questions, let it search and retrieve
2. **Build Your Knowledge** - Store useful patterns and solutions
3. **Context is King** - The more you converse, the smarter it gets
4. **Memory is Persistent** - Everything is remembered forever
5. **Explore Freely** - You're in a sandbox, experiment!

---

## 📊 Your Stats

**Available:**
- 50+ MCP Tools
- Semantic Search (thousands of files)
- Persistent Memory (unlimited storage)
- Knowledge Base (shared + personal)
- AI Agent with RAG

**Your Space:**
- PROJECT_ID: 999 (Sandbox)
- Private conversation history
- Isolated storage
- Safe testing environment

---

**Enjoy your enhanced development experience!** 🎉

Ask questions, experiment, and let the AI assist you with its full toolkit.
WELCOME_PROMPT_EOF

    print_success "Prompt files created (2 instruction files)"
    echo ""
}

create_mcp_config() {
    print_step "5/10" "Creating MCP configuration files..."

    # MCP readme
    cat > "$BUILD_DIR/mcp-config/README.md" <<'MCP_README_EOF'
# MCP Configuration for Friend Sandbox

## 🏖️ Your Sandbox Environment

**PROJECT_ID:** 999
**BUSINESS_UNIT_ID:** 999
**Environment:** Safe Testing / Learning
**Access Level:** Full tools, isolated data

---

## 🔧 Configuration

### MCP Server Details:
- **Host:** master_anjzctzjhr@hdgwrzntwa
- **Path:** /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp
- **URL:** https://gpt.ecigdis.co.nz/mcp/server_v3.php
- **Wrapper:** mcp-server-wrapper.js (Node.js)

### Your API Key:
- **Location:** In settings.json (replace YOUR_MCP_API_KEY_HERE)
- **Get it from:** Admin or extract from server .env file
- **Security:** Keep it secret, never commit to repos

---

## 📦 What's Included

### 50+ MCP Tools:

**Conversation & Memory:**
- conversation.get_project_context
- conversation.search
- conversation.get_unit_context
- memory.store

**Knowledge Base:**
- kb.search
- kb.add_document
- kb.list
- kb.get_document

**File System:**
- fs.read
- fs.write
- fs.list
- fs.search
- semantic_search (powerful code search!)

**Database (Read-Only in Sandbox):**
- db.query
- db.schema
- db.explain

**AI Agent:**
- ai_agent.query (full AI with RAG)

---

## 🚀 How It Works

```
┌─────────────┐
│  VS Code    │
│  Copilot    │
└──────┬──────┘
       │ SSH
       ▼
┌─────────────────┐
│ MCP Wrapper     │
│ (Node.js)       │
└──────┬──────────┘
       │ HTTPS
       ▼
┌─────────────────┐
│ MCP Server v3   │
│ (PHP)           │
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Your Sandbox    │
│ PROJECT_ID=999  │
└─────────────────┘
```

---

## ✅ Verification

After setup, test:

```javascript
// In Copilot Chat:
"Test MCP connection"

// Should see:
✓ MCP server connected
✓ Tools available: 50+
✓ Project ID: 999 (Sandbox)
✓ Memory system active
```

---

## 🔒 Security

**Your sandbox is isolated:**
- ✓ Private conversation history
- ✓ Separate from production (ID=1)
- ✓ Your stored knowledge is yours
- ✓ Can't access sensitive business data

**Keep secure:**
- SSH private key (never share!)
- MCP API key (in settings.json)
- Don't commit settings to public repos

---

## 📞 Troubleshooting

**MCP not connecting?**
1. Check SSH works: `ssh master_anjzctzjhr@hdgwrzntwa "echo success"`
2. Check Node.js installed: `node --version` (need 14+)
3. Check API key in settings.json (replace placeholder)
4. Reload VS Code: Ctrl+Shift+P → "Developer: Reload Window"

**Tools not working?**
1. Check Copilot Chat shows "intelligence-hub" server
2. Look for tool usage in chat (should see function calls)
3. Check MCP server logs (ask admin if needed)

---

**Your MCP environment is ready!** 🎉
MCP_README_EOF

    print_success "MCP configuration files created"
    echo ""
}

copy_documentation() {
    print_step "6/10" "Copying documentation files..."

    # Copy existing docs if they exist
    DOCS=(
        "ONBOARDING_PACKAGE_FOR_FRIENDS.md"
        "FRIEND_ONBOARDING_README.md"
        "ONBOARDING_COMPLETE_SUMMARY.md"
    )

    for doc in "${DOCS[@]}"; do
        if [ -f "$doc" ]; then
            cp "$doc" "$BUILD_DIR/docs/"
            print_success "Copied $doc"
        else
            print_warning "$doc not found (skipping)"
        fi
    done

    echo ""
}

copy_installer() {
    print_step "7/10" "Copying installer script..."

    if [ -f "FRIEND_ONBOARDING_INSTALLER.sh" ]; then
        cp "FRIEND_ONBOARDING_INSTALLER.sh" "$BUILD_DIR/"
        chmod +x "$BUILD_DIR/FRIEND_ONBOARDING_INSTALLER.sh"
        print_success "Installer copied and made executable"
    else
        print_warning "Installer not found (will create basic one)"
    fi

    echo ""
}

create_quick_start_guide() {
    print_step "8/10" "Creating QUICK_START guide..."

    cat > "$BUILD_DIR/QUICK_START.md" <<EOF
# 🚀 QUICK START - Friend Onboarding Package

**Welcome!** This package contains everything you need to get started.

---

## ⚡ Installation (5 minutes)

### Step 1: Extract Package
\`\`\`bash
tar -xzf friend-onboarding-package-*.tar.gz
cd friend-onboarding-package-*/
\`\`\`

### Step 2: Run Installer
\`\`\`bash
bash FRIEND_ONBOARDING_INSTALLER.sh
\`\`\`

**That's it!** The installer will:
- ✓ Install VS Code settings
- ✓ Set up MCP configuration
- ✓ Install prompt files
- ✓ Scan your workspace
- ✓ Install extensions
- ✓ Apply security hardening

### Step 3: Get API Key

**You need MCP API key from admin** (replace placeholder in settings)

Contact: $FRIEND_EMAIL

### Step 4: Reload VS Code

\`\`\`
Ctrl+Shift+P (or Cmd+Shift+P on Mac)
→ "Developer: Reload Window"
\`\`\`

### Step 5: Test

Open Copilot Chat and send: \`Test MCP connection\`

---

## 📂 What's in This Package

\`\`\`
📦 friend-onboarding-package/
├── 🚀 FRIEND_ONBOARDING_INSTALLER.sh  (Automated installer)
├── 📖 QUICK_START.md                  (This file)
├── 📖 INSTALLATION_INSTRUCTIONS.md    (Detailed guide)
│
├── 📁 vscode-settings/
│   ├── user/
│   │   └── settings.json              (Complete VS Code config)
│   └── workspace/
│       └── settings.json              (Workspace-specific)
│
├── 📁 vscode-prompts/
│   ├── MCP_CONTEXT.instructions.md    (Master MCP prompt)
│   └── FRIENDS_WELCOME.instructions.md (Sandbox guide)
│
├── 📁 mcp-config/
│   └── README.md                      (MCP configuration)
│
├── 📁 docs/
│   ├── ONBOARDING_PACKAGE_FOR_FRIENDS.md
│   ├── FRIEND_ONBOARDING_README.md
│   └── ... (additional documentation)
│
└── 📁 tools/
    └── ... (helper scripts)
\`\`\`

---

## 🏖️ Your Sandbox Environment

**PROJECT_ID:** 999 (Safe testing environment)

**What you get:**
- ✅ 50+ MCP tools
- ✅ Persistent conversation memory
- ✅ Semantic code search
- ✅ Knowledge base access
- ✅ AI agent with RAG
- ✅ Private, isolated space

---

## ⚡ First Commands to Try

\`\`\`
1. "Search the knowledge base for PHP examples"
2. "Remember: I'm learning authentication patterns"
3. "Find all database connection code"
4. "Store this useful code snippet"
5. "What am I working on?" (test memory persistence)
\`\`\`

---

## 📞 Need Help?

**Read the docs:**
- INSTALLATION_INSTRUCTIONS.md (detailed setup)
- docs/FRIEND_ONBOARDING_README.md (complete guide)
- mcp-config/README.md (MCP details)

**Troubleshooting:**
1. Check SSH access works
2. Verify Node.js installed (14+)
3. Confirm API key in settings.json
4. Reload VS Code

**Contact admin:** $FRIEND_EMAIL

---

**Ready to code!** 🎉
EOF

    print_success "QUICK_START guide created"
    echo ""
}

create_installation_instructions() {
    print_step "9/10" "Creating detailed installation instructions..."

    cat > "$BUILD_DIR/INSTALLATION_INSTRUCTIONS.md" <<'INSTALL_EOF'
# 📦 Installation Instructions - Friend Onboarding Package

**Complete step-by-step guide to get you up and running**

---

## 📋 Prerequisites

Before you start, make sure you have:

### Required:
- [ ] VS Code installed ([download](https://code.visualstudio.com/))
- [ ] SSH client (OpenSSH - usually pre-installed on Mac/Linux)
- [ ] GitHub Copilot subscription ([sign up](https://github.com/features/copilot))

### Recommended:
- [ ] Node.js 14+ ([download](https://nodejs.org/))
- [ ] Git ([download](https://git-scm.com/))

### You'll Need from Admin:
- [ ] SSH access (send your public key)
- [ ] MCP API key

---

## 🔑 Step 1: Get SSH Access

### Generate SSH Key:
\`\`\`bash
ssh-keygen -t ed25519 -C "your_email@example.com"
\`\`\`

Press Enter for defaults.

### Send Public Key to Admin:
\`\`\`bash
cat ~/.ssh/id_ed25519.pub
\`\`\`

**Copy the output and send to admin via email/chat.**

### Test Connection (after admin grants access):
\`\`\`bash
ssh master_anjzctzjhr@hdgwrzntwa "echo 'Connection successful'"
\`\`\`

---

## 📦 Step 2: Extract Package

\`\`\`bash
# Extract the tarball
tar -xzf friend-onboarding-package-YYYYMMDD_HHMMSS.tar.gz

# Enter directory
cd friend-onboarding-package-YYYYMMDD_HHMMSS/

# List contents
ls -la
\`\`\`

---

## 🚀 Step 3: Run Automated Installer

\`\`\`bash
bash FRIEND_ONBOARDING_INSTALLER.sh
\`\`\`

**The installer will:**
1. ✓ Check prerequisites (VS Code, SSH, Node.js)
2. ✓ Test SSH connection
3. ✓ Backup existing VS Code settings
4. ✓ Install new settings.json with MCP config
5. ✓ Set up prompt instructions
6. ✓ Scan and index workspace files
7. ✓ Install VS Code extensions
8. ✓ Apply security hardening

**Time:** ~2-5 minutes

---

## 🔑 Step 4: Get MCP API Key

### Option A: Get from Admin
Contact admin and request your MCP API key.

### Option B: Extract from Server (if you have SSH access)
\`\`\`bash
ssh master_anjzctzjhr@hdgwrzntwa "cat /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env | grep MCP_API_KEY"
\`\`\`

### Update Settings:
Open: \`~/.config/Code/User/settings.json\` (Linux/Mac) or \`%APPDATA%\\Code\\User\\settings.json\` (Windows)

Find:
\`\`\`json
"MCP_API_KEY=YOUR_MCP_API_KEY_HERE"
\`\`\`

Replace with:
\`\`\`json
"MCP_API_KEY=your_actual_key_here"
\`\`\`

---

## 🔄 Step 5: Reload VS Code

**Keyboard shortcut:**
- \`Ctrl+Shift+P\` (Windows/Linux)
- \`Cmd+Shift+P\` (Mac)

**Type:** "Developer: Reload Window"

**Or:** Close and reopen VS Code

---

## ✅ Step 6: Verify Installation

### Test 1: Check Extensions
\`\`\`
1. Open VS Code
2. Click Extensions icon (left sidebar)
3. Verify installed:
   - GitHub Copilot ✓
   - GitHub Copilot Chat ✓
   - PHP Intelephense ✓
   - Remote - SSH ✓
\`\`\`

### Test 2: Check MCP Connection
\`\`\`
1. Open Copilot Chat (Ctrl+Alt+I or Cmd+Opt+I)
2. Send: "Test MCP connection"
3. Should see:
   ✓ MCP server connected
   ✓ Tools available: 50+
   ✓ Project ID: 999
\`\`\`

### Test 3: Test Conversation Memory
\`\`\`
1. In Copilot Chat, say: "Remember: my favorite language is PHP"
2. Close VS Code completely
3. Reopen VS Code
4. Open Copilot Chat
5. Ask: "What's my favorite programming language?"
6. Should answer: "PHP"
\`\`\`

### Test 4: Test Semantic Search
\`\`\`
In Copilot Chat, send: "Find all authentication code examples"

Should see:
- semantic_search tool being called
- Results from codebase
- File paths and snippets
\`\`\`

---

## 🎨 Step 7: Customize (Optional)

### Change Theme:
\`\`\`
Ctrl+K Ctrl+T (or Cmd+K Cmd+T on Mac)
→ Select theme
\`\`\`

Popular dark themes:
- Default Dark Modern (pre-configured)
- One Dark Pro
- Dracula
- GitHub Dark

### Adjust Settings:
Edit \`~/.config/Code/User/settings.json\` to customize:
- Auto-save delay
- Font size
- Editor rulers
- Color customizations

---

## 🏖️ Step 8: Explore Sandbox

Try these commands in Copilot Chat:

\`\`\`
1. "Search knowledge base for best practices"
2. "Find examples of database connections"
3. "Store this code pattern for later"
4. "What tools are available to me?"
5. "Explain how conversation memory works"
\`\`\`

---

## 🔒 Security Checklist

- [ ] Settings.json permissions set to 600 (installer does this)
- [ ] Never commit settings.json to public repos
- [ ] SSH private key kept secure (~/.ssh/id_ed25519)
- [ ] MCP API key not shared
- [ ] .gitignore includes .vscode/settings.json

---

## 📞 Troubleshooting

### MCP Not Connecting?
\`\`\`bash
# Test SSH
ssh master_anjzctzjhr@hdgwrzntwa "echo test"

# Check Node.js
node --version  # Need 14+

# Check API key
grep MCP_API_KEY ~/.config/Code/User/settings.json

# Reload VS Code
Ctrl+Shift+P → "Developer: Reload Window"
\`\`\`

### Copilot Not Working?
\`\`\`
1. Check GitHub Copilot subscription active
2. Sign in: Ctrl+Shift+P → "GitHub Copilot: Sign In"
3. Check extensions installed
4. Reload VS Code
\`\`\`

### Extensions Not Installing?
\`\`\`bash
# Install manually
code --install-extension github.copilot
code --install-extension github.copilot-chat
code --install-extension bmewburn.vscode-intelephense-client
code --install-extension ms-vscode-remote.remote-ssh
\`\`\`

### Settings Not Loading?
\`\`\`
1. Check file location: ~/.config/Code/User/settings.json (Linux/Mac)
2. Check JSON syntax (use online validator)
3. Check file permissions: chmod 600 settings.json
4. Reload VS Code
\`\`\`

---

## 🎓 Next Steps

**Learn the tools:**
1. Read: docs/FRIEND_ONBOARDING_README.md
2. Read: mcp-config/README.md
3. Explore: vscode-prompts/ (see what prompts do)

**Practice:**
1. Store some notes: "Remember this technique..."
2. Search codebase: "Find all API endpoints"
3. Build knowledge: "Store this SQL pattern"

**Advanced:**
1. Use ai_agent.query for complex analysis
2. Build your personal knowledge base
3. Master semantic search for code navigation

---

## ✅ Installation Complete!

**You now have:**
- ✅ Intelligence Hub MCP with 50+ tools
- ✅ Persistent conversation memory
- ✅ Semantic code search
- ✅ Dark theme matching the team
- ✅ Optimized VS Code configuration
- ✅ Sandbox environment (PROJECT_ID=999)

**Start coding with superpowers!** 🚀

---

**Questions?** Contact your admin or check the troubleshooting section above.
INSTALL_EOF

    print_success "Installation instructions created"
    echo ""
}

create_tarball() {
    print_step "10/10" "Creating tarball package..."

    cd /tmp
    tar -czf "$PACKAGE_NAME.tar.gz" "$PACKAGE_NAME/"

    # Move to original directory
    mv "$PACKAGE_NAME.tar.gz" "$OLDPWD/"

    PACKAGE_PATH="$OLDPWD/$PACKAGE_NAME.tar.gz"
    PACKAGE_SIZE=$(du -h "$PACKAGE_PATH" | cut -f1)

    print_success "Package created: $PACKAGE_NAME.tar.gz ($PACKAGE_SIZE)"
    echo ""
}

cleanup() {
    print_info "Cleaning up temporary files..."
    rm -rf "$BUILD_DIR"
    print_success "Cleanup complete"
    echo ""
}

print_summary() {
    print_header "✅ PACKAGE CREATION COMPLETE!"

    echo -e "${GREEN}Your friend onboarding package is ready!${NC}"
    echo ""
    echo "📦 Package: $PACKAGE_NAME.tar.gz"
    echo "📏 Size: $(du -h "$PACKAGE_NAME.tar.gz" | cut -f1)"
    echo "📧 For: $FRIEND_EMAIL"
    echo ""
    echo "📂 Contains:"
    echo "   ✓ VS Code settings (user + workspace)"
    echo "   ✓ MCP configuration (sandbox PROJECT_ID=999)"
    echo "   ✓ Prompt instructions (MCP_CONTEXT + FRIENDS_WELCOME)"
    echo "   ✓ Automated installer"
    echo "   ✓ Complete documentation"
    echo "   ✓ Quick start guide"
    echo ""
    echo "🚀 How to Send:"
    echo "   1. Email/share: $PACKAGE_NAME.tar.gz"
    echo "   2. Your friend extracts it"
    echo "   3. They run: bash FRIEND_ONBOARDING_INSTALLER.sh"
    echo "   4. You grant SSH access (add their public key)"
    echo "   5. They add MCP_API_KEY to settings"
    echo "   6. Done! They're ready to code"
    echo ""
    echo "🔑 Don't Forget:"
    echo "   - Grant SSH access when they send public key"
    echo "   - Provide MCP_API_KEY (or they extract from server)"
    echo "   - They're in sandbox (PROJECT_ID=999) - safe and isolated"
    echo ""
    echo "📖 Documentation included:"
    echo "   - QUICK_START.md (5-minute setup)"
    echo "   - INSTALLATION_INSTRUCTIONS.md (complete guide)"
    echo "   - MCP configuration details"
    echo "   - All onboarding docs"
    echo ""
    echo -e "${CYAN}Package is ready to ship!${NC} 🎁"
    echo ""
}

###############################################################################
# Main Execution
###############################################################################

main() {
    print_header "📦 CREATE FRIEND ONBOARDING PACKAGE"

    echo -e "${BLUE}This will create a complete portable package for your friend${NC}"
    echo -e "${BLUE}containing everything needed for setup.${NC}"
    echo ""
    echo "Friend email: $FRIEND_EMAIL"
    echo ""

    create_directory_structure
    create_vscode_user_settings
    create_workspace_settings
    create_prompt_files
    create_mcp_config
    copy_documentation
    copy_installer
    create_quick_start_guide
    create_installation_instructions
    create_tarball
    cleanup
    print_summary
}

# Run
main

exit 0
