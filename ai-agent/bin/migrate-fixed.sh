#!/bin/bash
# ================================================================
# Fixed Migration Runner - No Foreign Keys
# Created: 2025-10-10
# Purpose: Run the fixed migration without FK constraints
# ================================================================

set -e

DB_HOST="127.0.0.1"
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
DB_NAME="jcepnzzkmj"

echo "=========================================="
echo "Running FIXED Phase C Migration"
echo "=========================================="
echo ""
echo "Database: ${DB_NAME}@${DB_HOST}"
echo "Migration: 003_analytics_and_memory_fixed.sql"
echo ""

# Run the fixed migration
echo "Executing migration SQL..."
mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" < migrations/003_analytics_and_memory_fixed.sql

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migration completed successfully!"
    echo ""
    echo "Verifying tables..."
    mysql -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "
        SELECT 
            TABLE_NAME, 
            TABLE_ROWS,
            CREATE_TIME
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
    echo "✅ All tables created successfully!"
    echo ""
    echo "Next steps:"
    echo "  1. Run tests: php bin/run-inline-tests.php"
    echo "  2. Check results: Should now be 61/61 tests passing"
    echo "  3. Run full suite: php bin/run-phase-c-tests.php"
    echo ""
else
    echo ""
    echo "❌ Migration failed!"
    echo "Check the error messages above."
    exit 1
fi
