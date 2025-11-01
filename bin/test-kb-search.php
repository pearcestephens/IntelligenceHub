#!/usr/bin/env php
<?php
/**
 * Quick KB Search Tester
 *
 * Test keyword search against ingested CIS knowledge base
 *
 * Usage:
 *   php bin/test-kb-search.php "search query"
 *
 * Examples:
 *   php bin/test-kb-search.php "stock transfer"
 *   php bin/test-kb-search.php "API documentation"
 */

declare(strict_types=1);

// Parse arguments
if ($argc < 2) {
    echo "Usage: php bin/test-kb-search.php \"search query\"\n";
    echo "Example: php bin/test-kb-search.php \"stock transfer\"\n";
    exit(1);
}

$query = implode(' ', array_slice($argv, 1));

// Configuration
$config = [
    'mysql_host' => '127.0.0.1',
    'mysql_user' => 'jcepnzzkmj',
    'mysql_pass' => 'wprKh9Jq63',
    'mysql_db' => 'jcepnzzkmj'
];

echo "🔍 CIS Knowledge Base Search Tester\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
echo "Query: \"{$query}\"\n\n";

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$config['mysql_host']};dbname={$config['mysql_db']};charset=utf8mb4",
        $config['mysql_user'],
        $config['mysql_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("❌ Database connection failed: {$e->getMessage()}\n");
}

$startTime = microtime(true);

// Build search query (keyword search in title and meta content)
$searchTerms = explode(' ', $query);
$conditions = [];
$params = [];

foreach ($searchTerms as $term) {
    if (strlen($term) < 3) continue;
    $conditions[] = '(title LIKE ? OR meta LIKE ?)';
    $params[] = "%{$term}%";
    $params[] = "%{$term}%";
}

if (empty($conditions)) {
    echo "❌ Search query too short (need words with 3+ characters)\n";
    exit(1);
}

$whereClause = implode(' OR ', $conditions);

$sql = "
    SELECT
        id,
        title,
        mime,
        uri as source,
        SUBSTRING(meta, 1, 200) as preview,
        LENGTH(meta) as content_length
    FROM agent_kb_docs
    WHERE {$whereClause}
    ORDER BY (
        CASE
            WHEN title LIKE ? THEN 3
            WHEN uri LIKE ? THEN 2
            ELSE 1
        END
    ) DESC
    LIMIT 10
";

// Add relevance params
$params[] = "%{$query}%";
$params[] = "%{$query}%";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    $duration = round((microtime(true) - $startTime) * 1000, 2);

    echo "Found " . count($results) . " results ({$duration}ms)\n\n";

    if (empty($results)) {
        echo "No results found. Suggestions:\n";
        echo "  - Use more general keywords\n";
        echo "  - Try different phrasing\n";
        echo "  - Check if KB is ingested: SELECT COUNT(*) FROM agent_kb_docs;\n";
        exit(0);
    }

    foreach ($results as $i => $result) {
        $num = $i + 1;
        $sizekb = round($result['content_length'] / 1024, 1);

        echo "[{$num}] {$result['title']}\n";
        echo "    MIME: {$result['mime']}\n";
        echo "    Source: {$result['source']}\n";
        echo "    Size: {$sizekb} KB\n";
        echo "    Preview: " . trim($result['preview']) . "...\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Search failed: {$e->getMessage()}\n";
    exit(1);
}
