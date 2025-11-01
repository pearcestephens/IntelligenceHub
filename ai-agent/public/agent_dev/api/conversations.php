<?php

declare(strict_types=1);

/**
 * Conversations API Endpoint - Manage conversations
 * 
 * Provides CRUD operations for conversations:
 * - GET: List conversations with pagination
 * - POST: Create new conversation
 * - GET /{id}: Get specific conversation details
 * - PUT /{id}: Update conversation (title, metadata)
 * - DELETE /{id}: Delete conversation
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Validate;
use App\Util\Errors;
use App\Util\RateLimit;
use App\Util\SecurityHeaders;

// Set headers only if running in web context
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    SecurityHeaders::applyJson();
    
    // Handle preflight OPTIONS request
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

try {
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();

    // Global rate limiting (per client/IP)
    if (RateLimit::shouldRateLimit()) {
        // Identifier derived from IP/session by default
        RateLimit::middleware();
    }
    
    // Parse URL path and query for conversation ID
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/api/conversations';
    $pathParts = explode('/', trim($requestUri, '/'));
    $conversationId = null;
    
    // Try to extract conversation ID from path first
    $apiIndex = array_search('conversations.php', $pathParts);
    if ($apiIndex !== false && isset($pathParts[$apiIndex + 1])) {
        $conversationId = $pathParts[$apiIndex + 1];
        // Remove query string if present
        $conversationId = strtok($conversationId, '?');
    }
    
    // If not found in path, check query parameters
    if (!$conversationId && isset($_GET['id'])) {
        $conversationId = $_GET['id'];
    }
    
    // Determine HTTP method (CLI defaults to GET; allow override via METHOD env)
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!$method) {
        $envMethod = getenv('METHOD');
        $method = ($envMethod && is_string($envMethod) && $envMethod !== '') ? strtoupper($envMethod) : (php_sapi_name() === 'cli' ? 'GET' : 'UNKNOWN');
    }
    $logger->info('Conversations API request', [
        'method' => $method,
        'conversation_id' => $conversationId,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    switch ($method) {
        case 'GET':
            if ($conversationId) {
                // Get specific conversation
                $result = $agent->getConversation($conversationId);
                
                // Add messages if requested
                if (isset($_GET['include_messages']) && $_GET['include_messages'] === 'true') {
                    $messages = $agent->getConversationManager()->getMessages($conversationId);
                    $result['conversation']['messages'] = $messages;
                }
                
            } else {
                // List conversations with pagination
                $limit = (int)($_GET['limit'] ?? 20);
                $offset = (int)($_GET['offset'] ?? 0);
                
                $limit = Validate::integer($limit, 'limit', 1, 100);
                $offset = Validate::integer($offset, 'offset', 0, PHP_INT_MAX);
                
                $result = $agent->listConversations($limit, $offset);
            }
            break;
            
        case 'POST':
            // Create new conversation
            $raw = file_get_contents('php://input');
            if ($raw === '' && php_sapi_name() === 'cli') {
                // Support CLI: read from STDIN if provided or from ENV JSON
                $raw = stream_get_contents(STDIN);
                if ($raw === '' || $raw === false) {
                    $raw = getenv('JSON') ?: '';
                }
            }
            $input = json_decode($raw, true);
            
            if (!is_array($input)) {
                throw Errors::validationError('Invalid JSON input');
            }
            
            $title = $input['title'] ?? 'New Conversation';
            $metadata = $input['metadata'] ?? [];
            
            $title = Validate::string($title, 'title', 1, 200);
            $metadata = Validate::array($metadata, 'metadata');
            
            $result = $agent->createConversation($title, $metadata);
            break;
            
        case 'PUT':
            if (!$conversationId) {
                throw new \InvalidArgumentException('Conversation ID required for update');
            }
            
            // Update conversation
            $raw = file_get_contents('php://input');
            if ($raw === '' && php_sapi_name() === 'cli') {
                $raw = stream_get_contents(STDIN);
                if ($raw === '' || $raw === false) {
                    $raw = getenv('JSON') ?: '';
                }
            }
            $input = json_decode($raw, true);
            
            if (!is_array($input)) {
                throw Errors::validationError('Invalid JSON input');
            }
            
            // Currently only supporting title updates
            if (isset($input['title'])) {
                $title = Validate::string($input['title'], 'title', 1, 200);
                
                $success = $agent->getConversationManager()->updateConversationTitle($conversationId, $title);
                
                if (!$success) {
                    throw new \RuntimeException('Failed to update conversation');
                }
                
                $result = [
                    'success' => true,
                    'message' => 'Conversation updated successfully'
                ];
            } else {
                throw new \InvalidArgumentException('No valid update fields provided');
            }
            break;
            
        case 'DELETE':
            if (!$conversationId) {
                throw new \InvalidArgumentException('Conversation ID required for deletion');
            }
            
            $result = $agent->deleteConversation($conversationId);
            
            if (!$result['success']) {
                throw new \RuntimeException('Failed to delete conversation');
            }
            break;
            
        default:
            throw new \InvalidArgumentException('Method not allowed: ' . $method);
    }
    
    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    // Clean up
    $agent->shutdown();
    
} catch (Exception $e) {
    $errorCode = 500;
    $errorType = 'internal_error';
    
    // Determine appropriate error code
    if (strpos($e->getMessage(), 'validation') !== false) {
        $errorCode = 400;
        $errorType = 'validation_error';
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        $errorCode = 404;
        $errorType = 'not_found_error';
    } elseif (strpos($e->getMessage(), 'Method not allowed') !== false) {
        $errorCode = 405;
        $errorType = 'method_not_allowed';
    }
    
    // Log error
    if (isset($logger)) {
        $logger->error('Conversations API error', [
            'error' => $e->getMessage(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'conversation_id' => $conversationId ?? null,
            'type' => $errorType,
            'code' => $errorCode
        ]);
    }
    
    // Return error response
    http_response_code($errorCode);
    
    $errorResponse = [
        'success' => false,
        'error' => [
            'type' => $errorType,
            'message' => $e->getMessage(),
            'code' => $errorCode
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Ensure output is flushed
if (ob_get_level()) {
    ob_end_flush();
}
flush();