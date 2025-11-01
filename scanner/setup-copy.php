<?php
/**
 * Scanner Setup - Copy V2 Pages Script
 *
 * This script copies all pages-v2 files to scanner/pages
 * and updates any path references
 */

declare(strict_types=1);

$sourceDir = __DIR__ . '/../dashboard/admin/pages-v2';
$destDir = __DIR__ . '/pages';
$assetsSourceCSS = __DIR__ . '/../dashboard/admin/assets/css';
$assetsSourceJS = __DIR__ . '/../dashboard/admin/assets/js';
$assetsDestCSS = __DIR__ . '/assets/css';
$assetsDestJS = __DIR__ . '/assets/js';

echo "========================================\n";
echo "Scanner Setup - Copying V2 Files\n";
echo "========================================\n\n";

// Create directories if they don't exist
if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}
if (!is_dir($assetsDestCSS)) {
    mkdir($assetsDestCSS, 0755, true);
}
if (!is_dir($assetsDestJS)) {
    mkdir($assetsDestJS, 0755, true);
}

// Copy page files
echo "→ Copying page files...\n";
$pageFiles = glob($sourceDir . '/*.php');
$copiedPages = 0;

foreach ($pageFiles as $sourceFile) {
    $filename = basename($sourceFile);
    $destFile = $destDir . '/' . $filename;

    // Read source file
    $content = file_get_contents($sourceFile);

    // Remove the old header/footer requires since we handle that in index.php now
    $content = preg_replace(
        '/require_once __DIR__ \. \'\/\.\.\/includes-v2\/(header|footer|sidebar)\.php\';/',
        '// Layout handled by index.php',
        $content
    );

    // Remove the app.php require since it's already loaded
    $content = preg_replace(
        '/require_once \$_SERVER\[\'DOCUMENT_ROOT\'\] \. \'\/app\.php\';/',
        '// Bootstrap already loaded by index.php',
        $content
    );

    // Remove project selector require
    $content = preg_replace(
        '/require_once \$_SERVER\[\'DOCUMENT_ROOT\'\] \. \'\/dashboard\/admin\/includes\/project-selector\.php\';/',
        '// Project context handled by index.php',
        $content
    );

    // Write modified content
    file_put_contents($destFile, $content);
    echo "  ✓ $filename\n";
    $copiedPages++;
}

// Copy CSS files
echo "\n→ Copying CSS files...\n";
$cssFiles = glob($assetsSourceCSS . '/*.css');
$copiedCSS = 0;

foreach ($cssFiles as $sourceFile) {
    $filename = basename($sourceFile);
    $destFile = $assetsDestCSS . '/' . $filename;

    if (copy($sourceFile, $destFile)) {
        echo "  ✓ $filename\n";
        $copiedCSS++;
    }
}

// Copy JS files
echo "\n→ Copying JS files...\n";
$jsFiles = glob($assetsSourceJS . '/*.js');
$copiedJS = 0;

foreach ($jsFiles as $sourceFile) {
    $filename = basename($sourceFile);
    $destFile = $assetsDestJS . '/' . $filename;

    if (copy($sourceFile, $destFile)) {
        echo "  ✓ $filename\n";
        $copiedJS++;
    }
}

// Summary
echo "\n========================================\n";
echo "Copy Complete!\n";
echo "========================================\n\n";
echo "Copied $copiedPages page files\n";
echo "Copied $copiedCSS CSS files\n";
echo "Copied $copiedJS JS files\n\n";

echo "Pages in scanner/pages/:\n";
foreach (glob($destDir . '/*.php') as $file) {
    echo "  • " . basename($file) . "\n";
}

echo "\n✅ Scanner application is ready!\n";
echo "📍 Access at: https://[your-domain]/scanner/\n\n";
