<?php
declare(strict_types=1);

// Lightweight chat UI that calls the existing AI Agent chat API
// No secrets stored in repo; reads optional API base and key from .env

$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
$envPaths = [$docRoot.'/.env', dirname($docRoot).'/private_html/config/.env'];
$env = [];
foreach ($envPaths as $ef) {
  if (!is_file($ef)) continue;
  foreach (file($ef, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $ln) {
    $ln = trim($ln);
    if ($ln === '' || $ln[0] === '#') continue;
    if (strpos($ln, '=') === false) continue;
    [$k,$v] = explode('=', $ln, 2);
    if (!isset($env[$k])) $env[$k] = trim($v);
  }
}

$apiBase = $env['CHAT_API_BASE'] ?? '/assets/services/ai-agent/api';
$apiUrl  = $apiBase . '/chat.php';
$requireAuth = ($env['AI_AGENT_REQUIRE_AUTH'] ?? '0') === '1';
$hasKey = !empty($env['AI_AGENT_TEST_KEY']);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>On-Server Chat</title>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/app.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
  <h1 class="h3 mb-3">On-Server Chat</h1>
  <div class="alert alert-info small">
    API: <code id="api-url"><?php echo htmlspecialchars($apiUrl, ENT_QUOTES, 'UTF-8'); ?></code>
    <?php if ($requireAuth): ?>
      <span class="ms-2 badge bg-warning text-dark">Auth required</span>
    <?php endif; ?>
  </div>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header">Settings</div>
        <div class="card-body small">
          <div class="mb-2">
            <label class="form-label">Provider</label>
            <select id="provider" class="form-select form-select-sm">
              <option value="openai">OpenAI</option>
              <option value="anthropic">Anthropic</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Model</label>
            <input id="model" class="form-control form-control-sm" value="gpt-4o-mini" />
          </div>
          <div class="mb-2">
            <label class="form-label">Temperature</label>
            <input id="temperature" type="number" step="0.1" min="0" max="2" class="form-control form-control-sm" value="0.2" />
          </div>
          <div class="mb-2">
            <label class="form-label">Session Key</label>
            <input id="session_key" class="form-control form-control-sm" />
          </div>
          <div class="mb-2">
            <label class="form-label">Bot Label</label>
            <input id="bot" class="form-control form-control-sm" placeholder="gpt|claude|ops" />
          </div>
          <div class="mb-2">
            <label class="form-label">Org/Unit/Project</label>
            <div class="input-group input-group-sm">
              <input id="org_id" class="form-control" placeholder="org_id" value="1" />
              <input id="unit_id" class="form-control" placeholder="unit_id" />
              <input id="project_id" class="form-control" placeholder="project_id" />
            </div>
          </div>
          <div class="mb-2">
            <label class="form-label">API Key (optional)</label>
            <input id="api_key" class="form-control form-control-sm" value="<?php echo $hasKey ? '•'.str_repeat('•', 8) : '';?>" placeholder="only if auth is enabled" />
            <div class="form-text">Uses Authorization: Bearer</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="card chat-card">
        <div class="card-body p-2">
          <div id="messages" class="messages"></div>
          <div class="d-flex gap-2 mt-2">
            <input id="message" class="form-control" placeholder="Type a message and press Enter" />
            <button id="send" class="btn btn-primary">Send</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
window.CHAT_CONFIG = {
  apiUrl: <?php echo json_encode($apiUrl); ?>,
  hasEnvKey: <?php echo $hasKey ? 'true' : 'false'; ?>
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
