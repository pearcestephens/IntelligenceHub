<?php
/**
 * Security headers automatically added by hardening script
 */
require_once __DIR__ . '/../services/CSRFProtection.php';
require_once __DIR__ . '/../services/RateLimiter.php';

// Rate limiting
RateLimiter::enforceLimit(RateLimiter::getClientIdentifier(), 'api');

// CSRF protection for POST/PUT/DELETE requests
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'DELETE', 'PATCH'])) {
    CSRFProtection::requireValidToken();
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

/**
 * Remote Content Processing API
 * 
 * Allows other applications to request content processing for their files
 * via HTTP API calls
 * 
 * Usage:
 *   POST https://gpt.ecigdis.co.nz/api/process_content_remote.php
 *   {
 *     "auth_key": "bFUdRjh4Jx",
 *     "application": "jcepnzzkmj",
 *     "batch_size": 1000,
 *     "async": true
 *   }
 */

header('Content-Type: application/json');

// Authentication
$input = json_decode(file_get_contents('php://input'), true);
$authKey = $input['auth_key'] ?? $_POST['auth_key'] ?? '';

if ($authKey !== 'bFUdRjh4Jx') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$application = $input['application'] ?? 'jcepnzzkmj'; // Default to CIS
$batchSize = (int)($input['batch_size'] ?? 1000);
$async = $input['async'] ?? true;

// Map applications to their paths and DB credentials
$appConfig = [
    'jcepnzzkmj' => [
        'name' => 'CIS',
        'path' => '/home/master/applications/jcepnzzkmj/',
        'db' => ['host' => '127.0.0.1', 'name' => 'jcepnzzkmj', 'user' => 'jcepnzzkmj', 'pass' => 'wprKh9Jq63'],
        'unit_id' => 2
    ],
    'dvaxgvsxmz' => [
        'name' => 'VapeShed',
        'path' => '/home/master/applications/dvaxgvsxmz/',
        'db' => ['host' => '127.0.0.1', 'name' => 'dvaxgvsxmz', 'user' => 'dvaxgvsxmz', 'pass' => 'password'],
        'unit_id' => 3
    ],
    'hdgwrzntwa' => [
        'name' => 'Intelligence Hub',
        'path' => '/home/master/applications/hdgwrzntwa/',
        'db' => ['host' => '127.0.0.1', 'name' => 'hdgwrzntwa', 'user' => 'hdgwrzntwa', 'pass' => 'bFUdRjh4Jx'],
        'unit_id' => 1
    ]
];

if (!isset($appConfig[$application])) {
    echo json_encode(['error' => 'Unknown application']);
    exit;
}

$config = $appConfig[$application];

// If async, trigger processing in background
if ($async) {
    $command = sprintf(
        'cd %s && php %sscripts/process_content_text.php --batch=%d > /tmp/content_process_%s.log 2>&1 &',
        dirname(__DIR__),
        dirname(__DIR__) . '/',
        $batchSize,
        $application
    );
    
    exec($command);
    
    echo json_encode([
        'success' => true,
        'message' => 'Processing started in background',
        'application' => $config['name'],
        'batch_size' => $batchSize,
        'log_file' => "/tmp/content_process_{$application}.log"
    ]);
    exit;
}

// Otherwise, process synchronously and stream results
ob_start();

try {
    // Connect to the intelligence database (shared across all apps)
    $db = new PDO('mysql:host=127.0.0.1;dbname=hdgwrzntwa', 'hdgwrzntwa', 'bFUdRjh4Jx');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get files that need processing for this application
    $query = "
        SELECT 
            ic.content_id,
            ic.content_path,
            ic.content_name,
            ic.content_type_id,
            ic.mime_type,
            ic.file_size,
            ict.type_name
        FROM intelligence_content ic
        JOIN intelligence_content_types ict ON ic.content_type_id = ict.content_type_id
        LEFT JOIN intelligence_content_text ct ON ic.content_id = ct.content_id
        WHERE ic.is_active = 1
          AND ic.unit_id = :unit_id
          AND ct.content_id IS NULL
          AND ic.content_path LIKE :path_pattern
        ORDER BY ic.content_id DESC
        LIMIT :batch_size
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bindValue(':unit_id', $config['unit_id'], PDO::PARAM_INT);
    $stmt->bindValue(':path_pattern', $config['path'] . '%', PDO::PARAM_STR);
    $stmt->bindValue(':batch_size', $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = [
        'processed' => 0,
        'skipped' => 0,
        'errors' => 0,
        'total_words' => 0
    ];
    
    foreach ($files as $file) {
        if (!file_exists($file['content_path'])) {
            $stats['skipped']++;
            continue;
        }
        
        $content = @file_get_contents($file['content_path']);
        if ($content === false || empty(trim($content))) {
            $stats['skipped']++;
            continue;
        }
        
        // Remove emojis
        $content = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $content);
        
        // Basic analysis
        $wordCount = str_word_count($content);
        $charCount = strlen($content);
        $lineCount = substr_count($content, "\n") + 1;
        
        // Extract keywords (top 20 words)
        $words = str_word_count(strtolower($content), 1);
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and'];
        $words = array_filter($words, fn($w) => strlen($w) > 3 && !in_array($w, $stopWords));
        $frequency = array_count_values($words);
        arsort($frequency);
        $keywords = array_slice(array_keys($frequency), 0, 20);
        
        // Insert into intelligence_content_text
        $insertStmt = $db->prepare("
            INSERT INTO intelligence_content_text (
                content_id, content_text, content_summary,
                extracted_keywords, line_count, word_count, character_count,
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                content_text = VALUES(content_text),
                content_summary = VALUES(content_summary),
                extracted_keywords = VALUES(extracted_keywords),
                line_count = VALUES(line_count),
                word_count = VALUES(word_count),
                character_count = VALUES(character_count),
                updated_at = NOW()
        ");
        
        $summary = substr($content, 0, 500);
        $insertStmt->execute([
            $file['content_id'],
            $content,
            $summary,
            json_encode($keywords),
            $lineCount,
            $wordCount,
            $charCount
        ]);
        
        $stats['processed']++;
        $stats['total_words'] += $wordCount;
    }
    
    echo json_encode([
        'success' => true,
        'application' => $config['name'],
        'stats' => $stats,
        'files_found' => count($files)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
