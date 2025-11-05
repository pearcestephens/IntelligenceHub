<?php

namespace IntelligenceHub\AI;

use IntelligenceHub\Config\Connection;
use IntelligenceHub\Services\Logger;
use Exception;

/**
 * AI Decision Engine
 *
 * Core AI brain powered by GPT-4 for autonomous decision-making
 * across all business operations.
 *
 * Features:
 * - Natural language command processing
 * - Context-aware decision making
 * - Function calling for agent actions
 * - Memory management
 * - Learning and adaptation
 */
class DecisionEngine
{
    private $logger;
    private $db;
    private $apiKey;
    private $model = 'gpt-4-turbo-preview';
    private $context = [];
    private $conversationHistory = [];

    // Decision confidence thresholds
    const CONFIDENCE_AUTO = 0.9;     // Auto-execute without approval
    const CONFIDENCE_RECOMMEND = 0.7; // Recommend to user
    const CONFIDENCE_SUGGEST = 0.5;   // Suggest as option

    public function __construct()
    {
        $this->logger = new Logger('ai-decision-engine');
        $this->db = Connection::getInstance();
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';

        if (empty($this->apiKey)) {
            $this->logger->warning("OpenAI API key not configured");
        }

        $this->loadContext();
    }

    /**
     * Process natural language command
     *
     * @param string $command User's natural language input
     * @param array $context Additional context
     * @return array Decision result with actions
     */
    public function processCommand(string $command, array $context = []): array
    {
        try {
            $this->logger->info("Processing command", [
                'command' => $command,
                'context' => $context
            ]);

            // Add to conversation history
            $this->conversationHistory[] = [
                'role' => 'user',
                'content' => $command
            ];

            // Build system prompt with business context
            $systemPrompt = $this->buildSystemPrompt($context);

            // Prepare messages for GPT-4
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$this->conversationHistory
            ];

            // Define available functions for agents
            $functions = $this->getAvailableFunctions();

            // Call GPT-4 with function calling
            $response = $this->callGPT4($messages, $functions);

            // Process response
            $result = $this->processGPT4Response($response);

            // Add assistant response to history
            $this->conversationHistory[] = [
                'role' => 'assistant',
                'content' => $result['response']
            ];

            // Store decision in database
            $this->storeDecision($command, $result);

            return $result;

        } catch (Exception $e) {
            $this->logger->error("Command processing failed", [
                'command' => $command,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => "I encountered an error processing your request. Please try again."
            ];
        }
    }

