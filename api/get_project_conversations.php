<?php
/**
 * Get Project Conversations API
 *
 * Retrieves past conversations for a specific project/unit/server
 * Used by AI assistants to automatically get context about what was discussed before
 *
 * AUTOMATIC CONTEXT RETRIEVAL:
 * - When bot starts conversation, it calls this API with current project_id
 * - Gets last N conversations about THIS project
 * - Understands "where we left off"
 * - Continues from previous discussion
 *
 * Usage Examples:
 *
 * 1. Get last 5 conversations for current project:
 *    POST /api/get_project_conversations.php
 *    { "project_id": 2, "limit": 5 }
 *
 * 2. Get conversations with full messages:
 *    POST /api/get_project_conversations.php
 *    { "project_id": 2, "limit": 5, "include_messages": true }
 *
 * 3. Get all conversations for a unit (all CIS projects):
 *    POST /api/get_project_conversations.php
 *    { "unit_id": 2, "limit": 20 }
 *
 * 4. Get conversations in date range:
 *    POST /api/get_project_conversations.php
 *    { "project_id": 2, "date_from": "2025-01-01", "limit": 10 }
 *
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Only POST requests are allowed'
        ]
    ]);
    exit;
}

// Database connection
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4', 'hdgwrzntwa', 'bFUdRjh4Jx', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'DB_CONNECTION_ERROR',
            'message' => 'Database connection failed'
        ]
    ]);
    exit;
}

try {
    // Get request data
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!$data || !is_array($data)) {
        throw new Exception('Invalid JSON input');
    }

    // Extract parameters
    $project_id = $data['project_id'] ?? null;
    $unit_id = $data['unit_id'] ?? null;
    $server_id = $data['server_id'] ?? null;
    $limit = isset($data['limit']) ? (int)$data['limit'] : 10;
    $offset = isset($data['offset']) ? (int)$data['offset'] : 0;
    $include_messages = (bool)($data['include_messages'] ?? false);
    $date_from = $data['date_from'] ?? null;
    $date_to = $data['date_to'] ?? null;
    $status = $data['status'] ?? null;
    $search = $data['search'] ?? null;

    // Validate limits
    if ($limit < 1 || $limit > 100) {
        $limit = 10;
    }

    // Build WHERE clause
    $where = ['org_id = 1'];
    $params = [];

    if ($project_id !== null) {
        $where[] = 'project_id = ?';
        $params[] = $project_id;
    }

    if ($unit_id !== null) {
        $where[] = 'unit_id = ?';
        $params[] = $unit_id;
    }

    if ($server_id !== null) {
        $where[] = 'server_id = ?';
        $params[] = $server_id;
    }

    if ($date_from !== null) {
        $where[] = 'started_at >= ?';
        $params[] = $date_from;
    }

    if ($date_to !== null) {
        $where[] = 'started_at <= ?';
        $params[] = $date_to;
    }

    if ($status !== null) {
        $where[] = 'status = ?';
        $params[] = $status;
    }

    if ($search !== null) {
        $where[] = '(conversation_title LIKE ? OR conversation_context LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    $whereClause = implode(' AND ', $where);

    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM ai_conversations WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Prepare parameters for main query (includes LIMIT/OFFSET)
    $mainParams = $params;
    $mainParams[] = (int)$limit;
    $mainParams[] = (int)$offset;

    // Get conversations
    $sql = "
        SELECT
            conversation_id,
            session_id,
            platform,
            user_identifier,
            unit_id,
            project_id,
            server_id,
            source,
            conversation_title,
            conversation_context,
            total_messages,
            total_tokens_estimated,
            status,
            metadata,
            started_at,
            last_message_at,
            ended_at
        FROM ai_conversations
        WHERE $whereClause
        ORDER BY started_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($mainParams);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process conversations
    $processedConversations = [];
    foreach ($conversations as $conv) {
        $processed = [
            'conversation_id' => (int)$conv['conversation_id'],
            'session_id' => $conv['session_id'],
            'platform' => $conv['platform'],
            'user_identifier' => $conv['user_identifier'],
            'context' => [
                'unit_id' => $conv['unit_id'] ? (int)$conv['unit_id'] : null,
                'project_id' => $conv['project_id'] ? (int)$conv['project_id'] : null,
                'server_id' => $conv['server_id'],
                'source' => $conv['source']
            ],
            'conversation_title' => $conv['conversation_title'],
            'conversation_context' => $conv['conversation_context'],
            'total_messages' => (int)$conv['total_messages'],
            'total_tokens_estimated' => (int)$conv['total_tokens_estimated'],
            'status' => $conv['status'],
            'started_at' => $conv['started_at'],
            'last_message_at' => $conv['last_message_at'],
            'ended_at' => $conv['ended_at']
        ];

        // Decode metadata
        if ($conv['metadata']) {
            $processed['metadata'] = json_decode($conv['metadata'], true);
        }

        // Include messages if requested
        if ($include_messages) {
            $msgSql = "
                SELECT
                    message_id,
                    role,
                    content,
                    tokens_estimated,
                    created_at
                FROM ai_conversation_messages
                WHERE conversation_id = ?
                ORDER BY message_sequence ASC
            ";
            $msgStmt = $pdo->prepare($msgSql);
            $msgStmt->execute([$conv['conversation_id']]);
            $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

            $processed['messages'] = array_map(function($msg) {
                return [
                    'message_id' => (int)$msg['message_id'],
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                    'tokens_estimated' => (int)$msg['tokens_estimated'],
                    'created_at' => $msg['created_at']
                ];
            }, $messages);
        }

        $processedConversations[] = $processed;
    }

    // Get project/unit names if available
    $contextInfo = [];
    if ($project_id) {
        // You can add project name lookup here if you have a projects table
        $contextInfo['project_id'] = $project_id;
    }
    if ($unit_id) {
        $contextInfo['unit_id'] = $unit_id;
    }
    if ($server_id) {
        $contextInfo['server_id'] = $server_id;
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'context' => $contextInfo,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'returned' => count($processedConversations),
        'conversations' => $processedConversations,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'DATABASE_ERROR',
            'message' => 'Database error occurred',
            'details' => $e->getMessage()
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INVALID_REQUEST',
            'message' => $e->getMessage()
        ]
    ]);
}
