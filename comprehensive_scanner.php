<?php
/**
 * GPT Server Comprehensive Scanner & Indexer
 * Scans, indexes, and summarizes all files on hdgwrzntwa server
 * 
 * Usage: php comprehensive_scanner.php [--full] [--quick]
 */

ini_set('memory_limit', '512M');
set_time_limit(300);

class ComprehensiveScanner {
    private $basePath;
    private $config;
    private $stats = [
        'total_files' => 0,
        'total_size' => 0,
        'by_type' => [],
        'by_directory' => [],
        'errors' => []
    ];
    private $fileIndex = [];
    private $startTime;
    
    public function __construct($configFile = 'scanner_config.json') {
        $this->startTime = microtime(true);
        $this->basePath = dirname(__FILE__);
        
        if (file_exists($configFile)) {
            $this->config = json_decode(file_get_contents($configFile), true);
        } else {
            $this->config = $this->getDefaultConfig();
        }
        
        echo "🔍 GPT Server Comprehensive Scanner v1.0.0\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "Base Path: {$this->basePath}\n";
        echo "Server: hdgwrzntwa\n\n";
    }
    
    private function getDefaultConfig() {
        return [
            'scan_patterns' => [
                'include' => ['*.php', '*.js', '*.json', '*.md', '*.sql', '*.sh', '*.txt'],
                'exclude' => ['node_modules', 'vendor', '.git', 'cache', 'logs', '.backups']
            ],
            'output' => [
                'index_file' => '.copilot/file_index.json',
                'summary_file' => '.copilot/server_summary.md',
                'stats_file' => '.copilot/scan_stats.json',
                'structure_file' => '.copilot/directory_structure.txt'
            ]
        ];
    }
    
    public function scan($mode = 'full') {
        echo "📂 Starting {$mode} scan...\n\n";
        
        // Scan directory structure
        $this->scanDirectory($this->basePath);
        
        // Generate outputs
        $this->generateFileIndex();
        $this->generateSummary();
        $this->generateStats();
        $this->generateStructure();
        
        $elapsed = round(microtime(true) - $this->startTime, 2);
        
        echo "\n✅ Scan Complete!\n";
        echo "=" . str_repeat("=", 50) . "\n";
        echo "Total Files: {$this->stats['total_files']}\n";
        echo "Total Size: " . $this->formatBytes($this->stats['total_size']) . "\n";
        echo "Time Elapsed: {$elapsed}s\n";
        echo "\n📊 Outputs Generated:\n";
        echo "  • File Index: {$this->config['output']['index_file']}\n";
        echo "  • Summary: {$this->config['output']['summary_file']}\n";
        echo "  • Stats: {$this->config['output']['stats_file']}\n";
        echo "  • Structure: {$this->config['output']['structure_file']}\n";
    }
    
