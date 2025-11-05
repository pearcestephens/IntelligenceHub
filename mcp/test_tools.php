#!/usr/bin/env php
<?php
/**
 * MCP Tool Test Runner
 * Loads tools_manifest.yaml and runs all tests
 */

// Minimal setup - NO Bootstrap.php (to avoid error handlers)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load .env for database password
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (($value[0] ?? '') === '"' && ($value[strlen($value) - 1] ?? '') === '"') {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  🧪 MCP TOOL TEST RUNNER                                              ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

// Load manifest
$manifestFile = __DIR__ . '/tools_manifest.json';
if (!file_exists($manifestFile)) {
    echo "❌ Failed to load tools_manifest.json\n";
    exit(1);
}

$manifestData = json_decode(file_get_contents($manifestFile), true);
if (!$manifestData || !isset($manifestData['tools'])) {
    echo "❌ Invalid manifest format\n";
    exit(1);
}

$manifest = $manifestData['tools'];
echo "📋 Loaded " . count($manifest) . " tools from manifest\n\n";// Load tool registry
$registry = require __DIR__ . '/bootstrap_tools.php';

// Run tests
$passed = 0;
$failed = 0;
$results = [];

foreach ($manifest as $toolName => $config) {
    echo "🔧 Testing: \033[1m$toolName\033[0m\n";
    echo "   Description: {$config['description']}\n";

    if (!isset($config['test'])) {
        echo "   ⚠️  No test defined\n\n";
        continue;
    }

    $testArgs = $config['test']['args'] ?? [];
    $expect = $config['test']['expect'] ?? [];

    try {
        $start = microtime(true);
        $result = $registry->execute($toolName, $testArgs);
        $duration = round((microtime(true) - $start) * 1000, 2);

        // Validate result structure
        if (!is_array($result)) {
            throw new \Exception("Tool returned non-array: " . gettype($result));
        }

        if (!isset($result['status'])) {
            throw new \Exception("Tool result missing 'status' key: " . json_encode($result));
        }

        // Check status
        $statusOk = ($result['status'] ?? 500) == ($expect['status'] ?? 200);

        // Check data keys
        $keysOk = true;
        if (isset($expect['data_keys'])) {
            foreach ($expect['data_keys'] as $key) {
                if (!isset($result['data'][$key])) {
                    $keysOk = false;
                    break;
                }
            }
        }

        if ($statusOk && $keysOk) {
            echo "   ✅ PASS ({$duration}ms)\n";
            if (isset($result['data'])) {
                $preview = json_encode($result['data'], JSON_UNESCAPED_SLASHES);
                if (strlen($preview) > 100) {
                    $preview = substr($preview, 0, 100) . '...';
                }
                echo "   📦 " . $preview . "\n";
            }
            $passed++;
            $results[$toolName] = ['status' => 'PASS', 'duration' => $duration];
        } else {
            echo "   ❌ FAIL ({$duration}ms)\n";
            if (!$statusOk) {
                echo "   Expected status {$expect['status']}, got {$result['status']}\n";
            }
            if (!$keysOk) {
                echo "   Missing expected data keys\n";
            }
            echo "   📦 " . json_encode($result, JSON_UNESCAPED_SLASHES) . "\n";
            $failed++;
            $results[$toolName] = ['status' => 'FAIL', 'duration' => $duration, 'result' => $result];
        }
    } catch (\Throwable $e) {
        echo "   💥 ERROR: " . $e->getMessage() . "\n";
        $failed++;
        $results[$toolName] = ['status' => 'ERROR', 'error' => $e->getMessage()];
    }

    echo "\n";
}

// Summary
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  📊 TEST SUMMARY                                                      ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

echo sprintf("✅ Passed: %d\n", $passed);
echo sprintf("❌ Failed: %d\n", $failed);
echo sprintf("📈 Success Rate: %.1f%%\n", $passed / ($passed + $failed) * 100);

echo "\n";

// Save results
$resultsFile = __DIR__ . '/test_results.json';
file_put_contents($resultsFile, json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'total' => count($manifest),
    'passed' => $passed,
    'failed' => $failed,
    'results' => $results
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "💾 Results saved to: $resultsFile\n\n";

exit($failed > 0 ? 1 : 0);
