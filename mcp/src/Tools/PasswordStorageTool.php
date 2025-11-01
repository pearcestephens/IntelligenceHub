<?php
/**
 * Password Storage Tool
 *
 * Securely stores and retrieves credentials with encryption
 *
 * @package IntelligenceHub\MCP\Tools
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use IntelligenceHub\MCP\Database\Connection;
use PDO;
use Exception;

class PasswordStorageTool
{
    private PDO $db;
    private string $encryptionKey;
    private string $tableName = 'mcp_secure_credentials';

    public function __construct()
    {
        $this->db = Connection::getInstance();

        // Get encryption key from environment or generate one
        $this->encryptionKey = $_ENV['CREDENTIAL_ENCRYPTION_KEY'] ??
            hash('sha256', $_ENV['DB_PASS'] ?? 'default_key_change_me');

        $this->ensureTable();
    }

    /**
     * Execute credential operations
     *
     * @param array $params Operation parameters
     * @return array Result with success status
     */
    public function execute(array $params = []): array
    {
        $action = $params['action'] ?? 'list';

        return match($action) {
            'store' => $this->storeCredential($params),
            'retrieve' => $this->retrieveCredential($params),
            'delete' => $this->deleteCredential($params),
            'list' => $this->listCredentials(),
            'update' => $this->updateCredential($params),
            default => [
                'success' => false,
                'error' => "Unknown action: {$action}. Available: store, retrieve, delete, list, update",
            ],
        };
    }

    /**
     * Store a new credential
     */
    private function storeCredential(array $params): array
    {
        $service = $params['service'] ?? '';
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';
        $notes = $params['notes'] ?? '';

        if (empty($service) || empty($password)) {
            return [
                'success' => false,
                'error' => 'service and password parameters are required',
            ];
        }

        try {
            // Check if service already exists
            $stmt = $this->db->prepare("SELECT id FROM {$this->tableName} WHERE service = ?");
            $stmt->execute([$service]);

            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => "Credential for service '{$service}' already exists. Use action=update to modify.",
                ];
            }

            // Encrypt password
            $encryptedPassword = $this->encrypt($password);

            // Store credential
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tableName} (service, username, encrypted_password, notes, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([$service, $username, $encryptedPassword, $notes]);

            return [
                'success' => true,
                'data' => [
                    'service' => $service,
                    'username' => $username,
                    'message' => 'Credential stored securely',
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to store credential: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a credential
     */
    private function retrieveCredential(array $params): array
    {
        $service = $params['service'] ?? '';
        $showPassword = $params['show_password'] ?? false;

        if (empty($service)) {
            return [
                'success' => false,
                'error' => 'service parameter is required',
            ];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT service, username, encrypted_password, notes, created_at, last_accessed
                FROM {$this->tableName}
                WHERE service = ?
            ");

            $stmt->execute([$service]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return [
                    'success' => false,
                    'error' => "Credential for service '{$service}' not found",
                ];
            }

            // Update last accessed time
            $this->db->prepare("UPDATE {$this->tableName} SET last_accessed = NOW() WHERE service = ?")
                ->execute([$service]);

            $result = [
                'service' => $row['service'],
                'username' => $row['username'],
                'notes' => $row['notes'],
                'created_at' => $row['created_at'],
                'last_accessed' => $row['last_accessed'],
            ];

            if ($showPassword) {
                $result['password'] = $this->decrypt($row['encrypted_password']);
            } else {
                $result['password'] = '***HIDDEN***';
            }

            return [
                'success' => true,
                'data' => $result,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to retrieve credential: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * List all stored credentials (without passwords)
     */
    private function listCredentials(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT service, username, notes, created_at, last_accessed
                FROM {$this->tableName}
                ORDER BY service
            ");

            $credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'count' => count($credentials),
                    'credentials' => $credentials,
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to list credentials: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a credential
     */
    private function deleteCredential(array $params): array
    {
        $service = $params['service'] ?? '';

        if (empty($service)) {
            return [
                'success' => false,
                'error' => 'service parameter is required',
            ];
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->tableName} WHERE service = ?");
            $stmt->execute([$service]);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => "Credential for service '{$service}' not found",
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'message' => "Credential for service '{$service}' deleted",
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to delete credential: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing credential
     */
    private function updateCredential(array $params): array
    {
        $service = $params['service'] ?? '';
        $username = $params['username'] ?? null;
        $password = $params['password'] ?? null;
        $notes = $params['notes'] ?? null;

        if (empty($service)) {
            return [
                'success' => false,
                'error' => 'service parameter is required',
            ];
        }

        try {
            $updates = [];
            $values = [];

            if ($username !== null) {
                $updates[] = 'username = ?';
                $values[] = $username;
            }

            if ($password !== null) {
                $updates[] = 'encrypted_password = ?';
                $values[] = $this->encrypt($password);
            }

            if ($notes !== null) {
                $updates[] = 'notes = ?';
                $values[] = $notes;
            }

            if (empty($updates)) {
                return [
                    'success' => false,
                    'error' => 'No fields to update. Provide username, password, or notes.',
                ];
            }

            $updates[] = 'updated_at = NOW()';
            $values[] = $service;

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $updates) . " WHERE service = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);

            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'error' => "Credential for service '{$service}' not found",
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'message' => "Credential for service '{$service}' updated",
                ],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to update credential: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Encrypt a password
     */
    private function encrypt(string $plaintext): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($plaintext, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a password
     */
    private function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $ciphertext = substr($data, 16);
        return openssl_decrypt($ciphertext, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }

    /**
     * Ensure the credentials table exists
     */
    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                service VARCHAR(255) NOT NULL UNIQUE,
                username VARCHAR(255) DEFAULT NULL,
                encrypted_password TEXT NOT NULL,
                notes TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_accessed TIMESTAMP NULL,
                INDEX idx_service (service)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
