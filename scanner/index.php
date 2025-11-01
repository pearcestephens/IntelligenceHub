<?php
/**
 * Scanner Dashboard - Production Grade Application
 *
 * Consolidated modern code quality monitoring system
 * Combines best features from Dashboard Admin V2 and BotShop
 *
 * @package Scanner
 * @version 3.0.0
 * @updated October 31, 2025
 * @author Intelligence Hub Team
 */

declare(strict_types=1);

// ============================================================================
// SECURITY & ERROR HANDLING
// ============================================================================

// Ensure logs directory exists
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/.htaccess', "Require all denied\n");
}

// Production error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Start session securely
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// ============================================================================
// CONFIGURATION
// ============================================================================

define('SCANNER_ROOT', __DIR__);
define('SCANNER_VERSION', '3.0.0');
define('SCANNER_ASSETS', '/scanner/assets');

// Load database configuration
require_once SCANNER_ROOT . '/config/database.php';

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// ============================================================================
// AUTHENTICATION CHECK
// ============================================================================

// Require authentication (can be customized based on your auth system)
if (!isset($_SESSION['authenticated'])) {
    // For now, auto-authenticate in production environment
    // TODO: Implement proper authentication
    $_SESSION['authenticated'] = true;
}

// ============================================================================
// DATABASE CONNECTION
// ============================================================================

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    );
} catch (PDOException $e) {
    error_log("Scanner: Database connection failed - " . $e->getMessage());
    die("Database connection failed. Please check logs.");
}

// ============================================================================
// PROJECT CONTEXT INITIALIZATION
// ============================================================================

// Initialize current project in session
if (!isset($_SESSION['current_project_id'])) {
    try {
        $stmt = $pdo->query("SELECT id FROM projects WHERE status = 'active' ORDER BY id ASC LIMIT 1");
        $project = $stmt->fetch();
        $_SESSION['current_project_id'] = $project['id'] ?? 1;
    } catch (PDOException $e) {
        error_log("Scanner: Failed to initialize project context - " . $e->getMessage());
        $_SESSION['current_project_id'] = 1;
    }
}

// Initialize current unit in session (default to Intelligence Hub)
if (!isset($_SESSION['current_unit_id'])) {
    $_SESSION['current_unit_id'] = 1;
}

// ============================================================================
// REQUEST HANDLING
// ============================================================================

// Get current page with sanitization (modern PHP 8.1+ approach)
$page = $_GET['page'] ?? 'overview';
$page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
$page = preg_replace('/[^a-z0-9\-_]/', '', strtolower($page));
$page = $page ?: 'overview';

// Available pages configuration
$pages = [
    // Core pages
    'overview' => [
        'title' => 'Dashboard Overview',
        'file' => 'overview.php',
        'icon' => 'bi-speedometer2',
        'description' => 'System health and statistics'
    ],
    'files' => [
        'title' => 'File Analysis',
        'file' => 'files.php',
        'icon' => 'bi-file-earmark-code',
        'description' => 'Browse and analyze code files'
    ],
    'dependencies' => [
        'title' => 'Code Dependencies',
        'file' => 'dependencies.php',
        'icon' => 'bi-diagram-3',
        'description' => 'Dependency tree and relationships'
    ],
    'violations' => [
        'title' => 'Rule Violations',
        'file' => 'violations.php',
        'icon' => 'bi-exclamation-triangle',
        'description' => 'Code quality violations'
    ],
    'rules' => [
        'title' => 'Project Rules',
        'file' => 'rules.php',
        'icon' => 'bi-list-check',
        'description' => 'Manage scanning rules'
    ],
    'metrics' => [
        'title' => 'Performance Metrics',
        'file' => 'metrics.php',
        'icon' => 'bi-graph-up',
        'description' => 'Performance statistics'
    ],

    // Management pages
    'projects' => [
        'title' => 'Projects',
        'file' => 'projects.php',
        'icon' => 'bi-folder',
        'description' => 'Manage projects'
    ],
    'business-units' => [
        'title' => 'Business Units',
        'file' => 'business-units.php',
        'icon' => 'bi-building',
        'description' => 'Manage business units'
    ],
    'scan-config' => [
        'title' => 'Scan Configuration',
        'file' => 'scan-config.php',
        'icon' => 'bi-gear',
        'description' => 'Configure scan settings'
    ],
    'scan-history' => [
        'title' => 'Scan History',
        'file' => 'scan-history.php',
        'icon' => 'bi-clock-history',
        'description' => 'View scan history'
    ],

    // Utility pages
    'settings' => [
        'title' => 'Settings',
        'file' => 'settings.php',
        'icon' => 'bi-sliders',
        'description' => 'Application settings'
    ],
    'documentation' => [
        'title' => 'Documentation',
        'file' => 'documentation.php',
        'icon' => 'bi-book',
        'description' => 'User documentation'
    ],
    'support' => [
        'title' => 'Support',
        'file' => 'support.php',
        'icon' => 'bi-question-circle',
        'description' => 'Help and support'
    ],
];

