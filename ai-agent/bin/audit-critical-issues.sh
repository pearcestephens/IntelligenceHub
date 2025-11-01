#!/bin/bash

# 🚨 CRITICAL FIXES - QUICK START
# Run this to begin fixing the most urgent security issues

set -e

echo "🚨 CRITICAL FIXES - QUICK START"
echo "==============================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Base directory
BASE_DIR="/home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent"
cd "$BASE_DIR"

echo "📋 RUNNING SECURITY AUDITS..."
echo ""

# 1. Find hardcoded credentials
echo "1️⃣  Checking for hardcoded credentials..."
if grep -r "jcepnzzkmj.*wprKh9Jq63\|new mysqli.*jcepnzzkmj" public/ src/ 2>/dev/null; then
    echo -e "${RED}❌ FOUND HARDCODED CREDENTIALS${NC}"
    echo "   Files with hardcoded DB credentials found!"
    echo "   ACTION: Replace with Config::get() calls"
else
    echo -e "${GREEN}✅ No hardcoded credentials found${NC}"
fi
echo ""

# 2. Find REDACTED tokens
echo "2️⃣  Checking for REDACTED tokens..."
REDACTED_COUNT=$(grep -r "REDACTED" src/ public/ 2>/dev/null | wc -l)
if [ "$REDACTED_COUNT" -gt 0 ]; then
    echo -e "${RED}❌ FOUND $REDACTED_COUNT REDACTED TOKENS${NC}"
    echo "   These break PHP syntax!"
    grep -r "REDACTED" src/ public/ 2>/dev/null | head -10
    echo "   ..."
    echo "   ACTION: Replace with proper Config lookups"
else
    echo -e "${GREEN}✅ No REDACTED tokens found${NC}"
fi
echo ""

# 3. Check for CORS issues
echo "3️⃣  Checking for over-permissive CORS..."
if grep -r "Access-Control-Allow-Origin: \*" public/api/ 2>/dev/null; then
    echo -e "${RED}❌ FOUND CORS WILDCARD${NC}"
    echo "   Never use * with credentials!"
    echo "   ACTION: Restrict to allowed origins"
else
    echo -e "${GREEN}✅ No CORS wildcards found${NC}"
fi
echo ""

# 4. Check for diagnostic leakage
echo "4️⃣  Checking for diagnostic leakage..."
if [ -f "public/api/diagnostic.php" ]; then
    if grep -q "display_errors.*1\|file_exists.*\.env" public/api/diagnostic.php 2>/dev/null; then
        echo -e "${RED}❌ DIAGNOSTIC ENDPOINT LEAKS INFO${NC}"
        echo "   ACTION: Add admin check or remove from production"
    else
        echo -e "${GREEN}✅ Diagnostic endpoint appears safe${NC}"
    fi
else
    echo -e "${GREEN}✅ No diagnostic endpoint found${NC}"
fi
echo ""

# 5. Check PHP syntax errors
echo "5️⃣  Checking for PHP syntax errors..."
SYNTAX_ERRORS=0
for file in $(find src/ public/api/ -name "*.php" 2>/dev/null); do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}❌ SYNTAX ERROR: $file${NC}"
        ((SYNTAX_ERRORS++))
    fi
done

if [ "$SYNTAX_ERRORS" -eq 0 ]; then
    echo -e "${GREEN}✅ No PHP syntax errors found${NC}"
else
    echo -e "${RED}❌ FOUND $SYNTAX_ERRORS FILES WITH SYNTAX ERRORS${NC}"
    echo "   ACTION: Fix REDACTED tokens and other syntax issues"
fi
echo ""

# 6. Check for transactions
echo "6️⃣  Checking MemoryCompressor for transactions..."
if [ -f "src/Memory/MemoryCompressor.php" ]; then
    if grep -q "beginTransaction\|START TRANSACTION" src/Memory/MemoryCompressor.php; then
        echo -e "${GREEN}✅ MemoryCompressor uses transactions${NC}"
    else
        echo -e "${RED}❌ MemoryCompressor missing transactions${NC}"
        echo "   Risk: Data loss if compression fails mid-operation"
        echo "   ACTION: Wrap DELETE + INSERT in transaction"
    fi
else
    echo -e "${YELLOW}⚠️  MemoryCompressor not found${NC}"
fi
echo ""

# 7. Check for Redis KEYS usage
echo "7️⃣  Checking for Redis KEYS anti-pattern..."
if grep -r "->keys(" public/api/ src/ 2>/dev/null; then
    echo -e "${RED}❌ FOUND Redis KEYS() USAGE${NC}"
    echo "   This blocks Redis!"
    echo "   ACTION: Replace with SCAN or use counters"
else
    echo -e "${GREEN}✅ No Redis KEYS usage found${NC}"
fi
echo ""

# 8. Check .env security
echo "8️⃣  Checking .env file permissions..."
if [ -f ".env" ]; then
    PERMS=$(stat -c '%a' .env)
    if [ "$PERMS" = "600" ]; then
        echo -e "${GREEN}✅ .env has correct permissions (600)${NC}"
    else
        echo -e "${RED}❌ .env has wrong permissions ($PERMS)${NC}"
        echo "   ACTION: chmod 600 .env"
    fi
else
    echo -e "${YELLOW}⚠️  .env file not found${NC}"
fi
echo ""

# Summary
echo "==============================="
echo "📊 AUDIT SUMMARY"
echo "==============================="
echo ""
echo "✅ = Fixed or safe"
echo "❌ = Needs immediate attention"
echo "⚠️  = Review required"
echo ""
echo "📋 NEXT STEPS:"
echo ""
echo "1. Review CRITICAL_FIXES_PRIORITY_LIST.md"
echo "2. Fix P0 issues first (security)"
echo "3. Run tests after each fix"
echo "4. Commit changes with clear messages"
echo ""
echo "🚀 QUICK FIXES:"
echo ""
echo "# Fix .env permissions"
echo "chmod 600 .env"
echo ""
echo "# Find all hardcoded credentials"
echo "grep -r 'new mysqli.*jcepnzzkmj' public/ src/"
echo ""
echo "# Find all REDACTED tokens"
echo "grep -r 'REDACTED' src/ public/"
echo ""
echo "# Check PHP syntax"
echo "find . -name '*.php' -exec php -l {} \; 2>&1 | grep -v 'No syntax errors'"
echo ""
echo "==============================="
echo "Audit complete! Review output above."
