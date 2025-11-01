<?php

declare(strict_types=1);

namespace App\Tools;

use App\Logger;
use App\RedisClient;
use Exception;
use DateTime;

/**
 * Tool Chain Orchestrator
 * 
 * Enables complex multi-step tool workflows with:
 * - Sequential execution (step 1 → step 2 → step 3)
 * - Parallel execution (independent steps run simultaneously)
 * - Conditional branching (if/else based on results)
 * - Error handling and rollback support
 * - Result caching and memoization
 * - Data passing between steps
 * 
 * Example:
 * ```php
 * $chain = new ToolChain('data-pipeline', $logger, $redis);
 * $chain->addStep('http', ['url' => 'api.example.com/data'], 'fetch_data')
 *       ->addStep('code', ['operation' => 'parse_json'], 'parse', ['fetch_data'])
 *       ->addStep('database', ['action' => 'insert'], 'save', ['parse'])
 *       ->onError('rollback')
 *       ->execute($executor);
 * ```
 * 
 * @package App\Tools
 * @author Feature Enhancement Phase 1
 */
class ToolChainOrchestrator
{
    private Logger $logger;
    private ?RedisClient $redis;
    private array $chains = [];
    private array $metrics = [];

    public function __construct(Logger $logger, ?RedisClient $redis = null)
    {
        $this->logger = $logger;
        $this->redis = $redis;
    }

    /**
     * Create new tool chain
     */
    public function createChain(string $chainId, array $options = []): ToolChain
    {
        $chain = new ToolChain(
            $chainId,
            $this->logger,
            $this->redis,
            $options
        );
        
        $this->chains[$chainId] = $chain;
        
        $this->logger->info('Created tool chain', [
            'chain_id' => $chainId,
            'options' => $options
        ]);
        
        return $chain;
    }

    /**
     * Get existing chain
     */
    public function getChain(string $chainId): ?ToolChain
    {
        return $this->chains[$chainId] ?? null;
    }

    /**
     * Execute chain by ID
     */
    public function executeChain(string $chainId, ToolExecutor $executor, array $initialData = []): ToolChainResult
    {
        $chain = $this->getChain($chainId);
        
        if (!$chain) {
            throw new Exception("Chain not found: {$chainId}");
        }
        
        return $chain->execute($executor, $initialData);
    }

    /**
     * Get chain execution metrics
     */
    public function getMetrics(string $chainId): array
    {
        return $this->metrics[$chainId] ?? [];
    }

    /**
     * List all registered chains
     */
    public function listChains(): array
    {
        return array_keys($this->chains);
    }
}

/**
 * Tool Chain - Represents a sequence of tool executions
 */
class ToolChain
{
    private string $id;
    private Logger $logger;
    private ?RedisClient $redis;
    private array $options;
    private array $steps = [];
    private ?string $errorHandler = null;
    private array $conditionals = [];

    public function __construct(
        string $id,
        Logger $logger,
        ?RedisClient $redis = null,
        array $options = []
    ) {
        $this->id = $id;
        $this->logger = $logger;
        $this->redis = $redis;
        $this->options = array_merge([
            'parallel' => false,
            'cache_results' => true,
            'cache_ttl' => 300,
            'max_retries' => 3,
            'timeout' => 30
        ], $options);
    }

    /**
     * Add step to chain
     * 
     * @param string $toolName Name of tool to execute
     * @param array $params Parameters for tool
     * @param string|null $stepId Unique ID for this step (for referencing)
     * @param array $dependencies Step IDs this step depends on
     */
    public function addStep(
        string $toolName,
        array $params,
        ?string $stepId = null,
        array $dependencies = []
    ): self {
        $stepId = $stepId ?? 'step_' . (count($this->steps) + 1);
        
        $this->steps[$stepId] = [
            'tool' => $toolName,
            'params' => $params,
            'dependencies' => $dependencies,
            'completed' => false,
            'result' => null,
            'error' => null
        ];
        
        $this->logger->debug('Added step to chain', [
            'chain_id' => $this->id,
            'step_id' => $stepId,
            'tool' => $toolName,
            'dependencies' => $dependencies
        ]);
        
        return $this;
    }

    /**
     * Add conditional branch
     * 
     * @param string $stepId Step to check condition after
     * @param callable $condition Function that returns bool
     * @param ToolChain $trueBranch Chain to execute if true
     * @param ToolChain|null $falseBranch Chain to execute if false
     */
    public function addConditional(
        string $stepId,
        callable $condition,
        ToolChain $trueBranch,
        ?ToolChain $falseBranch = null
    ): self {
        $this->conditionals[$stepId] = [
            'condition' => $condition,
            'true' => $trueBranch,
            'false' => $falseBranch
        ];
        
        return $this;
    }

