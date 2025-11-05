<?php
namespace IntelligenceHub\MCP\Tools;

interface ToolInterface {
    public function execute(array $args): array;
    public function getName(): string;
    public function getSchema(): array;
}
