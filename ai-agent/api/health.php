<?php
/**
 * AI Chat API - Health Check Endpoint
 *
 * Tests:
 * - Database connectivity
 * - OpenAI API configuration
 * - Required tables existence
 * - Agent configuration
 *
 * @version 1.0.0
 */

// Load environment variables from .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');$health = [
    'status' => 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => []
];

// Test database connection
try {
    $mysqli = new mysqli('127.0.0.1', 'jcepnzzkmj', 'wprKh9Jq63', 'jcepnzzkmj');

    if ($mysqli->connect_error) {
        throw new Exception($mysqli->connect_error);
    }

    $health['checks']['database'] = [
        'status' => 'ok',
        'message' => 'Connected to MySQL'
    ];

    // Check required tables
    $requiredTables = [
        'ai_kb_config',
        'ai_kb_queries',
        'ai_kb_conversations',
        'ai_kb_knowledge_items',
        'ai_kb_errors'
    ];

    $existingTables = [];
    $result = $mysqli->query("SHOW TABLES LIKE 'ai_kb_%'");
    while ($row = $result->fetch_array()) {
        $existingTables[] = $row[0];
    }

    $missingTables = array_diff($requiredTables, $existingTables);

    if (empty($missingTables)) {
        $health['checks']['tables'] = [
            'status' => 'ok',
            'message' => 'All required tables exist',
            'count' => count($existingTables)
        ];
    } else {
        $health['checks']['tables'] = [
            'status' => 'error',
            'message' => 'Missing tables: ' . implode(', ', $missingTables)
        ];
    }

    // Check for active agent
    $result = $mysqli->query("SELECT COUNT(*) as cnt FROM ai_kb_config WHERE is_active = 1");
    $row = $result->fetch_assoc();

    if ($row['cnt'] > 0) {
        $health['checks']['agent'] = [
            'status' => 'ok',
            'message' => 'Active agent configured'
        ];
    } else {
        $health['checks']['agent'] = [
            'status' => 'warning',
            'message' => 'No active agent configured'
        ];
    }

    $mysqli->close();

} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Check OpenAI configuration
$apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY') ?: '';
if (!empty($apiKey)) {
    $health['checks']['openai'] = [
        'status' => 'ok',
        'message' => 'API key configured',
        'key_preview' => substr($apiKey, 0, 10) . '...'
    ];
} else {
    $health['checks']['openai'] = [
        'status' => 'error',
        'message' => 'OPENAI_API_KEY not set'
    ];
}

// Check knowledge base
$kbPath = $_SERVER['DOCUMENT_ROOT'] . '/_kb/knowledge_base.json';
if (file_exists($kbPath)) {
    $kbData = json_decode(file_get_contents($kbPath), true);
    $health['checks']['knowledge_base'] = [
        'status' => 'ok',
        'message' => 'Knowledge base loaded',
        'facts' => count($kbData ?? [])
    ];
} else {
    $health['checks']['knowledge_base'] = [
        'status' => 'warning',
        'message' => 'Knowledge base file not found'
    ];
}

// Overall status
$hasErrors = false;
foreach ($health['checks'] as $check) {
    if ($check['status'] === 'error') {
        $hasErrors = true;
        break;
    }
}

$health['status'] = $hasErrors ? 'unhealthy' : 'healthy';
$health['ready'] = !$hasErrors && $health['checks']['openai']['status'] === 'ok';

http_response_code($hasErrors ? 503 : 200);
echo json_encode($health, JSON_PRETTY_PRINT);
