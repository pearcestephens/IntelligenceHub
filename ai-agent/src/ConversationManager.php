<?php

declare(strict_types=1);

namespace App;

use App\Util\Ids;
use App\Util\Validate;
use App\Util\Errors;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * ConversationManager - Handles conversation state, persistence, and lifecycle management
 *
 * Provides enterprise-grade conversation management with:
 * - State persistence and retrieval
 * - Message history management
 * - Context window optimization
 * - Tool call tracking
 * - Conversation metrics and analytics
 * - Concurrent conversation support
 *
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */
class ConversationManager
{
    private DB $db;
    private Logger $logger;
    private Config $config;
    private RedisClient $redis;

    /** Maximum messages to keep in context window */
    private const MAX_CONTEXT_MESSAGES = 50;

    /** Conversation idle timeout (24 hours) */
    private const CONVERSATION_TIMEOUT = 86400;

    /** Redis key prefixes for conversation data */
    private const REDIS_CONV_PREFIX = 'conv:';
    private const REDIS_CONTEXT_PREFIX = 'context:';
    private const REDIS_ACTIVE_PREFIX = 'active:';

    public function __construct(
        DB $db,
        Logger $logger,
        Config $config,
        RedisClient $redis
    ) {
        $this->db = $db;
        $this->logger = $logger;
        $this->config = $config;
        $this->redis = $redis;
    }

    /**
     * Create a new conversation
     */
    public function createConversation(
        string $title = 'New Conversation',
        array $metadata = []
    ): string {
        try {
            $conversationId = Ids::conversation();
            $now = new DateTime('now', new DateTimeZone('UTC'));

            // Validate inputs
            $title = Validate::string($title, 'title', 1, 200);
            $metadata = Validate::array($metadata, 'metadata');

            // Insert into database
            DB::execute(
                'INSERT INTO conversations (conversation_id, title, created_at, updated_at, metadata) VALUES (?, ?, ?, ?, ?)',
                [
                    $conversationId,
                    $title,
                    $now->format('Y-m-d H:i:s'),
                    $now->format('Y-m-d H:i:s'),
                    json_encode($metadata)
                ]
            );

            // Cache conversation metadata
            $this->cacheConversationData($conversationId, [
                'title' => $title,
                'created_at' => $now->format('c'),
                'metadata' => $metadata,
                'message_count' => 0,
                'last_activity' => $now->format('c')
            ]);

            // Mark as active
            $this->markConversationActive($conversationId);

            $this->logger->info('Created conversation', [
                'conversation_id' => $conversationId,
                'title' => $title,
                'metadata_keys' => array_keys($metadata)
            ]);

            return $conversationId;
        } catch (Exception $e) {
            $this->logger->error('Failed to create conversation', [
                'error' => $e->getMessage(),
                'title' => $title ?? null
            ]);
            throw Errors::conversationError('Failed to create conversation: ' . $e->getMessage());
        }
    }

