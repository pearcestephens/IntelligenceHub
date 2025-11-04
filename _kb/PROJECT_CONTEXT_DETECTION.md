# 🎯 Automatic Project & Server Context Detection

## The Problem You're Solving

**When you start a new chat with GitHub Copilot, it needs to know:**
1. **Which server** are you working on? (hdgwrzntwa, jcepnzzkmj, dvaxgvsxmz, fhrehrpjmu)
2. **Which project** within that server? (CIS Core, Consignments, Supplier Portal, etc.)
3. **Which business unit** does this belong to? (Hub, CIS, Retail, Wholesale)

**Why it matters:**
- Bots can retrieve previous conversations about the SAME project
- Context sharing across multiple bot sessions
- Build cumulative knowledge per project
- Filter search results to relevant codebase
- Track work history by project

---

## 🗺️ Your System Mapping

### Business Units (unit_id)
```
1 = Intelligence Hub (hdgwrzntwa)     - gpt.ecigdis.co.nz
2 = CIS System (jcepnzzkmj)           - staff.vapeshed.co.nz
3 = VapeShed Retail (dvaxgvsxmz)      - www.vapeshed.co.nz
4 = Wholesale Portal (fhrehrpjmu)     - www.ecigdis.co.nz
```

### Projects (project_id)
```
1  = Intelligence Hub (Scanner)       → hdgwrzntwa
2  = CIS - Consignments Module        → jcepnzzkmj/modules/consignments
3  = CIS - Supplier Portal            → jcepnzzkmj/modules/supplier
4  = CIS - Purchase Orders            → jcepnzzkmj/modules/purchase_orders
5  = CIS - Inventory Management       → jcepnzzkmj/modules/inventory
6  = CIS - Transfers Module           → jcepnzzkmj/modules/transfers
7  = CIS - HR & Staff                 → jcepnzzkmj/modules/hr
8  = CIS - Webhooks & Integration     → jcepnzzkmj/modules/webhooks
9  = CIS - Base Framework             → jcepnzzkmj/modules/base
10 = Ecigdis Wholesale Portal         → fhrehrpjmu
12 = VapeShed Retail - Main Site      → dvaxgvsxmz
13 = CIS Core Platform                → jcepnzzkmj (root)
```

### Path-to-Server Mapping
```
/home/129337.cloudwaysapps.com/hdgwrzntwa/     → server_id: hdgwrzntwa, unit_id: 1
/home/master/applications/hdgwrzntwa/          → server_id: hdgwrzntwa, unit_id: 1
/home/master/applications/jcepnzzkmj/          → server_id: jcepnzzkmj, unit_id: 2
/home/master/applications/dvaxgvsxmz/          → server_id: dvaxgvsxmz, unit_id: 3
/home/master/applications/fhrehrpjmu/          → server_id: fhrehrpjmu, unit_id: 4
```

---

## 🔍 How Auto-Detection Works

### Method 1: VS Code Workspace Path Detection (RECOMMENDED)

GitHub Copilot automatically knows the workspace root. We can detect project context from the file path you're editing:

**Example:**
```
You're editing: /home/master/applications/jcepnzzkmj/public_html/modules/consignments/pack.php

Auto-detected:
✅ server_id: jcepnzzkmj
✅ unit_id: 2 (CIS System)
✅ project_id: 2 (CIS - Consignments Module)
```

**Detection Logic:**
```javascript
// In GitHub Copilot's context
const workspacePath = vscode.workspace.workspaceFolders[0].uri.fsPath;
const currentFile = vscode.window.activeTextEditor.document.uri.fsPath;

// Extract server_id from path
let server_id = null;
if (currentFile.includes('/hdgwrzntwa/')) server_id = 'hdgwrzntwa';
if (currentFile.includes('/jcepnzzkmj/')) server_id = 'jcepnzzkmj';
if (currentFile.includes('/dvaxgvsxmz/')) server_id = 'dvaxgvsxmz';
if (currentFile.includes('/fhrehrpjmu/')) server_id = 'fhrehrpjmu';

// Extract project from module path
let project_id = null;
if (currentFile.includes('/modules/consignments/')) project_id = 2;
if (currentFile.includes('/modules/supplier/')) project_id = 3;
if (currentFile.includes('/modules/purchase_orders/')) project_id = 4;
// ... etc
```

### Method 2: .vscode/mcp-context.json File (FALLBACK)

If auto-detection fails, create a workspace-specific context file:

**Location:** `.vscode/mcp-context.json` (in each workspace root)

**Example for CIS:**
```json
{
  "server_id": "jcepnzzkmj",
  "unit_id": 2,
  "project_id": 13,
  "project_name": "CIS Core Platform",
  "default_project_id": 13,
  "module_mapping": {
    "modules/consignments": 2,
    "modules/supplier": 3,
    "modules/purchase_orders": 4,
    "modules/inventory": 5,
    "modules/transfers": 6,
    "modules/hr": 7,
    "modules/webhooks": 8,
    "modules/base": 9
  }
}
```

