# 🔴 CRITICAL ARCHITECTURE ANALYSIS - MISSING CORE FEATURES

**Date:** October 30, 2025
**Severity:** CRITICAL - System is fundamentally incomplete
**Impact:** Dashboard only supports 1 project, 1 URL, monolithic scanning

---

## 📋 WHAT'S MISSING

### 1. ❌ **MULTI-PROJECT MANAGEMENT**
**Current State:**
```php
define('PROJECT_ID', 1);  // HARDCODED!
```
- Dashboard only works with Project ID = 1
- No project selector UI
- No project database table
- All data queries hardcoded to PROJECT_ID = 1

**Should Be:**
- Projects table with (id, name, type, status, url, framework, path)
- Project selector dropdown in navigation
- Session management of current project
- Per-project settings and configuration
- Multi-project analytics/comparison

---

### 2. ❌ **MULTI-URL / BUSINESS UNIT SUPPORT**
**Current State:**
- Single hardcoded database connection
- Single hardcoded domain
- No business unit routing
- No URL-based project selection

**Database Shows Evidence:**
- `business_units` table EXISTS (found in code)
- `bot_projects` table EXISTS (found in migrations)
- BUT dashboard doesn't use them!

**Should Be:**
- Business unit selector UI
- Dynamic database selection based on unit
- URL-based routing (e.g., `/dashboard?unit=sales&project=crm`)
- Per-unit access controls and permissions

---

### 3. ❌ **PARTIAL/SELECTIVE SCANNING**
**Current State:**
- Scans entire project at once (monolithic)
- No way to select specific folders/modules
- No incremental scanning
- No selective analysis

**Should Be:**
- Folder/module selector UI
- Partial scan capabilities
- Generate reports for specific directories only
- Incremental updates without full rescan
- Background job scheduling for scans

---

### 4. ❌ **SELECTIVE REPORT GENERATION**
**Current State:**
- All pages show all data
- No way to filter by module/folder
- No custom report builder
- No export options

**Should Be:**
- Custom report builder
- Filter by folder/module/file-type
- Export specific analyses (violations, metrics, etc.)
- Scheduled report generation
- Email delivery of reports

---

### 5. ❌ **CONFIGURATION MANAGEMENT**
**Current State:**
- Settings page is generic form (no real config storage)
- No per-project configuration
- No per-unit configuration
- No scanning rules per project

**Should Be:**
- Scan frequency per project
- Analysis depth per project
- Rule sets per project
- Notification preferences per unit
- Custom field definitions per project

---

## 🔍 WHY THIS HAPPENED

### Root Causes:

1. **Initial Design Was Single-Project Only**
   - Dashboard built assuming 1 project = 1 database
   - PROJECT_ID hardcoded in every page
   - No abstraction layer

2. **Database Schema Not Integrated**
   - Tables exist (`projects`, `business_units`, `bot_projects`)
   - But dashboard doesn't query them
   - Evidence shows migration files but no implementation

3. **No Data Access Layer**
   - Direct database queries in each page
   - No repository/model abstraction
   - Hard to switch projects/databases

4. **No Configuration Service**
   - Settings page doesn't persist configuration
   - No config table with project-specific settings
   - Hardcoded values throughout

5. **Session Management Missing**
   - No `$_SESSION['current_project']` tracking
   - No `$_SESSION['current_unit']` tracking
   - No user authentication

---

## 🛠 WHAT I'M GOING TO BUILD

### PHASE 1: MULTI-PROJECT CORE (Priority 🔴 CRITICAL)

