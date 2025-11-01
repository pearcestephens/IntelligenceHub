#!/bin/bash
################################################################################
# Smart Cron Emergency Activation Script
# 
# Purpose: Fixes critical issues preventing Smart Cron from running
# Time: ~5 minutes
# Author: AI Development Assistant
# Date: October 27, 2025
################################################################################

set -e  # Exit on error

echo "════════════════════════════════════════════════════════════════════"
echo "  🚨 SMART CRON EMERGENCY ACTIVATION SCRIPT"
echo "════════════════════════════════════════════════════════════════════"
echo ""
echo "This script will:"
echo "  1. Fix script paths (symlink → real path)"
echo "  2. Update database job paths"
echo "  3. Create missing directories"
echo "  4. Add crontab entry for scheduler"
echo "  5. Test manual execution"
echo ""
echo "Time Required: ~5 minutes"
echo "Risk Level: LOW (all changes are reversible)"
echo ""
read -p "Continue? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Aborted by user"
    exit 1
fi

echo ""
echo "────────────────────────────────────────────────────────────────────"
echo "STEP 1: Creating Backup"
echo "────────────────────────────────────────────────────────────────────"

BACKUP_DIR="/home/129337.cloudwaysapps.com/jcepnzzkmj/backups/smart-cron-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"

echo "✓ Backing up crontab..."
crontab -l > "$BACKUP_DIR/crontab.backup" 2>/dev/null || echo "# No crontab" > "$BACKUP_DIR/crontab.backup"

echo "✓ Backing up scripts..."
cp -r /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/scripts "$BACKUP_DIR/" 2>/dev/null || true

echo "✓ Backup saved to: $BACKUP_DIR"
echo ""

echo "────────────────────────────────────────────────────────────────────"
echo "STEP 2: Fixing Script Paths"
echo "────────────────────────────────────────────────────────────────────"

cd /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/scripts

SCRIPTS_FIXED=0

for script in *.sh; do
    if [ -f "$script" ]; then
        if grep -q "/home/master/applications/" "$script"; then
            echo "  Fixing: $script"
            sed -i 's|/home/master/applications/jcepnzzkmj/public_html|/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html|g' "$script"
            chmod +x "$script"
            SCRIPTS_FIXED=$((SCRIPTS_FIXED + 1))
        fi
    fi
done

echo "✓ Fixed $SCRIPTS_FIXED script(s)"
echo ""

echo "────────────────────────────────────────────────────────────────────"
echo "STEP 3: Creating Missing Directories"
echo "────────────────────────────────────────────────────────────────────"

cd /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/scripts

# Create automation log directory
if [ ! -d "_automation/logs" ]; then
    mkdir -p _automation/logs
    chmod 755 _automation/logs
    echo "✓ Created: _automation/logs"
else
    echo "✓ Already exists: _automation/logs"
fi

# Ensure smart-cron log directory exists
if [ ! -d "../smart-cron/logs" ]; then
    mkdir -p ../smart-cron/logs
    chmod 755 ../smart-cron/logs
    echo "✓ Created: smart-cron/logs"
else
    echo "✓ Already exists: smart-cron/logs"
fi

# Ensure smart-cron locks directory exists
if [ ! -d "../smart-cron/logs/locks" ]; then
    mkdir -p ../smart-cron/logs/locks
    chmod 755 ../smart-cron/logs/locks
    echo "✓ Created: smart-cron/logs/locks"
else
    echo "✓ Already exists: smart-cron/logs/locks"
fi

echo ""

echo "────────────────────────────────────────────────────────────────────"
echo "STEP 4: Updating Database Job Paths"
echo "────────────────────────────────────────────────────────────────────"

echo "Connecting to database..."

# Count jobs before update
BEFORE_COUNT=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -sN -e \
    "SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE script_path LIKE '/home/master/applications/%';")

echo "Jobs with old path: $BEFORE_COUNT"

if [ "$BEFORE_COUNT" -gt 0 ]; then
    mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj <<EOF
    UPDATE smart_cron_integrated_jobs 
    SET script_path = REPLACE(script_path, 
        '/home/master/applications/', 
        '/home/129337.cloudwaysapps.com/')
    WHERE script_path LIKE '/home/master/applications/%';
EOF
    
    AFTER_COUNT=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -sN -e \
        "SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE script_path LIKE '/home/129337.cloudwaysapps.com/%';")
    
    echo "✓ Updated $BEFORE_COUNT job paths"
    echo "✓ Jobs with new path: $AFTER_COUNT"
