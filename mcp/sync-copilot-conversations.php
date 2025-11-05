#!/usr/bin/env php
<?php
/**
 * GitHub Copilot Conversation Sync Service
 *
 * Monitors local Copilot conversation logs and syncs them to database
 *
 * Copilot Log Locations:
 * - Windows: %APPDATA%\Code\User\globalStorage\github.copilot-chat\
 * - Mac: ~/Library/Application Support/Code/User/globalStorage/github.copilot-chat/
 * - Linux: ~/.config/Code/User/globalStorage/github.copilot-chat/
 *
 * Usage:
 *   php sync-copilot-conversations.php --watch    (continuous monitoring)
 *   php sync-copilot-conversations.php --once     (one-time sync)
 *   php sync-copilot-conversations.php --local-path=/path/to/logs
 *
 * @package IntelligenceHub\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

// Configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');
define('PROJECT_ID', 2);
define('BUSINESS_UNIT_ID', 2);
define('SYNC_INTERVAL', 60); // seconds

// Parse command line arguments
$options = getopt('', ['watch', 'once', 'local-path:', 'help']);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

$watchMode = isset($options['watch']);
$localPath = $options['local-path'] ?? null;

// Determine Copilot log path
$copilotPath = $localPath ?? detectCopilotPath();

if (!$copilotPath) {
    echo "❌ Could not detect Copilot logs path.\n";
    echo "   Please specify manually with: --local-path=/path/to/logs\n";
    echo "   Or mount Windows path: /mnt/c/Users/YourUser/AppData/Roaming/Code/User/globalStorage/github.copilot-chat/\n";
    exit(1);
}

echo "🔍 Copilot logs path: {$copilotPath}\n";

// Check if path is accessible
if (!is_dir($copilotPath) && !is_readable($copilotPath)) {
    echo "⚠️  Path not accessible: {$copilotPath}\n";
    echo "   Attempting to mount Windows drive...\n";

    // Try to access via WSL mount
    $windowsPath = '/mnt/c/Users/' . get_current_user() . '/AppData/Roaming/Code/User/globalStorage/github.copilot-chat/';
    if (is_dir($windowsPath)) {
        $copilotPath = $windowsPath;
        echo "✅ Using Windows mount: {$copilotPath}\n";
    } else {
        echo "❌ Could not access Copilot logs.\n";
        exit(1);
    }
}

// Initialize database connection
$db = connectDatabase();

echo "✅ Database connected\n";
echo "📊 Project ID: " . PROJECT_ID . "\n";
echo "🏢 Business Unit ID: " . BUSINESS_UNIT_ID . "\n";
echo "\n";

if ($watchMode) {
    echo "👀 Starting continuous monitoring (Ctrl+C to stop)...\n";
    echo "🔄 Sync interval: " . SYNC_INTERVAL . " seconds\n";
    echo "\n";

    $lastSync = 0;
    while (true) {
        $now = time();
        if ($now - $lastSync >= SYNC_INTERVAL) {
            syncConversations($db, $copilotPath);
            $lastSync = $now;
            echo "⏰ Next sync in " . SYNC_INTERVAL . " seconds...\n\n";
        }
        sleep(1);
    }
} else {
    echo "🔄 Running one-time sync...\n";
    syncConversations($db, $copilotPath);
    echo "✅ Sync complete!\n";
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function detectCopilotPath(): ?string
{
    $paths = [
        // Linux
        getenv('HOME') . '/.config/Code/User/globalStorage/github.copilot-chat/',
        getenv('HOME') . '/.vscode/extensions/github.copilot-chat-*/conversations/',

        // WSL Windows mount
        '/mnt/c/Users/' . get_current_user() . '/AppData/Roaming/Code/User/globalStorage/github.copilot-chat/',

        // Mac
        getenv('HOME') . '/Library/Application Support/Code/User/globalStorage/github.copilot-chat/',
    ];

    foreach ($paths as $path) {
        if (is_dir($path)) {
            return rtrim($path, '/') . '/';
        }
    }

    return null;
}

