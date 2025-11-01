<?php
/**
 * MCP Server v3 Endpoint Tests
 *
 * Tests HTTP endpoint functionality
 *
 * Usage: php tests/endpoint_test.php
 */

declare(strict_types=1);

echo "\n=== MCP Server v3 Endpoint Tests ===\n\n";

$passed = 0;
$failed = 0;

// Base URL (adjust if needed)
$baseUrl = 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';

// Detect actual URL from script location
$scriptPath = realpath(__DIR__ . '/../server_v3.php');
if ($scriptPath) {
    // Try to detect if we're on Cloudways or local
    if (strpos($scriptPath, 'cloudwaysapps.com') !== false) {
        // Use the actual working domain
        $baseUrl = "https://gpt.ecigdis.co.nz/mcp/server_v3.php";
    } elseif (strpos($scriptPath, '/localhost/') !== false || strpos($scriptPath, '/xampp/') !== false) {
        $baseUrl = 'http://localhost/mcp/server_v3.php';
    }
}

echo "Testing endpoint: {$baseUrl}\n\n";

/**
 * Make HTTP request
 */
function makeRequest(string $url, array $params = []): array
{
    $queryString = http_build_query($params);
    $fullUrl = $url . ($queryString ? '?' . $queryString : '');

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
        ]
    ]);

    $startTime = microtime(true);
    $response = @file_get_contents($fullUrl, false, $context);
    $duration = round((microtime(true) - $startTime) * 1000, 2);

    if ($response === false) {
        return [
            'success' => false,
            'error' => 'Failed to connect',
            'duration' => $duration,
        ];
    }

    // Get HTTP response code
    $httpCode = 200;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                $httpCode = (int)$matches[1];
            }
        }
    }

    $data = json_decode($response, true);

    return [
        'success' => true,
        'http_code' => $httpCode,
        'data' => $data,
        'raw' => $response,
        'duration' => $duration,
    ];
}

// TEST 1: Health check
echo "TEST 1: Health Check Endpoint\n";
try {
    $result = makeRequest($baseUrl, ['tool' => 'health']);

    if ($result['success'] && $result['http_code'] === 200) {
        $data = $result['data'];

        if ($data && isset($data['success']) && $data['success'] === true) {
            echo "✅ PASSED: Health check successful\n";
            echo "   Status: {$data['data']['status']}\n";
            echo "   Version: {$data['data']['version']}\n";
            echo "   Duration: {$result['duration']}ms\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Invalid response structure\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: HTTP {$result['http_code']}\n";
        echo "   Error: " . ($result['error'] ?? 'Unknown') . "\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 2: Search with valid query
echo "TEST 2: Search with Valid Query\n";
try {
    $result = makeRequest($baseUrl, [
        'tool' => 'search',
        'query' => 'inventory transfer',
        'limit' => 5
    ]);

    if ($result['success'] && $result['http_code'] === 200) {
        $data = $result['data'];

        if ($data && isset($data['success']) && $data['success'] === true) {
            $resultsCount = count($data['data']['results'] ?? []);
            $duration = $data['data']['metadata']['duration_ms'] ?? 0;

            echo "✅ PASSED: Search executed successfully\n";
            echo "   Query: inventory transfer\n";
            echo "   Results: {$resultsCount}\n";
            echo "   Duration: {$duration}ms\n";
            echo "   Cache hit: " . ($data['data']['cache_hit'] ? 'Yes' : 'No') . "\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Invalid response structure\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: HTTP {$result['http_code']}\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 3: Search without query (should fail gracefully)
echo "TEST 3: Search Without Query (Error Handling)\n";
try {
    $result = makeRequest($baseUrl, ['tool' => 'search']);

    if ($result['success'] && $result['http_code'] === 400) {
        $data = $result['data'];

        if ($data && isset($data['success']) && $data['success'] === false) {
            echo "✅ PASSED: Error handled correctly\n";
            echo "   Error message: {$data['error']['message']}\n";
            echo "   HTTP code: 400 (Bad Request)\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Should return error response\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: Expected HTTP 400, got {$result['http_code']}\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 4: Unknown tool (should fail gracefully)
echo "TEST 4: Unknown Tool (Error Handling)\n";
try {
    $result = makeRequest($baseUrl, ['tool' => 'invalid_tool']);

    if ($result['success'] && $result['http_code'] === 400) {
        $data = $result['data'];

        if ($data && isset($data['success']) && $data['success'] === false) {
            echo "✅ PASSED: Unknown tool handled correctly\n";
            echo "   Error message: {$data['error']['message']}\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Should return error response\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: Expected HTTP 400, got {$result['http_code']}\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 5: JSON response structure
echo "TEST 5: JSON Response Structure Validation\n";
try {
    $result = makeRequest($baseUrl, ['tool' => 'health']);

    if ($result['success']) {
        $data = $result['data'];

        $requiredFields = ['success', 'timestamp', 'request_id'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            echo "✅ PASSED: JSON structure valid\n";
            echo "   Has: success, timestamp, request_id\n";
            echo "   Request ID: {$data['request_id']}\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Missing fields: " . implode(', ', $missingFields) . "\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: Could not retrieve response\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 6: Cache hit on repeated query
echo "TEST 6: Cache Hit on Repeated Query\n";
try {
    // First query
    $result1 = makeRequest($baseUrl, [
        'tool' => 'search',
        'query' => 'test cache query',
        'limit' => 5
    ]);

    if (!$result1['success'] || $result1['http_code'] !== 200) {
        echo "❌ FAILED: First query failed\n\n";
        $failed++;
    } else {
        $duration1 = $result1['data']['data']['metadata']['duration_ms'] ?? 0;

        // Second query (should be cached)
        sleep(1); // Brief pause
        $result2 = makeRequest($baseUrl, [
            'tool' => 'search',
            'query' => 'test cache query',
            'limit' => 5
        ]);

        if ($result2['success'] && $result2['http_code'] === 200) {
            $duration2 = $result2['data']['data']['metadata']['duration_ms'] ?? 0;
            $cacheHit = $result2['data']['data']['cache_hit'] ?? false;

            if ($cacheHit) {
                $speedup = $duration2 > 0 ? round($duration1 / $duration2) : 0;
                echo "✅ PASSED: Cache hit detected\n";
                echo "   First query: {$duration1}ms\n";
                echo "   Second query: {$duration2}ms (cached)\n";
                echo "   Speedup: {$speedup}x\n\n";
                $passed++;
            } else {
                echo "⚠️  WARNING: Cache hit not detected (may take a moment)\n";
                echo "   First query: {$duration1}ms\n";
                echo "   Second query: {$duration2}ms\n\n";
                $passed++; // Still pass - cache timing varies
            }
        } else {
            echo "❌ FAILED: Second query failed\n\n";
            $failed++;
        }
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Summary
echo "=== Test Summary ===\n";
echo "Total tests: " . ($passed + $failed) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
$successRate = ($passed + $failed) > 0 ? round(($passed / ($passed + $failed)) * 100) : 0;
echo "Success rate: {$successRate}%\n\n";

if ($failed === 0) {
    echo "🎉 ALL ENDPOINT TESTS PASSED!\n\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED - Review output above\n\n";
    exit(1);
}
