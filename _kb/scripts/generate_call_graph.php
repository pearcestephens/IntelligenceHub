#!/usr/bin/env php
<?php
/**
 * Call Graph Generator
 * 
 * Uses PHP-Parser to build function call graph
 * Shows which functions call which other functions
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

class CallGraphVisitor extends NodeVisitorAbstract
{
    private array $callGraph = [];
    private string $currentFile = '';
    private ?string $currentFunction = null;
    private ?string $currentClass = null;
    
    public function setCurrentFile(string $file): void
    {
        $this->currentFile = $file;
    }
    
    public function getCallGraph(): array
    {
        return $this->callGraph;
    }
    
    public function enterNode(Node $node)
    {
        // Track current class
        if ($node instanceof Node\Stmt\Class_) {
            $this->currentClass = $node->name ? $node->name->toString() : null;
        }
        
        // Track current function/method
        if ($node instanceof Node\Stmt\Function_) {
            $this->currentFunction = $node->name->toString();
            $this->initFunction($this->currentFunction);
        } elseif ($node instanceof Node\Stmt\ClassMethod) {
            $methodName = $node->name->toString();
            $this->currentFunction = $this->currentClass 
                ? "{$this->currentClass}::{$methodName}" 
                : $methodName;
            $this->initFunction($this->currentFunction);
        }
        
        // Track function calls
        if ($node instanceof Node\Expr\FuncCall && $this->currentFunction) {
            if ($node->name instanceof Node\Name) {
                $calledFunction = $node->name->toString();
                $this->addCall($this->currentFunction, $calledFunction);
            }
        }
        
        // Track method calls
        if ($node instanceof Node\Expr\MethodCall && $this->currentFunction) {
            if ($node->name instanceof Node\Identifier) {
                $methodName = $node->name->toString();
                
                // Try to determine the class if possible
                $className = $this->inferClassName($node->var);
                $calledMethod = $className ? "{$className}::{$methodName}" : $methodName;
                
                $this->addCall($this->currentFunction, $calledMethod);
            }
        }
        
        // Track static method calls
        if ($node instanceof Node\Expr\StaticCall && $this->currentFunction) {
            if ($node->class instanceof Node\Name && $node->name instanceof Node\Identifier) {
                $className = $node->class->toString();
                $methodName = $node->name->toString();
                $calledMethod = "{$className}::{$methodName}";
                $this->addCall($this->currentFunction, $calledMethod);
            }
        }
        
        return null;
    }
    
    public function leaveNode(Node $node)
    {
        // Reset current function when leaving
        if ($node instanceof Node\Stmt\Function_ || $node instanceof Node\Stmt\ClassMethod) {
            $this->currentFunction = null;
        }
        
        // Reset current class when leaving
        if ($node instanceof Node\Stmt\Class_) {
            $this->currentClass = null;
        }
        
        return null;
    }
    
    private function initFunction(string $function): void
    {
        if (!isset($this->callGraph[$function])) {
            $this->callGraph[$function] = [
                'file' => $this->currentFile,
                'calls' => [],
                'called_by' => [],
            ];
        }
    }
    
    private function addCall(string $caller, string $callee): void
    {
        // Add to caller's calls list
        if (!in_array($callee, $this->callGraph[$caller]['calls'])) {
            $this->callGraph[$caller]['calls'][] = $callee;
        }
        
        // Initialize callee if not exists
        if (!isset($this->callGraph[$callee])) {
            $this->callGraph[$callee] = [
                'file' => '(unknown)',
                'calls' => [],
                'called_by' => [],
            ];
        }
        
        // Add to callee's called_by list
        if (!in_array($caller, $this->callGraph[$callee]['called_by'])) {
            $this->callGraph[$callee]['called_by'][] = $caller;
        }
    }
    
    private function inferClassName(Node $node): ?string
    {
        // Try to infer class name from variable
        if ($node instanceof Node\Expr\Variable && is_string($node->name)) {
            // Could potentially track variable assignments to infer types
            // For now, return null
            return null;
        }
        
        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            return $node->class->toString();
        }
        
        return null;
    }
}

class CallGraphGenerator
{
    private ParserFactory $parserFactory;
    private NodeTraverser $traverser;
    private CallGraphVisitor $visitor;
    private array $stats = [
        'files_processed' => 0,
        'functions_found' => 0,
        'calls_found' => 0,
    ];
    
    public function __construct()
    {
        $this->parserFactory = new ParserFactory();
        $this->traverser = new NodeTraverser();
        $this->visitor = new CallGraphVisitor();
        $this->traverser->addVisitor($this->visitor);
    }
    
    public function generateFromDirectory(string $directory, array $excludePatterns = []): array
    {
        $startTime = microtime(true);
        
        echo "📊 Generating call graph from: {$directory}\n\n";
        
        $files = $this->getPhpFiles($directory, $excludePatterns);
        $total = count($files);
        
        echo "📁 Found {$total} PHP files to process\n\n";
        
        foreach ($files as $i => $file) {
            $progress = round(($i + 1) / $total * 100);
            echo "\r[{$progress}%] Processing: " . basename($file) . str_repeat(' ', 50);
            
            $this->processFile($file);
        }
        
        echo "\n\n";
        
        $callGraph = $this->visitor->getCallGraph();
        $this->stats['functions_found'] = count($callGraph);
        
        // Count total calls
        foreach ($callGraph as $data) {
            $this->stats['calls_found'] += count($data['calls']);
        }
        
        $duration = round(microtime(true) - $startTime, 2);
        
        echo "✅ Call graph generated in {$duration}s\n";
        echo "📊 Statistics:\n";
        echo "   - Files processed: {$this->stats['files_processed']}\n";
        echo "   - Functions found: {$this->stats['functions_found']}\n";
        echo "   - Function calls: {$this->stats['calls_found']}\n\n";
        
        return $callGraph;
    }
    
    private function processFile(string $file): void
    {
        $this->stats['files_processed']++;
        
        try {
            $code = file_get_contents($file);
            $parser = $this->parserFactory->createForNewestSupportedVersion();
            
            $ast = $parser->parse($code);
            
            if ($ast === null) {
                return;
            }
            
            $this->visitor->setCurrentFile($file);
            $this->traverser->traverse($ast);
            
        } catch (Error $e) {
            echo "\n⚠️  Parse error in {$file}: {$e->getMessage()}\n";
        }
    }
    
    private function getPhpFiles(string $directory, array $excludePatterns): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = $file->getPathname();
                
                // Check exclusions
                $excluded = false;
                foreach ($excludePatterns as $pattern) {
                    if (strpos($path, $pattern) !== false) {
                        $excluded = true;
                        break;
                    }
                }
                
                if (!$excluded) {
                    $files[] = $path;
                }
            }
        }
        
        return $files;
    }
    
    public function saveToJson(array $callGraph, string $outputFile): void
    {
        file_put_contents($outputFile, json_encode($callGraph, JSON_PRETTY_PRINT));
        echo "💾 Call graph saved to: {$outputFile}\n";
    }
    
    public function generateMarkdownReport(array $callGraph, string $outputFile): void
    {
        $report = "# Function Call Graph\n\n";
        $report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n";
        $report .= "**Total Functions:** " . count($callGraph) . "\n\n";
        $report .= "---\n\n";
        
        // Find most called functions
        $callCounts = [];
        foreach ($callGraph as $func => $data) {
            $callCounts[$func] = count($data['called_by']);
        }
        arsort($callCounts);
        
        $report .= "## Most Called Functions\n\n";
        $report .= "| Function | Called By (count) |\n";
        $report .= "|----------|------------------|\n";
        
        $top = array_slice($callCounts, 0, 20, true);
        foreach ($top as $func => $count) {
            $report .= "| `{$func}` | {$count} |\n";
        }
        $report .= "\n---\n\n";
        
        // Find functions that call many others
        $callsMade = [];
        foreach ($callGraph as $func => $data) {
            $callsMade[$func] = count($data['calls']);
        }
        arsort($callsMade);
        
        $report .= "## Functions Making Most Calls\n\n";
        $report .= "| Function | Calls (count) |\n";
        $report .= "|----------|---------------|\n";
        
        $top = array_slice($callsMade, 0, 20, true);
        foreach ($top as $func => $count) {
            $report .= "| `{$func}` | {$count} |\n";
        }
        $report .= "\n---\n\n";
        
        // Detailed listing
        $report .= "## Full Call Graph\n\n";
        
        foreach ($callGraph as $func => $data) {
            $report .= "### `{$func}`\n\n";
            $report .= "**File:** `{$data['file']}`\n\n";
            
            if (!empty($data['calls'])) {
                $report .= "**Calls:**\n";
                foreach ($data['calls'] as $call) {
                    $report .= "- `{$call}`\n";
                }
                $report .= "\n";
            }
            
            if (!empty($data['called_by'])) {
                $report .= "**Called by:**\n";
                foreach ($data['called_by'] as $caller) {
                    $report .= "- `{$caller}`\n";
                }
                $report .= "\n";
            }
            
            $report .= "---\n\n";
        }
        
        file_put_contents($outputFile, $report);
        echo "📝 Markdown report saved to: {$outputFile}\n";
    }
}

// Main execution
if (php_sapi_name() === 'cli') {
    $options = getopt('d:o:m::', ['dir:', 'output:', 'markdown::']);
    
    $directory = $options['d'] ?? $options['dir'] ?? getcwd();
    $outputFile = $options['o'] ?? $options['output'] ?? __DIR__ . '/../intelligence/call_graph.json';
    $markdownFile = $options['m'] ?? $options['markdown'] ?? __DIR__ . '/../intelligence/CALL_GRAPH.md';
    
    $exclude = [
        'vendor',
        'node_modules',
        '.git',
        'cache',
        'logs',
        'tmp',
        'backup',
        'test',
    ];
    
    $generator = new CallGraphGenerator();
    $callGraph = $generator->generateFromDirectory($directory, $exclude);
    
    if (!empty($callGraph)) {
        $generator->saveToJson($callGraph, $outputFile);
        $generator->generateMarkdownReport($callGraph, $markdownFile);
    } else {
        echo "⚠️  No functions found to graph\n";
    }
    
    exit(0);
}
