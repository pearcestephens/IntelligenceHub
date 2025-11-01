<?php
/**
 * Phase 1 Foundation Test
 * Tests database connection, cache system, and configuration
 *
 * RUN: php tests/phase1_foundation_test.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IntelligenceHub\MCP\Database\Connection;
use IntelligenceHub\MCP\Cache\CacheManager;
use IntelligenceHub\MCP\Config\Config;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  PHASE 1 FOUNDATION TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

$tests = [];
$passed = 0;
$failed = 0;

// TEST 1: Database Connection
echo "TEST 1: Database Connection\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $pdo = Connection::getInstance();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM intelligence_content");
    $result = $stmt->fetch();
    $count = $result['count'];

    echo "✅ PASSED: Connected to database\n";
    echo "   Found {$count} indexed files in intelligence_content\n";

    $stats = Connection::getStats();
    echo "   Database: {$stats['database']}\n";
    echo "   Threads: {$stats['threads_connected']}\n";

    $tests[] = ['name' => 'Database Connection', 'status' => 'PASSED'];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Database Connection', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// TEST 2: Configuration System
echo "TEST 2: Configuration System\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    Config::init(__DIR__ . '/..');

    $dbHost = Config::get('database.host');
    $cacheEnabled = Config::get('cache.enable_redis');
    $satellites = Config::get('satellites');

    echo "✅ PASSED: Configuration loaded\n";
    echo "   Database host: {$dbHost}\n";
    echo "   Redis enabled: " . ($cacheEnabled ? 'Yes' : 'No') . "\n";
    echo "   Satellites configured: " . count($satellites) . "\n";

    $tests[] = ['name' => 'Configuration System', 'status' => 'PASSED'];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Configuration System', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// TEST 3: Cache Manager Initialization
echo "TEST 3: Cache Manager Initialization\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $cache = new CacheManager([
        'enable_redis' => true,
        'enable_apcu' => true,
        'enable_file' => true,
        'ttl' => 3600,
    ]);

    $backends = $cache->getAvailableBackends();
    echo "✅ PASSED: Cache manager initialized\n";
    echo "   Available backends: " . implode(', ', $backends) . "\n";

    $tests[] = ['name' => 'Cache Manager Init', 'status' => 'PASSED', 'backends' => $backends];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Cache Manager Init', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// TEST 4: Cache Set/Get Operations
echo "TEST 4: Cache Set/Get Operations\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $testKey = 'test_' . time();
    $testValue = ['message' => 'Hello from Phase 1!', 'timestamp' => time()];

    // Set value
    $setResult = $cache->set($testKey, $testValue, 60);
    if (!$setResult) {
        throw new Exception('Failed to set cache value');
    }

    // Get value
    $getValue = $cache->get($testKey);
    if ($getValue !== $testValue) {
        throw new Exception('Cache value mismatch');
    }

    echo "✅ PASSED: Cache operations working\n";
    echo "   Set: {$testKey}\n";
    echo "   Get: " . json_encode($getValue) . "\n";

    // Delete value
    $cache->delete($testKey);

    $tests[] = ['name' => 'Cache Operations', 'status' => 'PASSED'];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Cache Operations', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// TEST 5: Cache Statistics
echo "TEST 5: Cache Statistics\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $stats = $cache->getStats();

    echo "✅ PASSED: Cache stats retrieved\n";
    echo "   Backends: " . implode(', ', $stats['backends']) . "\n";
    echo "   Total hits: {$stats['total_hits']}\n";
    echo "   Total misses: {$stats['total_misses']}\n";
    echo "   Hit rate: {$stats['hit_rate']}%\n";

    if (!empty($stats['backend_stats'])) {
        foreach ($stats['backend_stats'] as $backend => $backendStats) {
            echo "   {$backend}: " . json_encode($backendStats) . "\n";
        }
    }

    $tests[] = ['name' => 'Cache Statistics', 'status' => 'PASSED'];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Cache Statistics', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// TEST 6: Database Query Performance
echo "TEST 6: Database Query Performance\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $start = microtime(true);

    $stmt = $pdo->query("
        SELECT content_path, intelligence_score, quality_score
        FROM intelligence_content
        ORDER BY intelligence_score DESC
        LIMIT 10
    ");
    $results = $stmt->fetchAll();

    $duration = round((microtime(true) - $start) * 1000, 2);

    echo "✅ PASSED: Query executed successfully\n";
    echo "   Duration: {$duration}ms\n";
    echo "   Results: " . count($results) . " rows\n";

    if ($duration < 100) {
        echo "   ⚡ Performance: EXCELLENT (< 100ms)\n";
    } elseif ($duration < 500) {
        echo "   ✓ Performance: GOOD (< 500ms)\n";
    } else {
        echo "   ⚠ Performance: SLOW (> 500ms)\n";
    }

    $tests[] = ['name' => 'Query Performance', 'status' => 'PASSED', 'duration_ms' => $duration];
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $tests[] = ['name' => 'Query Performance', 'status' => 'FAILED', 'error' => $e->getMessage()];
    $failed++;
}
echo "\n";

// SUMMARY
echo "═══════════════════════════════════════════════════════════════\n";
echo "  TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Total tests: " . ($passed + $failed) . "\n";
echo "✅ Passed: {$passed}\n";
echo "❌ Failed: {$failed}\n";
echo "Success rate: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED! Phase 1 Foundation is solid.\n";
    echo "✓ Database connection working\n";
    echo "✓ Configuration system working\n";
    echo "✓ Multi-level cache working\n";
    echo "✓ Ready to proceed to Phase 2 (Indexing)\n";
} else {
    echo "⚠ SOME TESTS FAILED. Review errors above.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

// Save test results to file
file_put_contents(
    __DIR__ . '/../logs/phase1_test_results.json',
    json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'total' => $passed + $failed,
        'passed' => $passed,
        'failed' => $failed,
        'tests' => $tests,
    ], JSON_PRETTY_PRINT)
);

exit($failed > 0 ? 1 : 0);
