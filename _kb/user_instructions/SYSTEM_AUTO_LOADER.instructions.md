---
applyTo: '**'
priority: 95
description: 'AUTO-LOADER - Ensures bots always load latest documentation on session start'
---

# 🔄 INTELLIGENCE HUB - AUTO-LOADER PROTOCOL

**Priority:** 95 (CRITICAL - Runs BEFORE other instructions)  
**Purpose:** Force bots to load latest knowledge base documentation  
**Last Updated:** 2025-10-28  

---

## 🚨 MANDATORY SESSION START SEQUENCE

**EVERY TIME YOU START A SESSION, YOU MUST:**

### Step 1: Read Master Index (30 seconds)
```
File: /home/master/applications/hdgwrzntwa/public_html/_kb/MASTER_INDEX.md
Action: SCAN for new/updated documentation
Look for: ⭐ NEW, 🔄 UPDATED, 📢 CRITICAL markers
```

### Step 2: Load Core Instructions (2 minutes)
```
MUST READ IN ORDER:
1. COMPLETE_SYSTEM_MANDATE.instructions.md (8KB - ALL 50+ tools)
2. COMPLETE_TOOLS_ECOSYSTEM.md (18KB - Comprehensive guide)
3. MCP_TOOLS_COMPLETE_GUIDE.md (42KB - MCP mastery)
```

### Step 3: Health Check (10 seconds)
```bash
curl https://gpt.ecigdis.co.nz/mcp/health.php
```

### Step 4: Announce Ready
```
"✅ Auto-loader complete. System knowledge current. Ready to work."
```

---

## 📚 DOCUMENTATION HIERARCHY

### LEVEL 1: CRITICAL (Read EVERY session)
```
_kb/user_instructions/COMPLETE_SYSTEM_MANDATE.instructions.md
- Contains: ALL 50+ tools, session management, protocols
- Size: 8KB
- Read time: 10 minutes
- Status: ⭐⭐⭐ MANDATORY
```

### LEVEL 2: ESSENTIAL (Read when working with tools)
```
_kb/COMPLETE_TOOLS_ECOSYSTEM.md
- Contains: MCP API, Dashboard, Frontend, Credential Manager, Standalone
- Size: 18KB
- Read time: 30 minutes
- Status: ⭐⭐ HIGHLY RECOMMENDED
```

### LEVEL 3: REFERENCE (Read as needed)
```
_kb/MCP_TOOLS_COMPLETE_GUIDE.md
- Contains: Detailed MCP API documentation
- Size: 42KB
- Read time: 40 minutes
- Status: ⭐ REFERENCE
```

### LEVEL 4: SPECIALIZED (Read for specific tasks)
```
frontend-tools/BOT_PROFILE_ACCESS_GUIDE.md - Bot authentication
frontend-tools/INTERACTIVE_CRAWLER.md - Crawler control
bots/tools/*.md - 100+ specialized guides
```

---

## 🔄 VERSION CHECK PROTOCOL

**EVERY 20 MESSAGES, CHECK FOR UPDATES:**

```bash
# Check latest version
cat /home/master/applications/hdgwrzntwa/public_html/_kb/MASTER_INDEX.md | grep "Last Updated:"

# If date newer than your session start date:
# 1. Read MASTER_INDEX.md again
# 2. Check for ⭐ NEW or 🔄 UPDATED markers
# 3. Read updated documentation
# 4. Announce: "Documentation refreshed. [X] files updated."
```

---

## 🎯 QUICK REFERENCE SYSTEM

### When User Asks "What tools do you have?"
```
Response:
"I have 50+ tools across 5 categories:
1. MCP API Tools (13) - Search, analyze, retrieve
2. Dashboard Tools (23) - MySQL, MCP, AI chat, crawler, logs
3. Frontend Tools (10+) - Bot profiles, interactive crawler, audit, SSE
4. Credential Manager (1) - Password safe
5. Standalone Tools (8+) - Analyzers, control panels, automation

Full catalog: /home/master/applications/hdgwrzntwa/public_html/_kb/COMPLETE_TOOLS_ECOSYSTEM.md"
```

### When User Asks "How do I [TASK]?"
```
1. Search COMPLETE_SYSTEM_MANDATE.instructions.md for quick reference
2. If not found, search COMPLETE_TOOLS_ECOSYSTEM.md
3. If still not found, use semantic_search MCP tool
4. Never say "I don't know" - USE THE TOOLS!
```

### When User Says "STOP" or "CHANGE SUBJECT"
```
IMMEDIATE ACTION:
1. Stop current operation (mid-sentence if needed)
2. Save progress to /tmp/bot-session-[timestamp].json
3. Document state (completed, remaining, current position)
4. Confirm: "Stopped. Progress saved."

(See COMPLETE_SYSTEM_MANDATE.instructions.md for full protocol)
```

