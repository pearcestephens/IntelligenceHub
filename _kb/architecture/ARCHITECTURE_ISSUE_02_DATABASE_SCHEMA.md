# 🔧 ARCHITECTURE ISSUE #2: DATABASE SCHEMA FIX

**Date:** October 30, 2025
**Status:** Schema design phase

---

## SQL SCHEMA TO CREATE

### Table 1: Business Units
```sql
CREATE TABLE IF NOT EXISTS business_units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    unit_name VARCHAR(255) NOT NULL,
    unit_type ENUM('company', 'division', 'department', 'team') NOT NULL,
    description TEXT,
    base_url VARCHAR(500),
    environment ENUM('production', 'staging', 'development') DEFAULT 'production',
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_name (unit_name),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Organize projects by company/division/department

---

### Table 2: Project-Unit Mapping
```sql
CREATE TABLE IF NOT EXISTS project_unit_mapping (
    mapping_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    unit_id INT NOT NULL,
    url_source VARCHAR(500) COMMENT 'The URL/domain this project was scanned from',
    role ENUM('primary', 'secondary', 'archived') DEFAULT 'primary',
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES business_units(unit_id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_unit (project_id, unit_id),
    INDEX idx_unit_id (unit_id),
    INDEX idx_url_source (url_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Link projects to business units and track source URLs

---

### Table 3: Scan Configurations
```sql
CREATE TABLE IF NOT EXISTS scan_configurations (
    config_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    config_name VARCHAR(255) NOT NULL,
    scan_type ENUM('full', 'partial', 'incremental') DEFAULT 'full',

    -- Path filtering
    folder_patterns TEXT COMMENT 'JSON array of folder paths to include/exclude',
    include_patterns TEXT COMMENT 'Regex patterns to include (e.g., *.php, src/**)',
    exclude_patterns TEXT COMMENT 'Regex patterns to exclude (e.g., vendor/**, node_modules/**)',

    -- Scan configuration
    max_file_size INT COMMENT 'Skip files larger than this (bytes)',
    max_files INT COMMENT 'Maximum files to scan per run',
    scan_depth INT COMMENT 'Directory depth to scan (0 = unlimited)',

    -- Scheduling
    enabled TINYINT DEFAULT 1,
    scan_schedule VARCHAR(100) COMMENT 'Cron expression or frequency',
    last_scan_at DATETIME,
    next_scan_at DATETIME,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id),
    INDEX idx_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Define what folders/files to scan and when

---

### Table 4: Scan History
```sql
CREATE TABLE IF NOT EXISTS scan_history (
    scan_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    config_id INT,
    scan_type ENUM('full', 'partial', 'incremental') DEFAULT 'full',

    -- Scan results
    total_files_scanned INT,
    files_added INT,
    files_modified INT,
    files_deleted INT,

    status ENUM('pending', 'running', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    error_message TEXT,

    started_at DATETIME,
    completed_at DATETIME,
    duration_seconds INT,

    triggered_by VARCHAR(255) COMMENT 'admin user, scheduled, api, etc',

    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (config_id) REFERENCES scan_configurations(config_id) ON DELETE SET NULL,
    INDEX idx_project_id (project_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Track all scan executions for audit trail and debugging

---

### Table 5: User Project Access (Add to existing)
```sql
-- Add to projects table if not present
ALTER TABLE projects ADD COLUMN IF NOT EXISTS
    owner_unit_id INT,
ADD FOREIGN KEY (owner_unit_id) REFERENCES business_units(unit_id) ON DELETE SET NULL;

-- Create user-project access control
CREATE TABLE IF NOT EXISTS user_project_access (
    access_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    project_id INT NOT NULL,
    unit_id INT,
    access_level ENUM('view', 'edit', 'admin', 'owner') DEFAULT 'view',
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (project_id) REFERENCES projects(project_id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES business_units(unit_id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_project (user_id, project_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Purpose:** Control which users can access which projects

---

## SAMPLE DATA

### Insert business units
```sql
INSERT INTO business_units (unit_name, unit_type, description, base_url, environment) VALUES
('Vapeshed NZ', 'company', 'Main Vapeshed business unit', 'vapeshed.co.nz', 'production'),
('Vapeshed AU', 'division', 'Australian operations', 'vapeshed.com.au', 'production'),
('Development Team', 'department', 'Internal development', 'dev.vapeshed.co.nz', 'staging'),
('QA Team', 'team', 'Quality assurance', 'qa.vapeshed.co.nz', 'staging');
```

### Map existing projects
```sql
-- Assuming you have a project already
INSERT INTO project_unit_mapping (project_id, unit_id, url_source, role) VALUES
(1, 1, 'https://staff.vapeshed.co.nz', 'primary');
```

### Create default scan config
```sql
INSERT INTO scan_configurations (project_id, config_name, scan_type, include_patterns, exclude_patterns, enabled) VALUES
(1, 'Default Full Scan', 'full', '*.php,*.js,*.css', 'vendor/**,node_modules/**,tests/**', 1),
(1, 'Source Only', 'partial', 'src/**', 'vendor/**,node_modules/**', 1);
```

---

## MIGRATION SCRIPT

```sql
-- Run this to create all new tables
SOURCE schema-additions.sql;

-- Verify creation
SHOW TABLES LIKE '%unit%';
SHOW TABLES LIKE '%mapping%';
SHOW TABLES LIKE '%config%';
SHOW TABLES LIKE '%history%';
```

---

## NEXT STEPS

1. ✅ Schema designed (this document)
2. ⏳ Execute SQL to create tables
3. ⏳ Add migration script to project setup
4. ⏳ Update dashboard PHP to query these tables
5. ⏳ Create UI for managing business units and scan configs

See: **ARCHITECTURE_ISSUE_03_DASHBOARD_CHANGES.md**
