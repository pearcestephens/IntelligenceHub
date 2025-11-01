#!/bin/bash
# ================================================================
# Master Production Setup Command
# One command to rule them all
# ================================================================

set -e

PROJECT_ROOT="/home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent"
cd "$PROJECT_ROOT"

echo ""
echo "🚀 AI AGENT PRODUCTION SETUP & VALIDATION"
echo "=========================================="
echo ""

# Make scripts executable
chmod +x bin/*.sh bin/*.php 2>/dev/null || true

# Run PHP setup
echo "Running comprehensive setup..."
echo ""
php bin/setup-production.php

SETUP_EXIT=$?

echo ""
echo "=========================================="
echo ""

if [ $SETUP_EXIT -eq 0 ]; then
    echo "✅ Setup completed successfully!"
    echo ""
    echo "Would you like to run PHPUnit tests? (y/n)"
    read -t 10 -r REPLY || REPLY="n"
    
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo ""
        echo "Running PHPUnit tests..."
        echo ""
        php vendor/bin/phpunit --testdox
        echo ""
    fi
    
    echo ""
    echo "=========================================="
    echo "  ✅ SYSTEM READY FOR PRODUCTION"
    echo "=========================================="
    echo ""
    echo "Quick Links:"
    echo "  • Analytics: https://staff.vapeshed.co.nz/assets/neuro/ai-agent/public/analytics-dashboard.html"
    echo "  • Dev Center: https://staff.vapeshed.co.nz/assets/neuro/dev-center/"
    echo "  • Logs: $PROJECT_ROOT/logs/"
    echo ""
    echo "Monitoring Commands:"
    echo "  • Tail logs: tail -f logs/*.log"
    echo "  • Check Redis: redis-cli -h 127.0.0.1 ping"
    echo "  • Check DB: mysql -h 127.0.0.1 -u jcepnzzkmj -pwprKh9Jq63 jcepnzzkmj -e 'SELECT COUNT(*) FROM importance_scores'"
    echo ""
    
    exit 0
else
    echo "❌ Setup encountered errors. Please review output above."
    exit 1
fi
