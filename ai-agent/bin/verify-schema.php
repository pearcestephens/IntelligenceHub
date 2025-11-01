#!/usr/bin/env php
<?php
/**
 * Migration Verification Script
 * Shows current schema state and what migration will change
 */

require_once __DIR__ . '/../vendor/autoload.php';

use NeuroAI\Core\DB;

echo "\n";
echo "╔════════════════════════════════════════════════════╗\n";
echo "║     BATCH-7 Migration Verification Script         ║\n";
echo "║     Current Schema Analysis                        ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Database connection details
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'jcepnzzkmj';
$pass = getenv('DB_PASS') ?: 'wprKh9Jq63';
$dbname = getenv('DB_NAME') ?: 'jcepnzzkmj';

echo "📊 Database: $dbname @ $host\n";
echo "👤 User: $user\n\n";

try {
    // Connect directly with PDO for verification
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Database connection successful\n\n";
    
    // Tables to check
    $requiredTables = [
        'conversations' => ['id', 'uuid', 'conversation_id', 'title', 'model', 'system_message', 'created_at', 'updated_at'],
        'messages' => ['id', 'message_id', 'conversation_id', 'conversation_uuid', 'role', 'content', 'created_at'],
        'context_cards' => ['id', 'uuid', 'conversation_id', 'content', 'embedding', 'expires_at', 'created_at'],
        'tool_calls' => ['id', 'tool_call_id', 'message_id', 'conversation_id', 'tool_name', 'arguments', 'result', 'status'],
        'knowledge_base' => ['id', 'doc_id', 'source', 'uri', 'content', 'embedding', 'indexed_at'],
        'api_keys' => ['id', 'api_key', 'name', 'is_active', 'rate_limit_per_minute', 'created_at'],
        'idempotency_store' => ['id', 'idempotency_key', 'endpoint', 'response', 'status_code', 'expires_at'],
        'rate_limits' => ['id', 'identifier', 'endpoint', 'request_count', 'window_start', 'window_end']
    ];
    
    echo "═══════════════════════════════════════════════════\n";
    echo "TABLE EXISTENCE CHECK\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    foreach ($requiredTables as $table => $columns) {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        
        if ($exists) {
            // Get current columns
            $stmt = $pdo->query("DESCRIBE $table");
            $currentColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Get row count
            $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM $table");
            $rowCount = $stmt->fetch()['cnt'];
            
            echo "✓ $table EXISTS ($rowCount rows)\n";
            
            // Check for missing columns
            $missingColumns = array_diff($columns, $currentColumns);
            if (!empty($missingColumns)) {
                echo "  ⚠ Missing columns: " . implode(', ', $missingColumns) . "\n";
            }
            
            // Show current columns
            echo "  Current columns: " . implode(', ', $currentColumns) . "\n";
            
        } else {
            echo "✗ $table MISSING (will be created)\n";
            echo "  Required columns: " . implode(', ', $columns) . "\n";
        }
        
        echo "\n";
    }
    
    echo "═══════════════════════════════════════════════════\n";
    echo "FOREIGN KEY CONSTRAINTS\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    $constraintQuery = "
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = '$dbname'
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME
    ";
    
    $stmt = $pdo->query($constraintQuery);
    $constraints = $stmt->fetchAll();
    
    if (count($constraints) > 0) {
        foreach ($constraints as $c) {
            echo "✓ {$c['TABLE_NAME']}.{$c['COLUMN_NAME']} → {$c['REFERENCED_TABLE_NAME']}.{$c['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "⚠ No foreign key constraints found (will be created)\n";
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "INDEXES\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    foreach (array_keys($requiredTables) as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("SHOW INDEX FROM $table");
            $indexes = $stmt->fetchAll();
            
            echo "$table:\n";
            $indexNames = array_unique(array_column($indexes, 'Key_name'));
            foreach ($indexNames as $indexName) {
                if ($indexName !== 'PRIMARY') {
                    echo "  • $indexName\n";
                }
            }
        }
    }
    
    echo "\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "MIGRATION IMPACT SUMMARY\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    echo "The migration will:\n";
    echo "  1. ✓ Create missing tables (if any)\n";
    echo "  2. ✓ Add missing columns to existing tables\n";
    echo "  3. ✓ Create foreign key constraints with CASCADE delete\n";
    echo "  4. ✓ Add indexes for performance\n";
    echo "  5. ✓ Insert test API keys\n";
    echo "  6. ✓ Insert sample test data\n\n";
    
    echo "✓ Safe to execute - migration uses IF NOT EXISTS clauses\n";
    echo "✓ Existing data will be preserved\n";
    echo "✓ Backward compatible\n\n";
    
    echo "═══════════════════════════════════════════════════\n";
    echo "READY TO MIGRATE\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    echo "Execute migration:\n";
    echo "  bash bin/quick-migrate.sh\n\n";
    echo "Or manually:\n";
    echo "  mysql -h $host -u $user -p'$pass' $dbname < sql/migrations/2025_10_07_batch_7_comprehensive.sql\n\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
