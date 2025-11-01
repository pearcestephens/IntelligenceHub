<?php

/**
 * Tool Execution Integration Tests
 * Tests tool system registration, validation, execution, and error handling
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package Tests\Integration
 */

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Tools\ToolRegistry;
use App\Tools\ToolExecutor;
use App\DB;
use App\RedisClient;

class ToolExecutionIntegrationTest extends TestCase
{
    private ToolRegistry $registry;
    private ToolExecutor $executor;
    private array $testExecutionIds = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->registry = new ToolRegistry();
        $this->executor = new ToolExecutor(null);
        
        // Register test tools
        $this->registerTestTools();
    }
    
    protected function tearDown(): void
    {
        // Clean up test execution records
        foreach ($this->testExecutionIds as $execId) {
            DB::query('DELETE FROM tool_executions WHERE id = ?', [$execId]);
            RedisClient::del("tool_execution:{$execId}");
        }
        
        parent::tearDown();
    }
    
    private function registerTestTools(): void
    {
        // Register a simple calculator tool
        $this->registry->register('calculator', [
            'name' => 'calculator',
            'description' => 'Performs basic math operations',
            'parameters' => [
                'operation' => ['type' => 'string', 'enum' => ['add', 'subtract', 'multiply', 'divide']],
                'a' => ['type' => 'number'],
                'b' => ['type' => 'number']
            ],
            'required' => ['operation', 'a', 'b'],
            'handler' => function($params) {
                switch ($params['operation']) {
                    case 'add':
                        return $params['a'] + $params['b'];
                    case 'subtract':
                        return $params['a'] - $params['b'];
                    case 'multiply':
                        return $params['a'] * $params['b'];
                    case 'divide':
                        if ($params['b'] == 0) {
                            throw new \Exception('Division by zero');
                        }
                        return $params['a'] / $params['b'];
                    default:
                        throw new \Exception('Unknown operation');
                }
            }
        ]);
        
        // Register async file processor tool
        $this->registry->register('file_processor', [
            'name' => 'file_processor',
            'description' => 'Processes files asynchronously',
            'parameters' => [
                'filename' => ['type' => 'string'],
                'action' => ['type' => 'string', 'enum' => ['read', 'count_lines', 'validate']]
            ],
            'required' => ['filename', 'action'],
            'async' => true,
            'handler' => function($params) {
                sleep(1); // Simulate processing
                return [
                    'filename' => $params['filename'],
                    'action' => $params['action'],
                    'result' => 'processed',
                    'timestamp' => time()
                ];
            }
        ]);
    }
    
    /**
     * @test
     */
    public function it_executes_simple_tool_successfully(): void
    {
        $result = $this->executor->execute('calculator', [
            'operation' => 'add',
            'a' => 5,
            'b' => 3
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(8, $result['result']);
        $this->assertArrayHasKey('execution_time_ms', $result);
        
        // Record for cleanup
        if (isset($result['execution_id'])) {
            $this->testExecutionIds[] = $result['execution_id'];
        }
    }
    
    /**
     * @test
     */
    public function it_validates_required_parameters(): void
    {
        $result = $this->executor->execute('calculator', [
            'operation' => 'add',
            'a' => 5
            // Missing 'b' parameter
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('required', strtolower($result['error']));
    }
    
    /**
     * @test
     */
    public function it_validates_parameter_types(): void
    {
        $result = $this->executor->execute('calculator', [
            'operation' => 'add',
            'a' => 'not-a-number',
            'b' => 3
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
    
    /**
     * @test
     */
    public function it_handles_tool_execution_errors(): void
    {
        $result = $this->executor->execute('calculator', [
            'operation' => 'divide',
            'a' => 10,
            'b' => 0
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Division by zero', $result['error']);
        
        // Verify error was logged
        if (isset($result['execution_id'])) {
            $this->testExecutionIds[] = $result['execution_id'];
            
            $execution = DB::query(
                'SELECT * FROM tool_executions WHERE id = ?',
                [$result['execution_id']]
            );
            
            $this->assertEquals('error', $execution[0]['status']);
        }
    }
    
    /**
     * @test
     */
    public function it_executes_async_tools(): void
    {
        $result = $this->executor->execute('file_processor', [
            'filename' => 'test.txt',
            'action' => 'read'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('execution_id', $result);
        $this->assertArrayHasKey('status', $result);
        
        $this->testExecutionIds[] = $result['execution_id'];
        
        // For async, status might be 'pending' or 'running'
        $this->assertContains($result['status'], ['pending', 'running', 'completed']);
    }
    
    /**
     * @test
     */
    public function it_tracks_tool_execution_metrics(): void
    {
        // Execute multiple times
        for ($i = 0; $i < 5; $i++) {
            $result = $this->executor->execute('calculator', [
                'operation' => 'multiply',
                'a' => $i,
                'b' => 2
            ]);
            
            if (isset($result['execution_id'])) {
                $this->testExecutionIds[] = $result['execution_id'];
            }
        }
        
        // Get metrics
        $metrics = $this->executor->getMetrics('calculator');
        
        $this->assertArrayHasKey('total_executions', $metrics);
        $this->assertGreaterThanOrEqual(5, $metrics['total_executions']);
        $this->assertArrayHasKey('success_rate', $metrics);
        $this->assertArrayHasKey('avg_execution_time_ms', $metrics);
    }
    
    /**
     * @test
     */
    public function it_implements_tool_rate_limiting(): void
    {
        $toolName = 'calculator';
        $limit = 10;
        
        // Set rate limit: 10 executions per minute
        $this->registry->setRateLimit($toolName, $limit, 60);
        
        $successCount = 0;
        $rateLimitedCount = 0;
        
        // Try to execute 15 times rapidly
        for ($i = 0; $i < 15; $i++) {
            $result = $this->executor->execute($toolName, [
                'operation' => 'add',
                'a' => $i,
                'b' => 1
            ]);
            
            if ($result['success']) {
                $successCount++;
                if (isset($result['execution_id'])) {
                    $this->testExecutionIds[] = $result['execution_id'];
                }
            } else {
                if (isset($result['error']) && strpos($result['error'], 'rate limit') !== false) {
                    $rateLimitedCount++;
                }
            }
        }
        
        $this->assertEquals($limit, $successCount, "Should allow exactly {$limit} executions");
        $this->assertEquals(5, $rateLimitedCount, 'Should rate-limit 5 executions');
    }
    
    /**
     * @test
     */
    public function it_supports_tool_chaining(): void
    {
        // Execute first tool
        $result1 = $this->executor->execute('calculator', [
            'operation' => 'add',
            'a' => 5,
            'b' => 3
        ]);
        
        $this->assertTrue($result1['success']);
        $intermediateValue = $result1['result'];
        
        if (isset($result1['execution_id'])) {
            $this->testExecutionIds[] = $result1['execution_id'];
        }
        
        // Use result in second tool execution
        $result2 = $this->executor->execute('calculator', [
            'operation' => 'multiply',
            'a' => $intermediateValue,
            'b' => 2
        ]);
        
        $this->assertTrue($result2['success']);
        $this->assertEquals(16, $result2['result']); // (5+3)*2 = 16
        
        if (isset($result2['execution_id'])) {
            $this->testExecutionIds[] = $result2['execution_id'];
        }
    }
    
    /**
     * @test
     */
    public function it_handles_concurrent_tool_executions(): void
    {
        $executionIds = [];
        
        // Simulate concurrent executions
        $promises = [];
        for ($i = 0; $i < 5; $i++) {
            $result = $this->executor->execute('calculator', [
                'operation' => 'add',
                'a' => $i,
                'b' => 10
            ]);
            
            if (isset($result['execution_id'])) {
                $executionIds[] = $result['execution_id'];
            }
        }
        
        $this->testExecutionIds = array_merge($this->testExecutionIds, $executionIds);
        
        // Verify all executions were recorded
        $this->assertCount(5, $executionIds);
        
        // Check database for all executions
        foreach ($executionIds as $execId) {
            $execution = DB::query('SELECT * FROM tool_executions WHERE id = ?', [$execId]);
            $this->assertNotEmpty($execution, "Execution {$execId} should be recorded");
        }
    }
    
    /**
     * @test
     */
    public function it_caches_tool_results_when_enabled(): void
    {
        // Enable caching for calculator tool
        $this->registry->enableCaching('calculator', 60); // 60 seconds TTL
        
        $params = [
            'operation' => 'add',
            'a' => 100,
            'b' => 200
        ];
        
        // First execution (cache miss)
        $result1 = $this->executor->execute('calculator', $params);
        $execTime1 = $result1['execution_time_ms'];
        
        if (isset($result1['execution_id'])) {
            $this->testExecutionIds[] = $result1['execution_id'];
        }
        
        // Second execution (should be cached)
        $result2 = $this->executor->execute('calculator', $params);
        $execTime2 = $result2['execution_time_ms'];
        
        $this->assertTrue($result2['success']);
        $this->assertEquals($result1['result'], $result2['result']);
        
        // Cached execution should be faster
        if (isset($result2['cached']) && $result2['cached'] === true) {
            $this->assertLessThan($execTime1, $execTime2, 'Cached execution should be faster');
        }
    }
    
    /**
     * @test
     */
    public function it_handles_tool_timeouts(): void
    {
        // Register slow tool with 2 second timeout
        $this->registry->register('slow_tool', [
            'name' => 'slow_tool',
            'description' => 'A tool that takes too long',
            'parameters' => ['delay' => ['type' => 'number']],
            'required' => ['delay'],
            'timeout' => 2,
            'handler' => function($params) {
                sleep($params['delay']);
                return 'completed';
            }
        ]);
        
        // Execute with delay > timeout
        $result = $this->executor->execute('slow_tool', ['delay' => 5]);
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('timeout', strtolower($result['error']));
        
        if (isset($result['execution_id'])) {
            $this->testExecutionIds[] = $result['execution_id'];
        }
    }
    
    /**
     * @test
     */
    public function it_provides_tool_execution_history(): void
    {
        // Execute several tools
        for ($i = 0; $i < 3; $i++) {
            $result = $this->executor->execute('calculator', [
                'operation' => 'add',
                'a' => $i,
                'b' => 10
            ]);
            
            if (isset($result['execution_id'])) {
                $this->testExecutionIds[] = $result['execution_id'];
            }
        }
        
        // Get execution history
        $history = $this->executor->getHistory('calculator', 10);
        
        $this->assertIsArray($history);
        $this->assertGreaterThanOrEqual(3, count($history));
        
        // Verify history structure
        foreach ($history as $entry) {
            $this->assertArrayHasKey('tool_name', $entry);
            $this->assertArrayHasKey('status', $entry);
            $this->assertArrayHasKey('execution_time_ms', $entry);
            $this->assertArrayHasKey('created_at', $entry);
        }
    }
}
