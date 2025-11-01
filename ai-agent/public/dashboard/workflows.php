<?php
/**
 * Workflows Page - Visual workflow builder
 */
$currentPage = 'workflows';
$pageTitle = 'Workflows - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Automation', 'Workflows'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3">Workflow Builder</h1>
            <p class="text-muted">Design and deploy automated workflows</p>
        </div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Workflow
        </button>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-diagram-3"></i> My Workflows</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Data Processing Pipeline</h5>
                                    <p class="card-text text-muted">5 nodes • Last edited 2 days ago</p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary">Edit</button>
                                        <button class="btn btn-sm btn-outline-secondary">Duplicate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Customer Engagement Flow</h5>
                                    <p class="card-text text-muted">8 nodes • Last edited 5 days ago</p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-primary">Edit</button>
                                        <button class="btn btn-sm btn-outline-secondary">Duplicate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
