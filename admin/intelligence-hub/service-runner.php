#!/usr/bin/env php
<?php
/**
 * Intelligence Hub - Service Runner
 *
 * Manages all system components:
 * - MCP Health Monitor
 * - Agent Scheduler
 * - System Health Checks
 *
 * Runs as a systemd service with auto-restart
 */

declare(strict_types=1);

// Load environment
$envFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value, '"\'');
            $_SERVER[trim($key)] = trim($value, '"\'');
        }
    }
}

// Logging
function logMessage(string $level, string $message): void {
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] [{$level}] {$message}\n";
}

logMessage('INFO', 'Intelligence Hub Service Starting...');

// Database connection
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_NAME'] ?? 'hdgwrzntwa',
            $_ENV['DB_CHARSET'] ?? 'utf8mb4'
        ),
        $_ENV['DB_USER'] ?? 'hdgwrzntwa',
        $_ENV['DB_PASS'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    logMessage('INFO', 'Database connected');
} catch (PDOException $e) {
    logMessage('ERROR', 'Database connection failed: ' . $e->getMessage());
    exit(1);
}

// Load agent classes
$agentFiles = [
    __DIR__ . '/agents/InventoryAgent.php',
    __DIR__ . '/agents/WebMonitorAgent.php',
    __DIR__ . '/agents/SecurityAgent.php'
];

$agents = [];
foreach ($agentFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
        $className = basename($file, '.php');
        if (class_exists($className)) {
            $agents[] = new $className($pdo);
            logMessage('INFO', "Loaded agent: {$className}");
        }
    }
}

logMessage('INFO', sprintf('Loaded %d agents', count($agents)));

// Service state
$lastHealthCheck = 0;
$lastAgentRun = 0;
$lastMcpCheck = 0;
$running = true;

// Signal handlers
pcntl_signal(SIGTERM, function() use (&$running) {
    logMessage('INFO', 'Received SIGTERM, shutting down gracefully...');
    $running = false;
});

pcntl_signal(SIGINT, function() use (&$running) {
    logMessage('INFO', 'Received SIGINT, shutting down gracefully...');
    $running = false;
});

logMessage('INFO', '✓ Service fully operational');
logMessage('INFO', 'Starting main service loop...');

// Main service loop
$iteration = 0;
while ($running) {
    pcntl_signal_dispatch();
    $now = time();
    $iteration++;

    // Every 60 seconds: Run agents
    if ($now - $lastAgentRun >= 60) {
        logMessage('INFO', "=== Agent Run #{$iteration} ===");

        foreach ($agents as $agent) {
            $agentName = get_class($agent);

            try {
                if (method_exists($agent, 'shouldRun') && $agent->shouldRun()) {
                    logMessage('INFO', "Executing: {$agentName}");

                    $startTime = microtime(true);
                    $result = $agent->execute();
                    $duration = round((microtime(true) - $startTime) * 1000, 2);

                    // Store run record
                    $stmt = $pdo->prepare(
                        "INSERT INTO agent_runs (agent_name, status, duration_ms, result_data, executed_at)
                         VALUES (?, ?, ?, ?, NOW())"
                    );
                    $stmt->execute([
                        $agentName,
                        $result['success'] ? 'success' : 'failed',
                        $duration,
                        json_encode($result)
                    ]);

                    logMessage('INFO', sprintf(
                        "%s completed in %.2fms - Status: %s",
                        $agentName,
                        $duration,
                        $result['success'] ? 'SUCCESS' : 'FAILED'
                    ));
                } else {
                    logMessage('DEBUG', "{$agentName} skipped (shouldRun returned false)");
                }
            } catch (Exception $e) {
                logMessage('ERROR', "{$agentName} failed: " . $e->getMessage());

                // Store error
                $stmt = $pdo->prepare(
                    "INSERT INTO agent_runs (agent_name, status, error_message, executed_at)
                     VALUES (?, 'error', ?, NOW())"
                );
                $stmt->execute([$agentName, $e->getMessage()]);
            }
        }

        $lastAgentRun = $now;
    }

    // Every 30 seconds: MCP health check
    if ($now - $lastMcpCheck >= 30) {
        logMessage('DEBUG', 'Checking MCP server health...');

        $mcpUrl = $_ENV['MCP_SERVER_URL'] ?? '';
        $mcpKey = $_ENV['MCP_API_KEY'] ?? '';

        if ($mcpUrl && $mcpKey) {
            $ch = curl_init($mcpUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-API-Key: ' . $mcpKey
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'jsonrpc' => '2.0',
                    'method' => 'tools/list',
                    'params' => [],
                    'id' => 1
                ]),
                CURLOPT_TIMEOUT => 5
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
            curl_close($ch);

            $toolCount = 0;
            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                $toolCount = isset($data['result']['tools']) ? count($data['result']['tools']) : 0;
            }

            // Log to database
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO mcp_health_log (status, http_code, response_time_ms, tool_count, checked_at)
                     VALUES (?, ?, ?, ?, NOW())"
                );
                $status = ($httpCode === 200) ? 'healthy' : (($httpCode === 401) ? 'degraded' : 'down');
                $stmt->execute([$status, $httpCode, $responseTime, $toolCount]);

                logMessage('DEBUG', sprintf(
                    "MCP: HTTP %d, %d tools, %.2fms",
                    $httpCode,
                    $toolCount,
                    $responseTime
                ));
            } catch (Exception $e) {
                logMessage('ERROR', 'Failed to log MCP health: ' . $e->getMessage());
            }
        }

        $lastMcpCheck = $now;
    }

    // Every 300 seconds (5 min): System health check
    if ($now - $lastHealthCheck >= 300) {
        logMessage('INFO', 'Running system health check...');

        // Check disk space
        $diskFree = disk_free_space('/home/129337.cloudwaysapps.com/hdgwrzntwa');
        $diskTotal = disk_total_space('/home/129337.cloudwaysapps.com/hdgwrzntwa');
        $diskPercent = round(($diskFree / $diskTotal) * 100, 1);

        logMessage('INFO', sprintf('Disk space: %.1f%% free', $diskPercent));

        if ($diskPercent < 10) {
            logMessage('WARNING', 'LOW DISK SPACE!');
        }

        // Check memory
        $memUsage = memory_get_usage(true);
        logMessage('INFO', sprintf('Memory usage: %s', number_format($memUsage / 1024 / 1024, 2) . 'MB'));

        $lastHealthCheck = $now;
    }

    // Sleep for 1 second
    sleep(1);
}

logMessage('INFO', 'Service shutdown complete');
exit(0);
