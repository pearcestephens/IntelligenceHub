<?php
/**
 * API-Based Neural Intelligence Scanner
 * 
 * Remote-triggered neural scanner that extracts intelligence from any application
 * Can be called via REST API from any client application
 * 
 * Usage via API:
 *   POST https://gpt.ecigdis.co.nz/api/intelligence/scan
 *   Headers: X-API-Key: master_api_key_2025
 *   Body: {"server": "jcepnzzkmj", "full": true}
 * 
 * Direct execution:
 *   php api_neural_scanner.php --server=jcepnzzkmj --full
 * 
 * @package Intelligence_Scanner
 * @version 2.0.0
 */

// Configuration for each server
define('SERVER_CONFIGS', [
        'jcepnzzkmj' => [
        'name' => 'CIS Staff Portal',
        'domain' => 'staff.vapeshed.co.nz',
        'path' => '/home/master/applications/jcepnzzkmj/public_html',
        'scan_paths' => [
            'assets',
            'modules',
            'modules2',
            'api',
            '_kb'  // Scan the KB folder
        ],
        'ignore_paths' => [
            'vendor',
            'node_modules',
            '.git'
        ],
        'ignore_files' => [
            '_kb/README.md',
            '_kb/API_CLIENT_USAGE.md',
            '_kb/SEARCH_INTERFACE_GUIDE.md',
            '_kb/BOT_COMMANDS.md'
        ]
    ],
        'dvaxgvsxmz' => [
        'name' => 'Vape Shed Retail',
        'domain' => 'vapeshed.co.nz',
        'path' => '/home/master/applications/dvaxgvsxmz/public_html',
        'scan_paths' => [
            'assets',
            'modules',
            '_kb'
        ],
        'ignore_paths' => [
            'vendor',
            'node_modules',
            '.git'
        ],
        'ignore_files' => [
            '_kb/README.md',
            '_kb/API_CLIENT_USAGE.md',
            '_kb/SEARCH_INTERFACE_GUIDE.md',
            '_kb/BOT_COMMANDS.md'
        ]
    ],
    'fhrehrpjmu' => [
        'name' => 'Ecigdis Wholesale',
        'domain' => 'ecigdis.co.nz',
        'path' => '/home/master/applications/fhrehrpjmu/public_html',
        'scan_paths' => [
            'assets',
            'documentation',
            'modules',
            '_kb'
        ],
        'ignore_paths' => [
            'vendor',
            'node_modules',
            '.git'
        ],
        'ignore_files' => [
            '_kb/README.md',
            '_kb/API_CLIENT_USAGE.md',
            '_kb/SEARCH_INTERFACE_GUIDE.md',
            '_kb/BOT_COMMANDS.md'
        ]
    ],
    'hdgwrzntwa' => [
        'name' => 'Intelligence Server',
        'domain' => 'gpt.ecigdis.co.nz',
        'path' => '/home/master/applications/hdgwrzntwa/public_html',
        'scan_paths' => [
            'scripts',
            'documentation',
            'api'
        ],
        'ignore_paths' => [
            'intelligence', // Don't scan the intelligence folder itself!
            'vendor',
            'node_modules',
            '.git'
        ]
    ]
]);

// Intelligence server paths
define('INTELLIGENCE_ROOT', '/home/master/applications/hdgwrzntwa/public_html/intelligence');
define('KB_IGNORE_CONFIG', '/home/master/applications/hdgwrzntwa/public_html/_kb/kb_ignore_config.json');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

/**
 * API-Based Neural Scanner
 */
class APINeuralScanner {
    private $db;
    private $server;
    private $config;
    private $full_scan;
    private $stats = [
        'files_scanned' => 0,
        'docs_extracted' => 0,
        'code_analyzed' => 0,
        'business_intel' => 0,
        'readmes_created' => 0,
        'errors' => []
    ];
    
    private $ignore_patterns = [];
    
