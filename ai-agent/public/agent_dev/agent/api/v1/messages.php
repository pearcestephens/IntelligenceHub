<?php
/**
 * Messages API v1 - Create messages and get conversation messages
 * 
 * POST /agent/api/v1/messages - Create new message
 * GET /agent/api/v1/conversations/{conversation_id}/messages - Get messages for conversation
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

try {
    switch ($method) {
        case 'POST':
            handleCreateMessage($auth);
            break;
            
        case 'GET':
            // Parse conversation ID from path: /conversations/{id}/messages
            if (preg_match('/^\/conversations\/([a-f0-9-]{36})\/messages$/', $pathInfo, $matches)) {
                $conversationId = $matches[1];
                handleGetMessages($conversationId);
            } else {
                ApiBootstrap::error('Invalid path. Use: /conversations/{id}/messages', 400);
            }
            break;
            
        default:
            ApiBootstrap::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    ApiBootstrap::getLogger()->error('Messages API error', [
        'error' => $e->getMessage(),
        'method' => $method,
        'path' => $pathInfo,
        'request_id' => ApiBootstrap::getRequestId(),
    ]);
    
    ApiBootstrap::error('Internal server error', 500);
}

/**
 * Create new message
 */
function handleCreateMessage(ApiAuth $auth): void
{
    // Check idempotency
    $idempotency = $auth->checkIdempotency('messages.create');
    
    $data = ApiBootstrap::getJsonBody();
    
    // Validate required fields
    ApiBootstrap::validateRequired($data, ['conversation_id', 'role', 'content']);
    
    $conversationId = $data['conversation_id'];
    $role = $data['role'];
    $content = $data['content'];
    $metadata = $data['metadata'] ?? null;
    
    // Validate role
    $validRoles = ['system', 'user', 'assistant', 'tool'];
    if (!in_array($role, $validRoles, true)) {
        ApiBootstrap::error(
            'Invalid role. Must be one of: ' . implode(', ', $validRoles),
            400,
            'validation_error'
        );
    }
    
    // Validate conversation exists
    $db = ApiBootstrap::getDb();
    $stmt = $db->prepare('SELECT conversation_id FROM conversations WHERE conversation_id = ?');
    $result = $stmt->execute([$conversationId]);
    
    if (!$result->fetch()) {
        ApiBootstrap::error('Conversation not found', 404);
    }
    
    // Validate metadata
    if ($metadata !== null && !is_array($metadata)) {
        ApiBootstrap::error('Metadata must be an object', 400);
    }
    
    // Generate UUID for message
    $messageId = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Insert message
    $stmt = $db->prepare('
        INSERT INTO messages (message_id, conversation_id, role, content, metadata, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ');
    
    $metadataJson = $metadata ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    
    $result = $stmt->execute([$messageId, $conversationId, $role, $content, $metadataJson]);
    
    if (!$result) {
        ApiBootstrap::error('Failed to create message', 500);
    }
    
    // Update conversation timestamp
    $stmt = $db->prepare('UPDATE conversations SET updated_at = NOW() WHERE conversation_id = ?');
    $stmt->execute([$conversationId]);
    
    $message = [
        'message_id' => $messageId,
        'conversation_id' => $conversationId,
        'role' => $role,
        'content' => $content,
        'metadata' => $metadata,
        'created_at' => date('c'),
    ];
    
    // Store idempotent response
    if ($idempotency) {
        $responseBody = json_encode([
            'success' => true,
            'data' => $message,
            'request_id' => ApiBootstrap::getRequestId(),
        ]);
        
        $auth->storeIdempotentResponse($idempotency['scope'], $idempotency['key'], 201, $responseBody);
    }
    
    ApiBootstrap::respond($message, 201);
}

/**
 * Get messages for conversation
 */
function handleGetMessages(string $conversationId): void
{
    // Validate conversation exists
    $db = ApiBootstrap::getDb();
    $stmt = $db->prepare('SELECT conversation_id FROM conversations WHERE conversation_id = ?');
    $result = $stmt->execute([$conversationId]);
    
    if (!$result->fetch()) {
        ApiBootstrap::error('Conversation not found', 404);
    }
    
    // Parse query parameters
    $since = $_GET['since'] ?? null;
    $limit = min(100, max(1, (int)($_GET['limit'] ?? 50))); // Default 50, max 100
    
    // Build query
    $conditions = ['conversation_id = ?'];
    $params = [$conversationId];
    
    if ($since) {
        $conditions[] = 'created_at > ?';
        $params[] = $since;
    }
    
    $whereClause = implode(' AND ', $conditions);
    
    // Get messages
    $stmt = $db->prepare("
        SELECT 
            message_id,
            conversation_id,
            role,
            content,
            metadata,
            created_at
        FROM messages 
        WHERE {$whereClause}
        ORDER BY created_at ASC
        LIMIT ?
    ");
    
    $params[] = $limit;
    $result = $stmt->execute($params);
    
    $messages = [];
    while ($row = $result->fetch()) {
        $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : null;
        $messages[] = $row;
    }
    
    // Get pagination info
    $hasMore = false;
    if (count($messages) === $limit) {
        // Check if there are more messages
        $lastMessage = end($messages);
        $stmt = $db->prepare('
            SELECT COUNT(*) as count 
            FROM messages 
            WHERE conversation_id = ? AND created_at > ?
        ');
        $result = $stmt->execute([$conversationId, $lastMessage['created_at']]);
        $countRow = $result->fetch();
        $hasMore = ($countRow['count'] ?? 0) > 0;
    }
    
    ApiBootstrap::respond([
        'messages' => $messages,
        'pagination' => [
            'limit' => $limit,
            'count' => count($messages),
            'has_more' => $hasMore,
        ],
    ]);
}