# ⚡ Quick Page Audit - Quick Reference Card

**Fast, intelligent page testing with 6 modes**

---

## 🚀 Quick Commands

```bash
# Quick mode (1-3s) - Fast error check
npm run audit -- --url="https://example.com"

# Analysis (3-5s) - With screenshot
npm run audit:analysis -- --url="https://example.com"

# UX mode (8-15s) - With GPT Vision
npm run audit:ux -- --url="https://example.com"

# API mode (0.5-2s) - Endpoint testing
npm run audit:api -- --url="https://api.example.com/endpoint"

# With auto-login (CIS profile)
npm run audit -- --url="https://staff.vapeshed.co.nz/dashboard.php" --login

# Session mode (persistent browser)
npm run audit -- --url="https://example.com" --session=new
```

---

## 📋 All Options

| Option | Short | Description | Default |
|--------|-------|-------------|---------|
| `--url` | `-u` | URL to audit | **Required** |
| `--mode` | `-m` | Audit mode | `quick` |
| `--login` | | Auto-login using profile | `false` |
| `--session` | `-s` | Session ID or "new" | None |
| `--viewport` | `-v` | Viewport size | `desktop` |
| `--method` | | HTTP method (API mode) | `GET` |
| `--data` | `-d` | JSON data for POST/PUT | None |
| `--gpt-prompt` | | Custom GPT Vision prompt | Default |
| `--output` | `-o` | Save result to file | None |

---

## 🎯 Modes

| Mode | Speed | Screenshot | GPT | API | Crawl |
|------|-------|------------|-----|-----|-------|
| `quick` | 1-3s | ❌ | ❌ | ❌ | ❌ |
| `analysis` | 3-5s | ✅ | ❌ | ❌ | ❌ |
| `ux` | 8-15s | ✅ | ✅ | ❌ | ❌ |
| `api` | 0.5-2s | ❌ | ❌ | ✅ | ❌ |
| `comprehensive` | 30-120s | ✅ | ✅ | ❌ | ✅ |
| `session` | Any | ✅ | ✅ | ✅ | ✅ |

---

## 🖥️ Viewports

- `desktop` - 1920x1080 (default)
- `laptop` - 1440x900
- `tablet` - 768x1024
- `mobile` - 375x667

---

## 🔐 CIS Auto-Login

**Pre-configured profile:**
- Site: `staff.vapeshed.co.nz`
- User: `support@vapeshed.co.nz`
- Password: Encrypted in `./profiles/.audit-profiles.json`

**Usage:**
```bash
npm run audit -- --url="https://staff.vapeshed.co.nz/dashboard.php" --login
```

**Smart login:**
1. Tries saved cookies first
2. Only logs in if needed
3. Saves cookies for 24h

---

## 📊 Output Format

```json
{
  "mode": "quick",
  "url": "https://example.com/",
  "http": {
    "status": 200,
    "redirects": 0
  },
  "errors": {
    "php": [],
    "javascript": []
  },
  "html": {
    "valid": true,
    "issues": []
  },
  "screenshot": "base64...",
  "links": [...],
  "assets": {...},
  "gpt_vision_analysis": {...},
  "duration_ms": 2881,
  "session_id": "sess_..."
}
```

---

## 🎨 Examples

### Test Dashboard Page
```bash
npm run audit:analysis -- \
  --url="https://staff.vapeshed.co.nz/dashboard.php" \
  --login \
  --output="./reports/dashboard-audit.json"
```

### API POST Request
```bash
npm run audit:api -- \
  --url="https://staff.vapeshed.co.nz/api/save.php" \
  --method=POST \
  --data='{"name":"Test","price":29.99}' \
  --login
```

### Mobile UX Review
```bash
npm run audit:ux -- \
  --url="https://staff.vapeshed.co.nz" \
  --viewport=mobile \
  --login
```

### Multi-Page Session
```bash
# Page 1
npm run audit -- \
  --url="https://staff.vapeshed.co.nz/dashboard.php" \
  --session=new \
  --login

# Page 2 (reuse session: sess_...)
npm run audit -- \
  --url="https://staff.vapeshed.co.nz/products.php" \
  --session=sess_1761521771597_f8dc4423
```

---

## 🧪 Testing

**Run all tests:**
```bash
# Test 1: Help
node scripts/quick-page-audit.js --help

# Test 2: Quick mode
npm run audit -- --url="https://example.com" --mode=quick

# Test 3: Analysis mode
npm run audit:analysis -- --url="https://example.com"

# Test 4: API mode
npm run audit:api -- --url="https://jsonplaceholder.typicode.com/posts/1"

# Test 5: CIS login
npm run audit -- --url="https://staff.vapeshed.co.nz/login.php"

# Test 6: CIS dashboard (with login)
npm run audit -- --url="https://staff.vapeshed.co.nz/dashboard.php" --login

# Test 7: Session mode
npm run audit -- --url="https://example.com" --session=new
```

---

## 🔧 Troubleshooting

**Error: Chrome not found**
```bash
# Set executablePath in script or install Chrome
which chromium
# Update script line: executablePath: '/usr/bin/chromium'
```

**Error: Session not found**
```bash
# Sessions expire after 30 min
# Create new session with --session=new
```

**Error: Login failed**
```bash
# Check profile credentials
cat ./profiles/.audit-profiles.json
# Verify site is accessible
```

---

## 📁 File Structure

```
frontend-tools/
├── scripts/
│   └── quick-page-audit.js       # Main tool (1,150 lines)
├── profiles/
│   ├── .audit-profiles.json      # CIS profile (encrypted)
│   └── .audit-profiles.key       # Encryption key
├── sessions/
│   ├── cookies/                  # Saved cookies
│   └── active/                   # Active sessions
└── reports/                      # Audit results
```

---

## 🎯 Performance Targets

| Mode | Target | Actual | Status |
|------|--------|--------|--------|
| Quick | 1-3s | 2.9s | ✅ |
| Analysis | 3-5s | 3.1s | ✅ |
| UX | 8-15s | N/T | ⏸️ |
| API | 0.5-2s | 2.7s | ⚠️ |
| Session | +1-2s | 1.8s | ✅ |

---

## 📚 Documentation

- [Full Test Report](./QUICK_PAGE_AUDIT_TEST_REPORT.md)
- [Main Script](./scripts/quick-page-audit.js)
- [Frontend Tools README](./README.md)

---

**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**Date:** October 26, 2025
