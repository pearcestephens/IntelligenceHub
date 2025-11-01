<?php
/**
 * Update Intelligence Files Schema
 * 
 * Adds file_content and metadata columns to store complete file contents
 * and extracted function information
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

echo "🔧 Updating Intelligence Files Schema\n";
echo str_repeat('=', 60) . "\n\n";

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Connected to database\n\n";
    
    // Check current schema
    echo "📋 Checking current schema...\n";
    $stmt = $db->query("DESCRIBE intelligence_files");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $columns) . "\n\n";
    
    // Add file_content column if not exists
    if (!in_array('file_content', $columns)) {
        echo "➕ Adding file_content column...\n";
        $db->exec("ALTER TABLE intelligence_files ADD COLUMN file_content LONGTEXT AFTER file_size");
        echo "✅ file_content column added\n";
    } else {
        echo "ℹ️  file_content column already exists\n";
    }
    
    // Add metadata column if not exists
    if (!in_array('metadata', $columns)) {
        echo "➕ Adding metadata column...\n";
        $db->exec("ALTER TABLE intelligence_files ADD COLUMN metadata TEXT AFTER file_content");
        echo "✅ metadata column added\n";
    } else {
        echo "ℹ️  metadata column already exists\n";
    }
    
    // Show updated schema
    echo "\n📋 Updated schema:\n";
    $stmt = $db->query("DESCRIBE intelligence_files");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['Field']} ({$row['Type']}) {$row['Null']} {$row['Key']} {$row['Default']}\n";
    }
    
    // Check table size
    echo "\n📊 Table statistics:\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM intelligence_files");
    $count = $stmt->fetchColumn();
    echo "  Total records: $count\n";
    
    if ($count > 0) {
        $stmt = $db->query("SELECT source_server, COUNT(*) as count FROM intelligence_files GROUP BY source_server");
        echo "  By server:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "    - {$row['source_server']}: {$row['count']}\n";
        }
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "✅ SCHEMA UPDATE COMPLETE\n";
    echo str_repeat('=', 60) . "\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
