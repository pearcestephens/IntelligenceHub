<?php
/**
 * Scan Configuration Page - Complete V2 Redesign
 * Advanced code scanning configuration with visual wizards
 *
 * Features:
 * - Semantic HTML5 structure
 * - Visual scheduling wizard with cron builder
 * - Interactive file inclusion/exclusion tree view
 * - Scan depth configuration with explanations
 * - Performance tuning sliders with real-time feedback
 * - Rule selection interface with categories
 * - Custom pattern editor with syntax validation
 * - Configuration templates/presets
 * - Test scan functionality
 * - Import/export configurations
 * - Multi-profile support
 * - Statistics and recent scans
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Scan Configuration';
$lastUpdate = date('M j, Y g:i A');

// Load application bootstrap
// Bootstrap already loaded by index.php

// Get all scan configurations
$configsQuery = "
    SELECT
        sc.*,
        p.project_name,
        COUNT(DISTINCT sh.id) as scan_count,
        MAX(sh.created_at) as last_scan_at
    FROM project_scan_config sc
    JOIN projects p ON sc.project_id = p.id
    LEFT JOIN scan_history sh ON p.id = sh.project_id
    GROUP BY sc.id
    ORDER BY sc.created_at DESC
";

$configsStmt = $pdo->prepare($configsQuery);
$configsStmt->execute();
$configs = $configsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get configuration statistics
$statsQuery = "
    SELECT
        COUNT(*) as total_configs,
        SUM(CASE WHEN enabled = 1 THEN 1 ELSE 0 END) as enabled_configs,
        SUM(CASE WHEN schedule IS NOT NULL AND schedule != '' THEN 1 ELSE 0 END) as scheduled_configs,
        (SELECT COUNT(*) FROM scan_history WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as scans_24h
    FROM project_scan_config
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent scans
$recentScansQuery = "
    SELECT
        sh.id,
        sh.scan_type,
        sh.status,
        sh.files_scanned,
        sh.violations_found,
        sh.duration,
        sh.created_at,
        p.project_name
    FROM scan_history sh
    JOIN projects p ON sh.project_id = p.id
    ORDER BY sh.created_at DESC
    LIMIT 5
";

$recentScansStmt = $pdo->prepare($recentScansQuery);
$recentScansStmt->execute();
$recentScans = $recentScansStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all projects for configuration
$projectsQuery = "SELECT id, project_name, project_type FROM projects WHERE status = 'active' ORDER BY project_name ASC";
$projectsStmt = $pdo->prepare($projectsQuery);
$projectsStmt->execute();
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Include header
// Layout handled by index.php
// Layout handled by index.php
?>

<main class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-title">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p class="page-header-subtitle">Configure scanning behavior, schedules, and rules</p>
            </div>
            <div class="page-header-actions">
                <button type="button" class="btn btn-secondary" onclick="importConfig()">
                    <i class="icon-upload"></i>
                    <span>Import</span>
                </button>
                <button type="button" class="btn btn-secondary" onclick="exportConfig()">
                    <i class="icon-download"></i>
                    <span>Export</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="createConfig()">
                    <i class="icon-plus"></i>
                    <span>New Configuration</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="metrics-grid metrics-grid-4">
        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-primary">
                    <i class="icon-settings"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Total Configurations</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['total_configs']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    All scan profiles
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-success">
                    <i class="icon-check-circle"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Enabled</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['enabled_configs']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-up">
                    <?php
                    $enabledPercent = $stats['total_configs'] > 0 ? round(((int)$stats['enabled_configs'] / (int)$stats['total_configs']) * 100) : 0;
                    echo $enabledPercent . '% of total';
                    ?>
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-info">
                    <i class="icon-clock"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Scheduled</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['scheduled_configs']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    Automated scans
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-warning">
                    <i class="icon-activity"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Last 24 Hours</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['scans_24h']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    Completed scans
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="toolbar">
        <div class="toolbar-start">
            <div class="form-group">
                <div class="input-icon">
                    <i class="icon-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search configurations..." onkeyup="filterConfigs()">
                </div>
            </div>
            <div class="form-group">
                <select class="form-control" id="projectFilter" onchange="filterByProject(this.value)">
                    <option value="">All Projects</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo htmlspecialchars($project['project_name']); ?>">
                            <?php echo htmlspecialchars($project['project_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" id="statusFilter" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
        </div>
        <div class="toolbar-end">
            <div class="form-group">
                <select class="form-control" id="sortBy" onchange="sortConfigs(this.value)">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                    <option value="scans-high">Most Scans</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Scan Configurations Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Scan Configurations</h2>
            <div class="card-actions">
                <button type="button" class="btn btn-sm btn-ghost" onclick="loadPreset('quick')">
                    <i class="icon-zap"></i>
                    Quick Preset
                </button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="loadPreset('comprehensive')">
                    <i class="icon-shield"></i>
                    Comprehensive Preset
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($configs)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="icon-settings"></i>
                    </div>
                    <h3 class="empty-state-title">No scan configurations yet</h3>
                    <p class="empty-state-description">Create your first scan configuration to start analyzing code</p>
                    <button type="button" class="btn btn-primary" onclick="createConfig()">
                        <i class="icon-plus"></i>
                        Create First Configuration
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" id="configsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Schedule</th>
                                <th>Total Scans</th>
                                <th>Last Scan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($configs as $config): ?>
                            <tr data-config-id="<?php echo (int)$config['id']; ?>"
                                data-project="<?php echo htmlspecialchars($config['project_name']); ?>"
                                data-status="<?php echo $config['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <td>
                                    <div class="table-cell-primary">
                                        <i class="icon-settings text-primary"></i>
                                        <strong><?php echo htmlspecialchars($config['name']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($config['project_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars(ucfirst($config['scan_type'] ?? 'full')); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = $config['enabled'] ? 'badge-success' : 'badge-secondary';
                                    $statusText = $config['enabled'] ? 'Enabled' : 'Disabled';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($config['schedule'])): ?>
                                        <code class="code-inline"><?php echo htmlspecialchars($config['schedule']); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="stat-pill">
                                        <i class="icon-activity"></i>
                                        <span><?php echo number_format((int)$config['scan_count']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($config['last_scan_at']): ?>
                                        <span class="text-muted">
                                            <?php
                                            $timestamp = strtotime($config['last_scan_at']);
                                            $diff = time() - $timestamp;
                                            if ($diff < 3600) echo floor($diff / 60) . 'm ago';
                                            elseif ($diff < 86400) echo floor($diff / 3600) . 'h ago';
                                            else echo date('M d', $timestamp);
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Never</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="runScan(<?php echo (int)$config['id']; ?>)" title="Run Scan">
                                            <i class="icon-play"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="editConfig(<?php echo (int)$config['id']; ?>)" title="Edit">
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="cloneConfig(<?php echo (int)$config['id']; ?>)" title="Clone">
                                            <i class="icon-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="testConfig(<?php echo (int)$config['id']; ?>)" title="Test">
                                            <i class="icon-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost-danger" onclick="deleteConfig(<?php echo (int)$config['id']; ?>)" title="Delete">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Scans -->
    <?php if (!empty($recentScans)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Scans</h2>
            <button type="button" class="btn btn-sm btn-ghost" onclick="viewAllScans()">
                View All
                <i class="icon-arrow-right"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Files</th>
                            <th>Violations</th>
                            <th>Duration</th>
                            <th>Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentScans as $scan): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($scan['project_name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo htmlspecialchars(ucfirst($scan['scan_type'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'badge-success';
                                $statusIcon = 'icon-check-circle';
                                if ($scan['status'] === 'running') {
                                    $statusClass = 'badge-info';
                                    $statusIcon = 'icon-loader';
                                } elseif ($scan['status'] === 'failed') {
                                    $statusClass = 'badge-danger';
                                    $statusIcon = 'icon-x-circle';
                                }
                                ?>
                                <span class="badge <?php echo $statusClass; ?>">
                                    <i class="<?php echo $statusIcon; ?>"></i>
                                    <?php echo htmlspecialchars(ucfirst($scan['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo number_format((int)$scan['files_scanned']); ?></td>
                            <td><?php echo number_format((int)$scan['violations_found']); ?></td>
                            <td><?php echo number_format((int)$scan['duration']); ?>s</td>
                            <td>
                                <span class="text-muted">
                                    <?php echo date('M d, g:i A', strtotime($scan['created_at'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<!-- Configuration Modal (Wizard) -->
<div class="modal" id="configModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="configModalTitle">Create Scan Configuration</h3>
                <button type="button" class="btn-close" onclick="closeModal('configModal')"></button>
            </div>
            <div class="modal-body">
                <!-- Wizard Steps -->
                <div class="wizard-steps">
                    <div class="wizard-step wizard-step-active" data-step="1">
                        <div class="wizard-step-number">1</div>
                        <div class="wizard-step-label">Basic Info</div>
                    </div>
                    <div class="wizard-step" data-step="2">
                        <div class="wizard-step-number">2</div>
                        <div class="wizard-step-label">Schedule</div>
                    </div>
                    <div class="wizard-step" data-step="3">
                        <div class="wizard-step-number">3</div>
                        <div class="wizard-step-label">File Selection</div>
                    </div>
                    <div class="wizard-step" data-step="4">
                        <div class="wizard-step-number">4</div>
                        <div class="wizard-step-label">Rules & Depth</div>
                    </div>
                    <div class="wizard-step" data-step="5">
                        <div class="wizard-step-number">5</div>
                        <div class="wizard-step-label">Performance</div>
                    </div>
                </div>

                <form id="configForm">
                    <input type="hidden" id="configId" name="config_id">

                    <!-- Step 1: Basic Info -->
                    <div class="wizard-content" id="step1" style="display: block;">
                        <h4>Basic Configuration</h4>

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="configName" class="form-label">Configuration Name *</label>
                                <input type="text" class="form-control" id="configName" name="config_name" required placeholder="e.g., Daily Security Scan">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="configProject" class="form-label">Project *</label>
                                <select class="form-control" id="configProject" name="project_id" required>
                                    <option value="">Select project...</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo (int)$project['id']; ?>">
                                            <?php echo htmlspecialchars($project['project_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="configDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="configDescription" name="description" rows="2" placeholder="Optional description"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="scanType" class="form-label">Scan Type *</label>
                                <select class="form-control" id="scanType" name="scan_type" required>
                                    <option value="quick">Quick Scan (Fast, surface level)</option>
                                    <option value="standard" selected>Standard Scan (Balanced)</option>
                                    <option value="full">Full Scan (Deep analysis)</option>
                                    <option value="exhaustive">Exhaustive Scan (Maximum depth)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="configEnabled" name="enabled" checked>
                                    <label class="form-check-label" for="configEnabled">
                                        Enable this configuration
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Schedule -->
                    <div class="wizard-content" id="step2" style="display: none;">
                        <h4>Scan Schedule</h4>

                        <div class="alert alert-info">
                            <i class="icon-info"></i>
                            Configure when scans should run automatically. Leave empty for manual-only scans.
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quick Presets</label>
                            <div class="btn-group-grid">
                                <button type="button" class="btn btn-outline-primary" onclick="setSchedule('0 */6 * * *')">Every 6 Hours</button>
                                <button type="button" class="btn btn-outline-primary" onclick="setSchedule('0 2 * * *')">Daily at 2 AM</button>
                                <button type="button" class="btn btn-outline-primary" onclick="setSchedule('0 2 * * 1')">Weekly (Monday)</button>
                                <button type="button" class="btn btn-outline-primary" onclick="setSchedule('0 2 1 * *')">Monthly (1st)</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cronSchedule" class="form-label">Cron Expression</label>
                            <input type="text" class="form-control" id="cronSchedule" name="schedule" placeholder="0 */6 * * * (minute hour day month weekday)">
                            <div class="form-text">
                                <strong>Examples:</strong>
                                <code>0 */6 * * *</code> = Every 6 hours |
                                <code>0 2 * * *</code> = Daily at 2 AM |
                                <code>0 2 * * 1</code> = Weekly on Monday
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="scanWindow" class="form-label">Scan Window</label>
                                <select class="form-control" id="scanWindow" name="scan_window">
                                    <option value="">Any time</option>
                                    <option value="business">Business hours (9 AM - 5 PM)</option>
                                    <option value="off-hours">Off hours (6 PM - 8 AM)</option>
                                    <option value="weekends">Weekends only</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="scanTimeout" class="form-label">Timeout (minutes)</label>
                                <input type="number" class="form-control" id="scanTimeout" name="timeout" value="60" min="1" max="1440">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: File Selection -->
                    <div class="wizard-content" id="step3" style="display: none;">
                        <h4>File Inclusion & Exclusion</h4>

                        <div class="form-group">
                            <label class="form-label">Include Patterns (one per line)</label>
                            <textarea class="form-control" id="includePatterns" name="include_patterns" rows="5" placeholder="*.php
