<?php
/**
 * Bot Repository
 *
 * Data access layer for bot operations with connection pooling
 *
 * @package BotDeployment\Repositories
 */

namespace BotDeployment\Repositories;

use BotDeployment\Database\Connection;
use BotDeployment\Database\QueryBuilder;
use BotDeployment\Models\Bot;
use BotDeployment\Exceptions\DatabaseException;
use PDO;
use PDOException;

class BotRepository
{
    /**
     * Database connection
     * @var PDO
     */
    private $connection;

    /**
     * Table name
     * @var string
     */
    private $table = 'bot_deployments';

    /**
     * Constructor
     * @param PDO|null $connection
     */
    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::get();
    }

    /**
     * Find bot by ID
     * @param int $bot_id
     * @return Bot|null
     * @throws DatabaseException
     */
    public function find(int $bot_id): ?Bot
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $data = $qb->table($this->table)
                ->where('bot_id', $bot_id)
                ->first();

            return $data ? new Bot($data) : null;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to find bot: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find all bots
     * @param array $filters
     * @return array
     * @throws DatabaseException
     */
    public function findAll(array $filters = []): array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $qb->table($this->table);

            // Apply filters
            if (isset($filters['status'])) {
                $qb->where('status', $filters['status']);
            }

            if (isset($filters['role'])) {
                $qb->where('bot_role', $filters['role']);
            }

            if (isset($filters['active_only']) && $filters['active_only']) {
                $qb->where('status', 'active');
            }

            // Order by
            $orderBy = $filters['order_by'] ?? 'bot_id';
            $orderDir = $filters['order_dir'] ?? 'ASC';
            $qb->orderBy($orderBy, $orderDir);

            // Limit
            if (isset($filters['limit'])) {
                $qb->limit((int) $filters['limit']);
            }

            if (isset($filters['offset'])) {
                $qb->offset((int) $filters['offset']);
            }

            $results = $qb->get();

            return array_map(function($data) {
                return new Bot($data);
            }, $results);

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to find bots: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find bots by role
     * @param string $role
     * @return array
     * @throws DatabaseException
     */
    public function findByRole(string $role): array
    {
        return $this->findAll(['role' => $role]);
    }

    /**
     * Find active bots
     * @return array
     * @throws DatabaseException
     */
    public function findActive(): array
    {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Find scheduled bots that need execution
     * @return array
     * @throws DatabaseException
     */
    public function findDueForExecution(): array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $results = $qb->table($this->table)
                ->where('status', 'active')
                ->whereNotNull('next_execution')
                ->where('next_execution', '<=', date('Y-m-d H:i:s'))
                ->orderBy('next_execution', 'ASC')
                ->get();

            return array_map(function($data) {
                return new Bot($data);
            }, $results);

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to find due bots: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Create new bot
     * @param Bot $bot
     * @return int Bot ID
     * @throws DatabaseException
     */
    public function create(Bot $bot): int
    {
        try {
            $bot->validate();

            $qb = new QueryBuilder($this->connection);
            $data = [
                'bot_name' => $bot->getBotName(),
                'bot_role' => $bot->getBotRole(),
                'system_prompt' => $bot->getSystemPrompt(),
                'schedule_cron' => $bot->getScheduleCron(),
                'status' => $bot->getStatus(),
                'config_json' => json_encode($bot->getConfigJson()),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($bot->getNextExecution()) {
                $data['next_execution'] = $bot->getNextExecution()->format('Y-m-d H:i:s');
            }

            $bot_id = $qb->table($this->table)->insert($data);
            $bot->setBotId($bot_id);

            return $bot_id;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to create bot: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update existing bot
     * @param Bot $bot
     * @return bool
     * @throws DatabaseException
     */
    public function update(Bot $bot): bool
    {
        try {
            $bot->validate();

            if (!$bot->getBotId()) {
                throw new DatabaseException('Cannot update bot without ID');
            }

            $qb = new QueryBuilder($this->connection);
            $data = [
                'bot_name' => $bot->getBotName(),
                'bot_role' => $bot->getBotRole(),
                'system_prompt' => $bot->getSystemPrompt(),
                'schedule_cron' => $bot->getScheduleCron(),
                'status' => $bot->getStatus(),
                'config_json' => json_encode($bot->getConfigJson()),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($bot->getLastExecution()) {
                $data['last_execution'] = $bot->getLastExecution()->format('Y-m-d H:i:s');
            }

            if ($bot->getNextExecution()) {
                $data['next_execution'] = $bot->getNextExecution()->format('Y-m-d H:i:s');
            }

            $affected = $qb->table($this->table)
                ->where('bot_id', $bot->getBotId())
                ->update($data);

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update bot: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete bot
     * @param int $bot_id
     * @return bool
     * @throws DatabaseException
     */
    public function delete(int $bot_id): bool
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $affected = $qb->table($this->table)
                ->where('bot_id', $bot_id)
                ->delete();

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to delete bot: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update bot execution timestamps
     * @param int $bot_id
     * @param string|null $nextExecution
     * @return bool
     * @throws DatabaseException
     */
    public function updateExecutionTime(int $bot_id, ?string $nextExecution = null): bool
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $data = [
                'last_execution' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($nextExecution) {
                $data['next_execution'] = $nextExecution;
            }

            $affected = $qb->table($this->table)
                ->where('bot_id', $bot_id)
                ->update($data);

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update execution time: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Pause bot
     * @param int $bot_id
     * @return bool
     * @throws DatabaseException
     */
    public function pause(int $bot_id): bool
    {
        return $this->updateStatus($bot_id, Bot::STATUS_PAUSED);
    }

    /**
     * Activate bot
     * @param int $bot_id
     * @return bool
     * @throws DatabaseException
     */
    public function activate(int $bot_id): bool
    {
        return $this->updateStatus($bot_id, Bot::STATUS_ACTIVE);
    }

    /**
     * Archive bot
     * @param int $bot_id
     * @return bool
     * @throws DatabaseException
     */
    public function archive(int $bot_id): bool
    {
        return $this->updateStatus($bot_id, Bot::STATUS_ARCHIVED);
    }

    /**
     * Update bot status
     * @param int $bot_id
     * @param string $status
     * @return bool
     * @throws DatabaseException
     */
    private function updateStatus(int $bot_id, string $status): bool
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $affected = $qb->table($this->table)
                ->where('bot_id', $bot_id)
                ->update([
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update status: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Count bots
     * @param array $filters
     * @return int
     * @throws DatabaseException
     */
    public function count(array $filters = []): int
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $qb->table($this->table);

            if (isset($filters['status'])) {
                $qb->where('status', $filters['status']);
            }

            if (isset($filters['role'])) {
                $qb->where('bot_role', $filters['role']);
            }

            return $qb->count();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to count bots: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get bot performance metrics
     * @param int $bot_id
     * @return array|null
     * @throws DatabaseException
     */
    public function getPerformanceMetrics(int $bot_id): ?array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            return $qb->table('v_bot_performance')
                ->where('bot_id', $bot_id)
                ->first();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get performance metrics: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get all bots with performance metrics
     * @return array
     * @throws DatabaseException
     */
    public function getAllWithMetrics(): array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            return $qb->table('v_bot_performance')
                ->orderBy('bot_id', 'ASC')
                ->get();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get bots with metrics: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Search bots by name or role
     * @param string $query
     * @param int $limit
     * @return array
     * @throws DatabaseException
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE bot_name LIKE :query1 OR bot_role LIKE :query2
                    ORDER BY bot_name ASC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $searchTerm = '%' . $query . '%';
            $stmt->bindValue(':query1', $searchTerm);
            $stmt->bindValue(':query2', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();

            return array_map(function($data) {
                return new Bot($data);
            }, $results);

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to search bots: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Begin transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}
