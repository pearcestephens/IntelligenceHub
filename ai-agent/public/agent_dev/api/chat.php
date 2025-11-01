<?php

declare(strict_types=1);

/**
 * Chat API Endpoint - Main chat interface with streaming support
 * 
 * Handles chat requests with full AI agent integration including:
 * - Message processing with OpenAI integration
 * - Tool calling and execution
 * - Real-time streaming via Server-Sent Events
 * - Rate limiting and security
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';
use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Errors;
use App\Util\Validate;
use App\Util\RateLimit;
use App\Util\SecurityHeaders;

// Set CORS headers only in web context; defer Content-Type until we know stream vs json
if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Note: Do NOT apply security headers yet; we will do it in the JSON or SSE branch appropriately.

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Determine method (CLI defaults to POST if METHOD env provided)
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!$method) {
    $envMethod = getenv('METHOD');
    $method = ($envMethod && is_string($envMethod) && $envMethod !== '') ? strtoupper($envMethod) : (php_sapi_name() === 'cli' ? 'POST' : 'UNKNOWN');
}

// Only allow POST requests
if ($method !== 'POST') {
    http_response_code(405);
    // JSON error response with security headers applied
    if (php_sapi_name() !== 'cli') {
        SecurityHeaders::applyJson();
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();
    
    // Get and validate request data (support CLI STDIN/ENV)
    $raw = file_get_contents('php://input');
    if (($raw === '' || $raw === false) && php_sapi_name() === 'cli') {
        $raw = stream_get_contents(STDIN);
        if ($raw === '' || $raw === false) {
            $raw = getenv('JSON') ?: '';
        }
    }
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        throw Errors::validationError('Invalid JSON input');
    }
    
    // Validate required fields
    $message = Validate::string($input['message'] ?? '', 'message', 1, 100000);
    $conversationId = isset($input['conversation_id']) 
        ? Validate::string($input['conversation_id'], 'conversation_id', 1, 100)
        : null;
    
    // Optional parameters
    $options = [
        'model' => $input['model'] ?? null,
        'stream' => (bool)($input['stream'] ?? false),
        'enable_tools' => (bool)($input['enable_tools'] ?? true),
        'title' => $input['title'] ?? null,
        'client_id' => $input['client_id'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    $logger->info('Chat API request', [
        'message_length' => strlen($message),
        'conversation_id' => $conversationId,
        'stream' => $options['stream'],
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);

    // Web requests: apply middleware to enforce and add headers once
    if (RateLimit::shouldRateLimit() && php_sapi_name() !== 'cli') {
        RateLimit::middleware();
    }
    
    // Handle streaming vs regular response
    if ($options['stream']) {
        // Set headers for Server-Sent Events
        if (php_sapi_name() !== 'cli') {
            // SSE-safe security headers
            SecurityHeaders::applySSE();
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable Nginx buffering
        }
        
        // Ensure we have a conversation ID for SSE
        if (!$conversationId) {
            // Create a conversation if none provided
            $created = $agent->createConversation(
                'Chat Conversation',
                ['stream' => true, 'client_id' => $options['client_id']]
            );
            if (!($created['success'] ?? false)) {
                throw new \RuntimeException('Failed to create conversation');
            }
            $conversationId = $created['conversation']['conversation_id'] 
                ?? $created['conversation_id'] 
                ?? null;
        }

        // Start SSE stream with conversation ID
        $agent->startSSEStream($conversationId);
        
        // Process message with streaming
        $result = $agent->chat($message, $conversationId, $options);
        
        // Send final result
        echo "event: complete\n";
        echo "data: " . json_encode($result) . "\n\n";
        
        // Send end event
        echo "event: end\n";
        echo "data: {}\n\n";
        
    } else {
        // Regular JSON response
        if (php_sapi_name() !== 'cli') {
            SecurityHeaders::applyJson();
            header('Content-Type: application/json; charset=utf-8');
        }
        $result = $agent->chat($message, $conversationId, $options);
        http_response_code(200);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    // Clean up
    $agent->shutdown();
    
} catch (Exception $e) {
    $errorCode = 500;
    $errorType = 'internal_error';
    
    // Determine appropriate error code
    if (strpos($e->getMessage(), 'Rate limit') !== false) {
        $errorCode = 429;
        $errorType = 'rate_limit_error';
    } elseif (strpos($e->getMessage(), 'validation') !== false) {
        $errorCode = 400;
        $errorType = 'validation_error';
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        $errorCode = 404;
        $errorType = 'not_found_error';
    }
    
    // Log error
    if (isset($logger)) {
        $logger->error('Chat API error', [
            'error' => $e->getMessage(),
            'type' => $errorType,
            'code' => $errorCode,
            'trace' => $e->getTraceAsString()
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
    
    // Handle streaming error
    if (isset($options) && ($options['stream'] ?? false)) {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: text/event-stream');
        }
        echo "event: error\n";
        echo "data: " . json_encode($errorResponse) . "\n\n";
    } else {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/json');
        }
        echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

// Ensure output is flushed
if (ob_get_level()) {
    ob_end_flush();
}
flush();