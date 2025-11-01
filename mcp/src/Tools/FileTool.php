<?php
/**
 * File Tool - Safe File Operations & Analysis
 *
 * Read, search, and analyze files with strict security controls
 */

namespace MCP\Tools;

class FileTool
{
    protected string $name = 'file';
    protected string $description = 'Safe file operations - read, search, analyze files with security controls';

    protected array $inputSchema = [
        'type' => 'object',
        'properties' => [
            'action' => [
                'type' => 'string',
                'enum' => ['read', 'search', 'list', 'analyze', 'stats', 'tree'],
                'description' => 'File operation to perform'
            ],
            'path' => [
                'type' => 'string',
                'description' => 'File or directory path (relative to allowed directories)'
            ],
            'pattern' => [
                'type' => 'string',
                'description' => 'Search pattern or file glob'
            ],
            'lines' => [
                'type' => 'integer',
                'description' => 'Number of lines to read (head)',
                'default' => 100
            ],
            'tail' => [
                'type' => 'boolean',
                'description' => 'Read from end of file',
                'default' => false
            ],
            'depth' => [
                'type' => 'integer',
                'description' => 'Directory traversal depth',
                'default' => 2
            ]
        ],
        'required' => ['action']
    ];

    private array $allowedPaths = [
        '/home/master/applications/hdgwrzntwa/public_html',
        '/home/master/applications/hdgwrzntwa/private_html'
    ];

    private array $blockedPaths = [
        'vendor',
        'node_modules',
        '.git',
        'cache',
        'sessions'
    ];

