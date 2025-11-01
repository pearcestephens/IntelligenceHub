<?php

/**
 * OpenAI API client for Responses, Realtime sessions, and Embeddings
 * Handles rate limiting, error recovery, and token tracking
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

class OpenAI
{
    private const BASE_URL = 'https://api.openai.com/v1';
    private const TIMEOUT = 45;
    private const MAX_RETRIES = 3;

    private static array $metrics = [
        'requests' => 0,
        'tokens_input' => 0,
        'tokens_output' => 0,
        'errors' => 0
    ];

    /**
     * Instance method for chat completion (wraps static API)
     */
    public function __construct(Config|string|null $config = null, ?Logger $logger = null)
    {
        // No-op: static methods use global Config/Logger; constructor exists for DI compatibility.
    }

    /**
     * Instance method for chat completion (wraps static API)
     */
    public function chatCompletion(array $requestData, ?callable $onDelta = null): array
    {
        // Extract messages and tools from request data
        $messages = $requestData['messages'] ?? [];
        $tools = $requestData['tools'] ?? [];
        $model = $requestData['model'] ?? null;
        $stream = (bool)($requestData['stream'] ?? false);

        if ($onDelta !== null || $stream) {
            return self::streamChatCompletion($messages, $tools, $model, $onDelta);
        }

        return self::createChatCompletion($messages, $tools, $model);
    }

    /**
     * Instance method for embeddings (wraps static API)
     */
    public function embedding(string $text, ?string $model = null): array
    {
        return self::createEmbedding($text, $model);
    }

    /**
     * Create chat completion using Responses API
     */
    public static function createChatCompletion(array $messages, array $tools = [], ?string $model = null): array
    {
        $model = $model ?? Config::get('OPENAI_MODEL');

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'stream' => false
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $startTime = microtime(true);

        try {
            $response = self::makeRequest('POST', '/chat/completions', $payload);
            $durationMs = (int)((microtime(true) - $startTime) * 1000);

            self::$metrics['requests']++;
            self::$metrics['tokens_input'] += $response['usage']['prompt_tokens'] ?? 0;
            self::$metrics['tokens_output'] += $response['usage']['completion_tokens'] ?? 0;

            Logger::logOpenAI('chat/completions', $payload, $response, $durationMs, true);

            return $response;
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            self::$metrics['errors']++;

            Logger::logOpenAI('chat/completions', $payload, null, $durationMs, false);
            Logger::error('OpenAI chat completion failed', [
                'error' => $e->getMessage(),
                'model' => $model,
                'tools_count' => count($tools)
            ]);

            throw $e;
        }
    }

    /**
     * Stream chat completion using SSE-like incremental deltas
     */
    public static function streamChatCompletion(array $messages, array $tools = [], ?string $model = null, ?callable $onDelta = null): array
    {
        $model = $model ?? Config::get('OPENAI_MODEL');

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'stream' => true
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $assembled = [
            'content' => '',
            'tool_calls' => [],
            'finish_reason' => null
        ];

        $startTime = microtime(true);

        try {
            self::makeRequestStream('POST', '/chat/completions', $payload, function ($event) use (&$assembled, $onDelta) {
                // $event is already decoded JSON of a single stream chunk
                if (!isset($event['choices'][0])) {
                    return;
                }
                $delta = $event['choices'][0]['delta'] ?? [];
                $finish = $event['choices'][0]['finish_reason'] ?? null;

                if (!empty($delta)) {
                    if (isset($delta['content'])) {
                        $assembled['content'] .= $delta['content'];
                    }
                    if (isset($delta['tool_calls'])) {
                        foreach ($delta['tool_calls'] as $tc) {
                            $index = $tc['index'] ?? 0;
                            if (!isset($assembled['tool_calls'][$index])) {
                                $assembled['tool_calls'][$index] = [
                                    'id' => $tc['id'] ?? '',
                                    'type' => $tc['type'] ?? 'function',
                                    'function' => [
                                        'name' => '',
                                        'arguments' => ''
                                    ]
                                ];
                            }
                            if (isset($tc['function']['name'])) {
                                $assembled['tool_calls'][$index]['function']['name'] .= $tc['function']['name'];
                            }
                            if (isset($tc['function']['arguments'])) {
                                $assembled['tool_calls'][$index]['function']['arguments'] .= $tc['function']['arguments'];
                            }
                        }
                    }
                }

                if ($finish !== null) {
                    $assembled['finish_reason'] = $finish;
                }

                // Propagate to caller
                if ($onDelta) {
                    $onDelta($event);
                }
            });

            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            Logger::logOpenAI('chat/completions(stream)', $payload, null, $durationMs, true);

            // Normalize final response shape to match non-stream
            return [
                'choices' => [[
                    'message' => [
                        'role' => 'assistant',
                        'content' => $assembled['content'],
                        'tool_calls' => array_values($assembled['tool_calls'])
                    ],
                    'finish_reason' => $assembled['finish_reason'] ?? 'stop'
                ]]
            ];
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            Logger::logOpenAI('chat/completions(stream)', $payload, null, $durationMs, false);
            Logger::error('OpenAI chat streaming failed', [
                'error' => $e->getMessage(),
                'model' => $model,
                'tools_count' => count($tools)
            ]);
            throw $e;
        }
    }

    /**
     * Create embeddings
     */
    public static function createEmbedding(string $text, ?string $model = null): array
    {
        $model = $model ?? Config::get('EMBEDDINGS_MODEL');

        $payload = [
            'model' => $model,
            'input' => $text,
            'encoding_format' => 'float'
        ];

        $startTime = microtime(true);

        try {
            $response = self::makeRequest('POST', '/embeddings', $payload);
            $durationMs = (int)((microtime(true) - $startTime) * 1000);

            self::$metrics['requests']++;
            self::$metrics['tokens_input'] += $response['usage']['prompt_tokens'] ?? 0;

            Logger::logOpenAI('embeddings', $payload, $response, $durationMs, true);

            return $response['data'][0]['embedding'] ?? [];
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            self::$metrics['errors']++;

            Logger::logOpenAI('embeddings', $payload, null, $durationMs, false);
            Logger::error('OpenAI embedding failed', [
                'error' => $e->getMessage(),
                'model' => $model,
                'text_length' => strlen($text)
            ]);

            throw $e;
        }
    }

    /**
     * Create realtime session (ephemeral token)
     */
    public static function createRealtimeSession(): array
    {
        $model = Config::get('REALTIME_MODEL');
        $voice = Config::get('REALTIME_VOICE');

        $payload = [
            'model' => $model,
            'voice' => $voice,
            'modalities' => ['text', 'audio'],
            'instructions' => 'You are a helpful AI assistant. Be concise and natural in conversation.',
            'turn_detection' => [
                'type' => 'server_vad',
                'threshold' => 0.5,
                'prefix_padding_ms' => 300,
                'silence_duration_ms' => 500
            ],
            'input_audio_format' => 'pcm16',
            'output_audio_format' => 'pcm16',
            'input_audio_transcription' => [
                'model' => 'whisper-1'
            ]
        ];

        $startTime = microtime(true);

        try {
            $response = self::makeRequest('POST', '/realtime/sessions', $payload);
            $durationMs = (int)((microtime(true) - $startTime) * 1000);

            self::$metrics['requests']++;

            Logger::logOpenAI('realtime/sessions', $payload, $response, $durationMs, true);
            Logger::info('Realtime session created', [
                'session_id' => $response['id'] ?? 'unknown',
                'model' => $model,
                'voice' => $voice
            ]);

            return $response;
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $startTime) * 1000);
            self::$metrics['errors']++;

            Logger::logOpenAI('realtime/sessions', $payload, null, $durationMs, false);
            Logger::error('OpenAI realtime session failed', [
                'error' => $e->getMessage(),
                'model' => $model
            ]);

            throw $e;
        }
    }

    /**
     * Estimate token cost for a completion
     */
    public static function estimateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        // Pricing as of 2025 (per 1K tokens)
        $pricing = [
            'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
            'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
            'gpt-4' => ['input' => 0.03, 'output' => 0.06],
            'text-embedding-3-small' => ['input' => 0.00002, 'output' => 0],
            'text-embedding-3-large' => ['input' => 0.00013, 'output' => 0]
        ];

        $rates = $pricing[$model] ?? ['input' => 0.001, 'output' => 0.002]; // Default fallback

        $inputCost = ($inputTokens / 1000) * $rates['input'];
        $outputCost = ($outputTokens / 1000) * $rates['output'];

        return round($inputCost + $outputCost, 5);
    }

    /**
     * Get API usage metrics
     */
    public static function getMetrics(): array
    {
        return self::$metrics;
    }

    /**
     * Reset metrics (for testing)
     */
    public static function resetMetrics(): void
    {
        self::$metrics = [
            'requests' => 0,
            'tokens_input' => 0,
            'tokens_output' => 0,
            'errors' => 0
        ];
    }

    /**
     * Make HTTP request to OpenAI API with retry logic
     */
    private static function makeRequest(string $method, string $endpoint, array $payload = []): array
    {
        // Validate OpenAI configuration when making actual API requests
        Config::validateOpenAI();

        $url = self::BASE_URL . $endpoint;
        $apiKey = Config::get('OPENAI_API_KEY');

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'User-Agent: PHP-AI-Agent/1.0'
        ];

        $retries = 0;
        $lastException = null;

        while ($retries < self::MAX_RETRIES) {
            try {
                $ch = curl_init();

                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => self::TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_CUSTOMREQUEST => $method,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_FOLLOWLOCATION => false,
                    CURLOPT_MAXREDIRS => 0
                ]);

                if ($method === 'POST' && !empty($payload)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                }

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);

                curl_close($ch);

                if ($curlError) {
                    throw new \RuntimeException("cURL error: {$curlError}");
                }

                if ($response === false) {
                    throw new \RuntimeException('Empty response from OpenAI API');
                }

                $decoded = json_decode($response, true);

                if ($decoded === null) {
                    throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
                }

                // Handle successful responses
                if ($httpCode >= 200 && $httpCode < 300) {
                    return $decoded;
                }

                // Handle rate limits and server errors with retry
                if (in_array($httpCode, [429, 500, 502, 503, 504])) {
                    $retries++;

                    if ($retries < self::MAX_RETRIES) {
                        $delay = min(2 ** $retries, 30) + (random_int(0, 1000) / 1000); // Exponential backoff with jitter

                        Logger::warning('OpenAI API rate limited or server error, retrying', [
                            'http_code' => $httpCode,
                            'retry' => $retries,
                            'delay' => $delay,
                            'endpoint' => $endpoint
                        ]);

                        usleep((int)($delay * 1000000));
                        continue;
                    }
                }

                // Handle client errors (400-499, excluding 429)
                $errorMessage = $decoded['error']['message'] ?? "HTTP {$httpCode}";
                throw new \RuntimeException("OpenAI API error: {$errorMessage}");
            } catch (\Throwable $e) {
                $lastException = $e;

                if ($retries >= self::MAX_RETRIES - 1) {
                    break;
                }

                $retries++;
                $delay = min(2 ** $retries, 10) + (random_int(0, 500) / 1000);

                Logger::warning('OpenAI API request failed, retrying', [
                    'error' => $e->getMessage(),
                    'retry' => $retries,
                    'delay' => $delay,
                    'endpoint' => $endpoint
                ]);

                usleep((int)($delay * 1000000));
            }
        }

        throw $lastException ?? new \RuntimeException('Unknown OpenAI API error');
    }

    /**
     * Streaming HTTP request parser for OpenAI SSE responses
     */
    private static function makeRequestStream(string $method, string $endpoint, array $payload, callable $onEvent): void
    {
        // Validate OpenAI configuration when making actual API requests
        Config::validateOpenAI();

        $url = self::BASE_URL . $endpoint;
        $apiKey = Config::get('OPENAI_API_KEY');

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'Accept: text/event-stream',
            'User-Agent: PHP-AI-Agent/1.0'
        ];

        $ch = curl_init();
        $buffer = '';

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$buffer, $onEvent) {
                $buffer .= $chunk;
                // Process complete lines
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);

                    $trim = trim($line);
                    if ($trim === '' || str_starts_with($trim, ':')) {
                        continue; // comments/keep-alives
                    }
                    if (str_starts_with($trim, 'data:')) {
                        $data = trim(substr($trim, 5));
                        if ($data === '[DONE]') {
                            // Signal end of stream
                            return strlen($chunk);
                        }
                        $decoded = json_decode($data, true);
                        if (is_array($decoded)) {
                            $onEvent($decoded);
                        }
                    }
                }
                return strlen($chunk);
            },
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 0, // allow long-lived stream
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        $result = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL stream error: ' . $error);
        }
        if ($result === false && ($httpCode < 200 || $httpCode >= 300)) {
            throw new \RuntimeException('OpenAI streaming HTTP error: ' . $httpCode);
        }
    }

    /**
     * Validate OpenAI API key
     */
    public static function validateApiKey(): bool
    {
        try {
            // Make a minimal request to validate the API key
            self::makeRequest('GET', '/models');
            return true;
        } catch (\Throwable $e) {
            Logger::error('OpenAI API key validation failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get available models
     */
    public static function getModels(): array
    {
        try {
            $response = self::makeRequest('GET', '/models');

            $models = [];
            foreach ($response['data'] ?? [] as $model) {
                $models[] = [
                    'id' => $model['id'],
                    'owned_by' => $model['owned_by'],
                    'created' => $model['created']
                ];
            }

            return $models;
        } catch (\Throwable $e) {
            Logger::error('Failed to fetch OpenAI models', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Responses API (incremental shim for Responses endpoint)
     */
    public static function responses(array $request): array
    {
        // Convert Responses format to Chat Completions format
        $messages = $request['input'] ?? [];
        $tools = $request['tools'] ?? [];
        $model = $request['model'] ?? Config::get('OPENAI_MODEL');

        $response = self::createChatCompletion($messages, $tools, $model);

        // Convert response back to Responses format
        return [
            'output' => [
                'text' => $response['choices'][0]['message']['content'] ?? '',
                'tool_calls' => $response['choices'][0]['message']['tool_calls'] ?? []
            ],
            'usage' => $response['usage'] ?? []
        ];
    }

    /**
     * Responses API with streaming (incremental shim)
     */
    public static function responsesStream(array $request, callable $onEvent): array
    {
        // For now, fallback to non-streaming and emit events manually
        $response = self::responses($request);

        // Emit text delta
        if (!empty($response['output']['text'])) {
            $onEvent([
                'type' => 'response.output_text.delta',
                'delta' => $response['output']['text']
            ]);
        }

        // Emit tool calls if any
        foreach ($response['output']['tool_calls'] ?? [] as $toolCall) {
            $onEvent([
                'type' => 'response.tool_call.delta',
                'delta' => $toolCall
            ]);
        }

        return $response;
    }
}
