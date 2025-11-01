# ⚡ QUICK REFERENCE GUIDE
**Intelligence Hub - Daily Operations & Tool Reference**

---

## 🔧 NEW TOOLS - QUICK ACCESS

### CredentialManager - Never Ask for Passwords
```php
// Load ALL credentials at once
require_once 'services/CredentialManager.php';
$creds = CredentialManager::getAll();

// Quick access helpers
$dbCreds = CredentialManager::getDatabaseCredentials();
$paths = CredentialManager::getPathCredentials();
$apiKeys = CredentialManager::getApiKeyCredentials();
$server = CredentialManager::getServerCredentials();
```

### DatabaseValidator - Auto-Fix SQL Errors
```php
// Validate query
require_once 'services/DatabaseValidator.php';
$validator = new DatabaseValidator();
$result = $validator->validateQuery("SELECT * FROM users WHERE id = 1");

// Auto-correct typos
$fixed = $validator->autoCorrectQuery("SELECT * FROM usr WHERE id = 1");

// Scan file for errors
$result = $validator->scanFile('api/endpoint.php', ['auto_fix' => true]);

// Scan directory
$result = $validator->scanDirectory('modules/api', ['recursive' => true]);
```

### BotPromptBuilder - 80+ Coding Standards
```php
// Generate custom prompt
require_once 'services/BotPromptBuilder.php';
$builder = new BotPromptBuilder();
$prompt = $builder->generatePrompt('api_builder', 'Build REST API endpoint');

// List templates
$templates = $builder->listTemplates();

// Get standard details
$security = $builder->getStandard('security');

// Validate code
$violations = $builder->validateCodeAgainstStandards($code, ['security']);
```

### MCP Tools - Semantic Search
```bash
# Semantic search
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php" \
  -d '{"method":"tools/call","params":{"name":"semantic_search","arguments":{"query":"payment processing"}}}'

# Find code
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php" \
  -d '{"method":"tools/call","params":{"name":"find_code","arguments":{"pattern":"calculateTotal"}}}'

# Health check
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php" \
  -d '{"method":"tools/call","params":{"name":"health_check","arguments":{}}}'
```

---

## 🎯 MOST COMMON TASKS

### Check System Status
```bash
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php kb_cron.php status
```

### View All Tasks
```bash
php kb_cron.php list
```

### View Logs
```bash
# All logs
php kb_cron.php logs

# Specific task logs
php kb_cron.php logs cron_intelligence_refresh

# Last 100 lines
php kb_cron.php logs --lines=100
```

### Trigger Intelligence Refresh Now
```bash
php kb_cron.php trigger cron_intelligence_refresh
```

### Enable/Disable Tasks
```bash
# Disable
php kb_cron.php disable security_scan_weekly

# Enable
php kb_cron.php enable security_scan_weekly
```

---

## 📊 KEY FILE LOCATIONS

### Core Application
```
/home/master/applications/hdgwrzntwa/public_html/

├── app.php                          # Bootstrap file
├── intelligence_control_panel.php   # Visual dashboard
├── check_db_tables.php              # Database verification
│
├── api/
│   ├── agent_kb.php                 # Agent KB API
│   └── intelligence/                # Intelligence APIs
│
├── app/
│   └── Services/
│       └── RedisService.php         # Cache service
│
├── config/
│   ├── redis.php                    # Redis config
│   └── database.php                 # DB config
│
├── mcp/
│   └── server.js                    # Copilot MCP server
│
├── scripts/                         # All automation scripts
│   ├── kb_intelligence_engine_v2.php
│   ├── kb_proactive_indexer.php
│   ├── kb_crawler.php
│   └── kb_correlator.php
│
├── _kb/
│   ├── scripts/
│   │   ├── smart_cron_manager.php   # Master cron controller
│   │   ├── kb_cron.php              # CLI manager
│   │   ├── cron_intelligence_refresh.php
│   │   └── ssh_session_detector.php
│   │
│   ├── MASTER_ARCHAEOLOGICAL_ANALYSIS.md  # Complete history
│   ├── PROJECT_TIMELINE_VISUAL.md         # Visual timeline
│   ├── SYSTEM_KNOWLEDGE_BASE.md           # System overview
│   ├── SERVER_ARCHITECTURE.md             # Multi-server docs
│   ├── DUAL_MODE_SYSTEM_COMPLETE.md       # CRON + daemon
│   ├── COPILOT_INTEGRATION.md             # Copilot setup
│   ├── DEPLOYMENT_COMPLETE.md             # Deployment status
│   ├── DATABASE_SCHEMA_DOCUMENTATION.md   # Schema reference
│   └── ZERO_OVERLAP_COMPLETE.md           # Scheduling proof
│
└── builds/
    ├── active/                      # 160 production scripts
    └── historic/                    # 69 archived scripts
```

