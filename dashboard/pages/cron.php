<?php
/**
 * Cron Management Page
 * Universal Cron Controller integrated into Intelligence Dashboard
 * 
 * Features:
 * - View all crons from all servers
 * - Real-time status monitoring
 * - Enable/disable tasks
 * - View logs and execution history
 * - Schedule coordination
 * - Add/edit/remove tasks
 * - System crontab viewer
 * 
 * @package CIS Intelligence Dashboard
 * @version 1.0.0
 */

if (!defined('DASHBOARD_ACCESS')) die('Direct access not permitted');

// Load server configurations
$servers_config_file = dirname(dirname(__DIR__)) . '/_kb/config/cron_servers.json';
$servers_config = [];
if (file_exists($servers_config_file)) {
    $servers_config = json_decode(file_get_contents($servers_config_file), true) ?? [];
}

// Get selected application from URL
$selected_app = $_GET['app'] ?? 'all';

// Configuration
$controller_path = dirname(dirname(__DIR__)) . '/_kb/scripts/universal_cron_controller.php';
$cache_file = dirname(dirname(__DIR__)) . '/_kb/cache/cron_status_' . $selected_app . '.json';
$cache_ttl = 30; // seconds

// Get cached status or fetch new
$status_data = null;
$servers_data = [];

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    $status_data = json_decode(file_get_contents($cache_file), true);
} else {
    // Fetch fresh data
    if (file_exists($controller_path)) {
        $serverArg = ($selected_app !== 'all') ? "--server={$selected_app}" : '';
        $output = shell_exec("php {$controller_path} status {$serverArg} 2>&1");
        
        // Parse output into structured data
        $servers_data = parseCronStatus($output);
        
        // Cache it
        $cache_dir = dirname($cache_file);
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        file_put_contents($cache_file, json_encode($servers_data));
        $status_data = $servers_data;
    }
}

// Calculate totals
$total_servers = is_array($status_data) ? count($status_data) : 0;
$total_tasks = 0;
$total_enabled = 0;
$total_running = 0;
$total_failed = 0;

if (is_array($status_data)) {
    foreach ($status_data as $server) {
        $total_tasks += $server['tasks'] ?? 0;
        $total_enabled += $server['enabled'] ?? 0;
        $total_running += $server['running'] ?? 0;
        $total_failed += $server['failed'] ?? 0;
    }
}

/**
 * Parse cron status output into structured array
 */
function parseCronStatus(string $output): array
{
    $servers = [];
    $lines = explode("\n", $output);
    $current_server = null;
    
    foreach ($lines as $line) {
        if (strpos($line, '📡') !== false) {
            // Server header line
            if (preg_match('/📡 (.+?) \((.+?)\)/', $line, $matches)) {
                if ($current_server !== null) {
                    $servers[] = $current_server;
                }
                $current_server = [
                    'name' => trim($matches[1]),
                    'id' => trim($matches[2]),
                    'tasks' => 0,
                    'enabled' => 0,
                    'running' => 0,
                    'failed' => 0,
                    'last_run' => 'Never'
                ];
            }
        } elseif ($current_server !== null) {
            if (preg_match('/Tasks: (\d+) \((\d+) enabled\)/', $line, $matches)) {
                $current_server['tasks'] = (int)$matches[1];
                $current_server['enabled'] = (int)$matches[2];
            } elseif (preg_match('/Running: (\d+)/', $line, $matches)) {
                $current_server['running'] = (int)$matches[1];
            } elseif (preg_match('/Failed.*?: (\d+)/', $line, $matches)) {
                $current_server['failed'] = (int)$matches[1];
            } elseif (preg_match('/Last Run: (.+)/', $line, $matches)) {
                $current_server['last_run'] = trim($matches[1]);
            }
        }
    }
    
    if ($current_server !== null) {
        $servers[] = $current_server;
    }
    
    return $servers;
}
?>

<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-clock text-primary"></i> Cron Management
    </h1>
    <p class="page-subtitle">Universal Cron Controller - Master control for all scheduled tasks across all servers</p>
