<?php

/**
 * Performance & Load Tests
 * Tests system performance, response times, and concurrent load handling
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package Tests\Performance
 */

declare(strict_types=1);

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;
use App\DB;
use App\RedisClient;

class PerformanceTest extends TestCase
{
    private string $apiUrl;
    private string $apiKey;
    private array $testData = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->apiUrl = $_ENV['API_URL'] ?? 'http://localhost:8000/api';
        $this->apiKey = $_ENV['API_KEY'] ?? 'test-api-key';
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        foreach ($this->testData as $data) {
            if (isset($data['conversation_uuid'])) {
                DB::query('DELETE FROM messages WHERE conversation_uuid = ?', [$data['conversation_uuid']]);
                DB::query('DELETE FROM conversations WHERE uuid = ?', [$data['conversation_uuid']]);
            }
        }
        
        parent::tearDown();
    }
    
    private function makeTimedRequest(string $method, string $endpoint, array $data = []): array
    {
        $startTime = microtime(true);
        
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ]
        ]);
        
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $duration = (microtime(true) - $startTime) * 1000; // Convert to ms
        
        return [
            'status' => $httpCode,
            'body' => json_decode($response, true),
            'duration_ms' => $duration
        ];
    }
    
    /**
     * @test
     * @group performance
     */
    public function conversation_creation_meets_performance_target(): void
    {
        $measurements = [];
        
        // Run 20 conversation creation requests
        for ($i = 0; $i < 20; $i++) {
            $response = $this->makeTimedRequest('POST', '/conversations', [
                'title' => "Performance Test {$i}",
                'model' => 'gpt-4'
            ]);
            
            $this->assertEquals(201, $response['status']);
            $measurements[] = $response['duration_ms'];
            
            if (isset($response['body']['uuid'])) {
                $this->testData[] = ['conversation_uuid' => $response['body']['uuid']];
            }
        }
        
        // Calculate statistics
        $avg = array_sum($measurements) / count($measurements);
        $p95 = $this->calculatePercentile($measurements, 95);
        $p99 = $this->calculatePercentile($measurements, 99);
        
        echo "\n=== Conversation Creation Performance ===\n";
        echo "Average: " . round($avg, 2) . "ms\n";
        echo "P95: " . round($p95, 2) . "ms\n";
        echo "P99: " . round($p99, 2) . "ms\n";
        
        // Targets: avg < 200ms, p95 < 500ms
        $this->assertLessThan(200, $avg, 'Average response time should be < 200ms');
        $this->assertLessThan(500, $p95, 'P95 response time should be < 500ms');
    }
    
    /**
     * @test
     * @group performance
     */
    public function message_posting_meets_performance_target(): void
    {
        // Create conversation first
        $conv = $this->makeTimedRequest('POST', '/conversations', [
            'title' => 'Message Performance Test',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $conv['body']['uuid'];
        $this->testData[] = ['conversation_uuid' => $conversationUuid];
        
        $measurements = [];
        
        // Post 30 messages
        for ($i = 0; $i < 30; $i++) {
            $response = $this->makeTimedRequest('POST', '/messages', [
                'conversation_uuid' => $conversationUuid,
                'role' => $i % 2 === 0 ? 'user' : 'assistant',
                'content' => "Message {$i} content for performance testing."
            ]);
            
            $this->assertEquals(201, $response['status']);
            $measurements[] = $response['duration_ms'];
        }
        
        $avg = array_sum($measurements) / count($measurements);
        $p95 = $this->calculatePercentile($measurements, 95);
        
        echo "\n=== Message Posting Performance ===\n";
        echo "Average: " . round($avg, 2) . "ms\n";
        echo "P95: " . round($p95, 2) . "ms\n";
        
        $this->assertLessThan(150, $avg, 'Average message posting should be < 150ms');
        $this->assertLessThan(300, $p95, 'P95 message posting should be < 300ms');
    }
    
    /**
     * @test
     * @group performance
     */
    public function message_retrieval_with_pagination_is_fast(): void
    {
        // Create conversation with 100 messages
        $conv = $this->makeTimedRequest('POST', '/conversations', [
            'title' => 'Retrieval Performance Test',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $conv['body']['uuid'];
        $this->testData[] = ['conversation_uuid' => $conversationUuid];
        
        // Bulk insert messages
        for ($i = 0; $i < 100; $i++) {
            DB::query(
                'INSERT INTO messages (conversation_uuid, role, content, created_at) VALUES (?, ?, ?, NOW())',
                [$conversationUuid, $i % 2 === 0 ? 'user' : 'assistant', "Message {$i}"]
            );
        }
        
        // Test pagination retrieval
        $measurements = [];
        
        for ($page = 1; $page <= 5; $page++) {
            $response = $this->makeTimedRequest('GET', "/conversations/{$conversationUuid}/messages?page={$page}&limit=20");
            
            $this->assertEquals(200, $response['status']);
            $measurements[] = $response['duration_ms'];
        }
        
        $avg = array_sum($measurements) / count($measurements);
        
        echo "\n=== Message Retrieval Performance ===\n";
        echo "Average (100 messages, paginated): " . round($avg, 2) . "ms\n";
        
        $this->assertLessThan(100, $avg, 'Paginated retrieval should be < 100ms');
    }
    
    /**
     * @test
     * @group performance
     */
    public function database_query_performance_is_acceptable(): void
    {
        $measurements = [];
        
        // Test query performance directly
        for ($i = 0; $i < 50; $i++) {
            $startTime = microtime(true);
            
            DB::query('SELECT * FROM conversations ORDER BY created_at DESC LIMIT 10');
            
            $duration = (microtime(true) - $startTime) * 1000;
            $measurements[] = $duration;
        }
        
        $avg = array_sum($measurements) / count($measurements);
        $p95 = $this->calculatePercentile($measurements, 95);
        
        echo "\n=== Database Query Performance ===\n";
        echo "Average: " . round($avg, 2) . "ms\n";
        echo "P95: " . round($p95, 2) . "ms\n";
        
        $this->assertLessThan(50, $avg, 'Database queries should average < 50ms');
        $this->assertLessThan(100, $p95, 'P95 database queries should be < 100ms');
    }
    
    /**
     * @test
     * @group performance
     */
    public function redis_cache_operations_are_fast(): void
    {
        $measurements = [];
        
        // Test Redis SET and GET performance
        for ($i = 0; $i < 100; $i++) {
            $key = "perf_test_{$i}";
            $value = json_encode(['test' => 'data', 'index' => $i]);
            
            $startTime = microtime(true);
            
            RedisClient::set($key, $value, 60);
            RedisClient::get($key);
            RedisClient::del($key);
            
            $duration = (microtime(true) - $startTime) * 1000;
            $measurements[] = $duration;
        }
        
        $avg = array_sum($measurements) / count($measurements);
        $p95 = $this->calculatePercentile($measurements, 95);
        
        echo "\n=== Redis Cache Performance ===\n";
        echo "Average (SET+GET+DEL): " . round($avg, 2) . "ms\n";
        echo "P95: " . round($p95, 2) . "ms\n";
        
        $this->assertLessThan(10, $avg, 'Redis operations should be < 10ms');
        $this->assertLessThan(20, $p95, 'P95 Redis operations should be < 20ms');
    }
    
    /**
     * @test
     * @group load
     */
    public function system_handles_concurrent_requests(): void
    {
        $concurrentRequests = 20;
        $measurements = [];
        
        // Simulate concurrent conversation creations
        $mh = curl_multi_init();
        $handles = [];
        
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $ch = curl_init($this->apiUrl . '/conversations');
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'title' => "Concurrent Test {$i}",
                    'model' => 'gpt-4'
                ])
            ]);
            
            curl_multi_add_handle($mh, $ch);
            $handles[] = $ch;
        }
        
        $startTime = microtime(true);
        
        // Execute all requests
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);
        
        $totalDuration = (microtime(true) - $startTime) * 1000;
        
        // Collect results
        $successCount = 0;
        foreach ($handles as $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = curl_multi_getcontent($ch);
            
            if ($httpCode === 201) {
                $successCount++;
                $body = json_decode($response, true);
                if (isset($body['uuid'])) {
                    $this->testData[] = ['conversation_uuid' => $body['uuid']];
                }
            }
            
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        
        curl_multi_close($mh);
        
        echo "\n=== Concurrent Load Test ===\n";
        echo "Total requests: {$concurrentRequests}\n";
        echo "Successful: {$successCount}\n";
        echo "Total duration: " . round($totalDuration, 2) . "ms\n";
        echo "Avg per request: " . round($totalDuration / $concurrentRequests, 2) . "ms\n";
        
        $this->assertGreaterThanOrEqual($concurrentRequests * 0.95, $successCount, 'At least 95% of concurrent requests should succeed');
        $this->assertLessThan(5000, $totalDuration, 'Concurrent requests should complete in < 5 seconds');
    }
    
    /**
     * @test
     * @group load
     */
    public function system_maintains_performance_under_sustained_load(): void
    {
        $duration = 30; // 30 seconds
        $requestsPerSecond = 10;
        $measurements = [];
        
        $startTime = time();
        $requestCount = 0;
        
        // Create test conversation
        $conv = $this->makeTimedRequest('POST', '/conversations', [
            'title' => 'Sustained Load Test',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $conv['body']['uuid'];
        $this->testData[] = ['conversation_uuid' => $conversationUuid];
        
        echo "\n=== Sustained Load Test ({$duration}s) ===\n";
        
        while ((time() - $startTime) < $duration) {
            $cycleStart = microtime(true);
            
            // Send batch of requests
            for ($i = 0; $i < $requestsPerSecond; $i++) {
                $response = $this->makeTimedRequest('POST', '/messages', [
                    'conversation_uuid' => $conversationUuid,
                    'role' => 'user',
                    'content' => "Load test message {$requestCount}"
                ]);
                
                $measurements[] = $response['duration_ms'];
                $requestCount++;
            }
            
            // Wait to maintain rate
            $cycleTime = microtime(true) - $cycleStart;
            if ($cycleTime < 1.0) {
                usleep((1.0 - $cycleTime) * 1000000);
            }
        }
        
        // Calculate statistics
        $avg = array_sum($measurements) / count($measurements);
        $p95 = $this->calculatePercentile($measurements, 95);
        $p99 = $this->calculatePercentile($measurements, 99);
        
        echo "Total requests: {$requestCount}\n";
        echo "Average response: " . round($avg, 2) . "ms\n";
        echo "P95: " . round($p95, 2) . "ms\n";
        echo "P99: " . round($p99, 2) . "ms\n";
        
        $this->assertLessThan(300, $avg, 'Average should stay < 300ms under sustained load');
        $this->assertLessThan(1000, $p95, 'P95 should stay < 1000ms under sustained load');
    }
    
    /**
     * @test
     * @group performance
     */
    public function memory_usage_stays_within_bounds(): void
    {
        $initialMemory = memory_get_usage();
        
        // Create and process 100 conversations
        for ($i = 0; $i < 100; $i++) {
            $response = $this->makeTimedRequest('POST', '/conversations', [
                'title' => "Memory Test {$i}",
                'model' => 'gpt-4'
            ]);
            
            if (isset($response['body']['uuid'])) {
                $this->testData[] = ['conversation_uuid' => $response['body']['uuid']];
            }
        }
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = ($finalMemory - $initialMemory) / 1024 / 1024; // MB
        
        echo "\n=== Memory Usage Test ===\n";
        echo "Initial: " . round($initialMemory / 1024 / 1024, 2) . " MB\n";
        echo "Final: " . round($finalMemory / 1024 / 1024, 2) . " MB\n";
        echo "Increase: " . round($memoryIncrease, 2) . " MB\n";
        
        $this->assertLessThan(50, $memoryIncrease, 'Memory increase should be < 50 MB for 100 operations');
    }
    
    private function calculatePercentile(array $data, int $percentile): float
    {
        sort($data);
        $index = ceil(($percentile / 100) * count($data)) - 1;
        return $data[$index];
    }
}
