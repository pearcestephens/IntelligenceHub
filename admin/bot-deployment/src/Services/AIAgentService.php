<?php
/**
 * AI Agent Service
 *
 * Client for communicating with Intelligence Hub MCP Server
 * Handles API calls to gpt.ecigdis.co.nz with retry logic,
 * rate limiting, timeout handling, and response parsing.
 *
 * @package BotDeployment\Services
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Services;

use BotDeployment\Config\Config;
use BotDeployment\Models\Bot;

class AIAgentService
{
    /**
     * Configuration array
     */
    private array $config;

    /**
     * Last API response time in milliseconds
     */
    private ?float $lastResponseTime = null;

    /**
     * Request counter for rate limiting
     */
    private array $requestLog = [];

    /**
     * Constructor
     */
    public function __construct(?array $config = null)
    {
        $this->config = $config ?? Config::aiAgent();
    }

    /**
     * Send a query to the AI Agent with context and tools
     *
     * @param Bot    $bot           Bot making the request
     * @param string $query         The query/prompt to send
     * @param array  $context       Additional context data
     * @param array  $tools         Specific MCP tools to use
     * @param bool   $stream        Whether to use streaming (default true)
     * @return array Response data
     * @throws \Exception If API call fails after retries
     */
    public function query(
        Bot $bot,
        string $query,
        array $context = [],
        array $tools = [],
        bool $stream = true
    ): array {
        // Rate limit check
        $this->checkRateLimit();

        // Build request payload
        $payload = [
            'query' => $query,
            'bot_id' => $bot->getBotId(),
            'bot_name' => $bot->getBotName(),
            'bot_role' => $bot->getBotRole(),
            'system_prompt' => $bot->getSystemPrompt(),
            'context' => $context,
            'tools' => $tools,
            'stream' => $stream,
            'config' => array_merge(
                $bot->getConfig(),
                ['timestamp' => time()]
            )
        ];

        // Execute with retry logic
        $maxRetries = $this->config->get('aiAgent.maxRetries', 3);
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $attempt++;

                // Calculate backoff delay for retries
                if ($attempt > 1) {
                    $delay = $this->calculateBackoff($attempt);
                    usleep($delay * 1000); // Convert ms to microseconds
                }

                // Make API call
                $startTime = microtime(true);
                $response = $this->makeRequest('/api/query', $payload);
                $this->lastResponseTime = (microtime(true) - $startTime) * 1000;

                // Log request
                $this->logRequest(true, $this->lastResponseTime);

                return $response;

            } catch (\Exception $e) {
                $lastException = $e;

                // Don't retry on certain errors
                if ($this->isNonRetryableError($e)) {
                    break;
                }

                // Last attempt failed
                if ($attempt >= $maxRetries) {
                    break;
                }
            }
        }

        // All retries failed
        $this->logRequest(false, 0, $lastException);

        throw new \Exception(
            "AI Agent API call failed after {$attempt} attempts: " .
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Get conversation context from MCP memory system
     *
     * @param string $conversationId Conversation ID to retrieve
     * @return array Conversation context data
     */
    public function getConversationContext(string $conversationId): array
    {
        $payload = [
            'tool' => 'conversation.get_context',
            'params' => [
                'conversation_id' => $conversationId
            ]
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Store memory in MCP system
     *
     * @param string $conversationId Conversation ID
     * @param string $content        Content to store
     * @param string $memoryType     Type of memory
     * @param string $importance     Importance level
     * @param array  $tags           Tags for categorization
     * @return array Storage result
     */
    public function storeMemory(
        string $conversationId,
        string $content,
        string $memoryType = 'general',
        string $importance = 'medium',
        array $tags = []
    ): array {
        $payload = [
            'tool' => 'memory.store',
            'params' => [
                'conversation_id' => $conversationId,
                'content' => $content,
                'memory_type' => $memoryType,
                'importance' => $importance,
                'tags' => $tags
            ]
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Search knowledge base
     *
     * @param string $query  Search query
     * @param array  $filters Optional filters
     * @return array Search results
     */
    public function searchKnowledgeBase(string $query, array $filters = []): array
    {
        $payload = [
            'tool' => 'kb.search',
            'params' => array_merge(['query' => $query], $filters)
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Execute semantic search across codebase
     *
     * @param string $query Search query
     * @param int    $limit Max results
     * @return array Search results with file paths and relevance scores
     */
    public function semanticSearch(string $query, int $limit = 10): array
    {
        $payload = [
            'tool' => 'semantic_search',
            'params' => [
                'query' => $query,
                'limit' => $limit
            ]
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Execute database query via MCP
     *
     * @param string $sql    SQL query
     * @param array  $params Query parameters
     * @return array Query results
     */
    public function dbQuery(string $sql, array $params = []): array
    {
        $payload = [
            'tool' => 'db.query',
            'params' => [
                'query' => $sql,
                'params' => $params
            ]
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Read file contents via MCP
     *
     * @param string $filePath File path to read
     * @return array File content and metadata
     */
    public function readFile(string $filePath): array
    {
        $payload = [
            'tool' => 'fs.read',
            'params' => ['path' => $filePath]
        ];

        return $this->makeRequest('/api/tool', $payload);
    }

    /**
     * Get AI Agent health status
     *
     * @return array Health check results
     */
    public function healthCheck(): array
    {
        try {
            $response = $this->makeRequest('/api/health', []);
            return [
                'status' => 'healthy',
                'response_time' => $this->lastResponseTime,
                'details' => $response
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get last response time in milliseconds
     */
    public function getLastResponseTime(): ?float
    {
        return $this->lastResponseTime;
    }

    /**
     * Make HTTP request to AI Agent API
     *
     * @param string $endpoint API endpoint
     * @param array  $payload  Request payload
     * @return array Decoded response
     * @throws \Exception On request failure
     */
    private function makeRequest(string $endpoint, array $payload): array
    {
        $baseUrl = $this->config['endpoint'] ?? 'https://gpt.ecigdis.co.nz/ai-agent/api/chat.php';
        $apiKey = $this->config['api_key'] ?? '';
        $timeout = $this->config['timeout'] ?? 30;

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        // Initialize cURL
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey,
                'User-Agent: BotDeployment/1.0'
            ]
        ]);

        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false) {
            throw new \Exception("cURL error: {$curlError}");
        }

        // Decode response
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON response: " . json_last_error_msg());
        }

        // Check HTTP status
        if ($httpCode >= 400) {
            $errorMsg = $data['error'] ?? $data['message'] ?? 'Unknown error';
            throw new \Exception("API error (HTTP {$httpCode}): {$errorMsg}");
        }

        return $data;
    }

    /**
     * Check rate limit and throttle if necessary
     *
     * @throws \Exception If rate limit exceeded
     */
    private function checkRateLimit(): void
    {
        $maxRequests = $this->config->get('aiAgent.rateLimit.maxRequests', 60);
        $timeWindow = $this->config->get('aiAgent.rateLimit.timeWindow', 60);

        // Clean old requests outside time window
        $cutoff = time() - $timeWindow;
        $this->requestLog = array_filter(
            $this->requestLog,
            fn($timestamp) => $timestamp > $cutoff
        );

        // Check if limit exceeded
        if (count($this->requestLog) >= $maxRequests) {
            throw new \Exception(
                "Rate limit exceeded: {$maxRequests} requests per {$timeWindow} seconds"
            );
        }
    }

    /**
     * Log request for rate limiting
     */
    private function logRequest(
        bool $success,
        float $responseTime,
        ?\Exception $error = null
    ): void {
        $this->requestLog[] = time();

        // TODO: Integrate with Logger class when available
        // For now, just track in memory for rate limiting
    }

    /**
     * Calculate exponential backoff delay in milliseconds
     */
    private function calculateBackoff(int $attempt): int
    {
        $baseDelay = $this->config->get('aiAgent.retryDelay', 1000);
        $maxDelay = $this->config->get('aiAgent.maxRetryDelay', 30000);

        // Exponential backoff with jitter
        $delay = min($baseDelay * pow(2, $attempt - 1), $maxDelay);
        $jitter = rand(0, (int)($delay * 0.1)); // 10% jitter

        return (int)($delay + $jitter);
    }

    /**
     * Check if error should not be retried
     */
    private function isNonRetryableError(\Exception $e): bool
    {
        $message = $e->getMessage();

        // Don't retry on validation errors, auth errors, etc.
        $nonRetryablePatterns = [
            '/invalid.*api.*key/i',
            '/unauthorized/i',
            '/forbidden/i',
            '/bad.*request/i',
            '/validation.*failed/i',
            '/rate.*limit/i'
        ];

        foreach ($nonRetryablePatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }
}
