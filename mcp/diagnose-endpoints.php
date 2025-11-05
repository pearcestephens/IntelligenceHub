#!/usr/bin/env php
<?php
/**
 * MCP Server Endpoint Diagnostic Tool
 *
 * Tests all MCP endpoints, identifies connection issues,
 * and generates a comprehensive diagnostic report.
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(strict_types=1);

// Configuration
$MCP_SERVER_URL = 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';
$MCP_API_KEY = '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';

// Colors for terminal output
define('COLOR_RESET', "\033[0m");
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_RED', "\033[0;31m");
define('COLOR_YELLOW', "\033[0;33m");
define('COLOR_BLUE', "\033[0;34m");
define('COLOR_CYAN', "\033[0;36m");
define('COLOR_BOLD', "\033[1m");

// Test results
$results = [];
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

/**
 * Print colored output
 */
function printColor(string $text, string $color = COLOR_RESET): void {
    echo $color . $text . COLOR_RESET . PHP_EOL;
}

/**
 * Make MCP JSON-RPC request
 */
function makeRequest(string $method, array $params = []): array {
    global $MCP_SERVER_URL, $MCP_API_KEY;

    $startTime = microtime(true);

    $payload = [
        'jsonrpc' => '2.0',
        'method' => $method,
        'params' => $params,
        'id' => time()
    ];

    $ch = curl_init($MCP_SERVER_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $MCP_API_KEY,
            'X-Workspace-Root: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html',
            'X-Project-ID: 2',
            'X-Business-Unit-ID: 2'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);

    $responseTime = round((microtime(true) - $startTime) * 1000, 2);

    $data = null;
    if ($response) {
        $data = json_decode($response, true);
    }

    return [
        'method' => $method,
        'http_code' => $httpCode,
        'response_time_ms' => $responseTime,
        'curl_error' => $curlError,
        'response' => $data,
        'raw_response' => $response,
        'curl_info' => $curlInfo
    ];
}

/**
 * Test endpoint
 */
function testEndpoint(string $method, array $params = [], string $description = ''): array {
    global $totalTests, $passedTests, $failedTests, $results;

    $totalTests++;

    printColor("\n[TEST #{$totalTests}] {$description}", COLOR_CYAN);
    printColor("Method: {$method}", COLOR_BLUE);

    $result = makeRequest($method, $params);

    $status = 'UNKNOWN';
    $issues = [];

    // Analyze result
    if ($result['curl_error']) {
        $status = 'FAIL';
        $issues[] = 'cURL Error: ' . $result['curl_error'];
    } elseif ($result['http_code'] !== 200) {
        $status = 'FAIL';
        $issues[] = 'HTTP Status: ' . $result['http_code'];
    } elseif (!$result['response']) {
        $status = 'FAIL';
        $issues[] = 'No valid JSON response';
    } elseif (isset($result['response']['error'])) {
        $status = 'FAIL';
        $issues[] = 'JSON-RPC Error: ' . ($result['response']['error']['message'] ?? 'Unknown');
    } elseif (isset($result['response']['result'])) {
        $status = 'PASS';
        $passedTests++;
    } else {
        $status = 'FAIL';
        $issues[] = 'Invalid response structure';
    }

    if ($status === 'FAIL') {
        $failedTests++;
    }

    // Print result
    if ($status === 'PASS') {
        printColor("✓ PASS ({$result['response_time_ms']}ms)", COLOR_GREEN);
    } else {
        printColor("✗ FAIL ({$result['response_time_ms']}ms)", COLOR_RED);
        foreach ($issues as $issue) {
            printColor("  └─ {$issue}", COLOR_YELLOW);
        }
    }

    $testResult = [
        'test_number' => $totalTests,
        'description' => $description,
        'method' => $method,
        'params' => $params,
        'status' => $status,
        'issues' => $issues,
        'http_code' => $result['http_code'],
        'response_time_ms' => $result['response_time_ms'],
        'response' => $result['response']
    ];

    $results[] = $testResult;

    return $testResult;
}

// ====================================================================
// START DIAGNOSTIC TESTS
// ====================================================================

printColor("\n" . str_repeat("=", 70), COLOR_BOLD);
printColor("MCP SERVER DIAGNOSTIC REPORT", COLOR_BOLD);
printColor(str_repeat("=", 70), COLOR_BOLD);
printColor("Server: {$MCP_SERVER_URL}", COLOR_CYAN);
printColor("Date: " . date('Y-m-d H:i:s'), COLOR_CYAN);
printColor(str_repeat("=", 70) . "\n", COLOR_BOLD);

