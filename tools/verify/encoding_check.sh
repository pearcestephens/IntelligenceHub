#!/usr/bin/env bash
# Check Content-Encoding and Cache-Control for a list of URLs
# Usage: encoding_check.sh <url1> [url2 ...]
set -euo pipefail
if [[ $# -eq 0 ]]; then
  echo "Usage: $0 <url1> [url2 ...]" >&2
  exit 1
fi
for u in "$@"; do
  echo "== $u =="
  curl -sSI -H "Accept-Encoding: br,gzip" "$u" | awk 'BEGIN{IGNORECASE=1} /^HTTP\//{print $0} /content-encoding|cache-control|content-type|age|expires/{print $0}'
  echo
done
