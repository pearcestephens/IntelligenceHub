<?php

/**
 * Simple Claude Chat API - Fixed Version
 * Works without complex dependencies
 */

require_once 'simple-bootstrap.php';

// Set proper headers for SSE
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Handle preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        http_response_code(400);
        echo "data: " . json_encode(['type' => 'error', 'data' => ['error' => 'No message provided']]) . "\n\n";
        exit;
    }
    
    $message = $input['message'];
    $stream = $input['stream'] ?? true;
    
    // Initialize Claude API
    $claude = new SimpleClaudeAPI();
    
    if ($stream) {
        // Send start event
        echo "data: " . json_encode(['type' => 'start', 'data' => ['status' => 'started']]) . "\n\n";
        flush();
        
        // Get response from Claude
        $response = $claude->chat($message);
        
        if (isset($response['error'])) {
            echo "data: " . json_encode(['type' => 'error', 'data' => ['error' => $response['error']]]) . "\n\n";
        } else {
            $content = $response['content'][0]['text'] ?? 'No response';
            
            // Send the response
            echo "data: " . json_encode(['type' => 'message', 'data' => ['content' => $content]]) . "\n\n";
        }
        
        // Send end event
        echo "data: " . json_encode(['type' => 'end', 'data' => ['status' => 'completed']]) . "\n\n";
        flush();
        
    } else {
        // Non-streaming response
        header('Content-Type: application/json');
        
        $response = $claude->chat($message);
        
        if (isset($response['error'])) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $response['error']]);
        } else {
            $content = $response['content'][0]['text'] ?? 'No response';
            echo json_encode(['success' => true, 'message' => $content]);
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    if ($stream ?? true) {
        echo "data: " . json_encode(['type' => 'error', 'data' => ['error' => $e->getMessage()]]) . "\n\n";
    } else {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

?>