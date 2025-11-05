<?php
/**
 * Company-Wide Bot Deployment Management Center
 *
 * Centralized control panel for managing multiple AI bots with:
 * - Different system prompts
 * - Scheduled tasks (different times)
 * - Role-based assignments
 * - Performance monitoring
 * - Multi-threaded conversations
 *
 * @package IntelligenceHub\BotManagement
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class BotDeploymentCenter {
    private mysqli $db;

    public function __construct() {
        $this->db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');
        if ($this->db->connect_error) {
            throw new Exception("Database connection failed");
        }
        $this->db->set_charset('utf8mb4');
    }

    /**
     * Register a new bot deployment
     */
    public function registerBot(array $config): int {
        $stmt = $this->db->prepare("
            INSERT INTO bot_deployments
            (bot_name, bot_role, system_prompt, schedule_cron, status, config_json, created_at)
            VALUES (?, ?, ?, ?, 'active', ?, NOW())
        ");

        $configJson = json_encode($config);
        $stmt->bind_param('sssss',
            $config['name'],
            $config['role'],
            $config['system_prompt'],
            $config['schedule'],
            $configJson
        );

        $stmt->execute();
        return $this->db->insert_id;
    }

    /**
     * Get all active bot deployments
     */
    public function getActiveBot() {
        $result = $this->db->query("
            SELECT * FROM bot_deployments
            WHERE status = 'active'
            ORDER BY created_at DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Start a multi-threaded conversation session
     */
    public function startMultiThreadSession(string $topic, int $threadCount = 4): array {
        // Create session
        $sessionId = 'mt_' . uniqid();

        $stmt = $this->db->prepare("
            INSERT INTO multi_thread_sessions
            (session_id, topic, thread_count, status, started_at)
            VALUES (?, ?, ?, 'active', NOW())
        ");

        $stmt->bind_param('ssi', $sessionId, $topic, $threadCount);
        $stmt->execute();

        // Create individual threads
        $threads = [];
        for ($i = 1; $i <= $threadCount; $i++) {
            $threadId = $this->createThread($sessionId, $i, $topic);
            $threads[] = $threadId;
        }

        return [
            'session_id' => $sessionId,
            'threads' => $threads,
            'topic' => $topic,
            'thread_count' => $threadCount
        ];
    }

    /**
     * Create individual thread
     */
    private function createThread(string $sessionId, int $threadNumber, string $topic): string {
        $threadId = "{$sessionId}_thread_{$threadNumber}";

        $stmt = $this->db->prepare("
            INSERT INTO conversation_threads
            (thread_id, session_id, thread_number, topic, status, created_at)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->bind_param('ssis', $threadId, $sessionId, $threadNumber, $topic);
        $stmt->execute();

        return $threadId;
    }

    /**
     * Add message to specific thread
     */
    public function addThreadMessage(string $threadId, string $role, string $content, array $metadata = []): void {
        $stmt = $this->db->prepare("
            INSERT INTO thread_messages
            (thread_id, role, content, metadata, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $metadataJson = json_encode($metadata);
        $stmt->bind_param('ssss', $threadId, $role, $content, $metadataJson);
        $stmt->execute();
    }

    /**
     * Get all messages from a thread
     */
    public function getThreadMessages(string $threadId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM thread_messages
            WHERE thread_id = ?
            ORDER BY created_at ASC
        ");

        $stmt->bind_param('s', $threadId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get session summary with all threads
     */
    public function getSessionSummary(string $sessionId): array {
        // Get session details
        $stmt = $this->db->prepare("
            SELECT * FROM multi_thread_sessions WHERE session_id = ?
        ");
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc();

        // Get all threads
        $stmt = $this->db->prepare("
            SELECT t.*, COUNT(m.message_id) as message_count
            FROM conversation_threads t
            LEFT JOIN thread_messages m ON t.thread_id = m.thread_id
            WHERE t.session_id = ?
            GROUP BY t.thread_id
        ");
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'session' => $session,
            'threads' => $threads
        ];
    }

    /**
     * Deploy bot with schedule
     */
    public function deployScheduledBot(array $botConfig): array {
        $botId = $this->registerBot($botConfig);

        // Create cron job
        $cronExpression = $botConfig['schedule'];
        $this->scheduleBotExecution($botId, $cronExpression);

        return [
            'bot_id' => $botId,
            'status' => 'deployed',
            'schedule' => $cronExpression,
            'next_run' => $this->calculateNextRun($cronExpression)
        ];
    }

    /**
     * Schedule bot execution
     */
    private function scheduleBotExecution(int $botId, string $cronExpression): void {
        $stmt = $this->db->prepare("
            INSERT INTO bot_schedules
            (bot_id, cron_expression, status, created_at)
            VALUES (?, ?, 'active', NOW())
        ");

        $stmt->bind_param('is', $botId, $cronExpression);
        $stmt->execute();
    }

    /**
     * Calculate next run time from cron expression
     */
    private function calculateNextRun(string $cronExpression): string {
        // Simple implementation - you can use a cron parser library
        // For now, return estimated time
        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }

    /**
     * Get bot performance metrics
     */
    public function getBotMetrics(int $botId): array {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_executions,
                AVG(execution_time_ms) as avg_execution_time,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_runs,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as failed_runs
            FROM bot_execution_logs
            WHERE bot_id = ?
        ");

        $stmt->bind_param('i', $botId);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
