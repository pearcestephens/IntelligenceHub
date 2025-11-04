# Database Analysis Archive
**Date:** November 2, 2025
**Database:** Intelligence Hub (hdgwrzntwa)
**Purpose:** Complete formal database analysis and relationship mapping

---

## 📂 Files in This Archive

### 📊 Analysis Reports

1. **DATABASE_QUICK_REFERENCE.txt** (16 KB)
   - One-page quick reference card
   - Database stats at a glance
   - Critical issues summary
   - Quick fix commands
   - Next steps checklist
   - **START HERE** for quick overview

2. **DATABASE_ANALYSIS_SUMMARY.md** (12 KB)
   - Executive summary with diagrams
   - Top 10 largest tables
   - Organizational hierarchy visualization
   - Critical issues breakdown
   - Recommended for stakeholders

3. **DATABASE_COMPLETE_ANALYSIS.txt** (35 KB, 732 lines)
   - Complete technical analysis
   - All 9 sections with FK mappings
   - Scanner pipeline diagrams
   - Intelligence architecture
   - Full findings and recommendations
   - For technical deep-dive

4. **DATABASE_COMPLETE_SCHEMA.json** (1.2 MB)
   - Raw structured schema data
   - All 140 tables with metadata
   - Column definitions, indexes, FK relationships
   - Up to 5 sample data rows per table
   - For programmatic analysis

### 🛠️ Scripts & Tools

5. **extract_full_schema.php** (4.8 KB)
   - Schema extraction tool
   - Connects to database and exports complete structure
   - Handles views vs tables correctly
   - Generates DATABASE_COMPLETE_SCHEMA.json
   - Run: `php extract_full_schema.php`

6. **analyze_relationships.php** (25 KB)
   - Comprehensive relationship analyzer
   - Maps all 92 foreign key relationships
   - Categorizes tables into 12 functional groups
   - Identifies redundant/empty tables
   - Generates complete analysis reports
   - Run: `php analyze_relationships.php > output.txt`

7. **fix_database_issues.sql** (5.4 KB)
   - SQL fix script (ALREADY EXECUTED ✓)
   - Fixed project_unit_mapping (0 → 12 rows)
   - Dropped 4 redundant cron tables
   - Dropped 2 broken views
   - Created log archive table
   - Status: **COMPLETED**

---

## 📊 Database Overview

- **Total Tables:** 140
- **Total Rows:** 182,002
- **Foreign Keys:** 92 relationships
- **Health Status:** 95% (minor issues resolved)

### Table Classification

- **67 tables with data** (48%) - Active tables
- **63 empty but useful** (45%) - Designed for future use
- **4 redundant** (3%) - **DROPPED** ✓
- **6 views** (2 broken - **DROPPED** ✓)

---

## 🏗️ Organizational Hierarchy

```
┌─────────────────┐
│ organizations   │ 1 row (Ecigdis Limited)
└────────┬────────┘
         │ org_id
         ▼
┌─────────────────┐
│ business_units  │ 4 rows (Hub, CIS, Retail, Wholesale)
└────────┬────────┘
         │ unit_id
         ▼
┌─────────────────┐
│    projects     │ 12 rows (Scanner + CIS modules)
└────────┬────────┘
         │ project_id
         ▼
┌─────────────────┐
│ project_domains │ 11 rows (URLs/subdomains)
└────────┬────────┘
         │
         ▼
┌──────────────────┐
│ intelligence_    │ 26,121 files scanned
│    files         │
└──────────────────┘
```

**Status:** ✓ **NOW PROPERLY LINKED** (project_unit_mapping populated)

---

## ✅ Issues Fixed (November 2, 2025)

### Critical Issues Resolved:

1. ✅ **project_unit_mapping populated**
   - Was: 0 rows (organizational hierarchy broken)
   - Now: 12 rows (all projects linked to business units)
   - Verified: All CIS modules → CIS unit, Scanner → Hub unit

2. ✅ **Redundant cron tables dropped**
   - Dropped: cron_jobs, cron_executions, cron_metrics, cron_satellites
   - Using: hub_cron_jobs, hub_cron_executions (with data)

3. ✅ **Broken views removed**
   - Dropped: kb_files, simple_quality (referenced invalid tables)

4. ✅ **Log archive table created**
   - Created: scan_logs_archive_202411
   - Ready for log rotation implementation

---

## 🚨 Outstanding Issues (Still Required)

### 1. Scanner Rules Missing
- **Table:** cis_rules
- **Current:** 0 rows
- **Impact:** Scanner cannot detect code violations
- **Action Required:** Import rule definitions from scanner config
- **Priority:** HIGH

### 2. Dual Intelligence Systems
- **Issue:** Two separate intelligence storage systems
  - intelligence_content: 22,191 rows (V2 tools read from this)
  - intelligence_files: 26,121 rows (V3 scanner writes to this)
