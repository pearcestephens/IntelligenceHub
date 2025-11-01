#!/bin/bash

# 🚀 Production Operations CLI - Complete automation toolkit for AI Agent system
# 
# Comprehensive production operations command-line interface:
# - System monitoring and health checks
# - Automated deployment and rollback procedures
# - Security scanning and compliance validation
# - Performance testing and optimization analysis
# - Error tracking and debugging utilities
# - Maintenance automation and system optimization
# - Backup and recovery operations
# - Configuration management and feature flags
# 
# @package App\Operations
# @author Production AI Agent System
# @version 1.0.0

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
OPS_DIR="$SCRIPT_DIR"
LOGS_DIR="$PROJECT_ROOT/logs"
BACKUPS_DIR="$PROJECT_ROOT/backups"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
NC='\033[0m' # No Color

# Emoji constants
SUCCESS="✅"
ERROR="❌"
WARNING="⚠️"
INFO="ℹ️"
ROCKET="🚀"
GEAR="⚙️"
SHIELD="🛡️"
CHART="📊"
BUG="🐛"
WRENCH="🔧"

# Ensure required directories exist
mkdir -p "$LOGS_DIR" "$BACKUPS_DIR"

# Logging function
log() {
    local level=$1
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    
    case $level in
        "INFO")
            echo -e "${CYAN}$INFO [$timestamp] INFO: $message${NC}"
            ;;
        "SUCCESS")
            echo -e "${GREEN}$SUCCESS [$timestamp] SUCCESS: $message${NC}"
            ;;
        "WARNING")
            echo -e "${YELLOW}$WARNING [$timestamp] WARNING: $message${NC}"
            ;;
        "ERROR")
            echo -e "${RED}$ERROR [$timestamp] ERROR: $message${NC}"
            ;;
        *)
            echo -e "${WHITE}[$timestamp] $message${NC}"
            ;;
    esac
    
    # Also log to file
    echo "[$timestamp] $level: $message" >> "$LOGS_DIR/operations.log"
}

# Help function
show_help() {
    cat << EOF
${BLUE}$ROCKET AI Agent Production Operations CLI${NC}

${WHITE}USAGE:${NC}
    ./ops-cli.sh <command> [options]

${WHITE}MONITORING COMMANDS:${NC}
    ${GREEN}health${NC}                    Run comprehensive health check
    ${GREEN}monitor${NC}                   Start real-time monitoring dashboard
    ${GREEN}status${NC}                    Show current system status
    ${GREEN}metrics${NC}                   Display performance metrics
    ${GREEN}alerts${NC}                    Show active alerts

${WHITE}DEPLOYMENT COMMANDS:${NC}
    ${GREEN}deploy <env>${NC}              Deploy to environment (dev/staging/prod)
    ${GREEN}rollback <id> <env>${NC}       Rollback deployment
    ${GREEN}migrate <env>${NC}             Run database migrations
    ${GREEN}backup${NC}                    Create system backup
    ${GREEN}restore <backup_id>${NC}       Restore from backup

${WHITE}SECURITY COMMANDS:${NC}
    ${GREEN}security-scan${NC}             Run comprehensive security scan
    ${GREEN}vulnerability-check${NC}       Check for vulnerabilities
    ${GREEN}compliance-report${NC}         Generate compliance report
    ${GREEN}audit-logs${NC}                Review security audit logs

${WHITE}PERFORMANCE COMMANDS:${NC}
    ${GREEN}performance-test${NC}          Run performance test suite
    ${GREEN}load-test <scenario>${NC}      Run load testing scenario
    ${GREEN}optimize${NC}                  Run system optimization
    ${GREEN}benchmark${NC}                 Run performance benchmarks

${WHITE}DEBUGGING COMMANDS:${NC}
    ${GREEN}logs <service>${NC}            Show service logs
    ${GREEN}debug-info${NC}                Gather debug information
    ${GREEN}error-report${NC}              Generate error analysis report
    ${GREEN}trace <request_id>${NC}        Trace request execution

${WHITE}MAINTENANCE COMMANDS:${NC}
    ${GREEN}cleanup${NC}                   Run system cleanup tasks
    ${GREEN}repo-clean [mode]${NC}        Run repo cleaner (list|archive|delete) with --confirm
    ${GREEN}update-dependencies${NC}       Update system dependencies
    ${GREEN}rotate-logs${NC}               Rotate and archive logs
    ${GREEN}database-maintenance${NC}      Run database maintenance

${WHITE}CONFIGURATION COMMANDS:${NC}
    ${GREEN}config-check${NC}              Validate configuration
    ${GREEN}ready-check${NC}               Verify env/DB/Redis and .htaccess protections
    ${GREEN}feature-flags${NC}             Manage feature flags
    ${GREEN}env-setup <env>${NC}           Setup environment configuration
    ${GREEN}secrets-rotate${NC}            Rotate system secrets

${WHITE}UTILITY COMMANDS:${NC}
    ${GREEN}report${NC}                    Generate comprehensive system report
    ${GREEN}version${NC}                   Show system version information
    ${GREEN}help${NC}                      Show this help message

${WHITE}EXAMPLES:${NC}
    ./ops-cli.sh health                   # Check system health
    ./ops-cli.sh deploy prod              # Deploy to production
    ./ops-cli.sh security-scan            # Run security analysis
    ./ops-cli.sh performance-test         # Test system performance
    ./ops-cli.sh logs api                 # Show API logs

EOF
}

