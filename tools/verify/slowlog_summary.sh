#!/usr/bin/env bash
# Summarize php slow log entries (recent rolled files) with counts by script and duration buckets.
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "$0")/../../.." && pwd)"
LOG_DIR="$ROOT_DIR/logs"

files=(
  "$LOG_DIR/php-app.slow.log"
  "$LOG_DIR/php-app.slow.log.1"
)

echo "Scanning PHP slow logs..."
for f in "${files[@]}"; do
  [[ -r "$f" ]] || continue
  echo "== $f =="
  # Count by script file
  awk '/script_filename/ {gsub("script_filename=", ""); split($0,a," "); printf "%s\n", a[1] }' "$f" | sort | uniq -c | sort -rn | head -n 20
  # Duration buckets (if present as "duration: X.YYY")
  awk '/^\[.*\] / { if (match($0, /duration: ([0-9.]+)/, m)) {d=m[1]; if (d<0.3) b="<300ms"; else if (d<1) b="300ms-1s"; else if (d<3) b="1-3s"; else b=">=3s"; c[b]++ } } END { for (k in c) printf "%s %d\n", k, c[k] | "sort" }' "$f"
  echo
done
