<?php

/**
 * Unit tests for Retry utility
 * 
 * Tests retry logic including:
 * - Exponential backoff with jitter
 * - Transient error detection
 * - Permanent error handling (no retry)
 * - Retry exhaustion
 * - Fixed delay and immediate retry variants
 * 
 * @package Tests\Unit\Util
 */

declare(strict_types=1);

namespace Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use App\Util\Retry;
use App\Logger;
use RuntimeException;
use InvalidArgumentException;
use Exception;

class RetryTest extends TestCase
{
    private Logger $logger;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $config = new \App\Config();
        $this->logger = new Logger($config);
    }
    
    public function test_successful_operation_returns_immediately(): void
    {
        $attempts = 0;
        
        $result = Retry::withBackoff(
            function() use (&$attempts) {
                $attempts++;
                return 'success';
            },
            3, // max attempts
            100, // initial delay
            2.0, // multiplier
            5000, // max delay
            $this->logger
        );
        
        $this->assertSame('success', $result);
        $this->assertSame(1, $attempts); // Only one attempt
    }
    
    public function test_transient_error_retries_and_succeeds(): void
    {
        $attempts = 0;
        
        $result = Retry::withBackoff(
            function() use (&$attempts) {
                $attempts++;
                if ($attempts < 3) {
                    throw new RuntimeException('Connection timeout'); // Transient
                }
                return 'success after retries';
            },
            3, // max attempts
            10, // short delay for test
            2.0,
            100,
            $this->logger
        );
        
        $this->assertSame('success after retries', $result);
        $this->assertSame(3, $attempts); // Failed twice, succeeded third time
    }
    
    public function test_permanent_error_fails_immediately(): void
    {
        $attempts = 0;
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid input');
        
        Retry::withBackoff(
            function() use (&$attempts) {
                $attempts++;
                throw new InvalidArgumentException('Invalid input'); // Permanent error
            },
            3, // max attempts
            10,
            2.0,
            100,
            $this->logger
        );
        
        // Should fail immediately without retries
        $this->assertSame(1, $attempts);
    }
    
    public function test_retry_exhaustion_throws_last_exception(): void
    {
        $attempts = 0;
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service unavailable');
        
        Retry::withBackoff(
            function() use (&$attempts) {
                $attempts++;
                throw new RuntimeException('Service unavailable'); // Transient
            },
            3, // max attempts
            10,
            2.0,
            100,
            $this->logger
        );
        
        // Should attempt all 3 times
        $this->assertSame(3, $attempts);
    }
    
    public function test_exponential_backoff_increases_delay(): void
    {
        $attempts = 0;
        $timestamps = [];
        
        try {
            Retry::withBackoff(
                function() use (&$attempts, &$timestamps) {
                    $attempts++;
                    $timestamps[] = microtime(true);
                    throw new RuntimeException('Timeout'); // Transient
                },
                3,
                50, // 50ms initial
                2.0, // Double each time
                500,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame(3, $attempts);
        $this->assertCount(3, $timestamps);
        
        // Check delays increased (approximately)
        // Delay 1: ~50ms (+ jitter up to 25ms)
        // Delay 2: ~100ms (+ jitter up to 50ms)
        $delay1 = ($timestamps[1] - $timestamps[0]) * 1000; // ms
        $delay2 = ($timestamps[2] - $timestamps[1]) * 1000; // ms
        
        $this->assertGreaterThan(40, $delay1); // Allow some variance
        $this->assertGreaterThan(80, $delay2);
        $this->assertGreaterThan($delay1, $delay2); // Second delay should be longer
    }
    
    public function test_max_delay_cap_enforced(): void
    {
        $attempts = 0;
        $timestamps = [];
        
        try {
            Retry::withBackoff(
                function() use (&$attempts, &$timestamps) {
                    $attempts++;
                    $timestamps[] = microtime(true);
                    throw new RuntimeException('Timeout');
                },
                4,
                1000, // Start at 1s
                10.0, // Huge multiplier
                200, // But cap at 200ms
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        // All delays should be capped at 200ms (+jitter)
        for ($i = 1; $i < count($timestamps); $i++) {
            $delay = ($timestamps[$i] - $timestamps[$i-1]) * 1000;
            $this->assertLessThan(300, $delay); // Cap + max jitter
        }
    }
    
    public function test_transient_error_detection_timeout(): void
    {
        $attempts = 0;
        
        try {
            Retry::withBackoff(
                function() use (&$attempts) {
                    $attempts++;
                    throw new RuntimeException('Connection timed out');
                },
                2,
                10,
                2.0,
                100,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame(2, $attempts); // Should retry
    }
    
    public function test_transient_error_detection_rate_limit(): void
    {
        $attempts = 0;
        
        try {
            Retry::withBackoff(
                function() use (&$attempts) {
                    $attempts++;
                    throw new RuntimeException('Rate limit exceeded');
                },
                2,
                10,
                2.0,
                100,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame(2, $attempts); // Should retry
    }
    
    public function test_transient_error_detection_503(): void
    {
        $attempts = 0;
        
        try {
            Retry::withBackoff(
                function() use (&$attempts) {
                    $attempts++;
                    throw new RuntimeException('HTTP 503 Service Unavailable');
                },
                2,
                10,
                2.0,
                100,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame(2, $attempts); // Should retry
    }
    
    public function test_operation_receives_attempt_number(): void
    {
        $receivedAttempts = [];
        
        try {
            Retry::withBackoff(
                function($attempt) use (&$receivedAttempts) {
                    $receivedAttempts[] = $attempt;
                    throw new RuntimeException('Timeout');
                },
                3,
                10,
                2.0,
                100,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame([1, 2, 3], $receivedAttempts);
    }
    
    public function test_fixed_delay_no_backoff(): void
    {
        $attempts = 0;
        $timestamps = [];
        
        try {
            Retry::withFixedDelay(
                function() use (&$attempts, &$timestamps) {
                    $attempts++;
                    $timestamps[] = microtime(true);
                    throw new RuntimeException('Timeout');
                },
                3,
                50, // Fixed 50ms delay
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $this->assertSame(3, $attempts);
        
        // Check delays are approximately equal
        $delay1 = ($timestamps[1] - $timestamps[0]) * 1000;
        $delay2 = ($timestamps[2] - $timestamps[1]) * 1000;
        
        $this->assertGreaterThan(40, $delay1);
        $this->assertGreaterThan(40, $delay2);
        $this->assertEqualsWithDelta($delay1, $delay2, 20); // Should be similar
    }
    
    public function test_immediate_retry_no_delay(): void
    {
        $attempts = 0;
        $startTime = microtime(true);
        
        try {
            Retry::immediate(
                function() use (&$attempts) {
                    $attempts++;
                    throw new RuntimeException('Timeout');
                },
                3,
                $this->logger
            );
        } catch (Exception $e) {
            // Expected
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000; // ms
        
        $this->assertSame(3, $attempts);
        $this->assertLessThan(10, $totalTime); // Should be very fast (no delays)
    }
}
