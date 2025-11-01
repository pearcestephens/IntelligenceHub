#!/bin/bash

##############################################################################
# Smart Cron Job: VS Code Prompt Sync - Daily Backup
##############################################################################
# Category: maintenance
# Schedule: Daily (2 AM)
# Priority: LOW
# Resource: Low CPU, Low Memory
# Estimated Duration: 30-60 seconds
##############################################################################

# Job Configuration (for Smart Cron discovery)
# @schedule: 0 2 * * *
# @priority: LOW
# @max_concurrent: 1
# @timeout: 300
# @retry_on_failure: true
# @max_retries: 2
# @resource_check: true
# @description: Daily automated backup of VS Code prompts - generates .instructions.md files

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="/home/master/applications/hdgwrzntwa/public_html"
LOG_FILE="${APP_ROOT}/assets/services/cron/smart-cron/logs/vscode-sync.log"
API_URL="https://gpt.ecigdis.co.nz/dashboard/api/vscode-sync.php"
BACKUP_DIR="${APP_ROOT}/private_html/backups/vscode-prompts"

# Ensure backup directory exists
mkdir -p "$BACKUP_DIR"

# Log function
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_message "=========================================="
log_message "VS Code Sync - Daily Backup Starting"
log_message "=========================================="

# Check if AI Control Center is accessible
if ! curl -s -f -o /dev/null "$API_URL?action=health_check" 2>/dev/null; then
    log_message "ERROR: API not accessible at $API_URL"
    exit 1
fi

log_message "✅ API accessible"

# Get list of all available prompts from intelligence hub
PROMPTS=$(php -r "
require_once '${APP_ROOT}/app.php';
\$db = getDbConnection();
\$stmt = \$db->query('SELECT id, title FROM ai_prompts WHERE status = \"active\" ORDER BY updated_at DESC LIMIT 20');
\$prompts = [];
while (\$row = \$stmt->fetch(PDO::FETCH_ASSOC)) {
    \$prompts[] = \$row;
}
echo json_encode(\$prompts);
")

if [ -z "$PROMPTS" ] || [ "$PROMPTS" == "[]" ]; then
    log_message "No active prompts found to backup"
    exit 0
fi

log_message "Found prompts to backup: $PROMPTS"

# Backup each prompt
BACKUP_COUNT=0
ERROR_COUNT=0

echo "$PROMPTS" | php -r "
\$prompts = json_decode(file_get_contents('php://stdin'), true);
foreach (\$prompts as \$prompt) {
    echo \$prompt['id'] . '|' . \$prompt['title'] . PHP_EOL;
}
" | while IFS='|' read -r PROMPT_ID PROMPT_TITLE; do

    SAFE_TITLE=$(echo "$PROMPT_TITLE" | tr -cs '[:alnum:]' '_' | tr '[:upper:]' '[:lower:]')
    FILENAME="${SAFE_TITLE}.instructions.md"
    TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
    BACKUP_FILE="${BACKUP_DIR}/${TIMESTAMP}_${FILENAME}"

    log_message "Backing up: $PROMPT_TITLE (ID: $PROMPT_ID)"

    # Generate file via API
    RESPONSE=$(curl -s -X POST "$API_URL" \
        -H "Content-Type: application/json" \
        -d "{\"action\":\"generate_file\",\"prompt_id\":${PROMPT_ID},\"metadata\":{\"auto_backup\":true}}")

    if echo "$RESPONSE" | grep -q '"success":true'; then
        # Extract content and save to backup file
        echo "$RESPONSE" | php -r "
        \$data = json_decode(file_get_contents('php://stdin'), true);
        if (isset(\$data['content'])) {
            file_put_contents('${BACKUP_FILE}', \$data['content']);
            echo 'saved';
        }
        " | grep -q 'saved' && {
            log_message "  ✅ Saved: $BACKUP_FILE"
            BACKUP_COUNT=$((BACKUP_COUNT + 1))
        } || {
            log_message "  ❌ Failed to save: $BACKUP_FILE"
            ERROR_COUNT=$((ERROR_COUNT + 1))
        }
    else
        log_message "  ❌ API error for prompt ID: $PROMPT_ID"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
done

log_message "=========================================="
log_message "Backup Summary:"
log_message "  ✅ Successful: $BACKUP_COUNT"
log_message "  ❌ Failed: $ERROR_COUNT"
log_message "  📁 Location: $BACKUP_DIR"
log_message "=========================================="

# Cleanup old backups (keep last 30 days)
find "$BACKUP_DIR" -name "*.instructions.md" -type f -mtime +30 -delete 2>/dev/null
OLD_DELETED=$(find "$BACKUP_DIR" -name "*.instructions.md" -type f -mtime +30 2>/dev/null | wc -l)
if [ "$OLD_DELETED" -gt 0 ]; then
    log_message "🗑️  Cleaned up $OLD_DELETED old backups (>30 days)"
fi

# Update cron job statistics
php -r "
require_once '${APP_ROOT}/app.php';
\$db = getDbConnection();
\$db->exec('CREATE TABLE IF NOT EXISTS cron_job_stats (
    job_name VARCHAR(100) PRIMARY KEY,
    last_run TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_runs INT DEFAULT 0,
    last_status VARCHAR(20),
    last_message TEXT
)');
\$stmt = \$db->prepare('INSERT INTO cron_job_stats (job_name, total_runs, last_status, last_message)
    VALUES (?, 1, ?, ?)
    ON DUPLICATE KEY UPDATE
    last_run = CURRENT_TIMESTAMP,
    total_runs = total_runs + 1,
    last_status = VALUES(last_status),
    last_message = VALUES(last_message)');
\$status = ($ERROR_COUNT == 0) ? 'success' : 'partial';
\$message = \"Backed up ${BACKUP_COUNT} prompts, ${ERROR_COUNT} errors\";
\$stmt->execute(['vscode-sync-daily', \$status, \$message]);
"

log_message "VS Code Sync - Daily Backup Completed"

exit 0
