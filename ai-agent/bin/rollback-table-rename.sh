#!/bin/bash
################################################################################
# AI-Agent Table Rename Rollback Script
# Purpose: Restore original table names if something goes wrong
# Usage: ./rollback-table-rename.sh [backup-directory]
################################################################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo -e "${YELLOW}════════════════════════════════════════════════════════════${NC}"
echo -e "${YELLOW}   AI-Agent Table Rename ROLLBACK${NC}"
echo -e "${YELLOW}   Restoring original table names${NC}"
echo -e "${YELLOW}════════════════════════════════════════════════════════════${NC}\n"

# Find most recent backup
if [ -z "$1" ]; then
    BACKUP_DIR=$(ls -td "$PROJECT_ROOT/backups/table-rename-"* 2>/dev/null | head -1)
    if [ -z "$BACKUP_DIR" ]; then
        echo -e "${RED}✗ No backup directory found${NC}"
        echo "Usage: $0 [backup-directory]"
        exit 1
    fi
    echo -e "${BLUE}Using most recent backup: $BACKUP_DIR${NC}\n"
else
    BACKUP_DIR="$1"
fi

# Verify backup exists
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}✗ Backup directory not found: $BACKUP_DIR${NC}"
    exit 1
fi

BACKUP_FILE="$BACKUP_DIR/database-backup.sql"
MAPPING_FILE="$BACKUP_DIR/table-mappings.json"

if [ ! -f "$BACKUP_FILE" ]; then
    echo -e "${RED}✗ Backup file not found: $BACKUP_FILE${NC}"
    exit 1
fi

# Load database credentials
if [ -f "$PROJECT_ROOT/.env" ]; then
    source "$PROJECT_ROOT/.env"
else
    echo -e "${RED}✗ .env file not found${NC}"
    exit 1
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-hdgwrzntwa}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD}"

echo -e "${YELLOW}⚠ WARNING: This will restore your database from backup${NC}"
echo -e "${YELLOW}⚠ All changes since the backup will be lost!${NC}\n"
echo "Backup date: $(stat -c %y "$BACKUP_FILE" | cut -d. -f1)"
echo "Backup size: $(du -h "$BACKUP_FILE" | cut -f1)"
echo ""

read -p "Are you sure you want to continue? (yes/no): " -r
echo
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    echo -e "${BLUE}Rollback cancelled${NC}"
    exit 0
fi

echo -e "\n${BLUE}[1/3] Restoring database from backup...${NC}"
mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Database restored successfully${NC}"
else
    echo -e "${RED}✗ Database restore failed${NC}"
    exit 1
fi

echo -e "\n${BLUE}[2/3] Restoring PHP files...${NC}"
if [ -d "$BACKUP_DIR/src_backup" ]; then
    cp -r "$BACKUP_DIR/src_backup/"* "$PROJECT_ROOT/src/"
    echo -e "${GREEN}✓ src/ restored${NC}"
fi

if [ -d "$BACKUP_DIR/public_backup" ]; then
    cp -r "$BACKUP_DIR/public_backup/"* "$PROJECT_ROOT/public/"
    echo -e "${GREEN}✓ public/ restored${NC}"
fi

if [ -d "$BACKUP_DIR/api_backup" ]; then
    cp -r "$BACKUP_DIR/api_backup/"* "$PROJECT_ROOT/api/"
    echo -e "${GREEN}✓ api/ restored${NC}"
fi

if [ -d "$BACKUP_DIR/tests_backup" ]; then
    cp -r "$BACKUP_DIR/tests_backup/"* "$PROJECT_ROOT/tests/"
    echo -e "${GREEN}✓ tests/ restored${NC}"
fi

echo -e "\n${BLUE}[3/3] Verifying restoration...${NC}"
if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" &>/dev/null; then
    echo -e "${GREEN}✓ Database connection verified${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    exit 1
fi

echo -e "\n${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}   ROLLBACK COMPLETE ✓${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}\n"
echo "Your database and PHP files have been restored to their state before the rename."
echo ""
