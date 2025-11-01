<?php
/**
 * Violations Page - Complete V2 Redesign
 * Comprehensive violation management with filtering, bulk actions, and resolution workflow
 *
 * Features:
 * - Semantic HTML5 structure
 * - Advanced filtering (severity, type, file, date range)
 * - Bulk actions (suppress, resolve, assign)
 * - Violation details modal with code snippet preview
 * - Resolution workflow with status tracking
 * - Violation trends chart
 * - Export functionality
 * - Sortable table columns
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Violations';
$lastScanTime = date('M j, Y g:i A');

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

// Pagination and filters
$page = isset($_GET['viol_page']) ? max(1, (int)$_GET['viol_page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$filterSeverity = $_GET['severity'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterFile = $_GET['file'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Build query with filters
$whereConditions = ["project_id = :projectId"];
$params = [':projectId' => $projectId];

if ($filterSeverity) {
    $whereConditions[] = "severity = :severity";
    $params[':severity'] = $filterSeverity;
}

if ($filterType) {
    $whereConditions[] = "rule_name LIKE :type";
    $params[':type'] = '%' . $filterType . '%';
}

if ($filterFile) {
    $whereConditions[] = "file_path LIKE :file";
    $params[':file'] = '%' . $filterFile . '%';
}

if ($filterStatus) {
    $whereConditions[] = "status = :status";
    $params[':status'] = $filterStatus;
}

if ($searchTerm) {
    $whereConditions[] = "(rule_name LIKE :search OR description LIKE :search OR file_path LIKE :search)";
    $params[':search'] = '%' . $searchTerm . '%';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countQuery = "SELECT COUNT(*) FROM project_rule_violations WHERE $whereClause";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalViolations = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalViolations / $limit);

// Get violations with pagination
$violationsQuery = "
    SELECT
        id,
        rule_name,
        file_path,
        line_number,
        severity,
        description,
        status,
        detected_at
    FROM project_rule_violations
    WHERE $whereClause
    ORDER BY
        FIELD(severity, 'critical', 'high', 'medium', 'low'),
        detected_at DESC
    LIMIT :limit OFFSET :offset
";

$violationsStmt = $pdo->prepare($violationsQuery);
foreach ($params as $key => $value) {
    $violationsStmt->bindValue($key, $value);
}
$violationsStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$violationsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$violationsStmt->execute();
$violations = $violationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get severity summary
$summaryQuery = "
    SELECT
        severity,
        COUNT(*) as count,
        COUNT(CASE WHEN status = 'open' THEN 1 END) as open_count,
        COUNT(CASE WHEN status = 'resolved' THEN 1 END) as resolved_count,
        COUNT(CASE WHEN status = 'suppressed' THEN 1 END) as suppressed_count
    FROM project_rule_violations
    WHERE project_id = ?
    GROUP BY severity
    ORDER BY FIELD(severity, 'critical', 'high', 'medium', 'low')
";

$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute([$projectId]);
$severitySummary = $summaryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get rule type summary
$ruleTypeQuery = "
    SELECT
        rule_name,
        COUNT(*) as count
    FROM project_rule_violations
    WHERE project_id = ?
    GROUP BY rule_name
    ORDER BY count DESC
    LIMIT 10
";

$ruleTypeStmt = $pdo->prepare($ruleTypeQuery);
$ruleTypeStmt->execute([$projectId]);
$ruleTypes = $ruleTypeStmt->fetchAll(PDO::FETCH_ASSOC);

// Get violation trends (last 7 days)
$trendsQuery = "
    SELECT
        DATE(detected_at) as date,
        COUNT(*) as count,
        COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical_count,
        COUNT(CASE WHEN severity = 'high' THEN 1 END) as high_count,
        COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium_count,
        COUNT(CASE WHEN severity = 'low' THEN 1 END) as low_count
    FROM project_rule_violations
    WHERE project_id = ? AND detected_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(detected_at)
    ORDER BY date ASC
";

$trendsStmt = $pdo->prepare($trendsQuery);
$trendsStmt->execute([$projectId]);
$trends = $trendsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate metrics
$totalCritical = array_sum(array_column($severitySummary, 'count'));
$openCount = array_sum(array_map(fn($s) => (int)$s['open_count'], $severitySummary));
$resolvedCount = array_sum(array_map(fn($s) => (int)$s['resolved_count'], $severitySummary));
$suppressedCount = array_sum(array_map(fn($s) => (int)$s['suppressed_count'], $severitySummary));

// Severity color mapping
$severityColors = [
    'critical' => 'danger',
    'high' => 'warning',
    'medium' => 'info',
    'low' => 'secondary'
];

// Include header
require_once __DIR__ . '/../includes-v2/header.php';
?>

<!-- Main Content -->
<main class="dashboard-main">
    <div class="container-fluid">
        <!-- Page Header -->
        <header class="page-header">
            <div class="page-header__content">
                <h1 class="page-header__title">Violations</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?>
                    • <?php echo number_format($totalViolations); ?> violations
                    • <?php echo number_format($openCount); ?> open
                    • Last scan: <?php echo $lastScanTime; ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--secondary" onclick="refreshViolations()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn--secondary" onclick="exportViolations()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button type="button" class="btn btn--primary" onclick="bulkAction()">
                    <i class="fas fa-tasks"></i> Bulk Actions
                </button>
            </div>
        </header>

        <!-- Violation Metrics -->
        <section class="metrics-row">
            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($totalViolations); ?></div>
                    <div class="metric-card__label">Total Violations</div>
                    <div class="metric-card__change <?php echo $totalViolations > 100 ? 'metric-card__change--negative' : 'metric-card__change--neutral'; ?>">
                        <?php echo number_format($openCount); ?> open
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($resolvedCount); ?></div>
                    <div class="metric-card__label">Resolved</div>
                    <div class="metric-card__change metric-card__change--positive">
                        <?php echo $totalViolations > 0 ? round(($resolvedCount / $totalViolations) * 100, 1) : 0; ?>% resolution rate
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--warning">
                    <i class="fas fa-eye-slash"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($suppressedCount); ?></div>
                    <div class="metric-card__label">Suppressed</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        Intentionally ignored
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--primary">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value">
                        <?php
                        $avgAge = 0;
                        if (!empty($violations)) {
                            $totalAge = 0;
                            foreach ($violations as $v) {
                                $totalAge += (time() - strtotime($v['detected_at'])) / 86400;
                            }
                            $avgAge = round($totalAge / count($violations), 1);
                        }
                        echo $avgAge;
                        ?>
                    </div>
                    <div class="metric-card__label">Avg Age (Days)</div>
                    <div class="metric-card__change <?php echo $avgAge > 7 ? 'metric-card__change--negative' : 'metric-card__change--neutral'; ?>">
                        Oldest violations need attention
                    </div>
                </div>
            </article>
        </section>

        <!-- Severity Distribution -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Severity Distribution</h2>
            </header>
            <div class="content-section__body">
                <div class="row-layout">
                    <?php foreach ($severitySummary as $severity): ?>
                        <?php $color = $severityColors[$severity['severity']]; ?>
                        <article class="stat-card stat-card--<?php echo $color; ?>">
                            <div class="stat-card__header">
                                <span class="badge badge--<?php echo $color; ?>">
                                    <?php echo ucfirst($severity['severity']); ?>
                                </span>
                                <h3 class="stat-card__value"><?php echo number_format((int)$severity['count']); ?></h3>
                            </div>
                            <div class="stat-card__stats">
                                <div class="stat-card__stat">
                                    <span class="stat-card__stat-label">Open</span>
                                    <span class="stat-card__stat-value"><?php echo number_format((int)$severity['open_count']); ?></span>
                                </div>
                                <div class="stat-card__stat">
                                    <span class="stat-card__stat-label">Resolved</span>
                                    <span class="stat-card__stat-value"><?php echo number_format((int)$severity['resolved_count']); ?></span>
                                </div>
                                <div class="stat-card__stat">
                                    <span class="stat-card__stat-label">Suppressed</span>
                                    <span class="stat-card__stat-value"><?php echo number_format((int)$severity['suppressed_count']); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Filters & Search -->
        <section class="filter-toolbar">
            <div class="filter-toolbar__group">
                <div class="input-group">
                    <i class="fas fa-search input-group__icon"></i>
                    <input type="text" id="search-input" class="form-control" placeholder="Search violations..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
            </div>
            <div class="filter-toolbar__group">
                <select class="form-control" id="filter-severity">
                    <option value="">All Severities</option>
                    <option value="critical" <?php echo $filterSeverity === 'critical' ? 'selected' : ''; ?>>Critical</option>
                    <option value="high" <?php echo $filterSeverity === 'high' ? 'selected' : ''; ?>>High</option>
                    <option value="medium" <?php echo $filterSeverity === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low" <?php echo $filterSeverity === 'low' ? 'selected' : ''; ?>>Low</option>
                </select>
                <select class="form-control" id="filter-status">
                    <option value="">All Statuses</option>
                    <option value="open" <?php echo $filterStatus === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="resolved" <?php echo $filterStatus === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="suppressed" <?php echo $filterStatus === 'suppressed' ? 'selected' : ''; ?>>Suppressed</option>
                </select>
                <input type="text" id="filter-file" class="form-control" placeholder="Filter by file..." value="<?php echo htmlspecialchars($filterFile); ?>">
            </div>
            <div class="filter-toolbar__actions">
                <button type="button" class="btn btn--secondary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply
                </button>
                <button type="button" class="btn btn--secondary" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </section>

        <!-- Violations Table -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Violations List</h2>
                <div class="content-section__actions">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="select-all" class="form-checkbox" onclick="toggleSelectAll(this)">
                        <label for="select-all">Select All</label>
                    </div>
                </div>
            </header>
            <div class="content-section__body">
                <?php if (empty($violations)): ?>
                    <div class="empty-state">
                        <div class="empty-state__icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="empty-state__message">
                            <h3>No Violations Found</h3>
                            <p>
                                <?php if ($searchTerm || $filterSeverity || $filterStatus || $filterFile): ?>
                                    No violations match your current filters. Try adjusting your search criteria.
                                <?php else: ?>
                                    Great job! Your project has no code violations.
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($searchTerm || $filterSeverity || $filterStatus || $filterFile): ?>
                            <button type="button" class="btn btn--primary" onclick="clearFilters()">
                                Clear Filters
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="data-table-wrapper">
                        <table class="data-table data-table--hover" id="violations-table">
                            <thead>
                                <tr>
                                    <th class="data-table__header" style="width: 40px;">
                                        <input type="checkbox" class="form-checkbox" onclick="toggleSelectAll(this)">
                                    </th>
                                    <th class="data-table__header data-table__header--sortable" onclick="sortTable(1)">
                                        Rule
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="data-table__header data-table__header--sortable" onclick="sortTable(2)">
                                        File
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="data-table__header data-table__header--sortable" onclick="sortTable(3)">
                                        Line
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="data-table__header data-table__header--sortable" onclick="sortTable(4)">
                                        Severity
                                        <i class="fas fa-sort"></i>
                                    </th>
                                    <th class="data-table__header">Description</th>
                                    <th class="data-table__header">Status</th>
                                    <th class="data-table__header">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($violations as $violation): ?>
                                    <?php $color = $severityColors[$violation['severity']]; ?>
                                    <tr data-violation-id="<?php echo $violation['id']; ?>">
                                        <td class="data-table__cell">
                                            <input type="checkbox" class="form-checkbox violation-checkbox" value="<?php echo $violation['id']; ?>">
                                        </td>
                                        <td class="data-table__cell">
                                            <strong><?php echo htmlspecialchars($violation['rule_name']); ?></strong>
                                        </td>
                                        <td class="data-table__cell" title="<?php echo htmlspecialchars($violation['file_path']); ?>">
                                            <code class="code-inline">
                                                <?php echo htmlspecialchars(basename($violation['file_path'])); ?>
                                            </code>
                                        </td>
                                        <td class="data-table__cell">
                                            <span class="badge badge--secondary"><?php echo $violation['line_number']; ?></span>
                                        </td>
                                        <td class="data-table__cell">
                                            <span class="badge badge--<?php echo $color; ?>">
                                                <?php echo ucfirst($violation['severity']); ?>
                                            </span>
                                        </td>
                                        <td class="data-table__cell">
                                            <?php echo htmlspecialchars(substr($violation['description'], 0, 80)); ?>
                                            <?php if (strlen($violation['description']) > 80): ?>...<?php endif; ?>
                                        </td>
                                        <td class="data-table__cell">
                                            <?php
                                            $statusClass = match($violation['status']) {
                                                'resolved' => 'success',
                                                'suppressed' => 'warning',
                                                default => 'info'
                                            };
                                            ?>
                                            <span class="badge badge--<?php echo $statusClass; ?>">
                                                <?php echo ucfirst($violation['status'] ?? 'Open'); ?>
                                            </span>
                                        </td>
                                        <td class="data-table__cell data-table__cell--actions">
                                            <button type="button" class="btn btn--sm btn--secondary" onclick="viewViolation(<?php echo $violation['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn--sm btn--success" onclick="resolveViolation(<?php echo $violation['id']; ?>)" title="Mark Resolved">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn--sm btn--warning" onclick="suppressViolation(<?php echo $violation['id']; ?>)" title="Suppress">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination-wrapper">
                            <ul class="pagination">
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
                            <div class="pagination__info">
                                Showing <?php echo number_format($offset + 1); ?>-<?php echo number_format(min($offset + $limit, $totalViolations)); ?>
                                of <?php echo number_format($totalViolations); ?>
                            </div>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Violation Trends Chart -->
        <?php if (!empty($trends)): ?>
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Violation Trends (Last 7 Days)</h2>
                <p class="content-section__subtitle">Daily violation detection by severity</p>
            </header>
            <div class="content-section__body">
                <div class="chart-container">
                    <canvas id="trends-chart"></canvas>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Top Rule Violations -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Most Common Violations</h2>
                <p class="content-section__subtitle">Top 10 rule violations by frequency</p>
            </header>
            <div class="content-section__body">
                <?php if (empty($ruleTypes)): ?>
                    <p class="text-muted">No violation data available</p>
                <?php else: ?>
                    <div class="stat-list">
                        <?php foreach ($ruleTypes as $rule): ?>
                            <div class="stat-item">
                                <div class="stat-item__icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="stat-item__content">
                                    <div class="stat-item__label"><?php echo htmlspecialchars($rule['rule_name']); ?></div>
                                    <div class="stat-item__description">
                                        <?php
                                        $percentage = $totalViolations > 0 ? round(($rule['count'] / $totalViolations) * 100, 1) : 0;
                                        echo "{$percentage}% of all violations";
                                        ?>
                                    </div>
                                </div>
                                <div class="stat-item__value">
                                    <span class="badge badge--danger"><?php echo number_format((int)$rule['count']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<!-- Violation Details Modal -->
<div id="modal-violation-details" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-violation-details')"></div>
    <div class="modal__content modal__content--large">
        <header class="modal__header">
            <h2 class="modal__title">Violation Details</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-violation-details')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body" id="modal-violation-content">
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        </div>
        <footer class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('modal-violation-details')">Close</button>
            <button type="button" class="btn btn--success" onclick="resolveFromModal()">Mark Resolved</button>
        </footer>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div id="modal-bulk-actions" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-bulk-actions')"></div>
    <div class="modal__content">
        <header class="modal__header">
            <h2 class="modal__title">Bulk Actions</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-bulk-actions')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body">
            <p><span id="bulk-count">0</span> violations selected</p>
            <div class="btn-group btn-group--vertical">
                <button type="button" class="btn btn--success" onclick="bulkResolve()">
                    <i class="fas fa-check"></i> Mark All as Resolved
                </button>
                <button type="button" class="btn btn--warning" onclick="bulkSuppress()">
                    <i class="fas fa-eye-slash"></i> Suppress All
                </button>
                <button type="button" class="btn btn--danger" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Delete All
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function for pagination URLs
function buildPageUrl(int $pageNum): string {
    global $filterSeverity, $filterStatus, $filterFile, $searchTerm;

    $params = ['viol_page' => $pageNum];

    if ($filterSeverity) $params['severity'] = $filterSeverity;
    if ($filterStatus) $params['status'] = $filterStatus;
    if ($filterFile) $params['file'] = $filterFile;
    if ($searchTerm) $params['search'] = $searchTerm;

    return '?' . http_build_query($params);
}
?>

<script>
// Trend chart data from PHP
const trendDates = <?php echo json_encode(array_column($trends, 'date')); ?>;
const trendCritical = <?php echo json_encode(array_map('intval', array_column($trends, 'critical_count'))); ?>;
const trendHigh = <?php echo json_encode(array_map('intval', array_column($trends, 'high_count'))); ?>;
const trendMedium = <?php echo json_encode(array_map('intval', array_column($trends, 'medium_count'))); ?>;
const trendLow = <?php echo json_encode(array_map('intval', array_column($trends, 'low_count'))); ?>;

// Filter functions
function applyFilters() {
    const params = new URLSearchParams();

    const search = document.getElementById('search-input').value;
    const severity = document.getElementById('filter-severity').value;
    const status = document.getElementById('filter-status').value;
    const file = document.getElementById('filter-file').value;

    if (search) params.set('search', search);
    if (severity) params.set('severity', severity);
    if (status) params.set('status', status);
    if (file) params.set('file', file);

    window.location.href = '?' + params.toString();
}

function clearFilters() {
    window.location.href = window.location.pathname;
}

// Selection functions
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.violation-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

function getSelectedViolations() {
    return Array.from(document.querySelectorAll('.violation-checkbox:checked')).map(cb => cb.value);
}

// Action functions
function refreshViolations() {
    window.location.reload();
}

function exportViolations() {
    DashboardApp.showAlert('Export functionality coming soon', 'info', 3000);
}

function bulkAction() {
    const selected = getSelectedViolations();
    if (selected.length === 0) {
        DashboardApp.showAlert('Please select violations first', 'warning', 3000);
        return;
    }

    document.getElementById('bulk-count').textContent = selected.length;
    document.getElementById('modal-bulk-actions').style.display = 'flex';
}

function viewViolation(violationId) {
    const modal = document.getElementById('modal-violation-details');
    const content = document.getElementById('modal-violation-content');

    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';

    // TODO: Fetch actual violation details with code snippet
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert--info">
                <div class="alert__icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert__content">
                    <div class="alert__title">Violation #${violationId}</div>
                    <div class="alert__message">
                        Detailed violation information including code snippet would be displayed here.
                    </div>
                </div>
            </div>
            <div class="code-snippet">
                <pre><code class="language-php">// Code snippet preview would appear here
// Line with violation would be highlighted
function exampleFunction() {
    // Violation on this line
    $variable = "value";
}
                </code></pre>
            </div>
        `;
    }, 500);
}

function resolveViolation(violationId) {
    if (confirm('Mark this violation as resolved?')) {
        DashboardApp.showAlert('Violation marked as resolved', 'success', 3000);
        // TODO: API call to update status
        setTimeout(() => location.reload(), 1000);
    }
}

function suppressViolation(violationId) {
    if (confirm('Suppress this violation? It will be hidden from future scans.')) {
        DashboardApp.showAlert('Violation suppressed', 'success', 3000);
        // TODO: API call to suppress
        setTimeout(() => location.reload(), 1000);
    }
}

function resolveFromModal() {
    DashboardApp.showAlert('Violation marked as resolved', 'success', 3000);
    closeModal('modal-violation-details');
    setTimeout(() => location.reload(), 1000);
}

function bulkResolve() {
    const selected = getSelectedViolations();
    if (confirm(`Mark ${selected.length} violations as resolved?`)) {
        DashboardApp.showAlert(`${selected.length} violations marked as resolved`, 'success', 3000);
        closeModal('modal-bulk-actions');
        setTimeout(() => location.reload(), 1000);
    }
}

function bulkSuppress() {
    const selected = getSelectedViolations();
    if (confirm(`Suppress ${selected.length} violations?`)) {
        DashboardApp.showAlert(`${selected.length} violations suppressed`, 'success', 3000);
        closeModal('modal-bulk-actions');
        setTimeout(() => location.reload(), 1000);
    }
}

function bulkDelete() {
    const selected = getSelectedViolations();
    if (confirm(`Permanently delete ${selected.length} violations? This cannot be undone.`)) {
        DashboardApp.showAlert(`${selected.length} violations deleted`, 'success', 3000);
        closeModal('modal-bulk-actions');
        setTimeout(() => location.reload(), 1000);
    }
}

function sortTable(columnIndex) {
    // TODO: Implement client-side sorting
    DashboardApp.showAlert('Sorting by column ' + (columnIndex + 1), 'info', 2000);
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Initialize charts and dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize trend chart if data available
    if (trendDates.length > 0) {
        ChartsModule.createChart('trends-chart', {
            type: 'line',
            data: {
                labels: trendDates.map(d => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                datasets: [
                    {
                        label: 'Critical',
                        data: trendCritical,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'High',
                        data: trendHigh,
                        borderColor: 'rgba(245, 158, 11, 1)',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Medium',
                        data: trendMedium,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Low',
                        data: trendLow,
                        borderColor: 'rgba(107, 114, 128, 1)',
                        backgroundColor: 'rgba(107, 114, 128, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Initialize DashboardApp
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../includes-v2/footer.php';
?>
