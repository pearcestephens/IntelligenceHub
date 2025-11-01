<?php
declare(strict_types=1);

/**
 * ready-check.php
 * Verifies environment, DB/Redis connectivity, and core config before go-live.
 *
 * Usage:
 *   php ops/ready-check.php
 */

require_once __DIR__ . '/../src/bootstrap.php';

use App\Config;
use App\DB;
use App\RedisClient;

function pass(string $msg): void { echo "[PASS] {$msg}\n"; }
function fail(string $msg): void { echo "[FAIL] {$msg}\n"; }

$errors = 0;

// Env sanity
try {
    Config::initialize();
    pass('.env loaded and configuration initialized');
} catch (\Throwable $e) {
    $errors++; fail('Config initialization: ' . $e->getMessage());
}

// OpenAI key presence (format check only)
try {
    Config::validateOpenAI();
    pass('OPENAI_API_KEY looks valid');
} catch (\Throwable $e) {
    $errors++; fail('OpenAI config: ' . $e->getMessage());
}

// Database connectivity
try {
    Config::validateDatabase();
    $pdo = DB::connection();
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();
    pass('Database connection OK');
} catch (\Throwable $e) {
    $errors++; fail('Database connectivity: ' . $e->getMessage());
}

// Redis connectivity
try {
    // Static client methods manage connection internally
    RedisClient::set('ready-check:ping', 'pong', 5);
    $ok = RedisClient::get('ready-check:ping') === 'pong';
    if ($ok) pass('Redis connection OK'); else throw new \RuntimeException('Redis ping failed');
} catch (\Throwable $e) {
    $errors++; fail('Redis connectivity: ' . $e->getMessage());
}

// Public protection checks
$rootHtaccess = realpath(__DIR__ . '/../.htaccess');
if ($rootHtaccess && is_readable($rootHtaccess)) {
    $content = file_get_contents($rootHtaccess) ?: '';
    if (str_contains($content, 'FilesMatch') && str_contains($content, 'RewriteRule')) {
        pass('Root .htaccess protections present (hidden files + internal dirs)');
    } else {
        $errors++; fail('Root .htaccess may be missing protections');
    }
} else {
    $errors++; fail('Root .htaccess not found/readable');
}

// Output summary
echo "\n" . ($errors === 0 ? 'READY ✅' : ("NOT READY ❌ - issues: {$errors}")) . "\n";
exit($errors === 0 ? 0 : 1);