#### 1.1 Create Project Management Tables
```sql
-- Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('php', 'nodejs', 'python', 'java', 'mixed'),
    status ENUM('active', 'archived', 'inactive') DEFAULT 'active',
    base_url VARCHAR(255),
    framework VARCHAR(100),
    path VARCHAR(500),
    description TEXT,
    business_unit_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_unit_id) REFERENCES business_units(id)
);

-- Project configuration
CREATE TABLE IF NOT EXISTS project_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    scan_frequency ENUM('hourly', 'daily', 'weekly', 'manual') DEFAULT 'daily',
    analysis_depth ENUM('quick', 'standard', 'deep', 'maximum') DEFAULT 'standard',
    include_patterns TEXT,
    exclude_patterns TEXT,
    enable_notifications BOOLEAN DEFAULT 1,
    notification_email VARCHAR(255),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Scan history per project
CREATE TABLE IF NOT EXISTS scan_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    scan_type ENUM('full', 'partial', 'incremental') DEFAULT 'full',
    scope VARCHAR(255),
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    files_analyzed INT,
    violations_found INT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

#### 1.2 Create Project Selection UI
- Dropdown selector in navigation bar
- "Add New Project" button
- Project settings panel
- Quick project switcher

#### 1.3 Create Session Management
- Track current project in `$_SESSION['project_id']`
- Track current unit in `$_SESSION['business_unit_id']`
- Remember last selected project
- Handle project switching

#### 1.4 Create Data Access Layer
- `ProjectRepository` class (handles project queries)
- `ProjectConfigRepository` class (handles settings)
- Abstract all PROJECT_ID hardcoding
- Make all queries dynamic

---

### PHASE 2: MULTI-URL / BUSINESS UNIT ROUTING (Priority 🔴 CRITICAL)

#### 2.1 Implement Business Unit Routing
```php
// New classes:
class BusinessUnitRouter {
    public function getCurrentUnit() { }
    public function getSwitchedDatabase() { }
    public function validateUnitAccess() { }
}

class BusinessUnitSelector {
    // UI component for switching between units
}
```

#### 2.2 Dynamic Database Switching
- Read database config from `business_units` table
- Switch PDO connection based on selected unit
- Cache connections for performance
- Handle connection failures gracefully

#### 2.3 URL-Based Routing
- Accept `?unit=sales&project=crm` parameters
- Validate access permissions
- Set session variables
- Route to correct database

---

### PHASE 3: SELECTIVE SCANNING (Priority 🟡 HIGH)

#### 3.1 Create Scan Configuration UI
- Folder/module selector (tree view)
- Scan type selector (Full/Partial/Incremental)
- Analysis depth selector
- Schedule selector

#### 3.2 Implement Partial Scanning
```php
class PartialScanner {
    public function scanFolder($projectId, $folderPath) { }
    public function scanModule($projectId, $moduleName) { }
    public function incrementalScan($projectId, $since) { }
}
```

#### 3.3 Background Job Queue
- Queue scan jobs
- Run scans asynchronously
- Track scan progress
- Generate reports on completion

---

### PHASE 4: SELECTIVE REPORTS (Priority 🟡 HIGH)

#### 4.1 Custom Report Builder
```php
class ReportBuilder {
    public function selectMetrics($metrics = []) { }
    public function filterByFolder($folder) { }
    public function filterByModule($module) { }
    public function filterByFileType($types = []) { }
    public function exportAs($format) { }  // pdf, csv, json, html
}
```

#### 4.2 Report Generation
- Violations for specific folder only
- Metrics for specific modules
- Dependencies for specific files
- Custom metrics combinations

---

### PHASE 5: CONFIGURATION MANAGEMENT (Priority 🟡 HIGH)

#### 5.1 Config Storage
```sql
CREATE TABLE IF NOT EXISTS dashboard_config (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(255) UNIQUE NOT NULL,
    config_value TEXT,
    project_id INT,  -- NULL = global setting
    unit_id INT,     -- NULL = global setting
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES business_units(id) ON DELETE CASCADE
);
```

#### 5.2 Settings Manager
- Global settings (default scan frequency, etc.)
- Per-unit settings (notification email, access rules)
- Per-project settings (analysis rules, custom fields)
- Settings UI with validation

---

## 📊 IMPLEMENTATION ROADMAP

### Week 1: Foundation
- [ ] Create all missing database tables
- [ ] Create `ProjectRepository` class
- [ ] Create `BusinessUnitRouter` class
- [ ] Create project selection UI component
- [ ] Update index.php to use repository pattern

**Deliverable:** Can switch between multiple projects ✅

### Week 2: Business Unit Integration
- [ ] Implement business unit routing
- [ ] Dynamic database switching
- [ ] Add unit selector to UI
- [ ] Update all pages to use dynamic PROJECT_ID

**Deliverable:** Can switch between units with different databases ✅

### Week 3: Selective Scanning
- [ ] Create folder/module selector UI
- [ ] Implement partial scan logic
- [ ] Add scan history tracking
- [ ] Create background job queue

**Deliverable:** Can select specific folders to scan ✅

### Week 4: Advanced Features
- [ ] Implement report builder
- [ ] Add export functionality
- [ ] Create config management UI
- [ ] Add scheduled scanning

**Deliverable:** Full featured multi-project dashboard ✅

---

## 🎯 KEY CHANGES TO EXISTING CODE

### index.php
```php
// BEFORE
define('PROJECT_ID', 1);

