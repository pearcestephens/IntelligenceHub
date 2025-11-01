<?php
/**
 * MCP Advanced Tools
 * 
 * Additional sophisticated tools for GitHub Copilot integration
 * 
 * @package CIS\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

class MCPAdvancedTools
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Suggest implementation based on existing patterns
     */
    public function suggestImplementation(array $args): array
    {
        $task = $args['task'] ?? '';
        $fileType = $args['file_type'] ?? 'php';
        $context = $args['context'] ?? '';
        
        if (empty($task)) {
            throw new Exception("task parameter is required", -32602);
        }
        
        // Find similar implementations
        $stmt = $this->pdo->prepare("
            SELECT 
                f.file_path,
                f.file_name,
                s.search_content,
                c.patterns,
                c.concepts,
                q.quality_score
            FROM kb_files f
            JOIN kb_search_index s ON f.id = s.file_id
            LEFT JOIN kb_quality c ON f.id = c.file_id
            LEFT JOIN simple_quality q ON f.id = q.file_id
            WHERE f.file_type = :file_type
            AND (
                s.search_content LIKE :task
                OR c.patterns LIKE :task
                OR c.concepts LIKE :task
            )
            ORDER BY q.quality_score DESC
            LIMIT 5
        ");
        $stmt->execute([
            ':file_type' => $fileType,
            ':task' => "%{$task}%"
        ]);
        $examples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Extract common patterns
        $patterns = [];
        foreach ($examples as $example) {
            if (!empty($example['patterns'])) {
                $patterns[] = $example['patterns'];
            }
        }
        
        $suggestion = [
            'task' => $task,
            'file_type' => $fileType,
            'examples_found' => count($examples),
            'example_files' => array_column($examples, 'file_path'),
            'common_patterns' => array_unique($patterns),
            'recommended_approach' => $this->generateRecommendation($examples, $task),
            'code_template' => $this->generateCodeTemplate($examples, $fileType, $task)
        ];
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($suggestion, JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Analyze impact of changing a file
     */
    public function analyzeImpact(array $args): array
    {
        $filePath = $args['file_path'] ?? '';
        $changeType = $args['change_type'] ?? 'modify';
        
        if (empty($filePath)) {
            throw new Exception("file_path parameter is required", -32602);
        }
        
        // Get file info
        $stmt = $this->pdo->prepare("
            SELECT id, file_name, file_type
            FROM kb_files
            WHERE file_path LIKE :path
            LIMIT 1
        ");
        $stmt->execute([':path' => "%{$filePath}%"]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            throw new Exception("File not found: $filePath", -32602);
        }
        
        // Find files that might be affected
        $impactAnalysis = [
            'file' => $filePath,
            'change_type' => $changeType,
            'potential_impacts' => []
        ];
        
        // Files in same category
        $stmt = $this->pdo->prepare("
            SELECT f.file_path, f.file_name, o.category
            FROM kb_files f
            JOIN kb_organization o ON f.id = o.file_id
            WHERE o.category IN (
                SELECT category FROM kb_organization WHERE file_id = :file_id
            )
            AND f.id != :file_id
            LIMIT 20
        ");
        $stmt->execute([':file_id' => $file['id']]);
        $relatedFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $impactAnalysis['potential_impacts'][] = [
            'type' => 'related_files',
            'severity' => 'medium',
            'count' => count($relatedFiles),
            'files' => array_column($relatedFiles, 'file_path')
        ];
        
        // Files that reference this filename
        $fileName = $file['file_name'];
        $stmt = $this->pdo->prepare("
            SELECT f.file_path
            FROM kb_files f
            JOIN kb_search_index s ON f.id = s.file_id
            WHERE s.search_content LIKE :filename
            AND f.id != :file_id
            LIMIT 20
        ");
        $stmt->execute([
            ':filename' => "%{$fileName}%",
            ':file_id' => $file['id']
        ]);
        $referencingFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($referencingFiles)) {
            $impactAnalysis['potential_impacts'][] = [
                'type' => 'direct_references',
                'severity' => 'high',
                'count' => count($referencingFiles),
                'files' => $referencingFiles,
                'warning' => 'These files may contain require/include statements or direct references'
            ];
        }
        
        // Estimate risk
        $totalImpact = array_sum(array_column($impactAnalysis['potential_impacts'], 'count'));
        $impactAnalysis['risk_level'] = $totalImpact === 0 ? 'low' : ($totalImpact < 10 ? 'medium' : 'high');
        $impactAnalysis['recommendation'] = $this->getImpactRecommendation($impactAnalysis['risk_level'], $changeType);
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($impactAnalysis, JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Enforce coding standards check
     */
    public function enforceStandards(array $args): array
    {
        $code = $args['code'] ?? '';
        $fileType = $args['file_type'] ?? 'php';
        
        if (empty($code)) {
            throw new Exception("code parameter is required", -32602);
        }
        
        $violations = [];
        $suggestions = [];
        
        if ($fileType === 'php') {
            // Check for strict types
            if (!preg_match('/declare\s*\(\s*strict_types\s*=\s*1\s*\)/', $code)) {
                $violations[] = [
                    'rule' => 'strict_types',
                    'severity' => 'error',
                    'message' => 'Missing declare(strict_types=1)',
                    'fix' => "Add 'declare(strict_types=1);' after opening PHP tag"
                ];
            }
            
            // Check for docblocks
            if (preg_match_all('/(?:function|class)\s+\w+/', $code) > 0) {
                $functionCount = preg_match_all('/function\s+\w+/', $code);
                $docblockCount = preg_match_all('/\/\*\*.*?\*\//s', $code);
                
                if ($docblockCount < $functionCount) {
                    $violations[] = [
                        'rule' => 'docblocks',
                        'severity' => 'warning',
                        'message' => 'Some functions missing docblocks',
                        'fix' => 'Add PHPDoc comments to all functions and classes'
                    ];
                }
            }
            
            // Check for PDO prepared statements
            if (preg_match('/\$pdo->query\s*\(\s*["\'].*?\$/', $code)) {
                $violations[] = [
                    'rule' => 'sql_injection',
                    'severity' => 'critical',
                    'message' => 'Potential SQL injection vulnerability',
                    'fix' => 'Use prepared statements with parameter binding'
                ];
            }
            
            // Check for error handling
            if (preg_match('/new\s+PDO\s*\(/', $code) && !preg_match('/try\s*{/', $code)) {
                $violations[] = [
                    'rule' => 'error_handling',
                    'severity' => 'warning',
                    'message' => 'Database connection without try-catch',
                    'fix' => 'Wrap PDO instantiation in try-catch block'
                ];
            }
            
            // Check for ecig_ table prefix
            if (preg_match('/FROM\s+`?(?!ecig_)\w+`?/i', $code)) {
                $violations[] = [
                    'rule' => 'table_naming',
                    'severity' => 'error',
                    'message' => 'Database tables must use ecig_ prefix',
                    'fix' => 'Rename tables to include ecig_ prefix (e.g., kb_files)'
                ];
            }
        }
        
        // Generate compliance score
        $totalChecks = 5;
        $passed = $totalChecks - count($violations);
        $complianceScore = round(($passed / $totalChecks) * 100);
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'compliance_score' => $complianceScore,
                        'violations' => $violations,
                        'total_violations' => count($violations),
                        'critical' => count(array_filter($violations, fn($v) => $v['severity'] === 'critical')),
                        'warnings' => count(array_filter($violations, fn($v) => $v['severity'] === 'warning')),
                        'passed' => $complianceScore >= 80,
                        'recommendation' => $complianceScore >= 80 ? 'Code meets standards' : 'Fix violations before committing'
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    /**
     * Multi-file refactoring suggestions
     */
    public function suggestRefactoring(array $args): array
    {
        $category = $args['category'] ?? '';
        $refactorType = $args['refactor_type'] ?? 'cleanup';
        
        $suggestions = [];
        
        // Find candidates for refactoring
        if ($refactorType === 'cleanup') {
            // Find low-quality files
            $stmt = $this->pdo->query("
                SELECT f.file_path, f.file_name, q.quality_score, q.issues_count
                FROM kb_files f
                JOIN simple_quality q ON f.id = q.file_id
                WHERE q.quality_score < 70
                ORDER BY q.quality_score ASC
                LIMIT 10
            ");
            $lowQuality = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($lowQuality)) {
                $suggestions[] = [
                    'type' => 'quality_improvement',
                    'priority' => 'high',
                    'files' => $lowQuality,
                    'action' => 'Improve code quality, add documentation, fix issues'
                ];
            }
        }
        
        if ($refactorType === 'consolidation') {
            // Find similar files that could be consolidated
            $stmt = $this->pdo->query("
                SELECT category, COUNT(*) as file_count
                FROM kb_organization
                WHERE category IS NOT NULL
                GROUP BY category
                HAVING file_count > 10
                ORDER BY file_count DESC
            ");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($categories as $cat) {
                $suggestions[] = [
                    'type' => 'consolidation_opportunity',
                    'priority' => 'medium',
                    'category' => $cat['category'],
                    'file_count' => $cat['file_count'],
                    'action' => 'Consider consolidating similar functionality into shared libraries'
                ];
            }
        }
        
        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode([
                        'refactor_type' => $refactorType,
                        'suggestions' => $suggestions,
                        'total_opportunities' => count($suggestions)
                    ], JSON_PRETTY_PRINT)
                ]
            ]
        ];
    }
    
    // Helper methods
    
    private function generateRecommendation(array $examples, string $task): string
    {
        if (empty($examples)) {
            return "No existing examples found. Follow PSR-12 standards and include proper error handling.";
        }
        
        $avgQuality = array_sum(array_column($examples, 'quality_score')) / count($examples);
        
        return sprintf(
            "Found %d similar implementations with average quality %.1f/100. " .
            "Review the example files and follow their patterns for consistency. " .
            "Key recommendation: %s",
            count($examples),
            $avgQuality,
            $avgQuality > 80 ? "These are high-quality examples - replicate their structure" :
                               "These examples have room for improvement - enhance upon their approach"
        );
    }
    
    private function generateCodeTemplate(array $examples, string $fileType, string $task): string
    {
        if ($fileType === 'php') {
            return "<?php\n" .
                   "declare(strict_types=1);\n\n" .
                   "/**\n * " . ucfirst($task) . "\n */\n\n" .
                   "// TODO: Implement based on examples found\n" .
                   "// Review: " . (empty($examples) ? "No examples" : $examples[0]['file_path']) . "\n";
        }
        
        return "// Template for: " . $task;
    }
    
    private function getImpactRecommendation(string $riskLevel, string $changeType): string
    {
        $recommendations = [
            'low' => [
                'modify' => 'Low risk change. Proceed with standard testing.',
                'delete' => 'File appears unused. Verify and archive before deletion.',
                'rename' => 'Safe to rename. Update any direct references.'
            ],
            'medium' => [
                'modify' => 'Medium risk. Test affected files after changes.',
                'delete' => 'File has dependencies. Check referencing files before deletion.',
                'rename' => 'Update all references. Consider creating alias for compatibility.'
            ],
            'high' => [
                'modify' => 'HIGH RISK! This file is widely used. Thorough testing required.',
                'delete' => 'DANGER! File is heavily referenced. Do not delete without migration plan.',
                'rename' => 'HIGH IMPACT! Many files reference this. Create compatibility layer first.'
            ]
        ];
        
        return $recommendations[$riskLevel][$changeType] ?? 'Exercise caution with this change.';
    }
}
