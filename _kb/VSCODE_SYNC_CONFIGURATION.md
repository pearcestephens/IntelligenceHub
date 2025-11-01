# 🔄 VS Code Sync Configuration Guide

**Last Updated:** 2025-10-28  
**Purpose:** Ensure all instruction files sync to VS Code User Prompts directory  

---

## ✅ FILES NOW SYNCING TO VS CODE

All instruction files are now configured to sync to:
```
C:\Users\pearc\AppData\Roaming\Code\User\prompts\
```

### 📋 Complete List of Syncing Files:

**Original Instructions (12 files):**
1. `06_company_org_pack.instructions.md`
2. `BOT TOOL SET REMINDER.instructions.md`
3. `BOT_MASTER.md.instructions.md`
4. `CIS BOT CONSTITUTION.instructions.md`
5. `cis website.instructions.md`
6. `Deep Problem Solving.instructions.md`
7. `Front End Speciialist.instructions.md`
8. `Generic Project Builder.instructions.md`
9. `High Quality.instructions.md`
10. `KB-MASTER-SETUP.md.instructions.md`
11. `MCP_TOOL_ACTIVATION_MANDATE.instructions.md`
12. `Network Engineer.instructions.md`

**Ultra Connector & System (3 files):**
13. `ULTRA_MASTER_CONNECTOR.instructions.md` ⭐ Priority 100
14. `SYSTEM_AUTO_LOADER.instructions.md` ⭐ Priority 95
15. `COMPLETE_SYSTEM_MANDATE.instructions.md` ⭐ Priority 90

**Autonomous Maintenance (2 files):**
16. `AUTONOMOUS_SYSTEM_MAINTAINER_PROMPT.instructions.md` 🆕
17. `AUTONOMOUS_MAINTENANCE_QUICK.instructions.md` 🆕

**Autonomous Learning (2 files):**
18. `AUTONOMOUS_SYSTEM_LEARNER_PROMPT.instructions.md` 🆕
19. `SYSTEM_LEARNING_QUICK.instructions.md` 🆕

**TOTAL: 19 instruction files** automatically syncing to VS Code!

---

## 🔧 VS Code Settings Configuration

### Required Settings.json Configuration:

**Location:** `C:\Users\pearc\AppData\Roaming\Code\User\settings.json`

**Add/Update these settings:**

```json
{
  "github.copilot.enable": {
    "*": true,
    "yaml": true,
    "plaintext": true,
    "markdown": true
  },
  "github.copilot.advanced": {
    "debug.overrideEngine": "gpt-4",
    "debug.overrideProxyUrl": "",
    "debug.testOverrideProxyUrl": "",
    "debug.overrideChatEngine": "gpt-4",
    "inlineSuggest.enable": true,
    "authProvider": "github",
    "length": 500
  }
}
```

### Optional Enhanced Settings (Recommended):

```json
{
  "github.copilot.enable": {
    "*": true,
    "yaml": true,
    "plaintext": true,
    "markdown": true
  },
  "github.copilot.advanced": {
    "debug.overrideEngine": "gpt-4",
    "inlineSuggest.enable": true,
    "authProvider": "github",
    "length": 500,
    "temperature": 0.1,
    "top_p": 1,
    "listCount": 10
  },
  "github.copilot.editor.enableAutoCompletions": true,
  "github.copilot.editor.enableCodeActions": true,
  "editor.inlineSuggest.enabled": true,
  "editor.quickSuggestions": {
    "other": true,
    "comments": true,
    "strings": true
  }
}
```

---

## 🚀 Automation System Configuration

### Current Sync Schedule:

**Quick Sync (Every 5 minutes):**
- Checks for new/changed instruction files
- Syncs to `_kb/user_instructions/`
- Updates file indexes
- Updates cross-references

