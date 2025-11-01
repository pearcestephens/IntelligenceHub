<?php
/**
 * System Logs Viewer
 * View and analyze system logs (Apache, PHP, Application)
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

// Get log files
$logDir = dirname(dirname(dirname(__FILE__))) . '/logs';
$logFiles = [
    'apache_error' => $logDir . '/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log.1',
    'apache_access' => $logDir . '/apache_phpstack-129337-5615757.cloudwaysapps.com.access.log.1',
    'nginx_error' => $logDir . '/nginx_phpstack-129337-5615757.cloudwaysapps.com.error.log.1',
    'nginx_access' => $logDir . '/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log.1',
    'php_error' => $logDir . '/php-app.access.log.1',
    'php_slow' => $logDir . '/php-app.slow.log.1'
];

// Get selected log and number of lines
$selectedLog = $_GET['log'] ?? 'apache_error';
$lines = (int)($_GET['lines'] ?? 100);
$search = $_GET['search'] ?? '';

// Validate log selection
if (!isset($logFiles[$selectedLog])) {
    $selectedLog = 'apache_error';
}

// Read log file
$logContent = '';
$logSize = 0;
$logModified = '';
$logExists = false;

if (file_exists($logFiles[$selectedLog])) {
    $logExists = true;
    $logSize = filesize($logFiles[$selectedLog]);
    $logModified = date('Y-m-d H:i:s', filemtime($logFiles[$selectedLog]));
    
    // Read last N lines
    $file = new SplFileObject($logFiles[$selectedLog]);
    $file->seek(PHP_INT_MAX);
    $totalLines = $file->key() + 1;
    
    $startLine = max(0, $totalLines - $lines);
    $file->seek($startLine);
    
    $logLines = [];
    while (!$file->eof()) {
        $line = $file->current();
        if ($search === '' || stripos($line, $search) !== false) {
            $logLines[] = htmlspecialchars($line);
        }
        $file->next();
    }
    $logContent = implode('', $logLines);
}

// Log stats
$errorCount = substr_count($logContent, '[error]');
$warningCount = substr_count($logContent, '[warn]');
$noticeCount = substr_count($logContent, '[notice]');
?>

<div class="page-header">
    <h1 class="page-title">System Logs</h1>
    <p class="page-subtitle">Monitor and analyze system logs in real-time</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($errorCount); ?></div>
            <div class="stats-card-label">Errors Found</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #ffa500 0%, #ff6347 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($warningCount); ?></div>
            <div class="stats-card-label">Warnings</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($noticeCount); ?></div>
            <div class="stats-card-label">Notices</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stats-card-value"><?php echo formatBytes($logSize); ?></div>
            <div class="stats-card-label">Log File Size</div>
        </div>
    </div>
</div>

<!-- Log Viewer -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-alt text-primary me-2"></i>
            Log Viewer
        </h5>
        <?php if ($logExists): ?>
        <small class="text-muted">Last modified: <?php echo $logModified; ?></small>
        <?php endif; ?>
    </div>
    <div class="card-body">
        
        <!-- Controls -->
        <form method="GET" class="row g-3 mb-3">
            <input type="hidden" name="page" value="logs">
            
            <div class="col-md-3">
                <label class="form-label">Log File</label>
                <select name="log" class="form-select" onchange="this.form.submit()">
                    <option value="apache_error" <?php echo $selectedLog === 'apache_error' ? 'selected' : ''; ?>>Apache Error Log</option>
                    <option value="apache_access" <?php echo $selectedLog === 'apache_access' ? 'selected' : ''; ?>>Apache Access Log</option>
                    <option value="nginx_error" <?php echo $selectedLog === 'nginx_error' ? 'selected' : ''; ?>>Nginx Error Log</option>
                    <option value="nginx_access" <?php echo $selectedLog === 'nginx_access' ? 'selected' : ''; ?>>Nginx Access Log</option>
                    <option value="php_error" <?php echo $selectedLog === 'php_error' ? 'selected' : ''; ?>>PHP Error Log</option>
                    <option value="php_slow" <?php echo $selectedLog === 'php_slow' ? 'selected' : ''; ?>>PHP Slow Log</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Lines</label>
                <select name="lines" class="form-select" onchange="this.form.submit()">
                    <option value="50" <?php echo $lines === 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo $lines === 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?php echo $lines === 200 ? 'selected' : ''; ?>>200</option>
                    <option value="500" <?php echo $lines === 500 ? 'selected' : ''; ?>>500</option>
                    <option value="1000" <?php echo $lines === 1000 ? 'selected' : ''; ?>>1000</option>
                </select>
            </div>
            
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search logs..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>
        
        <!-- Quick Actions -->
        <div class="btn-group mb-3" role="group">
            <a href="?page=logs&log=<?php echo $selectedLog; ?>&lines=<?php echo $lines; ?>&search=error" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-exclamation-circle me-1"></i> Errors Only
            </a>
            <a href="?page=logs&log=<?php echo $selectedLog; ?>&lines=<?php echo $lines; ?>&search=warn" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> Warnings
            </a>
            <a href="?page=logs&log=<?php echo $selectedLog; ?>&lines=<?php echo $lines; ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync me-1"></i> Refresh
            </a>
            <a href="<?php echo $logFiles[$selectedLog]; ?>" download class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download me-1"></i> Download
            </a>
        </div>
        
        <!-- Log Content -->
        <?php if ($logExists): ?>
        <div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; max-height: 600px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.5;">
            <pre style="margin: 0; white-space: pre-wrap; word-wrap: break-word;"><?php echo $logContent; ?></pre>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Log file not found: <code><?php echo htmlspecialchars($logFiles[$selectedLog]); ?></code>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<!-- Auto-refresh toggle -->
<div class="card mt-4">
    <div class="card-body">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="autoRefresh">
            <label class="form-check-label" for="autoRefresh">
                Auto-refresh every 10 seconds
            </label>
        </div>
    </div>
</div>

<script>
let autoRefreshInterval = null;

document.getElementById('autoRefresh').addEventListener('change', function() {
    if (this.checked) {
        autoRefreshInterval = setInterval(function() {
            window.location.reload();
        }, 10000);
    } else {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }
});
</script>
