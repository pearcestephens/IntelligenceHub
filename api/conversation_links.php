<?php
/**
 * Conversation Links API
 *
 * Link related conversations together
 *
 * Endpoints:
 *   POST /api/conversation_links.php?action=link
 *   GET  /api/conversation_links.php?action=get&conversation_id=X
 *   GET  /api/conversation_links.php?action=graph
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

// Database connection
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
// ACTION: Link conversations
// ===========================================================================
if ($action === 'link' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['source_id'], $input['target_id'], $input['link_type'])) {
        send_response(['success' => false, 'error' => 'Missing required fields'], 400);
    }

    $source_id = (int)$input['source_id'];
    $target_id = (int)$input['target_id'];
    $link_type = $input['link_type'];
    $description = $input['description'] ?? null;
    $created_by = $input['created_by'] ?? 'api';

    $valid_types = ['continuation', 'spawned', 'merged', 'related', 'supersedes', 'depends_on'];
    if (!in_array($link_type, $valid_types, true)) {
        send_response(['success' => false, 'error' => 'Invalid link_type'], 400);
    }

    try {
        // Use stored procedure for auto-strength calculation
        $stmt = $pdo->prepare("CALL sp_link_conversations(?, ?, ?, ?, ?)");
        $stmt->execute([$source_id, $target_id, $link_type, $description, $created_by]);

        send_response(['success' => true, 'message' => 'Conversations linked successfully']);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get conversation links
// ===========================================================================
if ($action === 'get' && $method === 'GET') {
    $conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;

    if ($conversation_id === 0) {
        send_response(['success' => false, 'error' => 'conversation_id required'], 400);
    }

    try {
        // Get links where this conversation is source or target
        $stmt = $pdo->prepare("
            SELECT * FROM v_conversation_relationships
            WHERE source_id = ? OR target_id = ?
            ORDER BY link_created_at DESC
        ");
        $stmt->execute([$conversation_id, $conversation_id]);
        $links = $stmt->fetchAll();

        send_response(['success' => true, 'count' => count($links), 'links' => $links]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get conversation graph
// ===========================================================================
if ($action === 'graph' && $method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM v_conversation_relationships
            ORDER BY link_created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $links = $stmt->fetchAll();

        send_response(['success' => true, 'count' => count($links), 'relationships' => $links]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

send_response(['success' => false, 'error' => 'Invalid action', 'available_actions' => ['link', 'get', 'graph']], 400);
