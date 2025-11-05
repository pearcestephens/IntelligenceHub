<?php

namespace IntelligenceHub\MCP\Tools;

class LogsTools extends BaseTool {
    private array $logPaths;

    public function __construct() {
        $this->logPaths = [
            'apache_error' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log.1',
            'apache_access' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/apache_phpstack-129337-5615757.cloudwaysapps.com.access.log.1',
            'nginx_error' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/nginx_phpstack-129337-5615757.cloudwaysapps.com.error.log.1',
            'nginx_access' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log.1',
            'php_error' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/php-app.access.log.1',
            'php_slow' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/php-app.slow.log.1'
        ];
    }

    public function getName(): string {
        return 'logs';
    }

    public function getSchema(): array {
        return [
            'logs.tail' => [
                'description' => 'Tail log files',
                'parameters' => [
                    'file' => ['type' => 'string', 'required' => true],
                    'lines' => ['type' => 'integer', 'required' => false]
                ]
            ]
        ];
    }

    public function execute(array $args): array {
        $method = $args['_method'] ?? 'tail';

        switch ($method) {
            case 'tail':
                return $this->tail($args);
            default:
                return $this->fail("Unknown method: $method");
        }
    }

    private function tail(array $args): array {
        $fileKey = $args['file'] ?? '';
        $lines = max(1, min(1000, (int)($args['lines'] ?? 100)));

        if (empty($fileKey)) {
            return $this->fail('file parameter is required. Available: ' . implode(', ', array_keys($this->logPaths)));
        }

        // Check if it's a predefined log
        if (isset($this->logPaths[$fileKey])) {
            $filePath = $this->logPaths[$fileKey];
        } else {
            return $this->fail("Unknown log file: $fileKey. Available: " . implode(', ', array_keys($this->logPaths)));
        }

        if (!file_exists($filePath)) {
            return $this->fail("Log file not found: $filePath");
        }

        if (!is_readable($filePath)) {
            return $this->fail("Log file not readable: $filePath");
        }

        // Read last N lines efficiently
        $result = $this->readLastLines($filePath, $lines);

        return $this->ok([
            'file' => $fileKey,
            'path' => $filePath,
            'lines' => $result,
            'line_count' => count($result),
            'size' => filesize($filePath),
            'modified' => date('Y-m-d H:i:s', filemtime($filePath))
        ]);
    }

    private function readLastLines(string $filePath, int $numLines): array {
        $lines = [];
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        $startLine = max(0, $lastLine - $numLines);

        $file->seek($startLine);
        while (!$file->eof()) {
            $line = trim($file->fgets());
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return array_slice($lines, -$numLines);
    }
}
