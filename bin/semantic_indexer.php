#!/usr/bin/env php
<?php
/**
 * Semantic Search Indexer
 *
 * Background script to index all intelligence files with:
 * - Vector embeddings (OpenAI)
 * - SimHash for fast similarity
 *
 * Usage:
 * php semantic_indexer.php [--batch-size=100] [--start-from=0] [--dry-run]
 */

require_once __DIR__ . '/../classes/Database.php';

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (!empty($key) && !empty($value)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Parse command line arguments
$options = getopt('', ['batch-size::', 'start-from::', 'dry-run', 'help']);

if (isset($options['help'])) {
    echo "Semantic Search Indexer\n";
    echo "Usage: php semantic_indexer.php [options]\n\n";
    echo "Options:\n";
    echo "  --batch-size=N    Process N files per batch (default: 100)\n";
    echo "  --start-from=N    Start from file ID N (default: 0)\n";
    echo "  --dry-run         Show what would be done without doing it\n";
    echo "  --help            Show this help\n";
    exit(0);
}

$batchSize = (int)($options['batch-size'] ?? 100);
$startFrom = (int)($options['start-from'] ?? 0);
$dryRun = isset($options['dry-run']);

echo "=================================================================\n";
echo "Semantic Search Indexer\n";
echo "=================================================================\n";
echo "Batch size: $batchSize\n";
echo "Start from: $startFrom\n";
echo "Dry run: " . ($dryRun ? 'YES' : 'NO') . "\n";
echo "=================================================================\n\n";

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Check OpenAI API key
$openaiKey = getenv('OPENAI_API_KEY');
if (!$openaiKey) {
    echo "⚠️  WARNING: OPENAI_API_KEY not set. Only SimHash will be generated.\n\n";
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

// Helper: Get OpenAI embedding
function getEmbedding($text, $apiKey, $model = 'text-embedding-3-small') {
    // Limit text to ~8000 characters for API
    $text = substr($text, 0, 8000);

    $ch = curl_init('https://api.openai.com/v1/embeddings');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'input' => $text,
            'model' => $model
        ]),
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data'][0]['embedding'] ?? null;
    } else {
        error_log("OpenAI API error: $httpCode - $error - $response");
        return null;
    }
}

// Get total count
$stmt = $conn->prepare("SELECT COUNT(*) FROM intelligence_files WHERE is_active = 1 AND file_id >= ?");
$stmt->execute([$startFrom]);
$totalFiles = $stmt->fetchColumn();

echo "📊 Total files to process: $totalFiles\n\n";

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No changes will be made\n\n";
}

// Process in batches
$processed = 0;
$indexed = 0;
$errors = 0;
$skipped = 0;

$startTime = microtime(true);

while ($processed < $totalFiles) {
    // Get batch of files
    $stmt = $conn->prepare("
        SELECT f.file_id, f.file_name, f.file_content, f.content_summary, f.intelligence_type
        FROM intelligence_files f
        LEFT JOIN intelligence_embeddings e ON f.file_id = e.file_id AND e.embedding_model = 'text-embedding-3-small'
        WHERE f.is_active = 1
            AND f.file_id >= :start_from
            AND e.embedding_id IS NULL
        ORDER BY f.file_id ASC
        LIMIT :batch_size
    ");
    $stmt->bindValue(':start_from', $startFrom, PDO::PARAM_INT);
    $stmt->bindValue(':batch_size', $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);    if (empty($files)) {
        echo "\n✅ No more files to process!\n";
        break;
    }

    echo "📦 Processing batch: " . count($files) . " files...\n";

    foreach ($files as $file) {
        $fileId = $file['file_id'];
        $fileName = $file['file_name'];
        $text = $file['file_content'] ?: $file['content_summary'];

        if (empty($text)) {
            echo "⏭️  Skipped #$fileId ($fileName): No content\n";
            $skipped++;
            continue;
        }

        $contentHash = hash('sha256', $text);
        $textLength = strlen($text);

        // Calculate SimHash
        $simhash = calculateSimHash($text);

        // Get embedding if API key available
        $embedding = null;
        if ($openaiKey && !$dryRun) {
            $embedding = getEmbedding($text, $openaiKey);

            if (!$embedding) {
                echo "❌ Error #$fileId ($fileName): Failed to get embedding\n";
                $errors++;
                continue;
            }
        }

        if ($dryRun) {
            echo "✓ Would index #$fileId ($fileName) - SimHash: $simhash\n";
            $indexed++;
        } else {
            // Store in database
            try {
                $embeddingJson = $embedding ? json_encode($embedding) : json_encode([]);
                $stmt = $conn->prepare("CALL sp_upsert_embedding(?, NULL, ?, ?, ?, ?, NULL, 'text-embedding-3-small')");
                $stmt->execute([$fileId, $embeddingJson, $simhash, $contentHash, $textLength]);

                echo "✅ Indexed #$fileId ($fileName)\n";
                $indexed++;
            } catch (Exception $e) {
                echo "❌ Error #$fileId ($fileName): " . $e->getMessage() . "\n";
                $errors++;
            }
        }

        $processed++;

        // Rate limiting for OpenAI API
        if ($openaiKey && !$dryRun) {
            usleep(100000); // 100ms delay = 10 requests/second
        }
    }

    $startFrom = end($files)['file_id'] + 1;

    $elapsed = round(microtime(true) - $startTime, 2);
    $rate = $processed > 0 ? round($processed / $elapsed, 2) : 0;
    $eta = $rate > 0 ? round(($totalFiles - $processed) / $rate) : 0;

    echo "\n📈 Progress: $processed / $totalFiles ($indexed indexed, $errors errors, $skipped skipped)\n";
    echo "⏱️  Elapsed: {$elapsed}s | Rate: {$rate} files/sec | ETA: {$eta}s\n\n";

    // Small delay between batches
    sleep(1);
}

$totalTime = round(microtime(true) - $startTime, 2);

echo "=================================================================\n";
echo "✅ INDEXING COMPLETE!\n";
echo "=================================================================\n";
echo "Processed: $processed files\n";
echo "Indexed: $indexed files\n";
echo "Errors: $errors files\n";
echo "Skipped: $skipped files\n";
echo "Total time: {$totalTime}s\n";
echo "=================================================================\n";

// Show index statistics
$stmt = $conn->query("
    SELECT
        COUNT(*) as total_embeddings,
        COUNT(DISTINCT file_id) as unique_files,
        COUNT(CASE WHEN simhash64 IS NOT NULL THEN 1 END) as with_simhash,
        AVG(text_length) as avg_text_length,
        MAX(indexed_at) as last_indexed
    FROM intelligence_embeddings
    WHERE is_active = 1
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "\n📊 INDEX STATISTICS:\n";
echo "Total embeddings: " . $stats['total_embeddings'] . "\n";
echo "Unique files: " . $stats['unique_files'] . "\n";
echo "With SimHash: " . $stats['with_simhash'] . "\n";
echo "Avg text length: " . round($stats['avg_text_length']) . " chars\n";
echo "Last indexed: " . $stats['last_indexed'] . "\n";
echo "=================================================================\n";
