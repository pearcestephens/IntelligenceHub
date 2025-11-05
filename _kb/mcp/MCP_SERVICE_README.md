# MCP Server - Permanent Service Installation

## ✅ Status: READY FOR INSTALLATION

The MCP server is configured to run as a permanent systemd service with auto-restart and health monitoring.

## 📋 What's Included

### 1. **MCP Server (Web-based)**
- Location: `server_v3.php`
- URL: `https://gpt.ecigdis.co.nz/mcp/server_v3.php`
- Tools: 54 available
- Status: ✅ ONLINE (via Nginx/Apache)

### 2. **Health Monitor Service**
- Script: `mcp-health-monitor.php`
- Service: `mcp-server.service`
- Function: Continuous health checks every 60 seconds
- Features:
  - HTTP health checks
  - Response time monitoring
  - Tool count verification
  - Database metrics storage
  - Auto-alerting on failures
  - Graceful shutdown handling

### 3. **Database Tables** ✅ CREATED
- `mcp_health_log` - Health check results
- `mcp_service_metrics` - Performance metrics
- `mcp_tool_usage` - Tool usage tracking

## 🚀 Installation

Run as root/sudo:

```bash
sudo bash /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/install-mcp-service.sh
```

This will:
1. ✓ Verify MCP directory and files
2. ✓ Create logs directory
3. ✓ Make health monitor executable
4. ✓ Install systemd service
5. ✓ Enable auto-start on boot
6. ✓ Start the service immediately

## 📊 Service Management

### View Status
```bash
systemctl status mcp-server
```

### View Real-time Logs
```bash
journalctl -u mcp-server -f
```

### View Health Log File
```bash
tail -f /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/logs/health-monitor.log
```

### Restart Service
```bash
sudo systemctl restart mcp-server
```

### Stop Service
```bash
sudo systemctl stop mcp-server
```

### Disable Auto-start
```bash
sudo systemctl disable mcp-server
```

## 🔧 Configuration

Service file location: `/etc/systemd/system/mcp-server.service`

Environment variables:
- `MCP_SERVER_URL` - Server endpoint
- `MCP_API_KEY` - Authentication key
- `PROJECT_ID` - Project identifier (2)
- `BUSINESS_UNIT_ID` - Business unit (2)
- `WORKSPACE_ROOT` - Workspace path

## 📈 Monitoring

### Database Queries

**Recent health checks:**
```sql
SELECT * FROM mcp_health_log
ORDER BY checked_at DESC
LIMIT 10;
```

**Health status summary:**
```sql
SELECT
    status,
    COUNT(*) as count,
    AVG(response_time_ms) as avg_response_ms
FROM mcp_health_log
WHERE checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY status;
```

**Tool usage statistics:**
```sql
SELECT
    tool_name,
    COUNT(*) as usage_count,
    AVG(execution_time_ms) as avg_time_ms,
    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count
FROM mcp_tool_usage
WHERE called_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY tool_name
ORDER BY usage_count DESC;
```

## 🔄 Auto-Restart Configuration

The service is configured to:
- ✅ Restart automatically on failure
- ✅ Wait 10 seconds between restart attempts
- ✅ No restart limit (will always try to recover)
- ✅ Start automatically on system boot
- ✅ Resource limits: 512MB RAM, 50% CPU

## 🎯 Features

### Health Checks
- HTTP status code verification
- Response time measurement
- Tool availability count
- Error detection and logging

### Alert System
- 3 consecutive failures trigger alerts
- Status change notifications
- Critical error logging

### Metrics Storage
- Every check logged to database
- Historical performance data
- Trend analysis capability

## 📝 Logs

### Systemd Journal
```bash
journalctl -u mcp-server --since today
journalctl -u mcp-server --since "1 hour ago"
journalctl -u mcp-server -n 100
```

### Application Log
```bash
cat /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/logs/health-monitor.log
```

## 🔐 Security

- Service runs as user `129337`
- API key required for all requests
- Resource limits enforced
- Logs rotation handled by systemd

## ✨ Next Steps

1. **Install the service** (run the installation script as sudo)
2. **Verify it's running** (`systemctl status mcp-server`)
3. **Monitor the logs** (`journalctl -u mcp-server -f`)
4. **Check database metrics** (run the SQL queries above)

## 🆘 Troubleshooting

### Service won't start
```bash
sudo journalctl -u mcp-server -xe
```

### Check PHP syntax
```bash
php -l /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-health-monitor.php
```

### Test health check manually
```bash
php /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/mcp-health-monitor.php
```

### Check MCP server directly
```bash
curl -s https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -X POST \
  -d '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":1}' \
  -H "Content-Type: application/json" | jq .
```

---

**Status**: ✅ Ready for production deployment
**Last Updated**: 2025-11-05
**Version**: 1.0.0
