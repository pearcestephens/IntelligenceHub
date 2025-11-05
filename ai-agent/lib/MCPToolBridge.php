<?php
/**
 * MCP Tool Bridge - Call MCP Server V3 Tools from AI Orchestrator
 *
 * This bridge allows the AI Orchestrator to use all MCP Server tools:
 * - semantic_search (8,645 files indexed)
 * - database queries
 * - file operations
 * - system info
 * - All other V3 tools
 *
 * @package IntelligenceHub\AIAgent
 * @version 1.0.0
 */

declare(strict_types=1);

class MCPToolBridge {
    private string $mcpServerUrl;
    private string $apiKey;

    public function __construct(string $mcpServerUrl = 'https://gpt.ecigdis.co.nz/mcp/server_v3.php', ?string $apiKey = null) {
        $this->mcpServerUrl = $mcpServerUrl;
        $this->apiKey = $apiKey ?? '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';
    }

    /**
     * Call an MCP tool
     */
    public function callTool(string $toolName, array $arguments): array {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => $toolName,
                'arguments' => $arguments
            ],
            'id' => uniqid('mcp-tool-')
        ];

        $ch = curl_init($this->mcpServerUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => "MCP Server returned HTTP {$httpCode}",
                'tool' => $toolName
            ];
        }

        $result = json_decode($response, true);

        if (!$result || isset($result['error'])) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown error',
                'tool' => $toolName
            ];
        }

        return [
            'success' => true,
            'tool' => $toolName,
            'result' => $result['result'] ?? []
        ];
    }

    /**
     * Semantic search across 8,645 indexed files
     */
    public function semanticSearch(string $query, int $limit = 10): array {
        return $this->callTool('semantic_search', [
            'query' => $query,
            'limit' => $limit
        ]);
    }

    /**
     * Execute database query
     */
    public function databaseQuery(string $sql, array $params = []): array {
        return $this->callTool('database_query', [
            'sql' => $sql,
            'params' => $params
        ]);
    }

    /**
     * Read file contents
     */
    public function readFile(string $filePath, ?int $startLine = null, ?int $endLine = null): array {
        return $this->callTool('file_read', [
            'file_path' => $filePath,
            'start_line' => $startLine,
            'end_line' => $endLine
        ]);
    }

    /**
     * Search files by pattern
     */
    public function fileSearch(string $pattern, int $maxResults = 50): array {
        return $this->callTool('file_search', [
            'pattern' => $pattern,
            'max_results' => $maxResults
        ]);
    }

    /**
     * Grep search in files
     */
    public function grepSearch(string $query, bool $isRegex = false, ?string $includePattern = null): array {
        return $this->callTool('grep_search', [
            'query' => $query,
            'is_regex' => $isRegex,
            'include_pattern' => $includePattern
        ]);
    }

    /**
     * Get system information
     */
    public function systemInfo(): array {
        return $this->callTool('system_info', []);
    }

    /**
     * List available MCP tools
     */
    public function listTools(): array {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'params' => [],
            'id' => uniqid('mcp-')
        ];

        $ch = curl_init($this->mcpServerUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        return $result['result']['tools'] ?? [];
    }
}
