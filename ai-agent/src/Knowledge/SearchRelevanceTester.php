<?php

/**
 * Knowledge Base Search Relevance Tester
 * Tests search quality using golden queries with expected results
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 - Knowledge Intelligence
 */

declare(strict_types=1);

namespace App\Knowledge;

use App\Logger;

class SearchRelevanceTester
{
    /**
     * Golden queries with expected relevant documents
     * Format: ['query' => 'search term', 'expected' => ['doc1', 'doc2', ...]]
     */
    private const GOLDEN_QUERIES = [
        [
            'query' => 'How do I deploy to Cloudways?',
            'expected_keywords' => ['cloudways', 'deploy', 'deployment', 'server'],
            'expected_files' => ['RUNBOOKS.md', 'NEURO_CORE_INSTALL.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'What are the AI assistant capabilities?',
            'expected_keywords' => ['assistant', 'capabilities', 'ai', 'features'],
            'expected_files' => ['ASSISTANT_CAPABILITIES.md', 'ASSISTANT_ENDPOINTS.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'Database schema for neural network',
            'expected_keywords' => ['schema', 'database', 'neural', 'table'],
            'expected_files' => ['SCHEMA_NOTES.md', 'NEURAL_NETWORK_ROLLOUT.md'],
            'min_similarity' => 0.70
        ],
        [
            'query' => 'API endpoints and routes',
            'expected_keywords' => ['api', 'endpoint', 'route', 'http'],
            'expected_files' => ['ROUTES_ENDPOINTS.md', 'ASSISTANT_ENDPOINTS.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'Known issues and bugs',
            'expected_keywords' => ['issue', 'bug', 'problem', 'fix'],
            'expected_files' => ['KNOWN_ISSUES.md'],
            'min_similarity' => 0.80
        ],
        [
            'query' => 'Component architecture map',
            'expected_keywords' => ['component', 'architecture', 'map', 'structure'],
            'expected_files' => ['COMPONENT_MAP.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'Gamification features',
            'expected_keywords' => ['gamification', 'points', 'rewards', 'badges'],
            'expected_files' => ['GAMIFICATION_OVERVIEW.md'],
            'min_similarity' => 0.80
        ],
        [
            'query' => 'Context and prompts configuration',
            'expected_keywords' => ['context', 'prompt', 'configuration', 'system'],
            'expected_files' => ['PROMPTS_CONTEXT.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'Future roadmap plans',
            'expected_keywords' => ['roadmap', 'future', 'plan', 'upcoming'],
            'expected_files' => ['ASSISTANT_ROADMAP.md'],
            'min_similarity' => 0.75
        ],
        [
            'query' => 'How to use vector embeddings in code?',
            'expected_keywords' => ['vector', 'embedding', 'semantic', 'search'],
            'expected_files' => ['Embeddings.php', 'SophisticatedVectorSearch.php'],
            'min_similarity' => 0.70
        ]
    ];
    
    /**
     * Run all golden query tests
     */
    public static function runTests(bool $verbose = false): array
    {
        $startTime = microtime(true);
        $results = [];
        $totalQueries = count(self::GOLDEN_QUERIES);
        $passedQueries = 0;
        
        foreach (self::GOLDEN_QUERIES as $index => $goldenQuery) {
            $testResult = self::testQuery($goldenQuery, $verbose);
            $results[] = $testResult;
            
            if ($testResult['passed']) {
                $passedQueries++;
            }
            
            if ($verbose) {
                self::outputTestResult($index + 1, $totalQueries, $testResult);
            }
        }
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Calculate metrics
        $top1HitRate = 0;
        $top3HitRate = 0;
        $top5HitRate = 0;
        
        foreach ($results as $result) {
            if ($result['metrics']['top1_hit']) {
                $top1HitRate++;
            }
            if ($result['metrics']['top3_hit']) {
                $top3HitRate++;
            }
            if ($result['metrics']['top5_hit']) {
                $top5HitRate++;
            }
        }
        
        $top1HitRate = ($top1HitRate / $totalQueries) * 100;
        $top3HitRate = ($top3HitRate / $totalQueries) * 100;
        $top5HitRate = ($top5HitRate / $totalQueries) * 100;
        
        $summary = [
            'total_queries' => $totalQueries,
            'passed_queries' => $passedQueries,
            'failed_queries' => $totalQueries - $passedQueries,
            'pass_rate' => round(($passedQueries / $totalQueries) * 100, 2),
            'top1_hit_rate' => round($top1HitRate, 2),
            'top3_hit_rate' => round($top3HitRate, 2),
            'top5_hit_rate' => round($top5HitRate, 2),
            'avg_search_duration_ms' => round(
                array_sum(array_column($results, 'search_duration_ms')) / $totalQueries,
                2
            ),
            'total_duration_ms' => round($duration, 2),
            'timestamp' => time()
        ];
        
        Logger::info('Golden query tests completed', $summary);
        
        return [
            'summary' => $summary,
            'results' => $results,
            'golden_queries' => self::GOLDEN_QUERIES
        ];
    }
    
    /**
     * Test a single golden query
     */
    private static function testQuery(array $goldenQuery, bool $verbose = false): array
    {
        $query = $goldenQuery['query'];
        $expectedKeywords = $goldenQuery['expected_keywords'];
        $expectedFiles = $goldenQuery['expected_files'];
        $minSimilarity = $goldenQuery['min_similarity'];
        
        // Perform search
        $searchResult = KnowledgeBase::search($query, [
            'max_results' => 10,
            'threshold' => 0.6
        ]);
        
        if (!$searchResult['success']) {
            return [
                'query' => $query,
                'passed' => false,
                'error' => $searchResult['error'],
                'search_duration_ms' => 0
            ];
        }
        
        $results = $searchResult['results'];
        $searchDuration = $searchResult['duration_ms'];
        
        // Check if expected files are in top results
        $foundFiles = [];
        $filePositions = [];
        
        foreach ($results as $index => $result) {
            $fileName = $result['metadata']['file_name'] ?? '';
            
            if (in_array($fileName, $expectedFiles)) {
                $foundFiles[] = $fileName;
                $filePositions[$fileName] = $index + 1; // 1-indexed position
            }
        }
        
        // Check keyword matches in top results
        $keywordMatchCount = 0;
        foreach ($results as $result) {
            $content = strtolower($result['content']);
            foreach ($expectedKeywords as $keyword) {
                if (strpos($content, strtolower($keyword)) !== false) {
                    $keywordMatchCount++;
                    break; // Count each result once
                }
            }
        }
        
        // Calculate metrics
        $top1Hit = !empty($results) && in_array($results[0]['metadata']['file_name'] ?? '', $expectedFiles);
        $top3Hit = count(array_intersect($foundFiles, array_slice(array_column($results, 'metadata'), 0, 3))) > 0;
        $top5Hit = count(array_intersect($foundFiles, array_slice(array_column($results, 'metadata'), 0, 5))) > 0;
        
        $avgSimilarity = !empty($results)
            ? array_sum(array_column($results, 'boosted_score')) / count($results)
            : 0;
        
        // Determine if test passed
        $passed = $top3Hit && $avgSimilarity >= $minSimilarity && $keywordMatchCount >= 2;
        
        return [
            'query' => $query,
            'passed' => $passed,
            'expected_files' => $expectedFiles,
            'found_files' => $foundFiles,
            'file_positions' => $filePositions,
            'metrics' => [
                'top1_hit' => $top1Hit,
                'top3_hit' => $top3Hit,
                'top5_hit' => $top5Hit,
                'avg_similarity' => round($avgSimilarity, 4),
                'min_similarity' => $minSimilarity,
                'keyword_matches' => $keywordMatchCount,
                'total_results' => count($results)
            ],
            'search_duration_ms' => round($searchDuration, 2)
        ];
    }
    
    /**
     * Output formatted test result
     */
    private static function outputTestResult(int $index, int $total, array $result): void
    {
        $status = $result['passed'] ? '✓' : '✗';
        $color = $result['passed'] ? "\033[32m" : "\033[31m";
        $reset = "\033[0m";
        
        echo PHP_EOL;
        echo "{$color}[{$index}/{$total}] {$status}{$reset} {$result['query']}" . PHP_EOL;
        
        if (isset($result['error'])) {
            echo "  Error: {$result['error']}" . PHP_EOL;
            return;
        }
        
        echo "  Expected files: " . implode(', ', $result['expected_files']) . PHP_EOL;
        echo "  Found files: " . implode(', ', $result['found_files']) . PHP_EOL;
        
        if (!empty($result['file_positions'])) {
            echo "  Positions: ";
            foreach ($result['file_positions'] as $file => $pos) {
                echo "{$file}=#{$pos} ";
            }
            echo PHP_EOL;
        }
        
        $metrics = $result['metrics'];
        echo "  Metrics: Top-1=" . ($metrics['top1_hit'] ? 'Y' : 'N')
            . " Top-3=" . ($metrics['top3_hit'] ? 'Y' : 'N')
            . " Top-5=" . ($metrics['top5_hit'] ? 'Y' : 'N')
            . " Similarity=" . round($metrics['avg_similarity'], 3)
            . " Keywords={$metrics['keyword_matches']}"
            . " Results={$metrics['total_results']}"
            . " Duration=" . round($result['search_duration_ms'], 2) . "ms"
            . PHP_EOL;
    }
    
    /**
     * Generate detailed report
     */
    public static function generateReport(array $testResults, string $outputPath = null): string
    {
        $summary = $testResults['summary'];
        $results = $testResults['results'];
        
        $report = "# Knowledge Base Search Relevance Report\n\n";
        $report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "## Summary\n\n";
        $report .= "| Metric | Value |\n";
        $report .= "|--------|-------|\n";
        $report .= "| Total Queries | {$summary['total_queries']} |\n";
        $report .= "| Passed | {$summary['passed_queries']} |\n";
        $report .= "| Failed | {$summary['failed_queries']} |\n";
        $report .= "| Pass Rate | {$summary['pass_rate']}% |\n";
        $report .= "| Top-1 Hit Rate | {$summary['top1_hit_rate']}% |\n";
        $report .= "| Top-3 Hit Rate | {$summary['top3_hit_rate']}% |\n";
        $report .= "| Top-5 Hit Rate | {$summary['top5_hit_rate']}% |\n";
        $report .= "| Avg Search Duration | {$summary['avg_search_duration_ms']}ms |\n";
        $report .= "| Total Test Duration | {$summary['total_duration_ms']}ms |\n\n";
        
        $report .= "## Gate Status\n\n";
        $gatePass = $summary['top3_hit_rate'] >= 80;
        $status = $gatePass ? '✅ PASSED' : '❌ FAILED';
        $report .= "**Target:** ≥80% Top-3 Hit Rate\n";
        $report .= "**Actual:** {$summary['top3_hit_rate']}%\n";
        $report .= "**Status:** {$status}\n\n";
        
        $report .= "## Detailed Results\n\n";
        foreach ($results as $index => $result) {
            $num = $index + 1;
            $status = $result['passed'] ? '✅' : '❌';
            
            $report .= "### {$num}. {$status} {$result['query']}\n\n";
            $report .= "- **Expected Files:** " . implode(', ', $result['expected_files']) . "\n";
            $report .= "- **Found Files:** " . implode(', ', $result['found_files']) . "\n";
            
            if (!empty($result['file_positions'])) {
                $report .= "- **Positions:** ";
                foreach ($result['file_positions'] as $file => $pos) {
                    $report .= "{$file} (##{$pos}) ";
                }
                $report .= "\n";
            }
            
            $m = $result['metrics'];
            $report .= "- **Metrics:**\n";
            $report .= "  - Top-1 Hit: " . ($m['top1_hit'] ? 'Yes' : 'No') . "\n";
            $report .= "  - Top-3 Hit: " . ($m['top3_hit'] ? 'Yes' : 'No') . "\n";
            $report .= "  - Top-5 Hit: " . ($m['top5_hit'] ? 'Yes' : 'No') . "\n";
            $report .= "  - Avg Similarity: {$m['avg_similarity']}\n";
            $report .= "  - Keyword Matches: {$m['keyword_matches']}\n";
            $report .= "  - Total Results: {$m['total_results']}\n";
            $report .= "  - Search Duration: {$result['search_duration_ms']}ms\n\n";
        }
        
        if ($outputPath) {
            file_put_contents($outputPath, $report);
        }
        
        return $report;
    }
}
