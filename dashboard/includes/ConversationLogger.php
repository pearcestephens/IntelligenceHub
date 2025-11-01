<?php
/**
 * UNIVERSAL Conversation Logger
 *
 * Works across ANY business unit, ANY bot, ANY platform
 *
 * FEATURES:
 * - Multi-tenant: Link conversations to any business unit
 * - Multi-bot: Works with GitHub Copilot, chatbots, support bots, etc.
 * - Multi-platform: OpenAI, Anthropic, GitHub, custom APIs, internal
 * - Flexible: Set business unit and agent by slug or ID
 * - Auto-tracking: Token counts, timing, sequence numbers
 * - Tagging: Add tags to conversations for categorization
 * - Performance: Updates aggregate metrics automatically
 *
 * USAGE EXAMPLES:
 *
 * Example 1: GitHub Copilot (Intelligence Hub)
 *   $logger = new ConversationLogger();
 *   $logger->setBusinessUnit('intelligence')
 *          ->setAgent('github-copilot');
 *   $logger->startConversation('user123', 'staff', 'coding');
 *   $logger->logMessage('user', 'How do I fix this bug?');
 *   $logger->logMessage('assistant', 'Try this approach...');
 *   $logger->addTags(['bug-fix', 'urgent']);
 *   $logger->endConversation('resolved', 5);
 *
 * Example 2: Customer Support (Vape Shed)
 *   $logger = new ConversationLogger();
 *   $logger->setBusinessUnit('vapeshed-retail')
 *          ->setAgent('customer-chat');
 *   $logger->startConversation('customer@email.com', 'customer', 'support');
 *   $logger->logMessage('user', 'What are your hours?');
 *   $logger->logMessage('assistant', 'We're open 9am-5pm...');
 *   $logger->endConversation('resolved', 4);
 *
 * Example 3: Quick Log (One-liner)
 *   ConversationLogger::quickLog(
 *       'User question here',
 *       'Assistant response here',
 *       'intelligence',      // business unit
 *       'github-copilot',   // agent
 *       ['context' => 'debugging'],
 *       ['bug-fix', 'optimization']
 *   );
 */

