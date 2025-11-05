<?php
/**
 * AI Agent Streaming Endpoint
 *
 * Provides Server-Sent Events (SSE) streaming for long AI Agent responses
 * Prevents summarization by sending chunks progressively
 *
 * @package IntelligenceHub\AIAgent
 */

declare(strict_types=1);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

require_once __DIR__ . '/lib/AIOrchestrator.php';
require_once __DIR__ . '/lib/Config/ConfigManager.php';

use AIAgent\AIOrchestrator;
use AIAgent\Config\ConfigManager;

// Decode token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    sendSSE('error', ['message' => 'Missing token']);
    exit;
}

try {
    $data = json_decode(base64_decode($token), true);
    if (!$data || empty($data['query'])) {
        sendSSE('error', ['message' => 'Invalid token']);
        exit;
    }

    // Check token age (30 minutes max)
    if ((time() - $data['timestamp']) > 1800) {
        sendSSE('error', ['message' => 'Token expired']);
        exit;
    }

    $query = $data['query'];
    $conversationId = $data['conversation_id'] ?? null;
    $context = $data['context'] ?? [];

    // Initialize orchestrator
    $config = ConfigManager::getInstance();
    $orchestrator = new AIOrchestrator($config);

    // Send start event
    sendSSE('start', [
        'conversation_id' => $conversationId,
        'query' => $query,
        'timestamp' => date('c')
    ]);

    // Process with streaming callback
    $chunkCount = 0;
    $totalTokens = 0;

    $result = $orchestrator->processWithStreaming($query, $context, function($chunk) use (&$chunkCount, &$totalTokens) {
        $chunkCount++;
        $totalTokens += $chunk['tokens'] ?? 0;

        sendSSE('chunk', [
            'index' => $chunkCount,
            'content' => $chunk['content'],
            'tokens' => $chunk['tokens'] ?? 0
        ]);

        // Flush output immediately
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    });

    // Send completion event
    sendSSE('complete', [
        'conversation_id' => $result['conversation_id'] ?? $conversationId,
        'total_chunks' => $chunkCount,
        'total_tokens' => $totalTokens,
        'sources' => $result['sources'] ?? [],
        'agent_used' => $result['agent'] ?? 'general',
        'processing_time' => $result['processing_time'] ?? 0
    ]);

} catch (Exception $e) {
    error_log("Streaming Error: " . $e->getMessage());
    sendSSE('error', [
        'message' => $e->getMessage(),
        'code' => 'STREAM_ERROR'
    ]);
}

sendSSE('end', ['message' => 'Stream complete']);

/**
 * Send Server-Sent Event
 */
function sendSSE(string $event, array $data): void {
    echo "event: {$event}\n";
    echo "data: " . json_encode($data) . "\n\n";

    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();
}
