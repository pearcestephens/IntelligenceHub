<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Config;
use App\DB;

// Simple DB schema status and optional apply script
// Usage:
//   php ops/db-status.php           # just report
//   php ops/db-status.php --apply   # apply sql/schema.sql idempotently

function out(string $level, string $msg, array $ctx = []): void {
    $ts = date('Y-m-d H:i:s');
    $ctxStr = $ctx ? ' ' . json_encode($ctx, JSON_UNESCAPED_SLASHES) : '';
    echo "[$ts] $level: $msg$ctxStr\n";
}

$apply = in_array('--apply', $argv, true);

Config::initialize();
DB::connection();

$tables = [
    'conversations',
    'messages',
    'tool_calls',
    'kb_docs',
    'kb_chunks',
];

$missing = [];
foreach ($tables as $t) {
    $exists = DB::selectValue('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$t]);
    if ((int)$exists === 1) {
        out('PASS', "table exists: $t");
    } else {
        out('FAIL', "table missing: $t");
        $missing[] = $t;
    }
}

if ($apply) {
    $schemaFile = __DIR__ . '/../sql/schema.sql';
    if (!is_readable($schemaFile)) {
        out('ERROR', 'schema.sql not readable', ['path' => $schemaFile]);
        exit(2);
    }
    $sql = file_get_contents($schemaFile);
    try {
        DB::connection()->exec($sql);
        out('PASS', 'schema applied');
    } catch (Throwable $e) {
        out('ERROR', 'schema apply failed', ['error' => $e->getMessage()]);
        exit(2);
    }
}

$afterMissing = [];
if ($apply) {
    foreach ($tables as $t) {
        $exists = DB::selectValue('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$t]);
        if ((int)$exists !== 1) {
            $afterMissing[] = $t;
        }
    }
}

$status = empty($missing) ? 'OK' : (empty($afterMissing) ? 'FIXED' : 'MISSING');
$payload = [
    'status' => $status,
    'missing_before' => $missing,
    'missing_after' => $afterMissing,
];

echo json_encode($payload, JSON_PRETTY_PRINT) . "\n";
exit($status === 'OK' || $status === 'FIXED' ? 0 : 1);
