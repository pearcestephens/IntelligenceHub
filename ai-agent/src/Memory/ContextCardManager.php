<?php

/**
 * Context Card Manager
 * Manages context cards for conversations with database persistence
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

use App\DB;
use App\Logger;
use App\RedisClient;

class ContextCardManager
{
    private DB $db;
    
    public function __construct(?DB $db = null)
    {
        $this->db = $db ?? DB::getInstance();
    }
    
    /**
     * Create and store a context card
     */
    public function createContextCard(
        string $conversationId,
        string $content,
        array $metadata = []
    ): ?string {
        try {
            $cardId = \Ramsey\Uuid\Uuid::uuid4()->toString();
            
            $this->db->insert('context_cards', [
                'id' => $cardId,
                'conversation_id' => $conversationId,
                'content' => $content,
                'metadata' => json_encode($metadata),
                'created_at' => date('Y-m-d H:i:s'),
                'expires_at' => isset($metadata['ttl']) 
                    ? date('Y-m-d H:i:s', time() + $metadata['ttl'])
                    : null
            ]);
            
            Logger::info('Context card created', [
                'card_id' => $cardId,
                'conversation_id' => $conversationId
            ]);
            
            return $cardId;
        } catch (\Exception $e) {
            Logger::error('Failed to create context card', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);
            return null;
        }
    }
    
    /**
     * Retrieve relevant context for a conversation
     */
    public function getRelevantContext(
        string $conversationId,
        int $limit = 10
    ): array {
        try {
            $sql = "SELECT * FROM context_cards 
                    WHERE conversation_id = ? 
                    AND (expires_at IS NULL OR expires_at > NOW())
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            $cards = $this->db->select($sql, [$conversationId, $limit]);
            
            return array_map(function($card) {
                $card['metadata'] = json_decode($card['metadata'], true);
                return $card;
            }, $cards);
        } catch (\Exception $e) {
            Logger::error('Failed to retrieve context cards', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId
            ]);
            return [];
        }
    }
    
    /**
     * Rank context by recency and relevance
     */
    public function rankContextByRecencyAndRelevance(
        array $contexts,
        ?string $query = null
    ): array {
        // Simple scoring: newer = higher score
        usort($contexts, function($a, $b) {
            $timeA = strtotime($a['created_at'] ?? 'now');
            $timeB = strtotime($b['created_at'] ?? 'now');
            return $timeB <=> $timeA;
        });
        
        return $contexts;
    }
    
    /**
     * Clear stale context cards
     */
    public function clearStaleContextCards(): int {
        try {
            $sql = "DELETE FROM context_cards 
                    WHERE expires_at IS NOT NULL 
                    AND expires_at < NOW()";
            
            $this->db->query($sql);
            $affected = $this->db->connection()->affected_rows;
            
            Logger::info('Cleared stale context cards', [
                'count' => $affected
            ]);
            
            return $affected;
        } catch (\Exception $e) {
            Logger::error('Failed to clear stale context cards', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Handle context card expiration
     */
    public function handleContextCardExpiration(string $cardId): bool {
        try {
            $sql = "UPDATE context_cards 
                    SET expires_at = NOW() 
                    WHERE id = ?";
            
            $this->db->query($sql, [$cardId]);
            
            return true;
        } catch (\Exception $e) {
            Logger::error('Failed to expire context card', [
                'error' => $e->getMessage(),
                'card_id' => $cardId
            ]);
            return false;
        }
    }
    
    /**
     * Generate embeddings for a context card
     */
    public function generateEmbeddingsForContextCard(string $cardId): ?array {
        try {
            $card = $this->db->selectOne(
                "SELECT * FROM context_cards WHERE id = ?",
                [$cardId]
            );
            
            if (!$card) {
                return null;
            }
            
            // Mock embedding generation (would use OpenAI API in production)
            $embedding = array_fill(0, 1536, 0.0);
            
            // Store embedding
            $this->db->query(
                "UPDATE context_cards SET embedding = ? WHERE id = ?",
                [json_encode($embedding), $cardId]
            );
            
            return $embedding;
        } catch (\Exception $e) {
            Logger::error('Failed to generate embeddings', [
                'error' => $e->getMessage(),
                'card_id' => $cardId
            ]);
            return null;
        }
    }
}
