<?php

namespace IntelligenceHub\MCP\Tools;

class BrowserTools extends BaseTool {
    private WebBrowserTool $legacy;

    public function __construct() {
        $this->legacy = new WebBrowserTool();
    }

    public function getName(): string {
        return 'browser';
    }

    public function getSchema(): array {
        return [
            'browser.fetch' => [
                'description' => 'Fetch webpage content',
                'parameters' => [
                    'url' => ['type' => 'string', 'required' => true],
                    'extract' => ['type' => 'string', 'required' => false]
                ]
            ],
            'browser.screenshot' => [
                'description' => 'Take webpage screenshot',
                'parameters' => [
                    'url' => ['type' => 'string', 'required' => true],
                    'width' => ['type' => 'integer', 'required' => false],
                    'height' => ['type' => 'integer', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'fetch';

        try {
            $result = $this->legacy->execute(array_merge(['action' => $method], $args));

            // Convert legacy response
            if (isset($result['success'])) {
                $status = $result['success'] ? 200 : 400;
                unset($result['success']);

                if (isset($result['error'])) {
                    return $this->fail($result['error'], $status);
                }

                return $this->ok($result, $status);
            }

            return $this->ok($result);

        } catch (\Throwable $e) {
            return $this->fail($e->getMessage());
        }
    }
}
