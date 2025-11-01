#!/bin/bash
# =====================================================
# BATCH-7: Execute Everything
# One-command solution for complete setup
# =====================================================

set -e

cd "$(dirname "$0")/.."

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "🚀 BATCH-7 Complete Setup Starting..."
echo ""

# Step 1: Make all scripts executable
echo "📝 Making scripts executable..."
chmod +x bin/*.sh bin/*.php 2>/dev/null || true

# Step 2: Run complete test environment setup
echo ""
echo "🔧 Setting up test environment..."
echo ""

# Check if we're in the right directory
if [ ! -f "bin/setup-test-env.sh" ]; then
    echo "❌ Error: bin/setup-test-env.sh not found"
    echo "📁 Current directory: $(pwd)"
    echo "📋 Available files:"
    ls -la bin/ 2>/dev/null || echo "  bin/ directory not found"
    exit 1
fi

bash bin/setup-test-env.sh
EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ BATCH-7 Setup Complete!${NC}"
    echo ""
    echo "Next steps:"
    echo "  • Run all tests: ${BLUE}php vendor/bin/phpunit${NC}"
    echo "  • Run specific suite: ${BLUE}php vendor/bin/phpunit tests/Integration/${NC}"
    echo "  • View test DB: ${BLUE}mysql -h 127.0.0.1 -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj_test${NC}"
else
    echo "⚠️  Setup completed with errors (see above)"
    echo ""
    echo "Troubleshooting:"
    echo "  • Check database connection"
    echo "  • Verify credentials in .env.test"
    echo "  • Review error messages above"
fi

echo ""
exit $EXIT_CODE
