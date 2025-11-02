<?php
declare(strict_types=1);

require_once __DIR__.'/../lib/Bootstrap.php';

$rid = new_request_id();
try {
  $db = get_pdo();
  $db->query("SELECT 1")->fetchColumn();
  envelope_success(['db'=> 'ok'], $rid, 200);
} catch (Throwable $e) {
  envelope_error('HEALTH_FAIL', $e->getMessage(), $rid, [], 500);
}
