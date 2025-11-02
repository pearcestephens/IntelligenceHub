# 🚨 ARCHITECTURE ISSUE #1: ROOT CAUSE ANALYSIS

**Date:** October 30, 2025
**Issue:** Dashboard hardcoded to single project ($projectId = 1)

---

## THE PROBLEM

### Current Code (All Pages)
```php
<?php
$projectId = 1;  // ← HARDCODED TO PROJECT 1 ONLY
$page = isset($_GET['file_page']) ? (int)$_GET['file_page'] : 1;
// ... rest of dashboard logic
```

**Found in:**
- ✅ overview.php (line 9)
- ✅ files.php (line 9)
- ✅ dependencies.php (line 9)
- ✅ violations.php (line 10)
- ✅ rules.php (line 9)
- ✅ metrics.php (line 9)
- ✅ settings.php (line 10)

**Status:** 🔴 **ALL 7 PAGES HARDCODED**

---

## WHY THIS IS A CRITICAL FLAW

### Missing Functionality #1: Multi-Project Support
| Feature | Status | Should Have |
|---------|--------|-------------|
| Project dropdown selector | ❌ | SELECT project_id FROM projects |
| Project switching | ❌ | Change ?project=1 to ?project=2 |
| Project list view | ❌ | /dashboard/projects.php |
| Project creation UI | ❌ | /dashboard/create-project.php |
| Project settings | ❌ | /dashboard/project-settings.php |

### Missing Functionality #2: Business Unit / URL Management
| Feature | Status | Should Have |
|---------|--------|-------------|
| Business unit selector | ❌ | Link projects to business units |
| URL/Domain mapping | ❌ | projects.url_source column |
| Multi-domain analysis | ❌ | Filter by source URL |
| Business unit view | ❌ | Group results by business unit |

### Missing Functionality #3: Selective Scanning
| Feature | Status | Should Have |
|---------|--------|-------------|
| Folder/module selection | ❌ | Scan specific directories only |
| Partial scans | ❌ | Filter by file path pattern |
| Scheduled scans | ❌ | Project scan schedule table |
| Scan history | ❌ | Audit trail of scans |

---

## DATABASE SCHEMA - WHAT EXISTS

### ✅ Projects Table EXISTS
```
projects table has:
- project_id (PK)
- project_name
- project_type
- framework
- version
- status
- health_score
- path (← THIS IS THE FOLDER PATH!)
- description
```

### ✅ But No References In Dashboard
**Problem:** Dashboard never queries `projects` table
**Result:** Always uses hardcoded projectId = 1
**Impact:** Can't switch between projects

---

## MISSING TABLES (Don't Exist Yet)

### ❌ Business Units Table
```sql
-- MISSING - Should exist but doesn't
CREATE TABLE business_units (
    unit_id INT PRIMARY KEY,
    unit_name VARCHAR(255),
    unit_type ENUM('company', 'division', 'team'),
    base_url VARCHAR(255),
    environment ENUM('production', 'staging', 'development')
);
```

### ❌ Project-to-Business Unit Mapping
```sql
-- MISSING - Should link projects to business units
CREATE TABLE project_business_unit_mapping (
    mapping_id INT PRIMARY KEY,
    project_id INT,
    unit_id INT,
    url_source VARCHAR(255)
);
```

### ❌ Scan Configuration Table
```sql
-- MISSING - Should control partial/selective scans
CREATE TABLE scan_configurations (
    config_id INT PRIMARY KEY,
    project_id INT,
    folder_pattern VARCHAR(255),
    include_patterns TEXT,
    exclude_patterns TEXT,
    scan_depth INT,
    enabled TINYINT
);
```

---

## WHY THIS HAPPENED

### Root Causes

**1. Dashboard Built for Single Project Demo**
- Initial MVP only needed to show one project's data
- Never upgraded to multi-project architecture

**2. No Project Selection UI**
- No navigation component to switch projects
- No query parameter handling for project_id

**3. Database Schema Incomplete**
- business_units table never created
- project_business_unit_mapping never created
- scan_configurations table never created

**4. No Business Unit Concept**
- Dashboard doesn't understand "which business unit owns this?"
- No filtering by business unit
- No URL/domain source tracking

**5. No Scan Targeting**
- Scanner always scans entire project folder
- No way to specify partial scan (e.g., "only src/ folder")
- No selective scanning per business unit/URL

---

## IMPACT SUMMARY

| Feature | Impact | Users Affected |
|---------|--------|----------------|
| **Hardcoded Project #1** | Can only view one project | All users |
| **No Project Selector** | Can't switch projects | All users |
| **No Business Units** | Can't organize by business unit | Multi-unit organizations |
| **No URL Management** | Can't track which URL each scan came from | Multiple domain users |
| **No Partial Scans** | Must scan entire project every time | Users with large projects |
| **No Selective Scanning** | Can't target specific folders | Users with large monorepos |

---

## WHAT NEEDS TO HAPPEN

### Phase 1: Add Project Selection (Week 1)
- [ ] Create project_id dropdown in navigation
- [ ] Remove hardcoded $projectId = 1
- [ ] Add ?project=X to all pages
- [ ] Validate project access

### Phase 2: Add Business Unit Support (Week 2)
- [ ] Create business_units table
- [ ] Create mapping table
- [ ] Add URL/domain source tracking
- [ ] Filter dashboard by business unit

### Phase 3: Add Selective Scanning (Week 3)
- [ ] Create scan_configurations table
- [ ] Add folder/file pattern selection UI
- [ ] Implement partial scan logic in scanner
- [ ] Add scan history/audit log

---

## NEXT: See ARCHITECTURE_ISSUE_02_*.md for specific implementation details
