# 09_TROUBLESHOOTING.md

**AI Agent + MCP Server - Troubleshooting Guide**

Common issues, debug techniques, and solutions for the AI Agent system.

---

## Table of Contents

1. [Common Errors](#common-errors)
2. [Debug Techniques](#debug-techniques)
3. [Log File Locations](#log-file-locations)
4. [Performance Issues](#performance-issues)
5. [Database Issues](#database-issues)
6. [Network Issues](#network-issues)
7. [FAQ](#faq)

---

## Common Errors

### FILE_NOT_FOUND

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "FILE_NOT_FOUND",
    "message": "File not found: assets/config.php"
  }
}
```

**Causes:**
- File path typo or incorrect spelling
- File doesn't exist at specified location
- Path is case-sensitive (Linux)

**Solutions:**
```bash
# Check if file exists
ls -la /home/master/applications/hdgwrzntwa/public_html/assets/config.php

# List directory contents to verify path
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{"tool":"fs.list","args":{"path":"assets"}}'

# Use correct relative path from DOCUMENT_ROOT
# Correct: "assets/config.php"
# Wrong: "/assets/config.php" (leading slash)
```

---

### DIR_NOT_FOUND

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "DIR_NOT_FOUND",
    "message": "Directory not found: logs"
  }
}
```

**Causes:**
- Directory doesn't exist
- Typo in directory name
- Missing parent directories

**Solutions:**
```bash
# Check directory exists
ls -ld /home/master/applications/hdgwrzntwa/public_html/logs

# Create directory if missing
mkdir -p /home/master/applications/hdgwrzntwa/public_html/logs

# Verify permissions
ls -ld /home/master/applications/hdgwrzntwa/public_html/logs
# Expected: drwxr-xr-x (755)
```

---

### PATH_OUTSIDE_ROOT

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "PATH_OUTSIDE_ROOT",
    "message": "Path is outside document root: /etc/passwd"
  }
}
```

**Causes:**
- Attempting to access file outside DOCUMENT_ROOT
- Using absolute paths (starting with `/`)
- Directory traversal attempt (`../../../`)
- Symlink escape attempt

**Solutions:**
```bash
# Use relative paths only
# Correct: "assets/config.php"
# Wrong: "/etc/passwd"
# Wrong: "../../../etc/passwd"

# If you need to access files outside public_html:
# - Move them to public_html or private_html
# - Or adjust FS_ROOT in .env (with caution)
```

**Security Note:** This error is **intentional** to prevent directory traversal attacks. Do not disable `secure_path()` validation.

---

### CHAT_FAILURE

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "CHAT_FAILURE",
    "message": "OpenAI API error: Incorrect API key provided"
  }
}
```

**Causes:**
- Missing API key in .env
- Incorrect API key format
- API key expired or revoked
- Network connectivity to provider

**Solutions:**

**For OpenAI:**
```bash
# Check .env file
grep OPENAI_API_KEY /home/master/applications/hdgwrzntwa/public_html/.env

# Verify key format (should start with sk-proj- or sk-)
# Example: sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Test API key manually
curl https://api.openai.com/v1/models \
  -H "Authorization: Bearer $OPENAI_API_KEY"

# If error: regenerate key at https://platform.openai.com/api-keys
```

**For Anthropic:**
```bash
# Check .env file
grep ANTHROPIC_API_KEY /home/master/applications/hdgwrzntwa/public_html/.env

# Verify key format (should start with sk-ant-)
# Example: sk-ant-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Test API key manually
curl https://api.anthropic.com/v1/models \
  -H "x-api-key: $ANTHROPIC_API_KEY" \
  -H "anthropic-version: 2023-06-01"

# If error: regenerate key at https://console.anthropic.com/
```

---

### TOOL_ERROR

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "TOOL_ERROR",
    "message": "Tool execution failed: Invalid arguments"
  }
}
```

**Causes:**
- Missing required arguments
- Wrong argument type (string vs array)
- Invalid argument values

**Solutions:**

**Check tool schema:**
```bash
# Get tool schema from MCP server
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"describe","params":{"tool":"fs.read"},"id":1}' | jq

