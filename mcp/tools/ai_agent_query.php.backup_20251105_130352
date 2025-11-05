<?php
/**
 * MCP Tool: AI Agent Query
 *
 * Routes queries through your custom AI Agent platform instead of generic LLM
 *
 * Features:
 * - Uses AIOrchestrator for RAG + context
 * - Conversation memory from database
 * - Tool execution (kb_scanner, db_query, file_reader, data_analyzer)
 * - Enhanced context building
 *
 * @package IntelligenceHub\MCP\Tools
 * @version 1.0.0
 */

declare(strict_types=1);

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

require_once __DIR__ . '/../../ai-agent/lib/AIOrchestrator.php';
require_once __DIR__ . '/../mcp_tools_turbo.php';

/**
 * Query your custom AI Agent with full RAG capabilities
 *
 * @param array $args {
 *     @type string $query The user's question/request
 *     @type string $conversation_id Optional conversation ID for memory
 *     @type int $agent_id Optional agent ID (default: 1)
 *     @type array $options Optional configuration overrides
 * }
 * @return array Response with AI-generated answer and metadata
 */
function ai_agent_query(array $args): array {
    $startTime = microtime(true);

    // Validate inputs
    if (empty($args['query'])) {
        return [
            'success' => false,
            'error' => 'Query is required',
            'code' => 'MISSING_QUERY'
        ];
    }

    $query = trim($args['query']);
    $conversationId = $args['conversation_id'] ?? 'mcp-' . uniqid();
    $agentId = $args['agent_id'] ?? 1;
    $options = $args['options'] ?? [];

    try {
        // Connect to database
        $db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');

        if ($db->connect_error) {
            throw new Exception("Database connection failed: " . $db->connect_error);
        }

        $db->set_charset('utf8mb4');

        // Initialize orchestrator with your configuration
        $orchestrator = new AIOrchestrator($db, [
            'enable_semantic_search' => true,
            'enable_tool_execution' => true,
            'enable_multi_agent' => false,
            'max_context_items' => 15,
            'similarity_threshold' => 0.7,
            'enable_conversation_memory' => true,
            'max_memory_turns' => 10
        ]);

        // Process query through orchestrator
        $result = $orchestrator->processQuery($query, $conversationId, $agentId, $options);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => 'Orchestrator processing failed',
                'details' => $result
            ];
        }

        // Get enhanced context
        $enhancedContext = $result['enhanced_context'];

        // Call your AI provider (OpenAI/Anthropic) with enhanced context
        $aiResponse = callAIProvider($query, $enhancedContext, $options);

        // Save to conversation history
        saveConversation($db, $conversationId, $query, $aiResponse, $result);

        $db->close();

        $totalTime = (int)((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'response' => $aiResponse['content'],
            'conversation_id' => $conversationId,
            'metadata' => [
                'agent_id' => $agentId,
                'intent' => $result['intent']['primary'],
                'tools_executed' => $result['tools_executed'],
                'knowledge_items_used' => $result['knowledge_items'],
                'memory_turns_loaded' => $result['memory_turns'],
                'orchestrator_time_ms' => $result['processing_time_ms'],
                'ai_provider_time_ms' => $aiResponse['processing_time_ms'] ?? 0,
                'total_time_ms' => $totalTime
            ],
            'debug' => [
                'enhanced_context_preview' => substr($enhancedContext['knowledge_context'] ?? '', 0, 200) . '...',
                'system_prompt_used' => $enhancedContext['system_prompt'] ?? ''
            ]
        ];

    } catch (Exception $e) {
        error_log("[MCP Tool: ai_agent_query] Error: " . $e->getMessage());

        return [
            'success' => false,
            'error' => $e->getMessage(),
            'code' => 'EXECUTION_ERROR'
        ];
    }
}

/**
 * Call AI provider (OpenAI or Anthropic) with enhanced context
 */
