#!/bin/bash
# MCP Health Monitor - Run every 5 minutes via cron
# Usage: ./monitor_health.sh

LOG_FILE="/home/master/applications/hdgwrzntwa/private_html/logs/health_monitor.log"
ALERT_EMAIL="admin@ecigdis.co.nz"
ENDPOINT="https://gpt.ecigdis.co.nz/mcp/server_v3.php"
MAX_RESPONSE_TIME=5000  # milliseconds

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Test health endpoint with timing
START=$(date +%s%3N)
RESPONSE=$(curl -s -w "\n%{http_code}" --max-time 10 "$ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"health_check"},"id":1}')
END=$(date +%s%3N)

HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | head -n-1)
RESPONSE_TIME=$((END - START))

# Check HTTP status
if [ "$HTTP_CODE" != "200" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ❌ Health check failed - HTTP $HTTP_CODE (${RESPONSE_TIME}ms)" >> "$LOG_FILE"

    # Optional: Send alert email (uncomment if mail is configured)
    # echo "MCP Health check failed: HTTP $HTTP_CODE" | mail -s "MCP Alert: Health Check Failed" "$ALERT_EMAIL"

    exit 1
fi

# Check if response contains success=true
if echo "$BODY" | grep -q '"success":true'; then
    # Check response time
    if [ "$RESPONSE_TIME" -gt "$MAX_RESPONSE_TIME" ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Health check passed but slow (${RESPONSE_TIME}ms > ${MAX_RESPONSE_TIME}ms)" >> "$LOG_FILE"
    else
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Health check passed (${RESPONSE_TIME}ms)" >> "$LOG_FILE"
    fi
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Health check returned unexpected response" >> "$LOG_FILE"
    exit 1
fi

# Check Redis connectivity (if configured)
if command -v redis-cli &> /dev/null; then
    if redis-cli ping &> /dev/null; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ✅ Redis connection OK" >> "$LOG_FILE"
    else
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Redis connection failed" >> "$LOG_FILE"
    fi
fi

exit 0
