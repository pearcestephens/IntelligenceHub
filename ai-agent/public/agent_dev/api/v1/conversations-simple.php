<?php
/**
 * Simple Conversations API - Gate 4 Testing
 * 
 * POST /conversations - Create new conversation
 * GET /conversations/{id} - Get conversation details
 */

declare(strict_types=1);

require_once __DIR__ . '/../../api/minimal-bootstrap.php';

$apiKey = checkAuth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    
    if ($method === 'POST') {
        // Create conversation
        $data = getJsonBody();
        $title = $data['title'] ?? 'Untitled Conversation';
        $metadata = $data['metadata'] ?? [];
        
        $conversationId = generateUuid();
        
        $stmt = $db->prepare("
            INSERT INTO conversations (conversation_id, title, metadata, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute([$conversationId, $title, json_encode($metadata)]);
        
        apiResponse([
            'conversation_id' => $conversationId,
            'title' => $title,
            'metadata' => $metadata,
            'created_at' => date('c')
        ], true, 201);
        
    } elseif ($method === 'GET') {
        // Get conversation
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        $conversationId = trim($pathInfo, '/');
        
        if (empty($conversationId)) {
            apiError('Conversation ID required in path', 'missing_parameter', 400);
        }
        
        $stmt = $db->prepare("
            SELECT conversation_id, title, metadata, created_at, updated_at
            FROM conversations 
            WHERE conversation_id = ?
        ");
        
        $stmt->execute([$conversationId]);
        $conversation = $stmt->fetch();
        
        if (!$conversation) {
            apiError('Conversation not found', 'not_found', 404);
        }
        
        $conversation['metadata'] = json_decode($conversation['metadata'] ?? '{}', true);
        
        apiResponse($conversation);
        
    } else {
        apiError('Method not allowed', 'method_not_allowed', 405);
    }
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    apiError('Database error occurred', 'database_error', 500);
} catch (Throwable $e) {
    error_log("API error: " . $e->getMessage());
    apiError('Internal server error', 'server_error', 500);
}
?>