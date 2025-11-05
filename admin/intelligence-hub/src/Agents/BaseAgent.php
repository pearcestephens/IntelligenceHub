<?php

namespace IntelligenceHub\Agents;

use IntelligenceHub\Config\Connection;
use IntelligenceHub\Services\Logger;
use IntelligenceHub\AI\DecisionEngine;
use Exception;

/**
 * Base Agent Class
 *
 * Abstract base for all specialized AI agents in the Intelligence Hub.
 * Provides common functionality for task execution, communication,
 * monitoring, and decision-making.
 */
abstract class BaseAgent
{
    protected $logger;
    protected $db;
    protected $aiEngine;
    protected $agentId;
    protected $agentName;
    protected $status = 'idle';
    protected $capabilities = [];

    // Agent states
    const STATUS_IDLE = 'idle';
    const STATUS_ACTIVE = 'active';
    const STATUS_BUSY = 'busy';
    const STATUS_ERROR = 'error';
    const STATUS_DISABLED = 'disabled';

    // Task priorities
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;
    const PRIORITY_CRITICAL = 5;

    public function __construct()
    {
        $this->logger = new Logger(get_class($this));
        $this->db = Connection::getInstance();
        $this->aiEngine = new DecisionEngine();
        $this->agentName = $this->getAgentName();

        $this->initialize();
        $this->registerAgent();
    }

    /**
     * Get agent name (must be implemented by child classes)
     */
    abstract protected function getAgentName(): string;

    /**
     * Initialize agent-specific settings
     */
    abstract protected function initialize(): void;

    /**
     * Execute a task (must be implemented by child classes)
     *
     * @param array $task Task details
     * @return array Execution result
     */
    abstract public function executeTask(array $task): array;

    /**
     * Get agent capabilities
     */
    abstract public function getCapabilities(): array;

    /**
     * Register agent in the system
     */
    protected function registerAgent(): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO agents (name, class, capabilities, status, last_heartbeat)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    class = VALUES(class),
                    capabilities = VALUES(capabilities),
                    status = VALUES(status),
                    last_heartbeat = NOW()
            ");

            $stmt->execute([
                $this->agentName,
                get_class($this),
                json_encode($this->getCapabilities()),
                self::STATUS_IDLE
            ]);

            $this->agentId = $this->db->lastInsertId() ?: $this->getAgentId();

