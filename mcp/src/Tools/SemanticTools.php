<?php

namespace IntelligenceHub\MCP\Tools;

class SemanticTools extends BaseTool {
    private SemanticSearchTool $legacy;

    public function __construct() {
        $this->legacy = new SemanticSearchTool();
    }

    public function getName(): string {
        return 'semantic';
    }

    public function getSchema(): array {
        return [
            'semantic.search' => [
                'description' => 'Semantic search with AI-powered relevance',
                'parameters' => [
                    'query' => ['type' => 'string', 'required' => true],
                    'limit' => ['type' => 'integer', 'required' => false],
                    'context' => ['type' => 'string', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'search';

        try {
            switch ($method) {
                case 'search':
                    $result = $this->legacy->search($args);
                    break;
                default:
                    return $this->fail("Unknown method: $method");
            }

            // Legacy tool returns direct results
            return $this->ok($result);

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
