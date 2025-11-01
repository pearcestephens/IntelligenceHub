# KB Auto-Indexer Installation for Smart Cron

**Complete guide for adding KB auto-indexing tasks to your Smart Cron system**

---

## 📋 Overview

This installation adds **11 automated KB indexing tasks** to your Smart Cron scheduler:

### Incremental Syncs (Every 15 minutes)
- **KB_Index_Staff_Incremental** - Fast file scanning for staff domain
- **KB_Index_Web_Incremental** - Fast file scanning for web domain  
- **KB_Index_GPT_Incremental** - Fast file scanning for gpt domain
- **KB_Index_Wiki_Incremental** - Fast file scanning for wiki domain

### Full Indexes (Every 6 hours)
- **KB_Index_Staff_Full** - Deep analysis for staff domain
- **KB_Index_Web_Full** - Deep analysis for web domain
- **KB_Index_GPT_Full** - Deep analysis for gpt domain
- **KB_Index_Wiki_Full** - Deep analysis for wiki domain

### Special Domains (Daily)
- **KB_Index_SuperAdmin** - Daily at 2 AM (inherits from all domains)
- **KB_Index_Global** - Daily at 3 AM (project-wide documentation)

### Maintenance (Weekly)
- **KB_Cleanup_Weekly** - Sunday at 4 AM (removes deleted items >90 days)

---

## 🚀 Quick Installation (2 Minutes)

### Method 1: Automated Script (Recommended)

```bash
# Navigate to sync directory
cd /home/master/applications/jcepnzzkmj/public_html/modules/ai-agent/sync

# Preview changes (dry-run)
./install-kb-cron-tasks.sh --dry-run

# Install tasks
./install-kb-cron-tasks.sh

# Verify installation
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log
```

**What the script does:**
1. ✅ Backs up existing tasks.json
2. ✅ Adds 11 KB indexing tasks
3. ✅ Regenerates Smart Cron schedule
4. ✅ Shows verification commands

---

### Method 2: Manual Dashboard Installation

**If you prefer using the web interface:**

1. **Open Dashboard**
   ```
   https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
   ```

