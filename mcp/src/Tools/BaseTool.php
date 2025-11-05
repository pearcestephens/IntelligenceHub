<?php
namespace IntelligenceHub\MCP\Tools;

abstract class BaseTool implements ToolInterface {
    protected function ok($data, int $status = 200): array {
        return ['status' => $status, 'data' => $data];
    }
    
    protected function fail(string $msg, int $status = 500): array {
        return ['status' => $status, 'data' => ['error' => $msg]];
    }
}
