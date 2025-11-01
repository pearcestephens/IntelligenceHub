# 📚 FRONTEND TOOLS - COMPLETE FUNCTION DOCUMENTATION

**Version:** 2.0.0  
**Date:** October 27, 2025  
**For:** All Bots and Developers

---

## 🎯 **MAIN ENTRY POINTS**

### **1. test-website** (Primary Bot Interface)
**Location:** `/frontend-tools/test-website`  
**Type:** Bash script (executable)  
**Purpose:** Main bot-friendly wrapper for all website testing

#### **Function Signature:**
```bash
./test-website <URL> [OPTIONS]
```

#### **Parameters:**
- **URL** (required): Full website URL to test
  - Format: `https://domain.com` or `https://domain.com/path`
  - Examples: `https://staff.vapeshed.co.nz`, `https://gpt.ecigdis.co.nz/dashboard`

#### **Options:**
- `--deep`: Deep comprehensive scan (2-5 minutes)
- `--endpoints`: Focus on API/endpoint testing
- `--vision`: AI visual analysis with GPT-4 Vision
- `--mobile`: Use mobile viewport (375x667)
- `--all`: Run all tests (5-10 minutes)
- `--auto-detect <context>`: Smart mode detection from user context
- `--help`: Show usage information

#### **Return Values:**
- **Exit Code 0:** Test completed successfully
- **Exit Code 1:** Invalid URL or missing parameters
- **Exit Code 2:** Test execution failed

#### **Output Files:**
- `reports/test_TIMESTAMP/index.html` - Interactive report
- `reports/test_TIMESTAMP/SUMMARY.md` - Quick text summary
- `reports/test_TIMESTAMP/full_crawl_data.json` - Complete data
- `reports/test_TIMESTAMP/screenshots/` - All captured images

#### **Examples:**
```bash
# Quick test (30 seconds)
./test-website https://staff.vapeshed.co.nz

# Deep scan with everything
./test-website https://staff.vapeshed.co.nz --deep

# API endpoint testing
./test-website https://gpt.ecigdis.co.nz --endpoints

# AI visual analysis
./test-website https://gpt.ecigdis.co.nz --vision

# Mobile viewport test
./test-website https://staff.vapeshed.co.nz --mobile

# Auto-detect mode from context
./test-website https://example.com --auto-detect "user said: check all the API endpoints"
```

---

## 🕷️ **DEEP CRAWLER FUNCTIONS**

### **2. deep-crawler.js** (Core Engine)
**Location:** `/frontend-tools/scripts/deep-crawler.js`  
**Type:** Node.js script  
**Purpose:** Comprehensive webpage analysis and interaction testing

#### **Function Signature:**
```bash
node scripts/deep-crawler.js --url=<URL> [OPTIONS]
```

#### **Parameters:**
- **--url, -u** (required): Target URL to crawl
- **--output, -o**: Output directory (default: `../reports`)
- **--viewport, -v**: Viewport size (default: `desktop`)
  - Presets: `desktop`, `laptop`, `tablet`, `mobile`
  - Custom: `WIDTHxHEIGHT` (e.g., `1366x768`)
- **--wait, -w**: Wait time after page load in ms (default: `2000`)
- **--slow-mo**: Slow motion delay for debugging (default: `0`)
- **--max-depth**: Maximum crawl depth for links (default: `2`)

#### **Interaction Options:**
- **--click-all-buttons**: Auto-click every button and capture state
- **--click-all-links**: Auto-click every link and navigate
- **--fill-forms**: Attempt to fill and submit forms
- **--crawl-links**: Follow links and crawl entire site

