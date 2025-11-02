<?php
/**
 * File: public/api/PerformanceTestTool.php
 * Purpose: HTTP wrapper bridging MCP ops.performance_test calls into App\Tools\PerformanceTestTool
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Tools\PerformanceTestTool;

ApiBootstrap::init();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    ApiBootstrap::error('Method not allowed', 405, 'method_not_allowed');
}

$payload = ApiBootstrap::getJsonBody();
$toolName = isset($payload['tool']) && is_string($payload['tool']) ? $payload['tool'] : 'ops.performance_test';
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
    $result = PerformanceTestTool::run($arguments, $context);
} catch (\Throwable $exception) {
    ApiBootstrap::error('Performance test tool execution failed', 500, 'tool_error', [
        'message' => $exception->getMessage(),
    ]);
}

ApiBootstrap::respond([
    'tool' => $toolName,
    'result' => $result,
]);
