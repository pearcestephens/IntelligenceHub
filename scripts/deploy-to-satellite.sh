#!/bin/bash

# =============================================================================
# Satellite Bot Deployment System
# =============================================================================
# Deploys complete bot intelligence infrastructure to any satellite server
# Usage: ./deploy-to-satellite.sh [satellite_name] [component]
# =============================================================================

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
INTELLIGENCE_HUB_URL="https://gpt.ecigdis.co.nz"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Satellite configurations
declare -A SATELLITES
SATELLITES[cis]="https://staff.vapeshed.co.nz"
SATELLITES[retail]="https://vapeshed.co.nz"
SATELLITES[wholesale]="https://wholesale.ecigdis.co.nz"

declare -A SATELLITE_IDS
SATELLITE_IDS[cis]=2
SATELLITE_IDS[retail]=3
SATELLITE_IDS[wholesale]=4

# Helper functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Show usage
show_usage() {
    cat << EOF
Usage: $0 [satellite] [component]

Satellites:
  cis        - CIS Staff System (Unit 2)
  retail     - VapeShed Retail (Unit 3)
  wholesale  - Wholesale System (Unit 4)
  all        - Deploy to all satellites

Components:
  mcp        - MCP server and tools
  kb         - Knowledge base files
  scanner    - File scanner system
  bots       - Bot management dashboard
  all        - All components (full deployment)

Examples:
  $0 cis all              # Full deployment to CIS
  $0 retail mcp           # Deploy only MCP to retail
  $0 all kb               # Deploy KB to all satellites
  $0 cis mcp,kb,scanner   # Deploy specific components

EOF
    exit 1
}

# Validate satellite name
validate_satellite() {
    local sat=$1
    if [[ ! " ${!SATELLITES[@]} " =~ " ${sat} " ]] && [[ "$sat" != "all" ]]; then
        log_error "Unknown satellite: $sat"
        show_usage
    fi
}

# Test connection to satellite
test_connection() {
    local name=$1
    local url=$2

    log_info "Testing connection to $name..."

    if curl -s -f -m 5 "$url" > /dev/null; then
        log_success "Connection to $name successful"
        return 0
    else
        log_error "Cannot connect to $name at $url"
        return 1
    fi
}

# Deploy MCP server
deploy_mcp() {
    local name=$1
    local url=$2
    local unit_id=$3

    log_info "Deploying MCP server to $name..."

    # Create deployment package
    local temp_dir=$(mktemp -d)
    mkdir -p "$temp_dir/mcp"

    # Copy MCP files
    cp -r /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/* "$temp_dir/mcp/"

    # Create satellite-specific config
    cat > "$temp_dir/mcp/satellite-config.php" << EOPHP
<?php
/**
 * Satellite MCP Configuration
 * Generated: $(date)
 * Satellite: $name (Unit $unit_id)
 */

return [
    'satellite_name' => '$name',
    'unit_id' => $unit_id,
    'hub_url' => '$INTELLIGENCE_HUB_URL',
    'sync_enabled' => true,
    'auto_sync_interval' => 3600, // 1 hour
    'mcp_version' => '3.0.0',
    'intelligence_level' => '$(get_intelligence_level $name)',
];
EOPHP

    # Deploy via API
    log_info "Uploading MCP files to $name..."

    local response=$(curl -s -X POST "$url/api/receive_deployment.php" \
        -F "component=mcp" \
        -F "files=@$temp_dir/mcp.tar.gz" \
        -F "auth=bFUdRjh4Jx")

    if echo "$response" | jq -e '.success' > /dev/null 2>&1; then
        log_success "MCP deployed to $name successfully"
    else
        log_error "MCP deployment to $name failed"
        echo "$response" | jq '.'
    fi

    # Cleanup
    rm -rf "$temp_dir"
}

