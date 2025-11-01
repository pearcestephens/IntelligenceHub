#!/bin/bash
# ========================================================================
# AI Agent Table Rename - Complete Deployment Script
# ========================================================================
# Purpose: Rename all core AI agent tables with full backup & code updates
# Date: 2025-10-18
# Author: AI System Architect
# ========================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Database credentials
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
DB_NAME="jcepnzzkmj"

# Paths
AI_AGENT_DIR="/home/master/applications/jcepnzzkmj/public_html/ai-agent"
BACKUP_DIR="$AI_AGENT_DIR/database/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo -e "${BLUE}========================================================================${NC}"
echo -e "${BLUE}AI AGENT TABLE RENAME - DEPLOYMENT SCRIPT${NC}"
echo -e "${BLUE}========================================================================${NC}"
echo ""

# ========================================================================
# STEP 1: PRE-FLIGHT CHECKS
# ========================================================================
echo -e "${YELLOW}[STEP 1] Pre-flight checks...${NC}"

# Check if backup directory exists
mkdir -p "$BACKUP_DIR"
echo -e "  ${GREEN}✓${NC} Backup directory ready: $BACKUP_DIR"

# Check database connection
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1;" > /dev/null 2>&1; then
    echo -e "  ${GREEN}✓${NC} Database connection successful"
else
    echo -e "  ${RED}✗${NC} Database connection failed!"
    exit 1
fi

# Check current table counts
echo -e "\n${YELLOW}Current table data:${NC}"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT 
    TABLE_NAME as 'Table',
    TABLE_ROWS as 'Rows',
    ROUND(DATA_LENGTH/1024,2) as 'Size_KB'
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='$DB_NAME' 
  AND TABLE_NAME IN (
    'conversations', 'messages', 'context_cards', 'tool_calls',
    'importance_scores', 'conversation_clusters', 'conversation_tags',
    'performance_tests', 'security_scans'
  )
ORDER BY TABLE_NAME;
"

read -p "Continue with rename? (yes/no): " CONFIRM
if [ "$CONFIRM" != "yes" ]; then
    echo -e "${RED}Aborted by user${NC}"
    exit 0
fi

# ========================================================================
# STEP 2: CREATE COMPREHENSIVE BACKUP
# ========================================================================
echo -e "\n${YELLOW}[STEP 2] Creating comprehensive backup...${NC}"

BACKUP_FILE="$BACKUP_DIR/pre_rename_backup_$TIMESTAMP.sql"
echo -e "  Backing up 9 tables to: $BACKUP_FILE"

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" --single-transaction > "$BACKUP_FILE" <<'EOSQL'
-- Backup of core AI agent tables before rename
SET FOREIGN_KEY_CHECKS=0;

-- Core tables
SELECT 'Backing up conversations...' as '';
SHOW CREATE TABLE conversations;
SELECT * FROM conversations;

SELECT 'Backing up messages...' as '';
SHOW CREATE TABLE messages;
SELECT * FROM messages;

SELECT 'Backing up context_cards...' as '';
SHOW CREATE TABLE context_cards;
SELECT * FROM context_cards;

SELECT 'Backing up tool_calls...' as '';
SHOW CREATE TABLE tool_calls;
SELECT * FROM tool_calls;

SELECT 'Backing up importance_scores...' as '';
SHOW CREATE TABLE importance_scores;
SELECT * FROM importance_scores;

SELECT 'Backing up conversation_clusters...' as '';
SHOW CREATE TABLE conversation_clusters;
SELECT * FROM conversation_clusters;

SELECT 'Backing up conversation_tags...' as '';
SHOW CREATE TABLE conversation_tags;
SELECT * FROM conversation_tags;

SELECT 'Backing up performance_tests...' as '';
SHOW CREATE TABLE performance_tests;
SELECT * FROM performance_tests;

SELECT 'Backing up security_scans...' as '';
SHOW CREATE TABLE security_scans;
SELECT * FROM security_scans;

SET FOREIGN_KEY_CHECKS=1;
EOSQL

# Better backup using mysqldump
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    conversations messages context_cards tool_calls \
    importance_scores conversation_clusters conversation_tags \
    performance_tests security_scans \
    > "$BACKUP_FILE"

if [ -f "$BACKUP_FILE" ]; then
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo -e "  ${GREEN}✓${NC} Backup created: $BACKUP_SIZE"
else
    echo -e "  ${RED}✗${NC} Backup failed!"
    exit 1
