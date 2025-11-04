#!/usr/bin/env bash
# Safe Apache error log tail + snapshot
set -euo pipefail

LINES=${1:-200}
LOG_PATH="/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log.1"
SNAP_DIR="/var/log/cis/snapshots"

mkdir -p "$SNAP_DIR"
STAMP=$(date +%Y%m%d-%H%M%S)
SNAP_FILE="$SNAP_DIR/apache-error-${STAMP}.log.gz"

if [[ -r "$LOG_PATH" ]]; then
  tail -n "$LINES" "$LOG_PATH" | tee >(gzip -c > "$SNAP_FILE")
  echo "\nSnapshot saved: $SNAP_FILE" >&2
else
  echo "Log file not readable: $LOG_PATH" >&2
  exit 1
fi
