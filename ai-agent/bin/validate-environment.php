#!/usr/bin/env php
<?php
/**
 * Environment Validator & Setup Tool
 * Purpose: Validate all .env variables and fix common issues
 */

$projectRoot = dirname(__DIR__);
$envFile = $projectRoot . '/.env';
$envExampleFile = $projectRoot . '/.env.example';

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  🔧 AI AGENT ENVIRONMENT VALIDATOR\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

// Check if .env exists
if (!file_exists($envFile)) {
    echo "⚠️  No .env file found!\n\n";
    
    // Try to find parent .env
    $parentEnv = dirname(dirname(dirname($projectRoot))) . '/.env';
    if (file_exists($parentEnv)) {
        echo "✓ Found parent .env, copying...\n";
        copy($parentEnv, $envFile);
        echo "✅ Created .env from parent\n\n";
    } elseif (file_exists($envExampleFile)) {
        echo "✓ Found .env.example, copying...\n";
        copy($envExampleFile, $envFile);
        echo "✅ Created .env from example\n";
        echo "⚠️  Please edit .env and add your configuration!\n\n";
    } else {
        echo "❌ No .env.example found. Creating minimal .env...\n";
        $minimalEnv = <<<ENV
# Database Configuration
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=jcepnzzkmj
MYSQL_PASSWORD=wprKh9Jq63
MYSQL_DATABASE=jcepnzzkmj

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PREFIX=aiagent:
REDIS_PASSWORD=

# Application
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=info

# Analytics
ANALYTICS_IP_SALT=random_salt_change_me
NEURO_HMAC_SECRET=random_secret_change_me

# OpenAI (Optional)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4
ENV;
        file_put_contents($envFile, $minimalEnv);
        echo "✅ Created minimal .env\n\n";
    }
}

// Parse .env file
function parseEnvFile($file) {
    $vars = [];
    if (!file_exists($file)) {
        return $vars;
    }
    
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }
        
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            // Remove quotes
            $value = trim($value, '"\'');
            $vars[$key] = $value;
        }
    }
    return $vars;
}

// Load environment
$env = parseEnvFile($envFile);

echo "════════════════════════════════════════════════════════════════\n";
echo "  📋 ENVIRONMENT VALIDATION\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

// Required variables
$required = [
    'MYSQL_HOST' => 'Database host',
    'MYSQL_PORT' => 'Database port',
    'MYSQL_USER' => 'Database username',
    'MYSQL_PASSWORD' => 'Database password',
    'MYSQL_DATABASE' => 'Database name',
    'REDIS_HOST' => 'Redis host',
    'REDIS_PORT' => 'Redis port',
];

$missing = [];
$present = [];

foreach ($required as $key => $description) {
    if (empty($env[$key])) {
        $missing[] = [$key, $description];
        echo "❌ Missing: $key ($description)\n";
    } else {
        $present[] = $key;
        // Mask sensitive values
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'SECRET') !== false || strpos($key, 'KEY') !== false) {
            echo "✓ $key = ********\n";
        } else {
            echo "✓ $key = {$env[$key]}\n";
        }
    }
}

echo "\n";

if (!empty($missing)) {
    echo "⚠️  WARNING: " . count($missing) . " required variables missing!\n";
    echo "Please add them to $envFile\n\n";
} else {
    echo "✅ All required variables are set!\n\n";
}

// Optional but recommended
$optional = [
    'OPENAI_API_KEY' => 'OpenAI API key (for AI features)',
    'ANALYTICS_IP_SALT' => 'Analytics IP hashing salt',
    'NEURO_HMAC_SECRET' => 'HMAC secret for security',
    'APP_ENV' => 'Application environment',
    'LOG_LEVEL' => 'Logging level',
];

echo "Optional Configuration:\n";
foreach ($optional as $key => $description) {
    if (empty($env[$key])) {
        echo "  ⚠️  $key not set ($description)\n";
    } else {
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'SECRET') !== false || strpos($key, 'KEY') !== false) {
            echo "  ✓ $key = ********\n";
        } else {
            echo "  ✓ $key = {$env[$key]}\n";
        }
    }
}

