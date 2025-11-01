<?php
/**
 * Files API - Get real intelligence files data
 */
header('Content-Type: application/json');

define('DASHBOARD_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        listFiles();
        break;
    case 'view':
        viewFile();
        break;
    case 'stats':
        getFileStats();
        break;
    default:
        sendError('Invalid action');
}

function listFiles() {
    $db = getDbConnection();
    if (!$db) {
        sendError('Database connection failed');
    }

    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 50);
    $offset = ($page - 1) * $limit;

    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    $server = $_GET['server'] ?? '';

    // Build WHERE clause
    $where = ["server_id != 'hdgwrzntwa'"]; // Exclude intelligence server scanning itself
    $params = [];

    if ($search) {
        $where[] = "(file_name LIKE ? OR file_path LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($type) {
        $where[] = "intelligence_type = ?";
        $params[] = $type;
    }

    if ($server) {
        $where[] = "server_id = ?";
        $params[] = $server;
    }

    $whereClause = implode(' AND ', $where);

    try {
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM intelligence_files WHERE $whereClause";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        // Get files
        $sql = "SELECT
                    file_id,
                    file_name,
                    file_path,
                    file_size,
                    file_type,
                    intelligence_type,
                    server_id,
                    extracted_at as created_at,
                    updated_at
                FROM intelligence_files
                WHERE $whereClause
                ORDER BY updated_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $files = $stmt->fetchAll();

        // Format file sizes
        foreach ($files as &$file) {
            $file['id'] = $file['file_id']; // Add id alias for frontend
            $file['file_size_formatted'] = formatFileSize($file['file_size']);
            $file['server_name'] = getServerName($file['server_id']);
            $file['type_badge'] = getTypeBadge($file['file_type']);
            $file['type_display'] = $file['intelligence_type'] ?: $file['file_type'];
            $file['icon'] = getFileIcon($file['file_name']);
        }        sendSuccess([
            'files' => $files,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);

    } catch (Exception $e) {
        sendError('Failed to fetch files: ' . $e->getMessage());
    }
}

function viewFile() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        sendError('File ID required');
    }

    $db = getDbConnection();
    if (!$db) {
        sendError('Database connection failed');
    }

    try {
        $stmt = $db->prepare("SELECT * FROM intelligence_files WHERE file_id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if (!$file) {
            sendError('File not found');
        }

        if (!$file) {
            sendError('File not found');
        }

        // Read file content from database (stored during extraction)
        $content = $file['file_content'] ?? null;

        if (!$content || trim($content) === '') {
            // No content in database
            $content = '[Content not available - file may not have been extracted yet]';
        } else {
            // Check if content is too large to display
            $contentSize = strlen($content);
            if ($contentSize > 1024 * 1024) {
                $content = '[File too large to display - ' . formatFileSize($contentSize) . ']';
            } else {
                // Detect if binary (though it shouldn't be stored in DB)
                if (mb_detect_encoding($content, 'UTF-8', true) === false) {
                    $content = '[Binary content - cannot display]';
                }
            }
        }

        sendSuccess([
            'file' => $file,
            'content' => $content,
            'server_name' => getServerName($file['server_id']),
            'file_size_formatted' => formatFileSize($file['file_size'])
        ]);

    } catch (Exception $e) {
        sendError('Failed to view file: ' . $e->getMessage());
    }
}function getFileStats() {
    $db = getDbConnection();
    if (!$db) {
        sendError('Database connection failed');
    }

    try {
        // Total files by type
        $stmt = $db->query("
            SELECT intelligence_type, COUNT(*) as count
            FROM intelligence_files
            WHERE server_id != 'hdgwrzntwa'
            GROUP BY intelligence_type
        ");
        $byType = $stmt->fetchAll();

        // Total files by server
        $stmt = $db->query("
            SELECT server_id, COUNT(*) as count
            FROM intelligence_files
            WHERE server_id != 'hdgwrzntwa'
            GROUP BY server_id
        ");
        $byServer = $stmt->fetchAll();

        // Total size
        $stmt = $db->query("
            SELECT SUM(file_size) as total_size
            FROM intelligence_files
            WHERE server_id != 'hdgwrzntwa'
        ");
        $totalSize = $stmt->fetch()['total_size'] ?? 0;

        // Total count
        $stmt = $db->query("
            SELECT COUNT(*) as total
            FROM intelligence_files
            WHERE server_id != 'hdgwrzntwa'
        ");
        $totalFiles = $stmt->fetch()['total'];

        sendSuccess([
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_formatted' => formatFileSize($totalSize),
            'by_type' => $byType,
            'by_server' => $byServer
        ]);

    } catch (Exception $e) {
        sendError('Failed to get stats: ' . $e->getMessage());
    }
}

// Helper functions
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

function getServerName($serverId) {
    $servers = [
        'jcepnzzkmj' => 'CIS Main',
        'dvaxgvsxmz' => 'Retail',
        'fhrehrpjmu' => 'Wholesale',
        'hdgwrzntwa' => 'Intelligence'
    ];
    return $servers[$serverId] ?? $serverId;
}

function getTypeBadge($type) {
    $badges = [
        'documentation' => 'info',
        'code_intelligence' => 'success',
        'business_intelligence' => 'primary',
        'operational_intelligence' => 'warning',
        'system' => 'secondary'
    ];
    return $badges[$type] ?? 'secondary';
}

function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $icons = [
        'php' => 'fa-file-code text-primary',
        'js' => 'fa-file-code text-warning',
        'css' => 'fa-file-code text-info',
        'html' => 'fa-file-code text-danger',
        'json' => 'fa-file-code text-success',
        'xml' => 'fa-file-code text-secondary',
        'md' => 'fa-file-alt text-primary',
        'txt' => 'fa-file-alt text-secondary',
        'pdf' => 'fa-file-pdf text-danger',
        'jpg' => 'fa-file-image text-info',
        'jpeg' => 'fa-file-image text-info',
        'png' => 'fa-file-image text-info',
        'gif' => 'fa-file-image text-info',
        'svg' => 'fa-file-image text-success',
        'zip' => 'fa-file-archive text-warning',
        'tar' => 'fa-file-archive text-warning',
        'gz' => 'fa-file-archive text-warning',
        'sql' => 'fa-database text-primary',
        'log' => 'fa-file-alt text-muted'
    ];

    return $icons[$ext] ?? 'fa-file text-secondary';
}

function sendSuccess($data) {
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function sendError($message) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
