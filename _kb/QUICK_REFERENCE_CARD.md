# ⚡ INTELLIGENCE HUB - QUICK REFERENCE CARD
## Essential Information for AI Assistants

**Last Updated:** November 6, 2025
**Status:** ✅ Complete System Knowledge Available

---

## 🎯 START HERE

### Read These Documents First:
1. **MASTER_SYSTEM_KNOWLEDGE_INDEX.md** - Complete system reference (~850 lines)
2. **AI_LEARNING_SESSION_COMPLETE_REPORT.md** - Learning summary (~650 lines)
3. **EXTENDED_MEMORY_INTEGRATION.md** - Memory system guide (~650 lines)

---

## 🔑 CRITICAL CREDENTIALS

### Database Access:
```
Host: localhost (127.0.0.1)
Database: hdgwrzntwa
User: hdgwrzntwa
Password: bFUdRjh4Jx
```

### MCP API Access:
```
Endpoint: https://gpt.ecigdis.co.nz/mcp/server_v3.php
API Key: bFUdRjh4Jx
Header: X-API-Key or Authorization: Bearer
Protocol: JSON-RPC 2.0
```

---

## 🛠️ MCP TOOLS (13 Available)

### Search Tools:
1. `semantic_search` - Natural language search
2. `find_code` - Pattern-based code search
3. `search_by_category` - Business domain search
4. `find_similar` - Vector similarity
5. `explore_by_tags` - Semantic tags

### Analysis Tools:
6. `analyze_file` - Deep file analysis
7. `get_file_content` - Content + context
8. `get_stats` - System statistics
9. `health_check` - Health dashboard

### Database Tools:
10. `db.query` - SQL execution (SELECT only)
11. `db.schema` - Table structures
12. `db.tables` - Table listing

### Business Tools:
13. `list_categories` - 31 business categories

---

## 📊 SYSTEM AT A GLANCE

### Database:
- **Tables:** 78 operational
- **Files:** 7,915 active (8,645 total)
- **Storage:** 179.33 MB (files) + 273.64 MB (embeddings)
- **Embeddings:** 8,549 vectors
- **Scan Logs:** 41,322 operations
- **MCP Metrics:** 21,330 records

### File System:
- **Knowledge Base:** 98 MB, 583 MD files
- **MCP Server:** 29 MB
- **AI Agent:** 45 MB, 434 files
- **Scanner:** 13 MB
- **Dashboard:** 2.7 MB

---

## 🏗️ SYSTEM LOCATIONS

### Core Systems:
```
/app.php                           # Bootstrap
/mcp/server_v3.php                 # MCP server
/ai-agent/                         # AI infrastructure
/scanner/                          # Intelligence scanner
/_kb/                              # Knowledge base
/dashboard/                        # Control panel
```

### Key Files:
```
/_kb/MASTER_SYSTEM_KNOWLEDGE_INDEX.md
/_kb/AI_LEARNING_SESSION_COMPLETE_REPORT.md
/_kb/EXTENDED_MEMORY_INTEGRATION.md
/_kb/database-analysis/COMPLETE_DATABASE_ARCHITECTURE.md
/_kb/mcp/API_REFERENCE.md
```

---

## 🗄️ KEY DATABASE TABLES

### Intelligence Core:
- `intelligence_files` (7,915 rows, 179.33 MB)
- `intelligence_embeddings` (8,549 rows, 273.64 MB)
- `intelligence_content` (linked to files)
- `intelligence_metrics` (3,000 rows)

### Memory System:
- `ai_conversations` (19 conversations)
- `ai_conversation_messages` (29 messages)
- `ai_conversation_topics` (39 topics)
- `bot_learned_knowledge` (2 entries)
- `bot_conversation_bookmarks` (2 bookmarks)

### MCP System:
- `mcp_tool_usage` (125 calls)
- `mcp_performance_metrics` (21,330 records)
- `mcp_search_analytics`
- `mcp_sessions`

### Scanner System:
- `scan_logs` (41,322 operations, 10.03 MB)
- `scan_history`
- `project_scan_config`

---

## 🚀 QUICK COMMANDS

### Database Query:
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT COUNT(*) FROM intelligence_files;"
```

### MCP Health Check:
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health" \
  -H "X-API-Key: bFUdRjh4Jx"
```

