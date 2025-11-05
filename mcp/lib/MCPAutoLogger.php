<?php
/**
 * MCP Auto-Logging Middleware
 *
 * Intercepts EVERY request/response and automatically logs to database
 *
 * - Captures user messages
 * - Captures assistant responses
 * - Extracts session_id from headers or generates one
 * - Saves to ai_conversations and ai_conversation_messages tables
 * - Non-blocking (uses async logging)
 *
 * @package IntelligenceHub\MCP
 * @version 3.0.0
 */

declare(strict_types=1);

class MCPAutoLogger {

    private static ?string $sessionId = null;
    private static array $messages = [];
    private static bool $enabled = true;

    /**
     * Initialize auto-logger (call at start of server_v3.php)
     */
    public static function init(): void {
        // Check if auto-logging is enabled
        self::$enabled = ($_SERVER['HTTP_X_AUTO_LOG'] ?? 'true') === 'true';

        if (!self::$enabled) {
            return;
        }

        // Get or generate session ID
        self::$sessionId = self::getSessionId();

        // Register shutdown function to save conversation
        register_shutdown_function([self::class, 'saveConversation']);

        // Capture request input
        self::captureRequest();
    }

    /**
     * Get session ID from various sources
     */
    private static function getSessionId(): string {
        // Priority:
        // 1. HTTP header X-Session-Id
        // 2. Query parameter session_id
        // 3. Generate from workspace + timestamp

        if (!empty($_SERVER['HTTP_X_SESSION_ID'])) {
            return $_SERVER['HTTP_X_SESSION_ID'];
        }

        if (!empty($_GET['session_id'])) {
            return $_GET['session_id'];
        }

        // Generate from workspace root + date
        $workspace = basename($_SERVER['HTTP_X_WORKSPACE_ROOT'] ?? 'unknown');
        $date = date('Ymd');
        $random = substr(md5(uniqid()), 0, 8);

        return "mcp-{$workspace}-{$date}-{$random}";
    }

    /**
     * Capture incoming request (user message)
     */
    private static function captureRequest(): void {
        if (!self::$enabled) {
            return;
        }

        $input = file_get_contents('php://input');
        if (empty($input)) {
            return;
        }

        $json = json_decode($input, true);
        if (!$json) {
            return;
        }

        // Extract user message from JSON-RPC request
        $method = $json['method'] ?? null;
        $params = $json['params'] ?? [];

        if ($method === 'tools/call') {
            $toolName = $params['name'] ?? 'unknown';
            $arguments = $params['arguments'] ?? [];

            // Extract message content
            $message = $arguments['message'] ?? $arguments['query'] ?? $arguments['prompt'] ?? null;

            if ($message) {
                self::addMessage('user', $message, [
                    'tool' => $toolName,
                    'arguments' => $arguments
                ]);
            }
        }
    }

    /**
     * Capture outgoing response (assistant message)
     */
    public static function captureResponse(string $content, array $metadata = []): void {
        if (!self::$enabled) {
            return;
        }

        self::addMessage('assistant', $content, $metadata);
    }

    /**
     * Add message to buffer
     */
    private static function addMessage(string $role, string $content, array $metadata = []): void {
        $tokens = self::estimateTokens($content);

        self::$messages[] = [
            'role' => $role,
            'content' => $content,
            'tokens' => $tokens,
            'metadata' => $metadata,
            'timestamp' => microtime(true)
        ];
    }

    /**
     * Estimate token count (rough approximation)
     */
    private static function estimateTokens(string $content): int {
        // Rough estimate: 1 token ≈ 4 characters
        return (int) ceil(strlen($content) / 4);
    }

    /**
     * Save conversation to database (called on shutdown)
     */
    public static function saveConversation(): void {
        if (!self::$enabled || empty(self::$messages)) {
            return;
        }

        // Get context
        $context = $GLOBALS['workspace_context'] ?? [];

        // Build payload
        $payload = [
            'session_id' => self::$sessionId,
            'platform' => 'github_copilot',
            'user_identifier' => $_SERVER['HTTP_X_USER'] ?? 'system',
            'conversation_title' => self::generateTitle(),
            'conversation_context' => self::generateContext(),
            'status' => 'active',
            'org_id' => 1,
            'unit_id' => $context['unit_id'] ?? 1,
            'project_id' => $context['project_id'] ?? null,
            'server_id' => $context['server_id'] ?? 'hdgwrzntwa',
            'source' => 'mcp_auto_log',
            'messages' => self::$messages,
            'topics' => self::extractTopics(),
            'metadata' => [
                'auto_logged' => true,
                'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? null,
                'workspace_root' => $_SERVER['HTTP_X_WORKSPACE_ROOT'] ?? null,
                'current_file' => $_SERVER['HTTP_X_CURRENT_FILE'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]
        ];

        // Send async (non-blocking)
        self::sendAsync($payload);
    }

    /**
     * Generate conversation title from first user message
     */
    private static function generateTitle(): string {
        foreach (self::$messages as $msg) {
            if ($msg['role'] === 'user') {
                $title = substr($msg['content'], 0, 100);
                return strlen($msg['content']) > 100 ? $title . '...' : $title;
            }
        }
        return 'MCP Conversation';
    }

    /**
     * Generate conversation context summary
     */
    private static function generateContext(): string {
        $summary = [];
        $summary[] = 'Auto-logged MCP conversation';
        $summary[] = 'Session: ' . self::$sessionId;
        $summary[] = 'Messages: ' . count(self::$messages);
        $summary[] = 'Workspace: ' . ($_SERVER['HTTP_X_WORKSPACE_ROOT'] ?? 'unknown');

        return implode("\n", $summary);
    }

    /**
     * Extract topics from messages using keyword extraction
     */
    private static function extractTopics(): array {
        $allText = '';
        foreach (self::$messages as $msg) {
            $allText .= ' ' . $msg['content'];
        }

        // Simple keyword extraction (can be enhanced with NLP)
        $keywords = [];
        $words = str_word_count(strtolower($allText), 1);
        $counts = array_count_values($words);
        arsort($counts);

        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'as', 'by'];

        foreach ($counts as $word => $count) {
            if (strlen($word) > 3 && !in_array($word, $stopWords)) {
                $keywords[] = ['topic' => $word, 'confidence' => min(1.0, $count / 10)];
            }
            if (count($keywords) >= 10) {
                break;
            }
        }

        return $keywords;
    }

    /**
     * Send payload to conversation-save.php asynchronously
     */
    private static function sendAsync(array $payload): void {
        $url = 'https://gpt.ecigdis.co.nz/mcp/api/conversation-save.php';
        $apiKey = $_ENV['MCP_API_KEY'] ?? '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';

        $jsonPayload = json_encode($payload);

        // Use non-blocking curl or file_get_contents with stream context
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $apiKey,
                    'Connection: close'
                ],
                'content' => $jsonPayload,
                'timeout' => 1.0, // Short timeout, we don't wait for response
                'ignore_errors' => true
            ]
        ]);

        // Fire and forget
        @file_get_contents($url, false, $context);
    }

    /**
     * Manual log method (can be called explicitly)
     */
    public static function log(string $role, string $content, array $metadata = []): void {
        if (!self::$enabled) {
            return;
        }

        self::addMessage($role, $content, $metadata);
    }
}

// Auto-initialize if included in server_v3.php
if (defined('MCP_SERVER_RUNNING')) {
    MCPAutoLogger::init();
}