    public function __construct($server, $full_scan = false) {
        if (!isset(SERVER_CONFIGS[$server])) {
            throw new Exception("Unknown server: $server");
        }
        
        $this->server = $server;
        $this->config = SERVER_CONFIGS[$server];
        $this->full_scan = $full_scan;
        
        // Connect to database
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Load ignore patterns from database
            $this->loadIgnorePatterns();
            
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Load ignore patterns from database
     */
    private function loadIgnorePatterns() {
        $stmt = $this->db->query("
            SELECT pattern_type, pattern_value 
            FROM scanner_ignore_config 
            WHERE is_active = 1
            ORDER BY priority
        ");
        
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $this->ignore_patterns[$row['pattern_type']][] = $row['pattern_value'];
        }
    }
    
    /**
     * Execute scan
     */
    public function scan() {
        $this->log("🔍 Starting API Neural Scanner for {$this->config['name']}");
        $this->log("📁 Base path: {$this->config['path']}");
        $this->log("🎯 Scan mode: " . ($this->full_scan ? 'FULL' : 'INCREMENTAL'));
        
        // Create intelligence directories if needed
        $this->createIntelligenceStructure();
        
        // Scan each configured path
        foreach ($this->config['scan_paths'] as $scan_path) {
            $full_path = $this->config['path'] . '/' . $scan_path;
            if (is_dir($full_path)) {
                $this->log("📂 Scanning: $scan_path");
                $this->scanDirectory($full_path, $scan_path);
            } else {
                $this->log("⚠️  Path not found: $scan_path");
            }
        }
        
        // Create master README in client KB folder
        $this->createClientKBStructure();
        
        // Generate scan report
        $this->log("\n" . str_repeat('=', 60));
        $this->log("✅ SCAN COMPLETE");
        $this->log(str_repeat('=', 60));
        $this->log("📊 Statistics:");
        $this->log("   Files scanned: {$this->stats['files_scanned']}");
        $this->log("   Documentation extracted: {$this->stats['docs_extracted']}");
        $this->log("   Code intelligence: {$this->stats['code_analyzed']}");
        $this->log("   Business intelligence: {$this->stats['business_intel']}");
        $this->log("   README files created: {$this->stats['readmes_created']}");
        
        if (!empty($this->stats['errors'])) {
            $this->log("\n⚠️  Errors encountered: " . count($this->stats['errors']));
        }
        
        return $this->stats;
    }
    
    /**
     * Create intelligence directory structure
     */
    private function createIntelligenceStructure() {
        $dirs = [
            INTELLIGENCE_ROOT . "/documentation/{$this->server}",
            INTELLIGENCE_ROOT . "/code_intelligence/{$this->server}",
            INTELLIGENCE_ROOT . "/business_intelligence/{$this->server}"
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->log("✅ Created: $dir");
            }
        }
    }
    
