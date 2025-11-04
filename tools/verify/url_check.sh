#!/usr/bin/env bash
# URL Verification Suite
set -euo pipefail

TARGET_BASE="https://staff.vapeshed.co.nz"

check() {
  local url="$1"
  local expect="$2"
  local code
  code=$(curl -fsS -o /dev/null -w "%{http_code}" "$url" || true)
  printf "%s -> %s (expected %s)\n" "$url" "$code" "$expect"
}

# Basic reachability
check "$TARGET_BASE" "200|301|302"

# Admin endpoints (should be 401/403 unauthenticated until Phase 1 guards)
check "$TARGET_BASE/?endpoint=admin/health/ping" "200|401|403"
check "$TARGET_BASE/?endpoint=admin/health/checks" "200|401|403"

exit 0
