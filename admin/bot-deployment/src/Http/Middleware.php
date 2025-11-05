<?php
/**
 * API Middleware
 *
 * Request middleware for authentication, rate limiting, and logging
 *
 * @package BotDeployment\Http
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Http;

use BotDeployment\Config\Config;

class Middleware
{
    private Config $config;
    private static array $requestLog = [];

    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    /**
     * Authenticate API request
     */
    public function authenticate(Request $request): bool
    {
        // Check if authentication is required
        if (!$this->config->get('security.apiAuthRequired', true)) {
            return true;
        }

        // Get API key from header
        $apiKey = $request->header('x-api-key') ?? $request->header('authorization');

        // Remove "Bearer " prefix if present
        if ($apiKey && strpos($apiKey, 'Bearer ') === 0) {
            $apiKey = substr($apiKey, 7);
        }

        // Validate API key
        $validKeys = $this->config->get('security.apiKeys', []);

        if (empty($validKeys)) {
            // No keys configured, allow all (development mode)
            return true;
        }

        return in_array($apiKey, $validKeys, true);
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(Request $request): bool
    {
        $maxRequests = $this->config->get('security.rateLimitRequests', 100);
        $timeWindow = $this->config->get('security.rateLimitWindow', 60);
        $identifier = $this->getRateLimitIdentifier($request);

        // Clean old requests
        $cutoff = time() - $timeWindow;
        self::$requestLog[$identifier] = array_filter(
            self::$requestLog[$identifier] ?? [],
            fn($timestamp) => $timestamp > $cutoff
        );

        // Check limit
        $requestCount = count(self::$requestLog[$identifier] ?? []);

        if ($requestCount >= $maxRequests) {
            return false;
        }

        // Log this request
        self::$requestLog[$identifier][] = time();

        return true;
    }

    /**
     * Get rate limit identifier (IP + API key)
     */
    private function getRateLimitIdentifier(Request $request): string
    {
        $ip = $request->ip();
        $apiKey = $request->header('x-api-key', 'anonymous');

        return md5($ip . ':' . $apiKey);
    }

    /**
     * Log request
     */
    public function logRequest(Request $request): void
    {
        if (!$this->config->get('logging.enabled', true)) {
            return;
        }

        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'uri' => $request->uri(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ];

        // Log to file or database
        $logFile = $this->config->get('logging.path', '/tmp') . '/api-access.log';
        $logLine = json_encode($logData) . PHP_EOL;

        @file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Validate CORS
     */
    public function validateCors(Request $request): void
    {
        $allowedOrigins = $this->config->get('security.corsOrigins', ['*']);
        $origin = $request->header('origin', '');

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
            header('Access-Control-Max-Age: 86400');
        }
    }

    /**
     * Sanitize input
     */
    public function sanitizeInput(Request $request): void
    {
        // This is handled by Request class validation
        // Additional sanitization can be added here if needed
    }
}
