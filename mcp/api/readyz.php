<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();
try {
  $db = get_pdo();
  // Tables existence check
  $need = [
    'ai_conversations','ai_conversation_messages','ai_agent_requests',
    'ai_tool_calls','ai_tool_results','ai_memory','mcp_tool_usage'
  ];
  $missing = [];
  foreach ($need as $t) {
    $st = $db->prepare("SHOW TABLES LIKE ?");
    $st->execute([$t]);
    if (!$st->fetchColumn()) $missing[] = $t;
  }

  // FS write test (under DOCUMENT_ROOT)
  $base = realpath($_SERVER['DOCUMENT_ROOT'] ?? getcwd()) ?: getcwd();
  $tmpFile = $base.'/.ai_agent_readyz.tmp';
  file_put_contents($tmpFile, 'ok @ '.date('c'));
  ensure_backup($tmpFile);
  @unlink($tmpFile);

  envelope_success([
    'ready' => empty($missing),
    'db' => 'ok',
    'missing_tables' => $missing,
    'fs' => 'ok',
    'doc_root' => $base
  ], $rid, empty($missing) ? 200 : 503);

} catch (Throwable $e) {
  envelope_error('READY_FAIL', $e->getMessage(), $rid, [], 500);
}
