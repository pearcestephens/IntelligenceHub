# Environment Variables - Complete System Analysis

**Generated:** 2025-11-05
**Analysis Source:** Entire codebase scan via grep
**Total Unique Variables Found:** 100+

---

## 🔴 CRITICAL FINDING: Variable Name Inconsistencies

The system uses **MULTIPLE NAMING CONVENTIONS** for the same variables, causing failures:

### Database Variables (3 different naming patterns!)
```
PATTERN 1: DB_USER, DB_PASS, DB_NAME
PATTERN 2: DB_USERNAME, DB_PASSWORD, DB_DATABASE
PATTERN 3: DB_USER, DB_PASSWORD, DB_NAME
```

**Result:** Scripts fail when they expect one pattern but .env uses another!

---

## 📊 Complete Variable Inventory

### 🗄️ Database Configuration [REQUIRED]
- `DB_HOST` - Database host (most common: used 40+ times)
- `DB_PORT` - Database port
- `DB_NAME` - Database name (most common)
- `DB_USER` - Database username (most common)
- `DB_PASS` - Database password (most common)
- **LEGACY ALTERNATIVES:**
  - `DB_DATABASE` (used in 10+ files)
  - `DB_USERNAME` (used in 5+ files)
  - `DB_PASSWORD` (used in 15+ files)
- `DB_CHARSET` - Character set (default: utf8mb4)

### 🤖 AI/API Keys [REQUIRED]
- `OPENAI_API_KEY` - OpenAI API key (used 20+ times)
- `ANTHROPIC_API_KEY` - Anthropic Claude API key (used 10+ times)
- `MCP_API_KEY` - MCP server authentication (used 30+ times)
- `AI_AGENT_API_KEY` - AI Agent API key
- `CIS_API_KEY` - CIS system API key
- `KB_API_KEY` - Knowledge Base API key
- `API_KEY` - Generic API key fallback

### 🛒 Vend POS Integration
- `VEND_API_URL` - Vend API endpoint
- `VEND_API_TOKEN` - Vend authentication token
- `VEND_DOMAIN_PREFIX` - Vend domain prefix

### 🔴 Redis Cache
- `REDIS_HOST` - Redis host (default: 127.0.0.1)
- `REDIS_PORT` - Redis port (default: 6379)
- `REDIS_PASSWORD` - Redis password (optional)
- `REDIS_DATABASE` - Redis database number
- **ALTERNATIVE:** `REDIS_DB` (same as REDIS_DATABASE)
- `REDIS_TIMEOUT` - Connection timeout

### 🌐 MCP Server Configuration
- `MCP_SERVER_URL` - MCP server endpoint
- **ALTERNATIVE:** `MCP_ENDPOINT` (same as above)
- `MCP_AUTH_TOKEN` - MCP authentication
- `WORKSPACE_ROOT` - Project workspace path
- `CURRENT_FILE` - Active file path
- `PROJECT_ID` - Project identifier
- `BUSINESS_UNIT_ID` - Business unit ID

### 🧠 AI Agent Configuration
- `AI_AGENT_ENDPOINT` - AI Agent API endpoint
- `AI_DEFAULT_UNIT_ID` - Default business unit
- `AI_DEFAULT_PROJECT_ID` - Default project
- `AI_DEFAULT_BOT` - Default bot identifier
- `AI_AGENT_DEFAULT_SYSTEM` - System prompt
- `AI_AGENT_REQUIRE_AUTH` - Require authentication (0/1)
- `AI_AGENT_TIMEOUT` - Request timeout (seconds)
- `AI_AGENT_RETRY` - Retry attempts
- `AI_AGENT_RETRY_DELAY` - Retry delay (milliseconds)

### ☁️ Cloudways API
- `CLOUDWAYS_EMAIL` - Cloudways account email
- `CLOUDWAYS_API_KEY` - Cloudways API key