fi

# ========================================================================
# STEP 3: RENAME TABLES IN DATABASE
# ========================================================================
echo -e "\n${YELLOW}[STEP 3] Renaming tables in database...${NC}"

TABLES=(
    "conversations:ai_agent_conversations"
    "messages:ai_agent_messages"
    "context_cards:ai_agent_context_cards"
    "tool_calls:ai_agent_tool_calls"
    "importance_scores:ai_agent_importance_scores"
    "conversation_clusters:ai_agent_conversation_clusters"
    "conversation_tags:ai_agent_conversation_tags"
    "performance_tests:ai_agent_performance_tests"
    "security_scans:ai_agent_security_scans"
)

for TABLE_PAIR in "${TABLES[@]}"; do
    OLD_NAME="${TABLE_PAIR%:*}"
    NEW_NAME="${TABLE_PAIR#*:}"
    
    echo -n "  Renaming $OLD_NAME → $NEW_NAME ... "
    
    if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "RENAME TABLE \`$OLD_NAME\` TO \`$NEW_NAME\`;" 2>&1; then
        echo -e "${GREEN}✓${NC}"
    else
        echo -e "${RED}✗${NC}"
        echo -e "${RED}ERROR: Failed to rename $OLD_NAME${NC}"
        exit 1
    fi
done

# ========================================================================
# STEP 4: VERIFY RENAMED TABLES
# ========================================================================
echo -e "\n${YELLOW}[STEP 4] Verifying renamed tables...${NC}"

echo -e "\nNew ai_agent_* tables:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT 
    TABLE_NAME as 'New Table Name',
    TABLE_ROWS as 'Rows',
    ROUND(DATA_LENGTH/1024,2) as 'Size_KB',
    ENGINE
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='$DB_NAME' 
  AND TABLE_NAME LIKE 'ai_agent_%'
ORDER BY TABLE_NAME;
"

