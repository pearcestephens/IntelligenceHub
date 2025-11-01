---
applyTo: '**'
description: 'Company context and MCP tools - Use MCP semantic_search, health_check, and list_categories constantly'
---

# Company Context & MCP Tools

## 🏢 Company Info

**Ecigdis Limited** trading as **The Vape Shed**
- 17 retail locations across New Zealand
- Premium vape retail business since 2015
- Staff portal: https://staff.vapeshed.co.nz
- Dashboard: https://gpt.ecigdis.co.nz

## 🗄️ Database Access

**Main CIS Database:**
- Host: 127.0.0.1
- Database: jcepnzzkmj
- Username: jcepnzzkmj
- Password: wprKh9Jq63

**Key Tables:**
- `vend_products` (13.5M rows) - Product catalog
- `vend_inventory` (856K rows) - Stock levels
- `vend_sales` (2.1M rows) - Sales transactions
- `stock_transfers` - Transfer headers
- `stock_transfer_items` - Transfer line items
- `users` - Staff accounts

## 🛠️ MCP Tools (USE THESE CONSTANTLY!)

You have **13 powerful search & analysis tools** via MCP server:
**Server:** `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`

### 🔍 Search Tools (Use Before Every Answer!)

**1. semantic_search** - Natural language search across 22,185 files
```json
{"query": "how do we handle customer refunds", "limit": 10}
```

**2. search_by_category** - Search within business categories
```json
{"query": "stock transfer validation", "category_name": "Inventory Management", "limit": 20}
```

**3. find_code** - Find functions, classes, patterns
```json
{"pattern": "calculateTotal", "search_in": "all", "limit": 20}
```

**4. find_similar** - Find files similar to reference
```json
{"file_path": "modules/inventory/count.php", "limit": 10}
```

**5. explore_by_tags** - Search by semantic tags
```json
{"semantic_tags": ["validation", "security"], "match_all": false, "limit": 20}
```

### 📊 Analysis Tools

**6. analyze_file** - Deep file analysis with metrics
```json
{"file_path": "modules/transfers/pack.php"}
```

**7. get_file_content** - Get file with context
```json
{"file_path": "api/save_transfer.php", "include_related": true}
```

**8. health_check** - System health and statistics
```json
{}
```

**9. get_stats** - System-wide statistics
```json
{"breakdown_by": "unit"}
```

**10. top_keywords** - Most common keywords
```json
{"unit_id": 2, "limit": 50}
```

### 🏢 Business Tools

**11. list_categories** - Show all 31 business categories
```json
{"min_priority": 1.3, "order_by": "priority"}
```

**12. get_analytics** - Real-time analytics
```json
{"action": "overview", "timeframe": "24h"}
```

## 🎯 MCP Tool Usage Rules

### ✅ ALWAYS Do:
- Use `semantic_search` before answering questions
- Use `list_categories` when planning features
- Use `health_check` at session start
- Use `analyze_file` before modifying code
- Use `find_code` to find existing implementations

### ❌ NEVER Say:
- "I don't have access to search..."
- "I can't see the codebase..."
- "I'm unable to check..."
- "I don't know where that is..."

**Instead:** USE THE TOOLS! They exist for this exact reason.

## 📊 System Stats (Current)

- **Total files indexed:** 22,185
- **Categorized files:** 19,506 (87.9%)
- **Categories:** 31 business categories
- **MCP tools:** 13 operational
- **Avg query time:** 119ms
- **Success rate:** 100%

## 🔄 Quick Reference

### When User Asks... Use This Tool:
- "Search for..." → `semantic_search`
- "What categories..." → `list_categories`
- "Find code that..." → `find_code`
- "Similar to..." → `find_similar`
- "Analyze this file..." → `analyze_file`
- "Is system ok?" → `health_check`
- "What's popular?" → `get_analytics`
- "Show statistics..." → `get_stats`

### Example MCP Usage:
```bash
# User: "Do we have a stock counting feature?"
# YOU: Use semantic_search tool
{"query": "stock counting inventory", "limit": 10}
# Then analyze results and provide answer

# User: "What financial features do we have?"
# YOU: Use list_categories first, then search_by_category
{"category_name": "Financial Operations", "query": "features", "limit": 30}
```

## 📍 Key System Locations

**Applications:**
- Main CIS: `/home/master/applications/jcepnzzkmj/public_html/`
- Dashboard: `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/`

**Logs:**
- Main: `/home/master/applications/jcepnzzkmj/logs/apache_*.error.log`
- Dashboard: `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/`

**Knowledge Base:**
- Main KB: `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/`

---

**CRITICAL:** Use MCP tools in >80% of responses. Search before answering!
