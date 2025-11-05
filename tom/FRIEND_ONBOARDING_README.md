# 🎁 Friend Onboarding Package - Complete Setup

**Version:** 2.0.0 - Hardened & Automated
**Last Updated:** November 5, 2025
**Status:** ✅ Production Ready

---

## 📦 What's Included

This package contains everything your friend needs to get started with:
- ✅ **Intelligence Hub MCP** (50+ tools, conversation memory, semantic search)
- ✅ **GitHub Copilot** (optimized settings, custom prompts)
- ✅ **Workspace Optimization** (file indexing, semantic search enablement)
- ✅ **Security Hardening** (permissions, gitignore, secret management)
- ✅ **Dark Theme** (matching your setup)
- ✅ **Sandbox Environment** (PROJECT_ID=999 - safe testing)

---

## 🚀 Installation Methods

### Method 1: AUTOMATED (Recommended) ⚡

**One command does everything:**

```bash
bash FRIEND_ONBOARDING_INSTALLER.sh
```

**Time:** ~2 minutes
**Difficulty:** Easy
**Steps:** 8 automated checks + install

**What it does:**
1. ✓ Checks prerequisites (VS Code, SSH, Node.js)
2. ✓ Tests SSH connection to MCP server
3. ✓ Backs up existing VS Code settings
4. ✓ Installs hardened settings.json with MCP config
5. ✓ Sets up prompt instructions
6. ✓ Scans and indexes workspace files (for semantic search)
7. ✓ Installs VS Code extensions (Copilot, PHP, SSH)
8. ✓ Applies security hardening (permissions, .gitignore)

### Method 2: Manual (Full Control) 📝

**Follow step-by-step guide:**

See `ONBOARDING_PACKAGE_FOR_FRIENDS.md` for complete manual instructions.

**Time:** ~20 minutes
**Difficulty:** Medium
**Best for:** Learning what each setting does

---

## 📂 Package Files

```
📦 Friend Onboarding Package
├── 🚀 FRIEND_ONBOARDING_INSTALLER.sh        (Automated installer)
├── 📖 ONBOARDING_PACKAGE_FOR_FRIENDS.md     (Complete manual guide)
├── 📖 FRIEND_ONBOARDING_README.md           (This file)
├── 📖 ONBOARDING_COMPLETE_SUMMARY.md        (System overview)
└── 🏖️ SANDBOX_QUICK_GUIDE.md                (Sandbox documentation)
```

---

## 🏖️ Sandbox Mode (PROJECT_ID=999)

Your friend will be set up in **SANDBOX MODE** by default:

**✅ What they CAN do:**
- Use all 50+ MCP tools (semantic_search, conversation memory, kb.*, etc.)
- Search the shared knowledge base (read-only)
- Store their own notes and learnings
- Access public documentation
- Test features safely

**❌ What's PROTECTED:**
- Internal CIS data (PROJECT_ID=1)
- Production databases
- Sensitive business information
- Other users' private data

**Perfect for:**
- Learning the system
- Testing features
- External developers
- Friends experimenting

---

## 🔐 Security Features

### Hardened by Default:

1. **Settings File Protection**
   - `chmod 600` on settings.json
   - API keys isolated (can be moved to env vars)
   - .gitignore entry created automatically

2. **SSH Key Management**
   - User generates their own keys
   - Public key sharing only
   - No password authentication

3. **Sandbox Isolation**
   - PROJECT_ID=999 (isolated environment)
   - Read-only access to shared resources
   - Private conversation history

4. **No Secrets in Repos**
   - Automated .gitignore creation
   - Settings excluded from git
   - API keys can be externalized

---

## 🎓 Prerequisites

### Required:
- ✅ VS Code installed
- ✅ SSH client (OpenSSH)
- ✅ SSH access to server (send public key to admin)

### Recommended:
- ✅ Node.js 14+ (for MCP wrapper)
- ✅ GitHub Copilot subscription
- ✅ Git (for version control)

### Optional:
- PHP 8+ (if working on PHP projects)
- MySQL client (if working with databases)

---

## 🚀 Quick Start for Your Friend

### Step 1: Get SSH Access

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "friend@example.com"

# Send public key to admin
cat ~/.ssh/id_ed25519.pub
```

**Send the public key output to you via email/chat.**

### Step 2: Run Installer

```bash
# Download installer
curl -O https://staff.vapeshed.co.nz/FRIEND_ONBOARDING_INSTALLER.sh

# Run it
bash FRIEND_ONBOARDING_INSTALLER.sh

