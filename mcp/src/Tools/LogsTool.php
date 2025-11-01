<?php
/**
 * Logs Tool - Log Analysis & Parsing
 *
 * Parse, analyze, and tail application logs with filtering
 */

namespace MCP\Tools;

class LogsTool
{
    protected string $name = 'logs';
    protected string $description = 'Parse, analyze, and tail logs with filtering and error detection';

    protected array $inputSchema = [
        'type' => 'object',
        'properties' => [
            'action' => [
                'type' => 'string',
                'enum' => ['tail', 'search', 'errors', 'stats', 'parse', 'analyze'],
                'description' => 'Log operation to perform'
            ],
            'log_file' => [
                'type' => 'string',
                'description' => 'Log file name (apache, nginx, php, crawler, etc.)',
                'default' => 'php'
            ],
            'lines' => [
                'type' => 'integer',
                'description' => 'Number of lines to read',
                'default' => 100
            ],
            'level' => [
                'type' => 'string',
                'enum' => ['error', 'warning', 'notice', 'info', 'debug', 'all'],
                'description' => 'Log level filter',
                'default' => 'all'
            ],
            'pattern' => [
                'type' => 'string',
                'description' => 'Search pattern'
            ],
            'since' => [
                'type' => 'string',
                'description' => 'Time filter (e.g., "1 hour ago", "2024-01-01")'
            ]
        ],
        'required' => ['action']
    ];

    private array $logFiles = [
        'apache' => '/home/master/applications/hdgwrzntwa/logs/apache_*.error.log',
        'nginx' => '/home/master/applications/hdgwrzntwa/logs/nginx_*.error.log',
        'php' => '/home/master/applications/hdgwrzntwa/public_html/logs/error.log',
        'crawler' => '/home/master/applications/hdgwrzntwa/public_html/logs/crawler.log',
        'mcp' => '/home/master/applications/hdgwrzntwa/public_html/mcp/logs/mcp.log'
    ];

