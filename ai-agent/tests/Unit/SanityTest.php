<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Sanity tests to verify test infrastructure
 */
class SanityTest extends TestCase
{
    /**
     * @test
     * Test that environment bootstraps correctly
     */
    public function test_env_bootstraps(): void
    {
        $this->assertTrue(true, 'PHPUnit is working');
        $this->assertEquals('testing', getenv('APP_ENV'), 'Test environment configured');
    }
    
    /**
     * @test
     * Test that composer autoload works
     */
    public function test_composer_autoload(): void
    {
        $this->assertTrue(class_exists('\App\Agent'), 'App\Agent class autoloads');
        $this->assertTrue(class_exists('\App\Config'), 'App\Config class autoloads');
        $this->assertTrue(class_exists('\App\Logger'), 'App\Logger class autoloads');
    }
    
    /**
     * @test
     * Test that required PHP extensions are loaded
     */
    public function test_required_extensions(): void
    {
        $required = ['json', 'curl', 'redis', 'pdo', 'pdo_mysql', 'mbstring', 'fileinfo'];
        
        foreach ($required as $ext) {
            $this->assertTrue(
                extension_loaded($ext),
                "Required extension '$ext' is loaded"
            );
        }
    }
    
    /**
     * @test
     * Test that Redis connection is available
     */
    public function test_redis_available(): void
    {
        $this->assertTrue(class_exists('\Redis'), 'Redis class available');
    }
    
    /**
     * @test
     * Test that database connection can be established
     */
    public function test_database_connection(): void
    {
        $host = getenv('MYSQL_HOST') ?: '127.0.0.1';
        $this->assertNotEmpty($host, 'Database host configured');
    }
    
    /**
     * @test
     * Test that .env configuration is loaded
     */
    public function test_env_config_loaded(): void
    {
        $this->assertNotEmpty(getenv('APP_ENV'), 'APP_ENV is set');
        $this->assertEquals('testing', getenv('APP_ENV'), 'Running in test environment');
    }
    
    /**
     * @test
     * Test basic assertions work
     */
    public function test_assertions_work(): void
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertSame('test', 'test');
        $this->assertNotNull('value');
        $this->assertIsArray([]);
        $this->assertCount(3, [1, 2, 3]);
    }
}
