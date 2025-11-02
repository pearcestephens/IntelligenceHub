<?php
declare(strict_types=1);

namespace AiAgent\Services;

use PDO;
use RuntimeException;

/**
 * MemoryService - Scoped persistent memory for AI agents
 *
 * Manages durable facts, preferences, and learned information across:
 * - User scope: Personal preferences, history (e.g., "prefers dark mode")
 * - Session scope: Temporary context (e.g., "working on project X")
 * - Conversation scope: Topic-specific memory (e.g., "debugging auth bug")
 * - Project scope: Project-level facts (e.g., "CIS uses PHP 8.1")
 * - Global scope: System-wide knowledge (e.g., "NZ business hours are 9am-5pm")
 *
 * Features:
 * - Automatic expiration (TTL)
 * - Importance levels (low, medium, high, critical)
 * - JSON value storage (flexible data structures)
 * - Scope isolation (prevents leakage)
 *
 * Usage:
 * ```php
 * $mem = new MemoryService($pdo);
 * $mem->store('user', 'user123', 'preferred_language', ['value' => 'php'], 'high');
 * $value = $mem->get('user', 'user123', 'preferred_language');
 * $allUserMemories = $mem->getAll('user', 'user123');
 * ```
 *
 * @package AiAgent\Services
 * @version 1.0.0
 */
class MemoryService
{
    private PDO $db;

