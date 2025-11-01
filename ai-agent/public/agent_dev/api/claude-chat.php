<?php

declare(strict_types=1);

/**
 * Claude Chat API Endpoint - Real-time chat with Claude AI
 * 
 * Provides streaming chat completions using Anthropic's Claude models.
 * Supports conversation management, tool calling, and real-time SSE streaming.
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Claude;
use App\SSE;
use App\Util\Validate;
use App\Util\Errors;
use App\Util\RateLimit;
use App\Util\SecurityHeaders;

// Set headers only in web context
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    SecurityHeaders::applyStreaming();
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    $sse = new SSE($logger);
    
    // Check if Claude is configured
    if (!$config->get('ANTHROPIC_API_KEY') || $config->get('ANTHROPIC_API_KEY') === 'YOUR_CLAUDE_API_KEY_HERE') {
        throw Errors::internalError('Claude AI not configured. Please add ANTHROPIC_API_KEY to .env file');
    }
    
    $claude = new Claude($config, $logger);
    $agent = new Agent($config, $logger);
    $agent->initialize();
    
    // Global rate limiting
    if (RateLimit::shouldRateLimit()) {
        RateLimit::middleware();
    }
    
    // Determine method (CLI-friendly)
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!$method) {
        $envMethod = getenv('METHOD');
        $method = ($envMethod && is_string($envMethod) && $envMethod !== '') ? strtoupper($envMethod) : (php_sapi_name() === 'cli' ? 'POST' : 'UNKNOWN');
    }
    
    if ($method !== 'POST') {
        throw Errors::validationError('Only POST method allowed');
    }
    
    // Read JSON body (supports CLI via STDIN/ENV)
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
    
    // Validate and extract input
    $message = Validate::string($input['message'] ?? '', 'message', 1, 50000);
    $conversationId = Validate::string($input['conversation_id'] ?? '', 'conversation_id', 0, 100);
    $stream = (bool)($input['stream'] ?? true);
    $model = Validate::string($input['model'] ?? $config->get('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'), 'model', 1, 100);
    
    // Generate conversation ID if not provided
    if (empty($conversationId)) {
        $conversationId = 'claude_' . uniqid(time() . '_', true);
    }
    
    $logger->info('Claude chat request', [
        'conversation_id' => $conversationId,
        'model' => $model,
        'stream' => $stream,
        'message_length' => strlen($message),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    // Get or create conversation
    $conversation = null;
    try {
        $conversation = $agent->getConversation($conversationId);
    } catch (\Exception $e) {
        // Conversation doesn't exist, will create it below
    }
    
    if (!$conversation || !$conversation['success']) {
        // Create new conversation
        $conversation = $agent->createConversation($conversationId, [
            'title' => 'Claude Chat',
            'model' => $model,
            'stream' => $stream
        ]);
        
        if (!$conversation['success']) {
            throw Errors::internalError('Failed to create conversation: ' . ($conversation['error']['message'] ?? 'Unknown error'));
        }
    }
    
    // Add user message to conversation
    $conversationManager = $agent->getConversationManager();
    if (!$conversationManager) {
        throw Errors::internalError('Conversation manager not available');
    }
    
    $userMessageId = $conversationManager->addMessage($conversationId, 'user', $message, []);
    
    // Get conversation history for Claude
    $history = $conversationManager->getMessages($conversationId);
    if (!$history['success']) {
        throw Errors::internalError('Failed to get conversation history');
    }
    
    // Format messages for Claude
    $claudeMessages = [];
    foreach ($history['messages'] as $msg) {
        if ($msg['role'] !== 'system') { // Claude doesn't use system messages the same way
            $claudeMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
    }
    
    if ($stream) {
        // Start SSE streaming
        $sse->start();
        
        // Send initial status
        $sse->send([
            'type' => 'status',
            'data' => [
                'status' => 'processing',
                'message' => 'Claude AI is thinking...',
                'model' => $model
            ]
        ]);
        
        $assistantMessageId = null;
        $fullResponse = '';
        $startTime = microtime(true);
        
        try {
            // Stream Claude response
            $streamCallback = function($content) use ($sse, &$fullResponse) {
                $fullResponse .= $content;
                $sse->send([
                    'type' => 'content',
                    'data' => [
                        'content' => $content
                    ]
                ]);
            };
            
            // Create placeholder assistant message
            $assistantMessage = $agent->addMessage($conversationId, 'assistant', '', [
                'model' => $model,
                'streaming' => true
            ]);
            
            if ($assistantMessage['success']) {
                $assistantMessageId = $assistantMessage['message']['message_id'];
            }
            
            // Stream from Claude
            foreach ($claude->streamCompletion($claudeMessages, $streamCallback) as $chunk) {
                // Chunks are handled by the callback
            }
            
            // Update the assistant message with full response
            if ($assistantMessageId && $fullResponse) {
                $agent->updateMessage($assistantMessageId, $fullResponse, [
                    'model' => $model,
                    'streaming' => false,
                    'response_time' => microtime(true) - $startTime
                ]);
            }
            
            // Send completion status
            $sse->send([
                'type' => 'status',
                'data' => [
                    'status' => 'completed',
                    'message' => 'Response completed',
                    'response_time' => round(microtime(true) - $startTime, 3)
                ]
            ]);
            
        } catch (Exception $e) {
            $logger->error('Claude streaming error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            
            $sse->send([
                'type' => 'error',
                'data' => [
                    'error' => $e->getMessage()
                ]
            ]);
        }
        
        $sse->end();
        
    } else {
        // Non-streaming response
        $startTime = microtime(true);
        
        try {
            $response = $claude->completion($claudeMessages);
            $content = $response['content'][0]['text'] ?? '';
            
            if (!$content) {
                throw Errors::apiError('Empty response from Claude AI');
            }
            
            // Add assistant message
            $assistantMessage = $agent->addMessage($conversationId, 'assistant', $content, [
                'model' => $model,
                'response_time' => microtime(true) - $startTime,
                'usage' => $response['usage'] ?? null
            ]);
            
            if (!$assistantMessage['success']) {
                throw Errors::internalError('Failed to save assistant message');
            }
            
            // Return JSON response
            if (php_sapi_name() !== 'cli') {
                header('Content-Type: application/json; charset=utf-8');
            }
            
            echo json_encode([
                'success' => true,
                'response' => $content,
                'message_id' => $assistantMessage['message']['message_id'],
                'model' => $model,
                'usage' => $response['usage'] ?? null,
                'response_time' => round(microtime(true) - $startTime, 3)
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            $logger->error('Claude completion error', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            
            if (php_sapi_name() !== 'cli') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
            }
            
            echo json_encode([
                'success' => false,
                'error' => [
                    'type' => 'claude_error',
                    'message' => $e->getMessage()
                ]
            ], JSON_PRETTY_PRINT);
        }
    }
    
} catch (Exception $e) {
    $logger->error('Claude chat API error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    
    echo json_encode([
        'success' => false,
        'error' => [
            'type' => 'system_error',
            'message' => $e->getMessage()
        ]
    ], JSON_PRETTY_PRINT);
}