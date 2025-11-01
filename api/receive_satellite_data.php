<?php
/**
 * Intelligence Hub - Satellite Data Receiver
 * 
 * Receives file data from satellite applications (CIS, VapeShed, Wholesale)
 * and stores it in the central intelligence database
 * 
 * Usage: POST from satellites with JSON payload containing file data
 * 
 * @author Intelligence System
 * @version 1.0.0
 */

declare(strict_types=1);
header('Content-Type: application/json');

// Authentication
$authKey = $_POST['auth'] ?? $_GET['auth'] ?? '';
if ($authKey !== 'bFUdRjh4Jx') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Database connection - INTELLIGENCE HUB
try {
    $db = new PDO(
        'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
        'hdgwrzntwa',
        'bFUdRjh4Jx',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Get JSON payload
$json = file_get_contents('php://input');
$payload = json_decode($json, true);

if (!$payload || !isset($payload['files']) || !is_array($payload['files'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payload']);
    exit;
}

// Process each file
$stats = [
    'received' => 0,
    'inserted' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0,
    'error_details' => []
];

foreach ($payload['files'] as $file) {
    $stats['received']++;
    
    try {
        // Validate required fields
        $required = ['org_id', 'unit_id', 'content_path', 'content_name', 'content_hash', 'file_size', 'mime_type', 'content_type'];
        foreach ($required as $field) {
            if (!isset($file[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Get or create content_type_id
        $contentType = $file['content_type'] ?? 'Unknown';
        $stmt = $db->prepare("SELECT content_type_id FROM intelligence_content_types WHERE type_name = ? LIMIT 1");
        $stmt->execute([$contentType]);
        $typeRow = $stmt->fetch();
        
        if (!$typeRow) {
            // Create new content type
            $stmt = $db->prepare("INSERT INTO intelligence_content_types (type_name, type_category, description, created_at) VALUES (?, 'code', ?, NOW())");
            $stmt->execute([$contentType, "Auto-created for {$contentType}"]);
            $contentTypeId = (int)$db->lastInsertId();
        } else {
            $contentTypeId = (int)$typeRow['content_type_id'];
        }
        
                // Check if content exists
        $stmt = $db->prepare("SELECT content_id, file_modified FROM intelligence_content WHERE content_hash = ?");
        $stmt->execute([$file['content_hash']]);
        $existing = $stmt->fetch();
        
        $contentId = null;
        
        if ($existing) {
            $contentId = (int)$existing['content_id'];
            
            // Skip if file hasn't been modified
            if (isset($file['file_modified']) && $existing['file_modified'] === $file['file_modified']) {
                $stats['skipped']++;
                continue;
            }
            
            // Update existing record
            $stmt = $db->prepare("
                UPDATE intelligence_content 
                SET 
                    file_size = ?,
                    file_modified = ?,
                    mime_type = ?,
                    language_detected = ?,
                    last_analyzed = NOW(),
                    updated_at = NOW()
                WHERE content_id = ?
            ");
            $stmt->execute([
                $file['file_size'],
                $file['file_modified'] ?? null,
                $file['mime_type'],
                isset($file['language_confidence']) && $file['language_confidence'] > 0.8 ? 'en' : null,
                $contentId
            ]);
            
            $stats['updated']++;
        } else {
            // Insert new content record
            $stmt = $db->prepare("
                INSERT INTO intelligence_content (
                    org_id,
                    unit_id,
                    content_type_id,
                    source_system,
                    content_path,
                    content_name,
                    content_hash,
                    file_size,
                    file_modified,
                    mime_type,
                    language_detected,
                    encoding,
                    intelligence_score,
                    complexity_score,
                    quality_score,
                    business_value_score,
                    last_analyzed,
                    is_active,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, NOW(), NOW())
            ");
            
            $stmt->execute([
                $file['org_id'],
                $file['unit_id'],
                $contentTypeId,
                $file['unit_name'] ?? 'Unknown',
                $file['content_path'],
                $file['content_name'],
                $file['content_hash'],
                $file['file_size'],
                $file['file_modified'] ?? null,
                $file['mime_type'],
                isset($file['language_confidence']) && $file['language_confidence'] > 0.8 ? 'en' : null,
                'UTF-8',
                0.00, // intelligence_score - calculated later
                0.00, // complexity_score - calculated later
                0.00, // quality_score - calculated later
                0.00  // business_value_score - calculated later
            ]);
            
            $contentId = (int)$db->lastInsertId();
            $stats['inserted']++;
        }
        
        // Now handle content_text if provided
        if (isset($file['content_text']) && $file['content_text'] !== '') {
            // Check if text record already exists
            $stmt = $db->prepare("SELECT text_id FROM intelligence_content_text WHERE content_id = ? LIMIT 1");
            $stmt->execute([$contentId]);
            $textExists = $stmt->fetch();
            
            if ($textExists) {
                // Update existing text record
                $stmt = $db->prepare("
                    UPDATE intelligence_content_text
                    SET
                        content_text = ?,
                        content_summary = ?,
                        extracted_keywords = ?,
                        semantic_tags = ?,
                        entities_detected = ?,
                        line_count = ?,
                        word_count = ?,
                        character_count = ?,
                        readability_score = ?,
                        sentiment_score = ?,
                        language_confidence = ?,
                        updated_at = NOW()
                    WHERE content_id = ?
                ");
                
                $stmt->execute([
                    $file['content_text'],
                    !empty($file['content_text']) ? substr($file['content_text'], 0, 500) : null,
                    isset($file['keywords']) ? json_encode($file['keywords']) : null,
                    isset($file['semantic_tags']) ? json_encode($file['semantic_tags']) : null,
                    isset($file['entities']) ? json_encode($file['entities']) : null,
                    $file['line_count'] ?? 0,
                    $file['word_count'] ?? 0,
                    $file['character_count'] ?? 0,
                    $file['readability_score'] ?? null,
                    $file['sentiment_score'] ?? null,
                    $file['language_confidence'] ?? null,
                    $contentId
                ]);
            } else {
                // Insert new text record
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
                ");
                
                $stmt->execute([
                    $contentId,
                    $file['content_text'],
                    !empty($file['content_text']) ? substr($file['content_text'], 0, 500) : null,
                    isset($file['keywords']) ? json_encode($file['keywords']) : null,
                    isset($file['semantic_tags']) ? json_encode($file['semantic_tags']) : null,
                    isset($file['entities']) ? json_encode($file['entities']) : null,
                    $file['line_count'] ?? 0,
                    $file['word_count'] ?? 0,
                    $file['character_count'] ?? 0,
                    $file['readability_score'] ?? null,
                    $file['sentiment_score'] ?? null,
                    $file['language_confidence'] ?? null
                ]);
            }
        }
        
    } catch (Exception $e) {
        $stats['errors']++;
        $stats['error_details'][] = [
            'file' => $file['content_path'] ?? 'unknown',
            'error' => $e->getMessage()
        ];
    }
}

// Return stats
echo json_encode([
    'success' => true,
    'message' => "Processed {$stats['received']} files",
    'stats' => $stats
], JSON_PRETTY_PRINT);

exit;