# Review required arguments
```

**Common tool argument mistakes:**

```javascript
// fs.read - CORRECT
{
  "tool": "fs.read",
  "args": {
    "path": "assets/config.php"  // String
  }
}

// fs.read - WRONG (missing args)
{
  "tool": "fs.read"
}

// fs.list - CORRECT
{
  "tool": "fs.list",
  "args": {
    "path": "assets"  // String
  }
}

// db.select - CORRECT
{
  "tool": "db.select",
  "args": {
    "query": "SELECT * FROM ai_conversations LIMIT 10"  // String
  }
}

// db.exec - CORRECT (requires allow_write flag)
{
  "tool": "db.exec",
  "args": {
    "query": "UPDATE ai_conversations SET archived=1 WHERE id=?",
    "params": [123],  // Array
    "allow_write": true  // Boolean - REQUIRED
  }
}
```

---

### ONLY_SELECT_ALLOWED

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ONLY_SELECT_ALLOWED",
    "message": "Only SELECT queries are allowed in db.select"
  }
}
```

**Causes:**
- Trying to run UPDATE/INSERT/DELETE with `db.select`
- SQL injection attempt detected

**Solutions:**

```javascript
// For SELECT queries - use db.select
{
  "tool": "db.select",
  "args": {
    "query": "SELECT * FROM ai_conversations LIMIT 10"
  }
}

// For write queries - use db.exec with allow_write flag
{
  "tool": "db.exec",
  "args": {
    "query": "UPDATE ai_conversations SET archived=1 WHERE id=?",
    "params": [123],
    "allow_write": true  // REQUIRED for write operations
  }
}
```

**Security Note:** This separation prevents accidental or malicious data modification via `db.select`.

---

### ALLOW_WRITE_REQUIRED

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ALLOW_WRITE_REQUIRED",
    "message": "Write operations require allow_write flag"
  }
}
```

**Causes:**
- Using `db.exec` without `allow_write: true`
- Forgot to include the flag in request

**Solutions:**

```javascript
// WRONG - missing allow_write flag
{
  "tool": "db.exec",
  "args": {
    "query": "DELETE FROM ai_conversations WHERE archived=1"
  }
}

// CORRECT - includes allow_write flag
{
  "tool": "db.exec",
  "args": {
    "query": "DELETE FROM ai_conversations WHERE archived=1",
    "allow_write": true  // REQUIRED
  }
}
```

---

### HOST_NOT_ALLOWED

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "HOST_NOT_ALLOWED",
    "message": "Host not in allowlist: example.com"
  }
}
```

**Causes:**
- Trying to fetch from host not in allowlist
- Typo in hostname
- Missing HTTP_ALLOWED_HOSTS in .env

**Solutions:**

```bash
# Check current allowlist
grep HTTP_ALLOWED_HOSTS /home/master/applications/hdgwrzntwa/public_html/.env

# Add host to allowlist in .env
HTTP_ALLOWED_HOSTS=api.github.com,api.openai.com,api.anthropic.com,staff.vapeshed.co.nz,gpt.ecigdis.co.nz,example.com

# Reload PHP-FPM to pick up changes
sudo systemctl reload php8.1-fpm

# Test again
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "http.fetch",
    "args": {
      "url": "https://example.com/api/data"
    }
  }'
```

**Security Note:** Only add trusted domains to the allowlist. This prevents SSRF attacks.

---

### 401 Unauthorized

**Error:**
```json
{
  "error": "Unauthorized",
  "message": "Invalid or missing API key"
}
```

**Causes:**
- Missing `Authorization` header
- Wrong API key format
- Incorrect MCP_API_KEY in .env
- Development mode disabled

**Solutions:**

**Check MCP_API_KEY in .env:**
```bash
grep MCP_API_KEY /home/master/applications/hdgwrzntwa/public_html/.env

# If empty - authentication disabled (development mode)
# MCP_API_KEY=

# If set - authentication required
# MCP_API_KEY=your_64_character_hex_key_here
```

**Test with correct Authorization header:**
```bash
# Get API key from .env
API_KEY=$(grep MCP_API_KEY /home/master/applications/hdgwrzntwa/public_html/.env | cut -d'=' -f2)

# Test request with Bearer token
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}'

# Expected: 200 OK with tool list
```

