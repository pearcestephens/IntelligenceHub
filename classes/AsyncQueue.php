<?php
/**
 * Async Queue Manager
 * Redis-based queue system for background job processing
 *
 * @package IntelligenceHub
 * @version 2.0.0
 * @author Ecigdis Limited - Intelligence Hub Team
 */

declare(strict_types=1);

require_once __DIR__ . '/RedisCache.php';

class AsyncQueue {
    private const QUEUE_PREFIX = 'queue:';
    private const JOB_PREFIX = 'job:';
    private const PROCESSING_PREFIX = 'processing:';

    private static ?Redis $redis = null;
    private static bool $enabled = false;

    /**
     * Initialize queue system
     */
    public static function init(): bool {
        if (self::$redis !== null) {
            return self::$enabled;
        }

        $connection = RedisCache::getConnection();
        if ($connection === null) {
            error_log('[AsyncQueue] Redis connection not available');
            self::$enabled = false;
            return false;
        }

        self::$redis = $connection;
        self::$enabled = true;
        error_log('[AsyncQueue] ✅ Queue system initialized');

        return true;
    }

    /**
     * Push job to queue
     */
    public static function push(string $queue, array $job, int $priority = 5): ?string {
        if (!self::$enabled && !self::init()) {
            return null;
        }

        try {
            $jobId = uniqid('job_', true);
            $jobData = [
                'id' => $jobId,
                'queue' => $queue,
                'data' => $job,
                'priority' => $priority,
                'attempts' => 0,
                'max_attempts' => 3,
                'created_at' => time(),
                'status' => 'pending'
            ];

            // Store job data
            self::$redis->setex(
                self::JOB_PREFIX . $jobId,
                86400, // 24 hours
                json_encode($jobData)
            );

            // Add to queue with priority score
            self::$redis->zAdd(
                self::QUEUE_PREFIX . $queue,
                $priority,
                $jobId
            );

            error_log("[AsyncQueue] ✅ Job {$jobId} pushed to queue: {$queue}");
            return $jobId;
        } catch (Exception $e) {
            error_log('[AsyncQueue] Push error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Pop job from queue (worker method)
     */
    public static function pop(string $queue): ?array {
        if (!self::$enabled && !self::init()) {
            return null;
        }

        try {
            // Get highest priority job (lowest score)
            $jobs = self::$redis->zRange(
                self::QUEUE_PREFIX . $queue,
                0,
                0
            );

            if (empty($jobs)) {
                return null; // Queue is empty
            }

            $jobId = $jobs[0];

            // Atomic move to processing set
            $moved = self::$redis->zRem(
                self::QUEUE_PREFIX . $queue,
                $jobId
            );

            if (!$moved) {
                return null; // Another worker grabbed it
            }

            // Mark as processing
            self::$redis->sAdd(
                self::PROCESSING_PREFIX . $queue,
                $jobId
            );

            // Get job data
            $jobJson = self::$redis->get(self::JOB_PREFIX . $jobId);
            if (!$jobJson) {
                return null;
            }

            $job = json_decode($jobJson, true);
            $job['status'] = 'processing';
            $job['started_at'] = time();

            // Update job status
            self::$redis->setex(
                self::JOB_PREFIX . $jobId,
                86400,
                json_encode($job)
            );

            return $job;
        } catch (Exception $e) {
            error_log('[AsyncQueue] Pop error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark job as complete
     */
    public static function complete(string $jobId, ?array $result = null): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            $jobJson = self::$redis->get(self::JOB_PREFIX . $jobId);
            if (!$jobJson) {
                return false;
            }

            $job = json_decode($jobJson, true);
            $job['status'] = 'completed';
            $job['completed_at'] = time();
            $job['result'] = $result;

            // Remove from processing
            self::$redis->sRem(
                self::PROCESSING_PREFIX . $job['queue'],
                $jobId
            );

            // Update job data with shorter TTL
            self::$redis->setex(
                self::JOB_PREFIX . $jobId,
                3600, // Keep completed jobs for 1 hour
                json_encode($job)
            );

            error_log("[AsyncQueue] ✅ Job {$jobId} completed");
            return true;
        } catch (Exception $e) {
            error_log('[AsyncQueue] Complete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark job as failed
     */
    public static function fail(string $jobId, string $error): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            $jobJson = self::$redis->get(self::JOB_PREFIX . $jobId);
            if (!$jobJson) {
                return false;
            }

            $job = json_decode($jobJson, true);
            $job['attempts']++;
            $job['last_error'] = $error;
            $job['failed_at'] = time();

            // Retry if under max attempts
            if ($job['attempts'] < $job['max_attempts']) {
                $job['status'] = 'retrying';

                // Remove from processing
                self::$redis->sRem(
                    self::PROCESSING_PREFIX . $job['queue'],
                    $jobId
                );

                // Re-queue with lower priority
                self::$redis->zAdd(
                    self::QUEUE_PREFIX . $job['queue'],
                    $job['priority'] + $job['attempts'],
                    $jobId
                );

                error_log("[AsyncQueue] ⚠️ Job {$jobId} retry {$job['attempts']}/{$job['max_attempts']}");
            } else {
                $job['status'] = 'failed';

                // Remove from processing
                self::$redis->sRem(
                    self::PROCESSING_PREFIX . $job['queue'],
                    $jobId
                );

                error_log("[AsyncQueue] ❌ Job {$jobId} failed permanently: {$error}");
            }

            // Update job data
            self::$redis->setex(
                self::JOB_PREFIX . $jobId,
                86400,
                json_encode($job)
            );

            return true;
        } catch (Exception $e) {
            error_log('[AsyncQueue] Fail error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get job status
     */
    public static function getJob(string $jobId): ?array {
        if (!self::$enabled && !self::init()) {
            return null;
        }

        try {
            $jobJson = self::$redis->get(self::JOB_PREFIX . $jobId);
            if (!$jobJson) {
                return null;
            }

            return json_decode($jobJson, true);
        } catch (Exception $e) {
            error_log('[AsyncQueue] GetJob error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get queue stats
     */
    public static function getStats(string $queue): array {
        if (!self::$enabled && !self::init()) {
            return ['error' => 'Queue not enabled'];
        }

        try {
            $pending = self::$redis->zCard(self::QUEUE_PREFIX . $queue);
            $processing = self::$redis->sCard(self::PROCESSING_PREFIX . $queue);

            return [
                'queue' => $queue,
                'pending' => $pending,
                'processing' => $processing,
                'total' => $pending + $processing
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Clear queue
     */
    public static function clear(string $queue): bool {
        if (!self::$enabled && !self::init()) {
            return false;
        }

        try {
            self::$redis->del(self::QUEUE_PREFIX . $queue);
            self::$redis->del(self::PROCESSING_PREFIX . $queue);
            error_log("[AsyncQueue] ✅ Queue {$queue} cleared");
            return true;
        } catch (Exception $e) {
            error_log('[AsyncQueue] Clear error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * List all queues
     */
    public static function listQueues(): array {
        if (!self::$enabled && !self::init()) {
            return [];
        }

        try {
            $keys = self::$redis->keys(self::QUEUE_PREFIX . '*');
            $queues = [];

            foreach ($keys as $key) {
                $queueName = str_replace(self::QUEUE_PREFIX, '', $key);
                $queues[] = $queueName;
            }

            return $queues;
        } catch (Exception $e) {
            error_log('[AsyncQueue] ListQueues error: ' . $e->getMessage());
            return [];
        }
    }
}

// Auto-initialize
AsyncQueue::init();
