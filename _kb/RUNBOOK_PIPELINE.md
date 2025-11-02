# CIS Intelligence Pipeline Runbook

Purpose: provide an exact, safe, ordered plan to run the CIS intelligence pipeline (ingest → scan → post-process) and to install the optimized cron schedule. Use this as the canonical checklist for maintenance windows.

Prerequisites (must be satisfied before any run)
- SSH access to server as deploy user (not root). Work from project root: /home/master/applications/hdgwrzntwa/public_html
- PHP CLI available (php -v >= 8.x)
- PDO MySQL extension enabled
- Composer dependencies installed if required (composer install)
- .env present and valid in project root or environment variables set
- CREDENTIAL_ENCRYPTION_KEY set and matching vault entries in table `mcp_secure_credentials`
- Backups: database and important files snapshot created (see Backup section)

Quick safety checklist
- Put site into maintenance mode (if public-facing) or inform stakeholders
- Ensure DB backups exist: `backup-database.php` or snapshot
- Confirm low-traffic window

High-level ordered steps
1) Prepare environment & verify credentials
2) Ingest projects (populate `intelligence_files`)
3) Run V3 QuickScan (scanner v3 / QuickScanService)
4) (Optional) Run v2 intelligence engine (legacy) if still required
5) Post-processing: optimize DB, cleanup, backups
6) Install optimized cron schedule (optional) and verify
7) Smoke tests and verification

Detailed steps and commands

1) Prepare environment & verify credentials
- Verify PHP CLI and paths
  - php -v
  - which php
- Ensure `.env` exists and contains values (copy from `.env.example` if needed)
  - Required variables: DB_HOST, DB_NAME, DB_USER, DB_PASS (for primary), CREDENTIAL_ENCRYPTION_KEY
- Verify vault entries are present using PasswordStorageTool (console or DB query). Example SQL (read-only):
  - SELECT id, label FROM mcp_secure_credentials LIMIT 50;
- Verify `mcp/src/Database/Connection.php` is returning a working PDO. Quick lint:
  - php -l mcp/src/Database/Connection.php

2) Ingest projects
- Script: `public_html/scripts/refresh-intelligence.php` (this is the canonical ingestion + v3 example)
- Dry-run first (if script supports it) or run with logging to a file
  - # Dry-run: (no-op) set DRY_RUN=1 to environment if script honours it
  - DRY_RUN=1 php scripts/refresh-intelligence.php --project=123 >> logs/refresh_dry.log 2>&1
- To ingest all projects carefully (batch):
  - php scripts/refresh-intelligence.php --all >> logs/refresh_all.log 2>&1
- Verify new rows inserted into `intelligence_files` and that UNIQUE constraint is respected
  - SELECT COUNT(*) FROM intelligence_files WHERE created_at >= NOW() - INTERVAL 1 HOUR;

3) Run V3 QuickScan (QuickScanService)
- Script(s): `scripts/safe_neural_scanner.php`, `scripts/refresh-intelligence.php` will call QuickScanService
- Run safe scanner first (smaller batch):
  - php scripts/safe_neural_scanner.php --limit=100 >> logs/neural_scan_safe.log 2>&1
- Then run full quick-scan loop (monitor logs):
  - php scripts/safe_neural_scanner.php --limit=1000 >> logs/neural_scan_full.log 2>&1
- Watch progress and slowdowns (tail logs):
  - tail -F logs/neural_scan.log

4) (Optional) Run V2 intelligence engine
- If your environment still needs the legacy V2 results, run the V2 engine after V3 ingestion/scans
  - php scripts/kb_intelligence_engine_v2.php --full >> logs/kb_intelligence_v2.log 2>&1
- Note: V2 can be heavier; schedule accordingly and monitor DB locks

5) Post-processing: optimize DB, cleanup, backups
- Run database optimize script
  - php scripts/optimize-database.php >> logs/optimize_db.log 2>&1
- Cleanup temp logs and run backups
  - php scripts/backup-database.php >> logs/backup_db.log 2>&1
  - php scripts/cleanup-logs.php >> logs/cleanup_logs.log 2>&1

6) Install optimized cron schedule (optional)
- Preview the optimized crontab (script creates temp file and shows count)
  - bash _kb/scripts/install_optimized_crons.sh
- If satisfied, answer 'yes' when prompted to install

7) Smoke tests and verification
- Check recent error logs (look for PHP fatal or DB errors)
  - grep -i "fatal\|error" logs/*.log | tail -n 200
- Verify key counts and staleness
  - SELECT COUNT(*) FROM intelligence_files;
  - SELECT COUNT(*) FROM intelligence_files WHERE processed = 0;
- Verify web endpoints (if changed): run `/admin/health/ping` endpoints (auth required)

Acceptance criteria (success)
- No duplicate rows reintroduced (UNIQUE constraint holds)
- QuickScanService processed recent ingests (logs show progress and completion)
- DB remains responsive (no long-running locks from DELETE/INSERT)
- Cron installer succeeds and crontab contains the new jobs

Rollback plan
- If an operation causes issues, steps:
  1. Re-import DB backup (mysqldump restore) taken at start
  2. Revert to previous crontab backup: crontab /path/to/backup.txt
  3. Rollback modified files via git (git checkout -- file)

Monitoring & post-run checks
- Monitor logs for 30 minutes after run (tail -F logs/*.log)
- Monitor DB slow queries: check php-app.slow.log and mysql slow log
- Check server CPU/memory for sustained high load

Next steps & recommended cleanups after successful run
1. Replace all remaining hardcoded DB connections (search for `new PDO("mysql:` or literal DB_* env use) with `mcp/src/Database/Connection.php` factory.
2. Update insertion points into `intelligence_files` to use INSERT IGNORE or ON DUPLICATE KEY UPDATE to gracefully handle unique constraint.
3. Add a monitoring alert (Sentry/Grafana) for failure spikes and job failures.
4. Add a dry-run flag to heavy scripts if missing (safe_neural_scanner, kb_intelligence_engine_v2, refresh-intelligence).

Files referenced (exact locations)
- /public_html/scripts/refresh-intelligence.php  (ingest + quick-scan orchestration)
- /public_html/scripts/safe_neural_scanner.php   (neural scanner runner)
- /public_html/scripts/kb_intelligence_engine_v2.php (legacy engine)
- /public_html/_kb/scripts/install_optimized_crons.sh (cron installer)
- /public_html/scripts/optimize-database.php
- /public_html/scripts/backup-database.php
- /public_html/scripts/cleanup-logs.php
- /public_html/scripts/run_cis_pipeline.sh (safe orchestrator - dry-run by default)

Contact / Escalation
- If DB locks or high-impact failures appear, stop the pipeline and contact the IT Manager / DBA.

Notes
- This runbook assumes the scripts under `scripts/` are the canonical implementations present in this repo. Always inspect the top of each script for environment flags and dry-run options.

---
Generated by Copilot agent — ask me to run any step or to create a stricter orchestrator/CI job for production execution.
