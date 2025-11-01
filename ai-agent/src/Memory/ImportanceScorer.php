<?php

namespace App\Memory;

use App\DB;

/**
 * ImportanceScorer
 * 
 * Assigns importance scores (1-10) to conversations and memories
 * based on recency, user engagement, uniqueness, and context relevance.
 * Enables smart pruning of low-value memories.
 */
class ImportanceScorer
{
    private DB $db;
    private array $config;

    // Scoring weights
    private const WEIGHT_RECENCY = 0.30;
    private const WEIGHT_ENGAGEMENT = 0.25;
    private const WEIGHT_UNIQUENESS = 0.20;
    private const WEIGHT_CONTEXT_RELEVANCE = 0.15;
    private const WEIGHT_MESSAGE_COUNT = 0.10;

    // Decay curves
    private const RECENCY_HALF_LIFE_DAYS = 7; // Score halves every 7 days
    private const MIN_SCORE = 1;
    private const MAX_SCORE = 10;

    public function __construct(array $config = [])
    {
        $this->db = new DB();
        $this->config = array_merge([
            'prune_threshold' => 3, // Auto-prune conversations scoring < 3
            'prune_age_days' => 30, // Only prune conversations older than 30 days
            'context_relevance_window' => 90, // Consider conversations from last 90 days
            'engagement_boost' => 2.0, // Multiplier for user-favorited conversations
        ], $config);
    }

    /**
     * Score a single conversation
     */
    public function scoreConversation(int $conversationId): float
    {
        $conversation = $this->fetchConversation($conversationId);
        if (!$conversation) {
            return 0.0;
        }

        $scores = [
            'recency' => $this->calculateRecencyScore($conversation),
            'engagement' => $this->calculateEngagementScore($conversation),
            'uniqueness' => $this->calculateUniquenessScore($conversation),
            'context_relevance' => $this->calculateContextRelevanceScore($conversation),
            'message_count' => $this->calculateMessageCountScore($conversation),
        ];

        // Weighted average
        $finalScore = (
            $scores['recency'] * self::WEIGHT_RECENCY +
            $scores['engagement'] * self::WEIGHT_ENGAGEMENT +
            $scores['uniqueness'] * self::WEIGHT_UNIQUENESS +
            $scores['context_relevance'] * self::WEIGHT_CONTEXT_RELEVANCE +
            $scores['message_count'] * self::WEIGHT_MESSAGE_COUNT
        );

        $finalScore = max(self::MIN_SCORE, min(self::MAX_SCORE, $finalScore));

        // Store score
        $this->storeScore($conversationId, $finalScore, $scores);

        return round($finalScore, 2);
    }

    /**
     * Score all conversations
     */
    public function scoreAllConversations(): array
    {
        $conversations = $this->db->query(
            "SELECT id FROM conversations WHERE deleted_at IS NULL ORDER BY created_at DESC"
        );

        $results = [];
        foreach ($conversations as $conv) {
            $score = $this->scoreConversation($conv['id']);
            $results[$conv['id']] = $score;
        }

        return $results;
    }

