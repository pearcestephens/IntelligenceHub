<?php
/**
 * File: public/api/ReadyCheckTool.php
 * Purpose: HTTP wrapper bridging MCP ops.ready_check calls into App\Tools\ReadyCheckTool
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Tools\ReadyCheckTool;

ApiBootstrap::init();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    ApiBootstrap::error('Method not allowed', 405, 'method_not_allowed');
}

$payload = ApiBootstrap::getJsonBody();
$toolName = isset($payload['tool']) && is_string($payload['tool']) ? $payload['tool'] : 'ops.ready_check';
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
    $result = ReadyCheckTool::run($arguments, $context);
} catch (\Throwable $exception) {
    ApiBootstrap::error('Ready check tool execution failed', 500, 'tool_error', [
        'message' => $exception->getMessage(),
    ]);
}

ApiBootstrap::respond([
    'tool' => $toolName,
    'result' => $result,
]);
