#!/usr/bin/env php
<?php
/**
 * Test New MCP Tools (MySQL, Password, Browser)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use IntelligenceHub\MCP\Tools\MySQLQueryTool;
use IntelligenceHub\MCP\Tools\PasswordStorageTool;
use IntelligenceHub\MCP\Tools\WebBrowserTool;

$tests = [];
$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void {
    global $tests, $passed, $failed;

    try {
        $result = $fn();
        if ($result === true) {
            $tests[] = "✅ PASS: {$name}";
            $passed++;
        } else {
            $tests[] = "❌ FAIL: {$name} - " . ($result ?: 'returned false');
            $failed++;
        }
    } catch (Exception $e) {
        $tests[] = "❌ FAIL: {$name} - Exception: " . $e->getMessage();
        $failed++;
    }
}

echo "===========================================\n";
echo "Testing New MCP Tools\n";
echo "===========================================\n\n";

// =====================================================================
// MySQL Tool Tests
// =====================================================================
echo "--- MySQL Tool Tests ---\n";

test('MySQL: SHOW TABLES query', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'SHOW TABLES LIMIT 5']);
    return $result['success'] && isset($result['data']['tables']);
});

test('MySQL: SELECT query with auto-limit', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'SELECT * FROM mcp_search_analytics']);
    return $result['success'] && isset($result['data']['rows']);
});

test('MySQL: Block INSERT query', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'INSERT INTO test VALUES (1)']);
    return !$result['success'] && str_contains($result['error'], 'read-only');
});

test('MySQL: Block INTO OUTFILE', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'SELECT * INTO OUTFILE "/tmp/bad"']);
    return !$result['success'] && str_contains($result['error'], 'dangerous');
});

test('MySQL: DESCRIBE table', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'DESCRIBE mcp_search_analytics']);
    return $result['success'] && isset($result['data']['columns']);
});

test('MySQL: CSV format output', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['query' => 'SHOW TABLES LIMIT 3', 'format' => 'csv']);
    return $result['success'] && isset($result['data']['csv']);
});

test('MySQL: Common queries helper', function() {
    $tool = new MySQLQueryTool();
    $result = $tool->execute(['action' => 'common_queries']);
    return $result['success'] && isset($result['data']['show_tables']);
});

echo "\n";

// =====================================================================
// Password Tool Tests
// =====================================================================
echo "--- Password Tool Tests ---\n";

$testService = 'test_service_' . time();

test('Password: Store new credential', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute([
        'action' => 'store',
        'service' => $testService,
        'username' => 'testuser',
        'password' => 'testpass123',
        'notes' => 'Test credential'
    ]);
    return $result['success'] && $result['data']['service'] === $testService;
});

test('Password: List credentials (hidden passwords)', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute(['action' => 'list']);
    return $result['success'] && isset($result['data']['credentials']);
});

test('Password: Retrieve without password reveal', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute([
        'action' => 'retrieve',
        'service' => $testService
    ]);
    return $result['success'] && $result['data']['password'] === '***HIDDEN***';
});

test('Password: Retrieve with password reveal', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute([
        'action' => 'retrieve',
        'service' => $testService,
        'show_password' => true
    ]);
    return $result['success'] && $result['data']['password'] === 'testpass123';
});

test('Password: Update credential', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute([
        'action' => 'update',
        'service' => $testService,
        'username' => 'newuser'
    ]);
    return $result['success'];
});

test('Password: Delete credential', function() use ($testService) {
    $tool = new PasswordStorageTool();
    $result = $tool->execute([
        'action' => 'delete',
        'service' => $testService
    ]);
    return $result['success'];
});

test('Password: Prevent duplicate service', function() use ($testService) {
    $tool = new PasswordStorageTool();
    // First store
    $tool->execute([
        'action' => 'store',
        'service' => $testService . '_dup',
        'username' => 'user1',
        'password' => 'pass1'
    ]);
    // Try to store again
    $result = $tool->execute([
        'action' => 'store',
        'service' => $testService . '_dup',
        'username' => 'user2',
        'password' => 'pass2'
    ]);
    // Cleanup
    $tool->execute(['action' => 'delete', 'service' => $testService . '_dup']);

    return !$result['success'] && str_contains($result['error'], 'already exists');
});

echo "\n";

// =====================================================================
// Browser Tool Tests
// =====================================================================
echo "--- Browser Tool Tests ---\n";

test('Browser: Fetch simple page', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'fetch',
        'url' => 'https://example.com'
    ]);
    return $result['success'] && $result['data']['http_code'] === 200;
});

test('Browser: Extract title', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'fetch',
        'url' => 'https://example.com'
    ]);
    return $result['success'] && !empty($result['data']['title']);
});

test('Browser: Extract links', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'fetch',
        'url' => 'https://example.com'
    ]);
    return $result['success'] && isset($result['data']['links']);
});

test('Browser: Get headers only', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'headers',
        'url' => 'https://example.com'
    ]);
    return $result['success'] && isset($result['data']['headers']);
});

test('Browser: Handle invalid URL', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'fetch',
        'url' => 'not-a-valid-url'
    ]);
    return !$result['success'];
});

test('Browser: Require URL parameter', function() {
    $tool = new WebBrowserTool();
    $result = $tool->execute([
        'action' => 'fetch'
    ]);
    return !$result['success'] && str_contains($result['error'], 'required');
});

echo "\n";

// =====================================================================
// Summary
// =====================================================================
echo "===========================================\n";
echo "Test Results:\n";
echo "===========================================\n";
foreach ($tests as $test) {
    echo $test . "\n";
}
echo "\n";
echo "Summary: {$passed} passed, {$failed} failed\n";
$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
echo "Pass rate: {$percentage}%\n";

exit($failed > 0 ? 1 : 0);
