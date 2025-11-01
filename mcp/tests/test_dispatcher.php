#!/usr/bin/env php
<?php
/**
 * Unit Tests - Central Dispatcher
 *
 * Tests the unified dispatcher endpoint routing.
 *
 * @package MCP\Tests
 */

declare(strict_types=1);

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

/**
 * Make HTTP request to dispatcher
 */
function makeRequest(string $tool, array $params = []): array
{
    $baseUrl = 'https://gpt.ecigdis.co.nz/mcp/dispatcher.php';
    $params['tool'] = $tool;
    $url = $baseUrl . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'http_code' => $httpCode,
        'body' => $response,
        'data' => json_decode($response, true),
    ];
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  CENTRAL DISPATCHER UNIT TESTS                                               ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================================================
// TEST SUITE 1: Basic Routing
// ============================================================================

echo "🚦 TEST SUITE 1: Basic Routing\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Dispatcher returns valid JSON', function() {
    $response = makeRequest('health');
    return is_array($response['data']) ? true : "Response is not valid JSON";
});

test('Missing tool parameter returns 400', function() {
    $response = makeRequest('');
    return ($response['http_code'] === 400) ? true : "Expected 400, got {$response['http_code']}";
});

test('Unknown tool returns 404', function() {
    $response = makeRequest('nonexistent_tool_xyz');
    return ($response['http_code'] === 404) ? true : "Expected 404, got {$response['http_code']}";
});

test('Response includes timestamp', function() {
    $response = makeRequest('health');
    return isset($response['data']['timestamp']) ? true : "Missing timestamp field";
});

test('Response includes success field', function() {
    $response = makeRequest('health');
    return isset($response['data']['success']) ? true : "Missing success field";
});

echo "\n";

// ============================================================================
// TEST SUITE 2: Search Tool Routing
// ============================================================================

echo "🔍 TEST SUITE 2: Search Tool Routing\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Search without query returns 400', function() {
    $response = makeRequest('search');
    return ($response['http_code'] === 400) ? true : "Expected 400, got {$response['http_code']}";
});

test('Search with query returns 200', function() {
    $response = makeRequest('search', ['query' => 'test']);
    return ($response['http_code'] === 200) ? true : "Expected 200, got {$response['http_code']}";
});

test('Search returns results array', function() {
    $response = makeRequest('search', ['query' => 'intelligence']);
    return isset($response['data']['data']['results']) && is_array($response['data']['data']['results'])
        ? true
        : "Results array not found in response";
});

test('Search respects limit parameter', function() {
    $response = makeRequest('search', ['query' => 'intelligence', 'limit' => 3]);
    $resultCount = count($response['data']['data']['results'] ?? []);
    return ($resultCount <= 3) ? true : "Expected ≤3 results, got {$resultCount}";
});

test('Search includes duration metric', function() {
    $response = makeRequest('search', ['query' => 'test']);
    return isset($response['data']['data']['duration_ms'])
        ? true
        : "Duration metric not found";
});

echo "\n";

// ============================================================================
// TEST SUITE 3: Analytics Tool Routing
// ============================================================================

echo "📊 TEST SUITE 3: Analytics Tool Routing\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Analytics with default timeframe returns 200', function() {
    $response = makeRequest('analytics');
    return ($response['http_code'] === 200) ? true : "Expected 200, got {$response['http_code']}";
});

test('Analytics with custom timeframe works', function() {
    $response = makeRequest('analytics', ['timeframe' => '7d']);
    return ($response['http_code'] === 200 && isset($response['data']['data']['timeframe']))
        ? true
        : "Custom timeframe not working";
});

test('Analytics with invalid timeframe returns 400', function() {
    $response = makeRequest('analytics', ['timeframe' => 'invalid']);
    return ($response['http_code'] === 400) ? true : "Expected 400, got {$response['http_code']}";
});

test('Analytics returns required metrics', function() {
    $response = makeRequest('analytics', ['timeframe' => '24h']);
    $data = $response['data']['data'] ?? [];
    $requiredKeys = ['popular_queries', 'performance', 'cache_stats', 'search_patterns'];

    foreach ($requiredKeys as $key) {
        if (!isset($data[$key])) {
            return "Missing required key: {$key}";
        }
    }
    return true;
});

echo "\n";

// ============================================================================
// TEST SUITE 4: Health Check Routing
// ============================================================================

