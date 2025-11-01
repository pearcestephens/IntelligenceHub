<?php
/**
 * Smart Cron Dashboard - Missing Script Checker
 * 
 * Displays all tasks with real-time script validation
 * Shows prominent alerts for missing scripts at the top
 * 
 * 🔒 CRITICAL FIX #6: CSRF PROTECTION - Add session-based CSRF tokens
 */

// Start session for CSRF protection
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include required classes
require_once __DIR__ . '/core/Config.php';

$config = new \SmartCron\Core\Config();
$tasks = $config->getTasks();
$projectRoot = '/home/master/applications/jcepnzzkmj/public_html';

// Check script existence for all tasks
$missingScripts = [];
$validScripts = [];
$totalTasks = count($tasks);

foreach ($tasks as $task) {
    $scriptPath = $projectRoot . '/' . $task['script'];
    $isEnabled = isset($task['enabled']) && $task['enabled'];
    
    if (!file_exists($scriptPath)) {
        $missingScripts[] = [
            'name' => $task['name'],
            'script' => $task['script'],
            'enabled' => $isEnabled,
            'frequency' => $task['frequency'] ?? 'unknown',
            'description' => $task['description'] ?? 'No description'
        ];
    } else {
        $validScripts[] = $task;
    }
}

$enabledCount = count(array_filter($tasks, function($t) {
    return isset($t['enabled']) && $t['enabled'];
}));

