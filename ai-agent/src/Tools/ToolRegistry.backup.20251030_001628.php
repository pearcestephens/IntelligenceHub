<?php

/**
 * Tool Registry for managing AI agent tools and capabilities
 * Handles tool registration, validation, and execution coordination
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\Logger;
use App\Config;

class ToolRegistry
{
    private static array $tools = [];
    private static array $categories = [];
    private static bool $initialized = false;

    /**
     * Optional instance constructor to align with code that injects ToolRegistry
     * Ensures static registry is initialized when an instance is created.
     */
    /**
     * @param \App\Logger|null $logger Unused; maintained for DI compatibility
     */
    public function __construct(?\App\Logger $logger = null)
    {
        // Initialize only once; safe to call repeatedly
        if (!self::$initialized) {
            self::initialize();
        }

        // Touch logger for side-effect (avoid unused param warnings)
        if ($logger) {
            Logger::debug('ToolRegistry instance created');
        }
    }

    /**
     * Initialize the tool registry with core tools
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        try {
            // Register core tools
            self::registerCoreTools();

            // Load custom tools from config if available
            self::loadCustomTools();

            self::$initialized = true;

            Logger::info('Tool registry initialized', [
                'total_tools' => count(self::$tools),
                'categories' => array_keys(self::$categories)
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to initialize tool registry', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Register core tools available to the AI agent
     */
    private static function registerCoreTools(): void
    {
        // Code tool for file operations and programming tasks
        self::register('code_tool', [
            'class' => CodeTool::class,
            'description' => 'Execute code operations like reading files, writing files, and code analysis',
            'category' => 'development',
            'enabled' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => ['read', 'write', 'analyze', 'format'],
                        'description' => 'The action to perform'
                    ],
                    'path' => [
                        'type' => 'string',
                        'description' => 'File or directory path'
                    ],
                    'content' => [
                        'type' => 'string',
                        'description' => 'Content for write operations'
                    ]
                ],
                'required' => ['action', 'path']
            ]
        ]);

        // Database tool for data operations
        self::register('database_tool', [
            'class' => DatabaseTool::class,
            'description' => 'Safe DB ops: read-only query, schema, stats, status, explain',
            'category' => 'data',
            'enabled' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => ['query','explain','schema','stats','tables','table_info','status'],
                        'description' => 'Operation to perform (default: query if query provided, else status)'
                    ],
                    'query' => [
                        'type' => 'string',
                        'description' => 'SQL for query/explain (read-only)'
                    ],
                    'params' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Bound parameters for query/explain'
                    ],
                    'table' => [
                        'type' => 'string',
                        'description' => 'Table name for table_info/schema'
                    ]
                ],
                'required' => []
            ]
        ]);

        // HTTP tool for web requests
        self::register('http_tool', [
            'class' => HttpTool::class,
            'description' => 'Make HTTP requests to external APIs',
            'category' => 'network',
            'enabled' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => ['type' => 'string'],
                    'method' => ['type' => 'string', 'enum' => ['GET','POST','PUT','DELETE','PATCH','HEAD','OPTIONS']],
                    'headers' => ['type' => 'object', 'additionalProperties' => ['type' => 'string']],
                    'body' => ['type' => 'string'],
                    'timeout' => ['type' => 'integer'],
                    'follow_redirects' => ['type' => 'boolean'],
                    'max_redirects' => ['type' => 'integer']
                ],
                'required' => ['url']
            ]
        ]);

        // Knowledge tool for information retrieval
        self::register('knowledge_tool', [
            'class' => KnowledgeTool::class,
            'description' => 'Search and retrieve knowledge from the knowledge base',
            'category' => 'knowledge',
            'enabled' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Maximum results to return'
                    ]
                ],
                'required' => ['query']
            ]
        ]);

        // Memory tool for context management
        self::register('memory_tool', [
            'class' => MemoryTool::class,
            'description' => 'Manage conversation memory and context',
            'category' => 'memory',
            'enabled' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => ['store', 'retrieve', 'clear'],
                        'description' => 'Memory action to perform'
                    ],
                    'key' => [
                        'type' => 'string',
                        'description' => 'Memory key'
                    ],
                    'data' => [
                        'type' => 'string',
                        'description' => 'Data to store'
                    ]
                ],
                'required' => ['action', 'key']
            ]
        ]);

        Logger::debug('Core tools registered', [
            'count' => 5,
            'tools' => ['code_tool', 'database_tool', 'http_tool', 'knowledge_tool', 'memory_tool']
        ]);

        // Bot-only Ops tools (exposed for internal agent use only)
        self::register('ready_check', [
            'class' => ReadyCheckTool::class,
            'method' => 'run',
            'description' => 'Run environment readiness checks (bot-only)',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => (object)[],
                'required' => []
            ],
            'safety' => [
                'timeout' => 30,
                'rate_limit' => 5
            ]
        ]);

        self::register('repo_clean', [
            'class' => RepoCleanerTool::class,
            'method' => 'run',
            'description' => 'List/archive/delete redundant files (bot-only)',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'mode' => ['type' => 'string', 'enum' => ['list','archive','delete']],
                    'confirm' => ['type' => 'boolean'],
                    'no_dirs' => ['type' => 'boolean'],
                    'only' => ['type' => ['array','string']],
                    'dry_run' => ['type' => 'boolean']
                ],
                'required' => ['mode']
            ],
            'safety' => [
                'timeout' => 60,
                'rate_limit' => 3
            ]
        ]);

        self::register('ops_maintain', [
            'class' => OpsMaintainTool::class,
            'method' => 'run',
            'description' => 'Autonomous maintenance: list -> archive(dry-run) -> archive -> ready-check',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'only' => ['type' => ['array','string']],
                    'no_dirs' => ['type' => 'boolean'],
                    'dry_run_first' => ['type' => 'boolean']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 180,
                'rate_limit' => 1
            ]
        ]);

        // Diagnostics and Dev Tools (bot-only where appropriate)
        self::register('logs_tool', [
            'class' => LogsTool::class,
            'method' => 'run',
            'description' => 'Tail logs with optional grep filter',
            'category' => 'diagnostics',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'path' => ['type' => 'string'],
                    'max_bytes' => ['type' => 'integer', 'minimum' => 1000, 'maximum' => 1000000],
                    'grep' => ['type' => 'string']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 10
            ]
        ]);

        self::register('grep_tool', [
            'class' => GrepTool::class,
            'method' => 'run',
            'description' => 'Recursive code search with include/exclude filters',
            'category' => 'development',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'pattern' => ['type' => 'string', 'minLength' => 1],
                    'include' => ['type' => ['array','string']],
                    'exclude' => ['type' => ['array','string']],
                    'max_hits' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 2000]
                ],
                'required' => ['pattern']
            ],
            'safety' => [
                'timeout' => 20,
                'rate_limit' => 5
            ]
        ]);

        // File tool (jailed FS operations)
        self::register('file_tool', [
            'class' => FileTool::class,
            'method' => 'run',
            'description' => 'Read, write, list, and inspect files within a jailed FS root',
            'category' => 'development',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['read','write','list','info']],
                    'path' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'encoding' => ['type' => 'string'],
                    'backup' => ['type' => 'boolean'],
                    'recursive' => ['type' => 'boolean'],
                    'show_hidden' => ['type' => 'boolean'],
                    'max_lines' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20000]
                ],
                'required' => ['action','path']
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 20
            ]
        ]);

        // Endpoint probe tool for HTTP health/assertions
        self::register('endpoint_probe', [
            'class' => EndpointProbeTool::class,
            'method' => 'run',
            'description' => 'Probe HTTPS endpoints with simple response assertions',
            'category' => 'diagnostics',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'endpoints' => ['type' => ['array','string']],
                    'assert_body_contains' => ['type' => ['array','string']],
                    'timeout' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 120]
                ],
                'required' => ['endpoints']
            ],
            'safety' => [
                'timeout' => 60,
                'rate_limit' => 5
            ]
        ]);

        self::register('db_explain', [
            'class' => DBExplainTool::class,
            'method' => 'run',
            'description' => 'Explain a SELECT query to view the execution plan',
            'category' => 'data',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'minLength' => 7]
                ],
                'required' => ['query']
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 10
            ]
        ]);

        self::register('redis_tool', [
            'class' => RedisTool::class,
            'method' => 'run',
            'description' => 'Interact with Redis: get/set/delete/exists',
            'category' => 'data',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['get','set','delete','exists']],
                    'key' => ['type' => 'string', 'minLength' => 1],
                    'value' => ['type' => 'string'],
                    'ttl' => ['type' => 'integer', 'minimum' => 0]
                ],
                'required' => ['action','key']
            ],
            'safety' => [
                'timeout' => 10,
                'rate_limit' => 20
            ]
        ]);

        self::register('env_tool', [
            'class' => EnvTool::class,
            'method' => 'run',
            'description' => 'Read resolved config keys with optional masking',
            'category' => 'diagnostics',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'keys' => ['type' => ['array','string']],
                    'mask' => ['type' => 'boolean']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 5,
                'rate_limit' => 20
            ]
        ]);

        // Static analysis tool (phpstan/phpcs)
        self::register('static_analysis', [
            'class' => StaticAnalysisTool::class,
            'method' => 'run',
            'description' => 'Run PHPStan and PHPCS and summarize results',
            'category' => 'development',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'modes' => ['type' => ['array','string']],
                    'paths' => ['type' => ['array','string']],
                    'level' => ['type' => 'string'],
                    'standard' => ['type' => 'string']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 180,
                'rate_limit' => 1
            ]
        ]);

        // Wrappers around ops scripts
        self::register('security_scan', [
            'class' => SecurityScanTool::class,
            'method' => 'run',
            'description' => 'Run security scanner (depends on ops/security-scanner.php)',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'scope' => ['type' => 'string', 'enum' => ['quick','full']],
                    'paths' => ['type' => ['array','string']],
                    'severity_threshold' => ['type' => 'string', 'enum' => ['low','medium','high','critical']]
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 120,
                'rate_limit' => 1
            ]
        ]);

        self::register('performance_test', [
            'class' => PerformanceTestTool::class,
            'method' => 'run',
            'description' => 'Run performance tests (depends on ops/performance-tester.php)',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => ['type' => 'string'],
                    'duration' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 600],
                    'concurrency' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 64],
                    'endpoints' => ['type' => ['array','string']]
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 180,
                'rate_limit' => 1
            ]
        ]);

        self::register('system_doctor', [
            'class' => SystemDoctorTool::class,
            'method' => 'run',
            'description' => 'Run system diagnostics (depends on ops/system-doctor.php)',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'deep' => ['type' => 'boolean'],
                    'fix' => ['type' => 'boolean']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 120,
                'rate_limit' => 2
            ]
        ]);

        self::register('deployment_manager', [
            'class' => DeploymentManagerTool::class,
            'method' => 'run',
            'description' => 'Plan/status/deploy/rollback using ops/deployment-manager.php',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['plan','status','deploy','rollback']],
                    'environment' => ['type' => 'string', 'enum' => ['dev','stage','prod']],
                    'version' => ['type' => 'string']
                ],
                'required' => ['action']
            ],
            'safety' => [
                'timeout' => 180,
                'rate_limit' => 1
            ]
        ]);

        self::register('monitoring', [
            'class' => MonitoringTool::class,
            'method' => 'run',
            'description' => 'Monitoring snapshot/tail via ops/monitoring-dashboard.php',
            'category' => 'ops',
            'enabled' => true,
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'mode' => ['type' => 'string', 'enum' => ['snapshot','tail']],
                    'window_seconds' => ['type' => 'integer', 'minimum' => 5, 'maximum' => 3600]
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 60,
                'rate_limit' => 4
            ]
        ]);

        // ═══════════════════════════════════════════════════════════════
        // 🎯 MULTI-DOMAIN TOOLS - HIGH PRIORITY KNOWLEDGE MANAGEMENT
        // ═══════════════════════════════════════════════════════════════

        // Switch Domain - Change knowledge context for conversation
        self::register('switch_domain', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '🔀 Switch conversation to specific knowledge domain (staff, web, gpt, wiki, superadmin, global). Controls which 342 documents are accessible.',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'switch_domain',
                        'description' => 'Tool name: switch_domain'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => 'Conversation UUID to switch'
                    ],
                    'domain_name' => [
                        'type' => 'string',
                        'enum' => ['staff', 'web', 'gpt', 'wiki', 'superadmin', 'global'],
                        'description' => 'Target domain name'
                    ]
                ],
                'required' => ['conversation_id', 'domain_name']
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 30]
        ]);

        // Enable GOD MODE - Grant access to ALL documents
        self::register('enable_god_mode', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '👁️ Enable GOD MODE - Access ALL 342 documents across all 6 domains at 100% relevance. ADMIN ONLY!',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'enable_god_mode',
                        'description' => 'Tool name: enable_god_mode'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => 'Conversation UUID for GOD MODE'
                    ]
                ],
                'required' => ['conversation_id']
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 10]
        ]);

        // Disable GOD MODE
        self::register('disable_god_mode', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '🔒 Disable GOD MODE - Return to domain-restricted access',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'disable_god_mode',
                        'description' => 'Tool name: disable_god_mode'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => 'Conversation UUID'
                    ]
                ],
                'required' => ['conversation_id']
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 30]
        ]);

        // List All Domains
        self::register('list_domains', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '📋 List all available knowledge domains with document counts and descriptions',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'list_domains',
                        'description' => 'Tool name: list_domains'
                    ]
                ],
                'required' => []
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 60]
        ]);

        // Get Domain Statistics
        self::register('get_domain_stats', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '📊 Get statistics for specific domain or all domains - doc counts, query counts, usage metrics',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'medium',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'get_domain_stats',
                        'description' => 'Tool name: get_domain_stats'
                    ],
                    'domain_id' => [
                        'type' => 'integer',
                        'description' => 'Domain ID (optional, omit for all domains)'
                    ]
                ],
                'required' => []
            ],
            'safety' => ['timeout' => 15, 'rate_limit' => 30]
        ]);

        // Domain-Aware Search
        self::register('domain_search', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '🔍 Perform domain-aware knowledge base search - respects current domain or GOD MODE',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'domain_search',
                        'description' => 'Tool name: domain_search'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => 'Conversation UUID'
                    ],
                    'query' => [
                        'type' => 'string',
                        'description' => 'Search query'
                    ],
                    'limit' => [
                        'type' => 'integer',
                        'description' => 'Max results (default: 10)'
                    ]
                ],
                'required' => ['conversation_id', 'query']
            ],
            'safety' => ['timeout' => 20, 'rate_limit' => 30]
        ]);

        // Get Current Domain
        self::register('get_current_domain', [
            'class' => MultiDomainTools::class,
            'method' => 'execute',
            'description' => '🎯 Get current active domain for conversation (or GOD MODE status)',
            'category' => 'knowledge',
            'enabled' => true,
            'priority' => 'high',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tool' => [
                        'type' => 'string',
                        'const' => 'get_current_domain',
                        'description' => 'Tool name: get_current_domain'
                    ],
                    'conversation_id' => [
                        'type' => 'string',
                        'description' => 'Conversation UUID'
                    ]
                ],
                'required' => ['conversation_id']
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 60]
        ]);

        // End of Multi-Domain Tools ═══════════════════════════════════
    }

    /**
     * Register a new tool
     */
    public static function register(string $name, array $definition): void
    {
        self::validateToolDefinition($name, $definition);

        self::$tools[$name] = $definition;

        $category = $definition['category'] ?? 'general';
        if (!isset(self::$categories[$category])) {
            self::$categories[$category] = [];
        }
        self::$categories[$category][] = $name;

        Logger::debug('Tool registered', [
            'name' => $name,
            'category' => $category,
            'class' => $definition['class'] ?? null
        ]);
    }

    /**
     * Get tool definition by name
     */
    public static function get(string $name): ?array
    {
        return self::$tools[$name] ?? null;
    }

    /**
     * Get all registered tools
     */
    public static function getAll(): array
    {
        return self::$tools;
    }

    /**
     * Get tools by category
     */
    public static function getByCategory(string $category): array
    {
        $categoryTools = self::$categories[$category] ?? [];
        $tools = [];

        foreach ($categoryTools as $toolName) {
            $tools[$toolName] = self::$tools[$toolName];
        }

        return $tools;
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return array_keys(self::$categories);
    }

    /**
     * Check if tool exists
     */
    public static function exists(string $name): bool
    {
        return isset(self::$tools[$name]);
    }

    /**
     * Convert union types to OpenAI-compatible format
     */
    private static function convertParametersForOpenAI(array $parameters): array
    {
        if (isset($parameters['properties'])) {
            foreach ($parameters['properties'] as $key => $property) {
                if (isset($property['type']) && is_array($property['type'])) {
                    // Convert union types like ['array','string'] to oneOf format
                    $oneOfSchemas = [];
                    foreach ($property['type'] as $type) {
                        $schema = ['type' => $type];
                        if ($type === 'array') {
                            $schema['items'] = ['type' => 'string']; // Default items type
                        }
                        $oneOfSchemas[] = $schema;
                    }
                    $parameters['properties'][$key] = ['oneOf' => $oneOfSchemas];
                    if (isset($property['description'])) {
                        $parameters['properties'][$key]['description'] = $property['description'];
                    }
                }
            }
        }
        return $parameters;
    }

    /**
     * Get tool schema for OpenAI function calling
     */
    public static function getOpenAISchema(): array
    {
        $functions = [];

        foreach (self::$tools as $name => $tool) {
            if (!($tool['enabled'] ?? true)) {
                continue;
            }
            // Removed internal filtering to expose bot-only tools

            $parameters = $tool['parameters'] ?? [
                'type' => 'object',
                'properties' => [],
                'required' => []
            ];

            // Convert union types to OpenAI-compatible format
            $parameters = self::convertParametersForOpenAI($parameters);

            $functions[] = [
                'type' => 'function',
                'function' => [
                    'name' => $name,
                    'description' => $tool['description'],
                    'parameters' => $parameters
                ]
            ];
        }

        return $functions;
    }

    /**
     * Return self-documenting catalog (array of tool specs)
     */
    public static function getCatalog(bool $includeInternal = true): array
    {
        return ToolCatalog::getSpecs($includeInternal);
    }

    /**
     * Return catalog in YAML format (for human-readable docs)
     */
    public static function getCatalogYaml(bool $includeInternal = true): string
    {
        return ToolCatalog::toYaml(ToolCatalog::getSpecs($includeInternal));
    }

    /**
     * Get tool for execution with safety checks
     */
    public static function getForExecution(string $name): ?array
    {
        $tool = self::get($name);

        if (!$tool) {
            Logger::warning('Tool not found for execution', ['name' => $name]);
            return null;
        }

        if (!($tool['enabled'] ?? true)) {
            Logger::warning('Tool is disabled', ['name' => $name]);
            return null;
        }

        // Check if class exists for class-based tools
        if (isset($tool['class']) && !class_exists($tool['class'])) {
            Logger::error('Tool class not found', [
                'name' => $name,
                'class' => $tool['class']
            ]);
            return null;
        }

        return $tool;
    }

    /**
     * Validate tool parameters against schema
     */
    public static function validateParameters(string $toolName, array $parameters): array
    {
        $tool = self::get($toolName);
        if (!$tool) {
            throw new \InvalidArgumentException("Unknown tool: {$toolName}");
        }

        $schema = $tool['parameters'] ?? [];
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        $validated = [];
        $errors = [];

        // Check required parameters
        foreach ($required as $requiredParam) {
            if (!isset($parameters[$requiredParam])) {
                $errors[] = "Missing required parameter: {$requiredParam}";
            }
        }

        // Validate provided parameters
        foreach ($parameters as $paramName => $paramValue) {
            if (!isset($properties[$paramName])) {
                $errors[] = "Unknown parameter: {$paramName}";
                continue;
            }

            $paramSchema = $properties[$paramName];
            $validationResult = self::validateParameter($paramValue, $paramSchema, $paramName);

            if ($validationResult['valid']) {
                $validated[$paramName] = $validationResult['value'];
            } else {
                $errors[] = $validationResult['error'];
            }
        }

        return [
            'valid' => empty($errors),
            'parameters' => $validated,
            'errors' => $errors
        ];
    }

    /**
     * Get tool execution statistics
     */
    public static function getStats(): array
    {
        $stats = [
            'total_tools' => count(self::$tools),
            'enabled_tools' => 0,
            'categories' => [],
            'by_category' => []
        ];

        foreach (self::$tools as $name => $tool) {
            if ($tool['enabled'] ?? true) {
                $stats['enabled_tools']++;
            }

            $category = $tool['category'] ?? 'general';
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = ['total' => 0, 'enabled' => 0];
            }

            $stats['by_category'][$category]['total']++;
            if ($tool['enabled'] ?? true) {
                $stats['by_category'][$category]['enabled']++;
            }
        }

        $stats['categories'] = array_keys($stats['by_category']);

        return $stats;
    }

    /**
     * Instance wrapper for statistics to match existing call sites
     */
    public function getStatistics(): array
    {
        return self::getStats();
    }

    /**
     * Instance wrapper returning available tool names
     */
    public function getAvailableTools(): array
    {
        return array_keys(self::$tools);
    }



    /**
     * Load custom tools from configuration
     */
    private static function loadCustomTools(): void
    {
        $customToolsFile = Config::get('TOOLS_CONFIG_FILE');

        if (!$customToolsFile || !file_exists($customToolsFile)) {
            return;
        }

        try {
            $customTools = json_decode(file_get_contents($customToolsFile), true);

            if (!is_array($customTools)) {
                Logger::warning('Custom tools file contains invalid JSON');
                return;
            }

            foreach ($customTools as $name => $definition) {
                try {
                    self::register($name, $definition);
                } catch (\Throwable $e) {
                    Logger::error('Failed to register custom tool', [
                        'name' => $name,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Logger::error('Failed to load custom tools', [
                'file' => $customToolsFile,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate tool definition
     */
    private static function validateToolDefinition(string $name, array $definition): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Tool name cannot be empty');
        }

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            throw new \InvalidArgumentException('Tool name must match pattern: ^[a-z][a-z0-9_]*$');
        }

        $required = ['description'];
        foreach ($required as $field) {
            if (!isset($definition[$field])) {
                throw new \InvalidArgumentException("Tool definition missing required field: {$field}");
            }
        }

        if (isset($definition['class']) && !is_string($definition['class'])) {
            throw new \InvalidArgumentException('Tool class must be a string');
        }

        if (isset($definition['parameters']) && !is_array($definition['parameters'])) {
            throw new \InvalidArgumentException('Tool parameters must be an array');
        }
    }

    /**
     * Validate single parameter against schema
     */
    private static function validateParameter($value, array $schema, string $paramName): array
    {
        $type = $schema['type'] ?? 'string';

        // Handle array of types
        if (is_array($type)) {
            $valid = false;
            $lastError = '';

            foreach ($type as $allowedType) {
                $result = self::validateParameter($value, ['type' => $allowedType] + $schema, $paramName);
                if ($result['valid']) {
                    return $result;
                }
                $lastError = $result['error'];
            }

            return ['valid' => false, 'error' => $lastError];
        }

        switch ($type) {
            case 'string':
                if (!is_string($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be a string"];
                }

                if (isset($schema['minLength']) && strlen($value) < $schema['minLength']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at least {$schema['minLength']} characters"];
                }

                if (isset($schema['maxLength']) && strlen($value) > $schema['maxLength']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at most {$schema['maxLength']} characters"];
                }

                if (isset($schema['enum']) && !in_array($value, $schema['enum'])) {
                    return ['valid' => false, 'error' => "{$paramName} must be one of: " . implode(', ', $schema['enum'])];
                }

                break;

            case 'integer':
                if (!is_int($value) && !is_numeric($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be an integer"];
                }

                $value = (int)$value;

                if (isset($schema['minimum']) && $value < $schema['minimum']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at least {$schema['minimum']}"];
                }

                if (isset($schema['maximum']) && $value > $schema['maximum']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at most {$schema['maximum']}"];
                }

                break;

            case 'number':
                if (!is_numeric($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be a number"];
                }

                $value = (float)$value;

                if (isset($schema['minimum']) && $value < $schema['minimum']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at least {$schema['minimum']}"];
                }

                if (isset($schema['maximum']) && $value > $schema['maximum']) {
                    return ['valid' => false, 'error' => "{$paramName} must be at most {$schema['maximum']}"];
                }

                break;

            case 'boolean':
                if (!is_bool($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be a boolean"];
                }
                break;

            case 'array':
                if (!is_array($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be an array"];
                }

                if (isset($schema['minItems']) && count($value) < $schema['minItems']) {
                    return ['valid' => false, 'error' => "{$paramName} must have at least {$schema['minItems']} items"];
                }

                if (isset($schema['maxItems']) && count($value) > $schema['maxItems']) {
                    return ['valid' => false, 'error' => "{$paramName} must have at most {$schema['maxItems']} items"];
                }

                break;

            case 'object':
                if (!is_array($value)) {
                    return ['valid' => false, 'error' => "{$paramName} must be an object"];
                }
                break;

            default:
                return ['valid' => false, 'error' => "Unknown parameter type: {$type}"];
        }

        return ['valid' => true, 'value' => $value];
    }
}
