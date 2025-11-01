# KB Auto-Indexer Quick Reference Card

## 🚀 Installation (Pick One)

### Option 1: Automated (Recommended)
```bash
cd /home/master/applications/jcepnzzkmj/public_html/modules/ai-agent/sync
./install-kb-cron-tasks.sh
```

### Option 2: Dashboard
```
https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
Click "Add Task" 11 times (see SMART_CRON_INSTALLATION.md)
```

### Option 3: Manual
```bash
# Edit tasks.json directly
nano /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/config/tasks.json
# Add 11 tasks from kb-smart-cron-tasks.json
php smart-cron.php --analyze
```

---

## ✅ Verification

### Test Single Task
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
php smart-cron.php --run-task='KB_Index_Staff_Incremental'
```

### Check Dashboard
```
https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
```

### Check Database
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT domain, COUNT(*) FROM kb_items GROUP BY domain;"
```

---

## 📊 Task Schedule

| Task | Frequency | Time/Offset |
|------|-----------|-------------|
| Staff Incremental | Every 15 min | +3 min |
| Web Incremental | Every 15 min | +6 min |
| GPT Incremental | Every 15 min | +9 min |
| Wiki Incremental | Every 15 min | +12 min |
| Staff Full | Every 6 hours | +15 min |
| Web Full | Every 6 hours | +30 min |
| GPT Full | Every 6 hours | +45 min |
| Wiki Full | Every 6 hours | +60 min |
| SuperAdmin | Daily | 2:00 AM |
| Global | Daily | 3:00 AM |
| Cleanup | Weekly | Sun 4 AM |

---

## 🔍 Monitoring

### Logs
```bash
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log
```

### Task Metrics
```sql
SELECT task_name, COUNT(*) as runs, AVG(duration_seconds), SUM(success=1) as successes
FROM cron_metrics WHERE task_name LIKE 'KB_%' GROUP BY task_name;
```

### KB Sync History
```sql
SELECT * FROM kb_sync_history ORDER BY completed_at DESC LIMIT 10;
```

---

## 🛠️ Troubleshooting

### Task Not Running
```bash
# 1. Check if Smart Cron active
crontab -l | grep smart-cron

# 2. List tasks
php smart-cron.php --list-tasks | grep KB_

# 3. Test manually
php smart-cron.php --run-task='KB_Index_Staff_Incremental'
```

### Task Failing
```sql
-- Check errors
SELECT task_name, output FROM cron_metrics 
WHERE task_name LIKE 'KB_%' AND success=0 
ORDER BY executed_at DESC LIMIT 5;
```

```bash
# Run directly
cd /home/master/applications/jcepnzzkmj/public_html
php modules/ai-agent/sync/kb-auto-indexer.php --domain=staff --incremental
```

---

## 📁 Important Paths

```
Scripts:
  /home/master/.../modules/ai-agent/sync/kb-auto-indexer.php
  /home/master/.../modules/ai-agent/sync/kb-cleanup.php

Smart Cron:
  /home/master/.../assets/services/cron/smart-cron.php
  /home/master/.../assets/services/cron/smart-cron/config/tasks.json

Logs:
  /home/master/.../assets/services/cron/smart-cron/logs/execution.log

Dashboard:
  https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
```

---

## 📚 Documentation

- **SMART_CRON_READY.md** - Quick start (this is the overview)
- **SMART_CRON_INSTALLATION.md** - Complete guide (read this first)
- **kb-smart-cron-tasks.json** - Task definitions
- **/assets/services/cron/README.md** - Smart Cron system docs

---

## 🎯 What It Does

### Incremental (Every 15 min)
- Scans for new/modified/deleted files (fast)
- Updates KB database
- Maps relationships
- ~30-60 seconds per domain

### Full (Every 6 hours)
- Everything above PLUS:
- Calculates importance scores
- Auto-summarizes important docs
- Deep relationship analysis
- ~3-5 minutes per domain

### SuperAdmin (Daily 2 AM)
- Inherits from ALL domains
- God-mode KB view

### Global (Daily 3 AM)
- Project-wide documentation
- High-level architecture

### Cleanup (Weekly Sun 4 AM)
- Removes deleted items >90 days
- Optimizes database

---

## ⚡ Quick Commands

```bash
# Install
./install-kb-cron-tasks.sh

# Test
php smart-cron.php --run-task='KB_Index_Staff_Incremental'

# Monitor logs
tail -f smart-cron/logs/execution.log

# Check database
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT * FROM kb_sync_history ORDER BY completed_at DESC LIMIT 5;"

# Regenerate schedule
php smart-cron.php --analyze

# List all KB tasks
php smart-cron.php --list-tasks | grep KB_
```

---

**Version:** 1.0.0  
**Status:** Production Ready ✅  
**Last Updated:** October 19, 2025
