<?php
/**
 * Dashboard Overview Page - VERSION 2.0 REDESIGN
 * Modern, semantic HTML5 with professional design
 *
 * IMPROVEMENTS:
 * - Semantic HTML5 structure (<main>, <header>, <article>, <section>)
 * - Modern CSS with design system
 * - Proper accessibility (ARIA labels, semantic tags)
 * - Type-safe data handling
 * - Clean component-based layout
 *
 * @package hdgwrzntwa/dashboard/admin
 * @version 2.0.0
 * @updated December 2025
 */

declare(strict_types=1);

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Load project selector component
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/includes/project-selector.php';

// Get current project ID from session
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
// DATA QUERIES WITH TYPE SAFETY
// ============================================================================

// Get project data
$projectQuery = "
    SELECT
        p.id,
        p.project_name,
        p.project_type,
        p.project_path,
        p.status,
        p.health_score,
        p.technical_debt,
        p.lines_of_code,
        p.framework,
        p.version,
        COUNT(DISTINCT f.file_id) as total_files,
        MAX(f.extracted_at) as last_scan_time
    FROM projects p
    LEFT JOIN intelligence_files f ON p.id = f.project_id
    WHERE p.id = ?
    GROUP BY p.id
    LIMIT 1
";

$projectData = [];
try {
    $stmt = $pdo->prepare($projectQuery);
    $stmt->execute([$projectId]);
    $projectData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Overview: Project query error - " . $e->getMessage());
}

