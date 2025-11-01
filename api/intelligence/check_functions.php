<?php
/**
 * Check Function Extraction Results
 */

$db = new PDO('mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4', 'hdgwrzntwa', 'bFUdRjh4Jx');

// Check PHP files with functions
$result = $db->query("SELECT file_path, file_name, intelligence_data, LENGTH(file_content) as content_len 
FROM intelligence_files 
WHERE server_id = 'jcepnzzkmj' 
AND file_type = 'code' 
AND file_name LIKE '%.php' 
AND intelligence_data IS NOT NULL
ORDER BY file_id DESC 
LIMIT 5");

echo "Sample PHP files with extracted functions:\n\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "File: " . $row['file_name'] . "\n";
    echo "Path: " . $row['file_path'] . "\n";
    echo "Content: " . $row['content_len'] . " bytes\n";
    
    $data = json_decode($row['intelligence_data'], true);
    if (isset($data['functions']) && count($data['functions']) > 0) {
        echo "Functions extracted: " . count($data['functions']) . "\n";
        foreach (array_slice($data['functions'], 0, 5) as $func) {
            echo "  - " . $func['type'] . ": " . $func['name'] . "(" . $func['signature'] . ")\n";
        }
    } else {
        echo "No functions found in this file\n";
    }
    echo "\n";
}

// Statistics
echo "\n=== STATISTICS ===\n\n";

$stats = $db->query("
    SELECT 
        COUNT(*) as total_files,
        SUM(CASE WHEN intelligence_data LIKE '%functions%' THEN 1 ELSE 0 END) as files_with_functions,
        SUM(file_size) as total_size,
        SUM(LENGTH(file_content)) as total_content
    FROM intelligence_files 
    WHERE server_id = 'jcepnzzkmj'
")->fetch(PDO::FETCH_ASSOC);

echo "Total files stored: " . number_format($stats['total_files']) . "\n";
echo "Files with functions: " . number_format($stats['files_with_functions']) . "\n";
echo "Total original size: " . number_format($stats['total_size']) . " bytes\n";
echo "Total stored content: " . number_format($stats['total_content']) . " bytes\n";
echo "Storage efficiency: " . round(($stats['total_content'] / $stats['total_size']) * 100, 2) . "%\n";
