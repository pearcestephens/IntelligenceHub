<?php
/**
 * PHP Code Indexer - Parses PHP files and indexes code elements
 *
 * Integrates existing php_code_indexer.php into PSR-4 architecture
 * Extracts functions, classes, methods, SQL queries, complexity metrics
 *
 * @package IntelligenceHub\MCP\Indexing
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Indexing;

use IntelligenceHub\MCP\Database\Connection;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PHPIndexer
{
    private PDO $pdo;
    private int $unitId;
    private array $stats = [
        'files_processed' => 0,
        'files_skipped' => 0,
        'functions_found' => 0,
        'classes_found' => 0,
        'methods_found' => 0,
        'lines_indexed' => 0,
        'errors' => [],
    ];

    public function __construct(int $unitId = 1)
    {
        $this->pdo = Connection::getInstance();
        $this->unitId = $unitId;
    }

    /**
     * Index a directory recursively
     *
     * @param string $directory Directory to index
     * @param array $options Options (exclude patterns, max depth)
     * @return array Statistics
     */
    public function indexDirectory(string $directory, array $options = []): array
    {
        $excludePatterns = $options['exclude'] ?? ['vendor', 'node_modules', '.git', 'tests'];
        $maxDepth = $options['max_depth'] ?? null;

        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Directory does not exist: {$directory}");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        if ($maxDepth !== null) {
            $iterator->setMaxDepth($maxDepth);
        }

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $filePath = $file->getPathname();

            // Check exclude patterns
            $shouldExclude = false;
            foreach ($excludePatterns as $pattern) {
                if (stripos($filePath, $pattern) !== false) {
                    $shouldExclude = true;
                    break;
                }
            }

            if ($shouldExclude) {
                $this->stats['files_skipped']++;
                continue;
            }

            try {
                $this->indexFile($filePath);
                $this->stats['files_processed']++;
            } catch (\Exception $e) {
                $this->stats['errors'][] = [
                    'file' => $filePath,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->stats;
    }

    /**
     * Index a single PHP file
     *
     * @param string $filePath Full path to PHP file
     * @return bool Success
     */
    public function indexFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File does not exist: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException("Could not read file: {$filePath}");
        }

        // Extract code elements
        $extracted = $this->extractCodeElements($content);

        // Calculate metrics
        $complexity = $this->calculateComplexity($content);
        $quality = $this->calculateQuality($extracted, $content);

        // Generate searchable text
        $searchText = $this->generateSearchText($extracted, $filePath);

        // Generate keywords, tags, entities
        $keywords = $this->generateKeywords($extracted);
        $tags = $this->generateTags($extracted);
        $entities = $this->extractEntities($extracted);

        // Update statistics
        $this->stats['functions_found'] += count($extracted['functions']);
        $this->stats['classes_found'] += count($extracted['classes']);
        $this->stats['methods_found'] += count($extracted['methods']);
        $this->stats['lines_indexed'] += substr_count($content, "\n");

        // Store in database
        return $this->storeInDatabase($filePath, $content, [
            'extracted' => $extracted,
            'complexity' => $complexity,
            'quality' => $quality,
            'search_text' => $searchText,
            'keywords' => $keywords,
            'tags' => $tags,
            'entities' => $entities,
        ]);
    }

    /**
     * Extract code elements (functions, classes, methods, SQL, etc.)
     *
     * @param string $content PHP file content
     * @return array Extracted elements
     */
    private function extractCodeElements(string $content): array
    {
        $elements = [
            'functions' => [],
            'classes' => [],
            'methods' => [],
            'properties' => [],
            'constants' => [],
            'sql_queries' => [],
            'api_endpoints' => [],
            'docblocks' => [],
            'comments' => [],
        ];

        // Extract functions
        preg_match_all(
            '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/i',
            $content,
            $functions
        );
        $elements['functions'] = $functions[1] ?? [];

        // Extract classes
        preg_match_all(
            '/class\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i',
            $content,
            $classes
        );
        $elements['classes'] = $classes[1] ?? [];

        // Extract methods (functions inside classes)
        preg_match_all(
            '/(?:public|private|protected)\s+(?:static\s+)?function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/i',
            $content,
            $methods
        );
        $elements['methods'] = $methods[1] ?? [];

        // Extract SQL queries (capture more complete queries)
        preg_match_all(
            '/(?:SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\s+[^;]+?(?:FROM|INTO|TABLE|SET|WHERE|VALUES)[^;"]*/is',
            $content,
            $sql
        );
        $elements['sql_queries'] = array_slice($sql[0] ?? [], 0, 50); // Limit to 50

        // Extract API endpoint patterns
        preg_match_all(
            '/(?:GET|POST|PUT|DELETE|PATCH)\s+[\'"]([^\'"]*)[\'"]/i',
            $content,
            $endpoints
        );
        $elements['api_endpoints'] = $endpoints[1] ?? [];

        // Extract docblocks
        preg_match_all(
            '/\/\*\*(.*?)\*\//s',
            $content,
            $docblocks
        );
        $elements['docblocks'] = array_slice($docblocks[1] ?? [], 0, 20);

        // Extract comments
        preg_match_all(
            '/\/\/\s*(.*)$/m',
            $content,
            $comments
        );
        $elements['comments'] = array_slice($comments[1] ?? [], 0, 50);

        return $elements;
    }

    /**
     * Calculate cyclomatic complexity
     *
     * @param string $content File content
     * @return int Complexity score
     */
    private function calculateComplexity(string $content): int
    {
        $complexity = 1; // Base complexity

        // Count decision points
        $complexity += preg_match_all('/\bif\b/i', $content);
        $complexity += preg_match_all('/\belse\b/i', $content);
        $complexity += preg_match_all('/\belseif\b/i', $content);
        $complexity += preg_match_all('/\bfor\b/i', $content);
        $complexity += preg_match_all('/\bforeach\b/i', $content);
        $complexity += preg_match_all('/\bwhile\b/i', $content);
        $complexity += preg_match_all('/\bcase\b/i', $content);
        $complexity += preg_match_all('/\bcatch\b/i', $content);
        $complexity += preg_match_all('/\?\?/', $content); // Null coalescing
        $complexity += preg_match_all('/\?[^:]/', $content); // Ternary
        $complexity += preg_match_all('/&&|\|\|/', $content); // Logical operators

        return $complexity;
    }

    /**
     * Calculate code quality score (0-100)
     *
     * @param array $extracted Extracted elements
     * @param string $content File content
     * @return float Quality score
     */
    private function calculateQuality(array $extracted, string $content): float
    {
        $score = 0.0;
        $maxScore = 100.0;

        // Factor 1: Documentation (30%)
        $docblockCount = count($extracted['docblocks']);
        $functionCount = count($extracted['functions']) + count($extracted['methods']);
        if ($functionCount > 0) {
            $docRatio = min($docblockCount / $functionCount, 1.0);
            $score += $docRatio * 30;
        }

        // Factor 2: Type hints (20%)
        $typeHints = preg_match_all('/:\s*\w+/', $content);
        $totalDeclarations = $functionCount + count($extracted['properties']);
        if ($totalDeclarations > 0) {
            $typeRatio = min($typeHints / $totalDeclarations, 1.0);
            $score += $typeRatio * 20;
        }

        // Factor 3: Error handling (20%)
        $tryCatchCount = preg_match_all('/\btry\b/', $content);
        if ($functionCount > 0) {
            $errorHandlingRatio = min($tryCatchCount / ($functionCount * 0.5), 1.0);
            $score += $errorHandlingRatio * 20;
        }

        // Factor 4: Input validation (15%)
        $validationCount = preg_match_all('/\b(filter_|validate|sanitize|trim|strip_|intval|floatval)\b/i', $content);
        if ($functionCount > 0) {
            $validationRatio = min($validationCount / $functionCount, 1.0);
            $score += $validationRatio * 15;
        }

        // Factor 5: Logging/debugging (10%)
        $logCount = preg_match_all('/\b(error_log|log_|logger|debug|trace)\b/i', $content);
        if ($functionCount > 0) {
            $logRatio = min($logCount / ($functionCount * 0.3), 1.0);
            $score += $logRatio * 10;
        }

        // Factor 6: File length penalty (5%)
        $lines = substr_count($content, "\n");
        if ($lines < 500) {
            $score += 5;
        } elseif ($lines < 1000) {
            $score += 2.5;
        }

        return round($score, 2);
    }

    /**
     * Generate searchable text
     *
     * @param array $extracted Extracted elements
     * @param string $filePath File path
     * @return string Searchable text
     */
    private function generateSearchText(array $extracted, string $filePath): string
    {
        $parts = [];

        $parts[] = basename($filePath);
        $parts[] = implode(' ', $extracted['functions']);
        $parts[] = implode(' ', $extracted['classes']);
        $parts[] = implode(' ', $extracted['methods']);
        $parts[] = implode(' ', $extracted['comments']);
        $parts[] = implode(' ', $extracted['sql_queries']);
        $parts[] = implode(' ', $extracted['docblocks']);
        $parts[] = implode(' ', $extracted['api_endpoints']);

        return implode(' ', array_filter($parts));
    }

    /**
     * Generate keywords
     *
     * @param array $extracted Extracted elements
     * @return string Comma-separated keywords
     */
    private function generateKeywords(array $extracted): string
    {
        $keywords = [];

        $keywords = array_merge($keywords, $extracted['functions']);
        $keywords = array_merge($keywords, $extracted['classes']);
        $keywords = array_merge($keywords, $extracted['methods']);

        // Remove duplicates and limit
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 50);

        return json_encode(array_values($keywords));
    }

    /**
     * Generate semantic tags
     *
     * @param array $extracted Extracted elements
     * @return string Comma-separated tags
     */
    private function generateTags(array $extracted): string
    {
        $tags = [];

        // Detect patterns
        if (!empty($extracted['sql_queries'])) {
            $tags[] = 'database';
            $tags[] = 'sql';
        }

        if (!empty($extracted['api_endpoints'])) {
            $tags[] = 'api';
            $tags[] = 'rest';
        }

        if (count($extracted['classes']) > 0) {
            $tags[] = 'oop';
            $tags[] = 'class';
        }

        if (count($extracted['functions']) > 5) {
            $tags[] = 'functional';
        }

        return json_encode(array_values(array_unique($tags)));
    }

    /**
     * Extract entities (meaningful identifiers)
     *
     * @param array $extracted Extracted elements
     * @return string Comma-separated entities
     */
    private function extractEntities(array $extracted): string
    {
        $entities = [];

        // Classes are usually entities
        $entities = array_merge($entities, $extracted['classes']);

        // Look for common domain terms in function names
        $domainTerms = ['user', 'customer', 'order', 'product', 'inventory', 'transfer', 'payment', 'invoice'];
        foreach ($extracted['functions'] as $func) {
            foreach ($domainTerms as $term) {
                if (stripos($func, $term) !== false) {
                    $entities[] = $term;
                }
            }
        }

        $entities = array_unique($entities);
        return json_encode(array_values(array_slice($entities, 0, 20)));
    }

    /**
     * Store indexed data in database
     *
     * @param string $filePath File path
     * @param string $content File content
     * @param array $data Indexed data
     * @return bool Success
     */
    private function storeInDatabase(string $filePath, string $content, array $data): bool
    {
        // Check if file already indexed
        $stmt = $this->pdo->prepare("
            SELECT content_id FROM intelligence_content
            WHERE content_path = ? AND unit_id = ?
        ");
        $stmt->execute([$filePath, $this->unitId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing record
            $contentId = $existing['content_id'];

            $stmt = $this->pdo->prepare("
                UPDATE intelligence_content SET
                    file_modified = NOW(),
                    complexity_score = ?,
                    quality_score = ?,
                    last_analyzed = NOW(),
                    is_active = 1
                WHERE content_id = ?
            ");
            $stmt->execute([
                $data['complexity'],
                $data['quality'],
                $contentId
            ]);
        } else {
            // Insert new record
            $orgId = $this->config['org_id'] ?? 1; // Default to org 1
            $contentTypeId = 1; // PHP code type

            $stmt = $this->pdo->prepare("
                INSERT INTO intelligence_content (
                    org_id, unit_id, content_type_id, content_path, content_name, content_hash,
                    file_size, mime_type, complexity_score, quality_score, source_system,
                    last_analyzed, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'php_indexer', NOW(), 1, NOW())
            ");

            $stmt->execute([
                $orgId,
                $this->unitId,
                $contentTypeId,
                $filePath,
                basename($filePath),
                md5($content),
                strlen($content),
                'text/x-php',
                $data['complexity'],
                $data['quality']
            ]);

            $contentId = (int)$this->pdo->lastInsertId();
        }

        // Store text content
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_content_text (
                content_id, content_text, content_summary,
                extracted_keywords, semantic_tags, entities_detected,
                line_count, word_count, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                content_text = VALUES(content_text),
                content_summary = VALUES(content_summary),
                extracted_keywords = VALUES(extracted_keywords),
                semantic_tags = VALUES(semantic_tags),
                entities_detected = VALUES(entities_detected),
                line_count = VALUES(line_count),
                word_count = VALUES(word_count),
                updated_at = NOW()
        ");

        $lineCount = substr_count($content, "\n");
        $wordCount = str_word_count($data['search_text']);
        $summary = substr($data['search_text'], 0, 500);

        $stmt->execute([
            $contentId,
            $data['search_text'],
            $summary,
            $data['keywords'],
            $data['tags'],
            $data['entities'],
            $lineCount,
            $wordCount
        ]);

        return true;
    }

    /**
     * Get indexing statistics
     *
     * @return array Statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }
}
