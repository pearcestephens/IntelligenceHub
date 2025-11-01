<?php
/**
 * API Authentication - Bearer token and rate limiting
 * 
 * Handles Bearer token authentication, API key validation,
 * rate limiting using Redis, and CSRF protection for APIs.
 * 
 * @author Gate 4 Implementation
 * @version 1.0.0
 */

declare(strict_types=1);

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access forbidden');
}

require_once __DIR__ . '/bootstrap.php';

class ApiAuth
{
    private Config $config;
    private RedisClient $redis;
    private Logger $logger;
    
    public function __construct()
    {
        $this->config = ApiBootstrap::getConfig();
        $this->redis = ApiBootstrap::getRedis();
        $this->logger = ApiBootstrap::getLogger();
    }
    
    /**
     * Authenticate API request
     */
    public function authenticate(): ?string
    {
        // Get authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!$authHeader) {
            ApiBootstrap::error('Authorization header required', 401, 'auth_required');
            return null;
        }
        
        // Parse Bearer token
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            ApiBootstrap::error('Invalid authorization format. Use: Bearer <token>', 401, 'auth_invalid');
            return null;
        }
        
        $apiKey = $matches[1];
        
        // Validate API key
        if (!$this->validateApiKey($apiKey)) {
            ApiBootstrap::error('Invalid API key', 401, 'auth_invalid');
            return null;
        }
        
        // Check rate limits
        $this->checkRateLimit($apiKey);
        
        return $apiKey;
    }
    
    /**
     * Validate API key
     */
    private function validateApiKey(string $apiKey): bool
    {
        // Get valid API keys from config
        $validKeys = $this->getValidApiKeys();
        
        return in_array($apiKey, $validKeys, true);
    }
    
    /**
     * Get valid API keys from config
     */
    private function getValidApiKeys(): array
    {
        $keys = $this->config->get('api.keys', '');
        
        if (empty($keys)) {
            // Fallback to environment
            $keys = $_ENV['API_KEYS'] ?? '';
        }
        
        if (empty($keys)) {
            return [];
        }
        
        return array_map('trim', explode(',', $keys));
    }
    
    /**
     * Check rate limits using Redis token bucket
     */
    private function checkRateLimit(string $apiKey): void
    {
        $route = $this->getCurrentRoute();
        $window = 60; // 1 minute window
        $maxRequests = $this->getRateLimitForRoute($route);
        
        if ($maxRequests <= 0) {
            return; // No rate limiting
        }
        
        $key = "rl:{$apiKey}:{$route}";
        $current = $this->redis->incr($key);
        
        if ($current === 1) {
            $this->redis->expire($key, $window);
        }
        
        if ($current > $maxRequests) {
            $this->logger->warning('Rate limit exceeded', [
                'api_key' => substr($apiKey, 0, 8) . '...',
                'route' => $route,
                'current' => $current,
                'max' => $maxRequests,
                'request_id' => ApiBootstrap::getRequestId(),
            ]);
            
            ApiBootstrap::error(
                'Rate limit exceeded. Try again later.',
                429,
                'rate_limit_exceeded',
                [
                    'max_requests' => $maxRequests,
                    'window_seconds' => $window,
                ]
            );
        }
    }
    
    /**
     * Get current route for rate limiting
     */
    private function getCurrentRoute(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Normalize route for rate limiting
        $route = preg_replace('/\/[a-f0-9-]{36}/', '/{id}', $path);
        
        return $route;
    }
    
    /**
     * Get rate limit for specific route
     */
    private function getRateLimitForRoute(string $route): int
    {
        $limits = [
            '/agent/api/v1/conversations' => 100,
            '/agent/api/v1/conversations/{id}' => 200,
            '/agent/api/v1/messages' => 100,
            '/agent/api/v1/conversations/{id}/messages' => 200,
            '/agent/api/v1/tool-calls' => 100,
            '/agent/api/v1/tool-calls/{id}' => 200,
            '/cis/ls/jobs' => 50,
            '/cis/ls/jobs/claim' => 200,
            '/cis/ls/jobs/{id}/heartbeat' => 500,
            '/cis/ls/jobs/{id}/complete' => 200,
            '/cis/ls/jobs/{id}/fail' => 200,
            '/cis/ls/jobs/{id}/log' => 300,
            '/cis/ls/jobs/{id}' => 200,
        ];
        
        return $limits[$route] ?? 100; // Default limit
    }
    
    /**
     * Check CSRF token for session-based requests
     */
    public function checkCsrf(): void
    {
        // Only check CSRF for session-based requests (not Bearer token)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($authHeader) {
            return; // Skip CSRF for Bearer token requests
        }
        
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return; // Skip CSRF for safe methods
        }
        
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!$this->validateCsrfToken($token)) {
            ApiBootstrap::error('CSRF token mismatch', 403, 'csrf_error');
        }
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCsrfToken(string $token): bool
    {
        session_start();
        $expected = $_SESSION['_token'] ?? '';
        
        return hash_equals($expected, $token);
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        session_start();
        
        if (empty($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_token'];
    }
    
    /**
     * Check idempotency key and return cached response if exists
     */
    public function checkIdempotency(string $scope): ?array
    {
        $key = $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? '';
        
        if (empty($key)) {
            return null; // No idempotency key provided
        }
        
        // Check database for existing response
        $db = ApiBootstrap::getDb();
        $stmt = $db->prepare('
            SELECT response_code, response_body 
            FROM api_idempotency 
            WHERE scope = ? AND idempotency_key = ?
        ');
        
        $result = $stmt->execute([$scope, $key]);
        
        if ($row = $result->fetch()) {
            // Return cached response
            http_response_code((int)$row['response_code']);
            echo $row['response_body'];
            exit;
        }
        
        return ['scope' => $scope, 'key' => $key];
    }
    
    /**
     * Store idempotent response
     */
    public function storeIdempotentResponse(string $scope, string $key, int $code, string $body): void
    {
        $db = ApiBootstrap::getDb();
        
        try {
            $stmt = $db->prepare('
                INSERT INTO api_idempotency (scope, idempotency_key, response_code, response_body)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    response_code = VALUES(response_code),
                    response_body = VALUES(response_body)
            ');
            
            $stmt->execute([$scope, $key, $code, $body]);
        } catch (Exception $e) {
            // Log but don't fail the request
            $this->logger->error('Failed to store idempotent response', [
                'scope' => $scope,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
        }
    }
}