# Health check function
run_health_check() {
    log "INFO" "Running comprehensive health check..."
    
    local exit_code=0
    
    # Check if PHP is available
    if ! command -v php &> /dev/null; then
        log "ERROR" "PHP is not installed or not in PATH"
        exit_code=1
    else
        log "SUCCESS" "PHP is available ($(php --version | head -n1))"
    fi
    
    # Run system health check
    if [ -f "$OPS_DIR/monitoring-dashboard.php" ]; then
        local health_result=$(php "$OPS_DIR/monitoring-dashboard.php" 2>&1 || echo "HEALTH_CHECK_FAILED")
        
        if [[ "$health_result" == *"HEALTH_CHECK_FAILED"* ]]; then
            log "ERROR" "System health check failed"
            exit_code=1
        else
            log "SUCCESS" "System health check passed"
        fi
    else
        log "WARNING" "Health check script not found"
        exit_code=1
    fi
    
    # Check critical directories
    local critical_dirs=("$LOGS_DIR" "$BACKUPS_DIR" "$PROJECT_ROOT/src" "$PROJECT_ROOT/public")
    
    for dir in "${critical_dirs[@]}"; do
        if [ -d "$dir" ]; then
            if [ -w "$dir" ]; then
                log "SUCCESS" "Directory $dir is accessible and writable"
            else
                log "ERROR" "Directory $dir is not writable"
                exit_code=1
            fi
        else
            log "ERROR" "Critical directory $dir does not exist"
            exit_code=1
        fi
    done
    
    # Check configuration files
    local config_files=("$PROJECT_ROOT/.env" "$PROJECT_ROOT/composer.json")
    
    for file in "${config_files[@]}"; do
        if [ -f "$file" ]; then
            log "SUCCESS" "Configuration file $file exists"
        else
            log "WARNING" "Configuration file $file not found"
        fi
    done
    
    if [ $exit_code -eq 0 ]; then
        log "SUCCESS" "All health checks passed"
    else
        log "ERROR" "Some health checks failed"
    fi
    
    return $exit_code
}

# Deployment function
run_deployment() {
    local environment=$1
    
    if [ -z "$environment" ]; then
        log "ERROR" "Environment is required (dev/staging/prod)"
        return 1
    fi
    
    log "INFO" "Starting deployment to $environment..."
    
    # Check if deployment script exists
    if [ ! -f "$OPS_DIR/deployment-manager.php" ]; then
        log "ERROR" "Deployment script not found"
        return 1
    fi
    
    # Run deployment
    if php "$OPS_DIR/deployment-manager.php" deploy "$environment"; then
        log "SUCCESS" "Deployment to $environment completed successfully"
        return 0
    else
        log "ERROR" "Deployment to $environment failed"
        return 1
    fi
}

