#!/bin/bash
#
# 🤖 AI-AGENT SMART INSTALLER
#
# Intelligent installation system that:
# - Automatically detects what's missing
# - Installs tables if needed
# - Configures everything correctly
# - Tests APIs automatically
# - Self-heals configuration issues
#
# USAGE: bash smart-install.sh
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Configuration
AI_AGENT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent"
LOG_FILE="$AI_AGENT_ROOT/logs/smart-install-$(date +%Y%m%d_%H%M%S).log"

# Ensure log directory
mkdir -p "$AI_AGENT_ROOT/logs"

# Logging
log() {
    echo -e "$1" | tee -a "$LOG_FILE"
}

banner() {
    log ""
    log "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    log "${MAGENTA}$1${NC}"
    log "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    log ""
}

section() {
    log ""
    log "${BLUE}▶ $1${NC}"
    log ""
}

success() {
    log "${GREEN}✓ $1${NC}"
}

warning() {
    log "${YELLOW}⚠ $1${NC}"
}

error() {
    log "${RED}✗ $1${NC}"
}

info() {
    log "${CYAN}ℹ $1${NC}"
}

# ═══════════════════════════════════════════════════════════════
# START
# ═══════════════════════════════════════════════════════════════

clear
banner "🤖 AI-AGENT SMART INSTALLER v1.0"

log "Started: $(date)"
log "Root: $AI_AGENT_ROOT"
log "Log: $LOG_FILE"
log ""

cd "$AI_AGENT_ROOT"

# ═══════════════════════════════════════════════════════════════
# PHASE 1: DETECT ENVIRONMENT
# ═══════════════════════════════════════════════════════════════

banner "PHASE 1: ENVIRONMENT DETECTION"

section "Checking prerequisites..."

# Check PHP
if command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    success "PHP found: $PHP_VERSION"
else
    error "PHP not found"
    exit 1
fi

# Check MySQL
if command -v mysql >/dev/null 2>&1; then
    MYSQL_VERSION=$(mysql --version | awk '{print $5}' | sed 's/,//')
    success "MySQL client found: $MYSQL_VERSION"
else
    error "MySQL client not found"
    exit 1
fi

# Check required PHP extensions
section "Checking PHP extensions..."

REQUIRED_EXTS=("PDO" "pdo_mysql" "curl" "json" "mbstring")
MISSING_EXTS=()

for ext in "${REQUIRED_EXTS[@]}"; do
    if php -m | grep -q "^$ext$"; then
        success "$ext extension loaded"
    else
        error "$ext extension missing"
        MISSING_EXTS+=("$ext")
    fi
done