2. **Add Each Task** (11 total)
   
   Click "Add Task" and enter these values:

   **Task 1: KB_Index_Staff_Incremental**
   - Name: `KB_Index_Staff_Incremental`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=staff --incremental`
   - Description: `Incremental KB indexing for staff domain`
   - Type: `light`
   - Frequency: `every_15_minutes`
   - Offset: `3`
   - Click "Save"

   **Task 2: KB_Index_Web_Incremental**
   - Name: `KB_Index_Web_Incremental`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=web --incremental`
   - Description: `Incremental KB indexing for web domain`
   - Type: `light`
   - Frequency: `every_15_minutes`
   - Offset: `6`
   - Click "Save"

   **Task 3: KB_Index_GPT_Incremental**
   - Name: `KB_Index_GPT_Incremental`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=gpt --incremental`
   - Description: `Incremental KB indexing for gpt domain`
   - Type: `light`
   - Frequency: `every_15_minutes`
   - Offset: `9`
   - Click "Save"

   **Task 4: KB_Index_Wiki_Incremental**
   - Name: `KB_Index_Wiki_Incremental`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=wiki --incremental`
   - Description: `Incremental KB indexing for wiki domain`
   - Type: `light`
   - Frequency: `every_15_minutes`
   - Offset: `12`
   - Click "Save"

   **Task 5: KB_Index_Staff_Full**
   - Name: `KB_Index_Staff_Full`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=staff --full`
   - Description: `Full KB indexing for staff domain`
   - Type: `medium`
   - Frequency: `every_6_hours`
   - Offset: `15`
   - Click "Save"

   **Task 6: KB_Index_Web_Full**
   - Name: `KB_Index_Web_Full`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=web --full`
   - Description: `Full KB indexing for web domain`
   - Type: `medium`
   - Frequency: `every_6_hours`
   - Offset: `30`
   - Click "Save"

   **Task 7: KB_Index_GPT_Full**
   - Name: `KB_Index_GPT_Full`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=gpt --full`
   - Description: `Full KB indexing for gpt domain`
   - Type: `medium`
   - Frequency: `every_6_hours`
   - Offset: `45`
   - Click "Save"

   **Task 8: KB_Index_Wiki_Full**
   - Name: `KB_Index_Wiki_Full`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=wiki --full`
   - Description: `Full KB indexing for wiki domain`
   - Type: `medium`
   - Frequency: `every_6_hours`
   - Offset: `60`
   - Click "Save"

   **Task 9: KB_Index_SuperAdmin**
   - Name: `KB_Index_SuperAdmin`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=superadmin --full`
   - Description: `SuperAdmin KB sync (inherits from all domains)`
   - Type: `medium`
   - Frequency: `daily`
   - Hour: `2`
   - Minute: `0`
   - Click "Save"

   **Task 10: KB_Index_Global**
   - Name: `KB_Index_Global`
   - Script: `ai-agent/sync/kb-auto-indexer.php --domain=global --full`
   - Description: `Global KB sync (project-wide documentation)`
   - Type: `medium`
   - Frequency: `daily`
   - Hour: `3`
   - Minute: `0`
   - Click "Save"

   **Task 11: KB_Cleanup_Weekly**
   - Name: `KB_Cleanup_Weekly`
   - Script: `ai-agent/sync/kb-cleanup.php --older-than=90`
   - Description: `Weekly KB cleanup (removes deleted items >90 days)`
   - Type: `light`
   - Frequency: `weekly`
   - Day of Week: `0` (Sunday)
   - Hour: `4`
   - Minute: `0`
   - Click "Save"

3. **Done!** Schedule auto-regenerates after each task is added.

---

### Method 3: Direct JSON Edit (Advanced)

**For developers who want to edit tasks.json directly:**

1. **Backup tasks.json**
   ```bash
   cp /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/config/tasks.json \
      /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/config/tasks.json.backup
   ```

2. **Open tasks.json**
   ```bash
   nano /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/config/tasks.json
   ```

3. **Add these 11 tasks** to the `"tasks"` array (see `kb-smart-cron-tasks.json` for exact JSON)

4. **Regenerate schedule**
   ```bash
   cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
   php smart-cron.php --analyze
   ```

---

## ✅ Verification

### 1. Check Dashboard

Open dashboard and verify all 11 tasks appear:
```
https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
```

Look for tasks starting with `KB_Index_` and `KB_Cleanup_`

### 2. Test Individual Task

Run a task manually to verify it works:
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
php smart-cron.php --run-task='KB_Index_Staff_Incremental'
```

**Expected output:**
```
[2025-10-19 12:00:00] Starting KB Auto-Indexer...
[2025-10-19 12:00:00] Domain: staff
[2025-10-19 12:00:00] Mode: incremental
[2025-10-19 12:00:01] Scanning for changes...
[2025-10-19 12:00:02] Found 5 new files, 3 modified, 0 deleted
[2025-10-19 12:00:03] Indexing files...
[2025-10-19 12:00:05] Mapping relationships...
[2025-10-19 12:00:06] ✓ Indexed 8 files successfully
[2025-10-19 12:00:06] Duration: 6.2 seconds
```

### 3. Check Logs

Monitor execution logs:
```bash
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log
```

### 4. Check Database

Verify KB items are being created:
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
SELECT 
    domain,
    COUNT(*) as total_items,
    SUM(deleted_at IS NULL) as active_items,
    SUM(deleted_at IS NOT NULL) as deleted_items
