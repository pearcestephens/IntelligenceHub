<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    envelope_error('METHOD_NOT_ALLOWED', 'Use GET', $rid, [], 405);
    exit;
  }

  $db = get_pdo();
  $domainId = require_api_key_if_enabled($db); // optional

  $sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : null;
  $conversationId = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : null;
  $limit = isset($_GET['limit']) ? max(1, min(200, (int)$_GET['limit'])) : 50;

  if ($sessionId) {
    // Return session header + chunks
    $stmt = $db->prepare("SELECT * FROM ai_stream_sessions WHERE id=? LIMIT 1");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();
    if (!$session) {
      envelope_error('NOT_FOUND', 'Session not found', $rid, [], 404);
      exit;
    }
    $chunks = $db->prepare("SELECT seq, chunk_type, chunk, bytes, created_at FROM ai_stream_chunks WHERE session_id=? ORDER BY seq ASC");
    $chunks->execute([$sessionId]);
    envelope_success(['session'=>$session, 'chunks'=>$chunks->fetchAll()], $rid);
    exit;
  }

  // Otherwise list recent sessions (optionally by conversation)
  if ($conversationId) {
    $stmt = $db->prepare("SELECT * FROM ai_stream_sessions WHERE conversation_id=? ORDER BY started_at DESC LIMIT ?");
    $stmt->bindValue(1, $conversationId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    envelope_success(['sessions'=>$stmt->fetchAll()], $rid);
  } else {
    $stmt = $db->prepare("SELECT * FROM ai_stream_sessions ORDER BY started_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    envelope_success(['sessions'=>$stmt->fetchAll()], $rid);
  }
} catch (Throwable $e) {
  envelope_error('STREAMS_API_ERROR', $e->getMessage(), $rid, [], 500);
}
