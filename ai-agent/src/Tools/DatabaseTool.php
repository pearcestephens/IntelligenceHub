<?php

/**
 * Database Tool for safe SQL query execution
 * Provides read-only database access with automatic parameter binding
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\DB;
use App\Logger;
use App\Util\Validate;
use App\Tools\Contracts\ToolContract;

class DatabaseTool implements ToolContract
{
    private const MAX_QUERY_TIME = 30;
    private const MAX_RESULT_ROWS = 1000;
    private const ALLOWED_OPERATIONS = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'];
    private const ALLOWED_ACTIONS = ['query', 'explain', 'schema', 'stats', 'tables', 'table_info', 'status'];

    /**
     * Execute a safe database query
     */
    public static function execute(array $parameters, array $context = []): array
    {
        $query = $parameters['query'] ?? '';
        $params = $parameters['params'] ?? [];

        Validate::string($query, 1);

        try {
            // Validate query safety
            self::validateQuery($query);

            // Log query execution
            Logger::info('Database tool executing query', [
                'query_hash' => hash('sha256', $query),
                'param_count' => count($params),
                'context_keys' => array_keys($context),
            ]);

            $startTime = microtime(true);

            // Execute query with timeout
            $oldTimeLimit = (int) ini_get('max_execution_time');
            set_time_limit(self::MAX_QUERY_TIME);

            try {
                $results = DB::select($query, $params);
                $duration = (microtime(true) - $startTime) * 1000;

                // Limit result size
                if (count($results) > self::MAX_RESULT_ROWS) {
                    $truncated = count($results) - self::MAX_RESULT_ROWS;
                    $results = array_slice($results, 0, self::MAX_RESULT_ROWS);

                    Logger::warning('Query results truncated', [
                        'total_rows' => count($results) + $truncated,
                        'truncated_rows' => $truncated,
                        'returned_rows' => count($results),
                    ]);
                }

                Logger::info('Database query completed', [
                    'rows_returned' => count($results),
                    'duration_ms' => (int) $duration,
                ]);

                return [
                    'rows' => $results,
                    'row_count' => count($results),
                    'duration_ms' => (int) $duration,
                    'query_hash' => hash('sha256', $query),
                ];
            } finally {
                set_time_limit($oldTimeLimit);
            }
        } catch (\Throwable $e) {
            Logger::error('Database tool query failed', [
                'query_hash' => hash('sha256', $query),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ]);

            // Return safe error message
            return [
                'error' => 'Query execution failed: ' . self::sanitizeErrorMessage($e->getMessage()),
                'error_type' => 'DatabaseError',
                'query_hash' => hash('sha256', $query),
            ];
        }
    }

    /**
     * Contract run() routes to safe operations
     */
    public static function run(array $params, array $context = []): array
    {
        $action = $params['action'] ?? (isset($params['query']) ? 'query' : 'status');

        if (!in_array($action, self::ALLOWED_ACTIONS, true)) {
            return [
                'error' => 'Invalid action. Allowed: ' . implode(', ', self::ALLOWED_ACTIONS),
                'error_type' => 'ValidationError'
            ];
        }

        switch ($action) {
            case 'query':
                return self::execute($params, $context);
            case 'explain':
                return self::explainQuery($params, $context);
            case 'schema':
                return self::getSchema($params, $context);
            case 'tables':
                return self::listTables();
            case 'table_info':
                if (empty($params['table'])) {
                    return [
                        'error' => 'Parameter "table" is required for action table_info',
                        'error_type' => 'ValidationError'
                    ];
                }
                $res = self::getSchema(['table' => $params['table']], $context);
                // Flatten to a single table entry if available
                if (isset($res['schema'][$params['table']])) {
                    return [
                        'table' => $params['table'],
                        'details' => $res['schema'][$params['table']]
                    ];
                }
                return $res;
            case 'stats':
                return self::getStats($params, $context);
            case 'status':
                try {
                    $healthy = DB::isHealthy();
                    $info = DB::getConnectionInfo();
                    return [
                        'healthy' => $healthy,
                        'connection' => $info
                    ];
                } catch (\Throwable $e) {
                    return [
                        'healthy' => false,
                        'error' => self::sanitizeErrorMessage($e->getMessage())
                    ];
                }
        }

        return [
            'error' => 'Unhandled action',
            'error_type' => 'InternalError'
        ];
    }

    public static function spec(): array
    {
        return [
            'name' => 'database_tool',
            'description' => 'Safe database operations: read-only queries, schema, stats, status, and explain',
            'category' => 'data',
            'internal' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => [
                        'type' => 'string',
                        'enum' => self::ALLOWED_ACTIONS,
                        'description' => 'Operation to perform (default: query if query provided, else status)'
                    ],
                    'query' => ['type' => 'string', 'description' => 'SQL for query/explain (read-only)'],
                    'params' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Bound parameters for query/explain'
                    ],
                    'table' => ['type' => 'string', 'description' => 'Table name for table_info/schema']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 30,
                'rate_limit' => 10
            ]
        ];
    }

    /**
     * Get database schema information
     */
    public static function getSchema(array $parameters, array $context = []): array
    {
        $tableName = $parameters['table'] ?? null;

        try {
            if ($tableName) {
                Validate::string($tableName, 1, 64);

                // Validate table name (alphanumeric + underscore only)
                if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $tableName)) {
                    throw new \InvalidArgumentException('Invalid table name format');
                }

                // Get specific table info
                $tables = [$tableName];
            } else {
                // Get all tables
                $tablesResult = DB::select('SHOW TABLES');
                if (!empty($tablesResult)) {
                    $firstKey = array_key_first($tablesResult[0]);
                    $tables = array_column($tablesResult, $firstKey);
                } else {
                    $tables = [];
                }
            }

            $schema = [];

            foreach ($tables as $table) {
                try {
                    // Get table structure
                    $columns = DB::select("DESCRIBE `{$table}`");

                    // Get table info
                    $tableInfo = DB::selectOne(
                        "SELECT TABLE_COMMENT, ENGINE, TABLE_ROWS, DATA_LENGTH 
                             FROM INFORMATION_SCHEMA.TABLES 
                             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
                        [$table]
                    );

                    // Get indexes
                    $indexes = DB::select("SHOW INDEX FROM `{$table}`");

                    $schema[$table] = [
                        'columns' => $columns,
                        'info' => $tableInfo,
                        'indexes' => $indexes,
                    ];
                } catch (\Throwable $e) {
                    Logger::warning('Failed to get schema for table', [
                        'table' => $table,
                        'error' => $e->getMessage(),
                    ]);

                    $schema[$table] = [
                        'error' => 'Failed to retrieve schema: ' . $e->getMessage(),
                    ];
                }
            }

            return [
                'schema' => $schema,
                'table_count' => count($schema),
            ];
        } catch (\Throwable $e) {
            Logger::error('Database schema query failed', [
                'table' => $tableName,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Schema query failed: ' . self::sanitizeErrorMessage($e->getMessage()),
                'error_type' => 'SchemaError',
            ];
        }
    }

    /**
     * Explain query execution plan
     */
    public static function explainQuery(array $parameters, array $context = []): array
    {
        $query = $parameters['query'] ?? '';
        $params = $parameters['params'] ?? [];

        Validate::string($query, 1);

        try {
            // Validate query safety
            self::validateQuery($query);

            // Add EXPLAIN prefix
            $explainQuery = 'EXPLAIN FORMAT=JSON ' . $query;

            Logger::info('Database tool explaining query', [
                'query_hash' => hash('sha256', $query),
                'param_count' => count($params),
            ]);

            $result = DB::selectOne($explainQuery, $params);
            $explainData = [];
            if (is_array($result)) {
                // MySQL returns column named 'EXPLAIN' with JSON
                $json = $result['EXPLAIN'] ?? null;
                if (is_string($json)) {
                    $explainData = json_decode($json, true) ?: [];
                }
            }

            return [
                'explain' => $explainData,
                'query_hash' => hash('sha256', $query),
                'formatted_explain' => self::formatExplainOutput($explainData),
            ];
        } catch (\Throwable $e) {
            Logger::error('Database explain query failed', [
                'query_hash' => hash('sha256', $query),
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Explain query failed: ' . self::sanitizeErrorMessage($e->getMessage()),
                'error_type' => 'ExplainError',
                'query_hash' => hash('sha256', $query),
            ];
        }
    }

    /**
     * Get database statistics
     */
    public static function getStats(array $parameters, array $context = []): array
    {
        try {
            // Database size and table info
            $dbStats = DB::selectOne(
                'SELECT 
                    COUNT(*) as table_count,
                    SUM(DATA_LENGTH + INDEX_LENGTH) as total_size_bytes,
                    SUM(TABLE_ROWS) as total_rows
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()'
            );

            // Largest tables
            $largestTables = DB::select(
                'SELECT 
                    TABLE_NAME as table_name,
                    TABLE_ROWS as row_count,
                    DATA_LENGTH + INDEX_LENGTH as size_bytes,
                    ENGINE as engine
                FROM INFORMATION_SCHEMA.TABLES 
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY DATA_LENGTH + INDEX_LENGTH DESC 
                LIMIT 10'
            );

            // Connection info
            $connectionStats = DB::select('SHOW STATUS LIKE "Threads_%"');
            $connections = [];
            foreach ($connectionStats as $stat) {
                $connections[$stat['Variable_name']] = $stat['Value'];
            }

            return [
                'database' => [
                    'name' => DB::selectOne('SELECT DATABASE() as name')['name'],
                    'table_count' => (int)($dbStats['table_count'] ?? 0),
                    'total_size_mb' => round(($dbStats['total_size_bytes'] ?? 0) / 1024 / 1024, 2),
                    'total_rows' => (int)($dbStats['total_rows'] ?? 0),
                ],
                'largest_tables' => array_map(
                    function ($table) {
                        return [
                            'name' => $table['table_name'],
                            'rows' => (int) $table['row_count'],
                            'size_mb' => round(($table['size_bytes'] ?? 0) / 1024 / 1024, 2),
                            'engine' => $table['engine'],
                        ];
                    },
                    $largestTables
                ),
                'connections' => $connections,
            ];
        } catch (\Throwable $e) {
            Logger::error('Database stats query failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Stats query failed: ' . self::sanitizeErrorMessage($e->getMessage()),
                'error_type' => 'StatsError',
            ];
        }
    }

    /**
     * List tables in current database (simple helper)
     */
    private static function listTables(): array
    {
        try {
            $tablesResult = DB::select('SHOW TABLES');
            $tables = [];
            if (!empty($tablesResult)) {
                $firstKey = array_key_first($tablesResult[0]);
                $tables = array_column($tablesResult, $firstKey);
            }
            return [
                'tables' => array_values($tables),
                'count' => count($tables)
            ];
        } catch (\Throwable $e) {
            Logger::error('List tables failed', ['error' => $e->getMessage()]);
            return [
                'error' => 'List tables failed: ' . self::sanitizeErrorMessage($e->getMessage()),
                'error_type' => 'DatabaseError'
            ];
        }
    }

    /**
     * Validate query for safety (read-only operations only)
     */
    private static function validateQuery(string $query): void
    {
        $query = trim($query);

        if ($query === '') {
            throw new \InvalidArgumentException('Query cannot be empty');
        }

        // Extract the first SQL keyword
        $firstWord = strtoupper(strtok($query, " \n\t"));

        if (!in_array($firstWord, self::ALLOWED_OPERATIONS, true)) {
            throw new \InvalidArgumentException(
                'Operation not allowed: ' . $firstWord . '. Only ' . implode(', ', self::ALLOWED_OPERATIONS) . ' operations are permitted.'
            );
        }

        // Additional safety checks
        $upperQuery = strtoupper($query);

        // Block dangerous keywords in any position
        $dangerousKeywords = [
            'DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE',
            'TRUNCATE', 'REPLACE', 'GRANT', 'REVOKE', 'LOAD', 'OUTFILE',
            'DUMPFILE', 'INTO OUTFILE', 'LOAD_FILE', 'BENCHMARK',
        ];

        foreach ($dangerousKeywords as $keyword) {
            if (str_contains($upperQuery, $keyword)) {
                throw new \InvalidArgumentException('Query contains forbidden keyword: ' . $keyword);
            }
        }

        // Check for SQL injection patterns
        $injectionPatterns = [
            '/;\s*(DROP|DELETE|UPDATE|INSERT|ALTER|CREATE)/i',
            '/UNION\s+SELECT/i',
            '/--\s*\w/i', // SQL comments with content
            '/\/\*.*?\*\//s', // Multi-line comments
        ];

        foreach ($injectionPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                throw new \InvalidArgumentException('Query contains potentially dangerous patterns');
            }
        }

        // Limit query length
        if (strlen($query) > 5000) {
            throw new \InvalidArgumentException('Query too long (max 5000 characters)');
        }
    }

    /**
     * Sanitize error messages to avoid information disclosure
     */
    private static function sanitizeErrorMessage(string $message): string
    {
        // Remove file paths
        $message = preg_replace('/\/[^\s]+\.php/', '[FILE_PATH]', $message);

        // Remove specific SQL table/column names in error messages
        $message = preg_replace("/Table '[^']+'/", 'Table [TABLE]', $message);
        $message = preg_replace("/Column '[^']+'/", 'Column [COLUMN]', $message);

        // Remove connection details
        $message = preg_replace("/Access denied for user '[^']+'/", 'Access denied for user [USER]', $message);

        return $message ?? '';
    }

    /**
     * Format EXPLAIN output for readability
     */
    private static function formatExplainOutput(array $explainData): array
    {
        $formatted = [];

        if (isset($explainData['query_block'])) {
            $queryBlock = $explainData['query_block'];

            $formatted['query_cost'] = $queryBlock['cost_info']['query_cost'] ?? 'unknown';
            $formatted['select_type'] = $queryBlock['select_id'] ?? 'unknown';

            if (isset($queryBlock['table'])) {
                $table = $queryBlock['table'];
                $formatted['table_access'] = [
                    'table_name' => $table['table_name'] ?? 'unknown',
                    'access_type' => $table['access_type'] ?? 'unknown',
                    'possible_keys' => $table['possible_keys'] ?? [],
                    'key' => $table['key'] ?? null,
                    'key_length' => $table['key_length'] ?? null,
                    'rows_examined' => $table['rows_examined_per_scan'] ?? 'unknown',
                    'filtered' => $table['filtered'] ?? 'unknown',
                ];
            }

            if (isset($queryBlock['nested_loop'])) {
                $formatted['nested_loop_info'] = 'Query uses nested loop join';
            }
        }

        return $formatted;
    }
}
