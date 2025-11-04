#!/usr/bin/env bash
# List top offenders by status (403/404/415/5xx) with paths and IPs
# Usage: error_offenders.sh [lines]
set -euo pipefail
LINES=${1:-10000}
ROOT_DIR="$(cd "$(dirname "$0")/../../.." && pwd)"
LOG="$ROOT_DIR/logs/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log"

if [[ ! -r "$LOG" ]]; then
  echo "Access log not readable: $LOG" >&2
  exit 1
fi

show_top_by_status() {
  local code_pat="$1"; local label="$2"
  echo "== Top 20 $label paths (last $LINES lines) =="
  tail -n "$LINES" "$LOG" \
    | awk -v pat="$code_pat" -F'"' '{
        if (match($0, /"([A-Z]+) ([^ ]+) HTTP\/[0-9.]+" ([0-9]{3}) /, m)) {
          path=m[2]; code=m[3]; if (code ~ pat) c[path]++
        }
      } END { for (k in c) printf "%7d %s\n", c[k], k | "sort -rn | head -n 20" }'
  echo
  echo "== Top 20 $label IPs (last $LINES lines) =="
  tail -n "$LINES" "$LOG" \
    | awk -v pat="$code_pat" '{ ip=$1; if (match($0, /"[^"]+" ([0-9]{3}) /, m)) { if (m[1] ~ pat) c[ip]++ } } END { for (k in c) printf "%7d %s\n", c[k], k | "sort -rn | head -n 20" }'
  echo
}

show_top_by_status "^(403)$" "403"
show_top_by_status "^(404)$" "404"
show_top_by_status "^(415)$" "415"
show_top_by_status "^5..$" "5xx"
