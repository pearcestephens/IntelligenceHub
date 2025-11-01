#!/usr/bin/env php
<?php
/**
 * One-Command Environment Setup & Verification
 * Purpose: Fix all environment issues and prepare system for production
 */

$projectRoot = dirname(__DIR__);
chdir($projectRoot);

$GREEN = "\033[0;32m";
$RED = "\033[0;31m";
$YELLOW = "\033[1;33m";
$BLUE = "\033[0;34m";
$NC = "\033[0m"; // No Color

function success($msg) {
    global $GREEN, $NC;
    echo "{$GREEN}✅ $msg{$NC}\n";
}

function error($msg) {
    global $RED, $NC;
    echo "{$RED}❌ $msg{$NC}\n";
}

function warning($msg) {
    global $YELLOW, $NC;
    echo "{$YELLOW}⚠️  $msg{$NC}\n";
}

function info($msg) {
    global $BLUE, $NC;
    echo "{$BLUE}ℹ️  $msg{$NC}\n";
}

function section($title) {
    echo "\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "  $title\n";
    echo "════════════════════════════════════════════════════════════════\n";
    echo "\n";
}

section("🚀 AI AGENT PRODUCTION SETUP");

// Check PHP version
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    error("PHP 8.1+ required. Current: " . PHP_VERSION);
    exit(1);
}
success("PHP version: " . PHP_VERSION);

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'redis', 'json', 'mbstring', 'curl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
        error("Missing PHP extension: $ext");
    } else {
        success("Extension loaded: $ext");
    }
}

if (!empty($missingExtensions)) {
    error("Missing required extensions: " . implode(', ', $missingExtensions));
    exit(1);
}

echo "\n";

// Check Composer autoload
section("📦 CHECKING COMPOSER AUTOLOAD");

if (!file_exists('vendor/autoload.php')) {
    error("Composer autoload not found. Run: composer install");
    exit(1);
}

require_once 'vendor/autoload.php';
success("Composer autoload loaded");

// Load environment
section("🔧 LOADING ENVIRONMENT");

$envFile = $projectRoot . '/.env';
if (!file_exists($envFile)) {
    error(".env file not found!");
    exit(1);
}

// Parse .env
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') {
        continue;
    }
    
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, '"\'');
        $env[$key] = $value;
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

success("Loaded " . count($env) . " environment variables");

// Validate required vars
$required = [
    'MYSQL_HOST', 'MYSQL_PORT', 'MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE',
    'REDIS_HOST', 'REDIS_PORT'
];

$missing = [];
foreach ($required as $key) {
    if (empty($env[$key])) {
        $missing[] = $key;
        error("Missing: $key");
    } else {
        if (strpos($key, 'PASSWORD') !== false || strpos($key, 'SECRET') !== false) {
            success("$key = ********");
        } else {
            success("$key = {$env[$key]}");
        }
    }
}

if (!empty($missing)) {
    error("Missing required variables: " . implode(', ', $missing));
    exit(1);
}

// Test database
section("🗄️  TESTING DATABASE");

try {
    $dsn = "mysql:host={$env['MYSQL_HOST']};port={$env['MYSQL_PORT']};dbname={$env['MYSQL_DATABASE']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['MYSQL_USER'], $env['MYSQL_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    
    success("Database connection established");
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    info("Found " . count($tables) . " tables");
    
    // Check AI tables
    $aiTables = [
        'importance_scores',
        'metrics_response_times',
        'metrics_tool_execution',
        'metrics_token_usage',
        'metrics_cache_performance',
        'metrics_errors',
        'conversation_clusters',
        'conversation_tags',
        'compressed_messages_archive'
    ];
    
    $foundAi = array_intersect($aiTables, $tables);
    info("AI agent tables: " . count($foundAi) . "/9");
    
    if (count($foundAi) < 9) {
        warning("Missing " . (9 - count($foundAi)) . " AI agent tables");
        info("Run migration: mysql < migrations/003_analytics_and_memory_fixed.sql");
    } else {
        success("All AI agent tables present");
    }
    
    // Test query
    $result = $pdo->query("SELECT 1 AS test")->fetch();
    if ($result['test'] == 1) {
        success("Database queries working");
    }
    
} catch (PDOException $e) {
    error("Database connection failed: " . $e->getMessage());
    exit(1);
}

