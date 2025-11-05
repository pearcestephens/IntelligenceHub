<?php
/**
 * Intelligence Hub Adapter - FIXED VERSION
 * Connects to IntelligenceHub AI platform with proper MCP JSON-RPC calls
 *
 * FIXES:
 * - Added missing callAPI() method
 * - Fixed JSON-RPC method field in MCP calls
 * - Proper error handling
 * - Correct request format
 *
 * Copy this file to:
 * /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/modules/consignments/lib/Services/AI/Adapters/IntelligenceHubAdapter.php
 */

namespace CIS\Consignments\Services\AI\Adapters;

use CIS\Consignments\Services\AI\AIProviderInterface;
use Exception;

class IntelligenceHubAdapter implements AIProviderInterface
{
    private string $apiEndpoint;
    private string $apiKey;
    private string $mcpEndpoint;
    private int $timeout;

    public function __construct(array $config)
    {
        $this->apiEndpoint = $config['endpoint'] ?? 'https://gpt.ecigdis.co.nz/ai-agent/api/chat.php';
        $this->apiKey = $config['api_key'] ?? '';
        $this->mcpEndpoint = $config['mcp_endpoint'] ?? 'https://gpt.ecigdis.co.nz/mcp/server_v3.php';
        $this->timeout = $config['timeout'] ?? 45;
    }

    /**
     * Send a chat query to the AI
     */
    public function query(string $prompt, array $options = []): array
    {
        $data = [
            'message' => $prompt,
            'bot_id' => $options['bot_id'] ?? 1,
            'session_id' => $options['session_id'] ?? 'consignments_' . uniqid(),
            'streaming' => $options['streaming'] ?? false,
            'context' => $options['context'] ?? null
        ];

        try {
            $response = $this->callAPI($this->apiEndpoint, $data);

            return [
                'success' => true,
                'response' => $response['response'] ?? $response['message'] ?? 'No response',
                'model' => $response['model'] ?? 'intelligence-hub',
                'usage' => $response['usage'] ?? null
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Use an MCP tool (semantic search, grep, db query, etc.)
     */
    public function useMCPTool(string $toolName, array $params = []): array
    {
        try {
            // Construct proper JSON-RPC 2.0 request
            $request = [
                'jsonrpc' => '2.0',
                'id' => uniqid('mcp_'),
                'method' => 'tools/call',  // FIXED: Added method field
                'params' => [
                    'name' => $toolName,
                    'arguments' => $params
                ]
            ];

            $response = $this->callMCPAPI($request);

            if (isset($response['error'])) {
                throw new Exception('MCP Tool Error: ' . ($response['error']['message'] ?? 'Unknown error'));
            }

            return [
                'success' => true,
                'result' => $response['result'] ?? null,
                'content' => $response['result']['content'] ?? []
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Semantic search using MCP semantic_search tool
     */
    public function semanticSearch(string $query, array $options = []): array
    {
        return $this->useMCPTool('semantic_search', [
            'query' => $query,
            'limit' => $options['limit'] ?? 10,
            'threshold' => $options['threshold'] ?? 0.7
        ]);
    }

    /**
     * Grep search using MCP grep_search tool
     */
    public function grepSearch(string $pattern, array $options = []): array
    {
        return $this->useMCPTool('grep_search', [
            'query' => $pattern,
            'isRegexp' => $options['is_regexp'] ?? false,
            'includePattern' => $options['include_pattern'] ?? null,
            'maxResults' => $options['max_results'] ?? 50
        ]);
    }

    /**
     * Database query using MCP db.query tool
     */
    public function databaseQuery(string $sql, array $params = []): array
    {
        return $this->useMCPTool('db.query', [
            'query' => $sql,
            'params' => $params
        ]);
    }

    /**
     * List available MCP tools
     */
    public function listTools(): array
    {
        try {
            $request = [
                'jsonrpc' => '2.0',
                'id' => uniqid('mcp_'),
                'method' => 'tools/list',  // FIXED: Proper method field
                'params' => []
            ];

            $response = $this->callMCPAPI($request);

            return [
                'success' => true,
                'tools' => $response['result']['tools'] ?? []
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Call the main AI API endpoint
     * FIXED: Added this missing method
     */
    private function callAPI(string $endpoint, array $data): array
    {
        $ch = curl_init($endpoint);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("API request failed: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("API returned HTTP $httpCode: $response");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Call the MCP Server API with JSON-RPC
     * FIXED: Proper JSON-RPC 2.0 format
     */
    private function callMCPAPI(array $request): array
    {
        $ch = curl_init($this->mcpEndpoint);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($request),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("MCP Server request failed: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("MCP Server API error: HTTP $httpCode - $response");
        }

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response from MCP: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'IntelligenceHub';
    }

    /**
     * Check if provider is available
     */
    public function isAvailable(): bool
    {
        try {
            $request = [
                'jsonrpc' => '2.0',
                'id' => 'health_check',
                'method' => 'health/check',
                'params' => []
            ];

            $response = $this->callMCPAPI($request);
            return isset($response['result']);

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get configuration
     */
    public function getConfig(): array
    {
        return [
            'provider' => 'IntelligenceHub',
            'api_endpoint' => $this->apiEndpoint,
            'mcp_endpoint' => $this->mcpEndpoint,
            'timeout' => $this->timeout,
            'features' => [
                'semantic_search' => true,
                'grep_search' => true,
                'database_query' => true,
                'rag' => true,
                'tools' => true
            ]
        ];
    }
}
