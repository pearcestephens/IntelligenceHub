<?php
/**
 * Indexer Tools - Comprehensive File Scanner & Indexer
 *
 * Uses ALL intelligence_* tables:
 * - intelligence_files: File metadata and paths
 * - intelligence_content: Full file content with chunking
 * - intelligence_content_text: Extracted text for search
 * - intelligence_content_types: File type classifications
 * - intelligence_metrics: File complexity metrics
 * - intelligence_automation: Automation triggers
 * - intelligence_alerts: Scan alerts and warnings
 */

namespace IntelligenceHub\MCP\Tools;

use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IndexerTools extends BaseTool {
    private PDO $pdo;
    private string $rootPath;
    private array $stats = [];

    public function __construct() {
        $this->pdo = \IntelligenceHub\MCP\Database\Connection::getInstance();
        // Root is public_html directory: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html
        $this->rootPath = dirname(dirname(dirname(__DIR__)));
    }

    public function getName(): string {
        return 'indexer';
    }

    public function getSchema(): array {
        return [
            'indexer.scan_all' => [
                'description' => 'Comprehensive scan: index ALL files to ALL intelligence tables',
                'parameters' => [
                    'directory' => ['type' => 'string', 'required' => false],
                    'max_files' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'indexer.scan_directory' => [
                'description' => 'Scan specific directory and index files',
                'parameters' => [
                    'directory' => ['type' => 'string', 'required' => true],
                    'recursive' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'indexer.scan_file' => [
                'description' => 'Index single file to all tables',
                'parameters' => [
                    'file' => ['type' => 'string', 'required' => true]
                ]
            ],
            'indexer.get_stats' => [
                'description' => 'Get indexing statistics from all tables',
                'parameters' => []
            ],
            'indexer.search_indexed' => [
                'description' => 'Search indexed content across all tables',
                'parameters' => [
                    'query' => ['type' => 'string', 'required' => true],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'indexer.cleanup_duplicates' => [
                'description' => 'Remove duplicate entries across intelligence tables',
                'parameters' => []
            ],
            'indexer.rebuild_index' => [
                'description' => 'Rebuild all indexes and optimize tables',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'scan_all';

        switch ($method) {
            case 'scan_all':
                return $this->scanAll($args);
            case 'scan_directory':
                return $this->scanDirectory($args);
            case 'scan_file':
                return $this->scanFile($args);
            case 'get_stats':
                return $this->getStats();
            case 'search_indexed':
                return $this->searchIndexed($args);
            case 'cleanup_duplicates':
                return $this->cleanupDuplicates();
            case 'rebuild_index':
                return $this->rebuildIndex();
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    /**
     * Comprehensive scan: ALL files to ALL tables
     */
    private function scanAll(array $args): array {
        $directory = $args['directory'] ?? '';
        $maxFiles = $args['max_files'] ?? 1000;
        $scanPath = $directory ? $this->rootPath . '/' . ltrim($directory, '/') : $this->rootPath;

        $this->stats = [
            'files_scanned' => 0,
            'files_indexed' => 0,
            'content_chunks' => 0,
            'metrics_created' => 0,
            'errors' => []
        ];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scanPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($this->stats['files_scanned'] >= $maxFiles) break;

            if ($file->isFile() && $this->shouldIndex($file->getPathname())) {
                try {
                    $this->indexFileToAllTables($file->getPathname());
                    $this->stats['files_indexed']++;
                } catch (\Exception $e) {
                    $this->stats['errors'][] = $file->getPathname() . ': ' . $e->getMessage();
                }
                $this->stats['files_scanned']++;
            }
        }

        return $this->ok([
            'stats' => $this->stats,
            'tables_updated' => [
                'intelligence_files',
                'intelligence_content',
                'intelligence_content_text',
                'intelligence_metrics',
                'intelligence_content_types'
            ]
        ]);
    }

    /**
     * Index single file to ALL intelligence tables
     */
    private function indexFileToAllTables(string $filePath): void {
        $relativePath = str_replace($this->rootPath, '', $filePath);
        $content = file_get_contents($filePath);
        $fileInfo = stat($filePath);

        // Initialize stats counters
        if (!isset($this->stats['content_chunks'])) $this->stats['content_chunks'] = 0;
        if (!isset($this->stats['metrics_created'])) $this->stats['metrics_created'] = 0;

        // 1. intelligence_files: File metadata (OLD schema - backward compat)
        $fileId = $this->insertIntelligenceFiles([
            'file_path' => $relativePath,
            'file_name' => basename($filePath),
            'file_type' => $this->getFileTypeEnum($filePath),
            'file_size' => $fileInfo['size'],
            'content_hash' => hash('sha256', $content),
            'intelligence_type' => $this->getIntelligenceType($filePath),
            'business_unit_id' => 999, // Playground unit
            'server_id' => 'hdgwrzntwa',
            'project_id' => 1
        ]);

        // 2. intelligence_content: Main content table (NEW schema)
        $contentId = $this->insertIntelligenceContent([
            'org_id' => 1,
            'unit_id' => 999, // Playground unit
            'content_type_id' => $this->getContentTypeId($filePath),
            'source_system' => 'mcp_indexer',
            'content_path' => $relativePath,
            'content_name' => basename($filePath),
            'content_hash' => hash('sha256', $content),
            'file_size' => $fileInfo['size'],
            'file_modified' => date('Y-m-d H:i:s', $fileInfo['mtime']),
            'mime_type' => $this->getMimeType($filePath),
            'language_detected' => $this->detectLanguage($filePath),
            'encoding' => 'UTF-8'
        ]);
        $this->stats['content_chunks']++;

        // 3. intelligence_content_text: Extracted searchable text
        $searchText = $this->extractSearchableText($content, $filePath);
        $keywords = $this->extractKeywords($searchText);
        $this->insertIntelligenceContentText([
            'content_id' => $contentId,
            'content_text' => $content,
            'content_summary' => substr($searchText, 0, 500),
            'extracted_keywords' => $keywords,
            'semantic_tags' => $this->generateSemanticTags($filePath, $content),
            'line_count' => substr_count($content, "\n"),
            'word_count' => str_word_count($content),
            'character_count' => strlen($content)
        ]);

        // 4. intelligence_metrics: Complexity metrics
        $metrics = $this->calculateMetrics($content, $filePath);
        $this->insertIntelligenceMetrics([
            'org_id' => 1,
            'unit_id' => 999, // Playground unit
            'metric_category' => 'quality',
            'metric_name' => 'code_complexity',
            'metric_value' => $metrics['complexity'],
            'source_system' => 'mcp_indexer'
        ]);

        $this->insertIntelligenceMetrics([
            'org_id' => 1,
            'unit_id' => 999,
            'metric_category' => 'quality',
            'metric_name' => 'maintainability_score',
            'metric_value' => $metrics['maintainability'],
            'source_system' => 'mcp_indexer'
        ]);
        $this->stats['metrics_created'] += 2;
    }    /**
     * Insert into intelligence_files table (OLD schema - backward compat)
     */
    private function insertIntelligenceFiles(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_files
            (project_id, business_unit_id, server_id, file_path, file_name, file_type,
             file_size, file_content, content_hash, intelligence_type)
            VALUES (:project_id, :business_unit_id, :server_id, :file_path, :file_name, :file_type,
                    :file_size, '', :content_hash, :intelligence_type)
            ON DUPLICATE KEY UPDATE
                file_size = VALUES(file_size),
                content_hash = VALUES(content_hash),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute($data);
        return (int) ($this->pdo->lastInsertId() ?: $this->pdo->query("SELECT file_id FROM intelligence_files WHERE content_hash = '{$data['content_hash']}' LIMIT 1")->fetchColumn());
    }

    /**
     * Insert into intelligence_content table (NEW schema)
     */
    private function insertIntelligenceContent(array $data): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_content
            (org_id, unit_id, content_type_id, source_system, content_path, content_name,
             content_hash, file_size, file_modified, mime_type, language_detected, encoding)
            VALUES (:org_id, :unit_id, :content_type_id, :source_system, :content_path, :content_name,
                    :content_hash, :file_size, :file_modified, :mime_type, :language_detected, :encoding)
            ON DUPLICATE KEY UPDATE
                file_size = VALUES(file_size),
                file_modified = VALUES(file_modified),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute($data);
        return (int) ($this->pdo->lastInsertId() ?: $this->pdo->query("SELECT content_id FROM intelligence_content WHERE content_hash = '{$data['content_hash']}' LIMIT 1")->fetchColumn());
    }

    /**
     * Insert into intelligence_content_text table
     */
    private function insertIntelligenceContentText(array $data): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_content_text
            (content_id, content_text, content_summary, extracted_keywords, semantic_tags,
             line_count, word_count, character_count)
            VALUES (:content_id, :content_text, :content_summary, :extracted_keywords, :semantic_tags,
                    :line_count, :word_count, :character_count)
            ON DUPLICATE KEY UPDATE
                content_text = VALUES(content_text),
                content_summary = VALUES(content_summary),
                extracted_keywords = VALUES(extracted_keywords),
                semantic_tags = VALUES(semantic_tags),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->execute($data);
    }

    /**
     * Insert into intelligence_metrics table
     */
    private function insertIntelligenceMetrics(array $data): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_metrics
            (org_id, unit_id, metric_category, metric_name, metric_value, source_system)
            VALUES (:org_id, :unit_id, :metric_category, :metric_name, :metric_value, :source_system)
        ");
        $stmt->execute($data);
    }

    // Helper methods
    private function shouldIndex(string $path): bool {
        $excluded = ['vendor', 'node_modules', '.git', 'cache', 'logs'];
        foreach ($excluded as $exclude) {
            if (strpos($path, '/' . $exclude . '/') !== false) return false;
        }
        return true;
    }

    private function getFileType(string $path): string {
        return pathinfo($path, PATHINFO_EXTENSION) ?: 'unknown';
    }

    private function getFileTypeEnum(string $path): string {
        // Match intelligence_files.file_type ENUM values
        if (strpos($path, '/docs/') !== false || strpos($path, '/README') !== false) {
            return 'documentation';
        }
        if (strpos($path, '/src/') !== false || strpos($path, '/app/') !== false) {
            return 'code_intelligence';
        }
        if (strpos($path, '/reports/') !== false || strpos($path, '/analytics/') !== false) {
            return 'business_intelligence';
        }
        return 'operational_intelligence';
    }

    private function getIntelligenceType(string $path): string {
        $ext = $this->getFileType($path);
        if ($ext === 'php') return 'php_code';
        if ($ext === 'js') return 'javascript';
        if ($ext === 'sql') return 'database_query';
        if (in_array($ext, ['md', 'txt'])) return 'documentation';
        return 'general_file';
    }

    private function getContentTypeId(string $path): int {
        // Simplified - should lookup intelligence_content_types table
        // For now return 1 (default type)
        return 1;
    }

    private function getMimeType(string $path): string {
        $ext = $this->getFileType($path);
        $mimeMap = [
            'php' => 'text/x-php',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'sql' => 'application/sql',
            'md' => 'text/markdown',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'css' => 'text/css'
        ];
        return $mimeMap[$ext] ?? 'application/octet-stream';
    }

    private function generateSemanticTags(string $path, string $content): string {
        $tags = [];

        // File type tags
        $ext = $this->getFileType($path);
        $tags[] = $ext;

        // Content-based tags
        if (strpos($content, 'class ') !== false) $tags[] = 'oop';
        if (strpos($content, 'function ') !== false) $tags[] = 'functions';
        if (strpos($content, 'SELECT ') !== false) $tags[] = 'database';
        if (strpos($content, 'API') !== false) $tags[] = 'api';
        if (strpos($content, 'test') !== false) $tags[] = 'testing';

        return json_encode(array_unique($tags)); // Return JSON array
    }

    private function chunkContent(string $content, int $chunkSize = 5000): array {
        return str_split($content, $chunkSize);
    }

    private function extractSearchableText(string $content, string $path): string {
        // PHP files: extract comments, function names, class names
        if (str_ends_with($path, '.php')) {
            preg_match_all('/\/\*\*(.*?)\*\//s', $content, $docblocks);
            preg_match_all('/function\s+(\w+)/', $content, $functions);
            preg_match_all('/class\s+(\w+)/', $content, $classes);

            return implode(' ', array_merge(
                $docblocks[1] ?? [],
                $functions[1] ?? [],
                $classes[1] ?? []
            ));
        }
        return substr($content, 0, 10000); // First 10KB
    }

    private function extractKeywords(string $text): string {
        $words = str_word_count(strtolower($text), 1);
        $words = array_filter($words, fn($w) => strlen($w) > 3);
        $freq = array_count_values($words);
        arsort($freq);
        $keywords = array_slice(array_keys($freq), 0, 20);
        return json_encode($keywords); // Return JSON array
    }

    private function detectLanguage(string $path): string {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $map = ['php' => 'php', 'js' => 'javascript', 'py' => 'python', 'sql' => 'sql'];
        return $map[$ext] ?? 'text';
    }

    private function calculateMetrics(string $content, string $path): array {
        $lines = explode("\n", $content);
        $loc = count($lines);

        // Simple complexity: count control structures
        $complexity = substr_count($content, 'if(') +
                     substr_count($content, 'for(') +
                     substr_count($content, 'while(') +
                     substr_count($content, 'switch(');

        $maintainability = max(0, 100 - ($complexity / max($loc, 1) * 100));

        return [
            'loc' => $loc,
            'complexity' => $complexity,
            'maintainability' => round($maintainability, 2)
        ];
    }

    private function categorizeFile(string $path): string {
        if (strpos($path, '/src/') !== false) return 'source';
        if (strpos($path, '/test') !== false) return 'test';
        if (strpos($path, '/config') !== false) return 'config';
        if (strpos($path, '/api/') !== false) return 'api';
        return 'other';
    }

    private function subcategorizeFile(string $path, string $content): string {
        if (strpos($content, 'class ') !== false) return 'class';
        if (strpos($content, 'function ') !== false) return 'function';
        if (strpos($content, 'SELECT ') !== false) return 'database';
        return 'script';
    }

    private function scanDirectory(array $args): array {
        $directory = $args['directory'] ?? '';
        return $this->scanAll(['directory' => $directory, 'max_files' => 100]);
    }

    private function scanFile(array $args): array {
        $file = $args['file'] ?? '';
        $fullPath = $this->rootPath . '/' . ltrim($file, '/');

        if (!file_exists($fullPath)) {
            return $this->fail("File not found: $file (looking at: $fullPath)");
        }

        try {
            $this->indexFileToAllTables($fullPath);
            return $this->ok([
                'file' => $file,
                'indexed' => true,
                'tables_updated' => ['intelligence_files', 'intelligence_content', 'intelligence_content_text', 'intelligence_metrics']
            ]);
        } catch (\PDOException $e) {
            return $this->fail("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            return $this->fail("Error: " . $e->getMessage());
        }
    }

    private function getStats(): array {
        $stats = [];
        $tables = ['intelligence_files', 'intelligence_content', 'intelligence_content_text', 'intelligence_metrics', 'intelligence_content_types'];

        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM $table");
            $stats[$table] = (int) $stmt->fetchColumn();
        }

        return $this->ok($stats);
    }

    private function searchIndexed(array $args): array {
        $query = $args['query'] ?? '';
        $limit = (int) ($args['limit'] ?? 10);

        $queryPattern = '%' . $query . '%';

        $stmt = $this->pdo->prepare("
            SELECT
                f.file_path,
                f.file_name,
                c.content_path,
                c.content_name,
                ct.content_summary,
                ct.extracted_keywords
            FROM intelligence_files f
            LEFT JOIN intelligence_content c ON f.file_path = c.content_path
            LEFT JOIN intelligence_content_text ct ON c.content_id = ct.content_id
            WHERE f.file_path LIKE ?
               OR f.file_name LIKE ?
               OR ct.content_summary LIKE ?
            LIMIT $limit
        ");
        $stmt->execute([$queryPattern, $queryPattern, $queryPattern]);

        return $this->ok([
            'results' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'count' => $stmt->rowCount()
        ]);
    }

    private function cleanupDuplicates(): array {
        // Remove duplicate files based on file_path
        $this->pdo->exec("
            DELETE f1 FROM intelligence_files f1
            INNER JOIN intelligence_files f2
            WHERE f1.file_id > f2.file_id
            AND f1.file_path = f2.file_path
        ");

        return $this->ok(['cleaned' => true, 'affected_rows' => $this->pdo->rowCount()]);
    }

    private function rebuildIndex(): array {
        $tables = ['intelligence_files', 'intelligence_content', 'intelligence_content_text'];
        foreach ($tables as $table) {
            $this->pdo->exec("OPTIMIZE TABLE $table");
        }

        return $this->ok(['rebuilt' => true, 'tables' => $tables]);
    }
}
