#!/bin/bash
##
# AI Agent Production Deployment Script
#
# Deploys complete CIS integration for AI Agent:
# - Knowledge base ingestion (73MB, 342 files)
# - Memory system configuration
# - CIS context loading
# - Health validation
#
# Usage:
#   bash bin/deploy-ai-agent.sh [options]
#
# Options:
#   --test-only      Run tests without full deployment
#   --skip-kb        Skip knowledge base ingestion
#   --limit=N        Ingest only N documents (for testing)
#   --help           Show this help
#
# Examples:
#   bash bin/deploy-ai-agent.sh --test-only
#   bash bin/deploy-ai-agent.sh --limit=50
#   bash bin/deploy-ai-agent.sh
##

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_ROOT="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html"
KB_PATH="${PROJECT_ROOT}/_kb"
AGENT_PATH="${PROJECT_ROOT}/ai-agent"
MYSQL_USER="jcepnzzkmj"
MYSQL_PASS="wprKh9Jq63"
MYSQL_DB="jcepnzzkmj"

# Parse options
TEST_ONLY=false
SKIP_KB=false
LIMIT=""

for arg in "$@"; do
    case $arg in
        --test-only)
            TEST_ONLY=true
            shift
            ;;
        --skip-kb)
            SKIP_KB=true
            shift
            ;;
        --limit=*)
            LIMIT="${arg#*=}"
            shift
            ;;
        --help)
            grep "^##" "$0" | sed 's/^## //'
            exit 0
            ;;
    esac
done

# Helper functions
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

error() {
    echo -e "${RED}[✗]${NC} $1"
    exit 1
}

separator() {
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
}

# Banner
clear
echo "╔════════════════════════════════════════════════╗"
echo "║                                                ║"
echo "║   🤖 AI Agent Production Deployment           ║"
echo "║                                                ║"
echo "║   Complete CIS Integration Setup               ║"
echo "║                                                ║"
echo "╚════════════════════════════════════════════════╝"
separator

if [ "$TEST_ONLY" = true ]; then
    warning "TEST MODE - Running validation only, no deployment"
    echo ""
fi

# Phase 1: Pre-flight checks
info "Phase 1/5: Pre-flight checks"
echo ""

# Check if project root exists
if [ ! -d "$PROJECT_ROOT" ]; then
    error "Project root not found: $PROJECT_ROOT"
fi
success "Project root exists"

# Check if KB exists
if [ ! -d "$KB_PATH" ]; then
    error "Knowledge base not found: $KB_PATH"
fi
KB_SIZE=$(du -sh "$KB_PATH" | cut -f1)
KB_FILES=$(find "$KB_PATH" -type f -name "*.md" | wc -l)
success "Knowledge base found: ${KB_SIZE}, ${KB_FILES} markdown files"

# Check if agent exists
if [ ! -d "$AGENT_PATH" ]; then
    error "AI Agent directory not found: $AGENT_PATH"
fi
success "AI Agent directory exists"

# Check database connection
info "Testing database connection..."
if ! mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -e "SELECT 1" &>/dev/null; then
    error "Database connection failed"
fi
success "Database connection OK"

# Check agent tables exist
TABLE_COUNT=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$MYSQL_DB' AND table_name LIKE 'agent_%'")
if [ "$TABLE_COUNT" -lt 12 ]; then
    error "Agent tables missing (found: $TABLE_COUNT, expected: 12)"
fi
success "Agent tables exist (12 tables)"

# Check API keys
if ! grep -q "OPENAI_API_KEY=sk-" "$AGENT_PATH/.env" 2>/dev/null; then
    error "OpenAI API key not configured in .env"
fi
if ! grep -q "ANTHROPIC_API_KEY=sk-" "$AGENT_PATH/.env" 2>/dev/null; then
    error "Anthropic API key not configured in .env"
fi
success "API keys configured"

separator

if [ "$TEST_ONLY" = true ]; then
    success "All pre-flight checks passed!"
    echo ""
    info "Remove --test-only to proceed with deployment"
    exit 0
fi

# Phase 2: Knowledge Base Ingestion
if [ "$SKIP_KB" = false ]; then
    info "Phase 2/5: Knowledge Base Ingestion"
    echo ""

    # Check current KB docs
    CURRENT_DOCS=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -N -e "SELECT COUNT(*) FROM agent_kb_docs")
    info "Current documents in KB: $CURRENT_DOCS"

    if [ "$CURRENT_DOCS" -gt 0 ]; then
        warning "Knowledge base already contains $CURRENT_DOCS documents"
        echo -n "Re-ingest anyway? (yes/no): "
        read -r CONFIRM
        if [ "$CONFIRM" != "yes" ]; then
            info "Skipping knowledge base ingestion"
        else
            info "Ingesting knowledge base (this may take 5-10 minutes)..."

            if [ -n "$LIMIT" ]; then
                php "$PROJECT_ROOT/bin/ingest-knowledge-base.php" --limit="$LIMIT" || error "KB ingestion failed"
            else
                php "$PROJECT_ROOT/bin/ingest-knowledge-base.php" || error "KB ingestion failed"
            fi

            success "Knowledge base ingestion complete"
        fi
    else
        info "Ingesting knowledge base (this may take 5-10 minutes)..."
        echo ""

        if [ -n "$LIMIT" ]; then
            php "$PROJECT_ROOT/bin/ingest-knowledge-base.php" --limit="$LIMIT" --verbose || error "KB ingestion failed"
        else
            php "$PROJECT_ROOT/bin/ingest-knowledge-base.php" --verbose || error "KB ingestion failed"
        fi

        echo ""
        success "Knowledge base ingestion complete"
    fi

    separator
