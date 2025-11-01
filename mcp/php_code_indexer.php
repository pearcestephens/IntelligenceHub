<?php
/**
 * PHP Code Indexer - Index PHP files for semantic search
 *
 * Extracts:
 * - Functions, classes, methods
 * - DocBlocks and comments
 * - Variable names and constants
 * - SQL queries
 * - API endpoints
 * - Business logic patterns
 *
 * @package IntelligenceHub\MCP
 * @version 3.0.0
 */

declare(strict_types=1);

class PHPCodeIndexer {
    private PDO $pdo;
    private string $rootPath;
    private array $stats = [
        'files_processed' => 0,
        'functions_found' => 0,
        'classes_found' => 0,
        'lines_indexed' => 0,
    ];

    public function __construct(PDO $pdo, string $rootPath) {
        $this->pdo = $pdo;
        $this->rootPath = rtrim($rootPath, '/');
    }

    /**
     * Index all PHP files in directory
     */
    public function indexDirectory(string $directory, int $unitId = 1): array {
        $directory = $this->rootPath . '/' . ltrim($directory, '/');

        if (!is_dir($directory)) {
            throw new Exception("Directory not found: {$directory}");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $indexed = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Skip vendor and node_modules
                $path = $file->getPathname();
                if (strpos($path, '/vendor/') !== false || strpos($path, '/node_modules/') !== false) {
                    continue;
                }

                try {
                    $result = $this->indexFile($path, $unitId);
                    $indexed[] = $result;
                    $this->stats['files_processed']++;
                } catch (Exception $e) {
                    error_log("Failed to index {$path}: " . $e->getMessage());
                }
            }
        }