---

## 🚫 ANTI-AMNESIA SYSTEM

**Triggers:** If you find yourself saying any of these:

❌ "I don't have access to..."  
❌ "I can't see the codebase..."  
❌ "I'm unable to check..."  
❌ "I don't know where that is..."  

**EMERGENCY PROTOCOL:**
```
1. STOP immediately
2. Re-read COMPLETE_SYSTEM_MANDATE.instructions.md (8KB)
3. Verify you remember all 13 MCP tools
4. Test health_check tool
5. THEN answer user's question
```

**If you say any of the above phrases, you have AMNESIA. FIX IT!**

---

## 📊 SESSION STATE TRACKING

**Store in memory throughout session:**

```json
{
  "session_start": "2025-10-28T14:30:00Z",
  "docs_loaded": [
    "COMPLETE_SYSTEM_MANDATE.instructions.md",
    "COMPLETE_TOOLS_ECOSYSTEM.md",
    "MCP_TOOLS_COMPLETE_GUIDE.md"
  ],
  "last_version_check": "2025-10-28T14:50:00Z",
  "tools_used_count": 23,
  "health_checks_passed": 3,
  "current_task": "Working on user request",
  "context_saved_to": "/tmp/bot-session-1730126400.json"
}
```

**Update every 10 messages**

---

## 🔧 TOOL USAGE ENFORCEMENT

**MINIMUM TOOL USAGE RATE: 80% of responses**

**After every 5 messages, self-check:**
```
□ Did I use tools in last 5 messages?
□ Did I search before answering?
□ Did I check health_check at session start?
□ Am I following session management protocols?

If any NO → USE TOOLS NOW!
```

**Acceptable tool usage examples:**
- semantic_search to find code
- find_code to locate functions
- get_file_content to read files
- health_check for system status
- get_analytics for usage patterns
- analyze_file for code understanding

**Not acceptable:**
- "I don't have access" (YOU DO!)
- "Let me think..." without using tools
- Generic answers without searching KB
- Assumptions without verification

---

## 🎓 TRAINING VERIFICATION

**Before starting ANY work, verify you can:**

□ List all 5 tool categories  
□ Name at least 10 specific tools  
□ Explain session management (STOP/CHANGE/RESUME)  
□ Access MCP API via JSON-RPC 2.0  
□ Use semantic_search tool  
□ Read files from KB  
□ Never say "I don't have access"  

**If any ❌, re-read COMPLETE_SYSTEM_MANDATE.instructions.md NOW!**

---

## 🔄 AUTO-UPDATE NOTIFICATIONS

**When KB documentation updates, you'll receive broadcast:**

```
From: broadcast-to-all-copilots.sh
Priority: HIGH
Message: "KB UPDATE: [FILENAME] has been updated. Review latest changes at [PATH]"

Action Required:
1. Read updated file
2. Note changes
3. Update working knowledge
4. Acknowledge: "Update received and processed: [FILENAME]"
```

---

## 📢 BROADCAST SYSTEM INTEGRATION

**This system is maintained by:**

- **Automation:** Copilot automation system (every 5 minutes)
- **Manual:** broadcast-to-all-copilots.sh script
- **Sync:** Auto-sync to VS Code (daily)
- **Monitor:** copilot-cron-manager.php

**If you miss an update:**
```bash
# Check for broadcasts
ls -lt /home/master/applications/hdgwrzntwa/private_html/copilot-broadcasts/

# Read latest broadcast
cat /home/master/applications/hdgwrzntwa/private_html/copilot-broadcasts/latest.txt
```

---

## ✅ SUCCESS CRITERIA

**You're properly loaded when:**

✅ You know all 50+ tools are available  
✅ You can list them by category  
✅ You know how to use MCP API  
✅ You know session management protocols  
✅ You NEVER say "I don't have access"  
✅ You use tools in >80% of responses  
✅ You check for updates every 20 messages  

---

## 🚀 ACTIVATION COMPLETE

**After reading this auto-loader:**

1. ✅ Read MASTER_INDEX.md (scan for new docs)
2. ✅ Read COMPLETE_SYSTEM_MANDATE.instructions.md (8KB)
3. ✅ Test health_check MCP tool
4. ✅ Verify tool knowledge (list 10+ tools)
5. ✅ Announce: "Auto-loader complete. Ready to work."

**Every session. No exceptions. This is THE LAW.**

---

**Version:** 1.0.0  
**Priority:** 95 (CRITICAL)  
**Applies To:** ALL files (`**`)  
**Auto-Generated:** No (manually maintained)  
**Last Updated:** 2025-10-28  

---

🎯 **REMEMBER: This auto-loader runs FIRST. Always. Forever.** 🎯
