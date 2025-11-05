#!/bin/bash
#
# MCP Server Installation Script
# Installs and enables the MCP health monitor as a systemd service
#

set -e

echo "==================================="
echo "MCP Server Service Installation"
echo "==================================="
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo "ERROR: This script must be run with sudo"
    echo "Usage: sudo bash install-mcp-service.sh"
    exit 1
fi

MCP_DIR="/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp"
SERVICE_FILE="$MCP_DIR/mcp-server.service"
SYSTEMD_DIR="/etc/systemd/system"

echo "✓ Checking MCP directory..."
if [ ! -d "$MCP_DIR" ]; then
    echo "ERROR: MCP directory not found: $MCP_DIR"
    exit 1
fi

echo "✓ Checking service file..."
if [ ! -f "$SERVICE_FILE" ]; then
    echo "ERROR: Service file not found: $SERVICE_FILE"
    exit 1
fi

echo "✓ Creating logs directory..."
mkdir -p "$MCP_DIR/logs"
chown 129337:129337 "$MCP_DIR/logs"
chmod 755 "$MCP_DIR/logs"

echo "✓ Making health monitor executable..."
chmod +x "$MCP_DIR/mcp-health-monitor.php"

echo "✓ Installing systemd service..."
cp "$SERVICE_FILE" "$SYSTEMD_DIR/mcp-server.service"

echo "✓ Reloading systemd daemon..."
systemctl daemon-reload

echo "✓ Enabling MCP server service (auto-start on boot)..."
systemctl enable mcp-server.service

echo "✓ Starting MCP server service..."
systemctl start mcp-server.service

echo ""
echo "==================================="
echo "Installation Complete!"
echo "==================================="
echo ""
echo "Service Status:"
systemctl status mcp-server.service --no-pager

echo ""
echo "Useful Commands:"
echo "  View status:  systemctl status mcp-server"
echo "  View logs:    journalctl -u mcp-server -f"
echo "  Restart:      sudo systemctl restart mcp-server"
echo "  Stop:         sudo systemctl stop mcp-server"
echo "  Disable:      sudo systemctl disable mcp-server"
echo ""
echo "Health log location: $MCP_DIR/logs/health-monitor.log"
echo ""