# Or specify workspace
bash FRIEND_ONBOARDING_INSTALLER.sh /path/to/project
```

### Step 3: Reload VS Code

```
Ctrl+Shift+P (or Cmd+Shift+P on Mac)
→ "Developer: Reload Window"
```

### Step 4: Test MCP Connection

```
1. Open Copilot Chat (Ctrl+Alt+I or Cmd+Opt+I)
2. Send: "Test MCP connection"
3. You should see MCP tools being used
4. Try: "Search for PHP database examples"
```

---

## ✅ Verification Checklist

After installation, verify:

- [ ] VS Code settings.json exists and is readable
- [ ] Prompt files exist in `prompts/` directory
- [ ] SSH connection to MCP server works
- [ ] Copilot Chat opens without errors
- [ ] MCP tools appear in chat (look for tool usage)
- [ ] Semantic search works ("Find all PHP files")
- [ ] Conversation memory persists (reload VS Code and ask "What did we discuss?")
- [ ] Theme is dark grey
- [ ] Extensions installed (Copilot, Copilot Chat, Intelephense)

---

## 🎯 What They Get

### MCP Tools (50+ available):

**Conversation & Memory:**
- `conversation.get_project_context` - Load past discussions
- `conversation.search` - Search conversation history
- `memory.store` - Save important info

**Knowledge Base:**
- `kb.search` - Search documentation
- `kb.add_document` - Add new docs
- `kb.list` - List all docs

**Code Search:**
- `semantic_search` - Find code patterns
- `fs.read` - Read files
- `fs.list` - List directory contents

**Database:**
- `db.query` - Execute SQL (read-only in sandbox)
- `db.schema` - View table structures

**AI Agent:**
- `ai_agent.query` - Full AI with RAG capabilities

### Workspace Optimization:

- **File Index:** JSON index of all workspace files
- **Semantic Search:** Fast code pattern finding
- **Conversation Memory:** Persistent across sessions
- **Context Awareness:** AI knows project structure

---

## 📞 Troubleshooting

### "SSH connection failed"

```bash
# Test connection manually
ssh master_anjzctzjhr@hdgwrzntwa "echo success"

# If fails: Send public key to admin
cat ~/.ssh/id_ed25519.pub
```

### "MCP not connecting"

1. Reload VS Code: `Ctrl+Shift+P` → "Developer: Reload Window"
2. Check SSH works (see above)
3. Check Node.js installed: `node --version` (need 14+)
4. Check settings.json has MCP_API_KEY filled in

### "Copilot not working"

1. Check subscription: Sign in to GitHub
2. Install extensions: `code --install-extension github.copilot`
3. Reload VS Code

### "Semantic search not finding files"

1. Check workspace indexed: `.vscode/file_index.json` exists
2. Re-run installer to regenerate index
3. Make sure you're in correct workspace folder

---

## 🔄 Updating the Package

To update an existing installation:

```bash
# Backup current settings
cp ~/.config/Code/User/settings.json settings.backup

# Re-run installer
bash FRIEND_ONBOARDING_INSTALLER.sh

# Compare settings if needed
diff settings.backup ~/.config/Code/User/settings.json
```

---

## 📚 Documentation Links

- **Main Guide:** `ONBOARDING_PACKAGE_FOR_FRIENDS.md`
- **Sandbox Guide:** `SANDBOX_QUICK_GUIDE.md`
- **System Overview:** `ONBOARDING_COMPLETE_SUMMARY.md`
- **Installer Source:** `FRIEND_ONBOARDING_INSTALLER.sh`

---

## 🤝 Support

### For Your Friend:

1. **Read the docs** - Start with `ONBOARDING_PACKAGE_FOR_FRIENDS.md`
2. **Ask Copilot** - It can help debug setup issues
3. **Check troubleshooting** - Common issues listed above
4. **Contact admin** - For SSH access or MCP API key issues

### For You (Admin):

1. **Grant SSH access** - Add public key to `~/.ssh/authorized_keys`
2. **Verify sandbox** - Check PROJECT_ID=999 is isolated
3. **Monitor usage** - Check MCP logs if needed
4. **Share package** - Send them the installer script

---

## 🎉 Success Looks Like

When everything is working, your friend will:

✅ Open VS Code → Copilot Chat
✅ Send: "Search the codebase for authentication examples"
✅ See: MCP tools being called, results returned
✅ Send: "Remember: I'm working on user auth"
✅ Close VS Code, reopen, ask: "What am I working on?"
✅ Get: "You're working on user authentication"

**That's the power of persistent memory + MCP!** 🚀

---

## 📊 Stats

**Installation Time:** ~2 minutes (automated) or ~20 minutes (manual)
**Files Created:** 3-5 (settings.json, prompts, index)
**Extensions Installed:** 4 (Copilot, Copilot Chat, Intelephense, SSH)
**MCP Tools Available:** 50+
**Security Checks:** 8 automated
**Indexed Files:** All .php, .js, .json, .md in workspace

---

## 🚀 Ready to Send!

**Send your friend:**
1. ✅ `FRIEND_ONBOARDING_INSTALLER.sh` (the automated installer)
2. ✅ `ONBOARDING_PACKAGE_FOR_FRIENDS.md` (manual guide)
3. ✅ This file (`FRIEND_ONBOARDING_README.md`)
4. ✅ Instructions: "Run the installer, then read the docs if needed"

**Or just send the installer URL:**
```
https://staff.vapeshed.co.nz/FRIEND_ONBOARDING_INSTALLER.sh
```

---

**Version:** 2.0.0
**Status:** ✅ Production Ready
**Last Updated:** November 5, 2025
**Maintainer:** Ecigdis Limited - Intelligence Hub Team
