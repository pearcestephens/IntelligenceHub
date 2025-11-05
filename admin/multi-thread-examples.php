<?php
/**
 * Multi-Threaded Conversation Example
 * Demonstrates how to use 4 different bot threads simultaneously
 *
 * This example shows a complete workflow for analyzing a complex task
 * using multiple specialized AI agents working in parallel.
 */

require_once __DIR__ . '/../api/bot-deployment-api.php';

class MultiThreadExample {
    private BotDeploymentCenterAPI $api;

    public function __construct() {
        $this->api = new BotDeploymentCenterAPI();
    }

    /**
     * EXAMPLE 1: Analyze Consignment Transfer Flow
     * Uses 4 threads with different bot specializations
     */
    public function example1_AnalyzeConsignmentFlow() {
        echo "=== EXAMPLE 1: Multi-Thread Consignment Analysis ===\n\n";

        // Start a 4-thread session
        $sessionId = 'session_' . bin2hex(random_bytes(8));
        $topic = "Analyze and optimize the consignment transfer flow";

        echo "Starting session: $sessionId\n";
        echo "Topic: $topic\n\n";

        // Create the session
        $session = $this->api->startMultiThreadSession([
            'session_id' => $sessionId,
            'topic' => $topic,
            'thread_count' => 4
        ]);

        // Define tasks for each thread
        $threads = [
            1 => [
                'bot_role' => 'security',
                'task' => 'Review security aspects: authentication, authorization, data validation in consignment transfers'
            ],
            2 => [
                'bot_role' => 'database',
                'task' => 'Analyze database queries and optimization opportunities in transfer processing'
            ],
            3 => [
                'bot_role' => 'api',
                'task' => 'Review API endpoints, request/response formats, and error handling for transfers'
            ],
            4 => [
                'bot_role' => 'frontend',
                'task' => 'Evaluate user interface and UX for transfer creation and management'
            ]
        ];

        // Execute each thread
        foreach ($threads as $threadNum => $config) {
            $threadId = $sessionId . '_thread_' . $threadNum;

            echo "Thread $threadNum: {$config['bot_role']} - {$config['task']}\n";

            $this->api->sendMessageToThread([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'thread_number' => $threadNum,
                'bot_role' => $config['bot_role'],
                'message' => $config['task']
            ]);
        }

        // Monitor progress
        echo "\nMonitoring threads...\n\n";
        $this->monitorSessionProgress($sessionId, 4);

        // Merge results
        echo "\nMerging thread results...\n";
        $results = $this->api->mergeThreadResults($sessionId);

        echo "\n=== CONSOLIDATED ANALYSIS ===\n";
        echo json_encode($results, JSON_PRETTY_PRINT);

        return $results;
    }

    /**
     * EXAMPLE 2: Code Review with Multiple Perspectives
     * Each thread reviews different aspects of the same code
     */
    public function example2_MultiPerspectiveCodeReview($codeFile) {
        echo "=== EXAMPLE 2: Multi-Perspective Code Review ===\n\n";

        $sessionId = 'review_' . bin2hex(random_bytes(8));
        $code = file_get_contents($codeFile);

        $session = $this->api->startMultiThreadSession([
            'session_id' => $sessionId,
            'topic' => "Comprehensive code review of $codeFile",
            'thread_count' => 4
        ]);

        $perspectives = [
            1 => [
                'role' => 'security',
                'focus' => 'Security vulnerabilities, injection risks, authentication flaws'
            ],
            2 => [
                'role' => 'architect',
                'focus' => 'Architecture patterns, SOLID principles, code organization'
            ],
            3 => [
                'role' => 'database',
                'focus' => 'Query optimization, N+1 problems, transaction safety'
            ],
            4 => [
                'role' => 'qa',
                'focus' => 'Testability, edge cases, error handling'
            ]
        ];

        foreach ($perspectives as $threadNum => $perspective) {
            $threadId = $sessionId . '_thread_' . $threadNum;
            $prompt = "Review this code from a {$perspective['focus']} perspective:\n\n```php\n$code\n```";

            $this->api->sendMessageToThread([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'thread_number' => $threadNum,
                'bot_role' => $perspective['role'],
                'message' => $prompt
            ]);
        }

        $this->monitorSessionProgress($sessionId, 4);
        return $this->api->mergeThreadResults($sessionId);
    }

    /**
     * EXAMPLE 3: Parallel Data Processing
     * Divide a large dataset across multiple threads
     */
    public function example3_ParallelDataProcessing() {
        echo "=== EXAMPLE 3: Parallel Data Processing ===\n\n";

        $sessionId = 'data_proc_' . bin2hex(random_bytes(8));

        $session = $this->api->startMultiThreadSession([
            'session_id' => $sessionId,
            'topic' => 'Process quarterly sales data across multiple outlets',
            'thread_count' => 4
        ]);

        // Divide outlets into chunks
        $outlets = range(1, 17); // Your 17 stores
        $chunks = array_chunk($outlets, 5); // Process ~4-5 stores per thread

        foreach ($chunks as $threadNum => $outletChunk) {
            $threadId = $sessionId . '_thread_' . ($threadNum + 1);
            $outletList = implode(', ', $outletChunk);

            $this->api->sendMessageToThread([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'thread_number' => $threadNum + 1,
                'bot_role' => 'database',
                'message' => "Analyze quarterly sales trends for outlets: $outletList"
            ]);
        }

        $this->monitorSessionProgress($sessionId, count($chunks));
        return $this->api->mergeThreadResults($sessionId);
    }

