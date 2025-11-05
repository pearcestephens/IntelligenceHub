<?php

namespace IntelligenceHub\MCP\Tools;

class PasswordTools extends BaseTool {
    private PasswordStorageTool $legacy;

    public function __construct() {
        $this->legacy = new PasswordStorageTool();
    }

    public function getName(): string {
        return 'password';
    }

    public function getSchema(): array {
        return [
            'password.store' => [
                'description' => 'Store encrypted credentials',
                'parameters' => [
                    'service' => ['type' => 'string', 'required' => true],
                    'username' => ['type' => 'string', 'required' => false],
                    'password' => ['type' => 'string', 'required' => true],
                    'notes' => ['type' => 'string', 'required' => false]
                ]
            ],
            'password.retrieve' => [
                'description' => 'Retrieve stored credentials',
                'parameters' => [
                    'service' => ['type' => 'string', 'required' => true]
                ]
            ],
            'password.delete' => [
                'description' => 'Delete stored credentials',
                'parameters' => [
                    'service' => ['type' => 'string', 'required' => true]
                ]
            ],
            'password.list' => [
                'description' => 'List all stored services',
                'parameters' => []
            ],
            'password.update' => [
                'description' => 'Update existing credentials',
                'parameters' => [
                    'service' => ['type' => 'string', 'required' => true],
                    'password' => ['type' => 'string', 'required' => false],
                    'notes' => ['type' => 'string', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'list';

        // Prepare params for legacy tool
        $params = array_merge(['action' => $method], $args);
        unset($params['_method']);

        // Execute legacy tool
        $result = $this->legacy->execute($params);

        // Convert response format: ['success' => bool] -> ['status' => int]
        if (isset($result['success'])) {
            $status = $result['success'] ? 200 : 400;
            unset($result['success']);

            if (isset($result['error'])) {
                return $this->fail($result['error'], $status);
            }

            return $this->ok($result, $status);
        }

        // Fallback - already in correct format or unknown format
        return $result;
    }
}
