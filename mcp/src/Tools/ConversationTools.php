<?php

namespace IntelligenceHub\MCP\Tools;

/**
 * ConversationTools - Manage AI Conversations
 *
 * Tools for saving, retrieving, and managing AI conversations
 * with full support for bot_id, project_id, unit_id tracking
 *
 * @package IntelligenceHub\MCP\Tools
 * @version 3.0.0
 */
class ConversationTools extends BaseTool {

    public function getName(): string {
        return 'conversation';
    }

    public function getSchema(): array {
        return [
            'conversation.save' => [
                'description' => 'Save or update a conversation with messages, topics, and tracking info (bot_id, project_id, unit_id)',
                'parameters' => [
                    'session_id' => ['type' => 'string', 'required' => true, 'description' => 'Unique session identifier'],
                    'platform' => ['type' => 'string', 'required' => false, 'default' => 'github_copilot', 'description' => 'Platform: github_copilot, vscode, web, api'],
                    'user_identifier' => ['type' => 'string', 'required' => false, 'description' => 'User email or ID'],
                    'conversation_title' => ['type' => 'string', 'required' => false, 'description' => 'Title/summary of conversation'],
                    'conversation_context' => ['type' => 'string', 'required' => false, 'description' => 'Full conversation context/summary'],
                    'status' => ['type' => 'string', 'required' => false, 'default' => 'active', 'description' => 'Status: active, completed, abandoned, error'],
                    'org_id' => ['type' => 'integer', 'required' => false, 'default' => 1, 'description' => 'Organization ID'],
                    'unit_id' => ['type' => 'integer', 'required' => false, 'description' => 'Business unit: 1=Hub, 2=CIS, 3=Retail, 4=Wholesale'],
                    'project_id' => ['type' => 'integer', 'required' => false, 'description' => 'Project ID'],
                    'bot_id' => ['type' => 'integer', 'required' => false, 'description' => 'Bot ID (if conversation with specific bot)'],
                    'server_id' => ['type' => 'string', 'required' => false, 'description' => 'Server identifier (hdgwrzntwa, jcepnzzkmj)'],
                    'source' => ['type' => 'string', 'required' => false, 'default' => 'github_copilot', 'description' => 'Source: github_copilot, vscode, web, api'],
                    'messages' => ['type' => 'array', 'required' => false, 'description' => 'Array of message objects with role, content, tokens'],
                    'topics' => ['type' => 'array', 'required' => false, 'description' => 'Array of topics (strings or objects with topic/confidence)'],
                    'metadata' => ['type' => 'object', 'required' => false, 'description' => 'Additional metadata as JSON object']
                ]
            ],
            'conversation.get' => [
                'description' => 'Retrieve conversation by ID or session_id',
                'parameters' => [
                    'conversation_id' => ['type' => 'integer', 'required' => false, 'description' => 'Conversation ID'],
                    'session_id' => ['type' => 'string', 'required' => false, 'description' => 'Session ID'],
                    'include_messages' => ['type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Include messages'],
                    'include_topics' => ['type' => 'boolean', 'required' => false, 'default' => true, 'description' => 'Include topics']
                ]
            ],
            'conversation.list' => [
                'description' => 'List conversations with filters',
                'parameters' => [
                    'platform' => ['type' => 'string', 'required' => false, 'description' => 'Filter by platform'],
                    'unit_id' => ['type' => 'integer', 'required' => false, 'description' => 'Filter by business unit'],
                    'project_id' => ['type' => 'integer', 'required' => false, 'description' => 'Filter by project'],
                    'bot_id' => ['type' => 'integer', 'required' => false, 'description' => 'Filter by bot'],
                    'status' => ['type' => 'string', 'required' => false, 'description' => 'Filter by status'],
                    'limit' => ['type' => 'integer', 'required' => false, 'default' => 20, 'description' => 'Max results']
                ]
            ],
            'conversation.search' => [
                'description' => 'Search conversations by keywords',
                'parameters' => [
                    'search' => ['type' => 'string', 'required' => true, 'description' => 'Search query for title/context'],
                    'unit_id' => ['type' => 'integer', 'required' => false, 'description' => 'Filter by business unit'],
                    'limit' => ['type' => 'integer', 'required' => false, 'default' => 20, 'description' => 'Max results']
                ]
            ],
            'conversation.get_project_context' => [
                'description' => 'Get past conversations for current project - USE AT START OF EVERY CONVERSATION',
                'parameters' => [
                    'project_id' => ['type' => 'integer', 'required' => false, 'description' => 'Project ID (auto-detected if omitted)'],
                    'limit' => ['type' => 'integer', 'required' => false, 'default' => 5, 'description' => 'Number of conversations']
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'save';

        switch ($method) {
            case 'save':
                return $this->save($args);
            case 'get':
                return $this->get($args);
            case 'list':
                return $this->listConversations($args);
            case 'search':
                return $this->search($args);
            case 'get_project_context':
                return $this->getProjectContext($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function save(array $args): array {
        // Call the conversation-save.php API endpoint
        $payload = [
            'session_id' => $args['session_id'] ?? null,
            'platform' => $args['platform'] ?? 'github_copilot',
            'user_identifier' => $args['user_identifier'] ?? null,
            'conversation_title' => $args['conversation_title'] ?? null,
            'conversation_context' => $args['conversation_context'] ?? null,
            'status' => $args['status'] ?? 'active',
            'org_id' => $args['org_id'] ?? 1,
            'unit_id' => $args['unit_id'] ?? null,
            'project_id' => $args['project_id'] ?? null,
            'bot_id' => $args['bot_id'] ?? null,
            'server_id' => $args['server_id'] ?? null,
            'source' => $args['source'] ?? 'github_copilot',
            'messages' => $args['messages'] ?? [],
            'topics' => $args['topics'] ?? [],
            'metadata' => $args['metadata'] ?? null
        ];

        $result = $this->httpPost('conversation-save.php', $payload);

        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function get(array $args): array {
        $db = $this->getDatabase();

        $conversation_id = $args['conversation_id'] ?? null;
        $session_id = $args['session_id'] ?? null;
        $include_messages = $args['include_messages'] ?? true;
        $include_topics = $args['include_topics'] ?? true;

        if (!$conversation_id && !$session_id) {
            return $this->fail('conversation_id or session_id required');
        }

        // Get conversation
        if ($conversation_id) {
            $stmt = $db->prepare("SELECT * FROM ai_conversations WHERE id = ?");
            $stmt->execute([$conversation_id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM ai_conversations WHERE session_id = ? ORDER BY updated_at DESC LIMIT 1");
            $stmt->execute([$session_id]);
        }

        $conversation = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$conversation) {
            return $this->fail('Conversation not found', 404);
        }

        // Get messages if requested
        if ($include_messages) {
            $stmt = $db->prepare("SELECT * FROM ai_conversation_messages WHERE conversation_id = ? ORDER BY message_sequence");
            $stmt->execute([$conversation['id']]);
            $conversation['messages'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        // Get topics if requested
        if ($include_topics) {
            $stmt = $db->prepare("SELECT topic, confidence FROM ai_conversation_topics WHERE conversation_id = ?");
            $stmt->execute([$conversation['id']]);
            $conversation['topics'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $this->ok($conversation);
    }

    private function listConversations(array $args): array {
        $db = $this->getDatabase();

        $where = ['1=1'];
        $params = [];

        if (!empty($args['platform'])) {
            $where[] = 'platform = ?';
            $params[] = $args['platform'];
        }

        if (!empty($args['unit_id'])) {
            $where[] = 'unit_id = ?';
            $params[] = $args['unit_id'];
        }

        if (!empty($args['project_id'])) {
            $where[] = 'project_id = ?';
            $params[] = $args['project_id'];
        }

        if (!empty($args['bot_id'])) {
            $where[] = 'bot_id = ?';
            $params[] = $args['bot_id'];
        }

        if (!empty($args['status'])) {
            $where[] = 'status = ?';
            $params[] = $args['status'];
        }

        $limit = min(100, max(1, (int)($args['limit'] ?? 20)));

        $sql = "SELECT * FROM ai_conversations WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->ok([
            'conversations' => $conversations,
            'count' => count($conversations)
        ]);
    }

    private function search(array $args): array {
        $db = $this->getDatabase();

        $search = $args['search'] ?? '';
        if (empty($search)) {
            return $this->fail('search parameter required');
        }

        $where = ['(conversation_title LIKE ? OR conversation_context LIKE ?)'];
        $params = ['%' . $search . '%', '%' . $search . '%'];

        if (!empty($args['unit_id'])) {
            $where[] = 'unit_id = ?';
            $params[] = $args['unit_id'];
        }

        $limit = min(100, max(1, (int)($args['limit'] ?? 20)));

        $sql = "SELECT * FROM ai_conversations WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->ok([
            'conversations' => $conversations,
            'count' => count($conversations),
            'search' => $search
        ]);
    }

    private function getProjectContext(array $args): array {
        $db = $this->getDatabase();

        // Auto-detect project_id from context if not provided
        $project_id = $args['project_id'] ?? null;
        if (!$project_id && function_exists('detect_context')) {
            $context = detect_context();
            $project_id = $context['project_id'] ?? null;
        }

        if (!$project_id) {
            return $this->fail('project_id required or could not be auto-detected');
        }

        $limit = min(20, max(1, (int)($args['limit'] ?? 5)));

        $stmt = $db->prepare("
            SELECT id, session_id, conversation_title, conversation_context, status,
                   total_messages, total_tokens_estimated, created_at, updated_at
            FROM ai_conversations
            WHERE project_id = ?
            ORDER BY updated_at DESC
            LIMIT ?
        ");
        $stmt->execute([$project_id, $limit]);
        $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->ok([
            'project_id' => $project_id,
            'conversations' => $conversations,
            'count' => count($conversations)
        ]);
    }

    private function httpPost(string $endpoint, array $payload): array {
        $url = ($_ENV['MCP_BASE_URL'] ?? 'https://gpt.ecigdis.co.nz/mcp') . '/api/' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . ($_ENV['MCP_API_KEY'] ?? '')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'code' => 502, 'data' => null];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['error' => "HTTP $httpCode", 'code' => $httpCode, 'data' => null];
        }

        $json = json_decode($response, true);
        return ['error' => null, 'code' => $httpCode, 'data' => $json ?? $response];
    }
}
