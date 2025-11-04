<?php
declare(strict_types=1);

/**
 * StreamStore
 * Persists live streaming sessions and their incremental chunks to MySQL.
 * Tables (ensure via migration):
 *  - ai_stream_sessions
 *  - ai_stream_chunks
 */
final class StreamStore
{
    public function __construct(private PDO $db) {}

    /**
     * Create a new stream session row.
     *
     * @param array $p Keys: conversation_id(int), provider, model, platform, session_key,
     *                 user_message_id(int), request_id, correlation_id, unit_id(int|null),
     *                 project_id(int|null), source(string|null)
     * @return int session_id
     */
    public function start(array $p): int
    {
        $stmt = $this->db->prepare("INSERT INTO ai_stream_sessions
            (conversation_id, provider, model, platform, session_key, request_id, correlation_id,
             user_message_id, assistant_message_id, unit_id, project_id, source,
             status, started_at)
            VALUES (?,?,?,?,?,?,?, ?,NULL, ?,?,?, 'running', NOW())");
        $stmt->execute([
            (int)$p['conversation_id'], (string)$p['provider'], (string)$p['model'], (string)($p['platform'] ?? 'github_copilot'),
            (string)($p['session_key'] ?? ''), (string)($p['request_id'] ?? ''), (string)($p['correlation_id'] ?? ''),
            (int)$p['user_message_id'],
            isset($p['unit_id']) ? $p['unit_id'] : null,
            isset($p['project_id']) ? $p['project_id'] : null,
            isset($p['source']) ? $p['source'] : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Append a chunk for a running session.
     */
    public function appendChunk(int $sessionId, int $seq, string $delta, string $type='delta'): void
    {
        $stmt = $this->db->prepare("INSERT INTO ai_stream_chunks (session_id, seq, chunk_type, chunk, bytes, created_at)
                                     VALUES (?,?,?,?,?, NOW())");
        $stmt->execute([$sessionId, $seq, $type, $delta, strlen($delta)]);
    }

    /**
     * Mark the session finished and optionally set assistant_message_id.
     */
    public function finish(int $sessionId, string $status='done', ?int $assistantMessageId=null, ?string $errorMessage=null): void
    {
        $stmt = $this->db->prepare("UPDATE ai_stream_sessions
            SET status=?, assistant_message_id=COALESCE(?, assistant_message_id),
                error_message=?, finished_at=NOW()
            WHERE id=?");
        $stmt->execute([$status, $assistantMessageId, $errorMessage, $sessionId]);
    }
}
