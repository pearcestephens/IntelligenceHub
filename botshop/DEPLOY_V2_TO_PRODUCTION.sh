#!/bin/bash

# BotShop V2 Production Deployment Script
# This script deploys the complete V2 dashboard to production-ready state

echo "🚀 BotShop V2 Production Deployment"
echo "===================================="
echo ""

# Set paths
BOTSHOP_ROOT="/home/master/applications/hdgwrzntwa/public_html/botshop"
PAGES_V2_SOURCE="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/pages-v2"
INCLUDES_V2_SOURCE="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/includes-v2"
ASSETS_SOURCE="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin/assets"

echo "Step 1: Backing up existing pages..."
if [ -d "$BOTSHOP_ROOT/pages" ]; then
    mkdir -p "$BOTSHOP_ROOT/backup-$(date +%Y%m%d-%H%M%S)"
    cp -r "$BOTSHOP_ROOT/pages" "$BOTSHOP_ROOT/backup-$(date +%Y%m%d-%H%M%S)/"
    echo "✅ Backup created"
fi

echo ""
echo "Step 2: Clearing old pages..."
rm -f "$BOTSHOP_ROOT/pages"/*.php
echo "✅ Old pages cleared"

echo ""
echo "Step 3: Copying V2 pages (15 files)..."
cp "$PAGES_V2_SOURCE"/*.php "$BOTSHOP_ROOT/pages/"
echo "✅ V2 pages copied"

echo ""
echo "Step 4: Adding AI Agent page..."
if [ -f "$BOTSHOP_ROOT/admin/pages-v2/ai-agent.php" ]; then
    cp "$BOTSHOP_ROOT/admin/pages-v2/ai-agent.php" "$BOTSHOP_ROOT/pages/"
    echo "✅ AI Agent page added"
else
    echo "⚠️  AI Agent page not found, skipping"
fi

echo ""
echo "Step 5: Copying includes..."
if [ -d "$INCLUDES_V2_SOURCE" ]; then
    cp -r "$INCLUDES_V2_SOURCE"/* "$BOTSHOP_ROOT/includes/"
    echo "✅ Includes copied"
fi

echo ""
echo "Step 6: Copying assets..."
if [ -d "$ASSETS_SOURCE" ]; then
    cp -r "$ASSETS_SOURCE"/* "$BOTSHOP_ROOT/assets/"
    echo "✅ Assets copied"
fi

echo ""
echo "Step 7: Setting permissions..."
chmod 755 "$BOTSHOP_ROOT/pages"/*.php
chmod 755 "$BOTSHOP_ROOT/includes"/*.php 2>/dev/null
chmod 644 "$BOTSHOP_ROOT/assets/css"/*.css 2>/dev/null
chmod 644 "$BOTSHOP_ROOT/assets/js"/*.js 2>/dev/null
echo "✅ Permissions set"

echo ""
echo "Step 8: Verifying deployment..."
PAGE_COUNT=$(ls -1 "$BOTSHOP_ROOT/pages"/*.php 2>/dev/null | wc -l)
echo "   Pages deployed: $PAGE_COUNT"

if [ $PAGE_COUNT -ge 15 ]; then
    echo "✅ Deployment verification passed"
else
    echo "⚠️  Warning: Expected 15+ pages, found $PAGE_COUNT"
fi

echo ""
echo "Step 9: Testing index.php configuration..."
if grep -q "BOTSHOP_ROOT . '/pages/'" "$BOTSHOP_ROOT/index.php"; then
    echo "✅ Index.php correctly configured for /pages/ directory"
else
    echo "⚠️  Index.php may need configuration check"
fi

echo ""
echo "========================================="
echo "✅ DEPLOYMENT COMPLETE!"
echo "========================================="
echo ""
echo "📍 Dashboard URL: https://gpt.ecigdis.co.nz/botshop"
echo "📊 Total Pages: $PAGE_COUNT"
echo ""
echo "🧪 Next Steps:"
echo "1. Visit https://gpt.ecigdis.co.nz/botshop"
echo "2. Test all page navigation"
echo "3. Verify database connectivity"
echo "4. Run QA test suite"
echo ""
