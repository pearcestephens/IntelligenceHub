<?php

declare(strict_types=1);

namespace App\Memory;

use App\DB;
use App\Logger;
use App\Util\Validate;
use Exception;

/**
 * Multi-Domain Management System
 *
 * Provides domain-aware knowledge base access with:
 * - Domain switching (global, staff, web, gpt, wiki, superadmin)
 * - GOD MODE (access all documents across all domains at 100% relevance)
 * - Domain-specific document filtering
 * - Usage analytics and query logging
 * - Real-time domain statistics
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 * @version 1.0.0
 */
class MultiDomain
{
    /** Domain IDs */
    public const DOMAIN_GLOBAL = 1;
    public const DOMAIN_STAFF = 2;
    public const DOMAIN_WEB = 3;
    public const DOMAIN_GPT = 4;
    public const DOMAIN_WIKI = 5;
    public const DOMAIN_SUPERADMIN = 6;

    /** Domain names */
    private const DOMAIN_NAMES = [
        self::DOMAIN_GLOBAL => 'global',
        self::DOMAIN_STAFF => 'staff',
        self::DOMAIN_WEB => 'web',
        self::DOMAIN_GPT => 'gpt',
        self::DOMAIN_WIKI => 'wiki',
        self::DOMAIN_SUPERADMIN => 'superadmin'
    ];

    /** GOD MODE relevance threshold */
    private const GOD_MODE_RELEVANCE = 1.0;

