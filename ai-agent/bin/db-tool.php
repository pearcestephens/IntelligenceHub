<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/bootstrap.php';

use App\Tools\DatabaseTool;

// Usage:
//   php bin/db-tool.php '{"action":"status"}'
//   php bin/db-tool.php --action=query --query='SELECT 1' --params='[]'

$args = $argv;
array_shift($args);

$payload = [];
if (!empty($args)) {
    if (str_starts_with($args[0], '{')) {
        $payload = json_decode($args[0], true) ?? [];
    } else {
        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            if (str_starts_with($arg, '--')) {
                $key = ltrim($arg, '-');
                $value = $args[$i+1] ?? null;
                if ($value !== null && !str_starts_with($value, '--')) {
                    $i++;
                } else {
                    $value = true;
                }
                $payload[$key] = $value;
            }
        }
        if (isset($payload['params']) && is_string($payload['params'])) {
            $decoded = json_decode($payload['params'], true);
            if (is_array($decoded)) $payload['params'] = $decoded; else unset($payload['params']);
        }
        if (isset($payload['action']) && $payload['action'] === 'table_info' && isset($payload['table']) === false) {
            fwrite(STDERR, "--table is required for action table_info\n");
            exit(2);
        }
    }
}

$result = DatabaseTool::run($payload);
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
