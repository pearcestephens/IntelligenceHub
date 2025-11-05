<?php

namespace IntelligenceHub\MCP\Tools;

class SystemStatsTools extends BaseTool {
    private SystemStatsTool $legacy;

    public function __construct() {
        $this->legacy = new SystemStatsTool();
    }

    public function getName(): string {
        return 'stats';
    }

    public function getSchema(): array {
        return [
            'stats.all' => [
                'description' => 'Get all system statistics (indexed files, database, search engine, system info)',
                'parameters' => []
            ],
            'stats.indexed_files' => [
                'description' => 'Get count of indexed files',
                'parameters' => []
            ],
            'stats.database' => [
                'description' => 'Get database statistics',
                'parameters' => []
            ],
            'stats.search_engine' => [
                'description' => 'Get search engine information',
                'parameters' => []
            ],
            'stats.system' => [
                'description' => 'Get system information',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'all';

        // Execute legacy tool
        $result = $this->legacy->execute();

        // Convert response format
        if (isset($result['success'])) {
            if ($result['success']) {
                $data = $result['data'] ?? [];

                // If specific stat requested, return just that section
                if ($method !== 'all' && isset($data[$method])) {
                    return $this->ok([$method => $data[$method]]);
                }

                return $this->ok($data);
            } else {
                return $this->fail($result['error'] ?? 'Stats retrieval failed');
            }
        }

        return $result;
    }
}
