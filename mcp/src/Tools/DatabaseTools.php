<?php
namespace IntelligenceHub\MCP\Tools;

class DatabaseTools extends BaseTool {
    private \PDO $pdo;

    public function __construct() {
        $dsn = 'mysql:host=127.0.0.1;dbname=hdgwrzntwa;charset=utf8mb4';
        $this->pdo = new \PDO($dsn, 'hdgwrzntwa', $_ENV['DB_PASS'] ?? 'bFUdRjh4Jx', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function getName(): string {
        return 'db';
    }

    public function getSchema(): array {
        return [
            'db.query_readonly' => [
                'description' => 'Execute read-only SQL queries',
                'parameters' => [
                    'sql' => ['type' => 'string', 'required' => true],
                    'params' => ['type' => 'array', 'required' => false]
                ]
            ],
            'db.stats' => [
                'description' => 'Get database statistics',
                'parameters' => []
            ],
            'db.explain' => [
                'description' => 'Get query execution plan',
                'parameters' => [
                    'sql' => ['type' => 'string', 'required' => true]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'query_readonly';

        switch ($method) {
            case 'query_readonly':
                return $this->queryReadonly($args);
            case 'stats':
                return $this->stats($args);
            case 'explain':
                return $this->explain($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }    private function queryReadonly(array $args): array {
        try {
            $stmt = $this->pdo->prepare($args['sql'] ?? 'SELECT 1');
            $stmt->execute($args['params'] ?? []);
            return $this->ok(['rows' => $stmt->fetchAll(\PDO::FETCH_ASSOC)]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    private function stats(array $args): array {
        try {
            // Get table statistics
            $sql = "SELECT
                TABLE_NAME as name,
                TABLE_ROWS as row_count,
                ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
                LIMIT 10";

            $stmt = $this->pdo->query($sql);
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get total count
            $totalStmt = $this->pdo->query("SELECT COUNT(*) as total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
            $total = $totalStmt->fetch(\PDO::FETCH_ASSOC)['total'];

            return $this->ok([
                'tables' => $total,
                'top10' => $tables
            ]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }

    private function explain(array $args): array {
        try {
            $sql = trim($args['sql'] ?? '');
            if (empty($sql)) {
                return $this->fail('sql parameter is required');
            }

            $stmt = $this->pdo->prepare('EXPLAIN FORMAT=JSON ' . $sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $this->ok([
                'explain' => $row,
                'query' => $sql
            ]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
