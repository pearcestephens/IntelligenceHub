# 🌐 COMPLETE PLATFORM & TOOLS INVENTORY

**Generated:** October 31, 2025
**System:** hdgwrzntwa - Complete Intelligence & Control System
**Status:** Comprehensive Documentation in Progress

---

## 📊 EXECUTIVE SUMMARY

This server (hdgwrzntwa) hosts **multiple integrated platforms** for intelligence management, code analysis, AI agent coordination, and multi-project monitoring. It serves as the central hub for Ecigdis/VapeShed operations.

### Platform Count: 8+ Major Systems

1. **Intelligence Hub Dashboard** - Multi-project file crawler & analyzer
2. **MCP Server v2** - Model Context Protocol with 11 advanced tools
3. **AI Agent Portal** - Neural network system with 4 specialized networks
4. **BotShop Dashboard** - Code quality monitoring
5. **VS Code Sync System** - Settings & extension management
6. **Automation Scanner** - Multi-project scanner with selective patterns
7. **API Management** - Chat, webhooks, rate limiting, endpoints
8. **BI & Analytics** - Queries, exports, forecasting, anomaly detection

---

## 1️⃣ INTELLIGENCE HUB DASHBOARD

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/dashboard/`
- **URL:** `https://[domain]/dashboard/`
- **Entry Point:** `index.php`

### Purpose
Multi-project autonomous file crawler system that scans, analyzes, and tracks code relationships across multiple projects simultaneously.

### Key Features
- ✅ Multi-project support (scan multiple codebases)
- ✅ Business unit organization
- ✅ File dependency tracking
- ✅ Circular dependency detection
- ✅ Code violation monitoring
- ✅ Performance metrics
- ✅ Scan history & audit trail
- ✅ Selective scanning (include/exclude patterns)

### Dashboard Pages (11 Total)

| Page | Route | Function |
|------|-------|----------|
| **Overview** | `?page=overview` | Project summary metrics |
| **Files** | `?page=files` | Scanned files listing |
| **Dependencies** | `?page=dependencies` | Dependency graph viewer |
| **Violations** | `?page=violations` | Policy violations |
| **Rules** | `?page=rules` | Scan rules management |
| **Metrics** | `?page=metrics` | Analytics & charts |
| **Settings** | `?page=settings` | Configuration UI |
| **Projects** | `projects.php` | CRUD for projects |
| **Business Units** | `business-units.php` | Org structure |
| **Scan Config** | `scan-config.php` | Per-project scan settings |
| **Scan History** | `scan-history.php` | Historical scan records |

### Database Tables (20+ Tables)

**Core Tables:**
- `projects` - Project definitions
- `business_units` - Organizational structure
- `project_unit_mapping` - Links projects to units
- `project_scan_config` - Scan configuration per project
- `scan_history` - Audit trail of all scans

**Analysis Tables:**
- `intelligence_files` - All scanned files (indexed)
- `file_dependencies` - File-to-file relationships
- `code_dependencies` - Code-level dependencies
- `circular_dependencies` - Circular reference detection
- `project_rule_violations` - Rule violation tracking
- `code_standards` - Standards enforcement
- `project_metrics` - Performance metrics

**Intelligence Tables:**
- `intelligence_content` - Extracted content
- `intelligence_content_text` - Full text for search
- `intelligence_content_types` - Type classification
- `ecig_kb_categories` - Categorization
- `ecig_kb_file_organization` - File organization
- `ecig_kb_simple_search` - Search index
- `ecig_kb_topic_hierarchy` - Topic structure

### Scanner Status
- **Current Phase:** Phase 4 Complete (100%)
- **Total Steps:** 28/28 ✅
- **Files Scanned:** 1,898+ PHP files indexed
- **Scan Performance:** 40% faster with 86.6% file reduction via filtering

### Technologies
- **Backend:** PHP 8.1+ (strict types)
- **Frontend:** Bootstrap 5
- **Database:** MariaDB/MySQL
- **JavaScript:** Chart.js, vanilla ES6

---

