<?php
declare(strict_types=1);

require_once __DIR__.'/../../lib/Bootstrap.php';

$rid = new_request_id();
try {
  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    envelope_error('METHOD_NOT_ALLOWED', 'Use GET', $rid, [], 405);
    exit;
  }
  $db = get_pdo();
  require_api_key_if_enabled($db); // optional gate
  $url = (string)env('MCP_SERVER_URL','');
  $hasAuth = (bool)env('MCP_AUTH_TOKEN');
  envelope_success([
    'server_url' => $url,
    'auth_required' => $hasAuth,
  ], $rid, 200);
} catch (Throwable $e) {
  envelope_error('MCP_CONFIG_ERROR', $e->getMessage(), $rid, [], 500);
}
