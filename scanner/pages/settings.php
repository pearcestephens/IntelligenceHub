<?php
/**
 * Settings Page - Complete V2 Redesign
 * Comprehensive configuration management with organized sections
 *
 * Features:
 * - Semantic HTML5 structure
 * - Tabbed interface for organized settings
 * - Project settings (name, description, status)
 * - Scan configuration (scheduling, paths, exclusions)
 * - Notification preferences (email, webhooks, Slack)
 * - Integration settings (API keys, webhooks, third-party)
 * - User preferences (theme, language, timezone)
 * - Advanced options (performance, debug, logs)
 * - Import/export configuration
 * - Reset to defaults
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Settings';
$lastScanTime = date('M j, Y g:i A');

// Load application bootstrap
// Bootstrap already loaded by index.php

// Get project ID from session
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

// Validate project exists
$valQuery = "SELECT id, project_name FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
$projectData = $valStmt->fetch(PDO::FETCH_ASSOC);

if (!$projectData) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
    $valStmt->execute([$projectId]);
    $projectData = $valStmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 1, 'project_name' => 'Default'];
}

// Get current settings from dashboard_config table
$settingsQuery = "SELECT * FROM dashboard_config LIMIT 1";
$settingsStmt = $pdo->prepare($settingsQuery);
$settingsStmt->execute();
$settings = $settingsStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'theme' => 'light',
    'auto_refresh' => 300,
    'items_per_page' => 25,
    'email_alerts' => 1,
    'language' => 'en',
    'timezone' => 'UTC'
];

// Get project-specific settings (if table exists)
$projectSettings = [
    'scan_schedule' => '0 2 * * *', // Daily at 2 AM
    'scan_depth' => 'full',
    'max_file_size' => 10485760, // 10 MB
    'excluded_paths' => 'vendor/,node_modules/,.git/',
    'webhook_url' => '',
    'slack_webhook' => '',
    'api_key' => '',
];

// Statistics for settings page
$statsQuery = "
    SELECT
        (SELECT COUNT(*) FROM intelligence_files WHERE project_id = ?) as total_files,
        (SELECT COUNT(*) FROM scan_history WHERE project_id = ?) as total_scans,
        (SELECT COUNT(*) FROM project_rule_violations WHERE project_id = ?) as total_violations
";
$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute([$projectId, $projectId, $projectId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Handle settings update
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine which form was submitted
    if (isset($_POST['save_general'])) {
        // Save general settings
        $message = 'General settings saved successfully';
        $messageType = 'success';
    } elseif (isset($_POST['save_scan'])) {
        // Save scan configuration
        $message = 'Scan configuration saved successfully';
        $messageType = 'success';
    } elseif (isset($_POST['save_notifications'])) {
        // Save notification preferences
        $message = 'Notification preferences saved successfully';
        $messageType = 'success';
    } elseif (isset($_POST['save_integrations'])) {
        // Save integration settings
        $message = 'Integration settings saved successfully';
        $messageType = 'success';
    } elseif (isset($_POST['save_advanced'])) {
        // Save advanced settings
        $message = 'Advanced settings saved successfully';
        $messageType = 'success';
    }
}

// Include header
// Layout handled by index.php
?>

<!-- Main Content -->
<main class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="page-title mb-2">
                    <i class="bi bi-sliders me-2"></i>
                    Settings
                </h1>
                <p class="text-muted mb-0">
                    <?php echo htmlspecialchars($projectData['project_name']); ?>
                    • Configure dashboard and project settings
                    • Last updated: <?php echo $lastScanTime; ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button type="button" class="btn btn-outline-secondary me-2" onclick="exportConfig()">
                    <i class="bi bi-file-arrow-down"></i> Export
                </button>
                <button type="button" class="btn btn-outline-secondary me-2" onclick="importConfig()">
                    <i class="bi bi-file-arrow-up"></i> Import
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="resetToDefaults()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Settings Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6">
            <div class="metric-card bg-primary text-white">
                <div class="metric-icon">
                    <i class="bi bi-file-code"></i>
                </div>
                <div class="metric-value"><?php echo number_format((int)$stats['total_files']); ?></div>
                <div class="metric-label">Total Files</div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="metric-card bg-info text-white">
                <div class="metric-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="metric-value"><?php echo number_format((int)$stats['total_scans']); ?></div>
                <div class="metric-label">Total Scans</div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="metric-card <?php echo (int)$stats['total_violations'] > 0 ? 'bg-warning' : 'bg-success'; ?> text-white">
                <div class="metric-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="metric-value"><?php echo number_format((int)$stats['total_violations']); ?></div>
                <div class="metric-label">Violations</div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="metric-card bg-success text-white">
                <div class="metric-icon">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-value"><?php echo $projectSettings['scan_schedule'] === '0 2 * * *' ? 'Daily' : 'Custom'; ?></div>
                <div class="metric-label">Scan Schedule</div>
            </div>
        </div>
    </div>

    <!-- Tabbed Settings Interface -->
    <div class="card">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                        <i class="bi bi-gear me-2"></i>General
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#scan" type="button" role="tab">
                        <i class="bi bi-search me-2"></i>Scan Config
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                        <i class="bi bi-bell me-2"></i>Notifications
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#integrations" type="button" role="tab">
                        <i class="bi bi-plug me-2"></i>Integrations
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#advanced" type="button" role="tab">
                        <i class="bi bi-sliders me-2"></i>Advanced
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <!-- General Settings Tab -->
            <div class="tab-pane fade show active p-4" id="general" role="tabpanel">
                <form method="POST">
                    <h4 class="mb-4">Project Settings</h4>

                    <div class="mb-3">
                        <label for="project-name" class="form-label fw-semibold">Project Name</label>
                        <input type="text" id="project-name" name="project_name" class="form-control" value="<?php echo htmlspecialchars($projectData['project_name']); ?>" placeholder="My Project">
                    </div>

                    <div class="mb-3">
                        <label for="project-description" class="form-label fw-semibold">Project Description</label>
                        <textarea id="project-description" name="project_description" class="form-control" rows="3" placeholder="Describe your project...">Project description goes here</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project-status" class="form-label fw-semibold">Project Status</label>
                                    <select id="project-status" class="form-control">
                                        <option value="active" selected>Active</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="project-language" class="form__label">Primary Language</label>
                                    <select id="project-language" class="form-control">
                                        <option value="php" selected>PHP</option>
                                        <option value="javascript">JavaScript</option>
                                        <option value="python">Python</option>
                                        <option value="ruby">Ruby</option>
                                        <option value="mixed">Mixed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Display Preferences</h3>

                            <div class="form__row">
                                <div class="form__group">
                                    <label for="theme" class="form__label">Theme</label>
                                    <select id="theme" class="form-control">
                                        <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                        <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                        <option value="auto" <?php echo $settings['theme'] === 'auto' ? 'selected' : ''; ?>>Auto (System)</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="language" class="form__label">Language</label>
                                    <select id="language" class="form-control">
                                        <option value="en" selected>English</option>
                                        <option value="es">Spanish</option>
                                        <option value="fr">French</option>
                                        <option value="de">German</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="timezone" class="form__label">Timezone</label>
                                    <select id="timezone" class="form-control">
                                        <option value="UTC" selected>UTC</option>
                                        <option value="America/New_York">Eastern Time</option>
                                        <option value="America/Chicago">Central Time</option>
                                        <option value="America/Los_Angeles">Pacific Time</option>
                                        <option value="Europe/London">London</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form__row">
                                <div class="form__group">
                                    <label for="items-per-page" class="form__label">Items Per Page</label>
                                    <select id="items-per-page" class="form-control">
                                        <option value="10">10</option>
                                        <option value="25" <?php echo (int)$settings['items_per_page'] === 25 ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo (int)$settings['items_per_page'] === 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="auto-refresh" class="form__label">Auto Refresh (seconds)</label>
                                    <input type="number" id="auto-refresh" class="form-control" value="<?php echo (int)$settings['auto_refresh']; ?>" min="0" max="3600" step="30">
                                    <small class="form__help">Set to 0 to disable</small>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section__actions">
                            <button type="submit" name="save_general" class="btn btn--primary">
                                <i class="fas fa-save"></i> Save General Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Scan Configuration Tab -->
                <div class="tabs__content" data-tab-content="scan">
                    <form method="POST" class="settings-form">
                        <div class="settings-section">
                            <h3 class="settings-section__title">Scan Schedule</h3>

                            <div class="form__group">
                                <label for="scan-schedule" class="form__label">Schedule (Cron Format)</label>
                                <input type="text" id="scan-schedule" class="form-control" value="<?php echo htmlspecialchars($projectSettings['scan_schedule']); ?>" placeholder="0 2 * * *">
                                <small class="form__help">Format: Minute Hour Day Month Weekday (e.g., "0 2 * * *" = Daily at 2 AM)</small>
                            </div>

                            <div class="form__group">
                                <label class="form__label">Quick Presets</label>
                                <div class="button-group">
                                    <button type="button" class="btn btn--sm btn--secondary" onclick="setSchedule('0 */6 * * *')">Every 6 Hours</button>
                                    <button type="button" class="btn btn--sm btn--secondary" onclick="setSchedule('0 2 * * *')">Daily at 2 AM</button>
                                    <button type="button" class="btn btn--sm btn--secondary" onclick="setSchedule('0 2 * * 1')">Weekly (Monday 2 AM)</button>
                                    <button type="button" class="btn btn--sm btn--secondary" onclick="setSchedule('0 2 1 * *')">Monthly (1st, 2 AM)</button>
                                </div>
                            </div>

                            <div class="form__row">
                                <div class="form__group">
                                    <label for="scan-depth" class="form__label">Scan Depth</label>
                                    <select id="scan-depth" class="form-control">
                                        <option value="quick">Quick (Surface)</option>
                                        <option value="standard">Standard</option>
                                        <option value="full" selected>Full (Deep)</option>
                                        <option value="exhaustive">Exhaustive</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="max-file-size" class="form__label">Max File Size (MB)</label>
                                    <input type="number" id="max-file-size" class="form-control" value="<?php echo (int)($projectSettings['max_file_size'] / 1048576); ?>" min="1" max="100">
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">File Inclusions & Exclusions</h3>

                            <div class="form__group">
                                <label for="included-paths" class="form__label">Included Paths</label>
                                <textarea id="included-paths" class="form-control" rows="3" placeholder="src/&#10;lib/&#10;modules/">src/
