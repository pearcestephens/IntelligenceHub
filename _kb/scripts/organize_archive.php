#!/usr/bin/env php
<?php
/**
 * Archive Organizer - Organize 1500+ historical docs into year-based structure
 * 
 * Usage:
 *   php organize_archive.php --analyze          # Dry run, show what would happen
 *   php organize_archive.php --execute          # Actually move files
 *   php organize_archive.php --index            # Generate index files
 * 
 * @package IntelligenceHub
 * @author Pearce Stephens
 */

declare(strict_types=1);

// Configuration
$config = [
    'source_dir' => '/home/master/applications/hdgwrzntwa/public_html',
    'archive_dir' => '/home/master/applications/hdgwrzntwa/public_html/_kb/archive',
    'backup_dir' => '/home/master/applications/hdgwrzntwa/public_html/_kb/backups/pre-archive',
    'file_patterns' => ['*.md', '*.txt'],
    'exclude_dirs' => ['_kb', 'vendor', 'node_modules', '.git'],
    'categories' => [
        'decision' => ['decision', 'adr', 'architecture decision'],
        'project' => ['project', 'implementation', 'feature'],
        'incident' => ['incident', 'postmortem', 'outage', 'bug'],
        'migration' => ['migration', 'upgrade', 'schema'],
        'audit' => ['audit', 'review', 'analysis'],
        'report' => ['report', 'summary', 'status'],
    ],
    'systems' => ['cis', 'retail', 'wholesale', 'intelligence-hub', 'mcp'],
];

// Parse command line
$mode = $argv[1] ?? '--help';

if ($mode === '--help') {
    showHelp();
    exit(0);
}

echo "============================================\n";
echo "Archive Organizer\n";
echo "============================================\n\n";

// Step 1: Find all markdown files in root
echo "Step 1: Finding files to archive...\n";
$files = findFilesToArchive($config);
echo "Found: " . count($files) . " files\n\n";

if (empty($files)) {
    echo "No files to archive. Exiting.\n";
    exit(0);
}

// Step 2: Analyze each file
echo "Step 2: Analyzing files...\n";
$analysis = analyzeFiles($files, $config);
echo "Analysis complete.\n\n";

// Step 3: Show summary
showSummary($analysis);

if ($mode === '--analyze') {
    echo "\n✅ Dry run complete. Use --execute to move files.\n";
    exit(0);
}

if ($mode === '--execute') {
    echo "\nStep 3: Creating backup...\n";
    createBackup($files, $config);
    
    echo "\nStep 4: Creating archive structure...\n";
    createArchiveStructure($analysis, $config);
    
    echo "\nStep 5: Moving files...\n";
    moveFilesToArchive($analysis, $config);
    
    echo "\nStep 6: Generating index files...\n";
    generateIndexFiles($analysis, $config);
    
    echo "\n✅ Archive organization complete!\n";
    echo "Files moved to: {$config['archive_dir']}\n";
    echo "Backup saved to: {$config['backup_dir']}\n";
}

if ($mode === '--index') {
    echo "\nGenerating index files only...\n";
    $existingFiles = scanArchive($config['archive_dir']);
    generateIndexFiles($existingFiles, $config);
    echo "\n✅ Index files generated.\n";
}

// ============================================================================
// FUNCTIONS
// ============================================================================

function showHelp(): void
{
    echo <<<HELP
Archive Organizer - Organize historical documentation

Usage:
  php organize_archive.php [MODE]

Modes:
  --analyze    Dry run - show what would happen (safe)
  --execute    Actually move files to archive (creates backup first)
  --index      Generate index files for existing archive
  --help       Show this help

Examples:
  php organize_archive.php --analyze
  php organize_archive.php --execute
  php organize_archive.php --index

Output Structure:
  _kb/archive/
  ├── 2023/
  │   ├── 2023-01-15_decision_api-versioning.md
  │   └── 2023-03-22_project_dashboard.md
  ├── 2024/
  │   └── 2024-06-10_incident_outage.md
  └── 2025/
      └── 2025-11-02_report_documentation-complete.md

HELP;
}

function findFilesToArchive(array $config): array
{
    $files = [];
    
    // Find all .md files in root directory (not in subdirs)
    $iterator = new FilesystemIterator($config['source_dir']);
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $filename = $file->getFilename();
            
            // Exclude README.md
            if ($filename === 'README.md') {
                continue;
            }
            
            // Exclude files already in proper format (YYYY-MM-DD_category_title.md)
            if (preg_match('/^\d{4}-\d{2}-\d{2}_\w+_/', $filename)) {
                continue;
            }
            
            $files[] = $file->getPathname();
        }
    }
    
    return $files;
}

