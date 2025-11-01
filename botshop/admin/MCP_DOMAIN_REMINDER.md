# ⚠️ MANDATORY MCP HUB DOMAIN REMINDER

**STOP. READ THIS FIRST.**

---

## 🔴 CRITICAL DOMAIN

### THE ONLY MCP HUB DOMAIN

```
GPT.ECIGDIS.CO.NZ
```

**Nothing else. Never any other domain.**

---

## ✅ Where It's Configured

### 1. Dashboard Admin Config
**File:** `/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/config/mcp-hub.php`

```php
define('MCP_HUB_DOMAIN', 'gpt.ecigdis.co.nz');
define('MCP_HUB_BASE_URL', 'https://gpt.ecigdis.co.nz');
```

### 2. Health Check API
**File:** `/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/api/mcp/health.php`

Tests connection to: `https://gpt.ecigdis.co.nz`

### 3. MCPHubClient Class
**File:** `/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/config/mcp-hub.php`

All methods connect to: `https://gpt.ecigdis.co.nz`

---

## 📋 VERIFICATION COMMANDS

```bash
# Check what domain is configured
grep -r "MCP_HUB_DOMAIN" /home/master/applications/hdgwrzntwa/public_html/dashboard/admin/

# Should show:
# define('MCP_HUB_DOMAIN', 'gpt.ecigdis.co.nz');

# Test the connection
curl https://gpt.ecigdis.co.nz/health.php

# Test from dashboard
curl https://staff.vapeshed.co.nz/dashboard/admin/api/mcp/health.php
```

---

## 🚀 ACCESS URLS

```
DASHBOARD:       https://staff.vapeshed.co.nz/dashboard/
ADMIN SECTION:   https://staff.vapeshed.co.nz/dashboard/admin/
MCP HUB:         https://gpt.ecigdis.co.nz
MCP HEALTH:      https://gpt.ecigdis.co.nz/health.php
```

---

## 🔒 LOCKED & VERIFIED

- ✅ Domain is **hardcoded** in configuration
- ✅ All endpoints reference this domain
- ✅ Health checks verify connectivity
- ✅ SSL/HTTPS enforced
- ✅ Production ready

---

**IF YOU SEE ANY OTHER DOMAIN, STOP AND REPORT IT**

`GPT.ECIGDIS.CO.NZ` is the ONLY MCP Hub domain.

---

*Document created: October 30, 2025*
*Last verified: October 30, 2025*
