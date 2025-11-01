<?php

/**
 * Circuit Breaker Pattern Implementation
 *
 * Prevents cascading failures by temporarily disabling failing services.
 * Implements three states: CLOSED (normal), OPEN (blocking), HALF_OPEN (testing recovery).
 *
 * @package App\Util
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Util;

use App\RedisClient;
use App\Logger;
use RuntimeException;
use Exception;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';      // Normal operation
    private const STATE_OPEN = 'open';          // Failures detected, blocking calls
    private const STATE_HALF_OPEN = 'half_open'; // Testing if service recovered

    private RedisClient $redis;
    private Logger $logger;
    private string $serviceName;
    private int $failureThreshold;
    private int $resetTimeoutSeconds;
    private string $correlationId;

    /**
     * @param RedisClient $redis Redis client for state storage
     * @param Logger $logger Logger instance
     * @param string $serviceName Unique service identifier (e.g., 'openai', 'claude', 'database')
     * @param int $failureThreshold Number of failures before opening circuit (default: 5)
     * @param int $resetTimeoutSeconds Time to wait before attempting reset (default: 60)
     */
    public function __construct(
        RedisClient $redis,
        Logger $logger,
        string $serviceName,
        int $failureThreshold = 5,
        int $resetTimeoutSeconds = 60
    ) {
        $this->redis = $redis;
        $this->logger = $logger;
        $this->serviceName = $serviceName;
        $this->failureThreshold = $failureThreshold;
        $this->resetTimeoutSeconds = $resetTimeoutSeconds;
        $this->correlationId = Ids::correlationId();
    }

    /**
     * Execute operation through circuit breaker
     *
     * @param callable $operation The operation to execute
     * @param string $operationName Human-readable operation name for logging
     * @return mixed Operation result
     * @throws RuntimeException If circuit is OPEN and service unavailable
     * @throws Exception If operation fails permanently
     */
    public function call(callable $operation, string $operationName): mixed
    {
        // Check current circuit state
        $state = $this->getState();

        if ($state === self::STATE_OPEN) {
            // Service is down, check if we should attempt reset
            if (!$this->shouldAttemptReset()) {
                $this->logger->warning("Circuit breaker blocking call to {$this->serviceName}", [
                    'operation' => $operationName,
                    'correlation_id' => $this->correlationId,
                    'state' => self::STATE_OPEN
                ]);

                throw new RuntimeException(
                    "Circuit breaker OPEN for {$this->serviceName}: service temporarily unavailable"
                );
            }

            // Transition to half-open for testing
            $this->setState(self::STATE_HALF_OPEN);
            $this->logger->info("Circuit breaker transitioning to HALF_OPEN for {$this->serviceName}", [
                'correlation_id' => $this->correlationId
            ]);
        }

        // Attempt operation
        try {
            $result = $operation();

            // Success - reset failure count
            if ($state === self::STATE_HALF_OPEN) {
                $this->setState(self::STATE_CLOSED);
                $this->logger->info("Circuit breaker CLOSED for {$this->serviceName} (service recovered)", [
                    'operation' => $operationName,
                    'correlation_id' => $this->correlationId
                ]);
            }

            $this->recordSuccess();

            return $result;
        } catch (Exception $e) {
            // Record failure
            $this->recordFailure($e, $operationName);

            // Check if we should open circuit
            if ($this->shouldOpenCircuit()) {
                $this->setState(self::STATE_OPEN);
                $this->logger->error("Circuit breaker OPENED for {$this->serviceName} (threshold exceeded)", [
                    'operation' => $operationName,
                    'threshold' => $this->failureThreshold,
                    'error' => $e->getMessage(),
                    'correlation_id' => $this->correlationId
                ]);
            }

            // Re-throw exception for caller to handle
            throw $e;
        }
    }

    /**
     * Get current circuit state
     */
    private function getState(): string
    {
        $state = $this->redis->get("circuit:{$this->serviceName}:state");
        return $state ?? self::STATE_CLOSED;
    }

    /**
     * Set circuit state with expiry
     */
    private function setState(string $state): void
    {
        // State expires after 1 hour (fallback to CLOSED)
        $this->redis->setex("circuit:{$this->serviceName}:state", 3600, $state);

        if ($state === self::STATE_OPEN) {
            // Record when circuit was opened for reset timeout calculation
            $this->redis->setex(
                "circuit:{$this->serviceName}:open_at",
                3600,
                (string)time()
            );
        }
    }

    /**
     * Check if enough time has passed to attempt reset
     */
    private function shouldAttemptReset(): bool
    {
        $openAt = (int)($this->redis->get("circuit:{$this->serviceName}:open_at") ?? 0);

        if ($openAt === 0) {
            return true; // No open timestamp, allow reset
        }

        return (time() - $openAt) >= $this->resetTimeoutSeconds;
    }

    /**
     * Check if failure threshold exceeded
     */
    private function shouldOpenCircuit(): bool
    {
        $failures = (int)($this->redis->get("circuit:{$this->serviceName}:failures") ?? 0);
        return $failures >= $this->failureThreshold;
    }

    /**
     * Record successful operation (reset failure count)
     */
    private function recordSuccess(): void
    {
        $this->redis->del("circuit:{$this->serviceName}:failures");
    }

    /**
     * Record failed operation (increment failure count)
     */
    private function recordFailure(Exception $e, string $operation): void
    {
        $key = "circuit:{$this->serviceName}:failures";
        $failures = $this->redis->incr($key);

        // Failures expire after 5 minutes (sliding window)
        $this->redis->expire($key, 300);

        $this->logger->error("Circuit breaker recorded failure for {$this->serviceName}", [
            'operation' => $operation,
            'error' => $e->getMessage(),
            'failure_count' => $failures,
            'threshold' => $this->failureThreshold,
            'correlation_id' => $this->correlationId
        ]);
    }

    /**
     * Manually reset circuit (for testing or admin intervention)
     */
    public function reset(): void
    {
        $this->redis->del("circuit:{$this->serviceName}:state");
        $this->redis->del("circuit:{$this->serviceName}:failures");
        $this->redis->del("circuit:{$this->serviceName}:open_at");

        $this->logger->info("Circuit breaker manually reset for {$this->serviceName}", [
            'correlation_id' => $this->correlationId
        ]);
    }

    /**
     * Get circuit breaker metrics
     */
    public function getMetrics(): array
    {
        $state = $this->getState();
        $failures = (int)($this->redis->get("circuit:{$this->serviceName}:failures") ?? 0);
        $openAt = (int)($this->redis->get("circuit:{$this->serviceName}:open_at") ?? 0);

        return [
            'service' => $this->serviceName,
            'state' => $state,
            'failure_count' => $failures,
            'failure_threshold' => $this->failureThreshold,
            'open_at' => $openAt > 0 ? date('c', $openAt) : null,
            'reset_timeout_seconds' => $this->resetTimeoutSeconds,
            'time_until_reset' => $openAt > 0 ? max(0, $this->resetTimeoutSeconds - (time() - $openAt)) : null
        ];
    }
}
