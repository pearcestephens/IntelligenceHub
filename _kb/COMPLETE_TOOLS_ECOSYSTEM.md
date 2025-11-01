# 🛠️ COMPLETE INTELLIGENCE HUB TOOLS ECOSYSTEM

**Generated:** 2025-10-29 (Current Session)  
**Purpose:** Comprehensive directory of ALL available tools across all systems  
**Version:** 1.0.0

---

## 🎯 CRITICAL: This Is Your Complete Toolkit

This document lists EVERY tool you can use. Never say "I don't have access" again!

---

## 📊 TOOLS BY CATEGORY

### 1️⃣ MCP API TOOLS (13 Tools) ✅ FULLY DOCUMENTED

**Server:** `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`  
**Protocol:** JSON-RPC 2.0  
**Health Check:** `https://gpt.ecigdis.co.nz/mcp/health.php`  
**Full Guide:** `/home/master/applications/hdgwrzntwa/public_html/_kb/MCP_TOOLS_COMPLETE_GUIDE.md`

| # | Tool Name | Purpose | Use When |
|---|-----------|---------|----------|
| 1 | `semantic_search` | Natural language search across 22,185 files | Need to find code/docs by concept |
| 2 | `find_code` | Precise code pattern matching | Looking for specific function/class |
| 3 | `search_by_category` | Category-aware search (31 categories) | Searching within business domain |
| 4 | `find_similar` | Find files similar to reference | Need related code examples |
| 5 | `explore_by_tags` | Browse by semantic tags | Exploring by topic (security, validation) |
| 6 | `analyze_file` | Deep file analysis with metrics | Need to understand file structure |
| 7 | `get_file_content` | Retrieve file with context | Reading specific file |
| 8 | `get_stats` | System-wide statistics | Getting system overview |
| 9 | `health_check` | System health dashboard | Checking if system is healthy |
| 10 | `list_categories` | Show all 31 business categories | Planning features, organizing work |
| 11 | `get_analytics` | Web Analytics & Performance tracking | Understanding usage patterns |
| 12 | `list_satellites` | Show all 4 satellite systems | Multi-system work |
| 13 | `sync_satellite` | Trigger satellite data sync | Updating satellite data |

**How to call:** See MCP_TOOLS_COMPLETE_GUIDE.md for JSON-RPC examples

---

### 2️⃣ DASHBOARD PAGE TOOLS (23 Web Tools)

**Base URL:** `https://gpt.ecigdis.co.nz/dashboard/pages/`  
**Access:** Web browser  
**Authentication:** Auto-authenticated in dev mode

| # | Tool | URL | Purpose |
|---|------|-----|---------|
| 1 | **MySQL Analyzer** | `/sql-query.php` | Execute SELECT queries, analyze database |
| 2 | **MCP Web Interface** | `/mcp-tools.php` | Web GUI for MCP tools (alternative to API) |
| 3 | **AI Chat** | `/ai-chat.php` | AI chat interface |
| 4 | **AI Prompt Generator** | `/ai-prompt-generator.php` | Generate AI prompts |
| 5 | **Analytics Dashboard** | `/analytics.php` | Usage analytics and metrics |
| 6 | **API Management** | `/api.php` | API configuration and testing |
| 7 | **Bot Commands** | `/bot-commands.php` | Bot command interface |
| 8 | **Bot Standards** | `/bot-standards.php` | Bot coding standards reference |
| 9 | **Cleanup Utilities** | `/cleanup.php` | System cleanup tools |
| 10 | **Conversations** | `/conversations.php` | Conversation history viewer |
| 11 | **Crawler Monitor** | `/crawler-monitor.php` | Monitor interactive crawler |
| 12 | **Cron Jobs** | `/cron.php` | Cron job management |
| 13 | **Documentation** | `/documentation.php` | Documentation viewer |
| 14 | **File Browser** | `/files.php` | Browse system files |
| 15 | **Function Library** | `/functions.php` | Function reference |
| 16 | **Log Viewer** | `/logs.php` | View system logs |
| 17 | **Neural/AI** | `/neural.php` | Neural network/AI features |
| 18 | **System Overview** | `/overview.php` | Complete system status |
| 19 | **Pattern Library** | `/patterns.php` | Code patterns reference |
| 20 | **System Scanner** | `/scanner.php` | Scan system for issues |
| 21 | **Search Interface** | `/search.php` | Search system |
| 22 | **Server Management** | `/servers.php` | Manage servers |
| 23 | **Settings** | `/settings.php` | System configuration |

