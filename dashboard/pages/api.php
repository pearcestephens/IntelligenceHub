<?php
/**
 * API Management Dashboard
 * Manage and test API endpoints
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');
?>

<div class="page-header">
    <h1 class="page-title">API Management</h1>
    <p class="page-subtitle">Monitor and test API endpoints</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-plug"></i>
            </div>
            <div class="stats-card-value">24</div>
            <div class="stats-card-label">Active Endpoints</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stats-card-value">1.2K</div>
            <div class="stats-card-label">Requests Today</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-tachometer-alt"></i>
            </div>
            <div class="stats-card-value">125ms</div>
            <div class="stats-card-label">Avg Response Time</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-card-value">99.8%</div>
            <div class="stats-card-label">Success Rate</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>API Endpoints</h5>
            </div>
            <div class="card-body">
                <?php
                $endpoints = [
                    ['method' => 'GET', 'path' => '/api/files', 'desc' => 'List intelligence files', 'status' => 'active'],
                    ['method' => 'GET', 'path' => '/api/search', 'desc' => 'Search codebase', 'status' => 'active'],
                    ['method' => 'POST', 'path' => '/api/scan', 'desc' => 'Trigger neural scan', 'status' => 'active'],
                    ['method' => 'GET', 'path' => '/api/functions', 'desc' => 'List functions', 'status' => 'active'],
                    ['method' => 'GET', 'path' => '/api/analytics', 'desc' => 'Get analytics data', 'status' => 'active']
                ];
                
                foreach ($endpoints as $ep):
                    $methodClass = $ep['method'] === 'GET' ? 'success' : ($ep['method'] === 'POST' ? 'primary' : 'warning');
                ?>
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-<?= $methodClass ?> me-2"><?= $ep['method'] ?></span>
                            <code><?= htmlspecialchars($ep['path']) ?></code>
                            <p class="text-muted small mb-0 mt-1"><?= htmlspecialchars($ep['desc']) ?></p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" onclick="testEndpoint('<?= $ep['path'] ?>')">
                            <i class="fas fa-play me-1"></i> Test
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-vial me-2"></i>API Tester</h5>
            </div>
            <div class="card-body">
                <form id="apiTestForm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <select class="form-select" name="method">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <input type="text" class="form-control" name="endpoint" placeholder="/api/endpoint" id="testEndpoint">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Request Body (JSON)</label>
                        <textarea class="form-control" name="body" rows="4" style="font-family: monospace;"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="executeTest()">
                        <i class="fas fa-paper-plane me-1"></i> Send Request
                    </button>
                </form>
                
                <div id="apiResponse" class="mt-4" style="display: none;">
                    <h6>Response:</h6>
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow: auto;"><code id="responseBody"></code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Documentation</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Quick API reference and examples</p>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-code me-2"></i>Authentication
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-code me-2"></i>Rate Limiting
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-code me-2"></i>Error Codes
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-code me-2"></i>Best Practices
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testEndpoint(path) {
    document.getElementById('testEndpoint').value = path;
    document.getElementById('apiTestForm').scrollIntoView({ behavior: 'smooth' });
}

function executeTest() {
    const responseDiv = document.getElementById('apiResponse');
    const responseBody = document.getElementById('responseBody');
    
    responseDiv.style.display = 'block';
    responseBody.textContent = JSON.stringify({
        "status": "success",
        "message": "API testing interface - connect to real endpoints",
        "data": []
    }, null, 2);
}
</script>
