<?php
/**
 * AI Agent Security Middleware
 * 
 * Provides authentication, authorization, rate limiting, and input validation
 * for all AI agent API endpoints.
 * 
 * @package CIS_Neural_AI_Agent
 * @author Pearce Stephens - Ecigdis Limited
 * @version 1.0.0
 * @date 2025-10-10
 */

declare(strict_types=1);

namespace App\Middleware;

use App\RedisClient;
use App\Logger;

class AISecurityMiddleware
{
    private array $config;
    private ?RedisClient $redis;
    
    /**
     * Configuration defaults
     */
    private const DEFAULTS = [
        'require_auth' => true,
        'require_ai_permission' => true,
        'rate_limit_enabled' => true,
        'rate_limit_requests' => 60,      // Max requests per minute
        'rate_limit_window' => 60,        // Window in seconds
        'max_payload_size' => 1048576,    // 1MB
        'allowed_origins' => [
            'https://staff.vapeshed.co.nz',
            'https://www.vapeshed.co.nz'
        ],
        'log_requests' => true,
        'anonymize_ip' => true
    ];
    
    public function __construct(array $config = [])
    {
        $this->config = array_merge(self::DEFAULTS, $config);
        $this->redis = $this->config['rate_limit_enabled'] ? RedisClient::getInstance() : null;
    }
    
    /**
     * Main security gate - call this first in every AI endpoint
     * 
     * @param array $allowedActions List of valid action names for this endpoint
     * @return array Validated request data
     * @throws SecurityException on any security violation
     */
    public function validateRequest(array $allowedActions = []): array
    {
        // 1. Validate HTTP method
        $this->validateMethod();
        
        // 2. Validate CORS
        $this->validateCors();
        
        // 3. Parse and validate payload
        $data = $this->parsePayload();
        
        // 4. Authenticate user
        $user = $this->authenticateUser();
        
        // 5. Check AI access permission
        if ($this->config['require_ai_permission']) {
            $this->checkAIPermission($user['id']);
        }
        
        // 6. Rate limiting
        if ($this->config['rate_limit_enabled']) {
            $this->checkRateLimit($user['id']);
        }
        
        // 7. Validate action if list provided
        if (!empty($allowedActions)) {
            $this->validateAction($data['action'] ?? '', $allowedActions);
        }
        
        // 8. Log request
        if ($this->config['log_requests']) {
            $this->logRequest($user, $data);
        }
        
        return [
            'user' => $user,
            'data' => $data,
            'request_id' => $this->generateRequestId()
        ];
    }
    
