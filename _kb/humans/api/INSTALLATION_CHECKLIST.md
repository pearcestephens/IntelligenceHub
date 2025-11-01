# ✅ INSTALLATION CHECKLIST

## 🎯 Goal
Replace 20-30 messy cron entries with ONE clean smart cron system.

---

## 📋 Pre-Installation

- [ ] Current working directory: `/home/master/applications/hdgwrzntwa/public_html/_kb/scripts`
- [ ] Have access to `crontab -e`
- [ ] Know your current KB cron entries (to remove them)

---

## 🚀 Installation Steps

### Step 1: Verify Files (30 seconds)

```bash
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts

# Check all files exist
ls -lh smart_cron_manager.php kb_cron.php cron_intelligence_refresh.php ssh_session_detector.php

# Make executable
chmod +x smart_cron_manager.php kb_cron.php cron_intelligence_refresh.php ssh_session_detector.php
```

- [ ] All 4 files exist and are executable

### Step 2: Initialize System (30 seconds)

```bash
# Create default schedule
php kb_cron.php list
```

**Expected output:**
```
📋 KB Cron Tasks
================================================================================

✅ ENABLED [cron_intelligence_refresh]
  Name: Intelligence Refresh (Every 4 Hours)
  ...
```

- [ ] Sees 7 tasks listed
- [ ] 6 tasks enabled, 1 disabled (kb_intelligence_v2)
- [ ] Config file created: `_kb/config/cron_schedule.json`

### Step 3: Backup Current Crontab (1 minute)

```bash
# Save current crontab to file
crontab -l > ~/crontab_backup_$(date +%Y%m%d_%H%M%S).txt

# View backup
cat ~/crontab_backup_*.txt
```

- [ ] Backup file created
- [ ] Can see current cron entries

### Step 4: Remove Old KB Cron Entries (2 minutes)

```bash
# Edit crontab
crontab -e
```

**Delete ALL lines containing:**
- `kb_intelligence`
- `smart_kb`
- `generate_call_graph`
- `ast_security`
- `enhanced_security`
- `cleanup`
- `push_intelligence`
- Any other `_kb/` related entries

**Keep:**
- Non-KB entries (if any)

**Save and exit** (`:wq` in vim, `Ctrl+X` then `Y` in nano)

- [ ] Old KB cron entries removed
- [ ] Non-KB entries preserved (if any)

### Step 5: Add Smart Cron Entry (1 minute)

```bash
# Edit crontab again
crontab -e
```

**Add this SINGLE line:**
```
* * * * * cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts && php smart_cron_manager.php >/dev/null 2>&1
```

**Save and exit**

- [ ] Smart cron entry added
- [ ] Saved successfully

### Step 6: Verify Installation (2 minutes)

```bash
# Check crontab (should show ONLY the smart cron entry)
crontab -l

# Wait 2 minutes for first execution
sleep 120

# Check status
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php kb_cron.php status

# View logs
php kb_cron.php logs | tail -20
```

**Expected status output:**
```
📊 Smart Cron Status
================================================================================

Total Runs: 1
Last Run: 2025-10-25 14:03:00
...
```

- [ ] Crontab shows only smart cron entry
- [ ] Status shows "Total Runs: 1" or more
- [ ] Last Run within last 2 minutes
- [ ] Logs showing execution

---

## 🎯 Post-Installation Tests

### Test 1: Task Listing

```bash
php kb_cron.php list
```

- [ ] Shows 7 tasks
- [ ] 6 enabled, 1 disabled
- [ ] All schedules displayed correctly

### Test 2: Trigger Manual Execution

```bash
# Trigger intelligence refresh
php kb_cron.php trigger cron_intelligence_refresh

# Wait 1 minute
sleep 60

# Check logs
php kb_cron.php logs | grep cron_intelligence_refresh | tail -10
```

- [ ] Task queued successfully
- [ ] Task executed within 1 minute
- [ ] Logs show execution

### Test 3: Check SSH Detector

```bash
# Check if SSH detector is running
php kb_cron.php logs | grep ssh_session_detector | tail -10
```

- [ ] SSH detector running every 30 seconds
- [ ] Shows session detection checks

### Test 4: View Statistics

```bash
php kb_cron.php status
```

- [ ] Shows run counts for each task
- [ ] Shows success rates
- [ ] Shows average execution times

---

## 🔍 Verification Checklist

