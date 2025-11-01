#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Development Environment Check
 * Verifies namespace unification and system health
 * 
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

// Set up basic error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "🔍 AI Agent Development Check\n";
echo "============================\n\n";

// Check if we're in the right directory
if (!file_exists('composer.json')) {
    echo "❌ Error: Run this script from the project root directory\n";
    exit(1);
}

// Check Composer autoloader
echo "📦 Checking Composer setup...\n";
if (!file_exists('vendor/autoload.php')) {
    echo "❌ Composer dependencies not installed. Run: composer install\n";
    exit(1);
}

require_once 'vendor/autoload.php';

// Load environment
if (file_exists('.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    echo "✅ Environment file (.env) loaded\n";
} else {
    echo "⚠️  No .env file found\n";
}

echo "✅ Composer autoloader loaded\n";

// Test namespace resolution
echo "\n🏗️  Testing namespace unification...\n";

$classes = [
    'App\Config' => 'Core configuration',
    'App\Logger' => 'Logging system', 
    'App\DB' => 'Database connection',
    'App\Agent' => 'Main AI agent',
    'App\ConversationManager' => 'Conversation management',
    'App\MessageHandler' => 'Message processing',
    'App\OpenAI' => 'OpenAI integration',
    'App\RedisClient' => 'Redis client',
    'App\Tools\ToolRegistry' => 'Tool registry',
    'App\Tools\DatabaseTool' => 'Database tool',
    'App\Memory\ContextCards' => 'Context cards',
    'App\Memory\KnowledgeBase' => 'Knowledge base',
    'App\Util\Validate' => 'Validation utilities',
    'App\Util\Errors' => 'Error handling'
];

$passed = 0;
$total = count($classes);

foreach ($classes as $class => $description) {
    if (class_exists($class)) {
        echo "✅ {$class} - {$description}\n";
        $passed++;
    } else {
        echo "❌ {$class} - {$description}\n";
    }
}

echo "\nNamespace Resolution: {$passed}/{$total} classes loaded successfully\n";

if ($passed !== $total) {
    echo "\n❌ Some classes failed to load. Run: composer dump-autoload\n";
    exit(1);
}

// Test configuration
echo "\n⚙️  Testing configuration...\n";
try {
    // Config::get will auto-initialize, no getInstance() needed
    echo "✅ Config system initialized\n";
    
    $requiredVars = ['MYSQL_HOST', 'MYSQL_DATABASE', 'OPENAI_API_KEY'];
    foreach ($requiredVars as $var) {
        $value = \App\Config::get($var);
        if ($value) {
            echo "✅ {$var} configured\n";
        } else {
            echo "⚠️  {$var} not set (check .env file)\n";
        }
    }
} catch (\Throwable $e) {
    echo "❌ Config error: " . $e->getMessage() . "\n";
}

// Test database connection
echo "\n🗄️  Testing database connection...\n";
try {
    $db = \App\DB::connection();
    if ($db) {
        echo "✅ Database connection established\n";
    }
} catch (\Throwable $e) {
    echo "⚠️  Database connection failed: " . $e->getMessage() . "\n";
}

// Test Redis connection  
echo "\n🔴 Testing Redis connection...\n";
try {
    $redis = \App\RedisClient::connection();
    if ($redis && \App\RedisClient::isHealthy()) {
        echo "✅ Redis connection established\n";
    } else {
        echo "⚠️  Redis ping failed\n";
    }
} catch (\Throwable $e) {
    echo "⚠️  Redis connection failed: " . $e->getMessage() . "\n";
}

// Test tool registry
echo "\n🔧 Testing tool registry...\n";
try {
    \App\Tools\ToolRegistry::initialize();
    $tools = \App\Tools\ToolRegistry::getAll();
    echo "✅ Tool registry initialized with " . count($tools) . " tools\n";
    
    $expectedTools = ['code_tool', 'database_tool', 'http_tool', 'knowledge_tool', 'memory_tool'];
    foreach ($expectedTools as $tool) {
        if (isset($tools[$tool])) {
            echo "✅ {$tool} registered\n";
        } else {
            echo "⚠️  {$tool} not found\n";
        }
    }
} catch (\Throwable $e) {
    echo "❌ Tool registry error: " . $e->getMessage() . "\n";
}

// Test logging
echo "\n📝 Testing logging system...\n";
try {
    \App\Logger::info('Development check completed', ['timestamp' => date('c')]);
    echo "✅ Logger functioning\n";
} catch (\Throwable $e) {
    echo "❌ Logger error: " . $e->getMessage() . "\n";
}

// Summary
echo "\n📋 Development Check Summary\n";
echo "===========================\n";

if ($passed === $total) {
    echo "🎉 All namespace checks passed!\n";
    echo "✅ App\\ namespace unification complete\n";
    echo "✅ Composer autoloading working\n";
    echo "✅ Core classes loading successfully\n";
    echo "\n🚀 Ready for development!\n";
    echo "\nNext steps:\n";
    echo "- Run tests: composer test\n";
    echo "- Check code style: composer cs\n";
    echo "- Run static analysis: composer analyse\n";
    exit(0);
} else {
    echo "❌ Some issues found. Please resolve before continuing.\n";
    echo "💡 Common solutions:\n";
    echo "   - Run: composer dump-autoload\n";
    echo "   - Check .env configuration\n";
    echo "   - Verify database/Redis connections\n";
    exit(1);
}