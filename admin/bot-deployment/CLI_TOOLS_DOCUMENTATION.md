# CLI Tools Documentation

## Overview

The Bot Deployment Management System includes four production-ready CLI tools for managing bots from the command line.

---

## 1. bot-deploy.php

**Purpose**: Interactive CLI tool for creating and deploying bots

### Usage

```bash
# Interactive mode (guided prompts)
php bot-deploy.php

# Non-interactive mode with arguments
php bot-deploy.php --name="Security Bot" --role=security --schedule="0 * * * *"
```

### Available Arguments

- `--name` - Bot name (default: "My Bot")
- `--role` - Bot role: security, developer, analyst, monitor, general (default: general)
- `--prompt` - System prompt text (default: generic)
- `--schedule` - Cron expression for scheduling (optional)
- `--status` - Initial status: active or paused (default: active)
- `--model` - AI model to use (default: gpt-5-turbo)
- `--temperature` - Temperature 0.0-1.0 (default: 0.7)

### Interactive Mode Flow

1. **Bot Name** - Enter unique bot name (min 3 chars)
2. **Bot Role** - Choose from: security, developer, analyst, monitor, general
3. **System Prompt** - Multi-line prompt (press ENTER twice to finish)
4. **Schedule** - Optional cron expression with validation
5. **Status** - active or paused
6. **Configuration** - Model, temperature, multi-threading options
7. **Confirmation** - Review and confirm creation

### Features

- ✅ Real-time cron validation
- ✅ Cron expression description (e.g., "Every hour")
- ✅ Multi-threading configuration
- ✅ Input validation with helpful error messages
- ✅ Color-coded CLI output
- ✅ Database integration with BotRepository
- ✅ Automatic schedule calculation

### Examples

```bash
# Create a security bot that runs every hour
php bot-deploy.php --name="Security Auditor" --role=security \
  --prompt="Perform security audits on CIS modules" \
  --schedule="0 * * * *" --status=active

# Create a developer bot with multi-threading
php bot-deploy.php --name="Code Analyzer" --role=developer \
  --model="gpt-5-turbo" --temperature=0.3
```

### Output

```
==============================================================
Bot Deployment Tool
==============================================================

ℹ Starting interactive bot creation...

Bot Name [My Bot]: Security Auditor
ℹ Available roles: security, developer, analyst, monitor, general
Bot Role [general]: security
...
✓ Bot created successfully!
ℹ Bot ID: 5
ℹ Bot Name: Security Auditor
ℹ Next execution: 2024-01-15 14:00:00
```

---

## 2. bot-execute.php

**Purpose**: Execute bots manually from command line

### Usage

```bash
php bot-execute.php <bot_id> "<input>" [--multi-thread]
```

### Arguments

- `bot_id` - Bot ID to execute (required)
- `input` - Input text for the bot (required, quoted)
- `--multi-thread` or `-m` - Enable multi-threaded execution (optional)

### Features

- ✅ Single-threaded execution
- ✅ Multi-threaded execution (2-6 threads)
- ✅ Real-time progress display
- ✅ Execution timing
- ✅ Detailed output display
- ✅ Metadata reporting (threads, tools used, etc.)
- ✅ Error handling with stack traces (--debug flag)

### Examples

```bash
# Execute bot #1 with simple input
php bot-execute.php 1 "Analyze the consignment module"

# Execute with multi-threading
php bot-execute.php 2 "Perform full security audit" --multi-thread

# Debug mode
php bot-execute.php 3 "Test query" --debug
```

### Output

```
==============================================================
Bot Execution
==============================================================

ℹ Bot ID: 1
ℹ Input: Analyze the consignment module
ℹ Mode: Single-threaded

ℹ Loading bot...
✓ Bot loaded: Security Auditor
ℹ Role: security
ℹ Status: active

ℹ Executing bot...

✓ Execution complete!
ℹ Execution ID: exec_abc123
ℹ Duration: 2345.67ms
ℹ Mode: single-threaded

==============================================================
Bot Output
==============================================================

[Bot's response appears here...]

==============================================================
Execution Metadata
==============================================================

ℹ tools_used: ["semantic_search", "db.query", "fs.read"]
ℹ total_tokens: 1234
✓ Bot execution completed successfully!
```

---

## 3. scheduler.php

**Purpose**: Cron job entry point for executing scheduled bots

### Usage

```bash
# Manual execution
php scheduler.php

# Add to system crontab (runs every minute)
* * * * * php /path/to/scheduler.php >> /path/to/scheduler.log 2>&1
```

### How It Works

1. Runs every minute (via cron)
2. Queries database for bots with `next_execution_at <= NOW()`
3. Executes each due bot
4. Updates `next_execution_at` based on cron expression
5. Logs all activities to stdout (redirect to log file)

### Features

- ✅ Automatic bot discovery
- ✅ Parallel execution of multiple due bots
- ✅ Automatic next execution calculation
- ✅ Error handling with retry prevention
- ✅ Structured JSON logging
- ✅ Exit codes (0 = success, 1 = failures occurred)

### Log Output

