#!/usr/bin/env php
<?php
/**
 * Environment Variable Standardization Script
 *
 * Standardizes all environment variable names across the entire codebase
 * to use consistent naming conventions.
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(strict_types=1);

// Standardization mappings (OLD => NEW)
$standardization = [
    // Database variables
    'DB_DATABASE' => 'DB_NAME',
    'DB_USERNAME' => 'DB_USER',
    'DB_PASSWORD' => 'DB_PASS',
    'DB_CHARSET' => 'DB_CHARSET', // Keep

    // Redis variables
    'REDIS_DB' => 'REDIS_DATABASE',

    // MCP variables
    'MCP_ENDPOINT' => 'MCP_SERVER_URL',
    'MCP_AUTH_TOKEN' => 'MCP_API_KEY',

    // AI Agent variables
    'AGENT_BASE' => 'AI_AGENT_BASE_URL',

    // Logging
    'LOG_PATH' => 'LOG_DIRECTORY',
];

$scanDirs = [
    '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html'
];

$filesToUpdate = [];
$changes = [];

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  ENVIRONMENT VARIABLE STANDARDIZATION SCRIPT                         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

echo "Phase 1: Scanning for files with environment variables...\n\n";

// Scan for PHP files
function scanDirectory(string $dir, array &$files, int $depth = 0): void {
    if ($depth > 10) return;
    if (!is_dir($dir)) return;

    $items = @scandir($dir);
    if (!$items) return;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            if (in_array($item, ['vendor', 'node_modules', '.git', 'cache', 'logs', 'tmp'])) {
                continue;
            }
            scanDirectory($path, $files, $depth + 1);
        } elseif (is_file($path) && preg_match('/\.php$/i', $path)) {
            $files[] = $path;
        }
    }
}

foreach ($scanDirs as $dir) {
    scanDirectory($dir, $filesToUpdate);
}

echo "Found " . count($filesToUpdate) . " PHP files\n\n";

echo "Phase 2: Analyzing and updating files...\n\n";

$updatedFiles = 0;
$totalChanges = 0;

foreach ($filesToUpdate as $file) {
    $content = @file_get_contents($file);
    if ($content === false) continue;

    $originalContent = $content;
    $fileChanges = 0;

    foreach ($standardization as $old => $new) {
        // Pattern 1: $_ENV['OLD']
        $pattern1 = '/\$_ENV\[[\'"]' . preg_quote($old, '/') . '[\'"]\]/';
        $replace1 = "\$_ENV['$new']";
        if (preg_match($pattern1, $content)) {
            $content = preg_replace($pattern1, $replace1, $content);
            $fileChanges++;
        }

        // Pattern 2: $_SERVER['OLD']
        $pattern2 = '/\$_SERVER\[[\'"]' . preg_quote($old, '/') . '[\'"]\]/';
        $replace2 = "\$_SERVER['$new']";
        if (preg_match($pattern2, $content)) {
            $content = preg_replace($pattern2, $replace2, $content);
            $fileChanges++;
        }

        // Pattern 3: getenv('OLD')
        $pattern3 = '/getenv\([\'"]' . preg_quote($old, '/') . '[\'"]\)/';
        $replace3 = "getenv('$new')";
        if (preg_match($pattern3, $content)) {
            $content = preg_replace($pattern3, $replace3, $content);
            $fileChanges++;
        }

        // Pattern 4: env('OLD')
        $pattern4 = '/env\([\'"]' . preg_quote($old, '/') . '[\'"]\)/';
        $replace4 = "env('$new')";
        if (preg_match($pattern4, $content)) {
            $content = preg_replace($pattern4, $replace4, $content);
            $fileChanges++;
        }
    }

    if ($content !== $originalContent) {
        // Backup original
        $backupFile = $file . '.backup.' . date('Ymd-His');
        @copy($file, $backupFile);

        // Write updated content
        if (@file_put_contents($file, $content)) {
            $updatedFiles++;
            $totalChanges += $fileChanges;

            $relativePath = str_replace('/home/129337.cloudwaysapps.com/hdgwrzntwa/', '', $file);
            echo "✓ Updated: {$relativePath} ({$fileChanges} changes)\n";

            $changes[] = [
                'file' => $file,
                'changes' => $fileChanges
            ];
        }
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  STANDARDIZATION COMPLETE                                            ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

echo "Files Updated: {$updatedFiles}\n";
echo "Total Changes: {$totalChanges}\n";
echo "Backup Files Created: {$updatedFiles}\n\n";

echo "Standardization Mappings Applied:\n";
foreach ($standardization as $old => $new) {
    echo "  {$old} → {$new}\n";
}

echo "\n✓ All environment variables standardized!\n";
echo "✓ Backups saved with .backup.YYYYMMDD-HHMMSS extension\n";
echo "\nNext: Update /private_html/config/.env with standardized names\n";
