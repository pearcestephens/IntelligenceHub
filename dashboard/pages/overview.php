<?php
/**
 * Dashboard Overview Page
 * Main dashboard with statistics and insights
 */

// Get system stats
$stats = getSystemStats();
?>

<div class="page-header">
    <h1 class="page-title">Dashboard Overview</h1>
    <p class="page-subtitle">Welcome to the CIS Intelligence Dashboard - Your central hub for all intelligence data</p>
</div>

<!-- Stats Cards Row -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-file-code"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($stats['total_files']); ?></div>
            <div class="stats-card-label">Total Files Indexed</div>
            <div class="stats-card-change positive">
                <i class="fas fa-arrow-up"></i> All systems scanned
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-code"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($stats['total_functions']); ?></div>
            <div class="stats-card-label">Functions Extracted</div>
            <div class="stats-card-change positive">
                <i class="fas fa-check"></i> Ready to query
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stats-card-value"><?php echo count($stats['servers']); ?></div>
            <div class="stats-card-label">Servers Connected</div>
            <div class="stats-card-change positive">
                <i class="fas fa-signal"></i> All online
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stats-card-value"><?php echo $stats['total_size_mb']; ?> MB</div>
            <div class="stats-card-label">Intelligence Storage</div>
            <div class="stats-card-change">
                <i class="fas fa-info-circle"></i> Last scan: <?php echo timeAgo($stats['last_scan']); ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row g-4">

    <!-- Files by Type -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Files by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="filesTypeChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Server Distribution -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Server Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="serverChart" height="300"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- VS Code Sync Status Widget -->
<?php
require_once __DIR__ . '/../../app.php';
$vscodeStats = getVSCodeSyncStats();
?>
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div style="font-size: 3rem; opacity: 0.9;">
                            <i class="fas fa-rocket"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h4 class="mb-1" style="color: white;">🤖 VS Code Sync System</h4>
                        <p class="mb-0" style="opacity: 0.9;">Automated prompt synchronization & backup system</p>
                    </div>
                    <div class="col-auto text-end">
                        <div class="mb-2">
                            <strong>Total Syncs:</strong> <?php echo number_format($vscodeStats['total_syncs']); ?>
                        </div>
                        <div class="mb-2">
                            <strong>Last Sync:</strong>
                            <?php echo $vscodeStats['last_sync'] ? date('M d, Y g:i A', strtotime($vscodeStats['last_sync'])) : 'Never'; ?>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> Active
                            </span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="?page=ai-control-center" class="btn btn-light btn-lg">
                            <i class="fas fa-cog me-2"></i> Open AI Control Center
                        </a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <div style="opacity: 0.8; font-size: 0.9rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Features:</strong> Auto-backup (2 AM daily) • 30-day retention • History tracking • Export to VS Code
                        </div>
                    </div>
                    <div class="col-auto">
                        <?php if ($vscodeStats['cron_status']): ?>
                        <span style="opacity: 0.8; font-size: 0.9rem;">
                            <i class="fas fa-clock me-1"></i>
                            Last cron: <?php echo date('M d g:i A', strtotime($vscodeStats['cron_status']['last_run'])); ?>
                            <span class="badge bg-light text-dark ms-2"><?php echo $vscodeStats['cron_status']['last_status']; ?></span>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Agent Status Widget -->
<?php
$aiAgentStats = getAIAgentStats();
$aiAgentConfig = getAIAgentConfigStatus();
?>
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div style="font-size: 3rem; opacity: 0.9;">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h4 class="mb-1" style="color: white;">🤖 AI Agent System</h4>
                        <p class="mb-0" style="opacity: 0.9;">Conversational AI with memory, tools, and knowledge base</p>
                    </div>
                    <div class="col-auto text-end">
                        <?php if ($aiAgentStats['status'] === 'online'): ?>
                        <div class="mb-2">
                            <strong>Conversations:</strong> <?php echo number_format($aiAgentStats['conversations']); ?>
                        </div>
                        <div class="mb-2">
                            <strong>Messages:</strong> <?php echo number_format($aiAgentStats['messages']); ?>
                        </div>
                        <div class="mb-2">
                            <strong>KB Docs:</strong> <?php echo number_format($aiAgentStats['kb_docs']); ?>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> Online
                            </span>
                        </div>
                        <?php elseif ($aiAgentStats['status'] === 'not_configured'): ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-exclamation-triangle" style="font-size: 2rem;"></i>
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-warning">
                                    <i class="fas fa-cog"></i> Not Configured
                                </span>
                            </div>
                            <small style="opacity: 0.9;">Database tables not created</small>
                        </div>
                        <?php else: ?>
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-times-circle" style="font-size: 2rem;"></i>
                            </div>
                            <div class="mb-2">
                                <strong>Status:</strong>
                                <span class="badge bg-danger">
                                    <i class="fas fa-exclamation-circle"></i> Offline
                                </span>
                            </div>
                            <small style="opacity: 0.9;">Connection error</small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <a href="?page=ai-agent" class="btn btn-light btn-lg">
                            <i class="fas fa-terminal me-2"></i> Open AI Agent Dashboard
                        </a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <?php if ($aiAgentStats['status'] === 'online'): ?>
                        <div style="opacity: 0.8; font-size: 0.9rem;">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Features:</strong> Memory system • Tool calling • Knowledge base • Multi-turn conversations
                        </div>
                        <?php else: ?>
                        <div style="opacity: 0.8; font-size: 0.9rem;">
                            <i class="fas fa-tools me-1"></i>
                            <strong>Setup Required:</strong>
                            <?php if (!$aiAgentConfig['directory_exists']): ?>
                                AI Agent directory not found
                            <?php elseif (!$aiAgentConfig['database_ready']): ?>
                                Database connection required
                            <?php elseif (!$aiAgentConfig['tables_created']): ?>
                                Run database migrations
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-auto">
                        <?php if ($aiAgentStats['status'] === 'online' && isset($aiAgentStats['last_activity'])): ?>
                        <span style="opacity: 0.8; font-size: 0.9rem;">
                            <i class="fas fa-clock me-1"></i>
                            Last activity: <?php echo timeAgo($aiAgentStats['last_activity']); ?>
                            <span class="badge bg-light text-dark ms-2">
                                <?php echo $aiAgentStats['conversations_today']; ?> today
                            </span>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4 mt-2">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recently Scanned Files</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Type</th>
                            <th>Server</th>
                            <th>Scanned</th>
                        </tr>
                    </thead>
                    <tbody id="recentFiles">
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="loading"></div> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="?page=search" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i> Search Intelligence
                    </a>
                    <a href="?page=functions" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-code me-2"></i> Explore Functions
                    </a>
                    <a href="?page=files" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-folder-open me-2"></i> Browse Files
                    </a>
                    <?php if (ENABLE_SYSTEM_SCAN): ?>
                    <button class="btn btn-success btn-lg" id="triggerFullScan">
                        <i class="fas fa-radar me-2"></i> Trigger Full Scan
                    </button>
                    <?php endif; ?>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">System Health</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span>API Status</span>
                    <span class="badge bg-success">Online</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Database</span>
                    <span class="badge bg-success">Connected</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Scanner</span>
                    <span class="badge bg-success">Ready</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Cache</span>
                    <span class="badge bg-info">Active</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for charts -->
<script id="chartData" type="application/json">
<?php echo json_encode([
    'filesByType' => $stats['by_type'],
    'servers' => $stats['servers']
]); ?>
</script>

<script>
// Chart initialization - will be handled by overview.js
</script>