    public function execute(array $arguments): array
    {
        $action = $arguments['action'];

        try {
            switch ($action) {
                case 'read':
                    return $this->readFile($arguments);

                case 'search':
                    return $this->searchFiles($arguments);

                case 'list':
                    return $this->listDirectory($arguments);

                case 'analyze':
                    return $this->analyzeFile($arguments['path'] ?? null);

                case 'stats':
                    return $this->getFileStats($arguments['path'] ?? null);

                case 'tree':
                    return $this->getDirectoryTree($arguments);

                default:
                    throw new \Exception("Unknown action: {$action}");
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function validatePath(string $path): string
    {
        // Convert relative to absolute
        if ($path[0] !== '/') {
            $path = $this->allowedPaths[0] . '/' . ltrim($path, '/');
        }

        // Resolve real path (prevents path traversal)
        $realPath = realpath($path);

        if ($realPath === false) {
            throw new \Exception("Path does not exist: {$path}");
        }

        // Check if path is within allowed directories
        $allowed = false;
        foreach ($this->allowedPaths as $allowedPath) {
            if (strpos($realPath, $allowedPath) === 0) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            throw new \Exception("Access denied: Path outside allowed directories");
        }

        // Check for blocked paths
        foreach ($this->blockedPaths as $blocked) {
            if (strpos($realPath, "/{$blocked}/") !== false ||
                substr($realPath, -strlen("/{$blocked}")) === "/{$blocked}") {
                throw new \Exception("Access denied: Blocked path component ({$blocked})");
            }
        }

        return $realPath;
    }

    private function readFile(array $args): array
    {
        $path = $this->validatePath($args['path'] ?? '');

        if (!is_file($path)) {
            throw new \Exception("Not a file: {$path}");
        }

        $lines = $args['lines'] ?? 100;
        $tail = $args['tail'] ?? false;

        $content = file_get_contents($path);
        $lineArray = explode("\n", $content);
        $totalLines = count($lineArray);

        if ($tail) {
            $lineArray = array_slice($lineArray, -$lines);
        } else {
            $lineArray = array_slice($lineArray, 0, $lines);
        }

        return [
            'success' => true,
            'data' => [
                'path' => $path,
                'lines' => implode("\n", $lineArray),
                'total_lines' => $totalLines,
                'displayed_lines' => count($lineArray),
                'tail' => $tail,
                'size' => filesize($path),
                'modified' => date('Y-m-d H:i:s', filemtime($path))
            ]
        ];
    }

    private function searchFiles(array $args): array
    {
        $basePath = $this->validatePath($args['path'] ?? '');
        $pattern = $args['pattern'] ?? '';

        if (!$pattern) {
            throw new \Exception('Search pattern is required');
        }

        if (!is_dir($basePath)) {
            throw new \Exception("Not a directory: {$basePath}");
        }

        $results = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;

            // Skip blocked paths
            $skip = false;
            foreach ($this->blockedPaths as $blocked) {
                if (strpos($file->getPathname(), "/{$blocked}/") !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            // Search in file content
            $content = file_get_contents($file->getPathname());

            if (stripos($content, $pattern) !== false) {
                // Find matching lines
                $lines = explode("\n", $content);
                $matches = [];

                foreach ($lines as $lineNum => $line) {
                    if (stripos($line, $pattern) !== false) {
                        $matches[] = [
                            'line' => $lineNum + 1,
                            'content' => trim($line)
                        ];

                        if (count($matches) >= 5) break; // Limit matches per file
                    }
                }

                $results[] = [
                    'file' => str_replace($this->allowedPaths[0] . '/', '', $file->getPathname()),
                    'matches' => $matches,
                    'match_count' => count($matches)
                ];

                if (count($results) >= 50) break; // Limit total results
            }
        }

        return [
            'success' => true,
            'data' => $results,
            'count' => count($results),
            'pattern' => $pattern
        ];
    }

    private function listDirectory(array $args): array
    {
        $path = $this->validatePath($args['path'] ?? '');

        if (!is_dir($path)) {
            throw new \Exception("Not a directory: {$path}");
        }

        $items = [];
        $dir = new \DirectoryIterator($path);

        foreach ($dir as $item) {
            if ($item->isDot()) continue;

            // Skip blocked paths
            if (in_array($item->getFilename(), $this->blockedPaths)) {
                continue;
            }

            $items[] = [
                'name' => $item->getFilename(),
                'type' => $item->isDir() ? 'directory' : 'file',
                'size' => $item->isFile() ? $item->getSize() : null,
                'modified' => date('Y-m-d H:i:s', $item->getMTime()),
                'permissions' => substr(sprintf('%o', $item->getPerms()), -4)
            ];
        }

        // Sort: directories first, then by name
        usort($items, function($a, $b) {
            if ($a['type'] === $b['type']) {
                return strcasecmp($a['name'], $b['name']);
            }
            return $a['type'] === 'directory' ? -1 : 1;
        });

        return [
            'success' => true,
            'data' => $items,
            'count' => count($items),
            'path' => $path
        ];
    }

    private function analyzeFile(?string $path): array
    {
        $path = $this->validatePath($path ?? '');

        if (!is_file($path)) {
            throw new \Exception("Not a file: {$path}");
        }

        $content = file_get_contents($path);
        $lines = explode("\n", $content);

        $analysis = [
            'path' => str_replace($this->allowedPaths[0] . '/', '', $path),
            'size' => filesize($path),
            'size_human' => $this->formatBytes(filesize($path)),
            'lines' => count($lines),
            'characters' => strlen($content),
            'words' => str_word_count($content),
            'modified' => date('Y-m-d H:i:s', filemtime($path)),
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'mime_type' => mime_content_type($path)
        ];

        // Code-specific analysis
        $ext = strtolower($analysis['extension']);
        if (in_array($ext, ['php', 'js', 'py', 'java', 'cpp', 'c'])) {
            $analysis['code_stats'] = [
                'functions' => preg_match_all('/function\s+\w+\s*\(/', $content),
                'classes' => preg_match_all('/class\s+\w+/', $content),
                'comments' => preg_match_all('/\/\/|\/\*|\*\/|#/', $content),
                'blank_lines' => count(array_filter($lines, function($line) {
                    return trim($line) === '';
                }))
            ];
        }

        return [
            'success' => true,
            'data' => $analysis
        ];
    }

    private function getFileStats(?string $path): array
    {
        $path = $this->validatePath($path ?? '');

        if (is_file($path)) {
            return $this->analyzeFile($path);
        }

        if (!is_dir($path)) {
            throw new \Exception("Invalid path: {$path}");
        }

        // Directory statistics
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'file_types' => [],
            'largest_files' => []
        ];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;

            $stats['total_files']++;
            $stats['total_size'] += $file->getSize();

            $ext = strtolower($file->getExtension());
            if (!isset($stats['file_types'][$ext])) {
                $stats['file_types'][$ext] = 0;
            }
            $stats['file_types'][$ext]++;

            $stats['largest_files'][] = [
                'path' => str_replace($this->allowedPaths[0] . '/', '', $file->getPathname()),
                'size' => $file->getSize()
            ];
        }

        // Sort and limit largest files
        usort($stats['largest_files'], function($a, $b) {
            return $b['size'] <=> $a['size'];
        });
        $stats['largest_files'] = array_slice($stats['largest_files'], 0, 10);

        $stats['total_size_human'] = $this->formatBytes($stats['total_size']);

        return [
            'success' => true,
            'data' => $stats,
            'path' => $path
        ];
    }

    private function getDirectoryTree(array $args): array
    {
        $path = $this->validatePath($args['path'] ?? '');
        $depth = $args['depth'] ?? 2;

        if (!is_dir($path)) {
            throw new \Exception("Not a directory: {$path}");
        }

        $tree = $this->buildTree($path, $depth);

        return [
            'success' => true,
            'data' => $tree,
            'path' => $path
        ];
    }

    private function buildTree(string $path, int $depth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth) {
            return [];
        }

        $tree = [];
        $dir = new \DirectoryIterator($path);

        foreach ($dir as $item) {
            if ($item->isDot() || in_array($item->getFilename(), $this->blockedPaths)) {
                continue;
            }

            $node = [
                'name' => $item->getFilename(),
                'type' => $item->isDir() ? 'directory' : 'file'
            ];

            if ($item->isDir()) {
                $node['children'] = $this->buildTree($item->getPathname(), $depth, $currentDepth + 1);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
