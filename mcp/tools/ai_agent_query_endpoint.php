<?php
/**
 * AI Agent Query - MCP Tool Endpoint
 *
 * HTTP wrapper for ai_agent_query tool
 * Handles POST requests from MCP server
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Load the tool implementation
require_once __DIR__ . '/ai_agent_query.php';

// Get request data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON input'
    ]);
    exit;
}

// Extract arguments
$arguments = $data['arguments'] ?? [];

try {
    // Call the tool function
    $result = ai_agent_query($arguments);

    // Return result
    echo json_encode($result);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'code' => 'TOOL_ERROR'
    ]);
}
