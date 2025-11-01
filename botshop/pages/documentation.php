<?php
/**
 * Documentation Page V2
 * Comprehensive system documentation with search, code examples, and tutorials
 *
 * Features:
 * - Searchable documentation with live filtering
 * - Categorized sections (Getting Started, API, Rules, Best Practices, Troubleshooting)
 * - Code examples with syntax highlighting
 * - Interactive rule explorer with good/bad examples
 * - FAQ accordion with expand/collapse
 * - Video tutorials section
 * - Keyboard shortcuts reference
 * - Printable documentation
 * - Table of contents with anchor navigation
 * - Copy code buttons
 *
 * @package CIS_Intelligence_Dashboard
 * @subpackage Documentation
 * @version 2.0.0
 */

declare(strict_types=1);

// Page configuration
$page_title = 'Documentation';
$page_subtitle = 'Comprehensive guides and reference materials';
$current_page = 'documentation';

// Get available rule categories for rule explorer
$rule_categories_query = $pdo->query("
    SELECT DISTINCT category, COUNT(*) as rule_count
    FROM code_standards
    WHERE project_id = {$current_project_id}
    GROUP BY category
    ORDER BY category
");
$rule_categories = $rule_categories_query->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-content">
        <div>
            <h1 class="page-title">
                <i class="fas fa-book"></i>
                <?= htmlspecialchars($page_title) ?>
            </h1>
            <p class="page-subtitle"><?= htmlspecialchars($page_subtitle) ?></p>
        </div>
        <div class="page-actions">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print"></i>
                Print Docs
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportDocs()">
                <i class="fas fa-download"></i>
                Export PDF
            </button>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control"
                        id="docSearch"
                        placeholder="Search documentation... (try 'API', 'rules', 'setup', etc.)"
                        onkeyup="searchDocs()"
                    >
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <small class="text-muted ms-2">
                    <i class="fas fa-info-circle"></i>
                    Press <kbd>Ctrl</kbd> + <kbd>K</kbd> to focus search
                </small>
            </div>
            <div class="col-lg-4 text-end">
                <div class="btn-group" role="group">
                    <input type="radio" class="btn-check" name="docView" id="viewAll" checked onchange="filterByCategory('all')">
                    <label class="btn btn-outline-primary" for="viewAll">All</label>

                    <input type="radio" class="btn-check" name="docView" id="viewGuides" onchange="filterByCategory('guides')">
                    <label class="btn btn-outline-primary" for="viewGuides">Guides</label>

                    <input type="radio" class="btn-check" name="docView" id="viewAPI" onchange="filterByCategory('api')">
                    <label class="btn btn-outline-primary" for="viewAPI">API</label>

                    <input type="radio" class="btn-check" name="docView" id="viewFAQ" onchange="filterByCategory('faq')">
                    <label class="btn btn-outline-primary" for="viewFAQ">FAQ</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="card position-sticky" style="top: 80px;">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i>
                    Table of Contents
                </h5>
            </div>
            <div class="list-group list-group-flush doc-toc">
                <a href="#getting-started" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-play-circle me-2"></i>Getting Started
                </a>
                <a href="#dashboard-overview" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview
                </a>
                <a href="#api-reference" class="list-group-item list-group-item-action" data-category="api">
                    <i class="fas fa-plug me-2"></i>API Reference
                </a>
                <a href="#rule-explorer" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-gavel me-2"></i>Rule Explorer
                </a>
                <a href="#best-practices" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-star me-2"></i>Best Practices
                </a>
                <a href="#database-schema" class="list-group-item list-group-item-action" data-category="api">
                    <i class="fas fa-database me-2"></i>Database Schema
                </a>
                <a href="#troubleshooting" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-wrench me-2"></i>Troubleshooting
                </a>
                <a href="#keyboard-shortcuts" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-keyboard me-2"></i>Shortcuts
                </a>
                <a href="#video-tutorials" class="list-group-item list-group-item-action" data-category="guides">
                    <i class="fas fa-video me-2"></i>Video Tutorials
                </a>
                <a href="#faq" class="list-group-item list-group-item-action" data-category="faq">
                    <i class="fas fa-question-circle me-2"></i>FAQ
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
        <!-- Getting Started -->
        <div class="card mb-4 doc-section" id="getting-started" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-play-circle me-2"></i>
                    Getting Started
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Welcome to the Intelligence Hub Dashboard</h6>
                <p class="lead">
                    This system provides comprehensive code intelligence, quality analysis, and project management capabilities.
                </p>

                <div class="alert alert-info border-start border-4 border-info">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="alert-heading">Quick Tip</h6>
                            <p class="mb-0">Use the sidebar menu to navigate between different sections. Most pages include real-time data and interactive filters.</p>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mt-4">Quick Start Guide</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">1</span>
                                <h6 class="mb-0">Dashboard Overview</h6>
                            </div>
                            <p class="small mb-0">View system statistics, health metrics, and recent activity at a glance.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">2</span>
                                <h6 class="mb-0">Create a Project</h6>
                            </div>
                            <p class="small mb-0">Set up your first project in the Projects page with scan configuration.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">3</span>
                                <h6 class="mb-0">Configure Scans</h6>
                            </div>
                            <p class="small mb-0">Set up automated code scans with custom rules and schedules.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary rounded-circle me-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">4</span>
                                <h6 class="mb-0">Review Results</h6>
                            </div>
                            <p class="small mb-0">Analyze code quality, violations, dependencies, and metrics.</p>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold mt-4">System Requirements</h6>
                <ul>
                    <li><strong>Browser:</strong> Chrome 90+, Firefox 88+, Safari 14+, Edge 90+</li>
                    <li><strong>Screen Resolution:</strong> Minimum 1280x720 (responsive design supports mobile)</li>
                    <li><strong>JavaScript:</strong> Must be enabled</li>
                    <li><strong>Cookies:</strong> Required for authentication and preferences</li>
                </ul>
            </div>
        </div>

        <!-- Dashboard Overview -->
        <div class="card mb-4 doc-section" id="dashboard-overview" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard Overview
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Main Dashboard Pages</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 150px;">Page</th>
                                <th>Description</th>
                                <th style="width: 120px;">Key Features</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Overview</strong></td>
                                <td>System health, statistics, and recent activity dashboard</td>
                                <td><span class="badge bg-info">Real-time</span></td>
                            </tr>
                            <tr>
                                <td><strong>Files</strong></td>
                                <td>Browse and explore indexed files with filters</td>
                                <td><span class="badge bg-success">Searchable</span></td>
                            </tr>
                            <tr>
                                <td><strong>Metrics</strong></td>
                                <td>Code quality metrics with visualizations</td>
                                <td><span class="badge bg-warning">Charts</span></td>
                            </tr>
                            <tr>
                                <td><strong>Scan History</strong></td>
                                <td>Timeline of all code scans with details</td>
                                <td><span class="badge bg-primary">Filterable</span></td>
                            </tr>
                            <tr>
                                <td><strong>Dependencies</strong></td>
                                <td>Visualize code dependencies and relationships</td>
                                <td><span class="badge bg-secondary">Graph View</span></td>
                            </tr>
                            <tr>
                                <td><strong>Violations</strong></td>
                                <td>Code quality issues and resolution tracking</td>
                                <td><span class="badge bg-danger">Actionable</span></td>
                            </tr>
                            <tr>
                                <td><strong>Rules</strong></td>
                                <td>Configure code quality rules and standards</td>
                                <td><span class="badge bg-info">Customizable</span></td>
                            </tr>
                            <tr>
                                <td><strong>Projects</strong></td>
                                <td>Manage multiple projects and configurations</td>
                                <td><span class="badge bg-success">Multi-project</span></td>
                            </tr>
                            <tr>
                                <td><strong>Business Units</strong></td>
                                <td>Team and organizational unit management</td>
                                <td><span class="badge bg-warning">Team Mgmt</span></td>
                            </tr>
                            <tr>
                                <td><strong>Settings</strong></td>
                                <td>System configuration and preferences</td>
                                <td><span class="badge bg-dark">Admin</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- API Reference -->
        <div class="card mb-4 doc-section" id="api-reference" data-category="api">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-plug me-2"></i>
                    API Reference
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning border-start border-4 border-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Authentication Required:</strong> All API endpoints require valid session authentication.
                </div>

                <h6 class="fw-bold">Available Endpoints</h6>

                <!-- Files API -->
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-success me-2">GET</span>
                            <code class="text-primary">/api/files</code>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyCode('api-files')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <p class="mb-2"><strong>Description:</strong> List all indexed files with optional filtering</p>
                    <p class="mb-2"><strong>Parameters:</strong></p>
                    <ul class="small mb-2">
                        <li><code>project_id</code> (integer, required) - Project identifier</li>
                        <li><code>file_type</code> (string, optional) - Filter by file extension</li>
                        <li><code>limit</code> (integer, optional) - Number of results (default: 50)</li>
                        <li><code>offset</code> (integer, optional) - Pagination offset</li>
                    </ul>
                    <p class="mb-2"><strong>Example Request:</strong></p>
                    <pre class="bg-dark text-light p-3 rounded" id="api-files"><code>GET /api/files?project_id=1&file_type=php&limit=20

// Response
{
  "success": true,
  "data": {
    "files": [
      {
        "id": 123,
        "file_path": "src/Controller/UserController.php",
        "file_type": "php",
        "file_size": 5432,
        "lines": 187,
        "last_scanned": "2025-10-31 14:30:00"
      }
    ],
    "total": 156,
    "limit": 20,
    "offset": 0
  }
}</code></pre>
                </div>

                <!-- Search API -->
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-success me-2">GET</span>
                            <code class="text-primary">/api/search</code>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyCode('api-search')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <p class="mb-2"><strong>Description:</strong> Search codebase with semantic query</p>
                    <p class="mb-2"><strong>Parameters:</strong></p>
                    <ul class="small mb-2">
                        <li><code>project_id</code> (integer, required) - Project identifier</li>
                        <li><code>query</code> (string, required) - Search query</li>
                        <li><code>search_in</code> (string, optional) - Search scope: files, functions, all</li>
                    </ul>
                    <p class="mb-2"><strong>Example Request:</strong></p>
                    <pre class="bg-dark text-light p-3 rounded" id="api-search"><code>GET /api/search?project_id=1&query=authentication&search_in=functions

// Response
{
  "success": true,
  "data": {
    "results": [
      {
        "type": "function",
        "name": "authenticateUser",
        "file": "src/Auth/Authentication.php",
        "line": 45,
        "snippet": "public function authenticateUser($username, $password)"
      }
    ],
    "total": 8
  }
}</code></pre>
                </div>

                <!-- Scan API -->
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-primary me-2">POST</span>
                            <code class="text-primary">/api/scan</code>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyCode('api-scan')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <p class="mb-2"><strong>Description:</strong> Trigger a code scan for a project</p>
                    <p class="mb-2"><strong>Parameters:</strong></p>
                    <ul class="small mb-2">
                        <li><code>project_id</code> (integer, required) - Project identifier</li>
                        <li><code>scan_type</code> (string, required) - Type: quick, standard, full, exhaustive</li>
                        <li><code>config_id</code> (integer, optional) - Use specific scan configuration</li>
                    </ul>
                    <p class="mb-2"><strong>Example Request:</strong></p>
                    <pre class="bg-dark text-light p-3 rounded" id="api-scan"><code>POST /api/scan
Content-Type: application/json

{
  "project_id": 1,
  "scan_type": "standard",
  "config_id": 5
}

// Response
{
  "success": true,
  "data": {
    "scan_id": 789,
    "status": "queued",
    "estimated_duration": 120,
    "message": "Scan queued successfully"
  }
}</code></pre>
                </div>

                <!-- Violations API -->
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-success me-2">GET</span>
                            <code class="text-primary">/api/violations</code>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyCode('api-violations')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <p class="mb-2"><strong>Description:</strong> Get code violations with filtering</p>
                    <p class="mb-2"><strong>Parameters:</strong></p>
                    <ul class="small mb-2">
                        <li><code>project_id</code> (integer, required) - Project identifier</li>
                        <li><code>severity</code> (string, optional) - Filter: critical, high, medium, low</li>
                        <li><code>status</code> (string, optional) - Filter: open, resolved, suppressed</li>
                        <li><code>file_id</code> (integer, optional) - Filter by file</li>
                    </ul>
                    <p class="mb-2"><strong>Example Request:</strong></p>
                    <pre class="bg-dark text-light p-3 rounded" id="api-violations"><code>GET /api/violations?project_id=1&severity=high&status=open

// Response
{
  "success": true,
  "data": {
    "violations": [
      {
        "id": 456,
        "rule_name": "SQL Injection Risk",
        "severity": "high",
        "file_path": "src/Database/Query.php",
        "line": 78,
        "message": "Unsanitized user input in SQL query",
        "status": "open"
      }
    ],
    "total": 12
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Rule Explorer -->
        <div class="card mb-4 doc-section" id="rule-explorer" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-gavel me-2"></i>
                    Rule Explorer
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">Explore code quality rules with examples of correct and incorrect usage.</p>

                <div class="mb-3">
                    <label class="form-label fw-bold">Select Rule Category:</label>
                    <select class="form-select" id="ruleCategory" onchange="loadRuleExamples()">
                        <option value="">Choose a category...</option>
                        <?php foreach ($rule_categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>">
                            <?= htmlspecialchars($cat['category']) ?> (<?= $cat['rule_count'] ?> rules)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="ruleExamples">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-arrow-up fa-3x mb-3"></i>
                        <p>Select a rule category above to view examples</p>
                    </div>
                </div>

                <!-- Example structure (will be populated dynamically) -->
                <div id="ruleExampleTemplate" style="display: none;">
                    <div class="rule-example mb-4">
                        <h6 class="fw-bold rule-name"></h6>
                        <p class="rule-description"></p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border border-danger rounded p-3 bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                        <strong class="text-danger">Bad Example</strong>
                                    </div>
                                    <pre class="mb-0 bad-example"><code></code></pre>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border border-success rounded p-3 bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <strong class="text-success">Good Example</strong>
                                    </div>
                                    <pre class="mb-0 good-example"><code></code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Practices -->
        <div class="card mb-4 doc-section" id="best-practices" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>
                    Best Practices
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Code Quality Guidelines</h6>

                <div class="accordion" id="bestPracticesAccordion">
                    <!-- Security -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#practice1">
                                <i class="fas fa-shield-alt text-danger me-2"></i>
                                Security Best Practices
                            </button>
                        </h2>
                        <div id="practice1" class="accordion-collapse collapse show" data-bs-parent="#bestPracticesAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>Input Validation:</strong> Always validate and sanitize user input before processing</li>
                                    <li><strong>SQL Injection:</strong> Use prepared statements for all database queries</li>
                                    <li><strong>XSS Prevention:</strong> Escape output when rendering user-generated content</li>
                                    <li><strong>CSRF Protection:</strong> Implement token-based CSRF protection for forms</li>
                                    <li><strong>Authentication:</strong> Use secure password hashing (bcrypt, Argon2)</li>
                                    <li><strong>Session Security:</strong> Regenerate session IDs after login</li>
                                </ul>
                                <div class="alert alert-danger">
                                    <strong>Critical:</strong> Never store passwords in plain text or use MD5/SHA1 for hashing.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#practice2">
                                <i class="fas fa-tachometer-alt text-success me-2"></i>
                                Performance Optimization
                            </button>
                        </h2>
                        <div id="practice2" class="accordion-collapse collapse" data-bs-parent="#bestPracticesAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>Database Queries:</strong> Use indexes on frequently queried columns</li>
                                    <li><strong>N+1 Queries:</strong> Avoid loading related data in loops (use eager loading)</li>
                                    <li><strong>Caching:</strong> Cache expensive operations and database results</li>
                                    <li><strong>Asset Optimization:</strong> Minify and compress CSS/JS files</li>
                                    <li><strong>Lazy Loading:</strong> Load resources only when needed</li>
                                    <li><strong>Code Profiling:</strong> Use profilers to identify bottlenecks</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Code Style -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#practice3">
                                <i class="fas fa-code text-primary me-2"></i>
                                Code Style & Standards
                            </button>
                        </h2>
                        <div id="practice3" class="accordion-collapse collapse" data-bs-parent="#bestPracticesAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>PSR Standards:</strong> Follow PSR-1, PSR-12 for PHP code style</li>
                                    <li><strong>Naming Conventions:</strong> Use descriptive, meaningful names for variables and functions</li>
                                    <li><strong>Function Length:</strong> Keep functions under 50 lines when possible</li>
                                    <li><strong>Comments:</strong> Write self-documenting code, add comments for complex logic</li>
                                    <li><strong>DRY Principle:</strong> Don't Repeat Yourself - extract common code</li>
                                    <li><strong>SOLID Principles:</strong> Apply object-oriented design principles</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Testing -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#practice4">
                                <i class="fas fa-vial text-warning me-2"></i>
                                Testing Guidelines
                            </button>
                        </h2>
                        <div id="practice4" class="accordion-collapse collapse" data-bs-parent="#bestPracticesAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>Unit Tests:</strong> Test individual functions and methods in isolation</li>
                                    <li><strong>Integration Tests:</strong> Test how components work together</li>
                                    <li><strong>Coverage:</strong> Aim for 80%+ code coverage</li>
                                    <li><strong>Test Data:</strong> Use fixtures and factories for test data</li>
                                    <li><strong>Mocking:</strong> Mock external dependencies and services</li>
                                    <li><strong>CI/CD:</strong> Run tests automatically on every commit</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Documentation -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#practice5">
                                <i class="fas fa-book text-info me-2"></i>
                                Documentation Standards
                            </button>
                        </h2>
                        <div id="practice5" class="accordion-collapse collapse" data-bs-parent="#bestPracticesAccordion">
                            <div class="accordion-body">
                                <ul>
                                    <li><strong>DocBlocks:</strong> Add PHPDoc comments to all classes and public methods</li>
                                    <li><strong>README:</strong> Maintain an up-to-date README.md in your project root</li>
                                    <li><strong>API Docs:</strong> Document all public API endpoints</li>
                                    <li><strong>Changelog:</strong> Keep a CHANGELOG.md with version history</li>
                                    <li><strong>Examples:</strong> Provide usage examples for complex features</li>
                                    <li><strong>Comments:</strong> Explain "why" not "what" in comments</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Schema -->
        <div class="card mb-4 doc-section" id="database-schema" data-category="api">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-database me-2"></i>
                    Database Schema
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Core Tables</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Description</th>
                                <th>Key Columns</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>projects</code></td>
                                <td>Project configurations and metadata</td>
                                <td>id, name, project_type, status, health_score, created_at</td>
                            </tr>
                            <tr>
                                <td><code>intelligence_files</code></td>
                                <td>Indexed file information</td>
                                <td>id, project_id, file_path, file_type, file_size, lines, last_scanned</td>
                            </tr>
                            <tr>
                                <td><code>code_dependencies</code></td>
                                <td>File and function dependencies</td>
                                <td>id, project_id, source_file, target_file, dependency_type</td>
                            </tr>
                            <tr>
                                <td><code>project_rule_violations</code></td>
                                <td>Code quality violations</td>
                                <td>id, project_id, file_id, rule_id, severity, line, status</td>
                            </tr>
                            <tr>
                                <td><code>code_standards</code></td>
                                <td>Quality rules and standards</td>
                                <td>id, project_id, rule_name, category, severity, pattern, enabled</td>
                            </tr>
                            <tr>
                                <td><code>scan_history</code></td>
                                <td>Audit trail of code scans</td>
                                <td>id, project_id, scan_type, status, files_scanned, violations_found, duration</td>
                            </tr>
                            <tr>
                                <td><code>project_metrics</code></td>
                                <td>Code quality metrics</td>
                                <td>id, project_id, metric_name, metric_value, recorded_at</td>
                            </tr>
                            <tr>
                                <td><code>business_units</code></td>
                                <td>Organizational units</td>
                                <td>id, unit_name, unit_type, status, parent_unit_id, created_at</td>
                            </tr>
                            <tr>
                                <td><code>project_scan_config</code></td>
                                <td>Scan configurations</td>
                                <td>id, project_id, name, scan_type, schedule, enabled, created_at</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-4">Entity Relationships</h6>
                <div class="border rounded p-3 bg-light">
                    <pre class="mb-0"><code>projects (1) → (∞) intelligence_files
projects (1) → (∞) project_rule_violations
projects (1) → (∞) scan_history
projects (1) → (∞) project_scan_config

intelligence_files (1) → (∞) project_rule_violations
intelligence_files (∞) ← → (∞) code_dependencies

business_units (1) → (∞) business_units (parent/child)
business_units (∞) ← → (∞) projects (via project_unit_mapping)</code></pre>
                </div>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class="card mb-4 doc-section" id="troubleshooting" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-wrench me-2"></i>
                    Troubleshooting
                </h5>
            </div>
            <div class="card-body">
                <h6 class="fw-bold">Common Issues & Solutions</h6>

                <div class="accordion" id="troubleshootingAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                <i class="fas fa-exclamation-circle text-warning me-2"></i>
                                Scanner not updating / No recent scans
                            </button>
                        </h2>
                        <div id="issue1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Possible Causes:</strong></p>
                                <ul>
                                    <li>Scan configuration is disabled</li>
                                    <li>Cron job not running</li>
                                    <li>Scanner process crashed</li>
                                    <li>Insufficient permissions on scan directory</li>
                                </ul>
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Check scan configuration status in <strong>Scan Config</strong> page</li>
                                    <li>Verify cron schedule is active: Settings → Advanced → Cron Jobs</li>
                                    <li>Check system logs for errors: Settings → Advanced → Debug & Logging</li>
                                    <li>Manually trigger a scan: Projects → Select Project → Run Scan</li>
                                    <li>Verify file permissions: <code>chmod -R 755 /path/to/project</code></li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                <i class="fas fa-search text-danger me-2"></i>
                                Search results empty or inaccurate
                            </button>
                        </h2>
                        <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Possible Causes:</strong></p>
                                <ul>
                                    <li>File index is outdated</li>
                                    <li>Project filter is restricting results</li>
                                    <li>Search query too specific</li>
                                </ul>
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Run a fresh scan to update the file index</li>
                                    <li>Check project filter at top of dashboard - ensure correct project is selected</li>
                                    <li>Try broader search terms or use wildcard patterns</li>
                                    <li>Verify files are within configured include paths (Settings → Scan)</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue3">
                                <i class="fas fa-chart-line text-info me-2"></i>
                                Metrics/Charts not displaying
                            </button>
                        </h2>
                        <div id="issue3" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Possible Causes:</strong></p>
                                <ul>
                                    <li>JavaScript not enabled</li>
                                    <li>Chart.js library not loading</li>
                                    <li>No data available for selected timeframe</li>
                                </ul>
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Enable JavaScript in browser settings</li>
                                    <li>Clear browser cache and reload page (Ctrl+Shift+R)</li>
                                    <li>Check browser console for errors (F12 → Console tab)</li>
                                    <li>Ensure project has completed at least one scan</li>
                                    <li>Try selecting a different date range in filters</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue4">
                                <i class="fas fa-shield-alt text-danger me-2"></i>
                                Too many false-positive violations
                            </button>
                        </h2>
                        <div id="issue4" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Go to <strong>Rules</strong> page</li>
                                    <li>Review enabled rules and adjust severity levels</li>
                                    <li>Disable rules that don't apply to your project</li>
                                    <li>Use "Suppress" button on individual violations to mark them as false positives</li>
                                    <li>Create custom rules tailored to your coding standards</li>
                                    <li>Adjust scan depth in Scan Config (lower depth = fewer false positives)</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue5">
                                <i class="fas fa-clock text-warning me-2"></i>
                                Scans taking too long
                            </button>
                        </h2>
                        <div id="issue5" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Use "Quick" scan type instead of "Exhaustive" for routine checks</li>
                                    <li>Increase parallel workers in Scan Config → Step 5 (Performance)</li>
                                    <li>Add common directories to exclude patterns: <code>vendor/*, node_modules/*, .git/*</code></li>
                                    <li>Reduce max file size limit in scan configuration</li>
                                    <li>Enable incremental scanning to scan only changed files</li>
                                    <li>Increase memory limit if available (Settings → Advanced → Performance)</li>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue6">
                                <i class="fas fa-users text-primary me-2"></i>
                                Cannot add team members to business unit
                            </button>
                        </h2>
                        <div id="issue6" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                <p><strong>Possible Causes:</strong></p>
                                <ul>
                                    <li>Insufficient permissions</li>
                                    <li>User already assigned to unit</li>
                                    <li>User account is inactive</li>
                                </ul>
                                <p><strong>Solutions:</strong></p>
                                <ol>
                                    <li>Verify you have admin permissions for the business unit</li>
                                    <li>Check if user is already a team member (they won't appear in available list)</li>
                                    <li>Ensure user account status is "Active"</li>
                                    <li>Contact system administrator if permission issues persist</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-life-ring me-2"></i>
                    <strong>Still having issues?</strong> Visit the <a href="?page=support" class="alert-link">Support Page</a> to contact our team or check system status.
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="card mb-4 doc-section" id="keyboard-shortcuts" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-keyboard me-2"></i>
                    Keyboard Shortcuts
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">Speed up your workflow with these keyboard shortcuts:</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Navigation</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><kbd>G</kbd> then <kbd>H</kbd></td>
                                    <td>Go to Home/Overview</td>
                                </tr>
                                <tr>
                                    <td><kbd>G</kbd> then <kbd>F</kbd></td>
                                    <td>Go to Files page</td>
                                </tr>
                                <tr>
                                    <td><kbd>G</kbd> then <kbd>M</kbd></td>
                                    <td>Go to Metrics page</td>
                                </tr>
                                <tr>
                                    <td><kbd>G</kbd> then <kbd>V</kbd></td>
                                    <td>Go to Violations page</td>
                                </tr>
                                <tr>
                                    <td><kbd>G</kbd> then <kbd>S</kbd></td>
                                    <td>Go to Settings page</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold">Search & Filters</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>K</kbd></td>
                                    <td>Focus search box</td>
                                </tr>
                                <tr>
                                    <td><kbd>/</kbd></td>
                                    <td>Quick search (anywhere)</td>
                                </tr>
                                <tr>
                                    <td><kbd>Esc</kbd></td>
                                    <td>Clear search / Close modal</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>F</kbd></td>
                                    <td>Open filter panel</td>
                                </tr>
                                <tr>
                                    <td><kbd>Alt</kbd> + <kbd>C</kbd></td>
                                    <td>Clear all filters</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold">Actions</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><kbd>N</kbd></td>
                                    <td>Create new (context-dependent)</td>
                                </tr>
                                <tr>
                                    <td><kbd>E</kbd></td>
                                    <td>Edit selected item</td>
                                </tr>
                                <tr>
                                    <td><kbd>R</kbd></td>
                                    <td>Refresh current page</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>S</kbd></td>
                                    <td>Save (in forms/modals)</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>E</kbd></td>
                                    <td>Export current view</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold">Selection & Bulk Actions</h6>
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>A</kbd></td>
                                    <td>Select all (in tables)</td>
                                </tr>
                                <tr>
                                    <td><kbd>Shift</kbd> + Click</td>
                                    <td>Select range</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + Click</td>
                                    <td>Toggle selection</td>
                                </tr>
                                <tr>
                                    <td><kbd>Delete</kbd></td>
                                    <td>Delete selected items</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>D</kbd></td>
                                    <td>Deselect all</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-success mt-3">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Pro Tip:</strong> Press <kbd>?</kbd> on any page to see page-specific shortcuts.
                </div>
            </div>
        </div>

        <!-- Video Tutorials -->
        <div class="card mb-4 doc-section" id="video-tutorials" data-category="guides">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-video me-2"></i>
                    Video Tutorials
                </h5>
            </div>
            <div class="card-body">
                <p class="lead">Learn by watching these step-by-step video guides:</p>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="ratio ratio-16x9">
                                <div class="bg-dark d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-play-circle fa-4x mb-3"></i>
                                        <p>Getting Started Tutorial</p>
                                        <small>5:30 minutes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Getting Started with Intelligence Hub</h6>
                                <p class="card-text small">
                                    Learn the basics: creating your first project, configuring scans, and navigating the dashboard.
                                </p>
                                <button class="btn btn-sm btn-primary" onclick="playVideo('getting-started')">
                                    <i class="fas fa-play me-1"></i> Watch Tutorial
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="ratio ratio-16x9">
                                <div class="bg-dark d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-play-circle fa-4x mb-3"></i>
                                        <p>Code Quality Tutorial</p>
                                        <small>8:15 minutes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Understanding Code Quality Metrics</h6>
                                <p class="card-text small">
                                    Deep dive into quality scores, violations, and how to improve your codebase health.
                                </p>
                                <button class="btn btn-sm btn-primary" onclick="playVideo('code-quality')">
                                    <i class="fas fa-play me-1"></i> Watch Tutorial
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="ratio ratio-16x9">
                                <div class="bg-dark d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-play-circle fa-4x mb-3"></i>
                                        <p>Rules Configuration</p>
                                        <small>6:45 minutes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Configuring Code Quality Rules</h6>
                                <p class="card-text small">
                                    Learn how to customize rules, create custom standards, and reduce false positives.
                                </p>
                                <button class="btn btn-sm btn-primary" onclick="playVideo('rules-config')">
                                    <i class="fas fa-play me-1"></i> Watch Tutorial
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="ratio ratio-16x9">
                                <div class="bg-dark d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-play-circle fa-4x mb-3"></i>
                                        <p>Team Management</p>
                                        <small>7:20 minutes</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title">Managing Business Units & Teams</h6>
                                <p class="card-text small">
                                    Set up business units, assign team members, configure permissions, and map projects.
                                </p>
                                <button class="btn btn-sm btn-primary" onclick="playVideo('team-mgmt')">
                                    <i class="fas fa-play me-1"></i> Watch Tutorial
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="#" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>
                        View All Tutorials on YouTube
                    </a>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="card mb-4 doc-section" id="faq" data-category="faq">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Frequently Asked Questions
                </h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What programming languages are supported?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                The Intelligence Hub supports PHP, JavaScript, Python, HTML, and CSS. You can configure which file types to scan in the Scan Configuration wizard (Step 3: File Selection).
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How often should I run scans?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                For active development projects, we recommend:<br>
                                • <strong>Quick scans:</strong> Every 6 hours (catches recent changes)<br>
                                • <strong>Standard scans:</strong> Daily (comprehensive quality checks)<br>
                                • <strong>Full scans:</strong> Weekly (deep analysis including dependencies)<br>
                                You can configure automated schedules in Scan Config → Step 2 (Schedule).
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I manage multiple projects in one dashboard?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Use the project dropdown at the top of the dashboard to switch between projects. Each project has its own scan configurations, rules, and metrics. You can also view cross-project reports in the Projects page.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What do the different severity levels mean?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ul class="mb-0">
                                    <li><strong class="text-danger">Critical:</strong> Security vulnerabilities or bugs that must be fixed immediately</li>
                                    <li><strong class="text-warning">High:</strong> Major code quality issues that should be addressed soon</li>
                                    <li><strong class="text-info">Medium:</strong> Moderate issues that impact maintainability</li>
                                    <li><strong class="text-secondary">Low:</strong> Minor style or documentation issues</li>
                                    <li><strong class="text-muted">Info:</strong> Suggestions and best practice recommendations</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How do I export data from the dashboard?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Most pages have an "Export" button in the top right corner. You can export:<br>
                                • <strong>JSON:</strong> Machine-readable format for integration<br>
                                • <strong>CSV:</strong> Spreadsheet format (for tables)<br>
                                • <strong>PDF:</strong> Printable reports (use browser print function)<br>
                                Exports include all visible data after applying filters.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Can I customize the quality rules?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely! Go to the Rules page where you can:<br>
                                • Enable/disable individual rules<br>
                                • Adjust severity levels<br>
                                • Create custom rules with your own patterns<br>
                                • Import/export rule sets<br>
                                • Load presets (Strict, Recommended, Minimal)
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                What is a "health score" and how is it calculated?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                The health score (0-100) is calculated based on:<br>
                                • <strong>30%:</strong> Critical/high severity violations (fewer is better)<br>
                                • <strong>25%:</strong> Code coverage and test presence<br>
                                • <strong>20%:</strong> Documentation completeness<br>
                                • <strong>15%:</strong> Code complexity metrics<br>
                                • <strong>10%:</strong> Dependency health and freshness<br>
                                A score above 80 is considered "excellent", 60-80 is "good", 40-60 is "fair", below 40 needs attention.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                How do I integrate with CI/CD pipelines?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Use our API endpoints to trigger scans and retrieve results:<br>
                                <code>POST /api/scan</code> - Trigger a scan<br>
                                <code>GET /api/violations</code> - Get violations<br>
                                <code>GET /api/metrics</code> - Get quality metrics<br>
                                You can configure webhooks in Settings → Integrations to receive notifications. See the API Reference section above for details.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                Is my code data secure and private?
                            </button>
                        </h2>
                        <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Your code never leaves your server. The Intelligence Hub runs entirely on your infrastructure. All scans are performed locally, and data is stored in your project database. See our <a href="?page=privacy">Privacy Policy</a> for full details.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                Where can I get support?
                            </button>
                        </h2>
                        <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Visit the <a href="?page=support">Support Page</a> to:<br>
                                • Submit a support ticket<br>
                                • Check system status<br>
                                • Browse the knowledge base<br>
                                • Contact our support team<br>
                                You can also join our community forum or check our GitHub repository for updates.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Documentation Page JavaScript
const DashboardApp = DashboardApp || {};

// Search functionality
function searchDocs() {
    const query = document.getElementById('docSearch').value.toLowerCase();
    const sections = document.querySelectorAll('.doc-section');
    let visibleCount = 0;

    sections.forEach(section => {
        const text = section.textContent.toLowerCase();
        if (text.includes(query)) {
            section.style.display = '';
            visibleCount++;
        } else {
            section.style.display = 'none';
        }
    });

    // Show message if no results
    if (visibleCount === 0 && query !== '') {
        showNoResults();
    }
}

function clearSearch() {
    document.getElementById('docSearch').value = '';
    document.querySelectorAll('.doc-section').forEach(section => {
        section.style.display = '';
    });
}

function filterByCategory(category) {
    const sections = document.querySelectorAll('.doc-section');
    const tocLinks = document.querySelectorAll('.doc-toc a');

    if (category === 'all') {
        sections.forEach(section => section.style.display = '');
        tocLinks.forEach(link => link.style.display = '');
    } else {
        sections.forEach(section => {
            if (section.dataset.category === category) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
            }
        });

        tocLinks.forEach(link => {
            if (link.dataset.category === category) {
                link.style.display = '';
            } else {
                link.style.display = 'none';
            }
        });
    }
}

// Copy code functionality
function copyCode(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;

    navigator.clipboard.writeText(text).then(() => {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary');

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}

// Rule examples loader
function loadRuleExamples() {
    const category = document.getElementById('ruleCategory').value;
    const container = document.getElementById('ruleExamples');

    if (!category) {
        container.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="fas fa-arrow-up fa-3x mb-3"></i>
                <p>Select a rule category above to view examples</p>
            </div>
        `;
        return;
    }

    // Show loading
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading ${category} rules...</p>
        </div>
    `;

    // In production, this would fetch from API
    // For now, show example structure
    setTimeout(() => {
        container.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Showing examples for <strong>${category}</strong> category
            </div>
            <div class="text-muted text-center py-4">
                <p>Rule examples would be loaded here from the API</p>
                <p class="small">This section is populated dynamically based on your configured rules</p>
            </div>
        `;
    }, 500);
}

// Video player
function playVideo(videoId) {
    alert('Video player would open here for: ' + videoId);
    // In production, this would open a modal with embedded video player
}

// Export documentation
function exportDocs() {
    // In production, this would generate PDF
    window.print();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+K or / - Focus search
    if ((e.ctrlKey && e.key === 'k') || e.key === '/') {
        e.preventDefault();
        document.getElementById('docSearch').focus();
    }

    // Esc - Clear search
    if (e.key === 'Escape') {
        clearSearch();
    }

    // ? - Show shortcuts (placeholder)
    if (e.key === '?') {
        e.preventDefault();
        alert('Keyboard shortcuts help would appear here');
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('.doc-toc a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const target = document.getElementById(targetId);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            // Update active link
            document.querySelectorAll('.doc-toc a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (typeof DashboardApp.init === 'function') {
        DashboardApp.init();
    }

    // Highlight current section in TOC while scrolling
    window.addEventListener('scroll', () => {
        const sections = document.querySelectorAll('.doc-section');
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (window.pageYOffset >= sectionTop - 100) {
                current = section.getAttribute('id');
            }
        });

        document.querySelectorAll('.doc-toc a').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes-v2/footer.php'; ?>
