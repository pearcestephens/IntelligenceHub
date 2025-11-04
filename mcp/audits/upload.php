<?php
/**
 * AUDIT UPLOAD HANDLER
 *
 * Receives screenshots, videos, and reports from frontend tools
 * Stores them in organized structure by project/server
 * Generates gallery entries for viewing
 *
 * URL: https://gpt.ecigdis.co.nz/audits/upload.php
 *
 * Expected POST data:
 * - file: Binary file data
 * - metadata: JSON metadata object
 * - api_key: Authentication key
 * - project_id: Project identifier
 * - server_id: Server identifier
 * - audit_id: Unique audit ID
 *
 * @version 1.0.0
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Configuration
define('UPLOAD_BASE_DIR', __DIR__ . '/files');
define('DATABASE_FILE', __DIR__ . '/audits.db');
define('VALID_API_KEY', '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35');
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100MB

// Validate API key
$apiKey = $_POST['api_key'] ?? '';
if ($apiKey !== VALID_API_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get POST data
$projectId = $_POST['project_id'] ?? 'unknown';
$serverId = $_POST['server_id'] ?? 'unknown';
$auditId = $_POST['audit_id'] ?? uniqid('audit_', true);
$metadata = json_decode($_POST['metadata'] ?? '{}', true);

// Validate file
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload error: ' . $file['error']]);
    exit;
}

// Check file size
if ($file['size'] > MAX_FILE_SIZE) {
    http_response_code(413);
    echo json_encode(['error' => 'File too large. Max: ' . MAX_FILE_SIZE . ' bytes']);
    exit;
}

// Sanitize project and server IDs
$projectId = preg_replace('/[^a-zA-Z0-9_-]/', '', $projectId);
$serverId = preg_replace('/[^a-zA-Z0-9_-]/', '', $serverId);

// Create directory structure
$projectDir = UPLOAD_BASE_DIR . '/' . $projectId;
$serverDir = $projectDir . '/' . $serverId;

if (!is_dir($serverDir)) {
    if (!mkdir($serverDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create storage directory']);
        exit;
    }
}

// Generate safe filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$safeFilename = $auditId . '.' . $extension;
$targetPath = $serverDir . '/' . $safeFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

// Set proper permissions
chmod($targetPath, 0644);

// Store audit metadata in SQLite
try {
    $db = new PDO('sqlite:' . DATABASE_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table if not exists
    $db->exec("
        CREATE TABLE IF NOT EXISTS audits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            audit_id TEXT UNIQUE NOT NULL,
            project_id TEXT NOT NULL,
            server_id TEXT NOT NULL,
            filename TEXT NOT NULL,
            file_path TEXT NOT NULL,
            file_size INTEGER NOT NULL,
            file_type TEXT,
            metadata TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_project (project_id),
            INDEX idx_server (server_id),
            INDEX idx_audit (audit_id),
            INDEX idx_created (created_at)
        )
    ");

    // Insert audit record
    $stmt = $db->prepare("
        INSERT INTO audits (
            audit_id, project_id, server_id, filename,
            file_path, file_size, file_type, metadata
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $auditId,
        $projectId,
        $serverId,
        $safeFilename,
        $targetPath,
        $file['size'],
        $file['type'],
        json_encode($metadata)
    ]);

} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    // Continue even if database fails
}

// Generate URLs
$directLink = 'https://gpt.ecigdis.co.nz/audits/files/' . $projectId . '/' . $serverId . '/' . $safeFilename;
$galleryLink = 'https://gpt.ecigdis.co.nz/audits/gallery.php?project=' . $projectId . '&audit=' . $auditId;

// Success response
http_response_code(201);
echo json_encode([
    'success' => true,
    'audit_id' => $auditId,
    'project_id' => $projectId,
    'server_id' => $serverId,
    'filename' => $safeFilename,
    'file_size' => $file['size'],
    'direct_link' => $directLink,
    'gallery_link' => $galleryLink,
    'timestamp' => date('c')
]);
