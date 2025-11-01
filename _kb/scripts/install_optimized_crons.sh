#!/bin/bash

# ============================================================================
# OPTIMIZED CRON SCHEDULE FOR HDGWRZNTWA (Intelligence Hub)
# Generated: October 25, 2025
# Backup saved: _kb/backups/crontab_backup_*.txt
# ============================================================================

# This script installs an optimized cron schedule
# Run: bash install_optimized_crons.sh

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     OPTIMIZED CRON SCHEDULE INSTALLER - Intelligence Hub      ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Check if backup exists
BACKUP_COUNT=$(ls -1 /home/master/applications/hdgwrzntwa/public_html/_kb/backups/crontab_backup_*.txt 2>/dev/null | wc -l)
if [ "$BACKUP_COUNT" -eq 0 ]; then
    echo "❌ ERROR: No crontab backup found!"
    echo "   Run: crontab -l > /home/master/applications/hdgwrzntwa/public_html/_kb/backups/crontab_backup_\$(date +%Y%m%d_%H%M%S).txt"
    exit 1
fi

echo "✅ Backup exists: $BACKUP_COUNT backup(s) found"
echo ""

# Show current cron count
CURRENT_COUNT=$(crontab -l 2>/dev/null | grep -v "^#" | grep -v "^$" | wc -l)
echo "📊 Current active cron jobs: $CURRENT_COUNT"
echo ""

# Create temporary file with optimized schedule
TEMP_CRON=$(mktemp)

cat > "$TEMP_CRON" << 'CRONEOF'
# ============================================================================
# OPTIMIZED CRON SCHEDULE - Intelligence Hub (hdgwrzntwa)
# Generated: October 25, 2025
# Server: /home/master/applications/hdgwrzntwa/public_html
# ============================================================================

SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
MAILTO=""

# ============================================================================
# KB INTELLIGENCE SYSTEM - CORE (Every 4 hours)
# ============================================================================

# Intelligence Engine V2 - Full analysis every 4 hours
0 */4 * * * cd /home/master/applications/hdgwrzntwa/public_html && php scripts/kb_intelligence_engine_v2.php >> logs/kb_intelligence.log 2>&1

# AST Security Scanner - Daily at 3 AM
0 3 * * * cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/ast_security_scanner.php -d . -o _kb/deep_intelligence/SECURITY_AST_DAILY.md >> logs/security_scan.log 2>&1

# Call Graph Generator - Every 6 hours
0 */6 * * * cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/generate_call_graph.php -d . -o _kb/intelligence/call_graph.json -m _kb/intelligence/CALL_GRAPH.md >> logs/call_graph.log 2>&1

# Enhanced Security Scanner - Weekly Sunday 4 AM
0 4 * * 0 cd /home/master/applications/hdgwrzntwa/public_html && php scripts/enhanced_security_scanner.php -d . -o _kb/deep_intelligence/SECURITY_VULNERABILITIES_V2.md >> logs/security_enhanced.log 2>&1

# ============================================================================
# PERFORMANCE & MONITORING (Periodic)
# ============================================================================

# Performance Analysis - Daily at 3:30 AM
30 3 * * * cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/analyze_performance.php >> logs/performance.log 2>&1

# Dead Code Detection - Weekly Sunday 5 AM
0 5 * * 0 cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/detect_dead_code.php >> logs/dead_code.log 2>&1

# Relationship Mapping - Every 6 hours (offset)
30 */6 * * * cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/map_relationships.php >> logs/relationships.log 2>&1

# ============================================================================
# MAINTENANCE & CLEANUP (Daily/Weekly)
# ============================================================================

# KB Cache Cleanup - Daily at 2 AM
0 2 * * * find /home/master/applications/hdgwrzntwa/public_html/_kb/cache -name "*.cache" -mtime +7 -delete >> logs/cache_cleanup.log 2>&1

# Log Rotation - Daily at 4 AM
0 4 * * * find /home/master/applications/hdgwrzntwa/public_html/logs -name "*.log" -size +50M -exec gzip {} \; >> logs/log_rotation.log 2>&1

# Old Log Cleanup - Weekly Monday 3 AM
0 3 * * 1 find /home/master/applications/hdgwrzntwa/public_html/logs -name "*.gz" -mtime +30 -delete >> logs/log_cleanup.log 2>&1

# KB Snapshots Cleanup - Weekly Monday 5 AM
0 5 * * 1 cd /home/master/applications/hdgwrzntwa/public_html && php _kb/scripts/cleanup_kb.php --older-than=30 >> logs/kb_cleanup.log 2>&1