# Deploy knowledge base
deploy_kb() {
    local name=$1
    local url=$2
    local unit_id=$3

    log_info "Deploying knowledge base to $name..."

    # Create KB sync manifest
    local manifest=$(cat << EOF
{
    "target": "$name",
    "unit_id": $unit_id,
    "sync_type": "full",
    "timestamp": "$(date -Iseconds)",
    "components": [
        "system_architecture",
        "database_schemas",
        "api_documentation",
        "bot_instructions",
        "operational_guides"
    ]
}
EOF
)

    # Trigger KB sync via Hub API
    local response=$(curl -s -X POST "$INTELLIGENCE_HUB_URL/api/sync_kb_to_satellite.php" \
        -H "Content-Type: application/json" \
        -d "$manifest")

    if echo "$response" | jq -e '.success' > /dev/null 2>&1; then
        local files_synced=$(echo "$response" | jq -r '.files_synced')
        log_success "Knowledge base deployed to $name ($files_synced files)"
    else
        log_error "KB deployment to $name failed"
        echo "$response" | jq '.'
    fi
}

# Deploy scanner
deploy_scanner() {
    local name=$1
    local url=$2
    local unit_id=$3

    log_info "Deploying scanner to $name..."

    # Deploy scanner via satellite-deploy API
    local payload=$(cat << EOF
{
    "target_satellite": "$name",
    "action": "deploy_scanner",
    "unit_id": $unit_id,
    "scanner_config": {
        "auto_scan": true,
        "scan_interval": 14400,
        "report_to_hub": true,
        "incremental_only": true
    }
}
EOF
)

    local response=$(curl -s -X POST "$INTELLIGENCE_HUB_URL/api/satellite-deploy.php" \
        -H "Content-Type: application/json" \
        -d "$payload")

    if echo "$response" | jq -e '.success' > /dev/null 2>&1; then
        log_success "Scanner deployed to $name"
    else
        log_error "Scanner deployment to $name failed"
        echo "$response" | jq '.'
    fi
}

