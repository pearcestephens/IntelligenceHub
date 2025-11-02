#!/usr/bin/env bash
# Orchestrator for CIS Intelligence Pipeline
# Safe defaults: dry-run until --execute passed
# Usage: ./scripts/run_cis_pipeline.sh [--execute]

set -euo pipefail
SCRIPT_DIR=$(cd "$(dirname "$0")" && pwd)
PROJECT_ROOT=$(cd "$SCRIPT_DIR/.." && pwd)
LOG_DIR="$PROJECT_ROOT/logs"
DRY_RUN=1
if [ "${1:-}" == "--execute" ]; then
  DRY_RUN=0
fi

timestamp() { date -u +"%Y-%m-%dT%H:%M:%SZ"; }
log() { echo "[$(timestamp)] $*"; }

# Ensure log dir
mkdir -p "$LOG_DIR"

# Step 0: quick checks
log "Starting CIS pipeline orchestrator (dry-run=$DRY_RUN). Project root: $PROJECT_ROOT"
log "PHP CLI: $(which php || echo 'php not found')"
php -v > /dev/null 2>&1 || { echo "PHP CLI not available"; exit 2; }

# Run helpers
run_cmd() {
  local cmd="$1"
  local name="$2"
  local outfile="$LOG_DIR/${name}_$(date +%Y%m%d_%H%M%S).log"
  if [ "$DRY_RUN" -eq 1 ]; then
    log "DRY RUN: $cmd"
    echo "DRY: $cmd" > "$outfile"
    return 0
  fi
  log "RUN: $cmd"
  bash -c "$cmd" >> "$outfile" 2>&1
  local rc=$?
  if [ $rc -ne 0 ]; then
    log "ERROR: command failed (rc=$rc). See $outfile"
    exit $rc
  fi
  log "Completed: $name -> $outfile"
}

# 1) Prepare: verify .env exists
if [ -f "$PROJECT_ROOT/.env" ]; then
  log ".env found"
else
  log "WARNING: .env not found in project root. Please ensure environment variables are available or copy .env.example"
fi

# 2) Ingest projects using refresh-intelligence.php
INGEST_CMD="php $PROJECT_ROOT/scripts/refresh-intelligence.php --all"
run_cmd "$INGEST_CMD" "ingest"

# 3) Run safe neural scan (small batch)
NEURAL_SAFE_CMD="php $PROJECT_ROOT/scripts/safe_neural_scanner.php --limit=100"
run_cmd "$NEURAL_SAFE_CMD" "neural_safe"

# 4) Run full neural scan (larger batch)
NEURAL_FULL_CMD="php $PROJECT_ROOT/scripts/safe_neural_scanner.php --limit=1000"
run_cmd "$NEURAL_FULL_CMD" "neural_full"

# 5) Optional: run v2 intelligence engine if present
V2_SCRIPT="$PROJECT_ROOT/scripts/kb_intelligence_engine_v2.php"
if [ -f "$V2_SCRIPT" ]; then
  V2_CMD="php $V2_SCRIPT --full"
  run_cmd "$V2_CMD" "v2_engine"
else
  log "V2 engine not found; skipping"
fi

# 6) Post-processing
OPTIMIZE_CMD="php $PROJECT_ROOT/scripts/optimize-database.php"
if [ -f "$PROJECT_ROOT/scripts/optimize-database.php" ]; then
  run_cmd "$OPTIMIZE_CMD" "optimize_db"
else
  log "optimize-database.php not found; skipping"
fi

BACKUP_CMD="php $PROJECT_ROOT/scripts/backup-database.php"
if [ -f "$PROJECT_ROOT/scripts/backup-database.php" ]; then
  run_cmd "$BACKUP_CMD" "backup_db"
else
  log "backup-database.php not found; skipping"
fi

# 7) Optional: install optimized crons
CRON_INSTALLER="$PROJECT_ROOT/_kb/scripts/install_optimized_crons.sh"
if [ -f "$CRON_INSTALLER" ]; then
  if [ "$DRY_RUN" -eq 1 ]; then
    log "DRY RUN: Preview cron installer (will prompt). To actually install run with --execute"
    bash "$CRON_INSTALLER"
  else
    log "Installing optimized crons"
    bash "$CRON_INSTALLER"
  fi
else
  log "Cron installer not found; skipping"
fi

# 8) Smoke checks
log "Performing smoke checks"
# Check recent error logs
ERR_COUNT=$(grep -i "fatal\|error" "$LOG_DIR"/*.log 2>/dev/null | wc -l || true)
log "Recent error/fatal count in $LOG_DIR: $ERR_COUNT"

# Summary
log "CIS pipeline orchestrator finished (dry-run=$DRY_RUN). Review logs in $LOG_DIR"

if [ "$DRY_RUN" -eq 1 ]; then
  echo "To execute for real: $0 --execute"
fi

exit 0
