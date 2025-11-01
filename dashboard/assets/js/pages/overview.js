/**
 * Overview Page JavaScript
 * Handles dashboard overview charts and interactions
 */

$(document).ready(function() {
    
    // Get chart data from page
    const chartData = JSON.parse($('#chartData').text());
    
    // ============================================================================
    // FILES BY TYPE CHART
    // ============================================================================
    
    const filesTypeCtx = document.getElementById('filesTypeChart');
    if (filesTypeCtx) {
        const labels = chartData.filesByType.map(item => item.intelligence_type);
        const data = chartData.filesByType.map(item => parseInt(item.count));
        const colors = [
            '#667eea',
            '#f093fb',
            '#4facfe',
            '#43e97b',
            '#fa709a',
            '#feca57',
            '#ff6348',
            '#5f27cd'
        ];
        
        new Chart(filesTypeCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Files',
                    data: data,
                    backgroundColor: colors,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    
    // ============================================================================
    // SERVER DISTRIBUTION CHART
    // ============================================================================
    
    const serverChartCtx = document.getElementById('serverChart');
    if (serverChartCtx) {
        // Get server counts
        const serverCounts = {};
        chartData.servers.forEach(server => {
            serverCounts[server] = (serverCounts[server] || 0) + 1;
        });
        
        const labels = Object.keys(serverCounts);
        const data = Object.values(serverCounts);
        const colors = ['#667eea', '#f093fb', '#4facfe', '#43e97b'];
        
        new Chart(serverChartCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // ============================================================================
    // LOAD RECENT FILES
    // ============================================================================
    
    function loadRecentFiles() {
        $.ajax({
            url: 'api/recent.php',
            method: 'GET',
            data: { limit: 10 },
            success: function(response) {
                if (response.success) {
                    displayRecentFiles(response.data);
                }
            }
        });
    }
    
    function displayRecentFiles(files) {
        const tbody = $('#recentFiles');
        tbody.empty();
        
        if (files.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">No files found</td></tr>');
            return;
        }
        
        files.forEach(file => {
            const row = $('<tr>');
            row.append(`<td><i class="fas fa-file-code me-2"></i> ${escapeHtml(file.file_name)}</td>`);
            row.append(`<td>${getTypeBadge(file.intelligence_type)}</td>`);
            row.append(`<td>${getServerBadge(file.server_id)}</td>`);
            row.append(`<td>${timeAgo(file.extracted_at)}</td>`);
            row.css('cursor', 'pointer');
            row.on('click', () => viewFile(file.file_path));
            tbody.append(row);
        });
    }
    
    function getServerBadge(serverId) {
        const servers = {
            'jcepnzzkmj': { name: 'CIS', color: '#667eea' },
            'dvaxgvsxmz': { name: 'Retail', color: '#f093fb' },
            'fhrehrpjmu': { name: 'Wholesale', color: '#4facfe' },
            'hdgwrzntwa': { name: 'Intelligence', color: '#43e97b' }
        };
        
        const server = servers[serverId];
        if (!server) return `<span class="badge bg-secondary">${serverId}</span>`;
        
        return `<span class="badge" style="background: ${server.color}">
                    <i class="fas fa-server me-1"></i> ${server.name}
                </span>`;
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    function viewFile(path) {
        window.location.href = '?page=files&view=' + encodeURIComponent(path);
    }
    
    // Load recent files on page load
    loadRecentFiles();
    
    // ============================================================================
    // TRIGGER FULL SCAN BUTTON
    // ============================================================================
    
    $('#triggerFullScan').on('click', function() {
        if (!confirm('This will scan all servers and may take 10-15 minutes. Continue?')) {
            return;
        }
        
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<i class="fas fa-spinner fa-spin me-2"></i> Scanning...').prop('disabled', true);
        
        $.ajax({
            url: 'api/scan.php',
            method: 'POST',
            data: JSON.stringify({
                scan_type: 'full',
                all_servers: true
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    showNotification('Full system scan started successfully!', 'success');
                    // Optionally redirect to scanner page
                    setTimeout(() => {
                        window.location.href = '?page=scanner';
                    }, 2000);
                } else {
                    showNotification('Failed to start scan: ' + response.error, 'error');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            },
            error: function() {
                showNotification('Error starting scan', 'error');
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
    
});
