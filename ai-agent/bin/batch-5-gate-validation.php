#!/usr/bin/env php
<?php

/**
 * BATCH-5 Gate Validation Script
 * Validates KB-01 and KB-02 implementation against acceptance criteria
 *
 * Gate Requirements:
 * 1. Show cron dry-run log
 * 2. Show kb_index_docs_total metric
 * 3. Show ≥80% top-3 hit-rate on golden queries
 *
 * @author Pearce Stephens - Ecigdis Limited
 */

declare(strict_types=1);

// Bootstrap application
require_once dirname(__DIR__) . '/autoload.php';

use App\Knowledge\KnowledgeBase;
use App\Knowledge\SearchRelevanceTester;

// Colors
$green = "\033[32m";
$blue = "\033[34m";
$yellow = "\033[33m";
$red = "\033[31m";
$reset = "\033[0m";

function section(string $title): void
{
    echo PHP_EOL;
    echo str_repeat('━', 100) . PHP_EOL;
    echo "  {$title}" . PHP_EOL;
    echo str_repeat('━', 100) . PHP_EOL;
    echo PHP_EOL;
}

function checkmark(bool $pass): string
{
    global $green, $red, $reset;
    return $pass ? "{$green}✓{$reset}" : "{$red}✗{$reset}";
}

// Start
section('🔒 BATCH-5 GATE VALIDATION');

echo "Date: " . date('Y-m-d H:i:s') . PHP_EOL;
echo "Location: /home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent" . PHP_EOL;
echo PHP_EOL;

$gateResults = [
    'gate1' => false,
    'gate2' => false,
    'gate3' => false
];

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// GATE 1: Cron Dry-Run Log
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

section('GATE 1: Cron Dry-Run Log');

echo "Requirement: Show cron dry-run log with discovered files" . PHP_EOL;
echo PHP_EOL;

echo "{$blue}Running:{$reset} ./bin/kb-indexer-cron.php --dry-run" . PHP_EOL;
echo PHP_EOL;

// Execute dry-run
$dryRunOutput = [];
$dryRunReturnCode = 0;
exec('php ' . __DIR__ . '/kb-indexer-cron.php --dry-run 2>&1', $dryRunOutput, $dryRunReturnCode);

// Display output
foreach ($dryRunOutput as $line) {
    echo $line . PHP_EOL;
}

// Validate dry-run output
$filesFound = 0;
foreach ($dryRunOutput as $line) {
    if (strpos($line, 'Total files:') !== false) {
        preg_match('/Total files: (\d+)/', $line, $matches);
        if (isset($matches[1])) {
            $filesFound += (int)$matches[1];
        }
    }
}

$gate1Pass = $filesFound > 0 && $dryRunReturnCode === 0;
$gateResults['gate1'] = $gate1Pass;

echo PHP_EOL;
echo checkmark($gate1Pass) . " Gate 1 Status: " . ($gate1Pass ? "{$green}PASSED{$reset}" : "{$red}FAILED{$reset}") . PHP_EOL;
echo "   Files discovered: {$filesFound}" . PHP_EOL;

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// GATE 2: kb_index_docs_total Metric
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

section('GATE 2: kb_index_docs_total Metric');

echo "Requirement: Show kb_index_docs_total metric is tracked and visible" . PHP_EOL;
echo PHP_EOL;

// Check if KB has been indexed
$metrics = KnowledgeBase::getMetrics();

echo "{$blue}Current Knowledge Base Metrics:{$reset}" . PHP_EOL;
echo PHP_EOL;

if (isset($metrics['index']) && !empty($metrics['index'])) {
    $indexMetrics = $metrics['index'];
    
    echo "  kb_index_docs_total:       " . ($indexMetrics['kb_index_docs_total'] ?? 0) . PHP_EOL;
    echo "  kb_index_chunks_total:     " . ($indexMetrics['kb_index_chunks_total'] ?? 0) . PHP_EOL;
    echo "  kb_embeddings_total:       " . ($indexMetrics['kb_embeddings_total'] ?? 0) . PHP_EOL;
    echo "  kb_index_characters_total: " . number_format($indexMetrics['kb_index_characters_total'] ?? 0) . PHP_EOL;
    echo "  kb_index_duration_total_ms: " . round($indexMetrics['kb_index_duration_total_ms'] ?? 0, 2) . "ms" . PHP_EOL;
    echo "  last_updated:              " . date('Y-m-d H:i:s', $indexMetrics['last_updated'] ?? 0) . PHP_EOL;
    
    $gate2Pass = isset($indexMetrics['kb_index_docs_total']);
    $gateResults['gate2'] = $gate2Pass;
} else {
    echo "  {$yellow}⚠ No metrics found. KB may not be indexed yet.{$reset}" . PHP_EOL;
    echo "  {$yellow}→ Run: ./bin/kb-indexer-cron.php (without --dry-run){$reset}" . PHP_EOL;
    
    $gate2Pass = false;
    $gateResults['gate2'] = false;
}

