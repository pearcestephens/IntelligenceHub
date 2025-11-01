<?php
/**
 * AI Agent - Bot Information API Endpoint
 * 
 * Provides bot information and management
 * 
 * @version 2.0.0
 * @author AI Agent System
 */

declare(strict_types=1);

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Type: application/json; charset=utf-8');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Bot configuration
 */
$bots = [
    1 => [
        'id' => 1,
        'name' => 'Neural Assistant',
        'description' => 'General purpose AI assistant for comprehensive help and information',
        'capabilities' => [
            'General Q&A',
            'Information retrieval',
            'Problem solving',
            'Creative assistance',
            'Language tasks'
        ],
        'icon' => 'fas fa-brain',
        'color' => '#0d6efd',
        'status' => 'active',
        'version' => '2.0.0'
    ],
    2 => [
        'id' => 2,
        'name' => 'Code Review Bot',
        'description' => 'Specialized AI for code analysis, review, and development guidance',
        'capabilities' => [
            'Code review',
            'Security analysis',
            'Performance optimization',
            'Best practices guidance',
            'Architecture review',
            'Bug detection'
        ],
        'icon' => 'fas fa-code',
        'color' => '#198754',
        'status' => 'active',
        'version' => '2.0.0'
    ],
    3 => [
        'id' => 3,
        'name' => 'Support Agent',
        'description' => 'Customer support specialist for troubleshooting and assistance',
        'capabilities' => [
            'Troubleshooting',
            'Technical support',
            'Feature guidance',
            'Issue resolution',
            'Documentation help'
        ],
        'icon' => 'fas fa-headset',
        'color' => '#dc3545',
        'status' => 'active',
        'version' => '2.0.0'
    ]
];

/**
 * Send JSON response
 */
function sendResponse(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    
    $response = [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'timestamp' => date('c'),
        ...$data
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response
 */
function sendError(string $message, int $code = 400): void
{
    sendResponse([
        'error' => $message,
        'error_code' => $code
    ], $code);
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Invalid request method', 405);
    }
    
    // Get bot ID from query parameter
    $botId = isset($_GET['bot_id']) ? (int)$_GET['bot_id'] : null;
    
    if ($botId === null) {
        // Return all bots
        sendResponse([
            'bots' => array_values($bots),
            'total_bots' => count($bots),
            'active_bots' => count(array_filter($bots, fn($bot) => $bot['status'] === 'active'))
        ]);
    } else {
        // Return specific bot
        if (!isset($bots[$botId])) {
            sendError('Bot not found', 404);
        }
        
        sendResponse([
            'bot' => $bots[$botId]
        ]);
    }
    
} catch (Exception $e) {
    sendError('An unexpected error occurred', 500);
}
?>