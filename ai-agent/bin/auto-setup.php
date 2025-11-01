#!/usr/bin/env php
<?php
/**
 * Automated Test Environment Setup
 * Creates test tables with test_ prefix in existing database
 * No user interaction required
 */

declare(strict_types=1);

echo "\n";
echo "🚀 Automated Test Environment Setup\n";
echo "=====================================\n\n";

$config = [
    'host' => '127.0.0.1',
    'user' => 'jcepnzzkmj',
    'pass' => 'wprKh9Jq63',
    'db' => 'jcepnzzkmj'
];

echo "📊 Database: {$config['db']}@{$config['host']}\n";
echo "🏷️  Prefix: test_\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Connected to database\n\n";
    
    // Step 1: Drop existing test tables
    echo "🧹 Dropping old test tables...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['test_conversations', 'test_messages', 'test_context_cards', 'test_tool_calls',
               'test_knowledge_base', 'test_api_keys', 'test_idempotency_store', 'test_rate_limits'];
    
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS {$table}");
        echo "  ✓ Dropped {$table}\n";
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "\n";
    
    // Step 2: Create test tables
    echo "📦 Creating test tables...\n";
    
    // Test Conversations
    $pdo->exec("
        CREATE TABLE test_conversations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid CHAR(36) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL DEFAULT 'Untitled',
            model VARCHAR(64) NULL DEFAULT 'gpt-4o',
            system_message TEXT NULL,
            version INT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_uuid (uuid),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_conversations\n";
    
    // Test Messages (with FK on both conversation_id and conversation_uuid)
    $pdo->exec("
        CREATE TABLE test_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            message_id CHAR(36) UNIQUE NULL,
            conversation_id INT NULL,
            conversation_uuid CHAR(36) NULL,
            role ENUM('system','user','assistant','tool') NOT NULL,
            content MEDIUMTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_conv_id (conversation_id),
            INDEX idx_conv_uuid (conversation_uuid),
            CONSTRAINT fk_test_messages_conv FOREIGN KEY (conversation_id) 
                REFERENCES test_conversations(id) ON DELETE CASCADE,
            CONSTRAINT fk_test_messages_uuid FOREIGN KEY (conversation_uuid)
                REFERENCES test_conversations(uuid) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_messages\n";
    
    // Test Context Cards
    $pdo->exec("
        CREATE TABLE test_context_cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            uuid CHAR(36) UNIQUE NOT NULL,
            conversation_id INT NULL,
            content TEXT NOT NULL,
            embedding LONGBLOB NULL,
            expires_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_conv_id (conversation_id),
            CONSTRAINT fk_test_context_conv FOREIGN KEY (conversation_id)
                REFERENCES test_conversations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_context_cards\n";
    
    // Test Tool Calls
    $pdo->exec("
        CREATE TABLE test_tool_calls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tool_call_id CHAR(36) UNIQUE NOT NULL,
            message_id CHAR(36) NULL,
            conversation_id INT NULL,
            tool_name VARCHAR(128) NOT NULL,
            arguments JSON NOT NULL,
            result JSON NULL,
            status ENUM('pending','running','ok','error','timeout') NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_conv_id (conversation_id),
            INDEX idx_tool_status (tool_name, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_tool_calls\n";
    
    // Test Knowledge Base
    $pdo->exec("
        CREATE TABLE test_knowledge_base (
            id INT AUTO_INCREMENT PRIMARY KEY,
            doc_id CHAR(36) UNIQUE NOT NULL,
            source VARCHAR(64) NOT NULL,
            uri TEXT NOT NULL,
            content MEDIUMTEXT NOT NULL,
            embedding LONGBLOB NULL,
            indexed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_doc_id (doc_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_knowledge_base\n";
    
    // Test API Keys
    $pdo->exec("
        CREATE TABLE test_api_keys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            api_key VARCHAR(64) UNIQUE NOT NULL,
            name VARCHAR(255) NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            rate_limit_per_minute INT NULL DEFAULT 100,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_api_key (api_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_api_keys\n";
    
    // Test Idempotency Store
    $pdo->exec("
        CREATE TABLE test_idempotency_store (
            id INT AUTO_INCREMENT PRIMARY KEY,
            idempotency_key VARCHAR(255) UNIQUE NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            response JSON NOT NULL,
            status_code INT NOT NULL,
            expires_at DATETIME NOT NULL,
            INDEX idx_key (idempotency_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_idempotency_store\n";
    
    // Test Rate Limits
    $pdo->exec("
        CREATE TABLE test_rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255) NOT NULL,
            endpoint VARCHAR(255) NOT NULL,
            request_count INT NOT NULL DEFAULT 1,
            window_start DATETIME NOT NULL,
            window_end DATETIME NOT NULL,
            UNIQUE KEY idx_window (identifier, endpoint, window_start)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  ✓ test_rate_limits\n";
    
    echo "\n";
    echo "✅ All test tables created successfully\n\n";
    
    // Step 3: Verify tables exist
    echo "🔍 Verifying tables...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'test_%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "  Found " . count($tables) . " test tables:\n";
    foreach ($tables as $table) {
        echo "    • {$table}\n";
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════════════╗\n";
    echo "║         Test Environment Ready! ✓                  ║\n";
    echo "╚════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "📊 Database: {$config['db']}\n";
    echo "🏷️  Test tables: 8 tables with test_ prefix\n";
    echo "🔒 Production: Untouched (conversations, messages)\n";
    echo "\n";
    echo "✅ Setup complete! No errors.\n\n";
    
} catch (PDOException $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
