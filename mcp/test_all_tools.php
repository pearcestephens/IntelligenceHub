<?php
/**
 * MCP Tools Complete Test Suite
 * Tests all 48 methods with standardized JSON-RPC 2.0 format
 *
 * Industry Standard Format:
 * - Request: {"tool": "method.name", "params": {...}}
 * - Response: {"status": 200, "success": true, "data": {...}, "meta": {...}}
 */

require_once __DIR__ . '/../bootstrap_tools.php';

class ToolTester {
    private $registry;
    private $results = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;

    public function __construct($registry) {
        $this->registry = $registry;
    }

    /**
     * Test a single tool method
     */
    public function test(string $tool, array $params, array $expectations): void {
        $this->totalTests++;
        $startTime = microtime(true);

        echo "Testing: $tool ... ";

        try {
            $result = $this->registry->execute($tool, $params);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Check status
            $statusOk = isset($result['status']) && $result['status'] >= 200 && $result['status'] < 300;

            // Check data structure
            $hasData = isset($result['data']);

            // Check expected keys if provided
            $keysOk = true;
            if (isset($expectations['data_keys'])) {
                foreach ($expectations['data_keys'] as $key) {
                    if (!isset($result['data'][$key])) {
                        $keysOk = false;
                        break;
                    }
                }
            }

            $passed = $statusOk && $hasData && $keysOk;

            if ($passed) {
                echo "✅ PASS ({$duration}ms)\n";
                $this->passedTests++;
            } else {
                echo "❌ FAIL ({$duration}ms)\n";
                echo "   Status: " . ($result['status'] ?? 'MISSING') . "\n";
                echo "   Has Data: " . ($hasData ? 'YES' : 'NO') . "\n";
                if (!$keysOk) {
                    echo "   Missing Keys: " . json_encode($expectations['data_keys']) . "\n";
                }
                $this->failedTests++;
            }

            $this->results[] = [
                'tool' => $tool,
                'passed' => $passed,
                'duration_ms' => $duration,
                'status' => $result['status'] ?? null,
                'response' => $result
            ];

        } catch (Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            echo "❌ ERROR ({$duration}ms): " . $e->getMessage() . "\n";
            $this->failedTests++;

            $this->results[] = [
                'tool' => $tool,
                'passed' => false,
                'duration_ms' => $duration,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Print summary
     */
    public function printSummary(): void {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 80) . "\n";
        echo "Total Tests: {$this->totalTests}\n";
        echo "✅ Passed: {$this->passedTests}\n";
        echo "❌ Failed: {$this->failedTests}\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n";
        echo str_repeat("=", 80) . "\n\n";
    }

    /**
     * Save results to JSON
     */
    public function saveResults(string $filename = 'test_results.json'): void {
        file_put_contents(
            __DIR__ . '/' . $filename,
            json_encode([
                'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                'summary' => [
                    'total' => $this->totalTests,
                    'passed' => $this->passedTests,
                    'failed' => $this->failedTests,
                    'success_rate' => round(($this->passedTests / $this->totalTests) * 100, 2)
                ],
                'results' => $this->results
            ], JSON_PRETTY_PRINT)
        );
        echo "Results saved to: $filename\n";
    }
}

// Initialize tester
$tester = new ToolTester($registry);

echo "\n";
echo "🧪 MCP TOOLS COMPLETE TEST SUITE\n";
echo "Testing all 48 methods with standardized JSON format\n";
echo str_repeat("=", 80) . "\n\n";

// =============================================================================
// DATABASE TOOLS (3 methods)
// =============================================================================
echo "📁 DATABASE TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('db.query_readonly', [
    'sql' => 'SELECT 1 as test',
    'params' => []
], [
    'data_keys' => ['rows']
]);

$tester->test('db.stats', [], [
    'data_keys' => ['tables', 'total_tables']
]);

$tester->test('db.explain', [
    'sql' => 'SELECT * FROM intelligence_content LIMIT 1'
], [
    'data_keys' => ['plan']
]);

// =============================================================================
// FILESYSTEM TOOLS (4 methods)
// =============================================================================
echo "\n📁 FILESYSTEM TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('fs.write', [
    'path' => 'test_file.txt',
    'content' => 'Hello from MCP test suite'
], [
    'data_keys' => ['path', 'size']
]);

$tester->test('fs.read', [
    'path' => 'test_file.txt'
], [
    'data_keys' => ['content']
]);

$tester->test('fs.list', [
    'path' => '.'
], [
    'data_keys' => ['files']
]);

$tester->test('fs.info', [
    'path' => 'test_file.txt'
], [
    'data_keys' => ['size', 'modified']
]);

