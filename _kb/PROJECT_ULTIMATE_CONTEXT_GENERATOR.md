# 🚀 PROJECT: ULTIMATE CONTEXT GENERATOR + INTELLIGENT HUB
**Status:** Planning Phase
**Started:** October 30, 2025
**Owner:** Pearce Stephens
**Priority:** CRITICAL - Foundation for entire AI ecosystem

---

## 🎯 EXECUTIVE SUMMARY

Transform the GPT Hub (`gpt.ecigdis.co.nz`) from a collection of scripts into an **AI-powered intelligent command center** that:

1. **Automatically generates perfect documentation** for all projects (hub + satellites)
2. **Provides instant bot context** for new and mid-conversation AI assistance
3. **Controls all satellite systems** (CIS, retail sites, etc.) from one location
4. **Manages all AI agents** with centralized configuration and monitoring
5. **Stores knowledge intelligently** (files when bots need them, database for search)
6. **Follows industry standards** (not made-up approaches)
7. **Adapts to user preferences** (PDO vs MySQLi, PSR-12, etc.)
8. **Never wastes time** (only generates useful documentation)

---

## ✅ DEFINITIVE FEATURE CHECKLIST
**Use this to track implementation progress - tick off as completed**

### 🟢 **FOUNDATION FEATURES (Already Have from universal-copilot-automation.php)**

#### Auto-Discovery & Basic Syncing
- [x] **F001** - Auto-discover all `/home/master/applications/` folders
- [x] **F002** - Create `.github/copilot-instructions.md` in every project
- [x] **F003** - Create `.vscode/settings.json` with MCP configuration
- [x] **F004** - Sync instruction files across all systems
- [x] **F005** - Auto-configure MCP servers in VS Code settings
- [x] **F006** - Point to Intelligence Hub MCP server (gpt.ecigdis.co.nz)
- [x] **F007** - Basic README.md generation (file lists)
- [x] **F008** - Multi-bot configuration generation
- [x] **F009** - Satellite syncing (CIS, retail sites)
- [x] **F010** - Knowledge Base organization (calls external script)
- [x] **F011** - Directory structure analysis
- [x] **F012** - Project-specific context generation
- [x] **F013** - CLI command execution

### 🔴 **CRITICAL MISSING FEATURES (Build These Next)**

#### Deep Code Scanning & Analysis
- [ ] **F014** - Deep code pattern extraction (design patterns, anti-patterns)
- [ ] **F015** - Dependency mapping (what files use what)
- [ ] **F016** - Database schema analysis and documentation
- [ ] **F017** - Function/class relationship tracking
- [ ] **F018** - Call graph generation
- [ ] **F019** - Inheritance hierarchy mapping
- [ ] **F020** - Dead code detection
- [ ] **F021** - Security vulnerability scanning
- [ ] **F022** - Performance bottleneck identification
- [ ] **F023** - Code complexity analysis (cyclomatic complexity)

#### Database Integration (Connect to Existing Intelligence Tables)
- [ ] **F024** - Store scanned files in `intelligence_content` table
- [ ] **F025** - Store file text in `intelligence_content_text` with full-text search
- [ ] **F026** - Calculate and store `intelligence_score` for each file
- [ ] **F027** - Calculate and store `quality_score` for each file
- [ ] **F028** - Calculate and store `business_value_score` for each file
- [ ] **F029** - Calculate and store `complexity_score` for each file
- [ ] **F030** - Store extracted patterns in `neural_patterns` table
- [ ] **F031** - Store pattern relationships in `neural_pattern_relationships`
- [ ] **F032** - Track file metrics in `intelligence_metrics`
- [ ] **F033** - Store AI predictions in `ai_predictions`
- [ ] **F034** - Link files to business units in `business_units`
- [ ] **F035** - Use existing `organizations` table for multi-org support
- [ ] **F036** - Integration with `intelligence_files` table
- [ ] **F037** - Integration with `intelligence_automation` for scheduled scans

#### Comprehensive Documentation Generation
- [ ] **F038** - Generate 200-500 line comprehensive READMEs (not basic file lists)
- [ ] **F039** - Include "Quick Start" section with actual commands
- [ ] **F040** - Include "Architecture Overview" with diagrams
- [ ] **F041** - Include "API Documentation" with examples
- [ ] **F042** - Include "Database Schema" documentation
- [ ] **F043** - Include "Configuration Guide"
- [ ] **F044** - Include "Testing Guide"
- [ ] **F045** - Include "Deployment Guide"
- [ ] **F046** - Include "Troubleshooting" section
- [ ] **F047** - Include "Contributing Guidelines"

#### .copilot/ Directory Generation (Coding Law)
- [ ] **F048** - Create `.copilot/instructions.md` with user's coding standards
- [ ] **F049** - Create `.copilot/patterns/` directory
- [ ] **F050** - Extract and document actual code patterns from project
- [ ] **F051** - Include database query patterns (PDO/MySQLi based on preference)
- [ ] **F052** - Include security patterns (prepared statements, input validation)
- [ ] **F053** - Include error handling patterns
- [ ] **F054** - Include API response patterns
- [ ] **F055** - Include authentication/authorization patterns
- [ ] **F056** - Include testing patterns
- [ ] **F057** - Project-specific "law" (PSR-12, naming conventions, etc.)

#### AI-Powered Content Generation
- [ ] **F058** - Use AI to generate better README content (not just templates)
- [ ] **F059** - Natural language analysis of code purpose
- [ ] **F060** - AI-generated function documentation
- [ ] **F061** - AI-generated class documentation
- [ ] **F062** - AI-suggested improvements and optimizations
- [ ] **F063** - AI-detected code smells and anti-patterns
- [ ] **F064** - AI-generated user guides based on code functionality
- [ ] **F065** - AI-generated API documentation with examples
- [ ] **F066** - Context-aware code examples (from actual codebase)

#### Change Detection System
- [ ] **F067** - File hash comparison (MD5/SHA256) for change detection
- [ ] **F068** - Only update changed files (incremental updates)
- [ ] **F069** - Track last scan timestamp per file
- [ ] **F070** - Detect new files since last scan
- [ ] **F071** - Detect deleted files since last scan
- [ ] **F072** - Detect modified files since last scan
- [ ] **F073** - Smart re-generation (only regenerate affected docs)
- [ ] **F074** - Change notification system (alerts on major changes)
- [ ] **F075** - Version history tracking (see what changed when)

#### Standards Library (User Preferences System)
- [ ] **F076** - Create `code_standards` table for user preferences
- [ ] **F077** - Database preference: PDO vs MySQLi vs Query Builder
- [ ] **F078** - Coding style: PSR-12, PSR-4, WordPress, Laravel, etc.
- [ ] **F079** - Framework preference: Bootstrap 4/5, Tailwind, custom
- [ ] **F080** - JavaScript preference: jQuery, Vue, React, Vanilla
- [ ] **F081** - Testing preference: PHPUnit, Pest, Jest
- [ ] **F082** - Documentation style: JSDoc, PHPDoc, TypeDoc
- [ ] **F083** - Naming conventions: camelCase, snake_case, kebab-case
- [ ] **F084** - Error handling approach: Exceptions vs return codes
- [ ] **F085** - Logging preference: Monolog, custom, PSR-3
- [ ] **F086** - Security requirements: OWASP level, custom rules
- [ ] **F087** - Performance requirements: response time targets, query limits
- [ ] **F088** - Store preferences per project or globally
- [ ] **F089** - Standards library UI (web form to configure preferences)
- [ ] **F090** - Apply standards to all generated documentation

#### One-Button Web Dashboard
- [ ] **F091** - Web UI dashboard at `/dashboard/context-generator/`
- [ ] **F092** - Big "SCAN NOW" button
- [ ] **F093** - Real-time progress tracking (WebSocket or SSE)
- [ ] **F094** - Progress bar with status updates
- [ ] **F095** - Async job system (`scan_jobs` table)
- [ ] **F096** - Job status: queued, scanning, analyzing, generating, complete, failed
- [ ] **F097** - Cancel running scan button
- [ ] **F098** - View scan history
- [ ] **F099** - Project selector (scan specific project vs all)
- [ ] **F100** - Application manager (add/edit/remove satellites)
- [ ] **F101** - SSH configuration UI (hostname, username, key path)
- [ ] **F102** - API configuration UI (endpoint, API key)
- [ ] **F103** - Scan paths configuration (include/exclude patterns)
- [ ] **F104** - Schedule automatic scans (cron-like interface)
- [ ] **F105** - View generated documentation preview
- [ ] **F106** - Download generated docs as ZIP
- [ ] **F107** - Push docs to GitHub/GitLab button
- [ ] **F108** - System health dashboard

#### Standards Configuration Page
- [ ] **F109** - Dedicated page: `/dashboard/standards/`
- [ ] **F110** - Set global standards (apply to all projects)
- [ ] **F111** - Set project-specific standards (override global)
- [ ] **F112** - Standards templates (Laravel, WordPress, Custom, etc.)
- [ ] **F113** - Import standards from file
- [ ] **F114** - Export standards to file
- [ ] **F115** - Share standards between projects
- [ ] **F116** - Version standards (track changes over time)
- [ ] **F117** - Preview how standards affect generated docs

#### Multi-Domain Project Types
- [ ] **F118** - Support "Development Project" type (code repos)
- [ ] **F119** - Support "Business Project" type (store openings, ops)
- [ ] **F120** - Support "Retail Project" type (new websites, integrations)
- [ ] **F121** - Support "Module Project" type (software features)
- [ ] **F122** - Custom project types (user-defined)
- [ ] **F123** - Type-specific documentation templates
- [ ] **F124** - Type-specific metadata storage (JSON fields)
- [ ] **F125** - Type-specific bot context generation

#### MCP Integration Enhancement
- [ ] **F126** - Auto-configure `.vscode/mcp.json` in every project
- [ ] **F127** - Generate project-specific MCP tools
- [ ] **F128** - Index all project files for MCP semantic search
- [ ] **F129** - Create vector embeddings for project documentation
- [ ] **F130** - Enable MCP to search project-specific knowledge
- [ ] **F131** - MCP health check integration
- [ ] **F132** - MCP performance monitoring

#### Satellite Integration
- [ ] **F133** - SSH scanning for remote satellites
- [ ] **F134** - API-based scanning for API-only satellites
- [ ] **F135** - Manual upload scanning (for restricted access)
- [ ] **F136** - Satellite status monitoring
- [ ] **F137** - Satellite sync scheduling
- [ ] **F138** - Satellite configuration backup
- [ ] **F139** - Satellite deployment automation

#### Version Control Integration
- [ ] **F140** - Git integration (detect repo, branch, commits)
- [ ] **F141** - Auto-commit generated documentation
- [ ] **F142** - Create pull requests for doc updates
- [ ] **F143** - GitHub Actions integration
- [ ] **F144** - GitLab CI integration
- [ ] **F145** - Bitbucket Pipelines integration
- [ ] **F146** - Track doc changes in Git history

#### Reporting & Analytics
- [ ] **F147** - Documentation coverage report
- [ ] **F148** - Quality score report (per project)
- [ ] **F149** - Code pattern report (discovered patterns)
- [ ] **F150** - Security issues report
- [ ] **F151** - Performance issues report
- [ ] **F152** - Dependency graph visualization
- [ ] **F153** - Documentation freshness report
- [ ] **F154** - Bot usage analytics (which projects accessed most)
- [ ] **F155** - Export reports as PDF/CSV