    public function execute(array $arguments): array
    {
        $action = $arguments['action'];

        try {
            switch ($action) {
                case 'tail':
                    return $this->tailLog($arguments);

                case 'search':
                    return $this->searchLog($arguments);

                case 'errors':
                    return $this->getErrors($arguments);

                case 'stats':
                    return $this->getLogStats($arguments);

                case 'parse':
                    return $this->parseLog($arguments);

                case 'analyze':
                    return $this->analyzeLog($arguments);

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

    private function getLogPath(string $logName): string
    {
        if (!isset($this->logFiles[$logName])) {
            throw new \Exception("Unknown log file: {$logName}");
        }

        $pattern = $this->logFiles[$logName];

        // If pattern has wildcard, get the most recent file
        if (strpos($pattern, '*') !== false) {
            $files = glob($pattern);
            if (empty($files)) {
                throw new \Exception("No log files found matching: {$pattern}");
            }
            // Sort by modification time, newest first
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            return $files[0];
        }

        if (!file_exists($pattern)) {
            throw new \Exception("Log file not found: {$pattern}");
        }

        return $pattern;
    }

    private function tailLog(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');
        $lines = $args['lines'] ?? 100;
        $level = $args['level'] ?? 'all';

        // Read last N lines
        $content = $this->readLastLines($logFile, $lines * 2); // Read more to filter
        $logLines = explode("\n", $content);

        // Filter by level
        if ($level !== 'all') {
            $logLines = array_filter($logLines, function($line) use ($level) {
                return stripos($line, "[$level]") !== false ||
                       stripos($line, $level) !== false;
            });
        }

        // Limit to requested number
        $logLines = array_slice($logLines, -$lines);

        return [
            'success' => true,
            'data' => [
                'log_file' => basename($logFile),
                'lines' => array_values($logLines),
                'count' => count($logLines),
                'level' => $level
            ]
        ];
    }

    private function searchLog(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');
        $pattern = $args['pattern'] ?? '';
        $lines = $args['lines'] ?? 100;

        if (!$pattern) {
            throw new \Exception('Search pattern is required');
        }

        $content = file_get_contents($logFile);
        $logLines = explode("\n", $content);

        $matches = [];
        foreach ($logLines as $lineNum => $line) {
            if (stripos($line, $pattern) !== false) {
                $matches[] = [
                    'line' => $lineNum + 1,
                    'content' => $line,
                    'timestamp' => $this->extractTimestamp($line)
                ];

                if (count($matches) >= $lines) break;
            }
        }

        return [
            'success' => true,
            'data' => [
                'log_file' => basename($logFile),
                'matches' => $matches,
                'count' => count($matches),
                'pattern' => $pattern
            ]
        ];
    }

    private function getErrors(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');
        $lines = $args['lines'] ?? 100;

        $content = $this->readLastLines($logFile, $lines * 3);
        $logLines = explode("\n", $content);

        $errors = [];
        $errorPatterns = [
            'fatal' => '/\[.*fatal.*\]/i',
            'error' => '/\[.*error.*\]/i',
            'warning' => '/\[.*warning.*\]/i',
            'exception' => '/exception/i',
            'failed' => '/failed/i'
        ];

        foreach ($logLines as $lineNum => $line) {
            foreach ($errorPatterns as $type => $pattern) {
                if (preg_match($pattern, $line)) {
                    $errors[] = [
                        'line' => $lineNum + 1,
                        'type' => $type,
                        'content' => $line,
                        'timestamp' => $this->extractTimestamp($line)
                    ];

                    if (count($errors) >= $lines) break 2;
                }
            }
        }

        // Group by error type
        $grouped = [];
        foreach ($errors as $error) {
            $type = $error['type'];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $error;
        }

        return [
            'success' => true,
            'data' => [
                'log_file' => basename($logFile),
                'errors' => $errors,
                'grouped' => $grouped,
                'total' => count($errors),
                'by_type' => array_map('count', $grouped)
            ]
        ];
    }

    private function getLogStats(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');

        $size = filesize($logFile);
        $lines = count(file($logFile));

        // Count errors by type
        $content = file_get_contents($logFile);

        $stats = [
            'file' => basename($logFile),
            'size' => $size,
            'size_human' => $this->formatBytes($size),
            'total_lines' => $lines,
            'modified' => date('Y-m-d H:i:s', filemtime($logFile)),
            'counts' => [
                'fatal' => substr_count(strtolower($content), 'fatal'),
                'error' => substr_count(strtolower($content), '[error]'),
                'warning' => substr_count(strtolower($content), 'warning'),
                'notice' => substr_count(strtolower($content), 'notice'),
                'exception' => substr_count(strtolower($content), 'exception')
            ]
        ];

        return [
            'success' => true,
            'data' => $stats
        ];
    }

    private function parseLog(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');
        $lines = $args['lines'] ?? 100;

        $content = $this->readLastLines($logFile, $lines);
        $logLines = explode("\n", $content);

        $parsed = [];
        foreach ($logLines as $line) {
            if (trim($line) === '') continue;

            $entry = $this->parseLogLine($line);
            if ($entry) {
                $parsed[] = $entry;
            }
        }

        return [
            'success' => true,
            'data' => [
                'log_file' => basename($logFile),
                'entries' => $parsed,
                'count' => count($parsed)
            ]
        ];
    }

    private function analyzeLog(array $args): array
    {
        $logFile = $this->getLogPath($args['log_file'] ?? 'php');

        $content = file_get_contents($logFile);
        $lines = explode("\n", $content);

        // Analyze patterns
        $analysis = [
            'total_lines' => count($lines),
            'error_density' => 0,
            'most_common_errors' => [],
            'error_timeline' => [],
            'recommendations' => []
        ];

        // Count error types
        $errorTypes = [];
        $errorMessages = [];

        foreach ($lines as $line) {
            if (trim($line) === '') continue;

            // Detect error level
            if (preg_match('/\[(fatal|error|warning|notice)\]/i', $line, $matches)) {
                $level = strtolower($matches[1]);
                if (!isset($errorTypes[$level])) {
                    $errorTypes[$level] = 0;
                }
                $errorTypes[$level]++;

                // Extract error message
                if (preg_match('/\]\s+(.+?)(?:\sin\s|$)/', $line, $msgMatches)) {
                    $msg = trim($msgMatches[1]);
                    if (!isset($errorMessages[$msg])) {
                        $errorMessages[$msg] = 0;
                    }
                    $errorMessages[$msg]++;
                }
            }
        }

        $analysis['error_types'] = $errorTypes;
        $analysis['error_density'] = round((array_sum($errorTypes) / count($lines)) * 100, 2);

        // Most common errors
        arsort($errorMessages);
        $analysis['most_common_errors'] = array_slice($errorMessages, 0, 10, true);

        // Recommendations
        if (isset($errorTypes['fatal']) && $errorTypes['fatal'] > 0) {
            $analysis['recommendations'][] = "⚠️ Fatal errors detected ({$errorTypes['fatal']}) - requires immediate attention";
        }
        if ($analysis['error_density'] > 10) {
            $analysis['recommendations'][] = "⚠️ High error density ({$analysis['error_density']}%) - investigate root causes";
        }
        if (isset($errorTypes['warning']) && $errorTypes['warning'] > 100) {
            $analysis['recommendations'][] = "ℹ️ Many warnings ({$errorTypes['warning']}) - consider addressing to improve code quality";
        }

        return [
            'success' => true,
            'data' => $analysis
        ];
    }

    private function parseLogLine(string $line): ?array
    {
        // Try to extract timestamp, level, and message
        $pattern = '/\[([^\]]+)\]\s+\[([^\]]+)\]\s+(.+)/';

        if (preg_match($pattern, $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'message' => trim($matches[3])
            ];
        }

        // Fallback: just return the line
        return [
            'timestamp' => $this->extractTimestamp($line),
            'level' => 'unknown',
            'message' => trim($line)
        ];
    }

    private function extractTimestamp(string $line): ?string
    {
        // Common log timestamp formats
        $patterns = [
            '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/',
            '/\[(\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2})\]/',
            '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    private function readLastLines(string $file, int $lines): string
    {
        $handle = fopen($file, 'r');
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];

        while ($linecounter > 0) {
            $t = ' ';
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) break;
        }

        fclose($handle);
        return implode('', array_reverse($text));
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}