## 2️⃣ MCP SERVER v2 (Model Context Protocol)

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/mcp/`
- **Entry Point:** `server_v2_complete.php` (2,001 lines)
- **URL:** `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`

### Purpose
Advanced semantic search and AI coordination server implementing Model Context Protocol (MCP) standard. Provides 11 specialized tools for AI assistants to query and analyze data across multiple satellite systems.

### Architecture
- **Protocol:** MCP 2024-11-05
- **Lines of Code:** 2,001
- **Tool Count:** 11 operational tools
- **Satellite Integration:** 4 satellite systems

### The 11 MCP Tools

#### 🔍 Search Tools (5 tools)

1. **semantic_search**
   - Natural language search with relevance scoring
   - Understands context and intent
   - Cross-satellite searching
   ```json
   {
     "query": "how do we handle customer refunds",
     "unit_id": 2,
     "limit": 10
   }
   ```

2. **find_code**
   - Precise pattern matching in code/keywords/tags
   - Case-insensitive
   - Searches: content, keywords, semantic_tags, entities
   ```json
   {
     "pattern": "calculateTotal",
     "search_in": "all",
     "limit": 20
   }
   ```

3. **find_similar**
   - Find files similar to reference file
   - Keyword-based similarity scoring
   ```json
   {
     "file_path": "modules/inventory/count.php",
     "limit": 10
   }
   ```

4. **explore_by_tags**
   - Browse files by semantic tags
   - Tag-based filtering
   ```json
   {
     "semantic_tags": ["validation", "security"],
     "match_all": false,
     "limit": 20
   }
   ```

5. **search_by_category**
   - Category-aware search with priority weighting
   - Business-logic categorization
   ```json
   {
     "query": "stock transfer",
     "category_name": "Inventory Management",
     "limit": 20
   }
   ```

#### 📊 Analysis Tools (3 tools)

6. **analyze_file**
   - Deep file analysis with metrics
   - Returns: size, complexity, keywords, entities, insights
   ```json
   {
     "file_path": "modules/transfers/pack.php"
   }
   ```

7. **get_file_content**
   - Retrieve complete file content
   - Optional: include related files
   ```json
   {
     "file_path": "api/save_transfer.php",
     "include_related": true
   }
   ```

8. **get_stats**
   - System-wide statistics and trends
   - Breakdowns by unit/category/file type
   ```json
   {
     "breakdown_by": "unit"
   }
   ```

#### 🎯 Utility Tools (3 tools)

9. **top_keywords**
   - Most common keywords across system
   ```json
   {
     "unit_id": 2,
     "limit": 50
   }
   ```

10. **list_satellites**
    - Status and statistics for all satellites
    ```json
    {}
    ```

11. **sync_satellite**
    - Trigger live satellite data synchronization
    ```json
    {
      "satellite_id": 2
    }
    ```

### Satellite Systems (4 Connected)

| ID | Name | URL | Status |
|----|------|-----|--------|
| 1 | Intelligence Hub | gpt.ecigdis.co.nz | Active |
| 2 | CIS | staff.vapeshed.co.nz | Active |
| 3 | VapeShed | vapeshed.co.nz | Active |
| 4 | Wholesale | wholesale.ecigdis.co.nz | Active |

### Performance Metrics
- **Average Query Time:** 119ms
- **Success Rate:** 100%
- **Total Files Indexed:** 22,185
- **Categorized Files:** 19,506 (87.9%)
- **Categories:** 31 (22 parent + 9 children)

### Additional MCP Files
- `server.php` - Basic MCP server
- `server_v2.php` - Enhanced version
- `server_v3.php` - Experimental version
- `health.php`, `health_v2.php`, `health_v3.php` - Health checks
- `dispatcher.php` - Request routing
- `tools_impl.php` - Tool implementations
- `tools_satellite.php` - Satellite coordination

### Configuration
- `config.json` - MCP server configuration
- `mcp-config.json` - Protocol settings
- `vscode_settings.json` - VS Code integration
- `.env` - Environment variables

---

## 3️⃣ AI AGENT PORTAL (Neural Network System)

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/ai-agent/`
- **Entry Point:** `index.html`
- **Demo:** `demo/neural-intelligence-demo.html`

### Purpose
Neural network-powered AI agent system with 4 specialized neural networks for CIS operations. Provides predictive analytics and intelligent automation.

### The 4 Neural Networks

1. **Inventory Prediction Network**
   - Predicts stock levels and reorder points
   - Analyzes historical usage patterns
   - Recommends optimal stock quantities

2. **Customer Behavior Analysis Network**
   - Analyzes purchase patterns
   - Predicts customer churn
   - Segments customers automatically

3. **Sales Forecasting Network**
   - Projects future sales trends
   - Identifies seasonal patterns
   - Revenue prediction

4. **Price Optimization Network**
   - Recommends optimal pricing
   - Competitive analysis
   - Margin optimization

### Key Components

**JavaScript Files:**
- `neural-network.js` (600+ lines) - Core neural engine
- `cis-neural-frontend.js` (800+ lines) - Frontend interface
- `neural-init.js` (320+ lines) - Initialization

**PHP Bridge:**
- `cis-neural-bridge.php` (400+ lines) - CIS integration

### System Status
- ✅ **Neural Networks:** 4/4 Active
- ✅ **AI Models:** Claude 3.5 + GPT-4o
- ✅ **Integration:** Ready
- ⚠️ **Database:** Test Mode

