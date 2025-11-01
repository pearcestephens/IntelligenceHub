# 🎮 Interactive Crawler - Quick Start Guide

## 🚀 What You Get:

A **fully controllable** web crawler with:
- ⏸️ **Pause/Resume** - Stop and restart anytime
- 📸 **Screenshots on demand** - Capture any moment
- 💬 **Chat interface** - Control via terminal
- 🐛 **JavaScript debugger** - Run code in the page
- 📊 **Real-time status** - Know what's happening
- 🔍 **Error detection** - 404s, 500s, JS errors
- 🌐 **Navigation control** - Go anywhere
- 🖱️ **Click anything** - Remote element clicking

---

## 📦 Installation

```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools

# Already installed! Just run it.
```

---

## 🎯 Quick Start

### Step 1: Start the Interactive Crawler

**Terminal 1:**
```bash
cd frontend-tools

# Start the crawler (opens HTTP API on port 3000)
npm run crawl:interactive -- \
  -u pearce.stephens@gmail.com \
  -p 'fmsADMINED2013!!' \
  --port=3000
```

**What happens:**
- Logs into staff.vapeshed.co.nz
- Starts HTTP API server on port 3000
- Waits for your commands
- Captures everything automatically

### Step 2: Open the Chat Interface

**Terminal 2:**
```bash
cd frontend-tools

# Start the chat interface
npm run chat
```

**Now you can control the crawler through chat!**

---

## 💬 Chat Commands

### Basic Commands

```
🤖 You: status
📊 Shows current crawler state, URL, screenshot count, errors

🤖 You: pause
⏸️  Pauses the crawler (it stops and waits)

🤖 You: resume
▶️  Resumes the crawler

🤖 You: screenshot
📸 Captures a screenshot immediately

🤖 You: messages
📝 Shows recent log messages

🤖 You: errors
❌ Shows all errors (404s, 500s, JS errors)
```

### Advanced Commands

```
🤖 You: eval document.title
⚙️  Runs JavaScript in the page context
Returns: "Staff Portal - Dashboard"

🤖 You: go https://staff.vapeshed.co.nz/transfers
🌐 Navigates to a new URL

🤖 You: click button.save-btn
🖱️  Clicks the specified element

🤖 You: screenshots
📸 Lists all captured screenshots with paths
```

---

## 🌐 HTTP API Endpoints

You can also control via HTTP (useful for automation):

### Status & Control

```bash
# Get status
curl http://localhost:3000/status

# Pause
curl http://localhost:3000/pause

# Resume
curl http://localhost:3000/resume

# Stop
curl http://localhost:3000/stop
```

### Screenshots & Logs

```bash
# Capture screenshot
curl http://localhost:3000/screenshot

# Get messages
curl http://localhost:3000/messages

# Get errors
curl http://localhost:3000/errors

# List screenshots
curl http://localhost:3000/screenshots
```

### JavaScript Execution

```bash
# Run JavaScript in page context
curl -X POST http://localhost:3000/evaluate \
  -H "Content-Type: application/json" \
  -d '{"code":"document.title"}'

# Example: Get all links
curl -X POST http://localhost:3000/evaluate \
  -H "Content-Type: application/json" \
  -d '{"code":"Array.from(document.querySelectorAll(\"a\")).map(a => a.href)"}'

# Example: Check for errors on page
curl -X POST http://localhost:3000/evaluate \
  -H "Content-Type: application/json" \
  -d '{"code":"document.querySelectorAll(\".error\").length"}'
```

### Navigation & Interaction

```bash
# Navigate to URL
curl "http://localhost:3000/navigate?url=https://staff.vapeshed.co.nz/transfers"

# Click element
curl -X POST http://localhost:3000/click \
  -H "Content-Type: application/json" \
  -d '{"selector":"button.save-btn"}'
```

---

## 📊 Example Workflow

### Workflow 1: Debug a Page

**Terminal 1 (Crawler):**
```bash
npm run crawl:interactive -- -u USER -p PASS
```

**Terminal 2 (Chat):**
```
🤖 You: status
📊 Status:
  Step: waiting_for_commands
  URL: https://staff.vapeshed.co.nz/dashboard

🤖 You: go https://staff.vapeshed.co.nz/transfers
🌐 Navigating to: https://staff.vapeshed.co.nz/transfers
✅ Navigated

🤖 You: screenshot
📸 Screenshot captured: manual_screenshot_1234567890.png

🤖 You: eval document.querySelectorAll('.error').length
⚙️  Evaluating: document.querySelectorAll('.error').length
Result: 3

🤖 You: eval Array.from(document.querySelectorAll('.error')).map(e => e.innerText)
Result: ["Required field missing", "Invalid date format", "Duplicate entry"]

🤖 You: errors
❌ Errors (2):
1. JavaScript Error: Cannot read property 'value' of null
2. HTTP 404: /assets/old-icon.png
```

