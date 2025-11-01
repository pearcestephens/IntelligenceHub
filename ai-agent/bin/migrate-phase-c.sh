#!/bin/bash

# 🗄️ Quick Migration Runner for Phase C (Analytics & Memory)
# Runs migration: 003_analytics_and_memory.sql

echo ""
echo "═══════════════════════════════════════════════════════════════════"
echo "  🗄️  PHASE C MIGRATION: Analytics & Memory Tables"
echo "═══════════════════════════════════════════════════════════════════"
echo ""

cd "$(dirname "$0")/.."

# Database credentials
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
DB_NAME="jcepnzzkmj"
DB_HOST="127.0.0.1"

MIGRATION_FILE="migrations/003_analytics_and_memory.sql"

if [ ! -f "$MIGRATION_FILE" ]; then
    echo "❌ Migration file not found: $MIGRATION_FILE"
    exit 1
fi

echo "📄 Migration: $MIGRATION_FILE"
echo "🗄️  Database: $DB_NAME@$DB_HOST"
echo ""

# Run migration
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$MIGRATION_FILE" 2>&1

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migration completed!"
    echo ""
    echo "Verifying new tables..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
        SELECT TABLE_NAME, TABLE_ROWS 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '$DB_NAME' 
        AND TABLE_NAME IN (
            'importance_scores',
            'metrics_response_times',
            'metrics_tool_execution',
            'conversation_clusters',
            'compressed_messages_archive'
        );
    "
    echo ""
    echo "🎉 Database ready!"
    echo ""
else
    echo "❌ Migration failed - check errors above"
    exit 1
fi
