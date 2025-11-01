# 🎯 INTELLIGENCE CONTROL PANEL V2 - QUICK ACCESS

## ✅ COMPLETE - Ready to Use!

### 🚀 Access URL
```
https://gpt.ecigdis.co.nz/intelligence_control_panel.php
```

---

## 🎨 What You'll See

### Tab 1: 🕐 Cron Management (DEFAULT)
**Loads automatically with:**
- **Server Status Cards** - Intelligence Hub + CIS Portal with live metrics
- **Quick Actions** - Refresh, Sync All, Coordinate, View Logs
- **System Overview** - Total servers, tasks, enabled count
- **Live Console** - Real-time command output with timestamps

**Click Actions:**
- `📋 View Tasks` - Shows all crons for that server
- `📄 View Logs` - Displays execution logs
- `🔄 Refresh Status` - Updates all server data
- `🔄 Sync All Servers` - Synchronizes across all servers
- `🎯 Coordinate Schedules` - Detects conflicts and suggests timing

**What Works Right Now:**
- ✅ Displays Intelligence Hub with 19 real crontab entries
- ✅ Shows CIS Portal status (needs API key for full data)
- ✅ Console logs all operations with timestamps
- ✅ Auto-refreshes every 30 seconds
- ✅ Click to view tasks per server

### Tab 2: 🔍 Neural Scanner
**Trigger intelligence extraction:**
- **CIS Staff Portal** (staff.vapeshed.co.nz)
- **Vape Shed Retail** (vapeshed.co.nz)
- **Ecigdis Wholesale** (ecigdis.co.nz)
- **All Servers** (full system scan)

**Click "Scan" Button:**
- Triggers API request to `/api/intelligence/scan`
- Returns scan ID and status
- Logs to `_kb/logs/neural_scanner.log`
- Runs in background
- Updates statistics after completion

### Tab 3: 📡 API Endpoints
**Documentation display:**
- All API endpoints listed
- Request methods shown
- Bot commands reference
- Link to full API docs

**Available Endpoints:**
```
GET  /api/intelligence/search?q=keyword
GET  /api/intelligence/document?path=file.md
GET  /api/intelligence/tree?path=directory
GET  /api/intelligence/stats
POST /api/intelligence/scan
```

### Tab 4: 📊 Statistics
**Real-time metrics:**
- Total files in intelligence directory
- Total storage size
- Monitored servers count
- Last scan timestamp
- Refresh button for latest data

**Data Source:**
- Counts files in `/intelligence` directory
- Calculates actual sizes
- Reads scan log for timestamps
- Server-by-server breakdown available

---

## 🔧 Current Status

### ✅ Working Features
- [x] Control panel accessible at URL
- [x] Cron Management tab fully functional
- [x] Server status cards with real data
- [x] Console output for all operations
- [x] Neural Scanner interface
- [x] Statistics with actual file counts
- [x] API documentation display
- [x] Auto-refresh every 30 seconds
- [x] AJAX backend integration
- [x] Error handling and logging
- [x] Visual loading states
- [x] Responsive design

### ⏳ Needs Configuration (Optional for Full Features)
- [ ] CIS API key - For remote CIS Portal operations
- [ ] Smart cron schedule - Initialize with one command

### 🎯 Already Showing
- **Intelligence Hub:** 19 crontab entries detected and displayed
- **CIS Portal:** Registered (shows "Error" without API key - expected)
- **Statistics:** Real file counts from intelligence directory
- **Console:** Live output with timestamps

---

## 🚀 Test It Right Now

### 1. Open Control Panel
```
Visit: https://gpt.ecigdis.co.nz/intelligence_control_panel.php
```

### 2. Watch It Load
- Cron Management tab shows automatically
- Status fetches from Universal Cron Controller
- Server cards display with metrics
- Console shows "Ready" message

### 3. Try Actions
**Click "🔄 Refresh Status":**
- Fetches latest data
- Updates server cards
- Logs to console

**Click "📋 View Tasks" on Intelligence Hub:**
- Shows 19 crontab entries
- Displays in console
- Intelligence engine, security scanner, call graph, etc.

**Switch to "Neural Scanner" Tab:**
- See 4 server scan options
- Click "Scan" on any server
- Watch console for results

**Switch to "Statistics" Tab:**
- See current file counts
- Total storage used
- Click "Refresh" for latest

---

## 📊 What Data You'll See

### Intelligence Hub Card
```
📡 Intelligence Hub (Master GPT Core)

Tasks: X (Y enabled)    [19 crontab + 0 smart cron]
Running: 0
Failed (24h): 0

[📋 View Tasks]  [📄 View Logs]
```

### CIS Portal Card
```
📡 CIS Portal (staff.vapeshed.co.nz)

Tasks: 0 (0 enabled)    [API key needed]
Running: 0
Failed (24h): 0
Last Run: Error         [Expected without API key]

[📋 View Tasks]  [📄 View Logs]
```

### System Overview
```
Managing X tasks across 2 servers. Y tasks currently enabled.

📊 System Overview
Total Servers: 2
Total Tasks: X
Enabled: Y
```

### Console Output (Example)
```
[14:23:45] 🚀 Universal Cron Controller - Ready
[14:23:45] 📡 Waiting for commands...
[14:23:50] 📡 Fetching server status...
[14:23:52] ✅ Server status updated successfully
[14:24:10] 📡 Loading tasks for intelligence_hub...
[14:24:12] ✅ Tasks loaded for intelligence_hub
```

