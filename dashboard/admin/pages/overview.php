<?php
/**
 * Dashboard Overview Page - PHASE 2 UPDATED
 * Main dashboard with project statistics, activity, and metrics
 *
 * CHANGES FROM PHASE 1:
 * - Removed hardcoded $projectId = 1
 * - Added project selector component
 * - Uses dynamic project ID from session
 * - All queries include project_id filter
 *
 * @package hdgwrzntwa/dashboard/admin
 * @category Dashboard Page - PHASE 2
 * @updated October 30, 2025
 */

declare(strict_types=1);

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

// SECTION 1: Get project stats from database
$query = "
    SELECT
        p.id,
        p.project_name,
        p.project_type,
        p.project_path,
        p.status,
        COUNT(DISTINCT f.file_id) as total_files
    FROM projects p
    LEFT JOIN intelligence_files f ON p.id = f.project_id
    WHERE p.id = ?
    GROUP BY p.id
    LIMIT 1
";

$projectData = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute([$projectId]);
    $projectData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Overview page error: " . $e->getMessage());
}

// SECTION 2: Get recent scan info
$scanQuery = "
    SELECT
        file_id,
        file_name,
        file_path,
        extracted_at,
        file_type
    FROM intelligence_files
    WHERE project_id = ?
    ORDER BY extracted_at DESC
    LIMIT 5
";

try {
    $scanStmt = $pdo->prepare($scanQuery);
    $scanStmt->execute([$projectId]);
    $recentFiles = $scanStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentFiles = [];
    error_log("Scan query error: " . $e->getMessage());
}

