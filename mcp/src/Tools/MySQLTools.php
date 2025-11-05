<?php

namespace IntelligenceHub\MCP\Tools;

class MySQLTools extends BaseTool {
    private MySQLQueryTool $legacy;

    public function __construct() {
        $this->legacy = new MySQLQueryTool();
    }

    public function getName(): string {
        return 'mysql';
    }

    public function getSchema(): array {
        return [
            'mysql.query' => [
                'description' => 'Execute safe read-only SQL query (SELECT, SHOW, DESCRIBE, EXPLAIN)',
                'parameters' => [
                    'query' => ['type' => 'string', 'required' => true],
                    'limit' => ['type' => 'integer', 'required' => false],
                    'format' => ['type' => 'string', 'required' => false]
                ]
            ],
            'mysql.common_queries' => [
                'description' => 'Get list of common useful queries',
                'parameters' => []
            ],
            'mysql.tables' => [
                'description' => 'Show all database tables',
                'parameters' => []
            ],
            'mysql.table_info' => [
                'description' => 'Describe table structure',
                'parameters' => [
                    'table' => ['type' => 'string', 'required' => true]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'query';

        // Prepare params for legacy tool
        $params = [];

        switch ($method) {
            case 'common_queries':
                $params = ['action' => 'common_queries'];
                break;

            case 'tables':
                $params = ['query' => 'SHOW TABLES'];
                break;

            case 'table_info':
                if (!isset($args['table'])) {
                    return $this->fail('table parameter is required');
                }
                $params = ['query' => 'DESCRIBE ' . $args['table']];
                break;

            case 'query':
            default:
                $params = $args;
                unset($params['_method']);
                break;
        }

        // Execute legacy tool
        $result = $this->legacy->execute($params);

        // Convert response format
        if (isset($result['success'])) {
            if ($result['success']) {
                return $this->ok($result['data'] ?? $result);
            } else {
                return $this->fail($result['error'] ?? 'Query execution failed');
            }
        }

        return $result;
    }
}
