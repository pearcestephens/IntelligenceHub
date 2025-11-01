<?php
/**
 * BotShop Dashboard - Main Router & Loader
 *
 * Modern code quality monitoring dashboard
 * Direct page loading without admin subdirectory
 *
 * @package BotShop
 * @version 2.0.0
 */

declare(strict_types=1);

// Configuration
define('BOTSHOP_ROOT', __DIR__);
define('BOTSHOP_ASSETS', BOTSHOP_ROOT . '/assets');
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
    'overview' => 'Dashboard Overview',
    'files' => 'File Analysis',
    'dependencies' => 'Code Dependencies',
    'violations' => 'Rule Violations',
    'rules' => 'Project Rules',
    'metrics' => 'Performance Metrics',
    'ai-agent' => 'AI Agent Config',
    'settings' => 'Settings',
    'projects' => 'Projects',
    'business-units' => 'Business Units',
    'scan-config' => 'Scan Configuration',
    'scan-history' => 'Scan History',
    'documentation' => 'Documentation',
    'support' => 'Support',
    'privacy' => 'Privacy Policy',
    'terms' => 'Terms of Service',
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
    <title><?php echo $pages[$page]; ?> - BotShop Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Auto-load CSS files in order -->
    <?php
    $cssFiles = glob(BOTSHOP_ASSETS . '/css/[0-1][0-9]*.css');
    if ($cssFiles) {
        sort($cssFiles);
        foreach ($cssFiles as $css) {
            $path = '/botshop' . str_replace(BOTSHOP_ROOT, '', $css);
            echo '<link rel="stylesheet" href="' . htmlspecialchars($path) . '">' . "\n";
        }
    }
    ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="?page=overview">
                <i class="fas fa-robot"></i> BotShop Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php foreach (array_slice($pages, 0, 8) as $pageKey => $pageTitle): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $page === $pageKey ? 'active' : ''; ?>"
                               href="?page=<?php echo $pageKey; ?>">
                                <?php echo $pageTitle; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4 bg-light sidebar py-4">
                <div class="sticky-top">
                    <!-- Project Info -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-project-diagram"></i> Current Project</h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $stmt = $pdo->query(
                                "SELECT p.*, pm.total_files, psc.last_scan
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

                    <!-- Navigation Menu -->
                    <div class="list-group">
                        <div class="list-group-item list-group-item-action active disabled">
                            <i class="fas fa-chart-line"></i> Analysis
                        </div>
                        <a href="?page=overview" class="list-group-item list-group-item-action <?php echo $page === 'overview' ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i> Overview
                        </a>
                        <a href="?page=files" class="list-group-item list-group-item-action <?php echo $page === 'files' ? 'active' : ''; ?>">
                            <i class="fas fa-file-code"></i> Files
                        </a>
                        <a href="?page=violations" class="list-group-item list-group-item-action <?php echo $page === 'violations' ? 'active' : ''; ?>">
                            <i class="fas fa-exclamation-triangle"></i> Violations
                        </a>
                        <a href="?page=metrics" class="list-group-item list-group-item-action <?php echo $page === 'metrics' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Metrics
                        </a>
                        <a href="?page=rules" class="list-group-item list-group-item-action <?php echo $page === 'rules' ? 'active' : ''; ?>">
                            <i class="fas fa-gavel"></i> Rules
                        </a>
                        <a href="?page=dependencies" class="list-group-item list-group-item-action <?php echo $page === 'dependencies' ? 'active' : ''; ?>">
                            <i class="fas fa-project-diagram"></i> Dependencies
                        </a>
                        <a href="?page=scan-history" class="list-group-item list-group-item-action <?php echo $page === 'scan-history' ? 'active' : ''; ?>">
                            <i class="fas fa-history"></i> Scan History
                        </a>

                        <div class="list-group-item list-group-item-action active disabled mt-3">
                            <i class="fas fa-cog"></i> Configuration
                        </div>
                        <a href="?page=projects" class="list-group-item list-group-item-action <?php echo $page === 'projects' ? 'active' : ''; ?>">
                            <i class="fas fa-folder"></i> Projects
                        </a>
                        <a href="?page=business-units" class="list-group-item list-group-item-action <?php echo $page === 'business-units' ? 'active' : ''; ?>">
                            <i class="fas fa-building"></i> Business Units
                        </a>
                        <a href="?page=scan-config" class="list-group-item list-group-item-action <?php echo $page === 'scan-config' ? 'active' : ''; ?>">
                            <i class="fas fa-sliders-h"></i> Scan Config
                        </a>
                        <a href="?page=ai-agent" class="list-group-item list-group-item-action <?php echo $page === 'ai-agent' ? 'active' : ''; ?>">
                            <i class="fas fa-robot"></i> AI Agent
                        </a>
                        <a href="?page=settings" class="list-group-item list-group-item-action <?php echo $page === 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> Settings
                        </a>

                        <div class="list-group-item list-group-item-action active disabled mt-3">
                            <i class="fas fa-info-circle"></i> Resources
                        </div>
                        <a href="?page=documentation" class="list-group-item list-group-item-action <?php echo $page === 'documentation' ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> Documentation
                        </a>
                        <a href="?page=support" class="list-group-item list-group-item-action <?php echo $page === 'support' ? 'active' : ''; ?>">
                            <i class="fas fa-life-ring"></i> Support
                        </a>
                        <a href="?page=privacy" class="list-group-item list-group-item-action <?php echo $page === 'privacy' ? 'active' : ''; ?>">
                            <i class="fas fa-shield-alt"></i> Privacy
                        </a>
                        <a href="?page=terms" class="list-group-item list-group-item-action <?php echo $page === 'terms' ? 'active' : ''; ?>">
                            <i class="fas fa-file-contract"></i> Terms
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <?php
                // Load page content
                $pagePath = BOTSHOP_ROOT . '/pages/' . $page . '.php';
                if (file_exists($pagePath)) {
                    include $pagePath;
                } else {
                    echo '<div class="alert alert-warning mt-4">Page not found: ' . htmlspecialchars($page) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Auto-load JS files in order -->
    <?php
    $jsFiles = glob(BOTSHOP_ASSETS . '/js/[0-2][0-9]*.js');
    if ($jsFiles) {
        sort($jsFiles);
        foreach ($jsFiles as $js) {
            $path = '/botshop' . str_replace(BOTSHOP_ROOT, '', $js);
            echo '<script src="' . htmlspecialchars($path) . '"></script>' . "\n";
        }
    }
    ?>

    <!-- Inject PDO for AJAX calls -->
    <script>
        const API_BASE = '/botshop/api/';
        const PROJECT_ID = <?php echo PROJECT_ID; ?>;
    </script>
</body>
</html>
<?php
ob_end_flush();
