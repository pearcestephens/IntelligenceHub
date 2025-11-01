<?php
/**
 * Intelligence Dashboard - Main Entry Point
 * Enterprise-Grade Multi-Page Modular Dashboard
 *
 * @package CIS Intelligence
 * @version 2.0.0
 */

// Define access constant for includes
define('DASHBOARD_ACCESS', true);

// Load configuration (includes session config)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Check authentication
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Get current page
$page = $_GET['page'] ?? 'overview';
$allowedPages = [
    // Main Section
    'overview',
    'search',
    'files',
    'functions',

    // Intelligence Section
    'analytics',
    'patterns',
    'neural',
    'conversations',

    // System Section
    'servers',
    'cron',
    'scanner',
    'logs',
    'api',

    // Tools Section
    'ai-control-center',
    'ai-agent',
    'ai-chat',
    'crawler-monitor',
    'mcp-tools',
    'bot-standards',
    'bot-commands',
    'sql-query',
    'cleanup',
    'documentation',

    // Settings Section
    'settings',
    'credentials',
    'db-validate'
];

if (!in_array($page, $allowedPages)) {
    $page = 'overview';
}

// Page title mapping
$pageTitles = [
    // Main Section
    'overview' => 'Dashboard Overview',
    'search' => 'Intelligence Search',
    'files' => 'File Browser',
    'functions' => 'Function Explorer',

    // Intelligence Section
    'analytics' => 'Analytics & Insights',
    'patterns' => 'Pattern Recognition',
    'neural' => 'Neural Networks',
    'conversations' => 'Conversations',

    // System Section
    'servers' => 'Server Management',
    'cron' => 'Cron Management',
    'scanner' => 'Neural Scanner',
    'logs' => 'System Logs',
    'api' => 'API Management',

    // Tools Section
    'ai-control-center' => '🤖 AI Control Center',
    'ai-agent' => 'AI Agent Dashboard',
    'ai-chat' => 'AI Chat Interface',
    'crawler-monitor' => 'Web Crawler Monitor',
    'mcp-tools' => 'MCP Tools Testing',
    'bot-standards' => 'Bot Standards Manager',
    'bot-commands' => 'Bot Commands',
    'sql-query' => 'SQL Query Tool',
    'cleanup' => 'Database Cleanup',
    'documentation' => 'Documentation',

    // Settings Section
    'settings' => 'Settings',
    'credentials' => 'Credential Manager',
    'db-validate' => 'Database Validator'
];

$pageTitle = $pageTitles[$page] ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CIS Intelligence Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <!-- Prism.js for syntax highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>

    <?php include __DIR__ . '/includes/header.php'; ?>

    <div class="dashboard-wrapper">

        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="main-content">
            <div class="container-fluid">

                <?php include __DIR__ . '/includes/breadcrumb.php'; ?>

                <!-- Page Content -->
                <div class="page-content">
                    <?php
                    $pageFile = __DIR__ . "/pages/{$page}.php";
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo '<div class="alert alert-danger">Page not found.</div>';
                    }
                    ?>
                </div>

            </div>
        </main>

    </div>

    <?php include __DIR__ . '/includes/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Prism.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/dashboard.js"></script>

    <!-- Page-specific JS -->
    <?php if (file_exists(__DIR__ . "/assets/js/pages/{$page}.js")): ?>
    <script src="assets/js/pages/<?php echo $page; ?>.js"></script>
    <?php endif; ?>

</body>
</html>