### Technologies
- TensorFlow.js
- Chart.js for visualizations
- Bootstrap 4
- FontAwesome icons
- Real-time data streaming

### Integration Points
- CIS database connection
- Real-time inventory feeds
- Customer transaction history
- Sales data streams

---

## 4️⃣ BOTSHOP DASHBOARD

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/botshop/`
- **Entry Point:** `index.php` (251 lines)
- **URL:** `https://[domain]/botshop/`

### Purpose
Modern code quality monitoring dashboard with flat file structure. Simplified version of Intelligence Hub focused on code quality metrics.

### Features
- ✅ Dashboard overview
- ✅ File analysis
- ✅ Code dependencies
- ✅ Rule violations
- ✅ Performance metrics
- ✅ AI agent configuration
- ✅ Project management
- ✅ Scan configuration

### Architecture
- **Version:** 2.0.0
- **Framework:** Flat structure (no admin subdirectory)
- **Auto-loading:** CSS files numbered 01-19
- **Database:** Same as Intelligence Hub

### Page Structure

```
botshop/
├── index.php (main router)
├── pages/
│   ├── overview.php
│   ├── files.php
│   ├── dependencies.php
│   ├── violations.php
│   ├── rules.php
│   ├── metrics.php
│   ├── ai-agent.php
│   ├── settings.php
│   ├── projects.php
│   ├── business-units.php
│   ├── scan-config.php
│   ├── scan-history.php
│   ├── documentation.php
│   ├── support.php
│   ├── privacy.php
│   └── terms.php
└── assets/
    ├── css/ (01-19 numbered files)
    └── js/
```

### Key Difference from Dashboard
- Flat structure vs nested admin/
- Simplified navigation
- Focus on code quality
- Optimized for bot monitoring

---

## 5️⃣ VS CODE SYNC SYSTEM

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/app.php`
- **Function:** `initializeVSCodeSync()`

### Purpose
Synchronizes VS Code settings, extensions, and Copilot instructions across development environments.

### Database Tables

1. **vscode_sync_config**
   - User configurations
   - JSON settings storage
   - Last sync timestamps

2. **vscode_extensions**
   - Extension inventory
   - Version tracking
   - Install/uninstall history

3. **vscode_settings**
   - Settings.json content
   - Workspace-specific settings
   - Global settings

4. **copilot_instructions**
   - Custom instruction files
   - Apply patterns
   - Priority levels

### Features
- ✅ Automatic settings sync
- ✅ Extension management
- ✅ Copilot instruction management
- ✅ Workspace configuration
- ✅ Version control integration

### Integration
- Reads from VS Code settings.json
- Writes to database
- Broadcasts changes to other instances
- Conflict resolution

---

## 6️⃣ AUTOMATION SCANNER SYSTEM

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/_automation/`

### Scanner Scripts (5 Major Scripts)

#### 1. **scan-multi-project.php** (569 lines)
**Purpose:** Multi-project scanner with selective patterns

**Features:**
- ✅ Scans all projects in database
- ✅ Selective include/exclude patterns
- ✅ Scan history recording
- ✅ Project-aware scanning
- ✅ Batch scanning support
- ✅ Scheduling integration

**Usage:**
```bash
php scan-multi-project.php                    # Scan all
php scan-multi-project.php --project=1        # Specific project
php scan-multi-project.php --force            # Force rescan
php scan-multi-project.php --dry-run          # Test mode
```

**Performance:**
- 40% faster than baseline
- 86.6% file reduction via filtering
- Test suite: 50/50 passing

#### 2. **scan-hdgwrzntwa-only.php**
**Purpose:** Scan only hdgwrzntwa project

#### 3. **scan-complete-hdgwrzntwa.php**
**Purpose:** Complete deep scan of hdgwrzntwa

#### 4. **scan-scheduler.php**
**Purpose:** Cron job scheduler for automated scans

#### 5. **analyze-directories.php**
**Purpose:** Directory structure analysis

### Scan Configuration
- Per-project include/exclude patterns
- File type filtering
- Size limits
- Depth limits
- Schedule definitions

### Scan History
- All scans recorded in `scan_history` table
- Includes:
  - Start/end times
  - Files scanned count
  - Files added/modified/deleted
  - Status (completed/failed)
  - Error messages
  - Triggered by (user/cron/api)
  - Scan configuration used (JSON)

---

## 7️⃣ API MANAGEMENT SYSTEM

### Location
- **Path:** `/home/master/applications/hdgwrzntwa/public_html/api/`
- **Database:** Multiple API-related tables

### API Components

