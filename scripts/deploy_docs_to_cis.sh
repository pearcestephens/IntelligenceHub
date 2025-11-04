#!/bin/bash
# ============================================================================
# Documentation Deployment Script - Transfer to CIS
# ============================================================================
# Purpose: Copy all master documentation from Intelligence Hub to CIS
# Target: staff.vapeshed.co.nz (CIS System)
# Date: November 4, 2025
# ============================================================================

set -e  # Exit on error

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  📚 Documentation Deployment to CIS                        ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Source and target paths
HUB_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html"
CIS_ROOT="/home/master/applications/jcepnzzkmj/public_html"

# Check if CIS is accessible via SSH
CIS_HOST="phpstack-129337-518184.cloudwaysapps.com"
CIS_USER="master"

echo -e "${YELLOW}📋 Creating comprehensive documentation bundle...${NC}"

# Create temporary bundle directory
BUNDLE_DIR="/tmp/cis_docs_bundle_$(date +%s)"
mkdir -p "$BUNDLE_DIR"

# ============================================================================
# 1. COPY MASTER DOCUMENTATION FILES
# ============================================================================

echo -e "${GREEN}✓${NC} Bundling master documents..."

# Main guides
cp "$HUB_ROOT/MASTER_SYSTEM_GUIDE.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/FRONTEND_TOOLS_BREAKDOWN.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/PRODUCTION_READY.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/COMPLETE_SETUP_STATUS.md" "$BUNDLE_DIR/"

# Frontend integration
cp "$HUB_ROOT/frontend-tools/INTEGRATION_MASTER_PLAN.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/frontend-tools/AUTOMATION_ROADMAP.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/frontend-tools/ARCHITECTURE_DEEP_DIVE.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/frontend-tools/AUDIT_GALLERY_SYSTEM.md" "$BUNDLE_DIR/"

# AI Agent integration
cp "$HUB_ROOT/ai-agent/FRONTEND_INTEGRATION_SETUP.md" "$BUNDLE_DIR/"

# Bot instructions
cp "$HUB_ROOT/.vscode/BOTS_GUIDE.md" "$BUNDLE_DIR/"
cp "$HUB_ROOT/.github/copilot-instructions.md" "$BUNDLE_DIR/"

# ============================================================================
# 2. CREATE CIS-SPECIFIC VERSIONS
# ============================================================================

echo -e "${GREEN}✓${NC} Creating CIS-specific versions..."

# CIS Quick Reference
cat > "$BUNDLE_DIR/CIS_QUICK_REFERENCE.md" << 'EOF'
# 🚀 CIS Quick Reference - Frontend Automation

**Target System:** CIS (staff.vapeshed.co.nz)
**Database:** jcepnzzkmj
**Purpose:** Frontend testing, automation, and AI-powered fixes

---

## 📍 Key Locations

### CIS Root
```
/home/master/applications/jcepnzzkmj/public_html/
```

### Documentation
```
/home/master/applications/jcepnzzkmj/public_html/_kb/
/home/master/applications/jcepnzzkmj/public_html/docs/
/home/master/applications/jcepnzzkmj/public_html/.vscode/
/home/master/applications/jcepnzzkmj/public_html/.github/
```

### AI Agent Integration
```
/home/master/applications/jcepnzzkmj/public_html/ai-agent/
├── src/Tools/Frontend/FrontendToolRegistry.php
├── public/dashboard/approvals.php
├── public/dashboard/workflows.php
├── api/execute-workflow.php
├── api/approve-fix.php
└── migrations/frontend_integration_schema.sql (✓ INSTALLED)
```

---

## 🗄️ Database Tables (jcepnzzkmj)

Frontend automation tables installed:

- `frontend_pending_fixes` - AI-generated fixes awaiting approval
- `frontend_workflows` - Saved automation workflows
- `frontend_workflow_executions` - Execution history
- `frontend_audit_history` - Page audit results
- `frontend_monitors` - Active monitoring configs
- `frontend_monitor_alerts` - Monitoring alerts
- `frontend_screenshot_gallery` - Screenshot metadata
- `frontend_visual_regression` - Visual regression tests
- `frontend_deployment_log` - Code change tracking

**Database Access:**
```bash
mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj
```

---

## 🔧 Frontend Tools (Remote)

Frontend tools run on Intelligence Hub, integrate with CIS via API.

**Hub Location:** `https://gpt.ecigdis.co.nz/frontend-tools/`
**CIS Target:** `https://staff.vapeshed.co.nz`