    /**
     * Make autonomous decision
     *
     * @param string $situation Description of situation requiring decision
     * @param array $options Available options
     * @param array $data Relevant data for decision
     * @return array Decision with confidence score
     */
    public function makeDecision(string $situation, array $options, array $data = []): array
    {
        try {
            $this->logger->info("Making autonomous decision", [
                'situation' => $situation,
                'options' => $options
            ]);

            $prompt = $this->buildDecisionPrompt($situation, $options, $data);

            $messages = [
                ['role' => 'system', 'content' => $this->buildSystemPrompt()],
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $this->callGPT4($messages);

            $decision = $this->parseDecision($response);

            // Store decision
            $this->storeDecision($situation, $decision);

            return $decision;

        } catch (Exception $e) {
            $this->logger->error("Decision making failed", [
                'situation' => $situation,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'decision' => null,
                'confidence' => 0.0
            ];
        }
    }

    /**
     * Analyze data and provide insights
     *
     * @param array $data Data to analyze
     * @param string $type Analysis type (inventory, sales, security, etc)
     * @return array Analysis results with recommendations
     */
    public function analyzeData(array $data, string $type): array
    {
        try {
            $this->logger->info("Analyzing data", ['type' => $type]);

            $prompt = "Analyze the following {$type} data and provide actionable insights:\n\n";
            $prompt .= json_encode($data, JSON_PRETTY_PRINT);
            $prompt .= "\n\nProvide: 1) Key findings, 2) Recommendations, 3) Priority actions";

            $messages = [
                ['role' => 'system', 'content' => $this->buildSystemPrompt()],
                ['role' => 'user', 'content' => $prompt]
            ];

            $response = $this->callGPT4($messages);

            $analysis = $this->parseAnalysis($response);

            return [
                'success' => true,
                'type' => $type,
                'analysis' => $analysis,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            $this->logger->error("Data analysis failed", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build system prompt with business context
     */
    private function buildSystemPrompt(array $additionalContext = []): string
    {
        $prompt = "You are the AI Brain of Ecigdis Limited's Intelligence Hub, managing 17 retail locations across New Zealand.\n\n";

        $prompt .= "BUSINESS CONTEXT:\n";
        $prompt .= "- Company: Ecigdis Limited (trading as The Vape Shed)\n";
        $prompt .= "- Locations: 17 stores across New Zealand\n";
        $prompt .= "- Systems: Vend POS, Xero accounting, Deputy scheduling, CISWatch security\n";
        $prompt .= "- Products: Vape equipment and accessories\n";
        $prompt .= "- Focus: Quality, customer satisfaction, compliance with NZ vaping laws\n\n";

        $prompt .= "YOUR CAPABILITIES:\n";
        $prompt .= "- Monitor inventory across all locations\n";
        $prompt .= "- Analyze sales trends and forecast demand\n";
        $prompt .= "- Optimize stock transfers and ordering\n";
        $prompt .= "- Track security events and compliance\n";
        $prompt .= "- Manage staff scheduling and performance\n";
        $prompt .= "- Optimize e-commerce conversions\n";
        $prompt .= "- Provide business intelligence insights\n\n";

        $prompt .= "DECISION FRAMEWORK:\n";
        $prompt .= "- Prioritize customer satisfaction and revenue\n";
        $prompt .= "- Ensure compliance with NZ vaping regulations\n";
        $prompt .= "- Optimize for efficiency and cost reduction\n";
        $prompt .= "- Prevent stockouts and overstock situations\n";
        $prompt .= "- Maintain security and prevent theft\n";
        $prompt .= "- Provide data-driven recommendations\n\n";

        $prompt .= "RESPONSE FORMAT:\n";
        $prompt .= "Always provide: 1) Analysis, 2) Recommendation, 3) Expected impact, 4) Confidence level (0-1)\n";
        $prompt .= "Be concise, actionable, and quantify impact when possible.\n\n";

        if (!empty($additionalContext)) {
            $prompt .= "ADDITIONAL CONTEXT:\n";
            $prompt .= json_encode($additionalContext, JSON_PRETTY_PRINT) . "\n\n";
        }

        if (!empty($this->context)) {
            $prompt .= "CURRENT STATE:\n";
            $prompt .= json_encode($this->context, JSON_PRETTY_PRINT) . "\n\n";
        }

        return $prompt;
    }

    /**
     * Get available functions for agents
     */
    private function getAvailableFunctions(): array
    {
        return [
            [
                'name' => 'create_transfer_order',
                'description' => 'Create a stock transfer order between locations',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'from_location' => ['type' => 'string', 'description' => 'Source location name'],
                        'to_location' => ['type' => 'string', 'description' => 'Destination location name'],
                        'products' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'sku' => ['type' => 'string'],
                                    'quantity' => ['type' => 'integer']
                                ]
                            ]
                        ],
                        'priority' => ['type' => 'string', 'enum' => ['low', 'normal', 'high', 'urgent']]
                    ],
                    'required' => ['from_location', 'to_location', 'products']
                ]
            ],
            [
                'name' => 'check_inventory_levels',
                'description' => 'Check current inventory levels across locations',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'product_sku' => ['type' => 'string', 'description' => 'Product SKU to check'],
                        'location' => ['type' => 'string', 'description' => 'Specific location or "all"']
                    ],
                    'required' => ['product_sku']
                ]
            ],
            [
                'name' => 'send_notification',
                'description' => 'Send notification to staff or management',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'recipient' => ['type' => 'string', 'description' => 'staff, management, or specific email'],
                        'priority' => ['type' => 'string', 'enum' => ['info', 'warning', 'error', 'critical']],
                        'title' => ['type' => 'string'],
                        'message' => ['type' => 'string']
                    ],
                    'required' => ['recipient', 'priority', 'title', 'message']
                ]
            ],
            [
                'name' => 'get_sales_data',
                'description' => 'Retrieve sales data for analysis',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string'],
                        'product' => ['type' => 'string'],
                        'date_from' => ['type' => 'string', 'format' => 'date'],
                        'date_to' => ['type' => 'string', 'format' => 'date']
                    ]
                ]
            ],
            [
                'name' => 'create_purchase_order',
                'description' => 'Create a purchase order for supplier',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'supplier_id' => ['type' => 'integer'],
                        'products' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'sku' => ['type' => 'string'],
                                    'quantity' => ['type' => 'integer'],
                                    'unit_price' => ['type' => 'number']
                                ]
                            ]
                        ],
                        'delivery_location' => ['type' => 'string']
                    ],
                    'required' => ['supplier_id', 'products', 'delivery_location']
                ]
            ]
        ];
    }

    /**
     * Call GPT-4 API
     */
    private function callGPT4(array $messages, array $functions = []): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("OpenAI API key not configured");
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500
        ];

        if (!empty($functions)) {
            $data['functions'] = $functions;
            $data['function_call'] = 'auto';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("GPT-4 API request failed with code {$httpCode}: {$response}");
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['choices'])) {
            throw new Exception("Invalid GPT-4 API response");
        }

        return $result;
    }

    /**
     * Process GPT-4 response
     */
    private function processGPT4Response(array $response): array
    {
        $choice = $response['choices'][0] ?? null;
        if (!$choice) {
            throw new Exception("No response from GPT-4");
        }

        $message = $choice['message'];

        // Check if function call was made
        if (isset($message['function_call'])) {
            return $this->executeFunctionCall($message['function_call']);
        }

        // Regular text response
        return [
            'success' => true,
            'response' => $message['content'],
            'type' => 'text',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Execute function call from GPT-4
     */
    private function executeFunctionCall(array $functionCall): array
    {
        $functionName = $functionCall['name'];
        $arguments = json_decode($functionCall['arguments'], true);

        $this->logger->info("Executing function call", [
            'function' => $functionName,
            'arguments' => $arguments
        ]);

        // Route to appropriate agent
        // This will be implemented when agents are built

        return [
            'success' => true,
            'response' => "Function {$functionName} will be executed",
            'type' => 'function_call',
            'function' => $functionName,
            'arguments' => $arguments,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Build decision prompt
     */
    private function buildDecisionPrompt(string $situation, array $options, array $data): string
    {
        $prompt = "SITUATION: {$situation}\n\n";

        $prompt .= "AVAILABLE OPTIONS:\n";
        foreach ($options as $i => $option) {
            $prompt .= ($i + 1) . ". {$option}\n";
        }
        $prompt .= "\n";

        if (!empty($data)) {
            $prompt .= "RELEVANT DATA:\n";
            $prompt .= json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        }

        $prompt .= "Analyze the situation and recommend the best option. ";
        $prompt .= "Provide your reasoning and a confidence score (0-1).";

        return $prompt;
    }

    /**
     * Parse decision from GPT-4 response
     */
    private function parseDecision(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        // Extract confidence score (looking for patterns like "confidence: 0.85")
        preg_match('/confidence[:\s]+([0-9.]+)/i', $content, $matches);
        $confidence = isset($matches[1]) ? (float) $matches[1] : 0.5;

        return [
            'success' => true,
            'decision' => $content,
            'confidence' => $confidence,
            'auto_execute' => $confidence >= self::CONFIDENCE_AUTO,
            'requires_approval' => $confidence < self::CONFIDENCE_AUTO && $confidence >= self::CONFIDENCE_RECOMMEND,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Parse analysis from GPT-4 response
     */
    private function parseAnalysis(array $response): array
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        return [
            'findings' => $content,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Load business context
     */
    private function loadContext(): void
    {
        try {
            // Load recent key metrics
            $stmt = $this->db->query("
                SELECT
                    (SELECT COUNT(*) FROM vend_outlets) as total_locations,
                    (SELECT COUNT(DISTINCT product_id) FROM vend_products) as total_products
            ");

            $metrics = $stmt->fetch(\PDO::FETCH_ASSOC);

            $this->context = [
                'locations' => $metrics['total_locations'] ?? 17,
                'products' => $metrics['total_products'] ?? 0,
                'loaded_at' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            $this->logger->warning("Failed to load context", ['error' => $e->getMessage()]);
            $this->context = [];
        }
    }

    /**
     * Store decision in database
     */
    private function storeDecision(string $input, array $result): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_decisions (
                    input, output, confidence, auto_executed, created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $input,
                json_encode($result),
                $result['confidence'] ?? 0.5,
                $result['auto_execute'] ?? 0
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to store decision", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get decision statistics
     */
    public function getStatistics(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) as total_decisions,
                    SUM(CASE WHEN auto_executed = 1 THEN 1 ELSE 0 END) as auto_executed,
                    AVG(confidence) as avg_confidence,
                    COUNT(DISTINCT DATE(created_at)) as days_active
                FROM ai_decisions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        } catch (Exception $e) {
            $this->logger->error("Failed to get statistics", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(): void
    {
        $this->conversationHistory = [];
    }
}
