# 📋 KB AUTO-SYNC QUICK REFERENCE

**Smart Cron Integration - Essential Commands**

---

## 🚀 Installation

```bash
# Preview (dry-run)
php install-kb-smart-cron.php --dry-run

# Install for real
php install-kb-smart-cron.php
```

---

## 📊 Monitoring

### Dashboard
```
https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
```

### Live Logs
```bash
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/logs/execution.log
```

### Database Check
```bash
# Count KB items
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT domain, COUNT(*) as items FROM kb_items GROUP BY domain;"

# Recent sync history
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SELECT * FROM kb_sync_history ORDER BY created_at DESC LIMIT 10;"

# Task performance
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
  SELECT task_name, AVG(duration_seconds) as avg_time, COUNT(*) as runs 
  FROM cron_metrics 
  WHERE task_name LIKE 'KB_%' 
  GROUP BY task_name;
"
```

---

## ⚙️ Management

### Run Specific Task
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron
php smart-cron.php --run-task="KB_Incremental_Staff"
```

### Regenerate Schedule
```bash
php smart-cron.php --analyze
```

### Test Indexer Directly
```bash
cd /home/master/applications/jcepnzzkmj/public_html/modules/ai-agent/sync
php kb-auto-indexer.php --domain=staff --incremental --verbose
```

---

## 🔧 Configuration Files

### Tasks Definition
```
/assets/services/cron/smart-cron/config/tasks.json
```

### Smart Cron Config
```
/assets/services/cron/smart-cron/config/config.json
```

### KB Scripts
```
/modules/ai-agent/sync/kb-auto-indexer.php
/modules/ai-agent/sync/kb-cleanup.php
```

---

## 📅 Task Schedule

| Task | Frequency | Stagger | Type |
|------|-----------|---------|------|
| **Incremental Syncs** | | | |
| KB_Incremental_Staff | 15 min | :00 | light |
| KB_Incremental_Web | 15 min | :05 | light |
| KB_Incremental_GPT | 15 min | :10 | light |
| KB_Incremental_Wiki | 15 min | :15 | light |
| **Full Scans** | | | |
| KB_Full_Staff | 6 hours | 0 AM | medium |
| KB_Full_Web | 6 hours | 1 AM | medium |
| KB_Full_GPT | 6 hours | 2 AM | medium |
| KB_Full_Wiki | 6 hours | 3 AM | medium |
| **Master Syncs** | | | |
| KB_Superadmin_Sync | daily | 2 AM | medium |
| KB_Global_Sync | daily | 3 AM | medium |
| **Cleanup** | | | |
| KB_Cleanup | weekly | Sun 4 AM | light |

**Total: 11 tasks**

---

## 🚨 Troubleshooting

### Tasks Not Running?
```bash
# Check Smart Cron is active
ps aux | grep smart-cron

# Check crontab
crontab -l | grep smart-cron

# View errors
tail -100 /home/master/applications/jcepnzzkmj/public_html/logs/bootstrap-errors.log
```

### No KB Items?
```bash
# Check tables exist
mysql -u jcepnzzkmj -p jcepnzzkmj -e "SHOW TABLES LIKE 'kb_%';"

# Test manually
cd /modules/ai-agent/sync
php kb-auto-indexer.php --domain=staff --incremental --verbose
```

### Regenerate Everything
```bash
cd /assets/services/cron
php smart-cron.php --analyze
```

---

## 📞 Support

- **Documentation**: KB_SMART_CRON_GUIDE.md
- **Dashboard**: https://staff.vapeshed.co.nz/assets/services/cron/dashboard.php
- **Logs**: /assets/services/cron/smart-cron/logs/execution.log

---

**Quick Status Check (One Command)**:
```bash
mysql -u jcepnzzkmj -p jcepnzzkmj -e "
  SELECT 
    'KB Items' as Metric, 
    COUNT(*) as Value 
  FROM kb_items
  UNION ALL
  SELECT 
    'Last Sync' as Metric,
    MAX(created_at) as Value
  FROM kb_sync_history;
"
```