# Count verification
EXPECTED_COUNT=9
ACTUAL_COUNT=$(mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
SELECT COUNT(*) 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='$DB_NAME' 
  AND TABLE_NAME LIKE 'ai_agent_%';
")

echo ""
if [ "$ACTUAL_COUNT" -eq "$EXPECTED_COUNT" ]; then
    echo -e "${GREEN}✓${NC} All $EXPECTED_COUNT tables renamed successfully!"
else
    echo -e "${RED}✗${NC} Expected $EXPECTED_COUNT tables, found $ACTUAL_COUNT"
    exit 1
fi

# ========================================================================
# STEP 5: UPDATE CODE REFERENCES
# ========================================================================
echo -e "\n${YELLOW}[STEP 5] Updating code references...${NC}"

# Create code backup
CODE_BACKUP_DIR="$BACKUP_DIR/code_backup_$TIMESTAMP"
mkdir -p "$CODE_BACKUP_DIR"

# Find and backup all PHP files that reference the old table names
echo "  Finding files with old table references..."
grep -rl "FROM conversations\|INTO conversations\|FROM messages\|INTO messages\|FROM context_cards\|FROM tool_calls" \
    "$AI_AGENT_DIR" --include="*.php" 2>/dev/null | while read FILE; do
    
    # Create backup
    BACKUP_PATH="$CODE_BACKUP_DIR/$(basename $FILE).backup"
    cp "$FILE" "$BACKUP_PATH"
    echo -e "    ${GREEN}✓${NC} Backed up: $(basename $FILE)"
done

echo ""
echo "  Updating table references in code..."

# Update table references
find "$AI_AGENT_DIR" -name "*.php" -type f -exec sed -i.bak_rename \
    -e 's/FROM conversations\b/FROM ai_agent_conversations/g' \
    -e 's/INTO conversations\b/INTO ai_agent_conversations/g' \
    -e 's/UPDATE conversations\b/UPDATE ai_agent_conversations/g' \
    -e 's/FROM messages\b/FROM ai_agent_messages/g' \
    -e 's/INTO messages\b/INTO ai_agent_messages/g' \
    -e 's/UPDATE messages\b/UPDATE ai_agent_messages/g' \
    -e 's/FROM context_cards\b/FROM ai_agent_context_cards/g' \
    -e 's/INTO context_cards\b/INTO ai_agent_context_cards/g' \
    -e 's/FROM tool_calls\b/FROM ai_agent_tool_calls/g' \
    -e 's/INTO tool_calls\b/INTO ai_agent_tool_calls/g' \
    -e 's/FROM importance_scores\b/FROM ai_agent_importance_scores/g' \
    -e 's/FROM conversation_clusters\b/FROM ai_agent_conversation_clusters/g' \
    -e 's/FROM conversation_tags\b/FROM ai_agent_conversation_tags/g' \
    -e 's/FROM performance_tests\b/FROM ai_agent_performance_tests/g' \
    -e 's/FROM security_scans\b/FROM ai_agent_security_scans/g' \
    {} \;

# Clean up sed backup files
find "$AI_AGENT_DIR" -name "*.bak_rename" -delete

echo -e "  ${GREEN}✓${NC} Code references updated"

# ========================================================================
# STEP 6: GENERATE ROLLBACK SCRIPT
# ========================================================================
echo -e "\n${YELLOW}[STEP 6] Generating rollback script...${NC}"

ROLLBACK_FILE="$BACKUP_DIR/rollback_$TIMESTAMP.sql"

cat > "$ROLLBACK_FILE" <<'EOF'
-- ========================================================================
-- ROLLBACK SCRIPT - Restore original table names
-- ========================================================================
-- Generated: TIMESTAMP_PLACEHOLDER
-- Use this if you need to revert the rename operation
-- ========================================================================

-- Rename tables back to original names
RENAME TABLE ai_agent_conversations TO conversations;
RENAME TABLE ai_agent_messages TO messages;
RENAME TABLE ai_agent_context_cards TO context_cards;
RENAME TABLE ai_agent_tool_calls TO tool_calls;
RENAME TABLE ai_agent_importance_scores TO importance_scores;
RENAME TABLE ai_agent_conversation_clusters TO conversation_clusters;
RENAME TABLE ai_agent_conversation_tags TO conversation_tags;
RENAME TABLE ai_agent_performance_tests TO performance_tests;
RENAME TABLE ai_agent_security_scans TO security_scans;

-- Verification
SELECT 'Rollback complete. Original tables restored.' as Status;
SELECT TABLE_NAME, TABLE_ROWS 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA='jcepnzzkmj' 
  AND TABLE_NAME IN (
    'conversations', 'messages', 'context_cards', 'tool_calls',
    'importance_scores', 'conversation_clusters', 'conversation_tags',
    'performance_tests', 'security_scans'
  )
ORDER BY TABLE_NAME;
EOF

sed -i "s/TIMESTAMP_PLACEHOLDER/$TIMESTAMP/g" "$ROLLBACK_FILE"
echo -e "  ${GREEN}✓${NC} Rollback script created: $ROLLBACK_FILE"

# ========================================================================
# STEP 7: FINAL SUMMARY
# ========================================================================
echo -e "\n${BLUE}========================================================================${NC}"
echo -e "${GREEN}RENAME OPERATION COMPLETE!${NC}"
echo -e "${BLUE}========================================================================${NC}"
echo ""
echo -e "${GREEN}✓${NC} 9 tables renamed successfully"
echo -e "${GREEN}✓${NC} Code references updated"
echo -e "${GREEN}✓${NC} Backup created: $BACKUP_FILE"
echo -e "${GREEN}✓${NC} Code backup: $CODE_BACKUP_DIR"
echo -e "${GREEN}✓${NC} Rollback script: $ROLLBACK_FILE"
echo ""
echo -e "${YELLOW}NAMING CONVENTION SUMMARY:${NC}"
echo -e "  Core tables:      ${GREEN}ai_agent_*${NC} (9 tables)"
echo -e "  Extensions:       ${GREEN}ai_agent_ext_*${NC} (for future use)"
echo -e "  Knowledge Base:   ${GREEN}ai_cis_kb_*${NC} (6 tables)"
echo -e "  Legacy KB:        ${GREEN}ai_kb_*${NC} (7 tables, unchanged)"
echo ""
echo -e "${YELLOW}NEXT STEPS:${NC}"
echo "  1. Test the AI agent system"
echo "  2. Update any external integrations"
echo "  3. Update documentation"
echo "  4. If issues occur, run: mysql < $ROLLBACK_FILE"
echo ""
echo -e "${BLUE}========================================================================${NC}"
