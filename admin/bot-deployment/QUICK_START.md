# 🚀 Bot Deployment Platform - Quick Start Guide

## Installation

### 1. Run Migrations
```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/admin/bot-deployment
./run-migrations.sh
```

### 2. Initialize Templates
```bash
php -r "
require 'vendor/autoload.php';
\$t = new \BotDeployment\Services\TemplateService();
\$t->initializeBuiltInTemplates();
echo 'Done!\n';
"
```

### 3. Start WebSocket Server
```bash
./websocket-server.php start
```

## Quick Examples

### Create a Bot from Template
```php
use BotDeployment\Services\TemplateService;

$templates = new TemplateService();
$botId = $templates->deployFromTemplate(
    templateId: 1,  // Daily Data Sync
    name: "My Sync Bot",
    variables: [
        'source' => 'mysql://prod',
        'target' => 'postgres://warehouse'
    ]
);
```

### Send Notification
```php
use BotDeployment\Services\NotificationService;

$notify = new NotificationService();
$notify->send(
    NotificationService::TYPE_BOT_FAILED,
    NotificationService::LEVEL_ERROR,
    "Bot Failed",
    "Data sync bot encountered an error",
    ['bot_id' => 123]
);
```

### Create Bot Chain
```php
use BotDeployment\Services\SchedulingService;

$scheduler = new SchedulingService();
$chainId = $scheduler->createBotChain(
    botIds: [1, 2, 3],
    options: ['mode' => 'sequential']
);
$scheduler->executeChain($chainId);
```

### Auto-Retry Failed Execution
```php
use BotDeployment\Services\SelfHealingService;

$healing = new SelfHealingService();
$healing->retryFailedExecution(
    executionId: 456,
    options: [
        'max_retries' => 3,
        'strategy' => SelfHealingService::STRATEGY_EXPONENTIAL
    ]
);
```

### Export Prometheus Metrics
```php
use BotDeployment\Services\MonitoringService;

$monitoring = new MonitoringService();
header('Content-Type: text/plain');
echo $monitoring->exportPrometheusMetrics();
```

## Cron Jobs

Add to crontab:
```cron
* * * * * php /path/to/bot-deployment/cli/process-retries.php
*/5 * * * * php /path/to/bot-deployment/cli/check-conditions.php
*/10 * * * * php /path/to/bot-deployment/cli/health-check.php
```

## WebSocket Client (JavaScript)

```javascript
const ws = new BotWebSocket('ws://localhost:8080');

ws.subscribe('bots', (data) => {
    console.log('Bot update:', data);
});

ws.subscribe('executions', (data) => {
    updateDashboard(data);
});
```

## Environment Variables

Required in `.env`:
```env
# Database
DB_HOST=localhost
DB_NAME=hdgwrzntwa
DB_USER=hdgwrzntwa
DB_PASSWORD=your-password

# Email
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-password

# Slack (optional)
SLACK_WEBHOOK_URL=https://hooks.slack.com/...

# Discord (optional)
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/...

# Twilio (optional)
TWILIO_ACCOUNT_SID=your-sid
TWILIO_AUTH_TOKEN=your-token
TWILIO_FROM_NUMBER=+1234567890

# Sentry (optional)
SENTRY_DSN=https://...@sentry.io/...
```

## Features Overview

| Feature | Status | Key Benefit |
|---------|--------|-------------|
| WebSocket Updates | ✅ | Real-time dashboard |
| Notifications | ✅ | Multi-channel alerts |
| Templates | ✅ | Rapid bot deployment |
| Monitoring | ✅ | Enterprise observability |
| Scheduling | ✅ | Complex automation |
| Self-Healing | ✅ | Auto-recovery |

## Documentation

- **Full Report:** `UPGRADE_COMPLETE_REPORT.md`
- **API Examples:** See report for detailed usage
- **Database Schema:** See migration files in `migrations/`

## Support

For issues or questions, see the comprehensive documentation in `UPGRADE_COMPLETE_REPORT.md`.
