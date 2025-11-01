<?php
/**
 * Scan History Page - PHASE 3 STEP 4
 *
 * View and analyze scan execution history
 * Audit trail for all scans performed
 *
 * @package hdgwrzntwa/dashboard/admin
 * @category Dashboard Page - PHASE 3
 * @created October 31, 2025
 */

declare(strict_types=1);

// Set current page for sidebar highlighting
$_GET['page'] = 'scan-history';
$currentPage = 'scan-history';

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Load project selector component
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/includes/project-selector.php';

// Get current project ID from session (set by project-selector.php)
$projectId = (int)($_SESSION['current_project_id'] ?? $_GET['project_id'] ?? 1);

// Validate project exists and is active
$valQuery = "SELECT id FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
if (!$valStmt->fetch()) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
}

// ============================================================================
// PAGINATION & FILTERING
// ============================================================================

$page = (int)($_GET['page'] ?? 1);
$limit = 25;
$offset = ($page - 1) * $limit;
$status_filter = $_GET['status'] ?? '';

// ============================================================================
// FETCH DATA
// ============================================================================

// Build query with filters
$baseQuery = "FROM scan_history WHERE project_id = ?";
$params = [$projectId];

if ($status_filter) {
    $baseQuery .= " AND status = ?";
    $params[] = $status_filter;
}

// Get total count
$countQuery = "SELECT COUNT(*) $baseQuery";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = ceil($total / $limit);

// Get scan history
$historyQuery = "
    SELECT
        scan_id,
        config_id,
        scan_type,
        files_added,
        files_modified,
        files_deleted,
        status,
        error_message,
        started_at,
        completed_at,
        triggered_by,
        created_at
    $baseQuery
    ORDER BY started_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $limit;
$params[] = $offset;

$historyStmt = $pdo->prepare($historyQuery);
$historyStmt->execute($params);
$history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

// Get scan summary statistics
$summaryQuery = "
    SELECT
        COUNT(*) as total_scans,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_scans,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_scans,
        SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_scans,
        SUM(files_added) as total_files_added,
        SUM(files_modified) as total_files_modified,
        SUM(files_deleted) as total_files_deleted,
        AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration
    FROM scan_history
    WHERE project_id = ?
";

$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute([$projectId]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC);

// Get status breakdown
$statusQuery = "
    SELECT
        status,
        COUNT(*) as count
    FROM scan_history
    WHERE project_id = ?
    GROUP BY status
";

$statusStmt = $pdo->prepare($statusQuery);
$statusStmt->execute([$projectId]);
$statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent scan summary (last 7 days)
$recentQuery = "
    SELECT
        DATE(started_at) as scan_date,
        COUNT(*) as scan_count,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM scan_history
    WHERE project_id = ? AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(started_at)
    ORDER BY scan_date DESC
";

$recentStmt = $pdo->prepare($recentQuery);
$recentStmt->execute([$projectId]);
$recentScans = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Get most common scan types
$typesQuery = "
    SELECT
        scan_type,
        COUNT(*) as count
    FROM scan_history
    WHERE project_id = ?
    GROUP BY scan_type
    ORDER BY count DESC
";

$typesStmt = $pdo->prepare($typesQuery);
$typesStmt->execute([$projectId]);
$scanTypes = $typesStmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<div class="dashboard-container">
    <!-- Project Selector Component (PHASE 3) -->
    <?php echo renderProjectSelector($pdo, $_SESSION['current_unit_id'], $projectId); ?>

    <!-- Page Header -->
    <div class="page-header">
        <h1>Scan History</h1>
        <p class="text-muted">View scan execution history and audit trail</p>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $summary['total_scans']; ?></h3>
                    <p class="text-muted mb-0">Total Scans</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success"><?php echo $summary['completed_scans']; ?></h3>
                    <p class="text-muted mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger"><?php echo $summary['failed_scans']; ?></h3>
                    <p class="text-muted mb-0">Failed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info"><?php echo round((float)($summary['avg_duration'] ?? 0)); ?>s</h3>
                    <p class="text-muted mb-0">Avg Duration</p>
                </div>
            </div>
        </div>
    </div>

    <!-- File Changes Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">+<?php echo $summary['total_files_added']; ?></h3>
                    <p class="text-muted mb-0">Files Added</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">~<?php echo $summary['total_files_modified']; ?></h3>
                    <p class="text-muted mb-0">Files Modified</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-danger">-<?php echo $summary['total_files_deleted']; ?></h3>
                    <p class="text-muted mb-0">Files Deleted</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <strong>Filter by Status:</strong>
                    <a href="?page=scan-history" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-outline-primary'; ?>">All (<?php echo $total; ?>)</a>
                    <?php foreach ($statusCounts as $status => $count): ?>
                        <a href="?page=scan-history&status=<?php echo htmlspecialchars($status); ?>" class="btn btn-sm <?php echo $status_filter === $status ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars(ucfirst($status)); ?> (<?php echo $count; ?>)
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan History Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Scans</h5>
        </div>
        <div class="card-body">
            <?php if (empty($history)): ?>
                <p class="text-muted text-center py-4">No scan history available for this project.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Scan ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Files Changed</th>
                                <th>Duration</th>
                                <th>Started</th>
                                <th>Triggered By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $scan): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($scan['scan_id']); ?></code></td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($scan['scan_type'])); ?></span></td>
                                <td>
                                    <?php
                                    $statusColor = match($scan['status']) {
                                        'completed' => 'success',
                                        'running' => 'warning',
                                        'failed' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $statusColor; ?>">
                                        <?php echo htmlspecialchars(ucfirst($scan['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <span class="text-success">+<?php echo $scan['files_added']; ?></span>
                                        <span class="text-warning">~<?php echo $scan['files_modified']; ?></span>
                                        <span class="text-danger">-<?php echo $scan['files_deleted']; ?></span>
                                    </small>
                                </td>
                                <td>
                                    <?php
                                    if ($scan['started_at'] && $scan['completed_at']) {
                                        $duration = strtotime($scan['completed_at']) - strtotime($scan['started_at']);
                                        echo $duration . 's';
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M d, Y H:i', strtotime($scan['started_at'])); ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo htmlspecialchars($scan['triggered_by'] ?? 'System'); ?></small>
                                </td>
                            </tr>
                            <?php if ($scan['status'] === 'failed' && $scan['error_message']): ?>
                            <tr class="table-danger">
                                <td colspan="7">
                                    <small><strong>Error:</strong> <?php echo htmlspecialchars($scan['error_message']); ?></small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=scan-history&p=1">First</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=scan-history&p=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=scan-history&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=scan-history&p=<?php echo $page + 1; ?>">Next</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=scan-history&p=<?php echo $totalPages; ?>">Last</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
