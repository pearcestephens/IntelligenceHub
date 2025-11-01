<?php
/**
 * Security Monitoring Service
 * Automatically added by Security Hardening Script
 */
declare(strict_types=1);

class SecurityMonitor {
    private static string $logFile = 'logs/security.log';
    
    public static function logSecurityEvent(string $event, string $level = 'INFO', array $context = []): void {
        $logPath = $_SERVER['DOCUMENT_ROOT'] . '/' . self::$logFile;
        $logDir = dirname($logPath);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'event' => $event,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'uri' => $uri,
            'context' => $context
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public static function detectSuspiciousActivity(string $ip, string $userAgent): bool {
        // Simple heuristics for suspicious activity
        $suspicious = false;
        
        // Check for common attack patterns in user agent
        $maliciousPatterns = [
            'sqlmap', 'nikto', 'nmap', 'masscan', 'nessus',
            'burp', 'metasploit', 'beef', 'havij', 'pangolin'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                $suspicious = true;
                break;
            }
        }
        
        // Check for too many requests from same IP
        $recentAttempts = self::getRecentAttempts($ip);
        if ($recentAttempts > 50) { // 50 requests in last hour
            $suspicious = true;
        }
        
        if ($suspicious) {
            self::logSecurityEvent('Suspicious activity detected', 'WARNING', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'recent_attempts' => $recentAttempts ?? 0
            ]);
        }
        
        return $suspicious;
    }
    
    private static function getRecentAttempts(string $ip): int {
        $logPath = $_SERVER['DOCUMENT_ROOT'] . '/' . self::$logFile;
        if (!file_exists($logPath)) return 0;
        
        $oneHourAgo = time() - 3600;
        $count = 0;
        
        $handle = fopen($logPath, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && isset($entry['ip']) && $entry['ip'] === $ip) {
                    $logTime = strtotime($entry['timestamp']);
                    if ($logTime > $oneHourAgo) {
                        $count++;
                    }
                }
            }
            fclose($handle);
        }
        
        return $count;
    }
}