### 📧 Email/SMTP Configuration
- `SMTP_HOST` - SMTP server host
- `SMTP_PORT` - SMTP port
- `SMTP_USER` - SMTP username
- `SMTP_PASS` - SMTP password
- `SMTP_FROM_EMAIL` - From email address
- `SMTP_FROM_NAME` - From display name
- `NOTIFICATION_EMAIL_ENABLED` - Enable email notifications
- `NOTIFICATION_EMAIL_RECIPIENTS` - Recipient email addresses
- `ALERT_EMAIL_FROM` - Alert sender email

### 🔔 Notifications (Slack/Discord/SMS)
- `NOTIFICATION_SLACK_ENABLED` - Enable Slack
- `SLACK_WEBHOOK_URL` - Slack webhook URL
- `SLACK_CHANNEL` - Slack channel
- `SLACK_USERNAME` - Slack bot username
- `SLACK_ICON` - Slack bot icon
- `NOTIFICATION_DISCORD_ENABLED` - Enable Discord
- `DISCORD_WEBHOOK_URL` - Discord webhook URL
- `DISCORD_USERNAME` - Discord bot username
- `NOTIFICATION_SMS_ENABLED` - Enable SMS
- `TWILIO_ACCOUNT_SID` - Twilio account SID
- `TWILIO_AUTH_TOKEN` - Twilio auth token
- `TWILIO_FROM_NUMBER` - Twilio phone number
- `NOTIFICATION_SMS_RECIPIENTS` - SMS recipients
- `NOTIFICATION_WEBHOOK_ENABLED` - Enable webhooks
- `NOTIFICATION_WEBHOOK_URLS` - Webhook URLs

### 🐛 Sentry Error Tracking
- `SENTRY_ENABLED` - Enable Sentry
- `SENTRY_DSN` - Sentry DSN
- `SENTRY_TRACES_SAMPLE_RATE` - Trace sampling rate
- `SENTRY_PROFILES_SAMPLE_RATE` - Profile sampling rate

### 💾 Caching
- `CACHE_ENABLED` - Enable caching
- `CACHE_DRIVER` - Cache driver (redis/file)
- `CACHE_TTL` - Cache time-to-live
- `CACHE_PREFIX` - Cache key prefix

### 📝 Logging
- `LOG_LEVEL` - Log level (info/debug/error)
- `LOG_PATH` - Log file directory

### 🤖 Bot Deployment
- `BOT_MAX_CONCURRENT` - Max concurrent bots
- `BOT_TIMEOUT` - Bot timeout (seconds)
- `BOT_MEMORY_LIMIT` - PHP memory limit
- `BOT_MAX_RETRIES` - Max retry attempts
- `BOT_ENABLE_QUEUE` - Enable job queue
- `BOT_QUEUE_DRIVER` - Queue driver
- `THREAD_TIMEOUT` - Thread timeout

### 🔒 Security
- `CREDENTIAL_ENCRYPTION_KEY` - Encryption key
- `ADMIN_SSE_TOKEN` - Admin SSE token
- `ALLOWED_IPS` - Allowed IP addresses

### ⏱️ Rate Limiting
- `RATE_LIMIT_ENABLED` - Enable rate limiting
- `RATE_LIMIT_REQUESTS` - Max requests
- `RATE_LIMIT_WINDOW` - Time window (seconds)

### ⚙️ Application
- `APP_ENV` - Environment (production/development)
- `APP_DEBUG` - Debug mode
- `APP_URL` - Application URL
- `APP_TIMEZONE` - Timezone (Pacific/Auckland)

### 📤 Upload Configuration
- `AI_UPLOAD_MAX_BYTES` - Max upload size
- `AI_UPLOAD_ALLOWED` - Allowed file extensions

### 🌐 HTTP Configuration
- `HTTP_FETCH_ALLOW_HOSTS` - Allowed fetch hosts

### 🏢 Business Configuration
- `BUSINESS_HOURS_START` - Business start hour
- `BUSINESS_HOURS_END` - Business end hour

### 🔧 Devkit
- `DEVKIT_ENTERPRISE_URL` - Devkit enterprise URL

