#!/bin/bash
#
# 🔍 AI-AGENT PRE-FLIGHT CHECK SYSTEM
#
# Intelligent verification system that:
# - Checks database connectivity and tables
# - Validates .env configuration
# - Tests all API endpoints
# - Verifies file permissions
# - Checks PHP dependencies
# - Validates API keys
#
# Run BEFORE and AFTER deployment to ensure system health
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Counters
CHECKS_PASSED=0
CHECKS_FAILED=0
CHECKS_WARNING=0
CHECKS_TOTAL=0

# Configuration
AI_AGENT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent"
LOG_FILE="$AI_AGENT_ROOT/logs/pre-flight-$(date +%Y%m%d_%H%M%S).log"

# Ensure log directory exists
mkdir -p "$AI_AGENT_ROOT/logs"

# Log function
log() {
    echo "$1" | tee -a "$LOG_FILE"
}

# Check function
check() {
    local name="$1"
    local command="$2"
    local critical="${3:-no}"

    CHECKS_TOTAL=$((CHECKS_TOTAL + 1))

    echo -ne "${CYAN}[CHECK $CHECKS_TOTAL]${NC} $name... "
    log "[CHECK $CHECKS_TOTAL] $name"

    if eval "$command" >> "$LOG_FILE" 2>&1; then
        echo -e "${GREEN}✓ PASS${NC}"
        log "✓ PASS"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
        return 0
    else
        if [ "$critical" == "yes" ]; then
            echo -e "${RED}✗ FAIL (CRITICAL)${NC}"
            log "✗ FAIL (CRITICAL)"
            CHECKS_FAILED=$((CHECKS_FAILED + 1))
            return 1
        else
            echo -e "${YELLOW}⚠ WARNING${NC}"
            log "⚠ WARNING"
            CHECKS_WARNING=$((CHECKS_WARNING + 1))
            return 0
        fi
    fi
}

# Banner
clear
log "═══════════════════════════════════════════════════════════════"
log "🔍 AI-AGENT PRE-FLIGHT CHECK SYSTEM"
log "═══════════════════════════════════════════════════════════════"
log "Started: $(date)"
log "Log: $LOG_FILE"
log ""

# ═══════════════════════════════════════════════════════════════
# SECTION 1: ENVIRONMENT CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 1: ENVIRONMENT CHECKS ━━━${NC}\n"
log "━━━ SECTION 1: ENVIRONMENT CHECKS ━━━"

check "PHP Binary Available" "command -v php" "yes"
check "MySQL Client Available" "command -v mysql" "yes"
check "OpenSSL Available" "command -v openssl" "yes"
check "Curl Available" "command -v curl" "yes"

check "PHP Version >= 8.1" "php -r 'exit(version_compare(PHP_VERSION, \"8.1.0\", \">=\") ? 0 : 1);'" "yes"

check "PHP Extension: PDO" "php -m | grep -q PDO" "yes"
check "PHP Extension: pdo_mysql" "php -m | grep -q pdo_mysql" "yes"
check "PHP Extension: redis" "php -m | grep -q redis" "no"
check "PHP Extension: curl" "php -m | grep -q curl" "yes"
check "PHP Extension: json" "php -m | grep -q json" "yes"
check "PHP Extension: mbstring" "php -m | grep -q mbstring" "yes"

# ═══════════════════════════════════════════════════════════════
# SECTION 2: FILE SYSTEM CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 2: FILE SYSTEM CHECKS ━━━${NC}\n"
log "━━━ SECTION 2: FILE SYSTEM CHECKS ━━━"

check "AI-Agent Root Exists" "test -d '$AI_AGENT_ROOT'" "yes"
check "src/ Directory Exists" "test -d '$AI_AGENT_ROOT/src'" "yes"
check "api/ Directory Exists" "test -d '$AI_AGENT_ROOT/api'" "yes"
check "database/ Directory Exists" "test -d '$AI_AGENT_ROOT/database'" "yes"
check "config/ Directory Exists" "test -d '$AI_AGENT_ROOT/config'" "yes"
check "logs/ Directory Writable" "test -w '$AI_AGENT_ROOT/logs'" "yes"

check ".env File Exists" "test -f '$AI_AGENT_ROOT/.env'" "yes"
check ".env File Readable" "test -r '$AI_AGENT_ROOT/.env'" "yes"

check "Agent.php Exists" "test -f '$AI_AGENT_ROOT/src/Agent.php'" "yes"
check "chat-enterprise.php Exists" "test -f '$AI_AGENT_ROOT/api/chat-enterprise.php'" "yes"
check "health.php Exists" "test -f '$AI_AGENT_ROOT/api/health.php'" "yes"

check "autoload.php Exists" "test -f '$AI_AGENT_ROOT/autoload.php'" "yes"
check "composer.json Exists" "test -f '$AI_AGENT_ROOT/composer.json'" "no"