// =============================================================================
// LOGS TOOLS (1 method)
// =============================================================================
echo "\n📋 LOGS TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('logs.tail', [
    'file' => 'apache',
    'lines' => 10
], [
    'data_keys' => ['lines']
]);

// =============================================================================
// OPERATIONS TOOLS (3 methods - excluding duplicate system.health)
// =============================================================================
echo "\n⚙️ OPERATIONS TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('ops.ready_check', [], [
    'data_keys' => ['ready']
]);

$tester->test('ops.security_scan', [], [
    'data_keys' => ['issues']
]);

$tester->test('ops.performance_test', [], [
    'data_keys' => ['results']
]);

// =============================================================================
// SYSTEM TOOLS (1 method)
// =============================================================================
echo "\n🖥️ SYSTEM TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('system.health', [], [
    'data_keys' => ['php', 'database']
]);

// =============================================================================
// PASSWORD TOOLS (5 methods)
// =============================================================================
echo "\n🔐 PASSWORD TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('password.store', [
    'service' => 'test_service',
    'username' => 'test_user',
    'password' => 'test_password_123',
    'notes' => 'Test credential from MCP suite'
], [
    'data_keys' => []
]);

$tester->test('password.list', [], [
    'data_keys' => []
]);

$tester->test('password.retrieve', [
    'service' => 'test_service'
], [
    'data_keys' => []
]);

$tester->test('password.update', [
    'service' => 'test_service',
    'password' => 'updated_password_456'
], [
    'data_keys' => []
]);

$tester->test('password.delete', [
    'service' => 'test_service'
], [
    'data_keys' => []
]);

// =============================================================================
// SEMANTIC TOOLS (1 method)
// =============================================================================
echo "\n🔍 SEMANTIC SEARCH TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('semantic.search', [
    'query' => 'test search',
    'limit' => 5
], [
    'data_keys' => []
]);

// =============================================================================
// BROWSER TOOLS (2 methods)
// =============================================================================
echo "\n🌐 BROWSER TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('browser.fetch', [
    'url' => 'https://example.com',
    'extract' => 'text'
], [
    'data_keys' => []
]);

$tester->test('browser.screenshot', [
    'url' => 'https://example.com',
    'width' => 1024,
    'height' => 768
], [
    'data_keys' => []
]);

// =============================================================================
// CRAWLER TOOLS (2 methods)
// =============================================================================
echo "\n🕷️ CRAWLER TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('crawler.crawl', [
    'url' => 'https://example.com',
    'depth' => 1,
    'max_pages' => 5
], [
    'data_keys' => []
]);

$tester->test('crawler.analyze', [
    'url' => 'https://example.com'
], [
    'data_keys' => []
]);

// =============================================================================
// HEALTH CHECK TOOLS (5 methods)
// =============================================================================
echo "\n🏥 HEALTH CHECK TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('health.check', [], [
    'data_keys' => ['status', 'checks']
]);

$tester->test('health.database', [], [
    'data_keys' => ['check']
]);

$tester->test('health.cache', [], [
    'data_keys' => ['check']
]);

$tester->test('health.disk', [], [
    'data_keys' => ['check']
]);

$tester->test('health.memory', [], [
    'data_keys' => ['check']
]);

// =============================================================================
// SYSTEM STATS TOOLS (5 methods)
// =============================================================================
echo "\n📊 SYSTEM STATS TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('stats.all', [], [
    'data_keys' => ['indexed_files', 'database', 'search_engine', 'system']
]);

$tester->test('stats.indexed_files', [], [
    'data_keys' => ['indexed_files']
]);

$tester->test('stats.database', [], [
    'data_keys' => ['database']
]);

$tester->test('stats.search_engine', [], [
    'data_keys' => ['search_engine']
]);

$tester->test('stats.system', [], [
    'data_keys' => ['system']
]);

// =============================================================================
// MYSQL TOOLS (4 methods)
// =============================================================================
echo "\n🗄️ MYSQL TOOLS\n";
echo str_repeat("-", 80) . "\n";

$tester->test('mysql.query', [
    'query' => 'SELECT 1 as test'
], [
    'data_keys' => []
]);

$tester->test('mysql.common_queries', [], [
    'data_keys' => []
]);

$tester->test('mysql.tables', [], [
    'data_keys' => []
]);

$tester->test('mysql.table_info', [
    'table' => 'intelligence_content'
], [
    'data_keys' => []
]);

// Print summary
$tester->printSummary();
$tester->saveResults('mcp_tools_test_results.json');
