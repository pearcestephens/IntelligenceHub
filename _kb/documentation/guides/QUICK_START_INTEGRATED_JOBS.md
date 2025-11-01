# 🚀 INTEGRATED CRON JOBS - QUICK START

**Goal:** Stop VSCode crashes by implementing load-balanced cron job execution  
**Time Required:** 30 minutes  
**Risk:** LOW (can rollback anytime)

---

## ⚡ 3-Step Quick Deployment

### STEP 1: Run Migration (2 minutes)

```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron/migrations
php run_003_integrated_jobs.php
```

**✅ Expected:** "Migration completed successfully!" + 6 tables created

---

### STEP 2: Discover Jobs (3 minutes)

```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron

# Preview first (safe)
php bin/discover-cron-jobs.php --dry-run

# If looks good, register
php bin/discover-cron-jobs.php
```

**✅ Expected:** "X jobs successfully registered!"

---

### STEP 3: Enable Critical Jobs (5 minutes)

```sql
-- Connect to database
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj

-- Enable health monitoring jobs
UPDATE smart_cron_integrated_jobs 
SET enabled = TRUE, status = 'active'
WHERE job_name LIKE '%health%' 
OR job_name LIKE '%monitor%'
OR priority = 'critical';

-- Verify
SELECT job_name, enabled, status, priority 
FROM smart_cron_integrated_jobs 
WHERE enabled = TRUE;
```

**✅ Expected:** 3-5 critical jobs enabled

---

## 🎯 What This Achieves

**BEFORE:**
```
VSCode: 9.4 GB + Unknown Cron Jobs = 16+ GB → Crash every 1-2 hours ❌
```

**AFTER:**
```
VSCode: 9.4 GB + Max 5 Concurrent Jobs (3 GB limit) = 13.4 GB → Stable ✅
```

**Load Balancing Activated:**
- ✅ Max 5 jobs run simultaneously (default slot)
- ✅ Max 2 heavy jobs simultaneously (heavy slot)
- ✅ Jobs queued when slots full
- ✅ Memory monitored per slot
- ✅ Resource spikes prevented

---

## 📊 Verify It's Working

### Check Execution Slots (30 seconds)

```sql
SELECT 
  slot_name,
  max_concurrent_jobs,
  current_running_jobs,
  max_total_memory_mb,
  current_memory_mb
FROM smart_cron_execution_slots;
```

**✅ Expected:**
```
+----------+--------------------+---------------------+-----------------+---------------+
| slot_name| max_concurrent_jobs| current_running_jobs| max_total_memory| current_memory|
+----------+--------------------+---------------------+-----------------+---------------+
| default  |                  5 |                   0 |            3072 |             0 |
| heavy    |                  2 |                   0 |            4096 |             0 |
| light    |                 10 |                   0 |            1024 |             0 |
+----------+--------------------+---------------------+-----------------+---------------+
```

### Monitor For 1 Hour

```bash
# Watch memory usage
watch -n 300 free -h

# Watch slot usage
watch -n 30 "mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e 'SELECT slot_name, current_running_jobs FROM smart_cron_execution_slots'"
```

**✅ Expected:**
- Total memory stays < 14 GB
- Slot usage: 0-5 jobs in default slot
- NO VSCode crashes!

---

## 🎉 Success Criteria

After 24 hours:

- [ ] ✅ VSCode has NOT crashed
- [ ] ✅ Memory stays < 14 GB
- [ ] ✅ Slots show activity (jobs running/completing)
- [ ] ✅ Dashboard shows job health

**If all checked:** Problem SOLVED! VSCode crashes eliminated! 🎉

---

## 🚨 Rollback (if needed)

If something goes wrong:

```bash
# Disable all integrated jobs
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "
UPDATE smart_cron_integrated_jobs SET enabled = FALSE;
"

# System returns to previous state
# Original cron jobs still work independently
```

---

## 📞 Next Actions

**Immediate:**
- Monitor VSCode stability for 24 hours
- Check dashboard daily: `dashboard.php`

**Week 1:**
- Enable more jobs in batches (5-10 at a time)
- Review job health summary
- Tune schedules if needed

**Month 1:**
- All jobs enabled and stable
- Performance baselines calculated
- Alert integration configured

---

## 💡 Key Commands Reference

```bash
# Check job status
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "
SELECT job_name, enabled, status, last_executed_at 
FROM smart_cron_integrated_jobs 
ORDER BY last_executed_at DESC LIMIT 10
"

# Check slot health
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "
SELECT * FROM smart_cron_execution_slots
"

# View job health summary
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "
SELECT * FROM smart_cron_active_jobs_summary 
WHERE enabled = TRUE
"

# Re-scan for new jobs
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/smart-cron
php bin/discover-cron-jobs.php
```

---

**Ready to deploy?** Follow the 3 steps above. Monitor for 24 hours. Celebrate when VSCode stops crashing! 🎉

**Questions?** See full guide: `INTEGRATED_JOBS_DEPLOYMENT.md`