# ═══════════════════════════════════════════════════════════════
# SECTION 3: CONFIGURATION CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 3: CONFIGURATION CHECKS ━━━${NC}\n"
log "━━━ SECTION 3: CONFIGURATION CHECKS ━━━"

cd "$AI_AGENT_ROOT"

check ".env: MYSQL_HOST Set" "grep -q '^MYSQL_HOST=' .env" "yes"
check ".env: MYSQL_USER Set" "grep -q '^MYSQL_USER=' .env" "yes"
check ".env: MYSQL_DATABASE Set" "grep -q '^MYSQL_DATABASE=' .env" "yes"

check ".env: MYSQL_USER=hdgwrzntwa" "grep -q '^MYSQL_USER=hdgwrzntwa' .env" "yes"
check ".env: MYSQL_DATABASE=hdgwrzntwa" "grep -q '^MYSQL_DATABASE=hdgwrzntwa' .env" "yes"

check ".env: ANTHROPIC_API_KEY Set" "grep -q '^ANTHROPIC_API_KEY=' .env && ! grep -q '^ANTHROPIC_API_KEY=$' .env" "yes"
check ".env: OPENAI_API_KEY Set" "grep -q '^OPENAI_API_KEY=' .env && ! grep -q '^OPENAI_API_KEY=$' .env" "yes"

check ".env: API_KEYS Set" "grep -q '^API_KEYS=' .env" "no"
check ".env: REDIS_URL Set" "grep -q '^REDIS_URL=' .env" "no"

# ═══════════════════════════════════════════════════════════════
# SECTION 4: DATABASE CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 4: DATABASE CHECKS ━━━${NC}\n"
log "━━━ SECTION 4: DATABASE CHECKS ━━━"

# Extract database credentials
DB_HOST=$(grep '^MYSQL_HOST=' .env | cut -d'=' -f2)
DB_USER=$(grep '^MYSQL_USER=' .env | cut -d'=' -f2)
DB_PASS=$(grep '^MYSQL_PASSWORD=' .env | cut -d'=' -f2)
DB_NAME=$(grep '^MYSQL_DATABASE=' .env | cut -d'=' -f2)

if [ -z "$DB_PASS" ]; then
    echo -e "${YELLOW}⚠ No password in .env, will prompt${NC}"
    read -sp "Enter MySQL password for $DB_USER: " DB_PASS
    echo ""
fi

check "MySQL Connection" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' -e 'SELECT 1;' > /dev/null 2>&1" "yes"

if [ $? -eq 0 ]; then
    check "Database '$DB_NAME' Exists" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' -e 'USE $DB_NAME;' > /dev/null 2>&1" "yes"

    # Check for required tables
    check "Table: ai_kb_domain_registry" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_domain_registry;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_domain_inheritance" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_domain_inheritance;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_files" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_files;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_categories" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_categories;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_file_categories" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_file_categories;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_semantic_tags" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_semantic_tags;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_file_tags" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_file_tags;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_embeddings" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_embeddings;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_search_log" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_search_log;' > /dev/null 2>&1" "yes"
    check "Table: ai_kb_analytics" "mysql -h '$DB_HOST' -u '$DB_USER' -p'$DB_PASS' '$DB_NAME' -e 'DESCRIBE ai_kb_analytics;' > /dev/null 2>&1" "yes"

    # Check domain data
    echo -ne "${CYAN}[CHECK $((CHECKS_TOTAL+1))]${NC} Domain Registry Has Data... "
    DOMAIN_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM ai_kb_domain_registry;" 2>/dev/null)
    CHECKS_TOTAL=$((CHECKS_TOTAL + 1))

    if [ "$DOMAIN_COUNT" -ge 6 ]; then
        echo -e "${GREEN}✓ PASS ($DOMAIN_COUNT domains)${NC}"
        log "✓ PASS - Domain Registry Has Data ($DOMAIN_COUNT domains)"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
    else
        echo -e "${YELLOW}⚠ WARNING (Only $DOMAIN_COUNT domains, expected 6+)${NC}"
        log "⚠ WARNING - Domain Registry Has Data (Only $DOMAIN_COUNT domains)"
        CHECKS_WARNING=$((CHECKS_WARNING + 1))
    fi
fi

# ═══════════════════════════════════════════════════════════════
# SECTION 5: API ENDPOINT CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 5: API ENDPOINT CHECKS ━━━${NC}\n"
log "━━━ SECTION 5: API ENDPOINT CHECKS ━━━"

# Determine base URL
if [ -f "$AI_AGENT_ROOT/.env" ] && grep -q "^APP_URL=" "$AI_AGENT_ROOT/.env"; then
    BASE_URL=$(grep "^APP_URL=" "$AI_AGENT_ROOT/.env" | cut -d'=' -f2 | tr -d '"')
