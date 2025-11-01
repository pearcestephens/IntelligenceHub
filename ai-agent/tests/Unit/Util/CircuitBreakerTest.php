<?php

/**
 * Unit tests for CircuitBreaker utility
 * 
 * Tests circuit breaker pattern implementation including:
 * - State transitions (CLOSED → OPEN → HALF_OPEN → CLOSED)
 * - Failure threshold detection
 * - Reset timeout behavior
 * - Metrics reporting
 * 
 * @package Tests\Unit\Util
 */

declare(strict_types=1);

namespace Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use App\Util\CircuitBreaker;
use App\RedisClient;
use App\Logger;
use RuntimeException;
use Exception;

class CircuitBreakerTest extends TestCase
{
    private CircuitBreaker $breaker;
    private RedisClient $redis;
    private Logger $logger;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Use real Redis for integration-like testing
        $config = new \App\Config();
        $this->logger = new Logger($config);
        $this->redis = new RedisClient($config, $this->logger);
        
        // Clean up any existing circuit breaker state
        $this->redis->del('circuit:test_service:state');
        $this->redis->del('circuit:test_service:failures');
        $this->redis->del('circuit:test_service:open_at');
        
        // Create breaker with low thresholds for testing
        $this->breaker = new CircuitBreaker(
            $this->redis,
            $this->logger,
            'test_service',
            3, // 3 failures to open
            2  // 2 seconds to reset
        );
    }
    
    protected function tearDown(): void
    {
        // Clean up
        $this->redis->del('circuit:test_service:state');
        $this->redis->del('circuit:test_service:failures');
        $this->redis->del('circuit:test_service:open_at');
        
        parent::tearDown();
    }
    
    public function test_circuit_starts_closed(): void
    {
        $metrics = $this->breaker->getMetrics();
        
        $this->assertSame('closed', $metrics['state']);
        $this->assertSame(0, $metrics['failure_count']);
    }
    
    public function test_successful_operation_passes_through(): void
    {
        $result = $this->breaker->call(
            fn() => 'success',
            'test_operation'
        );
        
        $this->assertSame('success', $result);
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('closed', $metrics['state']);
        $this->assertSame(0, $metrics['failure_count']);
    }
    
    public function test_single_failure_increments_count(): void
    {
        try {
            $this->breaker->call(
                fn() => throw new Exception('Test failure'),
                'test_operation'
            );
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertSame('Test failure', $e->getMessage());
        }
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('closed', $metrics['state']); // Still closed
        $this->assertSame(1, $metrics['failure_count']);
    }
    
    public function test_circuit_opens_after_threshold(): void
    {
        // Trigger 3 failures (threshold)
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('open', $metrics['state']);
        $this->assertSame(3, $metrics['failure_count']);
    }
    
    public function test_circuit_blocks_calls_when_open(): void
    {
        // Open the circuit
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        // Verify circuit is open
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('open', $metrics['state']);
        
        // Attempt operation - should be blocked
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Circuit breaker OPEN');
        
        $this->breaker->call(
            fn() => 'should not execute',
            'blocked_operation'
        );
    }
    
    public function test_circuit_transitions_to_half_open_after_timeout(): void
    {
        // Open the circuit
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('open', $metrics['state']);
        
        // Wait for reset timeout (2 seconds)
        sleep(3);
        
        // Next call should transition to half_open and succeed
        $result = $this->breaker->call(
            fn() => 'recovered',
            'test_operation'
        );
        
        $this->assertSame('recovered', $result);
        
        // Give Redis more time to update state (half_open → closed transition)
        usleep(250000); // 250ms - Increased from 100ms for reliability
        
        // Verify circuit closed after successful half_open attempt
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('closed', $metrics['state'], 
            'Circuit should be closed after successful recovery, got: ' . $metrics['state']);
        $this->assertSame(0, $metrics['failure_count']);
    }
    
    public function test_circuit_reopens_if_half_open_fails(): void
    {
        // Open the circuit
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        // Wait for reset timeout
        sleep(3);
        
        // Attempt operation that fails in half_open state
        try {
            $this->breaker->call(
                fn() => throw new Exception('Still failing'),
                'test_operation'
            );
            $this->fail('Expected exception was not thrown');
        } catch (Exception $e) {
            $this->assertSame('Still failing', $e->getMessage());
        }
        
        // Circuit should be open again
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('open', $metrics['state']);
    }
    
    public function test_successful_operation_resets_failure_count(): void
    {
        // Record 2 failures (below threshold)
        for ($i = 0; $i < 2; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame(2, $metrics['failure_count']);
        
        // Successful operation should reset count
        $result = $this->breaker->call(
            fn() => 'success',
            'test_operation'
        );
        
        $this->assertSame('success', $result);
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame(0, $metrics['failure_count']);
    }
    
    public function test_manual_reset(): void
    {
        // Open the circuit
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('open', $metrics['state']);
        
        // Manually reset
        $this->breaker->reset();
        
        // Circuit should be closed
        $metrics = $this->breaker->getMetrics();
        $this->assertSame('closed', $metrics['state']);
        $this->assertSame(0, $metrics['failure_count']);
        
        // Operations should work
        $result = $this->breaker->call(
            fn() => 'works after reset',
            'test_operation'
        );
        
        $this->assertSame('works after reset', $result);
    }
    
    public function test_metrics_include_time_until_reset(): void
    {
        // Open the circuit
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->breaker->call(
                    fn() => throw new Exception("Failure {$i}"),
                    'test_operation'
                );
            } catch (Exception $e) {
                // Expected
            }
        }
        
        $metrics = $this->breaker->getMetrics();
        
        $this->assertArrayHasKey('time_until_reset', $metrics);
        $this->assertIsInt($metrics['time_until_reset']);
        $this->assertGreaterThan(0, $metrics['time_until_reset']);
        $this->assertLessThanOrEqual(2, $metrics['time_until_reset']); // Reset timeout is 2s
    }
}