// Test 1: Server Discovery
testEndpoint('tools/list', [], 'List all available tools');

// Test 2: Database Operations
testEndpoint('db.query', [
    'query' => 'SELECT VERSION() as version',
    'return_format' => 'array'
], 'Execute database query');

// Test 3: File System
testEndpoint('fs.read', [
    'path' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/README.md',
    'encoding' => 'utf8'
], 'Read file from filesystem');

// Test 4: Semantic Search
testEndpoint('semantic_search', [
    'query' => 'MCP server configuration',
    'limit' => 5
], 'Semantic code search');

// Test 5: Conversation Memory
testEndpoint('conversation.get_project_context', [
    'project_id' => 2,
    'limit' => 5
], 'Retrieve conversation context');

// Test 6: Knowledge Base
testEndpoint('kb.search', [
    'query' => 'database',
    'limit' => 5
], 'Search knowledge base');

// Test 7: Memory Store
testEndpoint('memory.store', [
    'conversation_id' => 999999,
    'content' => 'Diagnostic test memory',
    'memory_type' => 'test',
    'importance' => 'low',
    'tags' => ['diagnostic', 'test']
], 'Store memory entry');

// Test 8: AI Agent Query
testEndpoint('ai_agent.query', [
    'query' => 'What is the MCP server version?',
    'context' => 'diagnostic test',
    'stream' => false
], 'Query AI Agent');

// Test 9: Logs Access
testEndpoint('logs.tail', [
    'log_file' => 'application',
    'lines' => 10
], 'Tail log file');

// Test 10: Database Schema
testEndpoint('db.schema', [
    'table' => 'conversations'
], 'Get database schema');

// ====================================================================
// GENERATE REPORT
// ====================================================================

printColor("\n" . str_repeat("=", 70), COLOR_BOLD);
printColor("TEST SUMMARY", COLOR_BOLD);
printColor(str_repeat("=", 70), COLOR_BOLD);

printColor("\nTotal Tests: {$totalTests}", COLOR_CYAN);
printColor("Passed: {$passedTests}", COLOR_GREEN);
printColor("Failed: {$failedTests}", COLOR_RED);
printColor("Success Rate: " . round(($passedTests / $totalTests) * 100, 1) . "%\n", COLOR_YELLOW);

// Failed tests details
if ($failedTests > 0) {
    printColor(str_repeat("=", 70), COLOR_BOLD);
    printColor("FAILED TESTS ANALYSIS", COLOR_BOLD);
    printColor(str_repeat("=", 70), COLOR_BOLD);

    foreach ($results as $result) {
        if ($result['status'] === 'FAIL') {
            printColor("\n[#{$result['test_number']}] {$result['description']}", COLOR_RED);
            printColor("Method: {$result['method']}", COLOR_YELLOW);
            foreach ($result['issues'] as $issue) {
                printColor("  └─ {$issue}", COLOR_YELLOW);
            }
        }
    }
}

// Connection diagnostics
printColor("\n" . str_repeat("=", 70), COLOR_BOLD);
printColor("CONNECTION DIAGNOSTICS", COLOR_BOLD);
printColor(str_repeat("=", 70) . "\n", COLOR_BOLD);

