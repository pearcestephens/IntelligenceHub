<?php
/**
 * MCP Conversation Save API
 *
 * UNIFIED endpoint for saving/updating AI conversations
 * Replaces old /api/save_conversation.php
 *
 * POST https://gpt.ecigdis.co.nz/mcp/api/conversation-save.php
 *
 * @package IntelligenceHub\MCP
 * @version 3.0.0
 */

declare(strict_types=1);
require_once __DIR__.'/../lib/Bootstrap.php';

// ⚡ SPEED: Load Redis cache (to invalidate on save)
require_once __DIR__.'/../../classes/RedisCache.php';

// Helper: Get request JSON and validate
if (!function_exists('req_json')) {
    function req_json(): array {
        $raw = file_get_contents('php://input');
        if (!$raw) { return []; }
        $json = json_decode($raw, true);
        return $json ?: [];
    }
}

// Helper: API key check (optional if enabled)
if (!function_exists('require_api_key_if_enabled')) {
    function require_api_key_if_enabled(PDO $db): void {
        $apiKeyRequired = env('MCP_REQUIRE_API_KEY', 'false') === 'true';
        if (!$apiKeyRequired) { return; }

        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$apiKey) {
            envelope_error('UNAUTHORIZED', 'API key required', request_id(), [], 401);
        }

        // Validate API key against DB or ENV
        $validKey = env('MCP_API_KEY');
        if ($apiKey !== $validKey && !str_contains($apiKey, $validKey)) {
            envelope_error('FORBIDDEN', 'Invalid API key', request_id(), [], 403);
        }
    }
}

$rid = new_request_id();

