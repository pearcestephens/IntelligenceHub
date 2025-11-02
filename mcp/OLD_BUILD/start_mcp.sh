#!/bin/bash
#
# CIS Intelligence - MCP Server Launcher
# 
# Starts the Model Context Protocol server for GitHub Copilot integration
#
# Usage:
#   ./start_mcp.sh           (foreground)
#   ./start_mcp.sh --daemon  (background)
#

cd "$(dirname "$0")"

if [[ "$1" == "--daemon" ]]; then
    echo "🚀 Starting MCP server in background..."
    nohup node server.js > /tmp/mcp-server.log 2>&1 &
    echo "✅ MCP server started (PID: $!)"
    echo "📝 Logs: tail -f /tmp/mcp-server.log"
else
    echo "🚀 Starting MCP server..."
    node server.js
fi
