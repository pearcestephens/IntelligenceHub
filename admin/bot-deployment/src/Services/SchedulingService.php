<?php

namespace BotDeployment\Services;

use BotDeployment\Config\Connection;
use BotDeployment\Services\Logger;
use Exception;

/**
 * Advanced Scheduling Service
 *
 * Provides advanced scheduling capabilities:
 * - Conditional triggers (if/then logic)
 * - Event-based execution
 * - Bot chaining
 * - Parallel execution groups
 * - Schedule templates
 */
class SchedulingService
{
    private $logger;
    private $db;

    // Trigger types
    const TRIGGER_CRON = 'cron';
    const TRIGGER_EVENT = 'event';
    const TRIGGER_CONDITIONAL = 'conditional';
    const TRIGGER_WEBHOOK = 'webhook';
    const TRIGGER_MANUAL = 'manual';

    // Event types
    const EVENT_BOT_COMPLETED = 'bot.completed';
    const EVENT_BOT_FAILED = 'bot.failed';
    const EVENT_FILE_CREATED = 'file.created';
    const EVENT_FILE_MODIFIED = 'file.modified';
    const EVENT_API_CALL = 'api.call';
    const EVENT_CUSTOM = 'custom';

    public function __construct()
    {
        $this->logger = new Logger('scheduling');
        $this->db = Connection::getInstance();
    }

