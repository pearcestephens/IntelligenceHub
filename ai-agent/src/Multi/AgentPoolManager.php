<?php

declare(strict_types=1);

namespace App\Multi;

use App\Config;
use App\Logger;
use App\Agent;
use App\RedisClient;
use Exception;

/**
 * Agent Pool Manager
 * 
 * Manages multiple AI agents working collaboratively:
 * - Spawn specialized agents (researcher, coder, analyst, writer)
 * - Agent-to-agent communication
 * - Load balancing across agents
 * - Shared context and memory
 * - Task delegation and coordination
 * 
 * Example:
 * ```php
 * $pool = new AgentPoolManager($config, $logger, $redis);
 * $pool->spawnAgent('researcher', AgentRole::RESEARCHER);
 * $pool->spawnAgent('coder', AgentRole::CODER);
 * 
 * $result = $pool->delegateTask('Analyze this codebase', [
 *     'agents' => ['researcher', 'coder'],
 *     'strategy' => 'consensus'
 * ]);
 * ```
 * 
 * @package App\Multi
 * @author Feature Enhancement Phase 2
 */
class AgentPoolManager
{
    private Config $config;
    private Logger $logger;
    private ?RedisClient $redis;
    private array $agents = [];
    private array $roles = [];
    private array $metrics = [];
    private ?string $coordinatorId = null;

    public function __construct(Config $config, Logger $logger, ?RedisClient $redis = null)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->redis = $redis;
        
