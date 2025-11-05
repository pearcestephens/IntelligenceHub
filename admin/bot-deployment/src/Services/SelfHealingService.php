<?php

namespace BotDeployment\Services;

use BotDeployment\Config\Connection;
use BotDeployment\Services\Logger;
use BotDeployment\Services\NotificationService;
use Exception;

/**
 * Self-Healing Service
 *
 * Provides automatic recovery and self-healing capabilities:
 * - Auto-retry failed executions
 * - Automatic error recovery
 * - Configuration rollback
 * - Health-based actions
 * - Circuit breaker pattern
 * - Exponential backoff
 */
class SelfHealingService
{
    private $logger;
    private $db;
    private $notifications;

    // Retry strategies
    const STRATEGY_IMMEDIATE = 'immediate';
    const STRATEGY_EXPONENTIAL = 'exponential';
    const STRATEGY_LINEAR = 'linear';
    const STRATEGY_FIXED = 'fixed';

    // Circuit breaker states
    const STATE_CLOSED = 'closed';      // Normal operation
    const STATE_OPEN = 'open';          // Failure threshold reached
    const STATE_HALF_OPEN = 'half_open'; // Testing recovery

    private $circuitBreakers = [];

    public function __construct()
    {
        $this->logger = new Logger('self-healing');
        $this->db = Connection::getInstance();
        $this->notifications = new NotificationService();
    }

    /**
     * Auto-retry failed execution
     *
     * @param int $executionId Execution ID
     * @param array $options Retry options
     * @return bool Success
     */
    public function retryFailedExecution(int $executionId, array $options = []): bool
    {
        try {
            // Get execution details
            $execution = $this->getExecution($executionId);
            if (!$execution || $execution['status'] !== 'failed') {
                throw new Exception("Execution not found or not in failed state");
            }

            // Check if already retrying
            if ($this->isRetrying($executionId)) {
                $this->logger->warning("Execution already being retried", ['execution_id' => $executionId]);
                return false;
            }

            // Get retry config
            $maxRetries = $options['max_retries'] ?? 3;
            $strategy = $options['strategy'] ?? self::STRATEGY_EXPONENTIAL;
            $baseDelay = $options['base_delay'] ?? 5; // seconds

            // Get current retry count
            $retryCount = $this->getRetryCount($executionId);

            if ($retryCount >= $maxRetries) {
                $this->logger->error("Max retries reached", [
                    'execution_id' => $executionId,
                    'retry_count' => $retryCount
                ]);

                $this->notifications->send(
                    NotificationService::TYPE_BOT_FAILED,
                    NotificationService::LEVEL_ERROR,
                    "Bot Execution Failed After {$retryCount} Retries",
                    "Bot '{$execution['bot_name']}' failed after {$retryCount} retry attempts",
                    ['execution_id' => $executionId, 'bot_id' => $execution['bot_id']]
                );

                return false;
            }

            // Calculate delay
            $delay = $this->calculateDelay($strategy, $retryCount, $baseDelay);

            // Schedule retry
            $this->scheduleRetry($executionId, $delay, $retryCount + 1);

            $this->logger->info("Retry scheduled", [
                'execution_id' => $executionId,
                'retry_count' => $retryCount + 1,
                'delay' => $delay
            ]);

            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to retry execution", [
                'execution_id' => $executionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Calculate retry delay based on strategy
     */
    private function calculateDelay(string $strategy, int $retryCount, int $baseDelay): int
    {
        return match ($strategy) {
            self::STRATEGY_IMMEDIATE => 0,
            self::STRATEGY_EXPONENTIAL => $baseDelay * pow(2, $retryCount),
            self::STRATEGY_LINEAR => $baseDelay * ($retryCount + 1),
            self::STRATEGY_FIXED => $baseDelay,
            default => $baseDelay
        };
    }

    /**
     * Schedule retry
     */
    private function scheduleRetry(int $executionId, int $delay, int $retryCount): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO execution_retries (
                execution_id, retry_count, scheduled_at, status, created_at
            ) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), 'pending', NOW())
        ");

