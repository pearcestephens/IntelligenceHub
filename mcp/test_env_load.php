<?php
echo "Current directory: " . __DIR__ . "\n";
echo ".env exists: " . (file_exists(__DIR__ . '/.env') ? 'YES' : 'NO') . "\n";
echo ".env readable: " . (is_readable(__DIR__ . '/.env') ? 'YES' : 'NO') . "\n";

if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env')) {
    $env = @parse_ini_file(__DIR__ . '/.env');
    echo "parse_ini_file result: " . ($env ? 'SUCCESS' : 'FAILED') . "\n";
    if ($env) {
        echo "Keys found: " . implode(', ', array_keys($env)) . "\n";
        echo "MCP_API_KEY in parsed: " . (isset($env['MCP_API_KEY']) ? 'YES - ' . substr($env['MCP_API_KEY'], 0, 20) . '...' : 'NO') . "\n";
    } else {
        echo "parse_ini_file error: " . error_get_last()['message'] ?? 'unknown' . "\n";
    }
}
