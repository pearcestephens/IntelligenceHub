#!/usr/bin/env php
<?php
/**
 * WebSocket Server Daemon
 *
 * Start the WebSocket server for real-time updates
 *
 * Usage:
 *   ./websocket-server.php [start|stop|restart|status]
 *   ./websocket-server.php --port=8080 --host=0.0.0.0
 */

require_once __DIR__ . '/vendor/autoload.php';

use BotDeployment\Services\WebSocketServer;
use BotDeployment\Services\Logger;

// Parse command line arguments
$options = getopt('', ['port::', 'host::']);
$action = $argv[1] ?? 'start';

$host = $options['host'] ?? '0.0.0.0';
$port = (int) ($options['port'] ?? 8080);

$pidFile = __DIR__ . '/websocket-server.pid';
$logger = new Logger('websocket-daemon');

/**
 * Start the server
 */
function startServer(string $host, int $port, string $pidFile, Logger $logger): void
{
    // Check if already running
    if (file_exists($pidFile)) {
        $pid = (int) file_get_contents($pidFile);
        if (posix_kill($pid, 0)) {
            echo "WebSocket server is already running (PID: {$pid})\n";
            exit(1);
        }
        // Stale PID file, remove it
        unlink($pidFile);
    }

    echo "Starting WebSocket server on {$host}:{$port}...\n";

    // Fork to background
    $pid = pcntl_fork();

    if ($pid === -1) {
        die("Failed to fork process\n");
    } elseif ($pid === 0) {
        // Child process - start the server
        try {
            $server = new WebSocketServer();
            file_put_contents($pidFile, posix_getpid());
            $logger->info("WebSocket server started", ['host' => $host, 'port' => $port]);
            $server->start($host, $port);
        } catch (Exception $e) {
            $logger->error("WebSocket server failed", ['error' => $e->getMessage()]);
            if (file_exists($pidFile)) {
                unlink($pidFile);
            }
            exit(1);
        }
    } else {
        // Parent process
        echo "WebSocket server started successfully (PID: {$pid})\n";
        echo "Clients can connect to: ws://{$host}:{$port}\n";
        exit(0);
    }
}

/**
 * Stop the server
 */
function stopServer(string $pidFile, Logger $logger): void
{
    if (!file_exists($pidFile)) {
        echo "WebSocket server is not running\n";
        exit(1);
    }

    $pid = (int) file_get_contents($pidFile);

    echo "Stopping WebSocket server (PID: {$pid})...\n";

    if (posix_kill($pid, SIGTERM)) {
        // Wait for process to terminate
        $timeout = 10;
        while ($timeout > 0 && posix_kill($pid, 0)) {
            sleep(1);
            $timeout--;
        }

        if ($timeout === 0) {
            echo "Force killing server...\n";
            posix_kill($pid, SIGKILL);
        }

        unlink($pidFile);
        echo "WebSocket server stopped successfully\n";
        $logger->info("WebSocket server stopped");
    } else {
        echo "Failed to stop server (PID: {$pid})\n";
        exit(1);
    }
}

/**
 * Get server status
 */
function getStatus(string $pidFile): void
{
    if (!file_exists($pidFile)) {
        echo "WebSocket server is not running\n";
        exit(1);
    }

    $pid = (int) file_get_contents($pidFile);

    if (posix_kill($pid, 0)) {
        echo "WebSocket server is running (PID: {$pid})\n";

        // Get process info
        $stat = file_get_contents("/proc/{$pid}/stat");
        if ($stat) {
            $parts = explode(' ', $stat);
            $startTime = (int) ($parts[21] ?? 0);
            $uptime = time() - ($startTime / 100);
            echo "Uptime: " . gmdate('H:i:s', $uptime) . "\n";
        }

        exit(0);
    } else {
        echo "WebSocket server is not running (stale PID file)\n";
        unlink($pidFile);
        exit(1);
    }
}

// Handle action
try {
    switch ($action) {
        case 'start':
            startServer($host, $port, $pidFile, $logger);
            break;

        case 'stop':
            stopServer($pidFile, $logger);
            break;

        case 'restart':
            stopServer($pidFile, $logger);
            sleep(1);
            startServer($host, $port, $pidFile, $logger);
            break;

        case 'status':
            getStatus($pidFile);
            break;

        default:
            echo "Usage: {$argv[0]} [start|stop|restart|status]\n";
            echo "Options:\n";
            echo "  --host=HOST    Host to bind to (default: 0.0.0.0)\n";
            echo "  --port=PORT    Port to listen on (default: 8080)\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $logger->error("WebSocket daemon error", ['error' => $e->getMessage()]);
    exit(1);
}
