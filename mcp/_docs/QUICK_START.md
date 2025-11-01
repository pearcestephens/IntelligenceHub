# MCP Intelligence Hub - Quick Start Deployment

**Status:** Phase 1 Complete (23/23 tests passing - 100%)
**Date:** October 28, 2025

---

## 🚀 30-Second Quick Deploy

### Prerequisites Check
```bash
cd /home/master/applications/hdgwrzntwa/public_html/mcp

# 1. Verify all tests pass
bash tests/run_all_tests.sh

# Expected: "🎉 ALL TESTS PASSED - PHASE 1 COMPLETE!"
```

### Critical Configuration (DO THIS FIRST)

**Update Database Password:**
```bash
# Edit the .env file
nano .env

# Update this line with actual password:
DB_PASS=your_actual_database_password_here
```

### Verify Deployment Readiness
```bash
# Run pre-deployment verification
bash scripts/verify_deployment.sh

# Expected: "🎉 VERIFICATION SUCCESSFUL - READY FOR DEPLOYMENT"
```

---

## ✅ Current Status

### All Tests Passing ✅
- **Semantic Search:** 5/5 tests (90ms avg)
- **PHPIndexer:** 6/6 tests (170ms avg)
- **HTTP Endpoint:** 6/6 tests (1271ms avg)
- **Cache Fallback:** 6/6 tests (127ms avg)
- **Total:** 23/23 tests (100%)

### Performance Validated ✅
- Cache speedup: **14,175x** (3402ms → 0.24ms)
- Cached query time: **0.24ms** (target <5ms) ✅
- Redis: 11.88ms per 100 ops
- FileCache: 18.23ms per 100 ops (fallback ready)

### System Ready ✅
- Redis connection: Working
- APCu available: Yes
- FileCache fallback: Tested
- Multi-tier caching: Operational
- Rate limiting: Configured
- CORS: Enabled

---

## 🎯 What's Working Right Now

### HTTP Endpoint
```bash
# Test the health endpoint
curl https://gpt.ecigdis.co.nz/mcp/server_v3.php \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"health_check"},"id":1}'

# Response: {"success":true,"health":"ok",...}
```

### CLI Interface
```bash
# Search via CLI
php cli/mcp search "database connection" --limit=5

# Results with colored output + cache timing
```

### Cache System
- **Redis** (primary): 35% faster, tested
- **APCu** (secondary): Available
- **FileCache** (tertiary): 30,972x speedup confirmed

---

## 📊 Monitoring (Optional but Recommended)

### Real-Time Stats
```bash
# View performance statistics
php scripts/performance_stats.php

# Output:
# - Cache backend status
# - Hit rate (target >80%)
# - Redis memory usage
# - Database statistics
```

### Health Monitoring
```bash
# Manual health check
bash scripts/monitor_health.sh

# View log
tail -f /home/master/applications/hdgwrzntwa/private_html/logs/health_monitor.log
```

### Add to Crontab (Recommended)
```bash
crontab -e

# Add these lines:
# Health monitoring every 5 minutes
*/5 * * * * /home/master/applications/hdgwrzntwa/public_html/mcp/scripts/monitor_health.sh

# Daily backup at 1 AM
0 1 * * * /home/master/applications/hdgwrzntwa/public_html/mcp/scripts/backup_data.sh
```

---

## 🆘 Troubleshooting

### Issue: Database Connection Failed
**Solution:**
```bash
# 1. Update .env with correct password
nano mcp/.env  # Set DB_PASS=actual_password

# 2. Test connection
php -r "
require 'vendor/autoload.php';
\$config = require 'config/database.php';
\$pdo = new PDO(
    \"mysql:host={\$config['host']};dbname={\$config['database']}\",
    \$config['username'],
    \$config['password']
);
echo 'Connected successfully\n';
"
```

### Issue: Redis Not Available
**Solution:** System will auto-fallback to FileCache (tested, working)

### Issue: Cache Directory Not Writable
**Solution:**
```bash
chmod 755 /home/master/applications/hdgwrzntwa/public_html/mcp/cache
```

---

## 📁 Important Files

### Configuration
- `.env` - Environment variables (UPDATE DATABASE PASSWORD!)
- `config/database.php` - Database configuration
- `composer.json` - Dependencies

### Entry Points
- `server_v3.php` - HTTP MCP endpoint
- `cli/mcp` - Command-line interface

### Scripts
- `scripts/verify_deployment.sh` - Pre-deployment checks
- `scripts/monitor_health.sh` - Health monitoring
- `scripts/backup_data.sh` - Data backup
- `scripts/performance_stats.php` - Performance dashboard

### Tests
- `tests/run_all_tests.sh` - Run all test suites
- `tests/semantic_search_test.php` - Search tests
- `tests/php_indexer_test.php` - Indexer tests
- `tests/endpoint_test.php` - HTTP tests
- `tests/cache_fallback_test.php` - Cache tests

---

## 🎉 Success Criteria

After updating `.env` with database password:

```bash
# Run verification
bash scripts/verify_deployment.sh

# Expected output:
# ✅ Passed: 31
# ❌ Failed: 0
# ⚠️  Warnings: 0
#
# 🎉 VERIFICATION SUCCESSFUL - READY FOR DEPLOYMENT
```

---

## 📞 Need Help?

**Full Documentation:**
- `DEPLOYMENT_GUIDE.md` - Complete deployment guide (60+ pages)
- `PHASE_1_COMPLETE.md` - Technical documentation & architecture

**Contact:**
- Primary: Pearce Stephens <pearce.stephens@ecigdis.co.nz>

---

## 🚀 Next Steps After Deployment

1. **Monitor for 24 hours**
   - Check health logs
   - Watch cache hit rate
   - Monitor response times

2. **Success metrics** (24h target):
   - Cache hit rate >80%
   - Response time <5ms (cached)
   - Zero critical errors
   - System uptime >99.9%

3. **Phase 2 Planning**
   - See `PHASE_1_COMPLETE.md` for recommendations
   - Distributed caching
   - Advanced search features
   - AI integration

---

**Last Updated:** October 28, 2025
**Version:** 1.0.0 (Phase 1 Complete)
**Test Status:** 23/23 passing (100%)
