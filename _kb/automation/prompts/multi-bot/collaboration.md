# Multi-Bot Collaboration Session

## 🤖 Starting a Multi-Bot Conversation:

```
@workspace Start multi-bot collaboration:
- Topic: [SPECIFIC_TASK/PROJECT]
- Bots needed: [architect, security, api, frontend, etc.]
- Goal: [WHAT_WE_WANT_TO_ACHIEVE]

Set up shared context for bot collaboration.
```

## 🔗 **Bot Roles Available:**

### 🏗️ **Architect Bot**
```
@workspace #file:_automation/prompts/multi-bot/architect.md
Focus on system design, module structure, and architectural decisions
```

### 🔒 **Security Bot**  
```
@workspace #file:_automation/prompts/multi-bot/security.md
Review for security issues, vulnerabilities, and compliance
```

### 🔧 **API Bot**
```
@workspace #file:_automation/prompts/multi-bot/api.md  
Handle API design, endpoints, and integration patterns
```

### 🎨 **Frontend Bot**
```
@workspace #file:_automation/prompts/multi-bot/frontend.md
Focus on UI/UX, responsive design, and user experience
```

### 🗄️ **Database Bot**
```
@workspace #file:_automation/prompts/multi-bot/database.md
Handle schema design, optimization, and data integrity
```

## 🔄 **Conversation Flow:**

### 1. **Initialize Session**
```
@workspace I need multiple bots to collaborate on [PROJECT]:

**Architect Bot:** Design the module structure
**Security Bot:** Review for vulnerabilities  
**API Bot:** Design the endpoints
**Frontend Bot:** Plan the UI components

Topic: [SPECIFIC_FEATURE]
Goal: [END_RESULT]
```

### 2. **Each Bot Contributes**
```
@workspace [BOT_ROLE] perspective on [TOPIC]:
- My analysis: [FINDINGS]
- Recommendations: [SUGGESTIONS]
- Dependencies: [WHAT_OTHER_BOTS_NEED]
- Next steps: [ACTIONS]
```

### 3. **Cross-Bot References**
```
@workspace Building on [OTHER_BOT]'s suggestion about [TOPIC]:
- I agree with [SPECIFIC_POINT]
- I would modify [ASPECT] because [REASON]
- This affects my [AREA] in [WAY]
```

### 4. **Consensus Building**
```
@workspace Multi-bot consensus check:
- Architect Bot says: [SUMMARY]
- Security Bot says: [SUMMARY]  
- API Bot says: [SUMMARY]
- Frontend Bot says: [SUMMARY]

Final recommendation: [AGREED_APPROACH]
```

## 📋 **Session Management:**

### **Join Existing Session**
```
@workspace Join multi-bot session [SESSION_ID] as [BOT_ROLE]:
- Review previous conversation
- Add my perspective on [TOPIC]
- Build on other bots' contributions
```

### **Session Summary**
```
@workspace Summarize multi-bot session:
- Decisions made by each bot
- Areas of agreement/disagreement
- Final recommendations
- Action items for implementation
```

## 🎯 **Example Multi-Bot Conversation:**

**User:** "I need to build a new inventory reporting module"

**Architect Bot:** 
```
@workspace System design for inventory reporting:
- Module: modules/reports/inventory/
- Structure: MVC pattern following base/shared
- Dependencies: modules/inventory/, modules/base/
```

**Security Bot:**
```
@workspace Building on Architect's design:
- Add permission checks for report access
- Sanitize all filter inputs
- Log all report generation activities
```

**API Bot:**
```
@workspace API design for inventory reports:
- GET /api/reports/inventory (with filters)
- POST /api/reports/inventory/generate
- Pagination for large datasets
- Export formats: CSV, PDF, Excel
```

**Frontend Bot:**
```
@workspace UI for inventory reports:
- Filter interface with date ranges
- Real-time progress indicator
- Export button with format selection
- Responsive design for mobile access
```

**Final Integration:**
```
@workspace Multi-bot consensus:
All bots agree on modular approach with security-first design,
comprehensive API, and user-friendly interface.
Proceed with implementation following all recommendations.
```