**Disable authentication for testing (NOT RECOMMENDED IN PRODUCTION):**
```bash
# Edit .env
nano /home/master/applications/hdgwrzntwa/public_html/.env

# Set MCP_API_KEY to empty
MCP_API_KEY=

# Reload PHP-FPM
sudo systemctl reload php8.1-fpm
```

---

### 404 Not Found

**Error:**
```html
<html>
<head><title>404 Not Found</title></head>
<body>
<h1>404 Not Found</h1>
</body>
</html>
```

**Causes:**
- Wrong endpoint URL
- Typo in path
- File doesn't exist
- Nginx/Apache misconfiguration

**Solutions:**

**Verify endpoint URL:**
```bash
# List available endpoints
ls -la /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/api/
ls -la /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/mcp/

# Correct URLs:
# https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php
# https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php
# https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php
```

**Test with curl verbose:**
```bash
curl -v https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php

# Look for:
# < HTTP/1.1 200 OK  (good)
# < HTTP/1.1 404 Not Found  (bad - check URL)
```

---

### Claude 404 Model Not Found

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "CHAT_FAILURE",
    "message": "Anthropic API error: model: claude-3-5-sonnet-20241022 not found"
  }
}
```

**Causes:**
- Model name incorrect or deprecated
- ANTHROPIC_API_KEY invalid or insufficient permissions
- Model not available in your region/plan

**Solutions:**

**Try different model name:**
```bash
# Edit .env to use different Claude model
nano /home/master/applications/hdgwrzntwa/public_html/.env

# Try these models (in order):
ANTHROPIC_MODEL=claude-3-5-sonnet-20240620
# or
ANTHROPIC_MODEL=claude-3-sonnet-20240229
# or
ANTHROPIC_MODEL=claude-3-opus-20240229

# Reload PHP-FPM
sudo systemctl reload php8.1-fpm
```

**Test API key and available models:**
```bash
# Get API key
API_KEY=$(grep ANTHROPIC_API_KEY /home/master/applications/hdgwrzntwa/public_html/.env | cut -d'=' -f2)

# Test API key (should return 200 with models list)
curl https://api.anthropic.com/v1/models \
  -H "x-api-key: $API_KEY" \
  -H "anthropic-version: 2023-06-01"

# If 401 Unauthorized: API key invalid
# If 200 OK: Check "id" field in response for valid model names
```

**Use OpenAI instead:**
```bash
# OpenAI GPT-4o-mini is confirmed working
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "test",
    "message": "Hello"
  }'
```

---

## Debug Techniques

### 1. Check Logs with logs.tail Tool

**Use the built-in logs.tail tool:**
```bash
# Tail Apache error log
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "logs.tail",
    "args": {
      "path": "logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log",
      "lines": 50
    }
  }' | jq -r '.data.result.content'
```

### 2. Query Recent Tool Calls

**Check tool execution history:**
```bash
mysql -u hdgwrzntwa -p hdgwrzntwa -e "
SELECT
  id,
  tool_name,
  status,
  error_message,
  latency_ms,
  created_at
FROM ai_tool_calls
WHERE created_at > NOW() - INTERVAL 1 HOUR
ORDER BY created_at DESC
LIMIT 20;
"
```

### 3. Query Failed Requests

**Find recent errors:**
```bash
mysql -u hdgwrzntwa -p hdgwrzntwa -e "
SELECT
  request_id,
  status_code,
  error_code,
  error_message,
  latency_ms,
  created_at
FROM ai_agent_requests
WHERE status_code >= 400
  AND created_at > NOW() - INTERVAL 1 HOUR
ORDER BY created_at DESC
LIMIT 20;
"
```

### 4. Verify .env Loaded

**Check environment variables:**
```php
<?php
// Create test file: test_env.php
require_once __DIR__ . '/app.php';

echo "Environment variables:\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
echo "OPENAI_API_KEY: " . (isset($_ENV['OPENAI_API_KEY']) ? 'SET (length: ' . strlen($_ENV['OPENAI_API_KEY']) . ')' : 'NOT SET') . "\n";
echo "MCP_API_KEY: " . (isset($_ENV['MCP_API_KEY']) ? 'SET (length: ' . strlen($_ENV['MCP_API_KEY']) . ')' : 'NOT SET') . "\n";
```

```bash
# Run test
php /home/master/applications/hdgwrzntwa/public_html/test_env.php

