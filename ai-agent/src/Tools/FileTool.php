<?php

/**
 * File Tool for safe file operations with security controls
 * Provides sandboxed file access for the AI agent
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\Logger;
use App\Config;
use App\Util\Validate;
use App\Tools\Contracts\ToolContract;

class FileTool implements ToolContract
{
    private const MAX_FILE_SIZE = 1024 * 1024; // 1MB
    private const ALLOWED_EXTENSIONS = ['txt', 'json', 'csv', 'log', 'md', 'php', 'js', 'css', 'html'];
    private const BLOCKED_PATTERNS = ['.env', 'config', 'password', 'secret', 'key', 'token'];

    /**
     * Read file contents with safety checks
     */
    public static function read(array $parameters, array $context = []): array
    {
        $path = $parameters['path'] ?? '';
        $encoding = $parameters['encoding'] ?? 'utf-8';
        $maxLines = $parameters['max_lines'] ?? null;

        Validate::string($path, 1, 500);

        try {
            // Validate and normalize path
            $safePath = self::validateAndNormalizePath($path);

            // Check file exists and is readable
            if (!file_exists($safePath)) {
                return [
                    'error' => 'File not found',
                    'error_type' => 'FileNotFound',
                    'path' => $path
                ];
            }

            if (!is_file($safePath)) {
                return [
                    'error' => 'Path is not a file',
                    'error_type' => 'InvalidFileType',
                    'path' => $path
                ];
            }

            if (!is_readable($safePath)) {
                return [
                    'error' => 'File is not readable',
                    'error_type' => 'PermissionDenied',
                    'path' => $path
                ];
            }

            // Check file size
            $fileSize = filesize($safePath);
            if ($fileSize > self::MAX_FILE_SIZE) {
                return [
                    'error' => 'File too large (max ' . self::formatBytes(self::MAX_FILE_SIZE) . ')',
                    'error_type' => 'FileTooLarge',
                    'path' => $path,
                    'size' => $fileSize
                ];
            }

            // Check file extension
            $extension = strtolower(pathinfo($safePath, PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                return [
                    'error' => 'File type not allowed',
                    'error_type' => 'InvalidFileType',
                    'path' => $path,
                    'extension' => $extension,
                    'allowed' => self::ALLOWED_EXTENSIONS
                ];
            }

            // Read file content
            $startTime = microtime(true);
            $content = file_get_contents($safePath);
            $duration = (microtime(true) - $startTime) * 1000;

            if ($content === false) {
                return [
                    'error' => 'Failed to read file',
                    'error_type' => 'ReadError',
                    'path' => $path
                ];
            }

            // Handle encoding
            if ($encoding !== 'utf-8') {
                $content = mb_convert_encoding($content, 'utf-8', $encoding);
            }

            // Limit lines if specified
            if ($maxLines !== null && $maxLines > 0) {
                $lines = explode("\n", $content);
                if (count($lines) > $maxLines) {
                    $content = implode("\n", array_slice($lines, 0, $maxLines));
                    $truncated = count($lines) - $maxLines;
                } else {
                    $truncated = 0;
                }
            } else {
                $truncated = 0;
            }

            // Sanitize sensitive content
            $content = self::sanitizeContent($content);

            Logger::info('File read successfully', [
                'path' => $path,
                'size' => $fileSize,
                'lines' => substr_count($content, "\n") + 1,
                'truncated_lines' => $truncated,
                'duration_ms' => (int)$duration
            ]);

            return [
                'content' => $content,
                'path' => $path,
                'size' => $fileSize,
                'size_formatted' => self::formatBytes($fileSize),
                'encoding' => $encoding,
                'extension' => $extension,
                'lines' => substr_count($content, "\n") + 1,
                'truncated_lines' => $truncated,
                'duration_ms' => (int)$duration
            ];
        } catch (\Throwable $e) {
            Logger::error('File read failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'File read failed: ' . $e->getMessage(),
                'error_type' => 'ReadError',
                'path' => $path
            ];
        }
    }

    /**
     * Contract run() dispatcher for file tool
     */
    public static function run(array $params, array $context = []): array
    {
        $action = $params['action'] ?? 'read';
        switch ($action) {
            case 'read':
                return self::read($params, $context);
            case 'write':
                return self::write($params, $context);
            case 'list':
            case 'listDirectory':
                return self::listDirectory($params, $context);
            case 'info':
            case 'getInfo':
                return self::getInfo($params, $context);
            default:
                return ['success' => false, 'error' => 'Unknown action: ' . (string)$action];
        }
    }

    public static function spec(): array
    {
        return [
            'name' => 'file_tool',
            'description' => 'Read, write, list, and inspect files within a jailed FS root',
            'category' => 'development',
            'internal' => true,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['read','write','list','info']],
                    'path' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'encoding' => ['type' => 'string'],
                    'backup' => ['type' => 'boolean'],
                    'recursive' => ['type' => 'boolean'],
                    'show_hidden' => ['type' => 'boolean'],
                    'max_lines' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 20000]
                ],
                'required' => ['action','path']
            ],
            'safety' => ['timeout' => 10, 'rate_limit' => 20]
        ];
    }

    /**
     * Write file contents with safety checks
     */
    public static function write(array $parameters, array $context = []): array
    {
        $path = $parameters['path'] ?? '';
        $content = $parameters['content'] ?? '';
        $encoding = $parameters['encoding'] ?? 'utf-8';
        $backup = $parameters['backup'] ?? true;

        Validate::string($path, 1, 500);
        Validate::string($content, 0, self::MAX_FILE_SIZE);

        try {
            // Validate and normalize path
            $safePath = self::validateAndNormalizePath($path, true);

            // Check if file is in writable directory
            $directory = dirname($safePath);
            if (!is_dir($directory)) {
                return [
                    'error' => 'Directory does not exist',
                    'error_type' => 'DirectoryNotFound',
                    'path' => $path,
                    'directory' => $directory
                ];
            }

            if (!is_writable($directory)) {
                return [
                    'error' => 'Directory is not writable',
                    'error_type' => 'PermissionDenied',
                    'path' => $path,
                    'directory' => $directory
                ];
            }

            // Create backup if file exists
            $backupPath = null;
            if ($backup && file_exists($safePath)) {
                $backupPath = $safePath . '.backup.' . date('Y-m-d-H-i-s');
                if (!copy($safePath, $backupPath)) {
                    return [
                        'error' => 'Failed to create backup',
                        'error_type' => 'BackupFailed',
                        'path' => $path
                    ];
                }
            }

            // Handle encoding
            if ($encoding !== 'utf-8') {
                $content = mb_convert_encoding($content, $encoding, 'utf-8');
            }

            // Write file
            $startTime = microtime(true);
            $bytesWritten = file_put_contents($safePath, $content, LOCK_EX);
            $duration = (microtime(true) - $startTime) * 1000;

            if ($bytesWritten === false) {
                return [
                    'error' => 'Failed to write file',
                    'error_type' => 'WriteError',
                    'path' => $path
                ];
            }

            Logger::info('File written successfully', [
                'path' => $path,
                'bytes_written' => $bytesWritten,
                'backup_created' => $backupPath !== null,
                'duration_ms' => (int)$duration
            ]);

            return [
                'success' => true,
                'path' => $path,
                'bytes_written' => $bytesWritten,
                'size_formatted' => self::formatBytes($bytesWritten),
                'backup_path' => $backupPath,
                'encoding' => $encoding,
                'duration_ms' => (int)$duration
            ];
        } catch (\Throwable $e) {
            Logger::error('File write failed', [
                'path' => $path,
                'content_length' => strlen($content),
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'File write failed: ' . $e->getMessage(),
                'error_type' => 'WriteError',
                'path' => $path
            ];
        }
    }

    /**
     * List directory contents with safety checks
     */
    public static function listDirectory(array $parameters, array $context = []): array
    {
        $path = $parameters['path'] ?? '';
        $recursive = $parameters['recursive'] ?? false;
        $showHidden = $parameters['show_hidden'] ?? false;

        Validate::string($path, 1, 500);

        try {
            // Validate and normalize path
            $safePath = self::validateAndNormalizePath($path);

            if (!is_dir($safePath)) {
                return [
                    'error' => 'Path is not a directory',
                    'error_type' => 'NotDirectory',
                    'path' => $path
                ];
            }

            if (!is_readable($safePath)) {
                return [
                    'error' => 'Directory is not readable',
                    'error_type' => 'PermissionDenied',
                    'path' => $path
                ];
            }

            $files = [];
            $directories = [];

            if ($recursive) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($safePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
            } else {
                $iterator = new \DirectoryIterator($safePath);
            }

            foreach ($iterator as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $filename = $item->getFilename();

                // Skip hidden files unless requested
                if (!$showHidden && str_starts_with($filename, '.')) {
                    continue;
                }

                $itemPath = $recursive ? $iterator->getSubPathname() : $filename;
                $fullPath = $item->getPathname();

                $itemInfo = [
                    'name' => $filename,
                    'path' => $itemPath,
                    'size' => $item->isFile() ? $item->getSize() : 0,
                    'size_formatted' => $item->isFile() ? self::formatBytes($item->getSize()) : '',
                    'modified' => date('Y-m-d H:i:s', $item->getMTime()),
                    'permissions' => substr(sprintf('%o', $item->getPerms()), -4),
                    'readable' => $item->isReadable(),
                    'writable' => $item->isWritable()
                ];

                if ($item->isDir()) {
                    $itemInfo['type'] = 'directory';
                    $directories[] = $itemInfo;
                } else {
                    $itemInfo['type'] = 'file';
                    $itemInfo['extension'] = strtolower($item->getExtension());
                    $files[] = $itemInfo;
                }
            }

            // Sort by name
            usort($directories, fn($a, $b) => strcasecmp($a['name'], $b['name']));
            usort($files, fn($a, $b) => strcasecmp($a['name'], $b['name']));

            return [
                'path' => $path,
                'directories' => $directories,
                'files' => $files,
                'directory_count' => count($directories),
                'file_count' => count($files),
                'total_size' => array_sum(array_column($files, 'size')),
                'recursive' => $recursive
            ];
        } catch (\Throwable $e) {
            Logger::error('Directory listing failed', [
                'path' => $path,
                'recursive' => $recursive,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Directory listing failed: ' . $e->getMessage(),
                'error_type' => 'ListError',
                'path' => $path
            ];
        }
    }

    /**
     * Get file/directory information
     */
    public static function getInfo(array $parameters, array $context = []): array
    {
        $path = $parameters['path'] ?? '';

        Validate::string($path, 1, 500);

        try {
            $safePath = self::validateAndNormalizePath($path);

            if (!file_exists($safePath)) {
                return [
                    'error' => 'Path does not exist',
                    'error_type' => 'NotFound',
                    'path' => $path
                ];
            }

            $stat = stat($safePath);
            $pathInfo = pathinfo($safePath);

            $info = [
                'path' => $path,
                'real_path' => realpath($safePath),
                'type' => is_dir($safePath) ? 'directory' : 'file',
                'size' => $stat['size'],
                'size_formatted' => self::formatBytes($stat['size']),
                'created' => date('Y-m-d H:i:s', $stat['ctime']),
                'modified' => date('Y-m-d H:i:s', $stat['mtime']),
                'accessed' => date('Y-m-d H:i:s', $stat['atime']),
                'permissions' => substr(sprintf('%o', $stat['mode']), -4),
                'readable' => is_readable($safePath),
                'writable' => is_writable($safePath),
                'executable' => is_executable($safePath)
            ];

            if (is_file($safePath)) {
                $info['extension'] = strtolower($pathInfo['extension'] ?? '');
                $info['mime_type'] = mime_content_type($safePath) ?: 'unknown';

                // For text files, add encoding detection
                if (str_starts_with($info['mime_type'], 'text/')) {
                    $info['encoding'] = mb_detect_encoding(file_get_contents($safePath, false, null, 0, 1024));
                    $info['line_count'] = substr_count(file_get_contents($safePath), "\n") + 1;
                }
            }

            return $info;
        } catch (\Throwable $e) {
            Logger::error('Failed to get file info', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Failed to get file info: ' . $e->getMessage(),
                'error_type' => 'InfoError',
                'path' => $path
            ];
        }
    }

    /**
     * Validate and normalize file path for security
     */
    private static function validateAndNormalizePath(string $path, bool $allowCreate = false): string
    {
        // Get jail directory from config
        $jailPath = Config::get('TOOL_FS_ROOT', sys_get_temp_dir() . '/agent_sandbox');

        // Ensure jail directory exists
        if (!is_dir($jailPath)) {
            if (!mkdir($jailPath, 0755, true)) {
                throw new \RuntimeException("Failed to create jail directory: {$jailPath}");
            }
        }

        // Remove any directory traversal attempts
        $path = str_replace(['../', '..\\', '..'], '', $path);

        // Normalize path separators
        $path = str_replace('\\', '/', $path);

        // Remove leading slash
        $path = ltrim($path, '/');

        // Construct full path within jail
        $fullPath = rtrim($jailPath, '/') . '/' . $path;

        // Resolve real path to prevent symlink escapes
        $realPath = realpath($fullPath);

        // If file doesn't exist but we're allowing creation, use the normalized path
        if (!$realPath && $allowCreate) {
            $realPath = $fullPath;
        }

        if (!$realPath) {
            throw new \InvalidArgumentException("Invalid or inaccessible path: {$path}");
        }

        // Ensure path is within jail
        if (!str_starts_with($realPath, realpath($jailPath) . '/') && $realPath !== realpath($jailPath)) {
            throw new \InvalidArgumentException("Path outside of allowed directory: {$path}");
        }

        // Check for blocked patterns
        $lowerPath = strtolower($realPath);
        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (str_contains($lowerPath, $pattern)) {
                throw new \InvalidArgumentException("Path contains blocked pattern: {$pattern}");
            }
        }

        return $realPath;
    }

    /**
     * Sanitize file content to remove sensitive information
     */
    private static function sanitizeContent(string $content): string
    {
        // Remove common password/key patterns
        $patterns = [
            '/password\s*[=:]\s*[^\s\n]+/i' => 'password=***',
            '/api[_-]?key\s*[=:]\s*[^\s\n]+/i' => 'api_key=***',
            '/secret\s*[=:]\s*[^\s\n]+/i' => 'secret=***',
            '/token\s*[=:]\s*[^\s\n]+/i' => 'token=***',
            '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/' => '***@***.***'
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /**
     * Format bytes to human readable format
     */
    private static function formatBytes(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
