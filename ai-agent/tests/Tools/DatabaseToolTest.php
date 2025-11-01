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

    public function testTablesAndQuery(): void
    {
        $res = DatabaseTool::run(['action' => 'tables']);
        $this->assertIsArray($res);
        $this->assertArrayHasKey('tables', $res);

        $q = DatabaseTool::run(['action' => 'query', 'query' => 'SELECT 1 AS one']);
        $this->assertIsArray($q);
        $this->assertArrayHasKey('rows', $q);
        $this->assertSame('1', (string)($q['rows'][0]['one'] ?? ''));
    }
}
