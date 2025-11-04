# 🚀 Quick Deployment Checklist

## Priority 1: GitHub Copilot MCP Integration (30 minutes)

### ✅ Step 1: Get API Key
```bash
# On server, check for existing key
cat /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env | grep MCP_API_KEY

# If not found, generate one
# Visit: https://gpt.ecigdis.co.nz/admin/api-keys.php
```

### ✅ Step 2: Configure VS Code
```bash
# Run setup script
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html
./setup-vscode-mcp.sh

# Or manually add to ~/.bashrc or ~/.zshrc:
export INTELLIGENCE_HUB_API_KEY="your-key-here"
source ~/.bashrc  # or ~/.zshrc
```

### ✅ Step 3: Add VS Code Settings
Create or update `.vscode/settings.json`:
```json
{
  "github.copilot.advanced": {
    "mcp.enabled": true,
    "mcp.servers": {
      "intelligence-hub": {
        "url": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc",
        "authentication": {
          "type": "bearer",
          "token": "${INTELLIGENCE_HUB_API_KEY}"
        }
      }
    }
  }
}
```

### ✅ Step 4: Test
```bash
# Test MCP connection
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health

# In VS Code GitHub Copilot Chat:
@workspace Use MCP to search the knowledge base for "inventory management"
```

---

## Priority 2: Deploy to CIS (2 hours)

### ✅ Step 1: Test CIS Connection
```bash
curl -I https://staff.vapeshed.co.nz
# Should return 200 OK
```

### ✅ Step 2: Deploy Knowledge Base
```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html
./scripts/deploy-to-satellite.sh cis kb
```

### ✅ Step 3: Deploy MCP Server
```bash
./scripts/deploy-to-satellite.sh cis mcp
```

### ✅ Step 4: Deploy Scanner
```bash
./scripts/deploy-to-satellite.sh cis scanner
```

### ✅ Step 5: Verify Deployment
```bash
# Check if files deployed
curl https://staff.vapeshed.co.nz/_kb/MASTER_INDEX.md

# Check MCP endpoint
curl -X POST https://staff.vapeshed.co.nz/mcp/server_v3.php?action=health
```

---

## Priority 3: Setup Payroll Bot (4 hours)

### ✅ Step 1: Get API Credentials

**Deputy API:**
1. Log into Deputy account
2. Navigate to Settings → API Credentials
3. Generate new API token
4. Copy token

**Xero API:**
1. Log into Xero Developer Portal
2. Create OAuth2 app
3. Get Client ID and Client Secret
4. Authorize app for payroll
5. Get access token and tenant ID

### ✅ Step 2: Add Credentials to CIS
```bash
# SSH to CIS server
ssh user@staff.vapeshed.co.nz

# Visit credentials manager
# https://staff.vapeshed.co.nz/admin/credentials.php

# Add Deputy credentials:
# Name: deputy_api
# Type: api
# Fields:
#   - key: YOUR_DEPUTY_API_KEY
#   - base_url: https://api.deputy.com/api/v1

# Add Xero credentials:
# Name: xero_payroll
# Type: api
# Fields:
#   - tenant_id: YOUR_XERO_TENANT_ID
#   - access_token: YOUR_XERO_ACCESS_TOKEN
#   - client_id: YOUR_CLIENT_ID
#   - client_secret: YOUR_CLIENT_SECRET
```

### ✅ Step 3: Create Database Tables
```sql
-- Connect to CIS database
mysql -u jcepnzzkmj -p jcepnzzkmj

-- Create payroll_runs table
CREATE TABLE IF NOT EXISTS payroll_runs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_end DATE NOT NULL,
    timesheets_processed INT DEFAULT 0,
    employees_paid INT DEFAULT 0,
    total_gross DECIMAL(10,2) DEFAULT 0,
    total_net DECIMAL(10,2) DEFAULT 0,
    pay_run_id VARCHAR(255),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    exceptions_count INT DEFAULT 0,
    warnings_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_period (period_end),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create bot_logs table
CREATE TABLE IF NOT EXISTS bot_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_name VARCHAR(100) NOT NULL,
    level ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    message TEXT,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bot (bot_name, created_at),
    INDEX idx_level (level),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create bot_conversations table (for tracking)
CREATE TABLE IF NOT EXISTS bot_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_name VARCHAR(100) NOT NULL,
    user_id INT,
    conversation_id VARCHAR(255),
    message_count INT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
    INDEX idx_bot (bot_name),
    INDEX idx_user (user_id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verify tables created
SHOW TABLES LIKE '%bot%';
SHOW TABLES LIKE '%payroll%';
```

### ✅ Step 4: Deploy Payroll Bot
```bash
# Copy bot to CIS
scp /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/bots/PayrollBot.php \
    user@staff.vapeshed.co.nz:/path/to/cis/_automation/bots/

# Or use deployment script
./scripts/deploy-to-satellite.sh cis bots
```