**Usage:**
```bash
# Access via browser:
https://gpt.ecigdis.co.nz/dashboard/pages/sql-query.php

# Or via curl (if authenticated):
curl https://gpt.ecigdis.co.nz/dashboard/pages/sql-query.php
```

---

### 3️⃣ FRONTEND TOOLS SUITE

**Location:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/`  
**Purpose:** Complete web testing, monitoring, and automation suite

#### 🤖 Bot Profile System

**File:** `CIS_ROBOT_AUTH_PROFILES.md`, `BOT_PROFILE_ACCESS_GUIDE.md`

**Available Profiles:**

1. **CIS Robot** (`cis-robot`)
   - Username: `cisrobot`
   - Password: `CISBot2025!`
   - Login URL: `https://staff.vapeshed.co.nz/login.php`
   - Auto-detected for: `staff.vapeshed.co.nz`
   - Permissions: read, test, monitor

2. **GPT Hub Robot** (`gpt-hub`)
   - Username: `botuser`
   - Password: `BotAccess2025!`
   - Login URL: `https://gpt.ecigdis.co.nz/login`
   - Auto-detected for: `gpt.ecigdis.co.nz`

3. **Retail Sites** (`retail-sites`)
   - No authentication (public)
   - Read-only testing
   - Auto-detected for: `www.vapeshed.co.nz`, `www.vapingkiwi.co.nz`

**Usage:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools

# Auto-detect profile and test:
./test-website https://staff.vapeshed.co.nz

# Force specific profile:
./test-website https://example.com --auth --profile=cis-robot
```

#### 🕷️ Interactive Crawler

**File:** `INTERACTIVE_CRAWLER.md`

**Features:**
- ⏸️ Pause/Resume crawler
- 📸 Screenshots on demand
- 💬 Chat interface for control
- 🐛 JavaScript debugger
- 📊 Real-time status
- 🔍 Error detection (404s, 500s, JS errors)
- 🌐 Navigation control
- 🖱️ Remote element clicking

**Usage:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools

# Start interactive crawler (Terminal 1):
npm run crawl:interactive -- \
  -u pearce.stephens@gmail.com \
  -p 'fmsADMINED2013!!' \
  --port=3000

# Start chat interface (Terminal 2):
npm run chat
```

**Chat Commands:**
```
status          - Show current crawler state
pause           - Pause the crawler
resume          - Resume the crawler
screenshot      - Capture screenshot
messages        - Show recent logs
errors          - Show all errors
eval <js>       - Run JavaScript in page
go <url>        - Navigate to URL
click <selector> - Click element
screenshots     - List all screenshots
```

#### 🔍 Audit System

**File:** `AUDIT_SYSTEM_COMPLETE.md`

**Features:**
- Cron job auditing
- Security scanning
- Performance monitoring
- Error tracking

**Usage:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools

# Run audit:
node cron-audit-scanner.js
```

#### 📡 SSE Streaming

**File:** `SSE_QUICK_START.md`

**Features:**
- Real-time event streaming
- Live crawler updates
- System notifications

**Usage:**
```javascript
// Connect to SSE stream:
const eventSource = new EventSource('https://gpt.ecigdis.co.nz/frontend-tools/sse-stream');

eventSource.addEventListener('crawler-update', (e) => {
  console.log('Crawler update:', JSON.parse(e.data));
});
```

#### 📦 Dropbox Integration

**File:** `DROPBOX_SYSTEM_STATUS.md`

**Features:**
- File synchronization
- Backup management
- Cloud storage integration

---

### 4️⃣ CREDENTIAL MANAGER (Password Safe)

**Service:** `CredentialManager.php`  
**Location:** `/home/master/applications/hdgwrzntwa/public_html/services/CredentialManager.php`

**Features:**
- 🔐 AES-256 encryption
- 🔑 Automatic credential loading
- 🗄️ Stores: Database credentials, API keys, file paths, server info
- 👤 Admin-only access
- 💾 One-time setup

**How to Use:**

```php
<?php
require_once '/home/master/applications/hdgwrzntwa/public_html/services/CredentialManager.php';

$credMgr = new CredentialManager();

// Store credential
$credMgr->set('database', 'cis_password', 'wprKh9Jq63', 'CIS Database Password');

// Retrieve credential
$password = $credMgr->get('database', 'cis_password');

