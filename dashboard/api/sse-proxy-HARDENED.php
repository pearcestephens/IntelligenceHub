<?php
/**
 * SSE Proxy - HARDENED VERSION
 * 
 * Connects dashboard to internal SSE server with enterprise security
 * 
 * SECURITY FEATURES:
 * - Connection limits (10 max concurrent)
 * - Reduced timeout (60 seconds, not 3600)
 * - Heartbeat monitoring (30 second timeout)
 * - CSRF protection
 * - Rate limiting (5 connections per minute)
 * - Authentication required
 * - Input validation
 * - Error recovery
 * - Resource monitoring
 * - Automatic cleanup
 * 
 * @package Intelligence Hub
 * @version 2.0.0 - HARDENED
 */

// ============================================================================
// SECURITY SERVICES - LOAD FIRST
// ============================================================================

require_once __DIR__ . '/../../services/CSRFProtection.php';
require_once __DIR__ . '/../../services/RateLimiter.php';
require_once __DIR__ . '/../../services/InputValidator.php';
require_once __DIR__ . '/../../services/SecurityMonitor.php';

// Start session for authentication
session_start();

// ============================================================================
// CONFIGURATION - HARDENED
// ============================================================================

define('SSE_HOST', '127.0.0.1');
define('SSE_PORT', 4000);
define('SSE_BASE_URL', 'http://' . SSE_HOST . ':' . SSE_PORT);

// SECURITY: Connection limits
define('MAX_SSE_CONNECTIONS', 10);              // Global limit
define('MAX_SSE_CONNECTIONS_PER_IP', 2);        // Per IP limit
define('SSE_CONNECTION_TIMEOUT', 60);            // 60 seconds (not 3600!)
define('SSE_IDLE_TIMEOUT', 30);                  // Close if idle 30 seconds
define('SSE_HEARTBEAT_INTERVAL', 10);            // Check heartbeat every 10s
define('SSE_MAX_RUNTIME', 90);                   // Maximum 90 seconds total

// Connection tracking file
define('SSE_CONNECTIONS_FILE', '/tmp/sse_proxy_connections.json');
define('SSE_LOG_FILE', __DIR__ . '/../../logs/sse-proxy.log');

// ============================================================================
// SECURITY LAYER - AUTHENTICATION & AUTHORIZATION
// ============================================================================

/**
 * Validate authentication
 */
function validateAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Authentication required',
            'message' => 'Please login to access SSE streaming'
        ]);
        SecurityMonitor::logSecurityEvent('sse_unauthorized_access', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'endpoint' => $_SERVER['REQUEST_URI']
        ]);
        exit;
    }
}

/**
 * Validate CSRF token (for POST requests)
 */
function validateCSRF() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!CSRFProtection::validate($token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'CSRF validation failed'
            ]);
            SecurityMonitor::logSecurityEvent('sse_csrf_failure', [
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            exit;
        }
    }
}

/**
 * Check rate limits
 */
function validateRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // 5 SSE connections per minute per IP
    if (!RateLimiter::check($ip, 'sse_connections', 5, 60)) {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: 60');
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded',
            'message' => 'Maximum 5 SSE connections per minute',
            'retry_after' => 60
        ]);
        SecurityMonitor::logSecurityEvent('sse_rate_limit', [
            'ip' => $ip,
            'limit' => 5,
            'window' => 60
        ]);
        exit;
    }
}

// ============================================================================
// CONNECTION MANAGEMENT - PREVENT RESOURCE EXHAUSTION
// ============================================================================

/**
 * Get current active connections
 */
function getActiveConnections() {
    if (!file_exists(SSE_CONNECTIONS_FILE)) {
        return [];
    }
    
    $connections = json_decode(file_get_contents(SSE_CONNECTIONS_FILE), true);
    if (!is_array($connections)) {
        return [];
    }
    
    // Clean stale connections (older than 2 minutes or dead processes)
    $now = time();
    $connections = array_filter($connections, function($conn) use ($now) {
        // Remove if older than 2 minutes
        if ($now - $conn['time'] > 120) {
            return false;
        }
        
        // Check if process still exists
        if (isset($conn['pid'])) {
            exec("ps -p {$conn['pid']} > /dev/null 2>&1", $output, $return);
            if ($return !== 0) {
                return false; // Process doesn't exist
            }
        }
        
        return true;
    });
    
    // Save cleaned connections
    file_put_contents(SSE_CONNECTIONS_FILE, json_encode($connections));
    
    return $connections;
}

/**
 * Check connection limits
 */
