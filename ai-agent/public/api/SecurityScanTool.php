<?php
/**
 * File: public/api/SecurityScanTool.php
 * Purpose: HTTP wrapper bridging MCP ops.security_scan calls into App\Tools\SecurityScanTool
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Tools\SecurityScanTool;

ApiBootstrap::init();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    ApiBootstrap::error('Method not allowed', 405, 'method_not_allowed');
}

$payload = ApiBootstrap::getJsonBody();
$toolName = isset($payload['tool']) && is_string($payload['tool']) ? $payload['tool'] : 'ops.security_scan';
$arguments = $payload['arguments'] ?? [];

if (!is_array($arguments)) {
    ApiBootstrap::error('Invalid arguments payload', 400, 'invalid_arguments');
}

$context = $payload['context'] ?? [];
if (!is_array($context)) {
    $context = ['raw_context' => $context];
}
$context['request_id'] = ApiBootstrap::getRequestId();
$context['tool'] = $toolName;

try {
    $result = SecurityScanTool::run($arguments, $context);
} catch (\Throwable $exception) {
    ApiBootstrap::error('Security scan tool execution failed', 500, 'tool_error', [
        'message' => $exception->getMessage(),
    ]);
}

ApiBootstrap::respond([
    'tool' => $toolName,
    'result' => $result,
]);
