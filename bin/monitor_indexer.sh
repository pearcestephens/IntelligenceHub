#!/bin/bash
# Semantic Search Indexer Monitor
# Usage: ./monitor_indexer.sh

echo "🔍 SEMANTIC SEARCH INDEXER - STATUS MONITOR"
echo "=========================================================="
echo ""

# Check if process is running
if pgrep -f "semantic_indexer.php" > /dev/null; then
    echo "✅ Process Status: RUNNING (PID: $(pgrep -f 'semantic_indexer.php'))"
else
    echo "❌ Process Status: NOT RUNNING"
fi

echo ""

# Check database stats
echo "📊 DATABASE STATS:"
mysql -h 127.0.0.1 -u hdgwrzntwa -pbFUdRjh4Jx hdgwrzntwa -e "
SELECT
    COUNT(*) as total_indexed,
    COUNT(CASE WHEN simhash64 IS NOT NULL THEN 1 END) as with_simhash,
    COUNT(CASE WHEN JSON_LENGTH(embedding_vector) > 0 THEN 1 END) as with_embeddings,
    ROUND(COUNT(*) / 8645 * 100, 2) as percent_complete,
    MAX(indexed_at) as last_indexed
FROM intelligence_embeddings
WHERE is_active = 1
" 2>/dev/null

echo ""

# Check recent log activity
echo "📝 RECENT ACTIVITY (last 5 files):"
tail -100 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/semantic_indexer.log | grep "✅ Indexed" | tail -5

echo ""

# Check for errors
ERROR_COUNT=$(tail -200 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/semantic_indexer.log | grep -c "❌ Error")
if [ $ERROR_COUNT -gt 0 ]; then
    echo "⚠️  ERRORS DETECTED: $ERROR_COUNT in last 200 lines"
    echo "Latest error:"
    tail -200 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/semantic_indexer.log | grep "❌ Error" | tail -1
else
    echo "✅ NO ERRORS in last 200 lines"
fi

echo ""
echo "=========================================================="
echo "To view live log: tail -f logs/semantic_indexer.log"
echo "To stop indexer: pkill -f semantic_indexer.php"
echo "=========================================================="