// List all credentials of type
$allDbCreds = $credMgr->getByType('database');

// Delete credential
$credMgr->delete('database', 'old_credential');
```

**Credential Types:**
- `database` - Database passwords
- `api_key` - API keys and tokens
- `server` - Server credentials
- `file_path` - Secure file paths
- `custom` - Any other type

**Storage:**
- Database table: `bot_credentials`
- Encrypted with AES-256-CBC
- Unique key per credential type + key combination

---

### 5️⃣ STANDALONE TOOLS

**Location:** `/home/master/applications/hdgwrzntwa/public_html/`

| Tool | File | Purpose | URL |
|------|------|---------|-----|
| **AI Activity Analyzer** | `ai-activity-analyzer.php` | Analyze AI usage patterns | `/ai-activity-analyzer.php` |
| **Intelligence Control Panel** | `intelligence_control_panel.php` | Master control dashboard | `/intelligence_control_panel.php` |
| **Copilot Cron Manager** | `copilot-cron-manager.php` | Manage Copilot automation cron jobs | `/copilot-cron-manager.php` |
| **AI Batch Processor** | `ai-batch-processor.php` | Process AI requests in batches | `/ai-batch-processor.php` |
| **Universal Copilot Automation** | `universal-copilot-automation.php` | Central automation engine | `/universal-copilot-automation.php` |
| **Command Control Panel** | `/dashboard/command-control-panel.php` | Execute system commands via GUI | `/dashboard/command-control-panel.php` |
| **Hardened Security Audit** | `/tools/hardened-security-audit.php` | Security vulnerability scan | `/tools/hardened-security-audit.php` |
| **Platform Security Audit** | `/tools/platform-security-audit.php` | Platform security check | `/tools/platform-security-audit.php` |

---

## 🔧 HOW TO CHOOSE THE RIGHT TOOL

### Use MCP API Tools When:
- ✅ You need programmatic access (bot/script)
- ✅ Searching/analyzing code
- ✅ Getting system statistics
- ✅ Automating workflows

### Use Dashboard Page Tools When:
- ✅ You need visual interface
- ✅ Manual exploration/testing
- ✅ Human operator involved
- ✅ Quick one-off tasks

### Use Frontend Tools When:
- ✅ Testing websites
- ✅ Monitoring crawlers
- ✅ Authenticating as bot
- ✅ Real-time streaming needed

### Use Credential Manager When:
- ✅ Storing passwords securely
- ✅ Sharing credentials between bots
- ✅ Managing API keys
- ✅ Centralizing authentication

### Use Standalone Tools When:
- ✅ Specific admin tasks
- ✅ Batch processing
- ✅ Security auditing
- ✅ System automation

---

## 📋 TOOL USAGE BY SCENARIO

### Scenario 1: "I need to search for code"
```
1. Use MCP: semantic_search
   - Natural language query
   - Finds relevant files
   
2. Or use Dashboard: Search Interface
   - Web UI for searching
   - Visual results
```

### Scenario 2: "I need to test staff portal"
```
1. Use Frontend Tools: Bot Profile System
   - Auto-authenticates as CIS Robot
   - Full access to staff portal
   
2. Or use Frontend Tools: Interactive Crawler
   - Control crawler via chat
   - Capture screenshots
```

### Scenario 3: "I need to run SQL query"
```
1. Use Dashboard: MySQL Analyzer
   - Web interface for queries
   - Execution time tracking
   - Query history
   
2. Or use Credential Manager + direct DB access
   - Get credentials securely
   - Connect with PDO
```

### Scenario 4: "I need system health check"
```
1. Use MCP: health_check
   - API call for programmatic check
   
2. Or use Dashboard: System Overview
   - Visual health dashboard
   - All metrics at once
```

### Scenario 5: "I need to monitor crawler"
```
1. Use Dashboard: Crawler Monitor
   - Real-time status
   - Visual interface
   
2. Or use Frontend Tools: Interactive Crawler Chat
   - Control via commands
   - Get status updates
