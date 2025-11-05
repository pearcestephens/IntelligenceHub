<?php

namespace BotDeployment\Services;

use BotDeployment\Config\Connection;
use BotDeployment\Services\Logger;
use Exception;

/**
 * Notification Service
 *
 * Sends notifications through multiple channels:
 * - Email (SMTP)
 * - Slack
 * - Discord
 * - SMS (Twilio)
 * - Browser Push Notifications
 * - Webhooks
 *
 * Configuration via .env:
 * NOTIFICATION_EMAIL_ENABLED=true
 * NOTIFICATION_SLACK_ENABLED=true
 * NOTIFICATION_DISCORD_ENABLED=true
 * NOTIFICATION_SMS_ENABLED=true
 */
class NotificationService
{
    private $logger;
    private $db;
    private $config;

    // Notification levels
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    // Notification types
    const TYPE_BOT_STARTED = 'bot_started';
    const TYPE_BOT_COMPLETED = 'bot_completed';
    const TYPE_BOT_FAILED = 'bot_failed';
    const TYPE_BOT_TIMEOUT = 'bot_timeout';
    const TYPE_SYSTEM_ALERT = 'system_alert';
    const TYPE_HEALTH_CHECK = 'health_check';

    public function __construct()
    {
        $this->logger = new Logger('notifications');
        $this->db = Connection::getInstance();
        $this->loadConfig();
    }

    /**
     * Load notification configuration
     */
    private function loadConfig(): void
    {
        $this->config = [
            'email' => [
                'enabled' => getenv('NOTIFICATION_EMAIL_ENABLED') === 'true',
                'smtp_host' => getenv('SMTP_HOST') ?: 'localhost',
                'smtp_port' => (int) (getenv('SMTP_PORT') ?: 587),
                'smtp_user' => getenv('SMTP_USER') ?: '',
                'smtp_pass' => getenv('SMTP_PASS') ?: '',
                'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'noreply@example.com',
                'from_name' => getenv('SMTP_FROM_NAME') ?: 'Bot Deployment System',
                'recipients' => explode(',', getenv('NOTIFICATION_EMAIL_RECIPIENTS') ?: '')
            ],
            'slack' => [
                'enabled' => getenv('NOTIFICATION_SLACK_ENABLED') === 'true',
                'webhook_url' => getenv('SLACK_WEBHOOK_URL') ?: '',
                'channel' => getenv('SLACK_CHANNEL') ?: '#bot-alerts',
                'username' => getenv('SLACK_USERNAME') ?: 'Bot Deployment',
                'icon_emoji' => getenv('SLACK_ICON') ?: ':robot_face:'
            ],
            'discord' => [
                'enabled' => getenv('NOTIFICATION_DISCORD_ENABLED') === 'true',
                'webhook_url' => getenv('DISCORD_WEBHOOK_URL') ?: '',
                'username' => getenv('DISCORD_USERNAME') ?: 'Bot Deployment'
            ],
            'sms' => [
                'enabled' => getenv('NOTIFICATION_SMS_ENABLED') === 'true',
                'twilio_sid' => getenv('TWILIO_ACCOUNT_SID') ?: '',
                'twilio_token' => getenv('TWILIO_AUTH_TOKEN') ?: '',
                'twilio_from' => getenv('TWILIO_FROM_NUMBER') ?: '',
                'recipients' => explode(',', getenv('NOTIFICATION_SMS_RECIPIENTS') ?: '')
            ],
            'webhook' => [
                'enabled' => getenv('NOTIFICATION_WEBHOOK_ENABLED') === 'true',
                'urls' => explode(',', getenv('NOTIFICATION_WEBHOOK_URLS') ?: '')
            ]
        ];
    }

