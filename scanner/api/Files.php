<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

use App\Tools\FileTool;
use App\Logger;

header('Content-Type: application/json; charset=utf-8');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success'=>false,'error'=>['type'=>'method_not_allowed','message'=>'POST required']]);
        exit;
    }
    $raw = file_get_contents('php://input') ?: '{}';
    $input = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

    $res = FileTool::run(is_array($input)?$input:[], ['request_id'=>uniqid('req_', true)]);
    echo json_encode($res, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    if (class_exists('\App\Logger')) { (new Logger())->error('Files API error', ['error'=>$e->getMessage()]); }
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>['type'=>'internal_error','message'=>$e->getMessage()]]);
}