echo PHP_EOL;
echo checkmark($gate2Pass) . " Gate 2 Status: " . ($gate2Pass ? "{$green}PASSED{$reset}" : "{$yellow}PENDING{$reset}") . PHP_EOL;
echo "   Metric exists: " . ($gate2Pass ? 'Yes' : 'No (run indexer first)') . PHP_EOL;

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// GATE 3: ≥80% Top-3 Hit Rate
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

section('GATE 3: Search Relevance (≥80% Top-3 Hit Rate)');

echo "Requirement: Achieve ≥80% top-3 hit-rate on golden queries" . PHP_EOL;
echo PHP_EOL;

if (!$gate2Pass) {
    echo "{$yellow}⚠ Skipping Gate 3: Knowledge base not indexed yet{$reset}" . PHP_EOL;
    echo "{$yellow}→ Run: ./bin/kb-indexer-cron.php{$reset}" . PHP_EOL;
    echo "{$yellow}→ Then: ./bin/kb-test-search.php{$reset}" . PHP_EOL;
    
    $gateResults['gate3'] = false;
} else {
    echo "{$blue}Running:{$reset} Golden Query Tests" . PHP_EOL;
    echo PHP_EOL;
    
    // Run tests
    $testResults = SearchRelevanceTester::runTests(false);
    $summary = $testResults['summary'];
    
    // Display summary
    echo "Test Results:" . PHP_EOL;
    echo "  Total Queries:       {$summary['total_queries']}" . PHP_EOL;
    echo "  Passed:              {$summary['passed_queries']}" . PHP_EOL;
    echo "  Failed:              {$summary['failed_queries']}" . PHP_EOL;
    echo "  Pass Rate:           {$summary['pass_rate']}%" . PHP_EOL;
    echo PHP_EOL;
    echo "Hit Rates:" . PHP_EOL;
    echo "  Top-1 Hit Rate:      {$summary['top1_hit_rate']}%" . PHP_EOL;
    echo "  Top-3 Hit Rate:      {$summary['top3_hit_rate']}%" . PHP_EOL;
    echo "  Top-5 Hit Rate:      {$summary['top5_hit_rate']}%" . PHP_EOL;
    echo PHP_EOL;
    echo "Performance:" . PHP_EOL;
    echo "  Avg Search Duration: {$summary['avg_search_duration_ms']}ms" . PHP_EOL;
    echo "  Total Duration:      {$summary['total_duration_ms']}ms" . PHP_EOL;
    echo PHP_EOL;
    
    $gate3Pass = $summary['top3_hit_rate'] >= 80.0;
    $gateResults['gate3'] = $gate3Pass;
    
    echo checkmark($gate3Pass) . " Gate 3 Status: " . ($gate3Pass ? "{$green}PASSED{$reset}" : "{$red}FAILED{$reset}") . PHP_EOL;
    echo "   Target:  ≥80.0%" . PHP_EOL;
    echo "   Actual:  {$summary['top3_hit_rate']}%" . PHP_EOL;
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Final Summary
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

section('🎯 BATCH-5 GATE SUMMARY');

echo "Gate 1 (Cron Dry-Run):        " . checkmark($gateResults['gate1']) . ($gateResults['gate1'] ? " PASSED" : " FAILED") . PHP_EOL;
echo "Gate 2 (Metrics Tracked):     " . checkmark($gateResults['gate2']) . ($gateResults['gate2'] ? " PASSED" : " PENDING") . PHP_EOL;
echo "Gate 3 (Search Relevance):    " . checkmark($gateResults['gate3']) . ($gateResults['gate3'] ? " PASSED" : " PENDING") . PHP_EOL;
echo PHP_EOL;

$allGatesPassed = $gateResults['gate1'] && $gateResults['gate2'] && $gateResults['gate3'];

if ($allGatesPassed) {
    echo "{$green}✓✓✓ ALL GATES PASSED ✓✓✓{$reset}" . PHP_EOL;
    echo PHP_EOL;
    echo "BATCH-5 (Knowledge Base) implementation is complete and validated." . PHP_EOL;
    $exitCode = 0;
} elseif ($gateResults['gate1'] && !$gateResults['gate2']) {
    echo "{$yellow}⚠ GATES PARTIALLY PASSED ⚠{$reset}" . PHP_EOL;
    echo PHP_EOL;
    echo "Next steps:" . PHP_EOL;
    echo "  1. Run indexer: ./bin/kb-indexer-cron.php --verbose" . PHP_EOL;
    echo "  2. Re-run validation: ./bin/batch-5-gate-validation.php" . PHP_EOL;
    $exitCode = 2;
} else {
    echo "{$red}✗ GATES FAILED ✗{$reset}" . PHP_EOL;
    echo PHP_EOL;
    echo "Review the output above for failure details." . PHP_EOL;
    $exitCode = 1;
}

echo PHP_EOL;
section('Complete');

exit($exitCode);
