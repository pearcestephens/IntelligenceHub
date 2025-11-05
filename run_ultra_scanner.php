#!/usr/bin/env php
<?php
require_once __DIR__ . '/mcp/src/Tools/UltraScannerV3.php';

use MCP\Tools\UltraScannerV3;

$db = new PDO(
    'mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4',
    'hdgwrzntwa',
    'bFUdRjh4Jx',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "🚀 SCANNER V3 ULTRA - COMPLETE CODE INTELLIGENCE\n";
echo str_repeat('=', 80) . "\n\n";

$scanner = new UltraScannerV3($db);

echo "Starting ultra scan...\n\n";

$results = $scanner->scanEverything();

echo "\n\n";
echo str_repeat('=', 80) . "\n";
echo "✅ ULTRA SCAN COMPLETE!\n";
echo str_repeat('=', 80) . "\n\n";

echo "RESULTS:\n";
foreach ($results as $key => $value) {
    echo sprintf("  %-25s: %s\n", $key, number_format($value));
}

echo "\n";
