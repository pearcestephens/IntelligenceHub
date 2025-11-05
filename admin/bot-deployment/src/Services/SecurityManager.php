<?php

namespace BotDeployment\Services;

use PDO;
use Exception;

/**
 * SecurityManager - Security & Rate Limiting Service
 *
 * Features:
 * - Rate limiting (per IP, per user, per endpoint)
 * - IP blacklist/whitelist
 * - CSRF token generation/validation
 * - Request signature verification
 * - Brute force protection
 * - Security event logging
 */
class SecurityManager
{
    private PDO $pdo;
    private ?Logger $logger;
    private array $config;

    /**
     * Constructor
     *
     * @param PDO $pdo Database connection
     * @param Logger|null $logger Optional logger
     * @param array $config Security configuration
     */
    public function __construct(PDO $pdo, ?Logger $logger = null, array $config = [])
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
        $this->config = array_merge([
            'rate_limit_window' => 60,        // seconds
            'rate_limit_max' => 60,           // requests per window
            'brute_force_threshold' => 5,     // failed attempts
            'brute_force_window' => 300,      // seconds (5 min)
            'blacklist_duration' => 3600,     // seconds (1 hour)
            'csrf_token_lifetime' => 3600,    // seconds
            'signature_algorithm' => 'sha256',
        ], $config);

