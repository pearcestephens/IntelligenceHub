<?php
/**
 * Neural Scanner Status & Monitoring
 */

if (!defined('DASHBOARD_ACCESS')) die('Direct access not permitted');

// Get scanner statistics
$db = getDbConnection();

// File counts by server
$serverStats = $db->query("
    SELECT 
        server_id,
        COUNT(*) as file_count,
        ROUND(SUM(file_size) / 1024 / 1024, 2) as total_mb,
        MIN(indexed_at) as first_scan,
        MAX(indexed_at) as last_scan
    FROM intelligence_files
    GROUP BY server_id
    ORDER BY file_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Intelligence type breakdown
$typeStats = $db->query("
    SELECT 
        intelligence_type,
        COUNT(*) as count,
        ROUND(AVG(file_size) / 1024, 2) as avg_kb
    FROM intelligence_files
    GROUP BY intelligence_type
    ORDER BY count DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Recent scan activity
$recentScans = $db->query("
    SELECT 
        server_id,
        DATE(indexed_at) as scan_date,
        COUNT(*) as files_indexed
    FROM intelligence_files
    WHERE indexed_at >= DATE_SUB(NOW(), INTERVAL 7 DAYS)
    GROUP BY server_id, DATE(indexed_at)
    ORDER BY scan_date DESC, server_id
    LIMIT 21
")->fetchAll(PDO::FETCH_ASSOC);

// Total stats
$totalFiles = $db->query("SELECT COUNT(*) FROM intelligence_files")->fetchColumn();
$totalSize = $db->query("SELECT ROUND(SUM(file_size) / 1024 / 1024, 2) FROM intelligence_files")->fetchColumn();

// Check for bloat (should be 0)
$bloatCheck = $db->query("
    SELECT COUNT(*) 
    FROM intelligence_files 
    WHERE server_id = 'hdgwrzntwa' 
       OR file_path LIKE '%node_modules%' 
       OR file_path LIKE '%vendor/%'
")->fetchColumn();

// Get last cron run from log
$lastCronRun = 'Unknown';
$cronLogFile = '/home/master/applications/hdgwrzntwa/public_html/logs/neural_scan.log';
if (file_exists($cronLogFile)) {
    $logLines = file($cronLogFile);
    if (!empty($logLines)) {
        $lastLine = end($logLines);
        if (preg_match('/\[([\d\-: ]+)\]/', $lastLine, $matches)) {
            $lastCronRun = $matches[1];
        }
    }
}
?>

<div class="page-header mb-4">
    <h2><i class="fas fa-robot text-primary"></i> Neural Scanner Status</h2>
    <p class="text-muted">Production-only intelligence scanning with bloat prevention</p>
</div>

<!-- Health Status Alert -->
<?php if ($bloatCheck > 0): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <strong>BLOAT DETECTED!</strong>
        Found <?= number_format($bloatCheck) ?> files that should have been excluded.
        <a href="?page=cleanup" class="btn btn-sm btn-light ms-3">Clean Up Now</a>
    </div>
<?php else: ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <strong>Scanner Health: EXCELLENT</strong>
        No bloat detected. All exclusion rules are working correctly.
    </div>
<?php endif; ?>

<!-- Scanner Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h3 class="mb-0"><?= number_format($totalFiles) ?></h3>
                <p class="mb-0">Total Files Indexed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h3 class="mb-0"><?= $totalSize ?> MB</h3>
                <p class="mb-0">Total Storage</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h3 class="mb-0"><?= count($serverStats) ?></h3>
                <p class="mb-0">Production Servers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h3 class="mb-0"><?= $bloatCheck ?></h3>
                <p class="mb-0">Bloat Files (Target: 0)</p>
            </div>
        </div>
    </div>
</div>

<!-- Last Cron Run -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-clock"></i> Scanner Schedule</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Schedule:</strong> Daily at 3:00 AM</p>
                <p><strong>Last Run:</strong> <?= htmlspecialchars($lastCronRun) ?></p>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" onclick="runScannerNow()">
                    <i class="fas fa-play"></i> Run Scanner Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Server Breakdown -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-server"></i> Files by Server (Production Only)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Server ID</th>
                        <th class="text-end">Files</th>
                        <th class="text-end">Storage (MB)</th>
                        <th>First Scan</th>
                        <th>Last Scan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($serverStats as $server): ?>
                        <?php 
                        // Check if this is a forbidden server
                        $isForbidden = ($server['server_id'] === 'hdgwrzntwa');
                        $rowClass = $isForbidden ? 'table-danger' : '';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td>
                                <strong><?= htmlspecialchars($server['server_id']) ?></strong>
                                <?php if ($isForbidden): ?>
                                    <span class="badge bg-danger ms-2">SHOULD NOT EXIST</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format($server['file_count']) ?></td>
                            <td class="text-end"><?= $server['total_mb'] ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($server['first_scan'])) ?></td>
                            <td><?= date('Y-m-d H:i', strtotime($server['last_scan'])) ?></td>
                            <td>
                                <?php if ($isForbidden): ?>
                                    <span class="badge bg-danger">BLOAT</span>
                                <?php else: ?>
                                    <span class="badge bg-success">HEALTHY</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Intelligence Type Breakdown -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Intelligence Types</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-end">Count</th>
                        <th class="text-end">Avg Size (KB)</th>
                        <th class="text-end">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($typeStats as $type): ?>
                        <?php 
                        $percentage = ($type['count'] / $totalFiles) * 100;
                        $isDocSpam = (strpos($type['intelligence_type'], 'documentation') !== false && $percentage > 50);
                        ?>
                        <tr class="<?= $isDocSpam ? 'table-warning' : '' ?>">
                            <td>
                                <?= htmlspecialchars($type['intelligence_type']) ?>
                                <?php if ($isDocSpam): ?>
                                    <span class="badge bg-warning ms-2">HIGH RATIO</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format($type['count']) ?></td>
                            <td class="text-end"><?= $type['avg_kb'] ?></td>
                            <td class="text-end"><?= number_format($percentage, 1) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Scan Activity (Last 7 Days) -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Scan Activity (Last 7 Days)</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Server</th>
                        <th class="text-end">Files Indexed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentScans as $scan): ?>
                        <tr>
                            <td><?= $scan['scan_date'] ?></td>
                            <td><?= htmlspecialchars($scan['server_id']) ?></td>
                            <td class="text-end"><?= number_format($scan['files_indexed']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scanner Configuration -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-cog"></i> Scanner Configuration</h5>
    </div>
    <div class="card-body">
        <h6>✅ Allowed Servers (Production Only):</h6>
        <ul>
            <li><code>jcepnzzkmj</code> - CIS Production Server</li>
            <li><code>fhrehrpjmu</code> - Production Server 2</li>
            <li><code>dvaxgvsxmz</code> - Production Server 3</li>
        </ul>

        <h6 class="mt-3">❌ Excluded Directories:</h6>
        <code>node_modules, vendor, .git, cache, temp, tmp, logs, backups, uploads, assets/template, .vscode, .idea, tests, __pycache__</code>

        <h6 class="mt-3">⚙️ Limits:</h6>
        <ul>
            <li>Max file size: <code>5 MB</code></li>
            <li>Max files per server: <code>20,000</code></li>
            <li>Max directory depth: <code>10 levels</code></li>
        </ul>

        <h6 class="mt-3">📁 Indexed Extensions:</h6>
        <code>php, js, css, html, json, xml, yaml, yml, md, txt</code>
    </div>
</div>

<script>
function runScannerNow() {
    if (!confirm('Run scanner now? This may take 5-10 minutes.')) return;
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Running...';
    
    fetch('api/run_scanner.php', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            alert(data.message || 'Scanner started');
            location.reload();
        })
        .catch(err => {
            alert('Error: ' + err);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-play"></i> Run Scanner Now';
        });
}
</script>
