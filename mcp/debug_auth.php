<?php
/**
 * Debug script to check what server_v3.php sees
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/mcp_tools_turbo.php';

echo "Environment after bootstrap:\n";
echo "MCP_API_KEY from getenv: " . (getenv('MCP_API_KEY') ?: 'NOT SET') . "\n";
echo "MCP_API_KEY from \$_ENV: " . ($_ENV['MCP_API_KEY'] ?? 'NOT SET') . "\n";
echo "MCP_API_KEY from \$_SERVER: " . ($_SERVER['MCP_API_KEY'] ?? 'NOT SET') . "\n\n";

// Test the exact key validation
$testKey = '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';
echo "Testing key: " . substr($testKey, 0, 20) . "...\n";

try {
    enforce_api_key($testKey);
    echo "✅ enforce_api_key PASSED!\n";
} catch (Exception $e) {
    echo "❌ enforce_api_key FAILED: " . $e->getMessage() . "\n";
    echo "Expected key would be: " . envv('MCP_API_KEY', 'bFUdRjh4Jx') . "\n";
}
