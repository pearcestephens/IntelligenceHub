#!/usr/bin/env php
<?php
/**
 * Environment Configuration Verification Script
 *
 * Verifies that all environment variables are properly loaded
 * and accessible from all critical locations.
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(strict_types=1);

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  ENVIRONMENT CONFIGURATION VERIFICATION                              ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

// Test locations
$testLocations = [
    'Master .env' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env',
    'MCP symlink' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env',
    'Intelligence Hub' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/admin/intelligence-hub/.env',
];

echo "1. Checking .env file locations:\n";
echo str_repeat("-", 70) . "\n";

foreach ($testLocations as $name => $path) {
    if (file_exists($path)) {
        $isSymlink = is_link($path);
        $size = filesize($path);
        $readable = is_readable($path);

        echo sprintf("✓ %s\n", $name);
        echo sprintf("  Path: %s\n", $path);
        echo sprintf("  Type: %s\n", $isSymlink ? 'Symlink' : 'Regular file');
        if ($isSymlink) {
            echo sprintf("  Target: %s\n", readlink($path));
        }
        echo sprintf("  Size: %s bytes\n", number_format($size));
        echo sprintf("  Readable: %s\n", $readable ? 'Yes' : 'No');
        echo "\n";
    } else {
        echo sprintf("✗ %s - NOT FOUND\n", $name);
        echo sprintf("  Path: %s\n\n", $path);
    }
}

// Load master .env
echo "2. Loading master .env file:\n";
echo str_repeat("-", 70) . "\n";

$masterEnv = '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env';
if (file_exists($masterEnv)) {
    // Load .env file line by line (parse_ini_file doesn't like comments with =)
    $lines = file($masterEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments and empty lines
        if (empty($line) || $line[0] === '#' || $line[0] === ';') {
            continue;
        }
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove quotes if present
            $value = trim($value, '"\'');
            $env[$key] = $value;
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    if (count($env) > 0) {
        echo sprintf("✓ Loaded %d environment variables\n\n", count($env));
    } else {
        echo "✗ No variables found in .env file\n\n";
        exit(1);
    }
} else {
    echo "✗ Master .env file not found\n\n";
    exit(1);
}

// Verify critical variables
echo "3. Verifying critical environment variables:\n";
echo str_repeat("-", 70) . "\n";

$criticalVars = [
    // Database (standardized names)
    'DB_HOST' => 'Database host',
    'DB_NAME' => 'Database name',
    'DB_USER' => 'Database username',
    'DB_PASS' => 'Database password',

    // MCP
    'MCP_API_KEY' => 'MCP API key',
    'MCP_SERVER_URL' => 'MCP server URL',

    // AI Agent
    'AI_AGENT_ENDPOINT' => 'AI Agent endpoint',
    'AI_AGENT_API_KEY' => 'AI Agent API key',
];

$missing = [];
$present = [];

foreach ($criticalVars as $var => $description) {
    $value = $_ENV[$var] ?? '';
    if (empty($value)) {
        echo sprintf("✗ %s - NOT SET or EMPTY\n", $var);
        echo sprintf("  Description: %s\n", $description);
        $missing[] = $var;
    } else {
        $displayValue = strlen($value) > 50 ? substr($value, 0, 30) . '...[' . strlen($value) . ' chars]' : $value;
        // Mask sensitive values
        if (preg_match('/(PASS|KEY|TOKEN|SECRET)/i', $var)) {
            $displayValue = str_repeat('*', min(20, strlen($value)));
        }
        echo sprintf("✓ %s = %s\n", $var, $displayValue);
        $present[] = $var;
    }
}

echo "\n";

// Test database connection
echo "4. Testing database connection:\n";
echo str_repeat("-", 70) . "\n";

try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $_ENV['DB_HOST'] ?? '127.0.0.1',
        $_ENV['DB_NAME'] ?? 'hdgwrzntwa',
        $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    );

    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USER'] ?? 'hdgwrzntwa',
        $_ENV['DB_PASS'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query('SELECT VERSION() as version, DATABASE() as dbname');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "✓ Database connection successful\n";
    echo sprintf("  MySQL Version: %s\n", $result['version']);
    echo sprintf("  Current Database: %s\n", $result['dbname']);
    echo "\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed\n";
    echo sprintf("  Error: %s\n\n", $e->getMessage());
}

// Test MCP server endpoint
echo "5. Testing MCP server endpoint:\n";
echo str_repeat("-", 70) . "\n";

$mcpUrl = $_ENV['MCP_SERVER_URL'] ?? '';
$mcpKey = $_ENV['MCP_API_KEY'] ?? '';

if ($mcpUrl && $mcpKey) {
    $ch = curl_init($mcpUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $mcpKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'params' => [],
            'id' => 1
        ]),
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['result']['tools'])) {
            $toolCount = count($data['result']['tools']);
            echo "✓ MCP server responding\n";
            echo sprintf("  HTTP Status: %d\n", $httpCode);
            echo sprintf("  Tools Available: %d\n", $toolCount);
            echo "\n";
        } else {
            echo "✗ MCP server response invalid\n";
            echo sprintf("  HTTP Status: %d\n", $httpCode);
            echo "\n";
        }
    } else {
        echo "✗ MCP server not responding\n";
        echo sprintf("  HTTP Status: %d\n", $httpCode);
        echo "\n";
    }
} else {
    echo "⚠ MCP configuration incomplete\n\n";
}

// Summary
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICATION SUMMARY                                                ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

echo sprintf("Total Variables Loaded: %d\n", count($_ENV));
echo sprintf("Critical Variables Present: %d/%d\n", count($present), count($criticalVars));

if (count($missing) > 0) {
    echo sprintf("Missing Critical Variables: %d\n", count($missing));
    echo "\nMissing:\n";
    foreach ($missing as $var) {
        echo "  - {$var}\n";
    }
    echo "\n";
}

if (count($missing) === 0) {
    echo "\n✓ ALL CRITICAL VARIABLES CONFIGURED\n";
    echo "✓ SYSTEM READY FOR OPERATION\n\n";
} else {
    echo "\n⚠ SOME VARIABLES MISSING - UPDATE .env FILE\n\n";
}

echo "Master .env location: {$masterEnv}\n";
echo "Edit this file to add missing values\n\n";
