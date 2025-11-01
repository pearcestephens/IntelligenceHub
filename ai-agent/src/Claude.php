<?php

declare(strict_types=1);

namespace App;

use App\Config;
use App\Logger;
use App\Util\Errors;

/**
 * Claude AI Client - Anthropic Claude Integration
 *
 * Provides Claude AI capabilities with streaming support for the AI agent system.
 * Handles conversation management, tool calling, and streaming responses.
 *
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */
class Claude
{
    private Config $config;
    private Logger $logger;
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private string $apiUrl = 'https://api.anthropic.com/v1/messages';
    private array $defaultHeaders;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;

        $this->apiKey = $config->get('ANTHROPIC_API_KEY');
        if (!$this->apiKey || $this->apiKey === 'YOUR_CLAUDE_API_KEY_HERE') {
            throw Errors::internalError('ANTHROPIC_API_KEY not configured');
        }

        $this->model = $config->get('CLAUDE_MODEL', 'claude-3-5-sonnet-20241022');
        $this->maxTokens = (int)$config->get('CLAUDE_MAX_TOKENS', '4096');

        $this->defaultHeaders = [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey,
            'Anthropic-Version: 2023-06-01'
        ];

        $this->logger->info('Claude client initialized', [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens
        ]);
    }

    /**
     * Generate streaming chat completion
     */
    public function streamCompletion(array $messages, ?callable $callback = null): \Generator
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->formatMessages($messages),
            'stream' => true
        ];

        $this->logger->debug('Claude streaming request', [
            'model' => $this->model,
            'message_count' => count($messages)
        ]);

        $ch = curl_init();

        // Initialize response buffer
        $responseBuffer = '';

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$responseBuffer) {
                $responseBuffer .= $data;
                return strlen($data);
            },
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($result === false || $error) {
            $this->logger->error('Claude API curl error', [
                'error' => $error,
                'http_code' => $httpCode
            ]);
            throw Errors::apiError('Claude API request failed: ' . $error);
        }

        if ($httpCode >= 400) {
            $this->logger->error('Claude API HTTP error', [
                'http_code' => $httpCode,
                'response' => $responseBuffer
            ]);
            throw Errors::apiError("Claude API returned HTTP $httpCode");
        }

        // Process the streaming response
        yield from $this->processStreamData($responseBuffer, $callback);
    }

    /**
     * Generate non-streaming completion
     */
    public function completion(array $messages): array
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->formatMessages($messages),
            'stream' => false
        ];

        $this->logger->debug('Claude completion request', [
            'model' => $this->model,
            'message_count' => count($messages)
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error) {
            $this->logger->error('Claude API curl error', [
                'error' => $error,
                'http_code' => $httpCode
            ]);
            throw Errors::apiError('Claude API request failed: ' . $error);
        }

        if ($httpCode >= 400) {
            $this->logger->error('Claude API HTTP error', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            throw Errors::apiError("Claude API returned HTTP $httpCode");
        }

        $result = json_decode($response, true);
        if (!$result) {
            throw Errors::apiError('Invalid JSON response from Claude API');
        }

        $this->logger->debug('Claude completion success', [
            'usage' => $result['usage'] ?? null
        ]);

        return $result;
    }

    /**
     * Format messages for Claude API
     */
    private function formatMessages(array $messages): array
    {
        $formatted = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'assistant' : 'user';

            $formatted[] = [
                'role' => $role,
                'content' => $message['content']
            ];
        }

        return $formatted;
    }

    /**
     * Process streaming data and yield chunks
     */
    private function processStreamData(string $data, ?callable $callback): \Generator
    {
        $lines = explode("\n", $data);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !str_starts_with($line, 'data: ')) {
                continue;
            }

            $jsonData = substr($line, 6); // Remove 'data: '
            if ($jsonData === '[DONE]') {
                break;
            }

            $decoded = json_decode($jsonData, true);
            if ($decoded && isset($decoded['delta']['text'])) {
                $content = $decoded['delta']['text'];
                if ($callback) {
                    $callback($content);
                }
                yield $content;
            }
        }
    }

    /**
     * Test Claude API connection
     */
    public function test(): array
    {
        try {
            $testMessages = [
                [
                    'role' => 'user',
                    'content' => 'Hello! Please respond with "Claude API test successful" to confirm the connection is working.'
                ]
            ];

            $response = $this->completion($testMessages);

            return [
                'success' => true,
                'model' => $this->model,
                'response' => $response['content'][0]['text'] ?? 'No response content',
                'usage' => $response['usage'] ?? null
            ];
        } catch (\Exception $e) {
            $this->logger->error('Claude API test failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get available models
     */
    public function getModels(): array
    {
        return [
            'claude-3-5-sonnet-20241022' => [
                'name' => 'Claude 3.5 Sonnet',
                'context_window' => 200000,
                'max_output' => 8192,
                'description' => 'Most intelligent model, best for complex tasks'
            ],
            'claude-3-5-haiku-20241022' => [
                'name' => 'Claude 3.5 Haiku',
                'context_window' => 200000,
                'max_output' => 8192,
                'description' => 'Fastest model, good for simple tasks'
            ],
            'claude-3-opus-20240229' => [
                'name' => 'Claude 3 Opus',
                'context_window' => 200000,
                'max_output' => 4096,
                'description' => 'Most capable model for highly complex tasks'
            ]
        ];
    }

    /**
     * Get current model info
     */
    public function getModelInfo(): array
    {
        $models = $this->getModels();
        return $models[$this->model] ?? [
            'name' => $this->model,
            'description' => 'Custom model'
        ];
    }
}
