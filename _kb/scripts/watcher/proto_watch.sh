#!/bin/bash
##############################################################################
# File Watcher Prototype - Incremental KB Analysis
# 
# Uses inotifywait to monitor PHP file changes and trigger incremental
# intelligence analysis on modified files only.
# 
# Features:
# - Watch specific directories for PHP changes
# - Debounce rapid changes (wait for edit to complete)
# - Trigger appropriate analysis based on file type
# - Log all triggered analyses
# - Dry-run mode for testing
# 
# Usage:
#   ./proto_watch.sh                    # Start watching (dry-run)
#   ./proto_watch.sh --live             # Actually run analysis
#   ./proto_watch.sh --dir=/path        # Watch specific directory
# 
# @version 1.0.0
##############################################################################

set -euo pipefail

# Configuration
WATCH_DIR="${1:-/home/master/applications/hdgwrzntwa/public_html}"
KB_ROOT="/home/master/applications/hdgwrzntwa/public_html/_kb"
LOG_FILE="${KB_ROOT}/logs/watcher.log"
DRY_RUN=true
DEBOUNCE_SECONDS=2

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --live)
            DRY_RUN=false
            shift
            ;;
        --dir=*)
            WATCH_DIR="${1#*=}"
            shift
            ;;
        --debounce=*)
            DEBOUNCE_SECONDS="${1#*=}"
            shift
            ;;
        *)
            WATCH_DIR="$1"
            shift
            ;;
    esac
done

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    case $level in
        INFO)
            echo -e "${BLUE}[INFO]${NC} ${message}"
            ;;
        SUCCESS)
            echo -e "${GREEN}[SUCCESS]${NC} ${message}"
            ;;
        WARNING)
            echo -e "${YELLOW}[WARNING]${NC} ${message}"
            ;;
        ERROR)
            echo -e "${RED}[ERROR]${NC} ${message}"
            ;;
    esac
    
    # Also log to file
    echo "[${timestamp}] [${level}] ${message}" >> "${LOG_FILE}"
}

# Check if inotifywait is available
check_dependencies() {
    if ! command -v inotifywait &> /dev/null; then
        log ERROR "inotifywait not found. Installing inotify-tools..."
        
        # Try to install (may require sudo)
        if command -v apt-get &> /dev/null; then
            log INFO "Attempting to install via apt-get..."
            sudo apt-get update && sudo apt-get install -y inotify-tools
        elif command -v yum &> /dev/null; then
            log INFO "Attempting to install via yum..."
            sudo yum install -y inotify-tools
        else
            log ERROR "Cannot install inotify-tools automatically. Please install manually."
            exit 1
        fi
    fi
}

# Analyze changed file
analyze_file() {
    local file="$1"
    local event="$2"
    
    log INFO "Change detected: ${event} on ${file}"
    
    # Skip if file is in excluded directories
    if [[ "$file" =~ (vendor|node_modules|cache|logs|tmp|backup|test) ]]; then
        log INFO "Skipping excluded path: ${file}"
        return
    fi
    
    # Debounce - wait for rapid changes to settle
    sleep "${DEBOUNCE_SECONDS}"
    
    # Check if file still exists (might have been deleted)
    if [[ ! -f "$file" ]]; then
        log WARNING "File no longer exists: ${file}"
        return
    fi
    
    if [[ "$DRY_RUN" == true ]]; then
        log WARNING "DRY-RUN: Would analyze ${file}"
        log WARNING "DRY-RUN: Would run: php ${KB_ROOT}/scripts/analyze_single_file.php ${file}"
        return
    fi
    
    # Determine analysis type based on file location/name
    local analysis_cmd=""
    
    if [[ "$file" =~ api/ ]]; then
        analysis_cmd="php ${KB_ROOT}/scripts/analyze_api_file.php \"${file}\""
    elif [[ "$file" =~ (security|auth|login) ]]; then
        analysis_cmd="php ${KB_ROOT}/scripts/ast_security_scanner.php -d \"$(dirname ${file})\""
    else
        # Generic analysis
        analysis_cmd="php ${KB_ROOT}/scripts/analyze_single_file.php \"${file}\""
    fi
    
    log INFO "Running: ${analysis_cmd}"
    
    # Run analysis and capture output
    if eval "${analysis_cmd}" >> "${LOG_FILE}" 2>&1; then
        log SUCCESS "Analysis complete for: ${file}"
    else
        log ERROR "Analysis failed for: ${file}"
    fi
}

# Main watch loop
watch_files() {
    log INFO "Starting file watcher..."
    log INFO "Watch directory: ${WATCH_DIR}"
    log INFO "Dry-run mode: ${DRY_RUN}"
    log INFO "Debounce: ${DEBOUNCE_SECONDS}s"
    log INFO "Log file: ${LOG_FILE}"
    echo ""
    log INFO "Watching for PHP file changes... (Ctrl+C to stop)"
    echo ""
    
    # Use inotifywait in monitor mode
    # Events: modify, close_write, move, create, delete
    inotifywait -m -r \
        --exclude '(vendor|node_modules|cache|logs|tmp|backup|\.git)' \
        --format '%w%f %e' \
        -e modify -e close_write -e moved_to -e create \
        "${WATCH_DIR}" \
        --include '\.php$' 2>/dev/null | while read file event; do
        
        # Run in background to not block watcher
        analyze_file "$file" "$event" &
    done
}

# Signal handler for clean shutdown
cleanup() {
    log INFO "Shutting down file watcher..."
    # Kill any background analysis jobs
    jobs -p | xargs -r kill 2>/dev/null
    exit 0
}

trap cleanup SIGINT SIGTERM

# Main execution
main() {
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║          KB File Watcher - Incremental Analysis               ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo ""
    
    # Create log directory if needed
    mkdir -p "$(dirname ${LOG_FILE})"
    
    # Check dependencies
    check_dependencies
    
    # Start watching
    watch_files
}

main
