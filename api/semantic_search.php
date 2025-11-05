<?php
/**
 * Semantic Search API
 *
 * Advanced search combining:
 * - Vector embeddings (OpenAI)
 * - SimHash similarity
 * - Full-text search
 * - Redis caching
 *
 * Endpoints:
 * - POST /search - Hybrid semantic search
 * - POST /similar - Find similar files by file_id
 * - GET /analytics - Search analytics
 * - POST /index - Index file for semantic search
 */

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../config/redis.php';

header('Content-Type: application/json');

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Redis connection
$redis = null;
try {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
} catch (Exception $e) {
    error_log("Redis connection failed: " . $e->getMessage());
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];

// Get action from query string for GET or from body for POST
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? 'search';
} else {
    $action = $_GET['action'] ?? 'analytics';
}

// Helper: Calculate cosine similarity between two vectors
function cosineSimilarity($vec1, $vec2) {
    if (count($vec1) !== count($vec2)) {
        return 0;
    }

    $dotProduct = 0;
    $mag1 = 0;
    $mag2 = 0;

    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $mag1 += $vec1[$i] * $vec1[$i];
        $mag2 += $vec2[$i] * $vec2[$i];
    }

    $mag1 = sqrt($mag1);
    $mag2 = sqrt($mag2);

    if ($mag1 == 0 || $mag2 == 0) {
        return 0;
    }

    return $dotProduct / ($mag1 * $mag2);
}

// Helper: Get OpenAI embedding
function getEmbedding($text, $model = 'text-embedding-3-small') {
    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        return null;
    }

    $ch = curl_init('https://api.openai.com/v1/embeddings');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'input' => substr($text, 0, 8000), // Limit text length
            'model' => $model
        ])
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data'][0]['embedding'] ?? null;
    }

    return null;
}

// Helper: Calculate SimHash
function calculateSimHash($text, $bits = 64) {
    $words = preg_split('/\s+/', strtolower($text));
    $hashBits = array_fill(0, $bits, 0);

    foreach ($words as $word) {
        if (empty($word)) continue;
        $hash = crc32($word);

        for ($i = 0; $i < $bits; $i++) {
            if ($hash & (1 << $i)) {
                $hashBits[$i]++;
            } else {
                $hashBits[$i]--;
            }
        }
    }

    $simhash = 0;
    for ($i = 0; $i < $bits; $i++) {
        if ($hashBits[$i] > 0) {
            $simhash |= (1 << $i);
        }
    }

    return $simhash;
}

