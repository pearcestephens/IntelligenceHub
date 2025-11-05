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
use IntelligenceHub\MCP\Tools\PasswordStorageTool;

class Connection
{
    private static array $instances = [];
    private static array $configs = [
        'hdgwrzntwa' => [
            'host' => '127.0.0.1',
            'database' => 'hdgwrzntwa',
            'charset' => 'utf8mb4',
        ],
        'jcepnzzkmj' => [
            'host' => '127.0.0.1',
            'database' => 'jcepnzzkmj',
            'charset' => 'utf8mb4',
        ],
    ];
    private static array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * Get singleton PDO instance for a specific database
     *
     * @param string $dbName 'hdgwrzntwa' or 'jcepnzzkmj'
     * @return PDO
     */
    public static function getInstance(string $dbName = 'hdgwrzntwa'): PDO
    {
        if (!isset(self::$instances[$dbName])) {
            self::$instances[$dbName] = self::createConnection($dbName);
        }

        return self::$instances[$dbName];
    }

    /**
     * Create new PDO connection
     */
    private static function createConnection(string $dbName): PDO
    {
        if (!isset(self::$configs[$dbName])) {
            throw new \RuntimeException("Unknown database configuration: {$dbName}");
        }

        $config = self::$configs[$dbName];

        // Temporarily create a raw connection to fetch credentials
        // This avoids a circular dependency with PasswordStorageTool
        $tempDsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $config['host'], 'hdgwrzntwa', $config['charset']);
        $tempPdo = new PDO($tempDsn, 'hdgwrzntwa', 'bFUdRjh4Jx', self::$options);

        $credStmt = $tempPdo->prepare("SELECT encrypted_password, username FROM mcp_secure_credentials WHERE service = ?");
        $credStmt->execute(["db_{$dbName}"]);
        $creds = $credStmt->fetch(PDO::FETCH_ASSOC);

        if (!$creds) {
             throw new \RuntimeException("Credentials for '{$dbName}' not found in secure storage.");
        }

        $encryptionKey = $_ENV['CREDENTIAL_ENCRYPTION_KEY'] ?? hash('sha256', $_ENV['DB_PASS'] ?? 'default_key_change_me');
        $data = base64_decode($creds['encrypted_password']);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        $password = openssl_decrypt($ciphertext, 'AES-256-CBC', $encryptionKey, 0, $iv);
        $username = $creds['username'];

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['database'],
            $config['charset']
        );

        try {
            return new PDO(
                $dsn,
                $username,
                $password,
                self::$options
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

            $config = self::$configs['hdgwrzntwa'] ?? [];

            return [
                'connected' => true,
                'host' => $config['host'] ?? '127.0.0.1',
                'database' => $config['database'] ?? 'hdgwrzntwa',
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
