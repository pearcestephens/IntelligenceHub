<?php

declare(strict_types=1);

namespace App;

use App\Tools\ToolRegistry;
use App\Tools\ToolExecutor;
use App\Memory\ContextCards;
use App\Memory\Summarizer;
use App\Memory\Embeddings;
use App\Memory\KnowledgeBase;
use App\Util\RateLimit;
use App\Util\Validate;
use App\Util\Errors;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * Agent - Main AI Agent orchestrator bringing together all systems
 *
 * Provides complete AI agent functionality with:
 * - Conversation management and message processing
 * - Tool orchestration and execution
 * - Memory and knowledge base integration
 * - Rate limiting and safety controls
 * - Real-time progress tracking via SSE
 * - Production-grade error handling and logging
 * - Health monitoring and metrics
 *
 * This is the main entry point for all AI agent operations.
 *
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */
class Agent
{
    private ?DB $db = null;
    private ?RedisClient $redis = null;
    private ?OpenAI $openai = null;
    private ?Claude $claude = null;
    private Logger $logger;
    private Config $config;
    private ?SSE $sse = null;

    private ?ConversationManager $conversationManager = null;
    private ?MessageHandler $messageHandler = null;
    private ?ToolRegistry $toolRegistry = null;
    private ?ToolExecutor $toolExecutor = null;
    private ?ContextCards $contextCards = null;
    private ?Summarizer $summarizer = null;
    private ?Embeddings $embeddings = null;
    private ?KnowledgeBase $knowledgeBase = null;
    // Rate limiting handled via static utility methods

    /** Agent configuration */
    private array $agentConfig;

    /** Performance metrics */
    private array $metrics = [];

    /** Agent status */
    private bool $initialized = false;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

