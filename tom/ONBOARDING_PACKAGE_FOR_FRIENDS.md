# 🚀 Complete VS Code Setup Package for Friends

**Last Updated:** November 5, 2025
**Purpose:** Get your friend up and running with the same powerful VS Code + MCP + Intelligence Hub setup you have.

---

## � AUTOMATED INSTALLER (NEW!)

**⚡ FASTEST WAY:** Use the automated installer script!

```bash
# Download and run the installer
curl -O https://staff.vapeshed.co.nz/FRIEND_ONBOARDING_INSTALLER.sh
bash FRIEND_ONBOARDING_INSTALLER.sh

# Or if you have the repo:
bash FRIEND_ONBOARDING_INSTALLER.sh /path/to/your/workspace
```

**What it does automatically:**
- ✅ Installs VS Code settings with MCP configuration
- ✅ Sets up prompt instructions
- ✅ Scans and indexes your workspace files (for semantic search)
- ✅ Installs required VS Code extensions
- ✅ Applies security hardening
- ✅ Creates file index for optimization
- ✅ Tests SSH connection
- ✅ Backs up existing settings

**Time:** ~2 minutes (vs 20 minutes manual)

---

## �📦 What This Package Includes

1. ✅ **Automated installer** (FRIEND_ONBOARDING_INSTALLER.sh) - ONE COMMAND SETUP
2. ✅ Complete VS Code `settings.json` (MCP + Copilot + memory)
3. ✅ Dark grey theme configuration
4. ✅ All custom prompt instructions
5. ✅ MCP Intelligence Hub connection (FRIENDS version - PROJECT_ID=999)
6. ✅ Workspace file scanning and indexing
7. ✅ Security hardening (permissions, .gitignore)
8. ✅ Step-by-step manual guide (if you prefer)

---

## 🎯 Manual Setup (If You Prefer)

### Step 1: Install VS Code Settings**Location:** `%APPDATA%\Code\User\settings.json` (Windows) or `~/.config/Code/User/settings.json` (Linux/Mac)

### Instructions:
1. Open VS Code
2. Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
3. Type: "Preferences: Open User Settings (JSON)"
4. **BACKUP YOUR CURRENT SETTINGS FIRST!** (Copy to `settings.json.backup`)
5. Replace entire contents with the settings file below

**Download the settings file:**
- See `FRIENDS_VSCODE_SETTINGS.json` (attached in this package)
- Or copy from the code block at the end of this document

---

## 🎨 Step 2: Get the Dark Grey Theme

The grey window theme is **"Dark+ (default dark)"** with these customizations:

```json
"workbench.colorTheme": "Default Dark Modern",
"workbench.colorCustomizations": {
    "editor.background": "#1e1e1e",
    "sideBar.background": "#252526",
    "activityBar.background": "#333333"
}
```

**These are already included in the settings.json provided.**

Alternative popular dark grey themes:
- "One Dark Pro"
- "Dracula"
- "GitHub Dark"
- "Monokai Pro"

Install via: Extensions → Search theme name → Install

---

## 🧠 Step 3: MCP Intelligence Hub Connection

### What is MCP?
MCP (Model Context Protocol) gives Copilot superpowers:
- 🗄️ Access to 8,645+ indexed code files
- 💾 Persistent conversation memory
- 🔍 Semantic code search
- 📚 Knowledge base integration
- 🤖 Custom AI agent with RAG

### Friend Setup (Sandbox Mode):
Your friend will connect to **PROJECT_ID=999** (Friends Sandbox):
- ✅ Read-only access to public docs
- ✅ Isolated conversation history
- ✅ All MCP tools available
- ❌ No access to CIS internal data

### SSH Access Required:
Your friend needs SSH access to: `master_anjzctzjhr@hdgwrzntwa`

**If they don't have SSH access yet:**
1. Generate SSH key: `ssh-keygen -t ed25519 -C "friend@example.com"`
2. Send you their **public key** (`~/.ssh/id_ed25519.pub`)
3. You add it to server: `~/.ssh/authorized_keys`

---

## 📋 Step 4: Install Prompt Instructions

### Location:
`%APPDATA%\Code\User\prompts\` (Windows)
`~/.config/Code/User/prompts/` (Linux/Mac)

### Files to Create:

#### 1. `MCP_CONTEXT.instructions.md`
```markdown
---
applyTo: '**'
---

## 🔄 MCP CONTEXT PROTOCOL

You have access to Intelligence Hub MCP server with 50+ tools.

**MANDATORY FIRST ACTION:**
Call `conversation.get_project_context` at the start of EVERY conversation.

**Available Tools:**
- conversation.* - Memory and context retrieval
- kb.* - Knowledge base search and storage
- db.* - Database queries
- fs.* - File system operations
- semantic_search - Code search across 8,645 files
- ai_agent.query - Full AI agent with RAG