#### **What It Captures:**
```javascript
// Network Data
{
  requests: [
    {
      method: "GET|POST|PUT|DELETE",
      url: "https://...",
      headers: {},
      postData: "...",
      timing: { start: 1234, end: 5678 },
      status: 200,
      response: {
        headers: {},
        body: "...",
        timing: 456
      }
    }
  ]
}

// Console Logs
{
  consoleLogs: [
    {
      type: "log|warn|error|info",
      message: "Console message",
      timestamp: "2025-10-27T14:30:15.123Z",
      location: "file.js:123:45"
    }
  ]
}

// JavaScript Errors
{
  errors: [
    {
      message: "TypeError: Cannot read property...",
      source: "file.js",
      line: 123,
      column: 45,
      stack: "Full stack trace...",
      timestamp: "2025-10-27T14:30:15.123Z"
    }
  ]
}

// Performance Metrics
{
  performance: {
    loadTime: 2345,
    firstContentfulPaint: 1234,
    largestContentfulPaint: 2000,
    timeToInteractive: 2500,
    domContentLoaded: 1500,
    resourceCount: 42,
    transferSize: 1234567
  }
}

// DOM Analysis
{
  dom: {
    elementCount: 1234,
    buttonCount: 45,
    linkCount: 67,
    formCount: 3,
    imageCount: 23,
    scriptCount: 12,
    stylesheetCount: 8
  }
}
```

#### **Examples:**
```bash
# Basic crawl
node scripts/deep-crawler.js --url="https://staff.vapeshed.co.nz"

# Full interaction testing
node scripts/deep-crawler.js \
  --url="https://staff.vapeshed.co.nz" \
  --click-all-buttons \
  --click-all-links \
  --viewport=desktop

# Mobile debug mode
node scripts/deep-crawler.js \
  --url="https://staff.vapeshed.co.nz" \
  --viewport=mobile \
  --slow-mo=1000 \
  --wait=3000

# Site crawling
node scripts/deep-crawler.js \
  --url="https://staff.vapeshed.co.nz" \
  --crawl-links \
  --max-depth=3
```

---

## 👁️ **GPT VISION ANALYZER FUNCTIONS**

### **3. gpt-vision-analyzer.js** (AI Analysis)
**Location:** `/frontend-tools/scripts/gpt-vision-analyzer.js`  
**Type:** Node.js script  
**Purpose:** AI-powered visual UI/UX analysis using GPT-4 Vision

#### **Function Signature:**
```bash
node scripts/gpt-vision-analyzer.js --url=<URL> [OPTIONS]
```

#### **Parameters:**
- **--url, -u** (required): Target URL to analyze
- **--prompt, -p**: Custom analysis prompt (default: comprehensive UI/UX analysis)
- **--openai-key, -k**: OpenAI API key (or use `OPENAI_API_KEY` env var)
- **--viewport, -v**: Viewport size (default: `1920x1080`)
- **--output, -o**: Output directory (default: `../reports`)
- **--wait, -w**: Wait time after page load (default: `3000`)

#### **AI Analysis Areas:**
```javascript
{
  analysis: {
    visualDesign: {
      score: 8.5,  // 1-10 rating
      strengths: ["Clean layout", "Good color contrast"],
      issues: ["Inconsistent spacing", "Too many fonts"]
    },
    usability: {
      score: 7.2,
      strengths: ["Clear navigation", "Logical flow"],
      issues: ["Small click targets", "Confusing forms"]
    },
    accessibility: {
      score: 6.8,
      strengths: ["Alt text present", "Semantic markup"],
      issues: ["Poor color contrast", "Missing ARIA labels"]
    },
    responsiveDesign: {
      score: 8.0,
      strengths: ["Mobile-first approach", "Flexible layouts"],
      issues: ["Horizontal scroll on mobile", "Touch targets too small"]
    },
    performance: {
      score: 7.5,
      strengths: ["Fast load time", "Optimized images"],
      issues: ["Too many HTTP requests", "Large CSS files"]
    }
  },
  recommendations: [
    {
      priority: "HIGH",
      category: "accessibility",
      issue: "Color contrast too low on buttons",
      solution: "Increase contrast ratio to 4.5:1 minimum",
      effort: "30 minutes"
    }
  ]
}
```

#### **Custom Prompts:**
```bash
# CSS-focused analysis
node scripts/gpt-vision-analyzer.js \
  --url="https://staff.vapeshed.co.nz" \
  --prompt="Focus on CSS architecture, specificity, and maintainability"

# Mobile-specific analysis
node scripts/gpt-vision-analyzer.js \
  --url="https://staff.vapeshed.co.nz" \
  --viewport=mobile \
  --prompt="Analyze mobile usability, touch targets, and responsive design"

# Accessibility audit
node scripts/gpt-vision-analyzer.js \
  --url="https://staff.vapeshed.co.nz" \
  --prompt="Comprehensive WCAG 2.1 AA accessibility audit"
```

