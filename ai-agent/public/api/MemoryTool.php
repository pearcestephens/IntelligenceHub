<?php
/**
 * File: public/api/MemoryTool.php
 * Purpose: HTTP wrapper bridging MCP memory tool calls into App\Tools\MemoryTool
 * Author: GitHub Copilot (AI Assistant)
 * Last Modified: 2025-11-02
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

use App\Tools\MemoryTool;

ApiBootstrap::init();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    ApiBootstrap::error('Method not allowed', 405, 'method_not_allowed');
}

$payload = ApiBootstrap::getJsonBody();
$toolName = isset($payload['tool']) && is_string($payload['tool']) ? $payload['tool'] : 'memory.get_context';
$arguments = $payload['arguments'] ?? [];

if (!is_array($arguments)) {
    ApiBootstrap::error('Invalid arguments payload', 400, 'invalid_arguments');
}

$action = $payload['action'] ?? null;
if ($action !== null) {
    if (!is_string($action)) {
        ApiBootstrap::error('Action must be a string when provided', 400, 'invalid_action');
    }
    $arguments['action'] = $action;
}

$context = $payload['context'] ?? [];
if (!is_array($context)) {
    $context = ['raw_context' => $context];
}
$context['request_id'] = ApiBootstrap::getRequestId();
$context['tool'] = $toolName;

try {
    $result = MemoryTool::run($arguments, $context);
} catch (\Throwable $exception) {
    ApiBootstrap::error('Memory tool execution failed', 500, 'tool_error', [
        'message' => $exception->getMessage(),
    ]);
}

ApiBootstrap::respond([
    'tool' => $toolName,
    'action' => $arguments['action'] ?? null,
    'result' => $result,
]);
