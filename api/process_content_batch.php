<?php
/**
 * HTTP API: Process Content Text in Batches
 * 
 * Accepts HTTP requests to process files and extract text content.
 * Returns JSON responses with progress updates.
 * 
 * Can be called from any application server to process their files.
 * 
 * Usage:
 *   curl -X POST https://gpt.ecigdis.co.nz/api/process_content_batch.php \
 *     -d "auth_key=bFUdRjh4Jx" \
 *     -d "server_path=/home/master/applications/jcepnzzkmj/" \
 *     -d "batch_size=100"
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Authentication
$authKey = $_POST['auth_key'] ?? $_GET['auth_key'] ?? '';
$validKey = 'bFUdRjh4Jx';

if ($authKey !== $validKey) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid authentication key'
    ]);
    exit;
}

// Parameters
$serverPath = $_POST['server_path'] ?? $_GET['server_path'] ?? '';
$batchSize = (int)($_POST['batch_size'] ?? $_GET['batch_size'] ?? 50);
$forceReprocess = isset($_POST['force']) || isset($_GET['force']);

if (empty($serverPath)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Missing required parameter: server_path'
    ]);
    exit;
}

// Database connection
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=hdgwrzntwa', 'hdgwrzntwa', 'bFUdRjh4Jx');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Get files that need processing
$whereClause = $forceReprocess ? "" : "AND ct.content_id IS NULL";
$whereClause .= " AND ic.content_path LIKE " . $db->quote($serverPath . '%');

$query = "
    SELECT 
        ic.content_id,
        ic.content_path,
        ic.content_name,
        ic.content_type_id,
        ic.mime_type,
        ic.file_size,
        ict.type_name,
        bu.unit_name
    FROM intelligence_content ic
    JOIN intelligence_content_types ict ON ic.content_type_id = ict.content_type_id
    JOIN business_units bu ON ic.unit_id = bu.unit_id
    LEFT JOIN intelligence_content_text ct ON ic.content_id = ct.content_id
    WHERE ic.is_active = 1
    {$whereClause}
    ORDER BY ic.content_id DESC
    LIMIT {$batchSize}
";

try {
    $stmt = $db->query($query);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . $e->getMessage()
    ]);
    exit;
}

if (empty($files)) {
    echo json_encode([
        'success' => true,
        'message' => 'No files need processing',
        'processed' => 0,
        'skipped' => 0,
        'errors' => 0
    ]);
    exit;
}

// Process files
$stats = [
    'processed' => 0,
    'errors' => 0,
    'skipped' => 0,
    'total_words' => 0,
    'total_chars' => 0,
    'files' => []
];

foreach ($files as $file) {
    try {
        $result = processFile($db, $file);
        $stats['processed'] += $result['processed'];
        $stats['skipped'] += $result['skipped'];
        $stats['total_words'] += $result['words'];
        $stats['total_chars'] += $result['chars'];
        
        if ($result['processed'] > 0) {
            $stats['files'][] = [
                'name' => $file['content_name'],
                'words' => $result['words'],
                'status' => 'processed'
            ];
        }
    } catch (Exception $e) {
        $stats['errors']++;
        $stats['files'][] = [
            'name' => $file['content_name'],
            'error' => $e->getMessage(),
            'status' => 'error'
        ];
    }
}

echo json_encode([
    'success' => true,
    'processed' => $stats['processed'],
    'skipped' => $stats['skipped'],
    'errors' => $stats['errors'],
    'total_words' => $stats['total_words'],
    'total_chars' => $stats['total_chars'],
    'files' => $stats['files']
], JSON_PRETTY_PRINT);

/**
 * Process a single file
 */
