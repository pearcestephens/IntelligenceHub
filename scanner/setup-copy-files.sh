#!/bin/bash
#
# Copy Dashboard Admin V2 to Scanner Application
# This script copies all pages-v2 files and assets to the new scanner directory
#

set -e

echo "=========================================="
echo "Scanner Setup - Copying V2 Files"
echo "=========================================="
echo ""

SOURCE_BASE="/home/master/applications/hdgwrzntwa/public_html/dashboard/admin"
DEST_BASE="/home/master/applications/hdgwrzntwa/public_html/scanner"

# Copy all page files
echo "→ Copying page files from pages-v2..."
cp -v "${SOURCE_BASE}/pages-v2/"*.php "${DEST_BASE}/pages/" 2>/dev/null || echo "  (some files may already exist)"

# Copy CSS files
echo ""
echo "→ Copying CSS assets..."
cp -v "${SOURCE_BASE}/assets/css/"*.css "${DEST_BASE}/assets/css/" 2>/dev/null || echo "  (some files may already exist)"

# Copy JS files
echo ""
echo "→ Copying JavaScript assets..."
cp -v "${SOURCE_BASE}/assets/js/"*.js "${DEST_BASE}/assets/js/" 2>/dev/null || echo "  (some files may already exist)"

# Count files
echo ""
echo "=========================================="
echo "Copy Complete!"
echo "=========================================="
echo ""
echo "Files in scanner/pages/: $(ls -1 ${DEST_BASE}/pages/*.php 2>/dev/null | wc -l)"
echo "Files in scanner/assets/css/: $(ls -1 ${DEST_BASE}/assets/css/*.css 2>/dev/null | wc -l)"
echo "Files in scanner/assets/js/: $(ls -1 ${DEST_BASE}/assets/js/*.js 2>/dev/null | wc -l)"
echo ""
echo "Next: Access at https://[domain]/scanner/"
echo ""