function connectDatabase(): PDO
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        echo "❌ Database connection failed: {$e->getMessage()}\n";
        exit(1);
    }
}

function syncConversations(PDO $db, string $copilotPath): void
{
    echo "[" . date('Y-m-d H:i:s') . "] 🔄 Starting sync...\n";

    // Find conversation files
    $conversationFiles = findConversationFiles($copilotPath);

    if (empty($conversationFiles)) {
        echo "⚠️  No conversation files found in: {$copilotPath}\n";
        echo "   This might mean:\n";
        echo "   - Copilot hasn't created conversations yet\n";
        echo "   - Path is incorrect\n";
        echo "   - Files are in a different format\n";
        return;
    }

    echo "📁 Found " . count($conversationFiles) . " conversation files\n";

    $synced = 0;
    $errors = 0;

    foreach ($conversationFiles as $file) {
        try {
            $conversation = parseConversationFile($file);
            if ($conversation) {
                saveConversation($db, $conversation);
                $synced++;
                echo "  ✅ Synced: {$conversation['title']}\n";
            }
        } catch (Exception $e) {
            $errors++;
            echo "  ❌ Error syncing {$file}: {$e->getMessage()}\n";
        }
    }

    echo "✅ Synced: {$synced} | ❌ Errors: {$errors}\n";
}

function findConversationFiles(string $path): array
{
    $files = [];

    // Look for JSON conversation files
    $patterns = [
        $path . '*.json',
        $path . 'conversations/*.json',
        $path . 'sessions/*.json',
        $path . '**/*.json',
    ];

    foreach ($patterns as $pattern) {
        $found = glob($pattern, GLOB_BRACE);
        if ($found) {
            $files = array_merge($files, $found);
        }
    }

    // Also check for SQLite databases (Copilot sometimes uses these)
    $dbFiles = glob($path . '*.db', GLOB_BRACE);
    if ($dbFiles) {
        foreach ($dbFiles as $dbFile) {
            $conversations = extractFromSqlite($dbFile);
            $files = array_merge($files, $conversations);
        }
    }

    return array_unique($files);
}

function parseConversationFile(string $file): ?array
{
    $content = file_get_contents($file);
    if (!$content) {
        return null;
    }

    // Try to parse as JSON
    $data = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null;
    }

    // Extract conversation metadata
    $conversation = [
        'external_id' => $data['id'] ?? basename($file, '.json'),
        'title' => $data['title'] ?? $data['name'] ?? 'Untitled Conversation',
        'messages' => [],
        'started_at' => $data['createdAt'] ?? $data['timestamp'] ?? date('Y-m-d H:i:s', filemtime($file)),
        'last_message_at' => $data['updatedAt'] ?? date('Y-m-d H:i:s', filemtime($file)),
        'metadata' => [],
    ];

    // Extract messages
    if (isset($data['messages']) && is_array($data['messages'])) {
        foreach ($data['messages'] as $msg) {
            $conversation['messages'][] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? $msg['text'] ?? '',
                'timestamp' => $msg['timestamp'] ?? $msg['createdAt'] ?? date('Y-m-d H:i:s'),
                'metadata' => [
                    'model' => $msg['model'] ?? null,
                    'tokens' => $msg['tokens'] ?? null,
                ],
            ];
        }
    }

    // Extract tools used
    if (isset($data['tools']) && is_array($data['tools'])) {
        $conversation['metadata']['tools_used'] = $data['tools'];
    }

    // Extract workspace context
    if (isset($data['workspace'])) {
        $conversation['metadata']['workspace'] = $data['workspace'];
    }

    return $conversation;
}

