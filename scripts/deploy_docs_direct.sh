#!/bin/bash
# ============================================================================
# Direct Documentation Deployment to CIS (Same System)
# ============================================================================

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔════════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  📚 Deploying Documentation to CIS (Direct Copy)          ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════════════════╝${NC}"
echo ""

# Source bundle
BUNDLE="/tmp/cis_docs_bundle_1762253832"

# Target directories on CIS
CIS_ROOT="/home/master/applications/jcepnzzkmj/public_html"
TARGET_DIRS=(
    "$CIS_ROOT/_kb/"
    "$CIS_ROOT/docs/"
    "$CIS_ROOT/.vscode/"
    "$CIS_ROOT/.github/"
)

# Create target directories
echo -e "${YELLOW}📁 Creating target directories...${NC}"
for dir in "${TARGET_DIRS[@]}"; do
    mkdir -p "$dir"
    echo -e "  ${GREEN}✓${NC} $dir"
done

# Copy files
echo ""
echo -e "${YELLOW}📋 Copying documentation files...${NC}"

for dir in "${TARGET_DIRS[@]}"; do
    echo -e "  → $dir"
    cp -v "$BUNDLE"/*.md "$dir/" 2>&1 | sed 's/^/    /'
done

# Special: Copy bot guide to .vscode
echo ""
echo -e "${YELLOW}🤖 Configuring bot instructions...${NC}"
cp -v "$BUNDLE/BOTS_GUIDE.md" "$CIS_ROOT/.vscode/"
cp -v "$BUNDLE/copilot-instructions.md" "$CIS_ROOT/.github/"

# Create index in _kb
echo ""
echo -e "${YELLOW}📇 Creating documentation index...${NC}"

cat > "$CIS_ROOT/_kb/INDEX.md" << 'EOF'
# 📚 CIS Documentation Index

**Last Updated:** $(date)
**System:** CIS (staff.vapeshed.co.nz)
**Database:** jcepnzzkmj

---

## 🎯 Quick Start

- [CIS Quick Reference](CIS_QUICK_REFERENCE.md) - ⭐ Start here!
- [Master System Guide](MASTER_SYSTEM_GUIDE.md) - Complete overview
- [Frontend Tools](FRONTEND_TOOLS_BREAKDOWN.md) - All testing tools

---

## 🤖 AI Integration

- [Integration Master Plan](INTEGRATION_MASTER_PLAN.md) - Architecture
- [Frontend Integration Setup](FRONTEND_INTEGRATION_SETUP.md) - Setup guide
- [Automation Roadmap](AUTOMATION_ROADMAP.md) - Features & roadmap
- [Architecture Deep Dive](ARCHITECTURE_DEEP_DIVE.md) - Technical details

---

## 🔧 Operations

- [Production Ready](PRODUCTION_READY.md) - API reference
- [Setup Status](COMPLETE_SETUP_STATUS.md) - Current status
- [Audit Gallery](AUDIT_GALLERY_SYSTEM.md) - Screenshot gallery

---

## 🤖 Bot Configuration

- [Bots Guide](.vscode/BOTS_GUIDE.md) - Complete bot instructions
- [Copilot Instructions](.github/copilot-instructions.md) - GitHub Copilot config

---

## 📊 Database Tables

Frontend automation tables in `jcepnzzkmj`:

- `frontend_pending_fixes`
- `frontend_workflows`
- `frontend_workflow_executions`
- `frontend_audit_history`
- `frontend_monitors`
- `frontend_monitor_alerts`
- `frontend_screenshot_gallery`
- `frontend_visual_regression`
- `frontend_deployment_log`

---

## 🔗 Quick Links

- Approval Dashboard: `/ai-agent/public/dashboard/approvals.php`
- Workflow Builder: `/ai-agent/public/dashboard/workflows.php`
- Gallery: `https://gpt.ecigdis.co.nz/audits/gallery.php?project=cis-frontend`

---

## 📞 Support

- Hub MCP: `https://gpt.ecigdis.co.nz/mcp/server_v3.php`
- Hub Docs: `https://gpt.ecigdis.co.nz/_kb/`
- Issues: Create in GitHub or discuss via AI Agent
EOF

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✓ Documentation deployed to CIS successfully!${NC}"
echo ""
echo -e "${YELLOW}📁 Deployed to:${NC}"
for dir in "${TARGET_DIRS[@]}"; do
    count=$(ls "$dir"*.md 2>/dev/null | wc -l)
    echo -e "  • $dir ${GREEN}($count files)${NC}"
done
echo ""
echo -e "${YELLOW}📋 Verification:${NC}"
echo -e "  ${BLUE}ls -la $CIS_ROOT/_kb/*.md${NC}"
echo -e "  ${BLUE}ls -la $CIS_ROOT/.vscode/*.md${NC}"
echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