function processFile(PDO $db, array $file): array
{
    $filePath = $file['content_path'];
    
    // Check if file exists and is readable
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return ['processed' => 0, 'skipped' => 1, 'words' => 0, 'chars' => 0];
    }
    
    // Skip binary files and very large files (>10MB)
    if ($file['file_size'] > 10 * 1024 * 1024) {
        return ['processed' => 0, 'skipped' => 1, 'words' => 0, 'chars' => 0];
    }
    
    // Read file content
    $content = @file_get_contents($filePath);
    if ($content === false || empty(trim($content))) {
        return ['processed' => 0, 'skipped' => 1, 'words' => 0, 'chars' => 0];
    }
    
    // Extract text based on file type
    $extractedText = extractText($content, $file['type_name'], $file['mime_type']);
    
    // Remove 4-byte UTF-8 characters (emojis) for MySQL compatibility
    $extractedText = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $extractedText);
    
    // Calculate metrics
    $metrics = analyzeText($extractedText);
    
    // Extract keywords and entities
    $keywords = extractKeywords($extractedText, $file['type_name']);
    $semanticTags = generateSemanticTags($extractedText, $file['type_name']);
    $entities = detectEntities($extractedText);
    
    // Generate summary
    $summary = generateSummary($extractedText);
    
    // Insert/update intelligence_content_text
    $stmt = $db->prepare("
        INSERT INTO intelligence_content_text (
            content_id,
            content_text,
            content_summary,
            extracted_keywords,
            semantic_tags,
            entities_detected,
            line_count,
            word_count,
            character_count,
            readability_score,
            sentiment_score,
            language_confidence,
            created_at,
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ON DUPLICATE KEY UPDATE
            content_text = VALUES(content_text),
            content_summary = VALUES(content_summary),
            extracted_keywords = VALUES(extracted_keywords),
            semantic_tags = VALUES(semantic_tags),
            entities_detected = VALUES(entities_detected),
            line_count = VALUES(line_count),
            word_count = VALUES(word_count),
            character_count = VALUES(character_count),
            readability_score = VALUES(readability_score),
            sentiment_score = VALUES(sentiment_score),
            language_confidence = VALUES(language_confidence),
            updated_at = NOW()
    ");
    
    $stmt->execute([
        $file['content_id'],
        $extractedText,
        $summary,
        json_encode($keywords),
        json_encode($semanticTags),
        json_encode($entities),
        $metrics['line_count'],
        $metrics['word_count'],
        $metrics['char_count'],
        $metrics['readability'],
        $metrics['sentiment'],
        $metrics['language_confidence']
    ]);
    
    return [
        'processed' => 1,
        'skipped' => 0,
        'words' => $metrics['word_count'],
        'chars' => $metrics['char_count']
    ];
}

function extractText(string $content, string $type, ?string $mimeType): string
{
    if (in_array($type, ['PHP Code Intelligence', 'JavaScript Intelligence', 'CSS Intelligence', 'HTML Intelligence'])) {
        return $content;
    }
    if ($type === 'Documentation Intelligence' || str_contains($mimeType ?? '', 'markdown')) {
        return $content;
    }
    if ($type === 'Data Structure Intelligence' || str_contains($mimeType ?? '', 'json')) {
        $decoded = json_decode($content, true);
        if ($decoded !== null) {
            return json_encode($decoded, JSON_PRETTY_PRINT);
        }
    }
    if (str_contains($mimeType ?? '', 'xml') || str_contains($mimeType ?? '', 'html')) {
        return strip_tags($content);
    }
    return $content;
}

function analyzeText(string $text): array
{
    $lines = explode("\n", $text);
    $words = str_word_count($text);
    $chars = strlen($text);
    
    $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $sentenceCount = max(count($sentences), 1);
    $avgWordsPerSentence = $words / $sentenceCount;
    $avgSyllablesPerWord = 1.5;
    $readability = 206.835 - (1.015 * $avgWordsPerSentence) - (84.6 * $avgSyllablesPerWord);
    $readability = max(0, min(100, $readability));
    
    $positiveWords = ['success', 'complete', 'active', 'working', 'good', 'great', 'excellent'];
    $negativeWords = ['error', 'fail', 'broken', 'issue', 'problem', 'bug', 'warning'];
    
    $textLower = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;
    
    foreach ($positiveWords as $word) {
        $positiveCount += substr_count($textLower, $word);
    }
    foreach ($negativeWords as $word) {
        $negativeCount += substr_count($textLower, $word);
    }
    
    $totalSentiment = $positiveCount + $negativeCount;
    $sentiment = $totalSentiment > 0 ? ($positiveCount - $negativeCount) / $totalSentiment : 0;
    $sentiment = max(-1, min(1, $sentiment));
    
    $asciiChars = strlen(preg_replace('/[^\x00-\x7F]/', '', $text));
    $languageConfidence = $chars > 0 ? $asciiChars / $chars : 1;
    
    return [
        'line_count' => count($lines),
        'word_count' => $words,
        'char_count' => $chars,
        'readability' => round($readability, 2),
        'sentiment' => round($sentiment, 2),
        'language_confidence' => round($languageConfidence, 2)
    ];
}

function extractKeywords(string $text, string $type): array
{
    $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'as', 'by', 'that', 'this', 'it', 'from', 'are', 'was', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];
    
    $words = str_word_count(strtolower($text), 1);
    $words = array_filter($words, function($word) use ($stopWords) {
        return strlen($word) > 3 && !in_array($word, $stopWords);
    });
    
    $frequency = array_count_values($words);
    arsort($frequency);
    
    return array_slice(array_keys($frequency), 0, 20);
}