    /**
     * Create advanced schedule
     */
    public function createSchedule(array $data): int
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bot_schedules (
                    bot_id, type, cron_expression, trigger_event,
                    conditions, chain_config, enabled, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['bot_id'],
                $data['type'] ?? self::TRIGGER_CRON,
                $data['cron_expression'] ?? null,
                $data['trigger_event'] ?? null,
                json_encode($data['conditions'] ?? []),
                json_encode($data['chain_config'] ?? []),
                $data['enabled'] ?? 1
            ]);

            $scheduleId = (int) $this->db->lastInsertId();

            $this->logger->info("Schedule created", [
                'schedule_id' => $scheduleId,
                'bot_id' => $data['bot_id'],
                'type' => $data['type'] ?? self::TRIGGER_CRON
            ]);

            return $scheduleId;

        } catch (Exception $e) {
            $this->logger->error("Failed to create schedule", [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Create conditional trigger
     */
    public function createConditionalTrigger(int $botId, array $conditions): int
    {
        return $this->createSchedule([
            'bot_id' => $botId,
            'type' => self::TRIGGER_CONDITIONAL,
            'conditions' => $conditions,
            'enabled' => 1
        ]);
    }

    /**
     * Create event-based trigger
     */
    public function createEventTrigger(int $botId, string $event, array $filters = []): int
    {
        return $this->createSchedule([
            'bot_id' => $botId,
            'type' => self::TRIGGER_EVENT,
            'trigger_event' => $event,
            'conditions' => $filters,
            'enabled' => 1
        ]);
    }

    /**
     * Create bot chain
     */
    public function createBotChain(array $botIds, array $options = []): int
    {
        try {
            // Create chain config
            $chainConfig = [
                'bots' => $botIds,
                'mode' => $options['mode'] ?? 'sequential', // sequential or parallel
                'on_failure' => $options['on_failure'] ?? 'stop', // stop, continue, retry
                'timeout' => $options['timeout'] ?? 3600,
                'max_retries' => $options['max_retries'] ?? 0
            ];

            // Create schedule for first bot in chain
            $stmt = $this->db->prepare("
                INSERT INTO bot_schedules (
                    bot_id, type, chain_config, enabled, created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $botIds[0],
                self::TRIGGER_MANUAL,
                json_encode($chainConfig),
                1
            ]);

            $chainId = (int) $this->db->lastInsertId();

            $this->logger->info("Bot chain created", [
                'chain_id' => $chainId,
                'bots' => $botIds,
                'mode' => $chainConfig['mode']
            ]);

            return $chainId;

        } catch (Exception $e) {
            $this->logger->error("Failed to create bot chain", [
                'error' => $e->getMessage(),
                'bot_ids' => $botIds
            ]);
            throw $e;
        }
    }

    /**
     * Execute bot chain
     */
    public function executeChain(int $chainId): bool
    {
        try {
            // Get chain config
            $stmt = $this->db->prepare("
                SELECT * FROM bot_schedules WHERE id = ?
            ");
            $stmt->execute([$chainId]);
            $chain = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$chain) {
                throw new Exception("Chain not found");
            }

            $config = json_decode($chain['chain_config'], true);
            $mode = $config['mode'] ?? 'sequential';

            if ($mode === 'sequential') {
                return $this->executeSequentialChain($chainId, $config);
            } else {
                return $this->executeParallelChain($chainId, $config);
            }

        } catch (Exception $e) {
            $this->logger->error("Failed to execute chain", [
                'chain_id' => $chainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Execute sequential chain
     */
    private function executeSequentialChain(int $chainId, array $config): bool
    {
        $botIds = $config['bots'];
        $onFailure = $config['on_failure'] ?? 'stop';

        $botService = new \BotDeployment\Services\BotExecutionService();

        foreach ($botIds as $botId) {
            $executionId = $botService->execute($botId);

            if (!$executionId) {
                $this->logger->error("Chain bot execution failed", [
                    'chain_id' => $chainId,
                    'bot_id' => $botId
                ]);

                if ($onFailure === 'stop') {
                    return false;
                }
                continue;
            }

            // Wait for completion
            $success = $this->waitForCompletion($executionId, $config['timeout'] ?? 3600);

            if (!$success && $onFailure === 'stop') {
                return false;
            }
        }

        return true;
    }

    /**
     * Execute parallel chain
     */
    private function executeParallelChain(int $chainId, array $config): bool
    {
        $botIds = $config['bots'];
        $botService = new \BotDeployment\Services\BotExecutionService();

        $executionIds = [];

        // Start all bots
        foreach ($botIds as $botId) {
            $executionId = $botService->execute($botId);
            if ($executionId) {
                $executionIds[] = $executionId;
            }
        }

        // Wait for all to complete
        $allSuccess = true;
        foreach ($executionIds as $executionId) {
            $success = $this->waitForCompletion($executionId, $config['timeout'] ?? 3600);
            if (!$success) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Wait for execution completion
     */
    private function waitForCompletion(int $executionId, int $timeout): bool
    {
        $start = time();

        while (time() - $start < $timeout) {
            $stmt = $this->db->prepare("
                SELECT status FROM bot_executions WHERE id = ?
            ");
            $stmt->execute([$executionId]);
            $status = $stmt->fetchColumn();

            if ($status === 'completed') {
                return true;
            } elseif ($status === 'failed') {
                return false;
            }

            sleep(1);
        }

        return false; // Timeout
    }

    /**
     * Check conditional triggers
     */
    public function checkConditionalTriggers(): int
    {
        try {
            // Get all conditional schedules
            $stmt = $this->db->query("
                SELECT * FROM bot_schedules
                WHERE type = 'conditional'
                AND enabled = 1
            ");

            $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $triggered = 0;

            foreach ($schedules as $schedule) {
                $conditions = json_decode($schedule['conditions'], true);

                if ($this->evaluateConditions($conditions)) {
                    $this->triggerBot($schedule['bot_id'], $schedule['id']);
                    $triggered++;
                }
            }

            return $triggered;

        } catch (Exception $e) {
            $this->logger->error("Failed to check conditional triggers", [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Evaluate conditions
     */
    private function evaluateConditions(array $conditions): bool
    {
        if (empty($conditions)) {
            return false;
        }

        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? 'comparison';

            switch ($type) {
                case 'comparison':
                    if (!$this->evaluateComparison($condition)) {
                        return false;
                    }
                    break;

                case 'time':
                    if (!$this->evaluateTimeCondition($condition)) {
                        return false;
                    }
                    break;

                case 'database':
                    if (!$this->evaluateDatabaseCondition($condition)) {
                        return false;
                    }
                    break;

                case 'api':
                    if (!$this->evaluateApiCondition($condition)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Evaluate comparison condition
     */
    private function evaluateComparison(array $condition): bool
    {
        $left = $condition['left'] ?? null;
        $operator = $condition['operator'] ?? '==';
        $right = $condition['right'] ?? null;

        return match ($operator) {
            '==' => $left == $right,
            '!=' => $left != $right,
            '>' => $left > $right,
            '<' => $left < $right,
            '>=' => $left >= $right,
            '<=' => $left <= $right,
            'contains' => str_contains($left, $right),
            'starts_with' => str_starts_with($left, $right),
            'ends_with' => str_ends_with($left, $right),
            default => false
        };
    }

    /**
     * Evaluate time condition
     */
    private function evaluateTimeCondition(array $condition): bool
    {
        $now = new \DateTime();

        // Check time range
        if (isset($condition['time_start']) && isset($condition['time_end'])) {
            $start = new \DateTime($condition['time_start']);
            $end = new \DateTime($condition['time_end']);

            if ($now < $start || $now > $end) {
                return false;
            }
        }

        // Check day of week
        if (isset($condition['days_of_week'])) {
            $today = strtolower($now->format('l'));
            if (!in_array($today, $condition['days_of_week'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate database condition
     */
    private function evaluateDatabaseCondition(array $condition): bool
    {
        try {
            $query = $condition['query'] ?? '';
            $expected = $condition['expected'] ?? null;

            if (empty($query)) {
                return false;
            }

            $stmt = $this->db->query($query);
            $result = $stmt->fetchColumn();

            return $result == $expected;

        } catch (Exception $e) {
            $this->logger->error("Database condition failed", [
                'error' => $e->getMessage(),
                'condition' => $condition
            ]);
            return false;
        }
    }

    /**
     * Evaluate API condition
     */
    private function evaluateApiCondition(array $condition): bool
    {
        try {
            $url = $condition['url'] ?? '';
            $method = $condition['method'] ?? 'GET';
            $expected_status = $condition['expected_status'] ?? 200;

            if (empty($url)) {
                return false;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_exec($ch);
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $status == $expected_status;

        } catch (Exception $e) {
            $this->logger->error("API condition failed", [
                'error' => $e->getMessage(),
                'condition' => $condition
            ]);
            return false;
        }
    }

    /**
     * Trigger bot execution
     */
    private function triggerBot(int $botId, int $scheduleId): void
    {
        try {
            $botService = new \BotDeployment\Services\BotExecutionService();
            $executionId = $botService->execute($botId);

            $this->logger->info("Conditional trigger executed", [
                'bot_id' => $botId,
                'schedule_id' => $scheduleId,
                'execution_id' => $executionId
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to trigger bot", [
                'bot_id' => $botId,
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fire event
     */
    public function fireEvent(string $event, array $data = []): int
    {
        try {
            // Log event
            $stmt = $this->db->prepare("
                INSERT INTO schedule_events (
                    event_type, event_data, created_at
                ) VALUES (?, ?, NOW())
            ");
            $stmt->execute([$event, json_encode($data)]);

            // Get event-based schedules
            $stmt = $this->db->prepare("
                SELECT * FROM bot_schedules
                WHERE type = 'event'
                AND trigger_event = ?
                AND enabled = 1
            ");
            $stmt->execute([$event]);

            $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $triggered = 0;

            foreach ($schedules as $schedule) {
                // Check filters
                $filters = json_decode($schedule['conditions'], true) ?? [];

                if ($this->matchesFilters($data, $filters)) {
                    $this->triggerBot($schedule['bot_id'], $schedule['id']);
                    $triggered++;
                }
            }

            return $triggered;

        } catch (Exception $e) {
            $this->logger->error("Failed to fire event", [
                'event' => $event,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Check if event data matches filters
     */
    private function matchesFilters(array $data, array $filters): bool
    {
        if (empty($filters)) {
            return true;
        }

        foreach ($filters as $key => $value) {
            if (!isset($data[$key]) || $data[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get schedule templates
     */
    public function getScheduleTemplates(): array
    {
        return [
            [
                'name' => 'Daily at 9 AM',
                'description' => 'Run every day at 9:00 AM',
                'config' => [
                    'type' => self::TRIGGER_CRON,
                    'cron_expression' => '0 9 * * *'
                ]
            ],
            [
                'name' => 'Hourly',
                'description' => 'Run every hour',
                'config' => [
                    'type' => self::TRIGGER_CRON,
                    'cron_expression' => '0 * * * *'
                ]
            ],
            [
                'name' => 'Every 15 Minutes',
                'description' => 'Run every 15 minutes',
                'config' => [
                    'type' => self::TRIGGER_CRON,
                    'cron_expression' => '*/15 * * * *'
                ]
            ],
            [
                'name' => 'Weekdays at 8 AM',
                'description' => 'Run Monday-Friday at 8:00 AM',
                'config' => [
                    'type' => self::TRIGGER_CRON,
                    'cron_expression' => '0 8 * * 1-5'
                ]
            ],
            [
                'name' => 'On Bot Completion',
                'description' => 'Run when another bot completes',
                'config' => [
                    'type' => self::TRIGGER_EVENT,
                    'trigger_event' => self::EVENT_BOT_COMPLETED
                ]
            ],
            [
                'name' => 'On Bot Failure',
                'description' => 'Run when another bot fails',
                'config' => [
                    'type' => self::TRIGGER_EVENT,
                    'trigger_event' => self::EVENT_BOT_FAILED
                ]
            ],
            [
                'name' => 'Business Hours Only',
                'description' => 'Run only during business hours (9 AM - 5 PM, weekdays)',
                'config' => [
                    'type' => self::TRIGGER_CONDITIONAL,
                    'conditions' => [
                        [
                            'type' => 'time',
                            'time_start' => '09:00',
                            'time_end' => '17:00',
                            'days_of_week' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        try {
            $stats = [];

            // Total schedules by type
            $stmt = $this->db->query("
                SELECT type, COUNT(*) as count
                FROM bot_schedules
                GROUP BY type
            ");
            $stats['by_type'] = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            // Active schedules
            $stmt = $this->db->query("SELECT COUNT(*) FROM bot_schedules WHERE enabled = 1");
            $stats['active'] = (int) $stmt->fetchColumn();

            // Total chains
            $stmt = $this->db->query("SELECT COUNT(*) FROM bot_schedules WHERE chain_config IS NOT NULL");
            $stats['chains'] = (int) $stmt->fetchColumn();

            // Events fired today
            $stmt = $this->db->query("SELECT COUNT(*) FROM schedule_events WHERE created_at >= CURDATE()");
            $stats['events_today'] = (int) $stmt->fetchColumn();

            return $stats;

        } catch (Exception $e) {
            $this->logger->error("Failed to get statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