### ✅ Step 5: Test Payroll Bot
```bash
# SSH to CIS
ssh user@staff.vapeshed.co.nz

# Test bot (dry run)
php /_automation/bots/PayrollBot.php 2024-11-08

# Check logs
tail -f /var/log/cis/bot.log

# Or check database
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT * FROM bot_logs ORDER BY created_at DESC LIMIT 10;"
```

### ✅ Step 6: Setup Cron Job
```bash
# Add to crontab on CIS server
crontab -e

# Add this line (runs every Friday at 9 AM):
0 9 * * 5 cd /path/to/cis && php /_automation/bots/PayrollBot.php >> /var/log/cis/payroll-bot.log 2>&1
```

---

## Verification Commands

### Check MCP Health
```bash
# Intelligence Hub
curl https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health

# CIS (after deployment)
curl https://staff.vapeshed.co.nz/mcp/server_v3.php?action=health
```

### Check Satellite Status
```bash
# Via MCP
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/call",
    "params": {
      "name": "satellite.list",
      "arguments": {}
    },
    "id": 1
  }'
```

### Check Bot Status
```bash
# On CIS database
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
SELECT
    bot_name,
    COUNT(*) as log_count,
    MAX(created_at) as last_activity,
    SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as errors
FROM bot_logs
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY bot_name;
"
```

---

## Troubleshooting

### MCP Connection Fails
```bash
# Check firewall
sudo ufw status

# Check SSL certificate
curl -vI https://gpt.ecigdis.co.nz

# Check API key
echo $INTELLIGENCE_HUB_API_KEY

# Test with curl
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":1}'
```

### Satellite Deployment Fails
```bash
# Check logs
tail -f /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/deployment.log

# Test connection manually
curl -I https://staff.vapeshed.co.nz

# Check file permissions
ls -la /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/scripts/

# Run in verbose mode
bash -x ./scripts/deploy-to-satellite.sh cis kb
```

### Payroll Bot Errors
```bash
# Check bot logs
tail -f /var/log/cis/bot.log

# Check database logs
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
SELECT * FROM bot_logs
WHERE bot_name = 'PayrollBot'
AND level = 'error'
ORDER BY created_at DESC
LIMIT 10;
"

# Test Deputy API
curl -H "Authorization: Bearer YOUR_DEPUTY_KEY" \
     https://api.deputy.com/api/v1/me

# Test Xero API
curl -H "Authorization: Bearer YOUR_XERO_TOKEN" \
     -H "Xero-Tenant-Id: YOUR_TENANT_ID" \
     https://api.xero.com/payroll.xro/2.0/Employees
```

---

## Quick Reference

### File Locations
```
Intelligence Hub:
  - MCP Server: /public_html/mcp/server_v3.php
  - Deployment Script: /public_html/scripts/deploy-to-satellite.sh
  - Documentation: /public_html/_kb/
  - Configuration: /public_html/config/

CIS (after deployment):
  - MCP Server: /mcp/server_v3.php
  - Bots: /_automation/bots/
  - Knowledge Base: /_kb/
  - Credentials: /admin/credentials.php
  - Bot Dashboard: /admin/bots/
```

### Important URLs
```
Intelligence Hub: https://gpt.ecigdis.co.nz
CIS Staff System: https://staff.vapeshed.co.nz
MCP Health Check: https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health
Bot Dashboard: https://staff.vapeshed.co.nz/admin/bots/ (after deployment)
Credentials: https://staff.vapeshed.co.nz/admin/credentials.php
```

### API Endpoints
```
MCP RPC: POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc
MCP Health: GET https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health
MCP Meta: GET https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta
Satellite Deploy: POST https://gpt.ecigdis.co.nz/api/satellite-deploy.php
KB Sync: POST https://gpt.ecigdis.co.nz/api/sync_kb_to_satellite.php
```

---

## Success Criteria Checklist

### Priority 1 Complete When:
- [ ] API key set in environment
- [ ] VS Code settings.json configured
- [ ] GitHub Copilot can query MCP
- [ ] Test query returns results
- [ ] No authentication errors

### Priority 2 Complete When:
- [ ] CIS accessible via curl
- [ ] KB deployed to CIS
- [ ] MCP endpoint working on CIS
- [ ] Scanner deployed and running
- [ ] Satellite status shows "online"

### Priority 3 Complete When:
- [ ] Deputy credentials added
- [ ] Xero credentials added
- [ ] Database tables created
- [ ] Payroll bot deployed to CIS
- [ ] Test payroll run succeeds
- [ ] Bot dashboard shows data
- [ ] Cron job scheduled

---

## Emergency Contacts

**If something breaks:**
1. Check logs first
2. Run health checks
3. Verify credentials
4. Test network connectivity
5. Check database connectivity

**Need help?**
- Documentation: /public_html/_kb/
- Implementation Plan: /public_html/_kb/PRIORITY_IMPLEMENTATION_PLAN.md
- Status Report: /public_html/_kb/IMPLEMENTATION_STATUS_REPORT.md
- Setup Guide: /public_html/_kb/GITHUB_COPILOT_MCP_SETUP.md

---

**Created:** 2025-11-04
**Version:** 1.0
**Status:** ✅ Ready to Execute
