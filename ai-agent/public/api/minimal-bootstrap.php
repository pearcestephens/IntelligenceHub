<?php
/**
 * Minimal API Bootstrap - Just the essentials for Gate 4 API testing
 */

declare(strict_types=1);

// Load environment
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'<>");
            if (!empty($key)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    // Compatibility mapping: support MYSQL_* variables by setting DB_* if missing
    $map = [
        'DB_HOST' => ['MYSQL_HOST'],
        'DB_PORT' => ['MYSQL_PORT'],
        'DB_NAME' => ['MYSQL_DATABASE', 'DB_DATABASE'],
        'DB_USER' => ['DB_USERNAME', 'MYSQL_USER'],
        'DB_PASS' => ['DB_PASSWORD', 'MYSQL_PASSWORD']
    ];
    foreach ($map as $target => $sources) {
        if (!isset($_ENV[$target]) || $_ENV[$target] === '') {
            foreach ($sources as $src) {
                if (isset($_ENV[$src]) && $_ENV[$src] !== '') {
                    $_ENV[$target] = $_ENV[$src];
                    putenv($target . '=' . $_ENV[$src]);
                    break;
                }
            }
        }
    }
}

// Database connection function
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_NAME'] ?? 'jcepnzzkmj';
        $user = $_ENV['DB_USER'] ?? 'jcepnzzkmj';
        $pass = $_ENV['DB_PASS'] ?? '';
        
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

// API Response helpers
function apiResponse($data = null, $success = true, $httpCode = 200) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Idempotency-Key');
    
    http_response_code($httpCode);
    
    $response = [
        'success' => $success,
        'data' => $data,
        'meta' => [
            'timestamp' => date('c'),
            'request_id' => bin2hex(random_bytes(4))
        ]
    ];
    
    if (!$success && $data) {
        $response['error'] = $data;
        unset($response['data']);
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

function apiError($message, $type = 'api_error', $httpCode = 400, $details = null) {
    $error = [
        'message' => $message,
        'type' => $type
    ];
    if ($details) $error['details'] = $details;
    
    apiResponse($error, false, $httpCode);
}

// Simple auth check
function checkAuth() {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
        apiError('Authentication required', 'auth_required', 401);
    }
    
    $apiKey = $matches[1];
    $validKeys = explode(',', $_ENV['API_KEYS'] ?? '');
    $validKeys = array_map('trim', $validKeys);
    
    if (!in_array($apiKey, $validKeys)) {
        apiError('Invalid API key', 'auth_invalid', 401);
    }
    
    return $apiKey;
}

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Idempotency-Key');
    http_response_code(204);
    exit;
}

// UUID generator
function generateUuid() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant bits
    return sprintf(
        '%08s-%04s-%04s-%04s-%12s',
        bin2hex(substr($data, 0, 4)),
        bin2hex(substr($data, 4, 2)),
        bin2hex(substr($data, 6, 2)),
        bin2hex(substr($data, 8, 2)),
        bin2hex(substr($data, 10, 6))
    );
}

// Get JSON body
function getJsonBody() {
    $json = file_get_contents('php://input');
    if (empty($json)) return [];
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        apiError('Invalid JSON in request body', 'json_error', 400);
    }
    
    return $data;
}
?>