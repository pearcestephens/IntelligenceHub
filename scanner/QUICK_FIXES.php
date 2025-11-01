<?php
/**
 * Quick Fixes for Scanner Application
 *
 * This file contains ready-to-execute code fixes for all critical issues
 * identified in CODE_ANALYSIS_REPORT.md
 *
 * @package Scanner
 * @version 3.0.0
 */

declare(strict_types=1);

// ============================================================================
// FIX #1 & #2: Bootstrap CSS and JavaScript
// ============================================================================

/**
 * LOCATION: index.php lines 239-240 (after Bootstrap Icons)
 * REPLACE THIS:
 *
 * <!-- Bootstrap Icons CDN -->
 * <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
 *
 * WITH THIS:
 */

$bootstrapCSSFix = <<<'HTML'
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
HTML;

/**
 * LOCATION: index.php lines 329-330 (before Chart.js)
 * INSERT THIS BEFORE Chart.js:
 */

$bootstrapJSFix = <<<'HTML'
    <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
HTML;

// ============================================================================
// FIX #3: FILTER_SANITIZE_STRING Deprecated
// ============================================================================

/**
 * LOCATION: index.php line 87
 * REPLACE THIS:
 *
 * $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING) ?? 'overview';
 *
 * WITH THIS:
 */

$filterFix = <<<'PHP'
// Modern approach for PHP 8.1+
$page = $_GET['page'] ?? 'overview';
$page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
// Additional validation
$page = preg_replace('/[^a-z0-9\-_]/', '', $page);
$page = $page ?: 'overview';
PHP;

// ============================================================================
// FIX #4: Missing Logs Directory
// ============================================================================

/**
 * LOCATION: index.php after line 22
 * INSERT THIS CODE:
 */

$logsDirFix = <<<'PHP'
// Ensure logs directory exists
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    // Create .htaccess to prevent web access
    file_put_contents($logDir . '/.htaccess', "Require all denied\n");
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');
PHP;

// ============================================================================
// FIX #5: Auto-Authentication
// ============================================================================

/**
 * LOCATION: index.php lines 53-58
 * REPLACE THIS:
 *
 * // TODO: Implement proper authentication
 * $isAuthenticated = true;
 *
 * WITH THIS (OPTION 1 - Development mode with warning):
 */

$authFixDevelopment = <<<'PHP'
// DEVELOPMENT MODE - AUTO AUTHENTICATION ENABLED
// WARNING: Change this before production deployment!
define('DEVELOPMENT_MODE', true);

if (DEVELOPMENT_MODE) {
    // Auto-authenticate for development
    $isAuthenticated = true;

    // Store dummy user in session
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = 'admin@scanner.local';
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['authenticated'] = true;
    }
} else {
    // Production authentication check
    $isAuthenticated = isset($_SESSION['user_id']) && isset($_SESSION['authenticated']);
}

if (!$isAuthenticated) {
    header('Location: /login.php');
    exit;
}
PHP;

/**
 * OR THIS (OPTION 2 - Proper authentication):
 */

$authFixProduction = <<<'PHP'
// Proper session-based authentication
$isAuthenticated = isset($_SESSION['user_id']) &&
                   isset($_SESSION['authenticated']) &&
                   $_SESSION['authenticated'] === true;

// Check session timeout (30 minutes)
if ($isAuthenticated && isset($_SESSION['last_activity'])) {
    $sessionTimeout = 1800; // 30 minutes
    if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
        session_destroy();
        header('Location: /login.php?reason=timeout');
        exit;
    }
}

// Update last activity timestamp
if ($isAuthenticated) {
    $_SESSION['last_activity'] = time();
}

if (!$isAuthenticated) {
    // Store intended destination for redirect after login
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /login.php');
    exit;
}
PHP;

// ============================================================================
// FIX #6: Hardcoded Asset Path
// ============================================================================

/**
 * LOCATION: index.php line 45
 * REPLACE THIS:
 *
 * define('SCANNER_ASSETS', '/scanner/assets');
 *
 * WITH THIS:
 */