echo "🏥 TEST SUITE 4: Health Check Routing\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Health check returns 200 when healthy', function() {
    $response = makeRequest('health');
    $isHealthy = ($response['data']['data']['status'] ?? '') === 'healthy';
    return ($response['http_code'] === 200 && $isHealthy)
        ? true
        : "Expected 200 with healthy status";
});

test('Health check includes database status', function() {
    $response = makeRequest('health');
    return isset($response['data']['data']['database'])
        ? true
        : "Database status not found";
});

test('Health check includes cache status', function() {
    $response = makeRequest('health');
    return isset($response['data']['data']['cache'])
        ? true
        : "Cache status not found";
});

echo "\n";

// ============================================================================
// TEST SUITE 5: System Stats Routing
// ============================================================================

echo "📉 TEST SUITE 5: System Stats Routing\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Stats endpoint returns 200', function() {
    $response = makeRequest('stats');
    return ($response['http_code'] === 200) ? true : "Expected 200, got {$response['http_code']}";
});

test('Stats include indexed files count', function() {
    $response = makeRequest('stats');
    return isset($response['data']['data']['total_indexed_files'])
        ? true
        : "Indexed files count not found";
});

test('Stats include search engine info', function() {
    $response = makeRequest('stats');
    return isset($response['data']['data']['search_engine'])
        ? true
        : "Search engine info not found";
});

echo "\n";

// ============================================================================
// TEST SUITE 6: Fuzzy Search Testing Endpoint
// ============================================================================

echo "🔊 TEST SUITE 6: Fuzzy Search Testing Endpoint\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Fuzzy endpoint without query returns 400', function() {
    $response = makeRequest('fuzzy');
    return ($response['http_code'] === 400) ? true : "Expected 400, got {$response['http_code']}";
});

test('Fuzzy endpoint corrects typos', function() {
    $response = makeRequest('fuzzy', ['query' => 'fucntion']);
    $corrected = $response['data']['data']['corrected_query'] ?? '';
    return ($corrected === 'function') ? true : "Expected 'function', got '{$corrected}'";
});

test('Fuzzy endpoint provides suggestions', function() {
    $response = makeRequest('fuzzy', ['query' => 'functin']);
    return isset($response['data']['data']['suggestions']) && is_array($response['data']['data']['suggestions'])
        ? true
        : "Suggestions not found or not array";
});

test('Fuzzy endpoint shows if correction was applied', function() {
    $response = makeRequest('fuzzy', ['query' => 'fucntion']);
    return isset($response['data']['data']['correction_applied']) && $response['data']['data']['correction_applied'] === true
        ? true
        : "correction_applied flag not set correctly";
});

echo "\n";

// ============================================================================
// TEST SUITE 7: Error Handling
// ============================================================================

echo "⚠️  TEST SUITE 7: Error Handling\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Error responses include message', function() {
    $response = makeRequest('nonexistent');
    return isset($response['data']['message']) && !empty($response['data']['message'])
        ? true
        : "Error message not included";
});

test('Error responses have success=false', function() {
    $response = makeRequest('search'); // Missing query
    return ($response['data']['success'] === false) ? true : "success field should be false";
});

test('Dispatcher handles special characters in query', function() {
    $response = makeRequest('search', ['query' => 'test<>"\'"']);
    return ($response['http_code'] === 200 || $response['http_code'] === 400)
        ? true
        : "Unexpected response to special characters";
});

echo "\n";

// ============================================================================
// TEST SUITE 8: Performance
// ============================================================================

echo "⚡ TEST SUITE 8: Performance\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Health check responds within 1 second', function() {
    $start = microtime(true);
    makeRequest('health');
    $duration = (microtime(true) - $start) * 1000;
    return ($duration < 1000) ? true : "Took {$duration}ms (expected <1000ms)";
});

test('Stats endpoint responds within 2 seconds', function() {
    $start = microtime(true);
    makeRequest('stats');
    $duration = (microtime(true) - $start) * 1000;
    return ($duration < 2000) ? true : "Took {$duration}ms (expected <2000ms)";
});

test('Search with cache responds quickly', function() {
    // First request (uncached)
    makeRequest('search', ['query' => 'test_cache_speed']);

    // Second request (should be cached)
    $start = microtime(true);
    makeRequest('search', ['query' => 'test_cache_speed']);
    $duration = (microtime(true) - $start) * 1000;

    return ($duration < 500) ? true : "Cached search took {$duration}ms (expected <500ms)";
});

echo "\n";

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
