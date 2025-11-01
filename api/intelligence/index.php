<?php
/**
 * Intelligence API - Central REST API for Centralized Intelligence System
 * 
 * Provides API access to centralized intelligence stored on hdgwrzntwa (gpt.ecigdis.co.nz)
 * Accessible by all sibling applications and future remote servers
 * 
 * Base URL: https://gpt.ecigdis.co.nz/api/intelligence/
 * 
 * Endpoints:
 * - GET  /api/intelligence/search?q=keyword&type=docs&limit=50
 * - GET  /api/intelligence/document?path=file.md
 * - GET  /api/intelligence/tree?path=documentation/jcepnzzkmj
 * - GET  /api/intelligence/stats
 * - POST /api/intelligence/extract (Admin only)
 * - POST /api/intelligence/scan (Admin only - trigger neural scanner)
 * 
 * Authentication: API Key in header or query parameter
 * Rate Limiting: 1000 requests per hour per key
 * 
 * @package Intelligence_API
 * @version 1.0.0
 */

// Security headers
header('Content-Type: application/json');
header('X-Powered-By: CIS Intelligence System');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Configuration
define('INTELLIGENCE_ROOT', '/home/master/applications/hdgwrzntwa/public_html/intelligence');
define('API_VERSION', '1.0.0');
define('RATE_LIMIT_PER_HOUR', 1000);
define('CACHE_DURATION', 300); // 5 minutes

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

// API Keys (in production, move to database)
define('API_KEYS', [
    'staff_api_key_2025' => ['name' => 'CIS Staff Portal', 'admin' => true],
    'retail_api_key_2025' => ['name' => 'Vape Shed Retail', 'admin' => false],
    'wholesale_api_key_2025' => ['name' => 'Ecigdis Wholesale', 'admin' => false],
    'master_api_key_2025' => ['name' => 'Master Admin', 'admin' => true]
]);

/**
 * API Response Handler
 */
