<?php
/**
 * BotShop Dashboard - Production Main Router
 *
 * Professional code quality monitoring dashboard
 * VERSION 2.0 - Production Ready
 *
 * @package BotShop
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// ============================================================================
// PRODUCTION CONFIGURATION
// ============================================================================

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/botshop_errors.log');

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// Path definitions
define('BOTSHOP_ROOT', __DIR__);
define('BOTSHOP_ASSETS', BOTSHOP_ROOT . '/assets');
define('BOTSHOP_PAGES', BOTSHOP_ROOT . '/pages');
define('BOTSHOP_INCLUDES', BOTSHOP_ROOT . '/includes');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');
define('DB_NAME', 'hdgwrzntwa');
define('PROJECT_ID', 1);

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
        ]
    );
} catch (PDOException $e) {
    error_log("BotShop DB Connection Error: " . $e->getMessage());
    http_response_code(503);
    die("Database connection failed. Please try again later.");
}

// ============================================================================
// ROUTING
// ============================================================================

// Get requested page
$page = isset($_GET['page']) ? htmlspecialchars($_GET['page'], ENT_QUOTES, 'UTF-8') : 'overview';

// Available pages (V2 complete list)
$availablePages = [
    // Core Analysis
    'overview' => 'Dashboard Overview',
    'files' => 'File Analysis',
    'metrics' => 'Performance Metrics',
    'scan-history' => 'Scan History',
    'dependencies' => 'Code Dependencies',
    'violations' => 'Rule Violations',
    'rules' => 'Project Rules',

    // Management
    'settings' => 'Settings',
    'projects' => 'Projects',
    'business-units' => 'Business Units',
    'scan-config' => 'Scan Configuration',

    // AI & Tools
    'ai-agent' => 'AI Agent Config',

    // Documentation
    'documentation' => 'Documentation',
    'support' => 'Support',

    // Legal
    'privacy' => 'Privacy Policy',
    'terms' => 'Terms of Service',
];

// Validate page exists
if (!isset($availablePages[$page])) {
    $page = 'overview';
}

$pageTitle = $availablePages[$page] ?? 'Dashboard';

// ============================================================================
// HTML OUTPUT
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BotShop - Professional Code Quality Monitoring Dashboard">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($pageTitle); ?> - BotShop Dashboard</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">

    <!-- Chart.js 4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" integrity="sha384-xDEHNEdWtWcz9Y6z0Z8qqBPDq0nBbD3h5vDKYWvG9w6UmmH7jqCnCzH2vLm0VGgS" crossorigin="anonymous"></script>

    <!-- Custom CSS (Auto-load in order) -->
    <?php
    $cssFiles = glob(BOTSHOP_ASSETS . '/css/[0-9][0-9]*.css');
    if ($cssFiles) {
        sort($cssFiles);
        foreach ($cssFiles as $css) {
            $cssPath = '/botshop' . str_replace(BOTSHOP_ROOT, '', $css);
            echo '<link rel="stylesheet" href="' . htmlspecialchars($cssPath) . '">' . "\n    ";
        }
    }
    ?>

    <style>
        /* Production-ready base styles */
        :root {
            --bs-primary: #007bff;
            --bs-success: #28a745;
            --bs-danger: #dc3545;
            --bs-warning: #ffc107;
            --bs-info: #17a2b8;
        }

        body {
            min-height: 100vh;
            background: #f8f9fa;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .content-wrapper {
            min-height: calc(100vh - 56px);
        }

        .sidebar {
            background: #fff;
            border-right: 1px solid #dee2e6;
            min-height: calc(100vh - 56px);
            position: sticky;
            top: 56px;
        }

        .main-content {
            padding: 2rem;
        }

        .nav-link {
            color: #6c757d;
            padding: 0.75rem 1.25rem;
            border-radius: 0.25rem;
            margin: 0.25rem 0.5rem;
        }

        .nav-link:hover {
            background: #f8f9fa;
            color: #007bff;
        }

        .nav-link.active {
            background: #007bff;
            color: #fff !important;
        }

        .page-loading {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="?page=overview">
                <i class="fas fa-robot"></i> BotShop Dashboard
            </a>
            <span class="badge bg-success ms-2">V2.0 Production</span>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link text-light">
                            <i class="fas fa-database"></i> Project #<?php echo PROJECT_ID; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=documentation">
                            <i class="fas fa-book"></i> Docs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=support">
                            <i class="fas fa-question-circle"></i> Support
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">

                <!-- Sidebar Navigation -->
                <nav class="col-md-2 sidebar">
                    <div class="py-3">
                        <h6 class="text-muted text-uppercase px-3 mb-3 small fw-bold">Analysis</h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'overview' ? 'active' : ''; ?>" href="?page=overview">
                                    <i class="fas fa-tachometer-alt"></i> Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'files' ? 'active' : ''; ?>" href="?page=files">
                                    <i class="fas fa-file-code"></i> Files
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'metrics' ? 'active' : ''; ?>" href="?page=metrics">
                                    <i class="fas fa-chart-line"></i> Metrics
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'violations' ? 'active' : ''; ?>" href="?page=violations">
                                    <i class="fas fa-exclamation-triangle"></i> Violations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'rules' ? 'active' : ''; ?>" href="?page=rules">
                                    <i class="fas fa-shield-alt"></i> Rules
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'dependencies' ? 'active' : ''; ?>" href="?page=dependencies">
                                    <i class="fas fa-project-diagram"></i> Dependencies
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'scan-history' ? 'active' : ''; ?>" href="?page=scan-history">
                                    <i class="fas fa-history"></i> Scan History
                                </a>
                            </li>
                        </ul>

                        <h6 class="text-muted text-uppercase px-3 mb-3 mt-4 small fw-bold">Configuration</h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'projects' ? 'active' : ''; ?>" href="?page=projects">
                                    <i class="fas fa-folder"></i> Projects
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'business-units' ? 'active' : ''; ?>" href="?page=business-units">
                                    <i class="fas fa-users"></i> Business Units
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'scan-config' ? 'active' : ''; ?>" href="?page=scan-config">
                                    <i class="fas fa-cog"></i> Scan Config
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'ai-agent' ? 'active' : ''; ?>" href="?page=ai-agent">
                                    <i class="fas fa-robot"></i> AI Agent
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>" href="?page=settings">
                                    <i class="fas fa-sliders-h"></i> Settings
                                </a>
                            </li>
                        </ul>

                        <h6 class="text-muted text-uppercase px-3 mb-3 mt-4 small fw-bold">Resources</h6>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'documentation' ? 'active' : ''; ?>" href="?page=documentation">
                                    <i class="fas fa-book"></i> Documentation
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page === 'support' ? 'active' : ''; ?>" href="?page=support">
                                    <i class="fas fa-life-ring"></i> Support
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Main Content Area -->
                <main class="col-md-10 main-content">
                    <div class="page-loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <?php
                    // Load requested page
                    $pagePath = BOTSHOP_PAGES . '/' . $page . '.php';

                    if (file_exists($pagePath)) {
                        try {
                            include $pagePath;
                        } catch (Throwable $e) {
                            error_log("BotShop Page Load Error ($page): " . $e->getMessage());
                            ?>
                            <div class="alert alert-danger" role="alert">
                                <h4 class="alert-heading">
                                    <i class="fas fa-exclamation-circle"></i> Page Load Error
                                </h4>
                                <p>An error occurred while loading this page. The issue has been logged.</p>
                                <hr>
                                <p class="mb-0">
                                    <a href="?page=overview" class="btn btn-sm btn-primary">Return to Overview</a>
                                    <a href="?page=support" class="btn btn-sm btn-outline-secondary">Contact Support</a>
                                </p>
                            </div>
                            <?php
                        }
                    } else {
                        ?>
                        <div class="alert alert-warning" role="alert">
                            <h4 class="alert-heading">
                                <i class="fas fa-exclamation-triangle"></i> Page Not Found
                            </h4>
                            <p>The requested page "<?php echo htmlspecialchars($page); ?>" does not exist.</p>
                            <hr>
                            <p class="mb-0">
                                <a href="?page=overview" class="btn btn-sm btn-primary">Return to Overview</a>
                            </p>
                        </div>
                        <?php
                    }
                    ?>
                </main>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

    <!-- Custom JS (Auto-load in order) -->
    <?php
    $jsFiles = glob(BOTSHOP_ASSETS . '/js/[0-9][0-9]*.js');
    if ($jsFiles) {
        sort($jsFiles);
        foreach ($jsFiles as $js) {
            $jsPath = '/botshop' . str_replace(BOTSHOP_ROOT, '', $js);
            echo '<script src="' . htmlspecialchars($jsPath) . '"></script>' . "\n    ";
        }
    }
    ?>

    <!-- Global Configuration -->
    <script>
        // Global configuration for AJAX calls
        const BOTSHOP_CONFIG = {
            apiBase: '/botshop/api/',
            projectId: <?php echo PROJECT_ID; ?>,
            currentPage: '<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>',
            csrfToken: '<?php echo bin2hex(random_bytes(16)); ?>',
        };

        // Console branding
        console.log('%c🤖 BotShop Dashboard V2.0', 'font-size: 20px; font-weight: bold; color: #007bff;');
        console.log('%cProduction Ready | Code Quality Monitoring', 'font-size: 12px; color: #6c757d;');
    </script>

</body>
</html>
