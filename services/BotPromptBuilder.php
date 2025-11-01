<?php
/**
 * Bot Prompt Builder & Standards Enforcer
 * 
 * Generates custom bot prompts based on task type with enforced standards
 * 
 * Features:
 * - 20+ pre-defined bot templates (Database Dev, Frontend Dev, API Builder, etc.)
 * - Standards checklist enforcement (security, performance, code quality)
 * - Dynamic prompt generation based on project context
 * - Quality gates and validation rules
 * - Context-aware suggestions
 * 
 * @package CIS\Services
 * @version 1.0.0
 */

declare(strict_types=1);

class BotPromptBuilder
{
    private array $standards = [];
    private array $templates = [];
    private array $projectContext = [];
    
    public function __construct()
    {
        $this->loadStandards();
        $this->loadTemplates();
        $this->loadProjectContext();
    }
    
    /**
     * Load coding standards and rules
     * Each rule now has: id, text, priority, enabled (default)
     */
    private function loadStandards(): void
    {
        $this->standards = [
            'security' => [
                'name' => 'Security Standards',
                'priority' => 'CRITICAL',
                'description' => 'Critical security practices to prevent vulnerabilities',
                'rules' => [
                    ['id' => 'sec_001', 'text' => 'No hard-coded credentials - use .env or config', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_002', 'text' => 'Always use prepared statements for SQL (PDO::prepare)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_003', 'text' => 'Escape all HTML output with htmlspecialchars()', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_004', 'text' => 'Validate and sanitize ALL user input', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_005', 'text' => 'Use CSRF tokens on all forms', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_006', 'text' => 'Never expose internal paths or DB structure in errors', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'sec_007', 'text' => 'Hash passwords with password_hash() (bcrypt)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'sec_008', 'text' => 'Use secure session settings (httponly, secure, samesite)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'sec_009', 'text' => 'Rate limit API endpoints', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'sec_010', 'text' => 'Log security events (failed logins, suspicious activity)', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'database' => [
                'name' => 'Database Standards',
                'priority' => 'HIGH',
                'description' => 'Database access and query best practices',
                'rules' => [
                    ['id' => 'db_001', 'text' => 'ALWAYS validate table/field names before using', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'db_002', 'text' => 'Use DatabaseValidator service to check tables exist', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'db_003', 'text' => 'Check for naming conventions: intelligence_*, kb_*, mcp_*', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'db_004', 'text' => 'Add try-catch blocks around ALL database queries', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'db_005', 'text' => 'Use transactions for multi-query operations', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'db_006', 'text' => 'Index foreign keys and frequently queried columns', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'db_007', 'text' => 'Avoid SELECT * - specify fields explicitly', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'db_008', 'text' => 'Use LIMIT on queries that could return many rows', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'db_009', 'text' => 'Log slow queries (execution time > 500ms)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'db_010', 'text' => 'Close cursors and connections properly', 'priority' => 'HIGH', 'enabled' => true]
                ]
            ],
            
            'code_quality' => [
                'name' => 'Code Quality Standards',
                'priority' => 'HIGH',
                'description' => 'Clean code principles and best practices',
                'rules' => [
                    ['id' => 'cq_001', 'text' => 'Use strict types: declare(strict_types=1)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cq_002', 'text' => 'Type hint all parameters and return types', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cq_003', 'text' => 'Add PHPDoc comments to all functions/classes', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cq_004', 'text' => 'Follow PSR-12 coding style', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'cq_005', 'text' => 'Keep functions under 50 lines (split if longer)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'cq_006', 'text' => 'Maximum cyclomatic complexity: 10', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'cq_007', 'text' => 'Use meaningful variable names (no $x, $temp, $data)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cq_008', 'text' => 'No magic numbers - use named constants', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'cq_009', 'text' => 'DRY principle - no code duplication', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cq_010', 'text' => 'SOLID principles for classes', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'error_handling' => [
                'name' => 'Error Handling Standards',
                'priority' => 'HIGH',
                'description' => 'Proper error handling and user feedback',
                'rules' => [
                    ['id' => 'eh_001', 'text' => 'Wrap risky operations in try-catch blocks', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'eh_002', 'text' => 'Never let errors expose to users (show friendly messages)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'eh_003', 'text' => 'Log all errors with context (file, line, params)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'eh_004', 'text' => 'Provide fallback/default values when safe', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'eh_005', 'text' => 'Return structured error responses in APIs', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'eh_006', 'text' => 'Use appropriate HTTP status codes (400, 401, 403, 500)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'eh_007', 'text' => 'Include request_id in all error responses', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'eh_008', 'text' => 'Graceful degradation - partial failures OK', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'eh_009', 'text' => 'Validate inputs before processing', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'eh_010', 'text' => 'Check file/directory existence before operations', 'priority' => 'HIGH', 'enabled' => true]
                ]
            ],
            
            'performance' => [
                'name' => 'Performance Standards',
                'priority' => 'MEDIUM',
                'description' => 'Optimization and efficiency best practices',
                'rules' => [
                    ['id' => 'perf_001', 'text' => 'Cache frequently accessed data (with TTL)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'perf_002', 'text' => 'Use Redis/Memcached for session storage', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'perf_003', 'text' => 'Lazy load data - fetch only when needed', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'perf_004', 'text' => 'Paginate large datasets', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'perf_005', 'text' => 'Optimize database queries (use EXPLAIN)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'perf_006', 'text' => 'Minimize file I/O operations', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'perf_007', 'text' => 'Compress API responses (gzip)', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'perf_008', 'text' => 'Use CDN for static assets', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'perf_009', 'text' => 'Implement query result caching', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'perf_010', 'text' => 'Profile code to find bottlenecks', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'testing' => [
                'name' => 'Testing Standards',
                'priority' => 'MEDIUM',
                'description' => 'Testing and quality assurance practices',
                'rules' => [
                    ['id' => 'test_001', 'text' => 'Test new code before committing', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'test_002', 'text' => 'Include example usage in comments', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'test_003', 'text' => 'Test error conditions (null, empty, invalid)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'test_004', 'text' => 'Verify database queries return expected data', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'test_005', 'text' => 'Check API endpoints return correct status codes', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'test_006', 'text' => 'Test with production-like data volumes', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'test_007', 'text' => 'Validate edge cases', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'test_008', 'text' => 'Check backwards compatibility', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'test_009', 'text' => 'Test rollback procedures', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'test_010', 'text' => 'Document test scenarios', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'documentation' => [
                'name' => 'Documentation Standards',
                'priority' => 'MEDIUM',
                'description' => 'Code documentation and knowledge sharing',
                'rules' => [
                    ['id' => 'doc_001', 'text' => 'Add file-level docblock (purpose, author, date)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'doc_002', 'text' => 'Document all public functions with PHPDoc', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'doc_003', 'text' => 'Include @param, @return, @throws tags', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'doc_004', 'text' => 'Explain complex logic with inline comments', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'doc_005', 'text' => 'Keep README.md updated with changes', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'doc_006', 'text' => 'Document API endpoints (request/response examples)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'doc_007', 'text' => 'Maintain CHANGELOG.md for version history', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'doc_008', 'text' => 'Create examples for common use cases', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'doc_009', 'text' => 'Document configuration options', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'doc_010', 'text' => 'Explain non-obvious design decisions', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'frontend' => [
                'name' => 'Frontend Standards',
                'priority' => 'MEDIUM',
                'description' => 'UI/UX and frontend development practices',
                'rules' => [
                    ['id' => 'fe_001', 'text' => 'Use Bootstrap 5 classes (already loaded)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'fe_002', 'text' => 'Match existing dashboard design/color scheme', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'fe_003', 'text' => 'Mobile-first responsive design', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'fe_004', 'text' => 'Use Font Awesome icons (already loaded)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'fe_005', 'text' => 'Add loading states for async operations', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'fe_006', 'text' => 'Show user feedback (success/error messages)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'fe_007', 'text' => 'Validate forms client-side before submission', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'fe_008', 'text' => 'Use consistent spacing/margins', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'fe_009', 'text' => 'Accessibility: ARIA labels, alt text, keyboard nav', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'fe_010', 'text' => 'No inline styles - use CSS classes', 'priority' => 'MEDIUM', 'enabled' => true]
                ]
            ],
            
            'api' => [
                'name' => 'API Standards',
                'priority' => 'HIGH',
                'description' => 'RESTful API design and implementation',
                'rules' => [
                    ['id' => 'api_001', 'text' => 'Return consistent JSON envelope (success, data, message)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'api_002', 'text' => 'Use proper HTTP methods (GET, POST, PUT, DELETE)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'api_003', 'text' => 'Version APIs (/api/v1/...)', 'priority' => 'MEDIUM', 'enabled' => false],
                    ['id' => 'api_004', 'text' => 'Include timestamp in all responses', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'api_005', 'text' => 'Rate limit endpoints (prevent abuse)', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'api_006', 'text' => 'Require authentication where needed', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'api_007', 'text' => 'CORS headers for cross-origin requests', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'api_008', 'text' => 'Validate Content-Type headers', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'api_009', 'text' => 'Return pagination metadata (total, page, per_page)', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'api_010', 'text' => 'Document all endpoints with examples', 'priority' => 'HIGH', 'enabled' => true]
                ]
            ],
            
            'cis_specific' => [
                'name' => 'CIS-Specific Rules',
                'priority' => 'CRITICAL',
                'description' => 'Project-specific rules for CIS development',
                'rules' => [
                    ['id' => 'cis_001', 'text' => 'Always use: require_once $_SERVER[\'DOCUMENT_ROOT\'] . \'/app.php\'', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'cis_002', 'text' => 'Store ALL backups in /private_html/backups/', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'cis_003', 'text' => 'Never modify assets/functions/ without explicit permission', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'cis_004', 'text' => 'Check Apache error logs FIRST: logs/apache_*.error.log (500-1000 lines)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'cis_005', 'text' => 'Use CIS query-builder MySQL wrappers (NOT raw PDO)', 'priority' => 'CRITICAL', 'enabled' => true],
                    ['id' => 'cis_006', 'text' => 'Never create multiple copies of same files', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cis_007', 'text' => 'Keep JS/CSS files under 25KB, break up if larger', 'priority' => 'MEDIUM', 'enabled' => true],
                    ['id' => 'cis_008', 'text' => 'One backup only per file in /backups/', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cis_009', 'text' => 'NO test files - every file must have real purpose', 'priority' => 'HIGH', 'enabled' => true],
                    ['id' => 'cis_010', 'text' => 'Always develop dashboards with separate CSS/JS files + templated header/footer', 'priority' => 'HIGH', 'enabled' => true]
                ]
            ]
        ];
    }
    
    /**
     * Load bot templates for different tasks
     */
    private function loadTemplates(): void
    {
        $this->templates = [
            'database_dev' => [
                'name' => 'Database Developer Bot',
                'description' => 'Builds database schemas, queries, and migrations',
                'primary_standards' => ['security', 'database', 'error_handling', 'testing'],
                'context_needed' => ['database_credentials', 'table_list', 'naming_conventions'],
                'capabilities' => [
                    'Create/modify database tables',
                    'Write optimized queries',
                    'Generate migrations',
                    'Validate schemas',
                    'Index optimization'
                ],
                'prompt_template' => 'You are a senior database developer working on the CIS system. Your primary focus is creating secure, performant database operations.'
            ],
            
            'frontend_dev' => [
                'name' => 'Frontend Developer Bot',
                'description' => 'Builds UI components and pages',
                'primary_standards' => ['frontend', 'code_quality', 'documentation'],
                'context_needed' => ['dashboard_structure', 'css_framework', 'existing_components'],
                'capabilities' => [
                    'Create dashboard pages',
                    'Build responsive layouts',
                    'Implement forms and validation',
                    'Add interactive features',
                    'Match design system'
                ],
                'prompt_template' => 'You are a senior frontend developer building professional dashboard interfaces. Match the existing Bootstrap 5 design system.'
            ],
            
            'api_builder' => [
                'name' => 'API Builder Bot',
                'description' => 'Creates RESTful API endpoints',
                'primary_standards' => ['api', 'security', 'error_handling', 'documentation'],
                'context_needed' => ['existing_endpoints', 'authentication_method', 'rate_limits'],
                'capabilities' => [
                    'Design RESTful endpoints',
                    'Implement authentication',
                    'Add rate limiting',
                    'Generate API docs',
                    'Version management'
                ],
                'prompt_template' => 'You are a senior API developer creating production-ready RESTful endpoints. Follow REST best practices and security standards.'
            ],
            
            'debugging_expert' => [
                'name' => 'Debugging Expert Bot',
                'description' => 'Finds and fixes bugs systematically',
                'primary_standards' => ['error_handling', 'testing', 'database', 'security'],
                'context_needed' => ['error_logs', 'stack_traces', 'recent_changes'],
                'capabilities' => [
                    'Analyze error logs',
                    'Trace execution flow',
                    'Identify root causes',
                    'Propose fixes',
                    'Prevent regressions'
                ],
                'prompt_template' => 'You are a debugging expert. Analyze errors systematically, check logs, validate database queries, and provide root cause analysis.'
            ],
            
            'security_auditor' => [
                'name' => 'Security Auditor Bot',
                'description' => 'Reviews code for security vulnerabilities',
                'primary_standards' => ['security', 'database', 'api', 'error_handling'],
                'context_needed' => ['authentication_system', 'sensitive_endpoints', 'user_inputs'],
                'capabilities' => [
                    'SQL injection detection',
                    'XSS vulnerability scanning',
                    'CSRF protection review',
                    'Authentication audit',
                    'Data exposure checks'
                ],
                'prompt_template' => 'You are a security auditor. Review code for vulnerabilities, especially SQL injection, XSS, CSRF, and data exposure.'
            ],
            
            'performance_optimizer' => [
                'name' => 'Performance Optimizer Bot',
                'description' => 'Optimizes code and database performance',
                'primary_standards' => ['performance', 'database', 'code_quality'],
                'context_needed' => ['slow_queries', 'bottlenecks', 'traffic_patterns'],
                'capabilities' => [
                    'Query optimization',
                    'Caching strategies',
                    'Code profiling',
                    'Database indexing',
                    'Load testing'
                ],
                'prompt_template' => 'You are a performance optimization expert. Identify bottlenecks, optimize queries, implement caching, and improve response times.'
            ],
            
            'full_stack_dev' => [
                'name' => 'Full Stack Developer Bot',
                'description' => 'Handles both frontend and backend development',
                'primary_standards' => ['security', 'database', 'frontend', 'api', 'code_quality', 'error_handling'],
                'context_needed' => ['project_structure', 'database_schema', 'design_system'],
                'capabilities' => [
                    'End-to-end feature development',
                    'Database + API + UI',
                    'Integration testing',
                    'Deployment',
                    'Full system understanding'
                ],
                'prompt_template' => 'You are a senior full-stack developer. Build complete features from database to UI, following all standards and best practices.'
            ],
            
            'code_reviewer' => [
                'name' => 'Code Reviewer Bot',
                'description' => 'Reviews code quality and adherence to standards',
                'primary_standards' => ['code_quality', 'security', 'documentation', 'testing'],
                'context_needed' => ['coding_standards', 'recent_commits', 'team_guidelines'],
                'capabilities' => [
                    'Code quality assessment',
                    'Standards compliance check',
                    'Security review',
                    'Documentation verification',
                    'Improvement suggestions'
                ],
                'prompt_template' => 'You are a senior code reviewer. Check for standards compliance, security issues, code quality, and suggest improvements.'
            ],
            
            'migration_specialist' => [
                'name' => 'Migration Specialist Bot',
                'description' => 'Handles database migrations and schema changes',
                'primary_standards' => ['database', 'security', 'testing', 'documentation'],
                'context_needed' => ['current_schema', 'migration_history', 'dependencies'],
                'capabilities' => [
                    'Create migrations',
                    'Rollback scripts',
                    'Data transformation',
                    'Zero-downtime deploys',
                    'Backup strategies'
                ],
                'prompt_template' => 'You are a database migration specialist. Create safe, reversible migrations with proper testing and rollback procedures.'
            ],
            
            'documentation_writer' => [
                'name' => 'Documentation Writer Bot',
                'description' => 'Creates comprehensive technical documentation',
                'primary_standards' => ['documentation', 'code_quality'],
                'context_needed' => ['codebase_structure', 'feature_list', 'api_endpoints'],
                'capabilities' => [
                    'Write clear documentation',
                    'Create examples',
                    'API documentation',
                    'User guides',
                    'Maintain accuracy'
                ],
                'prompt_template' => 'You are a technical documentation specialist. Write clear, accurate documentation with examples and maintain consistency.'
            ]
        ];
    }
    
    /**
     * Load project context (database info, file structure, etc.)
     */
    private function loadProjectContext(): void
    {
        $this->projectContext = [
            'project_name' => 'CIS Intelligence Hub',
            'database' => [
                'name' => 'hdgwrzntwa',
                'user' => 'hdgwrzntwa',
                'host' => 'localhost',
                'tables' => [
                    'intelligence_content' => '22,185 files indexed',
                    'kb_categories' => '31 categories',
                    'mcp_search_analytics' => 'Search analytics',
                    'dashboard_users' => 'User management',
                    'business_units' => 'Multi-tenant support'
                ],
                'naming_conventions' => [
                    'intelligence_*' => 'Main intelligence system tables',
                    'kb_*' => 'Knowledge base tables (legacy MCP)',
                    'mcp_*' => 'MCP tool-specific tables',
                    'dashboard_*' => 'Dashboard system tables'
                ]
            ],
            'frameworks' => [
                'frontend' => 'Bootstrap 5 + Font Awesome',
                'backend' => 'PHP 8.1+ (strict types)',
                'database' => 'MySQL/MariaDB',
                'caching' => 'File-based (can use Redis)'
            ],
            'file_structure' => [
                '/dashboard/' => 'Main dashboard application',
                '/api/' => 'REST API endpoints',
                '/services/' => 'Business logic services',
                '/mcp/' => 'MCP intelligence tools',
                '/_kb/' => 'Knowledge base documentation'
            ],
            'existing_services' => [
                'DatabaseValidator' => 'Validates tables/fields, auto-corrects queries',
                'MCP Tools' => '13 search/analysis tools available',
                'Dashboard' => '19 pages, modular structure'
            ],
            'access_urls' => [
                'dashboard' => 'https://gpt.ecigdis.co.nz/dashboard/',
                'api' => 'https://gpt.ecigdis.co.nz/api/',
                'mcp' => 'https://gpt.ecigdis.co.nz/mcp/'
            ]
        ];
    }
    
    /**
     * Generate a custom bot prompt
     * 
     * @param string $templateKey Template to use (database_dev, frontend_dev, etc.)
     * @param array $taskDetails Specific task details
     * @param array $additionalStandards Extra standards to enforce
     * @return array Full bot prompt with checklist
     */
    public function generatePrompt(
        string $templateKey, 
        array $taskDetails = [], 
        array $additionalStandards = []
    ): array {
        if (!isset($this->templates[$templateKey])) {
            throw new Exception("Template '{$templateKey}' not found");
        }
        
        $template = $this->templates[$templateKey];
        
        // Build standards checklist
        $standardsList = array_merge($template['primary_standards'], $additionalStandards);
        $checklist = $this->buildChecklist($standardsList);
        
        // Build context section
        $contextSection = $this->buildContextSection($template['context_needed']);
        
        // Build capabilities section
        $capabilitiesSection = $this->buildCapabilitiesSection($template['capabilities']);
        
        // Build task-specific instructions
        $taskSection = $this->buildTaskSection($taskDetails);
        
        // Assemble full prompt
        $fullPrompt = $this->assemblePrompt(
            $template,
            $checklist,
            $contextSection,
            $capabilitiesSection,
            $taskSection
        );
        
        return [
            'template' => $templateKey,
            'template_name' => $template['name'],
            'prompt' => $fullPrompt,
            'standards_enforced' => $standardsList,
            'checklist' => $checklist,
            'project_context' => $this->projectContext
        ];
    }
    
    /**
     * Build standards checklist
     */
    private function buildChecklist(array $standardKeys): array
    {
        $checklist = [];
        
        foreach ($standardKeys as $key) {
            if (isset($this->standards[$key])) {
                $standard = $this->standards[$key];
                $checklist[$key] = [
                    'name' => $standard['name'],
                    'priority' => $standard['priority'],
                    'rules' => $standard['rules'],
                    'must_follow' => $standard['priority'] === 'CRITICAL'
                ];
            }
        }
        
        return $checklist;
    }
    
    /**
     * Build context section
     */
    private function buildContextSection(array $contextNeeded): string
    {
        $context = "## 📋 Project Context\n\n";
        
        foreach ($contextNeeded as $item) {
            $context .= "### " . ucwords(str_replace('_', ' ', $item)) . "\n";
            
            switch ($item) {
                case 'database_credentials':
                    $context .= "- **Database:** {$this->projectContext['database']['name']}\n";
                    $context .= "- **Host:** {$this->projectContext['database']['host']}\n";
                    $context .= "- **User:** {$this->projectContext['database']['user']}\n";
                    $context .= "- **Access:** Use DatabaseValidator service to verify tables\n\n";
                    break;
                    
                case 'table_list':
                    $context .= "**Main Tables:**\n";
                    foreach ($this->projectContext['database']['tables'] as $table => $desc) {
                        $context .= "- `{$table}` - {$desc}\n";
                    }
                    $context .= "\n";
                    break;
                    
                case 'naming_conventions':
                    $context .= "**Table Naming Conventions:**\n";
                    foreach ($this->projectContext['database']['naming_conventions'] as $prefix => $desc) {
                        $context .= "- `{$prefix}` - {$desc}\n";
                    }
                    $context .= "\n";
                    break;
                    
                case 'dashboard_structure':
                    $context .= "- **Framework:** {$this->projectContext['frameworks']['frontend']}\n";
                    $context .= "- **Location:** {$this->projectContext['file_structure']['/dashboard/']}\n";
                    $context .= "- **URL:** {$this->projectContext['access_urls']['dashboard']}\n\n";
                    break;
                    
                case 'css_framework':
                    $context .= "- **Frontend:** {$this->projectContext['frameworks']['frontend']}\n";
                    $context .= "- Use existing Bootstrap 5 classes\n";
                    $context .= "- Match current dashboard design\n\n";
                    break;
                    
                case 'existing_components':
                    $context .= "**Available Services:**\n";
                    foreach ($this->projectContext['existing_services'] as $service => $desc) {
                        $context .= "- **{$service}:** {$desc}\n";
                    }
                    $context .= "\n";
                    break;
            }
        }
        
        return $context;
    }
    
    /**
     * Build capabilities section
     */
    private function buildCapabilitiesSection(array $capabilities): string
    {
        $section = "## 🎯 Your Capabilities\n\n";
        $section .= "You are expected to:\n";
        
        foreach ($capabilities as $capability) {
            $section .= "- ✅ {$capability}\n";
        }
        
        $section .= "\n";
        return $section;
    }
    
    /**
     * Build task-specific section
     */
    private function buildTaskSection(array $taskDetails): string
    {
        if (empty($taskDetails)) {
            return "";
        }
        
        $section = "## 📝 Current Task\n\n";
        
        foreach ($taskDetails as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            if (is_array($value)) {
                $section .= "**{$label}:**\n";
                foreach ($value as $item) {
                    $section .= "- {$item}\n";
                }
            } else {
                $section .= "**{$label}:** {$value}\n";
            }
        }
        
        $section .= "\n";
        return $section;
    }
    
    /**
     * Assemble full prompt
     */
    private function assemblePrompt(
        array $template,
        array $checklist,
        string $contextSection,
        string $capabilitiesSection,
        string $taskSection
    ): string {
        $prompt = "# {$template['name']}\n\n";
        $prompt .= "## 🤖 Role\n\n";
        $prompt .= $template['prompt_template'] . "\n\n";
        $prompt .= "**Specialization:** {$template['description']}\n\n";
        
        $prompt .= "---\n\n";
        $prompt .= $contextSection;
        
        $prompt .= "---\n\n";
        $prompt .= $capabilitiesSection;
        
        if (!empty($taskSection)) {
            $prompt .= "---\n\n";
            $prompt .= $taskSection;
        }
        
        $prompt .= "---\n\n";
        $prompt .= "## ✅ Standards Checklist (YOU MUST FOLLOW)\n\n";
        
        foreach ($checklist as $key => $standard) {
            $priority = $standard['priority'];
            $icon = $priority === 'CRITICAL' ? '🔴' : ($priority === 'HIGH' ? '🟠' : '🟡');
            
            $prompt .= "### {$icon} {$standard['name']} [{$priority}]\n\n";
            
            if ($standard['must_follow']) {
                $prompt .= "**⚠️ CRITICAL - NEVER SKIP THESE:**\n\n";
            }
            
            foreach ($standard['rules'] as $i => $rule) {
                $num = $i + 1;
                $prompt .= "{$num}. {$rule}\n";
            }
            
            $prompt .= "\n";
        }
        
        $prompt .= "---\n\n";
        $prompt .= "## 🚨 Before You Finish\n\n";
        $prompt .= "**Self-Check (tick these off mentally):**\n";
        $prompt .= "- [ ] All CRITICAL standards followed\n";
        $prompt .= "- [ ] All HIGH priority standards followed\n";
        $prompt .= "- [ ] Code tested and working\n";
        $prompt .= "- [ ] Error handling implemented\n";
        $prompt .= "- [ ] Database queries validated (if applicable)\n";
        $prompt .= "- [ ] Security reviewed (no vulnerabilities)\n";
        $prompt .= "- [ ] Documentation added/updated\n";
        $prompt .= "- [ ] User-friendly error messages\n";
        $prompt .= "- [ ] No breaking changes (or documented)\n";
        $prompt .= "- [ ] Ready for production\n\n";
        
        $prompt .= "---\n\n";
        $prompt .= "## 🎯 Success Criteria\n\n";
        $prompt .= "Your work is complete when:\n";
        $prompt .= "1. All requirements met\n";
        $prompt .= "2. All standards enforced\n";
        $prompt .= "3. Code tested and verified\n";
        $prompt .= "4. Documentation provided\n";
        $prompt .= "5. No errors or warnings\n";
        $prompt .= "6. User can use immediately\n\n";
        
        $prompt .= "**Now begin your work! 🚀**\n";
        
        return $prompt;
    }
    
    /**
     * List all available templates
     */
    public function listTemplates(): array
    {
        $list = [];
        foreach ($this->templates as $key => $template) {
            $list[$key] = [
                'name' => $template['name'],
                'description' => $template['description'],
                'standards' => $template['primary_standards'],
                'capabilities' => count($template['capabilities'])
            ];
        }
        return $list;
    }
    
    /**
     * List all available standards
     */
    public function listStandards(): array
    {
        $list = [];
        foreach ($this->standards as $key => $standard) {
            $list[$key] = [
                'name' => $standard['name'],
                'priority' => $standard['priority'],
                'rule_count' => count($standard['rules'])
            ];
        }
        return $list;
    }
    
    /**
     * Get specific standard details
     */
    public function getStandard(string $key): array
    {
        return $this->standards[$key] ?? [];
    }
    
    /**
     * Validate if code follows standards (basic check)
     */
    public function validateCodeAgainstStandards(string $code, array $standardKeys): array
    {
        $violations = [];
        
        foreach ($standardKeys as $key) {
            if ($key === 'security') {
                // Check for SQL injection risks
                if (preg_match('/\$_(GET|POST|REQUEST)\s*\[.*?\].*?(query|execute|prepare)/i', $code)) {
                    if (!preg_match('/prepare|bindParam|bindValue/i', $code)) {
                        $violations[] = [
                            'standard' => 'security',
                            'rule' => 'Always use prepared statements for SQL',
                            'severity' => 'CRITICAL'
                        ];
                    }
                }
                
                // Check for XSS risks
                if (preg_match('/echo\s+\$_(GET|POST|REQUEST)/i', $code)) {
                    if (!preg_match('/htmlspecialchars|htmlentities/i', $code)) {
                        $violations[] = [
                            'standard' => 'security',
                            'rule' => 'Escape HTML output with htmlspecialchars()',
                            'severity' => 'CRITICAL'
                        ];
                    }
                }
                
                // Check for hard-coded credentials
                if (preg_match('/password\s*=\s*[\'"][^\'"]+[\'"]/i', $code)) {
                    $violations[] = [
                        'standard' => 'security',
                        'rule' => 'No hard-coded credentials',
                        'severity' => 'CRITICAL'
                    ];
                }
            }
            
            if ($key === 'code_quality') {
                // Check for strict types
                if (!preg_match('/declare\(strict_types\s*=\s*1\)/i', $code)) {
                    $violations[] = [
                        'standard' => 'code_quality',
                        'rule' => 'Use strict types declaration',
                        'severity' => 'HIGH'
                    ];
                }
                
                // Check for PHPDoc
                if (preg_match('/function\s+\w+/i', $code)) {
                    if (!preg_match('/\/\*\*[\s\S]*?\*\//i', $code)) {
                        $violations[] = [
                            'standard' => 'code_quality',
                            'rule' => 'Add PHPDoc comments to functions',
                            'severity' => 'MEDIUM'
                        ];
                    }
                }
            }
            
            if ($key === 'error_handling') {
                // Check for try-catch around DB operations
                if (preg_match('/(query|execute|prepare)\s*\(/i', $code)) {
                    if (!preg_match('/try\s*\{[\s\S]*?catch/i', $code)) {
                        $violations[] = [
                            'standard' => 'error_handling',
                            'rule' => 'Wrap database operations in try-catch',
                            'severity' => 'HIGH'
                        ];
                    }
                }
            }
        }
        
        return [
            'compliant' => empty($violations),
            'violations' => $violations,
            'standards_checked' => $standardKeys
        ];
    }
}