#### Bot Context Features
- [ ] **F156** - "Emergency context injection" for mid-conversation
- [ ] **F157** - "New conversation starter pack" (instant context)
- [ ] **F158** - Context relevance scoring (most relevant files first)
- [ ] **F159** - Context caching for fast retrieval
- [ ] **F160** - Context expiry and refresh
- [ ] **F161** - Multi-bot context (different contexts for different bot types)
- [ ] **F162** - Context search (find relevant context by keyword)

#### Advanced Features
- [ ] **F163** - API endpoint documentation generator
- [ ] **F164** - Database schema diagram generator (Mermaid/PlantUML)
- [ ] **F165** - Class diagram generator
- [ ] **F166** - Sequence diagram generator
- [ ] **F167** - Architecture diagram generator
- [ ] **F168** - Dependency graph generator
- [ ] **F169** - Code coverage visualization
- [ ] **F170** - Technical debt tracking
- [ ] **F171** - License compliance checking
- [ ] **F172** - Third-party dependency auditing
- [ ] **F173** - Environment configuration documentation
- [ ] **F174** - Deployment pipeline documentation
- [ ] **F175** - Rollback procedure documentation

#### Intelligence Integration
- [ ] **F176** - Connect to existing `ai_models` table
- [ ] **F177** - Use AI models for code analysis
- [ ] **F178** - Store predictions in `ai_predictions`
- [ ] **F179** - Use `neural_patterns` for pattern matching
- [ ] **F180** - Integrate with `intelligence_alerts` for notifications
- [ ] **F181** - Use `intelligence_automation` for scheduled tasks
- [ ] **F182** - Track execution in `intelligence_automation_executions`
- [ ] **F183** - Store metrics in `intelligence_metrics`
- [ ] **F184** - Use `redis_cache_config` for caching strategy
- [ ] **F185** - Monitor with `system_health` table

### 🟡 **ENHANCEMENT FEATURES (Improve Existing)**

#### README Enhancement
- [ ] **F186** - Upgrade from basic file lists to comprehensive documentation
- [ ] **F187** - Add table of contents with anchor links
- [ ] **F188** - Add badges (build status, coverage, version)
- [ ] **F189** - Add screenshots/diagrams
- [ ] **F190** - Add code examples from actual codebase
- [ ] **F191** - Add FAQ section based on common patterns
- [ ] **F192** - Add changelog integration
- [ ] **F193** - Add contributor list from Git history

#### VS Code Settings Enhancement
- [ ] **F194** - Add debug configurations
- [ ] **F195** - Add task definitions
- [ ] **F196** - Add workspace recommendations (extensions)
- [ ] **F197** - Add snippets for common patterns
- [ ] **F198** - Add launch configurations for testing

#### GitHub Instructions Enhancement
- [ ] **F199** - More detailed project-specific context
- [ ] **F200** - Include actual code examples
- [ ] **F201** - Include common tasks and their solutions
- [ ] **F202** - Include project-specific gotchas
- [ ] **F203** - Include architectural decisions (ADRs)

#### Multi-Bot Config Enhancement
- [ ] **F204** - Add bot-specific knowledge bases
- [ ] **F205** - Add bot capability definitions
- [ ] **F206** - Add bot access control (which bots can access what)
- [ ] **F207** - Add bot performance tracking
- [ ] **F208** - Add bot recommendation system

#### Satellite Sync Enhancement
- [ ] **F209** - Bi-directional sync (satellites can push updates)
- [ ] **F210** - Conflict resolution (hub vs satellite changes)
- [ ] **F211** - Selective sync (only sync specific files)
- [ ] **F212** - Bandwidth optimization (compress transfers)
- [ ] **F213** - Retry logic with exponential backoff

---

## 📊 FEATURE COMPLETION METRICS

**Total Features:** 213
**Foundation (Already Have):** 13 (6%)
**Critical Missing:** 172 (81%)
**Enhancements:** 28 (13%)

**Implementation Priority:**

1. **Phase 1 (Weeks 1-2):** F024-F037 (Database Integration) - 14 features
2. **Phase 2 (Weeks 2-3):** F014-F023 (Deep Scanning) - 10 features
3. **Phase 3 (Weeks 3-4):** F067-F075 (Change Detection) - 9 features
4. **Phase 4 (Weeks 4-5):** F076-F090 (Standards Library) - 15 features
5. **Phase 5 (Weeks 5-6):** F038-F057 (Documentation) - 20 features
6. **Phase 6 (Weeks 6-7):** F091-F108 (Dashboard) - 18 features
7. **Phase 7 (Weeks 7-8):** F058-F066 (AI Content) - 9 features
8. **Phase 8 (Weeks 8+):** Remaining features as needed

**Success Criteria:**
- ✅ All F001-F090 complete = MVP Ready
- ✅ All F001-F132 complete = Production Ready
- ✅ All F001-F213 complete = Enterprise Ready

---

## 📋 TWO MAJOR COMPONENTS

### **COMPONENT 1: Ultimate Context Generator**
**Location:** `/dashboard/context-generator/`
**Purpose:** Auto-documentation + bot context system

**Features:**
- Database-driven knowledge storage
- Automatic README.md generation (detailed, not lazy)
- Automatic .copilot/instructions.md generation (coding standards)
- Automatic .copilot/patterns/ generation (code examples from YOUR code)
- Bot context generation (new conversation + emergency injection)
- Multi-application support (hub + ALL satellites)
- Change detection (auto-regenerate when code changes)
- Configurable standards (PDO/MySQLi, PSR-12, Bootstrap version, etc.)
- Control dashboard (manage everything visually)

### **COMPONENT 2: Intelligent Hub Restructure**
**Location:** Entire `/public_html/` reorganization
**Purpose:** Transform into version-controlled, registry-based, AI-powered command center

**Features:**
- Proper directory structure (production/, development/, experimental/, library/)
- Project registry system (single source of truth)
- Company-wide registry (hub + satellites)
- Dependency mapping (what uses what)
- Version control integration (Git)
- Satellite management (CIS, retail, etc.)
- AI agent central control (all agents managed here)
- Data centralization (all knowledge flows through hub)
- Tool builder (auto-generate tools for everything)

---

## 🏗️ COMPONENT 1 DETAILED SPEC: CONTEXT GENERATOR

### **1.1 Database Schema**

#### **Core Tables:**

```sql
-- Applications (hub + satellites)
CREATE TABLE applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('hub', 'satellite') NOT NULL,
    base_path VARCHAR(500),
    url VARCHAR(255),
    status ENUM('active', 'development', 'archived') DEFAULT 'active',
    description TEXT,
    tech_stack JSON,
    access_method ENUM('local', 'ssh', 'api', 'manual') DEFAULT 'local',
    ssh_host VARCHAR(255),
    ssh_user VARCHAR(100),
    ssh_key_path VARCHAR(500),
    api_endpoint VARCHAR(255),
    api_key VARCHAR(255),
    last_scanned TIMESTAMP NULL,
    scan_frequency_hours INT DEFAULT 4,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Projects within applications
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    type VARCHAR(100),
    path VARCHAR(500) NOT NULL,
    entry_point VARCHAR(255),
    status ENUM('concept', 'design', 'development', 'testing', 'staging', 'production', 'archived') DEFAULT 'development',
    description TEXT,
    tech_stack JSON,
    repository_url VARCHAR(255),
    documentation_url VARCHAR(255),
    last_scanned TIMESTAMP NULL,
    health_status ENUM('excellent', 'good', 'warning', 'critical') DEFAULT 'good',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_slug (application_id, slug),
    INDEX idx_status (status),
    INDEX idx_health (health_status)
);

-- Code standards (user preferences)
CREATE TABLE code_standards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NULL,
    scope ENUM('global', 'application', 'project') DEFAULT 'global',
    category VARCHAR(100) NOT NULL,
    standard_key VARCHAR(100) NOT NULL,
    standard_value TEXT NOT NULL,
    examples TEXT,
    enforcement_level ENUM('required', 'recommended', 'optional') DEFAULT 'required',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_scope (scope),
    INDEX idx_category (category)
);

-- Project files inventory
CREATE TABLE project_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size BIGINT,
    is_critical BOOLEAN DEFAULT FALSE,
    purpose TEXT,
    dependencies TEXT,
    last_modified TIMESTAMP NULL,
    file_hash VARCHAR(64),
    lines_of_code INT,
    complexity_score INT,
    has_documentation BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_file_per_project (project_id, file_path),
    INDEX idx_file_type (file_type),
    INDEX idx_critical (is_critical),
    INDEX idx_hash (file_hash)
);

-- Code patterns (extracted from actual code)
CREATE TABLE code_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    pattern_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    example_code TEXT,
    explanation TEXT,
    do_examples TEXT,
    dont_examples TEXT,
    frequency INT DEFAULT 1,
    quality_score INT,
    extracted_from VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_category (category)
);

-- Project relationships
CREATE TABLE project_relationships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source_project_id INT NOT NULL,
    target_project_id INT NOT NULL,
    relationship_type ENUM('uses', 'depends_on', 'integrates', 'calls', 'extends', 'implements') NOT NULL,
    details TEXT,
    is_critical BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (target_project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_relationship (source_project_id, target_project_id, relationship_type)
);

-- Bot context cache
CREATE TABLE bot_context_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    context_type ENUM('full', 'quick', 'emergency', 'focused') NOT NULL,
    topic VARCHAR(255),
    context_content LONGTEXT NOT NULL,
    context_hash VARCHAR(64),
    embeddings BLOB,
    usage_count INT DEFAULT 0,
    last_used TIMESTAMP NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_type (context_type),
    INDEX idx_expires (expires_at)
);

-- Known issues
CREATE TABLE known_issues (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    issue_title VARCHAR(255) NOT NULL,
    issue_description TEXT,
    root_cause TEXT,
    solution TEXT,
    status ENUM('open', 'investigating', 'fixed', 'wontfix', 'monitoring') DEFAULT 'open',
    severity ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    fixed_date DATE NULL,
    related_files JSON,
    related_commits JSON,
    impact_analysis TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_severity (severity)
);

-- Generation history
CREATE TABLE generation_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    file_generated VARCHAR(500) NOT NULL,
    generation_type ENUM('auto', 'manual', 'triggered', 'emergency') NOT NULL,
    trigger_reason TEXT,
    content_hash VARCHAR(64),
    generation_time_ms INT,
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_file (file_generated),
    INDEX idx_generated_at (generated_at)
);

-- Documentation templates
CREATE TABLE documentation_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('readme', 'copilot_instructions', 'copilot_context', 'copilot_patterns', 'api_docs') NOT NULL,
    template_content LONGTEXT NOT NULL,
    variables JSON,
    conditions JSON,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type)
);

-- Scan jobs (for tracking async scans)
CREATE TABLE scan_jobs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    application_id INT,
    project_id INT,
    scan_type ENUM('full', 'quick', 'files_only', 'patterns_only') NOT NULL,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    progress_percent INT DEFAULT 0,
    files_scanned INT DEFAULT 0,
    files_total INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_status (status)
);
```

