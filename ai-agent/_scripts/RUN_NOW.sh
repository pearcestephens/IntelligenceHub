#!/bin/bash
# =====================================================
# IMMEDIATE FIX: Run Test Database Setup Now
# =====================================================

set -e

cd "$(dirname "$0")"

echo "🚀 Executing BATCH-7 Test Database Setup..."
echo ""

# Navigate to project root
cd /home/master/applications/jcepnzzkmj/public_html/assets/neuro/ai-agent

# Make scripts executable
chmod +x bin/*.sh bin/*.php batch-7-setup.sh 2>/dev/null || true

# Execute setup
bash batch-7-setup.sh

echo ""
echo "✅ Setup complete! Tests should now work."
