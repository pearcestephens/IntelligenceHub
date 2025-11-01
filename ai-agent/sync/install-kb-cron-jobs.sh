#!/bin/bash
# ============================================================================
# AI Knowledge Base - Automated Cron Jobs Installer
# ============================================================================
# Purpose: Install all cron jobs for automatic KB indexing and sync
# Usage: bash install-kb-cron-jobs.sh
# ============================================================================

echo "════════════════════════════════════════════════════════════════"
echo "  AI Knowledge Base - Cron Jobs Installer"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Configuration
PHP_PATH="/usr/bin/php"
PROJECT_ROOT="/home/master/applications/jcepnzzkmj/public_html"
INDEXER_SCRIPT="${PROJECT_ROOT}/ai-agent/sync/kb-auto-indexer.php"
LOG_DIR="${PROJECT_ROOT}/logs/kb-sync"

# Create log directory
echo "[1/4] Creating log directory..."
mkdir -p "$LOG_DIR"
chmod 755 "$LOG_DIR"
echo "  ✓ Log directory created: $LOG_DIR"
echo ""

# Verify indexer script exists
echo "[2/4] Verifying indexer script..."
if [ ! -f "$INDEXER_SCRIPT" ]; then
    echo "  ✗ ERROR: Indexer script not found at: $INDEXER_SCRIPT"
    exit 1
fi
echo "  ✓ Indexer script found"
echo ""

# Generate crontab entries
echo "[3/4] Generating crontab entries..."

CRON_FILE="/tmp/kb-cron-jobs.txt"

cat > "$CRON_FILE" << 'CRONEOF'
# ============================================================================
# AI Knowledge Base - Automated Sync Jobs
# ============================================================================
# Installed: 2025-10-19
# Purpose: Keep knowledge base in sync with file system changes
# ============================================================================

# Every 5 minutes: Incremental sync for STAFF domain (most active)
*/5 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=staff --incremental >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/staff-incremental.log 2>&1

# Every 15 minutes: Incremental sync for WEB domain
*/15 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=web --incremental >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/web-incremental.log 2>&1

# Every 15 minutes: Incremental sync for GPT domain
*/15 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=gpt --incremental >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/gpt-incremental.log 2>&1

# Every 30 minutes: Incremental sync for WIKI domain
*/30 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=wiki --incremental >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/wiki-incremental.log 2>&1

# Every hour: Incremental sync for GLOBAL domain
0 * * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=global --incremental >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/global-incremental.log 2>&1

# Every 2 hours: Full index for STAFF domain (deep scan)
0 */2 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=staff --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/staff-full.log 2>&1

# Daily at 2 AM: Full index for ALL domains
0 2 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=global --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/global-full.log 2>&1
0 3 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=web --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/web-full.log 2>&1
0 4 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=gpt --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/gpt-full.log 2>&1
0 5 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=wiki --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/wiki-full.log 2>&1

# Daily at 6 AM: Full index for SUPERADMIN (god mode - all domains)
0 6 * * * /usr/bin/php /home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php --domain=superadmin --full >> /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync/superadmin-full.log 2>&1

# Weekly on Sunday at 1 AM: Cleanup old log files
0 1 * * 0 find /home/master/applications/jcepnzzkmj/public_html/logs/kb-sync -name "*.log" -mtime +30 -delete

# ============================================================================
# End of AI KB Cron Jobs
# ============================================================================

CRONEOF

echo "  ✓ Crontab entries generated"
echo ""

# Display what will be installed
echo "The following cron jobs will be installed:"
echo ""
cat "$CRON_FILE" | grep -E "^\*|^[0-9]" | while read line; do
    echo "  $line"
done
echo ""

# Ask for confirmation
echo "[4/4] Installing cron jobs..."
read -p "Do you want to install these cron jobs? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "  Installation cancelled"
    rm "$CRON_FILE"
    exit 0
fi

# Backup existing crontab
crontab -l > /tmp/crontab-backup-$(date +%Y%m%d-%H%M%S).txt 2>/dev/null
echo "  ✓ Existing crontab backed up"

# Install new cron jobs
crontab -l 2>/dev/null | cat - "$CRON_FILE" | crontab -
echo "  ✓ Cron jobs installed"

# Cleanup
rm "$CRON_FILE"

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "  ✅ INSTALLATION COMPLETE"
echo "════════════════════════════════════════════════════════════════"
echo ""
echo "Cron Schedule Summary:"
echo "  • Every 5 min:  STAFF incremental sync"
echo "  • Every 15 min: WEB, GPT incremental sync"
echo "  • Every 30 min: WIKI incremental sync"
echo "  • Every hour:   GLOBAL incremental sync"
echo "  • Every 2 hours: STAFF full index"
echo "  • Daily 2-6 AM: All domains full index"
echo "  • Weekly:       Log cleanup"
echo ""
echo "Log files location: $LOG_DIR"
echo ""
echo "To view cron jobs:  crontab -l"
echo "To remove KB jobs:  crontab -e  (manually remove lines)"
echo "To test manually:   php $INDEXER_SCRIPT --domain=staff --incremental"
echo ""
echo "════════════════════════════════════════════════════════════════"
