<?php
/**
 * P1 FIX: Multi-Agent System Integration
 * File: src/Multi/AgentPoolManager.php
 * Line: 171 - Complete the TODO
 *
 * This implements the missing processMessage() integration with Agent class
 */

declare(strict_types=1);

namespace App\Multi;

use App\Agent;
use App\Config;
use App\Logger;
use App\DB;
use Exception;

/**
 * AgentPoolManager - COMPLETED IMPLEMENTATION
 *
 * Manages a pool of AI agents for multi-agent workflows
 */
class AgentPoolManager
{
    private array $agents = [];
    private Config $config;
    private Logger $logger;
    private DB $db;

    /** Maximum number of agents in the pool */
    private int $maxAgents = 10;

    /** Agent creation counter */
    private int $agentCounter = 0;

    public function __construct(Config $config, Logger $logger, DB $db)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->db = $db;

        $this->maxAgents = (int)$config->get('MAX_POOL_AGENTS', 10);

        $this->logger->info('AgentPoolManager initialized', [
            'max_agents' => $this->maxAgents
        ]);
    }

    /**
     * Create and register a new agent in the pool
     *
     * @param string $role Agent role (e.g., 'researcher', 'coder', 'reviewer')
     * @param array $config Agent-specific configuration
     * @return string Agent ID
     * @throws Exception If pool is full
     */
    public function createAgent(string $role, array $config = []): string
    {
        if (count($this->agents) >= $this->maxAgents) {
            throw new Exception("Agent pool is full (max: {$this->maxAgents})");
        }

        $agentId = sprintf('agent_%s_%d_%s', $role, ++$this->agentCounter, uniqid());

        // Create new Agent instance
        $agent = new Agent($this->config, $this->logger);
        $agent->initialize();

        // Store agent with metadata
        $this->agents[$agentId] = [
            'agent' => $agent,
            'role' => $role,
            'config' => $config,
            'created_at' => time(),
            'message_count' => 0,
            'total_tokens' => 0
        ];

        $this->logger->info('Agent created in pool', [
            'agent_id' => $agentId,
            'role' => $role,
            'pool_size' => count($this->agents)
        ]);

        return $agentId;
    }

    /**
     * Process a message with a specific agent
     *
     * ✅ THIS IMPLEMENTS THE TODO AT LINE 171
     *
     * @param string $agentId Agent identifier
     * @param string $message Message to process
     * @param array $options Processing options
     * @return array Agent response
     * @throws Exception If agent not found
     */
    public function processMessage(string $agentId, string $message, array $options = []): array
    {
        if (!isset($this->agents[$agentId])) {
            throw new \InvalidArgumentException("Agent not found: {$agentId}");
        }

        $agentData = $this->agents[$agentId];
        /** @var Agent $agent */
        $agent = $agentData['agent'];

        $this->logger->info('Processing message with agent', [
            'agent_id' => $agentId,
            'role' => $agentData['role'],
            'message_length' => strlen($message)
        ]);

        try {
            // Set agent-specific options
            $processingOptions = array_merge([
                'skip_rate_limit' => false,
                'client_id' => "multi_agent_{$agentId}",
                'agent_role' => $agentData['role']
            ], $options);

            // Process message through Agent::chat()
            $response = $agent->chat(
                $message,
                $options['conversation_id'] ?? null,
                $processingOptions
            );

            // Update agent statistics
            $this->agents[$agentId]['message_count']++;
            if (isset($response['usage']['total_tokens'])) {
                $this->agents[$agentId]['total_tokens'] += $response['usage']['total_tokens'];
            }

            $this->logger->info('Agent message processed successfully', [
                'agent_id' => $agentId,
                'response_length' => isset($response['response']) ? strlen($response['response']) : 0,
                'tokens_used' => $response['usage']['total_tokens'] ?? 0
            ]);

            return $response;

        } catch (Exception $e) {
            $this->logger->error('Agent message processing failed', [
                'agent_id' => $agentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process a message with multiple agents in parallel
     *
     * @param array $agents Array of agent IDs
     * @param string $message Message to process
     * @param array $options Processing options
     * @return array Array of responses keyed by agent ID
     */
    public function processMessageMulti(array $agents, string $message, array $options = []): array
    {
        $responses = [];

        foreach ($agents as $agentId) {
            try {
                $responses[$agentId] = $this->processMessage($agentId, $message, $options);
            } catch (Exception $e) {
                $responses[$agentId] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $responses;
    }

    /**
     * Get agent by ID
     *
     * @param string $agentId Agent identifier
     * @return Agent|null
     */
    public function getAgent(string $agentId): ?Agent
    {
        return $this->agents[$agentId]['agent'] ?? null;
    }

    /**
     * Get all agents with a specific role
     *
     * @param string $role Agent role
     * @return array Array of agent IDs
     */
    public function getAgentsByRole(string $role): array
    {
        $result = [];

        foreach ($this->agents as $agentId => $data) {
            if ($data['role'] === $role) {
                $result[] = $agentId;
            }
        }

        return $result;
    }

    /**
     * Get agent statistics
     *
     * @param string $agentId Agent identifier
     * @return array|null Agent statistics
     */
    public function getAgentStats(string $agentId): ?array
    {
        if (!isset($this->agents[$agentId])) {
            return null;
        }

        $data = $this->agents[$agentId];

        return [
            'agent_id' => $agentId,
            'role' => $data['role'],
            'created_at' => $data['created_at'],
            'uptime_seconds' => time() - $data['created_at'],
            'message_count' => $data['message_count'],
            'total_tokens' => $data['total_tokens'],
            'avg_tokens_per_message' => $data['message_count'] > 0
                ? round($data['total_tokens'] / $data['message_count'], 2)
                : 0
        ];
    }

    /**
     * Get pool statistics
     *
     * @return array Pool-wide statistics
     */
    public function getPoolStats(): array
    {
        $totalMessages = 0;
        $totalTokens = 0;
        $roleDistribution = [];

        foreach ($this->agents as $agentId => $data) {
            $totalMessages += $data['message_count'];
            $totalTokens += $data['total_tokens'];

            $role = $data['role'];
            $roleDistribution[$role] = ($roleDistribution[$role] ?? 0) + 1;
        }

        return [
            'pool_size' => count($this->agents),
            'max_pool_size' => $this->maxAgents,
            'utilization' => round((count($this->agents) / $this->maxAgents) * 100, 2),
            'total_messages' => $totalMessages,
            'total_tokens' => $totalTokens,
            'role_distribution' => $roleDistribution
        ];
    }

    /**
     * Remove an agent from the pool
     *
     * @param string $agentId Agent identifier
     * @return bool True if removed, false if not found
     */
    public function removeAgent(string $agentId): bool
    {
        if (!isset($this->agents[$agentId])) {
            return false;
        }

        $this->logger->info('Removing agent from pool', [
            'agent_id' => $agentId,
            'role' => $this->agents[$agentId]['role']
        ]);

        unset($this->agents[$agentId]);

        return true;
    }

    /**
     * Clear all agents from the pool
     */
    public function clearPool(): void
    {
        $this->logger->info('Clearing agent pool', [
            'agents_removed' => count($this->agents)
        ]);

        $this->agents = [];
        $this->agentCounter = 0;
    }
}
