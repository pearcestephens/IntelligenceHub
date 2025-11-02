<?php
declare(strict_types=1);

namespace AiAgent\Services;

use PDO;
use Exception;
use RuntimeException;

/**
 * ConversationLogger - Unified conversation tracking service
 *
 * Responsibilities:
 * - Save user/assistant messages to ai_conversation_messages
 * - Link messages to ai_agent_requests (provider telemetry)
 * - Log tool calls to ai_tool_calls + ai_tool_results
 * - Track message attachments via ai_message_files
 * - Maintain conversation metadata
 *
 * Usage:
 * ```php
 * $logger = new ConversationLogger($pdo);
 * $messageId = $logger->logUserMessage($conversationId, $content, $sequence);
 * $logger->logAssistantMessage($conversationId, $content, $sequence, $provider, $model, $tokensIn, $tokensOut, $requestId);
 * ```
 *
 * @package AiAgent\Services
 * @version 1.0.0
 */
class ConversationLogger
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Log user message
     *
     * @param string $conversationId Conversation UUID
     * @param string $content Message text/markdown
     * @param int $sequence Message sequence number (1-based)
     * @param array|null $metadata Optional metadata (attachments, platform info, etc)
     * @return int message_id
     * @throws RuntimeException on database error
     */
    public function logUserMessage(
        string $conversationId,
        string $content,
        int $sequence,
        ?array $metadata = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_conversation_messages
            (conversation_id, message_sequence, role, content, metadata, created_at)
            VALUES (?, ?, 'user', ?, ?, NOW())
        ");

        $stmt->execute([
            $conversationId,
            $sequence,
            $content,
            $metadata ? json_encode($metadata) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Log assistant message with provider tracking
     *
     * @param string $conversationId
     * @param string $content Assistant response text
     * @param int $sequence Message sequence number
     * @param string|null $provider 'openai', 'anthropic', etc
     * @param string|null $model Model identifier (gpt-4o-mini, claude-3-5-sonnet-latest)
     * @param int|null $tokensIn Prompt tokens
     * @param int|null $tokensOut Completion tokens
     * @param string|null $requestId Provider request UUID for linking
     * @param string|null $responseId Provider response ID if different
     * @param array|null $metadata Optional metadata
     * @return int message_id
     */
    public function logAssistantMessage(
        string $conversationId,
        string $content,
        int $sequence,
        ?string $provider = null,
        ?string $model = null,
        ?int $tokensIn = null,
        ?int $tokensOut = null,
        ?string $requestId = null,
        ?string $responseId = null,
        ?array $metadata = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_conversation_messages
            (conversation_id, message_sequence, role, content, provider, model,
             tokens_in, tokens_out, request_id, response_id, metadata, created_at)
            VALUES (?, ?, 'assistant', ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $conversationId,
            $sequence,
            $content,
            $provider,
            $model,
            $tokensIn,
            $tokensOut,
            $requestId,
            $responseId,
            $metadata ? json_encode($metadata) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Log system message (instructions, context)
     */
    public function logSystemMessage(
        string $conversationId,
        string $content,
        int $sequence,
        ?array $metadata = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_conversation_messages
            (conversation_id, message_sequence, role, content, metadata, created_at)
            VALUES (?, ?, 'system', ?, ?, NOW())
        ");

        $stmt->execute([
            $conversationId,
            $sequence,
            $content,
            $metadata ? json_encode($metadata) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Log provider request telemetry (OpenAI/Anthropic API call)
     *
     * @param string $provider 'openai', 'anthropic', 'azure', 'local'
     * @param string $requestUuid Unique request UUID for tracing
     * @param string|null $conversationId Link to conversation
     * @param int|null $messageId Link to user message that triggered this
     * @param string|null $model Model identifier
     * @param string|null $endpoint API endpoint URL
     * @param int $promptTokens Input tokens
     * @param int $completionTokens Output tokens
     * @param int $totalTokens Total tokens
     * @param int|null $costNzdCents Calculated cost in NZD cents
     * @param int|null $responseTimeMs Latency in milliseconds
     * @param string $status 'success', 'error', 'timeout', 'rate_limited'
     * @param string|null $errorMessage Error details if failed
     * @param array|null $requestHeaders HTTP headers (auth redacted)
     * @param array|null $requestPayload Request body (minified)
     * @param array|null $responseBody Response body (minified)
     * @param int|null $domainId Optional domain linkage
     * @return int request_id
     */
    public function logProviderRequest(
        string $provider,
        string $requestUuid,
        ?string $conversationId = null,
        ?int $messageId = null,
        ?string $model = null,
        ?string $endpoint = null,
        int $promptTokens = 0,
        int $completionTokens = 0,
        int $totalTokens = 0,
        ?int $costNzdCents = null,
        ?int $responseTimeMs = null,
        string $status = 'success',
        ?string $errorMessage = null,
        ?array $requestHeaders = null,
        ?array $requestPayload = null,
        ?array $responseBody = null,
        ?int $domainId = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_agent_requests
            (provider, request_uuid, conversation_id, message_id, model, endpoint,
             prompt_tokens, completion_tokens, total_tokens, cost_nzd_cents,
             response_time_ms, status, error_message, request_headers, request_payload,
             response_body, domain_id, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                response_time_ms = VALUES(response_time_ms),
                status = VALUES(status),
                error_message = VALUES(error_message),
                response_body = VALUES(response_body)
        ");

        $stmt->execute([
            $provider,
            $requestUuid,
            $conversationId,
            $messageId,
            $model,
            $endpoint,
            $promptTokens,
            $completionTokens,
            $totalTokens,
            $costNzdCents,
            $responseTimeMs,
            $status,
            $errorMessage,
            $requestHeaders ? json_encode($requestHeaders) : null,
            $requestPayload ? json_encode($requestPayload) : null,
            $responseBody ? json_encode($responseBody) : null,
            $domainId
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Start tool call tracking
     *
     * @param string $conversationId
     * @param int $messageId Assistant message that invoked tool
     * @param string $toolName Tool identifier (db.query, fs.read, etc)
     * @param string $requestId Unique request ID for this tool call
     * @param array $requestArgs Tool input arguments
     * @return int tool_call_id
     */
    public function logToolCallStart(
        string $conversationId,
        int $messageId,
        string $toolName,
        string $requestId,
        array $requestArgs
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_tool_calls
            (conversation_id, message_id, tool_name, request_id, request,
             status, started_at)
            VALUES (?, ?, ?, ?, ?, 'started', NOW())
        ");

        $stmt->execute([
            $conversationId,
            $messageId,
            $toolName,
            $requestId,
            json_encode($requestArgs)
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Complete tool call with result
     *
     * @param int $toolCallId From logToolCallStart
     * @param string $status 'ok', 'error', 'timeout'
     * @param array $result Tool output
     * @param int|null $latencyMs Execution time in ms
     * @param string|null $errorCode Error code if failed
     * @param int|null $tokensIn If tool used LLM internally
     * @param int|null $tokensOut
     */
    public function logToolCallComplete(
        int $toolCallId,
        string $status,
        array $result,
        ?int $latencyMs = null,
        ?string $errorCode = null,
        ?int $tokensIn = null,
        ?int $tokensOut = null
    ): void {
        // Update tool_calls status
        $stmt = $this->db->prepare("
            UPDATE ai_tool_calls
            SET status = ?, error_code = ?, latency_ms = ?,
                tokens_in = ?, tokens_out = ?, finished_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $errorCode,
            $latencyMs,
            $tokensIn,
            $tokensOut,
            $toolCallId
        ]);

        // Insert result
        $stmt = $this->db->prepare("
            INSERT INTO ai_tool_results (tool_call_id, result, created_at)
            VALUES (?, ?, NOW())
        ");

        $stmt->execute([
            $toolCallId,
            json_encode($result)
        ]);
    }

    /**
     * Link message to file attachment (intelligence_content)
     *
     * @param int $messageId
     * @param int|null $intelligenceContentId FK to intelligence_content
     * @param string|null $filePath Relative path if not in intelligence_content
     * @param string|null $fileType 'image', 'document', 'code', etc
     * @param array|null $metadata File metadata
     * @return int Link ID
     */
    public function linkMessageFile(
        int $messageId,
        ?int $intelligenceContentId = null,
        ?string $filePath = null,
        ?string $fileType = null,
        ?array $metadata = null
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_message_files
            (message_id, intelligence_content_id, file_path, file_type, metadata, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $messageId,
            $intelligenceContentId,
            $filePath,
            $fileType,
            $metadata ? json_encode($metadata) : null
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Get conversation history (messages only)
     *
     * @param string $conversationId
     * @param int $limit Max messages to return
     * @return array Messages with metadata
     */
    public function getConversationHistory(string $conversationId, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT message_id, message_sequence, role, content, provider, model,
                   tokens_in, tokens_out, request_id, response_id, metadata, created_at
            FROM ai_conversation_messages
            WHERE conversation_id = ?
            ORDER BY message_sequence ASC
            LIMIT ?
        ");

        $stmt->execute([$conversationId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get full conversation trace (messages + provider requests + tool calls)
     *
     * @param string $conversationId
     * @return array Full trace from v_ai_conversation_trace view
     */
    public function getConversationTrace(string $conversationId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM v_ai_conversation_trace
            WHERE conversation_id = ?
            ORDER BY message_sequence ASC
        ");

        $stmt->execute([$conversationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