class ConversationLogger {
    private $db;
    private $businessUnitId = null;
    private $businessUnitSlug = null;
    private $agentId = null;
    private $agentSlug = null;
    private $conversationId = null;
    private $sessionId = null;
    private $messageSequence = 0;

    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Use session ID if available (session MUST be started by session-config.php FIRST!)
            // DO NOT start session here - it must be configured by session-config.php first
            if (session_status() === PHP_SESSION_ACTIVE) {
                $this->sessionId = session_id();
            } else {
                // If session not started yet, use a temporary unique ID
                // (session will be started properly by session-config.php later)
                $this->sessionId = uniqid('sess_', true);
            }

        } catch (PDOException $e) {
            error_log("ConversationLogger: DB connection failed - " . $e->getMessage());
        }
    }

    /**
     * Set business unit by slug or ID
     *
     * Examples:
     *   ->setBusinessUnit('intelligence')
     *   ->setBusinessUnit(1)
     */
    public function setBusinessUnit($identifier): self {
        if (!$this->db) return $this;

        try {
            if (is_numeric($identifier)) {
                $this->businessUnitId = (int)$identifier;
            } else {
                $stmt = $this->db->prepare("SELECT id FROM business_units WHERE unit_slug = ?");
                $stmt->execute([$identifier]);
                $this->businessUnitId = $stmt->fetchColumn();
                $this->businessUnitSlug = $identifier;
            }
        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to set business unit - " . $e->getMessage());
        }

        return $this;
    }

    /**
     * Set agent by slug or ID
     *
     * Examples:
     *   ->setAgent('github-copilot')
     *   ->setAgent(1)
     *
     * If business unit is set, only searches agents in that unit
     */
    public function setAgent($identifier): self {
        if (!$this->db) return $this;

        try {
            if (is_numeric($identifier)) {
                $this->agentId = (int)$identifier;
            } else {
                $where = $this->businessUnitId
                    ? "agent_slug = ? AND business_unit_id = ?"
                    : "agent_slug = ?";

                $stmt = $this->db->prepare("SELECT id FROM ai_agents WHERE $where LIMIT 1");

                if ($this->businessUnitId) {
                    $stmt->execute([$identifier, $this->businessUnitId]);
                } else {
                    $stmt->execute([$identifier]);
                }

                $this->agentId = $stmt->fetchColumn();
                $this->agentSlug = $identifier;
            }
        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to set agent - " . $e->getMessage());
        }

        return $this;
    }

    /**
     * Start a new conversation
     *
     * @param string|null $userId User identifier (email, username, ID, etc.)
     * @param string $userType Type: 'staff', 'customer', 'admin', 'developer', 'guest'
     * @param string $conversationType Type: 'chat', 'support', 'coding', 'analysis', 'workflow', 'other'
     * @param array $context Additional context (page, feature, metadata, etc.)
     * @return int|null Conversation ID
     */
    public function startConversation(
        string $userId = null,
        string $userType = 'staff',
        string $conversationType = 'chat',
        array $context = []
    ): ?int {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_conversations
                (business_unit_id, agent_id, session_id, conversation_type,
                 user_id, user_type, context, started_at, outcome)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'ongoing')
            ");

            $stmt->execute([
                $this->businessUnitId,
                $this->agentId,
                $this->sessionId,
                $conversationType,
                $userId,
                $userType,
                json_encode($context)
            ]);

            $this->conversationId = $this->db->lastInsertId();
            $this->messageSequence = 0;

            return $this->conversationId;

        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to start conversation - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a message (UNIVERSAL - works for ANY conversation)
     *
     * @param string $role 'user', 'assistant', 'system', 'function', 'tool'
     * @param string $content Message content
     * @param string $contentType 'text', 'code', 'markdown', 'html', 'json'
     * @param array $metadata Additional data (model, temperature, tokens, etc.)
     * @return bool Success
     */
    public function logMessage(
        string $role,
        string $content,
        string $contentType = 'text',
        array $metadata = []
    ): bool {
        if (!$this->db) return false;

        // Auto-start conversation if not started
        if (!$this->conversationId) {
            $this->startConversation();
            if (!$this->conversationId) return false;
        }

        try {
            $this->messageSequence++;
            $startTime = microtime(true);

            $stmt = $this->db->prepare("
                INSERT INTO ai_messages
                (conversation_id, sequence_number, role, content, content_type,
                 metadata, token_count, processing_time_ms, model_used, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $tokenCount = $metadata['token_count'] ?? $this->estimateTokens($content);
            $processingTime = $metadata['processing_time'] ?? round((microtime(true) - $startTime) * 1000);
            $modelUsed = $metadata['model'] ?? null;

            $stmt->execute([
                $this->conversationId,
                $this->messageSequence,
                $role,
                $content,
                $contentType,
                json_encode($metadata),
                $tokenCount,
                $processingTime,
                $modelUsed
            ]);

            // Update conversation counters
            $userCount = $role === 'user' ? 1 : 0;
            $assistantCount = $role === 'assistant' ? 1 : 0;

            $this->db->exec("
                UPDATE ai_conversations
                SET message_count = message_count + 1,
                    user_message_count = user_message_count + $userCount,
                    assistant_message_count = assistant_message_count + $assistantCount,
                    total_tokens = total_tokens + $tokenCount,
                    updated_at = NOW()
                WHERE id = {$this->conversationId}
            ");

            return true;

        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to log message - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add tags to conversation
     *
     * @param array $tags Tag names (e.g., ['bug-fix', 'urgent', 'optimization'])
     * @return bool Success
     */
    public function addTags(array $tags): bool {
        if (!$this->db || !$this->conversationId) return false;

        try {
            $stmt = $this->db->prepare("
                SELECT tags FROM ai_conversations WHERE id = ?
            ");
            $stmt->execute([$this->conversationId]);
            $existingTags = json_decode($stmt->fetchColumn() ?: '[]', true);

            $allTags = array_unique(array_merge($existingTags, $tags));

            $stmt = $this->db->prepare("
                UPDATE ai_conversations SET tags = ? WHERE id = ?
            ");
            $stmt->execute([json_encode($allTags), $this->conversationId]);

            // Update tag usage counts
            foreach ($tags as $tag) {
                $this->db->exec("
                    UPDATE conversation_tags
                    SET usage_count = usage_count + 1
                    WHERE tag_name = " . $this->db->quote($tag)
                );
            }

            return true;
        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to add tags - " . $e->getMessage());
            return false;
        }
    }

    /**
     * End conversation
     *
     * @param string $outcome 'resolved', 'escalated', 'abandoned', 'ongoing'
     * @param int|null $satisfaction Rating 1-5 (optional)
     * @param string|null $feedback User feedback (optional)
     * @return bool Success
     */
    public function endConversation(
        string $outcome = 'resolved',
        ?int $satisfaction = null,
        ?string $feedback = null
    ): bool {
        if (!$this->db || !$this->conversationId) return false;

        try {
            $stmt = $this->db->prepare("
                UPDATE ai_conversations
                SET ended_at = NOW(),
                    duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()),
                    outcome = ?,
                    satisfaction_rating = ?,
                    feedback = ?
                WHERE id = ?
            ");

            $stmt->execute([$outcome, $satisfaction, $feedback, $this->conversationId]);

            // Update agent total conversation count
            if ($this->agentId) {
                $this->db->exec("
                    UPDATE ai_agents
                    SET total_conversations = total_conversations + 1
                    WHERE id = {$this->agentId}
                ");
            }

            return true;

        } catch (PDOException $e) {
            error_log("ConversationLogger: Failed to end conversation - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Quick log - all in one (CONVENIENCE METHOD)
     *
     * Use when you just want to log a simple exchange without managing conversation lifecycle
     *
     * Example:
     *   ConversationLogger::quickLog(
     *       'How do I optimize this query?',
     *       'Use an index on the user_id column',
     *       'intelligence',
     *       'github-copilot',
     *       ['context' => 'database_optimization'],
     *       ['optimization', 'database']
     *   );
     */
    public static function quickLog(
        string $userMessage,
        string $assistantResponse,
        string $businessUnit = 'intelligence',
        string $agent = 'github-copilot',
        array $context = [],
        array $tags = []
    ): bool {
        $logger = new self();
        $logger->setBusinessUnit($businessUnit)
               ->setAgent($agent);

        $convId = $logger->startConversation(null, 'staff', 'chat', $context);
        if (!$convId) return false;

        $logger->logMessage('user', $userMessage);
        $logger->logMessage('assistant', $assistantResponse);

        if (!empty($tags)) {
            $logger->addTags($tags);
        }

        $logger->endConversation('resolved');

        return true;
    }

    /**
     * Estimate token count (rough approximation: ~4 chars per token)
     */
    private function estimateTokens(string $text): int {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Get current conversation ID
     */
    public function getConversationId(): ?int {
        return $this->conversationId;
    }

    /**
     * Get available business units
     */
    public static function getBusinessUnits(): array {
        try {
            $db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS
            );
            $stmt = $db->query("SELECT id, unit_slug, unit_name FROM business_units WHERE is_active = 1 ORDER BY unit_name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get available agents for a business unit
     */
    public static function getAgents(?int $businessUnitId = null): array {
        try {
            $db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS
            );

            $where = $businessUnitId ? "WHERE business_unit_id = ?" : "";
            $stmt = $db->prepare("SELECT id, agent_slug, agent_name, agent_type, platform FROM ai_agents $where ORDER BY agent_name");

            if ($businessUnitId) {
                $stmt->execute([$businessUnitId]);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