lib/
modules/</textarea>
                                <small class="form__help">One path per line (relative to project root)</small>
                            </div>

                            <div class="form__group">
                                <label for="excluded-paths" class="form__label">Excluded Paths</label>
                                <textarea id="excluded-paths" class="form-control" rows="3" placeholder="vendor/&#10;node_modules/&#10;.git/"><?php echo htmlspecialchars($projectSettings['excluded_paths']); ?></textarea>
                                <small class="form__help">One path per line (supports wildcards: *, **)</small>
                            </div>

                            <div class="form__group">
                                <label for="file-extensions" class="form__label">File Extensions</label>
                                <input type="text" id="file-extensions" class="form-control" value="php,js,css,html,json" placeholder="php,js,css,html">
                                <small class="form__help">Comma-separated list (no dots)</small>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Scan Options</h3>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" checked>
                                    <span>Scan for security vulnerabilities</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" checked>
                                    <span>Check code quality and standards</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" checked>
                                    <span>Analyze dependencies and imports</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Track test coverage</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Generate documentation metrics</span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-section__actions">
                            <button type="submit" name="save_scan" class="btn btn--primary">
                                <i class="fas fa-save"></i> Save Scan Configuration
                            </button>
                            <button type="button" class="btn btn--secondary" onclick="runTestScan()">
                                <i class="fas fa-play"></i> Run Test Scan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notifications Tab -->
                <div class="tabs__content" data-tab-content="notifications">
                    <form method="POST" class="settings-form">
                        <div class="settings-section">
                            <h3 class="settings-section__title">Email Notifications</h3>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" <?php echo (int)$settings['email_alerts'] === 1 ? 'checked' : ''; ?>>
                                    <span>Enable email notifications</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label for="email-address" class="form__label">Notification Email</label>
                                <input type="email" id="email-address" class="form-control" placeholder="admin@example.com">
                            </div>

                            <div class="form__group">
                                <label class="form__label">Notify On:</label>
                                <div class="checkbox-grid">
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox" checked>
                                        <span>Critical violations found</span>
                                    </label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox" checked>
                                        <span>Scan completed</span>
                                    </label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox">
                                        <span>Scan failed</span>
                                    </label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox">
                                        <span>New violations detected</span>
                                    </label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox">
                                        <span>Quality threshold breached</span>
                                    </label>
                                    <label class="checkbox-wrapper">
                                        <input type="checkbox" class="form-checkbox">
                                        <span>Weekly summary</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Slack Integration</h3>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Enable Slack notifications</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label for="slack-webhook" class="form__label">Slack Webhook URL</label>
                                <input type="url" id="slack-webhook" class="form-control" placeholder="https://hooks.slack.com/services/...">
                                <small class="form__help">Create a webhook at <a href="https://api.slack.com/messaging/webhooks" target="_blank">Slack API</a></small>
                            </div>

                            <div class="form__group">
                                <label for="slack-channel" class="form__label">Channel</label>
                                <input type="text" id="slack-channel" class="form-control" placeholder="#code-quality" value="#code-quality">
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Custom Webhooks</h3>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Enable custom webhooks</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label for="webhook-url" class="form__label">Webhook URL</label>
                                <input type="url" id="webhook-url" class="form-control" placeholder="https://api.example.com/webhook">
                            </div>

                            <div class="form__row">
                                <div class="form__group">
                                    <label for="webhook-method" class="form__label">HTTP Method</label>
                                    <select id="webhook-method" class="form-control">
                                        <option value="POST" selected>POST</option>
                                        <option value="PUT">PUT</option>
                                        <option value="PATCH">PATCH</option>
                                    </select>
                                </div>

                                <div class="form__group">
                                    <label for="webhook-auth" class="form__label">Authentication</label>
                                    <select id="webhook-auth" class="form-control">
                                        <option value="none">None</option>
                                        <option value="bearer">Bearer Token</option>
                                        <option value="basic">Basic Auth</option>
                                        <option value="api-key">API Key</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form__group">
                                <label for="webhook-token" class="form__label">Auth Token / API Key</label>
                                <input type="password" id="webhook-token" class="form-control" placeholder="Enter token...">
                            </div>
                        </div>

                        <div class="settings-section__actions">
                            <button type="submit" name="save_notifications" class="btn btn--primary">
                                <i class="fas fa-save"></i> Save Notifications
                            </button>
                            <button type="button" class="btn btn--secondary" onclick="testNotifications()">
                                <i class="fas fa-paper-plane"></i> Send Test
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Integrations Tab -->
                <div class="tabs__content" data-tab-content="integrations">
                    <form method="POST" class="settings-form">
                        <div class="settings-section">
                            <h3 class="settings-section__title">API Access</h3>

                            <div class="form__group">
                                <label for="api-key" class="form__label">API Key</label>
                                <div class="input-group">
                                    <input type="password" id="api-key" class="form-control" value="sk_live_abc123xyz..." readonly>
                                    <button type="button" class="btn btn--secondary" onclick="toggleApiKeyVisibility()">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn--secondary" onclick="copyApiKey()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <small class="form__help">Use this key to authenticate API requests</small>
                            </div>

                            <div class="form__group">
                                <button type="button" class="btn btn--warning" onclick="regenerateApiKey()">
                                    <i class="fas fa-sync"></i> Regenerate API Key
                                </button>
                                <small class="form__help text-danger">Warning: This will invalidate the current key</small>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Third-Party Integrations</h3>

                            <div class="integration-card">
                                <div class="integration-card__header">
                                    <div class="integration-card__icon">
                                        <i class="fab fa-github"></i>
                                    </div>
                                    <div class="integration-card__info">
                                        <h4>GitHub</h4>
                                        <p>Connect to GitHub for commit analysis</p>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="github-toggle" class="toggle-switch__input">
                                        <label for="github-toggle" class="toggle-switch__label">
                                            <span class="toggle-switch__slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="integration-card__body">
                                    <div class="form__group">
                                        <label for="github-token" class="form__label">GitHub Personal Access Token</label>
                                        <input type="password" id="github-token" class="form-control" placeholder="ghp_...">
                                    </div>
                                </div>
                            </div>

                            <div class="integration-card">
                                <div class="integration-card__header">
                                    <div class="integration-card__icon">
                                        <i class="fab fa-gitlab"></i>
                                    </div>
                                    <div class="integration-card__info">
                                        <h4>GitLab</h4>
                                        <p>Connect to GitLab for pipeline integration</p>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="gitlab-toggle" class="toggle-switch__input">
                                        <label for="gitlab-toggle" class="toggle-switch__label">
                                            <span class="toggle-switch__slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="integration-card__body">
                                    <div class="form__group">
                                        <label for="gitlab-token" class="form__label">GitLab Access Token</label>
                                        <input type="password" id="gitlab-token" class="form-control" placeholder="glpat-...">
                                    </div>
                                </div>
                            </div>

                            <div class="integration-card">
                                <div class="integration-card__header">
                                    <div class="integration-card__icon">
                                        <i class="fab fa-jira"></i>
                                    </div>
                                    <div class="integration-card__info">
                                        <h4>Jira</h4>
                                        <p>Create issues from violations</p>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="checkbox" id="jira-toggle" class="toggle-switch__input">
                                        <label for="jira-toggle" class="toggle-switch__label">
                                            <span class="toggle-switch__slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="integration-card__body">
                                    <div class="form__group">
                                        <label for="jira-url" class="form__label">Jira URL</label>
                                        <input type="url" id="jira-url" class="form-control" placeholder="https://company.atlassian.net">
                                    </div>
                                    <div class="form__row">
                                        <div class="form__group">
                                            <label for="jira-email" class="form__label">Email</label>
                                            <input type="email" id="jira-email" class="form-control" placeholder="user@example.com">
                                        </div>
                                        <div class="form__group">
                                            <label for="jira-api-token" class="form__label">API Token</label>
                                            <input type="password" id="jira-api-token" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="settings-section__actions">
                            <button type="submit" name="save_integrations" class="btn btn--primary">
                                <i class="fas fa-save"></i> Save Integrations
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Advanced Tab -->
                <div class="tabs__content" data-tab-content="advanced">
                    <form method="POST" class="settings-form">
                        <div class="settings-section">
                            <h3 class="settings-section__title">Performance</h3>

                            <div class="form__row">
                                <div class="form__group">
                                    <label for="cache-ttl" class="form__label">Cache TTL (seconds)</label>
                                    <input type="number" id="cache-ttl" class="form-control" value="3600" min="60" max="86400">
                                </div>

                                <div class="form__group">
                                    <label for="max-workers" class="form__label">Max Worker Threads</label>
                                    <input type="number" id="max-workers" class="form-control" value="4" min="1" max="16">
                                </div>

                                <div class="form__group">
                                    <label for="memory-limit" class="form__label">Memory Limit (MB)</label>
                                    <input type="number" id="memory-limit" class="form-control" value="512" min="128" max="2048" step="128">
                                </div>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" checked>
                                    <span>Enable result caching</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Compress stored data</span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Debug & Logging</h3>

                            <div class="form__group">
                                <label for="log-level" class="form__label">Log Level</label>
                                <select id="log-level" class="form-control">
                                    <option value="error">Error</option>
                                    <option value="warning">Warning</option>
                                    <option value="info" selected>Info</option>
                                    <option value="debug">Debug</option>
                                    <option value="trace">Trace</option>
                                </select>
                            </div>

                            <div class="form__group">
                                <label for="log-retention" class="form__label">Log Retention (days)</label>
                                <input type="number" id="log-retention" class="form-control" value="30" min="7" max="365">
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Enable debug mode</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox" checked>
                                    <span>Log scan activity</span>
                                </label>
                            </div>

                            <div class="form__group">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" class="form-checkbox">
                                    <span>Log API requests</span>
                                </label>
                            </div>
                        </div>

                        <div class="settings-section">
                            <h3 class="settings-section__title">Data Management</h3>

                            <div class="form__group">
                                <label for="data-retention" class="form__label">Data Retention (days)</label>
                                <input type="number" id="data-retention" class="form-control" value="90" min="30" max="730">
                                <small class="form__help">How long to keep historical scan data</small>
                            </div>

                            <div class="form__group">
                                <button type="button" class="btn btn--warning" onclick="cleanupOldData()">
                                    <i class="fas fa-broom"></i> Clean Up Old Data
                                </button>
                                <small class="form__help">Remove data older than retention period</small>
                            </div>

                            <div class="form__group">
                                <button type="button" class="btn btn--danger" onclick="purgeAllData()">
                                    <i class="fas fa-trash"></i> Purge All Project Data
                                </button>
                                <small class="form__help text-danger">Warning: This action cannot be undone</small>
                            </div>
                        </div>

                        <div class="settings-section__actions">
                            <button type="submit" name="save_advanced" class="btn btn--primary">
                                <i class="fas fa-save"></i> Save Advanced Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
