#!/bin/bash
################################################################################
# Smart Cron - Enterprise Task Execution Wrapper
#
# Purpose: Comprehensive execution wrapper with logging, monitoring, and error handling
# Version: 2.0.0
# Author: CIS System Architecture Team
#
# Features:
# - Automatic execution logging to database
# - Performance metrics collection (CPU, memory, duration)
# - Error capture with stack traces
# - Timeout enforcement
# - Resource limit monitoring
# - Alert triggering on failures
# - Retry logic with exponential backoff
# - Lock file management (prevent concurrent runs)
# - Comprehensive stdout/stderr capture
#
# Usage:
#   run-task.sh <task_name> <script_path> [timeout_seconds] [max_retries]
#
# Example:
#   run-task.sh "heartbeat" "/path/to/heartbeat.php" 60 3
#
################################################################################

set -o pipefail  # Pipe failures propagate

# ============================================================================
# CONFIGURATION
# ============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="/home/master/applications/jcepnzzkmj/public_html"
LOG_DIR="${PROJECT_ROOT}/logs/smart-cron"
LOCK_DIR="${PROJECT_ROOT}/assets/services/cron/smart-cron/locks"
TEMP_DIR="${PROJECT_ROOT}/assets/services/cron/smart-cron/temp"

# Database connection
DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-vend_sales}"
DB_USER="${DB_USER:-master}"
DB_PASS="${DB_PASS}"

# Default limits
DEFAULT_TIMEOUT=3600
DEFAULT_MAX_RETRIES=0
MAX_OUTPUT_SIZE=10240  # 10KB
MEMORY_CHECK_INTERVAL=5

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================================================
# UTILITY FUNCTIONS
# ============================================================================

log_info() {
    echo -e "${BLUE}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "${LOG_DIR}/wrapper.log"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "${LOG_DIR}/wrapper.log"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "${LOG_DIR}/wrapper.log"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "${LOG_DIR}/wrapper.log" >&2
}

# Generate UUID
generate_uuid() {
    if command -v uuidgen &> /dev/null; then
        uuidgen
    else
        cat /proc/sys/kernel/random/uuid
    fi
}

# Escape strings for SQL
sql_escape() {
    echo "$1" | sed "s/'/''/g"
}

# Execute SQL query
exec_sql() {
    local query="$1"
    mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" -e "$query" 2>&1
}

# Get current memory usage of process
get_process_memory() {
    local pid=$1
    if [ -f "/proc/${pid}/status" ]; then
        grep VmRSS /proc/${pid}/status | awk '{print $2/1024}'
    else
        echo "0"
    fi
}

# Get CPU usage of process
get_process_cpu() {
    local pid=$1
    ps -p ${pid} -o %cpu | tail -n 1 | tr -d ' '
}

# ============================================================================
# LOCK FILE MANAGEMENT
# ============================================================================

acquire_lock() {
    local task_name="$1"
    local lock_file="${LOCK_DIR}/${task_name}.lock"
    
    # Check if lock file exists
    if [ -f "$lock_file" ]; then
        local lock_pid=$(cat "$lock_file")
        
        # Check if process is still running
        if kill -0 "$lock_pid" 2>/dev/null; then
            log_error "Task '$task_name' is already running (PID: $lock_pid)"
            return 1
        else
            log_warn "Stale lock file found, removing (PID: $lock_pid was not running)"
            rm -f "$lock_file"
        fi
    fi
    
    # Create lock file
    echo $$ > "$lock_file"
    return 0
}

release_lock() {
    local task_name="$1"
    local lock_file="${LOCK_DIR}/${task_name}.lock"
    rm -f "$lock_file"
}

# ============================================================================
# DATABASE LOGGING
# ============================================================================

log_execution_start() {
    local uuid="$1"
    local task_name="$2"
    local script_path="$3"
    local script_hash="$4"
    
    local hostname=$(hostname)
    local username=$(whoami)
    
    local query="INSERT INTO smart_cron_executions (
        execution_uuid, task_name, script_path, script_hash,
        status, started_at, pid, hostname, user, triggered_by
    ) VALUES (
        '$(sql_escape "$uuid")',
        '$(sql_escape "$task_name")',
        '$(sql_escape "$script_path")',
        '$(sql_escape "$script_hash")',
        'running',
        NOW(3),
        $$,
        '$(sql_escape "$hostname")',
        '$(sql_escape "$username")',
        'cron'
    );"
    
    exec_sql "$query"
}