function extractFromSqlite(string $dbFile): array
{
    // If Copilot uses SQLite, we can extract conversations from there
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Try common table names
        $tables = ['conversations', 'sessions', 'chat_history'];
        $conversations = [];

        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT * FROM {$table}");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $conversations[] = $row;
                }
            } catch (PDOException $e) {
                // Table doesn't exist, continue
            }
        }

        return $conversations;
    } catch (Exception $e) {
        return [];
    }
}

function saveConversation(PDO $db, array $conversation): void
{
    // Check if conversation already exists
    $stmt = $db->prepare("
        SELECT conversation_id, total_messages
        FROM ai_conversations
        WHERE external_conversation_id = ?
    ");
    $stmt->execute([$conversation['external_id']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing conversation
        $conversationId = $existing['conversation_id'];

        // Only update if there are new messages
        if (count($conversation['messages']) > $existing['total_messages']) {
            $stmt = $db->prepare("
                UPDATE ai_conversations
                SET total_messages = ?,
                    last_message_at = ?,
                    updated_at = NOW()
                WHERE conversation_id = ?
            ");
            $stmt->execute([
                count($conversation['messages']),
                $conversation['last_message_at'],
                $conversationId,
            ]);

            // Add new messages
            saveMessages($db, $conversationId, $conversation['messages'], $existing['total_messages']);
        }
    } else {
        // Create new conversation
        $stmt = $db->prepare("
            INSERT INTO ai_conversations (
                project_id,
                business_unit_id,
                conversation_title,
                external_conversation_id,
                total_messages,
                started_at,
                last_message_at,
                status,
                metadata,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())
        ");

        $stmt->execute([
            PROJECT_ID,
            BUSINESS_UNIT_ID,
            $conversation['title'],
            $conversation['external_id'],
            count($conversation['messages']),
            $conversation['started_at'],
            $conversation['last_message_at'],
            json_encode($conversation['metadata'] ?? []),
        ]);

        $conversationId = (int)$db->lastInsertId();

        // Save all messages
        saveMessages($db, $conversationId, $conversation['messages'], 0);
    }
}

function saveMessages(PDO $db, int $conversationId, array $messages, int $startFrom): void
{
    // Only save messages after $startFrom index
    $newMessages = array_slice($messages, $startFrom);

    if (empty($newMessages)) {
        return;
    }

    $stmt = $db->prepare("
        INSERT INTO ai_conversation_messages (
            conversation_id,
            message_role,
            message_content,
            message_metadata,
            created_at
        ) VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($newMessages as $message) {
        $stmt->execute([
            $conversationId,
            $message['role'],
            $message['content'],
            json_encode($message['metadata'] ?? []),
            $message['timestamp'],
        ]);
    }
}

function showHelp(): void
{
    echo <<<HELP
GitHub Copilot Conversation Sync Service

Usage:
  php sync-copilot-conversations.php [OPTIONS]

Options:
  --watch              Run in continuous monitoring mode (syncs every 60s)
  --once               Run one-time sync and exit
  --local-path=PATH    Specify custom Copilot logs path
  --help               Show this help message

Examples:
  # One-time sync (auto-detect path)
  php sync-copilot-conversations.php --once

  # Continuous monitoring
  php sync-copilot-conversations.php --watch

  # Custom path (Windows via WSL)
  php sync-copilot-conversations.php --watch --local-path=/mnt/c/Users/pearc/AppData/Roaming/Code/User/globalStorage/github.copilot-chat/

  # Custom path (direct mount)
  php sync-copilot-conversations.php --watch --local-path=/path/to/mounted/logs/

Copilot Log Locations:
  Windows: %APPDATA%\\Code\\User\\globalStorage\\github.copilot-chat\\
  Mac:     ~/Library/Application Support/Code/User/globalStorage/github.copilot-chat/
  Linux:   ~/.config/Code/User/globalStorage/github.copilot-chat/

Notes:
  - Requires database access (hdgwrzntwa)
  - Can run as cron job or systemd service
  - Automatically detects new conversations
  - Updates existing conversations with new messages
  - Stores metadata (tools used, workspace context)

HELP;
}
