# 📁 MCP PROJECT DIRECTORY STRUCTURE

**Last Updated:** November 2, 2025
**Purpose:** Complete reference for AI Agent autonomous implementation

---

## 🗂️ ROOT PROJECT STRUCTURE

```
/home/129337.cloudwaysapps.com/hdgwrzntwa/
├── public_html/                          # Web root (production code)
│   ├── mcp/                              # MCP Server (production)
│   │   ├── server_v3.php                 # ✅ Production MCP endpoint
│   │   ├── mcp_tools_turbo.php           # ✅ Tool implementations
│   │   ├── health_v3.php                 # Health check endpoint
│   │   ├── lib/                          # 🆕 AI Agent creates classes here
│   │   │   ├── CacheManager.php          # 🔨 To be built (Redis 3-tier)
│   │   │   ├── SearchEngine.php          # 🔨 To be built (composite ranking)
│   │   │   ├── Analytics.php             # 🔨 To be built (query tracking)
│   │   │   ├── QueryExpander.php         # 🔨 To be built (synonyms)
│   │   │   ├── FederatedSearch.php       # 🔨 To be built (satellite search)
│   │   │   └── SemanticTagger.php        # 🔨 To be built (GPT integration)
│   │   ├── bootstrap.php                 # Application bootstrap
│   │   ├── dispatcher.php                # Request dispatcher
│   │   ├── semantic_search_engine.php    # Search engine (to be enhanced)
│   │   ├── php_code_indexer.php          # Code indexer
│   │   ├── tools_impl.php                # Tool implementations
│   │   ├── check_satellites.php          # Satellite checker
│   │   ├── tools_satellite.php           # Satellite tools
│   │   ├── DEPLOY.sh                     # Deployment script
│   │   └── OLD_BUILD/                    # 📦 Legacy versions (archived)
│   │       ├── server_v2_complete.php    # Old MCP v2
│   │       ├── server_v2.php
│   │       └── [15 legacy files]
│   │
│   ├── _automation/                      # 🆕 AI Agent creates scripts here
│   │   ├── extract_missing_content.php   # 🔨 To be built (Phase 1)
│   │   ├── warm_search_cache.php         # 🔨 To be built (Phase 1)
│   │   ├── generate_semantic_tags.php    # 🔨 To be built (Phase 4)
│   │   ├── mcp_health_check.sh           # 🔨 To be built (Phase 4)
│   │   ├── benchmark_search.php          # 🔨 To be built (testing)
│   │   ├── test_satellites.php           # 🔨 To be built (testing)
│   │   └── validate_database.sql         # 🔨 To be built (validation)
│   │
│   ├── _kb/                              # Knowledge Base (documentation)
│   │   ├── mcp/                          # MCP-specific documentation
│   │   │   ├── MCP_100_PERCENT_EFFICIENCY_PLAN.md      # ✅ Master plan
│   │   │   ├── SEARCH_OPTIMIZATION_ANALYSIS.md         # ✅ DB analysis
│   │   │   ├── COPILOT_ASSIGNMENT.md                   # ✅ AI Agent assignment
│   │   │   ├── DIRECTORY_STRUCTURE.md                  # ✅ This file
│   │   │   ├── DATABASE_SAMPLE_3ROWS.sql               # ✅ DB reference (324KB)
│   │   │   ├── API_REFERENCE.md                        # 🔨 To be created
│   │   │   ├── IMPLEMENTATION_LOG.md                   # 🔨 AI Agent tracks progress
│   │   │   ├── TESTING_GUIDE.md                        # 🔨 To be created
│   │   │   └── DEPLOYMENT_CHECKLIST.md                 # 🔨 To be created
│   │   └── [other KB docs]
│   │
│   ├── app.php                           # Application entry point
│   ├── composer.json                     # PHP dependencies
│   └── [other application files]
│
├── private_html/                         # Private storage (not web-accessible)
│   ├── ai/                               # AI-related files
│   ├── backups/                          # Database/file backups
│   ├── cache/                            # Application cache
│   ├── config/                           # Configuration files
│   ├── logs/                             # Application logs
│   ├── sessions/                         # PHP sessions
│   └── uploads/                          # User uploads
│
├── logs/                                 # Server logs
│   ├── apache_*.error.log                # Apache error logs
│   ├── apache_*.access.log               # Apache access logs
│   ├── nginx_*.error.log                 # Nginx error logs
│   ├── php-app.slow.log                  # PHP slow log
│   └── mcp_*.log                         # 🔨 MCP logs (to be created)
│
├── conf/                                 # Server configuration
│   ├── server.nginx                      # Nginx config
│   ├── server.apache                     # Apache config
│   ├── fpm-pool.conf                     # PHP-FPM pool
│   └── force-https.nginx                 # HTTPS redirect
│
├── ssl/                                  # SSL certificates
└── tmp/                                  # Temporary files
```