---

## 📊 **NPM SCRIPT FUNCTIONS**

### **4. NPM Scripts** (Package.json Aliases)
**Location:** `/frontend-tools/package.json`  
**Type:** NPM scripts  
**Purpose:** Easy access shortcuts for common testing scenarios

#### **Quick Access Scripts:**
```bash
# Main testing scripts
npm run test-website -- <URL>       # Calls ./test-website
npm run test-site -- <URL>          # Alias for test-website
npm run check-site -- <URL>         # Alias for test-website
npm run scan-page -- <URL>          # Alias for test-website

# Mode-specific shortcuts
npm run check-endpoints -- <URL>    # Calls ./test-website --endpoints
npm run test-mobile -- <URL>        # Calls ./test-website --mobile
npm run deep-scan -- <URL>          # Calls ./test-website --deep
npm run quick-test -- <URL>         # Calls ./test-website (default)
```

#### **Direct Tool Access:**
```bash
# Core tools
npm run crawl -- --url=<URL>                    # Deep crawler
npm run crawl:full -- --url=<URL>              # Deep crawler with all interactions
npm run crawl:buttons -- --url=<URL>           # Click all buttons
npm run analyze:vision -- --url=<URL>          # GPT Vision analysis

# Legacy scripts (still available)
npm run audit -- --url=<URL>                   # Quick page audit
npm run test:ui -- --url=<URL>                 # UI tester
npm run screenshot -- --url=<URL>              # Screenshot tool
```

---

## 🔧 **UTILITY FUNCTIONS**

### **5. Report Generation Functions**

#### **generateReport(crawlData, outputDir)**
**Purpose:** Generate interactive HTML report from crawl data  
**Parameters:**
- `crawlData`: Complete crawl data object
- `outputDir`: Directory to save report files

**Output Files:**
- `index.html`: Interactive report with tabs
- `SUMMARY.md`: Text summary
- `full_crawl_data.json`: Complete data export

#### **generateScreenshotGallery(screenshots, outputDir)**
**Purpose:** Create screenshot gallery with timestamps  
**Parameters:**
- `screenshots`: Array of screenshot file paths
- `outputDir`: Output directory

#### **analyzeDOMStructure(page)**
**Purpose:** Extract DOM structure and element counts  
**Returns:**
```javascript
{
  elementCount: 1234,
  buttonCount: 45,
  linkCount: 67,
  formCount: 3,
  imageCount: 23,
  headingStructure: ["h1", "h2", "h2", "h3"],
  interactiveElements: [
    { type: "button", text: "Submit", selector: "#submit-btn" },
    { type: "link", text: "Home", href: "/", selector: "a[href='/']" }
  ]
}
```

#### **captureNetworkRequests(page)**
**Purpose:** Monitor and capture all network activity  
**Returns:**
```javascript
{
  requests: [
    {
      method: "GET",
      url: "https://example.com/api/data",
      headers: {},
      timing: { start: 1234, duration: 456 },
      response: {
        status: 200,
        headers: {},
        body: "...",
        fromCache: false
      }
    }
  ],
  failedRequests: [
    {
      url: "https://example.com/missing.js",
      error: "404 Not Found",
      timing: { start: 1234, duration: 123 }
    }
  ]
}
```

#### **captureConsoleMessages(page)**
**Purpose:** Capture all console output with timestamps  
**Returns:**
```javascript
{
  messages: [
    {
      type: "error",
      text: "Uncaught TypeError: Cannot read property 'value' of null",
      location: "app.js:123:45",
      timestamp: "2025-10-27T14:30:15.123Z",
      stack: "Full stack trace..."
    }
  ]
}
```

---

## 🎨 **VIEWPORT FUNCTIONS**

### **6. Viewport Management**

#### **setViewport(page, viewportSpec)**
**Purpose:** Set browser viewport size and device characteristics  
**Parameters:**
- `page`: Puppeteer page object
- `viewportSpec`: Viewport specification

