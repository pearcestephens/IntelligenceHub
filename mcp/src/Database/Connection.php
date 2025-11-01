<?php
/**
 * Database Configuration and Connection Manager
 *
 * Reuses existing database credentials from server_v2_complete.php
 * Provides singleton PDO connection for all v3 components
 *
 * @package IntelligenceHub\MCP\Database
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;
    private static array $config = [
        'host' => '127.0.0.1',
        'database' => 'hdgwrzntwa',
        'username' => 'hdgwrzntwa',
        'password' => 'bFUdRjh4Jx',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    ];

    /**
     * Get singleton PDO instance
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    /**
     * Create new PDO connection
     */
    private static function createConnection(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::$config['host'],
            self::$config['database'],
            self::$config['charset']
        );

        try {
            return new PDO(
                $dsn,
                self::$config['username'],
                self::$config['password'],
                self::$config['options']
            );
        } catch (PDOException $e) {
            throw new \RuntimeException(
                "Database connection failed: " . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }

    /**
     * Test database connection
     */
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getInstance();
            $pdo->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get connection statistics
     */
    public static function getStats(): array
    {
        try {
            $pdo = self::getInstance();
            $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'");
            $connections = $stmt->fetch();

            return [
                'connected' => true,
                'host' => self::$config['host'],
                'database' => self::$config['database'],
                'threads_connected' => (int)($connections['Value'] ?? 0),
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
