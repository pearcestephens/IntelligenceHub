<?php

namespace BotDeployment\Services;

use BotDeployment\Config\Connection;
use BotDeployment\Services\Logger;
use Exception;

/**
 * WebSocket Server for Real-Time Updates
 *
 * Provides real-time push updates to dashboard clients:
 * - Live bot execution status
 * - Real-time logs
 * - System health updates
 * - Bot status changes
 *
 * Uses Ratchet WebSocket library (composer require cboden/ratchet)
 */
class WebSocketServer
{
    private $logger;
    private $clients;
    private $subscriptions;
    private $db;

    // Event types
    const EVENT_BOT_STARTED = 'bot.started';
    const EVENT_BOT_COMPLETED = 'bot.completed';
    const EVENT_BOT_FAILED = 'bot.failed';
    const EVENT_BOT_LOG = 'bot.log';
    const EVENT_SYSTEM_HEALTH = 'system.health';
    const EVENT_METRIC_UPDATE = 'metric.update';

    public function __construct()
    {
        $this->logger = new Logger('websocket');
        $this->clients = [];
        $this->subscriptions = [];
        $this->db = Connection::getInstance();
    }

    /**
     * Start WebSocket server
     *
     * @param string $host Host to bind to
     * @param int $port Port to listen on
     */
    public function start(string $host = '0.0.0.0', int $port = 8080): void
    {
        try {
            $this->logger->info("Starting WebSocket server on {$host}:{$port}");

            // Check if Ratchet is available
            if (!class_exists('Ratchet\Server\IoServer')) {
                throw new Exception(
                    "Ratchet library not found. Install with: composer require cboden/ratchet"
                );
            }

            $server = \Ratchet\Server\IoServer::factory(
                new \Ratchet\Http\HttpServer(
                    new \Ratchet\WebSocket\WsServer(
                        $this
                    )
                ),
                $port,
                $host
            );

            $this->logger->info("WebSocket server started successfully");
            $server->run();

        } catch (Exception $e) {
            $this->logger->error("Failed to start WebSocket server", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle new client connection
     */
    public function onOpen($conn): void
    {
        $clientId = $conn->resourceId;
        $this->clients[$clientId] = $conn;
        $this->subscriptions[$clientId] = [];

        $this->logger->info("Client connected", ['client_id' => $clientId]);

        // Send welcome message
        $this->sendToClient($conn, [
            'type' => 'connection',
            'status' => 'connected',
            'client_id' => $clientId,
            'timestamp' => time()
        ]);
    }

    /**
     * Handle client message
     */
    public function onMessage($from, $msg): void
    {
        $clientId = $from->resourceId;

        try {
            $data = json_decode($msg, true);

            if (!$data || !isset($data['action'])) {
                throw new Exception("Invalid message format");
            }

            switch ($data['action']) {
                case 'subscribe':
                    $this->handleSubscribe($from, $data);
                    break;

                case 'unsubscribe':
                    $this->handleUnsubscribe($from, $data);
                    break;

                case 'ping':
                    $this->handlePing($from);
                    break;

                default:
                    throw new Exception("Unknown action: " . $data['action']);
            }

        } catch (Exception $e) {
            $this->logger->warning("Invalid message from client", [
                'client_id' => $clientId,
                'error' => $e->getMessage()
            ]);

            $this->sendToClient($from, [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle client disconnect
     */
    public function onClose($conn): void
    {
        $clientId = $conn->resourceId;

        unset($this->clients[$clientId]);
        unset($this->subscriptions[$clientId]);

        $this->logger->info("Client disconnected", ['client_id' => $clientId]);
    }

    /**
     * Handle error
     */
    public function onError($conn, Exception $e): void
    {
        $clientId = $conn->resourceId ?? 'unknown';

        $this->logger->error("WebSocket error", [
            'client_id' => $clientId,
            'error' => $e->getMessage()
        ]);

        $conn->close();
    }

    /**
     * Handle subscribe action
     */
    private function handleSubscribe($conn, array $data): void
    {
        $clientId = $conn->resourceId;
        $channel = $data['channel'] ?? null;

        if (!$channel) {
            throw new Exception("Channel is required");
        }

        // Add subscription
        if (!in_array($channel, $this->subscriptions[$clientId])) {
            $this->subscriptions[$clientId][] = $channel;
        }

        $this->logger->debug("Client subscribed", [
            'client_id' => $clientId,
            'channel' => $channel
        ]);

        // Send confirmation
        $this->sendToClient($conn, [
            'type' => 'subscribed',
            'channel' => $channel,
            'timestamp' => time()
        ]);

        // Send initial data for the channel
        $this->sendInitialData($conn, $channel);
    }

    /**
     * Handle unsubscribe action
     */
    private function handleUnsubscribe($conn, array $data): void
    {
        $clientId = $conn->resourceId;
        $channel = $data['channel'] ?? null;

        if (!$channel) {
            throw new Exception("Channel is required");
        }

        // Remove subscription
        $this->subscriptions[$clientId] = array_filter(
            $this->subscriptions[$clientId],
            fn($c) => $c !== $channel
        );

        $this->logger->debug("Client unsubscribed", [
            'client_id' => $clientId,
            'channel' => $channel
        ]);

        $this->sendToClient($conn, [
            'type' => 'unsubscribed',
            'channel' => $channel,
            'timestamp' => time()
        ]);
    }

    /**
     * Handle ping
     */
    private function handlePing($conn): void
    {
        $this->sendToClient($conn, [
            'type' => 'pong',
            'timestamp' => time()
        ]);
    }

    /**
     * Send initial data for a channel
     */
    private function sendInitialData($conn, string $channel): void
    {
        try {
            switch ($channel) {
                case 'bots':
                    $this->sendBotsList($conn);
                    break;

                case 'executions':
                    $this->sendRecentExecutions($conn);
                    break;

                case 'health':
                    $this->sendSystemHealth($conn);
                    break;

                case 'metrics':
                    $this->sendMetrics($conn);
                    break;
            }
        } catch (Exception $e) {
            $this->logger->error("Failed to send initial data", [
                'channel' => $channel,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send bots list to client
     */
    private function sendBotsList($conn): void
    {
        $stmt = $this->db->query("
            SELECT id, name, status, last_run_at, next_run_at
            FROM bots
            ORDER BY created_at DESC
            LIMIT 100
        ");

        $bots = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->sendToClient($conn, [
            'type' => 'data',
            'channel' => 'bots',
            'data' => $bots,
            'timestamp' => time()
        ]);
    }

    /**
     * Send recent executions to client
     */
    private function sendRecentExecutions($conn): void
    {
        $stmt = $this->db->query("
            SELECT
                e.id, e.bot_id, e.status, e.started_at, e.completed_at,
                e.duration, e.result,
                b.name as bot_name
            FROM bot_executions e
            JOIN bots b ON b.id = e.bot_id
            ORDER BY e.started_at DESC
            LIMIT 50
        ");

        $executions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->sendToClient($conn, [
            'type' => 'data',
            'channel' => 'executions',
            'data' => $executions,
            'timestamp' => time()
        ]);
    }

    /**
     * Send system health to client
     */
    private function sendSystemHealth($conn): void
    {
        $health = [
            'timestamp' => time(),
            'status' => 'healthy',
            'metrics' => [
                'active_bots' => $this->getActiveBotsCount(),
                'running_executions' => $this->getRunningExecutionsCount(),
                'recent_failures' => $this->getRecentFailuresCount(),
                'uptime' => $this->getSystemUptime()
            ]
        ];

        $this->sendToClient($conn, [
            'type' => 'data',
            'channel' => 'health',
            'data' => $health,
            'timestamp' => time()
        ]);
    }

    /**
     * Send metrics to client
     */
    private function sendMetrics($conn): void
    {
        // Get metrics from MetricsCollector
        $metricsCollector = new MetricsCollector();
        $metrics = $metricsCollector->getAllMetrics();

        $this->sendToClient($conn, [
            'type' => 'data',
            'channel' => 'metrics',
            'data' => $metrics,
            'timestamp' => time()
        ]);
    }

    /**
     * Broadcast event to all subscribed clients
     *
     * @param string $eventType Event type constant
     * @param array $data Event data
     * @param string|null $channel Optional specific channel
     */
    public function broadcast(string $eventType, array $data, ?string $channel = null): void
    {
        $message = [
            'type' => 'event',
            'event' => $eventType,
            'data' => $data,
            'channel' => $channel,
            'timestamp' => time()
        ];

        foreach ($this->clients as $clientId => $client) {
            // If channel specified, only send to subscribed clients
            if ($channel && !in_array($channel, $this->subscriptions[$clientId] ?? [])) {
                continue;
            }

            $this->sendToClient($client, $message);
        }

        $this->logger->debug("Broadcast sent", [
            'event' => $eventType,
            'channel' => $channel,
            'clients' => count($this->clients)
        ]);
    }

    /**
     * Send message to specific client
     */
    private function sendToClient($conn, array $data): void
    {
        try {
            $conn->send(json_encode($data));
        } catch (Exception $e) {
            $this->logger->error("Failed to send to client", [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get active bots count
     */
    private function getActiveBotsCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM bots WHERE status = 'active'");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get running executions count
     */
    private function getRunningExecutionsCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM bot_executions WHERE status = 'running'");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get recent failures count
     */
    private function getRecentFailuresCount(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM bot_executions
            WHERE status = 'failed'
            AND started_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): int
    {
        // Get first bot creation time as system start time
        $stmt = $this->db->query("SELECT MIN(created_at) FROM bots");
        $startTime = $stmt->fetchColumn();

        if ($startTime) {
            return time() - strtotime($startTime);
        }

        return 0;
    }

    /**
     * Get connected clients count
     */
    public function getConnectedClientsCount(): int
    {
        return count($this->clients);
    }

    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats(): array
    {
        $stats = [];

        foreach ($this->subscriptions as $clientId => $channels) {
            foreach ($channels as $channel) {
                if (!isset($stats[$channel])) {
                    $stats[$channel] = 0;
                }
                $stats[$channel]++;
            }
        }

        return $stats;
    }
}