    /**
     * Switch conversation to a specific domain
     *
     * @param string $conversationId Conversation UUID
     * @param int $domainId Domain ID (1-6)
     * @return bool Success
     */
    public static function switchDomain(string $conversationId, int $domainId): bool
    {
        Validate::uuid($conversationId);

        if (!isset(self::DOMAIN_NAMES[$domainId])) {
            throw new Exception("Invalid domain ID: {$domainId}");
        }

        try {
            // Call stored procedure to switch domain
            DB::execute('CALL sp_switch_domain(?, ?)', [$conversationId, $domainId]);

            Logger::info('Domain switched', [
                'conversation_id' => $conversationId,
                'domain_id' => $domainId,
                'domain_name' => self::DOMAIN_NAMES[$domainId]
            ]);

            return true;
        } catch (\Throwable $e) {
            Logger::error('Failed to switch domain', [
                'conversation_id' => $conversationId,
                'domain_id' => $domainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Enable GOD MODE for conversation (access ALL documents at 100% relevance)
     *
     * @param string $conversationId Conversation UUID
     * @return bool Success
     */
    public static function enableGodMode(string $conversationId): bool
    {
        Validate::uuid($conversationId);

        try {
            // Call stored procedure to enable GOD MODE
            DB::execute('CALL sp_enable_god_mode(?)', [$conversationId]);

            Logger::info('GOD MODE enabled', [
                'conversation_id' => $conversationId,
                'security_note' => 'All 342 documents accessible at 100% relevance'
            ]);

            return true;
        } catch (\Throwable $e) {
            Logger::error('Failed to enable GOD MODE', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Disable GOD MODE for conversation
     *
     * @param string $conversationId Conversation UUID
     * @return bool Success
     */
    public static function disableGodMode(string $conversationId): bool
    {
        Validate::uuid($conversationId);

        try {
            $result = DB::execute(
                'UPDATE agent_conversations
                 SET god_mode_enabled = FALSE,
                     updated_at = NOW()
                 WHERE conversation_id = ?',
                [$conversationId]
            );

            Logger::info('GOD MODE disabled', [
                'conversation_id' => $conversationId
            ]);

            return $result > 0;
        } catch (\Throwable $e) {
            Logger::error('Failed to disable GOD MODE', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get current domain for conversation
     *
     * @param string $conversationId Conversation UUID
     * @return array|null Domain info (id, name, god_mode_enabled)
     */
    public static function getCurrentDomain(string $conversationId): ?array
    {
        Validate::uuid($conversationId);

        try {
            $result = DB::selectOne(
                'SELECT
                    ac.active_domain_id,
                    adr.name as domain_name,
                    ac.god_mode_enabled,
                    ac.domain_switch_count
                 FROM agent_conversations ac
                 LEFT JOIN ai_kb_domain_registry adr ON ac.active_domain_id = adr.domain_id
                 WHERE ac.conversation_id = ?',
                [$conversationId]
            );

            return $result ?: null;
        } catch (\Throwable $e) {
            Logger::error('Failed to get current domain', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get documents accessible in current domain
     *
     * @param string $conversationId Conversation UUID
     * @param int|null $limit Max results
     * @return array Document IDs with relevance scores
     */
    public static function getAccessibleDocuments(string $conversationId, ?int $limit = null): array
    {
        Validate::uuid($conversationId);

        try {
            $domain = self::getCurrentDomain($conversationId);
            if (!$domain) {
                return [];
            }

            // If GOD MODE, return all documents at 100% relevance
            if ($domain['god_mode_enabled']) {
                $sql = 'SELECT
                            kd.id as doc_id,
                            kd.title,
                            kd.type,
                            1.0 as relevance_score
                        FROM agent_kb_docs kd
                        WHERE kd.deleted_at IS NULL';

                if ($limit) {
                    $sql .= ' LIMIT ' . (int)$limit;
                }

                return DB::select($sql);
            }

            // Normal mode: domain-specific documents
            $sql = 'SELECT
                        dm.doc_id,
                        kd.title,
                        kd.type,
                        dm.relevance_score
                    FROM ai_kb_doc_domain_map dm
                    JOIN agent_kb_docs kd ON dm.doc_id = kd.id
                    WHERE dm.domain_id = ?
                      AND kd.deleted_at IS NULL
                    ORDER BY dm.relevance_score DESC';

            if ($limit) {
                $sql .= ' LIMIT ' . (int)$limit;
            }

            return DB::select($sql, [$domain['active_domain_id']]);
        } catch (\Throwable $e) {
            Logger::error('Failed to get accessible documents', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Search knowledge base with domain awareness
     *
     * @param string $conversationId Conversation UUID
     * @param string $query Search query
     * @param int $limit Max results
     * @param float|null $minSimilarity Minimum similarity threshold
     * @return array Search results filtered by domain
     */
    public static function domainAwareSearch(
        string $conversationId,
        string $query,
        int $limit = 5,
        ?float $minSimilarity = 0.7
    ): array {
        Validate::uuid($conversationId);
        Validate::string($query, 1);

        try {
            $domain = self::getCurrentDomain($conversationId);
            if (!$domain) {
                Logger::warning('No domain found for conversation', [
                    'conversation_id' => $conversationId
                ]);
                return [];
            }

            // Perform standard KB search
            $allResults = KnowledgeBase::search($query, $limit * 3, null, $minSimilarity);

            // Filter by domain accessibility
            $accessibleDocs = self::getAccessibleDocuments($conversationId);
            $accessibleDocIds = array_column($accessibleDocs, 'doc_id');

            $filteredResults = [];
            foreach ($allResults as $result) {
                if (in_array($result['doc_id'], $accessibleDocIds)) {
                    $filteredResults[] = $result;
                    if (count($filteredResults) >= $limit) {
                        break;
                    }
                }
            }

            // Log the query
            self::logDomainQuery(
                $domain['active_domain_id'],
                $conversationId,
                $query,
                count($filteredResults),
                $domain['god_mode_enabled']
            );

            Logger::info('Domain-aware search completed', [
                'conversation_id' => $conversationId,
                'domain_id' => $domain['active_domain_id'],
                'domain_name' => $domain['domain_name'],
                'god_mode' => $domain['god_mode_enabled'],
                'query_length' => strlen($query),
                'results_count' => count($filteredResults)
            ]);

            return $filteredResults;
        } catch (\Throwable $e) {
            Logger::error('Domain-aware search failed', [
                'conversation_id' => $conversationId,
                'query' => substr($query, 0, 100),
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Log domain query to audit table
     *
     * @param int $domainId Domain ID
     * @param string $conversationId Conversation UUID
     * @param string $query Query text
     * @param int $resultCount Number of results
     * @param bool $godModeActive GOD MODE status
     */
    private static function logDomainQuery(
        int $domainId,
        string $conversationId,
        string $query,
        int $resultCount,
        bool $godModeActive
    ): void {
        try {
            DB::execute(
                'CALL sp_log_domain_query(?, ?, ?, ?, ?, ?)',
                [
                    $domainId,
                    $conversationId,
                    $query,
                    $resultCount,
                    0, // response_time_ms (to be implemented)
                    $godModeActive ? 1 : 0
                ]
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to log domain query', [
                'domain_id' => $domainId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get domain statistics
     *
     * @param int|null $domainId Specific domain or all domains
     * @return array Domain statistics
     */
    public static function getDomainStats(?int $domainId = null): array
    {
        try {
            if ($domainId !== null) {
                return DB::selectOne(
                    'SELECT * FROM v_domain_stats_live WHERE domain_id = ?',
                    [$domainId]
                ) ?: [];
            }

            return DB::select('SELECT * FROM v_domain_stats_live ORDER BY domain_id');
        } catch (\Throwable $e) {
            Logger::error('Failed to get domain stats', [
                'domain_id' => $domainId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get GOD MODE overview
     *
     * @return array GOD MODE statistics
     */
    public static function getGodModeOverview(): array
    {
        try {
            return DB::selectOne('SELECT * FROM v_god_mode_overview') ?: [];
        } catch (\Throwable $e) {
            Logger::error('Failed to get GOD MODE overview', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Add document to domain
     *
     * @param string $docId Document UUID
     * @param int $domainId Domain ID
     * @param float $relevanceScore Relevance score (0.0 to 1.0)
     * @return bool Success
     */
    public static function addDocumentToDomain(
        string $docId,
        int $domainId,
        float $relevanceScore = 1.0
    ): bool {
        Validate::uuid($docId);

        if (!isset(self::DOMAIN_NAMES[$domainId])) {
            throw new Exception("Invalid domain ID: {$domainId}");
        }

        if ($relevanceScore < 0 || $relevanceScore > 1) {
            throw new Exception("Relevance score must be between 0.0 and 1.0");
        }

        try {
            DB::execute(
                'INSERT INTO ai_kb_doc_domain_map
                    (doc_id, domain_id, relevance_score, created_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE
                    relevance_score = VALUES(relevance_score),
                    updated_at = NOW()',
                [$docId, $domainId, $relevanceScore]
            );

            Logger::info('Document added to domain', [
                'doc_id' => $docId,
                'domain_id' => $domainId,
                'domain_name' => self::DOMAIN_NAMES[$domainId],
                'relevance_score' => $relevanceScore
            ]);

            return true;
        } catch (\Throwable $e) {
            Logger::error('Failed to add document to domain', [
                'doc_id' => $docId,
                'domain_id' => $domainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove document from domain
     *
     * @param string $docId Document UUID
     * @param int $domainId Domain ID
     * @return bool Success
     */
    public static function removeDocumentFromDomain(string $docId, int $domainId): bool
    {
        Validate::uuid($docId);

        try {
            $result = DB::execute(
                'DELETE FROM ai_kb_doc_domain_map
                 WHERE doc_id = ? AND domain_id = ?',
                [$docId, $domainId]
            );

            Logger::info('Document removed from domain', [
                'doc_id' => $docId,
                'domain_id' => $domainId
            ]);

            return $result > 0;
        } catch (\Throwable $e) {
            Logger::error('Failed to remove document from domain', [
                'doc_id' => $docId,
                'domain_id' => $domainId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get domain name by ID
     *
     * @param int $domainId Domain ID
     * @return string Domain name
     */
    public static function getDomainName(int $domainId): string
    {
        return self::DOMAIN_NAMES[$domainId] ?? 'unknown';
    }

    /**
     * Get domain ID by name
     *
     * @param string $domainName Domain name
     * @return int|null Domain ID or null if not found
     */
    public static function getDomainIdByName(string $domainName): ?int
    {
        $domainId = array_search(strtolower($domainName), self::DOMAIN_NAMES);
        return $domainId !== false ? $domainId : null;
    }

    /**
     * Get all available domains
     *
     * @return array Domains list
     */
    public static function getAllDomains(): array
    {
        try {
            return DB::select(
                'SELECT domain_id, name, description, is_active, created_at
                 FROM ai_kb_domain_registry
                 WHERE is_active = TRUE
                 ORDER BY domain_id'
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to get all domains', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