        $stmt->execute([$executionId, $retryCount, $delay]);
    }

    /**
     * Get execution details
     */
    private function getExecution(int $executionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, b.name as bot_name
            FROM bot_executions e
            JOIN bots b ON b.id = e.bot_id
            WHERE e.id = ?
        ");
        $stmt->execute([$executionId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if execution is being retried
     */
    private function isRetrying(int $executionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM execution_retries
            WHERE execution_id = ? AND status = 'pending'
        ");
        $stmt->execute([$executionId]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get retry count
     */
    private function getRetryCount(int $executionId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM execution_retries
            WHERE execution_id = ?
        ");
        $stmt->execute([$executionId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Process pending retries
     */
    public function processPendingRetries(): int
    {
        try {
            // Get retries ready to execute
            $stmt = $this->db->query("
                SELECT * FROM execution_retries
                WHERE status = 'pending'
                AND scheduled_at <= NOW()
                ORDER BY scheduled_at ASC
                LIMIT 100
            ");

            $retries = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $processed = 0;

            foreach ($retries as $retry) {
                if ($this->executeRetry($retry)) {
                    $processed++;
                }
            }

            return $processed;

        } catch (Exception $e) {
            $this->logger->error("Failed to process retries", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Execute retry
     */
    private function executeRetry(array $retry): bool
    {
        try {
            // Mark as processing
            $this->updateRetryStatus($retry['id'], 'processing');

            // Get original execution
            $execution = $this->getExecution($retry['execution_id']);
            if (!$execution) {
                throw new Exception("Original execution not found");
            }

            // Trigger new execution
            $botService = new \BotDeployment\Services\BotExecutionService();
            $newExecutionId = $botService->execute($execution['bot_id']);

            if ($newExecutionId) {
                $this->updateRetryStatus($retry['id'], 'completed', $newExecutionId);
                $this->logger->info("Retry executed successfully", [
                    'retry_id' => $retry['id'],
                    'new_execution_id' => $newExecutionId
                ]);
                return true;
            } else {
                $this->updateRetryStatus($retry['id'], 'failed');
                return false;
            }

        } catch (Exception $e) {
            $this->logger->error("Failed to execute retry", [
                'retry_id' => $retry['id'],
                'error' => $e->getMessage()
            ]);
            $this->updateRetryStatus($retry['id'], 'failed');
            return false;
        }
    }

    /**
     * Update retry status
     */
    private function updateRetryStatus(int $retryId, string $status, ?int $newExecutionId = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE execution_retries
            SET status = ?, new_execution_id = ?, completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, $newExecutionId, $retryId]);
    }

    /**
     * Rollback bot configuration
     */
    public function rollbackConfiguration(int $botId, ?int $versionId = null): bool
    {
        try {
            if ($versionId) {
                // Rollback to specific version
                $version = $this->getConfigVersion($versionId);
                if (!$version) {
                    throw new Exception("Configuration version not found");
                }
            } else {
                // Rollback to previous version
                $version = $this->getPreviousConfigVersion($botId);
                if (!$version) {
                    throw new Exception("No previous configuration found");
                }
            }

            // Create backup of current config
            $this->backupCurrentConfig($botId);

            // Restore previous config
            $stmt = $this->db->prepare("
                UPDATE bots
                SET config = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$version['config'], $botId]);

            $this->logger->info("Configuration rolled back", [
                'bot_id' => $botId,
                'version_id' => $version['id']
            ]);

            $this->notifications->send(
                NotificationService::TYPE_SYSTEM_ALERT,
                NotificationService::LEVEL_WARNING,
                "Bot Configuration Rolled Back",
                "Bot #{$botId} configuration was rolled back to version {$version['version']}",
                ['bot_id' => $botId, 'version_id' => $version['id']]
            );

            return true;

        } catch (Exception $e) {
            $this->logger->error("Failed to rollback configuration", [
                'bot_id' => $botId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get configuration version
     */
    private function getConfigVersion(int $versionId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM bot_config_versions WHERE id = ?
        ");
        $stmt->execute([$versionId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get previous configuration version
     */
    private function getPreviousConfigVersion(int $botId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM bot_config_versions
            WHERE bot_id = ?
            ORDER BY created_at DESC
            LIMIT 1, 1
        ");
        $stmt->execute([$botId]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Backup current configuration
     */
    private function backupCurrentConfig(int $botId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO bot_config_versions (bot_id, config, version, created_at)
            SELECT id, config, COALESCE(
                (SELECT MAX(version) + 1 FROM bot_config_versions WHERE bot_id = ?), 1
            ), NOW()
            FROM bots WHERE id = ?
        ");
        $stmt->execute([$botId, $botId]);
    }

    /**
     * Circuit breaker: Check if bot should execute
     */
    public function canExecute(int $botId): bool
    {
        $state = $this->getCircuitBreakerState($botId);

        switch ($state) {
            case self::STATE_CLOSED:
                return true;

            case self::STATE_OPEN:
                // Check if cool-down period has passed
                if ($this->shouldAttemptRecovery($botId)) {
                    $this->setCircuitBreakerState($botId, self::STATE_HALF_OPEN);
                    return true;
                }
                return false;

            case self::STATE_HALF_OPEN:
                // Allow single test execution
                return true;

            default:
                return true;
        }
    }

    /**
     * Record execution result for circuit breaker
     */
    public function recordExecutionResult(int $botId, bool $success): void
    {
        $state = $this->getCircuitBreakerState($botId);

        if ($success) {
            if ($state === self::STATE_HALF_OPEN) {
                // Recovery successful, close circuit
                $this->setCircuitBreakerState($botId, self::STATE_CLOSED);
                $this->resetFailureCount($botId);
            }
        } else {
            $failures = $this->incrementFailureCount($botId);
            $threshold = 5; // Open circuit after 5 failures

            if ($failures >= $threshold && $state === self::STATE_CLOSED) {
                // Open circuit breaker
                $this->setCircuitBreakerState($botId, self::STATE_OPEN);

                $this->notifications->send(
                    NotificationService::TYPE_SYSTEM_ALERT,
                    NotificationService::LEVEL_CRITICAL,
                    "Circuit Breaker Opened",
                    "Bot #{$botId} circuit breaker opened after {$failures} consecutive failures",
                    ['bot_id' => $botId, 'failures' => $failures]
                );
            } elseif ($state === self::STATE_HALF_OPEN) {
                // Recovery failed, reopen circuit
                $this->setCircuitBreakerState($botId, self::STATE_OPEN);
            }
        }
    }

    /**
     * Get circuit breaker state
     */
    private function getCircuitBreakerState(int $botId): string
    {
        if (!isset($this->circuitBreakers[$botId])) {
            // Load from database
            $stmt = $this->db->prepare("
                SELECT circuit_breaker_state
                FROM bots
                WHERE id = ?
            ");
            $stmt->execute([$botId]);

            $this->circuitBreakers[$botId] = $stmt->fetchColumn() ?: self::STATE_CLOSED;
        }

        return $this->circuitBreakers[$botId];
    }

    /**
     * Set circuit breaker state
     */
    private function setCircuitBreakerState(int $botId, string $state): void
    {
        $this->circuitBreakers[$botId] = $state;

        $stmt = $this->db->prepare("
            UPDATE bots
            SET circuit_breaker_state = ?,
                circuit_breaker_changed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$state, $botId]);
    }

    /**
     * Check if should attempt recovery
     */
    private function shouldAttemptRecovery(int $botId): bool
    {
        $stmt = $this->db->prepare("
            SELECT circuit_breaker_changed_at
            FROM bots
            WHERE id = ?
        ");
        $stmt->execute([$botId]);

        $changedAt = $stmt->fetchColumn();
        if (!$changedAt) {
            return true;
        }

        // Wait 60 seconds before attempting recovery
        $cooldownPeriod = 60;
        $elapsed = time() - strtotime($changedAt);

        return $elapsed >= $cooldownPeriod;
    }

    /**
     * Increment failure count
     */
    private function incrementFailureCount(int $botId): int
    {
        $stmt = $this->db->prepare("
            UPDATE bots
            SET failure_count = failure_count + 1
            WHERE id = ?
        ");
        $stmt->execute([$botId]);

        $stmt = $this->db->prepare("SELECT failure_count FROM bots WHERE id = ?");
        $stmt->execute([$botId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Reset failure count
     */
    private function resetFailureCount(int $botId): void
    {
        $stmt = $this->db->prepare("
            UPDATE bots
            SET failure_count = 0
            WHERE id = ?
        ");
        $stmt->execute([$botId]);
    }

    /**
     * Get self-healing statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total retries
            $stmt = $this->db->query("SELECT COUNT(*) FROM execution_retries");
            $stats['total_retries'] = (int) $stmt->fetchColumn();

            // Successful retries
            $stmt = $this->db->query("SELECT COUNT(*) FROM execution_retries WHERE status = 'completed'");
            $stats['successful_retries'] = (int) $stmt->fetchColumn();

            // Pending retries
            $stmt = $this->db->query("SELECT COUNT(*) FROM execution_retries WHERE status = 'pending'");
            $stats['pending_retries'] = (int) $stmt->fetchColumn();

            // Circuit breakers open
            $stmt = $this->db->query("SELECT COUNT(*) FROM bots WHERE circuit_breaker_state = 'open'");
            $stats['circuits_open'] = (int) $stmt->fetchColumn();

            // Configuration rollbacks
            $stmt = $this->db->query("SELECT COUNT(*) FROM bot_config_versions WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $stats['recent_rollbacks'] = (int) $stmt->fetchColumn();

            return $stats;

        } catch (Exception $e) {
            $this->logger->error("Failed to get statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