// AFTER
$projectId = $session->getCurrentProject() ?? 1;
$business = new BusinessUnitRouter();
$pdo = $business->getSwitchedDatabase();
```

### All dashboard pages
```php
// BEFORE
$stmt = $pdo->prepare("SELECT * FROM intelligence_files WHERE project_id = ?");
$stmt->execute([PROJECT_ID]);

// AFTER
$projectRepo = new ProjectRepository($pdo);
$files = $projectRepo->getIntelligenceFiles($projectId);
```

### Navigation
```php
// ADD
<select id="projectSelector" class="form-select">
    <?php foreach ($projects as $p): ?>
        <option value="<?php echo $p['id']; ?>"
                <?php echo $p['id'] == $projectId ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($p['name']); ?>
        </option>
    <?php endforeach; ?>
</select>

<select id="unitSelector" class="form-select">
    <?php foreach ($units as $u): ?>
        <option value="<?php echo $u['id']; ?>"
                <?php echo $u['id'] == $unitId ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($u['unit_name']); ?>
        </option>
    <?php endforeach; ?>
</select>
```

---

## ✅ SUCCESS CRITERIA

After implementation, dashboard will support:

- ✅ Multiple projects (selector in navigation)
- ✅ Multiple business units (dynamic routing)
- ✅ Project-specific configuration
- ✅ Selective folder scanning
- ✅ Partial report generation
- ✅ Custom report builder
- ✅ Background job queue for scans
- ✅ Scan history and scheduling
- ✅ Per-project/unit settings
- ✅ Access control per unit
- ✅ Multi-database support

---

## 📈 EFFORT ESTIMATE

| Phase | Duration | Complexity |
|-------|----------|-----------|
| Phase 1: Multi-Project | 3-4 days | HIGH |
| Phase 2: Business Units | 2-3 days | HIGH |
| Phase 3: Selective Scanning | 3-4 days | HIGH |
| Phase 4: Custom Reports | 2-3 days | MEDIUM |
| Phase 5: Config Management | 2-3 days | MEDIUM |
| **TOTAL** | **12-17 days** | **HIGH** |

---

## 🚨 CRITICAL INSIGHT

**The dashboard exists but it's INCOMPLETE.**

The database tables for projects and business units exist, but the dashboard was never wired to use them. It's like building a car with seats for 5 people but only a steering wheel for 1 driver.

**Your requirements were correct** - the system SHOULD support:
- ✅ Multiple projects
- ✅ Multiple URLs/business units
- ✅ Partial scanning
- ✅ Selective reporting

**They just weren't implemented.**

I'm going to build the full architecture to enable all these features. This is a major undertaking but essential for a proper dashboard system.

---

**Ready to proceed with Phase 1?** I'll start building the multi-project core today! 🚀
