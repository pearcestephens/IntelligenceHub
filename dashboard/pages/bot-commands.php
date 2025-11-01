<?php
/**
 * Bot Commands Dashboard
 * Execute and manage bot commands
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');
?>

<div class="page-header">
    <h1 class="page-title">Bot Commands</h1>
    <p class="page-subtitle">Execute commands and manage bot operations</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-terminal"></i>
            </div>
            <div class="stats-card-value">142</div>
            <div class="stats-card-label">Commands Executed</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-card-value">98.5%</div>
            <div class="stats-card-label">Success Rate</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-robot"></i>
            </div>
            <div class="stats-card-value">3</div>
            <div class="stats-card-label">Active Bots</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-card-value">2.1s</div>
            <div class="stats-card-label">Avg Response Time</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Available Commands</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $commands = [
                        ['name' => 'scan', 'desc' => 'Run full system scan', 'icon' => 'fa-search', 'color' => 'primary'],
                        ['name' => 'index', 'desc' => 'Rebuild file index', 'icon' => 'fa-database', 'color' => 'success'],
                        ['name' => 'analyze', 'desc' => 'Analyze codebase', 'icon' => 'fa-chart-bar', 'color' => 'info'],
                        ['name' => 'cleanup', 'desc' => 'Clean temporary files', 'icon' => 'fa-broom', 'color' => 'warning'],
                        ['name' => 'backup', 'desc' => 'Create system backup', 'icon' => 'fa-save', 'color' => 'secondary'],
                        ['name' => 'status', 'desc' => 'Check system status', 'icon' => 'fa-heartbeat', 'color' => 'danger']
                    ];
                    
                    foreach ($commands as $cmd): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas <?= $cmd['icon'] ?> text-<?= $cmd['color'] ?> me-2"></i>
                                            <?= htmlspecialchars($cmd['name']) ?>
                                        </h6>
                                        <p class="text-muted small mb-0"><?= htmlspecialchars($cmd['desc']) ?></p>
                                    </div>
                                    <button class="btn btn-sm btn-<?= $cmd['color'] ?>" onclick="executeCommand('<?= $cmd['name'] ?>')">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-terminal me-2"></i>Command Output</h5>
            </div>
            <div class="card-body">
                <div id="commandOutput" class="bg-dark text-light p-3 rounded" style="min-height: 200px; max-height: 400px; overflow-y: auto; font-family: monospace;">
                    <div class="text-muted">Click a command to see output...</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Command History</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>scan</strong>
                            <small class="text-muted">2m ago</small>
                        </div>
                        <small class="text-success"><i class="fas fa-check"></i> Success</small>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>index</strong>
                            <small class="text-muted">15m ago</small>
                        </div>
                        <small class="text-success"><i class="fas fa-check"></i> Success</small>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>analyze</strong>
                            <small class="text-muted">1h ago</small>
                        </div>
                        <small class="text-success"><i class="fas fa-check"></i> Success</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Bot Status</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-circle text-success"></i> Scanner Bot</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-circle text-success"></i> Index Bot</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fas fa-circle text-success"></i> Analysis Bot</span>
                        <span class="badge bg-success">Active</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function executeCommand(command) {
    const output = document.getElementById('commandOutput');
    const timestamp = new Date().toLocaleTimeString();
    
    output.innerHTML = `<div class="text-info">[${ timestamp }] Executing command: ${ command }</div>`;
    
    setTimeout(() => {
        output.innerHTML += `<div class="text-light mt-2">Command '${ command }' executed successfully</div>`;
        output.innerHTML += `<div class="text-success mt-2">[${ new Date().toLocaleTimeString() }] Completed</div>`;
    }, 1000);
}
</script>
