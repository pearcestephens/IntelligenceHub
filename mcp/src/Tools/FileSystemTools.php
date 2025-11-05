<?php

namespace IntelligenceHub\MCP\Tools;

class FileSystemTools extends BaseTool {
    private string $jailRoot;

    public function __construct() {
        $this->jailRoot = $_ENV['TOOL_FS_ROOT'] ?? '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html';
    }

    public function getName(): string {
        return 'fs';
    }

    public function getSchema(): array {
        return [
            'fs.write' => [
                'description' => 'Write content to a file in jailed directory',
                'parameters' => [
                    'path' => ['type' => 'string', 'required' => true],
                    'content' => ['type' => 'string', 'required' => true]
                ]
            ],
            'fs.read' => [
                'description' => 'Read content from a file in jailed directory',
                'parameters' => [
                    'path' => ['type' => 'string', 'required' => true],
                    'max_lines' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'fs.list' => [
                'description' => 'List files in a directory',
                'parameters' => [
                    'path' => ['type' => 'string', 'required' => true],
                    'recursive' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'fs.info' => [
                'description' => 'Get file/directory information',
                'parameters' => [
                    'path' => ['type' => 'string', 'required' => true]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'write';

        switch ($method) {
            case 'write':
                return $this->write($args);
            case 'read':
                return $this->read($args);
            case 'list':
                return $this->listFiles($args);
            case 'info':
                return $this->info($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }    private function write(array $args): array {
        $path = $args['path'] ?? '';
        $content = $args['content'] ?? '';

        if (empty($path)) {
            return $this->fail('path is required');
        }

        // Jail the path
        $fullPath = $this->jailPath($path);
        if ($fullPath === false) {
            return $this->fail('Invalid path (outside jail)');
        }

        // Create directory if needed
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return $this->fail("Failed to create directory: $dir");
            }
        }

        // Write file
        $bytes = file_put_contents($fullPath, $content);
        if ($bytes === false) {
            return $this->fail("Failed to write file: $fullPath");
        }

        return $this->ok([
            'written' => $bytes,
            'path' => $path,
            'full_path' => $fullPath
        ]);
    }

    private function read(array $args): array {
        $path = $args['path'] ?? '';
        $maxLines = $args['max_lines'] ?? null;

        if (empty($path)) {
            return $this->fail('path is required');
        }

        // Jail the path
        $fullPath = $this->jailPath($path);
        if ($fullPath === false) {
            return $this->fail('Invalid path (outside jail)');
        }

        if (!file_exists($fullPath)) {
            return $this->fail("File not found: $path");
        }

        if (!is_readable($fullPath)) {
            return $this->fail("File not readable: $path");
        }

        // Read file
        if ($maxLines !== null) {
            $lines = [];
            $handle = fopen($fullPath, 'r');
            $count = 0;
            while (($line = fgets($handle)) !== false && $count < $maxLines) {
                $lines[] = $line;
                $count++;
            }
            fclose($handle);
            $content = implode('', $lines);
        } else {
            $content = file_get_contents($fullPath);
            if ($content === false) {
                return $this->fail("Failed to read file: $path");
            }
        }

        return $this->ok([
            'content' => $content,
            'size' => strlen($content),
            'path' => $path
        ]);
    }

    private function listFiles(array $args): array {
        $path = $args['path'] ?? '/';
        $recursive = $args['recursive'] ?? false;

        // Jail the path
        $fullPath = $this->jailPath($path);
        if ($fullPath === false) {
            return $this->fail('Invalid path (outside jail)');
        }

        if (!is_dir($fullPath)) {
            return $this->fail("Not a directory: $path");
        }

        $files = [];

        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $relativePath = str_replace($this->jailRoot, '', $file->getPathname());
                $files[] = [
                    'path' => $relativePath,
                    'type' => $file->isDir() ? 'dir' : 'file',
                    'size' => $file->isFile() ? $file->getSize() : null
                ];
            }
        } else {
            $iterator = new \DirectoryIterator($fullPath);

            foreach ($iterator as $file) {
                if ($file->isDot()) continue;

                $relativePath = str_replace($this->jailRoot, '', $file->getPathname());
                $files[] = [
                    'path' => $relativePath,
                    'type' => $file->isDir() ? 'dir' : 'file',
                    'size' => $file->isFile() ? $file->getSize() : null
                ];
            }
        }

        return $this->ok([
            'files' => $files,
            'count' => count($files),
            'path' => $path
        ]);
    }

    private function jailPath(string $path): string|false {
        // Remove leading slash for relative resolution
        $path = ltrim($path, '/');

        // Handle root directory
        if ($path === '' || $path === '.') {
            return $this->jailRoot;
        }

        // Build full path
        $fullPath = $this->jailRoot . '/' . $path;

        // Check if path already exists (directory or file)
        $realFull = realpath($fullPath);
        if ($realFull !== false) {
            // Path exists, use it directly
            if (strpos($realFull, $this->jailRoot) !== 0) {
                return false;
            }
            return $realFull;
        }

        // Path doesn't exist yet - build it for write operations
        $realPath = realpath(dirname($fullPath));
        if ($realPath === false) {
            $realPath = $this->jailRoot . '/' . dirname($path);
        }

        $finalPath = $realPath . '/' . basename($path);

        // Ensure it's within jail
        if (strpos($finalPath, $this->jailRoot) !== 0) {
            return false;
        }


        return $finalPath;
    }

    private function info(array $args): array {
        $path = $args['path'] ?? '';

        if (empty($path)) {
            return $this->fail('path is required');
        }

        // Jail the path
        $fullPath = $this->jailPath($path);
        if ($fullPath === false) {
            return $this->fail('Invalid path (outside jail)');
        }

        if (!file_exists($fullPath)) {
            return $this->fail("Path not found: $path");
        }

        $info = [
            'path' => $path,
            'full_path' => $fullPath,
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'exists' => true,
            'readable' => is_readable($fullPath),
            'writable' => is_writable($fullPath),
            'size' => is_file($fullPath) ? filesize($fullPath) : null,
            'modified' => filemtime($fullPath),
            'modified_human' => date('Y-m-d H:i:s', filemtime($fullPath))
        ];

        if (is_file($fullPath)) {
            $info['mime_type'] = mime_content_type($fullPath);
            $info['extension'] = pathinfo($fullPath, PATHINFO_EXTENSION);
        }

        if (is_dir($fullPath)) {
            $iterator = new \FilesystemIterator($fullPath);
            $info['item_count'] = iterator_count($iterator);
        }

        return $this->ok($info);
    }
}