**Supported Presets:**
```javascript
const VIEWPORTS = {
  desktop: { width: 1920, height: 1080, deviceScaleFactor: 1 },
  laptop: { width: 1366, height: 768, deviceScaleFactor: 1 },
  tablet: { width: 768, height: 1024, deviceScaleFactor: 2 },
  mobile: { width: 375, height: 667, deviceScaleFactor: 2, isMobile: true, hasTouch: true }
};
```

**Custom Viewport:**
```javascript
// Custom size: "1440x900"
setViewport(page, "1440x900");

// Results in:
{
  width: 1440,
  height: 900,
  deviceScaleFactor: 1
}
```

---

## 📈 **PERFORMANCE FUNCTIONS**

### **7. Performance Monitoring**

#### **capturePerformanceMetrics(page)**
**Purpose:** Capture comprehensive performance data  
**Returns:**
```javascript
{
  timing: {
    navigationStart: 1635123456789,
    domContentLoaded: 1635123457234,  // +445ms
    loadComplete: 1635123458123,      // +1334ms
    firstPaint: 1635123457012,        // +223ms
    firstContentfulPaint: 1635123457234, // +445ms
    largestContentfulPaint: 1635123458000, // +1211ms
    timeToInteractive: 1635123458500  // +1711ms
  },
  resources: {
    total: 42,
    scripts: 8,
    stylesheets: 4,
    images: 23,
    fonts: 3,
    other: 4
  },
  sizes: {
    totalTransferSize: 1234567,  // bytes
    totalDecodedSize: 2345678,   // bytes
    largestResource: {
      url: "https://example.com/large-image.jpg",
      transferSize: 567890,
      type: "image"
    }
  },
  coreWebVitals: {
    LCP: 1211,  // Largest Contentful Paint (ms)
    FID: 45,    // First Input Delay (ms)
    CLS: 0.05   // Cumulative Layout Shift
  }
}
```

---

## 🔍 **ACCESSIBILITY FUNCTIONS**

### **8. Accessibility Analysis**

#### **runAccessibilityAudit(page)**
**Purpose:** Comprehensive accessibility audit using aXe-core  
**Returns:**
```javascript
{
  violations: [
    {
      id: "color-contrast",
      impact: "serious",
      description: "Elements must have sufficient color contrast",
      nodes: [
        {
          target: ["#submit-button"],
          html: "<button id='submit-button'>Submit</button>",
          failureSummary: "Fix any of the following: Element has insufficient color contrast ratio of 2.93 (foreground color: #666666, background color: #cccccc)"
        }
      ]
    }
  ],
  passes: [
    {
      id: "alt-text",
      description: "Images must have alternate text",
      nodes: 23
    }
  ],
  incomplete: [],
  summary: {
    violations: 5,
    passes: 18,
    incomplete: 0,
    score: 78  // Accessibility score out of 100
  }
}
```

---

## 🗂️ **FILE MANAGEMENT FUNCTIONS**

### **9. Output File Management**

#### **createOutputDirectory(baseDir, timestamp)**
**Purpose:** Create timestamped output directory structure  
**Returns:** `reports/test_20251027_143052/`

**Directory Structure:**
```
reports/test_TIMESTAMP/
├── index.html              # Main interactive report
├── SUMMARY.md              # Quick text summary
├── full_crawl_data.json    # Complete JSON data
├── page_source.html        # Full HTML source
├── screenshots/            # All captured images
│   ├── initial_load.png
│   ├── button_1_before.png
│   ├── button_1_after.png
│   └── final_state.png
├── network/                # Network data
│   ├── requests.har        # HAR file
│   └── failed_requests.json
└── console/                # Console logs
    ├── errors.json
    ├── warnings.json
    └── all_messages.json
```

#### **saveReport(data, outputDir, format)**
**Purpose:** Save report in specified format  
**Parameters:**
- `data`: Report data object
- `outputDir`: Output directory path
- `format`: `'html'|'json'|'markdown'|'all'`

---

## ⚙️ **CONFIGURATION FUNCTIONS**

### **10. Configuration Management**

#### **loadConfig(configFile)**
**Purpose:** Load configuration from file or environment  
**Default Locations:**
- `/frontend-tools/.env`
- `/frontend-tools/config.json`
- Environment variables

