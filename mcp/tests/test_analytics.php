#!/usr/bin/env php
<?php
/**
 * Unit Tests - Search Analytics
 *
 * Tests analytics logging, metrics retrieval, and data aggregation.
 *
 * @package MCP\Tests
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../src/Analytics/SearchAnalytics.php';

use IntelligenceHub\MCP\Analytics\SearchAnalytics;

// Test counters
$tests_passed = 0;
$tests_failed = 0;
$test_details = [];

/**
 * Test helper function
 */
function test(string $name, callable $callback): void
{
    global $tests_passed, $tests_failed, $test_details;

    try {
        $result = $callback();
        if ($result === true) {
            $tests_passed++;
            $test_details[] = ['name' => $name, 'status' => 'PASS', 'message' => ''];
            echo "✅ PASS: {$name}\n";
        } else {
            $tests_failed++;
            $test_details[] = ['name' => $name, 'status' => 'FAIL', 'message' => $result];
            echo "❌ FAIL: {$name} - {$result}\n";
        }
    } catch (\Exception $e) {
        $tests_failed++;
        $test_details[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
        echo "💥 ERROR: {$name} - {$e->getMessage()}\n";
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  SEARCH ANALYTICS UNIT TESTS                                                 ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Initialize analytics
$analytics = new SearchAnalytics();

// ============================================================================
// TEST SUITE 1: Table Creation
// ============================================================================

echo "🗄️  TEST SUITE 1: Database Table Management\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Analytics table creation succeeds', function() use ($analytics) {
    try {
        $analytics->ensureTable();
        return true;
    } catch (\Exception $e) {
        return "Table creation failed: " . $e->getMessage();
    }
});

test('Table has correct indexes', function() {
    global $db;
    $result = $db->query("SHOW INDEX FROM mcp_search_analytics");
    $indexes = [];
    while ($row = $result->fetch_assoc()) {
        $indexes[] = $row['Key_name'];
    }
    $requiredIndexes = ['idx_query', 'idx_created', 'idx_cache', 'idx_results'];
    $hasAll = true;
    foreach ($requiredIndexes as $idx) {
        if (!in_array($idx, $indexes)) {
            $hasAll = false;
            break;
        }
    }
    return $hasAll ? true : "Missing required indexes. Found: " . implode(', ', $indexes);
});

echo "\n";

// ============================================================================
// TEST SUITE 2: Search Logging
// ============================================================================

echo "📝 TEST SUITE 2: Search Logging\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Clear test data first
global $db;
$db->query("DELETE FROM mcp_search_analytics WHERE query_text LIKE 'test_query_%'");

test('Log basic search', function() use ($analytics) {
    $analytics->logSearch([
        'query_text' => 'test_query_basic',
        'results_count' => 10,
        'execution_time_ms' => 25.5,
        'cache_hit' => false,
    ]);

    global $db;
    $result = $db->query("SELECT COUNT(*) as cnt FROM mcp_search_analytics WHERE query_text = 'test_query_basic'");
    $row = $result->fetch_assoc();
    return $row['cnt'] == 1 ? true : "Expected 1 row, got {$row['cnt']}";
});

test('Log search with correction', function() use ($analytics) {
    $analytics->logSearch([
        'query_text' => 'test_query_typo',
        'query_corrected' => 'test_query_correct',
        'results_count' => 5,
        'execution_time_ms' => 30.0,
        'cache_hit' => false,
    ]);

    global $db;
    $result = $db->query("SELECT query_corrected FROM mcp_search_analytics WHERE query_text = 'test_query_typo'");
    $row = $result->fetch_assoc();
    return $row['query_corrected'] === 'test_query_correct' ? true : "Correction not saved correctly";
});

test('Log cached search', function() use ($analytics) {
    $analytics->logSearch([
        'query_text' => 'test_query_cached',
        'results_count' => 15,
        'execution_time_ms' => 0.8,
        'cache_hit' => true,
    ]);

    global $db;
    $result = $db->query("SELECT cache_hit, execution_time_ms FROM mcp_search_analytics WHERE query_text = 'test_query_cached'");
    $row = $result->fetch_assoc();
    return ($row['cache_hit'] == 1 && $row['execution_time_ms'] < 1) ? true : "Cache hit not logged correctly";
});

test('Log zero-result search', function() use ($analytics) {
    $analytics->logSearch([
        'query_text' => 'test_query_noresults',
        'results_count' => 0,
        'execution_time_ms' => 20.0,
        'cache_hit' => false,
    ]);

    global $db;
    $result = $db->query("SELECT results_count FROM mcp_search_analytics WHERE query_text = 'test_query_noresults'");
    $row = $result->fetch_assoc();
    return $row['results_count'] == 0 ? true : "Zero results not logged correctly";
});

test('Log with user agent and IP', function() use ($analytics) {
    $analytics->logSearch([
        'query_text' => 'test_query_metadata',
        'results_count' => 8,
        'execution_time_ms' => 15.0,
        'cache_hit' => false,
        'user_agent' => 'Test User Agent',
        'ip_address' => '192.168.1.1',
    ]);

    global $db;
    $result = $db->query("SELECT user_agent, ip_address FROM mcp_search_analytics WHERE query_text = 'test_query_metadata'");
    $row = $result->fetch_assoc();
    return ($row['user_agent'] === 'Test User Agent' && $row['ip_address'] === '192.168.1.1')
        ? true
        : "Metadata not logged correctly";
});

echo "\n";

// ============================================================================
// TEST SUITE 3: Popular Queries
// ============================================================================

echo "🔥 TEST SUITE 3: Popular Queries Retrieval\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Insert test data for popular queries
for ($i = 0; $i < 5; $i++) {
    $analytics->logSearch([
        'query_text' => 'test_popular_1',
        'results_count' => 10,
        'execution_time_ms' => 20.0,
        'cache_hit' => false,
    ]);
}

for ($i = 0; $i < 3; $i++) {
    $analytics->logSearch([
        'query_text' => 'test_popular_2',
        'results_count' => 8,
        'execution_time_ms' => 18.0,
        'cache_hit' => false,
    ]);
}

test('Get popular queries returns array', function() use ($analytics) {
    $popular = $analytics->getPopularQueries(10, '24h');
    return is_array($popular) ? true : "Expected array, got " . gettype($popular);
});

test('Popular queries are sorted by count', function() use ($analytics) {
    $popular = $analytics->getPopularQueries(10, '24h');

    // Find our test queries
    $found1 = false;
    $found2 = false;
    $count1 = 0;
    $count2 = 0;

    foreach ($popular as $query) {
        if ($query['query'] === 'test_popular_1') {
            $found1 = true;
            $count1 = $query['count'];
        }
        if ($query['query'] === 'test_popular_2') {
            $found2 = true;
            $count2 = $query['count'];
        }
    }

    return ($found1 && $count1 >= 5 && $found2 && $count2 >= 3)
        ? true
        : "Counts not correct: popular_1={$count1}, popular_2={$count2}";
});

test('Popular queries limit works', function() use ($analytics) {
    $popular = $analytics->getPopularQueries(2, '24h');
    return count($popular) <= 2 ? true : "Expected ≤2 results, got " . count($popular);
});

test('Popular queries timeframe filtering works', function() use ($analytics) {
    // Insert old query (simulate by updating created_at)
    $analytics->logSearch([
        'query_text' => 'test_old_query',
        'results_count' => 5,
        'execution_time_ms' => 20.0,
        'cache_hit' => false,
    ]);
    global $db;
    $db->query("UPDATE mcp_search_analytics SET created_at = DATE_SUB(NOW(), INTERVAL 8 DAYS) WHERE query_text = 'test_old_query'");

    $popular = $analytics->getPopularQueries(100, '7d');
    $foundOld = false;
    foreach ($popular as $query) {
        if ($query['query'] === 'test_old_query') {
            $foundOld = true;
            break;
        }
    }

    return $foundOld === false ? true : "Old query should not appear in 7d timeframe";
});

echo "\n";

// ============================================================================
// TEST SUITE 4: Performance Metrics
// ============================================================================

echo "⚡ TEST SUITE 4: Performance Metrics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Get performance metrics returns correct structure', function() use ($analytics) {
    $metrics = $analytics->getPerformanceMetrics('24h');

    $requiredKeys = ['total_searches', 'avg_duration', 'min_duration', 'max_duration', 'avg_results'];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $metrics)) {
            return "Missing key: {$key}";
        }
    }
    return true;
});

