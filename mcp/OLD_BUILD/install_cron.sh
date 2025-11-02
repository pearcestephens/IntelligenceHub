#!/bin/bash

# MCP Auto-Refresh Cron Setup
# Configures automatic KB refresh when code changes

echo "========================================="
echo "  MCP Auto-Refresh Cron Setup"
echo "========================================="
echo ""

MCP_DIR="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp"
CRON_JOB="*/5 * * * * cd $MCP_DIR && /usr/bin/php auto_refresh.php check >> /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/mcp_auto_refresh.log 2>&1"

echo "This will add the following cron job:"
echo ""
echo "$CRON_JOB"
echo ""
echo "This will check for code changes every 5 minutes and refresh KB if needed."
echo ""

read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Add to crontab
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    
    echo "✅ Cron job added successfully!"
    echo ""
    echo "Verify with: crontab -l | grep mcp"
    echo "View logs: tail -f /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/mcp_auto_refresh.log"
    echo ""
    echo "To remove: crontab -e (and delete the line)"
else
    echo "❌ Installation cancelled"
    exit 1
fi

echo ""
echo "========================================="
echo "  Additional Optional Cron Jobs"
echo "========================================="
echo ""
echo "1. Full KB refresh daily at 2 AM:"
echo "   0 2 * * * cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/kb && /usr/bin/php run_verified_kb_pipeline.php --full"
echo ""
echo "2. Clean old logs weekly:"
echo "   0 3 * * 0 find /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs -name '*.log' -mtime +30 -delete"
echo ""

exit 0