---

## 🚀 COMMON OPERATIONS

### View Intelligence Dashboard
Open browser: `https://hdgwrzntwa.cloudwaysapps.com/intelligence_control_panel.php`

### Check MCP Server Status
```bash
ps aux | grep "node.*server.js" | grep -v grep
```
Expected: Shows PID (e.g., 52634)

### Check Proactive Indexer Status
```bash
ps aux | grep "kb_proactive_indexer" | grep -v grep
```
Expected: Shows PID (e.g., 51626)

### Restart MCP Server
```bash
cd /home/master/applications/hdgwrzntwa/public_html/mcp
./start_mcp.sh --daemon
```

### Restart Proactive Indexer
```bash
cd /home/master/applications/hdgwrzntwa/public_html/scripts
nohup php kb_proactive_indexer.php --daemon > /tmp/kb_indexer_daemon.log 2>&1 &
```

### Check Redis Cache
```bash
cd /home/master/applications/hdgwrzntwa/public_html/scripts
php test_redis.php
```

### View Database Stats
```bash
mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "
SELECT 
  COUNT(*) as total_files,
  SUM(file_size) as total_bytes,
  SUM(file_size)/(1024*1024) as total_mb
FROM intelligence_files
WHERE is_active = 1;"
```

### Check Cron Job
```bash
crontab -l
```
Expected: Single line running smart_cron_manager.php every minute

---

## 🔍 TROUBLESHOOTING

### Problem: Tasks Not Running
```bash
# 1. Check cron entry exists
crontab -l

# 2. Check manager logs
tail -100 /home/master/applications/hdgwrzntwa/public_html/_kb/logs/smart_cron_manager.log

# 3. Check task status
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php kb_cron.php status

# 4. Check for lock file
ls -la /home/master/applications/hdgwrzntwa/public_html/_kb/logs/smart_cron_manager.lock
```

### Problem: MCP Server Not Responding
```bash
# 1. Check if running
ps aux | grep "node.*server.js"

# 2. Check logs
tail -100 /tmp/mcp-server.log

# 3. Restart
cd /home/master/applications/hdgwrzntwa/public_html/mcp
./start_mcp.sh --daemon
```

### Problem: Proactive Indexer Not Running
```bash
# 1. Check if running
ps aux | grep "kb_proactive_indexer"

# 2. Check logs
tail -100 /tmp/kb_indexer_daemon.log

# 3. Restart
cd /home/master/applications/hdgwrzntwa/public_html/scripts
nohup php kb_proactive_indexer.php --daemon > /tmp/kb_indexer_daemon.log 2>&1 &
```

### Problem: High Load
```bash
# Check what's running
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php kb_cron.php status

# Disable non-critical tasks temporarily
php kb_cron.php disable call_graph_generation
php kb_cron.php disable security_scan_weekly
```

### Problem: Redis Not Working
```bash
# 1. Check Redis service
redis-cli ping
Expected: PONG

# 2. Check connection
cd /home/master/applications/hdgwrzntwa/public_html/scripts
php test_redis.php

# 3. Restart Redis (if needed)
sudo systemctl restart redis
```

---

## 📈 MONITORING COMMANDS

### Watch System Status (Real-Time)
```bash
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
watch -n 60 'php kb_cron.php status'
```