---

## 📂 KEY DIRECTORIES EXPLAINED

### `/public_html/mcp/` - MCP Server Production
**Purpose:** Production MCP server endpoint and tools
**Status:** Active, serving https://gpt.ecigdis.co.nz/mcp/
**Files Count:** 11 production files + OLD_BUILD archive

**AI Agent Instructions:**
- Add new classes to `/mcp/lib/` subdirectory
- Integrate with `server_v3.php` for new tools
- Follow existing patterns in `mcp_tools_turbo.php`
- Never modify OLD_BUILD/ (archived legacy code)

---

### `/public_html/_automation/` - Automation Scripts
**Purpose:** Cron jobs, batch processors, maintenance scripts
**Status:** 🆕 To be created by AI Agent
**Expected Files:** 6-8 PHP/Shell scripts

**AI Agent Instructions:**
- Create all Phase 1-4 automation scripts here
- Make all scripts executable (`chmod +x *.sh`)
- Add proper error handling and logging
- Include progress indicators for long-running tasks
- Add to crontab after testing

---

### `/public_html/_kb/mcp/` - MCP Documentation
**Purpose:** Complete reference documentation for MCP system
**Status:** Partially complete, AI Agent adds implementation docs
**Current Files:** 6 documentation files (3 complete, 3 to be created)

**Documentation Standards:**
- Use Markdown format
- Include code examples
- Add table of contents for long docs
- Keep updated as code changes
- Version control all changes

---

### `/public_html/mcp/lib/` - MCP Class Library
**Purpose:** Core MCP functionality classes
**Status:** 🆕 To be created by AI Agent (Phase 1-4)
**Expected Files:** 6 PHP classes

**Class Standards:**
```php
<?php
declare(strict_types=1);

namespace MCP\Lib;

/**
 * ClassName - Brief description
 *
 * Detailed explanation of purpose
 *
 * @package MCP\Lib
 * @version 1.0.0
 */
class ClassName {
    // Implementation
}
```

---

### `/private_html/` - Private Storage
**Purpose:** Non-web-accessible storage for sensitive data
**Status:** Active
**Usage:**
- Configuration files (database credentials, API keys)
- Backups (automated daily)
- Cache (Redis persistence, file cache)
- Logs (application-level logs)
- Sessions (PHP session storage)

**Security:**
- Never serve files from here via web server
- Restrict permissions (750 directories, 640 files)
- Regular backup rotation (30 days)
- Encrypted sensitive files

---

### `/logs/` - Server Logs
**Purpose:** Apache, Nginx, PHP-FPM logs
**Status:** Active
**Current Logs:**
- `apache_phpstack-129337-5615757.cloudwaysapps.com.error.log`
- `nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log`
- `php-app.slow.log` (slow query log)

**AI Agent Instructions:**
- Create MCP-specific logs: `mcp_performance.log`, `mcp_errors.log`
- Rotate logs daily
- Parse slow query log for Phase 1 optimization
- Add health check logging

---

## 🎯 FILES TO BE CREATED BY AI AGENT

### Phase 1: Foundation (Week 1)
```
/public_html/mcp/lib/CacheManager.php                 # Redis 3-tier caching
/public_html/_automation/extract_missing_content.php  # Text extraction
/public_html/_automation/warm_search_cache.php        # Cache warming
/public_html/_kb/mcp/IMPLEMENTATION_LOG.md            # Progress tracking
```

**Database Changes:**
- `intelligence_content` - Add extraction status columns
- Create stored procedures: `calculate_content_scores()`, `recalculate_all_scores()`
- Add triggers: `update_scores_on_content_change`
- Create table: `mcp_cache_metrics`

---

### Phase 2: Intelligence (Weeks 2-3)
```
/public_html/mcp/lib/SearchEngine.php                 # Composite ranking
/public_html/mcp/lib/Analytics.php                    # Query tracking
/public_html/mcp/lib/QueryExpander.php                # Synonym expansion
/public_html/_kb/mcp/API_REFERENCE.md                 # API documentation
```

**Database Changes:**
- Create tables: `mcp_search_sessions`, `mcp_search_events`, `mcp_zero_result_queries`
- Create view: `mcp_search_ctr`
- Create table: `keyword_relationships`
- Populate synonym data

---

### Phase 3: Federation (Week 4)
```
/public_html/mcp/lib/FederatedSearch.php              # Satellite search
/public_html/_automation/test_satellites.php          # Satellite testing
```

