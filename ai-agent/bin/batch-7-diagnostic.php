#!/usr/bin/env php
<?php
/**
 * BATCH-7 Quick Diagnostic
 * Identifies the top issues preventing tests from passing
 * 
 * @author Pearce Stephens - Ecigdis Limited
 */

declare(strict_types=1);

echo "=== BATCH-7 Quick Diagnostic ===\n\n";

// Load environment
require __DIR__ . '/../vendor/autoload.php';
if (file_exists(__DIR__ . '/../.env.test')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.test');
    $dotenv->load();
}

$issues = [];
$quickFixes = [];

// Check 1: Database Connection & Tables
echo "1. Checking Database Schema...\n";
try {
    $pdo = new PDO(
        'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') . ';dbname=' . ($_ENV['DB_NAME'] ?? 'jcepnzzkmj'),
        $_ENV['DB_USER'] ?? 'jcepnzzkmj',
        $_ENV['DB_PASS'] ?? ''
    );
    
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $requiredTables = ['conversations', 'messages', 'context_cards', 'knowledge_base', 'tool_calls'];
    $missingTables = array_diff($requiredTables, $tables);
    
    echo "   Found tables: " . implode(', ', $tables) . "\n";
    
    if (!empty($missingTables)) {
        echo "   ✗ MISSING TABLES: " . implode(', ', $missingTables) . "\n";
        $issues[] = "Missing database tables: " . implode(', ', $missingTables);
        $quickFixes[] = "Create schema: mysql < sql/schema.sql";
    } else {
        echo "   ✓ All required tables exist\n";
        
        // Check columns
        if (in_array('conversations', $tables)) {
            $cols = $pdo->query('DESCRIBE conversations')->fetchAll(PDO::FETCH_COLUMN);
            $requiredCols = ['uuid', 'title', 'created_at'];
            $missingCols = array_diff($requiredCols, $cols);
            
            if (!empty($missingCols)) {
                echo "   ✗ conversations missing columns: " . implode(', ', $missingCols) . "\n";
                $issues[] = "conversations table incomplete";
            } else {
                echo "   ✓ conversations table looks good\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    $issues[] = "Cannot connect to database";
    $quickFixes[] = "Check .env.test database credentials";
}

echo "\n";

// Check 2: API Endpoints
echo "2. Checking API Endpoints...\n";
$apiFiles = [
    'public/agent/api/v1/conversations.php' => '/agent/api/v1/conversations',
    'public/agent/api/v1/messages.php' => '/agent/api/v1/messages',
    'public/agent/api/v1/tool-calls.php' => '/agent/api/v1/tool-calls',
];

foreach ($apiFiles as $file => $route) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        echo "   ✓ {$route} - file exists\n";
    } else {
        echo "   ✗ {$route} - MISSING FILE\n";
        $issues[] = "API endpoint missing: {$file}";
        $quickFixes[] = "Create API endpoint: {$file}";
    }
}

echo "\n";

// Check 3: Core Classes
echo "3. Checking Core Classes...\n";
$coreClasses = [
    'App\\Agent',
    'App\\ConversationManager',
    'App\\DB',
    'App\\RedisClient',
    'App\\ToolExecutor',
    'App\\Knowledge\\KnowledgeBase',
];

foreach ($coreClasses as $class) {
    if (class_exists($class)) {
        echo "   ✓ {$class}\n";
    } else {
        echo "   ✗ {$class} - NOT FOUND\n";
        $issues[] = "Core class missing: {$class}";
    }
}

echo "\n";

// Check 4: Test Execution Sample
echo "4. Running Sample Integration Test...\n";
$output = [];
$exitCode = 0;
exec('cd ' . dirname(__DIR__) . ' && php vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php --stop-on-failure 2>&1 | head -30', $output, $exitCode);

$testOutput = implode("\n", $output);
if (strpos($testOutput, 'OK') !== false) {
    echo "   ✓ Database integration tests passing\n";
} elseif (strpos($testOutput, 'Table') !== false && strpos($testOutput, 'doesn\'t exist') !== false) {
    echo "   ✗ Tests failing due to missing tables\n";
    if (!in_array("Database schema incomplete", $issues)) {
        $issues[] = "Database schema incomplete";
    }
} elseif (strpos($testOutput, 'Unknown column') !== false) {
    echo "   ✗ Tests failing due to missing columns\n";
    preg_match("/Unknown column '([^']+)'/", $testOutput, $matches);
    if ($matches) {
        $issues[] = "Missing column: {$matches[1]}";
    }
} elseif (strpos($testOutput, '404') !== false || strpos($testOutput, 'Not Found') !== false) {
    echo "   ✗ Tests failing due to 404 errors (routing/endpoints)\n";
    if (!in_array("API endpoints not responding", $issues)) {
        $issues[] = "API endpoints not responding";
        $quickFixes[] = "Check Apache/Nginx configuration and .htaccess";
    }
} else {
    echo "   ⚠ Tests ran but results unclear\n";
    echo "   First error:\n";
    foreach (array_slice($output, 0, 5) as $line) {
        echo "     " . $line . "\n";
    }
}

echo "\n";

// Check 5: Redis Connection
echo "5. Checking Redis Connection...\n";
try {
    $redis = new Redis();
    $redis->connect($_ENV['REDIS_HOST'] ?? '127.0.0.1', (int)($_ENV['REDIS_PORT'] ?? 6379));
    $redis->ping();
    echo "   ✓ Redis connected\n";
} catch (Exception $e) {
    echo "   ⚠ Redis not available: " . $e->getMessage() . "\n";
    echo "   (Non-critical - tests can run without Redis)\n";
}

echo "\n";

// Summary
echo str_repeat("=", 70) . "\n";
echo "DIAGNOSTIC SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

if (empty($issues)) {
    echo "✓ No critical issues found!\n\n";
    echo "Your system appears ready. Tests may be failing due to:\n";
    echo "  • Minor configuration issues\n";
    echo "  • Features not fully implemented\n";
    echo "  • Test expectations vs actual implementation mismatch\n\n";
    echo "Recommended: Start with BATCH-7 Phase 2 (API fixes)\n";
} else {
    echo "Found " . count($issues) . " issue(s):\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". " . $issue . "\n";
    }
    
    if (!empty($quickFixes)) {
        echo "\nQuick Fixes:\n";
        foreach ($quickFixes as $i => $fix) {
            echo ($i + 1) . ". " . $fix . "\n";
        }
    }
    
    echo "\nRecommended: Start with BATCH-7 Phase 1 (Database schema)\n";
}

echo "\nNext Steps:\n";
echo "1. Review BATCH-7-PLANNING.md for full roadmap\n";
echo "2. Choose approach (Pragmatic, Full, or Test-Driven)\n";
echo "3. Start implementation with highest priority fixes\n";
echo "4. Re-run: php bin/run-all-tests.php --quick\n";

echo "\n" . str_repeat("=", 70) . "\n";
