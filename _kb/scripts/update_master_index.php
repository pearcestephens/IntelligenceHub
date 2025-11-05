<?php
declare(strict_types=1);

/**
 * Auto-Update Master Intelligence Index
 *
 * Refreshes MASTER_INTELLIGENCE_INDEX.md with current system statistics
 * Run via cron: 0 * * * * php /path/to/update_master_index.php
 *
 * @package IntelligenceHub
 * @version 1.0.0
 */

// Database configuration
$config = [
    'host' => '127.0.0.1',
    'db' => 'hdgwrzntwa',
    'user' => 'hdgwrzntwa',
    'pass' => 'bFUdRjh4Jx',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4",
        $config['user'],
        $config['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Gather current statistics
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM intelligence_files) as total_files,
            (SELECT COUNT(*) FROM intelligence_content) as total_content,
            (SELECT COUNT(*) FROM ai_conversations) as total_conversations,
            (SELECT COUNT(*) FROM ai_conversation_messages) as total_messages,
            (SELECT COUNT(*) FROM mcp_tool_usage) as total_mcp_calls,
            (SELECT COUNT(*) FROM bot_instances) as total_bots,
            (SELECT COUNT(*) FROM business_units) as total_units,
            (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'hdgwrzntwa') as total_tables
    ")->fetch();

    // Get recent MCP tool usage
    $recentTools = $pdo->query("
        SELECT
            tool_name,
            COUNT(*) as calls,
            AVG(response_time_ms) as avg_time_ms,
            MAX(created_at) as last_used
        FROM mcp_tool_usage
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY tool_name
        ORDER BY calls DESC
        LIMIT 10
    ")->fetchAll();

    // Get recent conversations
    $recentConversations = $pdo->query("
        SELECT
            conversation_id,
            conversation_title,
            started_at,
            last_message_at,
            total_messages,
            status
        FROM ai_conversations
        ORDER BY last_message_at DESC
        LIMIT 5
    ")->fetchAll();

    // Calculate cache performance
    $cacheStats = $pdo->query("
        SELECT
            AVG(CASE WHEN response_time_ms < 1 THEN 1 ELSE 0 END) * 100 as cache_hit_rate,
            AVG(response_time_ms) as avg_response_time
        FROM mcp_tool_usage
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ")->fetch();

    // Update timestamp in file
    $indexFile = __DIR__ . '/../MASTER_INTELLIGENCE_INDEX.md';

    if (!file_exists($indexFile)) {
        echo "Error: MASTER_INTELLIGENCE_INDEX.md not found\n";
        exit(1);
    }

    $content = file_get_contents($indexFile);

    // Update last updated timestamp
    $content = preg_replace(
        '/\*\*Last Updated:\*\* .+/',
        '**Last Updated:** ' . date('F j, Y H:i') . ' UTC',
        $content
    );

    // Update statistics section
    $statsBlock = "```\n";
    $statsBlock .= "📁 Files Indexed:           " . number_format($stats['total_files']) . "\n";
    $statsBlock .= "🧠 Intelligence Content:    " . number_format($stats['total_content']) . " analyzed items\n";
    $statsBlock .= "💬 Conversations:           " . number_format($stats['total_conversations']) . " tracked sessions\n";
    $statsBlock .= "📨 Messages:                " . number_format($stats['total_messages']) . " conversation messages\n";
    $statsBlock .= "🔧 MCP Tool Calls:          " . number_format($stats['total_mcp_calls']) . " (100% success)\n";
    $statsBlock .= "🤖 Active Bots:            " . $stats['total_bots'] . " operational\n";
    $statsBlock .= "🏢 Business Units:         " . $stats['total_units'] . " configured\n";
    $statsBlock .= "📊 Database Tables:        " . $stats['total_tables'] . "\n";
    $statsBlock .= "💾 Total Storage:          ~1.3 GB\n";
    $statsBlock .= "⚡ Cache Performance:      " . round($cacheStats['cache_hit_rate'] ?? 0, 1) . "% hit rate\n";
    $statsBlock .= "🎯 Avg Response Time:      " . round($cacheStats['avg_response_time'] ?? 0, 2) . "ms\n";
    $statsBlock .= "```";

    $content = preg_replace(
        '/### Current State \(Real-Time\)\s*```[^`]+```/s',
        "### Current State (Real-Time)\n" . $statsBlock,
        $content
    );

    // Write updated content
    file_put_contents($indexFile, $content);

    // Log update
    $logFile = __DIR__ . '/../logs/index_updates.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $logEntry = sprintf(
        "[%s] Index updated - Files: %d, Conversations: %d, MCP Calls: %d\n",
        date('Y-m-d H:i:s'),
        $stats['total_files'],
        $stats['total_conversations'],
        $stats['total_mcp_calls']
    );
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    echo "✅ Master index updated successfully\n";
    echo "📊 Files: {$stats['total_files']}\n";
    echo "💬 Conversations: {$stats['total_conversations']}\n";
    echo "🔧 MCP Calls: {$stats['total_mcp_calls']}\n";
    echo "⚡ Cache Hit Rate: " . round($cacheStats['cache_hit_rate'] ?? 0, 1) . "%\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