else
    info "Phase 2/5: Skipped (--skip-kb)"
    separator
fi

# Phase 3: KB Search Test
info "Phase 3/5: Knowledge Base Search Test"
echo ""

FINAL_DOCS=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -N -e "SELECT COUNT(*) FROM agent_kb_docs")
info "Total documents in KB: $FINAL_DOCS"
echo ""

if [ "$FINAL_DOCS" -gt 0 ]; then
    info "Testing KB search with query: 'stock transfer'..."
    echo ""
    php "$PROJECT_ROOT/bin/test-kb-search.php" "stock transfer" || warning "KB search test failed (non-critical)"
    echo ""
    success "KB search test complete"
else
    warning "No documents in KB - search test skipped"
fi

separator

# Phase 4: Health Check
info "Phase 4/5: System Health Check"
echo ""

info "Testing health endpoint..."
HEALTH_RESPONSE=$(curl -s "http://staff.vapeshed.co.nz/ai-agent/api/health" 2>/dev/null || echo "{}")
HEALTH_STATUS=$(echo "$HEALTH_RESPONSE" | python3 -c "import sys, json; print(json.load(sys.stdin).get('status', 'unknown'))" 2>/dev/null || echo "error")

if [ "$HEALTH_STATUS" = "healthy" ]; then
    success "Health endpoint: HEALTHY"
else
    warning "Health endpoint: $HEALTH_STATUS"
    echo "Response: $HEALTH_RESPONSE"
fi

# Check conversation stats
CONV_COUNT=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -N -e "SELECT COUNT(*) FROM agent_conversations")
MSG_COUNT=$(mysql -u "$MYSQL_USER" -p"$MYSQL_PASS" "$MYSQL_DB" -N -e "SELECT COUNT(*) FROM agent_messages")
info "Conversations: $CONV_COUNT"
info "Messages: $MSG_COUNT"

separator

# Phase 5: Final Summary
info "Phase 5/5: Deployment Summary"
echo ""

echo "📊 System Status:"
echo ""
echo "  Knowledge Base:"
echo "    - Documents ingested: $FINAL_DOCS"
echo "    - Source files: $KB_FILES markdown files"
echo "    - Total size: $KB_SIZE"
echo ""
echo "  Memory System:"
echo "    - Conversations: $CONV_COUNT"
echo "    - Messages: $MSG_COUNT"
echo "    - Tables: 12 agent_* tables"
echo ""
echo "  Health:"
echo "    - Status: $HEALTH_STATUS"
echo "    - Database: Connected"
echo "    - API Keys: Configured"
echo ""

separator

if [ "$HEALTH_STATUS" = "healthy" ] && [ "$FINAL_DOCS" -gt 0 ]; then
    success "✅ AI Agent deployment complete!"
    echo ""
    echo "🚀 Next steps:"
    echo ""
    echo "  1. Test the agent:"
    echo "     curl -X POST http://staff.vapeshed.co.nz/ai-agent/api/chat \\"
    echo "       -H 'Content-Type: application/json' \\"
    echo "       -d '{\"message\": \"What is the CIS system?\"}'"
    echo ""
    echo "  2. Check agent logs:"
    echo "     tail -f $AGENT_PATH/logs/agent.log"
    echo ""
    echo "  3. Review configuration:"
    echo "     cat $AGENT_PATH/.env"
    echo ""
    echo "  4. Read deployment guide:"
    echo "     cat $AGENT_PATH/PRODUCTION_SETUP_COMPLETE_GUIDE.md"
    echo ""
else
    warning "⚠️  Deployment completed with warnings"
    echo ""
    echo "Issues detected:"
    [ "$HEALTH_STATUS" != "healthy" ] && echo "  - Health status: $HEALTH_STATUS"
    [ "$FINAL_DOCS" -eq 0 ] && echo "  - No documents in knowledge base"
    echo ""
    echo "Review logs and troubleshooting guide:"
    echo "  tail -f $AGENT_PATH/logs/agent.log"
    echo "  cat $AGENT_PATH/PRODUCTION_SETUP_COMPLETE_GUIDE.md"
    echo ""
fi

exit 0