    /**
     * Scan directory recursively
     */
    private function scanDirectory($path, $relative_path) {
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $this->stats['files_scanned']++;
                    
                    $file_path = $file->getPathname();
                    $file_relative = str_replace($this->config['path'] . '/', '', $file_path);
                    
                    // Check if should be ignored
                    if ($this->shouldIgnore($file_relative)) {
                        continue;
                    }
                    
                    // Process file based on type
                    $extension = strtolower($file->getExtension());
                    
                    if (in_array($extension, ['md', 'txt', 'rst'])) {
                        $this->extractDocumentation($file, $file_relative);
                    } elseif (in_array($extension, ['php', 'js', 'py', 'java'])) {
                        $this->extractCodeIntelligence($file, $file_relative);
                    } elseif (in_array($extension, ['json', 'yaml', 'yml', 'xml'])) {
                        $this->extractBusinessIntelligence($file, $file_relative);
                    }
                }
            }
        } catch (Exception $e) {
            $this->stats['errors'][] = "Error scanning $path: " . $e->getMessage();
        }
    }
    
    /**
     * Check if path should be ignored (using database config)
     */
    private function shouldIgnore($path) {
        // Check directory patterns
        if (isset($this->ignore_patterns['directory'])) {
            foreach ($this->ignore_patterns['directory'] as $dir_pattern) {
                if (strpos($path, $dir_pattern) !== false) {
                    return true;
                }
            }
        }
        
        // Check filename patterns
        if (isset($this->ignore_patterns['filename_pattern'])) {
            foreach ($this->ignore_patterns['filename_pattern'] as $pattern) {
                // Convert glob pattern to regex
                $regex = str_replace(['*', '?'], ['.*', '.'], $pattern);
                if (preg_match("#$regex#i", basename($path))) {
                    return true;
                }
            }
        }
        
        // Check extensions
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (isset($this->ignore_patterns['extension']) && in_array($ext, $this->ignore_patterns['extension'])) {
            return true;
        }
        
        // Check specific files
        $filename = basename($path);
        if (isset($this->ignore_patterns['file']) && in_array($filename, $this->ignore_patterns['file'])) {
            return true;
        }
        
        // Still check legacy hardcoded ignore_files from config
        if (isset($this->config['ignore_files'])) {
            foreach ($this->config['ignore_files'] as $ignore_file) {
                if ($path === $ignore_file || str_ends_with($path, '/' . $ignore_file)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Extract documentation file - just store in database
     */
    private function extractDocumentation($file, $relative_path) {
        $this->stats['docs_extracted']++;
        
        // Store content in database for AI search
        $this->recordIntelligence(
            $relative_path,
            $file->getPathname(),  // Original file path
            'documentation',
            $file->getSize(),
            $file->getPathname()   // Read from original
        );
    }
    
    /**
     * Categorize document intelligently
     * Categories: introduction, database, code_standards, structure, modules, templating
     */
    private function categorizeDocument($path, $file) {
        $filename = strtolower(basename($file));
        $path_lower = strtolower($path);
        
        // Introduction - README, overview, getting started
        if (preg_match('/(readme|overview|introduction|getting.?started|quickstart)/i', $filename)) {
            return 'introduction';
        }
        
        // Database - schema, migrations, queries
        if (preg_match('/(database|schema|migration|sql|query|table)/i', $path_lower)) {
            return 'database';
        }
        
        // Code Standards - coding style, conventions, best practices
        if (preg_match('/(standard|convention|style|best.?practice|coding|guideline)/i', $path_lower)) {
            return 'code_standards';
        }
        
        // Structure - architecture, system design, file structure
        if (preg_match('/(structure|architecture|design|system|organization)/i', $path_lower)) {
            return 'structure';
        }
        
        // Modules - module-specific docs
        if (preg_match('/(module|component|feature)/i', $path_lower)) {
            return 'modules';
        }
        
        // Templating - views, templates, UI
        if (preg_match('/(template|view|ui|frontend|layout)/i', $path_lower)) {
            return 'templating';
        }
        
        // Default to introduction for uncategorized
        return 'introduction';
    }
    
    /**
     * Extract code intelligence - just store in database
     */
    private function extractCodeIntelligence($file, $relative_path) {
        // Full scan: process ALL files
        // Incremental: only files modified in last 24 hours
        if ($this->full_scan) {
            // FULL SCAN: Process everything
            $this->stats['code_analyzed']++;
            
            $this->recordIntelligence(
                $relative_path,
                $file->getPathname(),
                'code_intelligence',
                $file->getSize(),
                $file->getPathname()
            );
        } elseif ($file->getMTime() > time() - 86400) {
            // INCREMENTAL: Only recent files
            $this->stats['code_analyzed']++;
            
            $this->recordIntelligence(
                $relative_path,
                $file->getPathname(),
                'code_intelligence',
                $file->getSize(),
                $file->getPathname()
            );
        }
    }
    
    /**
     * Extract business intelligence - just store in database
     */
    private function extractBusinessIntelligence($file, $relative_path) {
        $this->stats['business_intel']++;
        
        $this->recordIntelligence(
            $relative_path,
            $file->getPathname(),
            'business_intelligence',
            $file->getSize(),
            $file->getPathname()
        );
    }
    
    /**
     * Record intelligence in database with full content and function extraction
     */
    private function recordIntelligence($original_path, $intelligence_path, $category, $file_size, $source_file_path = null) {
        try {
            // DEBUG: Log call
            static $call_count = 0;
            $call_count++;
            if ($call_count <= 10 && $category === 'code') {
                echo "[DEBUG] recordIntelligence call #$call_count - category: $category, path: $original_path\n";
            }
            
            // Read file content from source file (original location)
            $content = '';
            $read_path = $source_file_path ?: $intelligence_path;
            
            if (file_exists($read_path)) {
                $content = file_get_contents($read_path);
                if ($content === false) {
                    $this->stats['errors'][] = "Failed to read content from: $read_path";
                    $content = '';
                }
            } else {
                $this->stats['errors'][] = "File not found for content reading: $read_path";
            }
            
            // Extract functions if it's a code file
            $functions = [];
            $extension = pathinfo($read_path, PATHINFO_EXTENSION);
            if (!empty($content) && in_array($extension, ['php', 'js', 'py'])) {
                $functions = $this->extractFunctions($content, $extension);
            }
            
            // Prepare intelligence data (metadata)
            $intelligence_data = json_encode([
                'extension' => $extension,
                'lines' => substr_count($content, "\n") + 1,
                'functions' => $functions,
                'function_count' => count($functions),
                'scan_mode' => $this->full_scan ? 'full' : 'incremental',
                'original_path' => $original_path,
                'intelligence_path' => $intelligence_path,
                'extracted_at' => date('Y-m-d H:i:s')
            ]);
            
            // Generate content summary (first 500 chars)
            $summary = substr($content, 0, 500);
            if (strlen($content) > 500) {
                $summary .= '...';
            }
            
            // Get business unit ID (default to 1 for now)
            $business_unit_id = 1;
            
            // Determine intelligence type
            $intelligence_type = $this->getIntelligenceType($extension, $category);
            
            $stmt = $this->db->prepare("
                INSERT INTO intelligence_files 
                (business_unit_id, server_id, file_path, file_name, file_type, file_size, 
                 file_content, intelligence_type, intelligence_data, content_summary, extracted_at)
                VALUES (:business_unit, :server, :path, :filename, :type, :size, 
                        :content, :intel_type, :intel_data, :summary, NOW())
                ON DUPLICATE KEY UPDATE
                    file_size = :size,
                    file_content = :content,
                    intelligence_data = :intel_data,
                    content_summary = :summary,
                    updated_at = NOW()
            ");
            
            $result = $stmt->execute([
                'business_unit' => $business_unit_id,
                'server' => $this->server,
                'path' => $original_path,
                'filename' => basename($original_path),
                'type' => $category,
                'size' => $file_size,
                'content' => $content,
                'intel_type' => $intelligence_type,
                'intel_data' => $intelligence_data,
                'summary' => $summary
            ]);
            
            // DEBUG: Log first few successful inserts for code category
            static $insert_count = 0;
            $insert_count++;
            if ($result && $category === 'code' && $insert_count <= 5) {
                echo "[DEBUG] Successfully inserted code file #$insert_count: type=$intelligence_type, path=$original_path\n";
            }
            
            if (!$result) {
                $this->stats['errors'][] = "Database insert failed for: $original_path - " . implode(', ', $stmt->errorInfo());
                if ($category === 'code') {
                    echo "[ERROR] Failed to insert code file: $original_path - " . implode(', ', $stmt->errorInfo()) . "\n";
                }
            }
            
        } catch (PDOException $e) {
            $this->stats['errors'][] = "Database error for $original_path: " . $e->getMessage();
        }
    }
    
    /**
     * Get intelligence type based on file extension and category
     */
    private function getIntelligenceType($extension, $category) {
        if ($category === 'documentation') {
            return 'documentation_' . $extension;
        } elseif ($category === 'code_intelligence') {
            return 'code_' . $extension;
        } elseif ($category === 'business_intelligence') {
            return 'business_data_' . $extension;
        }
        return 'general_' . $extension;
    }
    
    /**
     * Extract functions from code content
     */
    private function extractFunctions($content, $extension) {
        $functions = [];
        
        switch ($extension) {
            case 'php':
                // Extract PHP functions
                preg_match_all('/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\([^)]*\)/i', $content, $matches);
                foreach ($matches[1] as $index => $funcName) {
                    $functions[] = [
                        'name' => $funcName,
                        'signature' => $matches[0][$index],
                        'type' => 'function'
                    ];
                }
                
                // Extract PHP classes
                preg_match_all('/class\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i', $content, $classMatches);
                foreach ($classMatches[1] as $className) {
                    $functions[] = [
                        'name' => $className,
                        'signature' => 'class ' . $className,
                        'type' => 'class'
                    ];
                }
                break;
                
            case 'js':
                // Extract JavaScript functions
                preg_match_all('/function\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*\([^)]*\)/i', $content, $matches);
                foreach ($matches[1] as $index => $funcName) {
                    $functions[] = [
                        'name' => $funcName,
                        'signature' => $matches[0][$index],
                        'type' => 'function'
                    ];
                }
                
                // Extract arrow functions assigned to variables
                preg_match_all('/(?:const|let|var)\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*=\s*(?:\([^)]*\)|[a-zA-Z_$][a-zA-Z0-9_$]*)\s*=>/i', $content, $arrowMatches);
                foreach ($arrowMatches[1] as $funcName) {
                    $functions[] = [
                        'name' => $funcName,
                        'signature' => $funcName,
                        'type' => 'arrow_function'
                    ];
                }
                break;
                
            case 'py':
                // Extract Python functions
                preg_match_all('/def\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)/i', $content, $matches);
                foreach ($matches[1] as $index => $funcName) {
                    $functions[] = [
                        'name' => $funcName,
                        'signature' => $matches[0][$index],
                        'type' => 'function'
                    ];
                }
                
                // Extract Python classes
                preg_match_all('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $classMatches);
                foreach ($classMatches[1] as $className) {
                    $functions[] = [
                        'name' => $className,
                        'signature' => 'class ' . $className,
                        'type' => 'class'
                    ];
                }
                break;
        }
        
        return $functions;
    }
    
    /**
     * Create README in original documentation location
     */
    private function createDocumentationReadme($dir, $filename) {
        $readme_path = $dir . '/README_INTELLIGENCE.md';
        
        // Don't recreate if already exists
        if (file_exists($readme_path)) {
            return;
        }
        
        $content = <<<MD
# 📚 Documentation Moved to Intelligence Server

This documentation has been extracted and centralized on the Intelligence Server.

## Access via API

```bash
# Search for documents
curl -H "X-API-Key: your_api_key" \
  "https://gpt.ecigdis.co.nz/api/intelligence/search?q=$filename"

# Retrieve specific document
curl -H "X-API-Key: your_api_key" \
  "https://gpt.ecigdis.co.nz/api/intelligence/document?path=documentation/{$this->server}/$filename"
```

## Bot Commands

Use these commands in any AI interface:

- `!doc $filename` - Retrieve this document
- `!search keyword` - Search all documentation
- `!tree` - Browse directory structure

## Direct Access

Intelligence Server: https://gpt.ecigdis.co.nz/intelligence/

---
*Last updated: {date('Y-m-d H:i:s')}*
*Server: {$this->config['name']}*
MD;
        
        file_put_contents($readme_path, $content);
        $this->stats['readmes_created']++;
    }
    
    /**
     * Create client KB structure with search tools
     */
    private function createClientKBStructure() {
        $kb_path = $this->config['path'] . '/_kb';
        
        if (!is_dir($kb_path)) {
            mkdir($kb_path, 0755, true);
        }
        
        // Create main README
        $readme_content = <<<MD
# 📚 Knowledge Base - {$this->config['name']}

This is a lightweight KB folder for bot interaction and quick access.

**All documentation and intelligence is stored centrally on the Intelligence Server.**

## Quick Commands

### Bot Commands
- `!doc filename` - Retrieve document from intelligence server
- `!search keyword` - Search all centralized intelligence
- `!tree path` - Browse directory structure
- `!stats` - View intelligence statistics

### API Access

```php
// Search intelligence
\$api = new IntelligenceAPIClient('your_api_key');
\$results = \$api->search('keyword');

// Get document
\$doc = \$api->getDocument('documentation/{$this->server}/file.md');
```

## Intelligence Server

- **API**: https://gpt.ecigdis.co.nz/api/intelligence/
- **Web Interface**: https://gpt.ecigdis.co.nz/intelligence/
- **Documentation**: See API docs for full endpoint list

## Local Tools

- `search.php` - Simple search interface
- `quick_links.md` - Common document shortcuts

---
*Server: {$this->config['name']} ({$this->server})*
*Last scan: {date('Y-m-d H:i:s')}*
MD;
        
        file_put_contents($kb_path . '/README.md', $readme_content);
        $this->stats['readmes_created']++;
        
        // Create quick links file
        $quick_links = <<<MD
# 🔗 Quick Links

Common documentation shortcuts for {$this->config['name']}

## Frequently Accessed

- [System Architecture](https://gpt.ecigdis.co.nz/api/intelligence/document?path=documentation/{$this->server}/ARCHITECTURE.md)
- [API Documentation](https://gpt.ecigdis.co.nz/api/intelligence/document?path=documentation/{$this->server}/API.md)
- [Deployment Guide](https://gpt.ecigdis.co.nz/api/intelligence/document?path=documentation/{$this->server}/DEPLOYMENT.md)

## Recent Intelligence

Use `!search recent` to find recently extracted intelligence.

## API Endpoints

- Search: `/api/intelligence/search?q=keyword`
- Document: `/api/intelligence/document?path=...`
- Tree: `/api/intelligence/tree?path=...`
- Stats: `/api/intelligence/stats`

MD;
        
        file_put_contents($kb_path . '/QUICK_LINKS.md', $quick_links);
    }
    
    /**
     * Log message
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        echo "[$timestamp] $message\n";
        
        // Also log to file
        $log_file = INTELLIGENCE_ROOT . '/scanner_logs/' . $this->server . '_' . date('Y-m-d') . '.log';
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

// CLI execution
if (php_sapi_name() === 'cli') {
    $options = getopt('', ['server:', 'full']);
    
    $server = $options['server'] ?? 'jcepnzzkmj';
    $full = isset($options['full']);
    
    try {
        $scanner = new APINeuralScanner($server, $full);
        $stats = $scanner->scan();
        
        echo "\n✅ Scan completed successfully\n";
        exit(0);
    } catch (Exception $e) {
        echo "\n❌ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Return class for API usage
return 'APINeuralScanner';