### Semantic Search:
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: bFUdRjh4Jx" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"semantic_search","arguments":{"query":"inventory validation","limit":5}},"id":1}'
```

### Run Scanner:
```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html
php api/intelligence/api_neural_scanner.php
```

---

## 🌐 SATELLITE SYSTEMS

### 1. Intelligence Hub (Unit 1)
- Domain: gpt.ecigdis.co.nz
- Server: hdgwrzntwa
- Level: QUANTUM

### 2. CIS (Unit 2)
- Domain: staff.vapeshed.co.nz
- Server: jcepnzzkmj
- Level: NEURAL
- Database: 385+ tables

### 3. VapeShed (Unit 3)
- Domain: www.vapeshed.co.nz
- Server: dvaxgvsxmz
- Level: ADVANCED

### 4. Wholesale (Unit 4)
- Domain: www.ecigdis.co.nz
- Server: fhrehrpjmu
- Level: ADVANCED

---

## 📈 PERFORMANCE BENCHMARKS

### MCP Server:
- Average query: 119ms
- Success rate: 100%
- Tool usage: 125 logged calls

### Scanner:
- Processing: 500.5 files/second
- Memory: 38.24 MB
- Functions: 27,311 extracted
- Classes: 2,208 mapped

### Database:
- Query optimization: Indexed + foreign keyed
- Largest table: intelligence_embeddings (273.64 MB)

---

## 🎯 COMMON USE CASES

### 1. Find Code Pattern:
```json
{"name":"find_code","arguments":{"pattern":"calculateTotal"}}
```

### 2. Search Documentation:
```json
{"name":"semantic_search","arguments":{"query":"how to create transfers"}}
```

### 3. Analyze File:
```json
{"name":"analyze_file","arguments":{"file_path":"modules/transfers/lib/Transfer.php"}}
```

### 4. Check Health:
```json
{"name":"health_check","arguments":{}}
```

### 5. Query Database:
```json
{"name":"db.query","arguments":{"sql":"SELECT * FROM intelligence_files LIMIT 5"}}
```

---

## 🔍 TROUBLESHOOTING

### Issue: Can't connect to database
```bash
# Test connection
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT 1;"
```

### Issue: MCP tools not responding
```bash
# Check health endpoint
curl https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health
```

### Issue: Scanner not working
```bash
# Check scan logs
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT * FROM scan_logs ORDER BY created_at DESC LIMIT 5;"
```

---

## 📚 DOCUMENTATION INDEX

### Master Guides:
- `MASTER_SYSTEM_KNOWLEDGE_INDEX.md` - Complete reference
- `AI_LEARNING_SESSION_COMPLETE_REPORT.md` - Learning summary
- `EXTENDED_MEMORY_INTEGRATION.md` - Memory system

### Specialized Docs:
- `database-analysis/COMPLETE_DATABASE_ARCHITECTURE.md` - Database
- `mcp/API_REFERENCE.md` - MCP tools
- `humans/api/API_DIRECTORY.md` - API catalog
- `bots/tools/MASTER_ARCHAEOLOGICAL_ANALYSIS.md` - Bot history

### Quick Guides:
- `KB_QUICK_REFERENCE.md` - KB navigation
- `OPERATIONAL_READINESS.md` - Operations
- `HOW_TO_USE_MCP_IN_GITHUB_COPILOT.md` - Copilot integration

---

## ⚡ REMEMBER

1. **Always load context first** - Check conversation history and learned knowledge
2. **Use MCP tools** - Don't reinvent the wheel, use available tools
3. **Store learnings** - Capture important discoveries in bot_learned_knowledge
4. **Bookmark critical moments** - Flag important decisions
5. **Check master docs** - Comprehensive answers in MASTER_SYSTEM_KNOWLEDGE_INDEX.md

---

## 🎉 STATUS

**System Status:** ✅ **FULLY OPERATIONAL**
**AI Agent Status:** ✅ **READY FOR WORK**
**Knowledge Base Status:** ✅ **COMPLETE**
**MCP Server Status:** ✅ **ONLINE** (100% success rate)

---

**Quick Reference Card Generated by:** GitHub Copilot AI Assistant
**For Use by:** All AI assistants working with Intelligence Hub
**Last Updated:** November 6, 2025

**Ready to assist! 🚀**
