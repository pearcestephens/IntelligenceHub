#!/usr/bin/env php
<?php
/**
 * Queue Worker - Process background jobs
 *
 * Usage:
 *   php bin/queue-worker.php [queue-name] [--daemon]
 *
 * Examples:
 *   php bin/queue-worker.php ai-requests
 *   php bin/queue-worker.php ai-requests --daemon
 *
 * @package IntelligenceHub
 * @version 2.0.0
 */

declare(strict_types=1);

// Load dependencies
require_once __DIR__ . '/../classes/AsyncQueue.php';
require_once __DIR__ . '/../assets/services/ai-agent/jobs/ai_job_handler.php';

// Parse arguments
$queueName = $argv[1] ?? 'default';
$isDaemon = in_array('--daemon', $argv);

echo "🚀 Starting Queue Worker\n";
echo "   Queue: {$queueName}\n";
echo "   Mode: " . ($isDaemon ? "Daemon" : "Single Run") . "\n";
echo str_repeat('-', 50) . "\n";

// Initialize queue
if (!AsyncQueue::init()) {
    echo "❌ Failed to initialize queue system\n";
    exit(1);
}

// Job handlers registry
$handlers = [
    'ai-request' => function(array $job) {
        echo "🤖 Processing AI request: {$job['id']}\n";

        // Use the real AI job handler
        $result = processAIRequestJob($job);

        if ($result['success']) {
            echo "   ✅ AI request completed in {$result['processing_time_ms']}ms\n";
        } else {
            echo "   ❌ AI request failed: {$result['error']}\n";
        }

        return $result;
    },    'email' => function(array $job) {
        echo "📧 Sending email: {$job['data']['to'] ?? 'unknown'}\n";

        // Email sending logic here
        sleep(1);

        return ['status' => 'sent', 'sent_at' => time()];
    },

    'database-cleanup' => function(array $job) {
        echo "🧹 Running database cleanup\n";

        // Cleanup logic here
        sleep(3);

        return ['status' => 'cleaned', 'records_removed' => 0];
    },

    'cache-warm' => function(array $job) {
        echo "🔥 Warming cache\n";

        // Cache warming logic
        sleep(1);

        return ['status' => 'warmed', 'keys_cached' => 0];
    }
];

// Process jobs
$processedCount = 0;
$errorCount = 0;
$startTime = time();

do {
    $job = AsyncQueue::pop($queueName);

    if ($job === null) {
        if (!$isDaemon) {
            echo "✅ Queue empty - stopping\n";
            break;
        }

        // Wait before checking again
        sleep(1);
        continue;
    }

    try {
        echo "\n📦 Job received: {$job['id']}\n";
        echo "   Type: {$job['data']['type'] ?? 'unknown'}\n";
        echo "   Attempt: {$job['attempts']}/{$job['max_attempts']}\n";

        $jobType = $job['data']['type'] ?? 'unknown';

        if (!isset($handlers[$jobType])) {
            throw new Exception("Unknown job type: {$jobType}");
        }

        // Execute job handler
        $result = $handlers[$jobType]($job);

        // Mark as complete
        AsyncQueue::complete($job['id'], $result);

        $processedCount++;
        echo "   ✅ Completed in " . (time() - $job['started_at']) . "s\n";

    } catch (Exception $e) {
        AsyncQueue::fail($job['id'], $e->getMessage());
        $errorCount++;
        echo "   ❌ Failed: {$e->getMessage()}\n";
    }

    // Show stats every 10 jobs
    if ($processedCount % 10 === 0) {
        $stats = AsyncQueue::getStats($queueName);
        $runtime = time() - $startTime;

        echo "\n📊 Statistics:\n";
        echo "   Runtime: {$runtime}s\n";
        echo "   Processed: {$processedCount}\n";
        echo "   Errors: {$errorCount}\n";
        echo "   Pending: {$stats['pending']}\n";
        echo "   Processing: {$stats['processing']}\n";
        echo str_repeat('-', 50) . "\n";
    }

} while ($isDaemon);

$runtime = time() - $startTime;
echo "\n✅ Worker stopped\n";
echo "   Total processed: {$processedCount}\n";
echo "   Total errors: {$errorCount}\n";
echo "   Runtime: {$runtime}s\n";

exit(0);
