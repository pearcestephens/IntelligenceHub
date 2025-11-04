# Performance Rollout Checklist

Use this checklist to enable quick wins safely, validate with scripts, and roll back if needed.

## Pre-checks
- Take a server snapshot (Cloudways)
- Ensure you can tail logs: `bash public_html/tools/quick_dial/apache_tail.sh 200`
- Health check: `bash public_html/tools/verify/url_check.sh`

## Step 1 — Enable OPcache
- Apply settings from SERVER_TUNING_SNIPPETS.md
- Verify:
  - `phpinfo()` (admin only) shows OPcache enabled
  - Response time improvement on hot endpoints (optional curl timings)

## Step 2 — Adjust PHP-FPM pool
- Apply pool values from SERVER_TUNING_SNIPPETS.md
- Monitor for 5 minutes:
  - `tail -f logs/nginx-app.status.log` (if useful)
  - Errors: `bash public_html/tools/quick_dial/apache_tail.sh 200`

## Step 3 — Enable gzip/brotli
- Toggle in Cloudways Nginx settings
- Verify headers:
  ```bash
  bash public_html/tools/verify/encoding_check.sh https://staff.vapeshed.co.nz
  ```

## Step 4 — Static cache headers
- Add Nginx include or panel rules
- Verify:
  ```bash
  bash public_html/tools/verify/headers_check.sh https://staff.vapeshed.co.nz/public_html/assets/app.css
  ```
  Expect `Cache-Control: public, max-age=2592000` and possibly `immutable`.

## Step 5 — Clean up hot 404s
- Analyze:
  ```bash
  bash public_html/tools/verify/access_summary.sh 10000
  bash public_html/tools/verify/error_offenders.sh 10000
  ```
- Add redirects for top offenders at Nginx or app level; re-run analyzer to confirm drop.

## Optional — Micro-cache
- Apply snippet from SERVER_TUNING_SNIPPETS.md
- Verify with double request timing; ensure POST bypass is respected.

## Rollback
- Revert the panel toggles or restore the server snapshot.

## Success Criteria
- Error rate does not increase; 4xx/5xx stable or lower.
- p95 latency improves on hot endpoints.
- Bandwidth reduced (Content-Encoding active).
- Subsequent `access_summary.sh` shows fewer 404s after redirect mapping.
