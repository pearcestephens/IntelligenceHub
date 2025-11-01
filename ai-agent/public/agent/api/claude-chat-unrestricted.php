<?php

declare(strict_types=1);

/**
 * Unrestricted Claude Chat API Endpoint - Claude with minimal restrictions
 * 
 * Modified version of claude-chat.php with content restrictions removed
 * Uses unrestricted system prompts and relaxed safety boundaries
 * 
 * @package App
 * @author Pearce Stephens - Ecigdis Limited
 * @version 1.0.0 - Unrestricted Mode
 */

// Load unrestricted configuration first
require_once __DIR__ . '/../../unrestricted_config.php';

// Load CIS intelligence and logging
require_once __DIR__ . '/../../cis_intelligence_engine.php';

// Simple Anthropic API class for unrestricted Claude access
class UnrestrictedClaudeAPI
{
    private string $apiKey;
    private string $model;
    
    public function __construct()
    {
        $this->apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
        $this->model = getenv('ANTHROPIC_MODEL') ?: 'claude-3-5-sonnet-20241022';
        
        if (empty($this->apiKey)) {
            throw new Exception('ANTHROPIC_API_KEY environment variable is required');
        }
    }
    
    public function chat(string $message, array $context = []): array
    {
        $systemPrompt = UnrestrictedContextCards::buildUnrestrictedSystemPrompt();
        
        // Add CIS business intelligence if provided
        if (isset($context['cis_context'])) {
            $systemPrompt .= "\n\n# CIS BUSINESS INTELLIGENCE\n\n" . 
                            "You have access to complete VapeShed business data:\n" .
                            json_encode($context['cis_context'], JSON_PRETTY_PRINT);
        }
        
        $payload = [
            'model' => $this->model,
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'system' => $systemPrompt,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        ];
        
        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Ecigdis-AI-Agent/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('API request failed with HTTP ' . $httpCode . ': ' . $response);
        }
        
        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception('Invalid JSON response from Claude API');
        }
        
        return $data;
    }
    
    public function streamChat(string $message, array $context = []): void
    {
        $systemPrompt = UnrestrictedContextCards::buildUnrestrictedSystemPrompt();
        
        // Add CIS business intelligence if provided
        if (isset($context['cis_context'])) {
            $systemPrompt .= "\n\n# CIS BUSINESS INTELLIGENCE\n\n" . 
                            "You have access to complete VapeShed business data:\n" .
                            json_encode($context['cis_context'], JSON_PRETTY_PRINT);
        }
        
        $payload = [
            'model' => $this->model,
            'max_tokens' => 4000,
            'temperature' => 0.7,
            'system' => $systemPrompt,
            'stream' => true,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ]
        ];
        
        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'Accept: text/event-stream'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.anthropic.com/v1/messages',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_WRITEFUNCTION => [$this, 'handleStreamData'],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Ecigdis-AI-Agent/1.0'
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Send completion marker
        echo "data: " . json_encode([
            'type' => 'complete',
            'timestamp' => date('c')
        ]) . "\n\n";
        
        echo "data: [DONE]\n\n";
        flush();
    }
    
    private function handleStreamData($ch, $data): int
    {
        $lines = explode("\n", $data);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || !str_starts_with($line, 'data: ')) {
                continue;
            }
            
            $jsonData = substr($line, 6);
            
            if ($jsonData === '[DONE]') {
                continue;
            }
            
            $parsed = json_decode($jsonData, true);
            if (!$parsed) {
                continue;
            }
            
            // Handle different Claude streaming event types
            if (isset($parsed['type'])) {
                switch ($parsed['type']) {
                    case 'content_block_delta':
                        if (isset($parsed['delta']['text'])) {
                            echo "data: " . json_encode([
                                'type' => 'token',
                                'content' => $parsed['delta']['text']
                            ]) . "\n\n";
                            flush();
                        }
                        break;
                        
                    case 'message_start':
                        echo "data: " . json_encode([
                            'type' => 'start',
                            'message' => 'Claude response starting...'
                        ]) . "\n\n";
                        flush();
                        break;
                }
            }
        }
        
        return strlen($data);
    }
}

