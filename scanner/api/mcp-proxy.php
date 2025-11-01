<?php
/**
 * MCP AI Agent Proxy - Internal Intelligence Hub Integration
 *
 * Provides unified access to the MCP intelligence system for all Scanner features
 *
 * @package Scanner
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Load database config
require_once __DIR__ . '/../config/database.php';

// Start session
session_start();

// Authentication check (simplified for now)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['current_project_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

/**
 * MCP Agent Client
 */
class MCPAgent
{
    private string $mcpUrl = 'https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php';
    private array $tools = [
        'semantic_search',
        'search_by_category',
        'find_code',
        'find_similar',
        'explore_by_tags',
        'analyze_file',
        'get_file_content',
        'health_check',
        'get_stats',
        'top_keywords',
        'list_categories',
        'get_analytics',
        'list_satellites'
    ];

    public function __construct()
    {
        // Initialize MCP connection
    }

    /**
     * Call MCP tool
     */
    public function call(string $tool, array $arguments = []): array
    {
        if (!in_array($tool, $this->tools, true)) {
            return ['success' => false, 'error' => "Unknown tool: {$tool}"];
        }

        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => $tool,
                'arguments' => $arguments
            ],
            'id' => uniqid('mcp_', true)
        ];

        $ch = curl_init($this->mcpUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: Scanner/3.0.0'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'error' => "MCP connection error: {$error}"];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "MCP returned HTTP {$httpCode}"];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response from MCP'];
        }

        if (isset($data['error'])) {
            return ['success' => false, 'error' => $data['error']['message'] ?? 'MCP error'];
        }

        return ['success' => true, 'data' => $data['result'] ?? []];
    }

    /**
     * Semantic search for code patterns
     */
    public function semanticSearch(string $query, int $limit = 20): array
    {
        return $this->call('semantic_search', [
            'query' => $query,
            'limit' => $limit
        ]);
    }

    /**
     * Find similar files
     */
    public function findSimilar(string $filePath, int $limit = 10): array
    {
        return $this->call('find_similar', [
            'file_path' => $filePath,
            'limit' => $limit
        ]);
    }

    /**
     * Analyze file with MCP
     */
    public function analyzeFile(string $filePath): array
    {
        return $this->call('analyze_file', [
            'file_path' => $filePath
        ]);
    }

    /**
     * Get system health
     */
    public function healthCheck(): array
    {
        return $this->call('health_check', []);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(string $action = 'overview', string $timeframe = '24h'): array
    {
        return $this->call('get_analytics', [
            'action' => $action,
            'timeframe' => $timeframe
        ]);
    }

    /**
     * Search by business category
     */
    public function searchByCategory(string $categoryName, string $query = '', int $limit = 20): array
    {
        return $this->call('search_by_category', [
            'category_name' => $categoryName,
            'query' => $query,
            'limit' => $limit
        ]);
    }

    /**
     * List all categories
     */
    public function listCategories(float $minPriority = 1.0): array
    {
        return $this->call('list_categories', [
            'min_priority' => $minPriority,
            'order_by' => 'priority'
        ]);
    }

    /**
     * Find code by pattern
     */
    public function findCode(string $pattern, string $searchIn = 'all', int $limit = 20): array
    {
        return $this->call('find_code', [
            'pattern' => $pattern,
            'search_in' => $searchIn,
            'limit' => $limit
        ]);
    }

    /**
     * Get system statistics
     */
    public function getStats(string $breakdownBy = 'unit'): array
    {
        return $this->call('get_stats', [
            'breakdown_by' => $breakdownBy
        ]);
    }
}

// ============================================================================
// API ROUTES
// ============================================================================

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$mcp = new MCPAgent();

try {
    switch ($action) {
        case 'search':
            $query = $_POST['query'] ?? '';
            $limit = (int)($_POST['limit'] ?? 20);
            $result = $mcp->semanticSearch($query, $limit);
            break;

        case 'analyze':
            $filePath = $_POST['file_path'] ?? '';
            $result = $mcp->analyzeFile($filePath);
            break;

        case 'similar':
            $filePath = $_POST['file_path'] ?? '';
            $limit = (int)($_POST['limit'] ?? 10);
            $result = $mcp->findSimilar($filePath, $limit);
            break;

        case 'health':
            $result = $mcp->healthCheck();
            break;

        case 'analytics':
            $type = $_POST['type'] ?? 'overview';
            $timeframe = $_POST['timeframe'] ?? '24h';
            $result = $mcp->getAnalytics($type, $timeframe);
            break;

        case 'categories':
            $minPriority = (float)($_POST['min_priority'] ?? 1.0);
            $result = $mcp->listCategories($minPriority);
            break;

        case 'search_category':
            $category = $_POST['category'] ?? '';
            $query = $_POST['query'] ?? '';
            $limit = (int)($_POST['limit'] ?? 20);
            $result = $mcp->searchByCategory($category, $query, $limit);
            break;

        case 'find_code':
            $pattern = $_POST['pattern'] ?? '';
            $searchIn = $_POST['search_in'] ?? 'all';
            $limit = (int)($_POST['limit'] ?? 20);
            $result = $mcp->findCode($pattern, $searchIn, $limit);
            break;

        case 'stats':
            $breakdownBy = $_POST['breakdown_by'] ?? 'unit';
            $result = $mcp->getStats($breakdownBy);
            break;

        default:
            http_response_code(400);
            $result = ['success' => false, 'error' => 'Invalid action'];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