        $this->createTables();
    }

    /**
     * Create required security tables
     */
    private function createTables(): void
    {
        // Rate limiting table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                id INT AUTO_INCREMENT PRIMARY KEY,
                identifier VARCHAR(255) NOT NULL,
                endpoint VARCHAR(255) NOT NULL,
                attempts INT DEFAULT 1,
                window_start INT NOT NULL,
                INDEX idx_identifier (identifier, endpoint),
                INDEX idx_window (window_start)
            ) ENGINE=InnoDB
        ");

        // IP blacklist table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS ip_blacklist (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_address VARCHAR(45) NOT NULL UNIQUE,
                reason VARCHAR(255),
                expires_at INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip (ip_address),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB
        ");

        // Security events table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS security_events (
                id INT AUTO_INCREMENT PRIMARY KEY,
                event_type VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45),
                user_id INT,
                endpoint VARCHAR(255),
                details JSON,
                severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_type (event_type),
                INDEX idx_ip (ip_address),
                INDEX idx_severity (severity)
            ) ENGINE=InnoDB
        ");

        // CSRF tokens table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS csrf_tokens (
                token VARCHAR(64) PRIMARY KEY,
                session_id VARCHAR(128) NOT NULL,
                expires_at INT NOT NULL,
                INDEX idx_session (session_id),
                INDEX idx_expires (expires_at)
            ) ENGINE=InnoDB
        ");
    }

    /**
     * Check rate limit
     *
     * @param string $identifier IP address or user ID
     * @param string $endpoint Endpoint being accessed
     * @param int|null $maxAttempts Override max attempts
     * @return bool True if allowed, false if rate limited
     */
    public function checkRateLimit(string $identifier, string $endpoint = 'global', ?int $maxAttempts = null): bool
    {
        $maxAttempts = $maxAttempts ?? $this->config['rate_limit_max'];
        $window = $this->config['rate_limit_window'];
        $windowStart = time() - $window;

        // Clean up old entries
        $stmt = $this->pdo->prepare("
            DELETE FROM rate_limits
            WHERE window_start < ?
        ");
        $stmt->execute([$windowStart]);

        // Get current attempts
        $stmt = $this->pdo->prepare("
            SELECT SUM(attempts) as total
            FROM rate_limits
            WHERE identifier = ?
            AND endpoint = ?
            AND window_start >= ?
        ");
        $stmt->execute([$identifier, $endpoint, $windowStart]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $attempts = (int)($row['total'] ?? 0);

        // Check limit
        if ($attempts >= $maxAttempts) {
            $this->logSecurityEvent('rate_limit_exceeded', [
                'identifier' => $identifier,
                'endpoint' => $endpoint,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts
            ], 'medium');

            return false;
        }

        // Record attempt
        $stmt = $this->pdo->prepare("
            INSERT INTO rate_limits (identifier, endpoint, attempts, window_start)
            VALUES (?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE attempts = attempts + 1
        ");
        $stmt->execute([$identifier, $endpoint, time()]);

        return true;
    }

    /**
     * Check if IP is blacklisted
     *
     * @param string $ipAddress IP address to check
     * @return bool True if blacklisted
     */
    public function isBlacklisted(string $ipAddress): bool
    {
        // Clean up expired entries
        $this->pdo->prepare("
            DELETE FROM ip_blacklist
            WHERE expires_at IS NOT NULL AND expires_at < ?
        ")->execute([time()]);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM ip_blacklist
            WHERE ip_address = ?
            AND (expires_at IS NULL OR expires_at > ?)
        ");
        $stmt->execute([$ipAddress, time()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['count'] > 0;
    }

    /**
     * Blacklist IP address
     *
     * @param string $ipAddress IP to blacklist
     * @param string $reason Reason for blacklist
     * @param int|null $duration Duration in seconds (null = permanent)
     * @return bool Success
     */
    public function blacklistIP(string $ipAddress, string $reason, ?int $duration = null): bool
    {
        $expiresAt = $duration ? time() + $duration : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO ip_blacklist (ip_address, reason, expires_at)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                reason = VALUES(reason),
                expires_at = VALUES(expires_at)
        ");

        $success = $stmt->execute([$ipAddress, $reason, $expiresAt]);

        if ($success) {
            $this->logSecurityEvent('ip_blacklisted', [
                'ip_address' => $ipAddress,
                'reason' => $reason,
                'duration' => $duration
            ], 'high');
        }

        return $success;
    }

    /**
     * Remove IP from blacklist
     *
     * @param string $ipAddress IP to whitelist
     * @return bool Success
     */
    public function whitelistIP(string $ipAddress): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM ip_blacklist WHERE ip_address = ?");
        return $stmt->execute([$ipAddress]);
    }

    /**
     * Generate CSRF token
     *
     * @param string $sessionId Session identifier
     * @return string CSRF token
     */
    public function generateCSRFToken(string $sessionId): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = time() + $this->config['csrf_token_lifetime'];

        $stmt = $this->pdo->prepare("
            INSERT INTO csrf_tokens (token, session_id, expires_at)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$token, $sessionId, $expiresAt]);

        // Clean up expired tokens
        $this->pdo->prepare("DELETE FROM csrf_tokens WHERE expires_at < ?")->execute([time()]);

        return $token;
    }

    /**
     * Validate CSRF token
     *
     * @param string $token CSRF token
     * @param string $sessionId Session identifier
     * @return bool Valid
     */
    public function validateCSRFToken(string $token, string $sessionId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM csrf_tokens
            WHERE token = ?
            AND session_id = ?
            AND expires_at > ?
        ");
        $stmt->execute([$token, $sessionId, time()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $valid = $row['count'] > 0;

        if ($valid) {
            // Token can only be used once
            $this->pdo->prepare("DELETE FROM csrf_tokens WHERE token = ?")->execute([$token]);
        } else {
            $this->logSecurityEvent('csrf_validation_failed', [
                'token' => substr($token, 0, 8) . '...',
                'session_id' => $sessionId
            ], 'high');
        }

        return $valid;
    }

    /**
     * Record failed login attempt
     *
     * @param string $identifier IP or username
     * @param string $ipAddress IP address
     * @return bool True if account should be locked
     */
    public function recordFailedLogin(string $identifier, string $ipAddress): bool
    {
        $this->logSecurityEvent('failed_login', [
            'identifier' => $identifier,
            'ip_address' => $ipAddress
        ], 'medium');

        // Check for brute force
        $window = $this->config['brute_force_window'];
        $threshold = $this->config['brute_force_threshold'];
        $windowStart = time() - $window;

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM security_events
            WHERE event_type = 'failed_login'
            AND (ip_address = ? OR JSON_EXTRACT(details, '$.identifier') = ?)
            AND created_at > FROM_UNIXTIME(?)
        ");
        $stmt->execute([$ipAddress, $identifier, $windowStart]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row['count'] >= $threshold) {
            $this->blacklistIP(
                $ipAddress,
                'Brute force attack detected',
                $this->config['blacklist_duration']
            );
            return true;
        }

        return false;
    }

    /**
     * Generate request signature
     *
     * @param array $data Request data
     * @param string $secret Shared secret
     * @return string Signature
     */
    public function generateSignature(array $data, string $secret): string
    {
        ksort($data);
        $payload = json_encode($data);
        return hash_hmac($this->config['signature_algorithm'], $payload, $secret);
    }

    /**
     * Validate request signature
     *
     * @param array $data Request data
     * @param string $signature Provided signature
     * @param string $secret Shared secret
     * @return bool Valid
     */
    public function validateSignature(array $data, string $signature, string $secret): bool
    {
        $expected = $this->generateSignature($data, $secret);
        $valid = hash_equals($expected, $signature);

        if (!$valid) {
            $this->logSecurityEvent('signature_validation_failed', [
                'provided' => substr($signature, 0, 16) . '...',
                'expected' => substr($expected, 0, 16) . '...'
            ], 'high');
        }

        return $valid;
    }

    /**
     * Log security event
     *
     * @param string $eventType Event type
     * @param array $details Event details
     * @param string $severity Severity level
     */
    private function logSecurityEvent(string $eventType, array $details, string $severity = 'medium'): void
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $endpoint = $_SERVER['REQUEST_URI'] ?? null;

        $stmt = $this->pdo->prepare("
            INSERT INTO security_events (event_type, ip_address, user_id, endpoint, details, severity)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $eventType,
            $ipAddress,
            $userId,
            $endpoint,
            json_encode($details),
            $severity
        ]);

        if ($this->logger) {
            $this->logger->warning("Security event: {$eventType}", array_merge($details, [
                'severity' => $severity,
                'ip' => $ipAddress
            ]));
        }
    }

    /**
     * Get recent security events
     *
     * @param int $limit Number of events
     * @param string|null $type Filter by event type
     * @param string|null $severity Filter by severity
     * @return array Events
     */
    public function getSecurityEvents(int $limit = 100, ?string $type = null, ?string $severity = null): array
    {
        $sql = "SELECT * FROM security_events WHERE 1=1";
        $params = [];

        if ($type) {
            $sql .= " AND event_type = ?";
            $params[] = $type;
        }

        if ($severity) {
            $sql .= " AND severity = ?";
            $params[] = $severity;
        }

        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get security statistics
     *
     * @param int $hours Timeframe in hours
     * @return array Statistics
     */
    public function getStats(int $hours = 24): array
    {
        $since = time() - ($hours * 3600);

        $stmt = $this->pdo->prepare("
            SELECT
                event_type,
                severity,
                COUNT(*) as count
            FROM security_events
            WHERE created_at > FROM_UNIXTIME(?)
            GROUP BY event_type, severity
            ORDER BY count DESC
        ");
        $stmt->execute([$since]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean up old security data
     *
     * @param int $days Number of days to keep
     * @return array Cleanup stats
     */
    public function cleanup(int $days = 30): array
    {
        $cutoff = time() - ($days * 86400);

        $stmt = $this->pdo->prepare("DELETE FROM security_events WHERE created_at < FROM_UNIXTIME(?)");
        $stmt->execute([$cutoff]);
        $events = $stmt->rowCount();

        $stmt = $this->pdo->prepare("DELETE FROM rate_limits WHERE window_start < ?");
        $stmt->execute([$cutoff]);
        $rateLimits = $stmt->rowCount();

        $stmt = $this->pdo->prepare("DELETE FROM csrf_tokens WHERE expires_at < ?");
        $stmt->execute([time()]);
        $tokens = $stmt->rowCount();

        return [
            'events_deleted' => $events,
            'rate_limits_deleted' => $rateLimits,
            'tokens_deleted' => $tokens
        ];
    }
}