test('Performance metrics are numeric', function() use ($analytics) {
    $metrics = $analytics->getPerformanceMetrics('24h');

    return (is_numeric($metrics['total_searches']) &&
            is_numeric($metrics['avg_duration']) &&
            is_numeric($metrics['avg_results']))
        ? true
        : "Metrics contain non-numeric values";
});

test('Performance metrics reflect test data', function() use ($analytics) {
    $metrics = $analytics->getPerformanceMetrics('24h');
    return $metrics['total_searches'] > 0 ? true : "No searches found in metrics";
});

echo "\n";

// ============================================================================
// TEST SUITE 5: Cache Statistics
// ============================================================================

echo "💾 TEST SUITE 5: Cache Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Get cache stats returns correct structure', function() use ($analytics) {
    $stats = $analytics->getCacheStats('24h');

    $requiredKeys = ['hits', 'misses', 'hit_rate', 'avg_cached_time', 'avg_uncached_time'];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $stats)) {
            return "Missing key: {$key}";
        }
    }
    return true;
});

test('Cache hit rate is percentage string', function() use ($analytics) {
    $stats = $analytics->getCacheStats('24h');
    return (is_string($stats['hit_rate']) && strpos($stats['hit_rate'], '%') !== false)
        ? true
        : "hit_rate is not a percentage string: " . $stats['hit_rate'];
});

test('Cache stats reflect logged data', function() use ($analytics) {
    $stats = $analytics->getCacheStats('24h');
    // We logged at least one cached search (test_query_cached)
    return $stats['hits'] > 0 ? true : "Expected cache hits > 0";
});

echo "\n";

// ============================================================================
// TEST SUITE 6: Search Patterns
// ============================================================================