    private const SCOPES = ['user', 'session', 'conversation', 'project', 'global'];
    private const IMPORTANCE_LEVELS = ['low', 'medium', 'high', 'critical'];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Store memory
     *
     * @param string $scope 'user', 'session', 'conversation', 'project', 'global'
     * @param string $scopeIdentifier user_id, session_id, conversation_id, project_name, or "global"
     * @param string $keyName Memory key (e.g., "preferred_language", "last_query")
     * @param array $value Structured memory content (will be JSON encoded)
     * @param string $importance 'low', 'medium', 'high', 'critical'
     * @param int|null $ttlSeconds Time to live in seconds (null = no expiration)
     * @return int memory_id
     * @throws RuntimeException on invalid scope/importance or database error
     */
    public function store(
        string $scope,
        string $scopeIdentifier,
        string $keyName,
        array $value,
        string $importance = 'medium',
        ?int $ttlSeconds = null
    ): int {
        $this->validateScope($scope);
        $this->validateImportance($importance);

        $expiresAt = $ttlSeconds ? date('Y-m-d H:i:s', time() + $ttlSeconds) : null;

        $stmt = $this->db->prepare("
            INSERT INTO ai_memory
            (scope, scope_identifier, key_name, value, importance, expires_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                value = VALUES(value),
                importance = VALUES(importance),
                expires_at = VALUES(expires_at),
                updated_at = NOW()
        ");

        $stmt->execute([
            $scope,
            $scopeIdentifier,
            $keyName,
            json_encode($value),
            $importance,
            $expiresAt
        ]);

        // Return ID (either new or updated)
        if ($this->db->lastInsertId()) {
            return (int) $this->db->lastInsertId();
        }

        // If update, fetch the existing ID
        $stmt = $this->db->prepare("
            SELECT memory_id FROM ai_memory
            WHERE scope = ? AND scope_identifier = ? AND key_name = ?
        ");
        $stmt->execute([$scope, $scopeIdentifier, $keyName]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retrieve memory value
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @param string $keyName
     * @return array|null Memory value (decoded from JSON) or null if not found/expired
     */
    public function get(string $scope, string $scopeIdentifier, string $keyName): ?array
    {
        $stmt = $this->db->prepare("
            SELECT value
            FROM ai_memory
            WHERE scope = ?
              AND scope_identifier = ?
              AND key_name = ?
              AND (expires_at IS NULL OR expires_at > NOW())
        ");

        $stmt->execute([$scope, $scopeIdentifier, $keyName]);
        $result = $stmt->fetchColumn();

        return $result ? json_decode($result, true) : null;
    }

    /**
     * Get all memories for a scope
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @param string|null $minImportance Filter by minimum importance (e.g., 'high' returns high+critical)
     * @return array Array of memories with keys: memory_id, key_name, value, importance, created_at, updated_at
     */
    public function getAll(
        string $scope,
        string $scopeIdentifier,
        ?string $minImportance = null
    ): array {
        $this->validateScope($scope);

        $sql = "
            SELECT memory_id, key_name, value, importance, expires_at, created_at, updated_at
            FROM ai_memory
            WHERE scope = ?
              AND scope_identifier = ?
              AND (expires_at IS NULL OR expires_at > NOW())
        ";

        $params = [$scope, $scopeIdentifier];

        if ($minImportance) {
            $this->validateImportance($minImportance);
            $importanceHierarchy = ['low', 'medium', 'high', 'critical'];
            $minIndex = array_search($minImportance, $importanceHierarchy);
            $validImportances = array_slice($importanceHierarchy, $minIndex);

            $placeholders = implode(',', array_fill(0, count($validImportances), '?'));
            $sql .= " AND importance IN ($placeholders)";
            $params = array_merge($params, $validImportances);
        }

        $sql .= " ORDER BY importance DESC, updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $memories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON values
        foreach ($memories as &$memory) {
            $memory['value'] = json_decode($memory['value'], true);
        }

        return $memories;
    }

    /**
     * Delete specific memory
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @param string $keyName
     * @return bool True if deleted, false if not found
     */
    public function delete(string $scope, string $scopeIdentifier, string $keyName): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM ai_memory
            WHERE scope = ? AND scope_identifier = ? AND key_name = ?
        ");

        $stmt->execute([$scope, $scopeIdentifier, $keyName]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete all memories for a scope
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @return int Number of memories deleted
     */
    public function deleteAll(string $scope, string $scopeIdentifier): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM ai_memory
            WHERE scope = ? AND scope_identifier = ?
        ");

        $stmt->execute([$scope, $scopeIdentifier]);
        return $stmt->rowCount();
    }

    /**
     * Clean up expired memories (run via cron)
     *
     * @return int Number of expired memories deleted
     */
    public function cleanExpired(): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM ai_memory
            WHERE expires_at IS NOT NULL AND expires_at < NOW()
        ");

        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Get active memories from view (includes all scopes, non-expired)
     *
     * @param string|null $scope Optional scope filter
     * @param string|null $importance Optional importance filter
     * @param int $limit Max results
     * @return array
     */
    public function getActive(
        ?string $scope = null,
        ?string $importance = null,
        int $limit = 100
    ): array {
        $sql = "SELECT * FROM v_ai_memory_active WHERE 1=1";
        $params = [];

        if ($scope) {
            $this->validateScope($scope);
            $sql .= " AND scope = ?";
            $params[] = $scope;
        }

        if ($importance) {
            $this->validateImportance($importance);
            $sql .= " AND importance = ?";
            $params[] = $importance;
        }

        $sql .= " LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $memories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON values
        foreach ($memories as &$memory) {
            $memory['value'] = json_decode($memory['value'], true);
        }

        return $memories;
    }

    /**
     * Update memory importance
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @param string $keyName
     * @param string $newImportance
     * @return bool Success
     */
    public function updateImportance(
        string $scope,
        string $scopeIdentifier,
        string $keyName,
        string $newImportance
    ): bool {
        $this->validateImportance($newImportance);

        $stmt = $this->db->prepare("
            UPDATE ai_memory
            SET importance = ?, updated_at = NOW()
            WHERE scope = ? AND scope_identifier = ? AND key_name = ?
        ");

        $stmt->execute([$newImportance, $scope, $scopeIdentifier, $keyName]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Extend memory expiration
     *
     * @param string $scope
     * @param string $scopeIdentifier
     * @param string $keyName
     * @param int $additionalSeconds Seconds to add to current expiration
     * @return bool Success
     */
    public function extendExpiration(
        string $scope,
        string $scopeIdentifier,
        string $keyName,
        int $additionalSeconds
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE ai_memory
            SET expires_at = DATE_ADD(COALESCE(expires_at, NOW()), INTERVAL ? SECOND),
                updated_at = NOW()
            WHERE scope = ? AND scope_identifier = ? AND key_name = ?
        ");

        $stmt->execute([$additionalSeconds, $scope, $scopeIdentifier, $keyName]);
        return $stmt->rowCount() > 0;
    }

    private function validateScope(string $scope): void
    {
        if (!in_array($scope, self::SCOPES)) {
            throw new RuntimeException(
                "Invalid scope '$scope'. Must be one of: " . implode(', ', self::SCOPES)
            );
        }
    }

    private function validateImportance(string $importance): void
    {
        if (!in_array($importance, self::IMPORTANCE_LEVELS)) {
            throw new RuntimeException(
                "Invalid importance '$importance'. Must be one of: " . implode(', ', self::IMPORTANCE_LEVELS)
            );
        }
    }
}