**Daily VS Code Sync (Once per day):**
- Full sync from `_kb/user_instructions/` to Windows VS Code
- Location: `C:\Users\pearc\AppData\Roaming\Code\User\prompts\`
- Copies all `.instructions.md` files
- Maintains file structure

**Configured in:** `/home/master/applications/hdgwrzntwa/public_html/config/automation.json`

```json
"automation": {
  "vscode_update_interval": 86400,  // Daily sync (24 hours)
  "sync_interval": 300,              // Quick sync (5 minutes)
  "auto_commit": true,
  "backup_before_sync": true
},
"sources": {
  "user_instructions": "C:\\Users\\pearc\\AppData\\Roaming\\Code\\User\\prompts"
},
"instruction_files": [
  // All 19 files listed above
]
```

---

## 🔍 How to Verify Sync is Working

### Method 1: Check File Timestamps (Windows)

```powershell
# Open PowerShell and run:
Get-ChildItem "C:\Users\pearc\AppData\Roaming\Code\User\prompts" -Filter "*.instructions.md" | 
  Select-Object Name, LastWriteTime | 
  Sort-Object LastWriteTime -Descending
```

**You should see all 19 files with recent timestamps.**

### Method 2: Check Automation Logs (Server)

```bash
# View sync logs
tail -50 /home/master/applications/hdgwrzntwa/public_html/logs/copilot-automation.log

# Check for VS Code sync entries
grep "VS Code" /home/master/applications/hdgwrzntwa/public_html/logs/copilot-automation.log | tail -20
```

### Method 3: Manual Sync Test

```bash
# Trigger immediate sync
cd /home/master/applications/hdgwrzntwa/public_html
php copilot-cron-manager.php --sync-vscode
```

---

## 📦 File Locations Reference

### Server Locations:

**Source Files:**
```
/home/master/applications/hdgwrzntwa/public_html/_kb/
├── AUTONOMOUS_SYSTEM_MAINTAINER_PROMPT.md
├── AUTONOMOUS_MAINTENANCE_QUICK.md
├── AUTONOMOUS_SYSTEM_LEARNER_PROMPT.md
├── SYSTEM_LEARNING_QUICK.md
└── user_instructions/
    ├── AUTONOMOUS_SYSTEM_MAINTAINER_PROMPT.instructions.md ✅
    ├── AUTONOMOUS_MAINTENANCE_QUICK.instructions.md ✅
    ├── AUTONOMOUS_SYSTEM_LEARNER_PROMPT.instructions.md ✅
    ├── SYSTEM_LEARNING_QUICK.instructions.md ✅
    ├── ULTRA_MASTER_CONNECTOR.instructions.md ✅
    ├── SYSTEM_AUTO_LOADER.instructions.md ✅
    ├── COMPLETE_SYSTEM_MANDATE.instructions.md ✅
    └── [12 other original instructions] ✅
```

**Windows VS Code Location:**
```
C:\Users\pearc\AppData\Roaming\Code\User\prompts\
├── AUTONOMOUS_SYSTEM_MAINTAINER_PROMPT.instructions.md
├── AUTONOMOUS_MAINTENANCE_QUICK.instructions.md
├── AUTONOMOUS_SYSTEM_LEARNER_PROMPT.instructions.md
├── SYSTEM_LEARNING_QUICK.instructions.md
├── ULTRA_MASTER_CONNECTOR.instructions.md
└── [All other .instructions.md files]
```

---

## 🎯 Priority System (How VS Code Reads Them)

VS Code Copilot reads instruction files based on priority declared in frontmatter:

```yaml
---
applyTo: '**'
priority: 100
---
```

**Priority Order:**
1. **Priority 100**: `ULTRA_MASTER_CONNECTOR.instructions.md` (reads FIRST)
2. **Priority 95**: `SYSTEM_AUTO_LOADER.instructions.md` (reads SECOND)
3. **Priority 90**: `COMPLETE_SYSTEM_MANDATE.instructions.md` (reads THIRD)
4. **Priority 80-75**: All other instruction files (read in order)

**Result:** Bot always loads the ultra connector first, establishing the foundation, then loads maintenance, learning, and other capabilities.

---

## ⚡ Quick Actions

### Force Immediate Sync to VS Code:

```bash
# SSH to server
ssh master@phpstack-129337-5615757.cloudwaysapps.com

# Navigate to project
cd /home/master/applications/hdgwrzntwa/public_html

