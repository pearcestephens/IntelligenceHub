<?php

declare(strict_types=1);

/**
 * Claude Chat API Endpoint - Simplified version
 * 
 * Simple streaming chat with Claude AI without complex conversation management
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Config;
use App\Logger;
use App\Claude;
use App\SSE;
use App\Util\Validate;
use App\Util\Errors;
use App\Util\Ids;

// Set headers only in web context
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get request method
    $method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
    
    if ($method !== 'POST') {
        throw Errors::validationError('Only POST method allowed');
    }
    
    // Read JSON body (supports CLI via STDIN)
    $raw = file_get_contents('php://input');
    if (($raw === '' || $raw === false) && php_sapi_name() === 'cli') {
        $raw = stream_get_contents(STDIN);
    }
    
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        throw Errors::validationError('Invalid JSON input');
    }
    
    // Validate input
    $message = Validate::string($input['message'] ?? '', 'message', 1, 50000);
    $stream = (bool)($input['stream'] ?? true);
    $model = Validate::string($input['model'] ?? Config::get('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'), 'model', 1, 100);
    
    // Generate conversation ID for tracking
    $conversationId = 'simple_' . Ids::generate();
    
    Logger::info('Claude simple chat request', [
        'conversation_id' => $conversationId,
        'model' => $model,
        'stream' => $stream,
        'message_length' => strlen($message)
    ]);
    
    // Initialize Claude client
    $claude = new Claude(new Config(), new Logger(new Config()));
    
    // Prepare message for Claude
    $messages = [
        [
            'role' => 'user',
            'content' => $message
        ]
    ];
    
    if ($stream) {
        // Start SSE stream
        $sse = new SSE(new Logger(new Config()));
        $sse->start();
        
        try {
            // Stream response from Claude
            $responseData = '';
            foreach ($claude->streamCompletion($messages, function($chunk) use ($sse, &$responseData) {
                $responseData .= $chunk;
                $sse->send([
                    'type' => 'content',
                    'data' => [
                        'content' => $chunk
                    ]
                ]);
            }) as $chunk) {
                // Chunks are handled by the callback
            }
            
            // Send final response
            $sse->send([
                'type' => 'done',
                'data' => [
                    'conversation_id' => $conversationId,
                    'model' => $model,
                    'full_response' => $responseData
                ]
            ]);
            
        } catch (\Exception $e) {
            Logger::error('Claude streaming error', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);
            
            $sse->send([
                'type' => 'error',
                'error' => [
                    'message' => 'Failed to get response from Claude',
                    'details' => $e->getMessage()
                ]
            ]);
        } finally {
            $sse->end();
        }
        
    } else {
        // Non-streaming response
        try {
            $response = $claude->completion($messages);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'model' => $model,
                    'message' => $response['content'][0]['text'] ?? '',
                    'usage' => $response['usage'] ?? []
                ]
            ], JSON_THROW_ON_ERROR);
            
        } catch (\Exception $e) {
            throw Errors::internalError('Claude completion failed: ' . $e->getMessage());
        }
    }
    
} catch (\Exception $e) {
    Logger::error('Claude chat API error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (php_sapi_name() !== 'cli') {
        http_response_code(400);
        header('Content-Type: application/json');
    }
    
    echo json_encode([
        'success' => false,
        'error' => [
            'type' => 'system_error',
            'message' => $e->getMessage()
        ]
    ], JSON_THROW_ON_ERROR);
}