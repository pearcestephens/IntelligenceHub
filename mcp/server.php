<?php
/**
 * CIS Knowledge Base MCP Server
 * 
 * Model Context Protocol server that exposes KB intelligence to GitHub Copilot
 * and other AI coding assistants
 * 
 * @package CIS\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors in response
ini_set('log_errors', '1');

header('Content-Type: application/json');

// Load advanced tools
require_once __DIR__ . '/advanced_tools.php';

// Database connection
$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';
$password = 'bFUdRjh4Jx';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'jsonrpc' => '2.0',
        'error' => [
            'code' => -32603,
            'message' => 'Internal error: Database connection failed'
        ],
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
        'error' => [
            'code' => -32700,
            'message' => 'Parse error: Invalid JSON'
        ],
        'id' => null
    ]);
    exit;
}

// MCP Protocol Handler
class MCPServer
{
    private $pdo;
    private $version = '1.0.0';
    private $serverName = 'CIS Knowledge Base MCP Server';
    private $advancedTools;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->advancedTools = new MCPAdvancedTools($pdo);
    }
    
    /**
     * Handle MCP request
     */
    public function handleRequest(array $request): array
    {
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];
        $id = $request['id'] ?? null;
        
        try {
            switch ($method) {
                case 'initialize':
                    $result = $this->initialize($params);
                    break;
                    
                case 'tools/list':
                    $result = $this->listTools();
                    break;
                    
                case 'tools/call':
                    $result = $this->callTool($params);
                    break;
                    
                case 'resources/list':
                    $result = $this->listResources();
                    break;
                    
                case 'resources/read':
                    $result = $this->readResource($params);
                    break;
                    
                case 'prompts/list':
                    $result = $this->listPrompts();
                    break;
                    
                case 'prompts/get':
                    $result = $this->getPrompt($params);
                    break;
                    
                default:
                    throw new Exception("Method not found: $method", -32601);
            }
            
            return [
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            ];
            
        } catch (Exception $e) {
            return [
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => $e->getCode() ?: -32603,
                    'message' => $e->getMessage()
                ],
                'id' => $id
            ];
        }
    }
    
    /**
     * Initialize MCP server
     */
    private function initialize(array $params): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => ['listChanged' => false],
                'resources' => ['subscribe' => false, 'listChanged' => false],
                'prompts' => ['listChanged' => false]
            ],
            'serverInfo' => [
                'name' => $this->serverName,
                'version' => $this->version
            ]
        ];
    }
    
    /**
     * List available tools
     */
    private function listTools(): array
    {
        return [
            'tools' => [
                [
                    'name' => 'kb_semantic_search',
                    'description' => 'Search the entire codebase semantically for concepts, patterns, or implementations. Returns relevant code snippets with context.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Natural language search query (e.g., "how do we handle Vend API errors", "database connection pattern")'
                            ],
                            'file_type' => [
                                'type' => 'string',
                                'description' => 'Filter by file type (php, js, html, sql, etc.)',
                                'enum' => ['php', 'js', 'html', 'css', 'sql', 'json', 'md']
                            ],
                            'category' => [
                                'type' => 'string',
                                'description' => 'Filter by business category'
                            ],
                            'limit' => [
                                'type' => 'number',
                                'description' => 'Maximum results to return',
                                'default' => 10
                            ]
                        ],
                        'required' => ['query']
                    ]
                ],
                [
                    'name' => 'get_file_context',
                    'description' => 'Get comprehensive context about a specific file including its purpose, dependencies, who depends on it, and related patterns.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'file_path' => [
                                'type' => 'string',
                                'description' => 'Relative path to the file'
                            ],
                            'include_content' => [
                                'type' => 'boolean',
                                'description' => 'Include file content preview',
                                'default' => true
                            ],
                            'include_related' => [
                                'type' => 'boolean',
                                'description' => 'Include related/similar files',
                                'default' => true
                            ]
                        ],
                        'required' => ['file_path']
                    ]
                ],
                [
                    'name' => 'find_patterns',
                    'description' => 'Find code patterns used across the codebase (e.g., "how we connect to database", "error handling pattern", "API call pattern").',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'pattern_type' => [
                                'type' => 'string',
                                'description' => 'Type of pattern to find',
                                'enum' => ['database', 'api', 'error_handling', 'authentication', 'validation', 'logging']
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Describe the pattern you are looking for'
                            ]
                        ],
                        'required' => ['description']
                    ]
                ],
                [
                    'name' => 'analyze_quality',
                    'description' => 'Analyze code quality metrics for a file or check code against project standards.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'file_path' => [
                                'type' => 'string',
                                'description' => 'File to analyze (optional if providing code)'
                            ],
                            'code' => [
                                'type' => 'string',
                                'description' => 'Code snippet to analyze (optional if providing file_path)'
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'get_architecture',
                    'description' => 'Get system architecture information including modules, database schema, API endpoints, and integration points.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'component' => [
                                'type' => 'string',
                                'description' => 'Specific component to get info about',
                                'enum' => ['modules', 'database', 'api', 'integrations', 'all']
                            ]
                        ],
                        'required' => ['component']
                    ]
                ],
                [
                    'name' => 'suggest_implementation',
                    'description' => 'Get implementation suggestions based on existing codebase patterns. Analyzes similar code and provides templates.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'task' => [
                                'type' => 'string',
                                'description' => 'What you want to implement (e.g., "API endpoint for webhooks")'
                            ],
                            'file_type' => [
                                'type' => 'string',
                                'description' => 'Type of file',
                                'default' => 'php'
                            ],
                            'context' => [
                                'type' => 'string',
                                'description' => 'Additional context about the implementation'
                            ]
                        ],
                        'required' => ['task']
                    ]
                ],
                [
                    'name' => 'analyze_impact',
                    'description' => 'Analyze the impact of modifying, deleting, or renaming a file. Shows which files might be affected.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'file_path' => [
                                'type' => 'string',
                                'description' => 'Path to the file'
                            ],
                            'change_type' => [
                                'type' => 'string',
                                'description' => 'Type of change',
                                'enum' => ['modify', 'delete', 'rename'],
                                'default' => 'modify'
                            ]
                        ],
                        'required' => ['file_path']
                    ]
                ],
                [
                    'name' => 'enforce_standards',
                    'description' => 'Check code against CIS project standards. Returns violations and compliance score.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => [
                                'type' => 'string',
                                'description' => 'Code to check'
                            ],
                            'file_type' => [
                                'type' => 'string',
                                'description' => 'File type',
                                'default' => 'php'
                            ]
                        ],
                        'required' => ['code']
                    ]
                ],
                [
                    'name' => 'suggest_refactoring',
                    'description' => 'Get refactoring suggestions for multiple files. Identifies cleanup opportunities and consolidation targets.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => [
                                'type' => 'string',
                                'description' => 'Category to analyze'
                            ],
                            'refactor_type' => [
                                'type' => 'string',
                                'description' => 'Type of refactoring',
                                'enum' => ['cleanup', 'consolidation', 'optimization'],
                                'default' => 'cleanup'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Call a tool
     */
    private function callTool(array $params): array
    {
        $toolName = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        switch ($toolName) {
            case 'kb_semantic_search':
                return $this->semanticSearch($arguments);
                
            case 'get_file_context':
                return $this->getFileContext($arguments);
                
            case 'find_patterns':
                return $this->findPatterns($arguments);
                
            case 'analyze_quality':
                return $this->analyzeQuality($arguments);
                
            case 'get_architecture':
                return $this->getArchitecture($arguments);
                
            case 'suggest_implementation':
                return $this->advancedTools->suggestImplementation($arguments);
                
            case 'analyze_impact':
                return $this->advancedTools->analyzeImpact($arguments);
                
            case 'enforce_standards':
                return $this->advancedTools->enforceStandards($arguments);
                
            case 'suggest_refactoring':
                return $this->advancedTools->suggestRefactoring($arguments);
                
            default:
                throw new Exception("Unknown tool: $toolName", -32602);
        }
    }
    
    /**
     * Semantic search across codebase
     */
    private function semanticSearch(array $args): array
    {
        $query = $args['query'] ?? '';
        $fileType = $args['file_type'] ?? null;
        $category = $args['category'] ?? null;
        $limit = min((int)($args['limit'] ?? 10), 50); // Max 50 results
        
        if (empty($query)) {
            throw new Exception("Query parameter is required", -32602);
        }
        
        // Build search query
        $sql = "
            SELECT 
                f.file_path,
                f.file_name,
                f.file_type,
                f.file_size,
                s.search_content,
                s.search_terms,
                q.quality_score,
                o.category,
                o.business_context,
                c.concepts,
                c.patterns
            FROM kb_files f
            LEFT JOIN kb_search_index s ON f.id = s.file_id
            LEFT JOIN simple_quality q ON f.id = q.file_id
            LEFT JOIN kb_organization o ON f.id = o.file_id
            LEFT JOIN kb_quality c ON f.id = c.file_id
            WHERE (
                s.search_content LIKE :query
                OR s.search_terms LIKE :query
                OR f.file_name LIKE :query
                OR f.file_path LIKE :query
            )
        ";
        
        $params = [':query' => "%{$query}%"];
        
        if ($fileType) {
            $sql .= " AND f.file_type = :file_type";
            $params[':file_type'] = $fileType;
        }
        
        if ($category) {
            $sql .= " AND o.category = :category";
            $params[':category'] = $category;
        }
        
        $sql .= " ORDER BY q.quality_score DESC, f.file_size ASC LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format results
        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'file_path' => $row['file_path'],
                'file_name' => $row['file_name'],
                'file_type' => $row['file_type'],
                'category' => $row['category'] ?? 'Uncategorized',
                'business_context' => $row['business_context'] ?? '',
                'quality_score' => (float)($row['quality_score'] ?? 0),
                'preview' => substr($row['search_content'] ?? '', 0, 200),
                'concepts' => $row['concepts'] ?? '',
                'patterns' => $row['patterns'] ?? ''
            ];
        }
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'query' => $query,
                        'total_results' => count($formatted),
                        'results' => $formatted
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Get file context
     */
    private function getFileContext(array $args): array
    {
        $filePath = $args['file_path'] ?? '';
        $includeContent = $args['include_content'] ?? true;
        $includeRelated = $args['include_related'] ?? true;
        
        if (empty($filePath)) {
            throw new Exception("file_path parameter is required", -32602);
        }
        
        // Get file details
        $stmt = $this->pdo->prepare("
            SELECT 
                f.*,
                s.search_content,
                q.quality_score,
                q.issues_count,
                o.category,
                o.business_context,
                o.complexity_score,
                c.concepts,
                c.patterns,
                c.insights
            FROM kb_files f
            LEFT JOIN kb_search_index s ON f.id = s.file_id
            LEFT JOIN simple_quality q ON f.id = q.file_id
            LEFT JOIN kb_organization o ON f.id = o.file_id
            LEFT JOIN kb_quality c ON f.id = c.file_id
            WHERE f.file_path LIKE :path
            LIMIT 1
        ");
        $stmt->execute([':path' => "%{$filePath}%"]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            throw new Exception("File not found: $filePath", -32602);
        }
        
        $context = [
            'file_path' => $file['file_path'],
            'file_name' => $file['file_name'],
            'file_type' => $file['file_type'],
            'file_size' => (int)$file['file_size'],
            'category' => $file['category'] ?? 'Uncategorized',
            'business_context' => $file['business_context'] ?? '',
            'quality_score' => (float)($file['quality_score'] ?? 0),
            'complexity_score' => (float)($file['complexity_score'] ?? 0),
            'issues_count' => (int)($file['issues_count'] ?? 0),
            'concepts' => $file['concepts'] ?? '',
            'patterns' => $file['patterns'] ?? '',
            'insights' => $file['insights'] ?? ''
        ];
        
        if ($includeContent) {
            $context['content_preview'] = substr($file['search_content'] ?? '', 0, 1000);
        }
        
        if ($includeRelated) {
            // Find related files in same category
            $stmt = $this->pdo->prepare("
                SELECT f.file_path, f.file_name, o.category, q.quality_score
                FROM kb_files f
                JOIN kb_organization o ON f.id = o.file_id
                LEFT JOIN simple_quality q ON f.id = q.file_id
                WHERE o.category = :category
                AND f.file_path != :current_path
                ORDER BY q.quality_score DESC
                LIMIT 5
            ");
            $stmt->execute([
                ':category' => $file['category'] ?? '',
                ':current_path' => $file['file_path']
            ]);
            $context['related_files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($context, JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Find code patterns
     */
    private function findPatterns(array $args): array
    {
        $description = $args['description'] ?? '';
        $patternType = $args['pattern_type'] ?? null;
        
        if (empty($description)) {
            throw new Exception("description parameter is required", -32602);
        }
        
        // Search for patterns in cognitive analysis
        $sql = "
            SELECT 
                f.file_path,
                f.file_name,
                c.patterns,
                c.concepts,
                s.search_content
            FROM kb_quality c
            JOIN kb_files f ON c.file_id = f.id
            LEFT JOIN kb_search_index s ON f.id = s.file_id
            WHERE (
                c.patterns LIKE :desc
                OR c.concepts LIKE :desc
                OR s.search_content LIKE :desc
            )
        ";
        
        $params = [':desc' => "%{$description}%"];
        
        if ($patternType) {
            $sql .= " AND c.patterns LIKE :type";
            $params[':type'] = "%{$patternType}%";
        }
        
        $sql .= " LIMIT 20";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'pattern_search' => $description,
                        'total_matches' => count($results),
                        'patterns_found' => $results
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Analyze code quality
     */
    private function analyzeQuality(array $args): array
    {
        $filePath = $args['file_path'] ?? null;
        $code = $args['code'] ?? null;
        
        if (!$filePath && !$code) {
            throw new Exception("Either file_path or code parameter is required", -32602);
        }
        
        if ($filePath) {
            $stmt = $this->pdo->prepare("
                SELECT q.*, f.file_name, f.file_type
                FROM simple_quality q
                JOIN kb_files f ON q.file_id = f.id
                WHERE f.file_path LIKE :path
                LIMIT 1
            ");
            $stmt->execute([':path' => "%{$filePath}%"]);
            $quality = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$quality) {
                throw new Exception("Quality data not found for file: $filePath", -32602);
            }
            
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($quality, JSON_PRETTY_PRINT)
                    ]
                ]
            ];
        } else {
            // Analyze provided code snippet
            $analysis = [
                'code_length' => strlen($code),
                'line_count' => substr_count($code, "\n") + 1,
                'estimated_quality' => 'unknown',
                'suggestions' => [
                    'Consider adding inline documentation',
                    'Ensure proper error handling',
                    'Follow PSR-12 coding standards'
                ]
            ];
            
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($analysis, JSON_PRETTY_PRINT)
                    ]
                ]
            ];
        }
    }
    
    /**
     * Get system architecture
     */
    private function getArchitecture(array $args): array
    {
        $component = $args['component'] ?? 'all';
        
        $architecture = [];
        
        if ($component === 'modules' || $component === 'all') {
            // Get category distribution
            $stmt = $this->pdo->query("
                SELECT category, COUNT(*) as file_count
                FROM kb_organization
                WHERE category IS NOT NULL
                GROUP BY category
                ORDER BY file_count DESC
            ");
            $architecture['modules'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($component === 'database' || $component === 'all') {
            // Get file types that might indicate database usage
            $stmt = $this->pdo->query("
                SELECT file_type, COUNT(*) as count
                FROM kb_files
                WHERE file_type IN ('sql', 'php')
                GROUP BY file_type
            ");
            $architecture['database_files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($component === 'api' || $component === 'all') {
            // Search for API-related files
            $stmt = $this->pdo->query("
                SELECT f.file_path, f.file_name
                FROM kb_files f
                WHERE f.file_path LIKE '%api%'
                OR f.file_path LIKE '%endpoint%'
                OR f.file_name LIKE '%api%'
                LIMIT 20
            ");
            $architecture['api_files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $architecture['total_files'] = $this->pdo->query("SELECT COUNT(*) FROM kb_files")->fetchColumn();
        $architecture['total_categories'] = $this->pdo->query("SELECT COUNT(DISTINCT category) FROM kb_organization WHERE category IS NOT NULL")->fetchColumn();
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($architecture, JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * List available resources
     */
    private function listResources(): array
    {
        return [
            'resources' => [
                [
                    'uri' => 'codebase://architecture',
                    'name' => 'System Architecture',
                    'description' => 'Complete system architecture including modules, database schema, and integration points',
                    'mimeType' => 'application/json'
                ],
                [
                    'uri' => 'codebase://standards',
                    'name' => 'Coding Standards',
                    'description' => 'Project coding standards and best practices',
                    'mimeType' => 'text/markdown'
                ],
                [
                    'uri' => 'codebase://patterns',
                    'name' => 'Common Patterns',
                    'description' => 'Common code patterns used across the codebase',
                    'mimeType' => 'application/json'
                ]
            ]
        ];
    }
    
    /**
     * Read a resource
     */
    private function readResource(array $params): array
    {
        $uri = $params['uri'] ?? '';
        
        switch ($uri) {
            case 'codebase://architecture':
                return $this->getArchitecture(['component' => 'all']);
                
            case 'codebase://standards':
                $standards = "# CIS Coding Standards\n\n";
                $standards .= "## PHP Standards\n";
                $standards .= "- Follow PSR-12 coding style\n";
                $standards .= "- Use strict type declarations\n";
                $standards .= "- Include comprehensive docblocks\n\n";
                $standards .= "## Database Standards\n";
                $standards .= "- Use prepared statements\n";
                $standards .= "- Follow naming conventions (ecig_ prefix)\n";
                $standards .= "- Include proper error handling\n";
                
                return [
                    'contents' => [
                        [
                            'uri' => $uri,
                            'mimeType' => 'text/markdown',
                            'text' => $standards
                        ]
                    ]
                ];
                
            case 'codebase://patterns':
                return $this->findPatterns(['description' => 'common patterns']);
                
            default:
                throw new Exception("Unknown resource: $uri", -32602);
        }
    }
    
    /**
     * List available prompts
     */
    private function listPrompts(): array
    {
        return [
            'prompts' => [
                [
                    'name' => 'context_aware_code',
                    'description' => 'Generate code that matches existing patterns in this codebase',
                    'arguments' => [
                        [
                            'name' => 'task',
                            'description' => 'What you want to implement',
                            'required' => true
                        ],
                        [
                            'name' => 'file_type',
                            'description' => 'Type of file (controller, model, view, etc.)',
                            'required' => false
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get a prompt
     */
    private function getPrompt(array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];
        
        if ($name === 'context_aware_code') {
            $task = $arguments['task'] ?? '';
            $fileType = $arguments['file_type'] ?? 'general';
            
            $prompt = "You are an expert developer working on the CIS (Central Information System) for Ecigdis Limited / The Vape Shed.\n\n";
            $prompt .= "BUSINESS CONTEXT:\n";
            $prompt .= "- 17 retail locations across New Zealand\n";
            $prompt .= "- Vape equipment retail and inventory management\n";
            $prompt .= "- Integration with Vend POS and Xero accounting\n";
            $prompt .= "- PHP/MySQL stack on CloudWays infrastructure\n\n";
            $prompt .= "TASK: {$task}\n\n";
            $prompt .= "Generate code that:\n";
            $prompt .= "1. Matches existing patterns in this codebase\n";
            $prompt .= "2. Follows PSR-12 coding standards\n";
            $prompt .= "3. Includes proper error handling\n";
            $prompt .= "4. Uses ecig_ prefix for database tables\n";
            $prompt .= "5. Includes comprehensive documentation\n";
            
            return [
                'description' => 'Context-aware code generation for CIS system',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            'type' => 'text',
                            'text' => $prompt
                        ]
                    ]
                ]
            ];
        }
        
        throw new Exception("Unknown prompt: $name", -32602);
    }
}

// Handle request
$server = new MCPServer($pdo);
$response = $server->handleRequest($request);

echo json_encode($response);
?>