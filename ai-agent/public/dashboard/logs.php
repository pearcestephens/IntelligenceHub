<?php
/**
 * Logs Page - System logs viewer
 */
$currentPage = 'logs';
$pageTitle = 'System Logs - CIS Neural AI';
$breadcrumb = ['Dashboard', 'System', 'Logs'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">System Logs</h1>
        <p class="text-muted">Monitor system activity and errors</p>
    </div>
    
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <select class="form-select" id="logLevel">
                                <option value="all">All Levels</option>
                                <option value="error">Error</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                                <option value="debug">Debug</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="logSource">
                                <option value="all">All Sources</option>
                                <option value="api">API</option>
                                <option value="bot">Bot</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" placeholder="Search logs..." id="logSearch">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="refreshLogs">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-file-text"></i> Recent Logs</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="autoRefresh">
                        <label class="form-check-label" for="autoRefresh">Auto-refresh</label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 150px">Timestamp</th>
                                    <th style="width: 100px">Level</th>
                                    <th style="width: 120px">Source</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="logsTableBody">
                                <tr>
                                    <td>2025-10-10 14:32:15</td>
                                    <td><span class="badge badge-info">INFO</span></td>
                                    <td>API</td>
                                    <td>Request completed successfully</td>
                                </tr>
                                <tr>
                                    <td>2025-10-10 14:31:42</td>
                                    <td><span class="badge badge-warning">WARNING</span></td>
                                    <td>Bot</td>
                                    <td>Rate limit approaching threshold</td>
                                </tr>
                                <tr>
                                    <td>2025-10-10 14:30:18</td>
                                    <td><span class="badge badge-danger">ERROR</span></td>
                                    <td>System</td>
                                    <td>Database connection timeout</td>
                                </tr>
                                <tr>
                                    <td>2025-10-10 14:29:55</td>
                                    <td><span class="badge badge-success">DEBUG</span></td>
                                    <td>API</td>
                                    <td>Cache hit for key: metrics_data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
