<?php
/**
 * Scanner MCP Agent Client
 *
 * Provides shared access to the MCP intelligence server for all Scanner
 * services (batch, real-time, AI assistants).
 *
 * @package Scanner\Lib
 * @version 1.0.0
 */

declare(strict_types=1);

namespace Scanner\Lib;

use RuntimeException;

class MCPAgent
{
    private string $endpoint;

    /**
     * Supported MCP tools (keep in sync with server configuration)
     *
     * @var string[]
     */
    private array $allowedTools = [
        'semantic_search',
        'search_by_category',
        'find_code',
        'find_similar',
        'explore_by_tags',
        'analyze_file',
        'get_file_content',
        'health_check',
        'get_stats',
        'top_keywords',
        'list_categories',
        'get_analytics',
        'list_satellites'
    ];

    public function __construct(?string $endpoint = null)
    {
        $this->endpoint = $endpoint ?? 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';
    }

    /**
     * Perform a generic MCP call.
     *
     * @param string $tool
     * @param array<string,mixed> $arguments
     * @return array<string,mixed>
     */
    public function call(string $tool, array $arguments = []): array
    {
        if (!in_array($tool, $this->allowedTools, true)) {
            throw new RuntimeException("Unknown MCP tool: {$tool}");
        }

        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => $tool,
                'arguments' => $arguments,
            ],
            'id' => uniqid('mcp_', true),
        ];

        $ch = curl_init($this->endpoint);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize MCP request');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: Scanner/3.0.0',
            ],
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('MCP request failed: ' . ($error ?: 'unknown error'));
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("MCP returned HTTP status {$httpCode}");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response from MCP: ' . json_last_error_msg());
        }

        if (isset($data['error'])) {
            $message = $data['error']['message'] ?? 'Unknown MCP error';
            throw new RuntimeException('MCP error: ' . $message);
        }

        return $data['result'] ?? [];
    }

    public function analyzeFile(string $filePath): array
    {
        return $this->call('analyze_file', ['file_path' => $filePath]);
    }

    public function getFileContent(string $filePath): array
    {
        return $this->call('get_file_content', ['file_path' => $filePath]);
    }

    public function healthCheck(): array
    {
        return $this->call('health_check');
    }

    public function semanticSearch(string $query, int $limit = 20): array
    {
        return $this->call('semantic_search', ['query' => $query, 'limit' => $limit]);
    }

    public function getAnalytics(string $action = 'overview', string $timeframe = '24h'): array
    {
        return $this->call('get_analytics', [
            'action' => $action,
            'timeframe' => $timeframe,
        ]);
    }
}
