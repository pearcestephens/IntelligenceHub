<?php
/**
 * Test MCP V3 Intelligence Tools
 *
 * Tests all intelligence tools to verify they return correct file data
 */

declare(strict_types=1);

$mcpUrl = 'https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc';
$apiKey = 'bFUdRjh4Jx';

function callMCP(string $url, string $apiKey, string $toolName, array $arguments = []): array
{
    $payload = [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/call',
        'params' => [
            'name' => $toolName,
            'arguments' => $arguments
        ]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "HTTP $httpCode", 'response' => $response];
    }

    return json_decode($response, true) ?? ['error' => 'Invalid JSON'];
}

echo "\n=== MCP V3 INTELLIGENCE TOOLS TEST ===\n\n";

// Test 1: Health Check
echo "1. Testing health_check...\n";
$result = callMCP($mcpUrl, $apiKey, 'health_check');
if (isset($result['result'])) {
    echo "   ✓ Status: " . ($result['result']['status'] ?? 'unknown') . "\n";
    echo "   ✓ Total files: " . number_format($result['result']['total_files'] ?? 0) . "\n";
    echo "   ✓ Indexed: " . number_format($result['result']['indexed_files'] ?? 0) . "\n";
} else {
    echo "   ✗ Error: " . json_encode($result) . "\n";
}

// Test 2: Semantic Search
echo "\n2. Testing semantic_search (query: 'inventory transfer')...\n";
$result = callMCP($mcpUrl, $apiKey, 'semantic_search', [
    'query' => 'inventory transfer',
    'limit' => 5
]);
if (isset($result['result']['files'])) {
    echo "   ✓ Found " . count($result['result']['files']) . " files\n";
    foreach ($result['result']['files'] as $i => $file) {
        echo "   " . ($i + 1) . ". {$file['file_name']} (score: {$file['relevance_score']})\n";
        echo "      Path: {$file['file_path']}\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 3: Find Code
echo "\n3. Testing find_code (pattern: 'function')...\n";
$result = callMCP($mcpUrl, $apiKey, 'find_code', [
    'pattern' => 'function',
    'limit' => 5
]);
if (isset($result['result']['files'])) {
    echo "   ✓ Found " . count($result['result']['files']) . " files\n";
    foreach ($result['result']['files'] as $i => $file) {
        echo "   " . ($i + 1) . ". {$file['file_name']}\n";
        echo "      Path: {$file['file_path']}\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 4: List Categories
echo "\n4. Testing list_categories...\n";
$result = callMCP($mcpUrl, $apiKey, 'list_categories');
if (isset($result['result']['categories'])) {
    echo "   ✓ Found " . count($result['result']['categories']) . " categories\n";
    foreach (array_slice($result['result']['categories'], 0, 5) as $cat) {
        echo "   - {$cat['category_name']} (files: {$cat['file_count']}, priority: {$cat['priority']})\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 5: Get Stats
echo "\n5. Testing get_stats...\n";
$result = callMCP($mcpUrl, $apiKey, 'get_stats', ['breakdown_by' => 'unit']);
if (isset($result['result'])) {
    echo "   ✓ Total files: " . number_format($result['result']['total_files'] ?? 0) . "\n";
    echo "   ✓ Total size: " . round(($result['result']['total_size_bytes'] ?? 0) / 1024 / 1024, 2) . " MB\n";
    if (isset($result['result']['by_unit'])) {
        echo "   Units:\n";
        foreach ($result['result']['by_unit'] as $unit) {
            echo "   - Unit {$unit['business_unit_id']}: " . number_format($unit['file_count']) . " files\n";
        }
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 6: Search by Category
echo "\n6. Testing search_by_category (category: 'Inventory Management')...\n";
$result = callMCP($mcpUrl, $apiKey, 'search_by_category', [
    'query' => 'transfer',
    'category_name' => 'Inventory Management',
    'limit' => 5
]);
if (isset($result['result']['files'])) {
    echo "   ✓ Found " . count($result['result']['files']) . " files\n";
    foreach ($result['result']['files'] as $i => $file) {
        echo "   " . ($i + 1) . ". {$file['file_name']}\n";
        echo "      Category: {$file['category_name']}\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 7: Top Keywords
echo "\n7. Testing top_keywords...\n";
$result = callMCP($mcpUrl, $apiKey, 'top_keywords', ['limit' => 10]);
if (isset($result['result']['keywords'])) {
    echo "   ✓ Found " . count($result['result']['keywords']) . " keywords\n";
    foreach (array_slice($result['result']['keywords'], 0, 10) as $kw) {
        echo "   - {$kw['keyword']} (count: {$kw['count']})\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

// Test 8: Analyze File (pick first file from semantic search)
echo "\n8. Testing analyze_file...\n";
$searchResult = callMCP($mcpUrl, $apiKey, 'semantic_search', ['query' => 'transfer', 'limit' => 1]);
if (isset($searchResult['result']['files'][0]['file_path'])) {
    $filePath = $searchResult['result']['files'][0]['file_path'];
    echo "   Analyzing: $filePath\n";
    $result = callMCP($mcpUrl, $apiKey, 'analyze_file', ['file_path' => $filePath]);
    if (isset($result['result'])) {
        echo "   ✓ File size: " . ($result['result']['file_size'] ?? 0) . " bytes\n";
        echo "   ✓ Type: " . ($result['result']['file_type'] ?? 'unknown') . "\n";
        echo "   ✓ Keywords: " . count($result['result']['keywords'] ?? []) . "\n";
    } else {
        echo "   ✗ Error: " . json_encode($result) . "\n";
    }
} else {
    echo "   ✗ No file found to analyze\n";
}

// Test 9: Get Analytics
echo "\n9. Testing get_analytics...\n";
$result = callMCP($mcpUrl, $apiKey, 'get_analytics', ['action' => 'overview', 'timeframe' => '24h']);
if (isset($result['result'])) {
    echo "   ✓ Response: " . json_encode($result['result'], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   ✗ Error: " . json_encode($result) . "\n";
}

// Test 10: Explore by Tags
echo "\n10. Testing explore_by_tags (tags: ['php', 'function'])...\n";
$result = callMCP($mcpUrl, $apiKey, 'explore_by_tags', [
    'semantic_tags' => ['php', 'function'],
    'match_all' => false,
    'limit' => 5
]);
if (isset($result['result']['files'])) {
    echo "   ✓ Found " . count($result['result']['files']) . " files\n";
    foreach ($result['result']['files'] as $i => $file) {
        echo "   " . ($i + 1) . ". {$file['file_name']}\n";
        echo "      Tags: " . implode(', ', $file['tags'] ?? []) . "\n";
    }
} else {
    echo "   ✗ Error or no results: " . json_encode($result) . "\n";
}

echo "\n=== TEST COMPLETE ===\n\n";