---

### **1.2 Core Features**

#### **Feature: Automatic README Generation**

**What it does:**
- Scans project directory
- Detects architecture (MVC, API, library, etc.)
- Finds critical files (session-config.php, authentication, etc.)
- Extracts dependencies (what includes what)
- Generates comprehensive README.md (200-500 lines)

**README Structure:**
```markdown
# [Project Name]

## Table of Contents
## Overview
## Quick Start
## Architecture
## Directory Structure
## [Critical Topics - Auto-Detected]
  - Session Management (if found)
  - Authentication Flow (if found)
  - Database Connection (if found)
  - API Endpoints (if found)
## Configuration
## Troubleshooting
## Known Issues (from database)
## Changelog (from database)
## Related Documentation
```

**Generation Triggers:**
- Manual: User clicks "Generate"
- Auto: File changes detected in project
- Scheduled: Every 4 hours if changes exist
- Emergency: Bot requests context

---

#### **Feature: .copilot/ Instructions Generation**

**What it does:**
- Loads user's code standards from database (PDO, PSR-12, etc.)
- Analyzes actual code to find patterns
- Extracts examples from existing code
- Generates instructions.md with YOUR rules

**.copilot/instructions.md Structure:**
```markdown
# Code Standards for [Project Name]

## PHP Standards
- Version: [Detected]
- Style: [User preference - PSR-12, etc.]
- Type Declarations: [User preference]
- Database: [User preference - PDO/MySQLi]

## Security Requirements
[Based on user's security standards]

## File Organization
[Detected from actual structure]

## Naming Conventions
[Extracted from existing code]

## Error Handling
[User's preferred approach]

## Testing Requirements
[User's testing standards]

## Examples
[Actual code from this project]
```

---

#### **Feature: Code Pattern Extraction**

**What it does:**
- Scans project files for common patterns
- Identifies how YOU write code
- Extracts actual examples (not generic ones)
- Stores in database + .copilot/patterns/

**Pattern Categories:**
- Database queries (PDO prepared statements)
- API responses (JSON envelopes)
- Error handling (try-catch, logging)
- Authentication (session management)
- Form handling (CSRF, validation)
- File operations (uploads, reading)

**Output:**
```php
// .copilot/patterns/database-query.example.php
<?php
// ✅ CORRECT: PDO Prepared Statement (Your Standard)
// Extracted from: dashboard/controllers/UserController.php

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    throw new UserNotFoundException("User not found");
}

// ❌ WRONG: Never use raw SQL concatenation
$query = "SELECT * FROM users WHERE email = '$email'"; // SQL injection!
```

---

#### **Feature: Bot Context Generation**

**What it does:**
- Generates briefing files for AI bots
- Pre-caches context in database
- Provides instant context on demand
- Supports emergency injection mid-conversation

**Context Types:**

1. **Full Context (New Conversation)**
   - Complete project overview
   - All critical knowledge
   - User's coding standards
   - Known issues and solutions
   - Quick reference links
   - Generated once, cached for 24 hours

2. **Quick Context (Folder Navigation)**
   - Just this folder's purpose
   - File structure
   - Entry points
   - 5-20 lines max

3. **Emergency Context (Mid-Conversation)**
   - Focused on specific topic
   - Session management
   - Authentication flow
   - Database patterns
   - Generated on-demand, cached for 1 hour

4. **Focused Context (Specific Feature)**
   - Deep dive into one area
   - All related files
   - Dependencies
   - Examples
   - Generated on-demand

**Output Location:**
- `/bot-workspace/context_[project]_[type]_[timestamp].md`
- Auto-deleted after 24 hours
- Also cached in database with embeddings

---

#### **Feature: Multi-Application Support**

**Satellite Management:**

**Access Methods:**

1. **Local Access** (files on same server)
   - Direct file system scan
   - Fastest method
   - Used for hub projects

2. **SSH Access** (remote servers)
   - SSH connection to satellite
   - Run scanner remotely
   - Transfer results via SFTP
   - Used for: CIS (staff.vapeshed.co.nz)

3. **API Access** (satellite reports to hub)
   - Satellite runs scanner locally
   - Sends results to hub via API
   - Hub stores in database
   - Used for: Retail sites, external systems

4. **Manual** (user uploads scan results)
   - User runs scanner on satellite
   - Uploads JSON results
   - Hub imports data

**Satellite Configuration:**
```json
{
  "application_id": 2,
  "name": "CIS - Staff Portal",
  "type": "satellite",
  "url": "https://staff.vapeshed.co.nz",
  "access_method": "ssh",
  "ssh_config": {
    "host": "staff.vapeshed.co.nz",
    "user": "deploy",
    "key_path": "/secure/keys/cis_deploy_key",
    "base_path": "/home/master/applications/jcepnzzkmj/public_html"
  },
  "scan_frequency_hours": 6,
  "auto_generate_docs": true,
  "sync_to_hub": true
}
```

---

#### **Feature: Change Detection**

**How it works:**
- File hash stored for every scanned file
- Cron job runs every hour (configurable)
- Compares current hash to stored hash
- Triggers regeneration if changed

**Detection Levels:**

1. **Critical File Changed**
   - Example: session-config.php
   - Action: Immediate regeneration
   - Notification: Email/Slack alert

2. **Normal File Changed**
   - Example: UserController.php
   - Action: Mark for regeneration
   - Schedule: Next scan cycle (4 hours)

3. **Documentation File Changed**
   - Example: README.md manually edited
   - Action: Update database record
   - Respect: Don't overwrite manual changes

**Override Protection:**
- If README.md has manual edits, don't overwrite
- Add comment at top: "<!-- AUTO-GENERATED - Last updated: [date] -->"
- If comment missing, assume manual = don't touch

---

### **1.3 Control Dashboard UI**

**Location:** `/dashboard/context-generator/`

**Pages:**

#### **1.3.1 Overview Dashboard**
```
CONTEXT GENERATOR - OVERVIEW

┌─────────────────────────────────────────────────────────┐
│ System Health                                           │
│ ✅ Database: Connected                                  │
│ ✅ Scanner: Running                                     │
│ ✅ Generation Engine: Idle                              │
│ ⚠️  Change Detector: 3 files need update               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Quick Stats                                             │
│                                                         │
│ Applications:    4 (1 hub, 3 satellites)               │
│ Projects:        12 total                              │
│ Documentation:   87% complete (10/12)                  │
│ Files Tracked:   2,847                                 │
│ Last Full Scan:  2 hours ago                           │
│ Next Auto Scan:  In 2 hours                            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Recent Activity                                         │
│                                                         │
│ 14:23  ✅ Generated README for Dashboard               │
│ 14:15  ⚠️  Change detected: session-config.php         │
│ 14:00  ✅ Completed full scan of CIS satellite         │
│ 13:45  ✅ Generated bot context for MCP Server         │
│ 13:30  ✅ Extracted 12 new code patterns               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Documentation Health                                    │
│                                                         │
│ ✅ Admin Dashboard      Last updated: Today            │
│ ✅ MCP Server          Last updated: Today            │
│ ⚠️  AI Agent System     Code changed 3 days ago       │
│ ⚠️  API Gateway         New files added               │
│ ✅ Authentication       Last updated: Yesterday        │
│                                                         │
│ [Update All Outdated]  [Force Regenerate All]         │
└─────────────────────────────────────────────────────────┘
```

---

#### **1.3.2 Configure Standards**
```
YOUR CODE STANDARDS

Global standards apply to all projects unless overridden.

┌─────────────────────────────────────────────────────────┐
│ PHP Standards                                           │
│                                                         │
│ PHP Version:              [8.1+ ▼]                     │
│ Code Style:               [PSR-12 ▼]                   │
│ Strict Types:             [✓] Required                 │
│ Type Declarations:        [✓] Required                 │
│ PHPDoc Comments:          [✓] Required for public      │
│                                                         │
│ Error Handling:           [Exceptions ▼]               │
│ Error Logging:            [✓] Always log errors        │
│ Display Errors:           [ ] Never in production      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Database Standards                                      │
│                                                         │
│ Driver:                   [PDO ●] MySQLi ○             │
│ Prepared Statements:      [✓] Required (no raw SQL)    │
│ Connection Pooling:       [✓] Enabled                  │
│ Query Logging:            [✓] Log slow queries (>300ms)│
│                                                         │
│ Transaction Handling:     [Explicit ▼]                 │
│ Error Mode:               [Exceptions ▼]               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Security Standards                                      │
│                                                         │
│ Input Validation:         [✓] Required on all input    │
│ Output Escaping:          [✓] Required (htmlspecialchars)│
│ CSRF Protection:          [✓] Required on all forms    │
│ SQL Injection:            [✓] Use prepared statements  │
│ XSS Prevention:           [✓] Escape all output        │
│                                                         │
│ Password Hashing:         [password_hash() ▼]          │
│ Session Security:         [✓] HTTPOnly, Secure, SameSite│
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ UI Framework                                            │
│                                                         │
│ CSS Framework:            [Bootstrap 4.2 ▼]            │
│ JavaScript:               [Vanilla ES6+ ▼]             │
│ Icons:                    [Font Awesome ▼]             │
│                                                         │
│ ⚠️  Note: Some projects may override (e.g. Dashboard)  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ File Organization                                       │
│                                                         │
│ Controllers:              [/controllers/ ▼]            │
│ Models:                   [/models/ ▼]                 │
│ Views:                    [/views/ ▼]                  │
│ Config:                   [/config/ ▼]                 │
│                                                         │
│ Naming Convention:        [PascalCase for classes ▼]   │
│                           [camelCase for methods ▼]    │
│                           [snake_case for DB tables ▼] │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ API Standards                                           │
│                                                         │
│ Response Format:          [JSON Envelope ▼]            │
│ Status Codes:             [✓] Use proper HTTP codes    │
│ Error Format:             [Structured ▼]               │
│                                                         │
│ JSON Envelope Example:                                 │
│ {                                                       │
│   "success": true,                                     │
│   "data": {...},                                       │
│   "message": "Operation completed",                    │
│   "timestamp": "2025-10-30 14:23:00"                  │
│ }                                                       │
└─────────────────────────────────────────────────────────┘

[Save All Standards]  [Reset to Defaults]  [Export Config]
```

---

#### **1.3.3 Manage Applications**
```
APPLICATIONS & SATELLITES

┌─────────────────────────────────────────────────────────┐
│ Hub Application                                         │
│                                                         │
│ 🏠 GPT Hub (gpt.ecigdis.co.nz)                        │
│    Type: Hub                                           │
│    Status: Active ✅                                    │
│    Projects: 8                                         │
│    Last Scan: 2 hours ago                              │
│    Health: Excellent ✅                                 │
│                                                         │
│    [View Projects]  [Scan Now]  [Settings]            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Satellite Applications                                  │
│                                                         │
│ 📡 CIS - Staff Portal (staff.vapeshed.co.nz)          │
│    Type: Satellite (SSH)                               │
│    Status: Active ✅                                    │
│    Projects: 12                                        │
│    Last Scan: 4 hours ago                              │
│    Health: Good ✅                                      │
│    Next Scan: In 2 hours                               │
│                                                         │
│    [View Projects]  [Scan Now]  [SSH Config]          │
│                                                         │
│ 📡 Retail Site (vapeshed.co.nz)                       │
│    Type: Satellite (API)                               │
│    Status: Active ✅                                    │
│    Projects: 3                                         │
│    Last Scan: 6 hours ago                              │
│    Health: Good ✅                                      │
│    Next Scan: In 6 hours                               │
│                                                         │
│    [View Projects]  [Trigger API Scan]  [API Config]  │
│                                                         │
│ 📡 Vaping Kiwi (vapingkiwi.co.nz)                     │
│    Type: Satellite (Manual)                            │
│    Status: Active ✅                                    │
│    Projects: 2                                         │
│    Last Scan: 2 days ago ⚠️                            │
│    Health: Unknown ⚠️                                   │
│                                                         │
│    [Upload Scan Results]  [Manual Config]             │
└─────────────────────────────────────────────────────────┘

[Add New Application]  [Bulk Operations]
```

