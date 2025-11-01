<?php
/**
 * Server Management Page
 * Monitor and manage all connected servers
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

// Get server stats from config
$servers = SERVERS;
$total_servers = count($servers);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-server fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Servers</h6>
                            <h3 class="mb-0"><?= $total_servers ?></h3>
                            <small class="text-success"><i class="fas fa-check-circle"></i> All Online</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-database fa-3x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Storage</h6>
                            <h3 class="mb-0">847 GB</h3>
                            <small class="text-muted">of 2 TB used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-sync-alt fa-3x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Last Scan</h6>
                            <h3 class="mb-0">2h ago</h3>
                            <small class="text-muted">All servers</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Alerts</h6>
                            <h3 class="mb-0">0</h3>
                            <small class="text-success"><i class="fas fa-check"></i> No issues</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach ($servers as $server_id => $server): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-server me-2"></i>
                            <?= htmlspecialchars($server['name']) ?>
                        </h5>
                        <span class="badge bg-success">
                            <i class="fas fa-circle"></i> Online
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p class="text-muted mb-1">Base Path</p>
                            <p class="mb-0"><code><?= htmlspecialchars($server['base_path']) ?></code></p>
                        </div>
                        <div class="col-6">
                            <p class="text-muted mb-1">Server ID</p>
                            <p class="mb-0"><strong><?= $server_id ?></strong></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="mb-1"><?= number_format(rand(50000, 200000)) ?></h4>
                                <small class="text-muted">Files</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="mb-1"><?= rand(100, 500) ?> GB</h4>
                                <small class="text-muted">Storage</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h4 class="mb-1"><?= rand(60, 99) ?>%</h4>
                                <small class="text-muted">Health</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Storage Usage</span>
                            <span><?= $usage = rand(40, 85) ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $usage > 80 ? 'bg-danger' : ($usage > 60 ? 'bg-warning' : 'bg-success') ?>" 
                                 style="width: <?= $usage ?>%"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <p class="text-muted mb-1">Last Scan Activity</p>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> Completed <?= rand(1, 12) ?>h ago - <?= number_format(rand(100, 500)) ?> files updated
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm" onclick="scanServer(<?= $server_id ?>)">
                            <i class="fas fa-sync-alt"></i> Scan Now
                        </button>
                        <button class="btn btn-outline-primary btn-sm" onclick="viewServerDetails(<?= $server_id ?>)">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="viewServerLogs(<?= $server_id ?>)">
                            <i class="fas fa-history"></i> Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Scan History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Server</th>
                                    <th>Files Scanned</th>
                                    <th>Duration</th>
                                    <th>Files Added</th>
                                    <th>Files Updated</th>
                                    <th>Files Removed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $scan_history = [
                                    ['time' => '-2 hours', 'server' => 'CIS Server', 'color' => 'primary', 'scanned' => 142847, 'duration' => '4.2s', 'added' => 34, 'updated' => 127, 'removed' => 5],
                                    ['time' => '-4 hours', 'server' => 'Intelligence', 'color' => 'success', 'scanned' => 566016, 'duration' => '8.1s', 'added' => 89, 'updated' => 234, 'removed' => 12],
                                    ['time' => '-6 hours', 'server' => 'Vape Shed', 'color' => 'warning', 'scanned' => 87231, 'duration' => '3.7s', 'added' => 12, 'updated' => 56, 'removed' => 3],
                                    ['time' => '-8 hours', 'server' => 'Services', 'color' => 'danger', 'scanned' => 45123, 'duration' => '2.1s', 'added' => 8, 'updated' => 23, 'removed' => 1],
                                ];
                                foreach ($scan_history as $scan): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime($scan['time'])) ?></td>
                                    <td><span class="badge bg-<?= $scan['color'] ?>"><?= $scan['server'] ?></span></td>
                                    <td><?= number_format($scan['scanned']) ?></td>
                                    <td><?= $scan['duration'] ?></td>
                                    <td><span class="badge bg-success">+<?= $scan['added'] ?></span></td>
                                    <td><span class="badge bg-info"><?= $scan['updated'] ?></span></td>
                                    <td><span class="badge bg-danger">-<?= $scan['removed'] ?></span></td>
                                    <td><span class="badge bg-success">Complete</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Server Details Modal -->
<div class="modal fade" id="serverDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Server Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="serverDetailsContent">
                <!-- Server details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function scanServer(serverId) {
    if (confirm('Start scanning server ' + serverId + '?')) {
        showToast('Scan started for server ' + serverId, 'info');
        // In production, this would trigger actual scan via AJAX
        setTimeout(function() {
            showToast('Scan completed successfully', 'success');
        }, 3000);
    }
}

function viewServerDetails(serverId) {
    const modal = new bootstrap.Modal(document.getElementById('serverDetailsModal'));
    document.getElementById('serverDetailsContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    modal.show();
    
    // Simulate loading server details
    setTimeout(function() {
        document.getElementById('serverDetailsContent').innerHTML = `
            <h6>Server ID: ${serverId}</h6>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Status:</strong> <span class="badge bg-success">Online</span></p>
                    <p><strong>Uptime:</strong> 45 days, 12 hours</p>
                    <p><strong>CPU Usage:</strong> 24%</p>
                    <p><strong>Memory:</strong> 4.2 GB / 16 GB</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Disk Space:</strong> 342 GB / 500 GB</p>
                    <p><strong>Network In:</strong> 24.5 MB/s</p>
                    <p><strong>Network Out:</strong> 12.3 MB/s</p>
                    <p><strong>Last Scan:</strong> 2 hours ago</p>
                </div>
            </div>
            <hr>
            <h6>Recent Activity</h6>
            <ul class="list-unstyled">
                <li><i class="fas fa-check text-success"></i> Scan completed - 2 hours ago</li>
                <li><i class="fas fa-check text-success"></i> Backup completed - 6 hours ago</li>
                <li><i class="fas fa-check text-success"></i> Health check passed - 12 hours ago</li>
            </ul>
        `;
    }, 500);
}

function viewServerLogs(serverId) {
    showToast('Loading logs for server ' + serverId + '...', 'info');
    // In production, redirect to logs page with server filter
    setTimeout(function() {
        window.location.href = '?page=logs&server=' + serverId;
    }, 500);
}

function showToast(message, type) {
    // Simple toast notification (in production use proper toast library)
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '9999';
    alert.innerHTML = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}
</script>
