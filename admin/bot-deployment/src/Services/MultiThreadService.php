<?php
/**
 * Multi-Thread Service
 *
 * Manages parallel conversation threads for complex bot tasks.
 * Distributes work across 2-6 threads, collects results, and merges
 * outputs using intelligent strategies.
 *
 * @package BotDeployment\Services
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Services;

use BotDeployment\Config\Config;
use BotDeployment\Models\Bot;
use BotDeployment\Models\Session;
use BotDeployment\Repositories\SessionRepository;
use BotDeployment\Database\Connection;

class MultiThreadService
{
    private Config $config;
    private SessionRepository $sessionRepo;
    private AIAgentService $aiAgent;
    private Connection $db;

    /**
     * Constructor
     */
    public function __construct(
        ?Config $config = null,
        ?SessionRepository $sessionRepo = null,
        ?AIAgentService $aiAgent = null,
        ?Connection $db = null
    ) {
        $this->config = $config ?? Config::getInstance();
        $this->db = $db ?? Connection::getInstance();
        $this->sessionRepo = $sessionRepo ?? new SessionRepository($this->db);
        $this->aiAgent = $aiAgent ?? new AIAgentService($this->config);
    }

    /**
     * Execute a multi-threaded session
     *
     * @param int    $sessionId Session ID
     * @param Bot    $bot       Bot to execute
     * @param string $input     Input query
     * @param array  $context   Additional context
     * @return array Execution results with merged output
     * @throws \Exception On execution failure
     */
    public function executeSession(
        int $sessionId,
        Bot $bot,
        string $input,
        array $context = []
    ): array {
        // Load session
        $session = $this->sessionRepo->find($sessionId);
        if (!$session) {
            throw new \Exception("Session not found: {$sessionId}");
        }

        // Validate session
        if (!$session->isActive()) {
            throw new \Exception("Session is not active: {$sessionId}");
        }

        try {
            // Decompose input into thread tasks
            $threadTasks = $this->decomposeInput($input, $session->getThreadCount());

            // Execute threads in parallel (simulated)
            $threadResults = $this->executeThreads($session, $bot, $threadTasks, $context);

            // Merge results
            $mergedOutput = $this->mergeThreadResults($threadResults, $input);

            // Store merge history
            $this->storeMergeHistory($sessionId, $threadResults, $mergedOutput);

            // Mark session complete
            $this->sessionRepo->complete($sessionId);

            return [
                'merged_output' => $mergedOutput['output'],
                'merge_strategy' => $mergedOutput['strategy'],
                'threads' => array_map(function($result) {
                    return [
                        'thread_id' => $result['thread_id'],
                        'status' => $result['status'],
                        'execution_time' => $result['execution_time'],
                        'output_length' => strlen($result['output'])
                    ];
                }, $threadResults)
            ];

        } catch (\Exception $e) {
            // Mark session as abandoned
            $this->sessionRepo->abandon($sessionId);
            throw $e;
        }
    }

    /**
     * Decompose input into thread-specific tasks
     */
    private function decomposeInput(string $input, int $threadCount): array
    {
        $tasks = [];

        // Strategy: Break into perspectives/approaches
        $perspectives = [
            'Analyze from a technical implementation perspective',
            'Analyze from a security and safety perspective',
            'Analyze from a performance optimization perspective',
            'Analyze from a user experience perspective',
            'Analyze from a maintainability perspective',
            'Analyze from a scalability perspective'
        ];

        for ($i = 0; $i < $threadCount; $i++) {
            $perspective = $perspectives[$i] ?? 'Analyze comprehensively';

            $tasks[] = [
                'thread_id' => $i + 1,
                'perspective' => $perspective,
                'input' => $input,
                'prompt' => $this->buildThreadPrompt($input, $perspective, $i + 1, $threadCount)
            ];
        }

        return $tasks;
    }

    /**
     * Build thread-specific prompt
     */
    private function buildThreadPrompt(
        string $input,
        string $perspective,
        int $threadNum,
        int $totalThreads
    ): string {
        return <<<PROMPT
You are Thread {$threadNum} of {$totalThreads} in a multi-threaded analysis session.

YOUR SPECIFIC PERSPECTIVE: {$perspective}

ORIGINAL QUERY:
{$input}

YOUR TASK:
Analyze the query from YOUR specific perspective only. Be thorough but focused.
Your output will be merged with other threads, so:
- Be concise but complete
- Focus on your unique perspective
- Avoid redundancy with general knowledge
- Provide actionable insights specific to your angle

Begin your analysis:
PROMPT;
    }

    /**
     * Execute threads (parallel simulation using sequential calls)
     * In production, this could use actual threading or queue workers
     */
    private function executeThreads(
        Session $session,
        Bot $bot,
        array $threadTasks,
        array $context
    ): array {
        $results = [];
        $sessionId = $session->getSessionId();

        foreach ($threadTasks as $task) {
            try {
                $threadId = $task['thread_id'];
                $startTime = microtime(true);

                // Store thread start in database
                $conversationThreadId = $this->createConversationThread(
                    $sessionId,
                    $threadId,
                    $task['perspective']
                );

                // Execute thread
                $response = $this->aiAgent->query(
                    $bot,
                    $task['prompt'],
                    array_merge($context, [
                        'session_id' => $sessionId,
                        'thread_id' => $threadId,
                        'perspective' => $task['perspective']
                    ]),
                    $this->getThreadTools($task['perspective']),
                    true // streaming
                );

                $executionTime = (microtime(true) - $startTime) * 1000;

                // Store messages
                $this->storeThreadMessages(
                    $conversationThreadId,
                    $task['prompt'],
                    $response['response'] ?? $response['output'] ?? ''
                );

                $results[] = [
                    'thread_id' => $threadId,
                    'conversation_thread_id' => $conversationThreadId,
                    'perspective' => $task['perspective'],
                    'output' => $response['response'] ?? $response['output'] ?? '',
                    'metadata' => $response['metadata'] ?? [],
                    'execution_time' => $executionTime,
                    'status' => 'completed'
                ];

            } catch (\Exception $e) {
                // Thread failed, but continue with others
                $results[] = [
                    'thread_id' => $task['thread_id'],
                    'conversation_thread_id' => null,
                    'perspective' => $task['perspective'],
                    'output' => '',
                    'error' => $e->getMessage(),
                    'execution_time' => 0,
                    'status' => 'failed'
                ];
            }
        }

        return $results;
    }

    /**
     * Merge thread results into coherent output
     */
    private function mergeThreadResults(array $threadResults, string $originalInput): array
    {
        // Filter successful threads
        $successfulThreads = array_filter(
            $threadResults,
            fn($r) => $r['status'] === 'completed' && !empty($r['output'])
        );

        if (empty($successfulThreads)) {
            throw new \Exception("All threads failed");
        }

        // Choose merge strategy based on number of successful threads
        $count = count($successfulThreads);

        if ($count === 1) {
            $strategy = 'single';
            $output = $successfulThreads[0]['output'];
        } elseif ($count <= 3) {
            $strategy = 'sequential';
            $output = $this->mergeSequential($successfulThreads, $originalInput);
        } else {
            $strategy = 'synthesized';
            $output = $this->mergeSynthesized($successfulThreads, $originalInput);
        }

        return [
            'output' => $output,
            'strategy' => $strategy,
            'threads_used' => $count,
            'threads_failed' => count($threadResults) - $count
        ];
    }

    /**
     * Merge results sequentially (for 2-3 threads)
     */
    private function mergeSequential(array $threads, string $originalInput): string
    {
        $sections = [];

        foreach ($threads as $thread) {
            $sections[] = "## {$thread['perspective']}\n\n{$thread['output']}";
        }

        $merged = "# Multi-Perspective Analysis\n\n";
        $merged .= "**Original Query:** {$originalInput}\n\n";
        $merged .= implode("\n\n---\n\n", $sections);
        $merged .= "\n\n## Summary\n\n";
        $merged .= "The above analysis covers multiple perspectives to provide a comprehensive view.";

        return $merged;
    }

    /**
     * Merge results with AI synthesis (for 4-6 threads)
     */
    private function mergeSynthesized(array $threads, string $originalInput): string
    {
        // In a real implementation, this would call the AI Agent again
        // to synthesize the multiple thread outputs into a coherent response.
        // For now, we'll use a structured merge.

        $perspectives = [];
        $keyPoints = [];

        foreach ($threads as $thread) {
            $perspectives[] = "**{$thread['perspective']}:**\n{$thread['output']}";

            // Extract first paragraph as key point (simplified)
            $lines = explode("\n", trim($thread['output']));
            $keyPoints[] = trim($lines[0]);
        }

        $merged = "# Comprehensive Multi-Threaded Analysis\n\n";
        $merged .= "**Query:** {$originalInput}\n\n";
        $merged .= "## Executive Summary\n\n";
        $merged .= "This analysis synthesizes insights from {$this->countThreads($threads)} parallel analysis threads:\n\n";

        foreach ($keyPoints as $i => $point) {
            $merged .= ($i + 1) . ". " . $point . "\n";
        }

        $merged .= "\n## Detailed Analysis by Perspective\n\n";
        $merged .= implode("\n\n", $perspectives);

        $merged .= "\n\n## Integrated Recommendations\n\n";
        $merged .= "Based on the multi-perspective analysis above, here are the integrated recommendations:\n";
        $merged .= "- Consider all perspectives when implementing\n";
        $merged .= "- Balance trade-offs identified across threads\n";
        $merged .= "- Prioritize based on your specific context and constraints\n";

        return $merged;
    }

    /**
     * Get appropriate MCP tools for thread perspective
     */
    private function getThreadTools(string $perspective): array
    {
        $toolMap = [
            'technical' => ['semantic_search', 'fs.read', 'db.query'],
            'security' => ['semantic_search', 'ops.security_scan', 'logs.grep'],
            'performance' => ['db.explain', 'ops.monitoring_snapshot', 'semantic_search'],
            'user experience' => ['kb.search', 'semantic_search'],
            'maintainability' => ['semantic_search', 'fs.read', 'git.search'],
            'scalability' => ['db.query', 'ops.monitoring_snapshot', 'semantic_search']
        ];

        foreach ($toolMap as $key => $tools) {
            if (stripos($perspective, $key) !== false) {
                return $tools;
            }
        }

        return ['semantic_search', 'kb.search', 'db.query'];
    }

    /**
     * Create conversation thread record
     */
    private function createConversationThread(
        int $sessionId,
        int $threadId,
        string $perspective
    ): int {
        $pdo = $this->db->get();

        $stmt = $pdo->prepare("
            INSERT INTO conversation_threads
            (session_id, thread_number, perspective, status, created_at)
            VALUES (?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([$sessionId, $threadId, $perspective]);
        $conversationThreadId = (int) $pdo->lastInsertId();

        $this->db->release($pdo);

        return $conversationThreadId;
    }

    /**
     * Store thread messages
     */
    private function storeThreadMessages(
        int $conversationThreadId,
        string $userMessage,
        string $assistantMessage
    ): void {
        $pdo = $this->db->get();

        // Store user message
        $stmt = $pdo->prepare("
            INSERT INTO thread_messages
            (thread_id, role, content, created_at)
            VALUES (?, 'user', ?, NOW())
        ");
        $stmt->execute([$conversationThreadId, $userMessage]);

        // Store assistant message
        $stmt = $pdo->prepare("
            INSERT INTO thread_messages
            (thread_id, role, content, created_at)
            VALUES (?, 'assistant', ?, NOW())
        ");
        $stmt->execute([$conversationThreadId, $assistantMessage]);

        $this->db->release($pdo);
    }

    /**
     * Store merge history
     */
    private function storeMergeHistory(
        int $sessionId,
        array $threadResults,
        array $mergedOutput
    ): void {
        $pdo = $this->db->get();

        $stmt = $pdo->prepare("
            INSERT INTO thread_merge_history
            (session_id, strategy_used, thread_outputs, merged_output, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $sessionId,
            $mergedOutput['strategy'],
            json_encode($threadResults),
            $mergedOutput['output']
        ]);

        $this->db->release($pdo);
    }

    /**
     * Count successful threads
     */
    private function countThreads(array $threads): int
    {
        return count($threads);
    }
}
