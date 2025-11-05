<?php
/**
 * Bot Deployment Center - API Integration with AI-Agent Platform
 *
 * This integrates with your existing AI-Agent infrastructure:
 * - Uses Agent.php for bot instances
 * - Leverages AIOrchestrator for RAG
 * - Uses existing conversation management
 * - Multi-threaded conversations via AgentPoolManager
 *
 * @package IntelligenceHub\BotManagement
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../ai-agent/src/Agent.php';
require_once __DIR__ . '/../ai-agent/src/Multi/AgentPoolManager.php';
require_once __DIR__ . '/../ai-agent/lib/AIOrchestrator.php';
require_once __DIR__ . '/../ai-agent/src/Config.php';
require_once __DIR__ . '/../ai-agent/src/Logger.php';

use App\Agent;
use App\Multi\AgentPoolManager;
use App\Config;
use App\Logger;

class BotDeploymentCenterAPI {
    private mysqli $db;
    private Config $config;
    private Logger $logger;
    private ?AgentPoolManager $agentPool = null;

    public function __construct() {
        $this->db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');
        if ($this->db->connect_error) {
            throw new Exception("Database connection failed");
        }
        $this->db->set_charset('utf8mb4');

        $this->config = new Config();
        $this->logger = new Logger($this->config);
        $this->agentPool = new AgentPoolManager($this->config, $this->logger);
    }

    /**
     * Deploy a new bot with custom system prompt
     */
    public function deployBot(array $config): array {
        // Insert bot deployment record
        $stmt = $this->db->prepare("
            INSERT INTO bot_deployments
            (bot_name, bot_role, system_prompt, schedule_cron, status, config_json, created_at)
            VALUES (?, ?, ?, ?, 'active', ?, NOW())
        ");

        $configJson = json_encode([
            'model' => $config['model'] ?? 'gpt-4-turbo-preview',
            'temperature' => $config['temperature'] ?? 0.7,
            'max_tokens' => $config['max_tokens'] ?? 2000,
            'enable_tools' => $config['enable_tools'] ?? true,
            'enable_rag' => $config['enable_rag'] ?? true
        ]);

        $stmt->bind_param('sssss',
            $config['name'],
            $config['role'],
            $config['system_prompt'],
            $config['schedule'] ?? null,
            $configJson
        );

        $stmt->execute();
        $botId = $this->db->insert_id;

        // Create Agent instance with custom config
        $botAgent = $this->createBotAgent($botId, $config);

        // Store in agent pool
        $this->agentPool->addAgent(
            (string)$botId,
            $botAgent,
            [
                'role' => $config['role'],
                'system_prompt' => $config['system_prompt']
            ]
        );

        return [
            'bot_id' => $botId,
            'status' => 'deployed',
            'agent_created' => true,
            'capabilities' => [
                'tools' => $config['enable_tools'] ?? true,
                'rag' => $config['enable_rag'] ?? true,
                'memory' => true
            ]
        ];
    }

    /**
     * Create AI-Agent instance for a bot
     */
    private function createBotAgent(int $botId, array $config): Agent {
        // Override config for this bot
        $botConfig = clone $this->config;
        $botConfig->set('AGENT_NAME', $config['name']);
        $botConfig->set('DEFAULT_MODEL', $config['model'] ?? 'gpt-4-turbo-preview');
        $botConfig->set('ENABLE_TOOLS', $config['enable_tools'] ?? 'true');
        $botConfig->set('ENABLE_MEMORY', 'true');
        $botConfig->set('ENABLE_KNOWLEDGE', $config['enable_rag'] ?? 'true');

        $agent = new Agent($botConfig, $this->logger);
        $agent->initialize();

        return $agent;
    }

    /**
     * Start multi-threaded conversation (4 parallel threads)
     */
    public function startMultiThreadConversation(string $topic, array $botIds = []): array {
        $sessionId = 'mt_' . uniqid();
        $threadCount = 4;

        // Create session
        $stmt = $this->db->prepare("
            INSERT INTO multi_thread_sessions
            (session_id, topic, thread_count, status, metadata, started_at)
            VALUES (?, ?, ?, 'active', ?, NOW())
        ");

        $metadata = json_encode([
            'bot_ids' => $botIds,
            'initiated_by' => 'api',
            'topic' => $topic
        ]);

        $stmt->bind_param('ssis', $sessionId, $topic, $threadCount, $metadata);
        $stmt->execute();

        // Create 4 parallel threads
        $threads = [];
        for ($i = 1; $i <= 4; $i++) {
            $threadId = $this->createThread($sessionId, $i, $topic, $botIds[$i-1] ?? null);
            $threads[] = [
                'thread_id' => $threadId,
                'thread_number' => $i,
                'bot_id' => $botIds[$i-1] ?? null,
                'status' => 'active'
            ];
        }

        return [
            'session_id' => $sessionId,
            'topic' => $topic,
            'threads' => $threads,
            'thread_count' => $threadCount,
            'status' => 'active',
            'started_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Create individual thread
     */
    private function createThread(string $sessionId, int $threadNumber, string $topic, ?int $botId): string {
        $threadId = "{$sessionId}_thread_{$threadNumber}";

        $stmt = $this->db->prepare("
            INSERT INTO conversation_threads
            (thread_id, session_id, thread_number, topic, bot_id, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->bind_param('ssisi', $threadId, $sessionId, $threadNumber, $topic, $botId);
        $stmt->execute();

        return $threadId;
    }

    /**
     * Send message to specific thread using AI-Agent
     */
    public function sendMessageToThread(string $threadId, string $message, string $role = 'user'): array {
        // Get thread info
        $stmt = $this->db->prepare("SELECT * FROM conversation_threads WHERE thread_id = ?");
        $stmt->bind_param('s', $threadId);
        $stmt->execute();
        $thread = $stmt->get_result()->fetch_assoc();

        if (!$thread) {
            throw new Exception("Thread not found");
        }

        // Save user message
        $this->addThreadMessage($threadId, $role, $message);

        // If there's a bot assigned, process with AI-Agent
        if ($thread['bot_id']) {
            $response = $this->agentPool->processMessage(
                (string)$thread['bot_id'],
                $message,
                ['conversation_id' => $threadId]
            );

            // Save bot response
            $this->addThreadMessage($threadId, 'assistant', $response['content'] ?? $response['response']);

            return [
                'thread_id' => $threadId,
                'message' => $message,
                'response' => $response['content'] ?? $response['response'],
                'bot_id' => $thread['bot_id'],
                'processing_time_ms' => $response['processing_time_ms'] ?? 0
            ];
        }

        return [
            'thread_id' => $threadId,
            'message' => $message,
            'status' => 'queued',
            'note' => 'No bot assigned to this thread'
        ];
    }

    /**
     * Broadcast message to all 4 threads simultaneously
     */
    public function broadcastToAllThreads(string $sessionId, string $message): array {
        // Get all threads in session
        $stmt = $this->db->prepare("
            SELECT * FROM conversation_threads
            WHERE session_id = ? AND status = 'active'
        ");
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $responses = [];
        $botIds = [];

        // Collect bot IDs
        foreach ($threads as $thread) {
            if ($thread['bot_id']) {
                $botIds[] = (string)$thread['bot_id'];
            }
        }

        // Send to all bots simultaneously using AgentPoolManager
        if (!empty($botIds)) {
            $multiResponses = $this->agentPool->processMessageMulti(
                $botIds,
                $message,
                ['session_id' => $sessionId]
            );

            // Save responses to respective threads
            foreach ($threads as $thread) {
                if ($thread['bot_id'] && isset($multiResponses[(string)$thread['bot_id']])) {
                    $response = $multiResponses[(string)$thread['bot_id']];
                    $this->addThreadMessage($thread['thread_id'], 'user', $message);
                    $this->addThreadMessage($thread['thread_id'], 'assistant', $response['content'] ?? $response['response']);

                    $responses[] = [
                        'thread_id' => $thread['thread_id'],
                        'thread_number' => $thread['thread_number'],
                        'bot_id' => $thread['bot_id'],
                        'response' => $response['content'] ?? $response['response']
                    ];
                }
            }
        }

        return [
            'session_id' => $sessionId,
            'message' => $message,
            'threads_processed' => count($responses),
            'responses' => $responses,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Add message to thread
     */
    private function addThreadMessage(string $threadId, string $role, string $content, array $metadata = []): void {
        $stmt = $this->db->prepare("
            INSERT INTO thread_messages
            (thread_id, role, content, metadata, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $metadataJson = json_encode($metadata);
        $stmt->bind_param('ssss', $threadId, $role, $content, $metadataJson);
        $stmt->execute();
    }

    /**
     * Get all active bots
     */
    public function getActiveBots(): array {
        $result = $this->db->query("
            SELECT
                b.*,
                COUNT(DISTINCT t.thread_id) as active_threads,
                COUNT(m.message_id) as total_messages
            FROM bot_deployments b
            LEFT JOIN conversation_threads t ON b.bot_id = t.bot_id AND t.status = 'active'
            LEFT JOIN thread_messages m ON t.thread_id = m.thread_id
            WHERE b.status = 'active'
            GROUP BY b.bot_id
            ORDER BY b.created_at DESC
        ");

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get multi-thread session details
     */
    public function getSessionDetails(string $sessionId): array {
        // Session info
        $stmt = $this->db->prepare("SELECT * FROM multi_thread_sessions WHERE session_id = ?");
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $session = $stmt->get_result()->fetch_assoc();

        // Threads
        $stmt = $this->db->prepare("
            SELECT
                t.*,
                b.bot_name,
                b.bot_role,
                COUNT(m.message_id) as message_count
            FROM conversation_threads t
            LEFT JOIN bot_deployments b ON t.bot_id = b.bot_id
            LEFT JOIN thread_messages m ON t.thread_id = m.thread_id
            WHERE t.session_id = ?
            GROUP BY t.thread_id
        ");
        $stmt->bind_param('s', $sessionId);
        $stmt->execute();
        $threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'session' => $session,
            'threads' => $threads
        ];
    }

    /**
     * Assign bot to thread
     */
    public function assignBotToThread(string $threadId, int $botId): array {
        $stmt = $this->db->prepare("
            UPDATE conversation_threads
            SET bot_id = ?, updated_at = NOW()
            WHERE thread_id = ?
        ");
        $stmt->bind_param('is', $botId, $threadId);
        $stmt->execute();

        return [
            'thread_id' => $threadId,
            'bot_id' => $botId,
            'status' => 'assigned'
        ];
    }
}

// API Router
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $api = new BotDeploymentCenterAPI();
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    $response = ['success' => false];

    switch ($action) {
        case 'deploy_bot':
            $response = ['success' => true, 'data' => $api->deployBot($input)];
            break;

        case 'start_multithread':
            $response = ['success' => true, 'data' => $api->startMultiThreadConversation(
                $input['topic'],
                $input['bot_ids'] ?? []
            )];
            break;

        case 'send_message':
            $response = ['success' => true, 'data' => $api->sendMessageToThread(
                $input['thread_id'],
                $input['message'],
                $input['role'] ?? 'user'
            )];
            break;

        case 'broadcast':
            $response = ['success' => true, 'data' => $api->broadcastToAllThreads(
                $input['session_id'],
                $input['message']
            )];
            break;

        case 'get_bots':
            $response = ['success' => true, 'data' => $api->getActiveBots()];
            break;

        case 'get_session':
            $response = ['success' => true, 'data' => $api->getSessionDetails($input['session_id'])];
            break;

        case 'assign_bot':
            $response = ['success' => true, 'data' => $api->assignBotToThread(
                $input['thread_id'],
                $input['bot_id']
            )];
            break;

        default:
            throw new Exception("Unknown action: $action");
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
