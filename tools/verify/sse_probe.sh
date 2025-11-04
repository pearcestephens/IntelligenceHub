#!/usr/bin/env bash
# Probe the SSE endpoint with an admin token; print first two events
# Usage: sse_probe.sh <token>
set -euo pipefail
TOKEN=${1:-}
if [[ -z "$TOKEN" ]]; then
  echo "Usage: $0 <token>" >&2
  exit 1
fi
curl -sS -N -H "X-Admin-Token: $TOKEN" "https://staff.vapeshed.co.nz/admin/traffic/live.php" | sed -n '1,20p'
