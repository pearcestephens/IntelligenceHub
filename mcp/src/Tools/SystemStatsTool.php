<?php
/**
 * System Statistics Tool
 *
 * Provides system-wide statistics about indexed files, search engine, etc.
 *
 * @package IntelligenceHub\MCP\Tools
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use IntelligenceHub\MCP\Database\Connection;
use PDO;

class SystemStatsTool
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    /**
     * Execute system stats retrieval
     */
    public function execute(array $params = []): array
    {
        try {
            $stats = [
                'indexed_files' => $this->getIndexedFilesCount(),
                'database' => $this->getDatabaseStats(),
                'search_engine' => $this->getSearchEngineInfo(),
                'system' => $this->getSystemInfo(),
            ];

            return [
                'success' => true,
                'data' => $stats,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get count of indexed files
     */
    private function getIndexedFilesCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM intelligence_content");
            return (int) $stmt->fetchColumn();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStats(): array
    {
        try {
            // Get table sizes
            $stmt = $this->db->query("
                SELECT
                    table_name,
                    table_rows,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = DATABASE()
                AND table_name IN ('intelligence_content', 'intelligence_content_text', 'mcp_search_analytics')
            ");

            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'tables' => $tables,
                'total_size_mb' => array_sum(array_column($tables, 'size_mb')),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get search engine information
     */
    private function getSearchEngineInfo(): array
    {
        return [
            'version' => '4.0',
            'features' => [
                'semantic_search' => true,
                'fuzzy_matching' => true,
                'typo_correction' => true,
                'analytics' => true,
                'caching' => true,
            ],
            'capabilities' => [
                'natural_language_queries' => true,
                'file_content_search' => true,
                'category_filtering' => true,
                'unit_filtering' => true,
            ],
        ];
    }

    /**
     * Get system information
     */
    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'timezone' => date_default_timezone_get(),
        ];
    }
}
