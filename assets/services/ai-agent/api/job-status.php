<?php
/**
 * Job Status API
 *
 * Check status of queued job and retrieve result when complete
 *
 * GET /assets/services/ai-agent/api/job-status.php?job_id=xxx
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
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        envelope_error('METHOD_NOT_ALLOWED', 'Use GET', $rid, [], 405);
        exit;
    }

    $jobId = $_GET['job_id'] ?? null;

    if (empty($jobId)) {
        envelope_error('INVALID_INPUT', 'job_id is required', $rid, [], 422);
        exit;
    }

    // Get job status from queue
    $job = AsyncQueue::getJob($jobId);

    if ($job === null) {
        envelope_error('NOT_FOUND', 'Job not found', $rid, [], 404);
        exit;
    }

    $response = [
        'job_id' => $jobId,
        'status' => $job['status'],
        'created_at' => $job['created_at'],
        'attempts' => $job['attempts'],
        'max_attempts' => $job['max_attempts']
    ];

    // If completed, include result
    if ($job['status'] === 'completed') {
        $result = RedisCache::get('job:result:' . $jobId);

        if ($result !== null) {
            $response['result'] = $result;
            $response['completed_at'] = $job['completed_at'] ?? time();
        } else {
            $response['result'] = $job['result'] ?? null;
        }
    }

    // If failed, include error
    if ($job['status'] === 'failed') {
        $response['error'] = $job['last_error'] ?? 'Unknown error';
        $response['failed_at'] = $job['failed_at'] ?? time();
    }

    // If processing, include start time
    if ($job['status'] === 'processing') {
        $response['started_at'] = $job['started_at'] ?? null;
        $response['estimated_completion'] = 'Processing...';
    }

    envelope_success($response, $rid);

} catch (Throwable $e) {
    error_log('[Job Status] Error: ' . $e->getMessage());
    envelope_error('EXECUTION_ERROR', $e->getMessage(), $rid, [], 500);
}
