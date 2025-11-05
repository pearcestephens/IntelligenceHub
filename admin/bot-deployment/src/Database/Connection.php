<?php
/**
 * Database Connection Manager
 *
 * Enterprise connection pooling and management with health checks
 *
 * @package BotDeployment\Database
 */

namespace BotDeployment\Database;

use BotDeployment\Config\Config;
use BotDeployment\Exceptions\DatabaseException;
use PDO;
use PDOException;

class Connection
{
    /**
     * Connection pool
     * @var array
     */
    private static $pool = [];

    /**
     * Active connections count
     * @var int
     */
    private static $activeConnections = 0;

    /**
     * Max pool size
     * @var int
     */
    private static $maxPoolSize = 10;

    /**
     * Connection health check interval (seconds)
     * @var int
     */
    private static $healthCheckInterval = 300;

    /**
     * Last health check timestamp
     * @var int
     */
    private static $lastHealthCheck = 0;

    /**
     * Get database connection
     * @param bool $forceNew Force new connection
     * @return PDO
     * @throws DatabaseException
     */
    public static function get(bool $forceNew = false): PDO
    {
        if ($forceNew || empty(self::$pool)) {
            return self::create();
        }

        // Health check
        if (time() - self::$lastHealthCheck > self::$healthCheckInterval) {
            self::healthCheck();
        }

        // Get from pool
        if (!empty(self::$pool)) {
            $connection = array_shift(self::$pool);
            self::$activeConnections++;
            return $connection;
        }

        return self::create();
    }

    /**
     * Create new database connection
     * @return PDO
     * @throws DatabaseException
     */
    private static function create(): PDO
    {
        $config = Config::database();

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

            self::$activeConnections++;

            return $pdo;

        } catch (PDOException $e) {
            throw new DatabaseException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    /**
     * Release connection back to pool
     * @param PDO $connection
     */
    public static function release(PDO $connection): void
    {
        if (count(self::$pool) < self::$maxPoolSize) {
            self::$pool[] = $connection;
        }

        if (self::$activeConnections > 0) {
            self::$activeConnections--;
        }
    }

    /**
     * Health check all pooled connections
     */
    private static function healthCheck(): void
    {
        $healthy = [];

        foreach (self::$pool as $connection) {
            try {
                $connection->query('SELECT 1');
                $healthy[] = $connection;
            } catch (PDOException $e) {
                // Connection is dead, discard it
                continue;
            }
        }

        self::$pool = $healthy;
        self::$lastHealthCheck = time();
    }

    /**
     * Close all connections
     */
    public static function closeAll(): void
    {
        self::$pool = [];
        self::$activeConnections = 0;
    }

    /**
     * Get connection statistics
     * @return array
     */
    public static function getStats(): array
    {
        return [
            'pool_size' => count(self::$pool),
            'active_connections' => self::$activeConnections,
            'max_pool_size' => self::$maxPoolSize,
            'last_health_check' => self::$lastHealthCheck
        ];
    }

    /**
     * Test database connection
     * @return bool
     */
    public static function test(): bool
    {
        try {
            $pdo = self::get();
            $pdo->query('SELECT 1');
            self::release($pdo);
            return true;
        } catch (DatabaseException $e) {
            return false;
        }
    }
}
