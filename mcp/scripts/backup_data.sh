#!/bin/bash
# MCP Data Backup Script - Run daily via cron
# Usage: ./backup_data.sh

BACKUP_DIR="/home/master/applications/hdgwrzntwa/private_html/backups/mcp"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30
LOG_FILE="$BACKUP_DIR/backup.log"

# Database credentials (UPDATE THESE)
DB_USER="hdgwrzntwa"
DB_PASS="YOUR_PASSWORD_HERE"
DB_NAME="hdgwrzntwa"

# Create backup directory
mkdir -p "$BACKUP_DIR"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Starting MCP backup..." | tee -a "$LOG_FILE"

# Backup database tables
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backing up database..." | tee -a "$LOG_FILE"
if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
  content_index content_elements content_metrics \
  > "$BACKUP_DIR/mcp_db_$DATE.sql" 2>> "$LOG_FILE"; then

    # Compress database backup
    gzip "$BACKUP_DIR/mcp_db_$DATE.sql"
    DB_SIZE=$(du -h "$BACKUP_DIR/mcp_db_$DATE.sql.gz" | cut -f1)
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Database backup completed: $DB_SIZE" | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ❌ Database backup failed" | tee -a "$LOG_FILE"
    exit 1
fi

# Backup cache directory
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backing up cache..." | tee -a "$LOG_FILE"
if tar -czf "$BACKUP_DIR/mcp_cache_$DATE.tar.gz" \
  -C /home/master/applications/hdgwrzntwa/public_html/mcp cache/ 2>> "$LOG_FILE"; then

    CACHE_SIZE=$(du -h "$BACKUP_DIR/mcp_cache_$DATE.tar.gz" | cut -f1)
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Cache backup completed: $CACHE_SIZE" | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Cache backup failed (non-critical)" | tee -a "$LOG_FILE"
fi

# Backup configuration files
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backing up configuration..." | tee -a "$LOG_FILE"
if tar -czf "$BACKUP_DIR/mcp_config_$DATE.tar.gz" \
  -C /home/master/applications/hdgwrzntwa/public_html/mcp \
  .env config/ 2>> "$LOG_FILE"; then

    CONFIG_SIZE=$(du -h "$BACKUP_DIR/mcp_config_$DATE.tar.gz" | cut -f1)
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Config backup completed: $CONFIG_SIZE" | tee -a "$LOG_FILE"
else
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ⚠️  Config backup failed (non-critical)" | tee -a "$LOG_FILE"
fi

# Calculate total backup size
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Total backup size: $TOTAL_SIZE" | tee -a "$LOG_FILE"

# Cleanup old backups
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Cleaning up old backups (retention: $RETENTION_DAYS days)..." | tee -a "$LOG_FILE"
DELETED_COUNT=$(find "$BACKUP_DIR" -name "mcp_*" -mtime +$RETENTION_DAYS -delete -print | wc -l)
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deleted $DELETED_COUNT old backup files" | tee -a "$LOG_FILE"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Backup completed successfully" | tee -a "$LOG_FILE"
exit 0
