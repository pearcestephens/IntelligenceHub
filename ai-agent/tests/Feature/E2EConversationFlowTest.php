<?php

/**
 * End-to-End User Journey Tests
 * Tests complete user workflows from start to finish
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package Tests\Feature
 */

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\DB;
use App\RedisClient;

class E2EConversationFlowTest extends TestCase
{
    private string $apiUrl;
    private string $apiKey;
    private array $createdConversations = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->apiUrl = $_ENV['API_URL'] ?? 'http://localhost:8000/api';
        $this->apiKey = $_ENV['API_KEY'] ?? 'test-api-key';
    }
    
    protected function tearDown(): void
    {
        // Clean up all created conversations
        foreach ($this->createdConversations as $uuid) {
            DB::query('DELETE FROM messages WHERE conversation_uuid = ?', [$uuid]);
            DB::query('DELETE FROM conversations WHERE uuid = ?', [$uuid]);
            DB::query('DELETE FROM context_cards WHERE conversation_uuid = ?', [$uuid]);
            DB::query('DELETE FROM tool_executions WHERE conversation_uuid = ?', [$uuid]);
            
            RedisClient::del("conversation:{$uuid}");
            RedisClient::del("conversation:messages:{$uuid}");
            RedisClient::del("conversation:context:{$uuid}");
        }
        
        parent::tearDown();
    }
    
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
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
     * @group e2e
     */
    public function user_journey_create_conversation_and_chat(): void
    {
        // Step 1: User creates a new conversation
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Help with PHP debugging',
            'model' => 'gpt-4',
            'system_message' => 'You are an expert PHP developer.'
        ]);
        
        $this->assertEquals(201, $createResponse['status']);
        $this->assertArrayHasKey('uuid', $createResponse['body']);
        
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2: User sends first message
        $message1Response = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'I have a bug in my code where variables are not being passed correctly.'
        ]);
        
        $this->assertEquals(201, $message1Response['status']);
        $this->assertArrayHasKey('id', $message1Response['body']);
        
        // Step 3: System generates response (simulate agent)
        $assistantResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'assistant',
            'content' => 'I can help debug this. Can you share the code snippet?'
        ]);
        
        $this->assertEquals(201, $assistantResponse['status']);
        
        // Step 4: User provides code
        $message2Response = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => "```php\nfunction calculate($a, $b) {\n  return $a + $b;\n}\n```"
        ]);
        
        $this->assertEquals(201, $message2Response['status']);
        
        // Step 5: User retrieves conversation history
        $historyResponse = $this->makeRequest('GET', "/conversations/{$conversationUuid}/messages");
        
        $this->assertEquals(200, $historyResponse['status']);
        $this->assertCount(3, $historyResponse['body']['messages']);
        
        // Step 6: Verify database state
        $dbConversation = DB::query('SELECT * FROM conversations WHERE uuid = ?', [$conversationUuid]);
        $this->assertNotEmpty($dbConversation);
        $this->assertEquals('Help with PHP debugging', $dbConversation[0]['title']);
        
        $dbMessages = DB::query('SELECT * FROM messages WHERE conversation_uuid = ?', [$conversationUuid]);
        $this->assertCount(3, $dbMessages);
    }
    
    /**
     * @test
     * @group e2e
     */
    public function user_journey_file_upload_and_knowledge_query(): void
    {
        // Step 1: Create conversation for documentation queries
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Documentation Search',
            'model' => 'gpt-4'
        ]);
        
        $this->assertEquals(201, $createResponse['status']);
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2: User uploads a document
        $uploadResponse = $this->makeRequest('POST', '/knowledge/upload', [
            'conversation_uuid' => $conversationUuid,
            'filename' => 'api-guide.md',
            'content' => "# API Guide\n\n## Authentication\n\nUse Bearer tokens.",
            'metadata' => ['category' => 'documentation']
        ]);
        
        $this->assertEquals(201, $uploadResponse['status']);
        $this->assertArrayHasKey('document_id', $uploadResponse['body']);
        
        // Step 3: User queries the knowledge base
        $searchResponse = $this->makeRequest('POST', '/knowledge/search', [
            'conversation_uuid' => $conversationUuid,
            'query' => 'How do I authenticate?',
            'top_k' => 3
        ]);
        
        $this->assertEquals(200, $searchResponse['status']);
        $this->assertArrayHasKey('results', $searchResponse['body']);
        $this->assertGreaterThan(0, count($searchResponse['body']['results']));
        
        // Step 4: User asks follow-up question using context
        $messageResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'Based on the documentation, explain authentication.'
        ]);
        
        $this->assertEquals(201, $messageResponse['status']);
        
        // Step 5: Verify knowledge base integration
        $contextCards = DB::query(
            'SELECT * FROM context_cards WHERE conversation_uuid = ?',
            [$conversationUuid]
        );
        
        $this->assertNotEmpty($contextCards, 'Context cards should be created from knowledge base results');
    }
    
    /**
     * @test
     * @group e2e
     */
    public function user_journey_tool_execution_workflow(): void
    {
        // Step 1: Create conversation for tool usage
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Data Analysis with Tools',
            'model' => 'gpt-4-tools'
        ]);
        
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2: User requests calculation
        $messageResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'Calculate 15 * 24 for me'
        ]);
        
        $this->assertEquals(201, $messageResponse['status']);
        
        // Step 3: System detects tool need and executes
        $toolResponse = $this->makeRequest('POST', '/tools/execute', [
            'conversation_uuid' => $conversationUuid,
            'tool_name' => 'calculator',
            'parameters' => [
                'operation' => 'multiply',
                'a' => 15,
                'b' => 24
            ]
        ]);
        
        $this->assertEquals(200, $toolResponse['status']);
        $this->assertEquals(360, $toolResponse['body']['result']);
        
        // Step 4: System returns result to user
        $resultMessage = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'assistant',
            'content' => 'The result is 360.',
            'metadata' => json_encode(['tool_execution_id' => $toolResponse['body']['execution_id']])
        ]);
        
        $this->assertEquals(201, $resultMessage['status']);
        
        // Step 5: Verify tool execution was logged
        $toolExecutions = DB::query(
            'SELECT * FROM tool_executions WHERE conversation_uuid = ?',
            [$conversationUuid]
        );
        
        $this->assertNotEmpty($toolExecutions);
        $this->assertEquals('calculator', $toolExecutions[0]['tool_name']);
        $this->assertEquals('completed', $toolExecutions[0]['status']);
    }
    
    /**
     * @test
     * @group e2e
     */
    public function user_journey_context_persistence_across_messages(): void
    {
        // Step 1: Create conversation
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Context Memory Test',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2: User provides context about themselves
        $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'I am working on a React project with TypeScript.'
        ]);
        
        // Step 3: System stores context card
        $contextResponse = $this->makeRequest('POST', '/context/store', [
            'conversation_uuid' => $conversationUuid,
            'type' => 'project_context',
            'content' => 'User is working on React + TypeScript project'
        ]);
        
        $this->assertEquals(201, $contextResponse['status']);
        
        // Step 4: User asks follow-up (without repeating context)
        $followUpResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'How do I configure the tsconfig file?'
        ]);
        
        $this->assertEquals(201, $followUpResponse['status']);
        
        // Step 5: System retrieves relevant context
        $retrieveContextResponse = $this->makeRequest('GET', "/context/{$conversationUuid}");
        
        $this->assertEquals(200, $retrieveContextResponse['status']);
        $this->assertGreaterThan(0, count($retrieveContextResponse['body']));
        
        // Verify context contains React/TypeScript reference
        $contextContents = array_column($retrieveContextResponse['body'], 'content');
        $hasReactContext = false;
        
        foreach ($contextContents as $content) {
            if (stripos($content, 'React') !== false || stripos($content, 'TypeScript') !== false) {
                $hasReactContext = true;
                break;
            }
        }
        
        $this->assertTrue($hasReactContext, 'Context should remember React/TypeScript');
    }
    
    /**
     * @test
     * @group e2e
     */
    public function user_journey_error_handling_and_recovery(): void
    {
        // Step 1: Create conversation
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Error Handling Test',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2: User sends invalid message (too long)
        $invalidContent = str_repeat('x', 35000); // Exceeds limit
        
        $errorResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => $invalidContent
        ]);
        
        $this->assertEquals(400, $errorResponse['status']);
        $this->assertArrayHasKey('error', $errorResponse['body']);
        
        // Step 3: User corrects and resends valid message
        $validResponse = $this->makeRequest('POST', '/messages', [
            'conversation_uuid' => $conversationUuid,
            'role' => 'user',
            'content' => 'This is a valid message.'
        ]);
        
        $this->assertEquals(201, $validResponse['status']);
        
        // Step 4: Verify conversation still intact
        $conversationResponse = $this->makeRequest('GET', "/conversations/{$conversationUuid}");
        
        $this->assertEquals(200, $conversationResponse['status']);
        $this->assertEquals('Error Handling Test', $conversationResponse['body']['title']);
    }
    
    /**
     * @test
     * @group e2e
     */
    public function user_journey_multi_turn_conversation_with_memory(): void
    {
        // Step 1: Create conversation
        $createResponse = $this->makeRequest('POST', '/conversations', [
            'title' => 'Multi-Turn Conversation',
            'model' => 'gpt-4'
        ]);
        
        $conversationUuid = $createResponse['body']['uuid'];
        $this->createdConversations[] = $conversationUuid;
        
        // Step 2-10: Simulate 8 turns of conversation
        $turns = [
            ['role' => 'user', 'content' => 'Hello, I need help with database design.'],
            ['role' => 'assistant', 'content' => 'I can help with that. What type of database?'],
            ['role' => 'user', 'content' => 'MySQL for an e-commerce system.'],
            ['role' => 'assistant', 'content' => 'Great. Let\'s start with the main entities.'],
            ['role' => 'user', 'content' => 'I need tables for products, orders, and customers.'],
            ['role' => 'assistant', 'content' => 'Here\'s a schema design...'],
            ['role' => 'user', 'content' => 'Should I use foreign keys?'],
            ['role' => 'assistant', 'content' => 'Yes, for referential integrity.']
        ];
        
        foreach ($turns as $turn) {
            $response = $this->makeRequest('POST', '/messages', [
                'conversation_uuid' => $conversationUuid,
                'role' => $turn['role'],
                'content' => $turn['content']
            ]);
            
            $this->assertEquals(201, $response['status']);
        }
        
        // Step 11: Retrieve full conversation
        $historyResponse = $this->makeRequest('GET', "/conversations/{$conversationUuid}/messages");
        
        $this->assertEquals(200, $historyResponse['status']);
        $this->assertCount(8, $historyResponse['body']['messages']);
        
        // Step 12: Verify messages are in correct order
        $messages = $historyResponse['body']['messages'];
        $this->assertEquals('Hello, I need help with database design.', $messages[0]['content']);
        $this->assertEquals('Yes, for referential integrity.', $messages[7]['content']);
        
        // Step 13: Verify conversation metadata updated
        $conversationDetails = DB::query('SELECT * FROM conversations WHERE uuid = ?', [$conversationUuid]);
        $this->assertEquals(8, $conversationDetails[0]['message_count']);
    }
}