### Check Intelligence Growth
```bash
watch -n 30 'mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "
SELECT intelligence_type, COUNT(*), AVG(confidence_score)
FROM ecig_kb_intelligence GROUP BY intelligence_type;"'
```

### Monitor File Indexing
```bash
watch -n 60 'mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "
SELECT COUNT(*) as total_files, 
       SUM(file_size)/(1024*1024) as mb,
       MAX(extracted_at) as last_update
FROM intelligence_files;"'
```

### View Recent Log Entries
```bash
# Smart cron manager
tail -f /home/master/applications/hdgwrzntwa/public_html/_kb/logs/smart_cron_manager.log

# MCP server
tail -f /tmp/mcp-server.log

# Proactive indexer
tail -f /tmp/kb_indexer_daemon.log
```

---

## ⏰ CRON SCHEDULE REFERENCE

| Task | Schedule | Description |
|------|----------|-------------|
| **cron_intelligence_refresh** | 0 0,4,8,12,16,20 * * * | Intelligence extraction every 4 hours |
| **push_to_cis** | 15 0,4,8,12,16,20 * * * | Push to CIS 15 min after intelligence |
| **call_graph_generation** | 30 2,10,18 * * * | Call graph every 8 hours |
| **cleanup_old_data** | 30 4 * * * | Daily cleanup at 4:30am |
| **security_scan_weekly** | 0 3 * * 0 | Weekly security scan (Sunday 3am) |
| **ssh_session_detector** | Every 30s | Auto-start file watcher when coding |

**Load Distribution:**
- Peak: 16.7% (Sunday 3am security scan)
- Average: 1.5% per hour
- Idle: 67% (16 of 24 hours)

---

## 🎯 PERFORMANCE TARGETS

### Intelligence Extraction
- **Target:** 4-6 minutes per run
- **Check:** `php kb_cron.php logs cron_intelligence_refresh`
- **Alert If:** > 10 minutes

### MCP Server Response
- **Target:** <5ms per request
- **Check:** MCP server logs
- **Alert If:** >50ms average

### Redis Cache
- **Target:** >90% hit rate
- **Check:** `php test_redis.php`
- **Alert If:** <80% hit rate

### Proactive Indexer
- **Target:** 2.1 seconds per cycle
- **Check:** `/tmp/kb_indexer_daemon.log`
- **Alert If:** >5 seconds

---

## 📝 DAILY CHECKLIST

### Morning Check (5 minutes)
```bash
# 1. System status
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php kb_cron.php status

# 2. Check services
ps aux | grep -E "(node.*server|kb_proactive_indexer)" | grep -v grep

# 3. Check last intelligence refresh
php kb_cron.php logs cron_intelligence_refresh | tail -50

# 4. Check Redis
redis-cli ping

# 5. Check database
mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "SELECT COUNT(*) FROM intelligence_files;"
```

Expected Results:
- ✅ Status shows recent runs (within last 4 hours)
- ✅ Both services show PIDs
- ✅ Intelligence refresh completed successfully
- ✅ Redis responds "PONG"
- ✅ Database shows ~29,808 files

---

## 🔐 DATABASE ACCESS

### Command Line
```bash
mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa
```

### Common Queries

**File Count by Type:**
```sql
SELECT intelligence_type, COUNT(*), 
       SUM(file_size)/(1024*1024) as mb
FROM intelligence_files 
WHERE is_active = 1
GROUP BY intelligence_type
ORDER BY COUNT(*) DESC;
```

**Recent Files:**
```sql
SELECT file_name, intelligence_type, 
       file_size, extracted_at
FROM intelligence_files
WHERE is_active = 1
ORDER BY extracted_at DESC
LIMIT 20;
```

**Server Distribution:**
```sql
SELECT server_id, COUNT(*), 
       SUM(file_size)/(1024*1024) as mb
FROM intelligence_files
WHERE is_active = 1
GROUP BY server_id;
```

**Business Units:**
```sql
SELECT bu.name, COUNT(if.*) as files
FROM business_units bu
LEFT JOIN intelligence_files if ON bu.unit_id = if.business_unit_id
WHERE if.is_active = 1
GROUP BY bu.name
ORDER BY files DESC;
```

