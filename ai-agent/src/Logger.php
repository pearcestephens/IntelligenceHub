<?php

/**
 * JSON Logger with structured context support
 * Provides correlation ID tracking and Cloudways-compatible logging
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

class Logger
{
    /**
     * When Monolog is present we wrap it; otherwise we fallback to an internal lightweight logger.
     */
    private static $instance = null; // \Monolog\Logger | FallbackLogger | null
    private static bool $fallback = false;
    private static array $context = [];

    /**
     * Initialize logger
     */
    public static function initialize(): void
    {
        if (self::$instance !== null) {
            return;
        }

        // If Monolog core class missing, use fallback
        if (!class_exists('Monolog\\Logger')) {
            self::$fallback = true;
            self::$instance = new class {
                public function log(string $level, string $message, array $context = []): void
                {
                    $record = [
                        'ts' => date('c'),
                        'level' => $level,
                        'msg' => $message,
                        'context' => $context,
                    ];
                    // Write JSON line to stderr
                    file_put_contents('php://stderr', json_encode($record, JSON_UNESCAPED_UNICODE) . "\n");
                }
                public function debug($m, $c = [])
                {
                    $this->log('debug', $m, $c);
                }
                public function info($m, $c = [])
                {
                    $this->log('info', $m, $c);
                }
                public function warning($m, $c = [])
                {
                    $this->log('warning', $m, $c);
                }
                public function error($m, $c = [])
                {
                    $this->log('error', $m, $c);
                }
                public function critical($m, $c = [])
                {
                    $this->log('critical', $m, $c);
                }
            };
            // Early exit after fallback setup
            return;
        }

        // Monolog path
        self::$instance = new \Monolog\Logger('ai-agent');
        $formatter = new \Monolog\Formatter\JsonFormatter();
        $level = self::getLogLevel();
        if (class_exists('Monolog\\Level') && is_string($level)) {
            // For Monolog v3 when string like 'info'
            // Map string to proper Monolog\Level enum
            $levelMap = [
                'debug' => \Monolog\Level::Debug,
                'info' => \Monolog\Level::Info,
                'warning' => \Monolog\Level::Warning,
                'error' => \Monolog\Level::Error,
                'critical' => \Monolog\Level::Critical,
            ];
            $monologLevel = $levelMap[strtolower($level)] ?? \Monolog\Level::Info;
            $streamHandler = new \Monolog\Handler\StreamHandler('php://stderr', $monologLevel);
        } elseif (class_exists('Monolog\\Level') && $level instanceof \Monolog\Level) {
            $streamHandler = new \Monolog\Handler\StreamHandler('php://stderr', $level);
        } else {
            // Legacy int level mapping
            $streamHandler = new \Monolog\Handler\StreamHandler('php://stderr', $level);
        }
        $streamHandler->setFormatter($formatter);
        self::$instance->pushHandler($streamHandler);
        self::$instance->pushProcessor(function ($record) {
            $record['extra']['request_id'] = $_SERVER['REQUEST_ID'] ?? 'unknown';
            $record['extra']['conversation_id'] = self::$context['conversation_id'] ?? null;
            $record['extra']['user_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $record['extra']['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $record['extra']['timestamp'] = date('c');
            if (!empty(self::$context)) {
                $record['extra'] = array_merge($record['extra'], self::$context);
            }
            return $record;
        });
    }

    /**
     * Set conversation context
     */
    public static function setContext(array $context): void
    {
        self::$context = array_merge(self::$context, $context);
    }

    /**
     * Clear context
     */
    public static function clearContext(): void
    {
        self::$context = [];
    }

    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::ensureInitialized();
        self::dispatch('debug', $message, $context);
    }

    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::ensureInitialized();
        self::dispatch('info', $message, $context);
    }

    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::ensureInitialized();
        self::dispatch('warning', $message, $context);
    }

    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::ensureInitialized();
        self::dispatch('error', $message, $context);
    }

    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        self::ensureInitialized();
        self::dispatch('critical', $message, $context);
    }

    /**
     * Log tool execution
     */
    public static function logTool(string $toolName, array $args, ?array $result, int $durationMs, bool $success): void
    {
        self::info('Tool executed', [
            'tool_name' => $toolName,
            'args' => self::sanitizeForLog($args),
            'result' => $success ? 'success' : 'error',
            'duration_ms' => $durationMs,
            'success' => $success
        ]);
    }

    /**
     * Log OpenAI API call
     */
    public static function logOpenAI(string $endpoint, array $request, ?array $response, int $durationMs, bool $success): void
    {
        $requestData = self::sanitizeForLog($request);

        self::info('OpenAI API call', [
            'endpoint' => $endpoint,
            'request_size' => strlen(json_encode($requestData)),
            'response_size' => $response ? strlen(json_encode($response)) : 0,
            'duration_ms' => $durationMs,
            'success' => $success,
            'tokens_input' => $response['usage']['prompt_tokens'] ?? null,
            'tokens_output' => $response['usage']['completion_tokens'] ?? null
        ]);
    }

    /**
     * Ensure logger is initialized
     */
    private static function ensureInitialized(): void
    {
        if (self::$instance === null) {
            self::initialize();
        }
    }

    /**
     * Get log level from config
     */
    private static function getLogLevel(): mixed
    {
        // If Monolog missing, just return string level for fallback logs
        $level = method_exists(Config::class, 'get') ? Config::get('LOG_LEVEL', 'info') : 'info';
        if (!class_exists('Monolog\\Logger')) {
            return strtolower((string)$level);
        }
        $mapped = match (strtolower((string)$level)) {
            'debug' => \Monolog\Logger::DEBUG,
            'info' => \Monolog\Logger::INFO,
            'warning' => \Monolog\Logger::WARNING,
            'error' => \Monolog\Logger::ERROR,
            'critical' => \Monolog\Logger::CRITICAL,
            default => \Monolog\Logger::INFO
        };
        if (class_exists('Monolog\\Level')) {
            return match ($mapped) {
                \Monolog\Logger::DEBUG => 'debug',
                \Monolog\Logger::INFO => 'info',
                \Monolog\Logger::WARNING => 'warning',
                \Monolog\Logger::ERROR => 'error',
                \Monolog\Logger::CRITICAL => 'critical',
                default => 'info'
            };
        }
        return $mapped;
    }

    /**
     * Dispatch a log entry either to Monolog instance or fallback.
     */
    private static function dispatch(string $level, string $message, array $context): void
    {
        if (self::$fallback) {
            // Merge context + static context
            $merged = array_merge(self::$context, $context, [
                'request_id' => $_SERVER['REQUEST_ID'] ?? 'unknown',
                'timestamp' => date('c')
            ]);
            self::$instance->{$level}($message, $merged);
            return;
        }
        // Monolog path
        self::$instance->{$level}($message, $context);
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     */
    public static function sanitizeForLog(array $data): array
    {
        $sensitive = [
            'password',
            'api_key',
            'token',
            'secret',
            'authorization',
            'cookie',
            'session'
        ];

        array_walk_recursive($data, function (&$value, $key) use ($sensitive) {
            if (is_string($key) && in_array(strtolower($key), $sensitive, true)) {
                $value = '[REDACTED]';
            } elseif (is_string($value) && strlen($value) > 1000) {
                $value = substr($value, 0, 997) . '...';
            }
        });

        return $data;
    }
}
