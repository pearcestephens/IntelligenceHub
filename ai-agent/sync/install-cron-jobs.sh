#!/bin/bash
# ============================================================================
# Install KB Auto-Indexer Cron Jobs
# ============================================================================
# This script installs all cron jobs for automated KB indexing and syncing
# 
# Usage: bash install-cron-jobs.sh
# ============================================================================

SCRIPT_DIR="/home/master/applications/jcepnzzkmj/public_html/ai-agent/sync"
LOG_DIR="/home/master/applications/jcepnzzkmj/public_html/logs"

echo "========================================="
echo "  KB Auto-Indexer Cron Installation"
echo "========================================="
echo ""

# Create log directory if it doesn't exist
mkdir -p "$LOG_DIR"

echo "Adding cron jobs..."
echo ""

# Backup existing crontab
crontab -l > /tmp/crontab-backup-$(date +%Y%m%d-%H%M%S).txt 2>/dev/null
echo "✓ Backed up existing crontab"

# Create new cron entries
(crontab -l 2>/dev/null; cat <<EOF

# ============================================================================
# AI Knowledge Base Auto-Indexer (Installed: $(date))
# ============================================================================

# 1. INCREMENTAL SYNC - Every 15 minutes (quick updates)
*/15 * * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=staff --incremental >> $LOG_DIR/kb-staff-incremental.log 2>&1
*/15 * * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=web --incremental >> $LOG_DIR/kb-web-incremental.log 2>&1
*/15 * * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=gpt --incremental >> $LOG_DIR/kb-gpt-incremental.log 2>&1
*/15 * * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=wiki --incremental >> $LOG_DIR/kb-wiki-incremental.log 2>&1

# 2. FULL INDEX - Every 6 hours (deep scan)
0 */6 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=staff --full >> $LOG_DIR/kb-staff-full.log 2>&1
0 */6 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=web --full >> $LOG_DIR/kb-web-full.log 2>&1
0 */6 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=gpt --full >> $LOG_DIR/kb-gpt-full.log 2>&1
0 */6 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=wiki --full >> $LOG_DIR/kb-wiki-full.log 2>&1

# 3. SUPERADMIN SYNC - Daily at 2 AM (consolidate ALL domains)
0 2 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=superadmin --full >> $LOG_DIR/kb-superadmin-full.log 2>&1

# 4. GLOBAL SYNC - Daily at 3 AM (shared knowledge)
0 3 * * * cd $SCRIPT_DIR && /usr/bin/php kb-auto-indexer.php --domain=global --full >> $LOG_DIR/kb-global-full.log 2>&1

# 5. LOG CLEANUP - Weekly on Sunday at 4 AM (keep last 30 days)
0 4 * * 0 find $LOG_DIR -name "kb-*.log" -mtime +30 -delete

EOF
) | crontab -

echo ""
echo "========================================="
echo "  ✅ INSTALLATION COMPLETE!"
echo "========================================="
echo ""
echo "Cron jobs installed:"
echo "  • Incremental sync: Every 15 minutes (staff, web, gpt, wiki)"
echo "  • Full index: Every 6 hours (all domains)"
echo "  • SuperAdmin sync: Daily at 2 AM"
echo "  • Global sync: Daily at 3 AM"
echo "  • Log cleanup: Weekly (Sunday 4 AM)"
echo ""
echo "Log files location: $LOG_DIR"
echo ""
echo "To view active cron jobs:"
echo "  crontab -l"
echo ""
echo "To monitor logs:"
echo "  tail -f $LOG_DIR/kb-staff-incremental.log"
echo "  tail -f $LOG_DIR/kb-staff-full.log"
echo ""