    /**
     * Validate HTTP method (POST only for AI operations)
     */
    private function validateMethod(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError(405, 'METHOD_NOT_ALLOWED', 'Only POST requests accepted');
        }
    }
    
    /**
     * Validate CORS origin
     */
    private function validateCors(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (!empty($origin)) {
            if (in_array($origin, $this->config['allowed_origins'], true)) {
                header('Access-Control-Allow-Origin: ' . $origin);
                header('Access-Control-Allow-Credentials: true');
            } else {
                $this->sendError(403, 'ORIGIN_NOT_ALLOWED', 'Origin not in allowed list');
            }
        }
        
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    }
    
    /**
     * Parse and validate JSON payload
     */
    private function parsePayload(): array
    {
        $input = file_get_contents('php://input');
        
        // Check payload size
        if (strlen($input) > $this->config['max_payload_size']) {
            $this->sendError(413, 'PAYLOAD_TOO_LARGE', 
                'Request must be under ' . $this->formatBytes($this->config['max_payload_size']));
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError(400, 'INVALID_JSON', 'Invalid JSON payload: ' . json_last_error_msg());
        }
        
        return $data;
    }
    
    /**
     * Authenticate user session
     */
    private function authenticateUser(): array
    {
        if (!$this->config['require_auth']) {
            return ['id' => 0, 'name' => 'Anonymous'];
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['user_id']) || empty($_SESSION['is_authenticated'])) {
            $this->sendError(401, 'AUTH_REQUIRED', 'Authentication required');
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? 'Unknown',
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
    
    /**
     * Check if user has AI access permission
     */
    private function checkAIPermission(int $userId): void
    {
        // Check database for AI access flag
        $stmt = fetch("SELECT ai_access, role FROM users WHERE id = ? AND active = 1", [$userId]);
        
        if (empty($stmt)) {
            $this->sendError(403, 'USER_NOT_FOUND', 'User account not found or inactive');
        }
        
        $user = $stmt[0];
        
        // Admin always has access
        if ($user['role'] === 'admin' || $user['role'] === 'super_admin') {
            return;
        }
        
        // Check specific AI permission
        if (empty($user['ai_access']) || $user['ai_access'] != 1) {
            $this->sendError(403, 'AI_ACCESS_DENIED', 'AI features not authorized for this account');
        }
    }
    
    /**
     * Rate limiting check
     */
    private function checkRateLimit(int $userId): void
    {
        if (!$this->redis) {
            return;
        }
        
        $key = 'ai_rate_limit:user:' . $userId;
        $requests = (int)$this->redis->get($key);
        
        if ($requests >= $this->config['rate_limit_requests']) {
            $ttl = $this->redis->ttl($key);
            $this->sendError(429, 'RATE_LIMIT_EXCEEDED', 
                'Too many requests. Max ' . $this->config['rate_limit_requests'] . '/minute.', 
                ['retry_after' => max(1, $ttl)]);
        }
        
        $this->redis->incr($key);
        $this->redis->expire($key, $this->config['rate_limit_window']);
        
        // Set rate limit headers
        header('X-RateLimit-Limit: ' . $this->config['rate_limit_requests']);
        header('X-RateLimit-Remaining: ' . ($this->config['rate_limit_requests'] - $requests - 1));
        header('X-RateLimit-Reset: ' . (time() + $this->redis->ttl($key)));
    }
    
    /**
     * Validate action against allowed list
     */
    private function validateAction(string $action, array $allowedActions): void
    {
        if (empty($action)) {
            $this->sendError(400, 'ACTION_REQUIRED', 'Action parameter is required', 
                ['allowed_actions' => $allowedActions]);
        }
        
        if (!in_array($action, $allowedActions, true)) {
            $this->sendError(400, 'INVALID_ACTION', 'Action not recognized', 
                ['allowed_actions' => $allowedActions]);
        }
    }
    
    /**
     * Log request for audit trail
     */
    private function logRequest(array $user, array $data): void
    {
        $logData = [
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'action' => $data['action'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
        ];
        
        // Anonymize IP if configured
        if ($this->config['anonymize_ip']) {
            $logData['ip_hash'] = $this->hashIp($_SERVER['REMOTE_ADDR'] ?? '');
        } else {
            $logData['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
        
        Logger::info('AI API Request', $logData);
    }
    
    /**
     * Hash IP address for privacy
     */
    private function hashIp(string $ip): string
    {
        $salt = getenv('IP_HASH_SALT') ?: 'default_salt_change_me';
        return hash('sha256', $ip . $salt);
    }
    
    /**
     * Generate unique request ID
     */
    private function generateRequestId(): string
    {
        return uniqid('ai_req_', true);
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . 'MB';
        }
        return round($bytes / 1024, 2) . 'KB';
    }
    
    /**
     * Send standardized error response and exit
     */
    private function sendError(int $httpCode, string $errorCode, string $message, array $extra = []): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'http_code' => $httpCode
            ],
            'timestamp' => date('c')
        ];
        
        if (!empty($extra)) {
            $response['error'] = array_merge($response['error'], $extra);
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send standardized success response
     */
    public static function sendSuccess(array $data, string $requestId = ''): void
    {
        http_response_code(200);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ];
        
        if ($requestId) {
            $response['request_id'] = $requestId;
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
}
