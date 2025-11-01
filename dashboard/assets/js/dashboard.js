/**
 * Dashboard Main JavaScript
 * Handles global functionality and interactions
 */

$(document).ready(function() {
    
    // ============================================================================
    // SIDEBAR TOGGLE
    // ============================================================================
    
    $('#menuToggle').on('click', function() {
        $('#sidebar').toggleClass('collapsed');
        localStorage.setItem('sidebarCollapsed', $('#sidebar').hasClass('collapsed'));
    });
    
    // Restore sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $('#sidebar').addClass('collapsed');
    }
    
    // ============================================================================
    // GLOBAL SEARCH (Header)
    // ============================================================================
    
    let searchTimeout;
    $('#globalSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length < 3) {
            $('#searchResults').hide();
            return;
        }
        
        searchTimeout = setTimeout(() => {
            performGlobalSearch(query);
        }, 300);
    });
    
    // Keyboard shortcut Ctrl+K for search
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            $('#globalSearch').focus();
        }
    });
    
    // ============================================================================
    // REFRESH STATS
    // ============================================================================
    
    $('#refreshStats').on('click', function() {
        const $icon = $(this).find('i');
        $icon.addClass('fa-spin');
        
        $.ajax({
            url: 'api/stats.php',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showNotification('Statistics refreshed successfully', 'success');
                    location.reload();
                } else {
                    showNotification('Failed to refresh statistics', 'error');
                }
            },
            error: function() {
                showNotification('Error refreshing statistics', 'error');
            },
            complete: function() {
                $icon.removeClass('fa-spin');
            }
        });
    });
    
    // ============================================================================
    // TRIGGER SCAN
    // ============================================================================
    
    $('#triggerScan').on('click', function() {
        if (!confirm('This will trigger a full system scan. This may take several minutes. Continue?')) {
            return;
        }
        
        const $icon = $(this).find('i');
        $icon.addClass('fa-spin');
        $(this).prop('disabled', true);
        
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
                    showNotification('System scan started successfully', 'success');
                } else {
                    showNotification('Failed to start scan: ' + response.error, 'error');
                }
            },
            error: function() {
                showNotification('Error starting scan', 'error');
            },
            complete: function() {
                $icon.removeClass('fa-spin');
                $('#triggerScan').prop('disabled', false);
            }
        });
    });
    
    // ============================================================================
    // NOTIFICATIONS
    // ============================================================================
    
    // Load notification count
    function loadNotificationCount() {
        $.ajax({
            url: 'api/notifications.php',
            method: 'GET',
            success: function(response) {
                if (response.success && response.data.count > 0) {
                    $('#notificationCount').text(response.data.count).show();
                } else {
                    $('#notificationCount').hide();
                }
            }
        });
    }
    
    loadNotificationCount();
    setInterval(loadNotificationCount, 60000); // Check every minute
    
    // ============================================================================
    // HELP MENU
    // ============================================================================
    
    $('#helpMenu').on('click', function() {
        showHelpModal();
    });
    
    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================
    
    /**
     * Show notification
     */
    window.showNotification = function(message, type = 'info') {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        $('.toast-container').append(toast);
        new bootstrap.Toast(toast[0]).show();
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    };
    
    /**
     * Format bytes
     */
    window.formatBytes = function(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };
    
    /**
     * Time ago
     */
    window.timeAgo = function(dateString) {
        const date = new Date(dateString);
        const seconds = Math.floor((new Date() - date) / 1000);
        
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + " years ago";
        
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + " months ago";
        
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + " days ago";
        
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + " hours ago";
        
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + " minutes ago";
        
        return Math.floor(seconds) + " seconds ago";
    };
    
    /**
     * Get type badge
     */
    window.getTypeBadge = function(type) {
        const badges = {
            'code_php': '<span class="badge bg-primary"><i class="fab fa-php me-1"></i> PHP</span>',
            'code_js': '<span class="badge bg-warning"><i class="fab fa-js me-1"></i> JavaScript</span>',
            'code_python': '<span class="badge bg-info"><i class="fab fa-python me-1"></i> Python</span>',
            'documentation': '<span class="badge bg-secondary"><i class="fas fa-book me-1"></i> Documentation</span>',
            'business_data': '<span class="badge bg-success"><i class="fas fa-database me-1"></i> Business Data</span>',
            'config': '<span class="badge bg-dark"><i class="fas fa-cog me-1"></i> Config</span>'
        };
        return badges[type] || `<span class="badge bg-light text-dark">${type}</span>`;
    };
    
    /**
     * Perform global search
     */
    function performGlobalSearch(query) {
        $.ajax({
            url: 'api/search.php',
            method: 'GET',
            data: {
                query: query,
                limit: 5
            },
            success: function(response) {
                if (response.success) {
                    displayGlobalSearchResults(response.data);
                }
            }
        });
    }
    
    /**
     * Display global search results
     */
    function displayGlobalSearchResults(results) {
        // Implement dropdown search results
        // This is a simplified version
        console.log('Search results:', results);
    }
    
    /**
     * Show help modal
     */
    function showHelpModal() {
        const helpContent = `
            <div class="help-modal">
                <h4>Keyboard Shortcuts</h4>
                <ul>
                    <li><kbd>Ctrl</kbd> + <kbd>K</kbd> - Quick Search</li>
                    <li><kbd>Ctrl</kbd> + <kbd>R</kbd> - Refresh Stats</li>
                    <li><kbd>Esc</kbd> - Close Modals</li>
                </ul>
                <h4>Bot Commands</h4>
                <ul>
                    <li><code>!search [query]</code> - Search intelligence</li>
                    <li><code>!doc [path]</code> - Get document</li>
                    <li><code>!code [function]</code> - Find function</li>
                    <li><code>!stats</code> - System statistics</li>
                </ul>
                <h4>API Endpoints</h4>
                <ul>
                    <li><code>GET /api/search</code> - Search files</li>
                    <li><code>GET /api/stats</code> - Get statistics</li>
                    <li><code>GET /api/document</code> - Get file content</li>
                    <li><code>POST /api/scan</code> - Trigger scan</li>
                </ul>
            </div>
        `;
        
        // Show help in modal (simplified)
        alert('Help System - Check console for full documentation');
        console.log('Help Content:', helpContent);
    }
    
    // ============================================================================
    // INITIALIZE DATATABLES (if present)
    // ============================================================================
    
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            }
        });
    }
    
});
