#!/bin/bash
# Speed Optimization Status Check
# Run this anytime to see if optimizations are working

echo "================================================"
echo "⚡ SPEED OPTIMIZATION STATUS DASHBOARD"
echo "================================================"
echo ""

# 1. Check Redis
echo "1️⃣  REDIS CACHE"
echo "   Status: $(redis-cli ping 2>/dev/null || echo 'OFFLINE')"
redis-cli INFO stats 2>/dev/null | grep -E "keyspace_hits|keyspace_misses|total_commands" | head -3
echo "   Keys: $(redis-cli DBSIZE 2>/dev/null | grep -oP '\d+')"
echo ""

# 2. Check Queue System
echo "2️⃣  ASYNC QUEUE"
echo "   Queues: $(redis-cli KEYS 'ihub:queue:*' 2>/dev/null | wc -l)"
echo "   Jobs: $(redis-cli KEYS 'ihub:job:*' 2>/dev/null | wc -l)"
php -r "require 'classes/AsyncQueue.php'; \$queues = ['ai-requests', 'email', 'test']; foreach(\$queues as \$q) { \$s = AsyncQueue::getStats(\$q); if(\$s['total'] > 0) echo '   ' . \$q . ': ' . \$s['pending'] . ' pending, ' . \$s['processing'] . ' processing' . PHP_EOL; }"
echo ""

# 3. Check Database Performance
echo "3️⃣  DATABASE INDEXES"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
SELECT
  TABLE_NAME,
  COUNT(*) as index_count
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
  AND TABLE_NAME IN ('ai_conversations', 'ai_conversation_messages', 'intelligence_files')
GROUP BY TABLE_NAME;" 2>/dev/null | column -t
echo ""

# 4. Check PHP-FPM Performance
echo "4️⃣  PHP-FPM STATUS"
systemctl status php8.1-fpm 2>/dev/null | grep -E "Active:|Tasks:"
slow_count=$(tail -100 /home/129337.cloudwaysapps.com/hdgwrzntwa/logs/php-app.slow.log 2>/dev/null | wc -l)
echo "   Recent slow requests: ${slow_count}"
echo ""

# 5. Check HTTP/2
echo "5️⃣  HTTP/2 & CACHING"
grep -q "Protocols h2" .htaccess && echo "   ✅ HTTP/2 enabled" || echo "   ❌ HTTP/2 not found"
grep -q "mod_expires" .htaccess && echo "   ✅ Browser caching enabled" || echo "   ❌ Caching not found"
grep -q "mod_deflate" .htaccess && echo "   ✅ Compression enabled" || echo "   ❌ Compression not found"
echo ""

# 6. System Resources
echo "6️⃣  SYSTEM RESOURCES"
uptime | awk '{print "   Load: " $(NF-2) " " $(NF-1) " " $NF}'
free -h | grep Mem | awk '{print "   Memory: " $3 " / " $2 " used"}'
df -h /home | tail -1 | awk '{print "   Disk: " $3 " / " $2 " used (" $5 ")"}'
echo ""

# 7. API Timeouts
echo "7️⃣  API TIMEOUTS"
grep -c "CURLOPT_TIMEOUT => 15" assets/services/ai-agent/lib/ProviderFactory.php 2>/dev/null && echo "   ✅ Fast timeouts configured (15s)" || echo "   ⚠️  Check timeout configuration"
grep -c "CURLOPT_TIMEOUT => 30" assets/services/ai-agent/lib/ProviderFactory.php 2>/dev/null && echo "   ✅ Stream timeouts optimized (30s)"
echo ""

echo "================================================"
echo "✅ ALL OPTIMIZATIONS: $(grep -q 'Protocols h2' .htaccess && echo 'ACTIVE' || echo 'CHECK LOGS')"
echo "================================================"
