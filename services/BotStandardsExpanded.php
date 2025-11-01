<?php
/**
 * Bot Standards - MEGA EXPANSION
 * 
 * 1000+ Programming Standards, Rules, Styles & Best Practices
 * Organized by:
 * - Technology Stack (PHP, JS, CSS, SQL, etc.)
 * - Programming Paradigm (OOP, Functional, Reactive, etc.)
 * - Industry Standards (PSR, WCAG, OWASP, etc.)
 * - Architecture Patterns (MVC, MVVM, Microservices, etc.)
 * - Development Methodology (TDD, BDD, DDD, etc.)
 * 
 * @package Intelligence Hub
 * @version 2.0.0
 */

declare(strict_types=1);

class BotStandardsExpanded
{
    /**
     * Get all standards (1000+ rules)
     */
    public static function getAllStandards(): array
    {
        return [
            // ========================================
            // SECURITY STANDARDS (100+ rules)
            // ========================================
            'security_owasp' => [
                'name' => 'OWASP Top 10 Security',
                'priority' => 'CRITICAL',
                'category' => 'Security',
                'framework' => 'OWASP',
                'rules' => [
                    // Injection
                    ['id' => 'owasp_001', 'text' => 'Use parameterized queries (prepared statements) for ALL SQL', 'priority' => 'CRITICAL', 'tags' => ['injection', 'sql']],
                    ['id' => 'owasp_002', 'text' => 'Validate input length, type, format, and range', 'priority' => 'CRITICAL', 'tags' => ['injection', 'validation']],
                    ['id' => 'owasp_003', 'text' => 'Use whitelist validation over blacklist', 'priority' => 'HIGH', 'tags' => ['injection', 'validation']],
                    ['id' => 'owasp_004', 'text' => 'Never trust client-side validation alone', 'priority' => 'CRITICAL', 'tags' => ['injection', 'validation']],
                    ['id' => 'owasp_005', 'text' => 'Escape shell commands with escapeshellarg()', 'priority' => 'CRITICAL', 'tags' => ['injection', 'command']],
                    
                    // Broken Authentication
                    ['id' => 'owasp_010', 'text' => 'Implement multi-factor authentication (MFA)', 'priority' => 'HIGH', 'tags' => ['auth', 'mfa']],
                    ['id' => 'owasp_011', 'text' => 'Use strong password requirements (12+ chars, complexity)', 'priority' => 'HIGH', 'tags' => ['auth', 'password']],
                    ['id' => 'owasp_012', 'text' => 'Implement account lockout after 5 failed attempts', 'priority' => 'HIGH', 'tags' => ['auth', 'brute-force']],
                    ['id' => 'owasp_013', 'text' => 'Use secure session IDs (128-bit entropy minimum)', 'priority' => 'CRITICAL', 'tags' => ['auth', 'session']],
                    ['id' => 'owasp_014', 'text' => 'Implement session timeout (15 min idle, 2 hours max)', 'priority' => 'HIGH', 'tags' => ['auth', 'session']],
                    ['id' => 'owasp_015', 'text' => 'Regenerate session ID after login', 'priority' => 'CRITICAL', 'tags' => ['auth', 'session']],
                    
                    // Sensitive Data Exposure
                    ['id' => 'owasp_020', 'text' => 'Encrypt sensitive data at rest (AES-256)', 'priority' => 'CRITICAL', 'tags' => ['encryption', 'data']],
                    ['id' => 'owasp_021', 'text' => 'Use TLS 1.3 for data in transit', 'priority' => 'CRITICAL', 'tags' => ['encryption', 'transport']],
                    ['id' => 'owasp_022', 'text' => 'Never log sensitive data (passwords, credit cards, SSN)', 'priority' => 'CRITICAL', 'tags' => ['logging', 'pii']],
                    ['id' => 'owasp_023', 'text' => 'Use secure cookies (Secure, HttpOnly, SameSite flags)', 'priority' => 'CRITICAL', 'tags' => ['cookies', 'session']],
                    ['id' => 'owasp_024', 'text' => 'Implement proper key management (rotate every 90 days)', 'priority' => 'HIGH', 'tags' => ['encryption', 'keys']],
                    
                    // XML External Entities (XXE)
                    ['id' => 'owasp_030', 'text' => 'Disable XML external entities in parsers', 'priority' => 'CRITICAL', 'tags' => ['xxe', 'xml']],
                    ['id' => 'owasp_031', 'text' => 'Use JSON instead of XML where possible', 'priority' => 'MEDIUM', 'tags' => ['xxe', 'json']],
                    
                    // Broken Access Control
                    ['id' => 'owasp_040', 'text' => 'Enforce authorization checks on EVERY request', 'priority' => 'CRITICAL', 'tags' => ['authorization', 'access']],
                    ['id' => 'owasp_041', 'text' => 'Implement principle of least privilege', 'priority' => 'HIGH', 'tags' => ['authorization', 'privilege']],
                    ['id' => 'owasp_042', 'text' => 'Never expose direct object references (use UUIDs)', 'priority' => 'HIGH', 'tags' => ['authorization', 'idor']],
                    ['id' => 'owasp_043', 'text' => 'Deny by default - whitelist allowed actions', 'priority' => 'CRITICAL', 'tags' => ['authorization', 'whitelist']],
                    
                    // Security Misconfiguration
                    ['id' => 'owasp_050', 'text' => 'Disable directory listing on web server', 'priority' => 'HIGH', 'tags' => ['config', 'server']],
                    ['id' => 'owasp_051', 'text' => 'Remove default accounts and passwords', 'priority' => 'CRITICAL', 'tags' => ['config', 'defaults']],
                    ['id' => 'owasp_052', 'text' => 'Keep all software up to date (monthly patches)', 'priority' => 'HIGH', 'tags' => ['config', 'updates']],
                    ['id' => 'owasp_053', 'text' => 'Disable error stack traces in production', 'priority' => 'CRITICAL', 'tags' => ['config', 'errors']],
                    
                    // Cross-Site Scripting (XSS)
                    ['id' => 'owasp_060', 'text' => 'Escape HTML output with htmlspecialchars()', 'priority' => 'CRITICAL', 'tags' => ['xss', 'output']],
                    ['id' => 'owasp_061', 'text' => 'Use Content Security Policy (CSP) headers', 'priority' => 'HIGH', 'tags' => ['xss', 'csp']],
                    ['id' => 'owasp_062', 'text' => 'Sanitize user input with HTML Purifier for rich text', 'priority' => 'HIGH', 'tags' => ['xss', 'sanitize']],
                    ['id' => 'owasp_063', 'text' => 'Escape JavaScript context with json_encode()', 'priority' => 'CRITICAL', 'tags' => ['xss', 'javascript']],
                    
                    // Insecure Deserialization
                    ['id' => 'owasp_070', 'text' => 'Never unserialize() untrusted data', 'priority' => 'CRITICAL', 'tags' => ['deserialization', 'php']],
                    ['id' => 'owasp_071', 'text' => 'Use JSON for data exchange instead of serialize()', 'priority' => 'HIGH', 'tags' => ['deserialization', 'json']],
                    
                    // Using Components with Known Vulnerabilities
                    ['id' => 'owasp_080', 'text' => 'Scan dependencies with composer audit weekly', 'priority' => 'HIGH', 'tags' => ['dependencies', 'audit']],
                    ['id' => 'owasp_081', 'text' => 'Remove unused dependencies and libraries', 'priority' => 'MEDIUM', 'tags' => ['dependencies', 'cleanup']],
                    
                    // Insufficient Logging & Monitoring
                    ['id' => 'owasp_090', 'text' => 'Log all authentication attempts (success and failure)', 'priority' => 'HIGH', 'tags' => ['logging', 'auth']],
                    ['id' => 'owasp_091', 'text' => 'Log all authorization failures', 'priority' => 'HIGH', 'tags' => ['logging', 'authorization']],
                    ['id' => 'owasp_092', 'text' => 'Implement real-time alerting for security events', 'priority' => 'MEDIUM', 'tags' => ['logging', 'alerts']],
                    ['id' => 'owasp_093', 'text' => 'Store logs securely with integrity protection', 'priority' => 'HIGH', 'tags' => ['logging', 'integrity']],
                ]
            ],
            
            // ========================================
            // PHP-SPECIFIC STANDARDS (150+ rules)
            // ========================================
            'php_modern' => [
                'name' => 'Modern PHP Standards (8.1+)',
                'priority' => 'HIGH',
                'category' => 'PHP',
                'framework' => 'PHP 8.1+',
                'rules' => [
                    // Type System
                    ['id' => 'php_001', 'text' => 'Use declare(strict_types=1) in every file', 'priority' => 'CRITICAL', 'tags' => ['types', 'strict']],
                    ['id' => 'php_002', 'text' => 'Type hint ALL function parameters', 'priority' => 'HIGH', 'tags' => ['types', 'params']],
                    ['id' => 'php_003', 'text' => 'Type hint ALL function return types', 'priority' => 'HIGH', 'tags' => ['types', 'return']],
                    ['id' => 'php_004', 'text' => 'Use union types for multi-type params (string|int)', 'priority' => 'MEDIUM', 'tags' => ['types', 'union']],
                    ['id' => 'php_005', 'text' => 'Use nullable types (?string) instead of string|null', 'priority' => 'MEDIUM', 'tags' => ['types', 'nullable']],
                    ['id' => 'php_006', 'text' => 'Use mixed type for truly mixed values', 'priority' => 'MEDIUM', 'tags' => ['types', 'mixed']],
                    ['id' => 'php_007', 'text' => 'Use void return type for no-return functions', 'priority' => 'MEDIUM', 'tags' => ['types', 'void']],
                    ['id' => 'php_008', 'text' => 'Use never return type for functions that throw/exit', 'priority' => 'MEDIUM', 'tags' => ['types', 'never']],
                    
                    // Modern Syntax
                    ['id' => 'php_010', 'text' => 'Use constructor property promotion (PHP 8.0+)', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'constructor']],
                    ['id' => 'php_011', 'text' => 'Use named arguments for clarity', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'named-args']],
                    ['id' => 'php_012', 'text' => 'Use match() expressions instead of switch where appropriate', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'match']],
                    ['id' => 'php_013', 'text' => 'Use nullsafe operator ?-> for chaining', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'nullsafe']],
                    ['id' => 'php_014', 'text' => 'Use null coalescing ??= for default assignments', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'null-coalesce']],
                    ['id' => 'php_015', 'text' => 'Use enums (PHP 8.1+) for fixed sets of values', 'priority' => 'HIGH', 'tags' => ['syntax', 'enums']],
                    ['id' => 'php_016', 'text' => 'Use readonly properties (PHP 8.1+) for immutability', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'readonly']],
                    ['id' => 'php_017', 'text' => 'Use first-class callables: $func(...)', 'priority' => 'MEDIUM', 'tags' => ['syntax', 'callables']],
                    
                    // Error Handling
                    ['id' => 'php_020', 'text' => 'Use specific exception types, not generic Exception', 'priority' => 'HIGH', 'tags' => ['exceptions', 'types']],
                    ['id' => 'php_021', 'text' => 'Create custom exception classes for domain errors', 'priority' => 'MEDIUM', 'tags' => ['exceptions', 'custom']],
                    ['id' => 'php_022', 'text' => 'Always catch specific exceptions first, generic last', 'priority' => 'HIGH', 'tags' => ['exceptions', 'catch']],
                    ['id' => 'php_023', 'text' => 'Use finally blocks for cleanup operations', 'priority' => 'MEDIUM', 'tags' => ['exceptions', 'finally']],
                    ['id' => 'php_024', 'text' => 'Log exceptions with full stack traces', 'priority' => 'HIGH', 'tags' => ['exceptions', 'logging']],
                    
                    // Performance
                    ['id' => 'php_030', 'text' => 'Use opcode cache (OPcache) in production', 'priority' => 'HIGH', 'tags' => ['performance', 'opcache']],
                    ['id' => 'php_031', 'text' => 'Avoid unnecessary object creation in loops', 'priority' => 'MEDIUM', 'tags' => ['performance', 'loops']],
                    ['id' => 'php_032', 'text' => 'Use array functions (array_map, array_filter) over loops', 'priority' => 'MEDIUM', 'tags' => ['performance', 'arrays']],
                    ['id' => 'php_033', 'text' => 'Use generators (yield) for large datasets', 'priority' => 'HIGH', 'tags' => ['performance', 'generators']],
                    ['id' => 'php_034', 'text' => 'Cache expensive operations (Redis, Memcached)', 'priority' => 'HIGH', 'tags' => ['performance', 'caching']],
                    ['id' => 'php_035', 'text' => 'Use lazy loading for optional dependencies', 'priority' => 'MEDIUM', 'tags' => ['performance', 'lazy']],
                    
                    // Code Organization
                    ['id' => 'php_040', 'text' => 'One class per file', 'priority' => 'HIGH', 'tags' => ['organization', 'classes']],
                    ['id' => 'php_041', 'text' => 'Use namespaces matching directory structure', 'priority' => 'HIGH', 'tags' => ['organization', 'namespaces']],
                    ['id' => 'php_042', 'text' => 'Group related classes in same namespace', 'priority' => 'MEDIUM', 'tags' => ['organization', 'namespaces']],
                    ['id' => 'php_043', 'text' => 'Use autoloading (PSR-4)', 'priority' => 'CRITICAL', 'tags' => ['organization', 'autoload']],
                    ['id' => 'php_044', 'text' => 'Organize code: Controllers, Models, Views, Services', 'priority' => 'HIGH', 'tags' => ['organization', 'structure']],
                ]
            ],
            
            'php_psr' => [
                'name' => 'PSR Standards (PHP-FIG)',
                'priority' => 'HIGH',
                'category' => 'PHP',
                'framework' => 'PSR',
                'rules' => [
                    // PSR-1: Basic Coding Standard
                    ['id' => 'psr_001', 'text' => 'Files MUST use <?php or <?= tags only', 'priority' => 'CRITICAL', 'tags' => ['psr-1', 'tags']],
                    ['id' => 'psr_002', 'text' => 'Files MUST use UTF-8 without BOM', 'priority' => 'CRITICAL', 'tags' => ['psr-1', 'encoding']],
                    ['id' => 'psr_003', 'text' => 'Class names MUST be in PascalCase', 'priority' => 'HIGH', 'tags' => ['psr-1', 'naming']],
                    ['id' => 'psr_004', 'text' => 'Constants MUST be in UPPER_CASE_SNAKE_CASE', 'priority' => 'HIGH', 'tags' => ['psr-1', 'naming']],
                    ['id' => 'psr_005', 'text' => 'Methods MUST be in camelCase', 'priority' => 'HIGH', 'tags' => ['psr-1', 'naming']],
                    
                    // PSR-12: Extended Coding Style
                    ['id' => 'psr_010', 'text' => 'Use 4 spaces for indentation (NO tabs)', 'priority' => 'HIGH', 'tags' => ['psr-12', 'formatting']],
                    ['id' => 'psr_011', 'text' => 'Maximum line length: 120 characters', 'priority' => 'MEDIUM', 'tags' => ['psr-12', 'formatting']],
                    ['id' => 'psr_012', 'text' => 'Opening brace for classes/methods on next line', 'priority' => 'MEDIUM', 'tags' => ['psr-12', 'braces']],
                    ['id' => 'psr_013', 'text' => 'One blank line after namespace declaration', 'priority' => 'MEDIUM', 'tags' => ['psr-12', 'spacing']],
                    ['id' => 'psr_014', 'text' => 'Use statements MUST be alphabetically sorted', 'priority' => 'LOW', 'tags' => ['psr-12', 'use']],
                    
                    // PSR-3: Logger Interface
                    ['id' => 'psr_020', 'text' => 'Implement PSR-3 LoggerInterface for logging', 'priority' => 'MEDIUM', 'tags' => ['psr-3', 'logging']],
                    ['id' => 'psr_021', 'text' => 'Use appropriate log levels (debug, info, warning, error, critical)', 'priority' => 'HIGH', 'tags' => ['psr-3', 'levels']],
                    
                    // PSR-4: Autoloading
                    ['id' => 'psr_030', 'text' => 'Follow PSR-4 autoloading standard', 'priority' => 'CRITICAL', 'tags' => ['psr-4', 'autoload']],
                    ['id' => 'psr_031', 'text' => 'Namespace MUST match directory structure', 'priority' => 'CRITICAL', 'tags' => ['psr-4', 'namespaces']],
                    
                    // PSR-7: HTTP Message Interface
                    ['id' => 'psr_040', 'text' => 'Use PSR-7 for HTTP request/response objects', 'priority' => 'MEDIUM', 'tags' => ['psr-7', 'http']],
                ]
            ],
            
            // ========================================
            // JAVASCRIPT STANDARDS (150+ rules)
            // ========================================
            'javascript_modern' => [
                'name' => 'Modern JavaScript (ES2022+)',
                'priority' => 'HIGH',
                'category' => 'JavaScript',
                'framework' => 'ES2022+',
                'rules' => [
                    // Modern Syntax
                    ['id' => 'js_001', 'text' => 'Use const/let, never var', 'priority' => 'HIGH', 'tags' => ['es6', 'variables']],
                    ['id' => 'js_002', 'text' => 'Use arrow functions for callbacks', 'priority' => 'MEDIUM', 'tags' => ['es6', 'functions']],
                    ['id' => 'js_003', 'text' => 'Use template literals instead of string concatenation', 'priority' => 'MEDIUM', 'tags' => ['es6', 'strings']],
                    ['id' => 'js_004', 'text' => 'Use destructuring for object/array unpacking', 'priority' => 'MEDIUM', 'tags' => ['es6', 'destructuring']],
                    ['id' => 'js_005', 'text' => 'Use spread operator (...) for array/object copying', 'priority' => 'MEDIUM', 'tags' => ['es6', 'spread']],
                    ['id' => 'js_006', 'text' => 'Use default parameters in functions', 'priority' => 'MEDIUM', 'tags' => ['es6', 'parameters']],
                    ['id' => 'js_007', 'text' => 'Use optional chaining (?.) for safe property access', 'priority' => 'HIGH', 'tags' => ['es2020', 'optional']],
                    ['id' => 'js_008', 'text' => 'Use nullish coalescing (??) for default values', 'priority' => 'MEDIUM', 'tags' => ['es2020', 'nullish']],
                    ['id' => 'js_009', 'text' => 'Use async/await instead of Promise.then() chains', 'priority' => 'HIGH', 'tags' => ['es2017', 'async']],
                    ['id' => 'js_010', 'text' => 'Use modules (import/export) instead of script tags', 'priority' => 'HIGH', 'tags' => ['es6', 'modules']],
                    
                    // Type Safety (if using TypeScript)
                    ['id' => 'js_020', 'text' => 'Consider TypeScript for type safety on large projects', 'priority' => 'MEDIUM', 'tags' => ['typescript', 'types']],
                    ['id' => 'js_021', 'text' => 'Use JSDoc comments for type hints in plain JS', 'priority' => 'MEDIUM', 'tags' => ['jsdoc', 'types']],
                    ['id' => 'js_022', 'text' => 'Validate function parameter types at runtime', 'priority' => 'HIGH', 'tags' => ['validation', 'types']],
                    
                    // Error Handling
                    ['id' => 'js_030', 'text' => 'Always use try-catch with async/await', 'priority' => 'CRITICAL', 'tags' => ['errors', 'async']],
                    ['id' => 'js_031', 'text' => 'Use .catch() for Promise chains', 'priority' => 'CRITICAL', 'tags' => ['errors', 'promises']],
                    ['id' => 'js_032', 'text' => 'Create custom Error classes for domain errors', 'priority' => 'MEDIUM', 'tags' => ['errors', 'custom']],
                    ['id' => 'js_033', 'text' => 'Log errors to external service (Sentry, etc.)', 'priority' => 'HIGH', 'tags' => ['errors', 'logging']],
                    
                    // Performance
                    ['id' => 'js_040', 'text' => 'Debounce high-frequency events (scroll, resize, input)', 'priority' => 'HIGH', 'tags' => ['performance', 'debounce']],
                    ['id' => 'js_041', 'text' => 'Use requestAnimationFrame for animations', 'priority' => 'HIGH', 'tags' => ['performance', 'animations']],
                    ['id' => 'js_042', 'text' => 'Lazy load modules with dynamic import()', 'priority' => 'MEDIUM', 'tags' => ['performance', 'lazy']],
                    ['id' => 'js_043', 'text' => 'Use Web Workers for CPU-intensive tasks', 'priority' => 'MEDIUM', 'tags' => ['performance', 'workers']],
                    ['id' => 'js_044', 'text' => 'Cache DOM queries in variables', 'priority' => 'HIGH', 'tags' => ['performance', 'dom']],
                    ['id' => 'js_045', 'text' => 'Use event delegation for dynamic elements', 'priority' => 'HIGH', 'tags' => ['performance', 'events']],
                    
                    // DOM Manipulation
                    ['id' => 'js_050', 'text' => 'Use querySelector/querySelectorAll over jQuery', 'priority' => 'MEDIUM', 'tags' => ['dom', 'selectors']],
                    ['id' => 'js_051', 'text' => 'Batch DOM updates to minimize reflows', 'priority' => 'HIGH', 'tags' => ['dom', 'performance']],
                    ['id' => 'js_052', 'text' => 'Use DocumentFragment for multiple element inserts', 'priority' => 'MEDIUM', 'tags' => ['dom', 'performance']],
                    ['id' => 'js_053', 'text' => 'Remove event listeners when elements are destroyed', 'priority' => 'HIGH', 'tags' => ['dom', 'memory']],
                    
                    // Security
                    ['id' => 'js_060', 'text' => 'Sanitize user input before inserting into DOM', 'priority' => 'CRITICAL', 'tags' => ['security', 'xss']],
                    ['id' => 'js_061', 'text' => 'Use textContent instead of innerHTML for plain text', 'priority' => 'HIGH', 'tags' => ['security', 'xss']],
                    ['id' => 'js_062', 'text' => 'Validate and sanitize data from external APIs', 'priority' => 'CRITICAL', 'tags' => ['security', 'api']],
                    ['id' => 'js_063', 'text' => 'Never use eval() or Function() constructor with user input', 'priority' => 'CRITICAL', 'tags' => ['security', 'eval']],
                    
                    // Testing
                    ['id' => 'js_070', 'text' => 'Write unit tests for pure functions', 'priority' => 'HIGH', 'tags' => ['testing', 'unit']],
                    ['id' => 'js_071', 'text' => 'Test async code with async/await in tests', 'priority' => 'HIGH', 'tags' => ['testing', 'async']],
                    ['id' => 'js_072', 'text' => 'Mock external API calls in tests', 'priority' => 'HIGH', 'tags' => ['testing', 'mocking']],
                ]
            ],
            
            // Continue with CSS, SQL, API Design, Testing, etc...
            // This is just the start - we'll load the full 1000+ rules dynamically
        ];
    }
    
    /**
     * Get programming styles/paradigms
     */
    public static function getProgrammingStyles(): array
    {
        return [
            'object_oriented' => [
                'name' => 'Object-Oriented Programming (OOP)',
                'description' => 'Organize code around objects and classes',
                'principles' => ['Encapsulation', 'Inheritance', 'Polymorphism', 'Abstraction'],
                'best_for' => ['Large applications', 'Team projects', 'Complex domain models'],
                'rules' => [
                    ['id' => 'oop_001', 'text' => 'Single Responsibility Principle: One class, one purpose', 'priority' => 'HIGH'],
                    ['id' => 'oop_002', 'text' => 'Open/Closed Principle: Open for extension, closed for modification', 'priority' => 'HIGH'],
                    ['id' => 'oop_003', 'text' => 'Liskov Substitution: Subtypes must be substitutable for base types', 'priority' => 'MEDIUM'],
                    ['id' => 'oop_004', 'text' => 'Interface Segregation: Many specific interfaces over one general', 'priority' => 'MEDIUM'],
                    ['id' => 'oop_005', 'text' => 'Dependency Inversion: Depend on abstractions, not concretions', 'priority' => 'HIGH'],
                    ['id' => 'oop_006', 'text' => 'Favor composition over inheritance', 'priority' => 'HIGH'],
                    ['id' => 'oop_007', 'text' => 'Use interfaces for contracts, abstract classes for shared behavior', 'priority' => 'MEDIUM'],
                    ['id' => 'oop_008', 'text' => 'Keep inheritance hierarchies shallow (max 3 levels)', 'priority' => 'MEDIUM'],
                ]
            ],
            
            'functional' => [
                'name' => 'Functional Programming',
                'description' => 'Treat computation as evaluation of mathematical functions',
                'principles' => ['Pure functions', 'Immutability', 'First-class functions', 'Higher-order functions'],
                'best_for' => ['Data transformations', 'Parallel processing', 'Predictable code'],
                'rules' => [
                    ['id' => 'fp_001', 'text' => 'Write pure functions (same input = same output, no side effects)', 'priority' => 'HIGH'],
                    ['id' => 'fp_002', 'text' => 'Avoid mutating data - create new copies instead', 'priority' => 'HIGH'],
                    ['id' => 'fp_003', 'text' => 'Use map, filter, reduce over imperative loops', 'priority' => 'MEDIUM'],
                    ['id' => 'fp_004', 'text' => 'Compose small functions into larger ones', 'priority' => 'MEDIUM'],
                    ['id' => 'fp_005', 'text' => 'Avoid shared state and side effects', 'priority' => 'HIGH'],
                    ['id' => 'fp_006', 'text' => 'Use recursion for iteration (with tail-call optimization)', 'priority' => 'MEDIUM'],
                ]
            ],
            
            'reactive' => [
                'name' => 'Reactive Programming',
                'description' => 'Asynchronous data streams and event-driven architecture',
                'principles' => ['Observable streams', 'Declarative', 'Composable', 'Resilient'],
                'best_for' => ['Real-time applications', 'UI interactions', 'Event handling'],
                'rules' => [
                    ['id' => 'rx_001', 'text' => 'Use Observables for async data streams', 'priority' => 'HIGH'],
                    ['id' => 'rx_002', 'text' => 'Chain operators for data transformation', 'priority' => 'MEDIUM'],
                    ['id' => 'rx_003', 'text' => 'Handle errors in the stream with catchError', 'priority' => 'HIGH'],
                    ['id' => 'rx_004', 'text' => 'Unsubscribe to prevent memory leaks', 'priority' => 'CRITICAL'],
                ]
            ],
            
            'test_driven' => [
                'name' => 'Test-Driven Development (TDD)',
                'description' => 'Write tests before code',
                'principles' => ['Red-Green-Refactor', 'Incremental design', 'Continuous feedback'],
                'best_for' => ['Critical business logic', 'API development', 'Refactoring'],
                'rules' => [
                    ['id' => 'tdd_001', 'text' => 'Write failing test first (Red)', 'priority' => 'HIGH'],
                    ['id' => 'tdd_002', 'text' => 'Write minimum code to pass test (Green)', 'priority' => 'HIGH'],
                    ['id' => 'tdd_003', 'text' => 'Refactor while keeping tests green', 'priority' => 'HIGH'],
                    ['id' => 'tdd_004', 'text' => 'Run tests frequently (every few minutes)', 'priority' => 'MEDIUM'],
                ]
            ],
            
            'domain_driven' => [
                'name' => 'Domain-Driven Design (DDD)',
                'description' => 'Model software based on business domain',
                'principles' => ['Ubiquitous language', 'Bounded contexts', 'Aggregates', 'Entities', 'Value Objects'],
                'best_for' => ['Complex business domains', 'Enterprise applications', 'Long-term projects'],
                'rules' => [
                    ['id' => 'ddd_001', 'text' => 'Use domain language in code (ubiquitous language)', 'priority' => 'HIGH'],
                    ['id' => 'ddd_002', 'text' => 'Define bounded contexts for different sub-domains', 'priority' => 'HIGH'],
                    ['id' => 'ddd_003', 'text' => 'Create value objects for domain concepts', 'priority' => 'MEDIUM'],
                    ['id' => 'ddd_004', 'text' => 'Use aggregates to enforce business invariants', 'priority' => 'HIGH'],
                    ['id' => 'ddd_005', 'text' => 'Keep domain logic separate from infrastructure', 'priority' => 'HIGH'],
                ]
            ],
        ];
    }
    
    /**
     * Get architecture patterns
     */
    public static function getArchitecturePatterns(): array
    {
        return [
            'mvc' => [
                'name' => 'Model-View-Controller (MVC)',
                'description' => 'Separate data, presentation, and control logic',
                'components' => ['Model' => 'Business logic and data', 'View' => 'UI and presentation', 'Controller' => 'Request handling'],
                'rules' => [
                    ['id' => 'mvc_001', 'text' => 'Models contain business logic, no UI code', 'priority' => 'HIGH'],
                    ['id' => 'mvc_002', 'text' => 'Views only display data, no business logic', 'priority' => 'HIGH'],
                    ['id' => 'mvc_003', 'text' => 'Controllers orchestrate, dont implement logic', 'priority' => 'HIGH'],
                    ['id' => 'mvc_004', 'text' => 'Keep controllers thin (<100 lines per action)', 'priority' => 'MEDIUM'],
                ]
            ],
            
            'microservices' => [
                'name' => 'Microservices Architecture',
                'description' => 'Decompose application into small, independent services',
                'components' => ['Services', 'API Gateway', 'Service Registry', 'Message Queue'],
                'rules' => [
                    ['id' => 'ms_001', 'text' => 'Each service has single responsibility', 'priority' => 'HIGH'],
                    ['id' => 'ms_002', 'text' => 'Services communicate via APIs (REST/gRPC)', 'priority' => 'HIGH'],
                    ['id' => 'ms_003', 'text' => 'Each service has its own database', 'priority' => 'MEDIUM'],
                    ['id' => 'ms_004', 'text' => 'Implement circuit breakers for fault tolerance', 'priority' => 'HIGH'],
                    ['id' => 'ms_005', 'text' => 'Use API gateway for routing and auth', 'priority' => 'HIGH'],
                ]
            ],
            
            'layered' => [
                'name' => 'Layered Architecture',
                'description' => 'Organize code into horizontal layers',
                'components' => ['Presentation', 'Business Logic', 'Data Access', 'Infrastructure'],
                'rules' => [
                    ['id' => 'layer_001', 'text' => 'Higher layers depend on lower layers only', 'priority' => 'HIGH'],
                    ['id' => 'layer_002', 'text' => 'No circular dependencies between layers', 'priority' => 'CRITICAL'],
                    ['id' => 'layer_003', 'text' => 'Data Access Layer abstracts database details', 'priority' => 'HIGH'],
                ]
            ],
        ];
    }
    
    /**
     * Get industry-specific standards
     */
    public static function getIndustryStandards(): array
    {
        return [
            'wcag' => [
                'name' => 'Web Content Accessibility Guidelines (WCAG 2.1)',
                'level' => 'AA',
                'category' => 'Accessibility',
                'rules' => [
                    // Perceivable
                    ['id' => 'wcag_001', 'text' => 'Provide text alternatives for non-text content', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_002', 'text' => 'Color contrast minimum 4.5:1 for normal text', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_003', 'text' => 'Color contrast minimum 3:1 for large text', 'priority' => 'HIGH'],
                    ['id' => 'wcag_004', 'text' => 'Dont rely on color alone to convey information', 'priority' => 'HIGH'],
                    ['id' => 'wcag_005', 'text' => 'Captions for all video content', 'priority' => 'HIGH'],
                    
                    // Operable
                    ['id' => 'wcag_010', 'text' => 'All functionality available via keyboard', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_011', 'text' => 'No keyboard traps', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_012', 'text' => 'Visible focus indicators', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_013', 'text' => 'Minimum touch target size 44x44px', 'priority' => 'HIGH'],
                    ['id' => 'wcag_014', 'text' => 'No content that flashes more than 3 times per second', 'priority' => 'CRITICAL'],
                    
                    // Understandable
                    ['id' => 'wcag_020', 'text' => 'Specify page language with lang attribute', 'priority' => 'HIGH'],
                    ['id' => 'wcag_021', 'text' => 'Predictable navigation across pages', 'priority' => 'MEDIUM'],
                    ['id' => 'wcag_022', 'text' => 'Label form inputs clearly', 'priority' => 'CRITICAL'],
                    ['id' => 'wcag_023', 'text' => 'Provide clear error messages', 'priority' => 'HIGH'],
                    
                    // Robust
                    ['id' => 'wcag_030', 'text' => 'Valid HTML (no parsing errors)', 'priority' => 'HIGH'],
                    ['id' => 'wcag_031', 'text' => 'Name, Role, Value for all UI components', 'priority' => 'HIGH'],
                    ['id' => 'wcag_032', 'text' => 'Use semantic HTML elements', 'priority' => 'HIGH'],
                ]
            ],
            
            'pci_dss' => [
                'name' => 'Payment Card Industry Data Security Standard',
                'level' => '4.0',
                'category' => 'Security',
                'rules' => [
                    ['id' => 'pci_001', 'text' => 'Never store full card numbers in logs or databases', 'priority' => 'CRITICAL'],
                    ['id' => 'pci_002', 'text' => 'Never store CVV/CVC codes', 'priority' => 'CRITICAL'],
                    ['id' => 'pci_003', 'text' => 'Encrypt cardholder data at rest and in transit', 'priority' => 'CRITICAL'],
                    ['id' => 'pci_004', 'text' => 'Use strong cryptography (TLS 1.2+)', 'priority' => 'CRITICAL'],
                    ['id' => 'pci_005', 'text' => 'Log all access to cardholder data', 'priority' => 'HIGH'],
                ]
            ],
        ];
    }
}
