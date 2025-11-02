<?php
declare(strict_types=1);

require_once __FILE__ === $_SERVER['SCRIPT_FILENAME']
    ? __DIR__ . '/bootstrap.php'
    : __DIR__ . '/bootstrap.php';

use App\Tools\RedisTool;

ApiBootstrap::init();

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        ApiBootstrap::error('method_not_allowed', 405);
    }
    $input = ApiBootstrap::getJsonBody();
    $out = RedisTool::run($input, ['request_id'=>ApiBootstrap::getRequestId()]);
    ApiBootstrap::respond($out);
} catch (Throwable $e) {
    ApiBootstrap::error('internal_error', 500, ['message'=>$e->getMessage()]);
}