FROM kb_items
GROUP BY domain
ORDER BY domain;
"
```

**Expected output:**
```
+-----------+-------------+--------------+---------------+
| domain    | total_items | active_items | deleted_items |
+-----------+-------------+--------------+---------------+
| staff     | 1523        | 1498         | 25            |
| web       | 832         | 810          | 22            |
| gpt       | 456         | 445          | 11            |
| wiki      | 234         | 228          | 6             |
| superadmin| 3045        | 2981         | 64            |
| global    | 189         | 185          | 4             |
+-----------+-------------+--------------+---------------+
```

### 5. Check Sync History

View recent sync operations:
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
SELECT 
    domain,
    sync_type,
    items_indexed,
    new_items,
    modified_items,
    deleted_items,
    relationships_mapped,
    completed_at
FROM kb_sync_history
ORDER BY completed_at DESC
LIMIT 10;
"
```

### 6. Check Cron Metrics

View task execution statistics:
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
SELECT 
    task_name,
    COUNT(*) as runs,
    AVG(duration_seconds) as avg_duration,
    SUM(success=1) as successes,
    SUM(success=0) as failures,
    MAX(executed_at) as last_run
FROM cron_metrics
WHERE task_name LIKE 'KB_%'
GROUP BY task_name
ORDER BY runs DESC;
"
```

---

## 📊 What Each Task Does

### Incremental Syncs (Every 15 minutes)

**Purpose:** Fast detection and indexing of new/changed files

**What it does:**
- ✅ Scans directory for new files (MD5 hash comparison)
- ✅ Detects modified files (hash mismatch)
- ✅ Detects deleted files (missing from filesystem)
- ✅ Extracts and stores file content
- ✅ Maps basic relationships (requires, imports, extends)
- ✅ Updates `kb_items` table
- ✅ Updates `kb_relationships` table
- ✅ Records sync history

**Duration:** ~30-60 seconds per domain  
**Memory:** ~80MB per task  
**CPU:** Low

### Full Indexes (Every 6 hours)

**Purpose:** Deep analysis with importance scoring and auto-summarization

**What it does:**
- ✅ Everything from incremental sync, PLUS:
- ✅ Calculates importance scores (0.0-1.0):
  - File size and complexity
  - Number of relationships
  - Documentation indicators (README, docblocks)
  - File type and naming patterns
- ✅ Auto-summarizes important docs (score > 0.7)
- ✅ Deep relationship analysis
- ✅ Cross-domain reference validation
- ✅ Performance metrics collection

**Duration:** ~3-5 minutes per domain  
**Memory:** ~200MB per task  
**CPU:** Medium

### SuperAdmin Sync (Daily at 2 AM)

**Purpose:** God-mode view of entire knowledge base

**What it does:**
- ✅ Full index for superadmin domain
- ✅ Inherits content from all domains (via `kb_domain_inheritance`)
- ✅ Creates unified KB view for admins
- ✅ Validates cross-domain relationships
- ✅ Aggregates importance scores

**Duration:** ~3 minutes  
**Memory:** ~150MB  
**CPU:** Medium

### Global Sync (Daily at 3 AM)

**Purpose:** Project-wide documentation indexing

**What it does:**
- ✅ Full index for global domain
- ✅ Indexes project-wide documentation (README, ARCHITECTURE, SPECIFICATION)
- ✅ Maps high-level system architecture
- ✅ Tracks project-wide changes
- ✅ Creates top-level knowledge graph

**Duration:** ~4 minutes  
**Memory:** ~180MB  
**CPU:** Medium

### Weekly Cleanup (Sunday at 4 AM)

**Purpose:** Database maintenance and optimization

**What it does:**
- ✅ Removes `kb_items` with `deleted_at` > 90 days ago
- ✅ Cleans orphaned `kb_relationships` entries
- ✅ Removes old `kb_sync_history` entries (>180 days)
- ✅ Optimizes database tables (`ANALYZE TABLE`, `OPTIMIZE TABLE`)
- ✅ Records cleanup metrics

**Duration:** ~2 minutes  
**Memory:** ~100MB  
**CPU:** Low

---

## 🔍 Monitoring

### Smart Cron Dashboard

**URL:** `https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php`

**What you'll see:**
- ✅ All 11 KB tasks listed
- ✅ Last run time for each task
- ✅ Success/failure rates
- ✅ Average duration
- ✅ Execution timeline (24h chart)
- ✅ Resource usage graphs

### Real-Time Logs

**Smart Cron Execution Log:**
```bash
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log
```

**KB Auto-Indexer Output:**
All STDOUT/STDERR from `kb-auto-indexer.php` and `kb-cleanup.php` is captured by Smart Cron and logged to the execution log.

### Database Queries

**Recent KB activity:**
```sql
SELECT 
    domain,
    sync_type,
    items_indexed,
    duration_seconds,
    completed_at
