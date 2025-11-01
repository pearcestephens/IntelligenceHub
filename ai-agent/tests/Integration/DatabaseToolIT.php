<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/bootstrap.php';

use App\Tools\DatabaseTool;

final class DatabaseToolIT extends TestCase
{
    public function testStatusAndQueryIntegration(): void
    {
        $status = DatabaseTool::run(['action' => 'status']);
        $this->assertIsArray($status);
        $this->assertArrayHasKey('healthy', $status);

        $q = DatabaseTool::run(['action' => 'query', 'query' => 'SELECT 1 AS one']);
        $this->assertIsArray($q);
        $this->assertArrayHasKey('rows', $q);
        $this->assertSame('1', (string)($q['rows'][0]['one'] ?? ''));
    }
}
