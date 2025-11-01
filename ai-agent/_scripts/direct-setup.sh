#!/bin/bash
# Direct execution command - no dependencies

cd /home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent

echo "🚀 Creating test database and running migration..."
echo ""

DB_HOST="127.0.0.1"
DB_USER="jcepnzzkmj"
DB_PASS="wprKh9Jq63"
PROD_DB="jcepnzzkmj"
TEST_DB="jcepnzzkmj_test"

# Step 1: Create test database
echo "📦 [1/4] Creating test database: $TEST_DB"
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" <<EOF
DROP DATABASE IF EXISTS $TEST_DB;
CREATE DATABASE $TEST_DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF

if [ $? -eq 0 ]; then
    echo "✅ Test database created"
else
    echo "❌ Failed to create test database"
    exit 1
fi

# Step 2: Copy schema (no data)
echo ""
echo "📋 [2/4] Copying schema structure (no data)..."
mysqldump -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" --no-data "$PROD_DB" | \
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$TEST_DB"

if [ $? -eq 0 ]; then
    echo "✅ Schema copied"
else
    echo "❌ Failed to copy schema"
    exit 1
fi

# Step 3: Run BATCH-7 migration
echo ""
echo "🔧 [3/4] Running BATCH-7 migration..."
if [ -f "sql/migrations/2025_10_07_batch_7_comprehensive.sql" ]; then
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$TEST_DB" < sql/migrations/2025_10_07_batch_7_comprehensive.sql
    
    if [ $? -eq 0 ]; then
        echo "✅ Migration applied"
    else
        echo "❌ Migration failed"
        exit 1
    fi
else
    echo "⚠️  Migration file not found, skipping..."
fi

# Step 4: Run tests
echo ""
echo "🧪 [4/4] Running tests..."
echo ""
php vendor/bin/phpunit tests/Integration/DatabaseIntegrationTest.php --testdox

echo ""
echo "╔════════════════════════════════════════════════════╗"
echo "║              Setup Complete! ✓                     ║"
echo "╚════════════════════════════════════════════════════╝"
echo ""
echo "📊 Databases:"
echo "   Production: $PROD_DB (untouched)"
echo "   Test:       $TEST_DB (ready for testing)"
echo ""
echo "🔄 Run tests again: php vendor/bin/phpunit"
echo ""
