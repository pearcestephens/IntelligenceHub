<?php
/**
 * MCP Health Monitor Service
 *
 * Runs continuously to monitor MCP server health
 * Logs status, tracks uptime, alerts on failures
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(ticks=1);

// Load environment from multiple possible locations
$envPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../../private_html/config/.env',
    '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env'
];

foreach ($envPaths as $envPath) {
    if (file_exists($envPath) && is_readable($envPath)) {
        $env = parse_ini_file($envPath);
        if ($env && is_array($env)) {
            foreach ($env as $key => $value) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        break;
    }
}

$MCP_SERVER_URL = $_ENV['MCP_SERVER_URL'] ?? 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';
$MCP_API_KEY = $_ENV['MCP_API_KEY'] ?? '';
$CHECK_INTERVAL = 60; // Check every 60 seconds
$LOG_FILE = __DIR__ . '/logs/health-monitor.log';

// Ensure log directory exists
$logDir = dirname($LOG_FILE);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// Handle signals for graceful shutdown
$running = true;
pcntl_signal(SIGTERM, function() use (&$running) {
    global $running;
    $running = false;
    logMessage("Received SIGTERM, shutting down gracefully...");
});
pcntl_signal(SIGINT, function() use (&$running) {
    global $running;
    $running = false;
    logMessage("Received SIGINT, shutting down gracefully...");
});

/**
 * Log message with timestamp
 */
function logMessage(string $message): void {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($LOG_FILE, $logEntry, FILE_APPEND);
    echo $logEntry; // Also output to stdout for systemd journal
}

/**
 * Check MCP server health
 */
function checkHealth(): array {
    global $MCP_SERVER_URL, $MCP_API_KEY;

    $startTime = microtime(true);

    $ch = curl_init($MCP_SERVER_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $MCP_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'params' => [],
            'id' => 1
        ]),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $responseTime = round((microtime(true) - $startTime) * 1000, 2);

    $result = [
        'status' => 'unknown',
        'http_code' => $httpCode,
        'response_time_ms' => $responseTime,
        'error' => $error,
        'tool_count' => 0
    ];

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['result']['tools'])) {
            $result['status'] = 'healthy';
            $result['tool_count'] = count($data['result']['tools']);
        } else {
            $result['status'] = 'degraded';
        }
    } else {
        $result['status'] = 'down';
    }

    return $result;
}

/**
 * Store health metrics in database
 */
function storeMetrics(array $health): void {
    try {
        $dbHost = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1';
        $dbName = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'hdgwrzntwa_cis';
        $dbUser = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'hdgwrzntwa';
        $dbPass = $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? '';

        $db = new PDO(
            "mysql:host={$dbHost};dbname={$dbName}",
            $dbUser,
            $dbPass
        );

        $stmt = $db->prepare('
            INSERT INTO mcp_health_log
            (status, http_code, response_time_ms, tool_count, error, checked_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');

        $stmt->execute([
            $health['status'],
            $health['http_code'],
            $health['response_time_ms'],
            $health['tool_count'],
            $health['error'] ?: null
        ]);

    } catch (Exception $e) {
        logMessage("Failed to store metrics: " . $e->getMessage());
    }
}

/**
 * Send alert if server is down
 */
function sendAlert(array $health): void {
    // TODO: Integrate with notification system
    logMessage("ALERT: MCP Server is {$health['status']}!");
}

// Main monitoring loop
logMessage("MCP Health Monitor started");
logMessage("Monitoring: {$MCP_SERVER_URL}");
logMessage("Check interval: {$CHECK_INTERVAL} seconds");

$consecutiveFailures = 0;
$lastStatus = 'unknown';

while ($running) {
    pcntl_signal_dispatch();

    $health = checkHealth();

    // Log status change
    if ($health['status'] !== $lastStatus) {
        logMessage("Status changed: {$lastStatus} -> {$health['status']}");
        $lastStatus = $health['status'];
    }

    // Log detailed health info
    logMessage(sprintf(
        "Health check: %s | HTTP %d | %d tools | %sms",
        strtoupper($health['status']),
        $health['http_code'],
        $health['tool_count'],
        $health['response_time_ms']
    ));

    // Track failures
    if ($health['status'] === 'down') {
        $consecutiveFailures++;
        if ($consecutiveFailures >= 3) {
            sendAlert($health);
        }
    } else {
        $consecutiveFailures = 0;
    }

    // Store metrics
    storeMetrics($health);

    // Sleep until next check
    sleep($CHECK_INTERVAL);
}

logMessage("MCP Health Monitor stopped");
