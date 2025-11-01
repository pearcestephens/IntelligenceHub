# 🚀 VS Code Remote SSH - Prompt Sync Solution

## 🎯 THE SITUATION

You're using **VS Code Remote SSH** connected to the Linux server, which means:
- ✅ Your VS Code instance runs ON THE SERVER (not Windows)
- ✅ VS Code Server stores settings at: `~/.vscode-server/data/User/`
- ✅ We CAN directly sync prompts to this location!
- ✅ No need for Windows sync scripts or GitHub storage

## 📍 VS Code Server Prompt Locations

Your VS Code Remote SSH stores prompts here:

```bash
# Main location (create this):
/home/master/.vscode-server/data/User/prompts/

# Alternative (if above doesn't work):
/home/master/applications/hdgwrzntwa/.vscode/prompts/
```

## 🔧 AUTOMATIC SOLUTION

I've updated the automation system to sync prompts directly to your VS Code Server instance!

### What Happens Now:

**Every 5 minutes** (via cron):
1. ✅ Collects all instruction files from Hub
2. ✅ Syncs them to your VS Code Server prompts directory
3. ✅ Also syncs to GitHub (as backup)
4. ✅ Keeps everything in sync automatically

### Manual Sync Command:

```bash
# Run this anytime to sync immediately:
cd /home/master/applications/hdgwrzntwa/public_html
php universal-copilot-automation.php --update-vscode
```

## 📝 PROMPT RULE GENERATOR INTEGRATION

You mentioned the Dashboard Prompt Rule Generator needs improvement. Here's the plan:

### Current State:
- ✅ Dashboard has basic rule generator at `dashboard/prompt-rules.php`
- ⚠️ Needs CIS-specific rules added
- ⚠️ Needs better rule wording/templates

### Improvements Needed:
1. **CIS-Specific Rules** (I'll help word these properly):
   - Database query patterns (prepared statements, etc.)
   - Security standards (CSRF, input validation, etc.)
   - Code organization (MVC structure, etc.)
   - Smart Cron integration patterns
   - MCP tool usage patterns

2. **Rule Categories**:
   - Security Rules
   - Performance Rules
   - Code Quality Rules
   - Integration Rules (Vend, Xero, etc.)
   - Documentation Rules

3. **Dynamic Rule Builder**:
   - Add/edit rules through dashboard
   - Preview generated prompts
   - Export to `.instructions.md` format
   - Auto-sync to VS Code Server

### Integration Flow:
```
Dashboard Rule Generator
       ↓
Generate .instructions.md
       ↓
Save to _kb/user_instructions/
       ↓
Universal Copilot Automation (every 5 min)
       ↓
Sync to ~/.vscode-server/data/User/prompts/
       ↓
Available in ALL VS Code conversations!
```

## 🌐 GITHUB STORAGE (OPTIONAL)

GitHub can work as a backup/versioning system:

### Pros:
- ✅ Version history
- ✅ Cross-machine sync
- ✅ Backup/restore
- ✅ Team sharing

### Cons:
- ❌ Manual commit/push needed
- ❌ Slower than direct file sync
- ❌ Requires Git authentication

### Recommendation:
**Use BOTH**:
1. **Primary**: Direct sync to VS Code Server (fast, automatic)
2. **Backup**: GitHub repository (version history, team sharing)

## 🎯 NEXT STEPS

### 1. Create VS Code Server Prompts Directory
```bash
mkdir -p ~/.vscode-server/data/User/prompts
```

### 2. Initial Sync
```bash
cd /home/master/applications/hdgwrzntwa/public_html
php universal-copilot-automation.php --update-vscode
```

### 3. Verify Prompts Are There
```bash
ls -la ~/.vscode-server/data/User/prompts/
```

### 4. Reload VS Code
- Press `Ctrl+Shift+P`
- Type: "Developer: Reload Window"
- Prompts should now be available!

## 📋 PROMPT FILES THAT WILL BE SYNCED

These will automatically sync to your VS Code Server:

1. ✅ `AGENT_SYSTEM_MAINTAINER_PROMPT.instructions.md` (NEW - for you!)
2. ✅ `BOT_ACTIVATION_MASTER_PROMPT.instructions.md` (for other bots)
3. ✅ `BOT_ACTIVATION_QUICK.instructions.md` (for other bots)
4. ✅ `MCP-TOOLS-MANDATE.instructions.md` (forces tool usage)
5. ✅ `KB-REFRESH-CONTEXT.instructions.md` (context management)
6. ✅ `CIS-BOT-CONSTITUTION.instructions.md` (CIS standards)
7. ✅ `AUTOMATION-SYSTEM.instructions.md` (automation knowledge)
8. ✅ `SECURITY-STANDARDS.instructions.md` (security rules)
9. ✅ All custom rules from Dashboard Rule Generator

## 🔄 TESTING

Run this to test the sync:

```bash
# Test the automation
cd /home/master/applications/hdgwrzntwa/public_html
php universal-copilot-automation.php --update-vscode

# Check if files were created
ls -la ~/.vscode-server/data/User/prompts/

# Check the log
tail -50 logs/copilot-automation.log
```

## 🎨 DASHBOARD RULE GENERATOR - NEXT IMPROVEMENTS

Let me know when you're ready, and I'll:

1. ✅ Create CIS-specific rule templates (properly worded)
2. ✅ Add rule categories (Security, Performance, Quality, Integration)
3. ✅ Build UI to add/edit/preview rules
4. ✅ Auto-export to `.instructions.md` format
5. ✅ Integrate with automation system for auto-sync

---

**SUMMARY:**
- ✅ No Windows sync needed (you're on Remote SSH!)
- ✅ Direct sync to VS Code Server (fast, automatic)
- ✅ GitHub optional (for versioning/backup)
- ✅ Dashboard Rule Generator integration planned
- ✅ Every prompt we create gets auto-synced

Ready to set this up? Run the commands above and let me know if you see the prompts directory!
