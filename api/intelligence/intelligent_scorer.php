<?php
/**
 * Intelligent File Scorer
 * 
 * Analyzes files IN-PLACE and adds intelligence scores
 * NO duplication - just scores the existing intelligence_files table
 * 
 * Usage:
 *   php intelligent_scorer.php --server=jcepnzzkmj --batch=10000
 * 
 * @package Neural_Intelligence
 * @version 3.1.0
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

$db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$server = $argv[1] ?? 'jcepnzzkmj';
$batch_size = 10000;

echo date('Y-m-d H:i:s') . " 🧠 Starting Intelligent Scoring for $server\n";

// Get unscored files
$stmt = $db->prepare("
    SELECT file_id, file_type, intelligence_type, file_content, intelligence_data
    FROM intelligence_files
    WHERE server_id = ?
    AND file_content IS NOT NULL
    AND (intelligence_score IS NULL OR intelligence_score = 0)
    LIMIT $batch_size
");
$stmt->execute([$server]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo date('Y-m-d H:i:s') . " 📊 Scoring " . count($files) . " files...\n";

$scored = 0;
foreach ($files as $file) {
    $scores = calculateScores($file);
    
    $update = $db->prepare("
        UPDATE intelligence_files
        SET intelligence_score = ?,
            complexity_score = ?,
            quality_score = ?,
            business_value_score = ?,
            last_analyzed = NOW()
        WHERE file_id = ?
    ");
    
    $update->execute([
        $scores['intelligence'],
        $scores['complexity'],
        $scores['quality'],
        $scores['business_value'],
        $file['file_id']
    ]);
    
    $scored++;
    if ($scored % 1000 == 0) {
        echo date('Y-m-d H:i:s') . "    Scored $scored files...\n";
    }
}

echo date('Y-m-d H:i:s') . " ✅ Scored $scored files!\n";

// Show results
$stmt = $db->prepare("
    SELECT 
        file_type,
        COUNT(*) as count,
        AVG(intelligence_score) as avg_intel,
        AVG(complexity_score) as avg_complex,
        AVG(quality_score) as avg_quality,
        AVG(business_value_score) as avg_business
    FROM intelligence_files
    WHERE server_id = ?
    AND intelligence_score > 0
    GROUP BY file_type
");
$stmt->execute([$server]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n📊 INTELLIGENCE ANALYSIS:\n";
foreach ($results as $row) {
    printf("   %s: %d files | Intel: %.1f | Complex: %.1f | Quality: %.1f | Business: %.1f\n",
        $row['file_type'],
        $row['count'],
        $row['avg_intel'],
        $row['avg_complex'],
        $row['avg_quality'],
        $row['avg_business']
    );
}

function calculateScores($file) {
    $content = $file['file_content'];
    $type = $file['file_type'];
    $intel_data = json_decode($file['intelligence_data'], true) ?? [];
    
    $scores = ['intelligence' => 0, 'complexity' => 0, 'quality' => 0, 'business_value' => 0];
    
    $lines = substr_count($content, "\n") + 1;
    $size = strlen($content);
    
    if ($type === 'code_intelligence') {
        $func_count = $intel_data['function_count'] ?? 0;
        
        // Complexity
        $scores['complexity'] = min(100, (
            ($func_count * 2) +
            ($lines / 10) +
            (substr_count($content, 'if (') * 0.5) +
            (substr_count($content, 'foreach') * 0.7)
        ));
        
        // Quality
        $has_docblocks = substr_count($content, '/**') > 0;
        $has_type_hints = preg_match('/function\s+\w+\s*\([^)]*:\s*\w+/', $content);
        $has_error_handling = substr_count($content, 'try {') > 0;
        
        $scores['quality'] = (
            ($has_docblocks ? 35 : 0) +
            ($has_type_hints ? 35 : 0) +
            ($has_error_handling ? 30 : 0)
        );
        
        // Intelligence
        $is_class = preg_match('/class\s+\w+/', $content);
        $uses_interfaces = preg_match('/implements\s+\w+/', $content);
        $has_namespace = preg_match('/namespace\s+\w+/', $content);
        
        $scores['intelligence'] = (
            ($is_class ? 40 : 0) +
            ($uses_interfaces ? 30 : 0) +
            ($has_namespace ? 30 : 0)
        );
        
        // Business Value
        $is_api = strpos($content, 'API') !== false;
        $is_controller = strpos($content, 'Controller') !== false;
        $has_db = substr_count($content, 'PDO') > 0 || substr_count($content, 'query') > 0;
        
        $scores['business_value'] = (
            ($is_api ? 40 : 0) +
            ($is_controller ? 30 : 0) +
            ($has_db ? 30 : 0)
        );
        
    } elseif ($type === 'documentation') {
        $has_headers = preg_match_all('/^#+\s+/m', $content) > 0;
        $has_code = substr_count($content, '```') >= 2;
        $has_links = substr_count($content, '](') > 0;
        
        $scores['quality'] = (
            ($has_headers ? 40 : 0) +
            ($has_code ? 40 : 0) +
            ($has_links ? 20 : 0)
        );
        
        $scores['complexity'] = min(100, ($lines / 20) + (preg_match_all('/^#+\s+/m', $content) * 5));
        
        $scores['intelligence'] = min(100, (
            ($lines > 100 ? 40 : $lines / 2.5) +
            ($has_code ? 40 : 0) +
            (substr_count($content, 'example') > 0 ? 20 : 0)
        ));
        
        $is_spec = preg_match('/specification|requirement|architecture/i', $content);
        $is_guide = preg_match('/guide|tutorial|howto/i', $content);
        
        $scores['business_value'] = (
            ($is_spec ? 60 : 0) +
            ($is_guide ? 40 : 0)
        );
        
    } elseif ($type === 'business_intelligence') {
        if (strpos($content, '{') === 0 || strpos($content, '[') === 0) {
            $data = json_decode($content, true);
            if ($data) {
                $keys = count($data, COUNT_RECURSIVE);
                $scores['complexity'] = min(100, $keys / 5);
                $scores['intelligence'] = min(100, $keys / 2);
                $scores['quality'] = 100;
                
                $has_pricing = isset($data['price']) || isset($data['cost']);
                $has_inventory = isset($data['stock']) || isset($data['quantity']);
                
                $scores['business_value'] = (
                    ($has_pricing ? 50 : 0) +
                    ($has_inventory ? 50 : 0)
                );
            }
        }
    }
    
    // Normalize
    foreach ($scores as $key => $value) {
        $scores[$key] = max(0, min(100, round($value, 2)));
    }
    
    return $scores;
}
