<?php
/**
 * Conversation Bookmarks API
 *
 * Mark and retrieve important moments in conversations
 *
 * Endpoints:
 *   POST /api/conversation_bookmarks.php?action=add
 *   GET  /api/conversation_bookmarks.php?action=get&conversation_id=X
 *   GET  /api/conversation_bookmarks.php?action=critical
 *
 * @package IntelligenceHub
 * @author AI Team Leader
 * @date 2025-11-05
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$db_config = ['host' => '127.0.0.1', 'dbname' => 'hdgwrzntwa', 'user' => 'hdgwrzntwa', 'pass' => 'bFUdRjh4Jx'];

try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['user'], $db_config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? 'get';
$method = $_SERVER['REQUEST_METHOD'];

function send_response(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ===========================================================================
// ACTION: Add bookmark
// ===========================================================================
if ($action === 'add' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['conversation_id'], $input['bookmark_type'], $input['title'])) {
        send_response(['success' => false, 'error' => 'Missing required fields'], 400);
    }

    $conversation_id = (int)$input['conversation_id'];
    $message_id = isset($input['message_id']) ? (int)$input['message_id'] : null;
    $type = $input['bookmark_type'];
    $title = $input['title'];
    $notes = $input['notes'] ?? null;
    $code_snippet = $input['code_snippet'] ?? null;
    $file_path = $input['file_path'] ?? null;
    $line_number = isset($input['line_number']) ? (int)$input['line_number'] : null;
    $tags = isset($input['tags']) ? json_encode($input['tags']) : null;
    $importance = $input['importance'] ?? 'medium';
    $created_by = $input['created_by'] ?? 'api';

    $valid_types = ['decision', 'solution', 'blocker', 'milestone', 'question', 'insight', 'error', 'todo', 'reference'];
    if (!in_array($type, $valid_types, true)) {
        send_response(['success' => false, 'error' => 'Invalid bookmark_type'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_conversation_bookmarks
            (conversation_id, message_id, bookmark_type, title, notes, code_snippet,
             file_path, line_number, tags, importance, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$conversation_id, $message_id, $type, $title, $notes, $code_snippet, $file_path, $line_number, $tags, $importance, $created_by]);

        $bookmark_id = (int)$pdo->lastInsertId();

        send_response(['success' => true, 'bookmark_id' => $bookmark_id, 'message' => 'Bookmark added successfully']);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get bookmarks for conversation
// ===========================================================================
if ($action === 'get' && $method === 'GET') {
    $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
    $type = $_GET['bookmark_type'] ?? null;
    $importance = $_GET['importance'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    if ($conversation_id === 0) {
        send_response(['success' => false, 'error' => 'conversation_id required'], 400);
    }

    try {
        $sql = "
            SELECT * FROM bot_conversation_bookmarks
            WHERE conversation_id = ?
        ";
        $params = [$conversation_id];

        if ($type) {
            $sql .= " AND bookmark_type = ?";
            $params[] = $type;
        }

        if ($importance) {
            $sql .= " AND importance = ?";
            $params[] = $importance;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookmarks = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($bookmarks as &$bookmark) {
            $bookmark['tags'] = $bookmark['tags'] ? json_decode($bookmark['tags'], true) : [];
        }

        send_response(['success' => true, 'count' => count($bookmarks), 'bookmarks' => $bookmarks]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get critical bookmarks
// ===========================================================================
if ($action === 'critical' && $method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM v_critical_bookmarks
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $bookmarks = $stmt->fetchAll();

        send_response(['success' => true, 'count' => count($bookmarks), 'critical_bookmarks' => $bookmarks]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

send_response(['success' => false, 'error' => 'Invalid action', 'available_actions' => ['add', 'get', 'critical']], 400);