function callAIProvider(string $query, array $enhancedContext, array $options): array {
    $startTime = microtime(true);

    $provider = $options['ai_provider'] ?? 'openai'; // or 'anthropic'
    $model = $options['model'] ?? 'gpt-5-chat-latest'; // GPT-5 chat latest

    // Build full prompt
    $systemPrompt = $enhancedContext['system_prompt'];

    $contextParts = [];
    if (!empty($enhancedContext['knowledge_context'])) {
        $contextParts[] = $enhancedContext['knowledge_context'];
    }
    if (!empty($enhancedContext['conversation_history'])) {
        $contextParts[] = $enhancedContext['conversation_history'];
    }
    if (!empty($enhancedContext['tool_results'])) {
        $contextParts[] = $enhancedContext['tool_results'];
    }

    $fullContext = implode("\n\n---\n\n", $contextParts);

    $userPrompt = $fullContext . "\n\n**User Query:** " . $query;

    // Get API key from environment
    $apiKey = $_ENV['OPENAI_API_KEY'] ?? $_SERVER['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?? getenv('ANTHROPIC_API_KEY');

    if (!$apiKey) {
        // Fallback: return context-enhanced response without AI call
        return [
            'content' => "Based on the knowledge base:\n\n" . substr($fullContext, 0, 500) . "\n\n[AI provider not configured - showing RAG context only]",
            'processing_time_ms' => (int)((microtime(true) - $startTime) * 1000),
            'provider' => 'none'
        ];
    }

    // Call OpenAI API
    if ($provider === 'openai') {
        $response = callOpenAI($apiKey, $model, $systemPrompt, $userPrompt);
    } else {
        $response = callAnthropic($apiKey, $model, $systemPrompt, $userPrompt);
    }

    $response['processing_time_ms'] = (int)((microtime(true) - $startTime) * 1000);
    $response['provider'] = $provider;

    return $response;
}

/**
 * Call OpenAI API
 */
function callOpenAI(string $apiKey, string $model, string $systemPrompt, string $userPrompt): array {
    $url = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 2000
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['content' => 'AI provider error: HTTP ' . $httpCode];
    }

    $result = json_decode($response, true);

    return [
        'content' => $result['choices'][0]['message']['content'] ?? 'No response',
        'tokens_used' => $result['usage']['total_tokens'] ?? 0
    ];
}

/**
 * Call Anthropic API
 */
function callAnthropic(string $apiKey, string $model, string $systemPrompt, string $userPrompt): array {
    $url = 'https://api.anthropic.com/v1/messages';

    $data = [
        'model' => $model,
        'system' => $systemPrompt,
        'messages' => [
            ['role' => 'user', 'content' => $userPrompt]
        ],
        'max_tokens' => 2000
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['content' => 'AI provider error: HTTP ' . $httpCode];
    }

    $result = json_decode($response, true);

    return [
        'content' => $result['content'][0]['text'] ?? 'No response',
        'tokens_used' => $result['usage']['input_tokens'] + $result['usage']['output_tokens']
    ];
}

/**
 * Save conversation to database
 */
function saveConversation(mysqli $db, string $conversationId, string $query, array $aiResponse, array $orchestratorResult): void {
    try {
        // Check if conversation exists
        $stmt = $db->prepare("SELECT conversation_id FROM ai_conversations WHERE conversation_id = ?");
        $stmt->bind_param('s', $conversationId);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            // Create new conversation
            $stmt = $db->prepare(
                "INSERT INTO ai_conversations (conversation_id, project_id, business_unit_id, conversation_title, started_at, last_message_at, total_messages, status)
                 VALUES (?, 2, 2, ?, NOW(), NOW(), 1, 'active')"
            );
            $title = substr($query, 0, 100);
            $stmt->bind_param('ss', $conversationId, $title);
            $stmt->execute();
            $stmt->close();
        } else {
            // Update existing
            $stmt = $db->prepare(
                "UPDATE ai_conversations
                 SET last_message_at = NOW(), total_messages = total_messages + 1
                 WHERE conversation_id = ?"
            );
            $stmt->bind_param('s', $conversationId);
            $stmt->execute();
            $stmt->close();
        }

        // Save message pair
        $stmt = $db->prepare(
            "INSERT INTO ai_conversation_messages (conversation_id, role, content, metadata, created_at)
             VALUES (?, 'user', ?, ?, NOW()), (?, 'assistant', ?, ?, NOW())"
        );

        $userMetadata = json_encode(['intent' => $orchestratorResult['intent']]);
        $assistantMetadata = json_encode([
            'tools_used' => $orchestratorResult['tools_executed'],
            'knowledge_items' => $orchestratorResult['knowledge_items'],
            'processing_time_ms' => $orchestratorResult['processing_time_ms']
        ]);

        $stmt->bind_param('ssssss',
            $conversationId, $query, $userMetadata,
            $conversationId, $aiResponse['content'], $assistantMetadata
        );
        $stmt->execute();
        $stmt->close();

    } catch (Exception $e) {
        error_log("[ai_agent_query] Save conversation failed: " . $e->getMessage());
    }
}
