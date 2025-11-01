<?php
/**
 * Health Check v3 - Compatible with existing health.php
 * Uses new PSR-12 architecture while maintaining backward compatibility
 *
 * @package IntelligenceHub\MCP
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

use IntelligenceHub\MCP\Tools\HealthCheckTool;
use IntelligenceHub\MCP\Config\Config;

try {
    // Initialize configuration
    Config::init(__DIR__);

    // Execute health check
    $healthCheck = new HealthCheckTool();
    $result = $healthCheck->execute();

    // Set appropriate HTTP status code
    http_response_code($result['status'] === 'healthy' ? 200 : 503);

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
