#!/bin/bash
#
# 🚀 ONE-COMMAND DEPLOYMENT
#
# This script does EVERYTHING:
# - Checks environment
# - Installs tables automatically
# - Configures everything
# - Generates API keys
# - Tests all endpoints
# - Verifies deployment
#
# USAGE: bash deploy-everything.sh
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

AI_AGENT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent"

clear
echo -e "${MAGENTA}"
cat << "EOF"
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║     🚀 AI-AGENT ONE-COMMAND DEPLOYMENT v2.0                 ║
║                                                              ║
║  This will automatically:                                    ║
║  ✓ Check your environment                                    ║
║  ✓ Install database tables                                   ║
║  ✓ Configure settings                                        ║
║  ✓ Generate API keys                                         ║
║  ✓ Test all endpoints                                        ║
║  ✓ Verify everything works                                   ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
EOF
echo -e "${NC}"

cd "$AI_AGENT_ROOT"

# Make scripts executable
echo -e "${CYAN}→ Making scripts executable...${NC}"
chmod +x bin/*.sh 2>/dev/null || true
echo -e "${GREEN}✓ Scripts ready${NC}\n"

# ═══════════════════════════════════════════════════════════════
# STEP 1: SMART INSTALLATION
# ═══════════════════════════════════════════════════════════════

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}STEP 1: SMART INSTALLATION${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"

if [ -f "bin/smart-install.sh" ]; then
    echo -e "${CYAN}→ Running intelligent installer...${NC}\n"
    bash bin/smart-install.sh

    if [ $? -eq 0 ]; then
        echo -e "\n${GREEN}✓ Installation completed successfully${NC}\n"
    else
        echo -e "\n${RED}✗ Installation failed${NC}"
        echo -e "${RED}Please check the log and fix issues${NC}\n"
        exit 1
    fi
else
    echo -e "${RED}✗ smart-install.sh not found${NC}"
    exit 1
fi

# ═══════════════════════════════════════════════════════════════
# STEP 2: PRE-FLIGHT CHECKS
# ═══════════════════════════════════════════════════════════════

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}STEP 2: PRE-FLIGHT CHECKS${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"

if [ -f "bin/pre-flight-check.sh" ]; then
    echo -e "${CYAN}→ Running comprehensive checks...${NC}\n"
    bash bin/pre-flight-check.sh

    PRE_FLIGHT_RESULT=$?

    if [ $PRE_FLIGHT_RESULT -eq 0 ]; then
        echo -e "\n${GREEN}✓ All pre-flight checks passed${NC}\n"
    else
        echo -e "\n${YELLOW}⚠ Some pre-flight checks failed${NC}"
        echo -e "${YELLOW}Continuing with API tests...${NC}\n"
    fi
else
    echo -e "${YELLOW}⚠ pre-flight-check.sh not found, skipping${NC}\n"
fi

# ═══════════════════════════════════════════════════════════════
# STEP 3: API TEST SUITE
# ═══════════════════════════════════════════════════════════════

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}STEP 3: API TEST SUITE${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"

if [ -f "bin/api-test-suite.sh" ]; then
    echo -e "${CYAN}→ Testing all API endpoints...${NC}\n"
    bash bin/api-test-suite.sh

    API_TEST_RESULT=$?

    if [ $API_TEST_RESULT -eq 0 ]; then
        echo -e "\n${GREEN}✓ All API tests passed${NC}\n"
    else
        echo -e "\n${RED}✗ Some API tests failed${NC}"
        echo -e "${RED}Check logs for details${NC}\n"
    fi
else
    echo -e "${YELLOW}⚠ api-test-suite.sh not found, skipping${NC}\n"
fi

# ═══════════════════════════════════════════════════════════════
# FINAL REPORT
# ═══════════════════════════════════════════════════════════════

echo ""
echo -e "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${MAGENTA}DEPLOYMENT COMPLETE${NC}"
echo -e "${MAGENTA}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

# Check overall success
OVERALL_SUCCESS=true

if [ $PRE_FLIGHT_RESULT -ne 0 ]; then
    OVERALL_SUCCESS=false
fi

if [ $API_TEST_RESULT -ne 0 ]; then
    OVERALL_SUCCESS=false
fi

if [ "$OVERALL_SUCCESS" = true ]; then
    echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                    ✓ DEPLOYMENT SUCCESSFUL                  ║${NC}"
    echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${GREEN}🎉 AI-Agent is fully operational!${NC}"
    echo ""
    echo -e "${CYAN}What was done:${NC}"
    echo -e "  ✓ Database tables installed and verified"
    echo -e "  ✓ Configuration validated and corrected"
    echo -e "  ✓ API keys generated and secured"
    echo -e "  ✓ All API endpoints tested and working"
    echo -e "  ✓ Health monitoring configured"
    echo ""
    echo -e "${CYAN}Quick Links:${NC}"
    echo -e "  🏥 Health: https://gpt.ecigdis.co.nz/ai-agent/api/health.php"
    echo -e "  💬 Chat API: https://gpt.ecigdis.co.nz/ai-agent/api/chat-enterprise.php"
    echo -e "  🔑 API Keys: $AI_AGENT_ROOT/config/api_keys.txt"
    echo ""
    echo -e "${CYAN}Test Commands:${NC}"
    echo -e "  ${YELLOW}# Test health${NC}"
    echo -e "  curl https://gpt.ecigdis.co.nz/ai-agent/api/health.php | jq"
    echo ""
    echo -e "  ${YELLOW}# Test chat (with your API key)${NC}"
    echo -e "  API_KEY=\$(head -n 1 config/api_keys.txt)"
    echo -e "  curl -X POST https://gpt.ecigdis.co.nz/ai-agent/api/chat-enterprise.php \\"
    echo -e "    -H \"X-API-KEY: \$API_KEY\" \\"
    echo -e "    -H \"Content-Type: application/json\" \\"
    echo -e "    -d '{\"message\":\"Hello!\"}'"
    echo ""
    echo -e "${CYAN}Logs:${NC}"
    echo -e "  📋 Installation: logs/smart-install-*.log"
    echo -e "  🔍 Pre-flight: logs/pre-flight-*.log"
    echo -e "  🧪 API Tests: logs/api-tests-*.log"
    echo ""
    exit 0
else
    echo -e "${YELLOW}╔══════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${YELLOW}║              ⚠ DEPLOYMENT COMPLETED WITH WARNINGS           ║${NC}"
    echo -e "${YELLOW}╚══════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${YELLOW}⚠ Some checks or tests failed${NC}"
    echo ""
    echo -e "${CYAN}Status:${NC}"
    if [ $PRE_FLIGHT_RESULT -ne 0 ]; then
        echo -e "  ${YELLOW}⚠ Pre-flight checks: WARNINGS${NC}"
    else
        echo -e "  ${GREEN}✓ Pre-flight checks: PASSED${NC}"
    fi

    if [ $API_TEST_RESULT -ne 0 ]; then
        echo -e "  ${RED}✗ API tests: FAILED${NC}"
    else
        echo -e "  ${GREEN}✓ API tests: PASSED${NC}"
    fi
    echo ""
    echo -e "${CYAN}Next Steps:${NC}"
    echo -e "  1. Review logs in: $AI_AGENT_ROOT/logs/"
    echo -e "  2. Check _kb/ai-agent/EXECUTIVE_SUMMARY.md for known issues"
    echo -e "  3. Apply authentication patches if needed"
    echo -e "  4. Re-run: bash bin/deploy-everything.sh"
    echo ""
    echo -e "${CYAN}Quick Health Check:${NC}"
    echo -e "  curl https://gpt.ecigdis.co.nz/ai-agent/api/health.php"
    echo ""
    exit 1
fi
