<?php
echo "Test 1: Starting\n";
require_once __DIR__ . '/bootstrap.php';
echo "Test 2: Bootstrap loaded\n";
require_once __DIR__ . '/mcp_tools_turbo.php';
echo "Test 3: Tools loaded\n";
echo "Test 4: MCP_API_KEY = " . (getenv('MCP_API_KEY') ?: 'NOT SET') . "\n";
