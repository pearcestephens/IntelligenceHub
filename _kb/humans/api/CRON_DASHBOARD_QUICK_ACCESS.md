# ⚡ QUICK ACCESS GUIDE - Cron Dashboard Integration

## 🚀 Immediate Access

**Main Dashboard:** https://gpt.ecigdis.co.nz/dashboard  
**Cron Management:** https://gpt.ecigdis.co.nz/dashboard?page=cron

---

## 🎯 Quick URLs (Copy & Paste)

```
All Applications:
https://gpt.ecigdis.co.nz/dashboard?page=cron&app=all

Intelligence Hub:
https://gpt.ecigdis.co.nz/dashboard?page=cron&app=intelligence_hub

CIS Staff Portal:
https://gpt.ecigdis.co.nz/dashboard?page=cron&app=jcepnzzkmj

Vape Shed Retail:
https://gpt.ecigdis.co.nz/dashboard?page=cron&app=dvaxgvsxmz

Ecigdis Wholesale:
https://gpt.ecigdis.co.nz/dashboard?page=cron&app=fhrehrpjmu
```

---

## 📂 Key Files Modified

```bash
# Dashboard Page (main integration)
/home/master/applications/hdgwrzntwa/public_html/dashboard/pages/cron.php

# Sidebar Navigation (menu item added)
/home/master/applications/hdgwrzntwa/public_html/dashboard/includes/sidebar.php

# Server Configuration (expanded with all apps)
/home/master/applications/hdgwrzntwa/public_html/_kb/config/cron_servers.json

# Universal Cron Controller (backend - already working)
/home/master/applications/hdgwrzntwa/public_html/_kb/scripts/universal_cron_controller.php
```

---

## 🎨 What You'll See

### 1. Application Selector (Top of Page)
```
┌─────────────────────────────────────────────────────────┐
│ 🌐 Select Application / Domain                          │
├─────────────────────────────────────────────────────────┤
│ [🌐All] [🧠Hub] [🏢CIS] [🏪Vape] [📦Ecigdis]          │
│                                                         │
│ Domain: staff.vapeshed.co.nz                           │
│ Description: Main ERP system - inventory, transfers... │
└─────────────────────────────────────────────────────────┘
```

### 2. Settings Panel (Per Application)
```
┌─────────────────────────────────────────────────────────┐
│ ⚙️ Application Settings: CIS Staff Portal    [💾 Save] │
├─────────────────────────────────────────────────────────┤
│ Auto-Sync: [✓]     Automatically sync every 6 hours   │
│ Coordination: [✓]   Prevent timing conflicts          │
│ API URL: [https://staff.vapeshed.co.nz/api/...]       │
│ API Key: [********]  [👁️]                              │
│ Priority: [2 - High ▼]                                 │
└─────────────────────────────────────────────────────────┘
```

### 3. Quick Stats (4 Cards)
```
┌─────────┬─────────┬─────────┬─────────┐
│ 📡 5    │ 📋 42   │ ▶️ 3    │ ❌ 0    │
│ Servers │ Tasks   │ Running │ Failed  │
└─────────┴─────────┴─────────┴─────────┘
```

### 4. Quick Actions
```
[🔄 Refresh Status] [🔄 Sync All] [📊 Coordinate] [📄 View Logs]
```

### 5. Server Status Cards
```
┌─────────────────────────────────────────┐
│ 🏢 CIS Staff Portal        [✅ 15/20]   │
├─────────────────────────────────────────┤
│ Tasks: 20  │ Enabled: 15  │ Running: 3 │
│ [📋 View Tasks] [📄 Logs] [⌨️ Crontab] │
└─────────────────────────────────────────┘
```

### 6. Console Output
```
┌─────────────────────────────────────────┐
│ $ Universal Cron Controller             │
│ [12:34:56] 🚀 Status refresh complete   │
│ [12:34:57] ✅ All servers responding    │
│ [12:34:58] 📡 Waiting for commands...   │
└─────────────────────────────────────────┘
```

---

## 🎯 Common Actions

### View Crons for Specific Application
1. Go to: https://gpt.ecigdis.co.nz/dashboard?page=cron
2. Click application button (e.g., [🏢 CIS Portal])
3. View filtered cron tasks