function checkConnectionLimit() {
    $connections = getActiveConnections();
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Check global limit
    if (count($connections) >= MAX_SSE_CONNECTIONS) {
        http_response_code(503);
        header('Content-Type: application/json');
        header('Retry-After: 30');
        echo json_encode([
            'success' => false,
            'error' => 'Server at capacity',
            'message' => 'Maximum ' . MAX_SSE_CONNECTIONS . ' concurrent SSE connections',
            'active_connections' => count($connections),
            'retry_after' => 30
        ]);
        logMessage("Connection limit reached: " . count($connections) . " active");
        exit;
    }
    
    // Check per-IP limit
    $ipConnections = array_filter($connections, function($conn) use ($ip) {
        return $conn['ip'] === $ip;
    });
    
    if (count($ipConnections) >= MAX_SSE_CONNECTIONS_PER_IP) {
        http_response_code(429);
        header('Content-Type: application/json');
        header('Retry-After: 30');
        echo json_encode([
            'success' => false,
            'error' => 'Too many connections from your IP',
            'message' => 'Maximum ' . MAX_SSE_CONNECTIONS_PER_IP . ' connections per IP',
            'your_connections' => count($ipConnections),
            'retry_after' => 30
        ]);
        logMessage("Per-IP limit reached for $ip: " . count($ipConnections));
        exit;
    }
    
    return true;
}

/**
 * Register new connection
 */
function registerConnection() {
    $connections = getActiveConnections();
    
    $connId = uniqid('sse_', true);
    $connections[$connId] = [
        'id' => $connId,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_id' => $_SESSION['user_id'] ?? null,
        'time' => time(),
        'pid' => getmypid(),
        'start_time' => microtime(true)
    ];
    
    file_put_contents(SSE_CONNECTIONS_FILE, json_encode($connections));
    
    logMessage("Connection registered: $connId (IP: {$_SERVER['REMOTE_ADDR']}, Total: " . count($connections) . ")");
    
    return $connId;
}

/**
 * Unregister connection
 */
function unregisterConnection($connId) {
    if (!$connId) return;
    
    $connections = getActiveConnections();
    
    if (isset($connections[$connId])) {
        $duration = microtime(true) - $connections[$connId]['start_time'];
        unset($connections[$connId]);
        file_put_contents(SSE_CONNECTIONS_FILE, json_encode($connections));
        
        logMessage("Connection unregistered: $connId (Duration: " . round($duration, 2) . "s, Remaining: " . count($connections) . ")");
    }
}

// ============================================================================
// LOGGING
// ============================================================================

function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[$timestamp] [$level] $message\n";
    
    // Write to log file
    @file_put_contents(SSE_LOG_FILE, $logLine, FILE_APPEND);
    
    // Also log errors to PHP error log
    if ($level === 'ERROR') {
        error_log("SSE Proxy: $message");
    }
}

// ============================================================================
// HARDENED SSE STREAM PROXY
// ============================================================================

/**
 * Proxy SSE stream with security and timeout enforcement
 */
