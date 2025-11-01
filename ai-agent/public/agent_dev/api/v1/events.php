<?php
/**
 * Conversation Events SSE Stream
 * 
 * GET /agent/api/v1/conversations/{conversation_id}/events
 * 
 * Streams real-time events for a conversation including:
 * - message.created
 * - tool_call.started
 * - tool_call.completed  
 * - tool_call.failed
 * 
 * @author AI Agent System
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../api/bootstrap.php';
require_once __DIR__ . '/../../../api/auth.php';
require_once __DIR__ . '/../../../api/sse.php';

ApiBootstrap::init();

$auth = new ApiAuth();
$apiKey = $auth->authenticate();

$pathInfo = $_SERVER['PATH_INFO'] ?? '';

// Parse conversation ID from path
if (!preg_match('/^\/([a-f0-9-]{36})\/events$/', $pathInfo, $matches)) {
    ApiBootstrap::error('Invalid path. Use: /conversations/{id}/events', 400);
}

$conversationId = $matches[1];

// Validate conversation exists
$db = ApiBootstrap::getDb();
$stmt = $db->prepare('SELECT conversation_id FROM conversations WHERE conversation_id = ?');
$result = $stmt->execute([$conversationId]);

if (!$result->fetch()) {
    ApiBootstrap::error('Conversation not found', 404);
}

// Start SSE stream
$sse = new SSEHandler();
$sse->startStream('conversation', ['conversation_id' => $conversationId]);