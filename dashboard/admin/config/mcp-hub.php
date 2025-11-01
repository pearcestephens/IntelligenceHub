<?php
/**
 * MCP Intelligence Hub Configuration
 *
 * Connects dashboard to gpt.ecigdis.co.nz for advanced features:
 * - Semantic search across codebases
 * - AI-powered code analysis
 * - Real-time intelligence updates
 *
 * @package Dashboard\Config
 * @version 1.0.0
 */

declare(strict_types=1);

// ============================================================================
// MCP HUB CONFIGURATION
// ============================================================================

define('MCP_HUB_DOMAIN', 'gpt.ecigdis.co.nz');
define('MCP_HUB_PROTOCOL', 'https');
define('MCP_HUB_BASE_URL', MCP_HUB_PROTOCOL . '://' . MCP_HUB_DOMAIN);

// MCP Server Endpoints
define('MCP_API_SEMANTIC_SEARCH', MCP_HUB_BASE_URL . '/mcp/server_v2_complete.php');
define('MCP_API_ANALYSIS', MCP_HUB_BASE_URL . '/mcp/analysis.php');
define('MCP_API_INTELLIGENCE', MCP_HUB_BASE_URL . '/mcp/intelligence.php');
define('MCP_API_HEALTH', MCP_HUB_BASE_URL . '/health.php');

// ============================================================================
// MCP CLIENT CLASS
// ============================================================================

class MCPHubClient {
    /**
     * @var string MCP Hub base URL
     */
    private string $baseUrl;

    /**
     * @var array cURL options
     */
    private array $curlOptions;

    /**
     * Constructor
     */
    public function __construct() {
        $this->baseUrl = MCP_HUB_BASE_URL;
        $this->curlOptions = [
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ];
    }

    /**
     * Check MCP Hub connectivity
     *
     * @return array {success: bool, status: string, latency: float}
     */
    public function healthCheck(): array {
        $start = microtime(true);

        try {
            $ch = curl_init(MCP_API_HEALTH);
            curl_setopt_array($ch, $this->curlOptions);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $latency = (microtime(true) - $start) * 1000; // ms

            return [
                'success' => $httpCode === 200,
                'status' => $httpCode === 200 ? 'Connected' : "HTTP {$httpCode}",
                'latency' => round($latency, 2),
                'domain' => MCP_HUB_DOMAIN,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 'Connection failed: ' . $e->getMessage(),
                'latency' => round((microtime(true) - $start) * 1000, 2),
                'domain' => MCP_HUB_DOMAIN,
            ];
        }
    }

    /**
     * Perform semantic search on MCP Hub
     *
     * @param string $query Search query
     * @param array $options Search options
     * @return array Search results or error
     */
    public function semanticSearch(string $query, array $options = []): array {
        $payload = array_merge(
            [
                'jsonrpc' => '2.0',
                'method' => 'tools/call',
                'params' => [
                    'name' => 'semantic_search',
                    'arguments' => [
                        'query' => $query,
                        'limit' => $options['limit'] ?? 10,
                    ],
                ],
                'id' => uniqid(),
            ],
            $options
        );

        return $this->makeRequest(MCP_API_SEMANTIC_SEARCH, $payload);
    }

    /**
     * Analyze code using MCP Hub
     *
     * @param string $code Code to analyze
     * @param array $options Analysis options
     * @return array Analysis results
     */
    public function analyzeCode(string $code, array $options = []): array {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'analyze_code',
                'arguments' => [
                    'code' => $code,
                    'language' => $options['language'] ?? 'php',
                    'strict' => $options['strict'] ?? true,
                ],
            ],
            'id' => uniqid(),
        ];

        return $this->makeRequest(MCP_API_ANALYSIS, $payload);
    }

    /**
     * Get intelligence data from hub
     *
     * @param string $category Intelligence category
     * @param array $options Query options
     * @return array Intelligence data
     */
    public function getIntelligence(string $category, array $options = []): array {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_intelligence',
                'arguments' => [
                    'category' => $category,
                    'project_id' => $options['project_id'] ?? null,
                ],
            ],
            'id' => uniqid(),
        ];

        return $this->makeRequest(MCP_API_INTELLIGENCE, $payload);
    }

    /**
     * Make HTTP request to MCP Hub
     *
     * @param string $url Endpoint URL
     * @param array $payload JSON payload
     * @return array Response data or error
     */
    private function makeRequest(string $url, array $payload): array {
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, $this->curlOptions);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: Dashboard/1.0',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'error' => $error,
                ];
            }

            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => "HTTP {$httpCode}",
                    'response' => $response,
                ];
            }

            $data = json_decode($response, true);

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

// ============================================================================
// SINGLETON INSTANCE
// ============================================================================

/**
 * Get MCP Hub client instance
 *
 * @return MCPHubClient
 */
function mcpHub(): MCPHubClient {
    static $client = null;
    if ($client === null) {
        $client = new MCPHubClient();
    }
    return $client;
}

// ============================================================================
// EXPORT
// ============================================================================

return [
    'domain' => MCP_HUB_DOMAIN,
    'protocol' => MCP_HUB_PROTOCOL,
    'base_url' => MCP_HUB_BASE_URL,
    'endpoints' => [
        'semantic_search' => MCP_API_SEMANTIC_SEARCH,
        'analysis' => MCP_API_ANALYSIS,
        'intelligence' => MCP_API_INTELLIGENCE,
        'health' => MCP_API_HEALTH,
    ],
];
