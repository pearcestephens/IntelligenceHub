<?php
declare(strict_types=1);

/**
 * Telemetry writes for conversations, provider requests, tools, errors.
 * Uses your existing tables:
 *  - ai_conversation_messages
 *  - ai_agent_requests
 *  - ai_tool_calls / ai_tool_results
 *  - mcp_tool_usage (flat stream)
 */
final class Telemetry {
  public function __construct(private PDO $db) {}

  public function logUserMessage(
    int $conversationId, int $sequence, string $content, ?array $attachments=null
  ): int {
    $stmt = $this->db->prepare("
      INSERT INTO ai_conversation_messages
        (conversation_id, message_sequence, role, content, provider, model, tokens_estimated, attachments, metadata)
      VALUES (?,?,?,?, 'none', NULL, 0, ?, JSON_OBJECT())
    ");
    $stmt->execute([$conversationId, $sequence, 'user', $content, $attachments ? json_encode($attachments, JSON_UNESCAPED_UNICODE) : null]);
    return (int)$this->db->lastInsertId();
  }

  public function logAssistantMessage(
    int $conversationId, int $sequence, string $provider, string $model,
    string $content, ?int $tokensIn, ?int $tokensOut, ?string $requestId, ?string $responseId
  ): int {
    $stmt = $this->db->prepare("
      INSERT INTO ai_conversation_messages
        (conversation_id, message_sequence, role, content, provider, model, tokens_in, tokens_out, request_id, response_id)
      VALUES (?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([$conversationId, $sequence, 'assistant', $content, $provider, $model, $tokensIn, $tokensOut, $requestId, $responseId]);
    return (int)$this->db->lastInsertId();
  }

  public function logProviderRequest(
    ?int $domainId, string $provider, int $conversationId, int $messageId,
    string $requestId, string $model, string $endpoint,
    ?int $promptTokens, ?int $completionTokens, ?int $totalTokens, ?int $costCents,
    int $latencyMs, string $status, ?string $errorMessage,
    ?array $headers=null, ?array $payload=null, ?array $response=null,
    ?int $unitId=null, ?int $projectId=null, ?string $source=null
  ): void {
    $stmt = $this->db->prepare("
      INSERT INTO ai_agent_requests
        (domain_id, provider, conversation_id, unit_id, project_id, source,
         message_id, request_id, model, endpoint,
         prompt_tokens, completion_tokens, total_tokens, cost_nzd_cents,
         response_time_ms, status, error_message, request_headers, request_payload, response_body)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
      $domainId, $provider, $conversationId, $unitId, $projectId, $source,
      $messageId, $requestId, $model, $endpoint,
      $promptTokens, $completionTokens, $totalTokens, $costCents,
      $latencyMs, $status, $errorMessage,
      $headers ? json_encode($headers, JSON_UNESCAPED_UNICODE) : null,
      $payload ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
      $response ? json_encode($response, JSON_UNESCAPED_UNICODE) : null
    ]);
  }

  public function toolStart(
    ?int $conversationId, ?int $messageId, string $toolName, string $requestId, array $request
  ): int {
    $stmt = $this->db->prepare("
      INSERT INTO ai_tool_calls (conversation_id, message_id, tool_name, request_id, request, status)
      VALUES (?,?,?,?,?, 'started')
    ");
    $stmt->execute([$conversationId, $messageId, $toolName, $requestId, json_encode($request, JSON_UNESCAPED_UNICODE)]);
    return (int)$this->db->lastInsertId();
  }

  public function toolFinish(
    int $toolCallId, string $status, ?string $errorCode, int $latencyMs, ?int $tokIn=null, ?int $tokOut=null, ?array $result=null
  ): void {
    $this->db->prepare("UPDATE ai_tool_calls SET status=?, error_code=?, latency_ms=?, tokens_in=?, tokens_out=?, finished_at=NOW() WHERE id=?")
      ->execute([$status, $errorCode, $latencyMs, $tokIn, $tokOut, $toolCallId]);

    $this->db->prepare("INSERT INTO ai_tool_results (tool_call_id, result) VALUES (?,?)")
      ->execute([$toolCallId, $result ? json_encode($result, JSON_UNESCAPED_UNICODE) : null]);
  }

  public function toolFlatStream(
    string $toolName, array $args, int $latencyMs, bool $success, ?string $errorMsg,
    ?string $sessionId, ?int $conversationId, ?int $messageId, string $requestId, ?int $resultsCount=null
  ): void {
    $this->db->prepare("
      INSERT INTO mcp_tool_usage (tool_name, arguments, execution_time_ms, results_count, success, error_message, session_id, conversation_id, message_id, request_id)
      VALUES (?,?,?,?,?,?,?,?,?,?)
    ")->execute([
      $toolName,
      json_encode($args, JSON_UNESCAPED_UNICODE),
      $latencyMs,
      $resultsCount ?? 0,
      $success ? 1 : 0,
      $errorMsg ? substr($errorMsg, 0, 1000) : null,
      $sessionId,
      $conversationId,
      $messageId,
      $requestId
    ]);
  }
}