else
    echo "✓ No jobs need path updates"
fi

echo ""

echo "────────────────────────────────────────────────────────────────────"
echo "STEP 5: Adding Scheduler to Crontab"
echo "────────────────────────────────────────────────────────────────────"

# Check if entry already exists
if crontab -l 2>/dev/null | grep -q "smart-cron.*scheduler.php"; then
    echo "⚠ Scheduler entry already exists in crontab"
    echo "Current entry:"
    crontab -l | grep "scheduler.php" || true
    echo ""
    read -p "Replace with correct entry? (y/n): " -n 1 -r
    echo ""
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Remove old entry and add new one
        (crontab -l 2>/dev/null | grep -v "scheduler.php"; \
         echo "* * * * * cd /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/smart-cron && php bin/scheduler.php >> logs/scheduler.log 2>&1") | crontab -
        echo "✓ Replaced crontab entry"
    else
        echo "⚠ Keeping existing entry"
    fi
else
    # Add new entry
    (crontab -l 2>/dev/null; \
     echo "* * * * * cd /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/smart-cron && php bin/scheduler.php >> logs/scheduler.log 2>&1") | crontab -
    echo "✓ Added scheduler to crontab"
fi

echo ""
echo "Current crontab entries:"
crontab -l | grep -v "^#" | grep -v "^$" || echo "(no active entries except smart-cron)"
echo ""

echo "────────────────────────────────────────────────────────────────────"
echo "STEP 6: Testing Manual Execution"
echo "────────────────────────────────────────────────────────────────────"

cd /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/smart-cron

echo "Running scheduler manually..."
echo ""

# Run scheduler and capture output
php bin/scheduler.php 2>&1 | tee /tmp/smart-cron-test.log

echo ""
echo "────────────────────────────────────────────────────────────────────"
echo "STEP 7: Verification"
echo "────────────────────────────────────────────────────────────────────"

# Check if scheduler completed successfully
if grep -q "Found.*jobs due for execution" /tmp/smart-cron-test.log; then
    echo "✓ Scheduler executed successfully"
else
    echo "⚠ Scheduler may have issues - check output above"
fi

# Check enabled jobs
ENABLED_COUNT=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -sN -e \
    "SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE enabled = 1;")

echo "✓ Enabled jobs: $ENABLED_COUNT"

# Check recent successes
SUCCESS_COUNT=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -sN -e \
    "SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE enabled = 1 AND last_exit_code = 0;")

echo "✓ Recently successful jobs: $SUCCESS_COUNT"

# Check recent failures
FAILURE_COUNT=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -sN -e \
    "SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE enabled = 1 AND last_exit_code != 0 AND last_exit_code IS NOT NULL;")

echo "✓ Recently failed jobs: $FAILURE_COUNT"

echo ""

# Show last 5 executions
echo "Last 5 job executions:"
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -t -e \
    "SELECT job_name, last_executed_at, last_exit_code, 
     SUBSTRING(last_error_message, 1, 50) as error_preview
     FROM smart_cron_integrated_jobs 
     WHERE enabled = 1 
     ORDER BY last_executed_at DESC 
     LIMIT 5;"

echo ""
echo "════════════════════════════════════════════════════════════════════"
echo "  ✅ ACTIVATION COMPLETE!"
echo "════════════════════════════════════════════════════════════════════"
echo ""
echo "Summary:"
echo "  • Scripts fixed: $SCRIPTS_FIXED"
echo "  • Database paths updated: $BEFORE_COUNT jobs"
echo "  • Directories created: 3"
echo "  • Crontab entry: Added/verified"
echo "  • Enabled jobs: $ENABLED_COUNT"
echo "  • Recent successes: $SUCCESS_COUNT"
echo "  • Recent failures: $FAILURE_COUNT"
echo ""
echo "Next Steps:"
echo "  1. Monitor logs for next 5 minutes:"
echo "     tail -f /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/scheduler.log"
echo ""
echo "  2. Check dashboard:"
echo "     https://staff.vapeshed.co.nz/assets/services/cron/smart-cron/dashboard.php"
echo ""
echo "  3. Review diagnosis report:"
echo "     cat SMART_CRON_DIAGNOSIS_REPORT.md"
echo ""
echo "Backup Location: $BACKUP_DIR"
echo ""
echo "To rollback if needed:"
echo "  crontab $BACKUP_DIR/crontab.backup"
echo ""
echo "════════════════════════════════════════════════════════════════════"