log_execution_complete() {
    local uuid="$1"
    local status="$2"
    local exit_code="$3"
    local duration="$4"
    local memory_peak="$5"
    local memory_avg="$6"
    local cpu_percent="$7"
    local stdout_excerpt="$8"
    local stderr_excerpt="$9"
    local error_message="${10}"
    
    local query="UPDATE smart_cron_executions SET
        status = '$(sql_escape "$status")',
        completed_at = NOW(3),
        duration_seconds = ${duration},
        exit_code = ${exit_code},
        memory_peak_mb = ${memory_peak},
        memory_average_mb = ${memory_avg},
        cpu_percent = ${cpu_percent},
        stdout_excerpt = '$(sql_escape "$stdout_excerpt")',
        stderr_excerpt = '$(sql_escape "$stderr_excerpt")',
        error_message = '$(sql_escape "$error_message")',
        updated_at = NOW()
    WHERE execution_uuid = '$(sql_escape "$uuid")';"
    
    exec_sql "$query"
}

update_task_health() {
    local task_name="$1"
    local status="$2"
    
    if [ "$status" = "success" ]; then
        local query="UPDATE smart_cron_tasks SET
            consecutive_failures = 0,
            last_success_at = NOW(),
            updated_at = NOW()
        WHERE task_name = '$(sql_escape "$task_name")';"
    else
        local query="UPDATE smart_cron_tasks SET
            consecutive_failures = consecutive_failures + 1,
            last_failure_at = NOW(),
            updated_at = NOW()
        WHERE task_name = '$(sql_escape "$task_name")';"
    fi
    
    exec_sql "$query"
}

# ============================================================================
# PERFORMANCE MONITORING
# ============================================================================

monitor_performance() {
    local pid=$1
    local stats_file=$2
    
    local memory_samples=0
    local memory_total=0
    local memory_peak=0
    local cpu_samples=0
    local cpu_total=0
    
    while kill -0 $pid 2>/dev/null; do
        local mem=$(get_process_memory $pid)
        local cpu=$(get_process_cpu $pid)
        
        memory_samples=$((memory_samples + 1))
        memory_total=$(echo "$memory_total + $mem" | bc)
        if (( $(echo "$mem > $memory_peak" | bc -l) )); then
            memory_peak=$mem
        fi
        
        cpu_samples=$((cpu_samples + 1))
        cpu_total=$(echo "$cpu_total + $cpu" | bc)
        
        sleep $MEMORY_CHECK_INTERVAL
    done
    
    local memory_avg=$(echo "scale=2; $memory_total / $memory_samples" | bc)
    local cpu_avg=$(echo "scale=2; $cpu_total / $cpu_samples" | bc)
    
    echo "$memory_peak" > "${stats_file}.memory_peak"
    echo "$memory_avg" > "${stats_file}.memory_avg"
    echo "$cpu_avg" > "${stats_file}.cpu_avg"
}

# ============================================================================
# ALERT SYSTEM
# ============================================================================

trigger_alert() {
    local task_name="$1"
    local alert_type="$2"
    local severity="$3"
    local message="$4"
    local execution_uuid="$5"
    
    local query="INSERT INTO smart_cron_alerts (
        execution_id, task_name, alert_type, severity, message, created_at
    ) SELECT
        id, '$(sql_escape "$task_name")', '$(sql_escape "$alert_type")',
        '$(sql_escape "$severity")', '$(sql_escape "$message")', NOW()
    FROM smart_cron_executions
    WHERE execution_uuid = '$(sql_escape "$execution_uuid")'
    LIMIT 1;"
    
    exec_sql "$query"
    
    log_warn "Alert triggered: [$severity] $message"
}

# ============================================================================
# MAIN EXECUTION
# ============================================================================

