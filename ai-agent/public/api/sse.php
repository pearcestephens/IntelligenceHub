<?php
/**
 * Server-Sent Events (SSE) Handler
 * 
 * Provides SSE streaming for real-time updates on conversations
 * with proper heartbeats and reconnection.
 * 
 * @author AI Agent System
 * @version 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access forbidden');
}

require_once __DIR__ . '/bootstrap.php';

class SSEHandler
{
    private Config $config;
    private DB $db;
    private RedisClient $redis;
    private Logger $logger;
    private int $heartbeatInterval = 15; // seconds
    private int $maxDuration = 300; // 5 minutes max connection
    
    public function __construct()
    {
        $this->config = ApiBootstrap::getConfig();
        $this->db = ApiBootstrap::getDb();
        $this->redis = ApiBootstrap::getRedis();
        $this->logger = ApiBootstrap::getLogger();
    }
    
    /**
     * Start SSE stream
     */
    public function startStream(string $streamType, array $filters = []): void
    {
        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable Nginx buffering
        
        // Ignore user abort to clean up properly
        ignore_user_abort(true);
        
        $startTime = time();
        $lastHeartbeat = 0;
        $lastEventId = $this->getLastEventId();
        
        $this->logger->info('SSE stream started', [
            'stream_type' => $streamType,
            'filters' => $filters,
            'last_event_id' => $lastEventId,
            'request_id' => ApiBootstrap::getRequestId(),
        ]);
        
        try {
            while (connection_status() === CONNECTION_NORMAL) {
                $now = time();
                
                // Check max duration
                if ($now - $startTime > $this->maxDuration) {
                    $this->sendEvent('stream.close', [
                        'reason' => 'max_duration_exceeded',
                        'duration' => $this->maxDuration,
                    ]);
                    break;
                }
                
                // Send heartbeat
                if ($now - $lastHeartbeat >= $this->heartbeatInterval) {
                    $this->sendHeartbeat();
                    $lastHeartbeat = $now;
                }
                
                // Check for new events
                $events = $this->getEvents($streamType, $filters, $lastEventId);
                
                foreach ($events as $event) {
                    $this->sendEvent($event['type'], $event['data'], $event['id']);
                    $lastEventId = max($lastEventId, $event['id']);
                }
                
                // Flush output
                $this->flush();
                
                // Short sleep to prevent busy waiting
                usleep(250000); // 250ms
            }
        } catch (Exception $e) {
            $this->logger->error('SSE stream error', [
                'error' => $e->getMessage(),
                'stream_type' => $streamType,
                'request_id' => ApiBootstrap::getRequestId(),
            ]);
            
            $this->sendEvent('stream.error', [
                'message' => 'Stream error occurred',
                'error_code' => 'STREAM_ERROR'
            ]);
        } finally {
            $this->logger->info('SSE stream ended', [
                'stream_type' => $streamType,
                'duration' => time() - $startTime,
                'request_id' => ApiBootstrap::getRequestId(),
            ]);
        }
    }
    
    /**
     * Send SSE event
     */
    private function sendEvent(string $eventType, array $data, ?int $id = null): void
    {
        if ($id !== null) {
            echo "id: {$id}\n";
        }
        echo "event: {$eventType}\n";
        echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
    /**
     * Send heartbeat
     */
    private function sendHeartbeat(): void
    {
        $this->sendEvent('heartbeat', [
            'timestamp' => date('c'),
            'server_time' => time()
        ]);
    }
    
    /**
     * Flush output buffer
     */
    private function flush(): void
    {
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
    
    /**
     * Get last event ID from headers or request
     */
    private function getLastEventId(): int
    {
        // Check Last-Event-ID header (standard SSE reconnection)
        $lastEventId = $_SERVER['HTTP_LAST_EVENT_ID'] ?? null;
        
        // Check query parameter as fallback
        if (!$lastEventId) {
            $lastEventId = $_GET['lastEventId'] ?? '0';
        }
        
        return (int)$lastEventId;
    }
    
    /**
     * Get events based on stream type
     */
    private function getEvents(string $streamType, array $filters, int $lastEventId): array
    {
        switch ($streamType) {
            case 'conversations':
                return $this->getConversationEvents($filters, $lastEventId);
            
            default:
                $this->logger->warning('Unknown SSE stream type', ['type' => $streamType]);
                return [];
        }
    }
    
    /**
     * Get conversation events
     */
    private function getConversationEvents(array $filters, int $lastEventId): array
    {
        $conversationId = $filters['conversation_id'] ?? null;
        if (!$conversationId) {
            return [];
        }
        
        $events = [];
        
        // Get new messages
        $messageEvents = $this->getNewMessages($conversationId, $lastEventId);
        $events = array_merge($events, $messageEvents);
        
        // Get tool call updates
        $toolEvents = $this->getToolCallUpdates($conversationId, $lastEventId);
        $events = array_merge($events, $toolEvents);
        
        // Sort events by event_id
        usort($events, fn($a, $b) => $a['id'] <=> $b['id']);
        
        return $events;
    }
    
    /**
     * Get new messages for conversation
     */
    private function getNewMessages(string $conversationId, int $lastEventId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                message_id,
                role,
                content,
                metadata,
                UNIX_TIMESTAMP(created_at) as event_id,
                created_at
            FROM messages 
            WHERE conversation_id = ?
            AND UNIX_TIMESTAMP(created_at) > ?
            ORDER BY created_at ASC
            LIMIT 20
        ");
        
        $result = $stmt->execute([$conversationId, $lastEventId]);
        $events = [];
        
        while ($row = $result->fetch()) {
            $events[] = [
                'id' => (int)$row['event_id'],
                'type' => 'message.created',
                'data' => [
                    'message_id' => $row['message_id'],
                    'conversation_id' => $conversationId,
                    'role' => $row['role'],
                    'content' => $row['content'],
                    'metadata' => $row['metadata'] ? json_decode($row['metadata'], true) : null,
                    'created_at' => $row['created_at']
                ]
            ];
        }
        
        return $events;
    }
    
    /**
     * Get tool call updates
     */
    private function getToolCallUpdates(string $conversationId, int $lastEventId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                tool_call_id,
                tool_name,
                function_name,
                arguments,
                result,
                status,
                duration_ms,
                UNIX_TIMESTAMP(updated_at) as event_id,
                created_at,
                updated_at
            FROM tool_calls 
            WHERE conversation_id = ?
            AND UNIX_TIMESTAMP(updated_at) > ?
            ORDER BY updated_at ASC
            LIMIT 20
        ");
        
        $result = $stmt->execute([$conversationId, $lastEventId]);
        $events = [];
        
        while ($row = $result->fetch()) {
            $eventType = match($row['status']) {
                'pending' => 'tool_call.created',
                'running' => 'tool_call.started', 
                'completed' => 'tool_call.completed',
                'failed' => 'tool_call.failed',
                default => 'tool_call.updated'
            };
            
            $events[] = [
                'id' => (int)$row['event_id'],
                'type' => $eventType,
                'data' => [
                    'tool_call_id' => $row['tool_call_id'],
                    'conversation_id' => $conversationId,
                    'tool_name' => $row['tool_name'],
                    'function_name' => $row['function_name'],
                    'arguments' => $row['arguments'] ? json_decode($row['arguments'], true) : null,
                    'result' => $row['result'] ? json_decode($row['result'], true) : null,
                    'status' => $row['status'],
                    'duration_ms' => $row['duration_ms'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ]
            ];
        }
        
        return $events;
    }
}

// Handle SSE requests
try {
    // Authentication check
    require_once __DIR__ . '/auth.php';
    $auth = new ApiAuth();
    $apiKey = $auth->authenticate();
    
    // Get stream type and filters from path/query
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $streamType = trim($pathInfo, '/');
    
    // Parse filters from query parameters
    $filters = [];
    if (isset($_GET['conversation_id'])) {
        $filters['conversation_id'] = $_GET['conversation_id'];
    }
    
    // Validate stream type
    if (!in_array($streamType, ['conversations'])) {
        throw new InvalidArgumentException('Invalid stream type: ' . $streamType);
    }
    
    // Start SSE stream
    $sse = new SSEHandler();
    $sse->startStream($streamType, $filters);
    
} catch (Exception $e) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $e->getMessage(),
            'type' => 'sse_error'
        ],
        'request_id' => ApiBootstrap::getRequestId()
    ], JSON_UNESCAPED_UNICODE);
}