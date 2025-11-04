<?php
/**
 * Frontend Fix Approvals Dashboard
 *
 * User interface for reviewing and approving AI-generated fixes
 *
 * @package AI Agent
 * @version 1.0.0
 * @date 2025-11-04
 */

$currentPage = 'approvals';
$pageTitle = 'Fix Approvals - AI Agent Dashboard';
$breadcrumb = ['Dashboard', 'Automation', 'Fix Approvals'];

// Database connection (adjust path as needed)
require_once __DIR__ . '/../../config/database.php';

// Get pending fixes
$stmt = $db->prepare("
    SELECT
        f.*,
        COUNT(d.id) as deployment_count
    FROM frontend_pending_fixes f
    LEFT JOIN frontend_deployment_log d ON d.fix_id = f.id
    WHERE f.status = 'pending'
    GROUP BY f.id
    ORDER BY f.created_at DESC
");
$stmt->execute();
$pendingFixes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$statsStmt = $db->query("
    SELECT
        status,
        COUNT(*) as count
    FROM frontend_pending_fixes
    GROUP BY status
");
$stats = [];
while ($row = $statsStmt->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .code-block {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
        }
        .code-diff-remove {
            background: #ffebee;
            border-left: 3px solid #d32f2f;
        }
        .code-diff-add {
            background: #e8f5e9;
            border-left: 3px solid #388e3c;
        }
        .fix-card {
            transition: box-shadow 0.3s ease;
        }
        .fix-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .stat-card {
            border-left: 4px solid;
        }
        .stat-card.pending { border-color: #ffc107; }
        .stat-card.approved { border-color: #28a745; }
        .stat-card.rejected { border-color: #dc3545; }
        .stat-card.applied { border-color: #17a2b8; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="/ai-agent/public/dashboard/">
            <i class="bi bi-robot"></i> AI Agent Dashboard
        </a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="/ai-agent/public/dashboard/">
                <i class="bi bi-house"></i> Home
            </a>
            <a class="nav-link active" href="/ai-agent/public/dashboard/approvals.php">
                <i class="bi bi-check-circle"></i> Approvals
                <?php if (!empty($pendingFixes)): ?>
                    <span class="badge bg-warning text-dark"><?= count($pendingFixes) ?></span>
                <?php endif; ?>
            </a>
            <a class="nav-link" href="/ai-agent/public/dashboard/workflows.php">
                <i class="bi bi-diagram-3"></i> Workflows
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">
                <i class="bi bi-shield-check"></i> Pending Fix Approvals
            </h1>
            <p class="text-muted">Review and approve AI-generated fixes before they are applied to your codebase</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card pending">
                <div class="card-body">
                    <h5 class="card-title">Pending</h5>
                    <h2 class="mb-0"><?= $stats['pending'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card approved">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2 class="mb-0"><?= $stats['approved'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card rejected">
                <div class="card-body">
                    <h5 class="card-title">Rejected</h5>
                    <h2 class="mb-0"><?= $stats['rejected'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card applied">
                <div class="card-body">
                    <h5 class="card-title">Applied</h5>
                    <h2 class="mb-0"><?= $stats['applied'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Fixes -->
    <?php if (empty($pendingFixes)): ?>
        <div class="alert alert-info">
            <i class="bi bi-check-circle-fill"></i>
            <strong>All clear!</strong> No pending fixes to review at this time.
        </div>
    <?php else: ?>

        <div class="row">
            <?php foreach ($pendingFixes as $fix): ?>
                <div class="col-xl-6 mb-4">
                    <div class="card fix-card">

                        <!-- Card Header -->
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <div>
                                <i class="bi bi-file-code text-primary"></i>
                                <strong><?= htmlspecialchars(basename($fix['file_path'] ?? 'Unknown')) ?></strong>
                                <?php if ($fix['line_number']): ?>
                                    <span class="badge bg-secondary">Line <?= $fix['line_number'] ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning"><?= ucfirst($fix['fix_type']) ?></span>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">

                            <!-- URL & Timestamp -->
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-link-45deg"></i>
                                    <a href="<?= htmlspecialchars($fix['url']) ?>" target="_blank">
                                        <?= htmlspecialchars($fix['url']) ?>
                                    </a>
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    <?= date('M j, Y g:i A', strtotime($fix['created_at'])) ?>
                                </small>
                            </div>

                            <!-- Reason -->
                            <?php if ($fix['reason']): ?>
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Reason:</strong> <?= htmlspecialchars($fix['reason']) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Code Diff -->
                            <?php if ($fix['original_code'] || $fix['fixed_code']): ?>

                                <?php if ($fix['original_code']): ?>
                                    <div class="mb-3">
                                        <strong class="text-danger">
                                            <i class="bi bi-dash-circle"></i> Before:
                                        </strong>
                                        <pre class="code-block code-diff-remove mb-0"><code><?= htmlspecialchars($fix['original_code']) ?></code></pre>
                                    </div>
                                <?php endif; ?>

                                <?php if ($fix['fixed_code']): ?>
                                    <div class="mb-3">
                                        <strong class="text-success">
                                            <i class="bi bi-plus-circle"></i> After:
                                        </strong>
                                        <pre class="code-block code-diff-add mb-0"><code><?= htmlspecialchars($fix['fixed_code']) ?></code></pre>
                                    </div>
                                <?php endif; ?>

                            <?php elseif ($fix['errors_json']): ?>
                                <!-- Show error details if no code diff -->
                                <div class="mb-3">
                                    <strong>Error Details:</strong>
                                    <pre class="code-block mb-0"><code><?= htmlspecialchars(json_encode(json_decode($fix['errors_json']), JSON_PRETTY_PRINT)) ?></code></pre>
                                </div>
                            <?php endif; ?>

                            <!-- Screenshot Preview -->
                            <?php if ($fix['screenshot_path'] && file_exists($fix['screenshot_path'])): ?>
                                <div class="mb-3">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showScreenshot('<?= htmlspecialchars($fix['screenshot_path']) ?>')">
                                        <i class="bi bi-image"></i> View Screenshot
                                    </button>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-fill" onclick="approveFix(<?= $fix['id'] ?>)">
                                    <i class="bi bi-check-circle"></i> Approve & Apply
                                </button>
                                <button class="btn btn-danger flex-fill" onclick="rejectFix(<?= $fix['id'] ?>)">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                                <button class="btn btn-outline-secondary" onclick="showDetails(<?= $fix['id'] ?>)" title="More Details">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>

                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<!-- Screenshot Modal -->
<div class="modal fade" id="screenshotModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Screenshot Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="screenshotImage" src="" alt="Screenshot" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fix Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Approve Fix
async function approveFix(fixId) {
    if (!confirm('⚠️ Are you sure you want to APPLY this fix to the live codebase?\n\nThis will modify the actual files.')) {
        return;
    }

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Applying...';

    try {
        const response = await fetch('/ai-agent/api/approve-fix.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fix_id: fixId,
                action: 'approve'
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('✅ Fix applied successfully!\n\n' + (result.message || ''));
            location.reload();
        } else {
            alert('❌ Error applying fix:\n\n' + (result.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        alert('❌ Network error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Reject Fix
async function rejectFix(fixId) {
    const reason = prompt('Why are you rejecting this fix? (optional)');

    if (reason === null) return; // Cancelled

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Rejecting...';

    try {
        const response = await fetch('/ai-agent/api/approve-fix.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fix_id: fixId,
                action: 'reject',
                reason: reason
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('Fix rejected');
            location.reload();
        } else {
            alert('❌ Error: ' + (result.error || 'Unknown error'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        alert('❌ Network error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Show Screenshot
function showScreenshot(path) {
    document.getElementById('screenshotImage').src = path;
    new bootstrap.Modal(document.getElementById('screenshotModal')).show();
}

// Show Details
async function showDetails(fixId) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const content = document.getElementById('detailsContent');

    content.innerHTML = '<div class="text-center"><div class="spinner-border"></div></div>';
    modal.show();

    try {
        const response = await fetch('/ai-agent/api/get-fix-details.php?id=' + fixId);
        const result = await response.json();

        if (result.success) {
            content.innerHTML = `
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr><th>Fix ID</th><td>${result.data.id}</td></tr>
                        <tr><th>URL</th><td><a href="${result.data.url}" target="_blank">${result.data.url}</a></td></tr>
                        <tr><th>File</th><td>${result.data.file_path || 'N/A'}</td></tr>
                        <tr><th>Line</th><td>${result.data.line_number || 'N/A'}</td></tr>
                        <tr><th>Type</th><td>${result.data.fix_type}</td></tr>
                        <tr><th>Created</th><td>${result.data.created_at}</td></tr>
                        <tr><th>Status</th><td><span class="badge bg-warning">${result.data.status}</span></td></tr>
                    </table>
                </div>
                ${result.data.errors_json ? `
                    <h6>Full Error Data:</h6>
                    <pre class="code-block">${JSON.stringify(JSON.parse(result.data.errors_json), null, 2)}</pre>
                ` : ''}
            `;
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error loading details</div>';
        }
    } catch (error) {
        content.innerHTML = '<div class="alert alert-danger">Network error: ' + error.message + '</div>';
    }
}

// Auto-refresh every 30 seconds if there are pending fixes
<?php if (!empty($pendingFixes)): ?>
setTimeout(() => location.reload(), 30000);
<?php endif; ?>
</script>

</body>
</html>