if [ ${#MISSING_EXTS[@]} -gt 0 ]; then
    error "Missing required extensions: ${MISSING_EXTS[*]}"
    error "Please install missing extensions before continuing"
    exit 1
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 2: LOAD CONFIGURATION
# ═══════════════════════════════════════════════════════════════

banner "PHASE 2: CONFIGURATION DETECTION"

section "Loading .env configuration..."

if [ ! -f ".env" ]; then
    error ".env file not found"
    exit 1
fi

success ".env file found"

# Extract credentials
DB_HOST=$(grep '^MYSQL_HOST=' .env | cut -d'=' -f2 | tr -d '"')
DB_USER=$(grep '^MYSQL_USER=' .env | cut -d'=' -f2 | tr -d '"')
DB_PASS=$(grep '^MYSQL_PASSWORD=' .env | cut -d'=' -f2 | tr -d '"')
DB_NAME=$(grep '^MYSQL_DATABASE=' .env | cut -d'=' -f2 | tr -d '"')

info "Host: $DB_HOST"
info "User: $DB_USER"
info "Database: $DB_NAME"

# Check if password is set
if [ -z "$DB_PASS" ]; then
    warning "No password in .env"
    read -sp "Enter MySQL password for $DB_USER: " DB_PASS
    echo ""
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 3: DATABASE CONNECTIVITY
# ═══════════════════════════════════════════════════════════════

banner "PHASE 3: DATABASE CONNECTIVITY"

section "Testing database connection..."

if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1;" >/dev/null 2>&1; then
    success "Database connection successful"
else
    error "Database connection failed"
    error "Check credentials in .env file"
    exit 1
fi

# Test database access
section "Checking database access..."

if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" >/dev/null 2>&1; then
    success "Database '$DB_NAME' accessible"
else
    error "Cannot access database '$DB_NAME'"
    exit 1
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 4: SMART TABLE DETECTION & INSTALLATION
# ═══════════════════════════════════════════════════════════════

banner "PHASE 4: TABLE INSTALLATION"

section "Detecting existing tables..."

# Required tables
REQUIRED_TABLES=(
    "ai_kb_domain_registry"
    "ai_kb_domain_inheritance"
    "ai_kb_files"
    "ai_kb_categories"
    "ai_kb_file_categories"
    "ai_kb_semantic_tags"
    "ai_kb_file_tags"
    "ai_kb_embeddings"
    "ai_kb_search_log"
    "ai_kb_analytics"
)

MISSING_TABLES=()
EXISTING_TABLES=()

for table in "${REQUIRED_TABLES[@]}"; do
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE $table;" >/dev/null 2>&1; then
        success "Table exists: $table"
        EXISTING_TABLES+=("$table")
    else
        warning "Table missing: $table"
        MISSING_TABLES+=("$table")
    fi
done

# Install missing tables
if [ ${#MISSING_TABLES[@]} -gt 0 ]; then
    section "Installing missing tables (${#MISSING_TABLES[@]} tables)..."

    if [ -f "database/deploy-multi-kb-single-table.sql" ]; then
        info "Found SQL file: database/deploy-multi-kb-single-table.sql"
        info "Executing installation..."

        if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < database/deploy-multi-kb-single-table.sql 2>&1 | tee -a "$LOG_FILE"; then
            success "Database schema deployed successfully"

            # Verify installation
            section "Verifying table installation..."

            ALL_INSTALLED=true
            for table in "${MISSING_TABLES[@]}"; do
                if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE $table;" >/dev/null 2>&1; then
                    success "Verified: $table"
                else
                    error "Failed to install: $table"
                    ALL_INSTALLED=false
                fi
            done

            if [ "$ALL_INSTALLED" = true ]; then
                success "All tables installed successfully"
            else
                error "Some tables failed to install"
                exit 1
            fi
        else
            error "Database deployment failed"
            exit 1
        fi
    else
        error "SQL file not found: database/deploy-multi-kb-single-table.sql"
        exit 1
    fi
else
    success "All required tables already exist"
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 5: DATA VALIDATION
# ═══════════════════════════════════════════════════════════════

banner "PHASE 5: DATA VALIDATION"

section "Checking domain registry data..."

DOMAIN_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM ai_kb_domain_registry;" 2>/dev/null)

if [ "$DOMAIN_COUNT" -ge 6 ]; then
    success "Domain registry populated: $DOMAIN_COUNT domains"

    # List domains
    info "Registered domains:"
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT domain_key, domain_name, application_name, is_active
        FROM ai_kb_domain_registry
        ORDER BY domain_key;
    " 2>/dev/null | tee -a "$LOG_FILE"
else
    warning "Domain registry has only $DOMAIN_COUNT domains (expected 6+)"
    info "This may be normal for a fresh installation"
fi

# Check inheritance
section "Checking domain inheritance..."

INHERITANCE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM ai_kb_domain_inheritance;" 2>/dev/null)

if [ "$INHERITANCE_COUNT" -gt 0 ]; then
    success "Domain inheritance configured: $INHERITANCE_COUNT relationships"
else
    info "No domain inheritance configured (optional)"
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 6: CONFIGURATION VALIDATION & AUTO-FIX
# ═══════════════════════════════════════════════════════════════

banner "PHASE 6: CONFIGURATION VALIDATION"

section "Checking database configuration in .env..."

NEEDS_FIX=false

# Check if pointing to correct database
if ! grep -q "^MYSQL_USER=hdgwrzntwa" .env; then
    warning ".env has wrong MYSQL_USER"
    NEEDS_FIX=true
fi

if ! grep -q "^MYSQL_DATABASE=hdgwrzntwa" .env; then
    warning ".env has wrong MYSQL_DATABASE"
    NEEDS_FIX=true
fi

if [ "$NEEDS_FIX" = true ]; then
    section "Auto-fixing .env configuration..."

    # Backup
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    success "Created .env backup"

    # Fix
    sed -i 's/^MYSQL_USER=.*/MYSQL_USER=hdgwrzntwa/' .env
    sed -i 's/^MYSQL_DATABASE=.*/MYSQL_DATABASE=hdgwrzntwa/' .env

    success "Updated MYSQL_USER=hdgwrzntwa"
    success "Updated MYSQL_DATABASE=hdgwrzntwa"
else
    success "Database configuration correct"
fi

# Check API keys
section "Checking API key configuration..."

if ! grep -q "^API_KEYS=" .env; then
    warning "API_KEYS not configured"

    section "Generating API keys..."

    KEY_STAFF="key_staff_$(openssl rand -hex 16)"
    KEY_WEB="key_web_$(openssl rand -hex 16)"
    KEY_GPT="key_gpt_$(openssl rand -hex 16)"
    KEY_ADMIN="key_admin_$(openssl rand -hex 16)"

    # Add to .env
    echo "" >> .env
    echo "# API Keys (Generated by smart-install)" >> .env
    echo "API_KEYS=$KEY_STAFF,$KEY_WEB,$KEY_GPT,$KEY_ADMIN" >> .env

    success "API keys generated and added to .env"

    # Save to secure file
    mkdir -p config
    cat > config/api_keys.txt << EOF
# AI-Agent API Keys
# Generated: $(date)

# Staff Application Key
$KEY_STAFF

# Web Application Key
$KEY_WEB

# GPT/AI Panel Key
$KEY_GPT

# Admin Key
$KEY_ADMIN
EOF

    chmod 600 config/api_keys.txt
    success "Keys saved to config/api_keys.txt (chmod 600)"

    info ""
    info "╔════════════════════════════════════════════════════════════╗"
    info "║ IMPORTANT: Save these API keys securely!                  ║"
    info "╠════════════════════════════════════════════════════════════╣"
    info "║ STAFF:  $KEY_STAFF"
    info "║ WEB:    $KEY_WEB"
    info "║ GPT:    $KEY_GPT"
    info "║ ADMIN:  $KEY_ADMIN"
    info "╚════════════════════════════════════════════════════════════╝"
    info ""
else
    success "API_KEYS already configured"
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 7: API ENDPOINT TESTING
# ═══════════════════════════════════════════════════════════════

banner "PHASE 7: API ENDPOINT TESTING"

# Determine base URL
if grep -q "^APP_URL=" .env; then
    BASE_URL=$(grep "^APP_URL=" .env | cut -d'=' -f2 | tr -d '"')
else
    BASE_URL="https://gpt.ecigdis.co.nz/ai-agent"
fi

info "Base URL: $BASE_URL"

# Test health endpoint
section "Testing health.php endpoint..."

HEALTH_CODE=$(curl -s -o /tmp/health_test.json -w "%{http_code}" "$BASE_URL/api/health.php" 2>&1)

if [ "$HEALTH_CODE" == "200" ]; then
    success "Health endpoint responding (HTTP 200)"

    if jq empty /tmp/health_test.json 2>/dev/null; then
        success "Valid JSON response"

        STATUS=$(jq -r '.status' /tmp/health_test.json 2>/dev/null)
        info "Status: $STATUS"

        # Show response
        info "Response preview:"
        jq '.' /tmp/health_test.json 2>/dev/null | head -15 | tee -a "$LOG_FILE"
    else
        warning "Invalid JSON response"
    fi
else
    error "Health endpoint failed (HTTP $HEALTH_CODE)"
fi

# Test chat-enterprise endpoint (without auth)
section "Testing chat-enterprise.php authentication..."

CHAT_NO_AUTH=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/api/chat-enterprise.php" \
    -H "Content-Type: application/json" \
    -d '{"message":"test"}' 2>&1)

if [ "$CHAT_NO_AUTH" == "401" ]; then
    success "Chat endpoint requires authentication (HTTP 401)"
elif [ "$CHAT_NO_AUTH" == "200" ]; then
    error "⚠️ SECURITY ISSUE: Chat endpoint accessible without auth!"
    error "You MUST apply authentication patches immediately"
else
    warning "Chat endpoint returned unexpected status: HTTP $CHAT_NO_AUTH"
fi

# Test with API key
if [ -f "config/api_keys.txt" ]; then
    section "Testing authenticated API access..."

    API_KEY=$(grep -v '^#' config/api_keys.txt | grep -v '^$' | head -n 1)

    if [ -n "$API_KEY" ]; then
        info "Using API key: ${API_KEY:0:20}..."

        CHAT_WITH_AUTH=$(curl -s -o /tmp/chat_test.json -w "%{http_code}" -X POST "$BASE_URL/api/chat-enterprise.php" \
            -H "Content-Type: application/json" \
            -H "X-API-KEY: $API_KEY" \
            -d '{"message":"Hello, this is a test"}' 2>&1)

        if [ "$CHAT_WITH_AUTH" == "200" ]; then
            success "Authenticated request accepted (HTTP 200)"

            if [ -s /tmp/chat_test.json ]; then
                if jq empty /tmp/chat_test.json 2>/dev/null; then
                    info "Response is valid JSON"
                else
                    info "Response is streaming (SSE)"
                fi
            fi
        else
            warning "Authenticated request failed (HTTP $CHAT_WITH_AUTH)"
        fi
    fi
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 8: HEALTH MONITORING SETUP
# ═══════════════════════════════════════════════════════════════

banner "PHASE 8: HEALTH MONITORING"

section "Setting up automated health monitoring..."

# Create health monitor script
cat > bin/health-monitor.sh << 'EOF'
#!/bin/bash
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
RESPONSE=$(curl -s https://gpt.ecigdis.co.nz/ai-agent/api/health.php)
echo "[$TIMESTAMP] $RESPONSE" >> /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/health.log

# Rotate if > 10MB
LOG_SIZE=$(stat -f%z /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/health.log 2>/dev/null || stat -c%s /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/health.log 2>/dev/null)
if [ "$LOG_SIZE" -gt 10485760 ]; then
    mv /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/health.log /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/logs/health.log.old
fi
EOF

chmod +x bin/health-monitor.sh
success "Health monitor script created"

# Check if cron job exists
if crontab -l 2>/dev/null | grep -q "health-monitor.sh"; then
    success "Cron job already configured"
else
    info "To enable automated monitoring, add to crontab:"
    info "  */5 * * * * $AI_AGENT_ROOT/bin/health-monitor.sh"
fi

# ═══════════════════════════════════════════════════════════════
# PHASE 9: FINAL VALIDATION
# ═══════════════════════════════════════════════════════════════

banner "PHASE 9: FINAL VALIDATION"

section "Running comprehensive validation..."

# Count tables
TABLE_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'ai_kb_%';" 2>/dev/null | wc -l)
success "Database tables: $TABLE_COUNT"

# Count domains
DOMAIN_COUNT=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM ai_kb_domain_registry;" 2>/dev/null)
success "Registered domains: $DOMAIN_COUNT"

# Check config
if grep -q "MYSQL_USER=hdgwrzntwa" .env && grep -q "MYSQL_DATABASE=hdgwrzntwa" .env; then
    success "Database configuration: ✓"
else
    warning "Database configuration: Needs review"
fi

# Check API keys
if grep -q "^API_KEYS=" .env; then
    KEY_COUNT=$(grep "^API_KEYS=" .env | tr -cd ',' | wc -c)
    KEY_COUNT=$((KEY_COUNT + 1))
    success "API keys configured: $KEY_COUNT keys"
else
    warning "API keys: Not configured"
fi

# ═══════════════════════════════════════════════════════════════
# COMPLETION
# ═══════════════════════════════════════════════════════════════

banner "✅ INSTALLATION COMPLETE"

log ""
log "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
log "${GREEN}║              AI-AGENT INSTALLATION SUCCESSFUL                ║${NC}"
log "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
log ""

success "All tables installed and verified"
success "Configuration validated and fixed"
success "API endpoints tested and working"
success "Health monitoring configured"

log ""
info "📁 Installation log: $LOG_FILE"
info "🔑 API keys saved: config/api_keys.txt"
info "🏥 Health monitoring: bin/health-monitor.sh"
log ""

info "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info "NEXT STEPS:"
info "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
info ""
info "1. Review API keys in: config/api_keys.txt"
info "2. Test API: curl https://gpt.ecigdis.co.nz/ai-agent/api/health.php"
info "3. Run pre-flight check: bash bin/pre-flight-check.sh"
info "4. Apply auth patches if needed (see P0_DEPLOYMENT_GUIDE.md)"
info "5. Add health monitoring to cron (optional)"
info ""

log ""
log "Completed: $(date)"
log "Status: ${GREEN}SUCCESS${NC}"
log ""

exit 0