---

## � API QUICK REFERENCE

### Credentials API
**Base:** `https://gpt.ecigdis.co.nz/api/credentials.php`

```bash
# Get all credentials
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=get_all"

# Get database credentials
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=database_credentials"

# Test database connection
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=test_database"

# Get paths
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=path_credentials"

# Get API keys
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=api_keys"

# Health check
curl "https://gpt.ecigdis.co.nz/api/credentials.php?action=health_check"
```

### Database Validator API
**Base:** `https://gpt.ecigdis.co.nz/api/db-validate.php`

```bash
# Validate query
curl -X POST "https://gpt.ecigdis.co.nz/api/db-validate.php?action=validate_query" \
  -d "query=SELECT * FROM users WHERE id = 1"

# Auto-correct query
curl -X POST "https://gpt.ecigdis.co.nz/api/db-validate.php?action=auto_correct" \
  -d "query=SELECT * FROM usr WHERE id = 1"

# Scan file
curl "https://gpt.ecigdis.co.nz/api/db-validate.php?action=scan_file&file=api/test.php"

# Scan directory
curl "https://gpt.ecigdis.co.nz/api/db-validate.php?action=scan_directory&directory=modules/api"

# List tables
curl "https://gpt.ecigdis.co.nz/api/db-validate.php?action=list_tables"

# Clear cache
curl -X POST "https://gpt.ecigdis.co.nz/api/db-validate.php?action=clear_cache"
```

### Bot Prompt API
**Base:** `https://gpt.ecigdis.co.nz/api/bot-prompt.php`

```bash
# List templates
curl "https://gpt.ecigdis.co.nz/api/bot-prompt.php?action=list_templates"

# List standards
curl "https://gpt.ecigdis.co.nz/api/bot-prompt.php?action=list_standards"

# Get standard details
curl "https://gpt.ecigdis.co.nz/api/bot-prompt.php?action=get_standard&standard=security"

# Generate prompt
curl -X POST "https://gpt.ecigdis.co.nz/api/bot-prompt.php?action=generate_prompt" \
  -d "template=full_stack_dev&task=Build user dashboard"
```

### MCP Tools API (13 Tools)
**Base:** `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`

All use JSON-RPC 2.0 format:
```bash
# Format
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php" \
  -H "Content-Type: application/json" \
  -d '{"method":"tools/call","params":{"name":"TOOL_NAME","arguments":{}}}'

# Semantic search
-d '{"method":"tools/call","params":{"name":"semantic_search","arguments":{"query":"refund processing","limit":10}}}'

# Find code
-d '{"method":"tools/call","params":{"name":"find_code","arguments":{"pattern":"calculateTotal"}}}'

# Search by category
-d '{"method":"tools/call","params":{"name":"search_by_category","arguments":{"category_name":"Inventory Management","query":"stock count"}}}'

# Analyze file
-d '{"method":"tools/call","params":{"name":"analyze_file","arguments":{"file_path":"modules/transfers/pack.php"}}}'

# List categories
-d '{"method":"tools/call","params":{"name":"list_categories","arguments":{}}}'

# Get analytics
-d '{"method":"tools/call","params":{"name":"get_analytics","arguments":{"action":"overview","timeframe":"24h"}}}'

# Health check
-d '{"method":"tools/call","params":{"name":"health_check","arguments":{}}}'
```

---

## 📊 DASHBOARD QUICK ACCESS

### Main Dashboard
**URL:** https://gpt.ecigdis.co.nz/dashboard/  
**Login:** admin / admin123  
**Features:** System overview, quick stats, service health

### Bot Standards Manager
**URL:** https://gpt.ecigdis.co.nz/dashboard/?page=bot-standards  
**Features:** View 80+ standards, generate prompts, enable/disable rules

### MCP Tools Tester
**URL:** https://gpt.ecigdis.co.nz/dashboard/?page=mcp-tools  
**Features:** Test all 13 tools, view stats, export results

---

## ✅ VERIFICATION CHECKLIST