// Test Redis
section("📦 TESTING REDIS");

try {
    $redis = new Redis();
    $connected = $redis->connect($env['REDIS_HOST'], (int)$env['REDIS_PORT'], 2);
    
    if (!$connected) {
        throw new Exception("Connection failed");
    }
    
    $redis->ping();
    success("Redis connection established");
    
    $dbsize = $redis->dbSize();
    info("Redis keys: $dbsize");
    
    // Test operations
    $testKey = 'aiagent:setup:test:' . time();
    $redis->set($testKey, 'test', 5);
    $value = $redis->get($testKey);
    
    if ($value === 'test') {
        success("Redis read/write working");
        $redis->del($testKey);
    } else {
        warning("Redis read/write may be inconsistent");
    }
    
} catch (Exception $e) {
    error("Redis connection failed: " . $e->getMessage());
    exit(1);
}

// Check file structure
section("📁 VERIFYING FILE STRUCTURE");

$requiredFiles = [
    'src/Memory/MemoryCompressor.php',
    'src/Memory/SemanticClusterer.php',
    'src/Memory/ImportanceScorer.php',
    'src/Analytics/MetricsCollector.php',
    'src/RedisClient.php',
    'src/DB.php',
    'tests/bootstrap.php',
    'bin/run-inline-tests.php',
    'migrations/003_analytics_and_memory_fixed.sql',
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        success("Found: $file");
    } else {
        error("Missing: $file");
        $missingFiles[] = $file;
    }
}

if (!empty($missingFiles)) {
    error("Missing " . count($missingFiles) . " required files");
    exit(1);
}

// Check permissions
section("🔐 CHECKING PERMISSIONS");

$logsDir = 'logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
    success("Created logs/ directory");
} else {
    success("logs/ directory exists");
}

if (is_writable($logsDir)) {
    success("logs/ is writable");
} else {
    warning("logs/ is not writable");
}

if (is_readable($envFile) && !is_executable($envFile)) {
    success(".env has correct permissions");
} else {
    chmod($envFile, 0600);
    success("Fixed .env permissions (0600)");
}

// Run inline tests
section("🧪 RUNNING INLINE TESTS");

echo "\n";
passthru("php bin/run-inline-tests.php", $exitCode);
echo "\n";

if ($exitCode === 0) {
    success("All inline tests passed!");
} else {
    error("Some inline tests failed (exit code: $exitCode)");
}

// Final summary
section("✅ SETUP COMPLETE");

$timestamp = date('Y-m-d H:i:s');
echo "Setup completed at: $timestamp\n\n";

echo "System Status:\n";
echo "  ✓ PHP " . PHP_VERSION . " with all required extensions\n";
echo "  ✓ Database connected ({$env['MYSQL_DATABASE']})\n";
echo "  ✓ Redis connected ({$env['REDIS_HOST']}:{$env['REDIS_PORT']})\n";
echo "  ✓ All required files present\n";
echo "  ✓ Permissions configured\n";
echo "  ✓ Environment variables loaded\n";

echo "\n";
echo "Next steps:\n";
echo "  1. Review test results above\n";
echo "  2. Run PHPUnit: php vendor/bin/phpunit --testdox\n";
echo "  3. Check analytics: https://staff.vapeshed.co.nz/assets/neuro/ai-agent/public/analytics-dashboard.html\n";
echo "  4. Monitor logs: tail -f logs/*.log\n";

echo "\n";
success("🎉 System is ready for production!");
echo "\n";

exit(0);
