# Redirects Playbook (404/SEO/Housekeeping)

Use `public_html/tools/verify/error_offenders.sh 15000` to list top 404s and then add rules below.

## Patterns
- Favicon/noise
  - `location = /favicon.ico { return 204; }`
- Old asset paths
  - `location = /old.css { return 301 https://staff.vapeshed.co.nz/assets/app.css; }`
- Stray slugs
  - `location = /old-page { return 301 https://staff.vapeshed.co.nz/new-page; }`

## Steps
1. Run offenders analyzer and confirm persistent 404 paths (not transient bots).
2. Add explicit `location = /path { return 301 ...; }` rules to `conf/includes/redirects.conf`.
3. Reload Nginx via Cloudways panel; verify with:
   ```bash
   bash public_html/tools/verify/headers_check.sh https://staff.vapeshed.co.nz/old-page
   ```
4. Re-run offenders analyzer to confirm drop.
