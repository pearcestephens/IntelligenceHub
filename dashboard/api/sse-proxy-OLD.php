<?php
/**
 * SSE Proxy - Connects dashboard to internal SSE server
 * Proxies requests to localhost:4000 without exposing the port
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// SSE server configuration
define('SSE_HOST', '127.0.0.1');
define('SSE_PORT', 4000);
define('SSE_BASE_URL', 'http://' . SSE_HOST . ':' . SSE_PORT);

/**
 * Proxy SSE stream
 */
function proxySSEStream() {
    // Set SSE headers
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no'); // Disable nginx buffering
    
    // Connect to SSE server
    $url = SSE_BASE_URL . '/events';
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 3600, // 1 hour timeout
            'ignore_errors' => true
        ]
    ]);
    
    $stream = @fopen($url, 'r', false, $context);
    
    if (!$stream) {
        echo "event: error\n";
        echo "data: " . json_encode(['error' => 'Failed to connect to SSE server']) . "\n\n";
        ob_flush();
        flush();
        exit;
    }
    
    // Stream data from SSE server to client
    while (!feof($stream)) {
        $line = fgets($stream);
        if ($line !== false) {
            echo $line;
            ob_flush();
            flush();
        }
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
    }
    
    fclose($stream);
}

/**
 * Proxy API request
 */
function proxyAPIRequest($endpoint, $method = 'GET', $body = null) {
    $url = SSE_BASE_URL . '/api/' . $endpoint;
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => 'Content-Type: application/json',
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ];
    
    if ($body !== null) {
        $options['http']['content'] = is_string($body) ? $body : json_encode($body);
    }
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to connect to SSE server'
        ]);
        exit;
    }
    
    // Forward response
    header('Content-Type: application/json');
    echo $response;
}

// Main routing
if (isset($_GET['stream']) && $_GET['stream'] === 'events') {
    // Stream SSE events
    proxySSEStream();
} elseif (isset($_GET['endpoint'])) {
    // Proxy API request
    $endpoint = $_GET['endpoint'];
    $method = $_SERVER['REQUEST_METHOD'];
    $body = null;
    
    if ($method === 'POST' || $method === 'PUT') {
        $body = file_get_contents('php://input');
    }
    
    proxyAPIRequest($endpoint, $method, $body);
} else {
    // Health check
    header('Content-Type: application/json');
    
    $url = SSE_BASE_URL . '/health';
    $response = @file_get_contents($url, false, stream_context_create([
        'http' => ['timeout' => 5]
    ]));
    
    if ($response === false) {
        http_response_code(503);
        echo json_encode([
            'status' => 'error',
            'message' => 'SSE server not responding',
            'server' => SSE_BASE_URL
        ]);
    } else {
        echo $response;
    }
}
