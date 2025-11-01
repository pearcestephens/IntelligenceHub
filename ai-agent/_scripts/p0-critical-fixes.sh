#!/bin/bash
#
# 🚀 AI-AGENT P0 CRITICAL FIXES - AUTOMATED DEPLOYMENT
# Priority: IMMEDIATE EXECUTION
# Time: ~1 hour
# Status: READY TO RUN
#

set -e  # Exit on error

echo "═══════════════════════════════════════════════════════════════"
echo "🚀 AI-AGENT P0 CRITICAL FIXES - STARTING"
echo "═══════════════════════════════════════════════════════════════"
echo ""

# Configuration
DB_HOST="127.0.0.1"
DB_USER="hdgwrzntwa"
DB_PASS="your_password_here"  # UPDATE THIS
DB_NAME="hdgwrzntwa"
AI_AGENT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ═══════════════════════════════════════════════════════════════
# FIX #1: DEPLOY MULTI-KB DATABASE SCHEMA
# ═══════════════════════════════════════════════════════════════

echo -e "${YELLOW}[1/4] Deploying Multi-KB Database Schema...${NC}"

cd "$AI_AGENT_ROOT/database"

if [ -f "deploy-multi-kb-single-table.sql" ]; then
    echo "  → Found deploy-multi-kb-single-table.sql"
    echo "  → Executing SQL..."

    # Execute with error checking
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < deploy-multi-kb-single-table.sql 2>&1 | tee /tmp/ai-agent-deploy.log

    if [ $? -eq 0 ]; then
        echo -e "  ${GREEN}✓ Database schema deployed successfully${NC}"

        # Verify deployment
        echo "  → Verifying domains..."
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
            SELECT domain_key, domain_name, application_name
            FROM ai_kb_domain_registry;
        "

        echo -e "  ${GREEN}✓ Verification complete${NC}"
    else
        echo -e "  ${RED}✗ Database deployment failed${NC}"
        echo "  Check /tmp/ai-agent-deploy.log for details"
        exit 1
    fi
else
    echo -e "  ${RED}✗ deploy-multi-kb-single-table.sql not found${NC}"
    exit 1
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# FIX #2: UPDATE DATABASE CONFIGURATION IN .ENV
# ═══════════════════════════════════════════════════════════════

echo -e "${YELLOW}[2/4] Updating .env Database Configuration...${NC}"

cd "$AI_AGENT_ROOT"

# Backup original .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo "  → Backed up .env"

# Update database configuration
sed -i 's/^MYSQL_USER=jcepnzzkmj/MYSQL_USER=hdgwrzntwa/' .env
sed -i 's/^MYSQL_DATABASE=jcepnzzkmj/MYSQL_DATABASE=hdgwrzntwa/' .env

# Verify changes
if grep -q "MYSQL_USER=hdgwrzntwa" .env && grep -q "MYSQL_DATABASE=hdgwrzntwa" .env; then
    echo -e "  ${GREEN}✓ Database configuration updated${NC}"
    echo "  → MYSQL_USER=hdgwrzntwa"
    echo "  → MYSQL_DATABASE=hdgwrzntwa"
else
    echo -e "  ${RED}✗ Configuration update failed${NC}"
    exit 1
fi

echo ""

# ═══════════════════════════════════════════════════════════════
# FIX #3: ENABLE AUTHENTICATION IN chat-enterprise.php
# ═══════════════════════════════════════════════════════════════

echo -e "${YELLOW}[3/4] Enabling Authentication in chat-enterprise.php...${NC}"

cd "$AI_AGENT_ROOT/api"

# Generate API keys
KEY_STAFF="key_staff_$(openssl rand -hex 16)"
KEY_WEB="key_web_$(openssl rand -hex 16)"
KEY_GPT="key_gpt_$(openssl rand -hex 16)"

echo "  → Generated API keys:"
echo "    STAFF: $KEY_STAFF"
echo "    WEB: $KEY_WEB"
echo "    GPT: $KEY_GPT"

