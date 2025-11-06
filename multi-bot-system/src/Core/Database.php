<?php
/**
 * Database Connection Handler
 *
 * Manages PDO database connections with connection pooling
 */

declare(strict_types=1);

namespace MultiBot\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Get database instance (singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    /**
     * Initialize database configuration
     */
    public static function init(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Establish database connection
     */
    private static function connect(): void
    {
        if (empty(self::$config)) {
            throw new RuntimeException('Database configuration not initialized');
        }

        $config = self::$config;
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['name'],
            $config['charset']
        );

        try {
            self::$instance = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                $config['options']
            );
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed');
        }
    }

    /**
     * Execute a prepared statement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $db = self::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Insert and return last insert ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        self::query($sql, array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Update rows
     */
    public static function update(string $table, array $data, array $where): int
    {
        $sets = array_map(fn($col) => "$col = ?", array_keys($data));
        $conditions = array_map(fn($col) => "$col = ?", array_keys($where));

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $sets),
            implode(' AND ', $conditions)
        );

        $params = array_merge(array_values($data), array_values($where));
        $stmt = self::query($sql, $params);

        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }
}
