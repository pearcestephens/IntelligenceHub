#!/usr/bin/env php
<?php
/**
 * Automated Fix Application Script
 *
 * Automatically applies critical fixes to Scanner application
 *
 * Usage: php apply-fixes.php [--dry-run] [--all] [--critical-only]
 *
 * @package Scanner
 * @version 3.0.0
 */

declare(strict_types=1);

// Parse command line arguments
$options = getopt('', ['dry-run', 'all', 'critical-only', 'help']);
$dryRun = isset($options['dry-run']);
$applyAll = isset($options['all']);
$criticalOnly = isset($options['critical-only']) || (!$applyAll);
$showHelp = isset($options['help']);

if ($showHelp) {
    showHelp();
    exit(0);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║           SCANNER APPLICATION - AUTOMATED FIX APPLICATION SCRIPT           ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No files will be modified\n\n";
}

// Define file paths
$indexFile = __DIR__ . '/index.php';
$sidebarFile = __DIR__ . '/includes/sidebar.php';
$navbarFile = __DIR__ . '/includes/navbar.php';
$logsDir = __DIR__ . '/logs';

// Backup counter
$backupsCreated = 0;
$fixesApplied = 0;
$errors = [];

// ============================================================================
// FIX 1 & 2: Bootstrap CSS and JavaScript
// ============================================================================

echo "🔧 Fix #1 & #2: Adding Bootstrap CSS and JavaScript...\n";

if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);
    $modified = false;

    // Check if Bootstrap CSS already exists
    if (strpos($content, 'bootstrap@5.3.2/dist/css/bootstrap.min.css') === false) {
        // Find Bootstrap Icons line and add Bootstrap CSS before it
        $bootstrapIconsPattern = '/<!-- Bootstrap Icons CDN -->/';

        if (preg_match($bootstrapIconsPattern, $content)) {
            $bootstrapCSS = <<<'HTML'
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Bootstrap Icons CDN -->
HTML;

            $content = preg_replace($bootstrapIconsPattern, $bootstrapCSS, $content);
            $modified = true;
            echo "   ✓ Added Bootstrap CSS\n";
        } else {
            $errors[] = "Could not find Bootstrap Icons comment to insert Bootstrap CSS";
        }
    } else {
        echo "   ⊙ Bootstrap CSS already present\n";
    }

    // Check if Bootstrap JS already exists
    if (strpos($content, 'bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js') === false) {
        // Find Chart.js line and add Bootstrap JS before it
        $chartjsPattern = '/(<!-- Chart\.js CDN -->)/';

        if (preg_match($chartjsPattern, $content)) {
            $bootstrapJS = <<<'HTML'
    <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    $1
HTML;

            $content = preg_replace($chartjsPattern, $bootstrapJS, $content);
            $modified = true;
            echo "   ✓ Added Bootstrap JavaScript\n";
        } else {
            $errors[] = "Could not find Chart.js comment to insert Bootstrap JS";
        }
    } else {
        echo "   ⊙ Bootstrap JavaScript already present\n";
    }

    if ($modified && !$dryRun) {
        createBackup($indexFile);
        file_put_contents($indexFile, $content);
        $fixesApplied++;
    }
} else {
    $errors[] = "index.php not found";
}

echo "\n";

// ============================================================================
// FIX 3: FILTER_SANITIZE_STRING Deprecated
// ============================================================================

echo "🔧 Fix #3: Fixing deprecated FILTER_SANITIZE_STRING...\n";

if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);

    // Check if old filter is present
    if (strpos($content, 'FILTER_SANITIZE_STRING') !== false) {
        $oldCode = "\$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING) ?? 'overview';";
        $newCode = <<<'PHP'
// Modern approach for PHP 8.1+
$page = $_GET['page'] ?? 'overview';
$page = htmlspecialchars($page, ENT_QUOTES, 'UTF-8');
$page = preg_replace('/[^a-z0-9\-_]/', '', $page);
$page = $page ?: 'overview';
PHP;

        if (strpos($content, $oldCode) !== false) {
            $content = str_replace($oldCode, $newCode, $content);

            if (!$dryRun) {
                if ($backupsCreated === 0) createBackup($indexFile);
                file_put_contents($indexFile, $content);
                $fixesApplied++;
            }
            echo "   ✓ Fixed deprecated filter\n";
        } else {
            echo "   ⊙ Filter code structure different, manual fix recommended\n";
        }
    } else {
        echo "   ⊙ Deprecated filter not found (already fixed or different structure)\n";
    }
} else {
    $errors[] = "index.php not found";
}

echo "\n";

// ============================================================================
// FIX 4: Create Logs Directory
// ============================================================================

echo "🔧 Fix #4: Creating logs directory...\n";