---

## 🎯 Behind the Scenes

### Backend Flow
1. **Page Load** → Triggers `refreshCronStatus()`
2. **AJAX Call** → `?action=get_cron_data`
3. **PHP Executes** → `php universal_cron_controller.php status`
4. **Parse Output** → Extract server data
5. **Return JSON** → Structured response
6. **Update UI** → Server cards, overview, console
7. **Auto-Refresh** → Every 30 seconds

### Data Sources
- **Cron Status:** `universal_cron_controller.php status`
- **Task Lists:** `universal_cron_controller.php list --server=ID`
- **Logs:** `universal_cron_controller.php logs --server=ID`
- **Intelligence Stats:** File system scan of `/intelligence`
- **Neural Scanner:** `/api/intelligence/scan` endpoint

### Error Handling
- Network errors logged to console
- API failures shown as alerts
- Missing data displays as "Error" or "-"
- Graceful degradation (partial data shown)
- Retry mechanisms built-in

---

## 🔑 Optional Configuration

### Set CIS API Key (For Full CIS Portal Control)

**Option A: Environment Variable**
```bash
export CIS_API_KEY="your_api_key_here"
```

**Option B: Config File**
```bash
echo "your_api_key_here" > /home/master/applications/jcepnzzkmj/public_html/_kb/config/api_key.txt
```

**Then Test:**
```bash
php /home/master/applications/hdgwrzntwa/public_html/_kb/scripts/universal_cron_controller.php list --server=cis_portal
```

**Result:** CIS Portal card will show actual tasks instead of "Error"

### Initialize Smart Cron Schedule (For Smart Cron Tasks)

**Run Once:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/_kb/scripts
php smart_cron_manager.php
```

**Result:** Creates schedule file with default tasks (Intelligence refresh, Push to CIS, etc.)

---

## 🎉 Key Improvements Over V1

| Feature | V1 (Old) | V2 (New) |
|---------|----------|----------|
| Cron Management | ❌ Not present | ✅ Full God Mode control |
| Server Status | ❌ Static/fake | ✅ Real-time from controllers |
| Neural Scanner | ❌ Placeholder buttons | ✅ Working API integration |
| Statistics | ❌ Hardcoded numbers | ✅ Real file counts & sizes |
| Console | ❌ Not present | ✅ Live output with timestamps |
| Backend | ❌ No functionality | ✅ PHP handlers + AJAX |
| Navigation | ❌ One page | ✅ 4 organized tabs |
| Updates | ❌ Manual refresh | ✅ Auto-refresh every 30s |
| Error Handling | ❌ None | ✅ Graceful with messages |
| User Feedback | ❌ None | ✅ Loading states, alerts |

---

## 📚 Related Documentation

- **Full Setup Guide:** `_kb/CONTROL_PANEL_V2_COMPLETE.md` (this file's parent)
- **Universal Cron Docs:** `_kb/UNIVERSAL_CRON_COMPLETE.md`
- **Cron Quick Reference:** `_kb/UNIVERSAL_CRON_QUICKSTART.md`
- **Smart Cron Guide:** `_kb/docs/SMART_CRON_SETUP_GUIDE.md`

---

## 💡 Pro Tips

1. **Auto-Refresh:** Leave tab open, it updates every 30 seconds automatically
2. **Console History:** Scroll down in console to see full operation log
3. **Server Cards:** Color-coded (red border if failures detected)
4. **Multiple Tabs:** Each tab loads data only when active (performance)
5. **Quick Diagnosis:** Check console for error details if something fails
6. **Direct API:** Can also call `/api/intelligence/` endpoints directly
7. **Command Line:** All operations available via CLI too (universal_cron_controller.php)

---

## 🚨 Troubleshooting

**"No servers found"**
- Controller script may not be executable
- Run: `chmod +x /home/master/applications/hdgwrzntwa/public_html/_kb/scripts/universal_cron_controller.php`

**"CIS Portal shows Error"**
- Expected without API key
- Set API key to enable full remote control

**"Stats show 0 files"**
- Intelligence directory may be empty
- Run neural scanner to populate

**"Console empty"**
- JavaScript may be disabled
- Check browser console for errors

**"Auto-refresh not working"**
- Make sure tab is active
- Check network tab for API calls

---

## ✅ Success Checklist

When you access the control panel, you should see:

- [x] Page loads with 4 tabs
- [x] "Cron Management" tab active by default
- [x] Server cards for Intelligence Hub and CIS Portal
- [x] System overview with totals
- [x] Console showing "Ready" and initial commands
- [x] Green status badge saying "OPERATIONAL"
- [x] Click "View Tasks" shows 19 crontab entries
- [x] Stats tab shows file counts
- [x] Scanner tab has 4 scan buttons
- [x] API tab shows endpoint documentation

**If all checked:** ✅ **YOU'RE GOOD TO GO!**

---

**Access Now:** https://gpt.ecigdis.co.nz/intelligence_control_panel.php

**Status:** 🟢 OPERATIONAL  
**Version:** 2.0.0  
**Updated:** 2025-10-25

**🎯 Placeholder content: ELIMINATED**  
**🎯 Real functionality: INTEGRATED**  
**🎯 Production ready: YES**
