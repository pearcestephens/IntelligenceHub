<?php
/**
 * Intelligence Hub - AI Chat API
 * 
 * Production-hardened endpoint that connects to CIS AI Agent
 * with additional security layers specific to Intelligence Hub
 * 
 * @version 1.0.0
 * @package Intelligence Hub
 */

declare(strict_types=1);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS for dashboard (adjust as needed)
header('Access-Control-Allow-Origin: https://gpt.ecigdis.co.nz');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load AI Agent Client
require_once __DIR__ . '/../services/AIAgentClient.php';

// Configuration
define('MAX_MESSAGE_LENGTH', 5000);
define('RATE_LIMIT_PER_IP', 20); // 20 requests per minute
define('RATE_LIMIT_WINDOW', 60); // 1 minute window

/**
 * Simple rate limiter using file-based storage
 */
function checkRateLimit(string $ip): bool
{
    $cacheDir = __DIR__ . '/../private_html/cache';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    $cacheFile = $cacheDir . '/rate_limit_' . md5($ip) . '.json';
    $now = time();
    
    // Read existing data
    $data = [];
    if (file_exists($cacheFile)) {
        $content = file_get_contents($cacheFile);
        if ($content) {
            $data = json_decode($content, true) ?: [];
        }
    }
    
    // Clean old entries
    $data = array_filter($data, function($timestamp) use ($now) {
        return ($now - $timestamp) < RATE_LIMIT_WINDOW;
    });
    
    // Check limit
    if (count($data) >= RATE_LIMIT_PER_IP) {
        return false;
    }
    
    // Add current request
    $data[] = $now;
    file_put_contents($cacheFile, json_encode($data));
    
    return true;
}

/**
 * Sanitize user input
 */
function sanitizeInput(string $input, int $maxLength): string
{
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    
    if (strlen($input) > $maxLength) {
        $input = substr($input, 0, $maxLength);
    }
    
    return $input;
}

/**
 * Log request for security audit
 */
function logRequest(string $ip, string $action, ?string $error = null): void
{
    $logDir = __DIR__ . '/../private_html/logs/api';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/ai-chat-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $error ? 'ERROR' : 'SUCCESS';
    $message = $error ?? $action;
    
    $logLine = "[{$timestamp}] [{$status}] IP={$ip} Action={$action} Message={$message}\n";
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}

// Main request handler
try {
    // Get client IP
    $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Rate limiting check
    if (!checkRateLimit($clientIp)) {
        http_response_code(429);
        logRequest($clientIp, 'chat', 'Rate limit exceeded');
        echo json_encode([
            'success' => false,
            'error' => 'Rate limit exceeded. Please try again in a minute.',
            'retry_after' => 60
        ]);
        exit;
    }
    
    // Parse request
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        // Handle SSE streaming
        $message = $_GET['message'] ?? '';
        $provider = $_GET['provider'] ?? 'claude';
        $stream = isset($_GET['stream']) && $_GET['stream'] === 'true';
        
    } elseif ($method === 'POST') {
        // Handle JSON payload
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data) {
            throw new InvalidArgumentException('Invalid JSON payload');
        }
        
        $message = $data['message'] ?? '';
        $provider = $data['provider'] ?? 'claude';
        $stream = $data['stream'] ?? false;
        $conversationId = $data['conversation_id'] ?? null;
        
    } else {
        throw new BadMethodCallException('Method not allowed');
    }
    
    // Validate message
    if (empty($message)) {
        throw new InvalidArgumentException('Message is required');
    }
    
    $message = sanitizeInput($message, MAX_MESSAGE_LENGTH);
    
    // Validate provider
    if (!in_array($provider, ['claude', 'openai'])) {
        $provider = 'claude';
    }
    
    // Create AI agent client
    $client = new AIAgentClient();
    
    // Add crawler context from SSE server if available
    $context = [
        'source' => 'intelligence_hub',
        'crawler_integration' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Check if SSE server is running and add crawler state
    try {
        $sseHealth = @file_get_contents('http://127.0.0.1:4000/api/status', false, stream_context_create([
            'http' => ['timeout' => 2]
        ]));
        
        if ($sseHealth) {
            $sseStatus = json_decode($sseHealth, true);
            if ($sseStatus) {
                $context['crawler_status'] = $sseStatus['crawler'] ?? [];
                $context['system_stats'] = $sseStatus['system'] ?? [];
            }
        }
    } catch (Exception $e) {
        // SSE server not available, continue without crawler context
    }
    
    // Handle streaming vs regular response
    if ($stream) {
        // Stream response
        logRequest($clientIp, 'chat_stream_start');
        $client->streamChat($message, $provider, $context);
        logRequest($clientIp, 'chat_stream_complete');
        
    } else {
        // Regular JSON response
        logRequest($clientIp, 'chat_start');
        $response = $client->chat($message, $provider, $context, $conversationId ?? null);
        logRequest($clientIp, 'chat_complete');
        
        // Return response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'response' => $response,
            'provider' => $provider,
            'timestamp' => date('c')
        ]);
    }
    
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    logRequest($clientIp ?? 'unknown', 'chat', $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
} catch (BadMethodCallException $e) {
    http_response_code(405);
    logRequest($clientIp ?? 'unknown', 'chat', $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    
} catch (RuntimeException $e) {
    http_response_code(502);
    logRequest($clientIp ?? 'unknown', 'chat', 'AI Agent API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'AI service temporarily unavailable. Please try again.',
        'retry' => true
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    logRequest($clientIp ?? 'unknown', 'chat', 'Unexpected error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred. Please try again later.'
    ]);
}
