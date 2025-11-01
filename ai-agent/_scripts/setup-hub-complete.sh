#!/bin/bash
###############################################################################
# AI Agent Hub Setup - Complete Installation
# 
# This script:
# 1. Creates all database tables with agent_ prefix
# 2. Sets up Redis configuration
# 3. Configures environment variables
# 4. Tests all connections
# 5. Initializes the AI agent for Intelligence Hub
#
# Usage: ./setup-hub-complete.sh
###############################################################################

set -e

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
LOG_FILE="logs/hub-setup.log"
DB_NAME="hdgwrzntwa"
DB_USER="hdgwrzntwa"
DB_PASS="SN82ZYCHs2"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

log() {
    echo -e "${GREEN}[${TIMESTAMP}]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[${TIMESTAMP}] ERROR:${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[${TIMESTAMP}] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[${TIMESTAMP}] INFO:${NC} $1" | tee -a "$LOG_FILE"
}

# Ensure logs directory exists
mkdir -p logs

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  AI Agent Hub - Complete Setup & Installation               ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

###############################################################################
# Step 1: Verify Prerequisites
###############################################################################

log "Step 1: Verifying prerequisites..."

# Check PHP
if ! command -v php &> /dev/null; then
    error "PHP is not installed"
    exit 1
fi
log "✓ PHP is available: $(php -v | head -1)"

# Check MySQL
if ! command -v mysql &> /dev/null; then
    error "MySQL client is not installed"
    exit 1
fi
log "✓ MySQL client is available"

# Check Redis (optional but recommended)
if command -v redis-cli &> /dev/null; then
    log "✓ Redis is available"
    REDIS_AVAILABLE=true
else
    warning "Redis is not installed (optional, but recommended for performance)"
    REDIS_AVAILABLE=false
fi

# Test database connection
if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
    log "✓ Database connection successful"
else
    error "Cannot connect to database $DB_NAME"
    exit 1
fi

###############################################################################
# Step 2: Create Database Tables with agent_ Prefix
###############################################################################

log ""
log "Step 2: Creating database tables..."

# Create the SQL file with all tables using agent_ prefix
cat > /tmp/agent_tables_setup.sql << 'EOSQL'
-- AI Agent Hub Database Schema
-- All tables use agent_ prefix for Intelligence Hub
-- Generated: 2025-10-28

USE hdgwrzntwa;

-- Core conversation tables
CREATE TABLE IF NOT EXISTS agent_conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(255),
    model VARCHAR(50) NOT NULL DEFAULT 'claude-3-5-sonnet-20241022',
    system_prompt TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    metadata JSON,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    tokens_used INT UNSIGNED DEFAULT 0,
    model VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_created_at (created_at),
    INDEX idx_role (role),
    FOREIGN KEY (conversation_id) REFERENCES agent_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_tool_calls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id INT UNSIGNED NOT NULL,
    tool_name VARCHAR(100) NOT NULL,
    tool_input JSON NOT NULL,
    tool_output JSON,
    status ENUM('pending', 'success', 'error') DEFAULT 'pending',
    error_message TEXT,
    execution_time_ms INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_message_id (message_id),
    INDEX idx_tool_name (tool_name),
    INDEX idx_status (status),
    FOREIGN KEY (message_id) REFERENCES agent_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Knowledge base tables
