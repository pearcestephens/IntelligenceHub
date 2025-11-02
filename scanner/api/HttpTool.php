<?php
declare(strict_types=1);

require_once __FILE__ === $_SERVER['SCRIPT_FILENAME']
    ? __DIR__ . '/bootstrap.php'
    : __DIR__ . '/bootstrap.php';

use App\Tools\HttpTool;

ApiBootstrap::init();

try {
    if (($_l = $_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        ApiBootstrap::error('method_not_allowed', 405, ['got' => $_l]);
    }
    $input = ApiBootstrap::getJsonBody();
    $out = \App\Tools\HttpTool::run($input, ['request_id'=>ApiBootstrap::getRequestId()]);
    ApiBootstrap::respond($out);
} catch (Throwable $e) {
    $detail = ['message'=>$e->getMessage()];
    if (getenv('APP_DEBUG') === '1') { $detail['trace'] = $e->getTraceAsString(); }
    ApiBootstrap::error('internal_error', 500, $detail);
}
