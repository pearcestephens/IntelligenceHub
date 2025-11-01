<?php
/**
 * Hardened Chat API Endpoint
 * Provides secure chat functionality with comprehensive validation and error handling
 */

// Turn off error display to prevent HTML errors in JSON response
ini_set('display_errors', 0);
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'CORS preflight']);
    exit;
}

// Rate limiting (simple implementation)
session_start();
$rate_limit_key = 'chat_requests_' . session_id();
$current_time = time();
$rate_window = 60; // 1 minute window
$max_requests = 30; // Max requests per window

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [];
}

// Clean old requests
$_SESSION[$rate_limit_key] = array_filter($_SESSION[$rate_limit_key], function($timestamp) use ($current_time, $rate_window) {
    return $timestamp > ($current_time - $rate_window);
});

// Check rate limit
if (count($_SESSION[$rate_limit_key]) >= $max_requests) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded. Please wait before sending more messages.',
        'retry_after' => $rate_window
    ]);
    exit;
}

// Add current request
$_SESSION[$rate_limit_key][] = $current_time;

// Input validation
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid JSON input'
    ]);
    exit;
}

$message = trim($input['message'] ?? '');
$bot_id = $input['bot_id'] ?? 'general';
$conversation_id = $input['conversation_id'] ?? uniqid();

// Validate message
if (empty($message)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Message cannot be empty'
    ]);
    exit;
}

if (strlen($message) > 4000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Message too long (max 4000 characters)'
    ]);
    exit;
}

// Sanitize input
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Bot configuration
$bots = [
    'general' => [
        'name' => 'CIS General Assistant',
        'context' => 'You are a helpful AI assistant for the CIS system.',
        'max_response_length' => 2000
    ],
    'technical' => [
        'name' => 'Technical Support Bot',
        'context' => 'You are a technical support specialist for PHP, MySQL, and web development.',
        'max_response_length' => 3000
    ],
    'business' => [
        'name' => 'Business Intelligence Bot',
        'context' => 'You are a business analyst providing insights on operations and data.',
        'max_response_length' => 2500
    ]
];

$selected_bot = $bots[$bot_id] ?? $bots['general'];

try {
    // Generate response based on bot type and message content
    $response = generateBotResponse($message, $selected_bot, $conversation_id);
    
    // Log successful interaction
    error_log(sprintf(
        "[CHAT] Bot: %s, User: %s, Message: %s, Response Length: %d",
        $bot_id,
        session_id(),
        substr($message, 0, 50) . '...',
        strlen($response)
    ));
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'bot_id' => $bot_id,
        'bot_name' => $selected_bot['name'],
        'conversation_id' => $conversation_id,
        'timestamp' => date('c'),
        'tokens_used' => estimateTokens($message . $response)
    ]);
    
} catch (Exception $e) {
    error_log("[CHAT ERROR] " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while processing your request'
    ]);
}

function generateBotResponse($message, $bot_config, $conversation_id) {
    // Enhanced response generation with context awareness and CIS knowledge
    $message_lower = strtolower($message);
    
    // Handle CIS-specific queries first
    if (strpos($message_lower, 'consignment') !== false || strpos($message_lower, 'transfer') !== false) {
        return generateConsignmentResponse($message, $bot_config);
    }
    
    if (strpos($message_lower, 'outlet') !== false || strpos($message_lower, 'store') !== false) {
        return generateOutletResponse($message, $bot_config);
    }
    
    if (strpos($message_lower, 'vend') !== false || strpos($message_lower, 'lightspeed') !== false) {
        return generateVendResponse($message, $bot_config);
    }
    
    // Handle common queries with intelligent responses
    if (strpos($message_lower, 'help') !== false || strpos($message_lower, '?') !== false) {
        return generateHelpResponse($message, $bot_config);
    }
    
    if (strpos($message_lower, 'error') !== false || strpos($message_lower, 'bug') !== false) {
        return generateTechnicalResponse($message, $bot_config);
    }
    
    if (strpos($message_lower, 'data') !== false || strpos($message_lower, 'report') !== false) {
        return generateDataResponse($message, $bot_config);
    }
    
    // Default intelligent response
    return generateIntelligentResponse($message, $bot_config);
}

function generateHelpResponse($message, $bot_config) {
    $responses = [
        "I'm here to help! What specific question do you have?",
        "I'd be happy to assist you. Could you provide more details about what you're looking for?",
        "Let me help you with that. What particular aspect would you like me to focus on?",
        "I'm ready to help! Please let me know what specific information or assistance you need."
    ];
    
    return $responses[array_rand($responses)] . " (Powered by " . $bot_config['name'] . ")";
}

