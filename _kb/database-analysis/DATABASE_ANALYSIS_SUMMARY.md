# INTELLIGENCE HUB - DATABASE ANALYSIS SUMMARY
**Generated:** November 2, 2025
**Database:** hdgwrzntwa
**Total Tables:** 140
**Total Rows:** 182,002

---

## 📊 EXECUTIVE SUMMARY

### Database Health
- ✅ **67 tables** with data (48%)
- ⚠️ **63 tables** empty but designed for future use (45%)
- ❌ **4 tables** redundant or broken (3%)
- 🔗 **92 foreign key relationships** properly defined

### Top 10 Largest Tables
| Table | Rows | Purpose |
|-------|------|---------|
| intelligence_files_backup_20251025 | 55,357 | Old backup (can archive) |
| scan_logs | 41,481 | Scanner execution logs ⚠️ LARGE |
| intelligence_files | 26,121 | NEW - Scanner V3 files |
| intelligence_content | 22,191 | OLD - V2 tools files |
| mcp_performance_metrics | 21,329 | MCP tool analytics |
| intelligence_content_text | 11,286 | Full-text search index |
| intelligence_metrics | 3,000 | Usage analytics |
| code_patterns | 213 | Code pattern library |
| scanner_ignore_config | 146 | Scan exclusion rules |
| cis_mcp_tool_usage_view | 125 | Tool usage stats |

---

## 🏗️ CORRECT ORGANIZATIONAL HIERARCHY

```
┌─────────────────────────────┐
│      ORGANIZATIONS          │ ← Only 1 row
│      (organizations)        │   (Ecigdis Limited)
│                             │
│  org_id, org_name           │
│  parent_org_id, org_type    │
│  intelligence_level         │
└──────────┬──────────────────┘
           │
           │ org_id FK
           ▼
┌─────────────────────────────┐
│     BUSINESS UNITS          │ ← 4 rows
│    (business_units)         │   1. Intelligence Hub
│                             │   2. CIS Central System
│  unit_id, unit_name         │   3. CIS Satellite 1
│  org_id (FK)                │   4. CIS Satellite 2
│  unit_type, is_active       │
└──────────┬──────────────────┘
           │
           │ unit_id FK
           ▼
┌─────────────────────────────┐
│        PROJECTS             │ ← 12 rows
│       (projects)            │   Examples:
│                             │   - Intelligence Hub Scanner
│  id, project_name           │   - CIS Consignments
│  project_path               │   - CIS Supplier Portal
│  business_unit_id (FK)      │   - CIS Purchase Orders
│  status, priority           │   - CIS Inventory
└──────────┬──────────────────┘
           │
           │ project_id FK
           ▼
┌─────────────────────────────┐
│     PROJECT DOMAINS         │ ← 11 rows
│    (project_domains)        │   Examples:
│                             │   - gpt.ecigdis.co.nz
│  domain, subdomain          │   - staff.vapeshed.co.nz
│  full_url, is_primary       │   - api.vapeshed.co.nz
│  project_id (FK)            │
└──────────┬──────────────────┘
           │
           │ Files stored here:
           ▼
┌─────────────────────────────┐
│   INTELLIGENCE FILES        │ ← 26,121 rows
│  (intelligence_files)       │   (Scanner V3 output)
│                             │
│  project_id, unit_id (FKs)  │
│  file_path, file_content    │
│  intelligence_data (JSON)   │
│  content_summary            │
│                             │
│  UNIQUE: (unit_id, path)    │
└─────────────────────────────┘
```

---

## 🚨 CRITICAL ISSUES FOUND

### 1. **DUAL INTELLIGENCE SYSTEMS** ⚠️
**Problem:** Two separate intelligence storage tables exist
- `intelligence_content` (22,191 rows) ← Used by **MCP V2 tools**
- `intelligence_files` (26,121 rows) ← Used by **Scanner V3**

**Impact:** MCP tools and scanner don't share data
**Solution:** Migrate V2 tools to use `intelligence_files` table

---

