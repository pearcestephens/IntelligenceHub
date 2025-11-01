#!/bin/bash
#
# Make all scripts in bin/ executable
#

cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/bin

chmod +x *.sh 2>/dev/null || true
chmod +x *.php 2>/dev/null || true

echo "✓ All scripts in bin/ are now executable"
ls -lah *.sh 2>/dev/null | grep -E '\.sh$'
