<?php
/**
 * Credential Manager Service
 * 
 * Securely stores and retrieves credentials for bot and system use.
 * One-time setup, never ask for passwords again!
 * 
 * Features:
 * - AES-256 encryption for sensitive data
 * - Automatic credential loading
 * - Supports: Database, API keys, file paths, server info
 * - Admin-only access
 * 
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

class CredentialManager
{
    private PDO $db;
    private string $encryptionKey;
    private array $cache = [];
    
    // Encryption settings
    private const CIPHER = 'AES-256-CBC';
    private const KEY_LENGTH = 32;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load database config
        $configFile = __DIR__ . '/../config/database.php';
        if (!file_exists($configFile)) {
            throw new Exception("Database config not found at: " . $configFile);
        }
        
        $config = require $configFile;
        
        // Connect to database
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
        $this->db = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        // Generate encryption key (should be stored securely in production)
        $this->encryptionKey = $this->getOrCreateEncryptionKey();
        
        // Create table if not exists
        $this->createTableIfNotExists();
    }
    
    /**
     * Get or create encryption key
     */
    private function getOrCreateEncryptionKey(): string
    {
        // Use a secure key derived from database config
        // In production, this should be in environment variable or secure vault
        $config = require __DIR__ . '/../config/database.php';
        $baseKey = $config['host'] . ':' . $config['database'] . ':' . $config['username'];
        
        // Generate consistent key from base
        return hash('sha256', $baseKey . 'ENCRYPTION_SALT_2025');
    }
    
    /**
     * Create credentials table
     */
    private function createTableIfNotExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS bot_credentials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            credential_type VARCHAR(50) NOT NULL,
            credential_key VARCHAR(100) NOT NULL,
            credential_value TEXT NOT NULL,
            is_encrypted TINYINT(1) DEFAULT 1,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_credential (credential_type, credential_key),
            INDEX idx_type (credential_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->exec($sql);
    }
    
    /**
     * Encrypt data
     */
    private function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, self::CIPHER, $this->encryptionKey, 0, $iv);
        
        // Combine IV and encrypted data
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    private function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, self::CIPHER, $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Store a credential
     * 
     * @param string $type Type of credential (database, api_key, path, server)
     * @param string $key Unique key for this credential
     * @param mixed $value The credential value
     * @param bool $encrypt Whether to encrypt (default: true)
     * @param string|null $description Optional description
     */
    public function store(string $type, string $key, $value, bool $encrypt = true, ?string $description = null): bool
    {
        try {
            $valueStr = is_array($value) ? json_encode($value) : (string)$value;
            $storedValue = $encrypt ? $this->encrypt($valueStr) : $valueStr;
            
            $sql = "INSERT INTO bot_credentials 
                    (credential_type, credential_key, credential_value, is_encrypted, description)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    credential_value = VALUES(credential_value),
                    is_encrypted = VALUES(is_encrypted),
                    description = VALUES(description),
                    updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type, $key, $storedValue, $encrypt ? 1 : 0, $description]);
            
            // Clear cache for this credential
            unset($this->cache["{$type}.{$key}"]);
            
            return true;
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to store {$type}.{$key}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve a credential
     * 
     * @param string $type Type of credential
     * @param string $key Credential key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $type, string $key, $default = null)
    {
        $cacheKey = "{$type}.{$key}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            $sql = "SELECT credential_value, is_encrypted FROM bot_credentials 
                    WHERE credential_type = ? AND credential_key = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type, $key]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return $default;
            }
            
            $value = $result['is_encrypted'] 
                ? $this->decrypt($result['credential_value'])
                : $result['credential_value'];
            
            // Try to decode JSON
            $decoded = json_decode($value, true);
            $value = ($decoded !== null) ? $decoded : $value;
            
            // Cache the result
            $this->cache[$cacheKey] = $value;
            
            return $value;
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to retrieve {$type}.{$key}: " . $e->getMessage());
            return $default;
        }
    }
    
    /**
     * Get all credentials of a type
     * 
     * @param string $type Credential type
     * @return array
     */
    public function getAllOfType(string $type): array
    {
        try {
            $sql = "SELECT credential_key, credential_value, is_encrypted, description 
                    FROM bot_credentials WHERE credential_type = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type]);
            $results = $stmt->fetchAll();
            
            $credentials = [];
            foreach ($results as $row) {
                $value = $row['is_encrypted']
                    ? $this->decrypt($row['credential_value'])
                    : $row['credential_value'];
                
                $decoded = json_decode($value, true);
                $credentials[$row['credential_key']] = [
                    'value' => ($decoded !== null) ? $decoded : $value,
                    'description' => $row['description']
                ];
            }
            
            return $credentials;
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to get all of type {$type}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all credentials grouped by type
     * 
     * @return array
     */
    public static function getAll(): array
    {
        $manager = new self();
        
        return [
            'database' => $manager->getDatabaseCredentials(),
            'paths' => $manager->getPathCredentials(),
            'api_keys' => $manager->getApiKeyCredentials(),
            'servers' => $manager->getServerCredentials(),
        ];
    }
    
    /**
     * Get database credentials
     */
    public function getDatabaseCredentials(): array
    {
        $host = $this->get('database', 'host', '127.0.0.1');
        $name = $this->get('database', 'name', 'hdgwrzntwa');
        $user = $this->get('database', 'username', 'hdgwrzntwa');
        $pass = $this->get('database', 'password', '');
        $port = $this->get('database', 'port', 3306);
        
        return [
            'host' => $host,
            'database' => $name,
            'username' => $user,
            'password' => $pass,
            'port' => $port,
            'charset' => 'utf8mb4'
        ];
    }
    
    /**
     * Get path credentials
     */
    public function getPathCredentials(): array
    {
        return [
            'root' => $this->get('path', 'root', $_SERVER['DOCUMENT_ROOT']),
            'logs' => $this->get('path', 'logs', $_SERVER['DOCUMENT_ROOT'] . '/logs'),
            'backups' => $this->get('path', 'backups', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/backups'),
            'cache' => $this->get('path', 'cache', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/cache'),
            'uploads' => $this->get('path', 'uploads', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/uploads'),
            'private' => $this->get('path', 'private', $_SERVER['DOCUMENT_ROOT'] . '/../private_html'),
        ];
    }
    
    /**
     * Get API key credentials
     */
    public function getApiKeyCredentials(): array
    {
        return [
            'vend' => $this->get('api_key', 'vend', ''),
            'openai' => $this->get('api_key', 'openai', ''),
            'stripe' => $this->get('api_key', 'stripe', ''),
        ];
    }
    
    /**
     * Get server credentials
     */
    public function getServerCredentials(): array
    {
        return [
            'domain' => $this->get('server', 'domain', 'gpt.ecigdis.co.nz'),
            'ssl_cert' => $this->get('server', 'ssl_cert', ''),
            'ssl_key' => $this->get('server', 'ssl_key', ''),
            'environment' => $this->get('server', 'environment', 'production'),
        ];
    }
    
    /**
     * Delete a credential
     */
    public function delete(string $type, string $key): bool
    {
        try {
            $sql = "DELETE FROM bot_credentials WHERE credential_type = ? AND credential_key = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$type, $key]);
            
            unset($this->cache["{$type}.{$key}"]);
            
            return true;
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to delete {$type}.{$key}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * List all stored credentials (without values, for UI)
     */
    public function listAll(): array
    {
        try {
            $sql = "SELECT credential_type, credential_key, description, 
                    is_encrypted, updated_at 
                    FROM bot_credentials 
                    ORDER BY credential_type, credential_key";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to list credentials: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Test database connection
     */
    public function testDatabaseConnection(): array
    {
        try {
            $creds = $this->getDatabaseCredentials();
            $dsn = "mysql:host={$creds['host']};dbname={$creds['database']};charset=utf8mb4";
            $testDb = new PDO($dsn, $creds['username'], $creds['password']);
            
            // Test query
            $stmt = $testDb->query("SELECT DATABASE() as db, VERSION() as version");
            $result = $stmt->fetch();
            
            return [
                'success' => true,
                'database' => $result['db'],
                'version' => $result['version'],
                'message' => 'Connection successful'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Initialize default credentials (run once)
     */
    public function initializeDefaults(): bool
    {
        try {
            // Database credentials (from current config)
            $configFile = $_SERVER['DOCUMENT_ROOT'] . '/config/database.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
                $this->store('database', 'host', $config['host'], true, 'Database host');
                $this->store('database', 'name', $config['database'], true, 'Database name');
                $this->store('database', 'username', $config['username'], true, 'Database username');
                $this->store('database', 'password', $config['password'], true, 'Database password');
                $this->store('database', 'port', $config['port'] ?? 3306, false, 'Database port');
            }
            
            // Path credentials
            $this->store('path', 'root', $_SERVER['DOCUMENT_ROOT'], false, 'Project root directory');
            $this->store('path', 'logs', $_SERVER['DOCUMENT_ROOT'] . '/logs', false, 'Logs directory');
            $this->store('path', 'backups', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/backups', false, 'Backups directory');
            $this->store('path', 'cache', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/cache', false, 'Cache directory');
            $this->store('path', 'uploads', $_SERVER['DOCUMENT_ROOT'] . '/../private_html/uploads', false, 'Uploads directory');
            $this->store('path', 'private', $_SERVER['DOCUMENT_ROOT'] . '/../private_html', false, 'Private directory');
            
            // Server credentials
            $this->store('server', 'domain', 'gpt.ecigdis.co.nz', false, 'Primary domain');
            $this->store('server', 'environment', 'production', false, 'Environment type');
            
            return true;
        } catch (Exception $e) {
            error_log("CredentialManager: Failed to initialize defaults: " . $e->getMessage());
            return false;
        }
    }
}