// Route: POST /search - Hybrid semantic search
if ($method === 'POST' && $action === 'search') {
    $input = json_decode(file_get_contents('php://input'), true);
    $query = $input['query'] ?? '';
    $searchType = $input['type'] ?? 'hybrid'; // semantic, hybrid, fulltext, simhash
    $limit = min((int)($input['limit'] ?? 10), 50);
    $filters = $input['filters'] ?? [];

    if (empty($query)) {
        echo json_encode(['success' => false, 'error' => 'Query is required']);
        exit;
    }

    $startTime = microtime(true);
    $queryHash = hash('sha256', $query . $searchType . json_encode($filters));

    // Check cache first
    $cacheKey = "semantic_search:{$queryHash}";
    $cached = $redis ? $redis->get($cacheKey) : null;

    if ($cached) {
        $results = json_decode($cached, true);
        $executionTime = (microtime(true) - $startTime) * 1000;

        // Log analytics
        $stmt = $conn->prepare("CALL sp_log_search_analytics(?, ?, ?, ?, ?, 1, ?, ?, NULL, NULL, ?)");
        $topFileId = $results[0]['file_id'] ?? null;
        $topScore = $results[0]['score'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $stmt->execute([$query, $queryHash, $searchType, count($results), (int)$executionTime, $topFileId, $topScore, $ipAddress]);

        echo json_encode([
            'success' => true,
            'results' => $results,
            'count' => count($results),
            'execution_time_ms' => $executionTime,
            'cache_hit' => true
        ]);
        exit;
    }

    $results = [];

    // SEMANTIC SEARCH (Vector embeddings)
    if ($searchType === 'semantic' || $searchType === 'hybrid') {
        $queryEmbedding = getEmbedding($query);

        if ($queryEmbedding) {
            // Get all embeddings
            $stmt = $conn->prepare("
                SELECT e.file_id, e.embedding_vector, f.file_name, f.intelligence_type, f.file_size, f.content_summary
                FROM intelligence_embeddings e
                JOIN intelligence_files f ON e.file_id = f.file_id
                WHERE e.is_active = 1 AND f.is_active = 1
                LIMIT 1000
            ");
            $stmt->execute();
            $embeddings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($embeddings as $row) {
                $fileEmbedding = json_decode($row['embedding_vector'], true);
                $similarity = cosineSimilarity($queryEmbedding, $fileEmbedding);

                if ($similarity > 0.5) { // Threshold
                    $results[] = [
                        'file_id' => $row['file_id'],
                        'file_name' => $row['file_name'],
                        'intelligence_type' => $row['intelligence_type'],
                        'file_size' => $row['file_size'],
                        'content_summary' => substr($row['content_summary'], 0, 200),
                        'score' => round($similarity, 4),
                        'match_type' => 'semantic'
                    ];
                }
            }
        }
    }

    // FULLTEXT SEARCH
    if ($searchType === 'fulltext' || $searchType === 'hybrid') {
        $stmt = $conn->prepare("
            SELECT f.file_id, f.file_name, f.intelligence_type, f.file_size, f.content_summary,
                   MATCH(f.content_summary) AGAINST(:query1 IN NATURAL LANGUAGE MODE) as relevance
            FROM intelligence_files f
            WHERE MATCH(f.content_summary) AGAINST(:query2 IN NATURAL LANGUAGE MODE)
                AND f.is_active = 1
            ORDER BY relevance DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':query1', $query, PDO::PARAM_STR);
        $stmt->bindValue(':query2', $query, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $fullTextResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($fullTextResults as $row) {
            $results[] = [
                'file_id' => $row['file_id'],
                'file_name' => $row['file_name'],
                'intelligence_type' => $row['intelligence_type'],
                'file_size' => $row['file_size'],
                'content_summary' => substr($row['content_summary'], 0, 200),
                'score' => round($row['relevance'] / 20, 4), // Normalize
                'match_type' => 'fulltext'
            ];
        }
    }

    // SIMHASH SEARCH
    if ($searchType === 'simhash') {
        $querySimHash = calculateSimHash($query);

        $stmt = $conn->prepare("CALL sp_find_similar_by_simhash(?, 10, ?)");
        $stmt->execute([$querySimHash, $limit]);
        $simHashResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($simHashResults as $row) {
            $results[] = [
                'file_id' => $row['file_id'],
                'file_name' => $row['file_name'],
                'intelligence_type' => $row['intelligence_type'],
                'file_size' => $row['file_size'],
                'content_summary' => substr($row['content_summary'], 0, 200),
                'score' => round(1 - ($row['hamming_distance'] / 64), 4),
                'match_type' => 'simhash'
            ];
        }
    }

    // Remove duplicates and sort by score
    $uniqueResults = [];
    foreach ($results as $result) {
        $fileId = $result['file_id'];
        if (!isset($uniqueResults[$fileId]) || $uniqueResults[$fileId]['score'] < $result['score']) {
            $uniqueResults[$fileId] = $result;
        }
    }

    $results = array_values($uniqueResults);
    usort($results, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    $results = array_slice($results, 0, $limit);

    // Cache results
    if ($redis && !empty($results)) {
        $redis->setex($cacheKey, 3600, json_encode($results)); // 1 hour cache

        // Also store in database cache
        $fileIds = json_encode(array_column($results, 'file_id'));
        $scores = json_encode(array_column($results, 'score'));
        $stmt = $conn->prepare("CALL sp_cache_search_results(?, ?, ?, ?, ?, ?)");
        $stmt->execute([$query, $queryHash, $searchType, $fileIds, $scores, 60]);
    }

    $executionTime = (microtime(true) - $startTime) * 1000;

    // Log analytics
    $stmt = $conn->prepare("CALL sp_log_search_analytics(?, ?, ?, ?, ?, 0, ?, ?, NULL, NULL, ?)");
    $topFileId = $results[0]['file_id'] ?? null;
    $topScore = $results[0]['score'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt->execute([$query, $queryHash, $searchType, count($results), (int)$executionTime, $topFileId, $topScore, $ipAddress]);

    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results),
        'execution_time_ms' => round($executionTime, 2),
        'cache_hit' => false
    ]);
    exit;
}

// Route: POST /similar - Find similar files
if ($method === 'POST' && $action === 'similar') {
    $input = json_decode(file_get_contents('php://input'), true);
    $fileId = $input['file_id'] ?? 0;
    $limit = min((int)($input['limit'] ?? 10), 50);

    if (!$fileId) {
        echo json_encode(['success' => false, 'error' => 'file_id is required']);
        exit;
    }

    // Get file's SimHash
    $stmt = $conn->prepare("SELECT simhash64 FROM intelligence_embeddings WHERE file_id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$fileId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !$row['simhash64']) {
        echo json_encode(['success' => false, 'error' => 'File not indexed or no SimHash available']);
        exit;
    }

    $simhash = $row['simhash64'];

    // Find similar files
    $stmt = $conn->prepare("CALL sp_find_similar_by_simhash(?, 15, ?)");
    $stmt->execute([$simhash, $limit + 1]); // +1 to exclude self
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Remove the source file itself
    $results = array_filter($results, function($r) use ($fileId) {
        return $r['file_id'] != $fileId;
    });
    $results = array_values($results);
    $results = array_slice($results, 0, $limit);

    echo json_encode([
        'success' => true,
        'file_id' => $fileId,
        'similar_files' => $results,
        'count' => count($results)
    ]);
    exit;
}

// Route: GET /analytics - Search analytics
if ($method === 'GET' && $action === 'analytics') {
    $period = $_GET['period'] ?? '7days';

    $results = [
        'top_searches' => [],
        'performance' => [],
        'most_found_files' => []
    ];

    // Top searches
    $stmt = $conn->query("SELECT * FROM v_top_semantic_searches LIMIT 20");
    $results['top_searches'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Performance metrics
    $stmt = $conn->query("SELECT * FROM v_semantic_search_performance ORDER BY search_date DESC LIMIT 30");
    $results['performance'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Most found files
    $stmt = $conn->query("SELECT * FROM v_most_searched_files LIMIT 20");
    $results['most_found_files'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'analytics' => $results
    ]);
    exit;
}

// Route: POST /index - Index a file for semantic search
if ($method === 'POST' && $action === 'index') {
    $input = json_decode(file_get_contents('php://input'), true);
    $fileId = $input['file_id'] ?? 0;

    if (!$fileId) {
        echo json_encode(['success' => false, 'error' => 'file_id is required']);
        exit;
    }

    // Get file content
    $stmt = $conn->prepare("SELECT file_content, content_summary FROM intelligence_files WHERE file_id = ? AND is_active = 1");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$file) {
        echo json_encode(['success' => false, 'error' => 'File not found']);
        exit;
    }

    $text = $file['file_content'] ?: $file['content_summary'];
    $contentHash = hash('sha256', $text);

    // Calculate SimHash
    $simhash = calculateSimHash($text);

    // Get embedding (if OpenAI key available)
    $embedding = getEmbedding($text);

    if ($embedding) {
        $embeddingJson = json_encode($embedding);
        $stmt = $conn->prepare("CALL sp_upsert_embedding(?, NULL, ?, ?, ?, ?, ?, 'text-embedding-3-small')");
        $stmt->execute([$fileId, $embeddingJson, $simhash, $contentHash, strlen($text), null]);

        echo json_encode([
            'success' => true,
            'file_id' => $fileId,
            'indexed' => true,
            'has_embedding' => true,
            'has_simhash' => true
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Could not generate embedding (check OpenAI API key)'
        ]);
    }
    exit;
}

// Default: API info
echo json_encode([
    'api' => 'Semantic Search API',
    'version' => '2.0',
    'endpoints' => [
        'POST /search' => 'Hybrid semantic search',
        'POST /similar' => 'Find similar files by file_id',
        'GET /analytics' => 'Search analytics',
        'POST /index' => 'Index file for semantic search'
    ]
]);