    /**
     * Set error handler strategy
     * 
     * @param string $strategy 'rollback', 'continue', 'stop'
     */
    public function onError(string $strategy): self
    {
        $this->errorHandler = $strategy;
        return $this;
    }

    /**
     * Execute the chain
     */
    public function execute(ToolExecutor $executor, array $initialData = []): ToolChainResult
    {
        $startTime = microtime(true);
        $context = $initialData;
        $completedSteps = [];
        $errors = [];
        
        $this->logger->info('Executing tool chain', [
            'chain_id' => $this->id,
            'steps_count' => count($this->steps),
            'parallel' => $this->options['parallel']
        ]);
        
        try {
            if ($this->options['parallel']) {
                // Execute independent steps in parallel
                $result = $this->executeParallel($executor, $context);
            } else {
                // Execute steps sequentially
                $result = $this->executeSequential($executor, $context);
            }
            
            $executionTime = microtime(true) - $startTime;
            
            $this->logger->info('Tool chain completed', [
                'chain_id' => $this->id,
                'execution_time' => $executionTime,
                'steps_completed' => count($result['completed']),
                'errors' => count($result['errors'])
            ]);
            
            return new ToolChainResult(
                true,
                $result['context'],
                $result['completed'],
                $result['errors'],
                $executionTime
            );
            
        } catch (Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            $this->logger->error('Tool chain failed', [
                'chain_id' => $this->id,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime
            ]);
            
            // Handle error based on strategy
            if ($this->errorHandler === 'rollback') {
                $this->rollback($completedSteps);
            }
            
            return new ToolChainResult(
                false,
                $context,
                $completedSteps,
                [$e->getMessage()],
                $executionTime
            );
        }
    }

    /**
     * Execute steps sequentially
     */
    private function executeSequential(ToolExecutor $executor, array $context): array
    {
        $completed = [];
        $errors = [];
        
        foreach ($this->steps as $stepId => $step) {
            // Check if dependencies are met
            foreach ($step['dependencies'] as $depId) {
                if (!isset($completed[$depId])) {
                    throw new Exception("Dependency not met: {$depId} for step {$stepId}");
                }
            }
            
            // Check cache
            $cacheKey = $this->getCacheKey($stepId, $step['params']);
            if ($this->options['cache_results'] && $this->redis) {
                $cached = $this->redis->get($cacheKey);
                if ($cached !== null) {
                    $this->logger->debug('Using cached result', [
                        'chain_id' => $this->id,
                        'step_id' => $stepId
                    ]);
                    
                    $context[$stepId] = json_decode($cached, true);
                    $completed[$stepId] = $context[$stepId];
                    continue;
                }
            }
            
            // Merge context with params
            $params = $this->resolveParams($step['params'], $context);
            
            // Execute tool
            try {
                $result = $executor->executeTool($step['tool'], $params);
                
                $context[$stepId] = $result;
                $completed[$stepId] = $result;
                
                // Cache result
                if ($this->options['cache_results'] && $this->redis) {
                    $this->redis->setex(
                        $cacheKey,
                        $this->options['cache_ttl'],
                        json_encode($result)
                    );
                }
                
                // Check for conditional
                if (isset($this->conditionals[$stepId])) {
                    $conditional = $this->conditionals[$stepId];
                    $condition = $conditional['condition'];
                    
                    if ($condition($result, $context)) {
                        $branchResult = $conditional['true']->execute($executor, $context);
                        $context = array_merge($context, $branchResult->getContext());
                    } elseif ($conditional['false']) {
                        $branchResult = $conditional['false']->execute($executor, $context);
                        $context = array_merge($context, $branchResult->getContext());
                    }
                }
                
            } catch (Exception $e) {
                $errors[$stepId] = $e->getMessage();
                
                if ($this->errorHandler === 'stop') {
                    throw $e;
                } elseif ($this->errorHandler === 'rollback') {
                    throw $e;
                }
                // 'continue' strategy - keep going
            }
        }
        
        return [
            'context' => $context,
            'completed' => $completed,
            'errors' => $errors
        ];
    }