---

#### **1.3.4 Bot Context Tools**
```
BOT CONTEXT MANAGEMENT

┌─────────────────────────────────────────────────────────┐
│ Generate New Conversation Context                       │
│                                                         │
│ Project:  [Select Project ▼]                           │
│           - Admin Dashboard                            │
│           - MCP Server                                 │
│           - AI Agent System                            │
│           - API Gateway                                │
│           - Authentication System                      │
│                                                         │
│ Context Type:                                          │
│   ● Full (complete project overview)                   │
│   ○ Quick (just essentials)                            │
│   ○ Focused (specific feature)                         │
│                                                         │
│ [Generate Context File]                                │
│                                                         │
│ Output: /bot-workspace/context_dashboard_full_...md   │
│ Cache: Valid for 24 hours                              │
│ Embeddings: Generated for semantic search             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Emergency Context Injection                             │
│                                                         │
│ When a bot gets confused mid-conversation, inject      │
│ focused context on a specific topic.                   │
│                                                         │
│ Project:  [Select Project ▼]                           │
│ Topic:    [Select Topic ▼]                             │
│           - Session Management                         │
│           - Authentication Flow                        │
│           - Database Patterns                          │
│           - API Responses                              │
│           - Error Handling                             │
│           - File Organization                          │
│           - Custom Topic... [Type here]                │
│                                                         │
│ [Generate Emergency Briefing]                          │
│                                                         │
│ Output: /bot-workspace/context_emergency_...md        │
│ Cache: Valid for 1 hour                                │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ View Cached Context                                     │
│                                                         │
│ Project: [Dashboard ▼]                                 │
│                                                         │
│ Cached Contexts:                                       │
│ ✅ Full Context         Generated: 2 hours ago         │
│    Used: 3 times        Expires: In 22 hours          │
│    [View] [Regenerate] [Clear]                        │
│                                                         │
│ ✅ Quick Context        Generated: Today               │
│    Used: 8 times        Expires: In 18 hours          │
│    [View] [Regenerate] [Clear]                        │
│                                                         │
│ ✅ Emergency: Sessions  Generated: 1 hour ago          │
│    Used: 1 time         Expires: In 1 hour            │
│    [View] [Regenerate] [Clear]                        │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Context Usage Statistics                                │
│                                                         │
│ Total Contexts Generated: 247                          │
│ Most Used: Dashboard (43 times)                        │
│ Cache Hit Rate: 87%                                    │
│ Avg Generation Time: 1.2 seconds                       │
│                                                         │
│ [View Detailed Stats]                                  │
└─────────────────────────────────────────────────────────┘
```

---

### **1.4 CLI Tools**

For automation and cron jobs:

```bash
# Scan single project
php context-generator scan --project=dashboard

# Scan entire application
php context-generator scan --application=hub

# Scan satellite via SSH
php context-generator scan --application=cis --method=ssh

# Generate documentation for project
php context-generator generate --project=dashboard --type=readme

# Generate bot context
php context-generator bot-context --project=dashboard --type=full

# Emergency context injection
php context-generator bot-context --project=dashboard --topic=sessions --emergency

# Check for changes
php context-generator check-changes --all

# Regenerate outdated docs
php context-generator regenerate --outdated-only

# Full system scan
php context-generator scan --all --full

# Health check
php context-generator health

# Export configuration
php context-generator export --output=config.json

# Import configuration
php context-generator import --input=config.json
```

---

## 🏗️ COMPONENT 2 DETAILED SPEC: INTELLIGENT HUB RESTRUCTURE

### **2.1 New Directory Structure**

**Current State:** Messy, no organization, files everywhere

**Target State:**

```
/home/master/applications/hdgwrzntwa/public_html/
│
├── .github/                      # GitHub integration (if using)
├── .vscode/                      # VS Code settings
│   ├── settings.json
│   ├── extensions.json
│   └── mcp.json                 # MCP server connections
│
├── .copilot/                     # Global AI assistant rules
│   ├── instructions.md          # THE LAW for entire hub
│   ├── context.md               # Hub history and decisions
│   └── patterns/                # Global code patterns
│
├── README.md                     # Hub overview
├── composer.json                 # PHP dependencies (if used)
├── .env                          # Secrets (gitignored)
├── .gitignore                    # What not to commit
├── app.php                       # Bootstrap file (if needed)
│
├── production/                   # ✅ LIVE CODE ONLY
│   ├── README.md
│   ├── mcp/                     # MCP server (production)
│   ├── api/                     # Public APIs
│   ├── webhooks/                # External webhooks
│   └── [other production features]
│
├── development/                  # 🔧 ACTIVE DEVELOPMENT
│   ├── README.md
│   ├── context-generator/       # THIS PROJECT (while building)
│   ├── new-feature-x/
│   └── experimental-tool/
│
├── staging/                      # 🎭 PRE-PRODUCTION TESTING
│   ├── README.md
│   └── [features ready for testing]
│
├── experimental/                 # 🧪 PROOF OF CONCEPTS
│   ├── README.md
│   └── [wild ideas being tested]
│
├── library/                      # 📚 REUSABLE COMPONENTS
│   ├── README.md
│   ├── authentication/
│   ├── database/
│   ├── session-management/
│   └── utilities/
│
├── dashboard/                    # 📊 ADMIN INTERFACE
│   ├── README.md
│   ├── .copilot/                # Dashboard-specific rules
│   ├── index.php
│   ├── login.php
│   ├── config/
│   ├── controllers/
│   ├── views/
│   ├── assets/
│   └── context-generator/       # Context Generator UI (after moved to production)
│
├── ai-agent/                     # 🤖 AI AGENT SYSTEM
│   ├── README.md
│   ├── .copilot/
│   └── [agent files]
│
├── knowledge/                    # 📖 CENTRALIZED KNOWLEDGE
│   ├── README.md
│   ├── completed-projects/      # Project completion docs
│   ├── architecture/            # System design docs
│   ├── decisions/               # Architecture Decision Records
│   ├── troubleshooting/         # Problem-solution guides
│   └── lessons-learned/         # What we learned
│
├── satellites/                   # 🛰️ SATELLITE SYSTEM DATA
│   ├── README.md
│   ├── cis/                     # CIS system info (not scanned)
│   │   ├── scan-results.json
│   │   ├── projects.json
│   │   └── last-sync.txt
│   ├── retail/
│   └── vaping-kiwi/
│
├── bot-workspace/                # 🤖 BOT TEMPORARY FILES
│   ├── README.md
│   └── [auto-generated context files]
│   └── [auto-cleaned every 24 hours]
│
├── _dev-tools/                   # 🔧 DEVELOPMENT UTILITIES
│   ├── README.md
│   ├── tests/
│   ├── scripts/
│   ├── diagnostics/
│   └── generators/
│
├── _automation/                  # ⚙️ CRON JOBS & AUTOMATION
│   ├── README.md
│   ├── cron-jobs/
│   ├── scheduled-tasks/
│   └── maintenance/
│
├── _archived/                    # 📦 OLD/DEPRECATED CODE
│   ├── README.md
│   ├── old-projects/
│   ├── deprecated-features/
│   └── historical-docs/
│
├── conf/                         # Server configuration (unchanged)
├── logs/                         # Server logs (unchanged)
├── private_html/                 # Private files (unchanged)
├── ssl/                          # SSL certificates (unchanged)
└── tmp/                          # Temporary files (unchanged)
```

---

### **2.2 Registry System**

#### **PROJECT_REGISTRY.json** (Hub Projects)

**Location:** `/public_html/PROJECT_REGISTRY.json`

**Structure:**
```json
{
  "registry_version": "1.0.0",
  "hub_name": "GPT Hub",
  "hub_url": "https://gpt.ecigdis.co.nz",
  "last_updated": "2025-10-30T14:23:00Z",

  "projects": [
    {
      "id": "dashboard",
      "name": "Admin Dashboard",
      "status": "production",
      "type": "web_application",
      "path": "/dashboard/",
      "entry_point": "index.php",
      "repository": "git@github.com:company/dashboard.git",
      "version": "2.1.0",
      "tech_stack": ["PHP 8.1", "MySQL", "Bootstrap 4.2"],
      "dependencies": [
        "library/session-management",
        "library/authentication"
      ],
      "documentation": {
        "readme": "/dashboard/README.md",
        "copilot": "/dashboard/.copilot/",
        "api_docs": null
      },
      "health": {
        "status": "excellent",
        "last_check": "2025-10-30T14:00:00Z",
        "issues": []
      }
    },
    {
      "id": "mcp-server",
      "name": "MCP Server",
      "status": "production",
      "type": "api",
      "path": "/production/mcp/",
      "entry_point": "server_v2_complete.php",
      "version": "2.0.0",
      "tech_stack": ["PHP 8.1", "MySQL", "Vector Embeddings"],
      "dependencies": [
        "library/database"
      ],
      "documentation": {
        "readme": "/production/mcp/README.md",
        "copilot": "/production/mcp/.copilot/",
        "api_docs": "/production/mcp/API_DOCS.md"
      }
    },
    {
      "id": "context-generator",
      "name": "Context Generator",
      "status": "development",
      "type": "tool",
      "path": "/development/context-generator/",
      "version": "0.1.0",
      "tech_stack": ["PHP 8.1", "MySQL", "Bootstrap 5"],
      "dependencies": [
        "mcp-server",
        "library/database",
        "library/authentication"
      ]
    }
  ],

  "libraries": [
    {
      "id": "authentication",
      "name": "Authentication Library",
      "path": "/library/authentication/",
      "version": "1.5.0",
      "used_by": ["dashboard", "ai-agent", "context-generator"]
    },
    {
      "id": "session-management",
      "name": "Session Management",
      "path": "/library/session-management/",
      "version": "1.2.0",
      "used_by": ["dashboard", "ai-agent"]
    }
  ]
}
```

---

#### **COMPANY_REGISTRY.json** (All Applications)

**Location:** `/public_html/COMPANY_REGISTRY.json`