### Workflow 2: Test Button Interactions

```
🤖 You: pause
⏸️  Paused

🤖 You: screenshot
📸 Captured: before_click.png

🤖 You: resume
▶️  Resumed

🤖 You: click button[data-action="save"]
🖱️  Clicked: button[data-action="save"]
✅ Clicked button[data-action="save"]

🤖 You: screenshot
📸 Captured: after_click.png

🤖 You: messages
📝 Recent Messages:
  [12:34:56] [ACTION] Clicking: button[data-action="save"]
  [12:34:57] [SCREENSHOT] Screenshot captured: after_click
  [12:34:57] [NETWORK] HTTP 200: /api/save-transfer
```

### Workflow 3: Find All Broken Links

```
🤖 You: eval Array.from(document.querySelectorAll('a')).map(a => ({text: a.innerText, href: a.href}))
Result: [
  {"text":"Dashboard","href":"https://staff.vapeshed.co.nz/dashboard"},
  {"text":"Transfers","href":"https://staff.vapeshed.co.nz/transfers"},
  {"text":"Old Reports","href":"https://staff.vapeshed.co.nz/old-reports"}
]

🤖 You: go https://staff.vapeshed.co.nz/old-reports
🌐 Navigating...

🤖 You: errors
❌ Errors (1):
1. HTTP 404: https://staff.vapeshed.co.nz/old-reports
```

---

## 📁 Output Location

All data is saved to:
```
/home/master/applications/hdgwrzntwa/public_html/frontend-tools/reports/interactive_crawl_TIMESTAMP/
├── messages.log              # All log messages
├── screenshots/              # All captured screenshots
│   ├── login_page_*.png
│   ├── after_login_*.png
│   ├── manual_screenshot_*.png
│   └── after_click_*.png
```

---

## 🎯 Use Cases

### 1. **Debug Production Issues**
```
Start crawler → Navigate to problem page → 
Capture screenshot → Run diagnostics → 
Check console errors → Test fixes live
```

### 2. **Test Form Submissions**
```
Pause before submit → Inspect form data → 
Screenshot → Resume → Submit → 
Check response → Verify result
```

### 3. **Find All Errors on Site**
```
Start crawler → Let it run → 
Check errors command → 
Review 404s/500s/JS errors → 
Generate report
```

### 4. **Monitor Page Changes**
```
Navigate to page → Screenshot → 
Wait 5 minutes → Screenshot again → 
Compare images → Detect changes
```

### 5. **Extract Data**
```
Navigate to page → 
eval Array.from(document.querySelectorAll('.product')).map(p => ({
  name: p.querySelector('.name').innerText,
  price: p.querySelector('.price').innerText
}))
→ Get structured data
```

---

## 💡 Pro Tips

1. **Use `pause` before critical actions** - Lets you inspect state
2. **Always `screenshot` before/after clicks** - Visual debugging
3. **Use `eval` for quick checks** - Faster than full page load
4. **Check `errors` frequently** - Catch issues early
5. **Use `messages` to understand flow** - See what happened
6. **Save URLs in variables** - `eval window.location.href`

---

## 🐛 Troubleshooting

**Can't connect to crawler:**
```bash
# Make sure crawler is running in Terminal 1
npm run crawl:interactive -- -u USER -p PASS
```

**Crawler stuck:**
```
🤖 You: status
# Check currentStep - might be paused

🤖 You: resume
# Try resuming
```

**Need to restart:**
```
🤖 You: stop
# Stop crawler

# Then restart in Terminal 1
npm run crawl:interactive -- -u USER -p PASS
```

---

## 🎓 Advanced Usage

### Custom Port

```bash
# Start on different port
npm run crawl:interactive -- -u USER -p PASS --port=4000

# Connect chat to custom port
npm run chat -- --port=4000
```

### Remote Control

```bash
# Control from another machine
npm run chat -- --host=192.168.1.100 --port=3000
```

### Automation Script

```bash
#!/bin/bash
# auto-test.sh

# Wait for login
sleep 5

# Capture initial state
curl http://localhost:3000/screenshot

# Navigate to transfers
curl "http://localhost:3000/navigate?url=https://staff.vapeshed.co.nz/transfers"

# Wait for page load
sleep 3

# Check for errors
curl http://localhost:3000/errors > errors.json

# Generate report
curl http://localhost:3000/messages > messages.json
```

---

## 🚀 You're Ready!

**Start crawling:**
```bash
# Terminal 1
npm run crawl:interactive -- -u USER -p PASS

# Terminal 2
npm run chat
```

**Then type:** `help` to see all commands!

🎉 **Enjoy full control over your web crawler!**
