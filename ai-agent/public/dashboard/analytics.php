<?php
/**
 * Analytics Page - Detailed metrics and charts
 */
$currentPage = 'analytics';
$pageTitle = 'Analytics - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Analytics'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">Analytics & Reports</h1>
        <p class="text-muted">Detailed performance analytics and trends</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-graph-up"></i> Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-pie-chart"></i> Tool Usage</h5>
                </div>
                <div class="card-body">
                    <canvas id="toolUsageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0"><i class="bi bi-table"></i> Detailed Metrics</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Coming soon: Detailed analytics tables and export functionality</p>
        </div>
    </div>
</div>

<?php
$inlineScript = <<<'JS'
// Analytics charts initialization
console.log('Analytics page loaded');
JS;
require_once __DIR__ . '/templates/footer.php';
?>
