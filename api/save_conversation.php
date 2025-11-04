<?php
/**
 * AI Conversation Recorder API
 *
 * Saves GitHub Copilot conversations to intelligence database
 *
 * Usage:
 *   POST https://gpt.ecigdis.co.nz/api/save_conversation.php
 *
 * JSON Body:
 * {
 *   "session_id": "unique-session-id",
 *   "platform": "github_copilot",
 *   "user_identifier": "pearce.stephens@ecigdis.co.nz",
 *   "conversation_title": "Intelligence System Setup",
 *   "conversation_context": "Full conversation summary...",
 *   "messages": [
 *     {"role": "user", "content": "Message text", "tokens": 100},
 *     {"role": "assistant", "content": "Response text", "tokens": 200}
 *   ],
 *   "topics": ["database", "intelligence", "scanner"],
 *   "status": "completed"
 * }
 *
 * @package Intelligence\API
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4', 'hdgwrzntwa', 'bFUdRjh4Jx');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception('Invalid JSON input');
    }

    // Validate required fields
    if (empty($data['session_id'])) {
        throw new Exception('session_id is required');
    }

    $session_id = $data['session_id'];
    $platform = $data['platform'] ?? 'github_copilot';
    $user_identifier = $data['user_identifier'] ?? null;
    $conversation_title = $data['conversation_title'] ?? null;
    $conversation_context = $data['conversation_context'] ?? null;
    $messages = $data['messages'] ?? [];
    $topics = $data['topics'] ?? [];
    $status = $data['status'] ?? 'active';
    $metadata = isset($data['metadata']) ? json_encode($data['metadata']) : null;

    // NEW: Project/Server tracking
    $unit_id = $data['unit_id'] ?? null;  // 1=Hub, 2=CIS, 3=Retail, 4=Wholesale
    $project_id = $data['project_id'] ?? null;
    $server_id = $data['server_id'] ?? null;  // e.g. 'hdgwrzntwa', 'jcepnzzkmj'
    $source = $data['source'] ?? 'github_copilot';  // github_copilot, vscode, web, api

    // Start transaction
    $pdo->beginTransaction();

    // Check if conversation exists
    $stmt = $pdo->prepare("SELECT conversation_id FROM ai_conversations WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing conversation
        $conversation_id = $existing['conversation_id'];

        $stmt = $pdo->prepare("
            UPDATE ai_conversations
            SET conversation_title = COALESCE(?, conversation_title),
                conversation_context = COALESCE(?, conversation_context),
                unit_id = COALESCE(?, unit_id),
                project_id = COALESCE(?, project_id),
                server_id = COALESCE(?, server_id),
                source = COALESCE(?, source),
                total_messages = ?,
                total_tokens_estimated = ?,
                status = ?,
                metadata = COALESCE(?, metadata),
                last_message_at = CURRENT_TIMESTAMP,
                ended_at = IF(? IN ('completed', 'abandoned', 'error'), CURRENT_TIMESTAMP, ended_at)
            WHERE conversation_id = ?
        ");

        $total_tokens = array_sum(array_column($messages, 'tokens'));

        $stmt->execute([
            $conversation_title,
            $conversation_context,
            $unit_id,
            $project_id,
            $server_id,
            $source,
            count($messages),
            $total_tokens,
            $status,
            $metadata,
            $status,
            $conversation_id
        ]);

    } else {
        // Insert new conversation
        $stmt = $pdo->prepare("
            INSERT INTO ai_conversations (
                org_id, unit_id, project_id, server_id, source,
                session_id, platform, user_identifier,
                conversation_title, conversation_context,
                total_messages, total_tokens_estimated, status, metadata
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $total_tokens = array_sum(array_column($messages, 'tokens'));

        $stmt->execute([
            $unit_id,
            $project_id,
            $server_id,
            $source,
            $session_id,
            $platform,
            $user_identifier,
            $conversation_title,
            $conversation_context,
            count($messages),
            $total_tokens,
            $status,
            $metadata
        ]);

        $conversation_id = (int)$pdo->lastInsertId();
    }

    // Save messages (if provided)
    if (!empty($messages)) {
        // Delete old messages first (for updates)
        $stmt = $pdo->prepare("DELETE FROM ai_conversation_messages WHERE conversation_id = ?");
        $stmt->execute([$conversation_id]);

        $stmt = $pdo->prepare("
            INSERT INTO ai_conversation_messages (
                conversation_id, message_sequence, role, content,
                tokens_estimated, tool_calls, attachments, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($messages as $idx => $message) {
            $role = $message['role'] ?? 'user';
            $content = $message['content'] ?? '';
            $tokens = $message['tokens'] ?? 0;
            $tool_calls = isset($message['tool_calls']) ? json_encode($message['tool_calls']) : null;
            $attachments = isset($message['attachments']) ? json_encode($message['attachments']) : null;
            $msg_metadata = isset($message['metadata']) ? json_encode($message['metadata']) : null;

            $stmt->execute([
                $conversation_id,
                $idx + 1,
                $role,
                $content,
                $tokens,
                $tool_calls,
                $attachments,
                $msg_metadata
            ]);
        }
    }

    // Save topics (if provided)
    if (!empty($topics)) {
        // Delete old topics first
        $stmt = $pdo->prepare("DELETE FROM ai_conversation_topics WHERE conversation_id = ?");
        $stmt->execute([$conversation_id]);

        $stmt = $pdo->prepare("
            INSERT INTO ai_conversation_topics (conversation_id, topic, confidence)
            VALUES (?, ?, ?)
        ");

        foreach ($topics as $topic) {
            $topic_name = is_array($topic) ? ($topic['topic'] ?? $topic['name'] ?? null) : $topic;
            if (empty($topic_name)) { continue; }
            $confidence = is_array($topic) ? ($topic['confidence'] ?? 1.00) : 1.00;

            $stmt->execute([$conversation_id, $topic_name, $confidence]);
        }
    }

    // Commit transaction
    $pdo->commit();

    // Return success
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id,
        'session_id' => $session_id,
        'message' => $existing ? 'Conversation updated' : 'Conversation created',
        'stats' => [
            'total_messages' => count($messages),
            'total_tokens' => $total_tokens ?? 0,
            'topics_count' => count($topics)
        ]
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
