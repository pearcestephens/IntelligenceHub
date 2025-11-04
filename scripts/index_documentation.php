#!/usr/bin/env php
<?php
/**
 * Documentation Database Indexer
 *
 * Inserts all master documentation into the intelligence hub database
 * for MCP semantic search and bot preloading
 *
 * Usage: php index_documentation.php
 */

declare(strict_types=1);

// Database configuration
$dbConfig = [
    'host' => '127.0.0.1',
    'database' => 'jcepnzzkmj',
    'username' => 'jcepnzzkmj',
    'password' => 'wprKh9Jq63'
];

// Base path
$basePath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html';

// Documents to index
$documents = [
    // Root directory docs
    [
        'path' => '/MASTER_SYSTEM_GUIDE.md',
        'title' => 'Master System Guide',
        'category' => 'System Documentation',
        'priority' => 100,
        'tags' => ['master', 'guide', 'reference', 'complete']
    ],
    [
        'path' => '/FRONTEND_TOOLS_BREAKDOWN.md',
        'title' => 'Frontend Tools Complete Breakdown',
        'category' => 'Frontend Automation',
        'priority' => 90,
        'tags' => ['frontend', 'tools', 'playwright', 'testing']
    ],
    [
        'path' => '/PRODUCTION_READY.md',
        'title' => 'Production Readiness Status',
        'category' => 'System Status',
        'priority' => 85,
        'tags' => ['production', 'status', 'deployment']
    ],
    [
        'path' => '/README.md',
        'title' => 'System README',
        'category' => 'System Documentation',
        'priority' => 80,
        'tags' => ['readme', 'overview', 'introduction']
    ],

    // Frontend tools docs
    [
        'path' => '/frontend-tools/INTEGRATION_MASTER_PLAN.md',
        'title' => 'Frontend Integration Master Plan',
        'category' => 'AI Agent Integration',
        'priority' => 95,
        'tags' => ['integration', 'ai-agent', 'workflow', 'approval']
    ],
    [
        'path' => '/frontend-tools/AUTOMATION_ROADMAP.md',
        'title' => 'Automation Roadmap',
        'category' => 'Frontend Automation',
        'priority' => 85,
        'tags' => ['automation', 'roadmap', 'features', 'planning']
    ],
    [
        'path' => '/frontend-tools/ARCHITECTURE_DEEP_DIVE.md',
        'title' => 'Architecture Deep Dive',
        'category' => 'Architecture',
        'priority' => 90,
        'tags' => ['architecture', 'design', 'technical', 'deep-dive']
    ],
    [
        'path' => '/frontend-tools/AUDIT_GALLERY_SYSTEM.md',
        'title' => 'Audit Gallery System',
        'category' => 'Frontend Automation',
        'priority' => 80,
        'tags' => ['gallery', 'screenshots', 'audits', 'uploads']
    ],

    // AI Agent docs
    [
        'path' => '/ai-agent/FRONTEND_INTEGRATION_SETUP.md',
        'title' => 'Frontend Integration Setup Guide',
        'category' => 'AI Agent Setup',
        'priority' => 95,
        'tags' => ['setup', 'installation', 'configuration', 'guide']
    ],

    // VS Code docs
    [
        'path' => '/.vscode/BOTS_GUIDE.md',
        'title' => 'Bots Guide - Tools and Settings',
        'category' => 'Bot Instructions',
        'priority' => 100,
        'tags' => ['bots', 'mcp', 'tools', 'settings', 'preload']
    ],
    [
        'path' => '/.vscode/MASTER_SYSTEM_GUIDE.md',
        'title' => 'Master System Guide (VS Code)',
        'category' => 'System Documentation',
        'priority' => 100,
        'tags' => ['master', 'guide', 'vscode']
    ],

    // GitHub docs
    [
        'path' => '/.github/copilot-instructions.md',
        'title' => 'GitHub Copilot Instructions',
        'category' => 'Bot Instructions',
        'priority' => 90,
        'tags' => ['github', 'copilot', 'instructions']
    ],
    [
        'path' => '/.github/MASTER_SYSTEM_GUIDE.md',
        'title' => 'Master System Guide (GitHub)',
        'category' => 'System Documentation',
        'priority' => 100,
        'tags' => ['master', 'guide', 'github']
    ],

    // Knowledge base docs
    [
        'path' => '/_kb/MASTER_SYSTEM_GUIDE.md',
        'title' => 'Master System Guide (KB)',
        'category' => 'System Documentation',
        'priority' => 100,
        'tags' => ['master', 'guide', 'knowledge-base']
    ],
    [
        'path' => '/_kb/README.md',
        'title' => 'Knowledge Base README',
        'category' => 'Knowledge Base',
        'priority' => 75,
        'tags' => ['kb', 'readme', 'documentation']
    ]
];

echo "🚀 Documentation Database Indexer\n";
echo "==================================\n\n";

