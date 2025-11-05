<?php
/**
 * AI Orchestrator - Advanced RAG & Multi-Agent Coordination
 *
 * This orchestrator leverages the full AI platform:
 * - kb-organizer.php: Knowledge base scanning and indexing
 * - ai-agent-bridge.php: Multi-agent coordination
 * - Semantic search via embeddings
 * - Context-aware query enhancement
 * - Agent tool execution
 * - Memory management
 *
 * @package CIS\AI\Orchestration
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/MCPToolBridge.php';

class AIOrchestrator {
    private mysqli $db;
    private array $config;
    private array $tools = [];
    private array $conversationMemory = [];
    private MCPToolBridge $mcpBridge;

    // Paths to platform components
    private const KB_ORGANIZER_PATH = '/home/master/applications/jcepnzzkmj/public_html/assets/services/neuro/neuro_/kb-organizer.php';
    private const AI_BRIDGE_PATH = '/home/master/applications/jcepnzzkmj/public_html/assets/services/neuro/neuro_/ai-agent-bridge.php';
    private const TOOLS_PATH = '/home/master/applications/jcepnzzkmj/public_html/assets/services/neuro/neuro_/tools/';

    public function __construct(mysqli $db, array $config = []) {
        $this->db = $db;
        $this->config = array_merge([
            'enable_semantic_search' => true,
            'enable_tool_execution' => true,
            'enable_multi_agent' => false,
            'max_context_items' => 10,
            'similarity_threshold' => 0.7,
            'enable_conversation_memory' => true,
            'max_memory_turns' => 5,
            'enable_mcp_tools' => true // NEW: Enable MCP V3 tools
        ], $config);

        $this->mcpBridge = new MCPToolBridge();
        $this->loadTools();
    }

    /**
     * Enhanced query processing with full platform capabilities
     */
    public function processQuery(
        string $message,
        string $conversationId,
        int $agentId,
        array $options = []
    ): array {
        $startTime = microtime(true);

        // Step 1: Analyze query intent
        $intent = $this->analyzeIntent($message);

        // Step 2: Load conversation memory
        $memory = $this->loadConversationMemory($conversationId);

        // Step 3: Determine if tools are needed
        $toolsNeeded = $this->identifyRequiredTools($message, $intent);

        // Step 4: Execute tools if needed
        $toolResults = [];
        if (!empty($toolsNeeded) && $this->config['enable_tool_execution']) {
            $toolResults = $this->executeTools($toolsNeeded, $message, $conversationId);
        }

        // Step 5: Perform semantic search for relevant knowledge
        $knowledgeContext = $this->semanticSearch($message, $intent, $options);

        // Step 6: Check if kb-organizer needs to refresh
        if ($this->shouldRefreshKnowledge($message)) {
            $this->triggerKnowledgeRefresh();
        }

        // Step 7: Build enhanced context
        $enhancedContext = $this->buildEnhancedContext([
            'message' => $message,
            'intent' => $intent,
            'memory' => $memory,
            'knowledge' => $knowledgeContext,
            'tool_results' => $toolResults,
            'metadata' => $this->gatherMetadata($conversationId, $agentId)
        ]);

        // Step 8: Log orchestration
        $this->logOrchestration($conversationId, $message, $intent, $enhancedContext);

        $processingTime = (int)((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'enhanced_context' => $enhancedContext,
            'intent' => $intent,
            'tools_executed' => array_keys($toolResults),
            'knowledge_items' => count($knowledgeContext),
            'memory_turns' => count($memory),
            'processing_time_ms' => $processingTime
        ];
    }

    /**
     * Analyze query intent using pattern matching and keywords
     */
    private function analyzeIntent(string $message): array {
        $message_lower = strtolower($message);

        $intents = [
            'search_knowledge' => ['find', 'search', 'look for', 'where is', 'show me'],
            'get_data' => ['get', 'fetch', 'retrieve', 'show', 'display', 'list'],
            'update_data' => ['update', 'change', 'modify', 'edit', 'set'],
            'create_data' => ['create', 'add', 'new', 'insert', 'make'],
            'delete_data' => ['delete', 'remove', 'clear', 'drop'],
            'analyze' => ['analyze', 'compare', 'calculate', 'compute', 'stats'],
            'explain' => ['explain', 'what is', 'how does', 'why', 'describe'],
            'help' => ['help', 'assist', 'guide', 'tutorial', 'how to']
        ];

        $detected = [];
        foreach ($intents as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message_lower, $keyword) !== false) {
                    $detected[] = $intent;
                    break;
                }
            }
        }

        // Detect entities (tables, modules, concepts)
        $entities = $this->extractEntities($message);

        return [
            'primary' => $detected[0] ?? 'explain',
            'all' => $detected,
            'entities' => $entities,
            'requires_data' => in_array('get_data', $detected) || in_array('analyze', $detected),
            'requires_tools' => count($entities['tables']) > 0 || in_array('search_knowledge', $detected)
        ];
    }

    /**
     * Extract entities (tables, modules, files, concepts)
     */
    private function extractEntities(string $message): array {
        $entities = [
            'tables' => [],
            'modules' => [],
            'files' => [],
            'concepts' => []
        ];

        // Common CIS tables
        $tables = [
            'stock_transfers', 'stock_transfer_items', 'purchase_orders', 'purchase_order_items',
            'consignments', 'consignment_items', 'vend_products', 'vend_outlets',
            'ai_kb_knowledge_items', 'ai_kb_queries', 'ai_kb_conversations'
        ];

        foreach ($tables as $table) {
            if (stripos($message, $table) !== false || stripos($message, str_replace('_', ' ', $table)) !== false) {
                $entities['tables'][] = $table;
            }
        }

        // Common modules
        $modules = ['consignments', 'transfers', 'purchase orders', 'inventory', 'products'];
        foreach ($modules as $module) {
            if (stripos($message, $module) !== false) {
                $entities['modules'][] = $module;
            }
        }

        return $entities;
    }

    /**
     * Load conversation memory (last N turns)
     */
    private function loadConversationMemory(string $conversationId): array {
        if (!$this->config['enable_conversation_memory']) {
            return [];
        }

        if (isset($this->conversationMemory[$conversationId])) {
            return $this->conversationMemory[$conversationId];
        }

        $stmt = $this->db->prepare(
            "SELECT query_text, response_text, queried_at
             FROM ai_kb_queries
             WHERE conversation_id = ?
             ORDER BY queried_at DESC
             LIMIT ?"
        );

        $limit = $this->config['max_memory_turns'];
        $stmt->bind_param('si', $conversationId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $memory = [];
        while ($row = $result->fetch_assoc()) {
            $memory[] = [
                'query' => $row['query_text'],
                'response' => $row['response_text'],
                'timestamp' => $row['queried_at']
            ];
        }
        $stmt->close();

        $this->conversationMemory[$conversationId] = array_reverse($memory);
        return $this->conversationMemory[$conversationId];
    }

    /**
     * Identify which tools are needed for this query
     */
    private function identifyRequiredTools(string $message, array $intent): array {
        $needed = [];
        $message_lower = strtolower($message);

        // MCP semantic_search - for codebase questions
        if (strpos($message_lower, 'code') !== false ||
            strpos($message_lower, 'file') !== false ||
            strpos($message_lower, 'function') !== false ||
            strpos($message_lower, 'class') !== false ||
            strpos($message_lower, 'search') !== false ||
            strpos($message_lower, 'find') !== false) {
            $needed[] = 'mcp_semantic_search';
        }

        // MCP database query
        if (!empty($intent['entities']['tables']) ||
            strpos($message_lower, 'database') !== false ||
            strpos($message_lower, 'table') !== false ||
            strpos($message_lower, 'sql') !== false ||
            strpos($message_lower, 'query') !== false) {
            $needed[] = 'mcp_database';
        }

        // Knowledge base scanner
        if (strpos($message_lower, 'knowledge base') !== false ||
            strpos($message_lower, 'what do you know') !== false) {
            $needed[] = 'kb_scanner';
        }

        // Data analysis
        if ($intent['primary'] === 'analyze') {
            $needed[] = 'data_analyzer';
        }

        return $needed;
    }

    /**
     * Execute tools and return results
     */
    private function executeTools(array $tools, string $message, string $conversationId): array {
        $results = [];

        foreach ($tools as $toolName) {
            try {
                // Handle MCP tools
                if (strpos($toolName, 'mcp_') === 0 && $this->config['enable_mcp_tools']) {
                    $result = $this->executeMCPTool($toolName, $message);
                    $results[$toolName] = $result;
                    continue;
                }

                // Handle legacy tools
                if (!isset($this->tools[$toolName])) {
                    continue;
                }

                $tool = $this->tools[$toolName];
                $result = $this->executeTool($tool, $message, $conversationId);
                $results[$toolName] = $result;

                // Log tool execution
                $this->logToolExecution($conversationId, $toolName, $result);
            } catch (Exception $e) {
                error_log("[Orchestrator] Tool {$toolName} failed: {$e->getMessage()}");
                $results[$toolName] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Execute MCP tool via bridge
     */
    private function executeMCPTool(string $toolName, string $message): array {
        $keywords = $this->extractKeywords($message);
        $searchQuery = implode(' ', $keywords);

        switch ($toolName) {
            case 'mcp_semantic_search':
                return $this->mcpBridge->semanticSearch($searchQuery, 10);

            case 'mcp_database':
                // For now, just document what was requested
                return [
                    'success' => true,
                    'note' => 'Database query requested but requires specific SQL',
                    'query_intent' => $searchQuery
                ];

            case 'mcp_file_search':
                return $this->mcpBridge->fileSearch($searchQuery, 20);

            case 'mcp_grep':
                return $this->mcpBridge->grepSearch($searchQuery, false);

            default:
                return ['error' => 'Unknown MCP tool: ' . $toolName];
        }
    }

    /**
     * Execute a specific tool
     */
    private function executeTool(array $tool, string $message, string $conversationId): array {
        switch ($tool['name']) {
            case 'kb_scanner':
                return $this->runKBScanner();

            case 'db_query':
                return $this->runDatabaseQuery($message);

            case 'file_reader':
                return $this->readRelevantFiles($message);

            case 'data_analyzer':
                return $this->analyzeData($message);

            default:
                return ['error' => 'Unknown tool'];
        }
    }

    /**
     * Run KB scanner to get fresh knowledge base stats
     */
    private function runKBScanner(): array {
        if (!file_exists(self::KB_ORGANIZER_PATH)) {
            return ['error' => 'KB organizer not found'];
        }

        // Get knowledge base stats from database
        $stmt = $this->db->query(
            "SELECT
                COUNT(*) as total_items,
                COUNT(DISTINCT category) as categories,
                COUNT(DISTINCT source_file) as source_files,
                MAX(last_updated) as last_updated
             FROM ai_kb_knowledge_items"
        );

        $stats = $stmt->fetch_assoc();

        return [
            'success' => true,
            'stats' => $stats,
            'tool' => 'kb_scanner'
        ];
    }

    /**
     * Run database query based on natural language
     */
    private function runDatabaseQuery(string $message): array {
        // Extract table names from message
        $tables = [];
        $knownTables = ['stock_transfers', 'purchase_orders', 'consignments', 'vend_products'];

        foreach ($knownTables as $table) {
            if (stripos($message, $table) !== false || stripos($message, str_replace('_', ' ', $table)) !== false) {
                $tables[] = $table;
            }
        }

        if (empty($tables)) {
            return ['error' => 'No tables identified'];
        }

        $results = [];
        foreach ($tables as $table) {
            // Get basic stats
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM `{$table}` LIMIT 1");
            if ($stmt) {
                $row = $stmt->fetch_assoc();
                $results[$table] = ['count' => $row['count']];
            }
        }

        return [
            'success' => true,
            'tables' => $results,
            'tool' => 'db_query'
        ];
    }

    /**
     * Read relevant files based on query
     */
    private function readRelevantFiles(string $message): array {
        // Search knowledge base for file references
        $stmt = $this->db->prepare(
            "SELECT DISTINCT source_file, category
             FROM ai_kb_knowledge_items
             WHERE item_content LIKE ?
             LIMIT 5"
        );

        $searchTerm = "%{$message}%";
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $files = [];
        while ($row = $result->fetch_assoc()) {
            $files[] = [
                'file' => $row['source_file'],
                'category' => $row['category']
            ];
        }
        $stmt->close();

        return [
            'success' => true,
            'files' => $files,
            'tool' => 'file_reader'
        ];
    }

    /**
     * Analyze data based on query
     */
    private function analyzeData(string $message): array {
        // Get recent query trends
        $stmt = $this->db->query(
            "SELECT
                DATE(queried_at) as date,
                COUNT(*) as queries,
                AVG(response_time_ms) as avg_response_time
             FROM ai_kb_queries
             WHERE queried_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(queried_at)
             ORDER BY date DESC"
        );

        $trends = [];
        while ($row = $stmt->fetch_assoc()) {
            $trends[] = $row;
        }

        return [
            'success' => true,
            'trends' => $trends,
            'tool' => 'data_analyzer'
        ];
    }

    /**
     * Semantic search using embeddings and similarity
     */
    private function semanticSearch(string $message, array $intent, array $options): array {
        if (!$this->config['enable_semantic_search']) {
            return $this->keywordSearch($message);
        }

        // For now, use enhanced keyword search
        // TODO: Implement vector embeddings with OpenAI/Anthropic
        return $this->enhancedKeywordSearch($message, $intent);
    }

    /**
     * Enhanced keyword search with intent awareness
     */
    private function enhancedKeywordSearch(string $message, array $intent): array {
        $keywords = $this->extractKeywords($message);
        $contexts = [];

        // For now, just use keyword search without category filtering
        // This ensures we find relevant items regardless of category
        $contexts = $this->searchByKeywords($keywords);

        return array_slice($contexts, 0, $this->config['max_context_items']);
    }

    /**
     * Extract keywords from message
     */
    private function extractKeywords(string $message): array {
        // Remove common words
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for'];
        $words = explode(' ', strtolower($message));

        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) >= 4 && !in_array($word, $stopWords);
        });

        return array_values($keywords);
    }

    /**
     * Search by category
     */
    private function searchByCategory(array $keywords, array $categories): array {
        $contexts = [];

        foreach ($keywords as $keyword) {
            $categoryList = "'" . implode("','", $categories) . "'";

            $stmt = $this->db->prepare(
                "SELECT item_content, source_file, category, importance_score
                 FROM ai_kb_knowledge_items
                 WHERE (LOWER(item_content) LIKE ? OR LOWER(item_key) LIKE ?)
                   AND category IN ({$categoryList})
                 ORDER BY importance_score DESC, times_referenced DESC
                 LIMIT 3"
            );

            $searchTerm = "%{$keyword}%";
            $stmt->bind_param('ss', $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $contexts[] = $row;
            }
            $stmt->close();
        }

        return $contexts;
    }

    /**
     * Search by keywords (fallback)
     */
    private function searchByKeywords(array $keywords): array {
        $contexts = [];

        foreach ($keywords as $keyword) {
            $stmt = $this->db->prepare(
                "SELECT item_content, source_file, category, importance_score
                 FROM ai_kb_knowledge_items
                 WHERE LOWER(item_content) LIKE ? OR LOWER(item_key) LIKE ?
                 ORDER BY importance_score DESC, times_referenced DESC
                 LIMIT 3"
            );

            $searchTerm = "%{$keyword}%";
            $stmt->bind_param('ss', $searchTerm, $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $contexts[] = $row;
            }
            $stmt->close();
        }

        return $contexts;
    }

    /**
     * Simple keyword search (no intent awareness)
     */
    private function keywordSearch(string $message): array {
        $keywords = $this->extractKeywords($message);
        return $this->searchByKeywords($keywords);
    }

    /**
     * Check if knowledge base needs refresh
     */
    private function shouldRefreshKnowledge(string $message): bool {
        // Check if asking about recent changes
        if (strpos($message, 'latest') !== false || strpos($message, 'recent') !== false) {
            return true;
        }

        // Check last sync time (use created_at since last_updated doesn't exist yet)
        $stmt = $this->db->query(
            "SELECT MAX(created_at) as last_sync FROM ai_kb_knowledge_items"
        );

        if (!$stmt) {
            // Table might not exist yet, skip refresh check
            return false;
        }

        $row = $stmt->fetch_assoc();

        if (!$row || !$row['last_sync']) {
            return true;
        }

        // Refresh if older than 1 hour
        $lastSync = strtotime($row['last_sync']);
        return (time() - $lastSync) > 3600;
    }

    /**
     * Trigger knowledge base refresh
     */
    private function triggerKnowledgeRefresh(): void {
        // Log that refresh is needed
        error_log("[Orchestrator] Knowledge base refresh triggered");

        // In production, this would trigger kb-organizer.php via background job
        // For now, just log it
    }

    /**
     * Build enhanced context for AI
     */
    private function buildEnhancedContext(array $data): array {
        $context = [
            'system_prompt' => $this->buildSystemPrompt($data['intent']),
            'knowledge_context' => $this->formatKnowledgeContext($data['knowledge']),
            'conversation_history' => $this->formatMemory($data['memory']),
            'tool_results' => $this->formatToolResults($data['tool_results']),
            'metadata' => $data['metadata']
        ];

        return $context;
    }

    /**
     * Build system prompt based on intent
     */
    private function buildSystemPrompt(array $intent): string {
        $base = "You are an expert AI assistant with access to a comprehensive knowledge base. " .
                "Answer questions using ONLY the knowledge base context provided. " .
                "If the information isn't in the knowledge base, say so clearly. " .
                "Never make assumptions or use general knowledge that isn't in the context.";

        if ($intent['primary'] === 'explain') {
            return $base . " Focus on providing clear, educational explanations with examples from the knowledge base.";
        } elseif ($intent['requires_data']) {
            return $base . " Provide data-driven answers with specific numbers and references from the knowledge base.";
        } elseif (in_array('analyze', $intent['all'])) {
            return $base . " Analyze the data critically using the knowledge base and provide insights and recommendations.";
        }

        return $base . " Provide accurate, helpful responses based strictly on the knowledge base provided.";
    }

    /**
     * Format knowledge context for AI
     */
    private function formatKnowledgeContext(array $knowledge): string {
        if (empty($knowledge)) {
            return '';
        }

        $formatted = "# Relevant Knowledge Base Context:\n\n";

        foreach ($knowledge as $item) {
            $formatted .= "**Source:** {$item['source_file']} ({$item['category']})\n";
            $formatted .= "{$item['item_content']}\n\n";
        }

        return $formatted;
    }

    /**
     * Format conversation memory
     */
    private function formatMemory(array $memory): string {
        if (empty($memory)) {
            return '';
        }

        $formatted = "# Previous Conversation:\n\n";

        foreach ($memory as $turn) {
            $formatted .= "**User:** {$turn['query']}\n";
            if ($turn['response']) {
                $formatted .= "**Assistant:** {$turn['response']}\n";
            }
            $formatted .= "\n";
        }

        return $formatted;
    }

    /**
     * Format tool results
     */
    private function formatToolResults(array $results): string {
        if (empty($results)) {
            return '';
        }

        $formatted = "# Tool Execution Results:\n\n";

        foreach ($results as $tool => $result) {
            $formatted .= "**Tool:** {$tool}\n";
            $formatted .= "```json\n" . json_encode($result, JSON_PRETTY_PRINT) . "\n```\n\n";
        }

        return $formatted;
    }

    /**
     * Gather metadata about the conversation
     */
    private function gatherMetadata(string $conversationId, int $agentId): array {
        $stmt = $this->db->prepare(
            "SELECT started_at, total_messages
             FROM ai_kb_conversations
             WHERE conversation_id = ?"
        );
        $stmt->bind_param('s', $conversationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $conv = $result->fetch_assoc();
        $stmt->close();

        return [
            'conversation_id' => $conversationId,
            'agent_id' => $agentId,
            'started_at' => $conv['started_at'] ?? null,
            'total_messages' => $conv['total_messages'] ?? 0,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Log orchestration activity
     */
    private function logOrchestration(string $conversationId, string $message, array $intent, array $context): void {
        // Log to ai_cis_kb_orchestration_log table (if exists)
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ai_cis_kb_orchestration_log
                 (conversation_id, query_text, intent_detected, context_items, tools_used, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );

            if ($stmt) {
                $intentJson = json_encode($intent);
                $contextCount = count($context['knowledge_context'] ?? []);
                $toolsJson = json_encode(array_keys($context['tool_results'] ?? []));

                $stmt->bind_param('sssis', $conversationId, $message, $intentJson, $contextCount, $toolsJson);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("[Orchestrator] Logging failed: {$e->getMessage()}");
        }
    }

    /**
     * Log tool execution
     */
    private function logToolExecution(string $conversationId, string $toolName, array $result): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ai_cis_kb_tool_executions
                 (conversation_id, tool_name, result_json, executed_at)
                 VALUES (?, ?, ?, NOW())"
            );

            if ($stmt) {
                $resultJson = json_encode($result);
                $stmt->bind_param('sss', $conversationId, $toolName, $resultJson);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("[Orchestrator] Tool logging failed: {$e->getMessage()}");
        }
    }

    /**
     * Load available tools
     */
    private function loadTools(): void {
        $this->tools = [
            'kb_scanner' => [
                'name' => 'kb_scanner',
                'description' => 'Scan knowledge base for stats and structure',
                'enabled' => true
            ],
            'db_query' => [
                'name' => 'db_query',
                'description' => 'Query database tables',
                'enabled' => true
            ],
            'file_reader' => [
                'name' => 'file_reader',
                'description' => 'Read relevant files from knowledge base',
                'enabled' => true
            ],
            'data_analyzer' => [
                'name' => 'data_analyzer',
                'description' => 'Analyze trends and patterns in data',
                'enabled' => true
            ]
        ];
    }

    /**
     * Process query with streaming support to prevent summarization
     *
     * @param string $message User query
     * @param array $context Additional context
     * @param callable $streamCallback Function called for each chunk
     * @return array Final result with metadata
     */
    public function processWithStreaming(string $message, array $context, callable $streamCallback): array {
        $startTime = microtime(true);

        // Get conversation ID from context
        $conversationId = $context['conversation_id'] ?? 'mcp-' . uniqid();
        $agentId = $context['agent_id'] ?? 1;

        // Step 1: Process query normally to get enhanced context
        $result = $this->processQuery($message, $conversationId, $agentId);

        if (!$result['success']) {
            return $result;
        }

        // Step 2: Generate response in chunks to stream progressively
        $enhancedContext = $result['enhanced_context'];

        // Simulate chunked response (in real implementation, this would call LLM with streaming)
        // For now, we'll chunk the enhanced context and tool results

        $chunks = $this->chunkResponse($enhancedContext, $result);

        foreach ($chunks as $index => $chunk) {
            $streamCallback([
                'content' => $chunk,
                'tokens' => strlen($chunk) / 4, // Rough token estimate
                'index' => $index
            ]);

            // Small delay to simulate streaming
            usleep(50000); // 50ms
        }

        $processingTime = (int)((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'conversation_id' => $conversationId,
            'chunks_sent' => count($chunks),
            'processing_time' => $processingTime,
            'sources' => $enhancedContext['sources'] ?? [],
            'agent' => 'orchestrator'
        ];
    }

    /**
     * Chunk response into streamable pieces
     * Prevents summarization by sending data progressively
     */
    private function chunkResponse(array $enhancedContext, array $result): array {
        $chunks = [];

        // Chunk 1: Context summary
        if (!empty($enhancedContext['knowledge_context'])) {
            $chunks[] = "📚 **Knowledge Base Context** ({$result['knowledge_items']} items found)\n\n";

            foreach (array_slice($enhancedContext['knowledge_context'], 0, 3) as $item) {
                $chunks[] = "- {$item['title']}\n  File: `{$item['file_path']}`\n  Relevance: " .
                           number_format(($item['similarity'] ?? 0) * 100, 1) . "%\n\n";
            }
        }

        // Chunk 2: Tool results
        if (!empty($result['tools_executed'])) {
            $chunks[] = "\n🔧 **Tools Executed**: " . implode(', ', $result['tools_executed']) . "\n\n";

            foreach ($enhancedContext['tool_results'] as $toolName => $toolResult) {
                if (!empty($toolResult['result'])) {
                    $chunks[] = "**{$toolName}**: " . substr(json_encode($toolResult['result'], JSON_PRETTY_PRINT), 0, 500) . "\n\n";
                }
            }
        }

        // Chunk 3: Memory context
        if (!empty($enhancedContext['memory'])) {
            $chunks[] = "\n💭 **Conversation Memory** ({$result['memory_turns']} previous turns)\n\n";
        }

        // Chunk 4: Metadata
        $chunks[] = "\n📊 **Processing Stats**:\n";
        $chunks[] = "- Intent: {$result['intent']['primary']}\n";
        $chunks[] = "- Processing time: {$result['processing_time']}ms\n";
        $chunks[] = "- Knowledge items: {$result['knowledge_items']}\n";

        return array_filter($chunks);
    }
}