// Get file statistics
$fileStatsQuery = "
    SELECT
        COUNT(*) as total_files,
        SUM(CASE WHEN file_type = 'php' THEN 1 ELSE 0 END) as php_files,
        SUM(CASE WHEN file_type = 'js' THEN 1 ELSE 0 END) as js_files,
        SUM(CASE WHEN file_type = 'css' THEN 1 ELSE 0 END) as css_files,
        SUM(CASE WHEN file_type = 'json' THEN 1 ELSE 0 END) as json_files,
        SUM(CASE WHEN extracted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as recent_files
    FROM intelligence_files
    WHERE project_id = ?
";

$fileStats = [];
try {
    $stmt = $pdo->prepare($fileStatsQuery);
    $stmt->execute([$projectId]);
    $fileStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Overview: File stats query error - " . $e->getMessage());
    $fileStats = ['total_files' => 0, 'php_files' => 0, 'js_files' => 0, 'css_files' => 0, 'json_files' => 0, 'recent_files' => 0];
}

// Get violation statistics
$violationQuery = "
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical,
        SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high,
        SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium,
        SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low
    FROM project_rule_violations
    WHERE project_id = ?
";

$violationStats = [];
try {
    $stmt = $pdo->prepare($violationQuery);
    $stmt->execute([$projectId]);
    $violationStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Overview: Violations query error - " . $e->getMessage());
    $violationStats = ['total' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
}

// Get recent scan activity
$recentScansQuery = "
    SELECT
        COUNT(DISTINCT DATE(extracted_at)) as scan_days,
        COUNT(*) as total_scanned,
        MAX(extracted_at) as last_scan,
        AVG(TIMESTAMPDIFF(SECOND, DATE_SUB(extracted_at, INTERVAL 1 HOUR), extracted_at)) as avg_duration
    FROM intelligence_files
    WHERE project_id = ?
    AND extracted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
";

$scanData = [];
try {
    $stmt = $pdo->prepare($recentScansQuery);
    $stmt->execute([$projectId]);
    $scanData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Overview: Scan data query error - " . $e->getMessage());
    $scanData = ['scan_days' => 0, 'total_scanned' => 0, 'last_scan' => null, 'avg_duration' => 0];
}

// ============================================================================
// PAGE CONFIGURATION
// ============================================================================

$pageTitle = 'Overview';
$lastScanTime = !empty($scanData['last_scan']) ? date('M j, Y g:i A', strtotime($scanData['last_scan'])) : 'Never';
$violationCount = (int)($violationStats['critical'] ?? 0) + (int)($violationStats['high'] ?? 0);

// Include header and sidebar
require_once __DIR__ . '/../includes-v2/header.php';
require_once __DIR__ . '/../includes-v2/sidebar.php';
?>

<!-- Main Content Area -->
<main id="main-content" class="main-content" role="main">
    <!-- Page Header -->
    <header class="page-header">
        <div class="page-header__title-row">
            <div>
                <h1 class="page-header__title">Dashboard Overview</h1>
                <p class="page-header__subtitle">
                    Project: <strong><?= htmlspecialchars($projectData['project_name'] ?? 'Unknown') ?></strong>
                    &middot; Last scan: <?= $lastScanTime ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button class="btn btn--outline" id="btn-refresh-data" aria-label="Refresh data">
                    <i class="fas fa-sync-alt" aria-hidden="true"></i>
                    Refresh
                </button>
                <a href="?page=scan-config" class="btn btn--primary">
                    <i class="fas fa-play" aria-hidden="true"></i>
                    Run Scan
                </a>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="breadcrumb">
            <div class="breadcrumb__item">
                <a href="?page=overview" class="breadcrumb__link">Home</a>
            </div>
            <span class="breadcrumb__separator" aria-hidden="true">/</span>
            <div class="breadcrumb__item">
                <span class="breadcrumb__current" aria-current="page">Overview</span>
            </div>
        </nav>
    </header>

    <!-- Main Content Article -->
    <article class="page-content">
        <!-- Hero Health Score Card -->
        <section class="page-section" aria-labelledby="health-score-heading">
            <div class="hero-card">
                <div class="hero-card__score" id="health-score-heading">
                    <?= round((float)($projectData['health_score'] ?? 0)) ?>%
                </div>
                <div class="hero-card__label">
                    Overall Health Score
                </div>
                <p style="opacity: 0.9; margin-bottom: 0;">
                    <?php
                    $healthScore = (float)($projectData['health_score'] ?? 0);
                    if ($healthScore >= 90) {
                        echo '<i class="fas fa-check-circle"></i> Excellent - Your codebase is in great shape';
                    } elseif ($healthScore >= 75) {
                        echo '<i class="fas fa-thumbs-up"></i> Good - Minor improvements recommended';
                    } elseif ($healthScore >= 60) {
                        echo '<i class="fas fa-exclamation-triangle"></i> Fair - Some issues need attention';
                    } else {
                        echo '<i class="fas fa-times-circle"></i> Poor - Significant improvements needed';
                    }
                    ?>
                </p>

                <div class="hero-card__details">
                    <div class="hero-card__detail-item">
                        <div class="hero-card__detail-label">Technical Debt</div>
                        <div class="hero-card__detail-value"><?= round((float)($projectData['technical_debt'] ?? 0)) ?>%</div>
                    </div>
                    <div class="hero-card__detail-item">
                        <div class="hero-card__detail-label">Total Files</div>
                        <div class="hero-card__detail-value"><?= number_format((int)($fileStats['total_files'] ?? 0)) ?></div>
                    </div>
                    <div class="hero-card__detail-item">
                        <div class="hero-card__detail-label">Lines of Code</div>
                        <div class="hero-card__detail-value"><?= number_format((int)($projectData['lines_of_code'] ?? 0)) ?></div>
                    </div>
                    <div class="hero-card__detail-item">
                        <div class="hero-card__detail-label">Violations</div>
                        <div class="hero-card__detail-value"><?= number_format((int)($violationStats['total'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Key Metrics Grid -->
        <section class="page-section" aria-labelledby="metrics-heading">
            <h2 class="section-title" id="metrics-heading">Key Metrics</h2>

            <div class="metrics-grid">
                <!-- Metric Card 1: Total Files -->
                <div class="metric-card metric-card--primary">
                    <div class="metric-card__header">
                        <span class="metric-card__label">Total Files</span>
                        <div class="metric-card__icon">
                            <i class="fas fa-file-code" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="metric-card__value"><?= number_format((int)($fileStats['total_files'] ?? 0)) ?></div>
                    <div class="metric-card__change metric-card__change--positive">
                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                        <?= number_format((int)($fileStats['recent_files'] ?? 0)) ?> added this week
                    </div>
                </div>

                <!-- Metric Card 2: Critical Violations -->
                <div class="metric-card metric-card--danger">
                    <div class="metric-card__header">
                        <span class="metric-card__label">Critical Issues</span>
                        <div class="metric-card__icon" style="background: rgba(231, 74, 59, 0.1); color: var(--color-danger);">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="metric-card__value"><?= number_format((int)($violationStats['critical'] ?? 0)) ?></div>
                    <div class="metric-card__change">
                        <a href="?page=violations&severity=critical" style="color: inherit; text-decoration: underline;">
                            View all critical issues
                        </a>
                    </div>
                </div>

                <!-- Metric Card 3: PHP Files -->
                <div class="metric-card metric-card--success">
                    <div class="metric-card__header">
                        <span class="metric-card__label">PHP Files</span>
                        <div class="metric-card__icon" style="background: rgba(28, 200, 138, 0.1); color: var(--color-success);">
                            <i class="fab fa-php" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="metric-card__value"><?= number_format((int)($fileStats['php_files'] ?? 0)) ?></div>
                    <div class="metric-card__change">
                        <?php
                        $totalFiles = max(1, (int)($fileStats['total_files'] ?? 1));
                        $phpFiles = (int)($fileStats['php_files'] ?? 0);
                        $percentage = round(($phpFiles / $totalFiles) * 100);
                        echo "{$percentage}% of total files";
                        ?>
                    </div>
                </div>

                <!-- Metric Card 4: Recent Scans -->
                <div class="metric-card metric-card--warning">
                    <div class="metric-card__header">
                        <span class="metric-card__label">Scan Activity</span>
                        <div class="metric-card__icon" style="background: rgba(246, 194, 62, 0.1); color: var(--color-warning);">
                            <i class="fas fa-history" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="metric-card__value"><?= number_format((int)($scanData['scan_days'] ?? 0)) ?></div>
                    <div class="metric-card__change">
                        days active in last 30 days
                    </div>
                </div>
            </div>
        </section>

        <!-- File Statistics Section -->
        <section class="page-section" aria-labelledby="file-stats-heading">
            <div class="section-header">
                <div>
                    <h2 class="section-title" id="file-stats-heading">File Statistics</h2>
                    <p class="section-subtitle">Distribution of file types in your project</p>
                </div>
                <a href="?page=files" class="btn btn--ghost">
                    View All Files <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="card">
                <div class="card__body">
                    <div class="grid grid--3">
                        <!-- PHP Files -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fab fa-php" style="color: var(--color-primary);" aria-hidden="true"></i>
                                    <strong>PHP Files</strong>
                                </span>
                                <span class="badge badge--primary"><?= number_format((int)($fileStats['php_files'] ?? 0)) ?></span>
                            </div>
                            <div style="background: var(--bg-hover); height: 8px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--color-primary); height: 100%; width: <?= min(100, round((((int)($fileStats['php_files'] ?? 0)) / max(1, (int)($fileStats['total_files'] ?? 1))) * 100)) ?>%;"></div>
                            </div>
                        </div>

                        <!-- JavaScript Files -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fab fa-js-square" style="color: var(--color-warning);" aria-hidden="true"></i>
                                    <strong>JavaScript Files</strong>
                                </span>
                                <span class="badge badge--warning"><?= number_format((int)($fileStats['js_files'] ?? 0)) ?></span>
                            </div>
                            <div style="background: var(--bg-hover); height: 8px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--color-warning); height: 100%; width: <?= min(100, round((((int)($fileStats['js_files'] ?? 0)) / max(1, (int)($fileStats['total_files'] ?? 1))) * 100)) ?>%;"></div>
                            </div>
                        </div>

                        <!-- CSS Files -->
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fab fa-css3-alt" style="color: var(--color-info);" aria-hidden="true"></i>
                                    <strong>CSS Files</strong>
                                </span>
                                <span class="badge badge--info"><?= number_format((int)($fileStats['css_files'] ?? 0)) ?></span>
                            </div>
                            <div style="background: var(--bg-hover); height: 8px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--color-info); height: 100%; width: <?= min(100, round((((int)($fileStats['css_files'] ?? 0)) / max(1, (int)($fileStats['total_files'] ?? 1))) * 100)) ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Violations Overview -->
        <section class="page-section" aria-labelledby="violations-heading">
            <div class="section-header">
                <div>
                    <h2 class="section-title" id="violations-heading">Code Violations</h2>
                    <p class="section-subtitle">Issues detected by automated code analysis</p>
                </div>
                <a href="?page=violations" class="btn btn--ghost">
                    View All Violations <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="grid grid--4">
                <!-- Critical -->
                <div class="card" style="border-left: 4px solid var(--color-danger);">
                    <div class="card__body">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: var(--font-size-xs); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Critical</div>
                                <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-danger);"><?= number_format((int)($violationStats['critical'] ?? 0)) ?></div>
                            </div>
                            <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: var(--color-danger); opacity: 0.3;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- High -->
                <div class="card" style="border-left: 4px solid var(--color-warning);">
                    <div class="card__body">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: var(--font-size-xs); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">High</div>
                                <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-warning);"><?= number_format((int)($violationStats['high'] ?? 0)) ?></div>
                            </div>
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--color-warning); opacity: 0.3;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- Medium -->
                <div class="card" style="border-left: 4px solid var(--color-info);">
                    <div class="card__body">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: var(--font-size-xs); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Medium</div>
                                <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-info);"><?= number_format((int)($violationStats['medium'] ?? 0)) ?></div>
                            </div>
                            <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--color-info); opacity: 0.3;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>

                <!-- Low -->
                <div class="card" style="border-left: 4px solid var(--color-success);">
                    <div class="card__body">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: var(--font-size-xs); color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Low</div>
                                <div style="font-size: var(--font-size-3xl); font-weight: var(--font-weight-bold); color: var(--color-success);"><?= number_format((int)($violationStats['low'] ?? 0)) ?></div>
                            </div>
                            <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--color-success); opacity: 0.3;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="page-section" aria-labelledby="charts-heading">
            <h2 class="section-title" id="charts-heading">Visual Analytics</h2>

            <div class="grid grid--2">
                <!-- File Types Chart -->
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">File Type Distribution</h3>
                    </div>
                    <div class="card__body">
                        <canvas id="fileTypesChart" style="max-height: 300px;" aria-label="File type distribution chart" role="img"></canvas>
                    </div>
                </div>

                <!-- Violations Chart -->
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">Violations by Severity</h3>
                    </div>
                    <div class="card__body">
                        <canvas id="violationsChart" style="max-height: 300px;" aria-label="Violations by severity chart" role="img"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </article>