# ============================================================================
# SYSTEM HEALTH (Monitoring)
# ============================================================================

# Disk Space Check - Every 6 hours
0 */6 * * * df -h /home/master/applications/hdgwrzntwa/public_html | awk 'NR==2 {if (substr($5,1,length($5)-1) > 90) print "WARNING: Disk usage is " $5}' >> logs/disk_check.log 2>&1

# Error Log Summary - Daily at 8 AM
0 8 * * * grep -i "error\|fatal" /home/master/applications/hdgwrzntwa/public_html/logs/*.log 2>/dev/null | tail -50 >> logs/daily_errors.log 2>&1

# Cron Health Check - Every 12 hours
0 */12 * * * crontab -l | grep -c "hdgwrzntwa" > /home/master/applications/hdgwrzntwa/public_html/logs/cron_health.log 2>&1

# ============================================================================
# INTEGRATION - CIS (jcepnzzkmj) Webhooks
# ============================================================================

# Webhook Queue Processor - Every 2 minutes
*/2 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/webhooks/core/cron_queue_processor.php >> /home/master/applications/jcepnzzkmj/logs/webhook_queue.log 2>&1

# Webhook Monitor - Every 5 minutes
*/5 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/webhooks/monitor/cron_monitor.php >> /home/master/applications/jcepnzzkmj/logs/webhook_monitor.log 2>&1

# Background Sync - Every 5 minutes
*/5 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/assets/cron/utility_scripts/consignments/BACKGROUND_SYNC_TRIPLE_CHECKED.php >> /home/master/applications/jcepnzzkmj/logs/background_sync.log 2>&1

# ============================================================================
# DAILY SYNC - Intelligence to CIS (3 AM)
# ============================================================================

# Daily Intelligence Sync - 3 AM
0 3 * * * /bin/bash /home/master/applications/hdgwrzntwa/public_html/_kb/scripts/daily_sync_to_master.sh >> /home/master/applications/hdgwrzntwa/public_html/logs/daily_sync.log 2>&1

# ============================================================================
# NEURAL SCANNER (Safe Mode) - Daily 3 AM
# ============================================================================

0 3 * * * /usr/bin/php /home/master/applications/hdgwrzntwa/public_html/scripts/safe_neural_scanner.php >> /home/master/applications/hdgwrzntwa/public_html/logs/neural_scan.log 2>&1

# ============================================================================
# END OPTIMIZED SCHEDULE
# ============================================================================
CRONEOF

echo "📝 New optimized schedule preview:"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
cat "$TEMP_CRON" | grep -v "^#" | grep -v "^$" | head -20
echo "   ... ($(cat "$TEMP_CRON" | grep -v "^#" | grep -v "^$" | wc -l) total active jobs)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

NEW_COUNT=$(cat "$TEMP_CRON" | grep -v "^#" | grep -v "^$" | wc -l)
echo "📊 Comparison:"
echo "   Current jobs: $CURRENT_COUNT"
echo "   New jobs: $NEW_COUNT"
echo "   Reduction: $((CURRENT_COUNT - NEW_COUNT)) jobs"
echo ""

# Ask for confirmation
read -p "❓ Install this optimized schedule? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "❌ Installation cancelled"
    rm "$TEMP_CRON"
    exit 0
fi

# Install new crontab
crontab "$TEMP_CRON"
rm "$TEMP_CRON"

echo ""
echo "✅ Optimized cron schedule installed successfully!"
echo ""
echo "📋 Summary of key jobs:"
echo "   • Intelligence Engine: Every 4 hours"
echo "   • AST Security Scan: Daily at 3 AM"
echo "   • Call Graph: Every 6 hours"
echo "   • Enhanced Security: Weekly Sunday 4 AM"
echo "   • Performance Analysis: Daily 3:30 AM"
echo "   • Dead Code Detection: Weekly Sunday 5 AM"
echo "   • Webhook Processing: Every 2-5 minutes"
echo "   • Daily Sync: 3 AM to CIS"
echo ""
echo "🔍 Verify installation:"
echo "   crontab -l | grep -v '^#' | grep -v '^$'"
echo ""
echo "♻️  Restore backup if needed:"
echo "   crontab /home/master/applications/hdgwrzntwa/public_html/_kb/backups/crontab_backup_*.txt"
echo ""
echo "✅ Installation complete!"
