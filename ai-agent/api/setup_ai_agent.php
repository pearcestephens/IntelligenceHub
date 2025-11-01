#!/usr/bin/env php
<?php
/**
 * AI Chat Setup - Quick Configuration
 * 
 * Sets up the AI agent in the database and validates the system
 * 
 * Usage: php setup_ai_agent.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║              AI CHAT SYSTEM - QUICK SETUP                 ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";

// Database connection
$mysqli = new mysqli('127.0.0.1', 'jcepnzzkmj', 'wprKh9Jq63', 'jcepnzzkmj');

if ($mysqli->connect_error) {
    die("❌ Database connection failed: {$mysqli->connect_error}\n");
}

echo "✓ Database connected\n";

// Check if agent exists
$result = $mysqli->query("SELECT * FROM ai_kb_config WHERE is_active = 1 LIMIT 1");

if ($result->num_rows > 0) {
    $agent = $result->fetch_assoc();
    echo "✓ Active agent already configured: {$agent['agent_name']}\n";
    echo "\n";
    echo "Agent Details:\n";
    echo "  - ID: {$agent['id']}\n";
    echo "  - Name: {$agent['agent_name']}\n";
    echo "  - Agent ID: {$agent['agent_id']}\n";
    echo "  - API URL: {$agent['api_url']}\n";
    echo "  - Active: " . ($agent['is_active'] ? 'Yes' : 'No') . "\n";
    echo "  - Created: {$agent['created_at']}\n";
} else {
    echo "⚠ No active agent found. Creating one...\n";
    
    // Create default agent
    $stmt = $mysqli->prepare(
        "INSERT INTO ai_kb_config 
         (agent_name, api_url, agent_id, is_active, created_at) 
         VALUES (?, ?, ?, 1, NOW())"
    );
    
    $agentName = 'CIS AI Assistant';
    $apiUrl = 'https://api.openai.com/v1/chat/completions';
    $agentId = 'cis_agent_' . uniqid();
    
    $stmt->bind_param('sss', $agentName, $apiUrl, $agentId);
    
    if ($stmt->execute()) {
        echo "✓ Agent created successfully!\n";
        echo "\n";
        echo "Agent Details:\n";
        echo "  - Name: {$agentName}\n";
        echo "  - Agent ID: {$agentId}\n";
        echo "  - API URL: {$apiUrl}\n";
    } else {
        echo "❌ Failed to create agent: {$stmt->error}\n";
    }
    
    $stmt->close();
}

echo "\n";
echo str_repeat('-', 60) . "\n";
echo "Checking Environment Configuration\n";
echo str_repeat('-', 60) . "\n";

// Check OpenAI API key
$apiKey = getenv('OPENAI_API_KEY');
if (!empty($apiKey)) {
    echo "✓ OPENAI_API_KEY is set: " . substr($apiKey, 0, 10) . "...\n";
} else {
    echo "⚠ OPENAI_API_KEY not set\n";
    echo "\n";
    echo "To set it, run:\n";
    echo "  export OPENAI_API_KEY='sk-proj-YOUR-KEY-HERE'\n";
    echo "\n";
    echo "Or add to /etc/environment:\n";
    echo "  OPENAI_API_KEY='sk-proj-YOUR-KEY-HERE'\n";
}

echo "\n";
echo str_repeat('-', 60) . "\n";
echo "Checking Required Tables\n";
echo str_repeat('-', 60) . "\n";

$requiredTables = [
    'ai_kb_config',
    'ai_kb_queries',
    'ai_kb_conversations',
    'ai_kb_knowledge_items',
    'ai_kb_errors',
    'ai_kb_sync_history',
    'ai_kb_performance_metrics'
];

$allExist = true;
foreach ($requiredTables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '{$table}'");
    if ($result->num_rows > 0) {
        echo "✓ {$table}\n";
    } else {
        echo "❌ {$table} - MISSING\n";
        $allExist = false;
    }
}

if (!$allExist) {
    echo "\n";
    echo "⚠ Some tables are missing. Run the migration script first:\n";
    echo "  php create-tables.php\n";
}

echo "\n";
echo str_repeat('-', 60) . "\n";
echo "Checking Knowledge Base\n";
echo str_repeat('-', 60) . "\n";

$result = $mysqli->query("SELECT COUNT(*) as cnt FROM ai_kb_knowledge_items");
$row = $result->fetch_assoc();

if ($row['cnt'] > 0) {
    echo "✓ Knowledge base populated: {$row['cnt']} items\n";
} else {
    echo "⚠ Knowledge base is empty\n";
    echo "\n";
    echo "To populate, run:\n";
    echo "  php _kb/tools/kb-organizer.php\n";
    echo "  php _kb/tools/ai-agent-bridge.php --sync\n";
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                     SETUP SUMMARY                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";

if (!empty($apiKey) && $allExist) {
    echo "✅ System is READY for production!\n";
    echo "\n";
    echo "Next steps:\n";
    echo "1. Test health check:\n";
    echo "   curl https://staff.vapeshed.co.nz/assets/services/neuro/neuro_/ai-agent/api/health.php\n";
    echo "\n";
    echo "2. Run test suite:\n";
    echo "   php test_chat_ai.php\n";
    echo "\n";
    echo "3. Test chat API:\n";
    echo "   curl -X POST https://staff.vapeshed.co.nz/assets/services/neuro/neuro_/ai-agent/api/chat-v2.php \\\n";
    echo "     -H 'Content-Type: application/json' \\\n";
    echo "     -d '{\"message\": \"Hello!\"}'\n";
} else {
    echo "⚠ System needs configuration\n";
    echo "\n";
    echo "Required actions:\n";
    if (empty($apiKey)) {
        echo "- Set OPENAI_API_KEY environment variable\n";
    }
    if (!$allExist) {
        echo "- Run database migration: php create-tables.php\n";
    }
    if ($row['cnt'] == 0) {
        echo "- Populate knowledge base: php _kb/tools/kb-organizer.php\n";
    }
}

echo "\n";

$mysqli->close();
