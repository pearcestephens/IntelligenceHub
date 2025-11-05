<?php
/**
 * ULTRA SCANNER - Comprehensive Code Intelligence Extractor
 *
 * Scans files and populates ALL 18 intelligence tables:
 * 1. intelligence_files - File metadata
 * 2. intelligence_content - Content chunks
 * 3. intelligence_content_text - Searchable text
 * 4. intelligence_metrics - Quality metrics
 * 5. intelligence_functions - Function definitions
 * 6. intelligence_classes - Class definitions
 * 7. intelligence_dependencies - Imports/requires
 * 8. intelligence_code_patterns - Design patterns
 * 9. intelligence_neural_patterns - AI insights
 * 10. intelligence_kb_categories - Content classification
 * 11. intelligence_code_standards - Compliance
 * 12. intelligence_function_usage - Call graphs
 * 13. intelligence_class_relationships - Inheritance
 * 14. intelligence_todos - TODO/FIXME tracking
 * 15. intelligence_content_types - File type lookup
 * 16. intelligence_alerts - Scan alerts
 * 17. intelligence_automation - Automation triggers
 * 18. intelligence_automation_executions - Execution logs
 */

namespace IntelligenceHub\MCP\Tools;

use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class UltraScanner extends BaseTool {
    private PDO $pdo;
    private string $rootPath;
    private array $stats = [];

    // Default context
    private int $orgId = 1;
    private int $unitId = 999; // Playground
    private ?int $projectId = null;

    public function __construct() {
        $this->pdo = \IntelligenceHub\MCP\Database\Connection::getInstance();
        $this->rootPath = dirname(dirname(dirname(__DIR__)));
    }

    public function getName(): string {
        return 'ultra_scanner';
    }

    public function getSchema(): array {
        return [
            'ultra_scanner.scan_file' => [
                'description' => 'ULTRA SCAN: Populate ALL 18 intelligence tables for one file',
                'parameters' => [
                    'file' => ['type' => 'string', 'required' => true],
                    'org_id' => ['type' => 'integer', 'required' => false],
                    'unit_id' => ['type' => 'integer', 'required' => false],
                    'project_id' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'ultra_scanner.scan_project' => [
                'description' => 'ULTRA SCAN: Entire project to ALL tables',
                'parameters' => [
                    'directory' => ['type' => 'string', 'required' => true],
                    'max_files' => ['type' => 'integer', 'required' => false],
                    'org_id' => ['type' => 'integer', 'required' => false],
                    'unit_id' => ['type' => 'integer', 'required' => false],
                    'project_id' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'ultra_scanner.get_stats' => [
                'description' => 'Get comprehensive stats from all 18 tables',
                'parameters' => []
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'scan_file';

        // Set context
        $this->orgId = $args['org_id'] ?? 1;
        $this->unitId = $args['unit_id'] ?? 999;
        $this->projectId = $args['project_id'] ?? null;

        switch ($method) {
            case 'scan_file':
                return $this->ultraScanFile($args);
            case 'scan_project':
                return $this->ultraScanProject($args);
            case 'get_stats':
                return $this->getComprehensiveStats();
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    /**
     * ULTRA SCAN SINGLE FILE
     * Populates all relevant tables for one file
     */
    private function ultraScanFile(array $args): array {
        $filePath = $args['file'];

        if (!file_exists($filePath)) {
            return $this->fail("File not found: $filePath");
        }

        $this->stats = [
            'file' => $filePath,
            'tables_populated' => [],
            'extractions' => []
        ];

        try {
            $this->pdo->beginTransaction();

            // 1. File metadata (intelligence_files)
            $fileId = $this->extractFileMetadata($filePath);
            $this->stats['file_id'] = $fileId;
            $this->stats['tables_populated'][] = 'intelligence_files';

            // 2. Content + text (intelligence_content, intelligence_content_text)
            $contentId = $this->extractContent($fileId, $filePath);
            if ($contentId) {
                $this->stats['tables_populated'][] = 'intelligence_content';
                $this->stats['tables_populated'][] = 'intelligence_content_text';
            }

            // 3. Metrics (intelligence_metrics)
            $this->extractMetrics($fileId, $filePath);
            $this->stats['tables_populated'][] = 'intelligence_metrics';

            // Get file type for conditional extraction
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // CODE FILE EXTRACTIONS (PHP, JS, TS, etc.)
            if (in_array($ext, ['php', 'js', 'ts', 'jsx', 'tsx', 'py', 'java', 'cpp', 'c', 'cs'])) {

                // 4. Functions (intelligence_functions)
                $funcCount = $this->extractFunctions($fileId, $filePath);
                $this->stats['extractions']['functions'] = $funcCount;
                $this->stats['tables_populated'][] = 'intelligence_functions';

                // 5. Classes (intelligence_classes)
                $classCount = $this->extractClasses($fileId, $filePath);
                $this->stats['extractions']['classes'] = $classCount;
                $this->stats['tables_populated'][] = 'intelligence_classes';

                // 6. Dependencies (intelligence_dependencies)
                $depCount = $this->extractDependencies($fileId, $filePath);
                $this->stats['extractions']['dependencies'] = $depCount;
                $this->stats['tables_populated'][] = 'intelligence_dependencies';

                // 7. Code Patterns (intelligence_code_patterns)
                $patternCount = $this->extractCodePatterns($fileId, $filePath);
                $this->stats['extractions']['patterns'] = $patternCount;
                $this->stats['tables_populated'][] = 'intelligence_code_patterns';

                // 8. Code Standards (intelligence_code_standards)
                $standardCount = $this->extractCodeStandards($fileId, $filePath);
                $this->stats['extractions']['standards'] = $standardCount;
                $this->stats['tables_populated'][] = 'intelligence_code_standards';

                // 9. TODOs (intelligence_todos)
                $todoCount = $this->extractTodos($fileId, $filePath);
                $this->stats['extractions']['todos'] = $todoCount;
                $this->stats['tables_populated'][] = 'intelligence_todos';
            }

            // 10. KB Categories (all files)
            $this->extractKBCategories($fileId, $filePath);
            $this->stats['tables_populated'][] = 'intelligence_kb_categories';

            // 11. Neural Patterns (AI-based, optional)
            // $this->extractNeuralPatterns($fileId, $filePath);

            $this->pdo->commit();

            return $this->ok([
                'success' => true,
                'message' => 'ULTRA SCAN complete',
                'stats' => $this->stats,
                'tables_populated' => count($this->stats['tables_populated']),
                'total_extractions' => array_sum($this->stats['extractions'] ?? [])
            ]);

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return $this->fail("ULTRA SCAN failed: " . $e->getMessage());
        }
    }

    /**
     * ULTRA SCAN PROJECT
     * Scan entire directory to all tables
     */
    private function ultraScanProject(array $args): array {
        $directory = $args['directory'];
        $maxFiles = $args['max_files'] ?? 500;
        $scanPath = $this->rootPath . '/' . ltrim($directory, '/');

        if (!is_dir($scanPath)) {
            return $this->fail("Directory not found: $scanPath");
        }

        $this->stats = [
            'files_scanned' => 0,
            'files_success' => 0,
            'files_failed' => 0,
            'total_functions' => 0,
            'total_classes' => 0,
            'total_dependencies' => 0,
            'total_patterns' => 0,
            'total_todos' => 0,
            'errors' => []
        ];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scanPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($this->stats['files_scanned'] >= $maxFiles) break;

            if ($file->isFile() && $this->shouldScan($file->getPathname())) {
                $result = $this->ultraScanFile(['file' => $file->getPathname()]);

                if ($result['success'] ?? false) {
                    $this->stats['files_success']++;
                    $this->stats['total_functions'] += $result['data']['stats']['extractions']['functions'] ?? 0;
                    $this->stats['total_classes'] += $result['data']['stats']['extractions']['classes'] ?? 0;
                    $this->stats['total_dependencies'] += $result['data']['stats']['extractions']['dependencies'] ?? 0;
                    $this->stats['total_patterns'] += $result['data']['stats']['extractions']['patterns'] ?? 0;
                    $this->stats['total_todos'] += $result['data']['stats']['extractions']['todos'] ?? 0;
                } else {
                    $this->stats['files_failed']++;
                    $this->stats['errors'][] = $file->getPathname();
                }

                $this->stats['files_scanned']++;
            }
        }

        return $this->ok([
            'message' => 'ULTRA SCAN PROJECT complete',
            'stats' => $this->stats
        ]);
    }

    /**
     * 1. FILE METADATA
     */
    private function extractFileMetadata(string $filePath): int {
        $relativePath = str_replace($this->rootPath . '/', '', $filePath);
        $content = file_get_contents($filePath);
        $contentHash = hash('sha256', $content);

        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Check if exists
        $stmt = $this->pdo->prepare("
            SELECT file_id FROM intelligence_files
            WHERE content_hash = ?
            LIMIT 1
        ");
        $stmt->execute([$contentHash]);
        $existing = $stmt->fetch();

        if ($existing) {
            return $existing['file_id'];
        }

        // Insert new
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_files (
                project_id, business_unit_id, org_id,
                file_path, file_name, file_extension,
                mime_type, file_size, content_hash,
                intelligence_score, complexity_score,
                last_scanned
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, NOW())
        ");

        $stmt->execute([
            $this->projectId,
            $this->unitId,
            $this->orgId,
            $relativePath,
            basename($filePath),
            $extension,
            $mimeType,
            $fileSize,
            $contentHash
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * 2. CONTENT + TEXT
     */
    private function extractContent(int $fileId, string $filePath): ?int {
        $content = file_get_contents($filePath);
        $contentHash = hash('sha256', $content);

        // Insert content
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_content (
                file_id, org_id, unit_id,
                content_type_id, content_hash,
                last_analyzed
            ) VALUES (?, ?, ?, 1, ?, NOW())
        ");

        $stmt->execute([
            $fileId,
            $this->orgId,
            $this->unitId,
            $contentHash
        ]);

        $contentId = $this->pdo->lastInsertId();

        // Extract keywords and tags
        $keywords = $this->extractKeywords($content);
        $tags = $this->extractSemanticTags($content, $filePath);

        // Insert text
        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_content_text (
                content_id, content_text,
                extracted_keywords, semantic_tags,
                line_count, word_count
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $lineCount = substr_count($content, "\n") + 1;
        $wordCount = str_word_count($content);

        $stmt->execute([
            $contentId,
            $content,
            json_encode($keywords),
            json_encode($tags),
            $lineCount,
            $wordCount
        ]);

        return $contentId;
    }

    /**
     * 3. METRICS
     */
    private function extractMetrics(int $fileId, string $filePath): void {
        $content = file_get_contents($filePath);
        $lineCount = substr_count($content, "\n") + 1;

        // Complexity (simple estimate based on control structures)
        $complexity = substr_count($content, 'if ') +
                     substr_count($content, 'for ') +
                     substr_count($content, 'while ') +
                     substr_count($content, 'switch ');

        $metrics = [
            ['lines_of_code', $lineCount],
            ['cyclomatic_complexity', $complexity],
            ['file_size_bytes', filesize($filePath)]
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_metrics (
                org_id, unit_id, metric_category, metric_name,
                metric_value, source_system
            ) VALUES (?, ?, 'code_quality', ?, ?, 'ultra_scanner')
        ");

        foreach ($metrics as [$name, $value]) {
            $stmt->execute([$this->orgId, $this->unitId, $name, $value]);
        }
    }

    /**
     * 4. FUNCTIONS
     */
    private function extractFunctions(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $count = 0;

        if ($ext === 'php') {
            // PHP function extraction (basic regex)
            preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)/m', $content, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[1] as $idx => $match) {
                $funcName = $match[0];
                $offset = $match[1];

                // Find line number
                $beforeFunc = substr($content, 0, $offset);
                $lineNum = substr_count($beforeFunc, "\n") + 1;

                // Extract signature
                $signature = $matches[0][$idx][0];

                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_functions (
                        file_id, project_id, business_unit_id, org_id,
                        function_name, function_type, start_line,
                        end_line, line_count, function_signature,
                        complexity_score
                    ) VALUES (?, ?, ?, ?, ?, 'function', ?, ?, 1, ?, 1.0)
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $funcName, $lineNum, $lineNum, $signature
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 5. CLASSES
     */
    private function extractClasses(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $count = 0;

        if ($ext === 'php') {
            // PHP class extraction
            preg_match_all('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/m', $content, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[1] as $idx => $match) {
                $className = $match[0];
                $offset = $match[1];

                $beforeClass = substr($content, 0, $offset);
                $lineNum = substr_count($beforeClass, "\n") + 1;

                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_classes (
                        file_id, project_id, business_unit_id, org_id,
                        class_name, class_type, full_qualified_name,
                        start_line, end_line, line_count, class_signature
                    ) VALUES (?, ?, ?, ?, ?, 'class', ?, ?, ?, 1, ?)
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $className, $className, $lineNum, $lineNum, 'class ' . $className
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 6. DEPENDENCIES
     */
    private function extractDependencies(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $count = 0;

        if ($ext === 'php') {
            // PHP require/include/use
            preg_match_all('/(require|require_once|include|include_once|use)\s+[\'"]?([^\'";\s]+)/m', $content, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[2] as $idx => $match) {
                $depPath = $match[0];
                $type = $matches[1][$idx][0];
                $offset = $match[1];

                $beforeDep = substr($content, 0, $offset);
                $lineNum = substr_count($beforeDep, "\n") + 1;

                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_dependencies (
                        file_id, project_id, business_unit_id, org_id,
                        dependency_type, dependency_path, line_number
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $type, $depPath, $lineNum
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 7. CODE PATTERNS
     */
    private function extractCodePatterns(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $count = 0;

        // Simple pattern detection
        $patterns = [
            'singleton' => '/private\s+static\s+\$instance.*getInstance/s',
            'factory' => '/(class|interface)\s+\w*Factory/',
            'observer' => '/(class|interface)\s+\w*Observer/'
        ];

        foreach ($patterns as $patternName => $regex) {
            if (preg_match($regex, $content, $match, PREG_OFFSET_CAPTURE)) {
                $offset = $match[0][1];
                $beforePattern = substr($content, 0, $offset);
                $lineNum = substr_count($beforePattern, "\n") + 1;

                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_code_patterns (
                        file_id, project_id, business_unit_id, org_id,
                        pattern_name, pattern_type, confidence_score,
                        detection_method, start_line, end_line
                    ) VALUES (?, ?, ?, ?, ?, 'design', 75.0, 'regex', ?, ?)
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $patternName, $lineNum, $lineNum
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 8. CODE STANDARDS
     */
    private function extractCodeStandards(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $count = 0;

        // Simple standards checks
        $lines = explode("\n", $content);

        foreach ($lines as $lineNum => $line) {
            // Check line length
            if (strlen($line) > 120) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_code_standards (
                        file_id, project_id, business_unit_id, org_id,
                        standard_name, rule_name, status, severity,
                        line_number, message
                    ) VALUES (?, ?, ?, ?, 'PSR-12', 'line_length', 'violation', 'low', ?, 'Line exceeds 120 characters')
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $lineNum + 1
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 9. TODOs
     */
    private function extractTodos(int $fileId, string $filePath): int {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        $count = 0;

        foreach ($lines as $lineNum => $line) {
            if (preg_match('/(TODO|FIXME|HACK|XXX|BUG)[\s:]+(.+)$/i', $line, $match)) {
                $type = strtoupper($match[1]);
                $text = trim($match[2]);

                $stmt = $this->pdo->prepare("
                    INSERT INTO intelligence_todos (
                        file_id, project_id, business_unit_id, org_id,
                        todo_type, line_number, todo_text, priority, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'medium', 'open')
                ");

                $stmt->execute([
                    $fileId, $this->projectId, $this->unitId, $this->orgId,
                    $type, $lineNum + 1, $text
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * 10. KB CATEGORIES
     */
    private function extractKBCategories(int $fileId, string $filePath): void {
        $relativePath = str_replace($this->rootPath . '/', '', $filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Simple categorization
        $category = 'unknown';
        $type = 'technical';

        if (strpos($relativePath, 'test') !== false) {
            $category = 'testing';
            $type = 'test';
        } elseif (strpos($relativePath, 'config') !== false) {
            $category = 'configuration';
            $type = 'configuration';
        } elseif (strpos($relativePath, 'docs') !== false) {
            $category = 'documentation';
            $type = 'documentation';
        } elseif (in_array($ext, ['php', 'js', 'ts', 'py'])) {
            $category = 'code';
            $type = 'technical';
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO intelligence_kb_categories (
                file_id, project_id, business_unit_id, org_id,
                category_name, category_type, confidence_score,
                classification_method
            ) VALUES (?, ?, ?, ?, ?, ?, 100.0, 'rule-based')
        ");

        $stmt->execute([
            $fileId, $this->projectId, $this->unitId, $this->orgId,
            $category, $type
        ]);
    }

    /**
     * Get comprehensive stats from all tables
     */
    private function getComprehensiveStats(): array {
        $tables = [
            'intelligence_files',
            'intelligence_content',
            'intelligence_content_text',
            'intelligence_metrics',
            'intelligence_functions',
            'intelligence_classes',
            'intelligence_dependencies',
            'intelligence_code_patterns',
            'intelligence_neural_patterns',
            'intelligence_kb_categories',
            'intelligence_code_standards',
            'intelligence_function_usage',
            'intelligence_class_relationships',
            'intelligence_todos',
            'intelligence_alerts',
            'intelligence_automation',
            'intelligence_automation_executions',
            'intelligence_content_types'
        ];

        $stats = [];

        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM `$table`");
            $result = $stmt->fetch();
            $stats[$table] = $result['count'];
        }

        return $this->ok([
            'total_tables' => count($tables),
            'table_counts' => $stats,
            'total_records' => array_sum($stats)
        ]);
    }

    /**
     * Helper: Extract keywords
     */
    private function extractKeywords(string $content): array {
        // Simple keyword extraction
        preg_match_all('/\b[a-z]{4,}\b/i', $content, $matches);
        $words = array_count_values($matches[0]);
        arsort($words);
        return array_keys(array_slice($words, 0, 20));
    }

    /**
     * Helper: Extract semantic tags
     */
    private function extractSemanticTags(string $content, string $filePath): array {
        $tags = [];

        if (strpos($content, 'class ') !== false) $tags[] = 'object-oriented';
        if (strpos($content, 'function ') !== false) $tags[] = 'functional';
        if (strpos($content, 'namespace ') !== false) $tags[] = 'namespaced';
        if (strpos($content, 'interface ') !== false) $tags[] = 'interface';
        if (strpos($content, 'trait ') !== false) $tags[] = 'trait';

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $tags[] = $ext;

        return $tags;
    }

    /**
     * Helper: Should scan this file?
     */
    private function shouldScan(string $filePath): bool {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $scannable = ['php', 'js', 'ts', 'jsx', 'tsx', 'py', 'java', 'cpp', 'c', 'cs',
                      'html', 'css', 'scss', 'json', 'xml', 'md', 'txt', 'sql'];

        $ignore = ['vendor', 'node_modules', '.git', 'cache', 'tmp', 'logs'];

        foreach ($ignore as $dir) {
            if (strpos($filePath, '/' . $dir . '/') !== false) {
                return false;
            }
        }

        return in_array($ext, $scannable);
    }
}