# Add API keys to .env
cd "$AI_AGENT_ROOT"
echo "" >> .env
echo "# API Keys (Added by P0 fix script)" >> .env
echo "API_KEYS=$KEY_STAFF,$KEY_WEB,$KEY_GPT" >> .env

echo -e "  ${GREEN}✓ API keys added to .env${NC}"

# Save keys to secure file
echo "$KEY_STAFF" > "$AI_AGENT_ROOT/config/api_keys.txt"
echo "$KEY_WEB" >> "$AI_AGENT_ROOT/config/api_keys.txt"
echo "$KEY_GPT" >> "$AI_AGENT_ROOT/config/api_keys.txt"
chmod 600 "$AI_AGENT_ROOT/config/api_keys.txt"

echo -e "  ${GREEN}✓ Keys saved to config/api_keys.txt (chmod 600)${NC}"

# Create authentication patch file
cat > "$AI_AGENT_ROOT/api/auth_patch.php" << 'EOF'
<?php
/**
 * Authentication Check for AI Agent API
 * Insert at line 828 of chat-enterprise.php
 */

// Authentication check
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (empty($apiKey)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Missing API key',
        'message' => 'Please provide X-API-KEY header'
    ]);
    exit;
}

$validKeys = explode(',', getenv('API_KEYS') ?: '');
if (!in_array($apiKey, $validKeys, true)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid API key',
        'message' => 'The provided API key is not valid'
    ]);
    exit;
}

// Log authenticated request
error_log("[Auth] Authenticated request with key: " . substr($apiKey, 0, 12) . "...");
EOF

echo -e "  ${GREEN}✓ Created auth_patch.php${NC}"
echo -e "  ${YELLOW}⚠️  MANUAL STEP REQUIRED:${NC}"
echo "     Insert contents of api/auth_patch.php at line 828 of chat-enterprise.php"

# Create CORS patch file
cat > "$AI_AGENT_ROOT/api/cors_patch.php" << 'EOF'
<?php
/**
 * CORS Restriction for AI Agent API
 * Replace line 122 of chat-enterprise.php
 */

$allowedOrigins = [
    'https://staff.vapeshed.co.nz',
    'https://gpt.ecigdis.co.nz',
    'https://www.vapeshed.co.nz',
    'https://wiki.vapeshed.co.nz'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    // Log unauthorized origin attempts
    error_log("[CORS] Unauthorized origin attempt: " . $origin);
}
EOF

echo -e "  ${GREEN}✓ Created cors_patch.php${NC}"
echo -e "  ${YELLOW}⚠️  MANUAL STEP REQUIRED:${NC}"
echo "     Replace line 122 of chat-enterprise.php with contents of api/cors_patch.php"

echo ""

# ═══════════════════════════════════════════════════════════════
# FIX #4: ACTIVATE HEALTH MONITORING
# ═══════════════════════════════════════════════════════════════

echo -e "${YELLOW}[4/4] Activating Health Monitoring...${NC}"

# Create health check script
cat > "$AI_AGENT_ROOT/bin/health-monitor.sh" << 'EOF'
#!/bin/bash
# AI Agent Health Monitor
# Runs every 5 minutes via cron

HEALTH_URL="https://gpt.ecigdis.co.nz/ai-agent/api/health.php"
LOG_FILE="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/ai-agent-health.log"
ALERT_EMAIL="alerts@ecigdis.co.nz"

# Check health endpoint
RESPONSE=$(curl -s -w "\n%{http_code}" "$HEALTH_URL")
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)
BODY=$(echo "$RESPONSE" | head -n -1)

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

