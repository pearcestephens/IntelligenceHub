<?php
/**
 * Triggers Page - Manage workflow triggers
 */
$currentPage = 'triggers';
$pageTitle = 'Triggers - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Automation', 'Triggers'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3">Workflow Triggers</h1>
            <p class="text-muted">Configure events that start workflows</p>
        </div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Trigger
        </button>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-1 text-primary"></i>
                    <h5 class="card-title mt-3">Scheduled Triggers</h5>
                    <p class="card-text text-muted">Run workflows on a schedule</p>
                    <button class="btn btn-outline-primary btn-sm">Configure</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-webhook fs-1 text-info"></i>
                    <h5 class="card-title mt-3">Webhook Triggers</h5>
                    <p class="card-text text-muted">Trigger from external events</p>
                    <button class="btn btn-outline-primary btn-sm">Configure</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-database fs-1 text-success"></i>
                    <h5 class="card-title mt-3">Data Triggers</h5>
                    <p class="card-text text-muted">React to data changes</p>
                    <button class="btn btn-outline-primary btn-sm">Configure</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
