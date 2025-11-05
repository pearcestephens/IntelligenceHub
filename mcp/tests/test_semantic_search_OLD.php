#!/usr/bin/env php
<?php
/**
 * Test Semantic Search Performance
 * Tests the new optimized semantic search directly
 */

require_once __DIR__ . '/mcp/semantic_search_engine.php';

// Database connection
$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';

// Get password from .env
$env = parse_ini_file(__DIR__ . '/mcp/.env');
$password = $env['DB_PASS'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize search engine with optimized config
    $config = [
        'cache_ttl' => 3600,
        'min_relevance' => 0.1,
        'enable_synonyms' => true,
        'enable_redis' => true,
        'max_results' => 50,
    ];

    $searchEngine = new SemanticSearchEngine($pdo, $config);

    // Test queries
    $testQueries = [
        'transfer validation',
        'inventory management',
        'customer refund process',
        'consignment packing workflow',
        'stock counting audit',
    ];

    echo "🔍 SEMANTIC SEARCH PERFORMANCE TEST\n";
    echo str_repeat("=", 80) . "\n\n";

    foreach ($testQueries as $query) {
        echo "📝 Query: \"$query\"\n";
        echo str_repeat("-", 80) . "\n";

        $startTime = microtime(true);
        $results = $searchEngine->search($query, [], 5);
        $searchTime = (microtime(true) - $startTime) * 1000;

        echo "⏱️  Search Time: " . round($searchTime, 2) . "ms\n";
        echo "📊 Results Found: " . count($results['results']) . "\n";
        echo "💾 Cache Hit: " . ($results['cache_hit'] ? 'YES' : 'NO') . "\n";

        if (!empty($results['results'])) {
            echo "\n🎯 Top 3 Results:\n";
            foreach (array_slice($results['results'], 0, 3) as $i => $result) {
                echo sprintf(
                    "  %d. %s (score: %.4f)\n     📁 %s\n     🏷️  %s\n",
                    $i + 1,
                    $result['content_name'],
                    $result['relevance_score'],
                    $result['content_path'],
                    substr($result['preview'], 0, 100) . '...'
                );
            }
        }

        if (isset($results['stats'])) {
            echo "\n📈 Stats:\n";
            echo "   Cache Hits: " . ($results['stats']['cache_hits'] ?? 0) . "\n";
            echo "   Cache Misses: " . ($results['stats']['cache_misses'] ?? 0) . "\n";
            echo "   Results Count: " . ($results['stats']['results_count'] ?? 0) . "\n";
        }

        echo "\n" . str_repeat("=", 80) . "\n\n";

        // Wait a bit between queries
        usleep(100000); // 0.1 second
    }

    // Test with filters
    echo "🎯 FILTERED SEARCH TEST\n";
    echo str_repeat("=", 80) . "\n\n";

    $startTime = microtime(true);
    $results = $searchEngine->search(
        'stock transfer',
        ['file_type' => 'php', 'unit_id' => 2],
        10
    );
    $searchTime = (microtime(true) - $startTime) * 1000;

    echo "📝 Query: \"stock transfer\" (PHP files only, Unit 2)\n";
    echo "⏱️  Search Time: " . round($searchTime, 2) . "ms\n";
    echo "📊 Results Found: " . count($results['results']) . "\n\n";

    if (!empty($results['results'])) {
        echo "🎯 Results:\n";
        foreach ($results['results'] as $i => $result) {
            echo sprintf(
                "  %d. %s (score: %.4f, quality: %.2f)\n",
                $i + 1,
                $result['content_path'],
                $result['relevance_score'],
                $result['quality_score']
            );
        }
    }

    echo "\n✅ TEST COMPLETE!\n\n";
    echo "💡 TIP: Run this test again to see cache performance:\n";
    echo "   php test_semantic_search.php\n\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