function analyzeFiles(array $files, array $config): array
{
    $analysis = [];
    
    foreach ($files as $filePath) {
        $filename = basename($filePath);
        $content = file_get_contents($filePath);
        $mtime = filemtime($filePath);
        
        // Determine category
        $category = detectCategory($filename, $content, $config);
        
        // Determine system
        $system = detectSystem($filename, $content, $config);
        
        // Determine date (from file or mtime)
        $date = detectDate($filename, $content, $mtime);
        
        // Generate new filename
        $newFilename = generateFilename($date, $category, $filename);
        
        $analysis[] = [
            'original_path' => $filePath,
            'original_name' => $filename,
            'category' => $category,
            'system' => $system,
            'date' => $date,
            'year' => date('Y', $date),
            'new_filename' => $newFilename,
            'new_path' => $config['archive_dir'] . '/' . date('Y', $date) . '/' . $newFilename,
        ];
    }
    
    return $analysis;
}

function detectCategory(string $filename, string $content, array $config): string
{
    $filenameLower = strtolower($filename);
    $contentLower = strtolower(substr($content, 0, 500)); // Check first 500 chars
    
    foreach ($config['categories'] as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($filenameLower, strtolower($keyword)) !== false) {
                return $category;
            }
            if (strpos($contentLower, strtolower($keyword)) !== false) {
                return $category;
            }
        }
    }
    
    return 'general';
}

function detectSystem(string $filename, string $content, array $config): ?string
{
    $filenameLower = strtolower($filename);
    $contentLower = strtolower(substr($content, 0, 500));
    
    foreach ($config['systems'] as $system) {
        if (strpos($filenameLower, strtolower($system)) !== false) {
            return $system;
        }
        if (strpos($contentLower, strtolower($system)) !== false) {
            return $system;
        }
    }
    
    return null;
}

function detectDate(string $filename, string $content, int $mtime): int
{
    // Try to extract date from filename (YYYY-MM-DD or YYYYMMDD)
    if (preg_match('/(\d{4})-?(\d{2})-?(\d{2})/', $filename, $match)) {
        return strtotime("{$match[1]}-{$match[2]}-{$match[3]}");
    }
    
    // Try to extract date from content (first 1000 chars)
    $contentPreview = substr($content, 0, 1000);
    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $contentPreview, $match)) {
        return strtotime("{$match[1]}-{$match[2]}-{$match[3]}");
    }
    
    // Fallback to file modification time
    return $mtime;
}

function generateFilename(int $timestamp, string $category, string $originalName): string
{
    $date = date('Y-m-d', $timestamp);
    
    // Clean original name
    $cleanName = preg_replace('/^[A-Z_]+/', '', $originalName); // Remove prefix
    $cleanName = preg_replace('/\.md$/', '', $cleanName); // Remove extension
    $cleanName = strtolower($cleanName);
    $cleanName = preg_replace('/[^a-z0-9-]/', '-', $cleanName); // Alphanumeric + hyphens
    $cleanName = preg_replace('/-+/', '-', $cleanName); // Remove duplicate hyphens
    $cleanName = trim($cleanName, '-');
    
    // Limit length
    if (strlen($cleanName) > 60) {
        $cleanName = substr($cleanName, 0, 60);
    }
    
    return "{$date}_{$category}_{$cleanName}.md";
}

function showSummary(array $analysis): void
{
    echo "============================================\n";
    echo "Analysis Summary\n";
    echo "============================================\n\n";
    
    // Group by year
    $byYear = [];
    foreach ($analysis as $item) {
        $byYear[$item['year']][] = $item;
    }
    
    echo "Files by Year:\n";
    foreach ($byYear as $year => $items) {
        echo "  {$year}: " . count($items) . " files\n";
    }
    echo "\n";
    
    // Group by category
    $byCategory = [];
    foreach ($analysis as $item) {
        $byCategory[$item['category']][] = $item;
    }
    
    echo "Files by Category:\n";
    foreach ($byCategory as $category => $items) {
        echo "  {$category}: " . count($items) . " files\n";
    }
    echo "\n";
    
    // Show first 10 examples
    echo "First 10 Files:\n";
    foreach (array_slice($analysis, 0, 10) as $item) {
        echo "  {$item['original_name']}\n";
        echo "    → {$item['new_filename']}\n";
    }
    
    if (count($analysis) > 10) {
        echo "  ... and " . (count($analysis) - 10) . " more\n";
    }
}

function createBackup(array $files, array $config): void
{
    $backupDir = $config['backup_dir'] . '/' . date('Y-m-d_His');
    
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    foreach ($files as $file) {
        $dest = $backupDir . '/' . basename($file);
        copy($file, $dest);
    }
    
    echo "Backup created: {$backupDir}\n";
    echo "Backed up: " . count($files) . " files\n";
}