// Validate page exists
if (!isset($pages[$page])) {
    $page = 'overview';
}

$currentPage = $pages[$page];
$pageTitle = $currentPage['title'];
$pageFile = SCANNER_ROOT . '/pages/' . $currentPage['file'];

// Verify page file exists
if (!file_exists($pageFile)) {
    error_log("Scanner: Page file not found - $pageFile");
    $page = 'overview';
    $pageFile = SCANNER_ROOT . '/pages/overview.php';
}

// ============================================================================
// PROJECT CONTEXT
// ============================================================================

// Get current project ID from session or query
$projectId = (int)($_SESSION['current_project_id'] ?? filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT) ?? 1);

// Validate project exists and is active
try {
    $stmt = $pdo->prepare("SELECT id, project_name FROM projects WHERE id = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$projectId]);
    $currentProject = $stmt->fetch();

    if (!$currentProject) {
        // Fallback to first active project
        $stmt = $pdo->query("SELECT id, project_name FROM projects WHERE status = 'active' ORDER BY id LIMIT 1");
        $currentProject = $stmt->fetch();
        $projectId = $currentProject ? (int)$currentProject['id'] : 1;
    }

    $_SESSION['current_project_id'] = $projectId;
} catch (Exception $e) {
    error_log("Scanner: Project validation error - " . $e->getMessage());
}

// ============================================================================
// LAYOUT RENDERING
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($currentPage['description']); ?>">
    <meta name="application-name" content="Scanner Dashboard">
    <meta name="version" content="<?php echo SCANNER_VERSION; ?>">

    <title><?php echo htmlspecialchars($pageTitle); ?> - Scanner Dashboard</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo SCANNER_ASSETS; ?>/images/favicon.svg">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Scanner Bootstrap-Based Custom CSS -->
    <link rel="stylesheet" href="<?php echo SCANNER_ASSETS; ?>/css/scanner-bootstrap.css">

    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="scanner-app">

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="app-wrapper d-flex">

        <!-- Sidebar Navigation -->
        <?php require_once SCANNER_ROOT . '/includes/sidebar.php'; ?>

        <div class="app-main flex-fill">

            <!-- Top Navigation Bar -->
            <?php require_once SCANNER_ROOT . '/includes/navbar.php'; ?>

            <!-- Main Content Area -->
            <main class="page-content" role="main">
                <div class="container-fluid p-4">

                    <!-- Page Header -->
                    <header class="page-header mb-4">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="page-title">
                                    <i class="<?php echo $currentPage['icon']; ?> me-2"></i>
                                    <?php echo htmlspecialchars($pageTitle); ?>
                                </h1>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($currentPage['description']); ?></p>
                            </div>
                            <div class="col-auto">
                                <div class="badge bg-success">v<?php echo SCANNER_VERSION; ?></div>
                            </div>
                        </div>
                    </header>

                    <!-- Page Content -->
                    <div class="page-body">
                        <?php
                        try {
                            require $pageFile;
                        } catch (Exception $e) {
                            error_log("Scanner: Page load error - " . $e->getMessage());
                            ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Error loading page:</strong> An unexpected error occurred.
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                </div>
            </main>

            <!-- Footer -->
            <?php require_once SCANNER_ROOT . '/includes/footer.php'; ?>

        </div>
    </div>

    <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- JavaScript Files (in order) -->
    <script src="<?php echo SCANNER_ASSETS; ?>/js/01-utils.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/02-api.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/03-tables.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/04-modals.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/05-notifications.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/06-storage.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/07-forms.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/08-navigation.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/09-charts.js"></script>
    <script src="<?php echo SCANNER_ASSETS; ?>/js/10-init.js"></script>

    <!-- Page-specific JavaScript -->
    <script>
        // Global configuration
        window.ScannerConfig = {
            version: '<?php echo SCANNER_VERSION; ?>',
            currentPage: '<?php echo $page; ?>',
            projectId: <?php echo $projectId; ?>,
            apiBase: '/scanner/api',
            assetsBase: '<?php echo SCANNER_ASSETS; ?>'
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Scanner Dashboard v<?php echo SCANNER_VERSION; ?> initialized');

            // Initialize tooltips if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltips.forEach(el => new bootstrap.Tooltip(el));
            }
        });
    </script>

</body>
</html>