if (!is_dir($logsDir)) {
    if (!$dryRun) {
        mkdir($logsDir, 0755, true);

        // Create .htaccess to prevent web access
        file_put_contents($logsDir . '/.htaccess', "Require all denied\n");

        // Create empty log file
        touch($logsDir . '/php_errors.log');
        chmod($logsDir . '/php_errors.log', 0644);

        $fixesApplied++;
    }
    echo "   ✓ Created logs directory\n";
    echo "   ✓ Created .htaccess protection\n";
    echo "   ✓ Created php_errors.log file\n";
} else {
    echo "   ⊙ Logs directory already exists\n";

    // Check for .htaccess
    if (!file_exists($logsDir . '/.htaccess')) {
        if (!$dryRun) {
            file_put_contents($logsDir . '/.htaccess', "Require all denied\n");
        }
        echo "   ✓ Added .htaccess protection\n";
    }
}

echo "\n";

// ============================================================================
// FIX 5: Update Error Logging Configuration
// ============================================================================

echo "🔧 Fix #5: Updating error logging configuration...\n";

if (file_exists($indexFile)) {
    $content = file_get_contents($indexFile);

    // Check if old error logging is present
    $oldPattern = "/ini_set\('error_log',\s*__DIR__\s*\.\s*'\/logs\/php_errors\.log'\);/";

    if (preg_match($oldPattern, $content)) {
        $newCode = <<<'PHP'
// Ensure logs directory exists
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/.htaccess', "Require all denied\n");
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/php_errors.log');
PHP;

        $content = preg_replace(
            "/ini_set\('log_errors',\s*'1'\);\s*\n\s*ini_set\('error_log',\s*__DIR__\s*\.\s*'\/logs\/php_errors\.log'\);/",
            $newCode,
            $content
        );

        if (!$dryRun) {
            if ($backupsCreated === 0) createBackup($indexFile);
            file_put_contents($indexFile, $content);
            $fixesApplied++;
        }
        echo "   ✓ Updated error logging configuration\n";
    } else {
        echo "   ⊙ Error logging configuration not found or already updated\n";
    }
}

echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                              FIX SUMMARY                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

if ($dryRun) {
    echo "📋 DRY RUN COMPLETE\n";
    echo "   Changes that would be made: " . $fixesApplied . "\n";
} else {
    echo "✅ FIXES APPLIED: $fixesApplied\n";
    echo "📦 BACKUPS CREATED: $backupsCreated\n";
}

if (!empty($errors)) {
    echo "⚠️  ERRORS ENCOUNTERED: " . count($errors) . "\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

echo "\n";

if (!$dryRun && $fixesApplied > 0) {
    echo "📝 NEXT STEPS:\n";
    echo "   1. Review changes in affected files\n";
    echo "   2. Test the application: http://your-domain/scanner/\n";
    echo "   3. Check logs directory: /scanner/logs/\n";
    echo "   4. If issues occur, restore from backups (.backup files)\n";
    echo "\n";
}

if ($dryRun) {
    echo "💡 TIP: Run without --dry-run to apply fixes\n";
    echo "   Example: php apply-fixes.php\n";
    echo "\n";
}

echo "✅ Done!\n\n";

exit(empty($errors) ? 0 : 1);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function createBackup(string $file): void {
    global $backupsCreated, $dryRun;

    if ($dryRun) return;

    $backupFile = $file . '.backup.' . date('Y-m-d_His');
    if (copy($file, $backupFile)) {
        $backupsCreated++;
        echo "   💾 Created backup: " . basename($backupFile) . "\n";
    }
}

function showHelp(): void {
    echo <<<'HELP'

Scanner Application - Automated Fix Application Script

USAGE:
    php apply-fixes.php [OPTIONS]

OPTIONS:
    --dry-run           Show what would be changed without modifying files
    --critical-only     Apply only critical fixes (default)
    --all               Apply all fixes (critical + optional)
    --help              Show this help message

EXAMPLES:
    # Preview changes without modifying files
    php apply-fixes.php --dry-run

    # Apply critical fixes only (recommended)
    php apply-fixes.php

    # Apply all fixes
    php apply-fixes.php --all

CRITICAL FIXES APPLIED:
    1. Bootstrap CSS and JavaScript (REQUIRED for UI)
    2. FILTER_SANITIZE_STRING deprecation fix
    3. Logs directory creation
    4. Error logging configuration

BACKUPS:
    All modified files are automatically backed up with .backup.YYYY-MM-DD_HHmmss extension

    To restore:
    cp index.php.backup.YYYY-MM-DD_HHmmss index.php

REQUIREMENTS:
    - PHP 8.0+
    - Write permissions to /scanner/ directory

HELP;
}
