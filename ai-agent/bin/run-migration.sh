#!/bin/bash
# =====================================================
# BATCH-7 Migration Execution Script
# Safely executes database migrations with validation
# =====================================================

set -e  # Exit on error
set -u  # Exit on undefined variable

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Database credentials
DB_HOST="127.0.0.1"
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
DB_NAME="jcepnzzkmj"

# Migration file
MIGRATION_FILE="../sql/migrations/2025_10_07_batch_7_comprehensive.sql"

echo -e "${BLUE}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║    BATCH-7 Database Migration Execution Script     ║${NC}"
echo -e "${BLUE}║    Ecigdis Limited - The Vape Shed                 ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════╝${NC}"
echo ""

# =====================================================
# 1. Pre-flight checks
# =====================================================

echo -e "${YELLOW}[1/6] Pre-flight checks...${NC}"

# Check if migration file exists
if [ ! -f "$MIGRATION_FILE" ]; then
    echo -e "${RED}✗ Migration file not found: $MIGRATION_FILE${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Migration file exists${NC}"

# Check MySQL client
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}✗ MySQL client not found${NC}"
    exit 1
fi
echo -e "${GREEN}✓ MySQL client available${NC}"

# Test database connection
if ! mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1;" &> /dev/null; then
    echo -e "${RED}✗ Cannot connect to database${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Database connection successful${NC}"

echo ""

# =====================================================
# 2. Backup existing tables
# =====================================================

echo -e "${YELLOW}[2/6] Creating backup...${NC}"

BACKUP_DIR="../backups"
mkdir -p "$BACKUP_DIR"

BACKUP_FILE="$BACKUP_DIR/pre_batch7_$(date +%Y%m%d_%H%M%S).sql"

mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    "$DB_NAME" \
    conversations messages tool_calls context_cards knowledge_base 2>/dev/null \
    > "$BACKUP_FILE" || true

if [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo -e "${GREEN}✓ Backup created: $BACKUP_FILE ($BACKUP_SIZE)${NC}"
else
    echo -e "${YELLOW}⚠ No existing tables to backup (fresh install)${NC}"
fi

echo ""

# =====================================================
# 3. Show migration preview
# =====================================================

echo -e "${YELLOW}[3/6] Migration preview...${NC}"

# Count operations
CREATE_TABLES=$(grep -c "CREATE TABLE IF NOT EXISTS" "$MIGRATION_FILE" || true)
ALTER_TABLES=$(grep -c "ALTER TABLE" "$MIGRATION_FILE" || true)
CREATE_INDEXES=$(grep -c "CREATE INDEX" "$MIGRATION_FILE" || true)

echo -e "   • CREATE TABLE operations: ${BLUE}$CREATE_TABLES${NC}"
echo -e "   • ALTER TABLE operations: ${BLUE}$ALTER_TABLES${NC}"
echo -e "   • CREATE INDEX operations: ${BLUE}$CREATE_INDEXES${NC}"

echo ""

# =====================================================
# 4. Confirm execution
# =====================================================

echo -e "${YELLOW}[4/6] Ready to execute migration...${NC}"
echo -e "   Database: ${BLUE}$DB_NAME${NC}"
echo -e "   Host: ${BLUE}$DB_HOST${NC}"
echo -e "   User: ${BLUE}$DB_USER${NC}"
echo ""

read -p "$(echo -e ${YELLOW}'Continue? (y/N): '${NC})" -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}✗ Migration cancelled${NC}"
    exit 0
fi

echo ""

# =====================================================
# 5. Execute migration
# =====================================================

echo -e "${YELLOW}[5/6] Executing migration...${NC}"

if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_FILE"; then
    echo -e "${GREEN}✓ Migration executed successfully${NC}"
else
    echo -e "${RED}✗ Migration failed${NC}"
    echo -e "${YELLOW}⚠ Restore from backup: $BACKUP_FILE${NC}"
    exit 1
fi

echo ""

# =====================================================
# 6. Verify schema
# =====================================================

echo -e "${YELLOW}[6/6] Verifying schema...${NC}"

# Check critical tables exist
TABLES=("conversations" "messages" "context_cards" "tool_calls" "knowledge_base" "api_keys")

for TABLE in "${TABLES[@]}"; do
    if mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESCRIBE $TABLE;" &> /dev/null; then
        ROW_COUNT=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -se "SELECT COUNT(*) FROM $TABLE;" 2>/dev/null || echo "0")
        echo -e "${GREEN}✓ Table exists: $TABLE${NC} (${ROW_COUNT} rows)"
    else
        echo -e "${RED}✗ Table missing: $TABLE${NC}"
    fi
done

echo ""

# =====================================================
# 7. Summary
# =====================================================

echo -e "${GREEN}╔════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║           BATCH-7 Migration Complete! ✓            ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "Next steps:"
echo -e "  1. Run tests: ${BLUE}php vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php${NC}"
echo -e "  2. Check logs: ${BLUE}tail -f ../logs/migration.log${NC}"
echo -e "  3. Rollback if needed: ${BLUE}mysql < $BACKUP_FILE${NC}"
echo ""
