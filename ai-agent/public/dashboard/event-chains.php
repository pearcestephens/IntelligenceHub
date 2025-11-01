<?php
/**
 * Event Chains Page - Automation workflows
 */
$currentPage = 'event-chains';
$pageTitle = 'Event Chains - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Automation', 'Event Chains'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3">Event Chains</h1>
            <p class="text-muted">Create and manage automated event workflows</p>
        </div>
        <button class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Event Chain
        </button>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-link-45deg"></i> Active Event Chains</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Chain Name</th>
                                    <th>Trigger</th>
                                    <th>Actions</th>
                                    <th>Status</th>
                                    <th>Last Run</th>
                                    <th>Controls</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>New Customer Onboarding</td>
                                    <td><span class="badge badge-info">Webhook</span></td>
                                    <td>3 actions</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>5 minutes ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger">Disable</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Error Alert System</td>
                                    <td><span class="badge badge-warning">Error Threshold</span></td>
                                    <td>2 actions</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>1 hour ago</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                                        <button class="btn btn-sm btn-outline-danger">Disable</button>
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

<?php require_once __DIR__ . '/templates/footer.php'; ?>