# Expected output:
# DB_HOST: gpt.ecigdis.co.nz
# DB_NAME: hdgwrzntwa
# OPENAI_API_KEY: SET (length: 52)
# MCP_API_KEY: SET (length: 64)
```

### 5. Test Endpoints with Verbose cURL

**Get detailed request/response info:**
```bash
# Verbose mode shows headers, timing, SSL info
curl -v -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php

# Output includes:
# * Connected to gpt.ecigdis.co.nz
# * SSL connection using TLSv1.3
# > POST /assets/services/ai-agent/api/healthz.php HTTP/1.1
# < HTTP/1.1 200 OK
# < Content-Type: application/json
# {"alive":true,...}
```

### 6. Check PHP Error Log

**View recent PHP errors:**
```bash
# Apache error log
tail -100 /home/master/applications/hdgwrzntwa/logs/apache_phpstack-*.error.log

# Look for:
# [Sat Nov 02 12:00:00.000000 2025] [php:error] [pid 12345] [client x.x.x.x:xxxxx] PHP Fatal error: ...
# [Sat Nov 02 12:00:00.000000 2025] [php:warn] [pid 12345] [client x.x.x.x:xxxxx] PHP Warning: ...
```

### 7. Test Database Connection

**Verify database connectivity:**
```bash
# Test connection
mysql -u hdgwrzntwa -p'your_password' hdgwrzntwa -e "SELECT 1;"

# If error: check credentials in .env
grep DB_ /home/master/applications/hdgwrzntwa/public_html/.env

# Test with credentials from .env
DB_USER=$(grep DB_USER /home/master/applications/hdgwrzntwa/public_html/.env | cut -d'=' -f2)
DB_PASS=$(grep DB_PASS /home/master/applications/hdgwrzntwa/public_html/.env | cut -d'=' -f2)
DB_NAME=$(grep DB_NAME /home/master/applications/hdgwrzntwa/public_html/.env | cut -d'=' -f2)
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT COUNT(*) FROM ai_conversations;"
```

### 8. Check PHP Syntax

**Validate PHP files before deployment:**
```bash
# Check single file
php -l /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/api/chat.php

# Check all PHP files
find /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent -name "*.php" -exec php -l {} \;

# Expected: "No syntax errors detected"
```

### 9. Enable Debug Mode

**Temporary debug output (DEVELOPMENT ONLY):**
```php
<?php
// Add to top of problematic file (e.g., chat.php)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Add debug output
var_dump($_POST);
var_dump($_ENV);

// REMEMBER TO REMOVE AFTER DEBUGGING
```

**⚠️ WARNING:** Never enable `display_errors` in production. Use logs instead.

### 10. Test with Minimal Example

**Create minimal test endpoint:**
```php
<?php
// Create: test_minimal.php
require_once __DIR__ . '/app.php';

header('Content-Type: application/json');

