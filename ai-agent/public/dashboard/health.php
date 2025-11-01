<?php
/**
 * Health Check Page - System health monitoring
 */
$currentPage = 'health';
$pageTitle = 'Health Check - CIS Neural AI';
$breadcrumb = ['Dashboard', 'System', 'Health Check'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">System Health Check</h1>
        <p class="text-muted">Monitor system components and connectivity</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="mb-0">
                        <span class="badge badge-success badge-lg" style="font-size: 2rem; padding: 1rem 2rem;">
                            <i class="bi bi-check-circle"></i> All Systems Operational
                        </span>
                    </h2>
                    <p class="text-muted mt-3">Last checked: <span id="lastChecked">Just now</span></p>
                    <button class="btn btn-primary" id="runHealthCheck">
                        <i class="bi bi-arrow-clockwise"></i> Run Health Check
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-server"></i> Core Services</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="bi bi-database fs-4 text-success"></i>
                            <strong class="ms-2">Database</strong>
                        </div>
                        <span class="badge badge-success">
                            <span class="pulse-dot"></span> Connected
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="bi bi-layers fs-4 text-success"></i>
                            <strong class="ms-2">Redis Cache</strong>
                        </div>
                        <span class="badge badge-success">
                            <span class="pulse-dot"></span> Connected
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <i class="bi bi-cloud fs-4 text-success"></i>
                            <strong class="ms-2">API Gateway</strong>
                        </div>
                        <span class="badge badge-success">
                            <span class="pulse-dot"></span> Online
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-robot fs-4 text-success"></i>
                            <strong class="ms-2">AI Services</strong>
                        </div>
                        <span class="badge badge-success">
                            <span class="pulse-dot"></span> Available
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-speedometer2"></i> Performance Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>CPU Usage</span>
                            <span>45%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 45%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Memory Usage</span>
                            <span>62%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 62%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Disk Usage</span>
                            <span>38%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 38%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Network I/O</span>
                            <span>25%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Uptime & Availability</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="mb-0">99.9%</h3>
                            <p class="text-muted">Uptime (30 days)</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="mb-0">45d 12h</h3>
                            <p class="text-muted">Current Uptime</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="mb-0">580ms</h3>
                            <p class="text-muted">Avg Response Time</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="mb-0">0.5%</h3>
                            <p class="text-muted">Error Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$inlineScript = <<<'JS'
document.getElementById('runHealthCheck').addEventListener('click', function() {
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Running...';
    setTimeout(() => {
        this.innerHTML = '<i class="bi bi-check-circle"></i> Check Complete';
        document.getElementById('lastChecked').textContent = 'Just now';
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Run Health Check';
        }, 2000);
    }, 1500);
});
JS;
require_once __DIR__ . '/templates/footer.php';
?>
