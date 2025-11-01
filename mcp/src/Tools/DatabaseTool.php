<?php
/**
 * Database Tool - Advanced Query Builder & Analyzer
 *
 * Safe database operations with query building, analysis, and optimization
 */

namespace MCP\Tools;

class DatabaseTool
{
    protected string $name = 'database';
    protected string $description = 'Advanced database query builder, analyzer, and optimizer';

    protected array $inputSchema = [
        'type' => 'object',
        'properties' => [
            'action' => [
                'type' => 'string',
                'enum' => ['query', 'analyze', 'optimize', 'schema', 'relations', 'indexes'],
                'description' => 'Database operation to perform'
            ],
            'query' => [
                'type' => 'string',
                'description' => 'SQL query (SELECT only for safety)'
            ],
            'table' => [
                'type' => 'string',
                'description' => 'Table name for schema/analysis operations'
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Result limit',
                'default' => 100
            ]
        ],
        'required' => ['action']
    ];

    public function execute(array $arguments): array
    {
        $action = $arguments['action'];

        try {
            $db = $this->getDatabase();

            switch ($action) {
                case 'query':
                    return $this->executeQuery($db, $arguments);

                case 'analyze':
                    return $this->analyzeTable($db, $arguments['table'] ?? null);

                case 'optimize':
                    return $this->optimizeQuery($db, $arguments['query'] ?? null);

                case 'schema':
                    return $this->getSchema($db, $arguments['table'] ?? null);

                case 'relations':
                    return $this->getRelations($db, $arguments['table'] ?? null);

                case 'indexes':
                    return $this->getIndexes($db, $arguments['table'] ?? null);

                default:
                    throw new \Exception("Unknown action: {$action}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getDatabase(): \PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $dsn = "mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4";
            $pdo = new \PDO($dsn, 'hdgwrzntwa', 'bFUdRjh4Jx', [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        }

        return $pdo;
    }    private function executeQuery($db, array $args): array
    {
        $query = $args['query'] ?? '';
        $limit = $args['limit'] ?? 100;

        // Security: Only allow SELECT queries
        if (!preg_match('/^\s*SELECT/i', $query)) {
            throw new \Exception('Only SELECT queries are allowed');
        }

        // Add LIMIT if not present
        if (!preg_match('/LIMIT\s+\d+/i', $query)) {
            $query .= " LIMIT {$limit}";
        }

        $startTime = microtime(true);
        $result = $db->query($query);
        $executionTime = microtime(true) - $startTime;

        $rows = $result->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data' => $rows,
            'count' => count($rows),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'query' => $query
        ];
    }

    private function analyzeTable($db, ?string $table): array
    {
        if (!$table) {
            // Analyze all tables
            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            $analysis = [];
            foreach ($tables as $tableName) {
                $analysis[$tableName] = $this->getTableStats($db, $tableName);
            }

            return [
                'success' => true,
                'data' => $analysis,
                'total_tables' => count($tables)
            ];
        }

        // Analyze specific table
        return [
            'success' => true,
            'data' => $this->getTableStats($db, $table)
        ];
    }

    private function getTableStats($db, string $table): array
    {
        $stats = [];

        // Row count
        $count = $db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        $stats['row_count'] = $count;

        // Table size
        $size = $db->query("
            SELECT
                ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb,
                ROUND(data_length / 1024 / 1024, 2) as data_mb,
                ROUND(index_length / 1024 / 1024, 2) as index_mb
            FROM information_schema.TABLES
            WHERE table_schema = DATABASE()
            AND table_name = '{$table}'
        ")->fetch(\PDO::FETCH_ASSOC);
        $stats['size'] = $size;

        // Column count
        $columns = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll();
        $stats['column_count'] = count($columns);

        // Index count
        $indexes = $db->query("SHOW INDEXES FROM `{$table}`")->fetchAll();
        $stats['index_count'] = count(array_unique(array_column($indexes, 'Key_name')));

        return $stats;
    }

    private function optimizeQuery($db, ?string $query): array
    {
        if (!$query) {
            throw new \Exception('Query is required for optimization');
        }

        // Get EXPLAIN output
        $explain = $db->query("EXPLAIN {$query}")->fetchAll(\PDO::FETCH_ASSOC);

        // Analyze EXPLAIN output
        $suggestions = [];
        foreach ($explain as $row) {
            if ($row['type'] === 'ALL') {
                $suggestions[] = "Table scan detected on {$row['table']} - consider adding index";
            }
            if ($row['Extra'] && strpos($row['Extra'], 'Using filesort') !== false) {
                $suggestions[] = "Filesort detected - consider adding index for ORDER BY";
            }
            if ($row['Extra'] && strpos($row['Extra'], 'Using temporary') !== false) {
                $suggestions[] = "Temporary table used - query might be complex";
            }
        }

        return [
            'success' => true,
            'data' => [
                'explain' => $explain,
                'suggestions' => $suggestions,
                'query' => $query
            ]
        ];
    }

    private function getSchema($db, ?string $table): array
    {
        if (!$table) {
            // Get all table schemas
            $tables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            $schemas = [];
            foreach ($tables as $tableName) {
                $schemas[$tableName] = $this->getTableSchema($db, $tableName);
            }

            return [
                'success' => true,
                'data' => $schemas
            ];
        }

        // Get specific table schema
        return [
            'success' => true,
            'data' => $this->getTableSchema($db, $table)
        ];
    }

    private function getTableSchema($db, string $table): array
    {
        $columns = $db->query("SHOW FULL COLUMNS FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

        $schema = [];
        foreach ($columns as $col) {
            $schema[] = [
                'name' => $col['Field'],
                'type' => $col['Type'],
                'null' => $col['Null'] === 'YES',
                'key' => $col['Key'],
                'default' => $col['Default'],
                'extra' => $col['Extra'],
                'comment' => $col['Comment']
            ];
        }

        return $schema;
    }

    private function getRelations($db, ?string $table): array
    {
        $query = "
            SELECT
                TABLE_NAME as 'table',
                COLUMN_NAME as 'column',
                REFERENCED_TABLE_NAME as 'referenced_table',
                REFERENCED_COLUMN_NAME as 'referenced_column'
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ";

        if ($table) {
            $query .= " AND TABLE_NAME = '{$table}'";
        }

        $relations = $db->query($query)->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data' => $relations,
            'count' => count($relations)
        ];
    }

    private function getIndexes($db, ?string $table): array
    {
        if (!$table) {
            throw new \Exception('Table name is required for index analysis');
        }

        $indexes = $db->query("SHOW INDEXES FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);

        // Group by index name
        $grouped = [];
        foreach ($indexes as $idx) {
            $name = $idx['Key_name'];
            if (!isset($grouped[$name])) {
                $grouped[$name] = [
                    'name' => $name,
                    'unique' => $idx['Non_unique'] == 0,
                    'type' => $idx['Index_type'],
                    'columns' => []
                ];
            }
            $grouped[$name]['columns'][] = $idx['Column_name'];
        }

        return [
            'success' => true,
            'data' => array_values($grouped),
            'count' => count($grouped)
        ];
    }
}
