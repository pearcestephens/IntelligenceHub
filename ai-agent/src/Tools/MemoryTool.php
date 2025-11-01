<?php

/**
 * Memory Tool for conversation context and memory management
 * Provides AI agent access to conversation history and context
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\DB;
use App\Logger;
use App\Memory\ContextCards;
use App\Memory\Summarizer;
use App\Util\Validate;
use App\Tools\Contracts\ToolContract;

class MemoryTool implements ToolContract
{
    public static function run(array $parameters, array $context = []): array
    {
        $action = $parameters['action'] ?? 'get_context';
        return match ($action) {
            'get_context' => self::getContext($parameters, $context),
            'store_memory' => self::storeMemory($parameters, $context),
            'search_memories' => self::searchMemories($parameters, $context),
            'get_summary' => self::getSummary($parameters, $context),
            'get_memory_stats' => self::getMemoryStats($parameters, $context),
            default => ['error' => 'Unknown action', 'error_type' => 'InvalidAction', 'action' => $action]
        };
    }

    public static function spec(): array
    {
        return [
            'name' => 'memory_tool',
            'description' => 'Manage conversation context and long-term memory',
            'category' => 'memory',
            'internal' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['get_context','store_memory','search_memories','get_summary','get_memory_stats']],
                    'conversation_id' => ['type' => 'string'],
                    'include_summary' => ['type' => 'boolean'],
                    'max_messages' => ['type' => 'integer'],
                    'memory_type' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'importance' => ['type' => 'string'],
                    'tags' => ['type' => 'array', 'items' => ['type' => 'string']]
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 20,
                'rate_limit' => 50
            ]
        ];
    }
    /**
     * Get conversation context for AI agent
     */
    public static function getContext(array $parameters, array $context = []): array
    {
        $conversationId = $parameters['conversation_id'] ?? '';
        $includeSummary = (bool)($parameters['include_summary'] ?? true);
        $maxMessages = (int)($parameters['max_messages'] ?? 10);

        Validate::string($conversationId, 'conversation_id', 1);

        try {
            Logger::info('Memory tool retrieving conversation context', [
                'conversation_id' => $conversationId,
                'include_summary' => $includeSummary,
                'max_messages' => $maxMessages,
            ]);

            // Get conversation details
            $conversation = self::getConversationInfo($conversationId);
            if (!$conversation) {
                return [
                    'error' => 'Conversation not found',
                    'error_type' => 'NotFound',
                    'conversation_id' => $conversationId,
                ];
            }

            // Get recent messages
            $messages = self::getRecentMessages($conversationId, $maxMessages);

            // Get conversation summary if requested
            $summary = null;
            if ($includeSummary) {
                $summary = Summarizer::getLatestSummary($conversationId);
            }

            // Build context structure
            $contextData = [
                'conversation' => $conversation,
                'recent_messages' => $messages,
                'message_count' => count($messages),
                'summary' => $summary,
                'context_cards' => ContextCards::buildSystemPrompt(),
                'conversation_stats' => self::getConversationStats($conversationId),
            ];

            return $contextData;
        } catch (\Throwable $e) {
            Logger::error('Failed to get conversation context', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to retrieve context: ' . $e->getMessage(),
                'error_type' => 'ContextError',
                'conversation_id' => $conversationId,
            ];
        }
    }

    /**
     * Store conversation memory
     */
    public static function storeMemory(array $parameters, array $context = []): array
    {
        $conversationId = $parameters['conversation_id'] ?? '';
        $memoryType = $parameters['memory_type'] ?? 'fact';
        $content = $parameters['content'] ?? '';
        $importance = $parameters['importance'] ?? 'medium';
        $tags = $parameters['tags'] ?? [];

        Validate::string($conversationId, 'conversation_id', 1);
        Validate::string($content, 'content', 1, 2000);

        try {
            // Store memory in conversation metadata
            $memoryId = uniqid('mem_', true);
            $memoryData = [
                'id' => $memoryId,
                'type' => $memoryType,
                'content' => $content,
                'importance' => $importance,
                'tags' => $tags,
                'created_at' => date('Y-m-d H:i:s'),
                'context' => array_intersect_key($context, array_flip(['user_id', 'session_id', 'tool_context'])),
            ];

            // Get existing conversation metadata
            $conversation = DB::selectOne(
                'SELECT metadata FROM conversations WHERE id = ? LIMIT 1',
                [$conversationId]
            );

            if (!$conversation) {
                return [
                    'error' => 'Conversation not found',
                    'error_type' => 'NotFound',
                    'conversation_id' => $conversationId,
                ];
            }

            $metadata = [];
            if (!empty($conversation['metadata'])) {
                $decoded = json_decode((string) $conversation['metadata'], true);
                if (is_array($decoded)) {
                    $metadata = $decoded;
                }
            }

            // Add memory to metadata
            $metadata['memories'] = $metadata['memories'] ?? [];
            $metadata['memories'][] = $memoryData;

            // Keep only last 50 memories to prevent bloat
            if (count($metadata['memories']) > 50) {
                $metadata['memories'] = array_slice($metadata['memories'], -50);
            }

            // Update conversation
            DB::execute(
                'UPDATE conversations SET metadata = ?, updated_at = ? WHERE id = ?',
                [json_encode($metadata), date('Y-m-d H:i:s'), $conversationId]
            );

            Logger::info('Memory stored', [
                'conversation_id' => $conversationId,
                'memory_id' => $memoryId,
                'type' => $memoryType,
                'importance' => $importance,
            ]);

            return [
                'memory_id' => $memoryId,
                'conversation_id' => $conversationId,
                'success' => true,
                'message' => 'Memory successfully stored',
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to store memory', [
                'conversation_id' => $conversationId,
                'memory_type' => $memoryType,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to store memory: ' . $e->getMessage(),
                'error_type' => 'StoreError',
                'success' => false,
            ];
        }
    }

    /**
     * Search conversation memories
     */
    public static function searchMemories(array $parameters, array $context = []): array
    {
        $conversationId = $parameters['conversation_id'] ?? '';
        $query = $parameters['query'] ?? '';
        $memoryType = $parameters['memory_type'] ?? null;
        $importance = $parameters['importance'] ?? null;
        $limit = (int)($parameters['limit'] ?? 10);

        Validate::string($conversationId, 'conversation_id', 1);
        Validate::string($query, 1, 500);

        try {
            // Get conversation metadata
            $conversation = DB::selectOne(
                'SELECT metadata FROM conversations WHERE id = ? LIMIT 1',
                [$conversationId]
            );

            if (!$conversation) {
                return [
                    'error' => 'Conversation not found',
                    'error_type' => 'NotFound',
                    'conversation_id' => $conversationId,
                ];
            }

            $metadata = [];
            if (!empty($conversation['metadata'])) {
                $metadata = json_decode((string) $conversation['metadata'], true) ?: [];
            }
            $memories = $metadata['memories'] ?? [];

            // Filter memories
            $filteredMemories = array_filter(
                $memories,
                function ($memory) use ($query, $memoryType, $importance) {
                    // Text search
                    if ($query !== '') {
                        $searchIn = strtolower(($memory['content'] ?? '') . ' ' . implode(' ', $memory['tags'] ?? []));
                        if (strpos($searchIn, strtolower($query)) === false) {
                            return false;
                        }
                    }

                    // Type filter
                    if ($memoryType && ($memory['type'] ?? null) !== $memoryType) {
                        return false;
                    }

                    // Importance filter
                    if ($importance && ($memory['importance'] ?? null) !== $importance) {
                        return false;
                    }

                    return true;
                }
            );

            // Sort by creation date (newest first) and limit
            usort($filteredMemories, fn($a, $b) => strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now'));
            $filteredMemories = array_slice(array_values($filteredMemories), 0, $limit);

            return [
                'memories' => $filteredMemories,
                'total_found' => count($filteredMemories),
                'conversation_id' => $conversationId,
                'search_query' => $query,
                'filters' => [
                    'memory_type' => $memoryType,
                    'importance' => $importance,
                ],
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to search memories', [
                'conversation_id' => $conversationId,
                'query' => substr($query, 0, 100),
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to search memories: ' . $e->getMessage(),
                'error_type' => 'SearchError',
                'memories' => [],
            ];
        }
    }

    /**
     * Get conversation summary
     */
    public static function getSummary(array $parameters, array $context = []): array
    {
        $conversationId = $parameters['conversation_id'] ?? '';
        $regenerate = (bool)($parameters['regenerate'] ?? false);

        Validate::string($conversationId, 1);

        try {
            if ($regenerate) {
                // Force regenerate summary
                $summary = Summarizer::generateSummary($conversationId);
            } else {
                // Get existing summary or generate if needed
                $summary = Summarizer::getLatestSummary($conversationId);

                if (!$summary && Summarizer::needsSummary($conversationId)) {
                    $summary = Summarizer::generateSummary($conversationId);
                }
            }

            if ($summary) {
                return [
                    'summary' => $summary,
                    'conversation_id' => $conversationId,
                    'regenerated' => $regenerate,
                ];
            }

            return [
                'message' => 'No summary available or needed for this conversation',
                'conversation_id' => $conversationId,
                'summary' => null,
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get conversation summary', [
                'conversation_id' => $conversationId,
                'regenerate' => $regenerate,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to get summary: ' . $e->getMessage(),
                'error_type' => 'SummaryError',
                'conversation_id' => $conversationId,
            ];
        }
    }

    /**
     * Get memory statistics
     */
    public static function getMemoryStats(array $parameters, array $context = []): array
    {
        $conversationId = $parameters['conversation_id'] ?? '';

        if ($conversationId) {
            return self::getConversationMemoryStats($conversationId);
        }

        return self::getGlobalMemoryStats();
    }

    /**
     * Get conversation info
     */
    private static function getConversationInfo(string $conversationId): ?array
    {
        return DB::selectOne(
            'SELECT id, user_id, title, status, created_at, updated_at, metadata 
                 FROM conversations WHERE id = ? LIMIT 1',
            [$conversationId]
        );
    }

    /**
     * Get recent messages for conversation
     */
    private static function getRecentMessages(string $conversationId, int $limit): array
    {
        return DB::select(
            'SELECT id, role, content, tool_calls, created_at, metadata 
                 FROM messages 
                 WHERE conversation_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?',
            [$conversationId, $limit]
        );
    }

    /**
     * Get conversation statistics
     */
    private static function getConversationStats(string $conversationId): array
    {
        try {
            $stats = DB::selectOne(
                'SELECT 
                    COUNT(*) as message_count,
                    COUNT(CASE WHEN role = "user" THEN 1 END) as user_messages,
                    COUNT(CASE WHEN role = "assistant" THEN 1 END) as assistant_messages,
                    COUNT(CASE WHEN tool_calls IS NOT NULL THEN 1 END) as messages_with_tools,
                    MIN(created_at) as first_message_at,
                    MAX(created_at) as last_message_at
                FROM messages 
                WHERE conversation_id = ?',
                [$conversationId]
            );

            $toolStats = DB::selectOne(
                'SELECT 
                    COUNT(*) as tool_call_count,
                    COUNT(CASE WHEN success = 1 THEN 1 END) as successful_tools,
                    COUNT(DISTINCT tool_name) as unique_tools_used,
                    AVG(duration_ms) as avg_tool_duration
                FROM tool_calls 
                WHERE execution_id IN (
                    SELECT DISTINCT JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.execution_id"))
                    FROM messages 
                    WHERE conversation_id = ? AND metadata IS NOT NULL
                )',
                [$conversationId]
            );

            return [
                'message_count' => (int)($stats['message_count'] ?? 0),
                'user_messages' => (int)($stats['user_messages'] ?? 0),
                'assistant_messages' => (int)($stats['assistant_messages'] ?? 0),
                'messages_with_tools' => (int)($stats['messages_with_tools'] ?? 0),
                'first_message_at' => $stats['first_message_at'] ?? null,
                'last_message_at' => $stats['last_message_at'] ?? null,
                'tool_call_count' => (int)($toolStats['tool_call_count'] ?? 0),
                'successful_tools' => (int)($toolStats['successful_tools'] ?? 0),
                'unique_tools_used' => (int)($toolStats['unique_tools_used'] ?? 0),
                'avg_tool_duration_ms' => round($toolStats['avg_tool_duration'] ?? 0, 2),
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get conversation stats', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'message_count' => 0,
                'error' => 'Failed to calculate statistics',
            ];
        }
    }

    /**
     * Get memory statistics for specific conversation
     */
    private static function getConversationMemoryStats(string $conversationId): array
    {
        try {
            $conversation = DB::selectOne(
                'SELECT metadata FROM conversations WHERE id = ? LIMIT 1',
                [$conversationId]
            );

            if (!$conversation) {
                return [
                    'error' => 'Conversation not found',
                    'conversation_id' => $conversationId,
                ];
            }

            $metadata = [];
            if (!empty($conversation['metadata'])) {
                $metadata = json_decode((string) $conversation['metadata'], true) ?: [];
            }
            $memories = $metadata['memories'] ?? [];

            $stats = [
                'conversation_id' => $conversationId,
                'total_memories' => count($memories),
                'by_type' => [],
                'by_importance' => [],
                'avg_memory_length' => 0,
                'latest_memory_at' => null,
            ];

            if (!empty($memories)) {
                // Group by type and importance
                foreach ($memories as $memory) {
                    $type = $memory['type'] ?? 'unknown';
                    $importance = $memory['importance'] ?? 'medium';

                    $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
                    $stats['by_importance'][$importance] = ($stats['by_importance'][$importance] ?? 0) + 1;
                }

                // Calculate average memory length
                $totalLength = array_sum(array_map(fn($m) => strlen($m['content'] ?? ''), $memories));
                $stats['avg_memory_length'] = count($memories) > 0 ? round($totalLength / count($memories), 1) : 0;

                // Get latest memory date
                $latestMemory = max(array_column($memories, 'created_at'));
                $stats['latest_memory_at'] = $latestMemory;
            }

            return $stats;
        } catch (\Throwable $e) {
            Logger::error('Failed to get conversation memory stats', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to get memory statistics: ' . $e->getMessage(),
                'conversation_id' => $conversationId,
            ];
        }
    }

    /**
     * Get global memory statistics
     */
    private static function getGlobalMemoryStats(): array
    {
        try {
            $conversationStats = DB::selectOne(
                'SELECT 
                    COUNT(*) as total_conversations,
                    COUNT(CASE WHEN metadata IS NOT NULL AND JSON_CONTAINS_PATH(metadata, "one", "$.memories") THEN 1 END) as conversations_with_memories
                FROM conversations'
            );

            $messageStats = DB::selectOne(
                'SELECT 
                    COUNT(*) as total_messages,
                    AVG(CHAR_LENGTH(content)) as avg_message_length
                FROM messages'
            );

            $summaryStats = DB::selectOne(
                'SELECT COUNT(*) as total_summaries
                FROM conversation_summaries'
            );

            return [
                'conversations' => [
                    'total' => (int)($conversationStats['total_conversations'] ?? 0),
                    'with_memories' => (int)($conversationStats['conversations_with_memories'] ?? 0),
                ],
                'messages' => [
                    'total' => (int)($messageStats['total_messages'] ?? 0),
                    'avg_length' => round($messageStats['avg_message_length'] ?? 0, 1),
                ],
                'summaries' => [
                    'total' => (int)($summaryStats['total_summaries'] ?? 0),
                ],
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get global memory stats', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to get global memory statistics: ' . $e->getMessage(),
            ];
        }
    }
}
