<?php
/**
 * AI Agent - Streaming Response API (Server-Sent Events)
 * 
 * Handles streaming chat responses using SSE
 * 
 * @version 2.0.0
 * @author AI Agent System
 */

declare(strict_types=1);

// Set SSE headers
header('Content-Type: text/event-stream');

// Enable gzip compression (Performance Improvement)
if (!headers_sent() && extension_loaded('zlib') && !ob_get_length()) {
    ini_set('zlib.output_compression', '1');
    ini_set('zlib.output_compression_level', '6');
}

header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable Nginx buffering

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

/**
 * Send SSE data
 */
function sendSSEData(string $data, string $event = 'message', ?string $id = null): void
{
    if ($id !== null) {
        echo "id: {$id}\n";
    }
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";
    
    // Flush output immediately
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

/**
 * Send error via SSE
 */
function sendSSEError(string $message, string $code = 'error'): void
{
    sendSSEData([
        'error' => $message,
        'code' => $code,
        'timestamp' => date('c')
    ], 'error');
}

/**
 * Simulate streaming by breaking text into chunks
 */
function streamText(string $text, float $chunkDelay = 0.05): void
{
    // Split text into words for more natural streaming
    $words = explode(' ', $text);
    $currentChunk = '';
    $chunkSize = 3; // Words per chunk
    
    for ($i = 0; $i < count($words); $i++) {
        $currentChunk .= ($currentChunk ? ' ' : '') . $words[$i];
        
        // Send chunk when we have enough words or at the end
        if (($i + 1) % $chunkSize === 0 || $i === count($words) - 1) {
            sendSSEData([
                'chunk' => $currentChunk . ' ',
                'done' => false,
                'chunk_id' => $i + 1
            ]);
            
            $currentChunk = '';
            
            // Small delay between chunks
            usleep((int)($chunkDelay * 1000000));
        }
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
    }
    
    // Send completion signal
    sendSSEData([
        'chunk' => '',
        'done' => true,
        'total_chunks' => count($words)
    ]);
}

try {
    // Validate parameters
    $streamId = $_GET['stream_id'] ?? null;
    $sessionId = $_GET['session_id'] ?? null;
    
    if (!$streamId || !$sessionId) {
        sendSSEError('Missing required parameters');
        exit;
    }
    
    // Validate stream ID format
    if (!preg_match('/^stream_[a-f0-9]{16}$/', $streamId)) {
        sendSSEError('Invalid stream ID format');
        exit;
    }
    
    // Get stream file
    $streamFile = sys_get_temp_dir() . "/chat_stream_{$streamId}.txt";
    
    if (!file_exists($streamFile)) {
        sendSSEError('Stream not found or expired');
        exit;
    }
    
    // Read the response to stream
    $response = file_get_contents($streamFile);
    
    if ($response === false) {
        sendSSEError('Failed to read stream data');
        exit;
    }
    
    // Clean up the stream file
    unlink($streamFile);
    
    // Send initial connection confirmation
    sendSSEData([
        'status' => 'connected',
        'stream_id' => $streamId,
        'session_id' => $sessionId
    ], 'connection');
    
    // Start streaming the response
    streamText($response);
    
} catch (Exception $e) {
    sendSSEError('Streaming error: ' . $e->getMessage());
} finally {
    // Send final close event
    sendSSEData([
        'status' => 'closed',
        'timestamp' => date('c')
    ], 'close');
}
?>