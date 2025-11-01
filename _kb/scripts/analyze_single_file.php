#!/usr/bin/env php
<?php
/**
 * Single File Analyzer
 * 
 * Analyzes a single PHP file and updates KB intelligence incrementally
 * Called by the file watcher when changes are detected
 * 
 * @package KB\Scripts
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use PhpParser\Error;
use PhpParser\ParserFactory;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class FileAnalyzer extends NodeVisitorAbstract
{
    private array $analysis = [
        'functions' => [],
        'classes' => [],
        'calls' => [],
        'security_issues' => [],
        'complexity' => 0,
        'lines' => 0,
    ];
    
    private string $currentClass = '';
    
    public function getAnalysis(): array
    {
        return $this->analysis;
    }
    
    public function enterNode(Node $node)
    {
        // Track classes
        if ($node instanceof Node\Stmt\Class_) {
            $this->currentClass = $node->name ? $node->name->toString() : '';
            $this->analysis['classes'][] = [
                'name' => $this->currentClass,
                'line' => $node->getLine(),
                'abstract' => $node->isAbstract(),
                'final' => $node->isFinal(),
            ];
        }
        
        // Track functions
        if ($node instanceof Node\Stmt\Function_) {
            $this->analysis['functions'][] = [
                'name' => $node->name->toString(),
                'line' => $node->getLine(),
                'params' => count($node->params),
            ];
        }
        
        // Track methods
        if ($node instanceof Node\Stmt\ClassMethod) {
            $methodName = $node->name->toString();
            $fullName = $this->currentClass ? "{$this->currentClass}::{$methodName}" : $methodName;
            
            $this->analysis['functions'][] = [
                'name' => $fullName,
                'line' => $node->getLine(),
                'params' => count($node->params),
                'visibility' => $this->getVisibility($node),
            ];
        }
        
        // Track function calls
        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $this->analysis['calls'][] = $node->name->toString();
        }
        
        // Check for SQL without prepared statements
        if ($node instanceof Node\Expr\MethodCall && $node->name instanceof Node\Identifier) {
            $method = $node->name->toString();
            if (in_array($method, ['query', 'exec']) && isset($node->args[0])) {
                if ($this->hasStringConcatenation($node->args[0]->value)) {
                    $this->analysis['security_issues'][] = [
                        'type' => 'SQL_INJECTION',
                        'line' => $node->getLine(),
                        'message' => 'Potential SQL injection with string concatenation',
                    ];
                }
            }
        }
        
        // Calculate complexity
        if ($this->isComplexityNode($node)) {
            $this->analysis['complexity']++;
        }
        
        return null;
    }
    
    private function getVisibility(Node\Stmt\ClassMethod $node): string
    {
        if ($node->isPublic()) return 'public';
        if ($node->isProtected()) return 'protected';
        if ($node->isPrivate()) return 'private';
        return 'public'; // default
    }
    
    private function hasStringConcatenation($expr): bool
    {
        return $expr instanceof Node\Expr\BinaryOp\Concat;
    }
    
    private function isComplexityNode(Node $node): bool
    {
        return $node instanceof Node\Stmt\If_
            || $node instanceof Node\Stmt\ElseIf_
            || $node instanceof Node\Stmt\Else_
            || $node instanceof Node\Stmt\For_
            || $node instanceof Node\Stmt\Foreach_
            || $node instanceof Node\Stmt\While_
            || $node instanceof Node\Stmt\Do_
            || $node instanceof Node\Stmt\Switch_
            || $node instanceof Node\Stmt\Case_
            || $node instanceof Node\Stmt\Catch_
            || $node instanceof Node\Expr\Ternary
            || $node instanceof Node\Expr\BinaryOp\BooleanAnd
            || $node instanceof Node\Expr\BinaryOp\BooleanOr;
    }
    
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->currentClass = '';
        }
        return null;
    }
}

class SingleFileAnalyzer
{
    private ParserFactory $parserFactory;
    private string $kbRoot;
    
    public function __construct(string $kbRoot)
    {
        $this->parserFactory = new ParserFactory();
        $this->kbRoot = $kbRoot;
    }
    
    public function analyze(string $filePath): array
    {
        $startTime = microtime(true);
        
        echo "🔍 Analyzing: {$filePath}\n";
        
        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }
        
        $code = file_get_contents($filePath);
        $lines = substr_count($code, "\n") + 1;
        
        try {
            $parser = $this->parserFactory->createForNewestSupportedVersion();
            $ast = $parser->parse($code);
            
            if ($ast === null) {
                throw new RuntimeException("Failed to parse file");
            }
            
            $visitor = new FileAnalyzer();
            $traverser = new NodeTraverser();
            $traverser->addVisitor($visitor);
            $traverser->traverse($ast);
            
            $analysis = $visitor->getAnalysis();
            $analysis['lines'] = $lines;
            $analysis['file'] = $filePath;
            $analysis['analyzed_at'] = date('Y-m-d H:i:s');
            $analysis['duration'] = round(microtime(true) - $startTime, 3);
            
            $this->updateIntelligence($filePath, $analysis);
            
            echo "✅ Analysis complete\n";
            echo "   Functions: " . count($analysis['functions']) . "\n";
            echo "   Classes: " . count($analysis['classes']) . "\n";
            echo "   Calls: " . count($analysis['calls']) . "\n";
            echo "   Security issues: " . count($analysis['security_issues']) . "\n";
            echo "   Complexity: " . $analysis['complexity'] . "\n";
            echo "   Duration: " . $analysis['duration'] . "s\n";
            
            return $analysis;
            
        } catch (Error $e) {
            echo "⚠️  Parse error: {$e->getMessage()}\n";
            throw $e;
        }
    }
    
    private function updateIntelligence(string $filePath, array $analysis): void
    {
        // Update incremental intelligence cache
        $cacheDir = $this->kbRoot . '/cache/incremental';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheKey = md5($filePath);
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
        
        file_put_contents($cacheFile, json_encode($analysis, JSON_PRETTY_PRINT));
        
        // Update master index
        $indexFile = $this->kbRoot . '/intelligence/incremental_index.json';
        $index = [];
        
        if (file_exists($indexFile)) {
            $index = json_decode(file_get_contents($indexFile), true) ?? [];
        }
        
        $index[$filePath] = [
            'cache_key' => $cacheKey,
            'last_analyzed' => $analysis['analyzed_at'],
            'functions' => count($analysis['functions']),
            'security_issues' => count($analysis['security_issues']),
        ];
        
        file_put_contents($indexFile, json_encode($index, JSON_PRETTY_PRINT));
        
        echo "💾 Intelligence cache updated\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    if ($argc < 2) {
        echo "Usage: php analyze_single_file.php <file_path>\n";
        exit(1);
    }
    
    $filePath = $argv[1];
    $kbRoot = dirname(__DIR__);
    
    try {
        $analyzer = new SingleFileAnalyzer($kbRoot);
        $result = $analyzer->analyze($filePath);
        exit(0);
    } catch (Exception $e) {
        echo "❌ Error: {$e->getMessage()}\n";
        exit(1);
    }
}
