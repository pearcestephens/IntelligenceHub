<?php
/**
 * Bot Execution Service
 *
 * Orchestrates bot execution lifecycle including validation,
 * AI Agent API calls, multi-thread coordination, error handling,
 * retry logic, and execution logging.
 *
 * @package BotDeployment\Services
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Services;

use BotDeployment\Config\Config;
use BotDeployment\Models\Bot;
use BotDeployment\Models\Session;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Repositories\SessionRepository;
use BotDeployment\Database\Connection;

class BotExecutionService
{
    private Config $config;
    private BotRepository $botRepo;
    private SessionRepository $sessionRepo;
    private AIAgentService $aiAgent;
    private MultiThreadService $multiThread;
    private Connection $db;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        ?Config $config = null,
        ?BotRepository $botRepo = null,
        ?SessionRepository $sessionRepo = null,
        ?AIAgentService $aiAgent = null,
        ?MultiThreadService $multiThread = null,
        ?Connection $db = null
    ) {
        $this->config = $config ?? Config::getInstance();
        $this->db = $db ?? Connection::getInstance();
        $this->botRepo = $botRepo ?? new BotRepository($this->db);
        $this->sessionRepo = $sessionRepo ?? new SessionRepository($this->db);
        $this->aiAgent = $aiAgent ?? new AIAgentService($this->config);
        $this->multiThread = $multiThread ?? new MultiThreadService(
            $this->config,
            $this->sessionRepo,
            $this->aiAgent
        );
    }

    /**
     * Execute a bot with given input
     *
     * @param int|Bot $bot           Bot ID or Bot instance
     * @param string  $input         Input/query for the bot
     * @param array   $context       Additional context
     * @param bool    $useMultiThread Whether to use multi-threading
     * @return array Execution result with output and metadata
     * @throws \Exception On execution failure
     */
    public function execute(
        $bot,
        string $input,
        array $context = [],
        bool $useMultiThread = false
    ): array {
        // Load bot if ID provided
        if (is_int($bot)) {
            $bot = $this->botRepo->find($bot);
            if (!$bot) {
                throw new \Exception("Bot not found: {$bot}");
            }
        }

        // Validate bot
        $this->validateBotForExecution($bot);

        // Generate execution ID
        $executionId = $this->generateExecutionId($bot);

        try {
            // Log execution start
            $this->logExecutionStart($executionId, $bot, $input);

            // Execute based on mode
            if ($useMultiThread && $this->shouldUseMultiThread($bot, $input)) {
                $result = $this->executeMultiThreaded($executionId, $bot, $input, $context);
            } else {
                $result = $this->executeSingleThreaded($executionId, $bot, $input, $context);
            }

            // Log execution success
            $this->logExecutionComplete($executionId, $bot, $result);

            // Update bot execution time
            $this->updateBotExecutionTime($bot);

            return [
                'success' => true,
                'execution_id' => $executionId,
                'bot_id' => $bot->getBotId(),
                'bot_name' => $bot->getBotName(),
                'output' => $result['output'],
                'metadata' => $result['metadata'],
                'execution_time' => $result['execution_time'],
                'mode' => $useMultiThread ? 'multi-threaded' : 'single-threaded'
            ];

        } catch (\Exception $e) {
            // Log execution failure
            $this->logExecutionError($executionId, $bot, $e);

            // Rethrow with context
            throw new \Exception(
                "Bot execution failed [{$executionId}]: " . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Execute bot in single-threaded mode
     */
    private function executeSingleThreaded(
        string $executionId,
        Bot $bot,
        string $input,
        array $context
    ): array {
        $startTime = microtime(true);

        // Prepare context with bot metadata
        $fullContext = array_merge($context, [
            'execution_id' => $executionId,
            'bot_metadata' => [
                'bot_id' => $bot->getBotId(),
                'bot_name' => $bot->getBotName(),
                'bot_role' => $bot->getBotRole()
            ]
        ]);

        // Call AI Agent
        $response = $this->aiAgent->query(
            $bot,
            $input,
            $fullContext,
            $this->getToolsForBot($bot),
            true // streaming enabled
        );

        $executionTime = (microtime(true) - $startTime) * 1000;

        return [
            'output' => $response['response'] ?? $response['output'] ?? '',
            'metadata' => [
                'response_time' => $this->aiAgent->getLastResponseTime(),
                'tools_used' => $response['tools_used'] ?? [],
                'conversation_id' => $response['conversation_id'] ?? null
            ],
            'execution_time' => $executionTime
        ];
    }

    /**
     * Execute bot in multi-threaded mode
     */
    private function executeMultiThreaded(
        string $executionId,
        Bot $bot,
        string $input,
        array $context
    ): array {
        $startTime = microtime(true);

        // Determine thread count
        $threadCount = $this->getOptimalThreadCount($bot, $input);

        // Create multi-thread session
        $session = new Session();
        $session->setTopic($input)
                ->setThreadCount($threadCount)
                ->setStatus(Session::STATUS_ACTIVE)
                ->setMetadata([
                    'execution_id' => $executionId,
                    'bot_id' => $bot->getBotId(),
                    'bot_name' => $bot->getBotName()
                ]);

        $sessionId = $this->sessionRepo->create($session);

        try {
            // Execute multi-threaded conversation
            $result = $this->multiThread->executeSession(
                $sessionId,
                $bot,
                $input,
                $context
            );

            $executionTime = (microtime(true) - $startTime) * 1000;

            return [
                'output' => $result['merged_output'],
                'metadata' => [
                    'session_id' => $sessionId,
                    'thread_count' => $threadCount,
                    'threads' => $result['threads'],
                    'merge_strategy' => $result['merge_strategy']
                ],
                'execution_time' => $executionTime
            ];

        } catch (\Exception $e) {
            // Mark session as abandoned
            $this->sessionRepo->abandon($sessionId);
            throw $e;
        }
    }

    /**
     * Validate bot is ready for execution
     *
     * @throws \Exception If bot invalid
     */
    private function validateBotForExecution(Bot $bot): void
    {
        // Check bot is active
        if (!$bot->isActive()) {
            throw new \Exception(
                "Bot '{$bot->getBotName()}' is not active (status: {$bot->getStatus()})"
            );
        }

        // Validate required fields
        if (empty($bot->getSystemPrompt())) {
            throw new \Exception("Bot '{$bot->getBotName()}' has no system prompt");
        }

        // Check rate limits
        $metrics = $this->botRepo->getPerformanceMetrics($bot->getBotId());
        if ($metrics && $this->isRateLimited($metrics)) {
            throw new \Exception("Bot '{$bot->getBotName()}' is rate limited");
        }
    }

    /**
     * Check if bot should use multi-threading
     */
    private function shouldUseMultiThread(Bot $bot, string $input): bool
    {
        // Check if bot is configured for multi-threading
        $multiThreadEnabled = $bot->getConfig('multi_thread_enabled', false);
        if (!$multiThreadEnabled) {
            return false;
        }

        // Check input complexity (simple heuristic)
        $wordCount = str_word_count($input);
        $minWords = $this->config->get('multiThread.minWordsForMultiThread', 50);

        return $wordCount >= $minWords;
    }

    /**
     * Get optimal thread count for execution
     */
    private function getOptimalThreadCount(Bot $bot, string $input): int
    {
        // Get bot preference
        $preferred = $bot->getConfig('preferred_thread_count', 0);
        if ($preferred >= 2 && $preferred <= 6) {
            return $preferred;
        }

        // Calculate based on input complexity
        $wordCount = str_word_count($input);

        if ($wordCount < 100) return 2;
        if ($wordCount < 200) return 3;
        if ($wordCount < 500) return 4;
        if ($wordCount < 1000) return 5;

        return 6; // Maximum
    }

    /**
     * Get MCP tools that bot should use
     */
    private function getToolsForBot(Bot $bot): array
    {
        // Get bot-specific tools from config
        $tools = $bot->getConfig('mcp_tools', []);

        if (!empty($tools)) {
            return $tools;
        }

        // Default tools based on bot role
        $defaultTools = [
            'security' => ['semantic_search', 'fs.read', 'logs.grep', 'ops.security_scan'],
            'developer' => ['semantic_search', 'fs.read', 'db.query', 'git.search'],
            'analyst' => ['db.query', 'semantic_search', 'kb.search'],
            'monitor' => ['logs.tail', 'logs.grep', 'ops.monitoring_snapshot'],
            'general' => ['semantic_search', 'kb.search', 'conversation.search']
        ];

        return $defaultTools[$bot->getBotRole()] ?? $defaultTools['general'];
    }

    /**
     * Check if bot is rate limited
     */
    private function isRateLimited(array $metrics): bool
    {
        $maxExecutionsPerHour = $this->config->get('botExecution.maxExecutionsPerHour', 100);

        $recentExecutions = $metrics['executions_last_hour'] ?? 0;

        return $recentExecutions >= $maxExecutionsPerHour;
    }

    /**
     * Generate unique execution ID
     */
    private function generateExecutionId(Bot $bot): string
    {
        return sprintf(
            'exec_%d_%s_%s',
            $bot->getBotId(),
            date('YmdHis'),
            substr(md5(uniqid(mt_rand(), true)), 0, 8)
        );
    }

    /**
     * Update bot's last execution time
     */
    private function updateBotExecutionTime(Bot $bot): void
    {
        try {
            $this->botRepo->updateExecutionTime($bot->getBotId(), null);
        } catch (\Exception $e) {
            // Non-critical error, just log
            error_log("Failed to update bot execution time: " . $e->getMessage());
        }
    }

    /**
     * Log execution start
     */
    private function logExecutionStart(string $executionId, Bot $bot, string $input): void
    {
        $this->logExecution($executionId, $bot, 'started', [
            'input' => substr($input, 0, 500), // Truncate long inputs
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log execution completion
     */
    private function logExecutionComplete(string $executionId, Bot $bot, array $result): void
    {
        $this->logExecution($executionId, $bot, 'completed', [
            'execution_time' => $result['execution_time'],
            'output_length' => strlen($result['output']),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Log execution error
     */
    private function logExecutionError(string $executionId, Bot $bot, \Exception $e): void
    {
        $this->logExecution($executionId, $bot, 'failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Write execution log to database
     */
    private function logExecution(string $executionId, Bot $bot, string $status, array $data): void
    {
        try {
            $pdo = $this->db->get();

            $stmt = $pdo->prepare("
                INSERT INTO bot_execution_logs
                (execution_id, bot_id, status, data, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    data = VALUES(data),
                    updated_at = NOW()
            ");

            $stmt->execute([
                $executionId,
                $bot->getBotId(),
                $status,
                json_encode($data)
            ]);

            $this->db->release($pdo);

        } catch (\Exception $e) {
            // Non-critical, just log to error log
            error_log("Failed to log execution: " . $e->getMessage());
        }
    }
}
