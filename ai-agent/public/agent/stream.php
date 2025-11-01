<?php
/**
 * Server-Sent Events (SSE) Streaming Endpoint
 * Provides real-time chat streaming capabilities
 */
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Prevent timeout
set_time_limit(0);
ignore_user_abort(false);

// Get parameters
$conversation_id = $_GET['conversation_id'] ?? '';
$bot_id = $_GET['bot_id'] ?? 'general';

if (empty($conversation_id)) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Missing conversation_id']) . "\n\n";
    exit;
}

// Send initial connection confirmation
echo "event: connected\n";
echo "data: " . json_encode([
    'message' => 'Stream connected',
    'conversation_id' => $conversation_id,
    'bot_id' => $bot_id,
    'timestamp' => date('c')
]) . "\n\n";

if (ob_get_level()) {
    ob_end_flush();
}
flush();

// Simulate streaming response (in a real implementation, this would connect to an AI service)
$messages = [
    "I'm processing your request...",
    "Let me think about that for a moment...",
    "I'm analyzing the available information...",
    "Based on what you've asked, here's what I found...",
    "The answer to your question is quite interesting...",
    "I hope this information is helpful to you!"
];

$current_message = "";
foreach ($messages as $index => $message_part) {
    if (connection_aborted()) {
        break;
    }
    
    $current_message .= $message_part . " ";
    
    echo "event: message\n";
    echo "data: " . json_encode([
        'type' => 'partial',
        'content' => $current_message,
        'progress' => ($index + 1) / count($messages),
        'timestamp' => date('c')
    ]) . "\n\n";
    
    if (ob_get_level()) {
        ob_end_flush();
    }
    flush();
    
    // Simulate processing delay
    usleep(500000); // 0.5 seconds
}

// Send completion event
echo "event: complete\n";
echo "data: " . json_encode([
    'type' => 'complete',
    'final_response' => trim($current_message),
    'conversation_id' => $conversation_id,
    'timestamp' => date('c')
]) . "\n\n";

if (ob_get_level()) {
    ob_end_flush();
}
flush();
?>