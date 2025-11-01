<?php

declare(strict_types=1);

namespace App;

use App\Tools\ToolRegistry;
use App\Tools\ToolExecutor;
use App\Memory\ContextCards;
use App\Memory\Summarizer;
use App\Util\Validate;
use App\Util\Errors;
use App\Config;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * MessageHandler - Processes messages, manages OpenAI interactions, and orchestrates tool calling
 *
 * Provides enterprise-grade message processing with:
 * - OpenAI chat completion integration with streaming
 * - Tool calling orchestration with parallel execution
 * - Context management and memory integration
 * - Error handling and recovery
 * - Progress tracking via Server-Sent Events
 * - Rate limiting and safety controls
 *
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */
class MessageHandler
{
    private OpenAI $openai;
    private ConversationManager $conversationManager;
    private ToolRegistry $toolRegistry;
    private ToolExecutor $toolExecutor;
    private ContextCards $contextCards;
    private Summarizer $summarizer;
    private Logger $logger;
    private Config $config;
    private SSE $sse;

    /** Default model for chat completion */
    private const DEFAULT_MODEL = 'gpt-4-turbo-preview';

    /** Maximum retry attempts for tool calls */
    private const MAX_TOOL_RETRIES = 3;

    /** Context window management */
    private const MAX_CONTEXT_TOKENS = 32000;
    private const RESERVE_TOKENS = 8000;

    public function __construct(
        OpenAI $openai,
        ConversationManager $conversationManager,
        ToolRegistry $toolRegistry,
        ToolExecutor $toolExecutor,
        ContextCards $contextCards,
        Summarizer $summarizer,
        Logger $logger,
        Config $config,
        SSE $sse
    ) {
        $this->openai = $openai;
        $this->conversationManager = $conversationManager;
        $this->toolRegistry = $toolRegistry;
        $this->toolExecutor = $toolExecutor;
        $this->contextCards = $contextCards;
        $this->summarizer = $summarizer;
        $this->logger = $logger;
        $this->config = $config;
        $this->sse = $sse;
    }