</main>

<?php
// Page-specific JavaScript
$inlineScript = "
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // File Types Doughnut Chart
    if (document.getElementById('fileTypesChart')) {
        ChartsModule.createDoughnutChart('fileTypesChart', {
            labels: ['PHP', 'JavaScript', 'CSS', 'JSON'],
            values: [
                " . ((int)($fileStats['php_files'] ?? 0)) . ",
                " . ((int)($fileStats['js_files'] ?? 0)) . ",
                " . ((int)($fileStats['css_files'] ?? 0)) . ",
                " . ((int)($fileStats['json_files'] ?? 0)) . "
            ]
        });
    }

    // Violations Bar Chart
    if (document.getElementById('violationsChart')) {
        ChartsModule.createBarChart('violationsChart', {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                label: 'Violations',
                data: [
                    " . ((int)($violationStats['critical'] ?? 0)) . ",
                    " . ((int)($violationStats['high'] ?? 0)) . ",
                    " . ((int)($violationStats['medium'] ?? 0)) . ",
                    " . ((int)($violationStats['low'] ?? 0)) . "
                ]
            }]
        });
    }

    // Refresh button handler
    document.getElementById('btn-refresh-data')?.addEventListener('click', function() {
        window.location.reload();
    });
});
";

// Include footer
require_once __DIR__ . '/../includes-v2/footer.php';
?>
