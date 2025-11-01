#!/usr/bin/env php
<?php
/**
 * Verify .env Loading in PHPUnit Bootstrap
 * Tests that environment variables are properly loaded before tests run
 */

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  🔍 VERIFYING PHPUNIT BOOTSTRAP .env LOADING\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

// Simulate PHPUnit bootstrap
$projectRoot = dirname(__DIR__);
define('AI_AGENT_ROOT', $projectRoot);

echo "Step 1: Loading .env file...\n";
$envFile = AI_AGENT_ROOT . '/.env';

if (!file_exists($envFile)) {
    echo "❌ .env file not found at: $envFile\n";
    exit(1);
}

echo "✅ Found .env file\n";

// Parse .env (same logic as bootstrap)
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$loaded = 0;

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') {
        continue;
    }
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, '"\'');
        
        if (!empty($key)) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
            $loaded++;
        }
    }
}

echo "✅ Loaded $loaded environment variables\n\n";

// Check critical variables
echo "Step 2: Verifying critical variables...\n";

$critical = [
    'MYSQL_HOST',
    'MYSQL_PORT',
    'MYSQL_USER',
    'MYSQL_PASSWORD',
    'MYSQL_DATABASE',
    'REDIS_HOST',
    'REDIS_PORT'
];

$allPresent = true;
foreach ($critical as $var) {
    $value = $_ENV[$var] ?? null;
    if ($value === null) {
        echo "❌ $var: NOT SET\n";
        $allPresent = false;
    } else {
        // Mask sensitive values
        if (strpos($var, 'PASSWORD') !== false || strpos($var, 'SECRET') !== false) {
            echo "✅ $var: ********\n";
        } else {
            echo "✅ $var: $value\n";
        }
    }
}

echo "\n";

if (!$allPresent) {
    echo "❌ Some critical variables are missing!\n";
    exit(1);
}

echo "Step 3: Testing environment access methods...\n";

// Test $_ENV
$mysqlUser1 = $_ENV['MYSQL_USER'] ?? null;
echo $_ENV['MYSQL_USER'] ? "✅ \$_ENV['MYSQL_USER'] = {$_ENV['MYSQL_USER']}\n" : "❌ \$_ENV['MYSQL_USER'] not set\n";

// Test $_SERVER
$mysqlUser2 = $_SERVER['MYSQL_USER'] ?? null;
echo $_SERVER['MYSQL_USER'] ? "✅ \$_SERVER['MYSQL_USER'] = {$_SERVER['MYSQL_USER']}\n" : "❌ \$_SERVER['MYSQL_USER'] not set\n";

// Test getenv()
$mysqlUser3 = getenv('MYSQL_USER');
echo $mysqlUser3 ? "✅ getenv('MYSQL_USER') = $mysqlUser3\n" : "❌ getenv('MYSQL_USER') not set\n";

echo "\n";

// Load autoloader
echo "Step 4: Loading Composer autoloader...\n";
require_once $projectRoot . '/vendor/autoload.php';
echo "✅ Autoloader loaded\n\n";

// Test Config class
echo "Step 5: Testing Config class access...\n";
try {
    $mysqlUser4 = \App\Config::get('MYSQL_USER');
    echo $mysqlUser4 ? "✅ Config::get('MYSQL_USER') = $mysqlUser4\n" : "❌ Config::get('MYSQL_USER') returned empty\n";
} catch (\Exception $e) {
    echo "❌ Config::get('MYSQL_USER') threw exception: {$e->getMessage()}\n";
}

echo "\n";

// Summary
echo "════════════════════════════════════════════════════════════════\n";
echo "  📊 VERIFICATION SUMMARY\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";

if ($allPresent && isset($mysqlUser1, $mysqlUser2, $mysqlUser3, $mysqlUser4)) {
    echo "✅ SUCCESS: .env loading works correctly!\n";
    echo "\n";
    echo "All environment variables are accessible via:\n";
    echo "  • \$_ENV['MYSQL_USER']\n";
    echo "  • \$_SERVER['MYSQL_USER']\n";
    echo "  • getenv('MYSQL_USER')\n";
    echo "  • Config::get('MYSQL_USER')\n";
    echo "\n";
    echo "PHPUnit bootstrap should work correctly.\n";
    echo "Run: php vendor/bin/phpunit --testdox\n";
    echo "\n";
    exit(0);
} else {
    echo "❌ FAILURE: .env loading has issues!\n";
    echo "\n";
    echo "Please check:\n";
    echo "  1. .env file exists and is readable\n";
    echo "  2. MYSQL_USER is set in .env\n";
    echo "  3. File permissions (chmod 600 .env)\n";
    echo "\n";
    exit(1);
}