    /**
     * EXAMPLE 4: Real-Time Collaborative Problem Solving
     * Bots discuss and build on each other's suggestions
     */
    public function example4_CollaborativeProblemSolving($problem) {
        echo "=== EXAMPLE 4: Collaborative Problem Solving ===\n\n";

        $sessionId = 'collab_' . bin2hex(random_bytes(8));

        $session = $this->api->startMultiThreadSession([
            'session_id' => $sessionId,
            'topic' => $problem,
            'thread_count' => 4,
            'collaborative' => true // Threads can see each other's messages
        ]);

        $roles = ['architect', 'security', 'database', 'frontend'];

        // Round 1: Initial analysis
        echo "Round 1: Initial Analysis\n";
        foreach ($roles as $threadNum => $role) {
            $threadId = $sessionId . '_thread_' . ($threadNum + 1);

            $this->api->sendMessageToThread([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'thread_number' => $threadNum + 1,
                'bot_role' => $role,
                'message' => "From your perspective as a $role expert, analyze: $problem"
            ]);
        }

        sleep(5); // Wait for responses

        // Round 2: Build on each other's ideas
        echo "\nRound 2: Collaborative Refinement\n";
        $previousResponses = $this->api->getThreadResponses($sessionId);

        foreach ($roles as $threadNum => $role) {
            $threadId = $sessionId . '_thread_' . ($threadNum + 1);
            $others = array_diff($roles, [$role]);

            $this->api->sendMessageToThread([
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'thread_number' => $threadNum + 1,
                'bot_role' => $role,
                'message' => "Review the suggestions from " . implode(', ', $others) .
                            ". Add your thoughts and identify the best combined approach."
            ]);
        }

        $this->monitorSessionProgress($sessionId, 4);
        return $this->api->mergeThreadResults($sessionId);
    }

    /**
     * Monitor session progress and display status
     */
    private function monitorSessionProgress($sessionId, $expectedThreads) {
        $maxAttempts = 30; // 30 attempts x 2 seconds = 60 seconds max
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $status = $this->api->getSessionStatus(['session_id' => $sessionId]);
            $completed = $status['data']['completed_threads'] ?? 0;
            $progress = round(($completed / $expectedThreads) * 100);

            echo "\rProgress: $progress% ($completed/$expectedThreads threads completed)";

            if ($status['data']['status'] === 'completed') {
                echo "\n✓ Session completed!\n";
                break;
            }

            sleep(2);
            $attempt++;
        }

        if ($attempt >= $maxAttempts) {
            echo "\n⚠ Session timeout - not all threads completed\n";
        }
    }
}

// ============================================================================
// USAGE EXAMPLES
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════════╗\n";
    echo "║                    MULTI-THREADED BOT EXAMPLES                       ║\n";
    echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

    $examples = new MultiThreadExample();

    // Menu
    echo "Select an example to run:\n\n";
    echo "1. Analyze Consignment Transfer Flow (4 specialized bots)\n";
    echo "2. Multi-Perspective Code Review\n";
    echo "3. Parallel Data Processing (17 outlets)\n";
    echo "4. Collaborative Problem Solving\n";
    echo "5. Run All Examples\n";
    echo "\nEnter choice (1-5): ";

    $choice = trim(fgets(STDIN));

    try {
        switch ($choice) {
            case '1':
                $examples->example1_AnalyzeConsignmentFlow();
                break;
            case '2':
                echo "Enter file path to review: ";
                $file = trim(fgets(STDIN));
                $examples->example2_MultiPerspectiveCodeReview($file);
                break;
            case '3':
                $examples->example3_ParallelDataProcessing();
                break;
            case '4':
                echo "Enter problem to solve: ";
                $problem = trim(fgets(STDIN));
                $examples->example4_CollaborativeProblemSolving($problem);
                break;
            case '5':
                $examples->example1_AnalyzeConsignmentFlow();
                echo "\n" . str_repeat("=", 80) . "\n\n";
                $examples->example3_ParallelDataProcessing();
                break;
            default:
                echo "Invalid choice\n";
        }
    } catch (Exception $e) {
        echo "\nError: " . $e->getMessage() . "\n";
    }

    echo "\n\n";
    echo "╔══════════════════════════════════════════════════════════════════════╗\n";
    echo "║                         EXAMPLES COMPLETE                            ║\n";
    echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";
}

// ============================================================================
// WEB API USAGE
// ============================================================================

/**
 * Example API calls from JavaScript/Frontend:
 *
 * // Start a multi-thread session
 * fetch('/api/bot-deployment-api.php', {
 *     method: 'POST',
 *     headers: {
 *         'Content-Type': 'application/json',
 *         'Authorization': 'Bearer YOUR_API_KEY'
 *     },
 *     body: JSON.stringify({
 *         action: 'startMultiThread',
 *         topic: 'Analyze consignment flow',
 *         thread_count: 4,
 *         bot_assignments: {
 *             1: 1, // Bot ID 1 for thread 1
 *             2: 2, // Bot ID 2 for thread 2
 *             3: 3, // etc.
 *             4: 4
 *         }
 *     })
 * })
 * .then(res => res.json())
 * .then(data => {
 *     console.log('Session started:', data.data.session_id);
 *
 *     // Monitor progress
 *     const monitor = setInterval(() => {
 *         fetch('/api/bot-deployment-api.php', {
 *             method: 'POST',
 *             headers: {
 *                 'Content-Type': 'application/json',
 *                 'Authorization': 'Bearer YOUR_API_KEY'
 *             },
 *             body: JSON.stringify({
 *                 action: 'getSessionStatus',
 *                 session_id: data.data.session_id
 *             })
 *         })
 *         .then(res => res.json())
 *         .then(status => {
 *             console.log('Progress:', status.data.completed_threads + '/' + status.data.thread_count);
 *
 *             if (status.data.status === 'completed') {
 *                 clearInterval(monitor);
 *                 console.log('Session complete!');
 *             }
 *         });
 *     }, 2000);
 * });
 */