            $this->logger->info("Agent registered", ['agent_id' => $this->agentId]);

        } catch (Exception $e) {
            $this->logger->error("Failed to register agent", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get agent ID from database
     */
    private function getAgentId(): ?int
    {
        try {
            $stmt = $this->db->prepare("SELECT id FROM agents WHERE name = ?");
            $stmt->execute([$this->agentName]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Send heartbeat to indicate agent is alive
     */
    public function heartbeat(): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE agents
                SET last_heartbeat = NOW(), status = ?
                WHERE id = ?
            ");

            $stmt->execute([$this->status, $this->agentId]);

        } catch (Exception $e) {
            $this->logger->error("Heartbeat failed", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update agent status
     */
    protected function setStatus(string $status): void
    {
        $this->status = $status;
        $this->heartbeat();
    }

    /**
     * Process task with full lifecycle management
     *
     * @param array $task Task to process
     * @return array Result
     */
    public function processTask(array $task): array
    {
        $taskId = $task['id'] ?? uniqid('task_');

        try {
            $this->logger->info("Processing task", ['task_id' => $taskId, 'task' => $task]);

            $this->setStatus(self::STATUS_BUSY);

            // Record task start
            $this->recordTaskStart($taskId, $task);

            // Execute task (implemented by child class)
            $result = $this->executeTask($task);

            // Record task completion
            $this->recordTaskComplete($taskId, $result);

            $this->setStatus(self::STATUS_IDLE);

            return [
                'success' => true,
                'task_id' => $taskId,
                'result' => $result,
                'agent' => $this->agentName,
                'completed_at' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            $this->logger->error("Task execution failed", [
                'task_id' => $taskId,
                'error' => $e->getMessage()
            ]);

            $this->recordTaskError($taskId, $e->getMessage());
            $this->setStatus(self::STATUS_ERROR);

            return [
                'success' => false,
                'task_id' => $taskId,
                'error' => $e->getMessage(),
                'agent' => $this->agentName
            ];
        }
    }

    /**
     * Request AI decision
     *
     * @param string $situation Description of situation
     * @param array $options Available options
     * @param array $data Relevant data
     * @return array Decision result
     */
    protected function requestDecision(string $situation, array $options, array $data = []): array
    {
        return $this->aiEngine->makeDecision($situation, $options, $data);
    }

    /**
     * Analyze data using AI
     *
     * @param array $data Data to analyze
     * @param string $type Analysis type
     * @return array Analysis result
     */
    protected function analyzeWithAI(array $data, string $type): array
    {
        return $this->aiEngine->analyzeData($data, $type);
    }

    /**
     * Send message to another agent
     *
     * @param string $targetAgent Target agent name
     * @param string $action Action to perform
     * @param array $data Action data
     * @return bool Success
     */
    protected function sendToAgent(string $targetAgent, string $action, array $data = []): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO agent_messages (from_agent, to_agent, action, data, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $this->agentName,
                $targetAgent,
                $action,
                json_encode($data)
            ]);

            $this->logger->info("Message sent to agent", [
                'to' => $targetAgent,
                'action' => $action
            ]);

            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to send message", [
                'to' => $targetAgent,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get pending messages for this agent
     */
    public function getMessages(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM agent_messages
                WHERE to_agent = ? AND processed = 0
                ORDER BY created_at ASC
            ");

            $stmt->execute([$this->agentName]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get messages", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Mark message as processed
     */
    protected function markMessageProcessed(int $messageId): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE agent_messages
                SET processed = 1, processed_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$messageId]);

        } catch (Exception $e) {
            $this->logger->error("Failed to mark message processed", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send notification
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string $priority Priority level
     * @param array $recipients Recipients
     */
    protected function notify(string $title, string $message, string $priority = 'info', array $recipients = []): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    agent_id, title, message, priority, recipients, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $this->agentId,
                $title,
                $message,
                $priority,
                json_encode($recipients)
            ]);

            $this->logger->info("Notification sent", [
                'title' => $title,
                'priority' => $priority
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to send notification", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record task start
     */
    private function recordTaskStart(string $taskId, array $task): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO agent_tasks (
                    task_id, agent_id, task_data, status, started_at
                ) VALUES (?, ?, ?, 'running', NOW())
            ");

            $stmt->execute([
                $taskId,
                $this->agentId,
                json_encode($task)
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to record task start", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record task completion
     */
    private function recordTaskComplete(string $taskId, array $result): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE agent_tasks
                SET status = 'completed', result = ?, completed_at = NOW()
                WHERE task_id = ?
            ");

            $stmt->execute([
                json_encode($result),
                $taskId
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to record task completion", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record task error
     */
    private function recordTaskError(string $taskId, string $error): void
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE agent_tasks
                SET status = 'failed', error = ?, completed_at = NOW()
                WHERE task_id = ?
            ");

            $stmt->execute([
                $error,
                $taskId
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to record task error", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get agent performance metrics
     */
    public function getMetrics(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_tasks,
                    AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration_seconds
                FROM agent_tasks
                WHERE agent_id = ?
                AND started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");

            $stmt->execute([$this->agentId]);

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        } catch (Exception $e) {
            $this->logger->error("Failed to get metrics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Check if agent is healthy
     */
    public function isHealthy(): bool
    {
        return $this->status !== self::STATUS_ERROR && $this->status !== self::STATUS_DISABLED;
    }

    /**
     * Get agent status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get agent info
     */
    public function getInfo(): array
    {
        return [
            'id' => $this->agentId,
            'name' => $this->agentName,
            'class' => get_class($this),
            'status' => $this->status,
            'capabilities' => $this->getCapabilities(),
            'healthy' => $this->isHealthy()
        ];
    }
}
