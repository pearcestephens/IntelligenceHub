<?php
/**
 * CIS Neural AI Dashboard - Main Dashboard Page
 * Production-grade modular dashboard with real-time metrics
 * 
 * @package CIS Neural AI
 * @author Ecigdis Limited
 * @version 1.0.0
 */

// Page configuration
$currentPage = 'dashboard';
$pageTitle = 'Dashboard - CIS Neural AI Agent';
$breadcrumb = ['Dashboard'];

// Include header template
require_once __DIR__ . '/templates/header.php';
?>

<!-- Main Dashboard Content -->
<div class="dashboard-container">
    
    <!-- Page Header -->
    <div class="page-header mb-4">
        <h1 class="h3">Neural AI Dashboard</h1>
        <p class="text-muted">Real-time performance monitoring and system metrics</p>
    </div>
    
    <!-- Key Metrics Grid -->
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Response Time (P95)</div>
                <div class="metric-value" id="metric-response-p95">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change positive" id="metric-response-trend">
                    <i class="bi bi-arrow-down"></i> Target: &lt;700ms
                </div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-chat-dots"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Active Conversations</div>
                <div class="metric-value" id="metric-conversations">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change" id="metric-conversations-trend">
                    <i class="bi bi-graph-up"></i> Recent activity
                </div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Requests/Min</div>
                <div class="metric-value" id="metric-rpm">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change" id="metric-rpm-trend">
                    <i class="bi bi-activity"></i> Live rate
                </div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-layers"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Cache Hit Rate</div>
                <div class="metric-value" id="metric-cache-rate">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change positive" id="metric-cache-trend">
                    <i class="bi bi-arrow-up"></i> Target: &gt;70%
                </div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Error Rate</div>
                <div class="metric-value" id="metric-error-rate">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change negative" id="metric-error-trend">
                    <i class="bi bi-arrow-down"></i> Target: &lt;1%
                </div>
            </div>
        </div>
        
        <div class="metric-card">
            <div class="metric-icon">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="metric-content">
                <div class="metric-label">Token Usage</div>
                <div class="metric-value" id="metric-tokens-total">
                    <span class="spinner-border spinner-border-sm"></span>
                </div>
                <div class="metric-change" id="metric-tokens-cost">
                    <i class="bi bi-cash"></i> Cost: <span id="metric-tokens-cost-value">--</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-graph-up"></i> Response Time Trends
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-range="5min">5min</button>
                        <button type="button" class="btn btn-outline-secondary" data-range="15min">15min</button>
                        <button type="button" class="btn btn-outline-secondary" data-range="hour">1h</button>
                        <button type="button" class="btn btn-outline-secondary" data-range="day">24h</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="responseTimeChart" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-shield-check"></i> System Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="health-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Database</span>
                            <span class="badge badge-success" id="health-database">
                                <span class="pulse-dot"></span> Connected
                            </span>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Redis Cache</span>
                            <span class="badge badge-success" id="health-redis">
                                <span class="pulse-dot"></span> Connected
                            </span>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Memory Usage</span>
                            <span class="text-muted" id="health-memory">-- MB</span>
                        </div>
                    </div>
                    <div class="health-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>PHP Version</span>
                            <span class="text-muted" id="health-php">--</span>
                        </div>
                    </div>
                    <hr>
                    <div class="performance-score text-center">
                        <div class="score-label text-muted mb-2">Performance Score</div>
                        <div class="score-value h1 mb-1" id="health-score">--</div>
                        <div class="score-grade">
                            <span class="badge badge-lg badge-success" id="health-grade">A</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tool Performance Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-tools"></i> Tool Performance
                    </h5>
                    <a href="tools.php" class="btn btn-sm btn-outline-primary">
                        View All Tools <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tool Name</th>
                                    <th>Executions</th>
                                    <th>Avg Time</th>
                                    <th>Success Rate</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="tools-table-body">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Loading tool performance data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Errors Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-exclamation-octagon"></i> Recent Errors
                    </h5>
                    <a href="logs.php" class="btn btn-sm btn-outline-danger">
                        View All Logs <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Error Type</th>
                                    <th>Message</th>
                                    <th>Count</th>
                                    <th>Last Seen</th>
                                    <th>Severity</th>
                                </tr>
                            </thead>
                            <tbody id="errors-table-body">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Loading error data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<!-- Custom inline script for dashboard-specific functionality -->
<?php
$inlineScript = <<<'JAVASCRIPT'
// Chart.js instance
let responseTimeChart = null;

