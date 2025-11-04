#!/usr/bin/env bash
# Summarize recent access logs (Apache + Nginx) for status codes and top offenders.
# Usage: access_summary.sh [lines]
set -euo pipefail
LINES=${1:-5000}
ROOT_DIR="$(cd "$(dirname "$0")/../../.." && pwd)"
LOG_DIR="$ROOT_DIR/logs"

sum_codes() {
  local file="$1"; local lines="$2"
  if [[ -r "$file" ]]; then
    echo "== $file (last $lines lines) =="
    tail -n "$lines" "$file" \
      | awk '{ if (match($0, /"[^"]*" ([0-9]{3}) /, m)) { c[m[1]]++ } } END { for (k in c) printf "%s %d\n", k, c[k] | "sort -n" }' \
      | awk '{sum+=$2; out[NR]=$0} END { for(i=1;i<=NR;i++){ split(out[i],f," "); printf("%s: %d (%.1f%%)\n", f[1], f[2], (f[2]*100)/sum) } }'
    echo
  fi
}

top_404() {
  local file="$1"; local lines="$2"; local top=${3:-20}
  if [[ -r "$file" ]]; then
    echo "== Top ${top} 404 URLs in $file (last $lines lines) =="
    tail -n "$lines" "$file" \
      | awk -v limit=$top '{
          if (match($0, /"([A-Z]+) ([^ ]+) HTTP\/[0-9.]+" ([0-9]{3}) /, m)) {
            method=m[1]; path=m[2]; code=m[3]; if (code==404) p[path]++
          }
        } END { for (k in p) printf "%7d %s\n", p[k], k | "sort -rn | head -n " limit }'
    echo
  fi
}

sum_codes "$LOG_DIR/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log" "$LINES"
sum_codes "$LOG_DIR/apache_phpstack-129337-5615757.cloudwaysapps.com.access.log" "$LINES" || true

top_404 "$LOG_DIR/nginx_phpstack-129337-5615757.cloudwaysapps.com.access.log" "$LINES" 20
