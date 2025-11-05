<?php
/**
 * OpenAI-Compatible Chat Completions Endpoint
 *
 * Provides /v1/chat/completions compatible with VS Code 1.104+ "OpenAI Compatible" provider
 * Routes requests to your AI Agent with full conversation recording
 *
 * @package IntelligenceHub\API
 * @version 1.0.0
 */

declare(strict_types=1);

// Load environment
require_once __DIR__ . '/../../../ai-agent/lib/Bootstrap.php';
require_once __DIR__ . '/../../../ai-agent/lib/AIOrchestrator.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => ['message' => 'Method not allowed', 'type' => 'invalid_request_error']]);
    exit;
}

// Auth check
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$expectedKey = $_ENV['MCP_API_KEY'] ?? '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';

if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches) || $matches[1] !== $expectedKey) {
    http_response_code(401);
    echo json_encode(['error' => ['message' => 'Invalid API key', 'type' => 'invalid_request_error']]);
    exit;
}

// Parse request
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => 'Invalid request body', 'type' => 'invalid_request_error']]);
    exit;
}

$messages = $input['messages'];
$model = $input['model'] ?? 'gpt-5-turbo';
$stream = $input['stream'] ?? false;
$conversationId = $input['conversation_id'] ?? 'vscode-' . uniqid();

// Extract user message (last user role)
$userMessage = '';
foreach (array_reverse($messages) as $msg) {
    if ($msg['role'] === 'user') {
        $userMessage = $msg['content'];
        break;
    }
}

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => 'No user message found', 'type' => 'invalid_request_error']]);
    exit;
}

try {
    // Connect to database
    $db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');
    if ($db->connect_error) {
        throw new Exception("Database connection failed");
    }
    $db->set_charset('utf8mb4');

    // Record conversation input
    recordConversation($db, $conversationId, 'user', $userMessage, $messages);

    // Initialize orchestrator
    $orchestrator = new AIOrchestrator($db, [
        'enable_semantic_search' => true,
        'enable_tool_execution' => true,
        'enable_conversation_memory' => true,
        'max_context_items' => 15,
        'max_memory_turns' => 10,
        'similarity_threshold' => 0.7
    ]);

    // Process query
    $result = $orchestrator->processQuery($userMessage, $conversationId, 1, [
        'mode' => 'standard',
        'format' => 'openai',
        'include_context' => true
    ]);

    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Processing failed');
    }

    // Get AI response from your AI Agent API
    $aiResponse = callAIAgent($userMessage, $result['enhanced_context'], $conversationId);

    // Record conversation output
    recordConversation($db, $conversationId, 'assistant', $aiResponse['content'], $result);

    // Format as OpenAI response
    $response = [
        'id' => 'chatcmpl-' . uniqid(),
        'object' => 'chat.completion',
        'created' => time(),
        'model' => $model,
        'choices' => [
            [
                'index' => 0,
                'message' => [
                    'role' => 'assistant',
                    'content' => $aiResponse['content']
                ],
                'finish_reason' => 'stop'
            ]
        ],
        'usage' => [
            'prompt_tokens' => $aiResponse['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $aiResponse['usage']['completion_tokens'] ?? 0,
            'total_tokens' => $aiResponse['usage']['total_tokens'] ?? 0
        ],
        'intelligence_hub' => [
            'conversation_id' => $conversationId,
            'processing_time_ms' => $result['processing_time_ms'] ?? 0,
            'knowledge_items_used' => $result['knowledge_items'] ?? 0,
            'tools_executed' => $result['tools_executed'] ?? [],
            'memory_turns' => $result['memory_turns'] ?? 0
        ]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => [
            'message' => $e->getMessage(),
            'type' => 'server_error'
        ]
    ]);
}

/**
 * Record conversation to database using existing schema
 */
function recordConversation(
    mysqli $db,
    string $conversationId,
    string $role,
    string $content,
    array $metadata
): void {
    // First, ensure conversation exists
    $stmt = $db->prepare(
        "INSERT INTO ai_conversations
        (session_id, platform, source, conversation_title, started_at, last_message_at, status, total_messages)
        VALUES (?, 'github_copilot', 'vscode', ?, NOW(), NOW(), 'active', 0)
        ON DUPLICATE KEY UPDATE
            last_message_at = NOW(),
            total_messages = total_messages + 1"
    );

    $title = substr($content, 0, 100);
    $stmt->bind_param('ss', $conversationId, $title);
    $stmt->execute();
    $stmt->close();

    // Get conversation_id (bigint primary key)
    $stmt = $db->prepare("SELECT conversation_id FROM ai_conversations WHERE session_id = ? ORDER BY conversation_id DESC LIMIT 1");
    $stmt->bind_param('s', $conversationId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $convId = $row['conversation_id'];
    $stmt->close();

    // Insert message
    $stmt = $db->prepare(
        "INSERT INTO ai_conversation_messages
        (conversation_id, message_sequence, role, content, metadata, created_at)
        VALUES (?, (SELECT COALESCE(MAX(message_sequence), 0) + 1 FROM ai_conversation_messages WHERE conversation_id = ?), ?, ?, ?, NOW())"
    );

    $metadataJson = json_encode($metadata);
    $stmt->bind_param('iiss', $convId, $convId, $role, $content, $metadataJson);
    $stmt->execute();
    $stmt->close();
}

/**
 * Call your AI Agent API for response generation
 */
function callAIAgent(string $query, array $context, string $conversationId): array {
    $url = 'https://gpt.ecigdis.co.nz/ai-agent/api/chat.php';

    $payload = [
        'query' => $query,
        'conversation_id' => $conversationId,
        'context' => $context,
        'model' => 'gpt-5-turbo',
        'stream' => false
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . ($_ENV['MCP_API_KEY'] ?? '')
        ],
        CURLOPT_TIMEOUT => 45
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("AI Agent returned HTTP $httpCode");
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['response'])) {
        throw new Exception("Invalid AI Agent response");
    }

    return [
        'content' => $data['response'],
        'usage' => $data['usage'] ?? []
    ];
}
