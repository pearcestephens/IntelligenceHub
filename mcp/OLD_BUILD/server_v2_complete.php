<?php
/**
 * Intelligence Hub - MCP Server v2.0 (COMPLETE UNIFIED VERSION)
 *
 * Enhanced Model Context Protocol server with 11 advanced tools:
 * 1. semantic_search - Natural language search with relevance scoring
 * 2. find_code - Precise pattern matching in code/keywords/tags/entities
 * 3. analyze_file - Deep file analysis with metrics and insights
 * 4. get_file_content - Retrieve complete file content
 * 5. list_satellites - Status and statistics for all satellites
 * 6. sync_satellite - Trigger live satellite data synchronization
 * 7. find_similar - Find files similar to reference by keywords/tags
 * 8. explore_by_tags - Browse files by semantic tags
 * 9. get_stats - System-wide statistics and trends
 * 10. top_keywords - Most common keywords across entire system
 * 11. search_by_category - Category-aware search with priority weighting
 *
 * @package IntelligenceHub\MCP
 * @version 2.0.0
 * @author Intelligence Hub v2
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

// Satellite configuration
define('SATELLITES', [
    1 => ['name' => 'Intelligence Hub', 'url' => 'https://gpt.ecigdis.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    2 => ['name' => 'CIS', 'url' => 'https://staff.vapeshed.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    3 => ['name' => 'VapeShed', 'url' => 'https://vapeshed.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
    4 => ['name' => 'Wholesale', 'url' => 'https://wholesale.ecigdis.co.nz/api/scan_and_return.php', 'auth' => 'bFUdRjh4Jx'],
]);

class MCPServer {
    private ?PDO $db = null;
    private ?string $sessionId = null;
    private float $requestStartTime;

    public function __construct() {
        $this->requestStartTime = microtime(true);
        $this->sessionId = $this->getOrCreateSession();
    }

    /**
     * Initialize MCP server
     */
    public function initialize(): array {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => [
                    'listChanged' => true,
                ],
                'resources' => [
                    'subscribe' => false,
                    'listChanged' => false,
                ],
                'prompts' => [
                    'listChanged' => false,
                ],
                'experimental' => [
                    'semantic_search' => true,
                    'satellite_coordination' => true,
                    'nlp_analysis' => true,
                ],
            ],
            'serverInfo' => [
                'name' => 'Intelligence Hub v2.0',
                'version' => '2.0.0',
                'description' => 'Advanced semantic search, satellite coordination, NLP analysis across all systems',
            ],
        ];
    }

    /**
     * List all available tools
     */
    public function listTools(): array {
        return [
            [
                'name' => 'semantic_search',
                'description' => 'Natural language search across all satellite systems with relevance scoring. Understands context and intent.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Natural language search query (e.g., "how do we handle customer refunds")',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by satellite unit (1=Hub, 2=CIS, 3=VapeShed, 4=Wholesale). Optional.',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum results to return (default: 10, max: 50)',
                            'default' => 10,
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'find_code',
                'description' => 'Precise code pattern matching in content, keywords, tags, or entities. Case-insensitive.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'pattern' => [
                            'type' => 'string',
                            'description' => 'Code pattern to find (class name, function, variable, etc.)',
                        ],
                        'search_in' => [
                            'type' => 'string',
                            'enum' => ['content', 'extracted_keywords', 'semantic_tags', 'entities_detected', 'all'],
                            'description' => 'Where to search (default: all)',
                            'default' => 'all',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by satellite unit',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'default' => 20,
                        ],
                    ],
                    'required' => ['pattern'],
                ],
            ],
            [
                'name' => 'analyze_file',
                'description' => 'Deep analysis of a specific file including metrics, keywords, entities, and insights.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'content_path' => [
                            'type' => 'string',
                            'description' => 'Full file path to analyze',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Satellite unit ID',
                        ],
                    ],
                    'required' => ['content_path'],
                ],
            ],
            [
                'name' => 'get_file_content',
                'description' => 'Retrieve complete content of a file.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'content_path' => [
                            'type' => 'string',
                            'description' => 'Full file path',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Satellite unit ID',
                        ],
                    ],
                    'required' => ['content_path'],
                ],
            ],
            [
                'name' => 'list_satellites',
                'description' => 'Get status and statistics for all satellite systems.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
            [
                'name' => 'sync_satellite',
                'description' => 'Trigger live satellite data synchronization (pulls latest files from satellite).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Satellite unit ID to sync (1-4)',
                        ],
                        'batch_size' => [
                            'type' => 'integer',
                            'description' => 'Number of files to sync (default: 50, max: 500)',
                            'default' => 50,
                        ],
                    ],
                    'required' => ['unit_id'],
                ],
            ],
            [
                'name' => 'find_similar',
                'description' => 'Find files similar to a reference file based on keywords and semantic tags.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'reference_path' => [
                            'type' => 'string',
                            'description' => 'Path to reference file',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Unit ID of reference file',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'default' => 10,
                        ],
                    ],
                    'required' => ['reference_path'],
                ],
            ],
            [
                'name' => 'explore_by_tags',
                'description' => 'Browse files by semantic tags (code, database, api, customer, inventory, sales, etc.).',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'semantic_tags' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Tags to filter by (AND logic)',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by satellite',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'default' => 20,
                        ],
                    ],
                    'required' => ['semantic_tags'],
                ],
            ],
            [
                'name' => 'get_stats',
                'description' => 'Get system-wide statistics and trends.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'breakdown_by' => [
                            'type' => 'string',
                            'enum' => ['unit', 'type', 'tag', 'readability', 'sentiment'],
                            'description' => 'Break down stats by category (default: unit)',
                            'default' => 'unit',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'top_keywords',
                'description' => 'Get most common keywords across entire system or specific satellite.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by satellite (optional)',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Number of keywords to return (default: 50)',
                            'default' => 50,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'search_by_category',
                'description' => 'Category-aware search with priority weighting. Search within specific business categories (Inventory, POS, Finance, etc.) with relevance boost based on category importance.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'Natural language search query',
                        ],
                        'category_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by category ID (91-105). Optional - omit to search all categories.',
                        ],
                        'category_name' => [
                            'type' => 'string',
                            'description' => 'Filter by category name (e.g., "Inventory Management", "Point of Sale"). Optional alternative to category_id.',
                        ],
                        'unit_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by satellite unit',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum results (default: 20, max: 100)',
                            'default' => 20,
                        ],
                    ],
                    'required' => ['query'],
                ],
            ],
            [
                'name' => 'list_categories',
                'description' => 'List all business categories with statistics (file counts, priority weights, parent-child relationships). Shows the complete 31-category taxonomy.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'parent_id' => [
                            'type' => 'integer',
                            'description' => 'Filter by parent category ID to show only children (e.g., parent_id=91 shows sub-categories of Inventory)',
                        ],
                        'include_children' => [
                            'type' => 'boolean',
                            'description' => 'Include child categories in the listing (default: true)',
                            'default' => true,
                        ],
                        'min_priority' => [
                            'type' => 'number',
                            'description' => 'Filter categories by minimum priority weight (e.g., 1.30 for important categories)',
                        ],
                        'order_by' => [
                            'type' => 'string',
                            'enum' => ['priority', 'file_count', 'name'],
                            'description' => 'Sort order (default: priority DESC)',
                            'default' => 'priority',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_analytics',
                'description' => 'Retrieve analytics data from the comprehensive tracking system. Shows tool usage, search effectiveness, performance metrics, and more.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'action' => [
                            'type' => 'string',
                            'enum' => ['overview', 'hourly', 'failed', 'slow', 'popular_queries', 'tool_usage', 'category_performance'],
                            'description' => 'Type of analytics report to retrieve (default: overview)',
                            'default' => 'overview',
                        ],
                        'timeframe' => [
                            'type' => 'string',
                            'enum' => ['1h', '6h', '24h', '7d', '30d'],
                            'description' => 'Time period for analytics (default: 24h)',
                            'default' => '24h',
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum results for failed/slow queries (default: 50)',
                            'default' => 50,
                        ],
                        'threshold' => [
                            'type' => 'integer',
                            'description' => 'Threshold in milliseconds for slow query detection (default: 500)',
                            'default' => 500,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'health_check',
                'description' => 'Get system health status, performance metrics, and database connectivity.',
                'inputSchema' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
        ];
    }

    /**
     * Call a tool
     */
    public function callTool(string $name, array $arguments): array {
        $startTime = microtime(true);
        $success = true;
        $errorMessage = null;
        $result = [];

        try {
            $result = match($name) {
                'semantic_search' => $this->toolSemanticSearch($arguments),
                'find_code' => $this->toolFindCode($arguments),
                'analyze_file' => $this->toolAnalyzeFile($arguments),
                'get_file_content' => $this->toolGetFileContent($arguments),
                'list_satellites' => $this->toolListSatellites($arguments),
                'sync_satellite' => $this->toolSyncSatellite($arguments),
                'find_similar' => $this->toolFindSimilar($arguments),
                'explore_by_tags' => $this->toolExploreByTags($arguments),
                'get_stats' => $this->toolGetStats($arguments),
                'top_keywords' => $this->toolTopKeywords($arguments),
                'search_by_category' => $this->toolSearchByCategory($arguments),
                'list_categories' => $this->toolListCategories($arguments),
                'get_analytics' => $this->toolGetAnalytics($arguments),
                'health_check' => $this->toolHealthCheck($arguments),
                default => ['error' => "Unknown tool: {$name}"],
            };

            if (isset($result['error'])) {
                $success = false;
                $errorMessage = $result['error'];
            }
        } catch (Exception $e) {
            $success = false;
            $errorMessage = $e->getMessage();
            $result = ['error' => $errorMessage];
        }

        $executionTimeMs = (microtime(true) - $startTime) * 1000;
        $resultsCount = $result['results_count'] ?? count($result['results'] ?? []);

        // Log tool usage
        $this->logToolUsage($name, $arguments, $executionTimeMs, $resultsCount, $success, $errorMessage);

        // Log search analytics for search-type tools
        if (in_array($name, ['semantic_search', 'search_by_category', 'find_similar', 'explore_by_tags'])) {
            $queryType = match($name) {
                'semantic_search' => 'semantic',
                'search_by_category' => 'category',
                'find_similar' => 'similar',
                'explore_by_tags' => 'tags',
                default => 'semantic',
            };

            $avgRelevance = null;
            if (!empty($result['results'])) {
                $relevances = array_column($result['results'], 'relevance');
                $relevances = array_filter($relevances, fn($r) => $r !== null);
                if (!empty($relevances)) {
                    $avgRelevance = array_sum($relevances) / count($relevances);
                }
            }

            $this->logSearchAnalytics(
                $arguments['query'] ?? '',
                $queryType,
                $arguments['category_id'] ?? null,
                $arguments['unit_id'] ?? null,
                $resultsCount,
                $avgRelevance,
                $executionTimeMs
            );
        }

        // Log performance metric
        $this->logPerformanceMetric('query_time', $executionTimeMs, [
            'tool' => $name,
            'results_count' => $resultsCount,
            'success' => $success,
        ]);

        return $result;
    }

    /**
     * Get database connection
     */
    private function getDb(): PDO {
        if ($this->db === null) {
            $this->db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return $this->db;
    }

    /**
     * Tool: Semantic Search
     */
    private function toolSemanticSearch(array $args): array {
        $query = $args['query'] ?? '';
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 10, 50);

        if (empty($query)) {
            return ['error' => 'Query is required'];
        }

        $keywords = $this->extractQueryKeywords($query);
        $keywordPattern = '%' . implode('%', $keywords) . '%';

        $db = $this->getDb();

        // Build query with MATCH AGAINST for full-text search
        $sql = "SELECT
            c.content_id,
            c.content_path,
            c.content_name,
            c.unit_id,
            ct.content_summary,
            ct.extracted_keywords,
            ct.semantic_tags,
            ct.readability_score,
            ct.word_count,
            ct.entities_detected,
            (
                MATCH(ct.content_text) AGAINST(? IN NATURAL LANGUAGE MODE) * 10 +
                CASE WHEN ct.extracted_keywords LIKE ? THEN 5 ELSE 0 END +
                CASE WHEN ct.semantic_tags LIKE ? THEN 3 ELSE 0 END +
                CASE WHEN c.content_name LIKE ? THEN 8 ELSE 0 END
            ) AS relevance
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE ct.content_text IS NOT NULL";

        $params = [$query, $keywordPattern, $keywordPattern, $keywordPattern];

        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $sql .= " HAVING relevance > 0
        ORDER BY relevance DESC
        LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'query' => $query,
            'results_count' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Tool: Find Code
     */
    private function toolFindCode(array $args): array {
        $pattern = $args['pattern'] ?? '';
        $searchIn = $args['search_in'] ?? 'all';
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 20, 100);

        if (empty($pattern)) {
            return ['error' => 'Pattern is required'];
        }

        $db = $this->getDb();
        $likePattern = '%' . $pattern . '%';

        $conditions = [];
        $params = [];

        if ($searchIn === 'all' || $searchIn === 'content') {
            $conditions[] = "ct.content_text LIKE ?";
            $params[] = $likePattern;
        }
        if ($searchIn === 'all' || $searchIn === 'extracted_keywords') {
            $conditions[] = "ct.extracted_keywords LIKE ?";
            $params[] = $likePattern;
        }
        if ($searchIn === 'all' || $searchIn === 'semantic_tags') {
            $conditions[] = "ct.semantic_tags LIKE ?";
            $params[] = $likePattern;
        }
        if ($searchIn === 'all' || $searchIn === 'entities_detected') {
            $conditions[] = "ct.entities_detected LIKE ?";
            $params[] = $likePattern;
        }

        $whereClause = '(' . implode(' OR ', $conditions) . ')';

        if ($unitId !== null) {
            $whereClause .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $sql = "SELECT
            c.content_id,
            c.content_path,
            c.content_name,
            c.unit_id,
            ct.content_summary,
            ct.extracted_keywords,
            ct.semantic_tags,
            ct.entities_detected,
            ct.word_count
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE {$whereClause}
        LIMIT " . (int)$limit;


        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'pattern' => $pattern,
            'search_in' => $searchIn,
            'results_count' => count($results),
            'results' => $results,
        ];
    }

    /**
     * Tool: Analyze File
     */
    private function toolAnalyzeFile(array $args): array {
        $filePath = $args['content_path'] ?? '';
        $unitId = $args['unit_id'] ?? null;

        if (empty($filePath)) {
            return ['error' => 'file_path is required'];
        }

        $db = $this->getDb();

        $sql = "SELECT
            c.*,
            ct.content_text,
            ct.content_summary,
            ct.extracted_keywords,
            ct.semantic_tags,
            ct.entities_detected,
            ct.readability_score,
            ct.sentiment_score,
            ct.language_confidence,
            ct.word_count,
            ct.character_count,
            ct.line_count,
            cty.type_name,
            cty.description as type_description
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        LEFT JOIN intelligence_content_types cty ON c.content_type_id = cty.content_type_id
        WHERE c.content_path = ?";

        $params = [$filePath];

        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['error' => 'File not found', 'content_path' => $filePath];
        }

        return [
            'file' => $result,
            'insights' => [
                'complexity' => $this->assessComplexity($result),
                'importance' => $this->assessImportance($result),
                'recommendations' => $this->generateRecommendations($result),
            ],
        ];
    }

    /**
     * Tool: Get File Content
     */
    private function toolGetFileContent(array $args): array {
        $filePath = $args['content_path'] ?? '';
        $unitId = $args['unit_id'] ?? null;

        if (empty($filePath)) {
            return ['error' => 'file_path is required'];
        }

        $db = $this->getDb();

        $sql = "SELECT
            c.content_path,
            c.content_name,
            c.unit_id,
            ct.content_text,
            ct.word_count,
            ct.character_count,
            cty.type_name
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        LEFT JOIN intelligence_content_types cty ON c.content_type_id = cty.content_type_id
        WHERE c.content_path = ?";

        $params = [$filePath];

        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['error' => 'File not found'];
        }

        return $result;
    }

    /**
     * Tool: List Satellites
     */
    private function toolListSatellites(array $args): array {
        $db = $this->getDb();

        $satellites = [];

        foreach (SATELLITES as $unitId => $config) {
            $stmt = $db->prepare("SELECT
                COUNT(*) as total_files,
                COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
                ROUND(AVG(ct.readability_score), 2) as avg_readability,
                SUM(ct.word_count) as total_words
            FROM intelligence_content c
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            WHERE c.unit_id = ?");
            $stmt->execute([$unitId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            $coverage = $stats['total_files'] > 0
                ? round(($stats['with_content'] / $stats['total_files']) * 100, 1)
                : 0;

            $satellites[] = [
                'unit_id' => $unitId,
                'name' => $config['name'],
                'url' => $config['url'],
                'total_files' => (int)$stats['total_files'],
                'with_content' => (int)$stats['with_content'],
                'coverage' => $coverage . '%',
                'avg_readability' => (float)$stats['avg_readability'],
                'total_words' => (int)$stats['total_words'],
                'status' => $stats['total_files'] > 0 ? 'active' : 'pending',
            ];
        }

        return ['satellites' => $satellites];
    }

    /**
     * Tool: Sync Satellite
     */
    private function toolSyncSatellite(array $args): array {
        $unitId = $args['unit_id'] ?? null;
        $action = $args['action'] ?? 'stats'; // stats, scan, or full_scan

        if ($unitId === null || !isset(SATELLITES[$unitId])) {
            return ['error' => 'Invalid unit_id'];
        }

        $satellite = SATELLITES[$unitId];

        // Build scan URL with new intelligence endpoint
        $scanUrl = rtrim($satellite['url'], '/') . '/api/intelligence/scan.php';

        // Prepare request payload
        $payload = [
            'action' => $action === 'full_scan' ? 'scan' : $action,
            'recursive' => true
        ];

        if ($action === 'scan' || $action === 'full_scan') {
            $payload['path'] = '.';
        }

        // Make API request
        $ch = curl_init($scanUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minute timeout for large scans

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'Failed to connect to satellite: ' . $error, 'url' => $scanUrl];
        }

        if ($httpCode !== 200) {
            return ['error' => 'Satellite returned HTTP ' . $httpCode, 'url' => $scanUrl];
        }

        $scanResult = json_decode($response, true);

        if (!$scanResult || !isset($scanResult['success'])) {
            return ['error' => 'Invalid satellite response', 'response' => substr($response, 0, 500)];
        }

        if (!$scanResult['success']) {
            return ['error' => $scanResult['error'] ?? 'Unknown error from satellite'];
        }

        // Process results based on action
        $result = [
            'satellite' => $satellite['name'],
            'unit_id' => $unitId,
            'action' => $action,
            'scan_url' => $scanUrl
        ];

        if ($action === 'stats') {
            $result['statistics'] = $scanResult['statistics'] ?? [];
        } elseif ($action === 'scan' || $action === 'full_scan') {
            $result['files_found'] = $scanResult['file_count'] ?? 0;
            $result['scanned_at'] = $scanResult['scanned_at'] ?? date('Y-m-d H:i:s');

            // If full_scan, process and store files in database
            if ($action === 'full_scan' && !empty($scanResult['files'])) {
                $stored = $this->storeScannedFiles($unitId, $scanResult['files']);
                $result['files_stored'] = $stored;
            }
        }

        return $result;
    }

    /**
     * Store scanned files from satellite into intelligence database
     */
    private function storeScannedFiles(int $unitId, array $files): int {
        $db = $this->getDb();
        $stored = 0;

        try {
            $db->beginTransaction();

            foreach ($files as $file) {
                // Check if file already exists
                $stmt = $db->prepare("SELECT content_id FROM intelligence_content
                                     WHERE unit_id = ? AND content_path = ?");
                $stmt->execute([$unitId, $file['path']]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Update existing
                    $stmt = $db->prepare("UPDATE intelligence_content
                                         SET content_size = ?, last_modified = ?, updated_at = NOW()
                                         WHERE content_id = ?");
                    $stmt->execute([
                        $file['size'],
                        $file['modified'],
                        $existing['content_id']
                    ]);
                } else {
                    // Insert new
                    $stmt = $db->prepare("INSERT INTO intelligence_content
                                         (unit_id, content_path, content_name, content_type,
                                          content_size, last_modified, created_at, updated_at)
                                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([
                        $unitId,
                        $file['path'],
                        $file['name'],
                        $file['extension'],
                        $file['size'],
                        $file['modified']
                    ]);
                    $stored++;
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $stored;
    }

    /**
     * Tool: Find Similar
     */
    private function toolFindSimilar(array $args): array {
        $refPath = $args['reference_path'] ?? '';
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 10, 50);

        if (empty($refPath)) {
            return ['error' => 'reference_path is required'];
        }

        $db = $this->getDb();

        // Get reference file
        $sql = "SELECT ct.extracted_keywords, ct.semantic_tags
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE c.content_path = ?";

        $params = [$refPath];
        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ref = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ref) {
            return ['error' => 'Reference file not found'];
        }

        // Find similar files
        $refKeywords = explode(',', $ref['extracted_keywords'] ?? '');
        $refTags = explode(',', $ref['semantic_tags'] ?? '');

        $keywordPattern = '%' . implode('%', array_slice($refKeywords, 0, 5)) . '%';
        $tagPattern = '%' . implode('%', array_slice($refTags, 0, 3)) . '%';

        $sql = "SELECT
            c.content_id,
            c.content_path,
            c.content_name,
            c.unit_id,
            ct.content_summary,
            ct.extracted_keywords,
            ct.semantic_tags,
            (
                CASE WHEN ct.extracted_keywords LIKE ? THEN 5 ELSE 0 END +
                CASE WHEN ct.semantic_tags LIKE ? THEN 3 ELSE 0 END
            ) AS similarity
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE c.content_path != ?
        HAVING similarity > 0
        ORDER BY similarity DESC
        LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute([$keywordPattern, $tagPattern, $refPath]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'reference_path' => $refPath,
            'results_count' => count($results),
            'similar_files' => $results,
        ];
    }

    /**
     * Tool: Explore by Tags
     */
    private function toolExploreByTags(array $args): array {
        $tags = $args['semantic_tags'] ?? [];
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 20, 100);

        if (empty($tags)) {
            return ['error' => 'tags array is required'];
        }

        $db = $this->getDb();

        $conditions = [];
        $params = [];

        foreach ($tags as $tag) {
            $conditions[] = "ct.semantic_tags LIKE ?";
            $params[] = '%' . $tag . '%';
        }

        $whereClause = implode(' AND ', $conditions);

        if ($unitId !== null) {
            $whereClause .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $sql = "SELECT
            c.content_id,
            c.content_path,
            c.content_name,
            c.unit_id,
            ct.content_summary,
            ct.semantic_tags,
            ct.extracted_keywords,
            ct.readability_score
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE {$whereClause}
        LIMIT " . (int)$limit;


        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'semantic_tags' => $tags,
            'results_count' => count($results),
            'files' => $results,
        ];
    }

    /**
     * Tool: Get Stats
     */
    private function toolGetStats(array $args): array {
        $breakdownBy = $args['breakdown_by'] ?? 'unit';

        $db = $this->getDb();

        // Overall stats
        $stmt = $db->query("SELECT
            COUNT(*) as total_files,
            COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
            ROUND(AVG(ct.readability_score), 2) as avg_readability,
            SUM(ct.word_count) as total_words,
            AVG(ct.sentiment_score) as avg_sentiment
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id");
        $overall = $stmt->fetch(PDO::FETCH_ASSOC);

        // Breakdown
        $breakdown = [];

        if ($breakdownBy === 'unit') {
            $stmt = $db->query("SELECT
                c.unit_id,
                COUNT(*) as file_count,
                COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
                ROUND(AVG(ct.readability_score), 2) as avg_readability,
                SUM(ct.word_count) as total_words
            FROM intelligence_content c
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            GROUP BY c.unit_id
            ORDER BY c.unit_id");
            $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($breakdownBy === 'type') {
            $stmt = $db->query("SELECT
                cty.type_name,
                COUNT(*) as file_count,
                ROUND(AVG(ct.readability_score), 2) as avg_readability
            FROM intelligence_content c
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            LEFT JOIN intelligence_content_types cty ON c.content_type_id = cty.content_type_id
            GROUP BY cty.type_name
            ORDER BY file_count DESC");
            $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($breakdownBy === 'category') {
            $stmt = $db->query("SELECT
                cat.category_name,
                cat.priority_weight,
                COUNT(c.content_id) as file_count,
                COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
                ROUND(AVG(ct.readability_score), 2) as avg_readability,
                SUM(ct.word_count) as total_words
            FROM kb_categories cat
            LEFT JOIN intelligence_content c ON c.category_id = cat.category_id
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            GROUP BY cat.category_id
            HAVING file_count > 0
            ORDER BY file_count DESC");
            $breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'stats' => [
                'overall' => $overall,
                'breakdown_by' => $breakdownBy,
                'breakdown' => $breakdown,
            ]
        ];
    }

    /**
     * Tool: Top Keywords
     */
    private function toolTopKeywords(array $args): array {
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 50, 200);

        $db = $this->getDb();

        $sql = "SELECT ct.extracted_keywords
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        WHERE ct.extracted_keywords IS NOT NULL";

        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$unitId]);
        } else {
            $stmt = $db->query($sql);
        }

        // Aggregate keywords
        $keywordCounts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $keywords = explode(',', $row['extracted_keywords']);
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    $keywordCounts[$keyword] = ($keywordCounts[$keyword] ?? 0) + 1;
                }
            }
        }

        arsort($keywordCounts);
        $topKeywords = array_slice($keywordCounts, 0, $limit, true);

        $results = [];
        foreach ($topKeywords as $keyword => $count) {
            $results[] = [
                'keyword' => $keyword,
                'count' => $count,
            ];
        }

        return [
            'total_unique_keywords' => count($keywordCounts),
            'results_count' => count($results),
            'keywords' => $results,
        ];
    }

    /**
     * Extract keywords from query
     */
    private function extractQueryKeywords(string $query): array {
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how'];

        $words = preg_split('/\s+/', strtolower($query));
        $keywords = [];

        foreach ($words as $word) {
            $word = preg_replace('/[^a-z0-9_]/', '', $word);
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return $keywords;
    }

    /**
     * Assess file complexity
     */
    private function assessComplexity(array $file): string {
        $wordCount = $file['word_count'] ?? 0;
        $readability = $file['readability_score'] ?? 50;

        if ($wordCount > 5000 || $readability < 30) {
            return 'high';
        } elseif ($wordCount > 1000 || $readability < 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Assess file importance
     */
    private function assessImportance(array $file): string {
        $tags = $file['semantic_tags'] ?? '';
        $keywords = $file['extracted_keywords'] ?? '';

        $importantTags = ['api', 'database', 'security', 'customer'];
        foreach ($importantTags as $tag) {
            if (stripos($tags, $tag) !== false) {
                return 'high';
            }
        }

        if (stripos($keywords, 'class') !== false || stripos($keywords, 'function') !== false) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations(array $file): array {
        $recommendations = [];

        $readability = $file['readability_score'] ?? 50;
        if ($readability < 40) {
            $recommendations[] = 'Consider improving readability - current score is low';
        }

        $wordCount = $file['word_count'] ?? 0;
        if ($wordCount > 5000) {
            $recommendations[] = 'Large file - consider splitting into smaller modules';
        }

        $tags = $file['semantic_tags'] ?? '';
        if (stripos($tags, 'security') !== false && stripos($tags, 'testing') === false) {
            $recommendations[] = 'Security-related file - ensure adequate test coverage';
        }

        return $recommendations;
    }

    /**
     * Tool: Search by Category
     * NEW: Category-aware search with priority weighting
     */
    private function toolSearchByCategory(array $args): array {
        $query = $args['query'] ?? '';
        $categoryId = $args['category_id'] ?? null;
        $categoryName = $args['category_name'] ?? null;
        $unitId = $args['unit_id'] ?? null;
        $limit = min($args['limit'] ?? 20, 100);

        if (empty($query)) {
            return ['error' => 'Query is required'];
        }

        $db = $this->getDb();

        // If category_name provided, get category_id
        if ($categoryName && !$categoryId) {
            $stmt = $db->prepare("SELECT category_id FROM kb_categories WHERE category_name = ?");
            $stmt->execute([$categoryName]);
            $categoryId = $stmt->fetchColumn();

            if (!$categoryId) {
                return ['error' => "Category not found: {$categoryName}"];
            }
        }

        // Build query with category weighting
        $sql = "SELECT
            c.content_id,
            c.content_path,
            c.content_name,
            c.unit_id,
            c.category_id,
            cat.category_name,
            cat.priority_weight,
            ct.content_summary,
            ct.extracted_keywords,
            ct.semantic_tags,
            ct.readability_score,
            ct.word_count,
            (
                MATCH(ct.content_text) AGAINST(? IN NATURAL LANGUAGE MODE) *
                IFNULL(cat.priority_weight, 1.0) * 10 +
                CASE WHEN ct.extracted_keywords LIKE ? THEN 5 ELSE 0 END +
                CASE WHEN ct.semantic_tags LIKE ? THEN 3 ELSE 0 END +
                CASE WHEN c.content_name LIKE ? THEN 8 ELSE 0 END
            ) AS weighted_relevance
        FROM intelligence_content c
        LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
        LEFT JOIN kb_categories cat ON c.category_id = cat.category_id
        WHERE ct.content_text IS NOT NULL";

        $keywords = $this->extractQueryKeywords($query);
        $keywordPattern = '%' . implode('%', $keywords) . '%';

        $params = [$query, $keywordPattern, $keywordPattern, $keywordPattern];

        if ($categoryId !== null) {
            $sql .= " AND c.category_id = ?";
            $params[] = $categoryId;
        }

        if ($unitId !== null) {
            $sql .= " AND c.unit_id = ?";
            $params[] = $unitId;
        }

        $sql .= " HAVING weighted_relevance > 0
        ORDER BY weighted_relevance DESC
        LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get category distribution
        $categoryDist = [];
        foreach ($results as $result) {
            $catName = $result['category_name'] ?? 'Uncategorized';
            $categoryDist[$catName] = ($categoryDist[$catName] ?? 0) + 1;
        }

        return [
            'query' => $query,
            'category_filter' => $categoryId ? [
                'id' => $categoryId,
                'name' => $categoryName ?? $results[0]['category_name'] ?? null,
            ] : null,
            'results_count' => count($results),
            'category_distribution' => $categoryDist,
            'results' => $results,
        ];
    }

    /**
     * Get or create session ID
     */
    private function getOrCreateSession(): string {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Generate session ID from IP + User Agent + Date
        $sessionId = hash('sha256', $ipAddress . $userAgent . date('Y-m-d'));

        try {
            $db = $this->getDb();

            // Insert or update session
            $stmt = $db->prepare("
                INSERT INTO mcp_sessions (session_id, user_agent, ip_address, total_queries, total_tools_used)
                VALUES (?, ?, ?, 0, 0)
                ON DUPLICATE KEY UPDATE
                    last_seen = CURRENT_TIMESTAMP,
                    total_queries = total_queries + 1
            ");
            $stmt->execute([$sessionId, $userAgent, $ipAddress]);
        } catch (Exception $e) {
            // Silently fail if analytics tables don't exist
        }

        return $sessionId;
    }

    /**
     * Log tool usage
     */
    private function logToolUsage(string $toolName, array $arguments, float $executionTimeMs, int $resultsCount, bool $success, ?string $errorMessage = null): void {
        try {
            $db = $this->getDb();

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

            $stmt = $db->prepare("
                INSERT INTO mcp_tool_usage
                    (tool_name, arguments, execution_time_ms, results_count, success, error_message, user_agent, ip_address, session_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $toolName,
                json_encode($arguments),
                (int)$executionTimeMs,
                $resultsCount,
                $success,
                $errorMessage,
                $userAgent,
                $ipAddress,
                $this->sessionId,
            ]);

            // Update session tool count
            $db->exec("UPDATE mcp_sessions SET total_tools_used = total_tools_used + 1 WHERE session_id = '{$this->sessionId}'");

        } catch (Exception $e) {
            // Silently fail
        }
    }

    /**
     * Log search query analytics
     */
    private function logSearchAnalytics(string $queryText, string $queryType, ?int $categoryId, ?int $unitId, int $resultsFound, ?float $avgRelevance, float $executionTimeMs): void {
        try {
            $db = $this->getDb();

            $stmt = $db->prepare("
                INSERT INTO mcp_search_analytics
                    (query_text, query_type, category_id, unit_id, results_found, avg_relevance, execution_time_ms, session_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $queryText,
                $queryType,
                $categoryId,
                $unitId,
                $resultsFound,
                $avgRelevance,
                (int)$executionTimeMs,
                $this->sessionId,
            ]);

            // Update category usage stats if category search
            if ($categoryId && $queryType === 'category') {
                $this->updateCategoryUsage($categoryId, $resultsFound, $avgRelevance);
            }

            // Update popular queries cache
            $this->updatePopularQueriesCache($queryText, $executionTimeMs);

        } catch (Exception $e) {
            // Silently fail
        }
    }

    /**
     * Update category usage statistics
     */
    private function updateCategoryUsage(int $categoryId, int $resultsReturned, ?float $avgRelevance): void {
        try {
            $db = $this->getDb();

            $stmt = $db->prepare("
                INSERT INTO mcp_category_usage
                    (category_id, search_count, results_returned, avg_relevance, date)
                VALUES (?, 1, ?, ?, CURDATE())
                ON DUPLICATE KEY UPDATE
                    search_count = search_count + 1,
                    results_returned = results_returned + ?,
                    avg_relevance = (avg_relevance * search_count + ?) / (search_count + 1),
                    last_used = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                $categoryId,
                $resultsReturned,
                $avgRelevance,
                $resultsReturned,
                $avgRelevance ?? 0,
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }

    /**
     * Update popular queries cache
     */
    private function updatePopularQueriesCache(string $queryText, float $executionTimeMs): void {
        try {
            $db = $this->getDb();

            $queryHash = hash('sha256', strtolower(trim($queryText)));

            $stmt = $db->prepare("
                INSERT INTO mcp_popular_queries
                    (query_hash, query_text, query_count, avg_execution_time_ms)
                VALUES (?, ?, 1, ?)
                ON DUPLICATE KEY UPDATE
                    query_count = query_count + 1,
                    avg_execution_time_ms = (avg_execution_time_ms * query_count + ?) / (query_count + 1),
                    last_executed = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                $queryHash,
                $queryText,
                (int)$executionTimeMs,
                (int)$executionTimeMs,
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }

    /**
     * Log performance metric
     */
    private function logPerformanceMetric(string $metricType, float $metricValue, array $context = []): void {
        try {
            $db = $this->getDb();

            $stmt = $db->prepare("
                INSERT INTO mcp_performance_metrics
                    (metric_type, metric_value, context)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $metricType,
                $metricValue,
                json_encode($context),
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }

    /**
     * Tool #12: List Categories
     *
     * List all business categories with statistics
     */
    private function toolListCategories(array $args): array {
        $parentId = $args['parent_id'] ?? null;
        $includeChildren = $args['include_children'] ?? true;
        $minPriority = $args['min_priority'] ?? null;
        $orderBy = $args['order_by'] ?? 'priority';

        $db = $this->getDb();

        // Build query
        $sql = "SELECT
            category_id,
            category_name,
            parent_category_id,
            description,
            priority_weight,
            file_count,
            created_at,
            updated_at
        FROM kb_categories
        WHERE 1=1";

        $params = [];

        if ($parentId !== null) {
            $sql .= " AND parent_category_id = ?";
            $params[] = $parentId;
        } elseif (!$includeChildren) {
            // Only top-level categories
            $sql .= " AND parent_category_id IS NULL";
        }

        if ($minPriority !== null) {
            $sql .= " AND priority_weight >= ?";
            $params[] = $minPriority;
        }

        // Order by
        $orderColumn = match($orderBy) {
            'file_count' => 'file_count DESC',
            'name' => 'category_name ASC',
            default => 'priority_weight DESC, category_name ASC',
        };

        $sql .= " ORDER BY {$orderColumn}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add hierarchy information
        foreach ($categories as &$category) {
            // Get parent name if exists
            if ($category['parent_category_id']) {
                $parentStmt = $db->prepare("SELECT category_name FROM kb_categories WHERE category_id = ?");
                $parentStmt->execute([$category['parent_category_id']]);
                $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
                $category['parent_name'] = $parent ? $parent['category_name'] : null;
            }

            // Get children count
            $childStmt = $db->prepare("SELECT COUNT(*) FROM kb_categories WHERE parent_category_id = ?");
            $childStmt->execute([$category['category_id']]);
            $category['children_count'] = (int)$childStmt->fetchColumn();

            // Clean up integers
            $category['category_id'] = (int)$category['category_id'];
            $category['parent_category_id'] = $category['parent_category_id'] ? (int)$category['parent_category_id'] : null;
            $category['priority_weight'] = (float)$category['priority_weight'];
            $category['file_count'] = (int)$category['file_count'];
        }

        // Get summary stats
        $totalFiles = $db->query("SELECT COUNT(*) FROM intelligence_content WHERE category_id IS NOT NULL")->fetchColumn();
        $totalCategories = count($categories);

        return [
            'success' => true,
            'categories' => $categories,
            'summary' => [
                'total_categories' => $totalCategories,
                'total_categorized_files' => (int)$totalFiles,
                'avg_files_per_category' => $totalCategories > 0 ? round($totalFiles / $totalCategories, 1) : 0,
            ],
            'results_count' => $totalCategories,
        ];
    }

    /**
     * Tool #13: Get Analytics
     *
     * Retrieve analytics data from tracking system
     */
    private function toolGetAnalytics(array $args): array {
        $action = $args['action'] ?? 'overview';
        $timeframe = $args['timeframe'] ?? '24h';
        $limit = $args['limit'] ?? 50;
        $threshold = $args['threshold'] ?? 500;

        $db = $this->getDb();

        // Parse timeframe
        $hours = match($timeframe) {
            '1h' => 1,
            '6h' => 6,
            '24h' => 24,
            '7d' => 168,
            '30d' => 720,
            default => 24,
        };

        $since = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        switch ($action) {
            case 'overview':
                // Overall statistics
                $stats = $db->query("
                    SELECT
                        COUNT(*) as total_calls,
                        COUNT(DISTINCT session_id) as unique_sessions,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_calls,
                        AVG(execution_time_ms) as avg_execution_time,
                        SUM(results_count) as total_results_returned,
                        MIN(timestamp) as first_call,
                        MAX(timestamp) as last_call
                    FROM mcp_tool_usage
                    WHERE timestamp >= '{$since}'
                ")->fetch(PDO::FETCH_ASSOC);

                $stats['success_rate'] = $stats['total_calls'] > 0
                    ? round(($stats['successful_calls'] / $stats['total_calls']) * 100, 1)
                    : 0;

                // Tool popularity
                $toolPopularity = $db->query("
                    SELECT
                        tool_name,
                        COUNT(*) as call_count,
                        AVG(execution_time_ms) as avg_time,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count
                    FROM mcp_tool_usage
                    WHERE timestamp >= '{$since}'
                    GROUP BY tool_name
                    ORDER BY call_count DESC
                    LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);

                // Category performance
                $categoryPerf = $db->query("
                    SELECT
                        c.category_name,
                        cu.search_count,
                        cu.results_returned,
                        cu.avg_relevance,
                        cu.last_used
                    FROM mcp_category_usage cu
                    JOIN kb_categories c ON cu.category_id = c.category_id
                    WHERE cu.date >= DATE('{$since}')
                    ORDER BY cu.search_count DESC
                    LIMIT 10
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'analytics' => [
                        'action' => 'overview',
                        'timeframe' => $timeframe,
                        'overall' => $stats,
                        'tool_popularity' => $toolPopularity,
                        'category_performance' => $categoryPerf,
                    ]
                ];

            case 'hourly':
                $hourlyStats = $db->query("
                    SELECT
                        DATE_FORMAT(timestamp, '%Y-%m-%d %H:00') as hour,
                        COUNT(*) as calls,
                        AVG(execution_time_ms) as avg_time,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful
                    FROM mcp_tool_usage
                    WHERE timestamp >= '{$since}'
                    GROUP BY hour
                    ORDER BY hour DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'hourly',
                    'timeframe' => $timeframe,
                    'hourly_breakdown' => $hourlyStats,
                    'results_count' => count($hourlyStats),
                ];

            case 'failed':
                $failedCalls = $db->prepare("
                    SELECT
                        tool_name,
                        error_message,
                        execution_time_ms,
                        timestamp,
                        arguments
                    FROM mcp_tool_usage
                    WHERE success = 0
                        AND timestamp >= ?
                    ORDER BY timestamp DESC
                    LIMIT ?
                ");
                $failedCalls->execute([$since, $limit]);
                $failed = $failedCalls->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'failed',
                    'timeframe' => $timeframe,
                    'failed_requests' => $failed,
                    'results_count' => count($failed),
                ];

            case 'slow':
                $slowQueries = $db->prepare("
                    SELECT
                        tool_name,
                        execution_time_ms,
                        results_count,
                        timestamp,
                        arguments
                    FROM mcp_tool_usage
                    WHERE execution_time_ms > ?
                        AND timestamp >= ?
                    ORDER BY execution_time_ms DESC
                    LIMIT ?
                ");
                $slowQueries->execute([$threshold, $since, $limit]);
                $slow = $slowQueries->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'slow',
                    'timeframe' => $timeframe,
                    'threshold_ms' => $threshold,
                    'slow_queries' => $slow,
                    'results_count' => count($slow),
                ];

            case 'popular_queries':
                $popularQueries = $db->query("
                    SELECT
                        query_hash,
                        query_text,
                        query_count,
                        avg_execution_time_ms,
                        last_executed
                    FROM mcp_popular_queries
                    WHERE last_executed >= '{$since}'
                    ORDER BY query_count DESC
                    LIMIT {$limit}
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'popular_queries',
                    'timeframe' => $timeframe,
                    'popular_queries' => $popularQueries,
                    'results_count' => count($popularQueries),
                ];

            case 'tool_usage':
                $toolUsage = $db->query("
                    SELECT
                        tool_name,
                        COUNT(*) as total_calls,
                        AVG(execution_time_ms) as avg_execution_time,
                        MIN(execution_time_ms) as min_execution_time,
                        MAX(execution_time_ms) as max_execution_time,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_calls,
                        SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failed_calls,
                        AVG(results_count) as avg_results
                    FROM mcp_tool_usage
                    WHERE timestamp >= '{$since}'
                    GROUP BY tool_name
                    ORDER BY total_calls DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'tool_usage',
                    'timeframe' => $timeframe,
                    'tool_statistics' => $toolUsage,
                    'results_count' => count($toolUsage),
                ];

            case 'category_performance':
                $categoryStats = $db->query("
                    SELECT
                        c.category_id,
                        c.category_name,
                        c.priority_weight,
                        SUM(cu.search_count) as total_searches,
                        AVG(cu.avg_relevance) as avg_relevance,
                        SUM(cu.results_returned) as total_results,
                        MAX(cu.last_used) as last_used
                    FROM mcp_category_usage cu
                    JOIN kb_categories c ON cu.category_id = c.category_id
                    WHERE cu.date >= DATE('{$since}')
                    GROUP BY c.category_id
                    ORDER BY total_searches DESC
                ")->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'success' => true,
                    'action' => 'category_performance',
                    'timeframe' => $timeframe,
                    'category_statistics' => $categoryStats,
                    'results_count' => count($categoryStats),
                ];

            default:
                return ['error' => "Unknown analytics action: {$action}"];
        }
    }

    /**
     * Tool: Health Check
     */
    private function toolHealthCheck(array $args): array {
        $db = $this->getDb();
        $startTime = microtime(true);

        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'components' => []
        ];

        // Database connectivity
        try {
            $dbStart = microtime(true);
            $stmt = $db->query("SELECT COUNT(*) as total FROM intelligence_content LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $dbTime = (microtime(true) - $dbStart) * 1000;

            $health['components']['database'] = [
                'status' => 'healthy',
                'response_time_ms' => round($dbTime, 2),
                'total_files' => $result['total'] ?? 0
            ];
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['components']['database'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // Content indexing health
        try {
            $contentStats = $db->query("
                SELECT
                    COUNT(*) as total_files,
                    COUNT(CASE WHEN ct.content_text IS NOT NULL THEN 1 END) as with_content,
                    COUNT(CASE WHEN ct.extracted_keywords IS NOT NULL THEN 1 END) as with_keywords
                FROM intelligence_content c
                LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            ")->fetch(PDO::FETCH_ASSOC);

            $health['components']['content_indexing'] = [
                'status' => 'healthy',
                'total_files' => (int)$contentStats['total_files'],
                'files_with_content' => (int)$contentStats['with_content'],
                'files_with_keywords' => (int)$contentStats['with_keywords'],
                'content_coverage' => $contentStats['total_files'] > 0
                    ? round(($contentStats['with_content'] / $contentStats['total_files']) * 100, 1)
                    : 0
            ];
        } catch (Exception $e) {
            $health['status'] = 'degraded';
            $health['components']['content_indexing'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // MCP tools health
        try {
            $toolStats = $db->query("
                SELECT
                    COUNT(*) as total_calls,
                    AVG(execution_time_ms) as avg_response_time,
                    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_calls
                FROM mcp_tool_usage
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ")->fetch(PDO::FETCH_ASSOC);

            $successRate = $toolStats['total_calls'] > 0
                ? round(($toolStats['successful_calls'] / $toolStats['total_calls']) * 100, 1)
                : 100;

            $health['components']['mcp_tools'] = [
                'status' => $successRate >= 95 ? 'healthy' : ($successRate >= 80 ? 'degraded' : 'unhealthy'),
                'calls_24h' => (int)($toolStats['total_calls'] ?? 0),
                'success_rate' => $successRate,
                'avg_response_time_ms' => round((float)(is_numeric($toolStats['avg_response_time'] ?? 0) ? $toolStats['avg_response_time'] : 0), 2)
            ];

            if ($successRate < 95) {
                $health['status'] = $successRate >= 80 ? 'degraded' : 'unhealthy';
            }
        } catch (Exception $e) {
            $health['status'] = 'degraded';
            $health['components']['mcp_tools'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // Satellites health
        $satelliteHealth = [];
        foreach (SATELLITES as $unitId => $config) {
            try {
                $satStart = microtime(true);
                $satStmt = $db->prepare("
                    SELECT COUNT(*) as file_count, MAX(c.created_at) as last_sync
                    FROM intelligence_content c
                    WHERE c.unit_id = ?
                ");
                $satStmt->execute([$unitId]);
                $satResult = $satStmt->fetch(PDO::FETCH_ASSOC);
                $satTime = (microtime(true) - $satStart) * 1000;

                $satelliteHealth[$config['name']] = [
                    'status' => 'healthy',
                    'unit_id' => $unitId,
                    'file_count' => (int)($satResult['file_count'] ?? 0),
                    'last_sync' => $satResult['last_sync'] ?? null,
                    'query_time_ms' => round((float)$satTime, 2)
                ];
            } catch (Exception $e) {
                $satelliteHealth[$config['name']] = [
                    'status' => 'error',
                    'unit_id' => $unitId,
                    'error' => $e->getMessage()
                ];
                $health['status'] = 'degraded';
            }
        }

        $health['components']['satellites'] = $satelliteHealth;
        $health['total_response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'health' => $health
        ];
    }
}

// Main execution
$input = file_get_contents('php://input');
$request = json_decode($input, true);

if (!$request || !isset($request['jsonrpc']) || $request['jsonrpc'] !== '2.0') {
    echo json_encode(['error' => 'Invalid JSON-RPC request']);
    exit(1);
}

$server = new MCPServer();
$method = $request['method'] ?? '';
$params = $request['params'] ?? [];
$id = $request['id'] ?? null;

try {
    $result = match($method) {
        'initialize' => $server->initialize(),
        'tools/list' => ['tools' => $server->listTools()],
        'tools/call' => $server->callTool($params['name'] ?? '', $params['arguments'] ?? []),
        default => throw new Exception("Unknown method: {$method}"),
    };

    echo json_encode([
        'jsonrpc' => '2.0',
        'id' => $id,
        'result' => $result,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => -32603,
            'message' => $e->getMessage(),
        ],
    ]);
}
