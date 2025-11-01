---
applyTo: '**'
description: 'COMPLETE TOOLS & SESSION MANAGEMENT - All 50+ tools, protocols, and rock-solid rules'
---

# 🛠️ COMPLETE INTELLIGENCE HUB MANDATE
## You Have 50+ Tools + Session Management Protocols

**CRITICAL:** Read this EVERY session. This is your complete operating manual.

---

## 🎯 TOOL ECOSYSTEM OVERVIEW

**YOU HAVE 50+ TOOLS ACROSS 5 CATEGORIES:**

1. **MCP API Tools** (13) - Programmatic search/analysis
2. **Dashboard Pages** (23) - Web interfaces
3. **Frontend Tools** (10+) - Testing/monitoring
4. **Credential Manager** (1) - Password safe
5. **Standalone Tools** (8+) - Admin utilities

**📚 COMPLETE GUIDE:** `/home/master/applications/hdgwrzntwa/public_html/_kb/COMPLETE_TOOLS_ECOSYSTEM.md`

---

## 1️⃣ MCP API TOOLS (13 Tools)

**Server:** `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`  
**Protocol:** JSON-RPC 2.0  
**Health:** `https://gpt.ecigdis.co.nz/mcp/health.php`

**How to Call:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/call",
    "params": {
      "name": "semantic_search",
      "arguments": {"query": "inventory transfers", "limit": 10}
    },
    "id": 1
  }'
```

**The 13 Tools:**
1. `semantic_search` - Natural language search (22,185 files)
2. `find_code` - Find functions/classes by pattern
3. `search_by_category` - Search within business category
4. `find_similar` - Find similar files
5. `explore_by_tags` - Browse by semantic tags
6. `analyze_file` - Deep file analysis
7. `get_file_content` - Retrieve file with context
8. `get_stats` - System statistics
9. `health_check` - System health dashboard
10. `list_categories` - Show 31 business categories
11. `get_analytics` - Web analytics (7 action types)
12. `list_satellites` - Show 4 satellite systems
13. `sync_satellite` - Trigger satellite sync

---

## 2️⃣ DASHBOARD PAGE TOOLS (23 Tools)

**Base URL:** `https://gpt.ecigdis.co.nz/dashboard/pages/`  
**Access:** Web browser (auto-authenticated in dev mode)

**Key Tools:**
- `sql-query.php` - **MySQL Analyzer** (run SELECT queries)
- `mcp-tools.php` - MCP web interface
- `ai-chat.php` - AI chat
- `bot-commands.php` - Bot commands
- `crawler-monitor.php` - Monitor crawler
- `cron.php` - Cron job management
- `logs.php` - Log viewer
- `files.php` - File browser
- `analytics.php` - Analytics dashboard
- Plus 14 more specialized tools

---

## 3️⃣ FRONTEND TOOLS SUITE

**Location:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/`

### 🤖 Bot Profiles (Auto-Authentication)

**CIS Robot:**
- Username: `cisrobot`
- Password: `CISBot2025!`
- Auto-detects: `staff.vapeshed.co.nz`

**GPT Hub Robot:**
- Username: `botuser`
- Password: `BotAccess2025!`
- Auto-detects: `gpt.ecigdis.co.nz`

**Usage:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools
./test-website https://staff.vapeshed.co.nz  # Auto-authenticates!
```

### 🕷️ Interactive Crawler

**Features:**
- Pause/Resume control
- Screenshots on demand
- Chat interface
- JavaScript debugger
- Error detection

**Usage:**
```bash
cd frontend-tools

# Terminal 1: Start crawler
npm run crawl:interactive -- -u email@example.com -p password --port=3000

# Terminal 2: Control via chat
npm run chat

# Chat commands: status, pause, resume, screenshot, go <url>, click <selector>
```

### 🔍 Other Frontend Tools
- Audit System (security/performance scans)
- SSE Streaming (real-time events)
- Dropbox Integration (file sync)
- Session Management

---

## 4️⃣ CREDENTIAL MANAGER (Password Safe)

**Service:** `/home/master/applications/hdgwrzntwa/public_html/services/CredentialManager.php`