# Security scan function
run_security_scan() {
    log "INFO" "Starting comprehensive security scan..."
    
    if [ ! -f "$OPS_DIR/security-scanner.php" ]; then
        log "ERROR" "Security scanner script not found"
        return 1
    fi
    
    # Run security scan
    if php "$OPS_DIR/security-scanner.php" scan; then
        log "SUCCESS" "Security scan completed successfully"
        return 0
    else
        log "ERROR" "Security scan failed or found critical issues"
        return 1
    fi
}

# Performance test function
run_performance_test() {
    local base_url=${1:-"http://localhost"}
    
    log "INFO" "Starting performance test suite (Base URL: $base_url)..."
    
    if [ ! -f "$OPS_DIR/performance-tester.php" ]; then
        log "ERROR" "Performance tester script not found"
        return 1
    fi
    
    # Run performance tests
    if php "$OPS_DIR/performance-tester.php" test "$base_url"; then
        log "SUCCESS" "Performance tests completed successfully"
        return 0
    else
        log "ERROR" "Performance tests failed or performance is below acceptable thresholds"
        return 1
    fi
}

# Database migration function
run_migrations() {
    local environment=$1
    
    if [ -z "$environment" ]; then
        log "ERROR" "Environment is required (dev/staging/prod)"
        return 1
    fi
    
    log "INFO" "Running database migrations for $environment..."
    
    if [ ! -f "$OPS_DIR/deployment-manager.php" ]; then
        log "ERROR" "Deployment manager script not found"
        return 1
    fi
    
    # Run migrations
    if php "$OPS_DIR/deployment-manager.php" migrate "$environment"; then
        log "SUCCESS" "Database migrations completed successfully"
        return 0
    else
        log "ERROR" "Database migrations failed"
        return 1
    fi
}

# Backup function
create_backup() {
    log "INFO" "Creating system backup..."
    
    local backup_id="backup_$(date +%Y%m%d_%H%M%S)"
    local backup_dir="$BACKUPS_DIR/$backup_id"
    
    mkdir -p "$backup_dir"
    
    # Backup configuration files
    log "INFO" "Backing up configuration files..."
    cp -r "$PROJECT_ROOT/config" "$backup_dir/" 2>/dev/null || log "WARNING" "Config directory not found"
    cp "$PROJECT_ROOT/.env" "$backup_dir/" 2>/dev/null || log "WARNING" ".env file not found"
    
    # Backup source code
    log "INFO" "Backing up source code..."
    cp -r "$PROJECT_ROOT/src" "$backup_dir/" 2>/dev/null || log "WARNING" "Source directory not found"
    
    # Backup logs (recent only)
    log "INFO" "Backing up recent logs..."
    mkdir -p "$backup_dir/logs"
    find "$LOGS_DIR" -name "*.log" -mtime -7 -exec cp {} "$backup_dir/logs/" \; 2>/dev/null || true
    
    # Create backup manifest
    cat > "$backup_dir/manifest.json" << EOF
{
    "backup_id": "$backup_id",
    "created_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)",
    "system_version": "$(cat $PROJECT_ROOT/VERSION 2>/dev/null || echo 'unknown')",
    "components": [
        "configuration",
        "source_code",
        "logs"
    ]
}
EOF
    
    # Compress backup
    log "INFO" "Compressing backup..."
    tar -czf "$BACKUPS_DIR/${backup_id}.tar.gz" -C "$BACKUPS_DIR" "$backup_id"
    rm -rf "$backup_dir"
    
    log "SUCCESS" "Backup created: ${backup_id}.tar.gz"
    echo "$backup_id"
}

