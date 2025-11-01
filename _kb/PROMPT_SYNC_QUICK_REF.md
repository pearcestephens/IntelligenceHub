# 🚀 PROMPT SYNC QUICK REFERENCE

## ✅ IT'S WORKING!

Prompts automatically sync to: `~/.vscode-server/data/User/prompts/`

---

## 📝 QUICK COMMANDS

### Check What's Synced
```bash
ls -lh ~/.vscode-server/data/User/prompts/
```

### Force Sync Now
```bash
cd /home/master/applications/hdgwrzntwa/public_html
php universal-copilot-automation.php --update-vscode
```

### View Logs
```bash
tail -50 logs/copilot-automation.log
```

### View a Prompt
```bash
cat ~/.vscode-server/data/User/prompts/AGENT_SYSTEM_MAINTAINER_QUICK.instructions.md
```

---

## 🎯 HOW TO USE IN VS CODE

### Reference in Chat:
```
@workspace #file:AGENT_SYSTEM_MAINTAINER_QUICK.instructions.md
```

### VS Code Auto-Loads:
All `.instructions.md` files in prompts directory are automatically available!

---

## 📋 CURRENT PROMPTS

1. `AGENT_SYSTEM_MAINTAINER_QUICK.instructions.md` ← **YOUR main prompt!**
2. `MCP-TOOLS-MANDATE.instructions.md`
3. `KB-REFRESH-CONTEXT.instructions.md`
4. `CIS-BOT-CONSTITUTION.instructions.md`
5. `AUTOMATION-SYSTEM.instructions.md`
6. `SECURITY-STANDARDS.instructions.md`

---

## 🔄 AUTO-SYNC SCHEDULE

- **Every 5 minutes** - Quick sync
- **Every hour** - Full automation with all tasks
- **Manual anytime** - Run command above

---

## 🎨 NEXT: DASHBOARD RULE GENERATOR

Location: `/home/master/applications/hdgwrzntwa/public_html/dashboard/prompt-rules.php`

**Ready to improve with:**
- CIS-specific rules (properly worded)
- Rule categories
- Add/edit interface
- Live preview
- Auto-export to .instructions.md
- Auto-sync to VS Code

**Just say when!**

---

## ✅ STATUS

🟢 **OPERATIONAL** - Syncing every 5 minutes automatically!