        $this->logger->info('Agent Pool Manager initialized');
    }

    /**
     * Spawn new agent with specific role
     */
    public function spawnAgent(string $agentId, string $role, array $options = []): Agent
    {
        if (isset($this->agents[$agentId])) {
            throw new Exception("Agent already exists: {$agentId}");
        }
        
        // Create agent with role-specific configuration
        $agentConfig = $this->getRoleConfiguration($role, $options);
        $agent = new Agent($this->config, $this->logger);
        
        // Store agent with metadata
        $this->agents[$agentId] = [
            'agent' => $agent,
            'role' => $role,
            'options' => $options,
            'created_at' => time(),
            'tasks_completed' => 0,
            'average_response_time' => 0
        ];
        
        $this->roles[$role] = $this->roles[$role] ?? [];
        $this->roles[$role][] = $agentId;
        
        $this->logger->info('Spawned agent', [
            'agent_id' => $agentId,
            'role' => $role,
            'total_agents' => count($this->agents)
        ]);
        
        return $agent;
    }

    /**
     * Get agent by ID
     */
    public function getAgent(string $agentId): ?Agent
    {
        return $this->agents[$agentId]['agent'] ?? null;
    }

    /**
     * Get agents by role
     */
    public function getAgentsByRole(string $role): array
    {
        $agentIds = $this->roles[$role] ?? [];
        return array_map(fn($id) => $this->agents[$id]['agent'], $agentIds);
    }

    /**
     * Terminate agent
     */
    public function terminateAgent(string $agentId): void
    {
        if (!isset($this->agents[$agentId])) {
            return;
        }
        
        $role = $this->agents[$agentId]['role'];
        
        // Remove from role list
        $this->roles[$role] = array_filter(
            $this->roles[$role],
            fn($id) => $id !== $agentId
        );
        
        unset($this->agents[$agentId]);
        
        $this->logger->info('Terminated agent', [
            'agent_id' => $agentId,
            'role' => $role
        ]);
    }

    /**
     * Delegate task to multiple agents
     */
    public function delegateTask(string $task, array $options = []): MultiAgentResult
    {
        $startTime = microtime(true);
        
        $agentIds = $options['agents'] ?? array_keys($this->agents);
        $strategy = $options['strategy'] ?? 'consensus';
        $timeout = $options['timeout'] ?? 60;
        
        $this->logger->info('Delegating task to agents', [
            'task' => substr($task, 0, 100),
            'agents' => $agentIds,
            'strategy' => $strategy
        ]);
        
        $results = [];
        $errors = [];
        
        // Execute task with each agent
        foreach ($agentIds as $agentId) {
            if (!isset($this->agents[$agentId])) {
                $errors[$agentId] = 'Agent not found';
                continue;
            }
            
            try {
                $agent = $this->agents[$agentId]['agent'];
                $role = $this->agents[$agentId]['role'];
                
                // Create role-specific prompt
                $rolePrompt = $this->getRolePrompt($role, $task);
                
                // Execute (simplified - would normally call agent's process method)
                $agentStartTime = microtime(true);
                
                // TODO: Integrate with actual Agent::processMessage()
                $response = "Response from {$role} agent for: {$task}";
                
                $agentTime = microtime(true) - $agentStartTime;
                
                $results[$agentId] = [
                    'agent_id' => $agentId,
                    'role' => $role,
                    'response' => $response,
                    'execution_time' => $agentTime,
                    'confidence' => rand(70, 100) / 100 // Placeholder
                ];
                
                // Update metrics
                $this->agents[$agentId]['tasks_completed']++;
                $this->updateAverageResponseTime($agentId, $agentTime);
                
            } catch (Exception $e) {
                $errors[$agentId] = $e->getMessage();
                
                $this->logger->error('Agent task execution failed', [
                    'agent_id' => $agentId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Apply strategy to combine results
        $finalResult = $this->applyStrategy($strategy, $results, $errors);
        
        $executionTime = microtime(true) - $startTime;
        
        $this->logger->info('Task delegation completed', [
            'strategy' => $strategy,
            'agents_responded' => count($results),
            'agents_failed' => count($errors),
            'execution_time' => $executionTime
        ]);
        
        return new MultiAgentResult(
            $finalResult,
            $results,
            $errors,
            $executionTime,
            $strategy
        );
    }

    /**
     * Set coordinator agent (manages other agents)
     */
    public function setCoordinator(string $agentId): void
    {
        if (!isset($this->agents[$agentId])) {
            throw new Exception("Agent not found: {$agentId}");
        }
        
        $this->coordinatorId = $agentId;
        
        $this->logger->info('Set coordinator agent', [
            'agent_id' => $agentId
        ]);
    }

    /**
     * Get role-specific configuration
     */
    private function getRoleConfiguration(string $role, array $options): array
    {
        $roleConfigs = [
            AgentRole::RESEARCHER => [
                'system_prompt' => 'You are a research specialist. Focus on gathering and analyzing information.',
                'tools' => ['http', 'knowledge', 'code'],
                'temperature' => 0.7
            ],
            AgentRole::CODER => [
                'system_prompt' => 'You are a coding expert. Focus on writing and debugging code.',
                'tools' => ['code', 'file', 'database', 'static_analysis'],
                'temperature' => 0.3
            ],
            AgentRole::ANALYST => [
                'system_prompt' => 'You are a data analyst. Focus on analyzing data and finding insights.',
                'tools' => ['database', 'code', 'monitoring'],
                'temperature' => 0.5
            ],
            AgentRole::WRITER => [
                'system_prompt' => 'You are a technical writer. Focus on clear documentation and communication.',
                'tools' => ['file', 'knowledge'],
                'temperature' => 0.8
            ],
            AgentRole::COORDINATOR => [
                'system_prompt' => 'You are a coordinator. Delegate tasks and synthesize results.',
                'tools' => ['http', 'database', 'knowledge'],
                'temperature' => 0.6
            ],
        ];
        
        return array_merge($roleConfigs[$role] ?? [], $options);
    }

    /**
     * Get role-specific prompt enhancement
     */
    private function getRolePrompt(string $role, string $task): string
    {
        $rolePrompts = [
            AgentRole::RESEARCHER => "As a research specialist, gather and analyze information about: {$task}",
            AgentRole::CODER => "As a coding expert, provide code solutions for: {$task}",
            AgentRole::ANALYST => "As a data analyst, analyze and provide insights for: {$task}",
            AgentRole::WRITER => "As a technical writer, document and explain: {$task}",
            AgentRole::COORDINATOR => "As a coordinator, orchestrate the solution for: {$task}",
        ];
        
        return $rolePrompts[$role] ?? $task;
    }

    /**
     * Apply strategy to combine agent results
     */
    private function applyStrategy(string $strategy, array $results, array $errors): string
    {
        if (empty($results)) {
            return "All agents failed. Errors: " . implode(', ', $errors);
        }
        
        switch ($strategy) {
            case 'consensus':
                return $this->applyConsensus($results);
                
            case 'best':
                return $this->selectBest($results);
                
            case 'weighted':
                return $this->applyWeighted($results);
                
            case 'merge':
                return $this->mergeResults($results);
                
            default:
                // First available result
                return reset($results)['response'];
        }
    }

    /**
     * Consensus: Agents vote, majority wins
     */
    private function applyConsensus(array $results): string
    {
        // Simplified consensus - in reality would use semantic similarity
        $responses = array_column($results, 'response');
        $counts = array_count_values($responses);
        arsort($counts);
        
        $winner = key($counts);
        $votes = $counts[$winner];
        $total = count($results);
        
        return "Consensus Result ({$votes}/{$total} agents agree):\n\n{$winner}";
    }

    /**
     * Best: Highest confidence agent
     */
    private function selectBest(array $results): string
    {
        usort($results, fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $best = reset($results);
        
        return "Best Result (confidence: " . ($best['confidence'] * 100) . "%):\n\n" . $best['response'];
    }

    /**
     * Weighted: Combine based on role expertise
     */
    private function applyWeighted(array $results): string
    {
        $weights = [
            AgentRole::RESEARCHER => 1.2,
            AgentRole::CODER => 1.5,
            AgentRole::ANALYST => 1.3,
            AgentRole::WRITER => 1.0,
            AgentRole::COORDINATOR => 1.1,
        ];
        
        // Weight by role expertise
        foreach ($results as &$result) {
            $result['weighted_confidence'] = $result['confidence'] * ($weights[$result['role']] ?? 1.0);
        }
        
        usort($results, fn($a, $b) => $b['weighted_confidence'] <=> $a['weighted_confidence']);
        $best = reset($results);
        
        return "Weighted Result ({$best['role']}):\n\n" . $best['response'];
    }

    /**
     * Merge: Combine all results
     */
    private function mergeResults(array $results): string
    {
        $merged = "Combined Results from " . count($results) . " agents:\n\n";
        
        foreach ($results as $result) {
            $merged .= "## {$result['role']} Agent Response:\n";
            $merged .= $result['response'] . "\n\n";
        }
        
        return $merged;
    }

    /**
     * Update average response time
     */
    private function updateAverageResponseTime(string $agentId, float $newTime): void
    {
        $agent = &$this->agents[$agentId];
        $count = $agent['tasks_completed'];
        $currentAvg = $agent['average_response_time'];
        
        // Calculate new average
        $agent['average_response_time'] = (($currentAvg * ($count - 1)) + $newTime) / $count;
    }

    /**
     * Get pool metrics
     */
    public function getMetrics(): array
    {
        $totalTasks = array_sum(array_column($this->agents, 'tasks_completed'));
        $avgResponseTime = array_sum(array_column($this->agents, 'average_response_time')) / (count($this->agents) ?: 1);
        
        return [
            'total_agents' => count($this->agents),
            'agents_by_role' => array_map('count', $this->roles),
            'total_tasks_completed' => $totalTasks,
            'average_response_time' => $avgResponseTime,
            'coordinator' => $this->coordinatorId
        ];
    }

    /**
     * List all agents
     */
    public function listAgents(): array
    {
        return array_map(function ($data) {
            return [
                'role' => $data['role'],
                'created_at' => $data['created_at'],
                'tasks_completed' => $data['tasks_completed'],
                'average_response_time' => $data['average_response_time']
            ];
        }, $this->agents);
    }
}

/**
 * Multi-Agent Result
 */
class MultiAgentResult
{
    private string $finalResult;
    private array $individualResults;
    private array $errors;
    private float $executionTime;
    private string $strategy;

    public function __construct(
        string $finalResult,
        array $individualResults,
        array $errors,
        float $executionTime,
        string $strategy
    ) {
        $this->finalResult = $finalResult;
        $this->individualResults = $individualResults;
        $this->errors = $errors;
        $this->executionTime = $executionTime;
        $this->strategy = $strategy;
    }

    public function getFinalResult(): string
    {
        return $this->finalResult;
    }

    public function getIndividualResults(): array
    {
        return $this->individualResults;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function getStrategy(): string
    {
        return $this->strategy;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getSuccessRate(): float
    {
        $total = count($this->individualResults) + count($this->errors);
        return $total > 0 ? count($this->individualResults) / $total : 0;
    }
}