// Test basic connectivity
$ch = curl_init($MCP_SERVER_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$sslVerify = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
$connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

printColor("Server Reachability: " . ($httpCode > 0 ? 'YES' : 'NO'),
    $httpCode > 0 ? COLOR_GREEN : COLOR_RED);
printColor("HTTP Status Code: {$httpCode}", $httpCode === 200 ? COLOR_GREEN : COLOR_YELLOW);
printColor("SSL Certificate: " . ($sslVerify === 0 ? 'Valid' : 'Invalid'),
    $sslVerify === 0 ? COLOR_GREEN : COLOR_RED);
printColor("Connection Time: " . round($connectTime * 1000, 2) . "ms", COLOR_CYAN);
printColor("Total Time: " . round($totalTime * 1000, 2) . "ms", COLOR_CYAN);

// WHY CONNECTIONS FAIL - Analysis
printColor("\n" . str_repeat("=", 70), COLOR_BOLD);
printColor("WHY MCP CONNECTIONS FAIL - ROOT CAUSE ANALYSIS", COLOR_BOLD);
printColor(str_repeat("=", 70) . "\n", COLOR_BOLD);

$commonIssues = [
    "1. MISSING .env FILE" => [
        "Problem: mcp-health-monitor.php loads .env from __DIR__/.env",
        "Reality: .env is located at ../../private_html/config/.env",
        "Impact: Database credentials fall back to 'root'@'localhost' (no password)",
        "Fix: Update .env path or create symlink"
    ],
    "2. INCORRECT ENVIRONMENT VARIABLES" => [
        "Problem: Script uses DB_USERNAME, DB_DATABASE, DB_PASSWORD",
        "Reality: .env uses DB_USER, DB_NAME (inconsistent naming)",
        "Impact: Variables not found, defaults used",
        "Fix: Standardize variable names across all scripts"
    ],
    "3. API KEY VALIDATION" => [
        "Problem: Some tools may require API key in header",
        "Reality: Header 'X-API-Key' must match server expectation",
        "Impact: 401/403 errors on protected endpoints",
        "Fix: Verify API key is sent and matches"
    ],
    "4. METHOD NOT FOUND ERRORS" => [
        "Problem: GitHub Copilot sends 'health' method (not in MCP spec)",
        "Reality: Standard MCP methods: tools/list, tools/call",
        "Impact: -32601 'Unknown method' errors",
        "Fix: Use standard MCP protocol methods only"
    ],
    "5. WORKSPACE CONTEXT DETECTION" => [
        "Problem: Some tools require workspace context",
        "Reality: Headers X-Workspace-Root, X-Current-File needed",
        "Impact: Tools fail or return incomplete results",
        "Fix: Always send workspace context headers"
    ],
    "6. DATABASE CONNECTION POOLING" => [
        "Problem: New PDO connection created per health check",
        "Reality: Should reuse connections for performance",
        "Impact: Connection overhead, potential exhaustion",
        "Fix: Implement connection pooling/reuse"
    ],
    "7. PCNTL SIGNAL HANDLING" => [
        "Problem: declare(ticks=1) required for signal handling",
        "Reality: May not work in all PHP environments",
        "Impact: Graceful shutdown may fail",
        "Fix: Check pcntl extension availability"
    ],
    "8. FILE PERMISSIONS" => [
        "Problem: Health monitor writes to logs/health-monitor.log",
        "Reality: Directory may not exist or be writable",
        "Impact: Permission denied errors, silent failures",
        "Fix: Ensure log directory exists with correct permissions"
    ]
];

foreach ($commonIssues as $title => $details) {
    printColor($title, COLOR_RED . COLOR_BOLD);
    foreach ($details as $detail) {
        printColor("  {$detail}", COLOR_YELLOW);
    }
    echo "\n";
}

// Recommendations
printColor(str_repeat("=", 70), COLOR_BOLD);
printColor("RECOMMENDATIONS", COLOR_BOLD);
printColor(str_repeat("=", 70) . "\n", COLOR_BOLD);

$recommendations = [
    "✓ Create /mcp/.env or symlink to private_html/config/.env",
    "✓ Standardize environment variable names (DB_USER vs DB_USERNAME)",
    "✓ Implement connection pooling in health monitor",
    "✓ Add pre-flight checks before service start (file perms, db connect)",
    "✓ Use standard MCP protocol methods only",
    "✓ Document all required headers in API documentation",
    "✓ Implement exponential backoff for failed health checks",
    "✓ Add Prometheus/Grafana metrics export",
    "✓ Create unified .env loader library for consistency",
    "✓ Add automated tests for all MCP endpoints"
];

foreach ($recommendations as $rec) {
    printColor($rec, COLOR_GREEN);
}

// Save JSON report
$reportFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/diagnostic-report-' . date('Y-m-d-H-i-s') . '.json';
file_put_contents($reportFile, json_encode([
    'timestamp' => date('c'),
    'server_url' => $MCP_SERVER_URL,
    'total_tests' => $totalTests,
    'passed_tests' => $passedTests,
    'failed_tests' => $failedTests,
    'success_rate' => round(($passedTests / $totalTests) * 100, 1),
    'results' => $results,
    'common_issues' => $commonIssues,
    'recommendations' => $recommendations
], JSON_PRETTY_PRINT));

printColor("\n" . str_repeat("=", 70), COLOR_BOLD);
printColor("JSON report saved: {$reportFile}", COLOR_CYAN);
printColor(str_repeat("=", 70) . "\n", COLOR_BOLD);