try {
    // Connect to database
    echo "📡 Connecting to database...\n";
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connected\n\n";

    // Check if intelligence_documents table exists
    echo "🔍 Checking database schema...\n";
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'intelligence_documents'");
    if ($tableCheck->rowCount() === 0) {
        echo "⚠️  Table 'intelligence_documents' not found\n";
        echo "Creating table...\n";

        $createTable = <<<SQL
CREATE TABLE intelligence_documents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    doc_id VARCHAR(100) UNIQUE,
    title VARCHAR(500),
    file_path VARCHAR(500),
    content LONGTEXT,
    category VARCHAR(100),
    tags JSON,
    priority INT DEFAULT 50,
    file_size INT,
    last_modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    indexed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    search_vector TEXT,
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_last_modified (last_modified),
    FULLTEXT idx_content (content),
    FULLTEXT idx_search_vector (search_vector)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $pdo->exec($createTable);
        echo "✅ Table created\n\n";
    } else {
        echo "✅ Table exists\n\n";
    }

    // Prepare insert statement
    $insertStmt = $pdo->prepare("
        INSERT INTO intelligence_documents (
            doc_id, title, file_path, content, category, tags, priority, file_size, search_vector
        ) VALUES (
            :doc_id, :title, :file_path, :content, :category, :tags, :priority, :file_size, :search_vector
        ) ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            content = VALUES(content),
            category = VALUES(category),
            tags = VALUES(tags),
            priority = VALUES(priority),
            file_size = VALUES(file_size),
            search_vector = VALUES(search_vector),
            last_modified = CURRENT_TIMESTAMP
    ");

    // Process each document
    $successCount = 0;
    $errorCount = 0;

    echo "📚 Processing documents...\n";
    echo str_repeat("─", 80) . "\n\n";

    foreach ($documents as $doc) {
        $fullPath = $basePath . $doc['path'];

        echo "Processing: {$doc['path']}\n";

        // Check if file exists
        if (!file_exists($fullPath)) {
            echo "  ❌ File not found: {$fullPath}\n\n";
            $errorCount++;
            continue;
        }

        // Read file content
        $content = file_get_contents($fullPath);
        $fileSize = filesize($fullPath);

        // Generate doc_id
        $docId = 'doc_' . md5($doc['path']);

        // Create search vector (title + content + tags for better search)
        $searchVector = implode(' ', [
            $doc['title'],
            $content,
            implode(' ', $doc['tags'])
        ]);

        // Insert into database
        try {
            $insertStmt->execute([
                'doc_id' => $docId,
                'title' => $doc['title'],
                'file_path' => $doc['path'],
                'content' => $content,
                'category' => $doc['category'],
                'tags' => json_encode($doc['tags']),
                'priority' => $doc['priority'],
                'file_size' => $fileSize,
                'search_vector' => $searchVector
            ]);

            echo "  ✅ Indexed successfully\n";
            echo "     Size: " . number_format($fileSize / 1024, 2) . " KB\n";
            echo "     Priority: {$doc['priority']}\n";
            echo "     Tags: " . implode(', ', $doc['tags']) . "\n\n";

            $successCount++;

        } catch (PDOException $e) {
            echo "  ❌ Failed to index: {$e->getMessage()}\n\n";
            $errorCount++;
        }
    }

    echo str_repeat("─", 80) . "\n";
    echo "📊 Summary\n";
    echo str_repeat("─", 80) . "\n";
    echo "✅ Successfully indexed: {$successCount}\n";
    echo "❌ Errors: {$errorCount}\n";
    echo "📝 Total documents: " . count($documents) . "\n\n";

    // Verify with some queries
    echo "🔍 Verification Queries\n";
    echo str_repeat("─", 80) . "\n\n";

    // Count by category
    $categories = $pdo->query("
        SELECT category, COUNT(*) as count
        FROM intelligence_documents
        GROUP BY category
        ORDER BY count DESC
    ");

    echo "Documents by Category:\n";
    foreach ($categories as $row) {
        echo "  • {$row['category']}: {$row['count']}\n";
    }
    echo "\n";

    // High priority docs
    $highPriority = $pdo->query("
        SELECT title, priority
        FROM intelligence_documents
        WHERE priority >= 90
        ORDER BY priority DESC, title
    ");

    echo "High Priority Documents (>= 90):\n";
    foreach ($highPriority as $row) {
        echo "  • [{$row['priority']}] {$row['title']}\n";
    }
    echo "\n";

    // Search test
    echo "Testing Full-Text Search:\n";
    $searchTest = $pdo->prepare("
        SELECT title, MATCH(content) AGAINST(:query) as relevance
        FROM intelligence_documents
        WHERE MATCH(content) AGAINST(:query)
        ORDER BY relevance DESC
        LIMIT 5
    ");

    $testQueries = [
        'frontend tools',
        'workflow approval',
        'database migration'
    ];

    foreach ($testQueries as $query) {
        echo "\n  Query: \"{$query}\"\n";
        $searchTest->execute(['query' => $query]);
        $results = $searchTest->fetchAll();

        if (count($results) > 0) {
            foreach ($results as $result) {
                echo "    → {$result['title']} (relevance: " .
                     number_format($result['relevance'], 2) . ")\n";
            }
        } else {
            echo "    (no results)\n";
        }
    }

    echo "\n" . str_repeat("─", 80) . "\n";
    echo "✅ Documentation indexing complete!\n\n";

    echo "📖 Usage Examples:\n\n";
    echo "// Search documents via MCP\n";
    echo "semantic_search({query: 'frontend automation', limit: 10})\n\n";

    echo "// Get high priority docs\n";
    echo "SELECT * FROM intelligence_documents WHERE priority >= 90 ORDER BY priority DESC;\n\n";

    echo "// Search by category\n";
    echo "SELECT * FROM intelligence_documents WHERE category = 'Frontend Automation';\n\n";

    echo "// Full-text search\n";
    echo "SELECT title FROM intelligence_documents WHERE MATCH(content) AGAINST('workflow');\n\n";

} catch (PDOException $e) {
    echo "❌ Database error: {$e->getMessage()}\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}

exit(0);
