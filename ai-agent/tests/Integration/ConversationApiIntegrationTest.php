<?php

/**
 * API Integration Tests - Conversation Management
 * Tests the complete conversation API flow including authentication, CRUD operations, and validation
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package Tests\Integration
 */

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\DB;
use App\RedisClient;

class ConversationApiIntegrationTest extends TestCase
{
    private string $apiBaseUrl;
    private string $apiKey;
    private array $createdConversations = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up API configuration
        $this->apiBaseUrl = $_ENV['TEST_API_BASE_URL'] ?? 'http://localhost';
        $this->apiKey = $_ENV['TEST_API_KEY'] ?? 'test-api-key-' . bin2hex(random_bytes(16));
        
        // Clean up test data
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        // Clean up created conversations
        $this->cleanupTestData();
        
        parent::tearDown();
    }
    
    private function cleanupTestData(): void
    {
        foreach ($this->createdConversations as $conversationId) {
            try {
                DB::query('DELETE FROM conversations WHERE uuid = ?', [$conversationId]);
                DB::query('DELETE FROM messages WHERE conversation_uuid = ?', [$conversationId]);
                RedisClient::del("conversation:{$conversationId}");
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
        $this->createdConversations = [];
    }
    
    private function makeRequest(string $method, string $endpoint, ?array $data = null): array
    {
        $url = $this->apiBaseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle curl errors (returns false on failure)
        if ($response === false) {
            return [
                'status' => 0,
                'body' => ['error' => 'cURL error: ' . $curlError],
                'raw' => ''
            ];
        }
        
        return [
            'status' => $httpCode,
            'body' => json_decode($response, true) ?? [],
            'raw' => $response
        ];
    }
    
    /**
     * @test
     */
    public function it_creates_conversation_with_valid_request(): void
    {
        $response = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Integration Test Conversation',
            'model' => 'gpt-4',
            'system_message' => 'You are a helpful assistant.'
        ]);
        
        $this->assertEquals(201, $response['status'], 'Should return 201 Created');
        $this->assertArrayHasKey('success', $response['body']);
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('uuid', $response['body']['data']);
        
        $conversationId = $response['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Verify in database
        $dbResult = DB::query('SELECT * FROM conversations WHERE uuid = ?', [$conversationId]);
        $this->assertNotEmpty($dbResult, 'Conversation should exist in database');
        $this->assertEquals('Integration Test Conversation', $dbResult[0]['title']);
    }
    
    /**
     * @test
     */
    public function it_rejects_unauthenticated_requests(): void
    {
        $ch = curl_init($this->apiBaseUrl . '/agent/api/v1/conversations');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['title' => 'Test']));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(401, $httpCode, 'Should return 401 Unauthorized');
    }
    
    /**
     * @test
     */
    public function it_retrieves_conversation_by_id(): void
    {
        // Create conversation first
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Test Retrieval'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Retrieve it
        $getResponse = $this->makeRequest('GET', "/agent/api/v1/conversations/{$conversationId}");
        
        $this->assertEquals(200, $getResponse['status']);
        $this->assertTrue($getResponse['body']['success']);
        $this->assertEquals($conversationId, $getResponse['body']['data']['uuid']);
        $this->assertEquals('Test Retrieval', $getResponse['body']['data']['title']);
    }
    
    /**
     * @test
     */
    public function it_returns_404_for_nonexistent_conversation(): void
    {
        $fakeId = 'nonexistent-uuid-' . bin2hex(random_bytes(8));
        
        $response = $this->makeRequest('GET', "/agent/api/v1/conversations/{$fakeId}");
        
        $this->assertEquals(404, $response['status']);
        $this->assertFalse($response['body']['success']);
    }
    
    /**
     * @test
     */
    public function it_adds_message_to_conversation(): void
    {
        // Create conversation
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Test Messages'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Add message
        $messageResponse = $this->makeRequest('POST', '/agent/api/v1/messages', [
            'conversation_uuid' => $conversationId,
            'role' => 'user',
            'content' => 'Hello, this is a test message!'
        ]);
        
        $this->assertEquals(201, $messageResponse['status']);
        $this->assertTrue($messageResponse['body']['success']);
        $this->assertArrayHasKey('message_id', $messageResponse['body']['data']);
        
        // Verify message in database
        $messages = DB::query(
            'SELECT * FROM messages WHERE conversation_uuid = ? ORDER BY created_at DESC',
            [$conversationId]
        );
        
        $this->assertNotEmpty($messages);
        $this->assertEquals('user', $messages[0]['role']);
        $this->assertEquals('Hello, this is a test message!', $messages[0]['content']);
    }
    
    /**
     * @test
     */
    public function it_retrieves_conversation_messages_paginated(): void
    {
        // Create conversation and add multiple messages
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Pagination Test'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Add 15 messages
        for ($i = 1; $i <= 15; $i++) {
            $this->makeRequest('POST', '/agent/api/v1/messages', [
                'conversation_uuid' => $conversationId,
                'role' => $i % 2 === 0 ? 'assistant' : 'user',
                'content' => "Message {$i}"
            ]);
        }
        
        // Retrieve first page (default 10 per page)
        $page1Response = $this->makeRequest('GET', "/agent/api/v1/conversations/{$conversationId}/messages");
        
        $this->assertEquals(200, $page1Response['status']);
        $this->assertTrue($page1Response['body']['success']);
        $this->assertCount(10, $page1Response['body']['data']);
        $this->assertArrayHasKey('pagination', $page1Response['body']);
        $this->assertEquals(15, $page1Response['body']['pagination']['total']);
        
        // Retrieve second page
        $page2Response = $this->makeRequest(
            'GET',
            "/agent/api/v1/conversations/{$conversationId}/messages?page=2"
        );
        
        $this->assertCount(5, $page2Response['body']['data']);
    }
    
    /**
     * @test
     */
    public function it_validates_message_content_length(): void
    {
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Validation Test'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Try to add message that's too long (> 32000 chars)
        $longContent = str_repeat('A', 33000);
        
        $response = $this->makeRequest('POST', '/agent/api/v1/messages', [
            'conversation_uuid' => $conversationId,
            'role' => 'user',
            'content' => $longContent
        ]);
        
        $this->assertEquals(400, $response['status']);
        $this->assertFalse($response['body']['success']);
        $this->assertStringContainsString('too long', strtolower($response['body']['error']['message'] ?? ''));
    }
    
    /**
     * @test
     */
    public function it_handles_rate_limiting(): void
    {
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Rate Limit Test'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        // Make rapid requests
        $responses = [];
        for ($i = 0; $i < 200; $i++) {
            $response = $this->makeRequest('POST', '/agent/api/v1/messages', [
                'conversation_uuid' => $conversationId,
                'role' => 'user',
                'content' => "Rate limit test {$i}"
            ]);
            
            $responses[] = $response['status'];
            
            if ($response['status'] === 429) {
                break; // Rate limit hit
            }
        }
        
        // Should eventually hit rate limit
        $this->assertContains(429, $responses, 'Should hit rate limit after many requests');
    }
    
    /**
     * @test
     */
    public function it_supports_idempotency_keys(): void
    {
        $createResponse = $this->makeRequest('POST', '/agent/api/v1/conversations', [
            'title' => 'Idempotency Test'
        ]);
        
        $conversationId = $createResponse['body']['data']['uuid'];
        $this->createdConversations[] = $conversationId;
        
        $idempotencyKey = 'test-idem-key-' . bin2hex(random_bytes(16));
        
        // Make same request twice with same idempotency key
        $ch1 = curl_init($this->apiBaseUrl . '/agent/api/v1/messages');
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_POST, true);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . $idempotencyKey
        ]);
        curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode([
            'conversation_uuid' => $conversationId,
            'role' => 'user',
            'content' => 'Idempotent message'
        ]));
        
        $response1 = curl_exec($ch1);
        curl_close($ch1);
        
        $ch2 = curl_init($this->apiBaseUrl . '/agent/api/v1/messages');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . $idempotencyKey
        ]);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode([
            'conversation_uuid' => $conversationId,
            'role' => 'user',
            'content' => 'Idempotent message'
        ]));
        
        $response2 = curl_exec($ch2);
        curl_close($ch2);
        
        $data1 = json_decode($response1, true);
        $data2 = json_decode($response2, true);
        
        // Should return same message ID
        $this->assertEquals(
            $data1['data']['message_id'] ?? null,
            $data2['data']['message_id'] ?? null,
            'Idempotent requests should return same result'
        );
        
        // Should only create one message in database
        $messages = DB::query(
            'SELECT COUNT(*) as count FROM messages WHERE conversation_uuid = ? AND content = ?',
            [$conversationId, 'Idempotent message']
        );
        
        $this->assertEquals(1, $messages[0]['count'], 'Should only create one message');
    }
}