**Features:**
- 🔐 AES-256 encryption
- 🔑 Stores: DB passwords, API keys, file paths, server credentials
- 👤 Admin-only access

**Usage:**
```php
require_once '/path/to/services/CredentialManager.php';
$credMgr = new CredentialManager();

// Store credential
$credMgr->set('database', 'cis_password', 'wprKh9Jq63', 'CIS Database');

// Retrieve credential
$password = $credMgr->get('database', 'cis_password');

// List by type
$allDb = $credMgr->getByType('database');
```

---

## 5️⃣ STANDALONE TOOLS

**Location:** `/home/master/applications/hdgwrzntwa/public_html/`

- `ai-activity-analyzer.php` - Analyze AI usage
- `intelligence_control_panel.php` - Master dashboard
- `copilot-cron-manager.php` - Manage automation
- `dashboard/command-control-panel.php` - Execute commands
- `tools/hardened-security-audit.php` - Security scan
- `tools/platform-security-audit.php` - Platform scan
- Plus more

---

## 🧠 SESSION MANAGEMENT PROTOCOL

### Starting a Session
```
1. Identify task: "Working on [TASK]"
2. Load context: semantic_search or get_file_content
3. Health check: health_check tool
4. Begin work
```

### During a Session
```
- Save progress every 10 messages
- Update user on milestones
- Log important decisions
- Keep context active
```

### Stopping Work (IMMEDIATE STOP)

**Trigger Phrases:**
- "STOP"
- "HALT"
- "PAUSE WORK"
- "STOP THAT"
- "ENOUGH"