**Structure:**
```json
{
  "registry_version": "1.0.0",
  "company_name": "Ecigdis Limited (The Vape Shed)",
  "last_updated": "2025-10-30T14:23:00Z",

  "applications": [
    {
      "id": "gpt-hub",
      "name": "GPT Hub",
      "type": "hub",
      "url": "https://gpt.ecigdis.co.nz",
      "status": "active",
      "registry_file": "/public_html/PROJECT_REGISTRY.json",
      "description": "AI-powered command center",
      "projects_count": 8,
      "health": "excellent"
    },
    {
      "id": "cis",
      "name": "CIS - Staff Portal",
      "type": "satellite",
      "url": "https://staff.vapeshed.co.nz",
      "status": "active",
      "access_method": "ssh",
      "ssh_config": {
        "host": "staff.vapeshed.co.nz",
        "user": "deploy",
        "key_path": "/secure/keys/cis_deploy_key"
      },
      "description": "Central information system for staff",
      "projects_count": 12,
      "health": "good"
    },
    {
      "id": "retail-site",
      "name": "Retail Site",
      "type": "satellite",
      "url": "https://vapeshed.co.nz",
      "status": "active",
      "access_method": "api",
      "api_endpoint": "https://vapeshed.co.nz/api/scanner",
      "description": "Main retail e-commerce site",
      "projects_count": 3,
      "health": "good"
    },
    {
      "id": "vaping-kiwi",
      "name": "Vaping Kiwi",
      "type": "satellite",
      "url": "https://vapingkiwi.co.nz",
      "status": "active",
      "access_method": "manual",
      "description": "Secondary retail brand",
      "projects_count": 2,
      "health": "unknown"
    }
  ],

  "dependencies": {
    "cis_to_hub": [
      {
        "source": "cis",
        "target": "gpt-hub",
        "type": "reports_to",
        "details": "CIS sends scan results to hub"
      }
    ],
    "hub_to_cis": [
      {
        "source": "gpt-hub",
        "target": "cis",
        "type": "manages",
        "details": "Hub controls CIS via SSH"
      }
    ]
  }
}
```

---

#### **DEPENDENCY_MAP.json** (What Uses What)

**Location:** `/public_html/DEPENDENCY_MAP.json`

**Structure:**
```json
{
  "map_version": "1.0.0",
  "generated_at": "2025-10-30T14:23:00Z",

  "dependencies": [
    {
      "source": {
        "project": "dashboard",
        "file": "config/session-config.php"
      },
      "target": {
        "project": "library",
        "file": "session-management/SessionManager.php"
      },
      "type": "uses",
      "critical": true,
      "notes": "MUST load session-config.php before ConversationLogger"
    },
    {
      "source": {
        "project": "dashboard",
        "file": "login.php"
      },
      "target": {
        "project": "library",
        "file": "authentication/Auth.php"
      },
      "type": "depends_on",
      "critical": true
    },
    {
      "source": {
        "project": "context-generator",
        "file": "scanner.php"
      },
      "target": {
        "project": "mcp-server",
        "file": "server_v2_complete.php"
      },
      "type": "integrates",
      "critical": false,
      "notes": "Uses MCP for storing scanned data"
    }
  ],

  "circular_dependencies": [],

  "critical_chains": [
    {
      "chain": [
        "dashboard/login.php",
        "library/authentication/Auth.php",
        "library/database/Database.php",
        "app.php"
      ],
      "description": "Authentication chain - if any breaks, login fails"
    }
  ]
}
```

---

### **2.3 AI Agent Central Control**

**Features:**

1. **Agent Registry**
   - Track all AI agents (bots)
   - Monitor activity
   - Control permissions
   - View conversation logs

2. **Unified Configuration**
   - Single .env for all agents
   - Centralized API keys
   - Model preferences
   - Rate limits

3. **Agent Dashboard**
   - See all active agents
   - View recent conversations
   - Monitor token usage
   - Check performance

4. **Context Distribution**
   - Automatically provide context to new agents
   - Update context for existing agents
   - Emergency context injection
   - Shared knowledge base via MCP

---

### **2.4 Data Centralization**

**Hub as Single Source of Truth:**

1. **Satellite Data Collection**
   - CIS sends scan results to hub
   - Retail sites report inventory changes
   - All knowledge flows to hub

2. **Central Database**
   - Hub stores ALL application data
   - Satellites query hub for knowledge
   - MCP server provides search

3. **Unified Search**
   - Search across ALL applications
   - Semantic search via MCP
   - Cross-application relationships

---

### **2.5 Tool Builder System**

**Auto-Generate Tools:**

1. **CRUD Generators**
   - Generate create/read/update/delete interfaces
   - Based on database tables
   - Following your code standards

2. **API Generators**
   - Generate REST API endpoints
   - Based on database schema
   - Include authentication

3. **Dashboard Generators**
   - Generate admin interfaces
   - Data tables with search/filter
   - Charts and visualizations

4. **Report Generators**
   - Generate reports from data
   - Export to PDF/Excel
   - Schedule automated reports

---

## 🚀 IMPLEMENTATION PLAN

### **Phase 1: Database & Core (Week 1)**
- [ ] Create database schema
- [ ] Build file scanner
- [ ] Build pattern detector
- [ ] Build basic README generator
- [ ] Test with one project (dashboard)

### **Phase 2: Documentation Engine (Week 2)**
- [ ] Build .copilot/instructions.md generator
- [ ] Build .copilot/patterns/ extractor
- [ ] Build .copilot/context.md generator
- [ ] Implement change detection
- [ ] Test with multiple projects

### **Phase 3: Bot Integration (Week 3)**
- [ ] Build bot context generator
- [ ] Implement context caching
- [ ] Build emergency injection system
- [ ] Integrate with MCP server
- [ ] Test bot-workspace automation

### **Phase 4: Control Dashboard UI (Week 4)**
- [ ] Build overview dashboard
- [ ] Build standards configurator
- [ ] Build application manager
- [ ] Build bot context tools
- [ ] Build project scanner UI

### **Phase 5: Satellite Support (Week 5)**
- [ ] Implement SSH scanning
- [ ] Implement API scanning
- [ ] Test CIS integration
- [ ] Test retail site integration
- [ ] Manual upload system

### **Phase 6: Hub Restructure (Week 6)**
- [ ] **CRITICAL:** Backup everything first
- [ ] Create new directory structure
- [ ] Move files to proper locations
- [ ] Update all paths in code
- [ ] Generate PROJECT_REGISTRY.json
- [ ] Generate COMPANY_REGISTRY.json
- [ ] Generate DEPENDENCY_MAP.json
- [ ] Test everything still works

### **Phase 7: AI Agent Control (Week 7)**
- [ ] Build agent registry
- [ ] Build unified configuration
- [ ] Build agent dashboard
- [ ] Implement context distribution
- [ ] Test with existing agents

### **Phase 8: Automation & Polish (Week 8)**
- [ ] Set up cron jobs
- [ ] Implement auto-regeneration
- [ ] Build CLI tools
- [ ] Write documentation
- [ ] Training for users
- [ ] Go live!

---

## ⚠️ CRITICAL CONSIDERATIONS

### **Before Starting:**

1. **BACKUP EVERYTHING**
   - Full database backup
   - Full file backup
   - Test restore procedure

2. **Test Database**
   - Create test database first
   - Test schema on test DB
   - Verify performance

3. **Pilot Project**
   - Start with ONE project (dashboard)
   - Perfect it before scaling
   - Learn from mistakes

4. **Satellite Access**
   - Confirm SSH access to CIS
   - Test API endpoints
   - Verify permissions

5. **Change Management**
   - Document every change
   - Keep rollback plan
   - Test after each phase

### **During Development:**

1. **Don't Break Production**
   - Build in development/ folder
   - Test thoroughly
   - Move to production/ when ready

2. **Version Control**
   - Commit after each feature
   - Clear commit messages
   - Tag releases

3. **Performance**
   - Monitor database queries
   - Optimize slow operations
   - Cache aggressively

4. **Security**
   - Sanitize ALL input
   - Escape ALL output
   - Use prepared statements
   - Validate permissions

### **After Launch:**

1. **Monitor**
   - Check logs daily
   - Monitor performance
   - Watch for errors

2. **Iterate**
   - Gather feedback
   - Make improvements
   - Add features

3. **Maintain**
   - Keep documentation updated
   - Run regular scans
   - Clean old data

---

## 📊 SUCCESS METRICS

### **Context Generator:**
- ✅ 100% of projects have README.md
- ✅ 100% of projects have .copilot/instructions.md
- ✅ Bot context generation < 2 seconds
- ✅ Change detection < 1 hour lag
- ✅ Documentation health > 90%

### **Hub Restructure:**
- ✅ Zero broken links
- ✅ All files in proper locations
- ✅ PROJECT_REGISTRY.json complete
- ✅ All dependencies mapped
- ✅ Version control working

### **Overall:**
- ✅ Bots never say "I don't have context"
- ✅ Zero manual documentation needed
- ✅ Satellites reporting correctly
- ✅ Hub controlling everything
- ✅ AI agents centrally managed

---

## 🎯 END RESULT

**You will have:**

1. **Ultimate Context Generator**
   - Auto-generates perfect docs
   - Provides instant bot context
   - Tracks all projects (hub + satellites)
   - Adapts to YOUR preferences
   - Maintains itself automatically

2. **Intelligent Hub**
   - Clean, organized structure
   - Registry-based project management
   - AI agent central control
   - Data centralization
   - Tool builder system
   - Full version control

3. **Zero Manual Work**
   - Docs generate automatically
   - Satellites report automatically
   - Bots get context automatically
   - Changes detected automatically
   - Everything maintained automatically

**This becomes the foundation for EVERYTHING.**

---

## 📞 ANSWERS TO CRITICAL QUESTIONS

### **1. Satellite Access:**
✅ **ANSWERED:** Hub has SSH access to some satellites, HTTP/SSH may be limited, API-only for most

### **2. Database:**
✅ **ANSWERED:** Use existing database configured in .env

### **3. Current Files:**
✅ **ANSWERED:** Build scanner first with "1-button scan" feature

### **4. Restructure Timing:**
✅ **ANSWERED:** Build context generator first, restructure is part of the system

### **5. Git:**
✅ **ANSWERED:** Will be integrated into system

---

## 🎯 THE ULTIMATE VISION: ONE-BUTTON SOLUTION

### **THE DREAM WORKFLOW:**

```
USER EXPERIENCE:

1. Click: [Scan New Application] button
2. Enter: Application URL or path
3. Configure: Pre-determined coding specs (already saved)
4. Click: [Generate Everything] button
5. Wait: 2-5 minutes while magic happens
6. Result: COMPLETE application documentation + MCP setup

What Gets Generated:
✅ README.md in EVERY folder (comprehensive, not lazy)
✅ .vscode/settings.json with project config
✅ .vscode/mcp.json with MCP server connection
✅ .copilot/instructions.md with YOUR coding law
✅ .copilot/patterns/ with design patterns
✅ .copilot/context.md with history
✅ Complete MCP server with database integration
✅ Optimized tools for native experience
✅ All files cataloged in database
✅ All relationships mapped
✅ All patterns extracted
✅ Bot context pre-cached
```

---

## 🚀 COMPLETE FEATURE SPECIFICATIONS

### **FEATURE SET 1: ONE-BUTTON APPLICATION ONBOARDING**

#### **Feature 1.1: Universal Application Scanner**

**What It Does:**
Scans ANY application (hub or satellite) via SSH, API, or local access and catalogs EVERYTHING.

**One Button Click Does:**

