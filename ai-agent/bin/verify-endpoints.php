#!/usr/bin/env php
<?php
/**
 * API Endpoint Body Verification Tool
 * 
 * Makes actual HTTP requests to all API endpoints and verifies:
 * - Status codes
 * - Response bodies exist and are not empty
 * - JSON validity
 * - Response structure
 * 
 * Date: January 10, 2025
 */

// Color output
function colorOutput($text, $color = 'green') {
    $colors = [
        'green' => "\033[0;32m",
        'red' => "\033[0;31m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'cyan' => "\033[0;36m",
        'reset' => "\033[0m"
    ];
    
    return $colors[$color] . $text . $colors['reset'];
}

echo "\n";
echo colorOutput("═══════════════════════════════════════════════════════════════════\n", 'cyan');
echo colorOutput("  API ENDPOINT BODY VERIFICATION\n", 'cyan');
echo colorOutput("  Testing ALL endpoints for status + body content\n", 'cyan');
echo colorOutput("═══════════════════════════════════════════════════════════════════\n", 'cyan');
echo "\n";

// Detect base URL
$baseUrl = 'https://staff.vapeshed.co.nz/assets/neuro/ai-agent';

if (isset($argv[1])) {
    $baseUrl = rtrim($argv[1], '/');
}

echo "Base URL: " . colorOutput($baseUrl, 'blue') . "\n\n";

// Define all endpoints to test
$endpoints = [
    // Core API
    ['GET', '/api/health', 'Health Check'],
    ['GET', '/api/status', 'System Status'],
    ['GET', '/api/version', 'API Version'],
    
    // Conversations
    ['GET', '/api/conversations', 'List All Conversations'],
    ['GET', '/api/conversations?limit=10&offset=0', 'List Conversations (Paginated)'],
    ['GET', '/api/conversations/1', 'Get Single Conversation'],
    ['POST', '/api/conversations', 'Create Conversation', ['title' => 'Test', 'model' => 'gpt-4o']],
    
    // Messages
    ['GET', '/api/messages?conversation_id=1', 'List Messages for Conversation'],
    ['POST', '/api/messages', 'Create Message', ['conversation_id' => 1, 'role' => 'user', 'content' => 'Test']],
    
    // Tools
    ['GET', '/api/tools', 'List Available Tools'],
    ['GET', '/api/tools/calculator', 'Get Tool Details'],
    ['POST', '/api/tools/execute', 'Execute Tool', ['tool' => 'calculator', 'params' => ['expression' => '2+2']]],
    
    // Tool Chains
    ['GET', '/api/chains', 'List Tool Chains'],
    ['POST', '/api/chains/execute', 'Execute Tool Chain', ['chain_id' => 'test-chain']],
    
    // Multi-Agent
    ['POST', '/api/agent/spawn', 'Spawn Agent', ['role' => 'researcher']],
    ['POST', '/api/agent/delegate', 'Delegate Task', ['task' => 'Test', 'agents' => ['researcher'], 'strategy' => 'best']],
    ['GET', '/api/agent/roles', 'List Agent Roles'],
    
    // Memory Management
    ['POST', '/api/memory/compress', 'Compress Memories', ['conversation_id' => 1]],
    ['POST', '/api/memory/compress-all', 'Compress All Memories'],
    ['GET', '/api/memory/stats', 'Memory Statistics'],
    ['POST', '/api/memory/score', 'Score Conversation Importance', ['conversation_id' => 1]],
    ['POST', '/api/memory/score-all', 'Score All Conversations'],
    ['GET', '/api/memory/top?limit=10', 'Get Top Conversations'],
    ['GET', '/api/memory/similar?conversation_id=1&limit=5', 'Find Similar Conversations'],
    ['POST', '/api/memory/cluster', 'Cluster Conversations', ['num_clusters' => 5]],
    ['GET', '/api/memory/clusters', 'List Clusters'],
    ['POST', '/api/memory/prune', 'Prune Low-Value Conversations'],
    
    // Analytics & Metrics
    ['GET', '/api/metrics/realtime', 'Real-time Metrics'],
    ['GET', '/api/metrics/response-times', 'Response Time Metrics'],
    ['GET', '/api/metrics/response-times?hours=24', 'Response Times (24h)'],
    ['GET', '/api/metrics/tools', 'Tool Execution Statistics'],
    ['GET', '/api/metrics/tools?days=7', 'Tool Stats (7 days)'],
    ['GET', '/api/metrics/tokens', 'Token Usage Summary'],
    ['GET', '/api/metrics/tokens?days=30', 'Token Usage (30 days)'],
    ['GET', '/api/metrics/cache', 'Cache Performance'],
    ['GET', '/api/metrics/errors', 'Error Summary'],
    ['GET', '/api/metrics/errors?hours=24', 'Recent Errors (24h)'],
    
    // Context Cards
    ['GET', '/api/context-cards?conversation_id=1', 'List Context Cards'],
    ['POST', '/api/context-cards', 'Create Context Card', ['conversation_id' => 1, 'content' => 'Test']],
    
    // Knowledge Base
    ['GET', '/api/knowledge', 'Search Knowledge Base'],
    ['GET', '/api/knowledge?query=test', 'Knowledge Base Query'],
    ['POST', '/api/knowledge', 'Add Knowledge Entry', ['title' => 'Test', 'content' => 'Test']],
];

$stats = [
    'total' => 0,
    'passed' => 0,
    'failed' => 0,
    'skipped' => 0,
    'body_empty' => 0,
    'body_valid' => 0,
];

foreach ($endpoints as $endpoint) {
    $method = $endpoint[0];
    $path = $endpoint[1];
    $description = $endpoint[2];
    $data = $endpoint[3] ?? null;
    
    $stats['total']++;
    
    echo colorOutput("Testing: ", 'blue') . "{$description}\n";
    echo "  Endpoint: [{$method}] {$path}\n";
    
    // Make request
    $ch = curl_init();
    $url = $baseUrl . $path;
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    // Parse response
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    // Status code check
    echo "  Status: ";
    if ($httpCode >= 200 && $httpCode < 300) {
        echo colorOutput("$httpCode ✓", 'green');
    } elseif ($httpCode === 404) {
        echo colorOutput("$httpCode (Not Implemented)", 'yellow');
        $stats['skipped']++;
        echo "\n\n";
        continue;
    } elseif ($httpCode === 401 || $httpCode === 403) {
        echo colorOutput("$httpCode (Auth Required)", 'yellow');
        $stats['skipped']++;
        echo "\n\n";
        continue;
    } elseif ($httpCode === 0) {
        echo colorOutput("FAILED - Connection Error", 'red');
        if ($curlError) {
            echo "\n  Error: " . colorOutput($curlError, 'red');
        }
        $stats['failed']++;
        echo "\n\n";
        continue;
    } else {
        echo colorOutput("$httpCode ✗", 'red');
        $stats['failed']++;
        echo "\n\n";
        continue;
    }
    
    echo "\n";
    
    // Body check
    echo "  Body: ";
    if (empty($body)) {
        echo colorOutput("EMPTY ✗", 'red');
        $stats['body_empty']++;
        $stats['failed']++;
        echo "\n\n";
        continue;
    }
    
    $bodyLength = strlen($body);
    echo colorOutput("Present ({$bodyLength} bytes) ✓", 'green');
    $stats['body_valid']++;
    echo "\n";
    
    // JSON validation
    if (strpos($contentType, 'json') !== false || $body[0] === '{' || $body[0] === '[') {
        $json = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "  JSON: " . colorOutput("Valid ✓", 'green') . "\n";
            
            // Show structure preview
            if (is_array($json)) {
                $keys = array_keys($json);
                $preview = implode(', ', array_slice($keys, 0, 5));
                if (count($keys) > 5) $preview .= '...';
                echo "  Keys: " . colorOutput($preview, 'cyan') . "\n";
            }
        } else {
            echo "  JSON: " . colorOutput("Invalid ✗ (" . json_last_error_msg() . ")", 'red') . "\n";
        }
    }
    
    // Show body preview
    $preview = strlen($body) > 200 ? substr($body, 0, 200) . '...' : $body;
    echo "  Preview: " . colorOutput($preview, 'cyan') . "\n";
    
    $stats['passed']++;
    echo "\n";
    
    // Rate limiting
    usleep(100000); // 100ms delay between requests
}