else
    BASE_URL="https://gpt.ecigdis.co.nz/ai-agent"
fi

echo -e "${CYAN}Base URL: $BASE_URL${NC}\n"
log "Base URL: $BASE_URL"

# Health check (no auth required)
echo -ne "${CYAN}[CHECK $((CHECKS_TOTAL+1))]${NC} API: health.php responds... "
CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
log "[CHECK $CHECKS_TOTAL] API: health.php responds"

HEALTH_RESPONSE=$(curl -s -o /tmp/health_response.json -w "%{http_code}" "$BASE_URL/api/health.php" 2>&1)

if [ "$HEALTH_RESPONSE" == "200" ]; then
    echo -e "${GREEN}✓ PASS (HTTP 200)${NC}"
    log "✓ PASS (HTTP 200)"
    CHECKS_PASSED=$((CHECKS_PASSED + 1))

    # Validate JSON
    if jq empty /tmp/health_response.json 2>/dev/null; then
        echo -e "${GREEN}  → Valid JSON response${NC}"
        log "  → Valid JSON response"

        # Check status field
        STATUS=$(jq -r '.status' /tmp/health_response.json 2>/dev/null)
        if [ "$STATUS" == "healthy" ]; then
            echo -e "${GREEN}  → Status: healthy${NC}"
            log "  → Status: healthy"
        else
            echo -e "${YELLOW}  → Status: $STATUS${NC}"
            log "  → Status: $STATUS"
        fi

        # Show response
        echo -e "${CYAN}  → Response:${NC}"
        jq '.' /tmp/health_response.json 2>/dev/null | head -20
    else
        echo -e "${YELLOW}  → Invalid JSON response${NC}"
        log "  → Invalid JSON response"
    fi
else
    echo -e "${RED}✗ FAIL (HTTP $HEALTH_RESPONSE)${NC}"
    log "✗ FAIL (HTTP $HEALTH_RESPONSE)"
    CHECKS_FAILED=$((CHECKS_FAILED + 1))
fi

# Test chat-enterprise.php (should require auth)
echo -ne "${CYAN}[CHECK $((CHECKS_TOTAL+1))]${NC} API: chat-enterprise.php requires auth... "
CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
log "[CHECK $CHECKS_TOTAL] API: chat-enterprise.php requires auth"

CHAT_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/api/chat-enterprise.php" \
    -H "Content-Type: application/json" \
    -d '{"message":"test"}' 2>&1)

if [ "$CHAT_RESPONSE" == "401" ]; then
    echo -e "${GREEN}✓ PASS (HTTP 401 - Auth Required)${NC}"
    log "✓ PASS (HTTP 401 - Auth Required)"
    CHECKS_PASSED=$((CHECKS_PASSED + 1))
elif [ "$CHAT_RESPONSE" == "200" ]; then
    echo -e "${RED}✗ FAIL (HTTP 200 - No Auth!)${NC}"
    log "✗ FAIL (HTTP 200 - No Auth!)"
    CHECKS_FAILED=$((CHECKS_FAILED + 1))
    echo -e "${RED}  → SECURITY ISSUE: Endpoint accessible without authentication!${NC}"
    log "  → SECURITY ISSUE: Endpoint accessible without authentication!"
else
    echo -e "${YELLOW}⚠ WARNING (HTTP $CHAT_RESPONSE)${NC}"
    log "⚠ WARNING (HTTP $CHAT_RESPONSE)"
    CHECKS_WARNING=$((CHECKS_WARNING + 1))
fi

# Test with API key (if available)
if [ -f "$AI_AGENT_ROOT/config/api_keys.txt" ]; then
    API_KEY=$(head -n 1 "$AI_AGENT_ROOT/config/api_keys.txt" 2>/dev/null)

    if [ -n "$API_KEY" ]; then
        echo -ne "${CYAN}[CHECK $((CHECKS_TOTAL+1))]${NC} API: chat-enterprise.php accepts valid key... "
        CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
        log "[CHECK $CHECKS_TOTAL] API: chat-enterprise.php accepts valid key"

        AUTH_RESPONSE=$(curl -s -o /tmp/auth_response.json -w "%{http_code}" -X POST "$BASE_URL/api/chat-enterprise.php" \
            -H "Content-Type: application/json" \
            -H "X-API-KEY: $API_KEY" \
            -d '{"message":"Hello, test message"}' 2>&1)

        if [ "$AUTH_RESPONSE" == "200" ]; then
            echo -e "${GREEN}✓ PASS (HTTP 200 - Authenticated)${NC}"
            log "✓ PASS (HTTP 200 - Authenticated)"
            CHECKS_PASSED=$((CHECKS_PASSED + 1))

            # Check if response is streaming or JSON
            if jq empty /tmp/auth_response.json 2>/dev/null; then
                echo -e "${GREEN}  → Valid JSON response${NC}"
                log "  → Valid JSON response"
            else
                echo -e "${CYAN}  → Streaming response (SSE)${NC}"
                log "  → Streaming response (SSE)"
            fi
        else
            echo -e "${RED}✗ FAIL (HTTP $AUTH_RESPONSE)${NC}"
            log "✗ FAIL (HTTP $AUTH_RESPONSE)"
            CHECKS_FAILED=$((CHECKS_FAILED + 1))
        fi
    fi