$assetPathFix = <<<'PHP'
// Auto-detect asset path based on script location
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
define('SCANNER_ASSETS', $scriptPath . '/assets');
define('SCANNER_BASE_URL', $scriptPath);
PHP;

// ============================================================================
// FIX #7: CSRF Protection Implementation
// ============================================================================

/**
 * LOCATION: After session_start() in index.php (around line 36)
 * INSERT THIS CODE:
 */

$csrfTokenGeneration = <<<'PHP'
// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Function to get CSRF token
function getCsrfToken(): string {
    return $_SESSION['csrf_token'] ?? '';
}

// Function to validate CSRF token
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
PHP;

/**
 * USAGE IN FORMS:
 */

$csrfFormUsage = <<<'HTML'
<!-- Add this hidden field to all forms -->
<input type="hidden" name="csrf_token" value="<?php echo getCsrfToken(); ?>">
HTML;

/**
 * USAGE IN FORM HANDLERS:
 */

$csrfValidationUsage = <<<'PHP'
// At the top of any form processing script
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($submittedToken)) {
        http_response_code(403);
        die('CSRF validation failed. Please refresh the page and try again.');
    }

    // Process form...
}
PHP;

// ============================================================================
// FIX #8: Cache Violation Count in Sidebar
// ============================================================================

/**
 * LOCATION: sidebar.php lines 56-67
 * REPLACE THE VIOLATION COUNT QUERY WITH THIS:
 */

$violationCountCacheFix = <<<'PHP'
<?php
// Cache violation count with 60-second TTL
$cacheKey = 'violation_count_' . $projectId;
$cacheExpires = 'violation_count_expires_' . $projectId;

if (!isset($_SESSION[$cacheKey]) || !isset($_SESSION[$cacheExpires]) || $_SESSION[$cacheExpires] < time()) {
    try {
        $violationCount = dbFetchOne(
            "SELECT COUNT(*) as count FROM rule_violations WHERE project_id = ? AND status = 'open'",
            [$projectId]
        );
        $_SESSION[$cacheKey] = $violationCount;
        $_SESSION[$cacheExpires] = time() + 60; // Cache for 60 seconds
    } catch (Exception $e) {
        // Silently fail, use cached value if available
        $violationCount = $_SESSION[$cacheKey] ?? null;
    }
} else {
    $violationCount = $_SESSION[$cacheKey];
}

if ($violationCount && $violationCount['count'] > 0) {
    echo '<span class="badge bg-danger rounded-pill ms-auto">' . $violationCount['count'] . '</span>';
}
?>
PHP;

// ============================================================================
// FIX #9: Improved Project Validation with User Notification
// ============================================================================

/**
 * LOCATION: index.php lines 199-217
 * REPLACE WITH THIS:
 */

$projectValidationFix = <<<'PHP'
// Validate project exists and is active
$currentProject = null;
if ($projectId) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND status = 'active'");
    $stmt->execute([$projectId]);
    $currentProject = $stmt->fetch();
}

// Fallback logic with user notification
if (!$currentProject) {
    // Try to get first active project
    $stmt = $pdo->query("SELECT * FROM projects WHERE status = 'active' ORDER BY created_at DESC LIMIT 1");
    $currentProject = $stmt->fetch();

    if ($currentProject) {
        // Save to session
        $_SESSION['current_project_id'] = $currentProject['id'];
        $projectId = $currentProject['id'];

        // Set flash message for user
        $_SESSION['flash_message'] = [
            'type' => 'info',
            'message' => 'Automatically switched to project: ' . htmlspecialchars($currentProject['project_name']),
            'dismissible' => true
        ];
    } else {
        // No projects exist - redirect to create one
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'message' => 'No active projects found. Please create a project to continue.',
            'dismissible' => false
        ];
        header('Location: ?page=projects&action=create&reason=no_projects');
        exit;
    }
}
PHP;

/**
 * ADD FLASH MESSAGE DISPLAY FUNCTION:
 */

$flashMessageFunction = <<<'PHP'
/**
 * Display flash message (add to layout after navbar)
 */
