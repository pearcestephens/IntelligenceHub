<?php

/**
 * Conversation Summarizer for Enhanced Memory
 * Creates and maintains running summaries of conversations
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

use App\Config;
use App\DB;
use App\RedisClient;
use App\OpenAI;
use App\Logger;
use App\Util\Ids;

class Summarizer
{
    private const SUMMARY_PROMPT = <<<EOD
Please create a concise summary of this conversation so far. Focus on:

1. Key topics discussed and decisions made
2. Important information discovered or provided
3. Tasks completed and their outcomes
4. Unresolved questions or pending actions
5. User preferences or context that should be remembered

Format as bullet points under clear headings. Keep it under 400 words.

Conversation messages:
EOD;

    /**
     * Check if conversation needs summarization
     */
    public function __construct(?OpenAI $openai = null, ?Logger $logger = null)
    {
        // No instance state; static methods used. Constructor for DI compatibility.
    }

    /**
     * Check if conversation needs summarization
     */
    public static function needsSummary(string $conversationId): bool
    {
        $summaryEvery = Config::get('SUMMARY_EVERY', 8);

        // Count user messages since last summary
        $sql = "
            SELECT COUNT(*) as user_messages
            FROM messages 
            WHERE conversation_id = ? 
            AND role = 'user' 
            AND created_at > COALESCE(
                (SELECT MAX(created_at) FROM messages 
                 WHERE conversation_id = ? AND role = 'system' AND tool_name = 'summary'), 
                '1970-01-01'
            )
        ";

        $result = DB::selectOne($sql, [$conversationId, $conversationId]);
        $userMessages = (int)($result['user_messages'] ?? 0);

        Logger::debug('Summary check', [
            'conversation_id' => $conversationId,
            'user_messages' => $userMessages,
            'summary_threshold' => $summaryEvery
        ]);

        return $userMessages >= $summaryEvery;
    }

    /**
     * Generate summary for conversation
     */
    public static function generateSummary(string $conversationId): ?array
    {
        try {
            // Get messages for summarization (excluding existing summaries)
            $messages = self::getMessagesForSummary($conversationId);

            if (empty($messages)) {
                Logger::warning('No messages found for summarization', [
                    'conversation_id' => $conversationId
                ]);
                return null;
            }

            // Build prompt with conversation context
            $conversationText = self::buildConversationText($messages);
            $prompt = self::SUMMARY_PROMPT . "\n\n" . $conversationText;

            Logger::info('Generating conversation summary', [
                'conversation_id' => $conversationId,
                'messages_count' => count($messages),
                'text_length' => strlen($conversationText)
            ]);

            // Call OpenAI to generate summary
            $response = OpenAI::createChatCompletion([
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]);

            $summary = $response['choices'][0]['message']['content'] ?? '';

            if (empty($summary)) {
                Logger::error('Empty summary generated', [
                    'conversation_id' => $conversationId
                ]);
                return null;
            }

            // Store summary in database and Redis
            $summaryData = [
                'conversation_id' => $conversationId,
                'content' => $summary,
                'messages_count' => count($messages),
                'tokens_input' => $response['usage']['prompt_tokens'] ?? 0,
                'tokens_output' => $response['usage']['completion_tokens'] ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ];

            self::storeSummary($conversationId, $summaryData);

            Logger::info('Summary generated successfully', [
                'conversation_id' => $conversationId,
                'summary_length' => strlen($summary),
                'tokens_used' => $summaryData['tokens_input'] + $summaryData['tokens_output']
            ]);

            return $summaryData;
        } catch (\Throwable $e) {
            Logger::error('Summary generation failed', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get latest summary for conversation
     */
    public static function getLatestSummary(string $conversationId): ?array
    {
        // Try Redis first (fast access)
        $redisKey = Ids::redisKey('conv', $conversationId, 'summary');
        $cached = RedisClient::get($redisKey);

        if ($cached) {
            Logger::debug('Summary retrieved from cache', [
                'conversation_id' => $conversationId
            ]);
            return $cached;
        }

        // Fall back to database
        $sql = "
            SELECT content, created_at, tokens_input, tokens_output
            FROM messages 
            WHERE conversation_id = ? 
            AND role = 'system' 
            AND tool_name = 'summary'
            ORDER BY created_at DESC 
            LIMIT 1
        ";

        $result = DB::selectOne($sql, [$conversationId]);

        if ($result) {
            $summaryData = [
                'conversation_id' => $conversationId,
                'content' => $result['content'],
                'created_at' => $result['created_at'],
                'tokens_input' => $result['tokens_input'],
                'tokens_output' => $result['tokens_output']
            ];

            // Cache in Redis for future access
            RedisClient::set($redisKey, $summaryData, 3600); // 1 hour TTL

            Logger::debug('Summary retrieved from database and cached', [
                'conversation_id' => $conversationId
            ]);

            return $summaryData;
        }

        return null;
    }

    /**
     * Get conversation history with summaries for context building
     */
    public static function getConversationContext(string $conversationId, int $recentMessagesLimit = 40): array
    {
        $context = [];

        // Add latest summary if available
        $summary = self::getLatestSummary($conversationId);
        if ($summary) {
            $context[] = [
                'role' => 'system',
                'content' => "Previous conversation summary:\n\n" . $summary['content'],
                'timestamp' => $summary['created_at']
            ];
        }

        // Add recent messages (excluding summary messages)
        $sql = "
            SELECT role, content, tool_name, created_at
            FROM messages 
            WHERE conversation_id = ? 
            AND (role != 'system' OR tool_name != 'summary')
            ORDER BY created_at DESC 
            LIMIT ?
        ";

        $messages = DB::select($sql, [$conversationId, $recentMessagesLimit]);

        // Reverse to chronological order
        $messages = array_reverse($messages);

        foreach ($messages as $message) {
            $context[] = [
                'role' => $message['role'],
                'content' => $message['content'],
                'timestamp' => $message['created_at']
            ];
        }

        Logger::debug('Conversation context assembled', [
            'conversation_id' => $conversationId,
            'has_summary' => $summary !== null,
            'recent_messages' => count($messages),
            'total_context_items' => count($context)
        ]);

        return $context;
    }

    /**
     * Summarize a list of messages into a short paragraph.
     * This helper is used for token-optimization paths.
     */
    public static function summarizeMessages(array $messages): string
    {
        // Build a compact text of messages
        $parts = [];
        foreach ($messages as $m) {
            if (is_array($m)) {
                $role = $m['role'] ?? 'user';
                $content = $m['content'] ?? '';
                $parts[] = strtoupper($role) . ': ' . $content;
            } elseif (is_string($m)) {
                $parts[] = $m;
            }
        }
        $text = implode("\n", $parts);

        // If text is short, return directly; otherwise, request summary via OpenAI
        if (strlen($text) < 800) {
            return $text;
        }

        try {
            $response = OpenAI::createChatCompletion([
                [
                    'role' => 'user',
                    'content' => "Summarize the following messages briefly in under 200 words:\n\n" . $text
                ]
            ]);
            $summary = $response['choices'][0]['message']['content'] ?? '';
            return $summary ?: substr($text, 0, 800) . '...';
        } catch (\Throwable $e) {
            Logger::warning('SummarizeMessages fallback due to error', ['error' => $e->getMessage()]);
            return substr($text, 0, 800) . '...';
        }
    }

    /**
     * Force summarization of conversation
     */
    public static function forceSummarize(string $conversationId): ?array
    {
        Logger::info('Forcing conversation summarization', [
            'conversation_id' => $conversationId
        ]);

        return self::generateSummary($conversationId);
    }

    /**
     * Get messages for summarization (since last summary)
     */
    private static function getMessagesForSummary(string $conversationId): array
    {
        // Get all messages since the last summary (or from beginning)
        $sql = "
            SELECT role, content, tool_name, created_at
            FROM messages 
            WHERE conversation_id = ? 
            AND created_at > COALESCE(
                (SELECT MAX(created_at) FROM messages 
                 WHERE conversation_id = ? AND role = 'system' AND tool_name = 'summary'), 
                '1970-01-01'
            )
            AND (role != 'system' OR tool_name != 'summary')
            ORDER BY created_at ASC
        ";

        return DB::select($sql, [$conversationId, $conversationId]);
    }

    /**
     * Build conversation text for summarization
     */
    private static function buildConversationText(array $messages): string
    {
        $text = [];

        foreach ($messages as $message) {
            $role = ucfirst($message['role']);
            $content = $message['content'];

            // Truncate very long messages
            if (strlen($content) > 1000) {
                $content = substr($content, 0, 997) . '...';
            }

            // Format tool messages differently
            if ($message['role'] === 'tool') {
                $toolName = $message['tool_name'] ?? 'unknown';
                $text[] = "Tool ({$toolName}): {$content}";
            } else {
                $text[] = "{$role}: {$content}";
            }
        }

        return implode("\n\n", $text);
    }

    /**
     * Store summary in database and Redis
     */
    private static function storeSummary(string $conversationId, array $summaryData): void
    {
        // Store in database as a system message
        $sql = "
            INSERT INTO messages (
                conversation_id, role, content, tool_name, 
                tokens_input, tokens_output, created_at
            ) VALUES (?, 'system', ?, 'summary', ?, ?, ?)
        ";

        DB::execute($sql, [
            $conversationId,
            $summaryData['content'],
            $summaryData['tokens_input'],
            $summaryData['tokens_output'],
            $summaryData['created_at']
        ]);

        // Cache in Redis
        $redisKey = Ids::redisKey('conv', $conversationId, 'summary');
        RedisClient::set($redisKey, $summaryData, 3600); // 1 hour TTL

        Logger::info('Summary stored', [
            'conversation_id' => $conversationId,
            'storage' => 'database + redis'
        ]);
    }

    /**
     * Get summary statistics
     */
    public static function getStatistics(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_summaries,
                AVG(tokens_input + tokens_output) as avg_tokens,
                MAX(created_at) as latest_summary
            FROM messages 
            WHERE role = 'system' AND tool_name = 'summary'
        ";

        $result = DB::selectOne($sql);

        return [
            'total_summaries' => (int)($result['total_summaries'] ?? 0),
            'average_tokens' => (int)($result['avg_tokens'] ?? 0),
            'latest_summary' => $result['latest_summary'] ?? null
        ];
    }
}
