<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/bootstrap.php';

use App\Tools\DatabaseTool;

final class DatabaseToolTest extends TestCase
{
    public function testStatus(): void
    {
        $res = DatabaseTool::run(['action' => 'status']);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('healthy', $res);
    }

    public function testSimpleQuery(): void
    {
        // Skip if database not configured
        try {
            $statusCheck = DatabaseTool::run(['action' => 'status']);
            if (!($statusCheck['healthy'] ?? false)) {
                $this->markTestSkipped('Database not available');
            }
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database not configured: ' . $e->getMessage());
        }
        
        $q = DatabaseTool::run(['action' => 'query', 'query' => 'SELECT 1 AS one']);
        $this->assertIsArray($q);
        $this->assertArrayHasKey('rows', $q);
        $this->assertSame('1', (string)($q['rows'][0]['one'] ?? ''));
    }
}
