<?php
/**
 * Bot Learned Knowledge API
 *
 * Manage knowledge learned by bots and share between them
 *
 * Endpoints:
 *   POST /api/bot_knowledge.php?action=add
 *   GET  /api/bot_knowledge.php?action=search
 *   POST /api/bot_knowledge.php?action=apply
 *   GET  /a33333333333333333333333333333333333
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

$action = $_GET['action'] ?? 'search';
$method = $_SERVER['REQUEST_METHOD'];

function send_response(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ===========================================================================
// ACTION: Add learned knowledge
// ===========================================================================
if ($action === 'add' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['source_bot_id'], $input['knowledge_type'], $input['title'])) {
        send_response(['success' => false, 'error' => 'Missing required fields'], 400);
    }

    $bot_id = (int)$input['source_bot_id'];
    $type = $input['knowledge_type'];
    $title = $input['title'];
    $description = $input['description'] ?? null;
    $code_example = $input['code_example'] ?? null;
    $context_tags = isset($input['context_tags']) ? json_encode($input['context_tags']) : null;
    $applicability = $input['applicability'] ?? null;
    $confidence = $input['confidence_score'] ?? 50.00;
    $conversation_id = isset($input['learned_from_conversation_id']) ? (int)$input['learned_from_conversation_id'] : null;

    $valid_types = ['pattern', 'solution', 'gotcha', 'best_practice', 'optimization', 'bug_fix', 'tool_usage', 'workflow'];
    if (!in_array($type, $valid_types, true)) {
        send_response(['success' => false, 'error' => 'Invalid knowledge_type'], 400);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_learned_knowledge
            (source_bot_id, knowledge_type, title, description, code_example,
             context_tags, applicability, confidence_score, learned_from_conversation_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$bot_id, $type, $title, $description, $code_example, $context_tags, $applicability, $confidence, $conversation_id]);

        $knowledge_id = (int)$pdo->lastInsertId();

        send_response(['success' => true, 'knowledge_id' => $knowledge_id, 'message' => 'Knowledge added successfully']);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Search knowledge
// ===========================================================================
if ($action === 'search' && $method === 'GET') {
    $query = $_GET['query'] ?? '';
    $type = $_GET['knowledge_type'] ?? null;
    $min_confidence = isset($_GET['min_confidence']) ? (float)$_GET['min_confidence'] : 0;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

    try {
        $sql = "
            SELECT k.*, b.instance_name as source_bot_name
            FROM bot_learned_knowledge k
            JOIN bot_instances b ON k.source_bot_id = b.id
            WHERE k.confidence_score >= ?
        ";
        $params = [$min_confidence];

        if ($type) {
            $sql .= " AND k.knowledge_type = ?";
            $params[] = $type;
        }

        if ($query) {
            $sql .= " AND MATCH(k.title, k.description) AGAINST(? IN NATURAL LANGUAGE MODE)";
            $params[] = $query;
        }

        $sql .= " ORDER BY k.success_rate DESC, k.times_applied DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $knowledge = $stmt->fetchAll();

        // Decode JSON fields
        foreach ($knowledge as &$k) {
            $k['context_tags'] = json_decode($k['context_tags'], true);
        }

        send_response(['success' => true, 'count' => count($knowledge), 'knowledge' => $knowledge]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Apply knowledge (track usage)
// ===========================================================================
if ($action === 'apply' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['knowledge_id'], $input['was_successful'])) {
        send_response(['success' => false, 'error' => 'Missing required fields'], 400);
    }

    $knowledge_id = (int)$input['knowledge_id'];
    $was_successful = (bool)$input['was_successful'];

    try {
        $stmt = $pdo->prepare("CALL sp_apply_learned_knowledge(?, ?)");
        $stmt->execute([$knowledge_id, $was_successful]);

        send_response(['success' => true, 'message' => 'Knowledge application recorded']);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

// ===========================================================================
// ACTION: Get top knowledge
// ===========================================================================
if ($action === 'top' && $method === 'GET') {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

    try {
        $stmt = $pdo->prepare("
            SELECT * FROM v_top_learned_knowledge
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $knowledge = $stmt->fetchAll();

        send_response(['success' => true, 'count' => count($knowledge), 'top_knowledge' => $knowledge]);

    } catch (PDOException $e) {
        send_response(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
    }
}

send_response(['success' => false, 'error' => 'Invalid action', 'available_actions' => ['add', 'search', 'apply', 'top']], 400);
