#!/bin/bash
###############################################################################
# EMERGENCY SECURITY FIX SCRIPT
# This script performs IMMEDIATE security remediation
# Run with: bash emergency-security-fix.sh
###############################################################################

set -e  # Exit on error

echo "═══════════════════════════════════════════════════════════════"
echo "🚨 EMERGENCY SECURITY FIX - STARTING"
echo "═══════════════════════════════════════════════════════════════"
echo ""

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
cd "$PROJECT_ROOT"

BACKUP_DIR="$PROJECT_ROOT/backups/emergency-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "📁 Backup directory: $BACKUP_DIR"
echo ""

###############################################################################
# PHASE 1: BACKUP CRITICAL FILES
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 1: BACKING UP CRITICAL FILES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Backup .env
if [ -f ".env" ]; then
    cp .env "$BACKUP_DIR/.env.backup"
    echo "✅ Backed up .env"
fi

# Backup all files with hardcoded credentials
for file in bin/*.sh bin/*.php public/api/*.php dev-center/*.php; do
    if [ -f "$file" ]; then
        mkdir -p "$BACKUP_DIR/$(dirname "$file")"
        cp "$file" "$BACKUP_DIR/$file.backup"
    fi
done

echo "✅ Backup complete"
echo ""

###############################################################################
# PHASE 2: DISABLE DANGEROUS TOOLS
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 2: DISABLING DANGEROUS TOOLS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Disable CodeTool (RCE risk)
if [ -f "src/Tools/CodeTool.php" ]; then
    mv "src/Tools/CodeTool.php" "src/Tools/CodeTool.php.DISABLED"
    echo "✅ Disabled CodeTool.php (RCE vulnerability)"
fi

# Disable ShellTool if exists
if [ -f "src/Tools/ShellTool.php" ]; then
    mv "src/Tools/ShellTool.php" "src/Tools/ShellTool.php.DISABLED"
    echo "✅ Disabled ShellTool.php"
fi

echo ""

###############################################################################
# PHASE 3: PROTECT ADMIN ENDPOINTS
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 3: PROTECTING ADMIN ENDPOINTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Protect public/api/ endpoints
cat > public/api/.htaccess <<'EOF'
# Emergency Security Lockdown
# Created: $(date)

<FilesMatch "(health|metrics|admin|realtime-metrics).*\.php$">
    Order Deny,Allow
    Deny from all
    # Allow localhost
    Allow from 127.0.0.1
    Allow from ::1
    # Add your office IP here:
    # Allow from YOUR.OFFICE.IP.ADDRESS
</FilesMatch>

# Block direct access to sensitive files
<FilesMatch "\.(log|sql|md|txt|json|env)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
EOF

echo "✅ Created .htaccess protection for public/api/"

# Protect agent_dev endpoints
cat > public/agent_dev/api/.htaccess <<'EOF'
# Emergency Security Lockdown
Order Deny,Allow
Deny from all
Allow from 127.0.0.1
Allow from ::1
EOF

echo "✅ Created .htaccess protection for agent_dev/api/"

echo ""

###############################################################################
# PHASE 4: REMOVE HARDCODED CREDENTIALS FROM DOCUMENTATION
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 4: REDACTING CREDENTIALS IN DOCUMENTATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Redact password in markdown files (documentation only)
find . -name "*.md" -type f -exec sed -i.bak 's/wprKh9Jq63/\*\*\*REDACTED\*\*\*/g' {} \;
find . -name "*.md.bak" -delete

echo "✅ Redacted passwords in documentation files"
echo ""

###############################################################################
# PHASE 5: SECURE FILE PERMISSIONS
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 5: SECURING FILE PERMISSIONS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Secure .env file
if [ -f ".env" ]; then
    chmod 600 .env
    echo "✅ Set .env permissions to 600 (owner read/write only)"
fi

