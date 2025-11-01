# CIS Knowledge Base Cron Job Templates

**Purpose:** Automated Knowledge Base maintenance and refresh cycles  
**Created:** October 12, 2025  
**Last Updated:** October 12, 2025  

---

## 📋 Cron Job Overview

This document provides ready-to-use cron job templates for automating Knowledge Base operations. The cron jobs ensure regular updates, maintenance, and monitoring of the CIS Knowledge Base system.

---

## ⚡ Quick Start Cron Setup

### Automated Installation
Run the setup wizard to automatically configure cron jobs:

```bash
# Navigate to KB tools directory
cd /path/to/your/services/_kb/tools/

# Run setup with cron auto-install
php setup-kb.php --auto-cron

# Or manually install cron jobs
php setup-kb.php --cron-only
```

### Manual Installation
Copy the desired cron entries from this document and add them to your crontab:

```bash
# Edit current user's crontab
crontab -e

# Or edit system-wide cron (requires sudo)
sudo crontab -e
```

---

## 🕐 Production Cron Schedule

### Standard Production Environment

```bash
# CIS Knowledge Base Automated Maintenance
# Generated: October 12, 2025

# === HOURLY OPERATIONS ===
# Quick KB refresh every hour during business hours (8 AM - 8 PM)
0 8-20 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/cron.log 2>&1

# === DAILY OPERATIONS ===
# Full KB refresh daily at 2 AM
0 2 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=full --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/cron.log 2>&1

# Performance analysis daily at 3 AM
0 3 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php analyze-performance.php --auto --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/performance.log 2>&1

# Relationship mapping daily at 4 AM
0 4 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php map-relationships.php --full --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/relationships.log 2>&1

# === WEEKLY OPERATIONS ===
# Complete KB verification every Sunday at 1 AM
0 1 * * 0 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php verify-kb.php --fix --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/verification.log 2>&1

# Log cleanup every Sunday at 5 AM (keep last 30 days)
0 5 * * 0 find /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs -name "*.log" -mtime +30 -delete 2>/dev/null

# Cache cleanup every Sunday at 6 AM
0 6 * * 0 find /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/cache -type f -mtime +7 -delete 2>/dev/null

# === MONTHLY OPERATIONS ===
# Deep analysis and reporting first day of month at midnight
0 0 1 * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=deep --report --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/monthly.log 2>&1
```

---

## 🔧 Development Environment Cron

### Development/Staging Environment

```bash
# CIS Knowledge Base Development Cron Jobs
# More frequent updates for development environment

# === FREQUENT OPERATIONS ===
# Quick refresh every 30 minutes during work hours
*/30 9-17 * * 1-5 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/dev-cron.log 2>&1

# === DAILY OPERATIONS ===
# Full refresh at end of day
0 18 * * 1-5 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=full --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/dev-cron.log 2>&1

# Performance check daily
0 19 * * 1-5 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php analyze-performance.php --quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/dev-performance.log 2>&1

# === WEEKLY OPERATIONS ===
# Weekly verification every Friday evening
0 20 * * 5 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php verify-kb.php --fix --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/dev-verification.log 2>&1

# Log cleanup weekly (keep last 7 days)
0 21 * * 5 find /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs -name "*.log" -mtime +7 -delete 2>/dev/null
```

---

## 🚀 High-Frequency Environment Cron

### High-Traffic/Critical Systems

```bash
# CIS Knowledge Base High-Frequency Cron Jobs
# For systems requiring real-time KB updates

# === FREQUENT OPERATIONS ===
# Quick refresh every 15 minutes
*/15 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/highfreq-cron.log 2>&1

# Performance monitoring every hour
0 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php analyze-performance.php --monitor --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/performance-monitor.log 2>&1

# === DAILY OPERATIONS ===
# Full refresh twice daily
0 6,18 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=full --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/highfreq-full.log 2>&1

# Relationship analysis twice daily
0 7,19 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php map-relationships.php --incremental --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/relationships.log 2>&1

# === VERIFICATION ===
# Daily verification
0 23 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php verify-kb.php --quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/verification.log 2>&1
```

