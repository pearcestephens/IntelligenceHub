<?php

namespace IntelligenceHub\MCP\Tools;

class OperationsTools extends BaseTool {

    public function getName(): string {
        return 'ops';
    }

    public function getSchema(): array {
        return [
            'ops.ready_check' => [
                'description' => 'Check if system is ready for operation',
                'parameters' => []
            ],
            'ops.security_scan' => [
                'description' => 'Run security vulnerability scan',
                'parameters' => []
            ],
            'ops.performance_test' => [
                'description' => 'Run performance benchmarks',
                'parameters' => []
            ],
            'system.health' => [
                'description' => 'System health check',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'ready_check';

        switch ($method) {
            case 'ready_check':
                return $this->readyCheck();
            case 'security_scan':
                return $this->securityScan();
            case 'performance_test':
                return $this->performanceTest();
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function readyCheck(): array {
        $checks = [];

        // Check database
        try {
            $pdo = new \PDO(
                'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
                'hdgwrzntwa',
                $_ENV['DB_PASS'] ?? 'bFUdRjh4Jx'
            );
            $checks['database'] = 'OK';
        } catch (\Throwable $e) {
            $checks['database'] = 'FAIL: ' . $e->getMessage();
        }

        // Check filesystem
        $testFile = ($_ENV['TOOL_FS_ROOT'] ?? '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html') . '/.health_check';
        if (@file_put_contents($testFile, 'test') !== false) {
            @unlink($testFile);
            $checks['filesystem'] = 'OK';
        } else {
            $checks['filesystem'] = 'FAIL: Cannot write';
        }

        // Check memory
        $memLimit = ini_get('memory_limit');
        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $checks['memory'] = "OK - Using {$memUsage}MB / Limit: {$memLimit}";

        // Overall status
        $allOk = !str_contains(json_encode($checks), 'FAIL');

        return $this->ok([
            'ready' => $allOk,
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function securityScan(): array {
        $issues = [];

        // Check .env file permissions
        $envFile = dirname(__DIR__, 2) . '/.env';
        if (file_exists($envFile)) {
            $perms = substr(sprintf('%o', fileperms($envFile)), -4);
            if ($perms !== '0600' && $perms !== '0400') {
                $issues[] = ".env file has insecure permissions: $perms (should be 0600)";
            }
        } else {
            $issues[] = ".env file missing";
        }

        // Check for exposed sensitive files
        $sensitiveFiles = ['.git/config', 'composer.json', 'phpinfo.php'];
        foreach ($sensitiveFiles as $file) {
            $path = dirname(__DIR__, 2) . '/' . $file;
            if (file_exists($path) && is_readable($path)) {
                $issues[] = "Sensitive file exposed: $file";
            }
        }

        // Check PHP version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '<')) {
            $issues[] = "PHP version $phpVersion is outdated (< 8.0)";
        }

        $clean = count($issues) === 0;

        return $this->ok([
            'clean' => $clean,
            'issues' => $issues,
            'scanned_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function performanceTest(): array {
        $results = [];

        // Database query speed
        $start = microtime(true);
        try {
            $pdo = new \PDO(
                'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
                'hdgwrzntwa',
                $_ENV['DB_PASS'] ?? 'bFUdRjh4Jx'
            );
            $pdo->query('SELECT 1')->fetch();
            $dbTime = round((microtime(true) - $start) * 1000, 2);
            $results['database_query_ms'] = $dbTime;
            $results['database_status'] = $dbTime < 10 ? 'EXCELLENT' : ($dbTime < 50 ? 'GOOD' : 'SLOW');
        } catch (\Throwable $e) {
            $results['database_query_ms'] = null;
            $results['database_status'] = 'ERROR';
        }

        // File I/O speed
        $start = microtime(true);
        $testFile = ($_ENV['TOOL_FS_ROOT'] ?? '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html') . '/.perf_test';
        $testData = str_repeat('x', 1024 * 100); // 100KB
        file_put_contents($testFile, $testData);
        $read = file_get_contents($testFile);
        @unlink($testFile);
        $ioTime = round((microtime(true) - $start) * 1000, 2);
        $results['file_io_ms'] = $ioTime;
        $results['file_io_status'] = $ioTime < 50 ? 'EXCELLENT' : ($ioTime < 200 ? 'GOOD' : 'SLOW');

        // Memory allocation speed
        $start = microtime(true);
        $arr = array_fill(0, 10000, 'test');
        unset($arr);
        $memTime = round((microtime(true) - $start) * 1000, 2);
        $results['memory_allocation_ms'] = $memTime;

        return $this->ok([
            'results' => $results,
            'tested_at' => date('Y-m-d H:i:s')
        ]);
    }
}
