<?php

namespace IntelligenceHub\MCP\Tools;

class ChatTools extends BaseTool {

    public function getName(): string {
        return 'chat';
    }

    public function getSchema(): array {
        return [
            'chat.send' => [
                'description' => 'Send a chat message',
                'parameters' => [
                    'message' => ['type' => 'string', 'required' => true],
                    'conversation_id' => ['type' => 'string', 'required' => false],
                    'stream' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'chat.summarize' => [
                'description' => 'Summarize a conversation',
                'parameters' => [
                    'conversation_id' => ['type' => 'string', 'required' => true]
                ]
            ],
            'chat.send_stream' => [
                'description' => 'Send a streaming chat message',
                'parameters' => [
                    'message' => ['type' => 'string', 'required' => true],
                    'conversation_id' => ['type' => 'string', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'send';

        switch ($method) {
            case 'send':
                return $this->send($args);
            case 'summarize':
                return $this->summarize($args);
            case 'send_stream':
                return $this->sendStream($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function send(array $args): array {
        $payload = [
            'message' => $args['message'] ?? '',
            'conversation_id' => $args['conversation_id'] ?? null,
            'stream' => (bool)($args['stream'] ?? false)
        ];

        $result = $this->httpPost('agent/api/chat.php', $payload, 45);
        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        $json = $result['data'];
        $msg = is_array($json) ? ($json['message'] ?? $json['response'] ?? $json['data']['message'] ?? null) : null;
        $conv = is_array($json) ? ($json['conversation_id'] ?? $json['data']['conversation_id'] ?? null) : null;

        return $this->ok([
            'message' => $msg,
            'conversation_id' => $conv,
            'raw' => $json
        ]);
    }

    private function summarize(array $args): array {
        $payload = [
            'message' => '[SYSTEM_REQUEST] Summarize the conversation',
            'conversation_id' => $args['conversation_id'] ?? '',
            'stream' => false
        ];

        $result = $this->httpPost('agent/api/chat.php', $payload, 45);
        if ($result['error']) {
            return $this->fail($result['error'], $result['code']);
        }

        $json = $result['data'];
        $summary = is_array($json) ? ($json['summary'] ?? $json['message'] ?? $json['response'] ?? null) : null;

        return $this->ok([
            'summary' => $summary,
            'raw' => $json
        ]);
    }

    private function sendStream(array $args): array {
        $payload = [
            'message' => $args['message'] ?? '',
            'conversation_id' => $args['conversation_id'] ?? null,
            'stream' => true
        ];

        $result = $this->httpPost('agent/api/chat.php', $payload, 60);
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
}
