<?php
/**
 * Async AI Request API
 *
 * Queue AI requests for background processing
 * Returns job ID immediately, client polls for result
 *
 * POST /assets/services/ai-agent/api/async-chat.php
 *
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../../classes/AsyncQueue.php';
require_once __DIR__ . '/../../../../classes/RedisCache.php';
require_once __DIR__ . '/../lib/Bootstrap.php';

$rid = new_request_id();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        envelope_error('METHOD_NOT_ALLOWED', 'Use POST', $rid, [], 405);
        exit;
    }

    $in = req_json();

    // Validate inputs
    if (empty($in['message']) && empty($in['prompt'])) {
        envelope_error('INVALID_INPUT', 'message or prompt is required', $rid, [], 422);
        exit;
    }

    $prompt = $in['message'] ?? $in['prompt'];
    $provider = $in['provider'] ?? 'openai';
    $model = $in['model'] ?? 'gpt-4o-mini';
    $temperature = $in['temperature'] ?? 0.2;
    $userId = $in['user_id'] ?? null;
    $conversationId = $in['conversation_id'] ?? null;
    $context = $in['context'] ?? null;
    $priority = $in['priority'] ?? 5;

    // Queue the AI request job
    $jobId = AsyncQueue::push('ai-requests', [
        'type' => 'ai-request',
        'prompt' => $prompt,
        'provider' => $provider,
        'model' => $model,
        'temperature' => $temperature,
        'user_id' => $userId,
        'conversation_id' => $conversationId,
        'context' => $context,
        'requested_at' => time()
    ], $priority);

    if ($jobId === null) {
        envelope_error('QUEUE_ERROR', 'Failed to queue job', $rid, [], 500);
        exit;
    }

    // Return job ID immediately
    envelope_success([
        'job_id' => $jobId,
        'status' => 'queued',
        'message' => 'AI request queued for processing',
        'poll_url' => '/assets/services/ai-agent/api/job-status.php?job_id=' . $jobId
    ], $rid, 202); // 202 Accepted

} catch (Throwable $e) {
    error_log('[Async Chat] Error: ' . $e->getMessage());
    envelope_error('EXECUTION_ERROR', $e->getMessage(), $rid, [], 500);
}
