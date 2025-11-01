<?php
/**
 * Scan Configuration Page - PHASE 3 STEP 3
 *
 * Configure selective scanning patterns
 * Include/exclude rules per project
 *
 * @package hdgwrzntwa/dashboard/admin
 * @category Dashboard Page - PHASE 3
 * @created October 31, 2025
 */

declare(strict_types=1);

// Set current page for sidebar highlighting
$_GET['page'] = 'scan-config';
$currentPage = 'scan-config';

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Load project selector component
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/includes/project-selector.php';

// Get current project ID from session (set by project-selector.php)
$projectId = (int)($_SESSION['current_project_id'] ?? $_GET['project_id'] ?? 1);

// Validate project exists and is active
$valQuery = "SELECT id FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
if (!$valStmt->fetch()) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
}

// ============================================================================
// HANDLE CRUD OPERATIONS
// ============================================================================

$message = '';
$messageType = '';

// Handle config add/update
if ($_POST && isset($_POST['save_config'])) {
    $configName = trim($_POST['config_name'] ?? '');
    $scanType = trim($_POST['scan_type'] ?? 'full');
    $includePatterns = trim($_POST['include_patterns'] ?? '');
    $excludePatterns = trim($_POST['exclude_patterns'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $enabled = isset($_POST['enabled']) ? 1 : 0;
    $configId = isset($_POST['config_id']) && $_POST['config_id'] ? (int)$_POST['config_id'] : 0;

    if (!$configName) {
        $message = '❌ Configuration name is required';
        $messageType = 'danger';
    } else {
        try {
            if ($configId) {
                // Update existing config
                $updateStmt = $pdo->prepare("
                    UPDATE project_scan_config
                    SET name = ?, scan_type = ?, include_patterns = ?, exclude_patterns = ?, schedule = ?, enabled = ?, updated_at = NOW()
                    WHERE id = ? AND project_id = ? LIMIT 1
                ");
                $updateStmt->execute([$configName, $scanType, $includePatterns, $excludePatterns, $schedule, $enabled, $configId, $projectId]);
                $message = '✅ Scan configuration updated successfully';
            } else {
                // Create new config
                $insertStmt = $pdo->prepare("
                    INSERT INTO project_scan_config (project_id, name, scan_type, include_patterns, exclude_patterns, schedule, enabled, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $insertStmt->execute([$projectId, $configName, $scanType, $includePatterns, $excludePatterns, $schedule, $enabled]);
                $message = '✅ Scan configuration created successfully';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = '❌ Error saving configuration: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Handle config delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $configId = (int)$_GET['id'];

    try {
        $deleteStmt = $pdo->prepare("DELETE FROM project_scan_config WHERE id = ? AND project_id = ? LIMIT 1");
        $deleteStmt->execute([$configId, $projectId]);
        $message = '✅ Scan configuration deleted successfully';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = '❌ Error deleting configuration: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// ============================================================================
// FETCH DATA
// ============================================================================

// Get scan configurations for current project
$configsQuery = "
    SELECT *
    FROM project_scan_config
    WHERE project_id = ?
    ORDER BY created_at DESC
";

$configsStmt = $pdo->prepare($configsQuery);
$configsStmt->execute([$projectId]);
$configs = $configsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get edit config if specified
$editConfig = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $configId = (int)$_GET['id'];
    $editStmt = $pdo->prepare("SELECT * FROM project_scan_config WHERE id = ? AND project_id = ? LIMIT 1");
    $editStmt->execute([$configId, $projectId]);
    $editConfig = $editStmt->fetch(PDO::FETCH_ASSOC);
}

// Get scan statistics
$statsQuery = "
    SELECT
        COUNT(*) as total_configs,
        SUM(CASE WHEN enabled = 1 THEN 1 ELSE 0 END) as enabled_configs,
        SUM(CASE WHEN schedule != '' THEN 1 ELSE 0 END) as scheduled_configs
    FROM project_scan_config
    WHERE project_id = ?
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute([$projectId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="dashboard-container">
    <!-- Project Selector Component (PHASE 3) -->
    <?php echo renderProjectSelector($pdo, $_SESSION['current_unit_id'], $projectId); ?>

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Scan Configuration</h1>
            <p class="text-muted">Configure selective scanning patterns</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#configModal" onclick="resetConfigForm()">
            <i class="fas fa-plus"></i> New Configuration
        </button>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['total_configs']; ?></h3>
                    <p class="text-muted mb-0">Total Configurations</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success"><?php echo $stats['enabled_configs']; ?></h3>
                    <p class="text-muted mb-0">Enabled</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info"><?php echo $stats['scheduled_configs']; ?></h3>
                    <p class="text-muted mb-0">Scheduled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Configurations Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Scan Configurations</h5>
        </div>
        <div class="card-body">
            <?php if (empty($configs)): ?>
                <p class="text-muted text-center py-4">No scan configurations yet. <a href="#" data-bs-toggle="modal" data-bs-target="#configModal">Create one</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Schedule</th>
                                <th>Last Run</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($configs as $config): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($config['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($config['scan_type'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($config['enabled']): ?>
                                        <span class="badge bg-success">Enabled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Disabled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($config['schedule']): ?>
                                        <code class="text-muted"><?php echo htmlspecialchars($config['schedule']); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php if ($config['last_run']): ?>
                                        <?php echo date('M d, Y H:i', strtotime($config['last_run'])); ?>
                                    <?php else: ?>
                                        Never
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#configModal" onclick="editConfig(<?php echo htmlspecialchars(json_encode($config)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?page=scan-config&action=delete&id=<?php echo $config['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this configuration?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalTitle">New Scan Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="config_id" id="config_id" value="0">

                    <div class="mb-3">
                        <label for="config_name" class="form-label">Configuration Name *</label>
                        <input type="text" class="form-control" id="config_name" name="config_name" placeholder="e.g., Core Libraries Scan" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="scan_type" class="form-label">Scan Type</label>
                                <select class="form-select" id="scan_type" name="scan_type">
                                    <option value="full">Full Scan</option>
                                    <option value="incremental">Incremental</option>
                                    <option value="quick">Quick Scan</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule" class="form-label">Schedule (Cron)</label>
                                <input type="text" class="form-control" id="schedule" name="schedule" placeholder="0 */4 * * * (optional)">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="include_patterns" class="form-label">Include Patterns (one per line)</label>
                        <textarea class="form-control" id="include_patterns" name="include_patterns" rows="3" placeholder="*.php
src/**/*.js
config/"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="exclude_patterns" class="form-label">Exclude Patterns (one per line)</label>
                        <textarea class="form-control" id="exclude_patterns" name="exclude_patterns" rows="3" placeholder="vendor/
node_modules/
tests/"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="enabled" name="enabled" value="1" checked>
                        <label class="form-check-label" for="enabled">
                            Enable this configuration
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_config" class="btn btn-primary">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetConfigForm() {
    document.getElementById('config_id').value = '0';
    document.getElementById('config_name').value = '';
    document.getElementById('scan_type').value = 'full';
    document.getElementById('schedule').value = '';
    document.getElementById('include_patterns').value = '';
    document.getElementById('exclude_patterns').value = '';
    document.getElementById('enabled').checked = true;
    document.getElementById('configModalTitle').textContent = 'New Scan Configuration';
}

function editConfig(config) {
    document.getElementById('config_id').value = config.id;
    document.getElementById('config_name').value = config.name;
    document.getElementById('scan_type').value = config.scan_type;
    document.getElementById('schedule').value = config.schedule || '';
    document.getElementById('include_patterns').value = config.include_patterns || '';
    document.getElementById('exclude_patterns').value = config.exclude_patterns || '';
    document.getElementById('enabled').checked = config.enabled == 1;
    document.getElementById('configModalTitle').textContent = 'Edit Scan Configuration';
}
</script>