function generateTechnicalResponse($message, $bot_config) {
    $responses = [
        "For technical issues, I recommend checking the error logs first. Would you like me to help you locate them?",
        "Let me help you troubleshoot this. Can you provide the specific error message or symptoms?",
        "Technical problems can often be resolved by checking: 1) Error logs, 2) Configuration files, 3) Recent changes. Which would you like to start with?",
        "I can assist with technical debugging. Please share the error details and I'll help you work through it systematically."
    ];
    
    return $responses[array_rand($responses)];
}

function generateDataResponse($message, $bot_config) {
    $responses = [
        "I can help with data analysis. What specific metrics or reports are you interested in?",
        "For data requests, I can assist with queries, reporting, and analysis. What information do you need?",
        "Data insights are available for various business areas. Which dataset or time period interests you?",
        "I'm equipped to help with data interpretation and reporting. Please specify your requirements."
    ];
    
    return $responses[array_rand($responses)];
}

function generateIntelligentResponse($message, $bot_config) {
    // Context-aware responses based on message content
    $keywords = extractKeywords($message);
    
    if (in_array('database', $keywords) || in_array('sql', $keywords)) {
        return "I can help with database queries and optimization. What specific database task are you working on?";
    }
    
    if (in_array('code', $keywords) || in_array('programming', $keywords)) {
        return "I'm ready to assist with coding questions. What programming challenge can I help you solve?";
    }
    
    if (in_array('system', $keywords) || in_array('server', $keywords)) {
        return "For system-related questions, I can help with configuration, monitoring, and troubleshooting. What do you need assistance with?";
    }
    
    // Default intelligent response
    return "I understand you're asking about: '" . htmlspecialchars(substr($message, 0, 50)) . "'. Could you provide a bit more context so I can give you the most helpful response?";
}

function extractKeywords($message) {
    $message_lower = strtolower($message);
    $keywords = [];
    
    $technical_terms = ['database', 'sql', 'php', 'code', 'programming', 'system', 'server', 'api', 'error', 'bug', 'debug'];
    
    foreach ($technical_terms as $term) {
        if (strpos($message_lower, $term) !== false) {
            $keywords[] = $term;
        }
    }
    
    return $keywords;
}

function generateConsignmentResponse($message, $bot_config) {
    $message_lower = strtolower($message);
    
    if (strpos($message_lower, 'status') !== false) {
        return "To check transfer status, use: `SELECT * FROM transfers WHERE id = [TRANSFER_ID]` or visit the consignments module at https://staff.vapeshed.co.nz/modules/consignments/. You can also check the audit trail with `SELECT * FROM transfer_audit_log WHERE transfer_id = [ID] ORDER BY timestamp DESC`.";
    }
    
    if (strpos($message_lower, 'create') !== false || strpos($message_lower, 'new') !== false) {
        return "To create a new transfer: 1) Navigate to consignments module, 2) Click 'New Transfer', 3) Select source/destination outlets, 4) Add products and quantities, 5) Submit (moves to OPEN status). The system supports 4 types: Stock Transfers, Juice Transfers, Staff Transfers, and Purchase Orders.";
    }
    
    if (strpos($message_lower, 'api') !== false) {
        return "Consignments API endpoints (Base: /modules/consignments/api/): POST pack_submit.php (submit packed transfer), POST receive_submit.php (confirm receipt), GET transfer_status.php (check status), GET tracking.php (get tracking info). All POST endpoints require CSRF tokens and support idempotency with nonce parameters.";
    }
    
    return "The consignments module manages transfers between 17 NZ outlets. It handles Stock, Juice, Staff, and Purchase Order transfers with full audit trails. What specific aspect would you like help with? (status checking, creating transfers, API usage, troubleshooting)";
}

function generateOutletResponse($message, $bot_config) {
    return "The CIS system manages 17 retail outlets across New Zealand. Each outlet has a unique outlet_id used throughout the system for transfers, sales tracking, and inventory management. You can view outlet information in the outlets table or through the Vend/Lightspeed integration. What outlet-specific information do you need?";
}

function generateVendResponse($message, $bot_config) {
    return "The system integrates with Lightspeed Retail (formerly Vend) POS. CIS maintains a shadow/cache layer that syncs with Lightspeed for inventory, sales, and customer data. Key tables include vend_products, vend_outlets, and vend_sales. For sync issues, check the Lightspeed API connectivity and recent sync logs.";
}

function estimateTokens($text) {
    // Simple token estimation (approximately 4 characters per token)
    return ceil(strlen($text) / 4);
}
?>