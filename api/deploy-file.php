<?php
/**
 * File Deployment Endpoint for Satellites
 * Receives and deploys files from Intelligence Hub
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gpt.ecigdis.co.nz');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = [
    'success' => false,
    'message' => '',
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['path'], $input['content'])) {
        throw new Exception('Missing required parameters', 400);
    }
    
    $path = $input['path'];
    $content = $input['content'];
    $encoding = $input['encoding'] ?? 'plain';
    
    // Decode content if base64 encoded
    if ($encoding === 'base64') {
        $content = base64_decode($content);
    }
    
    // Security: Only allow deployment to specific paths
    $allowedPaths = [
        '_automation/',
        'api/automation/',
        'modules/',
    ];
    
    $pathAllowed = false;
    foreach ($allowedPaths as $allowedPath) {
        if (strpos($path, $allowedPath) === 0) {
            $pathAllowed = true;
            break;
        }
    }
    
    if (!$pathAllowed) {
        throw new Exception('Path not allowed for deployment', 403);
    }
    
    // Create full file path
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
    
    // Create directory if it doesn't exist
    $directory = dirname($fullPath);
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true)) {
            throw new Exception('Failed to create directory', 500);
        }
    }
    
    // Write file
    if (file_put_contents($fullPath, $content) === false) {
        throw new Exception('Failed to write file', 500);
    }
    
    // Set appropriate permissions
    chmod($fullPath, 0644);
    
    $response['success'] = true;
    $response['message'] = "File deployed successfully to {$path}";
    $response['data'] = [
        'path' => $path,
        'size' => strlen($content),
        'created' => date('Y-m-d H:i:s')
    ];
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = [
        'code' => $e->getCode() ?: 500,
        'message' => $e->getMessage()
    ];
    http_response_code($e->getCode() ?: 500);
}

echo json_encode($response, JSON_PRETTY_PRINT);