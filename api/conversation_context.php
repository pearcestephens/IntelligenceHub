<?php
/**
 * Conversation Context API
 *
 * Manage rich conversation context for resumable conversations
 *
 * Endpoints:
 *   POST /api/conversation_context.php?action=add
 *   GET  /api/conversation_context.php?action=get&conversation_id=X
 *   GET  /api/conversation_context.php?action=recent
 *
 * @package IntelligenceHub
 * @author AI Team Leader
 * @date 2025-11-05
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Database connection
$db_config = [
    'host' => '127.0.0.1',
    'dbname' => 'hdgwrzntwa',
    'user' => 'hdgwrzntwa',
    'pass' => 'bFUdRjh4Jx'
];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['user'],
        $db_config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? 'get';
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to send JSON response
function send_response(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ===========================================================================
// ACTION: Add conversation context
// ===========================================================================
if ($action === 'add' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['conversation_id'], $input['context_type'], $input['context_data'])) {
        send_response(['success' => false, 'error' => 'Missing required fields: conversation_id, context_type, context_data'], 400);
    }

    $conversation_id = (int)$input['conversation_id'];
    $context_type = $input['context_type'];
    $context_data = is_array($input['context_data']) ? json_encode($input['context_data']) : $input['context_data'];
    $context_summary = $input['context_summary'] ?? null;

    // Validate context_type
    $valid_types = ['project_state', 'file_state', 'decisions', 'code_snippets', 'terminal_output', 'errors', 'todo_list', 'research', 'dependencies'];
    if (!in_array($context_type, $valid_types, true)) {
        send_response(['success' => false, 'error' => 'Invalid context_type'], 400);
    }

    try {
        // Use stored procedure for auto-importance detection
        if ($context_summary) {
            $stmt = $pdo->prepare("CALL sp_add_conversation_context(?, ?, ?, ?)");
            $stmt->execute([$conversation_id, $context_type, $context_data, $context_summary]);
        } else {
            // Manual insert without stored procedure
            $importance = $input['importance'] ?? 'medium';
            $stmt = $pdo->prepare("
                INSERT INTO bot_conversation_context
                (conversation_id, context_type, context_data, context_summary, importance)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$conversation_id, $context_type, $context_data, $context_summary, $importance]);
        }

        $context_id = (int)$pdo->lastInsertId();

        send_response([
            'success' => true,
            'context_id' => $context_id,
            'message' => 'Context added successfully'
        ]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get conversation context
// ===========================================================================
if ($action === 'get' && $method === 'GET') {
    $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
    $context_type = $_GET['context_type'] ?? null;
    $importance = $_GET['importance'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    if ($conversation_id === 0) {
        send_response(['success' => false, 'error' => 'conversation_id required'], 400);
    }

    try {
        $sql = "
            SELECT
                context_id, conversation_id, context_type,
                context_data, context_summary, importance,
                created_at, updated_at
            FROM bot_conversation_context
            WHERE conversation_id = ?
        ";
        $params = [$conversation_id];

        if ($context_type) {
            $sql .= " AND context_type = ?";
            $params[] = $context_type;
        }

        if ($importance) {
            $sql .= " AND importance = ?";
            $params[] = $importance;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $contexts = $stmt->fetchAll();

        // Decode JSON context_data
        foreach ($contexts as &$context) {
            $context['context_data'] = json_decode($context['context_data'], true);
        }

        send_response([
            'success' => true,
            'count' => count($contexts),
            'contexts' => $contexts
        ]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get recent context (last 24 hours)
// ===========================================================================
if ($action === 'recent' && $method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM v_conversation_context_recent
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $contexts = $stmt->fetchAll();

        send_response([
            'success' => true,
            'count' => count($contexts),
            'contexts' => $contexts
        ]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// Invalid action
// ===========================================================================
send_response([
    'success' => false,
    'error' => 'Invalid action or method',
    'available_actions' => ['add', 'get', 'recent']
], 400);
