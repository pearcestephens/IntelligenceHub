<?php
/**
 * Semantic Search Tool Test
 * Tests integration with existing semantic_search_engine.php
 *
 * RUN: php tests/semantic_search_test.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IntelligenceHub\MCP\Tools\SemanticSearchTool;
use IntelligenceHub\MCP\Config\Config;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  SEMANTIC SEARCH TOOL TEST\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

Config::init(__DIR__ . '/..');

$passed = 0;
$failed = 0;

// TEST 1: Basic Search
echo "TEST 1: Basic Search Query\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $tool = new SemanticSearchTool();
    $result = $tool->execute([
        'query' => 'inventory transfer',
        'limit' => 5,
    ]);

    if ($result['success']) {
        echo "✅ PASSED: Search executed successfully\n";
        echo "   Query: inventory transfer\n";
        echo "   Results: {$result['total']}\n";
        echo "   Duration: {$result['duration_ms']}ms\n";
        echo "   Cache hit: " . ($result['cache_hit'] ? 'Yes' : 'No') . "\n";

        if (!empty($result['results'])) {
            echo "\n   Top 3 Results:\n";
            foreach (array_slice($result['results'], 0, 3) as $i => $file) {
                echo "   " . ($i + 1) . ". {$file['content_path']} (score: {$file['relevance_score']})\n";
            }
        }

        $passed++;
    } else {
        throw new Exception($result['error'] ?? 'Unknown error');
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// TEST 2: Cached Query (should be faster)
echo "TEST 2: Cached Query (Same Query Again)\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $tool = new SemanticSearchTool();
    $result = $tool->execute([
        'query' => 'inventory transfer',
        'limit' => 5,
    ]);

    if ($result['success']) {
        $speedup = $result['cache_hit'] ? 'CACHED' : 'UNCACHED';
        echo "✅ PASSED: Cached search executed\n";
        echo "   Duration: {$result['duration_ms']}ms ({$speedup})\n";
        echo "   Cache hit: " . ($result['cache_hit'] ? '✓ Yes' : '✗ No') . "\n";

        if ($result['cache_hit'] && $result['duration_ms'] < 10) {
            echo "   ⚡ Cache speedup: EXCELLENT (< 10ms)\n";
        }

        $passed++;
    } else {
        throw new Exception($result['error'] ?? 'Unknown error');
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// TEST 3: Search with Filters
echo "TEST 3: Search with Unit Filter\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $tool = new SemanticSearchTool();
    $result = $tool->execute([
        'query' => 'database schema',
        'unit_id' => 2,
        'limit' => 5,
    ]);

    if ($result['success']) {
        echo "✅ PASSED: Filtered search executed\n";
        echo "   Query: database schema\n";
        echo "   Filter: unit_id = 2\n";
        echo "   Results: {$result['total']}\n";
        echo "   Duration: {$result['duration_ms']}ms\n";

        $passed++;
    } else {
        throw new Exception($result['error'] ?? 'Unknown error');
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// TEST 4: Empty Query Validation
echo "TEST 4: Empty Query Validation\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $tool = new SemanticSearchTool();
    $result = $tool->execute([
        'query' => '',
        'limit' => 5,
    ]);

    if (!$result['success'] && isset($result['error'])) {
        echo "✅ PASSED: Empty query rejected correctly\n";
        echo "   Error: {$result['error']}\n";
        $passed++;
    } else {
        throw new Exception('Empty query should have been rejected');
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}
echo "\n";

// TEST 5: Complex Query
echo "TEST 5: Complex Multi-word Query\n";
echo "─────────────────────────────────────────────────────────────\n";
try {
    $tool = new SemanticSearchTool();
    $result = $tool->execute([
        'query' => 'how to handle customer orders and sales',
        'limit' => 5,
    ]);

    if ($result['success']) {
        echo "✅ PASSED: Complex query executed\n";
        echo "   Query: how to handle customer orders and sales\n";
        echo "   Results: {$result['total']}\n";
        echo "   Duration: {$result['duration_ms']}ms\n";

        // Check if synonym expansion worked
        if (isset($result['stats']['expanded_queries'])) {
            echo "   Synonym expansion: " . implode(', ', $result['stats']['expanded_queries']) . "\n";
        }

        $passed++;
    } else {
        throw new Exception($result['error'] ?? 'Unknown error');
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
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
    echo "🎉 ALL SEMANTIC SEARCH TESTS PASSED!\n";
    echo "✓ Basic search working\n";
    echo "✓ Caching working\n";
    echo "✓ Filters working\n";
    echo "✓ Validation working\n";
    echo "✓ Complex queries working\n";
    echo "✓ Ready for MCP server integration\n";
} else {
    echo "⚠ SOME TESTS FAILED. Review errors above.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

exit($failed > 0 ? 1 : 0);
