<?php
declare(strict_types=1);

require_once __FILE__ === $_SERVER['SCRIPT_FILENAME']
    ? __DIR__ . '/bootstrap.php'
    : __DIR__ . '/bootstrap.php';

use App\Tools\DatabaseTool;

ApiBootstrap::init();

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        ApiBootstrap::error('method_not_allowed', 405);
    }

    $input = ApiBootstrap::getJsonBody(); // throws on invalid JSON
    // Forward to tool; it will validate 'action' + params internally.
    $out = DatabaseTool::run($input, ['request_id' => ApiBootstrap::getRequestId()]);
    ApiBootstrap::respond($out);
} catch (Throwable $e) {
    $detail = ['message' => $e->getMessage()];
    if (getenv('APP_DEBUG') === '1') { $detail['trace'] = $e->getTraceAsString(); }
    ApiBootstrap::error('internal_error', 500, $detail);
}
