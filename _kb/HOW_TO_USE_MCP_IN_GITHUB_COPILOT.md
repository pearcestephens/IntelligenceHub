# 🤖 How To Use MCP Tools in GitHub Copilot Chat

## Quick Start - Copy & Paste These Commands

### Basic Format
```
Use MCP tool_name with parameters
```

### Examples You Can Use Right Now

#### 1. **Check System Health**
```
Use MCP health_check
```

#### 2. **List All Available Tools**
```
Use MCP tools/list to show me all available MCP tools
```

#### 3. **Search for Code/Files**
```
Use MCP semantic_search to find files about "payroll automation"
```

```
Use MCP semantic_search with query "inventory management" and limit 5
```

#### 4. **List Business Categories**
```
Use MCP list_categories to show all business categories
```

#### 5. **Search Within a Category**
```
Use MCP search_by_category with query "transfer" and category_name "Inventory Management"
```

#### 6. **Database Queries**
```
Use MCP db.tables to list all database tables
```

```
Use MCP db.schema to describe the vend_products table
```

```
Use MCP db.query to SELECT COUNT(*) FROM vend_products
```

#### 7. **Read Files**
```
Use MCP fs.read to read the file at path "modules/consignments/pack.php"
```

```
Use MCP get_file_content for "modules/transfers/pack.php" with context
```

#### 8. **Analyze Code**
```
Use MCP analyze_file for "modules/consignments/pack.php"
```

#### 9. **Get Statistics**
```
Use MCP get_stats broken down by unit
```

```
Use MCP top_keywords with limit 20
```

#### 10. **Knowledge Base Search**
```
Use MCP kb.search for "how to process transfers"
```

#### 11. **Get Analytics**
```
Use MCP get_analytics with action "overview" and timeframe "24h"
```

#### 12. **List Satellites**
```
Use MCP list_satellites to show all connected systems
```

## Advanced Examples

### Find Similar Files
```
Use MCP find_similar to files like "modules/consignments/pack.php"
```

### Search by Tags
```
Use MCP explore_by_tags with tags ["validation", "security"]
```

### Browser/Web Scraping
```
Use MCP browser.fetch to get content from "https://example.com"
```

### Memory & Context
```
Use MCP memory.get_context for conversation "conv_123"
```

### Security - Store Credentials
```
Use MCP password.store to save Xero API credentials
```

### Performance Testing
```
Use MCP ops.performance_test on "https://staff.vapeshed.co.nz/dashboard"
```

## Natural Language Works Too!

You can also just ask naturally:

```
@workspace Search the codebase for inventory management functions using MCP
```

```
@workspace Use MCP to find all files related to stock transfers
```

```
@workspace Query the database using MCP to show me today's sales count
```

```
@workspace Use MCP to analyze the performance of the pack.php file
```

## Pro Tips

### 1. **Combine Multiple Tools**
```
First use MCP list_categories, then use MCP search_by_category for "Sales & Orders"
to find all sales-related files, then use MCP analyze_file on the most relevant one
```

### 2. **Use for Development Planning**
```
Use MCP semantic_search to find all files mentioning "payroll", then use MCP
find_similar to discover related files, then analyze their structure to help me
design a new payroll automation feature
```

### 3. **Database Investigation**
```
Use MCP db.tables to list tables, then use MCP db.schema on interesting tables,
then use MCP db.query to explore the data
```

### 4. **Security Audit**
```
Use MCP find_code to search for "password" or "api_key" patterns, then analyze
those files to check if credentials are properly secured
```

## Common Patterns

### Pattern 1: Discovery → Analysis → Action
```
1. Use MCP semantic_search to find relevant files
2. Use MCP analyze_file to understand their structure
3. Use MCP get_file_content to read the full code
4. Make your changes based on what you learned
```

### Pattern 2: Database Exploration
```
1. Use MCP db.tables to see what's available
2. Use MCP db.schema on key tables
3. Use MCP db.query to understand the data
4. Use MCP db.explain to optimize your queries
```

### Pattern 3: Architecture Understanding
```
1. Use MCP list_categories to see how the system is organized
2. Use MCP search_by_category to explore each area
3. Use MCP find_similar to understand relationships
4. Use MCP get_stats to see what's most active
```

## All 43 Available Tools

### Database (4)
- `db.query` - SELECT queries
- `db.schema` - Table structure
- `db.tables` - List tables
- `db.explain` - Query plans

### Filesystem (4)
- `fs.list` - List files
- `fs.read` - Read files
- `fs.write` - Write files
- `fs.info` - File info

### Knowledge Base (4)
- `kb.search` - Semantic search
- `kb.add_document` - Add docs
- `kb.list_documents` - List docs
- `kb.get_document` - Get doc

### Intelligence (13) ⭐ MOST POWERFUL
- `semantic_search` - NLP search across 22K files
- `search_by_category` - Category-filtered search
- `find_code` - Pattern matching
- `analyze_file` - Deep analysis
- `get_file_content` - Full content
- `find_similar` - Similar files
- `explore_by_tags` - Tag browsing
- `get_stats` - System stats
- `top_keywords` - Keyword analysis
- `list_categories` - 31 categories
- `get_analytics` - Real-time analytics
- `health_check` - System health
- `list_satellites` - 4 satellites
- `sync_satellite` - Trigger sync

### Memory (2)
- `memory.get_context` - Get conversation
- `memory.store` - Store memory

### Operations (4)
- `ops.ready_check` - Readiness
- `ops.security_scan` - Security
- `ops.monitoring_snapshot` - Monitoring
- `ops.performance_test` - Performance

### HTTP & Browser (5)
- `http.request` - HTTP calls
- `browser.fetch` - Fetch pages
- `browser.extract` - Extract data
- `browser.headers` - Get headers
- `crawler.deep_crawl` - Deep crawl
- `crawler.single_page` - Single page

### Git (2)
- `git.search` - GitHub search
- `git.open_pr` - Create PR

### Redis (2)
- `redis.get` - Read
- `redis.set` - Write

### Logs (2)
- `logs.tail` - Tail logs
- `logs.grep` - Search logs

### Security (4)
- `password.store` - Store creds
- `password.retrieve` - Get creds
- `password.list` - List services
- `password.delete` - Delete creds

### MySQL (2)
- `mysql.query` - Safe queries
- `mysql.common_queries` - Templates

## Troubleshooting

### If MCP Doesn't Respond
1. Reload VS Code window
2. Check GitHub Copilot extension is active
3. Try: `Use MCP health_check` to verify connection

### If You Get Permission Errors
Some tools are read-only for safety:
- ✅ All read operations work
- ✅ `fs.write` works with backup
- ❌ `db.query` is SELECT-only (no INSERT/UPDATE/DELETE)

### If Results Are Empty
Try broader search terms:
```
Use MCP semantic_search with query "inventory" limit 20
```

---

## 🎯 Start Here

**Your First Command:**
```
Use MCP health_check to verify the system is working, then use MCP list_categories
to show me what's available, then use MCP get_stats to see the overall system statistics
```

This will give you a complete overview of what you have access to!

**Then Try:**
```
Use MCP semantic_search to find the 5 most important files about "stock transfers"
```

---

**💡 Remember:** The bots have access to **22,185 indexed files** across **4 satellite servers** with **complete context awareness**. Use it!
