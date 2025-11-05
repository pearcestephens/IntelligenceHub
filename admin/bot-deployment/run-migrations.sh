#!/bin/bash

##############################################################################
# Bot Deployment Platform - Migration Runner
# Runs all database migrations in the correct order
##############################################################################

set -e  # Exit on error

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
MIGRATIONS_DIR="${SCRIPT_DIR}/migrations"

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║                                                               ║${NC}"
echo -e "${BLUE}║          Bot Deployment Platform - Migration Runner           ║${NC}"
echo -e "${BLUE}║                                                               ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Load database credentials from .env
if [ -f "${SCRIPT_DIR}/../../../private_html/config/.env" ]; then
    export $(grep -v '^#' "${SCRIPT_DIR}/../../../private_html/config/.env" | xargs)
    echo -e "${GREEN}✓${NC} Loaded database credentials from .env"
elif [ -f "${SCRIPT_DIR}/.env" ]; then
    export $(grep -v '^#' "${SCRIPT_DIR}/.env" | xargs)
    echo -e "${GREEN}✓${NC} Loaded database credentials from local .env"
else
    echo -e "${RED}✗${NC} No .env file found!"
    echo "  Please create .env with DB_HOST, DB_NAME, DB_USER, DB_PASSWORD"
    exit 1
fi

# Check MySQL connection
echo ""
echo -e "${BLUE}→${NC} Testing database connection..."
if mysql -h"${DB_HOST:-localhost}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "SELECT 1" &>/dev/null; then
    echo -e "${GREEN}✓${NC} Database connection successful"
else
    echo -e "${RED}✗${NC} Database connection failed!"
    echo "  Host: ${DB_HOST:-localhost}"
    echo "  Database: ${DB_NAME}"
    echo "  User: ${DB_USER}"
    exit 1
fi

# Migration files in order
MIGRATIONS=(
    "001_base_schema.sql"
    "003_templates_and_notifications.sql"
    "004_full_upgrade_features.sql"
)

echo ""
echo -e "${BLUE}→${NC} Running migrations..."
echo ""

# Run each migration
for migration in "${MIGRATIONS[@]}"; do
    migration_file="${MIGRATIONS_DIR}/${migration}"

    if [ ! -f "$migration_file" ]; then
        echo -e "${YELLOW}⚠${NC}  ${migration} - NOT FOUND (skipping)"
        continue
    fi

    echo -e "${BLUE}→${NC} Running ${migration}..."

    if mysql -h"${DB_HOST:-localhost}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" < "$migration_file" 2>&1 | grep -v "Warning: Using a password"; then
        echo -e "${GREEN}✓${NC} ${migration} - SUCCESS"
    else
        echo -e "${RED}✗${NC} ${migration} - FAILED"
        echo "  Check the migration file for errors"
        exit 1
    fi

    echo ""
done

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                                                               ║${NC}"
echo -e "${GREEN}║                  ✓ ALL MIGRATIONS COMPLETE                    ║${NC}"
echo -e "${GREEN}║                                                               ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Show table count
echo -e "${BLUE}→${NC} Database summary:"
TABLE_COUNT=$(mysql -h"${DB_HOST:-localhost}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}'" 2>/dev/null)
echo -e "  Total tables: ${GREEN}${TABLE_COUNT}${NC}"

# Show bot-related tables
echo ""
echo -e "${BLUE}→${NC} Bot Deployment tables created:"
mysql -h"${DB_HOST:-localhost}" -u"${DB_USER}" -p"${DB_PASSWORD}" "${DB_NAME}" -e "
    SHOW TABLES LIKE 'bot%';
    SHOW TABLES LIKE 'mcp%';
    SHOW TABLES LIKE 'sandbox%';
    SHOW TABLES LIKE 'api%';
    SHOW TABLES LIKE 'audit%';
    SHOW TABLES LIKE 'security%';
    SHOW TABLES LIKE 'notification%';
    SHOW TABLES LIKE 'schedule%';
    SHOW TABLES LIKE 'execution%';
" 2>/dev/null | grep -v "Tables_in" | sed 's/^/  • /'

echo ""
echo -e "${GREEN}✓${NC} Platform is ready for use!"
echo ""
echo "Next steps:"
echo "  1. Start WebSocket server: ./websocket-server.php start"
echo "  2. Initialize templates: php -r 'require \"vendor/autoload.php\"; (new \\BotDeployment\\Services\\TemplateService())->initializeBuiltInTemplates();'"
echo "  3. Configure .env with notification credentials"
echo "  4. Set up cron jobs for automated tasks"
echo ""
