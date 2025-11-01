<?php
/**
 * AI Agent Client - Connects to CIS AI Agent Service
 * 
 * Uses the enterprise AI agent system in CIS as a shared service
 * No code duplication - just API calls
 * 
 * @package Intelligence Hub
 * @version 1.0.0
 */

declare(strict_types=1);

class AIAgentClient
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout;
    
    public function __construct(
        ?string $apiUrl = null,
        ?string $apiKey = null,
        int $timeout = 30
    ) {
        $this->apiUrl = $apiUrl ?? 'https://staff.vapeshed.co.nz/assets/services/ai-agent/api/chat.php';
        $this->apiKey = $apiKey ?? getenv('AI_AGENT_API_KEY') ?: 'your-api-key';
        $this->timeout = $timeout;
    }
    
    /**
     * Send message to AI agent
     * 
     * @param string $message User message
     * @param string $provider AI provider (claude|openai)
     * @param array $context Additional context
     * @param string $conversationId Optional conversation ID for continuity
     * @return array Response from AI agent
     */
    public function chat(
        string $message,
        string $provider = 'claude',
        array $context = [],
        ?string $conversationId = null
    ): array {
        $payload = [
            'message' => $message,
            'provider' => $provider,
            'context' => $context,
            'conversation_id' => $conversationId,
            'domain' => 'intelligence', // Use intelligence domain knowledge
            'api_key' => $this->apiKey
        ];
        
        return $this->request('POST', '', $payload);
    }
    
    /**
     * Stream chat response (SSE)
     * 
     * @param string $message User message
     * @param string $provider AI provider
     * @param array $context Additional context
     * @return void Streams SSE events
     */
    public function streamChat(
        string $message,
        string $provider = 'claude',
        array $context = []
    ): void {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        
        $payload = [
            'message' => $message,
            'provider' => $provider,
            'context' => $context,
            'stream' => true,
            'domain' => 'intelligence',
            'api_key' => $this->apiKey
        ];
        
        $url = $this->apiUrl . '?' . http_build_query(['stream' => 'true']);
        $this->streamRequest($url, $payload);
    }
    
    /**
     * Get conversation history
     * 
     * @param string $conversationId Conversation ID
     * @return array Conversation messages
     */
    public function getConversation(string $conversationId): array
    {
        return $this->request('GET', "/conversation/{$conversationId}");
    }
    
    /**
     * Execute tool/command via AI agent
     * 
     * @param string $tool Tool name
     * @param array $params Tool parameters
     * @return array Tool execution result
     */
    public function executeTool(string $tool, array $params = []): array
    {
        $payload = [
            'tool' => $tool,
            'params' => $params,
            'domain' => 'intelligence',
            'api_key' => $this->apiKey
        ];
        
        return $this->request('POST', '/tool/execute', $payload);
    }
    
    /**
     * Search knowledge base
     * 
     * @param string $query Search query
     * @param string $domain Domain to search (intelligence, global, staff)
     * @param int $limit Result limit
     * @return array Search results
     */
    public function searchKnowledge(
        string $query,
        string $domain = 'intelligence',
        int $limit = 10
    ): array {
        $payload = [
            'query' => $query,
            'domain' => $domain,
            'limit' => $limit,
            'api_key' => $this->apiKey
        ];
        
        return $this->request('POST', '/knowledge/search', $payload);
    }
    
    /**
     * Get AI agent analytics
     * 
     * @param string $metric Metric type (conversations, tokens, errors)
     * @param string $timeframe Timeframe (1h, 24h, 7d, 30d)
     * @return array Analytics data
     */
    public function getAnalytics(string $metric = 'conversations', string $timeframe = '24h'): array
    {
        return $this->request('GET', "/analytics/{$metric}?timeframe={$timeframe}");
    }
    
    /**
     * Make HTTP request to AI agent API
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array|null $data Request data
     * @return array Response data
     * @throws RuntimeException On request failure
     */
    private function request(string $method, string $endpoint, ?array $data = null): array
    {
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
                'User-Agent: Intelligence-Hub/1.0'
            ]
        ]);
        
        if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new RuntimeException("AI Agent API request failed: {$error}");
        }
        
        if ($httpCode >= 400) {
            throw new RuntimeException("AI Agent API returned error {$httpCode}: {$response}");
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('AI Agent API returned invalid JSON: ' . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Stream SSE response from AI agent
     * 
     * @param string $url Stream URL
     * @param array $data Request data
     * @return void
     */
    private function streamRequest(string $url, array $data): void
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey
            ],
            CURLOPT_WRITEFUNCTION => function($ch, $data) {
                echo $data;
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                return strlen($data);
            }
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }
}
