<?php
/**
 * MySQL Query Tool
 *
 * Executes safe read-only SQL queries against the database
 *
 * @package IntelligenceHub\MCP\Tools
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use IntelligenceHub\MCP\Database\Connection;
use PDO;
use Exception;

class MySQLQueryTool
{
    private PDO $db;
    private array $allowedStatements = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN'];

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Execute a safe read-only query
     *
     * @param array $params Query parameters
     * @return array Result with success status and data
     */
    public function execute(array $params = []): array
    {
        // Handle special actions
        $action = $params['action'] ?? null;
        if ($action === 'common_queries') {
            return [
                'success' => true,
                'data' => $this->getCommonQueries(),
            ];
        }

        $query = $params['query'] ?? '';
        $limit = $params['limit'] ?? 100;
        $format = $params['format'] ?? 'array'; // array, json, csv

        if (empty($query)) {
            return [
                'success' => false,
                'error' => 'Query parameter is required',
            ];
        }

        // Security: Only allow read-only statements
        $firstWord = strtoupper(trim(explode(' ', trim($query))[0]));
        if (!in_array($firstWord, $this->allowedStatements)) {
            return [
                'success' => false,
                'error' => "Only read-only queries allowed: " . implode(', ', $this->allowedStatements),
            ];
        }

        // Security: Block dangerous patterns
        $dangerousPatterns = [
            '/INTO\s+OUTFILE/i',
            '/LOAD_FILE/i',
            '/LOAD\s+DATA/i',
            '/SYSTEM/i',
            '/EXEC/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return [
                    'success' => false,
                    'error' => 'Query contains dangerous operations',
                ];
            }
        }

        try {
            $startTime = microtime(true);

            // Handle SHOW TABLES specially (doesn't support LIMIT in MariaDB)
            $actualQuery = $query;
            $limitInPhp = null;
            if (preg_match('/^SHOW\s+TABLES\s+LIMIT\s+(\d+)/i', $query, $matches)) {
                $limitInPhp = (int)$matches[1];
                $actualQuery = 'SHOW TABLES';
            }

            // Auto-append LIMIT if not present for SELECT queries
            if ($firstWord === 'SELECT' && !preg_match('/LIMIT\s+\d+/i', $actualQuery)) {
                $actualQuery .= " LIMIT {$limit}";
            }

            $stmt = $this->db->query($actualQuery);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Apply PHP limit if needed for SHOW TABLES
            if ($limitInPhp !== null) {
                $results = array_slice($results, 0, $limitInPhp);
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Determine the appropriate data key based on query type
            $dataKey = 'results';
            if (preg_match('/^SHOW\s+TABLES/i', $query)) {
                $dataKey = 'tables';
            } elseif (preg_match('/^DESCRIBE/i', $query)) {
                $dataKey = 'columns';
            } elseif ($firstWord === 'SELECT') {
                $dataKey = 'rows';
            }

            // Format results
            $formattedResults = $this->formatResults($results, $format);

            $data = [
                'query' => $query,
                'row_count' => count($results),
                'duration_ms' => $duration,
                'format' => $format,
            ];

            // Add results with appropriate key
            if ($format === 'csv') {
                $data['csv'] = $formattedResults;
            } else {
                $data[$dataKey] = $formattedResults;
            }

            return [
                'success' => true,
                'data' => $data,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Query execution failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format results based on requested format
     */
    private function formatResults(array $results, string $format): mixed
    {
        return match($format) {
            'json' => json_encode($results, JSON_PRETTY_PRINT),
            'csv' => $this->convertToCsv($results),
            default => $results,
        };
    }

    /**
     * Convert results to CSV format
     */
    private function convertToCsv(array $results): string
    {
        if (empty($results)) {
            return '';
        }

        $csv = '';

        // Header row
        $csv .= implode(',', array_keys($results[0])) . "\n";

        // Data rows
        foreach ($results as $row) {
            $csv .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', (string)$value) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    /**
     * Get common queries (helper for users)
     */
    public function getCommonQueries(): array
    {
        return [
            'show_tables' => 'SHOW TABLES',
            'describe_table' => 'DESCRIBE table_name',
            'count_rows' => 'SELECT COUNT(*) as count FROM table_name',
            'recent_content' => 'SELECT * FROM mcp_content ORDER BY indexed_at DESC LIMIT 10',
            'search_stats' => 'SELECT query_text, COUNT(*) as count FROM mcp_search_analytics GROUP BY query_text ORDER BY count DESC LIMIT 20',
            'table_sizes' => 'SELECT table_name, table_rows, data_length, index_length FROM information_schema.tables WHERE table_schema = DATABASE()',
        ];
    }
}
