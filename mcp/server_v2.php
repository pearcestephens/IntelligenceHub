<?php
/**
 * Intelligence Hub MCP Server v2.0
 * 
 * Enhanced Model Context Protocol server with:
 * - Advanced semantic search across all satellites
 * - Full-text content search with NLP
 * - Real-time satellite coordination
 * - Code intelligence and analysis
 * - User-friendly natural language queries
 * 
 * @package IntelligenceHub\MCP
 * @version 2.0.0
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
        'hdgwrzntwa',
        'bFUdRjh4Jx',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => ['code' => -32603, 'message' => 'Database connection failed'],
        'id' => null
    ]);
    exit;
}

// Parse request
$rawInput = file_get_contents('php://input');
$request = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => ['code' => -32700, 'message' => 'Invalid JSON'],
        'id' => null
    ]);
    exit;
}

/**
 * Enhanced MCP Server with Advanced Tools
 */
class IntelligenceHubMCP
{
    private PDO $pdo;
    private string $version = '2.0.0';
    private array $satellites;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->satellites = [
            1 => ['name' => 'Intelligence Hub', 'url' => 'https://gpt.ecigdis.co.nz'],
            2 => ['name' => 'CIS', 'url' => 'https://staff.vapeshed.co.nz'],
            3 => ['name' => 'VapeShed', 'url' => 'https://vapeshed.co.nz'],
            4 => ['name' => 'Wholesale', 'url' => 'https://wholesale.ecigdis.co.nz']
        ];
    }
    
    public function handleRequest(array $request): array
    {
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];
        $id = $request['id'] ?? null;
        
        try {
            $result = match ($method) {
                'initialize' => $this->initialize($params),
                'tools/list' => $this->listTools(),
                'tools/call' => $this->callTool($params),
                'resources/list' => $this->listResources(),
                'resources/read' => $this->readResource($params),
                'prompts/list' => $this->listPrompts(),
                'prompts/get' => $this->getPrompt($params),
                default => throw new Exception("Unknown method: {$method}")
            };
            
            return [
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            ];
            
        } catch (Exception $e) {
            return [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage()
                ],
                'id' => $id
            ];
        }
    }
    
    private function initialize(array $params): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'serverInfo' => [
                'name' => 'Intelligence Hub MCP Server',
                'version' => $this->version
            ],
            'capabilities' => [
                'tools' => [
                    'listChanged' => false
                ],
                'resources' => [
                    'subscribe' => true,
                    'listChanged' => true
                ],
                'prompts' => [
                    'listChanged' => false
                ],
                'experimental' => [
                    'semantic_search' => true,
                    'satellite_coordination' => true,
                    'nlp_analysis' => true,
                    'code_intelligence' => true
                ]
            ]
        ];
    }
    
    private function listTools(): array
    {
        return [
            'tools' => [
                // Search Tools
                [
                    'name' => 'semantic_search',
                    'description' => 'Search all code and documentation using natural language. Finds relevant files, functions, and concepts across all satellites.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Natural language search query (e.g., "how do we handle customer refunds")'
                            ],
                            'unit_ids' => [
                                'type' => 'array',
                                'description' => 'Optional: Filter by satellite (1=Hub, 2=CIS, 3=VapeShed, 4=Wholesale)',
                                'items' => ['type' => 'integer']
                            ],
                            'content_types' => [
                                'type' => 'array',
                                'description' => 'Optional: Filter by type (API, Controller, Model, Config, etc)',
                                'items' => ['type' => 'string']
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'Max results (default: 10)',
                                'default' => 10
                            ]
                        ],
                        'required' => ['query']
                    ]
                ],
                [
                    'name' => 'find_code',
                    'description' => 'Find specific code patterns, functions, classes, or keywords. More precise than semantic search.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'pattern' => [
                                'type' => 'string',
                                'description' => 'Code pattern to find (function name, class name, or keyword)'
                            ],
                            'search_in' => [
                                'type' => 'string',
                                'enum' => ['content_text', 'keywords', 'semantic_tags', 'entities'],
                                'description' => 'Where to search',
                                'default' => 'content_text'
                            ],
                            'unit_ids' => [
                                'type' => 'array',
                                'items' => ['type' => 'integer']
                            ]
                        ],
                        'required' => ['pattern']
                    ]
                ],
                
                // Content Analysis Tools
                [
                    'name' => 'analyze_file',
                    'description' => 'Get detailed analysis of a specific file including content, keywords, complexity, and relationships.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'file_path' => [
                                'type' => 'string',
                                'description' => 'Full or partial file path'
                            ]
                        ],
                        'required' => ['file_path']
                    ]
                ],
                [
                    'name' => 'get_file_content',
                    'description' => 'Retrieve full content of a file with all metadata.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'content_id' => [
                                'type' => 'integer',
                                'description' => 'Content ID from search results'
                            ]
                        ],
                        'required' => ['content_id']
                    ]
                ],
                
                // Satellite Tools
                [
                    'name' => 'list_satellites',
                    'description' => 'Get status and statistics for all satellite systems.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => []
                    ]
                ],
                [
                    'name' => 'sync_satellite',
                    'description' => 'Trigger a sync from a specific satellite to pull latest files.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'unit_id' => [
                                'type' => 'integer',
                                'description' => 'Satellite unit ID (2=CIS, 3=VapeShed, 4=Wholesale)'
                            ],
                            'batch_size' => [
                                'type' => 'integer',
                                'description' => 'Files per batch',
                                'default' => 100
                            ]
                        ],
                        'required' => ['unit_id']
                    ]
                ],
                
                // Discovery Tools
                [
                    'name' => 'find_similar',
                    'description' => 'Find files similar to a given file based on keywords and semantic tags.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'content_id' => [
                                'type' => 'integer',
                                'description' => 'Reference content ID'
                            ],
                            'limit' => [
                                'type' => 'integer',
                                'default' => 10
                            ]
                        ],
                        'required' => ['content_id']
                    ]
                ],
                [
                    'name' => 'explore_by_tags',
                    'description' => 'Browse files by semantic tags (api, database, customer, inventory, etc).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'tags' => [
                                'type' => 'array',
                                'description' => 'Tags to filter by',
                                'items' => ['type' => 'string']
                            ],
                            'match_all' => [
                                'type' => 'boolean',
                                'description' => 'Require all tags (AND) or any tag (OR)',
                                'default' => false
                            ]
                        ],
                        'required' => ['tags']
                    ]
                ],
                
                // Statistics Tools
                [
                    'name' => 'get_stats',
                    'description' => 'Get comprehensive statistics about the intelligence system.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'unit_id' => [
                                'type' => 'integer',
                                'description' => 'Optional: Stats for specific satellite'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'top_keywords',
                    'description' => 'Get most common keywords across all files or a specific satellite.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'unit_id' => ['type' => 'integer'],
                            'limit' => ['type' => 'integer', 'default' => 20]
                        ]
                    ]
                ]
            ]
        ];
    }