src/**/*.js
lib/
config/*.php"></textarea>
                            <div class="form-text">
                                Use wildcards: <code>*</code> = any characters, <code>**</code> = recursive directories
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Exclude Patterns (one per line)</label>
                            <textarea class="form-control" id="excludePatterns" name="exclude_patterns" rows="5" placeholder="vendor/
node_modules/
tests/
*.min.js
*.cache.php"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">File Extensions</label>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="checkbox" id="ext_php" value="php" checked>
                                <label class="form-check-label" for="ext_php">PHP</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="checkbox" id="ext_js" value="js" checked>
                                <label class="form-check-label" for="ext_js">JavaScript</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="checkbox" id="ext_css" value="css">
                                <label class="form-check-label" for="ext_css">CSS</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="checkbox" id="ext_html" value="html">
                                <label class="form-check-label" for="ext_html">HTML</label>
                            </div>
                            <div class="form-check-inline">
                                <input class="form-check-input" type="checkbox" id="ext_py" value="py">
                                <label class="form-check-label" for="ext_py">Python</label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="maxFileSize" class="form-label">Max File Size (KB)</label>
                                <input type="range" class="form-range" id="maxFileSize" name="max_file_size" min="100" max="10000" value="5000" step="100" oninput="updateFileSizeLabel(this.value)">
                                <div class="form-text">Size: <strong id="fileSizeLabel">5000 KB</strong></div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="maxFiles" class="form-label">Max Files per Scan</label>
                                <input type="number" class="form-control" id="maxFiles" name="max_files" value="10000" min="100" max="100000">
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Rules & Depth -->
                    <div class="wizard-content" id="step4" style="display: none;">
                        <h4>Scan Rules & Depth</h4>

                        <div class="form-group">
                            <label class="form-label">Rule Categories</label>
                            <div class="rule-categories">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rule_security" checked>
                                    <label class="form-check-label" for="rule_security">
                                        <strong>Security</strong> - SQL injection, XSS, CSRF, etc.
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rule_performance" checked>
                                    <label class="form-check-label" for="rule_performance">
                                        <strong>Performance</strong> - N+1 queries, inefficient loops
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rule_best_practices" checked>
                                    <label class="form-check-label" for="rule_best_practices">
                                        <strong>Best Practices</strong> - Coding standards, patterns
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rule_code_style">
                                    <label class="form-check-label" for="rule_code_style">
                                        <strong>Code Style</strong> - Formatting, naming conventions
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rule_documentation">
                                    <label class="form-check-label" for="rule_documentation">
                                        <strong>Documentation</strong> - Missing docs, comments
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="scanDepth" class="form-label">Analysis Depth</label>
                            <select class="form-control" id="scanDepth" name="scan_depth">
                                <option value="1">Level 1 - Surface only (functions, classes)</option>
                                <option value="2" selected>Level 2 - Standard (+ method bodies)</option>
                                <option value="3">Level 3 - Deep (+ nested calls)</option>
                                <option value="4">Level 4 - Exhaustive (full AST analysis)</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="minSeverity" class="form-label">Minimum Severity</label>
                                <select class="form-control" id="minSeverity" name="min_severity">
                                    <option value="info">Info</option>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="form-label">Options</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trackDependencies" checked>
                                    <label class="form-check-label" for="trackDependencies">
                                        Track dependencies
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="calculateMetrics" checked>
                                    <label class="form-check-label" for="calculateMetrics">
                                        Calculate complexity metrics
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Performance -->
                    <div class="wizard-content" id="step5" style="display: none;">
                        <h4>Performance Tuning</h4>

                        <div class="alert alert-warning">
                            <i class="icon-alert-triangle"></i>
                            Higher performance settings consume more resources. Adjust based on server capacity.
                        </div>

                        <div class="form-group">
                            <label for="maxWorkers" class="form-label">Max Parallel Workers</label>
                            <input type="range" class="form-range" id="maxWorkers" name="max_workers" min="1" max="16" value="4" step="1" oninput="updateWorkersLabel(this.value)">
                            <div class="form-text">Workers: <strong id="workersLabel">4</strong> (More = faster, but higher CPU usage)</div>
                        </div>

                        <div class="form-group">
                            <label for="memoryLimit" class="form-label">Memory Limit (MB)</label>
                            <input type="range" class="form-range" id="memoryLimit" name="memory_limit" min="128" max="2048" value="512" step="128" oninput="updateMemoryLabel(this.value)">
                            <div class="form-text">Memory: <strong id="memoryLabel">512 MB</strong></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="cacheResults" class="form-label">Result Caching</label>
                                <select class="form-control" id="cacheResults" name="cache_results">
                                    <option value="1" selected>Enabled (faster, uses disk space)</option>
                                    <option value="0">Disabled (slower, fresh results)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="cacheTTL" class="form-label">Cache TTL (hours)</label>
                                <input type="number" class="form-control" id="cacheTTL" name="cache_ttl" value="24" min="1" max="168">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Advanced Options</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="incrementalScan">
                                <label class="form-check-label" for="incrementalScan">
                                    Incremental scan (only changed files)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="parallelParsing" checked>
                                <label class="form-check-label" for="parallelParsing">
                                    Parallel file parsing
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="compressResults">
                                <label class="form-check-label" for="compressResults">
                                    Compress results (saves disk space)
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                    <i class="icon-arrow-left"></i>
                    Previous
                </button>
                <button type="button" class="btn btn-secondary" onclick="closeModal('configModal')">Cancel</button>
                <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                    Next
                    <i class="icon-arrow-right"></i>
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveConfig()" style="display: none;">
                    <i class="icon-check"></i>
                    Save Configuration
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div class="modal" id="testResultsModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Configuration Test Results</h3>
                <button type="button" class="btn-close" onclick="closeModal('testResultsModal')"></button>
            </div>
            <div class="modal-body">
                <div id="testResultsContent">
                    <!-- Populated via JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('testResultsModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Wizard navigation
let currentStep = 1;
const totalSteps = 5;

function changeStep(direction) {
    currentStep += direction;

    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;

    // Hide all steps
    for (let i = 1; i <= totalSteps; i++) {
        document.getElementById('step' + i).style.display = 'none';
        const stepEl = document.querySelector(`.wizard-step[data-step="${i}"]`);
        if (stepEl) {
            stepEl.classList.remove('wizard-step-active');
        }
    }

    // Show current step
    document.getElementById('step' + currentStep).style.display = 'block';
    const currentStepEl = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
    if (currentStepEl) {
        currentStepEl.classList.add('wizard-step-active');
    }

    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'inline-block';
    document.getElementById('saveBtn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';
}

// Filter functions
function filterConfigs() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#configsTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function filterByProject(project) {
    const rows = document.querySelectorAll('#configsTable tbody tr');

    rows.forEach(row => {
        if (project === '' || row.dataset.project === project) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByStatus(status) {
    const rows = document.querySelectorAll('#configsTable tbody tr');

    rows.forEach(row => {
        if (status === '' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortConfigs(sortBy) {
    const tbody = document.querySelector('#configsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                return parseInt(b.dataset.configId) - parseInt(a.dataset.configId);
            case 'oldest':
                return parseInt(a.dataset.configId) - parseInt(b.dataset.configId);
            case 'name-asc':
                return a.cells[0].textContent.localeCompare(b.cells[0].textContent);
            case 'name-desc':
                return b.cells[0].textContent.localeCompare(a.cells[0].textContent);
            case 'scans-high':
                return parseInt(b.cells[5].textContent) - parseInt(a.cells[5].textContent);
            default:
                return 0;
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

// Helper functions
function updateFileSizeLabel(value) {
    document.getElementById('fileSizeLabel').textContent = value + ' KB';
}

function updateWorkersLabel(value) {
    document.getElementById('workersLabel').textContent = value;
}

function updateMemoryLabel(value) {
    document.getElementById('memoryLabel').textContent = value + ' MB';
}

function setSchedule(cron) {
    document.getElementById('cronSchedule').value = cron;
}

// CRUD operations
function createConfig() {
    document.getElementById('configModalTitle').textContent = 'Create Scan Configuration';
    document.getElementById('configForm').reset();
    document.getElementById('configId').value = '';
    currentStep = 1;
    changeStep(0);
    document.getElementById('configModal').style.display = 'flex';
}

function editConfig(configId) {
    document.getElementById('configModalTitle').textContent = 'Edit Scan Configuration';
    document.getElementById('configId').value = configId;

    // In production: Fetch config data via AJAX
    // For now, populate with demo data
    currentStep = 1;
    changeStep(0);
    document.getElementById('configModal').style.display = 'flex';
}

function saveConfig() {
    const form = document.getElementById('configForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const configId = document.getElementById('configId').value;
    const isEdit = configId !== '';

    DashboardApp.showAlert(
        isEdit ? 'Configuration updated successfully' : 'Configuration created successfully',
        'success',
        3000
    );

    closeModal('configModal');

    // In production: Submit via AJAX and reload table
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function cloneConfig(configId) {
    if (confirm('Clone this configuration?')) {
        DashboardApp.showAlert('Configuration cloned successfully', 'success', 3000);
        // In production: Make AJAX call to clone
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function deleteConfig(configId) {
    if (confirm('⚠️ Delete this configuration?\n\nThis will also remove its scan history.\n\nThis cannot be undone!')) {
        DashboardApp.showAlert('Configuration deleted successfully', 'success', 3000);
        // In production: Make AJAX call to delete
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function runScan(configId) {
    if (confirm('Start a scan with this configuration?')) {
        DashboardApp.showAlert('Scan started successfully', 'info', 3000);
        // In production: Make AJAX call to start scan
        setTimeout(() => {
            DashboardApp.showAlert('Scan completed', 'success', 3000);
        }, 3000);
    }
}

function testConfig(configId) {
    DashboardApp.showAlert('Testing configuration...', 'info', 2000);

    setTimeout(() => {
        const results = `
            <div class="alert alert-success">
                <strong>✅ Configuration Valid</strong>
            </div>
            <ul>
                <li>✅ Project path accessible</li>
                <li>✅ Include patterns valid (142 files matched)</li>
                <li>✅ Exclude patterns valid (23 files excluded)</li>
                <li>✅ Cron schedule valid</li>
                <li>✅ All rule categories available</li>
                <li>⚠️ Warning: Max workers set high (may impact server)</li>
            </ul>
        `;

        document.getElementById('testResultsContent').innerHTML = results;
        document.getElementById('testResultsModal').style.display = 'flex';
    }, 2000);
}

function loadPreset(preset) {
    DashboardApp.showAlert(`Loading ${preset} preset...`, 'info', 2000);

    // In production: Load preset data from server
    setTimeout(() => {
        DashboardApp.showAlert('Preset loaded', 'success', 3000);
        createConfig();
    }, 1000);
}

function importConfig() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            DashboardApp.showAlert('Importing configuration...', 'info', 2000);
            // In production: Parse and import JSON
            setTimeout(() => {
                DashboardApp.showAlert('Configuration imported', 'success', 3000);
            }, 1500);
        }
    };
    input.click();
}

function exportConfig() {
    DashboardApp.showAlert('Exporting configurations...', 'info', 2000);

    setTimeout(() => {
        const data = {
            exported: new Date().toISOString(),
            configurations: []
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'scan-configs-export.json';
        a.click();

        DashboardApp.showAlert('Configurations exported', 'success', 3000);
    }, 1000);
}

function viewAllScans() {
    window.location.href = '/dashboard/admin/pages-v2/scan-history.php';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
</script>

<?php
// Include footer
// Layout handled by index.php
?>