---

## 📊 Monitoring & Alert Cron Jobs

### System Health Monitoring

```bash
# CIS Knowledge Base Health Monitoring
# Automated health checks and alerting

# === HEALTH CHECKS ===
# KB system health check every 5 minutes
*/5 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php verify-kb.php --health-check --silent || echo "KB Health Check Failed: $(date)" >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/health-alerts.log

# Performance threshold monitoring every 10 minutes
*/10 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php analyze-performance.php --threshold-check --silent || echo "Performance Threshold Exceeded: $(date)" >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/performance-alerts.log

# === ALERT PROCESSING ===
# Process and send alerts every 15 minutes
*/15 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php process-alerts.php --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/alerts.log 2>&1

# === BACKUP VERIFICATION ===
# Verify KB data integrity every 4 hours
0 */4 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php verify-kb.php --data-integrity --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/integrity.log 2>&1
```

---

## 🔄 Custom Cron Templates

### Template 1: Module-Specific Updates

```bash
# Custom: Module-specific KB updates
# Update specific modules more frequently

# Inventory module refresh every 30 minutes
*/30 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --modules=inventory --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/inventory-kb.log 2>&1

# Pack module refresh every hour
0 * * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --modules=pack --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/pack-kb.log 2>&1

# Queue module refresh every 2 hours
0 */2 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --modules=queue --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/queue-kb.log 2>&1
```

### Template 2: Performance-Focused Schedule

```bash
# Custom: Performance-optimized schedule
# Balanced approach for performance and accuracy

# Light refresh during business hours
0 9,12,15,18 * * 1-5 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=quick --performance-mode --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/perf-cron.log 2>&1

# Heavy operations during off hours
0 22 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=full && php analyze-performance.php --full && php map-relationships.php --full --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/heavy-ops.log 2>&1
```

### Template 3: Minimal Resource Usage

```bash
# Custom: Minimal resource usage
# For resource-constrained environments

# Daily updates only
0 2 * * * cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=quick --low-memory --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/minimal-cron.log 2>&1

# Weekly full operations
0 3 * * 0 cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools && php refresh-kb.php --mode=full --low-memory && php verify-kb.php --quick --silent >> /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/weekly-ops.log 2>&1
```

---

## 🛠️ Cron Management Commands

### Installation Commands

```bash
# Install production cron jobs
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools
php setup-kb.php --install-cron=production

# Install development cron jobs
php setup-kb.php --install-cron=development

# Install high-frequency cron jobs
php setup-kb.php --install-cron=high-frequency

# Install monitoring cron jobs
php setup-kb.php --install-cron=monitoring
```

### Verification Commands

```bash
# List current KB-related cron jobs
crontab -l | grep "_kb"

# Test cron job syntax
php setup-kb.php --test-cron

# Verify cron job permissions
php setup-kb.php --verify-cron-permissions

# Check cron job logs
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/cron.log
```

### Maintenance Commands

```bash
# Remove all KB cron jobs
php setup-kb.php --remove-cron

# Update existing cron jobs
php setup-kb.php --update-cron

# Backup current cron configuration
crontab -l > /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/backup/crontab-backup-$(date +%Y%m%d).txt

# Restore cron configuration
crontab /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/backup/crontab-backup-YYYYMMDD.txt
```

---

## 📋 Cron Job Variables

### Environment Variables

```bash
# Set these variables in your cron environment
# Add to top of crontab or /etc/environment

# PHP executable path
PHP_BIN="/usr/bin/php"

# KB base directory
KB_BASE="/home/master/applications/jcepnzzkmj/public_html/assets/services/_kb"

# KB tools directory
KB_TOOLS="${KB_BASE}/tools"

# KB logs directory
KB_LOGS="${KB_BASE}/logs"

# Email for cron notifications
MAILTO="admin@yourcompany.com"

# PATH for cron jobs
PATH="/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"
```

### Using Variables in Cron

