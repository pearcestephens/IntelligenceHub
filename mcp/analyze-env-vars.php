#!/usr/bin/env php
<?php
/**
 * Environment Variable Analyzer
 *
 * Scans entire codebase and identifies all environment variables being referenced
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(strict_types=1);

// Scan directories
$scanDirs = [
    '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html',
    '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html'
];

$envVars = [];
$files = [];

/**
 * Recursively scan directory for PHP files
 */
function scanDirectory(string $dir, array &$files): void {
    if (!is_dir($dir)) return;

    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . '/' . $item;

        if (is_dir($path)) {
            // Skip certain directories
            if (in_array($item, ['vendor', 'node_modules', '.git', 'cache', 'logs'])) {
                continue;
            }
            scanDirectory($path, $files);
        } elseif (is_file($path) && preg_match('/\.(php|js|json|sh|env)$/i', $path)) {
            $files[] = $path;
        }
    }
}

/**
 * Extract environment variables from file
 */
function extractEnvVars(string $filePath): array {
    $content = @file_get_contents($filePath);
    if ($content === false) return [];

    $vars = [];

    // Pattern 1: $_ENV['VAR_NAME']
    preg_match_all('/\$_ENV\[[\'"]([A-Z_0-9]+)[\'"]\]/i', $content, $matches1);
    $vars = array_merge($vars, $matches1[1]);

    // Pattern 2: $_SERVER['VAR_NAME']
    preg_match_all('/\$_SERVER\[[\'"]([A-Z_0-9]+)[\'"]\]/i', $content, $matches2);
    $vars = array_merge($vars, $matches2[1]);

    // Pattern 3: getenv('VAR_NAME')
    preg_match_all('/getenv\([\'"]([A-Z_0-9]+)[\'"]\)/i', $content, $matches3);
    $vars = array_merge($vars, $matches3[1]);

    // Pattern 4: env('VAR_NAME')
    preg_match_all('/env\([\'"]([A-Z_0-9]+)[\'"]\)/i', $content, $matches4);
    $vars = array_merge($vars, $matches4[1]);

    // Pattern 5: process.env.VAR_NAME (JavaScript)
    preg_match_all('/process\.env\.([A-Z_0-9]+)/i', $content, $matches5);
    $vars = array_merge($vars, $matches5[1]);

    return array_unique($vars);
}

echo "Scanning codebase for environment variables...\n\n";

foreach ($scanDirs as $dir) {
    if (is_dir($dir)) {
        echo "Scanning: {$dir}\n";
        scanDirectory($dir, $files);
    }
}

echo sprintf("Found %d files to analyze\n\n", count($files));

// Analyze each file
$fileCount = 0;
foreach ($files as $file) {
    $vars = extractEnvVars($file);
    if (!empty($vars)) {
        $fileCount++;
        foreach ($vars as $var) {
            if (!isset($envVars[$var])) {
                $envVars[$var] = ['files' => [], 'count' => 0];
            }
            $envVars[$var]['files'][] = $file;
            $envVars[$var]['count']++;
        }
    }
}

// Sort by usage count
uasort($envVars, fn($a, $b) => $b['count'] <=> $a['count']);

echo sprintf("Analyzed %d files\n", $fileCount);
echo sprintf("Found %d unique environment variables\n\n", count($envVars));

