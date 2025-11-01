<?php
/**
 * PHPIndexer Test Suite
 *
 * Tests the PHP file indexing system
 *
 * Usage: php tests/php_indexer_test.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use IntelligenceHub\MCP\Indexing\PHPIndexer;
use IntelligenceHub\MCP\Database\Connection;

// Test configuration
$testDir = __DIR__ . '/fixtures';
$testFile = $testDir . '/sample.php';

// Create test fixtures directory if it doesn't exist
if (!is_dir($testDir)) {
    mkdir($testDir, 0755, true);
}

// Create sample test file
file_put_contents($testFile, <<<'PHP'
<?php
/**
 * Sample Test File
 *
 * This file is used for testing the PHPIndexer
 *
 * @package Test
 */

declare(strict_types=1);

/**
 * Calculate total price
 *
 * @param float $price Base price
 * @param float $tax Tax rate
 * @return float Total with tax
 */
function calculateTotal(float $price, float $tax): float
{
    if ($price < 0) {
        throw new InvalidArgumentException("Price cannot be negative");
    }

    return $price * (1 + $tax);
}

/**
 * Sample class for testing
 */
class SampleClass
{
    private $db;

    public function __construct()
    {
        // Initialize database
    }

    /**
     * Get user by ID
     *
     * @param int $userId User ID
     * @return array|null User data
     */
    public function getUserById(int $userId): ?array
    {
        // SQL query for testing
        $sql = "SELECT * FROM users WHERE user_id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Save user
     *
     * @param array $data User data
     * @return bool Success
     */
    public function saveUser(array $data): bool
    {
        // Validate input
        if (empty($data['email'])) {
            return false;
        }

        $sql = "INSERT INTO users (email, name) VALUES (?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$data['email'], $data['name']]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

// API endpoint pattern
// POST /api/users
PHP
);

echo "\n=== PHPIndexer Test Suite ===\n\n";

$passed = 0;
$failed = 0;

// TEST 1: Initialize PHPIndexer
echo "TEST 1: Initialize PHPIndexer\n";
try {
    $indexer = new PHPIndexer();
    echo "✅ PASSED: PHPIndexer initialized successfully\n\n";
    $passed++;
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
    exit(1);
}

// TEST 2: Index single test file
echo "TEST 2: Index Single Test File\n";
try {
    $result = $indexer->indexFile($testFile);

    if ($result) {
        echo "✅ PASSED: Test file indexed successfully\n";
        echo "   File: $testFile\n\n";
        $passed++;
    } else {
        echo "❌ FAILED: Could not index test file\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 3: Verify database entry created
echo "TEST 3: Verify Database Entry Created\n";
try {
    $db = Connection::getInstance();

    $stmt = $db->prepare("
        SELECT ic.*, ict.content_text, ict.extracted_keywords, ict.semantic_tags
        FROM intelligence_content ic
        LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
        WHERE ic.content_path = ?
        LIMIT 1
    ");
    $stmt->execute([$testFile]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo "✅ PASSED: Database entry found\n";
        echo "   Content ID: {$row['content_id']}\n";
        echo "   File: {$row['content_name']}\n";
        echo "   Complexity: {$row['complexity_score']}\n";
        echo "   Quality: {$row['quality_score']}\n";
        echo "   Keywords: " . substr($row['extracted_keywords'] ?? '', 0, 80) . "...\n";
        echo "   Tags: {$row['semantic_tags']}\n\n";
        $passed++;
    } else {
        echo "❌ FAILED: No database entry found for test file\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 4: Test directory indexing
echo "TEST 4: Index Directory (test fixtures)\n";
try {
    $stats = $indexer->indexDirectory($testDir, [
        'unit_id' => 99, // Test unit ID
        'exclude' => ['vendor', 'node_modules']
    ]);

    echo "✅ PASSED: Directory indexed successfully\n";
    echo "   Files processed: {$stats['files_processed']}\n";
    echo "   Files skipped: {$stats['files_skipped']}\n";
    echo "   Functions found: {$stats['functions_found']}\n";
    echo "   Classes found: {$stats['classes_found']}\n";
    echo "   Methods found: {$stats['methods_found']}\n";
    echo "   Lines indexed: {$stats['lines_indexed']}\n";

    if (!empty($stats['errors'])) {
        echo "   Errors: " . count($stats['errors']) . "\n";
        foreach ($stats['errors'] as $error) {
            echo "     - $error\n";
        }
    }
    echo "\n";

    if ($stats['files_processed'] > 0) {
        $passed++;
    } else {
        echo "⚠️  WARNING: No files were processed\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 5: Verify code element extraction
echo "TEST 5: Verify Code Element Extraction\n";
try {
    $db = Connection::getInstance();

    $stmt = $db->prepare("
        SELECT ict.content_text
        FROM intelligence_content ic
        JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
        WHERE ic.content_path = ?
        LIMIT 1
    ");
    $stmt->execute([$testFile]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['content_text']) {
        $content = $row['content_text'];

        // Check for expected elements
        $checks = [
            'calculateTotal function' => strpos($content, 'calculateTotal') !== false,
            'SampleClass class' => strpos($content, 'SampleClass') !== false,
            'getUserById method' => strpos($content, 'getUserById') !== false,
            'SQL query' => strpos($content, 'SELECT * FROM users') !== false,
            'Docblocks' => strpos($content, 'Calculate total price') !== false,
        ];

        $allPassed = true;
        foreach ($checks as $item => $result) {
            if ($result) {
                echo "   ✓ Found: $item\n";
            } else {
                echo "   ✗ Missing: $item\n";
                $allPassed = false;
            }
        }

        if ($allPassed) {
            echo "✅ PASSED: All code elements extracted\n\n";
            $passed++;
        } else {
            echo "❌ FAILED: Some code elements missing\n\n";
            $failed++;
        }
    } else {
        echo "❌ FAILED: No content text found\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// TEST 6: Test quality score calculation
echo "TEST 6: Verify Quality Score Calculation\n";
try {
    $db = Connection::getInstance();

    $stmt = $db->prepare("
        SELECT quality_score, complexity_score
        FROM intelligence_content
        WHERE content_path = ?
        LIMIT 1
    ");
    $stmt->execute([$testFile]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $quality = (float)$row['quality_score'];
        $complexity = (int)$row['complexity_score'];

        echo "   Quality Score: $quality/100\n";
        echo "   Complexity Score: $complexity\n";

        // Quality should be reasonable (file has docblocks, type hints, error handling)
        if ($quality >= 40 && $quality <= 100) {
            echo "✅ PASSED: Quality score in reasonable range (40-100)\n";
            $passed++;
        } else {
            echo "❌ FAILED: Quality score out of expected range: $quality\n";
            $failed++;
        }

        // Complexity should be calculated (has if statements, try/catch)
        if ($complexity > 0) {
            echo "   ✓ Complexity calculated: $complexity\n\n";
        } else {
            echo "   ⚠️  WARNING: Complexity is 0\n\n";
        }
    } else {
        echo "❌ FAILED: Could not retrieve scores\n\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Clean up test files (optional - comment out to inspect)
echo "Cleaning up test fixtures...\n";
@unlink($testFile);
@rmdir($testDir);
echo "✓ Test fixtures removed\n\n";

// Summary
echo "=== Test Summary ===\n";
echo "Total tests: " . ($passed + $failed) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
$successRate = ($passed + $failed) > 0 ? round(($passed / ($passed + $failed)) * 100) : 0;
echo "Success rate: {$successRate}%\n\n";

if ($failed === 0) {
    echo "🎉 ALL PHPINDEXER TESTS PASSED!\n\n";
    exit(0);
} else {
    echo "⚠️  SOME TESTS FAILED - Review output above\n\n";
    exit(1);
}
