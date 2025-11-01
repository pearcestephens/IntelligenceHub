#!/bin/bash
################################################################################
# AI-Agent Table Rename Script
# Purpose: Add 'agent_' prefix to all core tables for better clarity
# Safety: Includes backup, validation, and rollback capabilities
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

echo -e "${BLUE}════════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}   AI-Agent Table Rename Script v1.0${NC}"
echo -e "${BLUE}   Adding 'agent_' prefix to core tables${NC}"
echo -e "${BLUE}════════════════════════════════════════════════════════════${NC}\n"

################################################################################
# CONFIGURATION
################################################################################

# Load .env file
if [ -f "$PROJECT_ROOT/.env" ]; then
    source "$PROJECT_ROOT/.env"
else
    echo -e "${RED}✗ Error: .env file not found${NC}"
    exit 1
fi

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-hdgwrzntwa}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD}"

BACKUP_DIR="$PROJECT_ROOT/backups/table-rename-$(date +%Y%m%d-%H%M%S)"
TIMESTAMP=$(date +%Y-%m-%d\ %H:%M:%S)

# Table mappings (old_name => new_name)
declare -A TABLE_MAP=(
    ["conversations"]="agent_conversations"
    ["messages"]="agent_messages"
    ["tool_calls"]="agent_tool_calls"
    ["kb_docs"]="agent_kb_docs"
    ["kb_chunks"]="agent_kb_chunks"
    ["importance_scores"]="agent_importance_scores"
    ["metrics_response_times"]="agent_metrics_response_times"
    ["metrics_tool_execution"]="agent_metrics_tool_execution"
    ["metrics_token_usage"]="agent_metrics_token_usage"
    ["metrics_cache_performance"]="agent_metrics_cache_performance"
    ["metrics_errors"]="agent_metrics_errors"
    ["conversation_clusters"]="agent_conversation_clusters"
    ["conversation_tags"]="agent_conversation_tags"
    ["compressed_messages_archive"]="agent_compressed_messages_archive"
    ["messages_backup"]="agent_messages_backup"
    ["api_idempotency"]="agent_api_idempotency"
)

################################################################################
# HELPER FUNCTIONS
################################################################################

log_success() { echo -e "${GREEN}✓ $1${NC}"; }
log_info() { echo -e "${BLUE}ℹ $1${NC}"; }
log_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
log_error() { echo -e "${RED}✗ $1${NC}"; }

# Test database connection
test_db_connection() {
    log_info "Testing database connection..."

    if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" &>/dev/null; then
        log_success "Database connection successful"
        return 0
    else
        log_error "Database connection failed"
        return 1
    fi
}

# Check if table exists
table_exists() {
    local table_name=$1
    local result=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME' AND table_name='$table_name'" 2>/dev/null)
    [ "$result" = "1" ]
}

# Get table row count
get_row_count() {
    local table_name=$1
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -N -e "SELECT COUNT(*) FROM \`$table_name\`" 2>/dev/null || echo "0"
}

################################################################################
# PHASE 1: PRE-FLIGHT CHECKS
################################################################################

echo -e "\n${BLUE}[PHASE 1] Pre-Flight Checks${NC}"
echo "─────────────────────────────────────────────────────────────"

# Check database connection
if ! test_db_connection; then
    log_error "Cannot proceed without database connection"
    exit 1
fi

# Check which tables exist
echo ""
log_info "Checking existing tables..."
TABLES_TO_RENAME=()
TABLES_NOT_FOUND=()

for old_table in "${!TABLE_MAP[@]}"; do
    if table_exists "$old_table"; then
        row_count=$(get_row_count "$old_table")
        log_success "Found: $old_table ($row_count rows)"
        TABLES_TO_RENAME+=("$old_table")
    else
        log_warning "Not found: $old_table (skipping)"
        TABLES_NOT_FOUND+=("$old_table")
    fi
done