1. **Initial Discovery**
   - Connects to application (SSH/API/Local)
   - Maps entire directory structure
   - Identifies all files and folders
   - Detects file types (PHP, JS, CSS, config, etc.)
   - Counts lines of code
   - Calculates file sizes

2. **Deep Analysis**
   - Parses PHP files for classes, functions, constants
   - Extracts database queries and table usage
   - Finds all include/require chains
   - Maps dependencies (what uses what)
   - Detects coding patterns (PDO, MySQLi, PSR style)
   - Identifies framework (Laravel, custom MVC, etc.)
   - Finds entry points (index.php, api endpoints)
   - Detects configuration files (.env, config.php)

3. **Security Scan**
   - Finds potential SQL injection points
   - Detects XSS vulnerabilities
   - Identifies missing CSRF protection
   - Finds hardcoded credentials
   - Detects insecure file operations

4. **Quality Analysis**
   - Calculates cyclomatic complexity
   - Finds duplicate code
   - Identifies dead code
   - Measures documentation coverage
   - Assigns quality score (0-100)

5. **Database Storage**
   - Stores all files in `project_files` table
   - Stores all patterns in `code_patterns` table
   - Stores all relationships in `project_relationships` table
   - Stores all issues in `known_issues` table
   - Generates file hashes for change detection

**UI:**
```
┌─────────────────────────────────────────────────────────┐
│ SCAN NEW APPLICATION                                    │
│                                                         │
│ Application Name:  [CIS - Staff Portal____________]    │
│                                                         │
│ Access Method:     ● SSH  ○ API  ○ Local              │
│                                                         │
│ SSH Configuration:                                      │
│   Host:     [staff.vapeshed.co.nz____________]         │
│   User:     [deploy_____________________]              │
│   Key Path: [/secure/keys/cis_deploy_key_____]        │
│   Base Path: [/home/master/applications/jcep...]       │
│                                                         │
│ Scan Options:                                          │
│   [✓] Catalog all files                                │
│   [✓] Extract code patterns                            │
│   [✓] Map dependencies                                 │
│   [✓] Security scan                                    │
│   [✓] Quality analysis                                 │
│   [✓] Generate documentation                           │
│                                                         │
│ Use Pre-determined Standards:                          │
│   [✓] Global Coding Standards (PDO, PSR-12, etc.)     │
│   [✓] Security Requirements (CSRF, validation, etc.)  │
│   [✓] Design Patterns (API responses, error handling) │
│                                                         │
│ [Cancel]                      [SCAN & GENERATE ALL] ←  │
└─────────────────────────────────────────────────────────┘

[ONE BUTTON DOES EVERYTHING]
```

**Progress Screen:**
```
┌─────────────────────────────────────────────────────────┐
│ SCANNING APPLICATION: CIS - Staff Portal               │
│                                                         │
│ ████████████████████░░░░░░░░░░ 67%                    │
│                                                         │
│ Current Stage: Extracting Code Patterns               │
│                                                         │
│ Progress Details:                                      │
│ ✅ Connected to server                                 │
│ ✅ Mapped directory structure (247 folders)            │
│ ✅ Cataloged files (2,847 files)                       │
│ ✅ Parsed PHP files (1,234 files)                      │
│ ✅ Extracted classes (423 classes)                     │
│ ✅ Extracted functions (2,891 functions)               │
│ ⏳ Extracting code patterns (67% complete)             │
│ ⏹️ Mapping dependencies (pending)                      │
│ ⏹️ Security scan (pending)                             │
│ ⏹️ Quality analysis (pending)                          │
│ ⏹️ Generating documentation (pending)                  │
│                                                         │
│ Time Elapsed: 2m 34s                                   │
│ Estimated Time Remaining: 1m 15s                       │
│                                                         │
│ [View Detailed Log]                                    │
└─────────────────────────────────────────────────────────┘
```

**Completion Screen:**
```
┌─────────────────────────────────────────────────────────┐
│ ✅ SCAN COMPLETE: CIS - Staff Portal                   │
│                                                         │
│ Scan Summary:                                          │
│ • Files Cataloged:        2,847                        │
│ • Classes Found:          423                          │
│ • Functions Found:        2,891                        │
│ • Code Patterns:          87                           │
│ • Dependencies Mapped:    1,234                        │
│ • Security Issues:        12 (3 critical)              │
│ • Quality Score:          78/100                       │
│                                                         │
│ Documentation Generated:                               │
│ • README.md files:        247 folders                  │
│ • .copilot/instructions.md: ✅                         │
│ • .copilot/patterns/:     87 patterns                  │
│ • .copilot/context.md:    ✅                           │
│ • .vscode/settings.json:  ✅                           │
│ • .vscode/mcp.json:       ✅                           │
│                                                         │
│ MCP Integration:                                       │
│ • Database configured:    ✅                           │
│ • Tools generated:        13 tools                     │
│ • Embeddings created:     2,847 files                  │
│ • Search indexed:         ✅                           │
│                                                         │
│ Total Time: 3m 49s                                     │
│                                                         │
│ [View Application]  [Download All Docs]  [Configure]  │
└─────────────────────────────────────────────────────────┘
```

---

#### **Feature 1.2: Pre-Determined Standards Library**

**What It Does:**
Stores YOUR coding law, security rules, and design patterns so they're applied to EVERY project automatically.

**Standards Categories:**

1. **PHP Standards**
   - Version requirement (PHP 8.1+)
   - Code style (PSR-12)
   - Strict types (required)
   - Type declarations (required)
   - PHPDoc (required for public methods)
   - Error handling (exceptions vs return codes)

2. **Database Standards**
   - Driver (PDO vs MySQLi)
   - Prepared statements (required, no raw SQL)
   - Transaction handling (explicit vs automatic)
   - Connection pooling (enabled/disabled)
   - Query logging (slow queries > 300ms)

3. **Security Standards**
   - Input validation (required on all input)
   - Output escaping (htmlspecialchars everywhere)
   - CSRF protection (required on all forms)
   - SQL injection prevention (prepared statements)
   - XSS prevention (escape all output)
   - Password hashing (password_hash())
   - Session security (HTTPOnly, Secure, SameSite)

4. **API Standards**
   - Response format (JSON envelope)
   - Status codes (proper HTTP codes)
   - Error format (structured)
   - Authentication (Bearer token, session, etc.)
   - Rate limiting (enabled/disabled)
   - Versioning (URL path vs header)

5. **File Organization**
   - Controllers location (/controllers/)
   - Models location (/models/)
   - Views location (/views/)
   - Config location (/config/)
   - Naming conventions (PascalCase, camelCase, snake_case)

6. **Design Patterns**
   - PDO query pattern
   - API response pattern
   - Error handling pattern
   - Authentication pattern
   - Form handling pattern
   - File upload pattern

**UI:**
```
┌─────────────────────────────────────────────────────────┐
│ CODING STANDARDS LIBRARY                                │
│                                                         │
│ These standards are applied to ALL new applications.   │
│                                                         │
│ [PHP Standards]  [Database]  [Security]  [API]         │
│ [File Org]  [Design Patterns]  [Import/Export]        │
│                                                         │
│ ┌─────────────────────────────────────────────────┐   │
│ │ PHP Standards                                   │   │
│ │                                                 │   │
│ │ Version:           [PHP 8.1+ ▼]                │   │
│ │ Code Style:        [PSR-12 ▼]                  │   │
│ │ Strict Types:      [✓] Required                │   │
│ │ Type Declarations: [✓] Required                │   │
│ │ PHPDoc:            [✓] Required for public     │   │
│ │ Error Handling:    [Exceptions ▼]              │   │
│ │                                                 │   │
│ │ [Save Changes]                                  │   │
│ └─────────────────────────────────────────────────┘   │
│                                                         │
│ ┌─────────────────────────────────────────────────┐   │
│ │ Design Patterns Library                         │   │
│ │                                                 │   │
│ │ 87 patterns saved                               │   │
│ │                                                 │   │
│ │ Most Used:                                      │   │
│ │ • PDO Query (used in 234 files)                │   │
│ │ • API Response (used in 89 files)              │   │
│ │ • Error Handling (used in 456 files)           │   │
│ │                                                 │   │
│ │ [View All Patterns]  [Add New Pattern]         │   │
│ └─────────────────────────────────────────────────┘   │
│                                                         │
│ [Export All Standards]  [Import Standards]             │
└─────────────────────────────────────────────────────────┘
```

---

#### **Feature 1.3: Mass Documentation Generator**

**What It Does:**
Generates comprehensive documentation for EVERY folder in the application automatically.

**Generated Files:**

1. **README.md (Every Folder)**
   - **Root:** Complete application overview (300-500 lines)
   - **Major folders:** Module documentation (200-400 lines)
   - **Subfolders:** Quick context (50-100 lines)
   - **File folders:** File listing with purpose (20-50 lines)

2. **.copilot/instructions.md (Root + Major Modules)**
   - YOUR coding standards applied
   - Security requirements
   - File organization rules
   - Naming conventions
   - Error handling patterns
   - Testing requirements
   - Examples from THIS project

3. **.copilot/patterns/ (Root)**
   - One file per pattern category
   - Actual code from THIS project (not generic)
   - ✅ DO examples
   - ❌ DON'T examples
   - Explanation of why

4. **.copilot/context.md (Root)**
   - Project history
   - Architecture decisions
   - Known issues (from database)
   - Lessons learned
   - Integration points

5. **.vscode/settings.json**
   - Editor preferences
   - PHP version
   - Extensions recommended
   - Linting rules
   - Formatting rules

6. **.vscode/mcp.json**
   - MCP server connection
   - Database access configured
   - Tools available
   - Search capabilities

**Generation Process:**
```
For each folder:
1. Analyze contents
2. Detect purpose (controllers, models, views, etc.)
3. Find critical files
4. Extract examples
5. Generate README using template + actual data
6. Apply YOUR standards (from library)
7. Write file
8. Log in generation_history table
```

**Example Generated README.md:**
```markdown
# Controllers

Business logic handlers for the CIS Staff Portal.

## Purpose

This folder contains all HTTP request handlers (controllers) that process
user requests, interact with models, and return responses.

## Architecture

**Pattern:** MVC (Model-View-Controller)
**Framework:** Custom PHP MVC
**Entry Point:** `/index.php` routes to these controllers

## Controllers in This Folder

### UserController.php
- **Purpose:** User management (CRUD operations)
- **Routes:** `/users/list`, `/users/create`, `/users/edit/:id`, `/users/delete/:id`
- **Dependencies:** `User` model, `Auth` library, `Database` library
- **Authentication:** Required (admin role)
- **Lines of Code:** 347
- **Complexity Score:** Medium (7/15)
- **Last Modified:** 2025-10-28

**Key Methods:**
- `listUsers()` - Display user list with pagination
- `createUser()` - Create new user (form + submission)
- `editUser($id)` - Edit existing user
- `deleteUser($id)` - Soft delete user
- `validateUserInput($data)` - Validate user form data

**Security Notes:**
- ✅ CSRF protection on all forms
- ✅ Input validation on all fields
- ✅ Output escaping in views
- ✅ Password hashing with password_hash()
- ✅ Permission checks on all actions

[... continues for every controller ...]

## Coding Standards

See [/.copilot/instructions.md](/.copilot/instructions.md) for full standards.

**Quick Reference:**
- Use PDO prepared statements (never raw SQL)
- Type-hint all parameters and returns
- Use try-catch for error handling
- Return JSON envelope for AJAX responses
- Log all errors to database

## Adding a New Controller

1. Create file: `NewThingController.php`
2. Extend `BaseController`
3. Add methods for each action
4. Register routes in `/routes.php`
5. Add tests in `/tests/controllers/`

**Template:**
```php
<?php
declare(strict_types=1);

