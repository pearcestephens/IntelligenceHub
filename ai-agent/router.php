<?php
/**
 * PHP Built-in Server Router
 * Routes requests to appropriate API endpoints for testing
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Static files
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false; // Serve static file
}

// API routing
if (preg_match('#^/agent/api/v1/conversations(/.*)?$#', $uri, $matches)) {
    $_SERVER['PATH_INFO'] = $matches[1] ?? '';
    require __DIR__ . '/public/agent/api/v1/conversations.php';
    exit;
}

if (preg_match('#^/agent/api/v1/messages(/.*)?$#', $uri, $matches)) {
    $_SERVER['PATH_INFO'] = $matches[1] ?? '';
    require __DIR__ . '/public/agent/api/v1/messages.php';
    exit;
}

if (preg_match('#^/agent/api/v1/conversations/([^/]+)/messages$#', $uri, $matches)) {
    $_SERVER['PATH_INFO'] = '/' . $matches[1] . '/messages';
    require __DIR__ . '/public/agent/api/v1/conversations.php';
    exit;
}

// Default: 404
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => [
        'code' => 'not_found',
        'message' => 'Endpoint not found: ' . $uri
    ]
]);
