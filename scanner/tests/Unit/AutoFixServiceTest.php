<?php

declare(strict_types=1);

namespace Scanner\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Scanner\Lib\AutoFixService;
use Scanner\Lib\AIAssistant;
use PDO;
use PDOStatement;
use InvalidArgumentException;
use RuntimeException;

/**
 * Unit Tests for AutoFixService
 *
 * @package Scanner\Tests\Unit
 * @covers Scanner\Lib\AutoFixService
 */
class AutoFixServiceTest extends TestCase
{
    private PDO $mockDb;
    private AIAssistant $mockAI;
    private string $testBackupDir;
    private AutoFixService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock database
        $this->mockDb = $this->createMock(PDO::class);

        // Create mock AI assistant
        $this->mockAI = $this->createMock(AIAssistant::class);

        // Create temporary backup directory
        $this->testBackupDir = sys_get_temp_dir() . '/scanner_test_backups_' . uniqid();
        mkdir($this->testBackupDir, 0755, true);

        // Create service instance
        $this->service = new AutoFixService(
            $this->mockDb,
            $this->mockAI,
            $this->testBackupDir
        );
    }

    protected function tearDown(): void
    {
        // Clean up test backup directory
        if (is_dir($this->testBackupDir)) {
            array_map('unlink', glob("{$this->testBackupDir}/*"));
            rmdir($this->testBackupDir);
        }

        parent::tearDown();
    }

    /**
     * @test
     * @covers AutoFixService::__construct
     */
    public function testConstructorCreatesBackupDirectory(): void
    {
        $newDir = sys_get_temp_dir() . '/scanner_test_' . uniqid();

        $service = new AutoFixService($this->mockDb, $this->mockAI, $newDir);

        $this->assertDirectoryExists($newDir);

        // Cleanup
        rmdir($newDir);
    }

    /**
     * @test
     * @covers AutoFixService::__construct
     */
    public function testConstructorThrowsExceptionForInvalidDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create backup directory');

        // Try to create directory in read-only location
        new AutoFixService($this->mockDb, $this->mockAI, '/invalid/path/that/cannot/exist');
    }

    /**
     * @test
     * @covers AutoFixService::generateFixPreview
     */
    public function testGenerateFixPreviewReturnsValidStructure(): void
    {
        // Create test file
        $testFile = $this->testBackupDir . '/test.php';
        file_put_contents($testFile, "<?php\n\$query = \"SELECT * FROM users WHERE id = \$_GET[id]\";\n");

        // Mock violation data
        $violationData = [
            'id' => 1,
            'rule_id' => 'SEC001',
            'severity' => 'critical',
            'message' => 'SQL Injection vulnerability',
            'line_number' => 2,
            'file_path' => $testFile,
            'file_type' => 'php'
        ];

        // Mock database query
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn($violationData);
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        // Mock AI response
        $this->mockAI->method('generateFix')->willReturn([
            'success' => true,
            'original_code' => '$query = "SELECT * FROM users WHERE id = $_GET[id]";',
            'fixed_code' => '$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");' . "\n" .
                           '$stmt->execute([$_GET["id"]]);',
            'explanation' => 'Use prepared statements to prevent SQL injection',
            'confidence' => 0.95
        ]);

        $result = $this->service->generateFixPreview(1);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('violation', $result);
        $this->assertArrayHasKey('original_code', $result);
        $this->assertArrayHasKey('fixed_code', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('is_safe', $result);
        $this->assertArrayHasKey('can_auto_apply', $result);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['can_auto_apply']);
    }

    /**
     * @test
     * @covers AutoFixService::generateFixPreview
     */
    public function testGenerateFixPreviewThrowsExceptionForInvalidViolation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Violation 999 not found');

        // Mock empty result
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false);
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        $this->service->generateFixPreview(999);
    }

    /**
     * @test
     * @covers AutoFixService::applyAutoFix
     */
    public function testApplyAutoFixCreatesBackup(): void
    {
        // Create test file
        $testFile = $this->testBackupDir . '/test_apply.php';
        $originalContent = "<?php\n\$query = \"SELECT * FROM users\";\n";
        file_put_contents($testFile, $originalContent);

        // Mock violation data
        $violationData = [
            'id' => 1,
            'rule_id' => 'SEC001',
            'severity' => 'critical',
            'message' => 'SQL Injection',
            'line_number' => 2,
            'file_path' => $testFile,
            'file_type' => 'php'
        ];

        // Mock database
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn($violationData);
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        // Mock AI response
        $this->mockAI->method('generateFix')->willReturn([
            'success' => true,
            'original_code' => '$query = "SELECT * FROM users";',
            'fixed_code' => '$stmt = $pdo->prepare("SELECT * FROM users");',
            'explanation' => 'Use prepared statements',
            'confidence' => 0.95
        ]);

        $result = $this->service->applyAutoFix(1, true);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('backup_path', $result);
        $this->assertFileExists($result['backup_path']);

        // Verify backup content matches original
        $backupContent = file_get_contents($result['backup_path']);
        $this->assertEquals($originalContent, $backupContent);
    }

    /**
     * @test
     * @covers AutoFixService::applyAutoFix
     */
    public function testApplyAutoFixThrowsExceptionForUnsafeCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot be auto-fixed');

        // Create test file
        $testFile = $this->testBackupDir . '/test_unsafe.php';
        file_put_contents($testFile, "<?php\n// Code\n");

        // Mock violation with unsafe rule
        $violationData = [
            'id' => 1,
            'rule_id' => 'UNSAFE999',
            'severity' => 'critical',
            'message' => 'Unsafe operation',
            'line_number' => 2,
            'file_path' => $testFile,
            'file_type' => 'php'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn($violationData);
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        $this->mockAI->method('generateFix')->willReturn([
            'success' => true,
            'original_code' => '// Code',
            'fixed_code' => 'system("rm -rf /");', // Dangerous!
            'explanation' => 'Bad fix',
            'confidence' => 0.50
        ]);

        $this->service->applyAutoFix(1);
    }

    /**
     * @test
     * @covers AutoFixService::batchApplyFixes
     */
    public function testBatchApplyFixesProcessesMultipleViolations(): void
    {
        // Create test files
        $testFile1 = $this->testBackupDir . '/test1.php';
        $testFile2 = $this->testBackupDir . '/test2.php';
        file_put_contents($testFile1, "<?php\n\$query1 = 'SELECT * FROM users';\n");
        file_put_contents($testFile2, "<?php\n\$query2 = 'SELECT * FROM posts';\n");

        // Mock violations
        $violations = [
            [
                'id' => 1,
                'rule_id' => 'SEC001',
                'severity' => 'critical',
                'message' => 'SQL Injection',
                'line_number' => 2,
                'file_path' => $testFile1,
                'file_type' => 'php'
            ],
            [
                'id' => 2,
                'rule_id' => 'SEC001',
                'severity' => 'critical',
                'message' => 'SQL Injection',
                'line_number' => 2,
                'file_path' => $testFile2,
                'file_type' => 'php'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $violations[0],  // First getViolation call for ID 1
                $violations[1]   // First getViolation call for ID 2
            );
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        // Mock AI responses
        $this->mockAI->method('generateFix')
            ->willReturnOnConsecutiveCalls(
                [
                    'success' => true,
                    'original_code' => '$query1 = \'SELECT * FROM users\';',
                    'fixed_code' => '$stmt = $pdo->prepare("SELECT * FROM users");',
                    'explanation' => 'Fix 1',
                    'confidence' => 0.95
                ],
                [
                    'success' => true,
                    'original_code' => '$query2 = \'SELECT * FROM posts\';',
                    'fixed_code' => '$stmt = $pdo->prepare("SELECT * FROM posts");',
                    'explanation' => 'Fix 2',
                    'confidence' => 0.95
                ]
            );

        $result = $this->service->batchApplyFixes([1, 2], false);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(2, $result['succeeded']);
        $this->assertEquals(0, $result['failed']);
        $this->assertCount(2, $result['results']);
    }

    /**
     * @test
     * @covers AutoFixService::batchApplyFixes
     */
    public function testBatchApplyFixesHandlesPartialFailures(): void
    {
        // Create one valid file, one invalid
        $testFile1 = $this->testBackupDir . '/test1.php';
        file_put_contents($testFile1, "<?php\n\$query = 'SELECT * FROM users';\n");

        $violations = [
            [
                'id' => 1,
                'rule_id' => 'SEC001',
                'severity' => 'critical',
                'message' => 'SQL Injection',
                'line_number' => 2,
                'file_path' => $testFile1,
                'file_type' => 'php'
            ],
            [
                'id' => 2,
                'rule_id' => 'SEC001',
                'severity' => 'critical',
                'message' => 'SQL Injection',
                'line_number' => 2,
                'file_path' => '/nonexistent/file.php',
                'file_type' => 'php'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')
            ->willReturnOnConsecutiveCalls(
                $violations[0],
                $violations[0],
                $violations[1],
                $violations[1]
            );
        $stmt->method('execute')->willReturn(true);

        $this->mockDb->method('prepare')->willReturn($stmt);

        $this->mockAI->method('generateFix')->willReturn([
            'success' => true,
            'original_code' => '$query = \'SELECT * FROM users\';',
            'fixed_code' => '$stmt = $pdo->prepare("SELECT * FROM users");',
            'explanation' => 'Fix',
            'confidence' => 0.95
        ]);

        $result = $this->service->batchApplyFixes([1, 2], false);

        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['succeeded']);
        $this->assertEquals(1, $result['failed']);
        $this->assertTrue($result['results'][1]['success']);
        $this->assertFalse($result['results'][2]['success']);
    }

    /**
     * @test
     * @covers AutoFixService::isFixSafe (via reflection)
     */
    public function testIsFixSafeDetectsDangerousCode(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('isFixSafe');
        $method->setAccessible(true);

        // Safe code
        $this->assertTrue($method->invoke($this->service, '$stmt = $pdo->prepare("SELECT * FROM users");'));

        // Dangerous patterns
        $this->assertFalse($method->invoke($this->service, 'system("rm -rf /");'));
        $this->assertFalse($method->invoke($this->service, 'exec("dangerous command");'));
        $this->assertFalse($method->invoke($this->service, 'eval($userInput);'));
        $this->assertFalse($method->invoke($this->service, 'DROP TABLE users;'));
    }

    /**
     * @test
     * @covers AutoFixService::extractCodeSnippet (via reflection)
     */
    public function testExtractCodeSnippetReturnsCorrectLines(): void
    {
        $content = implode("\n", [
            "<?php",
            "// Line 2",
            "// Line 3",
            "// Line 4",
            "// Target line 5",
            "// Line 6",
            "// Line 7",
            "// Line 8",
            "// Line 9",
        ]);

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractCodeSnippet');
        $method->setAccessible(true);

        $snippet = $method->invoke($this->service, $content, 5, 2);

        $this->assertStringContainsString('Line 3', $snippet);
        $this->assertStringContainsString('Target line 5', $snippet);
        $this->assertStringContainsString('Line 7', $snippet);
    }

    /**
     * @test
     * @covers AutoFixService::calculateLinesChanged (via reflection)
     */
    public function testCalculateLinesChangedReturnsCorrectCount(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('calculateLinesChanged');
        $method->setAccessible(true);

        $original = "line 1\nline 2\nline 3";
        $fixed = "line 1\nline 2\nline 3\nline 4\nline 5";

        $changed = $method->invoke($this->service, $original, $fixed);

        $this->assertEquals(2, $changed);
    }
}
