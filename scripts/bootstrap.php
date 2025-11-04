<?php
/**
 * Scanner Scripts Bootstrap
 *
 * Minimal bootstrap for scanner CLI scripts
 * Provides: .env loading, PDO connection factory
 *
 * @package Scripts
 * @author AI Development Assistant
 * @created 2025-11-02
 */

declare(strict_types=1);

// Load environment variables
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Remove quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Get Intelligence Hub database connection
 *
 * @return PDO Configured PDO instance for hdgwrzntwa database
 */
function getIntelligenceDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_NAME'] ?? 'hdgwrzntwa';
        $user = $_ENV['DB_USER'] ?? 'hdgwrzntwa';
        $pass = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}

/**
 * Get CIS database connection (staff.vapeshed.co.nz)
 *
 * @return PDO Configured PDO instance for jcepnzzkmj database
 */
function getCISDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = $_ENV['CIS_DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['CIS_DB_NAME'] ?? 'jcepnzzkmj';
        $user = $_ENV['CIS_DB_USER'] ?? 'jcepnzzkmj';
        $pass = $_ENV['CIS_DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $pdo;
}