try {
    // Test database
    $db = Bootstrap::getDbConnection();
    $stmt = $db->query("SELECT COUNT(*) as count FROM ai_conversations");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'database' => 'connected',
        'conversation_count' => $count['count'],
        'env_loaded' => isset($_ENV['DB_HOST']),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

```bash
# Test it
curl -s https://gpt.ecigdis.co.nz/test_minimal.php | jq
```

---

## Log File Locations

### Application Logs

```bash
# Apache error log (PHP errors, warnings, notices)
/home/master/applications/hdgwrzntwa/logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log

# Apache access log (HTTP requests)
/home/master/applications/hdgwrzntwa/logs/apache_phpstack-129337-5615757.cloudwaysapps.com.access.log

# Nginx error log
/home/master/applications/hdgwrzntwa/logs/nginx-app.error.log

# Nginx access log
/home/master/applications/hdgwrzntwa/logs/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log

# PHP-FPM access log
/home/master/applications/hdgwrzntwa/logs/php-app.access.log

# PHP-FPM slow log
/home/master/applications/hdgwrzntwa/logs/php-app.slow.log

# Application-specific logs (if created)
/home/master/applications/hdgwrzntwa/private_html/logs/*.log
```

### Viewing Logs

```bash
# Tail Apache error log (follow new entries)
tail -f /home/master/applications/hdgwrzntwa/logs/apache_phpstack-*.error.log

# View last 100 lines
tail -100 /home/master/applications/hdgwrzntwa/logs/apache_phpstack-*.error.log

# Search for specific error
grep "CHAT_FAILURE" /home/master/applications/hdgwrzntwa/logs/apache_phpstack-*.error.log

# Search for errors in last hour
find /home/master/applications/hdgwrzntwa/logs -name "*.log" -mmin -60 -exec grep -H "error" {} \;
```

---

## Performance Issues

### Slow Requests (> 2 seconds)

**Identify slow requests:**
```sql
SELECT
  request_id,
  latency_ms,
  created_at,
  error_message
FROM ai_agent_requests
WHERE latency_ms > 2000
  AND created_at > NOW() - INTERVAL 1 DAY
ORDER BY latency_ms DESC
LIMIT 20;
```

**Common causes:**
1. **Slow AI provider response**
   - OpenAI/Anthropic API latency
   - Large context or response
   - Provider rate limiting

2. **Slow database queries**
   - Missing indexes
   - Large result sets
   - Complex joins

3. **Slow tool execution**
   - Large file reads
   - Network timeouts (http.fetch)
   - Slow external APIs

**Solutions:**

**Optimize database queries:**
```sql
-- Add indexes for common queries
CREATE INDEX idx_created_at ON ai_agent_requests(created_at);
CREATE INDEX idx_session_key ON ai_conversations(session_key);
CREATE INDEX idx_tool_status ON ai_tool_calls(tool_name, status);

-- Check query performance
EXPLAIN SELECT * FROM ai_conversations WHERE session_key = 'test';
```

**Monitor tool latency:**
```sql
SELECT
  tool_name,
  AVG(latency_ms) as avg_latency,
  MAX(latency_ms) as max_latency,
  COUNT(*) as call_count
FROM ai_tool_calls
WHERE created_at > NOW() - INTERVAL 1 DAY
GROUP BY tool_name
ORDER BY avg_latency DESC;
```

### High Memory Usage

**Check PHP memory limit:**
```bash
php -i | grep memory_limit
# Expected: memory_limit => 256M => 256M

# Increase if needed (in php.ini)
memory_limit = 512M
```

**Monitor memory usage in code:**
```php
// Add to problematic code
error_log('Memory usage: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB');
```

---

## Database Issues

### Too Many Connections

**Error:**
```
SQLSTATE[HY000] [1040] Too many connections
```

**Check current connections:**
```sql
SHOW PROCESSLIST;
SHOW STATUS LIKE 'Threads_connected';
SHOW VARIABLES LIKE 'max_connections';
```

**Solutions:**
```sql
-- Increase max connections (in my.cnf)
max_connections = 200

-- Or use connection pooling in application
```

### Slow Queries

**Enable slow query log:**
```sql
-- Check if enabled
SHOW VARIABLES LIKE 'slow_query_log';

-- Enable it
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; -- Log queries > 1 second

-- Check log location
SHOW VARIABLES LIKE 'slow_query_log_file';
```

**Analyze slow queries:**
```bash
# View slow query log
tail -50 /var/lib/mysql/mysql-slow.log

# Or use logs.tail tool
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "logs.tail",
    "args": {
      "path": "logs/php-app.slow.log",
      "lines": 50
    }
  }'
```

---

## Network Issues

### HTTPS Redirect Not Working

**Test HTTP request:**
```bash
curl -I http://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php

# Expected:
# HTTP/1.1 301 Moved Permanently
# Location: https://gpt.ecigdis.co.nz/...
```

**If not redirecting:**
```bash
# Check Nginx config
sudo nginx -t
cat /etc/nginx/conf.d/force-https.conf

# Reload Nginx
sudo systemctl reload nginx
```

### SSL Certificate Issues

**Test SSL certificate:**
```bash
# Check certificate validity
openssl s_client -connect gpt.ecigdis.co.nz:443 -servername gpt.ecigdis.co.nz < /dev/null

# Look for:
# Verify return code: 0 (ok)  <- Good
# Verify return code: 20 (unable to get local issuer certificate)  <- Bad
```

**Check certificate expiration:**
```bash
echo | openssl s_client -connect gpt.ecigdis.co.nz:443 -servername gpt.ecigdis.co.nz 2>/dev/null | openssl x509 -noout -dates

# Output:
# notBefore=Nov 01 00:00:00 2024 GMT
# notAfter=Nov 01 23:59:59 2025 GMT
```

### Connection Timeout

**Error:**
```
curl: (28) Connection timed out after 10000 milliseconds
```

**Causes:**
- Firewall blocking requests
- Server overloaded
- Network issues

**Solutions:**
```bash
# Check server load
uptime
top

# Check firewall rules
sudo iptables -L

# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Increase timeout in curl
curl --max-time 60 https://gpt.ecigdis.co.nz/...
```

---

## FAQ

### Q: Why are my tool calls failing with "UNKNOWN_TOOL"?

**A:** The tool doesn't exist in the MCP server registry. Check available tools:

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}' | jq '.result.tools[].name'

