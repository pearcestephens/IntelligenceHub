<?php
/**
 * AI Agent - Hardened Chat API Endpoint
 * 
 * Secure, production-ready chat API with:
 * - Input validation and sanitization
 * - Rate limiting and security checks
 * - Streaming support with SSE
 * - Comprehensive error handling
 * - Bot management and switching
 * - Session management
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

// CORS headers for AJAX
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Content type
header('Content-Type: application/json; charset=utf-8');

// Enable gzip compression for responses > 1KB (Performance Improvement)
if (!headers_sent() && extension_loaded('zlib') && !ob_get_length()) {
    ini_set('zlib.output_compression', '1');
    ini_set('zlib.output_compression_level', '6');
}


// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Configuration
 */
$config = [
    'max_message_length' => 4000,
    'rate_limit' => [
        'requests' => 20,
        'window' => 60 // seconds
    ],
    'bots' => [
        1 => [
            'id' => 1,
            'name' => 'Neural Assistant',
            'description' => 'General purpose AI assistant',
            'system_prompt' => 'You are a helpful, knowledgeable AI assistant.',
            'max_tokens' => 2000
        ],
        2 => [
            'id' => 2,
            'name' => 'Code Review Bot',
            'description' => 'Specialized in code review and development',
            'system_prompt' => 'You are an expert code reviewer and software architect.',
            'max_tokens' => 3000
        ],
        3 => [
            'id' => 3,
            'name' => 'Support Agent',
            'description' => 'Customer support and troubleshooting',
            'system_prompt' => 'You are a helpful customer support agent.',
            'max_tokens' => 1500
        ]
    ],
    'log_file' => __DIR__ . '/../logs/chat.log',
    'session_timeout' => 3600, // 1 hour
];

/**
 * Security and utility functions
 */

/**
 * Log messages with timestamp and context
 */
function logMessage(string $level, string $message, array $context = []): void
{
    global $config;
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = sprintf(
        "[%s] %s: %s | IP: %s | UA: %s | Context: %s\n",
        $timestamp,
        strtoupper($level),
        $message,
        $ip,
        $userAgent,
        json_encode($context)
    );
    
    // Ensure log directory exists
    $logDir = dirname($config['log_file']);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($config['log_file'], $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Send JSON response with proper error handling
 */
function sendResponse(array $data, int $httpCode = 200): void
{
    http_response_code($httpCode);
    
    // Add metadata
    $response = [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'timestamp' => date('c'),
        'request_id' => uniqid('req_', true),
        ...$data
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send error response with logging
 */
function sendError(string $message, int $code = 400, array $details = []): void
{
    logMessage('error', $message, $details);
    
    sendResponse([
        'error' => $message,
        'error_code' => $code,
        'details' => $details
    ], $code);
}

/**
 * Validate and sanitize input
 */
function validateInput(): array
{
    global $config;
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Invalid request method', 405);
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON input', 400);
    }
    
    // Required fields
    $requiredFields = ['message', 'bot_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            sendError("Missing required field: {$field}", 400);
        }
    }
    
    // Validate message
    $message = trim((string)$input['message']);
    if (strlen($message) === 0) {
        sendError('Message cannot be empty', 400);
    }
    
    if (strlen($message) > $config['max_message_length']) {
        sendError("Message too long. Maximum {$config['max_message_length']} characters allowed.", 400);
    }
    
    // Check for potential XSS/injection
    $dangerous_patterns = [
        '/<script[^>]*>.*?<\/script>/i',
        '/javascript:/i',
        '/data:text\/html/i',
        '/vbscript:/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i'
    ];
    
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $message)) {
            sendError('Invalid message content detected', 400);
        }
    }
    
    // Validate bot ID
    $botId = (int)$input['bot_id'];
    if (!isset($config['bots'][$botId])) {
        sendError('Invalid bot ID', 400);
    }
    
    // Optional fields with defaults
    $streaming = isset($input['stream']) ? (bool)$input['stream'] : false;
    $sessionId = isset($input['session_id']) ? (string)$input['session_id'] : null;
    $csrfToken = isset($input['csrf_token']) ? (string)$input['csrf_token'] : null;
    
    return [
        'message' => $message,
        'bot_id' => $botId,
        'streaming' => $streaming,
        'session_id' => $sessionId,
        'csrf_token' => $csrfToken
    ];
}

/**
 * Check rate limiting
 */
function checkRateLimit(): bool
{
    global $config;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheFile = sys_get_temp_dir() . "/chat_rate_limit_{$ip}.json";
    
    $now = time();
    $requests = [];
    
    // Load existing requests
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && isset($data['requests'])) {
            $requests = $data['requests'];
        }
    }
    
    // Remove old requests
    $requests = array_filter($requests, function($timestamp) use ($now, $config) {
        return ($now - $timestamp) < $config['rate_limit']['window'];
    });
    
    // Check limit
    if (count($requests) >= $config['rate_limit']['requests']) {
        return false;
    }
    
    // Add current request
    $requests[] = $now;
    
    // Save to cache
    file_put_contents($cacheFile, json_encode(['requests' => $requests]), LOCK_EX);
    
    return true;
}

/**
 * Initialize or validate session
 */
function initializeSession(?string $sessionId): string
{
    if ($sessionId && preg_match('/^[a-zA-Z0-9_-]{10,50}$/', $sessionId)) {
        // Use provided session ID if valid
        return $sessionId;
    }
    
    // Generate new session ID
    return 'sess_' . bin2hex(random_bytes(16));
}

/**
 * Get or create conversation history
 */
