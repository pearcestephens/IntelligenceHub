<?php
/**
 * AlertManager - Notification and alerting system
 * 
 * Handles multi-channel alerting with rate limiting and severity levels.
 * Channels: Slack, Email, Database, Webhook
 * 
 * @package SmartCron\Core
 * @version 1.0.0
 */

declare(strict_types=1);

namespace SmartCron\Core;

use SmartCron\Core\Config;
use mysqli;

class AlertManager
{
    private Config $config;
    private mysqli $db;
    private array $alertCache = [];
    private int $rateLimitWindow = 300; // 5 minutes
    
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = $this->config->getDbConnection();
        $this->loadAlertRules();
    }
    
    /**
     * Send alert through configured channels
     * 
     * @param string $severity One of: critical, high, medium, low, info
     * @param string $message Alert message
     * @param array $context Additional context (task_name, duration, etc)
     * @return bool True if alert sent successfully
     */
    public function sendAlert(string $severity, string $message, array $context = []): bool
    {
        // Map severity to database ENUM values
        $severity = $this->mapSeverityToDb($severity);
        
        // Check rate limiting
        $taskName = $context['task_name'] ?? 'system';
        if ($this->isRateLimited($taskName, $severity)) {
            error_log("[AlertManager] Rate limited: {$taskName} ({$severity})");
            return false;
        }
        
        // Log to database (always)
        $this->logToDatabase($severity, $message, $context);
        
        $success = true;
        $channels = $this->getChannelsForSeverity($severity);
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'slack':
                    $success = $this->sendSlack($severity, $message, $context) && $success;
                    break;
                    
                case 'email':
                    $success = $this->sendEmail($severity, $message, $context) && $success;
                    break;
                    
                case 'webhook':
                    $success = $this->sendWebhook($severity, $message, $context) && $success;
                    break;
                    
                case 'database':
                    // Already logged above
                    break;
                    
                default:
                    error_log("[AlertManager] Unknown channel: {$channel}");
            }
        }
        
        // Update rate limit cache
        $this->updateRateLimitCache($taskName, $severity);
        
        return $success;
    }
    
    /**
     * Send alert to Slack webhook
     */
    private function sendSlack(string $severity, string $message, array $context): bool
    {
        // Check if Slack channel is enabled first
        if (!$this->config->get('notifications.channels.slack.enabled', false)) {
            return false; // Silently skip if disabled
        }
        
        $webhookUrl = $this->config->get('notifications.channels.slack.webhook_url');
        if (empty($webhookUrl)) {
            error_log("[AlertManager] Slack webhook URL not configured");
            return false;
        }
        
        // Build Slack message
        $emoji = $this->getSeverityEmoji($severity);
        $color = $this->getSeverityColor($severity);
        
        $payload = [
            'username' => 'Smart Cron',
            'icon_emoji' => $emoji,
            'attachments' => [
                [
                    'color' => $color,
                    'title' => strtoupper($severity) . ': ' . $message,
                    'fields' => $this->formatContextFields($context),
                    'footer' => 'Smart Cron Alert System',
                    'ts' => time()
                ]
            ]
        ];
        
        // Send via cURL
        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("[AlertManager] Slack webhook failed: HTTP {$httpCode}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Send alert via email
     */
    private function sendEmail(string $severity, string $message, array $context): bool
    {
        // Check if Email channel is enabled first
        if (!$this->config->get('notifications.channels.email.enabled', false)) {
            return false; // Silently skip if disabled
        }
        
        $to = $this->config->get('notifications.channels.email.recipients', '');
        if (empty($to)) {
            error_log("[AlertManager] Email recipients not configured");
            return false;
        }
        
        // Get from address from config (fallback to settings.json)
        $fromAddress = $this->config->get('email.from_address', 'cron@vapeshed.co.nz');
        $fromName = $this->config->get('email.from_name', 'Smart Cron System');
        
        $subject = "[Smart Cron] " . strtoupper($severity) . ": {$message}";
        
        $body = "Smart Cron Alert\n\n";
        $body .= "Severity: " . strtoupper($severity) . "\n";
        $body .= "Message: {$message}\n\n";
        $body .= "Context:\n";
        
        foreach ($context as $key => $value) {
            $valueStr = is_array($value) ? json_encode($value) : (string)$value;
            $body .= "  {$key}: {$valueStr}\n";
        }
        
        $body .= "\nTimestamp: " . date('Y-m-d H:i:s') . "\n";
        
        $headers = [
            "From: {$fromName} <{$fromAddress}>",
            'X-Mailer: Smart Cron Alert System',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        $success = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if (!$success) {
            error_log("[AlertManager] Email send failed");
        }
        
        return $success;
    }
    
    /**
     * Send alert to custom webhook
     */
    private function sendWebhook(string $severity, string $message, array $context): bool
    {
        // Check if Webhook channel is enabled first
        if (!$this->config->get('notifications.channels.webhook.enabled', false)) {
            return false; // Silently skip if disabled
        }
        
        $webhookUrl = $this->config->get('notifications.channels.webhook.webhook_url');
        if (empty($webhookUrl)) {
            return false; // Silently skip if not configured
        }
        
        $payload = [
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('c'),
            'hostname' => gethostname()
        ];
        
        $ch = curl_init($webhookUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ($httpCode >= 200 && $httpCode < 300);
    }
    
    /**
     * Log alert to database
     */
    private function logToDatabase(string $severity, string $message, array $context): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cron_notifications 
            (severity, message, context, channel, created_at) 
            VALUES (?, ?, ?, 'database', NOW())"
        );
        
        $contextJson = json_encode($context);
        $stmt->bind_param('sss', $severity, $message, $contextJson);
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Check if alert is rate limited
     */
    private function isRateLimited(string $taskName, string $severity): bool
    {
        $cacheKey = "{$taskName}:{$severity}";
        
        if (!isset($this->alertCache[$cacheKey])) {
            return false;
        }
        
        $lastSent = $this->alertCache[$cacheKey];
        $elapsed = time() - $lastSent;
        
        return $elapsed < $this->rateLimitWindow;
    }
    
    /**
     * Update rate limit cache
     */
    private function updateRateLimitCache(string $taskName, string $severity): void
    {
        $cacheKey = "{$taskName}:{$severity}";
        $this->alertCache[$cacheKey] = time();
    }
    
    /**
     * Get notification channels for severity level
     */
    private function getChannelsForSeverity(string $severity): array
    {
        $channelConfig = $this->config->get('alerts.channels', [
            'critical' => ['slack', 'email', 'database'],
            'high' => ['slack', 'database'],
            'medium' => ['database'],
            'low' => ['database'],
            'info' => ['database']
        ]);
        
        return $channelConfig[$severity] ?? ['database'];
    }
    
    /**
     * Load alert rules from database
     */
    private function loadAlertRules(): void
    {
        $result = $this->db->query(
            "SELECT alert_type, condition_config, cooldown_minutes 
            FROM cron_alerts 
            WHERE is_active = 1"
        );
        
        if (!$result) {
            error_log("[AlertManager] Failed to load alert rules: " . $this->db->error);
            return;
        }
        
        while ($row = $result->fetch_assoc()) {
            // Store rules for later evaluation
            // This would be expanded in a full implementation
        }
        
        $result->free();
    }
    
    /**
     * Get emoji for severity
     */
    private function getSeverityEmoji(string $severity): string
    {
        $emojis = [
            'critical' => ':rotating_light:',
            'high' => ':warning:',
            'medium' => ':information_source:',
            'low' => ':white_check_mark:',
            'info' => ':bulb:'
        ];
        
        return $emojis[$severity] ?? ':grey_question:';
    }
    
    /**
     * Get color for severity
     */
    private function getSeverityColor(string $severity): string
    {
        $colors = [
            'critical' => '#ff0000', // Red
            'high' => '#ff8c00',     // Orange
            'medium' => '#ffcc00',   // Yellow
            'low' => '#36a64f',      // Green
            'info' => '#0099ff'      // Blue
        ];
        
        return $colors[$severity] ?? '#cccccc';
    }
    
    /**
     * Format context array for Slack fields
     */
    private function formatContextFields(array $context): array
    {
        $fields = [];
        
        foreach ($context as $key => $value) {
            $valueStr = is_array($value) ? json_encode($value) : (string)$value;
            
            $fields[] = [
                'title' => ucwords(str_replace('_', ' ', $key)),
                'value' => $valueStr,
                'short' => strlen($valueStr) < 40
            ];
        }
        
        return $fields;
    }
    
    /**
     * Send test alert
     */
    public function sendTestAlert(): bool
    {
        return $this->sendAlert(
            'info',
            'Alert system test - all systems operational',
            [
                'test' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'system' => 'Smart Cron'
            ]
        );
    }
    
    /**
     * Map severity levels to database ENUM values
     * Database accepts: debug, info, warning, error, critical
     * Application uses: critical, high, medium, low, info
     */
    private function mapSeverityToDb(string $severity): string
    {
        $map = [
            'critical' => 'critical',
            'high' => 'error',
            'medium' => 'warning',
            'low' => 'info',
            'info' => 'info',
            'debug' => 'debug'
        ];
        
        return $map[$severity] ?? 'info';
    }
}
