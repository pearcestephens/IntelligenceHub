<?php

/**
 * Server-Sent Events (SSE) handler
 * Provides real-time progress streaming for AI agent operations
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

class SSE
{
    private static array $connections = [];
    private static bool $headersSet = false;

    /**
     * Optional constructor for dependency injection compatibility.
     */
    public function __construct(?Logger $logger = null)
    {
        // No instance state; static methods manage SSE. Provided for DI compatibility.
    }

    /**
     * Start SSE connection for conversation
     */
    public static function start(string $conversationId): void
    {
        if (!self::$headersSet) {
            self::setHeaders();
        }

        self::$connections[$conversationId] = true;

        Logger::info('SSE connection started', [
            'conversation_id' => $conversationId
        ]);

        // Send initial connection event
        self::emit($conversationId, 'connection', [
            'status' => 'connected',
            'timestamp' => date('c')
        ]);

        // Start keep-alive loop
        self::startKeepAlive($conversationId);
    }

    /**
     * Emit event to conversation
     */
    public static function emit(string $conversationId, string $type, array $data = []): void
    {
        if (!isset(self::$connections[$conversationId])) {
            return;
        }

        $event = [
            'type' => $type,
            'data' => $data,
            'timestamp' => date('c'),
            'conversation_id' => $conversationId
        ];

        $output = "data: " . json_encode($event) . "\n\n";

        echo $output;

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        Logger::debug('SSE event emitted', [
            'conversation_id' => $conversationId,
            'type' => $type,
            'data_size' => strlen(json_encode($data))
        ]);
    }



    /**
     * Emit phase transition event
     */
    public static function emitPhase(string $conversationId, string $phase): void
    {
        self::emit($conversationId, 'phase', [
            'label' => $phase
        ]);
    }

    /**
     * Emit tool batch start event
     */
    public static function emitToolBatch(string $conversationId, int $count): void
    {
        self::emit($conversationId, 'tool_batch', [
            'count' => $count
        ]);
    }

    /**
     * Emit tool start event
     */
    public static function emitToolStart(string $conversationId, string $toolName, array $args): void
    {
        self::emit($conversationId, 'tool_start', [
            'tool' => $toolName,
            'args' => self::sanitizeArgs($args)
        ]);
    }

    /**
     * Emit tool end event
     */
    public static function emitToolEnd(string $conversationId, string $toolName, int $durationMs, bool $success, ?string $error = null): void
    {
        $data = [
            'tool' => $toolName,
            'ms' => $durationMs,
            'ok' => $success
        ];

        if ($error) {
            $data['error'] = $error;
        }

        self::emit($conversationId, 'tool_end', $data);
    }

    /**
     * Emit progress update
     */
    public static function emitProgress(string $conversationId, string $message, array $context = []): void
    {
        self::emit($conversationId, 'progress', [
            'message' => $message,
            'context' => $context
        ]);
    }

    /**
     * Emit completion event
     */
    public static function emitComplete(string $conversationId, array $summary = []): void
    {
        self::emit($conversationId, 'complete', $summary);
    }

    /**
     * Emit error event
     */
    public static function emitError(string $conversationId, string $message, string $code = 'UNKNOWN'): void
    {
        self::emit($conversationId, 'error', [
            'message' => $message,
            'code' => $code
        ]);
    }

    /**
     * Close SSE connection
     */
    public static function close(string $conversationId): void
    {
        if (isset(self::$connections[$conversationId])) {
            self::emit($conversationId, 'close', [
                'status' => 'disconnected'
            ]);

            unset(self::$connections[$conversationId]);

            Logger::info('SSE connection closed', [
                'conversation_id' => $conversationId
            ]);
        }
    }

    /**
     * Set SSE headers
     */
    private static function setHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Cache-Control');

        // Disable output buffering for immediate streaming
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Disable PHP execution time limit for long-running streams
        set_time_limit(0);

        self::$headersSet = true;

        // Provide a client reconnection hint (milliseconds) per SSE spec
        // Allows clients to know how long to wait before retrying the connection
        $retryMs = (int)Config::get('SSE_RETRY_MS', 10000);
        echo "retry: {$retryMs}\n\n";
        flush();
    }

    /**
     * Start keep-alive mechanism
     */
    private static function startKeepAlive(string $conversationId): void
    {
        $keepaliveInterval = Config::get('SSE_KEEPALIVE_SEC', 20);

        register_shutdown_function(function () use ($conversationId) {
            self::close($conversationId);
        });

        // Send initial keep-alive
        echo ": keep-alive\n\n";
        flush();
    }

    /**
     * Send keep-alive comment
     */
    public static function keepAlive(): void
    {
        echo ": " . date('c') . "\n\n";

        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    /**
     * Handle client disconnect detection
     */
    public static function checkConnection(): bool
    {
        if (connection_aborted()) {
            Logger::info('SSE client disconnected');
            return false;
        }

        return true;
    }

    /**
     * Sanitize tool arguments for display (remove sensitive data)
     */
    private static function sanitizeArgs(array $args): array
    {
        $sensitive = [
            'password',
            'api_key',
            'token',
            'secret',
            'authorization'
        ];

        array_walk_recursive($args, function (&$value, $key) use ($sensitive) {
            if (is_string($key) && in_array(strtolower($key), $sensitive, true)) {
                $value = '[HIDDEN]';
            } elseif (is_string($value) && strlen($value) > 200) {
                $value = substr($value, 0, 197) . '...';
            }
        });

        return $args;
    }

    /**
     * Send event (compatibility shim for MessageHandler)
     * evt: ['type' => '...', 'data' => [...]]
     */
    public function send(array $evt): void
    {
        $conv = $evt['data']['conversation_id'] ?? $evt['conversation_id'] ?? null;
        if (!$conv) {
            return;
        }
        $type = $evt['type'] ?? 'event';
        $payload = $evt['data'] ?? [];
        self::emit($conv, $type, $payload);
    }

    /**
     * Get active connection count
     */
    public static function getActiveConnections(): int
    {
        return count(self::$connections);
    }

    /**
     * Get connection IDs
     */
    public static function getConnectionIds(): array
    {
        return array_keys(self::$connections);
    }
}