</div>

<!-- Application / Domain Selector -->
<div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0" style="color: white;">
                    <i class="fas fa-layer-group"></i> Select Application / Domain
                </h5>
            </div>
            <div class="col-md-9">
                <div class="row g-2">
                    <div class="col-md-2">
                        <a href="?page=cron&app=all" class="btn <?php echo $selected_app === 'all' ? 'btn-light' : 'btn-outline-light'; ?> w-100">
                            🌐 All Applications
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="?page=cron&app=intelligence_hub" class="btn <?php echo $selected_app === 'intelligence_hub' ? 'btn-light' : 'btn-outline-light'; ?> w-100">
                            🧠 Intelligence Hub
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=cron&app=jcepnzzkmj" class="btn <?php echo $selected_app === 'jcepnzzkmj' ? 'btn-light' : 'btn-outline-light'; ?> w-100">
                            🏢 CIS Staff Portal
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="?page=cron&app=dvaxgvsxmz" class="btn <?php echo $selected_app === 'dvaxgvsxmz' ? 'btn-light' : 'btn-outline-light'; ?> w-100">
                            🏪 Vape Shed
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?page=cron&app=fhrehrpjmu" class="btn <?php echo $selected_app === 'fhrehrpjmu' ? 'btn-light' : 'btn-outline-light'; ?> w-100">
                            📦 Ecigdis Wholesale
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($selected_app !== 'all' && isset($servers_config[$selected_app])): ?>
        <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
            <div class="row">
                <div class="col-md-4">
                    <small style="opacity: 0.8;">Domain:</small> 
                    <strong><?php echo htmlspecialchars($servers_config[$selected_app]['domain'] ?? 'N/A'); ?></strong>
                </div>
                <div class="col-md-8">
                    <small style="opacity: 0.8;">Description:</small> 
                    <strong><?php echo htmlspecialchars($servers_config[$selected_app]['description'] ?? 'N/A'); ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Stats -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stats-card-value"><?php echo $total_servers; ?></div>
            <div class="stats-card-label">Servers Managed</div>
            <div class="stats-card-change positive">
                <i class="fas fa-check"></i> All connected
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stats-card-value"><?php echo $total_tasks; ?></div>
            <div class="stats-card-label">Total Tasks</div>
            <div class="stats-card-change">
                <i class="fas fa-info-circle"></i> <?php echo $total_enabled; ?> enabled
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stats-card-value"><?php echo $total_running; ?></div>
            <div class="stats-card-label">Currently Running</div>
            <div class="stats-card-change positive">
                <i class="fas fa-spinner fa-spin"></i> Active now
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, <?php echo $total_failed > 0 ? '#ff6b6b 0%, #ee5a6f 100%' : '#43e97b 0%, #38f9d7 100%'; ?>);">
            <div class="stats-card-icon">
                <i class="fas fa-<?php echo $total_failed > 0 ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
            </div>
            <div class="stats-card-value"><?php echo $total_failed; ?></div>
            <div class="stats-card-label">Failed (24h)</div>
            <div class="stats-card-change <?php echo $total_failed > 0 ? 'negative' : ''; ?>">
                <i class="fas fa-<?php echo $total_failed > 0 ? 'times' : 'check'; ?>"></i> <?php echo $total_failed > 0 ? 'Needs attention' : 'All healthy'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-bolt text-warning"></i> Quick Actions
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <button class="btn btn-primary w-100" onclick="refreshStatus()">
                    <i class="fas fa-sync-alt"></i> Refresh Status
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-success w-100" onclick="syncAllServers()">
                    <i class="fas fa-server"></i> Sync All Servers
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info w-100" onclick="coordinateSchedules()">
                    <i class="fas fa-project-diagram"></i> Coordinate Schedules
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-secondary w-100" onclick="viewAllLogs()">
                    <i class="fas fa-file-alt"></i> View All Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Application Settings Panel (shown when specific app selected) -->
