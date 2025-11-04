#!/bin/bash
#
# INTELLIGENCE CLEANUP & REBUILD
# Removes garbage and re-indexes only essential files
#

set -e

echo "🧹 INTELLIGENCE SYSTEM CLEANUP & REBUILD"
echo "========================================"
echo ""

DB_USER="hdgwrzntwa"
DB_PASS=$(grep DB_PASS /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env | cut -d= -f2)
DB_NAME="hdgwrzntwa"

# Step 1: Backup current state
echo "📦 Step 1: Creating backup..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
    CREATE TABLE IF NOT EXISTS intelligence_content_backup_$(date +%Y%m%d)
    SELECT * FROM intelligence_content;

    CREATE TABLE IF NOT EXISTS intelligence_content_text_backup_$(date +%Y%m%d)
    SELECT * FROM intelligence_content_text;
"
echo "   ✅ Backup created"
echo ""

# Step 2: Delete garbage categories
echo "🗑️  Step 2: Removing garbage content..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << 'SQL'
-- Delete vendor directories
DELETE ic, ict
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.content_path LIKE '%/vendor/%'
   OR ic.content_path LIKE '%/node_modules/%';

-- Delete OLD/TEMP/ARCHIVE directories
DELETE ic, ict
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.content_path LIKE '%/OLD %'
   OR ic.content_path LIKE '%/TEMP %'
   OR ic.content_path LIKE '%OLD STUFF%'
   OR ic.content_path LIKE '%TEMP OLD%'
   OR ic.content_path LIKE '%/backup/%'
   OR ic.content_path LIKE '%/backups/%'
   OR ic.content_path LIKE '%/archive/%'
   OR ic.content_path LIKE '%/ARCHIVE/%'
   OR ic.content_path LIKE '%cleanup_%'
   OR ic.content_path LIKE '%CLEANUP_%';

-- Delete binary/media files that shouldn't be indexed
DELETE ic, ict
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.content_path LIKE '%.png'
   OR ic.content_path LIKE '%.jpg'
   OR ic.content_path LIKE '%.jpeg'
   OR ic.content_path LIKE '%.gif'
   OR ic.content_path LIKE '%.zip'
   OR ic.content_path LIKE '%.tar'
   OR ic.content_path LIKE '%.gz'
   OR ic.content_path LIKE '%.phar'
   OR ic.content_path LIKE '%.wav'
   OR ic.content_path LIKE '%.mp3'
   OR ic.content_path LIKE '%.log'
   OR ic.content_path LIKE '%.stream'
   OR ic.content_path LIKE '%.deploy'
   OR ic.content_path LIKE '%.bak'
   OR ic.content_path LIKE '%.backup';

-- Delete files that are too large (> 5MB - probably garbage)
DELETE ic, ict
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.file_size > 5242880;

-- Delete files with no meaningful content
DELETE ic, ict
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE (ict.content_text IS NULL OR ict.content_text = '' OR LENGTH(ict.content_text) < 50)
  AND ic.content_path NOT LIKE '%/modules/%'
  AND ic.content_path NOT LIKE '%/api/%'
  AND ic.content_path NOT LIKE '%/_kb/%';
SQL

echo "   ✅ Garbage removed"
echo ""

# Step 3: Show current state
echo "📊 Step 3: Current database state..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT
    'After Cleanup' as status,
    COUNT(*) as total_files,
    COUNT(CASE WHEN ict.content_text IS NOT NULL THEN 1 END) as with_text,
    ROUND(AVG(LENGTH(ict.content_text))) as avg_text_length,
    ROUND(AVG(ict.word_count)) as avg_words
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.is_active = 1;
"
echo ""

# Step 4: List what's left
echo "📁 Step 4: Remaining content by directory..."
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT
    SUBSTRING_INDEX(SUBSTRING_INDEX(ic.content_path, '/', 7), '/', -1) as directory,
    COUNT(*) as file_count,
    COUNT(CASE WHEN ict.content_text IS NOT NULL THEN 1 END) as with_text,
    CONCAT(ROUND(AVG(ict.word_count)), ' words') as avg_content
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.is_active = 1
GROUP BY directory
ORDER BY file_count DESC
LIMIT 20;
"
echo ""

# Step 5: Re-run scanner on remaining files
echo "🔄 Step 5: Re-scanning files to extract missing text..."
echo "   This will take 5-10 minutes..."
echo ""

cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/scanner

if [ -f "run_scanner.php" ]; then
    php run_scanner.php --force --extract-text --limit=0
    echo "   ✅ Scanner completed"
else
    echo "   ⚠️  Scanner not found at scanner/run_scanner.php"
    echo "   You'll need to run the scanner manually"
fi

echo ""
echo "✅ CLEANUP COMPLETE!"
echo ""
echo "📊 Final Statistics:"
mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "
SELECT
    COUNT(*) as total_files,
    COUNT(CASE WHEN ict.content_text IS NOT NULL AND LENGTH(ict.content_text) > 0 THEN 1 END) as with_content,
    COUNT(CASE WHEN ict.extracted_keywords IS NOT NULL AND ict.extracted_keywords != '[]' THEN 1 END) as with_keywords,
    COUNT(CASE WHEN ict.semantic_tags IS NOT NULL AND ict.semantic_tags != '[]' THEN 1 END) as with_tags,
    ROUND(AVG(ic.intelligence_score), 2) as avg_intelligence_score
FROM intelligence_content ic
LEFT JOIN intelligence_content_text ict ON ic.content_id = ict.content_id
WHERE ic.is_active = 1;
"

echo ""
echo "🎯 Next Steps:"
echo "   1. Test semantic search: php test_semantic_search.php"
echo "   2. Verify results are clean and relevant"
echo "   3. Run quality score updates if needed"
echo ""
