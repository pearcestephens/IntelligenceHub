# New Tables Design

**Date:** October 30, 2025
**Purpose:** 7 new tables for Context Generation + Hub Restructure
**Status:** Ready for implementation

---

## 📋 Tables Overview

1. **code_standards** - User coding preferences
2. **code_patterns** - Discovered code patterns
3. **code_dependencies** - File/class/function dependencies
4. **change_detection** - Change tracking and impact
5. **hub_projects** - Project registry
6. **hub_dependencies** - Cross-project dependencies
7. **hub_lost_knowledge** - Orphaned files catalog

---

## 🔧 SQL Schema

### 1. code_standards
**Purpose:** Store user coding preferences (Standards Library)

```sql
CREATE TABLE code_standards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    standard_key VARCHAR(100) NOT NULL UNIQUE,
    standard_value TEXT NOT NULL,
    category ENUM('database', 'framework', 'styling', 'security', 'performance', 'documentation') NOT NULL,
    priority INT DEFAULT 1,
    enforced BOOLEAN DEFAULT TRUE,
    description TEXT,
    examples JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_enforced (enforced)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert user preferences
INSERT INTO code_standards (standard_key, standard_value, category, priority, description) VALUES
('database.driver', 'PDO', 'database', 10, 'Always use PDO, never mysqli'),
('database.statements', 'prepared', 'database', 10, 'Always use prepared statements'),
('framework.frontend', 'Bootstrap 4.2', 'framework', 9, 'Use Bootstrap 4.2 for all UI'),
('framework.js', 'jQuery 3.6 + Vanilla ES6', 'framework', 8, 'jQuery for legacy, ES6 for new'),
('styling.standard', 'PSR-12', 'styling', 9, 'PHP code follows PSR-12'),
('styling.autoload', 'PSR-4', 'styling', 9, 'Autoloading follows PSR-4'),
('styling.indent', '4 spaces', 'styling', 7, 'Use 4 spaces for indentation'),
('security.csrf', 'always', 'security', 10, 'CSRF protection on all forms'),
('security.validation', 'always', 'security', 10, 'Validate all input'),
('performance.query_threshold', '300ms', 'performance', 8, 'Queries should complete < 300ms'),
('performance.file_size', '500 lines', 'performance', 7, 'Files should be < 500 lines'),
('documentation.phpdoc', 'required', 'documentation', 9, 'All functions need PHPDoc'),
('documentation.readme', 'required', 'documentation', 9, 'All projects need README');
```

---

### 2. code_patterns
**Purpose:** Store discovered code patterns from codebase

```sql
CREATE TABLE code_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pattern_name VARCHAR(200) NOT NULL,
    pattern_type ENUM('function', 'class', 'sql', 'api', 'security', 'other') NOT NULL,
    pattern_code TEXT NOT NULL,
    description TEXT,
    usage_count INT DEFAULT 0,
    first_seen_file VARCHAR(500),
    quality_score DECIMAL(3,2) DEFAULT 0.00,
    is_recommended BOOLEAN DEFAULT FALSE,
    tags JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pattern_type (pattern_type),
    INDEX idx_quality_score (quality_score),
    INDEX idx_recommended (is_recommended),
    FULLTEXT idx_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 3. code_dependencies
**Purpose:** Track file, class, and function dependencies

```sql
CREATE TABLE code_dependencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_file VARCHAR(500) NOT NULL,
    source_type ENUM('file', 'class', 'function', 'trait', 'interface') NOT NULL,
    source_name VARCHAR(200) NOT NULL,
    target_file VARCHAR(500) NOT NULL,
    target_type ENUM('file', 'class', 'function', 'trait', 'interface') NOT NULL,
    target_name VARCHAR(200) NOT NULL,
    dependency_type ENUM('require', 'include', 'extends', 'implements', 'uses', 'calls', 'instantiates') NOT NULL,
    line_number INT,
    is_circular BOOLEAN DEFAULT FALSE,
    depth_level INT DEFAULT 1,
    scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source_file (source_file(255)),
    INDEX idx_target_file (target_file(255)),
    INDEX idx_dependency_type (dependency_type),
    INDEX idx_circular (is_circular),
    INDEX idx_composite (source_file(200), target_file(200))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 4. change_detection
**Purpose:** Track changes and their impact