        return [
            'indexed_files' => count($indexed),
            'stats' => $this->stats,
            'files' => $indexed,
        ];
    }

    /**
     * Index single PHP file
     */
    public function indexFile(string $filePath, int $unitId = 1): array {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $relativePath = str_replace($this->rootPath, '', $filePath);

        // Extract code elements
        $extracted = $this->extractCodeElements($content);

        // Calculate file metrics
        $metrics = $this->calculateMetrics($content, $extracted);

        // Generate search text (content + extracted elements)
        $searchText = $this->generateSearchText($content, $extracted);

        // Generate keywords
        $keywords = $this->generateKeywords($extracted);

        // Generate semantic tags
        $tags = $this->generateSemanticTags($extracted, $content);

        // Generate entities
        $entities = $this->generateEntities($extracted);

        // Save to database
        $contentId = $this->saveToDatabase(
            $unitId,
            $relativePath,
            basename($filePath),
            $content,
            $searchText,
            $keywords,
            $tags,
            $entities,
            $metrics
        );

        $this->stats['lines_indexed'] += $metrics['lines'];
        $this->stats['functions_found'] += count($extracted['functions']);
        $this->stats['classes_found'] += count($extracted['classes']);

        return [
            'content_id' => $contentId,
            'file_path' => $relativePath,
            'functions' => count($extracted['functions']),
            'classes' => count($extracted['classes']),
            'lines' => $metrics['lines'],
        ];
    }

    /**
     * Extract code elements (functions, classes, etc.)
     */
    private function extractCodeElements(string $content): array {
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
            '/(?:public|private|protected)?\s*function\s+(\w+)\s*\(([^)]*)\)/i',
            $content,
            $functionMatches,
            PREG_SET_ORDER
        );
        foreach ($functionMatches as $match) {
            $elements['functions'][] = [
                'name' => $match[1],
                'params' => $match[2] ?? '',
            ];
        }

        // Extract classes
        preg_match_all(
            '/(?:abstract\s+)?class\s+(\w+)(?:\s+extends\s+(\w+))?(?:\s+implements\s+([^{]+))?/i',
            $content,
            $classMatches,
            PREG_SET_ORDER
        );
        foreach ($classMatches as $match) {
            $elements['classes'][] = [
                'name' => $match[1],
                'extends' => $match[2] ?? null,
                'implements' => isset($match[3]) ? array_map('trim', explode(',', $match[3])) : [],
            ];
        }

        // Extract methods (class methods)
        preg_match_all(
            '/(?:public|private|protected)\s+(?:static\s+)?function\s+(\w+)\s*\(/i',
            $content,
            $methodMatches,
            PREG_SET_ORDER
        );
        foreach ($methodMatches as $match) {
            if (!in_array($match[1], array_column($elements['functions'], 'name'))) {
                $elements['methods'][] = $match[1];
            }
        }

        // Extract constants
        preg_match_all(
            '/(?:const|define)\s+[\'"]?(\w+)[\'"]?\s*[=,]/i',
            $content,
            $constantMatches
        );
        $elements['constants'] = $constantMatches[1] ?? [];

        // Extract SQL queries
        preg_match_all(
            '/(SELECT|INSERT|UPDATE|DELETE|CREATE|ALTER|DROP)\s+.{10,200}/i',
            $content,
            $sqlMatches
        );
        $elements['sql_queries'] = array_slice($sqlMatches[0] ?? [], 0, 10); // Limit to 10

        // Extract API endpoints (routes)
        preg_match_all(
            '/[\'"]\/api\/[\w\/-]+[\'"]|Route::|->(?:get|post|put|delete|patch)\s*\([\'"]([^\'")]+)/i',
            $content,
            $apiMatches
        );
        $elements['api_endpoints'] = array_unique(array_filter($apiMatches[0] ?? []));

        // Extract docblocks
        preg_match_all(
            '/\/\*\*[\s\S]*?\*\//i',
            $content,
            $docblockMatches
        );
        $elements['docblocks'] = array_slice($docblockMatches[0] ?? [], 0, 20); // Limit to 20

        // Extract single-line comments with context
        preg_match_all(
            '/\/\/\s*(.{5,100})/i',
            $content,
            $commentMatches
        );
        $elements['comments'] = array_slice($commentMatches[1] ?? [], 0, 30); // Limit to 30

        return $elements;
    }

    /**
     * Calculate file metrics
     */
    private function calculateMetrics(string $content, array $extracted): array {
        $lines = substr_count($content, "\n") + 1;
        $codeLines = $lines - substr_count($content, "\n\n"); // Rough estimate

        return [
            'lines' => $lines,
            'code_lines' => $codeLines,
            'functions' => count($extracted['functions']),
            'classes' => count($extracted['classes']),
            'complexity' => $this->estimateComplexity($content),
            'quality_score' => $this->estimateQuality($extracted, $content),
        ];
    }

    /**
     * Estimate cyclomatic complexity
     */
    private function estimateComplexity(string $content): int {
        $complexity = 1; // Base complexity

        // Count decision points
        $complexity += substr_count($content, 'if (');
        $complexity += substr_count($content, 'else');
        $complexity += substr_count($content, 'elseif');
        $complexity += substr_count($content, 'for (');
        $complexity += substr_count($content, 'foreach (');
        $complexity += substr_count($content, 'while (');
        $complexity += substr_count($content, 'case ');
        $complexity += substr_count($content, 'catch (');
        $complexity += substr_count($content, '? '); // Ternary
        $complexity += substr_count($content, '&&');
        $complexity += substr_count($content, '||');

        return $complexity;
    }

    /**
     * Estimate code quality
     */
    private function estimateQuality(array $extracted, string $content): float {
        $score = 0.0;

        // Has docblocks?
        if (!empty($extracted['docblocks'])) {
            $score += 0.3;
        }

        // Has type hints?
        if (preg_match('/:\s*(int|string|bool|array|float)\s/', $content)) {
            $score += 0.2;
        }

        // Has error handling?
        if (stripos($content, 'try {') !== false || stripos($content, 'throw new') !== false) {
            $score += 0.2;
        }

        // Has validation?
        if (stripos($content, 'validate') !== false || stripos($content, 'filter_') !== false) {
            $score += 0.15;
        }

        // Has logging?
        if (stripos($content, 'log') !== false || stripos($content, 'error_log') !== false) {
            $score += 0.10;
        }

        // Not too long?
        $lines = substr_count($content, "\n");
        if ($lines < 500) {
            $score += 0.05;
        }

        return min($score, 1.0) * 100; // Convert to 0-100
    }

    /**
     * Generate search text (optimized for search)
     */
    private function generateSearchText(string $content, array $extracted): string {
        $searchParts = [];

        // Add function names
        foreach ($extracted['functions'] as $func) {
            $searchParts[] = $func['name'];
        }

        // Add class names
        foreach ($extracted['classes'] as $class) {
            $searchParts[] = $class['name'];
        }

        // Add method names
        $searchParts = array_merge($searchParts, $extracted['methods']);

        // Add docblock text (clean)
        foreach ($extracted['docblocks'] as $doc) {
            $clean = strip_tags($doc);
            $clean = preg_replace('/\s+/', ' ', $clean);
            $searchParts[] = $clean;
        }

        // Add comments
        $searchParts = array_merge($searchParts, $extracted['comments']);

        // Add a sample of the actual content (first 2000 chars)
        $searchParts[] = substr($content, 0, 2000);

        return implode("\n", $searchParts);
    }

    /**
     * Generate keywords
     */
    private function generateKeywords(array $extracted): string {
        $keywords = [];

        // Function names as keywords
        foreach ($extracted['functions'] as $func) {
            $keywords[] = $func['name'];
        }

        // Class names as keywords
        foreach ($extracted['classes'] as $class) {
            $keywords[] = $class['name'];
            if ($class['extends']) {
                $keywords[] = $class['extends'];
            }
        }

        // Method names
        $keywords = array_merge($keywords, $extracted['methods']);

        // Constants
        $keywords = array_merge($keywords, $extracted['constants']);

        // API endpoints (clean)
        foreach ($extracted['api_endpoints'] as $endpoint) {
            $clean = preg_replace('/[\'"\(\)]/', '', $endpoint);
            $keywords[] = $clean;
        }

        return implode(', ', array_unique($keywords));
    }

    /**
     * Generate semantic tags
     */
    private function generateSemanticTags(array $extracted, string $content): string {
        $tags = [];

        // Tag by what it does
        if (!empty($extracted['sql_queries'])) {
            $tags[] = 'database';
            $tags[] = 'data-access';
        }

        if (!empty($extracted['api_endpoints'])) {
            $tags[] = 'api';
            $tags[] = 'endpoint';
        }

        if (stripos($content, 'validate') !== false) {
            $tags[] = 'validation';
        }

        if (stripos($content, 'authenticate') !== false || stripos($content, 'authorize') !== false) {
            $tags[] = 'security';
            $tags[] = 'authentication';
        }

        if (stripos($content, 'email') !== false || stripos($content, 'mail') !== false) {
            $tags[] = 'email';
            $tags[] = 'notification';
        }

        if (stripos($content, 'payment') !== false || stripos($content, 'invoice') !== false) {
            $tags[] = 'payment';
            $tags[] = 'billing';
        }

        if (!empty($extracted['classes'])) {
            $tags[] = 'class';
            $tags[] = 'oop';
        }

        // Tag by patterns
        if (preg_match('/Controller|Handler|Service|Repository|Model/i', $content)) {
            $tags[] = 'mvc';
        }

        return implode(', ', array_unique($tags));
    }

    /**
     * Generate entities (important names/terms)
     */
    private function generateEntities(array $extracted): string {
        $entities = [];

        // Class names are entities
        foreach ($extracted['classes'] as $class) {
            $entities[] = $class['name'];
        }

        // Important function names (starting with capital or underscore)
        foreach ($extracted['functions'] as $func) {
            if (preg_match('/^[A-Z_]/', $func['name'])) {
                $entities[] = $func['name'];
            }
        }

        // Constants are entities
        $entities = array_merge($entities, $extracted['constants']);

        return implode(', ', array_unique($entities));
    }

    /**
     * Save to database
     */
    private function saveToDatabase(
        int $unitId,
        string $path,
        string $name,
        string $content,
        string $searchText,
        string $keywords,
        string $tags,
        string $entities,
        array $metrics
    ): int {
        // Check if exists
        $stmt = $this->pdo->prepare("
            SELECT content_id FROM intelligence_content
            WHERE unit_id = ? AND content_path = ?
        ");
        $stmt->execute([$unitId, $path]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        $hash = md5($content);
        $fileSize = strlen($content);
        $mimeType = 'text/x-php';

        if ($existing) {
            // Update existing
            $contentId = $existing['content_id'];

            $stmt = $this->pdo->prepare("
                UPDATE intelligence_content SET
                    content_hash = ?,
                    file_size = ?,
                    file_modified = NOW(),
                    mime_type = ?,
                    complexity_score = ?,
                    quality_score = ?,
                    updated_at = NOW()
                WHERE content_id = ?
            ");
            $stmt->execute([
                $hash,
                $fileSize,
                $mimeType,
                $metrics['complexity'],
                $metrics['quality_score'],
                $contentId
            ]);

            // Update text content
            $stmt = $this->pdo->prepare("
                UPDATE intelligence_content_text SET
                    text_content = ?,
                    extracted_keywords = ?,
                    semantic_tags = ?,
                    entities_detected = ?
                WHERE content_id = ?
            ");
            $stmt->execute([
                $searchText,
                $keywords,
                $tags,
                $entities,
                $contentId
            ]);

        } else {
            // Insert new
            $stmt = $this->pdo->prepare("
                INSERT INTO intelligence_content (
                    unit_id, content_path, content_name, content_hash,
                    file_size, mime_type, complexity_score, quality_score,
                    is_active, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $unitId,
                $path,
                $name,
                $hash,
                $fileSize,
                $mimeType,
                $metrics['complexity'],
                $metrics['quality_score']
            ]);

            $contentId = (int)$this->pdo->lastInsertId();

            // Insert text content
            $stmt = $this->pdo->prepare("
                INSERT INTO intelligence_content_text (
                    content_id, text_content, extracted_keywords,
                    semantic_tags, entities_detected
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $contentId,
                $searchText,
                $keywords,
                $tags,
                $entities
            ]);
        }

        return $contentId;
    }

    /**
     * Get indexing statistics
     */
    public function getStats(): array {
        return $this->stats;
    }
}