```
[2024-01-15 13:45:00] [INFO] Scheduler started
[2024-01-15 13:45:00] [INFO] Found due bots {"count":2}
[2024-01-15 13:45:00] [INFO] Executing bot {"bot_id":1,"bot_name":"Security Auditor","schedule":"0 * * * *"}
[2024-01-15 13:45:02] [INFO] Bot executed successfully {"bot_id":1,"execution_id":"exec_abc123","mode":"single-threaded"}
[2024-01-15 13:45:02] [INFO] Executing bot {"bot_id":2,"bot_name":"Monitor Bot","schedule":"*/5 * * * *"}
[2024-01-15 13:45:04] [INFO] Bot executed successfully {"bot_id":2,"execution_id":"exec_def456","mode":"single-threaded"}
[2024-01-15 13:45:04] [INFO] Scheduler completed {"total":2,"success":2,"failed":0}
```

### Crontab Setup

```bash
# Edit crontab
crontab -e

# Add this line (adjust paths)
* * * * * php /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/admin/bot-deployment/scheduler.php >> /var/log/bot-scheduler.log 2>&1
```

---

## 4. health-check.php

**Purpose**: Comprehensive system diagnostics

### Usage

```bash
# Basic health check
php health-check.php

# Verbose mode (detailed output)
php health-check.php --verbose

# Debug mode (with stack traces)
php health-check.php --debug
```

### Checks Performed

1. **PHP Version** - Verifies PHP >= 7.4
2. **PHP Extensions** - Checks: pdo, pdo_mysql, json, curl, mbstring
3. **Database Connection** - Tests PDO connection
4. **Database Tables** - Verifies all 7 required tables exist
5. **File Permissions** - Checks logs/, cache/, uploads/ are writable
6. **MCP Server** - Tests connection to Intelligence Hub
7. **Memory Usage** - Reports current usage vs. limit

### Features

- ✅ Color-coded results (green=pass, red=fail, yellow=warning)
- ✅ Detailed error messages
- ✅ Verbose mode for debugging
- ✅ Exit codes (0=pass, 1=fail)
- ✅ JSON-structured detail reporting

### Output Example

```
==============================================================
System Health Check
==============================================================

ℹ Checking PHP version...
✓ PHP 8.1.15

ℹ Checking PHP extensions...
✓ All extensions loaded: pdo, pdo_mysql, json, curl, mbstring

ℹ Checking database connection...
✓ Database connected - mysql 8.0.32

ℹ Checking database tables...
✓ All required tables exist (7 tables)

ℹ Checking file permissions...
✓ All directories writable

ℹ Checking MCP server connection...
✓ MCP server connected

ℹ Checking memory usage...
ℹ Memory Usage: 4.52MB used (limit: 256M)

==============================================================
Health Check Summary
==============================================================

✓ PHP Version: PHP 8.1.15
✓ PHP Extensions: All required extensions loaded
✓ Database: Connected
✓ Database Tables: All tables exist
✓ File Permissions: All directories writable
✓ MCP Server: Connected
ℹ Memory Usage: 4.52MB used (limit: 256M)

ℹ Total checks: 7
✓ Passed: 6
⚠ Warnings: 0
✗ Failed: 0

✓ System health check PASSED
```

### Verbose Mode

Adds detailed information for each check:

```bash
php health-check.php --verbose
```

```
✓ Database: Connected
  connection_count: 1
  max_connections: 151
  server_version: 8.0.32
  driver: mysql
```

---

## General Features (All Tools)

### Color Coding

- 🟢 **Green (✓)** - Success/Pass
- 🔴 **Red (✗)** - Error/Fail
- 🟡 **Yellow (⚠)** - Warning
- 🔵 **Cyan (ℹ)** - Information

### Error Handling

All tools include:
- Try-catch blocks for all operations
- Meaningful error messages
- Optional debug mode (--debug flag)
- Proper exit codes

### Exit Codes

- `0` - Success
- `1` - Failure/Error

---

## Integration with Existing System

All CLI tools integrate seamlessly with the existing architecture:

- **Config**: Uses `Config::getInstance()`
- **Database**: Uses `Connection::getInstance()`
- **Repositories**: Uses `BotRepository`, `SessionRepository`
- **Services**: Uses `BotExecutionService`, `SchedulerService`, `AIAgentService`

---

## Testing the CLI Tools

```bash
# 1. Test health check first
php health-check.php --verbose

# 2. Create a test bot
php bot-deploy.php

# 3. Execute the bot manually
php bot-execute.php 1 "Hello, test execution"

# 4. Test scheduler (manual run)
php scheduler.php
```

---

## Troubleshooting

### Permission Denied

```bash
chmod +x bot-deploy.php bot-execute.php scheduler.php health-check.php
```

### PHP Not Found

Use full path:
```bash
/usr/bin/php bot-deploy.php
```

### Database Connection Failed

Check `.env` file:
```bash
cat config/.env
```

### MCP Server Unreachable

Verify URL and API key in `config/config.php`

---

## Production Deployment Checklist

- [ ] Make all scripts executable (`chmod +x`)
- [ ] Test each tool manually
- [ ] Run `health-check.php` and verify all checks pass
- [ ] Add `scheduler.php` to crontab
- [ ] Set up log rotation for scheduler logs
- [ ] Document bot deployment process for team
- [ ] Create monitoring for scheduler execution
- [ ] Set up alerts for failed health checks

---

**Bot Deployment Management System v1.0.0**
**CLI Tools - Production Ready**