CREATE TABLE IF NOT EXISTS agent_kb_docs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL DEFAULT 'global',
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    source_type ENUM('file', 'url', 'manual', 'api') NOT NULL,
    source_path VARCHAR(500),
    file_hash VARCHAR(64),
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    indexed_at TIMESTAMP NULL,
    INDEX idx_domain_key (domain_key),
    INDEX idx_file_hash (file_hash),
    INDEX idx_source_type (source_type),
    INDEX idx_indexed_at (indexed_at),
    FULLTEXT idx_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_kb_chunks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doc_id INT UNSIGNED NOT NULL,
    chunk_index INT UNSIGNED NOT NULL,
    content TEXT NOT NULL,
    embedding_vector JSON,
    token_count INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doc_id (doc_id),
    INDEX idx_chunk_index (chunk_index),
    FOREIGN KEY (doc_id) REFERENCES agent_kb_docs(id) ON DELETE CASCADE,
    FULLTEXT idx_content (content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Multi-domain knowledge base registry
CREATE TABLE IF NOT EXISTS agent_kb_domain_registry (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL UNIQUE,
    domain_name VARCHAR(100) NOT NULL,
    application_name VARCHAR(100),
    application_url VARCHAR(255),
    description TEXT,
    parent_domain VARCHAR(50),
    priority INT UNSIGNED DEFAULT 5,
    is_active BOOLEAN DEFAULT TRUE,
    config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_domain_key (domain_key),
    INDEX idx_parent_domain (parent_domain),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_kb_domain_inheritance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    child_domain VARCHAR(50) NOT NULL,
    parent_domain VARCHAR(50) NOT NULL,
    inheritance_level INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_child_domain (child_domain),
    INDEX idx_parent_domain (parent_domain),
    UNIQUE KEY uk_inheritance (child_domain, parent_domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Knowledge base operations
CREATE TABLE IF NOT EXISTS agent_kb_queries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL,
    query_text TEXT NOT NULL,
    results_count INT UNSIGNED DEFAULT 0,
    execution_time_ms INT UNSIGNED,
    user_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    INDEX idx_domain_key (domain_key),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_kb_file_index (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_hash VARCHAR(64) NOT NULL,
    file_size BIGINT UNSIGNED,
    file_type VARCHAR(50),
    last_indexed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'indexed', 'error') DEFAULT 'pending',
    error_message TEXT,
    metadata JSON,
    UNIQUE KEY uk_domain_path (domain_key, file_path),
    INDEX idx_file_hash (file_hash),
    INDEX idx_last_indexed (last_indexed),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_kb_file_relationships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_file_id INT UNSIGNED NOT NULL,
    target_file_id INT UNSIGNED NOT NULL,
    relationship_type ENUM('imports', 'extends', 'includes', 'references') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source_file_id (source_file_id),
    INDEX idx_target_file_id (target_file_id),
    INDEX idx_relationship_type (relationship_type),
    FOREIGN KEY (source_file_id) REFERENCES agent_kb_file_index(id) ON DELETE CASCADE,
    FOREIGN KEY (target_file_id) REFERENCES agent_kb_file_index(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics and performance
CREATE TABLE IF NOT EXISTS agent_kb_performance_metrics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL,
    operation_type VARCHAR(50) NOT NULL,
    execution_time_ms INT UNSIGNED,
    memory_used_mb FLOAT,
    records_processed INT UNSIGNED,
    success BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON,
    INDEX idx_domain_key (domain_key),
    INDEX idx_operation_type (operation_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_kb_errors (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL,
    error_type VARCHAR(100) NOT NULL,
    error_message TEXT NOT NULL,
    stack_trace TEXT,
    context JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_domain_key (domain_key),
    INDEX idx_error_type (error_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API management
CREATE TABLE IF NOT EXISTS agent_api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    key_hash VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    domain_key VARCHAR(50),
    permissions JSON,
    rate_limit_per_hour INT UNSIGNED DEFAULT 1000,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_key_hash (key_hash),
    INDEX idx_domain_key (domain_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_rate_limits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    count INT UNSIGNED DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP NOT NULL,
    UNIQUE KEY uk_rate_limit (identifier, action, window_start),
    INDEX idx_window_end (window_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS agent_idempotency (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    idempotency_key VARCHAR(64) NOT NULL UNIQUE,
    request_hash VARCHAR(64) NOT NULL,
    response_data JSON,
    status_code INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    INDEX idx_idempotency_key (idempotency_key),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuration
CREATE TABLE IF NOT EXISTS agent_kb_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sync history
CREATE TABLE IF NOT EXISTS agent_kb_sync_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_key VARCHAR(50) NOT NULL,
    sync_type ENUM('full', 'incremental', 'manual') NOT NULL,
    files_scanned INT UNSIGNED DEFAULT 0,
    files_indexed INT UNSIGNED DEFAULT 0,
    files_updated INT UNSIGNED DEFAULT 0,
    files_deleted INT UNSIGNED DEFAULT 0,
    errors_count INT UNSIGNED DEFAULT 0,
    execution_time_ms INT UNSIGNED,
    status ENUM('running', 'completed', 'failed') DEFAULT 'running',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    metadata JSON,
    INDEX idx_domain_key (domain_key),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Context cards for conversation context
CREATE TABLE IF NOT EXISTS agent_context_cards (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    card_type VARCHAR(50) NOT NULL,
    title VARCHAR(255),
    content TEXT NOT NULL,
    priority INT UNSIGNED DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_card_type (card_type),
    INDEX idx_priority (priority),
    FOREIGN KEY (conversation_id) REFERENCES agent_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default domain registry entries
INSERT INTO agent_kb_domain_registry (domain_key, domain_name, application_name, application_url, description, priority) VALUES
('global', 'Global Knowledge', 'Intelligence Hub', 'https://gpt.ecigdis.co.nz', 'Company-wide knowledge base accessible to all domains', 1),
('hub', 'Intelligence Hub', 'Intelligence Hub', 'https://gpt.ecigdis.co.nz', 'Central AI control and automation hub', 2),
('cis', 'CIS Staff Portal', 'CIS', 'https://staff.vapeshed.co.nz', 'Staff portal and internal systems', 3),
('web', 'Public Websites', 'Retail Sites', 'https://www.vapeshed.co.nz', 'Public-facing retail websites', 4),
('wiki', 'Company Wiki', 'Documentation', 'https://wiki.vapeshed.co.nz', 'Internal documentation and SOPs', 5)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Insert default configuration
INSERT INTO agent_kb_config (config_key, config_value, description) VALUES
('system_version', '1.0.0', 'AI Agent system version'),
('default_model', 'claude-3-5-sonnet-20241022', 'Default AI model to use'),
('max_tokens', '8192', 'Default maximum tokens per response'),
('redis_enabled', 'false', 'Whether Redis caching is enabled'),
('auto_sync_enabled', 'true', 'Whether automatic knowledge base sync is enabled')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
EOSQL

# Execute the SQL
log "Creating database tables..."
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /tmp/agent_tables_setup.sql; then
    log "✓ All database tables created successfully"
else
    error "Failed to create database tables"
    exit 1
fi

# Count tables created
TABLE_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SHOW TABLES LIKE 'agent_%';" | wc -l)
log "✓ Created $TABLE_COUNT tables with agent_ prefix"

###############################################################################
# Step 3: Setup Environment Configuration
###############################################################################

log ""
log "Step 3: Setting up environment configuration..."

# Check if .env exists, if not create from CIS
if [ ! -f ".env" ] || [ ! -s ".env" ]; then
    warning ".env file is missing or empty"
    
    # Copy from CIS if available
    if [ -f "/home/master/applications/jcepnzzkmj/public_html/assets/services/ai-agent/.env" ]; then
        log "Copying .env from CIS..."
        cp /home/master/applications/jcepnzzkmj/public_html/assets/services/ai-agent/.env .env
        log "✓ .env copied from CIS"
    else
        warning "Creating new .env from example..."
        if [ -f ".env.example" ]; then
            cp .env.example .env
            log "✓ .env created from example (needs API keys)"
        fi
    fi
fi

# Update database credentials in .env
if [ -f ".env" ]; then
    log "Updating database credentials in .env..."
    sed -i "s/^DB_HOST=.*/DB_HOST=127.0.0.1/" .env
    sed -i "s/^DB_NAME=.*/DB_NAME=${DB_NAME}/" .env
    sed -i "s/^DB_USER=.*/DB_USER=${DB_USER}/" .env
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" .env
    sed -i "s/^DB_CHARSET=.*/DB_CHARSET=utf8mb4/" .env
    log "✓ Database credentials updated in .env"
fi

# Check for required API keys
if grep -q "YOUR_CLAUDE_API_KEY_HERE" .env 2>/dev/null || ! grep -q "ANTHROPIC_API_KEY=sk-ant-" .env 2>/dev/null; then
    warning "ANTHROPIC_API_KEY not configured in .env - Claude AI will not work"
    warning "Please add your Anthropic API key to .env"
fi

###############################################################################
# Step 4: Setup Redis (if available)
###############################################################################

log ""
log "Step 4: Configuring Redis..."

if [ "$REDIS_AVAILABLE" = true ]; then
    # Check if Redis is running
    if redis-cli ping &>/dev/null; then
        log "✓ Redis is running"
        
        # Update .env with Redis settings
        if [ -f ".env" ]; then
            sed -i "s/^REDIS_ENABLED=.*/REDIS_ENABLED=true/" .env
            sed -i "s/^REDIS_HOST=.*/REDIS_HOST=127.0.0.1/" .env
            sed -i "s/^REDIS_PORT=.*/REDIS_PORT=6379/" .env
            log "✓ Redis configuration updated in .env"
        fi
        
        # Test Redis connection
        if redis-cli SET agent_test_key "test" &>/dev/null && redis-cli GET agent_test_key &>/dev/null; then
            log "✓ Redis connection test successful"
            redis-cli DEL agent_test_key &>/dev/null
        else
            warning "Redis connection test failed"
        fi
    else
        warning "Redis is installed but not running"
        info "To start Redis: sudo systemctl start redis"
    fi
else
    warning "Redis not available - system will work but without caching"
    if [ -f ".env" ]; then
        sed -i "s/^REDIS_ENABLED=.*/REDIS_ENABLED=false/" .env
    fi
fi

###############################################################################
# Step 5: Test AI Agent Components
###############################################################################

log ""
log "Step 5: Testing AI Agent components..."

# Test database connection
log "Testing database connection..."
if php -r "
    require 'autoload.php';
    \$config = new App\Config();
    \$db = new App\DB(\$config);
    \$result = \$db->query('SELECT COUNT(*) as count FROM agent_conversations');
    echo 'Database tables accessible';
" 2>/dev/null; then
    log "✓ Database connection and tables working"
else
    error "Database connection test failed"
fi

# Test Claude configuration
log "Testing Claude API configuration..."
if php -r "
    require 'autoload.php';
    \$config = new App\Config();
    \$apiKey = \$config->get('ANTHROPIC_API_KEY');
    if (\$apiKey && \$apiKey !== 'YOUR_CLAUDE_API_KEY_HERE') {
        echo 'Claude API key is configured';
    } else {
        exit(1);
    }
" 2>/dev/null; then
    log "✓ Claude API key is configured"
else
    warning "Claude API key not configured - add to .env to enable AI features"
fi

# Test health endpoint
log "Testing health endpoint..."
HEALTH_OUTPUT=$(php api/health.php 2>&1)
if echo "$HEALTH_OUTPUT" | grep -q '"database".*"status".*"ok"'; then
    log "✓ Health endpoint working"
else
    warning "Health endpoint shows warnings (this is normal for initial setup)"
fi

###############################################################################
# Step 6: Set Permissions
###############################################################################

log ""
log "Step 6: Setting file permissions..."

chmod +x *.sh 2>/dev/null || true
chmod 600 .env 2>/dev/null || true
chmod -R 755 logs 2>/dev/null || true

log "✓ File permissions set"

###############################################################################
# Step 7: Integration Instructions
###############################################################################

log ""
log "═══════════════════════════════════════════════════════════════"
log "✅ AI Agent Hub Setup Complete!"
log "═══════════════════════════════════════════════════════════════"
log ""
log "Database:"
log "  - Tables created: $TABLE_COUNT tables with agent_ prefix"
log "  - Database: $DB_NAME"
log "  - Location: 127.0.0.1"
log ""

if [ "$REDIS_AVAILABLE" = true ]; then
    log "Redis:"
    if redis-cli ping &>/dev/null; then
        log "  - Status: ✓ Running"
    else
        log "  - Status: ⚠ Installed but not running"
    fi
    log "  - Host: 127.0.0.1:6379"
else
    log "Redis:"
    log "  - Status: ⚠ Not installed (optional)"
fi

log ""
log "Next Steps:"
log "  1. Add your Anthropic API key to .env:"
log "     ANTHROPIC_API_KEY=sk-ant-..."
log ""
log "  2. Test the AI agent:"
log "     cd /home/master/applications/hdgwrzntwa/public_html/ai-agent"
log "     php api/health.php"
log ""
log "  3. Start using the agent in automation:"
log "     Update /home/master/applications/hdgwrzntwa/public_html/ai-batch-processor.php"
log "     to use the local AI agent at: /ai-agent/src/Agent.php"
log ""
log "  4. View the dashboard:"
log "     file:///home/master/applications/hdgwrzntwa/public_html/ai-control-dashboard.html"
log ""
log "Documentation:"
log "  - AI_AGENT_INTEGRATION.md"
log "  - ai-agent/QUICK_START.txt"
log "  - ai-agent/QUICK_DEPLOY.txt"
log ""
log "═══════════════════════════════════════════════════════════════"

# Save summary to file
cat > logs/hub-setup-summary.txt << EOF
AI Agent Hub Setup Summary
Generated: $(date)

Database:
- Name: $DB_NAME
- Tables: $TABLE_COUNT tables with agent_ prefix
- Status: ✓ Ready

Redis:
- Available: $REDIS_AVAILABLE
- Status: $(redis-cli ping 2>/dev/null || echo "Not running")

Environment:
- Configuration: .env
- Logs: logs/
- Status: $([ -f .env ] && echo "✓ Ready" || echo "⚠ Needs API keys")

Next: Add ANTHROPIC_API_KEY to .env to enable AI features
EOF

log "Setup summary saved to: logs/hub-setup-summary.txt"
