#!/bin/bash

# Frontend Integration - Database Setup Script
# Created: 2025-11-04
# This script installs all 9 frontend integration tables

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  Frontend Integration - Database Installation              ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Database credentials
DB_HOST="localhost"
DB_NAME="hdgwrzntwa"
DB_USER="hdgwrzntwa"
DB_PASS="bFUdRjh4Jx"

# Path to SQL file
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SQL_FILE="${SCRIPT_DIR}/frontend_integration_schema.sql"

# Check if SQL file exists
if [ ! -f "$SQL_FILE" ]; then
    echo "❌ ERROR: SQL file not found: $SQL_FILE"
    exit 1
fi

echo "📋 Configuration:"
echo "   Database: $DB_NAME"
echo "   Host: $DB_HOST"
echo "   User: $DB_USER"
echo "   SQL File: $SQL_FILE"
echo ""

# Confirm before proceeding
read -p "⚠️  This will create 9 new tables. Continue? (y/N) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Installation cancelled"
    exit 1
fi

echo ""
echo "🔄 Installing database schema..."
echo ""

# Run the SQL file
mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_FILE"

# Check if successful
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ SUCCESS! Frontend integration database schema installed"
    echo ""
    echo "📊 Tables created:"
    echo "   1. frontend_pending_fixes"
    echo "   2. frontend_workflows"
    echo "   3. frontend_workflow_executions"
    echo "   4. frontend_audit_history"
    echo "   5. frontend_monitors"
    echo "   6. frontend_monitor_alerts"
    echo "   7. frontend_screenshot_gallery"
    echo "   8. frontend_visual_regression"
    echo "   9. frontend_deployment_log"
    echo ""
    echo "🎯 Sample workflows inserted:"
    echo "   - Quick Page Audit"
    echo "   - Auto-Fix Pipeline"
    echo "   - 24/7 Monitoring"
    echo ""
    echo "🚀 Next steps:"
    echo "   1. Visit: https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/approvals.php"
    echo "   2. Visit: https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/workflows.php"
    echo "   3. Run documentation indexer:"
    echo "      php /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/scripts/index_documentation.php"
    echo ""
else
    echo ""
    echo "❌ ERROR: Installation failed"
    echo ""
    echo "Troubleshooting:"
    echo "   1. Check database credentials"
    echo "   2. Ensure MySQL is running"
    echo "   3. Verify user has CREATE TABLE permissions"
    echo "   4. Check SQL file for syntax errors"
    echo ""
    exit 1
fi