# Expected output:
# "fs.read"
# "fs.list"
# "fs.write"
# "db.select"
# "db.exec"
# "logs.tail"
# "http.fetch"
# "devkit"
```

---

### Q: Why is Claude returning 404 model not found?

**A:** Model name is incorrect or API key has insufficient permissions. Try:

1. **Use different model name** in .env:
   ```bash
   ANTHROPIC_MODEL=claude-3-5-sonnet-20240620
   ```

2. **Verify API key** has model access:
   ```bash
   curl https://api.anthropic.com/v1/models \
     -H "x-api-key: YOUR_KEY" \
     -H "anthropic-version: 2023-06-01"
   ```

3. **Use OpenAI instead** (confirmed working):
   ```bash
   # In your request
   {
     "provider": "openai",
     "model": "gpt-4o-mini",
     ...
   }
   ```

---

### Q: How do I debug slow requests?

**A:** Use these queries to identify bottlenecks:

```sql
-- Slowest requests in last 24 hours
SELECT
  request_id,
  latency_ms,
  created_at,
  error_message
FROM ai_agent_requests
WHERE created_at > NOW() - INTERVAL 1 DAY
ORDER BY latency_ms DESC
LIMIT 10;

-- Slowest tool calls
SELECT
  tool_name,
  AVG(latency_ms) as avg_latency,
  MAX(latency_ms) as max_latency
FROM ai_tool_calls
WHERE created_at > NOW() - INTERVAL 1 DAY
GROUP BY tool_name
ORDER BY avg_latency DESC;

-- Requests with most tool calls (potential optimization target)
SELECT
  ar.request_id,
  COUNT(tc.id) as tool_count,
  ar.latency_ms
FROM ai_agent_requests ar
JOIN ai_tool_calls tc ON ar.id = tc.request_id
WHERE ar.created_at > NOW() - INTERVAL 1 DAY
GROUP BY ar.request_id
ORDER BY tool_count DESC
LIMIT 10;
```

---

### Q: How do I enable/disable authentication?

**A:** Control authentication via MCP_API_KEY in .env:

**Disable (development mode):**
```bash
# .env
MCP_API_KEY=
```

**Enable (production):**
```bash
# .env
MCP_API_KEY=your_64_character_hex_key_here

# Generate key:
openssl rand -hex 32
```

**Reload PHP-FPM after changes:**
```bash
sudo systemctl reload php8.1-fpm
```

---

### Q: Why can't I write files with fs.write?

**A:** The fs.write tool is marked **HIGH RISK** and may require special permissions. Check:

1. **File permissions:**
   ```bash
   ls -la /home/master/applications/hdgwrzntwa/public_html/assets/
   # Needs write permission for www-data user
   ```

2. **Path validation:**
   - Must be within DOCUMENT_ROOT
   - Use relative paths only
   - Cannot use absolute paths or `../`

3. **Tool implementation:**
   ```bash
   # Check if fs.write is implemented
   grep -n "case 'fs.write'" /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/mcp/server_v3.php
   ```

---

### Q: How do I monitor system health?

**A:** Use these health check endpoints:

```bash
# Liveness check (is server alive?)
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php | jq