**Integration:**
- Connect to 4 satellites (Intelligence Hub, CIS, VapeShed, Wholesale)
- Implement async HTTP calls with 500ms timeout
- Add result merging and de-duplication

---

### Phase 4: AI Enhancement (Weeks 5-6)
```
/public_html/mcp/lib/SemanticTagger.php               # GPT integration
/public_html/_automation/generate_semantic_tags.php   # Batch tagging
/public_html/_automation/mcp_health_check.sh          # Health monitoring
/public_html/_automation/benchmark_search.php         # Performance testing
/public_html/_automation/validate_database.sql        # Database validation
/public_html/_kb/mcp/TESTING_GUIDE.md                 # Testing procedures
/public_html/_kb/mcp/DEPLOYMENT_CHECKLIST.md         # Deployment steps
```

**Database Changes:**
- Populate `semantic_tags` JSON column in `intelligence_content_text`
- Create view: `mcp_performance_dashboard`
- Add monitoring tables if needed

---

## 📝 FILE NAMING CONVENTIONS

### PHP Files
- **Classes:** PascalCase (e.g., `CacheManager.php`, `SearchEngine.php`)
- **Scripts:** snake_case (e.g., `extract_missing_content.php`, `warm_search_cache.php`)
- **Include strict types:** `declare(strict_types=1);`
- **Namespace:** `MCP\Lib` for classes in `/mcp/lib/`

### Shell Scripts
- **Naming:** snake_case with `.sh` extension (e.g., `mcp_health_check.sh`)
- **Make executable:** `chmod +x script.sh`
- **Shebang:** `#!/bin/bash`
- **Error handling:** `set -e` at top

### Documentation
- **Naming:** UPPERCASE_WITH_UNDERSCORES.md (e.g., `IMPLEMENTATION_LOG.md`)
- **Format:** Markdown with headers, code blocks, tables
- **TOC:** Include for docs >100 lines
- **Version:** Add date at top

### SQL Files
- **Naming:** UPPERCASE_WITH_UNDERSCORES.sql (e.g., `DATABASE_SAMPLE_3ROWS.sql`)
- **Comments:** Use `--` for single line, `/* */` for blocks
- **Sections:** Separate with `-- ========` dividers
- **Formatting:** Readable indentation, uppercase keywords

---

## 🔒 SECURITY CONSIDERATIONS

### File Permissions
```bash
# Directories
755 - /public_html/ (web-accessible)
750 - /private_html/ (owner+group only)
755 - /public_html/mcp/
755 - /public_html/_automation/
755 - /public_html/_kb/

# Files
644 - .php files (read by web server)
640 - config files with secrets
755 - .sh scripts (executable)
644 - .md documentation
644 - .sql database files
```

### Git Ignore
```
# Never commit:
private_html/config/*.php
private_html/backups/
logs/*.log
tmp/*
.env
*.key
*.pem

# Database dumps with data:
*_WITH_DATA.sql
```

---

## 🎯 QUALITY STANDARDS

### Code Quality
- PSR-12 coding standards
- PHPDoc comments on all classes/methods
- Type hints on all parameters/returns
- Proper error handling (try-catch)
- Logging for debugging
- Unit tests where appropriate

### Documentation Quality
- Clear purpose statement
- Usage examples
- API reference
- Troubleshooting section
- Version history
- Author/date metadata

### Database Quality
- Indexed columns for queries
- Foreign key relationships
- Proper data types
- Default values
- NOT NULL constraints
- Migration scripts with rollback

---

## 📊 CURRENT STATUS

### Completed ✅
- MCP v3 server operational
- Database analysis complete (135 tables)
- Implementation plan documented
- AI Agent assignment prepared
- Database sample exported (324KB with 3 rows per table)
- Directory structure documented

### In Progress 🔨
- Awaiting AI Agent kickoff
- Ready for Phase 1 implementation

### Not Started ⏳
- All 9 deliverables (Phases 1-4)
- Automation scripts
- Class libraries
- Testing suite
- Monitoring dashboard

---

## 🚀 READY FOR AI AGENT

**All prerequisites complete:**
- ✅ Complete implementation plan (MCP_100_PERCENT_EFFICIENCY_PLAN.md)
- ✅ Detailed assignment (COPILOT_ASSIGNMENT.md)
- ✅ Database reference (DATABASE_SAMPLE_3ROWS.sql - 135 tables)
- ✅ Directory structure defined (this file)
- ✅ Clean codebase (production files organized, legacy archived)
- ✅ Clear success criteria and metrics

**AI Agent can start immediately with full context!**

---

**Document Version:** 1.0.0
**Last Reviewed:** November 2, 2025
**Maintained By:** Auto-updated by AI Agent during implementation
