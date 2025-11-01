# 🌐 Universal Cron Controller - Quick Start Guide

## ✅ SYSTEM READY!

You now have **GOD MODE** for cron management across ALL servers!

---

## 🚀 Quick Commands

### View All Tasks (Both Servers)
```bash
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php universal_cron_controller.php list
```

### View CIS Portal Only
```bash
php universal_cron_controller.php list --server=cis_portal
```

### View Intelligence Hub Only
```bash
php universal_cron_controller.php list --server=intelligence_hub
```

### Check Status Across All Servers
```bash
php universal_cron_controller.php status
```

### Sync All Servers & Coordinate Schedules
```bash
php universal_cron_controller.php sync
```

### Coordinate Schedules (Find Conflicts)
```bash
php universal_cron_controller.php coordinate
```

---

## 🎯 Managing Tasks

### Add Task to CIS
```bash
php universal_cron_controller.php add \
  --server=cis_portal \
  --task='{"id":"my_task","name":"My Task","script":"my_script.php","schedule":{"type":"cron","cron":"0 */4 * * *"}}'
```

### Disable Task on Intelligence Hub
```bash
php universal_cron_controller.php disable \
  --server=intelligence_hub \
  --task=ssh_session_detector
```

### Enable Task on CIS
```bash
php universal_cron_controller.php enable \
  --server=cis_portal \
  --task=webhook_handler
```

### Remove Task
```bash
php universal_cron_controller.php remove \
  --server=cis_portal \
  --task=old_task_id
```

---

## 📋 Viewing Logs

### View Logs from CIS
```bash
php universal_cron_controller.php logs \
  --server=cis_portal \
  --task=webhook_handler \
  --lines=100
```

### View Logs from Intelligence Hub
```bash
php universal_cron_controller.php logs \
  --server=intelligence_hub \
  --lines=50
```

---

## 🔍 Advanced Features

### View Actual Crontab (System Level)
```bash
# Intelligence Hub crontab
php universal_cron_controller.php crontab --server=intelligence_hub

# CIS Portal crontab (via API)
php universal_cron_controller.php crontab --server=cis_portal
```

### Launch Web Dashboard
```bash
php universal_cron_controller.php dashboard --port=8080
```

Then open: http://localhost:8080

### Install on New Server
```bash
php universal_cron_controller.php install \
  --server=new_server \
  --name="New Server Name" \
  --url=https://new.example.com \
  --api-key=your_api_key_here
```

---

## 📊 What You Can Do

### ✅ Multi-Server Control
- **Intelligence Hub** (local) - File watcher, intelligence generation, security scans
- **CIS Portal** (remote) - Webhooks, transfers, POs, inventory sync
- **Any Server** - Add more servers with `install` command

### ✅ Complete Visibility
- List ALL tasks across ALL servers
- View logs from any server
- Check system crontabs
- Monitor task execution status

### ✅ Coordination
- Automatically detect schedule conflicts
- Stagger tasks to avoid CPU spikes
- Second-level timing precision
- Cross-server load balancing

### ✅ Easy Management
- Add/Edit/Remove tasks anywhere
- Enable/Disable without SSH
- View logs without server access
- Dashboard for visual management

---

## 🎯 Typical Workflows

### Daily Check
```bash
# Morning routine - check everything
php universal_cron_controller.php status
php universal_cron_controller.php coordinate
```

### Adding New Task to CIS
```bash
# 1. Add the task
php universal_cron_controller.php add --server=cis_portal --task='...'

# 2. Verify it's added
php universal_cron_controller.php list --server=cis_portal

# 3. Check for conflicts
php universal_cron_controller.php coordinate

# 4. Adjust timing if needed
php universal_cron_controller.php edit --server=cis_portal --task=new_task --changes='{"schedule":{"wait_seconds":20}}'
```

### Troubleshooting Failed Task
```bash
# 1. Check overall status
php universal_cron_controller.php status

# 2. View recent logs
php universal_cron_controller.php logs --server=cis_portal --task=failed_task --lines=100

# 3. Check if enabled
php universal_cron_controller.php list --server=cis_portal | grep failed_task

# 4. Re-enable if needed
php universal_cron_controller.php enable --server=cis_portal --task=failed_task
```

### Before Deploying New Code
```bash
# 1. Disable affected tasks
php universal_cron_controller.php disable --server=cis_portal --task=webhook_handler

# 2. Deploy code
# ... your deployment ...

# 3. Re-enable tasks
php universal_cron_controller.php enable --server=cis_portal --task=webhook_handler

# 4. Monitor logs
php universal_cron_controller.php logs --server=cis_portal --task=webhook_handler --lines=20
```

---

## 🔐 API Key Setup

The Universal Controller needs API access to CIS Portal.

### Option 1: Environment Variable (Recommended)
```bash
export CIS_API_KEY="your_api_key_here"
```

Add to `~/.bashrc` for persistence.

### Option 2: Config File
The key is automatically read from:
```
/home/master/applications/hdgwrzntwa/public_html/_kb/config/api_key.txt
```

---

## 📁 Configuration Files

### Server List
```
_kb/config/cron_servers.json
```

Contains all registered servers:
- intelligence_hub (local)
- cis_portal (remote)
- ... any additional servers you add

### Coordination Cache
```
_kb/cache/cron_coordination.json
```

Stores schedule analysis and conflict detection results.

---

## 🎨 Dashboard Features

When you run `php universal_cron_controller.php dashboard --port=8080`:

- **Live server status** - See all servers at a glance
- **Task list** - View all tasks across all servers
- **Enable/Disable buttons** - Toggle tasks with one click
- **View logs** - Click to see recent executions
- **Auto-refresh** - Updates every 30 seconds
- **Color-coded status** - Green = enabled, Red = disabled

---

## 🚨 Important Notes

### Coordination is Automatic
When you add/edit/remove tasks, the system automatically:
1. Checks for schedule conflicts
2. Analyzes timing gaps
3. Suggests adjustments if needed

### Second-Level Staggering
All tasks support `wait_seconds` parameter:
```json
{
  "schedule": {
    "type": "cron",
    "cron": "0 */4 * * *",
    "wait_seconds": 20
  }
}
```

This runs at :00:20 instead of :00:00, avoiding CPU spikes.

### Current Staggering (Already Applied)
- Intelligence Refresh: :00:00 (0 seconds)
- Push to CIS: :15:20 (20 seconds)
- Call Graph: :30:40 (40 seconds)
- Cleanup: :30:10 (10 seconds)
- Security: :00:30 (30 seconds)

**No two tasks start within 15 minutes OR within 20 seconds of each other!**

---

## 📈 Next Steps

### 1. Test the System
```bash
php universal_cron_controller.php list
php universal_cron_controller.php status
```

### 2. Check Coordination
```bash
php universal_cron_controller.php coordinate
```

### 3. Launch Dashboard
```bash
php universal_cron_controller.php dashboard --port=8080
```

### 4. Set Up Auto-Sync (Optional)
Add to Intelligence Hub crontab:
```cron
0 */6 * * * cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts && php universal_cron_controller.php sync >/dev/null 2>&1
```

This syncs all servers every 6 hours.

---

## 🎉 You're All Set!

You now have:
- ✅ Single command to view all crons everywhere
- ✅ API control over CIS Portal crons
- ✅ Local control over Intelligence Hub crons
- ✅ Automatic conflict detection
- ✅ Web dashboard for visual management
- ✅ Cross-server coordination
- ✅ Second-level timing precision
- ✅ Complete logging and monitoring

**One controller to rule them all!** 👑
