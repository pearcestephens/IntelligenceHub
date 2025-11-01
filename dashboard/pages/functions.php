<?php
/**
 * Functions Analysis Page
 * Analyze code functions and intelligence patterns
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-code fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Functions</h6>
                            <h3 class="mb-0">12,847</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-cube fa-3x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Classes</h6>
                            <h3 class="mb-0">3,421</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-boxes fa-3x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Modules</h6>
                            <h3 class="mb-0">156</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-project-diagram fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Dependencies</h6>
                            <h3 class="mb-0">8,942</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Function Distribution by Type
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="functionTypeChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Function Complexity Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="complexityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Top Functions by Usage
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Function Name</th>
                                    <th>Type</th>
                                    <th>Module</th>
                                    <th>Lines of Code</th>
                                    <th>Complexity</th>
                                    <th>Usage Count</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>getDbConnection()</code></td>
                                    <td><span class="badge bg-primary">Core</span></td>
                                    <td>Database</td>
                                    <td>42</td>
                                    <td><span class="badge bg-success">Low (3)</span></td>
                                    <td>1,247</td>
                                    <td><span class="badge bg-success">Optimal</span></td>
                                </tr>
                                <tr>
                                    <td><code>validateInput()</code></td>
                                    <td><span class="badge bg-info">Utility</span></td>
                                    <td>Validation</td>
                                    <td>87</td>
                                    <td><span class="badge bg-warning">Medium (12)</span></td>
                                    <td>892</td>
                                    <td><span class="badge bg-success">Optimal</span></td>
                                </tr>
                                <tr>
                                    <td><code>processTransfer()</code></td>
                                    <td><span class="badge bg-success">Business</span></td>
                                    <td>Transfers</td>
                                    <td>324</td>
                                    <td><span class="badge bg-danger">High (28)</span></td>
                                    <td>567</td>
                                    <td><span class="badge bg-warning">Refactor Needed</span></td>
                                </tr>
                                <tr>
                                    <td><code>logActivity()</code></td>
                                    <td><span class="badge bg-primary">Core</span></td>
                                    <td>Logging</td>
                                    <td>56</td>
                                    <td><span class="badge bg-success">Low (5)</span></td>
                                    <td>2,341</td>
                                    <td><span class="badge bg-success">Optimal</span></td>
                                </tr>
                                <tr>
                                    <td><code>generateReport()</code></td>
                                    <td><span class="badge bg-success">Business</span></td>
                                    <td>Reports</td>
                                    <td>198</td>
                                    <td><span class="badge bg-warning">Medium (15)</span></td>
                                    <td>234</td>
                                    <td><span class="badge bg-success">Optimal</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Functions Requiring Attention
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-code me-2"></i>High Complexity Functions (3)</h6>
                        <ul class="mb-0">
                            <li><code>processTransfer()</code> - Complexity: 28 (Consider refactoring)</li>
                            <li><code>syncInventory()</code> - Complexity: 31 (Urgent refactoring needed)</li>
                            <li><code>calculatePricing()</code> - Complexity: 26 (Consider refactoring)</li>
                        </ul>
                    </div>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-clock me-2"></i>Unused Functions (5)</h6>
                        <ul class="mb-0">
                            <li><code>legacyImport()</code> - Last used: Never (Consider removing)</li>
                            <li><code>tempDebug()</code> - Last used: 6 months ago (Consider removing)</li>
                            <li><code>oldCalculation()</code> - Last used: 1 year ago (Consider removing)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function Type Distribution Chart
    const typeCtx = document.getElementById('functionTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: ['Core', 'Business Logic', 'Utility', 'API', 'UI'],
            datasets: [{
                data: [2847, 4521, 3142, 1892, 445],
                backgroundColor: [
                    '#0d6efd',
                    '#198754',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Complexity Chart
    const complexityCtx = document.getElementById('complexityChart').getContext('2d');
    new Chart(complexityCtx, {
        type: 'bar',
        data: {
            labels: ['Low (1-10)', 'Medium (11-20)', 'High (21-30)', 'Very High (31+)'],
            datasets: [{
                label: 'Number of Functions',
                data: [8942, 3124, 621, 160],
                backgroundColor: [
                    '#198754',
                    '#ffc107',
                    '#fd7e14',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