    /**
     * Send notification
     *
     * @param string $type Notification type
     * @param string $level Notification level
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $context Additional context data
     * @param array $channels Specific channels to use (null = all enabled)
     */
    public function send(
        string $type,
        string $level,
        string $title,
        string $message,
        array $context = [],
        ?array $channels = null
    ): array {
        $this->logger->info("Sending notification", [
            'type' => $type,
            'level' => $level,
            'title' => $title
        ]);

        $results = [];

        // Email
        if ($this->shouldSendTo('email', $channels) && $this->config['email']['enabled']) {
            $results['email'] = $this->sendEmail($title, $message, $level, $context);
        }

        // Slack
        if ($this->shouldSendTo('slack', $channels) && $this->config['slack']['enabled']) {
            $results['slack'] = $this->sendSlack($title, $message, $level, $context);
        }

        // Discord
        if ($this->shouldSendTo('discord', $channels) && $this->config['discord']['enabled']) {
            $results['discord'] = $this->sendDiscord($title, $message, $level, $context);
        }

        // SMS (only for critical/error)
        if ($this->shouldSendTo('sms', $channels) &&
            $this->config['sms']['enabled'] &&
            in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL])) {
            $results['sms'] = $this->sendSMS($title, $message, $context);
        }

        // Webhooks
        if ($this->shouldSendTo('webhook', $channels) && $this->config['webhook']['enabled']) {
            $results['webhook'] = $this->sendWebhook($type, $level, $title, $message, $context);
        }

        // Log notification
        $this->logNotification($type, $level, $title, $message, $context, $results);

        return $results;
    }

    /**
     * Check if should send to channel
     */
    private function shouldSendTo(string $channel, ?array $channels): bool
    {
        return $channels === null || in_array($channel, $channels);
    }

    /**
     * Send email notification
     */
    private function sendEmail(string $title, string $message, string $level, array $context): bool
    {
        try {
            $config = $this->config['email'];

            if (empty($config['recipients'])) {
                throw new Exception("No email recipients configured");
            }

            // Build email HTML
            $html = $this->buildEmailHTML($title, $message, $level, $context);

            // Build email headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
                'X-Mailer: PHP/' . phpversion(),
                'X-Priority: ' . ($level === self::LEVEL_CRITICAL ? '1' : '3')
            ];

            $subject = "[{$level}] {$title}";

            // Send to all recipients
            $success = true;
            foreach ($config['recipients'] as $recipient) {
                $recipient = trim($recipient);
                if (empty($recipient)) continue;

                if (!mail($recipient, $subject, $html, implode("\r\n", $headers))) {
                    $this->logger->warning("Failed to send email", ['recipient' => $recipient]);
                    $success = false;
                }
            }

            return $success;

        } catch (Exception $e) {
            $this->logger->error("Email notification failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Build email HTML
     */
    private function buildEmailHTML(string $title, string $message, string $level, array $context): string
    {
        $color = $this->getLevelColor($level);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: {$color}; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
        .footer { background: #333; color: white; padding: 10px; text-align: center; border-radius: 0 0 5px 5px; font-size: 12px; }
        .level { display: inline-block; padding: 5px 10px; background: {$color}; color: white; border-radius: 3px; font-weight: bold; text-transform: uppercase; }
        .context { background: white; padding: 10px; margin-top: 10px; border-left: 3px solid {$color}; }
        .context-item { margin: 5px 0; }
        .context-label { font-weight: bold; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🤖 Bot Deployment System</h2>
        </div>
        <div class="content">
            <p><span class="level">{$level}</span></p>
            <h3>{$title}</h3>
            <p>{$message}</p>
HTML;

        if (!empty($context)) {
            $html .= '<div class="context"><strong>Details:</strong>';
            foreach ($context as $key => $value) {
                $html .= '<div class="context-item">';
                $html .= '<span class="context-label">' . htmlspecialchars($key) . ':</span> ';
                $html .= htmlspecialchars(is_array($value) ? json_encode($value) : $value);
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= <<<HTML
        </div>
        <div class="footer">
            Bot Deployment Management System • {$this->getTimestamp()}
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Send Slack notification
     */
    private function sendSlack(string $title, string $message, string $level, array $context): bool
    {
        try {
            $config = $this->config['slack'];

            if (empty($config['webhook_url'])) {
                throw new Exception("Slack webhook URL not configured");
            }

            $color = $this->getLevelColor($level);

            $payload = [
                'channel' => $config['channel'],
                'username' => $config['username'],
                'icon_emoji' => $config['icon_emoji'],
                'attachments' => [[
                    'color' => $color,
                    'title' => $title,
                    'text' => $message,
                    'fields' => $this->buildSlackFields($level, $context),
                    'footer' => 'Bot Deployment System',
                    'ts' => time()
                ]]
            ];

            return $this->sendWebhookRequest($config['webhook_url'], $payload);

        } catch (Exception $e) {
            $this->logger->error("Slack notification failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Build Slack fields
     */
    private function buildSlackFields(string $level, array $context): array
    {
        $fields = [
            [
                'title' => 'Level',
                'value' => strtoupper($level),
                'short' => true
            ],
            [
                'title' => 'Time',
                'value' => $this->getTimestamp(),
                'short' => true
            ]
        ];

        foreach ($context as $key => $value) {
            $fields[] = [
                'title' => ucfirst($key),
                'value' => is_array($value) ? json_encode($value) : $value,
                'short' => strlen((string)$value) < 40
            ];
        }

        return $fields;
    }

    /**
     * Send Discord notification
     */
    private function sendDiscord(string $title, string $message, string $level, array $context): bool
    {
        try {
            $config = $this->config['discord'];

            if (empty($config['webhook_url'])) {
                throw new Exception("Discord webhook URL not configured");
            }

            $color = hexdec(ltrim($this->getLevelColor($level), '#'));

            $payload = [
                'username' => $config['username'],
                'embeds' => [[
                    'title' => $title,
                    'description' => $message,
                    'color' => $color,
                    'fields' => $this->buildDiscordFields($level, $context),
                    'footer' => [
                        'text' => 'Bot Deployment System'
                    ],
                    'timestamp' => date('c')
                ]]
            ];

            return $this->sendWebhookRequest($config['webhook_url'], $payload);

        } catch (Exception $e) {
            $this->logger->error("Discord notification failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Build Discord fields
     */
    private function buildDiscordFields(string $level, array $context): array
    {
        $fields = [
            [
                'name' => 'Level',
                'value' => strtoupper($level),
                'inline' => true
            ]
        ];

        foreach ($context as $key => $value) {
            $fields[] = [
                'name' => ucfirst($key),
                'value' => is_array($value) ? '```json' . "\n" . json_encode($value, JSON_PRETTY_PRINT) . "\n```" : $value,
                'inline' => strlen((string)$value) < 40
            ];
        }

        return $fields;
    }

    /**
     * Send SMS notification
     */
    private function sendSMS(string $title, string $message, array $context): bool
    {
        try {
            $config = $this->config['sms'];

            if (empty($config['twilio_sid']) || empty($config['twilio_token'])) {
                throw new Exception("Twilio credentials not configured");
            }

            if (empty($config['recipients'])) {
                throw new Exception("No SMS recipients configured");
            }

            // Truncate message for SMS
            $smsText = "{$title}: {$message}";
            if (strlen($smsText) > 160) {
                $smsText = substr($smsText, 0, 157) . '...';
            }

            // Send via Twilio API
            $success = true;
            foreach ($config['recipients'] as $recipient) {
                $recipient = trim($recipient);
                if (empty($recipient)) continue;

                if (!$this->sendTwilioSMS($config, $recipient, $smsText)) {
                    $success = false;
                }
            }

            return $success;

        } catch (Exception $e) {
            $this->logger->error("SMS notification failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendTwilioSMS(array $config, string $to, string $message): bool
    {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$config['twilio_sid']}/Messages.json";

        $data = [
            'From' => $config['twilio_from'],
            'To' => $to,
            'Body' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$config['twilio_sid']}:{$config['twilio_token']}");

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            $this->logger->error("Twilio SMS failed", [
                'to' => $to,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return false;
        }

        return true;
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(string $type, string $level, string $title, string $message, array $context): bool
    {
        try {
            $config = $this->config['webhook'];

            if (empty($config['urls'])) {
                throw new Exception("No webhook URLs configured");
            }

            $payload = [
                'type' => $type,
                'level' => $level,
                'title' => $title,
                'message' => $message,
                'context' => $context,
                'timestamp' => time(),
                'datetime' => date('c')
            ];

            $success = true;
            foreach ($config['urls'] as $url) {
                $url = trim($url);
                if (empty($url)) continue;

                if (!$this->sendWebhookRequest($url, $payload)) {
                    $success = false;
                }
            }

            return $success;

        } catch (Exception $e) {
            $this->logger->error("Webhook notification failed", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send webhook HTTP request
     */
    private function sendWebhookRequest(string $url, array $payload): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: BotDeployment/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->logger->error("Webhook request failed", [
                'url' => $url,
                'http_code' => $httpCode,
                'error' => $error
            ]);
            return false;
        }

        return true;
    }

    /**
     * Get color for notification level
     */
    private function getLevelColor(string $level): string
    {
        return match ($level) {
            self::LEVEL_INFO => '#17a2b8',
            self::LEVEL_WARNING => '#ffc107',
            self::LEVEL_ERROR => '#dc3545',
            self::LEVEL_CRITICAL => '#8b0000',
            default => '#6c757d'
        };
    }

    /**
     * Get formatted timestamp
     */
    private function getTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Log notification to database
     */
    private function logNotification(
        string $type,
        string $level,
        string $title,
        string $message,
        array $context,
        array $results
    ): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notification_log (type, level, title, message, context, channels, sent_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $type,
                $level,
                $title,
                $message,
                json_encode($context),
                json_encode($results)
            ]);

        } catch (Exception $e) {
            $this->logger->error("Failed to log notification", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get notification history
     */
    public function getHistory(int $limit = 100, ?string $level = null): array
    {
        try {
            $sql = "SELECT * FROM notification_log";
            $params = [];

            if ($level) {
                $sql .= " WHERE level = ?";
                $params[] = $level;
            }

            $sql .= " ORDER BY sent_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->logger->error("Failed to get notification history", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Test notification channels
     */
    public function testChannels(): array
    {
        $results = [];

        // Test each enabled channel
        if ($this->config['email']['enabled']) {
            $results['email'] = $this->sendEmail(
                'Test Notification',
                'This is a test email from Bot Deployment System',
                self::LEVEL_INFO,
                ['test' => true]
            );
        }

        if ($this->config['slack']['enabled']) {
            $results['slack'] = $this->sendSlack(
                'Test Notification',
                'This is a test message from Bot Deployment System',
                self::LEVEL_INFO,
                ['test' => true]
            );
        }

        if ($this->config['discord']['enabled']) {
            $results['discord'] = $this->sendDiscord(
                'Test Notification',
                'This is a test message from Bot Deployment System',
                self::LEVEL_INFO,
                ['test' => true]
            );
        }

        return $results;
    }
}
