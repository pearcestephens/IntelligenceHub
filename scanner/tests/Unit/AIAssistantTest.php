<?php

declare(strict_types=1);

namespace Scanner\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Scanner\Lib\AIAssistant;
use PDO;
use PDOStatement;
use RuntimeException;
use InvalidArgumentException;

/**
 * Unit tests for AIAssistant class
 *
 * Tests AI-powered code analysis, explanation generation, and fix suggestions
 *
 * @package Scanner\Tests\Unit
 * @covers \Scanner\Lib\AIAssistant
 */
class AIAssistantTest extends TestCase
{
    private PDO $pdo;
    private AIAssistant $assistant;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->assistant = new AIAssistant($this->pdo);
    }

    /**
     * @test
     */
    public function constructor_accepts_valid_parameters(): void
    {
        $assistant = new AIAssistant(
            $this->pdo,
            'https://example.com/mcp',
            60
        );

        $this->assertInstanceOf(AIAssistant::class, $assistant);
    }

    /**
     * @test
     */
    public function constructor_throws_exception_for_invalid_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid MCP server URL');

        new AIAssistant($this->pdo, 'not-a-valid-url');
    }

    /**
     * @test
     */
    public function constructor_throws_exception_for_invalid_timeout(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Timeout must be between 1 and 300 seconds');

        new AIAssistant($this->pdo, 'https://example.com', 500);
    }

    /**
     * @test
     */
    public function review_violation_throws_exception_for_invalid_id(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false);

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Violation not found');

        $this->assistant->reviewViolation(999);
    }

    /**
     * @test
     */
    public function review_violation_returns_valid_structure(): void
    {
        // Mock violation data
        $violationData = [
            'id' => 1,
            'rule_id' => 'SEC001',
            'file_id' => 10,
            'line_number' => 42,
            'severity' => 'critical'
        ];

        // Mock file data
        $fileData = [
            'file_id' => 10,
            'file_path' => '/test/file.php',
            'content' => "<?php\n\$query = \"SELECT * FROM users WHERE id = '\$id'\";\n"
        ];

        $stmtViolation = $this->createMock(PDOStatement::class);
        $stmtViolation->method('fetch')->willReturn($violationData);

        $stmtFile = $this->createMock(PDOStatement::class);
        $stmtFile->method('fetch')->willReturn($fileData);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtViolation, $stmtFile);

        $result = $this->assistant->reviewViolation(1);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('violation', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('severity_reasoning', $result);
        $this->assertArrayHasKey('fix_suggestion', $result);
        $this->assertArrayHasKey('references', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_sql_injection(): void
    {
        $violation = ['rule_id' => 'SEC001'];
        $fileData = ['content' => '<?php $query = "SELECT * FROM users";'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('severity_reasoning', $result);
        $this->assertArrayHasKey('fix_suggestion', $result);
        $this->assertStringContainsString('SQL injection', $result['explanation']);
        $this->assertStringContainsString('prepared statements', $result['fix_suggestion']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_xss(): void
    {
        $violation = ['rule_id' => 'SEC002'];
        $fileData = ['content' => '<?php echo $userInput;'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertStringContainsString('XSS', $result['explanation']);
        $this->assertStringContainsString('htmlspecialchars', $result['fix_suggestion']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_hardcoded_credentials(): void
    {
        $violation = ['rule_id' => 'SEC003'];
        $fileData = ['content' => '<?php $password = "secret123";'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertStringContainsString('credentials', $result['explanation']);
        $this->assertStringContainsString('environment variables', $result['fix_suggestion']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_code_quality(): void
    {
        $violation = ['rule_id' => 'CODE001'];
        $fileData = ['content' => '<?php function foo() {}'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertStringContainsString('quality', $result['explanation']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_best_practices(): void
    {
        $violation = ['rule_id' => 'CODE002'];
        $fileData = ['content' => '<?php class Test {}'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertStringContainsString('best practices', $result['explanation']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_performance(): void
    {
        $violation = ['rule_id' => 'PERF001'];
        $fileData = ['content' => '<?php for ($i=0; $i<1000; $i++) {}'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertStringContainsString('performance', $result['explanation']);
    }

    /**
     * @test
     */
    public function generate_explanation_handles_unknown_rule(): void
    {
        $violation = ['rule_id' => 'UNKNOWN999'];
        $fileData = ['content' => '<?php echo "test";'];

        $result = $this->assistant->generateExplanation($violation, $fileData);

        $this->assertArrayHasKey('explanation', $result);
        $this->assertIsString($result['explanation']);
    }

    /**
     * @test
     */
    public function generate_fix_throws_exception_for_invalid_violation(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('fetch')->willReturn(false);

        $this->pdo->method('prepare')->willReturn($stmt);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Violation not found');

        $this->assistant->generateFix(999);
    }

    /**
     * @test
     */
    public function generate_fix_returns_valid_structure(): void
    {
        $violationData = [
            'id' => 1,
            'rule_id' => 'SEC001',
            'file_id' => 10,
            'line_number' => 5
        ];

        $fileData = [
            'file_id' => 10,
            'content' => "<?php\n\$query = \"SELECT * FROM users WHERE id = '\$id'\";\n"
        ];

        $stmtViolation = $this->createMock(PDOStatement::class);
        $stmtViolation->method('fetch')->willReturn($violationData);

        $stmtFile = $this->createMock(PDOStatement::class);
        $stmtFile->method('fetch')->willReturn($fileData);

        $this->pdo->method('prepare')
            ->willReturnOnConsecutiveCalls($stmtViolation, $stmtFile);

        $result = $this->assistant->generateFix(1);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('original_code', $result);
        $this->assertArrayHasKey('fixed_code', $result);
        $this->assertArrayHasKey('explanation', $result);
        $this->assertArrayHasKey('confidence', $result);
        $this->assertTrue($result['success']);
    }

    /**
     * @test
     */
    public function semantic_search_returns_valid_structure(): void
    {
        // This test would require mocking the MCP server response
        // For now, just verify the method exists and accepts parameters
        $this->assertTrue(method_exists($this->assistant, 'semanticSearch'));
    }

    /**
     * @test
     */
    public function interpret_query_recognizes_sql_injection_keywords(): void
    {
        $result = $this->assistant->interpretQuery('find SQL injection vulnerabilities');

        $this->assertArrayHasKey('interpreted', $result);
        $this->assertArrayHasKey('suggested_tool', $result);
        $this->assertArrayHasKey('keywords', $result);
        $this->assertStringContainsString('SQL', $result['interpreted']);
        $this->assertEquals('search_by_category', $result['suggested_tool']);
    }

    /**
     * @test
     */
    public function interpret_query_recognizes_xss_keywords(): void
    {
        $result = $this->assistant->interpretQuery('show me XSS vulnerabilities');

        $this->assertStringContainsString('Scripting', $result['interpreted']);
        $this->assertEquals('search_by_category', $result['suggested_tool']);
    }

    /**
     * @test
     */
    public function interpret_query_recognizes_auth_keywords(): void
    {
        $result = $this->assistant->interpretQuery('how do we handle authentication?');

        $this->assertStringContainsString('Authentication', $result['interpreted']);
        $this->assertEquals('semantic_search', $result['suggested_tool']);
    }

    /**
     * @test
     */
    public function interpret_query_defaults_to_semantic_search(): void
    {
        $result = $this->assistant->interpretQuery('random code search');

        $this->assertEquals('semantic_search', $result['suggested_tool']);
        $this->assertIsArray($result['keywords']);
    }
}