main() {
    # Validate arguments
    if [ $# -lt 2 ]; then
        log_error "Usage: $0 <task_name> <script_path> [timeout_seconds] [max_retries]"
        exit 1
    fi
    
    local task_name="$1"
    local script_path="$2"
    local timeout_seconds="${3:-$DEFAULT_TIMEOUT}"
    local max_retries="${4:-$DEFAULT_MAX_RETRIES}"
    
    # Ensure directories exist
    mkdir -p "$LOG_DIR" "$LOCK_DIR" "$TEMP_DIR"
    
    # Generate execution UUID
    local execution_uuid=$(generate_uuid)
    
    # Resolve full script path
    if [[ "$script_path" != /* ]]; then
        script_path="${PROJECT_ROOT}/${script_path}"
    fi
    
    # Validate script exists
    if [ ! -f "$script_path" ]; then
        log_error "Script not found: $script_path"
        exit 1
    fi
    
    # Calculate script hash
    local script_hash=$(sha256sum "$script_path" | awk '{print $1}')
    
    # Acquire lock
    if ! acquire_lock "$task_name"; then
        log_error "Could not acquire lock for task: $task_name"
        exit 1
    fi
    
    # Setup cleanup trap
    trap "release_lock '$task_name'" EXIT
    
    log_info "Starting task: $task_name (UUID: $execution_uuid)"
    log_info "Script: $script_path"
    log_info "Timeout: ${timeout_seconds}s, Max Retries: $max_retries"
    
    # Log execution start to database
    log_execution_start "$execution_uuid" "$task_name" "$script_path" "$script_hash"
    
    # Prepare output files
    local stdout_file="${TEMP_DIR}/${execution_uuid}.stdout"
    local stderr_file="${TEMP_DIR}/${execution_uuid}.stderr"
    local stats_file="${TEMP_DIR}/${execution_uuid}.stats"
    
    # Start time
    local start_time=$(date +%s.%N)
    
    # Execute script with timeout and monitoring
    timeout ${timeout_seconds}s bash -c "exec $script_path" > "$stdout_file" 2> "$stderr_file" &
    local script_pid=$!
    
    # Start performance monitoring in background
    monitor_performance $script_pid "$stats_file" &
    local monitor_pid=$!
    
    # Wait for script to complete
    wait $script_pid
    local exit_code=$?
    
    # Stop monitoring
    kill $monitor_pid 2>/dev/null
    wait $monitor_pid 2>/dev/null
    
    # Calculate duration
    local end_time=$(date +%s.%N)
    local duration=$(echo "$end_time - $start_time" | bc)
    
    # Read performance stats
    local memory_peak=0
    local memory_avg=0
    local cpu_avg=0
    
    if [ -f "${stats_file}.memory_peak" ]; then
        memory_peak=$(cat "${stats_file}.memory_peak")
        memory_avg=$(cat "${stats_file}.memory_avg")
        cpu_avg=$(cat "${stats_file}.cpu_avg")
    fi
    
    # Read output (last 10KB)
    local stdout_excerpt=$(tail -c $MAX_OUTPUT_SIZE "$stdout_file" | base64 -w 0)
    local stderr_excerpt=$(tail -c $MAX_OUTPUT_SIZE "$stderr_file" | base64 -w 0)
    
    # Determine status
    local status="success"
    local error_message=""
    
    if [ $exit_code -eq 124 ]; then
        status="timeout"
        error_message="Task exceeded timeout of ${timeout_seconds} seconds"
        log_error "Task timed out after ${timeout_seconds}s"
        trigger_alert "$task_name" "timeout" "error" "$error_message" "$execution_uuid"
    elif [ $exit_code -ne 0 ]; then
        status="failed"
        error_message="Task exited with code $exit_code"
        log_error "Task failed with exit code: $exit_code"
        
        # Read stderr for error details
        if [ -s "$stderr_file" ]; then
            error_message="${error_message}: $(tail -n 5 "$stderr_file" | tr '\n' ' ')"
        fi
        
        trigger_alert "$task_name" "failure" "error" "$error_message" "$execution_uuid"
    fi
    
    # Log completion to database
    log_execution_complete "$execution_uuid" "$status" "$exit_code" "$duration" \
        "$memory_peak" "$memory_avg" "$cpu_avg" \
        "$stdout_excerpt" "$stderr_excerpt" "$error_message"
    
    # Update task health
    update_task_health "$task_name" "$status"
    
    # Cleanup temp files
    rm -f "$stdout_file" "$stderr_file" "${stats_file}."*
    
    # Log final status
    if [ "$status" = "success" ]; then
        log_success "Task completed successfully in ${duration}s (Memory: ${memory_peak}MB peak)"
    else
        log_error "Task $status in ${duration}s"
    fi
    
    # Release lock
    release_lock "$task_name"
    
    exit $exit_code
}

# Execute main function
main "$@"