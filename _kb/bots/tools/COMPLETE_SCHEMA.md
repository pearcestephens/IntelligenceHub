# 📊 Complete Database Schema - Ecigdis Enterprise Platform

**Database:** hdgwrzntwa  
**Version:** 1.0.0  
**Last Updated:** October 21, 2025

---

## Table of Contents

1. [Schema Overview](#schema-overview)
2. [Knowledge Base Tables (`ecig_kb_*`)](#knowledge-base-tables)
3. [Business Intelligence Tables (`ecig_bi_*`)](#business-intelligence-tables)
4. [AI Agent Tables (`ecig_ai_*`)](#ai-agent-tables)
5. [Live Chat Tables (`ecig_chat_*`)](#live-chat-tables)
6. [API Gateway Tables (`ecig_api_*`)](#api-gateway-tables)
7. [Relationships & Foreign Keys](#relationships--foreign-keys)
8. [Indexes & Performance](#indexes--performance)
9. [Installation SQL](#installation-sql)

---

## 🗂️ Schema Overview

### Table Prefixes

| Prefix | Purpose | Tables | Total Rows (Est.) |
|--------|---------|--------|-------------------|
| `ecig_kb_*` | Knowledge Base | 10 | ~50,000 |
| `ecig_bi_*` | Business Intelligence | 15 | ~1,000,000+ |
| `ecig_ai_*` | AI Infrastructure | 12 | ~500,000 |
| `ecig_chat_*` | Live Chat | 8 | ~100,000+ |
| `ecig_api_*` | API Gateway | 5 | ~10,000 |

**Total Tables:** 50  
**Total Estimated Size:** 5-10 GB

---

## 📚 Knowledge Base Tables (`ecig_kb_*`)

### 1. `ecig_kb_file_memory`
**Purpose:** Core file intelligence and code memory

```sql
CREATE TABLE ecig_kb_file_memory (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    file_path VARCHAR(500) NOT NULL UNIQUE,
    file_type ENUM('php', 'js', 'css', 'html', 'sql', 'md', 'json', 'other') NOT NULL,
    module VARCHAR(100),
    functions JSON,            -- Array of function names
    classes JSON,              -- Array of class names
    dependencies JSON,         -- Files this file requires
    used_by JSON,              -- Files that use this file
    lines_of_code INT,
    complexity_score INT,
    last_modified DATETIME,
    last_scanned DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_file_type (file_type),
    INDEX idx_module (module),
    INDEX idx_last_modified (last_modified),
    FULLTEXT idx_file_path (file_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. `ecig_kb_functions`
**Purpose:** Function-level documentation and intelligence

```sql
CREATE TABLE ecig_kb_functions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    file_id BIGINT NOT NULL,
    function_name VARCHAR(255) NOT NULL,
    parameters JSON,
    return_type VARCHAR(100),
    description TEXT,
    complexity INT,
    line_start INT,
    line_end INT,
    calls_functions JSON,      -- Functions this function calls
    called_by JSON,            -- Functions that call this
    last_analyzed DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (file_id) REFERENCES ecig_kb_file_memory(id) ON DELETE CASCADE,
    INDEX idx_function_name (function_name),
    INDEX idx_complexity (complexity),
    FULLTEXT idx_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. `ecig_kb_classes`
**Purpose:** Class-level documentation

```sql
CREATE TABLE ecig_kb_classes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    file_id BIGINT NOT NULL,
    class_name VARCHAR(255) NOT NULL,
    namespace VARCHAR(255),
    extends VARCHAR(255),
    implements JSON,
    properties JSON,
    methods JSON,
    description TEXT,
    line_start INT,
    line_end INT,
    last_analyzed DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (file_id) REFERENCES ecig_kb_file_memory(id) ON DELETE CASCADE,
    INDEX idx_class_name (class_name),
    INDEX idx_namespace (namespace),
    FULLTEXT idx_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. `ecig_kb_relationships`
**Purpose:** File dependency mapping

```sql
CREATE TABLE ecig_kb_relationships (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    source_file_id BIGINT NOT NULL,
    target_file_id BIGINT NOT NULL,
    relationship_type ENUM('requires', 'includes', 'extends', 'implements', 'uses') NOT NULL,
    strength INT DEFAULT 1,    -- How tightly coupled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (source_file_id) REFERENCES ecig_kb_file_memory(id) ON DELETE CASCADE,
    FOREIGN KEY (target_file_id) REFERENCES ecig_kb_file_memory(id) ON DELETE CASCADE,
    INDEX idx_source (source_file_id),
    INDEX idx_target (target_file_id),
    INDEX idx_type (relationship_type),
    UNIQUE KEY unique_relationship (source_file_id, target_file_id, relationship_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5. `ecig_kb_components`
**Purpose:** High-level component registry

```sql
CREATE TABLE ecig_kb_components (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    component_name VARCHAR(255) NOT NULL,
    component_type ENUM('service', 'library', 'module', 'widget', 'api', 'cron', 'other') NOT NULL,
    description TEXT,
    files JSON,                -- Array of file paths
    dependencies JSON,         -- Other components needed
    status ENUM('active', 'deprecated', 'planned') DEFAULT 'active',
    owner VARCHAR(100),
    documentation_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_component (component_name),
    INDEX idx_type (component_type),
    INDEX idx_status (status),
    FULLTEXT idx_description (description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6. `ecig_kb_intelligence`
**Purpose:** AI-discovered insights and patterns

```sql
CREATE TABLE ecig_kb_intelligence (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    insight_type ENUM('pattern', 'antipattern', 'optimization', 'security', 'bug_risk', 'other') NOT NULL,
    severity ENUM('critical', 'high', 'medium', 'low', 'info') DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    description TEXT,
    affected_files JSON,
    recommendation TEXT,
    discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'acknowledged', 'fixed', 'ignored') DEFAULT 'new',
    assigned_to VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (insight_type),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    FULLTEXT idx_title_desc (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 7. `ecig_kb_documentation`
**Purpose:** Manual documentation entries

```sql
CREATE TABLE ecig_kb_documentation (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    doc_type ENUM('guide', 'tutorial', 'reference', 'faq', 'troubleshooting', 'other') NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT,
    category VARCHAR(100),
    tags JSON,
    related_components JSON,
    author VARCHAR(100),
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT DEFAULT 0,
    last_viewed DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (doc_type),
    INDEX idx_category (category),
    INDEX idx_status (status),
    FULLTEXT idx_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 8. `ecig_kb_search_index`
**Purpose:** Optimized search indexing

```sql
CREATE TABLE ecig_kb_search_index (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    entity_type ENUM('file', 'function', 'class', 'component', 'doc') NOT NULL,
    entity_id BIGINT NOT NULL,
    searchable_text LONGTEXT,
    keywords JSON,
    last_indexed DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_entity (entity_type, entity_id),
    FULLTEXT idx_search (searchable_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 9. `ecig_kb_changelog`
**Purpose:** Track all KB changes

```sql
CREATE TABLE ecig_kb_changelog (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    change_type ENUM('file_added', 'file_modified', 'file_deleted', 'function_added', 'function_modified', 'function_deleted', 'other') NOT NULL,
    entity_type VARCHAR(50),
    entity_id BIGINT,
    file_path VARCHAR(500),
    changes JSON,
    changed_by VARCHAR(100),
    changed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_change_type (change_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 10. `ecig_kb_statistics`
**Purpose:** KB usage statistics

```sql
CREATE TABLE ecig_kb_statistics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    stat_date DATE NOT NULL,
    total_files INT DEFAULT 0,
    total_functions INT DEFAULT 0,
    total_classes INT DEFAULT 0,
    total_lines_code INT DEFAULT 0,
    avg_complexity DECIMAL(5,2),
    search_queries INT DEFAULT 0,
    most_viewed_docs JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_date (stat_date),
    INDEX idx_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📈 Business Intelligence Tables (`ecig_bi_*`)

### 1. `ecig_bi_business_units`
**Purpose:** Multi-tenant business unit registry

```sql
CREATE TABLE ecig_bi_business_units (
    id INT PRIMARY KEY AUTO_INCREMENT,
    unit_name VARCHAR(100) NOT NULL UNIQUE,
    unit_slug VARCHAR(100) NOT NULL UNIQUE,
    unit_type ENUM('parent', 'retail', 'wholesale', 'manufacturing', 'ecommerce', 'other') NOT NULL,
    parent_id INT NULL,
    description TEXT,
    primary_domain VARCHAR(255),
    api_key VARCHAR(100) UNIQUE,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES ecig_bi_business_units(id) ON DELETE SET NULL,
    INDEX idx_slug (unit_slug),
    INDEX idx_type (unit_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Initial Data:**
```sql
INSERT INTO ecig_bi_business_units (unit_name, unit_slug, unit_type, description) VALUES
('Ecigdis Limited', 'ecigdis', 'parent', 'Parent company'),
('The Vape Shed', 'vapeshed', 'retail', '17 retail stores across NZ'),
('The Vaping Kiwi', 'vapingkiwi', 'ecommerce', 'E-commerce retail site'),
('VapeHQ', 'vapehq', 'ecommerce', 'E-commerce retail site'),
('Ecigdis Wholesale', 'wholesale', 'wholesale', 'B2B wholesale operations'),
('Juice Manufacturing', 'manufacturing', 'manufacturing', 'E-liquid production facility');
```

### 2. `ecig_bi_domains`
**Purpose:** Business domain definitions

```sql
CREATE TABLE ecig_bi_domains (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    domain_name VARCHAR(100) NOT NULL,
    domain_slug VARCHAR(100) NOT NULL,
    description TEXT,
    parent_domain_id INT NULL,
    status ENUM('active', 'inactive', 'planned') DEFAULT 'active',
    component_count INT DEFAULT 0,
    last_activity DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_domain_id) REFERENCES ecig_bi_domains(id) ON DELETE SET NULL,
    UNIQUE KEY unique_domain (business_unit_id, domain_slug),
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. `ecig_bi_domain_components`
**Purpose:** Components within each domain

```sql
CREATE TABLE ecig_bi_domain_components (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    domain_id INT NOT NULL,
    component_type ENUM('service', 'api', 'cron', 'function', 'library', 'module', 'other') NOT NULL,
    component_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    description TEXT,
    status ENUM('active', 'deprecated', 'broken') DEFAULT 'active',
    health_score INT DEFAULT 100,
    last_checked DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (domain_id) REFERENCES ecig_bi_domains(id) ON DELETE CASCADE,
    INDEX idx_domain (domain_id),
    INDEX idx_type (component_type),
    INDEX idx_status (status),
    FULLTEXT idx_component_name (component_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4. `ecig_bi_metrics`
**Purpose:** Business and performance metrics

```sql
CREATE TABLE ecig_bi_metrics (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    domain_id INT NULL,
    metric_type ENUM('performance', 'business', 'system', 'custom') NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(15,4),
    metric_unit VARCHAR(50),
    metadata JSON,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    FOREIGN KEY (domain_id) REFERENCES ecig_bi_domains(id) ON DELETE SET NULL,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_domain (domain_id),
    INDEX idx_metric_name (metric_name),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_composite (business_unit_id, metric_name, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (YEAR(recorded_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

### 5. `ecig_bi_events`
**Purpose:** Business activity logging

```sql
CREATE TABLE ecig_bi_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    domain_id INT NULL,
    event_type VARCHAR(100) NOT NULL,
    event_category ENUM('sale', 'inventory', 'customer', 'system', 'error', 'other') NOT NULL,
    event_data JSON,
    severity ENUM('critical', 'high', 'medium', 'low', 'info') DEFAULT 'info',
    user_id VARCHAR(100),
    ip_address VARCHAR(45),
    occurred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    FOREIGN KEY (domain_id) REFERENCES ecig_bi_domains(id) ON DELETE SET NULL,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_event_type (event_type),
    INDEX idx_category (event_category),
    INDEX idx_severity (severity),
    INDEX idx_occurred_at (occurred_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (YEAR(occurred_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

### 6. `ecig_bi_alerts`
**Purpose:** Alert management and notifications

```sql
CREATE TABLE ecig_bi_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    alert_type ENUM('threshold', 'anomaly', 'system', 'security', 'other') NOT NULL,
    severity ENUM('critical', 'high', 'medium', 'low') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    metric_id BIGINT NULL,
    threshold_value DECIMAL(15,4),
    actual_value DECIMAL(15,4),
    status ENUM('new', 'acknowledged', 'resolved', 'ignored') DEFAULT 'new',
    notified_users JSON,
    triggered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at DATETIME NULL,
    resolved_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    FOREIGN KEY (metric_id) REFERENCES ecig_bi_metrics(id) ON DELETE SET NULL,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_severity (severity),
    INDEX idx_status (status),
    INDEX idx_triggered_at (triggered_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 7. `ecig_bi_dashboards`
**Purpose:** Custom dashboard configurations

```sql
CREATE TABLE ecig_bi_dashboards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    dashboard_name VARCHAR(255) NOT NULL,
    dashboard_slug VARCHAR(255) NOT NULL,
    description TEXT,
    layout JSON,               -- Widget positions and configs
    widgets JSON,              -- Widget definitions
    access_level ENUM('public', 'staff', 'admin', 'god') DEFAULT 'staff',
    is_default BOOLEAN DEFAULT FALSE,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dashboard (business_unit_id, dashboard_slug),
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_access_level (access_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 8. `ecig_bi_reports`
**Purpose:** Scheduled and generated reports

```sql
CREATE TABLE ecig_bi_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    report_name VARCHAR(255) NOT NULL,
    report_type ENUM('sales', 'inventory', 'performance', 'custom') NOT NULL,
    parameters JSON,
    schedule ENUM('once', 'daily', 'weekly', 'monthly', 'quarterly') DEFAULT 'once',
    last_generated DATETIME NULL,
    next_scheduled DATETIME NULL,
    recipients JSON,
    status ENUM('active', 'paused', 'completed') DEFAULT 'active',
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_schedule (schedule),
    INDEX idx_next_scheduled (next_scheduled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 9-15. Additional BI Tables

```sql
-- ecig_bi_kpis: Key Performance Indicator definitions
-- ecig_bi_targets: Performance targets and goals
-- ecig_bi_forecasts: Predictive forecasting data
-- ecig_bi_correlations: Discovered metric correlations
-- ecig_bi_insights: AI-generated business insights
-- ecig_bi_export_jobs: Data export job queue
-- ecig_bi_data_quality: Data quality monitoring
```

---

## 🤖 AI Agent Tables (`ecig_ai_*`)

### 1. `ecig_ai_agents`
**Purpose:** AI agent/bot registry

```sql
CREATE TABLE ecig_ai_agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_name VARCHAR(255) NOT NULL UNIQUE,
    agent_slug VARCHAR(255) NOT NULL UNIQUE,
    agent_type ENUM('customer_support', 'sales', 'staff_assistant', 'analytics', 'custom') NOT NULL,
    description TEXT,
    model_id INT NULL,
    system_prompt LONGTEXT,
    configuration JSON,
    capabilities JSON,
    status ENUM('active', 'training', 'inactive', 'maintenance') DEFAULT 'inactive',
    version VARCHAR(50),
    deployed_sites JSON,       -- Array of sites where deployed
    total_conversations INT DEFAULT 0,
    avg_satisfaction DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (agent_type),
    INDEX idx_status (status),
    FULLTEXT idx_name_desc (agent_name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. `ecig_ai_models`
**Purpose:** AI model configurations

```sql
CREATE TABLE ecig_ai_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(255) NOT NULL,
    model_provider ENUM('openai', 'anthropic', 'cohere', 'custom') NOT NULL,
    model_version VARCHAR(100),
    parameters JSON,
    cost_per_1k_tokens DECIMAL(10,6),
    max_tokens INT,
    temperature DECIMAL(3,2),
    status ENUM('active', 'deprecated', 'testing') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_provider (model_provider),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. `ecig_ai_conversations`
**Purpose:** AI conversation history

```sql
CREATE TABLE ecig_ai_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT NOT NULL,
    session_id VARCHAR(100) NOT NULL,
    business_unit_id INT NULL,
    user_id VARCHAR(100),
    user_type ENUM('customer', 'staff', 'admin', 'anonymous') DEFAULT 'anonymous',
    context JSON,              -- Conversation context
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME NULL,
    message_count INT DEFAULT 0,
    satisfaction_rating INT NULL,
    feedback TEXT,
    outcome ENUM('resolved', 'escalated', 'abandoned', 'ongoing') DEFAULT 'ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (agent_id) REFERENCES ecig_ai_agents(id) ON DELETE CASCADE,
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE SET NULL,
    INDEX idx_agent (agent_id),
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (YEAR(started_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

### 4. `ecig_ai_messages`
**Purpose:** Individual messages in conversations

```sql
CREATE TABLE ecig_ai_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content LONGTEXT NOT NULL,
    metadata JSON,
    token_count INT,
    processing_time_ms INT,
    model_used VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (conversation_id) REFERENCES ecig_ai_conversations(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5. `ecig_ai_training_data`
**Purpose:** Training data for model fine-tuning

```sql
CREATE TABLE ecig_ai_training_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    agent_id INT NULL,
    data_type ENUM('conversation', 'feedback', 'correction', 'example') NOT NULL,
    input_text LONGTEXT,
    expected_output LONGTEXT,
    actual_output LONGTEXT,
    quality_score INT,
    tags JSON,
    used_in_training BOOLEAN DEFAULT FALSE,
    approved_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (agent_id) REFERENCES ecig_ai_agents(id) ON DELETE SET NULL,
    INDEX idx_agent (agent_id),
    INDEX idx_data_type (data_type),
    INDEX idx_used (used_in_training)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6-12. Additional AI Tables

```sql
-- ecig_ai_deployments: Track where agents are deployed
-- ecig_ai_performance: Agent performance metrics
-- ecig_ai_intents: Recognized user intents
-- ecig_ai_entities: Extracted entities from conversations
-- ecig_ai_knowledge: Agent-specific knowledge base
-- ecig_ai_workflows: Multi-step AI workflows
-- ecig_ai_costs: API usage and cost tracking
```

---

## 💬 Live Chat Tables (`ecig_chat_*`)

### 1. `ecig_chat_sessions`
**Purpose:** Active and historical chat sessions

```sql
CREATE TABLE ecig_chat_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_token VARCHAR(100) NOT NULL UNIQUE,
    business_unit_id INT NOT NULL,
    site_identifier VARCHAR(100),
    visitor_id VARCHAR(100),
    visitor_name VARCHAR(255),
    visitor_email VARCHAR(255),
    visitor_ip VARCHAR(45),
    visitor_location JSON,
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    device_type ENUM('desktop', 'mobile', 'tablet', 'other') DEFAULT 'other',
    browser VARCHAR(100),
    status ENUM('waiting', 'active', 'ended', 'abandoned') DEFAULT 'waiting',
    assigned_agent_id INT NULL,
    ai_agent_id INT NULL,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    first_response_at DATETIME NULL,
    ended_at DATETIME NULL,
    wait_time_seconds INT,
    duration_seconds INT,
    message_count INT DEFAULT 0,
    satisfaction_rating INT NULL,
    tags JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    FOREIGN KEY (ai_agent_id) REFERENCES ecig_ai_agents(id) ON DELETE SET NULL,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at),
    INDEX idx_visitor (visitor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. `ecig_chat_messages`
**Purpose:** Chat messages

```sql
CREATE TABLE ecig_chat_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT NOT NULL,
    sender_type ENUM('visitor', 'agent', 'ai', 'system') NOT NULL,
    sender_id VARCHAR(100),
    sender_name VARCHAR(255),
    message_type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    message_content LONGTEXT,
    attachment_url VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    read_at DATETIME NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (session_id) REFERENCES ecig_chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_sent_at (sent_at),
    FULLTEXT idx_message (message_content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. `ecig_chat_agents`
**Purpose:** Human chat agents

```sql
CREATE TABLE ecig_chat_agents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agent_name VARCHAR(255) NOT NULL,
    agent_email VARCHAR(255) NOT NULL UNIQUE,
    business_unit_ids JSON,    -- Array of units they support
    avatar_url VARCHAR(500),
    status ENUM('online', 'away', 'busy', 'offline') DEFAULT 'offline',
    max_concurrent_chats INT DEFAULT 3,
    current_chat_count INT DEFAULT 0,
    total_chats_handled INT DEFAULT 0,
    avg_satisfaction DECIMAL(3,2),
    avg_response_time_seconds INT,
    last_active_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_email (agent_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4-8. Additional Chat Tables

```sql
-- ecig_chat_routing: Chat routing rules
-- ecig_chat_canned_responses: Pre-written responses
-- ecig_chat_analytics: Chat performance metrics
-- ecig_chat_visitors: Visitor profile tracking
-- ecig_chat_widget_config: Per-site widget configuration
```

---

## 🔌 API Gateway Tables (`ecig_api_*`)

### 1. `ecig_api_keys`
**Purpose:** API authentication

```sql
CREATE TABLE ecig_api_keys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    key_name VARCHAR(255) NOT NULL,
    api_key VARCHAR(100) NOT NULL UNIQUE,
    api_secret VARCHAR(255),
    permissions JSON,          -- Array of allowed endpoints
    rate_limit_per_hour INT DEFAULT 1000,
    rate_limit_per_day INT DEFAULT 10000,
    allowed_ips JSON,
    allowed_domains JSON,
    status ENUM('active', 'suspended', 'revoked') DEFAULT 'active',
    expires_at DATETIME NULL,
    last_used_at DATETIME NULL,
    created_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. `ecig_api_logs`
**Purpose:** API request logging

```sql
CREATE TABLE ecig_api_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    api_key_id INT NULL,
    request_method ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH') NOT NULL,
    endpoint VARCHAR(500) NOT NULL,
    request_headers JSON,
    request_body LONGTEXT,
    response_code INT,
    response_body LONGTEXT,
    response_time_ms INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (api_key_id) REFERENCES ecig_api_keys(id) ON DELETE SET NULL,
    INDEX idx_api_key (api_key_id),
    INDEX idx_endpoint (endpoint),
    INDEX idx_response_code (response_code),
    INDEX idx_requested_at (requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
PARTITION BY RANGE (YEAR(requested_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

### 3. `ecig_api_webhooks`
**Purpose:** Webhook subscriptions

```sql
CREATE TABLE ecig_api_webhooks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,
    webhook_name VARCHAR(255) NOT NULL,
    event_types JSON,          -- Array of events to listen for
    callback_url VARCHAR(500) NOT NULL,
    secret_key VARCHAR(255),
    retry_attempts INT DEFAULT 3,
    timeout_seconds INT DEFAULT 30,
    status ENUM('active', 'paused', 'failed') DEFAULT 'active',
    last_triggered_at DATETIME NULL,
    total_calls INT DEFAULT 0,
    failed_calls INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id) ON DELETE CASCADE,
    INDEX idx_business_unit (business_unit_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4-5. Additional API Tables

```sql
-- ecig_api_rate_limits: Dynamic rate limiting
-- ecig_api_endpoints: API endpoint catalog
```

---

## 🔗 Relationships & Foreign Keys

### Primary Relationships

```
ecig_bi_business_units (1) → (N) ecig_bi_domains
ecig_bi_domains (1) → (N) ecig_bi_domain_components
ecig_bi_business_units (1) → (N) ecig_bi_metrics
ecig_bi_business_units (1) → (N) ecig_bi_events

ecig_kb_file_memory (1) → (N) ecig_kb_functions
ecig_kb_file_memory (1) → (N) ecig_kb_classes
ecig_kb_file_memory (1) → (N) ecig_kb_relationships

ecig_ai_agents (1) → (N) ecig_ai_conversations
ecig_ai_conversations (1) → (N) ecig_ai_messages

ecig_chat_sessions (1) → (N) ecig_chat_messages
ecig_chat_agents (1) → (N) ecig_chat_sessions

ecig_api_keys (1) → (N) ecig_api_logs
ecig_bi_business_units (1) → (N) ecig_api_keys
```

---

## ⚡ Indexes & Performance

### Critical Indexes

```sql
-- Multi-column indexes for common queries
ALTER TABLE ecig_bi_metrics 
ADD INDEX idx_unit_metric_date (business_unit_id, metric_name, recorded_at);

ALTER TABLE ecig_bi_events
ADD INDEX idx_unit_type_date (business_unit_id, event_type, occurred_at);

ALTER TABLE ecig_ai_conversations
ADD INDEX idx_agent_user_date (agent_id, user_id, started_at);

ALTER TABLE ecig_chat_sessions
ADD INDEX idx_unit_status_date (business_unit_id, status, started_at);

-- Fulltext indexes for search
ALTER TABLE ecig_kb_file_memory 
ADD FULLTEXT INDEX ft_file_path (file_path);

ALTER TABLE ecig_kb_documentation
ADD FULLTEXT INDEX ft_title_content (title, content);
```

### Partitioning Strategy

Large tables partitioned by year:
- `ecig_bi_metrics` - By `recorded_at`
- `ecig_bi_events` - By `occurred_at`
- `ecig_ai_conversations` - By `started_at`
- `ecig_api_logs` - By `requested_at`

---

## 💾 Installation SQL

**Full installation script:** See `/database/install.sql`

**Quick install:**
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa < /public_html/docs/database/install.sql
```

---

**Last Updated:** October 21, 2025  
**Version:** 1.0.0  
**Total Tables:** 50