**Example for Intelligence Hub:**
```json
{
  "server_id": "hdgwrzntwa",
  "unit_id": 1,
  "project_id": 1,
  "project_name": "Intelligence Hub (Scanner)",
  "default_project_id": 1
}
```

### Method 3: Git Repository Detection (FUTURE)

Check `.git/config` for remote URL and map to project:

```ini
[remote "origin"]
    url = https://github.com/pearcestephens/IntelligenceHub.git
```

Map repository to project_id via database lookup.

---

## 🔧 Implementation Status

### ✅ COMPLETED
- [x] Database schema has unit_id, project_id, server_id fields
- [x] Projects table populated with 13 projects
- [x] Business units table populated with 4 units
- [x] Path-to-server mappings documented
- [x] save_conversation.php API accepts context fields

### 🟡 IN PROGRESS
- [ ] save_conversation.php needs SQL statements updated (INSERT/UPDATE)

### ⏳ TODO
- [ ] Create context detection library (`/mcp/detect_context.php`)
- [ ] Create .vscode/mcp-context.json files for each workspace
- [ ] Update GitHub Copilot integration to send context
- [ ] Create conversation retrieval API (`/api/get_project_conversations.php`)
- [ ] Test multi-bot context sharing

---

## 📝 How to Use This in Your Chat

### When Starting a New Conversation:

**Automatic (if using VS Code):**
```
GitHub Copilot automatically detects:
- You're editing /home/master/applications/jcepnzzkmj/public_html/modules/consignments/pack.php
- Sends to API: server_id=jcepnzzkmj, unit_id=2, project_id=2
- Recorded in database with context
```

**Manual (if needed):**
```
@workspace I'm working on CIS Consignments module (project_id=2)
```

### Retrieving Previous Context:

**Query by project:**
```
@workspace Show me previous conversations about the Consignments module
```

**Behind the scenes:**
```
API call: GET /api/get_project_conversations.php?project_id=2&limit=5
Returns: Last 5 conversations about Consignments module with full message history
```

---

## 🎯 Benefits Once Implemented

1. **Automatic Context Awareness**
   - Bot knows which project you're on without asking
   - Searches filtered to relevant codebase
   - Previous work automatically available

2. **Multi-Bot Collaboration**
   - Bot A works on Consignments, records context
   - Bot B retrieves Bot A's conversation
   - Seamless handoff between bot sessions

3. **Project-Specific Intelligence**
   - Each project builds cumulative knowledge
   - Patterns and decisions preserved
   - Faster onboarding for new bots

4. **Cross-Server Awareness**
   - Working on CIS but need Hub data? Bot knows to check unit_id=1
   - Wholesale portal bot can reference CIS patterns
   - Unified intelligence across all 4 servers

---

## 🚀 Next Steps

### Immediate (Complete API Fix):
1. Update save_conversation.php INSERT statement
2. Update save_conversation.php UPDATE statement
3. Test conversation recording with project context

### Short-term (Enable Detection):
1. Create `/mcp/detect_context.php` library
2. Create `.vscode/mcp-context.json` for each workspace
3. Test auto-detection from VS Code

### Medium-term (Enable Retrieval):
1. Create `/api/get_project_conversations.php` API
2. Add MCP tool: `get_project_context`
3. Document usage for GitHub Copilot

### Long-term (Advanced Features):
1. Cross-project conversation linking (correlation_id)
2. Project dependency awareness (Consignments uses Base framework)
3. Historical pattern analysis per project
4. Automated context suggestions

---

## 📊 Testing Checklist

### Phase 1: Recording Context
- [ ] Start conversation in CIS Consignments module
- [ ] Verify server_id=jcepnzzkmj recorded
- [ ] Verify unit_id=2 recorded
- [ ] Verify project_id=2 recorded
- [ ] Check ai_conversations table has data

### Phase 2: Retrieving Context
- [ ] Query conversations for project_id=2
- [ ] Verify previous messages returned
- [ ] Verify message order correct
- [ ] Verify timestamps accurate

### Phase 3: Multi-Bot Test
- [ ] Bot A creates conversation with context
- [ ] Bot B queries same project context
- [ ] Bot B receives Bot A's conversation
- [ ] Bot B continues work with shared knowledge

---

## 💡 Pro Tips

### For Development:
- Always work with workspace open in VS Code (enables path detection)
- Edit files within project structure (not random files)
- Use .vscode/mcp-context.json as fallback for edge cases

### For Bots:
- First message should include: "I'm working on [project name]"
- Check previous conversations at start: "Show me recent work on this project"
- Reference correlation_id to link related conversations

### For Search:
- Include unit_id in searches: "search CIS only"
- Filter by project_id: "show inventory management code only"
- Cross-project search: "compare consignments vs transfers approach"

---

**Last Updated:** November 4, 2025
**Status:** Implementation Phase (50% complete)
**Priority:** CRITICAL - User requested feature