```sql
CREATE TABLE change_detection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(500) NOT NULL,
    change_type ENUM('created', 'modified', 'deleted', 'renamed', 'moved') NOT NULL,
    old_hash VARCHAR(64),
    new_hash VARCHAR(64),
    old_path VARCHAR(500),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    impact_level ENUM('none', 'low', 'medium', 'high', 'critical') DEFAULT 'medium',
    affected_files JSON,
    breaking_change BOOLEAN DEFAULT FALSE,
    auto_analyzed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    INDEX idx_file_path (file_path(255)),
    INDEX idx_change_type (change_type),
    INDEX idx_changed_at (changed_at),
    INDEX idx_impact_level (impact_level),
    INDEX idx_breaking (breaking_change)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 5. hub_projects
**Purpose:** Registry of all projects in the hub

```sql
CREATE TABLE hub_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_key VARCHAR(100) NOT NULL UNIQUE,
    project_name VARCHAR(200) NOT NULL,
    project_type ENUM('website', 'api', 'library', 'tool', 'automation', 'other') NOT NULL,
    root_path VARCHAR(500) NOT NULL,
    description TEXT,
    primary_language VARCHAR(50),
    framework VARCHAR(100),
    database_name VARCHAR(100),
    git_repo VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    last_scanned TIMESTAMP NULL,
    file_count INT DEFAULT 0,
    total_size_mb DECIMAL(10,2) DEFAULT 0.00,
    standards_compliant DECIMAL(5,2) DEFAULT 0.00,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_project_key (project_key),
    INDEX idx_is_active (is_active),
    INDEX idx_last_scanned (last_scanned),
    FULLTEXT idx_search (project_name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 6. hub_dependencies
**Purpose:** Cross-project dependencies

```sql
CREATE TABLE hub_dependencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_project_id INT NOT NULL,
    target_project_id INT NOT NULL,
    dependency_type ENUM('library', 'api', 'database', 'service', 'file', 'other') NOT NULL,
    dependency_path VARCHAR(500),
    is_required BOOLEAN DEFAULT TRUE,
    version_constraint VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_project_id) REFERENCES hub_projects(id) ON DELETE CASCADE,
    FOREIGN KEY (target_project_id) REFERENCES hub_projects(id) ON DELETE CASCADE,
    INDEX idx_source (source_project_id),
    INDEX idx_target (target_project_id),
    INDEX idx_type (dependency_type),
    INDEX idx_required (is_required)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 7. hub_lost_knowledge
**Purpose:** Catalog orphaned and forgotten files

```sql
CREATE TABLE hub_lost_knowledge (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(500) NOT NULL UNIQUE,
    file_type VARCHAR(50),
    file_size_bytes BIGINT,
    last_modified TIMESTAMP NULL,
    discovered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reason ENUM('orphaned', 'unreferenced', 'outdated', 'duplicate', 'unknown') NOT NULL,
    referenced_by JSON,
    potential_value ENUM('none', 'low', 'medium', 'high', 'critical') DEFAULT 'medium',
    action_taken ENUM('keep', 'archive', 'delete', 'review', 'none') DEFAULT 'none',
    reviewed BOOLEAN DEFAULT FALSE,
    notes TEXT,
    INDEX idx_file_path (file_path(255)),
    INDEX idx_reason (reason),
    INDEX idx_potential_value (potential_value),
    INDEX idx_reviewed (reviewed),
    INDEX idx_action (action_taken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📦 Installation Script

```sql
-- Run this to create all 7 tables
SOURCE /path/to/new_tables_schema.sql;

-- Verify creation
SELECT
    TABLE_NAME,
    TABLE_ROWS,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
AND TABLE_NAME IN (
    'code_standards',
    'code_patterns',
    'code_dependencies',
    'change_detection',
    'hub_projects',
    'hub_dependencies',
    'hub_lost_knowledge'
)
ORDER BY TABLE_NAME;
```

---

## 🔗 Relationships

```
hub_projects (1) ──→ (N) hub_dependencies
                 ──→ (N) code_dependencies
                 ──→ (N) hub_lost_knowledge

code_standards ──→ (enforced in) code_patterns
code_patterns  ──→ (found in) code_dependencies
code_dependencies ──→ (impacts) change_detection
change_detection ──→ (affects) hub_projects
```

---

**Last Updated:** October 30, 2025
**Version:** 1.0.0
**Status:** ✅ Ready for implementation
