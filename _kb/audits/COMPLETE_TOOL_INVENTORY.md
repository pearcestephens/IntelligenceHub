# 📦 Complete Tool Inventory

**Generated:** 2025-10-30 00:14:43

---

## 📊 Statistics

- **Total Tools:** 3521
- **Categories:** 10
- **Duplicates:** 445
- **Registry Coverage:** 0.0%

---

## 🏷️  Tools by Category

### Ai Agent (47 tools)

#### PerformanceTestTool
- **File:** `ai-agent/src/Tools/PerformanceTestTool.php`
- **Type:** PHP
- **Size:** 2.13 KB
- **Methods:** 2

#### ToolExecutor
- **File:** `ai-agent/src/Tools/ToolExecutor.php`
- **Type:** PHP
- **Size:** 17.46 KB
- **Capabilities:**
  - Tool Executor for safe orchestration of AI agent tools
  - Get conversation ID from context
  - Execute a single tool
- **Methods:** 16

#### MultiDomainTools
- **File:** `ai-agent/src/Tools/MultiDomainTools.php`
- **Type:** PHP
- **Size:** 13.15 KB
- **Capabilities:**
  - Multi-Domain MCP Tools
  - Register all multi-domain tools with the tool registry
  - Execute multi-domain tool
- **Methods:** 9

#### GrepTool
- **File:** `ai-agent/src/Tools/GrepTool.php`
- **Type:** PHP
- **Size:** 2.91 KB
- **Methods:** 2

#### MemoryTool
- **File:** `ai-agent/src/Tools/MemoryTool.php`
- **Type:** PHP
- **Size:** 20.56 KB
- **Capabilities:**
  - Memory Tool for conversation context and memory management
  - Get conversation context for AI agent
  - Store conversation memory
- **Methods:** 12

#### HttpTool
- **File:** `ai-agent/src/Tools/HttpTool.php`
- **Type:** PHP
- **Size:** 17.59 KB
- **Capabilities:**
  - HTTP Tool for safe external API requests
  - Make HTTP request with safety controls
  - Contract run() proxies to request()
- **Methods:** 15

#### EnvTool
- **File:** `ai-agent/src/Tools/EnvTool.php`
- **Type:** PHP
- **Size:** 1.47 KB
- **Methods:** 2

#### ReadyCheckTool
- **File:** `ai-agent/src/Tools/ReadyCheckTool.php`
- **Type:** PHP
- **Size:** 1.63 KB
- **Capabilities:**
  - Execute the ready-check script and return its stdout plus success flag.
- **Methods:** 1

#### DeputyHRTool
- **File:** `ai-agent/src/Tools/DeputyHRTool.php`
- **Type:** PHP
- **Size:** 15.69 KB
- **Capabilities:**
  - Deputy Integration Tool for AI Agent System
  - Execute
  - Analyze Staffing
- **Methods:** 20

#### KnowledgeTool
- **File:** `ai-agent/src/Tools/KnowledgeTool.php`
- **Type:** PHP
- **Size:** 15.06 KB
- **Capabilities:**
  - Knowledge Base Tool for semantic search and document management
  - Search the knowledge base
  - Get document by ID with full content
- **Methods:** 11

#### RedisTool
- **File:** `ai-agent/src/Tools/RedisTool.php`
- **Type:** PHP
- **Size:** 1.87 KB
- **Methods:** 2

#### MonitoringTool
- **File:** `ai-agent/src/Tools/MonitoringTool.php`
- **Type:** PHP
- **Size:** 2 KB
- **Methods:** 2

#### EndpointProbeTool
- **File:** `ai-agent/src/Tools/EndpointProbeTool.php`
- **Type:** PHP
- **Size:** 3.69 KB
- **Methods:** 2

#### DeploymentManagerTool
- **File:** `ai-agent/src/Tools/DeploymentManagerTool.php`
- **Type:** PHP
- **Size:** 2.09 KB
- **Methods:** 2

#### OpsMaintainTool
- **File:** `ai-agent/src/Tools/OpsMaintainTool.php`
- **Type:** PHP
- **Size:** 1.62 KB
- **Capabilities:**
  - Run list -> archive(dry_run) -> archive -> ready-check
- **Methods:** 1

#### SystemDoctorTool
- **File:** `ai-agent/src/Tools/SystemDoctorTool.php`
- **Type:** PHP
- **Size:** 1.91 KB
- **Methods:** 2

#### DatabaseTool
- **File:** `ai-agent/src/Tools/DatabaseTool.php`
- **Type:** PHP
- **Size:** 18.41 KB
- **Capabilities:**
  - Database Tool for safe SQL query execution
  - Execute a safe database query
  - Contract run() routes to safe operations
- **Methods:** 10

#### OpsTools
- **File:** `ai-agent/src/Tools/OpsTools.php`
- **Type:** PHP
- **Size:** 4.76 KB
- **Capabilities:**
  - Ops-related bot-only tools: ReadyCheckTool and RepoCleanerTool
  - Execute the ready check script and return its JSON/stdout as array
  - Execute the repo-cleaner with TOOL_PARAMETERS and return its JSON result
- **Methods:** 2

#### RepoCleanerTool
- **File:** `ai-agent/src/Tools/RepoCleanerTool.php`
- **Type:** PHP
- **Size:** 1.8 KB
- **Capabilities:**
  - Execute the repo-cleaner with TOOL_PARAMETERS and return its JSON result
- **Methods:** 1

#### ToolContract
- **File:** `ai-agent/src/Tools/Contracts/ToolContract.php`
- **Type:** PHP
- **Size:** 798 B
- **Capabilities:**
  - ToolContract defines the strict interface all tools should follow.
  - Execute the tool.
  - Return tool specification (self-documentation):
- **Methods:** 2

#### ToolChainOrchestrator
- **File:** `ai-agent/src/Tools/ToolChainOrchestrator.php`
- **Type:** PHP
- **Size:** 16.99 KB
- **Capabilities:**
  - Tool Chain Orchestrator
  - Create new tool chain
  - Get existing chain
- **Methods:** 20

#### ToolCatalog
- **File:** `ai-agent/src/Tools/ToolCatalog.php`
- **Type:** PHP
- **Size:** 4.54 KB
- **Capabilities:**
  - Return catalog of tool specs using ToolRegistry definitions; includes spec() when available.
  - JSON for OpenAI tools (function calling) – mirrors ToolRegistry::getOpenAISchema but inlined here for convenience.
  - Export a YAML catalog (no external libs – simple emitter for our limited schema).
- **Methods:** 5

#### LogsTool
- **File:** `ai-agent/src/Tools/LogsTool.php`
- **Type:** PHP
- **Size:** 1.89 KB
- **Methods:** 2

#### FileTool
- **File:** `ai-agent/src/Tools/FileTool.php`
- **Type:** PHP
- **Size:** 18.97 KB
- **Capabilities:**
  - File Tool for safe file operations with security controls
  - Read file contents with safety checks
  - Contract run() dispatcher for file tool
- **Methods:** 9

#### SecurityScanTool
- **File:** `ai-agent/src/Tools/SecurityScanTool.php`
- **Type:** PHP
- **Size:** 2.09 KB
- **Methods:** 2

#### StaticAnalysisTool
- **File:** `ai-agent/src/Tools/StaticAnalysisTool.php`
- **Type:** PHP
- **Size:** 4.68 KB
- **Methods:** 3

#### DBExplainTool
- **File:** `ai-agent/src/Tools/DBExplainTool.php`
- **Type:** PHP
- **Size:** 1.14 KB
- **Methods:** 2

#### ToolRegistry
- **File:** `ai-agent/src/Tools/ToolRegistry.php`
- **Type:** PHP
- **Size:** 42.35 KB
- **Capabilities:**
  - Tool Registry for managing AI agent tools and capabilities
  - Optional instance constructor to align with code that injects ToolRegistry
  - Initialize the tool registry with core tools
- **Methods:** 20

#### batch-6-gate-validation
- **File:** `ai-agent/bin/batch-6-gate-validation.php`
- **Type:** PHP
- **Size:** 10.96 KB
- **Capabilities:**
  - BATCH-6 Gate Validation Script
- **Methods:** 4

#### verify-endpoints
- **File:** `ai-agent/bin/verify-endpoints.php`
- **Type:** PHP
- **Size:** 10.4 KB
- **Capabilities:**
  - API Endpoint Body Verification Tool
- **Methods:** 1

#### validate-environment
- **File:** `ai-agent/bin/validate-environment.php`
- **Type:** PHP
- **Size:** 10.28 KB
- **Capabilities:**
  - Environment Validator & Setup Tool
- **Methods:** 1

#### dev-check
- **File:** `ai-agent/bin/dev-check.php`
- **Type:** PHP
- **Size:** 5.15 KB
- **Capabilities:**
  - Development Environment Check

#### api-test-suite
- **File:** `ai-agent/bin/api-test-suite.sh`
- **Type:** Shell
- **Size:** 21.16 KB

#### quick-fix-db-methods
- **File:** `ai-agent/bin/quick-fix-db-methods.php`
- **Type:** PHP
- **Size:** 2.18 KB
- **Capabilities:**
  - Quick Fix Script: Database Method Name Corrections

#### batch-5-gate-validation
- **File:** `ai-agent/bin/batch-5-gate-validation.php`
- **Type:** PHP
- **Size:** 8.96 KB
- **Capabilities:**
  - BATCH-5 Gate Validation Script
  - Checkmark
- **Methods:** 2

#### setup-production
- **File:** `ai-agent/bin/setup-production.php`
- **Type:** PHP
- **Size:** 8.19 KB
- **Capabilities:**
  - One-Command Environment Setup & Verification
- **Methods:** 5

#### preflight-check
- **File:** `ai-agent/bin/preflight-check.php`
- **Type:** PHP
- **Size:** 8.01 KB
- **Capabilities:**
  - BATCH-6 Pre-Flight Checklist
- **Methods:** 4

#### db-tool
- **File:** `ai-agent/bin/db-tool.php`
- **Type:** PHP
- **Size:** 1.41 KB

#### verify-phpunit-env
- **File:** `ai-agent/bin/verify-phpunit-env.php`
- **Type:** PHP
- **Size:** 4.67 KB
- **Capabilities:**
  - Verify .env Loading in PHPUnit Bootstrap

#### emergency-security-fix
- **File:** `ai-agent/bin/emergency-security-fix.sh`
- **Type:** Shell
- **Size:** 18.36 KB

#### batch-7-diagnostic
- **File:** `ai-agent/bin/batch-7-diagnostic.php`
- **Type:** PHP
- **Size:** 6.35 KB
- **Capabilities:**
  - BATCH-7 Quick Diagnostic

#### rename-tables
- **File:** `ai-agent/bin/rename-tables.sh`
- **Type:** Shell
- **Size:** 15.03 KB
- **Methods:** 1

#### pre-flight-check
- **File:** `ai-agent/bin/pre-flight-check.sh`
- **Type:** Shell
- **Size:** 18.67 KB
- **Capabilities:**
  - Check
- **Methods:** 2

#### quick-migrate
- **File:** `ai-agent/bin/quick-migrate.sh`
- **Type:** Shell
- **Size:** 591 B

#### run-migration
- **File:** `ai-agent/bin/run-migration.sh`
- **Type:** Shell
- **Size:** 5.85 KB

#### dump-tool-catalog
- **File:** `ai-agent/bin/dump-tool-catalog.php`
- **Type:** PHP
- **Size:** 256 B

#### verify-schema
- **File:** `ai-agent/bin/verify-schema.php`
- **Type:** PHP
- **Size:** 8.01 KB
- **Capabilities:**
  - Migration Verification Script

---

### Mcp Server (13 tools)

#### RedisTool
- **File:** `mcp/src/Tools/RedisTool.php`
- **Type:** PHP
- **Size:** 10.19 KB
- **Capabilities:**
  - Redis Tool - Cache Management & Analysis
  - Execute
- **Methods:** 14

#### SystemStatsTool
- **File:** `mcp/src/Tools/SystemStatsTool.php`
- **Type:** PHP
- **Size:** 3.26 KB
- **Capabilities:**
  - System Statistics Tool
  - Execute system stats retrieval
  - Get count of indexed files
- **Methods:** 6

#### PasswordStorageTool
- **File:** `mcp/src/Tools/PasswordStorageTool.php`
- **Type:** PHP
- **Size:** 10.45 KB
- **Capabilities:**
  - Password Storage Tool
  - Execute credential operations
  - Store a new credential
- **Methods:** 10

#### DatabaseTool
- **File:** `mcp/src/Tools/DatabaseTool.php`
- **Type:** PHP
- **Size:** 9.26 KB
- **Capabilities:**
  - Database Tool - Advanced Query Builder & Analyzer
  - Execute
  - Execute Query
- **Methods:** 10

#### LogsTool
- **File:** `mcp/src/Tools/LogsTool.php`
- **Type:** PHP
- **Size:** 13.68 KB
- **Capabilities:**
  - Logs Tool - Log Analysis & Parsing
  - Execute
  - Analyze Log
- **Methods:** 12

#### FileTool
- **File:** `mcp/src/Tools/FileTool.php`
- **Type:** PHP
- **Size:** 12.94 KB
- **Capabilities:**
  - File Tool - Safe File Operations & Analysis
  - Execute
  - Validate Path
- **Methods:** 10

#### MySQLQueryTool
- **File:** `mcp/src/Tools/MySQLQueryTool.php`
- **Type:** PHP
- **Size:** 5.85 KB
- **Capabilities:**
  - MySQL Query Tool
  - Execute a safe read-only query
  - Format results based on requested format
- **Methods:** 5

#### WebBrowserTool
- **File:** `mcp/src/Tools/WebBrowserTool.php`
- **Type:** PHP
- **Size:** 9.71 KB
- **Capabilities:**
  - Web Browser Tool
  - Execute web browsing operations
  - Fetch a web page
- **Methods:** 7

#### HealthCheckTool
- **File:** `mcp/src/Tools/HealthCheckTool.php`
- **Type:** PHP
- **Size:** 7.59 KB
- **Capabilities:**
  - Health Check Tool - Unified system health monitoring
  - Execute health check
  - Check database health
- **Methods:** 9

#### SemanticSearchTool
- **File:** `mcp/src/Tools/SemanticSearchTool.php`
- **Type:** PHP
- **Size:** 6.88 KB
- **Capabilities:**
  - Semantic Search Tool - v4.0
  - Execute semantic search with fuzzy matching and analytics
  - Get real client IP address
- **Methods:** 4

#### CrawlerTool
- **File:** `mcp/src/Tools/CrawlerTool.php`
- **Type:** PHP
- **Size:** 12.37 KB
- **Capabilities:**
  - CrawlerTool - Comprehensive Web Crawler
  - Execute crawler
  - Build command arguments based on mode
- **Methods:** 7

#### tools_impl
- **File:** `mcp/tools_impl.php`
- **Type:** PHP
- **Size:** 11.7 KB
- **Capabilities:**
  - MCP Tool Implementations - Part 2
  - SEMANTIC SEARCH - Natural language search across all content
  - FIND CODE - Precise code pattern matching
- **Methods:** 5

#### advanced_tools
- **File:** `mcp/advanced_tools.php`
- **Type:** PHP
- **Size:** 14.58 KB
- **Capabilities:**
  - MCP Advanced Tools
  - Suggest implementation based on existing patterns
  - Analyze impact of changing a file
- **Methods:** 8

---

### Automation (21 tools)

#### deploy-smart-cron-hub
- **File:** `_automation/active/deploy-smart-cron-hub.sh`
- **Type:** Shell
- **Size:** 7.49 KB
- **Capabilities:**
  - Smart-Cron Central Hub - Main Executable

#### deploy-multibot-to-cis
- **File:** `_automation/active/deploy-multibot-to-cis.sh`
- **Type:** Shell
- **Size:** 6.68 KB
- **Capabilities:**
  - Multi-Bot Conversation Manager
- **Methods:** 7

#### ai-monitor
- **File:** `_automation/active/ai-monitor.sh`
- **Type:** Shell
- **Size:** 5.82 KB
- **Methods:** 1

#### ai-system-status
- **File:** `_automation/active/ai-system-status.sh`
- **Type:** Shell
- **Size:** 19.32 KB
- **Methods:** 2

#### copilot-command-center
- **File:** `_automation/active/copilot-command-center.sh`
- **Type:** Shell
- **Size:** 14.37 KB

#### ai-automation-manager
- **File:** `_automation/active/ai-automation-manager.sh`
- **Type:** Shell
- **Size:** 12.1 KB
- **Methods:** 2

#### harden-platform
- **File:** `_automation/active/harden-platform.sh`
- **Type:** Shell
- **Size:** 20.8 KB
- **Capabilities:**
  - CSRF Protection Service
  - Rate Limiting Service
  - Security headers automatically added by hardening script
- **Methods:** 19

#### deploy-ai-automation
- **File:** `_automation/active/deploy-ai-automation.sh`
- **Type:** Shell
- **Size:** 19.33 KB
- **Methods:** 2

#### monitor-copilot-automation
- **File:** `_automation/active/monitor-copilot-automation.sh`
- **Type:** Shell
- **Size:** 1.24 KB

#### pause-automation
- **File:** `_automation/utilities/pause-automation.sh`
- **Type:** Shell
- **Size:** 238 B

#### setup-ai-analysis
- **File:** `_automation/utilities/setup-ai-analysis.sh`
- **Type:** Shell
- **Size:** 4.33 KB

#### audit_reports
- **File:** `_automation/utilities/audit_reports.php`
- **Type:** PHP
- **Size:** 42.13 KB
- **Capabilities:**
  - Enterprise Audit Reports Dashboard
  - Generate CSRF token
  - Validate CSRF token
- **Methods:** 20

#### setup_copilot_memory
- **File:** `_automation/utilities/setup_copilot_memory.sh`
- **Type:** Shell
- **Size:** 18.22 KB
- **Capabilities:**
  - [Feature Name] API Endpoint
  - [ServiceName] - Brief description
  - Get database connection
- **Methods:** 4

#### setup-ai-cron
- **File:** `_automation/utilities/setup-ai-cron.sh`
- **Type:** Shell
- **Size:** 9.9 KB
- **Methods:** 1

#### copilot-cron-manager
- **File:** `_automation/utilities/copilot-cron-manager.php`
- **Type:** PHP
- **Size:** 9.87 KB
- **Capabilities:**
  - Automated Cron System for Universal Copilot Management
  - Install all cron jobs for copilot automation
  - Remove all copilot automation cron jobs
- **Methods:** 9

#### check_db_tables
- **File:** `_automation/utilities/check_db_tables.php`
- **Type:** PHP
- **Size:** 3.33 KB
- **Capabilities:**
  - Check Database Tables and Storage

#### smart-cron-dispatcher
- **File:** `_automation/utilities/smart-cron-dispatcher.sh`
- **Type:** Shell
- **Size:** 3.92 KB
- **Methods:** 1

#### universal-copilot-automation
- **File:** `_automation/utilities/universal-copilot-automation.php`
- **Type:** PHP
- **Size:** 57.54 KB
- **Capabilities:**
  - Universal Copilot Instructions Automation System
  - Main automation runner - executes all sync operations
  - Sync all prompt files from CIS _automation folder
- **Methods:** 20

#### broadcast-to-all-copilots
- **File:** `_automation/utilities/broadcast-to-all-copilots.sh`
- **Type:** Shell
- **Size:** 1.86 KB

#### setup-universal-copilot-automation
- **File:** `_automation/utilities/setup-universal-copilot-automation.sh`
- **Type:** Shell
- **Size:** 8.28 KB
- **Methods:** 1

#### resume-automation
- **File:** `_automation/utilities/resume-automation.sh`
- **Type:** Shell
- **Size:** 289 B

---

### Dev Tools (99 tools)

#### daily_sync_to_master
- **File:** `_dev-tools/scripts/daily_sync_to_master.sh`
- **Type:** Shell
- **Size:** 8.73 KB
- **Methods:** 1

#### kb-harvest
- **File:** `_dev-tools/scripts/kb-harvest.php`
- **Type:** PHP
- **Size:** 21.18 KB
- **Capabilities:**
  - Knowledge Base Document Harvester
  - Scan application for markdown files
  - Extract metadata from markdown file
- **Methods:** 14

#### dual_server_kb_scanner
- **File:** `_dev-tools/scripts/dual_server_kb_scanner.php`
- **Type:** PHP
- **Size:** 13.37 KB
- **Capabilities:**
  - Dual-Server KB Scanner with SSH Remote Access
  - Load central ignore configuration
  - Main scan execution
- **Methods:** 14

#### md_knowledge_consolidator
- **File:** `_dev-tools/scripts/md_knowledge_consolidator.php`
- **Type:** PHP
- **Size:** 34.81 KB
- **Capabilities:**
  - Advanced MD Knowledge Consolidator
  - Main consolidation process
  - Discover all markdown files in the project
- **Methods:** 20

#### detailed_noise_analysis
- **File:** `_dev-tools/scripts/detailed_noise_analysis.php`
- **Type:** PHP
- **Size:** 3.71 KB

#### safe_neural_scanner
- **File:** `_dev-tools/scripts/safe_neural_scanner.php`
- **Type:** PHP
- **Size:** 10.68 KB
- **Capabilities:**
  - Safe Neural Scanner - Production Servers Only
  - Scan
  - Scan Directory
- **Methods:** 10

#### check_table_structure
- **File:** `_dev-tools/scripts/check_table_structure.php`
- **Type:** PHP
- **Size:** 1.89 KB
- **Capabilities:**
  - Quick Database Table Check

#### install-kb-smart-cron
- **File:** `_dev-tools/scripts/install-kb-smart-cron.php`
- **Type:** PHP
- **Size:** 11.21 KB
- **Capabilities:**
  - KB Auto-Sync Smart Cron Integration Installer
- **Methods:** 1

#### kb_proactive_indexer
- **File:** `_dev-tools/scripts/kb_proactive_indexer.php`
- **Type:** PHP
- **Size:** 18.31 KB
- **Capabilities:**
  - CIS Intelligence - Proactive Knowledge Base Indexer
  - Scan For Changes
- **Methods:** 9

#### kb_md_collector
- **File:** `_dev-tools/scripts/kb_md_collector.php`
- **Type:** PHP
- **Size:** 12.93 KB
- **Capabilities:**
  - CIS Intelligence - Markdown File Collector
- **Methods:** 6

#### ultra_tight_db_update
- **File:** `_dev-tools/scripts/ultra_tight_db_update.php`
- **Type:** PHP
- **Size:** 15.69 KB
- **Capabilities:**
  - ULTRA TIGHT KB DATABASE UPDATE - ZERO NOISE
- **Methods:** 2

#### kb_content_analyzer
- **File:** `_dev-tools/scripts/kb_content_analyzer.php`
- **Type:** PHP
- **Size:** 13.61 KB
- **Capabilities:**
  - CIS KB - Enhanced Content Analysis & Versioning System
  - Analyze file content for relevance, quality, and decay
  - Calculate relevance score (0-100)
- **Methods:** 15

#### status
- **File:** `_dev-tools/scripts/status.php`
- **Type:** PHP
- **Size:** 2.57 KB
- **Capabilities:**
  - CIS Intelligence - Quick Status Check

#### enhanced_security_scanner
- **File:** `_dev-tools/scripts/enhanced_security_scanner.php`
- **Type:** PHP
- **Size:** 21.17 KB
- **Capabilities:**
  - Enhanced Security Scanner v2.0
  - Scan all PHP files for vulnerabilities
  - Scan single file for vulnerabilities
- **Methods:** 12

#### local_neural_scanner
- **File:** `_dev-tools/scripts/local_neural_scanner.php`
- **Type:** PHP
- **Size:** 19.08 KB
- **Capabilities:**
  - Neural Scanner - Local Server Version
  - Run Neural Scan
  - Scan Client Server
- **Methods:** 20

#### build-kb-html
- **File:** `_dev-tools/scripts/build-kb-html.php`
- **Type:** PHP
- **Size:** 9.21 KB
- **Capabilities:**
  - Knowledge Base Static HTML Generator
- **Methods:** 5

#### copilot_activity_integration
- **File:** `_dev-tools/scripts/copilot_activity_integration.php`
- **Type:** PHP
- **Size:** 1.16 KB
- **Capabilities:**
  - Copilot Activity Integration
  - Trigger K B Scan From Copilot
- **Methods:** 3

#### error_proof_content_analyzer
- **File:** `_dev-tools/scripts/error_proof_content_analyzer.php`
- **Type:** PHP
- **Size:** 13.67 KB
- **Capabilities:**
  - FLAWLESS KB CONTENT ANALYZER - ERROR-PROOF VERSION
- **Methods:** 1

#### fix_database_encoding
- **File:** `_dev-tools/scripts/fix_database_encoding.php`
- **Type:** PHP
- **Size:** 3.72 KB
- **Capabilities:**
  - DATABASE UTF8MB4 ENCODING FIX

#### proper_kb_scanner
- **File:** `_dev-tools/scripts/proper_kb_scanner.php`
- **Type:** PHP
- **Size:** 6.2 KB
- **Capabilities:**
  - Proper KB Scanner with Central Ignore Config

#### content_intelligence_processor
- **File:** `_dev-tools/scripts/content_intelligence_processor.php`
- **Type:** PHP
- **Size:** 10.67 KB
- **Capabilities:**
  - JOB 8: Content Intelligence Processor
  - Calculate intelligence metrics for a file
  - Store metrics in intelligence_metrics table
- **Methods:** 6

#### master_index_generator
- **File:** `_dev-tools/scripts/master_index_generator.php`
- **Type:** PHP
- **Size:** 19.25 KB
- **Capabilities:**
  - Master Index Generator
  - Check if path should be skipped
  - Scan all .md files recursively
- **Methods:** 14

#### comprehensive_md_scanner
- **File:** `_dev-tools/scripts/comprehensive_md_scanner.php`
- **Type:** PHP
- **Size:** 22.69 KB
- **Capabilities:**
  - Comprehensive MD Knowledge Scanner - CENTRALIZED CONFIG VERSION
  - Comprehensive scan of ALL MD files without limits
  - Find ALL MD files using centralized configuration
- **Methods:** 20

#### clean_db_update
- **File:** `_dev-tools/scripts/clean_db_update.php`
- **Type:** PHP
- **Size:** 11.44 KB
- **Capabilities:**
  - CLEAN KB DATABASE UPDATE - NO UNICODE ERRORS
- **Methods:** 2

#### user_activity_tracker
- **File:** `_dev-tools/scripts/user_activity_tracker.php`
- **Type:** PHP
- **Size:** 3.43 KB
- **Capabilities:**
  - User Activity Tracker for KB System
  - Track user activity and potentially trigger KB scan
  - Record activity to log file
- **Methods:** 8

#### filtered_flawless_db_update
- **File:** `_dev-tools/scripts/filtered_flawless_db_update.php`
- **Type:** PHP
- **Size:** 13.07 KB
- **Capabilities:**
  - FLAWLESS KB DATABASE UPDATE - WITH PROPER IGNORE FILTERING

#### auto-sync-monitor
- **File:** `_dev-tools/scripts/auto-sync-monitor.php`
- **Type:** PHP
- **Size:** 16.1 KB
- **Capabilities:**
  - Auto-Sync Database Monitor
- **Methods:** 12

#### fix_scanner_ignore
- **File:** `_dev-tools/scripts/fix_scanner_ignore.php`
- **Type:** PHP
- **Size:** 3.01 KB
- **Capabilities:**
  - Fix kb_intelligence_engine_v2.php to use database ignore patterns
  - Load ignore patterns from database
- **Methods:** 2

#### comprehensive_readme_generator
- **File:** `_dev-tools/scripts/comprehensive_readme_generator.php`
- **Type:** PHP
- **Size:** 16.08 KB
- **Capabilities:**
  - Comprehensive README Generator
  - Scan Directory
- **Methods:** 12

#### standalone_auto_organizer
- **File:** `_dev-tools/scripts/standalone_auto_organizer.php`
- **Type:** PHP
- **Size:** 14.22 KB
- **Capabilities:**
  - STANDALONE KB AUTO ORGANIZER - FLAWLESS VERSION
- **Methods:** 6

#### standalone_cognitive_analysis
- **File:** `_dev-tools/scripts/standalone_cognitive_analysis.php`
- **Type:** PHP
- **Size:** 23.28 KB
- **Capabilities:**
  - STANDALONE COGNITIVE ANALYSIS ENGINE - FLAWLESS VERSION
  - Analyze Technical Patterns
- **Methods:** 7

#### DEPLOY_TO_CIS
- **File:** `_dev-tools/scripts/DEPLOY_TO_CIS.sh`
- **Type:** Shell
- **Size:** 7.05 KB
- **Capabilities:**
  - JOB 8: Content Text Processor - Extract & Analyze File Contents (CIS VERSION)

#### emergency_db_update
- **File:** `_dev-tools/scripts/emergency_db_update.php`
- **Type:** PHP
- **Size:** 10.48 KB
- **Capabilities:**
  - EMERGENCY KB DATABASE UPDATE - GET EVERYTHING WORKING NOW

#### kb_intelligence_engine
- **File:** `_dev-tools/scripts/kb_intelligence_engine.php`
- **Type:** PHP
- **Size:** 26.37 KB
- **Capabilities:**
  - KB Intelligence Engine
  - Check if path should be skipped
  - Analyze all PHP files
- **Methods:** 20

#### kb_crawler
- **File:** `_dev-tools/scripts/kb_crawler.php`
- **Type:** PHP
- **Size:** 13.3 KB
- **Capabilities:**
  - CIS Intelligence System - Knowledge Base Crawler
  - Scan Directory
- **Methods:** 11

#### kb_status
- **File:** `_dev-tools/scripts/kb_status.sh`
- **Type:** Shell
- **Size:** 5.63 KB

#### extract_all_md_to_kb
- **File:** `_dev-tools/scripts/extract_all_md_to_kb.php`
- **Type:** PHP
- **Size:** 13.87 KB
- **Capabilities:**
  - MD File Extractor - Consolidate ALL .md files to Central KB
- **Methods:** 8

#### _kb_tools_map-relationships
- **File:** `_dev-tools/scripts/_kb_tools_map-relationships.php`
- **Type:** PHP
- **Size:** 28.48 KB
- **Capabilities:**
  - CIS Knowledge Base - Relationship Mapper
- **Methods:** 20

#### kb-indexer-cron
- **File:** `_dev-tools/scripts/kb-indexer-cron.php`
- **Type:** PHP
- **Size:** 6.3 KB
- **Capabilities:**
  - Knowledge Base Auto-Indexer Cron
- **Methods:** 2

#### scan_all_applications
- **File:** `_dev-tools/scripts/scan_all_applications.php`
- **Type:** PHP
- **Size:** 19.3 KB
- **Capabilities:**
  - JOB 1: Scan All Applications - Multi-Application Intelligence Scanner
  - Load content types from intelligence_content_types table
  - Load ignore patterns from scanner_ignore_config table
- **Methods:** 8

#### analyze_noise_patterns
- **File:** `_dev-tools/scripts/analyze_noise_patterns.php`
- **Type:** PHP
- **Size:** 3.35 KB

#### smart_md_scanner
- **File:** `_dev-tools/scripts/smart_md_scanner.php`
- **Type:** PHP
- **Size:** 20.8 KB
- **Capabilities:**
  - Smart MD Knowledge Scanner & Consolidator
  - Main scanning and consolidation process
  - Find relevant MD files with smart filtering
- **Methods:** 19

#### enhance_cognitive_schema
- **File:** `_dev-tools/scripts/enhance_cognitive_schema.php`
- **Type:** PHP
- **Size:** 2.57 KB
- **Capabilities:**
  - Database Schema Enhancement for Cognitive Intelligence

#### _kb_tools_verify-kb
- **File:** `_dev-tools/scripts/_kb_tools_verify-kb.php`
- **Type:** PHP
- **Size:** 22.03 KB
- **Capabilities:**
  - CIS Knowledge Base - Verification Script
  - Check Markdown Syntax
- **Methods:** 15

#### flawless_content_analyzer
- **File:** `_dev-tools/scripts/flawless_content_analyzer.php`
- **Type:** PHP
- **Size:** 11.43 KB
- **Capabilities:**
  - KB CONTENT ANALYZER - FLAWLESS VERSION

#### emergency_db_update_corrected
- **File:** `_dev-tools/scripts/emergency_db_update_corrected.php`
- **Type:** PHP
- **Size:** 8.84 KB
- **Capabilities:**
  - EMERGENCY KB DATABASE UPDATE - CORRECTED FOR ACTUAL SCHEMA

#### live_security_monitor
- **File:** `_dev-tools/scripts/live_security_monitor.php`
- **Type:** PHP
- **Size:** 10.57 KB
- **Capabilities:**
  - JOB 5: Live Security Monitor
  - Find files modified within the scan window
  - Scan file content for security threats
- **Methods:** 5

#### detect_rogue_kb
- **File:** `_dev-tools/scripts/detect_rogue_kb.php`
- **Type:** PHP
- **Size:** 3.53 KB
- **Capabilities:**
  - Detect Rogue _kb Folders Across All Servers
- **Methods:** 2

#### kb_deep_intelligence
- **File:** `_dev-tools/scripts/kb_deep_intelligence.php`
- **Type:** PHP
- **Size:** 31.04 KB
- **Capabilities:**
  - KB Deep Intelligence Engine - EXTREME MODE
  - Check if path should be skipped
  - Scan for security vulnerabilities
- **Methods:** 20

#### _kb_tools_analyze-performance
- **File:** `_dev-tools/scripts/_kb_tools_analyze-performance.php`
- **Type:** PHP
- **Size:** 31.58 KB
- **Capabilities:**
  - CIS Knowledge Base - Performance Analyzer
  - Analyze Code Patterns
  - Analyze Memory Usage
- **Methods:** 20

#### emergency_db_update_fixed
- **File:** `_dev-tools/scripts/emergency_db_update_fixed.php`
- **Type:** PHP
- **Size:** 11.4 KB
- **Capabilities:**
  - EMERGENCY KB DATABASE UPDATE - ENCODING FIXED
- **Methods:** 2

#### kb-sdk
- **File:** `_dev-tools/scripts/kb-sdk.js`
- **Type:** JavaScript
- **Size:** 7.92 KB
- **Capabilities:**
  - KB SDK - JavaScript Client Library
  - Fetch data from KB API with caching
  - Get all KB data

#### smart_kb_scanner
- **File:** `_dev-tools/scripts/smart_kb_scanner.php`
- **Type:** PHP
- **Size:** 7.83 KB
- **Capabilities:**
  - Smart KB Scanner - Maps files to business units by application folder

#### simple_cognitive_analysis
- **File:** `_dev-tools/scripts/simple_cognitive_analysis.php`
- **Type:** PHP
- **Size:** 12.98 KB
- **Capabilities:**
  - SIMPLE COGNITIVE ANALYSIS - FLAWLESS VERSION
- **Methods:** 3

#### kb-relationship-mapper
- **File:** `_dev-tools/scripts/kb-relationship-mapper.php`
- **Type:** PHP
- **Size:** 17.09 KB
- **Capabilities:**
  - KB Relationship Mapper
  - Map all relationships across the entire codebase
  - Find all PHP files in project
- **Methods:** 20

#### super_tight_db_update
- **File:** `_dev-tools/scripts/super_tight_db_update.php`
- **Type:** PHP
- **Size:** 13.35 KB
- **Capabilities:**
  - SUPER TIGHT KB DATABASE UPDATE - ZERO NOISE

#### nuclear_kb_cleanup
- **File:** `_dev-tools/scripts/nuclear_kb_cleanup.php`
- **Type:** PHP
- **Size:** 14.2 KB
- **Capabilities:**
  - Nuclear KB Cleanup - Find and Remove ALL Rogue Files
  - Main execution
  - Find all _kb folders outside of public_html/
- **Methods:** 10

#### cognitive_content_analyzer
- **File:** `_dev-tools/scripts/cognitive_content_analyzer.php`
- **Type:** PHP
- **Size:** 25.71 KB
- **Capabilities:**
  - CIS Intelligence - Cognitive Content Analyzer
  - MAIN ANALYSIS ENGINE
  - LAYER 1: SYNTACTIC ANALYSIS
- **Methods:** 20

#### kb-auto-indexer
- **File:** `_dev-tools/scripts/kb-auto-indexer.php`
- **Type:** PHP
- **Size:** 26.26 KB
- **Capabilities:**
  - AI Knowledge Base Auto-Indexer
  - Scan File System
  - Update Stats
- **Methods:** 20

#### create_generic_kb_schema
- **File:** `_dev-tools/scripts/create_generic_kb_schema.php`
- **Type:** PHP
- **Size:** 20.4 KB
- **Capabilities:**
  - Generic Knowledge Base Schema Creator

#### neural_intelligence_scanner
- **File:** `_dev-tools/scripts/neural_intelligence_scanner.php`
- **Type:** PHP
- **Size:** 38.81 KB
- **Capabilities:**
  - Neural Scanner - Centralized Intelligence Extraction System
  - Main Neural Scanner Execution
  - Quick KB Access Tool
- **Methods:** 20

#### install-kb-cron-tasks
- **File:** `_dev-tools/scripts/install-kb-cron-tasks.sh`
- **Type:** Shell
- **Size:** 12.12 KB

#### kb-cleanup
- **File:** `_dev-tools/scripts/kb-cleanup.php`
- **Type:** PHP
- **Size:** 11.13 KB
- **Capabilities:**
  - KB Cleanup Script
- **Methods:** 10

#### kb_correlator
- **File:** `_dev-tools/scripts/kb_correlator.php`
- **Type:** PHP
- **Size:** 22.51 KB
- **Capabilities:**
  - CIS Intelligence - KB Correlator & Documentation Generator
- **Methods:** 12

#### process_content_text
- **File:** `_dev-tools/scripts/process_content_text.php`
- **Type:** PHP
- **Size:** 14.72 KB
- **Capabilities:**
  - JOB 8: Content Text Processor - Extract & Analyze File Contents
  - Process a single file
  - Extract readable text from content based on type
- **Methods:** 8

#### enhanced_kb_crawler
- **File:** `_dev-tools/scripts/enhanced_kb_crawler.php`
- **Type:** PHP
- **Size:** 22.56 KB
- **Capabilities:**
  - CIS Intelligence - Enhanced KB Multi-Crawler with Cognitive Analysis
  - Enhanced crawling with cognitive analysis
  - Crawl domain with cognitive analysis integration
- **Methods:** 14

#### create_unified_ai_intelligence_system
- **File:** `_dev-tools/scripts/create_unified_ai_intelligence_system.php`
- **Type:** PHP
- **Size:** 36.54 KB
- **Capabilities:**
  - 🧠 UNIFIED AI NEURAL INTELLIGENCE SYSTEM - IMPLEMENTATION

#### smart_kb_trigger
- **File:** `_dev-tools/scripts/smart_kb_trigger.php`
- **Type:** PHP
- **Size:** 18.14 KB
- **Capabilities:**
  - Smart KB Trigger System
  - Main trigger check - determines if KB scan should run
  - Check for file modifications since last scan
- **Methods:** 17

#### db_schema_scanner
- **File:** `_dev-tools/scripts/db_schema_scanner.php`
- **Type:** PHP
- **Size:** 13.43 KB
- **Capabilities:**
  - Database Schema Scanner
  - Scan
  - Scan Tables
- **Methods:** 10

#### audit_and_setup_crons
- **File:** `_dev-tools/scripts/audit_and_setup_crons.sh`
- **Type:** Shell
- **Size:** 15.08 KB

#### kb_search_indexer
- **File:** `_dev-tools/scripts/kb_search_indexer.php`
- **Type:** PHP
- **Size:** 15.13 KB
- **Capabilities:**
  - KB SEARCH INDEXER - FLAWLESS VERSION
- **Methods:** 3

#### flawless_db_update
- **File:** `_dev-tools/scripts/flawless_db_update.php`
- **Type:** PHP
- **Size:** 10.84 KB
- **Capabilities:**
  - FLAWLESS KB DATABASE UPDATE - ZERO ERRORS

#### code_quality_scorer
- **File:** `_dev-tools/scripts/code_quality_scorer.php`
- **Type:** PHP
- **Size:** 18.33 KB
- **Capabilities:**
  - Code Quality Scoring Engine v1.0
  - Load intelligence from previous scans
  - Parse security report to extract issues by file
- **Methods:** 13

#### kb_priority_report
- **File:** `_dev-tools/scripts/kb_priority_report.php`
- **Type:** PHP
- **Size:** 10.6 KB
- **Capabilities:**
  - CIS KB - Priority & Relevance Report
- **Methods:** 5

#### scan_all_databases
- **File:** `_dev-tools/scripts/scan_all_databases.php`
- **Type:** PHP
- **Size:** 14.85 KB
- **Capabilities:**
  - Multi-Database Schema Scanner
  - Scan All
  - Scan Database
- **Methods:** 6

#### multi_db_schema_scanner
- **File:** `_dev-tools/scripts/multi_db_schema_scanner.php`
- **Type:** PHP
- **Size:** 17.69 KB
- **Capabilities:**
  - Multi-Database Schema Scanner
  - Scan All
  - Scan Database
- **Methods:** 8

#### smart_kb_organizer
- **File:** `_dev-tools/scripts/smart_kb_organizer.php`
- **Type:** PHP
- **Size:** 20.8 KB
- **Capabilities:**
  - Smart KB Organizer - Dynamic Structure Generator
  - Check if path should be skipped
  - Detect rogue _kb folders outside public_html/_kb
- **Methods:** 15

#### kb-cli
- **File:** `_dev-tools/scripts/kb-cli.php`
- **Type:** PHP
- **Size:** 18.28 KB
- **Capabilities:**
  - Knowledge Base CLI Query Tool
- **Methods:** 3

#### verify-kb
- **File:** `_dev-tools/scripts/verify-kb.php`
- **Type:** PHP
- **Size:** 22.03 KB
- **Capabilities:**
  - CIS Knowledge Base - Verification Script
  - Check Markdown Syntax
- **Methods:** 15

#### migrate_scanner_to_new_tables
- **File:** `_dev-tools/scripts/migrate_scanner_to_new_tables.php`
- **Type:** PHP
- **Size:** 10.2 KB
- **Capabilities:**
  - Migrate kb_intelligence_engine_v2.php to use NEW table structure
  - Initialize database connection
  - Load content types from database
- **Methods:** 6

#### kb_multi_crawler
- **File:** `_dev-tools/scripts/kb_multi_crawler.php`
- **Type:** PHP
- **Size:** 30.17 KB
- **Capabilities:**
  - CIS Intelligence - Multi-Domain Knowledge Base Crawler
  - Get full file content with size limits and encoding handling
  - Calculate file priority based on location, content, and context
- **Methods:** 18

#### install-cron
- **File:** `_dev-tools/scripts/install-cron.sh`
- **Type:** Shell
- **Size:** 9.97 KB
- **Methods:** 1

#### simple_neural_setup
- **File:** `_dev-tools/scripts/simple_neural_setup.php`
- **Type:** PHP
- **Size:** 8.12 KB
- **Capabilities:**
  - Simple Neural Scanner - Functional Implementation

#### kb_deployment_system
- **File:** `_dev-tools/scripts/kb_deployment_system.php`
- **Type:** PHP
- **Size:** 16.79 KB
- **Capabilities:**
  - STANDALONE KB DEPLOYMENT SYSTEM - FLAWLESS VERSION

#### quick_intelligence_scan
- **File:** `_dev-tools/scripts/quick_intelligence_scan.php`
- **Type:** PHP
- **Size:** 7.72 KB
- **Capabilities:**
  - Quick Intelligence Scan - Production Ready
  - Scan Directory
- **Methods:** 1

#### kb_intelligence_engine_v2
- **File:** `_dev-tools/scripts/kb_intelligence_engine_v2.php`
- **Type:** PHP
- **Size:** 22.64 KB
- **Capabilities:**
  - Enhanced KB Intelligence Engine v2.0
  - Initialize database connection
  - Load content types from database
- **Methods:** 20

#### quick_kb_scan
- **File:** `_dev-tools/scripts/quick_kb_scan.php`
- **Type:** PHP
- **Size:** 7.21 KB
- **Capabilities:**
  - Quick KB Scanner - Immediate progress display

#### kb-readme-generator
- **File:** `_dev-tools/scripts/kb-readme-generator.php`
- **Type:** PHP
- **Size:** 18.9 KB
- **Capabilities:**
  - KB README Generator
  - Generate README.md in every directory
  - Find all directories in project
- **Methods:** 19

#### quality_control_system
- **File:** `_dev-tools/scripts/quality_control_system.php`
- **Type:** PHP
- **Size:** 20.48 KB
- **Capabilities:**
  - STANDALONE QUALITY CONTROL SYSTEM - FLAWLESS VERSION
  - Analyze Content Quality
  - Analyze Structure Quality
- **Methods:** 7

#### simple_search_indexer
- **File:** `_dev-tools/scripts/simple_search_indexer.php`
- **Type:** PHP
- **Size:** 6.92 KB
- **Capabilities:**
  - SIMPLE KB SEARCH INDEXER - FLAWLESS VERSION
- **Methods:** 2

#### kb-sdk
- **File:** `_dev-tools/scripts/kb-sdk.php`
- **Type:** PHP
- **Size:** 8.24 KB
- **Capabilities:**
  - KB SDK - PHP Client Library
  - Fetch data from KB API with caching
  - Get all KB data
- **Methods:** 14

#### simple_quality_control
- **File:** `_dev-tools/scripts/simple_quality_control.php`
- **Type:** PHP
- **Size:** 10.28 KB
- **Capabilities:**
  - SIMPLE QUALITY CONTROL SYSTEM - FLAWLESS VERSION
  - Check Documentation
  - Check Structure
- **Methods:** 5

#### deploy_lightweight_kb_full_production
- **File:** `_dev-tools/scripts/deploy_lightweight_kb_full_production.sh`
- **Type:** Shell
- **Size:** 20.12 KB
- **Capabilities:**
  - Lightweight KB Bot Command Handler
- **Methods:** 16

#### install
- **File:** `_dev-tools/database/install.sh`
- **Type:** Shell
- **Size:** 33.24 KB

#### hardened-security-audit
- **File:** `_dev-tools/tools/hardened-security-audit.php`
- **Type:** PHP
- **Size:** 31.79 KB
- **Capabilities:**
  - Intelligence Hub - Comprehensive Platform Security Audit
  - Test Integration Points
  - Test Error Handling
- **Methods:** 20

#### platform-security-audit
- **File:** `_dev-tools/tools/platform-security-audit.php`
- **Type:** PHP
- **Size:** 28.88 KB
- **Capabilities:**
  - Intelligence Hub Platform Security Audit Tool
- **Methods:** 20

#### ai-activity-analyzer
- **File:** `_dev-tools/analysis/ai-activity-analyzer.php`
- **Type:** PHP
- **Size:** 23.03 KB
- **Capabilities:**
  - AI-Powered Activity Analysis and Change Detection System
  - Main analysis function - called every 3 minutes
  - Detect recent file changes across all projects
- **Methods:** 20

#### intelligence_control_panel
- **File:** `_dev-tools/analysis/intelligence_control_panel.php`
- **Type:** PHP
- **Size:** 40.64 KB
- **Capabilities:**
  - Intelligence Control Panel V2 - Enhanced Dashboard
  - Trigger Scan
- **Methods:** 13

#### ai-batch-processor
- **File:** `_dev-tools/analysis/ai-batch-processor.php`
- **Type:** PHP
- **Size:** 28.09 KB
- **Capabilities:**
  - AI Batch Processor with Budget Control and Usage Tracking
  - Main batch processing function
  - Detect recent file changes (VS Code saves, FTP uploads)
- **Methods:** 20

---

### Frontend (3302 tools)

#### bot-summary-generator
- **File:** `frontend-tools/scripts/bot-summary-generator.js`
- **Type:** JavaScript
- **Size:** 13.94 KB
- **Capabilities:**
  - Bot Summary Generator for Frontend Tools
  - Generate comprehensive bot summary from crawl data
  - Load main crawl data from full_crawl_data.json
- **Methods:** 1

#### gpt-vision-analyzer
- **File:** `frontend-tools/scripts/gpt-vision-analyzer.js`
- **Type:** JavaScript
- **Size:** 13.61 KB
- **Capabilities:**
  - GPT Vision UI Analyzer
  - Parse viewport argument
  - Capture screenshot with Puppeteer
- **Methods:** 5

#### auth-manager
- **File:** `frontend-tools/scripts/auth-manager.js`
- **Type:** JavaScript
- **Size:** 20.19 KB
- **Capabilities:**
  - Authentication Manager for Frontend Tools
  - Initialize default CIS Robot profiles
  - Save authentication profile with encryption
- **Methods:** 1

#### deep-crawler
- **File:** `frontend-tools/scripts/deep-crawler.js`
- **Type:** JavaScript
- **Size:** 38.38 KB
- **Capabilities:**
  - Deep Web Crawler & Debugger
  - Parse viewport argument
  - Setup page monitoring - captures EVERYTHING
- **Methods:** 14

#### business-unit-manager
- **File:** `frontend-tools/scripts/business-unit-manager.js`
- **Type:** JavaScript
- **Size:** 24.29 KB
- **Capabilities:**
  - Business Unit Manager
  - Detect business unit from URL
  - Get storage path for business unit

#### setup-profiles
- **File:** `frontend-tools/scripts/setup-profiles.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - Frontend Tools Profile Setup
- **Methods:** 1

#### quick-page-audit
- **File:** `frontend-tools/scripts/quick-page-audit.js`
- **Type:** JavaScript
- **Size:** 34.2 KB
- **Capabilities:**
  - Quick Page Audit Tool
- **Methods:** 1

#### crawl-staff-portal
- **File:** `frontend-tools/scripts/crawl-staff-portal.js`
- **Type:** JavaScript
- **Size:** 25.87 KB
- **Capabilities:**
  - Staff Portal Crawler with Bot Detection Bypass
  - Setup page monitoring (console, errors, network)
  - Apply stealth techniques to bypass bot detection
- **Methods:** 10

#### html-report-generator
- **File:** `frontend-tools/scripts/html-report-generator.js`
- **Type:** JavaScript
- **Size:** 22.75 KB
- **Capabilities:**
  - HTML Report Generator
  - Generate HTML report from JSON
  - Save HTML report to file
- **Methods:** 1

#### interactive-crawler
- **File:** `frontend-tools/scripts/interactive-crawler.js`
- **Type:** JavaScript
- **Size:** 14.75 KB
- **Capabilities:**
  - Interactive Staff Portal Crawler with Real-Time Control
  - Log message with timestamp
  - Capture screenshot
- **Methods:** 7

#### index
- **File:** `frontend-tools/node_modules/ansi-colors/index.js`
- **Type:** JavaScript
- **Size:** 5.77 KB

#### index
- **File:** `frontend-tools/node_modules/tr46/index.js`
- **Type:** JavaScript
- **Size:** 7.39 KB
- **Capabilities:**
  - Validate Label
- **Methods:** 6

#### tslib.es6
- **File:** `frontend-tools/node_modules/intl-messageformat/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/intl-messageformat/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/intl-messageformat/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### index
- **File:** `frontend-tools/node_modules/intl-messageformat/index.js`
- **Type:** JavaScript
- **Size:** 675 B

#### intl-messageformat.esm
- **File:** `frontend-tools/node_modules/intl-messageformat/intl-messageformat.esm.js`
- **Type:** JavaScript
- **Size:** 79.73 KB
- **Capabilities:**
  - Original message we're trying to format
- **Methods:** 20

#### intl-messageformat.iife
- **File:** `frontend-tools/node_modules/intl-messageformat/intl-messageformat.iife.js`
- **Type:** JavaScript
- **Size:** 89.35 KB
- **Capabilities:**
  - Original message we're trying to format
- **Methods:** 20

#### core
- **File:** `frontend-tools/node_modules/intl-messageformat/lib/src/core.js`
- **Type:** JavaScript
- **Size:** 8.31 KB
- **Methods:** 5

#### error
- **File:** `frontend-tools/node_modules/intl-messageformat/lib/src/error.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Methods:** 4

#### formatters
- **File:** `frontend-tools/node_modules/intl-messageformat/lib/src/formatters.js`
- **Type:** JavaScript
- **Size:** 6.86 KB
- **Methods:** 3

#### core
- **File:** `frontend-tools/node_modules/intl-messageformat/src/core.js`
- **Type:** JavaScript
- **Size:** 8.67 KB
- **Methods:** 5

#### error
- **File:** `frontend-tools/node_modules/intl-messageformat/src/error.js`
- **Type:** JavaScript
- **Size:** 2.55 KB
- **Methods:** 4

#### formatters
- **File:** `frontend-tools/node_modules/intl-messageformat/src/formatters.js`
- **Type:** JavaScript
- **Size:** 7.27 KB
- **Methods:** 3

#### ezoic
- **File:** `frontend-tools/node_modules/lighthouse-stack-packs/packs/ezoic.js`
- **Type:** JavaScript
- **Size:** 6.62 KB

#### octobercms
- **File:** `frontend-tools/node_modules/lighthouse-stack-packs/packs/octobercms.js`
- **Type:** JavaScript
- **Size:** 10.07 KB

#### joomla
- **File:** `frontend-tools/node_modules/lighthouse-stack-packs/packs/joomla.js`
- **Type:** JavaScript
- **Size:** 10.36 KB

#### wordpress
- **File:** `frontend-tools/node_modules/lighthouse-stack-packs/packs/wordpress.js`
- **Type:** JavaScript
- **Size:** 9.57 KB

#### decimal
- **File:** `frontend-tools/node_modules/decimal.js/decimal.js`
- **Type:** JavaScript
- **Size:** 133.47 KB
- **Capabilities:**
  - Check Int32
  - Check Rounding Digits
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/ansi-styles/index.js`
- **Type:** JavaScript
- **Size:** 4.04 KB
- **Methods:** 1

#### axios.min
- **File:** `frontend-tools/node_modules/axios/dist/axios.min.js`
- **Type:** JavaScript
- **Size:** 53.65 KB
- **Methods:** 20

#### axios
- **File:** `frontend-tools/node_modules/axios/dist/axios.js`
- **Type:** JavaScript
- **Size:** 145.24 KB
- **Capabilities:**
  - Determine if a value is an Array
  - Determine if a value is undefined
  - Determine if a value is a Buffer
- **Methods:** 20

#### axios.min
- **File:** `frontend-tools/node_modules/axios/dist/esm/axios.min.js`
- **Type:** JavaScript
- **Size:** 35.3 KB
- **Methods:** 20

#### axios
- **File:** `frontend-tools/node_modules/axios/dist/esm/axios.js`
- **Type:** JavaScript
- **Size:** 98.78 KB
- **Capabilities:**
  - Determine if a value is an Array
  - Determine if a value is undefined
  - Determine if a value is a Buffer
- **Methods:** 20

#### CanceledError
- **File:** `frontend-tools/node_modules/axios/lib/cancel/CanceledError.js`
- **Type:** JavaScript
- **Size:** 697 B
- **Capabilities:**
  - A `CanceledError` is an object that is thrown when an operation is canceled.
- **Methods:** 1

#### CancelToken
- **File:** `frontend-tools/node_modules/axios/lib/cancel/CancelToken.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - A `CancelToken` is an object that can be used to request cancellation of an operation.
  - Throws a `CanceledError` if cancellation has been requested.
  - Subscribe to the cancel signal
- **Methods:** 5

#### isCancel
- **File:** `frontend-tools/node_modules/axios/lib/cancel/isCancel.js`
- **Type:** JavaScript
- **Size:** 99 B
- **Capabilities:**
  - Is Cancel
- **Methods:** 1

#### utils
- **File:** `frontend-tools/node_modules/axios/lib/platform/common/utils.js`
- **Type:** JavaScript
- **Size:** 1.56 KB
- **Capabilities:**
  - Determine if we're running in a standard browser environment
  - Determine if we're running in a standard browser webWorker environment

#### index
- **File:** `frontend-tools/node_modules/axios/lib/platform/node/index.js`
- **Type:** JavaScript
- **Size:** 828 B

#### index
- **File:** `frontend-tools/node_modules/axios/lib/platform/browser/index.js`
- **Type:** JavaScript
- **Size:** 305 B

#### http
- **File:** `frontend-tools/node_modules/axios/lib/adapters/http.js`
- **Type:** JavaScript
- **Size:** 22.72 KB
- **Capabilities:**
  - If the proxy or config beforeRedirects functions are defined, call them with the options
  - If the proxy or config afterRedirects functions are defined, call them with the options
- **Methods:** 14

#### fetch
- **File:** `frontend-tools/node_modules/axios/lib/adapters/fetch.js`
- **Type:** JavaScript
- **Size:** 7.97 KB

#### xhr
- **File:** `frontend-tools/node_modules/axios/lib/adapters/xhr.js`
- **Type:** JavaScript
- **Size:** 6.72 KB
- **Methods:** 10

#### adapters
- **File:** `frontend-tools/node_modules/axios/lib/adapters/adapters.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### axios
- **File:** `frontend-tools/node_modules/axios/lib/axios.js`
- **Type:** JavaScript
- **Size:** 2.49 KB
- **Capabilities:**
  - Create an instance of Axios
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/axios/lib/defaults/index.js`
- **Type:** JavaScript
- **Size:** 4.39 KB
- **Capabilities:**
  - It takes a string, tries to parse it, and if it fails, it returns the stringified version
  - A timeout in milliseconds to abort a request. If set to 0 (default) a
  - Validate Status
- **Methods:** 5

#### utils
- **File:** `frontend-tools/node_modules/axios/lib/utils.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Capabilities:**
  - Determine if a value is an Array
  - Determine if a value is undefined
  - Determine if a value is a Buffer
- **Methods:** 12

#### bind
- **File:** `frontend-tools/node_modules/axios/lib/helpers/bind.js`
- **Type:** JavaScript
- **Size:** 134 B
- **Methods:** 2

#### AxiosTransformStream
- **File:** `frontend-tools/node_modules/axios/lib/helpers/AxiosTransformStream.js`
- **Type:** JavaScript
- **Size:** 3.59 KB
- **Methods:** 1

#### speedometer
- **File:** `frontend-tools/node_modules/axios/lib/helpers/speedometer.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - Calculate data maxRate
- **Methods:** 2

#### parseHeaders
- **File:** `frontend-tools/node_modules/axios/lib/helpers/parseHeaders.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - Parse headers into an object
- **Methods:** 1

#### buildURL
- **File:** `frontend-tools/node_modules/axios/lib/helpers/buildURL.js`
- **Type:** JavaScript
- **Size:** 1.57 KB
- **Capabilities:**
  - It replaces all instances of the characters `:`, `$`, `,`, `+`, `[`, and `]` with their
  - Build a URL by appending params to the end
- **Methods:** 2

#### callbackify
- **File:** `frontend-tools/node_modules/axios/lib/helpers/callbackify.js`
- **Type:** JavaScript
- **Size:** 372 B

#### parseProtocol
- **File:** `frontend-tools/node_modules/axios/lib/helpers/parseProtocol.js`
- **Type:** JavaScript
- **Size:** 151 B
- **Methods:** 1

#### throttle
- **File:** `frontend-tools/node_modules/axios/lib/helpers/throttle.js`
- **Type:** JavaScript
- **Size:** 852 B
- **Capabilities:**
  - Throttle decorator
- **Methods:** 1

#### formDataToStream
- **File:** `frontend-tools/node_modules/axios/lib/helpers/formDataToStream.js`
- **Type:** JavaScript
- **Size:** 2.9 KB

#### toURLEncodedForm
- **File:** `frontend-tools/node_modules/axios/lib/helpers/toURLEncodedForm.js`
- **Type:** JavaScript
- **Size:** 540 B
- **Methods:** 1

#### composeSignals
- **File:** `frontend-tools/node_modules/axios/lib/helpers/composeSignals.js`
- **Type:** JavaScript
- **Size:** 1.33 KB

#### deprecatedMethod
- **File:** `frontend-tools/node_modules/axios/lib/helpers/deprecatedMethod.js`
- **Type:** JavaScript
- **Size:** 746 B
- **Capabilities:**
  - Supply a warning to the developer that a method they are using
- **Methods:** 1

#### toFormData
- **File:** `frontend-tools/node_modules/axios/lib/helpers/toFormData.js`
- **Type:** JavaScript
- **Size:** 5.97 KB
- **Capabilities:**
  - Determines if the given thing is a array or js object.
  - It removes the brackets from the end of a string
  - It takes a path, a key, and a boolean, and returns a string
- **Methods:** 11

#### readBlob
- **File:** `frontend-tools/node_modules/axios/lib/helpers/readBlob.js`
- **Type:** JavaScript
- **Size:** 318 B

#### estimateDataURLDecodedBytes
- **File:** `frontend-tools/node_modules/axios/lib/helpers/estimateDataURLDecodedBytes.js`
- **Type:** JavaScript
- **Size:** 2.01 KB
- **Capabilities:**
  - Estimate decoded byte length of a data:// URL *without* allocating large buffers.
- **Methods:** 1

#### resolveConfig
- **File:** `frontend-tools/node_modules/axios/lib/helpers/resolveConfig.js`
- **Type:** JavaScript
- **Size:** 2.15 KB

#### ZlibHeaderTransformStream
- **File:** `frontend-tools/node_modules/axios/lib/helpers/ZlibHeaderTransformStream.js`
- **Type:** JavaScript
- **Size:** 681 B

#### isAbsoluteURL
- **File:** `frontend-tools/node_modules/axios/lib/helpers/isAbsoluteURL.js`
- **Type:** JavaScript
- **Size:** 561 B
- **Capabilities:**
  - Determines whether the specified URL is absolute
- **Methods:** 1

#### formDataToJSON
- **File:** `frontend-tools/node_modules/axios/lib/helpers/formDataToJSON.js`
- **Type:** JavaScript
- **Size:** 2.11 KB
- **Capabilities:**
  - It takes a string like `foo[x][y][z]` and returns an array like `['foo', 'x', 'y', 'z']
  - Convert an array to an object.
  - It takes a FormData object and returns a JavaScript object
- **Methods:** 4

#### combineURLs
- **File:** `frontend-tools/node_modules/axios/lib/helpers/combineURLs.js`
- **Type:** JavaScript
- **Size:** 382 B
- **Capabilities:**
  - Creates a new URL by combining the specified URLs
- **Methods:** 1

#### isAxiosError
- **File:** `frontend-tools/node_modules/axios/lib/helpers/isAxiosError.js`
- **Type:** JavaScript
- **Size:** 373 B
- **Capabilities:**
  - Determines whether the payload is an error thrown by Axios
- **Methods:** 1

#### AxiosURLSearchParams
- **File:** `frontend-tools/node_modules/axios/lib/helpers/AxiosURLSearchParams.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - It encodes a string by replacing all characters that are not in the unreserved set with
  - It takes a params object and converts it to a FormData object
- **Methods:** 6

#### validator
- **File:** `frontend-tools/node_modules/axios/lib/helpers/validator.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Transitional option validator
  - Assert object's properties type
- **Methods:** 5

#### fromDataURI
- **File:** `frontend-tools/node_modules/axios/lib/helpers/fromDataURI.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Parse data uri to a Buffer or Blob
- **Methods:** 1

#### spread
- **File:** `frontend-tools/node_modules/axios/lib/helpers/spread.js`
- **Type:** JavaScript
- **Size:** 564 B
- **Capabilities:**
  - Syntactic sugar for invoking a function and expanding an array for arguments.
- **Methods:** 4

#### trackStream
- **File:** `frontend-tools/node_modules/axios/lib/helpers/trackStream.js`
- **Type:** JavaScript
- **Size:** 1.65 KB

#### InterceptorManager
- **File:** `frontend-tools/node_modules/axios/lib/core/InterceptorManager.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Capabilities:**
  - Add a new interceptor to the stack
  - Remove an interceptor from the stack
  - Clear all interceptors from the stack
- **Methods:** 2

#### dispatchRequest
- **File:** `frontend-tools/node_modules/axios/lib/core/dispatchRequest.js`
- **Type:** JavaScript
- **Size:** 2.14 KB
- **Capabilities:**
  - Throws a `CanceledError` if cancellation has been requested.
  - Dispatch a request to the server using the configured adapter.
- **Methods:** 4

#### mergeConfig
- **File:** `frontend-tools/node_modules/axios/lib/core/mergeConfig.js`
- **Type:** JavaScript
- **Size:** 3.32 KB
- **Capabilities:**
  - Config-specific merge-function which creates a new config-object
- **Methods:** 8

#### AxiosHeaders
- **File:** `frontend-tools/node_modules/axios/lib/core/AxiosHeaders.js`
- **Type:** JavaScript
- **Size:** 7.22 KB
- **Methods:** 9

#### AxiosError
- **File:** `frontend-tools/node_modules/axios/lib/core/AxiosError.js`
- **Type:** JavaScript
- **Size:** 2.9 KB
- **Capabilities:**
  - Create an Error with the specified message, config, error code, request and response.
- **Methods:** 3

#### transformData
- **File:** `frontend-tools/node_modules/axios/lib/core/transformData.js`
- **Type:** JavaScript
- **Size:** 778 B
- **Capabilities:**
  - Transform the data for a request or a response
- **Methods:** 3

#### settle
- **File:** `frontend-tools/node_modules/axios/lib/core/settle.js`
- **Type:** JavaScript
- **Size:** 836 B
- **Capabilities:**
  - Resolve or reject a Promise based on response status.
- **Methods:** 2

#### Axios
- **File:** `frontend-tools/node_modules/axios/lib/core/Axios.js`
- **Type:** JavaScript
- **Size:** 6.68 KB
- **Capabilities:**
  - Create a new instance of Axios
  - Dispatch a request
- **Methods:** 6

#### buildFullPath
- **File:** `frontend-tools/node_modules/axios/lib/core/buildFullPath.js`
- **Type:** JavaScript
- **Size:** 783 B
- **Capabilities:**
  - Creates a new URL by combining the baseURL with the requestedURL,
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/is-arrayish/index.js`
- **Type:** JavaScript
- **Size:** 204 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/robots-parser/index.js`
- **Type:** JavaScript
- **Size:** 116 B

#### Robots
- **File:** `frontend-tools/node_modules/robots-parser/test/Robots.js`
- **Type:** JavaScript
- **Size:** 22.32 KB
- **Capabilities:**
  - Test Robots
- **Methods:** 1

#### Robots
- **File:** `frontend-tools/node_modules/robots-parser/Robots.js`
- **Type:** JavaScript
- **Size:** 10.38 KB
- **Capabilities:**
  - Trims the white space from the start and end of the line.
  - Remove comments from lines
  - Splits a line at the first occurrence of :
- **Methods:** 11

#### http
- **File:** `frontend-tools/node_modules/get-uri/dist/http.js`
- **Type:** JavaScript
- **Size:** 7.08 KB
- **Capabilities:**
  - Returns a Readable stream from an "http:" URI.
  - Returns `true` if the provided cache's "freshness" is valid. That is, either
  - Attempts to return a previous Response object from a previous GET call to the
- **Methods:** 2

#### notfound
- **File:** `frontend-tools/node_modules/get-uri/dist/notfound.js`
- **Type:** JavaScript
- **Size:** 499 B
- **Capabilities:**
  - Error subclass to use when the source does not exist at the specified endpoint.

#### data
- **File:** `frontend-tools/node_modules/get-uri/dist/data.js`
- **Type:** JavaScript
- **Size:** 1.6 KB
- **Capabilities:**
  - Returns a Readable stream from a "data:" URI.

#### file
- **File:** `frontend-tools/node_modules/get-uri/dist/file.js`
- **Type:** JavaScript
- **Size:** 2.03 KB
- **Capabilities:**
  - Returns a `fs.ReadStream` instance from a "file:" URI.
- **Methods:** 1

#### https
- **File:** `frontend-tools/node_modules/get-uri/dist/https.js`
- **Type:** JavaScript
- **Size:** 544 B
- **Capabilities:**
  - Returns a Readable stream from an "https:" URI.

#### ftp
- **File:** `frontend-tools/node_modules/get-uri/dist/ftp.js`
- **Type:** JavaScript
- **Size:** 3.12 KB
- **Capabilities:**
  - Returns a Readable stream from an "ftp:" URI.
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/get-uri/dist/index.js`
- **Type:** JavaScript
- **Size:** 2.09 KB
- **Capabilities:**
  - Async function that returns a `stream.Readable` instance that will output
- **Methods:** 3

#### http-error
- **File:** `frontend-tools/node_modules/get-uri/dist/http-error.js`
- **Type:** JavaScript
- **Size:** 506 B
- **Capabilities:**
  - Error subclass to use when an HTTP application error has occurred.

#### notmodified
- **File:** `frontend-tools/node_modules/get-uri/dist/notmodified.js`
- **Type:** JavaScript
- **Size:** 538 B
- **Capabilities:**
  - Error subclass to use when the source has not been modified.

#### get
- **File:** `frontend-tools/node_modules/dunder-proto/test/get.js`
- **Type:** JavaScript
- **Size:** 1.12 KB

#### set
- **File:** `frontend-tools/node_modules/dunder-proto/test/set.js`
- **Type:** JavaScript
- **Size:** 1.66 KB

#### get
- **File:** `frontend-tools/node_modules/dunder-proto/get.js`
- **Type:** JavaScript
- **Size:** 980 B
- **Methods:** 1

#### set
- **File:** `frontend-tools/node_modules/dunder-proto/set.js`
- **Type:** JavaScript
- **Size:** 1.25 KB
- **Methods:** 1

#### domain
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/domain.js`
- **Type:** JavaScript
- **Size:** 3.15 KB
- **Capabilities:**
  - Check if `vhost` is a valid suffix of `hostname` (top-domain)
  - Given a hostname and its public suffix, extract the general domain.
  - Detects the domain based on rules and upon and a host string
- **Methods:** 3

#### subdomain
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/subdomain.js`
- **Type:** JavaScript
- **Size:** 346 B
- **Capabilities:**
  - Returns the subdomain of a hostname string
- **Methods:** 1

#### extract-hostname
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/extract-hostname.js`
- **Type:** JavaScript
- **Size:** 5.44 KB
- **Methods:** 1

#### options
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/options.js`
- **Type:** JavaScript
- **Size:** 641 B
- **Methods:** 2

#### is-ip
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/is-ip.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - Check if a hostname is an IP. You should be aware that this only works
  - Similar to isProbablyIpv4.
  - Check if `hostname` is *probably* a valid ip addr (either ipv6 or ipv4).
- **Methods:** 3

#### fast-path
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/lookup/fast-path.js`
- **Type:** JavaScript
- **Size:** 2.2 KB

#### factory
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/factory.js`
- **Type:** JavaScript
- **Size:** 3.65 KB
- **Capabilities:**
  - Implement a factory allowing to plug different implementations of suffix
- **Methods:** 3

#### is-valid
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/is-valid.js`
- **Type:** JavaScript
- **Size:** 2.4 KB
- **Capabilities:**
  - Implements fast shallow verification of hostnames. This does not perform a
  - Check if a hostname string is valid. It's usually a preliminary check before
- **Methods:** 1

#### domain-without-suffix
- **File:** `frontend-tools/node_modules/tldts-core/dist/es6/src/domain-without-suffix.js`
- **Type:** JavaScript
- **Size:** 485 B
- **Capabilities:**
  - Return the part of domain without suffix.
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/index.js`
- **Type:** JavaScript
- **Size:** 19.68 KB
- **Capabilities:**
  - Check if `vhost` is a valid suffix of `hostname` (top-domain)
  - Given a hostname and its public suffix, extract the general domain.
  - Detects the domain based on rules and upon and a host string
- **Methods:** 17

#### domain
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/domain.js`
- **Type:** JavaScript
- **Size:** 3.24 KB
- **Capabilities:**
  - Check if `vhost` is a valid suffix of `hostname` (top-domain)
  - Given a hostname and its public suffix, extract the general domain.
  - Detects the domain based on rules and upon and a host string
- **Methods:** 3

#### subdomain
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/subdomain.js`
- **Type:** JavaScript
- **Size:** 440 B
- **Capabilities:**
  - Returns the subdomain of a hostname string
- **Methods:** 1

#### extract-hostname
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/extract-hostname.js`
- **Type:** JavaScript
- **Size:** 5.54 KB
- **Methods:** 1

#### options
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/options.js`
- **Type:** JavaScript
- **Size:** 746 B
- **Methods:** 2

#### is-ip
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/is-ip.js`
- **Type:** JavaScript
- **Size:** 2.17 KB
- **Capabilities:**
  - Check if a hostname is an IP. You should be aware that this only works
  - Similar to isProbablyIpv4.
  - Check if `hostname` is *probably* a valid ip addr (either ipv6 or ipv4).
- **Methods:** 3

#### fast-path
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/lookup/fast-path.js`
- **Type:** JavaScript
- **Size:** 2.29 KB
- **Methods:** 1

#### factory
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/factory.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Capabilities:**
  - Implement a factory allowing to plug different implementations of suffix
- **Methods:** 3

#### is-valid
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/is-valid.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Implements fast shallow verification of hostnames. This does not perform a
  - Check if a hostname string is valid. It's usually a preliminary check before
- **Methods:** 2

#### domain-without-suffix
- **File:** `frontend-tools/node_modules/tldts-core/dist/cjs/src/domain-without-suffix.js`
- **Type:** JavaScript
- **Size:** 589 B
- **Capabilities:**
  - Return the part of domain without suffix.
- **Methods:** 1

#### _baseFor
- **File:** `frontend-tools/node_modules/lodash/_baseFor.js`
- **Type:** JavaScript
- **Size:** 593 B
- **Capabilities:**
  - The base implementation of `baseForOwn` which iterates over `object`
- **Methods:** 2

#### _stringToPath
- **File:** `frontend-tools/node_modules/lodash/_stringToPath.js`
- **Type:** JavaScript
- **Size:** 840 B
- **Capabilities:**
  - Converts `string` to a property path array.

#### _mapCacheSet
- **File:** `frontend-tools/node_modules/lodash/_mapCacheSet.js`
- **Type:** JavaScript
- **Size:** 489 B
- **Capabilities:**
  - Sets the map `key` to `value`.
- **Methods:** 1

#### _stackGet
- **File:** `frontend-tools/node_modules/lodash/_stackGet.js`
- **Type:** JavaScript
- **Size:** 271 B
- **Capabilities:**
  - Gets the stack value for `key`.
- **Methods:** 1

#### _equalArrays
- **File:** `frontend-tools/node_modules/lodash/_equalArrays.js`
- **Type:** JavaScript
- **Size:** 2.6 KB
- **Capabilities:**
  - A specialized version of `baseIsEqualDeep` for arrays with support for
- **Methods:** 2

#### _baseForOwn
- **File:** `frontend-tools/node_modules/lodash/_baseForOwn.js`
- **Type:** JavaScript
- **Size:** 456 B
- **Capabilities:**
  - The base implementation of `_.forOwn` without support for iteratee shorthands.
- **Methods:** 2

#### lte
- **File:** `frontend-tools/node_modules/lodash/lte.js`
- **Type:** JavaScript
- **Size:** 629 B
- **Capabilities:**
  - Checks if `value` is less than or equal to `other`.

#### _baseSortedIndexBy
- **File:** `frontend-tools/node_modules/lodash/_baseSortedIndexBy.js`
- **Type:** JavaScript
- **Size:** 2.21 KB
- **Capabilities:**
  - The base implementation of `_.sortedIndexBy` and `_.sortedLastIndexBy`
- **Methods:** 1

#### _equalByTag
- **File:** `frontend-tools/node_modules/lodash/_equalByTag.js`
- **Type:** JavaScript
- **Size:** 3.66 KB
- **Capabilities:**
  - A specialized version of `baseIsEqualDeep` for comparing objects of
- **Methods:** 3

#### startsWith
- **File:** `frontend-tools/node_modules/lodash/startsWith.js`
- **Type:** JavaScript
- **Size:** 1017 B
- **Capabilities:**
  - Checks if `string` starts with the given target string.
- **Methods:** 1

#### _nativeKeysIn
- **File:** `frontend-tools/node_modules/lodash/_nativeKeysIn.js`
- **Type:** JavaScript
- **Size:** 490 B
- **Capabilities:**
  - This function is like
- **Methods:** 2

#### _baseConformsTo
- **File:** `frontend-tools/node_modules/lodash/_baseConformsTo.js`
- **Type:** JavaScript
- **Size:** 718 B
- **Capabilities:**
  - The base implementation of `_.conformsTo` which accepts `props` to check.
- **Methods:** 1

#### _baseSetData
- **File:** `frontend-tools/node_modules/lodash/_baseSetData.js`
- **Type:** JavaScript
- **Size:** 456 B
- **Capabilities:**
  - The base implementation of `setData` without support for hot loop shorting.
- **Methods:** 1

#### _baseToPairs
- **File:** `frontend-tools/node_modules/lodash/_baseToPairs.js`
- **Type:** JavaScript
- **Size:** 537 B
- **Capabilities:**
  - The base implementation of `_.toPairs` and `_.toPairsIn` which creates an array
- **Methods:** 1

#### _assignValue
- **File:** `frontend-tools/node_modules/lodash/_assignValue.js`
- **Type:** JavaScript
- **Size:** 899 B
- **Capabilities:**
  - Assigns `value` to `key` of `object` if the existing value is not equivalent
- **Methods:** 1

#### _baseLt
- **File:** `frontend-tools/node_modules/lodash/_baseLt.js`
- **Type:** JavaScript
- **Size:** 354 B
- **Capabilities:**
  - The base implementation of `_.lt` which doesn't coerce arguments.
- **Methods:** 1

#### rearg
- **File:** `frontend-tools/node_modules/lodash/rearg.js`
- **Type:** JavaScript
- **Size:** 1023 B
- **Capabilities:**
  - Creates a function that invokes `func` with arguments arranged according
- **Methods:** 3

#### forIn
- **File:** `frontend-tools/node_modules/lodash/forIn.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - Iterates over own and inherited enumerable string keyed properties of an
- **Methods:** 3

#### _createCurry
- **File:** `frontend-tools/node_modules/lodash/_createCurry.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Creates a function that wraps `func` to enable currying.
- **Methods:** 4

#### _createWrap
- **File:** `frontend-tools/node_modules/lodash/_createWrap.js`
- **Type:** JavaScript
- **Size:** 3.63 KB
- **Capabilities:**
  - Creates a function that either curries or invokes `func` with optional
- **Methods:** 4

#### fromPairs
- **File:** `frontend-tools/node_modules/lodash/fromPairs.js`
- **Type:** JavaScript
- **Size:** 596 B
- **Capabilities:**
  - The inverse of `_.toPairs`; this method returns an object composed
- **Methods:** 1

#### xorBy
- **File:** `frontend-tools/node_modules/lodash/xorBy.js`
- **Type:** JavaScript
- **Size:** 1.27 KB
- **Capabilities:**
  - This method is like `_.xor` except that it accepts `iteratee` which is

#### bind
- **File:** `frontend-tools/node_modules/lodash/bind.js`
- **Type:** JavaScript
- **Size:** 1.65 KB
- **Capabilities:**
  - Creates a function that invokes `func` with the `this` binding of `thisArg`
- **Methods:** 4

#### _memoizeCapped
- **File:** `frontend-tools/node_modules/lodash/_memoizeCapped.js`
- **Type:** JavaScript
- **Size:** 633 B
- **Capabilities:**
  - A specialized version of `_.memoize` which clears the memoized function's
- **Methods:** 2

#### _baseIsMatch
- **File:** `frontend-tools/node_modules/lodash/_baseIsMatch.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Capabilities:**
  - The base implementation of `_.isMatch` without support for iteratee shorthands.
- **Methods:** 2

#### _arrayPush
- **File:** `frontend-tools/node_modules/lodash/_arrayPush.js`
- **Type:** JavaScript
- **Size:** 437 B
- **Capabilities:**
  - Appends the elements of `values` to `array`.
- **Methods:** 1

#### _copySymbols
- **File:** `frontend-tools/node_modules/lodash/_copySymbols.js`
- **Type:** JavaScript
- **Size:** 446 B
- **Capabilities:**
  - Copies own symbols of `source` to `object`.
- **Methods:** 1

#### isWeakMap
- **File:** `frontend-tools/node_modules/lodash/isWeakMap.js`
- **Type:** JavaScript
- **Size:** 631 B
- **Capabilities:**
  - Checks if `value` is classified as a `WeakMap` object.
- **Methods:** 1

#### templateSettings
- **File:** `frontend-tools/node_modules/lodash/templateSettings.js`
- **Type:** JavaScript
- **Size:** 1.38 KB
- **Capabilities:**
  - By default, the template delimiters used by lodash are like those in
  - Used to detect `data` property values to be HTML-escaped.
  - Used to detect code to be evaluated.

#### pad
- **File:** `frontend-tools/node_modules/lodash/pad.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Capabilities:**
  - Pads `string` on the left and right sides if it's shorter than `length`.
- **Methods:** 1

#### _baseEachRight
- **File:** `frontend-tools/node_modules/lodash/_baseEachRight.js`
- **Type:** JavaScript
- **Size:** 491 B
- **Capabilities:**
  - The base implementation of `_.forEachRight` without support for iteratee shorthands.
- **Methods:** 1

#### uniqWith
- **File:** `frontend-tools/node_modules/lodash/uniqWith.js`
- **Type:** JavaScript
- **Size:** 958 B
- **Capabilities:**
  - This method is like `_.uniq` except that it accepts `comparator` which
- **Methods:** 1

#### _lazyClone
- **File:** `frontend-tools/node_modules/lodash/_lazyClone.js`
- **Type:** JavaScript
- **Size:** 657 B
- **Capabilities:**
  - Creates a clone of the lazy wrapper object.
- **Methods:** 1

#### _hashDelete
- **File:** `frontend-tools/node_modules/lodash/_hashDelete.js`
- **Type:** JavaScript
- **Size:** 445 B
- **Capabilities:**
  - Removes `key` and its value from the hash.
- **Methods:** 1

#### assignIn
- **File:** `frontend-tools/node_modules/lodash/assignIn.js`
- **Type:** JavaScript
- **Size:** 906 B
- **Capabilities:**
  - This method is like `_.assign` except that it iterates over own and
- **Methods:** 2

#### _baseMerge
- **File:** `frontend-tools/node_modules/lodash/_baseMerge.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - The base implementation of `_.merge` without support for multiple sources.
- **Methods:** 2

#### _arraySome
- **File:** `frontend-tools/node_modules/lodash/_arraySome.js`
- **Type:** JavaScript
- **Size:** 594 B
- **Capabilities:**
  - A specialized version of `_.some` for arrays without support for iteratee
- **Methods:** 2

#### isArguments
- **File:** `frontend-tools/node_modules/lodash/isArguments.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - Checks if `value` is likely an `arguments` object.

#### _compareAscending
- **File:** `frontend-tools/node_modules/lodash/_compareAscending.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - Compares values to sort them in ascending order.
- **Methods:** 1

#### _baseIsNative
- **File:** `frontend-tools/node_modules/lodash/_baseIsNative.js`
- **Type:** JavaScript
- **Size:** 1.38 KB
- **Capabilities:**
  - Used to match `RegExp`
  - The base implementation of `_.isNative` without bad shim checks.
- **Methods:** 1

#### castArray
- **File:** `frontend-tools/node_modules/lodash/castArray.js`
- **Type:** JavaScript
- **Size:** 768 B
- **Capabilities:**
  - Casts `value` as an array if it's not one.
- **Methods:** 1

#### _baseSampleSize
- **File:** `frontend-tools/node_modules/lodash/_baseSampleSize.js`
- **Type:** JavaScript
- **Size:** 548 B
- **Capabilities:**
  - The base implementation of `_.sampleSize` without param guards.
- **Methods:** 1

#### pickBy
- **File:** `frontend-tools/node_modules/lodash/pickBy.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - Creates an object composed of the `object` properties `predicate` returns
- **Methods:** 2

#### _baseInvoke
- **File:** `frontend-tools/node_modules/lodash/_baseInvoke.js`
- **Type:** JavaScript
- **Size:** 789 B
- **Capabilities:**
  - The base implementation of `_.invoke` without support for individual
- **Methods:** 1

#### isInteger
- **File:** `frontend-tools/node_modules/lodash/isInteger.js`
- **Type:** JavaScript
- **Size:** 669 B
- **Capabilities:**
  - Checks if `value` is an integer.
- **Methods:** 1

#### _baseGet
- **File:** `frontend-tools/node_modules/lodash/_baseGet.js`
- **Type:** JavaScript
- **Size:** 616 B
- **Capabilities:**
  - The base implementation of `_.get` without support for default values.
- **Methods:** 1

#### _overRest
- **File:** `frontend-tools/node_modules/lodash/_overRest.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - A specialized version of `baseRest` which transforms the rest array.
- **Methods:** 2

#### upperCase
- **File:** `frontend-tools/node_modules/lodash/upperCase.js`
- **Type:** JavaScript
- **Size:** 620 B
- **Capabilities:**
  - Converts `string`, as space separated words, to upper case.

#### _iteratorToArray
- **File:** `frontend-tools/node_modules/lodash/_iteratorToArray.js`
- **Type:** JavaScript
- **Size:** 360 B
- **Capabilities:**
  - Converts `iterator` to an array.
- **Methods:** 1

#### _stackHas
- **File:** `frontend-tools/node_modules/lodash/_stackHas.js`
- **Type:** JavaScript
- **Size:** 323 B
- **Capabilities:**
  - Checks if a stack value for `key` exists.
- **Methods:** 1

#### method
- **File:** `frontend-tools/node_modules/lodash/method.js`
- **Type:** JavaScript
- **Size:** 860 B
- **Capabilities:**
  - Creates a function that invokes the method at `path` of a given object.
- **Methods:** 1

#### _baseFindIndex
- **File:** `frontend-tools/node_modules/lodash/_baseFindIndex.js`
- **Type:** JavaScript
- **Size:** 766 B
- **Capabilities:**
  - The base implementation of `_.findIndex` and `_.findLastIndex` without
- **Methods:** 2

#### unionBy
- **File:** `frontend-tools/node_modules/lodash/unionBy.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Capabilities:**
  - This method is like `_.union` except that it accepts `iteratee` which is

#### drop
- **File:** `frontend-tools/node_modules/lodash/drop.js`
- **Type:** JavaScript
- **Size:** 890 B
- **Capabilities:**
  - Creates a slice of `array` with `n` elements dropped from the beginning.
- **Methods:** 1

#### _customDefaultsMerge
- **File:** `frontend-tools/node_modules/lodash/_customDefaultsMerge.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Used by `_.defaultsDeep` to customize its `_.merge` use to merge source
- **Methods:** 1

#### _customDefaultsAssignIn
- **File:** `frontend-tools/node_modules/lodash/_customDefaultsAssignIn.js`
- **Type:** JavaScript
- **Size:** 934 B
- **Capabilities:**
  - Used by `_.defaults` to customize its `_.assignIn` use to assign properties
- **Methods:** 1

#### sortedIndexOf
- **File:** `frontend-tools/node_modules/lodash/sortedIndexOf.js`
- **Type:** JavaScript
- **Size:** 762 B
- **Capabilities:**
  - This method is like `_.indexOf` except that it performs a binary
- **Methods:** 1

#### sortedUniqBy
- **File:** `frontend-tools/node_modules/lodash/sortedUniqBy.js`
- **Type:** JavaScript
- **Size:** 698 B
- **Capabilities:**
  - This method is like `_.uniqBy` except that it's designed and optimized
- **Methods:** 1

#### rest
- **File:** `frontend-tools/node_modules/lodash/rest.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Capabilities:**
  - Creates a function that invokes `func` with the `this` binding of the
- **Methods:** 4

#### isWeakSet
- **File:** `frontend-tools/node_modules/lodash/isWeakSet.js`
- **Type:** JavaScript
- **Size:** 643 B
- **Capabilities:**
  - Checks if `value` is classified as a `WeakSet` object.
- **Methods:** 1

#### toIterator
- **File:** `frontend-tools/node_modules/lodash/toIterator.js`
- **Type:** JavaScript
- **Size:** 403 B
- **Capabilities:**
  - Enables the wrapper to be iterable.
- **Methods:** 1

#### isEmpty
- **File:** `frontend-tools/node_modules/lodash/isEmpty.js`
- **Type:** JavaScript
- **Size:** 1.95 KB
- **Capabilities:**
  - Checks if `value` is an empty object, collection, map, or set.
- **Methods:** 1

#### _isLaziable
- **File:** `frontend-tools/node_modules/lodash/_isLaziable.js`
- **Type:** JavaScript
- **Size:** 712 B
- **Capabilities:**
  - Checks if `func` has a lazy counterpart.
- **Methods:** 2

#### _hasUnicode
- **File:** `frontend-tools/node_modules/lodash/_hasUnicode.js`
- **Type:** JavaScript
- **Size:** 949 B
- **Capabilities:**
  - Checks if `string` contains Unicode symbols.
- **Methods:** 1

#### _arrayEach
- **File:** `frontend-tools/node_modules/lodash/_arrayEach.js`
- **Type:** JavaScript
- **Size:** 537 B
- **Capabilities:**
  - A specialized version of `_.forEach` for arrays without support for
- **Methods:** 2

#### after
- **File:** `frontend-tools/node_modules/lodash/after.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - The opposite of `_.before`; this method creates a function that invokes
- **Methods:** 3

#### _strictLastIndexOf
- **File:** `frontend-tools/node_modules/lodash/_strictLastIndexOf.js`
- **Type:** JavaScript
- **Size:** 576 B
- **Capabilities:**
  - A specialized version of `_.lastIndexOf` which performs strict equality
- **Methods:** 1

#### repeat
- **File:** `frontend-tools/node_modules/lodash/repeat.js`
- **Type:** JavaScript
- **Size:** 893 B
- **Capabilities:**
  - Repeats the given string `n` times.
- **Methods:** 1

#### keysIn
- **File:** `frontend-tools/node_modules/lodash/keysIn.js`
- **Type:** JavaScript
- **Size:** 778 B
- **Capabilities:**
  - Creates an array of the own and inherited enumerable property names of `object`.
- **Methods:** 2

#### flatMapDeep
- **File:** `frontend-tools/node_modules/lodash/flatMapDeep.js`
- **Type:** JavaScript
- **Size:** 796 B
- **Capabilities:**
  - This method is like `_.flatMap` except that it recursively flattens the
- **Methods:** 3

#### _baseXor
- **File:** `frontend-tools/node_modules/lodash/_baseXor.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - The base implementation of methods like `_.xor`, without support for
- **Methods:** 1

#### _arrayFilter
- **File:** `frontend-tools/node_modules/lodash/_arrayFilter.js`
- **Type:** JavaScript
- **Size:** 632 B
- **Capabilities:**
  - A specialized version of `_.filter` for arrays without support for
- **Methods:** 2

#### _parent
- **File:** `frontend-tools/node_modules/lodash/_parent.js`
- **Type:** JavaScript
- **Size:** 436 B
- **Capabilities:**
  - Gets the parent value at `path` of `object`.
- **Methods:** 1

#### uniqBy
- **File:** `frontend-tools/node_modules/lodash/uniqBy.js`
- **Type:** JavaScript
- **Size:** 1013 B
- **Capabilities:**
  - This method is like `_.uniq` except that it accepts `iteratee` which is
- **Methods:** 1

#### _baseEach
- **File:** `frontend-tools/node_modules/lodash/_baseEach.js`
- **Type:** JavaScript
- **Size:** 455 B
- **Capabilities:**
  - The base implementation of `_.forEach` without support for iteratee shorthands.
- **Methods:** 1

#### _basePropertyDeep
- **File:** `frontend-tools/node_modules/lodash/_basePropertyDeep.js`
- **Type:** JavaScript
- **Size:** 391 B
- **Capabilities:**
  - A specialized version of `baseProperty` which supports deep paths.
- **Methods:** 1

#### isArrayLikeObject
- **File:** `frontend-tools/node_modules/lodash/isArrayLikeObject.js`
- **Type:** JavaScript
- **Size:** 742 B
- **Capabilities:**
  - This method is like `_.isArrayLike` except that it also checks if `value`
- **Methods:** 1

#### _mapCacheHas
- **File:** `frontend-tools/node_modules/lodash/_mapCacheHas.js`
- **Type:** JavaScript
- **Size:** 382 B
- **Capabilities:**
  - Checks if a map value for `key` exists.
- **Methods:** 1

#### isMap
- **File:** `frontend-tools/node_modules/lodash/isMap.js`
- **Type:** JavaScript
- **Size:** 613 B
- **Capabilities:**
  - Checks if `value` is classified as a `Map` object.

#### template
- **File:** `frontend-tools/node_modules/lodash/template.js`
- **Type:** JavaScript
- **Size:** 10.2 KB
- **Capabilities:**
  - Used to validate the `validate` option in `_.template` variable.
  - Used to match
  - Creates a compiled template function that can interpolate data properties
- **Methods:** 7

#### _root
- **File:** `frontend-tools/node_modules/lodash/_root.js`
- **Type:** JavaScript
- **Size:** 300 B

#### _getValue
- **File:** `frontend-tools/node_modules/lodash/_getValue.js`
- **Type:** JavaScript
- **Size:** 325 B
- **Capabilities:**
  - Gets the value at `key` of `object`.
- **Methods:** 1

#### sortedLastIndex
- **File:** `frontend-tools/node_modules/lodash/sortedLastIndex.js`
- **Type:** JavaScript
- **Size:** 679 B
- **Capabilities:**
  - This method is like `_.sortedIndex` except that it returns the highest
- **Methods:** 1

#### _baseHas
- **File:** `frontend-tools/node_modules/lodash/_baseHas.js`
- **Type:** JavaScript
- **Size:** 559 B
- **Capabilities:**
  - The base implementation of `_.has` without support for deep paths.
- **Methods:** 1

#### _basePick
- **File:** `frontend-tools/node_modules/lodash/_basePick.js`
- **Type:** JavaScript
- **Size:** 501 B
- **Capabilities:**
  - The base implementation of `_.pick` without support for individual
- **Methods:** 1

#### _baseSome
- **File:** `frontend-tools/node_modules/lodash/_baseSome.js`
- **Type:** JavaScript
- **Size:** 619 B
- **Capabilities:**
  - The base implementation of `_.some` without support for iteratee shorthands.
- **Methods:** 2

#### _baseRest
- **File:** `frontend-tools/node_modules/lodash/_baseRest.js`
- **Type:** JavaScript
- **Size:** 559 B
- **Capabilities:**
  - The base implementation of `_.rest` which doesn't validate or coerce arguments.
- **Methods:** 2

#### chain
- **File:** `frontend-tools/node_modules/lodash/chain.js`
- **Type:** JavaScript
- **Size:** 851 B
- **Capabilities:**
  - Creates a `lodash` wrapper instance that wraps `value` with explicit method
- **Methods:** 1

#### _strictIndexOf
- **File:** `frontend-tools/node_modules/lodash/_strictIndexOf.js`
- **Type:** JavaScript
- **Size:** 600 B
- **Capabilities:**
  - A specialized version of `_.indexOf` which performs strict equality
- **Methods:** 1

#### core
- **File:** `frontend-tools/node_modules/lodash/core.js`
- **Type:** JavaScript
- **Size:** 113.24 KB
- **Capabilities:**
  - Appends the elements of `values` to `array`.
  - The base implementation of `_.findIndex` and `_.findLastIndex` without
  - The base implementation of `_.property` without support for deep paths.
- **Methods:** 20

#### takeRight
- **File:** `frontend-tools/node_modules/lodash/takeRight.js`
- **Type:** JavaScript
- **Size:** 930 B
- **Capabilities:**
  - Creates a slice of `array` with `n` elements taken from the end.
- **Methods:** 1

#### _baseWhile
- **File:** `frontend-tools/node_modules/lodash/_baseWhile.js`
- **Type:** JavaScript
- **Size:** 933 B
- **Capabilities:**
  - The base implementation of methods like `_.dropWhile` and `_.takeWhile`
- **Methods:** 2

#### _createHybrid
- **File:** `frontend-tools/node_modules/lodash/_createHybrid.js`
- **Type:** JavaScript
- **Size:** 3.18 KB
- **Capabilities:**
  - Creates a function that wraps `func` to invoke it with optional `this`
- **Methods:** 5

#### isDate
- **File:** `frontend-tools/node_modules/lodash/isDate.js`
- **Type:** JavaScript
- **Size:** 642 B
- **Capabilities:**
  - Checks if `value` is classified as a `Date` object.

#### clone
- **File:** `frontend-tools/node_modules/lodash/clone.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - Creates a shallow clone of `value`.
- **Methods:** 1

#### wrapperReverse
- **File:** `frontend-tools/node_modules/lodash/wrapperReverse.js`
- **Type:** JavaScript
- **Size:** 1019 B
- **Capabilities:**
  - This method is the wrapper version of `_.reverse`.
- **Methods:** 1

#### _baseIsNaN
- **File:** `frontend-tools/node_modules/lodash/_baseIsNaN.js`
- **Type:** JavaScript
- **Size:** 296 B
- **Capabilities:**
  - The base implementation of `_.isNaN` without support for number objects.
- **Methods:** 1

#### isArrayBuffer
- **File:** `frontend-tools/node_modules/lodash/isArrayBuffer.js`
- **Type:** JavaScript
- **Size:** 732 B
- **Capabilities:**
  - Checks if `value` is classified as an `ArrayBuffer` object.

#### _flatRest
- **File:** `frontend-tools/node_modules/lodash/_flatRest.js`
- **Type:** JavaScript
- **Size:** 457 B
- **Capabilities:**
  - A specialized version of `baseRest` which flattens the rest array.
- **Methods:** 2

#### toInteger
- **File:** `frontend-tools/node_modules/lodash/toInteger.js`
- **Type:** JavaScript
- **Size:** 760 B
- **Capabilities:**
  - Converts `value` to an integer.
- **Methods:** 1

#### _mapping
- **File:** `frontend-tools/node_modules/lodash/fp/_mapping.js`
- **Type:** JavaScript
- **Size:** 9.72 KB

#### functionsIn
- **File:** `frontend-tools/node_modules/lodash/fp/functionsIn.js`
- **Type:** JavaScript
- **Size:** 195 B

#### convert
- **File:** `frontend-tools/node_modules/lodash/fp/convert.js`
- **Type:** JavaScript
- **Size:** 657 B
- **Capabilities:**
  - Converts `func` of `name` to an immutable auto-curried iteratee-first data-last
- **Methods:** 3

#### _util
- **File:** `frontend-tools/node_modules/lodash/fp/_util.js`
- **Type:** JavaScript
- **Size:** 524 B

#### _baseConvert
- **File:** `frontend-tools/node_modules/lodash/fp/_baseConvert.js`
- **Type:** JavaScript
- **Size:** 16.03 KB
- **Capabilities:**
  - Creates a function, with an arity of `n`, that invokes `func` with the
  - Creates a function that invokes `func`, with up to `n` arguments, ignoring
  - Creates a clone of `array`.
- **Methods:** 20

#### function
- **File:** `frontend-tools/node_modules/lodash/fp/function.js`
- **Type:** JavaScript
- **Size:** 86 B

#### isFunction
- **File:** `frontend-tools/node_modules/lodash/fp/isFunction.js`
- **Type:** JavaScript
- **Size:** 193 B

#### util
- **File:** `frontend-tools/node_modules/lodash/fp/util.js`
- **Type:** JavaScript
- **Size:** 82 B

#### _convertBrowser
- **File:** `frontend-tools/node_modules/lodash/fp/_convertBrowser.js`
- **Type:** JavaScript
- **Size:** 615 B
- **Capabilities:**
  - Converts `lodash` to an immutable auto-curried iteratee-first data-last
- **Methods:** 2

#### functions
- **File:** `frontend-tools/node_modules/lodash/fp/functions.js`
- **Type:** JavaScript
- **Size:** 191 B

#### isNumber
- **File:** `frontend-tools/node_modules/lodash/isNumber.js`
- **Type:** JavaScript
- **Size:** 886 B
- **Capabilities:**
  - Checks if `value` is classified as a `Number` primitive or object.
- **Methods:** 1

#### meanBy
- **File:** `frontend-tools/node_modules/lodash/meanBy.js`
- **Type:** JavaScript
- **Size:** 879 B
- **Capabilities:**
  - This method is like `_.mean` except that it accepts `iteratee` which is
- **Methods:** 1

#### cloneWith
- **File:** `frontend-tools/node_modules/lodash/cloneWith.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Capabilities:**
  - This method is like `_.clone` except that it accepts `customizer` which
- **Methods:** 3

#### _setWrapToString
- **File:** `frontend-tools/node_modules/lodash/_setWrapToString.js`
- **Type:** JavaScript
- **Size:** 847 B
- **Capabilities:**
  - Sets the `toString` method of `wrapper` to mimic the source of `reference`
- **Methods:** 2

#### _charsStartIndex
- **File:** `frontend-tools/node_modules/lodash/_charsStartIndex.js`
- **Type:** JavaScript
- **Size:** 636 B
- **Capabilities:**
  - Used by `_.trim` and `_.trimStart` to get the index of the first string symbol
- **Methods:** 1

#### lodash.min
- **File:** `frontend-tools/node_modules/lodash/lodash.min.js`
- **Type:** JavaScript
- **Size:** 71.3 KB
- **Methods:** 20

#### _copyArray
- **File:** `frontend-tools/node_modules/lodash/_copyArray.js`
- **Type:** JavaScript
- **Size:** 454 B
- **Capabilities:**
  - Copies the values of `source` to `array`.
- **Methods:** 1

#### pullAll
- **File:** `frontend-tools/node_modules/lodash/pullAll.js`
- **Type:** JavaScript
- **Size:** 710 B
- **Capabilities:**
  - This method is like `_.pull` except that it accepts an array of values to remove.
- **Methods:** 1

#### lang
- **File:** `frontend-tools/node_modules/lodash/lang.js`
- **Type:** JavaScript
- **Size:** 2.09 KB

#### setWith
- **File:** `frontend-tools/node_modules/lodash/setWith.js`
- **Type:** JavaScript
- **Size:** 1.03 KB
- **Capabilities:**
  - This method is like `_.set` except that it accepts `customizer` which is
- **Methods:** 2

#### methodOf
- **File:** `frontend-tools/node_modules/lodash/methodOf.js`
- **Type:** JavaScript
- **Size:** 912 B
- **Capabilities:**
  - The opposite of `_.method`; this method creates a function that invokes
- **Methods:** 1

#### toFinite
- **File:** `frontend-tools/node_modules/lodash/toFinite.js`
- **Type:** JavaScript
- **Size:** 868 B
- **Capabilities:**
  - Converts `value` to a finite number.
- **Methods:** 1

#### isMatchWith
- **File:** `frontend-tools/node_modules/lodash/isMatchWith.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - This method is like `_.isMatch` except that it accepts `customizer` which
- **Methods:** 4

#### object
- **File:** `frontend-tools/node_modules/lodash/object.js`
- **Type:** JavaScript
- **Size:** 1.63 KB

#### _getSymbolsIn
- **File:** `frontend-tools/node_modules/lodash/_getSymbolsIn.js`
- **Type:** JavaScript
- **Size:** 754 B
- **Capabilities:**
  - Creates an array of the own and inherited enumerable symbols of `object`.

#### _getMatchData
- **File:** `frontend-tools/node_modules/lodash/_getMatchData.js`
- **Type:** JavaScript
- **Size:** 573 B
- **Capabilities:**
  - Gets the property names, values, and compare flags of `object`.
- **Methods:** 1

#### _baseMatchesProperty
- **File:** `frontend-tools/node_modules/lodash/_baseMatchesProperty.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Capabilities:**
  - The base implementation of `_.matchesProperty` which doesn't clone `srcValue`.
- **Methods:** 1

#### kebabCase
- **File:** `frontend-tools/node_modules/lodash/kebabCase.js`
- **Type:** JavaScript
- **Size:** 659 B
- **Capabilities:**
  - Converts `string` to

#### _arraySampleSize
- **File:** `frontend-tools/node_modules/lodash/_arraySampleSize.js`
- **Type:** JavaScript
- **Size:** 500 B
- **Capabilities:**
  - A specialized version of `_.sampleSize` for arrays.
- **Methods:** 1

#### negate
- **File:** `frontend-tools/node_modules/lodash/negate.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Creates a function that negates the result of the predicate `func`. The
- **Methods:** 3

#### _baseSortedIndex
- **File:** `frontend-tools/node_modules/lodash/_baseSortedIndex.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Capabilities:**
  - The base implementation of `_.sortedIndex` and `_.sortedLastIndex` which
- **Methods:** 1

#### has
- **File:** `frontend-tools/node_modules/lodash/has.js`
- **Type:** JavaScript
- **Size:** 757 B
- **Capabilities:**
  - Checks if `path` is a direct property of `object`.
- **Methods:** 1

#### _lazyReverse
- **File:** `frontend-tools/node_modules/lodash/_lazyReverse.js`
- **Type:** JavaScript
- **Size:** 491 B
- **Capabilities:**
  - Reverses the direction of lazy iteration.
- **Methods:** 1

#### _cloneRegExp
- **File:** `frontend-tools/node_modules/lodash/_cloneRegExp.js`
- **Type:** JavaScript
- **Size:** 439 B
- **Capabilities:**
  - Creates a clone of `regexp`.
- **Methods:** 1

#### isEqualWith
- **File:** `frontend-tools/node_modules/lodash/isEqualWith.js`
- **Type:** JavaScript
- **Size:** 1.32 KB
- **Capabilities:**
  - This method is like `_.isEqual` except that it accepts `customizer` which
- **Methods:** 4

#### _compareMultiple
- **File:** `frontend-tools/node_modules/lodash/_compareMultiple.js`
- **Type:** JavaScript
- **Size:** 1.56 KB
- **Capabilities:**
  - Used by `_.orderBy` to compare multiple properties of a value to another
- **Methods:** 1

#### uniq
- **File:** `frontend-tools/node_modules/lodash/uniq.js`
- **Type:** JavaScript
- **Size:** 688 B
- **Capabilities:**
  - Creates a duplicate-free version of an array, using
- **Methods:** 1

#### _getSymbols
- **File:** `frontend-tools/node_modules/lodash/_getSymbols.js`
- **Type:** JavaScript
- **Size:** 886 B
- **Capabilities:**
  - Creates an array of the own enumerable symbols of `object`.

#### wrapperAt
- **File:** `frontend-tools/node_modules/lodash/wrapperAt.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - This method is the wrapper version of `_.at`.

#### conforms
- **File:** `frontend-tools/node_modules/lodash/conforms.js`
- **Type:** JavaScript
- **Size:** 978 B
- **Capabilities:**
  - Creates a function that invokes the predicate properties of `source` with
- **Methods:** 3

#### xorWith
- **File:** `frontend-tools/node_modules/lodash/xorWith.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - This method is like `_.xor` except that it accepts `comparator` which is

#### _createRelationalOperation
- **File:** `frontend-tools/node_modules/lodash/_createRelationalOperation.js`
- **Type:** JavaScript
- **Size:** 578 B
- **Capabilities:**
  - Creates a function that performs a relational operation on two values.
- **Methods:** 3

#### _LazyWrapper
- **File:** `frontend-tools/node_modules/lodash/_LazyWrapper.js`
- **Type:** JavaScript
- **Size:** 773 B
- **Capabilities:**
  - Creates a lazy wrapper object which wraps `value` to enable lazy evaluation.
- **Methods:** 1

#### tap
- **File:** `frontend-tools/node_modules/lodash/tap.js`
- **Type:** JavaScript
- **Size:** 703 B
- **Capabilities:**
  - This method invokes `interceptor` and returns `value`. The interceptor
- **Methods:** 2

#### isNil
- **File:** `frontend-tools/node_modules/lodash/isNil.js`
- **Type:** JavaScript
- **Size:** 426 B
- **Capabilities:**
  - Checks if `value` is `null` or `undefined`.
- **Methods:** 1

#### unzipWith
- **File:** `frontend-tools/node_modules/lodash/unzipWith.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - This method is like `_.unzip` except that it accepts `iteratee` to specify
- **Methods:** 2

#### _setToPairs
- **File:** `frontend-tools/node_modules/lodash/_setToPairs.js`
- **Type:** JavaScript
- **Size:** 364 B
- **Capabilities:**
  - Converts `set` to its value-value pairs.
- **Methods:** 1

#### _baseUpdate
- **File:** `frontend-tools/node_modules/lodash/_baseUpdate.js`
- **Type:** JavaScript
- **Size:** 605 B
- **Capabilities:**
  - The base implementation of `_.update`.
- **Methods:** 2

#### eq
- **File:** `frontend-tools/node_modules/lodash/eq.js`
- **Type:** JavaScript
- **Size:** 799 B
- **Methods:** 1

#### stubArray
- **File:** `frontend-tools/node_modules/lodash/stubArray.js`
- **Type:** JavaScript
- **Size:** 390 B
- **Capabilities:**
  - This method returns a new empty array.
- **Methods:** 1

#### _baseToNumber
- **File:** `frontend-tools/node_modules/lodash/_baseToNumber.js`
- **Type:** JavaScript
- **Size:** 539 B
- **Capabilities:**
  - The base implementation of `_.toNumber` which doesn't ensure correct
- **Methods:** 1

#### _castRest
- **File:** `frontend-tools/node_modules/lodash/_castRest.js`
- **Type:** JavaScript
- **Size:** 348 B
- **Capabilities:**
  - A `baseRest` alias which can be replaced with `identity` by module
- **Methods:** 1

#### wrapperLodash
- **File:** `frontend-tools/node_modules/lodash/wrapperLodash.js`
- **Type:** JavaScript
- **Size:** 6.78 KB
- **Capabilities:**
  - Creates a `lodash` object which wraps `value` to enable implicit method
- **Methods:** 2

#### toSafeInteger
- **File:** `frontend-tools/node_modules/lodash/toSafeInteger.js`
- **Type:** JavaScript
- **Size:** 836 B
- **Capabilities:**
  - Converts `value` to a safe integer. A safe integer can be compared and
- **Methods:** 1

#### _getRawTag
- **File:** `frontend-tools/node_modules/lodash/_getRawTag.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Capabilities:**
  - Used to resolve the
  - A specialized version of `baseGetTag` which ignores `Symbol.toStringTag` values.
- **Methods:** 1

#### _Stack
- **File:** `frontend-tools/node_modules/lodash/_Stack.js`
- **Type:** JavaScript
- **Size:** 734 B
- **Capabilities:**
  - Creates a stack cache object to store key-value pairs.
- **Methods:** 1

#### isEqual
- **File:** `frontend-tools/node_modules/lodash/isEqual.js`
- **Type:** JavaScript
- **Size:** 986 B
- **Capabilities:**
  - Performs a deep comparison between two values to determine if they are
- **Methods:** 1

#### _createSet
- **File:** `frontend-tools/node_modules/lodash/_createSet.js`
- **Type:** JavaScript
- **Size:** 501 B
- **Capabilities:**
  - Creates a set object of `values`.

#### toPairsIn
- **File:** `frontend-tools/node_modules/lodash/toPairsIn.js`
- **Type:** JavaScript
- **Size:** 737 B
- **Capabilities:**
  - Creates an array of own and inherited enumerable string keyed-value pairs
- **Methods:** 1

#### reduce
- **File:** `frontend-tools/node_modules/lodash/reduce.js`
- **Type:** JavaScript
- **Size:** 1.76 KB
- **Capabilities:**
  - Reduces `collection` to a value which is the accumulated result of running
- **Methods:** 2

#### _copySymbolsIn
- **File:** `frontend-tools/node_modules/lodash/_copySymbolsIn.js`
- **Type:** JavaScript
- **Size:** 470 B
- **Capabilities:**
  - Copies own and inherited symbols of `source` to `object`.
- **Methods:** 1

#### _objectToString
- **File:** `frontend-tools/node_modules/lodash/_objectToString.js`
- **Type:** JavaScript
- **Size:** 565 B
- **Capabilities:**
  - Used to resolve the
  - Converts `value` to a string using `Object.prototype.toString`.
- **Methods:** 1

#### memoize
- **File:** `frontend-tools/node_modules/lodash/memoize.js`
- **Type:** JavaScript
- **Size:** 2.17 KB
- **Capabilities:**
  - Creates a function that memoizes the result of `func`. If `resolver` is
- **Methods:** 4

#### _baseToString
- **File:** `frontend-tools/node_modules/lodash/_baseToString.js`
- **Type:** JavaScript
- **Size:** 1.13 KB
- **Capabilities:**
  - The base implementation of `_.toString` which doesn't convert nullish
- **Methods:** 1

#### sum
- **File:** `frontend-tools/node_modules/lodash/sum.js`
- **Type:** JavaScript
- **Size:** 453 B
- **Capabilities:**
  - Computes the sum of the values in `array`.
- **Methods:** 1

#### fill
- **File:** `frontend-tools/node_modules/lodash/fill.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Capabilities:**
  - Fills elements of `array` with `value` from `start` up to, but not
- **Methods:** 1

#### wrap
- **File:** `frontend-tools/node_modules/lodash/wrap.js`
- **Type:** JavaScript
- **Size:** 871 B
- **Capabilities:**
  - Creates a function that provides `value` to `wrapper` as its first
- **Methods:** 3

#### partial
- **File:** `frontend-tools/node_modules/lodash/partial.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Capabilities:**
  - Creates a function that invokes `func` with `partials` prepended to the
- **Methods:** 4

#### lodash
- **File:** `frontend-tools/node_modules/lodash/lodash.js`
- **Type:** JavaScript
- **Size:** 531.35 KB
- **Capabilities:**
  - Used to match `RegExp`
  - Used to validate the `validate` option in `_.template` variable.
  - Used to match
- **Methods:** 20

#### _cloneDataView
- **File:** `frontend-tools/node_modules/lodash/_cloneDataView.js`
- **Type:** JavaScript
- **Size:** 507 B
- **Capabilities:**
  - Creates a clone of `dataView`.
- **Methods:** 1

#### bindAll
- **File:** `frontend-tools/node_modules/lodash/bindAll.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Capabilities:**
  - Binds methods of an object to the object itself, overwriting the existing

#### functionsIn
- **File:** `frontend-tools/node_modules/lodash/functionsIn.js`
- **Type:** JavaScript
- **Size:** 714 B
- **Capabilities:**
  - Creates an array of function property names from own and inherited
- **Methods:** 4

#### cloneDeepWith
- **File:** `frontend-tools/node_modules/lodash/cloneDeepWith.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - This method is like `_.cloneWith` except that it recursively clones `value`.
- **Methods:** 3

#### stubObject
- **File:** `frontend-tools/node_modules/lodash/stubObject.js`
- **Type:** JavaScript
- **Size:** 400 B
- **Capabilities:**
  - This method returns a new empty object.
- **Methods:** 1

#### _asciiWords
- **File:** `frontend-tools/node_modules/lodash/_asciiWords.js`
- **Type:** JavaScript
- **Size:** 404 B
- **Capabilities:**
  - Splits an ASCII `string` into an array of its words.
- **Methods:** 1

#### _baseProperty
- **File:** `frontend-tools/node_modules/lodash/_baseProperty.js`
- **Type:** JavaScript
- **Size:** 360 B
- **Capabilities:**
  - The base implementation of `_.property` without support for deep paths.
- **Methods:** 1

#### _Hash
- **File:** `frontend-tools/node_modules/lodash/_Hash.js`
- **Type:** JavaScript
- **Size:** 747 B
- **Capabilities:**
  - Creates a hash object.
- **Methods:** 1

#### intersection
- **File:** `frontend-tools/node_modules/lodash/intersection.js`
- **Type:** JavaScript
- **Size:** 953 B
- **Capabilities:**
  - Creates an array of unique values that are included in all given arrays

#### isBoolean
- **File:** `frontend-tools/node_modules/lodash/isBoolean.js`
- **Type:** JavaScript
- **Size:** 681 B
- **Capabilities:**
  - Checks if `value` is classified as a boolean primitive or object.
- **Methods:** 1

#### _unicodeToArray
- **File:** `frontend-tools/node_modules/lodash/_unicodeToArray.js`
- **Type:** JavaScript
- **Size:** 1.55 KB
- **Capabilities:**
  - Converts a Unicode `string` to an array.
- **Methods:** 1

#### _baseHasIn
- **File:** `frontend-tools/node_modules/lodash/_baseHasIn.js`
- **Type:** JavaScript
- **Size:** 374 B
- **Capabilities:**
  - The base implementation of `_.hasIn` without support for deep paths.
- **Methods:** 1

#### overArgs
- **File:** `frontend-tools/node_modules/lodash/overArgs.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Creates a function that invokes `func` with its arguments transformed.
- **Methods:** 4

#### join
- **File:** `frontend-tools/node_modules/lodash/join.js`
- **Type:** JavaScript
- **Size:** 693 B
- **Capabilities:**
  - Converts all elements in `array` into a string separated by `separator`.
- **Methods:** 1

#### _baseValues
- **File:** `frontend-tools/node_modules/lodash/_baseValues.js`
- **Type:** JavaScript
- **Size:** 534 B
- **Capabilities:**
  - The base implementation of `_.values` and `_.valuesIn` which creates an
- **Methods:** 1

#### ary
- **File:** `frontend-tools/node_modules/lodash/ary.js`
- **Type:** JavaScript
- **Size:** 857 B
- **Capabilities:**
  - Creates a function that invokes `func`, with up to `n` arguments,
- **Methods:** 4

#### _equalObjects
- **File:** `frontend-tools/node_modules/lodash/_equalObjects.js`
- **Type:** JavaScript
- **Size:** 2.9 KB
- **Capabilities:**
  - A specialized version of `baseIsEqualDeep` for objects with support for
- **Methods:** 2

#### flattenDeep
- **File:** `frontend-tools/node_modules/lodash/flattenDeep.js`
- **Type:** JavaScript
- **Size:** 577 B
- **Capabilities:**
  - Recursively flattens `array`.
- **Methods:** 1

#### maxBy
- **File:** `frontend-tools/node_modules/lodash/maxBy.js`
- **Type:** JavaScript
- **Size:** 991 B
- **Capabilities:**
  - This method is like `_.max` except that it accepts `iteratee` which is
- **Methods:** 1

#### _baseUniq
- **File:** `frontend-tools/node_modules/lodash/_baseUniq.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - The base implementation of `_.uniqBy` without support for iteratee shorthands.
- **Methods:** 1

#### trim
- **File:** `frontend-tools/node_modules/lodash/trim.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - Removes leading and trailing whitespace or specified characters from `string`.
- **Methods:** 1

#### _isFlattenable
- **File:** `frontend-tools/node_modules/lodash/_isFlattenable.js`
- **Type:** JavaScript
- **Size:** 608 B
- **Capabilities:**
  - Checks if `value` is a flattenable `arguments` object or array.
- **Methods:** 1

#### _baseRepeat
- **File:** `frontend-tools/node_modules/lodash/_baseRepeat.js`
- **Type:** JavaScript
- **Size:** 952 B
- **Capabilities:**
  - The base implementation of `_.repeat` which doesn't coerce arguments.
- **Methods:** 1

#### isUndefined
- **File:** `frontend-tools/node_modules/lodash/isUndefined.js`
- **Type:** JavaScript
- **Size:** 416 B
- **Capabilities:**
  - Checks if `value` is `undefined`.
- **Methods:** 1

#### _baseSet
- **File:** `frontend-tools/node_modules/lodash/_baseSet.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - The base implementation of `_.set`.
- **Methods:** 2

#### isRegExp
- **File:** `frontend-tools/node_modules/lodash/isRegExp.js`
- **Type:** JavaScript
- **Size:** 646 B
- **Capabilities:**
  - Checks if `value` is classified as a `RegExp` object.

#### _replaceHolders
- **File:** `frontend-tools/node_modules/lodash/_replaceHolders.js`
- **Type:** JavaScript
- **Size:** 785 B
- **Capabilities:**
  - Replaces all `placeholder` elements in `array` with an internal placeholder
- **Methods:** 1

#### isError
- **File:** `frontend-tools/node_modules/lodash/isError.js`
- **Type:** JavaScript
- **Size:** 961 B
- **Capabilities:**
  - Checks if `value` is an `Error`, `EvalError`, `RangeError`, `ReferenceError`,
- **Methods:** 1

#### forOwnRight
- **File:** `frontend-tools/node_modules/lodash/forOwnRight.js`
- **Type:** JavaScript
- **Size:** 866 B
- **Capabilities:**
  - This method is like `_.forOwn` except that it iterates over properties of
- **Methods:** 3

#### _isMaskable
- **File:** `frontend-tools/node_modules/lodash/_isMaskable.js`
- **Type:** JavaScript
- **Size:** 395 B
- **Capabilities:**
  - Checks if `func` is capable of being masked.

#### size
- **File:** `frontend-tools/node_modules/lodash/size.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Capabilities:**
  - Gets the size of `collection` by returning its length for array-like
- **Methods:** 1

#### isArray
- **File:** `frontend-tools/node_modules/lodash/isArray.js`
- **Type:** JavaScript
- **Size:** 488 B
- **Capabilities:**
  - Checks if `value` is classified as an `Array` object.

#### get
- **File:** `frontend-tools/node_modules/lodash/get.js`
- **Type:** JavaScript
- **Size:** 884 B
- **Capabilities:**
  - Gets the value at `path` of `object`. If the resolved value is
- **Methods:** 1

#### _baseDifference
- **File:** `frontend-tools/node_modules/lodash/_baseDifference.js`
- **Type:** JavaScript
- **Size:** 1.87 KB
- **Capabilities:**
  - The base implementation of methods like `_.difference` without support
- **Methods:** 1

#### _cloneTypedArray
- **File:** `frontend-tools/node_modules/lodash/_cloneTypedArray.js`
- **Type:** JavaScript
- **Size:** 527 B
- **Capabilities:**
  - Creates a clone of `typedArray`.
- **Methods:** 1

#### toNumber
- **File:** `frontend-tools/node_modules/lodash/toNumber.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - Converts `value` to a number.
- **Methods:** 1

#### _baseIsRegExp
- **File:** `frontend-tools/node_modules/lodash/_baseIsRegExp.js`
- **Type:** JavaScript
- **Size:** 511 B
- **Capabilities:**
  - The base implementation of `_.isRegExp` without Node.js optimizations.
- **Methods:** 1

#### _arrayEvery
- **File:** `frontend-tools/node_modules/lodash/_arrayEvery.js`
- **Type:** JavaScript
- **Size:** 597 B
- **Capabilities:**
  - A specialized version of `_.every` for arrays without support for
- **Methods:** 2

#### findKey
- **File:** `frontend-tools/node_modules/lodash/findKey.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - This method is like `_.find` except that it returns the key of the first
- **Methods:** 2

#### _charsEndIndex
- **File:** `frontend-tools/node_modules/lodash/_charsEndIndex.js`
- **Type:** JavaScript
- **Size:** 600 B
- **Capabilities:**
  - Used by `_.trim` and `_.trimEnd` to get the index of the last string symbol
- **Methods:** 1

#### _getFuncName
- **File:** `frontend-tools/node_modules/lodash/_getFuncName.js`
- **Type:** JavaScript
- **Size:** 756 B
- **Capabilities:**
  - Gets the name of `func`.
- **Methods:** 3

#### _setCacheHas
- **File:** `frontend-tools/node_modules/lodash/_setCacheHas.js`
- **Type:** JavaScript
- **Size:** 316 B
- **Capabilities:**
  - Checks if `value` is in the array cache.
- **Methods:** 1

#### _LodashWrapper
- **File:** `frontend-tools/node_modules/lodash/_LodashWrapper.js`
- **Type:** JavaScript
- **Size:** 611 B
- **Capabilities:**
  - The base constructor for creating `lodash` wrapper objects.
- **Methods:** 1

#### _baseRange
- **File:** `frontend-tools/node_modules/lodash/_baseRange.js`
- **Type:** JavaScript
- **Size:** 850 B
- **Capabilities:**
  - The base implementation of `_.range` and `_.rangeRight` which doesn't
- **Methods:** 1

#### core.min
- **File:** `frontend-tools/node_modules/lodash/core.min.js`
- **Type:** JavaScript
- **Size:** 12.39 KB
- **Methods:** 20

#### differenceBy
- **File:** `frontend-tools/node_modules/lodash/differenceBy.js`
- **Type:** JavaScript
- **Size:** 1.49 KB
- **Capabilities:**
  - This method is like `_.difference` except that it accepts `iteratee` which

#### _getWrapDetails
- **File:** `frontend-tools/node_modules/lodash/_getWrapDetails.js`
- **Type:** JavaScript
- **Size:** 479 B
- **Capabilities:**
  - Extracts wrapper details from the `source` body comment.
- **Methods:** 1

#### throttle
- **File:** `frontend-tools/node_modules/lodash/throttle.js`
- **Type:** JavaScript
- **Size:** 2.65 KB
- **Capabilities:**
  - Creates a throttled function that only invokes `func` at most once per
- **Methods:** 5

#### _baseUnary
- **File:** `frontend-tools/node_modules/lodash/_baseUnary.js`
- **Type:** JavaScript
- **Size:** 332 B
- **Capabilities:**
  - The base implementation of `_.unary` without support for storing metadata.
- **Methods:** 2

#### over
- **File:** `frontend-tools/node_modules/lodash/over.js`
- **Type:** JavaScript
- **Size:** 558 B
- **Capabilities:**
  - Creates a function that invokes `iteratees` with the arguments it receives
- **Methods:** 1

#### _baseIsSet
- **File:** `frontend-tools/node_modules/lodash/_baseIsSet.js`
- **Type:** JavaScript
- **Size:** 478 B
- **Capabilities:**
  - The base implementation of `_.isSet` without Node.js optimizations.
- **Methods:** 1

#### divide
- **File:** `frontend-tools/node_modules/lodash/divide.js`
- **Type:** JavaScript
- **Size:** 491 B
- **Capabilities:**
  - Divide two numbers.

#### shuffle
- **File:** `frontend-tools/node_modules/lodash/shuffle.js`
- **Type:** JavaScript
- **Size:** 678 B
- **Capabilities:**
  - Creates an array of shuffled values, using a version of the
- **Methods:** 1

#### flow
- **File:** `frontend-tools/node_modules/lodash/flow.js`
- **Type:** JavaScript
- **Size:** 666 B
- **Capabilities:**
  - Creates a function that returns the result of invoking the given functions
- **Methods:** 2

#### before
- **File:** `frontend-tools/node_modules/lodash/before.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Capabilities:**
  - Creates a function that invokes `func`, with the `this` binding and arguments
- **Methods:** 4

#### _baseIsMap
- **File:** `frontend-tools/node_modules/lodash/_baseIsMap.js`
- **Type:** JavaScript
- **Size:** 478 B
- **Capabilities:**
  - The base implementation of `_.isMap` without Node.js optimizations.
- **Methods:** 1

#### isElement
- **File:** `frontend-tools/node_modules/lodash/isElement.js`
- **Type:** JavaScript
- **Size:** 574 B
- **Capabilities:**
  - Checks if `value` is likely a DOM element.
- **Methods:** 1

#### identity
- **File:** `frontend-tools/node_modules/lodash/identity.js`
- **Type:** JavaScript
- **Size:** 370 B
- **Capabilities:**
  - This method returns the first argument it receives.
- **Methods:** 1

#### _baseIsArrayBuffer
- **File:** `frontend-tools/node_modules/lodash/_baseIsArrayBuffer.js`
- **Type:** JavaScript
- **Size:** 504 B
- **Capabilities:**
  - The base implementation of `_.isArrayBuffer` without Node.js optimizations.
- **Methods:** 1

#### _baseFill
- **File:** `frontend-tools/node_modules/lodash/_baseFill.js`
- **Type:** JavaScript
- **Size:** 843 B
- **Capabilities:**
  - The base implementation of `_.fill` without an iteratee call guard.
- **Methods:** 1

#### indexOf
- **File:** `frontend-tools/node_modules/lodash/indexOf.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - Gets the index at which the first occurrence of `value` is found in `array`
- **Methods:** 1

#### _customOmitClone
- **File:** `frontend-tools/node_modules/lodash/_customOmitClone.js`
- **Type:** JavaScript
- **Size:** 475 B
- **Capabilities:**
  - Used by `_.omit` to customize its `_.cloneDeep` use to only clone plain
- **Methods:** 1

#### split
- **File:** `frontend-tools/node_modules/lodash/split.js`
- **Type:** JavaScript
- **Size:** 1.51 KB
- **Capabilities:**
  - Splits `string` by `separator`.
- **Methods:** 1

#### _updateWrapDetails
- **File:** `frontend-tools/node_modules/lodash/_updateWrapDetails.js`
- **Type:** JavaScript
- **Size:** 1.28 KB
- **Capabilities:**
  - Updates wrapper `details` based on `bitmask` flags.
- **Methods:** 2

#### _arrayShuffle
- **File:** `frontend-tools/node_modules/lodash/_arrayShuffle.js`
- **Type:** JavaScript
- **Size:** 365 B
- **Capabilities:**
  - A specialized version of `_.shuffle` for arrays.
- **Methods:** 1

#### _hashGet
- **File:** `frontend-tools/node_modules/lodash/_hashGet.js`
- **Type:** JavaScript
- **Size:** 772 B
- **Capabilities:**
  - Gets the hash value for `key`.
- **Methods:** 1

#### _listCacheSet
- **File:** `frontend-tools/node_modules/lodash/_listCacheSet.js`
- **Type:** JavaScript
- **Size:** 553 B
- **Capabilities:**
  - Sets the list cache `key` to `value`.
- **Methods:** 1

#### padStart
- **File:** `frontend-tools/node_modules/lodash/padStart.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - Pads `string` on the left side if it's shorter than `length`. Padding
- **Methods:** 1

#### _baseIsTypedArray
- **File:** `frontend-tools/node_modules/lodash/_baseIsTypedArray.js`
- **Type:** JavaScript
- **Size:** 2.17 KB
- **Capabilities:**
  - The base implementation of `_.isTypedArray` without Node.js optimizations.
- **Methods:** 1

#### _toKey
- **File:** `frontend-tools/node_modules/lodash/_toKey.js`
- **Type:** JavaScript
- **Size:** 523 B
- **Capabilities:**
  - Converts `value` to a string key if it's not a string or symbol.
- **Methods:** 1

#### initial
- **File:** `frontend-tools/node_modules/lodash/initial.js`
- **Type:** JavaScript
- **Size:** 461 B
- **Capabilities:**
  - Gets all but the last element of `array`.
- **Methods:** 1

#### once
- **File:** `frontend-tools/node_modules/lodash/once.js`
- **Type:** JavaScript
- **Size:** 665 B
- **Capabilities:**
  - Creates a function that is restricted to invoking `func` once. Repeat calls
- **Methods:** 4

#### chunk
- **File:** `frontend-tools/node_modules/lodash/chunk.js`
- **Type:** JavaScript
- **Size:** 1.38 KB
- **Capabilities:**
  - Creates an array of elements split into groups the length of `size`.
- **Methods:** 1

#### reject
- **File:** `frontend-tools/node_modules/lodash/reject.js`
- **Type:** JavaScript
- **Size:** 1.38 KB
- **Capabilities:**
  - The opposite of `_.filter`; this method returns the elements of `collection`
- **Methods:** 2

#### head
- **File:** `frontend-tools/node_modules/lodash/head.js`
- **Type:** JavaScript
- **Size:** 415 B
- **Capabilities:**
  - Gets the first element of `array`.
- **Methods:** 1

#### isNull
- **File:** `frontend-tools/node_modules/lodash/isNull.js`
- **Type:** JavaScript
- **Size:** 381 B
- **Capabilities:**
  - Checks if `value` is `null`.
- **Methods:** 1

#### slice
- **File:** `frontend-tools/node_modules/lodash/slice.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - Creates a slice of `array` from `start` up to, but not including, `end`.
- **Methods:** 1

#### isNaN
- **File:** `frontend-tools/node_modules/lodash/isNaN.js`
- **Type:** JavaScript
- **Size:** 911 B
- **Capabilities:**
  - Checks if `value` is `NaN`.
- **Methods:** 1

#### toUpper
- **File:** `frontend-tools/node_modules/lodash/toUpper.js`
- **Type:** JavaScript
- **Size:** 592 B
- **Capabilities:**
  - Converts `string`, as a whole, to upper case just like
- **Methods:** 1

#### constant
- **File:** `frontend-tools/node_modules/lodash/constant.js`
- **Type:** JavaScript
- **Size:** 528 B
- **Capabilities:**
  - Creates a function that returns `value`.
- **Methods:** 2

#### isSet
- **File:** `frontend-tools/node_modules/lodash/isSet.js`
- **Type:** JavaScript
- **Size:** 613 B
- **Capabilities:**
  - Checks if `value` is classified as a `Set` object.

#### padEnd
- **File:** `frontend-tools/node_modules/lodash/padEnd.js`
- **Type:** JavaScript
- **Size:** 1017 B
- **Capabilities:**
  - Pads `string` on the right side if it's shorter than `length`. Padding
- **Methods:** 1

#### _baseIndexOfWith
- **File:** `frontend-tools/node_modules/lodash/_baseIndexOfWith.js`
- **Type:** JavaScript
- **Size:** 660 B
- **Capabilities:**
  - This function is like `baseIndexOf` except that it accepts a comparator.
- **Methods:** 2

#### uniqueId
- **File:** `frontend-tools/node_modules/lodash/uniqueId.js`
- **Type:** JavaScript
- **Size:** 562 B
- **Capabilities:**
  - Generates a unique ID. If `prefix` is given, the ID is appended to it.
- **Methods:** 1

#### forOwn
- **File:** `frontend-tools/node_modules/lodash/forOwn.js`
- **Type:** JavaScript
- **Size:** 992 B
- **Capabilities:**
  - Iterates over own enumerable string keyed properties of an object and
- **Methods:** 3

#### unary
- **File:** `frontend-tools/node_modules/lodash/unary.js`
- **Type:** JavaScript
- **Size:** 469 B
- **Capabilities:**
  - Creates a function that accepts up to one argument, ignoring any
- **Methods:** 3

#### _stackClear
- **File:** `frontend-tools/node_modules/lodash/_stackClear.js`
- **Type:** JavaScript
- **Size:** 254 B
- **Capabilities:**
  - Removes all key-value entries from the stack.
- **Methods:** 1

#### parseInt
- **File:** `frontend-tools/node_modules/lodash/parseInt.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - Converts `string` to an integer of the specified radix. If `radix` is
- **Methods:** 1

#### defaults
- **File:** `frontend-tools/node_modules/lodash/defaults.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Capabilities:**
  - Assigns own and inherited enumerable string keyed properties of source

#### _cloneSymbol
- **File:** `frontend-tools/node_modules/lodash/_cloneSymbol.js`
- **Type:** JavaScript
- **Size:** 524 B
- **Capabilities:**
  - Creates a clone of the `symbol` object.
- **Methods:** 1

#### _isPrototype
- **File:** `frontend-tools/node_modules/lodash/_isPrototype.js`
- **Type:** JavaScript
- **Size:** 480 B
- **Capabilities:**
  - Checks if `value` is likely a prototype object.
- **Methods:** 1

#### _wrapperClone
- **File:** `frontend-tools/node_modules/lodash/_wrapperClone.js`
- **Type:** JavaScript
- **Size:** 658 B
- **Capabilities:**
  - Creates a clone of `wrapper`.
- **Methods:** 1

#### hasIn
- **File:** `frontend-tools/node_modules/lodash/hasIn.js`
- **Type:** JavaScript
- **Size:** 753 B
- **Capabilities:**
  - Checks if `path` is a direct or inherited property of `object`.
- **Methods:** 1

#### toLength
- **File:** `frontend-tools/node_modules/lodash/toLength.js`
- **Type:** JavaScript
- **Size:** 868 B
- **Capabilities:**
  - Converts `value` to an integer suitable for use as the length of an
- **Methods:** 1

#### debounce
- **File:** `frontend-tools/node_modules/lodash/debounce.js`
- **Type:** JavaScript
- **Size:** 5.96 KB
- **Capabilities:**
  - Creates a debounced function that delays invoking `func` until after `wait`
- **Methods:** 15

#### _mapCacheDelete
- **File:** `frontend-tools/node_modules/lodash/_mapCacheDelete.js`
- **Type:** JavaScript
- **Size:** 450 B
- **Capabilities:**
  - Removes `key` and its value from the map.
- **Methods:** 1

#### _createAggregator
- **File:** `frontend-tools/node_modules/lodash/_createAggregator.js`
- **Type:** JavaScript
- **Size:** 789 B
- **Capabilities:**
  - Creates a function like `_.groupBy`.
- **Methods:** 3

#### _isIndex
- **File:** `frontend-tools/node_modules/lodash/_isIndex.js`
- **Type:** JavaScript
- **Size:** 759 B
- **Capabilities:**
  - Checks if `value` is a valid array-like index.
- **Methods:** 1

#### _baseFunctions
- **File:** `frontend-tools/node_modules/lodash/_baseFunctions.js`
- **Type:** JavaScript
- **Size:** 552 B
- **Capabilities:**
  - The base implementation of `_.functions` which creates an array of
- **Methods:** 3

#### zipObjectDeep
- **File:** `frontend-tools/node_modules/lodash/zipObjectDeep.js`
- **Type:** JavaScript
- **Size:** 643 B
- **Capabilities:**
  - This method is like `_.zipObject` except that it supports property paths.
- **Methods:** 1

#### startCase
- **File:** `frontend-tools/node_modules/lodash/startCase.js`
- **Type:** JavaScript
- **Size:** 714 B
- **Capabilities:**
  - Converts `string` to

#### _cloneBuffer
- **File:** `frontend-tools/node_modules/lodash/_cloneBuffer.js`
- **Type:** JavaScript
- **Size:** 1.03 KB
- **Capabilities:**
  - Creates a clone of  `buffer`.
- **Methods:** 1

#### _baseAssign
- **File:** `frontend-tools/node_modules/lodash/_baseAssign.js`
- **Type:** JavaScript
- **Size:** 470 B
- **Capabilities:**
  - The base implementation of `_.assign` without support for multiple sources
- **Methods:** 1

#### _baseZipObject
- **File:** `frontend-tools/node_modules/lodash/_baseZipObject.js`
- **Type:** JavaScript
- **Size:** 660 B
- **Capabilities:**
  - This base implementation of `_.zipObject` which assigns values using `assignFunc`.
- **Methods:** 2

#### isPlainObject
- **File:** `frontend-tools/node_modules/lodash/isPlainObject.js`
- **Type:** JavaScript
- **Size:** 1.61 KB
- **Capabilities:**
  - Checks if `value` is a plain object, that is, an object created by the
- **Methods:** 2

#### find
- **File:** `frontend-tools/node_modules/lodash/find.js`
- **Type:** JavaScript
- **Size:** 1.27 KB
- **Capabilities:**
  - Iterates over elements of `collection`, returning the first element
- **Methods:** 1

#### _defineProperty
- **File:** `frontend-tools/node_modules/lodash/_defineProperty.js`
- **Type:** JavaScript
- **Size:** 233 B

#### _baseInverter
- **File:** `frontend-tools/node_modules/lodash/_baseInverter.js`
- **Type:** JavaScript
- **Size:** 736 B
- **Capabilities:**
  - The base implementation of `_.invert` and `_.invertBy` which inverts
- **Methods:** 2

#### lowerCase
- **File:** `frontend-tools/node_modules/lodash/lowerCase.js`
- **Type:** JavaScript
- **Size:** 622 B
- **Capabilities:**
  - Converts `string`, as space separated words, to lower case.

#### _createRange
- **File:** `frontend-tools/node_modules/lodash/_createRange.js`
- **Type:** JavaScript
- **Size:** 864 B
- **Capabilities:**
  - Creates a `_.range` or `_.rangeRight` function.
- **Methods:** 1

#### thru
- **File:** `frontend-tools/node_modules/lodash/thru.js`
- **Type:** JavaScript
- **Size:** 674 B
- **Capabilities:**
  - This method is like `_.tap` except that it returns the result of `interceptor`.
- **Methods:** 2

#### _composeArgs
- **File:** `frontend-tools/node_modules/lodash/_composeArgs.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Capabilities:**
  - Creates an array that is the composition of partially applied arguments,
- **Methods:** 1

#### difference
- **File:** `frontend-tools/node_modules/lodash/difference.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - Creates an array of `array` values not included in the other given arrays

#### _cloneArrayBuffer
- **File:** `frontend-tools/node_modules/lodash/_cloneArrayBuffer.js`
- **Type:** JavaScript
- **Size:** 449 B
- **Capabilities:**
  - Creates a clone of `arrayBuffer`.
- **Methods:** 1

#### camelCase
- **File:** `frontend-tools/node_modules/lodash/camelCase.js`
- **Type:** JavaScript
- **Size:** 701 B
- **Capabilities:**
  - Converts `string` to [camel case](https://en.wikipedia.org/wiki/CamelCase).

#### _baseIsArguments
- **File:** `frontend-tools/node_modules/lodash/_baseIsArguments.js`
- **Type:** JavaScript
- **Size:** 488 B
- **Capabilities:**
  - The base implementation of `_.isArguments`.
- **Methods:** 1

#### _baseMap
- **File:** `frontend-tools/node_modules/lodash/_baseMap.js`
- **Type:** JavaScript
- **Size:** 668 B
- **Capabilities:**
  - The base implementation of `_.map` without support for iteratee shorthands.
- **Methods:** 2

#### sortedLastIndexOf
- **File:** `frontend-tools/node_modules/lodash/sortedLastIndexOf.js`
- **Type:** JavaScript
- **Size:** 770 B
- **Capabilities:**
  - This method is like `_.lastIndexOf` except that it performs a binary
- **Methods:** 1

#### updateWith
- **File:** `frontend-tools/node_modules/lodash/updateWith.js`
- **Type:** JavaScript
- **Size:** 1.16 KB
- **Capabilities:**
  - This method is like `_.update` except that it accepts `customizer` which is
- **Methods:** 2

#### toPairs
- **File:** `frontend-tools/node_modules/lodash/toPairs.js`
- **Type:** JavaScript
- **Size:** 699 B
- **Capabilities:**
  - Creates an array of own enumerable string keyed-value pairs for `object`
- **Methods:** 1

#### _baseAssignIn
- **File:** `frontend-tools/node_modules/lodash/_baseAssignIn.js`
- **Type:** JavaScript
- **Size:** 482 B
- **Capabilities:**
  - The base implementation of `_.assignIn` without support for multiple sources
- **Methods:** 1

#### _toSource
- **File:** `frontend-tools/node_modules/lodash/_toSource.js`
- **Type:** JavaScript
- **Size:** 556 B
- **Capabilities:**
  - Converts `func` to its source code.
- **Methods:** 2

#### _baseExtremum
- **File:** `frontend-tools/node_modules/lodash/_baseExtremum.js`
- **Type:** JavaScript
- **Size:** 897 B
- **Capabilities:**
  - The base implementation of methods like `_.max` and `_.min` which accepts a
- **Methods:** 1

#### _baseShuffle
- **File:** `frontend-tools/node_modules/lodash/_baseShuffle.js`
- **Type:** JavaScript
- **Size:** 371 B
- **Capabilities:**
  - The base implementation of `_.shuffle`.
- **Methods:** 1

#### isObject
- **File:** `frontend-tools/node_modules/lodash/isObject.js`
- **Type:** JavaScript
- **Size:** 733 B
- **Capabilities:**
  - Checks if `value` is the
- **Methods:** 1

#### deburr
- **File:** `frontend-tools/node_modules/lodash/deburr.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Used to match [combining diacritical marks](https://en.wikipedia.org/wiki/Combining_Diacritical_Marks) and
  - Deburrs `string` by converting
- **Methods:** 1

#### sortedLastIndexBy
- **File:** `frontend-tools/node_modules/lodash/sortedLastIndexBy.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Capabilities:**
  - This method is like `_.sortedLastIndex` except that it accepts `iteratee`
- **Methods:** 1

#### take
- **File:** `frontend-tools/node_modules/lodash/take.js`
- **Type:** JavaScript
- **Size:** 851 B
- **Capabilities:**
  - Creates a slice of `array` with `n` elements taken from the beginning.
- **Methods:** 1

#### times
- **File:** `frontend-tools/node_modules/lodash/times.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Invokes the iteratee `n` times, returning an array of the results of
- **Methods:** 2

#### _createFlow
- **File:** `frontend-tools/node_modules/lodash/_createFlow.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Capabilities:**
  - Creates a `_.flow` or `_.flowRight` function.
- **Methods:** 2

#### mean
- **File:** `frontend-tools/node_modules/lodash/mean.js`
- **Type:** JavaScript
- **Size:** 422 B
- **Capabilities:**
  - Computes the mean of the values in `array`.
- **Methods:** 1

#### mixin
- **File:** `frontend-tools/node_modules/lodash/mixin.js`
- **Type:** JavaScript
- **Size:** 2.18 KB
- **Capabilities:**
  - Adds all own enumerable string keyed function properties of a source
- **Methods:** 4

#### flattenDepth
- **File:** `frontend-tools/node_modules/lodash/flattenDepth.js`
- **Type:** JavaScript
- **Size:** 787 B
- **Capabilities:**
  - Recursively flatten `array` up to `depth` times.
- **Methods:** 1

#### findIndex
- **File:** `frontend-tools/node_modules/lodash/findIndex.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - This method is like `_.find` except that it returns the index of the first
- **Methods:** 2

#### _castFunction
- **File:** `frontend-tools/node_modules/lodash/_castFunction.js`
- **Type:** JavaScript
- **Size:** 326 B
- **Capabilities:**
  - Casts `value` to `identity` if it's not a function.
- **Methods:** 1

#### _countHolders
- **File:** `frontend-tools/node_modules/lodash/_countHolders.js`
- **Type:** JavaScript
- **Size:** 469 B
- **Capabilities:**
  - Gets the number of `placeholder` occurrences in `array`.
- **Methods:** 1

#### cloneDeep
- **File:** `frontend-tools/node_modules/lodash/cloneDeep.js`
- **Type:** JavaScript
- **Size:** 679 B
- **Capabilities:**
  - This method is like `_.clone` except that it recursively clones `value`.
- **Methods:** 1

#### truncate
- **File:** `frontend-tools/node_modules/lodash/truncate.js`
- **Type:** JavaScript
- **Size:** 3.28 KB
- **Capabilities:**
  - Truncates `string` if it's longer than the given maximum string length.
- **Methods:** 1

#### _createPadding
- **File:** `frontend-tools/node_modules/lodash/_createPadding.js`
- **Type:** JavaScript
- **Size:** 1.13 KB
- **Capabilities:**
  - Creates the padding for `string` based on `length`. The `chars` string
- **Methods:** 1

#### _baseIsDate
- **File:** `frontend-tools/node_modules/lodash/_baseIsDate.js`
- **Type:** JavaScript
- **Size:** 504 B
- **Capabilities:**
  - The base implementation of `_.isDate` without Node.js optimizations.
- **Methods:** 1

#### reduceRight
- **File:** `frontend-tools/node_modules/lodash/reduceRight.js`
- **Type:** JavaScript
- **Size:** 1.13 KB
- **Capabilities:**
  - This method is like `_.reduce` except that it iterates over elements of
- **Methods:** 2

#### _hasPath
- **File:** `frontend-tools/node_modules/lodash/_hasPath.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Capabilities:**
  - Checks if `path` exists on `object`.
- **Methods:** 2

#### matchesProperty
- **File:** `frontend-tools/node_modules/lodash/matchesProperty.js`
- **Type:** JavaScript
- **Size:** 1.42 KB
- **Capabilities:**
  - Creates a function that performs a partial deep comparison between the
- **Methods:** 2

#### _baseReduce
- **File:** `frontend-tools/node_modules/lodash/_baseReduce.js`
- **Type:** JavaScript
- **Size:** 909 B
- **Capabilities:**
  - The base implementation of `_.reduce` and `_.reduceRight`, without support
- **Methods:** 3

#### _ListCache
- **File:** `frontend-tools/node_modules/lodash/_ListCache.js`
- **Type:** JavaScript
- **Size:** 869 B
- **Capabilities:**
  - Creates an list cache object.
- **Methods:** 1

#### _realNames
- **File:** `frontend-tools/node_modules/lodash/_realNames.js`
- **Type:** JavaScript
- **Size:** 98 B
- **Methods:** 1

#### sortBy
- **File:** `frontend-tools/node_modules/lodash/sortBy.js`
- **Type:** JavaScript
- **Size:** 1.63 KB
- **Capabilities:**
  - Creates an array of elements, sorted in ascending order by the results of

#### replace
- **File:** `frontend-tools/node_modules/lodash/replace.js`
- **Type:** JavaScript
- **Size:** 754 B
- **Capabilities:**
  - Replaces matches for `pattern` in `string` with `replacement`.
- **Methods:** 1

#### cond
- **File:** `frontend-tools/node_modules/lodash/cond.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Creates a function that iterates over `pairs` and invokes the corresponding
- **Methods:** 4

#### result
- **File:** `frontend-tools/node_modules/lodash/result.js`
- **Type:** JavaScript
- **Size:** 1.43 KB
- **Capabilities:**
  - This method is like `_.get` except that if the resolved value is a
- **Methods:** 2

#### _setToArray
- **File:** `frontend-tools/node_modules/lodash/_setToArray.js`
- **Type:** JavaScript
- **Size:** 345 B
- **Capabilities:**
  - Converts `set` to an array of its values.
- **Methods:** 1

#### subtract
- **File:** `frontend-tools/node_modules/lodash/subtract.js`
- **Type:** JavaScript
- **Size:** 511 B
- **Capabilities:**
  - Subtract two numbers.

#### nthArg
- **File:** `frontend-tools/node_modules/lodash/nthArg.js`
- **Type:** JavaScript
- **Size:** 730 B
- **Capabilities:**
  - Creates a function that gets the argument at index `n`. If `n` is negative,
- **Methods:** 2

#### forInRight
- **File:** `frontend-tools/node_modules/lodash/forInRight.js`
- **Type:** JavaScript
- **Size:** 929 B
- **Capabilities:**
  - This method is like `_.forIn` except that it iterates over properties of
- **Methods:** 3

#### stubFalse
- **File:** `frontend-tools/node_modules/lodash/stubFalse.js`
- **Type:** JavaScript
- **Size:** 280 B
- **Capabilities:**
  - This method returns `false`.
- **Methods:** 1

#### _baseSample
- **File:** `frontend-tools/node_modules/lodash/_baseSample.js`
- **Type:** JavaScript
- **Size:** 359 B
- **Capabilities:**
  - The base implementation of `_.sample`.
- **Methods:** 1

#### _baseIntersection
- **File:** `frontend-tools/node_modules/lodash/_baseIntersection.js`
- **Type:** JavaScript
- **Size:** 2.21 KB
- **Capabilities:**
  - The base implementation of methods like `_.intersection`, without support
- **Methods:** 1

#### mapKeys
- **File:** `frontend-tools/node_modules/lodash/mapKeys.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - The opposite of `_.mapValues`; this method creates an object with the
- **Methods:** 2

#### _SetCache
- **File:** `frontend-tools/node_modules/lodash/_SetCache.js`
- **Type:** JavaScript
- **Size:** 632 B
- **Capabilities:**
  - * Creates an array cache object to store unique values.
- **Methods:** 1

#### _baseCreate
- **File:** `frontend-tools/node_modules/lodash/_baseCreate.js`
- **Type:** JavaScript
- **Size:** 686 B
- **Capabilities:**
  - The base implementation of `_.create` without support for assigning
- **Methods:** 1

#### commit
- **File:** `frontend-tools/node_modules/lodash/commit.js`
- **Type:** JavaScript
- **Size:** 641 B
- **Capabilities:**
  - Executes the chain sequence and returns the wrapped result.
- **Methods:** 1

#### assign
- **File:** `frontend-tools/node_modules/lodash/assign.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Capabilities:**
  - Assigns own enumerable string keyed properties of source objects to the
- **Methods:** 2

#### toPlainObject
- **File:** `frontend-tools/node_modules/lodash/toPlainObject.js`
- **Type:** JavaScript
- **Size:** 744 B
- **Capabilities:**
  - Converts `value` to a plain object flattening inherited enumerable string
- **Methods:** 2

#### trimEnd
- **File:** `frontend-tools/node_modules/lodash/trimEnd.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - Removes trailing whitespace or specified characters from `string`.
- **Methods:** 1

#### _getAllKeysIn
- **File:** `frontend-tools/node_modules/lodash/_getAllKeysIn.js`
- **Type:** JavaScript
- **Size:** 488 B
- **Capabilities:**
  - Creates an array of own and inherited enumerable property names and
- **Methods:** 1

#### _assocIndexOf
- **File:** `frontend-tools/node_modules/lodash/_assocIndexOf.js`
- **Type:** JavaScript
- **Size:** 487 B
- **Capabilities:**
  - Gets the index at which the `key` is found in `array` of key-value pairs.
- **Methods:** 1

#### _insertWrapDetails
- **File:** `frontend-tools/node_modules/lodash/_insertWrapDetails.js`
- **Type:** JavaScript
- **Size:** 748 B
- **Capabilities:**
  - Inserts wrapper `details` in a comment at the top of the `source` body.
- **Methods:** 1

#### _baseMatches
- **File:** `frontend-tools/node_modules/lodash/_baseMatches.js`
- **Type:** JavaScript
- **Size:** 710 B
- **Capabilities:**
  - The base implementation of `_.matches` which doesn't clone `source`.
- **Methods:** 1

#### _copyObject
- **File:** `frontend-tools/node_modules/lodash/_copyObject.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Copies properties of `source` to `object`.
- **Methods:** 2

#### isSafeInteger
- **File:** `frontend-tools/node_modules/lodash/isSafeInteger.js`
- **Type:** JavaScript
- **Size:** 949 B
- **Capabilities:**
  - Checks if `value` is a safe integer. An integer is safe if it's an IEEE-754
- **Methods:** 1

#### _baseRandom
- **File:** `frontend-tools/node_modules/lodash/_baseRandom.js`
- **Type:** JavaScript
- **Size:** 541 B
- **Capabilities:**
  - The base implementation of `_.random` without support for returning
- **Methods:** 1

#### _unicodeSize
- **File:** `frontend-tools/node_modules/lodash/_unicodeSize.js`
- **Type:** JavaScript
- **Size:** 1.6 KB
- **Capabilities:**
  - Gets the size of a Unicode `string`.
- **Methods:** 1

#### _shortOut
- **File:** `frontend-tools/node_modules/lodash/_shortOut.js`
- **Type:** JavaScript
- **Size:** 941 B
- **Capabilities:**
  - Creates a function that'll short out and invoke `identity` instead
- **Methods:** 3

#### _baseIteratee
- **File:** `frontend-tools/node_modules/lodash/_baseIteratee.js`
- **Type:** JavaScript
- **Size:** 895 B
- **Capabilities:**
  - The base implementation of `_.iteratee`.
- **Methods:** 1

#### isFunction
- **File:** `frontend-tools/node_modules/lodash/isFunction.js`
- **Type:** JavaScript
- **Size:** 993 B
- **Capabilities:**
  - Checks if `value` is classified as a `Function` object.
- **Methods:** 1

#### max
- **File:** `frontend-tools/node_modules/lodash/max.js`
- **Type:** JavaScript
- **Size:** 614 B
- **Capabilities:**
  - Computes the maximum value of `array`. If `array` is empty or falsey,
- **Methods:** 1

#### partition
- **File:** `frontend-tools/node_modules/lodash/partition.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - Creates an array of elements split into two groups, the first of which
- **Methods:** 1

#### zipObject
- **File:** `frontend-tools/node_modules/lodash/zipObject.js`
- **Type:** JavaScript
- **Size:** 664 B
- **Capabilities:**
  - This method is like `_.fromPairs` except that it accepts two arrays,
- **Methods:** 1

#### _mapCacheGet
- **File:** `frontend-tools/node_modules/lodash/_mapCacheGet.js`
- **Type:** JavaScript
- **Size:** 330 B
- **Capabilities:**
  - Gets the map value for `key`.
- **Methods:** 1

#### omitBy
- **File:** `frontend-tools/node_modules/lodash/omitBy.js`
- **Type:** JavaScript
- **Size:** 854 B
- **Capabilities:**
  - The opposite of `_.pickBy`; this method creates an object composed of
- **Methods:** 2

#### _setToString
- **File:** `frontend-tools/node_modules/lodash/_setToString.js`
- **Type:** JavaScript
- **Size:** 392 B
- **Capabilities:**
  - Sets the `toString` method of `func` to return `string`.
- **Methods:** 1

#### intersectionWith
- **File:** `frontend-tools/node_modules/lodash/intersectionWith.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - This method is like `_.intersection` except that it accepts `comparator`

#### propertyOf
- **File:** `frontend-tools/node_modules/lodash/propertyOf.js`
- **Type:** JavaScript
- **Size:** 732 B
- **Capabilities:**
  - The opposite of `_.property`; this method creates a function that returns
- **Methods:** 2

#### isArrayLike
- **File:** `frontend-tools/node_modules/lodash/isArrayLike.js`
- **Type:** JavaScript
- **Size:** 830 B
- **Capabilities:**
  - Checks if `value` is array-like. A value is considered array-like if it's
- **Methods:** 2

#### _asciiToArray
- **File:** `frontend-tools/node_modules/lodash/_asciiToArray.js`
- **Type:** JavaScript
- **Size:** 257 B
- **Capabilities:**
  - Converts an ASCII `string` to an array.
- **Methods:** 1

#### filter
- **File:** `frontend-tools/node_modules/lodash/filter.js`
- **Type:** JavaScript
- **Size:** 1.64 KB
- **Capabilities:**
  - Iterates over elements of `collection`, returning an array of all elements
- **Methods:** 2

#### _baseForRight
- **File:** `frontend-tools/node_modules/lodash/_baseForRight.js`
- **Type:** JavaScript
- **Size:** 477 B
- **Capabilities:**
  - This function is like `baseFor` except that it iterates over properties
- **Methods:** 3

#### flatMapDepth
- **File:** `frontend-tools/node_modules/lodash/flatMapDepth.js`
- **Type:** JavaScript
- **Size:** 901 B
- **Capabilities:**
  - This method is like `_.flatMap` except that it recursively flattens the
- **Methods:** 3

#### multiply
- **File:** `frontend-tools/node_modules/lodash/multiply.js`
- **Type:** JavaScript
- **Size:** 530 B
- **Capabilities:**
  - Multiply two numbers.

#### _baseEvery
- **File:** `frontend-tools/node_modules/lodash/_baseEvery.js`
- **Type:** JavaScript
- **Size:** 625 B
- **Capabilities:**
  - The base implementation of `_.every` without support for iteratee shorthands.
- **Methods:** 2

#### toString
- **File:** `frontend-tools/node_modules/lodash/toString.js`
- **Type:** JavaScript
- **Size:** 580 B
- **Capabilities:**
  - Converts `value` to a string. An empty string is returned for `null`
- **Methods:** 1

#### random
- **File:** `frontend-tools/node_modules/lodash/random.js`
- **Type:** JavaScript
- **Size:** 2.32 KB
- **Capabilities:**
  - Produces a random number between the inclusive `lower` and `upper` bounds.
- **Methods:** 1

#### isString
- **File:** `frontend-tools/node_modules/lodash/isString.js`
- **Type:** JavaScript
- **Size:** 723 B
- **Capabilities:**
  - Checks if `value` is classified as a `String` primitive or object.
- **Methods:** 1

#### noop
- **File:** `frontend-tools/node_modules/lodash/noop.js`
- **Type:** JavaScript
- **Size:** 250 B
- **Capabilities:**
  - This method returns `undefined`.
- **Methods:** 1

#### util
- **File:** `frontend-tools/node_modules/lodash/util.js`
- **Type:** JavaScript
- **Size:** 1.15 KB

#### endsWith
- **File:** `frontend-tools/node_modules/lodash/endsWith.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - Checks if `string` ends with the given target string.
- **Methods:** 1

#### plant
- **File:** `frontend-tools/node_modules/lodash/plant.js`
- **Type:** JavaScript
- **Size:** 1016 B
- **Capabilities:**
  - Creates a clone of the chain sequence planting `value` as the wrapped value.
- **Methods:** 2

#### findLastKey
- **File:** `frontend-tools/node_modules/lodash/findLastKey.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - This method is like `_.findKey` except that it iterates over elements of
- **Methods:** 2

#### _arrayEachRight
- **File:** `frontend-tools/node_modules/lodash/_arrayEachRight.js`
- **Type:** JavaScript
- **Size:** 528 B
- **Capabilities:**
  - A specialized version of `_.forEachRight` for arrays without support for
- **Methods:** 2

#### _createCaseFirst
- **File:** `frontend-tools/node_modules/lodash/_createCaseFirst.js`
- **Type:** JavaScript
- **Size:** 811 B
- **Capabilities:**
  - Creates a function like `_.lowerFirst`.
- **Methods:** 2

#### _apply
- **File:** `frontend-tools/node_modules/lodash/_apply.js`
- **Type:** JavaScript
- **Size:** 714 B
- **Capabilities:**
  - A faster alternative to `Function#apply`, this function invokes `func`
- **Methods:** 3

#### _hashHas
- **File:** `frontend-tools/node_modules/lodash/_hashHas.js`
- **Type:** JavaScript
- **Size:** 626 B
- **Capabilities:**
  - Checks if a hash value for `key` exists.
- **Methods:** 1

#### toLower
- **File:** `frontend-tools/node_modules/lodash/toLower.js`
- **Type:** JavaScript
- **Size:** 592 B
- **Capabilities:**
  - Converts `string`, as a whole, to lower case just like
- **Methods:** 1

#### mergeWith
- **File:** `frontend-tools/node_modules/lodash/mergeWith.js`
- **Type:** JavaScript
- **Size:** 1.22 KB
- **Capabilities:**
  - This method is like `_.merge` except that it accepts `customizer` which
- **Methods:** 2

#### _createBaseFor
- **File:** `frontend-tools/node_modules/lodash/_createBaseFor.js`
- **Type:** JavaScript
- **Size:** 648 B
- **Capabilities:**
  - Creates a base function for methods like `_.forIn` and `_.forOwn`.
- **Methods:** 2

#### _baseFilter
- **File:** `frontend-tools/node_modules/lodash/_baseFilter.js`
- **Type:** JavaScript
- **Size:** 590 B
- **Capabilities:**
  - The base implementation of `_.filter` without support for iteratee shorthands.
- **Methods:** 2

#### _isKeyable
- **File:** `frontend-tools/node_modules/lodash/_isKeyable.js`
- **Type:** JavaScript
- **Size:** 430 B
- **Capabilities:**
  - Checks if `value` is suitable for use as unique object key.
- **Methods:** 1

#### _getView
- **File:** `frontend-tools/node_modules/lodash/_getView.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - Gets the view, applying any `transforms` to the `start` and `end` positions.
- **Methods:** 1

#### without
- **File:** `frontend-tools/node_modules/lodash/without.js`
- **Type:** JavaScript
- **Size:** 858 B
- **Capabilities:**
  - Creates an array excluding all given values using

#### unescape
- **File:** `frontend-tools/node_modules/lodash/unescape.js`
- **Type:** JavaScript
- **Size:** 1.03 KB
- **Capabilities:**
  - The inverse of `_.escape`; this method converts the HTML entities
- **Methods:** 1

#### inRange
- **File:** `frontend-tools/node_modules/lodash/inRange.js`
- **Type:** JavaScript
- **Size:** 1.22 KB
- **Capabilities:**
  - Checks if `n` is between `start` and up to, but not including, `end`. If
- **Methods:** 1

#### _stringSize
- **File:** `frontend-tools/node_modules/lodash/_stringSize.js`
- **Type:** JavaScript
- **Size:** 432 B
- **Capabilities:**
  - Gets the number of symbols in `string`.
- **Methods:** 1

#### isFinite
- **File:** `frontend-tools/node_modules/lodash/isFinite.js`
- **Type:** JavaScript
- **Size:** 793 B
- **Capabilities:**
  - Checks if `value` is a finite primitive number.
- **Methods:** 1

#### _arrayMap
- **File:** `frontend-tools/node_modules/lodash/_arrayMap.js`
- **Type:** JavaScript
- **Size:** 556 B
- **Capabilities:**
  - A specialized version of `_.map` for arrays without support for iteratee
- **Methods:** 2

#### overSome
- **File:** `frontend-tools/node_modules/lodash/overSome.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - Creates a function that checks if **any** of the `predicates` return
- **Methods:** 1

#### pullAllBy
- **File:** `frontend-tools/node_modules/lodash/pullAllBy.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - This method is like `_.pullAll` except that it accepts `iteratee` which is
- **Methods:** 1

#### capitalize
- **File:** `frontend-tools/node_modules/lodash/capitalize.js`
- **Type:** JavaScript
- **Size:** 529 B
- **Capabilities:**
  - Converts the first character of `string` to upper case and the remaining
- **Methods:** 1

#### _baseKeys
- **File:** `frontend-tools/node_modules/lodash/_baseKeys.js`
- **Type:** JavaScript
- **Size:** 776 B
- **Capabilities:**
  - The base implementation of `_.keys` which doesn't treat sparse arrays as dense.
- **Methods:** 1

#### _isStrictComparable
- **File:** `frontend-tools/node_modules/lodash/_isStrictComparable.js`
- **Type:** JavaScript
- **Size:** 414 B
- **Capabilities:**
  - Checks if `value` is suitable for strict equality comparisons, i.e. `===`.
- **Methods:** 1

#### _basePullAt
- **File:** `frontend-tools/node_modules/lodash/_basePullAt.js`
- **Type:** JavaScript
- **Size:** 939 B
- **Capabilities:**
  - The base implementation of `_.pullAt` without support for individual
- **Methods:** 1

#### toArray
- **File:** `frontend-tools/node_modules/lodash/toArray.js`
- **Type:** JavaScript
- **Size:** 1.37 KB
- **Capabilities:**
  - Converts `value` to an array.
- **Methods:** 1

#### _lazyValue
- **File:** `frontend-tools/node_modules/lodash/_lazyValue.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Capabilities:**
  - Extracts the unwrapped value from its lazy wrapper.
- **Methods:** 1

#### create
- **File:** `frontend-tools/node_modules/lodash/create.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - Creates an object that inherits from the `prototype` object. If a
- **Methods:** 3

#### set
- **File:** `frontend-tools/node_modules/lodash/set.js`
- **Type:** JavaScript
- **Size:** 960 B
- **Capabilities:**
  - Sets the value at `path` of `object`. If a portion of `path` doesn't exist,
- **Methods:** 1

#### unionWith
- **File:** `frontend-tools/node_modules/lodash/unionWith.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - This method is like `_.union` except that it accepts `comparator` which

#### now
- **File:** `frontend-tools/node_modules/lodash/now.js`
- **Type:** JavaScript
- **Size:** 520 B
- **Capabilities:**
  - Gets the timestamp of the number of milliseconds that have elapsed since

#### pick
- **File:** `frontend-tools/node_modules/lodash/pick.js`
- **Type:** JavaScript
- **Size:** 629 B
- **Capabilities:**
  - Creates an object composed of the picked `object` properties.

#### _createFind
- **File:** `frontend-tools/node_modules/lodash/_createFind.js`
- **Type:** JavaScript
- **Size:** 853 B
- **Capabilities:**
  - Creates a `_.find` or `_.findLast` function.
- **Methods:** 2

#### lastIndexOf
- **File:** `frontend-tools/node_modules/lodash/lastIndexOf.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - This method is like `_.indexOf` except that it iterates over elements of
- **Methods:** 1

#### isLength
- **File:** `frontend-tools/node_modules/lodash/isLength.js`
- **Type:** JavaScript
- **Size:** 802 B
- **Capabilities:**
  - Checks if `value` is a valid array-like length.
- **Methods:** 1

#### sumBy
- **File:** `frontend-tools/node_modules/lodash/sumBy.js`
- **Type:** JavaScript
- **Size:** 908 B
- **Capabilities:**
  - This method is like `_.sum` except that it accepts `iteratee` which is
- **Methods:** 1

#### _createAssigner
- **File:** `frontend-tools/node_modules/lodash/_createAssigner.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Creates a function like `_.assign`.
- **Methods:** 3

#### dropWhile
- **File:** `frontend-tools/node_modules/lodash/dropWhile.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - Creates a slice of `array` excluding elements dropped from the beginning.
- **Methods:** 2

#### _createBaseEach
- **File:** `frontend-tools/node_modules/lodash/_createBaseEach.js`
- **Type:** JavaScript
- **Size:** 886 B
- **Capabilities:**
  - Creates a `baseEach` or `baseEachRight` function.
- **Methods:** 2

#### _getData
- **File:** `frontend-tools/node_modules/lodash/_getData.js`
- **Type:** JavaScript
- **Size:** 325 B
- **Capabilities:**
  - Gets metadata for `func`.
- **Methods:** 1

#### _baseUnset
- **File:** `frontend-tools/node_modules/lodash/_baseUnset.js`
- **Type:** JavaScript
- **Size:** 580 B
- **Capabilities:**
  - The base implementation of `_.unset`.
- **Methods:** 1

#### defaultTo
- **File:** `frontend-tools/node_modules/lodash/defaultTo.js`
- **Type:** JavaScript
- **Size:** 608 B
- **Capabilities:**
  - Checks `value` to determine whether a default value should be returned in
- **Methods:** 1

#### every
- **File:** `frontend-tools/node_modules/lodash/every.js`
- **Type:** JavaScript
- **Size:** 1.83 KB
- **Capabilities:**
  - Checks if `predicate` returns truthy for **all** elements of `collection`.
- **Methods:** 2

#### _castPath
- **File:** `frontend-tools/node_modules/lodash/_castPath.js`
- **Type:** JavaScript
- **Size:** 569 B
- **Capabilities:**
  - Casts `value` to a path array if it's not one.
- **Methods:** 1

#### nth
- **File:** `frontend-tools/node_modules/lodash/nth.js`
- **Type:** JavaScript
- **Size:** 671 B
- **Capabilities:**
  - Gets the element at index `n` of `array`. If `n` is negative, the nth
- **Methods:** 1

#### _basePullAll
- **File:** `frontend-tools/node_modules/lodash/_basePullAll.js`
- **Type:** JavaScript
- **Size:** 1.42 KB
- **Capabilities:**
  - The base implementation of `_.pullAllBy` without support for iteratee
- **Methods:** 1

#### _baseDelay
- **File:** `frontend-tools/node_modules/lodash/_baseDelay.js`
- **Type:** JavaScript
- **Size:** 672 B
- **Capabilities:**
  - The base implementation of `_.delay` and `_.defer` which accepts `args`
- **Methods:** 2

#### _initCloneByTag
- **File:** `frontend-tools/node_modules/lodash/_initCloneByTag.js`
- **Type:** JavaScript
- **Size:** 2.21 KB
- **Capabilities:**
  - Initializes an object clone based on its `toStringTag`.
- **Methods:** 2

#### orderBy
- **File:** `frontend-tools/node_modules/lodash/orderBy.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - This method is like `_.sortBy` except that it allows specifying the sort
- **Methods:** 1

#### _baseLodash
- **File:** `frontend-tools/node_modules/lodash/_baseLodash.js`
- **Type:** JavaScript
- **Size:** 178 B
- **Capabilities:**
  - The function whose prototype chain sequence wrappers inherit from.
- **Methods:** 2

#### takeRightWhile
- **File:** `frontend-tools/node_modules/lodash/takeRightWhile.js`
- **Type:** JavaScript
- **Size:** 1.34 KB
- **Capabilities:**
  - Creates a slice of `array` with elements taken from the end. Elements are
- **Methods:** 2

#### defer
- **File:** `frontend-tools/node_modules/lodash/defer.js`
- **Type:** JavaScript
- **Size:** 693 B
- **Capabilities:**
  - Defers invoking the `func` until the current call stack has cleared. Any
- **Methods:** 1

#### partialRight
- **File:** `frontend-tools/node_modules/lodash/partialRight.js`
- **Type:** JavaScript
- **Size:** 1.52 KB
- **Capabilities:**
  - This method is like `_.partial` except that partially applied arguments
- **Methods:** 3

#### _basePropertyOf
- **File:** `frontend-tools/node_modules/lodash/_basePropertyOf.js`
- **Type:** JavaScript
- **Size:** 358 B
- **Capabilities:**
  - The base implementation of `_.propertyOf` without support for deep paths.
- **Methods:** 1

#### tail
- **File:** `frontend-tools/node_modules/lodash/tail.js`
- **Type:** JavaScript
- **Size:** 457 B
- **Capabilities:**
  - Gets all but the first element of `array`.
- **Methods:** 1

#### _cacheHas
- **File:** `frontend-tools/node_modules/lodash/_cacheHas.js`
- **Type:** JavaScript
- **Size:** 337 B
- **Capabilities:**
  - Checks if a `cache` value for `key` exists.
- **Methods:** 1

#### _reorder
- **File:** `frontend-tools/node_modules/lodash/_reorder.js`
- **Type:** JavaScript
- **Size:** 900 B
- **Capabilities:**
  - Reorder `array` according to the specified indexes where the element at
- **Methods:** 1

#### _initCloneArray
- **File:** `frontend-tools/node_modules/lodash/_initCloneArray.js`
- **Type:** JavaScript
- **Size:** 692 B
- **Capabilities:**
  - Initializes an array clone.
- **Methods:** 1

#### _baseOrderBy
- **File:** `frontend-tools/node_modules/lodash/_baseOrderBy.js`
- **Type:** JavaScript
- **Size:** 1.52 KB
- **Capabilities:**
  - The base implementation of `_.orderBy` without param guards.
- **Methods:** 1

#### _getHolder
- **File:** `frontend-tools/node_modules/lodash/_getHolder.js`
- **Type:** JavaScript
- **Size:** 280 B
- **Capabilities:**
  - Gets the argument placeholder value for `func`.
- **Methods:** 2

#### _baseGt
- **File:** `frontend-tools/node_modules/lodash/_baseGt.js`
- **Type:** JavaScript
- **Size:** 357 B
- **Capabilities:**
  - The base implementation of `_.gt` which doesn't coerce arguments.
- **Methods:** 1

#### pullAllWith
- **File:** `frontend-tools/node_modules/lodash/pullAllWith.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - This method is like `_.pullAll` except that it accepts `comparator` which
- **Methods:** 1

#### some
- **File:** `frontend-tools/node_modules/lodash/some.js`
- **Type:** JavaScript
- **Size:** 1.57 KB
- **Capabilities:**
  - Checks if `predicate` returns truthy for **any** element of `collection`.
- **Methods:** 2

#### _baseSortBy
- **File:** `frontend-tools/node_modules/lodash/_baseSortBy.js`
- **Type:** JavaScript
- **Size:** 543 B
- **Capabilities:**
  - The base implementation of `_.sortBy` which uses `comparer` to define the
- **Methods:** 2

#### _arrayLikeKeys
- **File:** `frontend-tools/node_modules/lodash/_arrayLikeKeys.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - Creates an array of the enumerable property names of the array-like `value`.
- **Methods:** 1

#### _safeGet
- **File:** `frontend-tools/node_modules/lodash/_safeGet.js`
- **Type:** JavaScript
- **Size:** 456 B
- **Capabilities:**
  - Gets the value at `key`, unless `key` is "__proto__" or "constructor".
- **Methods:** 1

#### sortedUniq
- **File:** `frontend-tools/node_modules/lodash/sortedUniq.js`
- **Type:** JavaScript
- **Size:** 513 B
- **Capabilities:**
  - This method is like `_.uniq` except that it's designed and optimized
- **Methods:** 1

#### defaultsDeep
- **File:** `frontend-tools/node_modules/lodash/defaultsDeep.js`
- **Type:** JavaScript
- **Size:** 839 B
- **Capabilities:**
  - This method is like `_.defaults` except that it recursively assigns

#### property
- **File:** `frontend-tools/node_modules/lodash/property.js`
- **Type:** JavaScript
- **Size:** 793 B
- **Capabilities:**
  - Creates a function that returns the value at `path` of a given object.
- **Methods:** 2

#### findLast
- **File:** `frontend-tools/node_modules/lodash/findLast.js`
- **Type:** JavaScript
- **Size:** 730 B
- **Capabilities:**
  - This method is like `_.find` except that it iterates over elements of
- **Methods:** 1

#### isSymbol
- **File:** `frontend-tools/node_modules/lodash/isSymbol.js`
- **Type:** JavaScript
- **Size:** 682 B
- **Capabilities:**
  - Checks if `value` is classified as a `Symbol` primitive or object.
- **Methods:** 1

#### _createCtor
- **File:** `frontend-tools/node_modules/lodash/_createCtor.js`
- **Type:** JavaScript
- **Size:** 1.45 KB
- **Capabilities:**
  - Creates a function that produces an instance of `Ctor` regardless of
- **Methods:** 2

#### _getMapData
- **File:** `frontend-tools/node_modules/lodash/_getMapData.js`
- **Type:** JavaScript
- **Size:** 400 B
- **Capabilities:**
  - Gets the data for `map`.
- **Methods:** 1

#### _matchesStrictComparable
- **File:** `frontend-tools/node_modules/lodash/_matchesStrictComparable.js`
- **Type:** JavaScript
- **Size:** 574 B
- **Capabilities:**
  - A specialized version of `matchesProperty` for source values suitable
- **Methods:** 1

#### _createPartial
- **File:** `frontend-tools/node_modules/lodash/_createPartial.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - Creates a function that wraps `func` to invoke it with the `this` binding
- **Methods:** 5

#### _getAllKeys
- **File:** `frontend-tools/node_modules/lodash/_getAllKeys.js`
- **Type:** JavaScript
- **Size:** 455 B
- **Capabilities:**
  - Creates an array of own enumerable property names and symbols of `object`.
- **Methods:** 1

#### _stackSet
- **File:** `frontend-tools/node_modules/lodash/_stackSet.js`
- **Type:** JavaScript
- **Size:** 853 B
- **Capabilities:**
  - Sets the stack `key` to `value`.
- **Methods:** 1

#### assignInWith
- **File:** `frontend-tools/node_modules/lodash/assignInWith.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - This method is like `_.assignIn` except that it accepts `customizer`
- **Methods:** 2

#### wrapperChain
- **File:** `frontend-tools/node_modules/lodash/wrapperChain.js`
- **Type:** JavaScript
- **Size:** 706 B
- **Capabilities:**
  - Creates a `lodash` wrapper instance with explicit method chain sequences enabled.
- **Methods:** 1

#### snakeCase
- **File:** `frontend-tools/node_modules/lodash/snakeCase.js`
- **Type:** JavaScript
- **Size:** 638 B
- **Capabilities:**
  - Converts `string` to

#### merge
- **File:** `frontend-tools/node_modules/lodash/merge.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - This method is like `_.assign` except that it recursively merges own and

#### _baseIndexOf
- **File:** `frontend-tools/node_modules/lodash/_baseIndexOf.js`
- **Type:** JavaScript
- **Size:** 659 B
- **Capabilities:**
  - The base implementation of `_.indexOf` without `fromIndex` bounds checks.
- **Methods:** 1

#### _setData
- **File:** `frontend-tools/node_modules/lodash/_setData.js`
- **Type:** JavaScript
- **Size:** 645 B
- **Capabilities:**
  - Sets metadata for `func`.
- **Methods:** 2

#### takeWhile
- **File:** `frontend-tools/node_modules/lodash/takeWhile.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - Creates a slice of `array` with elements taken from the beginning. Elements
- **Methods:** 2

#### reverse
- **File:** `frontend-tools/node_modules/lodash/reverse.js`
- **Type:** JavaScript
- **Size:** 844 B
- **Capabilities:**
  - Reverses `array` so that the first element becomes the last, the second
- **Methods:** 1

#### matches
- **File:** `frontend-tools/node_modules/lodash/matches.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Creates a function that performs a partial deep comparison between a given
- **Methods:** 3

#### unzip
- **File:** `frontend-tools/node_modules/lodash/unzip.js`
- **Type:** JavaScript
- **Size:** 1.25 KB
- **Capabilities:**
  - This method is like `_.zip` except that it accepts an array of grouped
- **Methods:** 1

#### _baseFlatten
- **File:** `frontend-tools/node_modules/lodash/_baseFlatten.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Capabilities:**
  - The base implementation of `_.flatten` with support for restricting flattening.
- **Methods:** 2

#### _initCloneObject
- **File:** `frontend-tools/node_modules/lodash/_initCloneObject.js`
- **Type:** JavaScript
- **Size:** 486 B
- **Capabilities:**
  - Initializes an object clone.
- **Methods:** 1

#### _baseGetAllKeys
- **File:** `frontend-tools/node_modules/lodash/_baseGetAllKeys.js`
- **Type:** JavaScript
- **Size:** 739 B
- **Capabilities:**
  - The base implementation of `getAllKeys` and `getAllKeysIn` which uses
- **Methods:** 2

#### _stringToArray
- **File:** `frontend-tools/node_modules/lodash/_stringToArray.js`
- **Type:** JavaScript
- **Size:** 450 B
- **Capabilities:**
  - Converts `string` to an array.
- **Methods:** 1

#### _isIterateeCall
- **File:** `frontend-tools/node_modules/lodash/_isIterateeCall.js`
- **Type:** JavaScript
- **Size:** 877 B
- **Capabilities:**
  - Checks if the given arguments are from an iteratee call.
- **Methods:** 1

#### pullAt
- **File:** `frontend-tools/node_modules/lodash/pullAt.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Capabilities:**
  - Removes elements from `array` corresponding to `indexes` and returns an

#### _arrayReduceRight
- **File:** `frontend-tools/node_modules/lodash/_arrayReduceRight.js`
- **Type:** JavaScript
- **Size:** 777 B
- **Capabilities:**
  - A specialized version of `_.reduceRight` for arrays without support for
- **Methods:** 2

#### _hasUnicodeWord
- **File:** `frontend-tools/node_modules/lodash/_hasUnicodeWord.js`
- **Type:** JavaScript
- **Size:** 491 B
- **Capabilities:**
  - Checks if `string` contains a word composed of Unicode symbols.
- **Methods:** 1

#### _nodeUtil
- **File:** `frontend-tools/node_modules/lodash/_nodeUtil.js`
- **Type:** JavaScript
- **Size:** 995 B

#### isObjectLike
- **File:** `frontend-tools/node_modules/lodash/isObjectLike.js`
- **Type:** JavaScript
- **Size:** 614 B
- **Capabilities:**
  - Checks if `value` is object-like. A value is object-like if it's not `null`
- **Methods:** 1

#### _createBind
- **File:** `frontend-tools/node_modules/lodash/_createBind.js`
- **Type:** JavaScript
- **Size:** 853 B
- **Capabilities:**
  - Creates a function that wraps `func` to invoke it with the optional `this`
- **Methods:** 5

#### dropRightWhile
- **File:** `frontend-tools/node_modules/lodash/dropRightWhile.js`
- **Type:** JavaScript
- **Size:** 1.38 KB
- **Capabilities:**
  - Creates a slice of `array` excluding elements dropped from the end.
- **Methods:** 2

#### groupBy
- **File:** `frontend-tools/node_modules/lodash/groupBy.js`
- **Type:** JavaScript
- **Size:** 1.37 KB
- **Capabilities:**
  - Creates an object composed of keys generated from the results of running

#### _mergeData
- **File:** `frontend-tools/node_modules/lodash/_mergeData.js`
- **Type:** JavaScript
- **Size:** 3.06 KB
- **Capabilities:**
  - Merges the function metadata of `source` into `data`.
- **Methods:** 3

#### _listCacheHas
- **File:** `frontend-tools/node_modules/lodash/_listCacheHas.js`
- **Type:** JavaScript
- **Size:** 403 B
- **Capabilities:**
  - Checks if a list cache value for `key` exists.
- **Methods:** 1

#### _createInverter
- **File:** `frontend-tools/node_modules/lodash/_createInverter.js`
- **Type:** JavaScript
- **Size:** 497 B
- **Capabilities:**
  - Creates a function like `_.invertBy`.
- **Methods:** 3

#### _mapToArray
- **File:** `frontend-tools/node_modules/lodash/_mapToArray.js`
- **Type:** JavaScript
- **Size:** 363 B
- **Capabilities:**
  - Converts `map` to its key-value pairs.
- **Methods:** 1

#### _baseMergeDeep
- **File:** `frontend-tools/node_modules/lodash/_baseMergeDeep.js`
- **Type:** JavaScript
- **Size:** 3 KB
- **Capabilities:**
  - A specialized version of `baseMerge` for arrays and objects which performs
- **Methods:** 2

#### min
- **File:** `frontend-tools/node_modules/lodash/min.js`
- **Type:** JavaScript
- **Size:** 614 B
- **Capabilities:**
  - Computes the minimum value of `array`. If `array` is empty or falsey,
- **Methods:** 1

#### _castArrayLikeObject
- **File:** `frontend-tools/node_modules/lodash/_castArrayLikeObject.js`
- **Type:** JavaScript
- **Size:** 381 B
- **Capabilities:**
  - Casts `value` to an empty array if it's not an array like object.
- **Methods:** 1

#### _baseClone
- **File:** `frontend-tools/node_modules/lodash/_baseClone.js`
- **Type:** JavaScript
- **Size:** 5.48 KB
- **Capabilities:**
  - The base implementation of `_.clone` and `_.cloneDeep` which tracks
- **Methods:** 2

#### _baseSum
- **File:** `frontend-tools/node_modules/lodash/_baseSum.js`
- **Type:** JavaScript
- **Size:** 600 B
- **Capabilities:**
  - The base implementation of `_.sum` and `_.sumBy` without support for
- **Methods:** 2

#### xor
- **File:** `frontend-tools/node_modules/lodash/xor.js`
- **Type:** JavaScript
- **Size:** 811 B
- **Capabilities:**
  - Creates an array of unique values that is the

#### _baseInRange
- **File:** `frontend-tools/node_modules/lodash/_baseInRange.js`
- **Type:** JavaScript
- **Size:** 612 B
- **Capabilities:**
  - The base implementation of `_.inRange` which doesn't coerce arguments.
- **Methods:** 1

#### flowRight
- **File:** `frontend-tools/node_modules/lodash/flowRight.js`
- **Type:** JavaScript
- **Size:** 590 B
- **Capabilities:**
  - This method is like `_.flow` except that it creates a function that
- **Methods:** 2

#### _arrayAggregator
- **File:** `frontend-tools/node_modules/lodash/_arrayAggregator.js`
- **Type:** JavaScript
- **Size:** 684 B
- **Capabilities:**
  - A specialized version of `baseAggregator` for arrays.
- **Methods:** 2

#### _hashClear
- **File:** `frontend-tools/node_modules/lodash/_hashClear.js`
- **Type:** JavaScript
- **Size:** 281 B
- **Capabilities:**
  - Removes all key-value entries from the hash.
- **Methods:** 1

#### differenceWith
- **File:** `frontend-tools/node_modules/lodash/differenceWith.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - This method is like `_.difference` except that it accepts `comparator`

#### _createOver
- **File:** `frontend-tools/node_modules/lodash/_createOver.js`
- **Type:** JavaScript
- **Size:** 780 B
- **Capabilities:**
  - Creates a function like `_.over`.
- **Methods:** 3

#### _createCompounder
- **File:** `frontend-tools/node_modules/lodash/_createCompounder.js`
- **Type:** JavaScript
- **Size:** 635 B
- **Capabilities:**
  - Creates a function like `_.camelCase`.
- **Methods:** 3

#### _isMasked
- **File:** `frontend-tools/node_modules/lodash/_isMasked.js`
- **Type:** JavaScript
- **Size:** 564 B
- **Capabilities:**
  - Checks if `func` has its source masked.
- **Methods:** 2

#### _arrayIncludesWith
- **File:** `frontend-tools/node_modules/lodash/_arrayIncludesWith.js`
- **Type:** JavaScript
- **Size:** 615 B
- **Capabilities:**
  - This function is like `arrayIncludes` except that it accepts a comparator.
- **Methods:** 2

#### _castSlice
- **File:** `frontend-tools/node_modules/lodash/_castSlice.js`
- **Type:** JavaScript
- **Size:** 517 B
- **Capabilities:**
  - Casts `array` to a slice if it's needed.
- **Methods:** 1

#### dropRight
- **File:** `frontend-tools/node_modules/lodash/dropRight.js`
- **Type:** JavaScript
- **Size:** 927 B
- **Capabilities:**
  - Creates a slice of `array` with `n` elements dropped from the end.
- **Methods:** 1

#### _setCacheAdd
- **File:** `frontend-tools/node_modules/lodash/_setCacheAdd.js`
- **Type:** JavaScript
- **Size:** 424 B
- **Capabilities:**
  - Adds `value` to the array cache.
- **Methods:** 1

#### add
- **File:** `frontend-tools/node_modules/lodash/add.js`
- **Type:** JavaScript
- **Size:** 469 B
- **Capabilities:**
  - Adds two numbers.

#### update
- **File:** `frontend-tools/node_modules/lodash/update.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - This method is like `_.set` except that accepts `updater` to produce the
- **Methods:** 2

#### _hashSet
- **File:** `frontend-tools/node_modules/lodash/_hashSet.js`
- **Type:** JavaScript
- **Size:** 598 B
- **Capabilities:**
  - Sets the hash `key` to `value`.
- **Methods:** 1

#### _escapeStringChar
- **File:** `frontend-tools/node_modules/lodash/_escapeStringChar.js`
- **Type:** JavaScript
- **Size:** 521 B
- **Capabilities:**
  - Used by `_.template` to escape characters for inclusion in compiled string literals.
- **Methods:** 1

#### _baseMean
- **File:** `frontend-tools/node_modules/lodash/_baseMean.js`
- **Type:** JavaScript
- **Size:** 568 B
- **Capabilities:**
  - The base implementation of `_.mean` and `_.meanBy` without support for
- **Methods:** 2

#### isTypedArray
- **File:** `frontend-tools/node_modules/lodash/isTypedArray.js`
- **Type:** JavaScript
- **Size:** 695 B
- **Capabilities:**
  - Checks if `value` is classified as a typed array.

#### spread
- **File:** `frontend-tools/node_modules/lodash/spread.js`
- **Type:** JavaScript
- **Size:** 1.69 KB
- **Capabilities:**
  - Creates a function that invokes `func` with the `this` binding of the
- **Methods:** 4

#### includes
- **File:** `frontend-tools/node_modules/lodash/includes.js`
- **Type:** JavaScript
- **Size:** 1.73 KB
- **Capabilities:**
  - Checks if `value` is in `collection`. If `collection` is a string, it's
- **Methods:** 1

#### isMatch
- **File:** `frontend-tools/node_modules/lodash/isMatch.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Performs a partial deep comparison between `object` and `source` to
- **Methods:** 1

#### wrapperValue
- **File:** `frontend-tools/node_modules/lodash/wrapperValue.js`
- **Type:** JavaScript
- **Size:** 455 B
- **Capabilities:**
  - Executes the chain sequence to resolve the unwrapped value.
- **Methods:** 1

#### _composeArgsRight
- **File:** `frontend-tools/node_modules/lodash/_composeArgsRight.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - This function is like `composeArgs` except that the arguments composition
- **Methods:** 2

#### delay
- **File:** `frontend-tools/node_modules/lodash/delay.js`
- **Type:** JavaScript
- **Size:** 795 B
- **Capabilities:**
  - Invokes `func` after `wait` milliseconds. Any additional arguments are
- **Methods:** 1

#### trimStart
- **File:** `frontend-tools/node_modules/lodash/trimStart.js`
- **Type:** JavaScript
- **Size:** 1.2 KB
- **Capabilities:**
  - Removes leading whitespace or specified characters from `string`.
- **Methods:** 1

#### _overArg
- **File:** `frontend-tools/node_modules/lodash/_overArg.js`
- **Type:** JavaScript
- **Size:** 382 B
- **Capabilities:**
  - Creates a unary function that invokes `func` with its argument transformed.
- **Methods:** 3

#### _baseWrapperValue
- **File:** `frontend-tools/node_modules/lodash/_baseWrapperValue.js`
- **Type:** JavaScript
- **Size:** 857 B
- **Capabilities:**
  - The base implementation of `wrapperValue` which returns the result of
- **Methods:** 1

#### words
- **File:** `frontend-tools/node_modules/lodash/words.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - Splits `string` into an array of its words.
- **Methods:** 1

#### bindKey
- **File:** `frontend-tools/node_modules/lodash/bindKey.js`
- **Type:** JavaScript
- **Size:** 2.02 KB
- **Capabilities:**
  - Creates a function that invokes the method at `object[key]` with `partials`
- **Methods:** 2

#### curry
- **File:** `frontend-tools/node_modules/lodash/curry.js`
- **Type:** JavaScript
- **Size:** 1.61 KB
- **Capabilities:**
  - Creates a function that accepts arguments of `func` and either invokes
- **Methods:** 4

#### transform
- **File:** `frontend-tools/node_modules/lodash/transform.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - An alternative to `_.reduce`; this method transforms `object` to a new
- **Methods:** 2

#### invokeMap
- **File:** `frontend-tools/node_modules/lodash/invokeMap.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Invokes the method at `path` of each element in `collection`, returning
- **Methods:** 1

#### isNative
- **File:** `frontend-tools/node_modules/lodash/isNative.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - Checks if `value` is a pristine native function.
- **Methods:** 1

#### iteratee
- **File:** `frontend-tools/node_modules/lodash/iteratee.js`
- **Type:** JavaScript
- **Size:** 1.66 KB
- **Capabilities:**
  - Creates a function that invokes `func` with the arguments of the created
- **Methods:** 3

#### _baseSortedUniq
- **File:** `frontend-tools/node_modules/lodash/_baseSortedUniq.js`
- **Type:** JavaScript
- **Size:** 758 B
- **Capabilities:**
  - The base implementation of `_.sortedUniq` and `_.sortedUniqBy` without
- **Methods:** 1

#### curryRight
- **File:** `frontend-tools/node_modules/lodash/curryRight.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - This method is like `_.curry` except that arguments are applied to `func`
- **Methods:** 3

#### keys
- **File:** `frontend-tools/node_modules/lodash/keys.js`
- **Type:** JavaScript
- **Size:** 884 B
- **Capabilities:**
  - Creates an array of the own enumerable property names of `object`.
- **Methods:** 2

#### values
- **File:** `frontend-tools/node_modules/lodash/values.js`
- **Type:** JavaScript
- **Size:** 733 B
- **Capabilities:**
  - Creates an array of the own enumerable string keyed property values of `object`.
- **Methods:** 2

#### omit
- **File:** `frontend-tools/node_modules/lodash/omit.js`
- **Type:** JavaScript
- **Size:** 1.59 KB
- **Capabilities:**
  - The opposite of `_.pick`; this method creates an object composed of the

#### remove
- **File:** `frontend-tools/node_modules/lodash/remove.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - Removes all elements from `array` that `predicate` returns truthy for
- **Methods:** 2

#### escape
- **File:** `frontend-tools/node_modules/lodash/escape.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Converts the characters "&", "<", ">", '"', and "'" in `string` to their
- **Methods:** 1

#### overEvery
- **File:** `frontend-tools/node_modules/lodash/overEvery.js`
- **Type:** JavaScript
- **Size:** 920 B
- **Capabilities:**
  - Creates a function that checks if **all** of the `predicates` return
- **Methods:** 1

#### _arrayReduce
- **File:** `frontend-tools/node_modules/lodash/_arrayReduce.js`
- **Type:** JavaScript
- **Size:** 787 B
- **Capabilities:**
  - A specialized version of `_.reduce` for arrays without support for
- **Methods:** 2

#### _shuffleSelf
- **File:** `frontend-tools/node_modules/lodash/_shuffleSelf.js`
- **Type:** JavaScript
- **Size:** 689 B
- **Capabilities:**
  - A specialized version of `_.shuffle` which mutates and sets the size of `array`.
- **Methods:** 1

#### unset
- **File:** `frontend-tools/node_modules/lodash/unset.js`
- **Type:** JavaScript
- **Size:** 804 B
- **Capabilities:**
  - Removes the property at `path` of `object`.
- **Methods:** 1

#### _basePickBy
- **File:** `frontend-tools/node_modules/lodash/_basePickBy.js`
- **Type:** JavaScript
- **Size:** 791 B
- **Capabilities:**
  - The base implementation of  `_.pickBy` without support for iteratee shorthands.
- **Methods:** 2

#### _metaMap
- **File:** `frontend-tools/node_modules/lodash/_metaMap.js`
- **Type:** JavaScript
- **Size:** 143 B
- **Methods:** 1

#### _arraySample
- **File:** `frontend-tools/node_modules/lodash/_arraySample.js`
- **Type:** JavaScript
- **Size:** 363 B
- **Capabilities:**
  - A specialized version of `_.sample` for arrays.
- **Methods:** 1

#### _baseTimes
- **File:** `frontend-tools/node_modules/lodash/_baseTimes.js`
- **Type:** JavaScript
- **Size:** 504 B
- **Capabilities:**
  - The base implementation of `_.times` without support for iteratee shorthands
- **Methods:** 2

#### map
- **File:** `frontend-tools/node_modules/lodash/map.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Creates an array of values by running each element in `collection` thru
- **Methods:** 3

#### conformsTo
- **File:** `frontend-tools/node_modules/lodash/conformsTo.js`
- **Type:** JavaScript
- **Size:** 954 B
- **Capabilities:**
  - Checks if `object` conforms to `source` by invoking the predicate
- **Methods:** 1

#### _arrayIncludes
- **File:** `frontend-tools/node_modules/lodash/_arrayIncludes.js`
- **Type:** JavaScript
- **Size:** 526 B
- **Capabilities:**
  - A specialized version of `_.includes` for arrays without support for
- **Methods:** 1

#### _createRound
- **File:** `frontend-tools/node_modules/lodash/_createRound.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Capabilities:**
  - Creates a function like `_.round`.
- **Methods:** 2

#### valuesIn
- **File:** `frontend-tools/node_modules/lodash/valuesIn.js`
- **Type:** JavaScript
- **Size:** 723 B
- **Capabilities:**
  - Creates an array of the own and inherited enumerable string keyed property
- **Methods:** 2

#### _mapCacheClear
- **File:** `frontend-tools/node_modules/lodash/_mapCacheClear.js`
- **Type:** JavaScript
- **Size:** 393 B
- **Capabilities:**
  - Removes all key-value entries from the map.
- **Methods:** 1

#### _baseSlice
- **File:** `frontend-tools/node_modules/lodash/_baseSlice.js`
- **Type:** JavaScript
- **Size:** 756 B
- **Capabilities:**
  - The base implementation of `_.slice` without an iteratee call guard.
- **Methods:** 1

#### _trimmedEndIndex
- **File:** `frontend-tools/node_modules/lodash/_trimmedEndIndex.js`
- **Type:** JavaScript
- **Size:** 515 B
- **Capabilities:**
  - Used by `_.trim` and `_.trimEnd` to get the index of the last non-whitespace
- **Methods:** 1

#### _baseClamp
- **File:** `frontend-tools/node_modules/lodash/_baseClamp.js`
- **Type:** JavaScript
- **Size:** 571 B
- **Capabilities:**
  - The base implementation of `_.clamp` which doesn't coerce arguments.
- **Methods:** 1

#### _createRecurry
- **File:** `frontend-tools/node_modules/lodash/_createRecurry.js`
- **Type:** JavaScript
- **Size:** 2.07 KB
- **Capabilities:**
  - Creates a function that wraps `func` to continue currying.
- **Methods:** 4

#### toPath
- **File:** `frontend-tools/node_modules/lodash/toPath.js`
- **Type:** JavaScript
- **Size:** 804 B
- **Capabilities:**
  - Converts `value` to a property path array.
- **Methods:** 1

#### _assignMergeValue
- **File:** `frontend-tools/node_modules/lodash/_assignMergeValue.js`
- **Type:** JavaScript
- **Size:** 582 B
- **Capabilities:**
  - This function is like `assignValue` except that it doesn't assign
- **Methods:** 2

#### concat
- **File:** `frontend-tools/node_modules/lodash/concat.js`
- **Type:** JavaScript
- **Size:** 1007 B
- **Capabilities:**
  - Creates a new array concatenating `array` with any additional arrays
- **Methods:** 1

#### _baseConforms
- **File:** `frontend-tools/node_modules/lodash/_baseConforms.js`
- **Type:** JavaScript
- **Size:** 484 B
- **Capabilities:**
  - The base implementation of `_.conforms` which doesn't clone `source`.
- **Methods:** 1

#### keyBy
- **File:** `frontend-tools/node_modules/lodash/keyBy.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Capabilities:**
  - Creates an object composed of keys generated from the results of running

#### invertBy
- **File:** `frontend-tools/node_modules/lodash/invertBy.js`
- **Type:** JavaScript
- **Size:** 1.61 KB
- **Capabilities:**
  - Used to resolve the
  - This method is like `_.invert` except that the inverted object is generated

#### findLastIndex
- **File:** `frontend-tools/node_modules/lodash/findLastIndex.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Capabilities:**
  - This method is like `_.findIndex` except that it iterates over elements
- **Methods:** 2

#### functions
- **File:** `frontend-tools/node_modules/lodash/functions.js`
- **Type:** JavaScript
- **Size:** 685 B
- **Capabilities:**
  - Creates an array of function property names from own enumerable properties
- **Methods:** 4

#### intersectionBy
- **File:** `frontend-tools/node_modules/lodash/intersectionBy.js`
- **Type:** JavaScript
- **Size:** 1.43 KB
- **Capabilities:**
  - This method is like `_.intersection` except that it accepts `iteratee`

#### countBy
- **File:** `frontend-tools/node_modules/lodash/countBy.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - Creates an object composed of keys generated from the results of running

#### _isKey
- **File:** `frontend-tools/node_modules/lodash/_isKey.js`
- **Type:** JavaScript
- **Size:** 880 B
- **Capabilities:**
  - Checks if `value` is a property name and not a property path.
- **Methods:** 1

#### invert
- **File:** `frontend-tools/node_modules/lodash/invert.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Capabilities:**
  - Used to resolve the
  - Creates an object composed of the inverted keys and values of `object`.

#### _MapCache
- **File:** `frontend-tools/node_modules/lodash/_MapCache.js`
- **Type:** JavaScript
- **Size:** 869 B
- **Capabilities:**
  - Creates a map cache object to store key-value pairs.
- **Methods:** 1

#### _baseGetTag
- **File:** `frontend-tools/node_modules/lodash/_baseGetTag.js`
- **Type:** JavaScript
- **Size:** 792 B
- **Capabilities:**
  - The base implementation of `getTag` without fallbacks for buggy environments.
- **Methods:** 1

#### _baseIsEqualDeep
- **File:** `frontend-tools/node_modules/lodash/_baseIsEqualDeep.js`
- **Type:** JavaScript
- **Size:** 2.94 KB
- **Capabilities:**
  - A specialized version of `baseIsEqual` for arrays and objects which performs
- **Methods:** 2

#### gte
- **File:** `frontend-tools/node_modules/lodash/gte.js`
- **Type:** JavaScript
- **Size:** 635 B
- **Capabilities:**
  - Checks if `value` is greater than or equal to `other`.

#### _getNative
- **File:** `frontend-tools/node_modules/lodash/_getNative.js`
- **Type:** JavaScript
- **Size:** 483 B
- **Capabilities:**
  - Gets the native function at `key` of `object`.
- **Methods:** 3

#### _getTag
- **File:** `frontend-tools/node_modules/lodash/_getTag.js`
- **Type:** JavaScript
- **Size:** 1.79 KB
- **Capabilities:**
  - Gets the `toStringTag` of `value`.

#### _stackDelete
- **File:** `frontend-tools/node_modules/lodash/_stackDelete.js`
- **Type:** JavaScript
- **Size:** 405 B
- **Capabilities:**
  - Removes `key` and its value from the stack.
- **Methods:** 1

#### _baseTrim
- **File:** `frontend-tools/node_modules/lodash/_baseTrim.js`
- **Type:** JavaScript
- **Size:** 444 B
- **Capabilities:**
  - The base implementation of `_.trim`.
- **Methods:** 1

#### assignWith
- **File:** `frontend-tools/node_modules/lodash/assignWith.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - This method is like `_.assign` except that it accepts `customizer`
- **Methods:** 2

#### escapeRegExp
- **File:** `frontend-tools/node_modules/lodash/escapeRegExp.js`
- **Type:** JavaScript
- **Size:** 871 B
- **Capabilities:**
  - Used to match `RegExp`
  - Escapes the `RegExp` special characters "^", "$", "\", ".", "*", "+",
- **Methods:** 1

#### _unicodeWords
- **File:** `frontend-tools/node_modules/lodash/_unicodeWords.js`
- **Type:** JavaScript
- **Size:** 2.99 KB
- **Capabilities:**
  - Splits a Unicode `string` into an array of its words.
- **Methods:** 1

#### clamp
- **File:** `frontend-tools/node_modules/lodash/clamp.js`
- **Type:** JavaScript
- **Size:** 890 B
- **Capabilities:**
  - Clamps `number` within the inclusive `lower` and `upper` bounds.
- **Methods:** 1

#### attempt
- **File:** `frontend-tools/node_modules/lodash/attempt.js`
- **Type:** JavaScript
- **Size:** 931 B
- **Capabilities:**
  - Attempts to invoke `func`, returning either the result or the caught error
- **Methods:** 1

#### mapValues
- **File:** `frontend-tools/node_modules/lodash/mapValues.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - Creates an object with the same keys as `object` and values generated
- **Methods:** 2

#### last
- **File:** `frontend-tools/node_modules/lodash/last.js`
- **Type:** JavaScript
- **Size:** 401 B
- **Capabilities:**
  - Gets the last element of `array`.
- **Methods:** 1

#### _baseAssignValue
- **File:** `frontend-tools/node_modules/lodash/_baseAssignValue.js`
- **Type:** JavaScript
- **Size:** 625 B
- **Capabilities:**
  - The base implementation of `assignValue` and `assignMergeValue` without
- **Methods:** 1

#### _baseAggregator
- **File:** `frontend-tools/node_modules/lodash/_baseAggregator.js`
- **Type:** JavaScript
- **Size:** 746 B
- **Capabilities:**
  - Aggregates elements of `collection` on `accumulator` with keys transformed
- **Methods:** 2

#### union
- **File:** `frontend-tools/node_modules/lodash/union.js`
- **Type:** JavaScript
- **Size:** 749 B
- **Capabilities:**
  - Creates an array of unique values, in order, from all given arrays using

#### flip
- **File:** `frontend-tools/node_modules/lodash/flip.js`
- **Type:** JavaScript
- **Size:** 636 B
- **Capabilities:**
  - Creates a function that invokes `func` with arguments reversed.
- **Methods:** 4

#### _listCacheDelete
- **File:** `frontend-tools/node_modules/lodash/_listCacheDelete.js`
- **Type:** JavaScript
- **Size:** 775 B
- **Capabilities:**
  - Removes `key` and its value from the list cache.
- **Methods:** 1

#### _createToPairs
- **File:** `frontend-tools/node_modules/lodash/_createToPairs.js`
- **Type:** JavaScript
- **Size:** 789 B
- **Capabilities:**
  - Creates a `_.toPairs` or `_.toPairsIn` function.
- **Methods:** 2

#### stubString
- **File:** `frontend-tools/node_modules/lodash/stubString.js`
- **Type:** JavaScript
- **Size:** 290 B
- **Capabilities:**
  - This method returns an empty string.
- **Methods:** 1

#### compact
- **File:** `frontend-tools/node_modules/lodash/compact.js`
- **Type:** JavaScript
- **Size:** 681 B
- **Capabilities:**
  - Creates an array with all falsey values removed. The values `false`, `null`,
- **Methods:** 1

#### _baseSetToString
- **File:** `frontend-tools/node_modules/lodash/_baseSetToString.js`
- **Type:** JavaScript
- **Size:** 641 B
- **Capabilities:**
  - The base implementation of `setToString` without support for hot loop shorting.
- **Methods:** 1

#### flatMap
- **File:** `frontend-tools/node_modules/lodash/flatMap.js`
- **Type:** JavaScript
- **Size:** 812 B
- **Capabilities:**
  - Creates a flattened array of values by running each element in `collection`
- **Methods:** 3

#### _baseForOwnRight
- **File:** `frontend-tools/node_modules/lodash/_baseForOwnRight.js`
- **Type:** JavaScript
- **Size:** 486 B
- **Capabilities:**
  - The base implementation of `_.forOwnRight` without support for iteratee shorthands.
- **Methods:** 2

#### sortedIndex
- **File:** `frontend-tools/node_modules/lodash/sortedIndex.js`
- **Type:** JavaScript
- **Size:** 626 B
- **Capabilities:**
  - Uses a binary search to determine the lowest index at which `value`
- **Methods:** 1

#### minBy
- **File:** `frontend-tools/node_modules/lodash/minBy.js`
- **Type:** JavaScript
- **Size:** 991 B
- **Capabilities:**
  - This method is like `_.min` except that it accepts `iteratee` which is
- **Methods:** 1

#### _baseAt
- **File:** `frontend-tools/node_modules/lodash/_baseAt.js`
- **Type:** JavaScript
- **Size:** 569 B
- **Capabilities:**
  - The base implementation of `_.at` without support for individual paths.
- **Methods:** 1

#### _createMathOperation
- **File:** `frontend-tools/node_modules/lodash/_createMathOperation.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Capabilities:**
  - Creates a function that performs a mathematical operation on two values.
- **Methods:** 3

#### flatten
- **File:** `frontend-tools/node_modules/lodash/flatten.js`
- **Type:** JavaScript
- **Size:** 489 B
- **Capabilities:**
  - Flattens `array` a single level deep.
- **Methods:** 1

#### forEach
- **File:** `frontend-tools/node_modules/lodash/forEach.js`
- **Type:** JavaScript
- **Size:** 1.32 KB
- **Capabilities:**
  - Iterates over elements of `collection` and invokes `iteratee` for each element.
- **Methods:** 2

#### _listCacheGet
- **File:** `frontend-tools/node_modules/lodash/_listCacheGet.js`
- **Type:** JavaScript
- **Size:** 420 B
- **Capabilities:**
  - Gets the list cache value for `key`.
- **Methods:** 1

#### _listCacheClear
- **File:** `frontend-tools/node_modules/lodash/_listCacheClear.js`
- **Type:** JavaScript
- **Size:** 218 B
- **Capabilities:**
  - Removes all key-value entries from the list cache.
- **Methods:** 1

#### stubTrue
- **File:** `frontend-tools/node_modules/lodash/stubTrue.js`
- **Type:** JavaScript
- **Size:** 272 B
- **Capabilities:**
  - This method returns `true`.
- **Methods:** 1

#### _baseKeysIn
- **File:** `frontend-tools/node_modules/lodash/_baseKeysIn.js`
- **Type:** JavaScript
- **Size:** 870 B
- **Capabilities:**
  - The base implementation of `_.keysIn` which doesn't treat sparse arrays as dense.
- **Methods:** 1

#### _baseIsEqual
- **File:** `frontend-tools/node_modules/lodash/_baseIsEqual.js`
- **Type:** JavaScript
- **Size:** 1019 B
- **Capabilities:**
  - The base implementation of `_.isEqual` which supports partial comparisons
- **Methods:** 2

#### _baseNth
- **File:** `frontend-tools/node_modules/lodash/_baseNth.js`
- **Type:** JavaScript
- **Size:** 483 B
- **Capabilities:**
  - The base implementation of `_.nth` which doesn't coerce arguments.
- **Methods:** 1

#### zipWith
- **File:** `frontend-tools/node_modules/lodash/zipWith.js`
- **Type:** JavaScript
- **Size:** 960 B
- **Capabilities:**
  - This method is like `_.zip` except that it accepts `iteratee` to specify
- **Methods:** 1

#### _baseFindKey
- **File:** `frontend-tools/node_modules/lodash/_baseFindKey.js`
- **Type:** JavaScript
- **Size:** 747 B
- **Capabilities:**
  - The base implementation of methods like `_.findKey` and `_.findLastKey`,
- **Methods:** 3

#### next
- **File:** `frontend-tools/node_modules/lodash/next.js`
- **Type:** JavaScript
- **Size:** 836 B
- **Capabilities:**
  - Gets the next value on a wrapped object following the
- **Methods:** 1

#### sample
- **File:** `frontend-tools/node_modules/lodash/sample.js`
- **Type:** JavaScript
- **Size:** 551 B
- **Capabilities:**
  - Gets a random element from `collection`.
- **Methods:** 1

#### sortedIndexBy
- **File:** `frontend-tools/node_modules/lodash/sortedIndexBy.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - This method is like `_.sortedIndex` except that it accepts `iteratee`
- **Methods:** 1

#### forEachRight
- **File:** `frontend-tools/node_modules/lodash/forEachRight.js`
- **Type:** JavaScript
- **Size:** 924 B
- **Capabilities:**
  - This method is like `_.forEach` except that it iterates over elements of
- **Methods:** 2

#### pack
- **File:** `frontend-tools/node_modules/tar-stream/pack.js`
- **Type:** JavaScript
- **Size:** 6.14 KB
- **Methods:** 5

#### headers
- **File:** `frontend-tools/node_modules/tar-stream/headers.js`
- **Type:** JavaScript
- **Size:** 8.4 KB
- **Methods:** 19

#### extract
- **File:** `frontend-tools/node_modules/tar-stream/extract.js`
- **Type:** JavaScript
- **Size:** 9 KB
- **Methods:** 8

#### source-map
- **File:** `frontend-tools/node_modules/source-map/dist/source-map.js`
- **Type:** JavaScript
- **Size:** 104.47 KB
- **Capabilities:**
  - An instance of the SourceMapGenerator represents a source map which is
  - Creates a new SourceMapGenerator based on a SourceMapConsumer
  - Add a single mapping from original source line and column to the generated
- **Methods:** 20

#### source-map.min
- **File:** `frontend-tools/node_modules/source-map/dist/source-map.min.js`
- **Type:** JavaScript
- **Size:** 26.48 KB
- **Methods:** 18

#### mapping-list
- **File:** `frontend-tools/node_modules/source-map/lib/mapping-list.js`
- **Type:** JavaScript
- **Size:** 2.28 KB
- **Capabilities:**
  - Determine whether mappingB is after mappingA with respect to generated
  - A data structure to provide a sorted view of accumulated mappings in a
  - Iterate through internal items. This method takes the same arguments that
- **Methods:** 5

#### source-map-generator
- **File:** `frontend-tools/node_modules/source-map/lib/source-map-generator.js`
- **Type:** JavaScript
- **Size:** 14.02 KB
- **Capabilities:**
  - An instance of the SourceMapGenerator represents a source map which is
  - Creates a new SourceMapGenerator based on a SourceMapConsumer
  - Add a single mapping from original source line and column to the generated
- **Methods:** 10

#### binary-search
- **File:** `frontend-tools/node_modules/source-map/lib/binary-search.js`
- **Type:** JavaScript
- **Size:** 4.15 KB
- **Capabilities:**
  - Recursive implementation of binary search.
  - This is an implementation of binary search which will always try and return
- **Methods:** 4

#### quick-sort
- **File:** `frontend-tools/node_modules/source-map/lib/quick-sort.js`
- **Type:** JavaScript
- **Size:** 3.53 KB
- **Capabilities:**
  - Swap the elements indexed by `x` and `y` in the array `ary`.
  - Returns a random integer within the range `low .. high` inclusive.
  - The Quick Sort algorithm.
- **Methods:** 5

#### array-set
- **File:** `frontend-tools/node_modules/source-map/lib/array-set.js`
- **Type:** JavaScript
- **Size:** 3.12 KB
- **Capabilities:**
  - A data structure which is a combination of an array and a set. Adding a new
  - Static method for creating ArraySet instances from an existing array.
  - Return how many unique items are in this ArraySet. If duplicates have been
- **Methods:** 8

#### base64
- **File:** `frontend-tools/node_modules/source-map/lib/base64.js`
- **Type:** JavaScript
- **Size:** 1.5 KB
- **Capabilities:**
  - Encode an integer in the range of 0 to 63 to a single base 64 digit.
  - Decode a single base 64 character code digit to an integer. Returns -1 on

#### util
- **File:** `frontend-tools/node_modules/source-map/lib/util.js`
- **Type:** JavaScript
- **Size:** 12.65 KB
- **Capabilities:**
  - This is a helper function for getting values from parameter/options
  - Normalizes a path, or the path portion of a URL:
  - Joins two paths/URLs.
- **Methods:** 17

#### base64-vlq
- **File:** `frontend-tools/node_modules/source-map/lib/base64-vlq.js`
- **Type:** JavaScript
- **Size:** 4.6 KB
- **Capabilities:**
  - Converts from a two-complement value to a value where the sign bit is
  - Converts to a two-complement value from a value where the sign bit is
  - Returns the base 64 VLQ encoded value.
- **Methods:** 4

#### source-map-consumer
- **File:** `frontend-tools/node_modules/source-map/lib/source-map-consumer.js`
- **Type:** JavaScript
- **Size:** 39.61 KB
- **Capabilities:**
  - The version of the source mapping spec that we are consuming.
  - Parse the mappings in a string in to a data structure which we can easily
  - Iterate over each mapping between an original source/line/column and a
- **Methods:** 20

#### source-node
- **File:** `frontend-tools/node_modules/source-map/lib/source-node.js`
- **Type:** JavaScript
- **Size:** 13.48 KB
- **Capabilities:**
  - SourceNodes provide a way to abstract over interpolating/concatenating
  - Creates a SourceNode from generated code and a SourceMapConsumer.
  - Add a chunk of generated JS to this source node.
- **Methods:** 14

#### common
- **File:** `frontend-tools/node_modules/debug/src/common.js`
- **Type:** JavaScript
- **Size:** 6.75 KB
- **Capabilities:**
  - This is the common logic for both the Node.js and web browser
  - The currently active debug mode names, and names to skip.
  - Map of special "%n" handling functions, for the debug "format" argument.
- **Methods:** 11

#### browser
- **File:** `frontend-tools/node_modules/debug/src/browser.js`
- **Type:** JavaScript
- **Size:** 5.96 KB
- **Capabilities:**
  - This is the web browser implementation of `debug()`.
  - Currently only WebKit-based Web Inspectors, Firefox >= v31,
  - Colorize log arguments if enabled.
- **Methods:** 5

#### node
- **File:** `frontend-tools/node_modules/debug/src/node.js`
- **Type:** JavaScript
- **Size:** 4.62 KB
- **Capabilities:**
  - Module dependencies.
  - This is the Node.js implementation of `debug()`.
  - Build up the default `inspectOpts` object from the environment variables.
- **Methods:** 7

#### common
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/node_modules/debug/src/common.js`
- **Type:** JavaScript
- **Size:** 6.14 KB
- **Capabilities:**
  - This is the common logic for both the Node.js and web browser
  - The currently active debug mode names, and names to skip.
  - Map of special "%n" handling functions, for the debug "format" argument.
- **Methods:** 11

#### browser
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/node_modules/debug/src/browser.js`
- **Type:** JavaScript
- **Size:** 5.87 KB
- **Capabilities:**
  - This is the web browser implementation of `debug()`.
  - Currently only WebKit-based Web Inspectors, Firefox >= v31,
  - Colorize log arguments if enabled.
- **Methods:** 5

#### node
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/node_modules/debug/src/node.js`
- **Type:** JavaScript
- **Size:** 4.58 KB
- **Capabilities:**
  - Module dependencies.
  - This is the Node.js implementation of `debug()`.
  - Build up the default `inspectOpts` object from the environment variables.
- **Methods:** 7

#### index
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/node_modules/ms/index.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Parse or format the given `val`.
  - Parse the given `str` and return milliseconds.
  - Short format for `ms`.
- **Methods:** 4

#### detectPlatform
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/CLI.js`
- **Type:** JavaScript
- **Size:** 10.9 KB
- **Methods:** 2

#### install
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/install.js`
- **Type:** JavaScript
- **Size:** 4.14 KB
- **Capabilities:**
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 7

#### types
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 1.56 KB
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 5.93 KB
- **Methods:** 3

#### chrome
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 4.94 KB
- **Methods:** 9

#### firefox
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 11.68 KB
- **Capabilities:**
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 8

#### chromium
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 2.03 KB
- **Methods:** 6

#### fileUtil
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/fileUtil.js`
- **Type:** JavaScript
- **Size:** 2.14 KB
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/httpUtil.js`
- **Type:** JavaScript
- **Size:** 3.8 KB
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/launch.js`
- **Type:** JavaScript
- **Size:** 11.38 KB
- **Methods:** 9

#### Cache
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/esm/Cache.js`
- **Type:** JavaScript
- **Size:** 3.87 KB
- **Capabilities:**
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### detectPlatform
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 1.79 KB
- **Capabilities:**
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/CLI.js`
- **Type:** JavaScript
- **Size:** 12.44 KB
- **Methods:** 2

#### debug
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/debug.js`
- **Type:** JavaScript
- **Size:** 446 B

#### main
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/main.js`
- **Type:** JavaScript
- **Size:** 3.75 KB

#### install
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/install.js`
- **Type:** JavaScript
- **Size:** 5 KB
- **Capabilities:**
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 7

#### types
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Capabilities:**
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 2.3 KB
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 8.41 KB
- **Methods:** 3

#### chrome
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 6.26 KB
- **Methods:** 9

#### firefox
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 12.52 KB
- **Capabilities:**
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 8

#### chromium
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 2.77 KB
- **Methods:** 6

#### fileUtil
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/fileUtil.js`
- **Type:** JavaScript
- **Size:** 3.6 KB
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/httpUtil.js`
- **Type:** JavaScript
- **Size:** 5.16 KB
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/launch.js`
- **Type:** JavaScript
- **Size:** 12.29 KB
- **Methods:** 9

#### Cache
- **File:** `frontend-tools/node_modules/@puppeteer/browsers/lib/cjs/Cache.js`
- **Type:** JavaScript
- **Size:** 4.42 KB
- **Capabilities:**
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/parse-cache-control/index.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Methods:** 1

#### lrucache
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/internal/lrucache.js`
- **Type:** JavaScript
- **Size:** 802 B

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/index.js`
- **Type:** JavaScript
- **Size:** 2.57 KB

#### range
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/classes/range.js`
- **Type:** JavaScript
- **Size:** 14.63 KB
- **Methods:** 1

#### comparator
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/classes/comparator.js`
- **Type:** JavaScript
- **Size:** 3.55 KB

#### semver
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/classes/semver.js`
- **Type:** JavaScript
- **Size:** 9.26 KB

#### satisfies
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/satisfies.js`
- **Type:** JavaScript
- **Size:** 247 B

#### major
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/major.js`
- **Type:** JavaScript
- **Size:** 136 B

#### minor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/minor.js`
- **Type:** JavaScript
- **Size:** 136 B

#### compare-build
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/compare-build.js`
- **Type:** JavaScript
- **Size:** 281 B

#### parse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/parse.js`
- **Type:** JavaScript
- **Size:** 331 B

#### patch
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/patch.js`
- **Type:** JavaScript
- **Size:** 136 B

#### compare
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/compare.js`
- **Type:** JavaScript
- **Size:** 170 B

#### inc
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/inc.js`
- **Type:** JavaScript
- **Size:** 478 B

#### coerce
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/functions/coerce.js`
- **Type:** JavaScript
- **Size:** 1.96 KB

#### valid
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/valid.js`
- **Type:** JavaScript
- **Size:** 326 B

#### max-satisfying
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/max-satisfying.js`
- **Type:** JavaScript
- **Size:** 593 B

#### outside
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/outside.js`
- **Type:** JavaScript
- **Size:** 2.15 KB

#### simplify
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/simplify.js`
- **Type:** JavaScript
- **Size:** 1.32 KB

#### subset
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/subset.js`
- **Type:** JavaScript
- **Size:** 7.35 KB

#### min-satisfying
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/min-satisfying.js`
- **Type:** JavaScript
- **Size:** 591 B

#### to-comparators
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/to-comparators.js`
- **Type:** JavaScript
- **Size:** 282 B

#### intersects
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/intersects.js`
- **Type:** JavaScript
- **Size:** 224 B

#### min-version
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/node_modules/semver/ranges/min-version.js`
- **Type:** JavaScript
- **Size:** 1.48 KB

#### detectPlatform
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/CLI.js`
- **Type:** JavaScript
- **Size:** 13.45 KB
- **Methods:** 2

#### install
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/install.js`
- **Type:** JavaScript
- **Size:** 7.97 KB
- **Capabilities:**
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 9

#### types
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 1.59 KB
- **Capabilities:**
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 1.57 KB
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 1.47 KB
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 8.08 KB
- **Capabilities:**
  - Returns a version comparator for the given browser that can be used to sort
- **Methods:** 5

#### chrome
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 5.35 KB
- **Methods:** 10

#### firefox
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 15.32 KB
- **Capabilities:**
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 12

#### chromium
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 2.11 KB
- **Methods:** 7

#### fileUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/fileUtil.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/httpUtil.js`
- **Type:** JavaScript
- **Size:** 3.94 KB
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/launch.js`
- **Type:** JavaScript
- **Size:** 12.78 KB
- **Methods:** 11

#### Cache
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/esm/Cache.js`
- **Type:** JavaScript
- **Size:** 5.67 KB
- **Capabilities:**
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### detectPlatform
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 1.79 KB
- **Capabilities:**
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/CLI.js`
- **Type:** JavaScript
- **Size:** 15 KB
- **Methods:** 2

#### debug
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/debug.js`
- **Type:** JavaScript
- **Size:** 446 B

#### main
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/main.js`
- **Type:** JavaScript
- **Size:** 3.93 KB

#### install
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/install.js`
- **Type:** JavaScript
- **Size:** 9.15 KB
- **Capabilities:**
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 9

#### types
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 2.35 KB
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 10.95 KB
- **Capabilities:**
  - Returns a version comparator for the given browser that can be used to sort
- **Methods:** 5

#### chrome
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 6.79 KB
- **Methods:** 10

#### firefox
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 16.46 KB
- **Capabilities:**
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 12

#### chromium
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 2.9 KB
- **Methods:** 7

#### fileUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/fileUtil.js`
- **Type:** JavaScript
- **Size:** 4.04 KB
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/httpUtil.js`
- **Type:** JavaScript
- **Size:** 5.3 KB
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/launch.js`
- **Type:** JavaScript
- **Size:** 13.69 KB
- **Methods:** 11

#### Cache
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/@puppeteer/browsers/lib/cjs/Cache.js`
- **Type:** JavaScript
- **Size:** 6.32 KB
- **Capabilities:**
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 5.61 KB
- **Capabilities:**
  - Shorthands for built-in supported types.
  - Supported proxy types.
  - Uses the appropriate `Agent` subclass based off of the "proxy"
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/https-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.28 KB
- **Capabilities:**
  - The `HttpsProxyAgent` implements an HTTP Agent subclass that connects to
  - Called when the node-core HTTP client library is creating a
- **Methods:** 2

#### parse-proxy-response
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/https-proxy-agent/dist/parse-proxy-response.js`
- **Type:** JavaScript
- **Size:** 3.82 KB
- **Methods:** 6

#### validation
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/validation.js`
- **Type:** JavaScript
- **Size:** 2.44 KB
- **Capabilities:**
  - Checks if a status code is allowed in a close frame.
  - Checks if a given buffer contains only correct UTF-8.
- **Methods:** 2

#### permessage-deflate
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/permessage-deflate.js`
- **Type:** JavaScript
- **Size:** 13.98 KB
- **Capabilities:**
  - permessage-deflate implementation.
  - Creates a PerMessageDeflate instance.
  - Create an extension negotiation offer.
- **Methods:** 3

#### limiter
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/limiter.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - A very simple job queue with adjustable concurrency. Adapted from
  - Creates a new `Limiter`.
  - Adds a job to the queue.

#### websocket-server
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/websocket-server.js`
- **Type:** JavaScript
- **Size:** 12.29 KB
- **Capabilities:**
  - Class representing a WebSocket server.
  - Create a `WebSocketServer` instance.
  - Returns the bound address, the address family name, and port of the server
- **Methods:** 7

#### event-target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/event-target.js`
- **Type:** JavaScript
- **Size:** 4.29 KB
- **Capabilities:**
  - Class representing an event.
  - Create a new `Event`.
  - Class representing a message event.
- **Methods:** 4

#### extension
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/extension.js`
- **Type:** JavaScript
- **Size:** 6.72 KB
- **Capabilities:**
  - Adds an offer to the map of extension offers or a parameter to the map of
  - Parses the `Sec-WebSocket-Extensions` header into an object.
  - Builds the `Sec-WebSocket-Extensions` header field value.
- **Methods:** 3

#### sender
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/sender.js`
- **Type:** JavaScript
- **Size:** 10.57 KB
- **Capabilities:**
  - HyBi Sender implementation.
  - Creates a Sender instance.
  - Frames a piece of data according to the HyBi WebSocket protocol.

#### websocket
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/websocket.js`
- **Type:** JavaScript
- **Size:** 30.18 KB
- **Capabilities:**
  - Class representing a WebSocket.
  - Create a new `WebSocket`.
  - This deviates from the WHATWG interface since ws doesn't support the
- **Methods:** 18

#### stream
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/stream.js`
- **Type:** JavaScript
- **Size:** 4.54 KB
- **Capabilities:**
  - Emits the `'close'` event on a stream.
  - The listener of the `'end'` event.
  - The listener of the `'error'` event.
- **Methods:** 10

#### buffer-util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/buffer-util.js`
- **Type:** JavaScript
- **Size:** 2.97 KB
- **Capabilities:**
  - Merges an array of buffers into a new buffer.
  - Masks a buffer using the given mask.
  - Unmasks a buffer using the given mask.
- **Methods:** 5

#### receiver
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/lib/receiver.js`
- **Type:** JavaScript
- **Size:** 13.71 KB
- **Capabilities:**
  - HyBi Receiver implementation.
  - Creates a Receiver instance.
  - Implements `Writable.prototype._write()`.
- **Methods:** 1

#### browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/ws/browser.js`
- **Type:** JavaScript
- **Size:** 176 B

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/tar-fs/index.js`
- **Type:** JavaScript
- **Size:** 10.65 KB
- **Capabilities:**
  - Validate
- **Methods:** 20

#### helpers
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/agent-base/dist/helpers.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/agent-base/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Determine whether this is an `http` or `https` request.

#### validation
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/validation.js`
- **Type:** JavaScript
- **Size:** 3.81 KB
- **Capabilities:**
  - Checks if a status code is allowed in a close frame.
  - Checks if a given buffer contains only correct UTF-8.
  - Determines whether a value is a `Blob`.
- **Methods:** 3

#### permessage-deflate
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/permessage-deflate.js`
- **Type:** JavaScript
- **Size:** 14.17 KB
- **Capabilities:**
  - permessage-deflate implementation.
  - Creates a PerMessageDeflate instance.
  - Create an extension negotiation offer.
- **Methods:** 3

#### subprotocol
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/subprotocol.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Parses the `Sec-WebSocket-Protocol` header into a set of subprotocol names.
- **Methods:** 1

#### limiter
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/limiter.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - A very simple job queue with adjustable concurrency. Adapted from
  - Creates a new `Limiter`.
  - Adds a job to the queue.

#### websocket-server
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/websocket-server.js`
- **Type:** JavaScript
- **Size:** 16.01 KB
- **Capabilities:**
  - Class representing a WebSocket server.
  - Create a `WebSocketServer` instance.
  - Returns the bound address, the address family name, and port of the server
- **Methods:** 7

#### event-target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/event-target.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Class representing an event.
  - Create a new `Event`.
  - Class representing a close event.
- **Methods:** 5

#### extension
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/extension.js`
- **Type:** JavaScript
- **Size:** 6.04 KB
- **Capabilities:**
  - Adds an offer to the map of extension offers or a parameter to the map of
  - Parses the `Sec-WebSocket-Extensions` header into an object.
  - Builds the `Sec-WebSocket-Extensions` header field value.
- **Methods:** 3

#### sender
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/sender.js`
- **Type:** JavaScript
- **Size:** 16.32 KB
- **Capabilities:**
  - HyBi Sender implementation.
  - Creates a Sender instance.
  - Frames a piece of data according to the HyBi WebSocket protocol.
- **Methods:** 3

#### websocket
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/websocket.js`
- **Type:** JavaScript
- **Size:** 35.61 KB
- **Capabilities:**
  - Class representing a WebSocket.
  - Create a new `WebSocket`.
  - For historical reasons, the custom "nodebuffer" type is used by the default
- **Methods:** 20

#### stream
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/stream.js`
- **Type:** JavaScript
- **Size:** 4.11 KB
- **Capabilities:**
  - Emits the `'close'` event on a stream.
  - The listener of the `'end'` event.
  - The listener of the `'error'` event.
- **Methods:** 9

#### buffer-util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/buffer-util.js`
- **Type:** JavaScript
- **Size:** 2.98 KB
- **Capabilities:**
  - Merges an array of buffers into a new buffer.
  - Masks a buffer using the given mask.
  - Unmasks a buffer using the given mask.
- **Methods:** 5

#### receiver
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/lib/receiver.js`
- **Type:** JavaScript
- **Size:** 16.07 KB
- **Capabilities:**
  - HyBi Receiver implementation.
  - Creates a Receiver instance.
  - Implements `Writable.prototype._write()`.

#### browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/ws/browser.js`
- **Type:** JavaScript
- **Size:** 176 B

#### Deferred
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.07 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### Base64
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/Base64.js`
- **Type:** JavaScript
- **Size:** 1.22 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.
  - Encodes a string to base64.
- **Methods:** 1

#### unitConversions
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/unitConversions.js`
- **Type:** JavaScript
- **Size:** 904 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### Buffer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/Buffer.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### GraphemeTools
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/GraphemeTools.js`
- **Type:** JavaScript
- **Size:** 1.51 KB
- **Capabilities:**
  - Check if the given string is a single complex grapheme. A complex grapheme is one that
  - Check if the given string is a single grapheme.
- **Methods:** 2

#### assert
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/assert.js`
- **Type:** JavaScript
- **Size:** 906 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### EventEmitter
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 2.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Like `on` but the listener will only be fired once and then it will be removed.
  - Emits an event and call any associated listeners.
- **Methods:** 1

#### log
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/log.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### uuid
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/uuid.js`
- **Type:** JavaScript
- **Size:** 2.43 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Generates a random v4 UUID, as specified in RFC4122.
- **Methods:** 1

#### ProcessingQueue
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/ProcessingQueue.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### DistinctValues
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/DistinctValues.js`
- **Type:** JavaScript
- **Size:** 1.99 KB
- **Capabilities:**
  - Returns an array of distinct values. Order is not guaranteed.
  - Returns a stringified version of the object with keys sorted. This is required to
- **Methods:** 3

#### DefaultMap
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/DefaultMap.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - A subclass of Map whose functionality is almost the same as its parent

#### IdWrapper
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/IdWrapper.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Creates an object with a positive unique incrementing id.

#### WebsocketTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/WebsocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### Mutex
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/utils/Mutex.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Use Mutex class to coordinate local concurrent operations.
- **Methods:** 1

#### mapperTabPage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiTab/mapperTabPage.js`
- **Type:** JavaScript
- **Size:** 3.36 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 3

#### BidiParser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiTab/BidiParser.js`
- **Type:** JavaScript
- **Size:** 5.9 KB

#### bidiTab
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiTab/bidiTab.js`
- **Type:** JavaScript
- **Size:** 2.54 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - A CdpTransport implementation that uses the window.cdp bindings
  - Launches the BiDi mapper instance.
- **Methods:** 2

#### Transport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiTab/Transport.js`
- **Type:** JavaScript
- **Size:** 4.82 KB

#### BidiNoOpParser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiNoOpParser.js`
- **Type:** JavaScript
- **Size:** 3.92 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### OutgoingMessage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/OutgoingMessage.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### BrowsingContextProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/context/BrowsingContextProcessor.js`
- **Type:** JavaScript
- **Size:** 8.96 KB

#### BrowsingContextImpl
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/context/BrowsingContextImpl.js`
- **Type:** JavaScript
- **Size:** 57.12 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - The ID of the parent browsing context.
  - Virtual navigation ID. Required, as CDP `loaderId` cannot be mapped 1:1 to all the
- **Methods:** 7

#### BrowsingContextStorage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/context/BrowsingContextStorage.js`
- **Type:** JavaScript
- **Size:** 3.41 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### logHelper
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/log/logHelper.js`
- **Type:** JavaScript
- **Size:** 5.61 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 5

#### LogManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/log/LogManager.js`
- **Type:** JavaScript
- **Size:** 5.99 KB
- **Capabilities:**
  - Try the best to get the exception text.
- **Methods:** 2

#### RealmStorage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/RealmStorage.js`
- **Type:** JavaScript
- **Size:** 3 KB

#### WorkerRealm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/WorkerRealm.js`
- **Type:** JavaScript
- **Size:** 2.52 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### PreloadScript
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/PreloadScript.js`
- **Type:** JavaScript
- **Size:** 4.67 KB
- **Capabilities:**
  - BiDi IDs are generated by the server and are unique within contexts.
  - String to be evaluated. Wraps user-provided function so that the following
  - Adds the script to the given CDP targets by calling the
- **Methods:** 2

#### SharedId
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/SharedId.js`
- **Type:** JavaScript
- **Size:** 2.35 KB
- **Methods:** 3

#### WindowRealm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/WindowRealm.js`
- **Type:** JavaScript
- **Size:** 6.57 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### ScriptProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/ScriptProcessor.js`
- **Type:** JavaScript
- **Size:** 3.89 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### PreloadScriptStorage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/PreloadScriptStorage.js`
- **Type:** JavaScript
- **Size:** 1.51 KB
- **Capabilities:**
  - Container class for preload scripts.
  - Finds all entries that match the given filter (OR logic).

#### ChannelProxy
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/ChannelProxy.js`
- **Type:** JavaScript
- **Size:** 9.96 KB
- **Capabilities:**
  - Used to send messages from realm to BiDi user.
  - Creates a channel proxy in the given realm, initialises listener and
  - Evaluation string which creates a ChannelProxy object on the client side.

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/script/Realm.js`
- **Type:** JavaScript
- **Size:** 20.4 KB
- **Capabilities:**
  - Relies on the CDP to implement proper BiDi serialization, except:
  - Serializes a given CDP object into BiDi, keeping references in the
  - Gets the string representation of an object. This is equivalent to
- **Methods:** 1

#### BrowserProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/browser/BrowserProcessor.js`
- **Type:** JavaScript
- **Size:** 3.04 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### EventManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/session/EventManager.js`
- **Type:** JavaScript
- **Size:** 9.44 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Maps event name to a desired buffer length.
  - Maps event name to a set of contexts where this event already happened.

#### events
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/session/events.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Returns true if the given event is a CDP event.
  - Asserts that the given event is known to BiDi or BiDi+, or throws otherwise.
- **Methods:** 2

#### SubscriptionManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/session/SubscriptionManager.js`
- **Type:** JavaScript
- **Size:** 11.65 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Returns the cartesian product of the given arrays.
  - Subscribes to event in the given context and channel.
- **Methods:** 3

#### SessionProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/session/SessionProcessor.js`
- **Type:** JavaScript
- **Size:** 2.19 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### CdpProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/cdp/CdpProcessor.js`
- **Type:** JavaScript
- **Size:** 2.22 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### CdpTargetManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/cdp/CdpTargetManager.js`
- **Type:** JavaScript
- **Size:** 10.68 KB
- **Capabilities:**
  - This method is called for each CDP session, since this class is responsible

#### CdpTarget
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/cdp/CdpTarget.js`
- **Type:** JavaScript
- **Size:** 9.31 KB
- **Capabilities:**
  - Enables all the required CDP domains and unblocks the target.
  - Toggles both Network and Fetch domains.
  - All the ProxyChannels from all the preload scripts of the given

#### InputProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/InputProcessor.js`
- **Type:** JavaScript
- **Size:** 9.09 KB
- **Capabilities:**
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### keyUtils
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/keyUtils.js`
- **Type:** JavaScript
- **Size:** 12.13 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Returns the normalized key value for a given key according to the table:
  - Returns the key code for a given key according to the table:
- **Methods:** 3

#### InputState
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/InputState.js`
- **Type:** JavaScript
- **Size:** 3.65 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputStateManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/InputStateManager.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### ActionDispatcher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/ActionDispatcher.js`
- **Type:** JavaScript
- **Size:** 27.61 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Translates a non-grapheme key to either an `undefined` for a special keys, or a single
- **Methods:** 4

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputSource
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/input/InputSource.js`
- **Type:** JavaScript
- **Size:** 4.62 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### NetworkUtils
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/network/NetworkUtils.js`
- **Type:** JavaScript
- **Size:** 10.3 KB
- **Capabilities:**
  - Converts from CDP Network domain cookie to BiDi network cookie.
  - Decodes a byte value to a string.
  - Converts from BiDi set network cookie params to CDP Network domain cookie.
- **Methods:** 17

#### NetworkStorage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/network/NetworkStorage.js`
- **Type:** JavaScript
- **Size:** 7.85 KB
- **Capabilities:**
  - A map from network request ID to Network Request objects.
  - Gets the network request with the given ID, if any.
  - Adds the given entry to the intercept map.

#### NetworkRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/network/NetworkRequest.js`
- **Type:** JavaScript
- **Size:** 28.89 KB
- **Capabilities:**
  - Each network request has an associated request id, which is a string
  - Indicates the network intercept phase, if the request is currently blocked.
  - When blocked returns the phase for it
- **Methods:** 2

#### NetworkProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/network/NetworkProcessor.js`
- **Type:** JavaScript
- **Size:** 11.76 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Validate https://fetch.spec.whatwg.org/#header-value
  - Attempts to parse the given url.
- **Methods:** 1

#### PermissionsProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/permissions/PermissionsProcessor.js`
- **Type:** JavaScript
- **Size:** 1.99 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### StorageProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/modules/storage/StorageProcessor.js`
- **Type:** JavaScript
- **Size:** 8.75 KB
- **Capabilities:**
  - Responsible for handling the `storage` domain.

#### BidiMapper
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiMapper.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### BidiServer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiServer.js`
- **Type:** JavaScript
- **Size:** 6.01 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - Creates and starts BiDi Mapper instance.
  - Sends BiDi message.

#### CommandProcessor
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiMapper/CommandProcessor.js`
- **Type:** JavaScript
- **Size:** 13.52 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### protocol-parser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol-parser/protocol-parser.js`
- **Type:** JavaScript
- **Size:** 13.62 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 20

#### webdriver-bidi-permissions
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol-parser/generated/webdriver-bidi-permissions.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.
  - THIS FILE IS AUTOGENERATED by cddlconv 0.1.5.

#### webdriver-bidi
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol-parser/generated/webdriver-bidi.js`
- **Type:** JavaScript
- **Size:** 98.86 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.
  - THIS FILE IS AUTOGENERATED by cddlconv 0.1.5.

#### CdpConnection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/cdp/CdpConnection.js`
- **Type:** JavaScript
- **Size:** 4.84 KB
- **Capabilities:**
  - Represents a high-level CDP connection to the browser backend.
  - Gets a CdpClient instance attached to the given session ID,
  - Creates a new CdpClient instance for the given session ID.

#### CdpClient
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/cdp/CdpClient.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### ErrorResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol/ErrorResponse.js`
- **Type:** JavaScript
- **Size:** 7.45 KB

#### chromium-bidi
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol/chromium-bidi.js`
- **Type:** JavaScript
- **Size:** 3.93 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### protocol
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/protocol/protocol.js`
- **Type:** JavaScript
- **Size:** 2.28 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### SimpleTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/SimpleTransport.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Capabilities:**
  - Implements simple transport that allows sending string messages via

#### BrowserInstance
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/BrowserInstance.js`
- **Type:** JavaScript
- **Size:** 5.21 KB
- **Capabilities:**
  - BrowserProcess is responsible for running the browser and BiDi Mapper within

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/index.js`
- **Type:** JavaScript
- **Size:** 2.15 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### WebSocketServer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/WebSocketServer.js`
- **Type:** JavaScript
- **Size:** 18.55 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### reader
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/reader.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### MapperCdpConnection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/cjs/bidiServer/MapperCdpConnection.js`
- **Type:** JavaScript
- **Size:** 6.24 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### mapperTab
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/node_modules/chromium-bidi/lib/iife/mapperTab.js`
- **Type:** JavaScript
- **Size:** 715.54 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Like `on` but the listener will only be fired once and then it will be removed.
  - Emits an event and call any associated listeners.
- **Methods:** 20

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/WebWorker.js`
- **Type:** JavaScript
- **Size:** 3.49 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
- **Methods:** 4

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - The type of the dialog.
  - The message displayed in the dialog.
  - The default value of the prompt, or an empty string if the dialog

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 217 B

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 39.79 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
  - Used to clear the document handle that has been destroyed.
- **Methods:** 9

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 3.84 KB
- **Capabilities:**
  - If defined, indicates an ongoing screenshot opereation.

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 62.34 KB
- **Capabilities:**
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
  - Queries the current element for an element matching the given selector.
- **Methods:** 10

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.05 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 15.08 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
  - The `ContinueRequestOverrides` that will be used
- **Methods:** 2

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 4.7 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Capabilities:**
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).
  - Promise which resolves to a text (utf8) representation of response body.

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.
  - If the target is not of type `"page"`, `"webview"` or `"background_page"`,

#### locators
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 25.57 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.2 KB
- **Capabilities:**
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.
  - The Mouse class operates in main-frame CSS pixels

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.24 KB
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 57.24 KB
- **Capabilities:**
  - Page provides methods to interact with a single tab or
  - Listen to page events.
  - Finds the first element that matches the selector. If no element matches
- **Methods:** 14

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 9.82 KB
- **Capabilities:**
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
  - Gets a map of handles representing the properties of the current handle.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 631 B

#### ErrorLike
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.88 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 6.31 KB
- **Capabilities:**
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.
  - Adds a disposable resource to the stack, returning the resource.

#### decorators
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 7.5 KB
- **Capabilities:**
  - The decorator only invokes the target if the target has not been invoked with
  - Event emitter fields marked with `bubble` will have their events bubble up
- **Methods:** 8

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 286 B

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 694 B

#### Function
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 5

#### Mutex
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 1.13 KB

#### PSelectorParser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.45 KB
- **Methods:** 1

#### ScriptInjector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.18 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.
  - The text of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 497 B

#### WaitTask
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 6.59 KB
- **Capabilities:**
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 2.64 KB
- **Capabilities:**
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.19 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 983 B

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 628 B

#### Puppeteer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 2.76 KB
- **Capabilities:**
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.
  - Gets the names of all custom query handlers.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 854 B
- **Capabilities:**
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Capabilities:**
  - The EventEmitter class that many Puppeteer classes extend.
  - If you pass an emitter, the returned emitter will wrap the passed emitter.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### CSSQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/CSSQueryHandler.js`
- **Type:** JavaScript
- **Size:** 532 B

#### SecurityDetails
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.
  - The security protocol being used, e.g. "TLS 1.2".

#### Errors
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 1.59 KB
- **Capabilities:**
  - The base class for all Puppeteer-specific errors
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 4.88 KB
- **Capabilities:**
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.05 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 521 B

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 10.49 KB
- **Capabilities:**
  - Validate Dialog Type
- **Methods:** 14

#### common
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 1.33 KB

#### FileChooser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 2.33 KB
- **Capabilities:**
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for
  - Accept the file chooser request with the given file paths.

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 3.71 KB
- **Capabilities:**
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.47 KB
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 403 B

#### HandleIterator
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 4.69 KB
- **Capabilities:**
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 550 B

#### TaskQueue
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 483 B

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 16.53 KB

#### QueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 8.51 KB
- **Capabilities:**
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.7 KB

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 10.54 KB
- **Capabilities:**
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 405 B
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 11.24 KB
- **Capabilities:**
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 9.79 KB
- **Capabilities:**
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when
  - The default executable path.

#### PipeTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.31 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 7.18 KB

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 9.61 KB
- **Capabilities:**
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/WebWorker.js`
- **Type:** JavaScript
- **Size:** 816 B

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 629 B

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 23.37 KB
- **Methods:** 6

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 14.61 KB
- **Methods:** 4

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 9.03 KB
- **Methods:** 4

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.73 KB

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 8 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 10.87 KB
- **Methods:** 2

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 6.7 KB
- **Methods:** 2

#### Serializer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 3.27 KB

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 3.07 KB

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.47 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 4.47 KB
- **Capabilities:**
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### Deserializer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 2.6 KB

#### bidi
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 502 B

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 1.92 KB
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 17 KB

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 11.51 KB
- **Methods:** 2

#### ExposedFunction
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 9.02 KB
- **Methods:** 2

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 4.49 KB
- **Capabilities:**
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
  - This transport is given to the BiDi server instance and allows Puppeteer
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 33.8 KB
- **Capabilities:**
  - Implements Page using WebDriver BiDi.
  - Check domains match.
  - Check paths match.
- **Methods:** 13

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 1.76 KB

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Browser.js`
- **Type:** JavaScript
- **Size:** 13.08 KB
- **Methods:** 4

#### Navigation
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Navigation.js`
- **Type:** JavaScript
- **Size:** 7.31 KB
- **Methods:** 2

#### Session
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Session.js`
- **Type:** JavaScript
- **Size:** 9.13 KB
- **Capabilities:**
  - Currently, there is a 1:1 relationship between the session and the
- **Methods:** 2

#### BrowsingContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 25.92 KB
- **Methods:** 2

#### Request
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Request.js`
- **Type:** JavaScript
- **Size:** 9.49 KB
- **Methods:** 2

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Realm.js`
- **Type:** JavaScript
- **Size:** 11.77 KB
- **Methods:** 2

#### UserPrompt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/UserPrompt.js`
- **Type:** JavaScript
- **Size:** 6.24 KB
- **Methods:** 2

#### UserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/UserContext.js`
- **Type:** JavaScript
- **Size:** 10.22 KB
- **Methods:** 2

#### injected
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 9.76 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 1

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 2.54 KB

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 568 B

#### FrameTree
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 6.27 KB
- **Capabilities:**
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',
  - Keeps track of targets that were created via 'Target.targetCreated'

#### NetworkManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 20.22 KB
- **Capabilities:**
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 16.16 KB
- **Capabilities:**
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 17.31 KB
- **Capabilities:**
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the
  - When the main frame is replaced by another main frame,

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 4.57 KB
- **Methods:** 2

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 9.52 KB
- **Capabilities:**
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 3.27 KB
- **Capabilities:**
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 5.22 KB

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 10.15 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 3.89 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.57 KB
- **Capabilities:**
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.3 KB
- **Capabilities:**
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 6.82 KB
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 22.46 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.

#### CdpPreloadScript
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/CdpPreloadScript.js`
- **Type:** JavaScript
- **Size:** 1001 B
- **Capabilities:**
  - This is the ID of the preload script returned by

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 13.47 KB
- **Capabilities:**
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
  - A target is added to this map once ChromeTargetManager has created
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 5.69 KB
- **Capabilities:**
  - Waits for the next context to be set on the isolated world.

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 6.55 KB
- **Capabilities:**
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 14.78 KB
- **Capabilities:**
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### utils
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/utils.js`
- **Type:** JavaScript
- **Size:** 6.37 KB
- **Methods:** 6

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 6.28 KB
- **Capabilities:**
  - Device in a request prompt.
  - Device id during a prompt.
  - Device name as it appears in a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 1.36 KB

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.19 KB
- **Capabilities:**
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 17.38 KB
- **Capabilities:**
  - Evaluates the given function.
- **Methods:** 5

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.15 KB
- **Capabilities:**
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 6.04 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 12.98 KB
- **Capabilities:**
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
  - Promise that resolves to the array of coverage reports
- **Methods:** 1

#### ExtensionTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ExtensionTransport.js`
- **Type:** JavaScript
- **Size:** 5.22 KB
- **Capabilities:**
  - Experimental ExtensionTransport allows establishing a connection via

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 34.93 KB
- **Capabilities:**
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 5

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.87 KB
- **Capabilities:**
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### index-browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/index-browser.js`
- **Type:** JavaScript
- **Size:** 416 B

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.46 KB

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 869 B

#### injected
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 1.26 KB

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 3.54 KB

#### PQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 7.85 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### CSSSelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/CSSSelector.js`
- **Type:** JavaScript
- **Size:** 424 B

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 569 B

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 3.31 KB
- **Capabilities:**
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
  - Erases the cache when the tree has mutated text.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 90.12 KB
- **Capabilities:**
  - Execute Schedule
  - Combine Latest
  - Combine Latest Init
- **Methods:** 20

#### parsel-js
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/third_party/parsel-js/parsel-js.js`
- **Type:** JavaScript
- **Size:** 6.3 KB
- **Methods:** 4

#### mitt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/esm/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Methods:** 1

#### puppeteer-core
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/puppeteer-core.js`
- **Type:** JavaScript
- **Size:** 1.44 KB

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/WebWorker.js`
- **Type:** JavaScript
- **Size:** 3.69 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
- **Methods:** 4

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.24 KB
- **Capabilities:**
  - The type of the dialog.
  - The message displayed in the dialog.
  - The default value of the prompt, or an empty string if the dialog

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 324 B

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 40.36 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
  - Used to clear the document handle that has been destroyed.
- **Methods:** 9

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 4.09 KB
- **Capabilities:**
  - If defined, indicates an ongoing screenshot opereation.

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 63.42 KB
- **Capabilities:**
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
  - Queries the current element for an element matching the given selector.
- **Methods:** 10

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.25 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 15.63 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
  - The `ContinueRequestOverrides` that will be used
- **Methods:** 2

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 4.96 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).
  - Promise which resolves to a text (utf8) representation of response body.

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.34 KB
- **Capabilities:**
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.
  - If the target is not of type `"page"`, `"webview"` or `"background_page"`,

#### locators
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 27.17 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.42 KB
- **Capabilities:**
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.
  - The Mouse class operates in main-frame CSS pixels

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 59.36 KB
- **Capabilities:**
  - Page provides methods to interact with a single tab or
  - Listen to page events.
  - Finds the first element that matches the selector. If no element matches
- **Methods:** 14

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 10 KB
- **Capabilities:**
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
  - Gets a map of handles representing the properties of the current handle.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 1.64 KB

#### ErrorLike
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 6.63 KB
- **Capabilities:**
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.
  - Adds a disposable resource to the stack, returning the resource.

#### decorators
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 8.01 KB
- **Capabilities:**
  - The decorator only invokes the target if the target has not been invoked with
  - Event emitter fields marked with `bubble` will have their events bubble up
- **Methods:** 8

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 1.11 KB

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 847 B

#### Function
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 2.6 KB
- **Capabilities:**
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 5

#### Mutex
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 1.28 KB

#### PSelectorParser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.72 KB
- **Methods:** 1

#### ScriptInjector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.34 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Capabilities:**
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.
  - The text of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 620 B

#### WaitTask
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 6.87 KB
- **Capabilities:**
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 3.99 KB
- **Capabilities:**
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 4.56 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 1.1 KB

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 804 B

#### Puppeteer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 2.94 KB
- **Capabilities:**
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.
  - Gets the names of all custom query handlers.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 992 B
- **Capabilities:**
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 4.3 KB
- **Capabilities:**
  - The EventEmitter class that many Puppeteer classes extend.
  - If you pass an emitter, the returned emitter will wrap the passed emitter.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### CSSQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CSSQueryHandler.js`
- **Type:** JavaScript
- **Size:** 703 B

#### SecurityDetails
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.
  - The security protocol being used, e.g. "TLS 1.2".

#### Errors
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 1.98 KB
- **Capabilities:**
  - The base class for all Puppeteer-specific errors
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 5.69 KB
- **Capabilities:**
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 685 B

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 13.22 KB
- **Capabilities:**
  - Validate Dialog Type
- **Methods:** 14

#### common
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 2.68 KB

#### FileChooser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for
  - Accept the file chooser request with the given file paths.

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 4.09 KB
- **Capabilities:**
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.91 KB
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 577 B

#### HandleIterator
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 4.88 KB
- **Capabilities:**
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 730 B

#### TaskQueue
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 612 B

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 16.63 KB

#### QueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 8.89 KB
- **Capabilities:**
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 2.02 KB

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 11.06 KB
- **Capabilities:**
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 731 B
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 12.63 KB
- **Capabilities:**
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 10.05 KB
- **Capabilities:**
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when
  - The default executable path.

#### PipeTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.57 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 7.73 KB

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 10.14 KB
- **Capabilities:**
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### node
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/node/node.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### index
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/index.js`
- **Type:** JavaScript
- **Size:** 983 B

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/WebWorker.js`
- **Type:** JavaScript
- **Size:** 989 B

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 779 B

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 24.33 KB
- **Methods:** 6

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 14.92 KB
- **Methods:** 4

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 10.33 KB
- **Methods:** 4

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.91 KB

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 8.27 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 11.13 KB
- **Methods:** 2

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 6.9 KB
- **Methods:** 2

#### Serializer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 3.43 KB

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 3.62 KB

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 4.76 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 4.76 KB
- **Capabilities:**
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### Deserializer
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 2.77 KB

#### bidi
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 1.44 KB

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 2.22 KB
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 17.38 KB

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 12.08 KB
- **Methods:** 2

#### ExposedFunction
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 10.36 KB
- **Methods:** 2

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 5.71 KB
- **Capabilities:**
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
  - This transport is given to the BiDi server instance and allows Puppeteer
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 34.52 KB
- **Capabilities:**
  - Implements Page using WebDriver BiDi.
  - Check domains match.
  - Check paths match.
- **Methods:** 13

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### core
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/core.js`
- **Type:** JavaScript
- **Size:** 1.3 KB

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Browser.js`
- **Type:** JavaScript
- **Size:** 13.46 KB
- **Methods:** 4

#### Navigation
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Navigation.js`
- **Type:** JavaScript
- **Size:** 7.57 KB
- **Methods:** 2

#### Session
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Session.js`
- **Type:** JavaScript
- **Size:** 9.42 KB
- **Capabilities:**
  - Currently, there is a 1:1 relationship between the session and the
- **Methods:** 2

#### BrowsingContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 26.75 KB
- **Methods:** 2

#### Request
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Request.js`
- **Type:** JavaScript
- **Size:** 9.72 KB
- **Methods:** 2

#### Realm
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Realm.js`
- **Type:** JavaScript
- **Size:** 12.34 KB
- **Methods:** 2

#### UserPrompt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/UserPrompt.js`
- **Type:** JavaScript
- **Size:** 6.48 KB
- **Methods:** 2

#### UserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/UserContext.js`
- **Type:** JavaScript
- **Size:** 10.61 KB
- **Methods:** 2

#### puppeteer-core-browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/puppeteer-core-browser.js`
- **Type:** JavaScript
- **Size:** 1.19 KB

#### injected
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 9.86 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 1

#### WebWorker
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 2.86 KB

#### Dialog
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 715 B

#### FrameTree
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 2.87 KB
- **Capabilities:**
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 6.55 KB
- **Capabilities:**
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',
  - Keeps track of targets that were created via 'Target.targetCreated'

#### NetworkManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 20.81 KB
- **Capabilities:**
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 16.92 KB
- **Capabilities:**
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 18.16 KB
- **Capabilities:**
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the
  - When the main frame is replaced by another main frame,

#### BrowserContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 4.76 KB
- **Methods:** 2

#### ElementHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 10.91 KB
- **Capabilities:**
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 5.49 KB

#### Browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 10.47 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 4.13 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.71 KB
- **Capabilities:**
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.7 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.91 KB
- **Capabilities:**
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 7.23 KB
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 22.93 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.

#### CdpPreloadScript
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/CdpPreloadScript.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - This is the ID of the preload script returned by

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 13.86 KB
- **Capabilities:**
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
  - A target is added to this map once ChromeTargetManager has created
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 6.03 KB
- **Capabilities:**
  - Waits for the next context to be set on the isolated world.

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 7.03 KB
- **Capabilities:**
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 15.26 KB
- **Capabilities:**
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### utils
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/utils.js`
- **Type:** JavaScript
- **Size:** 6.89 KB
- **Methods:** 6

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 6.77 KB
- **Capabilities:**
  - Device in a request prompt.
  - Device id during a prompt.
  - Device name as it appears in a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 2.73 KB

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.41 KB
- **Capabilities:**
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 17.95 KB
- **Capabilities:**
  - Evaluates the given function.
- **Methods:** 5

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.31 KB
- **Capabilities:**
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 6.24 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 13.4 KB
- **Capabilities:**
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
  - Promise that resolves to the array of coverage reports
- **Methods:** 1

#### ExtensionTransport
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ExtensionTransport.js`
- **Type:** JavaScript
- **Size:** 5.37 KB
- **Capabilities:**
  - Experimental ExtensionTransport allows establishing a connection via

#### Page
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 36.31 KB
- **Capabilities:**
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 5

#### JSHandle
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 3.11 KB
- **Capabilities:**
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### index-browser
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/index-browser.js`
- **Type:** JavaScript
- **Size:** 1.25 KB

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.57 KB

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.01 KB

#### injected
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 2.61 KB

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 3.94 KB

#### PQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 8.34 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### CSSSelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/CSSSelector.js`
- **Type:** JavaScript
- **Size:** 648 B

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 799 B

#### util
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.8 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 3.6 KB
- **Capabilities:**
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
  - Erases the cache when the tree has mutated text.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 367.5 KB
- **Capabilities:**
  - Execute Schedule
  - Combine Latest
  - Combine Latest Init
- **Methods:** 20

#### parsel-js
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/third_party/parsel-js/parsel-js.js`
- **Type:** JavaScript
- **Size:** 7.27 KB
- **Methods:** 4

#### mitt
- **File:** `frontend-tools/node_modules/lighthouse/node_modules/puppeteer-core/lib/cjs/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 3.75 KB
- **Methods:** 2

#### flow
- **File:** `frontend-tools/node_modules/lighthouse/dist/report/flow.js`
- **Type:** JavaScript
- **Size:** 548.85 KB
- **Capabilities:**
  - Icon -- Metric Name
  - Icon -- Metric Name -- Metric Value
- **Methods:** 20

#### bundle.esm
- **File:** `frontend-tools/node_modules/lighthouse/dist/report/bundle.esm.js`
- **Type:** JavaScript
- **Size:** 177.82 KB
- **Capabilities:**
  - Icon -- Metric Name
  - Icon -- Metric Name -- Metric Value
- **Methods:** 20

#### standalone
- **File:** `frontend-tools/node_modules/lighthouse/dist/report/standalone.js`
- **Type:** JavaScript
- **Size:** 179.4 KB
- **Capabilities:**
  - Icon -- Metric Name
  - Icon -- Metric Name -- Metric Value
- **Methods:** 20

#### file-namer
- **File:** `frontend-tools/node_modules/lighthouse/report/generator/file-namer.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - Generate a filenamePrefix of name_YYYY-MM-DD_HH-MM-SS
  - Generate a filenamePrefix of hostname_YYYY-MM-DD_HH-MM-SS.
  - Generate a filenamePrefix of name_YYYY-MM-DD_HH-MM-SS.
- **Methods:** 3

#### report-generator
- **File:** `frontend-tools/node_modules/lighthouse/report/generator/report-generator.js`
- **Type:** JavaScript
- **Size:** 6.18 KB
- **Capabilities:**
  - Replaces all the specified strings in source without serial replacements.
  - Returns the standalone report HTML as a string with the report JSON and renderer JS inlined.
  - Returns the standalone flow report HTML as a string with the report JSON and renderer JS inlined.

#### dom
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/dom.js`
- **Type:** JavaScript
- **Size:** 8.95 KB
- **Capabilities:**
  - Set link href, but safely, preventing `javascript:` protocol, etc.
  - Only create blob URLs for JSON & HTML
  - The channel to use for UTM data when rendering links to the documentation.

#### topbar-features
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/topbar-features.js`
- **Type:** JavaScript
- **Size:** 10.58 KB
- **Capabilities:**
  - Handler for tool button.
  - Handle copy events.
  - Copies the report JSON to the clipboard (if supported by the browser).

#### performance-category-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/performance-category-renderer.js`
- **Type:** JavaScript
- **Size:** 15.83 KB
- **Capabilities:**
  - Get a link to the interactive scoring calculator with the metric values.
  - Clamp figure to 2 decimal places
  - Returns true if the audit is a general performance insight (i.e. not a metric or hidden audit).
- **Methods:** 1

#### logger
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/logger.js`
- **Type:** JavaScript
- **Size:** 2.18 KB
- **Capabilities:**
  - Logs messages via a UI butter.
  - Shows a butter bar.
  - Explicitly hides the butter bar.

#### details-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/details-renderer.js`
- **Type:** JavaScript
- **Size:** 21.92 KB
- **Capabilities:**
  - Create small thumbnail with scaled down image asset.
  - Render a details item value for embedding in a table. Renders the value
  - Returns a new heading where the values are defined first by `heading.subItemsHeading`,

#### report-utils
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/report-utils.js`
- **Type:** JavaScript
- **Size:** 25.29 KB
- **Capabilities:**
  - Returns a new LHR that's reshaped for slightly better ergonomics within the report rendereer.
  - Given an audit's details, identify and return a URL locator function that
  - Mark TableItems/OpportunityItems with entity names.
- **Methods:** 1

#### report-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/report-renderer.js`
- **Type:** JavaScript
- **Size:** 12.7 KB
- **Capabilities:**
  - Returns a div with a list of top-level warnings, or an empty div if no warnings.

#### snippet-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/snippet-renderer.js`
- **Type:** JavaScript
- **Size:** 11.46 KB
- **Capabilities:**
  - Render snippet of text with line numbers and annotations.
  - Renders a line (text content, message, or placeholder) as a DOM element.
- **Methods:** 3

#### drop-down-menu
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/drop-down-menu.js`
- **Type:** JavaScript
- **Size:** 5.59 KB
- **Capabilities:**
  - Click handler for tools button.
  - Handler for tool button.
  - Handler for tool DropDown.

#### explodey-gauge
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/explodey-gauge.js`
- **Type:** JavaScript
- **Size:** 14.42 KB
- **Capabilities:**
  - On the first run, tease with a little peek reveal
- **Methods:** 6

#### crc-details-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/crc-details-renderer.js`
- **Type:** JavaScript
- **Size:** 6.5 KB
- **Capabilities:**
  - Create render context for critical-request-chain tree display.
  - Helper to create context for each critical-request-chain node based on its
  - Creates the DOM for a tree segment.

#### element-screenshot-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/element-screenshot-renderer.js`
- **Type:** JavaScript
- **Size:** 10.63 KB
- **Capabilities:**
  - Given the location of an element and the sizes of the preview and screenshot,
  - Render a clipPath SVG element to assist marking the element's rect.
  - Called by report renderer. Defines a css variable used by any element screenshots
- **Methods:** 3

#### report-ui-features
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/report-ui-features.js`
- **Type:** JavaScript
- **Size:** 12.68 KB
- **Capabilities:**
  - Adds tools button, print, and other functionality to the report. The method
  - Returns the html that recreates this report.
  - Save json as a gist. Unimplemented in base UI features.
- **Methods:** 1

#### swap-locale-feature
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/swap-locale-feature.js`
- **Type:** JavaScript
- **Size:** 2.66 KB

#### features-util
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/features-util.js`
- **Type:** JavaScript
- **Size:** 697 B
- **Methods:** 1

#### category-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/category-renderer.js`
- **Type:** JavaScript
- **Size:** 20.48 KB
- **Capabilities:**
  - Display info per top-level clump. Define on class to avoid race with Util init.
  - Inject the final screenshot next to the score gauge of the first category (likely Performance)
  - Renders the group container for a group of audits. Individual audit elements can be added

#### i18n-formatter
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/i18n-formatter.js`
- **Type:** JavaScript
- **Size:** 7.61 KB
- **Capabilities:**
  - Format number.
  - Format integer.
  - Format percent.

#### text-encoding
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/text-encoding.js`
- **Type:** JavaScript
- **Size:** 2.05 KB
- **Capabilities:**
  - Takes an UTF-8 string and returns a base64 encoded string.
- **Methods:** 2

#### report-globals
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/report-globals.js`
- **Type:** JavaScript
- **Size:** 1.03 KB

#### pwa-category-renderer
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/pwa-category-renderer.js`
- **Type:** JavaScript
- **Size:** 6.44 KB
- **Capabilities:**
  - Returns the group IDs found in auditRefs.
  - Returns the group IDs whose audits are all considered passing.
  - Returns a tooltip string summarizing group pass rates.

#### components
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/components.js`
- **Type:** JavaScript
- **Size:** 111.39 KB
- **Capabilities:**
  - Create Styles Component
- **Methods:** 20

#### open-tab
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/open-tab.js`
- **Type:** JavaScript
- **Size:** 3.89 KB
- **Capabilities:**
  - The popup's window.name is keyed by version+url+fetchTime, so we reuse/select tabs correctly.
  - Opens a new tab to an external page and sends data using postMessage.
  - Opens a new tab to an external page and sends data via base64 encoded url params.
- **Methods:** 8

#### api
- **File:** `frontend-tools/node_modules/lighthouse/report/renderer/api.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - Create Styles Element
- **Methods:** 5

#### bundle
- **File:** `frontend-tools/node_modules/lighthouse/report/clients/bundle.js`
- **Type:** JavaScript
- **Size:** 773 B

#### standalone
- **File:** `frontend-tools/node_modules/lighthouse/report/clients/standalone.js`
- **Type:** JavaScript
- **Size:** 1.5 KB
- **Methods:** 1

#### sentry-prompt
- **File:** `frontend-tools/node_modules/lighthouse/cli/sentry-prompt.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Methods:** 2

#### smokehouse
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/smokehouse.js`
- **Type:** JavaScript
- **Size:** 9.85 KB
- **Capabilities:**
  - Runs the selected smoke tests. Returns whether all assertions pass.
  - Run Lighthouse in the selected runner.
  - Logs an error to the console, including stdout and stderr if `err` is a
- **Methods:** 8

#### cli
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lighthouse-runners/cli.js`
- **Type:** JavaScript
- **Size:** 4.23 KB
- **Capabilities:**
  - Launch Chrome and do a full Lighthouse run via the Lighthouse CLI.
  - Internal runner.
- **Methods:** 2

#### bundle
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lighthouse-runners/bundle.js`
- **Type:** JavaScript
- **Size:** 4.69 KB
- **Capabilities:**
  - Launch Chrome and do a full Lighthouse run via the Lighthouse DevTools bundle.
- **Methods:** 2

#### devtools
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lighthouse-runners/devtools.js`
- **Type:** JavaScript
- **Size:** 1.83 KB
- **Capabilities:**
  - Download/pull latest DevTools, build Lighthouse for DevTools, roll to DevTools, and build DevTools.
  - Launch Chrome and do a full Lighthouse run via DevTools.
- **Methods:** 2

#### report-assert
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/report-assert.js`
- **Type:** JavaScript
- **Size:** 15.62 KB
- **Capabilities:**
  - Checks if the actual value matches the expectation. Does not recursively search. This supports
  - Walk down expected result, comparing to actual result. If a difference is found,
  - Delete expectations that don't match environment criteria.
- **Methods:** 11

#### exclusions
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/config/exclusions.js`
- **Type:** JavaScript
- **Size:** 1.24 KB
- **Capabilities:**
  - List of smoke tests excluded per runner. eg: 'cli': ['a11y', 'dbw']
- **Methods:** 1

#### version-check
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/version-check.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Capabilities:**
  - Returns false if fails check.
  - Chromium Version Check
- **Methods:** 3

#### back-compat-util
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/frontends/back-compat-util.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - COMPAT: update from the old TestDefn format (array of `expectations` per
  - Update Test Defn Format
- **Methods:** 1

#### lib
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/frontends/lib.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Methods:** 1

#### smokehouse-bin
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/frontends/smokehouse-bin.js`
- **Type:** JavaScript
- **Size:** 10.13 KB
- **Capabilities:**
  - Possible Lighthouse runners. Loaded dynamically so e.g. a CLI run isn't
  - Determine batches of smoketests to run, based on the `requestedIds`.
  - Prune the `networkRequests` from the test expectations when `takeNetworkRequestUrls`
- **Methods:** 3

#### local-console
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lib/local-console.js`
- **Type:** JavaScript
- **Size:** 863 B
- **Capabilities:**
  - A simple buffered log to use in place of `console`.
  - Log but without the ending newline.
  - Append a stdout and stderr to this log.

#### concurrent-mapper
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lib/concurrent-mapper.js`
- **Type:** JavaScript
- **Size:** 4.1 KB
- **Capabilities:**
  - A class that maintains a concurrency pool to coordinate many jobs that should
  - The limits of all currently running jobs. There will be duplicates.
  - Runs callbackfn on `values` in parallel, at a max of `concurrency` at a

#### child-process-error
- **File:** `frontend-tools/node_modules/lighthouse/cli/test/smokehouse/lib/child-process-error.js`
- **Type:** JavaScript
- **Size:** 643 B
- **Capabilities:**
  - An extension of Error that includes any stdout or stderr from a child

#### bin
- **File:** `frontend-tools/node_modules/lighthouse/cli/bin.js`
- **Type:** JavaScript
- **Size:** 3.98 KB
- **Methods:** 2

#### run
- **File:** `frontend-tools/node_modules/lighthouse/cli/run.js`
- **Type:** JavaScript
- **Size:** 9.41 KB
- **Capabilities:**
  - exported for testing
  - Attempts to connect to an instance of Chrome with an open remote-debugging
  - Attempt to kill the launched Chrome, if defined.
- **Methods:** 10

#### printer
- **File:** `frontend-tools/node_modules/lighthouse/cli/printer.js`
- **Type:** JavaScript
- **Size:** 2.02 KB
- **Capabilities:**
  - An enumeration of acceptable output modes:
  - Verify output path to use, either stdout or a file path.
  - Writes the output to stdout.
- **Methods:** 5

#### cli-flags
- **File:** `frontend-tools/node_modules/lighthouse/cli/cli-flags.js`
- **Type:** JavaScript
- **Size:** 20.26 KB
- **Capabilities:**
  - Support comma-separated values for some array flags by splitting on any ',' found.
  - Coerce output CLI input to `LH.SharedFlagsSettings['output']` or throw if not possible.
  - Verifies outputPath is something we can actually write to.
- **Methods:** 12

#### list-trace-categories
- **File:** `frontend-tools/node_modules/lighthouse/cli/commands/list-trace-categories.js`
- **Type:** JavaScript
- **Size:** 287 B
- **Methods:** 1

#### list-locales
- **File:** `frontend-tools/node_modules/lighthouse/cli/commands/list-locales.js`
- **Type:** JavaScript
- **Size:** 338 B
- **Methods:** 1

#### list-audits
- **File:** `frontend-tools/node_modules/lighthouse/cli/commands/list-audits.js`
- **Type:** JavaScript
- **Size:** 334 B
- **Methods:** 1

#### swap-flow-locale
- **File:** `frontend-tools/node_modules/lighthouse/shared/localization/swap-flow-locale.js`
- **Type:** JavaScript
- **Size:** 516 B
- **Methods:** 1

#### format
- **File:** `frontend-tools/node_modules/lighthouse/shared/localization/format.js`
- **Type:** JavaScript
- **Size:** 16.5 KB
- **Capabilities:**
  - The locale tags for the localized messages available to Lighthouse on disk.
  - Function to retrieve all elements from an ICU message AST that are associated
  - Returns a copy of the `values` object, with the values formatted based on how
- **Methods:** 20

#### swap-locale
- **File:** `frontend-tools/node_modules/lighthouse/shared/localization/swap-locale.js`
- **Type:** JavaScript
- **Size:** 3.54 KB
- **Capabilities:**
  - Returns a new LHR with all strings changed to the new `requestedLocale`.
- **Methods:** 1

#### esm-utils
- **File:** `frontend-tools/node_modules/lighthouse/shared/esm-utils.js`
- **Type:** JavaScript
- **Size:** 729 B
- **Capabilities:**
  - Commonjs equivalent of `require.resolve`.
- **Methods:** 3

#### util
- **File:** `frontend-tools/node_modules/lighthouse/shared/util.js`
- **Type:** JavaScript
- **Size:** 13.97 KB
- **Capabilities:**
  - If LHR is older than 10.0 it will not have the `finalDisplayedUrl` property.
  - If LHR is older than 10.0 it will not have the `mainDocumentUrl` property.
  - Given the entity classification dataset and a URL, identify the entity.
- **Methods:** 2

#### type-verifiers
- **File:** `frontend-tools/node_modules/lighthouse/shared/type-verifiers.js`
- **Type:** JavaScript
- **Size:** 920 B
- **Capabilities:**
  - Type predicate verifying `val` is an object (excluding `Array` and `null`).
  - Type predicate verifying `val` is an object or an array.
- **Methods:** 2

#### statistics
- **File:** `frontend-tools/node_modules/lighthouse/shared/statistics.js`
- **Type:** JavaScript
- **Size:** 3.84 KB
- **Capabilities:**
  - Approximates the Gauss error function, the probability that a random variable
  - Returns the score (1 - percentile) of `value` in a log-normal distribution
  - Interpolates the y value at a point x on the line defined by (x0, y0) and (x1, y1)
- **Methods:** 3

#### esbuild-polyfills
- **File:** `frontend-tools/node_modules/lighthouse/third-party/esbuild-plugins-polyfills/esbuild-polyfills.js`
- **Type:** JavaScript
- **Size:** 3.76 KB
- **Methods:** 6

#### download-content-shell
- **File:** `frontend-tools/node_modules/lighthouse/third-party/download-content-shell/download-content-shell.js`
- **Type:** JavaScript
- **Size:** 6.33 KB
- **Methods:** 13

#### utils
- **File:** `frontend-tools/node_modules/lighthouse/third-party/download-content-shell/utils.js`
- **Type:** JavaScript
- **Size:** 3.16 KB
- **Methods:** 11

#### valid-langs
- **File:** `frontend-tools/node_modules/lighthouse/third-party/axe/valid-langs.js`
- **Type:** JavaScript
- **Size:** 26.39 KB
- **Capabilities:**
  - Determine if a string is a valid language code
  - Returns array of valid language codes
- **Methods:** 2

#### user-flow
- **File:** `frontend-tools/node_modules/lighthouse/core/user-flow.js`
- **Type:** JavaScript
- **Size:** 11.36 KB
- **Capabilities:**
  - Default name for a user flow on the given url. "User flow" refers to the series of page navigations and user interactions being tested on the page. "url" is a trimmed version of a url that only includes the domain name.
  - This is an alternative to `navigate()` that can be used to analyze a navigation triggered by user interaction.
- **Methods:** 6

#### snapshot-runner
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/snapshot-runner.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Methods:** 1

#### driver
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver.js`
- **Type:** JavaScript
- **Size:** 2.88 KB

#### fetcher
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/fetcher.js`
- **Type:** JavaScript
- **Size:** 3.96 KB
- **Capabilities:**
  - Fetches any resource using the network directly.

#### runner-helpers
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/runner-helpers.js`
- **Type:** JavaScript
- **Size:** 5.61 KB
- **Capabilities:**
  - Runs the gatherer methods for a particular navigation phase (startInstrumentation/getArtifact/etc).
  - Awaits the result of artifact, catching errors to set the artifact to an error instead.
- **Methods:** 5

#### base-artifacts
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/base-artifacts.js`
- **Type:** JavaScript
- **Size:** 2.68 KB
- **Capabilities:**
  - Deduplicates identical warnings.
- **Methods:** 3

#### navigation-runner
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/navigation-runner.js`
- **Type:** JavaScript
- **Size:** 10.88 KB
- **Methods:** 8

#### session
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/session.js`
- **Type:** JavaScript
- **Size:** 3.88 KB
- **Capabilities:**
  - Puppeteer timeouts must fit into an int32 and the maximum timeout for `setTimeout` is a *signed*
  - Re-emit protocol events from the underlying CDPSession.
  - Disposes of a session so that it can no longer talk to Chrome.

#### network-monitor
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/network-monitor.js`
- **Type:** JavaScript
- **Size:** 8.73 KB
- **Capabilities:**
  - Reemit the same network recorder events.
  - Returns whether the network is completely idle (i.e. there are 0 inflight network requests).
  - Returns whether any important resources for the page are in progress.

#### dom
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/dom.js`
- **Type:** JavaScript
- **Size:** 1.73 KB
- **Capabilities:**
  - Resolves a backend node ID (from a trace event, protocol, etc) to the object ID for use with
  - Resolves a proprietary devtools node path (created from page-function.js) to the object ID for use
- **Methods:** 3

#### wait-for-condition
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/wait-for-condition.js`
- **Type:** JavaScript
- **Size:** 19.1 KB
- **Capabilities:**
  - Returns a promise that resolves immediately.
  - Returns a promise that resolve when a frame has been navigated.
  - Returns a promise that resolve when a frame has a FCP.
- **Methods:** 17

#### storage
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/storage.js`
- **Type:** JavaScript
- **Size:** 4.65 KB
- **Capabilities:**
  - A warning that previously-saved data may have affected the measured performance and instructions on how to avoid the problem. "locations" will be a list of possible types of data storage locations, e.g. "IndexedDB",  "Local Storage", or "Web SQL".
  - Clear the network cache on disk and in memory.
- **Methods:** 3

#### prepare
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/prepare.js`
- **Type:** JavaScript
- **Size:** 8.02 KB
- **Capabilities:**
  - Enables `Debugger` domain to receive async stacktrace information on network request initiators.
  - Resume any pauses that make it through `setSkipAllPauses`
  - `Debugger.setSkipAllPauses` is reset after every navigation, so retrigger it on main frame navigations.
- **Methods:** 11

#### network
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/network.js`
- **Type:** JavaScript
- **Size:** 896 B
- **Capabilities:**
  - Return the body of the response with the given ID. Rejects if getting the
- **Methods:** 1

#### target-manager
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/target-manager.js`
- **Type:** JavaScript
- **Size:** 9.64 KB
- **Capabilities:**
  - Tracks targets (the page itself, its iframes, their iframes, etc) as they
  - A map of target id to target/session information. Used to ensure unique
  - Returns the root session.
- **Methods:** 1

#### environment
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/environment.js`
- **Type:** JavaScript
- **Size:** 3.87 KB
- **Capabilities:**
  - Warning that the host device where Lighthouse is running appears to have a slower
  - We want to warn when the CPU seemed to be at least ~2x weaker than our regular target device.
  - Computes the benchmark index to get a rough estimate of device class.
- **Methods:** 4

#### service-workers
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/service-workers.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Methods:** 2

#### navigation
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/navigation.js`
- **Type:** JavaScript
- **Size:** 6.7 KB
- **Capabilities:**
  - Warning that the web page redirected during testing and that may have affected the load.
  - Navigates to the given URL, assuming that the page is not already on this URL.
- **Methods:** 3

#### execution-context
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/driver/execution-context.js`
- **Type:** JavaScript
- **Size:** 11.78 KB
- **Capabilities:**
  - Marks how many execution context ids have been created, for purposes of having a unique
  - Returns the isolated context ID currently in use.
  - Clears the remembered context ID. Use this method when we have knowledge that the runtime context
- **Methods:** 7

#### timespan-runner
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/timespan-runner.js`
- **Type:** JavaScript
- **Size:** 4.07 KB
- **Methods:** 2

#### base-gatherer
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/base-gatherer.js`
- **Type:** JavaScript
- **Size:** 1.51 KB
- **Capabilities:**
  - Base class for all gatherers.
  - Method to start observing a page for an arbitrary period of time.
  - Method to start observing a page when the measurements are very sensitive and

#### source-maps
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/source-maps.js`
- **Type:** JavaScript
- **Size:** 3.6 KB

#### js-usage
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/js-usage.js`
- **Type:** JavaScript
- **Size:** 2.19 KB

#### main-document-content
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/main-document-content.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Collects the content of the main html document.

#### accessibility
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/accessibility.js`
- **Type:** JavaScript
- **Size:** 7.11 KB
- **Capabilities:**
  - Run A11y Checks
  - Run A11y Checks And Reset Scroll
- **Methods:** 3

#### service-worker
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/service-worker.js`
- **Type:** JavaScript
- **Size:** 793 B

#### devtools-log
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/devtools-log.js`
- **Type:** JavaScript
- **Size:** 2.86 KB
- **Capabilities:**
  - This class saves all protocol messages whose method match a particular
  - Records a message if method matches filter and recording has been started.

#### link-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/link-elements.js`
- **Type:** JavaScript
- **Size:** 5.89 KB
- **Capabilities:**
  - Warning message explaining that there was an error parsing a link header in an HTTP response. `error` will be an english string with more details on the error. `header` will be the value of the header that caused the error. `link` is a type of HTTP header and should not be translated.
  - This needs to be in the constructor.
- **Methods:** 3

#### scripts
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/scripts.js`
- **Type:** JavaScript
- **Size:** 4.06 KB
- **Capabilities:**
  - Returns true if the script was created via our own calls
- **Methods:** 2

#### devtools-log-compat
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/devtools-log-compat.js`
- **Type:** JavaScript
- **Size:** 948 B

#### inputs
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/inputs.js`
- **Type:** JavaScript
- **Size:** 3.62 KB
- **Methods:** 1

#### viewport-dimensions
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/viewport-dimensions.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Methods:** 1

#### meta-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/meta-elements.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Methods:** 1

#### doctype
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/dobetterweb/doctype.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Capabilities:**
  - Get and return `name`, `publicId`, `systemId` from
- **Methods:** 1

#### optimized-images
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/dobetterweb/optimized-images.js`
- **Type:** JavaScript
- **Size:** 6.12 KB

#### domstats
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/dobetterweb/domstats.js`
- **Type:** JavaScript
- **Size:** 2.67 KB
- **Capabilities:**
  - Calculates the maximum tree depth of the DOM.
- **Methods:** 1

#### response-compression
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/dobetterweb/response-compression.js`
- **Type:** JavaScript
- **Size:** 4.28 KB

#### tags-blocking-first-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/dobetterweb/tags-blocking-first-paint.js`
- **Type:** JavaScript
- **Size:** 8.62 KB
- **Methods:** 2

#### installability-errors
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/installability-errors.js`
- **Type:** JavaScript
- **Size:** 1.37 KB
- **Capabilities:**
  - Creates an Artifacts.InstallabilityErrors, tranforming data from the protocol

#### web-app-manifest
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/web-app-manifest.js`
- **Type:** JavaScript
- **Size:** 3.45 KB
- **Capabilities:**
  - Uses the debugger protocol to fetch the manifest from within the context of

#### anchor-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/anchor-elements.js`
- **Type:** JavaScript
- **Size:** 4.09 KB
- **Capabilities:**
  - Function that is stringified and run in the page to collect anchor elements.
- **Methods:** 4

#### bf-cache-failures
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/bf-cache-failures.js`
- **Type:** JavaScript
- **Size:** 5.6 KB
- **Methods:** 2

#### stacks
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/stacks.js`
- **Type:** JavaScript
- **Size:** 4.02 KB
- **Capabilities:**
  - Obtains a list of detected JS libraries and their versions.
- **Methods:** 1

#### inspector-issues
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/inspector-issues.js`
- **Type:** JavaScript
- **Size:** 3.27 KB

#### css-usage
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/css-usage.js`
- **Type:** JavaScript
- **Size:** 4.52 KB
- **Capabilities:**
  - Initialize as undefined so we can assert results are fetched.

#### trace-compat
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/trace-compat.js`
- **Type:** JavaScript
- **Size:** 875 B

#### full-page-screenshot
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/full-page-screenshot.js`
- **Type:** JavaScript
- **Size:** 9.36 KB
- **Capabilities:**
  - Gatherers can collect details about DOM nodes, including their position on the page.
- **Methods:** 5

#### global-listeners
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/global-listeners.js`
- **Type:** JavaScript
- **Size:** 3.2 KB

#### trace-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/trace-elements.js`
- **Type:** JavaScript
- **Size:** 13.58 KB
- **Capabilities:**
  - This function finds the top (up to 15) elements that shift on the page.
  - We want to a single representative node to represent the shift, so let's pick
  - This function finds the top (up to 15) layout shifts on the page, and returns
- **Methods:** 2

#### network-user-agent
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/network-user-agent.js`
- **Type:** JavaScript
- **Size:** 1.11 KB

#### script-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/script-elements.js`
- **Type:** JavaScript
- **Size:** 2.8 KB
- **Methods:** 1

#### trace
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/trace.js`
- **Type:** JavaScript
- **Size:** 4.27 KB
- **Capabilities:**
  - Listener for when dataCollected events fire for each trace chunk

#### font-size
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/seo/font-size.js`
- **Type:** JavaScript
- **Size:** 11.96 KB
- **Capabilities:**
  - Finds the most specific directly matched CSS font-size rule from the list.
  - Returns the governing/winning CSS font-size rule for the set of styles given.
  - Returns the TextNodes in a DOM Snapshot.
- **Methods:** 6

#### embedded-content
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/seo/embedded-content.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Methods:** 1

#### tap-targets
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/seo/tap-targets.js`
- **Type:** JavaScript
- **Size:** 11.6 KB
- **Capabilities:**
  - Check if element is in a block of text, such as paragraph with a bunch of links in it.
  - Finds all position sticky/absolute elements on the page and adds a class
  - This needs to be in the constructor.
- **Methods:** 11

#### robots-txt
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/seo/robots-txt.js`
- **Type:** JavaScript
- **Size:** 740 B

#### root-causes
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/root-causes.js`
- **Type:** JavaScript
- **Size:** 5.73 KB

#### console-messages
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/console-messages.js`
- **Type:** JavaScript
- **Size:** 5.08 KB
- **Capabilities:**
  - Handles events for when a script invokes a console API.
  - Handles exception thrown events.
  - Handles browser reports logged to the console, including interventions,
- **Methods:** 1

#### iframe-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/iframe-elements.js`
- **Type:** JavaScript
- **Size:** 1.91 KB
- **Methods:** 1

#### cache-contents
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/cache-contents.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Creates an array of cached URLs.
- **Methods:** 1

#### image-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/gather/gatherers/image-elements.js`
- **Type:** JavaScript
- **Size:** 12.27 KB
- **Capabilities:**
  - If an image is within `picture`, the `picture` element's css position
  - Finds the most specific directly matched CSS font-size rule from the list.
  - Images might be sized via CSS. In order to compute unsized-images failures, we need to collect
- **Methods:** 10

#### index
- **File:** `frontend-tools/node_modules/lighthouse/core/index.js`
- **Type:** JavaScript
- **Size:** 4.23 KB
- **Capabilities:**
  - Run Lighthouse.
- **Methods:** 8

#### validation
- **File:** `frontend-tools/node_modules/lighthouse/core/config/validation.js`
- **Type:** JavaScript
- **Size:** 9.29 KB
- **Capabilities:**
  - Determines if the artifact dependency direction is valid. The dependency's minimum supported mode
  - Throws if pluginName is invalid or (somehow) collides with a category in the
  - Throws an error if the provided object does not implement the required gatherer interface.
- **Methods:** 10

#### config
- **File:** `frontend-tools/node_modules/lighthouse/core/config/config.js`
- **Type:** JavaScript
- **Size:** 9 KB
- **Capabilities:**
  - Certain gatherers are destructive to the page state.
  - Looks up the required artifact IDs for each dependency, throwing if no earlier artifact satisfies the dependency.
  - Overrides the settings that may not apply to the chosen gather mode.
- **Methods:** 8

#### config-helpers
- **File:** `frontend-tools/node_modules/lighthouse/core/config/config-helpers.js`
- **Type:** JavaScript
- **Size:** 21.04 KB
- **Capabilities:**
  - If any items with identical `path` properties are found in the input array,
  - Recursively merges config fragment objects in a somewhat Lighthouse-specific way.
  - Until support of jsdoc templates with constraints, type in config.d.ts.
- **Methods:** 17

#### config-plugin
- **File:** `frontend-tools/node_modules/lighthouse/core/config/config-plugin.js`
- **Type:** JavaScript
- **Size:** 7.48 KB
- **Capabilities:**
  - Asserts that obj has no own properties, throwing a nice error message if it does.
  - A set of methods for extracting and validating a Lighthouse plugin config.
  - Extract and validate the list of AuditDefns added by the plugin (or undefined
- **Methods:** 5

#### filters
- **File:** `frontend-tools/node_modules/lighthouse/core/config/filters.js`
- **Type:** JavaScript
- **Size:** 11.07 KB
- **Capabilities:**
  - Returns the set of audit IDs used in the list of categories.
  - Filters an array of artifacts down to the set that's required by the specified audits.
  - Filters an array of artifacts down to the set that supports the specified gather mode.
- **Methods:** 12

#### budget
- **File:** `frontend-tools/node_modules/lighthouse/core/config/budget.js`
- **Type:** JavaScript
- **Size:** 11.33 KB
- **Capabilities:**
  - Returns whether `val` is numeric. Will not coerce to a number. `NaN` will
  - Asserts that obj has no own properties, throwing a nice error message if it does.
  - Asserts that `strings` has no duplicate strings in it, throwing an error if
- **Methods:** 3

#### load-simulator
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/load-simulator.js`
- **Type:** JavaScript
- **Size:** 3.36 KB

#### document-urls
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/document-urls.js`
- **Type:** JavaScript
- **Size:** 1.99 KB

#### screenshots
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/screenshots.js`
- **Type:** JavaScript
- **Size:** 717 B

#### manifest-values
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/manifest-values.js`
- **Type:** JavaScript
- **Size:** 4.87 KB
- **Capabilities:**
  - Returns results of all manifest checks

#### module-duplication
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/module-duplication.js`
- **Type:** JavaScript
- **Size:** 4.28 KB

#### lcp-image-record
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/lcp-image-record.js`
- **Type:** JavaScript
- **Size:** 3.02 KB

#### tbt-impact-tasks
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/tbt-impact-tasks.js`
- **Type:** JavaScript
- **Size:** 6.92 KB

#### resource-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/resource-summary.js`
- **Type:** JavaScript
- **Size:** 4.72 KB

#### network-analysis
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/network-analysis.js`
- **Type:** JavaScript
- **Size:** 2.51 KB

#### network-records
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/network-records.js`
- **Type:** JavaScript
- **Size:** 631 B

#### user-timings
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/user-timings.js`
- **Type:** JavaScript
- **Size:** 2.94 KB

#### viewport-meta
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/viewport-meta.js`
- **Type:** JavaScript
- **Size:** 2.17 KB

#### unused-css
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/unused-css.js`
- **Type:** JavaScript
- **Size:** 5.8 KB
- **Capabilities:**
  - Adds used rules to their corresponding stylesheet.
  - Trims stylesheet content down to the first rule-set definition.

#### processed-navigation
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/processed-navigation.js`
- **Type:** JavaScript
- **Size:** 1.29 KB

#### main-thread-tasks
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/main-thread-tasks.js`
- **Type:** JavaScript
- **Size:** 867 B

#### critical-request-chains
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/critical-request-chains.js`
- **Type:** JavaScript
- **Size:** 5.18 KB
- **Capabilities:**
  - For now, we use network priorities as a proxy for "render-blocking"/critical-ness.
  - Create a tree of critical requests.
- **Methods:** 2

#### js-bundles
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/js-bundles.js`
- **Type:** JavaScript
- **Size:** 3.44 KB
- **Capabilities:**
  - Calculate the number of bytes contributed by each source file
- **Methods:** 2

#### processed-trace
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/processed-trace.js`
- **Type:** JavaScript
- **Size:** 545 B

#### unused-javascript-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/unused-javascript-summary.js`
- **Type:** JavaScript
- **Size:** 4.4 KB

#### page-dependency-graph
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/page-dependency-graph.js`
- **Type:** JavaScript
- **Size:** 1.59 KB

#### entity-classification
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/entity-classification.js`
- **Type:** JavaScript
- **Size:** 4.95 KB
- **Capabilities:**
  - Preload Chrome extensions found in the devtoolsLog into cache.
  - Convenience function to check if a URL belongs to first party.
- **Methods:** 2

#### trace-engine-result
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/trace-engine-result.js`
- **Type:** JavaScript
- **Size:** 3.04 KB

#### speedline
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/speedline.js`
- **Type:** JavaScript
- **Size:** 1.77 KB

#### main-resource
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/main-resource.js`
- **Type:** JavaScript
- **Size:** 1.67 KB

#### image-records
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/image-records.js`
- **Type:** JavaScript
- **Size:** 2.15 KB

#### computed-artifact
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/computed-artifact.js`
- **Type:** JavaScript
- **Size:** 2.54 KB
- **Capabilities:**
  - Decorate computableArtifact with a caching `request()` method which will
  - Return an automatically cached result from the computed artifact.
- **Methods:** 1

#### lantern-largest-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-largest-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 3.64 KB
- **Capabilities:**
  - Low priority image nodes are usually offscreen and very unlikely to be the

#### metric
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/metric.js`
- **Type:** JavaScript
- **Size:** 3.47 KB
- **Capabilities:**
  - Narrows the metric computation data to the input so child metric requests can be cached.

#### lantern-total-blocking-time
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-total-blocking-time.js`
- **Type:** JavaScript
- **Size:** 4.09 KB

#### first-contentful-paint-all-frames
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/first-contentful-paint-all-frames.js`
- **Type:** JavaScript
- **Size:** 1.11 KB

#### total-blocking-time
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/total-blocking-time.js`
- **Type:** JavaScript
- **Size:** 2.64 KB

#### time-to-first-byte
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/time-to-first-byte.js`
- **Type:** JavaScript
- **Size:** 2.42 KB

#### lantern-interactive
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-interactive.js`
- **Type:** JavaScript
- **Size:** 3.67 KB

#### cumulative-layout-shift
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/cumulative-layout-shift.js`
- **Type:** JavaScript
- **Size:** 10 KB
- **Capabilities:**
  - Returns all LayoutShift events that had no recent input.
  - Each layout shift event has a 'score' which is the amount added to the CLS as a result of the given shift(s).
  - Calculates cumulative layout shifts per cluster (session) of LayoutShift

#### largest-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/largest-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 1.86 KB

#### lantern-first-meaningful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-first-meaningful-paint.js`
- **Type:** JavaScript
- **Size:** 2.81 KB

#### lcp-breakdown
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lcp-breakdown.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### navigation-metric
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/navigation-metric.js`
- **Type:** JavaScript
- **Size:** 1.3 KB

#### lantern-metric
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-metric.js`
- **Type:** JavaScript
- **Size:** 1.59 KB

#### speed-index
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/speed-index.js`
- **Type:** JavaScript
- **Size:** 1.34 KB

#### largest-contentful-paint-all-frames
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/largest-contentful-paint-all-frames.js`
- **Type:** JavaScript
- **Size:** 1.42 KB
- **Capabilities:**
  - TODO: Simulate LCP all frames in lantern.

#### first-meaningful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/first-meaningful-paint.js`
- **Type:** JavaScript
- **Size:** 1.47 KB

#### tbt-utils
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/tbt-utils.js`
- **Type:** JavaScript
- **Size:** 3.01 KB
- **Capabilities:**
  - For TBT, We only want to consider tasks that fall in our time range
- **Methods:** 2

#### lantern-max-potential-fid
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-max-potential-fid.js`
- **Type:** JavaScript
- **Size:** 2.82 KB

#### lantern-first-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-first-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 8.71 KB
- **Capabilities:**
  - This function computes the set of URLs that *appeared* to be render-blocking based on our filter,
  - This function computes the graph required for the first paint of interest.
- **Methods:** 2

#### interactive
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/interactive.js`
- **Type:** JavaScript
- **Size:** 7.01 KB
- **Capabilities:**
  - Finds all time periods where the number of inflight requests is less than or equal to the
  - Finds all time periods where there are no long tasks.
  - Finds the first time period where a network quiet period and a CPU quiet period overlap.

#### timing-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/timing-summary.js`
- **Type:** JavaScript
- **Size:** 8.25 KB

#### first-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/first-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 1.28 KB

#### max-potential-fid
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/max-potential-fid.js`
- **Type:** JavaScript
- **Size:** 1.44 KB

#### responsiveness
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/responsiveness.js`
- **Type:** JavaScript
- **Size:** 6.82 KB
- **Capabilities:**
  - Finds the interaction event that was probably the responsivenessEvent.maxDuration
- **Methods:** 1

#### lantern-speed-index
- **File:** `frontend-tools/node_modules/lighthouse/core/computed/metrics/lantern-speed-index.js`
- **Type:** JavaScript
- **Size:** 5.94 KB
- **Capabilities:**
  - Approximate speed index using layout events from the simulated node timings.

#### long-tasks
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/long-tasks.js`
- **Type:** JavaScript
- **Size:** 9.53 KB
- **Capabilities:**
  - Insert `url` into `urls` array if not already present. Returns
  - Returns the timing information for the given task, recursively walking the
  - Get timing from task, overridden by taskTimingsByEvent if provided.
- **Methods:** 2

#### audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/audit.js`
- **Type:** JavaScript
- **Size:** 13.82 KB
- **Capabilities:**
  - Clamp figure to 2 decimal places
  - Computes a score between 0 and 1 based on the measured `value`. Score is determined by
  - This catches typos in the `key` property of a heading definition of table/opportunity details.

#### bootup-time
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/bootup-time.js`
- **Type:** JavaScript
- **Size:** 7.91 KB

#### predictive-perf
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/predictive-perf.js`
- **Type:** JavaScript
- **Size:** 3.97 KB

#### csp-xss
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/csp-xss.js`
- **Type:** JavaScript
- **Size:** 7.1 KB

#### errors-in-console
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/errors-in-console.js`
- **Type:** JavaScript
- **Size:** 4.5 KB

#### non-composited-animations
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/non-composited-animations.js`
- **Type:** JavaScript
- **Size:** 8.28 KB
- **Capabilities:**
  - [ICU Syntax] Descriptive reason for why a user-provided animation failed to be optimized by the browser due to the animated CSS property not being supported on the compositor. Shown in a table with a list of other potential failure reasons.
  - Each failure reason is represented by a bit flag. The bit shift operator '<<' is used to define which bit corresponds to each failure reason.
  - Return list of actionable failure reasons and a boolean if some reasons are not actionable.
- **Methods:** 1

#### screenshot-thumbnails
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/screenshot-thumbnails.js`
- **Type:** JavaScript
- **Size:** 5.57 KB
- **Capabilities:**
  - Scales down an image to THUMBNAIL_WIDTH using nearest neighbor for speed, maintains aspect

#### network-rtt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/network-rtt.js`
- **Type:** JavaScript
- **Size:** 3.25 KB

#### third-party-facades
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/third-party-facades.js`
- **Type:** JavaScript
- **Size:** 10.88 KB
- **Capabilities:**
  - Template for a table entry that gives the name of a product which we categorize as video related.
  - Sort items by transfer size and combine small items into a single row.

#### lcp-lazy-loaded
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/lcp-lazy-loaded.js`
- **Type:** JavaScript
- **Size:** 4.34 KB

#### diagnostics
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/diagnostics.js`
- **Type:** JavaScript
- **Size:** 2.88 KB

#### themed-omnibox
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/themed-omnibox.js`
- **Type:** JavaScript
- **Size:** 3.69 KB

#### layout-shifts
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/layout-shifts.js`
- **Type:** JavaScript
- **Size:** 7.22 KB

#### uses-rel-preload
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/uses-rel-preload.js`
- **Type:** JavaScript
- **Size:** 11.32 KB
- **Capabilities:**
  - A warning message that is shown when the user tried to follow the advice of the audit, but it's not working as expected. Forgetting to set the `crossorigin` HTML attribute, or setting it to an incorrect value, on the link is a common mistake when adding preload links.
  - Finds which URLs were attempted to be preloaded, but failed to be reused and were requested again.
  - We want to preload all first party critical requests at depth 2.

#### installable-manifest
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/installable-manifest.js`
- **Type:** JavaScript
- **Size:** 12.9 KB
- **Capabilities:**
  - [ICU Syntax] Label for an audit identifying the number of installability errors found in the page.
  - If there is an argument value, get it.

#### image-size-responsive
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/image-size-responsive.js`
- **Type:** JavaScript
- **Size:** 11.48 KB
- **Capabilities:**
  - Type check to ensure that the ImageElement has natural dimensions.
  - Compute the size an image should have given the display dimensions and pixel density in order to
  - Compute the size an image should have given the display dimensions and pixel density.
- **Methods:** 11

#### doctype
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/doctype.js`
- **Type:** JavaScript
- **Size:** 5.02 KB

#### dom-size
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/dom-size.js`
- **Type:** JavaScript
- **Size:** 7.19 KB

#### no-document-write
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/no-document-write.js`
- **Type:** JavaScript
- **Size:** 4.18 KB

#### geolocation-on-start
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/geolocation-on-start.js`
- **Type:** JavaScript
- **Size:** 2.89 KB

#### js-libraries
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/js-libraries.js`
- **Type:** JavaScript
- **Size:** 2.56 KB

#### paste-preventing-inputs
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/paste-preventing-inputs.js`
- **Type:** JavaScript
- **Size:** 2.45 KB

#### inspector-issues
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/inspector-issues.js`
- **Type:** JavaScript
- **Size:** 6.68 KB

#### uses-http2
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/uses-http2.js`
- **Type:** JavaScript
- **Size:** 12.58 KB
- **Capabilities:**
  - Computes the estimated effect of all results being converted to http/2 on the provided graph.
  - Computes the estimated effect all results being converted to use http/2, the max of:
  - Determines whether a network request is a "static resource" that would benefit from H2 multiplexing.

#### charset
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/charset.js`
- **Type:** JavaScript
- **Size:** 3.9 KB

#### notification-on-start
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/notification-on-start.js`
- **Type:** JavaScript
- **Size:** 2.76 KB

#### uses-passive-event-listeners
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/dobetterweb/uses-passive-event-listeners.js`
- **Type:** JavaScript
- **Size:** 2.8 KB

#### resource-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/resource-summary.js`
- **Type:** JavaScript
- **Size:** 3.47 KB

#### timing-budget
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/timing-budget.js`
- **Type:** JavaScript
- **Size:** 6.69 KB
- **Capabilities:**
  - Note: SpeedIndex, unlike other timing metrics, is not measured in milliseconds.

#### content-width
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/content-width.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Explanatory message stating that the viewport size and window size differ.

#### prioritize-lcp-image
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/prioritize-lcp-image.js`
- **Type:** JavaScript
- **Size:** 11.44 KB
- **Capabilities:**
  - Get the initiator path starting with lcpRecord back to mainResource, inclusive.
  - Computes the estimated effect of preloading the LCP image.
- **Methods:** 1

#### redirects
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/redirects.js`
- **Type:** JavaScript
- **Size:** 6.37 KB
- **Capabilities:**
  - This method generates the document request chain including client-side and server-side redirects.

#### autocomplete
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/autocomplete.js`
- **Type:** JavaScript
- **Size:** 14.14 KB
- **Capabilities:**
  - Warning that autocomplete token is invalid.
  - The autocomplete attribute can have multiple tokens in it. All tokens should be valid and in the correct order.

#### user-timings
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/user-timings.js`
- **Type:** JavaScript
- **Size:** 4.52 KB
- **Capabilities:**
  - We remove mark/measures entered by third parties not of interest to the user

#### violation-audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/violation-audit.js`
- **Type:** JavaScript
- **Size:** 1.52 KB
- **Methods:** 1

#### preload-fonts
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/preload-fonts.js`
- **Type:** JavaScript
- **Size:** 3.94 KB
- **Capabilities:**
  - Finds which font URLs were attempted to be preloaded,

#### network-requests
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/network-requests.js`
- **Type:** JavaScript
- **Size:** 4.97 KB

#### multi-check-audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/multi-check-audit.js`
- **Type:** JavaScript
- **Size:** 2.01 KB

#### main-thread-tasks
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/main-thread-tasks.js`
- **Type:** JavaScript
- **Size:** 1.57 KB

#### third-party-cookies
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/third-party-cookies.js`
- **Type:** JavaScript
- **Size:** 4.01 KB
- **Capabilities:**
  - https://source.chromium.org/chromium/chromium/src/+/d2fcd4ba302baeabf4b96d8fa9fdb7a215736c31:third_party/devtools-frontend/src/front_end/models/issues_manager/CookieIssue.ts;l=62-69

#### deprecations
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/deprecations.js`
- **Type:** JavaScript
- **Size:** 4.35 KB

#### mainthread-work-breakdown
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/mainthread-work-breakdown.js`
- **Type:** JavaScript
- **Size:** 5.64 KB

#### critical-request-chains
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/critical-request-chains.js`
- **Type:** JavaScript
- **Size:** 7.71 KB
- **Capabilities:**
  - Get stats about the longest initiator chain (as determined by time duration)
  - Audits the page to give a score for First Meaningful Paint.
- **Methods:** 2

#### splash-screen
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/splash-screen.js`
- **Type:** JavaScript
- **Size:** 3.24 KB

#### maskable-icon
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/maskable-icon.js`
- **Type:** JavaScript
- **Size:** 2.58 KB

#### server-response-time
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/server-response-time.js`
- **Type:** JavaScript
- **Size:** 4.48 KB

#### largest-contentful-paint-element
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/largest-contentful-paint-element.js`
- **Type:** JavaScript
- **Size:** 6.61 KB

#### viewport
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/viewport.js`
- **Type:** JavaScript
- **Size:** 3.05 KB

#### layout-shift-elements
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/layout-shift-elements.js`
- **Type:** JavaScript
- **Size:** 3.86 KB

#### uses-rel-preconnect
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/uses-rel-preconnect.js`
- **Type:** JavaScript
- **Size:** 12.1 KB
- **Capabilities:**
  - A warning message that is shown when the user tried to follow the advice of the audit, but it's not working as expected.
  - Check if record has valid timing
  - Check is the connection is already open

#### final-screenshot
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/final-screenshot.js`
- **Type:** JavaScript
- **Size:** 1.76 KB

#### script-treemap-data
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/script-treemap-data.js`
- **Type:** JavaScript
- **Size:** 9.54 KB
- **Capabilities:**
  - Returns a tree data structure where leaf nodes are sources (ie. real files from source tree)
  - Given a slash-delimited path, traverse the Node structure and increment
  - Collapse nodes that have only one child.
- **Methods:** 3

#### unsized-images
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/unsized-images.js`
- **Type:** JavaScript
- **Size:** 6.96 KB
- **Capabilities:**
  - An img size attribute prevents layout shifts if it is a non-negative integer (incl zero!).
  - An img css size property prevents layout shifts if it is defined, not empty, and not equal to 'auto'.
  - Images are considered sized if they have defined & valid values.

#### third-party-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/third-party-summary.js`
- **Type:** JavaScript
- **Size:** 9.73 KB

#### performance-budget
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/performance-budget.js`
- **Type:** JavaScript
- **Size:** 6.07 KB

#### network-server-latency
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/network-server-latency.js`
- **Type:** JavaScript
- **Size:** 3.3 KB

#### font-display
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/font-display.js`
- **Type:** JavaScript
- **Size:** 8.28 KB
- **Capabilities:**
  - [ICU Syntax] A warning message that is shown when Lighthouse couldn't automatically check some of the page's fonts, telling the user that they will need to manually check the fonts coming from a certain URL origin.
  - Some pages load many fonts we can't check, so dedupe on origin.

#### image-redundant-alt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/image-redundant-alt.js`
- **Type:** JavaScript
- **Size:** 2.09 KB

#### list
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/list.js`
- **Type:** JavaScript
- **Size:** 1.96 KB

#### image-alt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/image-alt.js`
- **Type:** JavaScript
- **Size:** 1.91 KB

#### object-alt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/object-alt.js`
- **Type:** JavaScript
- **Size:** 1.91 KB

#### table-fake-caption
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/table-fake-caption.js`
- **Type:** JavaScript
- **Size:** 2.12 KB

#### aria-progressbar-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-progressbar-name.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### html-lang-valid
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/html-lang-valid.js`
- **Type:** JavaScript
- **Size:** 2 KB

#### button-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/button-name.js`
- **Type:** JavaScript
- **Size:** 1.85 KB

#### aria-allowed-attr
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-allowed-attr.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### meta-refresh
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/meta-refresh.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### axe-audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/axe-audit.js`
- **Type:** JavaScript
- **Size:** 3.92 KB
- **Capabilities:**
  - Base class for audit rules which reflect assessment performed by the aXe accessibility library

#### td-headers-attr
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/td-headers-attr.js`
- **Type:** JavaScript
- **Size:** 2.31 KB

#### aria-tooltip-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-tooltip-name.js`
- **Type:** JavaScript
- **Size:** 1.89 KB

#### empty-heading
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/empty-heading.js`
- **Type:** JavaScript
- **Size:** 1.83 KB

#### aria-treeitem-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-treeitem-name.js`
- **Type:** JavaScript
- **Size:** 1.94 KB

#### aria-input-field-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-input-field-name.js`
- **Type:** JavaScript
- **Size:** 1.87 KB

#### aria-required-parent
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-required-parent.js`
- **Type:** JavaScript
- **Size:** 2.36 KB

#### input-image-alt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/input-image-alt.js`
- **Type:** JavaScript
- **Size:** 1.97 KB

#### html-has-lang
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/html-has-lang.js`
- **Type:** JavaScript
- **Size:** 2.07 KB

#### aria-command-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-command-name.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### duplicate-id-active
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/duplicate-id-active.js`
- **Type:** JavaScript
- **Size:** 1.92 KB

#### aria-toggle-field-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-toggle-field-name.js`
- **Type:** JavaScript
- **Size:** 1.88 KB

#### frame-title
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/frame-title.js`
- **Type:** JavaScript
- **Size:** 1.92 KB

#### bypass
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/bypass.js`
- **Type:** JavaScript
- **Size:** 2.23 KB

#### label-content-name-mismatch
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/label-content-name-mismatch.js`
- **Type:** JavaScript
- **Size:** 2.09 KB

#### input-button-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/input-button-name.js`
- **Type:** JavaScript
- **Size:** 1.82 KB

#### target-size
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/target-size.js`
- **Type:** JavaScript
- **Size:** 1.91 KB

#### video-caption
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/video-caption.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### aria-hidden-body
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-hidden-body.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### duplicate-id-aria
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/duplicate-id-aria.js`
- **Type:** JavaScript
- **Size:** 1.85 KB

#### color-contrast
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/color-contrast.js`
- **Type:** JavaScript
- **Size:** 2 KB

#### aria-hidden-focus
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-hidden-focus.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### link-in-text-block
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/link-in-text-block.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### valid-lang
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/valid-lang.js`
- **Type:** JavaScript
- **Size:** 1.89 KB

#### select-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/select-name.js`
- **Type:** JavaScript
- **Size:** 1.88 KB

#### document-title
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/document-title.js`
- **Type:** JavaScript
- **Size:** 1.87 KB

#### listitem
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/listitem.js`
- **Type:** JavaScript
- **Size:** 1.94 KB

#### td-has-header
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/td-has-header.js`
- **Type:** JavaScript
- **Size:** 2.01 KB

#### heading-order
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/heading-order.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### skip-link
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/skip-link.js`
- **Type:** JavaScript
- **Size:** 1.68 KB

#### aria-valid-attr
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-valid-attr.js`
- **Type:** JavaScript
- **Size:** 1.92 KB

#### html-xml-lang-mismatch
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/html-xml-lang-mismatch.js`
- **Type:** JavaScript
- **Size:** 2.17 KB

#### aria-dialog-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-dialog-name.js`
- **Type:** JavaScript
- **Size:** 2 KB

#### label
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/label.js`
- **Type:** JavaScript
- **Size:** 1.78 KB

#### tabindex
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/tabindex.js`
- **Type:** JavaScript
- **Size:** 2.01 KB

#### definition-list
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/definition-list.js`
- **Type:** JavaScript
- **Size:** 2.05 KB

#### th-has-data-cells
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/th-has-data-cells.js`
- **Type:** JavaScript
- **Size:** 2.1 KB

#### table-duplicate-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/table-duplicate-name.js`
- **Type:** JavaScript
- **Size:** 2.02 KB

#### aria-text
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-text.js`
- **Type:** JavaScript
- **Size:** 1.94 KB

#### landmark-one-main
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/landmark-one-main.js`
- **Type:** JavaScript
- **Size:** 1.78 KB

#### aria-valid-attr-value
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-valid-attr-value.js`
- **Type:** JavaScript
- **Size:** 1.91 KB

#### aria-allowed-role
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-allowed-role.js`
- **Type:** JavaScript
- **Size:** 2.06 KB

#### aria-required-attr
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-required-attr.js`
- **Type:** JavaScript
- **Size:** 1.96 KB

#### aria-roles
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-roles.js`
- **Type:** JavaScript
- **Size:** 1.78 KB

#### dlitem
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/dlitem.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### link-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/link-name.js`
- **Type:** JavaScript
- **Size:** 1.86 KB

#### identical-links-same-purpose
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/identical-links-same-purpose.js`
- **Type:** JavaScript
- **Size:** 1.94 KB

#### form-field-multiple-labels
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/form-field-multiple-labels.js`
- **Type:** JavaScript
- **Size:** 1.93 KB

#### meta-viewport
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/meta-viewport.js`
- **Type:** JavaScript
- **Size:** 2.16 KB

#### aria-required-children
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-required-children.js`
- **Type:** JavaScript
- **Size:** 2.21 KB

#### aria-meter-name
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/aria-meter-name.js`
- **Type:** JavaScript
- **Size:** 1.88 KB

#### accesskeys
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/accesskeys.js`
- **Type:** JavaScript
- **Size:** 1.87 KB

#### focus-traps
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/focus-traps.js`
- **Type:** JavaScript
- **Size:** 744 B

#### use-landmarks
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/use-landmarks.js`
- **Type:** JavaScript
- **Size:** 804 B

#### visual-order-follows-dom
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/visual-order-follows-dom.js`
- **Type:** JavaScript
- **Size:** 796 B

#### focusable-controls
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/focusable-controls.js`
- **Type:** JavaScript
- **Size:** 742 B

#### interactive-element-affordance
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/interactive-element-affordance.js`
- **Type:** JavaScript
- **Size:** 880 B

#### offscreen-content-hidden
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/offscreen-content-hidden.js`
- **Type:** JavaScript
- **Size:** 809 B

#### logical-tab-order
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/logical-tab-order.js`
- **Type:** JavaScript
- **Size:** 751 B

#### custom-controls-labels
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/custom-controls-labels.js`
- **Type:** JavaScript
- **Size:** 790 B

#### managed-focus
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/managed-focus.js`
- **Type:** JavaScript
- **Size:** 776 B

#### custom-controls-roles
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/accessibility/manual/custom-controls-roles.js`
- **Type:** JavaScript
- **Size:** 737 B

#### image-aspect-ratio
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/image-aspect-ratio.js`
- **Type:** JavaScript
- **Size:** 5.64 KB

#### bf-cache
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/bf-cache.js`
- **Type:** JavaScript
- **Size:** 6 KB
- **Capabilities:**
  - [ICU Syntax] Label for an audit identifying the number of back/forward cache failure reasons found in the page.

#### font-size
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/font-size.js`
- **Type:** JavaScript
- **Size:** 12.2 KB
- **Capabilities:**
  - TODO: return unique selector, like axe-core does, instead of just id/class/name of a single node
- **Methods:** 6

#### plugins
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/plugins.js`
- **Type:** JavaScript
- **Size:** 4.35 KB
- **Capabilities:**
  - Verifies if given MIME type matches any known plugin MIME type
  - Verifies if given url points to a file that has a known plugin extension
- **Methods:** 2

#### crawlable-anchors
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/crawlable-anchors.js`
- **Type:** JavaScript
- **Size:** 3.5 KB

#### http-status-code
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/http-status-code.js`
- **Type:** JavaScript
- **Size:** 2.5 KB

#### meta-description
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/meta-description.js`
- **Type:** JavaScript
- **Size:** 2.43 KB

#### tap-targets
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/tap-targets.js`
- **Type:** JavaScript
- **Size:** 12.75 KB
- **Capabilities:**
  - Returns a tap target augmented with a bounding rect for quick overlapping
  - A target is "too small" if none of its clientRects are at least the size of a finger.
  - Only report one failure if two targets overlap each other
- **Methods:** 7

#### hreflang
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/hreflang.js`
- **Type:** JavaScript
- **Size:** 5.14 KB
- **Methods:** 2

#### canonical
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/canonical.js`
- **Type:** JavaScript
- **Size:** 8.37 KB
- **Capabilities:**
  - Explanatory message stating that there was a failure in an audit caused by multiple URLs conflicting with each other.

#### link-text
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/link-text.js`
- **Type:** JavaScript
- **Size:** 5.03 KB

#### robots-txt
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/robots-txt.js`
- **Type:** JavaScript
- **Size:** 8.17 KB
- **Capabilities:**
  - Label for the audit identifying that the robots.txt request has returned a specific HTTP status code. Note: "robots.txt" is a canonical filename and should not be translated.
  - Validate Robots
- **Methods:** 3

#### is-crawlable
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/is-crawlable.js`
- **Type:** JavaScript
- **Size:** 7.95 KB
- **Capabilities:**
  - Checks if given directive is a valid unavailable_after directive with a date in the past
  - Returns true if any of provided directives blocks page from being indexed
  - Returns user agent if specified in robots header (e.g. `googlebot: noindex`)
- **Methods:** 3

#### structured-data
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/seo/manual/structured-data.js`
- **Type:** JavaScript
- **Size:** 1.67 KB

#### no-unload-listeners
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/no-unload-listeners.js`
- **Type:** JavaScript
- **Size:** 3.05 KB

#### metrics
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### valid-source-maps
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/valid-source-maps.js`
- **Type:** JavaScript
- **Size:** 6.42 KB
- **Capabilities:**
  - Returns true if the size of the script exceeds a static threshold.

#### legacy-javascript
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/legacy-javascript.js`
- **Type:** JavaScript
- **Size:** 18.2 KB
- **Capabilities:**
  - Takes a list of patterns (consisting of a name identifier and a RegExp expression string)
  - Returns a collection of match results grouped by script url.
- **Methods:** 2

#### uses-responsive-images
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/uses-responsive-images.js`
- **Type:** JavaScript
- **Size:** 7.95 KB

#### uses-long-cache-ttl
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/uses-long-cache-ttl.js`
- **Type:** JavaScript
- **Size:** 11.02 KB
- **Capabilities:**
  - Computes the percent likelihood that a return visit will be within the cache lifetime, based on
  - Return max-age if defined, otherwise expires header if defined, and null if not.
  - Given a network record, returns whether we believe the asset is cacheable, i.e. it was a network

#### uses-optimized-images
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/uses-optimized-images.js`
- **Type:** JavaScript
- **Size:** 5.54 KB

#### unused-javascript
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/unused-javascript.js`
- **Type:** JavaScript
- **Size:** 6.06 KB
- **Methods:** 2

#### modern-image-formats
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/modern-image-formats.js`
- **Type:** JavaScript
- **Size:** 7.52 KB

#### total-byte-weight
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/total-byte-weight.js`
- **Type:** JavaScript
- **Size:** 4.47 KB

#### efficient-animated-content
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/efficient-animated-content.js`
- **Type:** JavaScript
- **Size:** 3.69 KB
- **Capabilities:**
  - Calculate rough savings percentage based on 1000 real gifs transcoded to video

#### uses-responsive-images-snapshot
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/uses-responsive-images-snapshot.js`
- **Type:** JavaScript
- **Size:** 3.96 KB

#### duplicated-javascript
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/duplicated-javascript.js`
- **Type:** JavaScript
- **Size:** 8.16 KB
- **Capabilities:**
  - This audit highlights JavaScript modules that appear to be duplicated across all resources,
- **Methods:** 1

#### render-blocking-resources
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/render-blocking-resources.js`
- **Type:** JavaScript
- **Size:** 12.95 KB
- **Capabilities:**
  - Given a simulation's nodeTimings, return an object with the nodes/timing keyed by network URL
  - Adjust the timing of a node and its dependencies to account for stack specific overrides.
  - Any stack specific timing overrides should go in this function.
- **Methods:** 3

#### offscreen-images
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/offscreen-images.js`
- **Type:** JavaScript
- **Size:** 10.91 KB
- **Capabilities:**
  - Filters out image requests that were requested after the last long task based on lantern timings.
  - Filters out image requests that were requested after TTI.
  - The default byte efficiency audit will report max(TTI, load), since lazy-loading offscreen
- **Methods:** 1

#### unminified-css
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/unminified-css.js`
- **Type:** JavaScript
- **Size:** 4.22 KB
- **Capabilities:**
  - Computes the total length of the meaningful tokens (CSS excluding comments and whitespace).

#### uses-text-compression
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/uses-text-compression.js`
- **Type:** JavaScript
- **Size:** 3.55 KB

#### byte-efficiency-audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/byte-efficiency-audit.js`
- **Type:** JavaScript
- **Size:** 11.21 KB
- **Capabilities:**
  - Creates a score based on the wastedMs value using log-normal distribution scoring. A negative
  - Computes the estimated effect of all the byte savings on the provided graph.
  - Computes the estimated effect of all the byte savings on the maximum of the following:
- **Methods:** 1

#### unused-css-rules
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/unused-css-rules.js`
- **Type:** JavaScript
- **Size:** 2.86 KB

#### unminified-javascript
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/byte-efficiency/unminified-javascript.js`
- **Type:** JavaScript
- **Size:** 4.73 KB

#### total-blocking-time
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/total-blocking-time.js`
- **Type:** JavaScript
- **Size:** 4.52 KB
- **Capabilities:**
  - Audits the page to calculate Total Blocking Time.

#### cumulative-layout-shift
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/cumulative-layout-shift.js`
- **Type:** JavaScript
- **Size:** 2.87 KB

#### largest-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/largest-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 3.5 KB

#### speed-index
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/speed-index.js`
- **Type:** JavaScript
- **Size:** 3.11 KB
- **Capabilities:**
  - Audits the page to give a score for the Speed Index.

#### interaction-to-next-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/interaction-to-next-paint.js`
- **Type:** JavaScript
- **Size:** 2.81 KB

#### first-meaningful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/first-meaningful-paint.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Capabilities:**
  - Audits the page to give a score for First Meaningful Paint.

#### interactive
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/interactive.js`
- **Type:** JavaScript
- **Size:** 3.33 KB

#### first-contentful-paint
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/first-contentful-paint.js`
- **Type:** JavaScript
- **Size:** 3.04 KB

#### max-potential-fid
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/metrics/max-potential-fid.js`
- **Type:** JavaScript
- **Size:** 5.39 KB
- **Capabilities:**
  - Extract potential LoAF replacements for MPFID from the trace to log in

#### is-on-https
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/is-on-https.js`
- **Type:** JavaScript
- **Size:** 5.53 KB

#### work-during-interaction
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/work-during-interaction.js`
- **Type:** JavaScript
- **Size:** 12.13 KB
- **Capabilities:**
  - Summary text that identifies the time the browser took to process a user interaction.
  - Clip the tasks by the start and end points. Take the easy route and drop

#### pwa-page-transitions
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/manual/pwa-page-transitions.js`
- **Type:** JavaScript
- **Size:** 1.51 KB

#### manual-audit
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/manual/manual-audit.js`
- **Type:** JavaScript
- **Size:** 671 B

#### pwa-each-page-has-url
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/manual/pwa-each-page-has-url.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### pwa-cross-browser
- **File:** `frontend-tools/node_modules/lighthouse/core/audits/manual/pwa-cross-browser.js`
- **Type:** JavaScript
- **Size:** 1.33 KB

#### runner
- **File:** `frontend-tools/node_modules/lighthouse/core/runner.js`
- **Type:** JavaScript
- **Size:** 20.64 KB
- **Capabilities:**
  - List of top-level warnings for this Lighthouse run.
  - User can run -G solo, -A solo, or -GA together
  - This handles both the auditMode case where gatherer entries need to be merged in and

#### scoring
- **File:** `frontend-tools/node_modules/lighthouse/core/scoring.js`
- **Type:** JavaScript
- **Size:** 2.81 KB
- **Capabilities:**
  - Clamp figure to 2 decimal places
  - Computes the weighted-average of the score of the list of items.
  - Returns the report JSON object with computed scores.

#### icons
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/icons.js`
- **Type:** JavaScript
- **Size:** 2.76 KB
- **Methods:** 3

#### deprecation-description
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/deprecation-description.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - This links to the chrome feature status page when one exists.
- **Methods:** 1

#### polyfill-dom-rect
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/polyfill-dom-rect.js`
- **Type:** JavaScript
- **Size:** 2.7 KB
- **Methods:** 4

#### network-recorder
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/network-recorder.js`
- **Type:** JavaScript
- **Size:** 12.5 KB
- **Capabilities:**
  - Creates an instance of NetworkRecorder.
  - Returns the array of raw network request data without finalizing the initiator and
  - Listener for the DevTools SDK NetworkManager's RequestStarted event, which includes both

#### page-functions
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/page-functions.js`
- **Type:** JavaScript
- **Size:** 22.79 KB
- **Capabilities:**
  - `typed-query-selector`'s CSS selector parser.
  - The `exceptionDetails` provided by the debugger protocol does not contain the useful
  - Gets the opening tag text of the given node.
- **Methods:** 20

#### url-utils
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/url-utils.js`
- **Type:** JavaScript
- **Size:** 8.34 KB
- **Capabilities:**
  - There is fancy URL rewriting logic for the chrome://settings page that we need to work around.
  - Returns a primary domain for provided hostname (e.g. www.example.com -> example.com).
  - Check if rootDomains matches
- **Methods:** 1

#### lh-error
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lh-error.js`
- **Type:** JavaScript
- **Size:** 17.72 KB
- **Capabilities:**
  - Error message explaining that the Lighthouse run was not able to collect screenshots through Chrome.
  - A JSON.stringify replacer to serialize LighthouseErrors and (as a fallback) Errors.
  - A JSON.parse reviver. If any value passed in is a serialized Error or

#### stack-packs
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/stack-packs.js`
- **Type:** JavaScript
- **Size:** 3.21 KB
- **Capabilities:**
  - Pairs consisting of a stack pack's ID and the set of stacks needed to be
  - Returns all packs that match the stacks found in the page.
- **Methods:** 1

#### metric
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/metric.js`
- **Type:** JavaScript
- **Size:** 4.8 KB
- **Capabilities:**
  - Returns the coefficients, scaled by the throttling settings if needed by the metric.

#### base-node
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/base-node.js`
- **Type:** JavaScript
- **Size:** 10.3 KB
- **Capabilities:**
  - A union of all types derived from BaseNode, allowing type check discrimination
  - In microseconds
  - Computes whether the given node is anywhere in the dependency graph of this node.
- **Methods:** 1

#### network-node
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/network-node.js`
- **Type:** JavaScript
- **Size:** 2.7 KB
- **Capabilities:**
  - Returns whether this network record can be downloaded without a TCP connection.

#### simulator-timing-map
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/simulator-timing-map.js`
- **Type:** JavaScript
- **Size:** 6.57 KB

#### connection-pool
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/connection-pool.js`
- **Type:** JavaScript
- **Size:** 6.13 KB
- **Capabilities:**
  - This method finds an available connection to the origin specified by the network record or null
  - Return the connection currently being used to fetch a record. If no connection

#### tcp-connection
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/tcp-connection.js`
- **Type:** JavaScript
- **Size:** 6.4 KB
- **Capabilities:**
  - Sets the number of excess bytes that are available to this connection on future downloads, only
  - Simulates a network download of a particular number of bytes over an optional maximum amount of time

#### simulator
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/simulator.js`
- **Type:** JavaScript
- **Size:** 19.37 KB
- **Capabilities:**
  - Initializes the various state data structures such _nodeTimings and the _node Sets by state.
  - Updates each connection in use with the available throughput based on the number of network requests
  - Estimates the number of milliseconds remaining given current condidtions before the node is complete.

#### dns-cache
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/dns-cache.js`
- **Type:** JavaScript
- **Size:** 2.47 KB
- **Capabilities:**
  - Forcefully sets the DNS resolution time for a record.

#### network-analyzer
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/simulator/network-analyzer.js`
- **Type:** JavaScript
- **Size:** 20.4 KB
- **Capabilities:**
  - For certain resource types, server response time takes up a greater percentage of TTFB (dynamic
  - Estimates the observed RTT to each origin based on how long the connection setup.
  - Estimates the observed RTT to each origin based on how long a download took on a fresh connection.
- **Methods:** 1

#### page-dependency-graph
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/page-dependency-graph.js`
- **Type:** JavaScript
- **Size:** 17.6 KB
- **Capabilities:**
  - If the node has an associated frameId, then create a dependency on the root document request
  - Removes the given node from the graph, but retains all paths between its dependencies and
- **Methods:** 4

#### cpu-node
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern/cpu-node.js`
- **Type:** JavaScript
- **Size:** 1.68 KB
- **Capabilities:**
  - Returns true if this node contains a Layout task.
  - Returns the script URLs that had their EvaluateScript events occur in this task.

#### minification-estimator
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/minification-estimator.js`
- **Type:** JavaScript
- **Size:** 6.7 KB
- **Capabilities:**
  - Look backwards from `startPosition` in `content` for an ECMAScript punctuator.
  - Acts as stack for brace tracking.
- **Methods:** 4

#### network-request
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/network-request.js`
- **Type:** JavaScript
- **Size:** 24.13 KB
- **Capabilities:**
  - When the network service is about to handle a request, ie. just before going to the
  - Resolve differences between conflicting timing signals. Based on the property setters in DevTools.
  - Update responseHeadersEndTime to the networkEndTime if networkEndTime is earlier.

#### arbitrary-equality-map
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/arbitrary-equality-map.js`
- **Type:** JavaScript
- **Size:** 1.7 KB
- **Capabilities:**
  - Determines whether two objects are deeply equal. Defers to lodash isEqual, but is kept here for

#### bf-cache-strings
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/bf-cache-strings.js`
- **Type:** JavaScript
- **Size:** 33.28 KB
- **Capabilities:**
  - Description text for not restored reason NotMainFrame.

#### lighthouse-compatibility
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lighthouse-compatibility.js`
- **Type:** JavaScript
- **Size:** 5.81 KB
- **Capabilities:**
  - Upgrades an lhr object in-place to account for changes in the data structure over major versions.
- **Methods:** 1

#### rect-helpers
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/rect-helpers.js`
- **Type:** JavaScript
- **Size:** 5.93 KB
- **Capabilities:**
  - Returns whether rect2 is contained entirely within rect1;
  - Returns a bounding rect for all the passed in rects, with padded with half of
- **Methods:** 17

#### asset-saver
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/asset-saver.js`
- **Type:** JavaScript
- **Size:** 15.62 KB
- **Capabilities:**
  - Load artifacts object from files located within basePath
  - A replacer function for JSON.stingify of the artifacts. Used to serialize objects that
  - Saves flow artifacts with the following file structure:
- **Methods:** 16

#### Platform
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/cdt/Platform.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Methods:** 2

#### SDK
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/cdt/SDK.js`
- **Type:** JavaScript
- **Size:** 806 B

#### ParsedURL
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/cdt/generated/ParsedURL.js`
- **Type:** JavaScript
- **Size:** 6.05 KB
- **Capabilities:**
  - http://tools.ietf.org/html/rfc3986#section-5.2.4
- **Methods:** 1

#### SourceMap
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/cdt/generated/SourceMap.js`
- **Type:** JavaScript
- **Size:** 21.05 KB
- **Capabilities:**
  - Implements Source Map V3 model. See https://github.com/google/closure-compiler/wiki/Source-Maps
  - Returns a list of ranges in the generated script for original sources that
- **Methods:** 3

#### lh-trace-processor
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lh-trace-processor.js`
- **Type:** JavaScript
- **Size:** 1.24 KB
- **Capabilities:**
  - This isn't currently used, but will be when the time origin of trace processing is changed.

#### timing-trace-saver
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/timing-trace-saver.js`
- **Type:** JavaScript
- **Size:** 2.74 KB
- **Capabilities:**
  - Generates a chromium trace file from user timing measures
  - Writes a trace file to disk
- **Methods:** 2

#### proto-preprocessor
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/proto-preprocessor.js`
- **Type:** JavaScript
- **Size:** 4.27 KB
- **Capabilities:**
  - Transform an LHR into a proto-friendly, mostly-compatible LHR.
  - Execute `cb(obj, key)` on every object property where obj[key] is a string, recursively.
  - Iterate Strings
- **Methods:** 2

#### csp-evaluator
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/csp-evaluator.js`
- **Type:** JavaScript
- **Size:** 10.96 KB
- **Capabilities:**
  - Message shown when a CSP is missing a semicolon. Shown in a table with a list of other CSP vulnerabilities and suggestions. "CSP" stands for "Content Security Policy".
- **Methods:** 3

#### emulation
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/emulation.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Capabilities:**
  - Sets the throttling options specified in config settings, clearing existing network throttling if
- **Methods:** 8

#### trace-processor
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tracehouse/trace-processor.js`
- **Type:** JavaScript
- **Size:** 40.65 KB
- **Capabilities:**
  - Returns true if the event is a navigation start event of a document whose URL seems valid.
  - This method sorts a group of trace events that have the same timestamp. We want to...
  - Sorts and filters trace events by timestamp and respecting the nesting structure inherent to
- **Methods:** 4

#### task-groups
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tracehouse/task-groups.js`
- **Type:** JavaScript
- **Size:** 3.09 KB
- **Capabilities:**
  - Make sure the traceEventNames keep up with the ones in DevTools

#### task-summary
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tracehouse/task-summary.js`
- **Type:** JavaScript
- **Size:** 2.44 KB
- **Methods:** 3

#### main-thread-tasks
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tracehouse/main-thread-tasks.js`
- **Type:** JavaScript
- **Size:** 27.09 KB
- **Capabilities:**
  - This function takes the start and end events from a thread and creates tasks from them.
  - This function iterates through the tasks to set the `.parent`/`.children` properties of tasks
  - This function takes the raw trace events sorted in increasing timestamp order and outputs connected task nodes.
- **Methods:** 3

#### cpu-profile-model
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tracehouse/cpu-profile-model.js`
- **Type:** JavaScript
- **Size:** 22.38 KB
- **Capabilities:**
  - Initialization function to enable O(1) access to nodes by node ID.
  - Initialization function to enable O(1) access to the set of active nodes in the stack by node ID.
  - Returns all the node IDs in a stack when a specific nodeId is at the top of the stack
- **Methods:** 7

#### deprecations-strings
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/deprecations-strings.js`
- **Type:** JavaScript
- **Size:** 30.57 KB
- **Capabilities:**
  - We show this warning when 1) an 'authorization' header is attached to the request by scripts, 2) there is no 'authorization' in the 'access-control-allow-headers' header in the response, and 3) there is a wildcard symbol ('*') in the 'access-control-allow-header' header in the response. This is allowed now, but we're planning to reject such responses and require responses to have an 'access-control-allow-headers' containing 'authorization'.

#### script-helpers
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/script-helpers.js`
- **Type:** JavaScript
- **Size:** 6.86 KB
- **Capabilities:**
  - Estimates the number of bytes this network record would have consumed on the network based on the
  - Estimates the number of bytes the content of this network record would have consumed on the network based on the
  - Utility function to estimate the ratio of the compression on the resource.
- **Methods:** 6

#### trace-engine
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/trace-engine.js`
- **Type:** JavaScript
- **Size:** 625 B

#### lantern-trace-saver
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/lantern-trace-saver.js`
- **Type:** JavaScript
- **Size:** 7.85 KB
- **Methods:** 5

#### tappable-rects
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/tappable-rects.js`
- **Type:** JavaScript
- **Size:** 3.32 KB
- **Capabilities:**
  - Merge client rects together and remove small ones. This may result in a larger overall
  - Sometimes a child will reach out of the parent by a few px, but still
  - Merge touching rects based on what appears as one tappable area to the user.
- **Methods:** 3

#### sentry
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/sentry.js`
- **Type:** JavaScript
- **Size:** 5.07 KB
- **Capabilities:**
  - A delegate for sentry so that environments without error reporting enabled will use
  - When called, replaces noops with actual Sentry implementation.
- **Methods:** 3

#### manifest-parser
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/manifest-parser.js`
- **Type:** JavaScript
- **Size:** 11.29 KB
- **Capabilities:**
  - All display-mode fallbacks, including when unset, lead to default display mode 'browser'.
  - Returns whether the urls are of the same origin. See https://html.spec.whatwg.org/#same-origin
  - https://www.w3.org/TR/2016/WD-appmanifest-20160825/#start_url-member
- **Methods:** 16

#### metric-trace-events
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/traces/metric-trace-events.js`
- **Type:** JavaScript
- **Size:** 5.86 KB
- **Capabilities:**
  - Returns simplified representation of all metrics
  - Returns simplified representation of all metrics' timestamps from monotonic clock
  - Get the trace event data for our timeOrigin
- **Methods:** 1

#### median-run
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/median-run.js`
- **Type:** JavaScript
- **Size:** 2.88 KB
- **Capabilities:**
  - We want the run that's closest to the median of the FCP and the median of the TTI.
- **Methods:** 4

#### i18n
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/i18n/i18n.js`
- **Type:** JavaScript
- **Size:** 15.46 KB
- **Capabilities:**
  - Look up the best available locale for the requested language through these fall backs:
  - Returns a function that generates `LH.IcuMessage` objects to localize the
  - Combined so fn can access both caller's strings and i18n.UIStrings shared across LH.
- **Methods:** 4

#### minify-devtoolslog
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/minify-devtoolslog.js`
- **Type:** JavaScript
- **Size:** 1.96 KB
- **Methods:** 4

#### third-party-web
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/third-party-web.js`
- **Type:** JavaScript
- **Size:** 1.09 KB
- **Methods:** 4

#### navigation-error
- **File:** `frontend-tools/node_modules/lighthouse/core/lib/navigation-error.js`
- **Type:** JavaScript
- **Size:** 7.75 KB
- **Capabilities:**
  - Warning shown in report when the page under test returns an error code, which Lighthouse is not able to reliably load so we display a warning.
  - Warning shown in report when the page under test is an XHTML document, which Lighthouse does not directly support
  - Returns an error if the original network request failed or wasn't found.
- **Methods:** 4

#### js-yaml
- **File:** `frontend-tools/node_modules/js-yaml/dist/js-yaml.js`
- **Type:** JavaScript
- **Size:** 111.68 KB
- **Capabilities:**
  - Test Document Separator
  - Test Implicit Resolving
  - Test Ambiguity
- **Methods:** 20

#### js-yaml.min
- **File:** `frontend-tools/node_modules/js-yaml/dist/js-yaml.min.js`
- **Type:** JavaScript
- **Size:** 38.51 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/js-yaml/index.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Methods:** 2

#### js-yaml
- **File:** `frontend-tools/node_modules/js-yaml/bin/js-yaml.js`
- **Type:** JavaScript
- **Size:** 2.67 KB
- **Methods:** 1

#### int
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/int.js`
- **Type:** JavaScript
- **Size:** 3.6 KB
- **Methods:** 6

#### timestamp
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/timestamp.js`
- **Type:** JavaScript
- **Size:** 2.51 KB
- **Methods:** 3

#### pairs
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/pairs.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Methods:** 2

#### float
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/float.js`
- **Type:** JavaScript
- **Size:** 2.41 KB
- **Methods:** 4

#### binary
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/binary.js`
- **Type:** JavaScript
- **Size:** 2.84 KB
- **Methods:** 4

#### null
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/null.js`
- **Type:** JavaScript
- **Size:** 808 B
- **Methods:** 3

#### omap
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/omap.js`
- **Type:** JavaScript
- **Size:** 1023 B
- **Methods:** 2

#### seq
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/seq.js`
- **Type:** JavaScript
- **Size:** 191 B

#### bool
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/bool.js`
- **Type:** JavaScript
- **Size:** 971 B
- **Methods:** 3

#### set
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/set.js`
- **Type:** JavaScript
- **Size:** 547 B
- **Methods:** 2

#### merge
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/merge.js`
- **Type:** JavaScript
- **Size:** 230 B
- **Methods:** 1

#### str
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/str.js`
- **Type:** JavaScript
- **Size:** 189 B

#### map
- **File:** `frontend-tools/node_modules/js-yaml/lib/type/map.js`
- **Type:** JavaScript
- **Size:** 190 B

#### default
- **File:** `frontend-tools/node_modules/js-yaml/lib/schema/default.js`
- **Type:** JavaScript
- **Size:** 538 B

#### loader
- **File:** `frontend-tools/node_modules/js-yaml/lib/loader.js`
- **Type:** JavaScript
- **Size:** 46.04 KB
- **Capabilities:**
  - Test Document Separator
- **Methods:** 20

#### dumper
- **File:** `frontend-tools/node_modules/js-yaml/lib/dumper.js`
- **Type:** JavaScript
- **Size:** 31.15 KB
- **Capabilities:**
  - Test Implicit Resolving
  - Test Ambiguity
- **Methods:** 20

#### exception
- **File:** `frontend-tools/node_modules/js-yaml/lib/exception.js`
- **Type:** JavaScript
- **Size:** 1.27 KB
- **Methods:** 3

#### common
- **File:** `frontend-tools/node_modules/js-yaml/lib/common.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Methods:** 6

#### type
- **File:** `frontend-tools/node_modules/js-yaml/lib/type.js`
- **Type:** JavaScript
- **Size:** 1.81 KB
- **Methods:** 2

#### snippet
- **File:** `frontend-tools/node_modules/js-yaml/lib/snippet.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Methods:** 3

#### schema
- **File:** `frontend-tools/node_modules/js-yaml/lib/schema.js`
- **Type:** JavaScript
- **Size:** 3.3 KB
- **Methods:** 5

#### wrappy
- **File:** `frontend-tools/node_modules/wrappy/wrappy.js`
- **Type:** JavaScript
- **Size:** 905 B
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/require-directory/index.js`
- **Type:** JavaScript
- **Size:** 2.8 KB
- **Capabilities:**
  - Check File Inclusion
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/has-symbols/index.js`
- **Type:** JavaScript
- **Size:** 447 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/has-symbols/test/index.js`
- **Type:** JavaScript
- **Size:** 654 B

#### core-js
- **File:** `frontend-tools/node_modules/has-symbols/test/shams/core-js.js`
- **Type:** JavaScript
- **Size:** 797 B

#### get-own-property-symbols
- **File:** `frontend-tools/node_modules/has-symbols/test/shams/get-own-property-symbols.js`
- **Type:** JavaScript
- **Size:** 760 B

#### shams
- **File:** `frontend-tools/node_modules/has-symbols/shams.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Methods:** 1

#### yerror
- **File:** `frontend-tools/node_modules/yargs/build/lib/yerror.js`
- **Type:** JavaScript
- **Size:** 234 B

#### validation
- **File:** `frontend-tools/node_modules/yargs/build/lib/validation.js`
- **Type:** JavaScript
- **Size:** 12.36 KB
- **Methods:** 18

#### completion
- **File:** `frontend-tools/node_modules/yargs/build/lib/completion.js`
- **Type:** JavaScript
- **Size:** 10.25 KB
- **Methods:** 3

#### middleware
- **File:** `frontend-tools/node_modules/yargs/build/lib/middleware.js`
- **Type:** JavaScript
- **Size:** 3.15 KB
- **Methods:** 2

#### obj-filter
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/obj-filter.js`
- **Type:** JavaScript
- **Size:** 299 B
- **Methods:** 1

#### levenshtein
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/levenshtein.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Methods:** 1

#### is-promise
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/is-promise.js`
- **Type:** JavaScript
- **Size:** 155 B
- **Methods:** 1

#### maybe-async-result
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/maybe-async-result.js`
- **Type:** JavaScript
- **Size:** 496 B
- **Methods:** 2

#### process-argv
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/process-argv.js`
- **Type:** JavaScript
- **Size:** 436 B
- **Methods:** 5

#### apply-extends
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/apply-extends.js`
- **Type:** JavaScript
- **Size:** 2 KB
- **Capabilities:**
  - Check For Circular Extends
- **Methods:** 5

#### which-module
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/which-module.js`
- **Type:** JavaScript
- **Size:** 321 B
- **Methods:** 1

#### set-blocking
- **File:** `frontend-tools/node_modules/yargs/build/lib/utils/set-blocking.js`
- **Type:** JavaScript
- **Size:** 386 B
- **Methods:** 1

#### usage
- **File:** `frontend-tools/node_modules/yargs/build/lib/usage.js`
- **Type:** JavaScript
- **Size:** 20.9 KB
- **Methods:** 20

#### command
- **File:** `frontend-tools/node_modules/yargs/build/lib/command.js`
- **Type:** JavaScript
- **Size:** 18.91 KB
- **Methods:** 6

#### common-types
- **File:** `frontend-tools/node_modules/yargs/build/lib/typings/common-types.js`
- **Type:** JavaScript
- **Size:** 308 B
- **Methods:** 3

#### parse-command
- **File:** `frontend-tools/node_modules/yargs/build/lib/parse-command.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Methods:** 1

#### yargs-factory
- **File:** `frontend-tools/node_modules/yargs/build/lib/yargs-factory.js`
- **Type:** JavaScript
- **Size:** 75.82 KB
- **Methods:** 3

#### argsert
- **File:** `frontend-tools/node_modules/yargs/build/lib/argsert.js`
- **Type:** JavaScript
- **Size:** 2.42 KB
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/proxy-agent/node_modules/https-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.28 KB
- **Capabilities:**
  - The `HttpsProxyAgent` implements an HTTP Agent subclass that connects to
  - Called when the node-core HTTP client library is creating a
- **Methods:** 2

#### parse-proxy-response
- **File:** `frontend-tools/node_modules/proxy-agent/node_modules/https-proxy-agent/dist/parse-proxy-response.js`
- **Type:** JavaScript
- **Size:** 3.82 KB
- **Methods:** 6

#### helpers
- **File:** `frontend-tools/node_modules/proxy-agent/node_modules/agent-base/dist/helpers.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/proxy-agent/node_modules/agent-base/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Determine whether this is an `http` or `https` request.

#### index
- **File:** `frontend-tools/node_modules/proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 5.6 KB
- **Capabilities:**
  - Supported proxy types.
  - Uses the appropriate `Agent` subclass based off of the "proxy"
  - Cache for `Agent` instances.
- **Methods:** 1

#### chrome-finder
- **File:** `frontend-tools/node_modules/chrome-launcher/dist/chrome-finder.js`
- **Type:** JavaScript
- **Size:** 18.62 KB
- **Capabilities:**
  - check for MacOS default app paths first to avoid waiting for the slow lsregister command
  - Look for linux executables in 3 ways
- **Methods:** 10

#### random-port
- **File:** `frontend-tools/node_modules/chrome-launcher/dist/random-port.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - Return a random, unused port.
- **Methods:** 1

#### flags
- **File:** `frontend-tools/node_modules/chrome-launcher/dist/flags.js`
- **Type:** JavaScript
- **Size:** 6.16 KB
- **Capabilities:**
  - See the following `chrome-flags-for-tools.md` for exhaustive coverage of these and related flags

#### chrome-launcher
- **File:** `frontend-tools/node_modules/chrome-launcher/dist/chrome-launcher.js`
- **Type:** JavaScript
- **Size:** 31.5 KB
- **Methods:** 4

#### utils
- **File:** `frontend-tools/node_modules/chrome-launcher/dist/utils.js`
- **Type:** JavaScript
- **Size:** 9.09 KB
- **Methods:** 11

#### compiled-check
- **File:** `frontend-tools/node_modules/chrome-launcher/compiled-check.js`
- **Type:** JavaScript
- **Size:** 339 B

#### helpers
- **File:** `frontend-tools/node_modules/socks/build/common/helpers.js`
- **Type:** JavaScript
- **Size:** 6.77 KB
- **Capabilities:**
  - Validates the provided SocksClientOptions
  - Validates the SocksClientChainOptions
  - Validates a SocksRemoteHost
- **Methods:** 9

#### util
- **File:** `frontend-tools/node_modules/socks/build/common/util.js`
- **Type:** JavaScript
- **Size:** 698 B
- **Capabilities:**
  - Error wrapper for SocksClient
  - Shuffles a given array.
- **Methods:** 1

#### receivebuffer
- **File:** `frontend-tools/node_modules/socks/build/common/receivebuffer.js`
- **Type:** JavaScript
- **Size:** 1.51 KB

#### constants
- **File:** `frontend-tools/node_modules/socks/build/common/constants.js`
- **Type:** JavaScript
- **Size:** 7.37 KB

#### socksclient
- **File:** `frontend-tools/node_modules/socks/build/client/socksclient.js`
- **Type:** JavaScript
- **Size:** 34.79 KB
- **Capabilities:**
  - Creates a new SOCKS connection.
  - Creates a new SOCKS connection chain to a destination host through 2 or more SOCKS proxies.
  - Creates a SOCKS UDP Frame.
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/socks/build/index.js`
- **Type:** JavaScript
- **Size:** 846 B

#### index
- **File:** `frontend-tools/node_modules/js-tokens/index.js`
- **Type:** JavaScript
- **Size:** 1.41 KB

#### common
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/debug/src/common.js`
- **Type:** JavaScript
- **Size:** 6.14 KB
- **Capabilities:**
  - This is the common logic for both the Node.js and web browser
  - The currently active debug mode names, and names to skip.
  - Map of special "%n" handling functions, for the debug "format" argument.
- **Methods:** 11

#### browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/debug/src/browser.js`
- **Type:** JavaScript
- **Size:** 5.87 KB
- **Capabilities:**
  - This is the web browser implementation of `debug()`.
  - Currently only WebKit-based Web Inspectors, Firefox >= v31,
  - Colorize log arguments if enabled.
- **Methods:** 5

#### node
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/debug/src/node.js`
- **Type:** JavaScript
- **Size:** 4.58 KB
- **Capabilities:**
  - Module dependencies.
  - This is the Node.js implementation of `debug()`.
  - Build up the default `inspectOpts` object from the environment variables.
- **Methods:** 7

#### detectPlatform
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/CLI.js`
- **Type:** JavaScript
- **Size:** 11.41 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 2

#### install
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/install.js`
- **Type:** JavaScript
- **Size:** 4.66 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 7

#### types
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 2 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 2.07 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 1.97 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 6.44 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### chrome
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 5.46 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 9

#### firefox
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 12.12 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 8

#### chromium
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 2.55 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 6

#### fileUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/fileUtil.js`
- **Type:** JavaScript
- **Size:** 2.65 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/httpUtil.js`
- **Type:** JavaScript
- **Size:** 4.31 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/launch.js`
- **Type:** JavaScript
- **Size:** 11.9 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 9

#### Cache
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/esm/Cache.js`
- **Type:** JavaScript
- **Size:** 4.39 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### detectPlatform
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/detectPlatform.js`
- **Type:** JavaScript
- **Size:** 2.3 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Windows 11 is identified by the version 10.0.22000 or greater
- **Methods:** 2

#### CLI
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/CLI.js`
- **Type:** JavaScript
- **Size:** 12.96 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 2

#### debug
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/debug.js`
- **Type:** JavaScript
- **Size:** 974 B
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### main
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/main.js`
- **Type:** JavaScript
- **Size:** 4.27 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### install
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/install.js`
- **Type:** JavaScript
- **Size:** 5.52 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Returns metadata about browsers installed in the cache directory.
- **Methods:** 7

#### types
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/types.js`
- **Type:** JavaScript
- **Size:** 2.24 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Supported browsers.
  - Platform names used to identify a OS platform x architecture combination in the way

#### chrome-headless-shell
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome-headless-shell.js`
- **Type:** JavaScript
- **Size:** 2.82 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### chromedriver
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromedriver.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### browser-data
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/browser-data.js`
- **Type:** JavaScript
- **Size:** 8.92 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### chrome
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chrome.js`
- **Type:** JavaScript
- **Size:** 6.77 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 9

#### firefox
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/firefox.js`
- **Type:** JavaScript
- **Size:** 12.95 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Populates the user.js file with custom preferences as needed to allow
- **Methods:** 8

#### chromium
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/browser-data/chromium.js`
- **Type:** JavaScript
- **Size:** 3.28 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 6

#### fileUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/fileUtil.js`
- **Type:** JavaScript
- **Size:** 4.11 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### httpUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/httpUtil.js`
- **Type:** JavaScript
- **Size:** 5.68 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 6

#### launch
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/launch.js`
- **Type:** JavaScript
- **Size:** 12.81 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 9

#### Cache
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/@puppeteer/browsers/lib/cjs/Cache.js`
- **Type:** JavaScript
- **Size:** 4.93 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Path to the root of the installation folder. Use
  - The cache used by Puppeteer relies on the following structure:
- **Methods:** 1

#### urlpattern
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/urlpattern-polyfill/dist/urlpattern.js`
- **Type:** JavaScript
- **Size:** 15.75 KB
- **Methods:** 20

#### validation
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/validation.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Capabilities:**
  - Checks if a status code is allowed in a close frame.
  - Checks if a given buffer contains only correct UTF-8.
- **Methods:** 2

#### permessage-deflate
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/permessage-deflate.js`
- **Type:** JavaScript
- **Size:** 13.78 KB
- **Capabilities:**
  - permessage-deflate implementation.
  - Creates a PerMessageDeflate instance.
  - Create an extension negotiation offer.
- **Methods:** 3

#### subprotocol
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/subprotocol.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Parses the `Sec-WebSocket-Protocol` header into a set of subprotocol names.
- **Methods:** 1

#### limiter
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/limiter.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - A very simple job queue with adjustable concurrency. Adapted from
  - Creates a new `Limiter`.
  - Adds a job to the queue.

#### websocket-server
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/websocket-server.js`
- **Type:** JavaScript
- **Size:** 15.32 KB
- **Capabilities:**
  - Class representing a WebSocket server.
  - Create a `WebSocketServer` instance.
  - Returns the bound address, the address family name, and port of the server
- **Methods:** 7

#### event-target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/event-target.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Class representing an event.
  - Create a new `Event`.
  - Class representing a close event.
- **Methods:** 5

#### extension
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/extension.js`
- **Type:** JavaScript
- **Size:** 6.04 KB
- **Capabilities:**
  - Adds an offer to the map of extension offers or a parameter to the map of
  - Parses the `Sec-WebSocket-Extensions` header into an object.
  - Builds the `Sec-WebSocket-Extensions` header field value.
- **Methods:** 3

#### sender
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/sender.js`
- **Type:** JavaScript
- **Size:** 12.33 KB
- **Capabilities:**
  - HyBi Sender implementation.
  - Creates a Sender instance.
  - Frames a piece of data according to the HyBi WebSocket protocol.
- **Methods:** 1

#### websocket
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/websocket.js`
- **Type:** JavaScript
- **Size:** 33.44 KB
- **Capabilities:**
  - Class representing a WebSocket.
  - Create a new `WebSocket`.
  - This deviates from the WHATWG interface since ws doesn't support the
- **Methods:** 19

#### stream
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/stream.js`
- **Type:** JavaScript
- **Size:** 3.99 KB
- **Capabilities:**
  - Emits the `'close'` event on a stream.
  - The listener of the `'end'` event.
  - The listener of the `'error'` event.
- **Methods:** 9

#### buffer-util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/buffer-util.js`
- **Type:** JavaScript
- **Size:** 2.98 KB
- **Capabilities:**
  - Merges an array of buffers into a new buffer.
  - Masks a buffer using the given mask.
  - Unmasks a buffer using the given mask.
- **Methods:** 5

#### receiver
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/lib/receiver.js`
- **Type:** JavaScript
- **Size:** 15.36 KB
- **Capabilities:**
  - HyBi Receiver implementation.
  - Creates a Receiver instance.
  - Implements `Writable.prototype._write()`.
- **Methods:** 4

#### browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ws/browser.js`
- **Type:** JavaScript
- **Size:** 176 B

#### Deferred
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/Deferred.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### unitConversions
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/unitConversions.js`
- **Type:** JavaScript
- **Size:** 935 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### Buffer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/Buffer.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### assert
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/assert.js`
- **Type:** JavaScript
- **Size:** 931 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### EventEmitter
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 2.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Like `on` but the listener will only be fired once and then it will be removed.
  - Emits an event and call any associated listeners.
- **Methods:** 1

#### log
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/log.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### uuid
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/uuid.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Generates a random v4 UUID, as specified in RFC4122.
- **Methods:** 1

#### ProcessingQueue
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/ProcessingQueue.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### DefaultMap
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/DefaultMap.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - A subclass of Map whose functionality is almost the same as its parent

#### IdWrapper
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/IdWrapper.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Creates an object with a positive unique incrementing id.

#### WebsocketTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/WebsocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### UrlPattern
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/UrlPattern.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### Mutex
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/utils/Mutex.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Use Mutex class to coordinate local concurrent operations.
- **Methods:** 1

#### mapperTabPage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiTab/mapperTabPage.js`
- **Type:** JavaScript
- **Size:** 4.22 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - The following piece of HTML should be added to the `debug` element:
- **Methods:** 4

#### BidiParser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiTab/BidiParser.js`
- **Type:** JavaScript
- **Size:** 4.82 KB

#### bidiTab
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiTab/bidiTab.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - A CdpTransport implementation that uses the window.cdp bindings
  - Launches the BiDi mapper instance.
- **Methods:** 2

#### Transport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiTab/Transport.js`
- **Type:** JavaScript
- **Size:** 4.82 KB

#### BidiNoOpParser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiNoOpParser.js`
- **Type:** JavaScript
- **Size:** 3.16 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### OutgoingMessage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/OutgoingMessage.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### BrowsingContextProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextProcessor.js`
- **Type:** JavaScript
- **Size:** 10.89 KB
- **Capabilities:**
  - This method is called for each CDP session, since this class is responsible

#### BrowsingContextImpl
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextImpl.js`
- **Type:** JavaScript
- **Size:** 32.04 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - The ID of the parent browsing context.
  - Returns true if this is a top-level context.
- **Methods:** 5

#### BrowsingContextStorage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextStorage.js`
- **Type:** JavaScript
- **Size:** 2.65 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### CdpTarget
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/CdpTarget.js`
- **Type:** JavaScript
- **Size:** 5.36 KB
- **Capabilities:**
  - Enables all the required CDP domains and unblocks the target.
  - All the ProxyChannels from all the preload scripts of the given

#### logHelper
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/log/logHelper.js`
- **Type:** JavaScript
- **Size:** 5.67 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 5

#### LogManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/log/LogManager.js`
- **Type:** JavaScript
- **Size:** 5.33 KB
- **Capabilities:**
  - Try the best to get the exception text.
- **Methods:** 2

#### RealmStorage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/RealmStorage.js`
- **Type:** JavaScript
- **Size:** 2.92 KB

#### PreloadScript
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/PreloadScript.js`
- **Type:** JavaScript
- **Size:** 4.6 KB
- **Capabilities:**
  - BiDi IDs are generated by the server and are unique within contexts.
  - String to be evaluated. Wraps user-provided function so that the following
  - Adds the script to the given CDP targets by calling the
- **Methods:** 2

#### ScriptProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/ScriptProcessor.js`
- **Type:** JavaScript
- **Size:** 4.81 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### PreloadScriptStorage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/PreloadScriptStorage.js`
- **Type:** JavaScript
- **Size:** 1.49 KB
- **Capabilities:**
  - Container class for preload scripts.

#### ChannelProxy
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/ChannelProxy.js`
- **Type:** JavaScript
- **Size:** 9.95 KB
- **Capabilities:**
  - Used to send messages from realm to BiDi user.
  - Creates a channel proxy in the given realm, initialises listener and
  - Evaluation string which creates a ChannelProxy object on the client side.

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/Realm.js`
- **Type:** JavaScript
- **Size:** 23.83 KB
- **Capabilities:**
  - Relies on the CDP to implement proper BiDi serialization, except:
  - Serializes a given CDP object into BiDi, keeping references in the
  - Gets the string representation of an object. This is equivalent to
- **Methods:** 1

#### BrowserProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/browser/BrowserProcessor.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### SessionProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/session/SessionProcessor.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### CdpProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/cdp/CdpProcessor.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### EventManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/events/EventManager.js`
- **Type:** JavaScript
- **Size:** 7.49 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Maps event name to a desired buffer length.
  - Maps event name to a set of contexts where this event already happened.

#### events
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/events/events.js`
- **Type:** JavaScript
- **Size:** 1.47 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Returns true if the given event is a CDP event.
  - Asserts that the given event is known to BiDi or BiDi+, or throws otherwise.
- **Methods:** 2

#### SubscriptionManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/events/SubscriptionManager.js`
- **Type:** JavaScript
- **Size:** 9.36 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Returns the cartesian product of the given arrays.
  - Unsubscribes atomically from all events in the given contexts and channel.
- **Methods:** 3

#### InputProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputProcessor.js`
- **Type:** JavaScript
- **Size:** 3.64 KB

#### keyUtils
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/keyUtils.js`
- **Type:** JavaScript
- **Size:** 11.4 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 3

#### InputState
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputState.js`
- **Type:** JavaScript
- **Size:** 3.65 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputStateManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputStateManager.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### ActionDispatcher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/ActionDispatcher.js`
- **Type:** JavaScript
- **Size:** 26.23 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 4

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputSource
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputSource.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 4.58 KB
- **Capabilities:**
  - Gets the network request with the given ID, if any.

#### NetworkUtils
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkUtils.js`
- **Type:** JavaScript
- **Size:** 3.47 KB
- **Methods:** 6

#### NetworkStorage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkStorage.js`
- **Type:** JavaScript
- **Size:** 7.93 KB
- **Capabilities:**
  - A map from network request ID to Network Request objects.
  - Adds the given entry to the intercept map.
  - Removes the given intercept from the intercept map.

#### NetworkRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkRequest.js`
- **Type:** JavaScript
- **Size:** 21.84 KB
- **Capabilities:**
  - Each network request has an associated request id, which is a string
  - Indicates the network intercept phase, if the request is currently blocked.

#### NetworkProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkProcessor.js`
- **Type:** JavaScript
- **Size:** 11.01 KB
- **Capabilities:**
  - Either enables or disables the Fetch domain.
  - Returns the blocked request associated with the given network ID.
  - Attempts to parse the given url.

#### BidiMapper
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiMapper.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### BidiServer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiServer.js`
- **Type:** JavaScript
- **Size:** 4 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - Creates and starts BiDi Mapper instance.
  - Sends BiDi message.

#### CommandProcessor
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiMapper/CommandProcessor.js`
- **Type:** JavaScript
- **Size:** 11.15 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### protocol-parser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/protocol-parser/protocol-parser.js`
- **Type:** JavaScript
- **Size:** 9.76 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 20

#### webdriver-bidi
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/protocol-parser/webdriver-bidi.js`
- **Type:** JavaScript
- **Size:** 88.16 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - THIS FILE IS AUTOGENERATED. Run `npm run bidi-types` to regenerate.

#### CdpConnection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/cdp/CdpConnection.js`
- **Type:** JavaScript
- **Size:** 4.8 KB
- **Capabilities:**
  - Represents a high-level CDP connection to the browser backend.
  - Gets a CdpClient instance attached to the given session ID,
  - Creates a new CdpClient instance for the given session ID.

#### CdpClient
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/cdp/CdpClient.js`
- **Type:** JavaScript
- **Size:** 1.56 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### ErrorResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/protocol/ErrorResponse.js`
- **Type:** JavaScript
- **Size:** 5.6 KB

#### chromium-bidi
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/protocol/chromium-bidi.js`
- **Type:** JavaScript
- **Size:** 3.93 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### protocol
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/protocol/protocol.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### SimpleTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/SimpleTransport.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Implements simple transport that allows sending string messages via

#### BrowserInstance
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/BrowserInstance.js`
- **Type:** JavaScript
- **Size:** 5.35 KB
- **Capabilities:**
  - BrowserProcess is responsible for running the browser and BiDi Mapper within

#### index
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/index.js`
- **Type:** JavaScript
- **Size:** 2.49 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### WebSocketServer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/WebSocketServer.js`
- **Type:** JavaScript
- **Size:** 15.8 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### reader
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/reader.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### MapperCdpConnection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/cjs/bidiServer/MapperCdpConnection.js`
- **Type:** JavaScript
- **Size:** 5.53 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### mapperTab
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/chromium-bidi/lib/iife/mapperTab.js`
- **Type:** JavaScript
- **Size:** 247.15 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/ms/index.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Parse or format the given `val`.
  - Parse the given `str` and return milliseconds.
  - Short format for `ms`.
- **Methods:** 4

#### puppeteer-core
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/puppeteer-core.js`
- **Type:** JavaScript
- **Size:** 1.27 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.58 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The type of the dialog.
  - The message displayed in the dialog.

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 745 B
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 39.36 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
- **Methods:** 11

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 61.06 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
- **Methods:** 11

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.06 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 4.53 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 5.04 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.

#### locators
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 23.98 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.72 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.69 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 56.79 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Page provides methods to interact with a single tab or
  - Listen to page events.
- **Methods:** 15

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 10.34 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### ErrorLike
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.59 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 6.84 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.

#### DebuggableDeferred
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/DebuggableDeferred.js`
- **Type:** JavaScript
- **Size:** 575 B
- **Capabilities:**
  - Creates and returns a deferred promise using DEFERRED_PROMISE_DEBUG_TIMEOUT
- **Methods:** 1

#### decorators
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 6.06 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - The decorator only invokes the target if the target has not been invoked with
- **Methods:** 6

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 855 B
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 694 B

#### Function
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 2.47 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 4

#### Mutex
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 943 B

#### ScriptInjector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.09 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.64 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### WaitTask
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 7.1 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 3.16 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.77 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.13 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Puppeteer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 3.27 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 4.33 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - The EventEmitter class that many Puppeteer classes extend.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### SecurityDetails
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 2.25 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.

#### Errors
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 2.78 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 5.4 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.05 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 13.96 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Validate Dialog Type
- **Methods:** 17

#### common
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 1.77 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### FileChooser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 2.85 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 4.01 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.92 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 931 B
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### HandleIterator
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 5.21 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### TaskQueue
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 1011 B
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 17.04 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### QueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 8.91 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 2.03 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 11.07 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 933 B
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 10.91 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 10.34 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when

#### PipeTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 7.07 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 10.4 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 4.51 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 10.31 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Puppeteer's Frame class could be viewed as a BiDi BrowsingContext implementation
- **Methods:** 2

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 3.24 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 8.05 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 2.68 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 9.34 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.89 KB

#### lifecycle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/lifecycle.js`
- **Type:** JavaScript
- **Size:** 2.13 KB
- **Methods:** 4

#### Serializer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 4.91 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 2.7 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.96 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 6.23 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 731 B

#### Deserializer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 3.34 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 4.33 KB
- **Capabilities:**
  - Internal events that the BrowsingContext class emits.
  - Emitted on the top-level context, when a descendant context is created.
  - Emitted on the top-level context, when a descendant context or the

#### bidi
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 2.54 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 18.53 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 5.31 KB
- **Methods:** 1

#### ExposedFunction
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 8.47 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Sandbox
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Sandbox.js`
- **Type:** JavaScript
- **Size:** 2.76 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 4.84 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 26.61 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
- **Methods:** 6

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### injected
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 13.45 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 6

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 4.33 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
  - The CDP session client the WebWorker belongs to.
- **Methods:** 2

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### FrameTree
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 3.14 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 7 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 21.05 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 12.62 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 17.59 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 9.12 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 3.78 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 10.93 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 12.81 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 3.9 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.83 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.36 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.78 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 6.84 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 21.53 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.42 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - We use symbols to prevent external parties listening to these events.

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 13.5 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 9.2 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
- **Methods:** 2

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 7.29 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 15.23 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 6.79 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Device in a request prompt.
  - Device id during a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 1.79 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 11.43 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Evaluates the given function.
- **Methods:** 3

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.67 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 5.83 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 13.44 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 35.92 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 3

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.98 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### PSelectorParser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.56 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### injected
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.66 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 4.06 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### PQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 8.9 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Queries the given node for all nodes matching the given text selector.

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 913 B
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.49 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 3.82 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 26.51 KB
- **Methods:** 20

#### mitt
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/esm/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 323 B
- **Methods:** 1

#### puppeteer-core
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/puppeteer-core.js`
- **Type:** JavaScript
- **Size:** 2.34 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.74 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The type of the dialog.
  - The message displayed in the dialog.

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 852 B
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 39.97 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
- **Methods:** 11

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 2.68 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 62.18 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
- **Methods:** 11

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 5.31 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.

#### locators
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 25.56 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.94 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 58.65 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Page provides methods to interact with a single tab or
  - Listen to page events.
- **Methods:** 15

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 10.52 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 2.1 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### ErrorLike
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.87 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 7.16 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.

#### DebuggableDeferred
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/DebuggableDeferred.js`
- **Type:** JavaScript
- **Size:** 804 B
- **Capabilities:**
  - Creates and returns a deferred promise using DEFERRED_PROMISE_DEBUG_TIMEOUT
- **Methods:** 1

#### decorators
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 6.47 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - The decorator only invokes the target if the target has not been invoked with
- **Methods:** 6

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 1.68 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 847 B

#### Function
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 2.76 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 4

#### Mutex
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 1.07 KB

#### fetch
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/fetch.js`
- **Type:** JavaScript
- **Size:** 2.02 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - Gets the global version if we're in the browser, else loads the node-fetch module.

#### ScriptInjector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.25 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.78 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### WaitTask
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 7.39 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 4.51 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 5.16 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Puppeteer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 3.46 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 4.73 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - The EventEmitter class that many Puppeteer classes extend.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### SecurityDetails
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 2.4 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.

#### Errors
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 3.17 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 6.2 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.18 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 16.87 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Validate Dialog Type
- **Methods:** 17

#### common
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 3.09 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### FileChooser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 4.38 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.28 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### HandleIterator
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 5.39 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### TaskQueue
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 17.15 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### QueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 9.29 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 2.36 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 11.6 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 12.23 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 10.59 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when

#### PipeTransport
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.31 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 7.62 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 10.92 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### node
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/node/node.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 1.34 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 4.94 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 12.01 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Puppeteer's Frame class could be viewed as a BiDi BrowsingContext implementation
- **Methods:** 2

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 3.43 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 8.28 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 4

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 2.96 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 9.63 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 2.06 KB

#### lifecycle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/lifecycle.js`
- **Type:** JavaScript
- **Size:** 2.62 KB
- **Methods:** 4

#### Serializer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 5.13 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 3.12 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 5.25 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 6.53 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 881 B

#### Deserializer
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 4.7 KB
- **Capabilities:**
  - Internal events that the BrowsingContext class emits.
  - Emitted on the top-level context, when a descendant context is created.
  - Emitted on the top-level context, when a descendant context or the

#### bidi
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 2.16 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 2.82 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 18.91 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 6.67 KB
- **Methods:** 1

#### ExposedFunction
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 9.97 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### Sandbox
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Sandbox.js`
- **Type:** JavaScript
- **Size:** 2.97 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 6.06 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 28.87 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
- **Methods:** 6

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.64 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### injected
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 13.54 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 6

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 4.62 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
  - The CDP session client the WebWorker belongs to.
- **Methods:** 2

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 1.22 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### FrameTree
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 3.28 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 7.27 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 21.62 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 13.17 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 18.44 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 10.49 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 4.02 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 11.42 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 13.17 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 4.13 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.97 KB
- **Capabilities:**
  - Copyright 2018 Google Inc. All rights reserved.
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.7 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.95 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.98 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 7.24 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 21.98 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.55 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - We use symbols to prevent external parties listening to these events.

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 13.89 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 9.5 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
- **Methods:** 2

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 7.88 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 15.72 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 7.29 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Device in a request prompt.
  - Device id during a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 3.13 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 3.54 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 11.88 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Evaluates the given function.
- **Methods:** 3

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.82 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 6.03 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 13.89 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 37.36 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 3

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.94 KB
- **Capabilities:**
  - Copyright 2019 Google Inc. All rights reserved.
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### PSelectorParser
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.8 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### injected
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 3.05 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 4.45 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### PQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 9.43 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
  - Queries the given node for all nodes matching the given text selector.

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.

#### util
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 4.11 KB
- **Capabilities:**
  - Copyright 2022 Google Inc. All rights reserved.
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 146.6 KB
- **Methods:** 20

#### mitt
- **File:** `frontend-tools/node_modules/puppeteer/node_modules/puppeteer-core/lib/cjs/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 1.52 KB
- **Methods:** 1

#### install
- **File:** `frontend-tools/node_modules/puppeteer/lib/esm/puppeteer/node/install.js`
- **Type:** JavaScript
- **Size:** 3.59 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
- **Methods:** 4

#### getConfiguration
- **File:** `frontend-tools/node_modules/puppeteer/lib/esm/puppeteer/getConfiguration.js`
- **Type:** JavaScript
- **Size:** 4.47 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### cli
- **File:** `frontend-tools/node_modules/puppeteer/lib/cjs/puppeteer/node/cli.js`
- **Type:** JavaScript
- **Size:** 1.67 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.

#### install
- **File:** `frontend-tools/node_modules/puppeteer/lib/cjs/puppeteer/node/install.js`
- **Type:** JavaScript
- **Size:** 3.81 KB
- **Capabilities:**
  - Copyright 2020 Google Inc. All rights reserved.
- **Methods:** 4

#### getConfiguration
- **File:** `frontend-tools/node_modules/puppeteer/lib/cjs/puppeteer/getConfiguration.js`
- **Type:** JavaScript
- **Size:** 4.65 KB
- **Capabilities:**
  - Copyright 2023 Google Inc. All rights reserved.
- **Methods:** 1

#### puppeteer
- **File:** `frontend-tools/node_modules/puppeteer/lib/cjs/puppeteer/puppeteer.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - Copyright 2017 Google Inc. All rights reserved.

#### address-error
- **File:** `frontend-tools/node_modules/ip-address/dist/address-error.js`
- **Type:** JavaScript
- **Size:** 372 B

#### ipv6
- **File:** `frontend-tools/node_modules/ip-address/dist/ipv6.js`
- **Type:** JavaScript
- **Size:** 33.27 KB
- **Capabilities:**
  - Represents an IPv6 address
  - Returns true if the given address is in the subnet of the current address
  - Returns true if the address is correct, false otherwise
- **Methods:** 9

#### common
- **File:** `frontend-tools/node_modules/ip-address/dist/common.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Test Bit
- **Methods:** 5

#### helpers
- **File:** `frontend-tools/node_modules/ip-address/dist/v6/helpers.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Capabilities:**
  - Groups an address
- **Methods:** 5

#### regular-expressions
- **File:** `frontend-tools/node_modules/ip-address/dist/v6/regular-expressions.js`
- **Type:** JavaScript
- **Size:** 3.63 KB
- **Methods:** 4

#### ip-address
- **File:** `frontend-tools/node_modules/ip-address/dist/ip-address.js`
- **Type:** JavaScript
- **Size:** 1.73 KB

#### ipv4
- **File:** `frontend-tools/node_modules/ip-address/dist/ipv4.js`
- **Type:** JavaScript
- **Size:** 10 KB
- **Capabilities:**
  - Represents an IPv4 address
  - Returns true if the address is correct, false otherwise
  - Returns true if the given address is in the subnet of the current address
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/es-set-tostringtag/index.js`
- **Type:** JavaScript
- **Size:** 1.18 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/es-set-tostringtag/test/index.js`
- **Type:** JavaScript
- **Size:** 2.18 KB

#### index
- **File:** `frontend-tools/node_modules/cliui/build/lib/index.js`
- **Type:** JavaScript
- **Size:** 9.44 KB
- **Methods:** 6

#### string-utils
- **File:** `frontend-tools/node_modules/cliui/build/lib/string-utils.js`
- **Type:** JavaScript
- **Size:** 1011 B
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/b4a/index.js`
- **Type:** JavaScript
- **Size:** 3.96 KB
- **Methods:** 20

#### utf16le
- **File:** `frontend-tools/node_modules/b4a/lib/utf16le.js`
- **Type:** JavaScript
- **Size:** 641 B
- **Methods:** 3

#### ascii
- **File:** `frontend-tools/node_modules/b4a/lib/ascii.js`
- **Type:** JavaScript
- **Size:** 452 B
- **Methods:** 3

#### utf8
- **File:** `frontend-tools/node_modules/b4a/lib/utf8.js`
- **Type:** JavaScript
- **Size:** 2.6 KB
- **Methods:** 3

#### base64
- **File:** `frontend-tools/node_modules/b4a/lib/base64.js`
- **Type:** JavaScript
- **Size:** 1.44 KB
- **Methods:** 3

#### hex
- **File:** `frontend-tools/node_modules/b4a/lib/hex.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Methods:** 4

#### browser
- **File:** `frontend-tools/node_modules/b4a/browser.js`
- **Type:** JavaScript
- **Size:** 12.97 KB
- **Methods:** 20

#### text
- **File:** `frontend-tools/node_modules/emoji-regex/text.js`
- **Type:** JavaScript
- **Size:** 10.05 KB

#### index
- **File:** `frontend-tools/node_modules/emoji-regex/index.js`
- **Type:** JavaScript
- **Size:** 10.04 KB

#### index
- **File:** `frontend-tools/node_modules/fast-fifo/index.js`
- **Type:** JavaScript
- **Size:** 972 B

#### fixed-size
- **File:** `frontend-tools/node_modules/fast-fifo/fixed-size.js`
- **Type:** JavaScript
- **Size:** 875 B

#### index
- **File:** `frontend-tools/node_modules/mkdirp-classic/index.js`
- **Type:** JavaScript
- **Size:** 2.57 KB
- **Methods:** 2

#### node-progress
- **File:** `frontend-tools/node_modules/progress/lib/node-progress.js`
- **Type:** JavaScript
- **Size:** 6.52 KB
- **Capabilities:**
  - Expose `ProgressBar`.
  - Initialize a `ProgressBar` with the given `fmt` string and `options` or
  - "tick" the progress bar with optional `len` and optional `tokens`.
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/lines-and-columns/build/index.js`
- **Type:** JavaScript
- **Size:** 1.98 KB
- **Methods:** 1

#### benchmark
- **File:** `frontend-tools/node_modules/lru_map/benchmark.js`
- **Type:** JavaScript
- **Size:** 4.52 KB
- **Methods:** 1

#### lru
- **File:** `frontend-tools/node_modules/lru_map/lru.js`
- **Type:** JavaScript
- **Size:** 7.75 KB
- **Capabilities:**
  - A doubly linked list-based Least Recently Used (LRU) cache. Will keep most
- **Methods:** 5

#### index
- **File:** `frontend-tools/node_modules/https-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 579 B
- **Methods:** 1

#### agent
- **File:** `frontend-tools/node_modules/https-proxy-agent/dist/agent.js`
- **Type:** JavaScript
- **Size:** 7.66 KB
- **Capabilities:**
  - The `HttpsProxyAgent` implements an HTTP Agent subclass that connects to
  - Called when the node-core HTTP client library is creating a
- **Methods:** 8

#### parse-proxy-response
- **File:** `frontend-tools/node_modules/https-proxy-agent/dist/parse-proxy-response.js`
- **Type:** JavaScript
- **Size:** 2.4 KB
- **Methods:** 7

#### node-ponyfill
- **File:** `frontend-tools/node_modules/cross-fetch/dist/node-ponyfill.js`
- **Type:** JavaScript
- **Size:** 624 B

#### react-native-polyfill
- **File:** `frontend-tools/node_modules/cross-fetch/dist/react-native-polyfill.js`
- **Type:** JavaScript
- **Size:** 376 B

#### browser-ponyfill
- **File:** `frontend-tools/node_modules/cross-fetch/dist/browser-ponyfill.js`
- **Type:** JavaScript
- **Size:** 18.89 KB
- **Methods:** 20

#### browser-polyfill
- **File:** `frontend-tools/node_modules/cross-fetch/dist/browser-polyfill.js`
- **Type:** JavaScript
- **Size:** 17.74 KB
- **Methods:** 20

#### cross-fetch
- **File:** `frontend-tools/node_modules/cross-fetch/dist/cross-fetch.js`
- **Type:** JavaScript
- **Size:** 9 KB
- **Methods:** 13

#### isInNet
- **File:** `frontend-tools/node_modules/pac-resolver/dist/isInNet.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Capabilities:**
  - True iff the IP address of the host matches the specified IP address pattern.
- **Methods:** 1

#### dateRange
- **File:** `frontend-tools/node_modules/pac-resolver/dist/dateRange.js`
- **Type:** JavaScript
- **Size:** 2.37 KB
- **Capabilities:**
  - If only a single value is specified (from each category: day, month, year), the
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/pac-resolver/dist/index.js`
- **Type:** JavaScript
- **Size:** 3.44 KB
- **Capabilities:**
  - Built-in PAC functions.
  - Returns an asynchronous `FindProxyForURL()` function
- **Methods:** 6

#### localHostOrDomainIs
- **File:** `frontend-tools/node_modules/pac-resolver/dist/localHostOrDomainIs.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - Is true if the hostname matches exactly the specified hostname, or if there is
- **Methods:** 1

#### ip
- **File:** `frontend-tools/node_modules/pac-resolver/dist/ip.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Methods:** 1

#### dnsDomainIs
- **File:** `frontend-tools/node_modules/pac-resolver/dist/dnsDomainIs.js`
- **Type:** JavaScript
- **Size:** 790 B
- **Capabilities:**
  - Returns true iff the domain of hostname matches.
- **Methods:** 1

#### isResolvable
- **File:** `frontend-tools/node_modules/pac-resolver/dist/isResolvable.js`
- **Type:** JavaScript
- **Size:** 561 B
- **Capabilities:**
  - Tries to resolve the hostname. Returns true if succeeds.
- **Methods:** 1

#### dnsDomainLevels
- **File:** `frontend-tools/node_modules/pac-resolver/dist/dnsDomainLevels.js`
- **Type:** JavaScript
- **Size:** 666 B
- **Capabilities:**
  - Returns the number (integer) of DNS domain levels (number of dots) in the
- **Methods:** 1

#### isPlainHostName
- **File:** `frontend-tools/node_modules/pac-resolver/dist/isPlainHostName.js`
- **Type:** JavaScript
- **Size:** 528 B
- **Capabilities:**
  - True iff there is no domain name in the hostname (no dots).
- **Methods:** 1

#### util
- **File:** `frontend-tools/node_modules/pac-resolver/dist/util.js`
- **Type:** JavaScript
- **Size:** 567 B
- **Methods:** 2

#### shExpMatch
- **File:** `frontend-tools/node_modules/pac-resolver/dist/shExpMatch.js`
- **Type:** JavaScript
- **Size:** 1.09 KB
- **Capabilities:**
  - Returns true if the string matches the specified shell
  - Converts a "shell expression" to a JavaScript RegExp.
- **Methods:** 2

#### myIpAddress
- **File:** `frontend-tools/node_modules/pac-resolver/dist/myIpAddress.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Returns the IP address of the host that the Navigator is running on, as
- **Methods:** 1

#### weekdayRange
- **File:** `frontend-tools/node_modules/pac-resolver/dist/weekdayRange.js`
- **Type:** JavaScript
- **Size:** 2.6 KB
- **Capabilities:**
  - Only the first parameter is mandatory. Either the second, the third, or both
- **Methods:** 5

#### dnsResolve
- **File:** `frontend-tools/node_modules/pac-resolver/dist/dnsResolve.js`
- **Type:** JavaScript
- **Size:** 770 B
- **Capabilities:**
  - Resolves the given DNS hostname into an IP address, and returns it in the dot
- **Methods:** 1

#### timeRange
- **File:** `frontend-tools/node_modules/pac-resolver/dist/timeRange.js`
- **Type:** JavaScript
- **Size:** 3.26 KB
- **Capabilities:**
  - True during (or between) the specified time(s).
- **Methods:** 6

#### implementation
- **File:** `frontend-tools/node_modules/function-bind/implementation.js`
- **Type:** JavaScript
- **Size:** 2 KB
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/function-bind/index.js`
- **Type:** JavaScript
- **Size:** 126 B

#### index
- **File:** `frontend-tools/node_modules/function-bind/test/index.js`
- **Type:** JavaScript
- **Size:** 8.78 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/yauzl/index.js`
- **Type:** JavaScript
- **Size:** 32.29 KB
- **Capabilities:**
  - Validate File Name
- **Methods:** 20

#### tslib.es6
- **File:** `frontend-tools/node_modules/ast-types/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/ast-types/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/ast-types/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### main
- **File:** `frontend-tools/node_modules/ast-types/main.js`
- **Type:** JavaScript
- **Size:** 3.01 KB

#### namedTypes
- **File:** `frontend-tools/node_modules/ast-types/gen/namedTypes.js`
- **Type:** JavaScript
- **Size:** 213 B

#### es2020
- **File:** `frontend-tools/node_modules/ast-types/def/es2020.js`
- **Type:** JavaScript
- **Size:** 553 B
- **Methods:** 1

#### core
- **File:** `frontend-tools/node_modules/ast-types/def/core.js`
- **Type:** JavaScript
- **Size:** 11.82 KB
- **Methods:** 1

#### es6
- **File:** `frontend-tools/node_modules/ast-types/def/es6.js`
- **Type:** JavaScript
- **Size:** 9.48 KB
- **Methods:** 3

#### es7
- **File:** `frontend-tools/node_modules/ast-types/def/es7.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Methods:** 1

#### typescript
- **File:** `frontend-tools/node_modules/ast-types/def/typescript.js`
- **Type:** JavaScript
- **Size:** 14.89 KB
- **Methods:** 2

#### flow
- **File:** `frontend-tools/node_modules/ast-types/def/flow.js`
- **Type:** JavaScript
- **Size:** 11 KB
- **Methods:** 1

#### type-annotations
- **File:** `frontend-tools/node_modules/ast-types/def/type-annotations.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - Type annotation defs shared between Flow and TypeScript.
- **Methods:** 1

#### es-proposals
- **File:** `frontend-tools/node_modules/ast-types/def/es-proposals.js`
- **Type:** JavaScript
- **Size:** 1.34 KB
- **Methods:** 1

#### babel
- **File:** `frontend-tools/node_modules/ast-types/def/babel.js`
- **Type:** JavaScript
- **Size:** 397 B
- **Methods:** 1

#### esprima
- **File:** `frontend-tools/node_modules/ast-types/def/esprima.js`
- **Type:** JavaScript
- **Size:** 2.03 KB
- **Methods:** 1

#### babel-core
- **File:** `frontend-tools/node_modules/ast-types/def/babel-core.js`
- **Type:** JavaScript
- **Size:** 9.84 KB
- **Methods:** 2

#### jsx
- **File:** `frontend-tools/node_modules/ast-types/def/jsx.js`
- **Type:** JavaScript
- **Size:** 4.56 KB
- **Methods:** 2

#### fork
- **File:** `frontend-tools/node_modules/ast-types/fork.js`
- **Type:** JavaScript
- **Size:** 1.77 KB
- **Methods:** 3

#### types
- **File:** `frontend-tools/node_modules/ast-types/lib/types.js`
- **Type:** JavaScript
- **Size:** 30.98 KB
- **Capabilities:**
  - Check Field By Name
- **Methods:** 20

#### path
- **File:** `frontend-tools/node_modules/ast-types/lib/path.js`
- **Type:** JavaScript
- **Size:** 11.41 KB
- **Methods:** 20

#### shared
- **File:** `frontend-tools/node_modules/ast-types/lib/shared.js`
- **Type:** JavaScript
- **Size:** 1.76 KB
- **Methods:** 2

#### scope
- **File:** `frontend-tools/node_modules/ast-types/lib/scope.js`
- **Type:** JavaScript
- **Size:** 12.16 KB
- **Capabilities:**
  - Scan Scope
  - Recursive Scan Scope
  - Recursive Scan Child
- **Methods:** 9

#### path-visitor
- **File:** `frontend-tools/node_modules/ast-types/lib/path-visitor.js`
- **Type:** JavaScript
- **Size:** 13.29 KB
- **Methods:** 17

#### node-path
- **File:** `frontend-tools/node_modules/ast-types/lib/node-path.js`
- **Type:** JavaScript
- **Size:** 15.13 KB
- **Capabilities:**
  - Determine whether this.node needs to be wrapped in parentheses in order
  - Pruning certain nodes will result in empty or incomplete nodes, here we clean those nodes up.
- **Methods:** 8

#### equiv
- **File:** `frontend-tools/node_modules/ast-types/lib/equiv.js`
- **Type:** JavaScript
- **Size:** 5.04 KB
- **Methods:** 6

#### isInteger
- **File:** `frontend-tools/node_modules/math-intrinsics/isInteger.js`
- **Type:** JavaScript
- **Size:** 410 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/math-intrinsics/test/index.js`
- **Type:** JavaScript
- **Size:** 6.17 KB

#### mod
- **File:** `frontend-tools/node_modules/math-intrinsics/mod.js`
- **Type:** JavaScript
- **Size:** 218 B
- **Methods:** 1

#### isNaN
- **File:** `frontend-tools/node_modules/math-intrinsics/isNaN.js`
- **Type:** JavaScript
- **Size:** 121 B
- **Methods:** 1

#### isNegativeZero
- **File:** `frontend-tools/node_modules/math-intrinsics/isNegativeZero.js`
- **Type:** JavaScript
- **Size:** 143 B
- **Methods:** 1

#### isFinite
- **File:** `frontend-tools/node_modules/math-intrinsics/isFinite.js`
- **Type:** JavaScript
- **Size:** 262 B
- **Methods:** 1

#### sign
- **File:** `frontend-tools/node_modules/math-intrinsics/sign.js`
- **Type:** JavaScript
- **Size:** 214 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/third-party-web/www-v2/pages/index.js`
- **Type:** JavaScript
- **Size:** 4.87 KB
- **Methods:** 1

#### create-entity-finder-api
- **File:** `frontend-tools/node_modules/third-party-web/lib/create-entity-finder-api.js`
- **Type:** JavaScript
- **Size:** 4.36 KB
- **Methods:** 7

#### index
- **File:** `frontend-tools/node_modules/signal-exit/index.js`
- **Type:** JavaScript
- **Size:** 5.57 KB
- **Methods:** 6

#### signals
- **File:** `frontend-tools/node_modules/signal-exit/signals.js`
- **Type:** JavaScript
- **Size:** 1.26 KB

#### picocolors.browser
- **File:** `frontend-tools/node_modules/picocolors/picocolors.browser.js`
- **Type:** JavaScript
- **Size:** 598 B

#### link
- **File:** `frontend-tools/node_modules/http-link-header/lib/link.js`
- **Type:** JavaScript
- **Size:** 10.05 KB
- **Capabilities:**
  - Token character pattern
  - Shallow compares two objects to check if their properties match.
  - Get refs with given relation type
- **Methods:** 5

#### index
- **File:** `frontend-tools/node_modules/bare-os/index.js`
- **Type:** JavaScript
- **Size:** 2.38 KB
- **Methods:** 7

#### errors
- **File:** `frontend-tools/node_modules/bare-os/lib/errors.js`
- **Type:** JavaScript
- **Size:** 479 B

#### index
- **File:** `frontend-tools/node_modules/is-obj/index.js`
- **Type:** JavaScript
- **Size:** 144 B

#### escodegen
- **File:** `frontend-tools/node_modules/escodegen/escodegen.js`
- **Type:** JavaScript
- **Size:** 94.79 KB
- **Capabilities:**
  - flatten an array to a string, where the array can contain
  - convert generated to a SourceNode when source maps are enabled.
  - Generate Star Suffix
- **Methods:** 20

#### esgenerate
- **File:** `frontend-tools/node_modules/escodegen/bin/esgenerate.js`
- **Type:** JavaScript
- **Size:** 2.36 KB

#### escodegen
- **File:** `frontend-tools/node_modules/escodegen/bin/escodegen.js`
- **Type:** JavaScript
- **Size:** 2.65 KB

#### helpers
- **File:** `frontend-tools/node_modules/socks-proxy-agent/node_modules/agent-base/dist/helpers.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/socks-proxy-agent/node_modules/agent-base/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Determine whether this is an `http` or `https` request.

#### index
- **File:** `frontend-tools/node_modules/socks-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 6.57 KB
- **Capabilities:**
  - Initiates a SOCKS connection to the specified SOCKS proxy server,
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/open/index.js`
- **Type:** JavaScript
- **Size:** 7.66 KB
- **Methods:** 3

#### scope
- **File:** `frontend-tools/node_modules/@sentry/hub/dist/scope.js`
- **Type:** JavaScript
- **Size:** 15.55 KB
- **Capabilities:**
  - Absolute maximum number of breadcrumbs added to an event.
  - A place to stash data which is needed at some point in the SDK's event processing pipeline but which shouldn't get
  - Inherit values from the parent scope.
- **Methods:** 3

#### sessionflusher
- **File:** `frontend-tools/node_modules/@sentry/hub/dist/sessionflusher.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Capabilities:**
  - Wrapper function for _incrementSessionStatusCount that checks if the instance of SessionFlusher is enabled then
  - Increments status bucket in pendingAggregates buffer (internal state) corresponding to status of
- **Methods:** 2

#### session
- **File:** `frontend-tools/node_modules/@sentry/hub/dist/session.js`
- **Type:** JavaScript
- **Size:** 3.83 KB
- **Methods:** 1

#### hub
- **File:** `frontend-tools/node_modules/@sentry/hub/dist/hub.js`
- **Type:** JavaScript
- **Size:** 17.86 KB
- **Capabilities:**
  - API compatibility version of this hub.
  - Default maximum number of breadcrumbs added to an event. Can be overwritten
  - Sends the current Session on the scope
- **Methods:** 13

#### scope
- **File:** `frontend-tools/node_modules/@sentry/hub/esm/scope.js`
- **Type:** JavaScript
- **Size:** 15.14 KB
- **Capabilities:**
  - Absolute maximum number of breadcrumbs added to an event.
  - A place to stash data which is needed at some point in the SDK's event processing pipeline but which shouldn't get
  - Inherit values from the parent scope.
- **Methods:** 3

#### sessionflusher
- **File:** `frontend-tools/node_modules/@sentry/hub/esm/sessionflusher.js`
- **Type:** JavaScript
- **Size:** 4.74 KB
- **Capabilities:**
  - Wrapper function for _incrementSessionStatusCount that checks if the instance of SessionFlusher is enabled then
  - Increments status bucket in pendingAggregates buffer (internal state) corresponding to status of
- **Methods:** 2

#### session
- **File:** `frontend-tools/node_modules/@sentry/hub/esm/session.js`
- **Type:** JavaScript
- **Size:** 3.76 KB
- **Methods:** 1

#### hub
- **File:** `frontend-tools/node_modules/@sentry/hub/esm/hub.js`
- **Type:** JavaScript
- **Size:** 17.46 KB
- **Capabilities:**
  - API compatibility version of this hub.
  - Default maximum number of breadcrumbs added to an event. Can be overwritten
  - Sends the current Session on the scope
- **Methods:** 13

#### sessionstatus
- **File:** `frontend-tools/node_modules/@sentry/types/dist/sessionstatus.js`
- **Type:** JavaScript
- **Size:** 559 B

#### status
- **File:** `frontend-tools/node_modules/@sentry/types/dist/status.js`
- **Type:** JavaScript
- **Size:** 849 B

#### wrappedfunction
- **File:** `frontend-tools/node_modules/@sentry/types/dist/wrappedfunction.js`
- **Type:** JavaScript
- **Size:** 106 B

#### severity
- **File:** `frontend-tools/node_modules/@sentry/types/dist/severity.js`
- **Type:** JavaScript
- **Size:** 929 B
- **Capabilities:**
  - TODO(v7): Remove this enum and replace with SeverityLevel

#### requestsessionstatus
- **File:** `frontend-tools/node_modules/@sentry/types/dist/requestsessionstatus.js`
- **Type:** JavaScript
- **Size:** 570 B

#### sessionstatus
- **File:** `frontend-tools/node_modules/@sentry/types/esm/sessionstatus.js`
- **Type:** JavaScript
- **Size:** 471 B

#### status
- **File:** `frontend-tools/node_modules/@sentry/types/esm/status.js`
- **Type:** JavaScript
- **Size:** 768 B

#### wrappedfunction
- **File:** `frontend-tools/node_modules/@sentry/types/esm/wrappedfunction.js`
- **Type:** JavaScript
- **Size:** 43 B

#### severity
- **File:** `frontend-tools/node_modules/@sentry/types/esm/severity.js`
- **Type:** JavaScript
- **Size:** 849 B
- **Capabilities:**
  - TODO(v7): Remove this enum and replace with SeverityLevel

#### requestsessionstatus
- **File:** `frontend-tools/node_modules/@sentry/types/esm/requestsessionstatus.js`
- **Type:** JavaScript
- **Size:** 475 B

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/http.js`
- **Type:** JavaScript
- **Size:** 6.09 KB
- **Capabilities:**
  - Function which creates a function which creates wrapped versions of internal `request` and `get` calls within `http`
  - Captures Breadcrumb based on provided request/response pair
- **Methods:** 6

#### onunhandledrejection
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/onunhandledrejection.js`
- **Type:** JavaScript
- **Size:** 3.57 KB
- **Capabilities:**
  - Send an exception with reason
  - Handler for `mode` option
- **Methods:** 2

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/utils/http.js`
- **Type:** JavaScript
- **Size:** 7.16 KB
- **Capabilities:**
  - Checks whether given url points to Sentry server
  - Assemble a URL to be used for breadcrumbs and spans.
  - Handle various edge cases in the span description (for spans representing http(s) requests).
- **Methods:** 5

#### errorhandling
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/utils/errorhandling.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/index.js`
- **Type:** JavaScript
- **Size:** 781 B

#### linkederrors
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/linkederrors.js`
- **Type:** JavaScript
- **Size:** 3.78 KB
- **Methods:** 1

#### onuncaughtexception
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/onuncaughtexception.js`
- **Type:** JavaScript
- **Size:** 5.87 KB
- **Methods:** 1

#### console
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/console.js`
- **Type:** JavaScript
- **Size:** 1.96 KB
- **Capabilities:**
  - Wrapper function that'll be used for every console level
- **Methods:** 4

#### contextlines
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/contextlines.js`
- **Type:** JavaScript
- **Size:** 8.06 KB
- **Capabilities:**
  - Resets the file cache. Exists for testing purposes.
  - Reads file contents and caches them in a global LRU cache.
- **Methods:** 4

#### modules
- **File:** `frontend-tools/node_modules/@sentry/node/dist/integrations/modules.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Methods:** 3

#### eventbuilder
- **File:** `frontend-tools/node_modules/@sentry/node/dist/eventbuilder.js`
- **Type:** JavaScript
- **Size:** 3.21 KB
- **Capabilities:**
  - Extracts stack frames from the error.stack string
  - Extracts stack frames from the error and builds a Sentry Exception
  - Builds and Event from a Exception
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/dist/index.js`
- **Type:** JavaScript
- **Size:** 2.53 KB

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/dist/transports/http.js`
- **Type:** JavaScript
- **Size:** 1.24 KB
- **Methods:** 1

#### https
- **File:** `frontend-tools/node_modules/@sentry/node/dist/transports/https.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Methods:** 1

#### new
- **File:** `frontend-tools/node_modules/@sentry/node/dist/transports/new.js`
- **Type:** JavaScript
- **Size:** 4.22 KB
- **Capabilities:**
  - Creates a Transport that uses native the native 'http' and 'https' modules to send events to Sentry.
  - Honors the `no_proxy` env variable with the highest priority to allow for hosts exclusion.
  - Creates a RequestExecutor to be used with `createTransport`.
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/dist/transports/base/index.js`
- **Type:** JavaScript
- **Size:** 11.58 KB
- **Capabilities:**
  - Extracts proxy settings from client options and env variables.
  - Gets the time that given category is disabled until for rate limiting
  - Checks if a category is rate limited
- **Methods:** 2

#### backend
- **File:** `frontend-tools/node_modules/@sentry/node/dist/backend.js`
- **Type:** JavaScript
- **Size:** 2.79 KB
- **Capabilities:**
  - The Sentry Node SDK Backend.
- **Methods:** 1

#### handlers
- **File:** `frontend-tools/node_modules/@sentry/node/dist/handlers.js`
- **Type:** JavaScript
- **Size:** 16.22 KB
- **Capabilities:**
  - Express-compatible tracing handler.
  - Set parameterized as transaction name e.g.: `GET /users/:id`
  - Extracts complete generalized path from the request object and uses it to construct transaction name.
- **Methods:** 14

#### utils
- **File:** `frontend-tools/node_modules/@sentry/node/dist/utils.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - Recursively read the contents of a directory.
- **Methods:** 2

#### stack-parser
- **File:** `frontend-tools/node_modules/@sentry/node/dist/stack-parser.js`
- **Type:** JavaScript
- **Size:** 3.56 KB
- **Methods:** 2

#### client
- **File:** `frontend-tools/node_modules/@sentry/node/dist/client.js`
- **Type:** JavaScript
- **Size:** 5.37 KB
- **Capabilities:**
  - The Sentry Node SDK Client.
  - Creates a new Node SDK instance.
  - Method responsible for capturing/ending a request session by calling `incrementSessionStatusCount` to increment
- **Methods:** 1

#### sdk
- **File:** `frontend-tools/node_modules/@sentry/node/dist/sdk.js`
- **Type:** JavaScript
- **Size:** 8.79 KB
- **Capabilities:**
  - The Sentry Node SDK Client.
  - This is the getter for lastEventId.
  - Function that takes an instance of NodeClient and checks if autoSessionTracking option is enabled for that client
- **Methods:** 9

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/http.js`
- **Type:** JavaScript
- **Size:** 6 KB
- **Capabilities:**
  - Function which creates a function which creates wrapped versions of internal `request` and `get` calls within `http`
  - Captures Breadcrumb based on provided request/response pair
- **Methods:** 6

#### onunhandledrejection
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/onunhandledrejection.js`
- **Type:** JavaScript
- **Size:** 3.46 KB
- **Capabilities:**
  - Send an exception with reason
  - Handler for `mode` option
- **Methods:** 2

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/utils/http.js`
- **Type:** JavaScript
- **Size:** 6.89 KB
- **Capabilities:**
  - Checks whether given url points to Sentry server
  - Assemble a URL to be used for breadcrumbs and spans.
  - Handle various edge cases in the span description (for spans representing http(s) requests).
- **Methods:** 5

#### errorhandling
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/utils/errorhandling.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/index.js`
- **Type:** JavaScript
- **Size:** 356 B

#### linkederrors
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/linkederrors.js`
- **Type:** JavaScript
- **Size:** 3.7 KB
- **Methods:** 1

#### onuncaughtexception
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/onuncaughtexception.js`
- **Type:** JavaScript
- **Size:** 5.75 KB
- **Methods:** 1

#### console
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/console.js`
- **Type:** JavaScript
- **Size:** 1.89 KB
- **Capabilities:**
  - Wrapper function that'll be used for every console level
- **Methods:** 4

#### contextlines
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/contextlines.js`
- **Type:** JavaScript
- **Size:** 7.89 KB
- **Capabilities:**
  - Resets the file cache. Exists for testing purposes.
  - Reads file contents and caches them in a global LRU cache.
- **Methods:** 4

#### modules
- **File:** `frontend-tools/node_modules/@sentry/node/esm/integrations/modules.js`
- **Type:** JavaScript
- **Size:** 2.37 KB
- **Methods:** 3

#### eventbuilder
- **File:** `frontend-tools/node_modules/@sentry/node/esm/eventbuilder.js`
- **Type:** JavaScript
- **Size:** 3.03 KB
- **Capabilities:**
  - Extracts stack frames from the error.stack string
  - Extracts stack frames from the error and builds a Sentry Exception
  - Builds and Event from a Exception
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/esm/index.js`
- **Type:** JavaScript
- **Size:** 1.5 KB

#### http
- **File:** `frontend-tools/node_modules/@sentry/node/esm/transports/http.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Methods:** 1

#### https
- **File:** `frontend-tools/node_modules/@sentry/node/esm/transports/https.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Methods:** 1

#### new
- **File:** `frontend-tools/node_modules/@sentry/node/esm/transports/new.js`
- **Type:** JavaScript
- **Size:** 4.12 KB
- **Capabilities:**
  - Creates a Transport that uses native the native 'http' and 'https' modules to send events to Sentry.
  - Honors the `no_proxy` env variable with the highest priority to allow for hosts exclusion.
  - Creates a RequestExecutor to be used with `createTransport`.
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/@sentry/node/esm/transports/base/index.js`
- **Type:** JavaScript
- **Size:** 11.51 KB
- **Capabilities:**
  - Extracts proxy settings from client options and env variables.
  - Gets the time that given category is disabled until for rate limiting
  - Checks if a category is rate limited
- **Methods:** 2

#### backend
- **File:** `frontend-tools/node_modules/@sentry/node/esm/backend.js`
- **Type:** JavaScript
- **Size:** 2.71 KB
- **Capabilities:**
  - The Sentry Node SDK Backend.
- **Methods:** 1

#### handlers
- **File:** `frontend-tools/node_modules/@sentry/node/esm/handlers.js`
- **Type:** JavaScript
- **Size:** 15.96 KB
- **Capabilities:**
  - Express-compatible tracing handler.
  - Set parameterized as transaction name e.g.: `GET /users/:id`
  - Extracts complete generalized path from the request object and uses it to construct transaction name.
- **Methods:** 14

#### utils
- **File:** `frontend-tools/node_modules/@sentry/node/esm/utils.js`
- **Type:** JavaScript
- **Size:** 1.64 KB
- **Capabilities:**
  - Recursively read the contents of a directory.
- **Methods:** 2

#### stack-parser
- **File:** `frontend-tools/node_modules/@sentry/node/esm/stack-parser.js`
- **Type:** JavaScript
- **Size:** 3.49 KB
- **Methods:** 2

#### client
- **File:** `frontend-tools/node_modules/@sentry/node/esm/client.js`
- **Type:** JavaScript
- **Size:** 5.27 KB
- **Capabilities:**
  - The Sentry Node SDK Client.
  - Creates a new Node SDK instance.
  - Method responsible for capturing/ending a request session by calling `incrementSessionStatusCount` to increment
- **Methods:** 1

#### sdk
- **File:** `frontend-tools/node_modules/@sentry/node/esm/sdk.js`
- **Type:** JavaScript
- **Size:** 8.53 KB
- **Capabilities:**
  - The Sentry Node SDK Client.
  - This is the getter for lastEventId.
  - Function that takes an instance of NodeClient and checks if autoSessionTracking option is enabled for that client
- **Methods:** 9

#### polyfill
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/polyfill.js`
- **Type:** JavaScript
- **Size:** 838 B
- **Capabilities:**
  - setPrototypeOf polyfill using __proto__
  - setPrototypeOf polyfill using mixin
- **Methods:** 2

#### normalize
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/normalize.js`
- **Type:** JavaScript
- **Size:** 8.65 KB
- **Capabilities:**
  - Recursively normalizes the given object.
  - Visits a node to perform normalization on it
  - Stringify the given value. Handles various known special values and types.
- **Methods:** 7

#### logger
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/logger.js`
- **Type:** JavaScript
- **Size:** 2.9 KB
- **Capabilities:**
  - Temporarily disable sentry console instrumentations.
- **Methods:** 3

#### error
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/error.js`
- **Type:** JavaScript
- **Size:** 717 B
- **Methods:** 1

#### object
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/object.js`
- **Type:** JavaScript
- **Size:** 8.3 KB
- **Capabilities:**
  - Replace a method in an object with a wrapped version of itself.
  - Defines a non-enumerable property on the given object.
  - Remembers the original function on the wrapped function and
- **Methods:** 18

#### global
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/global.js`
- **Type:** JavaScript
- **Size:** 1.67 KB
- **Capabilities:**
  - NOTE: In order to avoid circular dependencies, if you add a function to this module and it needs to print something,
  - Safely get global scope object
  - Returns a global singleton contained in the global `__SENTRY__` object.
- **Methods:** 5

#### stacktrace
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/stacktrace.js`
- **Type:** JavaScript
- **Size:** 3.86 KB
- **Capabilities:**
  - Creates a stack parser with the supplied line parsers
  - Safely extract function name from itself
  - Create Stack Parser
- **Methods:** 4

#### supports
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/supports.js`
- **Type:** JavaScript
- **Size:** 5.64 KB
- **Capabilities:**
  - Tells whether current environment supports ErrorEvent objects
  - Tells whether current environment supports DOMError objects
  - Tells whether current environment supports DOMException objects
- **Methods:** 11

#### time
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/time.js`
- **Type:** JavaScript
- **Size:** 7.07 KB
- **Capabilities:**
  - A TimestampSource implementation for environments that do not support the Performance Web API natively.
  - Returns a wrapper around the native Performance API browser implementation, or undefined for browsers that do not
  - Returns the native Performance API implementation from Node.js. Returns undefined in old Node.js versions that don't
- **Methods:** 2

#### status
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/status.js`
- **Type:** JavaScript
- **Size:** 616 B
- **Methods:** 1

#### is
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/is.js`
- **Type:** JavaScript
- **Size:** 4.75 KB
- **Capabilities:**
  - Checks whether given value's type is one of a few Error or Error-like
  - Checks whether given value's type is ErrorEvent
  - Checks whether given value's type is DOMError
- **Methods:** 15

#### syncpromise
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/syncpromise.js`
- **Type:** JavaScript
- **Size:** 5.02 KB
- **Capabilities:**
  - Creates a resolved sync promise.
  - Creates a rejected sync promise.
  - Thenable class that behaves like a Promise and follows it's interface
- **Methods:** 3

#### path
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/path.js`
- **Type:** JavaScript
- **Size:** 5 KB
- **Methods:** 10

#### severity
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/severity.js`
- **Type:** JavaScript
- **Size:** 664 B
- **Methods:** 2

#### tracing
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/tracing.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - Extract transaction context data from a `sentry-trace` header.
- **Methods:** 1

#### misc
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/misc.js`
- **Type:** JavaScript
- **Size:** 9.16 KB
- **Capabilities:**
  - UUID4 generator
  - Parses string form of URL into an object
  - Extracts either message or type+value from an event that can be used for user-facing logs
- **Methods:** 12

#### env
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/env.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Capabilities:**
  - Figures out if we're building a browser bundle.
- **Methods:** 1

#### ratelimit
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/ratelimit.js`
- **Type:** JavaScript
- **Size:** 4.02 KB
- **Capabilities:**
  - Extracts Retry-After value from the request header or returns default value
  - Gets the time that given category is disabled until for rate limiting
  - Checks if a category is rate limited
- **Methods:** 4

#### promisebuffer
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/promisebuffer.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Capabilities:**
  - Creates an new PromiseBuffer object with the specified limit
  - Remove a promise from the queue.
  - Add a promise (representing an in-flight action) to the queue, and set it to remove itself on fulfillment.
- **Methods:** 6

#### dsn
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/dsn.js`
- **Type:** JavaScript
- **Size:** 3.69 KB
- **Capabilities:**
  - Renders the string representation of this Dsn.
  - Validate Dsn
- **Methods:** 6

#### string
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/string.js`
- **Type:** JavaScript
- **Size:** 3.74 KB
- **Capabilities:**
  - Truncates given string to the maximum characters count
  - This is basically just `trim_line` from
  - Join values in array
- **Methods:** 5

#### clientreport
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/clientreport.js`
- **Type:** JavaScript
- **Size:** 763 B
- **Capabilities:**
  - Creates client report envelope
- **Methods:** 1

#### async
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/async.js`
- **Type:** JavaScript
- **Size:** 492 B
- **Capabilities:**
  - Consumes the promise and logs the error when it rejects.
- **Methods:** 1

#### memo
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/memo.js`
- **Type:** JavaScript
- **Size:** 1.23 KB
- **Capabilities:**
  - Helper to decycle json objects
- **Methods:** 3

#### instrument
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/instrument.js`
- **Type:** JavaScript
- **Size:** 20.83 KB
- **Capabilities:**
  - Instrument native APIs to call handlers that can be used to create breadcrumbs, APM spans etc.
  - Add handler that will be called when given type of instrumentation triggers.
  - Decide whether the current event should finish the debounce of previously captured one.
- **Methods:** 17

#### browser
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/browser.js`
- **Type:** JavaScript
- **Size:** 3.56 KB
- **Capabilities:**
  - Given a child DOM element, returns a query-selector statement describing that
  - Returns a simple, query-selector representation of a DOM element
  - A safe form of location.href
- **Methods:** 3

#### node
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/node.js`
- **Type:** JavaScript
- **Size:** 2.25 KB
- **Capabilities:**
  - NOTE: In order to avoid circular dependencies, if you add a function to this module and it needs to print something,
  - Checks whether we're in the Node.js or Browser environment
  - Requires a module which is protected against bundler minification.
- **Methods:** 5

#### envelope
- **File:** `frontend-tools/node_modules/@sentry/utils/dist/envelope.js`
- **Type:** JavaScript
- **Size:** 2.08 KB
- **Capabilities:**
  - Creates an envelope.
  - Add an item to an envelope.
  - Get the type of the envelope. Grabs the type from the first envelope item.
- **Methods:** 4

#### polyfill
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/polyfill.js`
- **Type:** JavaScript
- **Size:** 778 B
- **Capabilities:**
  - setPrototypeOf polyfill using __proto__
  - setPrototypeOf polyfill using mixin
- **Methods:** 2

#### normalize
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/normalize.js`
- **Type:** JavaScript
- **Size:** 8.63 KB
- **Capabilities:**
  - Recursively normalizes the given object.
  - Visits a node to perform normalization on it
  - Stringify the given value. Handles various known special values and types.
- **Methods:** 7

#### logger
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/logger.js`
- **Type:** JavaScript
- **Size:** 2.74 KB
- **Capabilities:**
  - Temporarily disable sentry console instrumentations.
- **Methods:** 3

#### error
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/error.js`
- **Type:** JavaScript
- **Size:** 632 B
- **Methods:** 1

#### object
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/object.js`
- **Type:** JavaScript
- **Size:** 7.9 KB
- **Capabilities:**
  - Replace a method in an object with a wrapped version of itself.
  - Defines a non-enumerable property on the given object.
  - Remembers the original function on the wrapped function and
- **Methods:** 18

#### global
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/global.js`
- **Type:** JavaScript
- **Size:** 1.52 KB
- **Capabilities:**
  - NOTE: In order to avoid circular dependencies, if you add a function to this module and it needs to print something,
  - Safely get global scope object
  - Returns a global singleton contained in the global `__SENTRY__` object.
- **Methods:** 5

#### stacktrace
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/stacktrace.js`
- **Type:** JavaScript
- **Size:** 3.64 KB
- **Capabilities:**
  - Creates a stack parser with the supplied line parsers
  - Safely extract function name from itself
  - Create Stack Parser
- **Methods:** 4

#### supports
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/supports.js`
- **Type:** JavaScript
- **Size:** 5.18 KB
- **Capabilities:**
  - Tells whether current environment supports ErrorEvent objects
  - Tells whether current environment supports DOMError objects
  - Tells whether current environment supports DOMException objects
- **Methods:** 11

#### time
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/time.js`
- **Type:** JavaScript
- **Size:** 7.14 KB
- **Capabilities:**
  - A TimestampSource implementation for environments that do not support the Performance Web API natively.
  - Returns a wrapper around the native Performance API browser implementation, or undefined for browsers that do not
  - Returns the native Performance API implementation from Node.js. Returns undefined in old Node.js versions that don't
- **Methods:** 2

#### status
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/status.js`
- **Type:** JavaScript
- **Size:** 501 B
- **Methods:** 1

#### is
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/is.js`
- **Type:** JavaScript
- **Size:** 4.33 KB
- **Capabilities:**
  - Checks whether given value's type is one of a few Error or Error-like
  - Checks whether given value's type is ErrorEvent
  - Checks whether given value's type is DOMError
- **Methods:** 15

#### syncpromise
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/syncpromise.js`
- **Type:** JavaScript
- **Size:** 4.87 KB
- **Capabilities:**
  - Creates a resolved sync promise.
  - Creates a rejected sync promise.
  - Thenable class that behaves like a Promise and follows it's interface
- **Methods:** 3

#### path
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/path.js`
- **Type:** JavaScript
- **Size:** 4.79 KB
- **Methods:** 10

#### severity
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/severity.js`
- **Type:** JavaScript
- **Size:** 545 B
- **Methods:** 2

#### tracing
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/tracing.js`
- **Type:** JavaScript
- **Size:** 944 B
- **Capabilities:**
  - Extract transaction context data from a `sentry-trace` header.
- **Methods:** 1

#### misc
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/misc.js`
- **Type:** JavaScript
- **Size:** 8.69 KB
- **Capabilities:**
  - UUID4 generator
  - Parses string form of URL into an object
  - Extracts either message or type+value from an event that can be used for user-facing logs
- **Methods:** 12

#### env
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/env.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - Figures out if we're building a browser bundle.
- **Methods:** 1

#### ratelimit
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/ratelimit.js`
- **Type:** JavaScript
- **Size:** 3.79 KB
- **Capabilities:**
  - Extracts Retry-After value from the request header or returns default value
  - Gets the time that given category is disabled until for rate limiting
  - Checks if a category is rate limited
- **Methods:** 4

#### promisebuffer
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/promisebuffer.js`
- **Type:** JavaScript
- **Size:** 3.85 KB
- **Capabilities:**
  - Creates an new PromiseBuffer object with the specified limit
  - Remove a promise from the queue.
  - Add a promise (representing an in-flight action) to the queue, and set it to remove itself on fulfillment.
- **Methods:** 6

#### dsn
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/dsn.js`
- **Type:** JavaScript
- **Size:** 3.54 KB
- **Capabilities:**
  - Renders the string representation of this Dsn.
  - Validate Dsn
- **Methods:** 6

#### string
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/string.js`
- **Type:** JavaScript
- **Size:** 3.53 KB
- **Capabilities:**
  - Truncates given string to the maximum characters count
  - This is basically just `trim_line` from
  - Join values in array
- **Methods:** 5

#### clientreport
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/clientreport.js`
- **Type:** JavaScript
- **Size:** 646 B
- **Capabilities:**
  - Creates client report envelope
- **Methods:** 1

#### async
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/async.js`
- **Type:** JavaScript
- **Size:** 411 B
- **Capabilities:**
  - Consumes the promise and logs the error when it rejects.
- **Methods:** 1

#### memo
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/memo.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Helper to decycle json objects
- **Methods:** 3

#### instrument
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/instrument.js`
- **Type:** JavaScript
- **Size:** 20.57 KB
- **Capabilities:**
  - Instrument native APIs to call handlers that can be used to create breadcrumbs, APM spans etc.
  - Add handler that will be called when given type of instrumentation triggers.
  - Decide whether the current event should finish the debounce of previously captured one.
- **Methods:** 17

#### browser
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/browser.js`
- **Type:** JavaScript
- **Size:** 3.42 KB
- **Capabilities:**
  - Given a child DOM element, returns a query-selector statement describing that
  - Returns a simple, query-selector representation of a DOM element
  - A safe form of location.href
- **Methods:** 3

#### node
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/node.js`
- **Type:** JavaScript
- **Size:** 2.11 KB
- **Capabilities:**
  - NOTE: In order to avoid circular dependencies, if you add a function to this module and it needs to print something,
  - Checks whether we're in the Node.js or Browser environment
  - Requires a module which is protected against bundler minification.
- **Methods:** 5

#### envelope
- **File:** `frontend-tools/node_modules/@sentry/utils/esm/envelope.js`
- **Type:** JavaScript
- **Size:** 1.83 KB
- **Capabilities:**
  - Creates an envelope.
  - Add an item to an envelope.
  - Get the type of the envelope. Grabs the type from the first envelope item.
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/@sentry/minimal/dist/index.js`
- **Type:** JavaScript
- **Size:** 6.86 KB
- **Capabilities:**
  - This calls a function on the current hub.
  - Captures an exception event and sends it to Sentry.
  - Captures a message event and sends it to Sentry.
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@sentry/minimal/esm/index.js`
- **Type:** JavaScript
- **Size:** 6.41 KB
- **Capabilities:**
  - This calls a function on the current hub.
  - Captures an exception event and sends it to Sentry.
  - Captures a message event and sends it to Sentry.
- **Methods:** 20

#### integration
- **File:** `frontend-tools/node_modules/@sentry/core/dist/integration.js`
- **Type:** JavaScript
- **Size:** 3.34 KB
- **Capabilities:**
  - Given a list of integration instances this installs them all. When `withDefaults` is set to `true` then all default
- **Methods:** 4

#### functiontostring
- **File:** `frontend-tools/node_modules/@sentry/core/dist/integrations/functiontostring.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@sentry/core/dist/integrations/index.js`
- **Type:** JavaScript
- **Size:** 326 B

#### inboundfilters
- **File:** `frontend-tools/node_modules/@sentry/core/dist/integrations/inboundfilters.js`
- **Type:** JavaScript
- **Size:** 6.36 KB
- **Methods:** 10

#### request
- **File:** `frontend-tools/node_modules/@sentry/core/dist/request.js`
- **Type:** JavaScript
- **Size:** 9.27 KB
- **Capabilities:**
  - Apply SdkInfo (name, version, packages, integrations) to the corresponding event key.
  - Create an Envelope from an event. Note that this is duplicated from below,
- **Methods:** 6

#### basebackend
- **File:** `frontend-tools/node_modules/@sentry/core/dist/basebackend.js`
- **Type:** JavaScript
- **Size:** 3.67 KB
- **Capabilities:**
  - This is the base implemention of a Backend.
  - Sets up the transport so it can be used later to send requests.
- **Methods:** 1

#### baseclient
- **File:** `frontend-tools/node_modules/@sentry/core/dist/baseclient.js`
- **Type:** JavaScript
- **Size:** 22.04 KB
- **Capabilities:**
  - Base implementation for all JavaScript SDK clients.
  - Initializes this client instance.
  - Sets up the integrations
- **Methods:** 6

#### base
- **File:** `frontend-tools/node_modules/@sentry/core/dist/transports/base.js`
- **Type:** JavaScript
- **Size:** 2.51 KB
- **Capabilities:**
  - Creates a `NewTransport`
- **Methods:** 3

#### noop
- **File:** `frontend-tools/node_modules/@sentry/core/dist/transports/noop.js`
- **Type:** JavaScript
- **Size:** 733 B
- **Methods:** 1

#### api
- **File:** `frontend-tools/node_modules/@sentry/core/dist/api.js`
- **Type:** JavaScript
- **Size:** 5.8 KB
- **Capabilities:**
  - Helper class to provide urls, headers and metadata that can be used to form
  - Returns the store endpoint URL with auth in the query string.
  - Returns the envelope endpoint URL with auth in the query string.
- **Methods:** 11

#### sdk
- **File:** `frontend-tools/node_modules/@sentry/core/dist/sdk.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Internal function to create a new SDK client instance. The client is
- **Methods:** 2

#### integration
- **File:** `frontend-tools/node_modules/@sentry/core/esm/integration.js`
- **Type:** JavaScript
- **Size:** 3.15 KB
- **Capabilities:**
  - Given a list of integration instances this installs them all. When `withDefaults` is set to `true` then all default
- **Methods:** 4

#### functiontostring
- **File:** `frontend-tools/node_modules/@sentry/core/esm/integrations/functiontostring.js`
- **Type:** JavaScript
- **Size:** 1.13 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@sentry/core/esm/integrations/index.js`
- **Type:** JavaScript
- **Size:** 139 B

#### inboundfilters
- **File:** `frontend-tools/node_modules/@sentry/core/esm/integrations/inboundfilters.js`
- **Type:** JavaScript
- **Size:** 6.09 KB
- **Methods:** 10

#### request
- **File:** `frontend-tools/node_modules/@sentry/core/esm/request.js`
- **Type:** JavaScript
- **Size:** 9 KB
- **Capabilities:**
  - Apply SdkInfo (name, version, packages, integrations) to the corresponding event key.
  - Create an Envelope from an event. Note that this is duplicated from below,
- **Methods:** 6

#### basebackend
- **File:** `frontend-tools/node_modules/@sentry/core/esm/basebackend.js`
- **Type:** JavaScript
- **Size:** 3.52 KB
- **Capabilities:**
  - This is the base implemention of a Backend.
  - Sets up the transport so it can be used later to send requests.
- **Methods:** 1

#### baseclient
- **File:** `frontend-tools/node_modules/@sentry/core/esm/baseclient.js`
- **Type:** JavaScript
- **Size:** 21.72 KB
- **Capabilities:**
  - Base implementation for all JavaScript SDK clients.
  - Initializes this client instance.
  - Sets up the integrations
- **Methods:** 6

#### base
- **File:** `frontend-tools/node_modules/@sentry/core/esm/transports/base.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Creates a `NewTransport`
- **Methods:** 3

#### noop
- **File:** `frontend-tools/node_modules/@sentry/core/esm/transports/noop.js`
- **Type:** JavaScript
- **Size:** 654 B
- **Methods:** 1

#### api
- **File:** `frontend-tools/node_modules/@sentry/core/esm/api.js`
- **Type:** JavaScript
- **Size:** 5.45 KB
- **Capabilities:**
  - Helper class to provide urls, headers and metadata that can be used to form
  - Returns the store endpoint URL with auth in the query string.
  - Returns the envelope endpoint URL with auth in the query string.
- **Methods:** 11

#### sdk
- **File:** `frontend-tools/node_modules/@sentry/core/esm/sdk.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - Internal function to create a new SDK client instance. The client is
- **Methods:** 2

#### urlpattern
- **File:** `frontend-tools/node_modules/urlpattern-polyfill/dist/urlpattern.js`
- **Type:** JavaScript
- **Size:** 16.54 KB
- **Methods:** 20

#### tslib.es6
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### types
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/types.js`
- **Type:** JavaScript
- **Size:** 2.93 KB
- **Capabilities:**
  - Variable w/o any format, e.g `var` in `this is a {var}`
  - Variable w/ number format
  - Variable w/ date format
- **Methods:** 13

#### error
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/error.js`
- **Type:** JavaScript
- **Size:** 4.48 KB
- **Capabilities:**
  - Expecting a message fragment after the `plural` or `selectordinal` selector
  - Duplicate selectors in `plural` or `selectordinal` argument.

#### index
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/index.js`
- **Type:** JavaScript
- **Size:** 2.01 KB
- **Methods:** 2

#### parser
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/parser.js`
- **Type:** JavaScript
- **Size:** 48.2 KB
- **Capabilities:**
  - legacy Edge or Xbox One browser
  - A tag name must start with an ASCII lower/upper case letter. The grammar is based on the
  - This method assumes that the caller has peeked ahead for the first tag character.
- **Methods:** 16

#### manipulator
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/manipulator.js`
- **Type:** JavaScript
- **Size:** 4.93 KB
- **Capabilities:**
  - Hoist all selectors to the beginning of the AST & flatten the
  - Collect all variables in an AST to Record<string, TYPE>
  - Check if 2 ASTs are structurally the same. This primarily means that
- **Methods:** 7

#### printer
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/printer.js`
- **Type:** JavaScript
- **Size:** 3.78 KB
- **Methods:** 12

#### date-time-pattern-generator
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/date-time-pattern-generator.js`
- **Type:** JavaScript
- **Size:** 3.08 KB
- **Capabilities:**
  - Returns the best matching date time pattern if a date time skeleton
  - Maps the [hour cycle type](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/hourCycle)
- **Methods:** 2

#### no-parser
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/no-parser.js`
- **Type:** JavaScript
- **Size:** 615 B
- **Methods:** 1

#### types
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/types.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Variable w/o any format, e.g `var` in `this is a {var}`
  - Variable w/ number format
  - Variable w/ date format
- **Methods:** 13

#### error
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/error.js`
- **Type:** JavaScript
- **Size:** 4.36 KB
- **Capabilities:**
  - Expecting a message fragment after the `plural` or `selectordinal` selector
  - Duplicate selectors in `plural` or `selectordinal` argument.

#### index
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/index.js`
- **Type:** JavaScript
- **Size:** 1.7 KB
- **Methods:** 2

#### parser
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/parser.js`
- **Type:** JavaScript
- **Size:** 47.64 KB
- **Capabilities:**
  - legacy Edge or Xbox One browser
  - A tag name must start with an ASCII lower/upper case letter. The grammar is based on the
  - This method assumes that the caller has peeked ahead for the first tag character.
- **Methods:** 16

#### manipulator
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/manipulator.js`
- **Type:** JavaScript
- **Size:** 4.71 KB
- **Capabilities:**
  - Hoist all selectors to the beginning of the AST & flatten the
  - Collect all variables in an AST to Record<string, TYPE>
  - Check if 2 ASTs are structurally the same. This primarily means that
- **Methods:** 7

#### printer
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/printer.js`
- **Type:** JavaScript
- **Size:** 3.64 KB
- **Methods:** 12

#### date-time-pattern-generator
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/date-time-pattern-generator.js`
- **Type:** JavaScript
- **Size:** 2.87 KB
- **Capabilities:**
  - Returns the best matching date time pattern if a date time skeleton
  - Maps the [hour cycle type](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/hourCycle)
- **Methods:** 2

#### no-parser
- **File:** `frontend-tools/node_modules/@formatjs/icu-messageformat-parser/lib/no-parser.js`
- **Type:** JavaScript
- **Size:** 279 B
- **Methods:** 1

#### tslib.es6
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### index
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/index.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Methods:** 1

#### BestAvailableLocale
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/BestAvailableLocale.js`
- **Type:** JavaScript
- **Size:** 678 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-bestavailablelocale
- **Methods:** 1

#### InsertUnicodeExtensionAndCanonicalize
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/InsertUnicodeExtensionAndCanonicalize.js`
- **Type:** JavaScript
- **Size:** 1.45 KB
- **Methods:** 1

#### UnicodeExtensionValue
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/UnicodeExtensionValue.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-unicodeextensionvalue
- **Methods:** 1

#### LookupMatcher
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/LookupMatcher.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-lookupmatcher
- **Methods:** 1

#### CanonicalizeUValue
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/CanonicalizeUValue.js`
- **Type:** JavaScript
- **Size:** 491 B
- **Methods:** 1

#### LookupSupportedLocales
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/LookupSupportedLocales.js`
- **Type:** JavaScript
- **Size:** 902 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-lookupsupportedlocales
- **Methods:** 1

#### BestFitMatcher
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/BestFitMatcher.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-bestfitmatcher
- **Methods:** 1

#### ResolveLocale
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/ResolveLocale.js`
- **Type:** JavaScript
- **Size:** 4.19 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-resolvelocale
- **Methods:** 1

#### utils
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/utils.js`
- **Type:** JavaScript
- **Size:** 6.96 KB
- **Methods:** 7

#### UnicodeExtensionComponents
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/UnicodeExtensionComponents.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Methods:** 1

#### CanonicalizeLocaleList
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/CanonicalizeLocaleList.js`
- **Type:** JavaScript
- **Size:** 335 B
- **Capabilities:**
  - http://ecma-international.org/ecma-402/7.0/index.html#sec-canonicalizelocalelist
- **Methods:** 1

#### CanonicalizeUnicodeLocaleId
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/abstract/CanonicalizeUnicodeLocaleId.js`
- **Type:** JavaScript
- **Size:** 241 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/index.js`
- **Type:** JavaScript
- **Size:** 599 B
- **Methods:** 1

#### BestAvailableLocale
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/BestAvailableLocale.js`
- **Type:** JavaScript
- **Size:** 557 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-bestavailablelocale
- **Methods:** 1

#### InsertUnicodeExtensionAndCanonicalize
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/InsertUnicodeExtensionAndCanonicalize.js`
- **Type:** JavaScript
- **Size:** 1.22 KB
- **Methods:** 1

#### UnicodeExtensionValue
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/UnicodeExtensionValue.js`
- **Type:** JavaScript
- **Size:** 1.2 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-unicodeextensionvalue
- **Methods:** 1

#### LookupMatcher
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/LookupMatcher.js`
- **Type:** JavaScript
- **Size:** 1 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-lookupmatcher
- **Methods:** 1

#### CanonicalizeUValue
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/CanonicalizeUValue.js`
- **Type:** JavaScript
- **Size:** 362 B
- **Methods:** 1

#### LookupSupportedLocales
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/LookupSupportedLocales.js`
- **Type:** JavaScript
- **Size:** 765 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-lookupsupportedlocales
- **Methods:** 1

#### BestFitMatcher
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/BestFitMatcher.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-bestfitmatcher
- **Methods:** 1

#### ResolveLocale
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/ResolveLocale.js`
- **Type:** JavaScript
- **Size:** 3.9 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-resolvelocale
- **Methods:** 1

#### utils
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/utils.js`
- **Type:** JavaScript
- **Size:** 6.64 KB
- **Methods:** 7

#### UnicodeExtensionComponents
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/UnicodeExtensionComponents.js`
- **Type:** JavaScript
- **Size:** 1.53 KB
- **Methods:** 1

#### CanonicalizeLocaleList
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/CanonicalizeLocaleList.js`
- **Type:** JavaScript
- **Size:** 208 B
- **Capabilities:**
  - http://ecma-international.org/ecma-402/7.0/index.html#sec-canonicalizelocalelist
- **Methods:** 1

#### CanonicalizeUnicodeLocaleId
- **File:** `frontend-tools/node_modules/@formatjs/intl-localematcher/lib/abstract/CanonicalizeUnicodeLocaleId.js`
- **Type:** JavaScript
- **Size:** 104 B
- **Methods:** 1

#### tslib.es6
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### number
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/number.js`
- **Type:** JavaScript
- **Size:** 12.02 KB
- **Capabilities:**
  - https://github.com/unicode-org/icu/blob/master/docs/userguide/format_parse/numbers/skeletons.md#skeleton-stems-and-options
- **Methods:** 7

#### date-time
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/date-time.js`
- **Type:** JavaScript
- **Size:** 4.89 KB
- **Capabilities:**
  - https://unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
  - Parse Date time skeleton into Intl.DateTimeFormatOptions
- **Methods:** 1

#### number
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/lib/number.js`
- **Type:** JavaScript
- **Size:** 11.67 KB
- **Capabilities:**
  - https://github.com/unicode-org/icu/blob/master/docs/userguide/format_parse/numbers/skeletons.md#skeleton-stems-and-options
- **Methods:** 7

#### date-time
- **File:** `frontend-tools/node_modules/@formatjs/icu-skeleton-parser/lib/date-time.js`
- **Type:** JavaScript
- **Size:** 4.77 KB
- **Capabilities:**
  - https://unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
  - Parse Date time skeleton into Intl.DateTimeFormatOptions
- **Methods:** 1

#### tslib.es6
- **File:** `frontend-tools/node_modules/@formatjs/fast-memoize/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@formatjs/fast-memoize/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/@formatjs/fast-memoize/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### index
- **File:** `frontend-tools/node_modules/@formatjs/fast-memoize/index.js`
- **Type:** JavaScript
- **Size:** 2.63 KB
- **Methods:** 10

#### index
- **File:** `frontend-tools/node_modules/@formatjs/fast-memoize/lib/index.js`
- **Type:** JavaScript
- **Size:** 2.51 KB
- **Methods:** 10

#### tslib.es6
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 18.76 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### tslib
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 22.83 KB
- **Methods:** 16

#### data
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/data.js`
- **Type:** JavaScript
- **Size:** 611 B
- **Methods:** 2

#### CollapseNumberRange
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/CollapseNumberRange.js`
- **Type:** JavaScript
- **Size:** 1.99 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-collapsenumberrange
- **Methods:** 1

#### PartitionNumberRangePattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/PartitionNumberRangePattern.js`
- **Type:** JavaScript
- **Size:** 2.14 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-partitionnumberrangepattern
- **Methods:** 1

#### SetNumberFormatDigitOptions
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/SetNumberFormatDigitOptions.js`
- **Type:** JavaScript
- **Size:** 9.85 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-setnfdigitoptions
- **Methods:** 1

#### ToRawFixed
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/ToRawFixed.js`
- **Type:** JavaScript
- **Size:** 4.21 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-torawfixed
- **Methods:** 5

#### PartitionNumberPattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/PartitionNumberPattern.js`
- **Type:** JavaScript
- **Size:** 5 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-partitionnumberpattern
- **Methods:** 1

#### FormatNumericToString
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatNumericToString.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumberstring
- **Methods:** 1

#### ToRawPrecision
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/ToRawPrecision.js`
- **Type:** JavaScript
- **Size:** 5.64 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-torawprecision
- **Methods:** 4

#### FormatApproximately
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatApproximately.js`
- **Type:** JavaScript
- **Size:** 487 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatapproximately
- **Methods:** 1

#### ApplyUnsignedRoundingMode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/ApplyUnsignedRoundingMode.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Methods:** 1

#### ComputeExponentForMagnitude
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/ComputeExponentForMagnitude.js`
- **Type:** JavaScript
- **Size:** 3.27 KB
- **Capabilities:**
  - The abstract operation ComputeExponentForMagnitude computes an exponent by which to scale a
- **Methods:** 1

#### FormatNumericRangeToParts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatNumericRangeToParts.js`
- **Type:** JavaScript
- **Size:** 731 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumericrangetoparts
- **Methods:** 1

#### ComputeExponent
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/ComputeExponent.js`
- **Type:** JavaScript
- **Size:** 1.61 KB
- **Capabilities:**
  - The abstract operation ComputeExponent computes an exponent (power of ten) by which to scale x
- **Methods:** 1

#### GetUnsignedRoundingMode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/GetUnsignedRoundingMode.js`
- **Type:** JavaScript
- **Size:** 831 B
- **Methods:** 1

#### format_to_parts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/format_to_parts.js`
- **Type:** JavaScript
- **Size:** 21.4 KB
- **Capabilities:**
  - This is the decimal number pattern without signs or symbols.
- **Methods:** 5

#### InitializeNumberFormat
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/InitializeNumberFormat.js`
- **Type:** JavaScript
- **Size:** 3.94 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-initializenumberformat
- **Methods:** 1

#### CurrencyDigits
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/CurrencyDigits.js`
- **Type:** JavaScript
- **Size:** 398 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-currencydigits
- **Methods:** 1

#### SetNumberFormatUnitOptions
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/SetNumberFormatUnitOptions.js`
- **Type:** JavaScript
- **Size:** 3.74 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-setnumberformatunitoptions
- **Methods:** 1

#### FormatNumeric
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatNumeric.js`
- **Type:** JavaScript
- **Size:** 382 B
- **Methods:** 1

#### FormatNumericToParts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatNumericToParts.js`
- **Type:** JavaScript
- **Size:** 654 B
- **Methods:** 1

#### FormatNumericRange
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/NumberFormat/FormatNumericRange.js`
- **Type:** JavaScript
- **Size:** 591 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumericrange
- **Methods:** 1

#### GetNumberOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/GetNumberOption.js`
- **Type:** JavaScript
- **Size:** 523 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getnumberoption
- **Methods:** 1

#### SupportedLocales
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/SupportedLocales.js`
- **Type:** JavaScript
- **Size:** 965 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-supportedlocales
- **Methods:** 1

#### date-time
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/types/date-time.js`
- **Type:** JavaScript
- **Size:** 382 B

#### index
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/index.js`
- **Type:** JavaScript
- **Size:** 5.87 KB

#### DefaultNumberOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/DefaultNumberOption.js`
- **Type:** JavaScript
- **Size:** 626 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-defaultnumberoption
- **Methods:** 1

#### GetStringOrBooleanOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/GetStringOrBooleanOption.js`
- **Type:** JavaScript
- **Size:** 941 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getstringorbooleanoption
- **Methods:** 1

#### IsWellFormedUnitIdentifier
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/IsWellFormedUnitIdentifier.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Capabilities:**
  - This follows https://tc39.es/ecma402/#sec-case-sensitivity-and-case-mapping
  - https://tc39.es/ecma402/#sec-iswellformedunitidentifier
- **Methods:** 2

#### IsWellFormedCurrencyCode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/IsWellFormedCurrencyCode.js`
- **Type:** JavaScript
- **Size:** 695 B
- **Capabilities:**
  - This follows https://tc39.es/ecma402/#sec-case-sensitivity-and-case-mapping
  - https://tc39.es/ecma402/#sec-iswellformedcurrencycode
- **Methods:** 2

#### GetOptionsObject
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/GetOptionsObject.js`
- **Type:** JavaScript
- **Size:** 455 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getoptionsobject
- **Methods:** 1

#### 262
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/262.js`
- **Type:** JavaScript
- **Size:** 10.64 KB
- **Capabilities:**
  - https://tc39.es/ecma262/#sec-tostring
  - https://tc39.es/ecma262/#sec-tonumber
  - https://tc39.es/ecma262/#sec-tointeger
- **Methods:** 20

#### ToIntlMathematicalValue
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/ToIntlMathematicalValue.js`
- **Type:** JavaScript
- **Size:** 947 B
- **Methods:** 1

#### GetOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/GetOption.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getoption
- **Methods:** 1

#### IsValidTimeZoneName
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/IsValidTimeZoneName.js`
- **Type:** JavaScript
- **Size:** 847 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-isvalidtimezonename
- **Methods:** 1

#### utils
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/utils.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Capabilities:**
  - 7.3.5 CreateDataProperty
- **Methods:** 11

#### IsSanctionedSimpleUnitIdentifier
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/IsSanctionedSimpleUnitIdentifier.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#table-sanctioned-simple-unit-identifiers
  - https://tc39.es/ecma402/#sec-issanctionedsimpleunitidentifier
- **Methods:** 3

#### CanonicalizeLocaleList
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/CanonicalizeLocaleList.js`
- **Type:** JavaScript
- **Size:** 347 B
- **Capabilities:**
  - http://ecma-international.org/ecma-402/7.0/index.html#sec-canonicalizelocalelist
- **Methods:** 1

#### CoerceOptionsToObject
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/CoerceOptionsToObject.js`
- **Type:** JavaScript
- **Size:** 425 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-coerceoptionstoobject
- **Methods:** 1

#### CanonicalizeTimeZoneName
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/CanonicalizeTimeZoneName.js`
- **Type:** JavaScript
- **Size:** 709 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-canonicalizetimezonename
- **Methods:** 1

#### data
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/data.js`
- **Type:** JavaScript
- **Size:** 475 B
- **Methods:** 2

#### CollapseNumberRange
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/CollapseNumberRange.js`
- **Type:** JavaScript
- **Size:** 1.87 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-collapsenumberrange
- **Methods:** 1

#### PartitionNumberRangePattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/PartitionNumberRangePattern.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-partitionnumberrangepattern
- **Methods:** 1

#### SetNumberFormatDigitOptions
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/SetNumberFormatDigitOptions.js`
- **Type:** JavaScript
- **Size:** 9.46 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-setnfdigitoptions
- **Methods:** 1

#### ToRawFixed
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/ToRawFixed.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-torawfixed
- **Methods:** 5

#### PartitionNumberPattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/PartitionNumberPattern.js`
- **Type:** JavaScript
- **Size:** 4.68 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-partitionnumberpattern
- **Methods:** 1

#### FormatNumericToString
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatNumericToString.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumberstring
- **Methods:** 1

#### ToRawPrecision
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/ToRawPrecision.js`
- **Type:** JavaScript
- **Size:** 5.28 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-torawprecision
- **Methods:** 4

#### FormatApproximately
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatApproximately.js`
- **Type:** JavaScript
- **Size:** 366 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatapproximately
- **Methods:** 1

#### ApplyUnsignedRoundingMode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/ApplyUnsignedRoundingMode.js`
- **Type:** JavaScript
- **Size:** 1013 B
- **Methods:** 1

#### ComputeExponentForMagnitude
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/ComputeExponentForMagnitude.js`
- **Type:** JavaScript
- **Size:** 3.03 KB
- **Capabilities:**
  - The abstract operation ComputeExponentForMagnitude computes an exponent by which to scale a
- **Methods:** 1

#### FormatNumericRangeToParts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatNumericRangeToParts.js`
- **Type:** JavaScript
- **Size:** 562 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumericrangetoparts
- **Methods:** 1

#### ComputeExponent
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/ComputeExponent.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - The abstract operation ComputeExponent computes an exponent (power of ten) by which to scale x
- **Methods:** 1

#### GetUnsignedRoundingMode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/GetUnsignedRoundingMode.js`
- **Type:** JavaScript
- **Size:** 702 B
- **Methods:** 1

#### format_to_parts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/format_to_parts.js`
- **Type:** JavaScript
- **Size:** 21.07 KB
- **Capabilities:**
  - This is the decimal number pattern without signs or symbols.
- **Methods:** 5

#### InitializeNumberFormat
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/InitializeNumberFormat.js`
- **Type:** JavaScript
- **Size:** 3.5 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-initializenumberformat
- **Methods:** 1

#### CurrencyDigits
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/CurrencyDigits.js`
- **Type:** JavaScript
- **Size:** 284 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-currencydigits
- **Methods:** 1

#### SetNumberFormatUnitOptions
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/SetNumberFormatUnitOptions.js`
- **Type:** JavaScript
- **Size:** 3.39 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-setnumberformatunitoptions
- **Methods:** 1

#### FormatNumeric
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatNumeric.js`
- **Type:** JavaScript
- **Size:** 242 B
- **Methods:** 1

#### FormatNumericToParts
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatNumericToParts.js`
- **Type:** JavaScript
- **Size:** 494 B
- **Methods:** 1

#### FormatNumericRange
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/NumberFormat/FormatNumericRange.js`
- **Type:** JavaScript
- **Size:** 436 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-formatnumericrange
- **Methods:** 1

#### GetNumberOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/GetNumberOption.js`
- **Type:** JavaScript
- **Size:** 382 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getnumberoption
- **Methods:** 1

#### SupportedLocales
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/SupportedLocales.js`
- **Type:** JavaScript
- **Size:** 774 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-supportedlocales
- **Methods:** 1

#### date-time
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/types/date-time.js`
- **Type:** JavaScript
- **Size:** 250 B

#### DefaultNumberOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/DefaultNumberOption.js`
- **Type:** JavaScript
- **Size:** 505 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-defaultnumberoption
- **Methods:** 1

#### GetStringOrBooleanOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/GetStringOrBooleanOption.js`
- **Type:** JavaScript
- **Size:** 801 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getstringorbooleanoption
- **Methods:** 1

#### IsWellFormedUnitIdentifier
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/IsWellFormedUnitIdentifier.js`
- **Type:** JavaScript
- **Size:** 867 B
- **Capabilities:**
  - This follows https://tc39.es/ecma402/#sec-case-sensitivity-and-case-mapping
  - https://tc39.es/ecma402/#sec-iswellformedunitidentifier
- **Methods:** 2

#### IsWellFormedCurrencyCode
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/IsWellFormedCurrencyCode.js`
- **Type:** JavaScript
- **Size:** 564 B
- **Capabilities:**
  - This follows https://tc39.es/ecma402/#sec-case-sensitivity-and-case-mapping
  - https://tc39.es/ecma402/#sec-iswellformedcurrencycode
- **Methods:** 2

#### GetOptionsObject
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/GetOptionsObject.js`
- **Type:** JavaScript
- **Size:** 340 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getoptionsobject
- **Methods:** 1

#### 262
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/262.js`
- **Type:** JavaScript
- **Size:** 9.78 KB
- **Capabilities:**
  - https://tc39.es/ecma262/#sec-tostring
  - https://tc39.es/ecma262/#sec-tonumber
  - https://tc39.es/ecma262/#sec-tointeger
- **Methods:** 20

#### ToIntlMathematicalValue
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/ToIntlMathematicalValue.js`
- **Type:** JavaScript
- **Size:** 656 B
- **Methods:** 1

#### GetOption
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/GetOption.js`
- **Type:** JavaScript
- **Size:** 922 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-getoption
- **Methods:** 1

#### IsValidTimeZoneName
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/IsValidTimeZoneName.js`
- **Type:** JavaScript
- **Size:** 726 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-isvalidtimezonename
- **Methods:** 1

#### utils
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/utils.js`
- **Type:** JavaScript
- **Size:** 4.05 KB
- **Capabilities:**
  - 7.3.5 CreateDataProperty
- **Methods:** 11

#### IsSanctionedSimpleUnitIdentifier
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/IsSanctionedSimpleUnitIdentifier.js`
- **Type:** JavaScript
- **Size:** 1.63 KB
- **Capabilities:**
  - https://tc39.es/ecma402/#table-sanctioned-simple-unit-identifiers
  - https://tc39.es/ecma402/#sec-issanctionedsimpleunitidentifier
- **Methods:** 3

#### CanonicalizeLocaleList
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/CanonicalizeLocaleList.js`
- **Type:** JavaScript
- **Size:** 220 B
- **Capabilities:**
  - http://ecma-international.org/ecma-402/7.0/index.html#sec-canonicalizelocalelist
- **Methods:** 1

#### CoerceOptionsToObject
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/CoerceOptionsToObject.js`
- **Type:** JavaScript
- **Size:** 291 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-coerceoptionstoobject
- **Methods:** 1

#### CanonicalizeTimeZoneName
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/CanonicalizeTimeZoneName.js`
- **Type:** JavaScript
- **Size:** 578 B
- **Capabilities:**
  - https://tc39.es/ecma402/#sec-canonicalizetimezonename
- **Methods:** 1

#### PartitionPattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/lib/PartitionPattern.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Capabilities:**
  - Partition a pattern into a list of literals and placeholders
- **Methods:** 1

#### PartitionPattern
- **File:** `frontend-tools/node_modules/@formatjs/ecma402-abstract/PartitionPattern.js`
- **Type:** JavaScript
- **Size:** 1.24 KB
- **Capabilities:**
  - Partition a pattern into a list of literals and placeholders
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/proxy-from-env/index.js`
- **Type:** JavaScript
- **Size:** 3.27 KB
- **Capabilities:**
  - Determines whether a given URL should be proxied.
  - Get the value for an environment variable.
- **Methods:** 3

#### semver
- **File:** `frontend-tools/node_modules/semver/semver.js`
- **Type:** JavaScript
- **Size:** 39.86 KB
- **Capabilities:**
  - Test Set
- **Methods:** 20

#### base64js.min
- **File:** `frontend-tools/node_modules/base64-js/base64js.min.js`
- **Type:** JavaScript
- **Size:** 2.14 KB
- **Methods:** 8

#### index
- **File:** `frontend-tools/node_modules/base64-js/index.js`
- **Type:** JavaScript
- **Size:** 3.84 KB
- **Methods:** 7

#### lrucache
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/internal/lrucache.js`
- **Type:** JavaScript
- **Size:** 802 B

#### index
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/index.js`
- **Type:** JavaScript
- **Size:** 2.57 KB

#### range
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/classes/range.js`
- **Type:** JavaScript
- **Size:** 14.63 KB
- **Methods:** 1

#### comparator
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/classes/comparator.js`
- **Type:** JavaScript
- **Size:** 3.55 KB

#### semver
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/classes/semver.js`
- **Type:** JavaScript
- **Size:** 9.26 KB

#### satisfies
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/satisfies.js`
- **Type:** JavaScript
- **Size:** 247 B

#### major
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/major.js`
- **Type:** JavaScript
- **Size:** 136 B

#### minor
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/minor.js`
- **Type:** JavaScript
- **Size:** 136 B

#### compare-build
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/compare-build.js`
- **Type:** JavaScript
- **Size:** 281 B

#### parse
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/parse.js`
- **Type:** JavaScript
- **Size:** 331 B

#### patch
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/patch.js`
- **Type:** JavaScript
- **Size:** 136 B

#### compare
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/compare.js`
- **Type:** JavaScript
- **Size:** 170 B

#### inc
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/inc.js`
- **Type:** JavaScript
- **Size:** 478 B

#### coerce
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/functions/coerce.js`
- **Type:** JavaScript
- **Size:** 1.96 KB

#### valid
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/valid.js`
- **Type:** JavaScript
- **Size:** 326 B

#### max-satisfying
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/max-satisfying.js`
- **Type:** JavaScript
- **Size:** 593 B

#### outside
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/outside.js`
- **Type:** JavaScript
- **Size:** 2.15 KB

#### simplify
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/simplify.js`
- **Type:** JavaScript
- **Size:** 1.32 KB

#### subset
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/subset.js`
- **Type:** JavaScript
- **Size:** 7.35 KB

#### min-satisfying
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/min-satisfying.js`
- **Type:** JavaScript
- **Size:** 591 B

#### to-comparators
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/to-comparators.js`
- **Type:** JavaScript
- **Size:** 282 B

#### intersects
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/intersects.js`
- **Type:** JavaScript
- **Size:** 224 B

#### min-version
- **File:** `frontend-tools/node_modules/sharp/node_modules/semver/ranges/min-version.js`
- **Type:** JavaScript
- **Size:** 1.48 KB

#### composite
- **File:** `frontend-tools/node_modules/sharp/lib/composite.js`
- **Type:** JavaScript
- **Size:** 8.65 KB
- **Capabilities:**
  - Blend modes.
  - Composite image(s) over the processed (resized, extracted etc.) image.
  - Decorate the Sharp prototype with composite-related functions.
- **Methods:** 1

#### resize
- **File:** `frontend-tools/node_modules/sharp/lib/resize.js`
- **Type:** JavaScript
- **Size:** 20.35 KB
- **Capabilities:**
  - Weighting to apply when using contain/cover fit.
  - Position to apply when using contain/cover fit.
  - How to extend the image.
- **Methods:** 6

#### output
- **File:** `frontend-tools/node_modules/sharp/lib/output.js`
- **Type:** JavaScript
- **Size:** 57.26 KB
- **Capabilities:**
  - Write output image data to a file.
  - Write output to a Buffer.
  - Keep all EXIF metadata from the input image in the output image.
- **Methods:** 20

#### channel
- **File:** `frontend-tools/node_modules/sharp/lib/channel.js`
- **Type:** JavaScript
- **Size:** 5.17 KB
- **Capabilities:**
  - Boolean operations for bandbool.
  - Remove alpha channel, if any. This is a no-op if the image does not have an alpha channel.
  - Ensure the output image has an alpha transparency channel.
- **Methods:** 5

#### utility
- **File:** `frontend-tools/node_modules/sharp/lib/utility.js`
- **Type:** JavaScript
- **Size:** 9.5 KB
- **Capabilities:**
  - An Object containing nested boolean values representing the available input and output formats/methods.
  - An Object containing the available interpolators and their proper values
  - An Object containing the version numbers of sharp, libvips
- **Methods:** 6

#### is
- **File:** `frontend-tools/node_modules/sharp/lib/is.js`
- **Type:** JavaScript
- **Size:** 3.14 KB
- **Capabilities:**
  - Is this value defined and not null?
  - Is this value an object?
  - Is this value a plain object?

#### colour
- **File:** `frontend-tools/node_modules/sharp/lib/colour.js`
- **Type:** JavaScript
- **Size:** 4.99 KB
- **Capabilities:**
  - Colourspaces.
  - Tint the image using the provided colour.
  - Convert to 8-bit greyscale; 256 shades of grey.
- **Methods:** 8

#### constructor
- **File:** `frontend-tools/node_modules/sharp/lib/constructor.js`
- **Type:** JavaScript
- **Size:** 16.12 KB
- **Capabilities:**
  - Constructor factory to create an instance of `sharp`, to which further methods are chained.
  - Take a "snapshot" of the Sharp instance, returning a new instance.
  - Export constructor.
- **Methods:** 2

#### operation
- **File:** `frontend-tools/node_modules/sharp/lib/operation.js`
- **Type:** JavaScript
- **Size:** 31.28 KB
- **Capabilities:**
  - How accurate an operation should be.
  - Rotate the output image by either an explicit angle
  - Mirror the image vertically (up-down) about the x-axis.
- **Methods:** 20

#### input
- **File:** `frontend-tools/node_modules/sharp/lib/input.js`
- **Type:** JavaScript
- **Size:** 25.25 KB
- **Capabilities:**
  - Justication alignment
  - Extract input options, if any, from an object.
  - Create Object containing input and input-related options.
- **Methods:** 8

#### libvips
- **File:** `frontend-tools/node_modules/sharp/lib/libvips.js`
- **Type:** JavaScript
- **Size:** 5.77 KB

#### index
- **File:** `frontend-tools/node_modules/color-string/index.js`
- **Type:** JavaScript
- **Size:** 5.58 KB
- **Methods:** 2

#### validation
- **File:** `frontend-tools/node_modules/ws/lib/validation.js`
- **Type:** JavaScript
- **Size:** 3.81 KB
- **Capabilities:**
  - Checks if a status code is allowed in a close frame.
  - Checks if a given buffer contains only correct UTF-8.
  - Determines whether a value is a `Blob`.
- **Methods:** 3

#### permessage-deflate
- **File:** `frontend-tools/node_modules/ws/lib/permessage-deflate.js`
- **Type:** JavaScript
- **Size:** 14.17 KB
- **Capabilities:**
  - permessage-deflate implementation.
  - Creates a PerMessageDeflate instance.
  - Create an extension negotiation offer.
- **Methods:** 3

#### subprotocol
- **File:** `frontend-tools/node_modules/ws/lib/subprotocol.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Parses the `Sec-WebSocket-Protocol` header into a set of subprotocol names.
- **Methods:** 1

#### limiter
- **File:** `frontend-tools/node_modules/ws/lib/limiter.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - A very simple job queue with adjustable concurrency. Adapted from
  - Creates a new `Limiter`.
  - Adds a job to the queue.

#### websocket-server
- **File:** `frontend-tools/node_modules/ws/lib/websocket-server.js`
- **Type:** JavaScript
- **Size:** 16.01 KB
- **Capabilities:**
  - Class representing a WebSocket server.
  - Create a `WebSocketServer` instance.
  - Returns the bound address, the address family name, and port of the server
- **Methods:** 7

#### event-target
- **File:** `frontend-tools/node_modules/ws/lib/event-target.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Class representing an event.
  - Create a new `Event`.
  - Class representing a close event.
- **Methods:** 5

#### extension
- **File:** `frontend-tools/node_modules/ws/lib/extension.js`
- **Type:** JavaScript
- **Size:** 6.04 KB
- **Capabilities:**
  - Adds an offer to the map of extension offers or a parameter to the map of
  - Parses the `Sec-WebSocket-Extensions` header into an object.
  - Builds the `Sec-WebSocket-Extensions` header field value.
- **Methods:** 3

#### sender
- **File:** `frontend-tools/node_modules/ws/lib/sender.js`
- **Type:** JavaScript
- **Size:** 16.32 KB
- **Capabilities:**
  - HyBi Sender implementation.
  - Creates a Sender instance.
  - Frames a piece of data according to the HyBi WebSocket protocol.
- **Methods:** 3

#### websocket
- **File:** `frontend-tools/node_modules/ws/lib/websocket.js`
- **Type:** JavaScript
- **Size:** 35.61 KB
- **Capabilities:**
  - Class representing a WebSocket.
  - Create a new `WebSocket`.
  - For historical reasons, the custom "nodebuffer" type is used by the default
- **Methods:** 20

#### stream
- **File:** `frontend-tools/node_modules/ws/lib/stream.js`
- **Type:** JavaScript
- **Size:** 4.11 KB
- **Capabilities:**
  - Emits the `'close'` event on a stream.
  - The listener of the `'end'` event.
  - The listener of the `'error'` event.
- **Methods:** 9

#### buffer-util
- **File:** `frontend-tools/node_modules/ws/lib/buffer-util.js`
- **Type:** JavaScript
- **Size:** 2.98 KB
- **Capabilities:**
  - Merges an array of buffers into a new buffer.
  - Masks a buffer using the given mask.
  - Unmasks a buffer using the given mask.
- **Methods:** 5

#### receiver
- **File:** `frontend-tools/node_modules/ws/lib/receiver.js`
- **Type:** JavaScript
- **Size:** 16.07 KB
- **Capabilities:**
  - HyBi Receiver implementation.
  - Creates a Receiver instance.
  - Implements `Writable.prototype._write()`.

#### browser
- **File:** `frontend-tools/node_modules/ws/browser.js`
- **Type:** JavaScript
- **Size:** 176 B

#### index
- **File:** `frontend-tools/node_modules/write-file-atomic/index.js`
- **Type:** JavaScript
- **Size:** 6.65 KB
- **Methods:** 8

#### index.umd.min
- **File:** `frontend-tools/node_modules/tldts-icann/dist/index.umd.min.js`
- **Type:** JavaScript
- **Size:** 71.48 KB
- **Methods:** 6

#### index
- **File:** `frontend-tools/node_modules/tldts-icann/dist/es6/index.js`
- **Type:** JavaScript
- **Size:** 1.47 KB
- **Methods:** 6

#### suffix-trie
- **File:** `frontend-tools/node_modules/tldts-icann/dist/es6/src/suffix-trie.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Capabilities:**
  - Lookup parts of domain in Trie
  - Check if `hostname` has a valid public suffix in `trie`.
- **Methods:** 2

#### trie
- **File:** `frontend-tools/node_modules/tldts-icann/dist/es6/src/data/trie.js`
- **Type:** JavaScript
- **Size:** 104.42 KB

#### index.cjs.min
- **File:** `frontend-tools/node_modules/tldts-icann/dist/index.cjs.min.js`
- **Type:** JavaScript
- **Size:** 71.29 KB
- **Methods:** 6

#### index
- **File:** `frontend-tools/node_modules/tldts-icann/dist/cjs/index.js`
- **Type:** JavaScript
- **Size:** 127.01 KB
- **Capabilities:**
  - Check if `vhost` is a valid suffix of `hostname` (top-domain)
  - Given a hostname and its public suffix, extract the general domain.
  - Detects the domain based on rules and upon and a host string
- **Methods:** 20

#### suffix-trie
- **File:** `frontend-tools/node_modules/tldts-icann/dist/cjs/src/suffix-trie.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - Lookup parts of domain in Trie
  - Check if `hostname` has a valid public suffix in `trie`.
- **Methods:** 2

#### trie
- **File:** `frontend-tools/node_modules/tldts-icann/dist/cjs/src/data/trie.js`
- **Type:** JavaScript
- **Size:** 104.52 KB

#### index.esm.min
- **File:** `frontend-tools/node_modules/tldts-icann/dist/index.esm.min.js`
- **Type:** JavaScript
- **Size:** 71.27 KB
- **Methods:** 12

#### once
- **File:** `frontend-tools/node_modules/once/once.js`
- **Type:** JavaScript
- **Size:** 935 B
- **Methods:** 3

#### libraries
- **File:** `frontend-tools/node_modules/js-library-detector/library/libraries.js`
- **Type:** JavaScript
- **Size:** 60.47 KB
- **Capabilities:**
  - Test
- **Methods:** 7

#### mitt.umd
- **File:** `frontend-tools/node_modules/mitt/dist/mitt.umd.js`
- **Type:** JavaScript
- **Size:** 520 B

#### mitt
- **File:** `frontend-tools/node_modules/mitt/dist/mitt.js`
- **Type:** JavaScript
- **Size:** 349 B

#### smartbuffer
- **File:** `frontend-tools/node_modules/smart-buffer/build/smartbuffer.js`
- **Type:** JavaScript
- **Size:** 43.46 KB
- **Capabilities:**
  - Creates a new SmartBuffer instance.
  - Creates a new SmartBuffer instance with the provided internal Buffer size and optional encoding.
  - Creates a new SmartBuffer instance with the provided Buffer and optional encoding.
- **Methods:** 2

#### utils
- **File:** `frontend-tools/node_modules/smart-buffer/build/utils.js`
- **Type:** JavaScript
- **Size:** 4.17 KB
- **Capabilities:**
  - Error strings
  - Checks if a given encoding is a valid Buffer encoding. (Throws an exception if check fails)
  - Checks if a given number is a finite integer. (Throws an exception if check fails)
- **Methods:** 8

#### cli
- **File:** `frontend-tools/node_modules/extract-zip/cli.js`
- **Type:** JavaScript
- **Size:** 393 B

#### index
- **File:** `frontend-tools/node_modules/extract-zip/index.js`
- **Type:** JavaScript
- **Size:** 4.84 KB

#### Deferred
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/Deferred.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### unitConversions
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/unitConversions.js`
- **Type:** JavaScript
- **Size:** 935 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### Buffer
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/Buffer.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### assert
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/assert.js`
- **Type:** JavaScript
- **Size:** 931 B
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 1

#### EventEmitter
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 2.46 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Like `on` but the listener will only be fired once and then it will be removed.
  - Emits an event and call any associated listeners.
- **Methods:** 1

#### log
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/log.js`
- **Type:** JavaScript
- **Size:** 1.05 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### uuid
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/uuid.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Generates a random v4 UUID, as specified in RFC4122.
- **Methods:** 1

#### ProcessingQueue
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/ProcessingQueue.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### DefaultMap
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/DefaultMap.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - A subclass of Map whose functionality is almost the same as its parent

#### IdWrapper
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/IdWrapper.js`
- **Type:** JavaScript
- **Size:** 1.02 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Creates an object with a positive unique incrementing id.

#### WebsocketTransport
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/WebsocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.29 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### UrlPattern
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/UrlPattern.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### Mutex
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/utils/Mutex.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Use Mutex class to coordinate local concurrent operations.
- **Methods:** 1

#### mapperTabPage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiTab/mapperTabPage.js`
- **Type:** JavaScript
- **Size:** 4.22 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - The following piece of HTML should be added to the `debug` element:
- **Methods:** 4

#### BidiParser
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiTab/BidiParser.js`
- **Type:** JavaScript
- **Size:** 5.5 KB

#### bidiTab
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiTab/bidiTab.js`
- **Type:** JavaScript
- **Size:** 2.54 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - A CdpTransport implementation that uses the window.cdp bindings
  - Launches the BiDi mapper instance.
- **Methods:** 2

#### Transport
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiTab/Transport.js`
- **Type:** JavaScript
- **Size:** 4.82 KB

#### BidiNoOpParser
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiNoOpParser.js`
- **Type:** JavaScript
- **Size:** 3.65 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### OutgoingMessage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/OutgoingMessage.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### BrowsingContextProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextProcessor.js`
- **Type:** JavaScript
- **Size:** 14.23 KB
- **Capabilities:**
  - This method is called for each CDP session, since this class is responsible

#### BrowsingContextImpl
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextImpl.js`
- **Type:** JavaScript
- **Size:** 31.9 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - The ID of the parent browsing context.
  - Returns true if this is a top-level context.
- **Methods:** 6

#### BrowsingContextStorage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/BrowsingContextStorage.js`
- **Type:** JavaScript
- **Size:** 2.88 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### CdpTarget
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/context/CdpTarget.js`
- **Type:** JavaScript
- **Size:** 5.31 KB
- **Capabilities:**
  - Enables all the required CDP domains and unblocks the target.
  - All the ProxyChannels from all the preload scripts of the given

#### logHelper
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/log/logHelper.js`
- **Type:** JavaScript
- **Size:** 5.67 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 5

#### LogManager
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/log/LogManager.js`
- **Type:** JavaScript
- **Size:** 5.81 KB
- **Capabilities:**
  - Try the best to get the exception text.
- **Methods:** 2

#### RealmStorage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/RealmStorage.js`
- **Type:** JavaScript
- **Size:** 3 KB

#### DedicatedWorkerRealm
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/DedicatedWorkerRealm.js`
- **Type:** JavaScript
- **Size:** 1.86 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### PreloadScript
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/PreloadScript.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - BiDi IDs are generated by the server and are unique within contexts.
  - String to be evaluated. Wraps user-provided function so that the following
  - Adds the script to the given CDP targets by calling the
- **Methods:** 2

#### SharedId
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/SharedId.js`
- **Type:** JavaScript
- **Size:** 2.64 KB
- **Methods:** 3

#### WindowRealm
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/WindowRealm.js`
- **Type:** JavaScript
- **Size:** 6.62 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### ScriptProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/ScriptProcessor.js`
- **Type:** JavaScript
- **Size:** 4.81 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### PreloadScriptStorage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/PreloadScriptStorage.js`
- **Type:** JavaScript
- **Size:** 1.49 KB
- **Capabilities:**
  - Container class for preload scripts.

#### ChannelProxy
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/ChannelProxy.js`
- **Type:** JavaScript
- **Size:** 9.96 KB
- **Capabilities:**
  - Used to send messages from realm to BiDi user.
  - Creates a channel proxy in the given realm, initialises listener and
  - Evaluation string which creates a ChannelProxy object on the client side.

#### Realm
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/script/Realm.js`
- **Type:** JavaScript
- **Size:** 20.52 KB
- **Capabilities:**
  - Relies on the CDP to implement proper BiDi serialization, except:
  - Serializes a given CDP object into BiDi, keeping references in the
  - Gets the string representation of an object. This is equivalent to
- **Methods:** 1

#### BrowserProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/browser/BrowserProcessor.js`
- **Type:** JavaScript
- **Size:** 2.7 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### EventManager
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/session/EventManager.js`
- **Type:** JavaScript
- **Size:** 7.79 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Maps event name to a desired buffer length.
  - Maps event name to a set of contexts where this event already happened.

#### events
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/session/events.js`
- **Type:** JavaScript
- **Size:** 1.47 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
  - Returns true if the given event is a CDP event.
  - Asserts that the given event is known to BiDi or BiDi+, or throws otherwise.
- **Methods:** 2

#### SubscriptionManager
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/session/SubscriptionManager.js`
- **Type:** JavaScript
- **Size:** 9.36 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
  - Returns the cartesian product of the given arrays.
  - Unsubscribes atomically from all events in the given contexts and channel.
- **Methods:** 3

#### SessionProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/session/SessionProcessor.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### CdpProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/cdp/CdpProcessor.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputProcessor.js`
- **Type:** JavaScript
- **Size:** 7.07 KB
- **Methods:** 2

#### keyUtils
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/keyUtils.js`
- **Type:** JavaScript
- **Size:** 11.4 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 3

#### InputState
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputState.js`
- **Type:** JavaScript
- **Size:** 3.65 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputStateManager
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputStateManager.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### ActionDispatcher
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/ActionDispatcher.js`
- **Type:** JavaScript
- **Size:** 26.23 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.
- **Methods:** 4

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### InputSource
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/input/InputSource.js`
- **Type:** JavaScript
- **Size:** 4.59 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### NetworkManager
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 4.55 KB
- **Capabilities:**
  - Gets the network request with the given ID, if any.

#### NetworkUtils
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkUtils.js`
- **Type:** JavaScript
- **Size:** 7.73 KB
- **Capabilities:**
  - Converts from CDP Network domain cookie to BiDi network cookie.
  - Converts from BiDi set network cookie params to CDP Network domain cookie.
- **Methods:** 10

#### NetworkStorage
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkStorage.js`
- **Type:** JavaScript
- **Size:** 7.56 KB
- **Capabilities:**
  - A map from network request ID to Network Request objects.
  - Adds the given entry to the intercept map.
  - Removes the given intercept from the intercept map.

#### NetworkRequest
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkRequest.js`
- **Type:** JavaScript
- **Size:** 21.07 KB
- **Capabilities:**
  - Each network request has an associated request id, which is a string
  - Indicates the network intercept phase, if the request is currently blocked.

#### NetworkProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/network/NetworkProcessor.js`
- **Type:** JavaScript
- **Size:** 10.6 KB
- **Capabilities:**
  - Either enables or disables the Fetch domain.
  - Returns the blocked request associated with the given network ID.
  - Attempts to parse the given url.

#### PermissionsProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/permissions/PermissionsProcessor.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.

#### StorageProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/domains/storage/StorageProcessor.js`
- **Type:** JavaScript
- **Size:** 5.99 KB
- **Capabilities:**
  - Responsible for handling the `storage` domain.

#### BidiMapper
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiMapper.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.

#### BidiServer
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/BidiServer.js`
- **Type:** JavaScript
- **Size:** 5.06 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
  - Creates and starts BiDi Mapper instance.
  - Sends BiDi message.

#### CommandProcessor
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiMapper/CommandProcessor.js`
- **Type:** JavaScript
- **Size:** 13.03 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### protocol-parser
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol-parser/protocol-parser.js`
- **Type:** JavaScript
- **Size:** 13.11 KB
- **Capabilities:**
  - Copyright 2022 Google LLC.
- **Methods:** 20

#### webdriver-bidi-permissions
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol-parser/generated/webdriver-bidi-permissions.js`
- **Type:** JavaScript
- **Size:** 2.41 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.
  - THIS FILE IS AUTOGENERATED. Run `npm run bidi-types` to regenerate.

#### webdriver-bidi
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol-parser/generated/webdriver-bidi.js`
- **Type:** JavaScript
- **Size:** 96.7 KB
- **Capabilities:**
  - Copyright 2024 Google LLC.
  - THIS FILE IS AUTOGENERATED. Run `npm run bidi-types` to regenerate.

#### CdpConnection
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/cdp/CdpConnection.js`
- **Type:** JavaScript
- **Size:** 4.84 KB
- **Capabilities:**
  - Represents a high-level CDP connection to the browser backend.
  - Gets a CdpClient instance attached to the given session ID,
  - Creates a new CdpClient instance for the given session ID.

#### CdpClient
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/cdp/CdpClient.js`
- **Type:** JavaScript
- **Size:** 1.58 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### ErrorResponse
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol/ErrorResponse.js`
- **Type:** JavaScript
- **Size:** 7.17 KB

#### chromium-bidi
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol/chromium-bidi.js`
- **Type:** JavaScript
- **Size:** 3.93 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### protocol
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/protocol/protocol.js`
- **Type:** JavaScript
- **Size:** 2.28 KB
- **Capabilities:**
  - Copyright 2023 Google LLC.

#### SimpleTransport
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/SimpleTransport.js`
- **Type:** JavaScript
- **Size:** 1.39 KB
- **Capabilities:**
  - Implements simple transport that allows sending string messages via

#### BrowserInstance
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/BrowserInstance.js`
- **Type:** JavaScript
- **Size:** 5.46 KB
- **Capabilities:**
  - BrowserProcess is responsible for running the browser and BiDi Mapper within

#### index
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/index.js`
- **Type:** JavaScript
- **Size:** 2.49 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### WebSocketServer
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/WebSocketServer.js`
- **Type:** JavaScript
- **Size:** 15.35 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### reader
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/reader.js`
- **Type:** JavaScript
- **Size:** 1.21 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 1

#### MapperCdpConnection
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/cjs/bidiServer/MapperCdpConnection.js`
- **Type:** JavaScript
- **Size:** 5.7 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.

#### mapperTab
- **File:** `frontend-tools/node_modules/chromium-bidi/lib/iife/mapperTab.js`
- **Type:** JavaScript
- **Size:** 265.88 KB
- **Capabilities:**
  - Copyright 2021 Google LLC.
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/pac-proxy-agent/node_modules/https-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.28 KB
- **Capabilities:**
  - The `HttpsProxyAgent` implements an HTTP Agent subclass that connects to
  - Called when the node-core HTTP client library is creating a
- **Methods:** 2

#### parse-proxy-response
- **File:** `frontend-tools/node_modules/pac-proxy-agent/node_modules/https-proxy-agent/dist/parse-proxy-response.js`
- **Type:** JavaScript
- **Size:** 3.82 KB
- **Methods:** 6

#### helpers
- **File:** `frontend-tools/node_modules/pac-proxy-agent/node_modules/agent-base/dist/helpers.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/pac-proxy-agent/node_modules/agent-base/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Determine whether this is an `http` or `https` request.

#### index
- **File:** `frontend-tools/node_modules/pac-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 9.7 KB
- **Capabilities:**
  - The `PacProxyAgent` class.
  - Loads the PAC proxy file from the source if necessary, and returns
  - Loads the contents of the PAC proxy file.
- **Methods:** 1

#### argparse
- **File:** `frontend-tools/node_modules/argparse/argparse.js`
- **Type:** JavaScript
- **Size:** 126.67 KB
- **Methods:** 20

#### sub
- **File:** `frontend-tools/node_modules/argparse/lib/sub.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Methods:** 1

#### textwrap
- **File:** `frontend-tools/node_modules/argparse/lib/textwrap.js`
- **Type:** JavaScript
- **Size:** 16.98 KB
- **Methods:** 3

#### unbzip2-stream.min
- **File:** `frontend-tools/node_modules/unbzip2-stream/dist/unbzip2-stream.min.js`
- **Type:** JavaScript
- **Size:** 109.22 KB
- **Capabilities:**
  - Checked
  - Check Offset
  - Check Int
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/unbzip2-stream/index.js`
- **Type:** JavaScript
- **Size:** 2.87 KB
- **Methods:** 5

#### bit_iterator
- **File:** `frontend-tools/node_modules/unbzip2-stream/lib/bit_iterator.js`
- **Type:** JavaScript
- **Size:** 1.11 KB
- **Methods:** 2

#### bzip2
- **File:** `frontend-tools/node_modules/unbzip2-stream/lib/bzip2.js`
- **Type:** JavaScript
- **Size:** 12.89 KB
- **Methods:** 2

#### tokenize-arg-string
- **File:** `frontend-tools/node_modules/yargs-parser/build/lib/tokenize-arg-string.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/yargs-parser/build/lib/index.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Methods:** 1

#### yargs-parser-types
- **File:** `frontend-tools/node_modules/yargs-parser/build/lib/yargs-parser-types.js`
- **Type:** JavaScript
- **Size:** 425 B

#### string-utils
- **File:** `frontend-tools/node_modules/yargs-parser/build/lib/string-utils.js`
- **Type:** JavaScript
- **Size:** 2.04 KB
- **Methods:** 3

#### yargs-parser
- **File:** `frontend-tools/node_modules/yargs-parser/build/lib/yargs-parser.js`
- **Type:** JavaScript
- **Size:** 45.73 KB
- **Capabilities:**
  - Check All Aliases
  - Check Configuration
- **Methods:** 20

#### browser
- **File:** `frontend-tools/node_modules/yargs-parser/browser.js`
- **Type:** JavaScript
- **Size:** 1016 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/ms/index.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Parse or format the given `val`.
  - Parse the given `str` and return milliseconds.
  - Short format for `ms`.
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/end-of-stream/index.js`
- **Type:** JavaScript
- **Size:** 2.67 KB

#### semver
- **File:** `frontend-tools/node_modules/make-dir/node_modules/semver/bin/semver.js`
- **Type:** JavaScript
- **Size:** 4.61 KB
- **Methods:** 5

#### semver
- **File:** `frontend-tools/node_modules/make-dir/node_modules/semver/semver.js`
- **Type:** JavaScript
- **Size:** 43.25 KB
- **Capabilities:**
  - Test Set
- **Methods:** 20

#### form_data
- **File:** `frontend-tools/node_modules/form-data/lib/form_data.js`
- **Type:** JavaScript
- **Size:** 14.26 KB
- **Capabilities:**
  - Create readable "multipart/form-data" streams.
- **Methods:** 1

#### populate
- **File:** `frontend-tools/node_modules/form-data/lib/populate.js`
- **Type:** JavaScript
- **Size:** 232 B

#### index
- **File:** `frontend-tools/node_modules/dot-prop/index.js`
- **Type:** JavaScript
- **Size:** 2.71 KB
- **Methods:** 1

#### combined_stream
- **File:** `frontend-tools/node_modules/combined-stream/lib/combined_stream.js`
- **Type:** JavaScript
- **Size:** 4.58 KB
- **Methods:** 1

#### doc
- **File:** `frontend-tools/node_modules/prettier/doc.js`
- **Type:** JavaScript
- **Size:** 52.76 KB
- **Methods:** 20

#### standalone
- **File:** `frontend-tools/node_modules/prettier/standalone.js`
- **Type:** JavaScript
- **Size:** 76.37 KB
- **Methods:** 20

#### angular
- **File:** `frontend-tools/node_modules/prettier/plugins/angular.js`
- **Type:** JavaScript
- **Size:** 87.73 KB
- **Methods:** 20

#### graphql
- **File:** `frontend-tools/node_modules/prettier/plugins/graphql.js`
- **Type:** JavaScript
- **Size:** 43.15 KB
- **Methods:** 20

#### typescript
- **File:** `frontend-tools/node_modules/prettier/plugins/typescript.js`
- **Type:** JavaScript
- **Size:** 873.16 KB
- **Methods:** 20

#### markdown
- **File:** `frontend-tools/node_modules/prettier/plugins/markdown.js`
- **Type:** JavaScript
- **Size:** 146.87 KB
- **Methods:** 20

#### flow
- **File:** `frontend-tools/node_modules/prettier/plugins/flow.js`
- **Type:** JavaScript
- **Size:** 670.48 KB
- **Methods:** 20

#### postcss
- **File:** `frontend-tools/node_modules/prettier/plugins/postcss.js`
- **Type:** JavaScript
- **Size:** 150.97 KB
- **Methods:** 20

#### html
- **File:** `frontend-tools/node_modules/prettier/plugins/html.js`
- **Type:** JavaScript
- **Size:** 150.06 KB
- **Methods:** 20

#### babel
- **File:** `frontend-tools/node_modules/prettier/plugins/babel.js`
- **Type:** JavaScript
- **Size:** 319.1 KB
- **Methods:** 20

#### glimmer
- **File:** `frontend-tools/node_modules/prettier/plugins/glimmer.js`
- **Type:** JavaScript
- **Size:** 134.04 KB
- **Methods:** 20

#### acorn
- **File:** `frontend-tools/node_modules/prettier/plugins/acorn.js`
- **Type:** JavaScript
- **Size:** 152.07 KB
- **Methods:** 20

#### meriyah
- **File:** `frontend-tools/node_modules/prettier/plugins/meriyah.js`
- **Type:** JavaScript
- **Size:** 126.44 KB
- **Methods:** 20

#### yaml
- **File:** `frontend-tools/node_modules/prettier/plugins/yaml.js`
- **Type:** JavaScript
- **Size:** 119.69 KB
- **Methods:** 20

#### estree
- **File:** `frontend-tools/node_modules/prettier/plugins/estree.js`
- **Type:** JavaScript
- **Size:** 196.49 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/es-define-property/test/index.js`
- **Type:** JavaScript
- **Size:** 1.27 KB

#### index
- **File:** `frontend-tools/node_modules/wrap-ansi/index.js`
- **Type:** JavaScript
- **Size:** 5.64 KB

#### index
- **File:** `frontend-tools/node_modules/ieee754/index.js`
- **Type:** JavaScript
- **Size:** 2.1 KB

#### index
- **File:** `frontend-tools/node_modules/cookie/index.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Capabilities:**
  - Module exports.
  - Module variables.
  - RegExp to match field-content in RFC 7230 sec 3.2
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/enquirer/index.js`
- **Type:** JavaScript
- **Size:** 6.2 KB
- **Capabilities:**
  - Create an instance of `Enquirer`.
  - Register a custom prompt type.
  - Prompt function that takes a "question" object or array of question objects,
- **Methods:** 1

#### timer
- **File:** `frontend-tools/node_modules/enquirer/lib/timer.js`
- **Type:** JavaScript
- **Size:** 902 B
- **Methods:** 1

#### state
- **File:** `frontend-tools/node_modules/enquirer/lib/state.js`
- **Type:** JavaScript
- **Size:** 1.63 KB

#### number
- **File:** `frontend-tools/node_modules/enquirer/lib/types/number.js`
- **Type:** JavaScript
- **Size:** 2.24 KB

#### auth
- **File:** `frontend-tools/node_modules/enquirer/lib/types/auth.js`
- **Type:** JavaScript
- **Size:** 606 B

#### array
- **File:** `frontend-tools/node_modules/enquirer/lib/types/array.js`
- **Type:** JavaScript
- **Size:** 17.32 KB
- **Methods:** 1

#### string
- **File:** `frontend-tools/node_modules/enquirer/lib/types/string.js`
- **Type:** JavaScript
- **Size:** 4.96 KB

#### boolean
- **File:** `frontend-tools/node_modules/enquirer/lib/types/boolean.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### prompt
- **File:** `frontend-tools/node_modules/enquirer/lib/prompt.js`
- **Type:** JavaScript
- **Size:** 12.29 KB
- **Capabilities:**
  - Base class for creating a new Prompt.
- **Methods:** 2

#### list
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/list.js`
- **Type:** JavaScript
- **Size:** 811 B

#### confirm
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/confirm.js`
- **Type:** JavaScript
- **Size:** 275 B

#### basicauth
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/basicauth.js`
- **Type:** JavaScript
- **Size:** 966 B
- **Methods:** 1

#### sort
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/sort.js`
- **Type:** JavaScript
- **Size:** 897 B

#### form
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/form.js`
- **Type:** JavaScript
- **Size:** 4.92 KB

#### toggle
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/toggle.js`
- **Type:** JavaScript
- **Size:** 2.19 KB

#### multiselect
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/multiselect.js`
- **Type:** JavaScript
- **Size:** 192 B

#### invisible
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/invisible.js`
- **Type:** JavaScript
- **Size:** 179 B

#### password
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/password.js`
- **Type:** JavaScript
- **Size:** 432 B

#### select
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/select.js`
- **Type:** JavaScript
- **Size:** 3.84 KB

#### autocomplete
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/autocomplete.js`
- **Type:** JavaScript
- **Size:** 2.86 KB

#### quiz
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/quiz.js`
- **Type:** JavaScript
- **Size:** 1 KB

#### scale
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/scale.js`
- **Type:** JavaScript
- **Size:** 6.68 KB
- **Capabilities:**
  - Render the scale "Key". Something like:
  - Render the heading row for the scale.
  - Render a scale indicator => ◯ or ◉ by default

#### snippet
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/snippet.js`
- **Type:** JavaScript
- **Size:** 4.42 KB

#### input
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/input.js`
- **Type:** JavaScript
- **Size:** 1.25 KB

#### survey
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/survey.js`
- **Type:** JavaScript
- **Size:** 4.35 KB
- **Methods:** 1

#### editable
- **File:** `frontend-tools/node_modules/enquirer/lib/prompts/editable.js`
- **Type:** JavaScript
- **Size:** 3.1 KB

#### queue
- **File:** `frontend-tools/node_modules/enquirer/lib/queue.js`
- **Type:** JavaScript
- **Size:** 565 B

#### utils
- **File:** `frontend-tools/node_modules/enquirer/lib/utils.js`
- **Type:** JavaScript
- **Size:** 7.19 KB
- **Capabilities:**
  - Set a value on the given object.
  - Get a value from the given object.

#### interpolate
- **File:** `frontend-tools/node_modules/enquirer/lib/interpolate.js`
- **Type:** JavaScript
- **Size:** 6.64 KB
- **Capabilities:**
  - This file contains the interpolation and rendering logic for
- **Methods:** 1

#### keypress
- **File:** `frontend-tools/node_modules/enquirer/lib/keypress.js`
- **Type:** JavaScript
- **Size:** 5.54 KB
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/degenerator/dist/index.js`
- **Type:** JavaScript
- **Size:** 884 B

#### compile
- **File:** `frontend-tools/node_modules/degenerator/dist/compile.js`
- **Type:** JavaScript
- **Size:** 3.98 KB
- **Methods:** 3

#### degenerator
- **File:** `frontend-tools/node_modules/degenerator/dist/degenerator.js`
- **Type:** JavaScript
- **Size:** 6 KB
- **Capabilities:**
  - Compiles sync JavaScript code into JavaScript with async Functions.
  - Returns `true` if `node` has a matching name to one of the entries in the
  - Check Names
- **Methods:** 8

#### decoder
- **File:** `frontend-tools/node_modules/jpeg-js/lib/decoder.js`
- **Type:** JavaScript
- **Size:** 39.64 KB
- **Capabilities:**
  - Decode Scan
- **Methods:** 20

#### encoder
- **File:** `frontend-tools/node_modules/jpeg-js/lib/encoder.js`
- **Type:** JavaScript
- **Size:** 21.6 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/bare-fs/index.js`
- **Type:** JavaScript
- **Size:** 47.12 KB
- **Capabilities:**
  - Create Write Stream
- **Methods:** 20

#### promises
- **File:** `frontend-tools/node_modules/bare-fs/promises.js`
- **Type:** JavaScript
- **Size:** 1.82 KB
- **Methods:** 1

#### errors
- **File:** `frontend-tools/node_modules/bare-fs/lib/errors.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/node-fetch/lib/index.js`
- **Type:** JavaScript
- **Size:** 44.7 KB
- **Capabilities:**
  - fetch-error.js
  - Create FetchError instance
  - Decode response as ArrayBuffer
- **Methods:** 20

#### index.es
- **File:** `frontend-tools/node_modules/node-fetch/lib/index.es.js`
- **Type:** JavaScript
- **Size:** 44.28 KB
- **Capabilities:**
  - fetch-error.js
  - Create FetchError instance
  - Decode response as ArrayBuffer
- **Methods:** 20

#### browser
- **File:** `frontend-tools/node_modules/node-fetch/browser.js`
- **Type:** JavaScript
- **Size:** 781 B

#### index
- **File:** `frontend-tools/node_modules/path-type/index.js`
- **Type:** JavaScript
- **Size:** 1.15 KB
- **Methods:** 2

#### axe
- **File:** `frontend-tools/node_modules/axe-core/axe.js`
- **Type:** JavaScript
- **Size:** 1.2 MB
- **Capabilities:**
  - _check Private Redeclaration
  - _class Call Check
  - Check Instance
- **Methods:** 20

#### axe.min
- **File:** `frontend-tools/node_modules/axe-core/axe.min.js`
- **Type:** JavaScript
- **Size:** 546.7 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/parent-module/index.js`
- **Type:** JavaScript
- **Size:** 641 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/buffer-crc32/index.js`
- **Type:** JavaScript
- **Size:** 4.41 KB
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/has-tostringtag/index.js`
- **Type:** JavaScript
- **Size:** 196 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/has-tostringtag/test/index.js`
- **Type:** JavaScript
- **Size:** 679 B

#### core-js
- **File:** `frontend-tools/node_modules/has-tostringtag/test/shams/core-js.js`
- **Type:** JavaScript
- **Size:** 935 B

#### get-own-property-symbols
- **File:** `frontend-tools/node_modules/has-tostringtag/test/shams/get-own-property-symbols.js`
- **Type:** JavaScript
- **Size:** 828 B

#### shams
- **File:** `frontend-tools/node_modules/has-tostringtag/shams.js`
- **Type:** JavaScript
- **Size:** 189 B
- **Methods:** 1

#### image-ssim
- **File:** `frontend-tools/node_modules/image-ssim/dist/image-ssim.js`
- **Type:** JavaScript
- **Size:** 7.41 KB
- **Capabilities:**
  - - Original TypeScript implementation:
  - Grey = 1, GreyAlpha = 2, RGB = 3, RGBAlpha = 4
  - Entry point.
- **Methods:** 7

#### image-ssim.min
- **File:** `frontend-tools/node_modules/image-ssim/dist/image-ssim.min.js`
- **Type:** JavaScript
- **Size:** 2.4 KB
- **Methods:** 6

#### gulpfile
- **File:** `frontend-tools/node_modules/image-ssim/gulpfile.js`
- **Type:** JavaScript
- **Size:** 551 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/image-ssim/index.js`
- **Type:** JavaScript
- **Size:** 6.56 KB
- **Capabilities:**
  - - Original TypeScript implementation:
  - Grey = 1, GreyAlpha = 2, RGB = 3, RGBAlpha = 4
  - Entry point.
- **Methods:** 5

#### gulpfile
- **File:** `frontend-tools/node_modules/estraverse/gulpfile.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Capabilities:**
  - Bumping version number and tagging the repository with it.
- **Methods:** 1

#### estraverse
- **File:** `frontend-tools/node_modules/estraverse/estraverse.js`
- **Type:** JavaScript
- **Size:** 26.33 KB
- **Capabilities:**
  - __execute
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/pump/index.js`
- **Type:** JavaScript
- **Size:** 2.24 KB

#### ast
- **File:** `frontend-tools/node_modules/esutils/lib/ast.js`
- **Type:** JavaScript
- **Size:** 4.62 KB
- **Methods:** 6

#### code
- **File:** `frontend-tools/node_modules/esutils/lib/code.js`
- **Type:** JavaScript
- **Size:** 28.92 KB
- **Methods:** 10

#### utils
- **File:** `frontend-tools/node_modules/esutils/lib/utils.js`
- **Type:** JavaScript
- **Size:** 1.49 KB

#### keyword
- **File:** `frontend-tools/node_modules/esutils/lib/keyword.js`
- **Type:** JavaScript
- **Size:** 5.48 KB
- **Methods:** 11

#### lifetime
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/lifetime.js`
- **Type:** JavaScript
- **Size:** 6.57 KB
- **Capabilities:**
  - A lifetime prevents access to a value after the lifetime has been
  - When the Lifetime is disposed, it will call `disposer(_value)`. Use the
  - The value this Lifetime protects. You must never retain the value - it
- **Methods:** 3

#### types
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/types.js`
- **Type:** JavaScript
- **Size:** 1.68 KB
- **Capabilities:**
  - Work in progress.
- **Methods:** 2

#### module
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/module.js`
- **Type:** JavaScript
- **Size:** 12.44 KB
- **Capabilities:**
  - We use static functions per module to dispatch runtime or context calls from
  - Process RuntimeOptions and apply them to a QuickJSRuntime.
  - Process ModuleEvalOptions and apply them to a QuickJSRuntime.
- **Methods:** 4

#### memory
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/memory.js`
- **Type:** JavaScript
- **Size:** 1.67 KB

#### vm-interface
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/vm-interface.js`
- **Type:** JavaScript
- **Size:** 384 B
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/index.js`
- **Type:** JavaScript
- **Size:** 5.81 KB
- **Capabilities:**
  - Create a new [[QuickJSAsyncRuntime]] in a separate WebAssembly module.
  - Create a new [[QuickJSAsyncContext]] (with an associated runtime) in an
  - Returns an interrupt handler that interrupts Javascript execution after a deadline time.
- **Methods:** 5

#### emscripten-types
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/emscripten-types.js`
- **Type:** JavaScript
- **Size:** 776 B

#### deferred-promise
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/deferred-promise.js`
- **Type:** JavaScript
- **Size:** 3.67 KB
- **Capabilities:**
  - QuickJSDeferredPromise wraps a QuickJS promise [[handle]] and allows
  - Use [[QuickJSContext.newPromise]] to create a new promise instead of calling
  - Resolve [[handle]] with the given value, if any.

#### module-asyncify
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/module-asyncify.js`
- **Type:** JavaScript
- **Size:** 3.94 KB
- **Capabilities:**
  - Asyncified version of [[QuickJSWASMModule]].
  - Create a new async runtime inside this WebAssembly module. All runtimes inside a
  - A simplified API to create a new [[QuickJSRuntime]] and a
- **Methods:** 1

#### runtime
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/runtime.js`
- **Type:** JavaScript
- **Size:** 12.61 KB
- **Capabilities:**
  - A runtime represents a Javascript runtime corresponding to an object heap.
  - Set the loader for EcmaScript modules requested by any context in this
  - Remove the the loader set by [[setModuleLoader]]. This disables module loading.

#### ffi.WASM_RELEASE_SYNC
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/generated/ffi.WASM_RELEASE_SYNC.js`
- **Type:** JavaScript
- **Size:** 5.83 KB
- **Capabilities:**
  - Low-level FFI bindings to QuickJS's Emscripten module.

#### emscripten-module.WASM_RELEASE_SYNC
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/generated/emscripten-module.WASM_RELEASE_SYNC.js`
- **Type:** JavaScript
- **Size:** 617.93 KB
- **Methods:** 20

#### errors
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/errors.js`
- **Type:** JavaScript
- **Size:** 1.87 KB
- **Capabilities:**
  - Error thrown if [[QuickJSContext.unwrapResult]] unwraps an error value that isn't an object.

#### esmHelpers
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/esmHelpers.js`
- **Type:** JavaScript
- **Size:** 778 B
- **Methods:** 2

#### context
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/context.js`
- **Type:** JavaScript
- **Size:** 28.9 KB
- **Capabilities:**
  - Track `lifetime` so that it is disposed when this scope is disposed.
  - QuickJSContext wraps a QuickJS Javascript context (JSContext*) within a
  - Dispose of this VM's underlying resources.
- **Methods:** 6

#### runtime-asyncify
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/runtime-asyncify.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Capabilities:**
  - Set the max stack size for this runtime in bytes.

#### variants
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/variants.js`
- **Type:** JavaScript
- **Size:** 6.73 KB
- **Capabilities:**
  - Create a new, completely isolated WebAssembly module containing the QuickJS library.
  - Create a new, completely isolated WebAssembly module containing a version of the QuickJS library
  - Helper intended to memoize the creation of a WebAssembly module.
- **Methods:** 3

#### types-ffi
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/types-ffi.js`
- **Type:** JavaScript
- **Size:** 1.31 KB
- **Capabilities:**
  - compile but do not run. The result is an object with a
- **Methods:** 3

#### asyncify-helpers
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/asyncify-helpers.js`
- **Type:** JavaScript
- **Size:** 1.78 KB
- **Capabilities:**
  - Create a function that may or may not be async, using a generator
- **Methods:** 7

#### context-asyncify
- **File:** `frontend-tools/node_modules/@tootallnate/quickjs-emscripten/dist/context-asyncify.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Capabilities:**
  - Asyncified version of [[QuickJSContext]].
  - Asyncified version of [[evalCode]].
  - Similar to [[newFunction]].
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/data-uri-to-buffer/dist/index.js`
- **Type:** JavaScript
- **Size:** 1.79 KB
- **Capabilities:**
  - Returns a `Buffer` instance from the given data URI `uri`.
- **Methods:** 2

#### node
- **File:** `frontend-tools/node_modules/data-uri-to-buffer/dist/node.js`
- **Type:** JavaScript
- **Size:** 958 B
- **Capabilities:**
  - Returns a `Buffer` instance from the given data URI `uri`.
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/lighthouse-logger/index.js`
- **Type:** JavaScript
- **Size:** 6.28 KB
- **Capabilities:**
  - Fires off all status updates. Listen with
  - Fires off all warnings. Listen with
  - A simple formatting utility for event logging.

#### index
- **File:** `frontend-tools/node_modules/get-intrinsic/index.js`
- **Type:** JavaScript
- **Size:** 14.1 KB
- **Methods:** 4

#### GetIntrinsic
- **File:** `frontend-tools/node_modules/get-intrinsic/test/GetIntrinsic.js`
- **Type:** JavaScript
- **Size:** 8.55 KB
- **Methods:** 3

#### posix
- **File:** `frontend-tools/node_modules/bare-path/lib/posix.js`
- **Type:** JavaScript
- **Size:** 5.85 KB
- **Methods:** 10

#### win32
- **File:** `frontend-tools/node_modules/bare-path/lib/win32.js`
- **Type:** JavaScript
- **Size:** 13.11 KB
- **Methods:** 11

#### shared
- **File:** `frontend-tools/node_modules/bare-path/lib/shared.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Methods:** 1

#### esprima
- **File:** `frontend-tools/node_modules/esprima/dist/esprima.js`
- **Type:** JavaScript
- **Size:** 276.92 KB
- **Capabilities:**
  -  Scanner
- **Methods:** 20

#### esvalidate
- **File:** `frontend-tools/node_modules/esprima/bin/esvalidate.js`
- **Type:** JavaScript
- **Size:** 7.56 KB
- **Methods:** 2

#### esparse
- **File:** `frontend-tools/node_modules/esprima/bin/esparse.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/through/index.js`
- **Type:** JavaScript
- **Size:** 2.56 KB
- **Methods:** 3

#### auto-destroy
- **File:** `frontend-tools/node_modules/through/test/auto-destroy.js`
- **Type:** JavaScript
- **Size:** 516 B

#### index
- **File:** `frontend-tools/node_modules/through/test/index.js`
- **Type:** JavaScript
- **Size:** 2.31 KB
- **Methods:** 3

#### end
- **File:** `frontend-tools/node_modules/through/test/end.js`
- **Type:** JavaScript
- **Size:** 707 B

#### buffering
- **File:** `frontend-tools/node_modules/through/test/buffering.js`
- **Type:** JavaScript
- **Size:** 1.47 KB

#### async
- **File:** `frontend-tools/node_modules/through/test/async.js`
- **Type:** JavaScript
- **Size:** 629 B

#### colors
- **File:** `frontend-tools/node_modules/@colors/colors/lib/colors.js`
- **Type:** JavaScript
- **Size:** 5.73 KB
- **Methods:** 8

#### styles
- **File:** `frontend-tools/node_modules/@colors/colors/lib/styles.js`
- **Type:** JavaScript
- **Size:** 2.45 KB

#### extendStringPrototype
- **File:** `frontend-tools/node_modules/@colors/colors/lib/extendStringPrototype.js`
- **Type:** JavaScript
- **Size:** 3.22 KB
- **Methods:** 1

#### supports-colors
- **File:** `frontend-tools/node_modules/@colors/colors/lib/system/supports-colors.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Methods:** 3

#### has-flag
- **File:** `frontend-tools/node_modules/@colors/colors/lib/system/has-flag.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### trap
- **File:** `frontend-tools/node_modules/@colors/colors/lib/custom/trap.js`
- **Type:** JavaScript
- **Size:** 1.64 KB
- **Methods:** 1

#### zalgo
- **File:** `frontend-tools/node_modules/@colors/colors/lib/custom/zalgo.js`
- **Type:** JavaScript
- **Size:** 2.82 KB
- **Methods:** 4

#### rainbow
- **File:** `frontend-tools/node_modules/@colors/colors/lib/maps/rainbow.js`
- **Type:** JavaScript
- **Size:** 311 B

#### zebra
- **File:** `frontend-tools/node_modules/@colors/colors/lib/maps/zebra.js`
- **Type:** JavaScript
- **Size:** 146 B

#### random
- **File:** `frontend-tools/node_modules/@colors/colors/lib/maps/random.js`
- **Type:** JavaScript
- **Size:** 454 B

#### america
- **File:** `frontend-tools/node_modules/@colors/colors/lib/maps/america.js`
- **Type:** JavaScript
- **Size:** 278 B

#### index
- **File:** `frontend-tools/node_modules/bare-events/index.js`
- **Type:** JavaScript
- **Size:** 7.65 KB
- **Methods:** 14

#### web
- **File:** `frontend-tools/node_modules/bare-events/web.js`
- **Type:** JavaScript
- **Size:** 7.25 KB
- **Methods:** 1

#### errors
- **File:** `frontend-tools/node_modules/bare-events/lib/errors.js`
- **Type:** JavaScript
- **Size:** 671 B

#### index
- **File:** `frontend-tools/node_modules/mime-types/index.js`
- **Type:** JavaScript
- **Size:** 3.58 KB
- **Capabilities:**
  - Module dependencies.
  - Module variables.
  - Module exports.
- **Methods:** 6

#### types
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/insights/types.js`
- **Type:** JavaScript
- **Size:** 522 B

#### RenderBlocking
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/insights/RenderBlocking.js`
- **Type:** JavaScript
- **Size:** 1.62 KB
- **Methods:** 2

#### InteractionToNextPaint
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/insights/InteractionToNextPaint.js`
- **Type:** JavaScript
- **Size:** 1.76 KB
- **Methods:** 2

#### LargestContentfulPaint
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/insights/LargestContentfulPaint.js`
- **Type:** JavaScript
- **Size:** 4.6 KB
- **Methods:** 4

#### TraceEvents
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/types/TraceEvents.js`
- **Type:** JavaScript
- **Size:** 17.42 KB
- **Capabilities:**
  - Generally, before JS is executed, a trace event is dispatched that
  - Is Trace Event Invalidate Layout
- **Methods:** 20

#### Configuration
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/types/Configuration.js`
- **Type:** JavaScript
- **Size:** 1.72 KB
- **Capabilities:**
  - We want to yield regularly to maintain responsiveness. If we yield too often, we're wasting idle time.
  - Generates a key that can be used to represent this config in a cache. This is
- **Methods:** 1

#### Timing
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/types/Timing.js`
- **Type:** JavaScript
- **Size:** 368 B
- **Methods:** 3

#### Extensions
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/types/Extensions.js`
- **Type:** JavaScript
- **Size:** 1.3 KB
- **Capabilities:**
  - Validate Color In Payload
- **Methods:** 5

#### types
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/types.js`
- **Type:** JavaScript
- **Size:** 628 B
- **Capabilities:**
  - Because you can run the trace engine with a subset of handlers enabled,
- **Methods:** 1

#### InvalidationsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/InvalidationsHandler.js`
- **Type:** JavaScript
- **Size:** 5.8 KB
- **Methods:** 6

#### UserTimingsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/UserTimingsHandler.js`
- **Type:** JavaScript
- **Size:** 3.56 KB
- **Methods:** 4

#### RendererHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/RendererHandler.js`
- **Type:** JavaScript
- **Size:** 13.62 KB
- **Capabilities:**
  - This handler builds the hierarchy of trace events and profile calls
  - Steps through all the renderer processes we've located so far in the meta
  - Assigns origins to all threads in all processes.
- **Methods:** 16

#### SamplesHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/SamplesHandler.js`
- **Type:** JavaScript
- **Size:** 9.93 KB
- **Capabilities:**
  - A fake trace event created to support CDP.Profiler.Profiles in the
  - Returns the name of a function for a given synthetic profile call.
- **Methods:** 12

#### WorkersHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/WorkersHandler.js`
- **Type:** JavaScript
- **Size:** 1.84 KB
- **Methods:** 5

#### WarningsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/WarningsHandler.js`
- **Type:** JavaScript
- **Size:** 5.14 KB
- **Capabilities:**
  - Tracks the stack formed by nested trace events up to a given point
  - Tracks the stack formed by JS invocation trace events up to a given point.
  - Tracks reflow events in a task.
- **Methods:** 8

#### Threads
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/Threads.js`
- **Type:** JavaScript
- **Size:** 4.17 KB
- **Capabilities:**
  - Given trace parsed data, this helper will return a high level array of
- **Methods:** 3

#### PageLoadMetricsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/PageLoadMetricsHandler.js`
- **Type:** JavaScript
- **Size:** 17.23 KB
- **Capabilities:**
  - This handler stores page load metrics, including web vitals,
  - This represents the metric scores for all navigations, for all frames in a trace.
  - Page load events with no associated duration that happened in the
- **Methods:** 15

#### MemoryHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/MemoryHandler.js`
- **Type:** JavaScript
- **Size:** 824 B
- **Methods:** 3

#### LayerTreeHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/LayerTreeHandler.js`
- **Type:** JavaScript
- **Size:** 5.07 KB
- **Methods:** 6

#### GPUHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/GPUHandler.js`
- **Type:** JavaScript
- **Size:** 2.01 KB
- **Methods:** 6

#### LayoutShiftsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/LayoutShiftsHandler.js`
- **Type:** JavaScript
- **Size:** 16.77 KB
- **Capabilities:**
  - Collects backend node ids coming from LayoutShift and LayoutInvalidation
- **Methods:** 13

#### ExtensionTraceDataHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/ExtensionTraceDataHandler.js`
- **Type:** JavaScript
- **Size:** 3.46 KB
- **Methods:** 8

#### AuctionWorkletsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/AuctionWorkletsHandler.js`
- **Type:** JavaScript
- **Size:** 6.49 KB
- **Capabilities:**
  - There are two metadata events that we care about.
  - We cannot make the full event without knowing the type of event, but we can
- **Methods:** 6

#### ModelHandlers
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/ModelHandlers.js`
- **Type:** JavaScript
- **Size:** 1.39 KB

#### UserInteractionsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/UserInteractionsHandler.js`
- **Type:** JavaScript
- **Size:** 13.44 KB
- **Capabilities:**
  - See https://web.dev/better-responsiveness-metric/#interaction-types for the
  - We define a set of interactions as nested where:
  - Because we nest events only that are in the same category, we store the
- **Methods:** 10

#### LargestImagePaintHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/LargestImagePaintHandler.js`
- **Type:** JavaScript
- **Size:** 1.48 KB
- **Capabilities:**
  - If the LCP resource was an image, and that image was fetched over the
- **Methods:** 3

#### FramesHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/FramesHandler.js`
- **Type:** JavaScript
- **Size:** 18.64 KB
- **Capabilities:**
  - IMPORTANT: this handler is slightly different to the rest. This is because
- **Methods:** 9

#### NetworkRequestsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/NetworkRequestsHandler.js`
- **Type:** JavaScript
- **Size:** 18.7 KB
- **Methods:** 8

#### handlers
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/handlers.js`
- **Type:** JavaScript
- **Size:** 332 B

#### ScreenshotsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/ScreenshotsHandler.js`
- **Type:** JavaScript
- **Size:** 3.54 KB
- **Capabilities:**
  - Correct the screenshot timestamps
- **Methods:** 6

#### LargestTextPaintHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/LargestTextPaintHandler.js`
- **Type:** JavaScript
- **Size:** 889 B
- **Capabilities:**
  - A trace file will contain all the text paints that were candidates for the
- **Methods:** 3

#### InitiatorsHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/InitiatorsHandler.js`
- **Type:** JavaScript
- **Size:** 10.2 KB
- **Methods:** 7

#### AnimationHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/AnimationHandler.js`
- **Type:** JavaScript
- **Size:** 1.1 KB
- **Methods:** 4

#### MetaHandler
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/handlers/MetaHandler.js`
- **Type:** JavaScript
- **Size:** 16.17 KB
- **Capabilities:**
  - These represent the user navigating. Values such as First Contentful Paint,
- **Methods:** 6

#### RootCauses
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/root-causes/RootCauses.js`
- **Type:** JavaScript
- **Size:** 482 B

#### LayoutShift
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/root-causes/LayoutShift.js`
- **Type:** JavaScript
- **Size:** 26.37 KB
- **Capabilities:**
  - Calculates the potential root causes for a given layout shift event. Once
  - Determines potential root causes for shifts
  - "LayoutInvalidations" are a set of trace events dispatched in Blink under the name
- **Methods:** 17

#### Processor
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/Processor.js`
- **Type:** JavaScript
- **Size:** 10.93 KB
- **Capabilities:**
  - When the user passes in a set of handlers, we want to ensure that we have all
  - Some Handlers need data provided by others. Dependencies of a handler handler are
- **Methods:** 2

#### FetchNodes
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/extras/FetchNodes.js`
- **Type:** JavaScript
- **Size:** 6.64 KB
- **Capabilities:**
  - Looks up the DOM Node on the page for the given BackendNodeId. Uses the
  - Looks up for backend node ids in different types of trace events
  - Takes a set of Protocol.DOM.BackendNodeId ids and will return a map of NodeId=>DOMNode.
- **Methods:** 7

#### MainThreadActivity
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/extras/MainThreadActivity.js`
- **Type:** JavaScript
- **Size:** 3.46 KB
- **Capabilities:**
  - Calculates regions of low utilization and returns the index of the event
- **Methods:** 2

#### Metadata
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/extras/Metadata.js`
- **Type:** JavaScript
- **Size:** 2.72 KB
- **Methods:** 2

#### FilmStrip
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/extras/FilmStrip.js`
- **Type:** JavaScript
- **Size:** 1.65 KB
- **Methods:** 2

#### ModelImpl
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/ModelImpl.js`
- **Type:** JavaScript
- **Size:** 7.48 KB
- **Capabilities:**
  - The new trace engine model we are migrating to. The Model is responsible for
  - Updates the configuration. Useful if a user changes a setting - this lets
  - Parses an array of trace events into a structured object containing all the
- **Methods:** 3

#### trace
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/trace.js`
- **Type:** JavaScript
- **Size:** 970 B

#### EntriesFilter
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/EntriesFilter.js`
- **Type:** JavaScript
- **Size:** 13.8 KB
- **Capabilities:**
  - This class can take in a thread that has been generated by the
  - Checks which actions can be applied on an entry. This allows us to only show possible actions in the Context Menu.
  - Returns the amount of entry descendants that belong to the hidden entries array.
- **Methods:** 1

#### TreeHelpers
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/TreeHelpers.js`
- **Type:** JavaScript
- **Size:** 8.74 KB
- **Capabilities:**
  - Builds a hierarchy of the entries (trace events and profile calls) in
  - Iterates events in a tree hierarchically, from top to bottom,
  - Given a Helpers.TreeHelpers.RendererTree, this will iterates events in hierarchically, visiting
- **Methods:** 5

#### helpers
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/helpers.js`
- **Type:** JavaScript
- **Size:** 433 B

#### Trace
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/Trace.js`
- **Type:** JavaScript
- **Size:** 8.46 KB
- **Capabilities:**
  - Sorts all the events in place, in order, by their start time. If they have
  - Returns an array of ordered events that results after merging the two
- **Methods:** 16

#### SamplesIntegrator
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/SamplesIntegrator.js`
- **Type:** JavaScript
- **Size:** 17.44 KB
- **Capabilities:**
  - This is a helper that integrates CPU profiling data coming in the
  - The result of runing the samples integrator. Holds the JS calls
  - tracks the state of the JS stack at each point in time to update

#### Timing
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/Timing.js`
- **Type:** JavaScript
- **Size:** 7.6 KB
- **Methods:** 10

#### Extensions
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/trace/helpers/Extensions.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Methods:** 1

#### ProfileTreeModel
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/cpu_profile/ProfileTreeModel.js`
- **Type:** JavaScript
- **Size:** 2.61 KB

#### CPUProfileDataModel
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/models/cpu_profile/CPUProfileDataModel.js`
- **Type:** JavaScript
- **Size:** 21.1 KB
- **Capabilities:**
  - A cache for the nodes we have parsed.
  - Calculate timestamps using timeDeltas. Some CPU profile formats,
  - Creates a Tree of CPUProfileNodes using the Protocol.Profiler.ProfileNodes.
- **Methods:** 7

#### SetUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/SetUtilities.js`
- **Type:** JavaScript
- **Size:** 611 B

#### UserVisibleError
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/UserVisibleError.js`
- **Type:** JavaScript
- **Size:** 790 B
- **Capabilities:**
  - Represents an error that might become visible to the user. Where errors
- **Methods:** 1

#### NumberUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/NumberUtilities.js`
- **Type:** JavaScript
- **Size:** 2.24 KB
- **Capabilities:**
  - Rounds a number (including float) down.
  - Computes the great common divisor for two numbers.

#### DevToolsPath
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/DevToolsPath.js`
- **Type:** JavaScript
- **Size:** 319 B

#### platform
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/platform.js`
- **Type:** JavaScript
- **Size:** 3.02 KB

#### MimeType
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/MimeType.js`
- **Type:** JavaScript
- **Size:** 5.36 KB
- **Capabilities:**
  - MIME types other than the ones with the "text" type that have text content.
  - Port of net::HttpUtils::ParseContentType to extract mimeType and charset from
- **Methods:** 5

#### DateUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/DateUtilities.js`
- **Type:** JavaScript
- **Size:** 594 B
- **Methods:** 1

#### PromiseUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/PromiseUtilities.js`
- **Type:** JavaScript
- **Size:** 593 B
- **Capabilities:**
  - Returns a new pending promise together with it's resolve and reject functions.
- **Methods:** 1

#### KeyboardUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/KeyboardUtilities.js`
- **Type:** JavaScript
- **Size:** 742 B
- **Methods:** 3

#### DOMUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/DOMUtilities.js`
- **Type:** JavaScript
- **Size:** 3.73 KB
- **Capabilities:**
  - `document.activeElement` will not enter shadow roots to find the element
- **Methods:** 3

#### ArrayUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/ArrayUtilities.js`
- **Type:** JavaScript
- **Size:** 6.87 KB
- **Capabilities:**
  - Obtains the first or last item in the array that satisfies the predicate function.
  - Obtains the first item in the array that satisfies the predicate function.
  - Obtains the last item in the array that satisfies the predicate function.
- **Methods:** 11

#### TypescriptUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/TypescriptUtilities.js`
- **Type:** JavaScript
- **Size:** 999 B
- **Capabilities:**
  - This is useful to keep TypeScript happy in a test - if you have a value
  - This is useful to check on the type-level that the unhandled cases of
- **Methods:** 4

#### TypedArrayUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/TypedArrayUtilities.js`
- **Type:** JavaScript
- **Size:** 3.59 KB
- **Methods:** 2

#### StringUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/StringUtilities.js`
- **Type:** JavaScript
- **Size:** 17.66 KB
- **Capabilities:**
  - This implements a subset of the sprintf() function described in the Single UNIX
  - Tests if the `inputStr` is following the extended Kebab Case naming convetion,
  - Somewhat efficiently concatenates 2 base64 encoded strings.
- **Methods:** 5

#### Timing
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/Timing.js`
- **Type:** JavaScript
- **Size:** 408 B
- **Methods:** 3

#### MapUtilities
- **File:** `frontend-tools/node_modules/@paulirish/trace_engine/core/platform/MapUtilities.js`
- **Type:** JavaScript
- **Size:** 1.85 KB
- **Capabilities:**
  - Gets value for key, assigning a default if value is falsy.
- **Methods:** 1

#### marky.min
- **File:** `frontend-tools/node_modules/marky/dist/marky.min.js`
- **Type:** JavaScript
- **Size:** 1.06 KB
- **Methods:** 1

#### marky
- **File:** `frontend-tools/node_modules/marky/dist/marky.js`
- **Type:** JavaScript
- **Size:** 3.22 KB
- **Methods:** 2

#### marky.browser.es
- **File:** `frontend-tools/node_modules/marky/lib/marky.browser.es.js`
- **Type:** JavaScript
- **Size:** 2.88 KB
- **Methods:** 2

#### marky.browser.cjs
- **File:** `frontend-tools/node_modules/marky/lib/marky.browser.cjs.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Methods:** 2

#### marky.cjs
- **File:** `frontend-tools/node_modules/marky/lib/marky.cjs.js`
- **Type:** JavaScript
- **Size:** 3.45 KB
- **Methods:** 2

#### marky.es
- **File:** `frontend-tools/node_modules/marky/lib/marky.es.js`
- **Type:** JavaScript
- **Size:** 3.3 KB
- **Methods:** 2

#### delayed_stream
- **File:** `frontend-tools/node_modules/delayed-stream/lib/delayed_stream.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/fd-slicer/index.js`
- **Type:** JavaScript
- **Size:** 7.57 KB
- **Capabilities:**
  -  Write Stream
- **Methods:** 8

#### index
- **File:** `frontend-tools/node_modules/get-proto/index.js`
- **Type:** JavaScript
- **Size:** 821 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/get-proto/test/index.js`
- **Type:** JavaScript
- **Size:** 2.3 KB

#### index
- **File:** `frontend-tools/node_modules/typedarray-to-buffer/index.js`
- **Type:** JavaScript
- **Size:** 758 B
- **Capabilities:**
  - Convert a typed array to a Buffer without a copy
- **Methods:** 1

#### basic
- **File:** `frontend-tools/node_modules/typedarray-to-buffer/test/basic.js`
- **Type:** JavaScript
- **Size:** 1.59 KB

#### index
- **File:** `frontend-tools/node_modules/webidl-conversions/lib/index.js`
- **Type:** JavaScript
- **Size:** 4.94 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/speedline-core/lib/index.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - Retrieve speed index informations
- **Methods:** 1

#### frame
- **File:** `frontend-tools/node_modules/speedline-core/lib/frame.js`
- **Type:** JavaScript
- **Size:** 5.54 KB
- **Methods:** 6

#### speed-index
- **File:** `frontend-tools/node_modules/speedline-core/lib/speed-index.js`
- **Type:** JavaScript
- **Size:** 8.22 KB
- **Capabilities:**
  - This computes the allowed percentage of change between two frames in fast mode where we won't examine the frames in between them.
- **Methods:** 11

#### index
- **File:** `frontend-tools/node_modules/gopd/test/index.js`
- **Type:** JavaScript
- **Size:** 656 B

#### index
- **File:** `frontend-tools/node_modules/tar-fs/index.js`
- **Type:** JavaScript
- **Size:** 9.99 KB
- **Capabilities:**
  - Validate
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/json-parse-even-better-errors/index.js`
- **Type:** JavaScript
- **Size:** 3.81 KB

#### Explorer
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/Explorer.js`
- **Type:** JavaScript
- **Size:** 4.15 KB

#### index
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.22 KB
- **Methods:** 6

#### loaders
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/loaders.js`
- **Type:** JavaScript
- **Size:** 4.52 KB
- **Methods:** 7

#### ExplorerSync
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/ExplorerSync.js`
- **Type:** JavaScript
- **Size:** 4.28 KB

#### util
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/util.js`
- **Type:** JavaScript
- **Size:** 1.63 KB
- **Methods:** 3

#### ExplorerBase
- **File:** `frontend-tools/node_modules/cosmiconfig/dist/ExplorerBase.js`
- **Type:** JavaScript
- **Size:** 2.59 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/lru-cache/index.js`
- **Type:** JavaScript
- **Size:** 33.14 KB
- **Methods:** 1

#### RequireObjectCoercible
- **File:** `frontend-tools/node_modules/es-object-atoms/RequireObjectCoercible.js`
- **Type:** JavaScript
- **Size:** 313 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/es-object-atoms/test/index.js`
- **Type:** JavaScript
- **Size:** 1.07 KB

#### ToObject
- **File:** `frontend-tools/node_modules/es-object-atoms/ToObject.js`
- **Type:** JavaScript
- **Size:** 250 B
- **Methods:** 1

#### isObject
- **File:** `frontend-tools/node_modules/es-object-atoms/isObject.js`
- **Type:** JavaScript
- **Size:** 161 B
- **Methods:** 1

#### parseList
- **File:** `frontend-tools/node_modules/basic-ftp/dist/parseList.js`
- **Type:** JavaScript
- **Size:** 2.69 KB
- **Capabilities:**
  - Available directory listing parsers. These are candidates that will be tested
  - Parse raw directory listing.
- **Methods:** 4

#### parseListUnix
- **File:** `frontend-tools/node_modules/basic-ftp/dist/parseListUnix.js`
- **Type:** JavaScript
- **Size:** 5.65 KB
- **Capabilities:**
  - This parser is based on the FTP client library source code in Apache Commons Net provided
  - numeric or standard format date:
  - year (for non-recent standard format) - yyyy
- **Methods:** 4

#### transfer
- **File:** `frontend-tools/node_modules/basic-ftp/dist/transfer.js`
- **Type:** JavaScript
- **Size:** 13.04 KB
- **Capabilities:**
  - Prepare a data socket using passive mode over IPv6.
  - Parse an EPSV response. Returns only the port as in EPSV the host of the control connection is used.
  - Prepare a data socket using passive mode over IPv4.
- **Methods:** 11

#### index
- **File:** `frontend-tools/node_modules/basic-ftp/dist/index.js`
- **Type:** JavaScript
- **Size:** 1.37 KB

#### ProgressTracker
- **File:** `frontend-tools/node_modules/basic-ftp/dist/ProgressTracker.js`
- **Type:** JavaScript
- **Size:** 2.03 KB
- **Capabilities:**
  - Tracks progress of one socket data transfer at a time.
  - Register a new handler for progress info. Use `undefined` to disable reporting.
  - Start tracking transfer progress of a socket.
- **Methods:** 5

#### netUtils
- **File:** `frontend-tools/node_modules/basic-ftp/dist/netUtils.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Returns a string describing the encryption on a given socket instance.
  - Returns a string describing the remote address of a socket.
  - Upgrade a socket connection with TLS.
- **Methods:** 4

#### parseListDOS
- **File:** `frontend-tools/node_modules/basic-ftp/dist/parseListDOS.js`
- **Type:** JavaScript
- **Size:** 1.8 KB
- **Capabilities:**
  - This parser is based on the FTP client library source code in Apache Commons Net provided
  - Returns true if a given line might be a DOS-style listing.
  - Parse a single line of a DOS-style directory listing.
- **Methods:** 3

#### parseControlResponse
- **File:** `frontend-tools/node_modules/basic-ftp/dist/parseControlResponse.js`
- **Type:** JavaScript
- **Size:** 2.45 KB
- **Capabilities:**
  - Parse an FTP control response as a collection of messages. A message is a complete
  - Return true if an FTP return code describes a positive completion.
  - Return true if an FTP return code describes a positive intermediate response.
- **Methods:** 8

#### FtpContext
- **File:** `frontend-tools/node_modules/basic-ftp/dist/FtpContext.js`
- **Type:** JavaScript
- **Size:** 14.5 KB
- **Capabilities:**
  - Describes an FTP server error response including the FTP response code.
  - FTPContext holds the control and data sockets of an FTP connection and provides a
  - Instantiate an FTP context.
- **Methods:** 1

#### FileInfo
- **File:** `frontend-tools/node_modules/basic-ftp/dist/FileInfo.js`
- **Type:** JavaScript
- **Size:** 3.1 KB
- **Capabilities:**
  - Describes a file, directory or symbolic link.
  - Unparsed, raw modification date as a string.
  - Parsed modification date.

#### Client
- **File:** `frontend-tools/node_modules/basic-ftp/dist/Client.js`
- **Type:** JavaScript
- **Size:** 30.77 KB
- **Capabilities:**
  - High-level API to interact with an FTP server.
  - Instantiate an FTP client.
  - Close the client and all open socket connections.
- **Methods:** 6

#### StringWriter
- **File:** `frontend-tools/node_modules/basic-ftp/dist/StringWriter.js`
- **Type:** JavaScript
- **Size:** 675 B

#### parseListMLSD
- **File:** `frontend-tools/node_modules/basic-ftp/dist/parseListMLSD.js`
- **Type:** JavaScript
- **Size:** 7.76 KB
- **Capabilities:**
  - Parsers for MLSD facts.
  - Split a string once at the first position of a delimiter. For example
  - Returns true if a given line might be part of an MLSD listing.
- **Methods:** 6

#### index
- **File:** `frontend-tools/node_modules/lookup-closest-locale/index.js`
- **Type:** JavaScript
- **Size:** 644 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/agent-base/dist/src/index.js`
- **Type:** JavaScript
- **Size:** 7.72 KB
- **Capabilities:**
  - Base `http.Agent` implementation.
  - Called by node-core's "_http_client.js" module when creating
- **Methods:** 4

#### promisify
- **File:** `frontend-tools/node_modules/agent-base/dist/src/promisify.js`
- **Type:** JavaScript
- **Size:** 495 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/@babel/helper-validator-identifier/lib/index.js`
- **Type:** JavaScript
- **Size:** 1.33 KB

#### identifier
- **File:** `frontend-tools/node_modules/@babel/helper-validator-identifier/lib/identifier.js`
- **Type:** JavaScript
- **Size:** 12.25 KB
- **Methods:** 4

#### keyword
- **File:** `frontend-tools/node_modules/@babel/helper-validator-identifier/lib/keyword.js`
- **Type:** JavaScript
- **Size:** 1.54 KB
- **Methods:** 5

#### index
- **File:** `frontend-tools/node_modules/@babel/code-frame/lib/index.js`
- **Type:** JavaScript
- **Size:** 6.83 KB
- **Methods:** 7

#### URL
- **File:** `frontend-tools/node_modules/whatwg-url/lib/URL.js`
- **Type:** JavaScript
- **Size:** 4.11 KB
- **Methods:** 2

#### utils
- **File:** `frontend-tools/node_modules/whatwg-url/lib/utils.js`
- **Type:** JavaScript
- **Size:** 562 B
- **Methods:** 1

#### url-state-machine
- **File:** `frontend-tools/node_modules/whatwg-url/lib/url-state-machine.js`
- **Type:** JavaScript
- **Size:** 32.79 KB
- **Methods:** 20

#### URL-impl
- **File:** `frontend-tools/node_modules/whatwg-url/lib/URL-impl.js`
- **Type:** JavaScript
- **Size:** 3.71 KB

#### serial
- **File:** `frontend-tools/node_modules/asynckit/serial.js`
- **Type:** JavaScript
- **Size:** 501 B
- **Capabilities:**
  - Runs iterator over provided array elements in series
- **Methods:** 1

#### parallel
- **File:** `frontend-tools/node_modules/asynckit/parallel.js`
- **Type:** JavaScript
- **Size:** 1017 B
- **Capabilities:**
  - Runs iterator over provided array elements in parallel
- **Methods:** 1

#### bench
- **File:** `frontend-tools/node_modules/asynckit/bench.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### serialOrdered
- **File:** `frontend-tools/node_modules/asynckit/serialOrdered.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Capabilities:**
  - Runs iterator over provided sorted array elements in series
  - sort helper to sort array elements in ascending order
  - sort helper to sort array elements in descending order
- **Methods:** 4

#### readable_serial_ordered
- **File:** `frontend-tools/node_modules/asynckit/lib/readable_serial_ordered.js`
- **Type:** JavaScript
- **Size:** 941 B
- **Capabilities:**
  - Streaming wrapper to `asynckit.serialOrdered`
- **Methods:** 1

#### terminator
- **File:** `frontend-tools/node_modules/asynckit/lib/terminator.js`
- **Type:** JavaScript
- **Size:** 533 B
- **Capabilities:**
  - Terminates jobs in the attached state context
- **Methods:** 1

#### state
- **File:** `frontend-tools/node_modules/asynckit/lib/state.js`
- **Type:** JavaScript
- **Size:** 941 B
- **Capabilities:**
  - Creates initial state object
- **Methods:** 2

#### iterate
- **File:** `frontend-tools/node_modules/asynckit/lib/iterate.js`
- **Type:** JavaScript
- **Size:** 1.75 KB
- **Capabilities:**
  - Iterates over each job object
  - Runs iterator over provided job element
- **Methods:** 3

#### readable_parallel
- **File:** `frontend-tools/node_modules/asynckit/lib/readable_parallel.js`
- **Type:** JavaScript
- **Size:** 673 B
- **Capabilities:**
  - Streaming wrapper to `asynckit.parallel`
- **Methods:** 1

#### streamify
- **File:** `frontend-tools/node_modules/asynckit/lib/streamify.js`
- **Type:** JavaScript
- **Size:** 2.89 KB
- **Capabilities:**
  - Wraps iterators with long signature
  - Wraps provided callback function
  - Wraps provided iterator callback function
- **Methods:** 7

#### readable_serial
- **File:** `frontend-tools/node_modules/asynckit/lib/readable_serial.js`
- **Type:** JavaScript
- **Size:** 655 B
- **Capabilities:**
  - Streaming wrapper to `asynckit.serial`
- **Methods:** 1

#### abort
- **File:** `frontend-tools/node_modules/asynckit/lib/abort.js`
- **Type:** JavaScript
- **Size:** 497 B
- **Capabilities:**
  - Aborts leftover active jobs
  - Cleans up leftover job by invoking abort function for the provided job id
- **Methods:** 3

#### defer
- **File:** `frontend-tools/node_modules/asynckit/lib/defer.js`
- **Type:** JavaScript
- **Size:** 441 B
- **Capabilities:**
  - Runs provided function on next iteration of the event loop
- **Methods:** 3

#### readable_asynckit
- **File:** `frontend-tools/node_modules/asynckit/lib/readable_asynckit.js`
- **Type:** JavaScript
- **Size:** 1.57 KB
- **Capabilities:**
  - Base constructor for all streams
  - Destroys readable stream,
  - Starts provided jobs in async manner
- **Methods:** 5

#### async
- **File:** `frontend-tools/node_modules/asynckit/lib/async.js`
- **Type:** JavaScript
- **Size:** 599 B
- **Capabilities:**
  - Runs provided callback asynchronously
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/metaviewport-parser/index.js`
- **Type:** JavaScript
- **Size:** 11.42 KB
- **Methods:** 7

#### index
- **File:** `frontend-tools/node_modules/color/index.js`
- **Type:** JavaScript
- **Size:** 10.91 KB
- **Methods:** 7

#### index
- **File:** `frontend-tools/node_modules/is-docker/index.js`
- **Type:** JavaScript
- **Size:** 449 B
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/follow-redirects/index.js`
- **Type:** JavaScript
- **Size:** 20.16 KB
- **Capabilities:**
  - Validate Url
- **Methods:** 20

#### debug
- **File:** `frontend-tools/node_modules/follow-redirects/debug.js`
- **Type:** JavaScript
- **Size:** 315 B

#### conversions
- **File:** `frontend-tools/node_modules/color-convert/conversions.js`
- **Type:** JavaScript
- **Size:** 16.64 KB
- **Methods:** 1

#### route
- **File:** `frontend-tools/node_modules/color-convert/route.js`
- **Type:** JavaScript
- **Size:** 2.2 KB
- **Methods:** 5

#### index
- **File:** `frontend-tools/node_modules/color-convert/index.js`
- **Type:** JavaScript
- **Size:** 1.67 KB
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/get-stream/index.js`
- **Type:** JavaScript
- **Size:** 1.41 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/configstore/index.js`
- **Type:** JavaScript
- **Size:** 2.4 KB

#### index
- **File:** `frontend-tools/node_modules/pend/index.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Methods:** 5

#### lighthouse_checks
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/lighthouse/lighthouse_checks.js`
- **Type:** JavaScript
- **Size:** 3.37 KB
- **Methods:** 8

#### evaluator
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/evaluator.js`
- **Type:** JavaScript
- **Size:** 2.88 KB

#### csp
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/csp.js`
- **Type:** JavaScript
- **Size:** 9.22 KB
- **Methods:** 6

#### parser
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/parser.js`
- **Type:** JavaScript
- **Size:** 2.63 KB
- **Methods:** 1

#### strictcsp_checks
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/checks/strictcsp_checks.js`
- **Type:** JavaScript
- **Size:** 4.74 KB
- **Capabilities:**
  - Check Strict Dynamic
  - Check Strict Dynamic Not Standalone
  - Check Unsafe Inline Fallback
- **Methods:** 5

#### security_checks
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/checks/security_checks.js`
- **Type:** JavaScript
- **Size:** 16.03 KB
- **Capabilities:**
  - Check Script Unsafe Inline
  - Check Script Unsafe Eval
  - Check Plain Url Schemes
- **Methods:** 17

#### parser_checks
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/checks/parser_checks.js`
- **Type:** JavaScript
- **Size:** 4.18 KB
- **Capabilities:**
  - Check Unknown Directive
  - Check Missing Semicolon
  - Check Invalid Keyword
- **Methods:** 3

#### utils
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/utils.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - Apply Check Funktion To Directives
- **Methods:** 5

#### finding
- **File:** `frontend-tools/node_modules/csp_evaluator/dist/finding.js`
- **Type:** JavaScript
- **Size:** 3.17 KB

#### index
- **File:** `frontend-tools/node_modules/text-decoder/index.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Methods:** 1

#### pass-through-decoder
- **File:** `frontend-tools/node_modules/text-decoder/lib/pass-through-decoder.js`
- **Type:** JavaScript
- **Size:** 273 B

#### browser-decoder
- **File:** `frontend-tools/node_modules/text-decoder/lib/browser-decoder.js`
- **Type:** JavaScript
- **Size:** 342 B

#### utf8-decoder
- **File:** `frontend-tools/node_modules/text-decoder/lib/utf8-decoder.js`
- **Type:** JavaScript
- **Size:** 2.47 KB
- **Capabilities:**
  - https://encoding.spec.whatwg.org/#utf-8-decoder

#### helpers
- **File:** `frontend-tools/node_modules/http-proxy-agent/node_modules/agent-base/dist/helpers.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/http-proxy-agent/node_modules/agent-base/dist/index.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Determine whether this is an `http` or `https` request.

#### index
- **File:** `frontend-tools/node_modules/http-proxy-agent/dist/index.js`
- **Type:** JavaScript
- **Size:** 5.95 KB
- **Capabilities:**
  - The `HttpProxyAgent` implements an HTTP Agent subclass that connects
- **Methods:** 3

#### index
- **File:** `frontend-tools/node_modules/streamx/index.js`
- **Type:** JavaScript
- **Size:** 32.57 KB
- **Methods:** 20

#### imurmurhash.min
- **File:** `frontend-tools/node_modules/imurmurhash/imurmurhash.min.js`
- **Type:** JavaScript
- **Size:** 1.85 KB
- **Methods:** 1

#### imurmurhash
- **File:** `frontend-tools/node_modules/imurmurhash/imurmurhash.js`
- **Type:** JavaScript
- **Size:** 4.31 KB
- **Methods:** 2

#### legacy-streams
- **File:** `frontend-tools/node_modules/graceful-fs/legacy-streams.js`
- **Type:** JavaScript
- **Size:** 2.59 KB
- **Capabilities:**
  -  Write Stream
- **Methods:** 3

#### clone
- **File:** `frontend-tools/node_modules/graceful-fs/clone.js`
- **Type:** JavaScript
- **Size:** 496 B
- **Methods:** 1

#### graceful-fs
- **File:** `frontend-tools/node_modules/graceful-fs/graceful-fs.js`
- **Type:** JavaScript
- **Size:** 12.38 KB
- **Capabilities:**
  -  Write Stream
  - Create Write Stream
- **Methods:** 20

#### polyfills
- **File:** `frontend-tools/node_modules/graceful-fs/polyfills.js`
- **Type:** JavaScript
- **Size:** 9.9 KB
- **Methods:** 14

#### common
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/debug/src/common.js`
- **Type:** JavaScript
- **Size:** 6.14 KB
- **Capabilities:**
  - This is the common logic for both the Node.js and web browser
  - The currently active debug mode names, and names to skip.
  - Map of special "%n" handling functions, for the debug "format" argument.
- **Methods:** 11

#### browser
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/debug/src/browser.js`
- **Type:** JavaScript
- **Size:** 5.87 KB
- **Capabilities:**
  - This is the web browser implementation of `debug()`.
  - Currently only WebKit-based Web Inspectors, Firefox >= v31,
  - Colorize log arguments if enabled.
- **Methods:** 5

#### node
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/debug/src/node.js`
- **Type:** JavaScript
- **Size:** 4.58 KB
- **Capabilities:**
  - Module dependencies.
  - This is the Node.js implementation of `debug()`.
  - Build up the default `inspectOpts` object from the environment variables.
- **Methods:** 7

#### validation
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/validation.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Capabilities:**
  - Checks if a status code is allowed in a close frame.
  - Checks if a given buffer contains only correct UTF-8.
- **Methods:** 2

#### permessage-deflate
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/permessage-deflate.js`
- **Type:** JavaScript
- **Size:** 13.78 KB
- **Capabilities:**
  - permessage-deflate implementation.
  - Creates a PerMessageDeflate instance.
  - Create an extension negotiation offer.
- **Methods:** 3

#### subprotocol
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/subprotocol.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Parses the `Sec-WebSocket-Protocol` header into a set of subprotocol names.
- **Methods:** 1

#### limiter
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/limiter.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Capabilities:**
  - A very simple job queue with adjustable concurrency. Adapted from
  - Creates a new `Limiter`.
  - Adds a job to the queue.

#### websocket-server
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/websocket-server.js`
- **Type:** JavaScript
- **Size:** 15.79 KB
- **Capabilities:**
  - Class representing a WebSocket server.
  - Create a `WebSocketServer` instance.
  - Returns the bound address, the address family name, and port of the server
- **Methods:** 7

#### event-target
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/event-target.js`
- **Type:** JavaScript
- **Size:** 7.15 KB
- **Capabilities:**
  - Class representing an event.
  - Create a new `Event`.
  - Class representing a close event.
- **Methods:** 5

#### extension
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/extension.js`
- **Type:** JavaScript
- **Size:** 6.04 KB
- **Capabilities:**
  - Adds an offer to the map of extension offers or a parameter to the map of
  - Parses the `Sec-WebSocket-Extensions` header into an object.
  - Builds the `Sec-WebSocket-Extensions` header field value.
- **Methods:** 3

#### sender
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/sender.js`
- **Type:** JavaScript
- **Size:** 12.33 KB
- **Capabilities:**
  - HyBi Sender implementation.
  - Creates a Sender instance.
  - Frames a piece of data according to the HyBi WebSocket protocol.
- **Methods:** 1

#### websocket
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/websocket.js`
- **Type:** JavaScript
- **Size:** 34.36 KB
- **Capabilities:**
  - Class representing a WebSocket.
  - Create a new `WebSocket`.
  - This deviates from the WHATWG interface since ws doesn't support the
- **Methods:** 20

#### stream
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/stream.js`
- **Type:** JavaScript
- **Size:** 3.99 KB
- **Capabilities:**
  - Emits the `'close'` event on a stream.
  - The listener of the `'end'` event.
  - The listener of the `'error'` event.
- **Methods:** 9

#### buffer-util
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/buffer-util.js`
- **Type:** JavaScript
- **Size:** 2.98 KB
- **Capabilities:**
  - Merges an array of buffers into a new buffer.
  - Masks a buffer using the given mask.
  - Unmasks a buffer using the given mask.
- **Methods:** 5

#### receiver
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/lib/receiver.js`
- **Type:** JavaScript
- **Size:** 16.8 KB
- **Capabilities:**
  - HyBi Receiver implementation.
  - Creates a Receiver instance.
  - Implements `Writable.prototype._write()`.
- **Methods:** 3

#### browser
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ws/browser.js`
- **Type:** JavaScript
- **Size:** 176 B

#### index
- **File:** `frontend-tools/node_modules/puppeteer-core/node_modules/ms/index.js`
- **Type:** JavaScript
- **Size:** 2.95 KB
- **Capabilities:**
  - Parse or format the given `val`.
  - Parse the given `str` and return milliseconds.
  - Short format for `ms`.
- **Methods:** 4

#### puppeteer-core
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/puppeteer-core.js`
- **Type:** JavaScript
- **Size:** 776 B

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/WebWorker.js`
- **Type:** JavaScript
- **Size:** 3.33 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
- **Methods:** 4

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.06 KB
- **Capabilities:**
  - The type of the dialog.
  - The message displayed in the dialog.
  - The default value of the prompt, or an empty string if the dialog

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 217 B

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 39.34 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
  - Used to clear the document handle that has been destroyed.
- **Methods:** 11

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 1.98 KB

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 62.29 KB
- **Capabilities:**
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
  - Queries the current element for an element matching the given selector.
- **Methods:** 11

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.06 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 4.53 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 4.51 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.07 KB
- **Capabilities:**
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).
  - Promise which resolves to a text representation of response body.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.19 KB
- **Capabilities:**
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.
  - If the target is not of type `"page"`, `"webview"` or `"background_page"`,

#### locators
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 23.98 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.2 KB
- **Capabilities:**
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.
  - The Mouse class operates in main-frame CSS pixels

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.17 KB
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 60.32 KB
- **Capabilities:**
  - Page provides methods to interact with a single tab or
  - Listen to page events.
  - Runs `document.querySelector` within the page. If no element matches the
- **Methods:** 14

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 9.82 KB
- **Capabilities:**
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
  - Gets a map of handles representing the properties of the current handle.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 631 B

#### ErrorLike
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.01 KB
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.79 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 6.31 KB
- **Capabilities:**
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.
  - Adds a disposable resource to the stack, returning the resource.

#### decorators
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 5.73 KB
- **Capabilities:**
  - The decorator only invokes the target if the target has not been invoked with
- **Methods:** 7

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 286 B

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 694 B

#### Function
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 1.95 KB
- **Capabilities:**
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 4

#### Mutex
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 943 B

#### ScriptInjector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.09 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.12 KB
- **Capabilities:**
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.
  - The text of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 497 B

#### WaitTask
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 6.59 KB
- **Capabilities:**
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 2.64 KB
- **Capabilities:**
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.25 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 983 B

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 628 B

#### Puppeteer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 2.76 KB
- **Capabilities:**
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.
  - Gets the names of all custom query handlers.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 854 B
- **Capabilities:**
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 4.69 KB
- **Capabilities:**
  - The EventEmitter class that many Puppeteer classes extend.
  - If you pass an emitter, the returned emitter will wrap the passed emitter.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### SecurityDetails
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 1.74 KB
- **Capabilities:**
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.
  - The security protocol being used, e.g. "TLS 1.2".

#### Errors
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Capabilities:**
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.
  - Puppeteer will throw this error if a method is not

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 4.88 KB
- **Capabilities:**
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.05 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 521 B

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 9.74 KB
- **Capabilities:**
  - Validate Dialog Type
- **Methods:** 11

#### common
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 1.29 KB

#### FileChooser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 2.33 KB
- **Capabilities:**
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for
  - Accept the file chooser request with the given file paths.

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 3.78 KB
- **Capabilities:**
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 403 B

#### HandleIterator
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 4.69 KB
- **Capabilities:**
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 550 B

#### TaskQueue
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 483 B

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 16.53 KB

#### QueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 8.4 KB
- **Capabilities:**
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.53 KB

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 10.55 KB
- **Capabilities:**
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 405 B
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 10.82 KB
- **Capabilities:**
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 9.82 KB
- **Capabilities:**
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when
  - The default executable path.

#### PipeTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.03 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 6.78 KB

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 10.05 KB
- **Capabilities:**
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 688 B

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 3.99 KB

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 10.77 KB
- **Capabilities:**
  - Puppeteer's Frame class could be viewed as a BiDi BrowsingContext implementation
- **Methods:** 2

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 3.13 KB

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 7.53 KB
- **Methods:** 4

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 2.81 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 8.62 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.89 KB

#### lifecycle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/lifecycle.js`
- **Type:** JavaScript
- **Size:** 2.13 KB
- **Methods:** 4

#### Serializer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 4.4 KB

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 2.61 KB

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 3.45 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 6.06 KB
- **Capabilities:**
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 731 B

#### Deserializer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 2.82 KB

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 4.33 KB
- **Capabilities:**
  - Internal events that the BrowsingContext class emits.
  - Emitted on the top-level context, when a descendant context is created.
  - Emitted on the top-level context, when a descendant context or the

#### bidi
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 636 B

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 2.02 KB
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 18.02 KB

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 5.38 KB
- **Methods:** 1

#### ExposedFunction
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 8.12 KB

#### Sandbox
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Sandbox.js`
- **Type:** JavaScript
- **Size:** 2.25 KB

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 4.32 KB
- **Capabilities:**
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
  - This transport is given to the BiDi server instance and allows Puppeteer
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 25.35 KB
- **Methods:** 6

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 1.94 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Browser.js`
- **Type:** JavaScript
- **Size:** 12.77 KB
- **Methods:** 4

#### Navigation
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Navigation.js`
- **Type:** JavaScript
- **Size:** 6.66 KB
- **Methods:** 2

#### Session
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Session.js`
- **Type:** JavaScript
- **Size:** 8.87 KB
- **Capabilities:**
  - Currently, there is a 1:1 relationship between the session and the
- **Methods:** 2

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 18.77 KB
- **Methods:** 2

#### Request
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Request.js`
- **Type:** JavaScript
- **Size:** 6.68 KB
- **Methods:** 2

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/Realm.js`
- **Type:** JavaScript
- **Size:** 11.55 KB
- **Methods:** 2

#### UserPrompt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/UserPrompt.js`
- **Type:** JavaScript
- **Size:** 6.2 KB
- **Methods:** 2

#### UserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/bidi/core/UserContext.js`
- **Type:** JavaScript
- **Size:** 7.9 KB
- **Methods:** 2

#### injected
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 13.85 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 6

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 1.49 KB

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 568 B

#### FrameTree
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 2.62 KB
- **Capabilities:**
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 6.21 KB
- **Capabilities:**
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',
  - Keeps track of targets that were created via 'Target.targetCreated'

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 20.53 KB
- **Capabilities:**
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 12.35 KB
- **Capabilities:**
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 17.08 KB
- **Capabilities:**
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the
  - When the main frame is replaced by another main frame,

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 8.61 KB
- **Capabilities:**
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 3.27 KB
- **Capabilities:**
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 11.39 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 12.43 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 3.9 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.31 KB
- **Capabilities:**
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.16 KB
- **Capabilities:**
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 6.62 KB
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 21.53 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 928 B
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 12.83 KB
- **Capabilities:**
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
  - A target is added to this map once ChromeTargetManager has created
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 9.01 KB
- **Methods:** 2

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 6.78 KB
- **Capabilities:**
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 14.72 KB
- **Capabilities:**
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### utils
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/utils.js`
- **Type:** JavaScript
- **Size:** 6.35 KB
- **Methods:** 6

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 6.28 KB
- **Capabilities:**
  - Device in a request prompt.
  - Device id during a prompt.
  - Device name as it appears in a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 1.34 KB

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 2.78 KB
- **Capabilities:**
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 11 KB
- **Capabilities:**
  - Evaluates the given function.
- **Methods:** 3

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.15 KB
- **Capabilities:**
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 5.83 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 12.92 KB
- **Capabilities:**
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
  - Promise that resolves to the array of coverage reports
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 35.3 KB
- **Capabilities:**
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 3

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.23 KB
- **Capabilities:**
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.46 KB

#### PSelectorParser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.05 KB
- **Methods:** 1

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 869 B

#### injected
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 1.2 KB

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.14 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 3.54 KB

#### PQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 8.39 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 385 B

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.49 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 3.31 KB
- **Capabilities:**
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
  - Erases the cache when the tree has mutated text.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 31.14 KB
- **Methods:** 20

#### mitt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/esm/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 412 B
- **Methods:** 1

#### puppeteer-core
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/puppeteer-core.js`
- **Type:** JavaScript
- **Size:** 1.82 KB

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/WebWorker.js`
- **Type:** JavaScript
- **Size:** 3.52 KB
- **Capabilities:**
  - This class represents a
  - The URL of this web worker.
- **Methods:** 4

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Dialog.js`
- **Type:** JavaScript
- **Size:** 2.21 KB
- **Capabilities:**
  - The type of the dialog.
  - The message displayed in the dialog.
  - The default value of the prompt, or an empty string if the dialog

#### ElementHandleSymbol
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandleSymbol.js`
- **Type:** JavaScript
- **Size:** 324 B

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Frame.js`
- **Type:** JavaScript
- **Size:** 39.96 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.
  - Represents a DOM frame.
  - Used to clear the document handle that has been destroyed.
- **Methods:** 11

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 2.17 KB

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 63.42 KB
- **Capabilities:**
  - ElementHandle represents an in-page DOM element.
  - A given method will have it's `this` replaced with an isolated version of
  - Queries the current element for an element matching the given selector.
- **Methods:** 11

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/CDPSession.js`
- **Type:** JavaScript
- **Size:** 2.26 KB
- **Capabilities:**
  - Events that the CDPSession class emits.
  - Emitted when the session is ready to be configured during the auto-attach
  - The `CDPSession` instances are used to talk raw Chrome Devtools Protocol.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 4.83 KB
- **Capabilities:**
  - The default cooperative request interception resolution priority
  - Represents an HTTP request sent by a page.
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Browser.js`
- **Type:** JavaScript
- **Size:** 4.78 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 1.2 KB
- **Capabilities:**
  - The HTTPResponse class represents responses which are received by the
  - True if the response was successful (status in the range 200-299).
  - Promise which resolves to a text representation of response body.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Target.js`
- **Type:** JavaScript
- **Size:** 1.34 KB
- **Capabilities:**
  - Target represents a
  - If the target is not of type `"service_worker"` or `"shared_worker"`, returns `null`.
  - If the target is not of type `"page"`, `"webview"` or `"background_page"`,

#### locators
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/locators/locators.js`
- **Type:** JavaScript
- **Size:** 25.56 KB
- **Capabilities:**
  - All the events that a locator instance may emit.
  - Emitted every time before the locator performs an action on the located element(s).
  - Locators describe a strategy of locating objects and performing an action on
- **Methods:** 4

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Input.js`
- **Type:** JavaScript
- **Size:** 4.42 KB
- **Capabilities:**
  - Keyboard provides an api for managing a virtual keyboard.
  - Enum of valid mouse buttons.
  - The Mouse class operates in main-frame CSS pixels

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Realm.js`
- **Type:** JavaScript
- **Size:** 1.33 KB
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/Page.js`
- **Type:** JavaScript
- **Size:** 62.41 KB
- **Capabilities:**
  - Page provides methods to interact with a single tab or
  - Listen to page events.
  - Runs `document.querySelector` within the page. If no element matches the
- **Methods:** 14

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/JSHandle.js`
- **Type:** JavaScript
- **Size:** 10 KB
- **Capabilities:**
  - Represents a reference to a JavaScript object. Instances can be created using
  - Evaluates the given function with the current handle as its first argument.
  - Gets a map of handles representing the properties of the current handle.
- **Methods:** 6

#### api
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/api/api.js`
- **Type:** JavaScript
- **Size:** 1.64 KB

#### ErrorLike
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/ErrorLike.js`
- **Type:** JavaScript
- **Size:** 1.35 KB
- **Methods:** 4

#### Deferred
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Deferred.js`
- **Type:** JavaScript
- **Size:** 2.93 KB
- **Capabilities:**
  - Creates and returns a deferred object along with the resolve/reject functions.

#### disposable
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/disposable.js`
- **Type:** JavaScript
- **Size:** 6.63 KB
- **Capabilities:**
  - Returns a value indicating whether this stack has been disposed.
  - Disposes each resource in the stack in the reverse order that they were added.
  - Adds a disposable resource to the stack, returning the resource.

#### decorators
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/decorators.js`
- **Type:** JavaScript
- **Size:** 6.2 KB
- **Capabilities:**
  - The decorator only invokes the target if the target has not been invoked with
- **Methods:** 7

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/util.js`
- **Type:** JavaScript
- **Size:** 1.11 KB

#### AsyncIterableUtil
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/AsyncIterableUtil.js`
- **Type:** JavaScript
- **Size:** 847 B

#### Function
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Function.js`
- **Type:** JavaScript
- **Size:** 2.24 KB
- **Capabilities:**
  - Creates a function from a string.
  - Replaces `PLACEHOLDER`s with the given replacements.
- **Methods:** 4

#### Mutex
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/util/Mutex.js`
- **Type:** JavaScript
- **Size:** 1.07 KB

#### fetch
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/fetch.js`
- **Type:** JavaScript
- **Size:** 1.5 KB
- **Capabilities:**
  - Gets the global version if we're in the browser, else loads the node-fetch module.

#### ScriptInjector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ScriptInjector.js`
- **Type:** JavaScript
- **Size:** 1.25 KB

#### ConsoleMessage
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/ConsoleMessage.js`
- **Type:** JavaScript
- **Size:** 1.26 KB
- **Capabilities:**
  - ConsoleMessage objects are dispatched by page via the 'console' event.
  - The type of the console message.
  - The text of the console message.

#### LazyArg
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/LazyArg.js`
- **Type:** JavaScript
- **Size:** 620 B

#### WaitTask
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/WaitTask.js`
- **Type:** JavaScript
- **Size:** 6.87 KB
- **Capabilities:**
  - Not all errors lead to termination. They usually imply we need to rerun the task.

#### Debug
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Debug.js`
- **Type:** JavaScript
- **Size:** 3.99 KB
- **Capabilities:**
  - A debug function that can be used in any environment.
  - If the debug level is `foo*`, that means we match any prefix that
- **Methods:** 4

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 4.64 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
  - Establishes a websocket connection by given options and returns both transport and
- **Methods:** 3

#### TimeoutSettings
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TimeoutSettings.js`
- **Type:** JavaScript
- **Size:** 1.1 KB

#### XPathQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/XPathQueryHandler.js`
- **Type:** JavaScript
- **Size:** 804 B

#### Puppeteer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Puppeteer.js`
- **Type:** JavaScript
- **Size:** 2.94 KB
- **Capabilities:**
  - The main Puppeteer class.
  - Unregisters a custom query handler for a given name.
  - Gets the names of all custom query handlers.

#### NetworkManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/NetworkManagerEvents.js`
- **Type:** JavaScript
- **Size:** 992 B
- **Capabilities:**
  - We use symbols to prevent any external parties listening to these events.

#### EventEmitter
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/EventEmitter.js`
- **Type:** JavaScript
- **Size:** 5.13 KB
- **Capabilities:**
  - The EventEmitter class that many Puppeteer classes extend.
  - If you pass an emitter, the returned emitter will wrap the passed emitter.
  - Bind an event listener to fire when an event occurs.
- **Methods:** 2

#### SecurityDetails
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/SecurityDetails.js`
- **Type:** JavaScript
- **Size:** 1.88 KB
- **Capabilities:**
  - The SecurityDetails class represents the security details of a
  - The name of the issuer of the certificate.
  - The security protocol being used, e.g. "TLS 1.2".

#### Errors
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/Errors.js`
- **Type:** JavaScript
- **Size:** 2.65 KB
- **Capabilities:**
  - TimeoutError is emitted whenever certain operations are terminated due to
  - ProtocolError is emitted whenever there is an error from the protocol.
  - Puppeteer will throw this error if a method is not

#### CustomQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CustomQueryHandler.js`
- **Type:** JavaScript
- **Size:** 5.69 KB
- **Capabilities:**
  - Unregisters all custom query handlers.
- **Methods:** 4

#### BrowserWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/BrowserWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### PQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PQueryHandler.js`
- **Type:** JavaScript
- **Size:** 685 B

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/util.js`
- **Type:** JavaScript
- **Size:** 12.38 KB
- **Capabilities:**
  - Validate Dialog Type
- **Methods:** 11

#### common
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/common.js`
- **Type:** JavaScript
- **Size:** 2.63 KB

#### FileChooser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/FileChooser.js`
- **Type:** JavaScript
- **Size:** 2.5 KB
- **Capabilities:**
  - File choosers let you react to the page requesting for a file.
  - Whether file chooser allow for
  - Accept the file chooser request with the given file paths.

#### CallbackRegistry
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/CallbackRegistry.js`
- **Type:** JavaScript
- **Size:** 4.15 KB
- **Capabilities:**
  - Manages callbacks and their IDs for the protocol request/response communication.
- **Methods:** 1

#### GetQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/GetQueryHandler.js`
- **Type:** JavaScript
- **Size:** 1.77 KB
- **Methods:** 1

#### TextQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TextQueryHandler.js`
- **Type:** JavaScript
- **Size:** 577 B

#### HandleIterator
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/HandleIterator.js`
- **Type:** JavaScript
- **Size:** 4.88 KB
- **Capabilities:**
  - This will transpose an iterator JSHandle into a fast, Puppeteer-side iterator
  - This will transpose an iterator JSHandle in batches based on the default size
- **Methods:** 2

#### PierceQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/PierceQueryHandler.js`
- **Type:** JavaScript
- **Size:** 730 B

#### TaskQueue
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/TaskQueue.js`
- **Type:** JavaScript
- **Size:** 612 B

#### USKeyboardLayout
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/USKeyboardLayout.js`
- **Type:** JavaScript
- **Size:** 16.63 KB

#### QueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/common/QueryHandler.js`
- **Type:** JavaScript
- **Size:** 8.77 KB
- **Capabilities:**
  - Waits until a single node appears for a given selector and
- **Methods:** 2

#### NodeWebSocketTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/NodeWebSocketTransport.js`
- **Type:** JavaScript
- **Size:** 1.85 KB

#### ScreenRecorder
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ScreenRecorder.js`
- **Type:** JavaScript
- **Size:** 11.07 KB
- **Capabilities:**
  - Stops the recorder.
- **Methods:** 2

#### fs
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/util/fs.js`
- **Type:** JavaScript
- **Size:** 731 B
- **Methods:** 2

#### ProductLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ProductLauncher.js`
- **Type:** JavaScript
- **Size:** 12.21 KB
- **Capabilities:**
  - Describes a launcher - a class that is able to create and launch a browser instance.
  - Set only for Firefox, after the launcher resolves the `latest` revision to
- **Methods:** 1

#### PuppeteerNode
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PuppeteerNode.js`
- **Type:** JavaScript
- **Size:** 10.08 KB
- **Capabilities:**
  - This method attaches Puppeteer to an existing browser instance.
  - Launches a browser instance with given arguments and options when
  - The default executable path.

#### PipeTransport
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/PipeTransport.js`
- **Type:** JavaScript
- **Size:** 2.31 KB

#### FirefoxLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/FirefoxLauncher.js`
- **Type:** JavaScript
- **Size:** 7.34 KB

#### ChromeLauncher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/ChromeLauncher.js`
- **Type:** JavaScript
- **Size:** 10.58 KB
- **Capabilities:**
  - Extracts all features from the given command-line flag
  - Removes all elements in-place from the given string array
- **Methods:** 3

#### node
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/node/node.js`
- **Type:** JavaScript
- **Size:** 1.23 KB

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Dialog.js`
- **Type:** JavaScript
- **Size:** 838 B

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 4.42 KB

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Frame.js`
- **Type:** JavaScript
- **Size:** 12.56 KB
- **Capabilities:**
  - Puppeteer's Frame class could be viewed as a BiDi BrowsingContext implementation
- **Methods:** 2

#### BrowserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserContext.js`
- **Type:** JavaScript
- **Size:** 3.36 KB

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 7.76 KB
- **Methods:** 4

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 3.1 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Browser.js`
- **Type:** JavaScript
- **Size:** 8.89 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 2.06 KB

#### lifecycle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/lifecycle.js`
- **Type:** JavaScript
- **Size:** 2.62 KB
- **Methods:** 4

#### Serializer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Serializer.js`
- **Type:** JavaScript
- **Size:** 4.61 KB

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Target.js`
- **Type:** JavaScript
- **Size:** 3.09 KB

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 4.73 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling `puppeteer.connect`
  - Returns a BiDiConnection established to the endpoint specified by the options and a
- **Methods:** 2

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Connection.js`
- **Type:** JavaScript
- **Size:** 6.35 KB
- **Capabilities:**
  - Unbinds the connection, but keeps the transport open. Useful when the transport will
  - Unbinds the connection and closes the transport.
- **Methods:** 2

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 881 B

#### Deserializer
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Deserializer.js`
- **Type:** JavaScript
- **Size:** 3 KB

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 4.7 KB
- **Capabilities:**
  - Internal events that the BrowsingContext class emits.
  - Emitted on the top-level context, when a descendant context is created.
  - Emitted on the top-level context, when a descendant context or the

#### bidi
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/bidi.js`
- **Type:** JavaScript
- **Size:** 1.64 KB

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/util.js`
- **Type:** JavaScript
- **Size:** 2.3 KB
- **Methods:** 3

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Input.js`
- **Type:** JavaScript
- **Size:** 18.39 KB

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Realm.js`
- **Type:** JavaScript
- **Size:** 6.73 KB
- **Methods:** 1

#### ExposedFunction
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/ExposedFunction.js`
- **Type:** JavaScript
- **Size:** 9.62 KB

#### Sandbox
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Sandbox.js`
- **Type:** JavaScript
- **Size:** 2.46 KB

#### BidiOverCdp
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/BidiOverCdp.js`
- **Type:** JavaScript
- **Size:** 5.54 KB
- **Capabilities:**
  - Manages CDPSessions for BidiServer.
  - Wrapper on top of CDPSession/CDPConnection to satisfy CDP interface that
  - This transport is given to the BiDi server instance and allows Puppeteer
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/Page.js`
- **Type:** JavaScript
- **Size:** 27.44 KB
- **Methods:** 6

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.12 KB

#### core
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/core.js`
- **Type:** JavaScript
- **Size:** 1.3 KB

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Browser.js`
- **Type:** JavaScript
- **Size:** 13.21 KB
- **Methods:** 4

#### Navigation
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Navigation.js`
- **Type:** JavaScript
- **Size:** 6.92 KB
- **Methods:** 2

#### Session
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Session.js`
- **Type:** JavaScript
- **Size:** 9.14 KB
- **Capabilities:**
  - Currently, there is a 1:1 relationship between the session and the
- **Methods:** 2

#### BrowsingContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/BrowsingContext.js`
- **Type:** JavaScript
- **Size:** 19.42 KB
- **Methods:** 2

#### Request
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Request.js`
- **Type:** JavaScript
- **Size:** 6.91 KB
- **Methods:** 2

#### Realm
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/Realm.js`
- **Type:** JavaScript
- **Size:** 12.12 KB
- **Methods:** 2

#### UserPrompt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/UserPrompt.js`
- **Type:** JavaScript
- **Size:** 6.44 KB
- **Methods:** 2

#### UserContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/bidi/core/UserContext.js`
- **Type:** JavaScript
- **Size:** 8.23 KB
- **Methods:** 2

#### injected
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/generated/injected.js`
- **Type:** JavaScript
- **Size:** 13.95 KB
- **Capabilities:**
  - JavaScript code that provides the puppeteer utilities. See the
- **Methods:** 6

#### WebWorker
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/WebWorker.js`
- **Type:** JavaScript
- **Size:** 1.77 KB

#### Dialog
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Dialog.js`
- **Type:** JavaScript
- **Size:** 715 B

#### FrameTree
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameTree.js`
- **Type:** JavaScript
- **Size:** 2.77 KB
- **Capabilities:**
  - Keeps track of the page frame tree and it's is managed by
  - Returns a promise that is resolved once the frame with

#### FirefoxTargetManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FirefoxTargetManager.js`
- **Type:** JavaScript
- **Size:** 6.48 KB
- **Capabilities:**
  - FirefoxTargetManager implements target management using
  - Keeps track of the following events: 'Target.targetCreated',
  - Keeps track of targets that were created via 'Target.targetCreated'

#### NetworkManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkManager.js`
- **Type:** JavaScript
- **Size:** 21.1 KB
- **Capabilities:**
  - CDP may have sent a Fetch.requestPaused event already. Check for it.
  - CDP may send a Fetch.requestPaused without or before a

#### Frame
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Frame.js`
- **Type:** JavaScript
- **Size:** 12.94 KB
- **Capabilities:**
  - This is used internally in DevTools.
  - Updates the frame ID with the new ID. This happens when the main frame is
- **Methods:** 3

#### FrameManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManager.js`
- **Type:** JavaScript
- **Size:** 17.93 KB
- **Capabilities:**
  - Set of frame IDs stored to indicate if a frame has received a
  - Called when the frame's client is disconnected. We don't know if the
  - When the main frame is replaced by another main frame,

#### ElementHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ElementHandle.js`
- **Type:** JavaScript
- **Size:** 9.98 KB
- **Capabilities:**
  - The CdpElementHandle extends ElementHandle now to keep compatibility
  - The zero-length array is a special case, it seems that
- **Methods:** 2

#### CDPSession
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/CDPSession.js`
- **Type:** JavaScript
- **Size:** 3.51 KB
- **Capabilities:**
  - Detaches the cdpSession from the target. Once detached, the cdpSession object
  - Returns the session's id.

#### HTTPRequest
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPRequest.js`
- **Type:** JavaScript
- **Size:** 11.89 KB
- **Methods:** 1

#### Browser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Browser.js`
- **Type:** JavaScript
- **Size:** 12.79 KB

#### HTTPResponse
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/HTTPResponse.js`
- **Type:** JavaScript
- **Size:** 4.13 KB

#### Accessibility
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Accessibility.js`
- **Type:** JavaScript
- **Size:** 13.45 KB
- **Capabilities:**
  - The Accessibility class provides methods for inspecting the browser's
  - Captures the current state of the accessibility tree.
- **Methods:** 1

#### Tracing
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Tracing.js`
- **Type:** JavaScript
- **Size:** 3.7 KB
- **Capabilities:**
  - The Tracing class exposes the tracing audit interface.
  - Starts a trace for the current page.
  - Stops a trace started with the `start` method.

#### Target
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Target.js`
- **Type:** JavaScript
- **Size:** 7.77 KB
- **Capabilities:**
  - To initialize the target for use, call initialize.

#### BrowserConnector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/BrowserConnector.js`
- **Type:** JavaScript
- **Size:** 1.46 KB
- **Capabilities:**
  - Users should never call this directly; it's called when calling
- **Methods:** 1

#### Connection
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Connection.js`
- **Type:** JavaScript
- **Size:** 7.02 KB
- **Methods:** 1

#### EmulationManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/EmulationManager.js`
- **Type:** JavaScript
- **Size:** 21.98 KB
- **Capabilities:**
  - Resets default white background
  - Hides default white background
- **Methods:** 2

#### FrameManagerEvents
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/FrameManagerEvents.js`
- **Type:** JavaScript
- **Size:** 1.04 KB
- **Capabilities:**
  - We use symbols to prevent external parties listening to these events.

#### ChromeTargetManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ChromeTargetManager.js`
- **Type:** JavaScript
- **Size:** 13.19 KB
- **Capabilities:**
  - ChromeTargetManager uses the CDP's auto-attach mechanism to intercept
  - Keeps track of the following events: 'Target.targetCreated',
  - A target is added to this map once ChromeTargetManager has created
- **Methods:** 1

#### IsolatedWorld
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/IsolatedWorld.js`
- **Type:** JavaScript
- **Size:** 9.32 KB
- **Methods:** 2

#### LifecycleWatcher
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/LifecycleWatcher.js`
- **Type:** JavaScript
- **Size:** 7.36 KB
- **Capabilities:**
  - Check Lifecycle
- **Methods:** 1

#### Input
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Input.js`
- **Type:** JavaScript
- **Size:** 15.2 KB
- **Capabilities:**
  - This should match
  - This is a shortcut for a typical update, commit/rollback lifecycle based on

#### utils
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/utils.js`
- **Type:** JavaScript
- **Size:** 6.84 KB
- **Methods:** 6

#### DeviceRequestPrompt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/DeviceRequestPrompt.js`
- **Type:** JavaScript
- **Size:** 6.77 KB
- **Capabilities:**
  - Device in a request prompt.
  - Device id during a prompt.
  - Device name as it appears in a prompt.

#### cdp
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/cdp.js`
- **Type:** JavaScript
- **Size:** 2.71 KB

#### AriaQueryHandler
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/AriaQueryHandler.js`
- **Type:** JavaScript
- **Size:** 3.02 KB
- **Capabilities:**
  - The selectors consist of an accessible name to query for and optionally

#### ExecutionContext
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/ExecutionContext.js`
- **Type:** JavaScript
- **Size:** 11.47 KB
- **Capabilities:**
  - Evaluates the given function.
- **Methods:** 3

#### NetworkEventManager
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/NetworkEventManager.js`
- **Type:** JavaScript
- **Size:** 5.31 KB
- **Capabilities:**
  - Helper class to track network events by request ID
  - There are four possible orders of events:

#### Binding
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Binding.js`
- **Type:** JavaScript
- **Size:** 6.03 KB
- **Methods:** 2

#### Coverage
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Coverage.js`
- **Type:** JavaScript
- **Size:** 13.38 KB
- **Capabilities:**
  - The Coverage class provides methods to gather information about parts of
  - Promise that resolves to the array of coverage reports for
  - Promise that resolves to the array of coverage reports
- **Methods:** 1

#### Page
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/Page.js`
- **Type:** JavaScript
- **Size:** 36.63 KB
- **Capabilities:**
  - Sets up listeners for the primary target. The primary target can change
  - This method is typically coupled with an action that triggers a device
- **Methods:** 3

#### JSHandle
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/cdp/JSHandle.js`
- **Type:** JavaScript
- **Size:** 2.47 KB
- **Capabilities:**
  - Either `null` or the handle itself if the handle is an
- **Methods:** 1

#### CustomQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/CustomQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.57 KB

#### PSelectorParser
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/PSelectorParser.js`
- **Type:** JavaScript
- **Size:** 3.29 KB
- **Methods:** 1

#### XPathQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/XPathQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.01 KB

#### injected
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/injected.js`
- **Type:** JavaScript
- **Size:** 2.53 KB

#### TextQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextQuerySelector.js`
- **Type:** JavaScript
- **Size:** 1.36 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### Poller
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/Poller.js`
- **Type:** JavaScript
- **Size:** 3.94 KB

#### PQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/PQuerySelector.js`
- **Type:** JavaScript
- **Size:** 8.91 KB
- **Capabilities:**
  - Queries the given node for all nodes matching the given text selector.

#### ARIAQuerySelector
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/ARIAQuerySelector.js`
- **Type:** JavaScript
- **Size:** 615 B

#### util
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/util.js`
- **Type:** JavaScript
- **Size:** 1.71 KB
- **Methods:** 1

#### TextContent
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/puppeteer/injected/TextContent.js`
- **Type:** JavaScript
- **Size:** 3.6 KB
- **Capabilities:**
  - Determines if the node has a non-trivial value property.
  - Determines whether a given node is suitable for text matching.
  - Erases the cache when the tree has mutated text.
- **Methods:** 1

#### rxjs
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/third_party/rxjs/rxjs.js`
- **Type:** JavaScript
- **Size:** 147.32 KB
- **Methods:** 20

#### mitt
- **File:** `frontend-tools/node_modules/puppeteer-core/lib/cjs/third_party/mitt/mitt.js`
- **Type:** JavaScript
- **Size:** 1.61 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/es-errors/test/index.js`
- **Type:** JavaScript
- **Size:** 356 B

#### index
- **File:** `frontend-tools/node_modules/bare-url/index.js`
- **Type:** JavaScript
- **Size:** 9.28 KB
- **Methods:** 8

#### url-search-params
- **File:** `frontend-tools/node_modules/bare-url/lib/url-search-params.js`
- **Type:** JavaScript
- **Size:** 3.79 KB

#### errors
- **File:** `frontend-tools/node_modules/bare-url/lib/errors.js`
- **Type:** JavaScript
- **Size:** 881 B

#### index
- **File:** `frontend-tools/node_modules/y18n/build/lib/index.js`
- **Type:** JavaScript
- **Size:** 6.12 KB
- **Methods:** 1

#### tslib.es6
- **File:** `frontend-tools/node_modules/tslib/tslib.es6.js`
- **Type:** JavaScript
- **Size:** 10.03 KB
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/tslib/modules/index.js`
- **Type:** JavaScript
- **Size:** 943 B

#### tslib
- **File:** `frontend-tools/node_modules/tslib/tslib.js`
- **Type:** JavaScript
- **Size:** 12.89 KB
- **Methods:** 11

#### types
- **File:** `frontend-tools/node_modules/zod/lib/types.js`
- **Type:** JavaScript
- **Size:** 132.13 KB
- **Capabilities:**
  - The constructor of the discriminated union schema. Its behaviour is very similar to that of the normal z.union() constructor.
- **Methods:** 16

#### index.umd
- **File:** `frontend-tools/node_modules/zod/lib/index.umd.js`
- **Type:** JavaScript
- **Size:** 165.42 KB
- **Capabilities:**
  - The constructor of the discriminated union schema. Its behaviour is very similar to that of the normal z.union() constructor.
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/zod/lib/index.js`
- **Type:** JavaScript
- **Size:** 1.26 KB

#### discriminatedUnion
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/discriminatedUnion.js`
- **Type:** JavaScript
- **Size:** 1.88 KB

#### primitives
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/primitives.js`
- **Type:** JavaScript
- **Size:** 3.78 KB

#### object
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/object.js`
- **Type:** JavaScript
- **Size:** 1.86 KB

#### index
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/index.js`
- **Type:** JavaScript
- **Size:** 1.91 KB

#### datetime
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/datetime.js`
- **Type:** JavaScript
- **Size:** 2.1 KB

#### realworld
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/realworld.js`
- **Type:** JavaScript
- **Size:** 1.5 KB
- **Methods:** 3

#### string
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/string.js`
- **Type:** JavaScript
- **Size:** 1.49 KB

#### union
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/union.js`
- **Type:** JavaScript
- **Size:** 1.81 KB

#### ipv4
- **File:** `frontend-tools/node_modules/zod/lib/benchmarks/ipv4.js`
- **Type:** JavaScript
- **Size:** 2 KB

#### external
- **File:** `frontend-tools/node_modules/zod/lib/external.js`
- **Type:** JavaScript
- **Size:** 884 B

#### en
- **File:** `frontend-tools/node_modules/zod/lib/locales/en.js`
- **Type:** JavaScript
- **Size:** 5.98 KB
- **Methods:** 2

#### Mocker
- **File:** `frontend-tools/node_modules/zod/lib/__tests__/Mocker.js`
- **Type:** JavaScript
- **Size:** 1.4 KB
- **Methods:** 1

#### errors
- **File:** `frontend-tools/node_modules/zod/lib/errors.js`
- **Type:** JavaScript
- **Size:** 610 B
- **Methods:** 2

#### enumUtil
- **File:** `frontend-tools/node_modules/zod/lib/helpers/enumUtil.js`
- **Type:** JavaScript
- **Size:** 77 B

#### partialUtil
- **File:** `frontend-tools/node_modules/zod/lib/helpers/partialUtil.js`
- **Type:** JavaScript
- **Size:** 77 B

#### errorUtil
- **File:** `frontend-tools/node_modules/zod/lib/helpers/errorUtil.js`
- **Type:** JavaScript
- **Size:** 449 B

#### parseUtil
- **File:** `frontend-tools/node_modules/zod/lib/helpers/parseUtil.js`
- **Type:** JavaScript
- **Size:** 4.09 KB
- **Methods:** 1

#### util
- **File:** `frontend-tools/node_modules/zod/lib/helpers/util.js`
- **Type:** JavaScript
- **Size:** 4.37 KB
- **Methods:** 3

#### ZodError
- **File:** `frontend-tools/node_modules/zod/lib/ZodError.js`
- **Type:** JavaScript
- **Size:** 4.42 KB

#### index
- **File:** `frontend-tools/node_modules/is-typedarray/index.js`
- **Type:** JavaScript
- **Size:** 1016 B
- **Methods:** 3

#### layout-manager
- **File:** `frontend-tools/node_modules/cli-table3/src/layout-manager.js`
- **Type:** JavaScript
- **Size:** 6.89 KB
- **Methods:** 14

#### table
- **File:** `frontend-tools/node_modules/cli-table3/src/table.js`
- **Type:** JavaScript
- **Size:** 2.61 KB
- **Methods:** 1

#### cell
- **File:** `frontend-tools/node_modules/cli-table3/src/cell.js`
- **Type:** JavaScript
- **Size:** 13.35 KB
- **Capabilities:**
  - A representation of a cell within the table.
  - Each cell will have it's `x` and `y` values set by the `layout-manager` prior to
  - Initializes the Cells data structure.
- **Methods:** 4

#### utils
- **File:** `frontend-tools/node_modules/cli-table3/src/utils.js`
- **Type:** JavaScript
- **Size:** 8.09 KB
- **Capabilities:**
  - Credit: Matheus Sampaio https://github.com/matheussampaio
  - Update State
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/bare-stream/index.js`
- **Type:** JavaScript
- **Size:** 7.48 KB
- **Methods:** 8

#### web
- **File:** `frontend-tools/node_modules/bare-stream/web.js`
- **Type:** JavaScript
- **Size:** 4.88 KB
- **Methods:** 9

#### index
- **File:** `frontend-tools/node_modules/buffer/index.js`
- **Type:** JavaScript
- **Size:** 48.92 KB
- **Capabilities:**
  - If `Buffer.TYPED_ARRAY_SUPPORT`:
  - The Buffer constructor returns instances of `Uint8Array` that have their
  - Functionally equivalent to Buffer(arg, encoding) but throws a TypeError
- **Methods:** 20

#### index
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/index.js`
- **Type:** JavaScript
- **Size:** 511 B
- **Methods:** 2

#### index
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/test/index.js`
- **Type:** JavaScript
- **Size:** 2.6 KB
- **Methods:** 1

#### functionCall
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/functionCall.js`
- **Type:** JavaScript
- **Size:** 97 B

#### applyBind
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/applyBind.js`
- **Type:** JavaScript
- **Size:** 264 B
- **Methods:** 1

#### actualApply
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/actualApply.js`
- **Type:** JavaScript
- **Size:** 280 B

#### functionApply
- **File:** `frontend-tools/node_modules/call-bind-apply-helpers/functionApply.js`
- **Type:** JavaScript
- **Size:** 99 B

#### index
- **File:** `frontend-tools/node_modules/get-caller-file/index.js`
- **Type:** JavaScript
- **Size:** 1.08 KB
- **Methods:** 4

#### index
- **File:** `frontend-tools/node_modules/simple-swizzle/node_modules/is-arrayish/index.js`
- **Type:** JavaScript
- **Size:** 318 B
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/simple-swizzle/index.js`
- **Type:** JavaScript
- **Size:** 571 B
- **Methods:** 1

#### colors
- **File:** `frontend-tools/node_modules/colors/lib/colors.js`
- **Type:** JavaScript
- **Size:** 5.73 KB
- **Methods:** 8

#### styles
- **File:** `frontend-tools/node_modules/colors/lib/styles.js`
- **Type:** JavaScript
- **Size:** 2.45 KB

#### extendStringPrototype
- **File:** `frontend-tools/node_modules/colors/lib/extendStringPrototype.js`
- **Type:** JavaScript
- **Size:** 3.22 KB
- **Methods:** 1

#### supports-colors
- **File:** `frontend-tools/node_modules/colors/lib/system/supports-colors.js`
- **Type:** JavaScript
- **Size:** 3.95 KB
- **Methods:** 3

#### has-flag
- **File:** `frontend-tools/node_modules/colors/lib/system/has-flag.js`
- **Type:** JavaScript
- **Size:** 1.38 KB

#### trap
- **File:** `frontend-tools/node_modules/colors/lib/custom/trap.js`
- **Type:** JavaScript
- **Size:** 1.64 KB
- **Methods:** 1

#### zalgo
- **File:** `frontend-tools/node_modules/colors/lib/custom/zalgo.js`
- **Type:** JavaScript
- **Size:** 2.82 KB
- **Methods:** 4

#### rainbow
- **File:** `frontend-tools/node_modules/colors/lib/maps/rainbow.js`
- **Type:** JavaScript
- **Size:** 311 B

#### zebra
- **File:** `frontend-tools/node_modules/colors/lib/maps/zebra.js`
- **Type:** JavaScript
- **Size:** 146 B

#### random
- **File:** `frontend-tools/node_modules/colors/lib/maps/random.js`
- **Type:** JavaScript
- **Size:** 454 B

#### america
- **File:** `frontend-tools/node_modules/colors/lib/maps/america.js`
- **Type:** JavaScript
- **Size:** 278 B

#### netmask
- **File:** `frontend-tools/node_modules/netmask/tests/netmask.js`
- **Type:** JavaScript
- **Size:** 3.39 KB

#### netmask
- **File:** `frontend-tools/node_modules/netmask/lib/netmask.js`
- **Type:** JavaScript
- **Size:** 5.45 KB
- **Methods:** 1

#### index
- **File:** `frontend-tools/node_modules/escalade/dist/index.js`
- **Type:** JavaScript
- **Size:** 534 B

#### index
- **File:** `frontend-tools/node_modules/escalade/sync/index.js`
- **Type:** JavaScript
- **Size:** 416 B

#### index
- **File:** `frontend-tools/node_modules/hasown/index.js`
- **Type:** JavaScript
- **Size:** 206 B

#### index
- **File:** `frontend-tools/node_modules/error-ex/index.js`
- **Type:** JavaScript
- **Size:** 2.84 KB
- **Methods:** 2

#### sse-server
- **File:** `frontend-tools/server/sse-server.js`
- **Type:** JavaScript
- **Size:** 62.1 KB
- **Capabilities:**
  - Intelligence Hub - SSE Live Streaming Server
  - Validate Api Key
  - Validate I P
- **Methods:** 20

#### verify-functions
- **File:** `frontend-tools/tests/verify-functions.js`
- **Type:** JavaScript
- **Size:** 13.58 KB
- **Capabilities:**
  - Quick Page Audit - Function Verification
  - Test
- **Methods:** 16

#### cron-audit-scanner
- **File:** `frontend-tools/cron-audit-scanner.js`
- **Type:** JavaScript
- **Size:** 16.8 KB
- **Capabilities:**
  - Audit Scanner Cron Job
  - Main scan entry point
  - Ensure base path exists
- **Methods:** 1

---

### Database (1 tools)

#### DatabaseValidator
- **File:** `services/DatabaseValidator.php`
- **Type:** PHP
- **Size:** 27.86 KB
- **Capabilities:**
  - Database Validator & Auto-Correction Tool
  - Initialize validator with database connection
  - Load database connection from config (uses CredentialManager)
- **Methods:** 20

---

### Security (1 tools)

#### SecurityMonitor
- **File:** `services/SecurityMonitor.php`
- **Type:** PHP
- **Size:** 3.01 KB
- **Capabilities:**
  - Security Monitoring Service
- **Methods:** 3

---

### Deployment (3 tools)

#### deploy_intelligence_client
- **File:** `api/intelligence/deploy_intelligence_client.php`
- **Type:** PHP
- **Size:** 16.22 KB
- **Capabilities:**
  - Intelligence API Client - Quick Deploy Script
  - Intelligence API Configuration
  - Intelligence Bot Interface
- **Methods:** 1

#### satellite-deploy
- **File:** `api/satellite-deploy.php`
- **Type:** PHP
- **Size:** 45.61 KB
- **Capabilities:**
  - Satellite Deployment API
  - Multi-Bot Conversation Manager
  - Multi-Bot Collaboration API
- **Methods:** 17

#### deploy_satellite_scanners
- **File:** `api/deploy_satellite_scanners.php`
- **Type:** PHP
- **Size:** 11.99 KB
- **Capabilities:**
  - Deploy Intelligence Scan Endpoints to All Satellites
  - Intelligence Hub - Satellite Scan Endpoint
  - Scan Directory
- **Methods:** 5

---

### Testing (1 tools)

#### FrontendTester
- **File:** `services/FrontendTester.php`
- **Type:** PHP
- **Size:** 17.85 KB
- **Capabilities:**
  - Frontend Tester Service
  - Test a page with full analysis
  - Capture screenshots at different viewports
- **Methods:** 14

---

### Utilities (33 tools)

#### process_content_remote
- **File:** `api/process_content_remote.php`
- **Type:** PHP
- **Size:** 6.88 KB
- **Capabilities:**
  - Security headers automatically added by hardening script
  - Remote Content Processing API

#### bot-prompt
- **File:** `api/bot-prompt.php`
- **Type:** PHP
- **Size:** 4.43 KB
- **Capabilities:**
  - Bot Prompt Builder API
- **Methods:** 1

#### agent_kb
- **File:** `api/agent_kb.php`
- **Type:** PHP
- **Size:** 13.46 KB
- **Capabilities:**
  - Agent Knowledge Base API
  - Route to appropriate handler
  - Query specific topic/file
- **Methods:** 16

#### receive_satellite_data
- **File:** `api/receive_satellite_data.php`
- **Type:** PHP
- **Size:** 9.81 KB
- **Capabilities:**
  - Intelligence Hub - Satellite Data Receiver

#### ai-control
- **File:** `api/ai-control.php`
- **Type:** PHP
- **Size:** 29.88 KB
- **Capabilities:**
  - AI Control Dashboard API
  - Main API router
  - Get current settings
- **Methods:** 20

#### ai-chat
- **File:** `api/ai-chat.php`
- **Type:** PHP
- **Size:** 7.21 KB
- **Capabilities:**
  - Intelligence Hub - AI Chat API
  - Simple rate limiter using file-based storage
  - Sanitize user input
- **Methods:** 3

#### save_conversation
- **File:** `api/save_conversation.php`
- **Type:** PHP
- **Size:** 7.15 KB
- **Capabilities:**
  - AI Conversation Recorder API

#### multi-bot-collaboration
- **File:** `api/multi-bot-collaboration.php`
- **Type:** PHP
- **Size:** 16.58 KB
- **Capabilities:**
  - Multi-Bot Collaboration API - LIVE SYSTEM
  - Analyze C I S Performance
  - Check Real Data Availability
- **Methods:** 16

#### update_intelligence_schema
- **File:** `api/intelligence/update_intelligence_schema.php`
- **Type:** PHP
- **Size:** 2.6 KB
- **Capabilities:**
  - Update Intelligence Files Schema
- **Methods:** 1

#### intelligent_scorer
- **File:** `api/intelligence/intelligent_scorer.php`
- **Type:** PHP
- **Size:** 6.46 KB
- **Capabilities:**
  - Intelligent File Scorer
- **Methods:** 1

#### neural_intelligence_processor
- **File:** `api/intelligence/neural_intelligence_processor.php`
- **Type:** PHP
- **Size:** 28.19 KB
- **Capabilities:**
  - Neural Intelligence Processor
  - Process all files for a server
  - Process individual file with REAL intelligence
- **Methods:** 20

#### load_ignore_config
- **File:** `api/intelligence/load_ignore_config.php`
- **Type:** PHP
- **Size:** 2.38 KB
- **Capabilities:**
  - Load KB Ignore Config into Database

#### check_functions
- **File:** `api/intelligence/check_functions.php`
- **Type:** PHP
- **Size:** 1.98 KB
- **Capabilities:**
  - Check Function Extraction Results
- **Methods:** 1

#### api_neural_scanner
- **File:** `api/intelligence/api_neural_scanner.php`
- **Type:** PHP
- **Size:** 26.71 KB
- **Capabilities:**
  - API-Based Neural Intelligence Scanner
  - API-Based Neural Scanner
  - Load ignore patterns from database
- **Methods:** 17

#### index
- **File:** `api/intelligence/index.php`
- **Type:** PHP
- **Size:** 23.13 KB
- **Capabilities:**
  - Intelligence API - Central REST API for Centralized Intelligence System
  - API Response Handler
  - Authenticate API request
- **Methods:** 20

#### IntelligenceAPIClient
- **File:** `api/intelligence/IntelligenceAPIClient.php`
- **Type:** PHP
- **Size:** 14.85 KB
- **Capabilities:**
  - Intelligence API Client
  - Constructor
  - Search centralized intelligence
- **Methods:** 20

#### broadcast-to-copilots
- **File:** `api/broadcast-to-copilots.php`
- **Type:** PHP
- **Size:** 14.02 KB
- **Capabilities:**
  - Broadcast Instructions to All GitHub Copilot Instances
- **Methods:** 12

#### db-validate
- **File:** `api/db-validate.php`
- **Type:** PHP
- **Size:** 7.35 KB
- **Capabilities:**
  - Database Validator API
- **Methods:** 1

#### process_content_batch
- **File:** `api/process_content_batch.php`
- **Type:** PHP
- **Size:** 12.46 KB
- **Capabilities:**
  - HTTP API: Process Content Text in Batches
  - Process a single file
  - Analyze Text
- **Methods:** 7

#### CSRFProtection
- **File:** `services/CSRFProtection.php`
- **Type:** PHP
- **Size:** 1.64 KB
- **Capabilities:**
  - CSRF Protection Service
  - Validate Token
- **Methods:** 5

#### run_verified_kb_pipeline
- **File:** `services/run_verified_kb_pipeline.php`
- **Type:** PHP
- **Size:** 9.44 KB
- **Capabilities:**
  - COMPLETE KB PIPELINE RUNNER - CORRECTED VERSION
- **Methods:** 2

#### CredentialManager
- **File:** `services/CredentialManager.php`
- **Type:** PHP
- **Size:** 14.43 KB
- **Capabilities:**
  - Credential Manager Service
  - Constructor
  - Get or create encryption key
- **Methods:** 17

#### InputValidator
- **File:** `services/InputValidator.php`
- **Type:** PHP
- **Size:** 2.01 KB
- **Capabilities:**
  - Input Validation Service
  - Validate Email
  - Validate Integer
- **Methods:** 7

#### RateLimiter
- **File:** `services/RateLimiter.php`
- **Type:** PHP
- **Size:** 2.36 KB
- **Capabilities:**
  - Rate Limiting Service
  - Check
- **Methods:** 3

#### BotStandardsExpanded
- **File:** `services/BotStandardsExpanded.php`
- **Type:** PHP
- **Size:** 33.6 KB
- **Capabilities:**
  - Bot Standards - MEGA EXPANSION
  - Get all standards (1000+ rules)
  - Get programming styles/paradigms
- **Methods:** 7

#### run_complete_kb_pipeline
- **File:** `services/run_complete_kb_pipeline.php`
- **Type:** PHP
- **Size:** 8.51 KB
- **Capabilities:**
  - COMPLETE KB PIPELINE RUNNER
- **Methods:** 2

#### kb_data_validation_final
- **File:** `services/kb_data_validation_final.php`
- **Type:** PHP
- **Size:** 20.82 KB
- **Capabilities:**
  - CIS KB DATA VALIDATION & QUALITY AUDIT - FINAL VERSION

#### kb_data_validation_corrected
- **File:** `services/kb_data_validation_corrected.php`
- **Type:** PHP
- **Size:** 21.15 KB
- **Capabilities:**
  - CIS KB DATA VALIDATION & QUALITY AUDIT - CORRECTED VERSION

#### kb_pipeline_analyzer
- **File:** `services/kb_pipeline_analyzer.php`
- **Type:** PHP
- **Size:** 14.97 KB
- **Capabilities:**
  - CIS KNOWLEDGE BASE PIPELINE ANALYZER

#### AIAgentClient
- **File:** `services/AIAgentClient.php`
- **Type:** PHP
- **Size:** 6.95 KB
- **Capabilities:**
  - AI Agent Client - Connects to CIS AI Agent Service
  - Send message to AI agent
  - Stream chat response (SSE)
- **Methods:** 9

#### BotPromptBuilder
- **File:** `services/BotPromptBuilder.php`
- **Type:** PHP
- **Size:** 40.8 KB
- **Capabilities:**
  - Bot Prompt Builder & Standards Enforcer
  - Load coding standards and rules
  - Load bot templates for different tasks
- **Methods:** 14

#### kb_data_validation
- **File:** `services/kb_data_validation.php`
- **Type:** PHP
- **Size:** 17.2 KB
- **Capabilities:**
  - CIS KB DATA VALIDATION & QUALITY AUDIT

#### cloudways_cron_api
- **File:** `services/cloudways_cron_api.php`
- **Type:** PHP
- **Size:** 10.19 KB
- **Capabilities:**
  - Cloudways Cron API Integration
  - Authenticate with Cloudways API
  - Make API request
- **Methods:** 9

---

