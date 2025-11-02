# 08_DEPLOYMENT.md

**AI Agent + MCP Server - Deployment Guide**

Complete guide for deploying the AI Agent system to production servers.

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Nginx Configuration](#nginx-configuration)
3. [Environment Variables](#environment-variables)
4. [Database Setup](#database-setup)
5. [File Permissions](#file-permissions)
6. [Smoke Tests](#smoke-tests)
7. [Deployment Checklist](#deployment-checklist)
8. [Rollback Procedure](#rollback-procedure)
9. [Post-Deployment Monitoring](#post-deployment-monitoring)

---

## Server Requirements

### Minimum Requirements

**PHP:**
- Version: 8.1.33 or higher
- Extensions required:
  - `pdo_mysql` - Database connectivity
  - `json` - JSON encoding/decoding
  - `mbstring` - Multi-byte string handling
  - `curl` - HTTP requests (for http.fetch tool)
  - `openssl` - Secure connections

**Database:**
- MySQL 5.7+ or MariaDB 10.3+
- InnoDB storage engine
- UTF-8 (utf8mb4) character set
- Foreign key constraints enabled

**Web Server:**
- Nginx 1.18+ (recommended) or Apache 2.4+
- HTTPS/TLS 1.2+ support
- SSE (Server-Sent Events) support for streaming

**System:**
- Linux (Ubuntu 20.04+, Debian 11+, or similar)
- Minimum 2GB RAM
- Minimum 10GB disk space
- Cron for scheduled tasks

### Recommended Requirements

- PHP 8.2+ with JIT compiler
- MariaDB 10.6+ with improved JSON support
- 4GB+ RAM for production workloads
- SSD storage for database
- Redis for rate limiting (future feature)

### Cloudways-Specific

If deploying on Cloudways:
- Use "PHP Stack" application
- Select PHP 8.1 or 8.2
- Enable SSH/SFTP access
- Configure automated backups (daily recommended)
- Set up staging environment

---

## Nginx Configuration

### HTTPS Redirect

Force all traffic to HTTPS:

```nginx
# /etc/nginx/conf.d/force-https.conf
server {
    listen 80;
    server_name gpt.ecigdis.co.nz;

    # Redirect all HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}
```

### SSL/TLS Configuration

```nginx
server {
    listen 443 ssl http2;
    server_name gpt.ecigdis.co.nz;

    # SSL Certificate paths
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;

    # SSL Protocols and Ciphers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers 'ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384';
    ssl_prefer_server_ciphers on;

    # SSL Session
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # HSTS Header
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Security Headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Document root
    root /home/master/applications/hdgwrzntwa/public_html;
    index index.php index.html;

    # PHP-FPM configuration
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_read_timeout 300;
    }

    # Streaming endpoint configuration
    location /assets/services/ai-agent/api/chat_stream.php {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

        # SSE-specific headers
        fastcgi_buffering off;
        proxy_buffering off;

        # Keep connection alive
        fastcgi_read_timeout 300;
        keepalive_timeout 300;

        # Disable gzip for streaming
        gzip off;

        # Add SSE headers
        add_header Content-Type text/event-stream;
        add_header Cache-Control no-cache;
        add_header Connection keep-alive;
        add_header X-Accel-Buffering no;
    }

    # Static file caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 7d;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    # Deny access to sensitive files
    location ~ /\.(?!well-known) {
        deny all;
    }

    location ~ ^/(private_html|conf|logs|tmp) {
        deny all;
    }
}
```

### Test Nginx Configuration

```bash
# Test configuration syntax
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx

# Check status
sudo systemctl status nginx
```

---

## Environment Variables

### Complete .env File

Create `/home/master/applications/hdgwrzntwa/public_html/.env`:

```bash
# ============================================================================
# DATABASE CONFIGURATION
# ============================================================================

# MySQL/MariaDB connection details
DB_HOST=gpt.ecigdis.co.nz
DB_NAME=hdgwrzntwa
DB_USER=hdgwrzntwa
DB_PASS=your_secure_database_password_here

# Character set (use utf8mb4 for full Unicode support)
DB_CHARSET=utf8mb4

# ============================================================================
# AI PROVIDER CONFIGURATION
# ============================================================================

# OpenAI API Configuration
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
OPENAI_MODEL=gpt-4o-mini
OPENAI_MAX_TOKENS=16384
OPENAI_TEMPERATURE=0.7

# Anthropic API Configuration
ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
ANTHROPIC_MAX_TOKENS=8192
ANTHROPIC_TEMPERATURE=0.7

# ============================================================================
# MCP SERVER AUTHENTICATION
# ============================================================================

# MCP API Key for authentication
# Generate with: openssl rand -hex 32
# Leave empty to disable authentication (development only)
MCP_API_KEY=your_64_character_hex_api_key_here_generate_with_openssl_rand_hex_32

# Alternative: JWT secret for future token-based auth
# MCP_JWT_SECRET=your_jwt_secret_here

# ============================================================================
# APPLICATION CONFIGURATION
# ============================================================================

# Environment mode
APP_ENV=production
APP_DEBUG=false

# Base URL
APP_URL=https://gpt.ecigdis.co.nz

# Logging
LOG_LEVEL=info
LOG_PATH=/home/master/applications/hdgwrzntwa/private_html/logs

# ============================================================================
# TOOL CONFIGURATION
# ============================================================================

# HTTP Fetch Tool - Allowed hosts (comma-separated)
HTTP_ALLOWED_HOSTS=api.github.com,api.openai.com,api.anthropic.com,staff.vapeshed.co.nz,gpt.ecigdis.co.nz

# File System Tool - Root directory
FS_ROOT=/home/master/applications/hdgwrzntwa/public_html

# Database Tool - Allow write operations
DB_ALLOW_WRITE=true

# Logs Tool - Maximum lines per tail
LOGS_MAX_LINES=1000

# ============================================================================
# SECURITY CONFIGURATION
# ============================================================================

# Require HTTPS
REQUIRE_HTTPS=true

# Rate limiting (future feature)
# RATE_LIMIT_ENABLED=true
# RATE_LIMIT_PER_IP=10
# RATE_LIMIT_PER_KEY=100

# Session configuration
SESSION_LIFETIME=7200
SESSION_SECURE=true
SESSION_HTTPONLY=true

# ============================================================================
# MONITORING & TELEMETRY
# ============================================================================

# Enable telemetry logging
TELEMETRY_ENABLED=true

# Telemetry retention (days)
TELEMETRY_RETENTION_DAYS=90

# Performance monitoring
PERFORMANCE_MONITORING=true
SLOW_REQUEST_THRESHOLD_MS=1000

# ============================================================================
# BACKUP CONFIGURATION
# ============================================================================

# Automated backups
BACKUP_ENABLED=true
BACKUP_PATH=/home/master/applications/hdgwrzntwa/private_html/backups
BACKUP_RETENTION_DAYS=30

# ============================================================================
# EXTERNAL SERVICES
# ============================================================================

# Devkit Enterprise Proxy
DEVKIT_PROXY_URL=https://staff.vapeshed.co.nz/assets/services/gpt/devkit_enterprise.php
DEVKIT_PROXY_SECRET=your_devkit_secret_here

# Scanner v3 (if available)
# SCANNER_ENABLED=false
# SCANNER_INDEX_URL=https://staff.vapeshed.co.nz/scanner/search

# ============================================================================
# FEATURE FLAGS
# ============================================================================

# Enable experimental features
FEATURE_STREAMING=true
FEATURE_MEMORY=true
FEATURE_TOOL_PROXY=false
FEATURE_RATE_LIMITING=false

# ============================================================================
# NOTES
# ============================================================================
# 1. Never commit this file to version control
# 2. Use different values for staging and production
# 3. Rotate API keys regularly (quarterly recommended)
# 4. Keep MCP_API_KEY at least 64 characters (hex)
# 5. Set file permissions to 640: chmod 640 .env
```

### Generate Secure Keys

```bash
# Generate MCP API Key (64 characters)
openssl rand -hex 32

# Generate JWT Secret (if using JWT in future)
openssl rand -base64 48

# Generate database password (32 characters)
openssl rand -base64 32 | tr -d '=+/' | cut -c1-32
```

### Set File Permissions

```bash
# Secure .env file
chmod 640 /home/master/applications/hdgwrzntwa/public_html/.env
chown www-data:www-data /home/master/applications/hdgwrzntwa/public_html/.env

# Verify
ls -la /home/master/applications/hdgwrzntwa/public_html/.env
# Expected: -rw-r----- 1 www-data www-data
```

---

## Database Setup

### 1. Create Database

```sql
-- Create database with UTF-8 support
CREATE DATABASE IF NOT EXISTS hdgwrzntwa
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Create database user
CREATE USER IF NOT EXISTS 'hdgwrzntwa'@'localhost'
  IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON hdgwrzntwa.*
  TO 'hdgwrzntwa'@'localhost';

FLUSH PRIVILEGES;
```

### 2. Run Migrations

```bash
cd /home/master/applications/hdgwrzntwa/public_html

# Run Phase 1 migration (core tables)
mysql -u hdgwrzntwa -p hdgwrzntwa < migrations/PHASE1_MIGRATION.sql

# Run Phase 2 migration (schema updates)
mysql -u hdgwrzntwa -p hdgwrzntwa < migrations/PHASE2_SCHEMA_MIGRATION.sql

# Verify tables created
mysql -u hdgwrzntwa -p hdgwrzntwa -e "SHOW TABLES;"
```

**Expected output:**
```
+----------------------------+
| Tables_in_hdgwrzntwa       |
+----------------------------+
| ai_agent_requests          |
| ai_conversation_messages   |
| ai_conversations           |
| ai_idempotency_keys        |
| ai_memory                  |
| ai_stream_tickets          |
| ai_tool_calls              |
| ai_tool_results            |
| mcp_tool_usage             |
+----------------------------+
```

### 3. Verify Foreign Keys

```sql
-- Check foreign key constraints
SELECT
  TABLE_NAME,
  CONSTRAINT_NAME,
  REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
  AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME;
```

### 4. Verify Indexes

```sql
-- Check indexes for performance
SELECT
  TABLE_NAME,
  INDEX_NAME,
  COLUMN_NAME,
  SEQ_IN_INDEX
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
  AND TABLE_NAME LIKE 'ai_%'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;
```

---

## File Permissions

### Set Correct Permissions

```bash
cd /home/master/applications/hdgwrzntwa

# Application files (read-only for web server)
find public_html -type f -name "*.php" -exec chmod 644 {} \;
find public_html -type d -exec chmod 755 {} \;

# Secure .env file (read-only for owner and group)
chmod 640 public_html/.env

# Writable directories
chmod 755 private_html/logs
chmod 755 private_html/cache
chmod 755 private_html/sessions
chmod 755 private_html/uploads
chmod 755 private_html/backups

# Executable scripts
chmod 755 public_html/assets/services/ai-agent/mcp/tests/smoke_test.php

# Set ownership
chown -R www-data:www-data public_html
chown -R www-data:www-data private_html
```

### Verify Permissions

```bash
# Check critical files
ls -la public_html/.env
ls -la public_html/assets/services/ai-agent/mcp/server_v3.php
ls -la public_html/assets/services/ai-agent/api/chat.php

# Check writable directories
ls -ld private_html/logs
ls -ld private_html/cache
```

---

## Smoke Tests

### 1. Run Automated Tests

```bash
cd /home/master/applications/hdgwrzntwa/public_html

# Run MCP smoke tests
php assets/services/ai-agent/mcp/tests/smoke_test.php
```

**Expected output:**
```
================================
MCP Server v3 - Smoke Test Suite
================================

Testing: server_v3.php (Main Entry)
  ✓ Test 1: meta() returns tool list
  ✓ Test 2: call() fs.read works
  ✓ Test 3: call() fs.list works
  ✓ Test 4: describe() returns schema
  ✓ Test 5: call() unknown tool fails
  ✓ Test 6: call() invalid JSON fails
  ✓ Test 7: call() missing tool fails

Testing: call.php (Unified Tool Call)
  ✓ Test 8: fs.read works
  ✓ Test 9: fs.list works
  ✓ Test 10: db.select works
  ✓ Test 11: unknown tool fails

Testing: events.php (SSE Streaming)
  ✓ Test 12: SSE opens
  ✓ Test 13: heartbeat works
  ✓ Test 14: tool result received

================================
Summary: 14/14 tests passed ✓
================================
```

### 2. Test Health Endpoints

```bash
# Test liveness
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php | jq

# Expected:
# {
#   "alive": true,
#   "timestamp": "2025-11-02T12:00:00Z"
# }

# Test readiness
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq

# Expected:
# {
#   "ready": true,
#   "checks": {
#     "database": true,
#     "tables": true,
#     "filesystem": true
#   },
#   "missing_tables": [],
#   "timestamp": "2025-11-02T12:00:00Z"
# }
```

### 3. Test Chat Endpoint

```bash
# Test GPT-4o-mini
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
  -H "Content-Type: application/json" \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "test-deployment",
    "message": "Say hello in exactly 3 words"
  }' | jq

# Expected response structure:
# {
#   "success": true,
#   "request_id": "uuid-here",
#   "data": {
#     "content": "Hello to you!",
#     "provider": "openai",
#     "model": "gpt-4o-mini",
#     "tokens": {...},
#     "latency_ms": 1234
#   }
# }
```

### 4. Test Tool Invocation

```bash
# Test fs.list tool
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H "Content-Type: application/json" \
  -d '{
    "tool": "fs.list",
    "args": {
      "path": "assets/services/ai-agent/api"
    }
  }' | jq

# Expected:
# {
#   "success": true,
#   "request_id": "uuid-here",
#   "data": {
#     "result": {
#       "entries": ["chat.php", "chat_stream.php", ...]
#     }
#   }
# }
```

### 5. Test Authentication

```bash
# Test with valid API key (if MCP_API_KEY is set)
curl -s -H "Authorization: Bearer your_api_key_here" \
  https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}' | jq

# Expected: 200 OK with tool list

# Test with invalid API key
curl -s -H "Authorization: Bearer invalid_key" \
  https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}' | jq

# Expected: 401 Unauthorized
```

### 6. Test HTTPS Enforcement

```bash
# Test HTTP request (should redirect)
curl -I http://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php

# Expected: 301 Moved Permanently
# Location: https://gpt.ecigdis.co.nz/...
```

---

## Deployment Checklist

### Pre-Deployment

- [ ] **Backup database**
  ```bash
  mysqldump -u hdgwrzntwa -p hdgwrzntwa | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
  ```

- [ ] **Backup codebase**
  ```bash
  tar -czf codebase_backup_$(date +%Y%m%d_%H%M%S).tar.gz public_html/
  ```

- [ ] **Test on staging environment**
  - Deploy to staging server
  - Run all smoke tests
  - Verify endpoints working
  - Check logs for errors

- [ ] **Review code changes**
  ```bash
  git diff master origin/master
  git log --oneline -10
  ```

### Deployment Steps

- [ ] **Pull latest code**
  ```bash
  cd /home/master/applications/hdgwrzntwa/public_html
  git pull origin master
  ```

- [ ] **Update .env file**
  - Verify all variables set correctly
  - Check API keys valid
  - Confirm database credentials
  - Set MCP_API_KEY (64+ characters)

- [ ] **Set file permissions**
  ```bash
  chmod 640 .env
  find . -type f -name "*.php" -exec chmod 644 {} \;
  find . -type d -exec chmod 755 {} \;
  ```

- [ ] **Run database migrations** (if any)
  ```bash
  mysql -u hdgwrzntwa -p hdgwrzntwa < migrations/new_migration.sql
  ```

- [ ] **Clear caches**
  ```bash
  rm -rf private_html/cache/*
  ```

- [ ] **Reload PHP-FPM**
  ```bash
  sudo systemctl reload php8.1-fpm
  ```

- [ ] **Reload Nginx**
  ```bash
  sudo nginx -t && sudo systemctl reload nginx
  ```

### Post-Deployment Verification

- [ ] **Run smoke tests**
  ```bash
  php assets/services/ai-agent/mcp/tests/smoke_test.php
  ```
  - Verify 14/14 tests passing

- [ ] **Check health endpoints**
  ```bash
  curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php | jq
  curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq
  ```
  - Both should return `true`

- [ ] **Test chat endpoint**
  ```bash
  # Send test message
  curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
    -H "Content-Type: application/json" \
    -d '{"provider":"openai","model":"gpt-4o-mini","session_key":"test","message":"Hello"}'
  ```
  - Verify response successful

- [ ] **Test tool invocation**
  ```bash
  # Invoke fs.list tool
  curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
    -H "Content-Type: application/json" \
    -d '{"tool":"fs.list","args":{"path":"assets"}}'
  ```
  - Verify tool executes

- [ ] **Verify API authentication** (if enabled)
  - Test valid API key → 200 OK
  - Test invalid API key → 401 Unauthorized
  - Test missing API key → 401 or works (depending on config)

- [ ] **Check HTTPS enforcement**
  ```bash
  curl -I http://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php
  ```
  - Verify 301 redirect to HTTPS

- [ ] **Verify SSL certificate**
  ```bash
  openssl s_client -connect gpt.ecigdis.co.nz:443 -servername gpt.ecigdis.co.nz < /dev/null
  ```
  - Check certificate valid and not expired

- [ ] **Check database connectivity**
  ```bash
  mysql -u hdgwrzntwa -p hdgwrzntwa -e "SELECT COUNT(*) FROM ai_conversations;"
  ```
  - Verify connection works

- [ ] **Verify foreign keys intact**
  ```sql
  SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = 'hdgwrzntwa' AND REFERENCED_TABLE_NAME IS NOT NULL;
  ```
  - Verify expected count (8+ constraints)

- [ ] **Test all endpoints**
  - `/api/healthz.php` → 200 OK
  - `/api/readyz.php` → 200 OK, ready=true
  - `/api/chat.php` → 200 OK with response
  - `/api/chat_stream.php` → SSE stream working
  - `/api/tools/invoke.php` → 200 OK with result
  - `/api/memory_upsert.php` → 200 OK on upsert
  - `/mcp/server_v3.php` → 200 OK on meta()

### Monitoring (First Hour)

- [ ] **Monitor error logs**
  ```bash
  tail -f logs/apache_phpstack-*.error.log
  tail -f logs/nginx-app.error.log
  tail -f private_html/logs/*.log
  ```
  - Watch for errors or warnings

- [ ] **Monitor performance**
  ```sql
  -- Check recent requests
  SELECT request_id, latency_ms, status_code
  FROM ai_agent_requests
  WHERE created_at > NOW() - INTERVAL 1 HOUR
  ORDER BY created_at DESC
  LIMIT 20;

  -- Check for slow requests
  SELECT COUNT(*) as slow_count
  FROM ai_agent_requests
  WHERE latency_ms > 2000
    AND created_at > NOW() - INTERVAL 1 HOUR;
  ```

- [ ] **Monitor tool usage**
  ```sql
  -- Recent tool calls
  SELECT tool_name, status, COUNT(*) as count
  FROM ai_tool_calls
  WHERE created_at > NOW() - INTERVAL 1 HOUR
  GROUP BY tool_name, status;
  ```

- [ ] **Check for errors**
  ```sql
  -- Failed requests
  SELECT status_code, error_code, COUNT(*) as count
  FROM ai_agent_requests
  WHERE status_code >= 400
    AND created_at > NOW() - INTERVAL 1 HOUR
  GROUP BY status_code, error_code;
  ```

---

## Rollback Procedure

If deployment issues occur:

### 1. Quick Rollback (Code Only)

```bash
cd /home/master/applications/hdgwrzntwa/public_html

# Revert to previous commit
git log --oneline -10  # Find previous good commit
git reset --hard <previous-commit-hash>
git push -f origin master  # Force push (use with caution)

# Reload services
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx
```

### 2. Full Rollback (Code + Database)

```bash
# Restore codebase
cd /home/master/applications/hdgwrzntwa
rm -rf public_html
tar -xzf backups/codebase_backup_YYYYMMDD_HHMMSS.tar.gz

# Restore database
gunzip < backups/backup_YYYYMMDD_HHMMSS.sql.gz | mysql -u hdgwrzntwa -p hdgwrzntwa

# Reload services
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx

# Verify
php public_html/assets/services/ai-agent/mcp/tests/smoke_test.php
```

### 3. Verify Rollback

```bash
# Check version
cd /home/master/applications/hdgwrzntwa/public_html
git log --oneline -1

# Run smoke tests
php assets/services/ai-agent/mcp/tests/smoke_test.php

# Check endpoints
curl -s https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php
```

---

## Post-Deployment Monitoring

### 1. Set Up Monitoring Queries

Save these queries for regular checks:

```sql
-- Daily health check (run daily)
SELECT
  DATE(created_at) as date,
  COUNT(*) as total_requests,
  SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) as successful,
  SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors,
  AVG(latency_ms) as avg_latency,
  MAX(latency_ms) as max_latency
FROM ai_agent_requests
WHERE created_at > NOW() - INTERVAL 7 DAYS
GROUP BY DATE(created_at)
ORDER BY date DESC;

-- Tool usage (run weekly)
SELECT
  tool_name,
  COUNT(*) as calls,
  AVG(latency_ms) as avg_latency,
  SUM(CASE WHEN status = 'ok' THEN 1 ELSE 0 END) as successful,
  SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
FROM ai_tool_calls
WHERE created_at > NOW() - INTERVAL 7 DAYS
GROUP BY tool_name
ORDER BY calls DESC;

-- Recent errors (run when troubleshooting)
SELECT
  created_at,
  request_id,
  status_code,
  error_code,
  error_message
FROM ai_agent_requests
WHERE status_code >= 400
ORDER BY created_at DESC
LIMIT 20;
```

### 2. Set Up Log Rotation

```bash
# /etc/logrotate.d/ai-agent
/home/master/applications/hdgwrzntwa/private_html/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.1-fpm > /dev/null 2>&1 || true
    endscript
}
```

### 3. Set Up Automated Backups

Add to crontab:

```bash
# Daily database backup at 2 AM
0 2 * * * /usr/bin/mysqldump -u hdgwrzntwa -p'password' hdgwrzntwa | gzip > /home/master/applications/hdgwrzntwa/private_html/backups/daily_$(date +\%Y\%m\%d).sql.gz

# Weekly code backup at 3 AM on Sunday
0 3 * * 0 tar -czf /home/master/applications/hdgwrzntwa/private_html/backups/weekly_$(date +\%Y\%m\%d).tar.gz /home/master/applications/hdgwrzntwa/public_html

# Cleanup old backups (keep 30 days)
0 4 * * * find /home/master/applications/hdgwrzntwa/private_html/backups -name "daily_*.sql.gz" -mtime +30 -delete
0 4 * * * find /home/master/applications/hdgwrzntwa/private_html/backups -name "weekly_*.tar.gz" -mtime +90 -delete
```

### 4. Performance Monitoring

```bash
# Check slow queries
mysql -u hdgwrzntwa -p hdgwrzntwa -e "
SELECT * FROM ai_agent_requests
WHERE latency_ms > 2000
  AND created_at > NOW() - INTERVAL 1 DAY
ORDER BY latency_ms DESC
LIMIT 10;"

# Check error rate
mysql -u hdgwrzntwa -p hdgwrzntwa -e "
SELECT
  COUNT(*) * 100.0 / (SELECT COUNT(*) FROM ai_agent_requests WHERE created_at > NOW() - INTERVAL 1 DAY) as error_rate
FROM ai_agent_requests
WHERE status_code >= 400
  AND created_at > NOW() - INTERVAL 1 DAY;"
```

---

## Troubleshooting Deployment Issues

### Issue: Smoke tests failing

**Check:**
```bash
# Verify .env loaded
php -r "require 'app.php'; var_dump($_ENV);"

# Check file permissions
ls -la .env
ls -la assets/services/ai-agent/mcp/server_v3.php

# Check PHP errors
tail -100 logs/apache_phpstack-*.error.log
```

### Issue: 500 Internal Server Error

**Check:**
```bash
# PHP error log
tail -100 logs/apache_phpstack-*.error.log

# Nginx error log
tail -100 logs/nginx-app.error.log

# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Test PHP syntax
php -l assets/services/ai-agent/api/chat.php
```

### Issue: Database connection failed

**Check:**
```bash
# Test connection
mysql -u hdgwrzntwa -p hdgwrzntwa -e "SELECT 1;"

# Check .env values
grep DB_ .env

# Verify grants
mysql -u root -p -e "SHOW GRANTS FOR 'hdgwrzntwa'@'localhost';"
```

### Issue: HTTPS not enforcing

**Check:**
```bash
# Test nginx config
sudo nginx -t

# Check redirect
curl -I http://gpt.ecigdis.co.nz/

# Verify SSL certificate
openssl s_client -connect gpt.ecigdis.co.nz:443
```

---

## Next Steps

After successful deployment:

1. **Set up monitoring** - Configure regular health checks and alerting
2. **Review logs** - Check for errors or warnings in first 24 hours
3. **Performance tuning** - Optimize slow queries if needed
4. **Documentation** - Update docs with any deployment-specific notes
5. **Team training** - Ensure team knows how to monitor and troubleshoot

---

**See Also:**
- [03_AI_AGENT_ENDPOINTS.md](03_AI_AGENT_ENDPOINTS.md) - API reference
- [07_SECURITY.md](07_SECURITY.md) - Security best practices
- [09_TROUBLESHOOTING.md](09_TROUBLESHOOTING.md) - Common issues and solutions
