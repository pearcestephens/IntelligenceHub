#!/usr/bin/env php
<?php
/**
 * Simple Dispatcher Validation Test
 *
 * Tests the central dispatcher endpoint to verify it's working correctly
 * Uses actual production URL and real HTTP requests
 *
 * @package MCP\Tests
 * @version 1.0.0
 */

declare(strict_types=1);

// Configuration
$BASE_URL = 'https://gpt.ecigdis.co.nz/mcp/dispatcher.php';
$TIMEOUT = 10;

// Test counters
$tests_passed = 0;
$tests_failed = 0;
$tests_total = 0;

// Colors for output
$RED = "\033[31m";
$GREEN = "\033[32m";
$YELLOW = "\033[33m";
$BLUE = "\033[34m";
$PURPLE = "\033[35m";
$CYAN = "\033[36m";
$RESET = "\033[0m";

/**
 * Make HTTP request to dispatcher
 */
function makeRequest(string $url, array $params = []): array {
    global $TIMEOUT;

    $query = http_build_query($params);
    $full_url = $url . ($query ? '?' . $query : '');

    $ch = curl_init($full_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $TIMEOUT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => $error, 'http_code' => 0];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Invalid JSON response', 'http_code' => $http_code, 'raw' => $response];
    }

    return ['data' => $data, 'http_code' => $http_code];
}

/**
 * Run a test
 */
function test(string $name, callable $callback): void {
    global $tests_passed, $tests_failed, $tests_total, $GREEN, $RED, $YELLOW, $RESET;

    $tests_total++;

    try {
        $callback();
        $tests_passed++;
        echo "{$GREEN}✓{$RESET} {$name}\n";
    } catch (Exception $e) {
        $tests_failed++;
        echo "{$RED}✗{$RESET} {$name}\n";
        echo "  {$YELLOW}Error:{$RESET} " . $e->getMessage() . "\n";
    }
}

/**
 * Assert condition is true
 */
function assertTrue($condition, string $message = 'Assertion failed'): void {
    if (!$condition) {
        throw new Exception($message);
    }
}

/**
 * Assert equals
 */
function assertEquals($expected, $actual, string $message = 'Values not equal'): void {
    if ($expected !== $actual) {
        throw new Exception("{$message}: expected " . json_encode($expected) . ", got " . json_encode($actual));
    }
}

// ============================================================================
// TEST SUITE
// ============================================================================

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════╗\n";
echo "║  CENTRAL DISPATCHER VALIDATION TESTS                            ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "{$CYAN}Target:{$RESET} {$BASE_URL}\n\n";

// Test 1: Health check endpoint
echo "{$PURPLE}📊 Basic Endpoint Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("Health check returns 200", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'health']);
    assertTrue(!isset($result['error']), 'Request failed: ' . ($result['error'] ?? 'unknown'));
    assertEquals(200, $result['http_code'], 'HTTP status code');
});

test("Health check returns JSON", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'health']);
    assertTrue(isset($result['data']), 'No data in response');
    assertTrue(is_array($result['data']), 'Response is not an array');
});

test("Health check has success field", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'health']);
    assertTrue(isset($result['data']['success']), 'No success field');
});

test("Health check has timestamp", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'health']);
    assertTrue(isset($result['data']['timestamp']), 'No timestamp field');
});

echo "\n";

// Test 2: Error handling
echo "{$PURPLE}🛡️  Error Handling Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("Missing tool parameter returns error", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, []);
    assertTrue(isset($result['data']), 'No response data');
    assertEquals(false, $result['data']['success'] ?? true, 'Should return success=false');
});

test("Unknown tool returns error", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'nonexistent']);
    assertTrue(isset($result['data']), 'No response data');
    assertEquals(false, $result['data']['success'] ?? true, 'Should return success=false');
});

test("Error response has message", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'nonexistent']);
    assertTrue(isset($result['data']['message']), 'No error message');
});

echo "\n";

// Test 3: Stats endpoint
echo "{$PURPLE}📈 Stats Endpoint Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("Stats endpoint accessible", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'stats']);
    assertEquals(200, $result['http_code'], 'HTTP status code');
});

test("Stats returns success", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'stats']);
    assertTrue($result['data']['success'] ?? false, 'Stats should succeed');
});

test("Stats has data field", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'stats']);
    assertTrue(isset($result['data']['data']), 'No data field in stats');
});

echo "\n";

// Test 4: Search endpoint
echo "{$PURPLE}🔍 Search Endpoint Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("Search requires query parameter", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'search']);
    assertEquals(false, $result['data']['success'] ?? true, 'Should fail without query');
});

test("Search with query succeeds", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'search', 'query' => 'test']);
    assertTrue($result['data']['success'] ?? false, 'Search should succeed');
});

test("Search returns results", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'search', 'query' => 'function']);
    assertTrue(isset($result['data']['data']['results']), 'No results in search response');
});

echo "\n";

// Test 5: Analytics endpoint
echo "{$PURPLE}📊 Analytics Endpoint Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("Analytics endpoint accessible", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'analytics']);
    assertEquals(200, $result['http_code'], 'HTTP status code');
});

test("Analytics returns success", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'analytics']);
    assertTrue($result['data']['success'] ?? false, 'Analytics should succeed');
});

test("Analytics accepts timeframe", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'analytics', 'timeframe' => '24h']);
    assertTrue($result['data']['success'] ?? false, 'Analytics with timeframe should succeed');
});

echo "\n";

// Test 6: Response structure
echo "{$PURPLE}🏗️  Response Structure Tests{$RESET}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test("All responses have success field", function() use ($BASE_URL) {
    $tools = ['health', 'stats', 'analytics'];
    foreach ($tools as $tool) {
        $result = makeRequest($BASE_URL, ['tool' => $tool]);
        assertTrue(isset($result['data']['success']), "Tool '{$tool}' missing success field");
    }
});

test("All responses have timestamp", function() use ($BASE_URL) {
    $tools = ['health', 'stats', 'analytics'];
    foreach ($tools as $tool) {
        $result = makeRequest($BASE_URL, ['tool' => $tool]);
        assertTrue(isset($result['data']['timestamp']), "Tool '{$tool}' missing timestamp");
    }
});

test("Success responses have data field", function() use ($BASE_URL) {
    $result = makeRequest($BASE_URL, ['tool' => 'health']);
    if ($result['data']['success'] ?? false) {
        assertTrue(isset($result['data']['data']), "Successful response missing data field");
    }
});

echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                         TEST SUMMARY                               \n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$pass_rate = $tests_total > 0 ? ($tests_passed / $tests_total) * 100 : 0;

echo "Total Tests:    {$tests_total}\n";
echo "Passed:         {$GREEN}{$tests_passed} ✓{$RESET}\n";
echo "Failed:         " . ($tests_failed > 0 ? "{$RED}{$tests_failed} ✗{$RESET}" : "{$GREEN}0{$RESET}") . "\n";
echo "Pass Rate:      " . sprintf("%.1f%%", $pass_rate) . "\n\n";

if ($tests_failed === 0) {
    echo "{$GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$RESET}\n";
    echo "{$GREEN}🎉 ALL TESTS PASSED! DISPATCHER IS WORKING CORRECTLY{$RESET}\n";
    echo "{$GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$RESET}\n";
    exit(0);
} else {
    echo "{$RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$RESET}\n";
    echo "{$RED}⚠️  SOME TESTS FAILED - REVIEW ERRORS ABOVE{$RESET}\n";
    echo "{$RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━{$RESET}\n";
    exit(1);
}
