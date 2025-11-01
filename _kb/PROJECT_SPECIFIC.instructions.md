---
applyTo: '**'
description: 'Project-specific instructions - Load based on current working directory'
---

# Project-Specific Instructions

## Auto-Detection Based on Path

### If working in: `/home/master/applications/jcepnzzkmj/`
**Project:** Main CIS (Central Information System)
**URL:** https://staff.vapeshed.co.nz
**Database:** jcepnzzkmj

**Key Modules:**
- `modules/consignments/` - Vend consignment workflows
- `modules/transfers/` - Stock transfer system (3-stage workflow)
- `modules/purchase_orders/` - PO management
- `modules/inventory/` - Stock management
- `modules/human_resources/` - HR & payroll
- `modules/webhooks/` - Vend webhook handlers

**Session Variables (for authentication checks):**
- `$_SESSION['userID']` - User ID (integer)
- `$_SESSION['username']` - Email address
- `$_SESSION['authenticated']` - Boolean true/false
- `$_SESSION['first_name']` - First name
- `$_SESSION['last_name']` - Last name

**Login Handler:** `assets/functions/sessions.php` → `loginSuccess()` function

---

### If working in: `/home/129337.cloudwaysapps.com/hdgwrzntwa/`
**Project:** Intelligence Hub / Dashboard
**URL:** https://gpt.ecigdis.co.nz
**Database:** Same (jcepnzzkmj) but separate application

**Key Features:**
- `dashboard/` - Admin dashboard with 32 pages
- `mcp/` - MCP server and 13 tools
- `_kb/` - Knowledge base system (22,185 files indexed)
- `universal-copilot-automation.php` - Automation engine
- `_automation/` - Automated scripts

**Session:** Uses `CIS_SESSION` name, 24hr lifetime, .vapeshed.co.nz domain

---

### If working in: `modules/human_resources/payroll/`
**Project:** Payroll Module (part of Main CIS)
**URL:** https://staff.vapeshed.co.nz/modules/human_resources/payroll/
**Status:** Active development - watch for namespace issues!

**Known Issues:**
- Mixed namespaces: `HumanResources\Payroll\` vs `PayrollModule\`
- OpCache can cache old versions (touch index.php to clear)
- Requires CIS authentication (`$_SESSION['userID']` + `$_SESSION['authenticated']`)

**Namespace Standard (use this):**
- Controllers: `PayrollModule\Controllers\`
- Services: `PayrollModule\Services\`
- Lib: `PayrollModule\Lib\`

---

## Common Patterns by Project

### Main CIS Patterns:
- Use `require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';` for bootstrap
- Use `db_prepare()` function for database queries
- Session started automatically by app.php
- Error logs: `/home/master/applications/jcepnzzkmj/logs/apache_*.error.log`

### Dashboard Patterns:
- Use local `config/session-config.php` for session handling
- Use PDO directly (no app.php)
- Error display enabled in development
- Navigation: 32 allowed pages in index.php

### Payroll Module Patterns:
- Standalone bootstrap (no app.php)
- Custom autoloader in index.php
- Uses PDO via `getPayrollDb()` function
- Session: Checks for `$_SESSION['userID']` and `$_SESSION['authenticated']`
- Redirects to `/login.php` if not authenticated

---

## Quick Commands by Project

### Main CIS:
```bash
# Check logs
tail -100 /home/master/applications/jcepnzzkmj/logs/apache_*.error.log

# Test database
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "SELECT COUNT(*) FROM users;"

# Check syntax
cd /home/master/applications/jcepnzzkmj/public_html
php -l modules/[module]/[file].php
```

### Dashboard:
```bash
# Check logs
tail -100 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/*.log

# MCP health
curl https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php

# Test dashboard
curl -I https://gpt.ecigdis.co.nz/dashboard/
```

### Payroll:
```bash
# Check logs (uses Main CIS logs)
tail -100 /home/master/applications/jcepnzzkmj/logs/apache_*.error.log | grep payroll

# Clear OpCache
touch /home/master/applications/jcepnzzkmj/public_html/modules/human_resources/payroll/index.php

# Test auth
curl -I https://staff.vapeshed.co.nz/modules/human_resources/payroll/test-auth-simple.php
```

---

## Cross-Application Considerations

**Two Separate Applications:**
1. **jcepnzzkmj** (Main CIS) - Uses `PHPSESSID` session
2. **hdgwrzntwa** (Dashboard) - Uses `CIS_SESSION` session

**They share:**
- Same database (jcepnzzkmj)
- Same domain (.vapeshed.co.nz)
- Same session domain setting

**They DON'T share:**
- File access (can't require files across apps)
- Session cookies (different names)
- PHP-FPM processes

**Solution for cross-app work:**
- Check logs from correct app
- Use database for shared data
- Session sharing via cookie domain (if using same session name)

---

**Current Detection:** Based on working directory path in terminal/file operations
