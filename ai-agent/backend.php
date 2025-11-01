<?php
declare(strict_types=1);
/**
 * backend.php (deprecated)
 * This developer console endpoint has been removed to reduce attack surface.
 * Use authenticated APIs under /public/agent/api/ instead.
 * Docs: https://staff.vapeshed.co.nz/assets/cron/utility_scripts/devtools/ai-agent/README.md
 */
http_response_code(410);
header('Content-Type: application/json');
echo json_encode([
  'success' => false,
  'error' => [
    'code' => 'GONE',
    'message' => 'This endpoint has been removed. Use /public/agent/api/* endpoints.'
  ],
  'docs' => 'https://staff.vapeshed.co.nz/assets/cron/utility_scripts/devtools/ai-agent/README.md'
], JSON_UNESCAPED_SLASHES);
exit;