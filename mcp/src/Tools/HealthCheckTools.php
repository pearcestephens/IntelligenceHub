<?php

namespace IntelligenceHub\MCP\Tools;

class HealthCheckTools extends BaseTool {
    private HealthCheckTool $legacy;

    public function __construct() {
        $this->legacy = new HealthCheckTool();
    }

    public function getName(): string {
        return 'health';
    }

    public function getSchema(): array {
        return [
            'health.check' => [
                'description' => 'Comprehensive system health check (database, cache, disk, memory, content index)',
                'parameters' => []
            ],
            'health.database' => [
                'description' => 'Check database connectivity and performance',
                'parameters' => []
            ],
            'health.cache' => [
                'description' => 'Check cache system status',
                'parameters' => []
            ],
            'health.disk' => [
                'description' => 'Check disk space and I/O',
                'parameters' => []
            ],
            'health.memory' => [
                'description' => 'Check memory usage',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'check';

        // Legacy tool only has execute() method, returns full health check
        $result = $this->legacy->execute();

        // Convert response format: ['success' => bool, 'data' => array] -> ['status' => int, 'data' => array]
        if (isset($result['success'])) {
            if ($result['success']) {
                $data = $result['data'] ?? [];

                // If specific check requested, filter to that section
                if ($method !== 'check' && isset($data['checks'][$method])) {
                    return $this->ok(['check' => $data['checks'][$method]]);
                }

                return $this->ok($data);
            } else {
                return $this->fail($result['error'] ?? 'Health check failed');
            }
        }

        return $result;
    }
}