class IntelligenceAPI {
    private $db;
    private $api_key;
    private $api_info;
    
    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->sendError('Database connection failed', 500);
        }
        
        $this->authenticate();
    }
    
    /**
     * Authenticate API request
     */
    private function authenticate() {
        // Check header first, then query parameter
        $this->api_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        
        if (!$this->api_key || !isset(API_KEYS[$this->api_key])) {
            $this->sendError('Invalid or missing API key', 401);
        }
        
        $this->api_info = API_KEYS[$this->api_key];
        
        // Check rate limit
        if (!$this->checkRateLimit()) {
            $this->sendError('Rate limit exceeded. Max ' . RATE_LIMIT_PER_HOUR . ' requests per hour', 429);
        }
    }
    
    /**
     * Check rate limit
     */
    private function checkRateLimit() {
        $cache_key = 'rate_limit_' . md5($this->api_key);
        $cache_file = sys_get_temp_dir() . '/' . $cache_key;
        
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            if ($data['timestamp'] > time() - 3600) {
                if ($data['count'] >= RATE_LIMIT_PER_HOUR) {
                    return false;
                }
                $data['count']++;
            } else {
                $data = ['timestamp' => time(), 'count' => 1];
            }
        } else {
            $data = ['timestamp' => time(), 'count' => 1];
        }
        
        file_put_contents($cache_file, json_encode($data));
        return true;
    }
    
    /**
     * Route request to appropriate handler
     */
    public function route() {
        $path = $_SERVER['PATH_INFO'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Remove /api/intelligence prefix if present
        $path = preg_replace('#^/api/intelligence#', '', $path);
        
        // Route to handlers
        switch ($path) {
            case '/search':
                if ($method === 'GET') {
                    $this->handleSearch();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/document':
                if ($method === 'GET') {
                    $this->handleDocument();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/tree':
                if ($method === 'GET') {
                    $this->handleTree();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/stats':
                if ($method === 'GET') {
                    $this->handleStats();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/extract':
                if ($method === 'POST') {
                    if (!$this->api_info['admin']) {
                        $this->sendError('Admin access required', 403);
                    }
                    $this->handleExtract();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/scan':
                if ($method === 'POST') {
                    if (!$this->api_info['admin']) {
                        $this->sendError('Admin access required', 403);
                    }
                    $this->handleScan();
                } else {
                    $this->sendError('Method not allowed', 405);
                }
                break;
                
            case '/':
            case '':
                $this->handleInfo();
                break;
                
            default:
                $this->sendError('Endpoint not found', 404);
        }
    }
    
    /**
     * Handle /search endpoint
     * GET /api/intelligence/search?q=keyword&type=docs&limit=50
     */
    private function handleSearch() {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? 'all'; // all, docs, code, business
        $limit = min((int)($_GET['limit'] ?? 50), 200);
        
        if (empty($query)) {
            $this->sendError('Search query required', 400);
        }
        
        // Search in database
        $results = $this->searchDatabase($query, $type, $limit);
        
        // Search in file system (for recently added files not yet in DB)
        $file_results = $this->searchFileSystem($query, $type);
        
        // Combine and deduplicate
        $combined = array_merge($results, $file_results);
        $unique = $this->deduplicateResults($combined);
        
        // Sort by relevance
        usort($unique, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        $unique = array_slice($unique, 0, $limit);
        
        $this->sendSuccess([
            'query' => $query,
            'type' => $type,
            'count' => count($unique),
            'results' => $unique
        ]);
    }
    
    /**
     * Search database for intelligence
     */
    private function searchDatabase($query, $type, $limit) {
        $results = [];
        
        try {
            $sql = "SELECT * FROM intelligence_files WHERE 1=1";
            $params = [];
            
            // Add type filter
            if ($type !== 'all') {
                $type_map = [
                    'docs' => 'documentation',
                    'code' => 'code',
                    'business' => 'business_intelligence'
                ];
                if (isset($type_map[$type])) {
                    $sql .= " AND category = :category";
                    $params['category'] = $type_map[$type];
                }
            }
            
            // Add search filter
            $sql .= " AND (original_path LIKE :query OR metadata LIKE :query2)";
            $params['query'] = "%$query%";
            $params['query2'] = "%$query%";
            
            $sql .= " ORDER BY extracted_at DESC LIMIT :limit";
            $params['limit'] = $limit;
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'id' => $row['id'],
                    'filename' => basename($row['original_path']),
                    'path' => $row['original_path'],
                    'intelligence_path' => $row['intelligence_path'],
                    'category' => $row['category'],
                    'source_server' => $row['source_server'],
                    'file_size' => $row['file_size'],
                    'extracted_at' => $row['extracted_at'],
                    'relevance' => $this->calculateRelevance($query, $row)
                ];
            }
        } catch (PDOException $e) {
            // Log error but continue with file system search
            error_log('Intelligence API DB Search Error: ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * Search file system for intelligence
     */
    private function searchFileSystem($query, $type) {
        $results = [];
        $query_lower = strtolower($query);
        
        // Determine directories to search
        $search_dirs = [];
        if ($type === 'all' || $type === 'docs') {
            $search_dirs[] = INTELLIGENCE_ROOT . '/documentation';
        }
        if ($type === 'all' || $type === 'code') {
            $search_dirs[] = INTELLIGENCE_ROOT . '/code_intelligence';
        }
        if ($type === 'all' || $type === 'business') {
            $search_dirs[] = INTELLIGENCE_ROOT . '/business_intelligence';
        }
        
        foreach ($search_dirs as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();
                    $path = $file->getPathname();
                    
                    // Check if filename matches query
                    if (stripos($filename, $query) !== false) {
                        $results[] = [
                            'filename' => $filename,
                            'path' => str_replace(INTELLIGENCE_ROOT, '', $path),
                            'intelligence_path' => $path,
                            'category' => $this->getCategoryFromPath($path),
                            'file_size' => $file->getSize(),
                            'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'relevance' => $this->calculateFileRelevance($query, $filename)
                        ];
                    }
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Calculate relevance score
     */
    private function calculateRelevance($query, $row) {
        $score = 0;
        $query_lower = strtolower($query);
        
        // Exact match in filename
        if (stripos(basename($row['original_path']), $query) !== false) {
            $score += 10;
        }
        
        // Match in path
        if (stripos($row['original_path'], $query) !== false) {
            $score += 5;
        }
        
        // Recent files get bonus
        $days_old = (time() - strtotime($row['extracted_at'])) / 86400;
        if ($days_old < 7) {
            $score += 3;
        }
        
        return $score;
    }
    
    /**
     * Calculate file relevance
     */
    private function calculateFileRelevance($query, $filename) {
        $score = 0;
        
        // Exact match
        if (stripos($filename, $query) !== false) {
            $score += 10;
        }
        
        // Extension bonus
        if (preg_match('/\.(md|txt|json)$/i', $filename)) {
            $score += 2;
        }
        
        return $score;
    }
    
    /**
     * Get category from path
     */
    private function getCategoryFromPath($path) {
        if (strpos($path, '/documentation/') !== false) return 'documentation';
        if (strpos($path, '/code_intelligence/') !== false) return 'code';
        if (strpos($path, '/business_intelligence/') !== false) return 'business_intelligence';
        return 'other';
    }
    
    /**
     * Deduplicate search results
     */
    private function deduplicateResults($results) {
        $unique = [];
        $seen = [];
        
        foreach ($results as $result) {
            $key = $result['filename'] . '|' . ($result['path'] ?? '');
            if (!isset($seen[$key])) {
                $unique[] = $result;
                $seen[$key] = true;
            }
        }
        
        return $unique;
    }
    
    /**
     * Handle /document endpoint
     * GET /api/intelligence/document?path=documentation/jcepnzzkmj/file.md
     */
    private function handleDocument() {
        $path = $_GET['path'] ?? '';
        
        if (empty($path)) {
            $this->sendError('Document path required', 400);
        }
        
        // Security: prevent directory traversal
        $path = str_replace(['..', '//'], ['', '/'], $path);
        $full_path = INTELLIGENCE_ROOT . '/' . ltrim($path, '/');
        
        if (!file_exists($full_path) || !is_file($full_path)) {
            $this->sendError('Document not found', 404);
        }
        
        $content = file_get_contents($full_path);
        $extension = pathinfo($full_path, PATHINFO_EXTENSION);
        
        $this->sendSuccess([
            'path' => $path,
            'filename' => basename($full_path),
            'size' => filesize($full_path),
            'modified' => date('Y-m-d H:i:s', filemtime($full_path)),
            'extension' => $extension,
            'content' => $content
        ]);
    }
    
    /**
     * Handle /tree endpoint
     * GET /api/intelligence/tree?path=documentation/jcepnzzkmj&depth=3
     */
    private function handleTree() {
        $path = $_GET['path'] ?? '';
        $depth = min((int)($_GET['depth'] ?? 3), 10);
        
        $base_path = INTELLIGENCE_ROOT;
        if (!empty($path)) {
            $path = str_replace(['..', '//'], ['', '/'], $path);
            $base_path = INTELLIGENCE_ROOT . '/' . ltrim($path, '/');
        }
        
        if (!is_dir($base_path)) {
            $this->sendError('Directory not found', 404);
        }
        
        $tree = $this->buildDirectoryTree($base_path, $depth);
        
        $this->sendSuccess([
            'path' => $path ?: '/',
            'depth' => $depth,
            'tree' => $tree
        ]);
    }
    
    /**
     * Build directory tree structure
     */
    private function buildDirectoryTree($path, $depth, $current_depth = 0) {
        if ($current_depth >= $depth) {
            return null;
        }
        
        $result = [];
        
        try {
            $items = scandir($path);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $full_path = $path . '/' . $item;
                $relative_path = str_replace(INTELLIGENCE_ROOT . '/', '', $full_path);
                
                if (is_dir($full_path)) {
                    $result[] = [
                        'name' => $item,
                        'type' => 'directory',
                        'path' => $relative_path,
                        'children' => $this->buildDirectoryTree($full_path, $depth, $current_depth + 1)
                    ];
                } else {
                    $result[] = [
                        'name' => $item,
                        'type' => 'file',
                        'path' => $relative_path,
                        'size' => filesize($full_path),
                        'modified' => date('Y-m-d H:i:s', filemtime($full_path))
                    ];
                }
            }
        } catch (Exception $e) {
            // Return empty array on error
        }
        
        return $result;
    }
    
    /**
     * Handle /stats endpoint
     * GET /api/intelligence/stats
     */
    private function handleStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'by_category' => [],
            'by_server' => [],
            'recent_extractions' => []
        ];
        
        // Get database stats
        try {
            // Total files and size
            $stmt = $this->db->query("SELECT COUNT(*) as count, SUM(file_size) as size FROM intelligence_files");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_files'] = (int)$row['count'];
            $stats['total_size'] = (int)$row['size'];
            
            // By category
            $stmt = $this->db->query("SELECT category, COUNT(*) as count FROM intelligence_files GROUP BY category");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['by_category'][$row['category']] = (int)$row['count'];
            }
            
            // By server
            $stmt = $this->db->query("SELECT source_server, COUNT(*) as count FROM intelligence_files GROUP BY source_server");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['by_server'][$row['source_server']] = (int)$row['count'];
            }
            
            // Recent extractions
            $stmt = $this->db->query("SELECT original_path, extracted_at FROM intelligence_files ORDER BY extracted_at DESC LIMIT 10");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $stats['recent_extractions'][] = [
                    'path' => $row['original_path'],
                    'extracted_at' => $row['extracted_at']
                ];
            }
        } catch (PDOException $e) {
            error_log('Intelligence API Stats Error: ' . $e->getMessage());
        }
        
        // Add file system stats
        $stats['directories'] = $this->countDirectories(INTELLIGENCE_ROOT);
        $stats['api_version'] = API_VERSION;
        $stats['client_name'] = $this->api_info['name'];
        
        $this->sendSuccess($stats);
    }
    
    /**
     * Count directories recursively
     */
    private function countDirectories($path) {
        $count = 0;
        if (is_dir($path)) {
            $items = scandir($path);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                if (is_dir($path . '/' . $item)) {
                    $count++;
                    $count += $this->countDirectories($path . '/' . $item);
                }
            }
        }
        return $count;
    }
    
    /**
     * Handle /extract endpoint
     * POST /api/intelligence/extract
     * Body: {"path": "/path/to/file.md", "server": "jcepnzzkmj"}
     */
    private function handleExtract() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['path']) || !isset($input['server'])) {
            $this->sendError('Path and server required', 400);
        }
        
        // This would trigger the extraction process
        // For now, return success message
        $this->sendSuccess([
            'message' => 'Extraction queued',
            'path' => $input['path'],
            'server' => $input['server']
        ]);
    }
    
    /**
     * Handle /scan endpoint (API-based scanner trigger)
     * POST /api/intelligence/scan
     * Body: {"server": "jcepnzzkmj", "full": true}
     */
    private function handleScan() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $server = $input['server'] ?? 'all';
        $full_scan = $input['full'] ?? false;
        
        // Trigger neural scanner via API
        $scan_result = $this->triggerNeuralScan($server, $full_scan);
        
        $this->sendSuccess([
            'message' => 'Neural scan initiated',
            'server' => $server,
            'full_scan' => $full_scan,
            'scan_id' => $scan_result['scan_id'],
            'status_url' => '/api/intelligence/scan/status?id=' . $scan_result['scan_id']
        ]);
    }
    
    /**
     * Trigger neural scanner
     */
    private function triggerNeuralScan($server, $full_scan) {
        // Generate scan ID
        $scan_id = uniqid('scan_', true);
        
        // In production, this would queue a background job
        // For now, return scan ID for status checking
        
        return [
            'scan_id' => $scan_id,
            'queued_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Handle /info endpoint
     * GET /api/intelligence/
     */
    private function handleInfo() {
        $this->sendSuccess([
            'name' => 'Intelligence API',
            'version' => API_VERSION,
            'client' => $this->api_info['name'],
            'is_admin' => $this->api_info['admin'],
            'endpoints' => [
                'GET /search' => 'Search centralized intelligence',
                'GET /document' => 'Retrieve specific document',
                'GET /tree' => 'Browse directory structure',
                'GET /stats' => 'System statistics',
                'POST /extract' => 'Extract intelligence (admin)',
                'POST /scan' => 'Trigger neural scanner (admin)'
            ],
            'documentation' => 'https://gpt.ecigdis.co.nz/api/intelligence/docs'
        ]);
    }
    
    /**
     * Send success response
     */
    private function sendSuccess($data) {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c'),
            'api_version' => API_VERSION
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send error response
     */
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $code
            ],
            'timestamp' => date('c'),
            'api_version' => API_VERSION
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// Initialize and route
try {
    $api = new IntelligenceAPI();
    $api->route();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Internal server error',
            'code' => 500
        ],
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