# Deploy bot management dashboard
deploy_bots() {
    local name=$1
    local url=$2
    local unit_id=$3

    log_info "Deploying bot management to $name..."

    # Create bot management files
    local temp_dir=$(mktemp -d)
    mkdir -p "$temp_dir/admin/bots"

    # Copy bot management UI
    cat > "$temp_dir/admin/bots/index.php" << 'EOPHP'
<?php
/**
 * Bot Management Dashboard
 * Auto-deployed from Intelligence Hub
 */

require_once __DIR__ . '/../../app.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

$title = 'Bot Management';
require_once 'header.php';
?>

<div class="container-fluid mt-4">
    <h1><i class="fas fa-robot"></i> Bot Management Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Active Bots</h5>
                    <h2 id="active-bots-count">...</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Total Conversations</h5>
                    <h2 id="total-conversations">...</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>MCP Queries Today</h5>
                    <h2 id="mcp-queries-today">...</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Avg Response Time</h5>
                    <h2 id="avg-response-time">...</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Bot Instances</h5>
        </div>
        <div class="card-body">
            <table class="table" id="bots-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Last Active</th>
                        <th>Conversations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Load bot data via MCP
async function loadBotData() {
    const response = await fetch('/api/bots/list.php');
    const data = await response.json();

    // Update stats
    document.getElementById('active-bots-count').textContent = data.active_count;
    document.getElementById('total-conversations').textContent = data.total_conversations;
    document.getElementById('mcp-queries-today').textContent = data.mcp_queries_today;
    document.getElementById('avg-response-time').textContent = data.avg_response_time + 'ms';

    // Populate table
    const tbody = document.querySelector('#bots-table tbody');
    tbody.innerHTML = '';

    data.bots.forEach(bot => {
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${bot.name}</td>
            <td><span class="badge badge-primary">${bot.type}</span></td>
            <td><span class="badge badge-${bot.status === 'active' ? 'success' : 'secondary'}">${bot.status}</span></td>
            <td>${bot.last_active}</td>
            <td>${bot.conversation_count}</td>
            <td>
                <button class="btn btn-sm btn-info" onclick="viewBot(${bot.id})">View</button>
                <button class="btn btn-sm btn-warning" onclick="configureBot(${bot.id})">Configure</button>
            </td>
        `;
    });
}

loadBotData();
setInterval(loadBotData, 30000); // Refresh every 30 seconds
</script>

<?php require_once 'footer.php'; ?>
EOPHP

    # Deploy via API
    local response=$(curl -s -X POST "$url/api/receive_deployment.php" \
        -F "component=bots" \
        -F "files=@$temp_dir/bots.tar.gz" \
        -F "auth=bFUdRjh4Jx")

    if echo "$response" | jq -e '.success' > /dev/null 2>&1; then
        log_success "Bot management deployed to $name"
        log_info "Access at: $url/admin/bots/"
    else
        log_error "Bot management deployment to $name failed"
    fi

    rm -rf "$temp_dir"
}

# Get intelligence level for satellite
get_intelligence_level() {
    case $1 in
        cis) echo "NEURAL" ;;
        retail) echo "ADVANCED" ;;
        wholesale) echo "ADVANCED" ;;
        *) echo "BASIC" ;;
    esac
}

# Deploy all components
deploy_all() {
    local name=$1
    local url=$2
    local unit_id=$3

    log_info "=========================================="
    log_info "FULL DEPLOYMENT TO: $name"
    log_info "URL: $url"
    log_info "Unit ID: $unit_id"
    log_info "=========================================="

    deploy_mcp "$name" "$url" "$unit_id"
    deploy_kb "$name" "$url" "$unit_id"
    deploy_scanner "$name" "$url" "$unit_id"
    deploy_bots "$name" "$url" "$unit_id"

    log_success "=========================================="
    log_success "FULL DEPLOYMENT COMPLETE: $name"
    log_success "=========================================="
}

# Main execution
main() {
    if [[ $# -lt 2 ]]; then
        show_usage
    fi

    local satellite=$1
    local component=$2

    log_info "Starting satellite deployment..."
    log_info "Target: $satellite"
    log_info "Component: $component"

    # Deploy to all satellites or specific one
    if [[ "$satellite" == "all" ]]; then
        for sat in "${!SATELLITES[@]}"; do
            test_connection "$sat" "${SATELLITES[$sat]}" || continue

            case "$component" in
                mcp) deploy_mcp "$sat" "${SATELLITES[$sat]}" "${SATELLITE_IDS[$sat]}" ;;
                kb) deploy_kb "$sat" "${SATELLITES[$sat]}" "${SATELLITE_IDS[$sat]}" ;;
                scanner) deploy_scanner "$sat" "${SATELLITES[$sat]}" "${SATELLITE_IDS[$sat]}" ;;
                bots) deploy_bots "$sat" "${SATELLITES[$sat]}" "${SATELLITE_IDS[$sat]}" ;;
                all) deploy_all "$sat" "${SATELLITES[$sat]}" "${SATELLITE_IDS[$sat]}" ;;
                *) log_error "Unknown component: $component" ;;
            esac
        done
    else
        validate_satellite "$satellite"
        test_connection "$satellite" "${SATELLITES[$satellite]}" || exit 1

        local url="${SATELLITES[$satellite]}"
        local unit_id="${SATELLITE_IDS[$satellite]}"

        # Handle comma-separated components
        IFS=',' read -ra COMPONENTS <<< "$component"
        for comp in "${COMPONENTS[@]}"; do
            case "$comp" in
                mcp) deploy_mcp "$satellite" "$url" "$unit_id" ;;
                kb) deploy_kb "$satellite" "$url" "$unit_id" ;;
                scanner) deploy_scanner "$satellite" "$url" "$unit_id" ;;
                bots) deploy_bots "$satellite" "$url" "$unit_id" ;;
                all) deploy_all "$satellite" "$url" "$unit_id" ;;
                *) log_error "Unknown component: $comp" ;;
            esac
        done
    fi

    log_success "Deployment process completed!"
}

# Run main function
main "$@"
