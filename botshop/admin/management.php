<?php
/**
 * Management Page Wrapper - Routes management pages through proper layout
 *
 * This wrapper ensures management pages are loaded with:
 * - Full dashboard sidebar with navigation
 * - Top navigation bar
 * - Proper styling and assets
 * - Session management
 */

declare(strict_types=1);

// Set current page for sidebar highlighting
$_GET['page'] = $_GET['page'] ?? 'projects';
$currentPage = $_GET['page'];

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Load project selector component
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/includes/project-selector.php';

// Define layout variables
$pageTitle = '';
switch ($currentPage) {
    case 'projects':
        $pageTitle = 'Projects Management';
        break;
    case 'business-units':
        $pageTitle = 'Business Units';
        break;
    case 'scan-config':
        $pageTitle = 'Scan Configuration';
        break;
    case 'scan-history':
        $pageTitle = 'Scan History';
        break;
    default:
        $pageTitle = 'Dashboard';
}

$breadcrumbs = ['Management', $pageTitle];

// Capture page content
ob_start();

// Load the appropriate page content
$pagePath = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/pages/' . $currentPage . '.php';
if (file_exists($pagePath)) {
    include $pagePath;
} else {
    echo '<div class="alert alert-danger">Page not found: ' . htmlspecialchars($currentPage) . '</div>';
}

$pageContent = ob_get_clean();

// Load layout wrapper
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/_layout.php';
?>
