<?php

namespace IntelligenceHub\MCP\Tools;

class SystemTools extends BaseTool {

    public function getName(): string {
        return 'system';
    }

    public function getSchema(): array {
        return [
            'system.health' => [
                'description' => 'Comprehensive system health check',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'health';

        switch ($method) {
            case 'health':
                return $this->health();
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function health(): array {
        $health = [];

        // PHP Info
        $health['php'] = [
            'version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'curl' => extension_loaded('curl'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring')
            ]
        ];

        // Database
        try {
            $pdo = new \PDO(
                'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4',
                'hdgwrzntwa',
                $_ENV['DB_PASS'] ?? 'bFUdRjh4Jx',
                [\PDO::ATTR_TIMEOUT => 3]
            );
            $stmt = $pdo->query('SELECT VERSION() as version');
            $version = $stmt->fetch(\PDO::FETCH_ASSOC)['version'];
            $health['database'] = [
                'status' => 'connected',
                'version' => $version,
                'host' => '127.0.0.1',
                'name' => 'hdgwrzntwa'
            ];
        } catch (\Throwable $e) {
            $health['database'] = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }

        // Filesystem
        $fsRoot = $_ENV['TOOL_FS_ROOT'] ?? '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html';
        $health['filesystem'] = [
            'root' => $fsRoot,
            'exists' => is_dir($fsRoot),
            'readable' => is_readable($fsRoot),
            'writable' => is_writable($fsRoot),
            'free_space' => disk_free_space($fsRoot),
            'free_space_gb' => round(disk_free_space($fsRoot) / 1024 / 1024 / 1024, 2)
        ];

        // Memory
        $health['memory'] = [
            'current_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'limit' => ini_get('memory_limit')
        ];

        // Server
        $health['server'] = [
            'hostname' => gethostname(),
            'os' => PHP_OS,
            'load' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            'uptime' => $this->getUptime()
        ];

        // Overall status
        $health['status'] = 'healthy';
        if ($health['database']['status'] !== 'connected') {
            $health['status'] = 'degraded';
        }
        if (!$health['filesystem']['writable']) {
            $health['status'] = 'degraded';
        }

        $health['timestamp'] = date('Y-m-d H:i:s');

        return $this->ok($health);
    }

    private function getUptime(): ?string {
        if (file_exists('/proc/uptime')) {
            $uptime = file_get_contents('/proc/uptime');
            $seconds = (int)explode(' ', $uptime)[0];
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return "{$days}d {$hours}h {$minutes}m";
        }
        return null;
    }
}
