<?php
/**
 * Analytics Dashboard
 * Advanced analytics and intelligence insights
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
                            <i class="fas fa-chart-line fa-3x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Growth Rate</h6>
                            <h3 class="mb-0">+23.4%</h3>
                            <small class="text-success"><i class="fas fa-arrow-up"></i> vs last month</small>
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
                            <i class="fas fa-clock fa-3x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Avg Scan Time</h6>
                            <h3 class="mb-0">4.2s</h3>
                            <small class="text-success"><i class="fas fa-arrow-down"></i> -15% faster</small>
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
                            <i class="fas fa-brain fa-3x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Intelligence Score</h6>
                            <h3 class="mb-0">94.2</h3>
                            <small class="text-success"><i class="fas fa-arrow-up"></i> Excellent</small>
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
                            <i class="fas fa-compress-arrows-alt fa-3x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Storage Used</h6>
                            <h3 class="mb-0">84.7 GB</h3>
                            <small class="text-muted">of 500 GB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        File Growth Trend (Last 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="growthTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        Intelligence Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="intelligenceDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>
                        Server Performance Comparison
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="serverPerfChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-file-code me-2"></i>
                        Code Quality Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="codeQualityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>
                        Top Performing Modules
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Core System
                            <span class="badge bg-success rounded-pill">98.5</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            API Services
                            <span class="badge bg-success rounded-pill">97.2</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Dashboard
                            <span class="badge bg-success rounded-pill">96.8</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Authentication
                            <span class="badge bg-info rounded-pill">95.1</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            File Management
                            <span class="badge bg-info rounded-pill">93.4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Issues Detected
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            High Complexity
                            <span class="badge bg-warning rounded-pill">12</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Unused Code
                            <span class="badge bg-info rounded-pill">8</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Security Warnings
                            <span class="badge bg-danger rounded-pill">3</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Performance Issues
                            <span class="badge bg-warning rounded-pill">5</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            Documentation Gaps
                            <span class="badge bg-secondary rounded-pill">15</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-2">
                        <strong>Optimize Scanner:</strong> Consider parallel processing to reduce scan time by ~30%
                    </div>
                    <div class="alert alert-warning mb-2">
                        <strong>Refactor Code:</strong> 3 functions exceed complexity threshold of 25
                    </div>
                    <div class="alert alert-success mb-0">
                        <strong>Great Job!</strong> Overall system health is excellent at 94.2%
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
                        <i class="fas fa-history me-2"></i>
                        Recent Scan Activity
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
                                    <th>New Files</th>
                                    <th>Updated</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime('-2 hours')) ?></td>
                                    <td><span class="badge bg-primary">CIS Server</span></td>
                                    <td>142,847</td>
                                    <td>4.2s</td>
                                    <td>34</td>
                                    <td>127</td>
                                    <td><span class="badge bg-success">Complete</span></td>
                                </tr>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime('-4 hours')) ?></td>
                                    <td><span class="badge bg-success">Intelligence</span></td>
                                    <td>566,016</td>
                                    <td>8.1s</td>
                                    <td>89</td>
                                    <td>234</td>
                                    <td><span class="badge bg-success">Complete</span></td>
                                </tr>
                                <tr>
                                    <td><?= date('Y-m-d H:i:s', strtotime('-6 hours')) ?></td>
                                    <td><span class="badge bg-warning">Vape Shed</span></td>
                                    <td>87,231</td>
                                    <td>3.7s</td>
                                    <td>12</td>
                                    <td>56</td>
                                    <td><span class="badge bg-success">Complete</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Growth Trend Chart
    const growthCtx = document.getElementById('growthTrendChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: Array.from({length: 30}, (_, i) => {
                const d = new Date();
                d.setDate(d.getDate() - (29 - i));
                return d.toLocaleDateString('en-US', {month: 'short', day: 'numeric'});
            }),
            datasets: [{
                label: 'Total Files',
                data: [543120, 545234, 548921, 551234, 553456, 556789, 559012, 561345, 563678, 565901, 566016, 566245, 566489, 566734, 566981, 567228, 567475, 567722, 567969, 568216, 568463, 568710, 568957, 569204, 569451, 569698, 569945, 570192, 570439, 566016],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // Intelligence Distribution Chart
    const intelligenceCtx = document.getElementById('intelligenceDistChart').getContext('2d');
    new Chart(intelligenceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Code Files', 'Documentation', 'Business', 'Operational'],
            datasets: [{
                data: [342891, 145234, 56789, 21102],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545']
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

    // Server Performance Chart
    const serverPerfCtx = document.getElementById('serverPerfChart').getContext('2d');
    new Chart(serverPerfCtx, {
        type: 'bar',
        data: {
            labels: ['CIS Server', 'Intelligence', 'Vape Shed', 'Services'],
            datasets: [{
                label: 'Scan Speed (files/sec)',
                data: [33925, 69877, 23602, 18934],
                backgroundColor: '#0d6efd'
            }, {
                label: 'Avg Response Time (ms)',
                data: [45, 32, 67, 89],
                backgroundColor: '#198754'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Code Quality Chart
    const qualityCtx = document.getElementById('codeQualityChart').getContext('2d');
    new Chart(qualityCtx, {
        type: 'radar',
        data: {
            labels: ['Complexity', 'Documentation', 'Security', 'Performance', 'Maintainability', 'Test Coverage'],
            datasets: [{
                label: 'Current',
                data: [72, 85, 95, 88, 79, 65],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.2)'
            }, {
                label: 'Target',
                data: [90, 90, 95, 90, 85, 80],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.2)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
});
</script>

<style>
#growthTrendChart {
    height: 300px !important;
}
</style>
