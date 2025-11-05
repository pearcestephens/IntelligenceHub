<?php

namespace IntelligenceHub\MCP\Tools;

class KnowledgeTools extends BaseTool {

    public function getName(): string {
        return 'knowledge';
    }

    public function getSchema(): array {
        return [
            'knowledge.search' => [
                'description' => 'Search knowledge base',
                'parameters' => [
                    'query' => ['type' => 'string', 'required' => true],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'knowledge.get_document' => [
                'description' => 'Get a knowledge document by ID',
                'parameters' => [
                    'document_id' => ['type' => 'string', 'required' => true]
                ]
            ],
            'knowledge.list_documents' => [
                'description' => 'List knowledge documents',
                'parameters' => [
                    'page' => ['type' => 'integer', 'required' => false],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'search';

        switch ($method) {
            case 'search':
                return $this->search($args);
            case 'get_document':
                return $this->getDocument($args);
            case 'list_documents':
                return $this->listDocuments($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function search(array $args): array {
        $limit = isset($args['limit']) ? (int)$args['limit'] : 5;
        $payload = ['query' => $args['query'] ?? '', 'limit' => $limit];

        $result = $this->httpPost('agent/api/knowledge.php?action=search', $payload, 30);
        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function getDocument(array $args): array {
        $docId = $args['document_id'] ?? '';
        $url = 'agent/api/knowledge.php?action=documents&id=' . rawurlencode($docId);

        $result = $this->httpGet($url, 20);
        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        $json = $result['data'];
        $document = is_array($json) ? ($json['document'] ?? $json) : null;

        return $this->ok($document);
    }

    private function listDocuments(array $args): array {
        $page = max(1, (int)($args['page'] ?? 1));
        $limit = max(1, min(100, (int)($args['limit'] ?? 20)));
        $url = 'agent/api/knowledge.php?action=documents&limit=' . $limit . '&offset=' . (($page - 1) * $limit);

        $result = $this->httpGet($url, 20);
        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        return $this->ok($result['data']);
    }

    private function httpPost(string $endpoint, array $payload, int $timeout): array {
        $url = ($_ENV['AGENT_BASE'] ?? 'https://gpt.ecigdis.co.nz/mcp') . '/' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . ($_ENV['MCP_API_KEY'] ?? '')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'code' => 502, 'data' => null];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['error' => "HTTP $httpCode", 'code' => $httpCode, 'data' => null];
        }

        $json = json_decode($response, true);
        return ['error' => null, 'code' => $httpCode, 'data' => $json ?? $response];
    }

    private function httpGet(string $endpoint, int $timeout): array {
        $url = ($_ENV['AGENT_BASE'] ?? 'https://gpt.ecigdis.co.nz/mcp') . '/' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . ($_ENV['MCP_API_KEY'] ?? '')
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'code' => 502, 'data' => null];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['error' => "HTTP $httpCode", 'code' => $httpCode, 'data' => null];
        }

        $json = json_decode($response, true);
        return ['error' => null, 'code' => $httpCode, 'data' => $json ?? $response];
    }
}