### Change Application Settings
1. Select application from top buttons
2. Scroll to "Application Settings" panel
3. Toggle switches or edit fields
4. Click [💾 Save] button
5. See toast notification

### Refresh Status
1. Click [🔄 Refresh Status] button
2. Wait for page reload
3. View updated stats

### Sync All Servers
1. Click [🔄 Sync All Servers] button
2. Watch console output
3. Wait for completion message

### View Logs
1. Click [📄 View Logs] on server card
2. OR click [📄 View Logs] in quick actions
3. Console displays log output

---

## 💡 Pro Tips

### Tip 1: Bookmark Direct Links
Save these bookmarks for quick access:
- Intelligence Hub Crons: `?page=cron&app=intelligence_hub`
- CIS Portal Crons: `?page=cron&app=jcepnzzkmj`

### Tip 2: Use Auto-Refresh
- Page auto-refreshes every 30 seconds
- Status stays current automatically
- No manual refresh needed

### Tip 3: Settings Are Persistent
- Settings saved to JSON config file
- Survives page reloads and server restarts
- Shared across all dashboard sessions

### Tip 4: Console Is Your Friend
- Shows real-time command execution
- Color-coded messages (green=success, red=error)
- Auto-scrolls to latest output
- Shows timestamps for every action

### Tip 5: Application Priority
- Higher priority (1) = preferred during coordination
- Lower priority (5) = adjusted to avoid conflicts
- Set based on importance:
  - Intelligence Hub: 1 (Master)
  - CIS Portal: 2 (Critical ERP)
  - Retail sites: 3 (Normal)

---

## 🔧 Configuration Quick Reference

### API Key Setup
Edit `/home/master/applications/hdgwrzntwa/public_html/_kb/config/cron_servers.json`:

```json
{
  "jcepnzzkmj": {
    "api_key": "YOUR_ACTUAL_KEY_HERE"
  }
}
```

Or use environment variables (recommended):
```bash
export CIS_API_KEY="your-key"
export VAPESHED_API_KEY="your-key"
export ECIGDIS_API_KEY="your-key"
```

### Add New Application
1. Edit `cron_servers.json`
2. Add new entry:
```json
{
  "new_app_id": {
    "id": "new_app_id",
    "name": "My New App",
    "type": "remote",
    "domain": "myapp.com",
    "api_url": "https://myapp.com/api/cron/manage.php",
    "api_key": "${MY_API_KEY}",
    "priority": 3,
    "auto_sync": true,
    "coordination": false
  }
}
```
3. Add button in `cron.php` application selector
4. Refresh page

---

## 🎯 What Works NOW

✅ Application selector - Switch between apps with one click  
✅ Settings panel - Configure per-application settings  
✅ Save settings - Persistent config stored in JSON  
✅ Filter crons - View only selected application's tasks  
✅ Quick stats - Real-time overview across all or one app  
✅ Console output - Live command execution feedback  
✅ Auto-refresh - Status updates every 30 seconds  
✅ Navigation - Sidebar menu item "Cron Management"  
✅ Toast notifications - User feedback on actions  
✅ API key masking - Security for sensitive data  
✅ Server registry - All production apps configured  

---

## 📞 Need Help?

### Check These First
1. Browser console (F12) for JavaScript errors
2. PHP error logs: `/home/master/applications/hdgwrzntwa/public_html/logs/`
3. Cron logs: `_kb/logs/cron_*.log`

### Common Issues

**Problem:** Settings not saving  
**Solution:** Check file permissions on `_kb/config/cron_servers.json`

**Problem:** No crons showing  
**Solution:** Run `php _kb/scripts/universal_cron_controller.php status` manually

**Problem:** Application button doesn't work  
**Solution:** Check URL parameter `?page=cron&app=APP_ID`

**Problem:** API key not working  
**Solution:** Verify key in config file, check remote API endpoint

---

## 🎉 You're All Set!

**Access your unified cron management dashboard at:**  
👉 **https://gpt.ecigdis.co.nz/dashboard?page=cron** 👈

**Features:**
- ✨ Multi-application selector
- ⚙️ Per-application settings
- 📊 Real-time status monitoring
- 🔄 One-click sync and coordination
- 📝 Live console output
- 💾 Persistent configuration

**Your "God Mode" control panel is ready!** 🚀