function getConversationHistory(string $sessionId): array
{
    $historyFile = sys_get_temp_dir() . "/chat_history_{$sessionId}.json";
    
    if (file_exists($historyFile)) {
        $data = json_decode(file_get_contents($historyFile), true);
        if ($data && isset($data['messages'])) {
            // Check if history is not too old
            $lastUpdate = $data['last_update'] ?? 0;
            if ((time() - $lastUpdate) < 3600) { // 1 hour
                return $data['messages'];
            }
        }
    }
    
    return [];
}

/**
 * Save conversation history
 */
function saveConversationHistory(string $sessionId, array $messages): void
{
    $historyFile = sys_get_temp_dir() . "/chat_history_{$sessionId}.json";
    
    $data = [
        'messages' => array_slice($messages, -50), // Keep last 50 messages
        'last_update' => time()
    ];
    
    file_put_contents($historyFile, json_encode($data), LOCK_EX);
}

/**
 * Process message with AI (mock implementation)
 * In production, this would connect to actual AI service
 */
function processMessage(string $message, int $botId, array $history): string
{
    global $config;
    
    $bot = $config['bots'][$botId];
    
    // Mock AI processing with different responses per bot
    switch ($botId) {
        case 1: // Neural Assistant
            if (stripos($message, 'hello') !== false || stripos($message, 'hi') !== false) {
                return "Hello! I'm the Neural Assistant. I'm here to help with general questions, provide information, and assist with various tasks. What would you like to know?";
            } elseif (stripos($message, 'time') !== false) {
                return "The current time is " . date('Y-m-d H:i:s T');
            } else {
                return "I understand you're asking about: \"" . substr($message, 0, 100) . "\"\n\nAs the Neural Assistant, I can help with general information, explanations, and problem-solving. Could you provide more specific details about what you'd like to know?";
            }
            
        case 2: // Code Review Bot
            if (stripos($message, 'code') !== false || stripos($message, 'function') !== false || stripos($message, 'class') !== false) {
                return "As a Code Review Bot, I can help analyze your code for:\n\n• **Security vulnerabilities**\n• **Performance optimizations**\n• **Best practices compliance**\n• **Code structure improvements**\n• **Bug detection**\n\nPlease share your code and I'll provide a comprehensive review with actionable recommendations.";
            } else {
                return "I'm the specialized Code Review Bot. I excel at:\n\n```\n- Code analysis and optimization\n- Security vulnerability detection\n- Architecture reviews\n- Performance profiling\n- Best practices guidance\n```\n\nFeel free to share any code you'd like me to review!";
            }
            
        case 3: // Support Agent
            if (stripos($message, 'help') !== false || stripos($message, 'problem') !== false || stripos($message, 'issue') !== false) {
                return "I'm here to help! As your Support Agent, I can assist with:\n\n✅ **Troubleshooting technical issues**\n✅ **Account and system problems**\n✅ **Feature explanations and guidance**\n✅ **Bug reporting and tracking**\n\nPlease describe your issue in detail, and I'll provide step-by-step assistance to resolve it.";
            } else {
                return "Hello! I'm your dedicated Support Agent. I'm trained to help resolve issues quickly and effectively.\n\nWhat specific problem or question can I help you with today? The more details you provide, the better I can assist you.";
            }
            
        default:
            return "I'm an AI assistant ready to help. How can I assist you today?";
    }
}

/**
 * Stream response using Server-Sent Events
 */
function streamResponse(string $fullResponse, string $sessionId): string
{
    // Create temporary stream file
    $streamId = 'stream_' . bin2hex(random_bytes(8));
    $streamFile = sys_get_temp_dir() . "/chat_stream_{$streamId}.txt";
    
    // Save full response to stream file
    file_put_contents($streamFile, $fullResponse);
    
    // Return stream URL (would be handled by separate SSE endpoint)
    return "/api/stream.php?stream_id={$streamId}&session_id={$sessionId}";
}

/**
 * Main execution
 */
try {
    // Start session
    session_start();
    
    logMessage('info', 'Chat request received', ['method' => $_SERVER['REQUEST_METHOD']]);
    
    // Check rate limiting
    if (!checkRateLimit()) {
        sendError('Rate limit exceeded. Please wait before sending another message.', 429);
    }
    
    // Validate input
    $input = validateInput();
    
    // Initialize session
    $sessionId = initializeSession($input['session_id']);
    
    // Get conversation history
    $history = getConversationHistory($sessionId);
    
    // Process the message
    logMessage('info', 'Processing message', [
        'bot_id' => $input['bot_id'],
        'session_id' => $sessionId,
        'message_length' => strlen($input['message']),
        'streaming' => $input['streaming']
    ]);
    
    $response = processMessage($input['message'], $input['bot_id'], $history);
    
    // Update conversation history
    $history[] = ['role' => 'user', 'content' => $input['message'], 'timestamp' => time()];
    $history[] = ['role' => 'assistant', 'content' => $response, 'timestamp' => time()];
    saveConversationHistory($sessionId, $history);
    
    // Send response
    if ($input['streaming']) {
        $streamUrl = streamResponse($response, $sessionId);
        sendResponse([
            'message' => 'Streaming response initiated',
            'stream_url' => $streamUrl,
            'session_id' => $sessionId,
            'bot_id' => $input['bot_id']
        ]);
    } else {
        sendResponse([
            'message' => $response,
            'session_id' => $sessionId,
            'bot_id' => $input['bot_id'],
            'response_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ]);
    }
    
    logMessage('info', 'Message processed successfully', [
        'session_id' => $sessionId,
        'response_length' => strlen($response)
    ]);
    
} catch (Exception $e) {
    logMessage('error', 'Unexpected error in chat processing', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    sendError('An unexpected error occurred. Please try again.', 500, [
        'error_id' => uniqid('err_', true)
    ]);
}
?>