### Crontab
- [ ] `crontab -l` shows only ONE KB entry
- [ ] Entry runs every minute: `* * * * *`
- [ ] Points to correct path
- [ ] Redirects output: `>/dev/null 2>&1`

### Configuration
- [ ] File exists: `_kb/config/cron_schedule.json`
- [ ] Contains 7 default tasks
- [ ] Valid JSON format

### State Files
- [ ] File exists: `_kb/cache/smart_cron_state.json`
- [ ] Shows recent execution data
- [ ] Task statistics present

### Logs
- [ ] File exists: `_kb/logs/smart_cron.log`
- [ ] Shows recent activity (within last 2 min)
- [ ] No error messages

### Scheduled Tasks
- [ ] `cron_intelligence_refresh` - Every 4 hours
- [ ] `ssh_session_detector` - Every 30 seconds
- [ ] `security_scan_weekly` - Weekly Sunday 3am
- [ ] `call_graph_generation` - Every 8 hours
- [ ] `cleanup_old_data` - Daily 4am
- [ ] `push_to_cis` - Every 4 hours

---

## 🎮 Common Commands Reference

```bash
# Go to scripts directory
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts

# View all tasks
php kb_cron.php list

# Check system status
php kb_cron.php status

# View execution logs
php kb_cron.php logs

# Trigger task immediately
php kb_cron.php trigger <task_id>

# Enable/disable task
php kb_cron.php enable <task_id>
php kb_cron.php disable <task_id>

# Add custom task
php kb_cron.php add "Task Name" script.php "every 2 hours"
```

---

## 🚨 Troubleshooting

### Problem: "No runs showing in status"

**Solution:**
```bash
# Wait 2 minutes after installation
sleep 120

# Check again
php kb_cron.php status

# If still nothing, check crontab
crontab -l | grep smart_cron

# Check for errors
php smart_cron_manager.php
```

### Problem: "Lock file preventing execution"

**Solution:**
```bash
# Check lock file
ls -lh ../_kb/cache/smart_cron.lock

# If older than 5 minutes, delete it
rm ../_kb/cache/smart_cron.lock

# Try again
php kb_cron.php status
```

### Problem: "Task not running at scheduled time"

**Solution:**
```bash
# Check if task is enabled
php kb_cron.php list | grep <task_id>

# Enable if needed
php kb_cron.php enable <task_id>

# Check schedule
cat ../_kb/config/cron_schedule.json | grep -A10 <task_id>
```

### Problem: "SSH detector not auto-starting daemon"

**Solution:**
```bash
# Check VS Code server directory
ls -lah ~/.vscode-server/

# Check SSH connections
who

# Check recent file activity
find . -type f -mmin -5 | head -10

# View detector logs
cat ../_kb/cache/session_detector.log | tail -20
```

---

## ✅ Final Checks

### All Systems Go When:

1. **Crontab Clean**
   - [ ] Only 1 KB cron entry
   - [ ] Runs every minute

2. **Manager Running**
   - [ ] Status shows recent runs
   - [ ] Total runs increasing every minute

3. **Tasks Scheduled**
   - [ ] All 6 enabled tasks listed
   - [ ] Schedules correct

4. **Logs Active**
   - [ ] Recent entries in smart_cron.log
   - [ ] No error messages

5. **Auto-Daemon Ready**
   - [ ] SSH detector checking every 30s
   - [ ] Will start daemon when you code

---

## 🎉 Success Indicators

You'll know it's working when:

✅ `php kb_cron.php status` shows runs increasing  
✅ `php kb_cron.php logs` shows regular activity  
✅ `crontab -l` shows only ONE KB entry  
✅ Intelligence refreshes every 4 hours automatically  
✅ Daemon starts when you connect via VS Code Remote  
✅ Daemon stops when you disconnect  

---

## 📚 Documentation

- **Setup Guide:** `_kb/SMART_CRON_SETUP_GUIDE.md`
- **Complete System:** `_kb/DUAL_MODE_SYSTEM_COMPLETE.md`
- **This Checklist:** `_kb/INSTALLATION_CHECKLIST.md`

---

## ⏱️ Total Installation Time

- Step 1: 30 seconds
- Step 2: 30 seconds
- Step 3: 1 minute
- Step 4: 2 minutes
- Step 5: 1 minute
- Step 6: 2 minutes

**Total: ~7 minutes** ⚡

---

## 🎊 You're Done!

Your intelligent, self-managing, load-optimized KB cron system is now running!

**One cron entry. Zero maintenance. Maximum efficiency.** 🚀
