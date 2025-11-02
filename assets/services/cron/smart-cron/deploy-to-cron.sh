#!/bin/bash
################################################################################
# Smart Cron - Add to Crontab
################################################################################

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SMART_CRON_PATH="/home/master/applications/hdgwrzntwa/public_html/assets/services/cron/smart-cron.php"
LOG_PATH="/home/master/applications/hdgwrzntwa/public_html/assets/services/cron/logs/smart-cron.log"
PHP_PATH="/usr/bin/php"

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║          🚀 SMART CRON - PRODUCTION DEPLOYMENT               ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Check if smart-cron.php exists
if [ ! -f "$SMART_CRON_PATH" ]; then
    echo "❌ ERROR: smart-cron.php not found at: $SMART_CRON_PATH"
    exit 1
fi
echo "✅ Found smart-cron.php"

# Check if PHP exists
if [ ! -f "$PHP_PATH" ]; then
    echo "⚠️  WARNING: PHP not found at $PHP_PATH, trying to find it..."
    PHP_PATH=$(which php)
    echo "   Found PHP at: $PHP_PATH"
fi
echo "✅ PHP found at: $PHP_PATH"

# Create log directory if it doesn't exist
LOG_DIR=$(dirname "$LOG_PATH")
if [ ! -d "$LOG_DIR" ]; then
    mkdir -p "$LOG_DIR"
    echo "✅ Created log directory: $LOG_DIR"
fi

# Backup existing crontab
echo ""
echo "📦 Backing up existing crontab..."
crontab -l > /tmp/crontab.backup.$(date +%Y%m%d_%H%M%S) 2>/dev/null || true
echo "✅ Backup saved"

# Check if entry already exists
CRON_ENTRY="* * * * * $PHP_PATH $SMART_CRON_PATH >> $LOG_PATH 2>&1"

if crontab -l 2>/dev/null | grep -F "$SMART_CRON_PATH" > /dev/null; then
    echo ""
    echo "⚠️  Smart Cron already exists in crontab!"
    echo ""
    echo "Current entry:"
    crontab -l | grep "$SMART_CRON_PATH"
    echo ""
    read -p "Do you want to replace it? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Cancelled"
        exit 0
    fi

    # Remove old entry
    crontab -l | grep -v "$SMART_CRON_PATH" | crontab -
    echo "✅ Removed old entry"
fi

# Add new entry
echo ""
echo "📝 Adding Smart Cron to crontab..."
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -
echo "✅ Cron job added!"

# Verify
echo ""
echo "📋 Current crontab entries:"
echo "─────────────────────────────────────────────────────────────"
crontab -l | grep -v "^#" | grep -v "^$"
echo "─────────────────────────────────────────────────────────────"

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║                    ✅ DEPLOYMENT COMPLETE                    ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
echo "🎯 Next Steps:"
echo "   1. Monitor logs: tail -f $LOG_PATH"
echo "   2. Check health: php $SCRIPT_DIR/bin/health-check.php"
echo "   3. Watch for 5 minutes to ensure stability"
echo ""
echo "🔧 Commands:"
echo "   • View logs:      tail -f $LOG_PATH"
echo "   • Health check:   cd $(dirname $SMART_CRON_PATH) && php smart-cron/bin/health-check.php"
echo "   • Edit crontab:   crontab -e"
echo "   • Remove entry:   crontab -l | grep -v smart-cron.php | crontab -"
echo ""
