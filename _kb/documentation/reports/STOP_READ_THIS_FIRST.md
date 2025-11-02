# 🔴 STOP - READ THIS FIRST

## THE ONLY DOMAIN

```
GPT.ECIGDIS.CO.NZ
```

**PROTOCOL:** HTTPS
**URL:** https://gpt.ecigdis.co.nz

---

## WHERE IT'S SET

### 1. Core Configuration
```php
// /dashboard/admin/config/mcp-hub.php
define('MCP_HUB_DOMAIN', 'gpt.ecigdis.co.nz');
define('MCP_HUB_BASE_URL', 'https://gpt.ecigdis.co.nz');
```

### 2. All API Endpoints
- `/dashboard/admin/api/mcp/health.php` → connects to `gpt.ecigdis.co.nz`
- `/dashboard/admin/api/projects/get.php` → uses MCPHubClient
- `/dashboard/admin/api/files/details.php` → uses MCPHubClient
- `/dashboard/admin/api/violations/list.php` → uses MCPHubClient
- `/dashboard/admin/api/metrics/dashboard.php` → uses MCPHubClient
- `/dashboard/admin/api/scan/run.php` → uses MCPHubClient

### 3. Dashboard Pages
All 6 pages use the configured domain through MCPHubClient:
- `/dashboard/admin/pages/overview.php`
- `/dashboard/admin/pages/files.php`
- `/dashboard/admin/pages/dependencies.php`
- `/dashboard/admin/pages/violations.php`
- `/dashboard/admin/pages/rules.php`
- `/dashboard/admin/pages/metrics.php`

### 4. Documentation
- ✅ COMPREHENSIVE_AUDIT_REPORT.md
- ✅ AUDIT_SUMMARY_FOR_PEARCE.md
- ✅ MCP_DOMAIN_VERIFICATION.md
- ✅ MCP_DOMAIN_REMINDER.md
- ✅ MCP_HUB_INTEGRATION.md

---

## ACCESS URLS (MEMORIZE THESE)

```
🏠 Main Dashboard:
   https://staff.vapeshed.co.nz/dashboard/

🎛️ Admin Dashboard:
   https://staff.vapeshed.co.nz/dashboard/admin/

🧠 MCP Intelligence Hub:
   https://gpt.ecigdis.co.nz

✅ Health Check:
   https://gpt.ecigdis.co.nz/health.php
```

---

## VERIFICATION CHECKLIST

Run these commands to verify:

```bash
# Verify config
grep "MCP_HUB_DOMAIN" /home/master/applications/hdgwrzntwa/public_html/dashboard/admin/config/mcp-hub.php

# Should output:
# define('MCP_HUB_DOMAIN', 'gpt.ecigdis.co.nz');

# Count all references
grep -r "gpt.ecigdis.co.nz" /home/master/applications/hdgwrzntwa/public_html/dashboard/admin/ | wc -l

# Should output: 21+ (multiple references)

# Test connection
curl https://gpt.ecigdis.co.nz/health.php

# Test dashboard health check
curl https://staff.vapeshed.co.nz/dashboard/admin/api/mcp/health.php
```

---

## IF YOU SEE ANY OTHER DOMAIN

🚨 **STOP IMMEDIATELY**

Do not proceed. Report the discrepancy.

The ONLY valid MCP Hub domain is:
```
GPT.ECIGDIS.CO.NZ
```

---

## STATUS: ✅ LOCKED AND VERIFIED

- Configuration: LOCKED
- Code: LOCKED
- Documentation: LOCKED
- Verified: October 30, 2025
- References: 21+

**THIS WILL NOT CHANGE**

---

**Print this page. Pin it to your wall. Never forget.**

**GPT.ECIGDIS.CO.NZ**

---

*Last verified: October 30, 2025 15:55 UTC*
