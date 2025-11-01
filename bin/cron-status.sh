#!/bin/bash

##############################################################################
# CRON STATUS REPORT - Complete System Overview
##############################################################################

echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                    CRON SYSTEM STATUS REPORT                       ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
echo ""
echo "Generated: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# ============================================================================
# 1. ACTIVE CRONTAB ENTRIES
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1. ACTIVE CRONTAB ENTRIES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
CRON_COUNT=$(crontab -l 2>/dev/null | grep -v "^#" | grep -v "^$" | wc -l)
echo "Total active cron jobs: $CRON_COUNT"
echo ""
crontab -l 2>/dev/null | grep -v "^#" | grep -v "^$" | nl
echo ""

# ============================================================================
# 2. TOOL GOVERNANCE STATUS
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2. TOOL GOVERNANCE AUTOMATION STATUS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# Check if tool governance scripts exist
GOVERNANCE_SCRIPT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh"
SMART_CRON_JOB="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services/cron/smart-cron/jobs/maintenance/tool-governance-weekly.sh"

if [ -f "$GOVERNANCE_SCRIPT" ]; then
    echo "✅ Main Script: $GOVERNANCE_SCRIPT"
    ls -lh "$GOVERNANCE_SCRIPT"
else
    echo "❌ Main Script: NOT FOUND"
fi
echo ""

if [ -f "$SMART_CRON_JOB" ]; then
    echo "✅ Smart Cron Job: $SMART_CRON_JOB"
    ls -lh "$SMART_CRON_JOB"
else
    echo "❌ Smart Cron Job: NOT FOUND"
fi
echo ""

# Check if it's in crontab
if crontab -l 2>/dev/null | grep -q "tool-governance"; then
    echo "✅ Tool Governance IS in crontab:"
    crontab -l 2>/dev/null | grep "tool-governance"
else
    echo "⚠️  Tool Governance NOT in crontab"
fi
echo ""

# Check for recent logs
LOG_FILE="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/tool-governance.log"
if [ -f "$LOG_FILE" ]; then
    echo "✅ Log file exists: $LOG_FILE"
    echo "   Last modified: $(stat -c %y "$LOG_FILE" 2>/dev/null || stat -f "%Sm" "$LOG_FILE" 2>/dev/null)"
    echo "   Last 5 lines:"
    tail -5 "$LOG_FILE" | sed 's/^/   /'
else
    echo "⚠️  No log file yet: $LOG_FILE"
    echo "   (Will be created on first run)"
fi
echo ""

# ============================================================================
# 3. SMART CRON SYSTEM STATUS
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3. SMART CRON SYSTEM STATUS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

SMART_CRON_LOG="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services/cron/smart-cron/logs/smart-cron.log"
if [ -f "$SMART_CRON_LOG" ]; then
    echo "✅ Smart Cron is active"
    echo "   Last run: $(tail -1 "$SMART_CRON_LOG" 2>/dev/null)"
    echo ""
    echo "   Recent activity (last 10 lines):"
    tail -10 "$SMART_CRON_LOG" | sed 's/^/   /'
else
    echo "⚠️  Smart Cron log not found"
fi
echo ""

# ============================================================================
# 4. RECENT CRON ACTIVITY
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4. RECENT CRON ACTIVITY (from syslog)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
if [ -f /var/log/syslog ]; then
    echo "Last 20 cron entries:"
    grep CRON /var/log/syslog 2>/dev/null | tail -20 | sed 's/^/   /'
else
    echo "⚠️  Syslog not accessible"
fi
echo ""

# ============================================================================
# 5. RUNNING PROCESSES
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5. RUNNING CRON PROCESSES"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
CRON_PROCS=$(ps aux | grep -E "cron|smart-cron|tool-governance" | grep -v grep | wc -l)
if [ "$CRON_PROCS" -gt 0 ]; then
    echo "✅ Found $CRON_PROCS active processes:"
    ps aux | grep -E "cron|smart-cron|tool-governance" | grep -v grep | sed 's/^/   /'
else
    echo "⚠️  No active cron processes found"
fi
echo ""

# ============================================================================
# 6. TOOL AUDIT REPORTS
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "6. TOOL AUDIT REPORTS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

AUDIT_DIR="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/audits"
if [ -d "$AUDIT_DIR" ]; then
    echo "✅ Audit directory exists: $AUDIT_DIR"
    echo ""
    echo "   Recent reports:"
    ls -lht "$AUDIT_DIR"/*.md 2>/dev/null | head -10 | sed 's/^/   /' || echo "   (No reports yet)"
else
    echo "⚠️  Audit directory not found"
fi
echo ""

# ============================================================================
# 7. RECOMMENDATIONS
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "7. RECOMMENDATIONS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

RECOMMENDATIONS=()

# Check if tool governance is in crontab
if ! crontab -l 2>/dev/null | grep -q "tool-governance"; then
    RECOMMENDATIONS+=("❌ Add tool governance to crontab: 0 3 * * 1 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh")
fi

# Check if scripts are executable
if [ ! -x "$GOVERNANCE_SCRIPT" ]; then
    RECOMMENDATIONS+=("❌ Make script executable: chmod +x $GOVERNANCE_SCRIPT")
fi

# Check if logs directory exists
if [ ! -d "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs" ]; then
    RECOMMENDATIONS+=("❌ Create logs directory: mkdir -p /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs")
fi

if [ ${#RECOMMENDATIONS[@]} -eq 0 ]; then
    echo "✅ No issues found - system looks good!"
else
    echo "Found ${#RECOMMENDATIONS[@]} recommendations:"
    for rec in "${RECOMMENDATIONS[@]}"; do
        echo "$rec"
    done
fi
echo ""

# ============================================================================
# 8. QUICK ACTIONS
# ============================================================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "8. QUICK ACTIONS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "To add tool governance to crontab:"
echo "  crontab -e"
echo "  # Add this line:"
echo "  0 3 * * 1 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh >> /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/tool-governance.log 2>&1"
echo ""
echo "To test tool governance manually:"
echo "  bash /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh"
echo ""
echo "To run tool audit now:"
echo "  cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html && php bin/tool-audit-consolidator.php"
echo ""
echo "To run tool integration:"
echo "  cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html && php bin/tool-integration-merger.php --merge-now"
echo ""

echo "╔════════════════════════════════════════════════════════════════════╗"
echo "║                      END OF STATUS REPORT                          ║"
echo "╚════════════════════════════════════════════════════════════════════╝"