function displayFlashMessage(): void {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';
        $dismissible = $flash['dismissible'] ?? true;

        $dismissClass = $dismissible ? 'alert-dismissible fade show' : '';
        $closeButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

        echo <<<HTML
        <div class="alert alert-{$type} {$dismissClass} m-3" role="alert">
            {$message}
            {$closeButton}
        </div>
        HTML;

        // Clear flash message
        unset($_SESSION['flash_message']);
    }
}

// Usage: Call this after navbar in index.php
// <?php displayFlashMessage(); ?>
PHP;

// ============================================================================
// FIX #10: Improved Mobile Sidebar with Overlay
// ============================================================================

/**
 * LOCATION: navbar.php - Replace sidebar toggle JavaScript
 */

$mobileSidebarFix = <<<'JAVASCRIPT'
<script>
// Improved sidebar toggle for mobile with overlay
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');

            // Add/remove overlay
            if (sidebar.classList.contains('show')) {
                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1040;';
                document.body.appendChild(overlay);

                // Close sidebar when clicking overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.remove();
                });

                // Prevent body scroll
                document.body.style.overflow = 'hidden';
            } else {
                // Remove overlay
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                document.body.style.overflow = '';
            }
        });
    }
});
</script>
JAVASCRIPT;

// ============================================================================
// FIX #11: Enhanced Search Function
// ============================================================================

/**
 * LOCATION: navbar.php - Replace handleSearch function
 */

$searchFunctionFix = <<<'JAVASCRIPT'
<script>
// Enhanced global search with validation and loading state
function handleSearch(event) {
    event.preventDefault();

    const searchInput = document.getElementById('globalSearch');
    const searchTerm = searchInput.value.trim();

    // Validate minimum length
    if (searchTerm.length < 2) {
        alert('Please enter at least 2 characters to search');
        searchInput.focus();
        return false;
    }

    // Show loading state
    searchInput.disabled = true;
    searchInput.placeholder = 'Searching...';

    // Add small delay to show loading state
    setTimeout(() => {
        window.location.href = '?page=files&search=' + encodeURIComponent(searchTerm);
    }, 100);

    return false;
}
</script>
JAVASCRIPT;

// ============================================================================
// FIX #12: Notification API Implementation
// ============================================================================

/**
 * CREATE NEW FILE: /scanner/api/notifications.php
 */

$notificationAPIFile = <<<'PHP'
<?php
/**
 * Notifications API Endpoint
 *
 * Returns notification count and recent notifications
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();

    // Get user's project
    session_start();
    $projectId = $_SESSION['current_project_id'] ?? 0;

    // Query recent notifications (last 24 hours)
    $stmt = $pdo->prepare("
        SELECT
            id,
            type,
            message,
            created_at,
            is_read
        FROM notifications
        WHERE project_id = ?
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$projectId]);
    $notifications = $stmt->fetchAll();

    // Count unread
    $unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

    echo json_encode([
        'success' => true,
        'count' => $unreadCount,
        'notifications' => $notifications,
        'timestamp' => date('c')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch notifications',
        'timestamp' => date('c')
    ]);
}
PHP;

/**
 * LOCATION: navbar.php - Update checkNotifications function
 */

$checkNotificationsFix = <<<'JAVASCRIPT'
<script>
async function checkNotifications() {
    try {
        const response = await fetch('/scanner/api/notifications.php');
        if (!response.ok) throw new Error('Network response failed');

        const data = await response.json();

        if (data.success) {
            const badge = document.getElementById('notificationBadge');
            const noNotifications = document.getElementById('noNotifications');

            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';

                // TODO: Populate notification dropdown with data.notifications
                if (noNotifications) {
                    noNotifications.style.display = 'none';
                }
            } else {
                badge.style.display = 'none';
                if (noNotifications) {
                    noNotifications.style.display = 'block';
                }
            }
        }
    } catch (error) {
        console.error('Failed to check notifications:', error);
    }
}
</script>
JAVASCRIPT;

