<?php
declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') { envelope_error('METHOD_NOT_ALLOWED','Use POST', $rid, [], 405); exit; }
  $db = get_pdo();
  require_api_key_if_enabled($db);
  $in = req_json();
  $platform = (string)($in['platform'] ?? 'github_copilot');
  $sessionKey = isset($in['session_key']) ? (string)$in['session_key'] : null;
  $limit = max(1, min(50, (int)($in['limit'] ?? 20)));

  if ($sessionKey) {
    $stmt = $db->prepare("SELECT id, session_id, platform, org_id, unit_id, project_id, source, status, created_at, updated_at
      FROM ai_conversations WHERE session_id=? AND platform=? ORDER BY updated_at DESC LIMIT ?");
    $stmt->bindValue(1, $sessionKey, PDO::PARAM_STR);
    $stmt->bindValue(2, $platform, PDO::PARAM_STR);
    $stmt->bindValue(3, $limit, PDO::PARAM_INT);
    $stmt->execute();
  } else {
    $stmt = $db->prepare("SELECT id, session_id, platform, org_id, unit_id, project_id, source, status, created_at, updated_at
      FROM ai_conversations WHERE platform=? ORDER BY updated_at DESC LIMIT ?");
    $stmt->bindValue(1, $platform, PDO::PARAM_STR);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
  }

  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  envelope_success(['conversations'=>$rows], $rid, 200);
} catch (Throwable $e) {
  envelope_error('CONVERSATIONS_FAILURE', $e->getMessage(), $rid, [], 500);
}
