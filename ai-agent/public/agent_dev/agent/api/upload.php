<?php

declare(strict_types=1);

/**
 * Upload API Endpoint - File upload and document ingestion
 * 
 * Handles file uploads with document processing:
 * - Text file processing and ingestion into knowledge base
 * - Multiple file format support (txt, md, pdf, docx)
 * - Automatic text extraction and chunking
 * - Metadata extraction and storage
 * - Security validation and file sanitization
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Validate;
use App\Util\Errors;

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();
    
    // Validate file upload
    if (empty($_FILES['file'])) {
        throw Errors::validationError('No file uploaded');
    }
    
    $uploadedFile = $_FILES['file'];
    
    // Check for upload errors
    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds php.ini limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = $errorMessages[$uploadedFile['error']] ?? 'Unknown upload error';
        throw Errors::validationError('Upload error: ' . $errorMessage);
    }
    
    // Validate file properties
    $fileName = $uploadedFile['name'];
    $fileSize = $uploadedFile['size'];
    $fileTmpName = $uploadedFile['tmp_name'];
    $fileType = $uploadedFile['type'];
    
    $logger->info('File upload request', [
        'filename' => $fileName,
        'size' => $fileSize,
        'type' => $fileType,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    // Size validation (max 10MB)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($fileSize > $maxFileSize) {
        throw Errors::validationError('File too large. Maximum size: ' . ($maxFileSize / 1024 / 1024) . 'MB');
    }
    
    // File type validation
    $allowedExtensions = ['txt', 'md', 'pdf', 'doc', 'docx', 'rtf', 'html', 'htm'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw Errors::validationError('File type not supported. Allowed: ' . implode(', ', $allowedExtensions));
    }
    
    // Security: validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMimeType = finfo_file($finfo, $fileTmpName);
    finfo_close($finfo);
    
    $allowedMimeTypes = [
        'text/plain',
        'text/markdown',
        'text/html',
        'text/rtf',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/rtf'
    ];
    
    if (!in_array($detectedMimeType, $allowedMimeTypes)) {
        throw Errors::validationError('File MIME type not allowed: ' . $detectedMimeType);
    }
    
    // Extract text content based on file type
    $textContent = extractTextContent($fileTmpName, $fileExtension, $detectedMimeType);
    
    if (empty($textContent)) {
        throw Errors::validationError('Could not extract text content from file');
    }
    
    // Validate extracted content
    $textContent = Validate::string($textContent, 1, 10000000, 'extracted_content');
    
    // Get optional metadata from POST data
    $title = $_POST['title'] ?? pathinfo($fileName, PATHINFO_FILENAME);
    $description = $_POST['description'] ?? '';
    $tags = isset($_POST['tags']) ? explode(',', $_POST['tags']) : [];
    
    // Build document metadata
    $metadata = [
        'filename' => $fileName,
        'file_size' => $fileSize,
        'file_type' => $fileExtension,
        'mime_type' => $detectedMimeType,
        'uploaded_at' => date('c'),
        'description' => $description,
        'tags' => array_map('trim', $tags),
        'extraction_method' => getExtractionMethod($fileExtension),
        'character_count' => strlen($textContent),
        'word_count' => str_word_count($textContent)
    ];
    
    // Add document to knowledge base
    $documentId = $agent->addDocument($title, $textContent, $metadata);
    
    // Clean up temporary file
    if (file_exists($fileTmpName)) {
        unlink($fileTmpName);
    }
    
    $result = [
        'success' => true,
        'document_id' => $documentId,
        'filename' => $fileName,
        'title' => $title,
        'size' => $fileSize,
        'content_length' => strlen($textContent),
        'metadata' => $metadata,
        'message' => 'File uploaded and processed successfully'
    ];
    
    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    // Clean up
    $agent->shutdown();
    
} catch (Exception $e) {
    $errorCode = 500;
    $errorType = 'internal_error';
    
    // Determine appropriate error code
    if (strpos($e->getMessage(), 'validation') !== false || 
        strpos($e->getMessage(), 'Upload error') !== false ||
        strpos($e->getMessage(), 'File too large') !== false ||
        strpos($e->getMessage(), 'not supported') !== false) {
        $errorCode = 400;
        $errorType = 'validation_error';
    }
    
    // Log error
    if (isset($logger)) {
        $logger->error('Upload API error', [
            'error' => $e->getMessage(),
            'filename' => $_FILES['file']['name'] ?? null,
            'type' => $errorType,
            'code' => $errorCode
        ]);
    }
    
    // Clean up temporary file on error
    if (isset($fileTmpName) && file_exists($fileTmpName)) {
        unlink($fileTmpName);
    }
    
    // Return error response
    http_response_code($errorCode);
    
    $errorResponse = [
        'success' => false,
        'error' => [
            'type' => $errorType,
            'message' => $e->getMessage(),
            'code' => $errorCode
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * Extract text content from uploaded file based on type
 */