**Configuration Options:**
```javascript
{
  openai: {
    apiKey: "sk-...",
    model: "gpt-4-vision-preview",
    maxTokens: 4000
  },
  puppeteer: {
    headless: true,
    slowMo: 0,
    defaultViewport: { width: 1920, height: 1080 },
    args: ["--no-sandbox", "--disable-setuid-sandbox"]
  },
  output: {
    baseDir: "./reports",
    includeScreenshots: true,
    includeSourceCode: true,
    compressOldReports: true,
    maxReportAge: "30d"
  },
  performance: {
    waitAfterLoad: 2000,
    maxWaitForElement: 5000,
    screenshotQuality: 90,
    fullPageScreenshots: true
  }
}
```

---

## 🚨 **ERROR HANDLING FUNCTIONS**

### **11. Error Management**

#### **handlePageError(error, context)**
**Purpose:** Standardized error handling and logging  
**Parameters:**
- `error`: Error object or message
- `context`: Context information (URL, action, etc.)

**Error Types Handled:**
- Navigation timeouts
- Element not found
- JavaScript runtime errors
- Network failures
- Screenshot capture failures
- File write errors

**Error Response Format:**
```javascript
{
  success: false,
  error: {
    type: "NAVIGATION_TIMEOUT",
    message: "Page failed to load within 30 seconds",
    url: "https://example.com",
    timestamp: "2025-10-27T14:30:15.123Z",
    context: {
      action: "page.goto",
      timeout: 30000,
      userAgent: "Mozilla/5.0..."
    }
  },
  partial: {
    // Any data captured before failure
    screenshots: ["initial_load.png"],
    consoleLogs: [...],
    timing: { start: 1234, failedAt: 5678 }
  }
}
```

---

## 📋 **RETURN VALUE STANDARDS**

### **12. Standardized Response Formats**

#### **Success Response:**
```javascript
{
  success: true,
  data: {
    // Main result data
  },
  meta: {
    timestamp: "2025-10-27T14:30:15.123Z",
    duration: 1234,  // milliseconds
    url: "https://example.com",
    viewport: "desktop",
    version: "2.0.0"
  },
  files: {
    report: "reports/test_20251027_143052/index.html",
    summary: "reports/test_20251027_143052/SUMMARY.md",
    data: "reports/test_20251027_143052/full_crawl_data.json",
    screenshots: ["reports/test_20251027_143052/screenshots/..."]
  }
}
```

#### **Error Response:**
```javascript
{
  success: false,
  error: {
    code: "ERROR_CODE",
    message: "Human-readable error message",
    details: "Technical details for debugging",
    timestamp: "2025-10-27T14:30:15.123Z"
  },
  partial: {
    // Any data captured before failure
  }
}
```

---

## 🎯 **USAGE EXAMPLES FOR BOTS**

### **Complete Bot Workflow:**

```bash
# 1. Quick test (most common)
cd frontend-tools
./test-website https://staff.vapeshed.co.nz

# 2. Check results
if [ -f "reports/test_*/SUMMARY.md" ]; then
  echo "✅ Test completed successfully"
  head -20 reports/test_*/SUMMARY.md
else
  echo "❌ Test failed"
fi

# 3. Deep scan if needed
./test-website https://staff.vapeshed.co.nz --deep

# 4. Endpoint testing
./test-website https://gpt.ecigdis.co.nz --endpoints

# 5. AI visual analysis
./test-website https://gpt.ecigdis.co.nz --vision
```

### **Error Handling:**
```bash
# Check dependencies first
if ! command -v node &> /dev/null; then
  echo "❌ Node.js not found"
  exit 1
fi

# Run test with error handling
if ./test-website "$URL"; then
  echo "✅ Test completed"
  # Process results
else
  echo "❌ Test failed"
  # Check logs, suggest fixes
fi
```

---

## 📝 **SUMMARY FOR BOTS**

**Primary Function:** `./test-website <URL> [--mode]`

**Common Modes:**
- Default: Quick 30-second test
- `--deep`: Comprehensive 2-5 minute test  
- `--endpoints`: API/endpoint focused testing
- `--vision`: AI visual analysis
- `--mobile`: Mobile viewport testing

**Always Returns:**
- Interactive HTML report
- Quick markdown summary
- Complete JSON data
- Screenshots gallery
- Error logs (if any)

**All functions are documented, tested, and ready for bot use!** 🤖