<?php
/**
 * Database Schema Investigation
 * 
 * Find all intelligence-related tables and their schemas
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

echo "🔍 Investigating Database Schema\n";
echo str_repeat('=', 60) . "\n\n";

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Connected to database: " . DB_NAME . "\n\n";
    
    // Show all tables
    echo "📋 All tables in database:\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n" . str_repeat('-', 60) . "\n\n";
    
    // Show intelligence_files schema
    if (in_array('intelligence_files', $tables)) {
        echo "📊 intelligence_files schema:\n";
        $stmt = $db->query("DESCRIBE intelligence_files");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo sprintf("  %-25s %-20s %-8s %-8s %s\n", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key'],
                $row['Default'] ?: '');
        }
        
        // Count records
        $stmt = $db->query("SELECT COUNT(*) FROM intelligence_files");
        $count = $stmt->fetchColumn();
        echo "\n  Total records: $count\n";
        
        if ($count > 0) {
            // Show sample record
            $stmt = $db->query("SELECT * FROM intelligence_files LIMIT 1");
            $sample = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "\n  Sample record columns:\n";
            foreach (array_keys($sample) as $col) {
                echo "    - $col\n";
            }
        }
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
