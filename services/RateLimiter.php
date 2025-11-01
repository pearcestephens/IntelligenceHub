<?php
/**
 * Rate Limiting Service
 * Automatically added by Security Hardening Script
 */
declare(strict_types=1);

class RateLimiter {
    private static array $limits = [
        'api' => ['requests' => 100, 'window' => 3600], // 100 per hour
        'login' => ['requests' => 5, 'window' => 900],  // 5 per 15 min
        'default' => ['requests' => 200, 'window' => 3600] // 200 per hour
    ];
    
    private static string $storePath = '/tmp/rate_limits/';
    
    public static function check(string $identifier, string $type = 'default'): bool {
        $limit = self::$limits[$type] ?? self::$limits['default'];
        $key = hash('sha256', $identifier . $type);
        $file = self::$storePath . $key;
        
        // Ensure directory exists
        if (!is_dir(self::$storePath)) {
            mkdir(self::$storePath, 0750, true);
        }
        
        $now = time();
        $windowStart = $now - $limit['window'];
        
        // Read existing data
        $requests = [];
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $requests = json_decode($data, true) ?: [];
        }
        
        // Filter out old requests
        $requests = array_filter($requests, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $limit['requests']) {
            return false;
        }
        
        // Add current request
        $requests[] = $now;
        file_put_contents($file, json_encode($requests), LOCK_EX);
        
        return true;
    }
    
    public static function enforceLimit(string $identifier, string $type = 'default'): void {
        if (!self::check($identifier, $type)) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: 3600');
            echo json_encode([
                'success' => false,
                'error' => 'Rate limit exceeded',
                'code' => 'RATE_LIMIT_EXCEEDED'
            ]);
            exit;
        }
    }
    
    public static function getClientIdentifier(): string {
        // Use IP + User Agent for identifier
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        return hash('sha256', $ip . $userAgent);
    }
}