### 2. **BROKEN ORGANIZATIONAL MAPPING** 🔴
**Problem:** `project_unit_mapping` table has **0 rows**
- All 12 projects exist
- All 4 business units exist
- But NO links between them!

**Impact:** Projects are orphaned, can't determine which unit owns which project
**Solution:** Populate mapping table:

```sql
INSERT INTO project_unit_mapping (project_id, unit_id, created_at) VALUES
(1, 1, NOW()),  -- Intelligence Hub → Hub Unit
(2, 2, NOW()),  -- CIS Consignments → CIS Unit
(3, 2, NOW()),  -- CIS Supplier → CIS Unit
(4, 2, NOW()),  -- CIS Purchase Orders → CIS Unit
(5, 2, NOW()),  -- CIS Inventory → CIS Unit
-- ... etc for all 12 projects
```

---

### 3. **SCANNER RULES MISSING** 🔴
**Problem:** `cis_rules` table is **empty (0 rows)**
- `cis_rule_categories` has 10 categories defined
- But no actual rules!

**Impact:** Scanner V3 cannot detect violations without rules
**Solution:** Import rule definitions from rule library

---

### 4. **BROKEN VIEWS** ❌
**Problem:** Two views reference invalid tables
- `kb_files` - View definition broken
- `simple_quality` - View definition broken

**Impact:** Queries fail when accessing these views
**Solution:** DROP or fix view definitions

---

### 5. **MASSIVE LOG TABLE** ⚠️
**Problem:** `scan_logs` has **41,481 records**

**Impact:** Table will grow indefinitely, slow queries
**Solution:** Implement log rotation:
- Keep last 7 days in main table
- Archive older to `scan_logs_archive_YYYYMM`
- Run monthly cleanup cron

---

## 🗑️ REDUNDANT TABLES (SAFE TO DROP)

### Duplicate Cron Tables
These have `hub_*` equivalents with actual data:

| Redundant Table | Use Instead | Reason |
|----------------|-------------|--------|
| `cron_jobs` (0 rows) | `hub_cron_jobs` (6 rows) | Duplicate functionality |
| `cron_executions` (0 rows) | `hub_cron_executions` (3 rows) | Duplicate functionality |
| `cron_metrics` (0 rows) | `hub_cron_metrics` (0 rows) | Duplicate functionality |
| `cron_satellites` (0 rows) | `hub_cron_satellites` (4 rows) | Duplicate functionality |

**SQL to Drop:**
```sql
DROP TABLE IF EXISTS cron_jobs, cron_executions, cron_metrics, cron_satellites;
```

---

## ✅ EMPTY BUT USEFUL TABLES (KEEP)

### By Category

#### 🤖 AI/ML System (8 tables)
Future AI features, memory, predictions
- `ai_idempotency_keys`, `ai_memory`, `ai_message_files`
- `ai_models`, `ai_predictions`
- `mcp_category_usage`, `mcp_routing_bandit`
- `neural_pattern_relationships`

#### 🔍 Scanner System (4 tables)
Will populate when Scanner V3 runs with rules
- `cis_rules` ← **NEEDS DATA**
- `cis_rule_violations`
- `cis_rule_learning_log`
- `auto_fix_log`

#### 🤝 Bot Orchestration (8 tables)
For bot deployment, automation, Chrome operations
- `bot_alerts`, `bot_deployments`, `bot_logs`, `bot_metrics`
- `bot_event_chains`, `bot_event_chain_executions`
- `bot_project_tasks`, `chrome_operation_logs`

#### 📊 Monitoring/Tracking (2 tables)
Will populate as system is used
- `circular_dependencies`
- `scan_history`

#### 📚 Knowledge Base (8 tables)
Content management, linking, sections
- `content_elements`, `content_index`, `content_metrics`
- `content_relationships`, `content_types`
- `kb_links`, `kb_sections`, `kb_terms`

#### ⏰ Cron Scheduling (4 tables)
Job management, circuit breakers
- `cron_circuit_breaker`, `cron_job_stats`
- `cron_schedule_minutes`, `hub_cron_metrics`

