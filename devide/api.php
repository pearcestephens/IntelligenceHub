<?php
/**
 * DevIDE File API
 * Handles file/folder browsing, reading, and saving
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = $_GET['action'] ?? '';
$path = $_GET['path'] ?? '.';

// Base directory (current server root)
$baseDir = dirname(__DIR__);

// Security: Prevent directory traversal
function sanitizePath($path, $baseDir) {
    $realBase = realpath($baseDir);
    $realPath = realpath($baseDir . '/' . $path);

    // If path doesn't exist yet (for new files), check parent
    if (!$realPath) {
        $realPath = realpath(dirname($baseDir . '/' . $path));
    }

    if ($realPath === false || strpos($realPath, $realBase) !== 0) {
        return false;
    }

    return $realPath;
}

switch ($action) {
    case 'list':
        // List files and folders
        $safePath = sanitizePath($path, $baseDir);

        if (!$safePath || !is_dir($safePath)) {
            echo json_encode(['success' => false, 'error' => 'Invalid directory']);
            exit;
        }

        $files = [];
        $items = scandir($safePath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $safePath . '/' . $item;
            $relativePath = str_replace($baseDir . '/', '', $fullPath);

            // Skip hidden files/folders (except .htaccess)
            if ($item[0] === '.' && $item !== '.htaccess') continue;

            // Skip vendor, node_modules, etc
            if (in_array($item, ['vendor', 'node_modules', '.git', '.vscode'])) continue;

            $files[] = [
                'name' => $item,
                'type' => is_dir($fullPath) ? 'dir' : 'file',
                'path' => $relativePath,
                'size' => is_file($fullPath) ? filesize($fullPath) : 0,
                'modified' => filemtime($fullPath)
            ];
        }

        // Sort: directories first, then alphabetically
        usort($files, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $a['type'] === 'dir' ? -1 : 1;
        });

        echo json_encode(['success' => true, 'files' => $files]);
        break;

    case 'read':
        // Read file content
        $safePath = sanitizePath($path, $baseDir);

        if (!$safePath || !is_file($safePath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            exit;
        }

        // Check file size (max 5MB for editor)
        $filesize = filesize($safePath);
        if ($filesize > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'File too large (max 5MB)']);
            exit;
        }

        $content = file_get_contents($safePath);

        echo json_encode([
            'success' => true,
            'content' => $content,
            'size' => $filesize,
            'modified' => filemtime($safePath)
        ]);
        break;

    case 'save':
        // Save file content
        $data = json_decode(file_get_contents('php://input'), true);
        $filePath = $data['path'] ?? '';
        $content = $data['content'] ?? '';

        if (empty($filePath)) {
            echo json_encode(['success' => false, 'error' => 'No file path provided']);
            exit;
        }

        $safePath = sanitizePath($filePath, $baseDir);

        if (!$safePath) {
            echo json_encode(['success' => false, 'error' => 'Invalid file path']);
            exit;
        }

        // Create directory if it doesn't exist
        $dir = dirname($safePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Backup existing file
        if (file_exists($safePath)) {
            $backupDir = $baseDir . '/_kb/backups/devide';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupFile = $backupDir . '/' . basename($safePath) . '.' . date('Y-m-d_His') . '.bak';
            copy($safePath, $backupFile);
        }

        // Save file
        $result = file_put_contents($safePath, $content);

        if ($result === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write file']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'bytes' => $result,
            'path' => str_replace($baseDir . '/', '', $safePath)
        ]);
        break;

    case 'delete':
        // Delete file
        $filePath = $_POST['path'] ?? '';

        if (empty($filePath)) {
            echo json_encode(['success' => false, 'error' => 'No file path provided']);
            exit;
        }

        $safePath = sanitizePath($filePath, $baseDir);

        if (!$safePath || !file_exists($safePath)) {
            echo json_encode(['success' => false, 'error' => 'File not found']);
            exit;
        }

        // Move to trash instead of deleting
        $trashDir = $baseDir . '/_kb/backups/trash';
        if (!is_dir($trashDir)) {
            mkdir($trashDir, 0755, true);
        }

        $trashFile = $trashDir . '/' . basename($safePath) . '.' . date('Y-m-d_His');
        rename($safePath, $trashFile);

        echo json_encode(['success' => true, 'moved_to' => $trashFile]);
        break;

    case 'create':
        // Create new file or folder
        $data = json_decode(file_get_contents('php://input'), true);
        $filePath = $data['path'] ?? '';
        $type = $data['type'] ?? 'file'; // 'file' or 'dir'

        if (empty($filePath)) {
            echo json_encode(['success' => false, 'error' => 'No path provided']);
            exit;
        }

        $safePath = sanitizePath($filePath, $baseDir);

        if (!$safePath) {
            echo json_encode(['success' => false, 'error' => 'Invalid path']);
            exit;
        }

        if (file_exists($safePath)) {
            echo json_encode(['success' => false, 'error' => 'File/folder already exists']);
            exit;
        }

        if ($type === 'dir') {
            mkdir($safePath, 0755, true);
        } else {
            $dir = dirname($safePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($safePath, '');
        }

        echo json_encode(['success' => true, 'path' => str_replace($baseDir . '/', '', $safePath)]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}