### After Deployment
- [ ] All 3 services load: CredentialManager, DatabaseValidator, BotPromptBuilder
- [ ] Credentials API responds (curl test)
- [ ] Database Validator API responds (curl test)
- [ ] Bot Prompt API responds (curl test)
- [ ] MCP Server responds (curl health_check)
- [ ] Dashboard login works (admin/admin123)
- [ ] Bot Standards page shows all 10 categories
- [ ] MCP Tools page shows 4 stat cards
- [ ] All cron jobs running (php kb_cron.php status)
- [ ] No errors in logs (tail -100 logs/*.log)

### Daily Checks
- [ ] Cron tasks running: `php kb_cron.php status`
- [ ] MCP server running: `ps aux | grep "node.*server.js"`
- [ ] Proactive indexer running: `ps aux | grep "kb_proactive_indexer"`
- [ ] Redis responding: `redis-cli ping`
- [ ] Database accessible: `php check_db_tables.php`
- [ ] Dashboard accessible: Visit main dashboard URL
- [ ] Logs clean: Check for errors in recent logs

---

## �🚨 EMERGENCY PROCEDURES

### System Down
1. Check cron: `crontab -l`
2. Check services: `ps aux | grep -E "(node.*server|kb_proactive_indexer)"`
3. Check logs: `tail -100 /home/master/applications/hdgwrzntwa/public_html/_kb/logs/*.log`
4. Restart services (see Troubleshooting section above)

### High Load
1. Check status: `php kb_cron.php status`
2. Disable non-critical tasks: `php kb_cron.php disable security_scan_weekly`
3. Check for runaway processes: `ps aux | grep php | grep -v grep`
4. Kill if needed: `kill <PID>`

### Database Issues
1. Check connection: `mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "SELECT 1;"`
2. Check disk space: `df -h`
3. Check table status: `cd /home/master/applications/hdgwrzntwa/public_html && php check_db_tables.php`

### Redis Issues
1. Check service: `redis-cli ping`
2. Check memory: `redis-cli info memory`
3. Restart if needed: `sudo systemctl restart redis`

---

## 📞 SUPPORT CONTACTS

### Internal
- **System Administrator:** [Contact Info]
- **Database Administrator:** [Contact Info]
- **Developer Lead:** [Contact Info]

### External
- **Hosting (Cloudways):** support@cloudways.com
- **GitHub Support:** [If MCP issues]

---

## 📚 DOCUMENTATION REFERENCE

### Core Documentation
- **README.md** - Project overview and comprehensive tool documentation
- **QUICK_REFERENCE.md** - This file (daily operations + API reference)
- **SETUP_COMPLETE.md** - Setup verification and status
- **.github/copilot-instructions.md** - Complete bot manual (850+ lines)
- **.vscode/settings.json** - VS Code configuration with tool docs
- **DASHBOARD_FIX_SUMMARY.md** - Dashboard bug fixes applied

### Tool Documentation
- **CredentialManager** - `/services/CredentialManager.php` (600 lines)
  - API: `/api/credentials.php` (12 endpoints)
  - Never ask for passwords - load automatically
  
- **DatabaseValidator** - `/services/DatabaseValidator.php` (900 lines)
  - API: `/api/db-validate.php` (10 endpoints)
  - Validate queries, auto-fix typos, scan files/directories
  
- **BotPromptBuilder** - `/services/BotPromptBuilder.php` (800 lines)
  - API: `/api/bot-prompt.php` (5 endpoints)
  - Dashboard: https://gpt.ecigdis.co.nz/dashboard/?page=bot-standards
  - 80+ coding standards in 10 categories
  
- **MCP Tools** - `/mcp/server_v2_complete.php`
  - 13 tools for semantic search and analysis
  - Dashboard: https://gpt.ecigdis.co.nz/dashboard/?page=mcp-tools
  - 22,185 files indexed across 31 categories

### Knowledge Base (_kb/)
- **COMPLETE_UPDATE_SUMMARY_20251026.md** - Latest updates
- **DASHBOARD_COMPLETE_OPERATIONAL_GUIDE.md** - Dashboard guide
- **DATABASE_SCHEMA_DOCUMENTATION.md** - Complete schema
- **DEEP_DIVE_*.md** - Deep dive documentation (9 files)
- **CONTROL_PANEL_*.md** - Control panel guides
- **MASTER_ARCHAEOLOGICAL_ANALYSIS.md** - Complete history
- **PROJECT_TIMELINE_VISUAL.md** - Visual timeline
- **SYSTEM_KNOWLEDGE_BASE.md** - System overview
- **SERVER_ARCHITECTURE.md** - Multi-server docs

### Quick Access URLs
- **Main Dashboard:** https://gpt.ecigdis.co.nz/dashboard/
- **Bot Standards:** https://gpt.ecigdis.co.nz/dashboard/?page=bot-standards
- **MCP Tools:** https://gpt.ecigdis.co.nz/dashboard/?page=mcp-tools
- **Credentials API:** https://gpt.ecigdis.co.nz/api/credentials.php
- **DB Validator API:** https://gpt.ecigdis.co.nz/api/db-validate.php
- **Bot Prompt API:** https://gpt.ecigdis.co.nz/api/bot-prompt.php
- **MCP Server API:** https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php

---

## 🎓 REMEMBER

1. **Credentials** - Load automatically, never ask
2. **Validation** - Check SQL before running, auto-fix errors
3. **Standards** - Follow CRITICAL rules always, HIGH when possible
4. **MCP Tools** - Use for semantic search and analysis
5. **Documentation** - Keep this file handy for daily operations

**When in doubt: Search first, validate second, code third.** 🚀
- **INSTALLATION_CHECKLIST.md** - Installation steps

### Complete Documentation
- **MASTER_ARCHAEOLOGICAL_ANALYSIS.md** - Complete system history
- **SYSTEM_KNOWLEDGE_BASE.md** - Architecture overview
- **SERVER_ARCHITECTURE.md** - Multi-server setup
- **DUAL_MODE_SYSTEM_COMPLETE.md** - CRON + daemon modes
- **COPILOT_INTEGRATION.md** - GitHub Copilot setup
- **DATABASE_SCHEMA_DOCUMENTATION.md** - Schema reference
- **ZERO_OVERLAP_COMPLETE.md** - Scheduling details

### Technical Deep Dives
- **IMPLEMENTATION_STATUS.md** - Feature status
- **DEPLOYMENT_COMPLETE.md** - Deployment verification
- **architecture/** directory - Detailed architecture docs

---

## ⚡ POWER USER TIPS

### Tip 1: Quick Status Check
```bash
alias cis-status='cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts && php kb_cron.php status'
```

### Tip 2: Watch Logs in Real-Time
```bash
alias cis-logs='tail -f /home/master/applications/hdgwrzntwa/public_html/_kb/logs/smart_cron_manager.log'
```

### Tip 3: Quick Intelligence Trigger
```bash
alias cis-intel='cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts && php kb_cron.php trigger cron_intelligence_refresh'
```

### Tip 4: Database Quick Query
```bash
alias cis-db='mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa'
```

### Tip 5: Service Status
```bash
alias cis-services='ps aux | grep -E "(node.*server|kb_proactive_indexer)" | grep -v grep'
```

Add these to your `~/.bashrc` or `~/.bash_profile` for permanent aliases.

---

## 🎯 NEXT STEPS AFTER READING THIS

1. ✅ Bookmark this file for daily reference
2. ✅ Run morning checklist to familiarize yourself
3. ✅ Test each troubleshooting procedure
4. ✅ Add power user aliases to your shell
5. ✅ Read MASTER_ARCHAEOLOGICAL_ANALYSIS.md for complete understanding
6. ✅ Review SYSTEM_KNOWLEDGE_BASE.md for architecture
7. ✅ Set up monitoring dashboard if desired

---

**Quick Reference Guide - Version 1.0**  
**Generated:** October 25, 2025  
**Purpose:** Daily operations and troubleshooting  
**Keep This Handy:** Essential for system management