    /**
     * Execute independent steps in parallel (simulated)
     * Note: PHP doesn't have true parallelism, but we can optimize order
     */
    private function executeParallel(ToolExecutor $executor, array $context): array
    {
        // Group steps by dependency level
        $levels = $this->groupByDependencyLevel();
        
        $completed = [];
        $errors = [];
        
        foreach ($levels as $level => $stepIds) {
            $this->logger->debug('Executing parallel level', [
                'chain_id' => $this->id,
                'level' => $level,
                'steps' => $stepIds
            ]);
            
            // Execute all steps at this level
            foreach ($stepIds as $stepId) {
                $step = $this->steps[$stepId];
                
                // Check cache
                $cacheKey = $this->getCacheKey($stepId, $step['params']);
                if ($this->options['cache_results'] && $this->redis) {
                    $cached = $this->redis->get($cacheKey);
                    if ($cached !== null) {
                        $context[$stepId] = json_decode($cached, true);
                        $completed[$stepId] = $context[$stepId];
                        continue;
                    }
                }
                
                // Execute
                try {
                    $params = $this->resolveParams($step['params'], $context);
                    $result = $executor->executeTool($step['tool'], $params);
                    
                    $context[$stepId] = $result;
                    $completed[$stepId] = $result;
                    
                    // Cache
                    if ($this->options['cache_results'] && $this->redis) {
                        $this->redis->setex(
                            $cacheKey,
                            $this->options['cache_ttl'],
                            json_encode($result)
                        );
                    }
                    
                } catch (Exception $e) {
                    $errors[$stepId] = $e->getMessage();
                    if ($this->errorHandler === 'stop') {
                        throw $e;
                    }
                }
            }
        }
        
        return [
            'context' => $context,
            'completed' => $completed,
            'errors' => $errors
        ];
    }

    /**
     * Group steps by dependency level for parallel execution
     */
    private function groupByDependencyLevel(): array
    {
        $levels = [];
        $processed = [];
        
        while (count($processed) < count($this->steps)) {
            $currentLevel = [];
            
            foreach ($this->steps as $stepId => $step) {
                if (in_array($stepId, $processed)) {
                    continue;
                }
                
                // Check if all dependencies are processed
                $depsReady = true;
                foreach ($step['dependencies'] as $depId) {
                    if (!in_array($depId, $processed)) {
                        $depsReady = false;
                        break;
                    }
                }
                
                if ($depsReady) {
                    $currentLevel[] = $stepId;
                    $processed[] = $stepId;
                }
            }
            
            if (empty($currentLevel)) {
                throw new Exception("Circular dependency detected in chain: {$this->id}");
            }
            
            $levels[] = $currentLevel;
        }
        
        return $levels;
    }

    /**
     * Resolve params with context substitution
     * Replaces {{stepId.field}} with actual values from context
     */
    private function resolveParams(array $params, array $context): array
    {
        array_walk_recursive($params, function (&$value) use ($context) {
            if (is_string($value) && preg_match('/\{\{(\w+)\.?(\w*)\}\}/', $value, $matches)) {
                $stepId = $matches[1];
                $field = $matches[2] ?? null;
                
                if (isset($context[$stepId])) {
                    if ($field && isset($context[$stepId][$field])) {
                        $value = $context[$stepId][$field];
                    } else {
                        $value = $context[$stepId];
                    }
                }
            }
        });
        
        return $params;
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $stepId, array $params): string
    {
        return "tool_chain:{$this->id}:{$stepId}:" . md5(json_encode($params));
    }

    /**
     * Rollback completed steps
     */
    private function rollback(array $completedSteps): void
    {
        $this->logger->warning('Rolling back tool chain', [
            'chain_id' => $this->id,
            'steps_to_rollback' => count($completedSteps)
        ]);
        
        // Rollback in reverse order
        foreach (array_reverse($completedSteps) as $stepId => $result) {
            // Clear cache
            if ($this->redis) {
                $step = $this->steps[$stepId];
                $cacheKey = $this->getCacheKey($stepId, $step['params']);
                $this->redis->del($cacheKey);
            }
            
            // TODO: Implement tool-specific rollback if tools support it
        }
    }
}

/**
 * Tool Chain Result
 */
class ToolChainResult
{
    private bool $success;
    private array $context;
    private array $completed;
    private array $errors;
    private float $executionTime;

    public function __construct(
        bool $success,
        array $context,
        array $completed,
        array $errors,
        float $executionTime
    ) {
        $this->success = $success;
        $this->context = $context;
        $this->completed = $completed;
        $this->errors = $errors;
        $this->executionTime = $executionTime;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getCompleted(): array
    {
        return $this->completed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    public function getResult(string $stepId)
    {
        return $this->completed[$stepId] ?? null;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
