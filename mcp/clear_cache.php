<?php
/**
 * Clear OPcache and test environment loading
 * Access via: https://gpt.ecigdis.co.nz/mcp/clear_cache.php
 */

// Clear opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared\n";
} else {
    echo "ℹ️  OPcache not enabled\n";
}

// Reload bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "\n🔍 Environment check:\n";
echo "MCP_API_KEY: " . (getenv('MCP_API_KEY') ?: 'NOT SET') . "\n";
echo "API_KEY: " . (getenv('API_KEY') ?: 'NOT SET') . "\n";

echo "\n✅ Cache cleared and environment reloaded!\n";
echo "Now test your MCP authentication again.\n";
