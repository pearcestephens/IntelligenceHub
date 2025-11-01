<?php
/**
 * Bots Management Page - View and manage all bots
 */
$currentPage = 'bots';
$pageTitle = 'Bot Management - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Bot Management', 'All Bots'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3">Bot Management</h1>
            <p class="text-muted">Manage and monitor all AI bots</p>
        </div>
        <a href="bot-create.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Bot
        </a>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-robot"></i> Active Bots</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bot Name</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Last Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="botsTableBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Loading bots...
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

<?php
$inlineScript = <<<'JS'
// Load bots data
async function loadBots() {
    const tbody = document.getElementById('botsTableBody');
    tbody.innerHTML = `
        <tr>
            <td>Neural Assistant</td>
            <td><span class="badge badge-info">GPT-4</span></td>
            <td><span class="badge badge-success"><span class="pulse-dot"></span> Online</span></td>
            <td>2 minutes ago</td>
            <td>
                <button class="btn btn-sm btn-outline-primary">Configure</button>
                <button class="btn btn-sm btn-outline-danger">Stop</button>
            </td>
        </tr>
        <tr>
            <td>Code Review Bot</td>
            <td><span class="badge badge-info">GPT-4</span></td>
            <td><span class="badge badge-success"><span class="pulse-dot"></span> Online</span></td>
            <td>5 minutes ago</td>
            <td>
                <button class="btn btn-sm btn-outline-primary">Configure</button>
                <button class="btn btn-sm btn-outline-danger">Stop</button>
            </td>
        </tr>
    `;
}
loadBots();
JS;
require_once __DIR__ . '/templates/footer.php';
?>
