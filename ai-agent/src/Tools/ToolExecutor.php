<?php

/**
 * Tool Executor for safe orchestration of AI agent tools
 * Handles tool execution with timeout, rate limiting, and error handling
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\DB;
use App\Logger;
use App\SSE;
use App\Util\Ids;
use App\Util\RateLimit;

class ToolExecutor
{
    private const DEFAULT_TIMEOUT = 30;
    private const MAX_PARALLEL_TOOLS = 3;
    private const MAX_RETRY_ATTEMPTS = 2;

    private string $executionId;
    private array $context;
    private ?SSE $sse;

    public function __construct(?array $context = null, ?SSE $sse = null)
    {
        $this->executionId = Ids::uuid();
        $this->context = $context ?? [];
        $this->sse = $sse;

        Logger::debug('Tool executor initialized', [
            'execution_id' => $this->executionId,
            'context_keys' => array_keys($this->context)
        ]);
    }

    /**
     * Get conversation ID from context
     */
    private function convoId(): ?string
    {
        return $this->context['conversation_id'] ?? null;
    }

    /**
     * Execute a single tool
     */
    public function execute(string $toolName, array $parameters = []): array
    {
        $startTime = microtime(true);
        $callId = Ids::uuid();

        try {
            // Get tool definition
            $tool = ToolRegistry::getForExecution($toolName);
            if (!$tool) {
                throw new \InvalidArgumentException("Tool not available: {$toolName}");
            }

            // Validate parameters
            $validation = ToolRegistry::validateParameters($toolName, $parameters);
            if (!$validation['valid']) {
                throw new \InvalidArgumentException('Parameter validation failed: ' . implode(', ', $validation['errors']));
            }

            // Check rate limits
            $this->checkRateLimit($toolName);

            // Emit start event
            $this->emitStart($callId, $toolName, $parameters);

            // Log tool call
            $this->logToolCall($callId, $toolName, $validation['parameters']);

            // Execute the tool
            $result = $this->executeToolSafely($tool, $toolName, $validation['parameters']);

            $duration = (microtime(true) - $startTime) * 1000;

            // Log successful execution
            $this->logToolResult($callId, $result, $duration, true);

            // Emit completion event
            $this->emitComplete($callId, $toolName, $result, $duration);

            Logger::info('Tool executed successfully', [
                'execution_id' => $this->executionId,
                'call_id' => $callId,
                'tool' => $toolName,
                'duration_ms' => (int)$duration,
                'result_size' => is_array($result) ? count($result) : strlen(json_encode($result))
            ]);

            return [
                'success' => true,
                'call_id' => $callId,
                'tool_name' => $toolName,
                'result' => $result,
                'duration_ms' => (int)$duration,
                'execution_id' => $this->executionId
            ];
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $startTime) * 1000;

            // Log error
            $this->logToolResult($callId, null, $duration, false, $e->getMessage());

            // Emit error event
            $this->emitError($callId, $toolName, $e->getMessage(), $duration);

            Logger::error('Tool execution failed', [
                'execution_id' => $this->executionId,
                'call_id' => $callId,
                'tool' => $toolName,
                'error' => $e->getMessage(),
                'duration_ms' => (int)$duration
            ]);

            return [
                'success' => false,
                'call_id' => $callId,
                'tool_name' => $toolName,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'duration_ms' => (int)$duration,
                'execution_id' => $this->executionId
            ];
        }
    }

    /**
     * Execute multiple tools in parallel (limited concurrency)
     */
    public function executeParallel(array $toolCalls): array
    {
        $results = [];
        $batches = array_chunk($toolCalls, self::MAX_PARALLEL_TOOLS);

        foreach ($batches as $batch) {
            $batchResults = $this->executeBatch($batch);
            $results = array_merge($results, $batchResults);
        }

        Logger::info('Parallel tool execution completed', [
            'execution_id' => $this->executionId,
            'total_tools' => count($toolCalls),
            'successful' => count(array_filter($results, fn($r) => $r['success'])),
            'failed' => count(array_filter($results, fn($r) => !$r['success']))
        ]);

        return $results;
    }

    /**
     * Execute a sequence of tools (one after another)
     */
    public function executeSequence(array $toolCalls, bool $stopOnError = false): array
    {
        $results = [];
        $context = $this->context;

        foreach ($toolCalls as $index => $toolCall) {
            $toolName = $toolCall['name'] ?? $toolCall['tool_name'];
            $parameters = $toolCall['parameters'] ?? [];

            // Add previous results to context for subsequent calls
            $parameters['_context'] = $context;
            $parameters['_previous_results'] = $results;

            $result = $this->execute($toolName, $parameters);
            $results[] = $result;

            // Update context with result
            if ($result['success']) {
                $context[$toolName] = $result['result'];
            }

            // Stop on error if requested
            if (!$result['success'] && $stopOnError) {
                Logger::warning('Sequence stopped due to tool error', [
                    'execution_id' => $this->executionId,
                    'failed_tool' => $toolName,
                    'completed_tools' => $index,
                    'total_tools' => count($toolCalls)
                ]);
                break;
            }
        }

        return $results;
    }

    /**
     * Execute tools with retry logic
     */
    public function executeWithRetry(string $toolName, array $parameters = [], int $maxRetries = null): array
    {
        $maxRetries = $maxRetries ?? self::MAX_RETRY_ATTEMPTS;
        $attempt = 1;
        $lastError = null;

        while ($attempt <= $maxRetries + 1) {
            $result = $this->execute($toolName, $parameters);

            if ($result['success']) {
                if ($attempt > 1) {
                    Logger::info('Tool succeeded after retry', [
                        'tool' => $toolName,
                        'attempt' => $attempt,
                        'execution_id' => $this->executionId
                    ]);
                }
                return $result;
            }

            $lastError = $result['error'];

            if ($attempt <= $maxRetries) {
                $delay = min(1000 * pow(2, $attempt - 1), 5000); // Exponential backoff, max 5s
                Logger::warning('Tool failed, retrying', [
                    'tool' => $toolName,
                    'attempt' => $attempt,
                    'retry_delay_ms' => $delay,
                    'error' => $lastError
                ]);

                usleep($delay * 1000); // Convert to microseconds
            }

            $attempt++;
        }

        // All retries exhausted
        Logger::error('Tool failed after all retries', [
            'tool' => $toolName,
            'max_retries' => $maxRetries,
            'final_error' => $lastError,
            'execution_id' => $this->executionId
        ]);

        return [
            'success' => false,
            'call_id' => Ids::uuid(),
            'tool_name' => $toolName,
            'error' => "Tool failed after {$maxRetries} retries. Last error: {$lastError}",
            'error_type' => 'RetryExhausted',
            'execution_id' => $this->executionId
        ];
    }

    /**
     * Get execution statistics
     */
    public function getStats(): array
    {
        try {
            $stats = DB::selectOne('
                SELECT 
                    COUNT(*) as total_calls,
                    COUNT(CASE WHEN success = 1 THEN 1 END) as successful_calls,
                    COUNT(CASE WHEN success = 0 THEN 1 END) as failed_calls,
                    AVG(duration_ms) as avg_duration_ms,
                    MAX(duration_ms) as max_duration_ms,
                    COUNT(DISTINCT tool_name) as unique_tools_used
                FROM tool_calls 
                WHERE execution_id = ?
            ', [$this->executionId]);

            $toolBreakdown = DB::select('
                SELECT 
                    tool_name,
                    COUNT(*) as call_count,
                    AVG(duration_ms) as avg_duration,
                    COUNT(CASE WHEN success = 1 THEN 1 END) as success_count
                FROM tool_calls 
                WHERE execution_id = ?
                GROUP BY tool_name
                ORDER BY call_count DESC
            ', [$this->executionId]);

            return [
                'execution_id' => $this->executionId,
                'overall' => [
                    'total_calls' => (int)($stats['total_calls'] ?? 0),
                    'successful_calls' => (int)($stats['successful_calls'] ?? 0),
                    'failed_calls' => (int)($stats['failed_calls'] ?? 0),
                    'success_rate' => $stats['total_calls'] > 0 ?
                        round(($stats['successful_calls'] / $stats['total_calls']) * 100, 2) : 0,
                    'avg_duration_ms' => round($stats['avg_duration_ms'] ?? 0, 2),
                    'max_duration_ms' => (int)($stats['max_duration_ms'] ?? 0),
                    'unique_tools_used' => (int)($stats['unique_tools_used'] ?? 0)
                ],
                'by_tool' => array_map(function ($row) {
                    return [
                        'tool_name' => $row['tool_name'],
                        'call_count' => (int)$row['call_count'],
                        'avg_duration_ms' => round($row['avg_duration'], 2),
                        'success_count' => (int)$row['success_count'],
                        'success_rate' => round(($row['success_count'] / $row['call_count']) * 100, 2)
                    ];
                }, $toolBreakdown)
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get execution stats', [
                'execution_id' => $this->executionId,
                'error' => $e->getMessage()
            ]);

            return [
                'execution_id' => $this->executionId,
                'overall' => ['error' => 'Failed to retrieve stats'],
                'by_tool' => []
            ];
        }
    }

    /**
     * Execute tool safely with timeout and error handling
     */
    private function executeToolSafely(array $tool, string $toolName, array $parameters): mixed
    {
        $timeout = $tool['safety']['timeout'] ?? self::DEFAULT_TIMEOUT;

        // Set time limit
        $oldTimeLimit = ini_get('max_execution_time');
        set_time_limit($timeout);

        try {
            $result = null;

            if (isset($tool['class']) && isset($tool['method'])) {
                // Class-based tool
                $class = $tool['class'];
                $method = $tool['method'];

                if (!class_exists($class)) {
                    throw new \RuntimeException("Tool class not found: {$class}");
                }

                if (!method_exists($class, $method)) {
                    throw new \RuntimeException("Tool method not found: {$class}::{$method}");
                }

                $result = $class::{$method}($parameters, $this->context);
            } elseif (isset($tool['callable'])) {
                // Callable tool
                if (!is_callable($tool['callable'])) {
                    throw new \RuntimeException("Tool is not callable: {$toolName}");
                }

                $result = call_user_func($tool['callable'], $parameters, $this->context);
            } elseif (isset($tool['script'])) {
                // Script-based tool
                $result = $this->executeScript($tool['script'], $parameters);
            } else {
                throw new \RuntimeException("Tool has no valid execution method: {$toolName}");
            }

            return $result;
        } finally {
            // Restore time limit
            set_time_limit((int)$oldTimeLimit);
        }
    }

    /**
     * Execute a batch of tools
     */
    private function executeBatch(array $toolCalls): array
    {
        $results = [];
        $processes = [];

        // For now, execute sequentially in batch (true parallel would require process forking)
        foreach ($toolCalls as $toolCall) {
            $toolName = $toolCall['name'] ?? $toolCall['tool_name'];
            $parameters = $toolCall['parameters'] ?? [];

            $result = $this->execute($toolName, $parameters);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Execute script-based tool
     */
    private function executeScript(string $scriptPath, array $parameters): mixed
    {
        if (!file_exists($scriptPath)) {
            throw new \RuntimeException("Script not found: {$scriptPath}");
        }

        // Prepare environment
        $env = array_merge($_ENV, [
            'TOOL_PARAMETERS' => json_encode($parameters),
            'TOOL_CONTEXT' => json_encode($this->context)
        ]);

        // Execute script and capture output
        $process = proc_open(
            "php {$scriptPath}",
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ],
            $pipes,
            dirname($scriptPath),
            $env
        );

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to execute script: {$scriptPath}");
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException("Script execution failed: {$error}");
        }

        // Try to decode JSON output, fallback to raw string
        $result = json_decode($output, true);
        return $result !== null ? $result : $output;
    }

    /**
     * Check rate limits for tool execution
     */
    private function checkRateLimit(string $toolName): void
    {
        $tool = ToolRegistry::get($toolName);
        $rateLimit = $tool['safety']['rate_limit'] ?? null;

        if (!$rateLimit) {
            return;
        }

        $key = "tool_rate_limit:{$toolName}";

        if (!RateLimit::check($key, $rateLimit)) {
            throw new \RuntimeException("Rate limit exceeded for tool: {$toolName}");
        }
    }

    /**
     * Log tool call to database
     */
    private function logToolCall(string $callId, string $toolName, array $parameters): void
    {
        try {
            DB::execute(
                'INSERT INTO tool_calls (id, execution_id, tool_name, parameters, created_at) VALUES (?, ?, ?, ?, ?)',
                [
                    $callId,
                    $this->executionId,
                    $toolName,
                    json_encode($parameters),
                    date('Y-m-d H:i:s')
                ]
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to log tool call', [
                'call_id' => $callId,
                'tool' => $toolName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log tool execution result
     */
    private function logToolResult(string $callId, mixed $result, float $duration, bool $success, ?string $error = null): void
    {
        try {
            DB::execute(
                'UPDATE tool_calls SET result = ?, duration_ms = ?, success = ?, error = ?, completed_at = ? WHERE id = ?',
                [
                    $success ? json_encode($result) : null,
                    (int)$duration,
                    $success ? 1 : 0,
                    $error,
                    date('Y-m-d H:i:s'),
                    $callId
                ]
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to log tool result', [
                'call_id' => $callId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Emit tool start event via SSE
     */
    private function emitStart(string $callId, string $toolName, array $parameters): void
    {
        if (!$this->sse || !$this->convoId()) {
            return;
        }

        $this->sse->emitToolStart($this->convoId(), $toolName, $parameters);
    }

    /**
     * Emit tool completion event via SSE
     */
    private function emitComplete(string $callId, string $toolName, mixed $result, float $duration): void
    {
        if (!$this->sse || !$this->convoId()) {
            return;
        }

        $this->sse->emitToolEnd($this->convoId(), $toolName, (int)$duration, true, null);
    }

    /**
     * Emit tool error event via SSE
     */
    private function emitError(string $callId, string $toolName, string $error, float $duration): void
    {
        if (!$this->sse || !$this->convoId()) {
            return;
        }

        $this->sse->emitToolEnd($this->convoId(), $toolName, (int)$duration, false, $error);
    }
}