### 💰 AI Pricing (NZD per 1000 tokens)
- `OPENAI_IN_NZD_PER_1K` - OpenAI input pricing
- `OPENAI_OUT_NZD_PER_1K` - OpenAI output pricing
- `ANTHROPIC_IN_NZD_PER_1K` - Anthropic input pricing
- `ANTHROPIC_OUT_NZD_PER_1K` - Anthropic output pricing

---

## 🚨 WHY CONNECTIONS FAIL - ROOT CAUSES

### 1. **Inconsistent Variable Names** (CRITICAL)
**Problem:** Different parts of the system expect different variable names.

**Examples:**
- Health monitor uses: `DB_USER`, `DB_PASSWORD`
- Some scripts use: `DB_USERNAME`, `DB_PASS`
- Others use: `DB_DATABASE` instead of `DB_NAME`

**Impact:** Variables not found → defaults to 'root'@'localhost' → Access denied

**Fix:** Standardize on ONE naming convention everywhere

### 2. **.env File Location Mismatch** (CRITICAL)
**Problem:** Scripts look for .env in different locations:
- `/public_html/mcp/.env` (doesn't exist)
- `/private_html/config/.env` (actual location)
- `/public_html/admin/intelligence-hub/.env` (doesn't exist)

**Impact:** .env not loaded → all values default → connection failures

**Fix:** Create symlinks or use consistent path resolution

### 3. **Missing Environment Variable Fallbacks**
**Problem:** Code like:
```php
$_ENV['DB_USERNAME'] ?? 'root'  // Dangerous default!
```

**Impact:** Silent failures with insecure defaults

**Fix:** Fail fast if critical vars missing

### 4. **putenv() Disabled on Server**
**Problem:** Server disables `putenv()` for security

**Impact:** Only `$_ENV` and `$_SERVER` work

**Fix:** Never use `putenv()`, always use `$_ENV`/`$_SERVER`

---

## ✅ RECOMMENDED SOLUTION

### Create Master .env File
**Location:** `/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env`

### Create Symlinks
```bash
ln -s /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env \
      /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env

ln -s /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env \
      /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/admin/intelligence-hub/.env
```

### Use Shared .env Loader
Create `/public_html/lib/EnvLoader.php`:
```php
<?php
class EnvLoader {
    private static $loaded = false;

    public static function load(): void {
        if (self::$loaded) return;

        $paths = [
            __DIR__ . '/../../../private_html/config/.env',
            __DIR__ . '/../../.env',
            __DIR__ . '/.env'
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $env = parse_ini_file($path);
                foreach ($env as $key => $value) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
                self::$loaded = true;
                return;
            }
        }

        throw new Exception('No .env file found!');
    }

    public static function get(string $key, $default = null) {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
```

### Standard Variable Access Pattern
```php
// At top of every script:
require_once __DIR__ . '/lib/EnvLoader.php';
EnvLoader::load();

// Then access variables:
$dbHost = EnvLoader::get('DB_HOST', '127.0.0.1');
$dbUser = EnvLoader::get('DB_USER') ?? EnvLoader::get('DB_USERNAME', 'root');
$dbPass = EnvLoader::get('DB_PASS') ?? EnvLoader::get('DB_PASSWORD', '');
$dbName = EnvLoader::get('DB_NAME') ?? EnvLoader::get('DB_DATABASE', 'hdgwrzntwa');
```

---

## 📋 MASTER .env TEMPLATE

See attached file: `master.env.template`

Copy to `/private_html/config/.env` and fill in all values.

---

## 🎯 ACTION ITEMS

1. ✅ **DONE:** Identified all 100+ environment variables
2. ✅ **DONE:** Analyzed naming inconsistencies
3. ✅ **DONE:** Fixed health monitor .env loading
4. ⏳ **TODO:** Create master .env file
5. ⏳ **TODO:** Create symlinks
6. ⏳ **TODO:** Create shared EnvLoader class
7. ⏳ **TODO:** Update all scripts to use EnvLoader
8. ⏳ **TODO:** Test all MCP endpoints
9. ⏳ **TODO:** Run full diagnostic suite

---

**Generated:** 2025-11-05
**Status:** Analysis Complete - Ready for Implementation
