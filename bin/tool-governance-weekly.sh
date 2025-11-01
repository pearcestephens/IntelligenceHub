#!/bin/bash

##############################################################################
# Tool Governance - Weekly Audit Automation
##############################################################################
# This script runs weekly to discover all tools, detect duplicates,
# identify gaps, and maintain the tool registry.
#
# Schedule: Every Monday at 3:00 AM
# Cron: 0 3 * * 1 /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/bin/tool-governance-weekly.sh
##############################################################################

# Configuration
PROJECT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html"
LOG_DIR="$PROJECT_ROOT/logs"
AUDIT_LOG="$LOG_DIR/tool-governance.log"
ERROR_LOG="$LOG_DIR/tool-governance-error.log"
ALERT_EMAIL="pearce.stephens@ecigdis.co.nz"

# Ensure log directory exists
mkdir -p "$LOG_DIR"

# Log function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$AUDIT_LOG"
}

# Error handler
error() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $1" | tee -a "$ERROR_LOG" "$AUDIT_LOG"
}

log "=========================================="
log "Tool Governance Weekly Audit - START"
log "=========================================="

# Change to project directory
cd "$PROJECT_ROOT" || {
    error "Failed to change to project directory: $PROJECT_ROOT"
    exit 1
}

# Run tool audit
log "Running tool audit consolidator..."
if php bin/tool-audit-consolidator.php >> "$AUDIT_LOG" 2>> "$ERROR_LOG"; then
    log "✅ Tool audit completed successfully"
else
    error "Tool audit failed with exit code $?"
    exit 1
fi

# Extract key metrics from the last run
TOTAL_TOOLS=$(grep -oP 'Total Tools: \K\d+' "$AUDIT_LOG" | tail -1)
DUPLICATES=$(grep -oP 'Duplicates: \K\d+' "$AUDIT_LOG" | tail -1)
GAPS=$(grep -oP 'Gaps: \K\d+' "$AUDIT_LOG" | tail -1)
COVERAGE=$(grep -oP 'Registry Coverage: \K[\d.]+' "$AUDIT_LOG" | tail -1)

log "=========================================="
log "Audit Results:"
log "  Total Tools: $TOTAL_TOOLS"
log "  Duplicates: $DUPLICATES"
log "  Gaps: $GAPS"
log "  Coverage: ${COVERAGE}%"
log "=========================================="

# Alert conditions
ALERT=0

# Check for coverage drop
if (( $(echo "$COVERAGE < 80.0" | bc -l) )); then
    error "⚠️  Coverage dropped below 80% (current: ${COVERAGE}%)"
    ALERT=1
fi

# Check for excessive duplicates
if [ "$DUPLICATES" -gt 50 ]; then
    error "⚠️  High number of duplicates detected: $DUPLICATES"
    ALERT=1
fi

# Check for critical gaps
if [ "$GAPS" -gt 5 ]; then
    error "⚠️  Multiple capability gaps detected: $GAPS"
    ALERT=1
fi

# Send alert if needed
if [ $ALERT -eq 1 ]; then
    log "Sending alert notification..."

    # Create alert summary
    ALERT_MSG="Tool Governance Alert - $(date '+%Y-%m-%d %H:%M:%S')

WARNING: Tool governance metrics exceeded thresholds

Current Status:
- Total Tools: $TOTAL_TOOLS
- Duplicates: $DUPLICATES (threshold: 50)
- Gaps: $GAPS (threshold: 5)
- Coverage: ${COVERAGE}% (threshold: 80%)

Action Required:
1. Review audit reports in _kb/audits/
2. Run tool integration merger to consolidate duplicates
3. Address identified capability gaps

Reports Available:
- $PROJECT_ROOT/_kb/audits/COMPLETE_TOOL_INVENTORY.md
- $PROJECT_ROOT/_kb/audits/TOOL_CONSOLIDATION_PLAN.md
- $PROJECT_ROOT/_kb/audits/INTEGRATION_CHECKLIST.md

Full log: $AUDIT_LOG
Error log: $ERROR_LOG
"

    # Write alert to file
    echo "$ALERT_MSG" > "$LOG_DIR/tool-governance-alert.txt"

    # Send email if mail is available
    if command -v mail &> /dev/null; then
        echo "$ALERT_MSG" | mail -s "Tool Governance Alert" "$ALERT_EMAIL"
        log "✅ Alert email sent to $ALERT_EMAIL"
    else
        log "⚠️  Mail command not available, alert saved to: $LOG_DIR/tool-governance-alert.txt"
    fi
fi

# Cleanup old logs (keep last 90 days)
log "Cleaning up old logs..."
find "$LOG_DIR" -name "tool-governance*.log" -mtime +90 -delete 2>/dev/null
find "$PROJECT_ROOT/_kb/audits" -name "*.md" -mtime +90 -delete 2>/dev/null

log "=========================================="
log "Tool Governance Weekly Audit - COMPLETE"
log "=========================================="

exit 0
