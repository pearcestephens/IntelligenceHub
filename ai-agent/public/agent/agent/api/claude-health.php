<?php

declare(strict_types=1);

/**
 * Claude Health Check API Endpoint
 * 
 * Tests Claude AI connectivity and returns system status.
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Config;
use App\Logger;
use App\Claude;
use App\Util\Errors;
use App\Util\SecurityHeaders;

// Set headers only in web context
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    SecurityHeaders::applyJson();
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $config = new Config();
    $logger = new Logger($config);
    
    $health = [
        'success' => true,
        'service' => 'Claude AI Agent',
        'version' => '1.0.0',
        'timestamp' => date('c'),
        'status' => []
    ];
    
    // Check Claude configuration
    $claudeConfigured = $config->get('ANTHROPIC_API_KEY') && $config->get('ANTHROPIC_API_KEY') !== 'YOUR_CLAUDE_API_KEY_HERE';
    
    $health['status']['claude_configured'] = [
        'status' => $claudeConfigured ? 'healthy' : 'error',
        'message' => $claudeConfigured ? 'Claude API configured' : 'Claude API key not configured'
    ];
    
    if ($claudeConfigured) {
        // Test Claude API connection
        try {
            $claude = new Claude($config, $logger);
            $testResult = $claude->test();
            
            $health['status']['claude_api'] = [
                'status' => $testResult['success'] ? 'healthy' : 'error',
                'message' => $testResult['success'] ? 'Claude API connection successful' : $testResult['error'],
                'model' => $config->get('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022'),
                'response' => $testResult['success'] ? $testResult['response'] : null,
                'usage' => $testResult['usage'] ?? null
            ];
            
        } catch (Exception $e) {
            $health['status']['claude_api'] = [
                'status' => 'error',
                'message' => 'Claude API test failed: ' . $e->getMessage()
            ];
        }
    } else {
        $health['status']['claude_api'] = [
            'status' => 'disabled',
            'message' => 'Claude API not configured'
        ];
    }
    
    // Check available models
    if ($claudeConfigured) {
        try {
            $claude = new Claude($config, $logger);
            $health['status']['available_models'] = [
                'status' => 'healthy',
                'models' => $claude->getModels(),
                'current_model' => $claude->getModelInfo()
            ];
        } catch (Exception $e) {
            $health['status']['available_models'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    // Overall health check
    $allHealthy = true;
    foreach ($health['status'] as $check) {
        if ($check['status'] === 'error') {
            $allHealthy = false;
            break;
        }
    }
    
    $health['overall_status'] = $allHealthy ? 'healthy' : 'degraded';
    
    if (!$allHealthy) {
        http_response_code(503); // Service Unavailable
        $health['success'] = false;
    }
    
    echo json_encode($health, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    $logger->error('Claude health check error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'type' => 'health_check_error',
            'message' => $e->getMessage()
        ]
    ], JSON_PRETTY_PRINT);
}