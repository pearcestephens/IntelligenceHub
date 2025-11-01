<?php
/**
 * Check Scanner Errors - Extract detailed error messages
 */

// Run scanner and capture full output
exec('php api_neural_scanner.php --server=jcepnzzkmj --full 2>&1', $output);

echo "=== SCANNER OUTPUT ANALYSIS ===\n\n";

// Look for error patterns
$errors = [];
$db_errors = [];
$file_errors = [];

foreach ($output as $line) {
    if (stripos($line, 'error') !== false) {
        $errors[] = $line;
        
        if (stripos($line, 'Database') !== false) {
            $db_errors[] = $line;
        }
        if (stripos($line, 'Failed to read') !== false || stripos($line, 'not found') !== false) {
            $file_errors[] = $line;
        }
    }
}

echo "Total error lines: " . count($errors) . "\n\n";

if (!empty($db_errors)) {
    echo "DATABASE ERRORS (" . count($db_errors) . "):\n";
    foreach (array_slice($db_errors, 0, 10) as $err) {
        echo "  $err\n";
    }
    echo "\n";
}

if (!empty($file_errors)) {
    echo "FILE ERRORS (" . count($file_errors) . "):\n";
    foreach (array_slice($file_errors, 0, 10) as $err) {
        echo "  $err\n";
    }
    echo "\n";
}

// Check actual database records
echo "=== DATABASE ANALYSIS ===\n\n";

$db = new PDO('mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4', 'hdgwrzntwa', 'bFUdRjh4Jx');

// Check a specific PHP file
$result = $db->query("
    SELECT file_name, file_path, file_type, file_size, 
           LENGTH(file_content) as content_len, 
           LENGTH(intelligence_data) as data_len,
           extracted_at
    FROM intelligence_files 
    WHERE server_id = 'jcepnzzkmj' 
    AND file_name LIKE '%.php' 
    AND file_type = 'code'
    ORDER BY extracted_at DESC 
    LIMIT 5
");

echo "Latest 5 PHP code intelligence files:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo sprintf("  - %s (size: %d, content: %s, data: %s) @ %s\n",
        $row['file_name'],
        $row['file_size'],
        $row['content_len'] ?: 'NULL',
        $row['data_len'] ?: 'NULL',
        $row['extracted_at']
    );
}

echo "\n";

// Check what file_type 'code' actually maps to
$result = $db->query("
    SELECT file_type, COUNT(*) as count 
    FROM intelligence_files 
    WHERE server_id = 'jcepnzzkmj'
    GROUP BY file_type
");

echo "All file_type values:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "  - {$row['file_type']}: {$row['count']}\n";
}
