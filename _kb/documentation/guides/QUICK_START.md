# 🚀 Frontend Tools - Quick Start Guide

## 5-Minute Setup

### 1. **Install Dependencies** (if not done)
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools
npm install
```

### 2. **Set OpenAI API Key** (for GPT Vision)
```bash
export OPENAI_API_KEY="sk-your-key-here"
```

### 3. **Run Your First Test**
```bash
# Test the dashboard (basic crawl)
npm run crawl -- --url="https://gpt.ecigdis.co.nz/dashboard/"
```

---

## 🎯 Common Commands

### **Deep Crawl with Everything**
Captures: HTML, Network, Console, Screenshots, Button Clicks, Link Navigation
```bash
npm run crawl:full -- --url="https://gpt.ecigdis.co.nz/dashboard/?page=bot-standards"
```

### **Just Click All Buttons**
Perfect for testing button functionality
```bash
npm run crawl:buttons -- --url="https://gpt.ecigdis.co.nz/dashboard/"
```

### **GPT Vision Analysis**
AI-powered UI/UX feedback
```bash
npm run analyze:vision -- --url="https://gpt.ecigdis.co.nz/dashboard/"
```

### **Mobile Testing**
```bash
npm run crawl -- --url="https://gpt.ecigdis.co.nz/dashboard/" --viewport=mobile
```

---

## 📊 View Results

Reports are saved to: `reports/crawl_TIMESTAMP/`

**Open the HTML report:**
```bash
# Find latest report
ls -lt reports/ | head -5

# Open in browser (example path)
# reports/crawl_2025-10-26_04-20-00/index.html
```

**Quick stats:**
```bash
# Check JSON data
cat reports/crawl_*/full_crawl_data.json | grep -E '"totalRequests|totalErrors|pages"'
```

---

## 🎨 What Gets Captured?

### ✅ Deep Crawler Captures:
- Full HTML source code
- All network requests (with timing, headers, body)
- Console messages (all levels)
- JavaScript errors with stack traces
- Performance metrics
- Screenshots at every step:
  - Initial page load
  - Before each button click
  - After each button click
  - Before each link click
  - After navigation
  - Final state
- DOM structure analysis
- Interactive elements inventory
- Accessibility audit
- Performance timing

### 📁 Output Files:
- `index.html` - Interactive report with tabs
- `full_crawl_data.json` - Complete data
- `SUMMARY.md` - Markdown summary
- `page_X_source.html` - Full HTML of each page
- `screenshots/*.png` - All screenshots

---

## 🐛 Debug Mode

Watch the crawler work (slow motion):
```bash
node scripts/deep-crawler.js \
  --url="https://gpt.ecigdis.co.nz/dashboard/" \
  --slow-mo=1000 \
  --click-all-buttons
```

---

## 💡 Pro Tips

1. **Start with basic crawl**, then add features
2. **Use `--wait=5000`** if page loads slowly
3. **Check console output** for real-time feedback
4. **Save important reports** to named directories
5. **Use mobile viewport** to test responsive design

---

## 📞 Help

```bash
# Show all options
node scripts/deep-crawler.js --help
node scripts/gpt-vision-analyzer.js --help

# View README
cat README.md

# Check if tools are working
npm run crawl -- --url="https://example.com" --wait=2000
```

---

## 🎉 Example Workflow

```bash
# 1. Basic crawl first
npm run crawl -- --url="https://gpt.ecigdis.co.nz/dashboard/"

# 2. Review the report (open index.html)

# 3. Now test all buttons
npm run crawl:buttons -- --url="https://gpt.ecigdis.co.nz/dashboard/"

# 4. Get AI feedback
npm run analyze:vision -- --url="https://gpt.ecigdis.co.nz/dashboard/"

# 5. Test on mobile
npm run crawl -- --url="https://gpt.ecigdis.co.nz/dashboard/" --viewport=mobile
```

---

**You're ready to go! 🚀**

See `README.md` for full documentation.
