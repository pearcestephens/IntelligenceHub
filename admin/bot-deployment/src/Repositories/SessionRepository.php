<?php
/**
 * Session Repository
 *
 * Data access layer for multi-thread session operations
 *
 * @package BotDeployment\Repositories
 */

namespace BotDeployment\Repositories;

use BotDeployment\Database\Connection;
use BotDeployment\Database\QueryBuilder;
use BotDeployment\Models\Session;
use BotDeployment\Exceptions\DatabaseException;
use PDO;
use PDOException;

class SessionRepository
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
    private $table = 'multi_thread_sessions';

    /**
     * Constructor
     * @param PDO|null $connection
     */
    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::get();
    }

    /**
     * Find session by ID
     * @param string $session_id
     * @return Session|null
     * @throws DatabaseException
     */
    public function find(string $session_id): ?Session
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $data = $qb->table($this->table)
                ->where('session_id', $session_id)
                ->first();

            return $data ? new Session($data) : null;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to find session: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find all sessions
     * @param array $filters
     * @return array
     * @throws DatabaseException
     */
    public function findAll(array $filters = []): array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $qb->table($this->table);

            if (isset($filters['status'])) {
                $qb->where('status', $filters['status']);
            }

            $orderBy = $filters['order_by'] ?? 'started_at';
            $orderDir = $filters['order_dir'] ?? 'DESC';
            $qb->orderBy($orderBy, $orderDir);

            if (isset($filters['limit'])) {
                $qb->limit((int) $filters['limit']);
            }

            $results = $qb->get();

            return array_map(function($data) {
                return new Session($data);
            }, $results);

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to find sessions: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Find active sessions
     * @return array
     * @throws DatabaseException
     */
    public function findActive(): array
    {
        return $this->findAll(['status' => Session::STATUS_ACTIVE]);
    }

    /**
     * Create new session
     * @param Session $session
     * @return string Session ID
     * @throws DatabaseException
     */
    public function create(Session $session): string
    {
        try {
            $session->validate();

            // Generate session ID if not set
            if (!$session->getSessionId()) {
                $session->setSessionId(Session::generateId());
            }

            $qb = new QueryBuilder($this->connection);
            $data = [
                'session_id' => $session->getSessionId(),
                'topic' => $session->getTopic(),
                'thread_count' => $session->getThreadCount(),
                'status' => $session->getStatus(),
                'metadata' => json_encode($session->getMetadata()),
                'started_at' => date('Y-m-d H:i:s')
            ];

            $qb->table($this->table)->insert($data);

            return $session->getSessionId();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to create session: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update session
     * @param Session $session
     * @return bool
     * @throws DatabaseException
     */
    public function update(Session $session): bool
    {
        try {
            $session->validate();

            if (!$session->getSessionId()) {
                throw new DatabaseException('Cannot update session without ID');
            }

            $qb = new QueryBuilder($this->connection);
            $data = [
                'topic' => $session->getTopic(),
                'thread_count' => $session->getThreadCount(),
                'status' => $session->getStatus(),
                'metadata' => json_encode($session->getMetadata())
            ];

            if ($session->getCompletedAt()) {
                $data['completed_at'] = $session->getCompletedAt()->format('Y-m-d H:i:s');
            }

            $affected = $qb->table($this->table)
                ->where('session_id', $session->getSessionId())
                ->update($data);

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update session: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete session
     * @param string $session_id
     * @return bool
     * @throws DatabaseException
     */
    public function delete(string $session_id): bool
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $affected = $qb->table($this->table)
                ->where('session_id', $session_id)
                ->delete();

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to delete session: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Complete session
     * @param string $session_id
     * @return bool
     * @throws DatabaseException
     */
    public function complete(string $session_id): bool
    {
        return $this->updateStatus($session_id, Session::STATUS_COMPLETED);
    }

    /**
     * Abandon session
     * @param string $session_id
     * @return bool
     * @throws DatabaseException
     */
    public function abandon(string $session_id): bool
    {
        return $this->updateStatus($session_id, Session::STATUS_ABANDONED);
    }

    /**
     * Update session status
     * @param string $session_id
     * @param string $status
     * @return bool
     * @throws DatabaseException
     */
    private function updateStatus(string $session_id, string $status): bool
    {
        try {
            $qb = new QueryBuilder($this->connection);
            $data = ['status' => $status];

            if ($status === Session::STATUS_COMPLETED || $status === Session::STATUS_ABANDONED) {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }

            $affected = $qb->table($this->table)
                ->where('session_id', $session_id)
                ->update($data);

            return $affected > 0;

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to update session status: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get session analytics
     * @param string $session_id
     * @return array|null
     * @throws DatabaseException
     */
    public function getAnalytics(string $session_id): ?array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            return $qb->table('v_multithread_analytics')
                ->where('session_id', $session_id)
                ->first();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get session analytics: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get all sessions with analytics
     * @param int $limit
     * @return array
     * @throws DatabaseException
     */
    public function getAllWithAnalytics(int $limit = 50): array
    {
        try {
            $qb = new QueryBuilder($this->connection);
            return $qb->table('v_multithread_analytics')
                ->orderBy('started_at', 'DESC')
                ->limit($limit)
                ->get();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to get sessions with analytics: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Count sessions
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

            return $qb->count();

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to count sessions: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get recent sessions
     * @param int $limit
     * @return array
     * @throws DatabaseException
     */
    public function getRecent(int $limit = 10): array
    {
        return $this->findAll([
            'order_by' => 'started_at',
            'order_dir' => 'DESC',
            'limit' => $limit
        ]);
    }

    /**
     * Search sessions by topic
     * @param string $query
     * @param int $limit
     * @return array
     * @throws DatabaseException
     */
    public function search(string $query, int $limit = 20): array
    {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE topic LIKE :query
                    ORDER BY started_at DESC
                    LIMIT :limit";

            $stmt = $this->connection->prepare($sql);
            $searchTerm = '%' . $query . '%';
            $stmt->bindValue(':query', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();

            return array_map(function($data) {
                return new Session($data);
            }, $results);

        } catch (PDOException $e) {
            throw new DatabaseException('Failed to search sessions: ' . $e->getMessage(), 0, $e);
        }
    }
}