if [ "$HTTP_CODE" -eq 200 ]; then
    echo "[$TIMESTAMP] ✓ Health check passed" >> "$LOG_FILE"

    # Check if response indicates healthy
    if echo "$BODY" | grep -q '"status":"healthy"'; then
        echo "[$TIMESTAMP] ✓ System status: HEALTHY" >> "$LOG_FILE"
    else
        echo "[$TIMESTAMP] ⚠️ System status: UNHEALTHY" >> "$LOG_FILE"
        echo "$BODY" >> "$LOG_FILE"

        # Send alert email
        echo "AI Agent health check reported unhealthy status at $TIMESTAMP" | \
            mail -s "AI Agent Health Alert" "$ALERT_EMAIL"
    fi
else
    echo "[$TIMESTAMP] ✗ Health check failed (HTTP $HTTP_CODE)" >> "$LOG_FILE"

    # Send alert email
    echo "AI Agent health check failed with HTTP $HTTP_CODE at $TIMESTAMP" | \
        mail -s "AI Agent Health CRITICAL" "$ALERT_EMAIL"
fi

# Rotate log if > 10MB
LOG_SIZE=$(stat -f%z "$LOG_FILE" 2>/dev/null || stat -c%s "$LOG_FILE" 2>/dev/null || echo 0)
if [ "$LOG_SIZE" -gt 10485760 ]; then
    mv "$LOG_FILE" "$LOG_FILE.old"
    gzip "$LOG_FILE.old"
fi
EOF

chmod +x "$AI_AGENT_ROOT/bin/health-monitor.sh"
echo -e "  ${GREEN}✓ Created health-monitor.sh${NC}"

# Create cron job entry
CRON_ENTRY="*/5 * * * * $AI_AGENT_ROOT/bin/health-monitor.sh"
echo "$CRON_ENTRY" > "$AI_AGENT_ROOT/cron/health-monitoring.cron"

echo -e "  ${GREEN}✓ Created cron entry${NC}"
echo -e "  ${YELLOW}⚠️  MANUAL STEP REQUIRED:${NC}"
echo "     Add to crontab: $CRON_ENTRY"
echo "     Run: crontab -e"

echo ""

# ═══════════════════════════════════════════════════════════════
# COMPLETION SUMMARY
# ═══════════════════════════════════════════════════════════════

echo "═══════════════════════════════════════════════════════════════"
echo -e "${GREEN}🎉 P0 CRITICAL FIXES COMPLETED${NC}"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo -e "${GREEN}✓ COMPLETED AUTOMATICALLY:${NC}"
echo "  [1] Multi-KB database schema deployed"
echo "  [2] .env database configuration updated"
echo "  [3] API keys generated and saved"
echo "  [4] Health monitoring script created"
echo ""
echo -e "${YELLOW}⚠️  MANUAL STEPS REQUIRED:${NC}"
echo "  [ ] Insert auth_patch.php into chat-enterprise.php at line 828"
echo "  [ ] Replace line 122 of chat-enterprise.php with cors_patch.php"
echo "  [ ] Add health monitoring to crontab"
echo ""
echo -e "${GREEN}📁 FILES CREATED:${NC}"
echo "  - config/api_keys.txt (API keys - SECURE THIS)"
echo "  - api/auth_patch.php (Authentication code)"
echo "  - api/cors_patch.php (CORS restriction code)"
echo "  - bin/health-monitor.sh (Health monitoring script)"
echo "  - cron/health-monitoring.cron (Cron entry)"
echo ""
echo -e "${GREEN}🔑 API KEYS GENERATED:${NC}"
echo "  STAFF: $KEY_STAFF"
echo "  WEB: $KEY_WEB"
echo "  GPT: $KEY_GPT"
echo ""
echo "  Saved to: $AI_AGENT_ROOT/config/api_keys.txt"
echo ""
echo -e "${GREEN}📋 NEXT STEPS:${NC}"
echo "  1. Review and apply manual patches"
echo "  2. Test endpoints with new API keys"
echo "  3. Verify health monitoring"
echo "  4. Proceed to P1 fixes"
echo ""
echo "═══════════════════════════════════════════════════════════════"

exit 0