function proxySSEStream() {
    $connId = null;
    $stream = null;
    
    try {
        // SECURITY: Validate authentication
        validateAuth();
        
        // SECURITY: Check rate limits
        validateRateLimit();
        
        // SECURITY: Check connection limits
        checkConnectionLimit();
        
        // Register this connection
        $connId = registerConnection();
        
        // Set SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-transform');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        header('X-Content-Type-Options: nosniff');
        
        // SECURITY: Restrict CORS
        header('Access-Control-Allow-Origin: https://gpt.ecigdis.co.nz');
        header('Access-Control-Allow-Credentials: true');
        
        // Connect to SSE server
        $url = SSE_BASE_URL . '/events';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => SSE_CONNECTION_TIMEOUT, // FIXED: 60 seconds not 3600
                'ignore_errors' => true,
                'header' => 'X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR']
            ]
        ]);
        
        $stream = @fopen($url, 'r', false, $context);
        
        if (!$stream) {
            throw new Exception("Failed to connect to SSE server");
        }
        
        logMessage("SSE stream started for connection $connId");
        
        // Streaming with HARDENED security checks
        $startTime = time();
        $lastHeartbeat = time();
        $bytesSent = 0;
        $eventsSent = 0;
        
        while (!feof($stream)) {
            // SECURITY CHECK #1: Maximum runtime (90 seconds)
            if (time() - $startTime > SSE_MAX_RUNTIME) {
                logMessage("Connection $connId exceeded max runtime (" . SSE_MAX_RUNTIME . "s)", 'WARN');
                break;
            }
            
            // SECURITY CHECK #2: Heartbeat timeout (30 seconds without data)
            if (time() - $lastHeartbeat > SSE_IDLE_TIMEOUT) {
                logMessage("Connection $connId idle timeout (" . SSE_IDLE_TIMEOUT . "s)", 'WARN');
                break;
            }
            
            // SECURITY CHECK #3: Connection aborted
            if (connection_aborted()) {
                logMessage("Connection $connId aborted by client");
                break;
            }
            
            // Read line from stream
            $line = fgets($stream);
            
            if ($line !== false) {
                echo $line;
                @ob_flush();
                @flush();
                
                $bytesSent += strlen($line);
                
                // Update heartbeat on data received
                if (strpos($line, 'heartbeat') !== false || strpos($line, 'data:') !== false) {
                    $lastHeartbeat = time();
                    $eventsSent++;
                }
                
                // SECURITY CHECK #4: Excessive data (prevent memory exhaustion)
                if ($bytesSent > 1048576) { // 1MB limit
                    logMessage("Connection $connId exceeded data limit (1MB)", 'WARN');
                    break;
                }
            }
            
            // SECURITY: Prevent tight loop CPU burn
            usleep(10000); // 10ms sleep
        }
        
        $duration = time() - $startTime;
        logMessage("SSE stream ended for connection $connId (Duration: {$duration}s, Events: $eventsSent, Bytes: $bytesSent)");
        
    } catch (Exception $e) {
        logMessage("SSE stream error for connection $connId: " . $e->getMessage(), 'ERROR');
        
        // Send error event to client
        echo "event: error\n";
        echo "data: " . json_encode([
            'error' => 'Connection failed',
            'message' => 'SSE server unavailable',
            'retry' => 5000 // Retry after 5 seconds
        ]) . "\n\n";
        @ob_flush();
        @flush();
        
        http_response_code(503);
        
        SecurityMonitor::logSecurityEvent('sse_stream_error', [
            'connection_id' => $connId,
            'error' => $e->getMessage(),
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
    } finally {
        // CRITICAL: Always cleanup
        if (is_resource($stream)) {
            fclose($stream);
        }
        if ($connId) {
            unregisterConnection($connId);
        }
    }
}

// ============================================================================
// HARDENED API PROXY
// ============================================================================

/**
 * Proxy API request with security
 */
function proxyAPIRequest($endpoint, $method = 'GET', $body = null) {
    try {
        // SECURITY: Validate authentication
        validateAuth();
        
        // SECURITY: Validate CSRF for POST requests
        validateCSRF();
        
        // SECURITY: Sanitize endpoint
        $endpoint = InputValidator::sanitize($endpoint, 'string');
        $endpoint = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $endpoint);
        
        // SECURITY: Check rate limits (more lenient for API)
        $ip = $_SERVER['REMOTE_ADDR'];
        if (!RateLimiter::check($ip, 'sse_api', 20, 60)) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'retry_after' => 60
            ]);
            return;
        }
        
        $url = SSE_BASE_URL . '/api/' . $endpoint;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => "Content-Type: application/json\r\nX-Forwarded-For: " . $_SERVER['REMOTE_ADDR'],
                'timeout' => 30,
                'ignore_errors' => true
            ]
        ];
        
        if ($body !== null) {
            // SECURITY: Validate body size (max 100KB)
            if (strlen($body) > 102400) {
                http_response_code(413);
                echo json_encode([
                    'success' => false,
                    'error' => 'Request body too large',
                    'max_size' => '100KB'
                ]);
                return;
            }
            
            $options['http']['content'] = is_string($body) ? $body : json_encode($body);
        }
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Failed to connect to SSE server");
        }
        
        // Forward response
        header('Content-Type: application/json');
        echo $response;
        
        logMessage("API request: $method /api/$endpoint (IP: {$_SERVER['REMOTE_ADDR']})");
        
    } catch (Exception $e) {
        logMessage("API proxy error: " . $e->getMessage(), 'ERROR');
        
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to connect to SSE server',
            'message' => 'Backend service unavailable'
        ]);
    }
}

// ============================================================================
// MAIN ROUTING
// ============================================================================

// Clean old connection file on startup
if (file_exists(SSE_CONNECTIONS_FILE)) {
    $mtime = filemtime(SSE_CONNECTIONS_FILE);
    if (time() - $mtime > 300) { // Older than 5 minutes
        @unlink(SSE_CONNECTIONS_FILE);
        logMessage("Cleaned stale connection file");
    }
}

// Route request
if (isset($_GET['stream']) && $_GET['stream'] === 'events') {
    // Stream SSE events (HARDENED)
    proxySSEStream();
    
} elseif (isset($_GET['endpoint'])) {
    // Proxy API request (HARDENED)
    $endpoint = $_GET['endpoint'];
    $method = $_SERVER['REQUEST_METHOD'];
    $body = null;
    
    if ($method === 'POST' || $method === 'PUT') {
        $body = file_get_contents('php://input');
    }
    
    proxyAPIRequest($endpoint, $method, $body);
    
} else {
    // Health check (no auth required for monitoring)
    header('Content-Type: application/json');
    
    try {
        $url = SSE_BASE_URL . '/health';
        $context = stream_context_create([
            'http' => ['timeout' => 5]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Health check failed");
        }
        
        echo $response;
        
    } catch (Exception $e) {
        http_response_code(503);
        echo json_encode([
            'status' => 'error',
            'message' => 'SSE server not responding',
            'backend_url' => SSE_BASE_URL
        ]);
    }
}
