# 🤖 BOT WEBSITE TESTING - STANDARD RESPONSES

## When User Says: "Test the website" or "Check the site"

**Response Template:**
```
I'll test the website using Frontend Tools (our comprehensive testing system).

Running: cd frontend-tools && ./test-website [URL]

This will capture:
✅ Console errors & warnings
✅ Network requests & failures  
✅ JavaScript errors
✅ Performance metrics
✅ Screenshots
✅ Basic accessibility check

[Execute command here]

Results ready! Check:
- 📊 Interactive report: [path]/index.html
- 📄 Quick summary: [path]/SUMMARY.md
- 🚨 [Number] errors found, [Number] warnings

Key findings: [Brief 2-3 line summary]
```

## When User Says: "Deep scan" or "Comprehensive check"

**Command:** `./test-website URL --deep`

**Response Template:**
```
Running comprehensive deep scan (2-5 minutes)...

This captures EVERYTHING:
✅ All network requests (HAR format)
✅ Complete console log history
✅ Button interaction testing
✅ Full DOM analysis
✅ Performance breakdown
✅ Screenshot gallery
✅ Error stack traces

[Execute command]

Deep analysis complete! 
📊 [Number] requests captured
🖱️ [Number] buttons tested  
📸 [Number] screenshots taken
🚨 [Number] issues detected

Report: [Interactive HTML path]
```

## When User Says: "Check endpoints" or "Test APIs"

**Command:** `./test-website URL --endpoints`

**Response Template:**
```
Testing API endpoints and network calls...

Focus areas:
✅ API endpoint discovery
✅ Response validation
✅ Error handling
✅ Network timing
✅ Failed requests

[Execute command]

Endpoint analysis complete!
🔌 [Number] endpoints found
⚡ Average response: [X]ms
❌ [Number] failed requests
✅ [Number] successful calls

Issues: [Brief list of API problems]
```

## When User Says: "Check the design" or "Analyze UI"

**Command:** `./test-website URL --vision`

**Response Template:**
```
Running AI-powered visual analysis with GPT-4 Vision...

Analysis includes:
✅ UI/UX quality scoring
✅ CSS architecture review
✅ Accessibility compliance
✅ Design recommendations
✅ Color contrast checking
✅ Mobile responsiveness

[Execute command]

AI analysis complete!
🎨 Design score: [X]/10
♿ Accessibility: [X]/10
📱 Mobile ready: [X]/10

Top recommendations:
1. [Specific fix]
2. [Specific fix]
3. [Specific fix]

Full report: [Report path]
```

## When User Says: "Test mobile" or "Check responsiveness"

**Command:** `./test-website URL --mobile`

**Response Template:**
```
Testing mobile viewport (375x667)...

Mobile-specific checks:
✅ Touch target sizes
✅ Responsive layout
✅ Mobile navigation
✅ Performance on mobile
✅ Text readability

[Execute command]

Mobile test complete!
📱 Viewport: 375x667 (iPhone)
⚡ Load time: [X]s
🎯 Touch targets: [Pass/Fail]
📖 Text readable: [Pass/Fail]

Issues found: [Mobile-specific problems]
```

## When User Says: "Full analysis" or "Everything"

**Command:** `./test-website URL --all`

**Response Template:**
```
Running complete analysis suite (5-10 minutes)...

This includes:
✅ Deep crawling + button testing
✅ Mobile + desktop testing
✅ AI visual analysis
✅ Complete documentation
✅ Performance benchmarking

[Execute command]

Complete analysis finished!
📊 Desktop + Mobile reports generated
🤖 AI recommendations included
📈 Performance metrics captured
🛡️ Security scan completed

Master report: [Main report path]
Mobile report: [Mobile report path]
AI analysis: [AI report path]
```

## Error Handling Templates

### If test-website script not found:
```
Setting up Frontend Tools first...

cd frontend-tools
npm install
chmod +x test-website

Now running test: ./test-website [URL]
```

### If URL invalid:
```
❌ Invalid URL provided. 

Please provide a full URL like:
- https://staff.vapeshed.co.nz
- https://gpt.ecigdis.co.nz/dashboard
- https://api.example.com

Try: ./test-website https://example.com
```

### If test fails:
```
❌ Test failed. Checking logs...

Common fixes:
1. Check if site is accessible: curl -I [URL]
2. Verify NPM dependencies: cd frontend-tools && npm install
3. Check Node.js version: node --version (need v18+)

Would you like me to:
- Try with different viewport?
- Run basic connectivity test?
- Check site manually?
```

## Quick Reference for Bots

**Most Common:**
- Quick test: `cd frontend-tools && ./test-website URL`
- Deep scan: `cd frontend-tools && ./test-website URL --deep`
- Check APIs: `cd frontend-tools && ./test-website URL --endpoints`

**Always:**
1. Change to frontend-tools directory first
2. Use full HTTPS URLs
3. Check for report files after completion
4. Read SUMMARY.md for quick overview
5. Provide interactive HTML report path

**Never:**
- Skip the `cd frontend-tools` step
- Use relative URLs
- Run multiple tests simultaneously
- Ignore error output
- Forget to check generated reports