<?php
/**
 * MCP Hub Integration Test - Health Check Endpoint
 *
 * Checks connection to gpt.ecigdis.co.nz
 *
 * @route GET /dashboard/api/mcp/health
 * @return JSON {success, status, latency, domain}
 */

declare(strict_types=1);

header('Content-Type: application/json');

try {
    // Load MCP configuration
    require_once __DIR__ . '/../../config/mcp-hub.php';

    // Get MCP Hub client
    $hub = mcpHub();

    // Check health
    $health = $hub->healthCheck();

    // Return response
    http_response_code($health['success'] ? 200 : 503);
    echo json_encode([
        'success' => $health['success'],
        'data' => [
            'status' => $health['status'],
            'latency_ms' => $health['latency'],
            'domain' => $health['domain'],
            'connected' => $health['success'],
            'timestamp' => date('Y-m-d H:i:s'),
        ],
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Health check failed: ' . $e->getMessage(),
    ]);
}