### Quick Audit Command
```bash
# Run from hub server
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/frontend-tools
node examples/comprehensive-audit.js https://staff.vapeshed.co.nz
```

---

## 🤖 MCP Tools Available

### Intelligence Hub MCP Server
**URL:** `https://gpt.ecigdis.co.nz/mcp/server_v3.php`

**Tools (13 total):**
1. `semantic_search` - Search CIS codebase
2. `search_by_category` - Search by business category
3. `find_code` - Find functions/classes
4. `analyze_file` - Deep file analysis
5. `get_file_content` - Get file with context
6. `health_check` - System health
7. `get_stats` - System statistics
8. `list_categories` - All 31 categories
9. `get_analytics` - Real-time analytics
10. `find_similar` - Find similar files
11. `explore_by_tags` - Search by tags
12. `top_keywords` - Most common keywords
13. `conversation.*` - Conversation memory tools

### Frontend Automation Tools
**Registered via:** `FrontendToolRegistry.php`

**Tools (7 total):**
1. `frontend_audit_page` - Comprehensive page audit
2. `frontend_screenshot` - Capture screenshots
3. `frontend_monitor_start` - Start monitoring
4. `frontend_auto_fix` - Apply fixes (with approval)
5. `frontend_visual_regression` - Screenshot comparison
6. `frontend_performance_audit` - Lighthouse audit
7. `frontend_accessibility_check` - WCAG compliance

---

## 🎯 Quick Commands

### Test CIS Connection
```bash
curl -I https://staff.vapeshed.co.nz
```

### Check Database
```bash
mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj -e "SHOW TABLES LIKE 'frontend%';"
```

### View Pending Fixes
```bash
mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj -e "SELECT id, url, fix_type, status FROM frontend_pending_fixes WHERE status='pending';"
```

### View Workflows
```bash
mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj -e "SELECT id, name, execution_count, last_executed FROM frontend_workflows;"
```

---

## 📱 Web Interfaces

### Approval Dashboard
**URL:** `https://staff.vapeshed.co.nz/ai-agent/public/dashboard/approvals.php`
**Purpose:** Review and approve AI-generated fixes

### Workflow Builder
**URL:** `https://staff.vapeshed.co.nz/ai-agent/public/dashboard/workflows.php`
**Purpose:** Visual drag-drop workflow designer

### Gallery
**URL:** `https://gpt.ecigdis.co.nz/audits/gallery.php?project=cis-frontend`
**Purpose:** View all audit screenshots

---

## 🔄 Workflow Example

### Quick Page Audit Workflow

```json
{
  "name": "Quick CIS Audit",
  "nodes": [
    {
      "id": "node_1",
      "type": "audit",
      "config": {
        "url": "https://staff.vapeshed.co.nz/transfers",
        "checks": ["errors", "performance"],
        "auto_fix": false
      }
    },
    {
      "id": "node_2",
      "type": "screenshot",
      "config": {
        "type": "full_page",
        "upload": true
      }
    }
  ],
  "connections": [
    {"from": "node_1", "to": "node_2"}
  ]
}
```

**Execute via:**
```bash
curl -X POST https://staff.vapeshed.co.nz/ai-agent/api/execute-workflow.php \
  -H "Content-Type: application/json" \
  -d @workflow.json
```

---

## 📚 Documentation Index

All documentation available at:
- `_kb/` - Knowledge base
- `docs/` - Technical documentation
- `.vscode/BOTS_GUIDE.md` - Bot instructions
- `.github/copilot-instructions.md` - Copilot config

**Master Documents:**
1. `MASTER_SYSTEM_GUIDE.md` - Complete system overview
2. `FRONTEND_TOOLS_BREAKDOWN.md` - All frontend tools
3. `INTEGRATION_MASTER_PLAN.md` - Integration architecture
4. `FRONTEND_INTEGRATION_SETUP.md` - Setup guide
5. `CIS_QUICK_REFERENCE.md` - This file

---

## ✅ System Status

- ✅ Database schema installed (9 tables)
- ✅ Tool registry created (7 tools)
- ✅ Approval UI deployed
- ✅ Workflow builder deployed
- ✅ APIs operational
- ✅ MCP integration active
- ✅ Documentation deployed

**Next Steps:**
1. Create your first workflow in workflow builder
2. Run audit on CIS pages
3. Review fixes in approval dashboard
4. Test end-to-end workflow

---

**Last Updated:** November 4, 2025
**Maintained by:** Intelligence Hub Automation
EOF

# ============================================================================
# 3. CREATE TRANSFER SCRIPT
# ============================================================================

echo -e "${GREEN}✓${NC} Creating transfer script..."