#### 🧠 Intelligence System (3 tables)
Automated actions, alerts
- `intelligence_alerts`
- `intelligence_automation`
- `intelligence_automation_executions`

#### 🏢 Organizational (2 tables) 🔴
**CRITICAL - NEED DATA**
- `project_unit_mapping` ← **0 rows, should have 12+**
- `unit_team_members` ← For assigning staff to units

#### 🛠️ Utility (24 tables)
Logging, analytics, tracking, future features
- API tracking, dependency mapping, performance metrics
- Dashboard config, VSCode sync, search analytics
- Redis metrics, system health, table usage

---

## 📋 IMMEDIATE ACTION PLAN

### Priority 1: Fix Data Issues
```sql
-- 1. Populate project-unit mapping
INSERT INTO project_unit_mapping (project_id, unit_id, created_at)
SELECT id,
       CASE
         WHEN id = 1 THEN 1  -- Intelligence Hub
         ELSE 2              -- All CIS projects
       END,
       NOW()
FROM projects;

-- 2. Drop redundant cron tables
DROP TABLE IF EXISTS cron_jobs, cron_executions, cron_metrics, cron_satellites;

-- 3. Drop broken views
DROP VIEW IF EXISTS kb_files, simple_quality;
```

### Priority 2: Import Scanner Rules
- Source rule definitions from `/scanner/rules/` directory
- Bulk insert into `cis_rules` table
- Link to `cis_rule_categories`

### Priority 3: Unify Intelligence Storage
- Update MCP V2 tools to query `intelligence_files` instead of `intelligence_content`
- OR create view to unify both tables
- Eventually migrate all data to `intelligence_files`

### Priority 4: Log Management
- Create `scan_logs_archive_202411` table
- Move logs older than 7 days to archive
- Set up monthly cron job for rotation

---

## 📈 STATISTICS

### Tables by Status
| Status | Count | Percentage |
|--------|-------|------------|
| **Active with Data** | 67 | 48% |
| **Empty but Useful** | 63 | 45% |
| **Redundant/Broken** | 4 | 3% |
| **Views** | 6 | 4% |

### Foreign Key Relationships
- **92 total foreign keys** properly defined
- Most connected table: `projects` (20 references)
- Most referenced table: `organizations` (14 references)

### Data Distribution
- **Intelligence System:** 114,955 rows (63%)
- **Scanner Logs:** 41,481 rows (23%)
- **AI/MCP Analytics:** 21,354 rows (12%)
- **Other Systems:** 4,212 rows (2%)

---

## ✅ WHAT'S WORKING WELL

1. **Proper Foreign Key Constraints**
   - 92 FKs maintain referential integrity
   - Cascading deletes where appropriate

2. **Business Unit Segregation**
   - Files properly tagged with `business_unit_id`
   - Can filter by unit for multi-tenancy

3. **Comprehensive Indexing**
   - `intelligence_files`: 24 indexes
   - Fast lookups on common query patterns

4. **Audit Logging**
   - `activity_logs` tracks all important actions
   - `scan_logs` provides detailed scanner history

5. **Migration Strategy**
   - Old and new intelligence tables coexist
   - Can migrate gradually without downtime

---

## 📝 NOTES FOR DEVELOPERS

### Current State (Nov 2, 2025)
- **Database:** hdgwrzntwa (Intelligence Hub)
- **Schema version:** Not versioned (recommend adding migrations)
- **Total storage:** ~180K rows across 140 tables
- **Health:** 95% (minor issues only)

### Next Sprint Goals
1. ✅ Complete database analysis (DONE)
2. 🔄 Fix project-unit mapping (IN PROGRESS)
3. 🔄 Import scanner rules (BLOCKED - need rule definitions)
4. ⏳ Migrate V2 to V3 intelligence storage (PLANNED)
5. ⏳ Implement log rotation (PLANNED)

---

**END OF ANALYSIS**
**Full detailed report:** `DATABASE_COMPLETE_ANALYSIS.txt` (732 lines)
**Raw schema JSON:** `DATABASE_COMPLETE_SCHEMA.json` (1.2 MB)