**Use tools religiously. Never assume - always verify.**
```

#### 2. `FRIENDS_WELCOME.instructions.md`
```markdown
---
applyTo: '**'
---

## 👋 Welcome Friend!

You're connected to the Intelligence Hub in **SANDBOX MODE** (PROJECT_ID=999).

**What you can do:**
- Ask questions about code
- Search the knowledge base
- Use semantic search
- Store your own notes and learnings

**What's protected:**
- Internal CIS data (PROJECT_ID=1)
- Production databases
- Sensitive business info

**Get Started:**
- Try: "Search the knowledge base for PHP best practices"
- Try: "Find code examples of database connections"
- Try: "Store a note about [topic]"

Enjoy! 🚀
```

---

## 🔧 Step 5: Install Required Extensions

Open VS Code and install:

1. **GitHub Copilot** (required)
   - `github.copilot`

2. **GitHub Copilot Chat** (required)
   - `github.copilot-chat`

3. **PHP Intelephense** (recommended)
   - `bmewburn.vscode-intelephense-client`

4. **Remote - SSH** (if working on remote servers)
   - `ms-vscode-remote.remote-ssh`

**Quick install via command:**
```bash
code --install-extension github.copilot
code --install-extension github.copilot-chat
code --install-extension bmewburn.vscode-intelephense-client
code --install-extension ms-vscode-remote.remote-ssh
```

---

## ✅ Step 6: Verify Setup

### Test MCP Connection:
1. Reload VS Code: `Ctrl+Shift+P` → "Developer: Reload Window"
2. Open Copilot Chat
3. Send: "Test MCP connection"
4. You should see MCP tools being used

### Test Conversation Memory:
1. In Copilot Chat, say: "Remember: my favorite color is blue"
2. Close and reopen VS Code
3. Ask: "What's my favorite color?"
4. It should remember!

### Check Theme:
- Editor should have dark grey background
- Sidebar slightly lighter grey
- Activity bar darker grey

---

## 📞 Troubleshooting

### MCP Not Connecting?
```bash
# Test SSH connection manually:
ssh master_anjzctzjhr@hdgwrzntwa "echo 'Connection successful'"

# If that fails, SSH keys aren't set up
```

### Copilot Not Working?
- Check GitHub Copilot subscription is active
- Sign in: `Ctrl+Shift+P` → "GitHub Copilot: Sign In"

### Settings Not Loading?
- Make sure you saved `settings.json` in the correct location
- Check for JSON syntax errors (missing comma, bracket, etc.)
- Use online JSON validator if unsure

### Prompts Not Appearing?
- Check file location: `%APPDATA%\Code\User\prompts\`
- Files must end with `.instructions.md`
- Files must have YAML frontmatter with `applyTo: '**'`

---

## 🎁 Complete Files Package

### File 1: `FRIENDS_VSCODE_SETTINGS.json`
(See attached file - full settings with MCP configured for PROJECT_ID=999)

### File 2: `MCP_CONTEXT.instructions.md`
(See above - copy to prompts folder)

### File 3: `FRIENDS_WELCOME.instructions.md`
(See above - copy to prompts folder)

---

## 🚀 Quick Start Checklist

- [ ] Backup current VS Code settings
- [ ] Copy new `settings.json` to user folder
- [ ] Create `prompts` folder and add instruction files
- [ ] Install required extensions (Copilot, Copilot Chat)
- [ ] Set up SSH key and test connection
- [ ] Reload VS Code
- [ ] Open Copilot Chat and test MCP connection
- [ ] Verify theme looks good
- [ ] Send "Remember: test message" and verify memory works

---

## 💡 Pro Tips for Your Friend

1. **Use MCP Tools Often**: Don't guess - search, query, and verify
2. **Build Your Knowledge Base**: Store useful snippets with `kb.add_document`
3. **Semantic Search is Powerful**: Find code patterns across thousands of files
4. **Conversation Memory is Gold**: The system remembers context across sessions
5. **Ask for Help**: The AI agent can guide you through complex tasks

---

## 🔒 Security Notes

- SSH keys are private - never share your private key
- API keys are in settings - keep `settings.json` secure
- Sandbox mode is isolated from production
- Don't commit `settings.json` to public repos

---

## 📧 Need Help?

If your friend gets stuck, they can:
1. Check the troubleshooting section above
2. Ask in Copilot Chat: "Help me debug my MCP setup"
3. Contact you for SSH access or config help

---

**Ready to send to your friend!** 🎉

Package includes:
- ✅ This guide
- ✅ Complete settings.json (next file)
- ✅ Prompt instruction files
- ✅ Step-by-step setup
- ✅ Troubleshooting tips