function extractTextContent(string $filePath, string $extension, string $mimeType): string
{
    switch ($extension) {
        case 'txt':
        case 'md':
        case 'html':
        case 'htm':
            return file_get_contents($filePath);
            
        case 'pdf':
            return extractPdfText($filePath);
            
        case 'doc':
        case 'docx':
            return extractWordText($filePath, $extension);
            
        case 'rtf':
            return extractRtfText($filePath);
            
        default:
            // Fallback: try to read as plain text
            return file_get_contents($filePath);
    }
}

/**
 * Extract text from PDF files (requires external tool or library)
 */
function extractPdfText(string $filePath): string
{
    // Try using pdftotext command if available
    if (shell_exec('which pdftotext') !== null) {
        $output = shell_exec("pdftotext '$filePath' -");
        if ($output !== null) {
            return $output;
        }
    }
    
    // Fallback: basic PDF text extraction (very limited)
    $content = file_get_contents($filePath);
    
    // Simple regex to extract text (not reliable for complex PDFs)
    if (preg_match_all('/BT\s*(.*?)\s*ET/s', $content, $matches)) {
        $text = implode(' ', $matches[1]);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    throw new Exception('PDF text extraction not available. Install pdftotext or use a text file.');
}

/**
 * Extract text from Word documents (requires external tool or library)
 */
function extractWordText(string $filePath, string $extension): string
{
    if ($extension === 'docx') {
        // Try to extract from DOCX (ZIP format)
        $zip = new ZipArchive();
        
        if ($zip->open($filePath) === true) {
            $content = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if ($content !== false) {
                // Remove XML tags and decode entities
                $text = strip_tags($content);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                return $text;
            }
        }
    }
    
    // Try using antiword or catdoc for .doc files
    if ($extension === 'doc' && shell_exec('which antiword') !== null) {
        $output = shell_exec("antiword '$filePath'");
        if ($output !== null) {
            return $output;
        }
    }
    
    throw new Exception('Word document text extraction not available. Please convert to text format.');
}

/**
 * Extract text from RTF files
 */
function extractRtfText(string $filePath): string
{
    $content = file_get_contents($filePath);
    
    // Simple RTF text extraction (basic implementation)
    $text = preg_replace('/\{[^}]*\}/', '', $content); // Remove RTF commands
    $text = preg_replace('/\\\\[a-z]+\d*\s?/', '', $text); // Remove control words
    $text = str_replace(['\\', '{', '}'], '', $text); // Remove remaining RTF chars
    
    return trim($text);
}

/**
 * Get extraction method used for file type
 */
function getExtractionMethod(string $extension): string
{
    $methods = [
        'txt' => 'direct_read',
        'md' => 'direct_read',
        'html' => 'direct_read',
        'htm' => 'direct_read',
        'pdf' => 'pdftotext_or_regex',
        'doc' => 'antiword_or_unsupported',
        'docx' => 'zip_xml_extraction',
        'rtf' => 'rtf_parser'
    ];
    
    return $methods[$extension] ?? 'unknown';
}

// Ensure output is flushed
if (ob_get_level()) {
    ob_end_flush();
}
flush();