echo "\n";

// Test database connection
echo "════════════════════════════════════════════════════════════════\n";
echo "  🗄️  DATABASE CONNECTION TEST\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

if (!empty($env['MYSQL_HOST']) && !empty($env['MYSQL_USER']) && !empty($env['MYSQL_PASSWORD'])) {
    try {
        $dsn = "mysql:host={$env['MYSQL_HOST']};port={$env['MYSQL_PORT']};dbname={$env['MYSQL_DATABASE']};charset=utf8mb4";
        $pdo = new PDO($dsn, $env['MYSQL_USER'], $env['MYSQL_PASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "✅ Database connection successful!\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "✓ Found " . count($tables) . " tables\n";
        
        // Check AI agent tables
        $aiTables = ['importance_scores', 'metrics_response_times', 'metrics_tool_execution', 
                     'metrics_token_usage', 'metrics_cache_performance', 'metrics_errors',
                     'conversation_clusters', 'conversation_tags', 'compressed_messages_archive'];
        $foundAiTables = array_intersect($aiTables, $tables);
        echo "✓ AI agent tables: " . count($foundAiTables) . "/9\n";
        
        if (count($foundAiTables) < 9) {
            echo "\n⚠️  Some AI agent tables are missing. Run:\n";
            echo "   mysql -h {$env['MYSQL_HOST']} -u {$env['MYSQL_USER']} -p{$env['MYSQL_PASSWORD']} {$env['MYSQL_DATABASE']} < migrations/003_analytics_and_memory_fixed.sql\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ Database connection failed!\n";
        echo "   Error: " . $e->getMessage() . "\n";
        echo "   Please check your MYSQL_* variables in .env\n";
    }
} else {
    echo "⚠️  Skipping database test (credentials not set)\n";
}

echo "\n";

// Test Redis connection
echo "════════════════════════════════════════════════════════════════\n";
echo "  📦 REDIS CONNECTION TEST\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

if (!empty($env['REDIS_HOST']) && !empty($env['REDIS_PORT'])) {
    try {
        $redis = new Redis();
        $connected = $redis->connect($env['REDIS_HOST'], (int)$env['REDIS_PORT'], 2);
        
        if ($connected) {
            $redis->ping();
            echo "✅ Redis connection successful!\n";
            
            $dbsize = $redis->dbSize();
            echo "✓ Redis keys: $dbsize\n";
            
            // Test operations
            $testKey = 'aiagent:test:' . time();
            $redis->set($testKey, 'test', 5);
            $value = $redis->get($testKey);
            if ($value === 'test') {
                echo "✓ Redis read/write working\n";
                $redis->del($testKey);
            }
        } else {
            echo "❌ Redis connection failed!\n";
        }
    } catch (Exception $e) {
        echo "❌ Redis connection failed!\n";
        echo "   Error: " . $e->getMessage() . "\n";
        echo "   Please check your REDIS_* variables in .env\n";
    }
} else {
    echo "⚠️  Skipping Redis test (connection info not set)\n";
}

echo "\n";

// File permissions
echo "════════════════════════════════════════════════════════════════\n";
echo "  🔐 FILE PERMISSIONS\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

$logsDir = $projectRoot . '/logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
    echo "✓ Created logs/ directory\n";
}

chmod($envFile, 0600);
echo "✓ .env permissions: 0600 (owner read/write only)\n";

chmod($logsDir, 0755);
echo "✓ logs/ permissions: 0755\n";

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  ✅ VALIDATION COMPLETE\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

$totalIssues = count($missing);
if ($totalIssues === 0) {
    echo "🎉 Environment is properly configured!\n";
    echo "\nNext steps:\n";
    echo "  1. Run inline tests: php bin/run-inline-tests.php\n";
    echo "  2. Run PHPUnit tests: php vendor/bin/phpunit --testdox\n";
    echo "  3. Deploy to production\n";
} else {
    echo "⚠️  Found $totalIssues issue(s) that need attention.\n";
    echo "Please review the output above and update your .env file.\n";
}

echo "\n";