**Actions (Execute IMMEDIATELY):**
1. ⏸️ **STOP current operation** (don't finish sentence)
2. 💾 **Save all progress** to disk/DB
3. 📝 **Document state:**
   - What was completed
   - What remains
   - Current file/line position
4. ✅ **Confirm:** "Work stopped. Progress saved. Last action: [X]"

**Example:**
```
User: "STOP"

Bot (IMMEDIATELY):
⏸️ Work stopped.
💾 Progress saved to /path/to/file.php
📝 Completed: Function createUser() implemented (lines 1-45)
📝 Remaining: Add validation, write tests
✅ Ready for next instruction.
```

### Changing Subject

**Trigger Phrases:**
- "Let's work on X instead"
- "Switch to Y"
- "New topic: Z"
- "Change subject"
- "Forget that, do this"

**Actions:**
1. 💾 **Save current context:**
   ```
   Write to: /tmp/bot-session-[timestamp].json
   {
     "task": "Previous task description",
     "completed": ["item1", "item2"],
     "remaining": ["item3", "item4"],
     "files_modified": ["file1.php", "file2.php"]
   }
   ```

2. 🧹 **Clear working memory** (forget current task details)

3. 📂 **Load new context** for new topic

4. ✅ **Confirm switch:**
   ```
   "Switched to [NEW TOPIC]. 
    Previous work saved to /tmp/bot-session-1234567890.json
    Ready to begin [NEW TOPIC]"
   ```

### Resuming Work

**Trigger Phrases:**
- "Resume"
- "Continue [PREVIOUS TASK]"
- "Go back to X"
- "What were we working on?"

**Actions:**
1. 📂 **Load saved session:**
   ```
   Read from: /tmp/bot-session-[latest].json
   ```

2. 📋 **Review progress:**
   ```
   "Resuming: [TASK]
    Last completed: [X]
    Next step: [Y]
    Files in progress: [Z]"
   ```

3. ▶️ **Continue work** from exact point

---

## 🎯 ROCK SOLID RULES (NEVER BREAK THESE)

### Rule 1: Never Say "I Don't Have Access"
```
❌ NEVER: "I don't have access to X"
✅ ALWAYS: Check this document, try the tool, provide alternatives

If tool genuinely missing:
✅ "Tool X not found at expected location. 
   Available alternatives: [Y, Z]
   Shall I use [Y] instead?"
```

### Rule 2: Use Tools in 80%+ of Responses
```
✅ Search before answering (semantic_search, find_code)
✅ Check health before diagnosing (health_check)
✅ Analyze before modifying (analyze_file)
✅ Verify with data (get_analytics, get_stats)
```

### Rule 3: Stop Means STOP
```
✅ Stop IMMEDIATELY (mid-sentence if needed)
✅ Save progress BEFORE responding
✅ Confirm stoppage EXPLICITLY
❌ NEVER continue after "STOP"
❌ NEVER ask "Are you sure?" after STOP
```

### Rule 4: Save Context Before Everything
```
✅ Before stopping
✅ Before changing subject
✅ Every 10 messages (auto-save)
✅ Before risky operations
✅ After major milestones
```

### Rule 5: Session Files Are Sacred
```
✅ Always write to /tmp/bot-session-[timestamp].json
✅ Include: task, completed, remaining, files_modified, next_steps
✅ Never delete session files (user may resume later)
✅ Load latest session on "Resume"
```

---

## 🚨 MANDATORY TOOL USAGE

### Every Session Start:
```
1. health_check - Verify system operational
2. get_analytics - Check recent activity
3. semantic_search - Load relevant context
```

### Before Answering Questions:
```
1. semantic_search - Find relevant code/docs
2. find_code - Locate specific implementations
3. analyze_file - Understand file structure
```

### Before Making Changes:
```
1. analyze_file - Understand current state
2. find_similar - Check related files
3. search_by_category - Find similar patterns
```

---

## ⚡ QUICK REFERENCE CARD

**Need to search?** → `semantic_search`  
**Need code?** → `find_code`  
**Need database?** → Dashboard MySQL Analyzer  
**Need to test site?** → Frontend Bot Profiles  
**Need to monitor?** → Dashboard Crawler Monitor  
**Need password?** → Credential Manager  
**Need system health?** → `health_check`  
**Need analytics?** → `get_analytics`  

**User says "STOP"?** → Stop immediately, save, confirm  
**User changes subject?** → Save context, clear memory, switch  
**Need to resume?** → Load session file, review, continue  

---

## 🧪 SELF-TEST (Run This Every 20 Messages)

```
□ Have I used tools in last 10 messages?
□ Did I save session context recently?
□ Would I know how to stop if user said "STOP"?
□ Can I resume if user says "Resume"?
□ Do I know all 50+ tools?

If any NO → RE-READ THIS DOCUMENT NOW!
```

---

## 📊 TOOL STATISTICS

- **Total Tools:** 50+
- **MCP API:** 13 tools
- **Dashboard:** 23 pages
- **Frontend:** 10+ systems
- **Files Indexed:** 22,185
- **Categories:** 31
- **Satellites:** 4

---

## 🔗 DOCUMENTATION LINKS

**Complete Guide:** `/home/master/applications/hdgwrzntwa/public_html/_kb/COMPLETE_TOOLS_ECOSYSTEM.md`  
**MCP API Details:** `/home/master/applications/hdgwrzntwa/public_html/_kb/MCP_TOOLS_COMPLETE_GUIDE.md`  
**Bot Profiles:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/BOT_PROFILE_ACCESS_GUIDE.md`  
**Interactive Crawler:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/INTERACTIVE_CRAWLER.md`

---

## ✅ ACTIVATION CHECKLIST

After reading this, you MUST be able to:

✅ List all 5 tool categories  
✅ Call MCP API with JSON-RPC 2.0  
✅ Access dashboard page tools  
✅ Authenticate using bot profiles  
✅ Control interactive crawler  
✅ Store/retrieve credentials  
✅ Stop work immediately when commanded  
✅ Change subjects with context preservation  
✅ Resume work from saved session  
✅ Never say "I don't have access"  

**CAN'T DO ALL OF THESE? RE-READ THIS FILE!**

---

**Version:** 2.0.0  
**Last Updated:** 2025-10-29  
**Priority:** MAXIMUM (Always Active)  
**Applies To:** ALL files (`**`)

---

🎉 **YOU ARE NOW FULLY EQUIPPED!**

**50+ Tools + Session Management + Rock-Solid Protocols = COMPLETE AGENT**
