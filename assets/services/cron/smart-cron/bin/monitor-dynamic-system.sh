#!/bin/bash
# Real-time monitoring of dynamic resource management
# Shows system state, strategy, and execution slots

WATCH_SECONDS=${1:-5}

echo "🔍 Dynamic Resource Management Monitor"
echo "========================================"
echo "Updating every ${WATCH_SECONDS} seconds (Ctrl+C to exit)"
echo ""

while true; do
    clear
    echo "🕐 $(date '+%Y-%m-%d %H:%M:%S')"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    
    # System Memory
    echo "💾 SYSTEM MEMORY"
    free -h | head -2
    echo ""
    
    # VSCode Memory
    echo "🖥️  VSCODE MEMORY"
    vscode_total=$(ps aux | grep -E "extensionHost|vscode" | grep -v grep | awk '{sum+=$6} END {print sum/1024}')
    vscode_count=$(ps aux | grep -E "extensionHost|vscode" | grep -v grep | wc -l)
    printf "  Total: %.0f MB (%d processes)\n" "$vscode_total" "$vscode_count"
    echo ""
    
    # Current Strategy
    echo "🎯 CURRENT STRATEGY"
    if [ -f logs/dynamic-resources.log ]; then
        tail -1 logs/dynamic-resources.log 2>/dev/null || echo "  (waiting for first adjustment...)"
    else
        echo "  (waiting for first adjustment...)"
    fi
    echo ""
    
    # Execution Slots
    echo "⚙️  EXECUTION SLOTS"
    mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -s -N -e "
        SELECT CONCAT(
            '  ', RPAD(slot_name, 10), 
            ': ', max_concurrent_jobs, ' jobs, ',
            max_total_memory_mb, ' MB, ',
            max_cpu_percent, '% CPU'
        )
        FROM smart_cron_execution_slots 
        ORDER BY max_total_memory_mb DESC
    " 2>/dev/null
    echo ""
    
    # Running Jobs
    echo "🏃 RUNNING JOBS"
    job_count=$(mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -s -N -e "
        SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE status = 'running'
    " 2>/dev/null)
    echo "  Active: $job_count jobs"
    echo ""
    
    # Recent Adjustments
    echo "📊 RECENT ADJUSTMENTS (last 5)"
    if [ -f logs/dynamic-adjuster.log ]; then
        grep "Strategy:" logs/dynamic-adjuster.log | tail -5 | sed 's/^/  /'
    else
        echo "  (no adjustments yet)"
    fi
    
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Refresh in ${WATCH_SECONDS}s... (Ctrl+C to exit)"
    
    sleep "$WATCH_SECONDS"
done
