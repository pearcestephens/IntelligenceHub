<?php
/**
 * Admin Dashboard - Router & Loader
 *
 * Modular high-end admin dashboard for project management
 * Pages: overview, files, dependencies, violations, rules, metrics, settings
 *
 * Auto-loads CSS and JS in order
 */

declare(strict_types=1);

// Configuration
define('DASHBOARD_ROOT', __DIR__);
define('DASHBOARD_ASSETS', DASHBOARD_ROOT . '/assets');
define('DB_HOST', 'localhost');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');
define('DB_NAME', 'hdgwrzntwa');
define('PROJECT_ID', 1);

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get current page
$page = isset($_GET['page']) ? htmlspecialchars($_GET['page']) : 'overview';

// List of available pages
$pages = [
    'overview' => 'Project Overview',
    'files' => 'File Analysis',
    'dependencies' => 'Code Dependencies',
    'violations' => 'Rule Violations',
    'rules' => 'Project Rules',
    'metrics' => 'Performance Metrics',
    'ai-agent' => 'AI Agent Config',
    'settings' => 'Settings',
];

// Management pages (loaded via management.php wrapper)
$managementPages = [
    'projects' => 'Projects',
    'business-units' => 'Business Units',
    'scan-config' => 'Scan Config',
    'scan-history' => 'Scan History',
];

// Validate page
if (!isset($pages[$page])) {
    $page = 'overview';
}

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pages[$page]; ?> - hdgwrzntwa Admin Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Auto-load CSS files in order -->
    <?php
    $cssFiles = glob(DASHBOARD_ASSETS . '/css/[0-1][0-9]*.css');
    sort($cssFiles);
    foreach ($cssFiles as $css) {
        $path = '/dashboard/admin' . str_replace(DASHBOARD_ROOT, '', $css);
        echo '<link rel="stylesheet" href="' . htmlspecialchars($path) . '">' . "\n";
    }
    ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="?page=overview">
                <i class="fas fa-project-diagram"></i> hdgwrzntwa Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($pages as $pageKey => $pageTitle): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === $pageKey ? 'active' : ''; ?>"
                               href="?page=<?php echo $pageKey; ?>">
                                <?php echo $pageTitle; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <!-- Management Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Management
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="managementDropdown">
                            <?php foreach ($managementPages as $pageKey => $pageTitle): ?>
                                <li>
                                    <a class="dropdown-item" href="/dashboard/admin/management.php?page=<?php echo $pageKey; ?>">
                                        <?php echo $pageTitle; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Project Info</h5>
                    </div>
                    <div class="card-body small">
                        <?php
                        $stmt = $pdo->query("
                            SELECT p.*, pm.total_files, psc.last_scan
                            FROM projects p
                            LEFT JOIN project_metadata pm ON p.id = pm.project_id
                            LEFT JOIN project_scan_config psc ON p.id = psc.project_id
                            WHERE p.id = " . PROJECT_ID
                        );
                        $project = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($project):
                        ?>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($project['project_name']); ?></p>
                            <p><strong>Type:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($project['project_type']); ?></span></p>
                            <p><strong>Status:</strong> <span class="badge bg-success"><?php echo htmlspecialchars($project['status']); ?></span></p>
                            <p><strong>Files:</strong> <?php echo $project['total_files'] ?? 'N/A'; ?></p>
                            <p><strong>Last Scan:</strong> <?php echo $project['last_scan'] ? date('M d, Y H:i', strtotime($project['last_scan'])) : 'Never'; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php
                // Load page content
                $pagePath = DASHBOARD_ROOT . '/pages/' . $page . '.php';
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    echo '<div class="alert alert-warning">Page not found</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto-load JS files in order -->
    <?php
    $jsFiles = glob(DASHBOARD_ASSETS . '/js/[0-2][0-9]*.js');
    sort($jsFiles);
    foreach ($jsFiles as $js) {
        $path = '/dashboard/admin' . str_replace(DASHBOARD_ROOT, '', $js);
        echo '<script src="' . htmlspecialchars($path) . '"></script>' . "\n";
    }
    ?>

    <!-- Inject PDO for AJAX calls -->
    <script>
        const API_BASE = window.location.pathname.split('/dashboard/')[0] + '/dashboard/api/';
        const PROJECT_ID = <?php echo PROJECT_ID; ?>;
    </script>
</body>
</html>
<?php
ob_end_flush();