$criticalMissing = array_filter($missingScripts, function($t) {
    return $t['enabled'];
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Cron Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-good { color: #28a745; }
        .status-warning { color: #ffc107; }
        .status-error { color: #dc3545; }
        .script-path { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
        .missing-alert { border-left: 4px solid #dc3545; }
        .task-card { transition: all 0.2s; }
        .task-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .frequency-badge {
            font-size: 0.75em;
            font-weight: normal;
        }
        .memory-usage {
            background: linear-gradient(90deg, #e9ecef 0%, #28a745 100%);
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        .memory-bar {
            height: 100%;
            background: #28a745;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cogs text-primary"></i>
                    Smart Cron Dashboard
                </h1>
                <p class="text-muted mb-0">Task management and script validation</p>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Missing Scripts Alert -->
        <?php if (!empty($missingScripts)): ?>
        <div class="alert alert-danger missing-alert" role="alert">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h4 class="alert-heading mb-1">
                        <strong><?= count($missingScripts) ?> Missing Script<?= count($missingScripts) > 1 ? 's' : '' ?> Detected</strong>
                    </h4>
                    <p class="mb-0">
                        <?= count($criticalMissing) ?> critical (enabled tasks), 
                        <?= count($missingScripts) - count($criticalMissing) ?> non-critical (disabled tasks)
                    </p>
                </div>
            </div>
            
            <div class="row">
                <?php foreach ($missingScripts as $missing): ?>
                <div class="col-md-6 mb-2">
                    <div class="card border-danger">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0 text-danger">
                                    <i class="fas fa-file-slash"></i>
                                    <?= htmlspecialchars($missing['name']) ?>
                                </h6>
                                <span class="badge bg-<?= $missing['enabled'] ? 'danger' : 'secondary' ?> frequency-badge">
                                    <?= $missing['enabled'] ? 'ENABLED' : 'disabled' ?>
                                </span>
                            </div>
                            <p class="card-text small mb-2"><?= htmlspecialchars($missing['description']) ?></p>
                            <code class="script-path text-danger small">
                                <i class="fas fa-times-circle"></i>
                                <?= htmlspecialchars($missing['script']) ?>
                            </code>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> <?= htmlspecialchars($missing['frequency']) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-3 pt-3 border-top border-danger">
                <p class="mb-0">
                    <i class="fas fa-info-circle"></i>
                    <strong>Action Required:</strong> Create or verify the script paths above. 
                    Critical tasks (enabled) will fail to execute until scripts are available.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Overview -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Tasks</h5>
                        <h2 class="text-primary"><?= $totalTasks ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Enabled</h5>
                        <h2 class="text-success"><?= $enabledCount ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Valid Scripts</h5>
                        <h2 class="text-info"><?= count($validScripts) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Missing Scripts</h5>
                        <h2 class="<?= count($missingScripts) > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= count($missingScripts) ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i>
                    All Tasks (<?= $totalTasks ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="25%">Task Name</th>
                                <th width="15%">Status</th>
                                <th width="12%">Frequency</th>
                                <th width="35%">Script Path</th>
                                <th width="8%">Memory</th>
                                <th width="5%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tasks as $task): 
                                $scriptPath = $projectRoot . '/' . $task['script'];
                                $exists = file_exists($scriptPath);
                                $isEnabled = isset($task['enabled']) && $task['enabled'];
                                $memory = $task['estimated_memory_mb'] ?? 50;
                                $memoryPercent = min(100, ($memory / 800) * 100); // Scale to 800MB max
                            ?>
                            <tr class="<?= !$exists && $isEnabled ? 'table-danger' : ($exists && $isEnabled ? 'table-success' : '') ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-<?= $isEnabled ? 'play-circle text-success' : 'pause-circle text-muted' ?> me-2"></i>
                                        <div>
                                            <strong><?= htmlspecialchars($task['name']) ?></strong>
                                            <?php if (!empty($task['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($task['description'], 0, 80)) ?><?= strlen($task['description']) > 80 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isEnabled && $exists): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    <?php elseif ($isEnabled && !$exists): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Error
                                        </span>
                                    <?php elseif (!$isEnabled && $exists): ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-pause"></i> Disabled
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-question-circle"></i> Unknown
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary frequency-badge">
                                        <?= htmlspecialchars($task['frequency'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="script-path <?= $exists ? 'text-success' : 'text-danger' ?>">
                                        <i class="fas fa-<?= $exists ? 'check' : 'times' ?>"></i>
                                        <?= htmlspecialchars($task['script']) ?>
                                    </code>
                                </td>
                                <td>
                                    <div class="memory-usage mb-1">
                                        <div class="memory-bar" style="width: <?= $memoryPercent ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $memory ?>MB</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="showTaskDetails('<?= htmlspecialchars($task['name']) ?>')"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- System Health Footer -->
        <div class="row mt-4">
            <div class="col">
                <div class="card">
                    <div class="card-body text-center">
                        <?php 
                        $healthScore = round((count($validScripts) / $totalTasks) * 100, 1);
                        $healthColor = $healthScore >= 90 ? 'success' : ($healthScore >= 75 ? 'warning' : 'danger');
                        ?>
                        <h5 class="text-<?= $healthColor ?>">
                            System Health: <?= $healthScore ?>%
                        </h5>
                        <p class="text-muted mb-0">
                            <?= count($validScripts) ?> of <?= $totalTasks ?> scripts verified | 
                            <?= $enabledCount ?> tasks enabled |
                            Last checked: <?= date('Y-m-d H:i:s') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="taskModalBody">
                    <!-- Task details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 🔒 CRITICAL FIX #6: CSRF TOKEN - Embed in JavaScript for AJAX requests
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';
        
        // Auto-refresh every 5 minutes
        setTimeout(() => location.reload(), 300000);
        
        // Task details function
        function showTaskDetails(taskName) {
            // This would load detailed task information
            document.getElementById('taskModalBody').innerHTML = `
                <div class="text-center">
                    <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                    <h5>${taskName}</h5>
                    <p>Detailed task information and logs would be displayed here.</p>
                    <p class="text-muted">Feature coming soon...</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('taskModal')).show();
        }
        
        // Show success message if no missing scripts
        <?php if (empty($missingScripts)): ?>
        setTimeout(() => {
            if (!document.querySelector('.alert-danger')) {
                const container = document.querySelector('.container-fluid');
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <strong>All Scripts Verified!</strong> All <?= $totalTasks ?> tasks have valid script paths.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                container.insertBefore(successAlert, container.children[1]);
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>