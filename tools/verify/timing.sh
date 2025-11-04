#!/usr/bin/env bash
# Simple timing harness for a URL: TTFB, total time, size. Shows best/median/worst.
# Usage: timing.sh <url> [count]
set -euo pipefail
url=${1:-}
count=${2:-5}
if [[ -z "$url" ]]; then
  echo "Usage: $0 <url> [count]" >&2
  exit 1
fi
printf "Testing %s x%s\n" "$url" "$count"
TTFB=()
TOTAL=()
for i in $(seq 1 "$count"); do
  read -r ttfb total size <<<"$(curl -sS -o /dev/null -w '%{time_starttransfer} %{time_total} %{size_download}' "$url")"
  TTFB+=("$ttfb"); TOTAL+=("$total")
  printf "#%d TTFB=%.3fs Total=%.3fs Size=%s\n" "$i" "$ttfb" "$total" "$size"
  sleep 0.3
done
sort_and_pick() { printf "%s\n" "$@" | sort -n; }
median() { arr=($(sort_and_pick "$@")); echo "${arr[$((${#arr[@]} / 2))]}"; }
best() { printf "%s\n" "$@" | sort -n | head -n1; }
worst() { printf "%s\n" "$@" | sort -n | tail -n1; }
printf "TTFB best/median/worst: %s / %s / %s\n" "$(best "${TTFB[@]}")" "$(median "${TTFB[@]}")" "$(worst "${TTFB[@]}")"
printf "Total best/median/worst: %s / %s / %s\n" "$(best "${TOTAL[@]}")" "$(median "${TOTAL[@]}")" "$(worst "${TOTAL[@]}")"
