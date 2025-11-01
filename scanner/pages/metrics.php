<?php
/**
 * Metrics Page - Complete V2 Redesign
 * Comprehensive project metrics dashboard with analytics and trends
 *
 * Features:
 * - Semantic HTML5 structure
 * - KPI dashboard with key metrics
 * - Time-series charts for trends
 * - Comparison charts (current vs baseline)
 * - Export functionality
 * - Customizable widgets
 * - Real-time data updates
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Project Metrics';
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
    $valStmt->execute([$projectId]);
    $projectData = $valStmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 1, 'project_name' => 'Default'];
}

// Get current project metrics
$metricsQuery = "
    SELECT
        health_score,
        technical_debt_score as technical_debt,
        test_coverage,
        documented_percentage,
        code_duplication_percentage,
        complexity_score,
        lines_of_code,
        created_at,
        updated_at
    FROM project_metrics
    WHERE project_id = ?
    ORDER BY created_at DESC
    LIMIT 1
";

$metricsStmt = $pdo->prepare($metricsQuery);
$metricsStmt->execute([$projectId]);
$currentMetrics = $metricsStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'health_score' => 0,
    'technical_debt' => 0,
    'test_coverage' => 0,
    'documented_percentage' => 0,
    'code_duplication_percentage' => 0,
    'complexity_score' => 0,
    'lines_of_code' => 0
];

// Get historical metrics for trends (last 30 days)
$trendQuery = "
    SELECT
        DATE(created_at) as date,
        AVG(health_score) as health_score,
        AVG(technical_debt_score) as technical_debt,
        AVG(test_coverage) as test_coverage,
        AVG(complexity_score) as complexity_score
    FROM project_metrics
    WHERE project_id = ?
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";

$trendStmt = $pdo->prepare($trendQuery);
$trendStmt->execute([$projectId]);
$historicalData = $trendStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Get file statistics
$fileStatsQuery = "
    SELECT
        COUNT(*) as total_files,
        COUNT(CASE WHEN file_type = 'php' THEN 1 END) as php_files,
        COUNT(CASE WHEN file_type = 'js' THEN 1 END) as js_files,
        COUNT(CASE WHEN file_type = 'css' THEN 1 END) as css_files,
        COUNT(CASE WHEN file_type = 'json' THEN 1 END) as json_files,
        SUM(lines_of_code) as total_loc,
        AVG(complexity_score) as avg_complexity,
        SUM(file_size) as total_size
    FROM intelligence_files
    WHERE project_id = ?
    AND is_active = 1
";

$fileStatsStmt = $pdo->prepare($fileStatsQuery);
$fileStatsStmt->execute([$projectId]);
$fileStats = $fileStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_files' => 0,
    'php_files' => 0,
    'js_files' => 0,
    'css_files' => 0,
    'json_files' => 0,
    'total_loc' => 0,
    'avg_complexity' => 0,
    'total_size' => 0
];

// Get violation statistics
$violationStatsQuery = "
    SELECT
        COUNT(*) as total_violations,
        COUNT(CASE WHEN severity = 'critical' THEN 1 END) as critical,
        COUNT(CASE WHEN severity = 'high' THEN 1 END) as high,
        COUNT(CASE WHEN severity = 'medium' THEN 1 END) as medium,
        COUNT(CASE WHEN severity = 'low' THEN 1 END) as low
    FROM violations
    WHERE project_id = ?
    AND status = 'open'
";

$violationStatsStmt = $pdo->prepare($violationStatsQuery);
$violationStatsStmt->execute([$projectId]);
$violationStats = $violationStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_violations' => 0,
    'critical' => 0,
    'high' => 0,
    'medium' => 0,
    'low' => 0
];

// Get dependency statistics
$depStatsQuery = "
    SELECT
        COUNT(*) as total_dependencies,
        COUNT(DISTINCT source_file_id) as files_with_deps,
        AVG(dep_count) as avg_dependencies_per_file
    FROM (
        SELECT source_file_id, COUNT(*) as dep_count
        FROM file_dependencies
        WHERE project_id = ?
        GROUP BY source_file_id
    ) as dep_summary
";

$depStatsStmt = $pdo->prepare($depStatsQuery);
$depStatsStmt->execute([$projectId]);
$depStats = $depStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'total_dependencies' => 0,
    'files_with_deps' => 0,
    'avg_dependencies_per_file' => 0
];

// Calculate derived metrics
$healthScore = (float)($currentMetrics['health_score'] ?? 0);
$techDebt = (float)($currentMetrics['technical_debt'] ?? 0);
$testCoverage = (float)($currentMetrics['test_coverage'] ?? 0);
$documentation = (float)($currentMetrics['documented_percentage'] ?? 0);
$duplication = (float)($currentMetrics['code_duplication_percentage'] ?? 0);
$complexity = (float)($currentMetrics['complexity_score'] ?? 0);
$totalLOC = (int)($currentMetrics['lines_of_code'] ?? 0);

// Determine health status
$healthStatus = 'good';
$healthStatusText = 'Good';
$healthStatusColor = 'success';
if ($healthScore < 50) {
    $healthStatus = 'critical';
    $healthStatusText = 'Critical';
    $healthStatusColor = 'danger';
} elseif ($healthScore < 70) {
    $healthStatus = 'warning';
    $healthStatusText = 'Needs Attention';
    $healthStatusColor = 'warning';
}

// Include header and sidebar
// Layout handled by index.php
// Layout handled by index.php
?>

<main id="main-content" class="main-content" role="main">
    <!-- Page Header -->
    <header class="page-header">
        <div class="page-header__title-row">
            <div>
                <h1 class="page-header__title">Project Metrics</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?> ·
                    Health Status: <span class="badge badge--<?php echo $healthStatusColor; ?>">
                        <?php echo $healthStatusText; ?>
                    </span>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--outline" onclick="refreshMetrics()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn--primary" onclick="exportMetrics()">
                    <i class="fas fa-download"></i> Export Report
                </button>
            </div>
        </div>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="?page=overview" class="breadcrumb__item">Home</a>
            <span class="breadcrumb__separator">/</span>
            <span class="breadcrumb__item breadcrumb__item--active">Metrics</span>
        </nav>
    </header>

    <article class="page-content">
        <!-- KPI Dashboard -->
        <section class="kpi-row" aria-label="Key performance indicators">
            <div class="kpi-card kpi-card--primary">
                <div class="kpi-card__icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($healthScore); ?>%</div>
                    <div class="kpi-card__label">Health Score</div>
                </div>
                <div class="kpi-card__progress">
                    <div class="progress" style="height: 4px;">
                        <div class="progress__bar progress__bar--<?php echo $healthStatusColor; ?>"
                             style="width: <?php echo round($healthScore); ?>%"
                             role="progressbar"
                             aria-valuenow="<?php echo round($healthScore); ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-card--warning">
                <div class="kpi-card__icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($techDebt); ?>%</div>
                    <div class="kpi-card__label">Technical Debt</div>
                </div>
                <div class="kpi-card__progress">
                    <div class="progress" style="height: 4px;">
                        <div class="progress__bar progress__bar--warning"
                             style="width: <?php echo round($techDebt); ?>%"
                             role="progressbar">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-card--success">
                <div class="kpi-card__icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($testCoverage); ?>%</div>
                    <div class="kpi-card__label">Test Coverage</div>
                </div>
                <div class="kpi-card__progress">
                    <div class="progress" style="height: 4px;">
                        <div class="progress__bar progress__bar--success"
                             style="width: <?php echo round($testCoverage); ?>%"
                             role="progressbar">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-card--info">
                <div class="kpi-card__icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($documentation); ?>%</div>
                    <div class="kpi-card__label">Documentation</div>
                </div>
                <div class="kpi-card__progress">
                    <div class="progress" style="height: 4px;">
                        <div class="progress__bar progress__bar--info"
                             style="width: <?php echo round($documentation); ?>%"
                             role="progressbar">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-card--danger">
                <div class="kpi-card__icon">
                    <i class="fas fa-copy"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($duplication); ?>%</div>
                    <div class="kpi-card__label">Code Duplication</div>
                </div>
                <div class="kpi-card__progress">
                    <div class="progress" style="height: 4px;">
                        <div class="progress__bar progress__bar--danger"
                             style="width: <?php echo round($duplication); ?>%"
                             role="progressbar">
                        </div>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-card--secondary">
                <div class="kpi-card__icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="kpi-card__content">
                    <div class="kpi-card__value"><?php echo round($complexity, 1); ?></div>
                    <div class="kpi-card__label">Avg Complexity</div>
                </div>
                <div class="kpi-card__footer">
                    <span class="text-muted text-sm">Cyclomatic</span>
                </div>
            </div>
        </section>

        <!-- Detailed Statistics -->
        <section class="page-section">
            <h2 class="page-section__title">Detailed Statistics</h2>
            <div class="grid grid--3">
                <!-- File Statistics -->
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-file-code"></i> File Statistics
                        </h3>
                    </div>
                    <div class="card__body">
                        <div class="stat-list">
                            <div class="stat-list__item">
                                <span class="stat-list__label">Total Files</span>
                                <span class="stat-list__value"><?php echo number_format((int)$fileStats['total_files']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">PHP Files</span>
                                <span class="stat-list__value"><?php echo number_format((int)$fileStats['php_files']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">JavaScript Files</span>
                                <span class="stat-list__value"><?php echo number_format((int)$fileStats['js_files']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">CSS Files</span>
                                <span class="stat-list__value"><?php echo number_format((int)$fileStats['css_files']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">Total Lines</span>
                                <span class="stat-list__value"><?php echo number_format((int)$fileStats['total_loc']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Violation Statistics -->
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-exclamation-triangle"></i> Violations
                        </h3>
                    </div>
                    <div class="card__body">
                        <div class="stat-list">
                            <div class="stat-list__item">
                                <span class="stat-list__label">Total Violations</span>
                                <span class="stat-list__value"><?php echo number_format((int)$violationStats['total_violations']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">
                                    <span class="badge badge--danger" style="width: 12px; height: 12px; padding: 0; display: inline-block;"></span>
                                    Critical
                                </span>
                                <span class="stat-list__value"><?php echo number_format((int)$violationStats['critical']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">
                                    <span class="badge badge--warning" style="width: 12px; height: 12px; padding: 0; display: inline-block;"></span>
                                    High
                                </span>
                                <span class="stat-list__value"><?php echo number_format((int)$violationStats['high']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">
                                    <span class="badge badge--info" style="width: 12px; height: 12px; padding: 0; display: inline-block;"></span>
                                    Medium
                                </span>
                                <span class="stat-list__value"><?php echo number_format((int)$violationStats['medium']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">
                                    <span class="badge badge--secondary" style="width: 12px; height: 12px; padding: 0; display: inline-block;"></span>
                                    Low
                                </span>
                                <span class="stat-list__value"><?php echo number_format((int)$violationStats['low']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dependency Statistics -->
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title">
                            <i class="fas fa-link"></i> Dependencies
                        </h3>
                    </div>
                    <div class="card__body">
                        <div class="stat-list">
                            <div class="stat-list__item">
                                <span class="stat-list__label">Total Dependencies</span>
                                <span class="stat-list__value"><?php echo number_format((int)$depStats['total_dependencies']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">Files with Dependencies</span>
                                <span class="stat-list__value"><?php echo number_format((int)$depStats['files_with_deps']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">Avg per File</span>
                                <span class="stat-list__value"><?php echo round((float)$depStats['avg_dependencies_per_file'], 1); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">Total Size</span>
                                <span class="stat-list__value"><?php echo formatBytes((int)$fileStats['total_size']); ?></span>
                            </div>
                            <div class="stat-list__item">
                                <span class="stat-list__label">Avg Complexity</span>
                                <span class="stat-list__value"><?php echo round((float)$fileStats['avg_complexity'], 1); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="page-section">
            <h2 class="page-section__title">Trend Analysis</h2>
            <div class="grid grid--2">
                <!-- Health Score Trend -->
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3 class="chart-card__title">Health Score Trend (30 Days)</h3>
                        <div class="chart-card__legend">
                            <span class="chart-card__legend-item">
                                <span class="chart-card__legend-color" style="background: var(--color-primary);"></span>
                                Health Score
                            </span>
                        </div>
                    </div>
                    <div class="chart-card__body">
                        <canvas id="healthTrendChart" height="250"></canvas>
                    </div>
                </div>

                <!-- File Type Distribution -->
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3 class="chart-card__title">File Type Distribution</h3>
                    </div>
                    <div class="chart-card__body">
                        <canvas id="fileTypeChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Violations by Severity -->
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3 class="chart-card__title">Violations by Severity</h3>
                    </div>
                    <div class="chart-card__body">
                        <canvas id="violationsChart" height="250"></canvas>
                    </div>
                </div>

                <!-- Quality Radar -->
                <div class="chart-card">
                    <div class="chart-card__header">
                        <h3 class="chart-card__title">Quality Radar</h3>
                    </div>
                    <div class="chart-card__body">
                        <canvas id="qualityRadarChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </article>
</main>

<?php
// Helper function for formatting bytes
function formatBytes(int $bytes): string {
    if ($bytes === 0) return '0 B';

    $units = ['B', 'KB', 'MB', 'GB'];
    $i = (int)floor(log($bytes) / log(1024));

    return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
}

// Prepare chart data as JSON
$chartData = [
    'historical' => $historicalData,
    'fileTypes' => [
        'php' => (int)$fileStats['php_files'],
        'js' => (int)$fileStats['js_files'],
        'css' => (int)$fileStats['css_files'],
        'json' => (int)$fileStats['json_files']
    ],
    'violations' => [
        'critical' => (int)$violationStats['critical'],
        'high' => (int)$violationStats['high'],
        'medium' => (int)$violationStats['medium'],
        'low' => (int)$violationStats['low']
    ],
    'quality' => [
        'health' => round($healthScore),
        'coverage' => round($testCoverage),
        'documentation' => round($documentation),
        'complexity' => 100 - min(100, $complexity * 5),
        'duplication' => 100 - round($duplication)
    ]
];

// Page-specific JavaScript
$inlineScript = <<<JAVASCRIPT
// Chart data from PHP
const chartData = <?php echo json_encode($chartData, JSON_THROW_ON_ERROR); ?>;

// Initialize all charts
document.addEventListener('DOMContentLoaded', function() {
    // Health Score Trend Chart
    if (chartData.historical.length > 0) {
        ChartsModule.createLineChart('healthTrendChart', {
            labels: chartData.historical.map(d => d.date),
            datasets: [{
                label: 'Health Score',
                data: chartData.historical.map(d => parseFloat(d.health_score)),
                borderColor: 'rgb(78, 115, 223)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.4
            }]
        }, {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        });
    }

    // File Type Distribution Chart
    ChartsModule.createDoughnutChart('fileTypeChart', {
        labels: ['PHP', 'JavaScript', 'CSS', 'JSON'],
        values: [
            chartData.fileTypes.php,
            chartData.fileTypes.js,
            chartData.fileTypes.css,
            chartData.fileTypes.json
        ]
    });

    // Violations Chart
    ChartsModule.createBarChart('violationsChart', {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
            label: 'Open Violations',
            data: [
                chartData.violations.critical,
                chartData.violations.high,
                chartData.violations.medium,
                chartData.violations.low
            ],
            backgroundColor: [
                'rgba(231, 74, 59, 0.8)',
                'rgba(246, 194, 62, 0.8)',
                'rgba(54, 185, 204, 0.8)',
                'rgba(149, 165, 166, 0.8)'
            ]
        }]
    });

    // Quality Radar Chart
    ChartsModule.createRadarChart('qualityRadarChart', {
        labels: ['Health', 'Coverage', 'Documentation', 'Complexity', 'Duplication'],
        datasets: [{
            label: 'Current Quality',
            data: [
                chartData.quality.health,
                chartData.quality.coverage,
                chartData.quality.documentation,
                chartData.quality.complexity,
                chartData.quality.duplication
            ],
            backgroundColor: 'rgba(78, 115, 223, 0.2)',
            borderColor: 'rgb(78, 115, 223)',
            pointBackgroundColor: 'rgb(78, 115, 223)'
        }]
    }, {
        scales: {
            r: {
                beginAtZero: true,
                max: 100
            }
        }
    });

    // Initialize tooltips
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});

// Refresh metrics
function refreshMetrics() {
    DashboardApp.showAlert('Refreshing metrics...', 'info', 2000);
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Export metrics report
function exportMetrics() {
    DashboardApp.showAlert('Export functionality coming soon', 'info', 3000);
    // TODO: Implement CSV/PDF export
}
JAVASCRIPT;

// Include footer
// Layout handled by index.php
?>
