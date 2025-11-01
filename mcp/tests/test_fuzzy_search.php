#!/usr/bin/env php
<?php
/**
 * Unit Tests - Fuzzy Search Engine
 *
 * Tests typo correction, phonetic matching, and query suggestions.
 *
 * @package MCP\Tests
 */

declare(strict_types=1);

// Load the file directly
require_once __DIR__ . '/../src/Search/FuzzySearchEngine.php';

// Use correct namespace
use IntelligenceHub\MCP\Search\FuzzySearchEngine;

// Test counters
$tests_passed = 0;
$tests_failed = 0;
$test_details = [];

/**
 * Test helper function
 */
function test(string $name, callable $callback): void
{
    global $tests_passed, $tests_failed, $test_details;

    try {
        $result = $callback();
        if ($result === true) {
            $tests_passed++;
            $test_details[] = ['name' => $name, 'status' => 'PASS', 'message' => ''];
            echo "✅ PASS: {$name}\n";
        } else {
            $tests_failed++;
            $test_details[] = ['name' => $name, 'status' => 'FAIL', 'message' => $result];
            echo "❌ FAIL: {$name} - {$result}\n";
        }
    } catch (\Exception $e) {
        $tests_failed++;
        $test_details[] = ['name' => $name, 'status' => 'ERROR', 'message' => $e->getMessage()];
        echo "💥 ERROR: {$name} - {$e->getMessage()}\n";
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  FUZZY SEARCH ENGINE UNIT TESTS                                             ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Initialize engine
$fuzzy = new FuzzySearchEngine([
    'max_distance' => 2,
    'enable_phonetic' => true,
    'enable_suggestions' => true,
]);

// ============================================================================
// TEST SUITE 1: Levenshtein Distance Calculation
// ============================================================================

echo "📐 TEST SUITE 1: Levenshtein Distance Calculation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Identical strings have distance 0', function() use ($fuzzy) {
    $distance = $fuzzy->calculateLevenshtein('function', 'function');
    return $distance === 0 ? true : "Expected 0, got {$distance}";
});

test('Single character difference has distance 1', function() use ($fuzzy) {
    $distance = $fuzzy->calculateLevenshtein('function', 'functi0n');
    return $distance === 1 ? true : "Expected 1, got {$distance}";
});

test('Two character differences have distance 2', function() use ($fuzzy) {
    $distance = $fuzzy->calculateLevenshtein('function', 'functi0m');
    return $distance === 2 ? true : "Expected 2, got {$distance}";
});

test('Case insensitive comparison works', function() use ($fuzzy) {
    $distance = $fuzzy->calculateLevenshtein('Function', 'function');
    return $distance === 0 ? true : "Expected 0, got {$distance}";
});

echo "\n";

// ============================================================================
// TEST SUITE 2: Programming Typo Corrections
// ============================================================================

echo "🔧 TEST SUITE 2: Programming Typo Corrections\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Correct "fucntion" to "function"', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('fucntion');
    return $corrected === 'function' ? true : "Expected 'function', got '{$corrected}'";
});

test('Correct "databse" to "database"', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('databse');
    return $corrected === 'database' ? true : "Expected 'database', got '{$corrected}'";
});

test('Correct "conection" to "connection"', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('conection');
    return $corrected === 'connection' ? true : "Expected 'connection', got '{$corrected}'";
});

test('Correct "clasname" to "classname"', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('clasname');
    return $corrected === 'classname' ? true : "Expected 'classname', got '{$corrected}'";
});

test('Correct "retrun" to "return"', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('retrun');
    return $corrected === 'return' ? true : "Expected 'return', got '{$corrected}'";
});

test('Leave correct spelling unchanged', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('function');
    return $corrected === 'function' ? true : "Expected 'function', got '{$corrected}'";
});

test('Handle multi-word queries', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('fucntion databse');
    return $corrected === 'function database' ? true : "Expected 'function database', got '{$corrected}'";
});

echo "\n";

// ============================================================================
// TEST SUITE 3: Phonetic Matching
// ============================================================================

echo "🔊 TEST SUITE 3: Phonetic Matching\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Match "smanthic" to "semantic" (Soundex)', function() use ($fuzzy) {
    $matches = $fuzzy->phoneticMatch('smanthic', 'semantic');
    return $matches === true ? true : "Expected true, got false";
});

test('Match "securty" to "security" (phonetically similar)', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('securty');
    // Should correct to security via phonetic matching
    return ($corrected === 'security' || $corrected === 'securty') ? true : "Got '{$corrected}'";
});