// Categorize variables
$categories = [
    'Database' => ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_PASSWORD', 'DB_USERNAME', 'DB_DATABASE', 'DB_CHARSET'],
    'AI/API Keys' => ['OPENAI_API_KEY', 'ANTHROPIC_API_KEY', 'MCP_API_KEY', 'API_KEY', 'AI_AGENT_API_KEY', 'CIS_API_KEY', 'KB_API_KEY'],
    'Vend POS' => ['VEND_API_URL', 'VEND_API_TOKEN', 'VEND_DOMAIN_PREFIX'],
    'Redis' => ['REDIS_HOST', 'REDIS_PORT', 'REDIS_PASSWORD', 'REDIS_DATABASE', 'REDIS_DB', 'REDIS_TIMEOUT'],
    'MCP Server' => ['MCP_SERVER_URL', 'MCP_ENDPOINT', 'MCP_AUTH_TOKEN', 'WORKSPACE_ROOT', 'CURRENT_FILE', 'PROJECT_ID', 'BUSINESS_UNIT_ID'],
    'AI Agent' => ['AI_AGENT_ENDPOINT', 'AI_DEFAULT_UNIT_ID', 'AI_DEFAULT_PROJECT_ID', 'AI_DEFAULT_BOT', 'AI_AGENT_DEFAULT_SYSTEM', 'AI_AGENT_REQUIRE_AUTH', 'AI_AGENT_TIMEOUT', 'AI_AGENT_RETRY', 'AI_AGENT_RETRY_DELAY'],
    'Cloudways' => ['CLOUDWAYS_EMAIL', 'CLOUDWAYS_API_KEY'],
    'Email/SMTP' => ['SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_FROM_EMAIL', 'SMTP_FROM_NAME', 'NOTIFICATION_EMAIL_ENABLED', 'NOTIFICATION_EMAIL_RECIPIENTS', 'ALERT_EMAIL_FROM'],
    'Notifications' => ['NOTIFICATION_SLACK_ENABLED', 'SLACK_WEBHOOK_URL', 'SLACK_CHANNEL', 'SLACK_USERNAME', 'SLACK_ICON', 'NOTIFICATION_DISCORD_ENABLED', 'DISCORD_WEBHOOK_URL', 'DISCORD_USERNAME', 'NOTIFICATION_SMS_ENABLED', 'NOTIFICATION_WEBHOOK_ENABLED', 'NOTIFICATION_WEBHOOK_URLS'],
    'Twilio/SMS' => ['TWILIO_ACCOUNT_SID', 'TWILIO_AUTH_TOKEN', 'TWILIO_FROM_NUMBER', 'NOTIFICATION_SMS_RECIPIENTS'],
    'Sentry' => ['SENTRY_ENABLED', 'SENTRY_DSN', 'SENTRY_TRACES_SAMPLE_RATE', 'SENTRY_PROFILES_SAMPLE_RATE'],
    'Caching' => ['CACHE_ENABLED', 'CACHE_DRIVER', 'CACHE_TTL', 'CACHE_PREFIX'],
    'Logging' => ['LOG_LEVEL', 'LOG_PATH'],
    'Bot Deployment' => ['BOT_MAX_CONCURRENT', 'BOT_TIMEOUT', 'BOT_MEMORY_LIMIT', 'BOT_MAX_RETRIES', 'BOT_ENABLE_QUEUE', 'BOT_QUEUE_DRIVER', 'THREAD_TIMEOUT'],
    'Security' => ['CREDENTIAL_ENCRYPTION_KEY', 'ADMIN_SSE_TOKEN', 'ALLOWED_IPS'],
    'Rate Limiting' => ['RATE_LIMIT_ENABLED', 'RATE_LIMIT_REQUESTS', 'RATE_LIMIT_WINDOW'],
    'Application' => ['APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_TIMEZONE'],
    'Upload' => ['AI_UPLOAD_MAX_BYTES', 'AI_UPLOAD_ALLOWED'],
    'HTTP' => ['HTTP_FETCH_ALLOW_HOSTS'],
    'Business' => ['BUSINESS_HOURS_START', 'BUSINESS_HOURS_END'],
    'Devkit' => ['DEVKIT_ENTERPRISE_URL'],
    'Pricing' => ['OPENAI_IN_NZD_PER_1K', 'OPENAI_OUT_NZD_PER_1K', 'ANTHROPIC_IN_NZD_PER_1K', 'ANTHROPIC_OUT_NZD_PER_1K']
];

$categorized = [];
$uncategorized = [];