- **Impact:** Systems are incompatible, data fragmentation
- **Action Required:** Migrate V2 tools to use intelligence_files
- **Priority:** MEDIUM

### 3. Log Rotation Needed
- **Table:** scan_logs
- **Current:** 41,481 records (growing indefinitely)
- **Action Required:** Implement cron job to archive records older than 7 days
- **Priority:** LOW

### 4. Team Members Not Populated
- **Table:** unit_team_members
- **Current:** 0 rows
- **Impact:** Cannot track which users belong to which business units
- **Priority:** LOW

---

## 📈 Top 10 Largest Tables

1. intelligence_files_backup_20251025 - 55,357 rows (archive candidate)
2. scan_logs - 41,481 rows (needs rotation)
3. intelligence_files - 26,121 rows (Scanner V3 output)
4. intelligence_content - 22,191 rows (V2 tools storage)
5. mcp_performance_metrics - 21,329 rows (MCP analytics)
6. intelligence_content_text - 11,286 rows (full-text index)
7. intelligence_metrics - 3,000 rows (usage stats)
8. code_patterns - 213 rows (pattern library)
9. scanner_ignore_config - 146 rows (exclusion rules)
10. cis_mcp_tool_usage_view - 125 rows (tool stats view)

---

## 🔄 Next Steps

### Immediate (This Week)
- [ ] Import scanner rules into cis_rules table
- [ ] Test scanner violation detection
- [ ] Document rule definitions

### Short-term (This Month)
- [ ] Migrate V2 MCP tools to intelligence_files
- [ ] Test unified intelligence system
- [ ] Implement log rotation cron job

### Long-term (This Quarter)
- [ ] Populate unit_team_members
- [ ] Archive intelligence_files_backup_20251025
- [ ] Set up automated schema monitoring

---

## 📚 Related Documentation

- Main KB: `/_kb/`
- Scanner Docs: `/_kb/scanner/`
- MCP Tools: `/_kb/mcp-tools/`
- Database Config: `/config/database.php`

---

## 🔧 Quick Commands

### Re-extract Schema
```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/database-analysis
php extract_full_schema.php
```

### Re-run Analysis
```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/database-analysis
php analyze_relationships.php > NEW_ANALYSIS_$(date +%Y%m%d).txt
```

### Check Organizational Hierarchy
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
SELECT u.unit_name, COUNT(pum.project_id) as project_count
FROM business_units u
LEFT JOIN project_unit_mapping pum ON u.unit_id = pum.unit_id
GROUP BY u.unit_id
ORDER BY u.unit_id"
```

### View Project Assignments
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
SELECT u.unit_name, p.project_name, pum.role
FROM business_units u
JOIN project_unit_mapping pum ON u.unit_id = pum.unit_id
JOIN projects p ON pum.project_id = p.id
ORDER BY u.unit_id, p.project_name"
```

---

## 📝 Analysis Methodology

1. **Schema Extraction**
   - Connected to Intelligence Hub database
   - Extracted DESCRIBE for all 140 tables
   - Retrieved indexes and foreign key relationships
   - Captured up to 5 sample rows per table
   - Handled views vs tables correctly (views have no indexes)

2. **Relationship Mapping**
   - Parsed all 92 foreign key constraints
   - Categorized tables into 12 functional groups
   - Built organizational hierarchy diagram
   - Mapped scanner pipeline flow
   - Identified circular dependencies (none found)

3. **Classification**
   - Analyzed row counts for all tables
   - Identified 4 redundant tables (duplicates)
   - Classified 63 empty tables by purpose
   - Documented 67 active tables with data

4. **Issue Identification**
   - Found project_unit_mapping empty (critical)
   - Detected broken views (kb_files, simple_quality)
   - Identified dual intelligence systems (V2 vs V3)
   - Noted missing scanner rules
   - Flagged large log table (needs rotation)

---

## 🎯 Success Metrics

### Organizational Hierarchy
- ✅ **BEFORE:** Projects orphaned (0 unit mappings)
- ✅ **AFTER:** All 12 projects properly linked
- ✅ **VERIFIED:** Hub unit has 1 project, CIS unit has 11 projects

### Database Cleanliness
- ✅ **BEFORE:** 4 redundant tables + 2 broken views
- ✅ **AFTER:** All redundant/broken objects dropped
- ✅ **VERIFIED:** 140 → 134 objects (6 removed)

### Documentation
- ✅ Complete schema extracted (1.2 MB JSON)
- ✅ Formal analysis report (732 lines)
- ✅ Executive summary created
- ✅ Quick reference card generated
- ✅ Fix script created and executed

---

**Archive Status:** Complete ✓
**Last Updated:** November 2, 2025
**Maintained By:** Database Analysis Scripts (auto-update on re-run)