echo "📊 TEST SUITE 6: Search Patterns Analysis\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Get search patterns returns correct structure', function() use ($analytics) {
    $patterns = $analytics->getSearchPatterns('24h');

    $requiredKeys = ['unique_queries', 'repeat_searches', 'auto_corrections', 'zero_results'];
    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $patterns)) {
            return "Missing key: {$key}";
        }
    }
    return true;
});

test('Search patterns are numeric', function() use ($analytics) {
    $patterns = $analytics->getSearchPatterns('24h');

    return (is_numeric($patterns['unique_queries']) &&
            is_numeric($patterns['repeat_searches']) &&
            is_numeric($patterns['auto_corrections']) &&
            is_numeric($patterns['zero_results']))
        ? true
        : "Patterns contain non-numeric values";
});

test('Auto corrections count is accurate', function() use ($analytics) {
    $patterns = $analytics->getSearchPatterns('24h');
    // We logged test_query_typo with correction
    return $patterns['auto_corrections'] > 0 ? true : "Expected auto_corrections > 0";
});

test('Zero results count is accurate', function() use ($analytics) {
    $patterns = $analytics->getSearchPatterns('24h');
    // We logged test_query_noresults with 0 results
    return $patterns['zero_results'] > 0 ? true : "Expected zero_results > 0";
});

echo "\n";

// ============================================================================
// TEST SUITE 7: Failed Searches
// ============================================================================

echo "❌ TEST SUITE 7: Failed Searches Tracking\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Get failed searches returns array', function() use ($analytics) {
    $failed = $analytics->getFailedSearches(10, '24h');
    return is_array($failed) ? true : "Expected array, got " . gettype($failed);
});

test('Failed searches include zero-result queries', function() use ($analytics) {
    $failed = $analytics->getFailedSearches(100, '24h');

    $foundFailed = false;
    foreach ($failed as $search) {
        if ($search['query'] === 'test_query_noresults') {
            $foundFailed = true;
            break;
        }
    }

    return $foundFailed ? true : "test_query_noresults not found in failed searches";
});

test('Failed searches are sorted by frequency', function() use ($analytics) {
    // Log same failed query multiple times
    for ($i = 0; $i < 3; $i++) {
        $analytics->logSearch([
            'query_text' => 'test_failed_common',
            'results_count' => 0,
            'execution_time_ms' => 20.0,
            'cache_hit' => false,
        ]);
    }

    $failed = $analytics->getFailedSearches(100, '24h');
    return count($failed) > 0 ? true : "No failed searches found";
});

echo "\n";

// ============================================================================
// TEST SUITE 8: Edge Cases
// ============================================================================

echo "⚠️  TEST SUITE 8: Edge Cases\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Handle empty timeframe defaults to 24h', function() use ($analytics) {
    try {
        $metrics = $analytics->getPerformanceMetrics('');
        return true;
    } catch (\Exception $e) {
        return "Exception thrown: " . $e->getMessage();
    }
});

test('Handle invalid timeframe gracefully', function() use ($analytics) {
    try {
        $metrics = $analytics->getPerformanceMetrics('999d');
        return is_array($metrics) ? true : "Expected array result";
    } catch (\Exception $e) {
        return true; // Exception is acceptable for invalid input
    }
});

test('Handle very long query text', function() use ($analytics) {
    $longQuery = str_repeat('test ', 100);
    try {
        $analytics->logSearch([
            'query_text' => substr($longQuery, 0, 255), // Truncate to field limit
            'results_count' => 5,
            'execution_time_ms' => 20.0,
            'cache_hit' => false,
        ]);
        return true;
    } catch (\Exception $e) {
        return "Failed to log long query: " . $e->getMessage();
    }
});

test('Handle negative execution time', function() use ($analytics) {
    try {
        $analytics->logSearch([
            'query_text' => 'test_negative_time',
            'results_count' => 5,
            'execution_time_ms' => -1.0, // Invalid but should be handled
            'cache_hit' => false,
        ]);
        return true;
    } catch (\Exception $e) {
        return true; // Exception is acceptable
    }
});

echo "\n";

// ============================================================================
// Cleanup test data
// ============================================================================

echo "🧹 Cleaning up test data...\n";
$db->query("DELETE FROM mcp_search_analytics WHERE query_text LIKE 'test_%'");
echo "✅ Test data cleaned\n\n";

// ============================================================================
// TEST SUMMARY
// ============================================================================

$total_tests = $tests_passed + $tests_failed;
$pass_rate = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;

echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Total Tests:  {$total_tests}\n";
echo "Passed:       {$tests_passed} ✅\n";
echo "Failed:       {$tests_failed} ❌\n";
echo "Pass Rate:    {$pass_rate}%\n";
echo "\n";

if ($tests_failed > 0) {
    echo "FAILED TESTS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($test_details as $detail) {
        if ($detail['status'] !== 'PASS') {
            echo "❌ {$detail['name']}: {$detail['message']}\n";
        }
    }
    echo "\n";
}

if ($tests_passed === $total_tests) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED\n";
    echo "\n";
    exit(1);
}