cat > "$BUNDLE_DIR/transfer_to_cis.sh" << 'TRANSFER_EOF'
#!/bin/bash
# Transfer documentation to CIS server

TARGET_DIRS=(
    "/home/master/applications/jcepnzzkmj/public_html/_kb/"
    "/home/master/applications/jcepnzzkmj/public_html/docs/"
    "/home/master/applications/jcepnzzkmj/public_html/.vscode/"
    "/home/master/applications/jcepnzzkmj/public_html/.github/"
)

echo "📦 Transferring documentation to CIS..."

for dir in "${TARGET_DIRS[@]}"; do
    echo "  → $dir"
    mkdir -p "$dir"
    cp -v *.md "$dir/" 2>/dev/null || true
done

echo "✓ Transfer complete!"
TRANSFER_EOF

chmod +x "$BUNDLE_DIR/transfer_to_cis.sh"

# ============================================================================
# 4. CREATE MANIFEST
# ============================================================================

cat > "$BUNDLE_DIR/MANIFEST.md" << 'EOF'
# 📦 CIS Documentation Bundle

**Created:** $(date)
**Purpose:** Complete documentation deployment to CIS

## Files Included

### Master Guides
- `MASTER_SYSTEM_GUIDE.md` - Complete system overview
- `FRONTEND_TOOLS_BREAKDOWN.md` - All frontend tools detailed
- `PRODUCTION_READY.md` - Production API reference
- `COMPLETE_SETUP_STATUS.md` - Current setup status

### Integration Documentation
- `INTEGRATION_MASTER_PLAN.md` - Integration architecture
- `FRONTEND_INTEGRATION_SETUP.md` - Setup guide
- `AUTOMATION_ROADMAP.md` - Automation features
- `ARCHITECTURE_DEEP_DIVE.md` - Technical architecture
- `AUDIT_GALLERY_SYSTEM.md` - Gallery system docs

### Bot Instructions
- `BOTS_GUIDE.md` - Complete bot guide
- `copilot-instructions.md` - GitHub Copilot config

### CIS-Specific
- `CIS_QUICK_REFERENCE.md` - Quick reference for CIS
- `transfer_to_cis.sh` - Deployment script

## Deployment Targets

Copy to these locations on CIS server:

1. `/home/master/applications/jcepnzzkmj/public_html/_kb/`
2. `/home/master/applications/jcepnzzkmj/public_html/docs/`
3. `/home/master/applications/jcepnzzkmj/public_html/.vscode/`
4. `/home/master/applications/jcepnzzkmj/public_html/.github/`

## Quick Deploy

```bash
# On CIS server:
cd /path/to/bundle
./transfer_to_cis.sh
```

## Database

Frontend integration tables already installed in `jcepnzzkmj` database.

## Verification

After deployment, verify:
```bash
ls -la /home/master/applications/jcepnzzkmj/public_html/_kb/*.md
ls -la /home/master/applications/jcepnzzkmj/public_html/.vscode/*.md
```
EOF

# ============================================================================
# 5. CREATE ARCHIVE
# ============================================================================

echo -e "${GREEN}✓${NC} Creating deployment archive..."

cd /tmp
ARCHIVE_NAME="cis_docs_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf "$ARCHIVE_NAME" -C "$BUNDLE_DIR" .

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Documentation bundle created successfully!${NC}"
echo ""
echo -e "${YELLOW}Bundle location:${NC}"
echo -e "  Directory: ${GREEN}$BUNDLE_DIR${NC}"
echo -e "  Archive:   ${GREEN}/tmp/$ARCHIVE_NAME${NC}"
echo ""
echo -e "${YELLOW}Files included:${NC}"
ls -1 "$BUNDLE_DIR" | sed 's/^/  • /'
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}📋 Next Steps:${NC}"
echo ""
echo -e "  ${GREEN}1.${NC} Transfer to CIS via SCP:"
echo -e "     ${BLUE}scp /tmp/$ARCHIVE_NAME $CIS_USER@$CIS_HOST:/tmp/${NC}"
echo ""
echo -e "  ${GREEN}2.${NC} Extract on CIS:"
echo -e "     ${BLUE}ssh $CIS_USER@$CIS_HOST${NC}"
echo -e "     ${BLUE}cd /tmp && tar -xzf $ARCHIVE_NAME${NC}"
echo ""
echo -e "  ${GREEN}3.${NC} Run deployment:"
echo -e "     ${BLUE}cd /tmp/cis_docs_bundle_* && ./transfer_to_cis.sh${NC}"
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
