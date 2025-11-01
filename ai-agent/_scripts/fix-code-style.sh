#!/bin/bash

# Fix code style script - auto-fixes PSR-12 violations
# Location: /home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent/fix-code-style.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  AI Agent - Code Style Auto-Fixer (PSR-12)${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo

# Get script directory (ai-agent/)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo -e "${YELLOW}→${NC} Working directory: ${BLUE}$(pwd)${NC}"
echo

# Check if vendor/bin/phpcbf exists
if [ ! -f "vendor/bin/phpcbf" ]; then
    echo -e "${RED}✗${NC} phpcbf not found. Run: composer install"
    exit 1
fi

echo -e "${YELLOW}→${NC} Running PHPCS to count current violations..."
echo

# Count current violations (ignore exit code)
BEFORE_COUNT=$(vendor/bin/phpcs --standard=PSR12 --report=summary src/ 2>&1 | grep -oP '\d+(?= ERRORS)' || echo "0")

echo -e "${YELLOW}→${NC} Found ${RED}${BEFORE_COUNT}${NC} violations before auto-fix"
echo

echo -e "${YELLOW}→${NC} Running phpcbf to auto-fix violations..."
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo

# Run phpcbf (it returns non-zero if it fixes files, so we ignore exit code)
vendor/bin/phpcbf --standard=PSR12 src/ || true

echo
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo

echo -e "${YELLOW}→${NC} Running PHPCS to count remaining violations..."
echo

# Count remaining violations
AFTER_COUNT=$(vendor/bin/phpcs --standard=PSR12 --report=summary src/ 2>&1 | grep -oP '\d+(?= ERRORS)' || echo "0")
FIXED=$((BEFORE_COUNT - AFTER_COUNT))

echo
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✓${NC} Auto-fix complete!"
echo
echo -e "  ${YELLOW}Before:${NC} ${RED}${BEFORE_COUNT}${NC} violations"
echo -e "  ${YELLOW}After:${NC}  ${AFTER_COUNT} violations"
echo -e "  ${YELLOW}Fixed:${NC}  ${GREEN}${FIXED}${NC} violations automatically"
echo
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ "$AFTER_COUNT" -eq 0 ]; then
    echo
    echo -e "${GREEN}✓✓✓ SUCCESS!${NC} All code style violations fixed!"
    echo -e "    ${YELLOW}→${NC} You can now run: ${BLUE}composer qa${NC}"
    echo
    exit 0
else
    echo
    echo -e "${YELLOW}⚠${NC}  ${AFTER_COUNT} violations require manual fixes"
    echo -e "    ${YELLOW}→${NC} Run: ${BLUE}vendor/bin/phpcs --report=full src/${NC}"
    echo -e "    ${YELLOW}→${NC} To see detailed violation list"
    echo
    exit 1
fi