    /**
     * Process user message and generate AI response with tool calling
     */
    public function processMessage(
        string $conversationId,
        string $userMessage,
        array $options = []
    ): array {
        $startTime = microtime(true);

        try {
            // Validate inputs
            $conversationId = Validate::string($conversationId, 'conversation_id', 1, 100);
            $userMessage = Validate::string($userMessage, 'user_message', 1, 100000);
            $options = Validate::array($options, 'options');

            $model = $options['model'] ?? self::DEFAULT_MODEL;
            $stream = $options['stream'] ?? false;
            $toolsEnabled = $options['tools'] ?? true;

            $this->logger->info('Processing message', [
                'conversation_id' => $conversationId,
                'message_length' => strlen($userMessage),
                'model' => $model,
                'stream' => $stream,
                'tools_enabled' => $toolsEnabled
            ]);

            // Add user message to conversation
            $userMessageId = $this->conversationManager->addMessage(
                $conversationId,
                'user',
                $userMessage
            );

            // Emit progress event
            $this->sse->send([
                'type' => 'message_added',
                'data' => [
                    'conversation_id' => $conversationId,
                    'message_id' => $userMessageId,
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ]);

            // Build conversation context
            $context = $this->buildContext($conversationId, $model);

            // Generate AI response
            $response = $this->generateResponse($conversationId, $context, [
                'model' => $model,
                'stream' => $stream,
                'tools_enabled' => $toolsEnabled,
                'user_message' => $userMessage
            ]);

            $processingTime = microtime(true) - $startTime;

            $this->logger->info('Message processed successfully', [
                'conversation_id' => $conversationId,
                'processing_time' => $processingTime,
                'response_message_id' => $response['message_id'] ?? null,
                'tool_calls' => count($response['tool_calls'] ?? [])
            ]);

            return [
                'success' => true,
                'conversation_id' => $conversationId,
                'user_message_id' => $userMessageId,
                'response' => $response,
                'processing_time' => $processingTime
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to process message', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Emit error event
            $this->sse->send([
                'type' => 'error',
                'data' => [
                    'conversation_id' => $conversationId,
                    'message' => 'Failed to process message: ' . $e->getMessage()
                ]
            ]);

            throw Errors::processingError('Message processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate AI response with tool calling support
     */
    private function generateResponse(
        string $conversationId,
        array $context,
        array $options = []
    ): array {
        $model = $options['model'] ?? self::DEFAULT_MODEL;
        $stream = $options['stream'] ?? false;
        $toolsEnabled = $options['tools_enabled'] ?? true;

        // Prepare messages for OpenAI
        $messages = $this->prepareMessagesForOpenAI($context);

        // Prepare tools if enabled
        $tools = [];
        if ($toolsEnabled) {
            $tools = \App\Tools\ToolRegistry::getOpenAISchema();
        }

        $requestData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => min(4000, self::MAX_CONTEXT_TOKENS - $this->estimateTokens($messages))
        ];

        if (!empty($tools)) {
            $requestData['tools'] = $tools;
            $requestData['tool_choice'] = 'auto';
        }

        $this->sse->send([
            'type' => 'ai_thinking',
            'data' => [
                'conversation_id' => $conversationId,
                'model' => $model,
                'tools_available' => count($tools)
            ]
        ]);

            // Offline/dev fallback when OpenAI is not configured
        if (!Config::get('OPENAI_API_KEY')) {
            $content = '[dev] OpenAI disabled. You said: ' . ($options['user_message'] ?? '');
            $messageId = $this->conversationManager->addMessage($conversationId, 'assistant', $content, []);
            $this->sse->send([
                'type' => 'message_complete',
                'data' => [
                    'conversation_id' => $conversationId,
                    'message_id' => $messageId,
                    'content' => $content,
                    'tool_calls' => []
                ]
            ]);
            return [
                'message_id' => $messageId,
                'content' => $content,
                'tool_calls' => [],
                'finish_reason' => 'stop'
            ];
        }

            // Call OpenAI API
        if ($stream) {
            return $this->handleStreamingResponse($conversationId, $requestData);
        } else {
            return $this->handleRegularResponse($conversationId, $requestData);
        }
    }

    /**
     * Handle regular (non-streaming) OpenAI response
     */
    private function handleRegularResponse(string $conversationId, array $requestData): array
    {
        $response = $this->openai->chatCompletion($requestData);

            $choice = $response['choices'][0] ?? null;
        if (!$choice) {
            throw new Exception('No response choice from OpenAI');
        }

        $message = $choice['message'];
        $content = $message['content'] ?? '';
        $toolCalls = $message['tool_calls'] ?? [];

        // Process tool calls if present
        $processedToolCalls = [];
        if (!empty($toolCalls)) {
            $processedToolCalls = $this->processToolCalls($conversationId, $toolCalls);

            // If tool calls were made, generate follow-up response
            if (!empty($processedToolCalls)) {
                return $this->generateFollowUpResponse($conversationId, $content, $processedToolCalls, $requestData);
            }
        }

        // Add assistant message to conversation
        $messageId = $this->conversationManager->addMessage(
            $conversationId,
            'assistant',
            $content,
            $processedToolCalls
        );

        $this->sse->send([
            'type' => 'message_complete',
            'data' => [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'content' => $content,
                'tool_calls' => $processedToolCalls
            ]
        ]);

        return [
            'message_id' => $messageId,
            'content' => $content,
            'tool_calls' => $processedToolCalls,
            'finish_reason' => $choice['finish_reason'] ?? 'stop'
        ];
    }

    /**
     * Handle streaming OpenAI response
     */
    private function handleStreamingResponse(string $conversationId, array $requestData): array
    {
        $requestData['stream'] = true;

        $fullContent = '';
        $toolCalls = [];

        $callback = function ($chunk) use (&$fullContent, &$toolCalls, $conversationId) {
            if (isset($chunk['choices'][0]['delta'])) {
                $delta = $chunk['choices'][0]['delta'];

                if (isset($delta['content'])) {
                    $fullContent .= $delta['content'];
                    $this->sse->send([
                        'type' => 'content_chunk',
                        'data' => [
                            'conversation_id' => $conversationId,
                            'content' => $delta['content']
                        ]
                    ]);
                }

                if (isset($delta['tool_calls'])) {
                    foreach ($delta['tool_calls'] as $tc) {
                        $index = $tc['index'] ?? 0;
                        if (!isset($toolCalls[$index])) {
                            $toolCalls[$index] = [
                                'id' => $tc['id'] ?? '',
                                'type' => $tc['type'] ?? 'function',
                                'function' => [ 'name' => '', 'arguments' => '' ]
                            ];
                        }
                        if (isset($tc['function']['name'])) {
                            $toolCalls[$index]['function']['name'] .= $tc['function']['name'];
                        }
                        if (isset($tc['function']['arguments'])) {
                            $toolCalls[$index]['function']['arguments'] .= $tc['function']['arguments'];
                        }
                    }
                    $this->sse->send([
                        'type' => 'tool_call_chunk',
                        'data' => [
                            'conversation_id' => $conversationId,
                            'tool_calls' => array_values($toolCalls)
                        ]
                    ]);
                }
            }
        };

        // Announce assistant message placeholder so UI can append chunks
        $this->sse->send([
            'type' => 'message_added',
            'data' => [
                'conversation_id' => $conversationId,
                'role' => 'assistant',
                'content' => '',
                'created_at' => date('c')
            ]
        ]);

        // Stream response
        $response = $this->openai->chatCompletion($requestData, $callback);

        // Process tool calls if present
        $processedToolCalls = [];
        if (!empty($toolCalls)) {
            $processedToolCalls = $this->processToolCalls($conversationId, array_values($toolCalls));

            if (!empty($processedToolCalls)) {
                return $this->generateFollowUpResponse($conversationId, $fullContent, $processedToolCalls, $requestData);
            }
        }

        // Add assistant message to conversation
        $messageId = $this->conversationManager->addMessage(
            $conversationId,
            'assistant',
            $fullContent,
            $processedToolCalls
        );

        $this->sse->send([
            'type' => 'message_complete',
            'data' => [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'content' => $fullContent,
                'tool_calls' => $processedToolCalls
            ]
        ]);

        return [
            'message_id' => $messageId,
            'content' => $fullContent,
            'tool_calls' => $processedToolCalls,
            'finish_reason' => 'stop'
        ];
    }

    /**
     * Process tool calls from OpenAI response
     */
    private function processToolCalls(string $conversationId, array $toolCalls): array
    {
        $processedCalls = [];

        $this->sse->send([
            'type' => 'tool_execution_start',
            'data' => [
                'conversation_id' => $conversationId,
                'tool_count' => count($toolCalls),
                'tools' => array_map(fn($tc) => $tc['function']['name'] ?? 'unknown', $toolCalls)
            ]
        ]);

        foreach ($toolCalls as $toolCall) {
            try {
                $toolName = $toolCall['function']['name'] ?? '';
                $arguments = json_decode($toolCall['function']['arguments'] ?? '{}', true);

                $this->sse->send([
                    'type' => 'tool_execution',
                    'data' => [
                        'conversation_id' => $conversationId,
                        'tool_call_id' => $toolCall['id'] ?? '',
                        'tool_name' => $toolName,
                        'arguments' => $arguments
                    ]
                ]);

                // Execute tool (ToolExecutor::execute expects (string $toolName, array $parameters = []))
                $arguments['_context'] = [
                    'conversation_id' => $conversationId,
                    'tool_call_id' => $toolCall['id'] ?? ''
                ];
                $result = $this->toolExecutor->execute($toolName, $arguments);

                $processedCalls[] = [
                    'id' => $toolCall['id'] ?? '',
                    'tool' => $toolName,
                    'function' => $toolName,
                    'arguments' => $arguments,
                    'result' => $result,
                    'status' => $result['success'] ? 'completed' : 'failed'
                ];

                $this->sse->send([
                    'type' => 'tool_result',
                    'data' => [
                        'conversation_id' => $conversationId,
                        'tool_call_id' => $toolCall['id'] ?? '',
                        'success' => $result['success'],
                        'result' => $result
                    ]
                ]);
            } catch (Exception $e) {
                $this->logger->error('Tool execution failed', [
                    'tool_call_id' => $toolCall['id'] ?? '',
                    'tool_name' => $toolName ?? 'unknown',
                    'error' => $e->getMessage()
                ]);

                $processedCalls[] = [
                    'id' => $toolCall['id'] ?? '',
                    'tool' => $toolName ?? 'unknown',
                    'function' => $toolName ?? 'unknown',
                    'arguments' => $arguments ?? [],
                    'result' => ['success' => false, 'error' => $e->getMessage()],
                    'status' => 'failed'
                ];
            }
        }

        $this->sse->send([
            'type' => 'tool_execution_complete',
            'data' => [
                'conversation_id' => $conversationId,
                'processed_calls' => count($processedCalls),
                'successful_calls' => count(array_filter($processedCalls, fn($c) => $c['status'] === 'completed'))
            ]
        ]);

        return $processedCalls;
    }

    /**
     * Generate follow-up response after tool execution
     */
    private function generateFollowUpResponse(
        string $conversationId,
        string $initialContent,
        array $toolCalls,
        array $originalRequestData
    ): array {
        // Add initial assistant message with tool calls
        $this->conversationManager->addMessage(
            $conversationId,
            'assistant',
            $initialContent,
            $toolCalls
        );

        // Add tool results as tool messages
        foreach ($toolCalls as $toolCall) {
            $this->conversationManager->addMessage(
                $conversationId,
                'tool',
                json_encode($toolCall['result']),
                [],
                ['tool_call_id' => $toolCall['id']]
            );
        }

        // Build updated context
        $context = $this->buildContext($conversationId, $originalRequestData['model']);
        $messages = $this->prepareMessagesForOpenAI($context);

        // Generate follow-up response
        $requestData = array_merge($originalRequestData, [
            'messages' => $messages,
            'tools' => [], // No tools for follow-up
            'tool_choice' => null
        ]);

        $this->sse->send([
            'type' => 'followup_generation',
            'data' => [
                'tool_calls_processed' => count($toolCalls)
            ]
        ]);

        $response = $this->openai->chatCompletion($requestData);

        $choice = $response['choices'][0] ?? null;
        if (!$choice) {
            throw new Exception('No follow-up response from OpenAI');
        }

        $followUpContent = $choice['message']['content'] ?? '';

        // Add follow-up message
        $messageId = $this->conversationManager->addMessage(
            $conversationId,
            'assistant',
            $followUpContent
        );

        $this->sse->send([
            'type' => 'message_complete',
            'data' => [
                'conversation_id' => $conversationId,
                'message_id' => $messageId,
                'content' => $followUpContent,
                'tool_calls' => $toolCalls,
                'is_followup' => true
            ]
        ]);

        return [
            'message_id' => $messageId,
            'content' => $followUpContent,
            'tool_calls' => $toolCalls,
            'finish_reason' => $choice['finish_reason'] ?? 'stop',
            'is_followup' => true
        ];
    }

    /**
     * Build conversation context with memory integration
     */
    private function buildContext(string $conversationId, string $model): array
    {
        // Get recent messages
        $messages = $this->conversationManager->getMessages($conversationId);

        // Get conversation info
        $conversation = $this->conversationManager->getConversation($conversationId);

        // Build context with memory
        $context = [
            'system_prompt' => \App\Memory\ContextCards::buildSystemPrompt(),
            'messages' => $messages,
            'conversation' => $conversation,
            'model' => $model
        ];

        // Optimize for token limits
        $context = $this->optimizeContextForTokens($context, $model);

        return $context;
    }

    /**
     * Prepare messages for OpenAI API format
     */
    private function prepareMessagesForOpenAI(array $context): array
    {
        $messages = [];

        // Add system message if present
        if (isset($context['system_prompt'])) {
            $messages[] = [
                'role' => 'system',
                'content' => $context['system_prompt']
            ];
        }

        // Add conversation messages
        foreach ($context['messages'] as $message) {
            $openaiMessage = [
                'role' => $message['role'],
                'content' => $message['content']
            ];

            // Add tool calls if present
            if (!empty($message['tool_calls'])) {
                $openaiMessage['tool_calls'] = array_map(function ($tc) {
                    return [
                        'id' => $tc['id'],
                        'type' => 'function',
                        'function' => [
                            'name' => $tc['function'],
                            'arguments' => json_encode($tc['arguments'])
                        ]
                    ];
                }, $message['tool_calls']);
            }

            $messages[] = $openaiMessage;
        }

        return $messages;
    }

    /**
     * Optimize context for token limits
     */
    private function optimizeContextForTokens(array $context, string $model): array
    {
        $estimatedTokens = $this->estimateTokens($context['messages']);

        if ($estimatedTokens <= self::MAX_CONTEXT_TOKENS - self::RESERVE_TOKENS) {
            return $context;
        }

        // Truncate messages if too long
        $messages = $context['messages'];
        $systemPrompt = $context['system_prompt'] ?? '';

        // Keep system prompt and recent messages
        $optimizedMessages = [];
        $tokenCount = $this->estimateTokens([$systemPrompt]);

        // Add messages from newest to oldest
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            $messageTokens = $this->estimateTokens([$messages[$i]]);

            if ($tokenCount + $messageTokens > self::MAX_CONTEXT_TOKENS - self::RESERVE_TOKENS) {
                break;
            }

            array_unshift($optimizedMessages, $messages[$i]);
            $tokenCount += $messageTokens;
        }

        // Summarize older messages if needed
        if (count($optimizedMessages) < count($messages)) {
            $olderMessages = array_slice($messages, 0, count($messages) - count($optimizedMessages));
            $summary = $this->summarizer->summarizeMessages($olderMessages);

            // Add summary as system context
            $context['system_prompt'] = $systemPrompt . "\n\nPrevious conversation summary:\n" . $summary;
        }

        $context['messages'] = $optimizedMessages;

        $this->logger->info('Optimized context for tokens', [
            'original_messages' => count($messages),
            'optimized_messages' => count($optimizedMessages),
            'estimated_tokens' => $this->estimateTokens($optimizedMessages)
        ]);

        return $context;
    }

    /**
     * Estimate token count for messages
     */
    private function estimateTokens($data): int
    {
        if (is_array($data)) {
            $text = json_encode($data);
        } else {
            $text = (string)$data;
        }

        // Rough estimation: 1 token ≈ 4 characters for English text
        return (int)ceil(strlen($text) / 4);
    }

    /**
     * Get message processing statistics
     */
    public function getProcessingStats(): array
    {
        return [
            'default_model' => self::DEFAULT_MODEL,
            'max_context_tokens' => self::MAX_CONTEXT_TOKENS,
            'reserve_tokens' => self::RESERVE_TOKENS,
            'max_tool_retries' => self::MAX_TOOL_RETRIES,
            'available_tools' => $this->toolRegistry->getAvailableTools()
        ];
    }
}
