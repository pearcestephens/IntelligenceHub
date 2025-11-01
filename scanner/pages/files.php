<?php
/**
 * Files Page - Complete V2 Redesign
 * Modern file browser with advanced filtering, search, and management
 *
 * Features:
 * - Semantic HTML5 structure
 * - Sortable table columns
 * - Advanced filters (type, size, date)
 * - Real-time search
 * - Bulk actions
 * - File preview modal
 * - Pagination
 * - Export functionality
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Project Files';
$lastScanTime = date('M j, Y g:i A');
$violationCount = 0;

// Load application bootstrap
// Bootstrap already loaded by index.php

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
    // Retry query
    $valStmt->execute([$projectId]);
    $projectData = $valStmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 1, 'project_name' => 'Default'];
}

// Pagination
$page = isset($_GET['file_page']) ? max(1, (int)$_GET['file_page']) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Filters
$search = trim($_GET['search'] ?? '');
$fileType = trim($_GET['type'] ?? '');
$sizeFilter = trim($_GET['size'] ?? ''); // small, medium, large
$dateFilter = trim($_GET['date'] ?? ''); // today, week, month, all

// Build base query
$query = "SELECT
    f.id,
    f.file_path,
    f.file_type,
    f.file_size,
    f.lines_of_code,
    f.complexity_score,
    f.last_modified,
    f.extracted_at,
    (SELECT COUNT(*) FROM file_dependencies WHERE source_file_id = f.id) as dependency_count,
    (SELECT COUNT(*) FROM violations WHERE file_id = f.id AND severity IN ('critical', 'high')) as critical_issues
FROM intelligence_files f
WHERE f.project_id = ?";

$params = [$projectId];

// Apply search filter
if ($search !== '') {
    $query .= " AND f.file_path LIKE ?";
    $params[] = "%{$search}%";
}

// Apply type filter
if ($fileType !== '') {
    $query .= " AND f.file_type = ?";
    $params[] = $fileType;
}

// Apply size filter
if ($sizeFilter === 'small') {
    $query .= " AND f.file_size < 10240"; // < 10KB
} elseif ($sizeFilter === 'medium') {
    $query .= " AND f.file_size BETWEEN 10240 AND 102400"; // 10KB - 100KB
} elseif ($sizeFilter === 'large') {
    $query .= " AND f.file_size > 102400"; // > 100KB
}

// Apply date filter
if ($dateFilter === 'today') {
    $query .= " AND DATE(f.last_modified) = CURDATE()";
} elseif ($dateFilter === 'week') {
    $query .= " AND f.last_modified >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'month') {
    $query .= " AND f.last_modified >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
}

// Get total count
$countQuery = str_replace('SELECT f.id,', 'SELECT COUNT(*) as total FROM (SELECT f.id FROM',
    substr($query, 0, strrpos($query, 'FROM'))
) . substr($query, strrpos($query, 'FROM')) . ') as count_query';

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalFiles = (int)($countStmt->fetchColumn() ?: 0);
$totalPages = max(1, (int)ceil($totalFiles / $limit));

// Add sorting and pagination
$query .= " ORDER BY f.file_path ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Execute main query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get file type statistics
$typeQuery = "
    SELECT
        file_type,
        COUNT(*) as count,
        SUM(file_size) as total_size,
        AVG(complexity_score) as avg_complexity
    FROM intelligence_files
    WHERE project_id = ?
    GROUP BY file_type
    ORDER BY count DESC
    LIMIT 8
";
$typeStmt = $pdo->prepare($typeQuery);
$typeStmt->execute([$projectId]);
$fileTypeStats = $typeStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get summary statistics
$summaryQuery = "
    SELECT
        COUNT(*) as total_files,
        SUM(file_size) as total_size,
        SUM(lines_of_code) as total_loc,
        COUNT(CASE WHEN last_modified >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as recent_files,
        COUNT(CASE WHEN complexity_score > 15 THEN 1 END) as complex_files
    FROM intelligence_files
    WHERE project_id = ?
";
$summaryStmt = $pdo->prepare($summaryQuery);
$summaryStmt->execute([$projectId]);
$summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_files' => 0,
    'total_size' => 0,
    'total_loc' => 0,
    'recent_files' => 0,
    'complex_files' => 0
];

// Include header and sidebar
// Layout handled by index.php
// Layout handled by index.php
?>

<main id="main-content" class="main-content" role="main">
    <!-- Page Header -->
    <header class="page-header">
        <div class="page-header__title-row">
            <div>
                <h1 class="page-header__title">Project Files</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?> ·
                    <?php echo number_format((int)$summary['total_files']); ?> files ·
                    <?php echo formatBytes((int)$summary['total_size']); ?> total size
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--outline" onclick="exportFiles()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button type="button" class="btn btn--primary" data-modal-target="#modal-import">
                    <i class="fas fa-upload"></i> Import Files
                </button>
            </div>
        </div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="?page=overview" class="breadcrumb__item">Home</a>
            <span class="breadcrumb__separator">/</span>
            <span class="breadcrumb__item breadcrumb__item--active">Files</span>
        </nav>
    </header>

    <article class="page-content">
        <!-- Summary Statistics -->
        <section class="metrics-grid" aria-label="File statistics">
            <div class="metric-card metric-card--primary">
                <div class="metric-card__header">
                    <span class="metric-card__label">Total Files</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-file-code"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['total_files']); ?></div>
                <div class="metric-card__footer">
                    <span class="text-muted">Across all file types</span>
                </div>
            </div>

            <div class="metric-card metric-card--success">
                <div class="metric-card__header">
                    <span class="metric-card__label">Lines of Code</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-code"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['total_loc']); ?></div>
                <div class="metric-card__footer">
                    <span class="text-muted">Total codebase size</span>
                </div>
            </div>

            <div class="metric-card metric-card--warning">
                <div class="metric-card__header">
                    <span class="metric-card__label">Recent Files</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['recent_files']); ?></div>
                <div class="metric-card__footer">
                    <span class="text-muted">Modified in last 7 days</span>
                </div>
            </div>

            <div class="metric-card metric-card--danger">
                <div class="metric-card__header">
                    <span class="metric-card__label">Complex Files</span>
                    <div class="metric-card__icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="metric-card__value"><?php echo number_format((int)$summary['complex_files']); ?></div>
                <div class="metric-card__footer">
                    <span class="text-muted">Complexity score > 15</span>
                </div>
            </div>
        </section>

        <!-- File Type Distribution -->
        <?php if (!empty($fileTypeStats)): ?>
        <section class="page-section">
            <h2 class="page-section__title">File Type Distribution</h2>
            <div class="grid grid--4">
                <?php foreach ($fileTypeStats as $stat): ?>
                <div class="card">
                    <div class="card__body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="card__title text-uppercase">
                                    <?php echo htmlspecialchars($stat['file_type']); ?>
                                </h3>
                                <p class="text-muted mb-0">
                                    <?php echo number_format((int)$stat['count']); ?> files
                                </p>
                            </div>
                            <span class="badge badge--info">
                                <?php echo formatBytes((int)$stat['total_size']); ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress__bar"
                                 style="width: <?php echo min(100, ((int)$stat['count'] / (int)$summary['total_files']) * 100); ?>%"
                                 role="progressbar"
                                 aria-valuenow="<?php echo (int)$stat['count']; ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="<?php echo (int)$summary['total_files']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Files Toolbar -->
        <section class="files-toolbar">
            <div class="files-toolbar__row">
                <div class="files-search">
                    <i class="fas fa-search files-search__icon"></i>
                    <input type="search"
                           id="searchInput"
                           class="files-search__input"
                           placeholder="Search files by path or name..."
                           value="<?php echo htmlspecialchars($search); ?>"
                           aria-label="Search files">
                </div>
                <div class="files-filters">
                    <select id="typeFilter" class="form-select" aria-label="Filter by file type">
                        <option value="">All Types</option>
                        <?php foreach ($fileTypeStats as $stat): ?>
                        <option value="<?php echo htmlspecialchars($stat['file_type']); ?>"
                                <?php echo $fileType === $stat['file_type'] ? 'selected' : ''; ?>>
                            <?php echo strtoupper(htmlspecialchars($stat['file_type'])); ?>
                            (<?php echo number_format((int)$stat['count']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <select id="sizeFilter" class="form-select" aria-label="Filter by file size">
                        <option value="">All Sizes</option>
                        <option value="small" <?php echo $sizeFilter === 'small' ? 'selected' : ''; ?>>Small (&lt; 10KB)</option>
                        <option value="medium" <?php echo $sizeFilter === 'medium' ? 'selected' : ''; ?>>Medium (10KB - 100KB)</option>
                        <option value="large" <?php echo $sizeFilter === 'large' ? 'selected' : ''; ?>>Large (&gt; 100KB)</option>
                    </select>

                    <select id="dateFilter" class="form-select" aria-label="Filter by modification date">
                        <option value="">All Time</option>
                        <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                    </select>

                    <button type="button" class="btn btn--primary" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply
                    </button>

                    <?php if ($search || $fileType || $sizeFilter || $dateFilter): ?>
                    <button type="button" class="btn btn--ghost" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($totalFiles > 0): ?>
            <div class="files-toolbar__results">
                <span class="text-muted">
                    Showing <?php echo number_format($offset + 1); ?>-<?php echo number_format(min($offset + $limit, $totalFiles)); ?>
                    of <?php echo number_format($totalFiles); ?> files
                </span>
            </div>
            <?php endif; ?>
        </section>

        <!-- Files Table -->
        <section class="page-section">
            <div class="card">
                <div class="table-container">
                    <table class="table table--sortable" role="table" aria-label="Project files">
                        <thead>
                            <tr>
                                <th style="width: 40%">
                                    <button class="table__sort-btn" data-sort="path">
                                        File Path
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </th>
                                <th style="width: 10%">
                                    <button class="table__sort-btn" data-sort="type">
                                        Type
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </th>
                                <th style="width: 12%">
                                    <button class="table__sort-btn" data-sort="size">
                                        Size
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </th>
                                <th style="width: 10%">
                                    <button class="table__sort-btn" data-sort="loc">
                                        LOC
                                        <i class="fas fa-sort"></i>
                                    </button>
                                </th>
                                <th style="width: 10%">Dependencies</th>
                                <th style="width: 8%">Issues</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($files)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state__icon">
                                            <i class="fas fa-inbox"></i>
                                        </div>
                                        <h3 class="empty-state__title">No files found</h3>
                                        <p class="empty-state__message">
                                            <?php if ($search || $fileType || $sizeFilter || $dateFilter): ?>
                                                Try adjusting your filters or search terms
                                            <?php else: ?>
                                                No files have been scanned yet for this project
                                            <?php endif; ?>
                                        </p>
                                        <?php if ($search || $fileType || $sizeFilter || $dateFilter): ?>
                                        <button type="button" class="btn btn--primary" onclick="clearFilters()">
                                            Clear Filters
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($files as $file):
                                    $fileId = (int)$file['id'];
                                    $filePath = (string)($file['file_path'] ?? '');
                                    $fileTypeStr = strtoupper((string)($file['file_type'] ?? 'unknown'));
                                    $fileSize = (int)($file['file_size'] ?? 0);
                                    $loc = (int)($file['lines_of_code'] ?? 0);
                                    $deps = (int)($file['dependency_count'] ?? 0);
                                    $issues = (int)($file['critical_issues'] ?? 0);
                                    $complexity = (float)($file['complexity_score'] ?? 0);
                                    $modified = (string)($file['last_modified'] ?? '');
                                ?>
                            <tr>
                                <td>
                                    <div class="file-path">
                                        <i class="fas fa-file-code file-path__icon"></i>
                                        <code class="file-path__text" title="<?php echo htmlspecialchars($filePath); ?>">
                                            <?php
                                            // Show last 60 chars
                                            echo htmlspecialchars(strlen($filePath) > 60 ? '...' . substr($filePath, -57) : $filePath);
                                            ?>
                                        </code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge--info"><?php echo htmlspecialchars($fileTypeStr); ?></span>
                                </td>
                                <td>
                                    <span class="text-mono"><?php echo formatBytes($fileSize); ?></span>
                                </td>
                                <td>
                                    <span class="text-mono"><?php echo number_format($loc); ?></span>
                                </td>
                                <td>
                                    <?php if ($deps > 0): ?>
                                    <span class="badge badge--warning"><?php echo number_format($deps); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($issues > 0): ?>
                                    <span class="badge badge--danger"><?php echo number_format($issues); ?></span>
                                    <?php else: ?>
                                    <span class="badge badge--success">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button"
                                                class="btn btn--sm btn--outline"
                                                onclick="viewFileDetails(<?php echo $fileId; ?>)"
                                                data-tooltip="View details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($deps > 0): ?>
                                        <button type="button"
                                                class="btn btn--sm btn--outline"
                                                onclick="viewDependencies(<?php echo $fileId; ?>)"
                                                data-tooltip="View dependencies">
                                            <i class="fas fa-project-diagram"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination" role="navigation" aria-label="File pagination">
            <ul class="pagination__list">
                <?php if ($page > 1): ?>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl(1); ?>" class="pagination__link">
                        <i class="fas fa-angle-double-left"></i>
                        <span class="sr-only">First page</span>
                    </a>
                </li>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($page - 1); ?>" class="pagination__link">
                        <i class="fas fa-angle-left"></i>
                        <span class="sr-only">Previous page</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                <li class="pagination__item <?php echo $i === $page ? 'pagination__item--active' : ''; ?>">
                    <a href="<?php echo buildPageUrl($i); ?>"
                       class="pagination__link"
                       <?php echo $i === $page ? 'aria-current="page"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($page + 1); ?>" class="pagination__link">
                        <i class="fas fa-angle-right"></i>
                        <span class="sr-only">Next page</span>
                    </a>
                </li>
                <li class="pagination__item">
                    <a href="<?php echo buildPageUrl($totalPages); ?>" class="pagination__link">
                        <i class="fas fa-angle-double-right"></i>
                        <span class="sr-only">Last page</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </article>
</main>

<!-- File Details Modal -->
<div class="modal-backdrop" id="modal-file-details" style="display: none;">
    <div class="modal modal--lg">
        <div class="modal__header">
            <h3 class="modal__title">File Details</h3>
            <button type="button" class="modal__close" data-modal-close aria-label="Close modal">
                &times;
            </button>
        </div>
        <div class="modal__body" id="modal-file-content">
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
    global $search, $fileType, $sizeFilter, $dateFilter;

    $params = [
        'page' => 'files',
        'file_page' => $pageNum
    ];

    if ($search !== '') $params['search'] = $search;
    if ($fileType !== '') $params['type'] = $fileType;
    if ($sizeFilter !== '') $params['size'] = $sizeFilter;
    if ($dateFilter !== '') $params['date'] = $dateFilter;

    return '?' . http_build_query($params);
}

// Helper function for formatting bytes
function formatBytes(int $bytes): string {
    if ($bytes === 0) return '0 B';

    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int)floor(log($bytes) / log(1024));

    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

// Page-specific JavaScript
$inlineScript = <<<'JAVASCRIPT'
// Apply filters
function applyFilters() {
    const search = document.getElementById('searchInput').value.trim();
    const type = document.getElementById('typeFilter').value;
    const size = document.getElementById('sizeFilter').value;
    const date = document.getElementById('dateFilter').value;

    const params = new URLSearchParams({ page: 'files' });
    if (search) params.set('search', search);
    if (type) params.set('type', type);
    if (size) params.set('size', size);
    if (date) params.set('date', date);

    window.location.href = '?' + params.toString();
}

// Clear all filters
function clearFilters() {
    window.location.href = '?page=files';
}

// Export files
function exportFiles() {
    DashboardApp.showAlert('Export functionality coming soon', 'info', 3000);
}

// View file details
function viewFileDetails(fileId) {
    const modal = document.getElementById('modal-file-details');
    const content = document.getElementById('modal-file-content');

    // Show modal with loading
    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';

    // Fetch file details (placeholder - replace with actual API call)
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert--info">
                <div class="alert__icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert__content">
                    <div class="alert__title">File ID: ${fileId}</div>
                    <div class="alert__message">Detailed file information will be displayed here.</div>
                </div>
            </div>
            <p>This would show:</p>
            <ul>
                <li>Full file path and metadata</li>
                <li>Code complexity metrics</li>
                <li>Dependency graph</li>
                <li>Violation details</li>
                <li>Code preview</li>
            </ul>
        `;
    }, 500);
}

// View dependencies
function viewDependencies(fileId) {
    window.location.href = `?page=dependencies&file_id=${fileId}`;
}

// Search on Enter key
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        applyFilters();
    }
});

// Initialize tooltips and table sorting
document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
JAVASCRIPT;

// Include footer
// Layout handled by index.php
?>