    /**
     * Get conversation metadata and statistics
     */
    public function getConversation(string $conversationId): ?array
    {
        try {
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);

            // Try cache first
            $cached = RedisClient::get(self::REDIS_CONV_PREFIX . $conversationId);
            if ($cached !== null) {
                // RedisClient::get already JSON-decodes values when possible
                if (is_array($cached)) {
                    return $cached;
                }
                // If stored as string, attempt to decode; fall back to null if invalid
                $decoded = json_decode((string)$cached, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }

            // Query database
            $conversation = DB::selectOne(
                'SELECT 
                    c.conversation_id,
                    c.title,
                    c.created_at,
                    c.updated_at,
                    c.metadata,
                    (SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id) as message_count,
                    (SELECT MAX(created_at) FROM messages WHERE conversation_id = c.conversation_id) as last_message_at
                FROM conversations c 
                WHERE c.conversation_id = ?',
                [$conversationId]
            );

            if (!$conversation) {
                return null;
            }

            $data = [
                'conversation_id' => $conversation['conversation_id'],
                'title' => $conversation['title'],
                'created_at' => $conversation['created_at'],
                'updated_at' => $conversation['updated_at'],
                'metadata' => json_decode($conversation['metadata'] ?? '{}', true),
                'message_count' => (int)$conversation['message_count'],
                'last_message_at' => $conversation['last_message_at']
            ];

            // Cache for future requests
            $this->cacheConversationData($conversationId, $data);

            return $data;
        } catch (Exception $e) {
            $this->logger->error('Failed to get conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Add a message to conversation
     */
    public function addMessage(
        string $conversationId,
        string $role,
        string $content,
        array $toolCalls = [],
        array $metadata = []
    ): string {
        try {
            // Validate inputs
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);
            $role = Validate::enum($role, 'role', ['user', 'assistant', 'system', 'tool']);
            $content = Validate::string($content, 'content', 1, 1000000);
            $toolCalls = Validate::array($toolCalls, 'tool_calls');
            $metadata = Validate::array($metadata, 'metadata');

            $messageId = Ids::uuid();
            $now = new DateTime('now', new DateTimeZone('UTC'));

            // Transactional write
            DB::transaction(function () use ($messageId, $conversationId, $role, $content, $metadata, $toolCalls, $now) {
                // Insert message
                DB::execute(
                    'INSERT INTO messages (message_id, conversation_id, role, content, created_at, metadata) VALUES (?, ?, ?, ?, ?, ?)',
                    [
                        $messageId,
                        $conversationId,
                        $role,
                        $content,
                        $now->format('Y-m-d H:i:s'),
                        json_encode($metadata)
                    ]
                );

                // Insert tool calls if present
                foreach ($toolCalls as $toolCall) {
                    $this->addToolCall($messageId, $toolCall);
                }

                // Update conversation timestamp
                DB::execute(
                    'UPDATE conversations SET updated_at = ? WHERE conversation_id = ?',
                    [$now->format('Y-m-d H:i:s'), $conversationId]
                );
            });

            // Update cache
            $this->invalidateConversationCache($conversationId);
            $this->markConversationActive($conversationId);

            $this->logger->info('Added message to conversation', [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'role' => $role,
                'content_length' => strlen($content),
                'tool_calls' => count($toolCalls)
            ]);

            return $messageId;
        } catch (Exception $e) {
            $this->logger->error('Failed to add message', [
                'conversation_id' => $conversationId,
                'role' => $role ?? null,
                'error' => $e->getMessage()
            ]);
            throw Errors::conversationError('Failed to add message: ' . $e->getMessage());
        }
    }

    /**
     * Get conversation messages with context optimization
     */
    public function getMessages(
        string $conversationId,
        int $limit = null,
        string $beforeMessageId = null
    ): array {
        try {
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);
            $limit = $limit ?? self::MAX_CONTEXT_MESSAGES;
            $limit = Validate::integer($limit, 'limit', 1, 1000);

            // Try cache first for recent messages
            if (!$beforeMessageId) {
                $cached = RedisClient::get(self::REDIS_CONTEXT_PREFIX . $conversationId);
                if (is_array($cached) && count($cached) <= $limit) {
                    return $cached;
                }
            }

            // Build query
            $sql = '
                SELECT 
                    m.message_id,
                    m.role,
                    m.content,
                    m.created_at,
                    m.metadata,
                    GROUP_CONCAT(
                        CASE WHEN tc.tool_call_id IS NOT NULL 
                        THEN JSON_OBJECT(
                            "id", tc.tool_call_id,
                            "tool", tc.tool_name,
                            "function", tc.function_name,
                            "arguments", tc.arguments,
                            "result", tc.result,
                            "status", tc.status,
                            "created_at", tc.created_at
                        )
                        END SEPARATOR \'||\'
                    ) as tool_calls
                FROM messages m
                LEFT JOIN tool_calls tc ON m.message_id = tc.message_id
                WHERE m.conversation_id = ?
            ';

            $params = [$conversationId];

            if ($beforeMessageId) {
                $sql .= ' AND m.created_at < (SELECT created_at FROM messages WHERE message_id = ?)';
                $params[] = $beforeMessageId;
            }

            $sql .= ' GROUP BY m.message_id ORDER BY m.created_at DESC LIMIT ?';
            $params[] = $limit;

            $rows = DB::select($sql, $params);

            // Process messages
            $messages = [];
            foreach (array_reverse($rows) as $row) {
                $toolCalls = [];
                if (!empty($row['tool_calls'])) {
                    $toolCallsData = explode('||', $row['tool_calls']);
                    foreach ($toolCallsData as $toolCallJson) {
                        if ($toolCallJson) {
                            $toolCalls[] = json_decode($toolCallJson, true);
                        }
                    }
                }

                $messages[] = [
                    'message_id' => $row['message_id'],
                    'role' => $row['role'],
                    'content' => $row['content'],
                    'created_at' => $row['created_at'],
                    'metadata' => json_decode($row['metadata'] ?? '{}', true),
                    'tool_calls' => $toolCalls
                ];
            }

            // Cache recent messages
            if (!$beforeMessageId && count($messages) > 0) {
                // Cache recent context in Redis for 5 minutes
                RedisClient::set(
                    self::REDIS_CONTEXT_PREFIX . $conversationId,
                    $messages,
                    300
                );
            }

            return $messages;
        } catch (Exception $e) {
            $this->logger->error('Failed to get messages', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            throw Errors::conversationError('Failed to get messages: ' . $e->getMessage());
        }
    }

    /**
     * Update conversation title
     */
    public function updateConversationTitle(string $conversationId, string $title): bool
    {
        try {
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);
            $title = Validate::string($title, 'title', 1, 200);

            $result = DB::execute(
                'UPDATE conversations SET title = ?, updated_at = NOW() WHERE conversation_id = ?',
                [$title, $conversationId]
            ) > 0;

            if ($result) {
                $this->invalidateConversationCache($conversationId);

                $this->logger->info('Updated conversation title', [
                    'conversation_id' => $conversationId,
                    'title' => $title
                ]);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error('Failed to update conversation title', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * List conversations with pagination
     */
    public function listConversations(int $limit = 20, int $offset = 0): array
    {
        try {
            $limit = Validate::integer($limit, 'limit', 1, 100);
            $offset = Validate::integer($offset, 'offset', 0, PHP_INT_MAX);

            $conversations = DB::select(
                'SELECT 
                    c.conversation_id,
                    c.title,
                    c.created_at,
                    c.updated_at,
                    c.metadata,
                    COUNT(m.message_id) as message_count,
                    MAX(m.created_at) as last_message_at
                FROM conversations c
                LEFT JOIN messages m ON c.conversation_id = m.conversation_id
                GROUP BY c.conversation_id
                ORDER BY COALESCE(MAX(m.created_at), c.updated_at) DESC
                LIMIT ? OFFSET ?',
                [$limit, $offset]
            );

            $result = [];
            foreach ($conversations as $conv) {
                $result[] = [
                    'conversation_id' => $conv['conversation_id'],
                    'title' => $conv['title'],
                    'created_at' => $conv['created_at'],
                    'updated_at' => $conv['updated_at'],
                    'metadata' => json_decode($conv['metadata'] ?? '{}', true),
                    'message_count' => (int)$conv['message_count'],
                    'last_message_at' => $conv['last_message_at']
                ];
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->error('Failed to list conversations', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Delete conversation and all associated data
     */
    public function deleteConversation(string $conversationId): bool
    {
        try {
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);

            DB::transaction(function () use ($conversationId) {
                // Delete tool calls
                DB::execute(
                    'DELETE tc FROM tool_calls tc INNER JOIN messages m ON tc.message_id = m.message_id WHERE m.conversation_id = ?',
                    [$conversationId]
                );

                // Delete messages
                DB::execute('DELETE FROM messages WHERE conversation_id = ?', [$conversationId]);

                // Delete conversation
                DB::execute('DELETE FROM conversations WHERE conversation_id = ?', [$conversationId]);
            });

            // Clear cache
            $this->invalidateConversationCache($conversationId);
            RedisClient::delete(self::REDIS_ACTIVE_PREFIX . $conversationId);

            $this->logger->info('Deleted conversation', [
                'conversation_id' => $conversationId
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to delete conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(string $conversationId): array
    {
        try {
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);

            $stats = DB::selectOne(
                'SELECT 
                    COUNT(m.message_id) as total_messages,
                    SUM(CASE WHEN m.role = "user" THEN 1 ELSE 0 END) as user_messages,
                    SUM(CASE WHEN m.role = "assistant" THEN 1 ELSE 0 END) as assistant_messages,
                    SUM(CASE WHEN m.role = "system" THEN 1 ELSE 0 END) as system_messages,
                    SUM(LENGTH(m.content)) as total_characters,
                    COUNT(DISTINCT tc.tool_call_id) as total_tool_calls,
                    MIN(m.created_at) as first_message_at,
                    MAX(m.created_at) as last_message_at
                FROM messages m
                LEFT JOIN tool_calls tc ON m.message_id = tc.message_id
                WHERE m.conversation_id = ?',
                [$conversationId]
            );

            return [
                'total_messages' => (int)$stats['total_messages'],
                'user_messages' => (int)$stats['user_messages'],
                'assistant_messages' => (int)$stats['assistant_messages'],
                'system_messages' => (int)$stats['system_messages'],
                'total_characters' => (int)$stats['total_characters'],
                'total_tool_calls' => (int)$stats['total_tool_calls'],
                'first_message_at' => $stats['first_message_at'],
                'last_message_at' => $stats['last_message_at']
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get conversation stats', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Add tool call to message
     */
    private function addToolCall(string $messageId, array $toolCall): void
    {
        $toolCallId = $toolCall['id'] ?? Ids::toolCall();

        DB::execute(
            'INSERT INTO tool_calls (tool_call_id, message_id, tool_name, function_name, arguments, result, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())',
            [
                $toolCallId,
                $messageId,
                $toolCall['tool'] ?? '',
                $toolCall['function'] ?? '',
                json_encode($toolCall['arguments'] ?? []),
                json_encode($toolCall['result'] ?? null),
                $toolCall['status'] ?? 'pending'
            ]
        );
    }

    /**
     * Cache conversation data in Redis
     */
    private function cacheConversationData(string $conversationId, array $data): void
    {
        // Cache conversation metadata for 1 hour using RedisClient helper
        RedisClient::set(
            self::REDIS_CONV_PREFIX . $conversationId,
            $data,
            3600
        );
    }

    /**
     * Invalidate conversation cache
     */
    private function invalidateConversationCache(string $conversationId): void
    {
        RedisClient::delete(self::REDIS_CONV_PREFIX . $conversationId);
        RedisClient::delete(self::REDIS_CONTEXT_PREFIX . $conversationId);
    }

    /**
     * Mark conversation as active for cleanup tracking
     */
    private function markConversationActive(string $conversationId): void
    {
        // Mark active with TTL using RedisClient helper
        RedisClient::set(
            self::REDIS_ACTIVE_PREFIX . $conversationId,
            time(),
            self::CONVERSATION_TIMEOUT
        );
    }

    /**
     * Clean up inactive conversations (should be called periodically)
     */
    public function cleanupInactiveConversations(): int
    {
        try {
            $cutoff = new DateTime('-24 hours', new DateTimeZone('UTC'));

            $inactiveConversations = DB::select(
                'SELECT conversation_id 
                 FROM conversations 
                 WHERE updated_at < ? 
                 LIMIT 100',
                [$cutoff->format('Y-m-d H:i:s')]
            );
            $cleaned = 0;

            foreach ($inactiveConversations as $conv) {
                if ($this->deleteConversation($conv['conversation_id'])) {
                    $cleaned++;
                }
            }

            $this->logger->info('Cleaned up inactive conversations', [
                'cleaned_count' => $cleaned
            ]);

            return $cleaned;
        } catch (Exception $e) {
            $this->logger->error('Failed to cleanup inactive conversations', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
