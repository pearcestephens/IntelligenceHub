<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envelope_error('METHOD_NOT_ALLOWED','Use POST', $rid, [], 405); exit;
  }
  $db = get_pdo();
  require_api_key_if_enabled($db);
  $in = req_json();
  $conversationId = isset($in['conversation_id']) ? (int)$in['conversation_id'] : null;
  $sessionKey     = $in['session_key'] ?? null;

  if ($conversationId) {
    $stmt = $db->prepare("SELECT message_sequence, role, content, provider, model, created_at FROM ai_conversation_messages WHERE conversation_id=? ORDER BY message_sequence ASC");
    $stmt->execute([$conversationId]);
  } elseif ($sessionKey) {
    $stmt = $db->prepare("SELECT m.message_sequence, m.role, m.content, m.provider, m.model, m.created_at
      FROM ai_conversation_messages m
      JOIN ai_conversations c ON c.id = m.conversation_id
      WHERE c.session_id = ?
      ORDER BY m.message_sequence ASC");
    $stmt->execute([$sessionKey]);
  } else {
    envelope_error('INVALID_INPUT','conversation_id or session_key required', $rid, [], 422); exit;
  }
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  envelope_success(['history'=>$rows], $rid, 200);
} catch (Throwable $e) {
  envelope_error('HISTORY_FAILURE',$e->getMessage(),$rid,[],500);
}