function createArchiveStructure(array $analysis, array $config): void
{
    $years = array_unique(array_column($analysis, 'year'));
    
    foreach ($years as $year) {
        $yearDir = $config['archive_dir'] . '/' . $year;
        if (!is_dir($yearDir)) {
            mkdir($yearDir, 0755, true);
            echo "Created: {$yearDir}\n";
        }
    }
}

function moveFilesToArchive(array $analysis, array $config): void
{
    $moved = 0;
    $errors = 0;
    
    foreach ($analysis as $item) {
        $newDir = dirname($item['new_path']);
        
        if (!is_dir($newDir)) {
            mkdir($newDir, 0755, true);
        }
        
        if (rename($item['original_path'], $item['new_path'])) {
            $moved++;
            echo "  ✓ {$item['original_name']} → {$item['new_filename']}\n";
        } else {
            $errors++;
            echo "  ✗ Failed to move: {$item['original_name']}\n";
        }
    }
    
    echo "\nMoved: {$moved} files\n";
    if ($errors > 0) {
        echo "Errors: {$errors} files\n";
    }
}

function generateIndexFiles(array $analysis, array $config): void
{
    // Generate master index
    $masterIndex = generateMasterIndex($analysis);
    file_put_contents($config['archive_dir'] . '/INDEX.md', $masterIndex);
    echo "Generated: INDEX.md\n";
    
    // Generate per-year indexes
    $byYear = [];
    foreach ($analysis as $item) {
        $byYear[$item['year']][] = $item;
    }
    
    foreach ($byYear as $year => $items) {
        $yearIndex = generateYearIndex($year, $items);
        file_put_contents($config['archive_dir'] . "/{$year}/INDEX.md", $yearIndex);
        echo "Generated: {$year}/INDEX.md\n";
    }
}

function generateMasterIndex(array $analysis): string
{
    $byYear = [];
    $byCategory = [];
    
    foreach ($analysis as $item) {
        $byYear[$item['year']][] = $item;
        $byCategory[$item['category']][] = $item;
    }
    
    $content = "# Historical Documentation Archive\n\n";
    $content .= "**Generated:** " . date('Y-m-d H:i:s') . "\n";
    $content .= "**Total Files:** " . count($analysis) . "\n\n";
    $content .= "---\n\n";
    
    $content .= "## By Year\n\n";
    foreach ($byYear as $year => $items) {
        $content .= "- [{$year}]({$year}/) - " . count($items) . " files\n";
    }
    $content .= "\n";
    
    $content .= "## By Category\n\n";
    foreach ($byCategory as $category => $items) {
        $content .= "### " . ucfirst($category) . " (" . count($items) . " files)\n\n";
        foreach (array_slice($items, 0, 5) as $item) {
            $content .= "- [{$item['new_filename']}]({$item['year']}/{$item['new_filename']})\n";
        }
        if (count($items) > 5) {
            $content .= "- ... and " . (count($items) - 5) . " more\n";
        }
        $content .= "\n";
    }
    
    $content .= "## Search\n\n";
    $content .= "```bash\n";
    $content .= "# Find by category\n";
    $content .= "find . -name \"*_decision_*\"\n\n";
    $content .= "# Find by keyword\n";
    $content .= "grep -r \"search term\" .\n";
    $content .= "```\n";
    
    return $content;
}

function generateYearIndex(string $year, array $items): string
{
    $content = "# Archive: {$year}\n\n";
    $content .= "**Files:** " . count($items) . "\n\n";
    $content .= "---\n\n";
    
    // Group by category
    $byCategory = [];
    foreach ($items as $item) {
        $byCategory[$item['category']][] = $item;
    }
    
    foreach ($byCategory as $category => $categoryItems) {
        $content .= "## " . ucfirst($category) . "\n\n";
        foreach ($categoryItems as $item) {
            $content .= "- [{$item['new_filename']}]({$item['new_filename']})\n";
        }
        $content .= "\n";
    }
    
    return $content;
}

function scanArchive(string $archiveDir): array
{
    // Scan existing archive and return analysis-like structure
    $files = [];
    
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archiveDir)) as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $filename = $file->getFilename();
            
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})_(\w+)_(.+)\.md$/', $filename, $match)) {
                $files[] = [
                    'original_path' => $file->getPathname(),
                    'original_name' => $filename,
                    'category' => $match[4],
                    'system' => null,
                    'date' => strtotime("{$match[1]}-{$match[2]}-{$match[3]}"),
                    'year' => $match[1],
                    'new_filename' => $filename,
                    'new_path' => $file->getPathname(),
                ];
            }
        }
    }
    
    return $files;
}