foreach ($envVars as $var => $data) {
    $found = false;
    foreach ($categories as $category => $vars) {
        if (in_array($var, $vars)) {
            $categorized[$category][] = ['var' => $var, 'count' => $data['count']];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $uncategorized[] = ['var' => $var, 'count' => $data['count']];
    }
}

// Generate Report
echo str_repeat("=", 80) . "\n";
echo "ENVIRONMENT VARIABLES COMPREHENSIVE REPORT\n";
echo str_repeat("=", 80) . "\n\n";

echo "CATEGORIZED VARIABLES:\n\n";

foreach ($categories as $category => $vars) {
    if (isset($categorized[$category])) {
        echo "├─ {$category}\n";
        foreach ($categorized[$category] as $item) {
            $required = in_array($item['var'], ['DB_HOST', 'DB_NAME', 'DB_USER', 'OPENAI_API_KEY', 'MCP_API_KEY']) ? ' [REQUIRED]' : '';
            echo sprintf("│  ├─ %-40s (used %d times)%s\n", $item['var'], $item['count'], $required);
        }
        echo "│\n";
    }
}

if (!empty($uncategorized)) {
    echo "\nUNCATEGORIZED VARIABLES:\n\n";
    foreach ($uncategorized as $item) {
        echo sprintf("├─ %-40s (used %d times)\n", $item['var'], $item['count']);
    }
}

// Generate .env template
echo "\n" . str_repeat("=", 80) . "\n";
echo "RECOMMENDED .env TEMPLATE\n";
echo str_repeat("=", 80) . "\n\n";

$template = <<<'ENV'
# ============================================================================
# Ecigdis Intelligence Hub - Master .env Configuration
# ============================================================================
# Generated: 2025-11-05
#
# This file contains ALL environment variables used across the entire system.
# Copy this to your target .env location and fill in the values.
# ============================================================================

# ----------------------------------------------------------------------------
# Database Configuration [REQUIRED]
# ----------------------------------------------------------------------------
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=hdgwrzntwa
DB_USER=hdgwrzntwa
DB_PASS=your_password_here
DB_CHARSET=utf8mb4

# Note: Some legacy code uses these alternative names:
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

# ----------------------------------------------------------------------------
# AI/API Keys [REQUIRED]
# ----------------------------------------------------------------------------
OPENAI_API_KEY=sk-proj-your-openai-key-here
ANTHROPIC_API_KEY=sk-ant-your-anthropic-key-here
MCP_API_KEY=31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35
AI_AGENT_API_KEY=${MCP_API_KEY}
CIS_API_KEY=your_cis_api_key_here
KB_API_KEY=your_kb_api_key_here
API_KEY=${MCP_API_KEY}

# ----------------------------------------------------------------------------
# Vend POS Integration
# ----------------------------------------------------------------------------
VEND_API_URL=https://yourdomain.vendhq.com/api/2.0
VEND_API_TOKEN=your_vend_token_here
VEND_DOMAIN_PREFIX=yourdomain

# ----------------------------------------------------------------------------
# Redis Cache
# ----------------------------------------------------------------------------
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0
REDIS_DB=${REDIS_DATABASE}
REDIS_TIMEOUT=2.5

# ----------------------------------------------------------------------------
# MCP Server Configuration
# ----------------------------------------------------------------------------
MCP_SERVER_URL=https://gpt.ecigdis.co.nz/mcp/server_v3.php
MCP_ENDPOINT=${MCP_SERVER_URL}
MCP_AUTH_TOKEN=${MCP_API_KEY}
WORKSPACE_ROOT=/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html
CURRENT_FILE=
PROJECT_ID=2
BUSINESS_UNIT_ID=2

# ----------------------------------------------------------------------------
# AI Agent Configuration
# ----------------------------------------------------------------------------
AI_AGENT_ENDPOINT=https://gpt.ecigdis.co.nz/ai-agent/api/chat.php
AI_DEFAULT_UNIT_ID=2
AI_DEFAULT_PROJECT_ID=2
AI_DEFAULT_BOT=intelligence-hub
AI_AGENT_DEFAULT_SYSTEM=You are Ecigdis Assistant. Timezone: Pacific/Auckland.
AI_AGENT_REQUIRE_AUTH=0
AI_AGENT_TIMEOUT=45
AI_AGENT_RETRY=3
AI_AGENT_RETRY_DELAY=1000

# ----------------------------------------------------------------------------
# Cloudways API
# ----------------------------------------------------------------------------
CLOUDWAYS_EMAIL=your_email@example.com
CLOUDWAYS_API_KEY=your_cloudways_api_key_here

# ----------------------------------------------------------------------------
# Email/SMTP Configuration
# ----------------------------------------------------------------------------
SMTP_HOST=localhost
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SMTP_FROM_EMAIL=noreply@ecigdis.co.nz
SMTP_FROM_NAME=Ecigdis Intelligence Hub
NOTIFICATION_EMAIL_ENABLED=false
NOTIFICATION_EMAIL_RECIPIENTS=admin@ecigdis.co.nz
ALERT_EMAIL_FROM=alerts@ecigdis.co.nz

# ----------------------------------------------------------------------------
# Notifications (Slack/Discord/SMS/Webhook)
# ----------------------------------------------------------------------------
NOTIFICATION_SLACK_ENABLED=false
SLACK_WEBHOOK_URL=
SLACK_CHANNEL=#alerts
SLACK_USERNAME=Intelligence Hub
SLACK_ICON=:robot_face:

NOTIFICATION_DISCORD_ENABLED=false
DISCORD_WEBHOOK_URL=
DISCORD_USERNAME=Intelligence Hub

NOTIFICATION_SMS_ENABLED=false
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_FROM_NUMBER=
NOTIFICATION_SMS_RECIPIENTS=

NOTIFICATION_WEBHOOK_ENABLED=false
NOTIFICATION_WEBHOOK_URLS=

# ----------------------------------------------------------------------------
# Sentry Error Tracking
# ----------------------------------------------------------------------------
SENTRY_ENABLED=false
SENTRY_DSN=
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1

# ----------------------------------------------------------------------------
# Caching
# ----------------------------------------------------------------------------
CACHE_ENABLED=true
CACHE_DRIVER=redis
CACHE_TTL=3600
CACHE_PREFIX=mcp_

# ----------------------------------------------------------------------------
# Logging
# ----------------------------------------------------------------------------
LOG_LEVEL=info
LOG_PATH=/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs

# ----------------------------------------------------------------------------
# Bot Deployment
# ----------------------------------------------------------------------------
BOT_MAX_CONCURRENT=10
BOT_TIMEOUT=300
BOT_MEMORY_LIMIT=512M
BOT_MAX_RETRIES=3
BOT_ENABLE_QUEUE=true
BOT_QUEUE_DRIVER=database
THREAD_TIMEOUT=600

# ----------------------------------------------------------------------------
# Security
# ----------------------------------------------------------------------------
CREDENTIAL_ENCRYPTION_KEY=
ADMIN_SSE_TOKEN=
ALLOWED_IPS=

# ----------------------------------------------------------------------------
# Rate Limiting
# ----------------------------------------------------------------------------
RATE_LIMIT_ENABLED=true
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=60

# ----------------------------------------------------------------------------
# Application
# ----------------------------------------------------------------------------
APP_ENV=production
APP_DEBUG=false
APP_URL=https://gpt.ecigdis.co.nz
APP_TIMEZONE=Pacific/Auckland

# ----------------------------------------------------------------------------
# Upload Configuration
# ----------------------------------------------------------------------------
AI_UPLOAD_MAX_BYTES=10485760
AI_UPLOAD_ALLOWED=txt,md,log,json,csv,png,jpg,jpeg,gif,webp,pdf,zip,tar.gz

# ----------------------------------------------------------------------------
# HTTP Configuration
# ----------------------------------------------------------------------------
HTTP_FETCH_ALLOW_HOSTS=

# ----------------------------------------------------------------------------
# Business Hours
# ----------------------------------------------------------------------------
BUSINESS_HOURS_START=8
BUSINESS_HOURS_END=23

# ----------------------------------------------------------------------------
# Devkit
# ----------------------------------------------------------------------------
DEVKIT_ENTERPRISE_URL=

# ----------------------------------------------------------------------------
# AI Pricing (NZD per 1000 tokens)
# ----------------------------------------------------------------------------
OPENAI_IN_NZD_PER_1K=0.015
OPENAI_OUT_NZD_PER_1K=0.06
ANTHROPIC_IN_NZD_PER_1K=0.03
ANTHROPIC_OUT_NZD_PER_1K=0.15

# ============================================================================
# END OF CONFIGURATION
# ============================================================================
ENV;

echo $template;

// Save report to file
$reportFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/env-variables-report.txt';
$jsonFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/env-variables-analysis.json';

ob_start();
echo str_repeat("=", 80) . "\n";
echo "ENVIRONMENT VARIABLES COMPREHENSIVE REPORT\n";
echo str_repeat("=", 80) . "\n\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Files Scanned: " . count($files) . "\n";
echo "Variables Found: " . count($envVars) . "\n\n";

foreach ($categories as $category => $vars) {
    if (isset($categorized[$category])) {
        echo "\n{$category}:\n";
        echo str_repeat("-", 40) . "\n";
        foreach ($categorized[$category] as $item) {
            $required = in_array($item['var'], ['DB_HOST', 'DB_NAME', 'DB_USER', 'OPENAI_API_KEY', 'MCP_API_KEY']) ? ' [REQUIRED]' : '';
            echo sprintf("  %-35s used %d times%s\n", $item['var'], $item['count'], $required);
        }
    }
}

if (!empty($uncategorized)) {
    echo "\n\nUncategorized:\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($uncategorized as $item) {
        echo sprintf("  %-35s used %d times\n", $item['var'], $item['count']);
    }
}

$reportContent = ob_get_clean();
file_put_contents($reportFile, $reportContent);

// Save JSON
file_put_contents($jsonFile, json_encode([
    'generated' => date('c'),
    'files_scanned' => count($files),
    'variables_found' => count($envVars),
    'categorized' => $categorized,
    'uncategorized' => $uncategorized,
    'all_variables' => $envVars
], JSON_PRETTY_PRINT));

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "Reports saved:\n";
echo "  - Text report: {$reportFile}\n";
echo "  - JSON data: {$jsonFile}\n";
echo "  - Template: Copy the .env template above to your .env file\n";
echo str_repeat("=", 80) . "\n";
