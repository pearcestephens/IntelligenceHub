<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';
require_once __DIR__.'/../lib/MemoryStore.php';

$rid = new_request_id();

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envelope_error('METHOD_NOT_ALLOWED', 'Use POST with application/json', $rid, [], 405);
    exit;
  }
  $db = get_pdo();
  require_api_key_if_enabled($db);

  $in = req_json();
  $scope = (string)($in['scope'] ?? 'user');
  $userId = isset($in['user_id']) ? (int)$in['user_id'] : null;
  $conversationId = isset($in['conversation_id']) ? (int)$in['conversation_id'] : null;
  $project = isset($in['project']) ? (string)$in['project'] : null;
  $key = (string)($in['key'] ?? '');
  $value = $in['value'] ?? null;
  $confidence = (int)($in['confidence'] ?? 80);
  if ($key === '' || $value === null) {
    envelope_error('INVALID_INPUT', 'key and value required', $rid, [], 422);
    exit;
  }

  $mem = new MemoryStore($db);
  $mem->upsert($scope, $userId, $conversationId, $project, $key, (array)$value, 'assistant', $confidence);

  envelope_success(['stored'=>true], $rid, 200);

} catch (Throwable $e) {
  envelope_error('MEMORY_FAILURE', $e->getMessage(), $rid, ['trace'=>substr($e->getTraceAsString(),0,800)], 500);
}
