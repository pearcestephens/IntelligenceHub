<?php
/**
 * Tools Page - Tool registry and management
 */
$currentPage = 'tools';
$pageTitle = 'Tool Registry - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Tools', 'Tool Registry'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">Tool Registry</h1>
        <p class="text-muted">Available AI tools and integrations</p>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-tools"></i> Registered Tools</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tool Name</th>
                                    <th>Category</th>
                                    <th>Usage</th>
                                    <th>Success Rate</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="bi bi-search"></i> semantic_search</td>
                                    <td><span class="badge badge-info">Search</span></td>
                                    <td>1,247 calls</td>
                                    <td><span class="text-success">98.5%</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Test</button>
                                        <button class="btn btn-sm btn-outline-secondary">Configure</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-file-text"></i> read_file</td>
                                    <td><span class="badge badge-info">File I/O</span></td>
                                    <td>3,421 calls</td>
                                    <td><span class="text-success">99.2%</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Test</button>
                                        <button class="btn btn-sm btn-outline-secondary">Configure</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-code-slash"></i> execute_code</td>
                                    <td><span class="badge badge-warning">Execution</span></td>
                                    <td>892 calls</td>
                                    <td><span class="text-success">95.1%</span></td>
                                    <td><span class="badge badge-success">Active</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Test</button>
                                        <button class="btn btn-sm btn-outline-secondary">Configure</button>
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