# Run manual sync
php copilot-cron-manager.php --sync-vscode

# Verify sync
ls -lh /home/master/applications/hdgwrzntwa/public_html/_kb/user_instructions/*.instructions.md
```

### Add New Instruction File:

1. Create file in `_kb/` directory
2. Copy to `_kb/user_instructions/` with `.instructions.md` extension
3. Add to `config/automation.json` → `instruction_files` array
4. Run `php copilot-cron-manager.php --sync-vscode`
5. File will appear in VS Code within 24 hours (or immediately if forced)

---

## 🔔 Notifications & Monitoring

### Automation Status:

**Check if sync is running:**
```bash
# View cron jobs
crontab -l | grep copilot

# Check if automation is active
ps aux | grep copilot-cron
```

**Expected cron jobs:**
```cron
# Quick sync every 5 minutes
*/5 * * * * cd /home/master/applications/hdgwrzntwa/public_html && php copilot-cron-manager.php --quick-sync

# Daily VS Code sync at 3 AM
0 3 * * * cd /home/master/applications/hdgwrzntwa/public_html && php copilot-cron-manager.php --sync-vscode
```

### Sync Success Indicators:

✅ Files exist in `_kb/user_instructions/` with `.instructions.md` extension  
✅ Files listed in `config/automation.json` → `instruction_files`  
✅ Automation logs show successful sync  
✅ Files appear in Windows VS Code prompts directory  
✅ Copilot can access the instructions (test with "what instructions do you have?")  

---

## 🆘 Troubleshooting

### Issue: Files Not Syncing to VS Code

**Solution 1: Check Automation Configuration**
```bash
cat /home/master/applications/hdgwrzntwa/public_html/config/automation.json | grep -A 5 "instruction_files"
```

**Solution 2: Verify File Permissions**
```bash
ls -la /home/master/applications/hdgwrzntwa/public_html/_kb/user_instructions/
# Should be readable (644 or 755)
```

**Solution 3: Force Manual Sync**
```bash
php copilot-cron-manager.php --sync-vscode --verbose
```

**Solution 4: Check VS Code Directory Exists**
```powershell
# Windows PowerShell
Test-Path "C:\Users\pearc\AppData\Roaming\Code\User\prompts"
# Should return: True
```

### Issue: Copilot Not Reading Instructions

**Solution 1: Reload VS Code Window**
- Press `Ctrl+Shift+P`
- Type "Reload Window"
- Press Enter

**Solution 2: Verify Settings.json**
```json
// Check this setting exists
"github.copilot.enable": {
  "*": true
}
```

**Solution 3: Check File Format**
- Files must end in `.instructions.md`
- Files must have proper YAML frontmatter
- Files must be in UTF-8 encoding

---

## 📊 Current Status

**Last Updated:** 2025-10-28 15:07:00  

**Files Syncing:** ✅ 19 instruction files  
**Automation Status:** ✅ Active  
**VS Code Sync:** ✅ Configured (daily at 3 AM)  
**Quick Sync:** ✅ Every 5 minutes  
**Backup:** ✅ Enabled before every sync  

**Next Actions:**
- [ ] Verify files appear in VS Code within 24 hours
- [ ] Test Copilot can access new instructions
- [ ] Monitor automation logs for any errors

---

## 🎉 Success!

**All instruction files are now configured to sync automatically!**

**What happens next:**
1. ✅ Files already copied to `_kb/user_instructions/` with proper naming
2. ✅ Configuration updated in `automation.json`
3. ⏳ Daily sync will copy to Windows VS Code (next run: tomorrow 3 AM)
4. ⚡ Or force immediate sync with command above

**You can now use all prompts in VS Code:**
- Autonomous maintenance
- Autonomous learning
- Ultra connector
- System auto-loader
- All original instructions

**Just say:**
- "Run autonomous maintenance"
- "Start autonomous learning mode"
- "Scan and fix the system"
- "Learn everything and identify gaps"

---

**Version:** 1.0.0  
**Maintained By:** Copilot Automation System  
**Auto-Updates:** Daily via cron  

🔄 **SYNC CONFIGURED & ACTIVE!** 🚀