<?php if ($selected_app !== 'all' && isset($servers_config[$selected_app]) && $servers_config[$selected_app]['type'] !== 'alias'): ?>
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-cog text-primary"></i> Application Settings: <?php echo htmlspecialchars($servers_config[$selected_app]['name']); ?>
            </h5>
            <button class="btn btn-sm btn-primary" onclick="saveApplicationSettings('<?php echo $selected_app; ?>')">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label fw-bold">Auto-Sync Enabled</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="setting-autosync" 
                               <?php echo ($servers_config[$selected_app]['auto_sync'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting-autosync">
                            Automatically sync this application's crons every 6 hours
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Schedule Coordination</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="setting-coordination" 
                               <?php echo ($servers_config[$selected_app]['coordination'] ?? true) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="setting-coordination">
                            Automatically adjust task timing to prevent conflicts with other servers
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <?php if ($servers_config[$selected_app]['type'] === 'remote'): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">API URL</label>
                    <input type="text" class="form-control" id="setting-api-url" 
                           value="<?php echo htmlspecialchars($servers_config[$selected_app]['api_url'] ?? ''); ?>" 
                           placeholder="https://example.com/api/cron/manage.php">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">API Key</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="setting-api-key" 
                               value="<?php echo htmlspecialchars($servers_config[$selected_app]['api_key'] ?? ''); ?>" 
                               placeholder="Enter API key...">
                        <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKeyVisibility()">
                            <i class="fas fa-eye" id="api-key-icon"></i>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Base Path</label>
                    <input type="text" class="form-control" id="setting-path" 
                           value="<?php echo htmlspecialchars($servers_config[$selected_app]['path'] ?? ''); ?>" 
                           placeholder="/path/to/application">
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Priority</label>
                    <select class="form-select" id="setting-priority">
                        <option value="1" <?php echo ($servers_config[$selected_app]['priority'] ?? 5) == 1 ? 'selected' : ''; ?>>1 - Highest</option>
                        <option value="2" <?php echo ($servers_config[$selected_app]['priority'] ?? 5) == 2 ? 'selected' : ''; ?>>2 - High</option>
                        <option value="3" <?php echo ($servers_config[$selected_app]['priority'] ?? 5) == 3 ? 'selected' : ''; ?>>3 - Normal</option>
                        <option value="4" <?php echo ($servers_config[$selected_app]['priority'] ?? 5) == 4 ? 'selected' : ''; ?>>4 - Low</option>
                        <option value="5" <?php echo ($servers_config[$selected_app]['priority'] ?? 5) == 5 ? 'selected' : ''; ?>>5 - Lowest</option>
                    </select>
                    <small class="text-muted">Higher priority applications get preference during schedule coordination</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Server Status Cards -->
<div class="row g-4 mb-4">
    <?php if (is_array($status_data) && !empty($status_data)): ?>
        <?php foreach ($status_data as $server): ?>
            <div class="col-xl-6">
                <div class="card server-card <?php echo ($server['failed'] ?? 0) > 0 ? 'border-danger' : 'border-success'; ?>">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-server text-primary"></i> 
                                <?php echo htmlspecialchars($server['name'] ?? 'Unknown'); ?>
                            </h5>
                            <span class="badge <?php echo ($server['failed'] ?? 0) > 0 ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $server['enabled'] ?? 0; ?> / <?php echo $server['tasks'] ?? 0; ?> Active
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-3 text-center">
                                <div class="fs-3 fw-bold text-primary"><?php echo $server['tasks'] ?? 0; ?></div>
                                <small class="text-muted">Total Tasks</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="fs-3 fw-bold text-success"><?php echo $server['enabled'] ?? 0; ?></div>
                                <small class="text-muted">Enabled</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="fs-3 fw-bold text-info"><?php echo $server['running'] ?? 0; ?></div>
                                <small class="text-muted">Running</small>
                            </div>
                            <div class="col-3 text-center">
                                <div class="fs-3 fw-bold <?php echo ($server['failed'] ?? 0) > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo $server['failed'] ?? 0; ?>
                                </div>
                                <small class="text-muted">Failed (24h)</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Last Run:</small>
                            <strong><?php echo htmlspecialchars($server['last_run'] ?? 'Never'); ?></strong>
                        </div>
                        
                        <div class="btn-group w-100" role="group">
                            <button class="btn btn-outline-primary" onclick="viewServerTasks('<?php echo htmlspecialchars($server['id'] ?? ''); ?>')">
                                <i class="fas fa-list"></i> View Tasks
                            </button>
                            <button class="btn btn-outline-secondary" onclick="viewServerLogs('<?php echo htmlspecialchars($server['id'] ?? ''); ?>')">
                                <i class="fas fa-file-alt"></i> View Logs
                            </button>
                            <button class="btn btn-outline-info" onclick="viewCrontab('<?php echo htmlspecialchars($server['id'] ?? ''); ?>')">
                                <i class="fas fa-terminal"></i> Crontab
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No server data available. 
                <button class="btn btn-sm btn-warning ms-2" onclick="refreshStatus()">Refresh Now</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Console Output -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-terminal"></i> Console Output
        </h5>
    </div>
    <div class="card-body bg-dark p-0">
        <div id="consoleOutput" class="console-output" style="height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.4; color: #00ff00; background: #000; padding: 15px;">
            <div class="console-line">🚀 Universal Cron Controller - Ready</div>
            <div class="console-line">📡 Waiting for commands...</div>
            <div class="console-line">💡 Tip: Click any button above to execute commands</div>
        </div>
    </div>
</div>

<!-- Task List Modal -->
<div class="modal fade" id="taskListModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-list"></i> <span id="modalServerName">Server</span> - Tasks
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="taskListContent" class="loading-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading tasks...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 30 seconds
let autoRefreshInterval = setInterval(refreshStatus, 30000);

// Toggle API key visibility
function toggleApiKeyVisibility() {
    const input = document.getElementById('setting-api-key');
    const icon = document.getElementById('api-key-icon');
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

// Save application settings
function saveApplicationSettings(appId) {
    logToConsole('Saving settings for: ' + appId, 'warning');
    
    const settings = {
        app_id: appId,
        auto_sync: document.getElementById('setting-autosync')?.checked || false,
        coordination: document.getElementById('setting-coordination')?.checked || false,
        priority: document.getElementById('setting-priority')?.value || 3
    };
    
    // Add type-specific settings
    const apiKeyField = document.getElementById('setting-api-key');
    const apiUrlField = document.getElementById('setting-api-url');
    const pathField = document.getElementById('setting-path');
    
    if (apiKeyField && apiKeyField.value) {
        settings.api_key = apiKeyField.value;
    }
    if (apiUrlField && apiUrlField.value) {
        settings.api_url = apiUrlField.value;
    }
    if (pathField && pathField.value) {
        settings.path = pathField.value;
    }
    
    // Send to server
    const formData = new FormData();
    formData.append('action', 'save_settings');
    formData.append('settings', JSON.stringify(settings));
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            logToConsole('Settings saved successfully!', 'success');
            // Show success toast
            showToast('Settings saved', 'Settings have been updated successfully', 'success');
        } else {
            logToConsole('Error saving settings: ' + (data.error || 'Unknown error'), 'error');
            showToast('Error', 'Failed to save settings: ' + (data.error || 'Unknown error'), 'danger');
        }
    })
    .catch(err => {
        logToConsole('Error: ' + err.message, 'error');
        showToast('Error', 'Network error: ' + err.message, 'danger');
    });
}