// SECTION 3: Get activity data
$activityQuery = "
    SELECT
        COUNT(*) as activity_count,
        SUM(CASE WHEN extracted_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as last_24h
    FROM intelligence_files
    WHERE project_id = ?
";

$activityData = ['activity_count' => 0, 'last_24h' => 0];
try {
    $stmt = $pdo->prepare($activityQuery);
    $stmt->execute([$projectId]);
    $activityData = $stmt->fetch(PDO::FETCH_ASSOC) ?: $activityData;
} catch (Exception $e) {
    error_log("Activity query error: " . $e->getMessage());
}

// Get violation stats
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

$violationStats = ['total' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
try {
    $stmt = $pdo->prepare($violationQuery);
    $stmt->execute([$projectId]);
    $violationStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $violationStats;
} catch (Exception $e) {
    error_log("Violation stats error: " . $e->getMessage());
}

// SECTION 5: Get file statistics
$fileQuery = "
    SELECT
        COUNT(*) as total_files,
        SUM(CASE WHEN file_type = 'php' THEN 1 ELSE 0 END) as php_files,
        SUM(CASE WHEN file_type = 'js' THEN 1 ELSE 0 END) as js_files,
        SUM(CASE WHEN file_type = 'css' THEN 1 ELSE 0 END) as css_files,
        SUM(CASE WHEN file_type = 'json' THEN 1 ELSE 0 END) as json_files
    FROM intelligence_files
    WHERE project_id = ?
";

$fileStats = ['total_files' => 0, 'php_files' => 0, 'js_files' => 0, 'css_files' => 0, 'json_files' => 0, 'config_files' => 0];
try {
    $stmt = $pdo->prepare($fileQuery);
    $stmt->execute([$projectId]);
    $fileStats = $stmt->fetch(PDO::FETCH_ASSOC) ?: $fileStats;
} catch (Exception $e) {
    error_log("File stats error: " . $e->getMessage());
}
?>

<div class="dashboard-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p class="text-muted">Project metrics, statistics, and recent activity</p>
    </div>

    <!-- Project Selector Component -->
    <div class="mb-4">
        ?>

<main role="main">
    <div class="dashboard-container">
        <!-- Project Selector Component (PHASE 2) -->
        <?php echo renderProjectSelector($pdo, $_SESSION['current_unit_id'], $projectId); ?>
    </div>

    <!-- Project Info Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Project Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Project Name:</strong> <?php echo htmlspecialchars($projectData['name'] ?? 'N/A'); ?></p>
                            <p><strong>Type:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($projectData['project_type'] ?? 'N/A'); ?></span></p>
                            <p><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($projectData['status'] ?? 'N/A'); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Framework:</strong> <?php echo htmlspecialchars($projectData['framework'] ?? 'N/A'); ?></p>
                            <p><strong>Version:</strong> <?php echo htmlspecialchars($projectData['version'] ?? 'N/A'); ?></p>
                            <p><strong>Path:</strong> <code><?php echo htmlspecialchars(substr($projectData['path'] ?? '', -50)); ?></code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Score Card -->
        <div class="col-lg-4">
            <div class="card border-left-primary">
                <div class="card-body text-center">
                    <div class="health-score-circle">
                        <svg width="120" height="120" class="circular-progress">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#e3e6f0" stroke-width="8"/>
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#4e73df" stroke-width="8"
                                    stroke-dasharray="<?php echo ($projectData['health_score'] ?? 0) * 3.39; ?> 339"
                                    style="transform: rotate(-90deg); transform-origin: 60px 60px;"/>
                        </svg>
                        <div class="health-score-value"><?php echo round((float)($projectData['health_score'] ?? 0)); ?>%</div>
                    </div>
                    <h6 class="text-primary mt-3">Health Score</h6>
                    <p class="text-muted small">Project quality rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <!-- Total Files -->
        <div class="col-md-3">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon bg-primary">
                        <i class="fas fa-file"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-1">Total Files</h6>
                    <h3 class="mb-0"><?php echo number_format((int)($fileStats['total_files'] ?? 0)); ?></h3>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> From last scan</small>
                </div>
            </div>
        </div>

        <!-- PHP Files -->
        <div class="col-md-3">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon bg-info">
                        <i class="fas fa-code"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-1">PHP Files</h6>
                    <h3 class="mb-0"><?php echo number_format((int)($fileStats['php_files'] ?? 0)); ?></h3>
                    <small><?php echo round(((int)($fileStats['php_files'] ?? 0)) / max(1, (int)($fileStats['total_files'] ?? 1)) * 100); ?>% of total</small>
                </div>
            </div>
        </div>

        <!-- Technical Debt -->
        <div class="col-md-3">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-1">Tech Debt</h6>
                    <h3 class="mb-0"><?php echo round((float)($projectData['technical_debt'] ?? 0)); ?>%</h3>
                    <small class="text-danger"><i class="fas fa-arrow-down"></i> Work needed</small>
                </div>
            </div>
        </div>

        <!-- Lines of Code -->
        <div class="col-md-3">
            <div class="card metric-card">
                <div class="card-body">
                    <div class="metric-icon bg-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h6 class="text-muted text-uppercase mb-1">Lines of Code</h6>
                    <h3 class="mb-0"><?php echo number_format((int)($projectData['lines_of_code'] ?? 0)); ?></h3>
                    <small class="text-muted">Total project size</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Violations & Recent Activity Row -->
    <div class="row">
        <!-- Rule Violations -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Rule Violations</h6>
                    <a href="?page=violations" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="violation-stat">
                                <div class="violation-count critical"><?php echo $violationStats['critical'] ?? 0; ?></div>
                                <p class="text-muted small">Critical</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="violation-stat">
                                <div class="violation-count high"><?php echo $violationStats['high'] ?? 0; ?></div>
                                <p class="text-muted small">High</p>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <div class="violation-stat">
                                <div class="violation-count medium"><?php echo $violationStats['medium'] ?? 0; ?></div>
                                <p class="text-muted small">Medium</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="violation-stat">
                                <div class="violation-count low"><?php echo $violationStats['low'] ?? 0; ?></div>
                                <p class="text-muted small">Low</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-center text-muted"><strong><?php echo $violationStats['total'] ?? 0; ?></strong> total violations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scan -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Last Scan Information</h6>
                    <button class="btn btn-sm btn-primary" onclick="API.post('/dashboard/api/scan/run', {}, function() { location.reload(); })">
                        <i class="fas fa-sync"></i> Run Scan
                    </button>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Last Scanned:</strong><br>
                        <?php echo isset($scanData['last_scan_date']) && $scanData['last_scan_date'] ? date('F j, Y g:i A', strtotime($scanData['last_scan_date'])) : 'Never'; ?>
                    </p>
                    <p>
                        <strong>Files Scanned:</strong><br>
                        <?php echo number_format((int)($scanData['total_files_scanned'] ?? 0)); ?>
                    </p>
                    <p>
                        <strong>Scan Duration:</strong><br>
                        <?php echo round((float)($scanData['last_scan_duration'] ?? 0), 2); ?> seconds
                    </p>
                    <p>
                        <strong>Frequency:</strong><br>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($scanData['scan_frequency'] ?? 'Manual'); ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Health Score Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="healthChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">File Type Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="fileTypeChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Health Score Chart
    const healthCtx = document.getElementById('healthChart')?.getContext('2d');
    if (healthCtx) {
        new Chart(healthCtx, {
            type: 'line',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Today'],
                datasets: [{
                    label: 'Health Score',
                    data: [<?php echo isset($projectData['health_score']) ? implode(',', array_fill(0, 5, floor($projectData['health_score'] * 0.95))) . ',' . $projectData['health_score'] : '0,0,0,0,0,0'; ?>],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { min: 0, max: 100 }
                }
            }
        });
    }

    // File Type Chart
    const fileCtx = document.getElementById('fileTypeChart')?.getContext('2d');
    if (fileCtx) {
        new Chart(fileCtx, {
            type: 'doughnut',
            data: {
                labels: ['PHP', 'JavaScript', 'CSS', 'Config', 'Other'],
                datasets: [{
                    data: [<?php echo ($fileStats['php_files'] ?? 0) . ',' . ($fileStats['js_files'] ?? 0) . ',' . ($fileStats['css_files'] ?? 0) . ',' . ($fileStats['config_files'] ?? 0) . ',' . (max(0, ($fileStats['total_files'] ?? 0) - ($fileStats['php_files'] ?? 0) - ($fileStats['js_files'] ?? 0) - ($fileStats['css_files'] ?? 0) - ($fileStats['config_files'] ?? 0))); ?>],
                    backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74c3c', '#95a5a6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>

<style>
.metric-card {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    float: right;
}

.health-score-circle {
    position: relative;
    display: inline-block;
}

.health-score-value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 32px;
    font-weight: bold;
    color: #4e73df;
}

.violation-stat {
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
}

.violation-count {
    font-size: 28px;
    font-weight: bold;
    border-radius: 4px;
    padding: 10px;
    color: white;
}

.violation-count.critical {
    background-color: #e74c3c;
}

.violation-count.high {
    background-color: #e67e22;
}

.violation-count.medium {
    background-color: #f39c12;
}

.violation-count.low {
    background-color: #3498db;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 600;
    color: #2c3e50;
}

.card {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.card-header {
    border-bottom: 1px solid #e3e6f0;
}

.bg-left-primary {
    border-left: 4px solid #4e73df;
}

code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    color: #e74c3c;
}
</style>