FROM kb_sync_history
ORDER BY completed_at DESC
LIMIT 20;
```

**Task performance:**
```sql
SELECT 
    task_name,
    COUNT(*) as total_runs,
    AVG(duration_seconds) as avg_duration,
    MIN(duration_seconds) as min_duration,
    MAX(duration_seconds) as max_duration,
    SUM(success=1) as successes,
    SUM(success=0) as failures,
    ROUND(SUM(success=1) / COUNT(*) * 100, 2) as success_rate
FROM cron_metrics
WHERE task_name LIKE 'KB_%'
GROUP BY task_name
ORDER BY total_runs DESC;
```

**KB item counts:**
```sql
SELECT 
    domain,
    file_type,
    COUNT(*) as count,
    SUM(importance_score > 0.7) as important_items,
    AVG(importance_score) as avg_importance
FROM kb_items
WHERE deleted_at IS NULL
GROUP BY domain, file_type
ORDER BY domain, count DESC;
```

**Relationship statistics:**
```sql
SELECT 
    relationship_type,
    COUNT(*) as count
FROM kb_relationships
GROUP BY relationship_type
ORDER BY count DESC;
```

---

## 🛠️ Troubleshooting

### Task Not Running

**1. Check if task exists:**
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
php smart-cron.php --list-tasks | grep KB_
```

**2. Check if Smart Cron is running:**
```bash
crontab -l | grep smart-cron
```

Should see:
```
* * * * * php /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron.php >> /var/log/smart-cron.log 2>&1
```

**3. Test task manually:**
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
php smart-cron.php --run-task='KB_Index_Staff_Incremental'
```

**4. Check logs for errors:**
```bash
tail -100 /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log | grep -i error
```

### Task Failing

**1. Check error in cron_metrics:**
```sql
SELECT task_name, executed_at, duration_seconds, output
FROM cron_metrics
WHERE task_name LIKE 'KB_%' AND success = 0
ORDER BY executed_at DESC
LIMIT 10;
```

**2. Run kb-auto-indexer directly:**
```bash
cd /home/master/applications/jcepnzzkmj/public_html
php modules/ai-agent/sync/kb-auto-indexer.php --domain=staff --incremental
```

**3. Check database connection:**
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT 1;"
```

**4. Check file permissions:**
```bash
ls -la /home/master/applications/jcepnzzkmj/public_html/modules/ai-agent/sync/kb-auto-indexer.php
```

Should be readable by web user (www-data or similar).

### Slow Performance

**1. Check task duration:**
```sql
SELECT task_name, AVG(duration_seconds) as avg_duration
FROM cron_metrics
WHERE task_name LIKE 'KB_%'
GROUP BY task_name
ORDER BY avg_duration DESC;
```

**2. If incremental syncs > 90 seconds:**
- Consider reducing scope (fewer files)
- Check for slow queries in MySQL
- Verify indexes exist on `kb_items` and `kb_relationships`

**3. If full syncs > 10 minutes:**
- This is normal for large codebases
- Consider running less frequently (every 12 hours instead of 6)
- Check MySQL slow query log

