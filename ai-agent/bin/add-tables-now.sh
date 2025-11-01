#!/bin/bash
# ================================================================
# INSTANT TABLE CREATION - Phase C Migration
# Created: 2025-10-10
# Purpose: Add all 9 analytics & memory tables immediately
# ================================================================

set -e

DB_HOST="127.0.0.1"
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
DB_NAME="jcepnzzkmj"

echo "════════════════════════════════════════════════════════════════"
echo "  🚀 ADDING PHASE C TABLES NOW"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Database: ${DB_NAME}@${DB_HOST}"
echo "Tables to create: 9 + 1 column"
echo ""

# Run the fixed migration
echo "📦 Executing migration SQL..."
mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < migrations/003_analytics_and_memory_fixed.sql

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ MIGRATION COMPLETED SUCCESSFULLY!"
    echo ""
    echo "═══════════════════════════════════════════════════════════════"
    echo "  📊 VERIFYING TABLES"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""
    
    mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
        SELECT 
            TABLE_NAME as 'Table', 
            TABLE_ROWS as 'Rows',
            ROUND(DATA_LENGTH/1024, 2) as 'Size_KB',
            CREATE_TIME as 'Created'
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = '${DB_NAME}' 
        AND TABLE_NAME IN (
            'importance_scores',
            'metrics_response_times',
            'metrics_tool_execution',
            'metrics_token_usage',
            'metrics_cache_performance',
            'metrics_errors',
            'conversation_clusters',
            'conversation_tags',
            'compressed_messages_archive'
        )
        ORDER BY TABLE_NAME;
    "
    
    echo ""
    echo "═══════════════════════════════════════════════════════════════"
    echo "  ✅ ALL 9 TABLES CREATED SUCCESSFULLY!"
    echo "═══════════════════════════════════════════════════════════════"
    echo ""
    echo "Checking conversations.importance_score column..."
    
    mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
        SELECT 
            COLUMN_NAME,
            COLUMN_TYPE,
            IS_NULLABLE,
            COLUMN_DEFAULT
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '${DB_NAME}'
        AND TABLE_NAME = 'conversations'
        AND COLUMN_NAME = 'importance_score';
    "
    
    echo ""
    echo "════════════════════════════════════════════════════════════════"
    echo "  🎉 PHASE C DATABASE MIGRATION COMPLETE!"
    echo "════════════════════════════════════════════════════════════════"
    echo ""
    echo "Next Steps:"
    echo "  1. Run tests:     php bin/run-inline-tests.php"
    echo "  2. Expected:      61/61 tests passing (100%)"
    echo "  3. Full suite:    php bin/run-phase-c-tests.php"
    echo "  4. View dashboard: open public/analytics-dashboard.html"
    echo ""
else
    echo ""
    echo "❌ MIGRATION FAILED!"
    echo ""
    echo "Error details above. Common issues:"
    echo "  - Database credentials incorrect"
    echo "  - Permissions issue"
    echo "  - Table already exists (check manually)"
    echo ""
    echo "Manual fix:"
    echo "  mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj"
    echo "  SHOW TABLES LIKE '%importance%';"
    echo ""
    exit 1
fi
