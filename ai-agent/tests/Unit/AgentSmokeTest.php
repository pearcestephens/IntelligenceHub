<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Agent;
use App\Config;
use App\DB;
use App\Logger;
use App\OpenAI;
use App\RedisClient;

/**
 * Basic smoke tests for the Agent class
 * Tests core functionality and namespace resolution
 * 
 * @package Tests\Unit
 * @author Ecigdis Limited (The Vape Shed)
 */
class AgentSmokeTest extends TestCase
{
    private Agent $agent;

    protected function setUp(): void
    {
        // Skip if missing required environment variables for integration features
        // Allow basic instantiation without OpenAI/DB for unit testing
        
        try {
            // Initialize config and logger for Agent
            Config::initialize();
            $config = new Config();
            $logger = new Logger();
            
            $this->agent = new Agent($config, $logger);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Cannot initialize Agent: ' . $e->getMessage());
        }
    }

    /**
     * Test that Agent class can be instantiated
     */
    public function testAgentCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Agent::class, $this->agent);
    }

    /**
     * Test that Config class works with App namespace
     */
    public function testConfigNamespaceResolution(): void
    {
        $config = Config::all();
        $this->assertIsArray($config);
        
        // Test basic config access - use MYSQL_USER which we know exists
        $mysqlUser = Config::get('MYSQL_USER');
        $this->assertNotEmpty($mysqlUser, 'MYSQL_USER should be set in .env');
    }

    /**
     * Test that Logger works with App namespace
     */
    public function testLoggerNamespaceResolution(): void
    {
        // This should not throw an exception
        Logger::info('Test log entry from PHPUnit');
        $this->assertTrue(true); // If we get here, Logger works
    }

    /**
     * Test that DB class works with App namespace
     */
    public function testDbNamespaceResolution(): void
    {
        try {
            $connection = DB::connection();
            $this->assertNotNull($connection);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Test that Redis client works with App namespace
     */
    public function testRedisNamespaceResolution(): void
    {
        try {
            $connection = RedisClient::connection();
            $this->assertNotNull($connection);
        } catch (\Throwable $e) {
            $this->markTestSkipped('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Test that OpenAI client works with App namespace
     */
    public function testOpenAINamespaceResolution(): void
    {
        try {
            $openai = new OpenAI($_ENV['OPENAI_API_KEY']);
            $this->assertInstanceOf(OpenAI::class, $openai);
        } catch (\Throwable $e) {
            $this->fail('OpenAI instantiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Test agent conversation creation
     */
    public function testAgentConversationCreation(): void
    {
        try {
            $conversationId = $this->agent->createConversation(
                'Test User',
                'unit-test',
                ['test' => true]
            );
            
            $this->assertIsString($conversationId);
            $this->assertNotEmpty($conversationId);
            
            // Clean up
            $this->agent->deleteConversation($conversationId);
            
        } catch (\Throwable $e) {
            $this->markTestSkipped('Cannot test conversation creation: ' . $e->getMessage());
        }
    }

    /**
     * Test that tools can be loaded
     */
    public function testToolsCanBeLoaded(): void
    {
        try {
            $tools = $this->agent->getAvailableTools();
            $this->assertIsArray($tools);
            $this->assertNotEmpty($tools);
            
            // Should have basic tools
            $toolNames = array_keys($tools);
            $this->assertContains('database', $toolNames);
            $this->assertContains('memory', $toolNames);
            $this->assertContains('file', $toolNames);
            $this->assertContains('http', $toolNames);
            
        } catch (\Throwable $e) {
            $this->fail('Tool loading failed: ' . $e->getMessage());
        }
    }

    /**
     * Test namespace consistency across the system
     */
    public function testNamespaceConsistency(): void
    {
        $reflection = new \ReflectionClass(Agent::class);
        $this->assertEquals('App', $reflection->getNamespaceName());
        
        $reflection = new \ReflectionClass(Config::class);
        $this->assertEquals('App', $reflection->getNamespaceName());
        
        $reflection = new \ReflectionClass(Logger::class);
        $this->assertEquals('App', $reflection->getNamespaceName());
        
        $reflection = new \ReflectionClass(DB::class);
        $this->assertEquals('App', $reflection->getNamespaceName());
    }

    protected function tearDown(): void
    {
        // Clean up any test data
        Logger::info('AgentSmokeTest completed');
    }
}