if [ ${#TABLES_TO_RENAME[@]} -eq 0 ]; then
    log_error "No tables found to rename!"
    exit 1
fi

# Check if new tables already exist (conflict detection)
echo ""
log_info "Checking for naming conflicts..."
CONFLICTS=()

for old_table in "${TABLES_TO_RENAME[@]}"; do
    new_table="${TABLE_MAP[$old_table]}"
    if table_exists "$new_table"; then
        log_warning "Conflict: $new_table already exists!"
        CONFLICTS+=("$new_table")
    fi
done

if [ ${#CONFLICTS[@]} -gt 0 ]; then
    log_error "Cannot proceed: ${#CONFLICTS[@]} naming conflict(s) detected"
    log_error "Please manually resolve conflicts or drop existing tables"
    exit 1
fi

log_success "Pre-flight checks passed"

################################################################################
# PHASE 2: BACKUP DATABASE
################################################################################

echo -e "\n${BLUE}[PHASE 2] Creating Backup${NC}"
echo "─────────────────────────────────────────────────────────────"

mkdir -p "$BACKUP_DIR"
log_info "Backup directory: $BACKUP_DIR"

# Backup entire database
log_info "Backing up database: $DB_NAME"
BACKUP_FILE="$BACKUP_DIR/database-backup.sql"

mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" \
    --single-transaction --routines --triggers \
    "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null

if [ $? -eq 0 ] && [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_success "Database backed up: $BACKUP_SIZE"
else
    log_error "Backup failed! Aborting."
    exit 1
fi

# Create table mapping file for rollback
MAPPING_FILE="$BACKUP_DIR/table-mappings.json"
cat > "$MAPPING_FILE" << EOF
{
    "timestamp": "$TIMESTAMP",
    "database": "$DB_NAME",
    "tables": {
EOF

first=true
for old_table in "${TABLES_TO_RENAME[@]}"; do
    new_table="${TABLE_MAP[$old_table]}"
    if [ "$first" = true ]; then
        first=false
    else
        echo "," >> "$MAPPING_FILE"
    fi
    echo -n "        \"$old_table\": \"$new_table\"" >> "$MAPPING_FILE"
done

cat >> "$MAPPING_FILE" << EOF

    }
}
EOF

log_success "Mapping file created: table-mappings.json"

################################################################################
# PHASE 3: RENAME TABLES IN DATABASE
################################################################################

echo -e "\n${BLUE}[PHASE 3] Renaming Database Tables${NC}"
echo "─────────────────────────────────────────────────────────────"

RENAMED_TABLES=()
FAILED_RENAMES=()

for old_table in "${TABLES_TO_RENAME[@]}"; do
    new_table="${TABLE_MAP[$old_table]}"

    log_info "Renaming: $old_table → $new_table"

    if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -e "RENAME TABLE \`$old_table\` TO \`$new_table\`" 2>/dev/null; then

        log_success "  ✓ Renamed successfully"
        RENAMED_TABLES+=("$old_table:$new_table")
    else
        log_error "  ✗ Rename failed!"
        FAILED_RENAMES+=("$old_table")
    fi
done

if [ ${#FAILED_RENAMES[@]} -gt 0 ]; then
    log_error "${#FAILED_RENAMES[@]} table(s) failed to rename"
    log_warning "Attempting rollback..."

    # Rollback renamed tables
    for mapping in "${RENAMED_TABLES[@]}"; do
        old_table="${mapping%%:*}"
        new_table="${mapping##*:}"
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" \
            -e "RENAME TABLE \`$new_table\` TO \`$old_table\`" 2>/dev/null
    done

    log_error "Rollback complete. Please check errors and try again."
    exit 1
fi

log_success "${#RENAMED_TABLES[@]} tables renamed successfully"

################################################################################
# PHASE 4: UPDATE PHP FILES
################################################################################

echo -e "\n${BLUE}[PHASE 4] Updating PHP Files${NC}"
echo "─────────────────────────────────────────────────────────────"

# Backup PHP files before modification
log_info "Backing up PHP files..."
cp -r "$PROJECT_ROOT/src" "$BACKUP_DIR/src_backup" 2>/dev/null || true
cp -r "$PROJECT_ROOT/public" "$BACKUP_DIR/public_backup" 2>/dev/null || true
cp -r "$PROJECT_ROOT/api" "$BACKUP_DIR/api_backup" 2>/dev/null || true
cp -r "$PROJECT_ROOT/tests" "$BACKUP_DIR/tests_backup" 2>/dev/null || true
log_success "PHP files backed up"

# Function to update files
update_php_files() {
    local old_name=$1
    local new_name=$2
    local file_count=0

    log_info "Updating references: $old_name → $new_name"

    # Find and replace in PHP files
    find "$PROJECT_ROOT" -type f -name "*.php" ! -path "*/vendor/*" ! -path "*/node_modules/*" ! -path "*/backups/*" | while read file; do
        # Use case-sensitive replacement for exact matches
        if grep -q "\b$old_name\b" "$file" 2>/dev/null; then
            # Create patterns that preserve SQL syntax
            perl -i -pe "s/\bFROM $old_name\b/FROM $new_name/g" "$file"
            perl -i -pe "s/\bINTO $old_name\b/INTO $new_name/g" "$file"
            perl -i -pe "s/\bUPDATE $old_name\b/UPDATE $new_name/g" "$file"
            perl -i -pe "s/\bJOIN $old_name\b/JOIN $new_name/g" "$file"
            perl -i -pe "s/\bTABLE $old_name\b/TABLE $new_name/g" "$file"
            perl -i -pe "s/'$old_name'/'$new_name'/g" "$file"
            perl -i -pe "s/\"$old_name\"/\"$new_name\"/g" "$file"
            perl -i -pe "s/\`$old_name\`/\`$new_name\`/g" "$file"

            ((file_count++))
        fi
    done

    echo "    Updated $file_count PHP files"
}

# Update each table reference
for old_table in "${TABLES_TO_RENAME[@]}"; do
    new_table="${TABLE_MAP[$old_table]}"
    update_php_files "$old_table" "$new_table"
done

log_success "PHP files updated"

################################################################################
# PHASE 5: UPDATE SQL FILES
################################################################################

echo -e "\n${BLUE}[PHASE 5] Updating SQL Schema Files${NC}"
echo "─────────────────────────────────────────────────────────────"

SQL_FILE_COUNT=0

find "$PROJECT_ROOT" -type f -name "*.sql" ! -path "*/backups/*" | while read file; do
    modified=false

    for old_table in "${TABLES_TO_RENAME[@]}"; do
        new_table="${TABLE_MAP[$old_table]}"

        if grep -q "\b$old_table\b" "$file" 2>/dev/null; then
            perl -i -pe "s/\bCREATE TABLE IF NOT EXISTS $old_name\b/CREATE TABLE IF NOT EXISTS $new_name/g" "$file"
            perl -i -pe "s/\bCREATE TABLE $old_name\b/CREATE TABLE $new_name/g" "$file"
            perl -i -pe "s/\bFROM $old_name\b/FROM $new_name/g" "$file"
            perl -i -pe "s/\bINTO $old_name\b/INTO $new_name/g" "$file"
            perl -i -pe "s/\bUPDATE $old_name\b/UPDATE $new_name/g" "$file"
            perl -i -pe "s/\bTABLE $old_name\b/TABLE $new_name/g" "$file"
            modified=true
        fi
    done

    if [ "$modified" = true ]; then
        ((SQL_FILE_COUNT++))
        echo "  Updated: $(basename $file)"
    fi
done

log_success "Updated $SQL_FILE_COUNT SQL files"

################################################################################
# PHASE 6: VALIDATION
################################################################################

echo -e "\n${BLUE}[PHASE 6] Validation${NC}"
echo "─────────────────────────────────────────────────────────────"

# Verify all new tables exist
log_info "Verifying renamed tables..."
ALL_GOOD=true

for old_table in "${TABLES_TO_RENAME[@]}"; do
    new_table="${TABLE_MAP[$old_table]}"

    if table_exists "$new_table"; then
        old_count=$(cat "$BACKUP_FILE" | grep -c "INSERT INTO \`$old_table\`" || echo "0")
        new_count=$(get_row_count "$new_table")
        log_success "  ✓ $new_table exists ($new_count rows)"
    else
        log_error "  ✗ $new_table NOT FOUND!"
        ALL_GOOD=false
    fi
done

# PHP syntax check
log_info "Checking PHP syntax..."
PHP_ERRORS=0

find "$PROJECT_ROOT/src" "$PROJECT_ROOT/public" "$PROJECT_ROOT/api" -type f -name "*.php" 2>/dev/null | while read file; do
    if ! php -l "$file" &>/dev/null; then
        log_error "  Syntax error in: $file"
        ((PHP_ERRORS++))
    fi
done

if [ $PHP_ERRORS -eq 0 ]; then
    log_success "  All PHP files valid"
else
    log_error "  $PHP_ERRORS PHP syntax errors found"
    ALL_GOOD=false
fi

################################################################################
# PHASE 7: COMPLETION
################################################################################

echo -e "\n${BLUE}[PHASE 7] Completion${NC}"
echo "─────────────────────────────────────────────────────────────"

if [ "$ALL_GOOD" = true ]; then
    log_success "TABLE RENAME COMPLETE! 🎉"

    echo -e "\n${GREEN}Summary:${NC}"
    echo "  • Tables renamed: ${#RENAMED_TABLES[@]}"
    echo "  • PHP files updated: Auto-detected"
    echo "  • SQL files updated: $SQL_FILE_COUNT"
    echo "  • Backup location: $BACKUP_DIR"

    echo -e "\n${YELLOW}IMPORTANT NEXT STEPS:${NC}"
    echo "  1. Test your application thoroughly"
    echo "  2. Run: cd $PROJECT_ROOT && ./bin/api-test-suite.sh"
    echo "  3. Check logs for any table name errors"
    echo "  4. If everything works, backup can be deleted in 7 days"

    echo -e "\n${BLUE}Rollback Command (if needed):${NC}"
    echo "  mysql -u$DB_USER -p $DB_NAME < $BACKUP_FILE"

else
    log_error "VALIDATION FAILED - Please review errors above"
    log_warning "Backup preserved at: $BACKUP_DIR"
    log_warning "Database changes applied but PHP files may have issues"
    exit 1
fi

echo -e "\n${BLUE}════════════════════════════════════════════════════════════${NC}\n"