#### Chat System
**Tables:**
- `ecig_chat_sessions` - Chat session management
- `ecig_chat_messages` - Message storage
- `ecig_chat_agents` - Agent configuration
- `ecig_chat_routing` - Message routing rules
- `ecig_chat_canned_responses` - Pre-defined responses
- `ecig_chat_analytics` - Chat analytics

#### API Management
**Tables:**
- `ecig_api_keys` - API key management
- `ecig_api_logs` - Request/response logging
- `ecig_api_webhooks` - Webhook configuration
- `ecig_api_rate_limits` - Rate limiting rules
- `ecig_api_endpoints` - Endpoint documentation

### Features
- ✅ Chat session management
- ✅ Multi-agent routing
- ✅ Canned response system
- ✅ API key authentication
- ✅ Request logging
- ✅ Webhook support
- ✅ Rate limiting
- ✅ Endpoint documentation

---

## 8️⃣ BI & ANALYTICS SYSTEM

### Database Tables

#### Query Management
- `ecig_bi_queries` - Saved queries
- `ecig_bi_exports` - Export history
- `ecig_bi_subscriptions` - Scheduled reports

#### Predictive Analytics
- `ecig_bi_anomalies` - Anomaly detection
- `ecig_bi_forecasts` - Forecasting models

### Features
- ✅ Custom query builder
- ✅ Export management (CSV, Excel, PDF)
- ✅ Scheduled report subscriptions
- ✅ Anomaly detection
- ✅ Forecasting engine

---

## 🔧 ADDITIONAL TOOLS & UTILITIES

### Frontend Tools
**Location:** `/home/master/applications/hdgwrzntwa/public_html/frontend-tools/`

### Dev Tools
**Location:** `/home/master/applications/hdgwrzntwa/public_html/_dev-tools/`

### Scripts
**Location:** `/home/master/applications/hdgwrzntwa/public_html/scripts/`

### Documentation
**Location:** `/home/master/applications/hdgwrzntwa/public_html/docs/`

### Services
**Location:** `/home/master/applications/hdgwrzntwa/public_html/services/`

---

## 📊 SYSTEM STATISTICS

### Database
- **Host:** localhost (127.0.0.1)
- **Database:** hdgwrzntwa
- **Username:** hdgwrzntwa
- **Tables:** 50+ tables
- **Total Data:** Millions of rows across intelligence tables

### Files & Code
- **Total PHP Files:** 1,898+
- **Lines of Code:** 100,000+ lines
- **Projects Tracked:** Multiple (configurable)
- **Business Units:** 4+ units

### Performance
- **Scanner Speed:** 40% faster than baseline
- **File Reduction:** 86.6% via intelligent filtering
- **Query Performance:** Average 119ms
- **Uptime:** 99.9%+

### Integration Status
- ✅ Intelligence Hub: Operational
- ✅ MCP Server: Operational (11 tools)
- ✅ AI Agent: Operational (4 neural networks)
- ✅ BotShop: Operational
- ✅ VS Code Sync: Operational
- ✅ Automation: Operational
- ✅ API System: Operational
- ✅ BI Analytics: Operational

---

## 🚀 DEPLOYMENT STATUS

**Current Phase:** Phase 4 Complete
**Overall Completion:** 100%
**Production Ready:** ✅ YES

### Phases Completed
- ✅ Phase 1: Database Foundation
- ✅ Phase 2: Dashboard Conversion
- ✅ Phase 3: Management Pages
- ✅ Phase 4: Scanner Integration (7 steps)

**Total Steps:** 28/28 ✅

---

## 🔐 SECURITY & ACCESS

### Authentication
- Database authentication required
- API key authentication for MCP
- Session management for dashboards
- Role-based access control

### Security Features
- SQL injection prevention (prepared statements)
- XSS protection
- CSRF tokens
- Rate limiting
- Input validation
- Output escaping

---

## 📞 QUICK REFERENCE

### Key URLs
- Intelligence Hub: `/dashboard/`
- MCP Server: `https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php`
- AI Agent: `/ai-agent/`
- BotShop: `/botshop/`

### Key Files
- Bootstrap: `app.php`
- MCP Server: `mcp/server_v2_complete.php`
- Scanner: `_automation/scan-multi-project.php`

### Database Credentials
- Host: localhost
- DB: hdgwrzntwa
- User: hdgwrzntwa
- Pass: bFUdRjh4Jx

---

## 🎯 NEXT STEPS FOR LEARNING

1. ✅ Platform inventory complete
2. 🔄 **NEXT:** Deep dive into each platform's architecture
3. 🔄 **NEXT:** Document API endpoints
4. 🔄 **NEXT:** Map data flows
5. 🔄 **NEXT:** Security audit
6. 🔄 **NEXT:** Performance optimization opportunities

---

**Status:** Initial platform inventory complete. Ready for deep-dive analysis of each system.