// ============================================================================
// FIX #13: Add Main Landmark Role
// ============================================================================

/**
 * LOCATION: index.php - Update <main> tag
 * REPLACE THIS:
 *
 * <main class="page-content">
 *
 * WITH THIS:
 */

$mainLandmarkFix = <<<'HTML'
<main class="page-content" role="main" aria-label="Main content">
HTML;

// ============================================================================
// COMPLETE FIXED index.php HEADER SECTION
// ============================================================================

/**
 * For reference, here's the complete fixed header section of index.php
 */

$completeFixedHeader = <<<'PHP'
<?php
declare(strict_types=1);

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Error handling
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Ensure logs directory exists
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/.htaccess', "Require all denied\n");
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');

// Session security
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Constants
define('SCANNER_ROOT', __DIR__);
define('SCANNER_VERSION', '3.0.0');
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
define('SCANNER_ASSETS', $scriptPath . '/assets');
define('SCANNER_BASE_URL', $scriptPath);

// Bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';
require_once __DIR__ . '/config/database.php';

// Authentication check with development mode
define('DEVELOPMENT_MODE', true); // Set to false for production!

if (DEVELOPMENT_MODE) {
    $isAuthenticated = true;
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_email'] = 'admin@scanner.local';
        $_SESSION['user_name'] = 'Administrator';
        $_SESSION['authenticated'] = true;
    }
} else {
    $isAuthenticated = isset($_SESSION['user_id']) && isset($_SESSION['authenticated']);
    if ($isAuthenticated && isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > 1800) {
            session_destroy();
            header('Location: /login.php?reason=timeout');
            exit;
        }
    }
    if ($isAuthenticated) {
        $_SESSION['last_activity'] = time();
    }
}

if (!$isAuthenticated) {
    $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    header('Location: /login.php');
    exit;
}

// Database connection
try {
    $pdo = getDbConnection();
} catch (Exception $e) {
    error_log("Scanner: Database connection failed - " . $e->getMessage());
    die("Database connection failed. Please check logs.");
}

// Get current page with modern sanitization
$page = $_GET['page'] ?? 'overview';
$page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
$page = preg_replace('/[^a-z0-9\-_]/', '', $page);
$page = $page ?: 'overview';

// ... rest of the file
PHP;

// ============================================================================
// EXECUTION INSTRUCTIONS
// ============================================================================

echo <<<'TEXT'

╔════════════════════════════════════════════════════════════════════════════╗
║                    SCANNER APPLICATION - QUICK FIXES                        ║
║                                                                             ║
║  This file contains all code fixes from CODE_ANALYSIS_REPORT.md           ║
║                                                                             ║
║  TO APPLY FIXES:                                                           ║
║  1. Backup current files: cp index.php index.php.backup                    ║
║  2. Apply fixes manually using code snippets above                         ║
║  3. Test each fix individually                                             ║
║  4. Run: php pre-flight-check.sh to verify                                 ║
║                                                                             ║
║  CRITICAL FIXES (Must do first):                                           ║
║  ✓ Fix #1 & #2: Bootstrap CSS and JS                                       ║
║  ✓ Fix #3: FILTER_SANITIZE_STRING                                         ║
║  ✓ Fix #4: Create logs directory                                          ║
║  ✓ Fix #5: Authentication (dev mode is okay for now)                      ║
║                                                                             ║
║  SECURITY FIXES (Before production):                                       ║
║  ✓ Fix #7: CSRF protection                                                 ║
║  ✓ Fix #5: Proper authentication                                          ║
║                                                                             ║
║  PERFORMANCE FIXES:                                                        ║
║  ✓ Fix #8: Cache violation count                                           ║
║                                                                             ║
║  UX IMPROVEMENTS:                                                          ║
║  ✓ Fix #9: Project validation with notifications                           ║
║  ✓ Fix #10: Mobile sidebar overlay                                        ║
║  ✓ Fix #11: Enhanced search                                               ║
║  ✓ Fix #12: Notification API                                              ║
║                                                                             ║
╚════════════════════════════════════════════════════════════════════════════╝

TEXT;