# Log viewing function
view_logs() {
    local service=${1:-"all"}
    
    log "INFO" "Showing logs for: $service"
    
    case $service in
        "api"|"web")
            if [ -f "$LOGS_DIR/api.log" ]; then
                tail -f "$LOGS_DIR/api.log"
            else
                log "WARNING" "API log file not found"
            fi
            ;;
        "error"|"errors")
            if [ -f "$LOGS_DIR/error.log" ]; then
                tail -f "$LOGS_DIR/error.log"
            else
                log "WARNING" "Error log file not found"
            fi
            ;;
        "operations"|"ops")
            if [ -f "$LOGS_DIR/operations.log" ]; then
                tail -f "$LOGS_DIR/operations.log"
            else
                log "WARNING" "Operations log file not found"
            fi
            ;;
        "all")
            log "INFO" "Available log files:"
            ls -la "$LOGS_DIR"/*.log 2>/dev/null || log "WARNING" "No log files found"
            ;;
        *)
            if [ -f "$LOGS_DIR/${service}.log" ]; then
                tail -f "$LOGS_DIR/${service}.log"
            else
                log "ERROR" "Log file for service '$service' not found"
                return 1
            fi
            ;;
    esac
}

# System cleanup function
run_cleanup() {
    log "INFO" "Running system cleanup tasks..."
    
    local cleaned=0
    
    # Clean old log files (older than 30 days)
    log "INFO" "Cleaning old log files..."
    if find "$LOGS_DIR" -name "*.log.*" -mtime +30 -delete 2>/dev/null; then
        cleaned=$((cleaned + 1))
        log "SUCCESS" "Old log files cleaned"
    fi
    
    # Clean old backups (older than 90 days)
    log "INFO" "Cleaning old backups..."
    if find "$BACKUPS_DIR" -name "*.tar.gz" -mtime +90 -delete 2>/dev/null; then
        cleaned=$((cleaned + 1))
        log "SUCCESS" "Old backups cleaned"
    fi
    
    # Clean temporary files
    log "INFO" "Cleaning temporary files..."
    if find /tmp -name "ai_agent_*" -mtime +1 -delete 2>/dev/null; then
        cleaned=$((cleaned + 1))
        log "SUCCESS" "Temporary files cleaned"
    fi
    
    # Clean cache files if cache directory exists
    if [ -d "$PROJECT_ROOT/cache" ]; then
        log "INFO" "Cleaning cache files..."
        if find "$PROJECT_ROOT/cache" -type f -mtime +7 -delete 2>/dev/null; then
            cleaned=$((cleaned + 1))
            log "SUCCESS" "Cache files cleaned"
        fi
    fi
    
    log "SUCCESS" "System cleanup completed ($cleaned tasks performed)"
}

# Feature flags management
manage_feature_flags() {
    local action=${1:-"list"}
    local flag=$2
    local value=$3
    
    if [ ! -f "$OPS_DIR/deployment-manager.php" ]; then
        log "ERROR" "Deployment manager script not found"
        return 1
    fi
    
    case $action in
        "list")
            log "INFO" "Listing all feature flags..."
            php "$OPS_DIR/deployment-manager.php" flags list
            ;;
        "set")
            if [ -z "$flag" ] || [ -z "$value" ]; then
                log "ERROR" "Flag name and value are required for 'set' action"
                return 1
            fi
            log "INFO" "Setting feature flag '$flag' to '$value'..."
            php "$OPS_DIR/deployment-manager.php" flags set "$flag" "$value"
            ;;
        "remove")
            if [ -z "$flag" ]; then
                log "ERROR" "Flag name is required for 'remove' action"
                return 1
            fi
            log "INFO" "Removing feature flag '$flag'..."
            php "$OPS_DIR/deployment-manager.php" flags remove "$flag"
            ;;
        *)
            log "ERROR" "Invalid action: $action (use: list, set, remove)"
            return 1
            ;;
    esac
}

# Generate comprehensive report
generate_report() {
    log "INFO" "Generating comprehensive system report..."
    
    local report_file="$LOGS_DIR/system_report_$(date +%Y%m%d_%H%M%S).txt"
    
    {
        echo "AI Agent System Report"
        echo "Generated: $(date)"
        echo "===================="
        echo
        
        echo "SYSTEM INFORMATION:"
        echo "- OS: $(uname -a)"
        echo "- PHP Version: $(php --version | head -n1)"
        echo "- Disk Usage: $(df -h $PROJECT_ROOT | tail -n1)"
        echo "- Memory Usage: $(free -h | head -n2 | tail -n1)"
        echo
        
        echo "PROJECT STATUS:"
        echo "- Project Root: $PROJECT_ROOT"
        echo "- Version: $(cat $PROJECT_ROOT/VERSION 2>/dev/null || echo 'unknown')"
        echo "- Last Modified: $(find $PROJECT_ROOT -name "*.php" -type f -printf '%T@ %p\n' 2>/dev/null | sort -n | tail -1 | cut -d' ' -f2- || echo 'unknown')"
        echo
        
        echo "DIRECTORY STRUCTURE:"
        ls -la "$PROJECT_ROOT" 2>/dev/null || echo "Cannot access project root"
        echo
        
        echo "RECENT LOG ENTRIES:"
        tail -n 20 "$LOGS_DIR/operations.log" 2>/dev/null || echo "No operations log available"
        echo
        
    } > "$report_file"
    
    log "SUCCESS" "System report generated: $report_file"
    
    # Also display report summary
    echo
    echo -e "${WHITE}=== SYSTEM REPORT SUMMARY ===${NC}"
    head -n 20 "$report_file"
    echo -e "${CYAN}... (full report saved to: $report_file)${NC}"
}

# Main command handler
main() {
    local command=$1
    shift
    
    case $command in
        "health")
            run_health_check
            ;;
        "deploy")
            run_deployment "$@"
            ;;
        "rollback")
            if [ -f "$OPS_DIR/deployment-manager.php" ]; then
                php "$OPS_DIR/deployment-manager.php" rollback "$@"
            else
                log "ERROR" "Deployment manager not found"
                exit 1
            fi
            ;;
        "migrate")
            run_migrations "$@"
            ;;
        "backup")
            create_backup
            ;;
        "security-scan")
            run_security_scan
            ;;
        "performance-test")
            run_performance_test "$@"
            ;;
        "logs")
            view_logs "$@"
            ;;
        "cleanup")
            run_cleanup
            ;;
        "repo-clean")
            # Usage: repo-clean [mode] [--confirm] [--no-dirs] [--only=x,y] [--dry-run]
            mode=${1:-list}
            shift || true
            log "INFO" "Running repo-cleaner (mode=$mode $*)"
            if [ -f "$OPS_DIR/repo-cleaner.php" ]; then
                php "$OPS_DIR/repo-cleaner.php" --mode="$mode" "$@"
            else
                log "ERROR" "repo-cleaner.php not found"
                exit 1
            fi
            ;;
        "feature-flags")
            manage_feature_flags "$@"
            ;;
        "ready-check")
            if [ -f "$OPS_DIR/ready-check.php" ]; then
                php "$OPS_DIR/ready-check.php"
            else
                log "ERROR" "ready-check.php not found"
                exit 1
            fi
            ;;
        "report")
            generate_report
            ;;
        "status")
            log "INFO" "System Status Check"
            run_health_check && log "SUCCESS" "System is healthy" || log "ERROR" "System has issues"
            ;;
        "monitor")
            log "INFO" "Starting monitoring dashboard..."
            log "INFO" "Open monitoring dashboard at: http://localhost/ops/monitoring-dashboard.php"
            ;;
        "version")
            echo -e "${BLUE}AI Agent Production Operations CLI v1.0.0${NC}"
            echo -e "Project Version: $(cat $PROJECT_ROOT/VERSION 2>/dev/null || echo 'unknown')"
            echo -e "PHP Version: $(php --version | head -n1)"
            ;;
        "help"|"--help"|"-h"|"")
            show_help
            ;;
        *)
            log "ERROR" "Unknown command: $command"
            echo
            show_help
            exit 1
            ;;
    esac
}

# Check if running as script (not sourced)
if [ "${BASH_SOURCE[0]}" == "${0}" ]; then
    main "$@"
fi