function generateSemanticTags(string $text, string $type): array
{
    $tags = [];
    $textLower = strtolower($text);
    
    if (str_contains($textLower, 'function') || str_contains($textLower, 'class')) {
        $tags[] = 'code';
    }
    if (str_contains($textLower, 'select') || str_contains($textLower, 'insert') || str_contains($textLower, 'update')) {
        $tags[] = 'database';
    }
    if (str_contains($textLower, 'api') || str_contains($textLower, 'endpoint') || str_contains($textLower, 'rest')) {
        $tags[] = 'api';
    }
    if (str_contains($textLower, 'customer') || str_contains($textLower, 'user')) {
        $tags[] = 'customer';
    }
    if (str_contains($textLower, 'product') || str_contains($textLower, 'inventory')) {
        $tags[] = 'inventory';
    }
    if (str_contains($textLower, 'order') || str_contains($textLower, 'sale')) {
        $tags[] = 'sales';
    }
    if (str_contains($textLower, 'todo') || str_contains($textLower, 'fixme')) {
        $tags[] = 'action-required';
    }
    if (str_contains($textLower, 'documentation') || str_contains($textLower, 'readme')) {
        $tags[] = 'documentation';
    }
    if (str_contains($textLower, 'password') || str_contains($textLower, 'auth') || str_contains($textLower, 'security')) {
        $tags[] = 'security';
    }
    
    return array_unique($tags);
}

function detectEntities(string $text): array
{
    $entities = [];
    
    if (preg_match_all('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $text, $matches)) {
        $entities['emails'] = array_unique($matches[0]);
    }
    if (preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $matches)) {
        $entities['urls'] = array_unique($matches[0]);
    }
    if (preg_match_all('/class\s+([A-Z][a-zA-Z0-9_]*)/i', $text, $matches)) {
        $entities['classes'] = array_unique($matches[1]);
    }
    if (preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $text, $matches)) {
        $entities['functions'] = array_unique($matches[1]);
    }
    
    return $entities;
}

function generateSummary(string $text): string
{
    $text = preg_replace('/^[\s\/*#]+/', '', $text);
    $summary = substr($text, 0, 500);
    
    $lastPeriod = strrpos($summary, '.');
    if ($lastPeriod !== false && $lastPeriod > 100) {
        $summary = substr($summary, 0, $lastPeriod + 1);
    }
    
    return trim($summary);
}