    private function scanDirectory($dir, $depth = 0, $maxDepth = 10) {
        if ($depth > $maxDepth) return;
        
        $items = @scandir($dir);
        if (!$items) return;
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            $relativePath = str_replace($this->basePath . '/', '', $path);
            
            // Check exclusions
            if ($this->shouldExclude($relativePath)) continue;
            
            if (is_dir($path)) {
                $this->scanDirectory($path, $depth + 1, $maxDepth);
            } else {
                $this->processFile($path, $relativePath);
            }
        }
    }
    
    private function shouldExclude($path) {
        $excludes = $this->config['scan_patterns']['exclude'] ?? [];
        foreach ($excludes as $exclude) {
            if (strpos($path, $exclude) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function processFile($fullPath, $relativePath) {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $size = @filesize($fullPath);
        $modified = @filemtime($fullPath);
        
        // Update stats
        $this->stats['total_files']++;
        $this->stats['total_size'] += $size;
        
        if (!isset($this->stats['by_type'][$ext])) {
            $this->stats['by_type'][$ext] = ['count' => 0, 'size' => 0];
        }
        $this->stats['by_type'][$ext]['count']++;
        $this->stats['by_type'][$ext]['size'] += $size;
        
        $dir = dirname($relativePath);
        if (!isset($this->stats['by_directory'][$dir])) {
            $this->stats['by_directory'][$dir] = 0;
        }
        $this->stats['by_directory'][$dir]++;
        
        // Extract metadata
        $metadata = $this->extractMetadata($fullPath, $ext);
        
        // Add to index
        $this->fileIndex[] = [
            'path' => $relativePath,
            'type' => $ext,
            'size' => $size,
            'modified' => $modified,
            'modified_date' => date('Y-m-d H:i:s', $modified),
            'metadata' => $metadata
        ];
        
        // Progress indicator
        if ($this->stats['total_files'] % 100 === 0) {
            echo "  Scanned {$this->stats['total_files']} files...\r";
        }
    }
    
    private function extractMetadata($path, $ext) {
        $metadata = [];
        
        switch ($ext) {
            case 'php':
                $metadata = $this->extractPhpMetadata($path);
                break;
            case 'js':
                $metadata = $this->extractJsMetadata($path);
                break;
            case 'json':
                $metadata = $this->extractJsonMetadata($path);
                break;
            case 'md':
                $metadata = $this->extractMarkdownMetadata($path);
                break;
        }
        
        return $metadata;
    }
    
    private function extractPhpMetadata($path) {
        $content = @file_get_contents($path);
        if (!$content) return [];
        
        $metadata = [
            'lines' => substr_count($content, "\n"),
            'classes' => [],
            'functions' => []
        ];
        
        // Extract classes
        if (preg_match_all('/class\s+(\w+)/i', $content, $matches)) {
            $metadata['classes'] = $matches[1];
        }
        
        // Extract functions
        if (preg_match_all('/function\s+(\w+)/i', $content, $matches)) {
            $metadata['functions'] = array_slice($matches[1], 0, 10); // Limit to 10
        }
        
        return $metadata;
    }
    
    private function extractJsMetadata($path) {
        $content = @file_get_contents($path);
        if (!$content) return [];
        
        return [
            'lines' => substr_count($content, "\n"),
            'has_jquery' => stripos($content, 'jquery') !== false,
            'has_vue' => stripos($content, 'Vue') !== false,
            'has_react' => stripos($content, 'React') !== false
        ];
    }
    
    private function extractJsonMetadata($path) {
        $content = @file_get_contents($path);
        if (!$content) return [];
        
        $json = @json_decode($content, true);
        if (!$json) return ['valid' => false];
        
        return [
            'valid' => true,
            'keys' => array_keys($json),
            'depth' => $this->getJsonDepth($json)
        ];
    }
    
    private function extractMarkdownMetadata($path) {
        $content = @file_get_contents($path);
        if (!$content) return [];
        
        return [
            'lines' => substr_count($content, "\n"),
            'headings' => substr_count($content, '#'),
            'has_code_blocks' => stripos($content, '```') !== false
        ];
    }
    
    private function getJsonDepth($array, $depth = 0) {
        if (!is_array($array)) return $depth;
        
        $maxDepth = $depth;
        foreach ($array as $value) {
            if (is_array($value)) {
                $maxDepth = max($maxDepth, $this->getJsonDepth($value, $depth + 1));
            }
        }
        return $maxDepth;
    }
    
    private function generateFileIndex() {
        $outputPath = $this->basePath . '/' . $this->config['output']['index_file'];
        $this->ensureDirectory(dirname($outputPath));
        
        $index = [
            'generated' => date('Y-m-d H:i:s'),
            'server' => 'hdgwrzntwa',
            'base_path' => $this->basePath,
            'total_files' => $this->stats['total_files'],
            'files' => $this->fileIndex
        ];
        
        file_put_contents($outputPath, json_encode($index, JSON_PRETTY_PRINT));
    }
    
    private function generateSummary() {
        $outputPath = $this->basePath . '/' . $this->config['output']['summary_file'];
        $this->ensureDirectory(dirname($outputPath));
        
        $summary = "# GPT Server (hdgwrzntwa) - File Summary\n\n";
        $summary .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
        $summary .= "## 📊 Overview\n\n";
        $summary .= "- **Total Files:** " . number_format($this->stats['total_files']) . "\n";
        $summary .= "- **Total Size:** " . $this->formatBytes($this->stats['total_size']) . "\n";
        $summary .= "- **Base Path:** `{$this->basePath}`\n\n";
        
        $summary .= "## 📁 Files by Type\n\n";
        arsort($this->stats['by_type']);
        foreach (array_slice($this->stats['by_type'], 0, 20, true) as $ext => $data) {
            $ext = $ext ?: 'no extension';
            $summary .= sprintf(
                "- **%s**: %s files (%s)\n",
                $ext,
                number_format($data['count']),
                $this->formatBytes($data['size'])
            );
        }
        
        $summary .= "\n## 📂 Top Directories\n\n";
        arsort($this->stats['by_directory']);
        foreach (array_slice($this->stats['by_directory'], 0, 20, true) as $dir => $count) {
            $summary .= sprintf("- `%s`: %s files\n", $dir, number_format($count));
        }
        
        $summary .= "\n## 🔍 Notable Files\n\n";
        $summary .= $this->findNotableFiles();
        
        file_put_contents($outputPath, $summary);
    }
    
    private function findNotableFiles() {
        $notable = "";
        
        // Find largest files
        $bySize = $this->fileIndex;
        usort($bySize, function($a, $b) {
            return $b['size'] - $a['size'];
        });
        
        $notable .= "### Largest Files\n\n";
        foreach (array_slice($bySize, 0, 10) as $file) {
            $notable .= sprintf(
                "- `%s` - %s\n",
                $file['path'],
                $this->formatBytes($file['size'])
            );
        }
        
        // Find recently modified
        $byDate = $this->fileIndex;
        usort($byDate, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });
        
        $notable .= "\n### Recently Modified\n\n";
        foreach (array_slice($byDate, 0, 10) as $file) {
            $notable .= sprintf(
                "- `%s` - %s\n",
                $file['path'],
                $file['modified_date']
            );
        }
        
        return $notable;
    }
    
    private function generateStats() {
        $outputPath = $this->basePath . '/' . $this->config['output']['stats_file'];
        $this->ensureDirectory(dirname($outputPath));
        
        $stats = [
            'generated' => date('Y-m-d H:i:s'),
            'elapsed_seconds' => round(microtime(true) - $this->startTime, 2),
            'stats' => $this->stats
        ];
        
        file_put_contents($outputPath, json_encode($stats, JSON_PRETTY_PRINT));
    }
    
    private function generateStructure() {
        $outputPath = $this->basePath . '/' . $this->config['output']['structure_file'];
        $this->ensureDirectory(dirname($outputPath));
        
        $structure = "GPT Server Directory Structure\n";
        $structure .= "=" . str_repeat("=", 50) . "\n\n";
        $structure .= $this->buildTree($this->basePath);
        
        file_put_contents($outputPath, $structure);
    }
    
    private function buildTree($dir, $prefix = "", $maxDepth = 3, $currentDepth = 0) {
        if ($currentDepth >= $maxDepth) return "";
        
        $tree = "";
        $items = @scandir($dir);
        if (!$items) return "";
        
        $items = array_filter($items, function($item) use ($dir) {
            if ($item === '.' || $item === '..') return false;
            $path = str_replace($this->basePath . '/', '', $dir . '/' . $item);
            return !$this->shouldExclude($path);
        });
        
        $items = array_values($items);
        $count = count($items);
        
        foreach ($items as $i => $item) {
            $isLast = ($i === $count - 1);
            $marker = $isLast ? "└── " : "├── ";
            $path = $dir . '/' . $item;
            
            if (is_dir($path)) {
                $tree .= $prefix . $marker . $item . "/\n";
                $newPrefix = $prefix . ($isLast ? "    " : "│   ");
                $tree .= $this->buildTree($path, $newPrefix, $maxDepth, $currentDepth + 1);
            } else {
                $tree .= $prefix . $marker . $item . "\n";
            }
        }
        
        return $tree;
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function ensureDirectory($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

// Run scanner
$mode = isset($argv[1]) && $argv[1] === '--quick' ? 'quick' : 'full';
$scanner = new ComprehensiveScanner();
$scanner->scan($mode);

echo "\n🎉 Server scan complete! All files indexed and summarized.\n";
