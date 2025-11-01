<?php
/**
 * VS Code Auto-Sync API
 *
 * Handles automatic synchronization of generated prompts to user's local
 * VS Code instructions folder with versioning and backup.
 *
 * Features:
 * - Generate download link for prompt file
 * - Track sync history
 * - Version management
 * - Backup system
 */

header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/../../app.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save_config':
            saveVSCodeConfig();
            break;

        case 'get_config':
            getVSCodeConfig();
            break;

        case 'generate_file':
            generatePromptFile();
            break;

        case 'sync_history':
            getSyncHistory();
            break;

        case 'download_prompt':
            downloadPrompt();
            break;

        case 'get_instructions':
            getInstructions();
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function saveVSCodeConfig() {
    $data = json_decode(file_get_contents('php://input'), true);

    $config = [
        'local_path' => $data['local_path'] ?? '',
        'auto_sync' => $data['auto_sync'] ?? false,
        'backup_enabled' => $data['backup_enabled'] ?? true,
        'versioning' => $data['versioning'] ?? true,
        'filename_pattern' => $data['filename_pattern'] ?? '{date}_{title}.instructions.md'
    ];

    // Save to database
    $db = getDbConnection();

    // Create config table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS vscode_sync_config (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT DEFAULT 1,
            config JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Check if config exists
    $stmt = $db->prepare("SELECT id FROM vscode_sync_config WHERE user_id = 1");
    $stmt->execute();
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $db->prepare("UPDATE vscode_sync_config SET config = ? WHERE user_id = 1");
        $stmt->execute([json_encode($config)]);
    } else {
        $stmt = $db->prepare("INSERT INTO vscode_sync_config (user_id, config) VALUES (1, ?)");
        $stmt->execute([json_encode($config)]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'VS Code sync configuration saved',
        'config' => $config
    ]);
}

function getVSCodeConfig() {
    $db = getDbConnection();

    $stmt = $db->prepare("SELECT config FROM vscode_sync_config WHERE user_id = 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'success' => true,
            'config' => json_decode($result['config'], true)
        ]);
    } else {
        // Return default config
        echo json_encode([
            'success' => true,
            'config' => [
                'local_path' => '',
                'auto_sync' => false,
                'backup_enabled' => true,
                'versioning' => true,
                'filename_pattern' => '{date}_{title}.instructions.md'
            ]
        ]);
    }
}

function generatePromptFile() {
    $data = json_decode(file_get_contents('php://input'), true);

    $prompt = $data['prompt'] ?? '';
    $title = $data['title'] ?? 'prompt';
    $metadata = $data['metadata'] ?? [];

    if (empty($prompt)) {
        throw new Exception('Prompt content is required');
    }

    // Generate filename
    $date = date('Y-m-d');
    $sanitizedTitle = preg_replace('/[^a-z0-9]+/i', '_', strtolower($title));
    $filename = "{$date}_{$sanitizedTitle}.instructions.md";

    // Create full prompt with metadata
    $content = "---\n";
    $content .= "applyTo: '**'\n";
    $content .= "createdAt: " . date('Y-m-d H:i:s') . "\n";
    $content .= "title: " . $title . "\n";
    if (!empty($metadata['priority'])) {
        $content .= "priority: " . $metadata['priority'] . "\n";
    }
    if (!empty($metadata['categories'])) {
        $content .= "categories: " . implode(', ', $metadata['categories']) . "\n";
    }
    $content .= "---\n\n";
    $content .= $prompt;

    // Save to database
    $db = getDbConnection();

    // Create sync history table
    $db->exec("
        CREATE TABLE IF NOT EXISTS vscode_sync_history (
            id INT PRIMARY KEY AUTO_INCREMENT,
            filename VARCHAR(255),
            content LONGTEXT,
            metadata JSON,
            version INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Check for existing versions
    $stmt = $db->prepare("SELECT MAX(version) as max_version FROM vscode_sync_history WHERE filename = ?");
    $stmt->execute([$filename]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $version = ($result['max_version'] ?? 0) + 1;

    // Save to history
    $stmt = $db->prepare("
        INSERT INTO vscode_sync_history (filename, content, metadata, version)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $filename,
        $content,
        json_encode($metadata),
        $version
    ]);

    $id = $db->lastInsertId();

    echo json_encode([
        'success' => true,
        'id' => $id,
        'filename' => $filename,
        'version' => $version,
        'download_url' => "api/vscode-sync.php?action=download_prompt&id={$id}",
        'size' => strlen($content)
    ]);
}

function downloadPrompt() {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        throw new Exception('Prompt ID is required');
    }

    $db = getDbConnection();
    $stmt = $db->prepare("SELECT filename, content FROM vscode_sync_history WHERE id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        throw new Exception('Prompt not found');
    }

    // Force download
    header('Content-Type: text/markdown');
    header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
    header('Content-Length: ' . strlen($result['content']));
    echo $result['content'];
    exit;
}

function getSyncHistory() {
    $limit = $_GET['limit'] ?? 50;

    $db = getDbConnection();
    $stmt = $db->prepare("
        SELECT id, filename, version, created_at,
               LENGTH(content) as size,
               metadata
        FROM vscode_sync_history
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($history as &$item) {
        $item['metadata'] = json_decode($item['metadata'], true);
        $item['download_url'] = "api/vscode-sync.php?action=download_prompt&id={$item['id']}";
    }

    echo json_encode([
        'success' => true,
        'history' => $history,
        'total' => count($history)
    ]);
}

function getInstructions() {
    echo json_encode([
        'success' => true,
        'instructions' => [
            'windows' => [
                'path' => 'C:\\Users\\{YourUsername}\\AppData\\Roaming\\Code\\User\\prompts\\',
                'steps' => [
                    '1. Copy the path above',
                    '2. Replace {YourUsername} with your Windows username',
                    '3. Paste into the Local Path field',
                    '4. Enable Auto-Sync',
                    '5. Click "Save Configuration"',
                    '6. Generated prompts will auto-download to this folder'
                ]
            ],
            'mac' => [
                'path' => '~/Library/Application Support/Code/User/prompts/',
                'steps' => [
                    '1. Copy the path above',
                    '2. Paste into the Local Path field',
                    '3. Enable Auto-Sync',
                    '4. Click "Save Configuration"',
                    '5. Generated prompts will auto-download to this folder'
                ]
            ],
            'linux' => [
                'path' => '~/.config/Code/User/prompts/',
                'steps' => [
                    '1. Copy the path above',
                    '2. Paste into the Local Path field',
                    '3. Enable Auto-Sync',
                    '4. Click "Save Configuration"',
                    '5. Generated prompts will auto-download to this folder'
                ]
            ],
            'manual' => [
                'steps' => [
                    '1. Generate your prompt in AI Control Center',
                    '2. Click "Download" button',
                    '3. Save file to your VS Code prompts folder',
                    '4. VS Code will auto-detect and load it'
                ]
            ]
        ]
    ]);
}