```bash
# Example cron entry using variables
0 2 * * * cd $KB_TOOLS && $PHP_BIN refresh-kb.php --mode=full --silent >> $KB_LOGS/cron.log 2>&1
```

---

## 🔐 Security Considerations

### File Permissions

```bash
# Set proper permissions for cron scripts
chmod 755 /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools/*.php

# Secure log directory
chmod 750 /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs
chown -R www-data:www-data /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs

# Secure configuration files
chmod 640 /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/.kb-config.json
```

### Cron Security

```bash
# Use dedicated user for cron jobs (recommended)
sudo useradd -r -s /bin/bash kb-cron
sudo usermod -a -G www-data kb-cron

# Set up cron for dedicated user
sudo crontab -u kb-cron -e

# Restrict cron access
echo "kb-cron" | sudo tee -a /etc/cron.allow
```

---

## 📊 Monitoring Cron Jobs

### Log Analysis

```bash
# Monitor cron job execution
tail -f /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/cron.log

# Check for errors
grep -i error /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/*.log

# Monitor performance
grep "execution time" /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/performance.log

# Check cron job success rates
grep -c "completed successfully" /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/logs/cron.log
```

### Alert Setup

```bash
# Set up email alerts for failed cron jobs
# Add to crontab header:
MAILTO="admin@yourcompany.com"

# Or use custom alerting
*/5 * * * * cd $KB_TOOLS && php check-cron-health.php --alert-if-failed >> $KB_LOGS/cron-health.log 2>&1
```

---

## 🚨 Troubleshooting Cron Jobs

### Common Issues

#### Issue: Cron Jobs Not Running
**Symptoms:** No log entries, KB not updating  
**Solutions:**
```bash
# Check cron service status
systemctl status cron

# Restart cron service
sudo systemctl restart cron

# Verify cron entries
crontab -l

# Check system logs
sudo tail -f /var/log/syslog | grep CRON
```

#### Issue: Permission Denied Errors
**Symptoms:** Permission errors in logs  
**Solutions:**
```bash
# Fix file permissions
chmod +x /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools/*.php

# Fix directory permissions
chmod 755 /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb

# Check ownership
ls -la /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/tools/
```

#### Issue: PHP Not Found
**Symptoms:** "php: command not found" in logs  
**Solutions:**
```bash
# Find PHP path
which php

# Update cron to use full path
/usr/bin/php /path/to/script.php

# Or set PATH in crontab
PATH=/usr/local/bin:/usr/bin:/bin
```

#### Issue: High Resource Usage
**Symptoms:** Server slow during cron execution  
**Solutions:**
```bash
# Use low-priority execution
nice -n 19 php refresh-kb.php --mode=full

# Limit memory usage
php -d memory_limit=256M refresh-kb.php --mode=quick

# Spread execution times
# Instead of: 0 2 * * * (all at 2 AM)
# Use: 0,15,30,45 2 * * * (spread over hour)
```

---

## 📝 Cron Job Best Practices

### Scheduling Best Practices

1. **Avoid Peak Hours:** Schedule heavy operations during off-peak times
2. **Spread Execution:** Don't run all jobs at the same time
3. **Use Appropriate Frequency:** Match frequency to actual needs
4. **Monitor Resource Usage:** Ensure jobs don't overload the system

### Script Best Practices

```bash
# Always use absolute paths
cd /full/path/to/directory && php script.php

# Redirect output to logs
>> /path/to/log.log 2>&1

# Use silent mode for cron
php script.php --silent

# Include timeout protection
timeout 300 php script.php --mode=full
```

### Maintenance Best Practices

1. **Regular Log Review:** Check logs weekly for issues
2. **Performance Monitoring:** Monitor execution times and resource usage
3. **Backup Cron Configuration:** Regular crontab backups
4. **Test Changes:** Test cron job changes in development first

---

*This cron configuration guide is part of the CIS Knowledge Base system and is automatically updated.*

**Last Updated:** October 12, 2025  
**Next Review:** Monthly  
**Contact:** admin@yourcompany.com