```

---

## 🚨 SESSION MANAGEMENT PROTOCOL

### Starting a Session
```
1. Introduce yourself: "Starting work on [TASK]"
2. Load relevant context: Use semantic_search or get_analytics
3. Check system health: Use health_check
4. Proceed with task
```

### During a Session
```
1. Save progress every 10 messages
2. Update user on major milestones
3. Log important decisions
4. Keep context in memory
```

### Stopping Work (User says "STOP")
```
1. IMMEDIATELY STOP current operation
2. Save all progress to disk/DB
3. Document what was completed
4. Document what remains
5. Confirm stoppage: "Work stopped. Progress saved."
```

### Changing Subject (User introduces new topic)
```
1. Save current context: Write summary to file
2. Clear working memory
3. Load new context for new topic
4. Confirm switch: "Switched to [NEW TOPIC]. Previous work saved."
```

### Resuming Work
```
1. User says "Resume" or "Continue [PREVIOUS TASK]"
2. Load saved context from file
3. Review progress: "Resuming [TASK]. Last completed: [X]. Next: [Y]"
4. Continue work
```

---

## 🎯 ROCK SOLID RULES

### Rule 1: Always Check Tool Availability
```
❌ NEVER say: "I don't have access to X"
✅ ALWAYS check: This document, MCP health, dashboard
✅ If tool exists, USE IT
✅ If tool missing, say: "Tool X not found. Available alternatives: [Y, Z]"
```

### Rule 2: Use Sessions Properly
```
✅ Start new session for new major task
✅ Continue existing session for related work
✅ Save context before stopping
✅ Load context when resuming
```

### Rule 3: Stop Commands
```
Trigger phrases (immediate stop):
- "STOP"
- "HALT"
- "PAUSE WORK"
- "STOP THAT"

Actions:
1. Stop immediately
2. Save progress
3. Document state
4. Confirm stoppage
```

### Rule 4: Change Subject
```
Trigger phrases:
- "Let's work on X instead"
- "Switch to Y"
- "New topic: Z"
- "Change subject"

Actions:
1. Save current work
2. Clear context
3. Load new topic
4. Confirm switch
```

### Rule 5: Never Lose Work
```
✅ Save progress frequently
✅ Write to disk before stopping
✅ Log all major decisions
✅ Keep backups of critical changes
```

---

## 📊 TOOL STATISTICS

- **Total Tools:** 50+
- **MCP API Tools:** 13
- **Dashboard Pages:** 23
- **Frontend Tools:** 10+ (profiles, crawler, audit, SSE, etc.)
- **Credential Manager:** 1
- **Standalone Tools:** 8+

- **Total Files Indexed:** 22,185
- **Categories:** 31
- **Satellites:** 4 (Hub, CIS, VapeShed, Wholesale)

---

## ✅ QUICK REFERENCE CARD

### Most Common Tools:

**Search/Find:**
- `semantic_search` (MCP) - Natural language search
- `find_code` (MCP) - Find specific code
- Search Interface (Dashboard) - Web search

**Database:**
- MySQL Analyzer (Dashboard) - Run queries
- Credential Manager - Get DB passwords

**Testing:**
- Bot Profiles (Frontend) - Auto-authenticate
- Interactive Crawler (Frontend) - Control crawler

**Monitoring:**
- `health_check` (MCP) - System health
- `get_analytics` (MCP) - Usage stats
- System Overview (Dashboard) - Visual dashboard

**Admin:**
- Command Control Panel (Dashboard) - Run commands
- Cron Manager - Manage automation
- Security Audits (Standalone) - Scan for issues

---

## 🔗 RELATED DOCUMENTATION

- **MCP API Guide:** `/home/master/applications/hdgwrzntwa/public_html/_kb/MCP_TOOLS_COMPLETE_GUIDE.md`
- **Bot Profiles:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/BOT_PROFILE_ACCESS_GUIDE.md`
- **Interactive Crawler:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/INTERACTIVE_CRAWLER.md`
- **SSE Streaming:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/SSE_QUICK_START.md`
- **Audit System:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/AUDIT_SYSTEM_COMPLETE.md`

---

## 🎓 TRAINING CHECKPOINT

After reading this document, you should be able to:

✅ List all 50+ available tools  
✅ Choose the right tool for any task  
✅ Use MCP API tools via JSON-RPC  
✅ Access dashboard page tools via browser  
✅ Authenticate using bot profiles  
✅ Control interactive crawler  
✅ Store/retrieve credentials securely  
✅ Start, stop, and change sessions properly  
✅ Never say "I don't have access" again  

**If you can't do all of the above, RE-READ THIS DOCUMENT!**

---

**Last Updated:** 2025-10-29  
**Maintained By:** Intelligence Hub Automation  
**Version:** 1.0.0

---

🎉 **YOU NOW HAVE COMPLETE KNOWLEDGE OF ALL AVAILABLE TOOLS!**
