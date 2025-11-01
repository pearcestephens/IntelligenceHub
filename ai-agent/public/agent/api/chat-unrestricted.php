<?php

declare(strict_types=1);

/**
 * Unrestricted Chat API Endpoint - GPT with minimal restrictions
 * 
 * Modified version of chat.php with content restrictions removed
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

require_once __DIR__ . '/../../../src/bootstrap.php';
use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Errors;
use App\Util\Validate;
use App\Util\RateLimit;
use App\Util\SecurityHeaders;

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

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!$method) {
    $envMethod = getenv('METHOD');
    $method = ($envMethod && is_string($envMethod) && $envMethod !== '') ? strtoupper($envMethod) : (php_sapi_name() === 'cli' ? 'POST' : 'UNKNOWN');
}

// Only allow POST requests
if ($method !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize configuration with unrestricted settings
    Config::initialize();
    
    // Initialize CIS database connection
    require_once $_SERVER['DOCUMENT_ROOT'] . '/assets/functions/config.php';
    $cisIntelligence = new CISIntelligenceEngine($con);
    
    // Generate session ID for logging
    $sessionId = session_id() ?: uniqid('ai_session_', true);
    
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
    $conversationId = $data['conversation_id'] ?? null;
    $streaming = $data['streaming'] ?? false;
    
    // Create agent instance
    $agent = new Agent();
    
    if ($streaming) {
        // Set up SSE headers for streaming
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        SecurityHeaders::apply();
        
        // Override system prompt with unrestricted version
        $unrestrictedPrompt = UnrestrictedContextCards::buildUnrestrictedSystemPrompt();
        
        // Process message with streaming and unrestricted context
        $startTime = microtime(true);
        $result = $agent->processMessage($message, [
            'conversation_id' => $conversationId,
            'streaming' => true,
            'system_prompt_override' => $enhancedPrompt,
            'safety_mode' => 'unrestricted',
            'content_filter' => false,
            'cis_context' => $businessContext
        ]);
        
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        // Log AI response
        $cisIntelligence->logger->logAIResponse($sessionId, $result['response'] ?? '', [
            'conversation_id' => $conversationId,
            'agent_type' => 'gpt',
            'processing_time_ms' => $processingTime,
            'model_used' => 'gpt-4o-unrestricted',
            'restrictions_bypassed' => true,
            'security_level' => 'unrestricted',
            'cis_context_used' => $businessContext
        ]);
        
        // Send final result
        echo "data: " . json_encode([
            'type' => 'complete',
            'response' => $result['response'] ?? '',
            'conversation_id' => $result['conversation_id'] ?? null,
            'timestamp' => date('c')
        ]) . "\n\n";
        
        echo "data: [DONE]\n\n";
        
    } else {
        // JSON response mode
        header('Content-Type: application/json');
        SecurityHeaders::apply();
        
        // Log user message
        $cisIntelligence->logger->logUserMessage($sessionId, $message, [
            'conversation_id' => $conversationId,
            'agent_type' => 'gpt',
            'user_id' => $_SESSION['user_id'] ?? 'anonymous',
            'security_level' => 'unrestricted'
        ]);
        
        // Generate comprehensive business context
        $businessContext = $cisIntelligence->generateBusinessContext($conversationId, $message);
        
        // Override system prompt with unrestricted version + CIS intelligence
        $unrestrictedPrompt = UnrestrictedContextCards::buildUnrestrictedSystemPrompt();
        $enhancedPrompt = $unrestrictedPrompt . "\n\n# CIS BUSINESS INTELLIGENCE\n\n" . 
                         "You have access to complete VapeShed business data:\n" .
                         json_encode($businessContext, JSON_PRETTY_PRINT);
        
        // Process message with unrestricted context
        $startTime = microtime(true);
        $result = $agent->processMessage($message, [
            'conversation_id' => $conversationId,
            'streaming' => false,
            'system_prompt_override' => $enhancedPrompt,
            'safety_mode' => 'unrestricted',
            'content_filter' => false,
            'cis_context' => $businessContext
        ]);
        
        $processingTime = round((microtime(true) - $startTime) * 1000);
        
        // Log AI response
        $cisIntelligence->logger->logAIResponse($sessionId, $result['response'] ?? '', [
            'conversation_id' => $conversationId,
            'agent_type' => 'gpt',
            'processing_time_ms' => $processingTime,
            'model_used' => 'gpt-4o-unrestricted',
            'restrictions_bypassed' => true,
            'security_level' => 'unrestricted',
            'cis_context_used' => $businessContext
        ]);
        
        echo json_encode([
            'success' => true,
            'response' => $result['response'] ?? '',
            'conversation_id' => $result['conversation_id'] ?? null,
            'processing_time_ms' => $processingTime,
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