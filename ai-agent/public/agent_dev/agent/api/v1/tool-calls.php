<?php
/**
 * Tool Calls API v1 - Create and update tool calls
 * 
 * POST /agent/api/v1/tool-calls - Create new tool call
 * PATCH /agent/api/v1/tool-calls/{tool_call_id} - Update tool call status/result
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

// Parse tool call ID from path
$toolCallId = null;
if (preg_match('/^\/([a-f0-9-]{36})$/', $pathInfo, $matches)) {
    $toolCallId = $matches[1];
}

try {
    switch ($method) {
        case 'POST':
            handleCreateToolCall($auth);
            break;
            
        case 'PATCH':
            if ($toolCallId) {
                handleUpdateToolCall($toolCallId, $auth);
            } else {
                ApiBootstrap::error('Tool call ID required', 400);
            }
            break;
            
        default:
            ApiBootstrap::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    ApiBootstrap::getLogger()->error('Tool Calls API error', [
        'error' => $e->getMessage(),
        'method' => $method,
        'path' => $pathInfo,
        'request_id' => ApiBootstrap::getRequestId(),
    ]);
    
    ApiBootstrap::error('Internal server error', 500);
}

/**
 * Create new tool call
 */
function handleCreateToolCall(ApiAuth $auth): void
{
    // Check idempotency
    $idempotency = $auth->checkIdempotency('tool_calls.create');
    
    $data = ApiBootstrap::getJsonBody();
    
    // Validate required fields
    ApiBootstrap::validateRequired($data, ['message_id', 'tool_name']);
    
    $messageId = $data['message_id'];
    $toolName = $data['tool_name'];
    $functionName = $data['function_name'] ?? null;
    $arguments = $data['arguments'] ?? null;
    
    // Validate message exists
    $db = ApiBootstrap::getDb();
    $stmt = $db->prepare('SELECT message_id FROM messages WHERE message_id = ?');
    $result = $stmt->execute([$messageId]);
    
    if (!$result->fetch()) {
        ApiBootstrap::error('Message not found', 404);
    }
    
    // Validate arguments
    if ($arguments !== null && !is_array($arguments)) {
        ApiBootstrap::error('Arguments must be an object', 400);
    }
    
    // Generate UUID for tool call
    $toolCallId = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Insert tool call
    $stmt = $db->prepare('
        INSERT INTO tool_calls (
            tool_call_id, 
            message_id, 
            tool_name, 
            function_name, 
            arguments,
            status,
            created_at,
            updated_at
        )
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    
    $argumentsJson = $arguments ? json_encode($arguments, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    
    $result = $stmt->execute([
        $toolCallId, 
        $messageId, 
        $toolName, 
        $functionName, 
        $argumentsJson,
        'pending'
    ]);
    
    if (!$result) {
        ApiBootstrap::error('Failed to create tool call', 500);
    }
    
    $toolCall = [
        'tool_call_id' => $toolCallId,
        'message_id' => $messageId,
        'tool_name' => $toolName,
        'function_name' => $functionName,
        'arguments' => $arguments,
        'status' => 'pending',
        'result' => null,
        'duration_ms' => null,
        'created_at' => date('c'),
        'updated_at' => date('c'),
    ];
    
    // Store idempotent response
    if ($idempotency) {
        $responseBody = json_encode([
            'success' => true,
            'data' => $toolCall,
            'request_id' => ApiBootstrap::getRequestId(),
        ]);
        
        $auth->storeIdempotentResponse($idempotency['scope'], $idempotency['key'], 201, $responseBody);
    }
    
    ApiBootstrap::respond($toolCall, 201);
}

/**
 * Update tool call status and result
 */
function handleUpdateToolCall(string $toolCallId, ApiAuth $auth): void
{
    $data = ApiBootstrap::getJsonBody();
    
    // Get current tool call
    $db = ApiBootstrap::getDb();
    $stmt = $db->prepare('
        SELECT 
            tool_call_id,
            message_id,
            tool_name,
            function_name,
            arguments,
            status,
            result,
            duration_ms,
            created_at,
            updated_at
        FROM tool_calls 
        WHERE tool_call_id = ?
    ');
    
    $result = $stmt->execute([$toolCallId]);
    $toolCall = $result->fetch();
    
    if (!$toolCall) {
        ApiBootstrap::error('Tool call not found', 404);
    }
    
    // Prepare update fields
    $updates = [];
    $params = [];
    
    if (isset($data['status'])) {
        $validStatuses = ['pending', 'running', 'completed', 'failed'];
        if (!in_array($data['status'], $validStatuses, true)) {
            ApiBootstrap::error(
                'Invalid status. Must be one of: ' . implode(', ', $validStatuses),
                400
            );
        }
        $updates[] = 'status = ?';
        $params[] = $data['status'];
        $toolCall['status'] = $data['status'];
    }
    
    if (isset($data['result'])) {
        if (!is_array($data['result'])) {
            ApiBootstrap::error('Result must be an object', 400);
        }
        $resultJson = json_encode($data['result'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $updates[] = 'result = ?';
        $params[] = $resultJson;
        $toolCall['result'] = $data['result'];
    }
    
    if (isset($data['duration_ms'])) {
        $durationMs = (int)$data['duration_ms'];
        if ($durationMs < 0) {
            ApiBootstrap::error('Duration must be a positive integer', 400);
        }
        $updates[] = 'duration_ms = ?';
        $params[] = $durationMs;
        $toolCall['duration_ms'] = $durationMs;
    }
    
    if (empty($updates)) {
        ApiBootstrap::error('No fields to update', 400);
    }
    
    // Add updated_at
    $updates[] = 'updated_at = NOW()';
    $params[] = $toolCallId;
    
    // Update tool call
    $stmt = $db->prepare('
        UPDATE tool_calls 
        SET ' . implode(', ', $updates) . '
        WHERE tool_call_id = ?
    ');
    
    $result = $stmt->execute($params);
    
    if (!$result) {
        ApiBootstrap::error('Failed to update tool call', 500);
    }
    
    // Parse stored data for response
    $toolCall['arguments'] = $toolCall['arguments'] ? 
        json_decode($toolCall['arguments'], true) : null;
    $toolCall['result'] = isset($data['result']) ? 
        $data['result'] : 
        ($toolCall['result'] ? json_decode($toolCall['result'], true) : null);
    $toolCall['duration_ms'] = isset($data['duration_ms']) ? 
        $data['duration_ms'] : 
        $toolCall['duration_ms'];
    $toolCall['updated_at'] = date('c');
    
    ApiBootstrap::respond($toolCall);
}