# Readiness check (is server ready to handle requests?)
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq

# Check database health
mysql -u hdgwrzntwa -p hdgwrzntwa -e "
SELECT
  COUNT(*) as total_requests,
  SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) as successful,
  SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors,
  AVG(latency_ms) as avg_latency
FROM ai_agent_requests
WHERE created_at > NOW() - INTERVAL 1 HOUR;
"
```

---

### Q: Where are conversation histories stored?

**A:** Conversations are stored in the database:

```sql
-- Get conversation
SELECT * FROM ai_conversations WHERE session_key = 'your-session-key';

-- Get messages for conversation
SELECT
  cm.role,
  cm.content,
  cm.created_at
FROM ai_conversation_messages cm
JOIN ai_conversations c ON cm.conversation_id = c.id
WHERE c.session_key = 'your-session-key'
ORDER BY cm.created_at ASC;

-- Get conversation with token usage
SELECT
  c.session_key,
  c.provider,
  c.model,
  SUM(c.input_tokens) as total_input_tokens,
  SUM(c.output_tokens) as total_output_tokens,
  SUM(c.cost) as total_cost
FROM ai_conversations c
WHERE c.session_key = 'your-session-key'
GROUP BY c.id;
```

---

### Q: How do I clear old logs and data?

**A:** Use retention policies and cleanup scripts:

```sql
-- Delete old agent requests (> 90 days)
DELETE FROM ai_agent_requests WHERE created_at < NOW() - INTERVAL 90 DAY;

-- Delete old tool calls (> 90 days)
DELETE FROM ai_tool_calls WHERE created_at < NOW() - INTERVAL 90 DAY;

-- Delete old conversations (> 1 year)
DELETE FROM ai_conversations WHERE created_at < NOW() - INTERVAL 1 YEAR;

-- Vacuum tables to reclaim space
OPTIMIZE TABLE ai_agent_requests;
OPTIMIZE TABLE ai_tool_calls;
OPTIMIZE TABLE ai_conversations;
```

**Or use automated cron job:**
```bash
# /etc/cron.daily/cleanup-ai-logs
#!/bin/bash
mysql -u hdgwrzntwa -p'password' hdgwrzntwa <<EOF
DELETE FROM ai_agent_requests WHERE created_at < NOW() - INTERVAL 90 DAY;
DELETE FROM ai_tool_calls WHERE created_at < NOW() - INTERVAL 90 DAY;
OPTIMIZE TABLE ai_agent_requests;
OPTIMIZE TABLE ai_tool_calls;
EOF
```

---

## Getting More Help

If you've tried these troubleshooting steps and still have issues:

1. **Check documentation:**
   - [03_AI_AGENT_ENDPOINTS.md](03_AI_AGENT_ENDPOINTS.md) - API reference
   - [05_TOOLS_REFERENCE.md](05_TOOLS_REFERENCE.md) - Tool documentation
   - [07_SECURITY.md](07_SECURITY.md) - Security guide
   - [08_DEPLOYMENT.md](08_DEPLOYMENT.md) - Deployment guide

2. **Review logs:**
   - Apache error log: `logs/apache_phpstack-*.error.log`
   - Nginx error log: `logs/nginx-app.error.log`
   - Application logs: `private_html/logs/*.log`

3. **Run smoke tests:**
   ```bash
   php assets/services/ai-agent/mcp/tests/smoke_test.php
   ```

4. **Check system health:**
   ```bash
   curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq
   ```

5. **Query recent errors:**
   ```sql
   SELECT * FROM ai_agent_requests
   WHERE status_code >= 400
   ORDER BY created_at DESC
   LIMIT 10;
   ```

---

**See Also:**
- [01_SYSTEM_OVERVIEW.md](01_SYSTEM_OVERVIEW.md) - System architecture
- [08_DEPLOYMENT.md](08_DEPLOYMENT.md) - Deployment procedures
- [10_API_EXAMPLES.md](10_API_EXAMPLES.md) - Working code examples