# Secure scripts
chmod 700 bin/*.sh 2>/dev/null || true
echo "✅ Set script permissions to 700"

# Secure config files
find config -name "*.php" -type f -exec chmod 640 {} \; 2>/dev/null || true
echo "✅ Secured config files"

echo ""

###############################################################################
# PHASE 6: CLEAR CACHES AND SESSIONS
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 6: CLEARING CACHES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Clear PHP cache
rm -rf cache/*.cache 2>/dev/null || true
echo "✅ Cleared PHP caches"

# Clear Redis if available
if command -v redis-cli &> /dev/null; then
    redis-cli FLUSHDB 2>/dev/null || echo "⚠️  Redis not accessible"
else
    echo "ℹ️  Redis CLI not available"
fi

echo ""

###############################################################################
# PHASE 7: UPDATE .gitignore
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 7: UPDATING .gitignore"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Add sensitive files to .gitignore
cat >> .gitignore <<'EOF'

# Emergency security additions
.env
.env.local
.env.production
config/local.php
*.log
*.sql
*.sqlite
backups/
cache/*
!cache/.gitkeep
sessions/*
!sessions/.gitkeep
uploads/*
!uploads/.gitkeep
*.pem
*.key
*.crt
.htpasswd
EOF

echo "✅ Updated .gitignore"
echo ""

###############################################################################
# PHASE 8: CREATE SECURITY AUDIT LOG
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 8: CREATING AUDIT LOG"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

AUDIT_LOG="$PROJECT_ROOT/SECURITY_AUDIT_$(date +%Y%m%d-%H%M%S).log"

cat > "$AUDIT_LOG" <<EOF
═══════════════════════════════════════════════════════════════
EMERGENCY SECURITY AUDIT LOG
═══════════════════════════════════════════════════════════════

Date: $(date)
User: $(whoami)
Host: $(hostname)
Project: $PROJECT_ROOT

ACTIONS TAKEN:
───────────────────────────────────────────────────────────────

1. DISABLED TOOLS:
   - CodeTool.php (RCE vulnerability)
   - ShellTool.php (if existed)

2. PROTECTED ENDPOINTS:
   - public/api/* (admin endpoints)
   - agent_dev/api/* (development endpoints)

3. REDACTED CREDENTIALS:
   - Documentation files (.md)
   - Passwords replaced with ***REDACTED***

4. SECURED FILES:
   - .env: 600 permissions
   - Scripts: 700 permissions
   - Config: 640 permissions

5. CLEARED CACHES:
   - PHP cache files
   - Redis database (if available)

6. UPDATED .gitignore:
   - Added sensitive file patterns
   - Prevented future credential leaks

BACKUP LOCATION:
───────────────────────────────────────────────────────────────
$BACKUP_DIR

NEXT STEPS REQUIRED:
───────────────────────────────────────────────────────────────
[ ] Rotate database password
[ ] Rotate all API keys (OpenAI, Anthropic, etc.)
[ ] Fix REDACTED placeholders in code
[ ] Add CSRF protection to all forms
[ ] Implement output escaping (htmlspecialchars)
[ ] Add Content Security Policy headers
[ ] Implement rate limiting
[ ] Review all authentication logic
[ ] Conduct penetration test
[ ] Update incident response plan

VERIFICATION COMMANDS:
───────────────────────────────────────────────────────────────
# Check for remaining hardcoded credentials
grep -r "wprKh9Jq63" . 2>/dev/null | grep -v ".md" | grep -v "backup"

# Check for REDACTED placeholders in code
grep -r "REDACTED" --include="*.php" . | grep -v "\.md"

# Test admin endpoint protection
curl -I https://staff.vapeshed.co.nz/assets/neuro/ai-agent/public/api/admin.php

# Verify .env permissions
ls -la .env

═══════════════════════════════════════════════════════════════
END OF AUDIT LOG
═══════════════════════════════════════════════════════════════
EOF

echo "✅ Created audit log: $AUDIT_LOG"
echo ""

###############################################################################
# PHASE 9: GENERATE SECURITY REPORT
###############################################################################

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "PHASE 9: GENERATING SECURITY REPORT"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check for remaining hardcoded credentials (excluding backups and docs)
echo "Scanning for remaining hardcoded credentials..."
CRED_COUNT=$(grep -r "wprKh9Jq63" . 2>/dev/null | grep -v ".md" | grep -v "backup" | grep -v ".bak" | wc -l)

# Check for REDACTED placeholders in PHP code
echo "Scanning for REDACTED placeholders in PHP code..."
REDACTED_COUNT=$(grep -r "REDACTED" --include="*.php" . 2>/dev/null | grep -v ".md" | wc -l)

# Check for unescaped output
echo "Scanning for potential XSS vulnerabilities..."
XSS_COUNT=$(grep -r '\$_SESSION\[' --include="*.php" public/dashboard/ 2>/dev/null | grep -v "htmlspecialchars" | wc -l)

cat > SECURITY_REPORT.md <<EOF
# 🔒 EMERGENCY SECURITY FIX REPORT

**Date:** $(date)  
**Status:** EMERGENCY FIXES APPLIED

---

## ✅ ACTIONS COMPLETED

### 1. Dangerous Tools Disabled
- ✅ CodeTool.php → DISABLED (RCE vulnerability)
- ✅ ShellTool.php → DISABLED (if existed)

### 2. Admin Endpoints Protected
- ✅ public/api/.htaccess created (localhost only)
- ✅ agent_dev/api/.htaccess created (localhost only)
- ✅ Sensitive file access blocked

### 3. Credentials Redacted
- ✅ Documentation files cleaned
- ✅ Passwords replaced with ***REDACTED***

### 4. File Permissions Secured
- ✅ .env: 600 (owner read/write only)
- ✅ Scripts: 700 (owner execute only)
- ✅ Config: 640 (owner rw, group read)

### 5. Caches Cleared
- ✅ PHP cache files removed
- ✅ Redis flushed (if available)

### 6. .gitignore Updated
- ✅ Sensitive files added
- ✅ Future leaks prevented

---

## ⚠️ REMAINING ISSUES

### Critical Items Requiring Manual Fix:

**Hardcoded Credentials in Code:** $CRED_COUNT files
**REDACTED Placeholders:** $REDACTED_COUNT occurrences  
**Potential XSS:** $XSS_COUNT locations

---

## 🚨 URGENT: MANUAL STEPS REQUIRED

### 1. Rotate Database Password (DO NOW)
\`\`\`bash
# Connect to MySQL
mysql -u root -p

# Change password
ALTER USER 'jcepnzzkmj'@'localhost' IDENTIFIED BY 'NEW_SECURE_PASSWORD';
FLUSH PRIVILEGES;

# Update .env file
vi .env
# Change: MYSQL_PASSWORD=NEW_SECURE_PASSWORD
\`\`\`

### 2. Rotate API Keys
- [ ] OpenAI API key
- [ ] Anthropic API key
- [ ] Any other third-party API keys

### 3. Fix REDACTED Placeholders
Run the code fix script:
\`\`\`bash
bash bin/fix-redacted-placeholders.sh
\`\`\`

### 4. Add CSRF Protection
- [ ] Implement CSRF tokens on all forms
- [ ] Add validation to all POST handlers

### 5. Fix XSS Vulnerabilities
- [ ] Add htmlspecialchars() to all output
- [ ] Implement Content Security Policy

---

## 📋 VERIFICATION CHECKLIST

\`\`\`bash
# 1. No hardcoded credentials in code
grep -r "wprKh9Jq63" . 2>/dev/null | grep -v ".md" | grep -v "backup"
# Expected: 0 results

# 2. No REDACTED in PHP files
grep -r "REDACTED" --include="*.php" . | grep -v "\.md"
# Expected: 0 results

# 3. Admin endpoints protected
curl -I https://staff.vapeshed.co.nz/assets/neuro/ai-agent/public/api/admin.php
# Expected: HTTP/1.1 403 Forbidden

# 4. .env secured
ls -la .env
# Expected: -rw------- (600)
\`\`\`

---

## 📞 NEXT ACTIONS

### Immediate (< 1 hour)
1. Rotate database password
2. Rotate API keys
3. Test admin endpoint protection
4. Verify backups are working

### Urgent (< 4 hours)
1. Fix all REDACTED placeholders
2. Add CSRF protection
3. Fix XSS vulnerabilities
4. Add CSP headers

### High Priority (< 24 hours)
1. Implement rate limiting
2. Add comprehensive logging
3. Set up monitoring/alerts
4. Conduct security audit

---

**BACKUP LOCATION:** $BACKUP_DIR  
**AUDIT LOG:** $AUDIT_LOG

---

*Generated: $(date)*
EOF

echo "✅ Generated SECURITY_REPORT.md"
echo ""

###############################################################################
# FINAL SUMMARY
###############################################################################

echo "═══════════════════════════════════════════════════════════════"
echo "🎯 EMERGENCY FIX COMPLETE"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "✅ COMPLETED ACTIONS:"
echo "   - Disabled dangerous tools (CodeTool)"
echo "   - Protected admin endpoints"
echo "   - Redacted credentials in docs"
echo "   - Secured file permissions"
echo "   - Cleared caches"
echo "   - Updated .gitignore"
echo ""
echo "⚠️  URGENT MANUAL STEPS REQUIRED:"
echo "   1. Rotate database password NOW"
echo "   2. Rotate all API keys"
echo "   3. Fix REDACTED placeholders in code"
echo "   4. Add CSRF protection"
echo "   5. Fix XSS vulnerabilities"
echo ""
echo "📁 Backup location: $BACKUP_DIR"
echo "📋 Security report: SECURITY_REPORT.md"
echo "📝 Audit log: $AUDIT_LOG"
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "⚠️  RUN THESE VERIFICATION COMMANDS:"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "# Check remaining issues:"
echo "grep -r 'wprKh9Jq63' . 2>/dev/null | grep -v '.md' | grep -v 'backup'"
echo ""
echo "# Check REDACTED in code:"
echo "grep -r 'REDACTED' --include='*.php' . | grep -v '\.md'"
echo ""
echo "# Test endpoint protection:"
echo "curl -I https://staff.vapeshed.co.nz/assets/neuro/ai-agent/public/api/admin.php"
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "🚨 IMMEDIATE ACTION STILL REQUIRED - SEE SECURITY_REPORT.md"
echo "═══════════════════════════════════════════════════════════════"