fi

# ═══════════════════════════════════════════════════════════════
# SECTION 6: REDIS CHECKS (OPTIONAL)
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 6: REDIS CHECKS (OPTIONAL) ━━━${NC}\n"
log "━━━ SECTION 6: REDIS CHECKS (OPTIONAL) ━━━"

if grep -q '^REDIS_URL=' "$AI_AGENT_ROOT/.env" 2>/dev/null; then
    REDIS_URL=$(grep '^REDIS_URL=' "$AI_AGENT_ROOT/.env" | cut -d'=' -f2)

    check "Redis Ping" "redis-cli -u '$REDIS_URL' ping | grep -q PONG" "no"
else
    echo -e "${YELLOW}⚠ Redis not configured (optional)${NC}"
    log "⚠ Redis not configured (optional)"
fi

# ═══════════════════════════════════════════════════════════════
# SECTION 7: SECURITY CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}━━━ SECTION 7: SECURITY CHECKS ━━━${NC}\n"
log "━━━ SECTION 7: SECURITY CHECKS ━━━"

check ".env Not Web Accessible" "curl -s -o /dev/null -w '%{http_code}' '$BASE_URL/.env' | grep -q '403\|404'" "yes"
check "config/ Not Web Accessible" "curl -s -o /dev/null -w '%{http_code}' '$BASE_URL/config/' | grep -q '403\|404'" "yes"
check "logs/ Not Web Accessible" "curl -s -o /dev/null -w '%{http_code}' '$BASE_URL/logs/' | grep -q '403\|404'" "no"

check ".env File Permissions (not 777)" "test \$(stat -c '%a' '$AI_AGENT_ROOT/.env') != '777'" "yes"
check "config/ Directory Permissions" "test -d '$AI_AGENT_ROOT/config' && test \$(stat -c '%a' '$AI_AGENT_ROOT/config') != '777'" "no"

# ═══════════════════════════════════════════════════════════════
# FINAL REPORT
# ═══════════════════════════════════════════════════════════════

echo -e "\n${BLUE}═══════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}FINAL REPORT${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════════${NC}\n"

log ""
log "═══════════════════════════════════════════════════════════════"
log "FINAL REPORT"
log "═══════════════════════════════════════════════════════════════"

echo -e "Total Checks:  ${CYAN}$CHECKS_TOTAL${NC}"
echo -e "Passed:        ${GREEN}$CHECKS_PASSED${NC}"
echo -e "Failed:        ${RED}$CHECKS_FAILED${NC}"
echo -e "Warnings:      ${YELLOW}$CHECKS_WARNING${NC}"

log "Total Checks:  $CHECKS_TOTAL"
log "Passed:        $CHECKS_PASSED"
log "Failed:        $CHECKS_FAILED"
log "Warnings:      $CHECKS_WARNING"

PASS_RATE=$((CHECKS_PASSED * 100 / CHECKS_TOTAL))

echo -e "\nPass Rate:     ${CYAN}$PASS_RATE%${NC}"
log "Pass Rate:     $PASS_RATE%"

if [ $CHECKS_FAILED -eq 0 ]; then
    echo -e "\n${GREEN}✓ ALL CRITICAL CHECKS PASSED${NC}"
    log "✓ ALL CRITICAL CHECKS PASSED"

    if [ $CHECKS_WARNING -gt 0 ]; then
        echo -e "${YELLOW}⚠ $CHECKS_WARNING warnings (non-critical)${NC}"
        log "⚠ $CHECKS_WARNING warnings (non-critical)"
    fi

    echo -e "\n${GREEN}🚀 SYSTEM READY FOR DEPLOYMENT${NC}"
    log "🚀 SYSTEM READY FOR DEPLOYMENT"
    exit 0
else
    echo -e "\n${RED}✗ $CHECKS_FAILED CRITICAL CHECKS FAILED${NC}"
    log "✗ $CHECKS_FAILED CRITICAL CHECKS FAILED"
    echo -e "${RED}⚠ PLEASE FIX ISSUES BEFORE DEPLOYMENT${NC}"
    log "⚠ PLEASE FIX ISSUES BEFORE DEPLOYMENT"
    exit 1
fi