**4. Optimize database:**
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
ANALYZE TABLE kb_items;
ANALYZE TABLE kb_relationships;
ANALYZE TABLE kb_sync_history;
OPTIMIZE TABLE kb_items;
OPTIMIZE TABLE kb_relationships;
OPTIMIZE TABLE kb_sync_history;
"
```

### No Items Being Indexed

**1. Check if paths are correct:**
```bash
# Verify base paths in kb-auto-indexer.php
grep "PROJECT_ROOT\|MODULES_PATH" /home/master/applications/jcepnzzkmj/public_html/modules/ai-agent/sync/kb-auto-indexer.php
```

**2. Check domain configuration:**
```sql
SELECT * FROM kb_domains;
```

Should show 6 domains: global, staff, web, gpt, wiki, superadmin

**3. Verify files exist in scan paths:**
```bash
ls -la /home/master/applications/jcepnzzkmj/public_html/modules/
```

**4. Test file detection:**
```bash
cd /home/master/applications/jcepnzzkmj/public_html
php modules/ai-agent/sync/kb-auto-indexer.php --domain=staff --incremental --verbose
```

---

## 🎯 Best Practices

### 1. Monitor First Week Daily

After installation, check dashboard daily for first week to ensure:
- ✅ All tasks running successfully
- ✅ Duration is acceptable
- ✅ No memory issues
- ✅ KB items being created

### 2. Adjust Frequencies if Needed

**If your codebase changes rarely:**
- Reduce incremental syncs to every 30 minutes
- Reduce full syncs to every 12 hours

**If your codebase changes frequently:**
- Keep incremental syncs at 15 minutes
- Consider adding 5-minute incremental syncs for critical domains

### 3. Keep Database Optimized

Run weekly optimization:
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
ANALYZE TABLE kb_items;
OPTIMIZE TABLE kb_items;
"
```

### 4. Review Cleanup Settings

Default cleanup removes deleted items after 90 days. Adjust if needed:
```bash
# Keep deleted items for 180 days instead
# Edit KB_Cleanup_Weekly task in dashboard:
# Change: kb-cleanup.php --older-than=90
# To:     kb-cleanup.php --older-than=180
```

### 5. Monitor Disk Usage

KB system stores file content in database. Monitor table sizes:
```sql
SELECT 
    TABLE_NAME,
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'jcepnzzkmj'
AND TABLE_NAME LIKE 'kb_%'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;
```

If `kb_items` table > 1GB, consider:
- Reducing retention period for deleted items
- Excluding large binary files from indexing
- Compressing file content

---

## 📞 Support

**If you encounter issues:**

1. Check this README's troubleshooting section
2. Review Smart Cron README at `/assets/services/cron/README.md`
3. Check execution logs
4. Test tasks manually
5. Verify database schema matches expected structure

**Useful SQL for debugging:**
```sql
-- Check domain configuration
SELECT * FROM kb_domains;

-- Check domain inheritance (superadmin should inherit from all)
SELECT * FROM kb_domain_inheritance;

-- Check recent sync operations
SELECT * FROM kb_sync_history ORDER BY completed_at DESC LIMIT 10;

-- Check task metrics
SELECT * FROM cron_metrics WHERE task_name LIKE 'KB_%' ORDER BY executed_at DESC LIMIT 20;

-- Check for items with high importance scores
SELECT domain, file_path, importance_score, has_summary 
FROM kb_items 
WHERE deleted_at IS NULL AND importance_score > 0.7
ORDER BY importance_score DESC
LIMIT 20;
```

---

## ✨ What's Next?

After installation, you'll have:
- ✅ Automated file indexing across all domains
- ✅ Relationship mapping between files
- ✅ Importance scoring for documentation
- ✅ Auto-summarization of key documents
- ✅ Deletion tracking with grace period
- ✅ Weekly maintenance and optimization

**Your knowledge base will now:**
- Self-update every 15 minutes (incremental)
- Deep-analyze every 6 hours (full)
- Clean itself weekly
- Track all code relationships
- Score document importance
- Provide god-mode view for admins (superadmin domain)

**Enjoy your self-maintaining KB system! 🚀**

---

**Last Updated:** October 19, 2025  
**Version:** 1.0.0  
**Maintained By:** AI Agent Sync System