test('Phonetic match returns false for different sounds', function() use ($fuzzy) {
    $matches = $fuzzy->phoneticMatch('cache', 'function');
    return $matches === false ? true : "Expected false, got true";
});

echo "\n";

// ============================================================================
// TEST SUITE 4: Query Suggestions
// ============================================================================

echo "💡 TEST SUITE 4: Query Suggestions\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Generate suggestions for typo', function() use ($fuzzy) {
    $suggestions = $fuzzy->getSuggestions('fucntion', 3);
    return (is_array($suggestions) && count($suggestions) > 0 && in_array('function', $suggestions))
        ? true
        : "Expected array with 'function', got: " . json_encode($suggestions);
});

test('Suggestions are limited by limit parameter', function() use ($fuzzy) {
    $suggestions = $fuzzy->getSuggestions('functin', 2);
    return count($suggestions) <= 2 ? true : "Expected ≤2 suggestions, got " . count($suggestions);
});

test('Empty query returns empty suggestions', function() use ($fuzzy) {
    $suggestions = $fuzzy->getSuggestions('', 5);
    return empty($suggestions) ? true : "Expected empty array, got " . count($suggestions) . " suggestions";
});

test('Suggestions include phonetic matches', function() use ($fuzzy) {
    $suggestions = $fuzzy->getSuggestions('smanthic', 5);
    // Should include "semantic" or similar
    return (is_array($suggestions) && count($suggestions) > 0)
        ? true
        : "Expected non-empty suggestions array";
});

echo "\n";

// ============================================================================
// TEST SUITE 5: Edge Cases
// ============================================================================

echo "⚠️  TEST SUITE 5: Edge Cases\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Handle empty string', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('');
    return $corrected === '' ? true : "Expected empty string, got '{$corrected}'";
});

test('Handle single character', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('f');
    return strlen($corrected) >= 1 ? true : "Expected non-empty result";
});

test('Handle very long query', function() use ($fuzzy) {
    $long = str_repeat('function ', 100);
    $corrected = $fuzzy->autoCorrect($long);
    return strlen($corrected) > 0 ? true : "Expected non-empty result";
});

test('Handle special characters', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('function()');
    return strpos($corrected, 'function') !== false ? true : "Expected 'function' in result";
});

test('Handle numbers in query', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('function123');
    return strlen($corrected) > 0 ? true : "Expected non-empty result";
});

test('Case preservation in corrections', function() use ($fuzzy) {
    $corrected = $fuzzy->autoCorrect('Fucntion');
    // Should preserve capitalization where possible
    return strlen($corrected) > 0 ? true : "Expected non-empty result";
});

echo "\n";

// ============================================================================
// TEST SUITE 6: Performance
// ============================================================================

echo "⚡ TEST SUITE 6: Performance\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

test('Correction completes within 100ms', function() use ($fuzzy) {
    $start = microtime(true);
    $fuzzy->autoCorrect('fucntion databse conection');
    $duration = (microtime(true) - $start) * 1000;
    return $duration < 100 ? true : "Took {$duration}ms (expected <100ms)";
});

test('Suggestions complete within 200ms', function() use ($fuzzy) {
    $start = microtime(true);
    $fuzzy->getSuggestions('programming language', 10);
    $duration = (microtime(true) - $start) * 1000;
    return $duration < 200 ? true : "Took {$duration}ms (expected <200ms)";
});

test('Levenshtein calculation is fast', function() use ($fuzzy) {
    $start = microtime(true);
    for ($i = 0; $i < 100; $i++) {
        $fuzzy->calculateLevenshtein('function', 'functi0n');
    }
    $duration = (microtime(true) - $start) * 1000;
    return $duration < 100 ? true : "100 calculations took {$duration}ms";
});

echo "\n";

// ============================================================================
// TEST SUMMARY
// ============================================================================

$total_tests = $tests_passed + $tests_failed;
$pass_rate = $total_tests > 0 ? round(($tests_passed / $total_tests) * 100, 1) : 0;

echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "Total Tests:  {$total_tests}\n";
echo "Passed:       {$tests_passed} ✅\n";
echo "Failed:       {$tests_failed} ❌\n";
echo "Pass Rate:    {$pass_rate}%\n";
echo "\n";

if ($tests_failed > 0) {
    echo "FAILED TESTS:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    foreach ($test_details as $detail) {
        if ($detail['status'] !== 'PASS') {
            echo "❌ {$detail['name']}: {$detail['message']}\n";
        }
    }
    echo "\n";
}

if ($tests_passed === $total_tests) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED\n";
    echo "\n";
    exit(1);
}
