<?php
/**
 * Background Job Handler for AI Requests
 *
 * This script processes AI requests from the async queue
 * Can be called directly or via queue worker
 *
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../classes/AsyncQueue.php';
require_once __DIR__ . '/../../classes/RedisCache.php';
require_once __DIR__ . '/../lib/Bootstrap.php';
require_once __DIR__ . '/../lib/ProviderFactory.php';

/**
 * Process AI request job
 *
 * @param array $job Job data from queue
 * @return array Processing result
 */
function processAIRequestJob(array $job): array {
    $startTime = microtime(true);

    try {
        $data = $job['data'] ?? [];

        if (empty($data['prompt'])) {
            throw new Exception('Prompt is required');
        }

        $prompt = $data['prompt'];
        $provider = $data['provider'] ?? 'openai';
        $model = $data['model'] ?? 'gpt-4o-mini';
        $temperature = $data['temperature'] ?? 0.2;
        $userId = $data['user_id'] ?? null;
        $conversationId = $data['conversation_id'] ?? null;

        // Build messages
        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful AI assistant.']
        ];

        if (!empty($data['context'])) {
            $messages[] = ['role' => 'system', 'content' => 'Context: ' . $data['context']];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Call AI provider
        $openaiKey = env('OPENAI_API_KEY');
        $anthropicKey = env('ANTHROPIC_API_KEY');

        $response = null;
        $latencyMs = 0;

        if ($provider === 'openai') {
            if (!$openaiKey) {
                throw new RuntimeException('OPENAI_API_KEY not configured');
            }

            $call = ProviderFactory::openai($openaiKey);
            $response = $call([
                'apiKey' => $openaiKey,
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature
            ]);

            $latencyMs = (int)($response['_latency_ms'] ?? 0);
            $content = (string)($response['choices'][0]['message']['content'] ?? '');

        } else if ($provider === 'anthropic') {
            if (!$anthropicKey) {
                throw new RuntimeException('ANTHROPIC_API_KEY not configured');
            }

            $anthropicMessages = ProviderFactory::toAnthropicMessages($messages);
            $call = ProviderFactory::anthropic($anthropicKey);
            $response = $call([
                'apiKey' => $anthropicKey,
                'model' => $model,
                'system' => $messages[0]['content'],
                'messages' => $anthropicMessages,
                'temperature' => $temperature
            ]);

            $latencyMs = (int)($response['_latency_ms'] ?? 0);
            $content = (string)($response['content'][0]['text'] ?? '');
        } else {
            throw new Exception('Invalid provider: ' . $provider);
        }

        $processingTime = (int)((microtime(true) - $startTime) * 1000);

        // Store result in Redis for retrieval
        if (!empty($job['id'])) {
            RedisCache::set('job:result:' . $job['id'], [
                'content' => $content,
                'latency_ms' => $latencyMs,
                'processing_time_ms' => $processingTime,
                'provider' => $provider,
                'model' => $model,
                'completed_at' => time()
            ], 3600); // Keep for 1 hour
        }

        return [
            'success' => true,
            'content' => $content,
            'latency_ms' => $latencyMs,
            'processing_time_ms' => $processingTime
        ];

    } catch (Exception $e) {
        error_log('[AI Job Handler] Error: ' . $e->getMessage());

        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// If called directly (not included), process a single job from queue
if (basename($_SERVER['SCRIPT_FILENAME']) === 'ai_job_handler.php') {
    $job = AsyncQueue::pop('ai-requests');

    if ($job === null) {
        echo json_encode(['status' => 'no_jobs', 'message' => 'Queue is empty']);
        exit;
    }

    echo "Processing job: {$job['id']}\n";

    $result = processAIRequestJob($job);

    if ($result['success']) {
        AsyncQueue::complete($job['id'], $result);
        echo "Job completed successfully\n";
    } else {
        AsyncQueue::fail($job['id'], $result['error']);
        echo "Job failed: {$result['error']}\n";
    }

    echo json_encode($result);
}