namespace Controllers;

class NewThingController extends BaseController
{
    public function index(): void
    {
        // List all things
    }

    public function show(int $id): void
    {
        // Show single thing
    }
}
```

## Related Files

- **Models:** `/models/` - Data access layer
- **Views:** `/views/controllers/` - UI templates for these controllers
- **Routes:** `/routes.php` - URL routing configuration
- **Base:** `/library/BaseController.php` - Parent class with shared methods

## Testing

Test files: `/tests/controllers/`

Run tests:
```bash
php vendor/bin/phpunit tests/controllers/
```

## Known Issues

None currently.

## Recent Changes

- **2025-10-28:** Added `UserController::exportUsers()` for CSV export
- **2025-10-25:** Fixed CSRF token validation in `AuthController`
- **2025-10-20:** Refactored error handling to use exceptions

---

**Last Updated:** 2025-10-30
**Generated By:** Context Generator v1.0
**Quality Score:** 85/100
```

---

#### **Feature 1.4: AI-Powered Content Generation**

**What It Does:**
Uses AI agents to generate better documentation than template-only approach.

**AI Tasks:**

1. **Summarization**
   - Read complex code
   - Generate plain English explanations
   - Create high-level overviews
   - Write user-friendly descriptions

2. **Example Generation**
   - Extract best examples from code
   - Generate ✅ DO / ❌ DON'T pairs
   - Create step-by-step tutorials
   - Write quick-start guides

3. **Architecture Documentation**
   - Analyze code structure
   - Generate architecture diagrams (Mermaid)
   - Create data flow diagrams
   - Write design decision records

4. **Troubleshooting Guides**
   - Analyze known issues
   - Generate troubleshooting steps
   - Create diagnostic checklists
   - Write solution guides

**AI Integration:**

```php
// Use AI agent to generate better descriptions
$fileContent = file_get_contents($file);

$prompt = "
Analyze this PHP controller and generate a concise description:

File: $file
Code:
$fileContent

Generate:
1. Purpose (1 sentence)
2. Key responsibilities (3-5 bullet points)
3. Dependencies (list)
4. Security considerations (list)
5. Common use cases (3 examples)

Output as JSON.
";

$aiResponse = AIAgent::generate($prompt);
$description = json_decode($aiResponse);

// Use AI-generated content in README
$readme .= "## " . basename($file) . "\n\n";
$readme .= $description->purpose . "\n\n";
$readme .= "**Responsibilities:**\n";
foreach ($description->responsibilities as $resp) {
    $readme .= "- $resp\n";
}
```

**AI Agents Used:**

1. **Code Analyzer Agent**
   - Reads code
   - Understands logic
   - Explains purpose
   - Identifies patterns

2. **Documentation Writer Agent**
   - Writes clear explanations
   - Uses proper formatting
   - Follows templates
   - Maintains consistency

3. **Pattern Extractor Agent**
   - Finds common patterns
   - Generates examples
   - Creates anti-patterns
   - Documents best practices

4. **Architecture Analyzer Agent**
   - Maps system structure
   - Identifies components
   - Finds relationships
   - Documents flow

**UI:**
```
┌─────────────────────────────────────────────────────────┐
│ AI-POWERED GENERATION SETTINGS                          │
│                                                         │
│ Use AI to enhance documentation:                       │
│                                                         │
│ [✓] Generate descriptions (instead of templates)       │
│ [✓] Extract best examples (AI selects most useful)    │
│ [✓] Create architecture docs (AI analyzes structure)  │
│ [✓] Write troubleshooting guides (AI synthesizes)     │
│                                                         │
│ AI Model:                                              │
│   [GPT-4o ▼]  gpt-4-turbo, claude-3-opus, etc.        │
│                                                         │
│ Generation Style:                                      │
│   [Professional ▼]  Casual, Technical, Detailed        │
│                                                         │
│ Quality vs Speed:                                      │
│   Quality ████████░░ Speed                             │
│                                                         │
│ Estimated Time:                                        │
│   Without AI: 2 minutes                                │
│   With AI:    5 minutes (better quality)               │
│                                                         │
│ [Save Settings]                                        │
└─────────────────────────────────────────────────────────┘
```

---

#### **Feature 1.5: MCP Server Auto-Configuration**

**What It Does:**
Sets up complete MCP (Model Context Protocol) server with database integration, tools, and search.

**Auto-Generated MCP Setup:**

1. **Database Integration**
   ```sql
   -- Auto-creates MCP tables in existing database
   CREATE TABLE mcp_knowledge_items (
       id INT PRIMARY KEY AUTO_INCREMENT,
       project_id INT NOT NULL,
       file_path VARCHAR(500),
       content_type VARCHAR(50),
       title VARCHAR(255),
       content LONGTEXT,
       metadata JSON,
       embeddings BLOB,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       FOREIGN KEY (project_id) REFERENCES projects(id),
       FULLTEXT KEY ft_content (content),
       INDEX idx_project (project_id),
       INDEX idx_type (content_type)
   );
   ```

2. **MCP Tools Generation**
   ```json
   {
     "tools": [
       {
         "name": "semantic_search",
         "description": "Search project with natural language",
         "parameters": {
           "query": "string",
           "project_id": "int",
           "limit": "int"
         }
       },
       {
         "name": "get_file_content",
         "description": "Get file with context",
         "parameters": {
           "file_path": "string",
           "include_related": "boolean"
         }
       },
       {
         "name": "find_pattern",
         "description": "Find code pattern usage",
         "parameters": {
           "pattern_name": "string",
           "project_id": "int"
         }
       },
       {
         "name": "list_functions",
         "description": "List all functions in project",
         "parameters": {
           "project_id": "int",
           "filter": "string"
         }
       },
       {
         "name": "get_dependencies",
         "description": "Get file dependencies",
         "parameters": {
           "file_path": "string"
         }
       }
       // ... 13 tools total
     ]
   }
   ```

3. **.vscode/mcp.json Generation**
   ```json
   {
     "mcpServers": {
       "cis-knowledge": {
         "command": "php",
         "args": [
           "/home/master/applications/jcepnzzkmj/public_html/production/mcp/server_v2_complete.php"
         ],
         "env": {
           "PROJECT_ID": "2",
           "DATABASE_HOST": "127.0.0.1",
           "DATABASE_NAME": "jcepnzzkmj",
           "DATABASE_USER": "jcepnzzkmj",
           "DATABASE_PASS": "wprKh9Jq63"
         }
       }
     }
   }
   ```

4. **Vector Embeddings**
   - Generates embeddings for all files
   - Stores in database
   - Enables semantic search
   - Updates on file changes

5. **Optimized Tools**
   - Fast search (< 200ms)
   - Relevance ranking
   - Context-aware results
   - Relationship traversal

**Generated MCP Server Features:**

✅ **Semantic Search** - Natural language queries
✅ **Full-Text Search** - Keyword search
✅ **Code Pattern Search** - Find pattern usage
✅ **Dependency Lookup** - What uses what
✅ **File Content** - Get file with context
✅ **Function List** - All functions with signatures
✅ **Class Hierarchy** - Inheritance tree
✅ **Database Schema** - Table structure
✅ **API Endpoints** - All routes
✅ **Configuration** - All config values
✅ **Known Issues** - Problems and solutions
✅ **Recent Changes** - Changelog
✅ **Related Files** - Context-aware suggestions

---

### **FEATURE SET 2: INTELLIGENT UPDATES**

#### **Feature 2.1: Automatic Change Detection**

**What It Does:**
Detects when files change and triggers selective regeneration.

**Detection Levels:**

1. **Critical File Changed**
   - Examples: session-config.php, auth.php, database.php
   - Action: Immediate regeneration of all affected docs
   - Notification: Email + Slack alert
   - Priority: P1

2. **Normal File Changed**
   - Examples: UserController.php, User.php
   - Action: Mark for regeneration in next cycle (1-4 hours)
   - Priority: P2

3. **Documentation Changed**
   - Examples: README.md manually edited
   - Action: Update database record, don't overwrite
   - Priority: P3

4. **Config Changed**
   - Examples: .env, config.php
   - Action: Re-scan project, update standards if needed
   - Priority: P1

**Smart Regeneration:**
```
Only regenerates:
- README.md sections that reference the changed file
- .copilot/patterns/ if pattern usage changed
- .copilot/context.md if critical change
- MCP embeddings for changed file only

Does NOT regenerate:
- Entire README if small change
- Manually edited sections (protected)
- Unchanged documentation
```

**UI:**
```
┌─────────────────────────────────────────────────────────┐
│ CHANGE DETECTION                                        │
│                                                         │
│ ⚠️  3 files need update                                │
│                                                         │
│ 🔴 CRITICAL (Requires immediate action)                │
│ • session-config.php (changed 2 hours ago)             │
│   Impact: Dashboard login may be affected              │
│   Affected docs: 3 READMEs, 1 .copilot/instructions    │
│   [Regenerate Now]                                     │
│                                                         │
│ 🟡 NORMAL (Will update in next cycle)                  │
│ • UserController.php (changed 4 hours ago)             │
│   Impact: User management docs outdated                │
│   Next update: In 2 hours                              │
│   [Regenerate Now] [Skip]                              │
│                                                         │
│ • AuthController.php (changed 6 hours ago)             │
│   Impact: Auth flow docs may be outdated               │
│   Next update: In 4 hours                              │
│   [Regenerate Now] [Skip]                              │
│                                                         │
│ [Update All]  [Configure Detection]                    │
└─────────────────────────────────────────────────────────┘
```

---

#### **Feature 2.2: Incremental Scanning**

**What It Does:**
Only scans changed files instead of entire project on every update.

**Process:**
1. Compare file hashes
2. Identify changed files only
3. Re-parse changed files
4. Update database records
5. Regenerate affected docs only

**Performance:**
- Full scan: 3-5 minutes
- Incremental: 10-30 seconds

---

### **FEATURE SET 3: DASHBOARD REPLACEMENT**

#### **Feature 3.1: Replace Existing Pages**

**Current Dashboard Pages to Replace:**

1. **Standards Management** → Replace with Standards Library
2. **Prompts Generation** → Replace with AI Content Generator
3. **Project Overview** → Replace with Application Manager
4. **Code Analysis** → Replace with Scanner Results

**Migration Plan:**
```
Phase 1: Build new pages (don't touch old ones)
Phase 2: Test new pages thoroughly
Phase 3: Add "Switch to New UI" button
Phase 4: Migrate data from old system
Phase 5: Deprecate old pages (keep for 30 days)
Phase 6: Remove old pages completely
```

