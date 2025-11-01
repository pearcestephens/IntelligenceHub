<?php
/**
 * Metrics Page - Detailed metrics breakdown
 */
$currentPage = 'metrics';
$pageTitle = 'Metrics - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Metrics'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">System Metrics</h1>
        <p class="text-muted">Detailed metrics and performance data</p>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-speedometer2"></i> All Metrics</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Real-time metrics dashboard</p>
                    <div id="metricsContainer">Loading metrics...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