        $this->agentConfig = [
            'name' => $config->get('AGENT_NAME', 'AI Assistant'),
            'version' => '1.0.0',
            'model' => $config->get('DEFAULT_MODEL', 'gpt-4-turbo-preview'),
            'max_conversations' => (int)$config->get('MAX_CONVERSATIONS', 1000),
            'rate_limit_rpm' => (int)$config->get('RATE_LIMIT_RPM', 60),
            'enable_tools' => $config->get('ENABLE_TOOLS', 'true') === 'true',
            'enable_memory' => $config->get('ENABLE_MEMORY', 'true') === 'true',
            'enable_knowledge' => $config->get('ENABLE_KNOWLEDGE', 'true') === 'true'
        ];
    }

    /**
     * Initialize the agent with all required components
     */
    public function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            $this->logger->info('Initializing AI Agent', [
                'config' => $this->agentConfig
            ]);

            // Initialize core infrastructure
            $this->db = new DB($this->config, $this->logger);
            $this->redis = new RedisClient($this->config, $this->logger);
            $this->openai = new OpenAI($this->config, $this->logger);

            // Initialize Claude AI if configured
            if ($this->config->get('ANTHROPIC_API_KEY') && $this->config->get('ANTHROPIC_API_KEY') !== 'YOUR_CLAUDE_API_KEY_HERE') {
                $this->claude = new Claude($this->config, $this->logger);
            }

            $this->sse = new SSE($this->logger);

            // Rate limiting uses static utility; no instance initialization needed

            // Initialize memory system
            if ($this->agentConfig['enable_memory']) {
                $this->embeddings = new Embeddings($this->openai, $this->redis, $this->logger);
                $this->summarizer = new Summarizer($this->openai, $this->logger);
                $this->contextCards = new ContextCards($this->db, $this->logger);
            }

            // Initialize knowledge base
            if ($this->agentConfig['enable_knowledge']) {
                $this->knowledgeBase = new KnowledgeBase(
                    $this->db,
                    $this->redis,
                    $this->embeddings ?? new Embeddings($this->openai, $this->redis, $this->logger),
                    $this->logger
                );
            }

            // Initialize conversation management FIRST (needed by tools)
            $this->conversationManager = new ConversationManager(
                $this->db,
                $this->logger,
                $this->config,
                $this->redis
            );

            // Initialize tool system
            if ($this->agentConfig['enable_tools']) {
                $this->toolRegistry = new ToolRegistry($this->logger);
                $this->toolExecutor = new ToolExecutor(
                    null, // context array
                    $this->sse
                );

                // Register all available tools
                $this->registerTools();
            }

            // Initialize message handler
            $this->messageHandler = new MessageHandler(
                $this->openai,
                $this->conversationManager,
                $this->toolRegistry ?? new ToolRegistry($this->logger),
                $this->toolExecutor ?? new ToolExecutor(null, $this->sse),
                $this->contextCards ?? new ContextCards($this->db, $this->logger),
                $this->summarizer ?? new Summarizer($this->openai, $this->logger),
                $this->logger,
                $this->config,
                $this->sse
            );

            $this->initialized = true;

            $this->logger->info('AI Agent initialized successfully', [
                'agent_name' => $this->agentConfig['name'],
                'version' => $this->agentConfig['version'],
                'tools_enabled' => $this->agentConfig['enable_tools'],
                'memory_enabled' => $this->agentConfig['enable_memory'],
                'knowledge_enabled' => $this->agentConfig['enable_knowledge']
            ]);
        } catch (Exception $e) {
            $this->logger->error('Failed to initialize AI Agent', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw Errors::initializationError('Agent initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Process a chat message and return response
     */
    public function chat(
        string $message,
        string $conversationId = null,
        array $options = []
    ): array {
        $this->ensureInitialized();
        $startTime = microtime(true);

        try {
            // Validate inputs
            $message = Validate::string($message, 'message', 1, 100000);
            $options = Validate::array($options, 'options');

            // Apply rate limiting (per-minute)
            $clientId = $options['client_id'] ?? 'anonymous';
            $windowMs = 60000; // 1 minute window
            $maxRequests = (int)($this->agentConfig['rate_limit_rpm'] ?? 60);
            if (!(($options['skip_rate_limit'] ?? false) === true) && !RateLimit::check($clientId, $windowMs, $maxRequests)) {
                throw Errors::rateLimitError('Rate limit exceeded. Please try again later.');
            }

            // Create or validate conversation
            if ($conversationId === null) {
                $conversationId = $this->conversationManager->createConversation(
                    $options['title'] ?? 'Chat Conversation'
                );
            } else {
                $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);

                // Verify conversation exists
                $conversation = $this->conversationManager->getConversation($conversationId);
                if (!$conversation) {
                    throw Errors::validationError('Conversation not found: ' . $conversationId);
                }
            }

            // Enrich logs with conversation context
            Logger::setContext(['conversation_id' => $conversationId, 'client_id' => $clientId]);

            $this->logger->info('Processing chat message', [
                'conversation_id' => $conversationId,
                'message_length' => strlen($message),
                'client_id' => $clientId
            ]);

            // Process message
            $result = $this->messageHandler->processMessage($conversationId, $message, [
                'model' => $options['model'] ?? $this->agentConfig['model'],
                'stream' => $options['stream'] ?? false,
                'tools' => $this->agentConfig['enable_tools'] && ($options['enable_tools'] ?? true)
            ]);

            // Update metrics
            $processingTime = microtime(true) - $startTime;
            $this->updateMetrics('chat', $processingTime, true);

            return [
                'success' => true,
                'conversation_id' => $conversationId,
                'response' => $result['response'],
                'processing_time' => $processingTime,
                'agent_info' => [
                    'name' => $this->agentConfig['name'],
                    'version' => $this->agentConfig['version']
                ]
            ];
        } catch (Exception $e) {
            $processingTime = microtime(true) - $startTime;
            $this->updateMetrics('chat', $processingTime, false);

            $this->logger->error('Chat processing failed', [
                'message' => substr($message, 0, 100) . '...',
                'conversation_id' => $conversationId ?? null,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Create a new conversation
     */
    public function createConversation(string $title = null, $metadata = []): array
    {
        $this->ensureInitialized();

        try {
            // Back-compat: allow string second argument as a simple type label
            if (is_string($metadata)) {
                $metadata = ['type' => $metadata];
            } elseif (!is_array($metadata)) {
                $metadata = [];
            }

            $conversationId = $this->conversationManager->createConversation(
                $title ?? 'New Conversation',
                $metadata
            );

            $conversation = $this->conversationManager->getConversation($conversationId);

            return [
                'success' => true,
                'conversation_id' => $conversationId,
                'conversation' => $conversation
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to create conversation', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get conversation details
     */
    public function getConversation(string $conversationId): array
    {
        $this->ensureInitialized();

        try {
            $conversation = $this->conversationManager->getConversation($conversationId);

            if (!$conversation) {
                throw Errors::validationError('Conversation not found: ' . $conversationId);
            }

            return [
                'success' => true,
                'conversation' => $conversation
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * List conversations with pagination
     */
    public function listConversations(int $limit = 20, int $offset = 0): array
    {
        $this->ensureInitialized();

        try {
            $conversations = $this->conversationManager->listConversations($limit, $offset);

            return [
                'success' => true,
                'conversations' => $conversations,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'count' => count($conversations)
                ]
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to list conversations', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation(string $conversationId): array
    {
        $this->ensureInitialized();

        try {
            $success = $this->conversationManager->deleteConversation($conversationId);

            return [
                'success' => $success
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to delete conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Add document to knowledge base
     */
    public function addDocument(string $title, string $content, array $metadata = []): array
    {
        $this->ensureInitialized();

        if (!$this->agentConfig['enable_knowledge']) {
            throw Errors::configurationError('Knowledge base is disabled');
        }

        try {
            // KnowledgeBase::addDocument(string $title, string $content, string $type = 'document', ?array $metadata = null, ?string $source = null)
            $documentId = $this->knowledgeBase->addDocument($title, $content, 'document', $metadata, null);

            return [
                'success' => true,
                'document_id' => $documentId
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to add document', [
                'title' => $title,
                'content_length' => strlen($content),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Search knowledge base
     */
    public function searchKnowledge(string $query, int $limit = 10): array
    {
        $this->ensureInitialized();

        if (!$this->agentConfig['enable_knowledge']) {
            throw Errors::configurationError('Knowledge base is disabled');
        }

        try {
            $results = $this->knowledgeBase->search($query, $limit);

            return [
                'success' => true,
                'results' => $results
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to search knowledge', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get agent health status
     */
    public function getHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => (new DateTime())->format('c'),
            'initialized' => $this->initialized,
            'config' => $this->agentConfig,
            'metrics' => $this->metrics,
            'components' => []
        ];

        if (!$this->initialized) {
            $health['status'] = 'not_initialized';
            return $health;
        }

        try {
            // Check database
            $health['components']['database'] = [
                'status' => $this->db->checkConnection() ? 'healthy' : 'unhealthy',
                'connection_active' => $this->db->checkConnection()
            ];

            // Check Redis
            $health['components']['redis'] = [
                'status' => $this->redis->ping() ? 'healthy' : 'unhealthy',
                'connected' => $this->redis->ping()
            ];

            // Check OpenAI API
            $health['components']['openai'] = [
                'status' => 'unknown', // Would need a health check endpoint
                'api_key_configured' => !empty($this->config->get('OPENAI_API_KEY'))
            ];

            // Determine overall status
            $componentStatuses = array_column($health['components'], 'status');
            if (in_array('unhealthy', $componentStatuses)) {
                $health['status'] = 'degraded';
            }
        } catch (Exception $e) {
            $health['status'] = 'unhealthy';
            $health['error'] = $e->getMessage();
        }

        return $health;
    }

    /**
     * Get agent metrics and statistics
     */
    public function getMetrics(): array
    {
        return [
            'agent_metrics' => $this->metrics,
            'processing_stats' => $this->messageHandler?->getProcessingStats() ?? [],
            'tool_stats' => $this->toolRegistry?->getStatistics() ?? [],
            'conversation_stats' => $this->conversationManager ? [
                'total_conversations' => count($this->conversationManager->listConversations(1000)),
            ] : []
        ];
    }

    /**
     * Register all available tools
     */
    private function registerTools(): void
    {
        if (!$this->toolRegistry || !$this->toolExecutor) {
            return;
        }

        // The ToolRegistry already has all the core tools registered as array definitions
        // We just need to verify they're available
        $availableTools = $this->toolRegistry::getAll();

        $this->logger->info('Registered tools', [
            'tool_count' => count($availableTools),
            'tools' => array_keys($availableTools)
        ]);
    }

    /**
     * Get the knowledge base instance
     */
    public function getKnowledgeBase(): ?\App\Memory\KnowledgeBase
    {
        return $this->knowledgeBase ?? null;
    }

    /**
     * Get the conversation manager instance
     */
    public function getConversationManager(): ?\App\ConversationManager
    {
        return $this->conversationManager ?? null;
    }

    /**
     * Back-compat: expose available tool definitions for tests
     */
    public function getAvailableTools(): array
    {
        // Ensure registry is initialized even if agent not fully initialized
        ToolRegistry::initialize();
        $all = ToolRegistry::getAll();
        // Provide legacy-friendly aliases
        $aliases = [
            'database' => 'database_tool',
            'memory' => 'memory_tool',
            'file' => 'code_tool',
            'http' => 'http_tool',
        ];

        $mapped = [];
        foreach ($aliases as $legacy => $current) {
            if (isset($all[$current])) {
                $mapped[$legacy] = $all[$current];
            }
        }

        // Include any other tools under their current names to not hide extras
        foreach ($all as $name => $def) {
            if (!in_array($name, $aliases, true)) {
                $mapped[$name] = $def;
            }
        }

        return $mapped;
    }

    /**
     * Back-compat: create conversation returning ID when older signature used
     */
    public function createConversationId(string $title = 'New Conversation', array $metadata = []): string
    {
        $result = $this->createConversation($title, $metadata);
        return $result['conversation_id'] ?? '';
    }

    /**
     * Update performance metrics
     */
    private function updateMetrics(string $operation, float $duration, bool $success): void
    {
        if (!isset($this->metrics[$operation])) {
            $this->metrics[$operation] = [
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'total_duration' => 0.0,
                'average_duration' => 0.0,
                'min_duration' => PHP_FLOAT_MAX,
                'max_duration' => 0.0
            ];
        }

        $metric = &$this->metrics[$operation];
        $metric['total_requests']++;

        if ($success) {
            $metric['successful_requests']++;
        } else {
            $metric['failed_requests']++;
        }

        $metric['total_duration'] += $duration;
        $metric['average_duration'] = $metric['total_duration'] / $metric['total_requests'];
        $metric['min_duration'] = min($metric['min_duration'], $duration);
        $metric['max_duration'] = max($metric['max_duration'], $duration);
    }

    /**
     * Ensure agent is initialized before operations
     */
    private function ensureInitialized(): void
    {
        if (!$this->initialized) {
            throw Errors::initializationError('Agent not initialized. Call initialize() first.');
        }
    }

    /**
     * Get agent configuration
     */
    public function getConfig(): array
    {
        return $this->agentConfig;
    }

    /**
     * Start SSE stream for real-time updates
     */
    public function startSSEStream(string $conversationId): void
    {
        $this->ensureInitialized();
        $this->sse->start($conversationId);
    }

    // =========================================================================
    // MULTI-DOMAIN METHODS
    // =========================================================================

    /**
     * Switch conversation to a specific domain
     *
     * @param string $conversationId Conversation UUID
     * @param string $domainName Domain name (global, staff, web, gpt, wiki, superadmin)
     * @return bool Success
     */
    public function switchDomain(string $conversationId, string $domainName): bool
    {
        $this->ensureInitialized();

        $domainId = \App\Memory\MultiDomain::getDomainIdByName($domainName);
        if ($domainId === null) {
            $this->logger->error('Invalid domain name', [
                'domain_name' => $domainName,
                'conversation_id' => $conversationId
            ]);
            return false;
        }

        return \App\Memory\MultiDomain::switchDomain($conversationId, $domainId);
    }

    /**
     * Enable GOD MODE for conversation (access ALL documents)
     *
     * @param string $conversationId Conversation UUID
     * @return bool Success
     */
    public function enableGodMode(string $conversationId): bool
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::enableGodMode($conversationId);
    }

    /**
     * Disable GOD MODE for conversation
     *
     * @param string $conversationId Conversation UUID
     * @return bool Success
     */
    public function disableGodMode(string $conversationId): bool
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::disableGodMode($conversationId);
    }

    /**
     * Get current domain for conversation
     *
     * @param string $conversationId Conversation UUID
     * @return array|null Domain info
     */
    public function getCurrentDomain(string $conversationId): ?array
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::getCurrentDomain($conversationId);
    }

    /**
     * Search knowledge base with domain awareness
     *
     * @param string $conversationId Conversation UUID
     * @param string $query Search query
     * @param int $limit Max results
     * @return array Search results filtered by domain
     */
    public function domainAwareSearch(
        string $conversationId,
        string $query,
        int $limit = 5
    ): array {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::domainAwareSearch($conversationId, $query, $limit);
    }

    /**
     * Get domain statistics
     *
     * @param int|null $domainId Specific domain or all domains
     * @return array Domain statistics
     */
    public function getDomainStats(?int $domainId = null): array
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::getDomainStats($domainId);
    }

    /**
     * Get GOD MODE overview
     *
     * @return array GOD MODE statistics
     */
    public function getGodModeOverview(): array
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::getGodModeOverview();
    }

    /**
     * Get all available domains
     *
     * @return array Domains list
     */
    public function getAllDomains(): array
    {
        $this->ensureInitialized();
        return \App\Memory\MultiDomain::getAllDomains();
    }

    /**
     * Add document to specific domain
     *
     * @param string $docId Document UUID
     * @param string $domainName Domain name
     * @param float $relevanceScore Relevance score (0.0 to 1.0)
     * @return bool Success
     */
    public function addDocumentToDomain(
        string $docId,
        string $domainName,
        float $relevanceScore = 1.0
    ): bool {
        $this->ensureInitialized();

        $domainId = \App\Memory\MultiDomain::getDomainIdByName($domainName);
        if ($domainId === null) {
            return false;
        }

        return \App\Memory\MultiDomain::addDocumentToDomain($docId, $domainId, $relevanceScore);
    }

    // =========================================================================
    // END MULTI-DOMAIN METHODS
    // =========================================================================

    /**
     * Clean up resources
     */
    public function shutdown(): void
    {
        if ($this->initialized) {
            $this->logger->info('Shutting down AI Agent');

            // Close connections
            if ($this->db !== null) {
                DB::disconnect();
            }

            if ($this->redis !== null) {
                RedisClient::disconnect();
            }

            $this->initialized = false;
        }
    }

    /**
     * Destructor - ensure cleanup
     */
    public function __destruct()
    {
        $this->shutdown();
    }
}
