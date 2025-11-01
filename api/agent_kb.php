<?php
/**
 * Agent Knowledge Base API
 * 
 * Allows agents to query and update their own knowledge base remotely
 * Enables continuous learning and knowledge sharing across sessions
 * 
 * Endpoints:
 *   GET  /api/agent_kb/query?topic=architecture
 *   GET  /api/agent_kb/search?q=intelligence_files
 *   POST /api/agent_kb/update
 *   GET  /api/agent_kb/list?category=decisions
 *   GET  /api/agent_kb/version
 * 
 * @package Intelligence_Hub
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Agent-ID');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

define('AGENT_KB_ROOT', __DIR__ . '/../../_agent_kb');
define('API_KEY', 'master_api_key_2025'); // TODO: Move to .env

class AgentKnowledgeBaseAPI {
    
    private $action;
    private $params;
    private $agent_id;
    
    public function __construct() {
        // Parse action from URL
        $this->action = $_GET['action'] ?? 'query';
        $this->params = $_REQUEST;
        
        // Get agent ID from header or generate
        $this->agent_id = $_SERVER['HTTP_X_AGENT_ID'] ?? 'unknown_agent';
        
        // Authenticate
        if (!$this->authenticate()) {
            $this->sendError('Unauthorized', 401);
        }
    }
    
    /**
     * Route to appropriate handler
     */
    public function handle() {
        try {
            switch ($this->action) {
                case 'query':
                    return $this->query();
                case 'search':
                    return $this->search();
                case 'update':
                    return $this->update();
                case 'list':
                    return $this->listFiles();
                case 'version':
                    return $this->version();
                case 'stats':
                    return $this->stats();
                default:
                    $this->sendError('Unknown action', 400);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Query specific topic/file
     * GET /api/agent_kb/query?topic=architecture/OVERVIEW
     */
    private function query() {
        $topic = $this->params['topic'] ?? '';
        
        if (empty($topic)) {
            return $this->sendError('Missing topic parameter', 400);
        }
        
        // Sanitize path
        $safe_topic = $this->sanitizePath($topic);
        $file_path = AGENT_KB_ROOT . '/' . $safe_topic;
        
        // Try with .md extension if not provided
        if (!file_exists($file_path) && !str_ends_with($file_path, '.md')) {
            $file_path .= '.md';
        }
        
        if (!file_exists($file_path)) {
            return $this->sendError("Topic not found: $topic", 404);
        }
        
        $content = file_get_contents($file_path);
        
        return $this->sendSuccess([
            'topic' => $topic,
            'path' => $file_path,
            'content' => $content,
            'size' => strlen($content),
            'last_modified' => date('Y-m-d H:i:s', filemtime($file_path)),
            'format' => pathinfo($file_path, PATHINFO_EXTENSION)
        ]);
    }
    
    /**
     * Search KB content
     * GET /api/agent_kb/search?q=intelligence_files&category=architecture
     */
    private function search() {
        $query = $this->params['q'] ?? '';
        $category = $this->params['category'] ?? null;
        
        if (empty($query)) {
            return $this->sendError('Missing query parameter', 400);
        }
        
        $results = [];
        $search_dir = $category ? AGENT_KB_ROOT . '/' . $category : AGENT_KB_ROOT;
        
        if (!is_dir($search_dir)) {
            return $this->sendError('Invalid category', 400);
        }
        
        // Recursive search
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($search_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $content = file_get_contents($file->getPathname());
                
                // Search in content
                if (stripos($content, $query) !== false) {
                    $results[] = [
                        'file' => str_replace(AGENT_KB_ROOT . '/', '', $file->getPathname()),
                        'path' => $file->getPathname(),
                        'title' => $this->extractTitle($content),
                        'matches' => $this->getMatchingLines($content, $query),
                        'relevance' => $this->calculateRelevance($content, $query),
                        'last_modified' => date('Y-m-d H:i:s', $file->getMTime())
                    ];
                }
            }
        }
        
        // Sort by relevance
        usort($results, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });
        
        return $this->sendSuccess([
            'query' => $query,
            'total_results' => count($results),
            'results' => $results
        ]);
    }
    
    /**
     * Update KB file
     * POST /api/agent_kb/update
     * Body: {"file": "decisions/005_new.md", "content": "..."}
     */
    private function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->sendError('Method not allowed', 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $file = $input['file'] ?? '';
        $content = $input['content'] ?? '';
        $commit_message = $input['message'] ?? 'Agent KB update';
        
        if (empty($file) || empty($content)) {
            return $this->sendError('Missing file or content', 400);
        }
        
        $safe_file = $this->sanitizePath($file);
        $file_path = AGENT_KB_ROOT . '/' . $safe_file;
        
        // Create directory if needed
        $dir = dirname($file_path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Backup existing if exists
        if (file_exists($file_path)) {
            $backup_path = $file_path . '.backup.' . time();
            copy($file_path, $backup_path);
        }
        
        // Write new content
        file_put_contents($file_path, $content);
        
        // Log update
        $this->logUpdate($file, $this->agent_id, $commit_message);
        
        return $this->sendSuccess([
            'file' => $file,
            'path' => $file_path,
            'size' => strlen($content),
            'updated_by' => $this->agent_id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * List files in category
     * GET /api/agent_kb/list?category=architecture
     */
    private function listFiles() {
        $category = $this->params['category'] ?? '';
        $search_dir = $category ? AGENT_KB_ROOT . '/' . $category : AGENT_KB_ROOT;
        
        if (!is_dir($search_dir)) {
            return $this->sendError('Invalid category', 400);
        }
        
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($search_dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relative_path = str_replace(AGENT_KB_ROOT . '/', '', $file->getPathname());
                $content = file_get_contents($file->getPathname());
                
                $files[] = [
                    'path' => $relative_path,
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'title' => $this->extractTitle($content),
                    'last_modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'category' => dirname($relative_path)
                ];
            }
        }
        
        return $this->sendSuccess([
            'category' => $category ?: 'all',
            'total_files' => count($files),
            'files' => $files
        ]);
    }
    
    /**
     * Get KB version info
     */
    private function version() {
        $readme_path = AGENT_KB_ROOT . '/README.md';
        $version = 'unknown';
        
        if (file_exists($readme_path)) {
            $content = file_get_contents($readme_path);
            if (preg_match('/\*\*Version:\*\*\s+([0-9.]+)/', $content, $matches)) {
                $version = $matches[1];
            }
        }
        
        return $this->sendSuccess([
            'version' => $version,
            'last_updated' => date('Y-m-d H:i:s', filemtime($readme_path)),
            'kb_root' => AGENT_KB_ROOT
        ]);
    }
    
    /**
     * Get KB statistics
     */
    private function stats() {
        $stats = [
            'categories' => [],
            'total_files' => 0,
            'total_size' => 0,
            'last_update' => null
        ];
        
        $categories = ['architecture', 'decisions', 'patterns', 'schemas', 'migrations', 'troubleshooting'];
        
        foreach ($categories as $category) {
            $dir = AGENT_KB_ROOT . '/' . $category;
            if (is_dir($dir)) {
                $count = 0;
                $size = 0;
                
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $count++;
                        $size += $file->getSize();
                        $stats['total_files']++;
                        $stats['total_size'] += $file->getSize();
                        
                        if (!$stats['last_update'] || $file->getMTime() > strtotime($stats['last_update'])) {
                            $stats['last_update'] = date('Y-m-d H:i:s', $file->getMTime());
                        }
                    }
                }
                
                $stats['categories'][$category] = [
                    'files' => $count,
                    'size' => $size
                ];
            }
        }
        
        return $this->sendSuccess($stats);
    }
    
    // ============================================================================
    // HELPER METHODS
    // ============================================================================
    
    private function authenticate() {
        $provided_key = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        return $provided_key === API_KEY;
    }
    
    private function sanitizePath($path) {
        // Remove any path traversal attempts
        $path = str_replace(['../', '.\\', '..\\'], '', $path);
        return trim($path, '/');
    }
    
    private function extractTitle($content) {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Untitled';
    }
    
    private function getMatchingLines($content, $query) {
        $lines = explode("\n", $content);
        $matches = [];
        
        foreach ($lines as $i => $line) {
            if (stripos($line, $query) !== false) {
                $matches[] = [
                    'line_number' => $i + 1,
                    'content' => trim($line),
                    'context' => array_slice($lines, max(0, $i - 1), 3)
                ];
                
                if (count($matches) >= 5) break; // Max 5 matches per file
            }
        }
        
        return $matches;
    }
    
    private function calculateRelevance($content, $query) {
        $matches = substr_count(strtolower($content), strtolower($query));
        $title = $this->extractTitle($content);
        $title_match = stripos($title, $query) !== false ? 10 : 0;
        
        return $matches + $title_match;
    }
    
    private function logUpdate($file, $agent_id, $message) {
        $log_file = AGENT_KB_ROOT . '/.update_log.json';
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'file' => $file,
            'agent_id' => $agent_id,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $log = [];
        if (file_exists($log_file)) {
            $log = json_decode(file_get_contents($log_file), true) ?? [];
        }
        
        $log[] = $log_entry;
        
        // Keep last 100 entries
        if (count($log) > 100) {
            $log = array_slice($log, -100);
        }
        
        file_put_contents($log_file, json_encode($log, JSON_PRETTY_PRINT));
    }
    
    private function sendSuccess($data) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s'),
            'agent_id' => $this->agent_id
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s')
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// Execute API
$api = new AgentKnowledgeBaseAPI();
$api->handle();