// Show toast notification
function showToast(title, message, type = 'info') {
    // Create toast element
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Create toast container if it doesn't exist
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    // Add toast
    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = container.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove after hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Console logger
function logToConsole(message, type = 'info') {
    const console = document.getElementById('consoleOutput');
    const timestamp = new Date().toLocaleTimeString();
    const colors = {
        'info': '#00ff00',
        'success': '#00ff00',
        'warning': '#ffff00',
        'error': '#ff0000'
    };
    const icons = {
        'info': '📡',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌'
    };
    
    const line = document.createElement('div');
    line.className = 'console-line';
    line.style.color = colors[type] || '#00ff00';
    line.textContent = `[${timestamp}] ${icons[type]} ${message}`;
    console.appendChild(line);
    console.scrollTop = console.scrollHeight;
}

// Refresh status
function refreshStatus() {
    logToConsole('Refreshing server status...', 'info');
    
    fetch('?page=cron&action=refresh_status')
        .then(r => r.text())
        .then(data => {
            logToConsole('Status refresh complete - reloading page...', 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            logToConsole('Error: ' + err.message, 'error');
        });
}

// View server tasks
function viewServerTasks(serverId) {
    const modal = new bootstrap.Modal(document.getElementById('taskListModal'));
    const serverName = document.querySelector(`button[onclick*="${serverId}"]`)?.closest('.card')?.querySelector('.card-title')?.textContent?.trim() || serverId;
    
    document.getElementById('modalServerName').textContent = serverName;
    document.getElementById('taskListContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading tasks...</p></div>';
    
    modal.show();
    
    logToConsole(`Loading tasks for ${serverId}...`, 'info');
    
    fetch(`?page=cron&action=list_tasks&server=${serverId}`)
        .then(r => r.text())
        .then(data => {
            document.getElementById('taskListContent').innerHTML = '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 5px; max-height: 500px; overflow-y: auto;">' + data + '</pre>';
            logToConsole(`Tasks loaded for ${serverId}`, 'success');
        })
        .catch(err => {
            document.getElementById('taskListContent').innerHTML = '<div class="alert alert-danger">Error loading tasks: ' + err.message + '</div>';
            logToConsole('Error loading tasks: ' + err.message, 'error');
        });
}

// View server logs
function viewServerLogs(serverId) {
    logToConsole(`Loading logs for ${serverId}...`, 'info');
    
    fetch(`?page=cron&action=view_logs&server=${serverId}&lines=100`)
        .then(r => r.text())
        .then(data => {
            const console = document.getElementById('consoleOutput');
            console.innerHTML = '<div class="console-line">' + data.replace(/\n/g, '</div><div class="console-line">') + '</div>';
            logToConsole(`Logs loaded for ${serverId}`, 'success');
        })
        .catch(err => {
            logToConsole('Error loading logs: ' + err.message, 'error');
        });
}

// View crontab
function viewCrontab(serverId) {
    logToConsole(`Loading crontab for ${serverId}...`, 'info');
    
    fetch(`?page=cron&action=view_crontab&server=${serverId}`)
        .then(r => r.text())
        .then(data => {
            const console = document.getElementById('consoleOutput');
            console.innerHTML = '<div class="console-line">' + data.replace(/\n/g, '</div><div class="console-line">') + '</div>';
            logToConsole(`Crontab loaded for ${serverId}`, 'success');
        })
        .catch(err => {
            logToConsole('Error loading crontab: ' + err.message, 'error');
        });
}

// Sync all servers
function syncAllServers() {
    logToConsole('Syncing all servers...', 'warning');
    
    fetch('?page=cron&action=sync_all')
        .then(r => r.text())
        .then(data => {
            const console = document.getElementById('consoleOutput');
            console.innerHTML = '<div class="console-line">' + data.replace(/\n/g, '</div><div class="console-line">') + '</div>';
            logToConsole('Sync completed successfully', 'success');
            setTimeout(refreshStatus, 2000);
        })
        .catch(err => {
            logToConsole('Sync error: ' + err.message, 'error');
        });
}

// Coordinate schedules
function coordinateSchedules() {
    logToConsole('Analyzing schedules for conflicts...', 'warning');
    
    fetch('?page=cron&action=coordinate')
        .then(r => r.text())
        .then(data => {
            const console = document.getElementById('consoleOutput');
            console.innerHTML = '<div class="console-line">' + data.replace(/\n/g, '</div><div class="console-line">') + '</div>';
            logToConsole('Schedule coordination complete', 'success');
        })
        .catch(err => {
            logToConsole('Coordination error: ' + err.message, 'error');
        });
}

// View all logs
function viewAllLogs() {
    logToConsole('Loading all server logs...', 'info');
    
    fetch('?page=cron&action=view_logs&server=all&lines=200')
        .then(r => r.text())
        .then(data => {
            const console = document.getElementById('consoleOutput');
            console.innerHTML = '<div class="console-line">' + data.replace(/\n/g, '</div><div class="console-line">') + '</div>';
            logToConsole('All logs loaded', 'success');
        })
        .catch(err => {
            logToConsole('Error loading logs: ' + err.message, 'error');
        });
}
</script>

<style>
.server-card {
    transition: all 0.3s ease;
}
.server-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.console-line {
    margin-bottom: 2px;
    padding: 2px 0;
}
.btn-group .btn {
    font-size: 13px;
    padding: 8px 12px;
}
</style>

<?php
// Handle AJAX actions
if (isset($_GET['action']) || isset($_POST['action'])) {
    ob_clean();
    
    $action = $_POST['action'] ?? $_GET['action'];
    $server = $_POST['server'] ?? $_GET['server'] ?? '';
    $controller_path = dirname(dirname(__DIR__)) . '/_kb/scripts/universal_cron_controller.php';
    
    // Handle POST actions (JSON response)
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        
        switch ($action) {
            case 'save_settings':
                $settings = json_decode($_POST['settings'] ?? '{}', true);
                $app_id = $settings['app_id'] ?? '';
                
                if ($app_id && isset($servers_config[$app_id])) {
                    // Update settings in config
                    $servers_config[$app_id]['auto_sync'] = $settings['auto_sync'] ?? true;
                    $servers_config[$app_id]['coordination'] = $settings['coordination'] ?? false;
                    $servers_config[$app_id]['priority'] = (int)($settings['priority'] ?? 3);
                    
                    if (isset($settings['api_key'])) {
                        $servers_config[$app_id]['api_key'] = $settings['api_key'];
                    }
                    if (isset($settings['api_url'])) {
                        $servers_config[$app_id]['api_url'] = $settings['api_url'];
                    }
                    if (isset($settings['path'])) {
                        $servers_config[$app_id]['path'] = $settings['path'];
                    }
                    
                    // Save to file
                    $config_file = dirname(dirname(__DIR__)) . '/_kb/config/cron_servers.json';
                    if (file_put_contents($config_file, json_encode($servers_config, JSON_PRETTY_PRINT))) {
                        echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Failed to write config file']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid application ID']);
                }
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Unknown action']);
        }
        exit;
    }
    
    // Handle GET actions (plain text response)
    header('Content-Type: text/plain');
    
    switch ($action) {
        case 'refresh_status':
            // Clear cache
            if (file_exists($cache_file)) {
                unlink($cache_file);
            }
            echo "Status cache cleared";
            break;
            
        case 'list_tasks':
            $serverArg = $server ? "--server={$server}" : '';
            echo shell_exec("php {$controller_path} list {$serverArg} 2>&1");
            break;
            
        case 'view_logs':
            $lines = $_GET['lines'] ?? '50';
            $serverArg = $server && $server !== 'all' ? "--server={$server}" : '';
            echo shell_exec("php {$controller_path} logs {$serverArg} --lines={$lines} 2>&1");
            break;
            
        case 'view_crontab':
            $serverArg = $server ? "--server={$server}" : '';
            echo shell_exec("php {$controller_path} crontab {$serverArg} 2>&1");
            break;
            
        case 'sync_all':
            echo shell_exec("php {$controller_path} sync 2>&1");
            break;
            
        case 'coordinate':
            echo shell_exec("php {$controller_path} coordinate 2>&1");
            break;
            
        default:
            echo "Unknown action";
    }
    exit;
}
?>