// Set CORS headers
if (php_sapi_name() !== 'cli') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'POST';

// Only allow POST requests
if ($method !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize CIS database connection
    require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/functions/config.php';
    $cisIntelligence = new CISIntelligenceEngine($con);
    
    // Generate session ID for logging
    $sessionId = session_id() ?: uniqid('claude_session_', true);
    
    // Get input data
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($input, true);
    if (!$data) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    if (!isset($data['message']) || !is_string($data['message']) || trim($data['message']) === '') {
        throw new Exception('Message is required and must be a non-empty string');
    }
    
    $message = trim($data['message']);
    $streaming = $data['streaming'] ?? false;
    $conversationId = $data['conversation_id'] ?? uniqid('conv_', true);
    
    // Log user message
    $cisIntelligence->logger->logUserMessage($sessionId, $message, [
        'conversation_id' => $conversationId,
        'agent_type' => 'claude',
        'user_id' => $_SESSION['user_id'] ?? 'anonymous',
        'security_level' => 'unrestricted',
        'business_context' => ['unrestricted_mode' => true]
    ]);
    
    // Generate comprehensive business context
    $businessContext = $cisIntelligence->generateBusinessContext($conversationId, $message);
    
    // Create Claude API instance
    $claude = new UnrestrictedClaudeAPI();
    
    if ($streaming) {
        // Set up SSE headers for streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        
        // Process message with streaming and CIS context
        $startTime = microtime(true);
        $claude->streamChat($message, ['cis_context' => $businessContext]);
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        // Log streaming response
        $cisIntelligence->logger->logAIResponse($sessionId, '[Streaming Response]', [
            'conversation_id' => $conversationId,
            'agent_type' => 'claude',
            'processing_time_ms' => $processingTime,
            'model_used' => 'claude-3-5-sonnet-unrestricted',
            'restrictions_bypassed' => true,
            'security_level' => 'unrestricted',
            'cis_context_used' => $businessContext
        ]);
        
    } else {
        // JSON response mode
        header('Content-Type: application/json');
        
        // Process message with CIS context
        $startTime = microtime(true);
        $result = $claude->chat($message, ['cis_context' => $businessContext]);
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        $response = '';
        if (isset($result['content']) && is_array($result['content'])) {
            foreach ($result['content'] as $content) {
                if (isset($content['text'])) {
                    $response .= $content['text'];
                }
            }
        }
        
        // Log AI response
        $cisIntelligence->logger->logAIResponse($sessionId, $response, [
            'conversation_id' => $conversationId,
            'agent_type' => 'claude',
            'processing_time_ms' => $processingTime,
            'model_used' => 'claude-3-5-sonnet-unrestricted',
            'restrictions_bypassed' => true,
            'security_level' => 'unrestricted',
            'cis_context_used' => $businessContext
        ]);
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'conversation_id' => $conversationId,
            'processing_time_ms' => $processingTime,
            'model' => $claude->model ?? 'claude-3-5-sonnet',
            'cis_intelligence' => [
                'business_metrics' => $businessContext['current_business_metrics'] ?? [],
                'suggested_queries' => array_slice($businessContext['suggested_queries'] ?? [], 0, 3),
                'system_health' => $cisIntelligence->getSystemStatus()
            ],
            'timestamp' => date('c')
        ]);
    }
    
} catch (Exception $e) {
    if ($streaming ?? false) {
        header('Content-Type: text/event-stream');
        echo "data: " . json_encode([
            'type' => 'error',
            'error' => $e->getMessage(),
            'timestamp' => date('c')
        ]) . "\n\n";
        echo "data: [DONE]\n\n";
    } else {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'timestamp' => date('c')
        ]);
    }
}