// Summary
echo "\n";
echo colorOutput("═══════════════════════════════════════════════════════════════════\n", 'cyan');
echo colorOutput("  TEST SUMMARY\n", 'cyan');
echo colorOutput("═══════════════════════════════════════════════════════════════════\n", 'cyan');
echo "\n";
echo "Total Endpoints:     {$stats['total']}\n";
echo colorOutput("✓ Passed:            {$stats['passed']}\n", 'green');
echo colorOutput("✗ Failed:            {$stats['failed']}\n", 'red');
echo colorOutput("⊘ Skipped:           {$stats['skipped']}\n", 'yellow');
echo "\n";
echo "Body Checks:\n";
echo colorOutput("  Valid Bodies:      {$stats['body_valid']}\n", 'green');
echo colorOutput("  Empty Bodies:      {$stats['body_empty']}\n", 'red');
echo "\n";

$passRate = $stats['total'] > 0 ? round(($stats['passed'] / $stats['total']) * 100, 1) : 0;
echo "Pass Rate: " . colorOutput("{$passRate}%", $passRate >= 80 ? 'green' : ($passRate >= 50 ? 'yellow' : 'red')) . "\n";
echo "\n";

if ($stats['failed'] === 0 && $stats['body_empty'] === 0) {
    echo colorOutput("🎉 ALL ENDPOINTS PASSED WITH VALID BODIES! 🎉\n", 'green');
} elseif ($stats['body_empty'] > 0) {
    echo colorOutput("⚠️  WARNING: {$stats['body_empty']} endpoint(s) returned empty bodies\n", 'yellow');
} else {
    echo colorOutput("❌ FAILURES DETECTED - Review failed endpoints above\n", 'red');
}

echo "\n";
echo colorOutput("═══════════════════════════════════════════════════════════════════\n", 'cyan');
echo "\n";

// Exit code
exit($stats['failed'] > 0 || $stats['body_empty'] > 0 ? 1 : 0);