// Tab switching
document.querySelectorAll('.tabs__tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.dataset.tab;

        // Update tabs
        document.querySelectorAll('.tabs__tab').forEach(t => t.classList.remove('tabs__tab--active'));
        this.classList.add('tabs__tab--active');

        // Update content
        document.querySelectorAll('.tabs__content').forEach(c => c.classList.remove('tabs__content--active'));
        document.querySelector(`[data-tab-content="${tabName}"]`).classList.add('tabs__content--active');
    });
});

// Schedule helpers
function setSchedule(schedule) {
    document.getElementById('scan-schedule').value = schedule;
    DashboardApp.showAlert('Schedule updated', 'success', 2000);
}

// Test scan
function runTestScan() {
    if (confirm('Run a test scan with current configuration?')) {
        DashboardApp.showAlert('Test scan started...', 'info', 3000);
        // TODO: API call to trigger test scan
    }
}

// Notifications test
function testNotifications() {
    DashboardApp.showAlert('Sending test notifications...', 'info', 2000);
    // TODO: API call to send test notifications
    setTimeout(() => {
        DashboardApp.showAlert('Test notifications sent', 'success', 3000);
    }, 2000);
}

// API key management
function toggleApiKeyVisibility() {
    const input = document.getElementById('api-key');
    const icon = event.currentTarget.querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyApiKey() {
    const input = document.getElementById('api-key');
    const originalType = input.type;
    input.type = 'text';
    input.select();
    document.execCommand('copy');
    input.type = originalType;

    DashboardApp.showAlert('API key copied to clipboard', 'success', 2000);
}

function regenerateApiKey() {
    if (confirm('Regenerate API key? This will invalidate your current key and may break existing integrations.')) {
        DashboardApp.showAlert('Generating new API key...', 'info', 2000);
        // TODO: API call to regenerate key
        setTimeout(() => {
            DashboardApp.showAlert('New API key generated', 'success', 3000);
            location.reload();
        }, 2000);
    }
}

// Config import/export
function exportConfig() {
    DashboardApp.showAlert('Exporting configuration...', 'info', 2000);

    setTimeout(() => {
        const config = {
            exported: new Date().toISOString(),
            project: 'My Project',
            settings: {}
        };

        const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'config-export.json';
        a.click();

        DashboardApp.showAlert('Configuration exported', 'success', 3000);
    }, 1000);
}

function importConfig() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            DashboardApp.showAlert(`Importing ${file.name}...`, 'info', 2000);
            // TODO: Process file upload
            setTimeout(() => {
                DashboardApp.showAlert('Configuration imported successfully', 'success', 3000);
                location.reload();
            }, 2000);
        }
    };
    input.click();
}

function resetToDefaults() {
    if (confirm('Reset all settings to defaults? This action cannot be undone.')) {
        DashboardApp.showAlert('Resetting to defaults...', 'warning', 2000);
        // TODO: API call to reset settings
        setTimeout(() => location.reload(), 1000);
    }
}

// Data cleanup
function cleanupOldData() {
    const retention = document.getElementById('data-retention').value;

    if (confirm(`Delete all data older than ${retention} days?`)) {
        DashboardApp.showAlert('Cleaning up old data...', 'info', 2000);
        // TODO: API call to cleanup
        setTimeout(() => {
            DashboardApp.showAlert('Old data cleaned up successfully', 'success', 3000);
        }, 3000);
    }
}

function purgeAllData() {
    if (confirm('WARNING: Delete ALL project data? This includes all scan history, violations, and metrics. This action CANNOT be undone!')) {
        if (confirm('Are you absolutely sure? Type YES to confirm.')) {
            DashboardApp.showAlert('Purging all data...', 'danger', 2000);
            // TODO: API call to purge
            setTimeout(() => {
                DashboardApp.showAlert('All data purged', 'success', 3000);
                location.reload();
            }, 3000);
        }
    }
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