**New Dashboard Structure:**
```
/dashboard/
├── index.php                      (Overview dashboard)
├── login.php                      (Keep as-is)
├── logout.php                     (Keep as-is)
│
├── context-generator/             (NEW - Replaces old features)
│   ├── index.php                 (Overview)
│   ├── scan-application.php      (Scanner UI)
│   ├── standards-library.php     (Standards management)
│   ├── applications.php          (App manager)
│   ├── bot-context.php           (Bot tools)
│   ├── settings.php              (System config)
│   └── api/                      (API endpoints)
│       ├── scan.php
│       ├── generate.php
│       ├── standards.php
│       └── context.php
│
├── old-features/                  (DEPRECATED - To be removed)
│   ├── standards-old.php
│   ├── prompts-old.php
│   └── analysis-old.php
│
├── config/                        (Keep as-is)
├── controllers/                   (Keep as-is)
├── views/                         (Keep as-is)
└── assets/                        (Keep as-is)
```

---

### **FEATURE SET 4: COMPLETE SPECIFICATIONS**

#### **All Features Summary**

**ONE-BUTTON FEATURES:**

1. ✅ **Scan Application**
   - Connects to any application (SSH/API/Local)
   - Catalogs every file and folder
   - Parses all code
   - Extracts patterns
   - Maps dependencies
   - Security scan
   - Quality analysis
   - Stores in database

2. ✅ **Generate Documentation**
   - README.md in EVERY folder
   - Comprehensive (200-500 lines for major folders)
   - Uses AI for better content
   - Actual code examples (not generic)
   - YOUR standards applied automatically

3. ✅ **Generate .copilot/ Files**
   - instructions.md (YOUR coding law)
   - patterns/ (YOUR design patterns)
   - context.md (project history)
   - Applied to root + major modules

4. ✅ **Generate .vscode/ Files**
   - settings.json (editor config)
   - mcp.json (MCP server connection)
   - Project-specific settings

5. ✅ **Setup MCP Server**
   - Database integration
   - 13 optimized tools
   - Vector embeddings
   - Semantic search
   - Fast queries (< 200ms)

6. ✅ **AI-Powered Enhancement**
   - Better descriptions (AI writes)
   - Better examples (AI selects)
   - Architecture docs (AI analyzes)
   - Troubleshooting guides (AI creates)

7. ✅ **Automatic Updates**
   - Change detection
   - Incremental scanning
   - Smart regeneration
   - No manual maintenance

8. ✅ **Multi-Application Support**
   - Hub + satellites
   - SSH access
   - API access
   - Manual upload

**CONFIGURATION FEATURES:**

9. ✅ **Standards Library**
   - PHP standards
   - Database standards
   - Security standards
   - API standards
   - File organization
   - Design patterns
   - Applied globally or per-project

10. ✅ **AI Settings**
    - Model selection (GPT-4, Claude, etc.)
    - Generation style
    - Quality vs speed
    - Cost control

11. ✅ **Scan Settings**
    - What to scan
    - How deep to analyze
    - Security scan options
    - Quality thresholds

12. ✅ **Update Settings**
    - Auto-update frequency
    - Change detection sensitivity
    - Critical file list
    - Notification preferences

**MONITORING FEATURES:**

13. ✅ **Dashboard**
    - System health
    - Scan status
    - Documentation health
    - Recent activity
    - Quick stats

14. ✅ **Reports**
    - Quality scores
    - Security issues
    - Code patterns
    - Dependencies
    - Change history

15. ✅ **Alerts**
    - Critical file changes
    - Security vulnerabilities
    - Quality degradation
    - Scan failures

**INTEGRATION FEATURES:**

16. ✅ **MCP Integration**
    - Database-backed
    - 13 tools
    - Vector search
    - Fast queries

17. ✅ **AI Integration**
    - Multiple models
    - Natural language
    - Better content
    - Pattern recognition

18. ✅ **Git Integration**
    - Track changes
    - Commit history
    - Version control
    - Rollback capability

19. ✅ **API Access**
    - REST API for all features
    - Webhook support
    - External integrations
    - CLI tools

**ADVANCED FEATURES:**

20. ✅ **Batch Operations**
    - Scan multiple apps
    - Regenerate all docs
    - Update all patterns
    - Bulk configuration

21. ✅ **Templates**
    - Custom README templates
    - Custom .copilot templates
    - Project-specific templates
    - Template library

22. ✅ **Export/Import**
    - Export standards
    - Import standards
    - Export documentation
    - Migrate between systems

23. ✅ **Rollback**
    - Restore previous docs
    - Undo changes
    - Version history
    - Compare versions

24. ✅ **Performance**
    - Fast scans (< 5 min)
    - Incremental updates (< 30 sec)
    - Fast queries (< 200ms)
    - Caching everywhere

25. ✅ **Security**
    - Encrypted credentials
    - SSH key management
    - API token rotation
    - Audit logging

---

## 📊 TECHNICAL SPECIFICATIONS

### **Performance Targets:**

| Operation | Target | Maximum |
|-----------|--------|---------|
| Full Application Scan | < 5 min | 10 min |
| Incremental Scan | < 30 sec | 1 min |
| Documentation Generation | < 2 min | 5 min |
| MCP Query | < 200ms | 500ms |
| AI Content Generation | < 10 sec | 30 sec |
| Change Detection | < 5 sec | 10 sec |
| Dashboard Load | < 1 sec | 2 sec |

### **Storage Requirements:**

| Data Type | Size per Application |
|-----------|---------------------|
| Database records | 50-100 MB |
| Vector embeddings | 100-200 MB |
| Generated docs | 5-10 MB |
| Cache | 20-50 MB |
| **Total** | **175-360 MB** |

### **API Rate Limits:**

| Endpoint | Rate Limit |
|----------|------------|
| Scan Application | 1 per 5 minutes |
| Generate Docs | 10 per minute |
| MCP Queries | 100 per minute |
| AI Requests | 20 per minute |
| General API | 1000 per hour |

---

## 🎯 SUCCESS CRITERIA

**System is successful when:**

### User Experience Criteria:
1. ✅ User clicks ONE button to scan application
2. ✅ 3-5 minutes later, EVERYTHING is generated
3. ✅ README.md in EVERY folder (comprehensive, 200-500 lines)
4. ✅ .copilot/instructions.md with user's coding law
5. ✅ .copilot/patterns/ with actual code examples
6. ✅ .vscode/mcp.json with project-specific MCP configured
7. ✅ MCP working with semantic search across project
8. ✅ Bot gets instant context on any project
9. ✅ Changes auto-detected and docs updated automatically
10. ✅ Zero manual maintenance required
11. ✅ Works for hub + ALL satellites (local, SSH, API)
12. ✅ User preferences applied to all documentation

### Feature Completion Criteria:
- ✅ **MVP Ready:** Features F001-F090 complete (Foundation + Critical)
- ✅ **Production Ready:** Features F001-F132 complete (+ Dashboard + MCP)
- ✅ **Enterprise Ready:** Features F001-F213 complete (All features)

### Quality Metrics:
- Documentation coverage: > 95% of files
- Quality score: > 80/100 (AI-calculated)
- Intelligence score: > 70/100 (AI-calculated)
- Business value score: > 60/100 (AI-calculated)
- Documentation freshness: < 24 hours old
- MCP query speed: < 200ms p95
- Scan performance: < 5 minutes for full scan
- Incremental update: < 30 seconds
- User satisfaction: > 90%
- Bot context relevance: > 85%

### Integration Success:
- ✅ All files stored in `intelligence_content` table
- ✅ All patterns stored in `neural_patterns` table
- ✅ All metrics tracked in `intelligence_metrics`
- ✅ MCP server returns project-specific results
- ✅ AI models used for content generation
- ✅ Satellites sync successfully
- ✅ Standards library fully functional
- ✅ Change detection working accurately

---

## 🚀 NEXT STEPS

### Immediate Actions:
1. ✅ Review and approve definitive feature checklist (F001-F213)
2. Create database integration layer (F024-F037)
3. Build deep code scanner (F014-F023)
4. Implement standards library (F076-F090)
5. Create web dashboard UI (F091-F108)
6. Deploy MVP and test with one project

### Development Phases:
- **Phase 1 (Weeks 1-2):** Database integration - connect to existing intelligence tables
- **Phase 2 (Weeks 2-3):** Deep scanning - pattern extraction, dependency mapping
- **Phase 3 (Weeks 3-4):** Change detection - smart incremental updates
- **Phase 4 (Weeks 4-5):** Standards library - user preferences system
- **Phase 5 (Weeks 5-6):** Documentation generation - comprehensive READMEs + .copilot
- **Phase 6 (Weeks 6-7):** Dashboard - one-button operation with progress tracking
- **Phase 7 (Weeks 7-8):** AI content - natural language generation for better docs
- **Phase 8 (Ongoing):** Advanced features - diagrams, reports, integrations

### Quick Start After Approval:
```bash
# 1. Create database tables (new + integrate existing)
php scripts/setup-database.php

# 2. Run first scan
php scripts/scan-application.php --app=hub --full

# 3. Generate documentation
php scripts/generate-docs.php --app=hub

# 4. Start dashboard
php -S localhost:8080 -t dashboard/

# 5. Open browser and click "SCAN NOW"
```

---

## 📌 QUICK REFERENCE: FEATURE TRACKING

**Use this for daily standups and progress tracking:**

### By Category:
- **Foundation (Existing):** F001-F013 = 13 features ✅ DONE
- **Deep Scanning:** F014-F023 = 10 features ⏳ TODO
- **Database Integration:** F024-F037 = 14 features ⏳ TODO (PRIORITY 1)
- **Documentation Generation:** F038-F057 = 20 features ⏳ TODO (PRIORITY 2)
- **AI Content:** F058-F066 = 9 features ⏳ TODO
- **Change Detection:** F067-F075 = 9 features ⏳ TODO (PRIORITY 3)
- **Standards Library:** F076-F090 = 15 features ⏳ TODO (PRIORITY 4)
- **Dashboard:** F091-F117 = 27 features ⏳ TODO (PRIORITY 5)
- **Project Types:** F118-F125 = 8 features ⏳ TODO
- **MCP Integration:** F126-F132 = 7 features ⏳ TODO
- **Satellite Integration:** F133-F139 = 7 features ⏳ TODO
- **Version Control:** F140-F146 = 7 features ⏳ TODO
- **Reporting:** F147-F155 = 9 features ⏳ TODO
- **Bot Context:** F156-F162 = 7 features ⏳ TODO
- **Advanced Features:** F163-F175 = 13 features ⏳ TODO
- **Intelligence Integration:** F176-F185 = 10 features ⏳ TODO
- **Enhancements:** F186-F213 = 28 features ⏳ TODO

### Progress Tracker:
```
Total Features: 213
✅ Complete: 13 (6%)
⏳ In Progress: 0 (0%)
❌ Todo: 200 (94%)

MVP Target: 90 features (F001-F090)
MVP Progress: 13/90 = 14% complete

Production Target: 132 features (F001-F132)
Production Progress: 13/132 = 10% complete
```

### This Week's Focus:
- [ ] F024-F037: Database integration (14 features)
- [ ] F014-F023: Deep scanning foundation (10 features)
- [ ] F076-F085: Standards library core (10 features)

### Blockers:
- None currently - ready to build!

### Questions to Resolve:
- Database credentials for intelligence tables
- Preferred AI model for content generation
- SSH keys for satellite access
- Standards library initial configuration

---

**Ready to start building? Let's tick off these features! 🚀**