// Initialize response time chart
function initResponseTimeChart() {
    const ctx = document.getElementById('responseTimeChart');
    if (!ctx) return;
    
    responseTimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'P50 (ms)',
                    data: [],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'P95 (ms)',
                    data: [],
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'P99 (ms)',
                    data: [],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        color: '#cbd5e1',
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    borderColor: '#334155',
                    borderWidth: 1,
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#334155'
                    },
                    ticks: {
                        color: '#94a3b8',
                        callback: function(value) {
                            return value + 'ms';
                        }
                    }
                },
                x: {
                    grid: {
                        color: '#334155'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                }
            }
        }
    });
}

// Update chart with new data
function updateResponseTimeChart(metrics) {
    if (!responseTimeChart || !metrics.response_times) return;
    
    const timestamp = new Date().toLocaleTimeString();
    
    // Keep only last 20 data points
    if (responseTimeChart.data.labels.length >= 20) {
        responseTimeChart.data.labels.shift();
        responseTimeChart.data.datasets.forEach(dataset => dataset.data.shift());
    }
    
    responseTimeChart.data.labels.push(timestamp);
    responseTimeChart.data.datasets[0].data.push(metrics.response_times.p50_ms || 0);
    responseTimeChart.data.datasets[1].data.push(metrics.response_times.p95_ms || 0);
    responseTimeChart.data.datasets[2].data.push(metrics.response_times.p99_ms || 0);
    
    responseTimeChart.update('none'); // Update without animation
}

// Custom UI updates for dashboard-specific elements
Dashboard.UI.updateMetrics = (function(originalUpdate) {
    return function(data) {
        // Call original update method
        originalUpdate.call(this, data);
        
        if (!data || !data.metrics) return;
        
        const metrics = data.metrics;
        
        // Update conversations
        if (metrics.conversations) {
            this.updateElement('metric-conversations', metrics.conversations.total || 0);
        }
        
        // Calculate and update RPM (requests per minute)
        if (metrics.response_times && metrics.response_times.total_requests) {
            const rpm = Math.round(metrics.response_times.total_requests / 60);
            this.updateElement('metric-rpm', rpm);
        }
        
        // Update system health
        if (metrics.system) {
            this.updateElement('health-database', metrics.system.database);
            this.updateElement('health-redis', metrics.system.redis);
            this.updateElement('health-memory', metrics.system.memory_usage_mb + ' MB');
            this.updateElement('health-php', metrics.system.php_version);
            
            // Update health badges
            const dbBadge = document.getElementById('health-database');
            if (dbBadge) {
                dbBadge.className = 'badge badge-' + (metrics.system.database === 'connected' ? 'success' : 'danger');
                dbBadge.innerHTML = '<span class="pulse-dot"></span> ' + metrics.system.database;
            }
            
            const redisBadge = document.getElementById('health-redis');
            if (redisBadge) {
                redisBadge.className = 'badge badge-' + (metrics.system.redis === 'connected' ? 'success' : 'warning');
                redisBadge.innerHTML = '<span class="pulse-dot"></span> ' + metrics.system.redis;
            }
        }
        
        // Update performance score
        if (metrics.performance_score) {
            this.updateElement('health-score', metrics.performance_score.score);
            this.updateElement('health-grade', metrics.performance_score.grade);
            
            const gradeBadge = document.getElementById('health-grade');
            if (gradeBadge) {
                const gradeClass = 
                    metrics.performance_score.grade === 'A' ? 'success' :
                    metrics.performance_score.grade === 'B' ? 'info' :
                    metrics.performance_score.grade === 'C' ? 'warning' : 'danger';
                gradeBadge.className = 'badge badge-lg badge-' + gradeClass;
            }
        }
        
        // Update chart
        updateResponseTimeChart(metrics);
    };
})(Dashboard.UI.updateMetrics);

// Initialize chart when dashboard loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResponseTimeChart);
} else {
    initResponseTimeChart();
}

// Time range selector for chart
document.addEventListener('click', function(e) {
    if (e.target.hasAttribute('data-range')) {
        // Update active button
        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.classList.remove('active');
        });
        e.target.classList.add('active');
        
        // Fetch data with new range
        const range = e.target.getAttribute('data-range');
        Dashboard.API.fetchMetrics(range).then(data => {
            Dashboard.UI.updateMetrics(data);
        });
    }
});

// Initial load
console.log('🎯 Dashboard page loaded - starting initial data fetch');
Dashboard.refresh();
JAVASCRIPT;
?>

<?php
// Include footer template
require_once __DIR__ . '/templates/footer.php';
?>
