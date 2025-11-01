#!/bin/bash
# ================================================================
# Environment Setup & Validation Script
# Purpose: Ensure all required .env variables are set correctly
# ================================================================

set -e

PROJECT_ROOT="/home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent"
cd "$PROJECT_ROOT"

echo "════════════════════════════════════════════════════════════════"
echo "  🔧 AI AGENT ENVIRONMENT SETUP"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  No .env file found. Creating from parent directory..."
    
    # Check parent .env
    if [ -f "../../../.env" ]; then
        echo "✓ Found parent .env, copying..."
        cp "../../../.env" .env
    else
        echo "❌ No .env found. Creating from example..."
        if [ -f .env.example ]; then
            cp .env.example .env
        else
            echo "Creating new .env file..."
            cat > .env << 'ENVFILE'
# Database Configuration
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=jcepnzzkmj
MYSQL_PASSWORD=wprKh9Jq63
MYSQL_DATABASE=jcepnzzkmj

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PREFIX=aiagent:
REDIS_PASSWORD=

# OpenAI Configuration
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=2000

# Analytics Configuration
ANALYTICS_IP_SALT=random_salt_change_in_production
NEURO_HMAC_SECRET=

# Application Configuration
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=info
LOG_PATH=logs/ai-agent.log

# Session Configuration
SESSION_LIFETIME=7200
SESSION_SECURE=true

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW=60
ENVFILE
            echo "✅ Created new .env with defaults"
        fi
    fi
fi

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  📋 VALIDATING ENVIRONMENT VARIABLES"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Load .env
export $(grep -v '^#' .env | xargs)

# Required variables
REQUIRED_VARS=(
    "MYSQL_HOST"
    "MYSQL_PORT"
    "MYSQL_USER"
    "MYSQL_PASSWORD"
    "MYSQL_DATABASE"
    "REDIS_HOST"
    "REDIS_PORT"
)

MISSING_VARS=()

for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        echo "❌ Missing: $var"
        MISSING_VARS+=("$var")
    else
        # Mask password
        if [[ "$var" == *"PASSWORD"* ]] || [[ "$var" == *"SECRET"* ]] || [[ "$var" == *"KEY"* ]]; then
            echo "✓ $var = ********"
        else
            echo "✓ $var = ${!var}"
        fi
    fi
done

echo ""

if [ ${#MISSING_VARS[@]} -gt 0 ]; then
    echo "⚠️  WARNING: ${#MISSING_VARS[@]} required variables are missing!"
    echo "Please set them in .env file"
    echo ""
else
    echo "✅ All required variables are set!"
    echo ""
fi

# Test database connection
echo "════════════════════════════════════════════════════════════════"
echo "  🗄️  TESTING DATABASE CONNECTION"
echo "════════════════════════════════════════════════════════════════"
echo ""

if mysql -h "${MYSQL_HOST}" -P "${MYSQL_PORT}" -u "${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -e "SELECT 1" > /dev/null 2>&1; then
    echo "✅ Database connection successful!"
    
    # Count tables
    TABLE_COUNT=$(mysql -h "${MYSQL_HOST}" -u "${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -N -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='${MYSQL_DATABASE}'")
    echo "✓ Found $TABLE_COUNT tables in database"
    
    # Check for AI agent tables
    AI_TABLES=$(mysql -h "${MYSQL_HOST}" -u "${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" -N -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='${MYSQL_DATABASE}' AND TABLE_NAME IN ('importance_scores','metrics_response_times','metrics_tool_execution')")
    echo "✓ AI agent tables: $AI_TABLES/9 created"
else
    echo "❌ Database connection failed!"
    echo "Please check MYSQL_* variables in .env"
fi

echo ""

# Test Redis connection
echo "════════════════════════════════════════════════════════════════"
echo "  📦 TESTING REDIS CONNECTION"
echo "════════════════════════════════════════════════════════════════"
echo ""

if redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" ping > /dev/null 2>&1; then
    echo "✅ Redis connection successful!"
    REDIS_KEYS=$(redis-cli -h "${REDIS_HOST}" -p "${REDIS_PORT}" DBSIZE | awk '{print $2}')
    echo "✓ Redis keys: $REDIS_KEYS"
else
    echo "❌ Redis connection failed!"
    echo "Please check REDIS_* variables in .env"
fi

echo ""

# Verify file permissions
echo "════════════════════════════════════════════════════════════════"
echo "  🔐 CHECKING FILE PERMISSIONS"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Create logs directory if missing
if [ ! -d "logs" ]; then
    mkdir -p logs
    echo "✓ Created logs/ directory"
fi

chmod 755 logs
echo "✓ logs/ directory: 755"

# Ensure .env is not publicly readable
chmod 600 .env
echo "✓ .env file: 600 (owner read/write only)"

# Make scripts executable
chmod +x bin/*.sh 2>/dev/null || true
echo "✓ Shell scripts: executable"

echo ""

# Run inline tests
echo "════════════════════════════════════════════════════════════════"
echo "  🧪 RUNNING INLINE TESTS"
echo "════════════════════════════════════════════════════════════════"
echo ""

php bin/run-inline-tests.php

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  ✅ ENVIRONMENT SETUP COMPLETE!"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Next steps:"
echo "  1. Review .env file and add any missing API keys"
echo "  2. Run: php bin/run-inline-tests.php"
echo "  3. Run: php vendor/bin/phpunit --testdox"
echo "  4. Deploy to production"
echo ""
