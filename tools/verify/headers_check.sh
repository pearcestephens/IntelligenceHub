#!/usr/bin/env bash
# Show key headers for a URL (debug convenience)
# Usage: headers_check.sh <url>
set -euo pipefail
url=${1:-}
if [[ -z "$url" ]]; then
  echo "Usage: $0 <url>" >&2
  exit 1
fi
curl -sSI "$url" | awk 'BEGIN{IGNORECASE=1} /^HTTP\//{print $0} /cache-control|expires|etag|last-modified|vary|content-length|content-type/{print $0}'