try {
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        envelope_error('METHOD_NOT_ALLOWED', 'Use POST', $rid, [], 405);
    }

    $db = get_pdo();
    require_api_key_if_enabled($db);

    $in = req_json();

    // Validate required fields
    if (empty($in['session_id'])) {
        envelope_error('INVALID_INPUT', 'session_id is required', $rid, [], 422);
    }

    // Extract all fields
    $session_id = (string)$in['session_id'];
    $platform = (string)($in['platform'] ?? 'github_copilot');
    $user_identifier = isset($in['user_identifier']) ? (string)$in['user_identifier'] : null;
    $conversation_title = isset($in['conversation_title']) ? (string)$in['conversation_title'] : null;
    $conversation_context = isset($in['conversation_context']) ? (string)$in['conversation_context'] : null;
    $messages = $in['messages'] ?? [];
    $topics = $in['topics'] ?? [];
    $status = (string)($in['status'] ?? 'active');
    $metadata = isset($in['metadata']) ? json_encode($in['metadata']) : null;

    // NEW: Project/Bot/Server tracking
    $org_id = (int)($in['org_id'] ?? 1);
    $unit_id = isset($in['unit_id']) ? (int)$in['unit_id'] : null;  // 1=Hub, 2=CIS, 3=Retail, 4=Wholesale
    $project_id = isset($in['project_id']) ? (int)$in['project_id'] : null;
    $bot_id = isset($in['bot_id']) ? (int)$in['bot_id'] : null;  // NEW: Bot tracking
    $server_id = isset($in['server_id']) ? (string)$in['server_id'] : null;  // e.g. 'hdgwrzntwa', 'jcepnzzkmj'
    $source = (string)($in['source'] ?? 'github_copilot');  // github_copilot, vscode, web, api

    // ⚡ SPEED: Invalidate conversation cache on save
    RedisCache::deletePattern('conversations:*');

    // Start transaction
    $db->beginTransaction();

    // Check if conversation exists
    $stmt = $db->prepare("SELECT id FROM ai_conversations WHERE session_id = ? AND platform = ?");
    $stmt->execute([$session_id, $platform]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // UPDATE existing conversation
        $conversation_id = (int)$existing['id'];

        $total_tokens = !empty($messages) ? array_sum(array_column($messages, 'tokens')) : 0;

        $stmt = $db->prepare("
            UPDATE ai_conversations
            SET conversation_title = COALESCE(?, conversation_title),
                conversation_context = COALESCE(?, conversation_context),
                org_id = COALESCE(?, org_id),
                unit_id = COALESCE(?, unit_id),
                project_id = COALESCE(?, project_id),
                bot_id = COALESCE(?, bot_id),
                server_id = COALESCE(?, server_id),
                source = COALESCE(?, source),
                total_messages = ?,
                total_tokens_estimated = ?,
                status = ?,
                metadata = COALESCE(?, metadata),
                updated_at = CURRENT_TIMESTAMP,
                last_message_at = CURRENT_TIMESTAMP,
                ended_at = IF(? IN ('completed', 'abandoned', 'error'), CURRENT_TIMESTAMP, ended_at)
            WHERE id = ?
        ");

        $stmt->execute([
            $conversation_title,
            $conversation_context,
            $org_id,
            $unit_id,
            $project_id,
            $bot_id,
            $server_id,
            $source,
            count($messages),
            $total_tokens,
            $status,
            $metadata,
            $status,
            $conversation_id
        ]);

        $action = 'updated';

    } else {
        // INSERT new conversation
        $total_tokens = !empty($messages) ? array_sum(array_column($messages, 'tokens')) : 0;

        $stmt = $db->prepare("
            INSERT INTO ai_conversations (
                org_id, unit_id, project_id, bot_id, server_id, source,
                session_id, platform, user_identifier,
                conversation_title, conversation_context,
                total_messages, total_tokens_estimated, status, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $org_id,
            $unit_id,
            $project_id,
            $bot_id,
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

        $conversation_id = (int)$db->lastInsertId();
        $action = 'created';
    }

    // Save messages (if provided)
    $messages_saved = 0;
    if (!empty($messages)) {
        // Delete old messages first (for updates)
        $stmt = $db->prepare("DELETE FROM ai_conversation_messages WHERE conversation_id = ?");
        $stmt->execute([$conversation_id]);

        $stmt = $db->prepare("
            INSERT INTO ai_conversation_messages (
                conversation_id, message_sequence, role, content,
                tokens_estimated, tool_calls, attachments, metadata
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($messages as $idx => $message) {
            $role = (string)($message['role'] ?? 'user');
            $content = (string)($message['content'] ?? '');
            $tokens = (int)($message['tokens'] ?? 0);
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
            $messages_saved++;
        }
    }

    // Save topics (if provided)
    $topics_saved = 0;
    if (!empty($topics)) {
        // Delete old topics first
        $stmt = $db->prepare("DELETE FROM ai_conversation_topics WHERE conversation_id = ?");
        $stmt->execute([$conversation_id]);

        $stmt = $db->prepare("
            INSERT INTO ai_conversation_topics (conversation_id, topic, confidence)
            VALUES (?, ?, ?)
        ");

        foreach ($topics as $topic) {
            $topic_name = is_array($topic) ? ($topic['topic'] ?? $topic['name'] ?? null) : (string)$topic;
            if (empty($topic_name)) { continue; }

            $confidence = is_array($topic) ? (float)($topic['confidence'] ?? 1.00) : 1.00;

            $stmt->execute([$conversation_id, $topic_name, $confidence]);
            $topics_saved++;
        }
    }

    // Commit transaction
    $db->commit();

    // Return success with detailed stats
    envelope_success([
        'conversation_id' => $conversation_id,
        'session_id' => $session_id,
        'action' => $action,
        'stats' => [
            'messages_saved' => $messages_saved,
            'topics_saved' => $topics_saved,
            'total_tokens' => $total_tokens ?? 0
        ],
        'tracking' => [
            'org_id' => $org_id,
            'unit_id' => $unit_id,
            'project_id' => $project_id,
            'bot_id' => $bot_id,
            'server_id' => $server_id,
            'source' => $source
        ]
    ], $rid, 200);

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    envelope_error(
        'CONVERSATION_SAVE_FAILURE',
        $e->getMessage(),
        $rid,
        env('APP_DEBUG', 'false') === 'true' ? [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : [],
        500
    );
}
