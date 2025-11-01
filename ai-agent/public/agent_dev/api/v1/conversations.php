<?php
/**
 * Conversations API v1 - Create and retrieve conversations
 * 
 * POST /agent/api/v1/conversations - Create new conversation
 * GET /agent/api/v1/conversations/{conversation_id} - Get conversation details
 * 
 * @author AI Agent System
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../api/bootstrap.php';
require_once __DIR__ . '/../../../api/auth.php';

ApiBootstrap::init();

$auth = new ApiAuth();
$apiKey = $auth->authenticate();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$pathInfo = $_SERVER['PATH_INFO'] ?? '';

// Parse conversation ID from path
$conversationId = null;
if (preg_match('/^\/([a-f0-9-]{36})$/', $pathInfo, $matches)) {
    $conversationId = $matches[1];
}

try {
    switch ($method) {
        case 'POST':
            handleCreateConversation($auth);
            break;
            
        case 'GET':
            if ($conversationId) {
                handleGetConversation($conversationId);
            } else {
                ApiBootstrap::error('Conversation ID required', 400);
            }
            break;
            
        default:
            ApiBootstrap::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    ApiBootstrap::getLogger()->error('Conversation API error', [
        'error' => $e->getMessage(),
        'method' => $method,
        'path' => $pathInfo,
        'request_id' => ApiBootstrap::getRequestId(),
    ]);
    
    ApiBootstrap::error('Internal server error', 500);
}

/**
 * Create new conversation
 */
function handleCreateConversation(ApiAuth $auth): void
{
    // Check idempotency
    $idempotency = $auth->checkIdempotency('conversations.create');
    
    $data = ApiBootstrap::getJsonBody();
    
    // Validate optional fields
    $title = $data['title'] ?? 'New Conversation';
    $metadata = $data['metadata'] ?? null;
    
    if ($metadata !== null && !is_array($metadata)) {
        ApiBootstrap::error('Metadata must be an object', 400);
    }
    
    // Generate UUID for conversation
    $conversationId = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Insert conversation
    $db = ApiBootstrap::getDb();
    $stmt = $db->prepare('
        INSERT INTO conversations (conversation_id, title, metadata, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ');
    
    $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    
    $result = $stmt->execute([$conversationId, $title, $metadataJson]);
    
    if (!$result) {
        ApiBootstrap::error('Failed to create conversation', 500);
    }
    
    $conversation = [
        'conversation_id' => $conversationId,
        'title' => $title,
        'metadata' => $metadata,
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];
    
    // Store idempotent response
    if ($idempotency) {
        $responseBody = json_encode([
            'success' => true,
            'data' => $conversation,
            'request_id' => ApiBootstrap::getRequestId(),
        ]);
        
        $auth->storeIdempotentResponse($idempotency['scope'], $idempotency['key'], 201, $responseBody);
    }
    
    ApiBootstrap::respond($conversation, 201);
}

/**
 * Get conversation details
 */
function handleGetConversation(string $conversationId): void
{
    $db = ApiBootstrap::getDb();
    
    // Get conversation
    $stmt = $db->prepare('
        SELECT 
            conversation_id,
            title,
            metadata,
            created_at,
            updated_at
        FROM conversations 
        WHERE conversation_id = ?
    ');
    
    $result = $stmt->execute([$conversationId]);
    $conversation = $result->fetch();
    
    if (!$conversation) {
        ApiBootstrap::error('Conversation not found', 404);
    }
    
    // Parse metadata
    $conversation['metadata'] = $conversation['metadata'] ? 
        json_decode($conversation['metadata'], true) : null;
    
    // Get message count
    $stmt = $db->prepare('SELECT COUNT(*) as message_count FROM messages WHERE conversation_id = ?');
    $result = $stmt->execute([$conversationId]);
    $countRow = $result->fetch();
    $conversation['message_count'] = (int)($countRow['message_count'] ?? 0);
    
    ApiBootstrap::respond($conversation);
}