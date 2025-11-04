<?php
declare(strict_types=1);
require_once __DIR__.'/../../lib/Bootstrap.php';

$rid = new_request_id();
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') { envelope_error('METHOD_NOT_ALLOWED','Use POST', $rid, [], 405); exit; }
  $db = get_pdo();
  require_api_key_if_enabled($db);
  $in = req_json();
  $tool = (string)($in['tool'] ?? '');
  $args = $in['args'] ?? [];
  if ($tool === '') { envelope_error('INVALID_INPUT','tool is required', $rid, [], 422); exit; }

  // Create ticket row if table exists; otherwise return 501
  $ticket = bin2hex(random_bytes(16));
  try {
    $stmt = $db->prepare("INSERT INTO ai_stream_tickets (ticket, tool, args, request_id, expires_at) VALUES (?,?,?,?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    $stmt->execute([$ticket, $tool, json_encode($args, JSON_UNESCAPED_UNICODE), $rid]);
  } catch (Throwable $e) {
    envelope_error('NOT_IMPLEMENTED','ai_stream_tickets table missing', $rid, [], 501); exit;
  }

  $sse = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/assets/services/ai-agent/mcp/events.php?ticket=' . urlencode($ticket);
  envelope_success(['ticket'=>$ticket,'sse'=>$sse,'request_id'=>$rid], $rid, 200);
} catch (Throwable $e) {
  envelope_error('TICKET_FAILURE', $e->getMessage(), $rid, [], 500);
}
