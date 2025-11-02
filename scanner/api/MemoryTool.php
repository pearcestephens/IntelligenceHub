<?php
declare(strict_types=1);

require_once __FILE__ === $_SERVER'SCRIPT_FILENAME'
    ? __DIR__ . '/../../src/bootstrap.php'
    : __DIR__ . '/../../src/bootstrap.php';

use App\Logger;
use App\Tools\MemoryTool;

header('Content-Type: application/json; charset=utf-8');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success'=>false,'error'=>['type'=>'method_not_allowed','message'=>'POST required']]); exit;
    }
    $input = json_decode(file_get_contents('php://input') ?: '{}', true, 512, JSON_THROW_ON_ERROR);
    $out = MemoryTool::run(is_array($input)?$input:[], ['request_id'=>uniqid('req_',true)]);
    echo json_encode($out, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    if (class_exists('\App\Logger')) {(new Logger())->error('MemoryTool API error',['err'=>$e->getMessage()]);}
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>['type'=>'internal_error','message'=>$e->getMessage()]]);
}
