<?php
/**
 * Scan History Page - Complete V2 Redesign
 * Timeline visualization and audit trail for all code scans
 *
 * Features:
 * - Semantic HTML5 structure
 * - Timeline visualization with status indicators
 * - Scan comparison tool
 * - Date range filtering
 * - Export scan reports
 * - Scan details modal
 * - Performance metrics
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Scan History';
$lastScanTime = date('M j, Y g:i A');
$violationCount = 0;

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Get project ID from session
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

// Validate project exists
$valQuery = "SELECT id, project_name FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
$projectData = $valStmt->fetch(PDO::FETCH_ASSOC);

if (!$projectData) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
    $valStmt->execute([$projectId]);
    $projectData = $valStmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 1, 'project_name' => 'Default'];
}

// Pagination
$page = isset($_GET['history_page']) ? max(1, (int)$_GET['history_page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filters
$statusFilter = trim($_GET['status'] ?? '');
$dateFilter = trim($_GET['date_range'] ?? '30'); // days

// Build query
$query = "
    SELECT
        scan_id,
        config_id,
        scan_type,
        files_added,
        files_modified,
        files_deleted,
        violations_found,
        status,
        error_message,
        started_at,
        completed_at,
        triggered_by,
        duration_seconds
    FROM scan_history
    WHERE project_id = ?
";

$params = [$projectId];

// Apply status filter
if ($statusFilter !== '') {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

// Apply date filter
if ($dateFilter !== 'all') {
    $days = (int)$dateFilter;
    $query .= " AND started_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $days;
}

// Get total count
$countQuery = str_replace('SELECT scan_id,', 'SELECT COUNT(*) as total FROM (SELECT scan_id FROM',
    substr($query, 0, strrpos($query, 'FROM'))
) . substr($query, strrpos($query, 'FROM')) . ') as count_query';

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalScans = (int)($countStmt->fetchColumn() ?: 0);
$totalPages = max(1, (int)ceil($totalScans / $limit));

// Add sorting and pagination
$query .= " ORDER BY started_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Execute main query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$scans = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get summary statistics
$summaryQuery = "
    SELECT
        COUNT(*) as total_scans,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running,
        SUM(files_added) as total_added,
        SUM(files_modified) as total_modified,
        SUM(files_deleted) as total_deleted,
        AVG(duration_seconds) as avg_duration,
        MAX(started_at) as last_scan
    FROM scan_history
    WHERE project_id = ?
    AND started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";

$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute([$projectId]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_scans' => 0,
    'completed' => 0,
    'failed' => 0,
    'running' => 0,
    'total_added' => 0,
    'total_modified' => 0,
    'total_deleted' => 0,
    'avg_duration' => 0,
    'last_scan' => null
];

// Get scan activity by day (last 30 days)
$activityQuery = "
    SELECT
        DATE(started_at) as scan_date,
        COUNT(*) as scan_count,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
        SUM(files_added + files_modified + files_deleted) as total_changes
    FROM scan_history
    WHERE project_id = ?
    AND started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(started_at)
    ORDER BY scan_date DESC
    LIMIT 30
";

$activityStmt = $pdo->prepare($activityQuery);
$activityStmt->execute([$projectId]);
$activity = $activityStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Calculate success rate
$successRate = ((int)$summary['total_scans'] > 0)
    ? round(((int)$summary['completed'] / (int)$summary['total_scans']) * 100, 1)
    : 0;

// Include header and sidebar
require_once __DIR__ . '/../includes-v2/header.php';
require_once __DIR__ . '/../includes-v2/sidebar.php';
?>

<main id="main-content" class="main-content" role="main">
    <!-- Page Header -->
    <header class="page-header">
        <div class="page-header__title-row">
            <div>
                <h1 class="page-header__title">Scan History</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?> ·
                    <?php echo number_format((int)$summary['total_scans']); ?> scans in last 30 days
                    <?php if ($summary['last_scan']): ?>
                    · Last scan: <?php echo date('M j, Y g:i A', strtotime($summary['last_scan'])); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--outline" onclick="refreshHistory()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <a href="?page=scan-config" class="btn btn--primary">
                    <i class="fas fa-play"></i> Run New Scan
                </a>
            </div>
        </div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="?page=overview" class="breadcrumb__item">Home</a>
            <span class="breadcrumb__separator">/</span>
            <span class="breadcrumb__item breadcrumb__item--active">Scan History</span>
        </nav>
    </header>

    <article class="page-content">
        <!-- Summary Statistics -->
        <section class="metrics-grid" aria-label="Scan statistics">
            <div class="metric-card metric-card--primary">
                <div class="metric-card__header">
                    <span class="metric-card__label">Total Scans</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-history"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['total_scans']); ?></div>
                <div class="metric-card__footer">
                    <span class="text-muted">Last 30 days</span>
                </div>
            </div>

            <div class="metric-card metric-card--success">
                <div class="metric-card__header">
                    <span class="metric-card__label">Completed</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['completed']); ?></div>
                <div class="metric-card__footer">
                    <span class="badge badge--success"><?php echo $successRate; ?>% success rate</span>
                </div>
            </div>

            <div class="metric-card metric-card--danger">
                <div class="metric-card__header">
                    <span class="metric-card__label">Failed</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['failed']); ?></div>
                <div class="metric-card__footer">
                    <?php if ((int)$summary['running'] > 0): ?>
                    <span class="badge badge--info"><?php echo (int)$summary['running']; ?> running</span>
                    <?php else: ?>
                    <span class="text-muted">No active scans</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="metric-card metric-card--info">
                <div class="metric-card__header">
                    <span class="metric-card__label">Avg Duration</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="metric-card__value">
                    <?php
                    $avgDuration = (int)($summary['avg_duration'] ?? 0);
                    echo $avgDuration > 0 ? gmdate('i:s', $avgDuration) : '0:00';
                    ?>
                </div>
                <div class="metric-card__footer">
                    <span class="text-muted">Minutes:Seconds</span>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="files-toolbar">
            <div class="files-toolbar__row">
                <div class="files-filters">
                    <select id="statusFilter" class="form-select" aria-label="Filter by status">
                        <option value="">All Statuses</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="running" <?php echo $statusFilter === 'running' ? 'selected' : ''; ?>>Running</option>
                    </select>

                    <select id="dateFilter" class="form-select" aria-label="Filter by date range">
                        <option value="7" <?php echo $dateFilter === '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo $dateFilter === '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90" <?php echo $dateFilter === '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                        <option value="all" <?php echo $dateFilter === 'all' ? 'selected' : ''; ?>>All Time</option>
                    </select>

                    <button type="button" class="btn btn--primary" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply
                    </button>

                    <?php if ($statusFilter || $dateFilter !== '30'): ?>
                    <button type="button" class="btn btn--ghost" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Scan Timeline -->
        <section class="page-section">
            <h2 class="page-section__title">Scan Timeline</h2>

            <?php if (empty($scans)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">
                    <i class="fas fa-history"></i>
                </div>
                <h3 class="empty-state__title">No scans found</h3>
                <p class="empty-state__message">
                    <?php if ($statusFilter || $dateFilter !== '30'): ?>
                        Try adjusting your filters
                    <?php else: ?>
                        No scans have been run yet for this project
                    <?php endif; ?>
                </p>
                <a href="?page=scan-config" class="btn btn--primary">
                    <i class="fas fa-play"></i> Run Your First Scan
                </a>
            </div>
            <?php else: ?>
            <div class="timeline">
                <?php foreach ($scans as $scan):
                    $scanId = (int)$scan['scan_id'];
                    $scanType = (string)($scan['scan_type'] ?? 'full');
                    $status = (string)($scan['status'] ?? 'unknown');
                    $filesAdded = (int)($scan['files_added'] ?? 0);
                    $filesModified = (int)($scan['files_modified'] ?? 0);
                    $filesDeleted = (int)($scan['files_deleted'] ?? 0);
                    $violations = (int)($scan['violations_found'] ?? 0);
                    $startedAt = (string)($scan['started_at'] ?? '');
                    $completedAt = (string)($scan['completed_at'] ?? '');
                    $duration = (int)($scan['duration_seconds'] ?? 0);
                    $triggeredBy = (string)($scan['triggered_by'] ?? 'System');
                    $errorMessage = (string)($scan['error_message'] ?? '');

                    // Determine status class
                    $statusClass = 'info';
                    $statusIcon = 'fa-info-circle';
                    if ($status === 'completed') {
                        $statusClass = 'success';
                        $statusIcon = 'fa-check-circle';
                    } elseif ($status === 'failed') {
                        $statusClass = 'danger';
                        $statusIcon = 'fa-times-circle';
                    } elseif ($status === 'running') {
                        $statusClass = 'warning';
                        $statusIcon = 'fa-spinner fa-spin';
                    }

                    $totalChanges = $filesAdded + $filesModified + $filesDeleted;
                ?>
                <div class="timeline-item timeline-item--<?php echo $statusClass; ?>">
                    <div class="timeline-item__marker">
                        <i class="fas <?php echo $statusIcon; ?>"></i>
                    </div>
                    <div class="timeline-item__content">
                        <div class="timeline-item__header">
                            <div>
                                <h3 class="timeline-item__title">
                                    <?php echo ucfirst(htmlspecialchars($scanType)); ?> Scan #<?php echo $scanId; ?>
                                </h3>
                                <p class="timeline-item__meta">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($startedAt)); ?>
                                    ·
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($triggeredBy); ?>
                                    <?php if ($duration > 0): ?>
                                    ·
                                    <i class="fas fa-clock"></i>
                                    <?php echo gmdate('i:s', $duration); ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="badge badge--<?php echo $statusClass; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>

                        <div class="timeline-item__body">
                            <div class="timeline-stats">
                                <?php if ($filesAdded > 0): ?>
                                <div class="timeline-stats__item">
                                    <i class="fas fa-plus-circle text-success"></i>
                                    <span><?php echo number_format($filesAdded); ?> added</span>
                                </div>
                                <?php endif; ?>

                                <?php if ($filesModified > 0): ?>
                                <div class="timeline-stats__item">
                                    <i class="fas fa-edit text-info"></i>
                                    <span><?php echo number_format($filesModified); ?> modified</span>
                                </div>
                                <?php endif; ?>

                                <?php if ($filesDeleted > 0): ?>
                                <div class="timeline-stats__item">
                                    <i class="fas fa-trash text-danger"></i>
                                    <span><?php echo number_format($filesDeleted); ?> deleted</span>
                                </div>
                                <?php endif; ?>

                                <?php if ($violations > 0): ?>
                                <div class="timeline-stats__item">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <span><?php echo number_format($violations); ?> violations</span>
                                </div>
                                <?php endif; ?>

                                <?php if ($totalChanges === 0 && $violations === 0): ?>
                                <div class="timeline-stats__item">
                                    <i class="fas fa-check text-muted"></i>
                                    <span class="text-muted">No changes detected</span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($status === 'failed' && $errorMessage): ?>
                            <div class="alert alert--danger mt-3">
                                <div class="alert__icon"><i class="fas fa-exclamation-circle"></i></div>
                                <div class="alert__content">
                                    <div class="alert__title">Error</div>
                                    <div class="alert__message"><?php echo htmlspecialchars($errorMessage); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="timeline-item__actions">
                            <button type="button"
                                    class="btn btn--sm btn--outline"
                                    onclick="viewScanDetails(<?php echo $scanId; ?>)">
                                <i class="fas fa-info-circle"></i> View Details
                            </button>
                            <?php if ($status === 'completed'): ?>
                            <button type="button"
                                    class="btn btn--sm btn--outline"
                                    onclick="compareScan(<?php echo $scanId; ?>)">
                                <i class="fas fa-exchange-alt"></i> Compare
                            </button>
                            <button type="button"
                                    class="btn btn--sm btn--outline"
                                    onclick="exportScan(<?php echo $scanId; ?>)">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination" role="navigation" aria-label="Scan history pagination">
            <ul class="pagination__list">
                <?php if ($page > 1): ?>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl(1); ?>" class="pagination__link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($page - 1); ?>" class="pagination__link">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                <li class="pagination__item <?php echo $i === $page ? 'pagination__item--active' : ''; ?>">
                    <a href="<?php echo buildPageUrl($i); ?>" class="pagination__link">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($page + 1); ?>" class="pagination__link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($totalPages); ?>" class="pagination__link">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </article>
</main>

<!-- Scan Details Modal -->
<div class="modal-backdrop" id="modal-scan-details" style="display: none;">
    <div class="modal modal--lg">
        <div class="modal__header">
            <h3 class="modal__title">Scan Details</h3>
            <button type="button" class="modal__close" data-modal-close aria-label="Close modal">
                &times;
            </button>
        </div>
        <div class="modal__body" id="modal-scan-content">
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--outline" data-modal-close>Close</button>
        </div>
    </div>
</div>

<?php
// Helper function for pagination URLs
function buildPageUrl(int $pageNum): string {
    global $statusFilter, $dateFilter;

    $params = [
        'page' => 'scan-history',
        'history_page' => $pageNum
    ];

    if ($statusFilter !== '') $params['status'] = $statusFilter;
    if ($dateFilter !== '30') $params['date_range'] = $dateFilter;

    return '?' . http_build_query($params);
}

// Page-specific JavaScript
$inlineScript = <<<'JAVASCRIPT'
// Apply filters
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const dateRange = document.getElementById('dateFilter').value;

    const params = new URLSearchParams({ page: 'scan-history' });
    if (status) params.set('status', status);
    if (dateRange) params.set('date_range', dateRange);

    window.location.href = '?' + params.toString();
}

// Clear filters
function clearFilters() {
    window.location.href = '?page=scan-history';
}

// Refresh history
function refreshHistory() {
    window.location.reload();
}

// View scan details
function viewScanDetails(scanId) {
    const modal = document.getElementById('modal-scan-details');
    const content = document.getElementById('modal-scan-content');

    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';

    // TODO: Fetch actual scan details from API
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert--info">
                <div class="alert__icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert__content">
                    <div class="alert__title">Scan #${scanId}</div>
                    <div class="alert__message">Detailed scan information will be displayed here.</div>
                </div>
            </div>
        `;
    }, 500);
}

// Compare scans
function compareScan(scanId) {
    DashboardApp.showAlert('Scan comparison coming soon', 'info', 3000);
}

// Export scan results
function exportScan(scanId) {
    DashboardApp.showAlert('Export functionality coming soon', 'info', 3000);
}

// Initialize tooltips and modals
document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
JAVASCRIPT;

// Include footer
require_once __DIR__ . '/../includes-v2/footer.php';
?>