    /**
     * Get top N most important conversations
     */
    public function getTopConversations(int $limit = 10): array
    {
        return $this->db->query(
            "SELECT c.*, is.importance_score, is.scored_at
             FROM conversations c
             JOIN importance_scores is ON c.id = is.conversation_id
             WHERE c.deleted_at IS NULL
             ORDER BY is.importance_score DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get low-value conversations eligible for pruning
     */
    public function getLowValueConversations(): array
    {
        $threshold = $this->config['prune_threshold'];
        $ageLimit = $this->config['prune_age_days'];

        return $this->db->query(
            "SELECT c.*, is.importance_score
             FROM conversations c
             JOIN importance_scores is ON c.id = is.conversation_id
             WHERE c.deleted_at IS NULL
               AND is.importance_score < ?
               AND c.created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
             ORDER BY is.importance_score ASC",
            [$threshold, $ageLimit]
        );
    }

    /**
     * Auto-prune low-value conversations
     */
    public function pruneConversations(bool $hardDelete = false): int
    {
        $candidates = $this->getLowValueConversations();
        $pruned = 0;

        foreach ($candidates as $conv) {
            if ($hardDelete) {
                // Permanent deletion
                $this->db->query("DELETE FROM conversations WHERE id = ?", [$conv['id']]);
            } else {
                // Soft delete
                $this->db->query("UPDATE conversations SET deleted_at = NOW() WHERE id = ?", [$conv['id']]);
            }
            $pruned++;
        }

        return $pruned;
    }

    // --- SCORING ALGORITHMS ---

    /**
     * Calculate recency score (exponential decay)
     */
    private function calculateRecencyScore(array $conversation): float
    {
        $ageInDays = (time() - strtotime($conversation['updated_at'])) / 86400;
        $halfLife = self::RECENCY_HALF_LIFE_DAYS;

        // Exponential decay: score = 10 * (0.5 ^ (age / halfLife))
        $score = 10 * pow(0.5, $ageInDays / $halfLife);

        return max(1, min(10, $score));
    }

    /**
     * Calculate engagement score (user interactions)
     */
    private function calculateEngagementScore(array $conversation): float
    {
        $score = 5.0; // Base score

        // Boost for favorited conversations
        if ($conversation['is_favorited'] ?? false) {
            $score += $this->config['engagement_boost'];
        }

        // Boost for conversations with context cards
        $contextCards = $this->db->query(
            "SELECT COUNT(*) as count FROM context_cards WHERE conversation_id = ?",
            [$conversation['id']]
        );
        $score += min(3, $contextCards[0]['count'] * 0.5);

        // Boost for user-initiated conversations (vs automated)
        if ($conversation['source'] === 'user') {
            $score += 1.0;
        }

        return max(1, min(10, $score));
    }

    /**
     * Calculate uniqueness score (topic diversity)
     */
    private function calculateUniquenessScore(array $conversation): float
    {
        $title = $conversation['title'] ?? '';
        
        // Count how many conversations share similar titles
        $similarCount = $this->db->query(
            "SELECT COUNT(*) as count FROM conversations 
             WHERE id != ? 
               AND deleted_at IS NULL
               AND (
                 title LIKE CONCAT('%', SUBSTRING(?, 1, 20), '%')
                 OR MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)
               )",
            [$conversation['id'], $title, $title]
        );

        $similar = $similarCount[0]['count'] ?? 0;

        // Fewer similar conversations = higher uniqueness score
        if ($similar === 0) return 10.0;
        if ($similar <= 2) return 8.0;
        if ($similar <= 5) return 6.0;
        if ($similar <= 10) return 4.0;
        return 2.0;
    }

    /**
     * Calculate context relevance (how often referenced)
     */
    private function calculateContextRelevanceScore(array $conversation): float
    {
        $windowDays = $this->config['context_relevance_window'];

        // Count how many times this conversation's context was used
        $usageCount = $this->db->query(
            "SELECT COUNT(*) as count
             FROM context_cards cc
             JOIN messages m ON cc.message_id = m.id
             WHERE cc.conversation_id = ?
               AND m.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$conversation['id'], $windowDays]
        );

        $uses = $usageCount[0]['count'] ?? 0;

        // More usage = higher relevance
        if ($uses === 0) return 2.0;
        if ($uses <= 2) return 4.0;
        if ($uses <= 5) return 6.0;
        if ($uses <= 10) return 8.0;
        return 10.0;
    }

    /**
     * Calculate message count score (conversation depth)
     */
    private function calculateMessageCountScore(array $conversation): float
    {
        $messageCount = $this->db->query(
            "SELECT COUNT(*) as count FROM messages WHERE conversation_id = ?",
            [$conversation['id']]
        );

        $count = $messageCount[0]['count'] ?? 0;

        // More messages = deeper conversation = higher value
        if ($count === 0) return 1.0;
        if ($count <= 3) return 3.0;
        if ($count <= 10) return 5.0;
        if ($count <= 25) return 7.0;
        if ($count <= 50) return 9.0;
        return 10.0;
    }

    // --- DATA ACCESS ---

    private function fetchConversation(int $conversationId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM conversations WHERE id = ? AND deleted_at IS NULL",
            [$conversationId]
        );

        return $result[0] ?? null;
    }

    private function storeScore(int $conversationId, float $score, array $breakdown): void
    {
        // Store in importance_scores table
        $this->db->query(
            "INSERT INTO importance_scores (conversation_id, importance_score, recency_score, engagement_score, uniqueness_score, context_relevance_score, message_count_score, scored_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
               importance_score = VALUES(importance_score),
               recency_score = VALUES(recency_score),
               engagement_score = VALUES(engagement_score),
               uniqueness_score = VALUES(uniqueness_score),
               context_relevance_score = VALUES(context_relevance_score),
               message_count_score = VALUES(message_count_score),
               scored_at = NOW()",
            [
                $conversationId,
                $score,
                $breakdown['recency'],
                $breakdown['engagement'],
                $breakdown['uniqueness'],
                $breakdown['context_relevance'],
                $breakdown['message_count']
            ]
        );

        // Also update conversations table for quick access
        $this->db->query(
            "UPDATE conversations SET importance_score = ? WHERE id = ?",
            [$score, $conversationId]
        );
    }

    /**
     * Get scoring summary for a conversation
     */
    public function getScoringSummary(int $conversationId): ?array
    {
        $result = $this->db->query(
            "SELECT * FROM importance_scores WHERE conversation_id = ?",
            [$conversationId]
        );

        return $result[0] ?? null;
    }
}
